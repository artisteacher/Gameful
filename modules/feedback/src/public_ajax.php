<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-04-06
 * Time: 23:21
 */
function go_make_reader($with_header = true, $order = null) {
    wp_localize_script( 'go_frontend', 'IsReader', 'true' );

    //echo "success";
    if($with_header) {
        go_leaderboard_filters('reader');
    }
    //video options
    $go_lightbox_switch = get_option( 'go_video_lightbox_toggle_switch' );
    if($go_lightbox_switch === false){
        $go_lightbox_switch = 1;
    }
    $go_video_unit = get_option ('go_video_width_type_control');
    if ($go_video_unit == '%'){
        $percent = get_option( 'go_video_width_percent_control' );
        if($percent === false){
            $percent = 100;
        }
        $go_fitvids_maxwidth = $percent."%";
    }else{
        $pixels = get_option( 'go_video_width_px_control' );
        if($pixels === false){
            $pixels = 400;
        }
        $go_fitvids_maxwidth = $pixels."px";
    }

    $user_id = get_current_user_id();
    $cards = get_user_option('go_use_cards', $user_id);
    if($cards == "true"){
        $checked = 'checked';
    }else{
        $checked = '';
    }
    echo "<div id='go_cards_toggle_wrapper'><input id='go_cards_toggle' type='checkbox' name='cards' style='display: inline;' $checked> View with Cards</div><br>";

    echo "<div id='go_wrapper' data-lightbox='{$go_lightbox_switch}' data-maxwidth='{$go_fitvids_maxwidth}' >";

    echo "
          <div id='go_posts_wrapper' >";
    go_reader_get_posts(null, null, $order);


    $initial = (isset($_GET['is_initial_single_stage']) ? $_GET['is_initial_single_stage'] : null);
    if($initial === 'true') {
        $post_id = (isset($_GET['post_id']) ? $_GET['post_id'] : null);
        $stage = (isset($_GET['stage']) ? $_GET['stage'] : null);
        $is_admin = go_user_is_admin();
        $admin_view = go_get_admin_view($user_id);
        if ($is_admin && (in_array($admin_view, array('admin', 'all')))) {
            echo "<div>";
            $post_slug = get_post_field('post_name', get_post());
            $path = "quest_posts?post_id={$post_id}&stage={$stage}";
            $url = get_site_url(null, $path, null);
            $message = 'Get a link to these posts on their own page. This can then be placed other content to come directly to submissions on this assignment.';
            echo "
            <span onclick='go_copy_to_clipboard(this)' class='tooltip action_icon' data-tippy-content='$message'>
                            <span class='tooltip_click' data-tippy-content='Copied!'>
                                <span  style='background-color: white; display:none;' class='go_copy_this '>$url</span> 
                                <a><i style='' class='far fa-1x fa-link'></i></a>
                            </span>
                    </span>
                    ";

           // echo "<span class='tooltip'  data-tippy-content=''><a href='{$url}'><i class='fas fa-link'></i></a></span>";
            echo "</div>";
        }
    }

    echo "</div></div>";


    //go_hidden_footer();
    //get_sidebar();
}
add_shortcode( 'go_make_reader','go_make_reader' );

//saved queries are passed back from JS to make the readmore functions
function go_reader_get_posts($sQuery2 = null, $pWhere = null, $order = null, $blog_user_id = false, $cards = null){

    global $wpdb;

    if($blog_user_id){
        $is_initial_blog = true;
    }else{
        $is_initial_blog = false;
    }

    $user_id = get_current_user_id();
    if($cards === null) {
        $cards = get_user_option('go_use_cards', $user_id);
    }
    $blog_user_id = (isset($_GET['user_id']) ? $_GET['user_id'] : $blog_user_id);

    if (!isset($pWhere)) {
        $pWhere = go_reader_pWhere($is_initial_blog );
    }
    //$pWhere =  "";
    //$sOrder = go_sOrder('tasks', $section);

    $sOn = go_reader_sOn();


    $lTable = "{$wpdb->prefix}go_loot";
    $pTable = "{$wpdb->prefix}posts";
    $pmTable = "{$wpdb->prefix}postmeta";

    $stage_limit = '';
    $stage = (isset($_GET['stage']) ? $_GET['stage'] : false);
    if($stage != "false" && $stage != "all" && $stage != "bonus" && !empty($stage)) {

        $stage_limit = "INNER JOIN {$pmTable} AS t8 ON t4.ID = t8.post_id AND t8.meta_key = 'go_stage_uniqueid' AND t8.meta_value = '" . $stage . "'";
    }
    else if ($stage === "bonus"){
        $stage_limit = "INNER JOIN {$pmTable} AS t8 ON t4.ID = t8.post_id AND t8.meta_key = 'go_stage_uniqueid' AND t8.meta_value IS NULL";
    }

    $private_limit = '';
    $is_admin = go_user_is_admin();
    if(!$is_admin) {
        $private_limit = "INNER JOIN {$pmTable} AS t9 ON t4.ID = t9.post_id AND t9.meta_key = 'go_blog_private_post' AND t9.meta_value = 0";
    }

    //$order = $_POST['order'];
    if (!isset($order)) {
        $order = (isset($_GET['order']) ? $_GET['order'] : 'ASC');
    }

    $limit = (isset($_GET['limit']) ? $_GET['limit'] : 10);

    $feedQuery = "";
    $action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
    $current_tab = (isset($_GET['current_tab']) ? $_GET['current_tab'] : '');
    if($action === "go_make_feed" || $current_tab === 'social_feed') {
        $show_all = (isset($_GET['show_all']) ? $_GET['show_all'] : "false");
        if($show_all === "false") {
            $go_following = go_prefix_key('go_following');
            $following = get_user_meta($user_id, $go_following);
            $ids = join("','", $following);
            $feedQuery = "WHERE uid IN ('$ids')";
        }
    }else if(is_numeric($blog_user_id)){
        $feedQuery = "WHERE uid IN ('$blog_user_id')";
    }


    $sectionQuery = go_sectionQuery();
    $badgeQuery = go_badgeQuery();
    $groupQuery = go_groupQuery();

    $sQuery1 = "SELECT SQL_CALC_FOUND_ROWS
                t4.ID, t4.post_status";

    if (!isset($sQuery2)) {
        $sQuery2 = "
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
                                                    $feedQuery
                                            ) AS t2
                                            $sectionQuery
                                ) AS t4
                                $badgeQuery
                    ) AS t6
                    $groupQuery
            ) AS t5
            INNER JOIN {$pTable} AS t4 ON t5.user_id = t4.post_author $sOn
            {$stage_limit}
            {$private_limit}
        ";
    }
//INNER JOIN {$pmTable} AS t10 ON t10.post_id = t9.ID
    //WHERE ((t10.meta_key = 'go_blog_private_post') AND (t10.meta_value = 0))

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

    $include_unread = (isset($_GET['unread']) ?  $_GET['unread'] : 'true');
    if ($include_unread === 'true') {
        $tQuery1 = " SELECT COUNT(*) ";
        $pWhere2 = " WHERE ((t4.post_type = 'go_blogs') AND (t4.post_status = 'unread')) ";
        $tQuery = $tQuery1 . $sQuery2 . $pWhere2;

        $TotalunRead = $wpdb->get_results($tQuery, ARRAY_N);
        //$TotalunRead = $TotalunRead[0][0];
        $TotalunRead = (isset($TotalunRead[0][0]) ?  $TotalunRead[0][0] : 0);
    }else{
        $TotalunRead = 0;
    }

    //if ($iFilteredTotal < 1000 ) {
    echo "<div id='go_post_found' style='width: 600px;float:left;'>";
    if ($TotalunRead != $iFilteredTotal) {
        echo "<span>{$iFilteredTotal} posts found. </span>";
    }
    if ($TotalunRead > 0 && $is_admin) {
        echo "
                 <span>{$TotalunRead} <i class=\"far fa-eye-slash\"></i> posts found. </span><span><a  id='go_mark_all_read'  data-task='go_mark_all_read' >Mark all as <i class=\"fas fa-eye\"></i>.</a></span>";//data-post_ids='$posts_serialized
    }
    if (intval($iFilteredTotal == 0)){
        echo "<div style='padding: 30px;'>No posts match the filter.</div>";//data-post_ids='$posts_serialized
    }

    echo "</div>";
    //$sQuerynoLimit = $totalQuery;
    //$sQuerynoLimit = 5;
    ?>
    <div style="float: right;">
        Posts per page
        <select id="go_num_posts" class="go_num_posts" name="postNum" data-where="<?php echo $pWhere; ?>" data-cards="<?php echo $cards; ?>"
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
    if($cards === "true"){
        echo "<div id='go_cards' class='go_cards' >";
    }
    foreach ($posts_array as $post) {
        if ($i == $limit) {
            break;
        }
        $i++;
        //$post_id = get the_id();
        //$blog_post_id = $post['ID'];
        if($cards === "true") {
            go_blog_post_cards($post, null, true);
        }else{
            go_blog_post($post, null, false, true, true, true);
        }
    }
    if($cards === "true"){
        echo "</div>";
    }
    echo "<div class='go_reader_footer' style='padding-bottom: 20px;' data-query='{$localize}' data-limit='{$limit}' data-cards='{$cards}'>";
    if($is_admin){
        echo "<div class='go_read_printed' style='overflow: auto;'>
                    <button id='go_read_printed_button'  data-task='go_read_printed' style='float: right; margin: 20px;'>Mark all <i class=\"far fa-eye-slash\"></i> posts on this page as <i class=\"fas fa-eye\"></i>.</button>
                </div>";
    }

    if ($iFilteredTotal > $limit){
        echo "<div class='misha_loadmore go_loadmore_reader' data-offset='1' data-limit='".$limit."' data-cards='".$cards."' data-task='go_loadmore_reader'>More posts</div>";
        $remaining = $iFilteredTotal - $limit;
        if ($remaining === 1){
            echo "<div style='width: 100%; text-align: center;' class='go_remaining'>{$remaining} Post remaining</div>";
        }else {
            echo "<div style='width: 100%; text-align: center;' class='go_remaining'>{$remaining} Posts remaining</div>";
        }
    }
    echo "</div>";

/*
    wp_localize_script( 'go_frontend', 'misha_loadmore_params', array(
        'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
    ) );*/

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

    $tasks = (isset($_GET['post_id']) ? $_GET['post_id'] : '');
    if(empty($tasks)){
        $slug = (isset($_GET['post']) ? $_GET['post'] : '');
        if(!empty($slug)){
            if ( $post = get_page_by_path( $slug , OBJECT, 'tasks' ) )
                $tasks = $post->ID;
        }
    }

    //$tasks = $_GET['tasks'];
    if (isset($tasks) && !empty($tasks)) {
        if(!is_array($tasks)){
           // $tasks[]= $tasks;
            $tasks = (array)$tasks;
        }

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

function go_reader_pWhere($is_initial_blog ){
    if(defined('DOING_AJAX') && DOING_AJAX) {//if this isn't ajax, show the unread posts--It is a blog or reader initial load
        $include_unread = 'false';//AJAX, don't include automatically--get from filter
        $include_read = (isset($_REQUEST['read']) ? $_REQUEST['read'] : 'false');

        $include_published = (isset($_REQUEST['published']) ? $_REQUEST['published'] : 'false');
        if($include_published === 'true'){
            $include_unread = 'true';
            $include_read = 'true';

        }
    }else if (!$is_initial_blog){//if not AJAX and not inital blog, it is the initial reader
        $include_unread = 'true';//Initial, include unread
        $include_read = 'false';
    }else{//is not ajax
        $include_unread = 'false';
        $include_read = 'true';
    }

    $include_read = (isset($_REQUEST['read']) ? $_REQUEST['read'] : $include_read);
    $include_unread = (isset($_REQUEST['unread']) ? $_REQUEST['unread'] : $include_unread);
    $include_reset = (isset($_REQUEST['reset']) ? $_REQUEST['reset'] : 'false');
    $include_trash = (isset($_REQUEST['trash']) ? $_REQUEST['trash'] : 'false');
    $include_draft = (isset($_REQUEST['draft']) ? $_REQUEST['draft'] : 'false');




    if($is_initial_blog) {
        $include_read = 'true';
        $include_unread = 'true';
    }

    $pWhere = "WHERE ((t4.post_type = 'go_blogs')";

    $is_social_feed = false;
    $type = (isset($_REQUEST['type']) ?  $_REQUEST['type'] : null);
    $action = (isset($_REQUEST['action']) ?  $_REQUEST['action'] : null);
    $is_initial_single_stage = (isset($_REQUEST['is_initial_single_stage']) ?  $_REQUEST['is_initial_single_stage'] : false);
    if($action === 'go_make_feed' || $type === 'leaderboard' || ($action === 'go_filter_reader' && $is_initial_single_stage === 'true' )){
        $is_social_feed = true;
        $is_admin = go_user_is_admin();
        if(!$is_admin || $is_social_feed){
            $pWhere .= " AND ((t4.post_status = 'read') OR (t4.post_status = 'unread')))";
            return $pWhere;
        }
    }

        if ($include_read === 'true' || $include_unread === 'true' || $include_reset === 'true' || $include_trash === 'true' || $include_draft === 'true') {
            $pWhere .= " AND (";
            $first = true;

            if ($include_read === 'true') {
                $pWhere .= "(t4.post_status = 'read')";
                $first = false;
            }
            if ($include_unread === 'true') {
                if (!$first) {
                    $pWhere .= " OR ";
                }
                $pWhere .= "(t4.post_status = 'unread')";
                $first = false;
            }
            if ($include_reset === 'true') {
                if (!$first) {
                    $pWhere .= " OR ";
                }
                $pWhere .= "(t4.post_status = 'reset')";
                $first = false;
            }
            if ($include_trash === 'true') {
                if (!$first) {
                    $pWhere .= " OR ";
                }
                $pWhere .= "(t4.post_status = 'trash')";
            }
            if ($include_draft === 'true') {
                if (!$first) {
                    $pWhere .= " OR ";
                }
                $pWhere .= "(t4.post_status = 'draft')";
            }
            $pWhere .= ")";


        }else{
            $pWhere .= " AND ((t4.post_status = 'none'))";
        }


    $pWhere .= ")";

    return $pWhere;
}

function go_loadmore_reader(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_reader_bulk_read' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_loadmore_reader' ) ) {
        echo "refresh";
        die( );
    }

    // prepare our arguments for the query
    //$args = json_decode( stripslashes( $_POST['query'] ), true );
    $limit = $_POST['limit'];
    $offset = $_POST['offset'];
    $query = $_POST['query'];
    $task = $_POST['task'];
    $query = htmlspecialchars_decode($query);
    $query = stripslashes($query);
    $cards = (isset($_POST['cards']) ?  $_POST['cards'] : "false");

    //$query = unserialize($query);

    if($task == "go_loadmore_reader") {
        $slimit = " LIMIT " . intval($limit) . " OFFSET " . intval(($offset * $limit));
    }else{
        $slimit = " LIMIT " . intval($limit);
    }

    if($task == "go_mark_all_read"){
        go_reader_bulk_read($query);
    }
    else if($task == "go_read_printed"){
        go_reader_read_printed();
    }

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
            if($cards == "true"){
                go_blog_post_cards($post, null, true);
            }else{
                go_blog_post($post, null, false, true, true, false);
            }

        }
        if($remaining <= 0) {
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

