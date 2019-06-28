jQuery(document).ready(function(){
    //add on click
    //jQuery('#go_tool_update').one("click", function() {go_update_go_ajax();});
    //jQuery('#go_tool_update_no_loot').one("click", function() {go_update_go_ajax_no_task_loot();});
    jQuery('#go_reset_all_users').one("click", function() {go_reset_all_users_dialog();});
    //jQuery('#go_tool_update_v5').one("click", function() {go_update_go_ajax_v5_check();});


});

/*
function go_update_go_ajax_v5_check (){
    console.log('go_update_go_ajax_v5_check');
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_update_go_ajax_v5_check;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            action: 'go_update_go_ajax_v5_check',
            loot: true
        },
        success: function( res ) {
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;


            if(res == 'run_again'){
                swal.fire({
                        title: "Warning",
                        text: "This upgrade has been run before.  Running again will overwrite work you have done in version 5 with old v4 data. Are you sure you wish to proceed."
                    }
                );

                swal.fire({//sw2 OK
                    title: "Warning",
                    html: "This upgrade has been run before.  Running again will overwrite work you have done in version 5 with old v4 data. Are you sure you wish to proceed.",
                    type: 'warning',
                    showCancelButton: true,
                    showConfirmButton: true,
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        swal.fire({
                                text: "This may take a minute.  Please keep this window open."
                            }
                        ).then((result) => {
                            if (result.value) {
                                go_update_go_ajax_v5 ();
                            }
                        });
                    }
                });

            }else {
                swal.fire({
                        title: "This may take a minute.  Please keep this window open."
                    }
                ).then((result) => {
                    if (result.value) {
                        go_update_go_ajax_v5 ();
                    }
                });
            }
        }
    });
}

function go_update_go_ajax_v5 (){
    console.log('go_update_go_ajax_v5');
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_update_go_ajax_v5;
    swal.fire({
            title: "Updating",
            html: "<div><i class=\"fas fa-spinner fa-3x fa-pulse\"></i></div>",
            showCancelButton: false, // There won't be any cancel button
            showConfirmButton: false,
            allowOutsideClick: false
        }
    );

    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            action: 'go_update_go_ajax_v5',
            loot: true
        },
        success: function( raw ) {
            let error = go_ajax_error_checker(raw);
            if (error == 'true') return;

            swal.fire({
                    title: "Success"
                }
            );
            //jQuery('#go_tool_update_v5').one("click", function() {go_update_go_ajax_v5_check();});

            location.reload();

        }
    });
}

function go_update_go_ajax (){
    console.log('go_update_go_ajax');
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_upgade4;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            action: 'go_upgade4',
            loot: true
        },
        success: function( res ) {
            swal.fire({
                    title: "Done.  Hope that helps :)"
                }
            );
        }
    });
}

function go_update_go_ajax_no_task_loot (){
    console.log('go_update_go_ajax_no_task_loot');
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_upgade4;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            action: 'go_upgade4',
            loot: false
        },
        success: function( res ) {
            //alert ("")
            swal.fire({
                    title: "Done.  Hope that helps :)"
                }
            );
        }
    });
}
*/

function go_reset_all_users_dialog (){

    swal.fire({
        title: "Reset User Game Data",
        text: "Are you sure? This can't be undone!",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: 'Reset All User Game Data',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
    })
    .then((result) => {
        if (result.value) {
            go_reset_all_users ();

        } else {
            swal.fire({
                    text: "Your user data is safe.",
                    title: "No action taken."
                }
            );
            jQuery('#go_reset_all_users').one("click", function() {go_reset_all_users_dialog();});



        }
    });
    
}

function go_reset_all_users (){
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_reset_all_users;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            action: 'go_reset_all_users'
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
            if (res = 'reset') {
                swal.fire("Success", "All user game data was reset.", "success");
                jQuery('#go_reset_all_users').one("click", function () {
                    go_reset_all_users_dialog();
                });
            }
            else{
                swal.fire("Error", "There was an error. Please refresh the page and try again. No data was changed.", "error");

            }

        }
    });
}

