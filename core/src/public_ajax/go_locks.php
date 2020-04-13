<?php

/**
 * LOCKS START
 * prevents users (both logged-in and logged-out) from accessing the task content, if they
 * do not meet the requirements.
 * @param $id
 * @param $user_id
 * @param $is_admin
 * @param $task_name
 * @param $custom_fields
 * @param $is_logged_in
 * @param $check_only
 * @param $loot
 * @return bool
 */
function go_task_locks ( $id, $check_only, $user_id = null, $task_name = false, $custom_fields = null){

        ob_start();


    if($user_id === null){
        $user_id = get_current_user_id();
    }
    if($custom_fields === null){
        //$custom_fields = go_post_meta($id);
        $data = go_post_data($id);
        $custom_fields = $data[3];
        //$custom_fields = go_post_meta($id);
    }

    $is_logged_in = is_user_logged_in();

    //this is for maps.
    //This check is done later on tasks and store as well.
    //It is separated out because they treat it differently
    if ($check_only && $is_logged_in) {
        $is_unlocked = go_master_unlocked($user_id, $id); //one query per quest on map
        if ($is_unlocked == 'password' || $is_unlocked == 'master password') {
            //$is_unlocked = true;
            ob_get_clean();
            return $is_unlocked;
        }
        else {
            $go_password_lock = (isset($custom_fields['go_password_lock'][0]) ?  $custom_fields['go_password_lock'][0] : null);
            if ($go_password_lock == true){
                ob_get_clean();
                return true;
            }
        }
    }

    $task_is_locked = false;

    /**
     * This section is for the chain locks
     */
    if($is_logged_in) {
        $location_map_toggle = (isset($custom_fields['go-location_map_toggle'][0]) ? $custom_fields['go-location_map_toggle'][0] : null);
        if ($location_map_toggle == true) {

            $task_is_locked_cl = go_task_chain_lock($id, $user_id, $task_name, $custom_fields, $is_logged_in, $check_only);
            //$chain_message = ob_get_clean();

            if ($task_is_locked_cl == true) {
                //echo $chain_message;
                $task_is_locked = true;

            }
        }

    }
    /**
     * This section is for the scheduled access
     */
    $go_sched_toggle = (isset($custom_fields['go_sched_toggle'][0]) ?  $custom_fields['go_sched_toggle'][0] : null);
    if ($go_sched_toggle == true) {

        $this_lock = go_schedule_access($user_id, $custom_fields, $is_logged_in, $check_only);//returns and not prints result

        if ($this_lock !== false && !$check_only){
            echo $this_lock;
            $task_is_locked = true;
        }else{
            return true;
        }
    }

    //only continues if there is no scheduled access or it is the scheduled time frame.
    /**
     * Loop to check all the locks and keys
     */
    $go_lock_toggle = (isset($custom_fields['go_lock_toggle'][0]) ?  $custom_fields['go_lock_toggle'][0] : null);
    $locks_status = false;

    ob_start();

    if ($go_lock_toggle == true ) {

        //$task_is_locked_locks = false;
        if (!$check_only) {
            $task_caps = ucwords($task_name);
            $lock_message = (isset($custom_fields['go_lock_message'][0]) ?  $custom_fields['go_lock_message'][0] : null);
            if(!empty($lock_message)){
                $custom_message = true;
                echo '<div class="go_locks"><h3>Locked ' . $task_caps . '</h3>' . $lock_message .'</div>';

            }else{
                $custom_message = false;
                echo '<div class="go_locks"><h3>Locked ' . $task_caps . '</h3>' . $lock_message . 'You must unlock one lock to continue. <div class="go_locks_wrapper" style="display: flex; align-items: stretch; justify-items: center; justify-content: center;">';

            }
        }
        //$task_is_locked = false;
        $num_locks = $custom_fields['go_locks'][0];
        $lock_num_display = 1;
        $locks_status = true;

        for ($i = 0; $i < $num_locks; $i++) {
            $this_lock_status = false;
            //if (!$check_only) {

                if ($lock_num_display > 1){
                    if(!$custom_message) {
                        echo '<div style="width: 75px;display: flex;
    align-items: center;
    justify-content: center;">-or-</div>';
                    }
                }
            if(!$custom_message) {
                echo '<div class="go_lock" style="text-align: center;"><p>Lock ' . $lock_num_display . '<div class="go_lock_info">';
            }
            //}
            $this_lock = false;
            $lock_num = "go_locks_" . $i . "_keys";
            $num_keys = $custom_fields[$lock_num][0];
            $this_lock_on = false;
            $first = true;
            for ($k = 0; $k < $num_keys; $k++) {
                $key_type = "go_locks_" . $i . "_keys_" . $k . "_key";
                $key_type = $custom_fields[$key_type][0];

                if ($key_type != null) {
                    $this_lock = $key_type($id, $user_id, $task_name, $custom_fields, $i, $k, $is_logged_in, $check_only);
                    if ($this_lock !== false){
                        if(!$first){
                            if(!$custom_message) {
                                echo '<div style="padding:25px;">–and–</div>';
                            }
                        }
                        $first = false;
                        if(!$custom_message) {
                            echo $this_lock;
                        }
                        $this_lock_status = true;
                    }
                }

            }
            if(!$this_lock_status){
                $locks_status = false;
                break;
            }
            if(!$custom_message) {
                echo '</div></div>';
            }
            if ($this_lock_status) {
                $lock_num_display++;
            }


        }
        if(!$custom_message) {
            echo '</div></div>';
        }

    }
    if($locks_status){
        $task_is_locked = true;
        if (!$check_only) {
            //$message =  ob_get_clean();
            $message = ob_get_contents();
        }
    }
    ob_end_clean();
    if($locks_status){
        if (!$check_only) {
            echo $message;
        }
    }






    /**
     * This section is for the password lock


    $go_password_lock = (isset($custom_fields['go_password_lock'][0]) ?  $custom_fields['go_password_lock'][0] : null);
    //if ($go_lock_toggle == true || $go_password_lock == true){
    if ($go_password_lock == true){
        $task_is_locked = true;
    }
     */
    //if ($task_is_locked_l){
    //    $task_is_locked = true;
    //}

    //Locks End

    if (!$check_only && $task_is_locked) {
        $message = ob_get_contents();
    }
    ob_end_clean();
    if (!$check_only && $task_is_locked) {
        echo $message;
    }


    return $task_is_locked;
}

/**
 * Lock Until Date
 * @param $id
 * @param $user_id
 * @param $task_name
 * @param $custom_fields
 * @param $i
 * @param $k
 * @param $is_logged_in
 * @param $check_only
 * @return bool
 */
function go_until_lock($id, $user_id, $task_name, $custom_fields, $i, $k, $is_logged_in, $check_only ){
    $this_lock = false;
    $is_admin = go_user_is_admin();
    $option = "go_locks_" . $i . "_keys_" . $k . "_options_0_until";
    $start_filter = $custom_fields[$option][0];

    $unix_now = current_time('timestamp');
    //$offset = 3600 * get_option('gmt_offset');
    //$unix_now = $unix_now - $offset;
    if (!empty($start_filter)) {

        $start_unix = strtotime($start_filter);

        // stops execution if the start date and time has not passed yet
        if ($unix_now < $start_unix) {
            $time_string = date('g:ia', $start_unix) . ' on ' . date('D, F j, Y', $start_unix);
            if (!$check_only) {
                $this_lock = "<p class='go_error_red'>It is after {$time_string}.</p>";

                $this_lock .= go_timezone_message($user_id);

            }
            else{
                $this_lock = true;
            }
        }
    }

    return $this_lock;
}

/**
 * Lock After Date
 * @param $id
 * @param $user_id
 * @param $task_name
 * @param $custom_fields
 * @param $i
 * @param $k
 * @param $is_logged_in
 * @param $check_only
 * @return bool
 */
function go_after_lock($id, $user_id, $task_name, $custom_fields, $i, $k, $is_logged_in, $check_only ){
    //$is_admin = go_user_is_admin( );
    $this_lock = false;
    $option = "go_locks_" . $i . "_keys_" . $k . "_options_0_after";
    $start_filter = $custom_fields[$option][0];

    // holds the output to be displayed when a non-admin has been stopped by the start filter
    $unix_now = current_time('timestamp');
    //$offset = 3600 * get_option('gmt_offset');
    //$unix_now = $unix_now - $offset;
    if (!empty($start_filter)) {
        $start_unix = strtotime($start_filter);

        // stops execution if the user is a non-admin and the start date and time has not
        // passed yet
        if ($unix_now > $start_unix) {
            $time_string = date('g:ia', $start_unix) . ' on ' . date('D, F j, Y', $start_unix);
            if (!$check_only) {
                $this_lock =  "<p class='go_error_red'>This " . $task_name . " was only available until {$time_string}.</p>";
                $this_lock .= go_timezone_message($user_id);

            }
            else{
                $this_lock = true;
            }

        }
    }

    return $this_lock;
}

/**
 * Badge Lock
 * @param $id
 * @param $user_id
 * @param $task_name
 * @param $custom_fields
 * @param $i
 * @param $k
 * @param $is_logged_in
 * @param $check_only
 * @return bool
 */
function go_badge_lock($id, $user_id, $task_name, $custom_fields, $i, $k, $is_logged_in, $check_only ){

    //$badge_name = get_option( 'options_go_naming_other_badges' );
    $badges_name = get_option('options_go_badges_name_plural');
    $this_lock = false;
    if ($is_logged_in) {
        $option = "go_locks_" . $i . "_keys_" . $k . "_options_0_badge";
        $terms_needed = $custom_fields[$option][0];
        /*
        if (is_serialized($terms_needed)) {
            $terms_needed = unserialize($terms_needed);
        }
        if (!is_array($terms_needed)) {
            $terms_needed = array($terms_needed);
        }else{
            $terms_needed = array();
        }
    */
        if (is_serialized($terms_needed)){
            $terms_needed = unserialize($terms_needed);// badges saved as serialized array
        }
        if (!is_array($terms_needed)){
            $terms_needed = array($terms_needed);
        }else{
            //$terms_needed = array();
        }



        global $wpdb;

        $key = go_prefix_key('go_badge');
        $badges_array = get_user_meta($user_id, $key, false);


        if(!is_array($badges_array)) {
            $badges_array = array();
        }

        // determines if the user has the correct badges
        if (!empty($terms_needed)) {
            // checks to see if the filter array are in the the user's badge array
            $intersection = array_values(array_intersect($badges_array, $terms_needed));
            // stores an array of the badges that were not found in the user's badge array
            //$term_diff = array_diff($terms_needed, $intersection);
            if(count($intersection) === 0){
            //if (!empty($term_diff)) {
                if (!$check_only) {
                    //$this_lock = "<div class='go_error_red'>You are in the possession of one of these " . $badges_name . ":</div>";
                   // $this_lock = "<div><b>" . $badges_name . ":</b></div>";
                    $this_lock = "<div>You must possess one of these " . $badges_name . " to access this ".$task_name .":</div>";

                    $this_lock .= "<div class='go_term_list'>";
                    $first = true;
                    $count = count($terms_needed);
                    $i = 0;
                    foreach ($terms_needed as $term_id) {
                        $i++;
                        if (!empty($term_id)) {


                            $term = get_term($term_id);
                            $data = go_term_data($term_id);
                            if(!empty($data)) {
                                $term_name = $data[0];
                                if (!$first) {
                                    if ($count === $i) {
                                        $this_lock .= " or ";
                                    } else {
                                        //$this_lock .= "<p> –or– </p>";
                                        $this_lock .= ", ";
                                    }
                                }
                                $first = false;
                                $this_lock .= "$term_name";
                            }
                        }

                    }
                    $this_lock .= "</div>";

                    /*

                    $this_lock .= "<div class='go_term_list'>";
                    $first = true;
                    foreach ($terms_needed as $term_id) {



                        if (!empty($term_id)) {
                            if (!$first){
                                $this_lock .= "<p> –or– </p>";
                            }
                            $first = false;
                            $term = get_term($term_id);
                            $term_name = $term->name;
                            $this_lock .= "<p>$term_name</p>";
                        }
                    }
                    $this_lock .= "</div>";*/
                }
                else{
                    $this_lock = true;
                }

            }
        }
    }
    return $this_lock;
}

/**
 * Groups Lock
 * @param $id
 * @param $user_id
 * @param $task_name
 * @param $custom_fields
 * @param $i
 * @param $k
 * @param $is_logged_in
 * @param $check_only
 * @return bool
 */
function go_user_lock($id, $user_id, $task_name, $custom_fields, $i, $k, $is_logged_in, $check_only ){
    $this_lock = false;
    if( $is_logged_in ) {
        $option = "go_locks_" . $i . "_keys_" . $k . "_options_0_group";
        $terms_needed = $custom_fields[$option][0];
        $terms_needed = maybe_unserialize($terms_needed);
        if(!is_array($terms_needed)){
            $terms_needed = array($terms_needed);
        }
        $terms_needed = array_values($terms_needed);

        $key = go_prefix_key('go_group');
        $user_terms = get_user_meta($user_id, $key, false);

        if(!is_array($user_terms)) {
            $user_terms = array();
        }

        //set $user_terms to empty array if not set
        if (!is_array($user_terms)) {
            $user_terms = array();
        }

        // determines if the user has the correct group
        if (!empty($terms_needed)) {
            // checks to see if the filter array are in the the user's groups array
            $intersection = array_values(array_intersect($user_terms, $terms_needed));
            // stores an array of the groups that were not found in the user's groups array
            //$term_diff = array_diff($terms_needed, $intersection);
            if(count($intersection) === 0){
            //if (!empty($term_diff)) {
                if (!$check_only) {
                    //$this_lock = "<div class='go_error_red'>You are a member of one of these groups:</div>";

                    //$this_lock = "<div><b>Groups:</b></div>";
                    $this_lock = "<div>You must be in one of these groups to access this ".$task_name .":</div>";

                    $this_lock .= "<div class='go_term_list'>";
                    $first = true;
                    $count = count($terms_needed);
                    $i = 0;
                    foreach ($terms_needed as $term_id) {
                        $i++;
                        if (!empty($term_id)) {


                            $term = get_term($term_id);
                            $term_name = $term->name;
                            if (!$first) {
                                if ($count === $i) {
                                    $this_lock .= " or ";
                                } else {
                                    //$this_lock .= "<p> –or– </p>";
                                    $this_lock .= ", ";
                                }
                            }
                            $first = false;
                            $this_lock .= "$term_name";
                        }

                    }
                    $this_lock .= "</div>";
                }
                else{
                    $this_lock = true;
                }

            }
        }
    }

    return $this_lock;
}

/**
 * Seating Chart/ Period Lock
 * @param $id
 * @param $user_id
 * @param $task_name
 * @param $custom_fields
 * @param $i
 * @param $k
 * @param $is_logged_in
 * @param $check_only
 * @return bool
 */
function go_period_lock($id, $user_id, $task_name, $custom_fields, $i, $k, $is_logged_in, $check_only ){
    $this_lock = false;
    if( $is_logged_in ) {
        $option = "go_locks_" . $i . "_keys_" . $k . "_options_0_lock_sections";
        $terms_needed = $custom_fields[$option][0];
        $terms_needed = unserialize($terms_needed);

        //Get the users section(s)
        $key = go_prefix_key('go_section');
        $user_terms = get_user_meta($user_id, $key, false);

        if(!is_array($user_terms)) {
            $user_terms = array();
        }


        // determines if the user has the correct term
        if (!empty($terms_needed)) {
            // checks to see if the filter array are in the the user's section array
            $intersection = array_values(array_intersect($user_terms, $terms_needed));
            // stores an array of the terms that were not found in the user's term array
           // $term_diff = array_diff($terms_needed, $intersection);
            if(count($intersection) === 0){
            //if (!empty($term_diff)) {
                if (!$check_only) {
                    //$this_lock = "<div class='go_error_red'>You must be in one of the following classes:</div>";
                    //$this_lock = "<div><b>Classes:</b></div>";
                    $this_lock = "<div>You must be in one of these class sections to access this ".$task_name .":</div>";

                    $this_lock .= "<div class='go_term_list'>";
                    $first = true;
                    $count = count($terms_needed);
                    $i = 0;
                    foreach ($terms_needed as $term_id) {
                        $i++;
                        if (!empty($term_id)) {


                            $term = get_term($term_id);
                            $term_name = $term->name;
                            if (!$first) {
                                if ($count === $i) {
                                    $this_lock .= " or ";
                                } else {
                                    //$this_lock .= "<p> –or– </p>";
                                    $this_lock .= ", ";
                                }
                            }
                            $first = false;
                            $this_lock .= "$term_name";
                        }

                    }
                    $this_lock .= "</div>";

                    /*
                    $this_lock .= "<div class='go_term_list'>";
                    $first = true;
                    foreach ($terms_needed as $term_id) {
                        //$term_object = get_term($term_id);
                        //$term_name = $term_object->name;
                        if (!empty($term_id)) {
                            if (!$first){
                                $this_lock .= "<p> –or– </p>";
                            }
                            $first = false;
                            $term = get_term($term_id);
                            $term_name = $term->name;
                            $this_lock .= "<p>$term_name</p>";
                        }
                    }
                    $this_lock .= "</div>";*/
                }
                else{
                    $this_lock = true;
                }
            }
        }
    }

    return $this_lock;
}

/**
 * Minimum XP Lock
 * @param $id
 * @param $user_id
 * @param $task_name
 * @param $custom_fields
 * @param $i
 * @param $k
 * @param $is_logged_in
 * @param $check_only
 * @return bool
 */
function go_xp_lock($id, $user_id, $task_name, $custom_fields, $i, $k, $is_logged_in, $check_only ){
    $this_lock = false;
    if( $is_logged_in ) {
        $option = "go_locks_" . $i . "_keys_" . $k . "_options_0_xp";
        $xp_needed = $custom_fields[$option][0];
        //get user health from totals table
        //$user_xp = go_get_user_loot ($user_id, 'xp');
        $loot = go_get_loot($user_id);
        $user_xp = $loot['xp'];
        $xp_name = get_option('options_go_loot_xp_name');
        if ($user_xp < $xp_needed){
            if (!$check_only) {
                $this_lock = "<div class='go_error_red'>You must have {$xp_needed} {$xp_name} to access this {$task_name}.</div>";
            }
            else{
                $this_lock = true;
            }

        }
    }
    return $this_lock;
}

/**
 * Minimum XP LEVELS Lock
 * @param $id
 * @param $user_id
 * @param $task_name
 * @param $custom_fields
 * @param $i
 * @param $k
 * @param $is_logged_in
 * @param $check_only
 * @return bool
 */
function go_xp_levels_lock($id, $user_id, $task_name, $custom_fields, $i, $k, $is_logged_in, $check_only ){
    $this_lock = false;
    if( $is_logged_in ) {
        $option = "go_locks_" . $i . "_keys_" . $k . "_options_0_xp_level";
        $xp_needed = $custom_fields[$option][0];

        //$user_xp = go_get_user_loot ($user_id, 'xp');
        $loot = go_get_loot($user_id);
        $user_xp = $loot['xp'];
        $xp_name = get_option('options_go_loot_xp_name');
        if ($user_xp < $xp_needed){
            if (!$check_only) {
                $this_lock = "<div class='go_error_red'>You must have {$xp_needed} {$xp_name} to access this {$task_name}.</div>";
            }
            else{
                $this_lock = true;
            }

        }
    }
    return $this_lock;
}

/**
 * Minimum Gold Lock
 * @param $id
 * @param $user_id
 * @param $task_name
 * @param $custom_fields
 * @param $i
 * @param $k
 * @param $is_logged_in
 * @param $check_only
 * @return bool
 */
function go_gold_lock($id, $user_id, $task_name, $custom_fields, $i, $k, $is_logged_in, $check_only ){
    $this_lock = false;
    if( $is_logged_in ) {
        $option = "go_locks_" . $i . "_keys_" . $k . "_options_0_gold";
        $gold_needed = $custom_fields[$option][0];
        //get user health from totals table
        $user_gold = go_get_user_loot ($user_id, 'gold');
        $loot = go_get_loot($user_id);
        $user_gold = $loot['gold'];
        $gold_name = go_get_gold_name();
        if ($user_gold < $gold_needed){
            if (!$check_only) {
                $this_lock = "<div class='go_error_red'>You must have {$gold_needed} {$gold_name} to access this {$task_name}.</div>";
            }
            else{
                $this_lock = true;
            }

        }
    }
    return $this_lock;
}

/**
 * Minimum Health Lock
 * @param $id
 * @param $user_id
 * @param $task_name
 * @param $custom_fields
 * @param $i
 * @param $k
 * @param $is_logged_in
 * @param $check_only
 * @return bool
 */
function go_health_lock($id, $user_id, $task_name, $custom_fields, $i, $k, $is_logged_in, $check_only ){
    $this_lock = false;
    if( $is_logged_in ) {
        $option = "go_locks_" . $i . "_keys_" . $k . "_options_0_health";
        $health_needed = $custom_fields[$option][0];
        //get user health from totals table
        //$user_health = go_get_user_loot ($user_id, 'health');
        $loot = go_get_loot($user_id);
        $user_health = $loot['health'];
        $health_name = get_option('options_go_loot_health_name');
        if ($user_health < $health_needed){
            if (!$check_only) {
                $this_lock = "<div class='go_error_red'>You must have {$health_needed} {$health_name} to access this {$task_name}.</div>";
            }
            else{
                $this_lock = true;
            }

        }
    }
    return $this_lock;
}

/**
 * Scheduled Access
 * @param $user_id
 * @param $custom_fields
 * @param $is_logged_in
 * @param $check_only
 * @return bool
 */
function go_schedule_access($user_id, $custom_fields, $is_logged_in, $check_only){
    //if( $is_logged_in || !$is_logged_in) {
    $this_lock = true;

    //Get the users section(s)
    $key = go_prefix_key('go_section');
    $user_terms = get_user_meta($user_id, $key, false);

    if(!is_array($user_terms)) {
        $user_terms = array();
    }

    $sched_num = (isset($custom_fields['go_sched_opt'][0]) ?  $custom_fields['go_sched_opt'][0] : null); //the number of schedule locks

    //loop through the locks to see if any one of them allows the user to proceed
    for ($i = 0; $i < $sched_num; $i++) {
        $dow_section = "go_sched_opt_" . $i . "_sched_sections";
        $dow_section = (isset($custom_fields[$dow_section][0]) ?  unserialize($custom_fields[$dow_section][0]) : null);

        if (!$dow_section){
            $dow_section = array();
        }

        $dow_days = "go_sched_opt_" . $i . "_dow";
        $dow_days = (isset($custom_fields[$dow_days][0]) ?  unserialize($custom_fields[$dow_days][0]) : null);
        if (!$dow_days){$dow_days = array();}

        $current_time = current_time('Y-m-d');
        $dow_time = "go_sched_opt_" . $i . "_time";
        $dow_time = (isset($custom_fields[$dow_time][0]) ?  $custom_fields[$dow_time][0] : null);
        $dow_time = $current_time ." ". $dow_time;
        $dow_time = strtotime($dow_time);
        //$offset = 3600 * get_option('gmt_offset');
        //$dow_time = $dow_time + $offset;

        $dow_minutes = "go_sched_opt_" . $i . "_min";
        $dow_minutes = (isset($custom_fields[$dow_minutes][0]) ?  $custom_fields[$dow_minutes][0] : null);
        $seconds_available = 60 * $dow_minutes;


        $current_time = current_time('timestamp');
        //$offset = 3600 * get_option('gmt_offset');
        //$current_time = $current_time - $offset;

        //If the user is in at least one section, continue . . .
        if ((array_intersect($user_terms, $dow_section) != null) || (empty ($dow_section))) {
            //If today is one of the days it unlocks

            //$current_time = date( 'l', $current_time );
            if (in_array(date( 'l', $current_time ), $dow_days)  || (empty($dow_days))) {

                //if the current time is between the start time and the start time and the minutes unlocked
                if (($current_time >= $dow_time) && ($current_time < ( $dow_time + $seconds_available ))) {
                    //it is unlocked, so exit loop and continue
                    $this_lock = false;

                    break;
                }
            }
        }
    }

    if ($this_lock == true) {

        if (!$check_only) {
            $lock_message = (isset($custom_fields['go_sched_access_message'][0]) ?  $custom_fields['go_sched_access_message'][0] : null);
            if (!empty ($lock_message)){
                $lock_message = $lock_message . '<br>';
            }

            $this_lock = "<div class='go_sched_access_message'><h3>Not Currently Available</h3>$lock_message";

            $this_lock .= '<div class="scheduled_accordion"><h3>See Access Schedule</h3><div class="content">';



            //echo current_time( 'timestamp' );

            $first = true;
            for ($i = 0; $i < $sched_num; $i++) {
                $dow_sections = "go_sched_opt_" . $i . "_sched_sections";
                //$dow_section = unserialize($custom_fields[$dow_section][0]);
                $dow_sections = (isset($custom_fields[$dow_sections][0]) ?  unserialize($custom_fields[$dow_sections][0]) : null);

                    $dow_section = array();

                if (!empty($dow_sections)){
                    foreach ($dow_sections as $dow_term) {
                        if (!empty($dow_term)) {
                            $term = get_term($dow_term);
                            if(!empty($term)) {
                                $term_name = $term->name;
                                $dow_section[] = $term_name;
                            }
                        }
                    }
                }


                $dow_days = "go_sched_opt_" . $i . "_dow";
                //$dow_days = unserialize($custom_fields[$dow_days][0]);
                $dow_days = (isset($custom_fields[$dow_days][0]) ?  unserialize($custom_fields[$dow_days][0]) : null);
                if (!$dow_days) {
                    $dow_days = array();
                }
                $dow_time = "go_sched_opt_" . $i . "_time";
                //$dow_time = $custom_fields[$dow_time][0];
                $dow_time = (isset($custom_fields[$dow_time][0]) ?  $custom_fields[$dow_time][0] : null);
                if (!$dow_time) {
                    $dow_time = '';
                }
                $dow_minutes = "go_sched_opt_" . $i . "_min";
                //$dow_minutes = $custom_fields[$dow_minutes][0];
                $dow_minutes = (isset($custom_fields[$dow_minutes][0]) ?  $custom_fields[$dow_minutes][0] : null);
                if (!$dow_minutes) {
                    $dow_minutes = array();
                }
                $dow_time = strtotime($dow_time);
                if (!$first) {
                    $this_lock .= "–or–<br>";
                }

                if (!empty ($dow_section)) {
                    $this_lock .= (implode(" & ", $dow_section));
                    //echo ' on ';
                } else {
                    $this_lock .= 'All Classes';
                }
                if (!empty($dow_days)){
                    $this_lock .= " on ";
                    $this_lock .= (implode(" & ", $dow_days));
                }
                $this_lock .= " @ ";
                $this_lock .= date('g:ia', $dow_time);
                $this_lock .= " for " . $dow_minutes . " minutes.";
                $this_lock .= "<br>";
                $first = false;

            }

            $this_lock .= go_timezone_message($user_id);

            $this_lock .= '</div></div></div><script>
  jQuery( function() {
    jQuery( ".scheduled_accordion" ).accordion({
    active: 0,
    collapsible: true            
});
  } );
  </script>';
        }

    }
    return $this_lock;

}


//gets the last non optional task on a task chain
//starts at the $id of the task if provided
//returns the post_id of the previous task, or false if no previous non optional task on this chain
function go_prev_task($id = null, $chain_id, $is_nested = false){
    $prev_task = false;
    //FIND IF THE PREVIOUS NON OPTIONAL TASK IS ON THE PREVIOUS CHAIN
    //if this task is on a chain
    //get the ids on this chain
    $go_task_ids = go_get_chain_posts ($chain_id, 'task_chains', false);

    //Get this order of this task on the chain it is on
    if ($id === null){//if no post_id provided, then just get the last task on the chain
        $this_task_order = count($go_task_ids);
    }else{//else get the order
        $this_task_order = array_search($id, $go_task_ids);
    }

    //this is not the first item on a chain
    if ($this_task_order > 0) {
        //the order in the list of task of the previous task
        $prev_key = (int)$this_task_order - 1;

        //loop backwards through the list of tasks in this chain until it finds one that isn't optional
        $is_last_optional = false;
        while ($prev_key >= 0){
            $prev_task = $go_task_ids[$prev_key];
            $prev_task_custom_meta = go_post_meta($prev_task);
            if(!$is_nested) {
                $is_last_optional = (isset($prev_task_custom_meta['go-location_map_options_optional'][0]) ? $prev_task_custom_meta['go-location_map_options_optional'][0] : false);
                if (!$is_last_optional) {//if a not optional task is found, break the loop and return the id
                    break;
                    return $prev_task;
                }
            }else{
                $is_last_nested = (isset($prev_task_custom_meta['go-location_map_options_nested'][0]) ? $prev_task_custom_meta['go-location_map_options_nested'][0] : false);
                if (!$is_last_nested) {//if a not optional task is found, break the loop and return the id
                    break;
                    return $prev_task;
                }
            }
            $prev_key--;
        }
        //if all previous tasks on this chain are optional return false
        if ($is_last_optional){
            $prev_task = false;
        }
    }
    //else this is the first item on a chain, set variables that it is first and there is no previous task
    else{
        $prev_task = false;
    }
    return $prev_task;
}

function go_task_chain_lock_message($post_id, $task_name){

        $task_title = go_the_title($post_id);
        $task_link = go_post_permalink($post_id);

        return "<div class='go_sched_access_message'><h3 class='go_error_red'>Locked</h3>The $task_name, <a href='$task_link'>$task_title</a> must be done first.</div>";

}

/**
 * Task Chain Lock
 * @param $id
 * @param $user_id
 * @param $task_name
 * @param $custom_fields
 * @param $is_logged_in
 * @param $check_only
 * @return bool
 */
function go_task_chain_lock($id, $user_id, $task_name, $custom_fields, $is_logged_in, $check_only)
{
    $chain_id = $custom_fields['go-location_map_loc'][0];
    //if not empty chain id
    //get variables
    //is_optional
    //previous_task
    //is pod
    //is first in chain
    //locked by prev

    //if this task is not on a chain, it couldn't be locked by a chain
    if (empty($chain_id)) {
        return false;//it is unlocked
    }

    //SET SOME VARIABLES ABOUT THE CHAIN THIS TASK IS ON, (is it a pod, is it locked by previous chain)
    $term_data = go_term_data($chain_id);
    $term_custom = $term_data[1];
    $is_pod = (isset($term_custom['pod_toggle'][0]) ? $term_custom['pod_toggle'][0] : null);
    $locked_by_prev = (isset($term_custom['locked_by_previous'][0]) ? $term_custom['locked_by_previous'][0] : null);
    $first_in_chain = false;

    //GET THE TASK ID OF THE FIRST PREVIOUS NON_OPTIONAL TASK ON THE CURRENT CHAIN
    $prev_task = go_prev_task($id, $chain_id);
    if ($prev_task === false) {//if there is no previous task
        $first_in_chain = true;
    }

    //Check #0
    //is the chain or map locked
    $map_data = go_term_data($chain_id);
    $map_custom_fields = $map_data[1];
    $is_map_locked = go_task_locks ( $chain_id, $check_only, $user_id , 'Map', $map_custom_fields);
    if ($is_map_locked) {
        return true;//it is locked
    }

    $parent_map_term_id = go_get_parent_term_id($chain_id, 'task_chains');
    $map_data = go_term_data($parent_map_term_id);
    $map_custom_fields = $map_data[1];
    $is_map_locked = go_task_locks ( $parent_map_term_id, $check_only, $user_id , 'Map', $map_custom_fields);
    if ($is_map_locked) {
        return true;//it is locked
    }


    //nested check
    $nested = (isset($custom_fields['go-location_map_options_nested'][0]) ? $custom_fields['go-location_map_options_nested'][0] : false);
    if($nested){
        //GET THE TASK ID OF THE FIRST PREVIOUS NON_NESTED TASK ON THE CURRENT CHAIN
        $any_prev_task = go_prev_task($id, $chain_id, true);
        if ($any_prev_task) {//if there is a previous task
            $is_done = go_is_done($any_prev_task, $user_id);//is it done
            if ($is_done) {
                return false;//it is unlocked
            } else {
                if (!$check_only) {
                    $this_lock = go_task_chain_lock_message($any_prev_task, $task_name);
                    echo $this_lock;
                }
                return true;//it is locked
            }
        }
    }


    //CHECK #1
    // IF NOT FIRST ON CHAIN and IT IS NOT A POD then CHECK THE PREVIOUS TASK
    // AND RETURN TRUE OR FALSE
    if (!$first_in_chain && !$is_pod) {
        $is_done = go_is_done($prev_task, $user_id);
        if ($is_done) {
            return false;//it is unlocked
        } else {
            if (!$check_only) {
                $this_lock = go_task_chain_lock_message($prev_task, $task_name);
                echo $this_lock;
            }
            return true;//it is locked
        }
    }

    //CHECK #2
    //IF THIS IS A POD OR THE FIRST IN THE CHAIN
    //AND THIS CHAIN is not LOCKED BY THE PREVIOUS
    //THEN SET THIS TASK TO UNLOCKED(available)
    //else if (($is_pod || $first_in_chain) && !$locked_by_prev) {
    else if ( $first_in_chain && !$locked_by_prev && !$is_pod) {
        return false;//it is unlocked
    }



    //CHECK #3
    //THESE CHECKS ARE FOR PODS/FIRST IN CHAIN THAT ARE LOCKED BY PREVIOUS (that should be everything else)
    //IF THIS IS THE FIRST ON THE CHAIN or A POD
    //CHECK IF THIS CHAIN IS FIRST ON THE MAP

    else {

        //CHECK #4.1
        //IF THIS IS A POD
        //CHECK IF THE MASTER PASSWORD WAS USED ON ANY TASK ON THIS CHAIN
        //THEN SET THIS (AND ALL TASKS) TO UNLOCKED IF TRUE
        if ($is_pod) {

            $go_task_ids = go_get_chain_posts($chain_id, 'task_chains', false);
            $done_num = get_term_meta($chain_id, "pod_done_num", true);
            $max_num = get_term_meta($chain_id, "pod_max_num", true);

            //$master_unlock = false;

            $attempted = 0;
            foreach ($go_task_ids as $task_id) {

                //if is pod check if the max attempted is reached
                if($max_num) {
                    $go_task_data = go_post_data($task_id); //0--name, 1--status, 2--permalink, 3--metadata
                    $custom_fields = $go_task_data[3];
                    $nested = (isset($custom_fields['go-location_map_options_nested'][0]) ? $custom_fields['go-location_map_options_nested'][0] : false);
                    $optional = (isset($custom_fields['go-location_map_options_optional'][0]) ? $custom_fields['go-location_map_options_optional'][0] : false);
                    if(!$nested && !$optional) {
                        $status = go_get_status($task_id, $user_id);
                        if ($status >= 1) {
                            if ($task_id == $id) {
                                break;
                            }
                            $attempted++;
                        }
                    }
                    if ($attempted >= $done_num) {
                        if (!$check_only) {
                            $task_name_plural = get_option('options_go_tasks_name_plural');
                            echo "<div class='go_sched_access_message'><h3 class='go_error_red'>Locked</h3>You have reached the maximum number of " . $task_name_plural . " to attempt in this pod.</div>";

                            // echo "You have reached the maximum number of quests to attempt in this pod.";
                        }
                        return true;//it is locked
                    }
                }
            }

            //are any in the pod unlocked by masterpassword
            $is_unlocked = go_master_unlocked($user_id, $task_id);
            if ($is_unlocked == 'password' || $is_unlocked == 'master password') {
                return false;//it is unlocked
            }

            if ( !$locked_by_prev) {
                return false;//it is unlocked
            }

            /*
            $marked_hidden = (isset($custom_fields['go-location_map_options_hidden'][0]) ?  $custom_fields['go-location_map_options_hidden'][0] : false);
            if($marked_hidden) {
                $chain_order = go_get_chain_posts($chain_id, false);
                if (empty($chain_order) || !is_array($chain_order)) {
                    //do nothing
                } else {
                    $this_task_order = array_search($id, $chain_order);
                    if ($this_task_order == 0) {
                        //if the first task in pod is hidden, it is locked
                        return false;
                    } else {
                        $prev_found = false;
                        $i = (int)$this_task_order;
                        while (!$prev_found) {
                            $i--;
                            $prev_task = $chain_order[$i];
                            $is_hidden = get_post_meta($prev_task, 'go-location_map_options_hidden');
                            $is_hidden = (isset($is_hidden[0]) ?  $is_hidden[0] : false);
                            if (!$is_hidden) {
                                $is_done = go_is_done($prev_task, $user_id);
                                if($is_done){
                                    return false;
                                }else{
                                    if (!$check_only) {
                                        $this_lock = go_task_chain_lock_message($prev_task, $task_name);
                                        echo $this_lock;
                                    }
                                    return true;
                                }
                            }
                        }
                    }
                }
            }*/

        }

        //#4.2
        // CONTINUE FOR PODS THAT DO NOT HAVE A MASTER UNLOCK ON A TASK
        //AND TASKS THAT ARE FIRST ON THEIR CHAIN

        //HERE IT STILL COULD BE A POD OR REGULAR CHAIN
        //CHECK THE LAST TASK ON PREVIOUS CHAIN



        $first_on_map = false;
        //if (($is_pod || $first_in_chain) && $locked_by_prev) {

        //GET THE CHAINS ON THE MAP (TERM_IDS)
        $parent_map_term_id = go_get_parent_term_id($chain_id, 'task_chains');
        $sibling_chains = go_get_child_term_ids($parent_map_term_id, 'task_chains');
        //GET WHERE THIS CHAIN IS THE MAP BY ARRAY POSITION
        $this_chain_order = array_search($chain_id, $sibling_chains);

        //if this is first chain on map

        if ($this_chain_order == 0) {
            $first_on_map = true;
        }

        //CHECK #4.3
        //Get the previous chain id
        //either from this map
        //or the last chain on the previous map
        if ($first_on_map === false) {
            //this is not the first chain
            //then get the previous chain on this map
            $prev_key = (int)$this_chain_order - 1;
            $prev_chain = $sibling_chains[$prev_key];
        }
        else {//this was the first chain on the map. Get the id of the previous chain

                    //$prev_chain = null;
            //get the ids of the terms on this map
            $top_chains = go_get_parent_term_ids();
            $this_map_order = array_search($parent_map_term_id, $top_chains);

            //if this is the first map, then return unlocked
            if ($this_map_order == 0) {
                return false;
            }

            //if this is not the first map,
            //get previous map
            $prev_map_key = (int)$this_map_order - 1;
            $prev_map = $top_chains[$prev_map_key];


            //get all chains on previous map
            $children_chains = go_get_child_term_ids($prev_map, 'task_chains');
            //if no children chains on previous map, then return unlocked
            if ($children_chains == false) {
                return false;
            }
            //get last chain of previous map
            $rev_children_chains = array_reverse($children_chains);
            $prev_chain = $rev_children_chains[0];
        }

        //CHECK IF PREVIOUS CHAIN IS DONE
        $is_chain_done = is_chain_done($prev_chain, $user_id, null, null);
        if($is_chain_done){
            return false;
        }else{
            if (!$check_only) {
                $task_name = strtolower( get_option( 'options_go_tasks_name_plural' ) );


                $map_url = get_option('options_go_locations_map_map_link');
                $map_url = (string) $map_url;
                $go_map_link = get_site_url(null, $map_url);
                echo "<div class='go_sched_access_message'><h3 class='go_error_red'>Locked</h3>The previous group of " . $task_name . " must be done first. Please check the <a href='" . $go_map_link . "'>map</a>.</div>";

            }
            return true;
        }

        /*
        //CHECK #4.4
        //IF THE PREVIOUS CHAIN IS A POD
        //check if previous chain is a pod

        $term_data = go_term_data($prev_chain);
        $term_custom = $term_data[1];
        $is_pod = (isset($term_custom['pod_toggle'][0]) ? $term_custom['pod_toggle'][0] : null);



        //CHECK #4.5
        //IF IT IS A POD
        //CHECK IF IT IS DONE
        if ($is_pod){
            //count # done and compare to #requried
            $go_task_ids = go_get_chain_posts($prev_chain, false);
            //if all tasks in pod must be complete toggle is on
            $pod_all = (isset($term_custom['pod_all'][0]) ? $term_custom['pod_all'][0] : null);
            if ($pod_all) {
                $pod_min = count($go_task_ids);
            }
            else{
//HERE
                $pod_min = get_term_meta($prev_chain, 'pod_done_num', true);
            }
            $pod_count = 0;
            foreach ($go_task_ids as $task_id) {
                $is_task_done = go_is_done($task_id, $user_id);
                if ($is_task_done){
                    $pod_count++;
                }

            }
            if ($pod_count >= $pod_min){
                //the pod is complete so return that the current task is not locked
                return false;

            }
            else{
                //the found previous POD is NOT done, so print message
                if (!$check_only) {
                    $task_name = strtolower( get_option( 'options_go_tasks_name_singular' ) );
                    $uc_task_name = ucwords($task_name);

                    $map_url = get_option('options_go_locations_map_map_link');
                    $map_url = (string) $map_url;
                    $go_map_link = get_permalink( get_page_by_path($map_url) );

                    echo "<div class='go_sched_access_message'><h3 class='go_error_red'>Locked</h3>The previous group of " . $uc_task_name . " must be done first. Please check the <a href='" . $go_map_link . "'>map</a>.</div>";
                }

                return true;

            }
        }
        //CHECK #4.4
        //ELSE THE PREVIOUS CHAIN IS NOT A POD
        //CHECK IF
        //last task on previous chain is done
        else {
            $prev_task_id = go_prev_task(null, $prev_chain);
            if ($prev_task_id === false) {
                //if previous chain has no last task
                return false;
            }
            $is_done = go_is_done($prev_task_id, $user_id);
            if ($is_done) {
                return false;
            } else {
                if (!$check_only) {
                    go_task_chain_lock_message($prev_task_id, $task_name);
                }
                return true;
            }
        }
        return false;
        */
    }

    return false;

}

/**
 * Timezone message
 */
function go_timezone_message($user_id) {
    $is_admin = go_user_is_admin( );
    $timezone = get_option('timezone_string');
    $current_time = current_time('timestamp');
    //$offset = 3600 * get_option('gmt_offset');
    //$current_time = $current_time - $offset;
    $current_time = date( 'g:ia l', $current_time );
    $message = '<div> <br><p>This lock is set based on the timezone ' . $timezone . ' where it is currently ' . $current_time . '.</p>';
    if ($is_admin){
        $message .= '<p>The timezone can be changed in the <a href="' . admin_url('options-general.php') . '">settings.</a></p> ';
    }
    $message .= '</div>';
    return $message;
}

/**
 * @param $user_id
 * @param $post_id
 * @return string
 */
function go_master_unlocked($user_id, $post_id){
    global $wpdb;
    $key = 'go_master_unlocked_' . $post_id;
    $data = wp_cache_get( $key );
    if ($data !== false){
        $is_unlocked = $data;
    }else {

        $go_actions_table_name = "{$wpdb->prefix}go_actions";
        $is_unlocked = (string)$wpdb->get_var($wpdb->prepare("SELECT result 
				FROM {$go_actions_table_name} 
				WHERE uid = %d AND source_id = %d  AND check_type = %s
				ORDER BY id DESC LIMIT 1", $user_id, $post_id, 'unlock'));

        wp_cache_set( $key, $is_unlocked, 'go_single');
    }

    //$is_unlocked = ( $is_unlocked == 'password' ) ? true : false;
    return $is_unlocked;
}

/**
 * @param $task_id
 * @param null $user_id
 * @return bool|null
 */
function go_is_done($task_id, $user_id = null ) {

    if ( empty( $task_id ) ) {
        return null;
    }

    $key = 'go_is_done_' . $task_id;
    $data = wp_cache_get( $key );
    if ($data !== false){
        $is_done = $data;
    }else {

        if (empty($user_id)) {
            $user_id = get_current_user_id();
        } else {
            $user_id = (int)$user_id;
        }

        //get status from cache
        $task_status = go_get_status($task_id, $user_id);

        //get data from transient
        $task_custom_meta = go_post_meta($task_id);
        $task_stage_count = (isset($task_custom_meta['go_stages'][0]) ?  $task_custom_meta['go_stages'][0] : null);
        //$task_stage_count = go_post_meta($task_id, 'go_stages', true);

        if ($task_status >= $task_stage_count) {
            $is_done = true;
        } else {
            $is_done = false;
        }
        wp_cache_set($key, $is_done, 'go_single');

    }
    return $is_done;
}

/**
 * Retrieves the status of a task for a specific user.
 *
 * Task "status" values are stored in the `go`.`status` column. Statuses outside the range of [0,5]
 * are not used for tasks, so this function is for tasks ONLY.
 *
 * @since 3.0.0
 *
 * @global wpdb $wpdb The WordPress database class.
 *
 * @param int $task_id The task ID.
 * @param int $user_id Optional. The user ID.
 * @return int|null The status (0,1,2,3,4,5) of a task. Null if the query finds nothing.
 */
function go_get_status( $task_id, $user_id = null, $task = null ) {
    global $wpdb;

    $key = 'go_get_status_' . $task_id;
    $data = wp_cache_get( $key );
    if ($data !== false){
        $task_status = $data;
    }else {
        if ($task != null){
            $task_status = $task['status'];
        }
        else{
            $go_task_table_name = "{$wpdb->prefix}go_tasks";

            if ( empty( $task_id ) ) {
                return null;
            }

            if ( empty( $user_id ) ) {
                $user_id = get_current_user_id();
            } else {
                $user_id = (int) $user_id;
            }

            $task_status = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT status 
			FROM {$go_task_table_name} 
			WHERE uid = %d AND post_id = %d",
                    $user_id,
                    $task_id
                )
            );

            if ( null !== $task_status && ! is_int( $task_status ) ) {
                $task_status = (int) $task_status;
            }
        }
        wp_cache_set ($key, $task_status, 'go_single');
    }


    return $task_status;
}

/**
 * @param $pass
 * @param $custom_fields
 * @param $status
 * @return string
 */
function go_lock_password_validate($pass, $custom_fields){

    $lock_pass = $custom_fields['go_unlock_password'][0];
    $master_pass = get_option('options_go_masterpass');
    if ($pass == $lock_pass ) {
        //password is correct
        return 'password';
    } else if($pass == $master_pass){
        return 'master password';
    } else
    {
        echo json_encode(array('json_status' => 'bad_password', 'html' => '', 'rewards' => array(), 'location' => '',));
        die();
    }
}

function is_chain_done($chain_id, $user_id, $post_id, $is_progressing = true){
    $is_pod = get_term_meta($chain_id, "pod_toggle", true);
/*
    $args = array(
            'tax_query' => array(array('taxonomy' => 'task_chains', 'field' => 'term_id', 'terms' => $chain_id,)),
            'orderby' => 'meta_value_num',
            'order' => 'ASC', 'posts_per_page' => -1,
            'meta_key' => 'go-location_map_order_item',
            'meta_value' => '',
            'post_type' => 'tasks',
            'post_mime_type' => '',
            'post_parent' => '',
            'author' => '',
            'author_name' => '',
            'post_status' => 'publish',
            'suppress_filters' => true,
            'meta_query' => array(
                array(
                    'key' => 'go-location_map_toggle',
                    'value' => '1',
                    'compare' => '=',
                )
            )
    );

    $go_task_objs = get_posts($args);
*/

    $go_task_objs = go_get_ordered_posts($chain_id);

    //HOW MANY ARE NEEDED
    if ($is_pod ) {// it is a pod, find how many are neeed
        $all_needed = get_term_meta($chain_id, "pod_all", true);

        if ($all_needed) {

            $num_needed = count($go_task_objs);
        }else{
            $num_needed = get_term_meta($chain_id, "pod_done_num", true);
        }
    }else{
        $num_needed = count($go_task_objs);
    }



    //HOW MANY ARE DONE
    $num_done = 0;
    foreach ($go_task_objs as $go_task_obj) {//check if each task is done
        $go_task_id = $go_task_obj->ID;

        //count as done if unpublished (it is in the count and can't be done)
        $is_published = get_post_status( $go_task_id );
        if ($is_published !== 'publish'){//if is not published, then count as done and continue
            $num_done++;
            if ($num_done >= $num_needed) {
                return true;//if enough are done, return it is done
            }
            continue;
        }


        $is_optional = go_post_meta($go_task_id, 'go-location_map_options_optional', true);
        if ($is_optional){//if is optional, then count as done and continue
            if(!$is_pod){
                $num_done++;//count as done if optional on chain
                if ($num_done >= $num_needed) {
                    return true;//if enough are done, return it is done
                }
                continue;
            }
            else{
                continue;
            }
        }



        //count as done if complete
        $stage_count = intval(go_post_meta($go_task_id, 'go_stages', true));//total stages
        $status = intval(go_get_status($go_task_id, $user_id));
        //adjust status if this is being run on a stage complete/undo last
        if($post_id == $go_task_id){//if this is the current quest and complete was checked (that is when a $complete_post_id is passed)
            if ($is_progressing === true) {
                $status++;
            }else if ($is_progressing === false){
                $status--;
            }
        }

        if ($stage_count == $status){
            $num_done++;
            if ($num_done >= $num_needed) {
                return true;//if enough are done, return it is done
            }
        }

    }

    //IS IT DONE
    if ($num_done >= $num_needed) {
        return true;//if enough are done, return it is done
    }
    //}
    /*
    else {//not pod
        //is this the last item on chain that isn't optional



        foreach ($go_task_objs as $go_task_obj){
            $go_task_id = $go_task_obj->ID;

            $is_optional = go_post_meta($go_task_id, 'go-location_map_opt', true);
            if (!$is_optional){
                $stage_count = go_post_meta($go_task_id, 'go_stages', true);//total stages

                $status = go_get_status($go_task_id, $user_id);

                if ($stage_count == $status){
                    return true;
                }else{
                    return false;
                }
            }
        }
    }
    */
    return false;
}


function test_is_chain_done($chain_id, $user_id, $post_id, $is_progressing = true){
    $is_pod = get_term_meta($chain_id, "pod_toggle", true);
    /*
        $args = array(
                'tax_query' => array(array('taxonomy' => 'task_chains', 'field' => 'term_id', 'terms' => $chain_id,)),
                'orderby' => 'meta_value_num',
                'order' => 'ASC', 'posts_per_page' => -1,
                'meta_key' => 'go-location_map_order_item',
                'meta_value' => '',
                'post_type' => 'tasks',
                'post_mime_type' => '',
                'post_parent' => '',
                'author' => '',
                'author_name' => '',
                'post_status' => 'publish',
                'suppress_filters' => true,
                'meta_query' => array(
                    array(
                        'key' => 'go-location_map_toggle',
                        'value' => '1',
                        'compare' => '=',
                    )
                )
        );

        $go_task_objs = get_posts($args);
    */

    $go_task_objs = go_get_ordered_posts($chain_id, 'tasks');

    //HOW MANY ARE NEEDED
    if ($is_pod ) {// it is a pod, find how many are neeed
        $all_needed = get_term_meta($chain_id, "pod_all", true);

        if ($all_needed) {

            $num_needed = count($go_task_objs);
        }else{
            $num_needed = get_term_meta($chain_id, "pod_done_num", true);
        }
    }else{
        $num_needed = count($go_task_objs);
    }



    //HOW MANY ARE DONE
    $num_done = 0;
    $unpublished = 0;
    foreach ($go_task_objs as $go_task_obj) {//check if each task is done
        $go_task_id = $go_task_obj->ID;

        //count as done if unpublished (it is in the count and can't be done)
        $is_published = get_post_status( $go_task_id );
        if ($is_published !== 'publish'){//if is not published, then count as done and continue
            $unpublished++;
            if ($num_done >= $num_needed) {
                //return true;//if enough are done, return it is done
            }
            continue;
        }


        $is_optional = go_post_meta($go_task_id, 'go-location_map_options_optional', true);
        if ($is_optional){//if is optional, then count as done and continue
            if(!$is_pod){
                $num_done++;//count as done if optional on chain
                if ($num_done >= $num_needed) {
                   // return true;//if enough are done, return it is done
                }
            }
            else{
                continue;
            }
        }



        //count as done if complete
        $stage_count = intval(go_post_meta($go_task_id, 'go_stages', true));//total stages
        $status = intval(go_get_status($go_task_id, $user_id));
        //adjust status if this is being run on a stage complete/undo last
        if($post_id == $go_task_id){//if this is the current quest and complete was checked (that is when a $complete_post_id is passed)
            if ($is_progressing === true) {
                $status++;
            }else if ($is_progressing === false){
                $status--;
            }
        }

        if ($stage_count == $status){
            $num_done++;
            if ($num_done >= $num_needed) {
               // return true;//if enough are done, return it is done
            }
        }

    }



    $log = "the count of the obj is " . $num_needed .". and the num done is " . $num_done;
    ?>


    <script>
        console.log("<?php echo $log; ?>")
    </script>
    <?php




    //IS IT DONE
    if ($num_done >= $num_needed) {
       // return true;//if enough are done, return it is done
    }
    //}
    /*
    else {//not pod
        //is this the last item on chain that isn't optional



        foreach ($go_task_objs as $go_task_obj){
            $go_task_id = $go_task_obj->ID;

            $is_optional = go_post_meta($go_task_id, 'go-location_map_opt', true);
            if (!$is_optional){
                $stage_count = go_post_meta($go_task_id, 'go_stages', true);//total stages

                $status = go_get_status($go_task_id, $user_id);

                if ($stage_count == $status){
                    return true;
                }else{
                    return false;
                }
            }
        }
    }
    */
   // return false;
}


?>