<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 11/28/18
 * Time: 6:07 AM
 */



function go_sHaving($sColumns){

    $search_val = $_GET['search']['value'];
    $sHaving = "";
    if ( isset($search_val) && $search_val != "" )
    {
        $sHaving = "HAVING ";
        //$sWhere .= "";

        //search these columns
        for ( $i=0 ; $i<count($sColumns) ; $i++ )
        {
            $sHaving .= "`".$sColumns[$i]."` LIKE '%".esc_sql( $search_val )."%' OR ";
        }
        $sHaving = substr_replace( $sHaving, "", -4 );
        //$sWhere .= '';
    }
    return $sHaving;
}

function go_alpha_sort($a, $b)
{
    return strcmp($a->name, $b->name);
}


function go_sOrder($tab = null){
    if ($tab === null){
        return "";
    }

    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option('options_go_loot_health_toggle');
    $badges_toggle = get_option('options_go_badges_toggle');
    $groups_toggle = get_option('options_go_groups_toggle');

    /*
    $section = $_GET['section'];
    if ($section == "none"){
        $section = 0;
    }*/

    $order_dir = $_GET['order'][0]['dir'];
    //$order_dir = '';
    $order_col = $_GET['order'][0]['column'];
    $order_var = "";
    //column 7 is not sortable (it's the link icons)
    $loot_sort = false;
    if ($order_col == 2){
        $terms = get_terms( array(
            'taxonomy' => 'user_go_sections',
            'hide_empty' => false,
        ) );
        usort($terms, "go_alpha_sort");
        if(!empty($terms)){
            $order_var = "(CASE";
            $i =0;
            foreach($terms as $term){
                $i++;
                $term_id = $term->term_id;

                $order_var .= " WHEN  go_section = '".$term_id."' THEN ".$i;
            }
            $order_var .= ' END)';
            //$order_dir = '';
        }
        else {
            $order_var = 'go_section';//sections
        }
        //$order_var = 'go_sections';
    }
    if ($order_col == 3){
        //$order_var = 'go_seats';//seat
        $order_var =  'go_seats';
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
    else if ($tab == 'messages' || $tab == 'attendance'){
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
    $user_id = get_current_user_id();
    //$unmatched_saved = get_user_meta($user_id, 'unmatched_toggle', true);//saved value
   // $unmatched = (isset($_GET['unmatched']) ? $_GET['unmatched'] : $unmatched_saved);//sent value



    //if ($unmatched === true || $unmatched == 'true' ) {
        $sType = "LEFT";//add switch for "show unmatched users" toggle
   // }else{
     //   $sType = "INNER";
  //  }
    return $sType;
}

function go_sOn($action_type){
    $sOn = "";
    $date = $_GET['date'];

    if ($action_type == 'store' || $action_type == 'message' || $action_type == 'attendance') {
        if (isset($date) && $date != "") {
            $dates = explode(' - ', $date);
            $firstdate = $dates[0];
            $lastdate = $dates[1];
            $firstdate = date("Y-m-d", strtotime($firstdate));
            $lastdate = date("Y-m-d", strtotime($lastdate));
            $date = " AND ( DATE(t4.TIMESTAMP) BETWEEN '" . $firstdate . "' AND '" . $lastdate . "')";
        }
        if($action_type == 'message'){
            $sOn = "AND (action_type = 'message' OR action_type = 'note') ";
        }else {
            $sOn = "AND (action_type = '" . $action_type . "') ";
        }
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

    $user_display_name = $action['site_name'];
    if(empty($user_display_name)){
        $user_display_name = $action['nickname'];
        if(empty($user_display_name)){
            $user_display_name = $action['display_name'];
        }
    }
    $user_firstname = $action['first_name'];
    $user_lastname = $action['last_name'];
    $sections = $action['go_sections'];
    //$the_section = intval($action['the_section']);
    $seats = $action['go_seats'];

    $website = $action['site_url'];
    if(empty($website)){
        $website = $action['user_url'];
    }

    $login = $action['user_login'];


    if(!empty($sections)){
        $sections = explode(',', $sections);
        $sections = array_unique($sections);
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
           // $seat = substr($seat, 0, strpos($seat, "_"));
            $seats_list .= $seat;
            $first = false;
        }
    }



    ob_start();
    go_user_links($user_id, true, true, true, true, $website);
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
            if ($dir == "badges+" && !empty($badge_ids)){
                $badges = "Added: $badges";
                //$bg_links .= '+';
            }else if ($dir == "badges-" && !empty($badge_ids)){
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
            if ($dir == "groups+" && !empty($groups)) {
                $groups = "Added: $groups";
            } else if ($dir == "groups-" && !empty($groups)) {
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
    if ( ! go_user_is_admin() ) {
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

    //Move this to a database maintenance tool
    //but add function to remove user from Loot table on removing from site
    //it doesn't need to run everytime
    //Remove users that don't exist from Loot Table
    global $wpdb;
    $args = array(
        'fields'       => 'ID',
    );
    $user_ids = get_users( $args );
    global $wpdb;
    $lTable = "{$wpdb->prefix}go_loot";
    $sQuery = "    
                   SELECT t1.uid AS user_id
                                  FROM $lTable AS t1       
        ";

    $rResult = $wpdb->get_results($sQuery, ARRAY_A);
    $loot_ids = array_column($rResult, 'user_id');

    $remove_ids = array_diff($loot_ids, $user_ids);

    foreach ($remove_ids as $remove_id){

        $wpdb->delete( $lTable, array( 'uid' => $remove_id ) );
    }

    //END MAINTENANCE TOOL

    global $wpdb;
    $sColumns = array('first_name', 'last_name', 'site_name');
    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $badges_toggle = get_option('options_go_badges_toggle');
    $groups_toggle = get_option('options_go_groups_toggle');

    $sHaving = go_sHaving( $sColumns);
    $sLimit = '';
    if (isset($_GET['start']) && $_GET['length'] != '-1') {
        $sLimit = "LIMIT " . intval($_GET['start']) . ", " . intval($_GET['length']);
    }

    $sOrder = go_sOrder('stats');

    $lTable = "{$wpdb->prefix}go_loot";
    if(is_gameful()) {
        $main_site_id = get_network()->site_id;
        switch_to_blog($main_site_id);
    }
    $uTable = "{$wpdb->prefix}users";
    $umTable = "{$wpdb->prefix}usermeta";

    if(is_gameful()) {
        restore_current_blog();
    }
    $blog_id = get_current_blog_id();
    $sectionQuery = go_sectionQuery();
    $badgeQuery = go_badgeQuery();
    $groupQuery = go_groupQuery();

    $seat_key = go_prefix_key('go_seat');
    $section_key = go_prefix_key('go_section');
    $badge_key = go_prefix_key('go_badge');
    $group_key = go_prefix_key('go_group');

    $caps_key = "{$wpdb->prefix}capabilities";

    $site_display_name_key = go_prefix_key('go_nickname');
    $site_url_name_key = go_prefix_key('go_website');


    $section = (isset($_GET['section']) ?  $_GET['section'] : null);
    //if filtered by period, only get the seat for that period
    if(!empty($section)){
        $get_seat = "CAST(GROUP_CONCAT(CASE WHEN t2.meta_key = '".$seat_key."' AND ".$section." = (SUBSTRING_INDEX(t2.meta_value,'_',-1)) THEN (SUBSTRING_INDEX(t2.meta_value,'_',1)) END) as UNSIGNED INTEGER)";
        $get_sections = "GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' AND ".$section." = (SUBSTRING_INDEX(t2.meta_value,'_',-1)) THEN meta_value END)";
        $get_section = "MAX(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
    }
    else {//if not, get all seats
        $get_seat = "GROUP_CONCAT(CASE WHEN t2.meta_key = '".$seat_key."' THEN (SUBSTRING_INDEX(t2.meta_value,'_',1)) END)";
        $get_sections = "GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
        $get_section = "MAX(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
    }

    $admin_ids = go_get_all_admin();
    $admin_ids = implode( "', '" , $admin_ids );

    $sQuery = "    SELECT SQL_CALC_FOUND_ROWS 
                      t9.*
                      FROM
                    (SELECT 
                      t8.user_id, t1.*,
                      t3.display_name, t3.user_url, t3.user_login,
                      $get_seat as go_seats,
                      $get_sections as go_sections,
                      $get_section as go_section,
                      GROUP_CONCAT(CASE WHEN t2.meta_key = '$badge_key' THEN meta_value END) as go_badges,
                      GROUP_CONCAT(CASE WHEN t2.meta_key = '$group_key' THEN meta_value END) as go_groups,
                      MAX(CASE WHEN t2.meta_key = 'nickname' THEN meta_value END) AS nickname,
                      MAX(CASE WHEN t2.meta_key = '$site_display_name_key' THEN meta_value END) AS site_name,
                      MAX(CASE WHEN t2.meta_key = '$site_url_name_key' THEN meta_value END) AS site_url,
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
                      WHERE t8.user_id NOT IN ( '$admin_ids' ) 
                      GROUP BY t1.id
                      $sHaving
                      )  AS t9
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
    WHERE uid NOT IN ( '$admin_ids' ) 

    ";

    $rResultTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iTotal = $rResultTotal [0];
    //$iFilteredTotal = number that match without limit;
    //$iTotalRecords = number in this table total (total store items/messages)

    $data = array();
    foreach($rResult as $action){//output a row for each action
       /* $caps = $action['wp_capabilities'];
        $caps = unserialize($caps);
        if ($caps['administrator'] ===true || !$caps){
            $total = $iTotal[0];
            $iTotal[0] = $total -1;
            $total = $iFilteredTotal[0];
            $iFilteredTotal[0] = $total -1;
            continue;
        }*/
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

        $data[] = $row;
    }
    $output = array("iTotalRecords" => $iTotal, "iTotalDisplayRecords" => $iFilteredTotal, "aaData" => $data);
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

    $sColumns = array('first_name', 'last_name', 'site_name', 'result', 'post_title');
    $sHaving = go_sHaving($sColumns);

    $sLimit = '';
    if (isset($_GET['start']) && $_GET['length'] != '-1') {
        $sLimit = "LIMIT " . intval($_GET['start']) . ", " . intval($_GET['length']);
    }

    $sOrder = go_sOrder('store');
    //$sType = go_sType();
    $sType = 'LEFT';

    $pTable = "{$wpdb->prefix}posts";
    $lTable = "{$wpdb->prefix}go_loot";
    $aTable = "{$wpdb->prefix}go_actions";
    if(is_gameful()) {
        $main_site_id = get_network()->site_id;
        switch_to_blog($main_site_id);
    }
    $uTable = "{$wpdb->prefix}users";
    $umTable = "{$wpdb->prefix}usermeta";
    if(is_gameful()) {
        restore_current_blog();
    }

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

    $caps_key = "{$wpdb->prefix}capabilities";

    $site_display_name_key = go_prefix_key('go_nickname');

    $section = (isset($_GET['section']) ?  $_GET['section'] : null);
    //if filtered by period, only get the seat for that period
    if(!empty($section)){
        $get_seat = "CAST(GROUP_CONCAT(CASE WHEN t2.meta_key = '".$seat_key."' AND ".$section." = (SUBSTRING_INDEX(t2.meta_value,'_',-1)) THEN (SUBSTRING_INDEX(t2.meta_value,'_',1)) END) as UNSIGNED INTEGER)";
        $get_sections = "GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' AND ".$section." = (SUBSTRING_INDEX(t2.meta_value,'_',-1)) THEN meta_value END)";
        $get_section = "MAX(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
    }
    else {//if not, get all seats
        $get_seat = "GROUP_CONCAT(CASE WHEN t2.meta_key = '".$seat_key."' THEN (SUBSTRING_INDEX(t2.meta_value,'_',1)) END)";
        $get_sections = "GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
        $get_section = "MAX(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
    }

    $admin_ids = go_get_all_admin();
    $admin_ids = implode( "', '" , $admin_ids );

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
                          $get_seat as go_seats,
                          $get_sections as go_sections, 
                          $get_section as go_section, 
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$badge_key' THEN meta_value END) as go_badges,
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$group_key' THEN meta_value END) as go_groups,
                          MAX(CASE WHEN t2.meta_key = 'nickname' THEN meta_value END) AS nickname,
                          MAX(CASE WHEN t2.meta_key = '$site_display_name_key' THEN meta_value END) AS site_name,
                          MAX(CASE WHEN t2.meta_key = 'first_name' THEN meta_value END) AS first_name,
                          MAX(CASE WHEN t2.meta_key = 'last_name' THEN meta_value END) AS last_name,
                          MAX(CASE WHEN t2.meta_key = '$caps_key' THEN meta_value END) AS wp_capabilities
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
                        WHERE t8.user_id NOT IN ( '$admin_ids' ) 
                        GROUP BY t8.user_id 
                        ) AS t9
                      $sType JOIN $aTable AS t4 ON t9.user_id = t4.uid $sOn
                      $sType JOIN $pTable AS t6 ON t4.source_id = t6.ID
                      $sHaving
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

    $totalWhere = " WHERE (action_type = 'store') AND uid NOT IN ( '$admin_ids' )  ";
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

    $data = array();
    foreach($rResult as $action){//output a row for each action
        /*$caps = $action['wp_capabilities'];
        $caps = unserialize($caps);
        if ($caps['administrator'] ===true || !$caps){
            $total = $iTotal[0];
            $iTotal[0] = $total -1;
            $total = $iFilteredTotal[0];
            $iFilteredTotal[0] = $total -1;
            continue;
        }*/
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



        $data[] = $row;
    }
    $output = array("iTotalRecords" => $iTotal, "iTotalDisplayRecords" => $iFilteredTotal, "aaData" => $data);
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

    $sColumns = array('first_name', 'last_name', 'site_name', 'result');
    $sHaving = go_sHaving($sColumns);

    $sLimit = '';
    if (isset($_GET['start']) && $_GET['length'] != '-1') {
        $sLimit = "LIMIT " . intval($_GET['start']) . ", " . intval($_GET['length']);
    }

    $sOrder = go_sOrder('messages');

    //$sType = go_sType();
    $sType = 'LEFT';
    $sOn = go_sOn('message');
    //Index column
    $sIndexColumn = "id";

    $lTable = "{$wpdb->prefix}go_loot";
    $aTable = "{$wpdb->prefix}go_actions";
    if(is_gameful()) {
        $main_site_id = get_network()->site_id;
        switch_to_blog($main_site_id);
    }
    $uTable = "{$wpdb->prefix}users";
    $umTable = "{$wpdb->prefix}usermeta";
    if(is_gameful()) {
        restore_current_blog();
    }
    $sectionQuery = go_sectionQuery();
    $badgeQuery = go_badgeQuery();
    $groupQuery = go_groupQuery();

    $seat_key = go_prefix_key('go_seat');
    $section_key = go_prefix_key('go_section');
    $badge_key = go_prefix_key('go_badge');
    $group_key = go_prefix_key('go_group');

    $caps_key = "{$wpdb->prefix}capabilities";

    $site_display_name_key = go_prefix_key('go_nickname');

    $section = (isset($_GET['section']) ?  $_GET['section'] : null);
    //if filtered by period, only get the seat for that period
    if(!empty($section)){
        $get_seat = "CAST(GROUP_CONCAT(CASE WHEN t2.meta_key = '".$seat_key."' AND ".$section." = (SUBSTRING_INDEX(t2.meta_value,'_',-1)) THEN (SUBSTRING_INDEX(t2.meta_value,'_',1)) END) as UNSIGNED INTEGER)";
        $get_sections = "GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' AND ".$section." = (SUBSTRING_INDEX(t2.meta_value,'_',-1)) THEN meta_value END)";
        $get_section = "MAX(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
    }
    else {//if not, get all seats
        $get_seat = "GROUP_CONCAT(CASE WHEN t2.meta_key = '".$seat_key."' THEN (SUBSTRING_INDEX(t2.meta_value,'_',1)) END)";
        $get_sections = "GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
        $get_section = "MAX(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
    }

    $admin_ids = go_get_all_admin();
    $admin_ids = implode( "', '" , $admin_ids );

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
                          $get_seat as go_seats,
                          $get_sections as go_sections, 
                          $get_section as go_section, 
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$badge_key' THEN meta_value END) as go_badges,
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$group_key' THEN meta_value END) as go_groups,
                          MAX(CASE WHEN t2.meta_key = 'nickname' THEN meta_value END) AS nickname,
                          MAX(CASE WHEN t2.meta_key = '$site_display_name_key' THEN meta_value END) AS site_name,
                          MAX(CASE WHEN t2.meta_key = 'first_name' THEN meta_value END) AS first_name,
                          MAX(CASE WHEN t2.meta_key = 'last_name' THEN meta_value END) AS last_name,
                          MAX(CASE WHEN t2.meta_key = '$caps_key' THEN meta_value END) AS wp_capabilities
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
                        WHERE t8.user_id NOT IN ( '$admin_ids' ) 
                        GROUP BY t8.user_id 
                        ) AS t9
                      $sType JOIN $aTable AS t4 ON t9.user_id = t4.uid $sOn
                      $sHaving
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

    $totalWhere = " WHERE (action_type = 'message') AND uid NOT IN ( '$admin_ids' )  ";
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


    $data = array();
    foreach($rResult as $action){//output a row for each action
        /*$caps = $action['wp_capabilities'];
        $caps = unserialize($caps);
        if ($caps['administrator'] ===true || !$caps){
            $total = $iTotal[0];
            $iTotal[0] = $total -1;
            $total = $iFilteredTotal[0];
            $iFilteredTotal[0] = $total -1;
            continue;
        }*/

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

        if(empty($title) && !empty($message)){
            $title = 'View Message';
        }
        if($message == '<i class="fas fa-heart fa-4x" style="color:#8B0000"></i>'){//don't show message if was marked as favorite heart
            $message = '';
        }

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

        $data[] = $row;
    }
    $output = array("iTotalRecords" => $iTotal, "iTotalDisplayRecords" => $iFilteredTotal, "aaData" => $data);

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
    if ( ! go_user_is_admin()) {
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

    $sColumns = array('first_name', 'last_name', 'site_name', 'post_title');
    $sHaving = go_sHaving( $sColumns);

    $sLimit = '';
    if (isset($_GET['start']) && $_GET['length'] != '-1') {
        $sLimit = "LIMIT " . intval($_GET['start']) . ", " . intval($_GET['length']);
    }

    $sOrder = go_sOrder('tasks');

    //$sType = go_sType();
    $sType = 'LEFT';

    $sOn = go_sOn('tasks');
    //add store items to On statement
    $lTable = "{$wpdb->prefix}go_loot";
    //$aTable = "{$wpdb->prefix}go_actions";
    $tTable = "{$wpdb->prefix}go_tasks";
    $pTable = "{$wpdb->prefix}posts";

    $totalTable = $tTable;
    $tasks = $_GET['tasks'];
    if ( isset($tasks) && !empty($tasks) )
    {
        $sOn .= " AND (";
        if(is_array($tasks)) {
            for ($i = 0; $i < count($tasks); $i++) {
                $task = intval($tasks[$i]);
                $sOn .= "t4.post_id = " . $task . " OR ";
            }

        $sOn = substr_replace( $sOn, "", -3 );
        $sOn .= ")";
        }
        else{
            $sOn .= "t4.post_id = " . $tasks . ") ";
            $totalTable = $lTable;
        }
    }

    //Index column
    $sIndexColumn = "id";



    if(is_gameful()) {
        $main_site_id = get_network()->site_id;
        switch_to_blog($main_site_id);
    }
    $uTable = "{$wpdb->prefix}users";
    $umTable = "{$wpdb->prefix}usermeta";
    if(is_gameful()) {
        restore_current_blog();
    }


    $sectionQuery = go_sectionQuery();
    $badgeQuery = go_badgeQuery();
    $groupQuery = go_groupQuery();

    $seat_key = go_prefix_key('go_seat');
    $section_key = go_prefix_key('go_section');
    $badge_key = go_prefix_key('go_badge');
    $group_key = go_prefix_key('go_group');

    $caps_key = "{$wpdb->prefix}capabilities";

    $site_display_name_key = go_prefix_key('go_nickname');

    $section = (isset($_GET['section']) ?  $_GET['section'] : null);
    //if filtered by period, only get the seat for that period
    if(!empty($section)){
        $get_seat = "CAST(GROUP_CONCAT(CASE WHEN t2.meta_key = '".$seat_key."' AND ".$section." = (SUBSTRING_INDEX(t2.meta_value,'_',-1)) THEN (SUBSTRING_INDEX(t2.meta_value,'_',1)) END) as UNSIGNED INTEGER)";
        $get_sections = "GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' AND ".$section." = (SUBSTRING_INDEX(t2.meta_value,'_',-1)) THEN meta_value END)";
        $get_section = "MAX(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
    }
    else {//if not, get all seats
        $get_seat = "GROUP_CONCAT(CASE WHEN t2.meta_key = '".$seat_key."' THEN (SUBSTRING_INDEX(t2.meta_value,'_',1)) END)";
        $get_sections = "GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
        $get_section = "MAX(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
    }
    $admin_ids = go_get_all_admin();
    $admin_ids = implode( "', '" , $admin_ids );
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
                          $get_seat as go_seats,
                          $get_sections as go_sections, 
                          $get_section as go_section, 
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$badge_key' THEN meta_value END) as go_badges,
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$group_key' THEN meta_value END) as go_groups,
                          MAX(CASE WHEN t2.meta_key = 'nickname' THEN meta_value END) AS nickname,
                          MAX(CASE WHEN t2.meta_key = '$site_display_name_key' THEN meta_value END) AS site_name,
                          MAX(CASE WHEN t2.meta_key = 'first_name' THEN meta_value END) AS first_name,
                          MAX(CASE WHEN t2.meta_key = 'last_name' THEN meta_value END) AS last_name,
                          MAX(CASE WHEN t2.meta_key = '$caps_key' THEN meta_value END) AS wp_capabilities
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
                        WHERE t8.user_id NOT IN ( '$admin_ids' ) 
                        GROUP BY t8.user_id 
                        ) AS t9
                      $sType JOIN $tTable AS t4 ON t9.user_id = t4.uid $sOn
                      $sType JOIN $pTable AS t6 ON t4.post_id = t6.ID
                      $sHaving
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
    FROM   $totalTable
    WHERE uid NOT IN ( '$admin_ids' ) 
    ";

    $rResultTotal = $wpdb->get_results($sQuery3, ARRAY_N);

    $iTotal = $rResultTotal [0];
    //$iFilteredTotal = number that match without limit;
    //$iTotalRecords = number in this table total (total store items/messages)
    //$output = array("iTotalRecords" => $iTotal, "iTotalDisplayRecords" => $iFilteredTotal, "aaData" => array());


    $data = array();
    foreach($rResult as $action){//output a row for each action
        /*$caps = $action['wp_capabilities'];
        $caps = unserialize($caps);
        if ($caps['administrator'] ===true || !$caps){
            $total = $iTotal[0];
            $iTotal[0] = $total -1;
            $total = $iFilteredTotal[0];
            $iFilteredTotal[0] = $total -1;
            continue;
        }*/

        //The message content
        $row = go_start_row($action);
        $row[] = $action['post_title'];

        $task_id = (isset($action['post_id']) ?  $action['post_id'] : null);
        //if ($task_id != null) {
        //  $task_id = "data-task='" . $task_id . "'";
        //}

        $user_id = $action['user_id'];
        $row[] = '<a href="javascript:;" class="go_blog_user_task" data-user_id="'.$user_id.'" data-post_id="'.$task_id.'"><i style="padding: 0px 10px;" class="fas fa-search" aria-hidden="true"></i></a><a><i data-uid="' . $user_id . '" data-task="'. $task_id . '" style="padding: 0px 10px;" class="go_reset_task_clipboard fa fa-times-circle" aria-hidden="true"></a>';//actions
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

        $custom_fields = go_post_meta($action['post_id']);

        $bonus_count = 0;
        if ($action['status'] > 0) {
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
        else if ($action['status'] == 0){
            $row[] = "";
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

        $data[] = $row;
    }

    $output = array("iTotalRecords" => $iTotal, "iTotalDisplayRecords" => $iFilteredTotal, "aaData" => $data);
    //go_write_log("output: ");
    //go_write_log($output);
    //go_total_query_time();

    echo json_encode( $output );
    die();
}


function go_clipboard_activity_stats_ajax(){
    global $wpdb;

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }
    //check_ajax_referer( 'go_clipboard_messages' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_clipboard_activity_stats_ajax' ) ) {
        echo "refresh";
        die( );
    }

    $sColumns = array('first_name', 'last_name', 'display_name', 'post_title');
    $sHaving = go_sHaving( $sColumns);

    $sType = 'LEFT';

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
    //$aTable = "{$wpdb->prefix}go_actions";
    if(is_gameful()) {
        $main_site_id = get_network()->site_id;
        switch_to_blog($main_site_id);
    }
    $uTable = "{$wpdb->prefix}users";
    $umTable = "{$wpdb->prefix}usermeta";
    if(is_gameful()) {
        restore_current_blog();
    }
    $tTable = "{$wpdb->prefix}go_tasks";
    $pTable = "{$wpdb->prefix}posts";

    $sectionQuery = go_sectionQuery();
    $badgeQuery = go_badgeQuery();
    $groupQuery = go_groupQuery();

    $seat_key = go_prefix_key('go_seat');
    $section_key = go_prefix_key('go_section');
    $badge_key = go_prefix_key('go_badge');
    $group_key = go_prefix_key('go_group');

    $caps_key = "{$wpdb->prefix}capabilities";

    $site_display_name_key = go_prefix_key('go_nickname');

    $section = (isset($_GET['section']) ?  $_GET['section'] : null);
    //if filtered by period, only get the seat for that period
    if(!empty($section)){
        $get_seat = "CAST(GROUP_CONCAT(CASE WHEN t2.meta_key = '".$seat_key."' AND ".$section." = (SUBSTRING_INDEX(t2.meta_value,'_',-1)) THEN (SUBSTRING_INDEX(t2.meta_value,'_',1)) END) as UNSIGNED INTEGER)";
        $get_sections = "GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' AND ".$section." = (SUBSTRING_INDEX(t2.meta_value,'_',-1)) THEN meta_value END)";
        $get_section = "MAX(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
    }
    else {//if not, get all seats
        $get_seat = "GROUP_CONCAT(CASE WHEN t2.meta_key = '".$seat_key."' THEN (SUBSTRING_INDEX(t2.meta_value,'_',1)) END)";
        $get_sections = "GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
        $get_section = "MAX(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
    }
    $admin_ids = go_get_all_admin();
    $admin_ids = implode( "', '" , $admin_ids );
    $sQuery = "    
                  SELECT SQL_CALC_FOUND_ROWS
                    t9.*
                  FROM (
                      SELECT
                       t9.*, t4.status
                      FROM (
                        SELECT
                          t8.user_id
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
                        WHERE t8.user_id NOT IN ( '$admin_ids' ) 
                        GROUP BY t8.user_id 
                        ) AS t9
                      $sType JOIN $tTable AS t4 ON t9.user_id = t4.uid $sOn
                      $sType JOIN $pTable AS t6 ON t4.post_id = t6.ID
                      $sHaving
                  ) AS t9    
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

    $complete_num = 0;
    $started_num = 0;
    $not_started_num = 0;
    foreach($rResult as $action) {//output a row for each action


        $custom_fields = go_post_meta($action['post_id']);


        if ($action['status'] < 0 || $action['status'] === null  ){
            $not_started_num++;
        }
        else if ($action['status'] >= 0) {
            $stage_count = (isset($custom_fields['go_stages'][0]) ? $custom_fields['go_stages'][0] : null);

            if (($action['status'] >= $stage_count) && !empty($action['status'])) {
                $complete_num++;
            } else {
                $started_num++;
            }
        } else {
            $not_started_num++;
        }
    }
        echo json_encode(
            array(
                'json_status' => 'success',
                'complete_num' => $complete_num,
                'started_num' => $started_num,
                'not_started_num' => $not_started_num
            )
        );
    die();
}

/**
 *
 */
function go_clipboard_attendance() {

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }
    //check_ajax_referer( 'go_clipboard_messages' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_clipboard_attendance' ) ) {
        echo "refresh";
        die( );
    }

    $seats_name = get_option( 'options_go_seats_name' );

    echo "<div id='go_clipboard_attendance' class='go_datatables'><table id='go_clipboard_attendance_datatable' class='pretty display'>
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


    go_loot_headers(false, false );
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
function go_clipboard_attendance_dataloader_ajax(){
    global $wpdb;

    $sColumns = array('first_name', 'last_name', 'display_name', 'result');
    $sHaving = go_sHaving($sColumns);

    $sLimit = '';
    if (isset($_GET['start']) && $_GET['length'] != '-1') {
        $sLimit = "LIMIT " . intval($_GET['start']) . ", " . intval($_GET['length']);
    }

    $sOrder = go_sOrder('attendance');

    //$sType = go_sType();
    $sType = 'LEFT';
    $sOn = go_sOn('attendance');
    //Index column
    $sIndexColumn = "id";

    $lTable = "{$wpdb->prefix}go_loot";
    $aTable = "{$wpdb->prefix}go_actions";
    if(is_gameful()) {
        $main_site_id = get_network()->site_id;
        switch_to_blog($main_site_id);
    }
    $uTable = "{$wpdb->prefix}users";
    $umTable = "{$wpdb->prefix}usermeta";
    if(is_gameful()) {
        restore_current_blog();
    }
    $sectionQuery = go_sectionQuery();
    $badgeQuery = go_badgeQuery();
    $groupQuery = go_groupQuery();

    $seat_key = go_prefix_key('go_seat');
    $section_key = go_prefix_key('go_section');
    $badge_key = go_prefix_key('go_badge');
    $group_key = go_prefix_key('go_group');

    $caps_key = "{$wpdb->prefix}capabilities";

    $site_display_name_key = go_prefix_key('go_nickname');

    $section = (isset($_GET['section']) ?  $_GET['section'] : null);
    //if filtered by period, only get the seat for that period
    if(!empty($section)){
        $get_seat = "CAST(GROUP_CONCAT(CASE WHEN t2.meta_key = '".$seat_key."' AND ".$section." = (SUBSTRING_INDEX(t2.meta_value,'_',-1)) THEN (SUBSTRING_INDEX(t2.meta_value,'_',1)) END) as UNSIGNED INTEGER)";
        $get_sections = "GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' AND ".$section." = (SUBSTRING_INDEX(t2.meta_value,'_',-1)) THEN meta_value END)";
        $get_section = "MAX(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
    }
    else {//if not, get all seats
        $get_seat = "GROUP_CONCAT(CASE WHEN t2.meta_key = '".$seat_key."' THEN (SUBSTRING_INDEX(t2.meta_value,'_',1)) END)";
        $get_sections = "GROUP_CONCAT(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
        $get_section = "MAX(CASE WHEN t2.meta_key = '$section_key' THEN meta_value END)";
    }

    $admin_ids = go_get_all_admin();
    $admin_ids = implode( "', '" , $admin_ids );

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
                          $get_seat as go_seats,
                          $get_sections as go_sections, 
                          $get_section as go_section, 
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$badge_key' THEN meta_value END) as go_badges,
                          GROUP_CONCAT(CASE WHEN t2.meta_key = '$group_key' THEN meta_value END) as go_groups,
                          MAX(CASE WHEN t2.meta_key = 'nickname' THEN meta_value END) AS nickname,
                          MAX(CASE WHEN t2.meta_key = '$site_display_name_key' THEN meta_value END) AS site_name,
                          MAX(CASE WHEN t2.meta_key = 'first_name' THEN meta_value END) AS first_name,
                          MAX(CASE WHEN t2.meta_key = 'last_name' THEN meta_value END) AS last_name,
                          MAX(CASE WHEN t2.meta_key = '$caps_key' THEN meta_value END) AS wp_capabilities
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
                        WHERE t8.user_id NOT IN ( '$admin_ids' ) 
                        GROUP BY t8.user_id 
                        ) AS t9
                      $sType JOIN $aTable AS t4 ON t9.user_id = t4.uid $sOn
                      $sHaving
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

    $totalWhere = " WHERE (action_type = 'attendance') AND uid NOT IN ( '$admin_ids' )  ";
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


    $data = array();
    foreach($rResult as $action){//output a row for each action
        /*$caps = $action['wp_capabilities'];
        $caps = unserialize($caps);
        if ($caps['administrator'] ===true || !$caps){
            $total = $iTotal[0];
            $iTotal[0] = $total -1;
            $total = $iFilteredTotal[0];
            $iFilteredTotal[0] = $total -1;
            continue;
        }*/

        //The message content
        $row = go_start_row($action);
        $TIMESTAMP = $action['TIMESTAMP'];
        //$badge_ids = $action['badges'];
        //$group_ids = $action['groups'];
        $result = $action['result'];

        //unserialize the message and set the results
        $result_array = unserialize($result);
        $title = $result_array[0];

        $message = $result_array[1];

        if(empty($title) && !empty($message)){
            $title = 'View Message';
        }
        if($message == '<i class="fas fa-heart fa-4x" style="color:#8B0000"></i>'){//don't show message if was marked as favorite heart
            $message = '';
        }






        if (!empty($message)) {
            //$title = "<span class='tooltip' ><span class='tooltiptext'>{$message}</span>$title</span>";
            $title = '<span class="tooltip" data-tippy-content="'. $message .'">'. $title . '</span>';
        }

        $time  = go_clipboard_time($TIMESTAMP);

        $row[] = "{$time}";
        $row[] = "{$title}";

        $go_loot_columns = go_loot_columns_clipboard($action);
        $row = array_merge($row, $go_loot_columns);

        $data[] = $row;
    }
    $output = array("iTotalRecords" => $iTotal, "iTotalDisplayRecords" => $iFilteredTotal, "aaData" => $data);

    global $go_debug;
    if($go_debug) {
        go_total_query_time();
    }

    echo json_encode( $output );
    die();
}

function go_quests_frontend(){
    //check_ajax_referer( 'go_clipboard_messages' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_quests_frontend' ) ) {
        echo "refresh";
        die( );
    }

    if ( ! go_user_is_admin() ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    } else {

        echo "<div id='quest_frontend_wrapper'>";
        //Loader goes here
        echo "<div id='quest_frontend_loader'></div>";
        echo "<div id='quest_frontend_container' style='display: none;'>";
        //$task_name = get_option( 'options_go_tasks_name_plural'  );

        $taxonomy = (isset($_REQUEST['taxonomy']) ?  $_REQUEST['taxonomy'] : null);

        $post_id = (isset($_POST['post_id']) ?  $_POST['post_id'] : null);
        $post_title = go_the_title($post_id);


        if($taxonomy === 'task_chains'){
            //go_clipboard_filters();
            go_leaderboard_filters('single_quest');
            ?>
                <div id="clipboard_activity_wrap">
                    <div class="quest_stats_wrapper"><h2 style="padding-right: 20px; margin: 0px;"><?php echo $post_title; ?></h2><div class="quest_stats complete" ><span id="quest_complete" class="quest_stats_num"></span> Complete</div><div class=" quest_stats started" ><span id="quest_started"  class="quest_stats_num"></span> In Progress</div><div class="quest_stats not_started"><span id="quest_not_encountered"  class="quest_stats_num"></span> Not Started</div> </div>
                    <div id="clipboard_activity_datatable_container"></div>
                </div>
                <?php
        }
        else{
            go_leaderboard_filters('single_store_item');
            ?>
            <div id="clipboard_store_wrap">
                <div class="quest_stats_wrapper"><h2 style="padding-right: 20px; margin: 0px;"><?php echo $post_title; ?></h2></div>
                <div id="clipboard_store_datatable_container"></div>
            </div>
            <?php

        }
        ?>


        </div>
        </div>
        <?php
        //go_hidden_footer();
    }
    die();
}


function go_store_frontend(){
    //check_ajax_referer( 'go_clipboard_messages' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_quests_frontend' ) ) {
        echo "refresh";
        die( );
    }

    if ( ! go_user_is_admin() ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    } else {

        echo "<div id='quest_frontend_wrapper'>";
        //Loader goes here
        echo "<div id='quest_frontend_loader'></div>";
        echo "<div id='quest_frontend_container' style='display: none;'>";
        $task_name = get_option( 'options_go_tasks_name_plural'  );

        $post_id = (isset($_POST['post_id']) ?  $_POST['post_id'] : null);
        $post_title = go_the_title($post_id);
        //go_clipboard_filters();
        //single_store_item
        go_leaderboard_filters('single_quest');
        ?>




        <div id="clipboard_activity_wrap">
            <div class="quest_stats_wrapper"><h2 style="padding-right: 20px; margin: 0px;"><?php echo $post_title; ?></h2></div>
            <div id="clipboard_store_datatable_container"></div>
        </div>

        </div>
        </div>
        <?php
        //go_hidden_footer();
    }
    die();
}