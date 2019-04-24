<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-04-06
 * Time: 23:21
 */

function go_reader_get_posts($sQuery2 = null, $pWhere = null, $order = null)
{
    global $wpdb;


    $uWhere = go_reader_uWhere_values();

    if(!isset($pWhere)) {
        $pWhere = go_reader_pWhere();
    }

    //$sOrder = go_sOrder('tasks', $section);

    $sOn = go_reader_sOn('tasks');
    //add store items to On statement
    $tasks = (isset($_GET['tasks']) ? $_GET['tasks'] : '');
    //$tasks = $_GET['tasks'];
    if (isset($tasks) && !empty($tasks)) {
        $sOn .= " AND (";
        for ($i = 0; $i < count($tasks); $i++) {
            $task = intval($tasks[$i]);
            $sOn .= "t4.post_id = " . $task . " OR ";
        }
        $sOn = substr_replace($sOn, "", -3);
        $sOn .= ")";
    }

    $lTable = "{$wpdb->prefix}go_loot";
    //$aTable = "{$wpdb->prefix}go_actions";
    $uTable = "{$wpdb->prefix}users";
    $umTable = "{$wpdb->prefix}usermeta";
    //$tTable = "{$wpdb->prefix}posts";
    $pTable = "{$wpdb->prefix}posts";

    //$order = $_POST['order'];
    if(!isset($order)) {
        $order = (isset($_POST['order']) ? $_POST['order'] : 'ASC');
    }

    //$limit = intval($_POST['limit']);
    $limit = (isset($_POST['limit']) ? $_POST['limit'] : 10);


    $sQuery1 = "SELECT SQL_CALC_FOUND_ROWS
            t4.ID, t4.post_status";
    if (!isset($sQuery2)) {
        $sQuery2 = "
          FROM (
              SELECT
              t1.uid, t1.badges, t1.groups,    
              t3.display_name, t3.user_url, t3.user_login,
              MAX(CASE WHEN t2.meta_key = 'first_name' THEN meta_value END) AS first_name,
              MAX(CASE WHEN t2.meta_key = 'last_name' THEN meta_value END) AS last_name,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat' THEN meta_value END) AS num_section,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat_0_user-section' THEN meta_value END)  AS section_0,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat_1_user-section' THEN meta_value END) AS section_1,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat_2_user-section' THEN meta_value END) AS section_2,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat_3_user-section' THEN meta_value END) AS section_3,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat_4_user-section' THEN meta_value END) AS section_4,
              MAX(CASE WHEN t2.meta_key = 'go_section_and_seat_5_user-section' THEN meta_value END) AS section_5
              FROM $lTable AS t1 
              LEFT JOIN $umTable AS t2 ON t1.uid = t2.user_id
              LEFT JOIN $uTable AS t3 ON t2.user_id = t3.ID
              GROUP BY t1.id
              $uWhere
          ) AS t5
          INNER JOIN {$pTable} AS t4 ON t5.uid = t4.post_author $sOn
        ";
    }

        //$sQuery3 = $sQuery1 . $sQuery2 . $pWhere . " ORDER BY t4.post_modified " . $order;

        $sQuery = $sQuery1 . $sQuery2 . $pWhere . " ORDER BY t4.post_modified " . $order . " LIMIT " . $limit;



    //LIMIT $limit
    //ORDER BY OPTIONS, OLDEST/NEWEST
    //LIMIT to 10 (or option)--Read more to show the next 10.
    //CHANGE: DON'T LIMIT SEARCH.  GET ALL IDS BUT THEN ONLY SHOW FIRST FEW BELOW.
    // How to know what to load next.  What if new items were submitted?
    //  3 buttons: Mark all as read, Mark as read and load more, Load More.
    //  And buttons on individual items to mark as read.
    //  Mark sure that Mark all as read gets all post_ids with jQuery.

    ////columns that will be returned
    $posts = $wpdb->get_results($sQuery, ARRAY_A);
    $posts_array = array_column($posts, 'ID');
    //$status_array = array_column($posts, 'post_status');
    //$counts = array_count_values($status_array);
    //$unread = (isset($counts['unread']) ?  $counts['unread'] : null);
    //$unread = $counts['unread'];

    //$posts_serialized = json_encode($posts_array);
    //$posts_array = json_decode($posts_serialized);

    $fQuery = "SELECT FOUND_ROWS()";

    $rResultFilterTotal = $wpdb->get_results($fQuery, ARRAY_N);
    $iFilteredTotal = $rResultFilterTotal [0][0];


    $tQuery1 = " SELECT COUNT(*) ";
    $pWhere2 = " WHERE ((t4.post_type = 'go_blogs') AND (t4.post_status = 'unread')) ";
    $tQuery = $tQuery1 . $sQuery2 . $pWhere2;

    $TotalunRead = $wpdb->get_results($tQuery, ARRAY_N);
    $TotalunRead = $TotalunRead[0][0];


    //if ($iFilteredTotal < 1000 ) {
    echo "<div id='go_post_found' style='width: 600px;float:left;'>";
            if ($TotalunRead != $iFilteredTotal){
                echo "<span>{$iFilteredTotal} posts found. </span>";
            }
            if($TotalunRead>0){
                echo "
                 <span>{$TotalunRead} unread posts found. </span><span><a  id='go_mark_all_read' >Mark all as read</a>.</span>";//data-post_ids='$posts_serialized
            }

        echo "</div>";
    //$sQuerynoLimit = $totalQuery;
    //$sQuerynoLimit = 5;
    ?>
    <br>
    <br>
    <div style="float: right;clear:both;">
        Posts per page
        <select id="go_num_posts" class="go_num_posts" name="postNum" data-where="<?php echo $pWhere;?>"  data-order="<?php echo $order;?>" data-query="<?php echo $sQuery2;?>" >
            <option value="10" <?php if($limit == 10) {echo 'selected';}?>>10</option>
            <option value="25" <?php if($limit == 25) {echo 'selected';}?>>25</option>
            <option value="50" <?php if($limit == 50) {echo 'selected';}?>>50</option>
        </select>
    </div>
    <?php

    /*}else {
        echo "<div>
            Over 1000 unread posts found ";
        if ($unread) {

            echo "
                <br>{$unread} unread posts found in the first 1000 posts returned.  
                <br><a><span id='go_mark_all_read' data-post_ids='$posts_serialized'>Mark these {$unread} posts as read and reload the reader.</span></a>";
        }
        echo "</div>";
    }*/



    $i = 0;
    echo "<div style='clear:both'></div>";
    foreach ($posts_array as $post){
        if ($i == $limit){
            break;
        }
        $i++;
        //$post_id = get the_id();
        //$blog_post_id = $post['ID'];
        go_blog_post($post, null, false, true, true, false);
    }


    wp_localize_script( 'go_loadmore', 'misha_loadmore_params', array(
        'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
        //'posts' => json_encode( $go_query->query_vars ), // everything about your loop is here
        'current_page' => get_query_var( 'paged' ) ? get_query_var('paged') : 1,
        //'max_page' => $go_query->max_num_pages
    ) );

    wp_enqueue_script( 'go_loadmore' );

    //die();
}

function go_reader_uWhere_values(){
    //CREATE THE QUERY
    //CREATE THE USER WHERE STATEMENT
    //check the drop down filters only
    //Query 1:
    //WHERE (uWhere)
    //User_meta by section_id from the drop down filter
    //loot table by badge_id from drop down filter
    //and group_id from the drop down filter.

    $section = (isset($_POST['section']) ?  $_POST['section'] : '');
    $badge = (isset($_POST['badge']) ?  $_POST['badge'] : '');
    $group = (isset($_POST['group']) ?  $_POST['group'] : '');
    //$section = $_POST['section'];
    //$badge = $_POST['badge'];
    //$group = $_POST['group'];

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
        $uWhere .= ")";
    }
    return $uWhere;
}

//NOT USED
function go_reader_section(){
    $section = (isset($_POST['section']) ?  $_POST['section'] : '');
    //$section = $_POST['section'];
    if ($section == ""){
        $section = 0;
    }
    //if (is_array($sections) && count($sections) === 1){
    //    $section = $sections[0];
    //}
    return $section;
}

function go_reader_sOn($action_type){
    $sOn = "";
    //$date = $_POST['date'];
    $date = (isset($_POST['date']) ?  $_POST['date'] : '');


    if (isset($date) && $date != "") {
        $dates = explode(' - ', $date);
        $firstdate = $dates[0];
        $lastdate = $dates[1];
        $firstdate = date("Y-m-d", strtotime($firstdate));
        $lastdate = date("Y-m-d", strtotime($lastdate));
        $date = " AND ( DATE(t4.post_modified) BETWEEN '" . $firstdate . "' AND '" . $lastdate . "')";
    }
    $sOn .= $date;

    return $sOn;
}

function go_reader_pWhere(){

    $include_read = (isset($_POST['read']) ?  $_POST['read'] : 'false');
    $include_unread = (isset($_POST['unread']) ?  $_POST['unread'] : 'true');
    $include_reset = (isset($_POST['reset']) ?  $_POST['reset'] : 'false');
    $include_trash = (isset($_POST['trash']) ?  $_POST['trash'] : 'false');
    $include_draft = (isset($_POST['draft']) ?  $_POST['draft'] : 'false');

    //$include_read = $_POST['read'];
    //$include_unread = $_POST['unread'];
    //$include_reset = $_POST['reset'];
    //$include_trash = $_POST['trash'];
    //$include_draft = $_POST['draft'];
    //$pWhere = "";
    $pWhere = "WHERE ((t4.post_type = 'go_blogs')";

    /*
    $pWhere = array();

    if ($include_read){
        $pWhere[] = 'read';
    }
    if ($include_unread){
        $pWhere[] = 'unread';
    }
    if ($include_reset){
        $pWhere[] = 'reset';
    }
    if ($include_trash){
        $pWhere[] = 'trash';
    }
    */

    if ($include_read === 'true' || $include_unread === 'true' || $include_reset === 'true' || $include_trash === 'true' || $include_trash === 'draft'  )
    {
        $pWhere .= " AND (";
        $first = true;

        if ($include_read === 'true'){
            $pWhere .= "(t4.post_status = 'read')";
            $first = false;
        }
        if ($include_unread === 'true'){
            if (!$first){
                $pWhere .= " OR " ;
            }
            $pWhere .= "(t4.post_status = 'unread')";
            $first = false;
        }
        if ($include_reset === 'true'){
            if (!$first){
                $pWhere .= " OR " ;
            }
            $pWhere .= "(t4.post_status = 'reset')";
            $first = false;
        }
        if ($include_trash === 'true'){
            if (!$first){
                $pWhere .= " OR " ;
            }
            $pWhere .= "(t4.post_status = 'trash')";
        }
        if ($include_draft === 'true'){
            if (!$first){
                $pWhere .= " OR " ;
            }
            $pWhere .= "(t4.post_status = 'draft')";
        }
        $pWhere .= ")";


    }

    $pWhere .= ")";

    return $pWhere;
}