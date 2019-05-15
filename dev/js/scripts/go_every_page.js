jQuery( document ).ready( function() {


    //jQuery('#go_user_link').on("click", function(e){
    //    go_login_lightbox();
   // });


    let debug = go_debug;
    if (debug === 'false') {
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
            // Add additional data to Heartbeat data.
            data.go_heartbeat = true;
        });
    }else{
        console.log('Game On Debug Mode On');
    }





    jQuery(".go_password_change_modal").on("click", function(e){
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

});

function go_activate_password_checker(){
    jQuery( 'body' ).on( 'keyup', '.newpassword, .confirmpassword', function( event ) {
        console.log('wdmChkPwdStrength');
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

function go_update_password_lightbox(){//this is only needed on the frontend
    console.log('go_update_password_lightbox');
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            //_ajax_nonce: nonce,
            action: 'go_update_password_lightbox'
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


            }

        }
    });
}


//https://stackoverflow.com/questions/24602343/wordpress-custom-change-password-page
//
// https://wisdmlabs.com/blog/how-to-add-a-password-strength-meter-in-wordpress/

function wdmChkPwdStrength( $pwd,  $confirmPwd, $strengthStatus, $submitBtn, blacklistedWords ) {
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
    let requiredStrength = minPassword;

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
    //var nonce = GO_EVERY_PAGE_DATA.nonces.go_admin_bar_stats;
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
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            //_ajax_nonce: nonce,
            action: 'go_user_profile_link',
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
    jQuery('[class*= wp-image]').each(function(  ) {
        var fullSize = jQuery( this ).hasClass( "size-full" );
        //console.log("fullsize:" + fullSize);
        if (fullSize == true) {
            var imagesrc = jQuery(this).attr('src');
        }else{

            var class1 = jQuery(this).attr('class');
            //console.log(class1);
            //var patt = /w3schools/i;
            var regEx = /.*wp-image/;
            var imageID = class1.replace(regEx, 'wp-image');
            //console.log(imageID);

            var src1 = jQuery(this).attr('src');
            //console.log(src1);
            //var patt = /w3schools/i;
            var regEx2 = /-([^-]+).$/;


            //var regEx3 = /\.[0-9a-z]+$/i;
            var patt1 = /\.[0-9a-z]+$/i;
            var m1 = (src1).match(patt1);

            //var imagesrc = src1.replace(regEx2, regEx3);
            var imagesrc = src1.replace(regEx2, m1);
            //console.log(imagesrc);
        }
        jQuery(this).featherlight(imagesrc);
    });
}

function go_admin_bar_stats_page_button( id ) {//this is called from the admin bar
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_admin_bar_stats;
    //nonce = 'fail';
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            action: 'go_admin_bar_stats',
            uid: id
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
            //console.log(res);
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            if ( -1 !== res ) {

                jQuery.featherlight(res, {
                    variant: 'stats',
                    afterClose: function(event){
                        jQuery('.go_user_bar_stats').blur();
                        jQuery(body).focus();
                    }
                });

                go_stats_task_list();

                jQuery('#stats_tabs').tabs();
                jQuery( '.stats_tabs' ).click( function() {
                    //console.log("tabs");
                    tab = jQuery(this).attr('tab');
                    switch (tab) {
                        case 'about':
                            go_stats_about();
                            break;
                        case 'tasks':
                            go_stats_task_list();
                            break;
                        case 'store':
                            go_stats_item_list();
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
                        case 'leaderboard':
                            go_stats_leaderboard();
                            break;
                    }
                });

            }

        }
    });
}

function go_stats_links(){
    jQuery('.go_user_link_stats').prop('onclick',null).off('click');
    jQuery('.go_user_link_stats').one('click', function(){  var user_id = jQuery(this).attr('name'); go_admin_bar_stats_page_button(user_id)});
    jQuery('.go_stats_messages_icon').prop('onclick',null).off('click');
    jQuery(".go_stats_messages_icon").one("click", function(e){
        var user_id = this.getAttribute('data-uid');
        go_messages_opener(user_id, null, "single_message", this);
    });
}

function go_make_select2_filter(taxonomy, my_value, is_clipboard, is_hier) {

    if (is_clipboard) {
        // Get saved data from sessionStorage
        var value = localStorage.getItem('go_clipboard_' + my_value);
        var value_name = localStorage.getItem('go_clipboard_' + my_value + '_name');
    }else{
        value = jQuery('#go_clipboard_' + taxonomy + '_select').data('value');
        value_name = jQuery('#go_clipboard_' + taxonomy + '_select').data('value_name');
    }


    jQuery('#go_clipboard_' + taxonomy + '_select').select2({
        ajax: {
            url: MyAjax.ajaxurl, // AJAX URL is predefined in WordPress admin
            dataType: 'json',
            delay: 400, // delay in ms while typing when to perform a AJAX search
            data: function (params) {

                return {
                    q: params.term, // search query
                    action: 'go_make_taxonomy_dropdown_ajax', // AJAX action for admin-ajax.php
                    taxonomy: taxonomy,
                    is_hier: is_hier
                };


            },
            processResults: function( data ) {

                jQuery("#go_clipboard_" + taxonomy + "_select").select2("destroy");
                jQuery("#go_clipboard_" + taxonomy + "_select").children().remove();
                jQuery("#go_clipboard_" + taxonomy + "_select").select2({
                    data: data,
                    placeholder: "Show All",
                    allowClear: true}).val(value).trigger("change");
                jQuery("#go_clipboard_" + taxonomy + "_select").select2("open");
                return {
                    results: data
                };

            },
            cache: true
        },
        minimumInputLength: 0, // the minimum of symbols to input before perform a search
        multiple: false,
        placeholder: "Show All",
        allowClear: true
    });

    if( value != null && value != 'null') {
        // Fetch the preselected item, and add to the control
        var valueSelect = jQuery('#go_clipboard_' + taxonomy + '_select');
        // create the option and append to Select2
        var option = new Option(value_name, value, true, true);
        valueSelect.append(option).trigger('change');
    }

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


function go_stats_about(user_id) {
    //console.log("about");
    //jQuery(".go_datatables").hide();
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_about;
    if ( jQuery( "#go_stats_about" ).length == 0 ) {
        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                action: 'go_stats_about',
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
                if (-1 !== res) {
                    //console.log(res);
                    //console.log("about me");
                    //jQuery( '#go_stats_body' ).html( '' );
                    //var oTable = jQuery('#go_tasks_datatable').dataTable();
                    //oTable.fnDestroy();

                    jQuery('#stats_about').html(res);


                }
            }
        });
    }
}

/*
function go_blog_lightbox_opener(post_id){
    console.log("go_blog_lightbox_opener");
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_blog_lightbox_opener;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            action: 'go_blog_lightbox_opener',
            blog_post_id: post_id
        },
        success: function (res) {
            if (-1 !== res) {
                jQuery.featherlight(res, {variant: 'blog_post'});

                jQuery(".go_blog_lightbox").off().one("click", function(){
                    go_blog_lightbox_opener(this.id);
                });

            }

        }
    });
}
*/

function go_stats_task_list() {
    jQuery("#stats_tasks").html("<div id='loader_container' style='display:block; height: 250px; width: 100%; padding: 40px 30px;'><div id='loader' <i class='fas fa-spinner fa-pulse fa-4x'></i></div></div>");
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_task_list;
    if (jQuery("#go_tasks_datatable").length == 0) {
        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
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
                        },

                        "order": [[3, "desc"]],
                        /*'createdRow': function (row, data, dataIndex) {
                            var dateCell = jQuery(row).find('td:eq(0)').text(); // get first column

                            var d = new Date(dateCell * 1000);
                            var month = d.getMonth() + 1;
                            var day = d.getDate();
                            var year = d.getFullYear().toString().slice(-2);
                            var hours = d.getHours();
                            var dd = "AM";
                            var h = hours;
                            if (h >= 12) {
                                h = hours - 12;
                                dd = "PM";
                            }
                            if (h == 0) {
                                h = 12;
                            }
// Minutes part from the timestamp
                            var minutes = "0" + d.getMinutes();
// Seconds part from the timestamp
                            //var seconds = "0" + d.getSeconds();

// Will display time in 10:30:23 format
                            var formattedTime = month + "/" + day + "/" + year + "  " + h + ':' + minutes.substr(-2) + " " + dd;
                            jQuery(row).find('td:eq(0)').attr("data-order", dateCell).text(formattedTime);


                        }*/


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

function go_stats_item_list() {
    //console.log("store");
    //jQuery(".go_datatables").hide();
    jQuery("#stats_store").html("<div id='loader_container' style='display:block; height: 250px; width: 100%; padding: 40px 30px;'><div id='loader' <i class='fas fa-spinner fa-pulse fa-4x'></i></div></div>");

    var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_item_list;
    if (jQuery("#go_store_datatable").length == 0 ) {
        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                action: 'go_stats_item_list',
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

//the SSP v4 one
function go_stats_activity_list() {
    jQuery("#stats_history").html("<div id='loader_container' style='display:block; height: 250px; width: 100%; padding: 40px 30px;'><div id='loader' <i class='fas fa-spinner fa-pulse fa-4x'></i></div></div>");

    var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_activity_list;
    if (jQuery("#go_activity_datatable").length == 0) {
        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                action: 'go_stats_activity_list',
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
                            tippy('.tooltip', {
                                delay: 0,
                                arrow: true,
                                arrowType: 'round',
                                size: 'large',
                                duration: 300,
                                animation: 'scale',
                                zIndex: 999999
                            });
                        }

                    });

                }
            }
        });
    }
}

function go_stats_messages() {
    jQuery("#stats_messages").html("<div id='loader_container' style='display:block; height: 250px; width: 100%; padding: 40px 30px;'><div id='loader' <i class='fas fa-spinner fa-pulse fa-4x'></i></div></div>");

    var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_messages;
    if (jQuery("#go_messages_datatable").length == 0) {
        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                action: 'go_stats_messages',
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
                        "order": [[0, "desc"]]
                    });
                }
            }
        });
    }
}

function go_stats_badges_list() {
    jQuery("#stats_badges").html("<div id='loader_container' style='display:block; height: 250px; width: 100%; padding: 40px 30px;'><div id='loader' <i class='fas fa-spinner fa-pulse fa-4x'></i></div></div>");
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_badges_list;
    if (jQuery("#go_badges_list").length == 0) {

        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                action: 'go_stats_badges_list',
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
                //console.log(res);
                if (-1 !== res) {
                    jQuery('#stats_badges').html(res);
                }
            }
        });
    }
}

function go_stats_groups_list() {
    jQuery("#stats_groups").html("<div id='loader_container' style='display:block; height: 250px; width: 100%; padding: 40px 30px;'><div id='loader' <i class='fas fa-spinner fa-pulse fa-4x'></i></div></div>");

    var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_groups_list;

    if (jQuery("#go_groups_list").length == 0) {
        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                action: 'go_stats_groups_list',
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
                if (-1 !== res) {
                    jQuery('#stats_groups').html(res);
                }
            }
        });
    }
}

function go_stats_lite (user_id) {
    //jQuery(".go_datatables").hide();
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_stats_lite;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            action: 'go_stats_lite',
            uid: user_id
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

            jQuery.featherlight(res, {variant: 'stats_lite'});

            if ( -1 !== res ) {
                //jQuery( '#go_stats_lite_wrapper' ).remove();
                //jQuery( '#stats_leaderboard' ).append( res );
                //jQuery("#go_leaderboard_wrapper").hide();
                jQuery('#go_tasks_datatable_lite').dataTable({
                    "destroy": true,
                    responsive: true,
                    "autoWidth": false,
                    "drawCallback": function( settings ) {
                        go_stats_links();
                    },
                    "searching": false
                });
            }
        }
    });
}

function go_activate_apply_filters() {
    console.log("go_activate_apply_filters");
    jQuery('.go_update_clipboard').addClass("bluepulse");
    jQuery('.go_update_clipboard').html('<span class="ui-button-text">Apply Filters<i class="fas fa-filter" aria-hidden="true"></i></span>');
}

function go_date_loader(start, end, is_default) {
    if (is_default == true){
        start = moment();
        end = moment();
    }else{
        go_activate_apply_filters();
    }

    jQuery('#go_datepicker').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));


}

function go_load_daterangepicker(page){
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


function go_setup_reset_filter_button(is_reader){
    jQuery('#go_clipboard_user_go_sections_select, #go_clipboard_user_go_groups_select, #go_clipboard_go_badges_select, #go_task_select, #go_store_item_select').on('select2:select', function (e) {
        // Do something
        go_activate_apply_filters();
    });
    jQuery('#go_clipboard_user_go_sections_select, #go_clipboard_user_go_groups_select, #go_clipboard_go_badges_select, #go_task_select, #go_store_item_select').on('select2:unselect', function (e) {
        // Do something
        go_activate_apply_filters();
    });

    jQuery('.go_reset_clipboard').on("click", function () {
        jQuery('#go_datepicker').html("");
        jQuery('#go_clipboard_user_go_sections_select, #go_clipboard_user_go_groups_select, #go_clipboard_go_badges_select, #go_task_select, #go_store_item_select').val(null).trigger('change');


        if(is_reader){
            jQuery('#go_reader_read, #go_reader_reset, #go_reader_trash, #go_reader_draft').prop('checked', false);
            jQuery('#go_reader_unread').prop('checked', true);
            jQuery("#go_reader_order_oldest").prop("checked", true);
            jQuery("#go_posts_num").val("10");

        }else{
            jQuery('#go_unmatched_toggle').prop('checked', false); // Uncheck
        }
        go_activate_apply_filters();

    });


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
            go_load_daterangepicker('clear');
            jQuery('#go_reset_datepicker').show();
            go_daterange_clear();
        });
        go_activate_apply_filters();

        //go_load_daterangepicker_empty();

    });
}

function go_clear_daterange(){
}



//this now saves to session data
function go_save_clipboard_filters(){
    console.log("go_save_clipboard_filters");
    //SESSION STORAGE
    var section = jQuery( '#go_clipboard_user_go_sections_select' ).val();
    var section_name = jQuery("#go_clipboard_user_go_sections_select option:selected").text();
    var group = jQuery( '#go_clipboard_user_go_groups_select' ).val();
    var group_name = jQuery("#go_clipboard_user_go_groups_select option:selected").text();
    var badge = jQuery( '#go_clipboard_go_badges_select' ).val();
    var badge_name = jQuery("#go_clipboard_go_badges_select option:selected").text();

    var unmatched = document.getElementById("go_unmatched_toggle").checked;

    localStorage.setItem('go_clipboard_section', section);
    localStorage.setItem('go_clipboard_badge', badge);
    localStorage.setItem('go_clipboard_group', group);
    localStorage.setItem('go_clipboard_section_name', section_name);
    localStorage.setItem('go_clipboard_badge_name', badge_name);
    localStorage.setItem('go_clipboard_group_name', group_name);
    localStorage.setItem('go_clipboard_unmatched', unmatched);

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
    var section = jQuery( '#go_clipboard_user_go_sections_select' ).val();
    var group = jQuery( '#go_clipboard_user_go_groups_select' ).val();
    var badge = jQuery( '#go_clipboard_go_badges_select' ).val();
    var unmatched = document.getElementById("go_unmatched_toggle").checked;
    //alert ("badge " + badge);
    //console.log(jQuery( '#go_clipboard_user_go_sections_select' ).val());
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
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