<?php
namespace TheTrainingMangerLMS;

import('TheTrainingMangerLMS.Content.Error');

class ContentGenerationException extends \Exception {

}

trait ContentWrapper {
	
	public function contentWrap( $context, $data = null ) {
		// set-up our wrapped environment
		ob_start();
		try {
			static::content( $context, $data );
			ob_end_flush();
		} catch (ContentGenerationException $e) { // MAYBE: change this to any exception
			ob_end_clean();
			\TheTrainingMangerLMS\Content\Error::content( $context, array( 'message' => $e->getMessage() ) );
		}
	}

}

interface Renderable { //was ContentGenerator
	public function render();
//	public function content($context = '', $data = null);
}

?>
