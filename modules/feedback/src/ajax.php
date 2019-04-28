<?php

function go_filter_reader(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_filter_reader' );
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
    $sQuery1 = "SELECT SQL_CALC_FOUND_ROWS
            t4.ID, t4.post_status";

    $where = (isset($_POST['where']) ? $_POST['where'] : null);
    $pWhere = stripslashes($where);
    $order = (isset($_POST['order']) ? $_POST['order'] : null);

    $squery = (isset($_POST['query']) ? $_POST['query'] : null);
    $sQuery2 = stripslashes($squery);

    $sQuery = $sQuery1 . $sQuery2 . $pWhere . " ORDER BY t4.post_modified " . $order ;


    $posts = $wpdb->get_results($sQuery, ARRAY_A);
    //$task_ids = array_column($posts, 'ID');
    //$task_ids = json_decode($task_ids);

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
    return ("Posts were marked as read.");
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

    $title = (!empty($_POST['title']) ? $_POST['title'] : "");

    $message = (!empty($_POST['message']) ? $_POST['message'] : "");

    $percent = (!empty($_POST['percent']) ? $_POST['percent'] : "");

    $toggle = (!empty($_POST['toggle']) ? $_POST['toggle'] : "");

    $message = '<h3>' . $title . '</h3>' . $message;

    $blog_post_id = (!empty($_POST['post_id']) ? $_POST['post_id'] : "");

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
        if (empty($go_blog_task_id)) {//this post is not associated with a task
            die();//put error message here
        } else {
            $task_title = get_the_title($go_blog_task_id);
            $task_name = get_option('options_go_tasks_name_singular');
            $title = "Feedback on  " . $task_name . ": " . $task_title . ".";

            //the first time this blog_post_id was attached to this stage
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
            if ($toggle) {
                $direction = 1;
            } else {
                $direction = -1;
            }

            //if % is not 0
            if ($percent > 0) {

                //check last feedback, and if it exists, remove it
                $last_feedback = $wpdb->get_results($wpdb->prepare("SELECT id, xp, gold, health
                FROM {$aTable} 
                WHERE check_type = %s AND action_type = %s
                ORDER BY id DESC LIMIT 1",
                    $uniqueid,
                    'feedback'), ARRAY_A);
                //get original loot assigned on this stage--this is the baseline
                $last_xp = $last_feedback[0]['xp'];
                $last_gold = $last_feedback[0]['gold'];
                $last_health = $last_feedback[0]['health'];

                //compute change and +/-
                $xp = intval($xp * $percent * .01 * $direction) + $last_xp;
                $gold = $gold * $percent * .01 * $direction;
                $gold = number_format($gold, 2, '.', '') + $last_gold;
                $health = $health * $percent * .01 * $direction;
                $health = number_format($health, 2, '.', '') + $last_health;

                if ($toggle){
                    $loot_message = 'Your original loot was increased by ';
                }else{
                    $loot_message = 'Your original loot was decreased by ';
                }

                $loot_message .= $percent.'%.<br>';

                $message .= $loot_message;
            }

            ////START MESSAGE CONSTRUCTION
            //the results are combined for saving in the database as a serialized array
            $result = array();
            $result[] = $title;
            $result[] = $message;
            $result = serialize($result);
            //update actions
            go_update_actions($user_id, 'feedback', $go_blog_task_id, 1, null, $uniqueid, $result, null, null, null, null, $xp, $gold, $health, null, null, false, false);
        }

        //set new message user option to true so each user gets the message
        $user_id = intval($user_id);
        update_user_option($user_id, 'go_new_messages', true);
    }
}








