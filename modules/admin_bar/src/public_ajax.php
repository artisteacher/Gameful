<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-05-12
 * Time: 20:13
 */


function go_make_leaderboard_filter(){
    $current_id = get_current_user_id();

    //set the last searched leaderboard as an user option
    if(is_user_member_of_blog() || go_user_is_admin()) {
        $return = array();
        $taxonomy = $_POST['taxonomy'];
        $term_id = intval(get_user_option('go_leaderboard_' . $taxonomy, $current_id));
        $obj = get_term($term_id);
        $term_name = (mb_strlen($obj->name) > 50) ? mb_substr($obj->name, 0, 49) . '...' : $obj->name;
        //$return = array($term_id, $term_name);
        $term_id = intval($term_id);
        if ($term_id < 1){
            $term_id = '';
            $term_name = '';
        }
        $return['term_id'] = $term_id;
        $return['term_name'] = $term_name;
        echo json_encode( $return );
    }

    die();

}

//ajax only?
function go_stats_leaderboard_dataloader_ajax(){
    //needs nonce
    global $wpdb;
    $current_id = get_current_user_id();
    $is_admin = go_user_is_admin($current_id);

    //set the last searched leaderboard as an user option
    if(is_user_member_of_blog() || go_user_is_admin()) {
        $section = $_GET['section'];
        $group = $_GET['group'];

        if ($section === 'loading'){
            $section = intval(get_user_option('go_leaderboard_user_go_sections', $current_id));
            if ($section === 0){
                $section = '';
            }
        }else{
            update_user_option( $current_id, 'go_leaderboard_user_go_sections', $section );
        }


        if ($group === 'loading'){
            $group = intval(get_user_option('go_leaderboard_user_go_groups', $current_id));
            if ($group === 0){
                $group = '';
            }
        }else{
            update_user_option( $current_id, 'go_leaderboard_user_go_groups', $group );
        }

    }else{
        $section = $_GET['section'];
        $group = $_GET['group'];

    }

    //$section = go_section();
    $sLimit = '';
    if (isset($_GET['start']) && $_GET['length'] != '-1') {
        $sLimit = "LIMIT " . intval($_GET['start']) . ", " . intval($_GET['length']);
    }

    //$sOrder = go_sOrder('leaderboard', $section);

    $order_dir = $_GET['order'][0]['dir'];
    $order_col = $_GET['order'][0]['column'];
    if($is_admin){
        $order_col--;
    }
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

    $site_display_name_key = go_prefix_key('display_name');

    $sectionQuery = go_sectionQuery($section);
    //$badgeQuery = go_badgeQuery();
    $groupQuery = go_groupQuery($group);
    $badgeQuery = '';

    $caps_key = "{$wpdb->prefix}capabilities";

    $sQuery = "    
                    SELECT SQL_CALC_FOUND_ROWS
                      t1.*,
                      t3.display_name, t3.user_url, t3.user_login, 
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
    foreach($rResult as $action){//output a row for each action

        //The message content
        $row = array();
        $user_id = $action['uid'];
        $xp = $action['xp'];
        $gold = $action['gold'];
        $health = $action['health'];
        $badge_count = $action['badge_count'];
        $user_display_name = $action['site_name'];
        if(empty($user_display_name)){
            $user_display_name = $action['display_name'];
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

        $num++;

        ob_start();
        go_user_links($user_id, true, true, true);
        $links = ob_get_clean();

        $row[] = "{$num}";
        if ($full_name_toggle == 'full' || $is_admin){
            $row[] = $user_firstname . ' "' . $user_display_name .'" ' .$user_lastname;
        }else if ($full_name_toggle == 'first'){
            $row[] = $user_firstname . ' "' . $user_display_name .'"' ;

        }else{
            $row[] = "{$user_display_name}";
        }
        $row[] = "{$links}";//user period

        $xp_toggle = get_option('options_go_loot_xp_toggle');
        $gold_toggle = get_option('options_go_loot_gold_toggle');
        $health_toggle = get_option('options_go_loot_health_toggle');

        if ($xp_toggle){

            $rank = go_get_rank ( $user_id, $xp );
            $rank_num = $rank['rank_num'];
            $current_rank = $rank['current_rank'];
            $pts_to_rank_up_str = "Level $rank_num: $current_rank &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; XP:$xp ";

            $row[] = "{$pts_to_rank_up_str}";
        }
        if ($gold_toggle){
            $row[] = "{$gold}";
        }
        if ($health_toggle){
            $row[] = "{$health}";
        }
        $badges_toggle = get_option('options_go_badges_toggle');
        if ($badges_toggle) {
            $row[] = "{$badge_count}";
        }
        $output['aaData'][] = $row;
    }

    //$output['iTotalDisplayRecords'] =  count($output['aaData']);
    //global $go_debug;

    echo json_encode( $output );
    die();
}