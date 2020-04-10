
//jQuery(window).bind("load", function() {
jQuery( document ).ready( function() {
    console.log("go_feedback is Ready.")
    if (typeof (IsReader) !== 'undefined') {
        //console.log("jQuery is loaded2");
        go_activate_reader();
    }
});

function go_activate_reader(is_single_stage = false){
    go_blog_new_posts();

    go_make_select2_filter('user_go_sections','reader', true);
    go_make_select2_filter('user_go_groups','reader', true);
    go_make_select2_filter('go_badges','reader', true);

    //add task select2
    go_make_select2_cpt('#go_task_select', 'tasks');

    go_setup_filter_buttons(true);

    if(is_single_stage){
        jQuery('.go_apply_filters').off().one("click", function () {
            go_reader_update();
        });
    }
    else {
        jQuery('.go_apply_filters').off().one("click", function () {
            go_reader_update();
        });
    }

    go_load_daterangepicker('go_activate_reader');
    //set the datepicker to clear
    jQuery('#go_datepicker_container').html('<div id="go_datepicker_clipboard"><i class="fas fa-calendar" style="float: left; line-height: 1.5em;"></i><span id="go_datepicker"></span> <i id="go_reset_datepicker" class=""select2-selection__clear><b> × </b></i><i class="fa fa-caret-down"></i></div>');
    //jQuery('#go_datepicker_clipboard span').html('');
    jQuery('#go_reset_datepicker').hide();

    jQuery('#go_datepicker_container').one("click", function () {
        //console.log("hi there one");
        go_load_daterangepicker('go_activate_reader');
        jQuery('#go_reset_datepicker').show();
        go_daterange_clear();
        go_highlight_apply_filters();//datapicker
    });

    jQuery("#go_num_posts, #go_cards_toggle").off().change(function() {
        go_num_posts();
    });

    jQuery("#go_show_all_feed").off().change(function() {
        go_reader_update();
    });
}

function go_reader_update(target, is_initial_single_stage = false) {
    console.log("go_reader_update");
    console.log(target);
    jQuery('.go_apply_filters').removeClass("bluepulse");

    var user_id = jQuery("#go_leaderboard_filters").data('user_id');
    var type = jQuery("#go_leaderboard_filters").data('type');
    //this can be coming from the reader, blog, quest_stage, or social feed (leaderboard)
    //for all types send the data needed for this type
    console.log("Get task ID");
    if(typeof jQuery(target).data('post_id') !== 'undefined') { //this was from a single quest or stage button
        console.log("Get task ID1");
        var tasks = jQuery(target).data('post_id');//there was a post id on the button data (single quest stage)
        var stage = jQuery(target).data('stage');
    }
    else{
        console.log("Get task ID2");
        var tasks = jQuery("#go_task_select").val();//the post id on the filter
        var stage = jQuery("#go_leaderboard_filters").data('stage');
        go_save_filters('reader');
    }


    jQuery('.go_apply_filters').html('<span class="ui-button-text">Refresh Data <span class="dashicons dashicons-update" style="vertical-align: center;"></span></span>');

    var date = jQuery('#go_datepicker_clipboard span').html();

    var published  = jQuery('#go_reader_published').prop('checked');
    var unread  = jQuery('#go_reader_unread').prop('checked');
    var read    = jQuery('#go_reader_read').prop('checked');
    var reset   = jQuery('#go_reader_reset').prop('checked');
    var trash   = jQuery('#go_reader_trash').prop('checked');
    var draft   = jQuery('#go_reader_draft').prop('checked');
    var order   = jQuery("input[name='go_reader_order']:checked").val();
    /*console.log("order:"+order);
    if (typeof order == 'undefined') {
        order = "DESC";
    }*/
    var limit = jQuery('#go_num_posts').val();

    if(is_initial_single_stage){
        var section = localStorage.getItem('user_go_sections');
        var group = localStorage.getItem('user_go_groups');
        var badge = localStorage.getItem('go_badges');
    }
    else {
        var section = jQuery('#go_reader_user_go_sections_select').val();
        var group = jQuery('#go_reader_user_go_groups_select').val();
        var badge = jQuery('#go_reader_go_badges_select').val();
    }


    var current_tab = jQuery("#social_tabs").find("[aria-selected='true']").attr('aria-controls');
    var show_all = jQuery('#go_show_all_feed').is(':checked');

    var nonce = GO_FRONTEND_DATA.nonces.go_filter_reader;
    var loader_html = go_loader_html('big');
    jQuery('#go_posts_wrapper').html(loader_html);
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'GET',
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_filter_reader',
            date: date,
            section: section,
            group: group,
            badge: badge,
            post_id: tasks,
            unread: unread,
            published: published,
            read: read,
            reset: reset,
            trash: trash,
            draft: draft,
            order: order,
            limit: limit,
            //is_single_stage: is_single_stage,
            is_initial_single_stage: is_initial_single_stage,
            stage: stage,
            current_tab: current_tab,
            show_all: show_all,
            type: type,
            user_id: user_id
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
            go_after_ajax();
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            //console.log("success: " + res);
            if (-1 !== res) {
                console.log("success");
                if (is_initial_single_stage) {
                    jQuery.featherlight(res,
                        {
                            variant: 'go_quest_reader_lightbox',
                            afterContent: function () {
                                go_activate_reader(true);
                            }
                        });
                }else {

                    jQuery('#go_posts_wrapper').html(res).promise().done(function () {
                        //your callback logic / code here
                        jQuery('#go_posts_wrapper').show();

                        if(type === "quest_stage"){
                            jQuery('.go_apply_filters').off().one("click", function () {
                                go_reader_update();
                            });
                        }
                        else {
                            jQuery('.go_apply_filters').off().one("click", function () {
                                go_reader_update();
                            });
                        }



                        jQuery([document.documentElement, document.body]).animate({
                            scrollTop: ((jQuery("#go_posts_wrapper").offset().top) -150)
                        }, 500);

                        go_blog_new_posts();

                    });
                }
            }
        }
    });
}

//gets blog post revision and places in a lightbox with restore button
function go_blog_revision(target){
    console.log('function go_blog_revision');
    let post_id = jQuery(target).attr('blog_post_id');
    let nonce = GO_EVERY_PAGE_DATA.nonces.go_blog_revision;
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'post',
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_blog_revision',
            post_id: post_id
        },
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            jQuery('#loader_container').remove();
            //jQuery('#go_posts_wrapper').show();
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
            jQuery(target).off().one("click", function () {
                go_blog_revision(this);
            });
        },
        success: function( res ) {
            go_after_ajax();
            jQuery(target).off().one("click", function () {
                go_blog_revision(this);
            });
            //console.log("success: " + res);
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            if (-1 !== res) {
                //console.log('success');
                //console.log(res);
                jQuery.featherlight(res, {afterContent: function(){
                    jQuery('.go_restore_revision').off().one("click", function () {
                        go_restore_revision(this);
                    });
                    go_blog_new_posts();
                }});
            }
        }
    });
}

//restores previous revision or autosave
//can load form or post as needed
function go_restore_revision(target){
    console.log('function go_restore_revision');
    go_enable_loading( target );
    let lightbox = false;
    let post_id = jQuery(target).data('post_id');
    let parent_id = jQuery(target).data('parent_id');
    let autosave = jQuery(target).data('autosave');
    let load_current = jQuery(target).data('load_current');
    let nonce = GO_EVERY_PAGE_DATA.nonces.go_restore_revision;
    if (jQuery(target).parents('.featherlight-content').length) {
        lightbox = true;
    }
    let form = jQuery(target).data('form');


    console.log("load_current"+load_current);

    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'post',
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_restore_revision',
            post_id: post_id,
            parent_id: parent_id,
            autosave: autosave,
            lightbox: lightbox,
            form: form,
            load_current: load_current,

        },
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            jQuery('#loader_container').remove();
            //jQuery('#go_posts_wrapper').show();
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
            jQuery(target).off().one("click", function () {
                go_blog_revision(this);
            });
        },
        success: function( res ) {
            go_after_ajax();
            //console.log("success:" + res);
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            if (-1 !== res) {
                //console.log('res');
                //close featherlight
                //get the container for previous target and replace it with the result
                if(form){
                    if(load_current == true){
                        var wrapper = '.go_check_autosave_' + post_id;
                    }
                    else {
                        var wrapper = '.go_check_autosave_' + parent_id;
                    }
                }
                else {
                    var wrapper = '.go_blog_post_wrapper_' + parent_id;
                    jQuery.featherlight.close();
                }
                console.log('wrap'+wrapper);
                //jQuery(container).hide();
                jQuery(wrapper).replaceWith(res);

                if(form == "true"){
                    go_blog_after_ajax();
                    go_activate_tinymce_on_task_change_stage('go_blog_post');
                }else {
                    go_blog_new_posts();
                }

                jQuery('#go_buttons').show();
                if(load_current != true) {
                    swal.fire({//sw2 OK
                            text: "Your previous revision has been restored.",
                            type: 'success'
                        }
                    );
                }
            }
        }
    });
}

//used by the view with cards toggle as well as the change count
function go_num_posts(){

    const limit = jQuery('#go_num_posts').val();
    const query = jQuery('#go_num_posts').data('query');
    const where = jQuery('#go_num_posts').data('where');
    const order = jQuery('#go_num_posts').data('order');

    const cards = jQuery('#go_cards_toggle').is(':checked');
    jQuery('#go_cards_toggle_wrapper').hide();

    //const tQuery = jQuery('#go_num_posts').data('tQuery');
    const nonce = GO_FRONTEND_DATA.nonces.go_num_posts;

    var loader_html = go_loader_html('big');
    jQuery('#go_posts_wrapper').html(loader_html);

    var current_tab = jQuery("#social_tabs").find("[aria-selected='true']").attr('aria-controls');
    var show_all = jQuery('#go_show_all_feed').is(':checked');

    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'GET',
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_num_posts',
            query: query,
            where: where,
            order: order,
            //tQuery: tQuery,
            limit: limit,
            cards: cards,
            current_tab: current_tab,
            show_all: show_all,
        },
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            jQuery('#loader_container').remove();
            //jQuery('#go_posts_wrapper').show();
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
            jQuery('#go_cards_toggle_wrapper').show();
        },
        success: function( res ) {
            go_after_ajax();


            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            //console.log("success: " + res);
            if (-1 !== res) {
                jQuery('#go_posts_wrapper').html(res).promise().done(function(){
                    //your callback logic / code here
                    jQuery('#go_posts_wrapper').show("fast", function(){
                        go_blog_new_posts();
                    });
                });
            }
            jQuery('#go_cards_toggle_wrapper').show();
        }
    });

}


//Maybe remove this
//and remove the info attache to go_num_posts
//mark all that match the filter as read
/*
function go_reader_bulk_read(target){
    console.log("go_reader_bulk_read");
    var nonce = GO_FRONTEND_DATA.nonces.go_reader_bulk_read;

    const query = jQuery('#go_num_posts').data('query');
    const where = jQuery('#go_num_posts').data('where');
    const order = jQuery('#go_num_posts').data('order');


    var loader_html = go_loader_html('big');
    jQuery('#go_posts_wrapper').html(loader_html);

    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'post',
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_reader_bulk_read',
            query: query,
            where: where,
            order: order
        },

        error: function(jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
        },
        success: function( res ) {
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;
            //jQuery(target).parent().append("<i class='fas fa-spinner fa-pulse'></i>");
            go_reader_update();
        }
    });
}*/

//mark all on the page as read
//MAYBE DELETE
function go_reader_read_printed(){
    console.log('go_reader_read_printed');
    var nonce = GO_FRONTEND_DATA.nonces.go_reader_read_printed;
    var postids = new Array();
    jQuery('.go_blog_post_wrapper').each(function( index ) {
        var post_id = jQuery( this ).data('postid') ;
        postids.push(post_id);
    });
    //console.log("refresh" + nonce);
    console.log("postids");
    console.log(postids);
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'post',
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_reader_read_printed',
            //query: query,
            postids: postids
        },
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('not logged in');
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
        },
        success: function( res ) {
            console.log('success');
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
            is_frontend: is_frontend,
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
                    jQuery('.go_blog_post_wrapper_' + postid).find('.fa-eye-slash').hide();
                    jQuery('.go_blog_post_wrapper_' + postid).find('.fa-eye').show();
                } else if (res === 'unread') {

                    jQuery('.go_blog_post_wrapper_' + postid).find('.fa-eye-slash').show();
                    jQuery('.go_blog_post_wrapper_' + postid).find('.fa-eye').hide();
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
    const radio = jQuery(target).data('radio');
    const toggle_assign = jQuery(target).data('toggle_assign');
    const xp = jQuery(target).data('xp');
    const gold = jQuery(target).data('gold');
    const health = jQuery(target).data('health');
    const toggle_percent = jQuery(target).data('toggle_percent');
    const percent = jQuery(target).data('percent');
    const post_id = jQuery(target).closest('.go_blog_post_wrapper').data('postid');
    console.log(title);
    console.log(message);
    console.log(radio);
    console.log(toggle_percent);
    console.log(toggle_assign);

    jQuery(target).closest('.go_feedback_form').find('.go_title_input').val(title);
    //jQuery(target).closest('.go_feedback_form').find('.go_message_input').html($message);

    tinyMCE.get('go_feedback_text_area_id_'+post_id).setContent(message);



    //summernote
    //jQuery(target).closest('.go_feedback_form').find('.go_message_input').val(message);//the val of th text area
    //jQuery(target).closest('.go_feedback_form').find('.note-editable').html(message);//the display in the WYSIWYG

    //jQuery(target).trigger('click');



    ///////////
    if (radio == 'percent') {
        jQuery(target).closest('.go_feedback_form').find('.loot_option_percent').trigger("change");
        jQuery(target).closest('.go_feedback_form').find('.loot_option_percent').prop("checked", true);

        jQuery(target).closest('.go_feedback_form').find('.go_toggle_input').val(toggle_percent);
        if (toggle_percent){
            jQuery(target).closest('.go_feedback_form').find('.go-acf-switch').addClass('-on').removeClass('-off');
        }else{
            jQuery(target).closest('.go_feedback_form').find('.go-acf-switch').addClass('-off').removeClass('-on');
        }
        jQuery(target).closest('.go_feedback_form').find('.go_percent_input').val(percent);
    }
    else if (radio == 'assign') {
        jQuery(target).closest('.go_feedback_form').find('.loot_option_assign').prop("checked", true);
        jQuery(target).closest('.go_feedback_form').find('.loot_option_assign').trigger("change");

        if (toggle_assign){
            jQuery(target).closest('.go_feedback_form').find('.go-acf-switch').addClass('-on').removeClass('-off');
        }else{
            jQuery(target).closest('.go_feedback_form').find('.go-acf-switch').addClass('-off').removeClass('-on');
        }
        jQuery(target).closest('.go_feedback_form').find('.go_messages_toggle_input').val(toggle_assign);
        jQuery(target).closest('.go_feedback_form').find('.go_messages_xp_input').val(xp);
        jQuery(target).closest('.go_feedback_form').find('.go_messages_gold_input').val(gold);
        jQuery(target).closest('.go_feedback_form').find('.go_messages_health_input').val(health);
    }
    else if (radio == 'none') {
        //jQuery(target).closest('.go_feedback_form').find('.loot_option_none').prop("checked", true);
        jQuery(target).closest('.go_feedback_form').find('.loot_option_none').trigger("click");
    }
}

function go_send_feedback(target) {
    console.log('go_send_feedback');
    console.log(target);
    var title = jQuery(target).closest('.go_feedback_input').find('.go_title_input').val();
    const post_id = jQuery(target).data('postid');
    //var message = jQuery(target).closest('.go_feedback_input').find('.go_message_input').val();
    //var post_id = jQuery(target).closest('.go_blog_post_wrapper').data('postid');
    console.log("postid: "+post_id);
    var message_id = 'go_feedback_text_area_id_'+ post_id;
    console.log("message_id: "+message_id);
    var message = go_tmce_getContent(message_id);
    // var radio =  jQuery('input[type=radio][name=loot_option]').val();
    var radio = jQuery(target).closest('.go_feedback_input').find('input[name=loot_option]:checked').val();
    var toggle_assign = (jQuery(target).closest('.go_feedback_input').find('.go_messages_toggle_input').siblings().hasClass("-on")) ? 1 : 0;
    var xp = jQuery(target).closest('.go_feedback_input').find('.go_messages_xp_input').val();
    var gold = jQuery(target).closest('.go_feedback_input').find('.go_messages_gold_input').val();
    var health = jQuery(target).closest('.go_feedback_input').find('.go_messages_health_input').val();
    var toggle_percent = (jQuery(target).closest('.go_feedback_input').find('.go_feedback_toggle').siblings().hasClass("-on")) ? 1 : 0;
    var percent = jQuery(target).closest('.go_feedback_input').find('.feedback_percent_input').val();


    // send data
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_send_feedback;
    var gotoSend = {
        is_frontend: is_frontend,
        action:"go_send_feedback",
        _ajax_nonce: nonce,
        title: title,
        message: message,
        radio: radio,
        toggle_assign: toggle_assign,
        toggle_percent: toggle_percent,
        percent: percent,
        xp: xp,
        gold: gold,
        health: health,
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
        success: function( res ) {
            //console.log('success_test');
            //console.log(res);

            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            var response = {};
            try {
                var response = JSON.parse( res );
            } catch (e) {
                response = {
                    json_status: '101',
                    table: '',
                    form: '',
                };
            };
            //console.log(response.json_status);
            if ( 302 === Number.parseInt( response.json_status ) ) {
                console.log (302);
                jQuery('.go_blog_post_wrapper_' + post_id).find('.fa-eye-slash').hide();
                jQuery('.go_blog_post_wrapper_' + post_id).find('.fa-eye').show();

                let table = response.table;
                let form = response.form;
                let icon = response.icon;
                console.log(icon);
                /*if (radio == 'percent') {
                    if (toggle_percent === 1) {
                        let mypercent = "<strong>+" + percent + "%</strong>";
                        jQuery(target).closest('.go_blog_post_wrapper').find('.go_status_percent').addClass('up').removeClass('down').html(mypercent).show();
                    } else {
                        let mypercent = "<strong>-" + percent + "%</strong>";

                        jQuery(target).closest('.go_blog_post_wrapper').find('.go_status_percent').addClass('down').removeClass('up').html(mypercent).show();
                    }
                }*/
                //jQuery(target).closest('.go_blog_post_wrapper').find('.go_status_percent').html(percent);
                //jQuery(target).closest('.feedback_accordion').html(table);

                var wrapper = jQuery(target).closest('.go_blog_post_wrapper');
                jQuery(target).closest('.go_blog_post_wrapper').find('.feedback_icon').html(icon);
                if (jQuery(target).closest('.feedback_accordion').find('.go_feedback_table_container').length) {
                    jQuery(target).closest('.feedback_accordion').find('.go_feedback_table_container').html(table);
                }
                else{
                    var newDiv = '<h3>Feedback</h3><div class="go_blog_feedback"><div class="go_feedback_table_container"></div></div>'
                    //var newDiv = "<div><h3>Q4 New Question</h3><div>New Content</div></div>";
                    jQuery(target).closest('.feedback_accordion').prepend(newDiv)
                    jQuery(target).closest('.feedback_accordion').find('.go_feedback_table_container').html(table);
                }

                jQuery(target).closest('.feedback_accordion').find('.go_feedback_form_container').html(form);
                jQuery('.feedback_accordion').accordion("refresh");
                go_blog_new_posts();

                jQuery('html, body').animate({
                    scrollTop: jQuery(wrapper).offset().top-100
                }, 2000);

                /*jQuery(target).closest('.go_feedback_form_container').html(form).find('.summernote').summernote({
                    toolbar: [
                        // [groupName, [list of button]]
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        //['font', ['strikethrough', 'superscript', 'subscript']],
                        ['fontsize', ['fontsize']],
                        // ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        //['height', ['height']]
                        ['insert', ['link']],
                    ]
                });*/

                /*

                var fullId = 'go_feedback_text_area_id_'+post_id;
                var plugins = "charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,wordcount,go_shortcode_button,go_admin_comment";

                var toolbar1 = "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,go_shortcode_button,,fullscreen";
                var toolbar2 = '';
                tinymce.init({
                    selector: fullId,
                    branding: false,
                    theme: "modern",
                    skin: "lightgray",
                    language: "en",
                    formats: {
                        alignleft: [
                            {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign: 'left'}},
                            {selector: 'img,table,dl.wp-caption', classes: 'alignleft'}
                        ],
                        aligncenter: [
                            {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign: 'center'}},
                            {selector: 'img,table,dl.wp-caption', classes: 'aligncenter'}
                        ],
                        alignright: [
                            {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign: 'right'}},
                            {selector: 'img,table,dl.wp-caption', classes: 'alignright'}
                        ],
                        strikethrough: {inline: 'del'}
                    },
                    relative_urls: false,
                    remove_script_host: false,
                    convert_urls: false,
                    browser_spellcheck: true,
                    fix_list_elements: true,
                    entities: "38,amp,60,lt,62,gt",
                    entity_encoding: "raw",
                    keep_styles: false,
                    paste_webkit_styles: "font-weight font-style color",
                    preview_styles: "font-family font-size font-weight font-style text-decoration text-transform",
                    wpeditimage_disable_captions: false,
                    wpeditimage_html5_captions: true,
                    plugins: plugins,
                    selector: "#" + fullId,
                    resize: "vertical",
                    menubar: false,
                    wpautop: true,
                    wordpress_adv_hidden: false,
                    indent: false,
                    toolbar1: toolbar1,
                    toolbar2: toolbar2,
                    toolbar3: "",
                    toolbar4: "",
                    tabfocus_elements: ":prev,:next",
                    body_class: "id post-type-post post-status-publish post-format-standard",
                    height: "150",
                });

                // this is needed for the editor to initiate
                tinyMCE.execCommand('mceAddEditor', false, fullId);*/

               // jQuery(target).closest('.go_feedback_form').find('.go_feedback_canned').val(jQuery(".go_feedback_canned option:first").val());
                //jQuery(target).closest('.go_feedback_form').find('.go_title_input').val('');
                //jQuery(target).closest('.go_feedback_form').find('.go_message_input').html($message);

               // tinyMCE.get('go_feedback_text_area_id_'+post_id).setContent('');


               // go_blog_new_posts();


            }



            // show success or error message
            console.log("send successful2");
            console.log(target);
            Swal.fire(//sw2 OK
                {
                    type: 'success',
                    showConfirmButton: false,
                    timer: 1500
                }
            );
        }
    });
}

function go_get_likes_list(target){
    console.log("go_get_likes_list");

    console.log("go_get_likes_list");
    var user_id = jQuery(target).data('user_id');
    var post_id = jQuery(target).data('post_id');
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_get_likes_list;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_get_likes_list',
            user_id: user_id,
            post_id: post_id
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
        success: function (res) {
            go_after_ajax();
            jQuery(".go_show_likes_list").off().one("click", function(e){
                go_get_likes_list(this);
                //go_blog_new_posts();
            });
            console.log(res);
            if (-1 !== res) {
                jQuery.featherlight(res,
                    {
                        variant: 'go_get_likes_list',
                        afterContent: function () {
                        }
                    });
            }
        }
    });
}