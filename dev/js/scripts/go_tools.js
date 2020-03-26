
jQuery(document).ready(function(){
    //add on click
    //jQuery('#go_tool_update').one("click", function() {go_update_go_ajax();});
    //jQuery('#go_tool_update_no_loot').one("click", function() {go_update_go_ajax_no_task_loot();});
    if (typeof (go_is_tools) !== 'undefined') {
        console.log("tools page loaded");
        jQuery('#go_reset_all_users').one("click", function () {
            go_reset_all_users_dialog();
        });

        jQuery('#go_flush_all_permalinks').one("click", function () {
            go_flush_all_permalinks_dialog();
        });

        jQuery('#go_disable_game_on_this_site').one("click", function () {
            go_disable_game_on_this_site_dialog();
        });
       // jQuery('#go_export_game').one("click", function () {
         //   go_export_wp();
        //});
        //jQuery('#go_tool_update_v5').one("click", function() {go_update_go_ajax_v5_check();});

        jQuery("#go_use_beta").change(function() {
            go_use_beta();
        });
    }


});

function go_use_beta(){
    var nonce = GO_ADMIN_PAGE_DATA.nonces.go_use_beta;

    if (jQuery('#go_use_beta').is(":checked")) {
        var go_use_beta = 1;
    }
    else{
         var go_use_beta = 0;
    }
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_use_beta',
            go_use_beta: go_use_beta
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
            if (res === 'success') {
                if(go_use_beta) {
                    swal.fire("Success", "You are now using the beta code.", "success");
                }
                else{
                    swal.fire("Success  ", "You are now using the regular code.", "success");
                }
            }

        }
    });
}

function go_flush_all_permalinks_dialog(){
    console.log('go_flush_all_permalinks_dialog');
    swal.fire({
        title: "Flush all permalinks",
        text: "This is for the entire network.",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: 'Flush Away',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
    })
        .then((result) => {
            if (result.value) {
                go_flush_all_permalinks();

            } else {
                swal.fire({
                        title: "No action taken."
                    }
                );
                jQuery('#go_flush_all_permalinks').one("click", function() {go_flush_all_permalinks_dialog();});



            }
        });
}

function go_flush_all_permalinks(){
    var nonce = GO_ADMIN_PAGE_DATA.nonces.go_flush_all_permalinks;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_flush_all_permalinks'
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
            if (res === 'flushed') {
                swal.fire("Success", "Permalinks were flushed.", "success");
                jQuery('#go_flush_all_permalinks').one("click", function () {
                    go_flush_all_permalinks_dialog();
                });
            }
            else{
                swal.fire("Error", "There was an error. Please refresh the page and try again. No data was changed.", "error");

            }

        }
    });
}

function go_disable_game_on_this_site_dialog(){
    swal.fire({
        title: "Disable Game Features",
        text: "Are you sure? This can't be undone!",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: 'Disable',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
    })
        .then((result) => {
            if (result.value) {
                go_disable_game_on_this_site();

            } else {
                swal.fire({
                        text: "Your user data is safe.",
                        title: "No action taken."
                    }
                );
                jQuery('#go_disable_game_on_this_site').one("click", function() {go_disable_game_on_this_site_dialog();});



            }
        });
}

function go_disable_game_on_this_site(){
    var nonce = GO_ADMIN_PAGE_DATA.nonces.go_disable_game_on_this_site;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_disable_game_on_this_site'
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
            if (res = 'disabled') {
                swal.fire("Success", "Features are disabled.", "success");
                jQuery('#go_disable_game_on_this_site').one("click", function () {
                    go_disable_game_on_this_site_dialog();
                });
            }
            else{
                swal.fire("Error", "There was an error. Please refresh the page and try again. No data was changed.", "error");

            }

        }
    });
}

function go_reset_all_users_dialog (){

    swal.fire({
        title: "Reset User Game Data",
        html: "Are you sure? This can't be undone!<br><br>" +
            "All user history, progress, and loot will be removed." +
            " Users will not show on the clipboard until the visit the site again." +
            " It's like they have never played the game.",
        type: "error",
        showCancelButton: true,
        confirmButtonText: 'Reset All User Game Data',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        focusCancel: true,
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
    var nonce = GO_ADMIN_PAGE_DATA.nonces.go_reset_all_users;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
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
