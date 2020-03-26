<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-12-22
 * Time: 16:34
 */

add_action('wp_footer', 'go_timed_award', 1);
function go_timed_award($notify = false){
    $is_active  = get_option( 'options_go_timed_award' );
    if(!$is_active){
        return;
    }
    $current_date = date('Ymd');

    $start_date = get_option('options_go_attendance_start_date');

    if($start_date){
        if($start_date > $current_date){
            return;
        }
    }

    $end_date = get_option('options_go_attendance_end_date');

    if($end_date){
        if($end_date < $current_date){
            return;
        }
    }

    $holiday_count = get_option('options_go_attendance_holidays');
    $holidays = array();
    for ($i = 0; $i < $holiday_count; $i++) {
        $holiday = get_option('options_go_attendance_holidays_'.$i.'_holiday');
        $holidays[] = $holiday;

    }

    if(in_array($current_date, $holidays)){
        return;
    }



    if (!is_user_member_of_blog()){
        return;
    }

    $award_time = false;
    $user_id = get_current_user_id();
    //Get the users section(s)
    $key = go_prefix_key('go_section');
    $user_terms = get_user_meta($user_id, $key, false);

    if(!is_array($user_terms)) {
        $user_terms = array();
    }



    //NEW AWARDS

    $num_scheds  = get_option( 'options_go_attendance_schedules' );
    if(!is_numeric($num_scheds)){
        return;
    }

    //loop through the awards for a match of time and section
    for ($a = 0; $a < $num_scheds; $a++) {

        $is_active = get_option("options_go_attendance_schedules_" . $a . "_schedule_active");
        if (!$is_active) {
            continue;
        }


        $award_num = get_option('options_go_attendance_schedules_' . $a . '_awards');
        if (!is_numeric($award_num)) {
            return;
        }

        //loop through the awards for a match of time and section
        for ($i = 0; $i < $award_num; $i++) {


            $sched_num = get_option("options_go_attendance_schedules_" . $a . "_awards_" . $i . "_schedule");

            if (!is_numeric($sched_num)) {
                return;
            }
            for ($n = 0; $n < $sched_num; $n++) {

                //$dow_section = "go_sched_opt_" . $i . "_sched_sections";
                $dow_section = get_option("options_go_attendance_schedules_" . $a . "_awards_" . $i . "_schedule_" . $n . "_sched_sections");

                //$dow_section = (isset($custom_fields[$dow_section][0]) ? unserialize($custom_fields[$dow_section][0]) : null);

                if (!$dow_section) {
                    $dow_section = array();
                }

                $dow_days = get_option("options_go_attendance_schedules_" . $a . "_awards_" . $i . "_schedule_" . $n . "_dow");
                if (!$dow_days) {
                    $dow_days = array();
                }

                $current_time = current_time('Y-m-d');
                //$dow_time = "go_sched_opt_" . $i . "_time";
                //$dow_time = (isset($custom_fields[$dow_time][0]) ? $custom_fields[$dow_time][0] : null);
                $dow_time = get_option("options_go_attendance_schedules_" . $a . "_awards_" . $i . "_schedule_" . $n . "_time");
                $dow_time = $current_time . " " . $dow_time;
                $dow_time = strtotime($dow_time);
                //$offset = 3600 * get_option('gmt_offset');
                //$dow_time = $dow_time + $offset;

                //$dow_minutes = "go_sched_opt_" . $i . "_min";
                //$dow_minutes = (isset($custom_fields[$dow_minutes][0]) ? $custom_fields[$dow_minutes][0] : null);
                $dow_minutes = get_option("options_go_attendance_schedules_" . $a . "_awards_" . $i . "_schedule_" . $n . "_min");
                $seconds_available = 60 * $dow_minutes;


                $current_time = current_time('timestamp');
                //$offset = 3600 * get_option('gmt_offset');
                //$current_time = $current_time - $offset;

                //If the user is in at least one section, continue . . .
                if ((in_array($dow_section, $user_terms)) || (empty ($dow_section))) {
                    //If today is one of the days it unlocks

                    //$current_time = date( 'l', $current_time );
                    // $date = date('l', $current_time);
                    if (in_array(date('l', $current_time), $dow_days) || (empty($dow_days))) {

                        //if the current time is between the start time and the start time and the minutes unlocked
                        if (($current_time >= $dow_time) && ($current_time < ($dow_time + $seconds_available))) {
                            //it is unlocked, so exit loop and continue
                            $award_time = true;

                            break 3;
                        }
                    }
                }
            }
        }
    }

    if($award_time){

        $title = get_option("options_go_attendance_schedules_" . $a . "_awards_" . $i . "_award_title");
        $message = get_option("options_go_attendance_schedules_" . $a . "_awards_" . $i . "_message");

        $loot_toggle = get_option("options_go_attendance_schedules_" . $a . "_awards_" . $i . "_go_loot_toggle");
        $xp = floatval(get_option("options_go_attendance_schedules_" . $a . "_awards_" . $i . "_go_loot_loot_xp"));
        $gold = floatval(get_option("options_go_attendance_schedules_" . $a . "_awards_" . $i . "_go_loot_loot_gold"));
        $health = floatval(get_option("options_go_attendance_schedules_" . $a . "_awards_" . $i . "_go_loot_loot_health"));
        //options_go_attendance_schedules_0_awards_1_award_title
        if($loot_toggle == '0'){
            $xp = $xp * -1;
            $gold = $gold * -1;
            $health = $health * -1;
        }
        $vars = array();
        $vars[0]['uid'] = $user_id;
        $date = date('mdY', $current_time);
        //$uniqueid = $date . $dow_section;
        //$uniqueid = -1 * $uniqueid;

        global $wpdb;
        $aTable = "{$wpdb->prefix}go_actions";
        /*$message_id = $wpdb->get_var($wpdb->prepare("SELECT id
				FROM {$aTable}
				WHERE uid = %d AND  source_id = %d
				ORDER BY id DESC LIMIT 1",
            intval($user_id),
            $uniqueid));*/

        // SELECT * FROM `table` WHERE DATE(`timestamp`) = CURDATE()
        $message_id = $wpdb->get_var($wpdb->prepare("SELECT id
            FROM {$aTable} 
            WHERE DATE(`timestamp`) = CURDATE() AND uid = %d AND action_type = 'attendance' AND bonus_status = %d
            ORDER BY id DESC LIMIT 1",
            intval($user_id),
            $dow_section));


        $result = array();
        $result[] = $title;
        $result[] = $message;
        $result = serialize($result);

        if($message_id === null) {
            //go_send_message(true, $title, $message, "message", '', $xp, $gold, $health, '', false, '', '', $vars);
            go_update_actions($user_id, 'attendance', null, 1, $dow_section, null, $result, null, null, null, null, $xp, $gold, $health, null, null, false);
            update_user_option($user_id, 'go_new_messages', true);
            //return true;
        }
        return;
    }









    //START OLD AWARDS--Remove everything after this Summer 2020
    //$award_num = (isset($custom_fields['go_sched_opt'][0]) ?  $custom_fields['go_sched_opt'][0] : null); //the number of schedule locks
    $award_num  = get_option( 'options_go_timed_award' );
    if(!is_numeric($award_num)){
        return;
    }



    //loop through the awards for a match of time and section
    for ($i = 0; $i < $award_num; $i++) {


        $sched_num  = get_option( "options_go_timed_award_" . $i . "_schedule");
        $is_active  = get_option( "options_go_timed_award_" . $i . "_information_active");
        if(!$is_active){
            continue;
        }

        if(!is_numeric($sched_num)){
            return;
        }
        for ($n = 0; $n < $sched_num; $n++) {

            //$dow_section = "go_sched_opt_" . $i . "_sched_sections";
            $dow_section = get_option("options_go_timed_award_" . $i . "_schedule_" . $n . "_sched_sections");

            //$dow_section = (isset($custom_fields[$dow_section][0]) ? unserialize($custom_fields[$dow_section][0]) : null);

            if (!$dow_section) {
                $dow_section = array();
            }

            $dow_days = get_option("options_go_timed_award_" . $i . "_schedule_" . $n . "_dow");
            if (!$dow_days) {
                $dow_days = array();
            }

            $current_time = current_time('Y-m-d');
            //$dow_time = "go_sched_opt_" . $i . "_time";
            //$dow_time = (isset($custom_fields[$dow_time][0]) ? $custom_fields[$dow_time][0] : null);
            $dow_time = get_option("options_go_timed_award_" . $i . "_schedule_" . $n . "_time");
            $dow_time = $current_time . " " . $dow_time;
            $dow_time = strtotime($dow_time);
            //$offset = 3600 * get_option('gmt_offset');
            //$dow_time = $dow_time + $offset;

            //$dow_minutes = "go_sched_opt_" . $i . "_min";
            //$dow_minutes = (isset($custom_fields[$dow_minutes][0]) ? $custom_fields[$dow_minutes][0] : null);
            $dow_minutes = get_option("options_go_timed_award_" . $i . "_schedule_" . $n . "_min");
            $seconds_available = 60 * $dow_minutes;


            $current_time = current_time('timestamp');
            //$offset = 3600 * get_option('gmt_offset');
            //$current_time = $current_time - $offset;

            //If the user is in at least one section, continue . . .
            if ((in_array($dow_section, $user_terms)) || (empty ($dow_section))) {
                //If today is one of the days it unlocks

                //$current_time = date( 'l', $current_time );
                // $date = date('l', $current_time);
                if (in_array(date('l', $current_time), $dow_days) || (empty($dow_days))) {

                    //if the current time is between the start time and the start time and the minutes unlocked
                    if (($current_time >= $dow_time) && ($current_time < ($dow_time + $seconds_available))) {
                        //it is unlocked, so exit loop and continue
                        $award_time = true;

                        break 2;
                    }
                }
            }
        }
    }

    if($award_time){

        $title = get_option("options_go_timed_award_" . $i . "_information_award_title");
        $message = get_option("options_go_timed_award_" . $i . "_message");

        $loot_toggle = get_option("options_go_timed_award_" . $i . "_go_loot_toggle");
        $xp = floatval(get_option("options_go_timed_award_" . $i . "_go_loot_loot_xp"));
        $gold = floatval(get_option("options_go_timed_award_" . $i . "_go_loot_loot_gold"));
        $health = floatval(get_option("options_go_timed_award_" . $i . "_go_loot_loot_health"));
        if($loot_toggle == '0'){
            $xp = $xp * -1;
            $gold = $gold * -1;
            $health = $health * -1;
        }
        $vars = array();
        $vars[0]['uid'] = $user_id;
        $date = date('mdY', $current_time);
        //$uniqueid = $date . $dow_section;
        //$uniqueid = -1 * $uniqueid;

        global $wpdb;
        $aTable = "{$wpdb->prefix}go_actions";
        /*$message_id = $wpdb->get_var($wpdb->prepare("SELECT id
				FROM {$aTable}
				WHERE uid = %d AND  source_id = %d
				ORDER BY id DESC LIMIT 1",
            intval($user_id),
            $uniqueid));*/

        // SELECT * FROM `table` WHERE DATE(`timestamp`) = CURDATE()
        $message_id = $wpdb->get_var($wpdb->prepare("SELECT id
            FROM {$aTable} 
            WHERE DATE(`timestamp`) = CURDATE() AND uid = %d AND action_type = 'attendance' AND bonus_status = %d
            ORDER BY id DESC LIMIT 1",
            intval($user_id),
            $dow_section));


        $result = array();
        $result[] = $title;
        $result[] = $message;
        $result = serialize($result);

        if($message_id === null) {
            //go_send_message(true, $title, $message, "message", '', $xp, $gold, $health, '', false, '', '', $vars);
            go_update_actions($user_id, 'attendance', null, 1, $dow_section, null, $result, null, null, null, null, $xp, $gold, $health, null, null, false);
            update_user_option($user_id, 'go_new_messages', true);
            //return true;
        }
    }
   // return false;

}


function go_attendance_check_ajax(){
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_attendance_check_ajax' ) ) {
        echo "refresh";
        die();
    }

    go_timed_award(true);

    die();


}