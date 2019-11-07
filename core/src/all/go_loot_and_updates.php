<?php

/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 4/29/18
 * Time: 10:40 PM
 */

/**
 * Returns an array containing data for the user's current and next rank.
 *
 * Uses the user's "go_rank" meta data value to return the name and point threshold of the current
 * and next rank.
 *
 * @since 2.4.4
 *
 * @param  int $user_id The user's id.
 * @return array Returns an array of defaults on when the user's "go_rank" meta data is empty. 
 * 				 On success, returns array of rank data.
 */
function go_get_rank ( $user_id, $go_current_xp = null ) {
	if ( empty( $user_id ) ) {
		return;
	}
	//get xp if not passed
	if ($go_current_xp == null) {
        $go_current_xp = intval(go_get_user_loot($user_id, 'xp'));
    }
    //get number of ranks in options
    $rank_count = get_option('options_go_loot_xp_levels_level');
    $i = $rank_count - 1; //account for count starting at 0

    //test to see what the rank level is
    while ( $i >= 0 ) {
        //get the next xp level
        if ($i == 0){
            $xp = 0;
        }else {
            $xp = intval(get_option('options_go_loot_xp_levels_level_' . $i . '_xp'));
        }

        //Test if the User has more XP than this level
        if ($go_current_xp >= $xp){
            $current_rank = get_option('options_go_loot_xp_levels_level_' . $i . '_name');//get rank name
            $current_rank_points = $xp;
            $next_rank_points = get_option('options_go_loot_xp_levels_level_' . ($i +1) . '_xp');//get next rank xp
            $next_rank = get_option('options_go_loot_xp_levels_level_' . ($i +1) . '_name');//get next rank name
            break;
        }
        $i--;
    }

		return array(
			'current_rank' 		  => $current_rank,
			'current_rank_points' => $current_rank_points,
			'next_rank' 		  => $next_rank,
			'next_rank_points' 	  => $next_rank_points,
            'rank_num'            => ($i + 1)
		);
}

/**
 * @param $user_id
 * @return int
 */
function go_return_currency($user_id ) {
    global $wpdb;
    $table_name_go_totals = $wpdb->prefix . "go_loot";
    $currency = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT gold 
			FROM {$table_name_go_totals} 
			WHERE uid = %d",
            $user_id
        )
    );
    return $currency;

}

/**
 * @param $user_id
 * @return int
 */
function go_return_points($user_id ) {
    global $wpdb;
    $table_name_go_totals = $wpdb->prefix . "go_loot";
    $points = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT xp 
			FROM {$table_name_go_totals} 
			WHERE uid = %d",
            $user_id
        )
    );
    return $points;
}

/**
 * @param $user_id
 * @return int
 */
function go_return_health($user_id ) {
    global $wpdb;
    $table_name_go_totals = $wpdb->prefix . "go_loot";
    $health = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT health 
			FROM {$table_name_go_totals} 
			WHERE uid = %d",
            $user_id
        )
    );
    return $health;
}

/**
 * @param $loot_type
 * @return bool
 */
function go_get_loot_toggle ($loot_type ){

    if (get_option( 'options_go_loot_' . $loot_type . '_toggle' )){
        $is_on = true;
    }else{
        $is_on = false;
    }
    return $is_on;
}

/**
 * @param $loot_type
 * @return mixed
 */
function go_get_loot_short_name($loot_type){
    if($loot_type === 'gold'){
        $coins_currency = get_option("options_go_loot_gold_currency");
        if ($coins_currency === 'coins'){
            $name = get_option('options_go_loot_gold_coin_names_gold_coin_abbreviation');
            return $name;
        }
    }
    $name = get_option('options_go_loot_' . $loot_type . '_abbreviation');
    return $name;
}

//not used
/**
 * @param $points
 * @return string
 */
function go_display_points($points ) {

    $prefix = get_option( 'go_points_prefix' );
    $suffix = get_option( 'go_points_suffix' );
    return "{$prefix} {$points} {$suffix}";
}


/**
 * Output currency formatted for the admin bar dropdown.
 *
 * Outputs any currency in the format (without quotations): "1234 Experience (XP)".
 *
 * @param  STRING $currency_type Contains the base name of the currency to be displayed
 * 			(e.g. "xp", "gold", "health" ).
 * @param  NUMBER $amount
 * @param  BOOLEAN $output Optional. TRUE will echo the currency, FALSE will return it (default).
 * @param  BOOLEAN $show_empty
 * @param  BOOLEAN $show_coins
 * @return STRING/NULL Either echos or returns the currency. Returns FALSE on failure.
 */
function go_display_longhand_currency ( $currency_type, $amount, $output = false, $show_empty = true, $show_coins = true, $divider =', ' ) {

    $coins_currency = get_option("options_go_loot_gold_currency");
    $str = false;
    $side = "left";
    if ( "xp" === $currency_type || ("gold" === $currency_type && ($coins_currency != 'coins')) || "health" === $currency_type) {
        $currency_name = get_option("options_go_loot_{$currency_type}_name");
        $suffix = get_option("options_go_loot_{$currency_type}_abbreviation");
        if($currency_type == 'gold') {
            //$badge_ids = (isset($custom_fields['go_badges'][0]) ?  $custom_fields['go_badges'][0] : null);

            $side = get_option("options_go_loot_gold_abbreviation_display");
            if($side != 'right'){
                $side = "left";
            }
            if($side === 'left'){
                $str = " {$suffix}{$amount}";
            }else {
                $str = "{$amount} {$currency_name} ({$suffix})";
            }
        }
        else {
                $str = "{$amount} {$currency_name} ({$suffix})";
        }
    }
    else if("gold" === $currency_type && $coins_currency === 'coins') {
        $gold_name = get_option("options_go_loot_gold_coin_names_gold_coin_name");
        $silver_name = get_option("options_go_loot_gold_coin_names_silver_name");
        $copper_name = get_option("options_go_loot_gold_coin_names_copper_name");
        $gold_suffix = get_option("options_go_loot_gold_coin_names_gold_coin_abbreviation");
        $silver_suffix = get_option("options_go_loot_gold_coin_names_silver_abbreviation");
        $copper_suffix = get_option("options_go_loot_gold_coin_names_copper_abbreviation");
        $gold_amount = intval($amount);
        $silver_amount = intval(intval(($amount * 100) - ($gold_amount * 100))/10);
        $copper_amount = intval(($amount * 100) - ($gold_amount * 100) - ($silver_amount * 10));

        $str = '';
        /*
        if($show_coins) {

            if ($gold_amount != 0 || $show_empty) {

                $str .= '<div class="coin_wrapper"><div class="go_coin gold"><p>' . $gold_suffix . '</p></div>&nbsp;<div class="go_amount">' . $gold_amount . '</div>&nbsp;</div>';
            }
            if ($silver_amount != 0 || $show_empty) {
                $str .= '<div class="coin_wrapper"><div class="go_coin silver"><p>' . $silver_suffix . '</p></div>&nbsp;<div class="go_amount">' . $silver_amount . '</div>&nbsp;</div>';
            }
            if ($copper_amount != 0 || $show_empty) {
                $str .= '<div class="coin_wrapper"><div class="go_coin copper"><p>' .$copper_suffix . '</p></div>&nbsp;<div class="go_amount">' . $copper_amount . '</div>&nbsp;</div>';
            }
        }
        else{*/
            if ($gold_amount !=  0 || $show_empty) {

                $str .= "{$gold_amount} {$gold_name} ({$gold_suffix})";
            }
            if ($silver_amount !=  0 || $show_empty) {
                if(!empty($str)){
                    $str .= $divider;
                }
                $str .= "{$silver_amount} {$silver_name} ({$silver_suffix})";
            }
            if ($copper_amount !=  0 || $show_empty) {
                if(!empty($str)){
                    $str .= $divider;
                }
                $str .= "{$copper_amount} {$copper_name} ({$copper_suffix})";
            }

        $str .= ' ';
       // }
        if (empty($str)){
            $str = false;
        }
    }

    if ($output && $str) {
        echo $str;
    }
    else {
        return $str;
    }

}


/**
 * @param $currency_type
 * @param $amount
 * @param bool $output
 * @param bool $breaks
 * @param string $divider
 * @return bool|string
 */
function go_display_shorthand_currency ($currency_type, $amount, $output = false, $breaks = false, $divider =' ', $show_empty = false ) {

    $coins_currency = get_option("options_go_loot_gold_currency");
    $str = false;
    $side = "left";
    if ("xp" === $currency_type || ("gold" === $currency_type && $coins_currency != 'coins') || "health" === $currency_type) {
            if($currency_type == 'gold') {
                //$badge_ids = (isset($custom_fields['go_badges'][0]) ?  $custom_fields['go_badges'][0] : null);
                $side = get_option("options_go_loot_gold_abbreviation_display");
                if($side != 'right'){
                    $side = "left";
                }
            }
            $suffix = get_option("options_go_loot_{$currency_type}_abbreviation");
            if($side === 'left' && $currency_type === 'gold'){
                $str = "{$suffix}{$amount}";
            }else{
                $str = "{$amount}{$divider}{$suffix}";
            }
    } else if("gold" === $currency_type && $coins_currency === 'coins'){
        $gold_name = get_option("options_go_loot_gold_coin_names_gold_coin_name");
        $silver_name = get_option("options_go_loot_gold_coin_names_silver_name");
        $copper_name = get_option("options_go_loot_gold_coin_names_copper_name");
        $gold_suffix = get_option("options_go_loot_gold_coin_names_gold_coin_abbreviation");
        $silver_suffix = get_option("options_go_loot_gold_coin_names_silver_abbreviation");
        $copper_suffix = get_option("options_go_loot_gold_coin_names_copper_abbreviation");
        $gold_amount = intval($amount);
        $silver_amount = intval(intval(($amount * 100) - ($gold_amount * 100))/10);
        $copper_amount = intval(($amount * 100) - ($gold_amount * 100) - ($silver_amount * 10));


            $str = '';
            if ($gold_amount != 0 || $show_empty){


                if ($breaks) {
                    $l_str = "{$gold_amount}<br>";
                    $r_str = "{$gold_suffix}<br>";
                }else{
                    $str = "{$gold_amount}{$divider}{$gold_suffix}";
                    $str .= "&nbsp;&nbsp;&nbsp;";
                }
            }
            if ($silver_amount != 0 || $show_empty){

                if ($breaks) {
                    $l_str .= "{$silver_amount}<br>";
                    $r_str .= "{$silver_suffix}<br>";
                }else{
                    $str .=  "{$silver_amount}{$divider}{$silver_suffix}";
                    $str .= "&nbsp;&nbsp;&nbsp;";
                }
            }
            if ($copper_amount != 0 || $show_empty){

                if ($breaks) {
                    $l_str .= "{$copper_amount}<br>";
                    $r_str .= "{$copper_suffix}<br>";
                }
                else{
                    $str .=  "{$copper_amount}{$divider}{$copper_suffix}";
                    $str .= "&nbsp;&nbsp;&nbsp;";
                }
            }

            if ($breaks){//this is for the player bar
                $str = '<div style="text-align: right; padding-right:5px;">'.$l_str.'</div><div style="text-align: left;">'.$r_str.'</div>';
            }
            if (empty($str)){
                $str = false;
            }
            //$str = "{$gold_name}({$gold_suffix}):{$gold_amount}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$silver_name}({$silver_suffix}):{$silver_amount}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$copper_name}({$copper_suffix}):{$copper_amount}";


    }
    if ($output && $str) {
        echo $str;
    } else {
        return $str;
    }

}

/**
 * @param $user_id
 * @return float|int
 */
function go_get_health_mod ($user_id){
    //set the health mod
    $is_logged_in = ! empty( $user_id ) && is_user_member_of_blog( $user_id ) ? true : false;
    if (go_user_is_admin($user_id)){
        $is_logged_in = true;
    }
    $health_toggle = go_get_loot_toggle( 'health' );
    $max_mod = intval(get_option( 'options_go_loot_health_max_health_mod' ));
    if ($max_mod<100){
        $max_mod = 100;
    }
    $max_mod = $max_mod * .01;
    if ($health_toggle && $is_logged_in) {
        //get current health mod from totals table
        $health_mod = go_get_user_loot($user_id, 'health') * .01;
    }
    else {
        $health_mod = 1;
    }
    if ($health_mod > $max_mod){
        $health_mod = $max_mod;
    }
    if ($health_mod < 0){
        $health_mod = 0;
    }
    return $health_mod;
}

/**
 * @param $user_id
 * @param $post_id
 * @param $status
 * @return mixed
 */
function go_get_quiz_mod($user_id, $post_id, $status ){
    global $wpdb;
    $go_actions_table_name = "{$wpdb->prefix}go_actions";

    $quiz_mod = $wpdb->get_var($wpdb->prepare("SELECT result 
            FROM {$go_actions_table_name} 
            WHERE source_id = %d AND uid = %d AND stage = %d AND action_type = %s", $post_id, $user_id, $status, 'quiz_mod'));

    return $quiz_mod;

}

/**
 * @param $user_id
 * @param $post_id
 * @param $status
 * @param $type
 * @return mixed
 */
function go_get_quiz_result($user_id, $post_id, $status, $type ){
    global $wpdb;
    $go_actions_table_name = "{$wpdb->prefix}go_actions";

    $query = "SELECT result, check_type 
            FROM {$go_actions_table_name} 
            WHERE source_id = $post_id AND uid = $user_id AND stage = $status AND action_type = 'quiz_mod'";

    $quiz_result = $wpdb->get_results($query, ARRAY_A);

    if($type == 'array'){
        return $quiz_result;
    }
    else if($type == 'mod'){
        $quiz_mod = $quiz_result[0]['result'];
        return $quiz_mod;
    }
}

/**
 * @param $user_id
 * @param $loot_type
 * @return mixed
 */
function go_get_user_loot ($user_id, $loot_type){
    //get health from totals table
    $user_loot = go_get_loot($user_id);

   // $loot = $user_loot[$loot_type];
    $loot = (isset($user_loot[$loot_type]) ?  $user_loot[$loot_type] : 0);
    return $loot;
}

/**
 * @param $user_id
 * @param $post_id
 * @param $custom_fields
 * @param $status
 * @param $bonus_status
 * @param $progressing
 * @param null $result
 * @param null $check_type
 * @param null $badge_ids
 * @param null $group_ids
 */
function go_update_stage_table ($user_id, $post_id, $custom_fields, $status, $bonus_status, $progressing, $result = null, $check_type = null, $badge_ids = null, $group_ids = null )
{
    global $wpdb;
    $go_task_table_name = "{$wpdb->prefix}go_tasks";
    $go_actions_table_name = "{$wpdb->prefix}go_actions";
    $is_logged_in = !empty($user_id) && is_user_member_of_blog($user_id) ? true : false;
    if (go_user_is_admin($user_id)){
        $is_logged_in = true;
    }
    $bonus_status = (isset($bonus_status) ? $bonus_status : null);
    $status = (isset($status) ? $status : null);
    $health_mod = null;
    $stage_mod = null;
    $time = current_time('mysql');
    $last_time = $time;
    $xp = 0;
    $gold = 0;
    $health = 0;
    $action_type = 'task';
    $quiz_mod = 0;
    $due_date_mod = 0;
    $timer_mod = 0;

    //UPDATE ELEMENT CLASS (used for color on map
    //check if the parent quest is reset and un-reset if it is
    //outline color or reset red color for map
    $class = $wpdb->get_results($wpdb->prepare("SELECT class
			FROM {$go_task_table_name}
			WHERE uid = %d and post_id = %d
			ORDER BY last_time DESC", $user_id, $post_id
    ));

    $update = false;
    if(is_array($class)) {
        $class = (isset($class[0]->class) ?  $class[0]->class : null);
        //$class = $class[0]->class;
        if(is_serialized($class)) {
            $class = unserialize($class);
            if (in_array('reset', $class)) {
                if (($key = array_search('reset', $class)) !== false) {
                    unset($class[$key]);
                }
                $class[] = 'resetted';
                $class = serialize($class);
                $update = true;
            }
        }
    }



    //END UPDATE CLASS

    if ($progressing === 'timer') {
        $start_time = $time;
        $new_status_task = 'null';
        $new_bonus_status_task = 'null';
        $new_bonus_status_actions = 0;

        $wpdb->query($wpdb->prepare("UPDATE {$go_task_table_name} 
                    SET 
                        last_time = IFNULL('{$last_time}', last_time) ,
                        timer_time = IFNULL('{$last_time}', last_time)                  
                    WHERE uid= %d AND post_id=%d ", $user_id, $post_id));
    }
    else if ($progressing === true) {

        if ($status !== null) {
            $new_status_task = $status + 1;
            $new_status_actions = $status + 1;
        } else {
            $new_status_task = 'null';
            $new_status_actions = 'null';
        }
        if ($bonus_status !== null) {
            $new_bonus_status_task = $bonus_status + 1;
            $new_bonus_status_actions = $bonus_status + 1;
        } else {
            $new_bonus_status_task = 'null';
            $new_bonus_status_actions = 'null';
        }

        //if health toggle is on
        $health_toggle = get_option('options_go_loot_health_toggle');
        if ($health_toggle && $is_logged_in) {
            //get health_mod from the first time this task was tried.
            //if this is a bonus stage, then get the health mod the first time this bonus stage/repeat was attempted.
            if ($bonus_status !== null) {
                $temp_status = $bonus_status + 1;
                $original_health_mod = $wpdb->get_var($wpdb->prepare("SELECT global_mod 
                    FROM {$go_actions_table_name} 
                    WHERE source_id = %d AND uid = %d AND bonus_status = %d AND NOT result = %s
                    ORDER BY id DESC LIMIT 1", $post_id, $user_id, $temp_status, 'undo_bonus'));
            } //else this is not a bonus stage and get the health mod first time it was attempted
            else {
                $temp_status = $status + 1;
                $original_health_mod = $wpdb->get_var($wpdb->prepare("SELECT global_mod 
                    FROM {$go_actions_table_name} 
                    WHERE source_id = %d AND uid = %d AND stage = %d AND NOT result = %s
                    ORDER BY id DESC LIMIT 1", $post_id, $user_id, $temp_status, 'undo'));
            }
            //get current health mod from totals table
            $current_health_mod = go_get_health_mod($user_id);


            if ($original_health_mod === null) {
                $health_mod = $current_health_mod;
            } else if ($original_health_mod > $current_health_mod) {
                $health_mod = $current_health_mod;
            } else {
                $health_mod = $original_health_mod;
            }
        } else {
            $health_mod = 1;
        }

        //if not entry loot--it could have a quiz
        if ($status != -1) {
            //if stage check is a quiz
            if ($check_type == 'quiz') {
                $temp_status = $status + 1;
                $questions_missed = go_get_quiz_mod($user_id, $post_id, $temp_status );
                if ($questions_missed > 0) {
                    $quiz_stage_mod = 'go_stages_' . $status . '_quiz_modifier'; //% to take off for each question missed
                    $quiz_stage_mod = (isset($custom_fields[$quiz_stage_mod][0]) ? $custom_fields[$quiz_stage_mod][0] : 0);
                    $quiz_mod = $quiz_stage_mod * .01 * $questions_missed;

                }
                $quiz_array = "go_stages_" . strval($status) . "_quiz";
                $quiz_array= $custom_fields[$quiz_array];
                $quiz_array = unserialize($quiz_array[0]);
                $question_count = $quiz_array[3];
                if (empty($questions_missed)){$questions_missed = 0;}
                $result = ($question_count - $questions_missed) . "/" . $question_count;

            }

        }

        $due_date_mod = 0;
        if ($custom_fields['go_due_dates_toggle'][0] == true && $is_logged_in) {
            $num_loops = $custom_fields['go_due_dates_mod_settings'][0];
            $mod_date_latest = null;
            for ($i = 0; $i < $num_loops; $i++) {
                $mod_date = 'go_due_dates_mod_settings_' . $i . '_date';
                $mod_date = $custom_fields[$mod_date][0];
                $mod_date_timestamp = strtotime($mod_date);
                ////$mod_date = date('F j, Y \a\t g:i a\.', $mod_date_timestamp);
                //$mod_date_timestamp = $mod_date_timestamp + (3600 * get_option('gmt_offset'));
                $current_timestamp = current_time('timestamp');
                $mod_percent = 'go_due_dates_mod_settings_' . $i . '_mod';
                $mod_percent = $custom_fields[$mod_percent][0];
                if ($current_timestamp > $mod_date_timestamp) {
                    //set the latest mod date if this is the first mod date
                    if ($mod_date_latest == null) {
                        $mod_date_latest = $mod_date_timestamp;
                        $due_date_mod = $mod_percent * .01;
                    } else if ($mod_date_timestamp > $mod_date_latest) {
                        $mod_date_latest = $mod_date_timestamp;
                        $due_date_mod = $mod_percent * .01;
                    }
                }
            }
        }

        $timer_mod = 0;
        $timer_on = $custom_fields['go_timer_toggle'][0];
        if ($timer_on && $is_logged_in) {
            $end_time = go_end_time($custom_fields, $user_id, $post_id);
            $current_date = strtotime(current_time('mysql')); //current date and time
            $timer_time = $end_time - $current_date;
            //if the time is up, display message
            if ($timer_time <= 0) {
                $timer_mod = (isset($custom_fields['go_timer_settings_timer_mod'][0]) ? $custom_fields['go_timer_settings_timer_mod'][0] : 0);
                $timer_mod = $timer_mod * .01;
            }
        }

        $stage_mod = ($due_date_mod + $timer_mod + $quiz_mod);
        if ($stage_mod > 1) {
            $stage_mod = 1;
        }

        if ($status === -1) {
            /// get entry loot
            $xp = $custom_fields['go_entry_rewards_xp'][0];
            //$xp = go_mod_loot($xp, $xp_toggle, $xp_mod_toggle, $stage_mod, $health_mod);

            $gold = $custom_fields['go_entry_rewards_gold'][0];
            $gold = go_mod_loot($gold, $stage_mod, $health_mod);

            $health = $custom_fields['go_entry_rewards_health'][0];
            //$health = go_mod_loot($health, $health_toggle, $health_mod_toggle, $stage_mod, $health_mod);

        } else if ($status !== null && $progressing === true) {
            /// get modified stage loot
            $xp = $custom_fields['go_stages_' . $status . '_rewards_xp'][0];
            //$xp = go_mod_loot($xp, $xp_toggle, $xp_mod_toggle, $stage_mod, $health_mod);
            $gold = $custom_fields['go_stages_' . $status . '_rewards_gold'][0];
            $gold = go_mod_loot($gold, $stage_mod, $health_mod);
            $health = $custom_fields['go_stages_' . $status . '_rewards_health'][0];
            //$health = go_mod_loot($health, $health_toggle, $health_mod_toggle, $stage_mod, $health_mod);
        } else if ($bonus_status !== null && $progressing === true) {
            /// get modified bonus stage loot
            $xp = $custom_fields['go_bonus_stage_rewards_xp'][0];
            //$xp = go_mod_loot($xp, $xp_toggle, $xp_mod_toggle, $stage_mod, $health_mod);
            $gold = $custom_fields['go_bonus_stage_rewards_gold'][0];
            $gold = go_mod_loot($gold, $stage_mod, $health_mod);
            $health = $custom_fields['go_bonus_stage_rewards_health'][0];
            //$health = go_mod_loot($health, $health_toggle, $health_mod_toggle, $stage_mod, $health_mod);
        }

        $xp_toggle = get_option('options_go_loot_xp_toggle');
        $gold_toggle = get_option('options_go_loot_gold_toggle');
        $health_toggle = get_option( 'options_go_loot_health_toggle' );
        if (!$xp_toggle) {
            $xp = 0;
        }
        if (!$gold_toggle) {
            $gold = 0;
        }
        if (!$health_toggle) {
            $health = 0;
        }

        //make sure we don't go over 200 health
        //$health = go_health_to_add($user_id, $health);


        $badges_toggle = get_option('options_go_badges_toggle');
        //ADD BADGES
        if ($badges_toggle && !empty($badge_ids)) {
            $new_badges = go_add_badges ($badge_ids, $user_id, true);
            //$badge_count = count($new_badges);
            $badge_ids = serialize($new_badges);
        }

        //ADD GROUPS
        if (!empty($group_ids)) {
            $new_groups = go_add_groups ($group_ids, $user_id, true);
            $group_ids = serialize($new_groups);
        }

    } //end progressing = true
    else if ($progressing === false) {
        $action_type = 'undo_task';
        if ($status !== null) {
            $new_status_task = $status - 1;
            $new_status_actions = $status;
        } else {
            $new_status_task = 'null';
            $new_status_actions = 'null';
        }

        if ($bonus_status !== null) {
            $new_bonus_status_task = $bonus_status - 1;
            $new_bonus_status_actions = $bonus_status;
        } else {
            $new_bonus_status_task = 'null';
            $new_bonus_status_actions = 'null';
        }

        $row = $wpdb->get_row($wpdb->prepare("SELECT *
                FROM {$go_actions_table_name} 
                WHERE uid = %d and source_id  = %d and stage = %d 
                ORDER BY id DESC LIMIT 1", $user_id, $post_id, $status));
        $xp = ($row->xp) * -1;
        $gold = ($row->gold) * -1;
        $health = ($row->health) * -1;

        if ($status != 0) {
            $badges = serialize($badge_ids);
            $badge_ids = go_remove_badges($badges, $user_id, true);
            $badge_ids = serialize($badge_ids);

            $groups = serialize($group_ids);
            $group_ids = go_remove_groups($groups, $user_id, true);
            $group_ids = serialize($group_ids);
        }
    }



    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$go_task_table_name} 
                    SET 
                        status = IFNULL({$new_status_task}, status),
                        bonus_status = IFNULL({$new_bonus_status_task}, bonus_status),
                        xp = {$xp} + xp,
                        gold = {$gold} + gold,
                        health = {$health} + health,
                        last_time = '{$last_time}',
                        class = %s  
                    WHERE uid= %d AND post_id=%d ",
            $class,
            $user_id,
            $post_id
        )
    );

    if(!empty($badge_ids)){
        $wpdb->update($go_task_table_name,
            array('badges' => $badge_ids ),//data
            array('uid' => $user_id, 'post_id' => $post_id),//where
            array('%s'),//data format
            array('%d', '%d')//where data format
        );
    }

    if(!empty($group_ids)){
        $wpdb->update($go_task_table_name,
            array('groups' => $group_ids ),//data
            array('uid' => $user_id, 'post_id' => $post_id),//where
            array('%s'),//data format
            array('%d', '%d')//where data format
        );
    }


    go_update_actions($user_id, $action_type,  $post_id, $new_status_actions, $new_bonus_status_actions, $check_type, $result, $quiz_mod, $due_date_mod, $timer_mod, $health_mod,  $xp, $gold, $health, $badge_ids, $group_ids, true, true);

}

/**
 * @param $badge_ids
 * @param $user_id
 * @param bool $notify
 * @return array|mixed|null
 */
function go_add_badges ($badge_ids, $user_id, $notify = false) {
    if(is_serialized($badge_ids)){
        $badge_ids = unserialize($badge_ids);
    }
    $key = go_prefix_key('go_badge');
    $badges_added = array();

    if (is_array($badge_ids)) {
        foreach ($badge_ids as $badge_id) {
            if(!empty($badge_id)) {
                $existing_array = get_user_meta($user_id, $key, false);
                if (!in_array($badge_id, $existing_array)) {
                    add_user_meta($user_id, $key, $badge_id, false);
                    if ($notify === true) {
                        go_term_notification('badges', $badge_id, true);
                    }
                    $badges_added[] = $badge_id;
                }
            }
        }
    }else if (is_numeric($badge_ids)){
        $badge_id = $badge_ids;
        $existing_array = get_user_meta($user_id, $key, false);
        if(!in_array ( $badge_id, $existing_array )) {
            add_user_meta($user_id, $key, $badge_id, false);
            if ($notify === true) {
                go_term_notification('badges', $badge_id, true);
            }
            $badges_added[]=$badge_id;
        }
    }
    //update the badge count
    $key = go_prefix_key('go_badge');
    $badges_array = get_user_meta($user_id, $key, false);
    $badge_count = count($badges_array);
    go_update_badge_count($badge_count, $user_id);

    //reset the loot transient
    $key = 'go_get_loot_' . $user_id;
    go_delete_transient($key);

    return $badges_added;
}

function go_term_notification($term, $term_id, $add){
    $term_obj = get_term($term_id);
    $term_name = $term_obj->name;
    $title = get_option('options_go_'. $term . '_name_singular');
    $img_id = get_term_meta( $term_id, 'my_image' );
    if (isset($img_id[0]) && !empty($img_id[0])){
        $img = wp_get_attachment_image($img_id[0], array( 100, 100 ));
    }else{
        if($term === 'badges') {
            $img = '<i class="fas fa-award fa-4x"></i>';
        }
        else if($term === 'groups') {
            $img = '<i class="fas fa-users fa-4x"></i>';
        }
    }
    if($add){
        $prefix = 'New ';
        $type = 'success';
    }else{
        $prefix = 'Remove ';
        $type = 'error';
    }
    $title = $prefix . ucfirst($title);
    //go_noty_loot_success($badge_name, $message);
    $content = "<div>" . $img . "<br>" . $term_name . "</div>";
    go_noty_message_generic ($type, $title, $content);
}

/**
 * @param $badge_ids
 * @param $user_id
 * @param bool $notify
 * @return array
 */
function go_remove_badges ($badge_ids, $user_id, $notify = false) {

    if(is_serialized($badge_ids)){
        $badge_ids = unserialize($badge_ids);
    }
    $key = go_prefix_key('go_badge');
    $badges_removed = array();
    if (is_array($badge_ids)) {
        foreach ($badge_ids as $badge_id) {
            if(!empty($badge_id)) {
                delete_user_meta($user_id, $key, $badge_id);
                if ($notify === true) {
                    go_term_notification('badges', $badge_id, false);
                }
                $badges_removed[] = $badge_id;
            }
        }
    }else if (is_numeric($badge_ids)){
        $badge_id = $badge_ids;
        delete_user_meta($user_id, $key, $badge_id);
        if ($notify === true) {
            go_term_notification('badges', $badge_id, false);
        }
        $badges_removed[]=$badge_id;
    }

    $key = go_prefix_key('go_badge');
    $badges_array = get_user_meta($user_id, $key, false);
    $badge_count = count($badges_array);
    go_update_badge_count($badge_count, $user_id);

    $key = 'go_get_loot_' . $user_id;
    go_delete_transient($key);
    return $badges_removed;
}


function go_update_badge_count($badge_count, $user_id){
    global $wpdb;
    $go_totals_table_name = "{$wpdb->prefix}go_loot";

    $wpdb->query($wpdb->prepare("UPDATE {$go_totals_table_name} 
                    SET 
                        badge_count = {$badge_count}                      
                    WHERE uid= %d", $user_id));
}

/**
 * @param $group_ids
 * @param $user_id
 * @param bool $notify
 * @return array|mixed|null
 */
function go_add_groups($group_ids, $user_id, $notify = false) {
    if(is_serialized($group_ids)){
        $group_ids = unserialize($group_ids);
    }

    $key = go_prefix_key('go_group');
    $groups_added = array();
    if (is_array($group_ids)) {
        foreach ($group_ids as $group_id) {
            if(!empty($group_id)) {
                $existing_array = get_user_meta($user_id, $key, false);
                if (!in_array($group_id, $existing_array)) {
                    add_user_meta($user_id, $key, $group_id, false);
                    if ($notify === true) {
                        go_term_notification('groups', $group_id, true);
                    }
                    $groups_added[] = $group_id;
                }
            }
        }
    }else if (is_numeric($group_ids)){
        $group_id = $group_ids;
        $existing_array = get_user_meta($user_id, $key, false);
        if(!in_array ( $group_id, $existing_array )) {
            add_user_meta($user_id, $key, $group_id, false);
            if ($notify === true) {
                go_term_notification('groups', $group_id, true);
            }
            $groups_added[]=$group_id;
        }
    }

    $key = 'go_get_loot_' . $user_id;
    go_delete_transient($key);

    return $groups_added;
}



/**
 * @param $group_ids
 * @param $user_id
 * @param bool $notify
 * @return array
 */
function go_remove_groups($group_ids, $user_id, $notify = false) {

    if(is_serialized($group_ids)){
        $group_ids = unserialize($group_ids);
    }

    $key = go_prefix_key('go_group');

    if (is_array($group_ids)) {
        foreach ($group_ids as $group_id) {
            if(!empty($group_id)) {
                delete_user_meta($user_id, $key, $group_id);
                if ($notify === true) {
                    go_term_notification('groups', $group_id, false);
                }
            }
        }
    }else if (is_numeric($group_ids)){
        $group_id = $group_ids;
        delete_user_meta($user_id, $key, $group_id);
        if ($notify === true) {
            go_term_notification('groups', $group_id, false);
        }
    }
    $key = 'go_get_loot_' . $user_id;
    go_delete_transient($key);
}

function go_print_single_badge( $term_id, $type = 'badge', $output = true, $user_id = null, $additional_class = null){

    //if user_id is passed, find if badge is earned
    if($user_id != null) {
        if($type === 'badge') {
            $key = go_prefix_key('go_badge');
        }else{
            $key = go_prefix_key('go_group');
        }
        $badges_array = get_user_meta($user_id, $key, false);

        if (empty($badges_array)) {
            $badges_array = array();
        }

        $badge_assigned = in_array($term_id, $badges_array);
        if ($badge_assigned) {
            $class = 'go_badge_earned';
        } else {
            $class = 'go_badge_needed';
        }

    }else{
        $class = '';
    }

    if ($additional_class != null){
        $class .= " go_map_badge";
    }

    $obj = get_term($term_id);
    if (!empty($obj)) {
        $name = $obj->name;
        $id = $obj->term_id;

        $icon_toggle = get_term_meta( $id, 'image_source' );
        $icon_toggle = (isset($icon_toggle[0]) ?  $icon_toggle[0] : false);

        if($icon_toggle[0] === '1'){
            $icon = get_term_meta( $id, 'icon' );
            $color = get_term_meta( $id, 'icon_color' );
            $img = '<i class="'.$icon[0].' fa-4x" style="color:'.$color[0].'"></i>';

        }
        else{
            $img_id = get_term_meta( $id, 'my_image' );
            $img ='';
            if (isset($img_id[0]) && !empty($img_id[0])){
                $img = wp_get_attachment_image($img_id[0], array( 100, 100 ));
            }else{
                if ($type === 'badge') {
                    $img = '<i class="fas fa-award fa-4x" style="height: 100px"></i>';
                }
                else if($type ==='group'){
                    $img = '<i class="fas fa-users fa-4x" style="height: 100px"></i>';
                }
            }
        }


        $description = term_description( $id );



        $badge = "<div class='go_badge_wrap'><div class='go_badge_container $class'>";

        if (!empty($description)){
           $badge .= "<span class='tooltip' data-tippy-content='{$description}'><figure class=go_badge title='{$name}'>{$img}<figcaption>{$name}</figcaption></figure></span>";
        }else{
            $badge .= "<figure class=go_badge title='{$name}'>$img<figcaption>{$name}</figcaption></figure>";
        }
        $badge .= "</div></div>";


        if($output){
            echo $badge;
        }
        else{
            return $badge;
        }

    }
}


/**
 * @param $loot
 * @param $toggle
 * @param $mod_toggle
 * @param $stage_mod
 * @param $health_mod
 * @return float|int
 */
function go_mod_loot($loot, $stage_mod, $health_mod)
{
    $loot = ($loot - ($loot * $stage_mod));
    $loot = $loot * $health_mod;
    return $loot;
}

//makes sure health doesn't go over 200
/**
 * @param $user_id
 * @param $added_health
 * @return int|mixed
 */
function go_health_to_add($user_id, $added_health){
    $current_health = go_get_user_loot( $user_id, 'health' );
    $max_new_health = 200 - $current_health;

    if ($max_new_health < $added_health){
        $added_health = $max_new_health;
    }
    return $added_health;
}

/**
 * @param $user_id
 * @param $type
 * @param $source_id
 * @param $status
 * @param $bonus_status
 * @param $check_type
 * @param $result
 * @param $quiz_mod
 * @param $late_mod
 * @param $timer_mod
 * @param $global_mod
 * @param $xp
 * @param $gold
 * @param $health
 * @param $badge_ids
 * @param $group_ids
 * @param $notify
 * @param $debt
 */
function go_update_actions($user_id, $type, $source_id, $status, $bonus_status, $check_type, $result, $quiz_mod, $late_mod, $timer_mod, $global_mod, $xp, $gold, $health, $badge_ids, $group_ids, $notify)
{

    global $wpdb;

    if (get_option('options_go_loot_xp_toggle') == false) {
        $xp = 0;
    }
    if (get_option('options_go_loot_gold_toggle') == false) {
        $gold = 0;
    }
    if (get_option('options_go_loot_health_toggle') == false) {
        $health = 0;
    }


    $user_loot = go_get_loot($user_id);

    // the user's current amount of experience (points)
    $go_current_xp = $user_loot['xp'];
    $new_xp_total = $go_current_xp + $xp;
    if ($new_xp_total < 0) {
        $new_xp_total = 0;
        $xp = $go_current_xp * -1;//ony subtract the amount that a player has
    }


    //Get the health before the gold incase there is a bankruptcy penalty
    $go_current_health = $user_loot['health'];


    // the user's current amount of currency
    $go_current_gold = $user_loot['gold'];
    $new_gold_total = $go_current_gold + $gold;
    //penalty for bankruptcy
    if($new_gold_total < 0 && $type != 'admin'){
        $new_gold_total = 0;
        $gold = $go_current_gold * -1;//ony subtract the amount that a player has


        //send message about bankruptcy
        $health_penalty = get_option('options_go_loot_health_bankruptcy_penalty') * -1;

        $gold_name = go_get_gold_name();
        $health_name = get_option('options_go_loot_health_name');
        $title = $health_name . " Penalty";
        $message = 'You did not have enough ' . $gold_name . " for this action.";
        $vars = array();
        $vars[0]['uid'] = $user_id;

       go_send_message(true, $title, $message, 'message', true, 0, 0, $health_penalty, $source_id, false, '', '', $vars);
       $go_current_health = $go_current_health + $health_penalty;//adjust current health because message will change the total
    }

    // the user's current amount of bonus currency,
    // also used for % in the admin bar

    $new_health_total = $go_current_health + $health;
    if ($new_health_total < 0) {
        $new_health_total = 0;
        $health = $go_current_health * -1;//ony subtract the amount that a player has
    }
    /*
    else if ($new_health_total > 200) {
        $new_health_total = 200;
        $health = 200 - $go_current_health;
    }*/

    $go_actions_table_name = "{$wpdb->prefix}go_actions";
    //$time = date( 'Y-m-d G:i:s', current_time( 'timestamp', 0 ) );
    $time = current_time('mysql');

    $wpdb->insert($go_actions_table_name, array('uid' => $user_id, 'action_type' => $type, 'source_id' => $source_id, 'TIMESTAMP' => $time, 'stage' => $status, 'bonus_status' => $bonus_status, 'check_type' => $check_type, 'result' => $result, 'quiz_mod' => $quiz_mod, 'late_mod' => $late_mod, 'timer_mod' => $timer_mod, 'global_mod' => $global_mod, 'xp' => $xp, 'gold' => $gold, 'health' => $health, 'badges' => $badge_ids, 'groups' => $group_ids, 'xp_total' => $new_xp_total, 'gold_total' => $new_gold_total, 'health_total' => $new_health_total));

    //if notify = admin than this action is just creating
    //a message and should not update the totals.
    //The totals will be updated in another call to this function.
    //So, if this is not a store item with admin notifications, then update the totals table
    if ($notify !== 'admin') {
        go_update_totals_table($user_id, $xp, $gold, $health, $notify);
        if ($notify === true) {
            go_update_admin_bar($user_id);
        }
    }


    //badges and groups are only updated from the add/remove badges and groups functions
}

/**
 * @param $user_id
 * @param $xp
 * @param $xp_name
 * @param $gold
 * @param $gold_name
 * @param $health
 * @param $health_name
 * @param $notify
 * @param $debt
 */
function go_update_totals_table($user_id, $xp, $gold, $health, $notify){
    global $wpdb;

    //$key = 'go_get_loot_' . $user_id;

    $key = 'go_get_loot_' . $user_id;
    $blog_id = get_current_blog_id();
    $group = 'blog_'.$blog_id;


    go_delete_transient($key);
    //wp_cache_delete( $key, 'go_single' );
    wp_cache_delete( $key, $group );

    $go_totals_table_name = "{$wpdb->prefix}go_loot";

    //create row for user if none exists
    go_add_user_to_totals_table($user_id);
    /*
    if ($debt == true) {
        $wpdb->query($wpdb->prepare("UPDATE {$go_totals_table_name}
                    SET
                        xp = {$xp} + xp,
                        gold = {$gold} + gold,
                        health = {$health} + health
                    WHERE uid= %d", $user_id));
    }else{}*/
    $max_health = get_option('options_go_loot_health_max_health_mod');

    $wpdb->query($wpdb->prepare("UPDATE {$go_totals_table_name} 
                SET 
                    xp = GREATEST(({$xp} + xp), 0),
                    gold = GREATEST(({$gold} + gold ), 0),
                    health = LEAST(GREATEST(({$health} + health ), 0), {$max_health})            
                WHERE uid= %d", $user_id));


    if ($xp != 0) {
        $new_rank = go_get_rank($user_id);
        $rank_num = $new_rank['rank_num'];
        $rank_name = $new_rank['current_rank'];
        $old_rank = get_user_option("go_rank", $user_id);
        if ($rank_num > $old_rank){

            $i = $rank_num - $old_rank;
            while ($i > 0){
                $badge_level = $rank_num - $i;//adjust for starting at row 0
                $badge = get_option('options_go_loot_xp_levels_level_'.$badge_level.'_award_badge');
                if(!empty($badge)){
                    if ($user_id == get_current_user_id()) {
                        go_add_badges($badge, $user_id, true);
                    }else{
                        $vars[0]['uid']= $user_id;
                        go_send_message(true, '', '', 'message', false, 0, 0, 0, null, true, $badge, '', $vars);
                    }

                }
                $i--;
            }


            update_user_option($user_id, "go_rank", $rank_num);
            go_noty_level_up($rank_num, $rank_name );
            echo "<script>var audio = new Audio( PluginDir.url + 'media/sounds/milestone2.mp3' ); audio.play();</script>";
        }

        if ($rank_num < $old_rank){
            update_user_option($user_id, "go_rank", $rank_num);
            go_noty_level_down($rank_num, $rank_name );

            $i = $old_rank - $rank_num;
            while ($i > 0){
                $badge_level = $old_rank - $i;//adjust for starting at row 0
                $badge = get_option('options_go_loot_xp_levels_level_'.$badge_level.'_award_badge');
                if(!empty($badge)){

                    if ($user_id == get_current_user_id()) {
                        go_remove_badges ($badge, $user_id, true);
                    }else{
                        $vars[0]['uid']= $user_id;
                        go_send_message(true, '', '', 'message', true, null, null, null, null, false, $badge, '', $vars);
                    }
                }
                $i--;
            }
        }
    }

    if ($notify === true) {
        $up = false;
        $down = false;

        if($xp != 0){
            $xp_loot = go_display_shorthand_currency('xp', $xp);

            if ($xp > 0) {
                $text = go_loot_notification_text('xp', '+'.$xp_loot );
                go_noty_loot_success($text);
                $up = true;
            }
            else if ($xp < 0) {
                $text = go_loot_notification_text('xp', $xp_loot );
                go_noty_loot_error($text);
                $down = true;
            }
        }


        if($gold != 0){
            $gold_loot = go_display_shorthand_currency('gold', $gold, false, false);

            if ($gold > 0) {
                $text = go_loot_notification_text('gold', '+'.$gold_loot );
                go_noty_loot_success($text);
                $up = true;
            }
            else if ($gold < 0) {
                $text = go_loot_notification_text('gold', $gold_loot );
                go_noty_loot_error($text);
                $down = true;
            }
        }


        if ($health!=0){
            $health_loot = go_display_shorthand_currency('health', $health);

            if ($health > 0) {
                $text = go_loot_notification_text('health', '+'.$health_loot );
                go_noty_loot_success($text);
                $up = true;
            }
            else if ($health < 0) {
                $text = go_loot_notification_text('health', $health_loot );
                go_noty_loot_error($text);
                $down = true;
            }
        }


        if ($up == true){
            $sound = go_up_sound();
            echo $sound;
        }
        if ($down == true){
            $sound = go_down_sound();
            echo $sound;
        }

    }
}



function go_loot_notification_text($loot_type, $loot){

    if($loot_type === 'gold') {
        $color = get_option('options_go_loot_gold_icon_color');
        /*if(empty($color)){
            $color = "#ffffff";
        }*/
        $icon = get_option('options_go_loot_gold_icon_option');
        if($icon === 'custom'){
            $icon = get_option('options_go_loot_gold_icon_icon');
            $icon = '<i class="'.$icon.' fa-3x" style="color:'.$color.';"></i>';
            $text = '<div >'.$icon.'<div style=\"font-size: 1.5em;\">' . addslashes($loot) . '</div></div>';

        }else if( $icon === 'default'){
            $coin_color = get_option('options_go_loot_gold_icon_coin_color');
            $gold_icon = get_option('options_go_loot_gold_icon_icon');
            $gold_icon = '<i class="'.$gold_icon.'" style="display: table-cell; vertical-align: middle; color:darkgrey;"></i>';
            $gold_icon_back = get_option('options_go_loot_gold_icon_icon_back');
            $gold_icon_back = '<i class="'.$gold_icon_back.'" style="display: table-cell; vertical-align: middle; color:darkgrey;"></i>';
            $icon = '<div><div style=\"font-size: 1.5em;\">' . addslashes($loot) . '</div></div>';
            $text = '<div class="coin" ><div class="coin__front" style="background-color:'.$coin_color.';" >'.$gold_icon.'</div><div class=\"coin__edge\"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div><div class="coin__back" style="background-color:'.$coin_color.';">'.$gold_icon_back.'</div><div class=\"coin__shadow\"></div></div>'.$icon;
            $rgba = go_hex2rgba($coin_color, .5);
            $text .= "<style> .coin__edge div{border: 1px solid ".$rgba." !important;}</style>";
        }else{

            $text = '<div><div style="font-size: 1.5em;">' . addslashes($loot) . '</div></div>';
        }
    }else if($loot_type == 'xp') {
        $xp_icon_toggle = get_option('options_go_loot_xp_icon_toggle');
        $color = get_option('options_go_loot_xp_icon_color');
        if($xp_icon_toggle){
            $icon = get_option('options_go_loot_xp_icon_icon');
            $icon = '<i class="'.$icon.' fa-3x" style="color:'.$color.';"></i>';
            $text = '<div>'.$icon.'<div style=\"font-size: 1.5em;\">' . addslashes($loot) . '</div></div>';
        }else{
            $text = '<div><div style="font-size: 1.5em;">' . addslashes($loot) . '</div></div>';
        }
    }else if($loot_type == 'health') {
        $health_icon_toggle = get_option('options_go_loot_health_icon_toggle');
        $color = get_option('options_go_loot_health_icon_color');
        if( $health_icon_toggle ){
            $icon = get_option('options_go_loot_health_icon_icon');
            $icon = '<i class="'.$icon.' fa-3x" style="color:'.$color.';"></i>';
            $text = '<div>'.$icon.'<div style=\"font-size: 1.5em;\">' . addslashes($loot) . '</div></div>';
        }else{
            $text = '<div><div style="font-size: 1.5em;">' . addslashes($loot) . '</div></div>';
        }
    }
return $text;
}


/**
 * @param $loot
 * @param $loot_type
 */
function go_noty_loot_success ($text) {
    //go_noty_close_oldest();
    echo "
    <script>
        jQuery( document ).ready( function() {
            new Noty({
                type: 'success',
                layout: 'topRight',
                text: '$text',
                theme: 'sunset',
                timeout: '3000',
                visibilityControl: true,
                callbacks: {
                    beforeShow: function() { go_noty_close_oldest();},
                }
            }).show();
        });
    </script>";

}


/* Convert hexdec color string to rgb(a) string */

function go_hex2rgba($color, $opacity = false) {

    $default = 'rgb(0,0,0)';

    //Return default if no color provided
    if(empty($color))
        return $default;

    //Sanitize $color if "#" is provided
    if ($color[0] == '#' ) {
        $color = substr( $color, 1 );
    }

    //Check if color has 6 or 3 characters and get values
    if (strlen($color) == 6) {
        $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
    } elseif ( strlen( $color ) == 3 ) {
        $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
    } else {
        return $default;
    }

    //Convert hexadec to rgb
    $rgb =  array_map('hexdec', $hex);

    //Check if opacity is set(rgba or rgb)
    if($opacity){
        if(abs($opacity) > 1)
            $opacity = 1.0;
        $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
    } else {
        $output = 'rgb('.implode(",",$rgb).')';
    }

    //Return rgb(a) color string
    return $output;
}


/**
 * @param $rank
 * @param $rank_name
 */
function go_noty_level_up ($rank, $rank_name) {
    //go_noty_close_oldest();
    echo "<script> 
        jQuery( document ).ready( function() {
        new Noty({
            type: 'success',
            layout: 'topRight',
            text: '<h2>Level Up! You are now Level " . addslashes($rank) . " (" . addslashes($rank_name) . ").</h2>',
            theme: 'sunset', 
            timeout: false,
            visibilityControl: true,
            callbacks: {
                beforeShow: function() { go_noty_close_oldest();},
            }
        
        }).show(); 
        });
        </script>";
}

/**
 * @param $rank
 * @param $rank_name
 */
function go_noty_level_down ($rank, $rank_name) {
    //go_noty_close_oldest();
    echo "<script> 
        jQuery( document ).ready( function() {
        new Noty({
            type: 'error',
            layout: 'topRight',
            text: '<h2>Level Down! You are now Level " . addslashes($rank) . " (" . addslashes($rank_name) . ").</h2>',
            theme: 'sunset', 
            timeout: false,
            visibilityControl: true,
            callbacks: {
                beforeShow: function() { go_noty_close_oldest();},
            }
            
        }).show();  
        });
    </script>";
}

/**
 * @param $loot
 * @param $loot_type
 */
function go_noty_loot_error ($text) {

    //go_noty_close_oldest();
    echo "<script> 
        jQuery( document ).ready( function() {
        new Noty({
            type: 'error',
            layout: 'topRight',
            text: '$text',
            theme: 'sunset',
            timeout: '3000',
            visibilityControl: true,
            callbacks: {
                beforeShow: function() { go_noty_close_oldest();},
            }
        }).show();
        }); 
    </script>";
}

/**
 * @param string $type
 * @param $title
 * @param $content
 * @param $timeout
 */
function go_noty_message_generic ($type = 'alert', $title, $content, $timeout = 3000) {
    if (!empty($title)){
        $text = "<h2>" . $title . "</h2><div>" . $content . "</div>";
    }
    else{
        $text = $content;
    }
    //go_noty_close_oldest();
    echo "<script> 
        jQuery( document ).ready( function() { 
           new Noty({
                type: '" . $type . "',
                layout: 'topRight',
                text: '" . addslashes($text) . "',
                theme: 'sunset',
                timeout: '" . $timeout . "',
                visibilityControl: true,
                //closeWith: ['button'], // ['click', 'button', 'hover', 'backdrop'] // backdrop click will close all notifications
                modal: true,
                callbacks: {
                    beforeShow: function() { go_noty_close_oldest();},
                }   
            }).show();
        });</script>";
}

/**
 * @param string $type
 * @param $title
 * @param $content
 */
function go_noty_message_modal($type = 'alert', $title, $content) {
    if (!empty($title)){
        $text = "<h2>" . $title . "</h2><div>" . $content . "</div>";
    }
    else{
        $text = $content;
    }
    if(go_user_is_admin()){
        $closeWith = 'click';
        $modal = '';
        $timeout = "timeout: '3000',";
        //$closeWith = 'button';
        //$modal = true;
    } else {
        $closeWith = 'button';
        $modal = 'modal: true,';
        $timeout = '';

    }
    //go_noty_close_oldest();
    echo "<script> 
        jQuery( document ).ready( function() { 
           new Noty({
                type: '" . $type . "',
                layout: 'topRight',
                text: '" . addslashes($text) . "',
                " . $timeout . "
                theme: 'sunset',
                visibilityControl: true,
                closeWith: ['" . $closeWith . "'], // ['click', 'button', 'hover', 'backdrop'] // backdrop click will close all notifications
                " . $modal . "
                callbacks: {
                    beforeShow: function() { go_noty_close_oldest();},
                }   
            }).show();
        });</script>";
}




function go_up_sound(){
    $sound =  "<script>var audio = new Audio( PluginDir.url + 'media/sounds/coins.mp3' ); audio.play();</script>";
    return $sound;
}

function go_down_sound(){
    $sound = "<script>var audio = new Audio( PluginDir.url + 'media/sounds/down.mp3' ); audio.play();</script>";
    return $sound;
}


/**
 * @param $user_id
 * @param $xp
 * @param $xp_name
 * @param $gold
 * @param $gold_name
 * @param $health
 * @param $health_name
 */
function go_update_admin_bar($user_id) {


    //$user_loot = go_get_loot($user_id);

    ?><script language='javascript'>
        jQuery(document).ready(function() {
    <?php

    if (get_option('options_go_loot_xp_toggle')){

        $progress_bar = xp_progress_bar($user_id);
        echo "jQuery( '.go_admin_bar_progress_bar_border' ).replaceWith( '{$progress_bar}' );";

    }
    if (get_option('options_go_loot_gold_toggle')){

        $gold_bar = go_gold_bar($user_id);
        echo "jQuery( '#go_user_bar_loot' ).replaceWith( '{$gold_bar}' );";

    }
    if (get_option('options_go_loot_health_toggle')){

        $health_bar = go_health_bar($user_id);
        echo "jQuery( '.go_admin_health_bar_border' ).replaceWith( '{$health_bar}' );";

    }

    echo "
			} );
		</script>";
}

/**
 * @param $user_id
 */
function go_add_user_to_totals_table($user_id){
    global $wpdb;
    $go_totals_table_name = "{$wpdb->prefix}go_loot";
    $default_health = get_option('options_go_loot_health_starting');

    //create row for user if none exists
    $row_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID 
					FROM {$go_totals_table_name} 
					WHERE uid = %d LIMIT 1",
            $user_id
        )
    );

    //create the row
    if ( $row_exists == null ) {
        $wpdb->insert(
            $go_totals_table_name,
            array(
                'uid' => $user_id,
                'health' => $default_health
            )
        );
    }
}

/**
 * @param $custom_fields
 * @param bool $health_mod
 * @param string $user_id
 * @return array
 */
function go_get_bonus_loot_rows($custom_fields, $health_mod = false, $user_id = 'guest'){


    if (!$health_mod) {//if the health mod was not passed, get it
        $health_mod = 1;//default mod
        $health_toggle = get_option('options_go_loot_health_toggle');
        if ($health_toggle) {
            if ($user_id != 'guest') {//if not a guest user
                $health_mod = go_get_health_mod($user_id);//get this users health level
            }
        }
    }

    //LOGIC
    //get bonus radio
    //if default, get default values
    //if custom, get custom
    //return rows found as values array

    $bonus_radio =(isset($custom_fields['bonus_loot_toggle'][0]) ? $custom_fields['bonus_loot_toggle'][0] : null);//is bonus set default, custom or off


    //1=custom bonus loot
    if ($bonus_radio == "1" || $bonus_radio == "default") {
        if ($bonus_radio == "1") {
            $key_prefix = 'bonus_loot_go_bonus_loot_';
            $row_count = (isset($custom_fields['bonus_loot_go_bonus_loot'][0]) ? $custom_fields['bonus_loot_go_bonus_loot'][0] : null);//number of loot drops
        }else if ($bonus_radio == "default"){
            $key_prefix = 'options_go_loot_bonus_loot_';
            $row_count = get_option('options_go_loot_bonus_loot');
        }


        $values = array();
        if (!empty($row_count)) {//if there are drop rows
            for ($i = 0; $i < $row_count; $i++) {//get the values for each row
                $message = $key_prefix . $i . "_message";
                $title = $key_prefix . $i . "_title";
                $xp = $key_prefix . $i . "_defaults_xp";
                $gold = $key_prefix . $i . "_defaults_gold";
                $health = $key_prefix . $i . "_defaults_health";
                $drop = $key_prefix . $i . "_defaults_drop_rate";

                if ($bonus_radio == "1") {
                    $title = (isset($custom_fields[$title][0]) ? $custom_fields[$title][0] : null);
                    $message = (isset($custom_fields[$message][0]) ? $custom_fields[$message][0] : null);
                    $xp = (isset($custom_fields[$xp][0]) ? $custom_fields[$xp][0] : null);
                    $gold = (isset($custom_fields[$gold][0]) ? $custom_fields[$gold][0] : null) * $health_mod;
                    $health = (isset($custom_fields[$health][0]) ? $custom_fields[$health][0] : null);
                    $drop = (isset($custom_fields[$drop][0]) ? $custom_fields[$drop][0] : null);
                }else if($bonus_radio == "default"){
                    $title = get_option($title);
                    $message = get_option($message);
                    $xp = get_option($xp);
                    $gold = get_option($gold) * $health_mod;
                    $health = get_option($health);
                    $drop = get_option($drop);
                }

                $row_val = array('title' => $title, 'message' => $message, 'xp' => $xp, 'gold' => $gold, 'health' => $health, 'drop' => $drop);

                $values[] = $row_val;//stuff each row in to an array

            }
        }
        //sort by drop rate
        $bonus_option = array();
        foreach ($values as $key => $row) {
            $bonus_option[$key] = $row['drop'];
        }
        array_multisort($bonus_option, SORT_ASC, $values);

        return $values;
    }

    return array();
}

/**
 * @param $post_id
 */
function go_update_bonus_loot ($post_id){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_update_bonus_loot' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_update_bonus_loot' ) ) {
        echo "refresh";
        die( );
    }
    $post_id = $_POST['post_id'];

    $user_id = get_current_user_id();
    global $wpdb;

    $go_actions_table_name = "{$wpdb->prefix}go_actions";

    //check to see if the bonus has been attempted by this user on this task before.  You can only get 1 bonus per task.
    $previous_bonus_attempt = $wpdb->get_var($wpdb->prepare("SELECT result 
                FROM {$go_actions_table_name} 
                WHERE source_id = %d AND uid = %d AND action_type = %s
                ORDER BY id DESC LIMIT 1", $post_id, $user_id, 'bonus_loot'));

    //ob_start();
    if(!empty($previous_bonus_attempt)) {
        //if(0==1){
        go_noty_message_generic('error', '', "You have previously attempted this bonus.  No award given.");


    }else {
        //if health toggle is on, get health.  Health affects the bonus.
        $health_toggle = get_option('options_go_loot_health_toggle');
        if ($health_toggle) {

            $health_mod = go_get_health_mod($user_id);

        } else {
            $health_mod = 1;
        }



        $custom_fields = go_post_meta( $post_id );


        /*
        $row_count = (isset($custom_fields['bonus_loot_go_bonus_loot'][0]) ? $custom_fields['bonus_loot_go_bonus_loot'][0] : null);//number of loot drops
        $values = array();
        if (!empty($row_count)) {//if there are drop rows
            for ($i = 0; $i < $row_count; $i++) {//get the values for each row
                $message = "bonus_loot_go_bonus_loot_" . $i . "_message";
                $message = (isset($custom_fields[$message][0]) ? $custom_fields[$message][0] : null);
                $title = "bonus_loot_go_bonus_loot_" . $i . "_title";
                $title = (isset($custom_fields[$title][0]) ? $custom_fields[$title][0] : null);
                $xp = "bonus_loot_go_bonus_loot_" . $i . "_defaults_xp";
                $xp = (isset($custom_fields[$xp][0]) ? $custom_fields[$xp][0] : null) * $health_mod;
                $gold = "bonus_loot_go_bonus_loot_" . $i . "_defaults_gold";
                $gold = (isset($custom_fields[$gold][0]) ? $custom_fields[$gold][0] : null) * $health_mod;;
                $health = "bonus_loot_go_bonus_loot_" . $i . "_defaults_health";
                $health = (isset($custom_fields[$health][0]) ? $custom_fields[$health][0] : null);
                $drop = "bonus_loot_go_bonus_loot_" . $i . "_defaults_drop_rate";
                $drop = (isset($custom_fields[$drop][0]) ? $custom_fields[$drop][0] : null);

                $row_val = array('title' => $title, 'message' => $message, 'xp' => $xp, 'gold' => $gold, 'health' => $health, 'drop' => $drop);

                $values[] = $row_val;//stuff each row in to an array

            }

        }
        //sort by drop rate
        $bonus_option = array();
        foreach ($values as $key => $row) {
            $bonus_option[$key] = $row['drop'];
        }
        array_multisort($bonus_option, SORT_ASC, $values);
        */

        $values = go_get_bonus_loot_rows($custom_fields, $health_mod, $user_id);


        //add all the drop rates together
        //if greater than 100, treat them as ratios
        $drop_total = 0;
        foreach ($values as $value) {
            $drop_total = $value['drop'] + $drop_total;
        }
        if ($drop_total < 100){
            $drop_total = 100;
        }

        $drop_total = $drop_total * 1000;

        $winner = false;
        foreach ($values as $value) { //for each drop, test to award randomly
            $drop = $value['drop'] * 1000;

            $rand = mt_rand(0, $drop_total);
            if ( $rand <= $drop) {
                $xp_abbr = get_option( "options_go_loot_xp_abbreviation" );
                $gold_abbr =  go_get_loot_short_name('gold');
                $health_abbr = get_option( "options_go_loot_health_abbreviation" );
                $xp = $value['xp'];
                if ($xp > 0){
                    $xp_message = $xp_abbr . ": " .  $xp . "<br>";
                }else {$xp_message = '';}
                $gold = $value['gold'];
                if ($gold > 0){
                    $gold_message = $gold_abbr . ": " .  $gold . "<br>";
                }else {$gold_message = '';}
                $health = $value['health'];
                if ($health > 0){
                    $health_message = $health_abbr . ": " .  $health . "<br>";
                }else {$health_message = '';}

                $title = $value['title'];
                $message = $value['message'];
                $title  = do_shortcode( $title );
                $result = 'Bonus Loot Winner: '. $title;
                $message  = do_shortcode( $message );

                //$title = get_option('options_go_loot_bonus_loot_name');;
                $message = $message . "<br><br>" . $xp_message .  $gold_message . $health_message;
                go_noty_message_generic('success', $title, $message);
                //go_noty_loot_success($title,$message );
                go_update_actions($user_id, 'bonus_loot', $post_id, null, null, null, $result, null, null, null, $health_mod, $xp, $gold, $health, null, null, true);
                $winner = true;
                go_print_bonus_result($user_id, $post_id, $result, $xp, $gold, $health);
                break;
            }
            $drop_total = $drop_total - $drop;
        }
        if (!$winner) {//NOT winner
            //add update here for no winner
            go_update_actions($user_id, 'bonus_loot', $post_id, null, null, null, 'Bonus Loot Attempt - Not Winner', null, null, null, null, null, null, null, null, null, null);
            go_noty_message_generic('warning', "", "Better luck next time!");
        }

    }
    /*
    $buffer = ob_get_contents();
    ob_end_clean();

    // constructs the JSON response
    echo json_encode(
        array(
            'json_status' => 'success',
            'html' => $buffer
        )
    );
    */
    die();
}

function go_print_bonus_result($user_id, $post_id, $result, $xp, $gold, $health){

    //check for an undo
    global $wpdb;
    $go_actions_table_name = "{$wpdb->prefix}go_actions";
    $previous_result = $wpdb->get_var($wpdb->prepare("SELECT id
            FROM {$go_actions_table_name} 
            WHERE source_id = %d AND uid = %d AND action_type = %s
            ORDER BY id DESC LIMIT 1", $post_id, $user_id, 'undo_bonus_loot'));


    if(!empty($previous_result)) {
        $task_name = get_option('options_go_tasks_name_singular');
        echo "<span class='go-error-message'>Your bonus loot was removed.  <span class='tooltip' data-tippy-content='This happened because of a reset or undo on this $task_name.'><i class='fa fa-info-circle' aria-hidden='true'></i></span><br>";
        //go_print_rewards('bottom', $user_id, $xp, $gold, $health);
        //go_display_task_badges_and_groups($badges, $groups);
    }

        echo "<span>".$result."</span>";



    go_print_rewards('bottom', $user_id, $xp, $gold, $health);



}

function xp_progress_bar($user_id){

    if (!is_user_member_of_blog()){
        $not_logged_in_class = 'not_logged_in';
        $pts_to_rank_up_str = 'Log in to see stats.';
        $color = 'grey';
        $percentage = 100;
    }else{
        // the user's current amount of experience (points)
        $go_current_xp = go_get_user_loot($user_id, 'xp');

        $rank = go_get_rank($user_id);
        $rank_num = $rank['rank_num'];
        $current_rank = $rank['current_rank'];
        $current_rank_points = $rank['current_rank_points'];
        //$next_rank = $rank['next_rank'];
        $next_rank_points = $rank['next_rank_points'];
        $xp_abbr = get_option( "options_go_loot_xp_abbreviation" );

        if ($next_rank_points != false) {
            $rank_threshold_diff = $next_rank_points - $current_rank_points;
            $pts_to_rank_threshold = $go_current_xp - $current_rank_points;
            $pts_to_rank_up_str = "{$go_current_xp} $xp_abbr &nbsp;&nbsp;•&nbsp;&nbsp; LEVEL {$rank_num} $current_rank &nbsp;&nbsp;•&nbsp;&nbsp; {$pts_to_rank_threshold}/{$rank_threshold_diff}";
            $percentage = $pts_to_rank_threshold / $rank_threshold_diff * 100;
            //$color = barColor( $go_current_health, 0 );
            $color = "#39b54a";
        } else {
            $pts_to_rank_up_str = "$xp_abbr:$go_current_xp L:$rank_num $current_rank";;
            $percentage = 100;
            $color = "gold";
        }
        if ($percentage <= 0) {
            $percentage = 0;
        } else if ($percentage >= 100) {
            $percentage = 100;
        }
        $not_logged_in_class = '';
    }
    $progress_bar = '<div class="go_admin_bar_progress_bar_border '. $not_logged_in_class .' progress-bar-border"><div class="go_admin_bar_progress_bar stats_progress_bar" ' .
        'style="width: ' . $percentage . '%; background-color: ' . $color . ' ;">' .
        '</div>' .
        '<div class="points_needed_to_level_up go_admin_bar_text">' .
        $pts_to_rank_up_str .
        '</div>' .
        '</div>';

    return $progress_bar;
}

function go_health_bar($user_id){
    // the user's current amount of bonus currency,
    // also used for coloring the admin bar
    $text_color = 'white';
    $class = '';
    $bonus='';
    if (!is_user_member_of_blog()){
        //$pts_to_rank_up_str = 'Log in to see stats.';
        $color = 'grey';
        $bkg_color = '';
        $health_string = '';
        $width = 100;
    }else{
        $name = strtoupper(get_option("options_go_loot_health_abbreviation"));
        $go_current_health = go_get_user_loot($user_id, 'health');
        $health_amount = intval($go_current_health);
        $color = '#db2424';//red
        $bkg_color = '';

        //if ($health_amount <= 0) {
           // $health_amount = 0;
        //}
        //else if ($health_percentage > 100) {
        //else{
            //$health_amount= $health_percentage;
           // $health_percentage = 100;
        $max_health = get_option('options_go_loot_health_max_health_mod');

        $health_percent = $go_current_health/100;
        $width = 0;
        //$health_over = ($health_amount -100) / 100;
        if ($health_amount == $max_health){
            $color = '';
            $class = 'max_health';
            $width = 100;
        }else if($go_current_health <= 100){
            $width = $go_current_health;
            if($health_percent < (1/4)){
                $color = '#cf343a';//red
                $bkg_color = 'rgb(207, 52, 58, .2)';
            }else if($health_percent < (1/2)){
                $color = '#ff6700';//red-orange
                $bkg_color = 'rgb(255, 103, 0, .2)';
            }else if($health_percent < (3/4)){
                $color = '#ffe400';//yellow
                $bkg_color = 'rgb(255, 228, 0, .2)';
            }else {
                $color = '#bfdb00';//light green
                $bkg_color = 'rgb(191, 219, 0, .2)';
                $text_color = 'black';
            }
        } else {
            $bonus = 'bonus';
            $health_percentage = ($go_current_health-100)/($max_health-100);
            $width = $health_percentage * 100;
            if($health_percentage < (1/4)){
                //$text_color = 'black';
                $color = '#1bb15a ';//green
                $bkg_color = 'rgb(27, 177, 90, .2)';
            }else if($health_percentage < (1/2)){
                $color = '#00ccff ';// light blue
                $bkg_color = 'rgb(0, 204, 255, .2)';
               // $text_color = 'white';
            }else if($health_percentage < (3/4)){
                $color = '#4a84ef ';// dark blue
                $bkg_color = 'rgb(74, 132, 239, .2)';
                // $text_color = 'white';
            } else {
                $color = '#944aef';//violet
                $bkg_color = 'rgb(148, 74, 239, .2)';
            }
        }


        $health_string = $go_current_health . "%  " . $name . " MODIFIER";
    }

    $health_bar = '<div class="go_admin_health_bar_border progress-bar-border '.$bonus.'"><div class="go_admin_bar_health_bar stats_progress_bar progress_bar '.$class.'" style="width: ' . $width . '%; background-color: ' . $color .' ;">' . '</div>' . '<div class="health_bar_percentage_str go_admin_bar_text" style="color:'.$text_color.'; background-color:'.$bkg_color.';">' . $health_string . '</div>' . '</div>';

    return $health_bar;
}

function go_gold_bar($user_id){

    $go_current_gold = go_get_user_loot($user_id, 'gold');
    //$go_current_gold = 100;
    $user_bar_coins = '<div id="go_admin_bar_gold" class="admin_bar_loot" style="line-height: 1em !important; text-align: right; padding-top: 9px; padding-right: 10px; display: flex;">' . go_display_shorthand_currency('gold', $go_current_gold, false, true, " " , true) . '</div>';
    //$user_bar_coins ='';
    $gold_bar = '<div id="go_user_bar_loot" class="userbar_dropdown"><div class="narrow_content">' . $user_bar_coins . '</div><div class="wide_content">' . $user_bar_coins . '</div>';

    //$gold_bar = '';

    return $gold_bar;
}

?>