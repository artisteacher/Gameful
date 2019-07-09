jQuery(document).ready(function(){


    if (typeof (is_tools_archive_page) !== 'undefined') {
        if (jQuery('#records_tabs').length) {
            jQuery('#records_tabs').tabs();
        }
        go_blog_archive_datatable();//draw the stats tab on load

        go_make_select2_filter('user_go_sections', false, true);

        go_make_select2_filter('user_go_groups',false, true);

        go_make_select2_filter('go_badges',false, true);

        jQuery(".go_save_icon_multiple_clipboard").parent().off().one("click", function(e){
            go_save_admin_archive();
        });
    }
});


function go_save_admin_archive(){
    console.log('go_save_admin_archive');
    var inputs = jQuery(".go_checkbox:visible");
    //console.log(inputs);
    var archive_vars = [];
    for(var i = 0; i < inputs.length; i++){
        if (inputs[i]['checked'] === true ){
            var uid = (inputs[i]).getAttribute('data-uid');
            archive_vars.push({uid:uid});
        }
    }
    let num_users = archive_vars.length;

    if (num_users <=0){
        Swal.fire({//sw2 OK
            title: "Error",
            text: "No users were selected.",
            type: 'error',
            showCancelButton: false,
        });
        jQuery(".go_save_icon_multiple_clipboard").parent().off().one("click", function(e){
            go_save_admin_archive();
        });
        return;
    }

    //select private or public archive
    Swal.fire({//sw2 OK
        title: "Select Archive Type",
        html: "What type of archive would you like to create? <br><br>A public archive will only have blog posts that are publicly available.<br><br>A private archive includes all posts, including private posts, as well as the feedback.",
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Private Archive',
        cancelButtonText: 'Public Archive',
        focusConfirm: false,
        focusCancel: true,
        reverseButtons: false,
        //buttonsStyling: false,
        cancelButtonColor: '#3085d6'

    })
        .then((result) => {
            if (result.value) {
                var archive_type = 'private';
            } else if (result.dismiss === Swal.DismissReason.cancel){
                var archive_type = 'public';
            }
            //loader
            Swal.fire({//sw2 OK
                title: "Generating Archives . . .",
                showCloseButton: false,
                showCancelButton: false,
                showConfirmButton: false,
                allowEscapeKey: false,
                allowOutsideClick: false,
                html: '<div id="go_archive_bar_border" class="progress-bar-border-swal" style="width: 100%"><div id="go_archive_bar_progress" class="archive_progress_bar" style="width: 0%;"></div></div><div><h3 id="archive_status_text"></h3></div>',
                onBeforeOpen: () => {
                    //Swal.showLoading();
                    //go_archive_progress(num_users, true);
                }

            })

            go_create_user_list(archive_type, archive_vars);
        })
}


function go_create_user_list(archive_type, archive_vars){
    console.log('go_create_user_list');
    //send the ajax with the input from the alert
    var nonce = go_create_user_list_nonce;
    let section = jQuery('#go_page_user_go_sections_select').val();
    let group = jQuery('#go_page_user_go_groups_select').val();
    let badge = jQuery('#go_page_go_badges_select').val();
    //generate_user_list($user_list, $is_private)
    var gotoSend = {
        action:"go_create_user_list",
        archive_type: archive_type,
        is_admin_archive: true,
        archive_vars: archive_vars,
        section: section,
        group: group,
        badge: badge,
        _ajax_nonce: nonce,
        // blog_post_id: blog_post_id,
        // checked: checked
    };
    //jQuery.ajaxSetup({ cache: true });

    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'POST',
        data: gotoSend,
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('error');
            jQuery(".go_save_icon_multiple_clipboard").parent().off().one("click", function(e){
                go_save_admin_archive();
            });
            Swal.fire({//sw2 OK
                title: "Error",
                text: "There was a problem creating your archive.",
                type: 'error',
                showCancelButton: false,
            });
            console.log('error1');
            //return 'error1';
        },
        success: function (raw) {
            console.log(raw);
            if (raw == 0 || raw == '0'){
                Swal.fire({//sw2 OK
                    title: "Error",
                    text: "There was a problem creating your archive.",
                    type: 'error',
                    showCancelButton: false,
                });
                console.log('error2');
                //return 'error2';
            }else {
                console.log('3');

                console.log("archive_vars");
                console.log(archive_vars);
                let error_count = 0;
                var counter = 0;
                var total_users = archive_vars.length;
                go_generate_user_archive(archive_type, archive_vars, 0, total_users, 0 );

            }
        }
    });

}

function go_generate_user_archive(archive_type, archive_vars, i, total_users, error_count){
    //add error log--which users failed
    console.log('go_generate_multi_user_archives');

    console.log("total_users: " + total_users);
    console.log('users_done:' + i)

    //update the progress bar and message
    let percent = (i / total_users) * 100;
    percent = percent + '%';
    jQuery('#go_archive_bar_progress').css('width', percent);
    let message = i + ' of ' + total_users + ' archives created.'
    jQuery("#archive_status_text").html(message);


    var user_id = (archive_vars[i]['uid']);

    //send the ajax with the input from the alert
    var nonce = go_make_user_archive_zip_nonce;
    let section = jQuery('#go_page_user_go_sections_select').val();
    let group = jQuery('#go_page_user_go_groups_select').val();
    let badge = jQuery('#go_page_go_badges_select').val();
    //generate_user_list($user_list, $is_private)
    var gotoSend = {
        action:"go_make_user_archive_zip",
        archive_type: archive_type,
        is_admin_archive: true,
        user_id: user_id,
        section: section,
        group: group,
        badge: badge,
        _ajax_nonce: nonce,
        // blog_post_id: blog_post_id,
        // checked: checked
    };
    //jQuery.ajaxSetup({ cache: true });

    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'POST',
        data: gotoSend,
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('error');

            error_count++;
            //console.log(raw);
            i++;
            if (i < total_users) {
                go_generate_user_archive(archive_type, archive_vars, i, total_users)
            }else{
                go_zip_archive();

                //reactivate button
                jQuery(".go_save_icon_multiple_clipboard").parent().off().one("click", function(e){
                    go_save_admin_archive();
                });
            }
        },
        success: function (raw) {
            if (raw == 0 || raw == '0'){
                error_count++;
            }
            i++;
            if (i < total_users) {
                go_generate_user_archive(archive_type, archive_vars, i, total_users)
            }else{
                jQuery("#archive_status_text").html('Preparing files for download.');
                go_zip_archive();

                //reactivate button
                jQuery(".go_save_icon_multiple_clipboard").parent().off().one("click", function(e){
                    go_save_admin_archive();
                });
            }

        }
    });
}

/*
function go_generate_user_archive2(archive_type, user_id){
    console.log('go_generate_user_archive');
    //send the ajax with the input from the alert
    var nonce = go_make_user_archive_zip_nonce;
    let section = jQuery('#go_page_user_go_sections_select').val();
    let group = jQuery('#go_page_user_go_groups_select').val();
    let badge = jQuery('#go_page_go_badges_select').val();
    //generate_user_list($user_list, $is_private)
    var gotoSend = {
        action:"go_make_user_archive_zip",
        archive_type: archive_type,
        is_admin_archive: true,
        user_id: user_id,
        section: section,
        group: group,
        badge: badge,
        _ajax_nonce: nonce,
        // blog_post_id: blog_post_id,
        // checked: checked
    };
    //jQuery.ajaxSetup({ cache: true });

    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'POST',
        data: gotoSend,

        error: function(jqXHR, textStatus, errorThrown) {
            console.log('error');
            jQuery(".go_save_icon_multiple_clipboard").parent().off().one("click", function(e){
                go_save_admin_archive();
            });
            return 1;
        },
        success: function (raw) {
            console.log(raw);
            if (raw == 0 || raw == '0'){
                return 1;
            }else {

                return 0;
            }
        }
    });
}
*/
/*
function go_archive_progress(num_users, first = true, last_time_out = 0){

    console.log('go_archive_progress');
    var nonce = go_archive_progress_nonce;
    jQuery.ajax({
        type: "POST",
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            action: 'go_archive_progress',
            first: first
            //refresh: refresh,
        },

        error: function(jqXHR, textStatus, errorThrown) {
            echo ('error');
            echo (jqXHR);
            echo(textStatus);
            echo(errorThrown);

            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
        },
        success: function( res ) {
            if(res == 'done'){
                jQuery("#archive_status_text").html('Preparing files for download.');
                console.log('done');
                return;
            }
            console.log("progress");
            console.log(res);
            console.log(num_users);
            let percent = (res / num_users) * 100;
            percent = percent + '%';

            console.log(percent);
            jQuery('#go_archive_bar_progress').css('width', percent);
            let timeout = Math.min(500 + last_time_out, 5000);//slow the checks down for archives that take a long time.
            console.log(timeout);
            setTimeout(function() {
                //your code to be executed after 3 seconds
                go_archive_progress(num_users, false, timeout);
            }, timeout)

        }
    });
}
*/

function go_blog_archive_datatable(refresh) {
    if (jQuery("#go_clipboard_stats_datatable").length == 0  || refresh == true) {
        jQuery("#clipboard_stats_datatable_container").html("<h2>Loading . . .</h2>");
        var nonce = go_clipboard_stats_nonce;
        //console.log("refresh" + refresh);
        //console.log("stats");
        jQuery.ajax({
            type: "post",
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                action: 'go_clipboard_stats',
                refresh: refresh
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
                //console.log("success");
                if (-1 !== res) {
                    jQuery('#clipboard_stats_datatable_container').html(res);

                    Clipboard = jQuery('#go_clipboard_stats_datatable').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": MyAjax.ajaxurl + '?action=go_clipboard_stats_dataloader_ajax',
                            "data": function(d){
                                //d.user_id = jQuery('#go_stats_hidden_input').val();
                                //d.user_id = jQuery('#go_stats_hidden_input').val();
                                d.section = jQuery('#go_page_user_go_sections_select').val();
                                d.group = jQuery('#go_page_user_go_groups_select').val();
                                d.badge = jQuery('#go_page_go_badges_select').val();
                            }
                        },
                        "bPaginate": true,
                        //colReorder: true,
                        "order": [[6, "desc"]],
                        responsive: true,
                        "autoWidth": false,
                        stateSave: true,
                        stateLoadParams: function( settings, data ) {
                            //if (data.order) delete data.order;
                            if (data.search) delete data.search;
                            if (data.start) delete data.start;
                        },
                        "stateDuration": 31557600,
                        searchDelay: 1000,
                        dom: 'lBfrtip',
                        "drawCallback": function( settings ) {
                            go_clipboard_callback();
                            jQuery(".go_save_icon_multiple_clipboard").parent().one("click", function(e){
                                go_save_admin_archive();
                            });

                        },
                        "columnDefs": [
                            { type: 'natural', targets: '_all'  },
                            {
                                "targets": [0],
                                className: 'noVis',
                                "width": "1px",
                                sortable: false
                            },
                            {
                                "targets": [1],
                                className: 'noVis',
                                "width": "20px",
                                sortable: false
                            },
                            {
                                "targets": [7],
                                //className: 'noVis',
                                sortable: false
                            },
                            {
                                "targets": [8],
                                //className: 'noVis',
                                //sortable: false
                            },
                            {
                                "targets": [13],
                                //className: 'noVis',
                                //sortable: false
                            }
                        ],
                        buttons: [
                            {
                                text: '<span class="go_save_icon_multiple_clipboard">Archive Selected Users <i class="fas fa-save" aria-hidden="true"></i><span>',
                                action: function ( e, dt, node, config ) {

                                }

                            },
                            {
                                extend: 'colvis',
                                columns: ':not(.noVis)',
                                postfixButtons: ['colvisRestore'],
                                text: 'Column Visibility'
                            }


                        ]
                    });
                    //Filter the table
                    //go_filter_clipboard_datatables(true);
                    //redraw table
                    //Clipboard.draw();
                }
            }
        });
    }else{
        go_clipboard_callback();
        jQuery(".go_save_icon_multiple_clipboard").parent().one("click", function(e){
            go_save_admin_archive();
        });
    }
}