<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 10/13/18
 * Time: 8:41 PM
 */

//Uses the hidden footer that is in the core of GO.

/**
 * @param $blog_post_id
 * @param $suffix
 * @param $go_blog_task_id
 * @param $i
 * @param $bonus
 * @param $check_for_understanding
 * @param $all_content //if true load form that can't be submitted with blank content on all stages
 * @param $instructions
 */
function go_blog_form($blog_post_id, $suffix = '', $go_blog_task_id = null, $i = null, $bonus = null, $check_for_understanding = true, $all_content = false){
    //save draft button for drafts
    //print saved info for all
    global $all_feedback;
    $all_feedback = array();
    $is_lightbox = (isset($_POST['lightbox']) ?  $_POST['lightbox'] : false);
    if($is_lightbox === "true"){
        $suffix = '_lightbox';
        $autosave_wrapper = 'autosave_wrapper';
    }else{
        $autosave_wrapper = '';
    }
    ob_start();
    $text_toggle = true;
    $content ='';
    $title ='';
    $custom_fields = null;
    $url_content = null;
    $video_content = null;
    $media_content = null;
    $min_words = null;
    $post_status = null;
    $blog_meta = array();

    if (!empty($blog_post_id)) {
        $post = get_post($blog_post_id, OBJECT, 'edit');
        $content = $post->post_content;
        $title = get_the_title($blog_post_id);
        $blog_meta = get_post_meta($blog_post_id);
        $post_status = get_post_status($blog_post_id);

        $go_blog_task_id = (isset($blog_meta['go_blog_task_id'][0]) ? $blog_meta['go_blog_task_id'][0] : null); //for posts created before v4.6
        if (empty($go_blog_task_id)) {
            $go_blog_task_id = wp_get_post_parent_id($blog_post_id);//for posts created after v4.6
        }

        if($all_content){
            $content = '';
        }
    }

    $task_version = false;
    if(!empty($go_blog_task_id)) {

        if(!empty($blog_meta)){
            if(empty($i)){
                $i = $blog_meta['go_blog_task_stage'][0];
            }
            if(empty($bonus)){
                $bonus = $blog_meta['go_blog_bonus_stage'][0];
            }
        }
        $custom_fields = go_post_meta($go_blog_task_id);
        $task_version =  get_post_meta($go_blog_task_id, 'go_task_version', true);

        if ($bonus == true ) {

            $blog_title = (isset($custom_fields['go_bonus_stage_blog_options_v5_title'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_title'][0] : false);
            $text_toggle = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_text_toggle'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_text_toggle'][0] : true);
            $prompt = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_text_prompt'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_text_prompt'][0] : '');

            $min_words = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_text_minimum_length'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_text_minimum_length'][0] : null);


            //$is_private = (isset($custom_fields['go_bonus_stage_blog_options_v5_private'][0]) ?  $custom_fields['go_bonus_stage_blog_options_v5_private'][0] : false);
            $is_private = false;
            $opts = get_post_meta($go_blog_task_id, 'go_bonus_stage_blog_options_v5_opts', true) ? true : false;
            if($opts){//if this post has been saved with the new options
                $opts = get_post_meta($go_blog_task_id, 'go_bonus_stage_blog_options_v5_opts', true);//get the new options
                if(!is_array($opts)){
                    $opts = array();
                }
                if(in_array('private', $opts)){
                    $is_private = true;
                }
            }else{
                $is_private = get_post_meta($go_blog_task_id, 'go_bonus_stage_blog_options_v5_private', true);//old style private setting
            }

            $num_elements = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements'][0]) ?  $custom_fields['go_bonus_stage_blog_options_v5_blog_elements'][0] : false);

            if (!$blog_title){
                $blog_title = "Bonus";
            }
            for($x = 0; $x < $num_elements; $x++){//if this post has elements assigned, loop through them
                $uniqueid = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_uniqueid'][0]) ?  $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_uniqueid'][0] : 0);
                $type = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_element'][0]) ?  $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_element'][0] : null);
                $question = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_promptquestion'][0]) ?  $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_promptquestion'][0] : '');

                if ($type =='URL'){
                    $required_string = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_requirements_url_validation'][0]) ?  $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_requirements_url_validation'][0] : '');
                    go_url_field($blog_meta, $uniqueid, $all_content, $required_string, $question, $suffix);
                }

                if ($type =='File') {

                    $mime_types = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_requirements_allowed_types'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_requirements_allowed_types'][0] : array());
                    go_file_field($mime_types, $blog_meta, $uniqueid, $question, $all_content);
                }

                if ($type =='Video'){
                    go_video_field($blog_meta, $uniqueid, $all_content, $question, $suffix);
                }

                if ($type =='Text') {

                    //$this_min_words = get_post_meta($go_blog_task_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_minimum_length');
                    $this_min_words = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_minimum_length'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_minimum_length'][0] : array());

                    if($all_content){//if this is the all content view, don't load responses
                        $response = '';
                    }else {
                        $response = (isset($blog_meta[$uniqueid][0]) ? $blog_meta[$uniqueid][0] : null);//the submitted response
                        if($x + 1 === intval($num_elements) && empty($response)) {//if there is no response and this is the last question
                            //check if this is an old style call to action
                            $opts = get_post_meta($go_blog_task_id, 'go_bonus_stage_blog_options_v5_opts', true) ? true : false;
                            if ($text_toggle && $opts) {
                                $response = $content;
                            }
                        }
                    }

                    $full = 0;
                    $media = 0;
                    $text_tab = 0;

                    $options = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_editor_opts_toggle'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_editor_opts_toggle'][0] : false);
                    if($options === 'custom'){
                        $options = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_editor_opts'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_editor_opts'][0] : array());
                        $options = unserialize($options);
                        if(!is_array($options)){
                            $options = array();
                        }
                        $full = in_array('full', $options);
                        $media = in_array('media', $options);
                        $text_tab = in_array('text', $options);

                    }
                    go_text_field( $uniqueid, $question, $suffix, $this_min_words, $response, $full, $media, $text_tab);



                    //$this_min_words = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_minimum_length'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_minimum_length'][0] : array());
                    //go_text_field($uniqueid, $height, $question, $suffix, $this_min_words);
                }
            }
            if ($num_elements > 0){
                echo "<hr>";
            }
        }
        //Not a Bonus stage
        else{

            $blog_title = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_title'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_title'][0] : false);
            $text_toggle = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_toggle'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_toggle'][0] : true);
            $prompt = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_prompt'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_prompt'][0] : '');
            $min_words = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_minimum_length'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_minimum_length'][0] : null);
            //$is_private = (isset($custom_fields['go_stages_'.$i.'_blog_options_v5_private'][0]) ?  $custom_fields['go_stages_'.$i.'_blog_options_v5_private'][0] : false);

            $is_private = false;
            $opts = get_post_meta($go_blog_task_id, 'go_stages_' . $i . '_blog_options_v5_opts', true) ? true : false;
            if($opts){//if this post has been saved with the new options
                $opts = get_post_meta($go_blog_task_id, 'go_stages_' . $i . '_blog_options_v5_opts', true);//get the new options
                if(!is_array($opts)){
                    $opts = array();
                }

                if(in_array('private', $opts)){
                    $is_private = true;
                }
            }else{
                $is_private = get_post_meta($go_blog_task_id, 'go_stages_'.$i.'_blog_options_v5_privat', true);//old style private setting
            }

            $num_elements = (isset($custom_fields['go_stages_'.$i.'_blog_options_v5_blog_elements'][0]) ?  $custom_fields['go_stages_'.$i.'_blog_options_v5_blog_elements'][0] : false);

            $s = $i + 1;
            for($x = 0; $x < $num_elements; $x++){//if this post has elements assigned, loop through them
                $type = go_post_meta($go_blog_task_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_element');
                $uniqueid = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_uniqueid'][0]) ?  $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_uniqueid'][0] : 0);
                $question = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_promptquestion'][0]) ?  $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_promptquestion'][0] : '');

                if ($type =='URL'){
                    $required_string = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_requirements_url_validation'][0]) ?  $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_requirements_url_validation'][0] : '');
                    go_url_field($blog_meta, $uniqueid, $all_content, $required_string, $question, $suffix);
                }

                if ($type =='sketch'){
                    $required_string = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_requirements_url_validation'][0]) ?  $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_requirements_url_validation'][0] : '');
                    go_canvas_field($blog_meta, $uniqueid, $all_content, $required_string, $question, $suffix);
                }

                if ($type =='File') {
                    $mime_types = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_requirements_allowed_types'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_requirements_allowed_types'][0] : array());
                    go_file_field($mime_types, $blog_meta, $uniqueid, $question, $all_content);
                }

                if ($type =='Video'){
                    go_video_field($blog_meta, $uniqueid, $all_content, $question, $suffix);

                }

                if ($type =='Text') {
                    $this_min_words = get_post_meta($go_blog_task_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_minimum_length');

                    if($all_content){//if this is the all content view, don't load responses
                        $response = '';
                    }else {
                        $response = (isset($blog_meta[$uniqueid][0]) ? $blog_meta[$uniqueid][0] : null);//the submitted response
                        if($x + 1 === intval($num_elements) && empty($response)) {//if there is no response and this is the last question
                            //check if this is an old style call to action
                            $opts = get_post_meta($go_blog_task_id, 'go_stages_' . $i . '_blog_options_v5_opts', true) ? true : false;
                            if ($text_toggle && $opts) {
                                $response = $content;
                            }
                        }
                    }

                    $full = 0;
                    $media = 0;
                    $text_tab = 0;

                    $options = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_editor_opts_toggle'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_editor_opts_toggle'][0] : false);
                    if($options === 'custom') {
                        $options = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_editor_opts'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_editor_opts'][0] : array());
                        $options = unserialize($options);
                        if(!is_array($options)){
                            $options = array();
                        }
                        $full = in_array('full', $options);
                        $media = in_array('media', $options);
                        $text_tab = in_array('text', $options);
                    }

                    go_text_field( $uniqueid, $question, $suffix, $this_min_words, $response, $full, $media, $text_tab);
                }
            }
            if ($num_elements > 0){
                echo "<hr>";
            }

            if (!$blog_title){
                $blog_title = "Stage ". (intval($i) +1) ;
            }
        }
        //set the title on blog post forms attached to quests
        $title = get_the_title($go_blog_task_id);
        if(!empty($title)) {
            $title = $title . " - " . $blog_title;
        }else{
            $title = $blog_title;
        }

    }else{//this is not attached to a quest
        $is_private = get_post_meta($blog_post_id, 'go_blog_private_post', true) ? get_post_meta($blog_post_id, 'go_blog_private_post', true) : false;
    }

    $buffer = ob_get_contents();

    ob_end_clean();

    echo "<div class='go_blog_div go_blog_form_div {$autosave_wrapper}' data-status='$post_status'><div class='go_blog_title_container'>";

    if( !empty($go_blog_task_id) && $is_private) {
        echo "<div ><i>This post is private. Only you and the site administrators/instructors will be able to see it.</i></div>";
    }
    if($go_blog_task_id) {
        echo "<div><h3 style='width: 100%;' data-blog_post_title='fixed' data-blog_post_id ='{$blog_post_id}' id='go_blog_title{$suffix}'>" . $title . "</h3> </div>";
    }else{
        echo "<div>Title:<div><input style='width: 100%;' data-blog_post_title='custom' id='go_blog_title".$suffix."' type='text' placeholder='' value ='{$title}' data-blog_post_id ='{$blog_post_id}' ></div> </div>";

    }
    echo "</div>";

    $instructions = go_get_instructions($custom_fields, $bonus, $i);
    echo $instructions;
    echo $buffer;


    //this will be true if the new check for understanding is being used
    //if it is true, this next section isn't used

    $text_toggle_attr = '';
    if($text_toggle && !$task_version) {
        $text_toggle_attr = "text_toggle='1'";
        if(!empty($prompt)){
            echo "<p class='question'>".$prompt."</p>";
        }
/*
        $is_admin = go_user_is_admin();
        if($is_admin){
            $plugins = "charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,wordcount,go_shortcode_button,go_admin_comment,tma_annotate";
            $buttons = "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,wp_adv,go_shortcode_button,go_admin_comment,tma_annotate,tma_annotatedelete,tma_annotatehide";
        }else{
            $plugins = "charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,wordcount,tma_annotate";
            $buttons ="formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,wp_adv,tma_annotatehide";
        }
        $settings = array(
            'tinymce'=> array(
                'menubar'   => true,
                'plugins'   =>  "{$plugins}",
                'toolbar1'  =>  "{$buttons}",
                'toolbar2'  =>  "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
                'content_css' => plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . "dev/css/files/annotate.css"
            ),
            //'tinymce'=>true,
            //'wpautop' =>false,
            'textarea_name' => 'go_result'.$suffix,
            'media_buttons' => true,
            //'teeny' => true,
            'menubar' => false,
            'drag_drop_upload' => true
        );

        //echo "<button id='go_save_button' class='progress left'  check_type='blog' button_type='save'  admin_lock='true' >Save Draft</button> ";

        //$id = $_POST['editorID'];
        //$content = $_POST['content'];

        //wp_editor( $content, $id );

        wp_editor($content, 'go_blog_post'.$suffix, $settings);*/
        $name = 'go_result'.$suffix;
        $id = 'go_blog_post'.$suffix;

        $height = intval($min_words)/5;

        go_mce_activate($name, $id, $content, $height, 1, 1, 1 );

        //add stuff below the mce window if it is shown

        //Private Post Toggle
        if (empty($go_blog_task_id)) {//only if not attached to quest
            if ($is_private) {
                $checked = 'checked';
            } else {
                $checked = '';
            }
            echo "<div style='width: 100%;text-align: right;'><input type='checkbox' id='go_private_post{$suffix}' value='go_private_post{$suffix}' {$checked}> Private Post</div>";
        }
        //word Count
        if ($min_words > 0) {
            echo "<div id='go_blog_min' style='text-align:right'><span class='char_count'>" . $min_words . "</span> Words Required</div>";
        }
    }

    if ( (is_user_member_of_blog() || go_user_is_admin()) && !$all_content) {
        echo "<p id='go_blog_stage_error_msg' class='go_error_msg' style='display: none; color: red;'></p>";

        echo "<div class='go_blog_form_footer {$suffix}' style='background-color: #b3b3b3;'>";
            //$current_user = get_current_user_id();
            //$is_admin = go_user_is_admin();
            if ($suffix != '_lightbox') {
                //go_blog_status($blog_post_id, $is_admin, true);
                $button_class = "right";
            } else {
                $button_class = "left";
            }
        //if ($text_toggle) {
        //show save button if this is a draft, reset, trashed or new post
        $allow_drafts = array("draft", "reset", "trash", "initial", null);
        if (in_array($post_status, $allow_drafts)) {
            echo "<span id='go_save_button{$suffix}' class='go_button_round go_save_button progress {$button_class}'  status='{$i}' data-bonus_status='{$bonus}' check_type='skip_checks' button_type='save{$suffix}'  admin_lock='true' blog_post_id='{$blog_post_id}' blog_suffix='{$suffix}' task_id='{$go_blog_task_id}' data-check_for_understanding ='{$check_for_understanding}'><span class='go_round_inner'><i class='fas fa-save'></i></span></span>";
        }

        if ($suffix == '_lightbox'){
            echo "<button id='go_blog_submit' style='display:block;' class='go_blog_autosave_button' check_type='blog_lightbox' button_type='submit' blog_post_id ={$blog_post_id} blog_suffix ='_lightbox'  task_id='{$go_blog_task_id}' min_words='{$min_words}' blog_suffix ='' $text_toggle_attr data-check_for_understanding ='{$check_for_understanding}'>Submit</button>";
        }
        // }
        echo "</div>";
    }

    if($suffix !='_lightbox') {
        if ($blog_post_id) {
           // do_action('go_blog_template_after_post', $blog_post_id, false);
        }
    }


    echo "</div>";

    //Save Draft Button
    if($suffix =='_lightbox') {
        ?>
        <script>
            jQuery(document).ready(function () {
                jQuery('#go_save_button_lightbox').one("click", function (e) {
                    go_blog_submit( this, true );
                });
            });

        </script>

        <?php
    }
}

function go_mce_activate($name, $id, $content, $height, $full = 0, $media = 0, $text_tab = 0 ){
    $is_admin = go_user_is_admin();
    $options = get_option('options_go_text_editor_defaults');
    if(!$options){
        $options = array('media');
    }
    if($full === 0){
        $full = in_array('full', $options);
    }
    if($media === 0){
        $media = in_array('media', $options);
    }
    if($text_tab === 0){
        $text_tab = in_array('text', $options);
    }

    if($is_admin){
        //$plugins = "charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,wordcount,go_shortcode_button,go_admin_comment,tma_annotate";
        $plugins = "charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,wordcount,go_shortcode_button,go_admin_comment,tma_annotate";

        if($full){
            $buttons = "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,wp_adv,go_shortcode_button,go_admin_comment,tma_annotate,tma_annotatedelete,tma_annotatehide";
        }else{
            $buttons = "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,go_shortcode_button,go_admin_comment,tma_annotate,tma_annotatedelete,tma_annotatehide";
        }
    }else{
        //$plugins = "charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,wordcount,tma_annotate";
        $plugins = "charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,wordcount,go_shortcode_button,go_admin_comment,tma_annotate";
        if($full) {
            $buttons = "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,wp_adv,tma_annotatehide";
        }else{
            $buttons = "bold,italic,bullist,numlist,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,tma_annotatehide";
        }
    }

    if($full){
        $buttons2 = 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help';
    }else{
        $buttons2 = '';
    }

    $settings = array(
        'tinymce'=> array(
            'menubar'   => true,
            'plugins'   =>  "{$plugins}",
            'toolbar1'  =>  "{$buttons}",
            'toolbar2'  =>  "{$buttons2}",
            'content_css' => plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . "dev/css/files/annotate.css"
        ),
        //'tinymce'=>true,
        //'wpautop' =>false,
        'textarea_name' => $name,
        'media_buttons' => $media,
        //'teeny' => true,
        'quicktags' => $text_tab,
        'menubar' => false,
        'drag_drop_upload' => true,
        'textarea_rows' => $height,
        'editor_class' => 'go_mce_textarea',
    );

    //echo "<button id='go_save_button' class='progress left'  check_type='blog' button_type='save'  admin_lock='true' >Save Draft</button> ";

    //$id = $_POST['editorID'];
    //$content = $_POST['content'];

    //wp_editor( $content, $id );

    wp_editor($content, $id, $settings);
}

function go_file_field($mime_types, $blog_meta, $uniqueid, $question, $all_content){
    $mime_types_array = array();
    if (is_serialized($mime_types)) {
        $mime_types = unserialize($mime_types);
    }
    if(!is_array($mime_types)){
        $mime_types_array[] = $mime_types;
    }else{
        $mime_types_array = $mime_types;
    }
    //$mime_types = implode(",", $mime_types_array);
    //$mime_types_pretty = implode(", ", $mime_types_array);
    $mime_types_count = count($mime_types_array);
    if ($mime_types_count > 1) {
        $mime_types = null;
    }else if ($mime_types_count == 1){
        $mime_types = $mime_types_array[0];
        if($mime_types == 'all'){
            $mime_types = null;
        }
    }else{
        $mime_types = null;
    }


    $media_content = (isset($blog_meta[$uniqueid][0]) ? $blog_meta[$uniqueid][0] : null);
    if($all_content){
        $media_content = '';
    }

    $post_type = get_post_type($media_content);
    if($post_type != 'attachment'){
        $media_content = null;
    }


    echo "<hr><p class='question_title'>Add a File</p><div>";
    if(!empty($question)){
        echo "<p class='question' style='margin-bottom: unset;'>".$question."</p>";
    }

    go_upload_check_blog ($media_content, $uniqueid, $mime_types,  $uniqueid);
    echo "</div>";
    //echo "<div>";

    /*
    if (!empty($mime_types))
    {
        echo " (Allowed file type: " . $mime_types . ")";
    }
    echo "</div>";*/
}

function go_url_field($blog_meta, $uniqueid, $all_content, $required_string, $question, $suffix){
    $url_content = (isset($blog_meta[$uniqueid][0]) ? $blog_meta[$uniqueid][0] : null);
    if($all_content){
        $url_content = '';
    }

    echo "<hr><p class='question_title'>Submit a URL";
    if(!empty($question)){
        echo "<br><span class='question'>".$question."</span>";
    }
    echo "</p>";
    go_url_check_blog ('http://website.com', 'go_result_url'.$suffix , $url_content, 'URL', $required_string, $uniqueid);
    if (!empty($required_string)){
        echo "<span style='font-size: .8em;'>URL must contain: <i>".$required_string."</i></span>";
    }
}

function go_canvas_field($blog_meta, $uniqueid, $all_content, $required_string, $question, $suffix){
    $url_content = (isset($blog_meta[$uniqueid][0]) ? $blog_meta[$uniqueid][0] : null);
    if($all_content){
        $url_content = '';
    }

    echo "<hr><p class='question_title'>Submit a Sketch";
    if(!empty($question)){
        echo "<br><span class='question'>".$question."</span>";
    }
    echo "</p>";
    go_canvas_blog ('http://website.com', 'go_result_url'.$suffix , $url_content, 'sketch', $required_string, $uniqueid);

}

function go_video_field($blog_meta, $uniqueid, $all_content, $question, $suffix){
    $video_content = (isset($blog_meta[$uniqueid][0]) ? $blog_meta[$uniqueid][0] : null);
    if($all_content){
        $video_content = '';
    }
    if ($video_content === null){
        $video_content = (isset($blog_meta['go_blog_video'][0]) ? $blog_meta['go_blog_video'][0] : null);//previously saved content v4
    }
    echo "<hr><p class='question'>Submit a Video";
    if(!empty($question)){
        echo "<br><span class='question'>".$question."</span>";
    }
    echo "</p><div>Video Link:<div>";
    go_url_check_blog ('URL of Video', 'go_result_video'.$suffix, $video_content, 'video', '', $uniqueid);
    echo "</div> </div>";
}

function go_text_field($uniqueid, $question, $suffix, $min_words, $response, $full = 0, $media = 0, $text_tab = 0){
    //$height = $height[0];
    $min_words = (isset($min_words[0]) ?  $min_words[0] : null);
    $height = intval($min_words)/5;

    echo "<hr><p class='question'>".$question."</p>";
    //$height = intval($height);


    //////////////////
    $class = 'go_result_text'.$suffix;

    $is_admin = go_user_is_admin();
    if($is_admin && $suffix === '_lightbox') {
        echo "<div class='go_blog_element_input {$class}'  data-type='text'  data-uniqueID='{$uniqueid}' rows='{$height}' data-toolbar='{$full}'>";

      /*  $plugins = "wordpress, tma_annotate";
        $buttons = "tma_annotate,tma_annotatedelete,tma_annotatehide";

        $settings = array(
            'tinymce' => array(
                'menubar' => true,
                'plugins' => "{$plugins}",
                'toolbar1' => "{$buttons}",
                //'toolbar2' => "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
                'content_css' => plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . "dev/css/files/annotate.css"
            ),
            //'tinymce'=>true,
            //'wpautop' =>false,
            'textarea_name' => 'go_result2' . $suffix,
            'media_buttons' => true,
            //'teeny' => true,
            'menubar' => false,
            'drag_drop_upload' => true,
            'editor_class' => 'go_mce_textarea',
            'textarea_rows' => $height,
            //'textarea_rows' => ,
        );
        //wp_editor($response, 'my_post_' . $uniqueid . $suffix, $settings);*/

        $name = 'go_result2' . $suffix;
        $id = 'my_post_' . $uniqueid . $suffix;
        go_mce_activate($name, $id, $response, $height,  $full, $media, $text_tab );

        echo "</div>";
    }else{

        //echo "<div class='go_url_div'>";
        //echo "<textarea class='go_blog_element_input {$class} summernote'  data-type='text'  data-uniqueID='{$uniqueid}'  value='{$text_content}' style='width: 90%;' rows='{$height}'>{$text_content}</textarea>";

        echo "<div class='go_blog_element_input {$class}'  data-type='text'  data-uniqueID='{$uniqueid}'  data-min_words='{$min_words}' rows='{$height}' data-toolbar='{$full}'>";
       /* $is_admin = go_user_is_admin();
        if($is_admin){
            $plugins = "charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,wordcount,go_shortcode_button,go_admin_comment,tma_annotate";
            $buttons = "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,wp_adv,go_shortcode_button,go_admin_comment,tma_annotate,tma_annotatedelete,tma_annotatehide";
        }else{
            $plugins = "charmap,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,wordcount,tma_annotate";
            $buttons ="formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,wp_adv,tma_annotatehide";
        }

        $settings = array(
            'tinymce' => array(
                'menubar' => true,
                'plugins' => "{$plugins}",
                'toolbar1' => "{$buttons}",
                'toolbar2'  =>  "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
                'content_css' => plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . "dev/css/files/annotate.css"
            ),
            //'tinymce'=>true,
            //'wpautop' =>false,
            'textarea_name' => 'go_result2' . $suffix,
            'media_buttons' => true,
            //'teeny' => true,
            'menubar' => false,
            'drag_drop_upload' => true,
            'editor_class' => 'go_mce_textarea',
            'textarea_rows' => $height,
        );
        //wp_editor($response, 'my_post_' . $uniqueid . $suffix, $settings);*/

        $name = 'go_result2' . $suffix;
        $id = 'my_post_' . $uniqueid . $suffix;
        go_mce_activate($name, $id, $response, $height, $full, $media, $text_tab );
        if ($min_words > 0) {
            echo "<div id='go_blog_min' style='text-align:right'><span class='char_count'>" . $min_words . "</span> Words Required</div>";
        }

        echo "</div>";

   }


    //////////////////////



    //go_text_check_blog ($id = 'go_result', $text = null, $data_type = 'text',  $uniqueID = null, $height = 1)
    //go_text_check_blog ('go_result_text'.$suffix , $text_content, 'text', $uniqueid, $height);
}

/*
function go_autosave_info(){
    echo "<span class='go_autosave_info' style='display:none;' data-do_autosave='false' ></span>";
}*/

//add_filter( 'option_page_capability_' . ot_options_id(), create_function( '$caps', "return '$caps';" ), 999 );

//add_filter( 'option_page_capability_' . ot_options_id(), function($caps) {return $caps;},999);

/**
 * @param $blog_post_id
 * @param $go_blog_task_id
 * @param bool $check_for_understanding
 * @param bool $with_feedback
 * @param bool $show_author
 * @param bool $show_edit
 * @param bool $task_stage_num
 * @param bool $is_revision
 * @param bool $is_archive
 * @param string $instructions
 * @param $is_single
 */
function go_blog_post($blog_post_id, $go_blog_task_id = null, $check_for_understanding = false, $with_feedback = false, $show_author = false, $show_edit = false, $task_stage_num = null, $is_archive  = false, $is_single = false)
{
    global $all_feedback;
    $all_feedback = array();
    $current_user = get_current_user_id();
    $is_admin = go_user_is_admin();

    $text_toggle = true;
    $url_content = null;
    $video_content = null;
    $media_content = null;
    $min_words = null;

    $is_revision = false;

    //Get post info
    //get the post object for this post
    $post = get_post($blog_post_id, OBJECT, 'edit');
    //get content from the object
    $author_id = $post->post_author;

    global $go_blog_author_id;
    $go_blog_author_id = $author_id;
    if (intval($current_user) === intval($author_id)) {
        $is_current_user = true;
    } else {
        $is_current_user = false;
    }
    $content = $post->post_content;
    $post_date = $post->post_date;
    $post_modified = $post->post_modified;
    //apply the text filters
    $text_content = apply_filters('go_awesome_text', $content);
    $go_blog_author_id = null;
    //get info from the post_id
    $title = get_the_title($blog_post_id);
    if (isset($blog_post_id)) {
        $blog_meta = get_post_meta($blog_post_id);
    }

    //if the task that this post is attached to was not sent, try to get the task_id
    if (!isset($go_blog_task_id)) {
        $go_blog_task_id = (isset($blog_meta['go_blog_task_id'][0]) ? $blog_meta['go_blog_task_id'][0] : null); //for posts created before v4.6
        if (empty($go_blog_task_id)) {
            $go_blog_task_id = wp_get_post_parent_id($blog_post_id);//for posts created after v4.6
        }
        $is_revision = wp_is_post_revision( $blog_post_id );
        if($is_revision){
            $go_blog_task_id = wp_get_post_parent_id($go_blog_task_id);
        }
    }

    echo "<script>console.log('task id: {$go_blog_task_id}')</script>";

    //if the task_id is not 0, get some info about it
    if ($go_blog_task_id != 0) {
        $task_title = get_the_title($go_blog_task_id);
        $task_url = get_permalink($go_blog_task_id);
        if (empty($title)) {
            $title = $task_title;
        }

    }

    ob_start();

    $status = get_post_status($blog_post_id);
    echo "<div class='go_blog_post_wrapper go_blog_post_wrapper_$blog_post_id $status' style='padding: 20px;margin: 10px; background-color: white;' data-postid ='{$blog_post_id}'>";

    $icon = go_post_status_icon($blog_post_id, false, true);
    //$icon = "<span class='float:left; font-size: 12px;'>$icon</span>";

    echo $icon;


    $private = go_blog_is_private($blog_post_id);

    if ($private) {
        echo $private;
        //echo "";
    }


    if(!$is_single) {
        echo "<div class='go_post_title'>";
        if (!empty($task_url) && !$is_archive) {
            echo "<h2><a href='{$task_url}'>" . $title . "</a></h2>";
        } else {
            echo "<h2>" . $title . "</a></h2>";
        }
        echo "</div>";
    }

    echo "<div class='go_blog_meta' style='font-size: .9em;'>";

    if ($show_author) {
        $user_data = get_userdata($author_id);
        //$blogURL = get_site_url() . "/user/" . $user_data->user_login;

        //$user_display_name = go_get_user_display_name($author_id);

        ob_start();
        go_user_links($author_id, true, true, true, null, null, true );
        $links =  ob_get_clean();
        $avatar = go_get_avatar($author_id, false, array(32, 32));
        //ob_end_flush();
        //$full_name_toggle = get_option('options_go_full-names_toggle');
        echo "<div><span id='go-name'>{$avatar} <b>";
        echo go_get_fullname($author_id);
        echo "</b></span><br>{$links}";

        echo "<span>";
        if($is_admin){

            $seat_key = go_prefix_key('go_seat');
            $section_key = go_prefix_key('go_section');
            $sections = get_user_meta($author_id, $section_key, false);
            $seats = get_user_meta($author_id, $seat_key, false);

            if (!empty($sections)) {
                $i = 0;
                foreach ($sections as $section) {
                    $term = get_term($section);
                    if (!empty($term)) {
                        //$name = $term->name;
                        $name = (isset($term->name) ?  $term->name : '');
                        echo $name;
                        if(!empty($seats)) {
                            $name = get_option('options_go_seats_name');
                            $seat = $seats[$i];
                            $arr = explode("_", $seat, 2);
                            $first = $arr[0];
                            if(!empty($first)) {
                                echo " – $name " . $first ;
                            }
                            $i++;

                        }
                        echo "<br>";
                    }
                }

            }
            echo "</span>";

        }
        echo "</div>";

    }

    echo "<i>" . date("M d, Y g:i a", strtotime($post_date));
    if($post_modified != $post_date){
        echo "<br> updated: " . date("M d, Y g:i a", strtotime($post_modified));
    }

    echo "</i>";

    if(!$is_single) {
        $permalink = get_post_permalink($blog_post_id);
        echo "<br><a href='$permalink'>permalink</a>";
    }

    echo"</div><br><br>";
    //for each number of elements
    //get the type
    //get the uniqueID
    //get the data by UniqueID
    //if no data, get it by old key
    //if no data, get it by actions table
    //verify data
    echo "<div class='go_blog_elements'>";

    //if this post was submitted from a task, then add the task required fields
    if($go_blog_task_id != 0) {
        if (!isset($task_stage_num)) {
            //get the stage number from actions or in meta--
            $task_stage_num = (isset($blog_meta['go_blog_task_stage'][0]) ? $blog_meta['go_blog_task_stage'][0] : null);
            //$bonus_stage_num = (isset($blog_meta['go_blog_bonus_stage'][0]) ? $blog_meta['go_blog_bonus_stage'][0] : null);
        }

        //echo "<script>console.log('tsn: {$task_stage_num}')</script>";
        $i = $task_stage_num;
        echo "<script>console.log('i: {$i}')</script>";
        //if $i (task stage) is not set, then this must be a bonus stage
        $custom_fields = go_post_meta($go_blog_task_id);
        //variables for retrieving v4 content not in blog
        global $wpdb;
        // $go_actions_table_name = "{$wpdb->prefix}go_actions";
        // $user_id = get_current_user_id();

        //these are for the old blog posts that save right in the posts table--not as a question
        if ($i !== null && $i !== '0') {//regular stage
            $num_elements = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements'][0] : false);
            $text_toggle = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_toggle'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_toggle'][0] : true);
            $prompt = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_prompt'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_prompt'][0] : '');
        } else {//bonus stage
            $num_elements = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_elements'][0] : false);
            $text_toggle = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_text_toggle'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_text_toggle'][0] : true);
            $prompt = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_text_prompt'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_text_prompt'][0] : '');
        }

        echo "<script>console.log('#: {$num_elements}')</script>";
        for ($x = 0; $x < $num_elements; $x++) {
            if ($i !== null && $i !== '0') {//regular stage
                $bonus = false;
                $type = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_element'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_element'][0] : 0);
                $uniqueid = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_uniqueid'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_uniqueid'][0] : 0);
                $question = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_promptquestion'][0]) ?  $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_promptquestion'][0] : '');

            } else {//bonus stage
                $bonus = true;
                $type = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_element'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_element'][0] : 0);
                $uniqueid = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_uniqueid'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_uniqueid'][0] : 0);
                //$task_stage_num = $bonus_stage_num - 1;
                $question = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_promptquestion'][0]) ?  $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_promptquestion'][0] : '');

            }
            echo "<script>console.log('type: {$type}')</script>";
            //get the content by UniqueID
            $content = (isset($blog_meta[$uniqueid][0]) ? $blog_meta[$uniqueid][0] : null);
            if ($type == 'URL') {
                //  echo "<script>console.log('1: {$content}')</script>";
                if ($content === null) {
                    $content = (isset($blog_meta['go_blog_url'][0]) ? $blog_meta['go_blog_url'][0] : null);//v4 data
                    // echo "<script>console.log('2: {$content}')</script>";
                }
                if (!empty($content )) {
                    if(!empty($question)){
                        echo "<p class='question'>".$question."</p>";
                    }
                    go_print_URL_check_result($content);

                }
            }
            else if ($type == 'File') {
                //if null, check for v4 data
                if ($content === null) {
                    $content = (isset($blog_meta['go_blog_media'][0]) ? $blog_meta['go_blog_media'][0] : null);//v4 data
                }
                //if v5 or v4 data found, print the result
                if (!empty($content)) {
                    if(!empty($question)){
                        echo "<hr><p class='question'>".$question."</p>";
                    }
                    go_print_upload_check_result($content);
                }

            }
            else if ($type == 'Video') {
                if ($content === null) {
                    $content = (isset($blog_meta['go_blog_video'][0]) ? $blog_meta['go_blog_video'][0] : null);//v4 data
                }
                if (!empty($content)) {
                    if(!empty($question)){
                        echo "<p class='question'>".$question."</p>";
                    }
                    echo "<div class='go_required_blog_content width100'>";

                    $video_content = apply_filters('go_awesome_text', $content);
                    echo "$video_content";
                    echo "</div>";
                }
            }
            else if ($type == 'Text') {
                if ($content === null) {
                    $content = (isset($blog_meta['go_blog_url'][0]) ? $blog_meta['go_blog_url'][0] : null);//v4 data
                }
                if (!empty($content )) {
                    if(!empty($question)){
                        echo "<p class='question' style='margin-bottom: 5px'><b>".$question."</b></p>";
                    }
                    go_print_text_check_result($content);

                }
            }
            if ($type == 'sketch') {
                //  echo "<script>console.log('1: {$content}')</script>";

                if (!empty($content )) {
                    if(!empty($question)){
                        echo "<p class='question'>".$question."</p>";
                    }
                    go_print_canvas_result($content);

                }



            }

        }
    }

    $buffer = ob_get_contents();
    ob_end_clean();
    echo $buffer;

    echo "</div>";

    if($text_toggle) {

        if(!$is_admin && !$is_current_user){
            ////////////////////
            /// STRIP ANNOTATIONS
            $dom = new \DOMDocument;
            $dom->preserveWhiteSpace = false;
            $dom->loadHTML($text_content);
            $xpath = new \DOMXpath($dom);
            foreach ($xpath->query('//span[@class="annotation"]') as $span) {
                // Move all span tag content to its parent node just before it.
                while ($span->hasChildNodes()) {
                    $child = $span->removeChild($span->firstChild);
                    $span->parentNode->insertBefore($child, $span);
                }
                // Remove the span tag.
                $span->parentNode->removeChild($span);
            }
            $text_content = $dom->saveHTML();
            /////////////////////////
            ///
        }
        if (!empty($prompt)) {
            echo "<p class='question'>" . $prompt . "</p>";
        }
        echo "<div class='go_blog_content'>". $text_content . "</div>";
    }

    if (!$is_revision) {
        go_blog_post_footer($blog_post_id, $go_blog_task_id, false, $is_archive , $show_edit , $check_for_understanding);
    }
    if ( ($is_current_user || $is_admin) && !$is_single) {

        if ($with_feedback && ($is_current_user || $is_admin)) {
            do_action('go_blog_template_after_post', $blog_post_id, true, $is_archive);
        }
    }

    echo "</div>";

}

function go_blog_post_footer($blog_post_id, $go_blog_task_id = 0, $is_card = false, $is_archive = false, $show_edit = true, $check_for_understanding = false){
    $current_user = get_current_user_id();
    $is_admin = go_user_is_admin();
    $post = get_post($blog_post_id, OBJECT, 'edit');
    //get content from the object
    $author_id = $post->post_author;

    if ($go_blog_task_id != 0) {
        $task_title = get_the_title($go_blog_task_id);
        $task_url = get_permalink($go_blog_task_id);
    }

    echo "<div class='go_blog_form_footer'>";
    go_blog_status($blog_post_id, false, $is_archive);
    echo "<div><div class='go_blog_actions'>";

    if(!$is_archive) {
        if ((intval($current_user) == intval($author_id) && $show_edit) || $is_admin) {//if current user then show edit and maybe trash
            $status = get_post_status($blog_post_id);
            if(($status === 'reset' && !empty($task_url)) && !$is_admin) {
                $task_name = get_option('options_go_tasks_name_singular');
                echo "<div style='padding: 0 20px;'>Resubmit on {$task_name}: <a href='{$task_url}'>" . $task_title . "</a></div>";

            }else{
                echo "<div class='go_blog_opener go_blog_opener_round go_button_round' blog_post_id ='{$blog_post_id}' data-check_for_understanding ='{$check_for_understanding}'><span class='go_round_inner'><i class='fas fa-pencil-alt'></i></span></div>";

            }
        }
        if (($current_user == $author_id || $is_admin) && $check_for_understanding == false && empty($go_blog_task_id) && ($status !='trash' && $status !='reset' )) {
            echo '<div class="go_blog_trash go_button_round" blog_post_id ="' . $blog_post_id . '"><span class="go_round_inner"><i class="fas fa-trash"></i></span></div>';
        } else if ($is_admin  && ($status != 'trash' && $status != 'reset' )) {
            echo '<div data-uid="' . $author_id . '" data-task="' . $blog_post_id . '" class="go_reset_task_stage_blog go_button_round go_blog_reset" ><span class="go_round_inner"><i class="fas fa-times-circle"></i></span></div>';
            //echo '<span class="go_blog_trash" blog_post_id ="' . $blog_post_id . '"><i class="fa fa-times-circle fa-2x"></i></span>';
        }
        if($is_card){
            echo '<div class="go_blog_post_opener go_button_round" blog_post_id ="' . $blog_post_id . '"><span class="go_round_inner"><i class="fas fa-book-open"></i></span></div>';
            //<div class='go_blog_post_opener' blog_post_id ='{$blog_post_id}' style='float: right;'><i class='fas fa-book-open'></i></div>
        }
    }

    echo "</div></div></div>";
}

function go_blog_post_cards($blog_post_id, $go_blog_task_id = null, $show_author = false, $instructions = '')
{
    global $all_feedback;
    $all_feedback = array();
    //$current_user = get_current_user_id();
    $is_admin = go_user_is_admin();

    $text_toggle = true;
    $url_content = null;
    $video_content = null;
    $media_content = null;
    $min_words = null;

    //Get post info
    //get the post object for this post
    $post = get_post($blog_post_id, OBJECT, 'edit');
    //get content from the object
    $author_id = $post->post_author;

    global $go_blog_author_id;
    $go_blog_author_id = $author_id;
    /*
    if (intval($current_user) === intval($author_id)) {
        $is_current_user = true;
    } else {
        $is_current_user = false;
    }*/
    $content = $post->post_content;
    $post_date = $post->post_date;
    $post_modified = $post->post_modified;
    //apply the text filters
    $text_content = apply_filters('go_awesome_text', $content);
    $go_blog_author_id = null;


    //get info from the post_id
    if (isset($blog_post_id)) {
        $blog_meta = get_post_meta($blog_post_id);
        if (!isset($task_stage_num)) {
            //get the stage number from actions or in meta--
            $task_stage_num = (isset($blog_meta['go_blog_task_stage'][0]) ? $blog_meta['go_blog_task_stage'][0] : null);
        }
    }
    //if the task that this post is attached to was not sent, try to get the task_id
    if (!isset($go_blog_task_id)) {
        $go_blog_task_id = (isset($blog_meta['go_blog_task_id'][0]) ? $blog_meta['go_blog_task_id'][0] : null); //for posts created before v4.6
        if (empty($go_blog_task_id)) {
            $go_blog_task_id = wp_get_post_parent_id($blog_post_id);//for posts created after v4.6
        }
    }

    echo "<script>console.log('task id: {$go_blog_task_id}')</script>";

    ob_start();

    $status = get_post_status($blog_post_id);
    echo "<div class='go_blog_post_card_sizer'><div class='go_blog_post_wrapper go_blog_post_card_wrapper go_blog_post_wrapper_$blog_post_id $status' data-postid ='{$blog_post_id}'>";



    //if(!$is_single) {
    $title = get_the_title($blog_post_id);
    if ($go_blog_task_id != 0) {
        $task_url = get_permalink($go_blog_task_id);
        if (empty($title)) {
            $title = get_the_title($go_blog_task_id);
        }
    }


    echo "<div class='go_post_title'>";
    if (!empty($task_url)) {
        echo "<h3><a href='{$task_url}'>" . $title . "</a></h3>";
    } else {
        echo "<h3>" . $title . "</a></h3>";
    }
    echo "</div>";
       // }
   // }

    $icon = go_post_status_icon($blog_post_id, false, true);
    //$icon = "<span class='float:left; font-size: 12px;'>$icon</span>";

    echo $icon;

    $private = go_blog_is_private($blog_post_id);

    if ($private) {
        echo $private;
        //echo "";
    }

    if ($show_author) {
        //$user_data = get_userdata($author_id);
        //$blogURL = get_site_url() . "/user/" . $user_data->user_login;

        //$user_display_name = go_get_user_display_name($author_id);

        ob_start();
        go_user_links($author_id, true, true, true, null, null, true );
        $links =  ob_get_clean();
        //ob_end_flush();
        $avatar = go_get_avatar($author_id, false, array(32, 32));
        echo "<div>{$avatar} ";
        //$full_name_toggle = get_option('options_go_full-names_toggle');
        echo go_get_fullname($author_id);
        echo "</b></span><br>{$links}";



        $seat_key = go_prefix_key('go_seat');
        $section_key = go_prefix_key('go_section');
        $sections = get_user_meta($author_id, $section_key, false);
        $seats = get_user_meta($author_id, $seat_key, false);

        if (!empty($sections)) {
            $i = 0;
            $sections_and_seats = array();
            foreach ($sections as $section) {
                $term = get_term($section);
                if (!empty($term)) {
                    //$name = $term->name;
                    $name = (isset($term->name) ?  $term->name : '');
                    $section_seat = $name;
                    if($is_admin) {
                        if (!empty($seats)) {
                            $name = get_option('options_go_seats_name');
                            $seat = $seats[$i];
                            $arr = explode("_", $seat, 2);
                            $first = $arr[0];
                            if (!empty($first)) {
                                $section_seat .= " – $name " . $first;
                            }
                            $i++;
                        }
                    }
                    $sections_and_seats[] = $section_seat;
                }
            }
            $sections_and_seats = implode( ", ", $sections_and_seats );
            echo $sections_and_seats;



        }
        echo "</span>";


        echo "</div>";

    }

    echo "<div class='go_blog_meta' style='font-size: .9em;'>";
    echo "<i>" . date("M d, Y g:i a", strtotime($post_date));
    if($post_modified != $post_date){
        echo "<br> updated: " . date("M d, Y g:i a", strtotime($post_modified));
    }

    echo "</i>";

    $permalink = get_post_permalink($blog_post_id);
    echo "<br><a href='$permalink'>permalink</a>";
    echo"</div><br>";




    if($text_toggle) {
        if(!empty($prompt)){
            echo "<p class='question'>".$prompt."</p>";
        }
    }





    //for each number of elements
    //get the type
    //get the uniqueID
    //get the data by UniqueID
    //if no data, get it by old key
    //if no data, get it by actions table
    //verify data
    echo "<div class='go_blog_elements'>";

    //if this post was submitted from a task, then add the task required fields
    if($go_blog_task_id != 0) {
        echo "<script>console.log('tsn: {$task_stage_num}')</script>";
        $i = $task_stage_num;
        echo "<script>console.log('i: {$i}')</script>";
        //if $i (task stage) is not set, then this must be a bonus stage
        $custom_fields = go_post_meta($go_blog_task_id);
        //variables for retrieving v4 content not in blog
        global $wpdb;
        // $go_actions_table_name = "{$wpdb->prefix}go_actions";
        // $user_id = get_current_user_id();

        if ($i !== null) {//regular stage
            $num_elements = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements'][0] : false);
            $text_toggle = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_toggle'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_toggle'][0] : true);
            $prompt = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_prompt'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_prompt'][0] : '');
        } else {//bonus stage
            $num_elements = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_elements'][0] : false);
            $text_toggle = (isset($custom_fields['go_bonus_stage_blog_options_v5_bonus_blog_text_toggle'][0]) ? $custom_fields['go_bonus_stage_blog_options_bonus_blog_text_toggle'][0] : true);
            $prompt = (isset($custom_fields['go_bonus_stage_blog_options_v5_bonus_blog_text_prompt'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_bonus_blog_text_prompt'][0] : '');

        }

        echo "<script>console.log('#: {$num_elements}')</script>";
        for ($x = 0; $x < $num_elements; $x++) {
            if ($i !== null) {//regular stage
                $bonus = false;
                $type = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_element'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_element'][0] : 0);
                $uniqueid = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_uniqueid'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_uniqueid'][0] : 0);
                $question = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_promptquestion'][0]) ?  $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_promptquestion'][0] : '');

            } else {//bonus stage
                $bonus = true;
                $type = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_element'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_element'][0] : 0);
                $uniqueid = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_uniqueid'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_elements_' . $x . '_uniqueid'][0] : 0);
                //$task_stage_num = $bonus_stage_num - 1;
                $question = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_promptquestion'][0]) ?  $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_elements_' . $x . '_promptquestion'][0] : '');

            }
            echo "<script>console.log('type: {$type}')</script>";
            //get the content by UniqueID
            $content = (isset($blog_meta[$uniqueid][0]) ? $blog_meta[$uniqueid][0] : null);
            if ($type == 'URL') {
                //  echo "<script>console.log('1: {$content}')</script>";
                if ($content === null) {
                    $content = (isset($blog_meta['go_blog_url'][0]) ? $blog_meta['go_blog_url'][0] : null);//v4 data
                    // echo "<script>console.log('2: {$content}')</script>";
                }
                if (!empty($content )) {
                    if(!empty($question)){
                        echo "<p class='question'>".$question."</p>";
                    }
                    go_print_URL_check_result($content);

                }



            }
            else if ($type == 'File') {
                //if null, check for v4 data
                if ($content === null) {
                    $content = (isset($blog_meta['go_blog_media'][0]) ? $blog_meta['go_blog_media'][0] : null);//v4 data
                }
                //if v5 or v4 data found, print the result
                if (!empty($content)) {
                    if(!empty($question)){
                        echo "<hr><p class='question'>".$question."</p>";
                    }
                    go_print_upload_check_result($content);
                }

            }
            else if ($type == 'Video') {
                if ($content === null) {
                    $content = (isset($blog_meta['go_blog_video'][0]) ? $blog_meta['go_blog_video'][0] : null);//v4 data
                }
                if (!empty($content)) {
                    if(!empty($question)){
                        echo "<p class='question'>".$question."</p>";
                    }
                    echo "<div class='go_required_blog_content width100'>";

                    $video_content = apply_filters('go_awesome_text', $content);
                    echo "$video_content";
                    echo "</div>";
                }
            }else if ($type == 'Text') {
                if ($content === null) {
                    $content = (isset($blog_meta['go_blog_url'][0]) ? $blog_meta['go_blog_url'][0] : null);//v4 data
                }
                if (!empty($content )) {
                    if(!empty($question)){
                        echo "<p class='question'>".$question."</p>";
                    }
                    go_print_text_check_result($content);

                }



            }

        }
    }

    $buffer = ob_get_contents();
    ob_end_clean();
    echo $buffer;

    echo "</div>";

    if($text_toggle) {

        echo "<div class='go_blog_content'>". $text_content . "</div>";
    }


    //$favorite = go_blog_favorite($blog_post_id, false);
    //echo "<div class='go_blog_status_icons_cards'>";

    //echo $favorite ;
    echo "<div>";
    //go_blog_status($blog_post_id, true, false);
    //echo "<div style='padding-bottom: 10px'><div class='go_blog_post_opener' blog_post_id ='{$blog_post_id}' style='float: right;'><i class='fas fa-book-open'></i></div><div class='go_blog_opener' blog_post_id ='{$blog_post_id}' style='float: right;'><i class='fas fa-pencil-alt'></i></div></div>";
    go_blog_post_footer($blog_post_id, $go_blog_task_id, true);
    echo "</div>";

    echo "</div></div>";


}

/**
 * @param $blog_post_id
 * @param $is_admin
 */
function go_blog_status($blog_post_id, $is_form = false, $is_archive = false){

    $favorite = '';
    $status_icon = '';
    // $private = go_blog_is_private($blog_post_id);
    if (!$is_form) {
        $favorite = go_blog_favorite($blog_post_id, $is_archive);
    }
    $is_admin = go_user_is_admin();
    $user_id = get_current_user_id();
    $post_author_id = get_post_field('post_author', $blog_post_id);
    if($user_id == $post_author_id) {
        $is_current_user = true;
    }else{
        $is_current_user = false;
    }

    if($is_admin || $is_current_user) {
       // $percent = go_post_meta($blog_post_id, 'go_feedback_percent', true);
        $status_icon = go_post_status_icon($blog_post_id, $is_archive);

    }else{
        //$percent = '';
    }

   // $direction = (($percent > 0) ? '+' : '');
    //$class = (($percent > 0) ? 'up' : 'down');
    /*if ($percent == '' || empty($percent)) {
        $percent_hide = " style='display:none;' ";
    }else{
        $percent_hide = '';
    }
    $percent =  '<div class="go_status_percent '.$class.'"'.$percent_hide.' ><strong>'.$direction.$percent.'%</strong></div>';
*/
    $feedback_icon = get_feedback_icon($blog_post_id);
    $feedback_icon = "<div class='feedback_icon'>$feedback_icon</div>";
    if (!empty($status_icon) || !empty($favorite) ) {
        echo "
            <div class='go_blog_status'>
            <div class='go_blog_status_icons'>";

        echo $status_icon . $favorite . $feedback_icon;//; . $percent;
        echo "</div></div>";
    }else{
        echo "<div></div>";
    }

}

// Register Custom Post Type
/**
 *
 */
function go_blogs() {

    $labels = array(
        'name'                  => _x( 'User Blog Posts', 'Post Type General Name', 'text_domain' ),
        'singular_name'         => _x( 'User Blog Post', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'             => __( 'User Blog Posts', 'text_domain' ),
        'name_admin_bar'        => __( 'User Blog Post', 'text_domain' ),
        'archives'              => __( 'Item Archives', 'text_domain' ),
        'attributes'            => __( 'Item Attributes', 'text_domain' ),
        'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
        'all_items'             => __( 'All Items', 'text_domain' ),
        'add_new_item'          => __( 'Add New Item', 'text_domain' ),
        'add_new'               => __( 'Add New', 'text_domain' ),
        'new_item'              => __( 'New Item', 'text_domain' ),
        'edit_item'             => __( 'Edit Item', 'text_domain' ),
        'update_item'           => __( 'Update Item', 'text_domain' ),
        'view_item'             => __( 'View Item', 'text_domain' ),
        'view_items'            => __( 'View Items', 'text_domain' ),
        'search_items'          => __( 'Search Item', 'text_domain' ),
        'not_found'             => __( 'Not found', 'text_domain' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
        'featured_image'        => __( 'Featured Image', 'text_domain' ),
        'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
        'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
        'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
        'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
        'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
        'items_list'            => __( 'Items list', 'text_domain' ),
        'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
        'filter_items_list'     => __( 'Filter items list', 'text_domain' ),

    );
    $rewrite = array(
        'slug'                  => 'blogs',
        'with_front'            => true,
        'pages'                 => true,
        'feeds'                 => true,
    );
    $args = array(
        'label'                 => __( 'User Blog Post', 'text_domain' ),
        'description'           => __( 'User Blog Posts', 'text_domain' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'author', 'revisions' ),
        'taxonomies'            => array(  ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => false,
        'menu_position'         => 20,
        'show_in_admin_bar'     => false,
        'show_in_nav_menus'     => false,
        'can_export'            => false,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        //'rewrite'               => $rewrite,
        'capability_type'       => 'page',
    );
    register_post_type( 'go_blogs', $args );

}
add_action( 'init', 'go_blogs', 0 );

// Register custom post status
/**
 *
 */
function go_custom_post_status(){
    register_post_status( 'initial', array(
        'label'                     => _x( 'Initial', 'post' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Initial <span class="count">(%s)</span>', 'Initial <span class="count">(%s)</span>' ),
    ) );

    register_post_status( 'unread', array(
        'label'                     => _x( 'Unread', 'post' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Unread <span class="count">(%s)</span>', 'Unread <span class="count">(%s)</span>' ),
    ) );

    register_post_status( 'read', array(
        'label'                     => _x( 'Read', 'post' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Read <span class="count">(%s)</span>', 'Read <span class="count">(%s)</span>' ),
    ) );

    register_post_status( 'reset', array(
        'label'                     => _x( 'Reset', 'post' ),
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Reset <span class="count">(%s)</span>', 'Reset <span class="count">(%s)</span>' ),
    ) );

    register_post_status( 'revise', array(
        'label'                     => _x( 'Revise', 'post' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Revise <span class="count">(%s)</span>', 'Revise <span class="count">(%s)</span>' ),
    ) );
}
add_action( 'init', 'go_custom_post_status', 0 );

/**
 * Creates new rewrite rules.
 * Only needs to to be run on activation/flushing of rewrite rules
 */
function go_blogs_rewrite() {
    // we are telling wordpress that if somebody access yoursite.com/all-post/user/username
    // wordpress will do a request on this query var yoursite.com/index.php?query_type=user_blog&uname=username
    //flush_rewrite_rules();

    add_rewrite_rule( "^user/([^/]*)/page/(.*)/?", 'index.php?query_type=user_blog&uname=$matches[1]&paged=$matches[2]', "top");
    add_rewrite_rule( "^user/(.*)", 'index.php?query_type=user_blog&uname=$matches[1]', "top");

}
add_action( 'init', 'go_blogs_rewrite' );

/**
 * @param $vars
 * @return array
 */
function go_custom_query($vars ) {
    // we will register the two custom query var on wordpress rewrite rule
    $vars[] = 'query_type';
    $vars[] = 'uname';
    $vars[] = 'paged';
    return $vars;
}
// Then add those two functions on their appropriate hook and filter
add_filter( 'query_vars', 'go_custom_query' );

/**
 * @param $template
 * @return string
 */
function go_template_loader($template){

    // get the custom query var we registered
    $query_var = get_query_var('query_type');

    // load the custom template if ?query_type=all_post is  found on wordpress url/request
    if( $query_var == 'user_blog' ){
        $directory = plugin_dir_path( __FILE__ ) . '/templates/go_user_blog_template.php';
        //$directory = plugin_dir_path( __FILE__ ) . '/templates/go_save_blog.php';
        return $directory;
    }
    return $template;
}
add_filter('template_include', 'go_template_loader');


/**
 * SINGLE BLOG POST TEMPLATE
 * USE REGULAR TEMPLATE WITH A SHORTCODE
 */
add_filter( 'template_include', 'go_single_blog_template_function', 1 );
function go_single_blog_template_function( $template_path ) {
    if ( get_post_type() == 'go_blogs' ) {
        if ( is_single() ) {
            // checks if the file exists in the theme first
            if ( $theme_file = locate_template( array (  'index.php' ) )) {
                $template_path = $theme_file;
                add_filter( 'the_content', 'go_single_blog_filter_content' );
            }
        }
    }
    return $template_path;
}

function go_single_blog_filter_content() {
    $post_id = get_the_id();
    echo do_shortcode( '[go_single_blog "'.$post_id.'"]' );
}


function go_single_blog_shortcode($atts, $content = null ) {
    $post_id = $atts[0];
    go_blog_post($post_id, null, false, false, true, false, null, false, true);
}
add_shortcode( 'go_single_blog','go_single_blog_shortcode' );

