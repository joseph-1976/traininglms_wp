<?php
namespace TheTrainingMangerLMS\Content;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

final class Error {

	public function content( $context, $data = null ) {
		echo $data['message'];
	}

}

?>
