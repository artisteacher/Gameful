<?php

function go_blog_revision(){
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_blog_revision' ) ) {
        echo "refresh";
        die( );
    }

    $post_id = (isset($_POST['post_id']) ? $_POST['post_id'] : null);
    go_blog_post($post_id, null, false,false , false,false , null,null ,true);

    die();
}

function go_restore_revision(){
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_restore_revision' ) ) {
        echo "refresh";
        die( );
    }

    //check that post type is revision
    //die if not with error

    $post_id = (isset($_POST['post_id']) ? $_POST['post_id'] : null);
    $parent_id = (isset($_POST['parent_id']) ? $_POST['parent_id'] : null);
    wp_restore_post_revision($post_id);
    go_blog_post($parent_id, null, false,true , true,false , null,null ,false);

    die();
}

function go_filter_reader(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_filter_reader' ) ) {
        echo "refresh";
        die( );
    }
    go_reader_get_posts();
    die();
}

function go_reader_bulk_read(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_reader_bulk_read' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_reader_bulk_read' ) ) {
        echo "refresh";
        die( );
    }

    global $wpdb;
    $sQuery1 = "SELECT
            t4.ID";

    $where = (isset($_POST['where']) ? $_POST['where'] : null);
    $pWhere = stripslashes($where);
    $order = (isset($_POST['order']) ? $_POST['order'] : null);

    $squery = (isset($_POST['query']) ? $_POST['query'] : null);
    $sQuery2 = stripslashes($squery);

    //$sQuery = $sQuery1 . $sQuery2 . $pWhere . " ORDER BY t4.post_modified " . $order ;
    $sQuery = $sQuery1 . $sQuery2 . $pWhere ;

    $posts = $wpdb->get_results($sQuery, ARRAY_A);
    //$posts = array_column($posts, 'ID');

    $task_ids = array_column($posts, 'ID');
    //$task_ids = json_decode($task_ids);

    $comma_separated = "(".implode(",", $task_ids).")";


    $posts_table_name = "{$wpdb->prefix}posts";
    $wpdb->query(
            "UPDATE {$posts_table_name} SET post_status = 'read' WHERE ID IN {$comma_separated};"

    );
/*
    foreach($posts as $post){
        //$status = get_post_status($task_id);
        $task_id = $post['ID'];
        $status = $post['post_status'];
        if($status == 'unread') {
            $query = array(
                'ID' => $task_id,
                'post_status' => 'read',
            );
            wp_update_post($query, true);
        }
    }
    */
    echo ("Posts were marked as read.");
    die();
}

function go_reader_read_printed(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_reader_bulk_read' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_reader_read_printed' ) ) {
        echo "refresh";
        die( );
    }

    $post_ids = (isset($_POST['postids']) ? $_POST['postids'] : null);


    foreach($post_ids as $post_id){
        $query = array(
            'ID' => $post_id,
            'post_status' => 'read',
        );
        wp_update_post($query, true);
    }
    //echo ("Posts were marked as read.");
    die();
}

function go_mark_one_read_toggle(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_reader_bulk_read' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_mark_one_read_toggle' ) ) {
        echo "refresh";
        die( );
    }
    $post_id = (isset($_POST['postid']) ? $_POST['postid'] : null);
    $status = get_post_status($post_id);
    if($status == 'unread') {
        $query = array(
            'ID' => $post_id,
            'post_status' => 'read',
        );
        wp_update_post($query, true);
        echo "read";
    }
    else if($status == 'read') {
        $query = array(
            'ID' => $post_id,
            'post_status' => 'unread',
        );
        wp_update_post($query, true);
        echo "unread";
    }
    else{
        echo "refresh";
    }


    die();
}

function go_num_posts()
{

    if (!is_user_logged_in()) {
        echo "login";
        die();
    }

    check_ajax_referer( 'go_num_posts' );
    if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_num_posts')) {
        echo "refresh";
        die();
    }
    $where = (isset($_POST['where']) ? $_POST['where'] : null);
    $where = stripslashes($where);
    $order = (isset($_POST['order']) ? $_POST['order'] : null);

    $squery = (isset($_POST['query']) ? $_POST['query'] : null);
    $sQuery = stripslashes($squery);

    //$tQuery = (isset($_POST['tQuery']) ? $_POST['tQuery'] : null);
    //$tQuery = stripslashes($tQuery);



    go_reader_get_posts($sQuery, $where, $order);


    //echo "Posts were marked as read.";
    die();
}

function go_send_feedback()
{
    if (!is_user_logged_in()) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_send_message');
    if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_send_feedback')) {
        echo "refresh";
        die();
    }

    global $wpdb;
    //$go_task_table_name = "{$wpdb->prefix}go_tasks";

    $feedback_title = (!empty($_POST['title']) ? $_POST['title'] : "");
    $feedback_message = (!empty($_POST['message']) ? $_POST['message'] : "");
    $percent = (!empty($_POST['percent']) ? $_POST['percent'] : "");
    $percent_toggle = (!empty($_POST['toggle_percent']) ? $_POST['toggle_percent'] : "");
    $assign_toggle = (!empty($_POST['toggle_assign']) ? $_POST['toggle_assign'] : "");
    $radio = (!empty($_POST['radio']) ? $_POST['radio'] : "");
    $xp = (!empty($_POST['xp']) ? $_POST['xp'] : "");
    $gold = (!empty($_POST['gold']) ? $_POST['gold'] : "");
    $health = (!empty($_POST['health']) ? $_POST['health'] : "");
    $title = '';
    //$feedback_percent = null;

    $message = '<h3>' . $feedback_title . '</h3>' . $feedback_message;

    $blog_post_id = (!empty($_POST['post_id']) ? $_POST['post_id'] : "");
    $class = null;

    //$user_id = $vars['uid'];
    $user_id = get_post_field('post_author', $blog_post_id);
    if ($blog_post_id != 0 && !empty($blog_post_id)) {

       // $custom_fields = get_post_meta($blog_post_id, '');
       // $blog_meta = (isset($custom_fields['go_badges'][0]) ?  $custom_fields['go_badges'][0] : null);
        $uniqueid = get_post_meta($blog_post_id, 'go_stage_uniqueid', true);

        //$go_blog_task_id = (isset($blog_meta['go_blog_task_id'][0]) ? $blog_meta['go_blog_task_id'][0] : null);
        //$stage_num = (isset($blog_meta['go_blog_task_stage'][0]) ? $blog_meta['go_blog_task_stage'][0] : null);
        //$bonus_stage_num = (isset($blog_meta['go_blog_bonus_stage'][0]) ? $blog_meta['go_blog_bonus_stage'][0] : null);
        $aTable = "{$wpdb->prefix}go_actions";

        $go_blog_task_id = go_get_task_id($blog_post_id);
        $type = 'feedback';

        if ($radio == 'percent') {
            $type = 'feedback_percent';
            if (empty($go_blog_task_id)) {//this post is not associated with a task
                die();//put error message here
            } else {
                $task_title = get_the_title($go_blog_task_id);
                $task_name = get_option('options_go_tasks_name_singular');
                $title = "Feedback on  " . $task_name . ": " . $task_title . ".";

                //the last time this blog_post_id was attached to this stage
                $result = $wpdb->get_results($wpdb->prepare("SELECT id, uid, xp, gold, health
            FROM {$aTable} 
            WHERE result = %d AND source_id = %d AND action_type = %s
            ORDER BY id DESC LIMIT 1",
                    $blog_post_id,
                    $go_blog_task_id,
                    'task'), ARRAY_A);

                //get original loot assigned on this stage--this is the baseline
                $xp = $result[0]['xp'];
                $gold = $result[0]['gold'];
                $health = $result[0]['health'];

                if ($percent !=0) {
                    if ($percent_toggle) {
                        $direction = 1;
                        $class = 'up';
                    } else {
                        $direction = -1;
                        $class = 'down';
                    }
                }else{
                    $direction = 1;
                }

                //if % is not 0
                if ($percent > 0) {

                    //check last feedback, and if it exists, remove it
                    $last_feedback = $wpdb->get_results($wpdb->prepare("SELECT id, xp, gold, health
                FROM {$aTable} 
                WHERE source_id = %d AND check_type = %s AND action_type = %s
                ORDER BY id DESC LIMIT 1",
                        $blog_post_id,
                        $uniqueid,
                        'feedback_percent'), ARRAY_A);
                    //get last feedback
                    $last_xp = $last_feedback[0]['xp'];
                    $last_gold = $last_feedback[0]['gold'];
                    $last_health = $last_feedback[0]['health'];

                    //compute change and +/-
                    $xp = intval($xp * $percent * .01 * $direction) - intval($last_xp);
                    $gold = $gold * $percent * .01 * $direction;
                    $gold = number_format($gold, 2, '.', '') - $last_gold;
                    $health = $health * $percent * .01 * $direction;
                    $health = number_format($health, 2, '.', '') - $last_health;

                    if ($percent_toggle) {
                        $loot_message = '<br>Your original loot was increased by ';
                    } else {
                        $loot_message = '<br>Your original loot was decreased by ';
                    }

                    $loot_message .= $percent . '%.<br>';

                    $message .= $loot_message;
                }

                $feedback_percent = $percent * $direction;
                //go_update_actions($user_id, 'feedback', $blog_post_id, 1, null, $uniqueid, $result, null, null, null, null, $xp, $gold, $health, null, null, false, false);
                update_post_meta($blog_post_id, 'go_feedback_percent', $feedback_percent);

            }
        }
        else if ($radio == 'assign') {
            $type = 'feedback_loot';
            if (!empty($go_blog_task_id)) {//this post is not associated with a task

                $task_title = get_the_title($go_blog_task_id);
                $task_name = get_option('options_go_tasks_name_singular');
                $title = "Feedback on  " . $task_name . ": " . $task_title . ".";
            }
            else {
                $title = $feedback_title;
                $message = $feedback_message;
            }

            if ($assign_toggle){
                $class = 'up';
            }
            else{
                $xp = $xp * (-1);
                $gold = $gold * (-1);
                $health = $health * (-1);
                $class = 'down';
            }


        }
        else if ($radio == 'none') {
            if (!empty($go_blog_task_id)) {//this post is not associated with a task
                $task_title = get_the_title($go_blog_task_id);
                $task_name = get_option('options_go_tasks_name_singular');
                $title = "Feedback on  " . $task_name . ": " . $task_title . ".";
            } else {
                $title = $feedback_title;
                $message = $feedback_message;
            }
        }

        ////START MESSAGE CONSTRUCTION
        //the results are combined for saving in the database as a serialized array
        $result = array();
        $result[] = $title;
        $result[] = $message;
        $result[] = $feedback_title;
        $result[] = $feedback_message;
        $result[] = $percent_toggle;
        $result[] = $feedback_percent;
        $result = serialize($result);
        //update actions--send the feedback
        go_update_actions($user_id, $type, $blog_post_id, 1, null, $uniqueid, $result, null, null, null, null, $xp, $gold, $health, null, null, false, false);

        //set new message user option to true so each user gets the message
        $user_id = intval($user_id);
        update_user_option($user_id, 'go_new_messages', true);

        if ($class != null) {
            $go_task_table_name = "{$wpdb->prefix}go_tasks";
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$go_task_table_name} 
                    SET 
                        class = %s        
                    WHERE uid= %d AND post_id=%d ",
                    $class,
                    $user_id,
                    $go_blog_task_id
                )
            );
        }

        ob_start();
        go_feedback_form($blog_post_id);
        $form = ob_get_contents();
        ob_end_clean();

        global $wpdb;
        $aTable = "{$wpdb->prefix}go_actions";
        //check last feedback, and if it exists, remove it
        $all_feedback = $wpdb->get_results($wpdb->prepare("SELECT id, result
                FROM {$aTable} 
                WHERE source_id = %d AND action_type LIKE %s
                ORDER BY id DESC",
            $blog_post_id,
            '%feedback%'), ARRAY_A);
        ob_start();
        go_feedback_table($all_feedback);
        $feedback_table = ob_get_contents();
        ob_end_clean();

        echo json_encode(
            array(
                'json_status' => 302,
                'form' => $form,
                'table' => $feedback_table
            )
        );

    }
    die();
}








