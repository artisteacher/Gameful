//this is needed on every front end page!
jQuery( document ).ready( function() {
    jQuery(".acf-image-uploader .acf-icon[data-name='edit']").remove();//removes edit button on avatar profile page--it doesn't work
    if(jQuery('#go_user_bar').length) {
        //jQuery("#go_user_bar_inner").fadeIn();

        var user_bar_width = jQuery('#go_user_bar_inner ').width() + 40;
        var user_bar_width_1 = user_bar_width + 1;

        //sets the media queries based on the current width of the user bar
        console.log("UBW" + user_bar_width);
        document.querySelector('style').textContent +=
            "@media screen and (max-width:" + user_bar_width_1 + "px) { #go_user_bar .narrow_content  { display: table-cell !important; } " +
            "#go_user_bar .wide_content {display: none !important;} " +
            "#go_user_bar {height: 78px !important;} " +
            "#go_user_bar .go_player_bar_text {display: none !important;}" +
            ".admin-bar #go_user_bar { top: 46px;}  " +
            "body{margin-top: 81px !important;} " +
            ".userbar_dropdown-content {top: 43px !important;}}";


        var height = GO_STYLES.playerbar_height;
        document.querySelector('style').textContent +=
            "@media screen and (min-width:" + user_bar_width + "px) { body{margin-top: " + height + "px !important;}}";


        jQuery('.userbar_dropdown_toggle.search').on('click', function () {
            console.log('show search');

            jQuery('.userbar_dropdown-content.search').toggle();
            jQuery('#go_admin_bar_task_search_input').focus();

            jQuery('body').on("click", function (evt) {
                console.log('body');
                console.log(evt.target.id);
                if (evt.target.id == "userbar_search") {
                    console.log("1");
                    return;
                }
                //For descendants of menu_content being clicked, remove this check if you do not want to put constraint on descendants.
                if (jQuery(evt.target).closest('#userbar_search').length) {
                    console.log("2");
                    return;
                }
                //Do processing of click event here for every element except with id menu_content
                console.log("3");
                jQuery('.userbar_dropdown-content.search').toggle();
                jQuery('body').off();
            });


        });
        jQuery('body').fadeIn(100);
        //jQuery('body').show();
    }else {


        jQuery('body').show();
    }
});