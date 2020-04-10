<?php

/**
 *
 */
function go_stats_lightbox() {

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }


    //check_ajax_referer( 'go_stats_lightbox_' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_stats_lightbox' ) ) {
        echo "refresh";
        die( );
    }

    //$user_id = 0;
    //Get the user_id for the stats
    if ( ! empty( $_POST['uid'] ) ) {
        $user_id = (int) $_POST['uid'];
    } else {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
    }

    $show_about_me = get_option( 'options_about_me_toggle' );
    $about_me_is_public = get_option( 'options_about_me_public' );

    $current_user_id = get_current_user_id();
    $is_admin = go_user_is_admin();
    $is_current_user = false;
    if($current_user_id === $user_id){
        $is_current_user = true;
        //reactivate the stats icon in admin bar if this is the current user
        ?>
        <script>
            //jQuery("#wp-admin-bar-go_stats").off().one("click", function(){ go_stats_lightbox_page_button()});
            jQuery(".go_user_bar_stats").off().one("click", function(){ go_stats_lightbox_page_button(<?php echo $user_id; ?>)});
        </script>
        <?php
    }




    go_stats_header($user_id, false, true, true, false, true);
    ?>


    <div id="stats_tabs">
        <ul>
            <?php


            if ($show_about_me && ($is_admin || $about_me_is_public)) {
                    echo '<li class="stats_tabs" tab="about"><a href="#stats_about">ABOUT</a></li>';
                }



            $badges_toggle = get_option('options_go_badges_toggle');
            $groups_toggle = get_option('options_go_groups_toggle');
            $store_toggle = get_option('options_go_store_toggle');

            // prepares tab titles
            $badges_name = ucfirst(get_option('options_go_badges_name_plural'));
            $groups_name = ucfirst(get_option('options_go_groups_name_plural'));
            if($badges_toggle) {
                ?>
                <li class="stats_tabs" tab="badges"><a
                            href="#stats_badges"><?php echo strtoupper($badges_name); ?></a></li>
                <?php
            }
            if($groups_toggle) {
                ?>
                <li class="stats_tabs" tab="groups"><a
                            href="#stats_groups"><?php echo strtoupper($groups_name); ?></a></li>
                <?php
            }

            if($is_admin || $is_current_user) {
                ?>

                <li class="stats_tabs" tab="messages"><a href="#stats_messages">MESSAGES</a></li>
                <li class="stats_tabs" tab="tasks"><a
                            href="#stats_tasks"><?php echo strtoupper(get_option('options_go_tasks_name_plural')); ?></a>
                </li>
                <?php

                if ($store_toggle) {
                    ?>
                    <li class="stats_tabs" tab="store"><a
                                href="#stats_store"><?php echo strtoupper(get_option('options_go_store_name')); ?></a>
                    </li>
                    <?php
                }
                ?>

                <li class="stats_tabs" tab="history"><a href="#stats_history">HISTORY</a></li>
                <?php
            }

        echo '</ul>';


        //output containers for the tab content
            //include the content that is loaded on lightbox open (

        if ($show_about_me && ($is_admin || $about_me_is_public )){
              echo '<div id="stats_about">';
           go_stats_about($user_id,  true);
              echo "</div>";
      }
        else if ($is_current_user){
            ?>
            <script>
                go_stats_task_list();
            </script>
            <?php
        }



        if ($is_current_user || $is_admin){//is current user
            ?>
            <div id="stats_tasks"></div>
            <div id="stats_store"></div>
            <div id="stats_messages"></div>
            <div id="stats_history"></div>
            <?php
            if($is_admin){
                echo '<div id="stats_badges" class="sortable stats_badges"></div>';
                echo '<div id="stats_groups" class="sortable stats_groups"></div>';
            }else{
                echo '<div id="stats_badges" class="stats_badges"></div>';
                echo '<div id="stats_groups" class="stats_groups"></div>';
            }
        }else  {

            echo '<div id="stats_badges">';

            go_stats_badges_list(true, $user_id);

            echo '  
                    </div>
                    <div id="stats_groups"></div>';
        }
        ?>






    </div>



    <?php
    die();
}

function go_loot_headers($totals = null, $terms = true){
    $xp_abbr = get_option( "options_go_loot_xp_abbreviation" );
    //$gold_abbr = get_option( "options_go_loot_gold_abbreviation" );
    $gold_abbr = go_get_loot_short_name('gold');
    $health_abbr = get_option( "options_go_loot_health_abbreviation" );

    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option('options_go_loot_health_toggle');

    $badges_toggle = get_option('options_go_badges_toggle');
    $groups_toggle = get_option('options_go_groups_toggle');

    // prepares tab titles
    $badges_name = ucfirst(get_option('options_go_badges_name_plural'));
    $groups_name = ucfirst(get_option('options_go_groups_name_plural'));

    if ($totals == true){
        $total = "Total ";

    }else{
        $total ="";
    }

    if ($xp_toggle){
        ?>
        <th class='header'><?php echo "$total" . "$xp_abbr"; ?></th>
        <?php
    }
    if ($gold_toggle){
        ?>
        <th class='header'><?php echo "$total" . "$gold_abbr"; ?></th>
        <?php
    }
    if ($health_toggle){
        ?>
        <th class='header'><?php echo "$total" . "$health_abbr"; ?></th>
        <?php
    }
    if($terms) {
        if ($badges_toggle && !$totals) {
            ?>
            <th class='header'><?php echo "$badges_name"; ?></th>
            <?php
        }

        if ($groups_toggle && !$totals) {
            ?>
            <th class='header'><?php echo "$groups_name"; ?></th>
            <?php
        }
    }
}

/**
 * @param null $user_id
 * @param bool $not_ajax
 */
function go_stats_about($user_id, $not_ajax = false) {

    if (!$not_ajax){

        if ( !is_user_logged_in() ) {
            echo "login";
            die();
        }

        //check_ajax_referer( 'go_stats_about' );
        if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_stats_about' ) ) {
            echo "refresh";
            die( );
        }

        $user_id = (int) $_POST['user_id'];
    }

    echo "<div id='go_stats_about' class='go_datatables'>";
    $about_me_quest = get_option( 'options_about_me_quest' );

    go_blog_user_task(false, $user_id, $about_me_quest);

    //go_blog_post($about_me_quest)

    echo "</div>";

    //die();
}

function go_stats_task_list($skip_ajax_checks = false) {
    if(!$skip_ajax_checks) {
        if (!is_user_logged_in()) {
            echo "login";
            die();
        }

        //check_ajax_referer( 'go_stats_task_list_' );
        if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_stats_task_list')) {
            echo "refresh";
            die();
        }
    }
    $current_user = get_current_user_id();
    $is_admin = go_user_is_admin();

    echo "<div id='go_task_list' class='go_datatables'><table id='go_tasks_datatable' class='pretty display'>
                   <thead>
						<tr><th></th>";
    if ($is_admin){
        echo "<th class='header go_tasks_reset_multiple'  style='color: red;'><a href='#' class='go_tasks_reset_multiple_clipboard'><i class='fas fa-times-circle' aria-hidden='true'></i></a></th>
        <th class='header go_tasks_reset' ><a href='#'></a></th>";
    }
    echo "    
        <th class='header' id='go_stats_last_time'><a href=\"#\">Time</a></th>
        <th class='header' id='go_stats_post_name'><a href=\"#\">Title</a></th>
        
    
        <th class='header' id='go_stats_status'><a href=\"#\">Status</a></th>
        <th class='header' id='go_stats_bonus_status'><a href=\"#\">Bonus</a></th>
        <th class='header' id='go_stats_actions'><a href=\"#\">Actions</a></th>
        <th class='header' id='go_stats_links'><a href=\"#\">History</a></th>";
    go_loot_headers();
    echo"
            </tr>
            </thead>
            <tfoot>
            <tr><th></th>";
    if ($is_admin){
        echo "<th class='header go_tasks_reset_multiple'  style='color: red;'><a href='#' class='go_tasks_reset_multiple_clipboard'><i class='fas fa-times-circle' aria-hidden='true'></i></a></th>
    <th class='header go_tasks_reset' ><a href='#'></a></th>";
    }
    echo "
							<th class='header' id='go_stats_last_time'><a href=\"#\">Time</a></th>
							<th class='header' id='go_stats_post_name'><a href=\"#\">Title</a></th>
							
						
							<th class='header' id='go_stats_status'><a href=\"#\">Status</a></th>
							<th class='header' id='go_stats_bonus_status'><a href=\"#\">Bonus</a></th>
							<th class='header' id='go_stats_actions'><a href=\"#\">Actions</a></th>
                            <th class='header' id='go_stats_links'><a href=\"#\">History</a></th>";

    go_loot_headers();
    echo"
            </tr>
            </tfoot>
			   
				</table></div>";
    die();
}

function go_tasks_dataloader_ajax(){
    global $wpdb;
    $go_task_table_name = "{$wpdb->prefix}go_tasks";
    $aColumns = array( 'id', 'uid', 'post_id', 'status', 'bonus_status' ,'xp', 'gold', 'health', 'start_time', 'last_time', 'badges', 'groups' );
    $sIndexColumn = "id";
    $sTable = $go_task_table_name;
    $current_user = get_current_user_id();
    $is_admin = go_user_is_admin();


    $sLimit = '';
    if ( isset( $_GET['start'] ) && $_GET['length'] != '-1' )
    {
        $sLimit = "LIMIT ".intval( $_GET['start'] ).", ".
            intval( $_GET['length'] );
    }

    $sOrder = "ORDER BY last_time desc"; //always in reverse order

    $sWhere = "";
    if ( isset($_REQUEST['sSearch']) && $_REQUEST['sSearch'] != "" )
    {
        $sWhere = "WHERE (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            $sWhere .= "`".$aColumns[$i]."` LIKE '%".esc_sql( $_REQUEST['sSearch'] )."%' OR ";
        }
        $sWhere = substr_replace( $sWhere, "", -3 );
        $sWhere .= ')';
    }

    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {
        if ( isset($_REQUEST['bSearchable_'.$i]) && $_REQUEST['bSearchable_'.$i] == "true" && $_REQUEST['sSearch_'.$i] != '' )
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE ";
            }
            else
            {
                $sWhere .= " AND ";
            }
            $sWhere .= "`".$aColumns[$i]."` LIKE '%".esc_sql($_REQUEST['sSearch_'.$i])."%' ";
        }
    }




    /////////////
    /// START
    ///
    $search_val = $_GET['search']['value'];

    $user_id = $_GET['user_id'];

    $sWhere = "WHERE uid = ".$user_id;

    if ( isset($search_val) && $search_val != "" )
    {

        $sWhere .= " AND (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            $sWhere .= "`".$aColumns[$i]."` LIKE '%".esc_sql( $search_val )."%' OR ";
        }
        $sWhere = substr_replace( $sWhere, "", -3 );//removes the last OR

        $sWhere .= ')';
    }

    $totalWhere = " WHERE uid = ".$user_id;

    $sQuery = "
          SELECT `".str_replace(" , ", " ", implode("`, `", $aColumns))."`
          FROM   $sTable
          $sWhere
          $sOrder
          $sLimit
  
      ";

    $rResult = $wpdb->get_results($sQuery, ARRAY_A);

    $sQuery = "SELECT FOUND_ROWS()";

    $rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iFilteredTotal = $rResultFilterTotal [0];

    $sQuery = "
      SELECT COUNT(`".$sIndexColumn."`)
      FROM   $sTable
      $totalWhere
     ";

    $rResultTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iTotal = $rResultTotal [0];

    $output = array(
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );

    ///
    ///END
    ///

    foreach($rResult as $task){//output a row for each task
        $row = array();
        ///////////
        ///
        $post_id = $task['post_id'];
        //$post_name = $task[post_title];
        $custom_fields = go_post_meta( $post_id );
        $post_name = get_the_title($post_id);
        $post_link = get_post_permalink($post_id);
        $status = $task['status'];
        $total_stages = (isset($custom_fields['go_stages'][0]) ?  $custom_fields['go_stages'][0] : null);


        $bonus_switch = (isset($custom_fields['bonus_switch'][0]) ?  $custom_fields['bonus_switch'][0] : null);
        $bonus_status = null;
        $total_bonus_stages = null;
        if ($bonus_switch) {
            $bonus_status = $task['bonus_status'];
            $total_bonus_stages = (isset($custom_fields['go_bonus_limit'][0]) ? $custom_fields['go_bonus_limit'][0] : null);
            $bonus_status = $bonus_status ."/". $total_bonus_stages;
        }
        $last_time = $task['last_time'];
        $time  = date("m/d/y g:i A", strtotime($last_time));
        $next_bonus_stage = null;

        $check_box = "<input class='go_checkbox' type='checkbox' name='go_selected' data-uid='" . $user_id . "' data-task='". $post_id . "'/>";
        $row[] = "";//empty
        if($is_admin) {
            $row[] = "{$check_box}";//checkbox

            $row[] = '<a><i data-uid="' . $user_id . '" data-task="' . $post_id . '" style="padding: 0px 10px;" class="go_reset_task_clipboard fas fa-times-circle" aria-hidden="true"></a>';
        }
        $row[] = "{$time}";
        $row[] = "<a href='{$post_link}' >{$post_name}</a>";
        if($status == -2) {
            $row[] = "reset";
        }else if($status == -1) {
            $row[] = "abandoned";
        }else {
            $row[] = "{$status} / {$total_stages}";
        }
        $row[] = "{$bonus_status}";
        $row[] = '<a href="javascript:;" class="go_blog_user_task" data-user_id="'.$user_id.'" data-post_id="'.$post_id.'" ><i style="padding: 0px 10px;" class="fas fa-search" aria-hidden="true"></i></a>';//actions

        $row[] = " <a href='javascript:;' class='go_stats_body_activity_single_task' data-postID='{$post_id}' onclick='go_stats_single_task_activity_list({$post_id});'><i style=\"padding: 0px 10px;\" class=\"fas fa-table\" aria-hidden=\"true\"></i></a>";

        $go_loot_columns = go_loot_columns_stats($task);
        $row = array_merge($row, $go_loot_columns);

        $output['aaData'][] = $row;
    }


    echo json_encode( $output );
    die();
}

/**
 * Called by the ajax dataloaders.
 * @param $action
 * @return array
 */
function go_loot_columns_stats($action){
    $xp = $action['xp'];
    $gold = $action['gold'];
    $health = $action['health'];
    $badge_ids = $action['badges'];
    $group_ids = $action['groups'];

    $result_serialized = $action['result'];
    $result_array = unserialize($result_serialized);

    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option('options_go_loot_health_toggle');

    $badges_toggle = get_option('options_go_badges_toggle');
    $groups_toggle = get_option('options_go_groups_toggle');



    $row = array();
    if ($xp_toggle){
        $row[] = $xp;
    }
    if ($gold_toggle){
        $row[] = $gold;
    }
    if ($health_toggle){
        $row[] = $health;
    }
    if ($badges_toggle){
        $dir = $result_array[2];

        $badges = go_badges_list($badge_ids, $dir);
        $row[] = $badges;
    }

    if($groups_toggle){
        $dir = $result_array[3];
        $groups = go_groups_list($group_ids, $dir);
        $row[] = $groups;
    }

    return $row;
}

/**
 * @param $post_id
 */
function go_stats_single_task_activity_list($post_id) {

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_stats_single_task_activity_list' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_stats_single_task_activity_list' ) ) {
        echo "refresh";
        die( );
    }

    global $wpdb;
    $go_task_table_name = "{$wpdb->prefix}go_actions";
    if ( ! empty( $_POST['user_id'] ) ) {
        $user_id = (int) $_POST['user_id'];
    }

    $post_id = (int) $_POST['postID'];

    $task_name = get_option('options_go_tasks_name_singular');
    $tasks_name = get_option('options_go_tasks_name_plural');

    $actions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * 
			FROM {$go_task_table_name} 
			WHERE uid = %d and source_id = %d",
            $user_id,
            $post_id
        )
    );
    $post_title = get_the_title($post_id);
    echo "<div id='go_task_list_single' class='go_datatables'>
            <div style='float: right;'><a onclick='go_close_single_history()' href='javascript:void(0);'><i class='fas fa-times ab-icon' aria-hidden='true'></i> Show All $tasks_name</a></div>
            <h3>Single $task_name History: $post_title</h3>

            <table id='go_single_task_datatable' class='pretty display'>
                   <thead>
						<tr>
						
							<th class='header' id='go_stats_time'><a href=\"#\">Time</a></th>
							<th class='header' id='go_stats_action'><a href=\"#\">Action</a></th>
							<th class='header' id='go_stats_post_name'><a href=\"#\">Stage</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">Modifiers</a></th>";
    go_loot_headers();
    //go_loot_headers(true);
    echo"
						</tr>
						</thead>
			    <tbody>
						";
    foreach ( $actions as $action ) {
        $action_type = $action->action_type;
        $source_id = $action->source_id;
        $TIMESTAMP = $action->TIMESTAMP;
        $time  = date("m/d/y g:i A", strtotime($TIMESTAMP));
        $stage = $action->stage;
        $bonus_status = $action->bonus_status;
        $result = $action->result;
        $quiz_mod = $action->quiz_mod;
        $late_mod = $action->late_mod;
        $timer_mod = $action->timer_mod;
        $health_mod = $action->global_mod;
        $xp = $action->xp;
        $gold = $action->gold;
        $health = $action->health;
        $badges =$action->badges;
        $groups =$action->groups;


        $xp_toggle = get_option('options_go_loot_xp_toggle');
        $gold_toggle = get_option('options_go_loot_gold_toggle');
        $health_toggle = get_option('options_go_loot_health_toggle');
        $badges_toggle = get_option('options_go_badges_toggle');
        $groups_toggle = get_option('options_go_groups_toggle');

        $post_title = get_the_title($source_id);


        if ($action_type == 'admin'){
            $type = "Admin";
        }
        if ($action_type == 'reset'){
            $type = "Reset";
        }

        if ($action_type == 'store'){
            $store_qnty = $stage;
            $type = strtoupper( get_option( 'options_go_store_name' ) );
            $post_title = "Qnt: " . $store_qnty . " of " . $post_title ;
        }

        if ($action_type == 'task'){
            $type = strtoupper( get_option( 'options_go_tasks_name_singular' ) );
            if ($bonus_status == 0) {
                //$type = strtoupper( get_option( 'options_go_tasks_name_singular' ) );
                $type = 'Continue';
                $post_title = " Stage: " . $stage;
            }
        }

        if ($action_type == 'undo_task'){
            $type = strtoupper( get_option( 'options_go_tasks_name_singular' ) );
            if ($bonus_status == 0) {
                $type = "Undo";
                $post_title = " Stage: " . $stage;
            }
        }
        if ($result == 'undo_bonus'){
            $type = "Undo Bonus";
            $post_title = $post_title . " Bonus: " . $bonus_status ;
        }

        $quiz_mod_int = intval($quiz_mod);
        if (!empty($quiz_mod_int)){
            $quiz_mod = "<i class=\"fas fa-check-circle-o\" aria-hidden=\"true\"></i> ". $late_mod;
        }
        else{
            $quiz_mod = null;
        }

        $late_mod_int = intval($late_mod);
        if (!empty($late_mod_int)){
            $late_mod = "<i class=\"fa fa-calendar\" aria-hidden=\"true\"></i> ". $late_mod;
        }
        else{
            $late_mod = null;
        }

        $timer_mod_int = intval($timer_mod);
        if (!empty($timer_mod_int)){
            $timer_mod = "<i class=\"fa fa-hourglass\" aria-hidden=\"true\"></i> ". $timer_mod;
        }
        else{
            $timer_mod = null;
        }

        $health_mod_int = intval($health_mod);
        if (!empty($health_mod_int)){
            $health_abbr = get_option( "options_go_loot_health_abbreviation" );
            $health_mod_str = $health_abbr . ": ". $health_mod;
        }
        else{
            $health_mod_str = null;
        }

        echo " 			
			        <tr id='postID_{$source_id}'>
			            <td data-order='{$TIMESTAMP}'>{$time}</td>
					    <td>{$type} </td>
					    <td>{$post_title} </td>
					    <td>{$health_mod_str}   {$timer_mod}   {$late_mod}   {$quiz_mod}</td>";
        if($xp_toggle){
            echo "<td>$xp</td>";
        }
        if($gold_toggle){
            echo "<td>$gold</td>";
        }
        if($health_toggle){
            echo "<td>$health</td>";
        }
        if($badges_toggle){
            $badges_list = go_badges_list($badges);
            echo "<td>$badges_list</td>";
        }
        if($groups_toggle){
            $groups_list = go_groups_list($groups);
            echo "<td>$groups_list</td>";
        }
		echo "</tr>";


    }
    echo "</tbody>
				</table></div>";

    die();
}

/**
 *
 */
function go_stats_store_list() {
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_stats_item_list_' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_stats_store_list' ) ) {
        echo "refresh";
        die( );
    }

    echo "<div id='go_store_list' class='go_datatables'><table id='go_store_datatable' class='pretty display'>
                   <thead>
						<tr>
						
							<th class='header'><a href=\"#\">Time</a></th>
							<th class='header'><a href=\"#\">Item</a></th>					
							<th class='header'><a href=\"#\">QTY</a></th>";

    go_loot_headers();
    ?>
					</tr>
						</thead>

				</table></div>
    <?php

    die();
}

/**
 * go_messages_dataloader_ajax
 * Called for Server Side Processing from the JS
 */
function go_stats_store_item_dataloader(){
    global $wpdb;

    $go_action_table_name = "{$wpdb->prefix}go_actions";

    $aColumns = array( 'id', 'uid', 'source_id', 'action_type', 'stage', 'TIMESTAMP' , 'result', 'global_mod', 'xp', 'gold', 'health', 'badges', 'groups' );

    $sIndexColumn = "id";
    $sTable = $go_action_table_name;

    $sLimit = '';

    if ( isset( $_GET['start'] ) && $_GET['length'] != '-1' )
    {
        $sLimit = "LIMIT ".intval( $_GET['start'] ).", ".
            intval( $_GET['length'] );
    }

    $sOrder = "ORDER BY TIMESTAMP desc"; //always in reverse order


    $search_val = $_GET['search']['value'];

    $user_id = $_GET['user_id'];

    $sWhere = "WHERE uid = ".$user_id . " AND (action_type = 'store') ";
    $sWhere2 = '';

    if ( isset($search_val) && $search_val != "" )
    {
/*
        $sWhere .= " AND (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            $sWhere .= "`".$aColumns[$i]."` LIKE '%".esc_sql( $search_val )."%' OR ";
        }
        $sWhere = substr_replace( $sWhere, "", -3 );//removes the last OR


        $sWhere .= ')';
*/
        $sWhere2 = " WHERE `post_title` LIKE '%".esc_sql( $search_val )."%'";

    }

    $totalWhere = " WHERE uid = ".$user_id . " AND (action_type = 'store') ";

    $pTable = "{$wpdb->prefix}posts";
    $sQuery = "
    SELECT SQL_CALC_FOUND_ROWS 
      t1.*, t2.post_title 
    FROM
        (
          SELECT `".str_replace(" , ", " ", implode("`, `", $aColumns))."`
          FROM   $sTable
          $sWhere
          $sOrder
        ) AS t1
      INNER JOIN $pTable AS t2 ON t1.source_id = t2.ID
      $sWhere2
      $sLimit
      ";

    $rResult = $wpdb->get_results($sQuery, ARRAY_A);

    $sQuery = "SELECT FOUND_ROWS()";

    $rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iFilteredTotal = $rResultFilterTotal [0];

    $sQuery = "
      SELECT COUNT(`".$sIndexColumn."`)
      FROM   $sTable
      leftjoin
      $totalWhere
     ";

    $rResultTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iTotal = $rResultTotal [0];

    $output = array(
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );

    foreach($rResult as $action){//output a row for each task
        $row = array();
        ///////////
        ///
        $TIMESTAMP = $action['TIMESTAMP'];
        $title = $action['post_title'];
        $qnt = $action['stage'];

        //$unix_time = strtotime($TIMESTAMP);
        $row[] = "{$TIMESTAMP}";
        $row[] = "{$title}";
        $row[] = "{$qnt}";

        $go_loot_columns = go_loot_columns_stats($action);
        $row = array_merge($row, $go_loot_columns);

        $output['aaData'][] = $row;
    }

    echo json_encode( $output );
    die();
}

/**
 *
 */
function go_stats_messages() {

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_stats_messages' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_stats_messages' ) ) {
        echo "refresh";
        die( );
    }

    echo "<div id='go_messages' class='go_datatables'><table id='go_messages_datatable' class='pretty display'>
                   <thead>
						<tr>
						
							<th class='header'><a href=\"#\">Time</a></th>
							<th class='header'><a href=\"#\">Title</a></th>					
							<th class='header'><a href=\"#\">Message</a></th>";


    go_loot_headers();
    ?>
					</tr>
						</thead>

				</table></div>
    <?php

    die();
}

/**
 * go_messages_dataloader_ajax
 * Called for Server Side Processing from the JS
 */
function go_messages_dataloader_ajax(){

    global $wpdb;
    $go_action_table_name = "{$wpdb->prefix}go_actions";

    $aColumns = array( 'id', 'uid', 'action_type', 'TIMESTAMP' , 'result', 'global_mod', 'xp', 'gold', 'health', 'badges', 'groups' );

    $sIndexColumn = "id";
    $sTable = $go_action_table_name;

    $sLimit = '';

    if ( isset( $_GET['start'] ) && $_GET['length'] != '-1' )
    {
        $sLimit = "LIMIT ".intval( $_GET['start'] ).", ".
            intval( $_GET['length'] );
    }

    $sOrder = "ORDER BY TIMESTAMP desc"; //always in reverse order


    $search_val = $_GET['search']['value'];

    $user_id = $_GET['user_id'];

    $is_admin = go_user_is_admin();
    if($is_admin){
        $include_notes = "OR action_type = 'note'";
    }
    $sWhere = "WHERE uid = ".$user_id . " AND (action_type = 'message' OR action_type = 'reset' ". $include_notes. ") ";

    if ( isset($search_val) && $search_val != "" )
    {

        $sWhere .= " AND (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            $sWhere .= "`".$aColumns[$i]."` LIKE '%".esc_sql( $search_val )."%' OR ";
        }
        $sWhere = substr_replace( $sWhere, "", -3 );//removes the last OR


        $sWhere .= ')';

    }

    $totalWhere = " WHERE uid = ".$user_id . " AND (action_type = 'message' OR action_type = 'reset') ";

    $sQuery = "
      SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`
      FROM   $sTable
      $sWhere
      $sOrder
      $sLimit
      ";

    $rResult = $wpdb->get_results($sQuery, ARRAY_A);

    $sQuery = "SELECT FOUND_ROWS()";

    $rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iFilteredTotal = $rResultFilterTotal [0];

    $sQuery = "
      SELECT COUNT(`".$sIndexColumn."`)
      FROM   $sTable
      $totalWhere
     ";

    $rResultTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iTotal = $rResultTotal [0];

    $output = array(
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );

    foreach($rResult as $action){//output a row for each task
        $row = array();
        ///////////
        ///
        $action_type = $action['action_type'];
        $TIMESTAMP = $action['TIMESTAMP'];
        $result = $action['result'];
        $result_array = unserialize($result);
        $title = $result_array[0];
        $message = $result_array[1];

        //$unix_time = strtotime($TIMESTAMP);
        $row[] = "{$TIMESTAMP}";
        $row[] = "{$title}";
        $row[] = "{$message}";

        $go_loot_columns = go_loot_columns_stats($action);
        $row = array_merge($row, $go_loot_columns);
        $output['aaData'][] = $row;
    }

    echo json_encode( $output );
    die();
}

//History table
/**
 *
 */
function go_stats_activity_list() {
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_stats_activity_list_' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_stats_activity_list' ) ) {
        echo "refresh";
        die( );
    }

    echo "<div id='go_activity_list' class='go_datatables'><table id='go_activity_datatable' class='pretty display'>
                   <thead>
						<tr>
						
							<th class='header'><a href=\"#\">Time</a></th>
							<th class='header'><a href=\"#\">Type</a></th>
							
							<th class='header'><a href=\"#\">Item</a></th>
							<th class='header'><a href=\"#\">Action</a></th>
							<th class='header'><a href=\"#\">Modifiers</a></th>";


    go_loot_headers();
    go_loot_headers(true);


    echo "</tr>
        </thead>
				</table></div>";

    die();
}

/**
 * go_activity_dataloader_ajax
 * Called for Server Side Processing from the JS
 */
function go_activity_dataloader_ajax(){

    global $wpdb;
    $go_action_table_name = "{$wpdb->prefix}go_actions";

    $aColumns = array( 'id', 'uid', 'action_type', 'source_id', 'TIMESTAMP' ,'stage', 'bonus_status', 'check_type', 'result', 'quiz_mod', 'late_mod', 'timer_mod', 'global_mod', 'xp', 'gold', 'health', 'xp_total', 'gold_total', 'health_total', 'badges', 'groups' );

    $sIndexColumn = "id";
    $sTable = $go_action_table_name;

    $sLimit = '';

    if ( isset( $_GET['start'] ) && $_GET['length'] != '-1' )
    {
        $sLimit = "LIMIT ".intval( $_GET['start'] ).", ".
            intval( $_GET['length'] );
    }

    $sOrder = "ORDER BY TIMESTAMP desc"; //always in reverse order

    $sWhere = "";
    $search_val = $_GET['search']['value'];

    if ( isset($search_val) && $search_val != "" )
    {
        $sWhere = "WHERE (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            $sWhere .= "`".$aColumns[$i]."` LIKE '%".esc_sql( $search_val )."%' OR ";
        }
        $sWhere = substr_replace( $sWhere, "", -3 );


        $posts_table_name = "{$wpdb->prefix}posts";

        $tWhere = " WHERE post_title LIKE '%".$search_val."%'";
        $task_id_query = "
                SELECT ID
          FROM $posts_table_name
          $tWhere
        
        ";
        $task_ids = $wpdb->get_results($task_id_query, ARRAY_A);
        if(is_array($task_ids)){
            $sWhere .= "OR ";

            foreach ($task_ids as $task_id){
                $sWhere .= "`source_id` LIKE '%".esc_sql( $task_id['ID'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
        }
        $sWhere .= ')';

    }


    //add the filter by UID
    $user_id = $_GET['user_id'];
    if($user_id != ''){
        if ( $sWhere == "" )
        {
            $sWhere = "WHERE ";
        }
        else
        {
            $sWhere .= " AND ";
        }
        $sWhere .= "uid = ".$user_id . " AND NOT action_type = 'admin_notification'";
    }

    $totalWhere = " WHERE uid = ".$user_id . " AND NOT action_type = 'admin_notification'";


    $sQuery = "
    
      SELECT SQL_CALC_FOUND_ROWS `" . str_replace(" , ", " ", implode("`, `", $aColumns)) . "`
      FROM   $sTable
      $sWhere
      $sOrder
      $sLimit
      
      ";

    $rResult = $wpdb->get_results($sQuery, ARRAY_A);

    $sQuery = "
  SELECT FOUND_ROWS()
 ";
    $rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N);
    $iFilteredTotal = $rResultFilterTotal [0];

    $sQuery = "
  SELECT COUNT(`".$sIndexColumn."`)
  FROM   $sTable
  $totalWhere
 ";
    $rResultTotal = $wpdb->get_results($sQuery, ARRAY_N);
    $iTotal = $rResultTotal [0];

    $output = array(
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );

    foreach($rResult as $action){//output a row for each task
        $row = array();

        $action_type = $action['action_type'];
        $source_id = $action['source_id'];
        $TIMESTAMP = $action['TIMESTAMP'];
        $stage = $action['stage'];
        $bonus_status = $action['bonus_status'];
        $result = $action['result'];
        $quiz_mod = $action['quiz_mod'];
        $late_mod = $action['late_mod'];
        $timer_mod = $action['timer_mod'];
        $health_mod = $action['global_mod'];

        $xp_total = $action['xp_total'];
        $gold_total = $action['gold_total'];
        $health_total = $action['health_total'];

        $post_title = get_the_title($source_id);

        $type="";
        $action_title="";

        if ($action_type == 'store'){
            $store_qnty = $stage;
            $type = ucfirst( get_option( 'options_go_store_name' ) );
            $action_title = "Qnt: " . $store_qnty ;
        }
        else if ($action_type == 'task'){
            $type = ucfirst( get_option( 'options_go_tasks_name_singular' ) );
            if ($bonus_status == 0) {
                //$type = strtoupper( get_option( 'options_go_tasks_name_singular' ) );
                //$type = "Continue";
                $action_title = "Stage: " . $stage;
            }
            else{
                $action_title = "Bonus: " . $bonus_status;
            }
        }
        else if ($action_type == 'feedback'){
            $type = ucfirst($action_type);
            $result_array = unserialize($result);
            $title = $result_array[0];
            $message = $result_array[1];
            $message = $title . " <br><br>" . $message;
            $action_title = '<a href="javascript:void(0)"><span class="tooltip" data-tippy-content="'. $message .'">Feedback Stage: ' . $stage. '</span></a>';
           // $action =' <span class="tooltip" data-tippy-content="'. $message .'">See Message</span>';
        }
        else if ($action_type == 'undo_task'){
            $type = ucfirst( get_option( 'options_go_tasks_name_singular' ) );
            if ($bonus_status == 0) {
                //$type = strtoupper( get_option( 'options_go_tasks_name_singular' ) ) . " Undo";
                //$type = "Undo";
                $action_title = "Undo Stage: " . $stage;
            }
        }
        else if ($action_type == 'message' || $action_type == 'reset' || $action_type == 'attendance'){
            $type = ucfirst($action_type);
            $result_array = unserialize($result);
            $title = $result_array[0];
            $message = $result_array[1];
            $message = $title . ": <br>" . $message;
            //$action = "<span class='tooltip' ><span class='tooltiptext'>{$message}</span>See Message</span>  ";
            $action_title =' <span class="tooltip" data-tippy-content="'. $message .'">See Message</span>';
        }
        else if ($action_type == 'bonus_loot'){
            $type = "Bonus Loot";
            $action_title = $result;
        }
        else if ($action_type == 'undo_bonus_loot'){
            $type = "Undo Bonus Loot";
            $action_title = $result;
        }else{
            continue;
        }

        if ($result == 'undo_bonus'){
            //$type = strtoupper( get_option( 'options_go_tasks_name_singular' ) ) . " Undo Bonus";
            //$type = "Undo Bonus";
            $action_title = "Undo Bonus: " . $bonus_status ;
        }

        $quiz_mod_int = intval($quiz_mod);
        if (!empty($quiz_mod_int)){
            $quiz_mod = "<i class=\"fa fa-check-circle-o\" aria-hidden=\"true\"></i> ". $quiz_mod;
        }
        else{
            $quiz_mod = null;
        }

        $late_mod_int = intval($late_mod);
        if (!empty($late_mod_int)){
            $late_mod = "<i class=\"fa fa-calendar\" aria-hidden=\"true\"></i> ". $late_mod;
        }
        else{
            $late_mod = null;
        }

        $timer_mod_int = intval($timer_mod);
        if (!empty($timer_mod_int)){
            $timer_mod = "<i class=\"fa fa-hourglass\" aria-hidden=\"true\"></i> ". $timer_mod;
        }
        else{
            $timer_mod = null;
        }

        $health_mod_int = $health_mod;
        if (!empty($health_mod_int)){
            $health_abbr = get_option( "options_go_loot_health_abbreviation" );
            $health_mod_str = $health_abbr . ": ". $health_mod;
        }
        else{
            $health_mod_str = null;
        }
        //$unix_time = strtotime($TIMESTAMP);
        $row[] = "{$TIMESTAMP}";
        $row[] = "{$type}";
        $row[] = "{$post_title}";
        $row[] = "{$action_title}";
        $row[] = "{$health_mod_str}   {$timer_mod}   {$late_mod}   {$quiz_mod}";


        $go_loot_columns = go_loot_columns_stats($action);
        $row = array_merge($row, $go_loot_columns);

        $xp_toggle = get_option('options_go_loot_xp_toggle');
        $gold_toggle = get_option('options_go_loot_gold_toggle');
        $health_toggle = get_option('options_go_loot_health_toggle');

        if ($xp_toggle){
            $row[] = "{$xp_total}";
        }
        if ($gold_toggle){
            $row[] = "{$gold_total}";
        }
        if ($health_toggle){
            $row[] = "{$health_total}";
        }
        $output['aaData'][] = $row;
    }

    echo json_encode( $output );
    die();
}


function go_update_badge_group_sort(){
    $is_admin_user = go_user_is_admin();
    if(!$is_admin_user){
        echo "not admin";
        die();
    }

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_update_badge_group_sort' ) ) {
        echo "refresh";
        die( );
    }

    if(empty($_POST) || !isset($_POST)) {
        ajaxStatus('error', 'Nothing to update.');
    } else {
        $terms = $_POST['terms'];
        $parent_id = $_POST['term_id'];
        $taxonomy = $_POST['taxonomy'];
        $i = 0;
        foreach($terms as $term){
            $i++;
            //$obj = get_term($term);
            wp_update_term(intval($term),$taxonomy, $args = array(
                'parent' => $parent_id,));
            update_term_meta(intval($term), 'choose_category', $parent_id);
            update_term_meta(intval($term), 'go_order', $i);


            $key = 'go_term_data_' . $term;
            go_delete_transient($key);

            $key = 'go_get_child_term_ids_' . $term;
            go_delete_transient($key);
        }

        $key = 'go_term_data_' . $parent_id;
        go_delete_transient($key);

        $key = 'go_term_data_' . $parent_id;
        go_delete_transient($key);




        echo json_encode(
            array(
                'json_status' => 302
            )
        );
        die();
    }
}


function go_update_badges_page(){
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_update_badges_page' ) ) {
        echo "There was an error.  Please refresh the page.";
        die( );
    }

    if(empty($_POST) || !isset($_POST)) {
        ajaxStatus('error', 'Nothing to update.');
    } else {
        try {
            $taxonomy = $_POST['taxonomy'];

            if($taxonomy === 'go_badges'){
                go_stats_badges_list(true, 'edit');
            }
            else if($taxonomy === 'user_go_groups'){
                go_stats_groups_list(true, 'edit');
            }




            die();
        } catch (Exception $e){
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
}