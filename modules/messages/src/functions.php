<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/21/18
 * Time: 6:04 PM
 */

/*
global $go_debug;

if(!$go_debug) {
    add_filter('heartbeat_received', 'go_check_messages', 10, 2);
}*/

add_action( 'wp_footer', 'go_check_messages', 2 );
add_action('go_after_stage_change', 'go_check_messages');
//function go_check_messages($response = null , $data = null){
function go_check_messages(){
    global $wpdb;

    /*
    if ( empty( $data['go_heartbeat'] ) ) {
        $heartbeat = false;
    }else{
        $heartbeat = true;
    }*/

    ob_start();
    //on each page load, check if user has new messages
    $user_id =  get_current_user_id();
    $is_logged_in = is_user_member_of_blog() || go_user_is_admin();
    $is_new_messages = get_user_option('go_new_messages');

    $up = false;
    $down = false;

    if ($is_logged_in && $is_new_messages ){
        //get unread messages
        $go_actions_table_name = "{$wpdb->prefix}go_actions";
        $actions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *
			FROM {$go_actions_table_name}
			WHERE uid = %d and (action_type = %s or action_type = %s or action_type = %s or action_type = %s or action_type = %s OR action_type = %s OR action_type = %s)  and stage = %d
			ORDER BY id DESC",
                $user_id,
                'message',
                'reset',
                'admin_notification',
                'feedback',
                'feedback_percent',
                'feedback_loot',
                'attendance',
                1
            )
        );
        //turn them into noty
        //set them as read
        foreach ($actions as $action) {
            $type = $action->action_type;
            //$post_id = $action->source_id;
            $result = $action->result;
            $result = unserialize($result);
            $title = $result[0];
            $title = stripslashes($title);
            $message = $result[1];
            $message = stripslashes($message);
            $xp = $action->xp;
            $gold = $action->gold;
            $health = $action->health;

            $badges = $action->badges;
            $badges_ids = array();
            $badges = unserialize($badges);
            if(is_array($badges)){
                $badges_ids =  $badges;
            }else if(is_numeric($badges)){
                $badges_ids[] = $badges;
            }else{
                $badges_ids = array();
            }

            $groups = $action->groups;
            $groups = unserialize($groups);
            $group_ids = array();
            if(is_array($groups)){
            $group_ids =  $groups;
            }
            else if(is_numeric($groups)){
                $group_ids[] = $groups;
            }else{
                $group_ids = array();
            }

            if ($type == 'admin_notification'){
                go_noty_message_modal('warning', '', $title);
            }
            else if ( $type == 'reset' && $type = 'reset_stage') {
                go_noty_message_modal('warning', $title, $message);
                $sound = go_down_sound();
                echo $sound;
            }
            else{
                $xp_penalty = null;
                $xp_reward = null;
                $xp_loot = null;
                if($xp != 0){
                    $xp_loot = go_display_longhand_currency('xp', $xp);
                }
                if ($xp > 0) {
                    $xp_reward = $xp_loot.'<br>';
                    $up = true;
                } else if ($xp < 0) {
                    $xp_penalty = $xp_loot.'<br>';
                    $down = true;
                }

                $gold_penalty = null;
                $gold_reward = null;
                $gold_loot = null;
                if($gold != 0){
                    $gold_loot = go_display_longhand_currency('gold', $gold, false, false, false);
                }
                if ($gold > 0) {
                    $gold_reward = $gold_loot.'<br>';
                    $up = true;
                }
                else if ($gold < 0) {
                    $gold_penalty = $gold_loot.'<br>';
                    $down = true;
                }


                $health_penalty = null;
                $health_reward = null;
                $health_loot = null;
                if ($health != 0){
                    $health_loot = go_display_longhand_currency('health', $health);
                }
                if ($health > 0) {
                    $health_reward = $health_loot.'<br>';
                    $up = true;
                }
                else if ($health < 0) {
                    $health_penalty = $health_loot.'<br>';
                    $down = true;
                }





                if (!empty($xp_reward) || !empty($gold_reward) || !empty($health_reward) ) {
                    //$loot = "<div class='go_messages_loot go_messages_rewards'><h3>Reward:</h3>$xp_reward $gold_reward $health_reward $badge_award $group_award</div>";
                    $loot = "<div class='go_messages_loot go_messages_rewards'>$xp_reward $gold_reward $health_reward </div>";
                    $type = 'success';
                } else if (!empty($xp_penalty) || !empty($gold_penalty) || !empty($health_penalty) ){
                    //if (empty($post_id)){
                    //$loot = "<div class='go_messages_loot go_messages_penalties'><h3>Consequence:</h3>$xp_penalty $gold_penalty $health_penalty $badge_penalty $group_penalty</div>";
                    $loot = "<div class='go_messages_loot go_messages_penalties'>$xp_penalty $gold_penalty $health_penalty </div>";
                    //}
                    //else{
                    //    $penalty = "<h4>Additional Penalty:</h4>{$xp_penalty}{$gold_penalty}{$health_penalty}{$badge_penalty}{$group_penalty}";
                    // }
                    $type = 'error';
                }else{
                    $loot='';
                    $type='info';
                }

                if(!empty($message) || !empty($loot)) {

                    $message = "<div>$message</div><div>$loot</div>";

                    go_noty_message_modal($type, $title, $message);
                }

                $badges_toggle = get_option('options_go_badges_toggle');
                if ($badges_toggle && !empty($badges_ids)) {
                    $badge_dir = $result[2];
                    $badges_name = get_option('options_go_badges_name_plural');

                    $badges_names = array();
                    $badges_names[] = "<br><b>" . $badges_name . ":</b>";
                    foreach ($badges_ids as $badge_id) {

                        $badges_names[] = go_print_single_badge( $badge_id, 'badge', false);

                        if ($badge_dir == "badges+") {
                            $add = true;
                        }else{
                            $add = false;
                        }
                        go_term_notification('badges', $badge_id, $add);
                    }

                }


                if (!empty($group_ids)) {
                    $groups_dir = $result[3];
                    $groups_names = array();
                    $groups_names[] = "<br><b>Groups:</b>";
                    foreach ($group_ids as $group_id) {
                        $groups_names[] = go_print_single_badge( $group_id, 'group', false);
                        if ($groups_dir == "groups+") {
                            $add = true;
                        }else{
                            $add = false;
                        }
                        go_term_notification('groups', $group_id, $add);
                    }

                }


            }

        }
        go_update_admin_bar($user_id);

        if($up){
            $sound = go_up_sound();
            echo $sound;
        }

        if($down){
            $sound = go_down_sound();
            echo $sound;
        }



        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$go_actions_table_name}
            SET stage = %d
			WHERE uid = %d and (action_type = %s or action_type = %s or action_type = %s or action_type = %s or action_type = %s OR action_type = %s OR action_type = %s)  and stage = %d",
                0,
                $user_id,
                'message',
                'reset',
                'admin_notification',
                'feedback',
                'feedback_percent',
                'feedback_loot',
                'attendance',
                1
            )
        );

        update_user_option($user_id, 'go_new_messages', false);
    }
    $buffer = ob_get_contents();
    ob_end_clean();
    //if ($heartbeat === false ){
    if ( defined( 'DOING_AJAX' )) {

        //add these scripts to the json ajax response
       // $response['go_message'] = $buffer;
        return $buffer;
    }else {
        echo $buffer;//just print the scripts in the footer
    }

}


/**
 * @param bool $skip_ajax
 * @param string $title
 * @param string $sent_message
 * @param string $type
 * @param string $penalty
 * @param int $sent_xp
 * @param int $sent_gold
 * @param int $sent_health
 * @param null $task_id //only sent from when undo causes bankruptcy and a rep penalty is applied
 * @param bool $loot_toggle
 * @param string $sent_badge_id
 * @param string $sent_group_id
 * @param string $reset_vars
 */
function go_send_message($skip_ajax = false, $title = '', $sent_message = '', $type = '', $penalty = '', $sent_xp = 0, $sent_gold = 0, $sent_health = 0, $task_id = null, $loot_toggle = false, $sent_badge_id = '', $sent_group_id = '', $reset_vars = ''){

    if(!$skip_ajax) {
        if (!is_user_logged_in()) {
            echo "login";
            die();
        }

        //check_ajax_referer( 'go_send_message');
        if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_send_message')) {
            echo "refresh";
            die();
        }

        $title = (!empty($_POST['title']) ? $_POST['title'] : "");
        $title = wp_kses_post($title);
        $title  = do_shortcode( $title );
        //$sent_message = stripslashes(!empty($_POST['message']) ? $_POST['message'] : "");
        $sent_message = (!empty($_POST['message']) ? $_POST['message'] : "");
        $sent_message = wp_kses_post($sent_message);
        $sent_message  = do_shortcode( $sent_message );
        $type = (!empty($_POST['message_type']) ? $_POST['message_type'] : "message");// can be message, or reset

        $penalty = (!empty($_POST['penalty']) ? $_POST['penalty'] : false);
        $is_note = (!empty($_POST['is_note']) ? $_POST['is_note'] : false);
        if($is_note == "true"){
            $type = 'note';
        }

        $sent_xp = ($_POST['xp']);
        $sent_gold = ($_POST['gold']);
        $sent_health = ($_POST['health']);

        $loot_toggle = $_POST['loot_toggle'];
        if($loot_toggle == '1'){
            $loot_toggle = true;
        }else{
            $loot_toggle = false;
        }
        $sent_badge_id = $_POST['badges'];

        //$groups_toggle = $_POST['groups_toggle'];
        $sent_group_id = $_POST['groups'];

        $sent_section_ids = $_POST['sections'];

        $reset_vars = $_POST['reset_vars'];
    }


    global $wpdb;
    $go_task_table_name = "{$wpdb->prefix}go_tasks";

    $task_name = get_option('options_go_tasks_name_singular');

    foreach ($reset_vars as $vars){
        $user_id = $vars['uid'];
        $message = '';
        $xp_task = 0;
        $gold_task = 0;
        $health_task = 0;
        $badge_array = array();
        $group_array = array();
        $sections_array = array();

        $status = 0;
        $bonus_status = 0;
        $last_time = current_time('mysql');
        if ($type == "reset" || $type == "reset_stage") {
            if ($type == "reset") {//set the reset variables for a full quest reset
                $task_id = $vars['task'];
                $task_title = get_the_title($task_id);
                $title = "The following " . $task_name . " has been reset: " . $task_title . ".";
                $message = "All loot and rewards earned have been removed.";
                if (!empty($sent_message)) {      //if there is a custom message
                    $message = $sent_message."<br><br>".$message;
                }

                //get task table info
                $tasks = $wpdb->get_results($wpdb->prepare("SELECT *
                    FROM {$go_task_table_name}
                    WHERE uid = %d and post_id = %d
                    ORDER BY last_time DESC", $user_id, $task_id
                ));

                $task = $tasks[0];//the array of task info for this user

                $xp_task = ($task->xp * -1);
                $gold_task = ($task->gold * -1);
                $health_task = ($task->health * -1);

                //this info is a serialized array--convert it to an array or create an empty array
                $badge_array = unserialize($task->badges);
                $group_array = unserialize($task->groups);
                if (!is_array($badge_array)) {
                    if(is_numeric($badge_array)){
                        $badge_array = array($badge_array);
                    }else{
                        $badge_array = array();
                    }
                }
                if (!is_array($group_array)) {
                    if(is_numeric($group_array)) {
                        $group_array = array($group_array);
                    }else{
                        $group_array = array();
                    }
                }

            }
            else if ($type == 'reset_stage') {//set the reset variables for a stage reset by resetting a blog post
                $type = 'reset';
                $blog_post_id = $vars['task'];//this variable is not a task_id, but is the blog_id--we use that to find the task_id
                //$user_id = $vars['uid'];
                //$user_id = get_post_field('post_author', $blog_post_id);
                if ($blog_post_id != 0 && !empty($blog_post_id)) {
                    //get task_id from the blog post id
                    $task_id = go_get_task_id($blog_post_id);
                    //ERROR CHECK
                    if (empty($task_id)) {//this post is not associated with a task
                        die();//maybe put error message here
                    }

                    $task_title = get_the_title($task_id);
                    $task_url = get_permalink($task_id);
                    $title = "A blog post from the following " . $task_name . " has been reset: <a href='{$task_url}'>" . $task_title . "</a>.";
                    $message = "All loot and rewards earned on this " . $task_name . " from this point forward have been removed.";
                    if (!empty($sent_message)) {
                        $message = $sent_message."<br><br>".$message;
                    }

                    //get info about when this blog post was submitted

                    //get the last time this task is in the actions table
                    //this gives the first row because it is searching for the blog post id and sorted by id
                    $aTable = "{$wpdb->prefix}go_actions";
                    $result = $wpdb->get_results($wpdb->prepare("SELECT id, stage, bonus_status
                        FROM {$aTable} 
                        WHERE result = %d AND source_id = %d AND action_type = 'task'
                        ORDER BY id DESC LIMIT 1",
                        $blog_post_id,
                        $task_id
                    ), ARRAY_A
                    );

                    $first_row = $result[0];
                    //$result = json_decode(json_encode($result), true);
                    $id = $first_row['id'];//this row ID of the last time this blog post was submitted.
                    $status = $first_row['stage'];//the stage this was submitted on--empty if it was a bonus task

                    //get task table info
                    $bonus_status = $wpdb->get_var($wpdb->prepare("SELECT bonus_status
                        FROM {$go_task_table_name}
                        WHERE uid = %d and post_id = %d
                        ORDER BY id DESC", $user_id, $task_id
                    ));

                    //$current_bonus_status = $tasks[0]->bonus_status;

                    //get all loot on this stage since the last time this blog post was submitted
                    if (!empty($status)) {//remove all loot since this stage, including this stage and (maybe?) mark all other blog posts deleted
                        $status = intval($status) -1;
                        $bonus_status = 0;
                        //get all actions on this stage so the loot can be added up
                        $loot = $wpdb->get_results($wpdb->prepare("SELECT xp, gold, health, badges, groups, check_type, result
                        FROM {$aTable} 
                        WHERE uid = %d AND source_id = %d AND id >= %d
                        ORDER BY id ", $user_id, $task_id, $id), ARRAY_A);


                        foreach ($loot as $loot_row) {
                            $xp_task = $loot_row['xp'] + $xp_task;
                            $gold_task = $loot_row['gold'] + $gold_task;
                            $health_task = $loot_row['health'] + $health_task;
                            $badges_task = $loot_row['badges'];
                            $groups_task = $loot_row['groups'];
                            $check_type = $loot_row['check_type'];
                            $result = $loot_row['result'];

                            //set all posts submitted after this post to reset
                            if ($check_type === "blog") {
                                if(is_numeric($result)) {
                                    $post = array('ID' => intval($result), 'post_status' => 'reset');
                                    wp_update_post($post);
                                }
                            }

                            //combine any badges and groups earned into an array.  This should only be one badge.
                            $badge_task = unserialize($badges_task);
                            $group_task = unserialize($groups_task);

                            if (!is_array($badge_task)) {
                                if (is_numeric($badge_task)) {
                                    $badge_task = array($badge_task);
                                } else {
                                    $badge_task = array();
                                }
                            }
                            if (!is_array($group_task)) {
                                if (is_numeric($group_task)) {
                                    $group_task = array($group_task);
                                } else {
                                    $group_task = array();
                                }
                            }

                            $badge_array = array_unique(array_merge($badge_task, $badge_array));
                            $group_array = array_unique(array_merge($group_task, $group_array));
                            //END combine badges and groups
                        }
                    }
                    else if (!empty($bonus_status)) {
                        $bonus_status = $bonus_status -1;

                        //get status--don't change it because this is a bonus stage reset
                        $status = $wpdb->get_var($wpdb->prepare("SELECT status
                        FROM {$go_task_table_name}
                        WHERE uid = %d and post_id = %d
                        ORDER BY id DESC", $user_id, $task_id
                        ));


                        $bonus_stage_loot = $wpdb->get_results($wpdb->prepare("SELECT xp, gold, health, check_type
                        FROM {$aTable} 
                        WHERE uid = %d AND source_id = %d AND id >= %d AND result = %d
                        ORDER BY id DESC LIMIT 1", $user_id, $task_id, $id, $blog_post_id), ARRAY_A);


                        $xp_task = $bonus_stage_loot[0]['xp'];
                        $gold_task = $bonus_stage_loot[0]['gold'];
                        $health_task = $bonus_stage_loot[0]['health'];
                        /*
                        $gold_task = $loot_row['gold'] + $gold_task;
                        $health_task = $loot_row['health'] + $health_task;
                        $badges_task = $loot_row['badges'];
                        $groups_task = $loot_row['groups'];
                        $check_type = $loot_row['check_type'];
                        $result = $loot_row['result'];


                        //set all posts submitted after this post to reset
                        if ($check_type === "blog") {
                            if(is_numeric($result)) {
                                $post = array('ID' => intval($result), 'post_status' => 'reset');
                                wp_update_post($post);
                            }
                        }

                        //combine any badges and groups earned into an array.  This should only be one badge.
                        $badge_task = unserialize($badges_task);
                        $group_task = unserialize($groups_task);

                        if (!is_array($badge_task)) {
                            if (is_numeric($badge_task)) {
                                $badge_task = array($badge_task);
                            } else {
                                $badge_task = array();
                            }
                        }
                        if (!is_array($group_task)) {
                            if (is_numeric($group_task)) {
                                $group_task = array($group_task);
                            } else {
                                $group_task = array();
                            }
                        }

                        $badge_array = array_unique(array_merge($badge_task, $badge_array));
                        $group_array = array_unique(array_merge($group_task, $group_array));
                        //END combine badges and groups
                        */
                        //}


                        $post = array('ID' => intval($blog_post_id), 'post_status' => 'reset');
                        wp_update_post($post);
                    }else{
                        die();
                    }

                    //loot to be removed
                    $xp_task = ($xp_task) * -1;
                    $gold_task = ($gold_task) * -1;
                    $health_task = ($health_task) * -1;




                }

            }
            //below is for both resets and reset_stage

            //set class
           // $class = array('reset');
            $class = 'reset';
            //if (!empty($sent_xp) || !empty($sent_gold) || !empty($sent_health)) {
               // $class[] = 'down';
           // }

            //$class = serialize($class);

            //update task table
            /*
            $wpdb->update($go_task_table_name,
                array('status' => $status, 'bonus_status' => $bonus_status, 'xp' => $xp_task, 'gold' => $gold_task, 'health' => $health_task, 'badges' => null, 'groups' => null, 'last_time' => $last_time, 'class' => $class ),//data
                array('uid' => $user_id, 'post_id' => $task_id),//where
                array('%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s'),//data format
                array('%d', '%d')//where data format
            );*/

            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$go_task_table_name} 
                    SET 
                        status = {$status}, 
                        bonus_status = {$bonus_status},
                        xp = GREATEST(({$xp_task} + xp), 0),
                        gold = GREATEST(({$gold_task} + gold ), 0),
                        health = GREATEST(({$health_task} + health ), 0),
                        last_time = '{$last_time}',
                        class = '{$class}'     
                    WHERE uid= %d AND post_id=%d ",
                    intval($user_id),
                    intval($task_id)
                )
            );

            //This returns the string that is sent as a message.  It doesn't do the database changes.
            $message = go_reset_message($message, $penalty, $sent_xp, $sent_gold, $sent_health, $xp_task, $gold_task, $health_task, $sent_badge_id, $sent_group_id, $badge_array, $group_array);

            //combine the penalty loot and task loot. This is what will be removed
            $xp = $sent_xp + $xp_task;
            $gold = $sent_gold + $gold_task;
            $health = $sent_health + $health_task;

            //if (!empty($badge_task)) {//if a badge was earned, merge them with the sent badge to be removed later
            $badge_ids = array_merge($badge_array, array($sent_badge_id));
            // }

            // if (!empty($group_task)) {//if a group was earned, merge it with the sent group to be removed later
            $group_ids = array_merge($group_array, array($sent_group_id));
            // }

        }
        else {//this is a regular message
            if(!empty($sent_badge_id)) {
                $badge_ids = array($sent_badge_id);
            }else{ $badge_ids = array();}

            if(!empty($sent_group_id)) {
                $group_ids = array($sent_group_id);
            }else{ $group_ids = array();}

            if(!empty($sent_section_ids)) {
                $section_ids = array($sent_section_ids);
            }else{ $section_ids = array();}

            $xp = $sent_xp;
            $gold = $sent_gold;
            $health = $sent_health;

            $message = $sent_message;

        }

        //add sections to message
        if ($loot_toggle  && !empty($section_ids)) {//if groups toggle is true and groups exist
            $key = go_prefix_key('go_section');
            add_user_meta( $user_id, $key, $section_ids[0], false );
            $term = get_term($section_ids[0]);
            $term_name = $term->name;
            $message .= "<p>You have been added to ".$term_name.".";

        }else if (!$loot_toggle && !empty($section_ids)) {//else if groups toggle is false and groups exist
            $key = go_prefix_key('go_section');
            delete_user_meta( $user_id, $key, $section_ids[0] );
            $term = get_term($section_ids[0]);
            $term_name = $term->name;
            $message .= "<p>You have been removed from ".$term_name.".";

        }

        ////START MESSAGE CONSTRUCTION
        //the results are combined for saving in the database as a serialized array
        $result = array();
        if($is_note == "true"){
            $title = "NOTE: " . $title;
        }
        $result[] = $title;
        $result[] = $message;

        //store the badge and group toggles so later we know if they were awarded or taken.
        if ($loot_toggle && !empty($badge_ids)) {//if badges toggle is true and badges exist
            $result[] = "badges+";
            $badge_ids = go_add_badges($badge_ids, $user_id, true);//add badges
            $badge_ids = serialize($badge_ids);
        }else if (!$loot_toggle && !empty($badge_ids)) {//else if badges toggle is false and badges exist
            $result[] = "badges-";
            $badge_ids = go_remove_badges($badge_ids, $user_id, true);//remove badges
            $badge_ids = serialize($badge_ids);
        }else {
            $result[] = "badges0";
            $badge_ids = null;
        }

        //add to DB and then serialize for storage with the message
        if ($loot_toggle  && !empty($group_ids)) {//if groups toggle is true and groups exist
            $result[] = "groups+";
            go_add_groups($group_ids, $user_id, true);//add groups
            $group_ids = serialize($group_ids);
        }else if (!$loot_toggle && !empty($group_ids)) {//else if groups toggle is false and groups exist
            $result[] = "groups-";
            go_remove_groups($group_ids, $user_id, true);//remove groups
            $group_ids = serialize($group_ids);
        }else{
            $result[] = "groups0";
            $group_ids = null;
        }
        $result = serialize($result);



        if(empty($title) && empty($message) && empty($badge_ids) && empty($group_ids) && empty($xp) && empty($gold) && empty($health)){
            die();
        }

        //update actions
        go_update_actions($user_id, $type, $task_id, 1, null, null, $result, null, null, null, null, $xp, $gold, $health, $badge_ids, $group_ids, true);
        if($is_note != "true") {
            update_user_option($user_id, 'go_new_messages', true);
        }
    }

    if(!$skip_ajax) {
    die();
    }

}
