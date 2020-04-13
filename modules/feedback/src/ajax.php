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
    go_blog_post($post_id, null, false,false , false,false ,null);

    $parent = wp_get_post_parent_id($post_id);
    ?>
    <div style="text-align:right;">
        <button class="go_restore_revision" data-post_id="<?php echo $post_id; ?>"
                data-parent_id="<?php echo $parent; ?>">Restore this post
        </button>
    </div>
    <script>
        jQuery('.go_restore_revision').off().one("click", function () {
            go_restore_revision(this);
        });
    </script>
    <?php




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
    $is_autosave = (isset($_POST['autosave']) ? $_POST['autosave'] : false);
    $form = (isset($_POST['form']) ? $_POST['form'] : false);
    $load_current = (isset($_POST['load_current']) ? $_POST['load_current'] : false);
    if(!$load_current) {
        wp_restore_post_revision($post_id);
    }
    if(!$form) {
        go_blog_post($parent_id, null, false, true, true, false, null);
    }
    else{
        if($is_autosave === "true") {
            go_blog_form($parent_id);
        }
        else{
            go_blog_form($post_id);
        }
        //go_buttons($custom_fields, $i, $stage_count, $status, $check_type, $bonus, $bonus_status, $repeat_max, false, $blog_post_id);
    }

    die();
}

//This filters the reader and loads the reader if it is the lightbox
function go_filter_reader(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_filter_reader' ) ) {
        echo "refresh";
        die( );
    }
    $is_initial_single_stage = (isset($_GET['is_initial_single_stage']) ? $_GET['is_initial_single_stage'] : false);

    if($is_initial_single_stage == "true"){

        go_make_reader();

    }else{
        go_reader_get_posts( null, null, null);
    }
    //go_reader_get_posts(false);

    die();
}

//remove nonce?
function go_reader_bulk_read($query){
/*
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_reader_bulk_read' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_reader_bulk_read' ) ) {
        echo "refresh";
        die( );
    }
*/

    global $wpdb;
    /*$sQuery1 = "SELECT
            t4.ID";

    $where = (isset($_POST['where']) ? $_POST['where'] : null);
    $pWhere = stripslashes($where);
    $order = (isset($_POST['order']) ? $_POST['order'] : null);

    $squery = (isset($_POST['query']) ? $_POST['query'] : null);
    $sQuery2 = stripslashes($squery);

    //$sQuery = $sQuery1 . $sQuery2 . $pWhere . " ORDER BY t4.post_modified " . $order ;
    $sQuery = $sQuery1 . $sQuery2 . $pWhere ;
*/

    //$posts = $wpdb->get_results($sQuery, ARRAY_A);
    //$posts = array_column($posts, 'ID');
    $posts = $wpdb->get_results($query, ARRAY_A);
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
    //echo '<div id="go_post_found" style="width: 600px;float:left;"></div>';
    //die();
}

//remove nonce?
function go_reader_read_printed(){
/*
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_reader_bulk_read' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_reader_read_printed' ) ) {
        echo "refresh";
        die( );
    }*/

    $post_ids = (isset($_POST['postids']) ? $_POST['postids'] : null);


    foreach($post_ids as $post_id){
        if(get_post_status($post_id) == 'unread') {
            $query = array(
                'ID' => $post_id,
                'post_status' => 'read',
            );
            wp_update_post($query, true);
        }
    }
    //echo ("Posts were marked as read.");
    //die();
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
    $post_author_id = get_post_field( 'post_author', $post_id );
    $status = get_post_status($post_id);
    if($status == 'unread') {
        $query = array(
            'ID' => $post_id,
            'post_status' => 'read',
        );
        wp_update_post($query, true);
        echo "read";
        //also set parent task class to read
            //get parent task
        $go_blog_task_id = go_get_task_id($post_id);
        global $wpdb;
        $class = null;
        $go_task_table_name = "{$wpdb->prefix}go_tasks";
        $current_class = $wpdb->get_var($wpdb->prepare("SELECT class
			FROM {$go_task_table_name}
			WHERE uid = %d and post_id = %d
			ORDER BY last_time DESC", $post_author_id, $go_blog_task_id
        ));

        if(empty($current_class)){
            $class = 'read';
        }

        if ($class != null) {

            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$go_task_table_name} 
                    SET 
                        class = %s        
                    WHERE uid= %d AND post_id=%d ",
                    $class,
                    $post_author_id,
                    $go_blog_task_id
                )
            );
        }

            //check for class
            //if class is empty
            //set class to read

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
    $where = (isset($_GET['where']) ? $_GET['where'] : null);
    $where = stripslashes($where);
    $order = (isset($_GET['order']) ? $_GET['order'] : null);

    $squery = (isset($_GET['query']) ? $_GET['query'] : null);

    $sQuery = stripslashes($squery);

    $cards = (isset($_GET['cards']) ? $_GET['cards'] : "false");
    $user_id = get_current_user_id();

    update_user_option($user_id, 'go_use_cards', $cards);

    //$tQuery = (isset($_POST['tQuery']) ? $_POST['tQuery'] : null);
    //$tQuery = stripslashes($tQuery);

    go_reader_get_posts( $sQuery, $where, $order);

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

    $feedback_title = (!empty($_POST['title']) ? $_POST['title'] : "");
    $feedback_title = wp_kses_post($feedback_title);
    $feedback_title = stripslashes($feedback_title);
    $feedback_message = (!empty($_POST['message']) ? $_POST['message'] : "");
    $feedback_message = wp_kses_post($feedback_message);
    $feedback_message = stripslashes($feedback_message);
    $feedback_title  = do_shortcode( $feedback_title );
    $feedback_message  = do_shortcode( $feedback_message );
    $percent = (!empty($_POST['percent']) ? $_POST['percent'] : "");
    $percent_toggle = (!empty($_POST['toggle_percent']) ? $_POST['toggle_percent'] : "");
    $assign_toggle = (!empty($_POST['toggle_assign']) ? $_POST['toggle_assign'] : "");
    $radio = (!empty($_POST['radio']) ? $_POST['radio'] : "");
    $xp = (!empty($_POST['xp']) ? $_POST['xp'] : "");
    $gold = (!empty($_POST['gold']) ? $_POST['gold'] : "");
    $health = (!empty($_POST['health']) ? $_POST['health'] : "");
    $title = '';
    $feedback_percent = null;

    $message = '<h3>' . $feedback_title . '</h3>' . $feedback_message;

    $blog_post_id = (!empty($_POST['post_id']) ? $_POST['post_id'] : "");
    $this_class = null;
    $class = null;

    //$user_id = $vars['uid'];
    $user_id = get_post_field('post_author', $blog_post_id);
    if ($blog_post_id != 0 && !empty($blog_post_id)) {

       // $custom_fields = get_post_meta($blog_post_id, '');
       // $blog_meta = (isset($custom_fields['go_badges'][0]) ?  $custom_fields['go_badges'][0] : null);
        $uniqueid = go_post_meta($blog_post_id, 'go_stage_uniqueid', true);

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
                       // $class = 'up';
                    } else {
                        $direction = -1;
                       // $class = 'down';
                    }
                }else{
                    $direction = 1;
                }

                //if % is not 0
                if ($percent > 0) {

                    //check last feedback, and if it exists, remove it
                    /*
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
                    $last_health = $last_feedback[0]['health'];*/

                    //compute change and +/-
                    $xp = intval($xp * $percent * .01 * $direction); //- intval($last_xp);
                    $gold = $gold * $percent * .01 * $direction;
                    $gold = number_format($gold, 2, '.', ''); // - $last_gold;
                    $health = $health * $percent * .01 * $direction;
                    $health = number_format($health, 2, '.', ''); // - $last_health;

                    if ($percent_toggle) {
                        $loot_message = '<br>Your loot was increased by ';
                    } else {
                        $loot_message = '<br>Your loot was decreased by ';
                    }

                    $loot_message .= $percent . '%.<br>';

                    $message .= $loot_message;
                }

                $feedback_percent = $percent * $direction;
                //go_update_actions($user_id, 'feedback', $blog_post_id, 1, null, $uniqueid, $result, null, null, null, null, $xp, $gold, $health, null, null, false, false);
                //update_post_meta($blog_post_id, 'go_feedback_percent', $feedback_percent);

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
                $this_class = 'up';
            }
            else{
                $xp = $xp * (-1);
                $gold = $gold * (-1);
                $health = $health * (-1);
                $this_class = 'down';
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

        //the class for the entire task should change based on the following
            //if the current class is reset, it stays reset
                //this changes to resetted when the user resubmits, still shows reset until admin looks at it

            //get all stages on this quest
            //are any down?
                //then down
            //are any up?
                //then up
            //are all read
                //then read
        $go_task_table_name = "{$wpdb->prefix}go_tasks";
        $current_class = $wpdb->get_var($wpdb->prepare("SELECT class
			FROM {$go_task_table_name}
			WHERE uid = %d and post_id = %d
			ORDER BY last_time DESC", $user_id, $go_blog_task_id
        ));

        if($current_class === 'reset'){
            $class = 'reset';
        }
        else{
            if($current_class === 'down'){
                $class = 'down';
            }else {
                $i = 0;
                $data = go_post_data($go_blog_task_id);
                $custom_fields = $data[3];
                $status = $data[1];
                $stage_count = $custom_fields['go_stages'][0];
                $blog_post_ids = array();
                while ($i <= $status && $stage_count > $i) {//get blog post ids from regular stages

                    //check the task meta for a uniqueid
                    $uniqueid = (isset($custom_fields['go_stages_' . $i . '_uniqueid'][0]) ? $custom_fields['go_stages_' . $i . '_uniqueid'][0] : false);

                    //if uniqueid found then get the blog_post_id with the meta data
                    if ($uniqueid) {
                        $blog_post_id = go_get_blog_post_id($go_blog_task_id, $user_id, 'go_stage_uniqueid', $uniqueid, null);
                    }
                    if (empty($blog_post_id)) {
                        //if no uniqueid was set or the blog post couldn't be found
                        //search using the v4 methods where that was saved with the stage# in the meta
                        $blog_post_id = go_get_blog_post_id($go_blog_task_id, $user_id, 'go_blog_task_stage', null, $i);
                    }
                    if (!empty($blog_post_id)) {
                        $blog_post_ids[] = $blog_post_id;
                    }
                    $i++;

                }
                //if there are bonus stages
                $bonus_status = go_get_bonus_status($go_blog_task_id, $user_id);
                if ($bonus_status <= 0) {

                    $statuses = array('draft', 'unread', 'read', 'publish', 'revise');

                    $args = array(
                        'post_status' => $statuses,
                        'post_type' => 'go_blogs',
                        'post_parent' => intval($go_blog_task_id),
                        'author' => $user_id,
                        'posts_per_page' => 0,
                        'meta_query' => array(
                            array(
                                'key' => 'go_blog_bonus_stage',
                                'value' => 1,
                                'compare' => '>=',
                            )
                        ),
                    );
                    $my_query = new WP_Query($args);


                    if ($my_query->have_posts()) {
                        while ($my_query->have_posts()) {
                            // Do your work...
                            $my_query->the_post();
                            $blog_post_id = get_the_ID();
                            $blog_post_ids[] = $blog_post_id;
                        } // end while
                    } // end if
                    wp_reset_postdata();
                }

                //get all stages on this quest
                //are any down?
                //then down
                //are any up?
                //then up
                //are all read
                //then read
                $all_read = null;
                if (!empty($blog_post_ids)) {
                    $all_read = true;
                }

                foreach ($blog_post_ids as $blog_post_id) {
                    $status = get_feedback_status($blog_post_id);
                    if ($status === 'down') {
                        $down = true;
                        break;//stop if any have down
                    } else if ($status === 'up') {
                        $up = true;
                    } else if ($status === 'has') {
                        $has = true;
                    }
                }

                if (!empty($blog_post_ids)) {
                    if(!empty($down)){
                        $class = 'down';
                    }else if(!empty($up)){
                        $class = 'up';
                    }
                    else if(!empty($has)){
                        $class = 'has_feedback';
                    }

                    if (empty($class)) {
                        if ($all_read) {
                            $class = 'read';
                        }
                    }
                }

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
        go_update_actions($user_id, $type, $blog_post_id, 1, null, $uniqueid, $result, null, null, null, null, $xp, $gold, $health, null, null, false);

        //set new message user option to true so each user gets the message
        $user_id = intval($user_id);
        update_user_option($user_id, 'go_new_messages', true);

        if ($class != null) {

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

        //make a new feedback table to load on the page
        global $wpdb;
        $aTable = "{$wpdb->prefix}go_actions";
        //check last feedback, and if it exists, remove it
        $all_feedback = $wpdb->get_results($wpdb->prepare("SELECT id, action_type, result, xp, gold, health
                FROM {$aTable} 
                WHERE source_id = %d AND (action_type = %s OR action_type = %s OR action_type = %s)
                ORDER BY id DESC",
            $blog_post_id,
            'feedback',
            'feedback_percent',
            'feedback_loot'), ARRAY_A);
        ob_start();
        go_feedback_table($all_feedback);
        $feedback_table = ob_get_contents();
        ob_end_clean();

        ob_start();
        echo get_feedback_icon($blog_post_id);
        $icon = ob_get_contents();
        ob_end_clean();

        echo json_encode(
            array(
                'json_status' => 302,
                'form' => $form,
                'table' => $feedback_table,
                'icon' => $icon
            )
        );

    }

    $query = array(
        'ID' => $blog_post_id,
        'post_status' => 'read',
    );
    wp_update_post($query, true);
    $key = 'go_post_data_' . $blog_post_id;
    go_delete_transient($key);
    die();
}

function go_get_likes_list(){
    if (!is_user_logged_in()) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_send_message');
    if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_get_likes_list')) {
        echo "refresh";
        die();
    }

    $user_id = (!empty($_POST['user_id']) ? $_POST['user_id'] : null);
    $post_id = (!empty($_POST['post_id']) ? $_POST['post_id'] : null);
    if(empty($user_id) || empty($post_id)){
        die();
    }

    $status = go_post_meta($post_id, 'go_blog_favorite', true );
    if(is_serialized($status)) {
        $status = unserialize($status);
    }
    if (is_array($status)) {
        $count = count($status);
        if ($count > 0) {
            echo "<div><div class='go_likes_list'><h3>Likes</h3><div style='display: flex; '>";
            foreach ($status as $uid) {
                echo go_get_avatar($uid, false, array(35, 35));
                echo "<span style='width: 250px; padding-left: 10px;'>" . go_get_user_display_name($uid) . "</span>";
                $blog_toggle = get_option('options_go_blogs_toggle');
                if ($blog_toggle) {
                    /*if ($is_clipboard) {
                        $info_login = $login;
                    } else {
                        $user_info = get_userdata($user_id);
                        $info_login = $user_info->user_login;
                    }*/
                    $user_blog_link = get_site_url(null, '/user/' . $uid);
                    echo '<button onclick="window.open(\'' . $user_blog_link . '\')">View Blog</button>';
                }

                //echo "<span style='width: 50px;'>" . go_user_links($uid, false,  false,  true,  false ) . "</span>";
                echo "<br>";
            }
            echo"</div>";
        }
    }
    die();
}









