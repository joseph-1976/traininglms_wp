<?php
namespace TheTrainingMangerLMS\Admin;

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

abstract class AjaxActionHandler {
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_OTHER_CLIENT = 420;
    const HTTP_INTERNAL_SERVER_ERROR = 500;


	public static function addHandler() {
		add_action( 'wp_ajax_' . static::getAjaxAction(), array( get_called_class(), 'ajaxCallback' ) );
	}

	public abstract static function getAjaxAction(); // NameSpace
	public abstract static function getAjaxMethods(); // [Method]Handlers

	public static function ajaxCallback() {
		static::validateAjaxReferer(static::getAjaxAction());
		if (!isset($_POST['method']) || !array_key_exists($_POST['method'], static::getAjaxMethods()))
			//!in_array($_POST['subaction'], array_map(function($e) { return $e['name']; }, static::getAjaxMethods())))
			static::returnStatusCode(self::HTTP_BAD_REQUEST);
		// clean-up $_POST for our methods
		$_POST = stripslashes_deep( $_POST );
		// set up a wrapped environment for the callback
		// force display_errors to off to generate status code 500
		ini_set('display_errors', 0); 
		// register shutdown function to catch fatal errors
		register_shutdown_function( array(__CLASS__, 'isShutdownError') );
		// set an exception handler for uncaught exceptions
		set_exception_handler ( array(__CLASS__, 'exceptionHandler') );
		//$func = static::getAjaxMethods()[$_POST['method']]; $func();
		$func = static::getAjaxMethods()[$_POST['method']];
		if (!is_callable($func))
			self::returnStatusCode(self::HTTP_INTERNAL_SERVER_ERROR, "Server side method isn't callable.");
		call_user_func($func);
		//$this->{$_POST['subaction'] . '_callback'}();
	}

	protected static function validateAjaxReferer( $action, $die = false ) {
		if ( current_user_can( 'manage_options' ) && check_ajax_referer($action, 'nonce', $die )  ) {
			return true;
		}
		// 401: Unauthorized
		static::returnStatusCode(self::HTTP_UNAUTHORIZED);
	}

	protected static function getAjaxRequestInfo () {
		return array('Namespace' => static::getAjaxAction(), 'Method' => $_POST['method'], 'User' => get_current_user_id(), 'IP' => $_SERVER['REMOTE_ADDR']);
	}

	protected static function returnStatusCode( $code, $content = '' ) {
		http_response_code($code);
		echo $content;
    	if ( is_ajax() )
			wp_die();
		else
			die;
	}

	protected static function returnException(\Exception $exception ) {
		$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
		$code = self::HTTP_OTHER_CLIENT;
		$text = "Internal Error";
        @header($protocol . ' ' . $code . ' ' . $text);
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		wp_send_json(array(
			'errors' => array( array( 'msg' => $exception->getMessage(), 'code' => $exception->getCode(), 'type' => get_class($exception) ) )
		));
	}

	protected static function returnTemplate($template, $env) {
		extract($env);
		ob_start();
		try {
			require $template;
		} catch (\Exception $exception) {
			ob_end_clean();
			self::returnException($exception);
		}
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'Content-Length: ' . ob_get_length() ) ;
		ob_end_flush();
    	if ( is_ajax() )
			wp_die();
		else
			die;
	}

	public static function errorHandler( $errno, $errstr ) {
		http_response_code(self::HTTP_INTERNAL_SERVER_ERROR);
		return false;
	}

	public static function exceptionHandler( \Exception $e ) {
		self::returnException($e);
	}

	public static function isShutdownError() {
		$e = error_get_last();
		if ($e && ($e['type'] & (E_ERROR|E_COMPILE_ERROR|E_USER_ERROR|E_CORE_ERROR))) {
			http_response_code(self::HTTP_INTERNAL_SERVER_ERROR);
			echo "<br/><p>" . $e['message'] . "</p>";
		}
	}
	
}

?>