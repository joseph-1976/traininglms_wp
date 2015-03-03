<?php
/***************************************************************
 *                                                             *
 * This file contains a collection of functions that extend    *
 * the functionality of a WordPress install.                   *
 *                                                             *
 ***************************************************************/

// Is the request an ajax call
if (!function_exists('is_ajax')):
function is_ajax() {
	return defined('DOING_AJAX') && DOING_AJAX;
}
endif;

abstract class WP_Content_Handler {
	public abstract function bind();
	public abstract function matchRequest();
	public abstract function content( $context, $data = null );
}

class WP_Bind_Exception extends Exception {}

$GLOBALS['registry'] = array();
function wp_addContentHandler(WP_Content_Handler $handler, $priority = 10) {
	if (!is_int($priority)) $priority = 10;
	if (!array_key_exists('contentHandlers', $GLOBALS['registry'])) {
		$GLOBALS['registry']['contentHandlers'] = array();
	}
	if (array_key_exists($priority, $GLOBALS['registry']['contentHandlers'])) {
		array_push($GLOBALS['registry']['contentHandlers'], $handler);
	} else {
		$GLOBALS['registry']['contentHandlers'][$priority] = array( $handler );
	}
}

add_action('wp', 'bindContentHandlers');

/* Bind any content handlers to the current request if their
   isMyRequest function evaluates to true.  Binding a handler is no
   guarentee that content will be generated for the specific
   request since the call for content also depends on the
   context.
 */

function bindContentHandlers() {
	if (array_key_exists('contentHandlers', $GLOBALS['registry'])) {
		$contentHandlers = $GLOBALS['registry']['contentHandlers'];
		foreach ($contentHandlers as $priority => $handlers) {
			foreach($handlers as $handler) {
				if ($handler->matchRequest()) {
					try {
						$handler->bind();
						return;
					} catch (WP_Bind_Exception $e) {
						trigger_error($e->message(), E_NOTICE);
					}
				}
			}
		}
	}
}

function get_content( $context = '', $data = null ) {
	do_action( 'get_content', $context, $data );
}

?>
