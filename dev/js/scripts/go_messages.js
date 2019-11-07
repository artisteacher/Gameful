function go_reset_opener(message_type){
    console.log("go_reset_opener");
    if (message_type == "multiple_messages" || message_type == null ) {
        //apply on click to the messages button at the top
        jQuery('.go_messages_icon_multiple_clipboard').parent().prop('onclick', null).off('click');
        jQuery(".go_messages_icon_multiple_clipboard").parent().one("click", function (e) {
            go_messages_opener(null, null, "multiple_messages");
        });
    }

    if (message_type == "single_reset" || message_type == null) {
        //apply on click to the individual task reset icons
        jQuery('.go_reset_task_clipboard').prop('onclick', null).off('click');
        jQuery(".go_reset_task_clipboard").one("click", function () {
            go_messages_opener(this.getAttribute('data-uid'), this.getAttribute('data-task'), 'single_reset', this);
        });
    }

    if (message_type == "multiple_reset" || message_type == null) {
        //apply on click to the reset button at the top
        jQuery('.go_tasks_reset_multiple_clipboard').parent().prop('onclick', null).off('click');
        jQuery(".go_tasks_reset_multiple_clipboard").parent().one("click", function () {
            go_messages_opener(null, null, 'multiple_reset', this);
        });
    }

    if (message_type == "single_message" || message_type == null) {
        jQuery(".go_stats_messages_icon").prop('onclick', null).off('click');
        jQuery(".go_stats_messages_icon").one("click", function (e) {
            var user_id = this.getAttribute('data-uid');
            go_messages_opener(user_id, null, "single_message", this);
        });
    }

    if (message_type == "reset_stage" || message_type == null) {
        //apply on click to the individual task reset icons
        jQuery('.go_reset_task_stage_blog').prop('onclick', null).off('click');
        jQuery(".go_reset_task_stage_blog").one("click", function () {
            go_messages_opener(this.getAttribute('data-uid'), this.getAttribute('data-task'), 'reset_stage', this);
        });
    }

}

function go_messages_opener( user_id, post_id, message_type, target ) {
    post_id = (typeof post_id !== 'undefined') ?  post_id : null;
    message_type = (typeof message_type !== 'undefined') ?  message_type : null;
    console.log("type: " + message_type);
    console.log("UID: " + user_id);
    console.log("post_id: " + post_id);
    jQuery('.go_tasks_reset_multiple_clipboard').prop('onclick',null).off('click');

    var reset_vars = [];
    if (message_type == 'multiple_messages' || message_type == 'multiple_reset' ){//the reset button or messages button on clipboard was pressed
        var inputs = jQuery(".go_checkbox:visible");
        for(var i = 0; i < inputs.length; i++){
            if (inputs[i]['checked'] === true ){
                var uid = (inputs[i]).getAttribute('data-uid');
                var task = (inputs[i]).getAttribute('data-task');
                if (message_type == 'multiple_messages'){
                   task = "";
                }
                reset_vars.push({uid:uid, task:task});
            }
        }
    }
    else if (message_type == 'single_reset' || message_type == 'single_message' || message_type == 'reset_stage'){ //single task reset or message was pressed
        reset_vars.push({uid:user_id, task:post_id});
        if (message_type == 'reset_stage'){
            console.log("target: " + target);
            jQuery(target).find('.go_round_inner').html("<i class='fas fa-spinner fa-pulse'></i>")
        }
    }


    //if only a uid was passed, this is just a send message to single user box (no reset)

    var nonce = GO_EVERY_PAGE_DATA.nonces.go_create_admin_message;
    var gotoSend = {
        action:"go_create_admin_message",
        _ajax_nonce: nonce,
        //post_id: post_ids,
        //user_id: user_id,
        message_type: message_type,
        reset_vars: reset_vars
    };
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type:'POST',
        data: gotoSend,
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            go_reset_opener(message_type);
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
        },
        success: function( results ) {
            //console.log("results:" + results);
            var res = jQuery.parseJSON(results);



            //console.log(results);
            //jQuery.featherlight(results, {variant: 'message'});
            if (res.type == 'reset') {
                var type = '';
                var show_cancel = true;
                var showConfirmButton = true;
                var confirmButtonColor = 'IndianRed';
                var confirmButtonText = '<i class="fas fa-paper-plane"></i> Send';
                var cancelButtonText = '<i class="fas fa-times-circle"></i> Cancel';

            }else if (res.type == 'no_users') {
                var type = 'error';
                var show_cancel = true;
                var showConfirmButton = false;
                var confirmButtonColor = 'grey';
                var cancelButtonText= '<i class="fas fa-times-circle"></i> Try again!';
                var confirmButtonText= 'Cancel';


            }
            else {
                var type = '';
                var show_cancel = true;
                var showConfirmButton = true;
                var confirmButtonColor = '';
                var confirmButtonText= '<i class="fas fa-paper-plane"></i> Send Message';
                var cancelButtonText = '<i class="fas fa-times-circle"></i> Cancel';

            }


                swal.fire({//sw2 OK
                    title: res.title,
                    html: res.message,
                    type: type,
                    showCancelButton: show_cancel,
                    showConfirmButton: showConfirmButton,
                    reverseButtons: true,
                    customClass: "go_wide_swal",
                    confirmButtonColor: confirmButtonColor,
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: cancelButtonText,
                }).then((result) => {
                    if (result.value) {
                        go_send_message(reset_vars, message_type, post_id, this);

                    }
                });
            jQuery(target).find('.go_round_inner').html('<i class="fas fa-times-circle"></i>');
            go_reset_opener(message_type);

            jQuery(".go_messages_canned").on('change', function (e) {
                var optionSelected = jQuery("option:selected", this);
                go_messages_canned(optionSelected);
            });

            jQuery('.go-acf-switch').click(function () {
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

            go_make_select2_filter('go_badges', true, false);
            go_make_select2_filter('user_go_groups', true, false);

            go_activate_tippy();

            jQuery('#go_additional_penalty_toggle').change(function () {
                var penalty = document.getElementById("go_additional_penalty_toggle").checked;
                //console.log(penalty);
                if (penalty == true){
                    jQuery(".go_penalty_table").css('display', 'block');
                }else{
                    jQuery(".go_penalty_table").css('display', 'none');
                }
            });

            jQuery('#go_custom_message_toggle').change(function () {
                var penalty = document.getElementById("go_custom_message_toggle").checked;
                //console.log(penalty);
                if (penalty == true){
                    jQuery("#go_custom_message_table").css('display', 'block');
                }else{
                    jQuery("#go_custom_message_table").css('display', 'none');
                }
            });

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
            });

            var fullId = 'go_message_text_area_id';
            //tinymce.execCommand('mceRemoveEditor', true, 'go_blog_post_lightbox');
            tinymce.execCommand('mceRemoveEditor', true, fullId);
            //quicktags({id :'go_blog_post_lightbox'});

            quicktags({id : fullId});
            // use wordpress settings
            tinymce.init({
                selector: fullId,
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
                plugins:"charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,go_shortcode_button",
                selector:"#" + fullId,
                resize:"vertical",
                menubar:false,
                wpautop:true,
                wordpress_adv_hidden:false,
                indent:false,
                toolbar1:"formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,wp_more,spellchecker,fullscreen,wp_adv,go_shortcode_button",
                toolbar2:"strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
                toolbar3:"",
                toolbar4:"",
                tabfocus_elements:":prev,:next",
                body_class:"id post-type-post post-status-publish post-format-standard",
                height : "125"
            });
            // this is needed for the editor to initiate
            tinyMCE.execCommand('mceAddEditor', false, fullId);



        }
    });
}

function go_send_message(reset_vars, message_type, post_id) {
    var title = jQuery('.go_messages_title_input').val();
    //alert(title);
    if (message_type == "multiple_reset" || message_type == "single_reset" ){
        message_type = "reset";
    }else if(message_type == 'reset_stage'){
        message_type = 'reset_stage';
    }  else{
        message_type = "message";
    }

    if (message_type == "reset" || message_type == "reset_stage"){
        var message_toggle =  document.getElementById("go_custom_message_toggle").checked;
        var additional_penalty_toggle =  document.getElementById("go_additional_penalty_toggle").checked;
    }
    else{
        var message_toggle =  null;
        var additional_penalty_toggle =  null;
    }

    if (message_type == "message" || ((message_type == "reset" || message_type == "reset_stage") && message_toggle == true ) ){
        var message = jQuery('.go_messages_message_input').val();
        var message = go_tmce_getContent('go_message_text_area_id');
        //console.log("message: "+ message);
    }
    else{
        message = "";
       // console.log("no message");
    }

    console.log("1:" + message_type);
    if (message_type == "message" || ((message_type == "reset" || message_type == "reset_stage") && additional_penalty_toggle == true ) ){
        if (message_type == "message" ){
            var loot_toggle =( jQuery('#messages_form .go-acf-switch').hasClass("-on")) ? 1 : -1;

        }else{
            var loot_toggle = -1;

        }

        //calculate loot up or down
        //console.log("xp: " + jQuery('#messages_form .xp_messages').val());
        var xp = jQuery('#messages_form .xp_messages').val() * loot_toggle;
        //console.log("3:" + xp);
        var gold = jQuery('#messages_form .gold_messages').val() * loot_toggle;
        var health = jQuery('#messages_form .health_messages').val() * loot_toggle;


        var badges = jQuery('#messages_form #go_lightbox_go_badges_select').val();
        var groups = jQuery('#messages_form #go_lightbox_user_go_groups_select').val();
       /* if(loot_toggle == 1){
            var badges_toggle = true
            var groups_toggle = true;
        }else{
            var badges_toggle = false
            var groups_toggle = false;
        }*/

        //console.log("badges:");
        //console.log(badges);
    }
    else if ((message_type == "reset" || message_type == "reset_stage") && additional_penalty_toggle == false ){
        //var badges_toggle = false;
        //var groups_toggle = false;
        var loot_toggle = -1;
        var xp = 0;
        var gold = 0;
        var health = 0;
        var badges = null;
        var groups = null;
    }
    // send data
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_send_message;
    var gotoSend = {
        action:"go_send_message",
        _ajax_nonce: nonce,
        reset_vars: reset_vars,
        message_type: message_type,
        title: title,
        message: message,
        xp: xp,
        gold: gold,
        health: health,
        loot_toggle: loot_toggle,
        badges: badges,
       // groups_toggle: groups_toggle,
        groups: groups,
        penalty: additional_penalty_toggle

    };
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type:'POST',
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
        success: function( results ) {
            // show success or error message
            console.log("my send successful");
            Swal.fire(//sw2 OK
                {
                    type: 'success',
                    showConfirmButton: false,
                    timer: 1500
                }
            );


            //if this is was a reset message from the stats task tab, then redraw the table
            if(jQuery('#go_tasks_datatable').length && message_type === 'reset') {
                var stTable = jQuery('#go_tasks_datatable').DataTable();
                stTable.ajax.reload();
            }

            if(jQuery('#go_messages_datatable').length && message_type === 'message') {
                var smTable = jQuery('#go_messages_datatable').DataTable();
                smTable.ajax.reload();
            }

            if(jQuery('#go_clipboard_messages_datatable').length && message_type === 'message') {
                var mTable = jQuery('#go_clipboard_messages_datatable').DataTable();
                mTable.ajax.reload();
            }

            if(jQuery('#go_clipboard_activity_datatable').length && message_type === 'reset') {
                var caTable = jQuery('#go_clipboard_activity_datatable').DataTable();
                caTable.ajax.reload();
            }


            if(message_type == 'reset_stage'){
                var post_wrapper_class = ".go_blog_post_wrapper_" + post_id;
                //jQuery(post_wrapper_class).hide();
                jQuery(post_wrapper_class + " .go_reset_task_stage_blog").hide();
                jQuery(post_wrapper_class).addClass('reset');

               // jQuery(post_wrapper_class + " .go_status_icon").html('<i class="fas fa-times-circle fa-2x"></i>');
                jQuery(post_wrapper_class + " .go_status_icon").html('');
            }else{
                go_toggle_off();
            }
        }
    });
}

function go_messages_canned(target){

    console.log('go_messages_canned');
    //console.log(target);
    const title = jQuery(target).data('title');
    const message = jQuery(target).data('message');
    const toggle = jQuery(target).data('toggle');
    const xp = jQuery(target).data('xp');
    const gold = jQuery(target).data('gold');
    const health = jQuery(target).data('health');
    console.log(title);
    console.log(message);
    console.log(toggle);
    console.log(xp);
    console.log(gold);
    console.log(health);


    jQuery(target).closest('.swal2-container').find('.go_messages_title_input').val(title);
    //jQuery(target).closest('.go_feedback_form').find('.go_message_input').html($message);
    jQuery(target).closest('.swal2-container').find('.go_messages_message_input').val(message);
    jQuery(target).closest('.swal2-container').find('.note-editable').html(message);
    jQuery(target).closest('.swal2-container').find('.go_messages_toggle_input').val(toggle);
    jQuery(target).closest('.swal2-container').find('.go_messages_xp_input').val(xp);
    jQuery(target).closest('.swal2-container').find('.go_messages_gold_input').val(gold);
    jQuery(target).closest('.swal2-container').find('.go_messages_health_input').val(health);
    if (toggle){
        jQuery(target).closest('.swal2-container').find('.go-acf-switch').addClass('-on').removeClass('-off');
    }else{
        jQuery(target).closest('.swal2-container').find('.go-acf-switch').addClass('-off').removeClass('-on');
    }
    //jQuery(target).closest('.go_feedback_form').find('.go_percent_input').val($percent);


}

function go_toggle_off() {
    checkboxes = jQuery( '.go_checkbox' );
    for (var i = 0, n = checkboxes.length; i < n ;i++) {
        checkboxes[ i ].checked = false;
    }
}
