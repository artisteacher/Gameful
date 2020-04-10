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

if (typeof (go_is_reader_or_blog) !== 'undefined') {
    jQuery(document).ready(function () {
        jQuery(".go_grade_scales").off().one("click", function(){
            go_print_grade_scales(this);
        });



    });
}

//if (typeof (go_is_reader_or_blog) !== 'undefined') {
jQuery( document ).ready( function() {
    jQuery("#go_show_private").one("click", function (e) {
        go_show_private(this);
    });

    jQuery('#go_hidden_mce').remove();
    jQuery('#go_hidden_mce_edit').remove();

    if(is_frontend == 'true') {
        go_blog_new_posts('true');
        go_check_needs_autosave();//sets the autosave initial state
        go_start_autosave_timer();
    }

});


function go_start_autosave_timer(){
    console.log('go_start_autosave_timer');
    window.go_autosave_timer = setInterval(function() {
        go_check_needs_autosave();
    }, 120 * 1000); // 60 * 1000 milsec
}

function go_check_needs_autosave(){
    console.log ("go_check_needs_autosave");
    jQuery(".go_blog_autosave_button").each(function() {
        var go_button = jQuery(this);

        //console.log(go_button);
        //jQuery(go_button).hide();
        //return;

        var blog_div = jQuery(go_button).closest(".autosave_wrapper");
        //jQuery(blog_div).css("background-color", "yellow");

        var required_elements = go_set_required_elements(blog_div);
        var nonce = GO_FRONTEND_DATA.nonces.go_blog_autosave;
        var suffix = jQuery( go_button ).attr( 'blog_suffix' );//for lightbox
        //console.log("required_elements" + required_elements);
        if(jQuery('#go_blog_title' + suffix).data('blog_post_title') == 'fixed') {
            var result_title = jQuery('#go_blog_title' + suffix).html();
        }else {
            result_title = jQuery('#go_blog_title' + suffix).val();
            if (result_title == ''){
                result_title = 'My Blog Post'
            }
        }
        //console.log("suffix:" + suffix);
        var button= jQuery( go_button ).attr( 'button_type' );
        //console.log("button:" + button);
        var result = go_get_tinymce_content_blog(suffix);
        //console.log("title: " + result_title);
        //var blog_post_id= jQuery( el ).attr( 'blog_post_id' );
        var blog_post_id = jQuery('#go_blog_title' + suffix).attr( 'data-blog_post_id' );
        //console.log("blog_post_id: " + blog_post_id);
        var go_blog_bonus_stage= jQuery( go_button ).attr( 'data-bonus_status' );
        var go_blog_task_stage= jQuery( go_button ).attr( 'status' );
        var task_id= jQuery( go_button ).attr( 'task_id' );
        var check_for_understanding= jQuery( go_button ).attr( 'data-check_for_understanding' );

        if (jQuery('#go_private_post' + suffix).is(":checked"))
        {
            var blog_private = 1;
        }

        var gotoSend = {
            is_frontend: is_frontend,
            action:"go_blog_autosave",
            is_frontend: is_frontend,
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

        //var reString = JSON.stringify(required_elements);
        //console.log("required_elements:" + reString);

        var gotoSendString = JSON.stringify(gotoSend);

       // console.log("gotoSendString:" + gotoSendString);
        //console.log("initial:" + initial);

        //if key_name does exist
        if (typeof (window["last_go_to_send_" +  blog_post_id]) != 'undefined') {
            //check it against string//if different
            if((window["last_go_to_send_" +  blog_post_id]) != gotoSendString){
               // console.log(window["last_go_to_send_" +  blog_post_id]);
                //console.log(gotoSendString);
                go_blog_autosave( gotoSend ); //set variable and do autosave
            }
        }
        window["last_go_to_send_" +  blog_post_id]= gotoSendString;
    });
}

function go_blog_autosave( gotoSend ) {

    console.log ("go_blog_autosave");
    //return;
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
            console.log('autosave success');
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
            console.log(res.message);
        }
    });
}

//creates array of required elements to be sent in ajax
function go_set_required_elements(blog_div){
    const required_elements = {};
    jQuery(blog_div).find('.go_blog_element_input').each(
        function( ) {
            const type = jQuery(this).attr('data-type');
            const uniqueID = jQuery(this).attr('data-uniqueID');

            if (type ==='URL'){
                const the_url =jQuery(this).val();
                required_elements[uniqueID] = the_url;
               // console.log("URL" + the_url);
            }

            if (type ==='video'){
                const the_url =jQuery(this).val().replace(/\s+/, '');
                required_elements[uniqueID] = the_url;
            }

            if (type ==='file'){
                const result = jQuery(this).attr('value');
                required_elements[uniqueID] = result;
            }

            if (type ==='text'){

                //const result = jQuery(this).val();

                const go_mce_id = jQuery(this).find(".go_mce_textarea").attr('id');
                if(go_mce_id) {
                    var result = go_tmce_getContent(go_mce_id);
                }else{
                    var result = jQuery(this).val();
                }


                required_elements[uniqueID] = result;
            }
            if (type ==='sketch'){
                //const sketch_id = "sketch" + uniqueID;
                const result =  window['sketch'+uniqueID].getSnapshot();
                console.log("sketch");
                console.log(result);
                required_elements[uniqueID] = result;
            }

        }
    );
    return required_elements;
}

//stuff to do when a new post is loaded on a page
function go_blog_new_posts(initial = 'false'){
    console.log('go_blog_new_posts');
    go_lightbox_blog_img();
    go_Vids_Fit_and_Box("body");
    go_disable_loading();
    go_enable_task_buttons();

    //go_reader_activate_buttons();
    //jQuery('#go_cards').masonry()

    //console.log("go_reader_activate_buttons");

    //var cards = jQuery('#go_cards_toggle').is(':checked');
    if(jQuery('#go_cards').length) {
        var grid = jQuery('#go_cards').masonry({
            itemSelector: '.go_blog_post_card_sizer',
            columnWidth: '.go_blog_post_card_sizer',
        });

        grid.imagesLoaded().progress(function () {
            grid.masonry('layout');
        });
    }

    jQuery(".go_show_likes_list").off().one("click", function(e){
       go_get_likes_list(this);
        //go_blog_new_posts();
    });

    jQuery(".go_blog_opener").off().one("click", function(e){
        go_blog_opener( this );
    });

    jQuery('#go_read_printed_button').off().on("click", function () {
        //console.log("clicked");
        go_loadmore_reader(this);
    });

    jQuery('input[type=radio][name=loot_option]').on('change', function() {
        switch (jQuery(this).val()) {
            case 'none':
                jQuery(this).closest('.messages_form').find('.go_feedback_assign_loot').hide();
                jQuery(this).closest('.messages_form').find('.go_feedback_percent_loot').hide();
                break;
            case 'percent':
                jQuery(this).closest('.messages_form').find('.go_feedback_assign_loot').hide();
                jQuery(this).closest('.messages_form').find('.go_feedback_percent_loot').show();
                break;
            case 'assign':
                jQuery(this).closest('.messages_form').find('.go_feedback_assign_loot').show();
                jQuery(this).closest('.messages_form').find('.go_feedback_percent_loot').hide();
                break;
        }
    });

    jQuery('.go_blog_revision').off().one("click", function () {
        go_blog_revision(this);
    });

    jQuery(".go_blog_favorite").off().click(function() {
        go_blog_favorite(this);
    });

    jQuery(".go_blog_trash").off().one("click", function (e) {
        go_blog_trash(this);
    });

    jQuery(".go_reset_task_stage_blog").off().one("click", function(){
        go_messages_opener( this.getAttribute('data-uid'), this.getAttribute('data-task'), 'reset_stage', this );
    });

    go_activate_tippy();

    jQuery("#go_mark_all_read").off().one("click", function(){
        //go_reader_bulk_read(this);
        go_loadmore_reader(this);

    });

    jQuery(".go_status_read_toggle").off().one("click", function(){
        go_mark_one_read_toggle(this);
    });

    jQuery( ".feedback_accordion" ).accordion({
        collapsible: true,
        active: false,
        heightStyle: "content"
    });

    jQuery(".go_send_feedback").off().one("click", function(){
        go_send_feedback(this);
    });

    jQuery('.go-acf-switch').off().click(function () {
        console.log("click");
        if (jQuery(this).hasClass('-on') == false) {
            jQuery(this).prev('input').prop('checked', true);
            jQuery(this).addClass('-on');
            jQuery(this).removeClass('-off');
        } else {
            jQuery(this).prev('input').prop('checked', false);
            jQuery(this).removeClass('-on');
            jQuery(this).addClass('-off');
        }
    });

    jQuery(".go_feedback_canned").off().on('change', function (e) {
        var optionSelected = jQuery("option:selected", this);
        go_feedback_canned(optionSelected);
    });

    jQuery('.feedback_accordion').show('slow');

    go_stats_links();

    jQuery('.go_quest_reader_lightbox_button').off().one("click", function () {
        go_reader_update(this, true);
    });

    /*
    jQuery('.summernote').summernote({
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            //['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            // ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            //['height', ['height']]
            ['insert', ['link']],
        ]
    });*/

    //the book icon on cards
    jQuery(".go_blog_post_opener").off().one("click", function(e){
        go_blog_post_opener( this );
    });

    jQuery('.go_loadmore_reader').off().one("click", function () {
        go_loadmore_reader(this);
    });

    /*
    jQuery('.go_loadmore_blog').off().one("click", function () {
        go_loadmore_blog(this);
    });*/


    jQuery("#go_num_posts, #go_cards_toggle").off().change(function() {
        go_num_posts();
    });

    jQuery('.go_str_item').off().one("click", function (e) {
        go_lb_opener(this);
    });

    if(initial === 'false') {
        go_activate_tinymce_on_task_change_stage('go_blog_post');

        /*
        jQuery('.go_blog_element_input').each(
            function () {
                console.log("go_blog_element_input");
                const go_mce_id = jQuery(this).find(".wp-editor-area").attr('id');
                console.log("go_mce_id" + go_mce_id);
                go_activate_tinymce_on_task_change_stage(go_mce_id);
            }
        );*/

        jQuery(".wp-editor-area").each(function(){
            var go_mce_id = jQuery(this).attr('id');
            console.log("go_mce_id" + go_mce_id);
            go_activate_tinymce_on_task_change_stage(go_mce_id);
        })




    }

/*
    var image_prefix = PluginDir.url + "includes/literallycanvas/img";
    console.log("LC_img_prefix: " + image_prefix);
    jQuery('body').find('.go_blog_element_input').each(
        function( ) {
            const type = jQuery(this).attr('data-type');
            if (type ==='sketch'){
                const uniqueID = jQuery(this).attr('data-uniqueID');
                var val = jQuery(this).attr('value');
                val = JSON.parse(val);
                console.log(val);
                window['sketch'+uniqueID] = LC.init(
                    document.getElementsByClassName('my-drawing')[0],
                    {
                        imageURLPrefix: image_prefix,
                        snapshot: val
                        }
                );

            }

        }
    );

    jQuery('body').find('.my-drawing-image').each(
        function(index, value) {
            var val = jQuery(this).attr('value');
            val = JSON.parse(val);
            var svg =  LC.renderSnapshotToSVG(val);
            jQuery(this).find(".my-drawing-svg").html(svg);
        }
    );*/


}

function go_blog_favorite(target){
    console.log("go_blog_favorite");
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
        is_frontend: is_frontend,
        action:"go_blog_favorite_toggle",
        _ajax_nonce: nonce,
        blog_post_id: blog_post_id,
        checked: checked
    };
    //jQuery.ajaxSetup({ cache: true });
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        is_frontend: is_frontend,
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
            go_after_ajax();
        }
    });
}

//on_task is used to determine where to show the error message
//it could be on the page or in a lightbox
//why not just have one place?--fix this
function task_stage_check_input( target, on_task, reload = true) {
    console.log('task_stage_check_input');
    //throw new Error("1-Something went badly wrong!");
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
    //const required_elements = {};

    if ( check_type == 'blog' || check_type == 'blog_lightbox') {

        const text_toggle = jQuery(target).attr('text_toggle');

        var blog_div = jQuery(target).closest(".autosave_wrapper");

        jQuery('.go_blog_element_error').remove();
        jQuery(blog_div).find('.go_blog_element_input').each(
            function( ) {
                console.log('check element');
                const type = jQuery(this).attr('data-type');
                const uniqueID = jQuery(this).attr('data-uniqueID');

                if (type ==='URL'){
                    const required_string = jQuery(this).attr('data-required');
                    const URL_error = "<span class='go_blog_element_error' style='color: red;'><br>Enter a valid URL.</span>";
                    ///
                    //const the_url =jQuery(this).val().replace(/\s+/, '');
                    const the_url =jQuery(this).val();
                    //required_elements[uniqueID] = the_url;
                    console.log("URL" + the_url);
                    if (the_url.length > 0) {
                        console.log('1');
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
                        fail = true;
                        jQuery(this).after(URL_error);
                    }
                }

                if (type ==='video'){
                    const the_url =jQuery(this).val().replace(/\s+/, '');
                    //required_elements[uniqueID] = the_url;
                    const video_error_message = "Enter a valid video URL. YouTube and Vimeo are supported.";
                    const video_error = "<span class='go_blog_element_error' style='color: red;'><br>"+video_error_message+"</span>";
                    console.log("videoURL" + the_url);
                    if (the_url.length > 0) {
                        if (the_url.match(/^(http:\/\/|https:\/\/).*\..*$/) && !(the_url.lastIndexOf('http://') > 0) && !(the_url.lastIndexOf('https://') > 0)) {
                            if ((the_url.search("youtu") == -1) && (the_url.search("vimeo") == -1)) {
                                error_message += "<li>"+video_error_message+"</li>";
                                fail = true;
                                jQuery(this).after(video_error);

                            }
                        } else {
                            error_message += "<li>"+video_error_message+"</li>";
                            fail = true;
                            jQuery(this).after(video_error);
                        }
                    } else {
                        error_message += "<li>"+video_error_message+"</li>";
                        fail = true;
                        jQuery(this).after(video_error);
                    }
                }

                if (type ==='file'){
                    console.log("file result:");
                    const result = jQuery(this).attr('value');
                    //required_elements[uniqueID] = result;
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

                if (type ==='text'){
                    console.log("text result:");
                    //const result = jQuery(this).val();
                    //const result = jQuery(this).find(".go_text_element").val();
                    const go_mce_id = jQuery(this).find(".go_mce_textarea").attr('id');
                    if(go_mce_id) {
                        var result = go_tmce_getContent(go_mce_id);
                    }else{
                        var result = jQuery(this).val();
                    }

                    var min_words = jQuery(this).data('min_words'); //this variable is used in the other functions as well
                   // alert("min Words: " + min_words);
                    var my_words = tinymce_getContentLength_new(go_mce_id);
                    //alert("my_words: " + my_words);
                    //var bb = tinymce.get(tinymce.activeEditor.id);
                    //console.log(bb);
                    if (my_words < min_words) {
                        const file_error = "<br><span class='go_blog_element_error' style='color: red;'><br>Your answer is not long enough. There must be " + min_words + " words minimum. You have " + my_words + " words.</span>";
                        error_message += "<li>Please check your answers.</li>";
                        fail = true;
                        jQuery(this).after(file_error);
                    }
//////FIX THIS

                }

            }
        );

        if(text_toggle  == '1') {
            console.log("MCE wordcount validation");
            //Word count validation
            var min_words = jQuery(target).attr('min_words'); //this variable is used in the other functions as well
            //alert("min Words: " + min_words);
            var my_words = tinymce_getContentLength_new(check_type);
            //var bb = tinymce.get(tinymce.activeEditor.id);
            //console.log(bb);
            if (my_words < min_words) {
                error_message += "<li>Your post is not long enough. There must be " + min_words + " words minimum. You have " + my_words + " words.</li>";
                fail = true;
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

    console.log("error message");
    console.log((error_message));
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

        swal.fire({//sw2 OK
            title: 'There are errors with your submission.',
                html: error_message,
                type: 'error'
            }
        );

        jQuery(target).closest(".go_checks_and_buttons").find("#go_save_button").trigger('click');//saves a draft--it should save somewhere else

        go_disable_loading();
        go_enable_task_buttons();
        console.log('disable loading3');
        return;
    }else{
        jQuery('#go_blog_stage_error_msg').hide();
        jQuery('#go_blog_error_msg').hide();
    }



    if (on_task == true) {
        var required_elements = go_set_required_elements(blog_div);
        //throw new Error("1-Something went badly wrong!");
        task_stage_change(target, required_elements);
    }else{ //this was a blog submit button in a lightbox, so just save without changing stage.
        go_blog_submit( target, reload );
    }
    //go_disable_loading();
}

//adds a loading gif to the button
function go_enable_loading( target ) {
    console.log("go_enable_loading");
    var loader_html = go_loader_html('small');
    if (jQuery(target).hasClass('go_button_round')){
        jQuery("#stats_tasks").html(loader_html);
        jQuery(target).find('.go_round_inner').html("<span style='margin-top:7px;'>" + loader_html + "</span>");
    }else{
        //target.innerHTML = '<span class="go_loading">' + loader_html + '</span> ' + target.innerHTML;
        loader_html = '<span class="go_loading">' + loader_html + '</span> ';
        jQuery(target).prepend(loader_html);
    }
}

// re-enables the stage button, and removes the loading gif
function go_disable_loading( ) {
    console.log ("go_disable_loading");
    jQuery('.go_loading').remove();
    jQuery('#go_save_button .go_round_inner').html("<i class='fas fa-save'></i>");
    jQuery('.go_blog_trash .go_round_inner').html("<i class='fas fa-trash'></i>");
    jQuery('.go_blog_post_opener .go_round_inner').html("<i class='fas fa-book-open'></i>");
    jQuery('.go_blog_opener_round .go_round_inner').html("<i class='fas fa-pencil-alt'></i>");


    //things that need to be reenabled--perhaps move these to their specific spots?
}

function go_enable_task_buttons( ) {

    jQuery('#go_back_button').off().one("click", function(e){
        task_stage_change(this);
    });

    //things that need to be reenabled--perhaps move these to their specific spots?
    jQuery('#go_button').off().one("click", function(e){
        task_stage_check_input( this, true );
    });

    jQuery('#go_save_button').off().one("click", function(e){
        //go_blog_submit( this, false );//disable loading
        task_stage_check_input( this, false, false);
    });

    jQuery("#go_blog_submit").off().one("click", function(e){
        task_stage_check_input( this, false );//disable loading
    });

    //also activate the mystery box if needed
    jQuery( "#go_bonus_button" ).off().one("click", function(e) {
        go_update_bonus_loot(this);
    });

    //add active class to checks and buttons
    jQuery(".progress").closest(".go_checks_and_buttons").addClass('active');

}

function tinymce_getContentLength_new(source) {
    //var b = jQuery(target).closest(".go_checks_and_buttons").find('.mce-content-body').hide();
    //console.log(b);
    //var b = tinymce.get(tinymce.activeEditor.id).contentDocument.body.innerText;
//alert("source:"+source)
    if (source == 'blog_lightbox'){
        //var b = tinymce.get('go_blog_post_lightbox').contentDocument.body.innerText;
        var b = go_tmce_getContent('go_blog_post_lightbox');

    }else if (source == 'blog'){
        //var b = tinymce.get('go_blog_post').contentDocument.body.innerText;
        var b = go_tmce_getContent('go_blog_post');
    }
    else {
        //var b = tinymce.get('go_blog_post').contentDocument.body.innerText;
        var b = go_tmce_getContent(source);
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

//This is the form
function go_blog_opener( el ) {
    console.log ("go_blog_opener");
    if(Number.isInteger(el)){
        var blog_post_id= el;
        //console.log("isInteger");
    }else{
        var check_for_understanding = jQuery( el ).attr( 'data-check_for_understanding' );
        var blog_post_id= jQuery( el ).attr( 'blog_post_id' );
        //console.log("isInteger--NOT");
    }
    go_enable_loading( el );
    jQuery("#go_hidden_mce").remove();

    var nonce = GO_FRONTEND_DATA.nonces.go_blog_opener;
    var gotoSend = {
        is_frontend: is_frontend,
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
            go_after_ajax();
            if (results == 'login'){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            };
            if (results == 'locked'){
               swal.fire({//sw2 OK
                    title: "Locked",
                    html: 'This post was created from a task that has since been locked. That task must be unlocked before this blog post can be edited. Click on the blog post title to go to view the task and view the lock.',
                    type: 'warning',
                });
                go_disable_loading();
                return;
            };
            //console.log(results);
            jQuery.featherlight(results, {
                    variant: 'autosave_wrapper',
                    afterContent: function(){
                    console.log("aftercontent");

                    jQuery( 'body' ).attr( 'data-go_blog_saved', '0' );
                    jQuery( 'body' ).attr( 'data-go_blog_updated', '0' );

                    go_blog_after_ajax();
                    go_disable_loading( );

                    go_activate_tinymce_on_task_change_stage('go_blog_post_lightbox');
                    jQuery('.go_result_text_lightbox').each(
                        function( ) {
                            const go_mce_id = jQuery(this).find(".go_mce_textarea").attr('id');
                            go_activate_tinymce_on_task_change_stage(go_mce_id);
                        }
                    );
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
                    }
                }
            });
            jQuery(".featherlight").css('background', 'rgba(0,0,0,.8)');
            jQuery(".featherlight .featherlight-content").css('width', '80%');


            go_disable_loading();
            jQuery(".go_blog_opener").off().one("click", function(e){
                go_blog_opener( this );
            });
        }
    });
}

//This is the post
function go_blog_post_opener( el ) {
    console.log ("go_blog_post_opener");
    if(Number.isInteger(el)){
        var blog_post_id= el;
        //console.log("isInteger");
    }else{
        var blog_post_id= jQuery( el ).attr( 'blog_post_id' );
        //console.log("isInteger--NOT");
    }
    go_enable_loading( el );

    var nonce = GO_FRONTEND_DATA.nonces.go_blog_post_opener;
    var gotoSend = {
        is_frontend: is_frontend,
        action:"go_blog_post_opener",
        _ajax_nonce: nonce,
        blog_post_id: blog_post_id
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
            jQuery(".go_blog_post_opener").off().one("click", function(e){
                go_blog_post_opener( this );
            });
        },
        success: function (results) {
            go_after_ajax();
            jQuery(".go_blog_post_opener").off().one("click", function(e){
                go_blog_post_opener( this );
            });
            if (results == 'login'){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            };
            if (results == 'locked'){
                //var title = jQuery(el).closest(".go_blog_post_wrapper").find('.go_post_title').html();
                return;
            };

            jQuery.featherlight(results, {
                variant: 'blog_post',
                afterContent: function(){
                    go_blog_new_posts();
                }
            });

            jQuery(".featherlight").css('background', 'rgba(0,0,0,.8)');
            jQuery(".featherlight .featherlight-content").css('width', '80%');
        }
    });
}

//for after the form is loaded
function go_blog_after_ajax(){
    jQuery("#go_result_url_lightbox, #go_result_video_lightbox, .go_result_text_lightbox").change(function() {
        jQuery('body').attr('data-go_blog_updated', '1');
    });

    jQuery('.go_frontend-button').on("click", function() {
        jQuery('body').attr('data-go_blog_updated', '1');
    });

    jQuery("#go_blog_submit").off().one("click", function(e){
        task_stage_check_input(this, false);
    });

}

function go_blog_submit( el, reload) {
    var blog_div = jQuery(el).closest(".autosave_wrapper");
    var required_elements = go_set_required_elements(blog_div);
    console.log ("go_blog_submit");
    //console.log("required_elements:" + required_elements);
    //go_enable_loading( el );

    clearInterval(go_autosave_timer);

    var nonce = GO_FRONTEND_DATA.nonces.go_blog_submit;
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
    console.log("go_blog_submit2");
    //var blog_post_id= jQuery( el ).attr( 'blog_post_id' );
    var blog_post_id = jQuery('#go_blog_title' + suffix).attr( 'data-blog_post_id' );
    console.log("blog_post_id: " + blog_post_id);
    var go_blog_bonus_stage= jQuery( el ).attr( 'data-bonus_status' );
    var go_blog_task_stage= jQuery( el ).attr( 'status' );
    var task_id= jQuery( el ).attr( 'task_id' );
    var check_for_understanding= jQuery( el ).attr( 'data-check_for_understanding' );

    //var blog_url= jQuery( '#go_result_url' + suffix ).val();
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
        is_frontend: is_frontend,
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
           go_start_autosave_timer();
        },
        success: function (raw) {
            go_after_ajax();
            console.log('go_blog_submit success');

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

            console.log("message");
            console.log(res.message);
            jQuery('body').append(res.message);
            //jQuery('.go_loading').remove();
            go_disable_loading();
            jQuery('#go_save_button' + suffix).off().one("click", function(e){
                //task_stage_check_input( this, false );//on submit, no reload
                go_blog_submit( this, false );
                //task_stage_check_input( target, false, false);
            });


            console.log("reload: " + reload);
            if (reload == true) {//If this is a submit, either place the blog content in the page or reload page if the element to reload doesn't exist
                console.log("reload");
                var post_wrapper_class = ".go_blog_post_wrapper_" + blog_post_id;//checks if the post exists on the page--used to reload that section later
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
                    go_blog_new_posts();
                }else{
                   location.reload();
                }

            }else{//if this is a draft save, then make sure the blog_post_id is on the save button
                console.log("1");
                //var form_wrapper_class = ".go_blog_form_wrapper_" + blog_post_id;//checks if the post exists on the page--used to reload that section later
                if (jQuery('.go_blog_form_div').length > 0) {//make sure there is an active form
                    console.log("2");
                    if(jQuery('.go_save_button').length > 0) {//if there is a save button, add blog_post_id as element
                        console.log("3");
                        if(jQuery('.go_save_button').attr('blog_post_id').length > 0) {
                            console.log("4");
                            var existing_blog_post_id = jQuery('.go_save_button').attr('blog_post_id');
                            if (existing_blog_post_id.length > 0) {
                                console.log("5");
                                if (res.blog_post_id != existing_blog_post_id) {
                                    console.log(blog_post_id);
                                    console.log(existing_blog_post_id);
                                    console.log(existing_blog_post_id.length);
                                    console.log("dont match");
                                    location.reload();//add error log and message here
                                }
                            }
                        }
                        else{
                            console.log("6");
                            jQuery( '#go_save_button' + suffix ).attr( 'blog_post_id', res.blog_post_id );
                            jQuery( '#go_blog_title' + suffix ).attr( 'data-blog_post_id', res.blog_post_id );
                            jQuery('#go_button').attr('blog_post_id', res.blog_post_id);
                            console.log("blog post id attribute added");
                        }

                    }else{
                        location.reload();//add error log and message here
                    }

                }else{
                    location.reload();//add error log and message here
                }
            }
            go_start_autosave_timer();

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
                var nonce = GO_FRONTEND_DATA.nonces.go_blog_trash;

                var blog_post_id= jQuery( el ).attr( 'blog_post_id' );

                var gotoSend = {
                    is_frontend: is_frontend,
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
                        go_after_ajax();
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
    console.log("go_get_tinymce_content_blog");
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

//on clipboard map and about me quest--makes lightbox of all stages from one quest
function go_blog_user_task (target) {
    go_enable_loading(target);
    var uid = jQuery(target).data("user_id");
    var task_id = jQuery(target).data("post_id");
    //jQuery(".go_datatables").hide();
    console.log("blogs!");
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_blog_user_task;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_blog_user_task',
            uid: uid,
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
            go_after_ajax();
            go_disable_loading();

            jQuery.featherlight(res, {
                variant: 'blogs',
                afterOpen: function(event){
                    go_blog_new_posts();
                }

            });
            jQuery( target ).off().one("click", function () {
                go_blog_user_task(this);
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
    const nonce = GO_FRONTEND_DATA.nonces.go_show_private;
    const userid = jQuery(target).data('userid');

    var loader_html = go_loader_html('big');
    jQuery('#go_wrapper').html(loader_html);

    const gotoSend = {
        is_frontend: is_frontend,
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
            go_after_ajax();
            //console.log(raw);
            //refresh page
            jQuery("#go_wrapper").html(raw);
            //enable checkbox
            jQuery("#go_show_private").one("click", function (e) {
                go_show_private(this);
            });
            jQuery("#go_wrapper").show();
            go_blog_new_posts();
        }
    });

    //refresh page (or posts)
}

function go_activate_tinymce_on_task_change_stage(fullId, use_full = null){
    //tinymce.execCommand('mceRemoveEditor', true, 'go_blog_post_lightbox');
    //console.log("go_activate_tinymce_on_task_change_stage:"+fullId);

    var is_admin = GO_FRONTEND_DATA.go_is_admin;
    //var is_admin = false;
    if(use_full === null) {
        use_full = jQuery("#" + fullId).closest('.go_blog_element_input').data('toolbar');
    } else if(use_full === 'admin'){
        use_full = is_admin;
    }
    //console.log("use_full: " + use_full);
    //console.log(use_full);
   // var use_summernote = false;
   // var use_mce = ["go_message_text_area_id", "go_blog_post_lightbox", "go_blog_post"];
   // if(use_mce.includes(fullId)) {//settings for regular blog posts
    var plugins = "charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,wordcount,go_shortcode_button,go_admin_comment,tma_annotate";

    if (is_admin) {
        var toolbar1 = "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,wp_adv,go_shortcode_button,go_admin_comment,tma_annotate,tma_annotatedelete,tma_annotatehide";
        //var toolbar2 = "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help";
        if(use_full){
            var toolbar1 = "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,wp_adv,go_shortcode_button,go_admin_comment,tma_annotate,tma_annotatedelete,tma_annotatehide";
        }else{
            var toolbar1 = "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,go_shortcode_button,go_admin_comment,tma_annotate,tma_annotatedelete,tma_annotatehide";
        }

    } else{
        if(use_full) {
            var toolbar1 = "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,wp_adv,tma_annotatehide";
        }else{
            var toolbar1 = "bold,italic,bullist,numlist,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,tma_annotatehide";
        }
    }

    if(use_full){
        var toolbar2 = 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help';
    }else{
        var toolbar2  = '';
    }


        /*else if(use_full == 1){
            console.log("BIG");
            var plugins = "charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,wordcount,go_shortcode_button,go_admin_comment,tma_annotate";
            var toolbar1 = "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,wp_adv,tma_annotatehide";
            var toolbar2 = "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help";
        } else{
            var toolbar1 = "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,wp_adv,tma_annotatehide";
            var toolbar2 = "";
        }*/




    /*}
    else{
        if (is_admin) {
            var plugins = "charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,wordcount,go_shortcode_button,go_admin_comment,tma_annotate";
            var toolbar1 = "bold,italic,bullist,numlist,alignleft,aligncenter,alignright,link, tma_annotate,tma_annotatedelete,tma_annotatehide";
            var toolbar2 = "removeformat,outdent,indent,undo,redo,wp_help";
        } else {
            use_summernote = true;
        }

    }

    if (use_summernote == true){
        jQuery('.summernote').summernote({
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
            ]
        });
    }else {*/
        tinymce.execCommand('mceRemoveEditor', true, fullId);
        //quicktags({id :'go_blog_post_lightbox'});

        var go_annotate_css = TMA.myurl + "dev/css/files/annotate.css";
        quicktags({id: fullId});
        // use wordpress settings
        tinymce.init({
            selector: fullId,
            branding: false,
            theme: "modern",
            skin: "lightgray",
            language: "en",
            formats: {
                alignleft: [
                    {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign: 'left'}},
                    {selector: 'img,table,dl.wp-caption', classes: 'alignleft'}
                ],
                aligncenter: [
                    {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign: 'center'}},
                    {selector: 'img,table,dl.wp-caption', classes: 'aligncenter'}
                ],
                alignright: [
                    {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign: 'right'}},
                    {selector: 'img,table,dl.wp-caption', classes: 'alignright'}
                ],
                strikethrough: {inline: 'del'}
            },
            relative_urls: false,
            remove_script_host: false,
            convert_urls: false,
            browser_spellcheck: true,
            fix_list_elements: true,
            entities: "38,amp,60,lt,62,gt",
            entity_encoding: "raw",
            keep_styles: false,
            paste_webkit_styles: "font-weight font-style color",
            preview_styles: "font-family font-size font-weight font-style text-decoration text-transform",
            wpeditimage_disable_captions: false,
            wpeditimage_html5_captions: true,
            plugins: plugins,
            selector: "#" + fullId,
            resize: "vertical",
            menubar: false,
            wpautop: true,
            wordpress_adv_hidden: false,
            indent: false,
            toolbar1: toolbar1,
            toolbar2: toolbar2,
            toolbar3: "",
            toolbar4: "",
            tabfocus_elements: ":prev,:next",
            body_class: "id post-type-post post-status-publish post-format-standard",
            height: "150",
            content_css: go_annotate_css,
        });

        // this is needed for the editor to initiate
        tinyMCE.execCommand('mceAddEditor', false, fullId);
   // }
}


//NOT USED
/*
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
*/