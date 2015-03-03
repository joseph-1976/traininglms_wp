<?php
$fields = array(
	'number_of_questions' => array('label' => "Number of Questions",
		'description' => "The number of questions that will be given to user.  Use 0 for all."),
	'time_limit' => array('label' => 'Time Limit (seconds)',
		'description' => "Amount of time the user has to take the test.  Use 0 for no limit."),
	'allowed_repeats' => array('label' => "Allowed User Repeats",
		'description' => "Number of times the user is allowed to repeat the test."),
	'pass_percentage' => array('label' => "Passing Percentge",
		'description' => "Percentage of points requierd for the user to pass this quiz."),
//	'score_levels' =>
	// delivery [mode] options
	'provide_feedback' => array('label' => "Proivide Feedback", 'input_type' => 'checkbox',
		'description' => "Provide feedback to the user if the user misses the question.");
	'immediate_feedback' => array('label' => "Immediate Feedback",
		'description' => "Should feedback be provided immediately after the question or the end of the quiz."),
	'allow_back_button' => array('label' => "Allow Back Button",
		'description' => "Show and allow user to use the back button to resubmit the question."),
	'all_questions_one_page' => array('label' => "All Questions",
		'description' => "Show all questions on one page."),
	// reporting options
	'send_instructor_email' => array('label' => "Send Instructor Email",
		'description' => "Send the instructor an email report on quiz completion."),
	'send_user_email' => array('label' => "Send User Email",
		'description' => "Send the user an email report on quiz completion."),
);

class QuestionsOptions extends Utility\PostOptions {
static $fields = array(
	'provide_feedback' => array('label' => "Proivide Feedback", 'default' => 'true', 'input_type' => 'checkbox',
		'description' => "Provide feedback to the user if the user misses the question.");
	'immediate_feedback' => array('label' => "Immediate Feedback",  'default' => 'false',
		'description' => "Should feedback be provided immediately after the question or the end of the quiz."),
/*	'randomize_answers' => array('label' => "Randomize Answers", 'default' => 'true',
		'description' => "Randomize multiple choice answers.")
	'alphabetize_answers' => array('label' => "Alphabetize Answers", 'default' => 'true',
		'description' => "Show letters beside multiple choice answers."),*/
	'display_points' => array('label' => "Display Points", 'default' => 'false',
		'description' => "Show the number of points with each question."),
	'display_category' => array('label' => "Show Category", 'default' => 'false',
		'description' => "Show the associated category with each question."),
);

class ReportingOptions extends Utility\PostOptions {
Report Score
Report Total Quiz Time
Report Average Point Score?
Report Category Score

---- Radio
No Grouping
Group by category
Group by Group
----
----- Radio
One question per page
All questions on one page
Grouped questions on one page
-----

?>