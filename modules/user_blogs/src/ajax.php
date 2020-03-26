<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 10/13/18
 * Time: 8:45 PM
 */


function go_blog_opener(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_blog_opener' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_blog_opener' ) ) {
        echo "refresh";
        die( );
    }

    $blog_post_id = ( ! empty( $_POST['blog_post_id'] ) ? (int) $_POST['blog_post_id'] : 0 );
    $check_for_understanding = ( ! empty( $_POST['check_for_understanding'] ) ? (int) $_POST['check_for_understanding'] : false );
    $min_words = null;
    $text_toggle = null;
    //$file_toggle = null;
    //$video_toggle = null;
    //$url_toggle = null;
    $i = null;
    //$required_string = null;
    $go_blog_task_id = null;
    $bonus = false;

    $is_admin = go_user_is_admin();

    if ($blog_post_id != 0) { //if opening an existing post
        //get the minimum character count to add to the button
        $blog_meta = get_post_meta($blog_post_id);
        //$go_blog_task_id = wp_get_post_parent_id($blog_post_id);

        $stage = (isset($blog_meta['go_blog_task_stage'][0]) ? $blog_meta['go_blog_task_stage'][0] : null);

        $go_blog_task_id = go_get_task_id($blog_post_id);
        if(!empty($go_blog_task_id)) {
            $custom_fields = get_post_meta($go_blog_task_id);
            $task_is_locked = go_task_locks($go_blog_task_id, true,null, false, $custom_fields);
            if ( $task_is_locked === true && !$is_admin ) {
                echo "locked";
                die();
            }

            if ($stage !== null) {
                $i = intval($stage);
                //$url_toggle = (isset($custom_fields['go_stages_' . $i . '_blog_options_url_toggle'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_url_toggle'][0] : null);
                //$file_toggle = (isset($custom_fields['go_stages_' . $i . '_blog_options_attach_file_toggle'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_attach_file_toggle'][0] : null);
                //$video_toggle = (isset($custom_fields['go_stages_' . $i . '_blog_options_video'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_video'][0] : null);
                //$text_toggle = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_toggle'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_toggle'][0] : null);
               // $min_words = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_minimum_length'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_minimum_length'][0] : null);
                //$required_string = (isset($custom_fields['go_stages_'.$i.'_blog_options_url_url_validation'][0]) ?  $custom_fields['go_stages_'.$i.'_blog_options_url_url_validation'][0] : null);
                $bonus = false;
            }
            else{
                //$url_toggle = (isset($custom_fields['go_bonus_stage_blog_options_bonus_url_toggle'][0]) ? $custom_fields['go_bonus_stage_blog_options_bonus_url_toggle'][0] : null);
                //$file_toggle = (isset($custom_fields['go_bonus_stage_blog_options_bonus_attach_file_toggle'][0]) ? $custom_fields['go_bonus_stage_blog_options_bonus_attach_file_toggle'][0] : null);
                //$video_toggle = (isset($custom_fields['go_bonus_stage_blog_options_bonus_video'][0]) ? $custom_fields['go_bonus_stage_blog_options_bonus_video'][0] : null);
                //$text_toggle = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_text_toggle'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_text_toggle'][0] : null);
               // $min_words = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_text_minimum_length'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_text_minimum_length'][0] : null);
                //$required_string = (isset($custom_fields['go_bonus_stage_blog_options_url_url_validation'][0]) ?  $custom_fields['go_stages_'.$stage.'_blog_options_url_url_validation'][0] : null);
                $bonus = true;
            }
        }
    }
    if($blog_post_id){
        $post = get_post($blog_post_id);
        $autosave = wp_get_post_autosave($blog_post_id);

        $autosave_time = strtotime($autosave->post_modified);
        $post_time = strtotime($post->post_modified);

        if( $post_time < $autosave_time){
            $check_autosave = true;
        }

    }
    $check_autosave = false;
    if($blog_post_id){
        $post = get_post($blog_post_id);
        $autosave = wp_get_post_autosave($blog_post_id);

        if($autosave) {
            $autosave_time = strtotime($autosave->post_modified);
            $post_time = strtotime($post->post_modified);


            if ($post_time < $autosave_time) {
                $check_autosave = true;
                go_check_autosave($post, $autosave);
            }
        }

    }
    if(!$check_autosave){
        go_blog_form($blog_post_id, '_lightbox', $go_blog_task_id, $i, $bonus, $check_for_understanding );
    }
    echo "<p id='go_blog_error_msg' class='go_error_msg' style='display: none; color: red;'></p>";
    die();
}

function go_blog_post_opener(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }else{
        $is_logged_in = true;
    }

    //check_ajax_referer( 'go_blog_opener' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_blog_post_opener' ) ) {
        echo "refresh";
        die( );
    }

    $blog_post_id = ( ! empty( $_POST['blog_post_id'] ) ? (int) $_POST['blog_post_id'] : 0 );

    go_blog_post($blog_post_id, null, false, true);

    die();
}

function go_blog_trash(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_blog_trash' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_blog_trash' ) ) {
        echo "refresh";
        die( );
    }

    global $wpdb;

    $blog_post_id = ( ! empty( $_POST['blog_post_id'] ) ? (int) $_POST['blog_post_id'] : 0 );

    if ($blog_post_id != 0 && !empty($blog_post_id)) {

        $blog_meta = get_post_meta($blog_post_id);
        //$go_blog_task_id = (isset($blog_meta['go_blog_task_id'][0]) ? $blog_meta['go_blog_task_id'][0] : null);
        $stage_num = (isset($blog_meta['go_blog_task_stage'][0]) ? $blog_meta['go_blog_task_stage'][0] : null);
        $bonus_stage_num = (isset($blog_meta['go_blog_bonus_stage'][0]) ? $blog_meta['go_blog_bonus_stage'][0] : null);
        $aTable = "{$wpdb->prefix}go_actions";

        //try to get task_id from the old style blog posts
        if(empty($go_blog_task_id)) {
            $go_blog_task_id = $wpdb->get_var($wpdb->prepare("SELECT source_id
				FROM {$aTable} 
				WHERE result = %d AND  action_type = %s
				ORDER BY id DESC LIMIT 1",
                intval($blog_post_id),
                'task'));
        }

        if(empty($go_blog_task_id)) {//this post is not associated with a task
            //wp_trash_post( intval($blog_post_id ) );
            wp_update_post(array(
                'ID'    =>  $blog_post_id,
                'post_status'   =>  'trash'
            ));
        }
        else{

            if ($stage_num !== null) {//if a stage number was sent (it is not a bonus stage)
                $stage_type = 'stage';
                $new_status_task = $stage_num;
                $stage_num = $stage_num + 1 ;

                $new_bonus_status_task = null;


                //get all tasks with a ID that is greater and add loot then subtract
                //get all blog post IDs and set as trash

            }
            else{//if it is a bonus only mark that one and remove the loot
                $stage_type = 'bonus_status';
                $new_status_task = null;
                $new_bonus_status_task = $bonus_stage_num;
            }

            ////////////////////
            ///
            ///
            $result = $wpdb->get_results($wpdb->prepare("SELECT id, uid, xp, gold, health, badges, groups, check_type, result
				FROM {$aTable} 
				WHERE result = %d AND source_id = %d AND action_type = %s
				ORDER BY id DESC LIMIT 1",
                $blog_post_id,
                $go_blog_task_id,
                'task'), ARRAY_A);

            $loot = $result;
            $result = $result[0];
            //$result = json_decode(json_encode($result), true);
            $id = $result['id'];
            $uid = $result['uid'];

            if ($stage_type === 'stage'){ //remove all loot since this stage, including this stage and mark all other blog posts deleted


                $loot = $wpdb->get_results($wpdb->prepare("SELECT xp, gold, health, badges, groups, check_type, result
				FROM {$aTable} 
				WHERE uid = %d AND source_id = %d AND action_type = %s AND id >= %d
				ORDER BY id ", $uid, $go_blog_task_id, 'task', $id), ARRAY_A);

            }
            $xp = 0;
            $gold = 0;
            $health = 0;
            $badge_array = array();
            $group_array = array();

            foreach($loot as $loot_row){
                $xp = $loot_row['xp'] + $xp;
                $gold = $loot_row['gold'] + $gold;
                $health = $loot_row['health'] + $health;
                $badges = $loot_row['badges'];
                $groups = $loot_row['groups'];
                $check_type = $loot_row['check_type'];
                $result = $loot_row['result'];

                if ($check_type === "blog"){
                    //wp_trash_post( intval($result ) );
                    wp_update_post(array(
                        'ID'    =>  intval($result ),
                        'post_status'   =>  'trash'
                    ));
                }

                $badge_task = unserialize($badges);
                $group_task = unserialize($groups);
                if (!is_array($badge_task)){
                    $badge_task = array();
                }
                if (!is_array($group_task)){
                    $group_task = array();
                }
                $badge_array = array_merge($badge_task, $badge_array);
                $group_array = array_merge($group_task, $group_array);
            }

            if (!empty($badge_array)) {//else if badges toggle is false and badges exist
                //$result[] = "badges-";
                go_remove_badges($badge_array, $uid, false);//remove badges
                $badge_ids = serialize($badge_array);
            }else{
                $badge_ids = "";
            }

            if (!empty($group_array)) {//else if groups toggle is false and groups exist
                //$result[] = "groups-";
                go_remove_groups($group_array, $uid, false);//remove groups
                $group_ids = serialize($group_array);
            }else{
                $group_ids = "";
            }
            //$result = serialize($result);


            $go_task_table_name = "{$wpdb->prefix}go_tasks";
            $time = current_time('mysql');
            $last_time = $time;

            $xp = intval($xp) * -1;
            $gold = intval($gold) * -1;
            $health = intval($health) * -1;

            $new_status_task = intval($new_status_task);
            $new_bonus_status_task = intval($new_bonus_status_task);
            if ($stage_type === 'bonus_status'){
                $update_col = "bonus_status = -1 + bonus_status ";
                $update_col = max($update_col,0);
            }else{
                $update_col = "status = {$new_status_task}, bonus_status = 0";
            }

            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$go_task_table_name} 
                    SET 
                        {$update_col},
                        xp = {$xp} + xp,
                        gold = {$gold} + gold,
                        health = {$health} + health,
                        last_time = IFNULL('{$last_time}', last_time)         
                    WHERE uid= %d AND post_id=%d ",
                    intval($uid),
                    intval($go_blog_task_id)
                )
            );
            go_update_actions($uid, 'reset',  $go_blog_task_id, $new_status_task, $new_bonus_status_task, null, null, null, null, null, null,  $xp, $gold, $health, $badge_ids, $group_ids, false);

            update_user_option(intval($uid), 'go_new_messages', true);


        }
        ob_start();
        go_blog_post($blog_post_id, '', false, true, false, true);
        $wrapper = ob_get_contents();

        ob_end_clean();
        echo $wrapper;
        die();
    }
}

function go_blog_autosave(){
//return;
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_blog_submit' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_blog_autosave' ) ) {
        echo "refresh";
        die( );
    }


    add_filter( 'user_has_cap', 'go_allow_to_autosave', 10, 4 );

    $blog_post_id = intval(!empty($_POST['blog_post_id']) ? (string)$_POST['blog_post_id'] : '');

    if($blog_post_id){
        $post_author_id = get_post_field( 'post_author', $blog_post_id );
        $user_id = get_current_user_id();
        if ($post_author_id != $user_id){
            echo "not your post";
            die( );
        }
    }
    $is_private = !empty($_POST['blog_private']) ? $_POST['blog_private'] : 0;
    $post_id = !empty($_POST['post_id']) ? intval($_POST['post_id']) : null;

    $post_status = 'unread';//if submit was pressed, set status as unread


    $blog_meta = get_post_meta($blog_post_id);
    //$go_blog_task_id = intval(isset($blog_meta['go_blog_task_id'][0]) ? $blog_meta['go_blog_task_id'][0] : null);
    $go_blog_task_id = wp_get_post_parent_id($blog_post_id);
    $go_blog_task_stage = intval(isset($blog_meta['go_blog_task_stage'][0]) ? $blog_meta['go_blog_task_stage'][0] : null);
    $go_blog_task_bonus = (isset($blog_meta['go_blog_bonus_stage'][0]) ? $blog_meta['go_blog_bonus_stage'][0] : null);


    if ($go_blog_task_bonus !== null){
        $go_blog_task_bonus = intval($go_blog_task_bonus);
        $go_blog_task_stage = null;
    }

    $result = go_save_blog_post($go_blog_task_id, $go_blog_task_stage, $go_blog_task_bonus, $post_status, $is_private, true);

    if ( is_wp_error( $result ) ) {
        $message = $result;
    }else{
        $message = 'autosave complete';
    }

    echo json_encode(
        array(
            'json_status' => 'success',
            'message'   => $message
        )
    );

    die();
}

function go_blog_submit(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_blog_submit' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_blog_submit' ) ) {
        echo "refresh";
        die( );
    }
    $blog_post_id = intval(!empty($_POST['blog_post_id']) ? (string)$_POST['blog_post_id'] : '');
    $check_for_understanding = !empty($_POST['check_for_understanding']) ? (string)$_POST['check_for_understanding'] : false;
    $button = !empty($_POST['button']) ? (string)$_POST['button'] : false;
    $post_status = !empty(get_post_status($blog_post_id)) ? get_post_status($blog_post_id) : 'draft';//if new post, set as draft
    $is_private = !empty($_POST['blog_private']) ? $_POST['blog_private'] : 0;
    $post_id = !empty($_POST['post_id']) ? intval($_POST['post_id']) : null;

    if ($button == 'submit'){
        $post_status = 'unread';//if submit was pressed, set status as unread


        //if this is an existing post, check if it is being edited by admin and set to read
        if (go_post_exists($blog_post_id)){
            $user_id = get_current_user_id();
            $author_id = get_post_field( 'post_author', $blog_post_id );
            if($user_id != $author_id){
                $post_status = 'read';//if submit was pressed by admin on another's post, set to read
            }
        }
    }

    if($blog_post_id) {//if this blog post already exists
        $blog_meta = get_post_meta($blog_post_id);
        //$go_blog_task_id = intval(isset($blog_meta['go_blog_task_id'][0]) ? $blog_meta['go_blog_task_id'][0] : null);
        $go_blog_task_id = wp_get_post_parent_id($blog_post_id);
        $go_blog_task_stage = intval(isset($blog_meta['go_blog_task_stage'][0]) ? $blog_meta['go_blog_task_stage'][0] : null);
        $go_blog_task_bonus = (isset($blog_meta['go_blog_bonus_stage'][0]) ? $blog_meta['go_blog_bonus_stage'][0] : null);
    }else {
        //$go_blog_task_id = intval(!empty($_POST['post_id']) ? (string)$_POST['post_id'] : '');
        $go_blog_task_id = $post_id;
        $go_blog_task_stage = intval(!empty($_POST['go_blog_task_stage']) ? (string)$_POST['go_blog_task_stage'] : null);
        $go_blog_task_bonus = ((($_POST['go_blog_bonus_stage']) !='') ? intval($_POST['go_blog_bonus_stage']) : null);
    }

    if ($go_blog_task_bonus !== null){
        $go_blog_task_bonus = intval($go_blog_task_bonus);
        $go_blog_task_stage = null;
    }
    $result = go_save_blog_post($go_blog_task_id, $go_blog_task_stage, $go_blog_task_bonus, $post_status, $is_private);

    ob_start();
    if ($button == 'submit'){
        go_noty_message_generic('success', 'Post Saved Successfully', '', 2000);

    }else{
        go_noty_message_generic('success', 'Draft Saved Successfully', '', 2000);
    }
    $buffer = ob_get_contents();

    ob_end_clean();


    ob_start();
    if($button == 'submit') {

        go_blog_post($blog_post_id, $post_id, $check_for_understanding, true, false, true, $go_blog_task_stage);
    }
    $wrapper = ob_get_contents();
    ob_end_clean();


    echo json_encode(
        array(
            'json_status' => 'success',
            'blog_post_id' => $result,
            'message' => $buffer,
            'wrapper' => $wrapper
        )
    );

    die();
}

function go_save_blog_post($go_blog_task_id = null, $stage = null, $bonus_status = null, $post_status = false, $is_private = 0, $is_autosave = false){

    $result = (!empty($_POST['result']) ? (string)$_POST['result'] : ''); // Contains the result from the check for understanding
    $result_title = (!empty($_POST['result_title']) ? (string)$_POST['result_title'] : '');// Contains the result from the check for understanding
    $blog_post_id = intval(!empty($_POST['blog_post_id']) ? (string)$_POST['blog_post_id'] : null);
    if (go_post_exists($blog_post_id) == false){
        $blog_post_id = null;
        $user_id = get_current_user_id();
    }else{
        $user_id = get_post_field( 'post_author', $blog_post_id );
    }
    $status = null;
    $uniqueid = null;
    if (is_int($go_blog_task_id) and $go_blog_task_id > 0) {//if this is attached to a quest
        //update_post_meta( $post_id, 'go_is_reset', false );
        //stage uniqueid

        global $wpdb;
        $go_task_table_name = "{$wpdb->prefix}go_tasks";



        //check if there is an existing post from this quest and stage

       // $args = array('meta_key' => 'go_stage_uniqueid', 'meta_value' => $uniqueid, 'post_type' => 'go_blogs', 'post_parent' => $post_id, 'author' => $user_id, 'post_status' => 'read, unread, reset, draft, trash');
        //$go_blog_post_ids = get_posts($args);

        if(!empty($go_blog_post_id)){
            $post = $go_blog_post_id;
            $db_id = $post->ID;
            if (is_int($db_id)){
                $blog_post_id = $db_id;
            }
        }
        if ($bonus_status !== null) {//if this is a bonus stage blog post, set variables
            //$uniqueid = 'bonus';
            $bonus_status = $bonus_status + 1;
            $stage = null;
            $status = null;
            //$meta_key = 'go_bonus_stage_blog_options_v5_bonus_private';
            //$meta_key = 'go_bonus_stage_blog_options_v5_bonus_private';
            //$is_private =  get_post_meta($go_blog_task_id, 'go_bonus_stage_blog_options_v5_opts', true) ? get_post_meta($go_blog_task_id, 'go_bonus_stage_blog_options_v5_opts', true) : get_post_meta($go_blog_task_id, 'go_bonus_stage_blog_options_v5_bonus_private', true);
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

        } else {//if this is a regular stage blog post, set variables
            $uniqueid = go_post_meta($go_blog_task_id, 'go_stages_' . $stage . '_uniqueid', true);
            $status = $stage;
           // $meta_key = 'go_stages_' . $status . '_blog_options_v5_private';
            //$new_style =  get_post_meta($go_blog_task_id, 'go_stages_' . $i . '_blog_options_v5_opts', true) ? true : false;
            //$opts =  get_post_meta($go_blog_task_id, 'go_stages_' . $status . '_blog_options_v5_opts', true) ? get_post_meta($go_blog_task_id, 'go_stages_' . $status . '_blog_options_v5_opts', true) : false;


            $is_private = false;
            $opts = get_post_meta($go_blog_task_id, 'go_stages_' . $stage . '_blog_options_v5_opts', true) ? true : false;
            if($opts){//if this post has been saved with the new options
                $opts = get_post_meta($go_blog_task_id, 'go_stages_' . $stage . '_blog_options_v5_opts', true);//get the new options
                if(!is_array($opts)){
                    $opts = array();
                }
                if(in_array('private', $opts)){
                    $is_private = true;
                }
            }else{
                $is_private = get_post_meta($go_blog_task_id, 'go_stages_'.$stage.'_blog_options_v5_privat', true);//old style private setting
            }
            $stage = ($stage + 1);

        }

        //Set Privacy
        //don't change the privacy status if post exists
        /*
        if (go_post_exists($blog_post_id) == true) {
            //do something if this blog post already exists

            $is_private = get_post_meta($blog_post_id, 'go_blog_private_post', true) ? get_post_meta($blog_post_id, 'go_blog_private_post', true) : 0;

            if($is_private == true){
                $is_private = 1;//this is a fix for some v4 posts
            }

        } else {*/
            //do something for new blog posts
                //$custom_fields = get_post_meta($post_id);
                //$is_private = (isset($custom_fields[$meta_key][0]) ? $custom_fields[$meta_key][0] : true);
            //$is_private = get_post_meta($go_blog_task_id, $meta_key, true);
       // }


    }

    //$blog_url = (!empty($_POST['blog_url']) ? (string)$_POST['blog_url'] : '');
   // $blog_media = (!empty($_POST['blog_media']) ? (string)$_POST['blog_media'] : '');
    //$blog_video = (!empty($_POST['blog_video']) ? (string)$_POST['blog_video'] : '');

   /* if(!is_array($_POST['required_elements'])){
         $required_elements = (!empty($_POST['required_elements']) ? (string)$_POST['required_elements'] : '');
         //this is a string of the unique Ids and the element contents.
        $required_elements = str_replace("\\", "", $required_elements);
        // $required_elements = json_decode($required_elements);
        $required_elements = json_decode($required_elements, true);
    }
    else{

    }
   */
    $required_elements = $_POST['required_elements'];
    $required_elements = array_map( 'wp_kses_post', $required_elements );
    $test['go_blog_task_stage'] = $status;
    $required_elements['go_blog_bonus_stage'] = $bonus_status;
    $required_elements['go_blog_private_post'] = $is_private;
    $required_elements['go_stage_uniqueid'] = $uniqueid;


   global $sent_meta;
    $sent_meta = $required_elements;

    $my_post = array(
        'ID'        => $blog_post_id,
        'post_type'     => 'go_blogs',
        'post_title'    => $result_title,
        'post_content'  => $result,
        'post_status'   => $post_status,
        'post_author'   => $user_id,
        'post_parent'    => $go_blog_task_id,
        'meta_input'    => $required_elements
    );

    if($is_autosave){
        $autosave_post_data = array(
            'post_ID'        => $blog_post_id,
            'post_title'    => $result_title,
            'post_content'  => $result,
            'meta_input'    => $required_elements,
            'user_ID'       => $user_id,
        );


        $result = wp_create_post_autosave( $autosave_post_data );
    }
    else if(empty($blog_post_id)) {
        // Insert the post into the database
        $new_post_id = wp_insert_post( $my_post );
        if(empty($go_blog_task_id)) {
            wp_save_post_revision($new_post_id);//add to only save this if there is content/required elements
        }
        $result = $new_post_id;
        //create an entry in the actions table that attaches this blog post to this task and stage.  This is how the check for understanding looks up the blog post.
        go_update_actions($user_id, 'blog_post', $go_blog_task_id, $stage, $bonus_status, null, $result, null, null, null, null, null, null, null, null, null, false);

    }else{
        $return = wp_update_post($my_post);//revisions are saved automatically on update post
        //ADD if a revision was saved then meta data needs to be saved to the revision

        //$return =wp_save_post_revision(  $blog_post_id );
        $result = $blog_post_id;
        $key = 'go_post_data_' . $blog_post_id;
        go_delete_transient($key);
    }
    //$result = go_blog_save($blog_post_id, $my_post);
    return $result;
}

/**
 * If meta is changed, save a new post revision
 */
add_filter( 'wp_save_post_revision_post_has_changed', 'go_is_meta_changed', 10, 3 );
function go_is_meta_changed($post_has_changed, $last_revision, $post){

    //check if meta has changed

    global $sent_meta;

    $post_id = $last_revision->ID;
    $custom_fields = get_post_meta($post_id);
    foreach($sent_meta as $key=>$value){
        $prev_value = $custom_fields[$key];
        if($prev_value[0] != $value){
            return true;
        }
    }

    return $post_has_changed;
}

//do_action( "save_post_{$post->post_type}", $post_ID, $post, $update );
add_action('save_post_revision', 'go_save_blog_revision_meta', 10, 2);
function go_save_blog_revision_meta($post_id, $post){
    //if post is revision or autosave
    //then add metadata to post

    $parent_id = $post->post_parent;
    //if ( $parent_id = wp_is_post_revision( $post_id ) ) {

    $type = get_post_type($parent_id);
    if($type != 'go_blogs'){
        return;
    }
    //$parent = get_post($parent_id);

    if($_POST['action'] === "go_blog_autosave"){
        global $sent_meta;
        $blog_meta = $sent_meta;
    }else {

        $blog_meta = get_post_meta($parent_id);
    }
    //Save values from created array into db
    foreach($blog_meta as $meta_key=>$meta_value) {
       // update_post_meta($post_id, $meta_key, $meta_value[0]);
        if(is_array($meta_value)){
            $meta_value = $meta_value[0];
        }
        update_metadata( 'post', $post_id, $meta_key, $meta_value);
    }
   // }
}

add_action( 'wp_restore_post_revision', 'pmr_restore_revision', 10, 2 );
function pmr_restore_revision( $post_id, $revision_id ) {
    $type = get_post_type($post_id);
    if($type != 'go_blogs'){
        return;
    }
    $post_meta      = get_post_meta($post_id);
    $revision_meta      = get_post_meta($revision_id);

    //remove post meta
    foreach($post_meta as $meta_key=>$meta_value) {
        delete_post_meta($post_id, $meta_key);
    }

    //add revision meta to post
    foreach($revision_meta as $meta_key=>$meta_value) {
        update_post_meta($post_id, $meta_key, $meta_value[0]);
    }
}

function go_allow_to_autosave($all_caps, $caps, $args, $hmm){
    $all_caps['edit_post']= true;
    $all_caps['edit_pages']= true;
    return $all_caps;
}

/**
 * Prints content for the clipboard tasks table and user map viewer
 */
function go_blog_user_task($is_ajax = true, $user_id = null, $post_id = null){
    if($is_ajax !== false){
        $is_ajax = true;
    }
    if ($is_ajax) {
        if (!is_user_logged_in()) {
            echo "login";
            die();
        }

        //check_ajax_referer( 'go_blog_user_task' );
        if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_blog_user_task')) {
            echo "refresh";
            die();
        }

        $user_id = intval($_POST['uid']);
        $post_id = intval($_POST['task_id']);
    }

    global $wpdb;



    $go_activity_table_name = "{$wpdb->prefix}go_actions";
    //get all blog posts from a particular task
    //get task history
    //get post #s
    //print posts
    /*
    $actions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT *
			FROM {$go_activity_table_name}
			WHERE action_type = %s and uid = %d and source_id = %d and check_type = %s
			ORDER BY id DESC",
            "task",
            $user_id,
            $post_id,
            "blog"
        )
    );
*/
    $actions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * 
			FROM {$go_activity_table_name} 
			WHERE action_type = %s and uid = %d and source_id = %d  
			ORDER BY id DESC",
            "task",
            $user_id,
            $post_id
        )
    );

    $entry_time = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT TIMESTAMP 
			FROM {$go_activity_table_name} 
			WHERE result = %s and uid = %d and source_id = %d  
			ORDER BY id DESC",
            "entry_reward",
            $user_id,
            $post_id
        )
    );


    $post_title = get_the_title($post_id);
    $task_name = get_option('options_go_tasks_name_singular');
    echo "<div id='go_blog_container' class='go_blogs'><h2>{$task_name}: {$post_title}</h2>";
    $current_stage = null;
    $bonus = false;

    $stage_results = array();
    $this_result = array();
    $first_loop = true;
    foreach ( $actions as $action ) {
        $print_end = true;
        $stage_name ="";
        $action_type = $action->action_type;
        $TIMESTAMP = $action->TIMESTAMP;
        $stage = $action->stage;
        $bonus_status = $action->bonus_status;
        $result = $action->result;
        $quiz_mod = $action->quiz_mod;
        $late_mod = $action->late_mod;
        $timer_mod = $action->timer_mod;
        $health_mod = $action->global_mod;
        $xp = $action->xp;
        $gold = $action->gold;
        $health = $action->health;
        $xp_total = $action->xp_total;
        $gold_total = $action->gold_total;
        $health_total = $action->health_total;
        $check_type = $action->check_type;


        $print = false;
        $bonus_name = "";

        //this is the trigger that this was the last bonus
        if ($bonus == true && $bonus_status == 0){
            $current_stage = null;
            $bonus = false;
        }
        //Only print the content last submitted for each stage.

        //this is the first entry for this task or the first bonus stage (in reverse order)
        if ($current_stage == null){
            if ($bonus_status > 0){ //if this is a bonus stage, set some stuff
                $current_stage = $bonus_status;
                $bonus = true;
                $bonus_name = "Bonus ";
                $stage_name =  get_option('options_go_tasks_bonus_stage');

                $print = true;
                $current_time = $TIMESTAMP;
            }else {//this is not a bonus stage so set these things
                $current_stage = $stage;
                $print = true;
                $current_time = $TIMESTAMP;
                $stage_name = get_option('options_go_tasks_stage_name_singular');
            }
        }else {//this is not the first bonus or regular stage (in reverse order)

            //after the first action
            if ($bonus == true && intval($bonus_status) > 0 && intval($bonus_status) < intval($current_stage)) {
                $current_stage = $bonus_status;
                $stage_name =  get_option('options_go_tasks_bonus_stage');
                $print = true;
            } else if ($bonus == false && intval($stage) < intval($current_stage) && intval($stage) > 0) {
                $current_stage = $stage;
                $stage_name = get_option('options_go_tasks_stage_name_singular');
                $print = true;
            }
        }

        if ($print){
            //this is the time for the previous task
            //$time_on_task = $current_time - $TIMESTAMP;
            if($first_loop === false){
                $time_on_task = go_time_on_task($current_time, $TIMESTAMP);


                $this_result[] = $time_on_task;
                $stage_results[] = $this_result;
            }
            $first_loop = false;
            $this_result = array();//clear it for the next go

            //set the time for the next loop
            $current_time = $TIMESTAMP;


            //THERE ARE SOME LEGAGY check_types THAT CAN BE REMOVED IN THIS NEXT PART
            ob_start();
            $stage_name = ucfirst($stage_name);
            echo  "<span class='go_blog_stage'><h3>". $stage_name . " " . $current_stage .": ";
            if ($check_type == "blog"){
                echo "Blog Post</h3>";
                //go_print_blog_check_result($result, false);
                go_blog_post($result, $post_id, false, true, false, true);
            }else if($check_type == "URL"){
                echo "URL</h3>";
                go_print_URL_check_result($result);
            }else if($check_type == "upload"){
                echo "Upload</h3>";
                go_print_upload_check_result($result);
            }else if($check_type == "password"){
                echo "Password</h3>";
                go_print_password_check_result($result);
            }else if($check_type == "text"){
                echo "Text</h3>";
                go_print_text_check_result($result);
            }else if($check_type == "none"){
                echo "No Check for Understanding</h3>";
            }
            else if($check_type == "quiz"){
                $quiz_result = go_get_quiz_result($user_id, $post_id, $current_stage, 'array' );
                $quiz_mod = (isset($quiz_result[0]['result']) ?  $quiz_result[0]['result'] : null);
                $total_questions = (isset($quiz_result[0]['check_type']) ?  $quiz_result[0]['check_type'] : null);
                //$total_questions = $quiz_result[0]['check_type'];
                $score = ($total_questions - $quiz_mod )."/".$total_questions;
                if (!($quiz_mod > 0)){
                    $score = $total_questions."/".$total_questions;
                }
                echo "Quiz</h3>";
                //echo "<h3>{$score}</h3>";
                //echo "Quiz Score: " .$result;
                go_test_check (null, $current_stage - 1, null, null, $user_id, $post_id, $bonus, $bonus_status, true);


            }
            echo "</span>";

            $this_result[]= ob_get_contents();
            ob_end_clean();
        }

    }

    if (isset($print_end)){
        $time_on_task = go_time_on_task($current_time, $entry_time);
        $this_result[] = $time_on_task;
        $stage_results[] = $this_result;
    }

    $stage_results = array_reverse($stage_results);
    foreach ($stage_results as $result){
        echo $result[0];
        echo $result[1];
    }
    echo "</div>";

    if($is_ajax){
        die();
    }
}

/*
function go_show_private(){
    //check_ajax_referer( 'go_blog_opener' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_show_private' ) ) {
        echo "refresh";
        die( );
    }
    $user_id = (isset($_POST['userid']) ?  $_POST['userid'] : 0);
    $checked = (isset($_POST['checked']) ?  $_POST['checked'] : 0);
    $current_uid = get_current_user_id();
    if($checked === 'checked'){
        $checked = 1;
    }else{
        $checked = 0;
    }

    update_user_option( $current_uid, 'go_show_private', $checked );
    ob_start();
    go_get_blog_posts($user_id);
    $buffer = ob_get_contents();
    ob_end_clean();
    echo $buffer;
    die();

}*/