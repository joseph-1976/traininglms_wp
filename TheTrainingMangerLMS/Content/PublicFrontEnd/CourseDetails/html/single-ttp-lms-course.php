<?php

namespace TheTrainingMangerLMS\Content\CourseDetails\SideBarLinks;
use TheTrainingMangerLMS\Constants;
use TheTrainingMangerLMS\LiveCourse;

import('TheTrainingMangerLMS.Content.PublicFrontEnd.CourseDetails.SideBarLinks');
//import('TheTrainingMangerLMS.LiveCourse.LiveCourseEvent');
?>

<?php
get_header();

?>

<?php while (have_posts()) : the_post(); ?>


    <?php
    // Grab an instance from LiveCourse, and well....use it.
    $course = LiveCourse::instance(get_the_ID());

    // Need to check the Course type
    if (ttp_lms_course_get_type($course->ID()) == 'TheTrainingMangerLMS\LiveCourse') {

         //print_r($course->getUpcomingEventsAndProducts());
        //print_r($course->getUpcomingEvents());
        //Get count of events
        $events_count = $course->getSetNumberOfUpcomingEventsForLiveCourse();
        //Gather information to for events WITHOUT products
        $sidebar_right_data = array();
        foreach ($course->getUpcomingEvents() as $obj) {


            $sidebar_right_data[] = array(
                "date_start" => $obj->start_datetime,
                "date_end" => $obj->stop_datetime,
                //"id" => get_the_ID(),
                "location" => $obj->location

            );

        }
        //Gather information to for events WITH products
        foreach ($course->getUpcomingEventsAndProducts() as $obj_group) {

            foreach ($obj_group as $key => $obj) {

                switch ($key) {
                    case 'product':

                        //echo  \TheTrainingMangerLMS\Content\CourseDetails::getID();

                    case 'event':

                        break;
                }


            }
        }
    }

    // WP pages

    ?>
    <!-- Start Content
        ================================================== -->
    <div id="sidebar-container">
        <div class="collapse-btn"><a onClick="collapseSidebar();"><</a></div>
        <div class="expand-btn"><a onClick="expandSidebar();">></a></div>

        <?php
        // Not sure if page type is going to be needed. Will keep here for now.
        $page_type = '';
        $current_section = Constants::LiveEventSection;
        $title_left = get_the_title();
        $title_right = Constants::DatesAndLocations;
        $title_left_bottom_1 = Constants::DateRange;
        $title_left_bottom_2 = Constants::Location;

        $sidebar_content_left_top = \TheTrainingMangerLMS\Content\PublicFrontEnd\CourseDetails\SideBarLinks::render_left_top_sidebar_links($page_type, $current_section, $title_left);
        //$sidebar_content_left_bottom = \TheTrainingMangerLMS\Content\CourseDetails\SideBarLinks::render_left_bottom_sidebar_links($page_type, $current_section, $title_left_bottom_1, $title_left_bottom_2);
        $sidebar_content_right_top = \TheTrainingMangerLMS\Content\PublicFrontEnd\CourseDetails\SideBarLinks::render_right_top_sidebar_links($page_type, $current_section, $title_right, $sidebar_right_data);


        /*Side Bar Top Left Top
        ==================================================
        */
        echo $sidebar_content_left_top;
        //




        ?>
        <script type='text/javascript' src='http://localhost/tp/wp-content/plugins/ttp-lms/TheTrainingMangerLMS/Content/PublicFrontEnd/CourseDetails/js/fancybox/source/jquery.fancybox.js'></script>
        <div id="sidebar-middle-dnl">
            <h2>Dates And Locations (<?php echo $title_left;?>)</h2>

            <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>

            <script type="text/javascript">
                function initialize() {
                    var mapOptions = {
                        zoom:14,
                        center:new google.maps.LatLng(35.595058,-82.551487),
                        mapTypeId: google.maps.MapTypeId.ROADMAP

                    };

                    var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
                    marker = new google.maps.Marker({map: map,position: new google.maps.LatLng(35.595058, -82.551487)});
                    infowindow = new google.maps.InfoWindow({content:"<b>Event: Skateboarding 101</b><br/>Asheville<br/> North Carolina" });
                    google.maps.event.addListener(marker, "click", function(){infowindow.open(map,marker);});
                    infowindow.open(map,marker);
                }


            </script>

            <div id="map-canvas" style="display:none;width:100%; height:276px;"></div>


            <table class="sortable" cellpadding="0" cellspacing="0">
                <tr>

                    <th>Location</th>
                    <th>Event Date(s)</th>
                    <th>Price</th>
                    <th class="unsortable"
                    </th>
                </tr>
                <?php foreach($sidebar_right_data as $obj){?>
                <tr>

                    <td><?php echo $obj['location']->city . ', ' . $obj['location']->state;?></td>
                    <td>12/12/14 - 12/14/2014</td>
                    <td>$196</td>
                    <td>
                        <div class="enroll-now-btn-dnt"><a onCLick="initiateCheck()">Enroll Now</a></div>
                    </td>
                </tr>

                <?php }?>


            </table>

        </div>
        <div id="sidebar-middle">

            <h1><?php echo the_title() . " (" . $events_count . " events)"; ?></h1>
            Offered by: <a href="javascript:void();">New Age Group Ltd</a>
            <hr/>

            <div class="middle-left-header">Average Rating: <img
                    src="<?php echo plugin_dir_url(__FILE__) . 'images/rating.png'; ?>" alt=""></div>
            <script type="text/javascript">var switchTo5x = true;</script>
            <script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
            <script type="text/javascript">stLight.options({
                    publisher: "bc20167c-dbd5-4fe0-9c2c-573a293da0cd",
                    doNotHash: false,
                    shorten:   false,
                    doNotCopy: false,
                    hashAddressBar: false
                });</script>


            <div class="middle-right-header">
                <span class='st_sharethis_large' displayText='ShareThis'></span>
                <span class='st_facebook_large' displayText='Facebook' ></span>
                <span class='st_twitter_large' displayText='Tweet'></span>
                <span class='st_linkedin_large' displayText='LinkedIn'></span>
                <span class='st_pinterest_large' displayText='Pinterest'></span>
                <span class='st_email_large' displayText='Email'></span>
            </div>

            <div class="clearfix"></div>
            <hr/>
            <p><?php the_content();?></p>
           <h2> Course objectives:</h2>

            <ul>
                <li>Learn to skate better</li>
                <li>Learn to take a fall</li>
                <li>Skating Safety</li>
                <li>How to purchase a skateboard</li>


            </ul>
            <?php global $woocommerce , $product;
            /*if (isset($woocommerce->cart)) {
                $cart = $woocommerce->cart;
                echo sizeof( $cart->get_cart());
            }*/ ?>

            </p>
        </div>


        <?php
        /*Side Bar Top Right
        ==================================================
        */
        echo $sidebar_content_right_top; ?>



        <div style="clear:left;"></div>

    </div>


    <div id="force-login-register" style="display:none;">
        <p>To make a purchase, you must first Login or create an account. Please choose an option below.</p>
        <div id="popup-login" class="popup popup-login">
            <h2>Login</h2>
            <hr />
            <form class="" role="form">
                <div class="field">
                    <label for="login_user_email" class="sr-only"><?php echo __( 'Email' ); ?></label>
                    <input type="email" name="user_email" id="login_user_email" class="user_email" value="" placeholder="Email" />
                </div>
                <div class="field">
                    <label for="login_user_pass" class="sr-only"><?php echo __( 'Password' ); ?></label>
                    <input type="password" name="user_pass" id="login_user_pass" class="user_pass" value="" placeholder="Password" />
                </div>

                <?php wp_nonce_field('hep_new_user','hep_new_user_nonce', true, true ); ?>
                <hr />
                <a onClick="closeFB();">Cancel</a> <input type="submit" id="submit_login_L" value="Login" />

                <p><a href="#popup-forgot-password" class="fancybox">Forgot your password? Click here &gt;&gt;</a></p>
            </form>
            <div class="indicator" style="display: none;">Please wait...</div>
            <div class="alert result-message"></div>

        </div>

        <div id="popup-signup" class="popup popup-signup">
            <h2>Create An Account</h2>
            <hr />
            <form class="" role="form">

                <div class="field">
                    <label for="registration_user_email" class="sr-only"><?php echo __( 'Email' ); ?></label>
                    <input type="email" name="user_email" id="registration_user_email" class="user_email" value="" placeholder="Email" />
                </div>
                <div class="field">
                    <label for="registration_user_pass" class="sr-only"><?php echo __( 'Password' ); ?></label>
                    <input type="password" name="user_pass" id="registration_user_pass" class="user_pass" value="" placeholder="Password" />
                </div>

                <?php wp_nonce_field('hep_new_user','hep_new_user_nonce', true, true ); ?>
                <hr />
                <a onClick="closeFB();">Cancel</a> <input type="submit" id="submit_register_R" value="Create My Account" />

                <p>By signing up, you agree to our <a href="">Terms of Use</a> and <a href="">Privacy Policy</a>.</p>
            </form>

            <div class="indicator" style="display: none;">Please wait...</div>
            <div class="alert result-message"></div>
        </div>
        <div style="clear:both;"></div>

    </div>
    <?php
    /**
     * TP -440 Purchase Seats Template
     * @param $product : wc product global variable for use
     */


    ?>

    <div id="purchase-seats" style="display:none;">
        <div id="purchase-container">
            <div class="purchase-seats-header-left">
                <h2><?php the_title(); ?></h2>
                <?php the_title(); ?> , Asheville , NC 01-26-2015
            </div>
            <div class="purchase-seats-header-right">
                <h2>PRICE $64.25</h2>
            </div>
            <div class="clearfix"></div>
            <hr/>
            <div class="purchase-seats-middle-left">

                <ul>
                    <li>Select # of Seats</li>

                    <li>
                        <!--TP-440 Added quantity selector as to not interfere with the element name of the main one.
                        Also added min/max quantity. Can be easily adjusted in the <input>-->
                        <div class="quantity buttons_added">

                            <input class="minus" type="button" value="-"></input>
                            <input class="input-text qty text" type="number" size="4" title="Qty" value="1"
                                   name="quantity-popup" max="1" min="1" step="1"></input>
                            <input class="plus" type="button" value="+"></input>

                        </div>
                    </li>
                </ul>
            </div>
            <div class="purchase-seats-middle-right">For:
                <select>
                    <option selected value="">Personal Use</option>
                    <option disabled value="">Company Use</option>
                </select>
            </div>
            <div class="clearfix"></div>
            <hr/>
            <!--TP-440 This select company class element is hidden and available for whenever it will be functional
            <div class="select-company" style="display:none;"><select></select></div>
            -->

            <div class="purchase-seats-btns">
                <a onClick="closeFB();">Cancel</a>
                <a onCLick="keepshoppingPU('<?php echo "147"; ?>');" class="single_add_to_cart_button button alt">Keep
                    Shopping</a>
                <a onCLick="checkoutPU('<?php echo "147"; ?>');" class="single_add_to_cart_button button alt">Checkout</a>

            </div>
            <div class="indicator" style="display: none;">Please wait...</div>
        </div>

    </div>
    <!-- End Content
    ================================================== -->
<?php endwhile; ?>
    <script>

        /** TP-440 User Prompted to create new account as part of checkout process
         * Let's check login session
         * */

        function initiateCheck(id) {
            <?php  if( is_user_logged_in() ){?>
            jQuery(document).ready(function ($) {
                $.fancybox({

                    inline: true,
                    href: "#purchase-seats"
                });

            });

            <?php }else{?>

            jQuery(document).ready(function ($) {
                $.fancybox({

                    inline: true,
                    href: "#force-login-register"
                });
            });

            <?php }?>
        }

        /** Check if user just logged in or registered and if so, load the
         * purchase seats page pop up
         */

        <?php if(isset($_SESSION['pu_login'])){?>

        jQuery(document).ready(function ($) {
            $.fancybox({

                inline: true,
                href: "#purchase-seats"
            });

        });
        <?php unset($_SESSION['pu_login']); ?>
        <?php }?>
    </script>
<?php

get_footer();
?>