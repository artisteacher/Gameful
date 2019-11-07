function go_delete_temp_archive(){
    var nonce = go_delete_temp_archive_nonce;

    console.log("go_delete_temp_archive ");
    var gotoSend = {
        action:"go_delete_temp_archive",
        _ajax_nonce: nonce,
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
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
        },
        success: function (raw) {
            console.log('temp directory removed');
        }
    });
}

function go_zip_archive(){
    console.log('go_zip_archive');
    var nonce = go_zip_archive_nonce;
    //generate_user_list($user_list, $is_private)
    var gotoSend = {
        action:"go_zip_archive",
        _ajax_nonce: nonce,
    };

    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'POST',
        data: gotoSend,
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('go_zip_archive_error');
            return 'error';
        },
        success: function (raw) {
            console.log(raw);
            if (raw == 0 || raw == '0'){
                Swal.fire({//sw2 OK
                    title: "Error",
                    text: "There was a problem creating your archive. Error during the compression process.",
                    type: 'error',
                    showCancelButton: false,
                });
                //delete archive folder
                go_delete_temp_archive();
            }else {
                console.log(raw);
                window.location = raw;
                Swal.fire({//sw2 OK
                    title: "Success",
                    text: "Your archive was created.  It should be in your download folder. To view the archive, unzip it and open the index file.",
                    type: 'success',
                    showCancelButton: false,
                });

                //delete archive folder
                go_delete_temp_archive();


            }
        }
    });
}
