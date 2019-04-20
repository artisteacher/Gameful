
//jQuery(window).bind("load", function() {
jQuery( document ).ready( function() {
    console.log("jQuery is loaded2");
    go_load_daterangepicker('reader');


    go_make_select2_filter('user_go_sections', 'section', false);
    go_make_select2_filter('user_go_groups', 'group', false);
    go_make_select2_filter('go_badges', 'badge', false);

    jQuery(".go_reader_input").change(function() {
        console.log("go_activate_apply_filters1");
        go_activate_apply_filters();
    });



    jQuery('#go_clipboard_user_go_sections_select, #go_clipboard_user_go_groups_select, #go_clipboard_go_badges_select, #go_task_select, #go_store_item_select').on('select2:select', function (e) {
        // Do something
        jQuery('.go_update_clipboard').addClass("bluepulse");
        jQuery('.go_update_clipboard').html('<span class="ui-button-text">Apply Filters<i class="fa fa-filter" aria-hidden="true"></i></span>');
    });

    jQuery('.go_reset_clipboard').on("click", function () {
        jQuery('#datepicker_clipboard span').html("");
        jQuery('#go_clipboard_user_go_sections_select, #go_clipboard_user_go_groups_select, #go_clipboard_go_badges_select, #go_task_select, #go_store_item_select').val(null).trigger('change');
        jQuery('.go_update_clipboard').addClass("bluepulse");
        jQuery('.go_update_clipboard').html('<span class="ui-button-text">Apply Filters<i class="fa fa-filter" aria-hidden="true"></i></span>');
    });

    go_setup_reset_filter_button(true);

    //add task select2
    go_make_select2_cpt('#go_task_select', 'tasks');

    //update button--set this table to update
    jQuery('.go_update_clipboard').prop('onclick',null).off('click');//unbind click
    jQuery('.go_update_clipboard').one("click", function () {
        go_reader_update();
    });

    go_reader_activate_buttons();

    //set the datepicker to clear
    jQuery('#go_datepicker_container').html('<div id="go_datepicker_clipboard"><i class="fa fa-calendar" style="float: left;"></i><span id="go_datepicker"></span> <i id="go_reset_datepicker" class=""select2-selection__clear><b> × </b></i><i class="fa fa-caret-down"></i></div>');
    //jQuery('#go_datepicker_clipboard span').html('');
    jQuery('#go_reset_datepicker').hide();
    jQuery('#go_datepicker_container').one("click", function (){
        //console.log("hi there one");
        go_load_daterangepicker('clear');
        jQuery('#go_reset_datepicker').show();
        go_daterange_clear();
        go_activate_apply_filters();
    });
    //END

    //get localstorage data and set fields
    /*




    var date = localStorage.getItem('go_reader_date')
    if (date){
        jQuery('#go_datepicker_clipboard span').html(date)
    }
    if (localStorage.getItem('go_reader_unread') == 'true'){
        jQuery('#go_reader_unread').prop('checked', true);
    }

    //console.log("Read_var: " + localStorage.getItem('go_reader_read'));
    if (localStorage.getItem('go_reader_read') == 'true'){
        jQuery('#go_reader_read').prop('checked', true);
    }

    if (localStorage.getItem('go_reader_reset') == 'true'){
        jQuery('#go_reader_reset').prop('checked', true);
    }

    if (localStorage.getItem('go_reader_trash') == 'true'){
        jQuery('#go_reader_trash').prop('checked', true);
    }
    if (localStorage.getItem('go_reader_order') == 'true'){
        jQuery('#go_reader_draft').prop('checked', true);
    }

    if (localStorage.getItem('go_reader_draft') == 'true'){
        jQuery('#go_reader_draft').prop('checked', true);
    }
    */


    //go_reader_update(true);

});

function go_reader_update() {
    console.log("update reader");
    //document.getElementById("loader_container").style.display = "block";
    jQuery('#loader_container').show();
    jQuery('#go_posts_wrapper').hide();
    //if(!first) {
    //    go_save_clipboard_filters();
    //}
    jQuery('.go_update_clipboard').removeClass("bluepulse");
    jQuery('.go_update_clipboard').html('<span class="ui-button-text">Refresh Data <span class="dashicons dashicons-update" style="vertical-align: center;"></span></span>');
    jQuery('.go_update_clipboard').prop('onclick',null).off('click');//unbind click
    jQuery('.go_update_clipboard').one("click", function () {
        go_reader_update();
    });

    var date = jQuery('#go_datepicker_clipboard span').html();
    var section = jQuery('#go_clipboard_user_go_sections_select').val();
    var group = jQuery('#go_clipboard_user_go_groups_select').val();
    var badge = jQuery('#go_clipboard_go_badges_select').val();
    var tasks = jQuery("#go_task_select").val();
    var unread = jQuery('#go_reader_unread').prop('checked');
    var read = jQuery('#go_reader_read').prop('checked');
    var reset = jQuery('#go_reader_reset').prop('checked');
    var trash = jQuery('#go_reader_trash').prop('checked');
    var draft = jQuery('#go_reader_draft').prop('checked');
    var order = jQuery("input[name='go_reader_order']:checked").val();
    var limit = jQuery('#go_posts_num').val();
    //console.log("unread:" + unread);

    var nonce = GO_EVERY_PAGE_DATA.nonces.go_filter_reader;
    //console.log("refresh" + nonce);
    //console.log("stats");
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'post',
        data: {
            _ajax_nonce: nonce,
            action: 'go_filter_reader',
            date: date,
            section: section,
            group: group,
            badge: badge,
            tasks: tasks,
            unread: unread,
            read: read,
            reset: reset,
            trash: trash,
            draft: draft,
            order: order,
            limit: limit
        },
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            jQuery('#loader_container').hide();
            jQuery('#go_posts_wrapper').show();
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
        },
        success: function( res ) {
            //console.log("success: " + res);
            if (-1 !== res) {
                jQuery('#go_posts_wrapper').html(res);

                go_reader_activate_buttons();

                //document.getElementById("loader_container").style.display = "none";
                jQuery('#loader_container').hide();
                jQuery('#go_posts_wrapper').show();


            }
        }
    });


}

function go_reader_activate_buttons(){
    jQuery(".go_blog_favorite").click(function() {
        go_blog_favorite(this);
    });

    jQuery(".go_blog_trash").one("click", function (e) {
        go_blog_trash(this);
    });

    jQuery(".go_reset_task_clipboard").one("click", function(){
        go_messages_opener( this.getAttribute('data-uid'), this.getAttribute('data-task'), 'reset_stage', this );
    });

    tippy('.tooltip', {
        delay: 0,
        arrow: true,
        arrowType: 'round',
        size: 'large',
        duration: 300,
        animation: 'scale',
        zIndex: 999999
    });

    jQuery("#go_mark_all_read").one("click", function(){
        var post_ids = jQuery('#go_mark_all_read').data('post_ids')
        go_reader_bulk_read( post_ids );

    });

    jQuery( ".feedback_accordion" ).accordion({
        collapsible: true,
        active: false
    });
}

function go_reader_bulk_read(post_ids){
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_reader_bulk_read;
    //console.log("refresh" + nonce);
    //console.log("stats");
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'post',
        data: {
            _ajax_nonce: nonce,
            action: 'go_reader_bulk_read',
            post_ids: post_ids
        },
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
        },
        success: function( res ) {

            go_reader_update();
        }
    });
}

