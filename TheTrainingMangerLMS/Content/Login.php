<?php
namespace TheTrainingMangerLMS\Content;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

final class Login {//implements Renderable {

	public function render() {
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('get_content', array($this, 'content'));
	}

	public function enqueue_scripts() {
		// load css and scripts for wp_login_form
		wp_admin_css( 'login', true );
		do_action( 'login_enqueue_scripts' );
	}

	public function content( $context = '', $data = null ) {
		echo '<div class="login wp-core-ui" style="width:340px; margin: 20px auto;">';
		wp_login_form(array('redirect' => $_SERVER['REQUEST_URI']));
		// TODO: message if the login failed
		echo "</div>";
		return;
	}

}
?>
