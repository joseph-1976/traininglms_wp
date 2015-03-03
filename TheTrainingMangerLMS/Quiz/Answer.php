<?php
namespace TheTrainingMangerLMS\Quiz;

abstract class Answer {
	const CORRECT = 'correct';
	const PARTIALLY_CORRECT = 'partially_correct';
	const INCORRECT = 'incorrect';
	const NOT_APPLICABLE = 'not_applicable';
	const HASH_ALGO = 'crc32b';
	public static $outcomes = array(self::CORRECT, self::PARTIALLY_CORRECT, self::INCORRECT, self::NOT_APPLICABLE);
	public abstract static function getType();
	public abstract function getChoices();
	public abstract function evaluate($response);
	public abstract function getTotalPossiblePoints();
}

abstract class SimpleObject {

	public function __construct( $parameters = array() ) {
		if (!is_array($parameters))
			throw new \InvalidArgumentException("Parameters must be an associate array of key, value pairs.");
		foreach($parameters as $key => $value) {
			if (!property_exists($this, $key))
				throw new \InvalidArgumentException("Parameter {$key} is not a member of " . get_called_class() . ".");
			$this->$key = $value;
		}
	}
}

/**
 * Single Choice.
 *
 * This class provides the fields associated with a single choice answer.
 * 
 */
class SingleChoice extends SimpleObject {
/**
 * Display text for the choice.
 */
public $content;
/**
 * The type of the display text; plain, html, etc...
 */
public $content_type;
/**
 * Is this choice correct?
 */
public $correct;
/**
 * The number of points for this choice.
 */
public $points;
}

/**
 * Single Choice Answer.
 *
 * This class provides encapsulation for a question with multiple choice answers where
 * there is only one correct answer.  
 * 
 */
class SingleChoiceAnswer extends Answer {
private $choices;

	public function __construct($choices) {
		// validate $choices
		// choices must be SingleChoice
		// only one Choice can be 'correct'
		$this->choices = array();
		$found = false;
		foreach($choices as $choice) {
			if (get_class($choice) != 'TheTrainingMangerLMS\Quiz\SingleChoice')
				throw new \InvalidArgumentException("SingleChoiceAnswer requires choices be of type SingleChoice.");
			if ($choice->correct) {
				if ($found)
					throw new \InvalidArgumentException("SingleChoiceAnswer requires only one choice to be marked as correct.");
				$found = true;
			}
			if (array_key_exists(hash(Answer::HASH_ALGO, $choice->content), $this->choices))
				throw new \InvalidArgumentException("Choices can't have the same content.");
			$this->choices[hash(Answer::HASH_ALGO, $choice->content)] = $choice;
		}
		if (!$found)
			throw new \InvalidArgumentException("SingleChoiceAnswer requires one choice to be marked as correct.");
	}

	public static function getType() {
		return "SingleChoice";
	}

	public function getChoices() {
		return array_values($this->choices);
	}
	
	public function getTotalPossiblePoints() {
		$total = 0;
		foreach($this->choices as $choice) {
			$total = $total + $choice->points;
		}
		return $total;
	}

	/**
	 * Evaluate and score the user's response for a Single Choice Question.
	 *
	 * This function evaluates the users response to the question based on the choice
	 * criterion.  Users get points if they accurately select the right answer and
	 * points if they do not select incorrect answers.
	 *
	 * @param array $response {
	 *      @type array $answer An array containing the hash of the choice content from
	 *                          of a single selection.
	 * }
	 * @return array An array containing the outcome and score.
	 */
	public function evaluate($response) {
		$answers = $response['answer'];
		if ( count($answers) > 1 )
			throw new \InvalidArgumentException("SingleChoiceAnswer expects zero or one answers.");
		if (count(array_diff($answers, array_keys($this->choices))) != 0)
			throw new \InvalidArgumentException("Supplied answer is not a valid choice.");
		$total = 0; $outcome = self::INCORRECT;
		foreach($this->choices as $choice_hash => $choice) {
			if ($choice->correct && in_array($choice_hash, $answers)) {
				$total += $choice->points;
				$outcome = self::CORRECT;
			} elseif (!$choice->correct && !in_array($choice_hash, $answers)) {
				$total += $choice->points;
			}
		}
		return array( 'outcome' => $outcome, 'score' => $total );
	}
}

/**
 * Multiple Choice.
 *
 * This class provides the fields associated with a multiple choice answer.
 * 
 */
class MultipleChoice extends SimpleObject {
/**
 * Display text for the choice.
 */
public $content;
/**
 * The type of the display text; plain, html, etc...
 */
public $content_type;
/**
 * Is this choice correct?
 */
public $correct;
/**
 * The number of points for this choice.
 */
public $points;
}

/**
 * Multiple Choice Answer.
 *
 * This class provides encapsulation for a question with multiple choice answers where
 * there is more than one correct answer.  
 * 
 */
class MultipleChoiceAnswer extends Answer {
private $choices;

	public function __construct($choices) {
		// validate $choices
		// choices must be MultipleChoice
		// many Choice's can be 'correct', but at least one must be correct
		$this->choices = array();
		$found = false;
		foreach($choices as $choice) {
			if (get_class($choice) != 'TheTrainingMangerLMS\Quiz\MultipleChoice')
				throw new \InvalidArgumentException("MultipleChoiceAnswer requires choices be of type MultipleChoice.");
			if ($choice->correct) {
				$found = true;
			}
			if (array_key_exists(hash(Answer::HASH_ALGO, $choice->content), $this->choices))
				throw new \InvalidArgumentException("Choices can't have the same content.");
			$this->choices[hash(Answer::HASH_ALGO, $choice->content)] = $choice;
		}
		if (!$found)
			throw new \InvalidArgumentException("MultipleChoiceAnswer requires at least one choice to be marked as correct.");
	}

	public static function getType() {
		return "MultipleChoice";
	}

	public function getChoices() {
		return array_values($this->choices);
	}
	
	public function getTotalPossiblePoints() {
		$total = 0;
		foreach($this->choices as $choice) {
			$total = $total + $choice->points;
		}
		return $total;
	}

	/**
	 * Evaluate and score the user's response for a Multiple Choice Question.
	 *
	 * This function evaluates the users response to the question based on the choice
	 * criterion.  Users get points if they accurately select the right answer(s) and
	 * points if they do not select incorrect answer(s).
	 *
	 * @param array $response {
	 *      @type array $answer An array containing the hash of the choice content from
	 *                          multiple selections.
	 * }
	 * @return array An array containing the outcome and score.
	 */
	public function evaluate($response) {
		$answers = $response['answer'];
		if (count(array_diff($answers, array_keys($this->choices))) != 0)
			throw new \InvalidArgumentException("Supplied answer is not a valid choice.");
		$total = 0; $mark_correct = false; $mark_incorrect = false;
		foreach($this->choices as $choice_hash => $choice) {
			if ($choice->correct) {
				if (in_array($choice_hash, $answers)) {
					$total += $choice->points;
					$mark_correct = true;
				} else {
					$mark_incorrect = true;
				}
			} elseif (!$choice->correct) {
				if (!in_array($choice_hash, $answers)) {
					$total += $choice->points;
					$mark_correct = true;
				} else {
					$mark_incorrect = true;
				}
			}
		}
		$outcome = $mark_correct ? $mark_incorrect ? self::PARTIALLY_CORRECT : self::CORRECT : self::INCORRECT;
		return array( 'outcome' => $outcome, 'score' => $total );
	}
}

/**
 * Free Choice.
 *
 * This class provides the fields associated with a free choice answer.
 * 
 */
class FreeChoice extends SimpleObject {
/**
 * Display text for the choice.
 */
public $content;
/**
 * The type of the display text; plain, html, etc...
 */
public $content_type;
/**
 * The number of points for this choice.
 */
public $points;
}

/**
 * Free Choice Answer.
 *
 * This class provides encapsulation for a question with multiple choices where
 * there is no right or wrong answer.  
 * 
 */
class FreeChoiceAnswer extends Answer {
private $choices;

	public function __construct($choices) {
		// validate $choices
		// choices must be of type FreeChoice
		$this->choices = array();
		foreach($choices as $choice) {
			if (get_class($choice) != 'TheTrainingMangerLMS\Quiz\FreeChoice')
				throw new \InvalidArgumentException("FreeChoiceAnswer requires choices be of type FreeChoice.");
			if (array_key_exists(hash(Answer::HASH_ALGO, $choice->content), $this->choices))
				throw new \InvalidArgumentException("Choices can't have the same content.");
			$this->choices[hash(Answer::HASH_ALGO, $choice->content)] = $choice;
		}
	}

	public static function getType() {
		return "FreeChoice";
	}

	public function getChoices() {
		return array_values($this->choices);
	}
	
	public function getTotalPossiblePoints() {
		$total = 0;
		foreach($this->choices as $choice) {
			$total = $total + $choice->points;
		}
		return $total;
	}

	/**
	 * Evaluate and score the user's response for a Free Choice Question.
	 *
	 * This function evaluates the users response to the question based on the choice
	 * criterion.  Users get points based on their selections.  There is no right or
	 * wrong answers.
	 *
	 * @param array $response {
	 *      @type array $answer An array containing the hash of the choice content from
	 *                          multiple selections.
	 * }
	 * @return array An array containing the outcome and score.
	 */
	public function evaluate($response) {
		$answers = $response['answer'];
		if (count(array_diff($answers, array_keys($this->choices))) != 0)
			throw new \InvalidArgumentException("Supplied answer(s) is not a valid choice.");
		$total = 0;
		foreach($this->choices as $choice_hash => $choice) {
			if (in_array($choice_hash, $answers))
				$total += $choice->points;
		}
		return array( 'outcome' => self::NOT_APPLICABLE, 'score' => $total );
	}
}

/**
 * Sorting Choice.
 *
 * This class provides the fields associated with a sorting choice answer.
 * 
 */
class SortingChoice extends SimpleObject {
/**
 * Display text for the choice.
 */
	public $content;
/**
 * The type of the display text; plain, html, etc...
 */
	public $content_type;
/**
 * The number of points for this choice.
 */
	public $points;
}

/**
 * Sorting Answer.
 *
 * This class provides encapsulation for a question with the choices must be
 * sorted in a certain order.  
 * 
 */
class SortingAnswer extends Answer {
private $choices;

	public function __construct($choices) {
		// validate $choices
		// choices must be of type SortingChoice
		$this->choices = array();
		foreach($choices as $choice) {
			if (get_class($choice) != 'TheTrainingMangerLMS\Quiz\SortingChoice')
				throw new \InvalidArgumentException("SortingChoiceAnswer requires choices be of type SortingChoice.");
			if (array_key_exists(hash(Answer::HASH_ALGO, $choice->content), $this->choices))
				throw new \InvalidArgumentException("Choices can't have the same content.");
			$this->choices[hash(Answer::HASH_ALGO, $choice->content)] = $choice;
		}
	}

	public static function getType() {
		return "Sorting";
	}

	public function getChoices() {
		return array_values($this->choices);
	}
	
	public function getTotalPossiblePoints() {
		$total = 0;
		foreach($this->choices as $choice) {
			$total = $total + $choice->points;
		}
		return $total;
	}

	/**
	 * Evaluate and score the user's response for a Sorting Question.
	 *
	 * This function evaluates the users response to the question based on the choice
	 * criterion.  Users get points based on the order of their selections.
	 *
	 * @param array $response {
	 *      @type array $answer An array containing the hash of the choice content from
	 *                          the sorted selections.
	 * }
	 * @return array An array containing the outcome and score.
	 */
	public function evaluate($response) {
		$answers = $response['answer'];
		if (count(array_diff($answers, array_keys($this->choices))) != 0)
			throw new \InvalidArgumentException("Supplied answer(s) is not a valid choice.");
		//TODO: answers should be the same size as $choices
		$total = 0; $mark_correct = false; $mark_incorrect = false;
		foreach($this->choices as $choice_hash => $choice) {
			$answer = array_shift($answers);
			if ($choice_hash == $answer) {
				$total += $choice->points;
				$mark_correct = true;
			} else {
				$mark_incorrect = true;
			}
		}
		$outcome = $mark_correct ? $mark_incorrect ? self::PARTIALLY_CORRECT : self::CORRECT : self::INCORRECT;
		return array( 'outcome' => $outcome, 'score' => $total );
	}
}

/**
 * Assessment Choice.
 *
 * This class provides the fields associated with an assessment choice answer.
 * 
 */
class AssessmentChoice extends SimpleObject {
/**
 * The text values that display on the bottom of the scale.
 */
	public $scale_values;
/**
 * The text label displayed on the left of the scale.
 */
	public $left_label;
/**
 * The text label displayed on the right of the scale.
 */
	public $right_label;
}

/**
 * Assessment Answer.
 *
 * This class provides encapsulation for a question where the choice is limited to a
 * range of values.  
 * 
 */
class AssessmentAnswer extends Answer {
private $choice;

	public function __construct($choice) {
		// validate $choice
		// choices must be of type AssessmentChoice
		if (is_array($choice))
			throw new \InvalidArgumentException("AssessmentAnswer only takes one choice.");
		if (get_class($choice) != 'TheTrainingMangerLMS\Quiz\AssessmentChoice')
				throw new \InvalidArgumentException("AssessmentAnswer requires choices be of type AssessmentChoice.");
		$this->choice = $choice;
	}

	public static function getType() {
		return "Assessment";
	}

	public function getChoices() {
		return $this->choice;
	}
	
	public function getTotalPossiblePoints() {
		return count($this->choice->scale_values);
	}

	/**
	 * Evaluate and score the user's response for an Assessment Question.
	 *
	 * This function evaluates the users response to the question based on the choice
	 * criterion.  The users score is based on the index of selection in the scale values.
	 *
	 * @param array $response {
	 *      @type array $answer An array containing the free text of the choice from 
	 *                          the scale values.
	 * }
	 * @return array An array containing the outcome and score.
	 */
	public function evaluate($response) {
		$answers = $response['answer'];
		// check to see if User answered the question (no points, if so)
		if (count($answers) == 0) {
			$index = 0;
		} else {
			$index = array_search($answers[0], $this->choice->scale_values);
			if ($index == FALSE)
				throw \InvalidArgumentException("The response answer value is not a part of the Answers scale values.");
			$index++;
		}
		return array( 'score' => $index, 'outcome' => self::NOT_APPLICABLE );
	}
}

/**
 * Matching Criterion.
 *
 * This class provides the fields associated with a matching answer.
 * 
 */
class MatchingCriterion extends SimpleObject {
/**
 * Display text for the choice.
 */
	public $content;
/**
 * The type of the display text; plain, html, etc...
 */
	public $content_type;
/**
 * The number of points for this choice.
 */
	public $points;
/**
 * The plain text matches for this choice.
 */
	public $matches;
}

/**
 * Matching Answer.
 *
 * This class provides encapsulation for a question where users must match term(s) to their
 * corresponding choice(s).
 * 
 */
class MatrixMatchingAnswer extends Answer {
private $choices;

	public function __construct($choices) {
		// validate $choices
		// choices must be of type MatcingCriterion
		$this->choices = array();
		foreach($choices as $choice) {
			if (get_class($choice) != 'TheTrainingMangerLMS\Quiz\MatchingCriterion')
				throw new \InvalidArgumentException("MatrixMatchingAnswer requires choices be of type MatchingCriterion.");
			// TODO: veriy that $matches are not in previously added Criterion
			if (array_key_exists(hash(Answer::HASH_ALGO, $choice->content), $this->choices))
				throw new \InvalidArgumentException("Choices can't have the same content.");
			$this->choices[hash(Answer::HASH_ALGO, $choice->content)] = $choice;
		}
	}

	public static function getType() {
		return "MatrixMatching";
	}

	public function getChoices() {
		return array_values($this->choices);
	}

	public function getTotalPossiblePoints() {
		$total = 0;
		foreach($this->choices as $choice) {
			$total = $total + $choice->points;
		}
		return $total;
	}

	/**
	 * Evaluate and score the user's response for a Matching Question.
	 *
	 * This function evaluates the users response to a series of choices where users must match
	 * terms to each choice.  Users only get points if all terms for the choice are matched 
	 * correctly.
	 *
	 * @param array $response {
	 *      @type array $answer An array containing keys that are the hash of the choice content
     *                          and values that are an array of the plain text terms that users have
	 *                          matched to that choice.
	 * }
	 * @return array An array containing the outcome and score.
	 */
	public function evaluate($response) {
		$answers = $response['answer'];
		if (count(array_diff(array_keys($answers), array_keys($this->choices))) != 0)
			throw new \InvalidArgumentException("Supplied answer(s) is not a valid choice.");
		$total = 0; $mark_correct = false; $mark_incorrect = false;
		foreach($this->choices as $choice_hash => $choice) {
			if (!array_key_exists($choice_hash, $answers)) { $mark_incorrect = true; next; }
			$matches = $choice->matches;
			if (!array_diff($matches, $answers[$choice_hash]) && !array_diff($answers[$choice_hash], $matches)) {
				$total += $choice->points;
				$mark_correct = true;
			} else {
				$mark_incorrect = true;
			}
		}
		$outcome = $mark_correct ? $mark_incorrect ? self::PARTIALLY_CORRECT : self::CORRECT : self::INCORRECT;
		return array( 'outcome' => $outcome, 'score' => $total );
	}
}

/**
 * Cloze Answer.
 *
 * This class provides encapsulation for a question where users must fill in blanks in the context
 * of the supplied text.  Text is free form with embedded "cloze" answers appearing as 
 * {term1:points|term2:points|term3:points}.  If the term points isn't provided, it defaults to 1.
 * 
 */
class ClozeAnswer extends Answer {
private $text;
private $terms;
	public function __construct($text) {
		$this->text = $text;
		$this->terms = self::getTerms($text); // provides validation
	}

	public static function getType() {
		return "Cloze";
	}

	public function getChoices() {
		return $this->text;
	}

	public function getTotalPossiblePoints() {
		$total = 0;
		foreach($this->terms as $term) {
			$max = 0;
			foreach($term as $match) {
				$max = max($max, $match['points']);
			}
			$total += $max;
		}
		return $total;
	}

	public static function validate($text) {
		self::getTerms($text);
	}

	private static function parseTerm($term) {
		$pos = strrpos($term, ':');
		if ($pos !== FALSE) {
			$points = substr($term, $pos+1);
			if ($points == '') $points = 1;
			$points = $points + 0;
			if (!is_int($points))
				throw new \InvalidArgumentException("Points in term not an integer value.");
			return array( 'term' => substr($term, 0, $pos), 'points' => $points );
		} else {
			return array( 'term' => $term, 'points' => 1 );
		}
	}

	private static function getTerms($text) {
		$terms = array(); $pos = 0; 
		while ($pos !== FALSE) {
			// step through text and look for {} entries
			$pos = strpos($text, "{", $pos);
			if ($pos !== FALSE) {
				$stop = strpos($text, "}", $pos+1);
				if ($stop === FALSE)
					throw new \InvalidArgumentException("Found starting '{' with no matching '}'"); // ParseException
				$excerpt = substr($text, $pos+1, $stop-$pos-1);
				$matches = explode('|', $excerpt);
				$matches = array_map(array(__CLASS__, 'parseTerm'), $matches);
				array_push($terms, $matches);
				$pos = $stop;
			}
			$pos++;
		}
		return $terms;
	}

	/**
	 * Evaluate and score the user's response for a Cloze Question.
	 *
	 * This function evaluates the users response to a series of fill in the blanks where users
	 * must provide the correct term.  The user only gets points if the term matches one of the
	 * possible choices provided by the criteria.
	 *
	 * @param array $response {
	 *      @type array $answer An array containing the plain text responses from the user in the
     *                          order in which they appear in the text.
	 * }
	 * @return array An array containing the outcome and score.
	 */
	public function evaluate($response) {
		if (count($this->terms) != count($response['answer']))
			throw new \InvalidArgumentException("Number of answers do not match number of terms.");
		$answers = $response['answer'];
		$total = 0; $mark_correct = false; $mark_incorrect = false;
		foreach($this->terms as $term) {
			$answer = array_shift($answers);
			$found = false;
			foreach($term as $match) {
				if (strtolower($answer) == strtolower($match['term'])) {
					$total += $match['points'];
					$mark_correct = true;
					$found = true;
					break;
				}
			}
			if (!$found) $mark_incorrect = true;
		}
		$outcome = $mark_correct ? $mark_incorrect ? self::PARTIALLY_CORRECT : self::CORRECT : self::INCORRECT;
		return array( 'outcome' => $outcome, 'score' => $total );
	}
}

?>