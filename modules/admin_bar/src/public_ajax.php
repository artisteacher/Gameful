<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-05-12
 * Time: 20:13
 */



function go_stats_uWhere_values(){
    //CREATE THE QUERY
    //CREATE THE USER WHERE STATEMENT
    //check the drop down filters only
    //Query 1:
    //WHERE (uWhere)
    //User_meta by section_id from the drop down filter
    //loot table by badge_id from drop down filter
    //and group_id from the drop down filter.

    $section = $_GET['section'];
    $badge = $_GET['badge'];
    $group = $_GET['group'];


    $uWhere = "";
    if ((isset($section) && $section != "" ) || (isset($badge) && $badge != "") || (isset($group) && $group != "") )
    {
        $uWhere = "HAVING ";
        $uWhere .= " (";
        $first = true;

        //add search for section number
        if  (isset($section) && $section != "") {
            //search for badge IDs
            $sColumns = array('section_0', 'section_1', 'section_2', 'section_3', 'section_4', 'section_5', );
            $uWhere .= " (";
            $first = false;

            /*
            $search_array = $section;

            if ( isset($search_array) && !empty($search_array) )
            {
                for ( $i=0 ; $i<count($search_array) ; $i++ )
                {
                    for ($i2 = 0; $i2 < count($sColumns); $i2++) {
                        $uWhere .= "`" . $sColumns[$i2] . "` = " . intval($search_array[$i]) . " OR ";
                    }
                }
            }
            */
            for ($i = 0; $i < count($sColumns); $i++) {
                $uWhere .= "`" . $sColumns[$i] . "` = " . intval($section) . " OR ";
            }
            $uWhere = substr_replace( $uWhere, "", -3 );
            $uWhere .= ")";
        }

        if  (isset($badge) && $badge != "") {
            //search for badge IDs
            $sColumn = 'badges';
            if ($first == false) {
                $uWhere .= " AND (";
            }else {
                $uWhere .= " (";
                $first = false;
            }
            $search_var = $badge;
            $uWhere .= "`" . $sColumn . "` LIKE '%\"" . esc_sql($search_var). "\"%'";
            $uWhere .= ')';
        }

        if  (isset($group)  && $group != "") {
            //search for group IDs
            $sColumn = 'groups';
            if ($first == false) {
                $uWhere .= " AND (";
            }else {
                $uWhere .= " (";
                $first = false;
            }
            $search_var = $group;
            $uWhere .= "`" . $sColumn . "` LIKE '%\"" . esc_sql($search_var). "\"%'";
            $uWhere .= ')';
        }
        $uWhere .= "AND wp_capabilities NOT LIKE '%administrator%')";
    }else{
        $uWhere .= "HAVING (wp_capabilities NOT LIKE '%administrator%')";
    }
    return $uWhere;
}


function go_stats_leaderboard_dataloader_ajax(){
    global $wpdb;
    $current_id = get_current_user_id();
    $is_admin = go_user_is_admin($current_id);

    //set the last searched leaderboard as an user option
    if(is_user_logged_in()) {
        $section = $_GET['section'];
        //$badge = $_GET['badge'];
        $group = $_GET['group'];
        update_user_option( $current_id, 'go_leaderboard_section', $section );

        update_user_option( $current_id, 'go_leaderboard_group', $group );
    }

    //$section = go_section();
    $uWhere = go_stats_uWhere_values();
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
    $umTable = "{$wpdb->prefix}usermeta";
    $uTable = "{$wpdb->prefix}users";
    $sColumn = "{$wpdb->prefix}capabilities";

    $sQuery = "
          
      SELECT SQL_CALC_FOUND_ROWS
        t5.*
      FROM (
              SELECT
              t1.*,
              MAX(CASE WHEN t2.meta_key = 'first_name' THEN meta_value END) AS first_name,
              MAX(CASE WHEN t2.meta_key = 'last_name' THEN meta_value END) AS last_name,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat' THEN meta_value END) AS num_section,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat_0_user-section' THEN meta_value END)  AS section_0,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat_1_user-section' THEN meta_value END) AS section_1,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat_2_user-section' THEN meta_value END) AS section_2,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat_3_user-section' THEN meta_value END) AS section_3,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat_4_user-section' THEN meta_value END) AS section_4,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat_5_user-section' THEN meta_value END) AS section_5,
              MAX(CASE WHEN t2.meta_key = 'wp_capabilities' THEN meta_value END) AS wp_capabilities,
              t3.display_name, t3.user_url, t3.user_login
              FROM $lTable AS t1 
              LEFT JOIN $umTable AS t2 ON t1.uid = t2.user_id
              LEFT JOIN $uTable AS t3 ON t2.user_id = t3.ID
              GROUP BY t1.id
              $uWhere
          ) AS t5
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

    $sQuery = "
     SELECT COUNT(*)
     FROM( 
      SELECT 
          MAX(CASE WHEN t2.meta_key = '$sColumn' THEN meta_value END) AS capabilities
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
        $user_display_name = $action['display_name'];
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
        if ($full_name_toggle || $is_admin){
            $user_fullname = $user_firstname.' '.$user_lastname;
        }

        $num++;

        ob_start();
        go_user_links($user_id, true, true, true, true, true, true);
        $links = ob_get_clean();

        $row[] = "{$num}";
        if ($full_name_toggle || $is_admin){
            $row[] = $user_fullname;
        }
        $row[] = "{$user_display_name}";
        $row[] = "{$links}";//user period

        $xp_toggle = get_option('options_go_loot_xp_toggle');
        $gold_toggle = get_option('options_go_loot_gold_toggle');
        $health_toggle = get_option('options_go_loot_health_toggle');

        if ($xp_toggle){
            $row[] = "{$xp}";
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
    global $go_debug;

    echo json_encode( $output );
    die();
}