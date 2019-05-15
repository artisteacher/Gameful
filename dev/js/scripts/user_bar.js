jQuery( document ).ready( function() {
    jQuery("#go_user_bar_inner").fadeIn();

    var user_bar_width = jQuery('#go_user_bar_inner ').width() + 40;
    var user_bar_width_1 = user_bar_width + 1;

    //sets the media queries based on the current width of the user bar
    console.log("UBW" + user_bar_width);
    document.querySelector('style').textContent +=
        "@media screen and (max-width:" + user_bar_width_1 + "px) { #go_user_bar .narrow_content  { display: block !important; } " +
        "#go_user_bar .wide_content {display: none !important;} " +
        "#go_user_bar {height: 45px !important;} " +
        "#go_user_bar .go_player_bar_text {display: none !important;}" +
        ".admin-bar #go_user_bar { top: 46px;}  " +
        "body{margin-top: 45px !important;} " +
        ".userbar_dropdown-content {top: 45px !important;}}";

    document.querySelector('style').textContent +=
        "@media screen and (min-width:" + user_bar_width + "px) { body{margin-top: 55px !important;}}";
});

/*
jQuery(window).on('load', function() {
    var user_bar_width = jQuery('#go_user_bar_inner ').width();
    var user_bar_width_1 = user_bar_width + 1;
    //change the media queries if the default wordpress header has a sticky setting
    var top = jQuery('#main-header').css('top');
    console.log(top);
    if (typeof (top) !== 'undefined') {
        var new_top = '85px !important';
        console.log(new_top);
        jQuery('#main-header').css('top', 85);
        //jQuery('#main-header').css('display', 'none');
        document.querySelector('style').textContent +=
            "@media screen and (min-width:" + user_bar_width + "px) { #main-header{top:" + new_top + "px !important;}}";
    }
});

jQuery( window ).resize(function() {
    var user_bar_width = jQuery('#go_user_bar_inner ').width();
    var user_bar_width_1 = user_bar_width + 1;
    //change the media queries if the default wordpress header has a sticky setting
    var top = jQuery('#main-header').css('top');
    console.log(top);
    if (typeof (top) !== 'undefined') {
        var new_top = '85px !important';
        console.log(new_top);
        jQuery('#main-header').css('top', 85);
        //jQuery('#main-header').css('display', 'none');
        document.querySelector('style').textContent +=
            "@media screen and (min-width:" + user_bar_width + "px) { #main-header{top:" + new_top + "px !important;}}";
    }
});

*/


