<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 11/28/18
 * Time: 6:07 AM
 */

function go_section(){
    $section = $_GET['section'];
    if ($section == ""){
        $section = 0;
    }
    //if (is_array($sections) && count($sections) === 1){
    //    $section = $sections[0];
    //}

    return $section;
}

function go_sWhere($sColumns){

    $search_val = $_GET['search']['value'];
    $sWhere = "";
    if ( isset($search_val) && $search_val != "" )
    {
        $sWhere = "WHERE  ";
        $sWhere .= "";

        //search these columns
        for ( $i=0 ; $i<count($sColumns) ; $i++ )
        {
            $sWhere .= "`".$sColumns[$i]."` LIKE '%".esc_sql( $search_val )."%' OR ";
        }
        $sWhere = substr_replace( $sWhere, "", -3 );
        $sWhere .= '';
    }
    return $sWhere;
}

function go_sOrder($tab = null, $section = 0){
    if ($tab === null){
        return "";
    }

    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option('options_go_loot_health_toggle');
    $badges_toggle = get_option('options_go_badges_toggle');
    $groups_toggle = get_option('options_go_groups_toggle');

    $section = $_GET['section'];
    if ($section == "none"){
        $section = 0;
    }

    $order_dir = $_GET['order'][0]['dir'];
    $order_col = $_GET['order'][0]['column'];
    $order_var = "";
    //column 7 is not sortable (it's the link icons)
    $loot_sort = false;
    if ($order_col == 2){
        $order_var = 'go_sections';//first
    }
    if ($order_col == 3){
        $order_var = 'go_seats';//first
    }
    else if ($order_col == 4){
        $order_var = 'first_name';//first
    }
    else if ($order_col == 5){
        $order_var = 'last_name';//last
    }
    else if ($order_col == 6){
        $order_var = 'display_name';//display
    }
    else if ($tab == 'stats') {
        $loot_sort = true;
    }
    else if ($tab == 'store'){
        if ($order_col == 8){
            $order_var = 'action_id';//Time (ids are sequential)
        }
        else if ($order_col == 9){
            $order_var = 'post_title';
        }
        else {
            $order_col = $order_col - 2;//subtract the columns unique to the table to search the loot columns
            $loot_sort = true;
        }

    }
    else if ($tab == 'messages'){
        if ($order_col == 8){
            $order_var = 'action_id';//Time (ids are sequential)
        }
        else if ($order_col == 9){
            $order_var = 'post_title';
        }
        else {
            $order_col = $order_col - 2;//subtract the columns unique to the table to search the loot columns
            $loot_sort = true;
        }

    }
    else if ($tab == 'tasks'){
        if ($order_col == 8){
            $order_var = 'post_title';
        }
        else if ($order_col == 10){
            $order_var = 'start_time';
        }
        else if ($order_col == 11){
            $order_var = 'last_time';
        }
        else if ($order_col == 12){
            $order_var = 'timediff';
        }
        else if ($order_col == 13){
            $order_var = 'status';
        }
        else if ($order_col == 14){
            $order_var = 'status';
        }
        else if ($order_col == 14){
            $order_var = 'bonus_status';
        }
        else {
            $order_col = $order_col - 7;//subtract the columns unique to the table to search the loot columns
            $loot_sort = true;
        }
    }

    if($loot_sort) {
        if (!$xp_toggle){
            $order_col = $order_col + 2;
        }
        if ($order_col == 8) {
            $order_var = 'xp';
        }
        if ($order_col == 9) {
            $order_var = 'xp';
        }

        if (!$gold_toggle){
            $order_col = $order_col + 1;
        }
        if ($order_col == 10) {
            $order_var = 'gold';
        }

        if (!$health_toggle){
            $order_col = $order_col + 1;
        }
        if ($order_col == 11) {
            $order_var = 'health';
        }

        if (!$badges_toggle){
            $order_col = $order_col + 1;
        }
        if ($order_col == 12) {
            $order_var = 'length(go_badges)';
        }

        if (!$groups_toggle){
            $order_col = $order_col + 1;
        }
        if ($order_col == 13) {
            $order_var = 'length(go_groups)';
        }

    }

    $sOrder = "ORDER BY " . $order_var . " " . $order_dir;
    if (isset($order_var2)){
        $sOrder .= " , " . $order_var2 . " " . $order_dir;
    }
    return $sOrder;
}

function go_sType(){
    $unmatched = (isset($_GET['unmatched']) ? $_GET['unmatched'] : false);
    if ($unmatched === true || $unmatched == 'true' ) {
        $sType = "LEFT";//add switch for "show unmatched users" toggle
    }else{
        $sType = "INNER";
    }
    return $sType;
}

function go_sOn($action_type){
    $sOn = "";
    $date = $_GET['date'];

    if ($action_type == 'store' || $action_type == 'message') {
        if (isset($date) && $date != "") {
            $dates = explode(' - ', $date);
            $firstdate = $dates[0];
            $lastdate = $dates[1];
            $firstdate = date("Y-m-d", strtotime($firstdate));
            $lastdate = date("Y-m-d", strtotime($lastdate));
            $date = " AND ( DATE(t4.TIMESTAMP) BETWEEN '" . $firstdate . "' AND '" . $lastdate . "')";
        }
        $sOn = "AND (action_type = '" . $action_type . "') ";
        $sOn .= $date;


    }else if($action_type == 'tasks'){
        if (isset($date) && $date != "") {
            $dates = explode(' - ', $date);
            $firstdate = $dates[0];
            $lastdate = $dates[1];
            $firstdate = date("Y-m-d", strtotime($firstdate));
            $lastdate = date("Y-m-d", strtotime($lastdate));
            $date = " AND ( DATE(t4.last_time) BETWEEN '" . $firstdate . "' AND '" . $lastdate . "')";
        }
        $sOn .= $date;
    }
    return $sOn;
}

/**
 * The first rows on several tables.
 */
function go_start_row($action){
    $row = array();
    $user_id = $action['user_id'];
    $user_display_name = $action['display_name'];
    $user_firstname = $action['first_name'];
    $user_lastname = $action['last_name'];
    $sections = $action['go_sections'];
    //$the_section = intval($action['the_section']);
    $seats = $action['go_seats'];
    $website = $action['user_url'];
    $login = $action['user_login'];


    if(!empty($sections)){
        $sections = explode(',', $sections);
        $sections = go_print_term_list($sections);
    }

    $seats_list = '';
    if(!empty($seats)){
        $seats = explode(',', $seats);
        $first = true;
        foreach($seats as $seat){
            if (!$first){
                $seats_list .= '<br>';
            }
            $seat = substr($seat, 0, strpos($seat, "_"));
            $seats_list .= $seat;
            $first = false;
        }
    }



    ob_start();
    go_user_links($user_id, true, true, true, true, $website, $login);
    $links = ob_get_clean();


    $task_id = (isset($action['post_id']) ?  $action['post_id'] : null);
    //if ($task_id != null) {
    //    $task_id = "data-task='" . $task_id . "'";
    //}

    $check_box = "<input class='go_checkbox' type='checkbox' name='go_selected' data-uid='" . $user_id . "' data-task='". $task_id . "'/>";

    $row[] = "";
    $row[] = "{$check_box}";
    $row[] = "{$sections}";//user period
    $row[] = "{$seats_list}";//user seat
    $row[] = "{$user_firstname}";
    $row[] = "{$user_lastname}";
    $row[] = "{$user_display_name}";
    $row[] = "{$links}";

    return $row;

}

/**
 * Called by the ajax dataloaders.
 * @param $action
 * @return array
 */
function go_loot_columns_clipboard($action){
    $xp = $action['xp'];
    $gold = $action['gold'];
    $health = $action['health'];

    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option('options_go_loot_health_toggle');
    $row = array();
    if ($xp_toggle){
        $row[] = "{$xp}";
    }

    if ($gold_toggle){
        $row[] = "{$gold}";
    }

    if ($health_toggle){
        $row[] = "{$health}";
    }

    return $row;
}

function go_badges_list($badge_ids, $dir = null){
    $badges = "";

    $badge_ids = unserialize($badge_ids);

    if (!empty($badge_ids)) {
        $badges = go_print_term_list($badge_ids);
        if (!empty($badges)) {
            if ($dir == "badges+"){
                $badges = "Added: $badges";
                //$bg_links .= '+';
            }else if ($dir == "badges-"){
                $badges = "Removed: $badges";
            }
        }
    }
    return $badges;
}

function go_groups_list($group_ids, $dir = null){
    $groups = "";
    $group_ids = unserialize($group_ids);
    if (!empty($group_ids)) {
        $groups = go_print_term_list($group_ids);

        if (!empty($groups)) {
            if ($dir == "groups+") {
                $groups = "Added: $groups";
            } else if ($dir == "groups-") {
                $groups = "Removed: $groups";
            }
        }
    }
    return $groups;
}

/**
 * @param $badge_ids
 * @param $group_ids
 * @return string
 * NOT USING FOR NOW
 * OUTPUTS ICONS WITH TOOLTIPS FOR A SINGLE COLUMN
 * USING TWO COLUMNS WITH LISTS
 */
function go_badges_and_groups($badge_ids, $group_ids){
    $bg_links = '';
    $badges_toggle = get_option('options_go_badges_toggle');
    if ($badges_toggle) {
        $badges_names = array();
        $badge_ids = unserialize($badge_ids);
        $badges_name_sing = get_option('options_go_badges_name_singular');

        if (!empty($badge_ids)) {
            $badges_names_heading = "<b>" . $badges_name_sing . ": </b>";
            foreach ($badge_ids as $badge_id) {
                $term = get_term($badge_id, "go_badges");
                $badge_name = $term->name;
                $badges_names[] = $badge_name;
            }
            $badges_names = $badges_names_heading . implode(", " , $badges_names);
            $bg_links = '<i class="fas fa-certificate" aria-hidden="true"></i>';
        }else{
            $badges_names = "";
        }
    }else{
        $badges_names = "";
    }

    $groups_toggle = get_option('options_go_groups_toggle');
    if($groups_toggle) {
        $group_names = array();
        $group_ids = unserialize($group_ids);
        if (!empty($group_ids)) {
            $group_names_heading = "<b>Group: </b>";
            foreach ($group_ids as $group_id) {
                $term = get_term($group_id, "user_go_groups");
                $group_name = $term->name;
                $group_names[] = $group_name;
            }
            $group_names = $group_names_heading . implode(", ", $group_names);
            $bg_links = ' <i class="fas fa-users" aria-hidden="true"></i>';
        } else {
            $group_names = "";
        }
    }else {
        $group_names = "";
    }

    if (!empty($badges_names) && !empty($group_names) ) {
        $badges_names = $badges_names . "<br>" ;
    }
    $badges_names =  $badges_names . $group_names;

    $badges_and_groups = '<span class="tooltip" data-tippy-content="'. $badges_names .'">'. $bg_links . '</span>';

    return $badges_and_groups;
}

/**
 * Called by the ajax dataloaders.
 */
function go_clipboard_stats() {
    if ( ! current_user_can( 'manage_options' ) ) {
        die( -1 );
    }

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_clipboard_stats_' . get_current_user_id() );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_clipboard_stats' ) ) {
        echo "refresh";
        die( );
    }

    $xp_toggle = get_option('options_go_loot_xp_toggle');

    $seats_name = get_option('options_go_seats_name');

    echo '<div id="go_clipboard_wrapper" class="go_clipboard">';
    ?>

    <table id='go_clipboard_stats_datatable' class='pretty display'>
        <thead>
        <tr>
            <th></th>
            <th><input type="checkbox" onClick="go_toggle(this);"/></th>
            <th class="header">Section</th>
            <th class="header"><?php echo "$seats_name"; ?></a></th>
            <th class="header">First</th>
            <th class="header">Last</th>
            <th class="header">Display</th>
            <th class="header">Links</th>

            <?php
            if ($xp_toggle) {
                ?>
                <th class="header"><?php echo get_option('options_go_loot_xp_levels_name_singular'); ?></th>
                <?php
            }
            go_loot_headers();
                ?>
        </tr>
        </thead>
    </table></div>


    <?php
    die();
}

/**
 *
 */
function go_clipboard_stats_dataloader_ajax(){
    global $wpdb;
    $sColumns = array('first_name', 'last_name', 'display_name');
    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $badges_toggle = get_option('options_go_badges_toggle');
    $groups_toggle = get_option('options_go_groups_toggle');

    $section = go_section();// the section number from AJAX
    $sWhere = go_sWhere( $sColumns);
    $sLimit = '';
    if (isset($_GET['start']) && $_GET['length'] != '-1') {
        $sLimit = "LIMIT " . intval($_GET['start']) . ", " . intval($_GET['length']);
    }

    $sOrder = go_sOrder('stats', $section);

    $lTable = "{$wpdb->prefix}go_loot";
    $uTable = "{$wpdb->prefix}users";
    $umTable = "{$wpdb->prefix}usermeta";

    $sectionQuery = go_sectionQuery();
    $badgeQuery = go_badgeQuery();
    $groupQuery = go_groupQuery();

    $seat_key = go_prefix_key('go_seat');
    $section_key = go_prefix_key('go_section');
    $badge_key = go_prefix_key('go_badge');
    $group_key = go_prefix_key('go_group');

    $sQuery = "    
                    SELECT
                      t8.user_id, t1.*,
                      t3.display_name, t3.user_url, t3.user_login,
                      GROUP_CONCAT(CASE WHEN t2.meta_key = '$seat_key 'THEN meta_value END) as go_seats, 
                      GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END) as go_sections, 
                      GROUP_CONCAT(CASE WHEN t2.meta_key = '$badge_key' THEN meta_value END) as go_badges,
                      GROUP_CONCAT(CASE WHEN t2.meta_key = '$group_key' THEN meta_value END) as go_groups,
                      MAX(CASE WHEN t2.meta_key = 'first_name' THEN meta_value END) AS first_name,
                      MAX(CASE WHEN t2.meta_key = 'last_name' THEN meta_value END) AS last_name
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
                      $sWhere
                      $sOrder
                      $sLimit       
        ";

    //WHERE will always start with the current course
    //need to prefix blogID to go_section for multisite
    //RIGHT JOIN $umTable AS t5 ON t4.user_id = t5.user_id
    // WHERE t5.meta_key = 'go_badge'
    //                      GROUP BY t4.user_id

    //1.  Query uMeta for course, then attach loot table. --You now have all users and their loot for one course (WHERE meta_key = go_course and meta_value = #)
    //2. Add uMeta again for sections, badges, groups, and seats--you now have one array per user with data attached.
    //3. Add Seats--stored as sectionID_seat#, strip seat# off


    ////columns that will be returned
    $rResult = $wpdb->get_results($sQuery, ARRAY_A);

    $sQuery = "SELECT FOUND_ROWS()";

    $rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iFilteredTotal = $rResultFilterTotal [0];

    $sQuery = "
    SELECT COUNT(`uid`)
    FROM   $lTable
    
    ";

    $rResultTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iTotal = $rResultTotal [0];
    //$iFilteredTotal = number that match without limit;
    //$iTotalRecords = number in this table total (total store items/messages)
    $output = array("iTotalRecords" => $iTotal, "iTotalDisplayRecords" => $iFilteredTotal, "aaData" => array());

    foreach($rResult as $action){//output a row for each action

        //The message content
        $row = go_start_row($action);
        $user_id = $action['uid'];
        $xp = $action['xp'];


        $rank = go_get_rank ( $user_id, $xp );
        $current_rank_name = $rank['current_rank'];
        if (!empty($current_rank_name )){
            $current_rank_name = ": " . $current_rank_name;
        }
        $rank_num = $rank['rank_num'];

        //add to output
        if ($xp_toggle) {
            $row[] = "{$rank_num}{$current_rank_name}";
        }

        $go_loot_columns = go_loot_columns_clipboard($action);
        $row = array_merge($row, $go_loot_columns);

        if($badges_toggle){
            $badge_ids = $action['go_badges'];
            $badge_list = go_print_term_list($badge_ids);
            $row[] = $badge_list;
        }

        if($groups_toggle){
            $group_ids = $action['go_groups'];
            $group_list = go_print_term_list($group_ids);
            $row[] = $group_list;
        }

        $output['aaData'][] = $row;
    }

    //$output['iTotalDisplayRecords'] =  count($output['aaData']);

    global $go_debug;
    if($go_debug) {
        go_total_query_time();
    }

    echo json_encode( $output );
    die();
}

/**
 *
 */
function go_clipboard_store() {

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_clipboard_store' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_clipboard_store' ) ) {
        echo "refresh";
        die( );
    }

    $seats_name = get_option( 'options_go_seats_name' );

    echo "<div id='go_clipboard_store' class='go_datatables'><table id='go_clipboard_store_datatable' class='pretty display'>
    <thead>
    <tr>
        <th></th>
        <th><input type=\"checkbox\" onClick=\"go_toggle(this);\" /></th>
        <th class=\"header\">Section</th>
        <th class=\"header\">" . $seats_name . "</a></th>
        <th class=\"header\">First</th>
        <th class=\"header\">Last</th>
        <th class=\"header\">Display</th>
        <th class=\"header\">Links</th>
        <th class='header'>Time</th>
        <th class='header'>Item</th>";


    go_loot_headers();
    ?>
        </tr>
        </thead>

        </table></div>
    <?php
    die();
}

/**
 * go_clipboard_store_dataloader_ajax
 * Called for Server Side Processing from the JS
 *
 */
function go_clipboard_store_dataloader_ajax(){
    global $wpdb;

    //Get the search value
    $section = go_section();

    $sColumns = array('first_name', 'last_name', 'display_name', 'result', 'post_title');
    $sWhere = go_sWhere($sColumns);

    $sLimit = '';
    if (isset($_GET['start']) && $_GET['length'] != '-1') {
        $sLimit = "LIMIT " . intval($_GET['start']) . ", " . intval($_GET['length']);
    }

    $sOrder = go_sOrder('store', $section);
    $sType = go_sType();

    $pTable = "{$wpdb->prefix}posts";
    $lTable = "{$wpdb->prefix}go_loot";
    $aTable = "{$wpdb->prefix}go_actions";
    $uTable = "{$wpdb->prefix}users";
    $umTable = "{$wpdb->prefix}usermeta";

    $sOn = go_sOn('store');
    //add store items to On statement
    $store_items = $_GET['store_item'];
    if ( isset($store_items) && !empty($store_items) )
    {
        $sOn .= " AND (";
        for ( $i=0 ; $i<count($store_items) ; $i++ )
        {
            $store_item = intval($store_items[$i]);
            $sOn .= "t4.source_id = ".$store_item." OR ";
        }
        $sOn = substr_replace( $sOn, "", -3 );
        $sOn .= ")";
    }
    //Index column
    $sIndexColumn = "id";

    $sectionQuery = go_sectionQuery();
    $badgeQuery = go_badgeQuery();
    $groupQuery = go_groupQuery();

    $seat_key = go_prefix_key('go_seat');
    $section_key = go_prefix_key('go_section');
    $badge_key = go_prefix_key('go_badge');
    $group_key = go_prefix_key('go_group');

    $sQuery = "    
                  SELECT SQL_CALC_FOUND_ROWS
                    t9.*
                  FROM (
                      SELECT
                        t6.post_title, t9.*, t4.id AS action_id, t4.source_id, t4.action_type, t4.TIMESTAMP, t4.result, t4.xp, t4.gold, t4.health, t4.badges, t4.groups
                      FROM (
                        SELECT
                          t8.user_id,
                          t3.display_name, t3.user_url, t3.user_login,
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$seat_key 'THEN meta_value END) as go_seats, 
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END) as go_sections, 
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$badge_key' THEN meta_value END) as go_badges,
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$group_key' THEN meta_value END) as go_groups,
                          MAX(CASE WHEN t2.meta_key = 'first_name' THEN meta_value END) AS first_name,
                          MAX(CASE WHEN t2.meta_key = 'last_name' THEN meta_value END) AS last_name
                        FROM(
                          SELECT t6.user_id
                          FROM (
                            SELECT t4.user_id
                            FROM (
                                SELECT t2.*
                                FROM(
                                  SELECT t1.uid AS user_id
                                  FROM $lTable AS t1
                                  ) AS t2
                                $sectionQuery
                                ) AS t4
                            $badgeQuery
                            ) AS t6
                          $groupQuery
                          )AS t8
                        LEFT JOIN $umTable AS t2 ON t8.user_id = t2.user_id
                        LEFT JOIN $uTable AS t3 ON t8.user_id = t3.ID
                        GROUP BY t8.user_id 
                        ) AS t9
                      $sType JOIN $aTable AS t4 ON t9.user_id = t4.uid $sOn
                      $sType JOIN $pTable AS t6 ON t4.source_id = t6.ID
                      $sWhere
                  ) AS t9
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

    $totalWhere = " WHERE (action_type = 'store') ";
    $sQuery = "
    SELECT COUNT(`" . $sIndexColumn . "`)
    FROM   $aTable
    $totalWhere
    ";

    $rResultTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iTotal = $rResultTotal [0];
    //$iFilteredTotal = number that match without limit;
    //$iTotalRecords = number in this table total (total store items/messages)
    $output = array("iTotalRecords" => $iTotal, "iTotalDisplayRecords" => $iFilteredTotal, "aaData" => array());


    foreach($rResult as $action){//output a row for each action
        $row = go_start_row($action);
        //The message content
        $TIMESTAMP = $action['TIMESTAMP'];
        $badge_ids = $action['badges'];
        $group_ids = $action['groups'];
        $title = $action['post_title'];



        //$unix_time = strtotime($TIMESTAMP);
        $time = go_clipboard_time($TIMESTAMP);

        $row[] = "{$time}";
        $row[] = "{$title}";

        $go_loot_columns = go_loot_columns_clipboard($action);
        $row = array_merge($row, $go_loot_columns);

        $badges_toggle = get_option('options_go_badges_toggle');
        if ($badges_toggle) {
            $badges = go_badges_list($badge_ids);
            $row[] = $badges;
        }

        $groups_toggle = get_option('options_go_groups_toggle');
        if ($groups_toggle) {
            $groups = go_groups_list($group_ids);

            $row[] = $groups;
        }



        $output['aaData'][] = $row;
    }

    //$output['iTotalDisplayRecords'] =  count($output['aaData']);
    global $go_debug;
    if($go_debug) {
        go_total_query_time();
    }

    echo json_encode( $output );
    die();
}

/**
 *
 */
function go_clipboard_messages() {

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }
    //check_ajax_referer( 'go_clipboard_messages' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_clipboard_messages' ) ) {
        echo "refresh";
        die( );
    }

    $xp_abbr = get_option( "options_go_loot_xp_abbreviation" );
    $gold_abbr = get_option( "options_go_loot_gold_abbreviation" );
    $health_abbr = get_option( "options_go_loot_health_abbreviation" );

    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option('options_go_loot_health_toggle');

    $badges_toggle = get_option('options_go_badges_toggle');
    $groups_toggle = get_option('options_go_groups_toggle');

    $seats_name = get_option( 'options_go_seats_name' );

    echo "<div id='go_clipboard_messages' class='go_datatables'><table id='go_clipboard_messages_datatable' class='pretty display'>
    <thead>
    <tr>
        <th></th>
        <th><input type=\"checkbox\" onClick=\"go_toggle(this);\" /></th>
        <th class=\"header\">Section</th>
        <th class=\"header\">" . $seats_name . "</a></th>
        <th class=\"header\">First</th>
        <th class=\"header\">Last</th>
        <th class=\"header\">Display</th>
        <th class=\"header\">Links</th>
        <th class='header'>Time</th>
        <th class='header'>Message</th>";


    go_loot_headers();
    ?>
    </tr>
    </thead>

    </table></div>
    <?php

    die();
}

/**
 * go_clipboard_messages_dataloader_ajax
 * Called for Server Side Processing from the JS
 */
function go_clipboard_messages_dataloader_ajax(){
    global $wpdb;

    //Get the search value
    //$search_val = $_GET['search']['value'];
    $section = go_section();

    $sColumns = array('first_name', 'last_name', 'display_name', 'result');
    $sWhere = go_sWhere($sColumns);

    $sLimit = '';
    if (isset($_GET['start']) && $_GET['length'] != '-1') {
        $sLimit = "LIMIT " . intval($_GET['start']) . ", " . intval($_GET['length']);
    }

    $sOrder = go_sOrder('messages', $section);

    $sType = go_sType();
    $sOn = go_sOn('message');
    //Index column
    $sIndexColumn = "id";

    $lTable = "{$wpdb->prefix}go_loot";
    $aTable = "{$wpdb->prefix}go_actions";
    $uTable = "{$wpdb->prefix}users";
    $umTable = "{$wpdb->prefix}usermeta";

    $sectionQuery = go_sectionQuery();
    $badgeQuery = go_badgeQuery();
    $groupQuery = go_groupQuery();

    $seat_key = go_prefix_key('go_seat');
    $section_key = go_prefix_key('go_section');
    $badge_key = go_prefix_key('go_badge');
    $group_key = go_prefix_key('go_group');

    $sQuery = "    
                  SELECT SQL_CALC_FOUND_ROWS
                    t9.*
                  FROM (
                      SELECT
                        t9.*, t4.id AS action_id, t4.source_id, t4.action_type, t4.TIMESTAMP, t4.result, t4.xp, t4.gold, t4.health, t4.badges, t4.groups
                      FROM (
                        SELECT
                          t8.user_id,
                          t3.display_name, t3.user_url, t3.user_login,
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$seat_key 'THEN meta_value END) as go_seats, 
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END) as go_sections, 
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$badge_key' THEN meta_value END) as go_badges,
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$group_key' THEN meta_value END) as go_groups,
                          MAX(CASE WHEN t2.meta_key = 'first_name' THEN meta_value END) AS first_name,
                          MAX(CASE WHEN t2.meta_key = 'last_name' THEN meta_value END) AS last_name
                        FROM(
                          SELECT t6.user_id
                          FROM (
                            SELECT t4.user_id
                            FROM (
                                SELECT t2.*
                                FROM(
                                  SELECT t1.uid AS user_id
                                  FROM $lTable AS t1
                                  ) AS t2
                                $sectionQuery
                                ) AS t4
                            $badgeQuery
                            ) AS t6
                          $groupQuery
                          )AS t8
                        LEFT JOIN $umTable AS t2 ON t8.user_id = t2.user_id
                        LEFT JOIN $uTable AS t3 ON t8.user_id = t3.ID
                        GROUP BY t8.user_id 
                        ) AS t9
                      $sType JOIN $aTable AS t4 ON t9.user_id = t4.uid $sOn
                      $sWhere
                  ) AS t9
                  $sOrder
                  $sLimit     
        ";

    /*
     * $sType JOIN $aTable AS t4 ON t5.uid = t4.uid $sOn
          $sWhere
          ) AS t9
          $sOrder
          $sLimit
     */


    ////columns that will be returned
    $rResult = $wpdb->get_results($sQuery, ARRAY_A);


    $sQuery = "SELECT FOUND_ROWS()";

    $rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iFilteredTotal = $rResultFilterTotal [0];

    $totalWhere = " WHERE (action_type = 'message') ";
    $sQuery = "
    SELECT COUNT(`" . $sIndexColumn . "`)
    FROM   $aTable
    $totalWhere
    ";

    $rResultTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iTotal = $rResultTotal [0];
    //$iFilteredTotal = number that match without limit;
    //$iTotalRecords = number in this table total (total store items/messages)
    $output = array("iTotalRecords" => $iTotal, "iTotalDisplayRecords" => $iFilteredTotal, "aaData" => array());


    foreach($rResult as $action){//output a row for each message

        //The message content
        $row = go_start_row($action);
        $TIMESTAMP = $action['TIMESTAMP'];
        $badge_ids = $action['badges'];
        $group_ids = $action['groups'];
        $result = $action['result'];

        //unserialize the message and set the results
        $result_array = unserialize($result);
        $title = $result_array[0];
        $message = $result_array[1];







        if (!empty($message)) {
            //$title = "<span class='tooltip' ><span class='tooltiptext'>{$message}</span>$title</span>";
            $title = '<span class="tooltip" data-tippy-content="'. $message .'">'. $title . '</span>';
        }

        $time  = go_clipboard_time($TIMESTAMP);

        $row[] = "{$time}";
        $row[] = "{$title}";

        $go_loot_columns = go_loot_columns_clipboard($action);
        $row = array_merge($row, $go_loot_columns);

        $badges_toggle = get_option('options_go_badges_toggle');
        if ($badges_toggle) {

            $dir = $result_array[2];
            $badges = go_badges_list($badge_ids, $dir);
            $row[] = $badges;
        }

        $groups_toggle = get_option('options_go_groups_toggle');
        if ($groups_toggle) {

            $dir = $result_array[3];
            $groups = go_groups_list($group_ids, $dir);

            $row[] = $groups;
        }

        $output['aaData'][] = $row;
    }

    global $go_debug;
    if($go_debug) {
        go_total_query_time();
    }

    echo json_encode( $output );
    die();
}

/**
 *
 */
function go_clipboard_activity() {
    if ( ! current_user_can( 'manage_options' ) ) {
        die( -1 );
    }

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_clipboard_activity_' . get_current_user_id() );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_clipboard_activity' ) ) {
        echo "refresh";
        die( );
    }

    $seats_name = get_option( 'options_go_seats_name' );

    ?>

    <?php
    echo '<div id="go_clipboard_activity_wrapper" class="go_clipboard">';
    ?>
    <table id='go_clipboard_activity_datatable' class='pretty display'>
        <thead>
        <tr>
            <th>
            </th><th><input type="checkbox" onClick="go_toggle(this);" /></th>
            <th class="header">Section</th>
            <th class="header"><?php echo "$seats_name"; ?></a></th>
            <th class="header">First</th>
            <th class="header">Last</th>
            <th class="header">Display</th>
            <th class="header">Links</th>
            <th class='header'>Task</th>
            <th class='header'>Actions</th>
            <th class='header'>Start</th>
            <th class='header'>Last</th>
            <th class='header'>Time On</th>
            <th class='header'>Status</th>
            <th class='header'>Done</th>
            <th class='header'>Bonus</th>
            <?php
            go_loot_headers();
            ?>

        </tr>
        </thead>
    </table></div>
    <?php
    die();
}

/**
 *
 */
function go_clipboard_activity_dataloader_ajax(){
    global $wpdb;
    $section = go_section();

    $sColumns = array('first_name', 'last_name', 'display_name', 'post_title');
    $sWhere = go_sWhere( $sColumns);

    $sLimit = '';
    if (isset($_GET['start']) && $_GET['length'] != '-1') {
        $sLimit = "LIMIT " . intval($_GET['start']) . ", " . intval($_GET['length']);
    }

    $sOrder = go_sOrder('tasks', $section);

    $sType = go_sType();
    $sOn = go_sOn('tasks');
    //add store items to On statement
    $tasks = $_GET['tasks'];
    if ( isset($tasks) && !empty($tasks) )
    {
        $sOn .= " AND (";
        for ( $i=0 ; $i<count($tasks) ; $i++ )
        {
            $task = intval($tasks[$i]);
            $sOn .= "t4.post_id = ".$task." OR ";
        }
        $sOn = substr_replace( $sOn, "", -3 );
        $sOn .= ")";
    }

    //Index column
    $sIndexColumn = "id";


    $lTable = "{$wpdb->prefix}go_loot";
    $aTable = "{$wpdb->prefix}go_actions";
    $uTable = "{$wpdb->prefix}users";
    $umTable = "{$wpdb->prefix}usermeta";
    $tTable = "{$wpdb->prefix}go_tasks";
    $pTable = "{$wpdb->prefix}posts";

    $sectionQuery = go_sectionQuery();
    $badgeQuery = go_badgeQuery();
    $groupQuery = go_groupQuery();

    $seat_key = go_prefix_key('go_seat');
    $section_key = go_prefix_key('go_section');
    $badge_key = go_prefix_key('go_badge');
    $group_key = go_prefix_key('go_group');

    $sQuery = "    
                  SELECT SQL_CALC_FOUND_ROWS
                    t9.*
                  FROM (
                      SELECT
                        t6.post_title, t9.*, t4.post_id, t4.status, t4.bonus_status, t4.xp, t4.gold, t4.health, t4.start_time, t4.last_time, t4.badges, t4.groups,
            TIMESTAMPDIFF(SECOND, t4.start_time, t4.last_time ) AS timediff
                      FROM (
                        SELECT
                          t8.user_id,
                          t3.display_name, t3.user_url, t3.user_login,
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$seat_key 'THEN meta_value END) as go_seats, 
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END) as go_sections, 
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$badge_key' THEN meta_value END) as go_badges,
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$group_key' THEN meta_value END) as go_groups,
                          MAX(CASE WHEN t2.meta_key = 'first_name' THEN meta_value END) AS first_name,
                          MAX(CASE WHEN t2.meta_key = 'last_name' THEN meta_value END) AS last_name
                        FROM(
                          SELECT t6.user_id
                          FROM (
                            SELECT t4.user_id
                            FROM (
                                SELECT t2.*
                                FROM(
                                  SELECT t1.uid AS user_id
                                  FROM $lTable AS t1
                                  ) AS t2
                                $sectionQuery
                                ) AS t4
                            $badgeQuery
                            ) AS t6
                          $groupQuery
                          )AS t8
                        LEFT JOIN $umTable AS t2 ON t8.user_id = t2.user_id
                        LEFT JOIN $uTable AS t3 ON t8.user_id = t3.ID
                        GROUP BY t8.user_id 
                        ) AS t9
                      $sType JOIN $tTable AS t4 ON t9.user_id = t4.uid $sOn
                      $sType JOIN $pTable AS t6 ON t4.post_id = t6.ID
                      $sWhere
                  ) AS t9
                  $sOrder
                  $sLimit     
        ";

    /*
     * ) AS t5
          $sType JOIN $tTable AS t4 ON t5.uid = t4.uid $sOn
          $sType JOIN $pTable AS t6 ON t4.post_id = t6.ID
          $sWhere
          ) AS t9
          $sOrder
          $sLimit
     */
    ////columns that will be returned
    $rResult = $wpdb->get_results($sQuery, ARRAY_A);

    //go_write_log("ERROR: ");
    //go_write_log($wpdb->print_error());
    // go_write_log($rResult);
    $sQuery2 = "SELECT FOUND_ROWS()";

    $rResultFilterTotal = $wpdb->get_results($sQuery2, ARRAY_N);

    $iFilteredTotal = $rResultFilterTotal [0];

    //$totalWhere = " WHERE (action_type = 'message') ";
    $sQuery3 = "
    SELECT COUNT(`" . $sIndexColumn . "`)
    FROM   $tTable
    ";

    $rResultTotal = $wpdb->get_results($sQuery3, ARRAY_N);

    $iTotal = $rResultTotal [0];
    //$iFilteredTotal = number that match without limit;
    //$iTotalRecords = number in this table total (total store items/messages)
    $output = array("iTotalRecords" => $iTotal, "iTotalDisplayRecords" => $iFilteredTotal, "aaData" => array());


    foreach($rResult as $action){//output a row for each message

        //The message content
        $row = go_start_row($action);
        $row[] = $action['post_title'];

        $task_id = (isset($action['post_id']) ?  $action['post_id'] : null);
        //if ($task_id != null) {
        //  $task_id = "data-task='" . $task_id . "'";
        //}

        $user_id = $action['user_id'];
        $row[] = '<a href="javascript:;" class="go_blog_user_task" data-UserId="'.$user_id.'" onclick="go_blog_user_task('.$user_id.', '.$task_id.');"><i style="padding: 0px 10px;" class="fas fa-search" aria-hidden="true"></i></a><a><i data-uid="' . $user_id . '" data-task="'. $task_id . '" style="padding: 0px 10px;" class="go_reset_task_clipboard fa fa-times-circle" aria-hidden="true"></a>';//actions

        $start = $action['start_time'];
        $row[] = go_clipboard_time($start);
        $last = $action['last_time'];
        $row[] = go_clipboard_time($last);
        $diff = $action['timediff'];
        $hours = 0;
        $minutes = 0;
        $seconds = 0;
        if (!empty($diff)) {
            //$diff = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $diff);
            //sscanf($diff, "%d:%d:%d", $hours, $minutes, $seconds);
            //$time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
            //$diff = go_time_on_task($time_seconds, false);
            $diff = go_time_on_task($diff, false);
        }
        $row[] = $diff;


        $go_post_data = go_post_data($action['post_id']);
        //$the_title = $go_post_data[0];
        //$status = $go_post_data[1];
        //$task_link = $go_post_data[2];
        $custom_fields = $go_post_data[3];

        $bonus_count = 0;
        if ($action['status'] >= 0) {
            $stage_count = (isset($custom_fields['go_stages'][0]) ? $custom_fields['go_stages'][0] : null);
            $bonus_count = (isset($custom_fields['go_bonus_limit'][0]) ? $custom_fields['go_bonus_limit'][0] : null);
            $row[] = strval($action['status']) . " / " . strval($stage_count);

            if (($action['status'] >= $stage_count) && !empty($action['status'])){
                $complete = "<i class=\"fa fa-check\" aria-hidden=\"true\"></i>";
            }else{
                $complete = "";
            }
            $row[] = $complete;

        }
        else if($action['status'] == -2){
            $row[] = "reset";
            $row[] = "";
        }
        else if ($action['status'] == -1){
            $row[] = "abandoned";
            $row[] = "";
        }

        if ($action['bonus_status'] > 0) {
            $row[] = strval($action['bonus_status']) . " / " . strval($bonus_count);
        }else{
            $row[] = "";
        }

        $go_loot_columns = go_loot_columns_clipboard($action);
        $row = array_merge($row, $go_loot_columns);

        $badge_ids = $action['badges'];
        $group_ids = $action['groups'];
        $badges_toggle = get_option('options_go_badges_toggle');
        if ($badges_toggle) {
            $badges = go_badges_list($badge_ids);
            $row[] = $badges;
        }

        $groups_toggle = get_option('options_go_groups_toggle');
        if ($groups_toggle) {
            $groups = go_groups_list($group_ids);

            $row[] = $groups;
        }

        $output['aaData'][] = $row;
    }
    //go_write_log("output: ");
    //go_write_log($output);
    //go_total_query_time();

    echo json_encode( $output );
    die();
}
