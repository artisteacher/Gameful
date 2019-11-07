<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-04-06
 * Time: 23:21
 */


//saved queries are passed back from JS to make the readmore functions
function go_reader_get_posts($initial = false, $sQuery2 = null, $pWhere = null, $order = null){
    global $wpdb;


    if (!isset($pWhere)) {
        $pWhere = go_reader_pWhere($initial);
    }

    //$sOrder = go_sOrder('tasks', $section);

    $sOn = go_reader_sOn();

    $lTable = "{$wpdb->prefix}go_loot";
    $pTable = "{$wpdb->prefix}posts";

    //$order = $_POST['order'];
    if (!isset($order)) {
        $order = (isset($_GET['order']) ? $_GET['order'] : 'ASC');
    }

    $limit = (isset($_GET['limit']) ? $_GET['limit'] : 10);

    $sectionQuery = go_sectionQuery();
    $badgeQuery = go_badgeQuery();
    $groupQuery = go_groupQuery();

    $sQuery1 = "SELECT SQL_CALC_FOUND_ROWS
            t4.ID, t4.post_status";

    if (!isset($sQuery2)) {
        $sQuery2 = "
          FROM (
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
          ) AS t5
          INNER JOIN {$pTable} AS t4 ON t5.user_id = t4.post_author $sOn
        ";
    }


    //$sQuery3 = $sQuery1 . $sQuery2 . $pWhere . " ORDER BY t4.post_modified " . $order;

    $sQuery = $sQuery1 . $sQuery2 . $pWhere . " ORDER BY t4.post_modified " . $order . " LIMIT " . $limit;

    $localize = $sQuery1 . $sQuery2 . $pWhere . " ORDER BY t4.post_modified " . $order;
    $localize = htmlspecialchars( $localize, ENT_QUOTES );

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
    if(!empty($posts)) {
        $fQuery = "SELECT FOUND_ROWS()";

        $rResultFilterTotal = $wpdb->get_results($fQuery, ARRAY_N);
        $iFilteredTotal = $rResultFilterTotal [0][0];
    }else{
        $iFilteredTotal = 0;
    }



    $include_unread = (isset($_POST['unread']) ?  $_POST['unread'] : 'true');
    if ($include_unread === 'true') {
        $tQuery1 = " SELECT COUNT(*) ";
        $pWhere2 = " WHERE ((t4.post_type = 'go_blogs') AND (t4.post_status = 'unread')) ";
        $tQuery = $tQuery1 . $sQuery2 . $pWhere2;

        $TotalunRead = $wpdb->get_results($tQuery, ARRAY_N);
        $TotalunRead = $TotalunRead[0][0];
    }else{
        $TotalunRead = 0;
    }

    //if ($iFilteredTotal < 1000 ) {
    echo "<div id='go_post_found' style='width: 600px;float:left;'>";
    if ($TotalunRead != $iFilteredTotal) {
        echo "<span>{$iFilteredTotal} posts found. </span>";
    }
    if ($TotalunRead > 0) {
        echo "
                 <span>{$TotalunRead} <i class=\"far fa-eye-slash\"></i> posts found. </span><span><a  id='go_mark_all_read' >Mark all as <i class=\"fas fa-eye\"></i>.</a></span>";//data-post_ids='$posts_serialized
    }
    if (intval($iFilteredTotal == 0)){
        echo "<div style='padding: 30px;'>No posts match the filter.</div>";//data-post_ids='$posts_serialized
    }

    echo "end posts found</div>";
    //$sQuerynoLimit = $totalQuery;
    //$sQuerynoLimit = 5;
    ?>
    <br>
    <br>
    <div style="float: right;clear:both;">
        Posts per page
        <select id="go_num_posts" class="go_num_posts" name="postNum" data-where="<?php echo $pWhere; ?>"
                data-order="<?php echo $order; ?>" data-query="<?php echo $sQuery2; ?>">
            <option value="10" <?php if ($limit == 10) {
                echo 'selected';
            } ?>>10
            </option>
            <option value="25" <?php if ($limit == 25) {
                echo 'selected';
            } ?>>25
            </option>
            <option value="50" <?php if ($limit == 50) {
                echo 'selected';
            } ?>>50
            </option>
        </select>
    </div>
    <?php

    $i = 0;
    echo "<div style='clear:both'></div>";
    foreach ($posts_array as $post) {
        if ($i == $limit) {
            break;
        }
        $i++;
        //$post_id = get the_id();
        //$blog_post_id = $post['ID'];
        go_blog_post($post, null, false, true, true, false);
    }
    echo "<div class='go_reader_footer' style='height: 160px;'>
                <div class='go_read_printed' style='overflow: auto;'>
                    <button id='go_read_printed_button'  style='float: right; margin: 20px;'>Mark all <i class=\"far fa-eye-slash\"></i> posts on this page as <i class=\"fas fa-eye\"></i>.</button>
                </div>";
    if ($iFilteredTotal > $limit){
        echo "<div class='misha_loadmore go_loadmore_reader' data-offset='1' data-limit='".$limit."' data-query='".$localize."'>More posts</div>";
        $remaining = $iFilteredTotal - $limit;
        if ($remaining === 1){
            echo "<div style='width: 100%; text-align: center;' class='go_remaining'>{$remaining} Post remaining</div>";
        }else {
            echo "<div style='width: 100%; text-align: center;' class='go_remaining'>{$remaining} Posts remaining</div>";
        }

    }
    echo "</div>";


    wp_localize_script( 'go_frontend', 'misha_loadmore_params', array(
        'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
    ) );

    //wp_enqueue_script( 'go_loadmore' );

    //die();
}

function go_reader_sOn(){
    $sOn = "";
    //$date = $_POST['date'];
    $date = (isset($_GET['date']) ?  $_GET['date'] : '');


    if (isset($date) && $date != "") {
        $dates = explode(' - ', $date);
        $firstdate = $dates[0];
        $lastdate = $dates[1];
        $firstdate = date("Y-m-d", strtotime($firstdate));
        $lastdate = date("Y-m-d", strtotime($lastdate));
        $date = " AND ( DATE(t4.post_modified) BETWEEN '" . $firstdate . "' AND '" . $lastdate . "')";
    }
    $sOn .= $date;

    $tasks = (isset($_GET['tasks']) ? $_GET['tasks'] : '');
    //$tasks = $_GET['tasks'];
    if (isset($tasks) && !empty($tasks)) {
        $sOn .= " AND (";
        for ($i = 0; $i < count($tasks); $i++) {
            $task = intval($tasks[$i]);
            $sOn .= "t4.post_parent = " . $task . " OR ";
        }
        $sOn = substr_replace($sOn, "", -3);
        $sOn .= ")";
    }

    return $sOn;
}

function go_reader_pWhere($initial = false){

    $include_read = (isset($_GET['read']) ?  $_GET['read'] : 'false');
    $include_unread = (isset($_GET['unread']) ?  $_GET['unread'] : 'false');
    $include_reset = (isset($_GET['reset']) ?  $_GET['reset'] : 'false');
    $include_trash = (isset($_GET['trash']) ?  $_GET['trash'] : 'false');
    $include_draft = (isset($_GET['draft']) ?  $_GET['draft'] : 'false');

    if($initial){
        $include_unread = 'true';
    }

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

function go_loadmore_reader(){

    // prepare our arguments for the query
    //$args = json_decode( stripslashes( $_POST['query'] ), true );
    $limit = $_POST['limit'];
    $offset = $_POST['offset'];
    $query = $_POST['query'];
    $query = htmlspecialchars_decode($query);
    $query = stripslashes($query);
    //$query = unserialize($query);

    $slimit = " LIMIT " . intval($limit) ." OFFSET " . intval(($offset * $limit));

    $query .= $slimit;
    global $wpdb;
    $posts = $wpdb->get_results($query, ARRAY_A);
    $posts_array = array_column($posts, 'ID');

    $fQuery = "SELECT FOUND_ROWS()";

    $rResultFilterTotal = $wpdb->get_results($fQuery, ARRAY_N);
    $iFilteredTotal = $rResultFilterTotal [0][0];
    $printed  = intval($offset * $limit);
    $remaining = intval($iFilteredTotal) - intval((($offset + 1) * $limit));

    $i = 0;
    if ($printed < $iFilteredTotal){
        foreach ($posts_array as $post) {
            if ($i == $limit) {
                break;
            }
            $i++;
            //$post_id = get the_id();
            //$blog_post_id = $post['ID'];
            go_blog_post($post, null, false, true, true, false);
        }
        if($remaining < 0) {
            ?>
            <script>
                jQuery('.go_loadmore_reader').hide();
                jQuery('.go_remaining').hide();
            </script>
            <?php
        }else{

            if ($remaining === 1){
                //echo "<div style='width: 100%; text-align: center;'>{$remaining} Post remaining</div>";
                ?>
                <script>
                    jQuery('.go_remaining').html("<?php echo $remaining;?> Post remaining");
                </script>
                <?php
            }else {
                //echo "<div style='width: 100%; text-align: center;'>{$remaining} Posts remaining</div>";
                ?>
                <script>
                    jQuery('.go_remaining').html("<?php echo $remaining;?> Posts remaining");
                </script>
                <?php
            }


        }

    }




    die;
}

