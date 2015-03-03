<?php

/**
 * Training LMS Plugin Main class
 *
 * This file provides the main Training LMS class which contains the
 * functionality necessary to set-up the plugin.
 *
 */


// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!class_exists('TheTrainingMangerLMS')) :
    /** Load classes **/
// Core
    import('TheTrainingMangerLMS.OnlineCourse');
    import('TheTrainingMangerLMS.OnlineLesson');

    import('TheTrainingMangerLMS.Company');
    import('TheTrainingMangerLMS.TestAdminInterface.Company_Testing');

    import('TheTrainingMangerLMS.Milestone');
    import('TheTrainingMangerLMS.TestAdminInterface.Milestone_Testing');

    import('TheTrainingMangerLMS.LiveCourse');
    import('TheTrainingMangerLMS.OnlineCourseProduct');
    import('TheTrainingMangerLMS.LiveCourse.LiveCourseEventProduct');

    import('TheTrainingMangerLMS.ExternalCertification');
    import('TheTrainingMangerLMS.TestAdminInterface.ExternalCertification_Testing');

    import('TheTrainingMangerLMS.InternalCertification');
    import('TheTrainingMangerLMS.TestAdminInterface.InternalCertification_Testing');

    import('TheTrainingMangerLMS.Pathway');
    import('TheTrainingMangerLMS.TestAdminInterface.Pathway_Testing');

    import('TheTrainingMangerLMS.Trainer');
    import('TheTrainingMangerLMS.TrainingCompany');

// Actions and Hooks
    import('TheTrainingMangerLMS.ActionMap');

    import('TheTrainingMangerLMS.Utility');
// Filters/Content
    import('TheTrainingMangerLMS.Content.MyAccount');

    import('TheTrainingMangerLMS.Content.CourseDetails');
    //

    import('TheTrainingMangerLMS.Content.CourseList');

    /**
     *
     * Main Class
     *
     */
    final class TheTrainingMangerLMS
    {
        private static $instance;  // plugin instance
        private $internal;         // internal working variables

        /**
         * Main TheTrainingMangerLMS Instance
         *
         * Insures that only one instance of the plugin exists in memory.
         *
         * @return TheTrainingMangerLMS instance
         */
        public static function instance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new TheTrainingMangerLMS;
                self::$instance->setup_fields();
                self::$instance->setup_actions();
                self::$instance->setup_filters();
            }
            return self::$instance;
        }

        /**
         * Prevent TheTrainingMangerLMS from being loaded more than once.
         *
         */
        private function __construct()
        { /* Do nothing here */
        }

        /**
         * Prevent TheTrainingMangerLMS from being cloned
         *
         */
        public function __clone()
        {
            _doing_it_wrong(__FUNCTION__, __('Clone not allowed', 'TheTrainingMangerLMS'), '2.1');
        }

        /**
         * Prevent TheTrainingMangerLMS from being unserialized
         *
         */
        public function __wakeup()
        {
            _doing_it_wrong(__FUNCTION__, __('Wakeup not allowed', 'TheTrainingMangerLMS'), '2.1');
        }


        /**
         *
         * Set-up runtime values
         *
         */
        private function setup_fields()
        {

            /** Versioning **/
            $this->version = ttp_lms_version();
            $this->prefix = ttp_lms_prefix();

            /** Paths **/
            $this->file = __FILE__;//WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . plugin_basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'TheTrainingMangerLMS.php';
            $this->basename = plugin_basename($this->file);
            $this->plugin_dir = plugin_dir_path($this->file);
            $this->plugin_url = plugin_dir_url($this->file);

            if (is_admin()) {
                import('TheTrainingMangerLMS.Admin');
                $this->admin = new \TheTrainingMangerLMS\Admin();
            }

        }

        /**
         *
         * Setup the default hooks and actions
         *
         */
        private function setup_actions()
        {
            // If the action sequence gets more complicated consider adding an ActionMap;
            add_action('init', array($this, 'initialize'), 0);
            add_action('user_register', array($this, 'register_users'), 10, 1);
            add_action('wp_trash_post', array($this, 'trash_post'), 10);
            // these should probably be moved to a Taxonomy related module or class
            // these could potentially be deprecated in the future, in that case
            // switch to created_taxonomy...
            add_action('created_category', array($this, 'category_added'), 10, 1);
            add_action('edited_category', array($this, 'category_added'), 10, 1);

            add_action("create_object_TheTrainingMangerLMS\\TrainingCompany", array($this, 'add_url_path_name'));
            add_action("create_object_TheTrainingMangerLMS\\OnlineCourse", array($this, 'add_product_association'));
            add_action("update_object_TheTrainingMangerLMS\\OnlineCourse", array($this, 'update_online_product_association'), 10, 2);
            add_action("update_object_TheTrainingMangerLMS\\LiveCourse", array($this, 'update_live_product_association'), 10, 2);

            if (!is_admin()) {
                add_action('wp', array($this, 'route_request'));
            }
            add_action('wp_loaded', array('TheTrainingMangerLMS\Install', 'update_rewrite_rules'), 0);
        }

        public function route_request()
        {
            // routing table
            if (is_page(TheTrainingMangerLMS\Constants::AccountPage)) {
                \TheTrainingMangerLMS\Content\MyAccount::route();

                //} else if (is_post_type_archive(ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::AccountPage))) {
                //\TheTrainingMangerLMS\Content\CourseList::route();


            } else if (is_singular(ttp_lms_post_prefix(\TheTrainingMangerLMS\Constants::CoursePostType))) {
                \TheTrainingMangerLMS\Content\CourseDetails::route();

            }
        }

        public function setup_filters()
        {

            if (!is_admin())
                add_filter('request', array($this, 'remap_category_requests'), 10, 1);
        }

        public function initialize()
        {
            TheTrainingMangerLMS\Install::upgrade();
            TheTrainingMangerLMS\Install::validateInstall();
            // PH: load settings
            $this->register_post_types();
            // PH: $this->register_statuses();
            // rewrite tags/permastructs/endpoints/rules
            $this->add_rewrite_rules();
        }

        /**
         * Set-up rewrite rules
         *
         */
        public function add_rewrite_rules()
        {
            // tags
            add_rewrite_tag("%area%", '([^/]*)');
            add_rewrite_tag("%course%", '([^/]*)');
            // permastruct
            // add_permastruct('my_account_section', '/' . TheTrainingMangerLMS\Constants::AccountPage . '/%my_account_section%', array( 'with_front' => true, 'ep_mask' => EP_PAGES ));
            // endpoints
            // rules (?:/course/([^/]*))?/?$
            add_rewrite_rule('^' . TheTrainingMangerLMS\Constants::AccountPage . '/([^/]*)(/course/([^/]*))?/?$', 'index.php?pagename=' . TheTrainingMangerLMS\Constants::AccountPage . '&area=$matches[1]&course=$matches[3]', 'top');
            add_rewrite_endpoint('course', EP_PAGES);
        }

        /**
         * Setup custom post types
         *
         */
        public function register_post_types()
        {
            /** Course **/
            $course_labels = array(
                'name' => __('Courses', 'ttp-lms'),
                'singular_name' => __('Course', 'ttp-lms'),
                'add_new' => __('Add New', 'ttp-lms'),
                'add_new_item' => __('Add New Course', 'ttp-lms'),
                'edit_item' => __('Edit Course', 'ttp-lms'),
                'new_item' => __('New Course', 'ttp-lms'),
                'all_items' => __('Courses', 'ttp-lms'),
                'view_item' => __('View Course', 'ttp-lms'),
                'search_items' => __('Search Courses', 'ttp-lms'),
                'not_found' => __('No Courses found', 'ttp-lms'),
                'not_found_in_trash' => __('No Courses found in Trash', 'ttp-lms'),
                'parent_item_colon' => '',
            );
            $cpt_options = Array(
                'label' => 'Course',
                'labels' => $course_labels,
                'supports' => array('title', 'editor', 'thumbnail', 'author', 'comments', 'revisions'),
                'taxonomies' => array('post_tag', 'category'),
                'public' => true,
                'rewrite' => Array('slug' => 'courses', 'with_front' => false),
                'show_ui' => false,
                'has_archive' => true,
                'show_in_nav_menus' => true,
                'hierarchical' => 'false'
            );
            register_post_type(ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::CoursePostType), $cpt_options);

            /** Lesson **/
            $lesson_labels = array(
                'name' => __('Lessons', 'ttp-lms'),
                'singular_name' => __('Lesson', 'ttp-lms'),
                'add_new' => __('Add New', 'ttp-lms'),
                'add_new_item' => __('Add New Lesson', 'ttp-lms'),
                'edit_item' => __('Edit Lesson', 'ttp-lms'),
                'new_item' => __('New Lesson', 'ttp-lms'),
                'all_items' => __('Lessons', 'ttp-lms'),
                'view_item' => __('View Lesson', 'ttp-lms'),
                'search_items' => __('Search Lessons', 'ttp-lms'),
                'not_found' => __('No Lessons found', 'ttp-lms'),
                'not_found_in_trash' => __('No Lessons found in Trash', 'ttp-lms'),
                'parent_item_colon' => '',
            );
            $cpt_options = Array(
                'label' => 'Lesson',
                'labels' => $lesson_labels,
                'supports' => array('title', 'thumbnail', 'editor', 'page-attributes', 'author', 'comments', 'revisions'),
                'taxonomies' => array('post_tag', 'category'),
                'public' => true,
                'rewrite' => Array('slug' => 'courses', 'with_front' => false),
                'show_ui' => false,
                'has_archive' => false,
                'show_in_nav_menus' => true
                // might be missing show_in_menu
            );
            register_post_type(ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::LessonPostType), $cpt_options);

            /** Lesson Topic **/
            $lesson_topic_labels = array(
                'name' => __('Topics', 'ttp-lms'),
                'singular_name' => __('Topic', 'ttp-lms'),
                'add_new' => __('Add New', 'ttp-lms'),
                'add_new_item' => __('Add New Topic', 'ttp-lms'),
                'edit_item' => __('Edit Topic', 'ttp-lms'),
                'new_item' => __('New Topic', 'ttp-lms'),
                'all_items' => __('Topics', 'ttp-lms'),
                'view_item' => __('View Topic', 'ttp-lms'),
                'search_items' => __('Search Topics', 'ttp-lms'),
                'not_found' => __('No Topics found', 'ttp-lms'),
                'not_found_in_trash' => __('No Topics found in Trash', 'ttp-lms'),
                'parent_item_colon' => '',
                'menu_name' => __('Topics', 'ttp-lms')
            );
            $cpt_options = Array(
                'label' => 'Lesson Topic',
                'labels' => $lesson_topic_labels,
                'supports' => array('title', 'thumbnail', 'editor', 'page-attributes', 'author', 'comments', 'revisions'),
                'public' => true,
                'rewrite' => Array('slug' => 'topic', 'with_front' => false),
                'show_ui' => false,
                'show_in_nav_menus' => false,
                'show_in_menu' => false,
                'has_archive' => false
            );
            register_post_type(ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::TopicPostType), $cpt_options);

            /** Company **/
            $company_labels = array(
                'name' => __('Companies', 'learndash'),
                'singular_name' => __('Company', 'learndash'),
                'add_new' => __('Add New', 'learndash'),
                'add_new_item' => __('Add New Company', 'learndash'),
                'edit_item' => __('Edit Company', 'learndash'),
                'new_item' => __('New Company', 'learndash'),
                'all_items' => __('Companies', 'learndash'),
                'view_item' => __('View Company', 'learndash'),
                'search_items' => __('Search Companies', 'learndash'),
                'not_found' => __('No Companies found', 'learndash'),
                'not_found_in_trash' => __('No Companies found in Trash', 'learndash'),
                'parent_item_colon' => '',
                'menu_name' => __('Companies', 'learndash')
            );
            $cpt_options = Array(
                'label' => 'Company',
                'labels' => $company_labels,
                'supports' => array('title'),
                'public' => true,
                'rewrite' => Array('slug' => 'company', 'with_front' => false),
                'show_ui' => true,
                'show_in_nav_menus' => false,
                'show_in_menu' => true, // Set to false when done testing stuff
                'has_archive' => false
            );
            register_post_type(ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::CompanyPostType), $cpt_options);

            /** Milestone **/
            $milestone_labels = array(
                'name' => __('Milestones', 'learndash'),
                'singular_name' => __('Milestone', 'learndash'),
                'add_new' => __('Add New', 'learndash'),
                'add_new_item' => __('Add New Milestone', 'learndash'),
                'edit_item' => __('Edit Milestone', 'learndash'),
                'new_item' => __('New Milestone', 'learndash'),
                'all_items' => __('Milestones', 'learndash'),
                'view_item' => __('View Milestone', 'learndash'),
                'search_items' => __('Search Milestones', 'learndash'),
                'not_found' => __('No Milestones found', 'learndash'),
                'not_found_in_trash' => __('No Milestones found in Trash', 'learndash'),
                'parent_item_colon' => '',
                'menu_name' => __('Milestones', 'learndash')
            );
            $cpt_options = Array(
                'label' => 'Milestone',
                'labels' => $milestone_labels,
                'supports' => array('title', 'editor', 'thumbnail'),
                'public' => true,
                'rewrite' => Array('slug' => 'milestone', 'with_front' => false),
                'show_ui' => true,
                'show_in_nav_menus' => false,
                'show_in_menu' => true, // Set to false when done testing stuff
                'has_archive' => false
            );
            register_post_type(ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::MilestonePostType), $cpt_options);

            /** Internal Certification **/
            $internal_certification_labels = array(
                'name' => __('Internal Certifications', 'learndash'),
                'singular_name' => __('Internal Certification', 'learndash'),
                'add_new' => __('Add New', 'learndash'),
                'add_new_item' => __('Add New Internal Certification', 'learndash'),
                'edit_item' => __('Edit Internal Certification', 'learndash'),
                'new_item' => __('New Internal Certification', 'learndash'),
                'all_items' => __('Internal Certifications', 'learndash'),
                'view_item' => __('View Internal Certification', 'learndash'),
                'search_items' => __('Search Internal Certifications', 'learndash'),
                'not_found' => __('No Internal Certifications found', 'learndash'),
                'not_found_in_trash' => __('No Internal Certifications found in Trash', 'learndash'),
                'parent_item_colon' => '',
                'menu_name' => __('Internal Certifications', 'learndash')
            );
            $cpt_options = Array(
                'label' => 'Internal Certification',
                'labels' => $internal_certification_labels,
                'supports' => array('title', 'editor'),
                'public' => true,
                'rewrite' => Array('slug' => 'certification', 'with_front' => false),
                'show_ui' => true,
                'show_in_nav_menus' => false,
                'show_in_menu' => true, // Set to false when done testing stuff
                'has_archive' => false
            );
            register_post_type(ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::InternalCertificationPostType), $cpt_options);

            /** External Certification **/
            $external_certification_labels = array(
                'name' => __('External Certifications', 'learndash'),
                'singular_name' => __('External Certification', 'learndash'),
                'add_new' => __('Add New', 'learndash'),
                'add_new_item' => __('Add New External Certification', 'learndash'),
                'edit_item' => __('Edit External Certification', 'learndash'),
                'new_item' => __('New External Certification', 'learndash'),
                'all_items' => __('External Certifications', 'learndash'),
                'view_item' => __('View External Certification', 'learndash'),
                'search_items' => __('Search External Certifications', 'learndash'),
                'not_found' => __('No External Certifications found', 'learndash'),
                'not_found_in_trash' => __('No External Certifications found in Trash', 'learndash'),
                'parent_item_colon' => '',
                'menu_name' => __('External Certifications', 'learndash')
            );
            $cpt_options = Array(
                'label' => 'External Certification',
                'labels' => $external_certification_labels,
                'supports' => array('title', 'editor'),
                'public' => true,
                'rewrite' => Array('slug' => 'certification', 'with_front' => false),
                'show_ui' => true,
                'show_in_nav_menus' => false,
                'show_in_menu' => true, // Set to false when done testing stuff
                'has_archive' => false
            );
            register_post_type(ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::ExternalCertificationPostType), $cpt_options);

            /** Quiz **/
            $quiz_topic_labels = array(
                'name' => __('Quizzes', 'ttp-lms'),
                'singular_name' => __('Quiz', 'ttp-lms'),
                'add_new' => __('New Quiz', 'ttp-lms'),
                'add_new_item' => __('Add New Quiz', 'ttp-lms'),
                'edit_item' => __('Edit Quiz', 'ttp-lms'),
                'new_item' => __('New Quiz', 'ttp-lms'),
                'all_items' => __('Quizzes', 'ttp-lms'),
                'view_item' => __('View Quiz', 'ttp-lms'),
                'search_items' => __('Search Quizzes', 'ttp-lms'),
                'not_found' => __('No Quizzes found', 'ttp-lms'),
                'not_found_in_trash' => __('No Quizzes found in Trash', 'ttp-lms'),
                'parent_item_colon' => '',
                'menu_name' => __('Quizzes', 'ttp-lms')
            );
            $cpt_options = Array(
                'label' => 'Quiz',
                'labels' => $quiz_topic_labels,
                'supports' => array('title', 'thumbnail', 'editor', 'page-attributes', 'author', 'comments', 'revisions'),
                'public' => true,
                'rewrite' => Array('slug' => 'topic', 'with_front' => false),
                'show_ui' => true,
                'show_in_nav_menus' => false,
                'show_in_menu' => false,
                'has_archive' => false
            );
            register_post_type(ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::QuizPostType), $cpt_options);

            /** Pathway **/
            $pathway_labels = array(
                'name' => __('Pathways', 'ttp-lms'),
                'singular_name' => __('Pathway', 'ttp-lms'),
                'add_new' => __('Add New', 'ttp-lms'),
                'add_new_item' => __('Add New Pathway', 'ttp-lms'),
                'edit_item' => __('Edit Pathway', 'ttp-lms'),
                'new_item' => __('New Pathway', 'ttp-lms'),
                'all_items' => __('Pathways', 'ttp-lms'),
                'view_item' => __('View Pathway', 'ttp-lms'),
                'search_items' => __('Search Pathways', 'ttp-lms'),
                'not_found' => __('No Pathways found', 'ttp-lms'),
                'not_found_in_trash' => __('No Pathways found in Trash', 'ttp-lms'),
                'parent_item_colon' => '',
            );
            $cpt_options = Array(
                'label' => 'Pathway',
                'labels' => $pathway_labels,
                'supports' => array('title', 'editor', 'thumbnail'),
                'public' => true,
                'rewrite' => Array('slug' => 'pathway', 'with_front' => false),
                'show_ui' => true,
                'has_archive' => true,
                'show_in_nav_menus' => true,
                'hierarchical' => 'false'
            );
            register_post_type(ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::PathwayPostType), $cpt_options);

            /** Training Company **/
            $training_company_labels = array(
                'name' => __('Training Companies', 'ttp-lms'),
                'singular_name' => __('Training Company', 'ttp-lms'),
                'add_new' => __('Add New', 'ttp-lms'),
                'add_new_item' => __('Add New Training Company', 'ttp-lms'),
                'edit_item' => __('Edit Training Company', 'ttp-lms'),
                'new_item' => __('New Training Company', 'ttp-lms'),
                'all_items' => __('Training Companies', 'ttp-lms'),
                'view_item' => __('View Training Company', 'ttp-lms'),
                'search_items' => __('Search Training Companies', 'ttp-lms'),
                'not_found' => __('No Training Companies found', 'ttp-lms'),
                'not_found_in_trash' => __('No Training Companies found in Trash', 'ttp-lms'),
                'parent_item_colon' => '',
            );
            $cpt_options = Array(
                'label' => 'Training Company',
                'labels' => $training_company_labels,
                'supports' => array('title', 'editor', 'thumbnail'),
                'public' => true,
                'rewrite' => Array('slug' => 'pathway', 'with_front' => false),
                'show_ui' => true,
                'has_archive' => true,
                'show_in_nav_menus' => true,
                'hierarchical' => 'false'
            );
            register_post_type(ttp_lms_post_prefix(TheTrainingMangerLMS\Constants::TrainingCompanyPostType), $cpt_options);


        }

        public function register_users($user_id)
        {
            TheTrainingMangerLMS\User::addMetaFields($user_id);
        }

        /**
         * Redirect requests for categories to page requests using the category slug and
         * change page requests for page names matching category slugs so that the category
         * template is served instead.
         */
        public function remap_category_requests($wp_query)
        {
            if (array_key_exists('page', $wp_query) && (array_key_exists('name', $wp_query)
                    || array_key_exists('pagename', $wp_query))
            ) {
                $name = $wp_query[array_key_exists('name', $wp_query) ? 'name' : 'pagename'];
                if (get_term_by('slug', $name, 'category')) {
                    return array('category_name' => $name);
                }
            } else if (array_key_exists('category_name', $wp_query)) {
                $url = site_url('/' . $wp_query['category_name']);
                wp_safe_redirect($url);
                exit();
            }
            return $wp_query;
        }

        public function trash_post($post_id)
        {
            $post_type = get_post_type($post_id);
            $post_status = get_post_status($post_id);
            if ('ttp-lms-company' == $post_type && in_array($post_status, array('publish', 'draft', 'future'))) {
                $company_name = get_the_title($post_id);
                $company = new \TheTrainingMangerLMS\Company($company_name, false, false, false);
                /*
                 * We need to specify the prefix here as a "bulk move to trach action"
                 * will not find the post ID using get_the_ID() in the constructor as common
                 * actions would
                 */
                $company->group_prefix = $post_id . "_";
                $company->delete_company();
            }
        }

        /**
         * Move the ACF url short name field value to the category slug field when a category
         * is created or edited.
         *
         * @param int $term_id The taxonomy id
         * @param int $tt_id The taxonomy term id
         */
        public function category_added($term_id, $tt_id)
        {
            // get url_short_name from acf field (located under options)
            $shortname = get_field('url_short_name', 'category_' . $term_id);
            // update the slug field with url_short_name
            global $wpdb;
            $wpdb->update($wpdb->terms, array('slug' => $shortname), array('term_id' => $term_id));
        }

        public function add_product_association($object)
        {
            $product = \TheTrainingMangerLMS\OnlineCourseProduct::create(
                array(
                    'virtual' => 'yes',
                    'title' => $object->getTitle(),
                    'price' => $object->getRegularPrice(),
                    'regular_price' => $object->getRegularPrice(),
                    'sale_price' => $object->getSalePrice(),
                    'featured' => ($object->isFeatured() ? 'yes' : 'no'),
                    'course_id' => $object->ID(),
                )
            );
        }

        public function update_online_product_association($object, $updates)
        {
            if (array_key_exists('title', $updates)) {
                $product = $object->getAssociatedProduct();
                if (is_null($product))
                    throw RuntimeException("Unable to retrieve product for course (" . $object->ID() . ").");
                $product->setTitle($updates['title']);
            }
        }

        public function update_live_product_association($object, $updates)
        {
            if (array_key_exists('events', $updates)) {
                // we have an array of events; find any of the events that are missing their Product association
                $events = explode(',', \TheTrainingMangerLMS\Utility::returnIDIfHasAssociatedProduct('_event_id', $updates['events']));
                $events = array_diff($updates['events'], $events);
                foreach ($events as $event) {
                    $product = \TheTrainingMangerLMS\LiveCourse\LiveCourseEventProduct::create(
                        array(
                            'virtual' => 'yes',
                            'title' => $object->getTitle(),
                            'price' => $object->getDefaultEventPrice(),
                            'regular_price' => $object->getDefaultEventPrice(),
                            'sale_price' => $object->getDefaultEventPrice(),
                            'featured' => ($object->isFeatured() ? 'yes' : 'no'),
                            'event_id' => $event,
                        )
                    );
                }
            }
            if (array_key_exists('title', $updates)) {
                // get event's associated with the course
                $events = $object->getEvents();
                foreach ($events as $event) {
                    $product = $event->getAssociatedProduct();
                    if (is_null($product == null))
                        throw RuntimeException("Unable to retrieve product for event (" . $event->ID() . ").");
                    $product->setTitle($updates['title']);
                }
            }
        }
    }

endif; // class_exists check
