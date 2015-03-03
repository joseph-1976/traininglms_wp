jQuery(document).ready((function ($) {
    return function () {
        $("li").on("click", function (e) {
            e.preventDefault();
            $(this).siblings("li").find('a').css("font-weight", "normal");
            $(this).find('a').css("font-weight", "bold");

        });
    };

})(jQuery));
/** Collapse Left Side Bar
 * Simple code to show/hide sidebar and respective elements.
 *
 * */

function collapseSidebar() {

    jQuery("#sidebar-left").hide();
    jQuery(".collapse-btn").hide();
    jQuery(".expand-btn").show();
    jQuery("#sidebar-left-collapsed").show();
    jQuery('#sidebar-middle').css({"width": '75%'});
    jQuery('#sidebar-middle-dnl').css({"width": '75%'});
}
function expandSidebar() {

    jQuery("#sidebar-left").show();
    jQuery("#sidebar-left-collapsed").hide();
    jQuery(".collapse-btn").show();
    jQuery(".expand-btn").hide();
    jQuery('#sidebar-middle').css({"width": '65%'});
}
function LoadDetails() {


    jQuery('#sidebar-middle').show();
    jQuery('#sidebar-middle-dnl').hide();
    jQuery('#sidebar-left-bottom').hide();
    jQuery('#sidebar-right').show();
    jQuery('#sidebar-right-bottom').hide();
}

function LoadDatesNLocations() {
    document.getElementById('map-canvas').style.display="block";
    initialize();

    jQuery('#sidebar-middle-dnl').show();
    jQuery('#sidebar-middle').hide();
    jQuery('#sidebar-right').hide();
    jQuery('#sidebar-right-bottom').show();

}
/**
 *  Ajax functions to add items to cart within POPUP
 * This ajax works in tandem with the WC Form Submission class and watches for $_REQUEST variables add-to-cart
 * and quantity.
 * @param p_id : wc product item ID , quantity : quantity of item. Both are passed in URL
 */
function keepshoppingPU(p_id) {

    var quantity = jQuery("input[name='quantity-popup']").val();
    jQuery.ajax({
        type: 'GET',
        url: '/tp/?post_type=product&add-to-cart=' + p_id + '&quantity=' + quantity,

        beforeSend: function () {
            jQuery("#purchase-seats").find('.indicator').show();
        },
        complete: function () {
            jQuery("#purchase-seats").find('.indicator').hide();
        },
        success: function (response, textStatus, jqXHR) {
            window.location.reload(true);
        }
    });

}
function checkoutPU(p_id) {
    var quantity = jQuery("input[name='quantity-popup']").val();
    jQuery.ajax({
        type: 'GET',
        url: '/tp/?post_type=product&add-to-cart=' + p_id + '&quantity=' + quantity,
        beforeSend: function () {
            jQuery("#purchase-seats").find('.indicator').show();
        },
        complete: function () {
            jQuery("#purchase-seats").find('.indicator').hide();
        },
        success: function (response, textStatus, jqXHR) {
            window.location.href = "/tp/cart";
        }
    });

}
function closeFB() {

    jQuery.fancybox.close();

}
//Listen for quantity field(s) to change and auto fill the other
jQuery("input[name='quantity-popup']").bind("change", function () {
    jQuery("input[name='quantity']").val(jQuery("input[name='quantity-popup']").val());

});
jQuery("input[name='quantity']").bind("change", function () {
    jQuery("input[name='quantity-popup']").val(jQuery("input[name='quantity']").val());
});

