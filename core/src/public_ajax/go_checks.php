<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 4/9/18
 * Time: 10:31 PM
 */


/**
 * Prints Checks for understanding for the current stage
 * @param $custom_fields
 * @param $i
 * @param $status
 * @param $user_id
 * @param $post_id
 * @param $bonus
 * @param $bonus_status
 * @param $repeat_max
 * @param $all_content
 */
function go_checks_for_understanding ($custom_fields, $i, $status, $user_id, $post_id, $bonus, $bonus_status, $repeat_max, $all_content){
    global $wpdb;
    $go_actions_table_name = "{$wpdb->prefix}go_actions";
    $stage_count = (isset($custom_fields['go_stages'][0]) ?  $custom_fields['go_stages'][0] : null); //total # of stages


    if ($bonus){
        $check_type = (isset($custom_fields['go_bonus_stage_check_v5'][0]) ?  $custom_fields['go_bonus_stage_check_v5'][0] : null);

    }
    else{
        $check_type = 'go_stages_' . $i . '_check_v5'; //which type of check to print
        //$check_type = $custom_fields[$check_type][0];
        $check_type = (isset($custom_fields[$check_type][0]) ?  $custom_fields[$check_type][0] : null);

    }

    if (isset($custom_fields['go_stages_' . $i . '_instructions'][0]) && (!$bonus)) {
        $instructions = $custom_fields['go_stages_' . $i . '_instructions'][0];
        $instructions = apply_filters( 'go_awesome_text', $instructions );
        $instructions = "<div class='go_call_to_action'>" . $instructions . " </div>";
    } else if (isset($custom_fields['go_bonus_stage_instructions'][0]) && ($bonus)) {
        $instructions = $custom_fields['go_bonus_stage_instructions'][0];
        $instructions = apply_filters( 'go_awesome_text', $instructions );
        $instructions = "<div class='go_call_to_action'>" . $instructions . " </div>";
    }else {
        $instructions = null;
    }

    if ($bonus){
        if ($i == $repeat_max - 1 && $bonus_status == $repeat_max){
            $bonus_is_complete = true;
        }else{
            $bonus_is_complete = false;
        }
    }else{
        $bonus_is_complete = false;
    }

    if ($bonus){
        $status_active_check = $bonus_status;
    }else{
        $status_active_check = $status;
    }

    if($i == $status_active_check && $bonus_is_complete == false ){
        $class = 'active';
    }else{$class = 'null';}
    echo "<div class='go_checks_and_buttons {$class}' style='display:block;'>";
    echo $instructions;

    $blog_post_id = null;
    if ($check_type == 'blog') {
        $blog_post_id = go_blog_check($custom_fields, $i, $status, $go_actions_table_name, $user_id, $post_id, $bonus, $bonus_status, $all_content, $repeat_max, $check_type, $stage_count);
    //} else if ($check_type == 'URL') {
        //go_url_check($custom_fields, $i, $status, $go_actions_table_name, $user_id, $post_id, $bonus, $bonus_status);
    //} else if ($check_type == 'upload') {
            //go_upload_check($custom_fields, $i, $status, $go_actions_table_name, $user_id, $post_id, $bonus, $bonus_status);
    } else if ($check_type == 'password') {
        go_password_check($custom_fields, $i, $status, $go_actions_table_name, $user_id, $post_id, $bonus, $bonus_status);
    } else if ($check_type == 'quiz') {
        go_test_check($custom_fields, $i, $status, $go_actions_table_name, $user_id, $post_id, $bonus, $bonus_status, false);
    } else if ($check_type == 'none' || $check_type == null) {
        go_no_check($i, $status, $custom_fields, $bonus, $bonus_status);
    }

    if ($check_type != 'blog') {//they add buttons with some extra stuff
        //Buttons
        go_buttons($user_id, $custom_fields, $i, $stage_count, $status, $check_type, $bonus, $bonus_status, $repeat_max, false, $blog_post_id);
    }


    echo "</div>";
}

/**
 * Prints the buttons on checks for understanding/task pages
 * @param $user_id
 * @param $custom_fields
 * @param $i
 * @param $stage_count
 * @param $status
 * @param $check_type
 * @param $bonus
 * @param $bonus_status
 * @param $repeat_max
 * @param bool $outro
 * @param $blog_post_id
 */
function go_buttons($user_id, $custom_fields, $i, $stage_count, $status, $check_type, $bonus, $bonus_status, $repeat_max, $outro = false, $blog_post_id = null){

    $is_admin = go_user_is_admin($user_id);
    $admin_view = null;
    if ($is_admin) {
        $admin_view = get_user_option('go_admin_view', $user_id );
    }
    $undo = 'undo';
    $abandon = 'abandon';
    $complete = 'complete';
    $continue = 'continue';
    $stage = $i;

    if ($bonus != true) {//not a bonus stage
        //$url_toggle = (isset($custom_fields['go_stages_' . $i . '_blog_options_url_toggle'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_url_toggle'][0] : null);
        //$file_toggle = (isset($custom_fields['go_stages_' . $i . '_blog_options_attach_file_toggle'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_attach_file_toggle'][0] : null);
        //$video_toggle = (isset($custom_fields['go_stages_' . $i . '_blog_options_video'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_video'][0] : null);
        $text_toggle = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_toggle'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_toggle'][0] : null);
        //$restrict_mime_types = (isset($custom_fields['go_stages_' . $i . '_blog_options_attach_file_restrict_file_types'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_attach_file_restrict_file_types'][0] : null);
        $min_words = (isset($custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_minimum_length'][0]) ? $custom_fields['go_stages_' . $i . '_blog_options_v5_blog_text_minimum_length'][0] : null);
        //$required_string = (isset($custom_fields['go_stages_'.$i.'_blog_options_url_url_validation'][0]) ?  $custom_fields['go_stages_'.$stage.'_blog_options_url_url_validation'][0] : null);

    }
    else{//this is a bonus stage blog
        //$url_toggle = (isset($custom_fields['go_bonus_stage_blog_options_bonus_url_toggle'][0]) ? $custom_fields['go_bonus_stage_blog_options_bonus_url_toggle'][0] : null);
        //$file_toggle = (isset($custom_fields['go_bonus_stage_blog_options_bonus_attach_file_toggle'][0]) ? $custom_fields['go_bonus_stage_blog_options_bonus_attach_file_toggle'][0] : null);
        //$video_toggle = (isset($custom_fields['go_bonus_stage_blog_options_bonus_video'][0]) ? $custom_fields['go_bonus_stage_blog_options_bonus_video'][0] : null);
        $text_toggle = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_text_toggle'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_text_toggle'][0] : null);
        //$restrict_mime_types = (isset($custom_fields['go_bonus_stage_blog_options_bonus_attach_file_restrict_file_types'][0]) ? $custom_fields['go_bonus_stage_blog_options_bonus_attach_file_restrict_file_types'][0] : null);
        $min_words = (isset($custom_fields['go_bonus_stage_blog_options_v5_blog_text_minimum_length'][0]) ? $custom_fields['go_bonus_stage_blog_options_v5_blog_text_minimum_length'][0] : null);
        //$required_string = (isset($custom_fields['go_bonus_stage_blog_options_url_url_validation'][0]) ?  $custom_fields['go_stages_'.$stage.'_blog_options_url_url_validation'][0] : null);

    }

    $bonus_is_complete = false;
    if ($bonus){
        $stage_count = $repeat_max;
        $undo = 'undo_bonus';
        $abandon = 'abandon_bonus';
        $complete = 'complete_bonus';
        $continue = 'continue_bonus';
        if ($i == $repeat_max - 1 && $bonus_status == $repeat_max){
            $bonus_is_complete = true;
            $undo = 'undo_last_bonus';
        }
        $status = $bonus_status;

    }

    if ($admin_view === 'all') {
        $onclick = '';
    }

    //Buttons
    if ($outro || $check_type == 'show_bonus' || $bonus_is_complete) {
        if (! $bonus_is_complete ){
            $undo = 'undo_last';
        }
        echo "<div id='go_buttons'>";
        echo "<div id='go_back_button' class='go_buttons' undo='true' button_type='{$undo}' status='{$status}' check_type='{$check_type}' blog_post_id='{$blog_post_id}'>⬆ Undo</div>";
        if ($custom_fields['bonus_switch'][0] && ! $bonus_is_complete) {
            //echo "There is a bonus stage.";
            echo "<button id='go_button' class='show_bonus go_buttons' status='{$status}' check_type='{$check_type}' button_type='show_bonus'  admin_lock='true' >Show Bonus Challenge</button> ";
        }
        echo "</div>";
    }

    else if ( $i == $status || $status == 'unlock' || $admin_view == 'all' || $admin_view == 'guest' ) {

        if ($bonus) {
            global $go_print_next;//the bonus stage to be printed, sometimes they print out of order if posts were trashed
            $go_print_next = (isset($go_print_next) ? $go_print_next : $status);
        }
        else{
            $go_print_next = null;
        }

        echo "<div id='go_buttons'>";
        if ($i == 0) {
            echo "<div id='go_back_button'  class='go_buttons' undo='true' button_type='{$abandon}' status='{$status}' check_type='{$check_type}' blog_post_id='{$blog_post_id}'>Abandon</div>";
        } else {
            echo "<div id='go_back_button' class='go_buttons' undo='true' button_type='{$undo}' next_bonus='{$go_print_next}' status='{$status}' next_bonus='{$go_print_next}' check_type='{$check_type}' blog_post_id='{$blog_post_id}'>⬆ Undo</div>";
        }
        if (($i + 1) == $stage_count) {
            echo "<button id='go_button' class='progress go_buttons' status='{$status}' check_type='{$check_type}' button_type='{$complete}' next_bonus='{$go_print_next}' admin_lock='true' min_words='{$min_words}' blog_suffix ='' text_toggle='{$text_toggle}' blog_post_id='{$blog_post_id}' >Complete</button> ";
        } else {

            echo "<button id='go_button' class='progress go_buttons' status='{$status}' check_type='{$check_type}' button_type='{$continue}' next_bonus='{$go_print_next}' admin_lock='true' min_words='{$min_words}' blog_suffix ='' text_toggle='{$text_toggle}' blog_post_id='{$blog_post_id}'>Continue</button>";

        }
        echo "</div>";
        echo "<p id='go_stage_error_msg' style='display: none; color: red;'></p>";
    }
}

/**
 * @param $i
 * @param $status
 * @param $custom_fields
 * @param $bonus
 * @param $bonus_status
 */
function go_no_check ($i, $status, $custom_fields, $bonus, $bonus_status){
    //for bonus stages
    if ($bonus){
        $status = $bonus_status;
    }
    if ($i !=$status) {
        echo "Stage complete!";
    }
}

/**
 * @param $custom_fields
 * @param $i
 * @param $status
 * @param $go_actions_table_name
 * @param $user_id
 * @param $post_id
 * @param $bonus
 * @param $bonus_status
 */
function go_password_check ($custom_fields, $i, $status, $go_actions_table_name, $user_id, $post_id, $bonus, $bonus_status){
    global $wpdb;

    //for bonus stages
    $stage = 'stage';
    if ($bonus){
        $status = $bonus_status;
        $stage = 'bonus_status';
    }
    //end for bonus stages

    if ($i == $status) {
        echo "<input id='go_result' class='clickable' type='password' placeholder='Enter Password'/>";
    }
    else {
        $i++;
        $password_type = (string) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT result 
				FROM {$go_actions_table_name} 
				WHERE uid = %d AND source_id = %d AND {$stage} = %d
				ORDER BY id DESC LIMIT 1",
                $user_id,
                $post_id,
                $i
            )
        );
        go_print_password_check_result($password_type);

    }
}

/**
 * @param $password_type
 */
function go_print_password_check_result($password_type){
    echo "The " . $password_type . " was entered correctly.";
}


/**
 * @param $custom_fields
 * @param $i    //the stage # being printed
 * @param $status //the current stage of this user
 * @param $go_actions_table_name
 * @param $user_id
 * @param $post_id
 * @param $bonus bool (are we printing a bonus stage
 * @param $bonus_status
 * @param $all_content //if true, print a form that can't be edited if in the visitor/all content for admin view
 * @param $repeat_max
 * @param $check_type
 * @param $stage_count
 * @return |null
 */
function go_blog_check ($custom_fields, $i, $status, $go_actions_table_name, $user_id, $post_id, $bonus, $bonus_status, $all_content, $repeat_max, $check_type, $stage_count){

    if (!$bonus){//if this is not a bonus
        //check the task meta for a uniqueid
        $uniqueid = (isset($custom_fields['go_stages_' . $i . '_uniqueid'][0]) ?  $custom_fields['go_stages_' . $i . '_uniqueid'][0] : false);

        //if uniqueid found then get the blog_post_id with the meta data
        if ($uniqueid){
            $blog_post_id = go_get_blog_post_id($post_id,$user_id, 'go_stage_uniqueid', $uniqueid, null  );
        }
        if(empty($blog_post_id)) {
            //if no uniqueid was set or the blog post couldn't be found
            //search using the v4 methods where that was saved with the stage# in the meta
            $blog_post_id = go_get_blog_post_id($post_id, $user_id, 'go_blog_task_stage', null, $i  );
        }

        if ($i != $status && !$all_content){//if this is a complete stage, print the result
            go_blog_post($blog_post_id, $post_id, true, true, false, true, $i, null);
        }
        else{//this is the current stage and print the form (or all content is on)
            go_blog_form($blog_post_id, '', $post_id, $i, $bonus_status, true, $all_content);

            go_buttons($user_id, $custom_fields, $i, $stage_count, $status, $check_type, $bonus, $bonus_status, $repeat_max, false, $blog_post_id);

            return $blog_post_id;
        }
    }
    else{//this is a bonus
        global $go_bonus_count;//the number of stages that have been printed
        global $go_print_next;//the bonus stage to be printed, they print by last modified, published posts first.
        $go_print_next = (isset($go_print_next) ?  $go_print_next : 0);
        $go_bonus_count++;

        //LOGIC
        //the first time bonus_status == x and bonus_count == 1
        //if x >= bonus count, print the first bonus that isn't trash
        //else then print the form
            //print the first that isn't trash
            //if that doesn't exist, print first that is trash
            //else print blank form

        //the $go_print_next variable is set as the status in the buttons function

        if ($bonus_status >= $go_bonus_count && !$all_content) {//if this is a complete stage
            $blog_post_id = go_get_bonus_blog_post_id($post_id,$user_id, $go_print_next, false );
            go_blog_post($blog_post_id, $post_id, true, true, false, true, null, $go_bonus_count);
            if($bonus_status == $go_bonus_count){
                go_buttons($user_id, $custom_fields, $i, $stage_count, $status, $check_type, $bonus, $bonus_status, $repeat_max, false, $blog_post_id);

            }
            $go_print_next++;
            return $blog_post_id;
        }
        else{//this is the current bonus stage and print the form
            //get the next,
            $blog_post_id = (isset($_POST['blog_post)id']) ? $_POST['blog_post)id']  : false);
            if(!$blog_post_id) {
                if (!$all_content) {

                    //check for published posts first
                    $blog_post_id = go_get_bonus_blog_post_id($post_id,$user_id, $go_print_next, false );

                    if ($blog_post_id === null){
                        //then get the trash
                        $blog_post_id = go_get_bonus_blog_post_id($post_id,$user_id, 0, true );


                    } // end if
                }
            }
            $blog_post_id = (isset($blog_post_id) ?  $blog_post_id : null);
            if($blog_post_id) {
                wp_trash_post(intval($blog_post_id));
            }
            go_blog_form($blog_post_id, '', $post_id, $i, $bonus, true, $all_content);

            go_buttons($user_id, $custom_fields, $i, $stage_count, $status, $check_type, $bonus, $bonus_status, $repeat_max, false, $blog_post_id);
            //if($blog_post_id){do_action('go_blog_template_after_post', $blog_post_id, false);}

            return $blog_post_id;

        }
    }
}

/**
 * @param $post_id
 * @param $user_id
 * @param $key
 * @return |null
 */
function go_get_blog_post_id($post_id, $user_id, $key, $uniqueid, $stage_num ){
    //v4.6 method
    if (isset($uniqueid)) {
        $args = array(
            'post_status' => array('any', 'trash'),//'draft', 'unread', 'read', 'publish', 'reset', 'revise', 'trash
            'post_type' => 'go_blogs',
            'post_parent' => intval($post_id),
            'author' => $user_id,
            'posts_per_page' => 1,
            'meta_key' => $key,
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => $key,
                    'value' => $uniqueid,
                    'compare' => '=',
                )
            )
        );

        $my_query = new WP_Query($args);

        //get the blog post id from the query loop(only 1 post, so it doesn't actually loop
        if ($my_query->have_posts()) {
            while ($my_query->have_posts()) {
                // Do your work...
                $my_query->the_post();
                $blog_post_id = get_the_ID();
            } // end while
        } // end if
        wp_reset_postdata();
    }

    else if(isset($stage_num)){
        $stage_num++;
        global $wpdb;
        $go_actions_table_name = "{$wpdb->prefix}go_actions";

        $blog_post_id = (string) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT result 
				FROM {$go_actions_table_name} 
				WHERE uid = %d AND source_id = %d AND stage = %d AND action_type = %s
				ORDER BY id DESC LIMIT 1",
                $user_id,
                $post_id,
                $stage_num,
                'blog_post'
            )
        );
    }
    $blog_post_id = (!empty($blog_post_id) ?  $blog_post_id : null);

    return $blog_post_id;
}

/**
 * @param $post_id
 * @param $user_id
 * @param $go_print_next
 * @param bool $get_the_trash
 */
function go_get_bonus_blog_post_id($post_id, $user_id, $go_print_next, $get_the_trash = false ){

    if ($get_the_trash){
        $statuses = array( 'trash' );
    }
    else{
        $statuses = array( 'draft', 'unread', 'read', 'publish', 'reset', 'revise' );
    }
    $args = array(
        'post_status' => $statuses,
        'post_type' => 'go_blogs',
        'post_parent'=> intval($post_id),
        'author'    => $user_id,
        'posts_per_page' => 1,
        'offset'    => $go_print_next,
        'meta_query' => array(
            array(
                'key' => 'go_blog_bonus_stage',
                'value' => 1,
                'compare' => '>=',
            )
        ),
        'orderby' => 'modified',
        'order' => 'ASC'
    );
    $my_query = new WP_Query($args);

    if( $my_query->have_posts() ) {
        while( $my_query->have_posts() ) {
            // Do your work...
            $my_query->the_post();
            $blog_post_id = get_the_ID() ;
        } // end while
    } // end if
    wp_reset_postdata();
    $blog_post_id = (isset($blog_post_id) ?  $blog_post_id : null);
    return $blog_post_id;
}

/**
 * @param $custom_fields
 * @param $i
 * @param $status
 * @param $go_actions_table_name
 * @param $user_id
 * @param $post_id
 * @param $bonus
 * @param $bonus_status
 */
function go_url_check ($i, $go_actions_table_name, $user_id, $post_id, $bonus){
    global $wpdb;


    $stage = 'stage';
    if ($bonus){
        $stage = 'bonus_status';
    }

    $i++;
    $url = (string) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT result 
            FROM {$go_actions_table_name} 
            WHERE uid = %d AND source_id = %d AND {$stage}  = %d 
            ORDER BY id DESC LIMIT 1",
            $user_id,
            $post_id,
            $i
        )
    );
    go_print_URL_check_result($url);
}
/*
function go_url_check ($custom_fields, $i, $status, $go_actions_table_name, $user_id, $post_id, $bonus, $bonus_status){
    global $wpdb;

    //for bonus stages
    $stage = 'stage';
    if ($bonus){
        $status = $bonus_status;
        $stage = 'bonus_status';
    }
    //end for bonus stages

    if ($i == $status) {//the form
        $i++;
        $url = (string)$wpdb->get_var($wpdb->prepare("SELECT result 
				FROM {$go_actions_table_name} 
				WHERE uid = %d AND source_id = %d AND {$stage}  = %d AND action_type = %s
				ORDER BY id DESC LIMIT 1", $user_id, $post_id, $i, 'task'));

        echo "<div class='go_url_div'>";
        echo "<input id='go_result' class='clickable' type='url' placeholder='Enter URL' value='{$url}'>";
        echo "</div>";
    }
    else {//the result
        $i++;
        $url = (string) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT result 
				FROM {$go_actions_table_name} 
				WHERE uid = %d AND source_id = %d AND {$stage}  = %d 
				ORDER BY id DESC LIMIT 1",
                $user_id,
                $post_id,
                $i
            )
        );
        //add lightbox to this--NOPE too many protected URLs
        go_print_URL_check_result($url);
        //Too many security errors with Lightbox
        //echo "<br><a href='" . $url . "' data-featherlight='iframe'>Open in a lightbox.</a>";
    }
}
*/

/**
 * @param string $placeholder
 * @param string $id
 * @param null $url
 * @param string $data_type
 * @param null $required_string
 * @param null $uniqueID
 */
function go_url_check_blog ($placeholder = 'Enter URL', $id = 'go_result', $url = null, $data_type = 'url', $required_string = null, $uniqueID = null){
    global $wpdb;

        echo "<div class='go_url_div'>";
        echo "<input id='{$id}' class='clickable go_blog_element_input' type='url' data-type='{$data_type}' data-required='{$required_string}' data-uniqueID='{$uniqueID}' placeholder='{$placeholder}' value='{$url}' style='width: 90%;'>";
        echo "</div>";
}

/**
 * @param $url
 */
function go_print_URL_check_result($url){
    echo "<div class='go_required_blog_content width100'>";
    echo "<div>URL Submitted : <a href='" . $url . "' target='blank'>" . $url . "</a></div>";
    echo "</div>";
}

/**
 * @param $custom_fields
 * @param $i
 * @param $status
 * @param $go_actions_table_name
 * @param $user_id
 * @param $post_id
 * @param $bonus
 * @param $bonus_status
 */
function go_upload_check ($i, $go_actions_table_name, $user_id, $post_id, $bonus) {
    global $wpdb;

    $stage = 'stage';
    if ($bonus){
        $stage = 'bonus_status';
    }

    $i++;
    $media_id = (int)$wpdb->get_var($wpdb->prepare("SELECT result 
            FROM {$go_actions_table_name} 
            WHERE uid = %d AND source_id = %d AND {$stage}  = %d
            ORDER BY id DESC LIMIT 1", $user_id, $post_id, $i));

    go_print_upload_check_result($media_id);

}

/**
 * @param null $media_id
 * @param $div_id
 * @param $mime_types
 * @param null $uniqueID
 */
function go_upload_check_blog ($media_id = null, $div_id, $mime_types, $uniqueID = null) {

    $attachment_type = get_post_type($media_id);
    if (empty($media_id) || ($attachment_type !== 'attachment')) {
        echo do_shortcode('[frontend-button div_id="'.$div_id.'" mime_types="'.$mime_types.'" uniqueid="'.$uniqueID.'"]');
    }else{
        echo do_shortcode( '[frontend_submitted_media div_id="'.$div_id.'" id="'.$media_id.'" mime_types="'.$mime_types.'" uniqueid="'.$uniqueID.'" class="go_blog_element_input" ]' );
    }
}

/**
 * @param $media_id
 */
function go_print_upload_check_result($media_id){
    $type = get_post_mime_type($media_id);

    //return $icon;
    switch ($type) {
        case 'image/jpeg':
        case 'image/png':
        case 'image/gif':
            $type_image = true;
            break;
        default:
            $type_image = false;
    }
    echo "<div class='go_required_blog_content'>";
    if ($type_image == true){
        $med = wp_get_attachment_image_src( $media_id, 'medium' );
        $full = wp_get_attachment_image_src( $media_id, 'full' );
        //echo "<img src='" . $thumb[0] . "' >" ;
        echo '<a href="#" data-featherlight="' . $full[0] . '"><img src="' . $med[0] . '"></a>';

    }
    else{
        // $img = wp_mime_type_icon($type);
        //echo "<img src='" . $img . "' >";
        $url = wp_get_attachment_url( $media_id );
        $thumb = wp_get_attachment_image_src( $media_id, 'thumbnail',true );
        echo "<a href='{$url}'>";
        echo "<img src='" . $thumb[0] . "' >" ;
        echo "<div>" . get_the_title($media_id) . "</div>" ;
        echo "</a>";
    }
    echo "</div>";
}

/**
 * @param $custom_fields
 * @param $i
 * @param $status
 * @param $go_actions_table_name
 * @param $user_id
 * @param $post_id
 * @param $bonus
 * @param $bonus_status
 * @param $show_first
 */
function go_test_check ($custom_fields, $i, $status, $go_actions_table_name, $user_id, $post_id, $bonus, $bonus_status, $show_first){
    global $wpdb;
    $go_actions_table_name = (isset($go_actions_table_name) ?  $go_actions_table_name : $wpdb->prefix ."go_actions");
    if ($i === $status) {

        $test_array = $custom_fields['go_stages_' . $i . '_quiz'][0];

        $atts['quiz'] = $test_array;
        $atts['stage'] = $i + 1;

        go_test_shortcode( $atts );
            //do_shortcode("[go_test $atts ]");

    }
    else {
        //for bonus stages
        $stage = 'stage';
        if ($bonus){
            $status = $bonus_status;
            $stage = 'bonus_status';
        }
        //end for bonus stages
        //echo "Questions answered correctly.";
        $i++;



        //LOGIC:
        //get the first attempt
        //get the score
        //if on task and not 100%
        //then get the most recent attempt

        //build the html
        //if on task
            //if 100%
                //echo the score
                //echo first
            //else
                //echo link to first
                //echo recent
        //else if on clipboard
            //echo message with score

                //echo first

        //get the first attempt
        $first = (string) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT result 
				FROM {$go_actions_table_name} 
				WHERE uid = %d AND source_id = %d AND {$stage}  = %d AND action_type = %s
				ORDER BY id ASC LIMIT 1",
                $user_id,
                $post_id,
                $i,
                'quiz_result'
            )
        );
        $first = stripslashes($first);

        //get the score
        //$quiz_mod = go_get_quiz_mod($user_id, $post_id, $i,  );
        $quiz_result = go_get_quiz_result($user_id, $post_id, $i, 'array' );
        $quiz_mod = (isset($quiz_result[0]['result']) ?  $quiz_result[0]['result'] : null);
        $total_questions = (isset($quiz_result[0]['check_type']) ?  $quiz_result[0]['check_type'] : null);
        //$total_questions = $quiz_result[0]['check_type'];
        $score = ($total_questions - $quiz_mod )."/".$total_questions;

        //if on task and not 100%
        //then get the most recent attempt
        if (!$show_first) {//on a task
            if ($quiz_mod == 0) {
                echo "<div>You got {$score} correct. 100% Good job!</div>";
            }
            else if ($quiz_mod > 0) {//not 100%
                echo "<div>On your <a href='#' data-featherlight='#go_first_quiz_attempt_{$i} '>first attempt</a> you got {$score} correct.</div>";


            }

            //Get the most recent attempt
            $recent = (string)$wpdb->get_var(
                $wpdb->prepare(
                    "SELECT result 
				FROM {$go_actions_table_name} 
				WHERE uid = %d AND source_id = %d AND {$stage}  = %d AND action_type = %s
				ORDER BY id DESC LIMIT 1",
                    $user_id,
                    $post_id,
                    $i,
                    'quiz_result'
                )
            );
            $recent = stripslashes($recent);

            //print the hidden div for the lightbox
            echo "<div id='go_first_quiz_attempt_{$i}' class='go_first_quiz_attempt' >{$first}</div>";
            echo "<div>{$recent}</div>";

        }
        else {//on the clipboard
            echo "<h3>{$score}</h3>";

            echo "<div>{$first}</div>";


        }
        /*
        if ($quiz_mod > 0) {


             //$html = '<ul><li><div>Do it 50%<span class="go_correct_answer_marker">correct</span></div></li><li><input type="radio" value="Yes" checked="checked"> Yes</li><li><input type="radio" name="go_test_answer_0" value="no"> no</li></ul><ul><li><div >Nope<span class="go_wrong_answer_marker" style="">wrong</span></div></li><li class="go_test go_test_element"><input type="radio"  value="yup" checked="checked"> yup</li><li class="go_test go_test_element"><input type="radio" value="nope"> nope</li></ul>';
            $show = '';
            if ($show_first) {
                $show = 'show';
            }
            //echo "<br>On your first attempt you missed {$quiz_mod}.";
            $first_link = "<div>On your <a href='#' data-featherlight='#go_first_quiz_attempt_{$i} '>first attempt</a> you got {$score} correct.</div>";
            $first = "<div id='go_first_quiz_attempt_{$i}' class='go_first_quiz_attempt{$show}' >{$html}</div>";

        }else{
            $first_link = "<div>You got 100% correct!</div>";

        }



        $html = (string) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT result 
				FROM {$go_actions_table_name} 
				WHERE uid = %d AND source_id = %d AND {$stage}  = %d AND action_type = %s
				ORDER BY id DESC LIMIT 1",
                $user_id,
                $post_id,
                $i,
                'quiz_result'
            )
        );

        if ($show_first){//only show the first attempt if on the clipboard

            echo $first;
            return;
        }else{//this is a view for the user regular check for understanding
            echo $first_link;
            echo $first;
            echo "<div>".stripslashes($html)."</div>";
        }


        */

    }


}

//Quiz function
/**
 * Retrieves and formulates test meta data from a specific task and stage.
 *
 */
function go_task_get_test_meta($custom_fields, $stage ) {

    $test_array = $custom_fields['go_stages_' . $stage . '_quiz'][0];
    $test_array = unserialize($test_array);
    if ( ! empty( $test_array ) ) {
        $test_num = $test_array[3];
        $test_all_questions = array();
        foreach ( $test_array[0] as $question ) {
            $esc_question = htmlspecialchars( $question, ENT_QUOTES );
            if ( preg_match( "/[\\\[\]]/", $question ) ) {
                $str = preg_replace( array( "/\[/", "/\]/", "/\\\/" ), array( '&#91;', '&#93;', '\\\\\\\\\\\\' ), $esc_question );
                $test_all_questions[] = $str;
            } else {
                $test_all_questions[] = $esc_question;
            }
        }
        $test_all_types = $test_array[2];
        $test_all_inputs = $test_array[1];
        $test_all_input_num = $test_array[4];
        $test_all_answers = array();
        $test_all_keys = array();
        for ( $i = 0; $i < count( $test_all_inputs ); $i++ ) {
            if ( ! empty( $test_all_inputs[ $i ][0] ) ) {
                $answer_temp = implode( "###", $test_all_inputs[ $i ][0] );
                $esc_answer = htmlspecialchars( $answer_temp, ENT_QUOTES );
                if ( preg_match( "/[\\\[\]]/", $answer_temp ) ) {
                    $str = preg_replace( array( "/\[/", "/\]/", "/\\\/" ), array( '&#91;', '&#93;', '\\\\\\\\\\\\' ), $esc_answer );
                    $test_all_answers[] = $str;
                } else {
                    $test_all_answers[] = $esc_answer;
                }
            }
            if ( ! empty( $test_all_inputs[ $i ][1] ) ) {
                $key_temp = implode( "###", $test_all_inputs[ $i ][1] );
                $esc_key = htmlspecialchars( $key_temp, ENT_QUOTES );
                if (preg_match( "/[\\\[\]]/", $key_temp) ) {
                    $str = preg_replace( array( "/\[/", "/\]/", "/\\\/" ), array( '&#91;', '&#93;', '\\\\\\\\\\\\' ), $esc_key );
                    $test_all_keys[] = $str;
                } else {
                    $test_all_keys[] = $esc_key;
                }
            }
        }

        return array( $test_num, array( $test_all_questions, $test_all_types, $test_all_answers, $test_all_keys ) );
    } else {
        return null;
    }
}


