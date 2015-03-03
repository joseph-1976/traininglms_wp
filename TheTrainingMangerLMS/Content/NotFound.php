<?php
namespace TheTrainingMangerLMS\Content;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

final class NotFound { //implements TheTrainingMangerLMS\ContentGenerator {

	public static function content() {
		echo "There is no content available that corresponds to the given request.";
	}

}

?>
