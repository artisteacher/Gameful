<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-05-27
 * Time: 19:30
 */

/* Template Include */
add_filter('template_include', 'go_badges_template_include', 1, 1);
function go_badges_template_include($template)
{
    if(is_gameful() && is_main_site()){
        $hide = true;
    }
    else{
        $hide = false;
    }
    if (!$hide) {
        global $wp_query; //Load $wp_query object
        //$store_name = get_option('options_go_store_store_link');

        $page_value = (isset($wp_query->query['pagename']) ? $wp_query->query['pagename'] : false); //Check for query var "blah"
        $badges_page = "edit_" . lcfirst(get_option('options_go_badges_name_plural'));
        $groups_page = "edit_" . lcfirst(get_option('options_go_groups_name_plural'));
        if (in_array($page_value, array($badges_page, $groups_page))) { //Verify "blah" exists and value is "true".
            return plugin_dir_path(__FILE__) . 'templates/badges_template.php'; //Load your template or file
        }
    }
    return $template; //Load normal template when $page_value != "true" as a fallback
}



/**
 * @param $skip_ajax_checks
 * @param $user_id
 *
 */
function go_stats_badges_list($skip_ajax_checks = false, $user_id = null) {
    if(!$skip_ajax_checks) {
        if (!is_user_logged_in()) {
            echo "login";
            die();
        }

        //check_ajax_referer( 'go_stats_badges_list_' );
        if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_stats_badges_list')) {
            echo "refresh";
            die();
        }
    }

    if($user_id === 'edit'){
        $user_id = null;
        $show_edit_button = false;
    }else {
        $user_id = (isset($_POST['user_id']) ? (int)$_POST['user_id'] : null);
        if (empty($user_id)) {
            $user_id = get_current_user_id();
        }
        $show_edit_button = true;
    }



    /* Get all task chains with no parents--these are the badge categories.  */
    $taxonomy = 'go_badges';
    //$badges_name = get_option('options_go_badges_name_plural');

    //$rows = go_get_terms_ordered($taxonomy, '0');
    $rows = go_get_parent_term_ids($taxonomy);
    echo"<div id='go_badges_list' class='go_datatables'> ";

    if(go_user_is_admin() && $show_edit_button){
        echo "<span class='go_map_action_icons ' style='right: 40px; z-index: 100; font-size: 1.3em;'>";

        echo "<div class='my_tooltip'><div class='go_actions_wrapper_flex'>";
        echo "<span class='tools task_tools'>";

        $link = go_get_link_from_option('options_go_badges_name_plural', true);
        echo "<span class='go_edit_frontend_badge action_icon'><a href='$link'><i class='far fa-edit'></i></a></span>";



        echo "</span></div></div></span>";
    }

    // For each Store Category with no parent.
    $chainParentNum = 0;
    echo '<div id="go_stats_badges">';
    //for each row
    foreach ( $rows as $row ) {
        $chainParentNum++;
        $row_id = $row->term_id;//id of the row

        //$badges = go_get_terms_ordered($taxonomy, $row_id);
        $badges = go_get_child_term_ids($row_id, $taxonomy);

        if(empty($badges)){
            // continue;
        }

        $name = '';
        $data = go_term_data($row_id);
        $custom_fields = $data[1];
        $hidden = (isset($custom_fields['go_hidden'][0]) ?  $custom_fields['go_hidden'][0] : false);
        if($hidden && ($user_id === null)){
            $name .= "Hidden: ";
        }

        $name .= $data[0];

        echo 	"<div class='parent_cat' data-term_id='$row_id' >
                        <div id='row_$chainParentNum' class='badges_row_container go_show_actions' style='display: inline-block;'>
						    <h3>$name</h3>";

        go_add_action_icons($row_id, $taxonomy, true);
        echo "	</div>
					    <div class='badges_row badges_row_$row_id' data-taxonomy='go_badges' data-term_id='$row_id'>
						";//row title and row container



        /*Loop for each chain.  Prints the chain name then looks up children (quests). */
        $badge_blocks = '';

        foreach ( $badges as $badge) {
            $badge_id = $badge;

            go_print_single_badge( $badge_id, 'badge', true, $user_id , 'stats');
        }
        echo "</div></div>";
    }
    echo "</div></div>";
    if(!$skip_ajax_checks) {
        die();
    }
}


/**
 *
 * @param $skip_ajax_checks
 * @param $user_id
 */
function go_stats_groups_list($skip_ajax_checks = false, $user_id = null) {
    if(!$skip_ajax_checks) {
        if ( !is_user_logged_in() ) {
            echo "login";
            die();
        }

        //check_ajax_referer( 'go_stats_groups_list_' );
        if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_stats_groups_list' ) ) {
            echo "refresh";
            die( );
        }
    }


    if($user_id === 'edit'){
        $user_id = null;
        $show_edit_button = false;
    }else {
        $user_id = (isset($_POST['user_id']) ? (int)$_POST['user_id'] : null);
        if (empty($user_id)) {
            $user_id = get_current_user_id();
        }
        $show_edit_button = true;
    }


    /* Get all task chains with no parents--these are the sections of the page.  */
    $taxonomy = 'user_go_groups';
    //$rows = go_get_terms_ordered($taxonomy, '0');
    $rows = go_get_parent_term_ids($taxonomy);

    echo"<div id='go_groups_list' class='go_datatables' >";

    if(go_user_is_admin() && $show_edit_button){
        echo "<span class='go_map_action_icons ' style='right: 40px; z-index: 100; font-size: 1.3em;'>";

        echo "<div class='my_tooltip'><div class='go_actions_wrapper_flex'>";
        echo "<span class='tools task_tools'>";

        $link = go_get_link_from_option('options_go_groups_name_plural', true);
        echo "<span class='go_edit_frontend_badge action_icon'><a href='$link'><i class='far fa-edit'></i></a></span>";



        echo "</span></div></div></span>";
    }



    /* For each Store Category with no parent, get all the children.  These are the store rows.*/
    $chainParentNum = 0 ;
    echo '<div id="go_groups">';

    foreach ( $rows as $row ) {
        $chainParentNum++;
        $row_id = $row->term_id;//id of the row

        //$groups = go_get_terms_ordered($taxonomy, $row_id);
        $groups = go_get_child_term_ids($row_id, $taxonomy);
        if(empty($groups)){
           // continue;
        }

        $name = '';
        $data = go_term_data($row_id);
        $custom_fields = $data[1];
        $hidden = (isset($custom_fields['go_hidden'][0]) ?  $custom_fields['go_hidden'][0] : false);
        if($hidden && ($user_id === null)){
            $name .= "Hidden: ";
        }

        $name .= $data[0];

        echo 	"<div class='parent_cat' data-term_id='$row_id'>
                        <div id='row_$chainParentNum' class='badges_row_container go_show_actions' style='display: inline-block;'>
						    <h3>$name</h3>";

        go_add_action_icons($row_id, $taxonomy, true);

        echo "</div>
					    <div class='badges_row badges_row_$row_id' data-taxonomy='user_go_groups' data-term_id='$row_id'>
						";//row title and row container

        /*Loop for each chain.  Prints the chain name then looks up children. */
        foreach ( $groups as $group) {
            $id = $group;
            go_print_single_badge( $id, 'group', $output = true, $user_id, 'stats' );
        }
        echo "</div></div>";
    }
    echo "</div></div>";
    if(!$skip_ajax_checks) {
        die();
    }
}

function go_add_action_icons($term_id, $taxonomy, $parent){
    if($taxonomy === 'go_badges') {
        $name_plural = 'badges';
    }else{
        $name_plural = 'groups';
    }
    $term_data = go_term_data($term_id);
    $term_name = $term_data[0];
    $custom_fields = $term_data[1];
    $is_hidden = (isset($custom_fields['go_hidden'][0]) ?  $custom_fields['go_hidden'][0] : null);

    echo "<div class='actions_tooltip' style='display: none;'>";


    echo "<div class='my_tooltip'><div class='go_actions_wrapper_flex'>";
    echo "<span class='tools task_tools'>";
    if(is_gameful()){
        do_action('gop_add_importer_icon', $term_id, 'term', null, false);
    }
    if(!$parent) {
        echo "<span class='go_edit_frontend action_icon actiontip' data-tippy-content='Edit this section.' data-taxonomy='$taxonomy' data-term_id='$term_id'><a><i class='far fa-edit'></i></a></span>";
    }else{
        echo "<span class='go_edit_frontend action_icon actiontip' data-new_child_term='$term_id' data-term_name='$term_name' data-tippy-content='Add a new item.' data-taxonomy='$taxonomy' ><a><i class='far fa-plus-circle'></i></a></span>";

    }
    echo "<span class='go_quick_edit_show action_icon actiontip' data-tippy-content='Quick edit.' data-taxonomy='$taxonomy' data-term_id='$term_id'><a><i class='far fa-bolt'></i></a></span>";

    echo "<span class='go_trash_post action_icon actiontip' data-tippy-content='Trash this section and all the $name_plural in it.' data-taxonomy='$taxonomy' data-term_id='{$term_id}' data-title='{$term_name}'><a><i class='far fa-trash'></i></a></span>";

    echo "</span>";

    ?>
    <span class="quickedit_form_container" style="display: none;">
                    <span class="quickedit_form" data-term_id='<?php echo $term_id; ?>'>
                        <div style="display: block;">

                            <input type="text" class="term_title" name="term_title" size="30" value="<?php echo htmlspecialchars($term_name); ?>">
                                    <span class="checkbox" style="padding: 10px;">
                                    <label for="hidden">  Hidden </label>
                                    <input type="checkbox" class="hidden_checkbox" name="hidden"
                                        <?php if ($is_hidden) {
                                            echo 'checked';
                                        } ?>>
                                    </span>

                        </div>
                    </span>
                </span>

    <?php

    echo "</div></div></div>";
}

function go_stats_header($user_id, $show_stats_link = true, $show_internal_links = true, $show_blog_link = true, $is_blog = false, $show_loot = false ){

    ?>
    <input type="hidden" id="go_stats_hidden_input" value="<?php echo $user_id; ?>"/>
    <?php
    $user_data = get_userdata( $user_id );

    $current_user_id = get_current_user_id();
    $is_admin = go_user_is_admin();
    $full_name_toggle = get_option('options_go_full-names_toggle');
    $user_fullname = $user_data->first_name.' '.$user_data->last_name;

    $user_login =  $user_data->user_login;

    $user_display_name = go_get_user_display_name($user_id);
    //$user_website = $user_data->user_url;


    //$user_avatar_id = get_user_option( 'go_avatar', $user_id );
    //$user_avatar = wp_get_attachment_image($user_avatar_id);

    //$user_avatar = get_avatar($user_id);
    $user_avatar = go_get_avatar($user_id, false, array(96, 96));

/////////////////////////
///
    if($show_loot) {
        $xp_toggle = get_option('options_go_loot_xp_toggle');
        $gold_toggle = get_option('options_go_loot_gold_toggle');
        $health_toggle = get_option('options_go_loot_health_toggle');

        if ($xp_toggle) {
            $progress_bar = xp_progress_bar($user_id);
        } else {
            $progress_bar = '';
        }


        if ($health_toggle) {
            $health_bar = go_health_bar($user_id);
        } else {
            $health_bar = '';
        }

        if ($gold_toggle) {
            // the user's current amount of currency
            $go_current_gold = go_get_user_loot($user_id, 'gold');

        } else {
            $go_current_gold = 0;
        }
    }
    ?>
    <div id='go_stats_header_container' class='go_datatables'>
        <div id='go_stats_header'>
            <div class="go_stats_id_card">
                <div class='go_stats_gravatar'><?php echo $user_avatar; ?></div>

                <div class='go_stats_user_info'>
                    <?php
                    echo "<h2>{$user_display_name}</h2>";

                    if ($full_name_toggle == 'full' || $is_admin){
                        echo "<h3>{$user_fullname}</h3>";
                    }else if ($full_name_toggle == 'first'){
                        echo "<h3>{$user_data->first_name}</h3>";
                    }

                    if($is_admin){

                        $seat_key = go_prefix_key('go_seat');
                        $section_key = go_prefix_key('go_section');
                        $sections = get_user_meta($user_id, $section_key, false);
                        $seats = get_user_meta($user_id, $seat_key, false);

                        //if(!empty($sections)){

                            if (!empty($sections)) {
                                $sections = array_unique($sections);
                                $i = 0;
                                foreach ($sections as $section) {
                                    $term = get_term($section);
                                    if (!empty($term)) {
                                        //$name = $term->name;
                                        $name = (isset($term->name) ?  $term->name : null);
                                        echo $name;
                                        if(!empty($seats)) {
                                            $name = get_option('options_go_seats_name');
                                            $seat = $seats[$i];
                                            $arr = explode("_", $seat, 2);
                                            $first = $arr[0];
                                            if(!empty($first)) {
                                                echo ": $name " . $first ;
                                            }
                                            $i++;

                                        }
                                        echo "<br>";
                                    }
                                }
                            }
                        //}

                    }
                    //echo "<br>";


                    go_user_links($user_id, $show_stats_link, $show_internal_links, $show_blog_link, null, null, true );
                    ?>

                </div>
            </div>


            <?php
           if($show_loot) {
               ?>
               <div class="go_stats_bars">
                   <?php
                   if(is_user_logged_in()) {
                      /* if ($xp_toggle) {
                           $rank = go_get_rank($user_id);
                           $rank_num = $rank['rank_num'];
                           $current_rank = $rank['current_rank'];
                           $go_option_ranks = get_option('options_go_loot_xp_levels_name_singular');

                           echo '<div class="go_stats_rank"><h3>' . $go_option_ranks . ' ' . $rank_num . ": " . $current_rank . '</h3></div>';
                       }*/
                       $grade_toggle = get_option('options_go_grade_toggle');
                       if($grade_toggle && ($is_admin || ($current_user_id === $user_id))){
                           $grade = go_get_grade($user_id);
                           echo "<div style='float: right'> <h4>Predicted Grade: {$grade} (based on current progress) <a href='javascript:void(0)' class='go_grade_scales'><i class=\"far fa-balance-scale\"></i></a></h4> </div>";
                       }

                       echo $progress_bar;
                       //echo "<div id='go_stats_user_points'><span id='go_stats_user_points_value'>{$current_points}</span> {$points_name}</div><div id='go_stats_user_currency'><span id='go_stats_user_currency_value'>{$current_currency}</span> {$currency_name}</div><div id='go_stats_user_bonus_currency'><span id='go_stats_user_bonus_currency_value'>{$current_bonus_currency}</span> {$bonus_currency_name}</div>{$current_penalty} {$penalty_name}<br/>{$current_minutes} {$minutes_name}";
                       echo $health_bar;


                       if ($xp_toggle) {
                           //echo '<div class="go_stats_xp">' . go_display_longhand_currency('xp', $go_current_xp) . '</div>';
                       }
                       if ($gold_toggle) {
                           echo '<div class="go_stats_gold">' . go_display_longhand_currency('gold', $go_current_gold) . '</div>';
                       }
                       if ($health_toggle) {
                           // echo '<div class="go_stats_health">' . go_display_longhand_currency('health', $go_current_health) . '</div>';
                       }
                   }


                   ?>

               </div>

               <?php


           }

        ?>
        </div>
        <?php
        if ($show_internal_links && $is_blog) {
            if (($current_user_id === $user_id) || $is_admin) {
               /* $hide_private = get_user_option('go_show_private');
                $checked = '';
                if ($hide_private) {
                    $checked = 'checked';
                };*/
                echo "<div id='go_blog_actions' style='' >";
                //echo "<div style=''><input id='go_show_private' data-user_id='{$user_id}' type='checkbox' {$checked} ><label for='go_show_private'> Show Private, Trashed, and Reset Posts </label></div>";
                if ($current_user_id === $user_id) {
                    echo '<div style=""> <button class="go_blog_opener" blog_post_id ="">New Post</button></div>';
                }

                echo "</div>";
            }

            //User filter
            //Public (read/ unread) //include Private (read/unread  //Drafts //Trash //Reset

            //Admin filter      //include Private
            //Read //Unread     //toggle





        }
        $social_feed = get_option('options_go_stats_leaderboard_show_social_feed');
        if($current_user_id !== $user_id  && $social_feed){


            $go_following = go_prefix_key('go_following');
            $go_follow_request = go_prefix_key('go_follow_request');
            $go_follow_requested = go_prefix_key('go_follow_requested');

            $following = get_user_meta($current_user_id, $go_following);
            $follow_request = get_user_meta($current_user_id, $go_follow_request);
            $follow_requested = get_user_meta($current_user_id, $go_follow_requested);
            if($current_user_id) {
                echo "<div id='go_blog_actions' style='' >";
                if (!in_array($user_id, $following)) {
                    //
                    //if request pending
                    if (in_array($user_id, $follow_requested)) {
                        echo "<span>Request Pending</span>";

                    } else {
                        echo "<button onclick='go_follow_request(this)' data-user_id='$user_id'>Follow</button>";
                    }
                } else {
                    echo "<button style='opacity: .5;' onclick='go_follow_unfollow(this)' data-user_id='$user_id'>Following</button>";
                }

                echo "</div>";
            }
        }
        ?>

    </div>


    <?php
}



/*
 * GRADE PREDICTOR
 */

//add_action("init", 'go_grade_percent_needed_now');
function go_grade_percent_needed_now(){

    if ( ! $data = wp_cache_get( 'go_grade_percent_needed_now' ) ) {
        $start_date = get_option('options_go_attendance_start_date');
        $end_date = get_option('options_go_attendance_end_date');
        $today = current_time('Ymd');
        $today = strtotime($today);

        $start_date = strtotime($start_date);
        $end_date = strtotime($end_date);
        if ($today > $end_date) {
            wp_cache_set('go_grade_percent_needed_now', 1);
            return 1;
        }


        $holiday_count = get_option('options_go_attendance_holidays');
        $holidays = array();
        for ($i = 0; $i < $holiday_count; $i++) {
            $holiday = get_option('options_go_attendance_holidays_' . $i . '_holiday');
            $holidays[] = strtotime($holiday);

        }


        $progress_dates_count = get_option('options_go_grade_progress_dates');
        //$progress_dates = array();
        $progress_dates_percents = array();
        for ($i = 0; $i < $progress_dates_count; $i++) {
            $progress_date = get_option('options_go_grade_progress_dates_' . $i . '_date');
            $progress_date = strtotime($progress_date);

            $progress_percent = get_option('options_go_grade_progress_dates_' . $i . '_percent_done');

            $progress_dates_percents[$progress_date] = $progress_percent;

        }
        ksort($progress_dates_percents);

        $the_progress_date = null;
        while ($the_progress_date = null) {

        }
        $percent_needed = 0;
        foreach ($progress_dates_percents as $key => $value) {
            if ($key > $today) {

                $the_progress_date = $key;
                $progress_percent = $value;
                if (isset($last_date)) {
                    $days_in_progress = go_getWorkingDays($last_date, $the_progress_date, $holidays);
                } else {
                    $last_date = $start_date;
                    $days_in_progress = go_getWorkingDays($start_date, $the_progress_date, $holidays);
                }
                $days_in_progress_elapsed = go_getWorkingDays($last_date, $today, $holidays);
                $daily_percent = $progress_percent / $days_in_progress;
                $percent_needed = $percent_needed + ($days_in_progress_elapsed * $daily_percent);
                break;
            } else {
                $percent_needed = $percent_needed + $value;
                $last_date = $key;
            }
        }
        $data = ($percent_needed * .01);
        wp_cache_set('go_grade_percent_needed_now', $data);
    }

    return $data;
}

//add_action("init", 'go_get_grade');
function go_get_grade($user_id=null, $start_date = null, $today = null){

    if(empty($start_date)) {
        $start_date = get_option('options_go_attendance_start_date');
        $start_date = strtotime($start_date);
    }

    if(empty($today)) {
        $today = current_time('Ymd');
        $today = strtotime($today);
    }

    if($today < $start_date){
        $result = "N/A";
        return $result;
    }

    $my_grade = null;
    if(empty($user_id)){
        $user_id = get_current_user_id();
    }
    $go_current_xp = go_get_user_loot($user_id, 'xp');
    $percent_needed = go_grade_percent_needed_now();

    $grade_scales = go_get_grade_scale(true);

    foreach($grade_scales as $key => $value){
        //$key = $key * $percent_needed * .01;
        if($go_current_xp >= $key) {
            $my_grade = $value;
            break;
        }
    }

    return $my_grade;
}

//add_action("init", 'go_get_grade_scale');
function go_get_grade_scale($current = false){

    if ( ! $grade_scales = wp_cache_get( 'go_get_grade_scale'.$current ) ) {
        $grade_scale_grade_count = get_option('options_grade_scale');
        $grade_scales = array();

        for ($i = 0; $i < $grade_scale_grade_count; $i++) {
            $grade_scale_grade = get_option('options_grade_scale_' . $i . '_grade');
            $grade_scale_xp = get_option('options_grade_scale_' . $i . '_min_xp');
            $grade_scales[$grade_scale_xp] = $grade_scale_grade;
        }
        krsort($grade_scales);

        $today = current_time('Ymd');
        $today = strtotime($today);
        $end_date = get_option('options_go_attendance_end_date');
        $end_date = strtotime($end_date);
        if ($today > $end_date) {
            return $grade_scales;
        }

        //$xp_abbr = get_option( "options_go_loot_xp_abbreviation" );
        if ($current) {
            $orig_grade_scale = $grade_scales;
            $grade_scales = array();
            $percent_needed = go_grade_percent_needed_now();
            foreach ($orig_grade_scale as $key => $value) {
                $key = $key * $percent_needed;
                $grade_scales[$key] = $value;
            }
        }
        wp_cache_set('go_get_grade_scale'.$current, $grade_scales);
    }
    return $grade_scales;
}

//The function returns the no. of business days between two dates and it skips the holidays
function go_getWorkingDays($startDate,$endDate,$holidays){
    // do strtotime calculations just once
    //$endDate = strtotime($endDate);
    //$startDate = strtotime($startDate);


    //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
    //We add one to inlude both dates in the interval.
    $days = ($endDate - $startDate) / 86400 + 1;

    $no_full_weeks = floor($days / 7);
    $no_remaining_days = fmod($days, 7);

    //It will return 1 if it's Monday,.. ,7 for Sunday
    $the_first_day_of_week = date("N", $startDate);
    $the_last_day_of_week = date("N", $endDate);

    //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
    //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
    if ($the_first_day_of_week <= $the_last_day_of_week) {
        if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
        if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
    }
    else {
        // (edit by Tokes to fix an edge case where the start day was a Sunday
        // and the end day was NOT a Saturday)

        // the day of the week for start is later than the day of the week for end
        if ($the_first_day_of_week == 7) {
            // if the start date is a Sunday, then we definitely subtract 1 day
            $no_remaining_days--;

            if ($the_last_day_of_week == 6) {
                // if the end date is a Saturday, then we subtract another day
                $no_remaining_days--;
            }
        }
        else {
            // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
            // so we skip an entire weekend and subtract 2 days
            $no_remaining_days -= 2;
        }
    }

    //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
    $workingDays = $no_full_weeks * 5;
    if ($no_remaining_days > 0 )
    {
        $workingDays += $no_remaining_days;
    }

    //We subtract the holidays
    foreach($holidays as $holiday){
        $time_stamp=$holiday;
        //If the holiday doesn't fall in weekend
        if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
            $workingDays--;
    }

    return $workingDays;
}

function go_print_grade_scales(){
    $is_ajax = false;


    if(defined('DOING_AJAX') && DOING_AJAX) {
        $is_ajax = true;
        if (!is_user_logged_in()) {
            echo "login";
            die();
        }
        //check_ajax_referer('go_stats_leaderboard_');
        if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_print_grade_scales')) {
            echo "refresh";
            die();
        }
    }

    $xp_abbr = get_option( "options_go_loot_xp_abbreviation" );

    echo "<h3>Grade Scale</h3>";
    $grade_scale = go_get_grade_scale();

    $first= "+";
    foreach($grade_scale as $key => $value){
        echo "{$value}: $key{$first} $xp_abbr<br>";
        $first = "";
    }
    $today_str = current_time('F d, Y');
    $today = strtotime($today_str);
    $end_date = get_option('options_go_attendance_end_date');
    $end_date = strtotime($end_date);

    $start_date = get_option('options_go_attendance_start_date');
    $start_date = strtotime($start_date);
    if (($today > $end_date) || ($today < $start_date)) {
        if($is_ajax){
            die();
        }
        return;
    }

    echo "<br><br><h3>Current Grade Scale ($today_str)</h3>";
    $grade_scale = go_get_grade_scale(true);
    $first= "+";
    foreach($grade_scale as $key => $value){
        echo "{$value}: $key{$first} $xp_abbr<br>";
        $first = "";
    }
    if($is_ajax){
        die();
    }
    return;


}
