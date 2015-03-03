<?php
namespace TheTrainingMangerLMS\Content;
import('TheTrainingMangerLMS.Constants');


// Exit if accessed directly
if (!defined('ABSPATH')) exit;


final class CourseList
{ //implements Routable {

    public function __construct()
    { // maybe pass in sections such as Default, NotFound and Error and Login???
    }

    public static function route()
    {

        add_filter('template_include', array(__CLASS__, 'switch_template'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
        //add_action('get_content'), array($this, 'get_content'));

    }

    /**
     *
     */
    public function enqueue_scripts()
    {
        //wp_enqueue_style('main-style' , plugin_dir_url(__FILE__) . 'CourseDetails/css/layout-default.css');
        //wp_enqueue_style('sortable-style' , plugin_dir_url(__FILE__) . 'CourseDetails/css/sortable.css');
        //wp_enqueue_script('main-js' , plugin_dir_url(__FILE__) . 'CourseDetails/js/main.js');
        //wp_enqueue_script('sortable-js' , plugin_dir_url(__FILE__) . 'CourseDetails/js/sortable.js');
        //wp_enqueue_style('fancybox-style' , plugin_dir_url(__FILE__) . 'CourseDetails/js/fancybox/source/jquery.fancybox.css');
        //De-register / Re-register
         //error_log(plugin_dir_url(__FILE__) . 'CourseDetails/js/fancybox/source/jquery.fancybox.js');
        //wp_deregister_script('fancybox');
        //wp_enqueue_script('jquery-fancybox', plugin_dir_url( __FILE__ ) . 'CourseDetails/js/fancybox/source/jquery.fancybox.js', array('jquery'), NULL);
        //wp_enqueue_script('jquery-datatables', plugin_dir_url( __FILE__ ) . 'CourseDetails/js/jquery.dataTables.min.js', array('jquery'), NULL);
        //wp_enqueue_script('jquery-maskedinput', plugin_dir_url( __FILE__ ) . 'CourseDetails/js/jquery.maskedinput.js', array('jquery'), NULL);
    }

    public static function switch_template($template)
    {

        $template = dirname(__FILE__) . '\PublicFrontEnd\CourseList\html\course-list.php';

        return $template;
    }
    /*public function content( $context = '', $data = null ) {
        // check for action and act accordinly, load $course
        require dirname(__FILE__) . '/html/course-builder.php';

    }*/
}

?>
