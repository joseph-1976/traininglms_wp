<?php

// Set-up class_path superglobal variable using php include_path as basis
if (!array_key_exists('class_path', $GLOBALS)) {
	$GLOBALS['class_path'] = array();
	foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
		// skip '.' for now.  Will use plugin path instead
		if ($path == '.') continue;
		array_push( $GLOBALS['class_path'], realpath($path) );
	}
}

if (!function_exists('import')):
// FIXME: we'll need an import call stack for circular references
function import($package = '') {
	if ($package == '') {
		trigger_error("Package argument must be specified.", E_USER_ERROR);
	}
	$package_bits = explode('.', $package);
	// use explode to make this more robust
	// also check for '*' for directory imports
	//$package_path =  str_replace('.', DIRECTORY_SEPARATOR, $package) . '.php';
	$package_path = implode(DIRECTORY_SEPARATOR, $package_bits) . '.php';
	foreach ($GLOBALS['class_path'] as $path) {
		$file = $path . DIRECTORY_SEPARATOR . $package_path;//combinepath($path, $package_path))
		if (file_exists($file)) {
 			require_once($file);
 			$entity_name = implode('\\', $package_bits);
 			if (!class_exists($entity_name, false) && !interface_exists($entity_name, false)
 					&& !trait_exists($entity_name, false)) {
 				$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
 				trigger_error("Entity '" . $package . "' not found in file '" . $package_path . "' for import called in " .
 					$caller['file'] . " on line " . $caller['line'], E_USER_ERROR);
 			}
			return;
		}
	}
	$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
	trigger_error("Entity '" . $package . "' not found for " . $caller['function'] . " called in " . $caller['file'] . " on line " . $caller['line'], E_USER_ERROR);
}
endif;

if (!function_exists('log')):
function log($tag = '', $details = '', $options = null) {
	if (($tag == 'DEBUG') && (WP_DEBUG == false)) return;
	// TODO: if (($tag == 'TRACE')) ...
	// TRACE, INFO, DEBUG, WARN, ERROR, FATAL
	$msg = $tag . ': ';
	if ( is_string($details) ) {
		$msg .= $details;
	} else {
		$msg .= print_r( $details, true );
	}
	error_log($msg);
}
endif;
?>
