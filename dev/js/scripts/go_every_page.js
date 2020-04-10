
jQuery( document ).ready( function() {
    go_setup_badges_page();
    go_setup_groups_page();
    go_activate_tippy();
    if (typeof (GO_TASK_LIST) !== 'undefined'){
        jQuery('.page-title-action').removeAttr("href").css("cursor","pointer").on("click", function(e){
            go_new_task_from_template();
        });
    }

    //This is to fix a bug in select2 that affects Safari only
    jQuery('select.select2-hidden-accessible').on('select2:closing', function() {
        jQuery('body > span.select2-container.select2-container--default.select2-container--open').hide();
    });

    jQuery('select.select2-hidden-accessible').on('select2:open', function() {
        jQuery('body > span.select2-container.select2-container--default.select2-container--open').css('display','inline-block');
    });
    //END SELECT2 fix

    //jQuery('#go_user_link').on("click", function(e){
    //    go_login_lightbox();
   // });

    go_activate_tippy();
    let debug = go_debug;

    if (debug === 'false') {
        /*
        jQuery(document).on('heartbeat-tick', function (event, data) {

            // Check for our data, and use it.
            if (!data.go_message) {
                return;
            }
            jQuery('body').append(data.go_message);
            //alert( 'The hash is ' + data.go_message );
        });

        jQuery(document).on('heartbeat-send', function (event, data) {
            console.log('send');
            // Add additional data to Heartbeat data. //used for checking messages
            data.go_heartbeat = true;
        });*/
    }else{
        console.log('Game On Debug Mode On');
    }

    jQuery('#wp-admin-bar-go_add_quest_from_template, .go_add_quest_from_template').on("click", function(e){
        go_new_task_from_template();
    });

    jQuery(".go_password_change_modal").one("click", function(e){
        go_update_password_lightbox();
    });

    //if this page has the password set fields (password, confirmation, strength)
    if (typeof (hasPassword) !== 'undefined') {
        go_activate_password_checker();
        jQuery('#footer-widgets').hide();
    }
    if (typeof (hideFooterWidgets) !== 'undefined') {
        jQuery('#footer-widgets').hide();
    }

    jQuery(".go_edit_frontend_task").off().one("click", function(){
        go_edit_frontend(this);
    });
});


function go_highlight_apply_filters() {
    console.log("go_highlight_apply_filters");
    //if there is a filter, add the filter on change to all inputs
    //with the correct type of data to filter
    //typeof jQuery("#go_leaderboard_filters")
    var filter_on_change = jQuery("#go_leaderboard_filters").data("filter_on_change");
    var type = jQuery("#go_leaderboard_filters").data("type");
    console.log('filter on change');
    console.log(filter_on_change);

    if (filter_on_change) {
        //console.log(filter_on_change2);
        console.log(type);
        //var filter_on_change = jQuery("#go_leaderboard_filters").data("filter_on_change");
        //if (filter_on_change) {

            if (type === 'leaderboard') {
                console.log("Leaderboard Update Now");
                go_update_leaderboard();
            } else if (type == 'blog') {
                go_reader_update();
                console.log("Update Now");
            }
            else if (type === 'single_stage') {
                go_reader_update();
                console.log("Update Now");
            }
       // }
    }
    else {//clipboard and reader
            jQuery('.go_apply_filters').addClass("bluepulse");
            jQuery('.go_apply_filters').html('<span class="ui-button-text">Apply Filter<i class="fas fa-filter" aria-hidden="true"></i></span>');
    }
}

function go_after_ajax(){
    //activate any new store item links on the result
    jQuery('.go_str_item').off().one("click", function(e){
        go_lb_opener( this );
    });
    //do the attendance ajax
    go_check_attendance();
}

function go_check_attendance(){
    console.log('go_check_attendance');
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_attendance_check_ajax;

    jQuery.ajax({
        type: "GET",
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            action: 'go_attendance_check_ajax',
            is_frontend: is_frontend
        },
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400) {
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [{'wp-auth-check': false}]);
            }

        },
        success: function (res) {
            console.log("go_check_attendance SUCCESS")
            go_check_messages_ajax();
            jQuery('body').append(res);
        }
    });
}

function go_show_clone_buttons(){
    jQuery(".go_actions_wrapper").toggle();
    jQuery(".go_actions_wrapper_flex").toggle();

    //get the option from local storage
    //toggle the option
    //set local storage
    //toggle the items
    //jQuery(".loot_info").toggle();
}

function go_activate_tippy(){
    console.log('go_activate_tippy');
    tippy('.tooltip', {
        delay: 0,
        arrow: true,
        arrowType: 'round',
        size: 'large',
        duration: 300,
        animation: 'scale',
        zIndex: 999999,
        placement: 'top',
    });

    tippy('.tooltip_click', {
        trigger: 'click',
        delay: 0,
        arrow: true,
        arrowType: 'round',
        size: 'large',
        duration: 300,
        animation: 'scale',
        zIndex: 999999,
        placement: 'top',
    });

    tippy('.actiontip', {
        delay: 0,
        arrow: true,
        arrowType: 'round',
        size: 'large',
        duration: [300, 0],
        animation: 'scale',
        zIndex: 9999999,
        placement: 'top',
    });


}

function go_new_task_from_template(target){
    tippyInstances.forEach(instance => {
        instance.hide();
    });
    console.log('go_new_task_from_template');
    var nonce = GO_FRONTEND_DATA.nonces.go_new_task_from_template;

    var frontend_edit = jQuery(target).data('frontend_edit');
    var chain_id = jQuery(target).data('chain_id');
    var chain_name = jQuery(target).data('chain_name');
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            action: 'go_new_task_from_template',
            frontend_edit: frontend_edit,
            chain_id: chain_id,
            chain_name: chain_name,
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
            if (error == 'true') {
                return;
            }

            if ( -1 !== res ) {
                //console.log('winning');
                console.log(res);
                if (res){
                    console.log(res);
                    jQuery.featherlight.close();
                    jQuery.featherlight(res);

                    jQuery('.go_new_task_from_template_button').one('click', function(){
                        go_clone_post_new_menu_bar(this);
                    });
                }

            }

        }
    });
}

//sends the selected post_id to the clone function
function go_clone_post_new_menu_bar(target) {
    console.log('go_clone_post_new_menu_bar');
    let post_id = jQuery('.go_new_task_from_template').val();
    let global = jQuery('.go_new_task_from_template .'+ post_id).data('global');
    let chain_id = jQuery(target).data('chain_id');
    let frontend_edit = jQuery(target).data('frontend_edit');
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_clone_post_new_menu_bar;

    jQuery.ajax({
        type: "GET",
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            action: 'go_clone_post_new_menu_bar',
            is_frontend: is_frontend,
            post: post_id,
            chain_id: chain_id,
            global: global,
            frontend_edit: frontend_edit

        },
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400) {
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [{'wp-auth-check': false}]);
            }

        },
        success: function (results) {
console.log(results);
            let error = go_ajax_error_checker(results);
            if (error == 'true') return;

            if (-1 !== results) {
                var res = jQuery.parseJSON(results);
                console.log(res);
                //console.log(res);
                if (res.redirect == 'true') {
                    window.location.href = res.link;
                }
                else{
                    go_edit_frontend(null, res.post_id, true);
                }


            }

        }
    });
}

function go_activate_password_checker(){
    console.log('go_activate_password_checker');


    jQuery( document ).ready( function() {
        //your code here
        //jQuery('.acf-form-submit .acf-button').hide();
        if(jQuery("#acf-field_5cd3638830f17").length){
            jQuery('.acf-form-submit .acf-button').attr('disabled', true).css( 'cursor', 'default' ).css('opacity', '.5');
        }

    });

    jQuery( 'body' ).on( 'keyup', '.newpassword, .confirmpassword', function( event ) {

        wdmChkPwdStrength(
            // password field   2
            jQuery('#newpassword input'),
            // confirm password field
            jQuery('#confirmpassword input'),
            // strength status
            jQuery('.password-strength'),
            // Submit button
            jQuery('.acf-form-submit .acf-button'),
            // blacklisted words which should not be a part of the password
            ['admin', 'happy', 'hello', '1234']
        );
    });
}

function go_update_password_lightbox(){//this is only needed on the frontend profile page
    console.log('go_update_password_lightbox');
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            //_ajax_nonce: nonce,
            action: 'go_update_password_lightbox',
            is_frontend: is_frontend,
        },
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
            jQuery(".featherlight-content #go_password_change").off().one("click", function(e){
                go_update_password();
            });
        },
        success: function( res ) {
            //console.log(res);
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            if ( -1 !== res ) {

                if (res){
                    //add the returned form to the existing div on page that stores it temporarily
                    jQuery('#go_password_change_lightbox').html(res);
                    console.log('open lightbox');

                    //open form in a lightbox
                    jQuery.featherlight(jQuery(".go_password_change_container"), {
                        afterContent: function() {
                            //delete the form content that was stored before opening it in a lightbox.  The lightbox has a copy.
                            jQuery('#go_password_change_lightbox').empty();

                            //make it so you can tab through the form.
                            jQuery('.featherlight-content').find('a, input[type!="hidden"], select, textarea, iframe, button:not(.featherlight-close), iframe, [contentEditable=true]').each(function (index) {
                                if (index === 0) {
                                    jQuery(this).prop('autofocus', true);
                                }
                                jQuery(this).prop('tabindex', 0);
                            });

                            // trigger the wdmChkPwdStrength
                            go_activate_password_checker();
                            /*
                            jQuery( 'body' ).on( 'keyup', '.featherlight-content .newpassword input, .featherlight-content .confirmpassword input', function( event ) {
                                console.log('wdmChkPwdStrength');
                                wdmChkPwdStrength(
                                    // password field   2
                                    jQuery('.featherlight-content .newpassword input'),
                                    // confirm password field
                                    jQuery('.featherlight-content .confirmpassword input'),
                                    // strength status
                                    jQuery('.featherlight-content .password-strength'),
                                    // Submit button
                                    jQuery('.featherlight-content .acf-button'),
                                    // blacklisted words which should not be a part of the password
                                    ['admin', 'happy', 'hello', '1234']
                                );
                            });
                            */
                        },
                        afterClose: function() {
                            jQuery('.acf-form-submit .acf-button').removeAttr( 'disabled' );
                        },
                    } );

                    //////////////////

                }
                jQuery(".go_password_change_modal").one("click", function(e){
                    go_update_password_lightbox();
                });

            }

        }
    });
}

//https://stackoverflow.com/questions/24602343/wordpress-custom-change-password-page
//
// https://wisdmlabs.com/blog/how-to-add-a-password-strength-meter-in-wordpress/

function wdmChkPwdStrength( $pwd,  $confirmPwd, $strengthStatus, $submitBtn, blacklistedWords ) {
    console.log("wdmChkPwdStrength");
    var pwd = $pwd.val();
    var confirmPwd = $confirmPwd.val();

    // extend the blacklisted words array with those from the site data
    blacklistedWords = blacklistedWords.concat( wp.passwordStrength.userInputBlacklist() )

    // every time a letter is typed, reset the submit button and the strength meter status
    // disable the submit button
    $submitBtn.attr( 'disabled', 'disabled' );
    console.log('disabled');
    $strengthStatus.removeClass( 'short bad good strong' );
    $submitBtn.css( 'cursor', 'default' );
    $submitBtn.css('opacity', '.5');


    // calculate the password strength
    var pwdStrength = wp.passwordStrength.meter( pwd, blacklistedWords, confirmPwd );
    let requiredStrength = 4;

    // check the password strength
    switch ( pwdStrength ) {

        case 2:
            $strengthStatus.addClass( 'bad' ).html( pwsL10n.bad );
            break;

        case 3:
            $strengthStatus.addClass( 'good' ).html( pwsL10n.good );
            break;

        case 4:
            $strengthStatus.addClass( 'strong' ).html( pwsL10n.strong );
            break;

        case 5:
            $strengthStatus.addClass( 'short' ).html( pwsL10n.mismatch );
            break;

        default:
            $strengthStatus.addClass( 'short' ).html( pwsL10n.short );

    }
// set the status of the submit button
    //if ( 4 === pwdStrength && '' !== confirmPwd.trim() ) {
        console.log("pws: " + pwdStrength);
    if ( (pwdStrength >= requiredStrength) && (pwdStrength < 5) && ('' !== confirmPwd.trim() )) {
        console.log('enable');
        $submitBtn.removeAttr( 'disabled' );
        $submitBtn.css( 'cursor', 'pointer' );;
        $submitBtn.css('opacity', '1');
    }

    return pwdStrength;
}

function go_update_password(){//this is only needed on the frontend
    console.log('go_update_password');
    //var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_lightbox;
    //nonce = 'fail';
    var current_password = jQuery(".featherlight-content input[name=currentpassword]").val();
    var new_password = jQuery(".featherlight-content input[name=newpassword]").val();
    var confirm_password = jQuery(".featherlight-content input[name=confirmpassword]").val();
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            //_ajax_nonce: nonce,
            action: 'go_update_password',
            is_frontend: is_frontend,
            current_password: current_password,
            new_password: new_password,
            confirm_password: confirm_password,
        },
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
            jQuery(".featherlight-content #go_password_change").off().one("click", function(e){
                go_update_password();
            });
        },
        success: function( res ) {
            go_after_ajax();
            console.log(res);
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            if ( -1 !== res ) {

            if (res == 'success'){
                console.log('password changed')
                jQuery.featherlight.close();
                swal.fire({//sw2 OK
                    text: "Your password was changed."
                });

            }else if (res == 'current_password_invalid') {
                swal.fire({//sw2 OK
                        text: 'The current password entered is invalid.',
                        type: 'error'
                    }
                );
                jQuery(".featherlight-content #go_password_change").off().one("click", function(e){
                    go_update_password();
                });
            }


            }

        }
    });
}

function go_ajax_error_checker(raw){
    if (raw == 'login'){
        console.log('not logged in');
        jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
        return 'true';
    };
    if (raw ==='refresh'){
        console.log('nonce verify failed');
        Swal.fire({//sw2 OK
            title: "Error",
            text: "Refresh the page and then try again? You will lose unsaved changes. You can cancel and copy any unsaved changes to a safe location before refresh.",
            type: 'warning',
            //showCancelButton: true,
            confirmButtonText: 'Refresh Now',
            //cancelButtonText: 'No, cancel!',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger'
            },
        })
            .then((result) => {
                if (result.value) {
                    location.reload();
                }
            });

        return 'true';
    };
}

function go_user_profile_link(uid){
    console.log('go_user_profile_link');
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            //_ajax_nonce: nonce,
            action: 'go_user_profile_link',
            is_frontend: is_frontend,
            uid: uid
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
        success: function (url) {
            go_after_ajax();
            //console.log(url);
            //console.log('go_user_profile_link_success');
            window.open(url);
        }
    })
}

function go_noty_close_oldest(){
    Noty.setMaxVisible(6);
    var noty_list_count = jQuery('#noty_layout__topRight > div').length;
    if(noty_list_count == 0) {
        jQuery('#noty_layout__topRight').remove();
    }
    if(noty_list_count >= 5) {
        jQuery('#noty_layout__topRight > div').first().trigger( "click" );
    }
}

function go_lightbox_blog_img(){
    console.log("go_lightbox_blog_img");
    jQuery('[class*= wp-image]').each(function(  ) {
        var fullSize = jQuery( this ).hasClass( "size-full" );
        console.log("fullsize:" + fullSize);
        if (fullSize == true) {
            var imagesrc = jQuery(this).attr('src');
        }else{

            var class1 = jQuery(this).attr('class');
            console.log(class1);
            //var patt = /w3schools/i;
            var regEx = /.*wp-image/;
            var imageID = class1.replace(regEx, 'wp-image');
            //console.log(imageID);

            var src1 = jQuery(this).attr('src');
            console.log(src1);
            var regEx2 = /-([^-]+).$/;



            //var regEx3 = /\.[0-9a-z]+$/i;
            var patt1 = /\.[0-9a-z]+$/i;
            var m1 = (src1).match(patt1);

            //var imagesrc = src1.replace(regEx2, regEx3);
            var imagesrc = src1.replace(regEx2, m1);
            console.log(imagesrc);
        }
        jQuery(this).featherlight(imagesrc);
    });


    //lightbox gallery
    jQuery('.gallery img').each(function(  ) {
        var fullSize = jQuery( this ).hasClass( "size-full" );
        //console.log("fullsize:" + fullSize);
        if (fullSize == true) {
            var imagesrc = jQuery(this).attr('src');
        }else{

            //var class1 = jQuery(this).attr('class');
            //console.log(class1);
            //var patt = /w3schools/i;
            //var regEx = /.*wp-image/;
            //var imageID = class1.replace(regEx, 'wp-image');
            //console.log(imageID);

            var src = jQuery(this).attr('src');//the link to the thumbnail or original file if it is small
            console.log(src);
            var src2 = src.substr(0, src.lastIndexOf("/")); //string before last slash

            var src3 = src.substring(src.lastIndexOf("/") + 1); //string after last slash
            //console.log(src3);
            //var patt = /w3schools/i;
            var regEx2 = /-([^-]+).$/;
            //var regEx3 = /\.[0-9a-z]+$/i;
            var patt1 = /\.[0-9a-z]+$/i;
            var m1 = (src3).match(patt1);
            //console.log('m1');
            //console.log(m1);

            //var imagesrc = src1.replace(regEx2, regEx3);
            var imagename = src3.replace(regEx2, m1); //string minus the file dimensions (the original file for the lightbox)
            //console.log("img");
            //console.log(imagename);

            var imagesrc = src2 + "/" + imagename;
        }

        var attr = jQuery(this).parent().attr('href');

// For some browsers, `attr` is undefined; for others, `attr` is false. Check for both.
        if (typeof attr !== typeof undefined && attr !== false) {
            // Element has this attrib
            jQuery(this).parent().attr('href', imagesrc);
        }else{
            jQuery(this).parent().wrap("<a href='"+ imagesrc +"'></a>");
        }
        //console.log('make href');
        //jQuery(this).featherlight(imagesrc);
    });



    //console.log('gallery');
    var num_variant = 0;
    var variant_name = 'gallery';
    var this_variant = '';
    jQuery('.gallery').each(function( index, element  ) {
        num_variant++;
        this_variant = variant_name +"_"+ num_variant
        jQuery(element).find('a').featherlightGallery({
        });
    });
}

function go_stats_lightbox_page_button( uid ) {//this is called from the admin bar
    console.log("go_stats_lightbox_page_button");
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_lightbox;
    //nonce = 'fail';
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_stats_lightbox',
            uid: uid
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
            console.log("go_stats_lightbox_page_button SUCCESS");
            go_after_ajax();
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            if ( -1 !== res ) {

                jQuery.featherlight(res, {
                    variant: 'stats',
                    afterClose: function(event){
                        jQuery('.go_user_bar_stats').blur();
                        jQuery('body').focus();
                    }
                });

                //go_stats_task_list();

                jQuery('#stats_tabs').tabs();
                jQuery( '.stats_tabs' ).click( function() {
                    console.log("tabs");
                    tab = jQuery(this).attr('tab');
                    switch (tab) {
                        case 'about':
                            //go_stats_about();
                            break;
                        case 'tasks':
                            go_stats_task_list();
                            break;
                        case 'store':
                            go_stats_store_list();
                            break;
                        case 'history':
                            go_stats_activity_list();
                            break;
                        case 'messages':
                            go_stats_messages();
                            break;
                        case 'badges':
                            go_stats_badges_list();
                            break;
                        case 'groups':
                            go_stats_groups_list();
                            break;
                    }
                });

                go_stats_links(); //links for the header
                //go_reader_activate_buttons();//activate buttons on about new blog post
                go_blog_new_posts();

                jQuery(".go_grade_scales").off().one("click", function(){
                    go_print_grade_scales(this);
                });


                //if there is no about tab, and there is a task tab, load the task table
                //if(!jQuery('#stats_about').length){
                    if(jQuery('#stats_badges').length) {
                        go_stats_badges_list();
                    }else{
                        if(jQuery('#stats_tasks').length) {
                            go_stats_task_list();
                        }
                    }
                //}


            }

        }
    });
}

function go_print_grade_scales(target){
    console.log("go_print_grade_scales");
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_print_grade_scales;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_print_grade_scales'
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
            jQuery(target).off().one("click", function(){
                go_print_grade_scales(this);
            });
            go_after_ajax();
            if (-1 !== res) {
                jQuery.featherlight(res,
                    {
                        variant: 'go_print_grade_scales',
                        afterContent: function () {

                        }
                    });
            }
        }
    });
}

//Applies the stats and messages links to the icons on tables on clipboard and leaderboard
function go_stats_links(){
    //jQuery('.go_user_link_stats').prop('onclick',null).off('click');
    jQuery('.go_user_link_stats').off().one('click', function(){
        let user_id = jQuery(this).attr('uid');
        go_stats_lightbox_page_button(user_id)
    });

    jQuery(".go_stats_messages_icon").off().one("click", function(e){
        var user_id = this.getAttribute('data-uid');
        go_messages_opener(user_id, null, "single_message", this);
    });

    jQuery(".go_user_map").off().one("click", function(e){
        go_user_map(this);
    });
}

function go_make_select2_filter(taxonomy, location, use_saved_value = false, parents_only = false, value = null, value_name = null) {
    console.log("go_make_select2_filter");
    var value = jQuery('#go_' + location + '_' + taxonomy + '_select').data('value');
    var value_name = jQuery('#go_' + location + '_' + taxonomy + '_select').data('value_name');
    if (use_saved_value) {

        // Get saved data from sessionStorage
        if(value === null || typeof value === 'undefined') {
            var saved_value = localStorage.getItem(taxonomy);
            var saved_value_name = localStorage.getItem(taxonomy + '_name');
        }else{
            var saved_value = value;
            var saved_value_name = value_name;
        }
        //console.log("saved_value"+saved_value);
        //console.log('saved_value_name'+saved_value_name);
        if(saved_value != 'undefined' && saved_value_name != 'undefined' && saved_value != '' && saved_value_name != '' && saved_value != 'null' ){
            value = saved_value;
            value_name = saved_value_name;
        }else{
            value = '';
            value_name = '';
        }
    }
    //console.log("taxonomy"+taxonomy);
   // console.log("value"+value);
   // console.log('value_name'+value_name);
    //console.log('location: ' + location);

    if( value != null && value != 'null') {
        // Fetch the preselected item, and add to the control
        var valueSelect = jQuery('#go_' + location + '_' + taxonomy + '_select');
        var option = new Option(value_name, value, true, true);
        //valueSelect.append(option);
        valueSelect.append(option).trigger('change.select2');
    }

    //if clear is pressed, remove all options before continueing with ajax
    jQuery('#go_' + location + '_' + taxonomy + '_select').on("select2:unselecting", function(e) {
        jQuery('#go_' + location + '_' + taxonomy + '_select').val(null).trigger('change');
        jQuery('#go_' + location + '_' + taxonomy + '_select').empty();
        if(jQuery('#go_' + location + '_' + taxonomy + '_select').hasClass("go_activate_filter")) {
            go_highlight_apply_filters();
        }
    });


    jQuery('#go_' + location + '_' + taxonomy + '_select').select2({
        ajax: {
            url: MyAjax.ajaxurl, // AJAX URL is predefined in WordPress admin
            dataType: 'json',
            delay: 400, // delay in ms while typing when to perform a AJAX search

            data: function (params) {

                return {
                    q: params.term, // search query
                    is_frontend: is_frontend,
                    action: 'go_make_taxonomy_dropdown_ajax', // AJAX action for admin-ajax.php
                    taxonomy: taxonomy,
                    parents_only: parents_only,
                };
            },
            processResults: function( data ) {
                console.log("processing");
                jQuery('#go_' + location + '_' + taxonomy + '_select').off();
                var myvar = jQuery('#go_' + location + '_' + taxonomy + '_select').val();//get the current value
                console.log(myvar);
                jQuery('#go_' + location + '_' + taxonomy + '_select').select2("destroy");
                jQuery('#go_' + location + '_' + taxonomy + '_select').empty();
                jQuery('#go_' + location + '_' + taxonomy + '_select').select2({
                    data: data,
                    placeholder: "Show All",
                    allowClear: true});
                jQuery('#go_' + location + '_' + taxonomy + '_select').val(myvar).trigger('change');
                jQuery('#go_' + location + '_' + taxonomy + '_select').select2("open");

                if(jQuery('#go_' + location + '_' + taxonomy + '_select').hasClass("go_activate_filter")) {
                    jQuery('#go_' + location + '_' + taxonomy + '_select').on('select2:select', function (e) {
                        // Do something
                        console.log("apply_filters");
                        go_highlight_apply_filters();
                    });

                    jQuery('#go_' + location + '_' + taxonomy + '_select').on('select2:unselect', function (e) {
                        go_highlight_apply_filters();
                    });
                }

                /*
                // Event listener to the range filtering inputs to redraw on input
                if (jQuery("#go_leaders_datatable").length) {
                    console.log("On the Leaderboard");
                    jQuery('#go_page_user_go_sections_select, #go_page_user_go_groups_select').change(function () {
                        //var section = jQuery('#go_user_go_sections_select').val();
                        console.log('redraw');
                        leaderboard.draw();
                    });
                }*/

                //var is_reader = jQuery("#go_leaderboard_filters").data("is_reader");

                //
                return {
                    results: data
                };

            },
            success: function( data ) {
                go_after_ajax();
            },
            cache: true
        },
        minimumInputLength: 0, // the minimum of symbols to input before perform a search
        multiple: false,
        placeholder: "Show All",
        allowClear: true,
    });
}

function go_make_select2_cpt( my_div, cpt) {

    jQuery(my_div).select2({
        ajax: {
            url: MyAjax.ajaxurl, // AJAX URL is predefined in WordPress admin
            dataType: 'json',
            delay: 400, // delay in ms while typing when to perform a AJAX search
            data: function (params) {
                return {
                    q: params.term, // search query
                    is_frontend: is_frontend,
                    action: 'go_make_cpt_select2_ajax', // AJAX action for admin-ajax.php
                    cpt: cpt
                };
            },
            processResults: function( data ) {
                //console.log("search results: " + data);
                var options = [];
                if ( data ) {

                    // data is the array of arrays, and each of them contains ID and the Label of the option
                    jQuery.each( data, function( index, text ) { // do not forget that "index" is just auto incremented value
                        options.push( { id: text[0], text: text[1]  } );
                    });

                }
                return {
                    results: options
                };
            },
            cache: false
        },
        minimumInputLength: 1, // the minimum of symbols to input before perform a search
        multiple: true,
        placeholder: "Show All"
    });

}

function go_loader_html(size){
    var url = ( PluginDir.url + 'media/images/spinner-solid.svg' );
    var url2 = ( PluginDir.url + 'media/images/30.svg' );
    if(size == "big"){
        var px = 75;
        var html = "<div id='loader_container' class='go_loader_container'><div id='loader'><img style='height: " + px + "px;' class='go_loader fa-pulse' src='" + url + "'></div></div>";

    }else if(size == "tiny"){
        var px = 15
        var html = "<img style='height: " + px + "px; margin-top: 7px;' class='go_loader fa-pulse' src='" + url + "'>";
    }
    else if(size === 'fountain') {
        var html = "<div id='loader_container' class='go_loader_container'><img class='' src='" + url2 + "'></div>";
    }
    else{
        var px = 25
        var html = "<img style='height: " + px + "px; margin-top: 7px;' class='go_loader fa-pulse' src='" + url + "'>";
    }

    return html;
}

function go_stats_task_list() {
console.log("go_stats_task_list");
    if (jQuery("#go_tasks_datatable").length == 0) {

        var loader_html = go_loader_html('big');
        jQuery("#stats_tasks").html(loader_html);

        var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_task_list;
        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                is_frontend: is_frontend,
                action: 'go_stats_task_list',
                user_id: jQuery('#go_stats_hidden_input').val()
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
                console.log("SUCCESS go_stats_task_list");
                //console.log(res);
                if (-1 !== res) {
                    jQuery('#stats_tasks').html(res);
                    jQuery('#go_tasks_datatable').dataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": MyAjax.ajaxurl + '?action=go_tasks_dataloader_ajax',
                            "data": function(d){
                                d.user_id = jQuery('#go_stats_hidden_input').val();}
                        },
                        responsive: true,
                        "autoWidth": false,
                        columnDefs: [
                            { targets: '_all', "orderable": false }
                        ],

                        "searching": true,
                        "drawCallback": function( settings ) {
                            go_enable_reset_buttons();
                            jQuery( ".go_blog_user_task" ).off().one("click", function () {
                                go_blog_user_task(this);
                            });
                        },

                        "order": [[3, "desc"]],



                    });
                }
                go_reset_opener(null);
            }
        });
    }
    else{
        jQuery("#go_task_list").show();
        jQuery("#go_task_list_single").hide();
    }
}

function go_enable_reset_buttons(){
    //apply on click to the individual task reset icons
    jQuery('.go_reset_task_clipboard').prop('onclick',null).off('click');
    jQuery(".go_reset_task_clipboard").one("click", function(){
        go_messages_opener( this.getAttribute('data-uid'), this.getAttribute('data-task'), 'single_reset', this );
    });

    //apply on click to the reset button at the top
    jQuery('.go_tasks_reset_multiple_clipboard').parent().prop('onclick',null).off('click');
    jQuery(".go_tasks_reset_multiple_clipboard").parent().one("click", function(){
        go_messages_opener( null, null, 'multiple_reset', this );
    });
}

//not used
function go_close_single_history(){
    jQuery("#go_task_list").show();
    jQuery("#go_task_list_single").hide();
}

function go_stats_single_task_activity_list (postID) {
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_single_task_activity_list;

    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_stats_single_task_activity_list',
            user_id: jQuery( '#go_stats_hidden_input' ).val(),
            postID: postID
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
            if ( -1 !== res ) {
                //jQuery( '#go_stats_body' ).html( '' );
                jQuery( '#go_task_list_single' ).remove();
                jQuery("#go_task_list").hide();
                jQuery( '#stats_tasks' ).append( res );
                jQuery( '#go_single_task_datatable' ).dataTable( {

                    "bPaginate": true,
                    "order": [[0, "desc"]],
                    //"destroy": true,
                    responsive: true,
                    "autoWidth": false
                });
            }
        }
    });
}

function go_stats_store_list() {
    //console.log("store");
    //jQuery(".go_datatables").hide();

    if (jQuery("#go_store_datatable").length == 0 ) {

        var loader_html = go_loader_html('big');
        jQuery("#stats_store").html(loader_html);

        var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_store_list;
        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                is_frontend: is_frontend,
                action: 'go_stats_store_list',
                user_id: jQuery('#go_stats_hidden_input').val()
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
                if (-1 !== res) {
                    jQuery('#stats_store').html(res);
                    jQuery('#go_store_datatable').dataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": MyAjax.ajaxurl + '?action=go_stats_store_item_dataloader',
                            "data": function(d){
                                d.user_id = jQuery('#go_stats_hidden_input').val();}
                        },
                        responsive: true,
                        "autoWidth": false,
                        columnDefs: [
                            { targets: '_all', "orderable": false }
                        ],
                        "searching": true,
                        "order": [[0, "desc"]]
                    });
                }
            }
        });
    }
}

function go_actions_tooltip(){

    if(typeof window.tippyInstances == 'undefined' ){
        window.tippyInstances = []
    };
    var map_actions = sessionStorage.getItem('map_actions');
    if(map_actions === 'true') {

        if (jQuery('.go_show_actions').each(function(){
            if(jQuery(this).find('.actions_tooltip').length) {


                const instances = tippy(this, {
                    content(reference) {
                        const tooltip = reference.getElementsByClassName("actions_tooltip")[0];
                        if (typeof (tooltip) !== 'undefined') {
                            let this_tooltip = tooltip.getElementsByClassName("my_tooltip")[0].innerHTML;
                            return this_tooltip;
                        }
                        //return reference;
                    },
                    interactive: true,
                    arrow: true,
                    delay: [500, 0],
                    theme: 'light',
                    placement: 'top',
                    flip: false,
                    followCursor: 'initial',
                    zIndex: 9999999,
                    onCreate(instance) {

                    },
                    onMount(instance) {

                        //jQuery('.tools').show();
                        //jQuery('.quickedit_form').hide();
                    },
                    onShown(instance) {
                        // ...

                        go_tooltip_callback();


                    }
                });

                if (typeof instances !== 'undefined') {
                    window.tippyInstances = tippyInstances.concat(instances);
                }

            }
        }));
    }
}

function go_tooltip_callback(){
    // ...
    jQuery(".go_clone_icon").off().one("click", function () {
        go_importer(this);
    });

    jQuery('.go_quest_reader_lightbox_button').off().one("click", function () {
        go_reader_update(this, true);
    });

    jQuery(".go_quests_frontend").off().one("click", function(){
        go_quests_frontend(this);
    });

    jQuery(".go_quick_edit_show").off().one("click", function(){
        go_quick_edit_show(this);
    });

    jQuery(".go_edit_frontend").off().one("click", function(){
        go_edit_frontend(this);
    });

    jQuery(".go_edit_frontend_post").off().one("click", function(){
        go_new_task_from_template(this);
    });

    jQuery(".go_trash_post").off().one("click", function(){
        go_trash_post(this);
    });

    go_activate_tippy();

}

function go_stats_activity_list() {
    console.log("go_stats_activity_list");
    if (jQuery("#go_activity_datatable").length == 0) {

        var loader_html = go_loader_html('big');
        jQuery("#stats_history").html(loader_html);

        var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_activity_list;
        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                is_frontend: is_frontend,
                action: 'go_stats_activity_list',
                user_id: jQuery('#go_stats_hidden_input').val(),
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
                if (-1 !== res) {
                    jQuery('#stats_history').html(res);
                    jQuery('#go_activity_datatable').dataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": MyAjax.ajaxurl + '?action=go_activity_dataloader_ajax',
                            "data": function(d){
                                d.user_id = jQuery('#go_stats_hidden_input').val();}
                        },
                        responsive: true,
                        "autoWidth": false,
                        columnDefs: [
                            { targets: '_all', "orderable": false }
                        ],

                        "searching": true,

                        "order": [[0, "desc"]],
                        "drawCallback": function( settings ) {
                            go_activate_tippy();
                        }

                    });

                }
            }
        });
    }
}

function go_stats_messages() {

    if (jQuery("#go_messages_datatable").length == 0) {

        var loader_html = go_loader_html('big');
        jQuery("#stats_messages").html(loader_html);

        var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_messages;
        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                is_frontend: is_frontend,
                action: 'go_stats_messages',
                user_id: jQuery('#go_stats_hidden_input').val(),
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
                if (-1 !== res) {
                    jQuery('#stats_messages').html(res);
                    jQuery('#go_messages_datatable').dataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": MyAjax.ajaxurl + '?action=go_messages_dataloader_ajax',
                            "data": function(d){
                                d.user_id = jQuery('#go_stats_hidden_input').val();}
                        },
                        responsive: true,
                        "autoWidth": false,
                        columnDefs: [
                            { targets: '_all', "orderable": false }
                        ],
                        "searching": true,
                        "order": [[0, "desc"]],
                        "drawCallback": function( settings ) {
                            go_lightbox_blog_img();
                        },
                    });

                }
            }
        });
    }
}

function go_stats_badges_list() {

    if (jQuery("#stats_badges #go_badges_list").length === 0) {
        var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_badges_list;

        var loader_html = go_loader_html('big');
        jQuery("#stats_badges").html(loader_html);

        console.log("go_stats_badges_list");
        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                is_frontend: is_frontend,
                action: 'go_stats_badges_list',
                user_id: jQuery('#go_stats_hidden_input').val(),
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
                //console.log(res);
                if (-1 !== res) {
                    jQuery('#stats_badges').html(res);
                    go_activate_tippy();

                }
            }
        });
    }
}

function go_setup_badges_page(){
    console.log('go_setup_badges');
    go_activate_tippy();
    go_actions_tooltip();
    go_disable_tooltips(true);

    jQuery(".go_edit_frontend_badge").off().one("click", function(){
        go_edit_frontend(this);
    });

    if(jQuery('#stats_badges_page').hasClass('sortable')) {

        var elements = document.getElementsByClassName('go_stats_badges');
        for (var i = 0; i < elements.length; i++) {

            var el = elements[i];
            new Sortable(el, {


        //var el = document.getElementsByClassName('go_stats_badges');
       // console.log(el);
        //new Sortable(el, {
            //group: 'shared', // set both lists to same group
            animation: 150,
            onUpdate: function (/**Event*/evt) {
                console.log('update badge group order');
                console.log(evt);
                var itemEl = evt.item; // dragged HTMLElement
                var listEl = evt.from; //previous list
                console.log(itemEl);
                console.log(listEl);

                var terms = [];

                jQuery(listEl).find('.parent_cat').each(function () {
                    var term_id = jQuery(this).data('term_id');
                    terms.push(term_id);
                });
                console.log('terms');
                console.log(terms);
                var nonce = GO_EVERY_PAGE_DATA.nonces.go_update_chain_order;
                jQuery.ajax({
                    type: 'post',
                    url: MyAjax.ajaxurl,
                    data: {
                        _ajax_nonce: nonce,
                        action: 'go_update_chain_order',
                        terms: terms,
                        taxonomy: 'go_badges',
                    },

                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 400) {
                            jQuery(document).trigger('heartbeat-tick.wp-auth-check', [{'wp-auth-check': false}]);
                        }
                    },
                    success: function (res) {
                        console.log(res);
                        if (res !== 'success') {
                            swal.fire({
                                type: 'warning',
                                title: 'There was a problem updating the order.',
                                text: 'The page will be updated and then you can try again.',
                                confirmButtonText:
                                    "Refresh",
                                timer: 15000,
                            }).then((result) => {
                                location.reload();
                            });
                        }

                    }
                });

            },
        });

        }




        var elements = document.getElementsByClassName('badges_row');
        for (var i = 0; i < elements.length; i++) {

            var el = elements[i];
            new Sortable(el, {
                group: 'shared', // set both lists to same group
                animation: 150,
                onAdd: function (/**Event*/evt) {
                    console.log('update task order Add');
                    var itemEl = evt.item; // dragged HTMLElement
                    var NewListEl = evt.to;    // target list
                    var listEl = evt.from; //previous list

                    go_update_badge_group_sort(NewListEl)

                },
                onRemove: function (/**Event*/evt) {
                    console.log('update task order Remove');
                    var itemEl = evt.item; // dragged HTMLElement
                    var NewListEl = evt.to;    // target list
                    var listEl = evt.from; //previous list

                    go_update_badge_group_sort(listEl)

                },
                onUpdate: function (/**Event*/evt) {
                    console.log('update task order update');
                    var itemEl = evt.item; // dragged HTMLElement
                    var NewListEl = evt.to;    // target list
                    var listEl = evt.from; //previous list

                    go_update_badge_group_sort(listEl)

                },
            });
        }


    }
}

function go_stats_groups_list() {
    console.log("go_stats_groups_list");
    if (jQuery("#stats_badges #go_groups_list").length == 0) {
        var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_groups_list;

        var loader_html = go_loader_html('big');
        jQuery("#stats_groups").html(loader_html);

        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                is_frontend: is_frontend,
                action: 'go_stats_groups_list',
                user_id: jQuery('#go_stats_hidden_input').val(),
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
                console.log(res)
                //console.log(res);
                if (-1 !== res) {
                    jQuery('#stats_groups').html(res);
                    go_activate_tippy();

                }
            }
        });
    }
}

function go_setup_groups_page(){
    go_activate_tippy();
    go_actions_tooltip();
    go_disable_tooltips(true);

    jQuery(".go_edit_frontend_badge").off().one("click", function(){
        go_edit_frontend(this);
    });

    if(jQuery('#stats_groups_page').hasClass('sortable')) {

        var el = document.getElementById('go_groups');
        console.log(el);
        new Sortable(el, {
            //group: 'shared', // set both lists to same group
            animation: 150,
            onUpdate: function (/**Event*/evt) {
                console.log('update badge group order');
                console.log(evt);
                var itemEl = evt.item; // dragged HTMLElement
                var listEl = evt.from; //previous list
                console.log(itemEl);
                console.log(listEl);

                var terms = [];

                jQuery(listEl).find('.parent_cat').each(function () {
                    var term_id = jQuery(this).data('term_id');
                    terms.push(term_id);
                });
                console.log('terms');
                console.log(terms);
                var nonce = GO_EVERY_PAGE_DATA.nonces.go_update_chain_order;
                jQuery.ajax({
                    type: 'post',
                    url: MyAjax.ajaxurl,
                    data: {
                        _ajax_nonce: nonce,
                        action: 'go_update_chain_order',
                        terms: terms,
                        taxonomy: 'user_go_groups',
                    },

                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 400) {
                            jQuery(document).trigger('heartbeat-tick.wp-auth-check', [{'wp-auth-check': false}]);
                        }
                    },
                    success: function (res) {
                        console.log(res);
                        if (res !== 'success') {
                            swal.fire({
                                type: 'warning',
                                title: 'There was a problem updating the order.',
                                text: 'The page will be updated and then you can try again.',
                                confirmButtonText:
                                    "Refresh",
                                timer: 15000,
                            }).then((result) => {
                                location.reload();
                            });
                        }

                    }
                });

            },
        });

        var elements = document.getElementsByClassName('badges_row');
        for (var i = 0; i < elements.length; i++) {

            var el = elements[i];
            new Sortable(el, {
                group: 'shared', // set both lists to same group
                animation: 150,
                onAdd: function (/**Event*/evt) {
                    console.log('update task order Add');
                    var itemEl = evt.item; // dragged HTMLElement
                    var NewListEl = evt.to;    // target list
                    var listEl = evt.from; //previous list

                    go_update_badge_group_sort(NewListEl)

                },
                onRemove: function (/**Event*/evt) {
                    console.log('update task order Remove');
                    var itemEl = evt.item; // dragged HTMLElement
                    var NewListEl = evt.to;    // target list
                    var listEl = evt.from; //previous list

                    go_update_badge_group_sort(listEl)

                },
                onUpdate: function (/**Event*/evt) {
                    console.log('update task order update');
                    var itemEl = evt.item; // dragged HTMLElement
                    var NewListEl = evt.to;    // target list
                    var listEl = evt.from; //previous list

                    go_update_badge_group_sort(listEl)

                },
            });
        }


    }
}



function go_date_loader(start, end, is_default) {
    if (is_default == true){
        start = moment();
        end = moment();
    }else{
        go_highlight_apply_filters();//on date change
    }

    jQuery('#go_datepicker').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
}

function go_load_daterangepicker(source){
    console.log(source);
    console.log("go_load_daterangepicker");
    jQuery('#go_datepicker_clipboard').daterangepicker({
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        "startDate": moment(),
        "endDate": moment(),
        "opens": "center",
        locale: {
            cancelLabel: 'Clear'
        }
    }, function(start, end, label) {
        console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
        go_date_loader(start, end, false);
    });

    jQuery('#go_datepicker_clipboard').on('cancel.daterangepicker', function(ev, picker) {
        jQuery('#go_datepicker_clipboard span').html('');
    });

    go_date_loader(null, null, true);
}

//NOT USED
function go_load_daterangepicker_empty(){
    console.log('go_load_daterangepicker_empty');
    jQuery('#go_datepicker_clipboard').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    jQuery('#go_datepicker_clipboard').on('apply.daterangepicker', function(ev, picker) {
        jQuery('#go_datepicker_clipboard span').html(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    jQuery('#go_datepicker_clipboard').on('cancel.daterangepicker', function(ev, picker) {
        jQuery('#go_datepicker_clipboard span').html('');
    });

}

function go_setup_filter_buttons(is_reader){
    console.log("go_setup_filter_buttons");

    jQuery('#go_task_select, #go_store_item_select').on('select2:select', function (e) {
        // Do something
        go_highlight_apply_filters();
    });

    jQuery('#go_task_select, #go_store_item_select').on('select2:unselect', function (e) {
        // Do something
        go_highlight_apply_filters();
    });

    jQuery(".go_reader_input").change(function () {
        go_highlight_apply_filters();//on reader checkbox change
    });

    jQuery('.go_reset_filters').on("click", function () {
        jQuery('#datepicker_clipboard span').html("");
        jQuery('#go_reader_user_go_sections_select, #go_reader_user_go_groups_select, #go_reader_go_badges_select, #go_task_select, #go_store_item_select').val(null).trigger('change');
        go_highlight_apply_filters();
    });

    jQuery('.go_reset_filters').on("click", function () {
        jQuery('#go_datepicker').html("");
        jQuery('#go_reader_user_go_sections_select, #go_reader_user_go_groups_select, #go_reader_go_badges_select, #go_task_select, #go_store_item_select').val(null).trigger('change');


        if(is_reader){
            jQuery('#go_reader_read, #go_reader_reset, #go_reader_trash, #go_reader_draft').prop('checked', false);
            jQuery('#go_reader_unread').prop('checked', true);
            jQuery("#go_reader_order_oldest").prop("checked", true);
            jQuery("#go_posts_num").val("10");




        }else{
            //jQuery('#go_unmatched_toggle').prop('checked', false); // Uncheck
        }


        go_highlight_apply_filters();//on reset of the filter

    });

    if(is_reader){
        jQuery("#go_leaderboard_filters").css('display', 'flex');
    }

    /*
    var go_unmatched_toggle = localStorage.getItem('go_unmatched');
    //console.log("go_unmatched_toggle: "+go_unmatched_toggle);
    if(go_unmatched_toggle == 'checked'){
        jQuery('#go_unmatched_toggle').prop('checked', true); // check
    }*/

    go_daterange_clear();
}

function go_daterange_clear(){

    jQuery('#go_reset_datepicker').on("click", function (e){
        e.stopPropagation();
        jQuery('#go_datepicker_container').html('<div id="go_datepicker_clipboard"><i class="fas fa-calendar" style="float: left;"></i><span id="go_datepicker"></span> <i id="go_reset_datepicker" class=""select2-selection__clear><b> × </b></i><i class="fa fa-caret-down"></i></div>');
        //jQuery('#go_datepicker_clipboard span').html('');
        jQuery('#go_reset_datepicker').hide();
        jQuery('#go_datepicker_container').one("click", function (){
            //console.log("hi there one");
            go_load_daterangepicker('go_daterange_clear');
            jQuery('#go_reset_datepicker').show();
            go_daterange_clear();
        });
        go_highlight_apply_filters();//on clear of daterange
    });
}

function go_copy_to_clipboard(event){
    console.log("go_copy_to_clipboard");

    var copyText = jQuery(event).find('.go_copy_this').html();


    var dummy = document.createElement("textarea");
    // to avoid breaking orgain page when copying more words
    // cant copy when adding below this code
    // dummy.style.display = 'none'
    document.body.appendChild(dummy);
    //Be careful if you use texarea. setAttribute('value', value), which works with "input" does not work with "textarea". – Eduard
    dummy.value = copyText;
    dummy.select();
    document.execCommand("copy");
    document.body.removeChild(dummy);

    document.querySelectorAll('.tippy-popper').forEach(popper => {
       // if (popper !== instance.popper) {
        setTimeout(function() {
            //your code to be executed after 1 second
            popper._tippy.hide();
        }, 1000);

       // }
    })



}

//this now saves to session data
function go_save_filters(location){
    console.log("go_save_filters");
    var source = location;
    if(location == 'clipboard'){
        location = 'reader'
    }
    //SESSION STORAGE
    var section = jQuery( "#go_" + location + "_user_go_sections_select" ).val();
    var section_name = jQuery("#go_" + location + "_user_go_sections_select option:selected").text();
    var group = jQuery( "#go_" + location + "_user_go_groups_select" ).val();
    var group_name = jQuery("#go_" + location + "_user_go_groups_select option:selected").text();
    var badge = jQuery( "#go_" + location + "_go_badges_select" ).val();
    var badge_name = jQuery("#go_" + location + "_go_badges_select option:selected").text();

    /*
    if(source == 'clipboard') {
        //var unmatched = document.getElementById("go_unmatched_toggle").checked;
        var unmatched = jQuery("#go_unmatched_toggle").attr('checked');
    }*/
    //localStorage.setItem('test_this', "yep");
    localStorage.setItem('user_go_sections', section);
    localStorage.setItem('go_badges', badge);
    localStorage.setItem('user_go_groups', group);
    localStorage.setItem('user_go_sections_name', section_name);
    localStorage.setItem('go_badges_name', badge_name);
    localStorage.setItem('user_go_groups_name', group_name);
    //localStorage.setItem('go_unmatched', unmatched);


    /*
    if(is_reader){
        var date = jQuery('#go_datepicker_clipboard span').html();
        var tasks = jQuery("#go_task_select").val();
        var unread = jQuery('#go_reader_unread').prop('checked');
        var read = jQuery('#go_reader_read').prop('checked');
        var reset = jQuery('#go_reader_reset').prop('checked');
        var trash = jQuery('#go_reader_trash').prop('checked');
        var draft = jQuery('#go_reader_draft').prop('checked');
        var order = jQuery("input[name='go_reader_order']:checked").val();
        var limit = jQuery('#go_posts_num').val();
        localStorage.setItem('go_reader_date', date);
        localStorage.setItem('go_reader_tasks', tasks);
        localStorage.setItem('go_reader_unread', unread);
        localStorage.setItem('go_reader_read', read);
        localStorage.setItem('go_reader_reset', reset);
        localStorage.setItem('go_reader_trash', trash);
        localStorage.setItem('go_reader_draft', draft);
        localStorage.setItem('go_reader_order', order);
        localStorage.setItem('go_reader_limit', limit);
    }
    */

    /*
    //THIS IS FOR SAVING AS OPTION IN DB WITH AJAX
    //ajax to save the values
    var nonce = GO_CLIPBOARD_DATA.nonces.go_clipboard_save_filters;
    var section = jQuery( '#go_page_user_go_sections_select' ).val();
    var group = jQuery( '#go_page_user_go_groups_select' ).val();
    var badge = jQuery( '#go_page_go_badges_select' ).val();
    var unmatched = document.getElementById("go_unmatched_toggle").checked;
    //alert ("badge " + badge);
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_clipboard_save_filters',
            section: section,
            badge: badge,
            group: group,
            unmatched: unmatched
        },
        success: function( res ) {
            console.log("values saved");
        }
    });
    */
}


function go_update_badges_page(){
    console.log('go_update_badges_page');
    var loader_html = go_loader_html('big');

    var nonce = GO_EVERY_PAGE_DATA.nonces.go_update_badges_page;
    var taxonomy = jQuery('.go_page_container').data("taxonomy");

    if(taxonomy==='go_badges'){
        jQuery("#stats_badges_page").html(loader_html);
    }
    else if(taxonomy==='go_groups'){
        jQuery("#stats_groups_page").html(loader_html);
    }


    jQuery.ajax({
        type: "POST",
        url : MyAjax.ajaxurl,
        data: {
            is_frontend: is_frontend,
            action:'go_update_badges_page',
            taxonomy : taxonomy,
            _ajax_nonce: nonce
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
        success:function(res) {
            go_after_ajax();
            console.log("success!");
            if(taxonomy==='go_badges'){
                jQuery("#stats_badges_page").html(res);
                go_setup_badges_page();
            }
            else if(taxonomy==='user_go_groups'){
                jQuery("#stats_groups_page").html(res);
                go_setup_groups_page();
            }
        }

    });
}