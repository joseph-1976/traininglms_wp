<?php
/**
 * 
 * This file provides the environmental set-up and main method to introduce
 * the plugin into the WordPress system.
 *
 */


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

require( dirname( __FILE__ ) . '/bootstrap.php' );
array_push( $GLOBALS['class_path'], realpath(__DIR__) );
require( dirname( __FILE__ ) . '/wp_extend.php' );

// Common
require( dirname( __FILE__ ) . '/functions.php' );

import('TheTrainingMangerLMS.Install');
import('TheTrainingMangerLMS');

// Do activate/deactivate/uninstall here
//register_activation_hook(   __FILE__, array( 'TheTrainingMangerLMS\Install', 'activate' ) );
//register_deactivation_hook( __FILE__, array( 'TheTrainingMangerLMS\Install', 'deactivate' ) );
//register_uninstall_hook(    __FILE__, array( 'TheTrainingMangerLMS\Install', 'uninstall' ) );

// Let's do it!
TheTrainingMangerLMS();

?>
