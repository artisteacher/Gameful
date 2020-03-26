jQuery( document ).ready( function() {
    if (typeof (IsLeaderboard) !== 'undefined') {//this only needs to run on the leaderboard
        //groups and the table are callbacks on success of this function call
        //go_make_leaderboard_filter('user_go_sections');
        //go_make_leaderboard_filter('user_go_groups');

        go_make_select2_filter('user_go_sections','reader', true);
        go_make_select2_filter('user_go_groups','reader', true);
        go_make_select2_filter('go_badges','reader', true);
       // go_make_select2_filter('go_badges','reader', true);

        /*jQuery('#go_show_all_leaderboard').change(function() {
            go_stats_leaderboard_page();
        });*/
        jQuery('#social_tabs').tabs();
        jQuery( '.social_tabs' ).click( function() {
            console.log("tabs");
            tab = jQuery(this).attr('tab');
            switch (tab) {
                case 'leaderboard':
                    go_leaderboard_table();
                    break;
                case 'users':
                   go_all_user_list();
                    break;
                case 'feed':
                    go_make_feed()
                    break;
            }
        });
        go_leaderboard_table();
    }
});


/*
function go_make_leaderboard_filter(taxonomy){
    console.log('go_make_leaderboard_filter');
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            //_ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_make_leaderboard_filter',
            taxonomy: taxonomy
        },

        error: function(jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }

        },
        success: function( res ) {
            console.log(res);
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            if ( -1 !== res ) {

                if (res){
                    var res = JSON.parse( res );
                    go_make_select2_filter(taxonomy,'page', true, false);
                    let value = res['term_id'];
                    let value_name = res['term_name'];

                    var valueSelect = jQuery('#go_page_' + taxonomy + '_select');
                    var option = new Option(value_name, value, true, true);
                    //valueSelect.append(option);
                    valueSelect.append(option).trigger('change.select2');
                    jQuery('#go_page_' + taxonomy +'_select').val(value);
                }
            }

        }
    });
}
*/


function go_update_leaderboard() {
    console.log("go_leaderboard_update");
    go_save_filters('reader');

    //*******************//
    //GET CURRENT TAB
    //*******************//
    var current_tab = jQuery("#social_tabs").find("[aria-selected='true']").attr('aria-controls');
    console.log("current_tab:"+ current_tab);
    //IF CURRENT TAB IS . . .
    if (current_tab == "social_leaderboard"){
        //jQuery('#go_leaderboard_wrapper').remove();
        jQuery('#go_leaderboard_wrapper_all').remove();
        leaderboard.draw();

    }
    else if (current_tab == "social_users") {
        //Clear other tabs
        jQuery('#go_leaderboard_wrapper').remove();
        //jQuery('#go_leaderboard_wrapper_all').remove();
        leaderboard_all.draw();
    }
    else if (current_tab == "social_feed") {
        //Clear other tabs
        jQuery('#go_leaderboard_wrapper').remove();
        jQuery('#go_leaderboard_wrapper_all').remove();
        go_reader_update();
    }
}

function go_leaderboard_table() {
    console.log("go_leaderboard_table");
    if ( jQuery( "#go_leaders_datatable" ).length == 0 ) {

        var loader_html = go_loader_html('big');
        jQuery("#social_leaderboard_datatable_container").html(loader_html);

        var nonce = GO_EVERY_PAGE_DATA.nonces.go_make_leaderboard;
        jQuery.ajax({
            url: MyAjax.ajaxurl,
            type: "post",
            data: {
                _ajax_nonce: nonce,
                is_frontend: is_frontend,
                action: 'go_make_leaderboard',
                show_all: false,
                //go_clipboard_messages_datatable: jQuery( '#go_clipboard_store_datatable' ).val()
            },
            /**
             * A function to be called if the request fails.
             * Assumes they are not logged in and shows the login message in lightbox
             */
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 400){
                    jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
                }
                //go_clipboard_callback();
            },
            success: function( res ) {
                go_after_ajax();
                //console.log("success");
                if (-1 !== res) {
                    jQuery('#social_leaderboard_datatable_container').html(res).promise().done(function () {
                        //go_filter_datatables();
                        //console.log("get_data1");
                        var is_admin = GO_FRONTEND_DATA.go_is_admin;
                        var initial_sort = 3;
                        if (is_admin == true) {
                            initial_sort = 4;
                        }
                        var nonce = GO_FRONTEND_DATA.nonces.go_leaderboard_dataloader_ajax;
                        if (jQuery("#go_leaders_datatable").length) {
                            //console.log("get_data2");
                            leaderboard = jQuery('#go_leaders_datatable').DataTable({
                                "processing": true,
                                "serverSide": true,
                                "ajax": {
                                    "url": MyAjax.ajaxurl + '?action=go_leaderboard_dataloader_ajax',
                                    "data": function (d) {
                                        //test if select2 exists, and send value or send false so it will get saved value from database
                                        //this is useful on page load while the select2 is still being drawn
                                        if (jQuery('#go_reader_user_go_sections_select').hasClass("select2-hidden-accessible")) {
                                            var section_value = jQuery('#go_reader_user_go_sections_select').val();
                                        } else {
                                            var section_value = localStorage.getItem('user_go_sections');
                                        }
                                        console.log("section_value:" + section_value)
                                        if (jQuery('#go_reader_user_go_groups_select').hasClass("select2-hidden-accessible")) {
                                            var group_value = jQuery('#go_reader_user_go_groups_select').val();
                                        } else {
                                            var group_value = localStorage.getItem('user_go_groups');
                                        }
                                        if (jQuery('#go_reader_go_badges_select').hasClass("select2-hidden-accessible")) {
                                            var badge_value = jQuery('#go_reader_go_badges_select').val();
                                        } else {
                                            var badge_value = localStorage.getItem('go_badges');
                                        }
                                        //var show_all = jQuery('#go_show_all_leaderboard').prop('checked');

                                        d.section = section_value;
                                        d.group = group_value;
                                        d.badge = badge_value;
                                        d.show_all = false;
                                        d._ajax_nonce = nonce;
                                    }
                                },
                                //"orderFixed": [[4, "desc"]],
                                //"destroy": true,
                                responsive: false,
                                "autoWidth": false,
                                "paging": true,
                                "drawCallback": function (settings) {
                                    go_stats_links();
                                },
                                "searching": false,
                                "order": [[initial_sort, "desc"]],
                                columnDefs: [
                                    {targets: [0, 1, 2], sortable: false},
                                    {targets: '_all', type: 'natural', sortable: true, "orderSequence": ["desc"]}
                                ],

                            });
                        }
                    });
                }
            }
        });







        //////////////////////

    }
/*
    // Event listener to the range filtering inputs to redraw on input
    jQuery('#go_page_user_go_sections_select, #go_page_user_go_groups_select').change( function() {
        leaderboard.draw();
    });*/

}

function go_all_user_list() {
    console.log("go_all_user_list");

    if (jQuery("#go_leaders_datatable_all").length == 0 ) {

        var loader_html = go_loader_html('big');
        jQuery("#social_users_datatable_container").html(loader_html);

        var nonce = GO_EVERY_PAGE_DATA.nonces.go_make_leaderboard;
        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                is_frontend: is_frontend,
                action: 'go_make_leaderboard',
                show_all: true,
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
                    jQuery('#social_users_datatable_container').html(res).promise().done(function () {
                        var nonce = GO_FRONTEND_DATA.nonces.go_leaderboard_dataloader_ajax;
                        leaderboard_all = jQuery('#go_leaders_datatable_all').DataTable({
                            "processing": true,
                            "serverSide": true,
                            "ajax": {
                                "url": MyAjax.ajaxurl + '?action=go_leaderboard_dataloader_ajax',
                                "data": function (d) {
                                    var section_value = jQuery('#go_reader_user_go_sections_select').val();
                                    var group_value = jQuery('#go_reader_user_go_groups_select').val();
                                    var badge_value = jQuery('#go_reader_go_badges_select').val();
                                    //var show_all = jQuery('#go_show_all_leaderboard').prop('checked');

                                    d.section = section_value;
                                    d.group = group_value;
                                    d.badge = badge_value;
                                    d.show_all = true;
                                    d._ajax_nonce = nonce;
                                }
                            },
                            //"orderFixed": [[4, "desc"]],
                            //"destroy": true,
                            responsive: false,
                            "autoWidth": false,
                            "ordering": false,
                            "paging": true,
                            "drawCallback": function (settings) {
                                go_stats_links();
                            },
                            "searching": false,
                        });
                    });
                }

                /*
                jQuery('#go_page_user_go_sections_select, #go_page_user_go_groups_select').change( function() {
                    console.log("change select2 leaderbaord");
                    leaderboard_all.draw();
                });*/
            }
        });
    }
}

function go_follow_request(target){
    console.log("go_follow_request");
    go_enable_loading( target );
    var user_id = jQuery(target).data('user_id');
    var nonce = GO_FRONTEND_DATA.nonces.go_follow_request;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_follow_request',
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
        success: function (res) {
            go_after_ajax();
            if (-1 !== res) {
                console.log('follow request');
                jQuery(target).replaceWith('Request Pending');
            }
        }
    });
}

function go_follow_request_accept(target){
    console.log("go_follow_request_accept");
    go_enable_loading( target );
    var user_id = jQuery(target).data('user_id');
    var nonce = GO_FRONTEND_DATA.nonces.go_follow_request_accept;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_follow_request_accept',
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
        success: function (res) {
            go_after_ajax();
            if (-1 !== res) {
                console.log('follow accept');
                jQuery(target).remove();
                //jQuery(target).replaceWith("<button onclick='go_follow_remove_follower(this)' data-user_id='" + user_id + "'>Block</button>");
            }
        }
    });
}

function go_follow_request_deny(target){
    console.log("go_follow_request_deny");

    swal.fire({
        title: "Deny Follow Request",
        text: "Are you sure? They will still be able to see your public posts.",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: 'Remove',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
    })
        .then((result) => {
            if (result.value) {


                var user_id = jQuery(target).data('user_id');
                var nonce = GO_FRONTEND_DATA.nonces.go_follow_request_deny;
                jQuery.ajax({
                    type: 'post',
                    url: MyAjax.ajaxurl,
                    data: {
                        _ajax_nonce: nonce,
                        is_frontend: is_frontend,
                        action: 'go_follow_request_deny',
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
                    success: function (res) {
                        go_after_ajax();
                        if (-1 !== res) {
                            console.log('follow success');
                            jQuery(target).closest('.go_follower').remove();
                        }
                    }
                });

            } else {
            }
        });

}

function go_follow_unfollow(target){
    console.log("go_follow_request");
    swal.fire({
        title: "Unfollow",
        text: "Are you sure you want to unfollow this user.",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: 'Unfollow',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
    })
    .then((result) => {
        if (result.value) {


            var user_id = jQuery(target).data('user_id');
            var nonce = GO_FRONTEND_DATA.nonces.go_follow_unfollow;
            jQuery.ajax({
                type: 'post',
                url: MyAjax.ajaxurl,
                data: {
                    _ajax_nonce: nonce,
                    is_frontend: is_frontend,
                    action: 'go_follow_unfollow',
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
                success: function (res) {
                    go_after_ajax();
                    if (-1 !== res) {
                        console.log('follow success');
                        jQuery(target).replaceWith("<button onclick='go_follow_request(this)' data-user_id='" + user_id + "'>Follow</button>");
                    }
                }
            });

        } else {
        }
    });

}

function go_follow_remove_follower(target){
    console.log("go_follow_request");

    swal.fire({
        title: "Remove Follower",
        text: "Are you sure you want to remove this follower? They will still be able to see your public posts.",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: 'Remove',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
    })
        .then((result) => {
            if (result.value) {


                var user_id = jQuery(target).data('user_id');
                var nonce = GO_FRONTEND_DATA.nonces.go_follow_remove_follower;
                jQuery.ajax({
                    type: 'post',
                    url: MyAjax.ajaxurl,
                    data: {
                        _ajax_nonce: nonce,
                        is_frontend: is_frontend,
                        action: 'go_follow_remove_follower',
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
                    success: function (res) {
                        go_after_ajax();
                        if (-1 !== res) {
                            console.log('follow success');
                            jQuery(target).closest('.go_follower').remove();
                        }
                    }
                });

            } else {
            }
        });



}

function go_followers_list(target){
    console.log("go_followers_list");
    var user_id = jQuery(target).data('user_id');
    var nonce = GO_FRONTEND_DATA.nonces.go_followers_list;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_followers_list',
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
        success: function (res) {
            go_after_ajax();
            if (-1 !== res) {
                jQuery.featherlight(res,
                    {
                        variant: 'go_follow_list',
                        afterContent: function () {

                        }
                    });
            }
        }
    });
}

function go_following_list(target){
    console.log("go_following_list");
    var user_id = jQuery(target).data('user_id');
    var nonce = GO_FRONTEND_DATA.nonces.go_following_list;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_following_list',
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
        success: function (res) {
            go_after_ajax();
            if (-1 !== res) {
                jQuery.featherlight(res,
                    {
                        variant: 'go_follow_list',
                        afterContent: function () {

                        }
                    });
            }
        }
    });
}

function go_make_feed() {

   if (jQuery("#go_feed_container").length == 0 ) {

        var loader_html = go_loader_html('big');
        jQuery("#social_feed_container").html(loader_html);

        var nonce = GO_FRONTEND_DATA.nonces.go_make_feed;

       var section_value = jQuery('#go_reader_user_go_sections_select').val();
       var group_value = jQuery('#go_reader_user_go_groups_select').val();
       var badge_value = jQuery('#go_reader_go_badges_select').val();

        jQuery.ajax({
            type: 'post',
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                is_frontend: is_frontend,
                action: 'go_make_feed',
                section: section_value,
                group: group_value,
                badge: badge_value,

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
                go_after_ajax();
                if (-1 !== res) {
                    jQuery('#social_feed_container').html(res).promise().done(function () {
                        go_activate_reader();
                    });
                }
            }
        });
    }
}