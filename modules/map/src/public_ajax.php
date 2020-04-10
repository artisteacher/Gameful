<?php



/**
 * @param $last_map_id
 * @param $reload
 * @param $user_id
 * @param $taxonomy
 */
function go_make_single_map($link_to_tasks = true, $map_id = null, $reload, $taxonomy = 'task_chains')
{
    global $wpdb;

    $is_sortable = '';
    if($link_to_tasks){
        if (go_user_is_admin()) {
            $is_sortable = 'sortable';
        }
    }

    if($taxonomy === 'store_types'){
        $is_store = true;
        $link_to_tasks = false;
        $clipboard_map = false;
    }else{
        $is_store = false;
        if(!$link_to_tasks){
            $clipboard_map = true;
        }else{
            $clipboard_map = false;
        }
    }

    $user_id = (isset($_POST['uid']) ?  $_POST['uid'] : null);
    if(empty($user_id)) {
        $user_id = get_current_user_id();
        $is_admin = go_user_is_admin();
    }else{
        $link_to_tasks = false;
        $is_admin = false;
    }


    global $is_really_admin;
    $is_admin_any_other_blog = go_user_is_admin_on_any_other_blog();
    if (function_exists ( 'wu_is_active_subscriber' ) ) {
        $is_subscriber = wu_is_active_subscriber($user_id);
    }else{
        $is_subscriber = false;
    }
    $admin_view = go_get_admin_view($user_id);


    //Set names for items/quests
    if($is_store){
        $task_name_plural = 'Items';

        $task_name_singular = 'Item';
    }
    else{
        $task_name_plural = get_option('options_go_tasks_name_plural');

        $task_name_singular = get_option('options_go_tasks_name_singular');
    }



    //Get the map to show on page load
    if(!$is_store) {


        if (empty($map_id)) {
            $map_id = (isset($_GET['map_id']) ? $_GET['map_id'] : get_user_option('go_last_map', $user_id));
            //$test = term_exists( intval($map_id), $taxonomy);
            if (term_exists(intval($map_id), $taxonomy) === null) {
                $map_id = false;
            }
        }

        if (!$map_id) {
            $map_id = get_option('options_go_locations_map_default', '');

            if (!term_exists($map_id)) {
                $map_id = false;
            }
        }
        if (!$map_id) {

            //$maps = go_get_terms_ordered($taxonomy, '0');
            $maps = go_get_parent_term_ids($taxonomy);
            if (!empty($maps)) {
                foreach ($maps as $map) {
                    $map_id = $map->term_id;
                    if (!$is_admin) {
                        $is_hidden = get_term_meta($map_id, 'go_hide_map', true);
                        if (!$is_hidden) {
                            break;
                        }
                    } else {
                        break;
                    }
                }
            } else {
                $map_id = null;
            }
        }

    }
    else{
        if (empty($map_id)) {
            $map_id = (isset($_GET['store_id']) ? $_GET['store_id'] : get_user_option('go_last_store', $user_id));
            //$test = term_exists( intval($map_id), $taxonomy);
            if (term_exists(intval($map_id), $taxonomy) === null) {
                $map_id = false;
            }
        }

        if (!$map_id) {
            //$maps = go_get_terms_ordered($taxonomy, '0');
            $maps = go_get_parent_term_ids($taxonomy);
            if (!empty($maps)) {
                foreach ($maps as $map) {
                    $map_id = $map->term_id;
                    if (!$is_admin) {
                        $is_hidden = get_term_meta($map_id, 'go_hide_store_cat', true);
                        if (!$is_hidden) {
                            break;
                        }
                    } else {
                        break;
                    }
                }
            } else {
                $map_id = null;
            }
        }
    }

    //does this term exist
    $map_data = go_term_data($map_id);
    if(empty($map_data)){
        echo "There was an error getting the map data.";
        return;
    }
    //get the term data
    $name = $map_data[0];
    $custom_fields = $map_data[1];

    //hide if locked only exists for tasks
    if(!$is_store) {
        //if hide if locked, then don't need to retrieve locked message
        $hide_if_locked = (isset($custom_fields['hide_if_locked'][0]) ? $custom_fields['hide_if_locked'][0] : false);
        if (in_array($hide_if_locked, array('message', 'show'))) {
            $check_only = false;
        } else {
            $check_only = true;
        }

        ob_start();
        //this uses the admin id if they are viewing a player map to get the locked message
        //$locked_user_id = get_current_user_id();

        //if this is an admin viewing a player map, the lock message should be the player locked message
        $is_locked = go_task_locks($map_id, $check_only, $user_id, 'Map', $custom_fields);
        $locked_html = ob_get_contents();
        ob_end_clean();
    }
    else{
        $check_only = false;
        $locked_html = '';
        $is_locked = false;
    }


    //is this a hidden map
    if($is_store){
        $is_hidden = get_term_meta($map_id, 'go_hide_store_cat', true);
    }
    else{
        $is_hidden = get_term_meta($map_id, 'go_hide_map', true);
    }
    if ($is_hidden && !$is_admin) {
        $map_id = '';//don't just return because need to print an empty wrapper
    }






    //$is_logged_in = ! empty( $user_id ) && $user_id > 0 ? true : false;
    //$taxonomy_name = 'task_chains';

    if(!$is_store) {
        $key = go_prefix_key('go_badge');
        $user_badges = get_user_meta($user_id, $key, false);
        if (empty($user_badges)) { //if there were no badges then create empty array
            $user_badges = array();
        } else {//else unserialize
            if (is_serialized($user_badges)) {
                $user_badges = unserialize($user_badges);
            }
        }
    }

    if($is_store){
        $map_class = 'store_map';
    }else{
        $map_class = 'quest_map';
    }

    if ($reload == false) {
        echo "<div id='mapwrapper' class='$map_class' data-singular='$task_name_singular' data-plural='$task_name_plural'  >";
    }


    echo "<span class='go_map_action_icons ' style='position: absolute; right: 40px; z-index: 100; font-size: 1.3em;'>";

    echo "<div class='my_tooltip'><div class='go_actions_wrapper_flex'>";
    echo "<span class='tools task_tools'>";
    //show loot
    if(!$is_store) {
        echo "<span class='tooltip action_icon' data-tippy-content='Show available and earned loot on the map.' onclick='go_show_map_loot();'>";
        echo "<a> <i class='far fa-chart-area'></i></a>";
        echo "</span>";
    }

    if ($is_admin) {
        echo "<span class='action_icon tooltip_toggle tooltip' onclick='go_disable_tooltips();' data-tippy-content='Toggle the admin actions tooltips on or off. Useful when doing demos or if they annoy you.'>";
        echo "<a><i class='active far fa-comment-alt-dots'></i><i style='display: none;' class='inactive far fa-comment-alt-times'></i></a>";
        echo "</span>";

        if($is_store){
            $map_link = go_get_link_from_option('options_go_store_store_link');
            $map_link = add_query_arg('store_id', $map_id, $map_link);
        }
        else{
            $map_link = go_get_link_from_option('options_go_locations_map_map_link');
            $map_link = add_query_arg('map_id', $map_id, $map_link);
        }


        echo go_copy_var_to_clipboard($map_link, 'Copy direct link to this map to clipboard.', true);

    }

    echo "</span></div></div></span>";

    go_make_map_dropdown($taxonomy, $map_id, $user_id, $is_admin, $clipboard_map);




    echo "<div id='maps' data-mapid='$map_id' data-taxonomy='$taxonomy' style='clear: both;' class='$is_sortable map_{$map_id}'>";







    echo 	"<div id='map_$map_id' class='map'>";


     if ($is_locked) {
         if (!$check_only) {
             $name = "";
             echo $locked_html;
         }

         if (!go_user_is_admin() && in_array($hide_if_locked, array('message', 'hide'))) {
             echo "</div>";
             return;
         }
     }

    //echo "$map_prefix $name</div>";

    //go_map_quest_badge($badge_id, $user_badges, true);
    if(!$is_store) {
        $badge_id = get_term_meta($map_id, "pod_achievement", true);
        if (intval($badge_id) > 0) {
            go_print_single_badge($badge_id, 'badge', $output = true, $user_id, 'go_map_badge');
        }
    }

    echo "</div>";

    echo "<ul id='primaryNav' class='primaryNav'>"; //the main list of columns

    $term_ids = go_get_child_term_ids($map_id, $taxonomy);

    $map_xp = 0;
    $map_gold = 0;
    $map_health = 0;

    $map_my_xp = 0;
    $map_my_gold = 0;
    $map_my_health = 0;

    if(empty($term_ids)){
        echo "<div style='text-align: center; width: 100%; padding: 20px;'>This map does not have any map sections.</div>";
        return;
    }


    foreach ($term_ids as $term_id) { //the task chains --columns
        //ob_start();
        $chain_xp = 0;
        $chain_gold = 0;
        $chain_health = 0;

        $chain_my_xp = 0;
        $chain_my_gold = 0;
        $chain_my_health = 0;


        $term_data = go_term_data($term_id);
        $term_name = $term_data[0];
        $term_custom = $term_data[1];


        if($is_store){
            $is_hidden = (isset($term_custom['go_hide_store_cat'][0]) ? $term_custom['go_hide_store_cat'][0] : null);
        }
        else{
            $is_hidden = (isset($term_custom['go_hide_map'][0]) ? $term_custom['go_hide_map'][0] : null);
        }

        if ($is_hidden) {
            if (!$is_admin) {
                continue;
            }
        }

        //$map_data = go_term_data($map_id);
        //$name = $term_data[0];
        //$custom_fields = $map_data[1];
        $hide_if_locked = (isset($term_custom['hide_if_locked'][0]) ? $term_custom['hide_if_locked'][0] : false);
        if (in_array($hide_if_locked, array('message', 'show'))) {
            $check_only = false;
        } else {
            $check_only = true;
        }
        if(!$is_store) {
            ob_start();
            $is_locked = go_task_locks($term_id, $check_only, $user_id, 'Map', $term_custom);
            $locked_html = ob_get_contents();
            ob_end_clean();
            if ($is_locked) {
                if (in_array($hide_if_locked, array('hide')) && !$is_admin) {
                    continue;
                }
            }
        }else{
            $is_locked = false;
        }
        $is_pod = (isset($term_custom['pod_toggle'][0]) ? $term_custom['pod_toggle'][0] : null);
        $locked_by_prev = (isset($term_custom['locked_by_previous'][0]) ? $term_custom['locked_by_previous'][0] : null);

        //Get array of postIDs from transient--this also creates transients of each posts data if needed

        $go_post_ids = go_get_chain_posts($term_id, $taxonomy,true);


        //echo "<li><p>$term_object->name";
        echo "<li class='go_task_chain quick_container task_chain_container_{$term_id}' data-term_id='$term_id'><div class='go_task_chain_map_box go_show_actions'>";

        if(((($is_subscriber || $is_admin) && $admin_view === 'admin') || ($is_admin_any_other_blog && !$is_really_admin) && $link_to_tasks)){

            echo "<div class='actions_tooltip' style='display: none;'>";


            echo "<div class='my_tooltip'><div class='go_actions_wrapper_flex'>";
            echo "<span class='tools task_tools'>";

            if($is_admin) {
                if($is_store){

                        echo "<span class='go_edit_frontend action_icon actiontip' data-new_store_item='true'  data-chain_id='$term_id' data-chain_name='$term_name' data-frontend_edit='true' data-tippy-content='Add an item to this section.'><a><i class='far fa-plus-circle'></i></a></span>";

                }else{
                    echo "<span class='go_edit_frontend_post action_icon actiontip' data-tippy-content='Add a $task_name_singular to this section.' data-chain_id='$term_id' data-chain_name='$term_name' data-frontend_edit='true'><a><i class='far fa-plus-circle'></i></a></span>";

                }
           }
            if(is_gameful()){
                do_action('gop_add_importer_icon', $term_id, 'term', $user_id, false);
            }
            if($is_admin) {
                echo "<span class='go_edit_frontend action_icon actiontip' data-tippy-content='Edit this section.' data-term_id='$term_id'><a><i class='far fa-edit'></i></a></span>";

                echo "<span class='go_quick_edit_show action_icon actiontip' data-tippy-content='Quick edit.' data-term_id='$term_id'><a><i class='far fa-bolt'></i></a></span>";

                echo "<span class='go_trash_post action_icon actiontip' data-tippy-content='Trash this section and all the $task_name_plural in it.' data-term_id='{$term_id}' data-title='{$term_name}'><a><i class='far fa-trash'></i></a></span>";
                /*
                $url = get_edit_term_link($term_id, 'task_chains', 'tasks');
                echo "<span class='go_edit_term'><a href='{$url}'><i class='fas fa-edit'></i></a></span>";
*/
            }
            echo "</span>";
            if($is_admin){
                ?>
                <span class="quickedit_form_container" style="display: none;">
                    <span class="quickedit_form" data-term_id='<?php echo $term_id; ?>'>
                        <div style="display: block;">

                            <input type="text" class="term_title" name="term_title" size="30" value="<?php echo htmlspecialchars($term_name); ?>">

                            <?php
                            if(!$is_store) {
                                ?>
                                    <br>

                                    <span class="checkbox" style="padding: 10px;">
                                    <label for="pod">  Pod </label>
                                    <input type="checkbox" class="pod_checkbox" name="pod" <?php if ($is_pod) {
                                        echo 'checked';
                                    } ?>>
                                </span>

                                    <span class="checkbox" style="padding: 10px;">
                                    <label for="locked_prev">  Locked by Previous Chain </label>
                                    <input type="checkbox" class="locked_prev_checkbox"
                                           name="locked_prev" <?php if ($locked_by_prev) {
                                        echo 'checked';
                                    } ?>>
                                    </span>

                                <?php
                            }
                            ?>

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

            }

            echo "</div></div></div>";

        }


        echo "<div style='clear:both;' class='go_map_chain_title title'>$term_name</div></div>";

        //START: The list of tasks in the chain
        $chain_messages = array();

        if ($is_hidden){
            $chain_messages[] = "This section is hidden from players.";
        }
        if ($is_locked) {
            if(!empty($locked_html)) {
                $chain_messages[] = $locked_html;
            }
        }

        if (!in_array($hide_if_locked, array('message'))) {
            if ($is_pod) {
                $pod_min = (isset($term_custom['pod_done_num'][0]) ? $term_custom['pod_done_num'][0] : false);
                $pod_max = (isset($term_custom['pod_max_num'][0]) ? $term_custom['pod_max_num'][0] : false);
                $pod_all = (isset($term_custom['pod_all'][0]) ? $term_custom['pod_all'][0] : null);
                $pod_count = count($go_post_ids);
                if ($pod_all || ((($pod_min >= $pod_count)) && ($pod_count > 1))) {
                    //$task_name_pl = get_option('options_go_tasks_name_plural'); //Q option
                    //echo "<br><span style='padding-top: 10px; font-size: .8em;'>Complete all $task_name_pl. </span>";
                } else {
                    if ($pod_max && $pod_count > 0) {
                        if ($pod_count > 1) {
                            $task_name = $task_name_plural; //Q option
                        } else {
                            $task_name = $task_name_singular; //Q option
                        }
                        $chain_messages[] = "Choose $pod_min $task_name to complete.";

                    } else if ($pod_count > 0) {
                        if ($pod_min > 1) {
                            $task_name = $task_name_plural; //Q option
                        } else {
                            $task_name = $task_name_singular; //Q option
                        }

                        $chain_messages[] = "Choose $pod_min $task_name to complete.";
                    }
                }
            }
        }


        // echo "<h3>$term_name</h3>";
        if(!empty($chain_messages)) {
            $messages = implode('<br>', $chain_messages);
            echo "<div class='go_task_pod_required'>$messages</div>";
        }

        if (in_array($hide_if_locked, array('message')) && !$is_admin) {
            continue;
        }



        echo "<ul class='tasks connectedSortable' data-chain_id='$term_id'>"; //the task list in the column

        //If there are tasks
        $first = true;
        if (!empty($go_post_ids)) {

            //Query 3
            //Get User info for these tasks
            if(!$is_store) {
                $go_task_table_name = "{$wpdb->prefix}go_tasks";
                $id_string = implode(',', $go_post_ids);
                $user_tasks = $wpdb->get_results(
                    "SELECT *
                    FROM {$go_task_table_name}
                    WHERE uid = $user_id AND post_id IN ($id_string)
                    ORDER BY last_time DESC"
                );
                $user_tasks = json_decode(json_encode($user_tasks), True);
            }else{
                $user_tasks = array();
            }
                $task_color = '';
                $prev_done = true;
                $optional = '';
                $hidden = '';
                $first_nested = false;
                $last_was_nested = false;
                // $last_was_hidden = false;
                $hidden_nest = false;
                $parent_hidden = false;



            //for each of the tasks (objects retrieved in query 2)
            foreach ($go_post_ids as $post_id) {


                $go_task_data = go_post_data($post_id); //0--name, 1--status, 2--permalink, 3--metadata
                $task_name = $go_task_data[0];
                $status = $go_task_data[1];
                $task_link = $go_task_data[2];
                $custom_fields = $go_task_data[3];


                if(!$is_store) {
                    $loot = go_task_loot($post_id);


                    $my_xp = 0;
                    $my_gold = 0;
                    $my_health = 0;


                    $xp_loot = $loot['xp'];
                    $gold_loot = $loot['gold'];
                    $health_loot = $loot['health'];
                    $badges = $loot['badges'];
                    $groups = $loot['groups'];

                    $chain_xp = $chain_xp + $xp_loot;
                    $chain_gold = $chain_gold + $gold_loot;
                    $chain_health = $chain_health + $health_loot;

                    $map_xp = $map_xp + $xp_loot;
                    $map_gold = $map_gold + $gold_loot;
                    $map_health = $map_health + $health_loot;
                }
                else{
                    $xp_toggle = (isset($custom_fields['go_loot_reward_toggle_xp'][0]) ?  $custom_fields['go_loot_reward_toggle_xp'][0] : null);
                    $xp_value = (isset($custom_fields['go_loot_loot_xp'][0]) ?  $custom_fields['go_loot_loot_xp'][0] : null);
                    $gold_toggle = (isset($custom_fields['go_loot_reward_toggle_gold'][0]) ?  $custom_fields['go_loot_reward_toggle_gold'][0] : null);
                    $gold_value = (isset($custom_fields['go_loot_loot_gold'][0]) ?  $custom_fields['go_loot_loot_gold'][0] : null);
                    $health_toggle = (isset($custom_fields['go_loot_reward_toggle_health'][0]) ?  $custom_fields['go_loot_reward_toggle_health'][0] : null);
                    $health_value = (isset($custom_fields['go_loot_loot_health'][0]) ?  $custom_fields['go_loot_loot_health'][0] : null);

                    $badges = false;
                    $groups = false;
                }




                $nested_class = "";
                $nested = (isset($custom_fields['go-location_map_options_nested'][0]) ? $custom_fields['go-location_map_options_nested'][0] : false);
                $marked_hidden = (isset($custom_fields['go-location_map_options_hidden'][0]) ? $custom_fields['go-location_map_options_hidden'][0] : false);
                $optional = (isset($custom_fields['go-location_map_options_optional'][0]) ? $custom_fields['go-location_map_options_optional'][0] : false);
                $hidden_class = '';

                if(!is_user_logged_in() && $marked_hidden){
                    continue;
                }

                //$status = get_post_status( $row );//is post published
                if ($status !== 'publish') {
                    continue;
                }//don't show if not pubished

                //$task_name = $row->post_title; //Q
                //$task_link = get_permalink($row); //Q
                //$id = $row->ID;
                //$custom_fields = go_post_meta( $id ); // Just gathering some data about this task with its post id
                //$stage_count = $custom_fields['go_stages'][0];//total stages
                $stage_count = (isset($custom_fields['go_stages'][0]) ?  $custom_fields['go_stages'][0] : null);

                //$user_tasks is an array of task object arrays
                //These next lines pull information from the array by
                //getting the position of this task in the array,
                //then get the status this task from the tasks array
                $ids = array_map(function ($each) {
                    return $each['post_id'];
                }, $user_tasks);

                $key = array_search($post_id, $ids);

                if ($key !== false) {
                    $this_task = $user_tasks[$key];
                    $status = $this_task['status'];
                    $class = $this_task['class'];
                    $my_xp = $this_task['xp'];
                    $my_gold = $this_task['gold'];
                    $my_health = $this_task['health'];

                    $chain_my_xp = $chain_my_xp + $my_xp;
                    $chain_my_gold = $chain_my_gold + $my_gold;
                    $chain_my_health = $chain_my_health + $my_health;

                    $map_my_xp = $map_my_xp + $my_xp;
                    $map_my_gold = $map_my_gold + $my_gold;
                    $map_my_health = $map_my_health + $my_health;


                    if (!empty($class)) {
                        if (is_serialized($class)) {
                            $class = unserialize($class);
                        }
                        if (is_array($class)) {
                            $class = implode(" ", $class);
                        }
                    }
                    if ($status == -2) {//if the entire task was reset
                        $class .= ' reset';
                    }
                }
                else {
                    $status = 0;
                    $this_task = array();
                    $class = '';
                }
                //add status to cache
                $cache_key = 'go_get_status_' . $post_id;
                wp_cache_set($cache_key, $status, 'go_single');


                $bonus_switch = (isset($custom_fields['bonus_switch'][0]) ?  $custom_fields['bonus_switch'][0] : false);
                if ($bonus_switch) {
                    $bonus_stage_toggle = true;
                    if ($key !== false) {
                        $bonus_status = $this_task['bonus_status'];
                    } else {
                        $bonus_status = 0;
                    }
                    //$bonus_status = go_get_bonus_status($id, $user_id);
                    $repeat_max = $custom_fields['go_bonus_limit'][0];//max repeats of bonus stage
                    $bonus_stage_name = get_option('options_go_tasks_bonus_stage') . ':';
                } else {
                    $bonus_stage_toggle = false;
                }

                //if locked
                if(!$is_store) {
                    $task_is_locked = go_task_locks($post_id, true, $user_id, false, $custom_fields);

                    //$task_is_locked = false;
                    $unlock_message = '';
                    if ($task_is_locked === 'password') {
                        $unlock_message = '<div><i class="fas fa-unlock"></i> Password</div>';
                        $task_is_locked = false;
                    } else if ($task_is_locked === 'master password') {
                        $unlock_message = '<div><i class="fas fa-unlock"></i> Master Password</div>';
                        $task_is_locked = false;
                    }
                }else{
                    $task_is_locked = false;
                    $unlock_message = '';
                    $scheduled_message = '';
                    if ($custom_fields['go_sched_toggle'][0] == true) {
                        $task_is_locked = go_task_locks($post_id, true, $user_id, "Item", $custom_fields);
                        if($task_is_locked){
                            $scheduled_message = '<div style="font-size: .8em;">See Access Schedule</div>';
                        }
                    }

                    if ($custom_fields['go_lock_toggle'][0] == true && !$task_is_locked) {
                        $task_is_locked = go_task_locks($post_id, true, $user_id, "Item", $custom_fields);
                    }
                    $go_password_lock = (isset($custom_fields['go_password_lock'][0]) ? $custom_fields['go_password_lock'][0] : null);
                    if ($go_password_lock == true && !$task_is_locked) {
                        $task_is_locked = true;
                    }

                    if(!$task_is_locked){
                        $purchase_limit = null;
                        $purchase_limit = go_get_purchase_limit($post_id, $user_id, $custom_fields);
                        if(!is_numeric($purchase_limit)){
                            $task_is_locked = true;
                        }
                    }
                }
                /*
                                    if ($custom_fields['go-location_map_opt'][0]) {
                                        $optional = 'optional_task';
                                        //$bonus_task = get_option('options_go_tasks_optional_task').':';  //Q option
                                    }
                                    else {
                                        $optional = null;
                                        //$bonus_task = null;
                                    }
                */
                //$optional = false;
                // $nested = false;
                //$hidden = false;



                if ($optional) {
                    $hidden_class .= ' optional ';
                }


                //if it is hidden and not nested
                    //show to admin always
                    //only show if available to players
                //
                if ($marked_hidden && !$nested){
                    if ($is_admin) {
                        $hidden_class .= ' show_shadow ';
                       /* if ((!$is_pod && !$prev_done ) || ( $task_is_locked)) {
                            $hidden_class .= ' low_opacity';
                        }*/
                    } else {
                        if ((!$is_pod && !$prev_done) || ($task_is_locked)) {
                            $parent_hidden = true;
                            continue;
                        }
                    }
                    $parent_hidden = false;
                    //$hidden_class .= '';
                }

                //if it is nested
                    //was parent hidden
                        //hide if not admin
                    //is marked hidden
                        //hide if not available
                            //but show to admin

                if($nested){
                    $nested_class = "nested";
                    if($parent_hidden){
                        if ($is_admin) {
                           /* if ((!$is_pod && !$prev_done)) {
                                $hidden_class .= ' low_opacity ';
                            }*/
                        } else {
                            continue;
                        }
                    }

                    if ($marked_hidden){
                        if ($is_admin) {
                            $hidden_class .= ' show_shadow ';
                           /* if ((!$is_pod && !$prev_done)) {
                                $hidden_class .= ' low_opacity ';
                            }*/
                        } else {
                            if ((!$is_pod && !$prev_done) || ($is_pod && $task_is_locked) ) {//|| ($is_pod && !$prev_done)
                                continue;
                            }
                        }
                    }
                }else{
                    $nested_class = "not_nested";
                }

                if(!$optional) {
                    if ($stage_count <= $status) {//it is done
                        $prev_done = true;
                    } else {
                        $prev_done = false;
                    }
                }

/*
                //if this task is hidden and the previous task isn't done, then don't print the task
                if ($marked_hidden || ($nested && $hidden_nest)) {//if marked hidden or nested in a hidden quest
                    $hidden = true;
                    if($marked_hidden) {
                        $hidden_class .= 'show_shadow';
                    }
                    if ($is_admin) {
                        if ((!$is_pod && !$prev_done)) {
                            $hidden_class .= ' low_opacity ';
                        }
                    } else {

                        if ((!$is_pod && !$prev_done) || ($is_pod && ($task_is_locked)) || ($is_pod && !$prev_done && $marked_hidden && $nested)) {
                            $prev_hidden = true;
                            continue;
                        }
                    }
                    $prev_hidden = false;
                }else{
                    $prev_hidden = false;
                }



                if ($nested) {
                    //$nested = true;
                    $nested_class = "nested";
                    //$bonus_task = get_option('options_go_tasks_optional_task').':';  //Q option
                }else{
                    if($hidden){
                        $hidden_nest = true;
                    }else{
                        $hidden_nest = false;
                    }
                }

                else {//not marked hidden or the other thing
                    $hidden = false;
                    if ($nested) {
                        //$nested = true;
                        $nested_class = "nested";
                        $hidden_class .= ' low_opacity ';
                        //$bonus_task = get_option('options_go_tasks_optional_task').':';  //Q option
                    }else{
                        if($hidden){
                            $hidden_nest = true;
                        }else{
                            $hidden_nest = false;
                        }
                    }
                }
*/

/*
                if (!$hidden) {//if it is not hidden
                    if ($stage_count <= $status && !$optional) {//it is done
                        $prev_done = true;
                    }
                }*/


                //close previous task
                //this is done here to check if the next one was nested before closing
                if($first) {
                    $first = false;
                }else{

                    if ($nested && !$first_nested) {//the previous task is done and this is the first nested, print the toggle
                        echo "</div></div>";//close the last task, but not the <li>
                        if($link_to_tasks) {
                            echo "</a>";
                        }
                        echo "</div>";//close the show actions
                        //echo "</li>";//close the last nested

                        echo "<span class='go_nested_hover'><div class='go_nested_toggle'><div class='nested_opaque'><div class='nested_icon'><i class='fas fa-caret-down'></i></div></div> </div>";
                        echo "<ul class='go_nested_list' style='display: none;'>";
                        $first_nested = true;
                        $last_was_nested = true;
                    } else if ($nested) {//this is nested, but not the first nested
                        echo "</div></div>";
                        if($link_to_tasks) {
                            echo "</a>";
                        }
                        echo "</div>";//close the show actions
                        echo "</li>";//close the last nested task
                        $last_was_nested = true;
                    }else if (!$nested){
                        if ($last_was_nested){
                            echo "</div></div>";
                            if($link_to_tasks) {
                                echo "</a>";
                            }
                            echo "</div>";//close the show actions
                            echo "</li>";//close the last nested
                            echo "</ul></span>";// close the nested task
                            echo "</li>";//close the parent
                            //clost the nest
                        }else {
                            echo "</div></div>";
                             if($link_to_tasks) {
                                echo "</a>";
                            }
                            echo "</div>";//close the show actions
                             echo "</li>";
                        }
                        $first_nested = false;
                        $last_was_nested = false;
                    }


                }


                //echo "<li class='task_container'><div onclick='go_to_task()' class='task $task_color $nested_class $class $hidden_class'>";
                if ($link_to_tasks) {//This is the regular map
                    $addClass = '';

                }
                else {//this is the map on the clipboard
                    if($is_store){
                        $addClass = 'go_str_item ';
                    }
                    else {
                        $addClass = 'go_blog_user_task';
                    }
                    $task_link = '';
                }


                $badges_class = '';
                if ($badges) {//if there are badges awarded on this task
                    $badges_class = 'hasBadges';
                }

                $bonus_class = '';
                if ($bonus_stage_toggle) {//if there are badges awarded on this task
                    $bonus_class = 'hasBonus';
                }

                echo "<li class='task_container task_container_{$post_id} $badges_class $bonus_class quick_container' data-post_id='$post_id'>";
                echo "<div class='go_show_actions'>";
                if(((($is_subscriber || $is_admin) && $admin_view === 'admin') || ($is_admin_any_other_blog && !$is_really_admin) && $link_to_tasks)){

                    echo "<div class='actions_tooltip no_redirect' style='display: none;'><div class='my_tooltip'><div class='go_actions_wrapper_flex no_redirect'>";

                    echo "<span class='tools task_tools no_redirect'>";

                    if(is_gameful()){
                        do_action('gop_add_importer_icon', $post_id, 'post', $user_id, false);
                    }
                    if($is_admin) {
                        $url = get_edit_post_link($post_id);
                        //echo "<span class='no_redirect'><a href='{$url}'><i class='far fa-edit'></i></a></span>";
                        echo "<span class='go_edit_frontend action_icon actiontip' data-tippy-content='Edit this $task_name_singular.' data-post_id='$post_id'><a><i class='far fa-edit'></i></a></span>";
                        echo "<span class='go_quick_edit_show action_icon actiontip' data-tippy-content='Quick edit.' data-task_id='$post_id'><a><i class='far fa-bolt'></i></a></span>";

                        if(!$is_store){
                            echo "<span class='go_quest_reader_lightbox_button action_icon actiontip' data-tippy-content='See items submitted on this $task_name_singular.' data-post_id='{$post_id}' data-stage='all'><a><i class='far fa-book-open'></i></a></span>";
                        }
                        echo "<span class='go_quests_frontend action_icon actiontip' data-post_id='{$post_id}' data-tippy-content='Get detailed info about this $task_name_singular.'><a><i class='far fa-clipboard-list'></i></a></span>";
                        echo "<span class='go_trash_post action_icon actiontip' data-post_id='{$post_id}' data-title='{$task_name}' data-tippy-content='Trash this $task_name_singular.'><a><i class='far fa-trash'></i></a></span>";
                    }
                    echo "</span>";
                    if($is_admin){
                        ?>
                        <span class="quickedit_form_container" style="display: none;">
                            <span class="quickedit_form" data-post_id='<?php echo $post_id; ?>' data-taxonomy='<?php echo $taxonomy; ?>'>
                                <div style="display: block;">

                                    <input type="text" class="post_title" name="post_title" size="30" value="<?php echo htmlspecialchars($task_name); ?>">

                                    <?php
                                    if(!$is_store) {
                                        ?>
                                        <br>
                                        <span class="checkbox" style="padding: 10px;">
                                        <label for="hidden">  Hidden </label>
                                        <input type="checkbox" class="hidden_checkbox"
                                               name="hidden" <?php if ($marked_hidden) {
                                            echo 'checked';
                                        } ?>>
                                    </span>
                                        <span class="checkbox" style="padding: 10px;">
                                        <label for="nested">  Nested </label>
                                        <input type="checkbox" class="nested_checkbox"
                                               name="nested" <?php if ($nested) {
                                            echo 'checked';
                                        } ?>>
                                    </span>
                                        <span class="checkbox" style="padding: 10px;">
                                        <label for="optional">  Optional </label>
                                        <input type="checkbox" class="optional_checkbox"
                                               name="optional" <?php if ($optional) {
                                            echo 'checked';
                                        } ?>>
                                    </span>
                                        <?php
                                    }
                                        ?>

                                </div>
                            </span>
                        </span>

                        <?php
                    }
                    echo "</div></div></div>";
                }


                if($is_store){

                    if($task_is_locked){
                        $task_color = 'locked';
                    }else {
                        $task_color = 'available';
                    }
                }else {


                    if ($stage_count <= $status) {
                        $class = str_replace('reset', 'resetted', $class);
                        $task_color = 'done';
                    } else if ($task_is_locked) {
                        $task_color = 'locked';
                    } else {
                        $task_color = 'available';
                    }
                }


                if($link_to_tasks) {
                    echo "<a href='$task_link'>";
                }
                echo "<div  data-user_id='$user_id' data-post_id='$post_id' class='task no_redirect $task_color $nested_class $class $addClass $hidden_class task_id_$post_id'>";

                echo "<div class='go_map_quest_hover '>";



                echo "<div><div class='title'>$task_name </div>$unlock_message</div>";


                //<a href="javascript:;" class="go_blog_user_task" data-UserId="'.$user_id.'" onclick="go_blog_user_task('.$user_id.', '.$post_id.');">

                if(!$is_store) {
                    echo "<div class='loot_info' >";
                    go_map_loot($xp_loot, $gold_loot, $health_loot, $my_xp, $my_gold, $my_health);

                    echo "</div>";
                }
                else{
                    if(isset($scheduled_message)){
                        echo $scheduled_message;
                    }
                    echo "<div class='go_store_loot_list'>";

                    if (!empty($xp_value)){
                        if ($xp_toggle == 1 ){
                            $loot_class = 'go_store_loot_list_reward';
                            $loot_direction = "+";

                        }
                        else{
                            $loot_class = 'go_store_loot_list_cost';
                            $loot_direction = "-";
                        }
                        $xp_value = go_display_shorthand_currency('xp', $xp_value);
                        echo "<div id = 'go_store_loot_list_xp' class='go_store_loot_list_item " . $loot_class . "' >" . $loot_direction . $xp_value ."</div > ";
                    }

                    if (!empty($gold_value)){
                        if ($gold_toggle == 1 ){
                            $loot_class = 'go_store_loot_list_reward';
                            $loot_direction = "+";
                        }
                        else{
                            $loot_class = 'go_store_loot_list_cost';
                            $loot_direction = "-";
                        }
                        $gold_value = go_display_shorthand_currency('gold', $gold_value);
                        echo "<div id = 'go_store_loot_list_gold' class='go_store_loot_list_item " . $loot_class . "' >"  . $loot_direction . $gold_value . "</div > ";
                    }

                    if (!empty($health_value)){
                        if ($health_toggle == 1 ){
                            $loot_class = 'go_store_loot_list_reward';
                            $loot_direction = "+";
                        }
                        else{
                            $loot_class = 'go_store_loot_list_cost';
                            $loot_direction = "-";
                        }
                        $health_value = go_display_shorthand_currency('health', $health_value);
                        echo "<div id = 'go_store_loot_list_health' class='go_store_loot_list_item " . $loot_class . "' >" . $loot_direction . $health_value . "</div > ";
                    }

                    echo "</div>";
                }


                //$badge_ids = (isset($custom_fields['go_badges'][0]) ?  $custom_fields['go_badges'][0] : null);

                if ($badges) {//if there are badges awarded on this task
                    if (!empty($badges)) {
                        //go_map_badge($badge_ids, $user_badges, false, $user_id);
                        go_print_single_badge($badges, 'badge', $output = true, $user_id, 'go_map_badge');
                    }
                }

                if ($groups) {//if there are badges awarded on this task
                    if (!empty($groups)) {
                        //go_map_badge($badge_ids, $user_badges, false, $user_id);
                        go_print_single_badge($groups, 'group', $output = true, $user_id, 'go_map_badge');
                    }
                }

                /*
                if ($bonus_stage_toggle == true){
                    if ($bonus_status == 0 || $bonus_status == null){
                        echo "<br><div id='repeat_ratio' style='padding-top: 10px; font-size: .7em;'>$bonus_stage_name
                            <div class='star-empty fa-stack'>
                                <i class='fa fa-star fa-stack-2x''></i>
                                <i class='fa fa-star-o fa-stack-2x'></i>
                            </div> 0 / $repeat_max</div>

                    ";
                    }
                    else if ($bonus_status == $repeat_max) {
                        echo "<br><div style='padding-top: 10px; font-size: .7em;'>$bonus_stage_name
                            <div class='star-full fa-stack'>
                                <i class='fa fa-star fa-stack-2x''></i>
                                <i class='fa fa-star-o fa-stack-2x'></i>
                            </div> $bonus_status / $repeat_max</div>
                        ";
                    }
                    else {
                        echo "<br><div style='padding-top: 10px; font-size: .7em;'>$bonus_stage_name
                            <div class='star-half fa-stack'>
                                <i class='fa fa-star fa-stack-2x''></i>
                                <i class='fa fa-star-half-o fa-stack-2x'></i>
                                <i class='fa fa-star-o fa-stack-2x'></i>

                            </div> $bonus_status / $repeat_max</div>
                        ";
                    }
                }
                */

                if ($bonus_stage_toggle == true) {
                    $percentage = $bonus_status / $repeat_max * 100;
                    $progress_bar = '<div class="go_bonus_progresss_bar_position"></div><div class="go_bonus_progresss_bar_container" style="border:none;">' . '<div class="go_bonus_progress_bar" ' .
                        'style="width: ' . $percentage . '%;"' .
                        ' data-width=" '. $percentage . '">' .
                        '</div>' .
                        '<div class="bonus_progress">BONUS: ' .
                        $bonus_status . ' / ' . $repeat_max . '</div>' .
                        '</div>';
                    echo $progress_bar;
                }
            }//end of for each

            if ($last_was_nested){
                echo "</div></div>";
                 if($link_to_tasks) {
                   echo "</a>";
                 }
                echo "</div>";//close the show actions
                 echo "</li>";//close the last nested
                echo "</ul></span>";// close the nested task
                echo "</li>";//close the parent
                //close the nest
            }else {
                echo "</div></div>";
                if($link_to_tasks) {
                    echo "</a>";
                }
                echo "</div>";//close the show actions
                echo "</li>";//close the last nested
            }

        }


        echo "</ul>";//close the list of tasks

        //badge for chains
        $badge_ids = get_term_meta($term_id, "pod_achievement", true);


        if (!empty($badge_ids)) {
            go_map_badge($badge_ids, $user_badges, true, $user_id);
        }

        if(!$is_store) {
            echo "<div class='loot_info go_chain_loot_info' style='font-size: .7em;  background-color: rgba(255, 255, 255, 0.7);'>";
            go_map_loot($chain_xp, $chain_gold, $chain_health, $chain_my_xp, $chain_my_gold, $chain_my_health);
            echo "</div>";
        }

        echo "</li>";//end of column

        //$locked_html = ob_get_contents();
        //ob_end_clean();
        // $locked_html;
    }

    echo "</ul>"; //closes the main list of columns PrimaryNav
    /*
     * //Show map at end of map in own colum
    //badge for map
    $badge_ids = get_term_meta($map_id, "pod_achievement", true);
    if (!empty($badge_ids)) {
        go_map_badge($badge_ids, $user_badges, true);
    }*/


    echo "</div>";

    if(!$is_store) {
        echo "<div class='loot_info go_map_loot_info' style='font-size: .8em;  background-color: rgba(255, 255, 255, 0.7); display: none;'>";
        go_map_loot($map_xp, $map_gold, $map_health, $map_my_xp, $map_my_gold, $map_my_health);
        echo "</div>";
    }

    echo "</div>";
    if ($reload == false) {echo "</div>";}
}

/**
 * @param $xp
 * @param $gold
 * @param $health
 * @param $my_xp
 * @param $my_gold
 * @param $my_health
 */
function go_map_loot($xp, $gold, $health, $my_xp, $my_gold, $my_health){

    $xp = floatval($xp);
    $gold = floatval($gold);
    $health = floatval($health);
    $my_xp = floatval($my_xp);
    $my_gold = floatval($my_gold);
    $my_health = floatval($my_health);


    $add_divider = false;
    $no_loot = false;
    $my_loot = "<div class='my_loot'>";
    if (!empty($my_xp)) {
        $my_loot .= "<b>";
        $my_loot .= go_display_shorthand_currency('xp', $my_xp, false, false, "", false);
        $my_loot .= "</b>";
        $add_divider = true;
    }else{
        $my_xp = null;
    }
    if (!empty($my_gold)) {
        if($add_divider){
            $my_loot .= "&nbsp;|&nbsp;";
            $add_divider = true;
        }
        $my_loot .= "<b>";
        $my_loot .= go_display_shorthand_currency('gold', $my_gold, false, false, "", false);
        $my_loot .= "</b>";
    }else{
        $my_gold = null;
    }
    if(!empty($my_health)) {
        if($add_divider){
            $my_loot .= "&nbsp;|&nbsp;";
            $add_divider = true;
        }
        $my_loot .= "<b>";
        $my_loot .= go_display_shorthand_currency('health', $my_health, false, false, "", false);
        $my_loot .= "</b>";
    }else{
        $my_health = null;
    }
    if (empty($my_xp) && empty($my_gold) && empty($my_health)  ){
        $my_loot .= "No loot earned.";
        $no_loot = true;
    }
    $my_loot .= "</div>";

    $loot_seperator = "<div>-of-</div>";


    $add_divider = false;
    $no_assigned_loot = false;
    $the_loot = "<div class='max_loot'>";
    if (!empty($xp)) {
        $the_loot .= "<b>";
        $the_loot .= go_display_shorthand_currency('xp', $xp, false, false, "", false);
        $the_loot .= "</b>";
        $add_divider = true;
    }
    if (!empty($gold)) {
        if($add_divider){
            $the_loot .= "&nbsp;|&nbsp;";
            $add_divider = true;
        }
        $the_loot .= "<b>";
        $the_loot .= go_display_shorthand_currency('gold', $gold, false, false, "", false);
        $the_loot .= "</b>";
    }
    if(!empty($health)) {
        if($add_divider){
            $the_loot .= "&nbsp;|&nbsp;";
            $add_divider = true;
        }
        $the_loot .= "<b>";
        $the_loot .= go_display_shorthand_currency('health', $health, false, false, "", false);
        $the_loot .= "</b>";
    }
    if (empty($xp) && empty($gold) && empty($health)  ){
        $the_loot .= "No loot assigned.";
        $no_assigned_loot = true;

    }
    $the_loot .= "</div>";

    if($no_loot && $no_assigned_loot){
        echo $the_loot;
    }
    else{
        echo $my_loot;
        echo $loot_seperator;
        echo $the_loot;
    }
}

/**
 * @param $atts
 * @param null $content
 * @return string
 */
function go_single_map_link($atts, $content = null ) {
    $atts = shortcode_atts(
        array(
            "map_id" => ''
        ),
        $atts
    );
    $map_id = $atts['map_id'];
    return "<a href='#' onclick='go_to_this_map(" . $map_id . ")'>" . $content . "</a>";
}
add_shortcode( 'go_single_map_link', 'go_single_map_link' );



/**
 * @param $badge_ids
 * @param $user_badges
 * @param $container
 *
 * Show badge on map for tasks
 */
function go_map_badge($badge_ids, $user_badges, $container, $user_id){

    if($badge_ids) {//if there are badges awarded on this task
        if (is_serialized($badge_ids)){
            $badge_ids = unserialize($badge_ids);//legacy badges saved as serialized array
        }
        if (!is_array($badge_ids)){
            $badge_ids = array($badge_ids);
        }
        if (is_array($badge_ids)){
            foreach($badge_ids as $badge_id) {
                if ($container){
                    if (in_array($badge_id, $user_badges)){
                        $task_color = 'done';//set to green if badge is posessed
                        //$badge_needed = '';
                    }else{
                        $task_color = 'locked';//set to grey if needed
                        //$badge_needed = 'go_badge_needed';
                    }
                    echo "<div class='". $task_color . " go_task_chain_map_box go_task_chain_map_box_badge'><a class='go_map_badge'>";
                }
                //go_map_quest_badge($badge_id, $user_badges, true);
                go_print_single_badge( $badge_id, 'badge', $output = true, $user_id );
                if ($container){
                    echo "</a></div>";
                }
            }
        }
    }

}

/**
 *  * Show badge on map for chains
 *
 * @param $badge
 * @param $user_badges
 */
/*
function go_map_quest_badge($badge, $user_badges, $small = false){
    //does this term have a badge assigned and if so show it
    if ($user_badges == null){
        $user_badges = array();
    }
    if($badge){
        if (in_array($badge, $user_badges)){
            //$task_color = 'done';
            $badge_needed = '';
        }else{
            //$task_color = 'locked';
            $badge_needed = 'go_badge_needed';
        }
        $badge_img_id = get_term_meta( $badge, 'my_image' );
        $badge_description = term_description( $badge );

        //echo "<li>";
        $badge_obj = get_term( $badge);
        $badge_name = $badge_obj->name;
        //$badge_img_id =(isset($custom_fields['my_image'][0]) ?  $custom_fields['my_image'][0] : null);



        //$badge_attachment = wp_get_attachment_image( $badge_img_id, array( 100, 100 ) );
        //$img_post = get_post( $badge_id );
        if(!$small) {
            if (isset($badge_img_id[0]) && !empty($badge_img_id[0])){
                $badge_img = wp_get_attachment_image($badge_img_id[0], array( 100, 100 ));
            }else{
                $badge_img = '<i class="fas fa-award fa-4x"></i>';
            }

            if (!empty($badge_obj)) {
                echo "<div class='go_badge_quest_wrap'>
                        <div class='go_badge_quest_container " . $badge_needed . "'><figure class=go_quest_badge title='{$badge_name}'>";

                if (!empty($badge_description)) {
                    $badge_description = strip_tags($badge_description);
                    //echo "<span class='tooltip' ><span class='tooltiptext'>{$badge_description}</span>{$badge_img}</span>";
                    echo "<span class='tooltip' data-tippy-content='{$badge_description}'>{$badge_img}</span>";
                } else {
                    echo "$badge_img";
                }
                echo "        
              				 <figcaption>{$badge_name}</figcaption>
                            </figure>
                        </div>
                       </div>";

            }
        }else{
            if (isset($badge_img_id[0]) && !empty($badge_img_id[0])){
                $badge_img = wp_get_attachment_image($badge_img_id[0], array( 50, 50 ));
            }else{
                $badge_img = '<i class="fas fa-award fa-3x"></i>';
            }
            if (!empty($badge_obj)) {
                echo "<div class='go_badge_quest_container " . $badge_needed . "'><figure class=go_quest_badge title='{$badge_name}'>";

                if (!empty($badge_description)) {
                    $badge_description = strip_tags($badge_description);
                    echo "<span class='tooltip' data-tippy-content='{$badge_description}'>{$badge_img}</span>";
                } else {
                    echo "$badge_img";
                }
                echo "        
              				 <figcaption>{$badge_name}</figcaption>
                            </figure>
                        </div>
                       ";

            }
        }
        //echo "</li>";

    }
}
*/


/**
 * @param $taxonomy
 * @param $last_map_id
 * @param $user_id
 * @param $is_admin
 * @param $link_to_tasks
 */
function go_make_map_dropdown($taxonomy, $map_id, $user_id, $is_admin, $clipboard_map){
    /* Get all task chains with no parents--these are the top level on the map.  They are chains of chains (realms). */
    //$taxonomy = 'task_chains';
    /*$term_args0=array(
          'hide_empty' => false,
          'order' => 'ASC',
          'parent' => '0'
    );*/
    global $wpdb;

    global $is_really_admin;
    $title = 'Choose';
    $is_hidden = "";
    //if($taxonomy === 'task_chains'){
        $map_data = go_term_data($map_id);
        $name = $map_data[0];
        $map_prefix = "";


    if($taxonomy === 'store_types'){
        $is_hidden = get_term_meta($map_id, 'go_hide_store_cat', true);
    }
    else{
        $is_hidden = get_term_meta($map_id, 'go_hide_map', true);
    }


        if ($is_hidden && $is_admin) {
            $map_prefix = "Hidden: ";
        }

        $title = $map_prefix." ".$name;
   // }
    //$tax_terms_maps = get_terms($taxonomy,$term_args0);
    //$tax_terms_maps = go_get_terms_ordered($taxonomy, '0');

    $tax_terms_maps = go_get_parent_term_ids($taxonomy);

    if($user_id != null && $clipboard_map){


        $user_name = go_get_fullname($user_id);
        echo "<div style='justify-content:center; align-items:center; display: flex;'><div class='go_map_prev' style='display: none; padding-right: 30px;'>Prev</div><h1 id='go_map_user' data-uid='".intval($user_id)."' >Viewing map as ". $user_name . "</h1><div class='go_map_next' style='display: none; padding-left: 30px;'>Next</div></div>";

    }

    echo"
	<div id='sitemap' style='text-align:center;'>";


    //echo "<div class='map_title go_show_actions'></div>";
    //$admin_view = get_user_option('go_admin_view', $user_id);
    $admin_view = go_get_admin_view($user_id);

    $is_admin_any_other_blog = go_user_is_admin_on_any_other_blog();
    if (function_exists ( 'wu_is_active_subscriber' ) ) {
        $is_subscriber = wu_is_active_subscriber($user_id);
    }else{
        $is_subscriber = false;
    }
    echo"
	<div class='go_show_actions'>";
    if (((($is_subscriber || $is_admin) && $admin_view === 'admin') || ($is_admin_any_other_blog && !$is_really_admin)) && !$clipboard_map){
        echo "<div class='actions_tooltip' style='display: none;' ><div class='my_tooltip'><div class='go_actions_wrapper_flex'>";
        echo "<span class='tools task_tools'>";
        if($is_admin) {

            echo "<span class='go_edit_frontend action_icon actiontip' data-new_child_term='$map_id' data-term_name='$title' data-tippy-content='Add a new section to this page.'><a><i class='far fa-plus-circle'></i></a></span>";
        }
        if(is_gameful()){
            do_action('gop_add_importer_icon', $map_id, 'term', $user_id, false);
        }
        if($is_admin) {
            if($taxonomy === 'task_chains') {
                echo "<span class='go_edit_frontend action_icon actiontip' data-term_id='$map_id' data-tippy-content='Edit this page.'><a><i class='far fa-edit'></i></a></span>";
            }
            echo "<span class='go_quick_edit_show action_icon actiontip' data-term_id='$map_id' data-tippy-content='Quick edit.'><a><i class='far fa-bolt'></i></a></span>";

            echo "<span class='go_trash_post action_icon actiontip' data-map_id='{$map_id}' data-title='{$name}' data-tippy-content='Trash this page.'><a><i class='far fa-trash'></i></a></span>";


        }
        echo "</span>";
        if($is_admin) {
            ?>
            <span class="quickedit_form_container" style="display: none;">
                    <span class="quickedit_form" data-term_id='<?php echo $map_id; ?>'>
                        <div style="display: block;">

                            <input type="text" class="term_title" name="term_title" size="30"
                                   value="<?php echo htmlspecialchars($name); ?>">
                            <br>

            <?php
           // if($taxonomy === 'task_chains') {
                ?>
                <span class="checkbox" style="padding: 10px;">
                                <label for="hidden">  Hidden </label>
                                <input type="checkbox" class="hidden_checkbox" name="hidden" <?php if ($is_hidden) {
                                    echo 'checked';
                                } ?>>
                            </span>
                <?php
           // }
                    ?>



                        </div>
                    </span>
                </span>
            <?php
        }
        echo"</div></div></div>";
    }


    echo "<div class='dropdown available' onmouseleave='go_map_dropDown(false)'>";





    echo"
      <div class='droptop quick_container' onmouseenter='go_map_dropDown(true)'><div class='drop_title go_show_actions '>$title";






    echo "</div><div id='go_drop_arrow'><i class='down fas fa-caret-down'></i><i class='up fas fa-caret-up' style='display: none;'></i></div></div>
      <div id='go_Dropdown' class='dropdown-content .dropdown-menu hidden'>";


    /* For each task chain with no parent, add to Dropdown  */
    foreach ( $tax_terms_maps as $tax_term_map ) {
        $prefix = "";
        $term_id = $tax_term_map->term_id;


        $map_data = go_term_data($term_id);
        $custom_fields = $map_data[1];
        $hide_if_locked = (isset($custom_fields['hide_if_locked'][0]) ?  $custom_fields['hide_if_locked'][0] : false);
        $check_only = true;
        $is_locked = go_task_locks ( $term_id, $check_only, $user_id , 'Map', $custom_fields);

        if($is_locked){

            if(in_array($hide_if_locked, array('hide'))){
                if($is_admin){
                    $prefix = 'Hidden by lock: ';
                }else {
                    continue;
                }
            }
        }


        if($taxonomy === 'store_types'){
            $is_hidden = get_term_meta( $term_id, 'go_hide_store_cat', true );
           // $is_hidden = (isset($term_custom['go_hide_store_cat'][0]) ? $term_custom['go_hide_store_cat'][0] : null);
        }
        else{
            $is_hidden = get_term_meta( $term_id, 'go_hide_map', true );
        }

        if($is_hidden){
            if($is_admin){
                $prefix = 'Hidden: ';
            }else {
                continue;
            }
        }

        $class = '';
        if($map_id  ==  $term_id){
            $class = 'current';
        }
        echo "
                <div id='mapLink_$term_id' class='mapLink $class' onclick=go_show_map($term_id) data-term_id='$term_id' >
                <span class=''>$prefix $tax_term_map->name</span></div>";

        // }
    }
    if($is_admin){
        echo "
                <div class='mapLink '  >
                    <span class='go_edit_frontend action_icon' data-new_parent_term='true'><a><i class='far fa-plus-circle'></i></a></span>
                </div>";
    }
    echo"</div></div></div></div> ";
}

/**
 *
 */
function go_update_map_order() {

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_update_map_order' ) ) {
        echo "refresh";
        die( );
    }

    if(empty($_POST) || !isset($_POST)) {
        ajaxStatus('error', 'Nothing to update.');
    } else {
        $terms = $_POST['terms'];
        $i = 0;
        foreach($terms as $term){
            $i++;
            update_term_meta(intval($term), 'go_order', $i);
        }
        //$map_id = $_POST['map_id'];
        //go_reset_map_transient($map_id);
        echo "success";
        die();
    }
}

/**
 *
 */
function go_update_chain_order() {

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_update_chain_order' ) ) {
        echo "refresh";
        die( );
    }

    if(empty($_POST) || !isset($_POST)) {
        ajaxStatus('error', 'Nothing to update.');
    } else {
        $terms = $_POST['terms'];
        $taxonomy = $_POST['taxonomy'];
        $i = 0;
        foreach($terms as $term){
            $i++;
            update_term_meta(intval($term), 'go_order', $i);
        }

        if(in_array($taxonomy, array('task_chains', 'store_types'))) {
            $map_id = $_POST['map_id'];
            go_reset_map_transient($map_id, $taxonomy);
        }else{

        }
        echo "success";
        die();
    }
}

/**
 *
 */
function go_update_task_order() {
    $is_admin_user = go_user_is_admin();
    if(!$is_admin_user){
        echo "not admin";
        die();
    }

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_update_task_order' ) ) {
        echo "refresh";
        die( );
    }

    if(empty($_POST) || !isset($_POST)) {
        ajaxStatus('error', 'Nothing to update.');
    } else {
        $tasks = $_POST['tasks'];
        $chain_id = $_POST['chain_id'];
        $taxonomy = $_POST['taxonomy'];
        $i = 0;
        $lock_info = array();
        foreach($tasks as $task){
            $task_id = $task[0];
            $nested = $task[1];
            $i++;

            if($taxonomy === 'task_chains') {
                update_post_meta(intval($task_id), 'go-location_map_options_nested', $nested);
                update_post_meta(intval($task_id), 'go-location_map_loc', $chain_id);
                wp_set_post_terms($task_id, array($chain_id), 'task_chains', false);
                update_post_meta(intval($task_id), 'go-location_map_order_item', $i);
                $user_id = get_current_user_id();
                $task_is_locked = go_task_locks($task_id, true, $user_id, false);
            }
            else if ($taxonomy === 'store_types'){
                $task_is_locked = false;
                //update_post_meta(intval($task_id), 'go-location_map_options_nested', $nested);
                update_post_meta(intval($task_id), 'go-store-location_store-sec_loc', $chain_id);
                wp_set_post_terms($task_id, array($chain_id), 'store_types', false);
                update_post_meta(intval($task_id), 'go-store-location_store_item', $i);
            }
            $key = 'go_post_data_' . $task_id;
            go_delete_transient($key);

            $task_info = array();
            $task_info[] = $task_id;
            $task_info[] = $task_is_locked;
            $lock_info[] = $task_info;
        }

        $key = 'go_get_chain_posts_' . $chain_id;
        go_delete_transient($key);

        echo json_encode(
            array(
                'json_status' => 302,
                'lock_info' => $lock_info,
            )
        );
        die();
    }
}


/**
 *
 */
function go_quick_edit(){
    //check_ajax_referer( 'go_clipboard_messages' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_quick_edit' ) ) {
        echo "refresh";
        die( );
    }

    if ( ! go_user_is_admin() ) {
        wp_die( __( 'refresh' ) );
    } else {


        $post_id = (isset($_POST['post_id']) ?  $_POST['post_id'] : null);
        $post_title = (isset($_POST['title']) ?  $_POST['title'] : null);
        $nested = (isset($_POST['nested']) ?  $_POST['nested'] : null);
        $optional = (isset($_POST['optional']) ?  $_POST['optional'] : null);
        $hidden = (isset($_POST['hidden']) ?  $_POST['hidden'] : null);
        $taxonomy = (isset($_POST['taxonomy']) ?  $_POST['taxonomy'] : null);


        $term_id = (isset($_POST['term_id']) ?  $_POST['term_id'] : null);
        $term_title = (isset($_POST['term_title']) ?  $_POST['term_title'] : null);
        $is_pod = (isset($_POST['pod_checkbox']) ?  $_POST['pod_checkbox'] : null);
        $locked_prev_checkbox = (isset($_POST['locked_prev_checkbox']) ?  $_POST['locked_prev_checkbox'] : null);

        if(!empty($post_id)) {
            if (!empty($post_title)) {
                $post = array(
                    'ID' => esc_sql($post_id),
                    'post_title' => wp_strip_all_tags($post_title)
                );
                wp_update_post($post, true);
            }

            if($taxonomy === 'task_chains') {

                if (!empty($nested)) {
                    if ($nested === 'false') {
                        $last_nested_value = get_post_meta($post_id, 'go-location_map_options_nested');
                        $last_nested_value = (isset($last_nested_value[0]) ? $last_nested_value[0] : false);
                        if ($last_nested_value) {
                            $term_id = get_post_meta(intval($post_id), 'go-location_map_loc');
                            $post_ids = go_get_chain_posts($term_id, $taxonomy, true);
                            $new_order = array();
                            $in_nest = false;
                            foreach ($post_ids as $this_post_id) {
                                $is_nested = get_post_meta($this_post_id, 'go-location_map_options_nested');
                                $is_nested = (isset($is_nested[0]) ? $is_nested[0] : false);
                                if ($is_nested) {
                                    if ($post_id == $this_post_id) {
                                        $in_nest = true;
                                    } else {
                                        $new_order[] = $this_post_id;
                                    }
                                } else {
                                    if ($in_nest) {
                                        $in_nest = false;
                                        $new_order[] = $post_id;
                                    }
                                    $new_order[] = $this_post_id;
                                }
                            }

                            $i = 0;
                            foreach ($new_order as $task) {
                                $i++;
                                update_post_meta(intval($task), 'go-location_map_order_item', $i);
                            }
                        }
                    }
                }

                if (!empty($nested)) {
                    if ($nested === 'true') {
                        $nested = 1;
                    } else {
                        $nested = 0;
                    }
                    update_post_meta($post_id, 'go-location_map_options_nested', $nested);
                }

                if (!empty($optional)) {
                    if ($optional === 'true') {
                        $optional = 1;
                    } else {
                        $optional = 0;
                    }
                    update_post_meta($post_id, 'go-location_map_options_optional', $optional);
                }

                if (!empty($hidden)) {
                    if ($hidden === 'true') {
                        $hidden = 1;
                    } else {
                        $hidden = 0;
                    }
                    update_post_meta($post_id, 'go-location_map_options_hidden', $hidden);
                }
            }
            else{
                if (!empty($hidden)) {
                    if ($hidden === 'true') {
                        $hidden = 1;
                    } else {
                        $hidden = 0;
                    }
                    update_post_meta($post_id, 'go-go_hide_store_cat', $hidden);
                }
            }
            $key = 'go_post_data_' . $post_id;
            go_delete_transient($key);
        }
        else if(!empty($term_id)){
            if (!empty($term_title)){
                $term = array(
                    'name' => wp_strip_all_tags($term_title)
                );
                wp_update_term($term_id, $taxonomy, $term);
            }

            if($taxonomy === 'task_chains') {
                if (!empty($is_pod)) {
                    if ($is_pod === 'true') {
                        $is_pod = 1;
                    } else {
                        $is_pod = 0;
                    }
                    update_term_meta($term_id, 'pod_toggle', $is_pod);
                }

                if (!empty($locked_prev_checkbox)) {
                    if ($locked_prev_checkbox === 'true') {
                        $locked_prev_checkbox = 1;
                    } else {
                        $locked_prev_checkbox = 0;
                    }
                    update_term_meta($term_id, 'locked_by_previous', $locked_prev_checkbox);
                }

                if (!empty($hidden)) {
                    if ($hidden === 'true') {
                        $hidden = 1;
                    } else {
                        $hidden = 0;
                    }
                    update_term_meta($term_id, 'go_hide_map', $hidden);
                }
            }
            else if ($taxonomy === 'store_types'){
                if (!empty($hidden)) {
                    if ($hidden === 'true') {
                        $hidden = 1;
                    } else {
                        $hidden = 0;
                    }
                    update_term_meta($term_id, 'go_hide_store_cat', $hidden);
                }
            }else{
                if (!empty($hidden)) {
                    if ($hidden === 'true') {
                        $hidden = 1;
                    } else {
                        $hidden = 0;
                    }
                    update_term_meta($term_id, 'go_hidden', $hidden);
                }
            }

            $key = 'go_term_data_' . $term_id;
            go_delete_transient($key);
        }
    }

    if(in_array($taxonomy, array('task_chains', 'store_types'))) {
        go_make_single_map(true,null, true, $taxonomy);
    }
    else if($taxonomy === 'go_badges'){
        go_stats_badges_list(true, 'edit');
    }
    else if($taxonomy === 'user_go_groups'){
        go_stats_groups_list(true, 'edit');
    }

    die();
}

/**
 * @param null $post_id
 * @param bool $skip_ajax
 */
function go_edit_frontend($post_id = null , $skip_ajax = true){
    //check_ajax_referer( 'go_clipboard_messages' );

    if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_edit_frontend')) {
        echo "refresh";
        die();
    }

    if (!go_user_is_admin()) {
        wp_die(__('refresh'));
    } else {
    }
    $map_url = get_option('options_go_locations_map_map_link');
    $go_map_link = (string)$map_url;
    //$go_map_link = get_permalink(get_page_by_path($go_map_link));
    $go_map_link = get_site_url(null, $go_map_link);


    $go_store_link = get_option('options_go_store_store_link');
    //$go_store_link = get_permalink(get_page_by_path($go_store_link));
    $go_store_link = get_site_url(null, $go_store_link);
    if(empty($post_id)) {
        $post_id = $_REQUEST['post_id'];
    }

    $term_id = $_REQUEST['tag_ID'];
    $taxonomy = $_REQUEST['taxonomy'];
    $term_obj = get_term($term_id);
    $new_parent_term = $_REQUEST['new_parent_term'];
    $new_child_term = $_REQUEST['new_child_term'];
    $new_store_item = $_REQUEST['new_store_item'];
    $settings = $_REQUEST['settings'];
    $group = $_REQUEST['group'];
    $title = $_REQUEST['title'];
    $url = $_REQUEST['url'];

    if (!empty($post_id)) {
        acf_form(array(
            'post_id' => $post_id,
            'post_title' => true,
            'post_content' => false,
            'return' => $url
        ));

        $key = 'go_post_data_' . $post_id;
        go_delete_transient($key);
    }
    else if (!empty($new_store_item)) {
        acf_form(array(
            'post_id' => 'new_post',
            'post_title' => true,
            'post_content' => false,
            'return' => $url,
            'new_post'		=> array(
                'post_type'		=> 'go_store',
                'post_status'	=> 'publish'
            )
        ));

        //$key = 'go_post_data_' . $post_id;
       // go_delete_transient($key);
    }
    else if (!empty($term_id)) {//editing a term
        //acf_form_head();
        if($taxonomy === 'store_types'){
            $field_groups = array('group_5e8bcca1bbe9e', 'group_5e37ba0ceec3a');
        }
        else if ($taxonomy === 'task_chains') {
            $field_groups = array('group_5e83393da1ab2', 'group_5e35978c81de6');
        }else if ($taxonomy === 'go_badges') {
            $field_groups = array('group_5e8d46a8e03fc', 'group_5e37cdd9253d4');
        }
        else if ($taxonomy === 'user_go_groups') {
            $field_groups = array('group_5e8d46bd3ef4a', 'group_5e389128bfb72');
        }
        $acf_form_args = array(
            //'id' => 'technic_edit_form',
            'post_id' => $term_obj,
            //'form' => true,
            'post_title' => false,
            'field_groups' => $field_groups,
            //'fields' => array('field_5e833944130e4'),
            'return' => $url
        );
        acf_form($acf_form_args);

        $key = 'go_term_data_' . $term_id;
        go_delete_transient($key);
    }
    else if (!empty($new_parent_term)) {
        if($taxonomy === 'store_types'){
            $field_groups = array('group_5e8bcca1bbe9e', 'group_5e37ba0ceec3a');
        }
        else if ($taxonomy === 'task_chains') {
            $field_groups = array('group_5e83393da1ab2', 'group_5e35978c81de6');
        }else if ($taxonomy === 'go_badges') {
            $field_groups = array('group_5e8d46a8e03fc', 'group_5e37cdd9253d4');
        }
        else if ($taxonomy === 'user_go_groups') {
            $field_groups = array('group_5e8d46bd3ef4a', 'group_5e389128bfb72');
        }

        acf_form(array(
            'post_id' => 'new_post',
            'post_title' => false,
            'post_content' => false,
            'return' => $url,
            'field_groups' => $field_groups,
            'new_post' => array(
                'post_type' => $taxonomy,
                'post_status' => 'publish'
            )
        ));
    }
    else if (!empty($new_child_term)) {
        if($taxonomy === 'store_types'){
            $field_groups = array('group_5e8bcca1bbe9e', 'group_5e37ba0ceec3a');
        }
        else if ($taxonomy === 'task_chains') {
            $field_groups = array('group_5e83393da1ab2', 'group_5e35978c81de6');
        }else if ($taxonomy === 'go_badges') {
            $field_groups = array('group_5e8d46a8e03fc', 'group_5e37cdd9253d4');
        }
        else if ($taxonomy === 'user_go_groups') {
            $field_groups = array('group_5e8d46bd3ef4a', 'group_5e389128bfb72');
        }
            acf_form(array(
            'post_id' => 'new_post',
            'post_title' => false,
            'post_content' => false,
            'return' => $url,
            'field_groups' => $field_groups,
            'new_post' => array(
                'post_type' => $taxonomy,
                'post_status' => 'publish'
            )
        ));
    }
    else if (!empty($settings)) {
        $field_groups = array($group);
        acf_form(array(
            'post_id' => 'options',
            'post_title' => false,
            'post_content' => false,
            'field_groups' => $field_groups,
            'html_before_fields' => '<h2>'.$title.'</h2>',
            'return' => $url,
        ));
    }

    die();

}


/**
 *
 */
function go_trash_post(){
    //check_ajax_referer( 'go_clipboard_messages' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_trash_post' ) ) {
        echo "refresh";
        die( );
    }

    if ( ! go_user_is_admin() ) {
        wp_die( __( 'refresh' ) );
    } else {

        $post_id = $_REQUEST['post_id'];
        $term_id = $_REQUEST['term_id'];
        $children = $_REQUEST['children'];
        $children_terms = $_REQUEST['children_terms'];
        $taxonomy = $_REQUEST['taxonomy'];
        if(!empty($post_id)) {
            wp_trash_post( $post_id );

            $key = 'go_post_data_' . $post_id;
            go_delete_transient($key);
        }

        if(!empty($term_id)) {


            $term = get_term($term_id, $taxonomy);
            //Get the parent object
            if($term) {
                $termParent = ($term->parent == 0) ? $term : get_term($term->parent, $taxonomy);
                //GET THE ID FROM THE MAP OBJECT
                $map_id = $termParent->term_id;
            }

            wp_delete_term( $term_id, $taxonomy);

            $key = 'go_term_data_' . $term_id;
            go_delete_transient($key);

            if(in_array($taxonomy, array('task_chains', 'store_types'))) {
                $key = 'go_get_child_term_ids_' . $map_id;
                go_delete_transient($key);
            }
        }

        if(!empty($children_terms)) {
            foreach ($children_terms as $child_term){
                wp_delete_term( $child_term, $taxonomy);

                $key = 'go_term_data_' . $child_term;
                go_delete_transient($key);
            }
        }

        if(!empty($children)) {
            foreach ($children as $child){
                wp_trash_post($child);

                $key = 'go_post_data_' . $child;
                go_delete_transient($key);
            }
        }

        if(in_array($taxonomy, array('task_chains', 'store_types'))) {
            go_make_single_map(true,null, true, $taxonomy);
        }
        else if($taxonomy === 'go_badges'){
            go_stats_badges_list(true);
        }
        else if($taxonomy === 'user_go_groups'){
            go_stats_groups_list(true);
        }


        die();
    }



}

function go_update_badge_cat_order(){
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_update_badge_cat_order' ) ) {
        echo "refresh";
        die( );
    }

    if(empty($_POST) || !isset($_POST)) {
        ajaxStatus('error', 'Nothing to update.');
    } else {
        $terms = $_POST['terms'];
        $taxonomy = $_POST['taxonomy'];
        $i = 0;
        foreach($terms as $term){
            $i++;
            update_term_meta(intval($term), 'go_order', $i);
        }
        $map_id = $_POST['map_id'];
        go_reset_map_transient($map_id, $taxonomy);
        echo "success";
        die();
    }
}
