
//jQuery(window).bind("load", function() {
jQuery( document ).ready( function() {

    if (typeof (IsReader) !== 'undefined') {

        //console.log("jQuery is loaded2");
        go_load_daterangepicker('reader');


        go_make_select2_filter('user_go_sections', 'section', false);
        go_make_select2_filter('user_go_groups', 'group', false);
        go_make_select2_filter('go_badges', 'badge', false);

        jQuery(".go_reader_input").change(function () {
            //console.log("go_activate_apply_filters1");
            go_activate_apply_filters();
        });

        jQuery('#go_read_printed_button').on("click", function () {
            console.log("clicked");
            go_reader_read_printed();
        });


        jQuery('#go_clipboard_user_go_sections_select, #go_clipboard_user_go_groups_select, #go_clipboard_go_badges_select, #go_task_select, #go_store_item_select').on('select2:select', function (e) {
            // Do something
            jQuery('.go_update_clipboard').addClass("bluepulse");
            jQuery('.go_update_clipboard').html('<span class="ui-button-text">Apply Filters<i class="fas fa-filter" aria-hidden="true"></i></span>');
        });

        jQuery('.go_reset_clipboard').on("click", function () {
            jQuery('#datepicker_clipboard span').html("");
            jQuery('#go_clipboard_user_go_sections_select, #go_clipboard_user_go_groups_select, #go_clipboard_go_badges_select, #go_task_select, #go_store_item_select').val(null).trigger('change');
            jQuery('.go_update_clipboard').addClass("bluepulse");
            jQuery('.go_update_clipboard').html('<span class="ui-button-text">Apply Filters<i class="fas fa-filter" aria-hidden="true"></i></span>');
        });

        go_setup_reset_filter_button(true);

        //add task select2
        go_make_select2_cpt('#go_task_select', 'tasks');

        //update button--set this table to update
        jQuery('.go_update_clipboard').prop('onclick', null).off('click');//unbind click
        jQuery('.go_update_clipboard').one("click", function () {
            go_reader_update();
        });

        go_reader_activate_buttons();

        //set the datepicker to clear
        jQuery('#go_datepicker_container').html('<div id="go_datepicker_clipboard"><i class="fas fa-calendar" style="float: left;"></i><span id="go_datepicker"></span> <i id="go_reset_datepicker" class=""select2-selection__clear><b> × </b></i><i class="fa fa-caret-down"></i></div>');
        //jQuery('#go_datepicker_clipboard span').html('');
        jQuery('#go_reset_datepicker').hide();
        jQuery('#go_datepicker_container').one("click", function () {
            //console.log("hi there one");
            go_load_daterangepicker('clear');
            jQuery('#go_reset_datepicker').show();
            go_daterange_clear();
            go_activate_apply_filters();
        });


    }



        //alert("go_num_posts");

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

function go_blog_revision(target){
    console.log('function go_blog_revision');
    let post_id = jQuery(target).attr('blog_post_id');
    let current_post_id = jQuery(target).closest('.go_blog_post_wrapper').data('postid');
    let nonce = GO_EVERY_PAGE_DATA.nonces.go_blog_revision;
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'post',
        data: {
            _ajax_nonce: nonce,
            action: 'go_blog_revision',
            post_id: post_id
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
            jQuery(target).one("click", function () {
                go_blog_revision(this);
            });
        },
        success: function( res ) {
            //console.log("success: " + res);
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            if (-1 !== res) {
                console.log('success');
                console.log(res);
                jQuery.featherlight(res, {afterContent: function(){
                        jQuery('.go_restore_revision').one("click", function () {
                            go_restore_revision(this);
                        });
                    }});

                jQuery(target).one("click", function () {
                    go_blog_revision(this);
                });

            }
        }
    });


}

function go_restore_revision(target){
    console.log('function go_restore_revision');
    let post_id = jQuery(target).data('post_id');
    let parent_id = jQuery(target).data('parent_id');
    let nonce = GO_EVERY_PAGE_DATA.nonces.go_restore_revision;
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'post',
        data: {
            _ajax_nonce: nonce,
            action: 'go_restore_revision',
            post_id: post_id,
            parent_id: parent_id
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
            jQuery(target).one("click", function () {
                go_blog_revision(this);
            });
        },
        success: function( res ) {
            console.log("success: " + res);
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            if (-1 !== res) {
                console.log('res');
                //close featherlight
                //get the container for previous target and replace it with the result
                let container = '.go_blog_post_wrapper_' + parent_id;
                //jQuery(container).hide();
                jQuery(container).replaceWith(res);
                go_reader_activate_buttons();
                jQuery.featherlight.close();


                swal.fire({//sw2 OK
                        text: "Your previous revision has been restored.",
                        type: 'success'
                    }
                );


            }
        }
    });


}

function go_reader_update() {
    //console.log("update reader");
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
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            //console.log("success: " + res);
            if (-1 !== res) {

                jQuery('#loader_container').hide();
                jQuery('#go_posts_wrapper').html(res).promise().done(function(){
                    //your callback logic / code here
                    jQuery('#go_posts_wrapper').show();
                    go_reader_activate_buttons();
                    go_loadmore_reader();
                });



                //document.getElementById("loader_container").style.display = "none";



            }
        }
    });


}

function go_reader_activate_buttons(){

    jQuery('.go_blog_revision').one("click", function () {
        go_blog_revision(this);
    });

    jQuery(".go_blog_favorite").off().click(function() {
        go_blog_favorite(this);
    });

    jQuery(".go_blog_trash").off().one("click", function (e) {
        go_blog_trash(this);
    });

    jQuery(".go_reset_task_clipboard").off().one("click", function(){
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

    jQuery("#go_mark_all_read").off().one("click", function(){
        go_reader_bulk_read( );

    });

    jQuery(".go_status_read_toggle").off().one("click", function(){
        go_mark_one_read_toggle(this);
    });

    jQuery( ".feedback_accordion" ).accordion({
        collapsible: true,
        active: false,
        heightStyle: "content"
    });

    jQuery("#go_num_posts").off().change(function() {
        go_num_posts();
    });

    jQuery(".go_send_feedback").off().one("click", function(){
        go_send_feedback(this);
    });


    jQuery('.go-acf-switch').off().click(function () {
        console.log("click");
        if (jQuery(this).hasClass('-on') == false) {
            jQuery(this).prev('input').prop('checked', true);
            jQuery(this).addClass('-on');
            jQuery(this).removeClass('-off');
        } else {
            jQuery(this).prev('input').prop('checked', false);
            jQuery(this).removeClass('-on');
            jQuery(this).addClass('-off');
        }
    });

    jQuery(".go_feedback_canned").off().on('change', function (e) {
        var optionSelected = jQuery("option:selected", this);
        go_feedback_canned(optionSelected);
    });
}

function go_num_posts(){
    jQuery('#loader_container').show();
    jQuery('#go_posts_wrapper').hide();
    //console.log("go_num_posts");

    const limit = jQuery('#go_num_posts').val();
    const query = jQuery('#go_num_posts').data('query');
    const where = jQuery('#go_num_posts').data('where');
    const order = jQuery('#go_num_posts').data('order');
    //const tQuery = jQuery('#go_num_posts').data('tQuery');
    const nonce = GO_EVERY_PAGE_DATA.nonces.go_num_posts;

    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'post',
        data: {
            _ajax_nonce: nonce,
            action: 'go_num_posts',
            query: query,
            query: query,
            where: where,
            order: order,
            //tQuery: tQuery,
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
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            //console.log("success: " + res);
            if (-1 !== res) {
                jQuery('#go_posts_wrapper').html(res).promise().done(function(){
                    //your callback logic / code here
                    jQuery('#loader_container').hide();
                    jQuery('#go_posts_wrapper').show("fast", function(){
                        go_reader_activate_buttons();
                        go_loadmore_reader();
                    });
                });;



                //document.getElementById("loader_container").style.display = "none";

            }
        }
    });

}

function go_reader_bulk_read(){
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_reader_bulk_read;

    const query = jQuery('#go_num_posts').data('query');
    const where = jQuery('#go_num_posts').data('where');
    const order = jQuery('#go_num_posts').data('order');
    //console.log("refresh" + nonce);
    //console.log("stats");
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'post',
        data: {
            _ajax_nonce: nonce,
            action: 'go_reader_bulk_read',
            query: query,
            where: where,
            order: order
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
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            go_reader_update();
        }
    });
}

function go_reader_read_printed(){
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_reader_read_printed;

    const postids = jQuery('.go_blog_post_wrapper').data('postid');
    const where = jQuery('#go_num_posts').data('where');
    const order = jQuery('#go_num_posts').data('order');
    //console.log("refresh" + nonce);
    console.log("postids");
    console.log(postids);
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'post',
        data: {
            _ajax_nonce: nonce,
            action: 'go_reader_read_printed',
            //query: query,
            where: where,
            order: order
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
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            go_reader_update();
        }
    });
}

function go_mark_one_read_toggle(target){
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_mark_one_read_toggle;

    const postid = jQuery(target).data('postid');

    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'post',
        data: {
            _ajax_nonce: nonce,
            action: 'go_mark_one_read_toggle',
            postid: postid
        },
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }

            jQuery(target).off().one("click", function(){
                go_mark_one_read_toggle(this);
            });

        },
        success: function( res ) {
            console.log(res);

            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            if ( -1 !== res ) {
                jQuery(".go_status_read_toggle").off().one("click", function () {
                    go_mark_one_read_toggle(this);
                });
                if (res === 'read') {
                    console.log(target);
                    jQuery(target).find('.fa-eye-slash').hide();
                    jQuery(target).find('.fa-eye').show();
                } else if (res === 'unread') {
                    jQuery(target).find('.fa-eye-slash').show();
                    jQuery(target).find('.fa-eye').hide();
                }
            }
        }
    });
}

function go_feedback_canned(target){

    console.log('go_feedback_canned');
    console.log(target);
    const title = jQuery(target).data('title');
    const message = jQuery(target).data('message');
    const toggle = jQuery(target).data('toggle');
    const percent = jQuery(target).data('percent');
    console.log(title);
    console.log(message);
    console.log(toggle);
    console.log(percent);


    jQuery(target).closest('.go_feedback_form').find('.go_title_input').val(title);
    //jQuery(target).closest('.go_feedback_form').find('.go_message_input').html($message);
    jQuery(target).closest('.go_feedback_form').find('.go_message_input').val(message);
    jQuery(target).closest('.go_feedback_form').find('.go_toggle_input').val(toggle);
    if (toggle){
        jQuery(target).closest('.go_feedback_form').find('.go-acf-switch').addClass('-on').removeClass('-off');
    }else{
        jQuery(target).closest('.go_feedback_form').find('.go-acf-switch').addClass('-off').removeClass('-on');
    }
    jQuery(target).closest('.go_feedback_form').find('.go_percent_input').val(percent);


}

function go_send_feedback(target) {
    console.log('go_send_feedback');
    console.log(target);
    var title = jQuery(target).closest('.go_feedback_input').find('.go_title_input').val();
    //title = go_stripslashes(title);
    var message = jQuery(target).closest('.go_feedback_input').find('.go_message_input').val();
    //message = go_stripslashes(message);
    var toggle = (jQuery(target).closest('.go_feedback_input').find('.go_feedback_toggle').siblings().hasClass("-on")) ? 1 : 0;
    var percent = jQuery(target).closest('.go_feedback_input').find('.feedback_percent_input').val();
    const post_id = jQuery(target).data('postid');

    // send data
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_send_feedback;
    var gotoSend = {
        action:"go_send_feedback",
        _ajax_nonce: nonce,
        //post_id: post_id,
        title: title,
        message: message,
        toggle: toggle,
        percent: percent,
        post_id: post_id


    };
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type:'POST',
        data: gotoSend,
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
        },
        success: function( results ) {

            let error = go_ajax_error_checker(results);
            if (error == 'true') return;

            // show success or error message
            console.log("send successful");
            Swal.fire(//sw2 OK
                'Success!',
                '',
                'success'
            );


        }
    });
}
