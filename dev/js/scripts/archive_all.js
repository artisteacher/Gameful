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