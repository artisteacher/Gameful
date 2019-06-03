jQuery(document).ready(function(){
    if (typeof (is_login_page) !== 'undefined') {
        jQuery('#go_save_archive').one("click", function (e) {
            go_save_archive(this);
        });
    }
});


function go_save_archive(){
    console.log("go_save_archive");

    Swal.fire({//sw2 OK
        title: "Create Archive",
        text: "Choose an option below",
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Create Archive',
        cancelButtonText: 'No, cancel!',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },

    })
        .then((result) => {
            if (result.value) {
                Swal.fire({//sw2 OK
                    title: "What type of archive would you like to create?",
                    text: "A public archive will only have blog posts that are publicly available. A private archive includes all posts, including private posts, as well as the feedback.",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Public Archive',
                    cancelButtonText: 'Private Archive',
                    focusConfirm: true,
                    focusCancel: false,
                    reverseButtons: true,
                    //buttonsStyling: false,
                    cancelButtonColor: '#3085d6'

                })
                    .then((result) => {
                        if (result.value) {
                            var archive_type = 'public';
                        } else if (result.dismiss === Swal.DismissReason.cancel){
                            var archive_type = 'private';
                        }
                        //loader
                        Swal.fire({//sw2 OK
                            title: "Generating Archive . . .",
                            text: "",
                            onBeforeOpen: () => {
                                Swal.showLoading()
                            }

                        })

                        //send the ajax with the input from the alert
                        var nonce = go_make_user_archive_zip_nonce;
                        var gotoSend = {
                            action:"go_make_user_archive_zip",
                            archive_type: archive_type,
                            is_admin_archive: false,
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
                                Swal.fire({//sw2 OK
                                    title: "Error",
                                    text: "There was a problem creating your archive.",
                                    type: 'error',
                                    showCancelButton: false,
                                });
                                console.log('errors');
                                console.log(jqXHR);
                                console.log(textStatus);
                                console.log(errorThrown);
                                jQuery('#go_save_archive').one("click", function (e) {
                                    go_save_archive(this);
                                });

                            },
                            success: function (raw) {
                                if (raw === 0 || raw === '0'){
                                    Swal.fire({//sw2 OK
                                        title: "Error",
                                        text: "There was a problem creating your archive.",
                                        type: 'error',
                                        showCancelButton: false,
                                    })
                                }else {

                                    go_zip_archive();
                                    jQuery('#go_save_archive').one("click", function (e) {
                                        go_save_archive(this);
                                    });

                                }

                            }
                        });
                    })
            }else {
                jQuery('#go_save_archive').one("click", function (e) {
                    go_save_archive(this);
                });
            }
        });
}

