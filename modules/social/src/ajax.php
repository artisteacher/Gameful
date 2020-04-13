<?php

/**
 *
 */

function go_follow_request(){
    if (!is_user_logged_in()) {
        echo "login";
        die();
    }

    //check_ajax_referer('go_stats_leaderboard_');
    if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_follow_request')) {
        echo "refresh";
        die();
    }

    $user_id = $_POST['user_id'];

    $requester = get_current_user_id();

    $go_follow_request = go_prefix_key('go_follow_request');
    $go_follow_requested = go_prefix_key('go_follow_requested');

    add_user_meta( $user_id, $go_follow_request, $requester);
    add_user_meta( $requester, $go_follow_requested, $user_id);

    //delete_user_meta( int $user_id, string $meta_key, mixed $meta_value = '' )
    //add_user_meta( int $user_id, string $meta_key, mixed $meta_value, bool $unique = false )
    $message = "<button onclick='go_follow_request_accept(this)' data-user_id='$requester' class='go_social_button'>Accept Request</button>";
    ob_start();
    go_user_links($user_id, false, false, true);
    $links = ob_get_clean();

    echo "success";
    $user_name = go_get_user_display_name($requester);
    $vars[0]['uid']= $user_id;
    go_send_message(true, 'You have a friend request from ' . $user_name . "." . $links , $message, 'message', true, 0, 0, 0, 0, false, '', '', $vars);
    die();
}

function go_follow_request_accept(){
    if (!is_user_logged_in()) {
        echo "login";
        die();
    }

    //check_ajax_referer('go_stats_leaderboard_');
    if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_follow_request_accept')) {
        echo "refresh";
        die();
    }

    $requester = $_POST['user_id'];

    $user_id = get_current_user_id();

    $go_follow_request = go_prefix_key('go_follow_request');
    $go_follow_requested = go_prefix_key('go_follow_requested');

    $go_follower = go_prefix_key('go_follower');
    $go_following = go_prefix_key('go_following');

    delete_user_meta( $user_id, $go_follow_request, $requester);
    delete_user_meta( $requester, $go_follow_requested, $user_id);

    add_user_meta( $user_id, $go_follower, $requester);
    add_user_meta( $requester, $go_following, $user_id);

    echo "success";
    die();
}

function go_follow_unfollow(){
    if (!is_user_logged_in()) {
        echo "login";
        die();
    }

    //check_ajax_referer('go_stats_leaderboard_');
    if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_follow_unfollow')) {
        echo "refresh";
        die();
    }

    $user_id = $_POST['user_id'];
    $requester = get_current_user_id();

    $go_follower = go_prefix_key('go_follower');
    $go_following = go_prefix_key('go_following');

    delete_user_meta( $user_id, $go_follower, $requester);
    delete_user_meta( $requester, $go_following, $user_id);

    echo "success";
    die();
}

function go_follow_remove_follower(){
    if (!is_user_logged_in()) {
        echo "login";
        die();
    }

    //check_ajax_referer('go_stats_leaderboard_');
    if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_follow_remove_follower')) {
        echo "refresh";
        die();
    }

    $requester = $_POST['user_id'];
    $user_id = get_current_user_id();

    $go_follower = go_prefix_key('go_follower');
    $go_following = go_prefix_key('go_following');

    add_user_meta( $user_id, $go_follower, $requester);
    add_user_meta( $requester, $go_following, $user_id);

    delete_user_meta( $user_id, $go_follower, $requester);
    delete_user_meta( $requester, $go_following, $user_id);

    echo "success";
    die();
}

function go_follow_request_deny(){
    if (!is_user_logged_in()) {
        echo "login";
        die();
    }

    //check_ajax_referer('go_stats_leaderboard_');
    if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_follow_request_deny')) {
        echo "refresh";
        die();
    }

    $requester = $_POST['user_id'];
    $user_id = get_current_user_id();

    $go_follow_request = go_prefix_key('go_follow_request');
    $go_follow_requested = go_prefix_key('go_follow_requested');

    delete_user_meta( $user_id, $go_follow_request, $requester);
    delete_user_meta( $requester, $go_follow_requested, $user_id);

    echo "success";
    die();
}

function go_leaderboard_dataloader_ajax(){

    if (!is_user_logged_in()) {
        echo "login";
        die();
    }

    //check_ajax_referer('go_stats_leaderboard_');
    if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_leaderboard_dataloader_ajax')) {
        echo "refresh";
        die();
    }

    global $wpdb;
    $is_admin = go_user_is_admin();


    //$section = $_GET['section'];
    //$group = $_GET['group'];
    $show_all = $_GET['show_all'];

    //$section = go_section();
    $sLimit = '';
    if (isset($_GET['start']) && $_GET['length'] != '-1') {
        $sLimit = "LIMIT " . intval($_GET['start']) . ", " . intval($_GET['length']);
    }

    //$sOrder = go_sOrder('leaderboard', $section);

    $order_dir = $_GET['order'][0]['dir'];
    $order_col = $_GET['order'][0]['column'];

    if ($order_col == 3){
        $order_col = 'xp';//xp
    }
    else if ($order_col == 4){
        $order_col = 'gold';//gold
    }
    else if ($order_col == 5){
        $order_col = 'health';//health
    }
    else if ($order_col == 6){
        $order_col = 'badge_count';//badges
    }


    $sOrder = "ORDER BY " . $order_col . " " . $order_dir;



    $lTable = "{$wpdb->prefix}go_loot";
    if(is_gameful()) {
        $main_site_id = get_network()->site_id;
        switch_to_blog($main_site_id);
    }
    $umTable = "{$wpdb->prefix}usermeta";
    $uTable = "{$wpdb->prefix}users";
    if(is_gameful()) {
        restore_current_blog();
    }

    $site_display_name_key = go_prefix_key('go_nickname');

    if($show_all === 'true'){
        $sOrder = "ORDER BY " . 'mynickname' . " ASC";
    }//

    $sectionQuery = go_sectionQuery();
    $badgeQuery = go_badgeQuery();
    $groupQuery = go_groupQuery();
    //$badgeQuery = '';

    $caps_key = "{$wpdb->prefix}capabilities";

    $sQuery = "    
                    SELECT SQL_CALC_FOUND_ROWS
                      t10.*,
                      CASE 
                        WHEN t10.site_name IS NOT NULL THEN t10.site_name 
                        WHEN t10.nickname IS NOT NULL THEN t10.nickname
                        END AS mynickname 
                    FROM
                    (
                    
                        SELECT 
                          t1.*,
                          t3.display_name, t3.user_url, t3.user_login,
                          MAX(CASE WHEN t2.meta_key = 'nickname' THEN meta_value END) AS nickname, 
                          MAX(CASE WHEN t2.meta_key = '$site_display_name_key' THEN meta_value END) AS site_name,
                          MAX(CASE WHEN t2.meta_key = 'first_name' THEN meta_value END) AS first_name,
                          MAX(CASE WHEN t2.meta_key = 'last_name' THEN meta_value END) AS last_name,
                          MAX(CASE WHEN t2.meta_key = '$caps_key' THEN meta_value END) AS wp_capabilities
                        FROM
                              (
                              SELECT t6.user_id
                              FROM
                                (
                                SELECT t4.user_id
                                FROM
                                    (
                                    SELECT t2.*
                                    FROM
                                      (
                                      SELECT t1.uid AS user_id
                                      FROM $lTable AS t1
                                      ) AS t2
                                      $sectionQuery
                                    ) AS t4
                                $badgeQuery
                                ) AS t6
                              $groupQuery
                              )AS t8
                          LEFT JOIN $lTable AS t1 ON t8.user_id = t1.uid
                          LEFT JOIN $umTable AS t2 ON t8.user_id = t2.user_id
                          LEFT JOIN $uTable AS t3 ON t8.user_id = t3.ID
                          GROUP BY t1.id
                          HAVING (wp_capabilities NOT LIKE '%administrator%')   
                    ) AS t10
                    $sOrder
                    $sLimit 
        ";


    //Add Badge and Group names from the action item?,
    //can't do because they might have multiple saved in a serialized array so it can't be joined.

    ////columns that will be returned
    $rResult = $wpdb->get_results($sQuery, ARRAY_A);

    $sQuery = "SELECT FOUND_ROWS()";

    $rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iFilteredTotal = $rResultFilterTotal [0];



    if($show_all === 'false') {
        $percent_to_show = get_option('options_go_stats_leaderboard_percent_to_show');
        if (!$percent_to_show) {
            $percent_to_show = 50;
        }
        $percent_to_show = $percent_to_show * .01;
    }else{
        $percent_to_show = 1;
    }
    //$percent_to_show = .2;
    $newTotal = intval($iFilteredTotal[0]) * $percent_to_show;
    $iFilteredTotal[0] = $newTotal;
    $num_to_show = intval($iFilteredTotal[0]) * $percent_to_show;

    // $_GET['start']) . ", " . intval($_GET['length']




    $caps_key = "{$wpdb->prefix}capabilities";
    $sQuery = "
     SELECT COUNT(*)
     FROM( 
      SELECT 
          MAX(CASE WHEN t2.meta_key = '$caps_key' THEN meta_value END) AS capabilities
      FROM $lTable AS t1 
          LEFT JOIN $umTable AS t2 ON t1.uid = t2.user_id
          GROUP BY t1.id
          HAVING ( capabilities NOT LIKE '%administrator%')
      ) AS t3   
    ";

    $rResultTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iTotal = $rResultTotal [0];
    //$iFilteredTotal = number that match without limit;
    //$iTotalRecords = number in this table total (total store items/messages)
    $output = array("iTotalRecords" => $iTotal, "iTotalDisplayRecords" => $iFilteredTotal, "aaData" => array());

    $num = $_GET['start'];
    $i = 0;
    //$is_alpha_sorted = false;
    foreach($rResult as $action){//output a row for each action
        unset($rResult[$i]);
        $i++;
        $num++;
        //$percent = $i / intval($iFilteredTotal[0]);

        if($num > $newTotal){
            break;
            /*
            function compare_display_name($a, $b)
            {
                return strnatcmp($a['display_name'], $b['display_name']);
            }
            function compare_nickname($a, $b)
            {
                return strnatcmp($a['nickname'], $b['nickname']);
            }
            function compare_site_name($a, $b)
            {
                return strnatcmp($a['site_name'], $b['site_name']);
            }

            // sort alphabetically by name
            usort($rResult, 'compare_display_name');
            usort($rResult, 'compare_nickname');
            usort($rResult, 'compare_site_name');
            $is_alpha_sorted = true;*/
        };//

        $current_user_id = get_current_user_id();
        //$go_follower = go_prefix_key('go_follower');
        $go_following = go_prefix_key('go_following');
        $go_follow_request = go_prefix_key('go_follow_request');
        $go_follow_requested = go_prefix_key('go_follow_requested');

        //$followers = get_user_meta($current_user_id, $go_follower);
        $following = get_user_meta($current_user_id, $go_following);
        $follow_request = get_user_meta($current_user_id, $go_follow_request);
        $follow_requested = get_user_meta($current_user_id, $go_follow_requested);

        //The message content
        $row = array();
        $user_id = $action['uid'];

        if($show_all === 'true'){
            $xp = '';
            $gold = '';
            $health = '';
            $badge_count = '';
            //$gold = $action['nickname'];
            // $health = $action['display_name'];
            // $badge_count = $action['mynickname'];
        }else{
            $xp = $action['xp'];
            $gold = $action['gold'];
            $health = $action['health'];
            $badge_count = $action['badge_count'];
        }

        $user_display_name = $action['site_name'];
        if(empty($user_display_name)){
            $user_display_name = $action['nickname'];
            if(empty($user_display_name)){
                $user_display_name = $action['display_name'];
            }
        }
        $user_firstname = $action['first_name'];
        $user_lastname = $action['last_name'];

        /*
        $userdata = get_userdata($user_id);
        $user_display_name = $userdata->display_name;
        $user_firstname = $userdata->user_firstname;
        $user_lastname = $userdata->user_lastname;
        */

        //set full name
        $full_name_toggle = get_option('options_go_full-names_toggle');
        //$user_fullname = $user_firstname.' '.$user_lastname;

        //$num++;

        ob_start();
        go_user_links($user_id, true, true, true);
        $links = ob_get_clean();

        $row[] = "{$num}";
        $avatar = go_get_avatar($user_id, false, array(32, 32));
        $row[] = "{$avatar} {$user_display_name}";
        if ($full_name_toggle == 'full' || $is_admin){
            $row[] = $user_firstname . ' ' . $user_lastname;
        }else if ($full_name_toggle == 'first'){
            $row[] = $user_firstname ;

        }
        $row[] = "{$links}";//user period

        $xp_toggle = get_option('options_go_loot_xp_toggle');
        $gold_toggle = get_option('options_go_loot_gold_toggle');
        $health_toggle = get_option('options_go_loot_health_toggle');
        $social_feed = get_option('options_go_stats_leaderboard_show_social_feed');

        if($show_all === "false") {
            if ($xp_toggle) {

                $rank = go_get_rank($user_id, $xp);
                $rank_num = $rank['rank_num'];
                $current_rank = $rank['current_rank'];
                $pts_to_rank_up_str = "Level $rank_num: $current_rank &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; XP:$xp ";

                $row[] = "{$pts_to_rank_up_str}";
            }
            if ($gold_toggle) {
                $row[] = "{$gold}";
            }
            if ($health_toggle) {
                $row[] = "{$health}";
            }
            $badges_toggle = get_option('options_go_badges_toggle');
            if ($badges_toggle) {
                $row[] = "{$badge_count}";
            }
        }

        if ($social_feed) {
            $follow_id = $action['uid'];





            $follow = '';
            if($follow_id != $current_user_id) {
                if (!in_array($follow_id, $following)) {
                    //
                    //if request pending
                    if (in_array($follow_id, $follow_requested)) {
                        $follow .= "<span>Request Pending</span>";

                    } else {
                        $follow .= "<button onclick='go_follow_request(this)' data-user_id='$follow_id' class='go_social_button'>Follow</button>";
                    }
                } else {
                    $follow .= "<button style='opacity: .5;' onclick='go_follow_unfollow(this)' data-user_id='$follow_id' class='go_social_button'>Following</button>";
                }
                if (in_array($follow_id, $follow_request)) {
                    $follow .= " <button onclick='go_follow_request_accept(this)' data-user_id='$follow_id' class='go_social_button'>Accept Request</button>";
                }
            }

            $row[] = "{$follow}";
        }

        $output['aaData'][] = $row;
    }

    //$output['iTotalDisplayRecords'] =  count($output['aaData']);
    //global $go_debug;

    echo json_encode( $output );
    die();
}

function go_make_feed(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_make_feed' ) ) {
        echo "refresh";
        die();
    }

    echo "<div id='go_feed_container'>";
    go_make_reader(false, "DESC");
    echo "</div>";

    die();
}

function go_followers_list(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_followers_list' ) ) {
        echo "refresh";
        die();
    }

    $user_id = get_current_user_id();
    $go_follower = go_prefix_key('go_follower');
    $go_following = go_prefix_key('go_following');
    $go_follow_request = go_prefix_key('go_follow_request');

    $followers = get_user_meta($user_id, $go_follower);
    $following = get_user_meta($user_id, $go_following);
    $follow_request = get_user_meta($user_id, $go_follow_request);

    echo "<div><h3>Followers</h3>";

    $count = count($followers) + count($follow_request);
    if ($count == 0){
        echo "You don't have any followers yet.";
    }

    foreach ($follow_request as $uid){
        $avatar = go_get_avatar($uid, false, array(32, 32));
        $name = go_get_fullname($uid);
        ob_start();
        go_user_links($user_id, false, false, true);
        $links = ob_get_clean();
        echo "<div class='go_follower' style='display: flex; justify-content:space-between;'><span style='display: flex;'><span>$avatar</span> $name $links</span><span style='margin: 0 0 5px 30px;' >";
        if(in_array($uid, $following)) {
            echo "<button style='margin: 0 0 0 5px; opacity: .5;' onclick='go_follow_unfollow(this)' data-user_id='$uid' class='go_social_button'>Following</button>";
        }else{
            echo "<button style='margin: 0 0 0 5px;' onclick='go_follow_request(this)' data-user_id='$uid' class='go_social_button'>Follow</button>";
        }
        echo "<button onclick='go_follow_request_accept(this)' data-user_id='$uid' class='go_social_button'>Accept Request</button>";
        echo "<a href='javascript:void(0)'  style='margin: 0 0 0 5px;' onclick='go_follow_request_deny(this)' data-user_id='$uid'><i class=\"fas fa-ellipsis-v\"></i></a>";
        echo "</span></div>";
    }

    foreach ($followers as $uid){
        $name = go_get_fullname($uid);
        $avatar = go_get_avatar($uid, false, array(32, 32));
        ob_start();
        $user_is_admin = go_user_is_admin($uid);
        $show_blog_link = ($user_is_admin ?  false : true);
        go_user_links($uid, false, false, $show_blog_link);
        $links = ob_get_clean();
        echo "<div class='go_follower' style='display: flex; justify-content:space-between;'><span><span>$avatar</span> $name  $links</span><span style='margin: 0 0 5px 30px;' >";
        if(in_array($uid, $following)) {
            echo "<button style='margin: 0 0 0 5px; opacity: .5;' onclick='go_follow_unfollow(this)' data-user_id='$uid' class='go_social_button'>Following</button>";
        }else{
            echo "<button style='margin: 0 0 0 5px;' onclick='go_follow_request(this)' data-user_id='$uid' class='go_social_button'>Follow</button>";
        }
        echo "<a href='javascript:void(0)'  style='margin: 0 0 0 5px;' onclick='go_follow_remove_follower(this)' data-user_id='$uid'><i class=\"fas fa-ellipsis-v\"></i></a>";

        echo "</span></div>";
    }

    echo "</div>";

    die();
}

function go_following_list(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_following_list' ) ) {
        echo "refresh";
        die();
    }

    echo "<div><h3>Following</h3>";

    $user_id = get_current_user_id();
    $go_following = go_prefix_key('go_following');
    $following = get_user_meta($user_id, $go_following);

    $count = count($following);
    if ($count == 0){
        echo "You aren't following anyone yet.";
    }
   foreach ($following as $uid){

        $name = go_get_fullname($uid);
       $avatar = go_get_avatar($uid, false, array(32, 32));
        echo "<div style='display: flex; justify-content:space-between;'><span>$avatar $name</span><button style='margin: 0 0 5px 30px; opacity: .5;' onclick='go_follow_unfollow(this)' data-user_id='$uid'>Following</button></div>";
    }

    echo "</div>";
    die();
}