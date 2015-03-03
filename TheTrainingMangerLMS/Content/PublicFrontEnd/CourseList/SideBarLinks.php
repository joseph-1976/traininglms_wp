<?php
/**
 * Class for SideBarLinks
 */


namespace TheTrainingMangerLMS\Content\CourseDetails;
use \DateTime;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

import('TheTrainingMangerLMS.Constants');


final class SideBarLinks
{

    public function render_left_top_sidebar_links($page_type, $current_section, $title)
        /**
         * Function for top left sidebar
         * @param string $page_name (currently inactive)
         * @param string $current_section
         * @param string $title
         * @return $html
         */
    {
        $html = '';

        switch ($current_section) {
            case "single-ttp-lms";

                $html .= '<div id="sidebar-left-collapsed">';
                $html .= 'Button<br />';
                $html .= 'Button<br />';
                $html .= 'Button<br />';
                $html .= 'Button<br />';
                $html .= 'Button<br />';
                $html .= '</div>';
                $html .= '<div id="sidebar-left">';
                $html .= '<div class="sidebar-left-menu-links-container">';
                $html .= '<h2>' . $title . '</h2>';
                $html .= '<div class="see-all-courses"><a href="javascript:void();">< See all Courses/Events</a></div>';
                $html .= '<ul>';
                $html .= '<li><span class="bold"><a onClick="LoadDetails();">Details</a></span></li>';
                $html .= '<li><span><a onClick="LoadDatesNLocations();">Dates &amp; Locations</a></span></li>';
                $html .= '</ul>';
                $html .= '</div>';
                //Top Left Sidebar
                $html .= '<div id="sidebar-left-bottom">';
                $html .= '<div class="sidebar-left-menu-links-container">';
                $html .= '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">';
                $html .= '<script src="//code.jquery.com/jquery-1.10.2.js"></script>';
                $html .= '<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>';
                $html .= '<link rel="stylesheet" href="/resources/demos/style.css">';
                $html .='<script>
            $(function() {
                jQuery( "#datepicker1" ).datepicker();
                jQuery( "#datepicker2" ).datepicker();
            });
              </script>';
                $html .= '<h2>Date Range</h2>';
                $html .= '<strong>Start date is between:</strong><br />';
                $html .= '<input type="text" id="datepicker1">';
                $html .= '<hr />';
                $html .= '<input type="text" id="datepicker2">';
                $html .= '<h2>Location</h2>';
                $html .= '<strong>Zip Code</strong><br />';
                $html .= '<strong>Radius</strong><br />';

                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';

                break;

        }


        return $html;


    }
    private function render_left_bottom_sidebar_links($page_type, $current_section, $title1,$title2)
        /**
         * Function for top left sidebar
         * @param string $page_name (currently inactive)
         * @param string $current_section
         * @param string $title1
         * @param string $title2
         * @return $html
         */
    {

        $html = '';

        switch ($current_section) {
            case "single-ttp-lms";

                $html .= '<div id="sidebar-left-bottom">';
                $html .= '<div class="sidebar-left-menu-links-container">';
                $html .= '<h2>' . $title1 . '</h2>';
                $html .= '<h2>' . $title2 . '</h2>';
                /*$html .= '<div class="see-all-courses"><a href="javascript:void();">< See all Courses/Events</a></div>';
                $html .= '<ul>';
                $html .= '<li><span class="bold"><a onClick="LoadDetails();">Details</a></span></li>';
                $html .= '<li><span><a onClick="LoadDatesNLocations();">Dates &amp; Locations</a></span></li>';
                $html .= '</ul>';*/
                $html .= '</div>';
                $html .= '</div>';

                break;

        }


        return $html;


    }
    public function render_right_top_sidebar_links($page_type, $current_section, $title, $data)

        /**
         * Function for top right sidebar
         * @param string $page_name (currently inactive)
         * @param string $current_section
         * @param string $title
         * @param array $data (dates and info)
         * @return $html
         */
    {

        $html = '';

        switch ($current_section) {
            case "single-ttp-lms";

                $html .= '<div id="sidebar-right">';
                $html .= '<div class="sidebar-right-info">';
                $html .= '<h2>' . $title . '</h2>';

                foreach ($data as $obj) {

                    $date = date_create($obj['date_end']->date);
                    $timestamp = date_format($date, 'F d, Y');
                    //$event_id = $obj['id'];



                    $html .= '<div>';
                    $html .= '<div class="sidebar-right-info-left">';
                    $html .= '<strong>'.$obj['location']->city . ', ' . $obj['location']->state . '</strong><br />';
                    $html .= $timestamp;
                    $html .= '</div>';
                    $html .= '<div class="sidebar-right-info-right">';
                    $html .= '<div class="enroll-now-btn"><a onCLick="initiateCheck();">Enroll Now</a></div>';
                    //$html .= '<div class="enroll-now-btn"><a onCLick="initiateCheck(\''.$event_id.'\');">Enroll Now</a></div>';
                    $html .= '</div>';
                    $html .= '<div class="clearfix"></div>';
                    $html .= '<hr />';
                    $html .= '</div>';
                }

                $html .= '<p><a href="#">View all dates & locations</a></p>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '<div id="sidebar-right-bottom">';
                $html .= '<div class="sidebar-right-info">';
                $html .= '<h2>Future Block</h2>';


                $html .= 'Content<br><br>';
                $html .= 'Content<br><br>';
                $html .= 'Content<br><br>';
                $html .= 'Content<br><br>';
                $html .= '<hr />';
                $html .= '</div>';
                $html .= '</div>';

                break;

        }


        return $html;


    }
}
