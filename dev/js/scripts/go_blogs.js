/*
function tinymce_updateCharCounter(el, len) {
    jQuery('.char_count').text(len + '/' + '500');
}

function tinymce_getContentLength() {
    var len = tinymce.get(tinymce.activeEditor.id).contentDocument.body.innerText.length;
    console.log(len);
    return len;
}
*/
jQuery( document ).ready( function() {
//jQuery(window).bind("load", function() {

    //console.log("jQuery is loaded");

    jQuery("#go_show_private").one("click", function (e) {
        go_show_private(this);
    });

    jQuery('#go_hidden_mce').remove();
    jQuery('#go_hidden_mce_edit').remove();

    go_blog_new_posts();

    jQuery('#go_save_archive').one("click", function (e) {
        go_save_archive(this);
    });

    //go_blog_tags_select2();


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
                        //var nonce = GO_EVERY_PAGE_DATA.nonces.go_blog_favorite_toggle;
                        var gotoSend = {
                            action:"go_make_user_archive_zip",
                            archive_type: archive_type,
                            // _ajax_nonce: nonce,
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
                                if (jqXHR.status === 400){
                                    jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
                                }
                            },
                            success: function (raw) {
                                console.log(raw);
                                if (raw == 0 || raw == '0'){
                                    Swal.fire({//sw2 OK
                                        title: "Error",
                                        text: "There was a problem creating your archive.",
                                        type: 'error',
                                        showCancelButton: false,
                                    })
                                }else {

                                    window.location = raw;
                                    Swal.fire({//sw2 OK
                                        title: "Success",
                                        text: "Your archive was created.  It should be in your download folder. To view the archive, unzip it and open the index file.",
                                        type: 'success',
                                        showCancelButton: false,
                                    })
                                    go_delete_temp_archive();
                                }
                            }
                        });
                    })
            }
        });
}

function go_delete_temp_archive(){
   // var nonce = GO_EVERY_PAGE_DATA.nonces.go_blog_favorite_toggle;

    console.log("go_delete_temp_archive ");
    var gotoSend = {
        action:"go_delete_temp_archive",
        //_ajax_nonce: nonce,
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


function go_blog_new_posts(){
    console.log('go_blog_new_posts');
    go_lightbox_blog_img();

    go_disable_loading();

    jQuery( ".feedback_accordion" ).accordion({
        collapsible: true,
        active: false,
        heightStyle: "content"
    });
}

function  go_blog_favorite(target){
    blog_post_id = jQuery( target ).attr( 'data-post_id' );


    if (jQuery(target).is(":checked"))
    {
        var checked = true;
    }else{
        checked = false;
    }

    var nonce = GO_EVERY_PAGE_DATA.nonces.go_blog_favorite_toggle;

    //console.log("favorite_id: " + blog_post_id);
    var gotoSend = {
        action:"go_blog_favorite_toggle",
        _ajax_nonce: nonce,
        blog_post_id: blog_post_id,
        checked: checked
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

        }
    });
}

function go_blog_tags_select2(){
    jQuery('.go_feedback_go_blog_tags_select').select2({
        ajax: {
            url: MyAjax.ajaxurl, // AJAX URL is predefined in WordPress admin
            dataType: 'json',
            delay: 400, // delay in ms while typing when to perform a AJAX search
            data: function (params) {

                return {
                    q: params.term, // search query
                    action: 'go_make_taxonomy_dropdown_ajax', // AJAX action for admin-ajax.php
                    taxonomy: 'go_blog_tags',
                    is_hier: false
                };


            },
            processResults: function( data ) {

                jQuery(".go_feedback_go_blog_tags_select").select2("destroy");
                jQuery('.go_feedback_go_blog_tags_select').children().remove();
                jQuery(".go_feedback_go_blog_tags_select").select2({
                    data: data,
                    placeholder: "Show All",
                    allowClear: true}).trigger("change");
                jQuery(".go_feedback_go_blog_tags_select").select2("open");
                return {
                    results: data
                };

            },
            cache: true
        },
        minimumInputLength: 0, // the minimum of symbols to input before perform a search
        multiple: true,
        placeholder: "Show All",
        allowClear: true
    });
}

//on_task is used to determine where to show the error message
//it could be on the page or in a lightbox
//why not just have one place?--fix this
function task_stage_check_input( target, on_task) {

    console.log('task_stage_check_input');
    //disable button to prevent double clicks
    go_enable_loading( target );

    //BUTTON TYPES
    //Abandon
    //Start Timer
    //Continue
    //Undo
    //Repeat
    //Undo Repeat --is this different than just undo

    //Continue or Complete button needs to validate input for:
    ////quizes
    ///URLs
    ///passwords
    ///uploads

    //if it passes validation:
    ////send information to php with ajax and wait for a response

    //if response is success
    ////update totals
    ///flash rewards and sounds
    ////update last check
    ////update current stage and check


    //v4 Set variables
    var button_type = "";
    if ( 'undefined' !== typeof jQuery( target ).attr( 'button_type' ) ) {
        button_type = jQuery( target ).attr( 'button_type' )
        console.log("button_type: " + button_type);
    }

    var task_status = "";
    if ( 'undefined' !== typeof jQuery( target ).attr( 'status' ) ) {
        task_status = jQuery( target ).attr( 'status' )
    }

    var check_type = "";
    if ( 'undefined' !== typeof jQuery( target ).attr( 'check_type' ) ) {
        check_type = jQuery( target ).attr( 'check_type' )
        console.log("Check Type: " + check_type);
    }
    var fail = false;
    jQuery('.go_error_msg').text("");
    //jQuery('#go_blog_error_msg').text("");
    var error_message = '<ul style=" text-align: left;"> ';

    var suffix = jQuery( target ).attr( 'blog_suffix' );
    console.log ("suffix: " + suffix);



    ///v4 START VALIDATE FIELD ENTRIES BEFORE SUBMIT
    //if (button_type == 'continue' || button_type == 'complete' || button_type =='continue_bonus' || button_type =='complete_bonus') {
    const required_elements = {};
    if ( check_type == 'blog' || check_type == 'blog_lightbox') {

        const text_toggle = jQuery(target).attr('text_toggle');
        let blog_div = "";
        if (check_type == 'blog_lightbox' ){

            console.log('lb');
            blog_div = jQuery(target).parent();
        }else{
            blog_div = jQuery(target).parent().prev();
        }

        jQuery('.go_blog_element_error').remove();
        jQuery(blog_div).find('.go_blog_element_input').each(
            function(  ) {
                console.log('check element');
                const type = jQuery(this).attr('data-type');
                const uniqueID = jQuery(this).attr('data-uniqueID');

                if (type ==='URL'){
                    const required_string = jQuery(this).attr('data-required');
                    const URL_error = "<span class='go_blog_element_error' style='color: red;'><br>Enter a valid URL.</span>";
                    ///
                    //const the_url =jQuery(this).val().replace(/\s+/, '');
                    const the_url =jQuery(this).val();
                    required_elements[uniqueID] = the_url;
                    console.log("URL" + the_url);
                    if (the_url.length > 0) {
                        if (the_url.match(/^(http:\/\/|https:\/\/).*\..*$/) && !(the_url.lastIndexOf('http://') > 0) && !(the_url.lastIndexOf('https://') > 0)) {
                            if ( check_type == 'blog' || check_type == 'blog_lightbox') {
                                if ((the_url.indexOf(required_string) == -1) ){
                                    error_message += "<li>Enter a valid URL. The URL must contain \"" + required_string + "\".</li>";
                                    fail = true;
                                    jQuery(this).after(URL_error);
                                }
                            }
                        } else {

                            error_message += "<li>Enter a valid URL.</li>";
                            fail = true;
                            jQuery(this).after(URL_error);

                        }
                    } else {
                        error_message += "<li>Enter a valid URL.</li>";
                        go_disable_loading();
                        fail = true;
                        jQuery(this).after(URL_error);
                    }
                }
                if (type ==='video'){
                    const the_url =jQuery(this).val().replace(/\s+/, '');
                    required_elements[uniqueID] = the_url;
                    const video_error = "<span class='go_blog_element_error' style='color: red;'><br>Enter a valid video URL. YouTube and Vimeo are supported.</span>";
                    console.log("videoURL" + the_url);
                    if (the_url.length > 0) {
                        if (the_url.match(/^(http:\/\/|https:\/\/).*\..*$/) && !(the_url.lastIndexOf('http://') > 0) && !(the_url.lastIndexOf('https://') > 0)) {
                            if ((the_url.search("youtube") == -1) && (the_url.search("vimeo") == -1)) {
                                error_message += "<li>Enter a valid video URL. YouTube and Vimeo are supported.</li>";
                                fail = true;
                                jQuery(this).after(video_error);

                            }
                        } else {
                            error_message += "<li>Enter a valid video URL. YouTube and Vimeo are supported.</li>";
                            fail = true;
                            jQuery(this).after(video_error);
                        }
                    } else {
                        error_message += "<li>Enter a valid video URL. YouTube and Vimeo are supported.</li>";
                        fail = true;
                        jQuery(this).after(video_error);
                    }
                }

                if (type ==='file'){
                    console.log("file result:");
                    const result = jQuery(this).attr('value');
                    required_elements[uniqueID] = result;
                    //alert(result);
                    console.log(result);
                    const file_error = "<br><span class='go_blog_element_error' style='color: red;'><br>Please attach a file.</span>";

                    //var result = jQuery("#go_result").attr('value');
                    if (typeof (result) == 'undefined') {
                        console.log('undefined');
                        error_message += "<li>Please attach a file.</li>";
                        fail = true;
                        jQuery(this).after(file_error);
                    }

                }

            }
        );

        /*
        if(video_toggle == '1') {
            var the_url = jQuery(go_result_video).attr('value').replace(/\s+/, '');
            console.log(the_url);

            if (the_url.length > 0) {
                if (the_url.match(/^(http:\/\/|https:\/\/).*\..*$/) && !(the_url.lastIndexOf('http://') > 0) && !(the_url.lastIndexOf('https://') > 0)) {
                    if ((the_url.search("youtube") == -1) && (the_url.search("vimeo") == -1)) {
                        error_message += "<li>Enter a valid video URL. YouTube and Vimeo are supported.</li>";
                        fail = true;
                    }
                } else {
                    error_message += "<li>Enter a valid video URL. YouTube and Vimeo are supported.</li>";
                    fail = true;
                }
            } else {
                error_message += "<li>Enter a valid video URL. YouTube and Vimeo are supported.</li>";
                fail = true;
            }
        }
        */
        if(text_toggle  == '1') {
            //Word count validation
            var min_words = jQuery(target).attr('min_words'); //this variable is used in the other functions as well
            //alert("min Words: " + min_words);
            var my_words = tinymce_getContentLength_new(check_type);
            //var bb = tinymce.get(tinymce.activeEditor.id);
            //console.log(bb);
            if (my_words < min_words) {
                error_message += "<li>Your post is not long enough. There must be " + min_words + " words minimum. You have " + my_words + " words.</li>";
                fail = true;
                jQuery(target).closest(".go_checks_and_buttons").find("#go_save_button").trigger('click');
            }

        }


    }
    else if (check_type === 'password' || check_type == 'unlock') {
        var pass_entered = jQuery('#go_result').attr('value').length > 0 ? true : false;
        if (!pass_entered) {
            error_message += "Retrieve the password from " + go_task_data.admin_name + ".";
            fail = true;
        }
    }
    else if (check_type == 'quiz') {
        var test_list = jQuery(target).closest(".go_checks_and_buttons").find(" .go_test_list");
        //console.log("test_list.length: " + test_list.length);
        if (test_list.length >= 1) {
            var checked_ans = 0;
            for (var i = 0; i < test_list.length; i++) {
                var obj_str = "#go_test_container_" + (parseFloat(task_status)+1) + " .go_test_" + i + " input:checked";
                console.log("obj_str: " + obj_str);
                var chosen_answers = jQuery(obj_str);
                if (chosen_answers.length >= 1) {
                    checked_ans++;
                }
            }
           // console.log("checked_ans:" + checked_ans );

            //if all questions were answered
            if (checked_ans >= test_list.length) {
                go_quiz_check_answers(task_status, target);
                return;


            }
            //else print error message
            else if (test_list.length > 1) {
                error_message +="<li>Please answer all questions!</li>";
                fail = true;
            }
            else {
                error_message += "<li>Please answer the question!</li>";
                fail = true;
            }
        }
    }
    //}
    error_message += "</ul>";
    if (fail === true){

        //jQuery('.go_error_msg').append(error_message);
        //jQuery('.go_error_msg').show();
        if (on_task == true) {
            //console.log("error_stage");
            //console.log("message:" + error_message);
            //flash_error_msg('#go_stage_error_msg');
            jQuery('#go_blog_stage_error_msg').append(error_message);
            jQuery('#go_blog_stage_error_msg').show();
        }else {
            //console.log("error_blog");
            jQuery('#go_blog_error_msg').append(error_message);
            jQuery('#go_blog_error_msg').show();
        }



        console.log("error validation");
        /*
        new Noty({
                type: 'error',
                layout: 'center',
                text: error_message,
                theme: 'relax',
                timeout: '5000',
                visibilityControl: true,
            }).show();
            */

        swal.fire({//sw2 OK
            title: 'There are errors with your submission.',
                html: error_message,
                type: 'error'
            }
        );

        go_disable_loading();
        //console.log('disable loading');
        return;
    }else{
        jQuery('#go_blog_stage_error_msg').hide();
        jQuery('#go_blog_error_msg').hide();
    }

    if (on_task == true) {
        task_stage_change(target, required_elements);
    }else{ //this was a blog submit button in a lightbox, so just save without changing stage.
        go_blog_submit( target, true, required_elements );
    }
    //go_disable_loading();
}

// disables the target stage button, and adds a loading gif to it
function go_enable_loading( target ) {
    //prevent further events with this button
    //jQuery('#go_button').prop('disabled',true);
    // prepend the loading gif to the button's content, to show that the request is being processed
    //jQuery('.go_loading').remove();

    //console.log("target");
   // console.log(target.innerHTML);

    if (jQuery(target).hasClass('go_button_round')){
        jQuery(target).find('.go_round_inner').html("<i class='fas fa-spinner fa-pulse'></i>");
    }else{
        target.innerHTML = '<span class="go_loading"><i class="fas fa-spinner fa-pulse"></i></span> ' + target.innerHTML;
        //alert("loading");
    }

}

// re-enables the stage button, and removes the loading gif
function go_disable_loading( ) {
    console.log ("go_disable_loading");
    jQuery('.go_loading').remove();
    jQuery('#go_save_button .go_round_inner').html("<i class='fas fa-save'></i>");
    jQuery('.go_blog_trash .go_round_inner').html("<i class='fas fa-trash'></i>");

    jQuery('.go_blog_opener_round .go_round_inner').html("<i class='fas fa-pencil-alt'></i>");

    jQuery('#go_button').off().one("click", function(e){
        task_stage_check_input( this, true );
    });
    jQuery('#go_back_button').off().one("click", function(e){
        task_stage_change(this);
    });

    jQuery('#go_save_button').off().one("click", function(e){
        //task_stage_check_input( this, false );
        go_blog_submit( this, false );//disable loading
    });


    jQuery( "#go_bonus_button" ).off().one("click", function(e) {
        go_update_bonus_loot(this);
    });

    //console.log("opener4");
    jQuery(".go_blog_opener").off().one("click", function(e){
        go_blog_opener( this );
    });

    jQuery(".go_blog_trash").off().one("click", function(e){
        go_blog_trash( this );
    });


    jQuery("#go_blog_submit").off().one("click", function(e){
        task_stage_check_input( this, false );//disable loading
    });

    //add active class to checks and buttons
    jQuery(".progress").closest(".go_checks_and_buttons").addClass('active');

    jQuery(".go_blog_favorite").off().click(function() {
        go_blog_favorite(this);
    });

    jQuery('.go_str_item').off().one("click", function(e){
        go_lb_opener( this.id );
    });

}

function tinymce_getContentLength_new(source) {
    //var b = jQuery(target).closest(".go_checks_and_buttons").find('.mce-content-body').hide();
    //console.log(b);
    //var b = tinymce.get(tinymce.activeEditor.id).contentDocument.body.innerText;

    if (source == 'blog_lightbox'){
        //var b = tinymce.get('go_blog_post_lightbox').contentDocument.body.innerText;
        var b = go_tmce_getContent('go_blog_post_lightbox');

    }else {
        //var b = tinymce.get('go_blog_post').contentDocument.body.innerText;
        var b = go_tmce_getContent('go_blog_post');
    }
    var e = 0;
    if (b) {
        b = b.replace(/\.\.\./g, " "),
            b = b.replace(/<.[^<>]*?>/g, " ").replace(/&nbsp;|&#160;/gi, " "),
            b = b.replace(/(\w+)(&#?[a-z0-9]+;)+(\w+)/i, "$1$3").replace(/&.+?;/g, " "),
            b = b.replace(/[0-9.(),;:!?%#$?\x27\x22_+=\\\/\-]*/g, "");
        var f = b.match(/[\w\u2019\x27\-\u00C0-\u1FFF]+/g);
        f && (e = f.length)
    }
    return e
}

function go_blog_opener( el ) {
    console.log ("go_blog_opener");
    go_enable_loading( el );
    jQuery("#go_hidden_mce").remove();
    jQuery(".go_blog_opener").prop("onclick", null).off("click");

    var check_for_understanding= jQuery( el ).attr( 'data-check_for_understanding' );

    //var result_title = jQuery( this ).attr( 'value' );
    var blog_post_id= jQuery( el ).attr( 'blog_post_id' );
    //console.log(el);
    //console.log(blog_post_id);
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_blog_opener;
    var gotoSend = {
        action:"go_blog_opener",
        _ajax_nonce: nonce,
        blog_post_id: blog_post_id,
        check_for_understanding: check_for_understanding
    };
    //jQuery.ajaxSetup({ cache: true });
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'POST',
        data: gotoSend,
        cache: false,
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            go_disable_loading();
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
        },
        success: function (results) {
            if (results == 'login'){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            };
            if (results == 'locked'){
                var title = jQuery(el).closest(".go_blog_post_wrapper").find('.go_post_title').html();
                swal.fire({//sw2 OK
                    title: "Locked",
                    html: 'This post is locked. It must be unlocked before this blog post can be edited. Click on the blog post title to go to view the lock.',
                    type: 'warning',
                });
                go_disable_loading();
                return;
            };
            //console.log(results);
            //tinymce.execCommand('mceRemoveEditor', true, 'go_blog_post_edit');
            //tinymce.execCommand( 'mceAddEditor', true, 'go_blog_post_edit' );

            jQuery.featherlight(results, {afterContent: function(){
                    console.log("aftercontent");

                    jQuery( 'body' ).attr( 'data-go_blog_saved', '0' );
                    jQuery( 'body' ).attr( 'data-go_blog_updated', '0' );

                    jQuery("#go_result_url_lightbox, #go_result_video_lightbox").change(function() {
                        jQuery('body').attr('data-go_blog_updated', '1');
                    });

                    jQuery('.go_frontend-button').on("click", function() {
                        jQuery('body').attr('data-go_blog_updated', '1');
                    });

                    //tinymce.init(tinyMCEPreInit.mceInit['go_result_lightbox']);
                    //https://stackoverflow.com/questions/25732679/load-wordpress-editor-via-ajax-plugin
                    var fullId = 'go_blog_post_lightbox';
                    //tinymce.execCommand('mceRemoveEditor', true, 'go_blog_post_lightbox');
                    tinymce.execCommand('mceRemoveEditor', true, fullId);


                    //quicktags({id :'go_blog_post_lightbox'});

                    quicktags({id : fullId});
                    // use wordpress settings
                    tinymce.init({
                        selector: fullId,
                         // change this value according to your HTML
                        setup: function(editor) {
                            editor.on('keyup', function(e) {
                                jQuery('body').attr('data-go_blog_updated', '1');
                                console.log("updated");
                            });
                        },
                        branding: false,
                        theme:"modern",
                        skin:"lightgray",
                        language:"en",
                        formats:{
                            alignleft: [
                                {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign:'left'}},
                                {selector: 'img,table,dl.wp-caption', classes: 'alignleft'}
                            ],
                            aligncenter: [
                                {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign:'center'}},
                                {selector: 'img,table,dl.wp-caption', classes: 'aligncenter'}
                            ],
                            alignright: [
                                {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign:'right'}},
                                {selector: 'img,table,dl.wp-caption', classes: 'alignright'}
                            ],
                            strikethrough: {inline: 'del'}
                        },
                        relative_urls:false,
                        remove_script_host:false,
                        convert_urls:false,
                        browser_spellcheck:true,
                        fix_list_elements:true,
                        entities:"38,amp,60,lt,62,gt",
                        entity_encoding:"raw",
                        keep_styles:false,
                        paste_webkit_styles:"font-weight font-style color",
                        preview_styles:"font-family font-size font-weight font-style text-decoration text-transform",
                        wpeditimage_disable_captions:false,
                        wpeditimage_html5_captions:true,
                        plugins:"charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,wordcount",
                        selector:"#" + fullId,
                        resize:"vertical",
                        menubar:false,
                        wpautop:true,
                        wordpress_adv_hidden:false,
                        indent:false,
                        toolbar1:"formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,wp_more,spellchecker,fullscreen,wp_adv",
                        toolbar2:"strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
                        toolbar3:"",
                        toolbar4:"",
                        tabfocus_elements:":prev,:next",
                        body_class:"id post-type-post post-status-publish post-format-standard",});
                    // this is needed for the editor to initiate
                    tinyMCE.execCommand('mceAddEditor', false, fullId);


                    //tinymce.execCommand( 'mceAddEditor', true, 'go_blog_post_lightbox' );
                    //tinyMCE.execCommand("mceAddControl", false, 'go_blog_post_lightbox');

                    //tinymce.execCommand( 'mceToggleEditor', true, 'go_blog_post_lightbox' );
                    //tinymce.execCommand( 'mceToggleEditor', true, 'go_blog_post_edit' );

                },
                beforeClose: function() {
                    console.log("beforeClose");
                    //var go_blog_saved= jQuery( 'body' ).attr( 'data-go_blog_saved' );
                    var go_blog_updated= jQuery( 'body' ).attr( 'data-go_blog_updated' );
                    if (go_blog_updated == '1') {
                        swal.fire({ //sw2 OK
                            title: "You have unsaved changes.",
                            text: "Would you like to save? If you don't save you will not be able to recover these changes.",
                            type: "warning",
                           //buttons: ["Save and Close", "Close without Saving"],
                            //dangerMode: true,
                            showCancelButton: true,
                            confirmButtonText: 'Save and Close',
                            cancelButtonText: 'Close without Saving',
                            reverseButtons: true,
                            customClass: {
                                confirmButton: 'btn btn-success',
                                cancelButton: 'btn btn-danger'
                            },
                        })
                    .then((result) => {
                            if (result.value) {
                                if ( jQuery( "#go_save_button_lightbox" ).length ) {

                                    jQuery('#go_save_button_lightbox').trigger('click');

                                }else{
                                    jQuery('#go_blog_submit').trigger('click');
                                }



                               // break;


                            } else {
                                Swal.fire("Your changes were not saved.");//sw2 OK
                                jQuery( 'body' ).attr( 'data-go_blog_updated', '0' );
                                jQuery.featherlight.close();
                               // break;
                            }
                        });

                        return false;
                    }else {
                        var post_wrapper_class = ".go_blog_post_wrapper_" + blog_post_id;
                        if (jQuery(post_wrapper_class).length = 0) {
                            location.reload();
                        }


                        //location.reload();
                        //change reload to swap wrapper

                    }
                }
            });
            //tinymce.execCommand('mceRemoveEditor', true, 'go_blog_post_edit');
            //tinymce.execCommand( 'mceAddEditor', true, 'go_blog_post_edit' );

            jQuery(".featherlight").css('background', 'rgba(0,0,0,.8)');
            jQuery(".featherlight .featherlight-content").css('width', '80%');

            //console.log("opener2");
            jQuery(".go_blog_opener").one("click", function(e){
                go_blog_opener( this );
            });
            go_disable_loading();

        }
    });
}

//NOT USED
function go_get_blog_required_elements(){
    const required_elements = {};
    jQuery('.go_blog_element_input').each(
        function(  ) {
            const type = jQuery(this).attr('data-type');
            //console.log('element: '+type);
            const uniqueID = jQuery(this).attr('data-uniqueID');
            if (type ==='URL'){

                const the_url = jQuery(this).val();
                required_elements[uniqueID] = the_url;//sets the url to be returned attached to this element uniqueID
            }
            if (type ==='video'){
                const the_video =jQuery(this).val();
                required_elements[uniqueID] = the_video;//sets the video to be returned attached to this element uniqueID
            }

            if (type ==='file'){
                const result = jQuery(this).attr( 'value' );
                required_elements[uniqueID] = result;
            }

        }
    );
    return required_elements;
}

function go_blog_submit( el, reload, required_elements = null ) {
    console.log ("go_blog_submit");

    //go_enable_loading( el );

    var nonce = GO_EVERY_PAGE_DATA.nonces.go_blog_submit;
    var suffix = jQuery( el ).attr( 'blog_suffix' );

    //var result = tinyMCE.activeEditor.getContent();
    if(jQuery('#go_blog_title' + suffix).data('blog_post_title') == 'fixed') {
        var result_title = jQuery('#go_blog_title' + suffix).html();
    }else {
        var result_title = jQuery('#go_blog_title' + suffix).val();
        if (result_title == ''){
            result_title = 'My Blog Post'
        }
    }
    var button= jQuery( el ).attr( 'button_type' );
    var result = go_get_tinymce_content_blog(suffix);
    //console.log("title: " + result_title);
    console.log("go_blog_submit");
    //var blog_post_id= jQuery( el ).attr( 'blog_post_id' );
    var blog_post_id = jQuery('#go_blog_title' + suffix).attr( 'data-blog_post_id' );
    console.log("blog_post_id: " + blog_post_id);
    var post_wrapper_class = ".go_blog_post_wrapper_" + blog_post_id;//checks if the post exists on the page--used to reload that section later
    //alert(post_wrapper_class);
    var go_blog_bonus_stage= jQuery( el ).attr( 'data-bonus_status' );
    var go_blog_task_stage= jQuery( el ).attr( 'status' );
    var task_id= jQuery( el ).attr( 'task_id' );
    var check_for_understanding= jQuery( el ).attr( 'data-check_for_understanding' );

    var blog_url= jQuery( '#go_result_url' + suffix ).val();
    //var blog_private= jQuery( '#go_private_post' + suffix ).val();
    if (jQuery('#go_private_post' + suffix).is(":checked"))
    {
        var blog_private = 1;
    }
    //var blog_media= jQuery( '#go_result_media' + suffix ).attr( 'value' );
    //var blog_video= jQuery( '#go_result_video' + suffix).val();
    //console.log("go_blog_bonus_stage: " + go_blog_bonus_stage);
    console.log("blog_private: " + blog_private);

    //make an array of the required elements type and values
    //const required_elements = go_get_blog_required_elements();

    var gotoSend = {
        action:"go_blog_submit",
        _ajax_nonce: nonce,
        result: result,
        result_title: result_title,
        blog_post_id: blog_post_id,
        required_elements: required_elements,
        //blog_url: blog_url,
        //blog_media: blog_media,
        //blog_video: blog_video,
        blog_private: blog_private,
        go_blog_task_stage: go_blog_task_stage,
        go_blog_bonus_stage: go_blog_bonus_stage,
        post_id: task_id,
        button: button,
        check_for_understanding: check_for_understanding
    };
    //jQuery.ajaxSetup({ cache: true });
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'POST',
        data: gotoSend,
        cache: false,
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
            console.log('success1');
            //console.log(raw);
            // parse the raw response to get the desired JSON
            let error = go_ajax_error_checker(raw);
            if (error == 'true') return;

            var res = {};
            try {
                var res = JSON.parse( raw );
            } catch (e) {
                res = {
                    json_status: '101',
                    message: '',
                    blog_post_id: '',
                    wrapper: ''
                };
            }
            //console.log("message" + res.message);
            //console.log("blog_post_id: " + res.blog_post_id);
            //console.log("suffix: " + suffix);
            jQuery( 'body' ).attr( 'data-go_blog_updated', '0' );


            jQuery('body').append(res.message);
            //jQuery('.go_loading').remove();
            go_disable_loading();
            jQuery('#go_save_button' + suffix).off().one("click", function(e){
                //task_stage_check_input( this, false );//on submit, no reload
                go_blog_submit( this, false );
            });


            jQuery( '#go_save_button' + suffix ).attr( 'blog_post_id', res.blog_post_id );

            jQuery( '#go_blog_title' + suffix ).attr( 'data-blog_post_id', res.blog_post_id );



            if (reload == true) {
                //alert("here");
                var is_new = jQuery(post_wrapper_class).length;
                //console.log("reload is true:" + is_new);
                //go_disable_loading();
                var current = jQuery.featherlight.current();
                current.close();
                if (jQuery(post_wrapper_class).length > 0) {
                    jQuery(post_wrapper_class).replaceWith(res.wrapper);
                    jQuery( ".feedback_accordion" ).accordion({
                        collapsible: true,
                        active: false,
                        heightStyle: "content"
                    });
                    //go_blog_tags_select2();
                    go_disable_loading();
                    go_Vids_Fit_and_Box("body");
                }else{
                    location.reload();
                }

            }


            //});
        }
    });
}

function go_blog_trash( el ) {
    console.log ("go_blog_trash");
    go_enable_loading( el );


    Swal.fire({//sw2 OK
        title: "Are you sure?",
        text: "Do you really want to delete this post?",
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel!',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
    })
        .then((result) => {
            if (result.value) {
                var nonce = GO_EVERY_PAGE_DATA.nonces.go_blog_trash;

                var blog_post_id= jQuery( el ).attr( 'blog_post_id' );

                var gotoSend = {
                    action:"go_blog_trash",
                    _ajax_nonce: nonce,
                    blog_post_id: blog_post_id,
                };
                //jQuery.ajaxSetup({ cache: true });
                jQuery.ajax({
                    url: MyAjax.ajaxurl,
                    type: 'POST',
                    data: gotoSend,
                    cache: false,
                    /**
                     * A function to be called if the request fails.
                     * Assumes they are not logged in and shows the login message in lightbox
                     */
                    error: function(jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 400){
                            jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
                        }
                        go_disable_loading( el );
                    },
                    success: function (raw) {
                        jQuery("body").append(raw);
                        var post_wrapper_class = ".go_blog_post_wrapper_" + blog_post_id;
                        const checked = jQuery('#go_show_private').attr('checked');
                        if(checked == 'checked'){
                            jQuery(post_wrapper_class).replaceWith(raw);
                        }else{
                            jQuery(post_wrapper_class).hide();
                        }



                        //location.reload();
                        jQuery(".go_blog_trash").off().one("click", function(e){
                            go_blog_trash( this );
                        });
                        swal.fire({//sw2 OK
                                text: "Poof! Your post has been deleted!",
                                type: 'success'
                            }
                        );
                        go_disable_loading( el );
                    }
                });


            } else {
                swal.fire({//sw2 OK
                    text: "Your post is safe!"
                });
                go_disable_loading( el );
            }
        });

}

function go_get_tinymce_content_blog( source ){
    //console.log("html");
    if (jQuery("#wp-go_blog_post_edit-wrap .wp-editor-area").is(":visible")){
        //alert("content1");
        return jQuery('#wp-go_blog_post_edit-wrap .wp-editor-area').val();

    }else{
        //console.log("visual");
        //alert(go_tmce_getContent('go_blog_post'));

        if (source == '_lightbox'){//this was a save in a lightbox
            //return tinymce.get('go_blog_post_lightbox').getContent();
            return go_tmce_getContent('go_blog_post_lightbox');
        }else{
            //return tinymce.get('go_blog_post').getContent();
            return go_tmce_getContent('go_blog_post');
        }



    }
}

function go_blog_user_task (user_id, task_id) {
    //jQuery(".go_datatables").hide();
    console.log("blogs!");
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_blog_user_task;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            action: 'go_blog_user_task',
            uid: user_id,
            task_id: task_id
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

            jQuery.featherlight(res, {
                variant: 'blogs',
                afterOpen: function(event){
                    //console.log("fitvids"); // this contains all related elements
                    //alert(this.$content.hasClass('true')); // alert class of content
                    //jQuery("#go_blog_container").fitVids();
                    go_fit_and_max_only("#go_blog_container");
                    go_reader_activate_buttons();
                }

            });


            if ( -1 !== res ) {

            }
        }
    });
}

/*
Based on: http://wordpress.stackexchange.com/questions/42652/#answer-42729
These functions provide a simple way to interact with TinyMCE (wp_editor) visual editor.
This is the same thing that WordPress does, but a tad more intuitive.
Additionally, this works for any editor - not just the "content" editor.
Usage:
0) If you are not using the default visual editor, make your own in PHP with a defined editor ID:
  wp_editor( $content, 'tab-editor' );

1) Get contents of your editor in JavaScript:
  tmce_getContent( 'tab-editor' )

2) Set content of the editor:
  tmce_setContent( content, 'tab-editor' )
Note: If you just want to use the default editor, you can leave the ID blank:
  tmce_getContent()
  tmce_setContent( content )

Note: If using a custom textarea ID, different than the editor id, add an extra argument:
  tmce_getContent( 'visual-id', 'textarea-id' )
  tmce_getContent( content, 'visual-id', 'textarea-id')

Note: An additional function to provide "focus" to the displayed editor:
  tmce_focus( 'tab-editor' )
=========================================================
*/
function go_tmce_getContent(editor_id, textarea_id) {
    if ( typeof editor_id == 'undefined' ) editor_id = wpActiveEditor;
    if ( typeof textarea_id == 'undefined' ) textarea_id = editor_id;

    if ( jQuery('#wp-'+editor_id+'-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id) ) {
        return tinyMCE.get(editor_id).getContent();
    }else{
        return jQuery('#'+textarea_id).val();
    }
}

function go_tmce_setContent(content, editor_id, textarea_id) {
    if ( typeof editor_id == 'undefined' ) editor_id = wpActiveEditor;
    if ( typeof textarea_id == 'undefined' ) textarea_id = editor_id;

    if ( jQuery('#wp-'+editor_id+'-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id) ) {
        return tinyMCE.get(editor_id).setContent(content);
    }else{
        return jQuery('#'+textarea_id).val(content);
    }
}

function go_tmce_focus(editor_id, textarea_id) {
    if ( typeof editor_id == 'undefined' ) editor_id = wpActiveEditor;
    if ( typeof textarea_id == 'undefined' ) textarea_id = editor_id;

    if ( jQuery('#wp-'+editor_id+'-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id) ) {
        return tinyMCE.get(editor_id).focus();
    }else{
        return jQuery('#'+textarea_id).focus();
    }
}

function go_show_private(target){
    //show loader
    const checked = jQuery(target).attr('checked');
    //send ajax to set variable
    const nonce = GO_EVERY_PAGE_DATA.nonces.go_show_private;
    const userid = jQuery(target).data('userid');

    jQuery("#go_wrapper").hide();
    jQuery("#loader_container").show();

    const gotoSend = {
        action:"go_show_private",
        _ajax_nonce: nonce,
        checked: checked,
        userid: userid
    };
    //jQuery.ajaxSetup({ cache: true });
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'POST',
        data: gotoSend,
        cache: false,
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
            //console.log(raw);
            //refresh page
            jQuery("#go_wrapper").html(raw);
            //enable checkbox
            jQuery("#go_show_private").one("click", function (e) {
                go_show_private(this);
            });

            jQuery("#loader_container").hide();
            jQuery("#go_wrapper").show();
            go_reader_activate_buttons();
            go_disable_loading( );
        }
    });

    //refresh page (or posts)
}

