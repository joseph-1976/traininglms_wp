<?php
ob_start();

//change this to your path
$path = 'c:/BitNami/wordpress-3.5.1-1/apps/wordpress-tests/includes/bootstrap.php';

if (file_exists($path)) {
    $GLOBALS['wp_tests_options'] = array(
        'active_plugins' => array('ttp-lms/main.php'),
        'template' => 'weaver',
        'stylesheet' => 'weaver'
    );

    require_once $path;
} else {
    exit("Couldn't find wordpress-tests/bootstrap.php.");
}

error_reporting(-1);
?>
