<?php



/**
 * @param $last_map_id
 * @param $reload
 * @param $user_id
 */
function go_make_single_map($last_map_id, $reload, $user_id = null)
{
    global $wpdb;
    if ($user_id == null) {
        $user_id = get_current_user_id();
        $task_links = true;
    } else {
        $task_links = false;
    }

    $is_admin = go_user_is_admin();
    $is_admin_any_other_blog = go_user_is_admin_on_any_other_blog();
    if (function_exists ( 'wu_is_active_subscriber' ) ) {
        $is_subscriber = wu_is_active_subscriber($user_id);
    }else{
        $is_subscriber = false;
    }

    //$go_loot_table_name = "{$wpdb->prefix}go_loot";
    $locked_user_id = get_current_user_id();
    //$custom_fields = get_term_meta($last_map_id);
    $map_data = go_term_data($last_map_id);
    $name = $map_data[0];
    $custom_fields = $map_data[1];
    $hide_if_locked = (isset($custom_fields['hide_if_locked'][0]) ? $custom_fields['hide_if_locked'][0] : false);
    if (in_array($hide_if_locked, array('message', 'show'))) {
        $check_only = false;
    } else {
        $check_only = true;
    }
    ob_start();
    $is_locked = go_task_locks($last_map_id, $check_only, $locked_user_id, 'Map', $custom_fields);
    $locked_html = ob_get_contents();
    ob_end_clean();
    $map_prefix = "";


    $go_task_table_name = "{$wpdb->prefix}go_tasks";
    //wp_nonce_field( 'go_update_last_map');
    $last_map_object = get_term_by('id', $last_map_id, 'task_chains');//Query 1 - get the map
    //$show_hidden = go_show_hidden($user_id);
    $is_hidden = get_term_meta($last_map_id, 'go_hide_map', true);
    if ($is_hidden && !$is_admin) {
        //return;
        $last_map_id = '';//don't just return because need to print an empty wrapper
    } else if ($is_hidden && $is_admin) {
        $map_prefix = "Hidden: ";
    }


    //$is_logged_in = ! empty( $user_id ) && $user_id > 0 ? true : false;
    //$taxonomy_name = 'task_chains';

    $key = go_prefix_key('go_badge');
    $user_badges = get_user_meta($user_id, $key, false);
    if (empty($user_badges)) { //if there were no badges then create empty array
        $user_badges = array();
    } else {//else unserialize
        if (is_serialized($user_badges)) {
            $user_badges = unserialize($user_badges);
        }
    }

    if ($reload == false) {
        echo "<div id='mapwrapper' >";
    }
    if ($is_locked) {
        if (!$check_only) {
            echo "<h2>$map_prefix $name</h2>";
            $name = "";
            echo $locked_html;
        }

        if (!go_user_is_admin() && in_array($hide_if_locked, array('message', 'hide'))) {
            return;
        }
    }
    if (empty($last_map_id)) {
        echo "<div id='maps' data-mapid='$last_map_id' style='overflow: auto;'>";
    }
    else {


        $is_sortable = '';
        if (go_user_is_admin()) {
            $is_sortable = 'sortable';
        }
        echo "<div id='maps' data-mapid='$last_map_id' style='clear: both;' class='$is_sortable'>";

        echo "<span class='go_map_action_icons' style='position: absolute; right: 40px; z-index: 10'>";

        //show loot
        echo "<span style='z-index: 10; float: right;'>";
        echo "<a onclick='go_show_map_loot();' style='height: 0px; padding: 2px 5px;'> <i class='fas fa-chart-area'></i></a>";
        echo "</span>";

        if (go_user_is_admin()) {
            echo "<span style='z-index: 10; float: right;' class='tooltip_toggle'>";
            echo "<a onclick='go_disable_tooltips();' style='height: 0px; padding: 2px 5px;'> <i class='active fal fa-comment-alt-edit'></i><i style='display: none;' class='inactive fad fa-comment-alt-times'></i></a>";
            echo "</span>";

            $map_link = go_get_link_from_option('options_go_locations_map_map_link');
            $map_link = add_query_arg('map_id', $last_map_id, $map_link);
            echo go_copy_var_to_clipboard($map_link, 'Copy direct link to this map to clipboard.', true);

        }

        echo "</span>";


        $badge_id = get_term_meta($last_map_id, "pod_achievement", true);


        echo 	"<div id='map_$last_map_id' class='map'>";

        echo "<div class='ParentNav go_show_actions'>";


        //echo "<div class='map_title go_show_actions'></div>";
        //$admin_view = get_user_option('go_admin_view', $user_id);
        $admin_view = go_get_admin_view($user_id);


        if(($is_subscriber || $is_admin || $is_admin_any_other_blog) && ($admin_view === 'admin' || $is_admin_any_other_blog)){
            echo "<div class='actions_tooltip' style='display: none;'  onclick= 'event.stopPropagation();'>  ><div class='my_tooltip'><div class='go_actions_wrapper_flex'>";
            echo "<span class='tools task_tools'>";
            if(is_gameful()){
                do_action('gop_add_importer_icon', $last_map_id, 'term', $user_id, false);
            }
            if($is_admin) {
                $url = get_edit_term_link($last_map_id, 'task_chains', 'tasks');
                echo "<span><a href='{$url}'><i class='fas fa-edit'></i></a></span>";
            }
            echo "</span></div></div></div>";
        }

        go_make_map_dropdown('task_chains', $last_map_id);

        echo "</div>";
        //echo "$map_prefix $name</div>";

        //go_map_quest_badge($badge_id, $user_badges, true);
        if (intval($badge_id) > 0) {
            go_print_single_badge($badge_id, 'badge', $output = true, $user_id, 'go_map_badge');
        }

        echo "</div>";

		echo "<ul class='primaryNav'>"; //the main list of columns



        $term_ids = go_get_map_chain_term_ids($last_map_id);

        $map_xp = 0;
        $map_gold = 0;
        $map_health = 0;

        $map_my_xp = 0;
        $map_my_gold = 0;
        $map_my_health = 0;

        foreach ($term_ids as $term_id) { //the task chains --columns
            $chain_xp = 0;
            $chain_gold = 0;
            $chain_health = 0;

            $chain_my_xp = 0;
            $chain_my_gold = 0;
            $chain_my_health = 0;


            $term_data = go_term_data($term_id);
            $term_name = $term_data[0];
            $term_custom = $term_data[1];

            $is_hidden = (isset($term_custom['go_hide_map'][0]) ? $term_custom['go_hide_map'][0] : null);
            if ($is_hidden) {
                if (!$is_admin) {
                    continue;
                }
            }

            //$map_data = go_term_data($last_map_id);
            //$name = $term_data[0];
            //$custom_fields = $map_data[1];
            $hide_if_locked = (isset($term_custom['hide_if_locked'][0]) ? $term_custom['hide_if_locked'][0] : false);
            if (in_array($hide_if_locked, array('message', 'show'))) {
                $check_only = false;
            } else {
                $check_only = true;
            }
            ob_start();
            $is_locked = go_task_locks($term_id, $check_only, $locked_user_id, 'Map', $term_custom);
            $locked_html = ob_get_contents();
            ob_end_clean();
            if ($is_locked) {
                if (in_array($hide_if_locked, array('hide')) && !$is_admin) {
                    continue;
                }
            }

            //Get array of postIDs from transient--this also creates transients of each posts data if needed
            $go_post_ids = go_get_chain_posts($term_id, true);

            //echo "<li><p>$term_object->name";
            echo "<li class='go_task_chain' data-term_id='$term_id'><div class='go_task_chain_map_box go_show_actions'>";

            if(($is_subscriber || $is_admin || $is_admin_any_other_blog) && ($admin_view === 'admin' || $is_admin_any_other_blog)){

                echo "<div class='actions_tooltip' style='display: none;' onclick= 'event.stopPropagation();'><div class='my_tooltip'><div class='go_actions_wrapper_flex'>";
                echo "<span class='tools task_tools'>";
                if(is_gameful()){
                    do_action('gop_add_importer_icon', $term_id, 'term', $user_id, false);
                }
                if($is_admin) {
                    $url = get_edit_term_link($term_id, 'task_chains', 'tasks');
                    echo "<span><a href='{$url}'><i class='fas fa-edit'></i></a></span>";
                }
                echo "</span></div></div></div>";

            }

            echo "<p style='clear:both;' id='go_map_chain_title' class='title'> $term_name</p></div>";

            //START: The list of tasks in the chain
            if ($is_locked) {
                if (!$check_only) {
                    // echo "<h3>$term_name</h3>";
                    $name = "";
                    echo "<div class='go_task_pod_required'>" . $locked_html . "</div>";
                }

                if (in_array($hide_if_locked, array('message'))) {
                    //echo "</ul>";
                    continue;
                }
            }

            $is_pod = (isset($term_custom['pod_toggle'][0]) ? $term_custom['pod_toggle'][0] : null);

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
                            $task_name = get_option('options_go_tasks_name_plural'); //Q option
                        } else {
                            $task_name = get_option('options_go_tasks_name_singular'); //Q option
                        }

                        echo "<div class='go_task_pod_required'>Choose $pod_min $task_name to complete.</div>";
                    } else if ($pod_count > 0) {
                        if ($pod_min > 1) {
                            $task_name = get_option('options_go_tasks_name_plural'); //Q option
                        } else {
                            $task_name = get_option('options_go_tasks_name_singular'); //Q option
                        }

                        echo "<div class='go_task_pod_required'>Complete at least $pod_min $task_name.</div>";
                    }


                }

            }

            echo "<ul class='tasks connectedSortable' data-chain_id='$term_id'>"; //the task list in the column

            //If there are tasks
            $first = true;
            if (!empty($go_post_ids)) {

                //Query 3
                //Get User info for these tasks
                $id_string = implode(',', $go_post_ids);
                $user_tasks = $wpdb->get_results(
                    "SELECT *
						FROM {$go_task_table_name}
						WHERE uid = $user_id AND post_id IN ($id_string)
						ORDER BY last_time DESC"
                );
                $user_tasks = json_decode(json_encode($user_tasks), True);

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


                    $go_task_data = go_post_data($post_id); //0--name, 1--status, 2--permalink, 3--metadata
                    $task_name = $go_task_data[0];
                    $status = $go_task_data[1];
                    $task_link = $go_task_data[2];
                    $custom_fields = $go_task_data[3];

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
                    $stage_count = $custom_fields['go_stages'][0];//total stages

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


                    if ($custom_fields['bonus_switch'][0]) {
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
                    $task_is_locked = go_task_locks($post_id, true, $user_id, false, $custom_fields);

                    //$task_is_locked = false;
                    $unlock_message = '';
                    if ($task_is_locked === 'password') {
                        $unlock_message = '<div><i class="fas fa-unlock"></i> Password</div>';
                        $task_is_locked = false;
                    }
                    else if ($task_is_locked === 'master password') {
                        $unlock_message = '<div><i class="fas fa-unlock"></i> Master Password</div>';
                        $task_is_locked = false;
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

                    if ($stage_count <= $status) {
                        $class = str_replace('reset', 'resetted', $class);
                        $task_color = 'done';
                    } else if ($task_is_locked) {
                        $task_color = 'locked';
                    } else {
                        $task_color = 'available';
                    }

                    //close previous task
                    //this is done here to check if the next one was nested before closing
                    if($first) {
                        $first = false;
                    }else{

                        if ($nested && !$first_nested) {//the previous task is done and this is the first nested, print the toggle
                            echo "</div></div>";//close the last task
                            echo "<span class='go_nested_hover'><div class='go_nested_toggle'><div class='nested_opaque'><div class='nested_icon'><i class='fas fa-caret-down'></i></div></div> </div>";
                            echo "<ul class='go_nested_list' style='display: none;'>";
                            $first_nested = true;
                            $last_was_nested = true;
                        } else if ($nested) {
                            echo "</div></li>";
                            $last_was_nested = true;
                        }else if (!$nested){
                            if ($last_was_nested){
                                echo "</div></li></ul></span></li>";
                            }else {
                                echo "</div></li>";
                            }
                            $first_nested = false;
                            $last_was_nested = false;
                        }


                    }


                    //echo "<li class='task_container'><div onclick='go_to_task()' class='task $task_color $nested_class $class $hidden_class'>";
                    if ($task_links === true) {//This is the regular map
                        $addClass = '';
                        //$data = '';
                        $onClick = 'go_to_task(event, "'.$task_link.'")';
                        //$onClick = '';
                        //echo "<a style='display: none;' href='$task_link'></a>";
                    } else {//this is the map on the clipboard
                        $addClass = 'go_blog_user_task';
                        //$data = "data-user_id='$user_id' data-post_id='$post_id';'";
                        $onClick = '';
                        //echo "<a style='display: none;' href='javascript:;' class='go_blog_user_task' data-user_id='$user_id' data-post_id='$post_id';'></a>";
                        //echo "<li class='$task_color $optional '><a href='$task_link'><span style='font-size: .8em;'>$bonus_task $task_name <br>$unlock_message</span>";
                    }

                    echo "<li class='task_container'>";
                    echo "<div onclick='$onClick' data-user_id='$user_id' data-post_id='$post_id' class='task no_redirect $task_color $nested_class $class $addClass $hidden_class task_id_$post_id'>";

                    echo "<div class='go_map_quest_hover go_show_actions'>";


                    if(($is_subscriber || $is_admin || $is_admin_any_other_blog) && ($admin_view === 'admin' || $is_admin_any_other_blog)){

                        echo "<div class='actions_tooltip no_redirect' onclick= 'event.stopPropagation();' style='display: none;'><div class='my_tooltip'><div class='go_actions_wrapper_flex no_redirect'>";

                        echo "<span class='tools task_tools no_redirect'>";

                        if(is_gameful()){
                            do_action('gop_add_importer_icon', $post_id, 'post', $user_id, false);
                        }
                        if($is_admin) {
                            $url = get_edit_post_link($post_id);
                            echo "<span class='no_redirect'><a href='{$url}'><i class='fas fa-edit'></i></a></span>";
                            echo "<span class='go_quick_edit_show' data-task_id='$post_id'><a><i class='fas fa-bolt'></i></a></span>";
                            echo "<span class='go_quest_reader_lightbox_button' data-post_id='{$post_id}' data-stage='all'><a><i class='fas fa-book-open'></i></a></span>";
                            echo "<span class='go_quests_frontend' data-post_id='{$post_id}'><a><i class='fas fa-clipboard-list'></i></a></span>";
                        }
                        echo "</span>";
                        if($is_admin){
                            ?>
                            <span class="quickedit_form_container" style="display: none;">
                                <span class="quickedit_form" data-post_id='<?php echo $post_id; ?>'>
                                    <form style="display: block;">

                                        <input type="text" class="post_title" name="post_title" size="30" value="<?php echo $task_name; ?>">
                                        <br>

                                        <span class="checkbox" style="padding: 10px;">
                                            <label for="hidden">  Hidden </label>
                                            <input type="checkbox" class="hidden_checkbox" name="hidden" <?php if($marked_hidden){echo 'checked'; } ?>>
                                        </span>
                                        <span class="checkbox" style="padding: 10px;">
                                            <label for="nested">  Nested </label>
                                            <input type="checkbox" class="nested_checkbox" name="nested" <?php if($nested){echo 'checked'; }?>>
                                        </span>
                                        <span class="checkbox" style="padding: 10px;">
                                            <label for="optional">  Optional </label>
                                            <input type="checkbox" class="optional_checkbox" name="optional" <?php if($optional){echo 'checked'; } ?>>
                                        </span>

                                    </form>
                                </span>
                            </span>

                            <?php
                        }
                        echo "</div></div></div>";


                    }

                    echo "<div class='title'>$task_name <br>$unlock_message</div>";


                    //<a href="javascript:;" class="go_blog_user_task" data-UserId="'.$user_id.'" onclick="go_blog_user_task('.$user_id.', '.$post_id.');">

                    echo "<div class='loot_info' >";
                    go_map_loot($xp_loot, $gold_loot, $health_loot, $my_xp, $my_gold, $my_health);

                    echo "</div>";


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
                            '<div class="bonus_progress">Bonus: ' .
                            $bonus_status . ' / ' . $repeat_max . '</div>' .
                            '</div>';
                        echo $progress_bar;
                    }
                }

                if ($last_was_nested){
                    echo "</div></li></ul></li>";
                }else {
                    echo "</div></li>";
                }


            }
            else{
                //echo "<li></li>";//for the sortable
            }

            echo "</ul>";

            //badge for chains
            $badge_ids = get_term_meta($term_id, "pod_achievement", true);


            if (!empty($badge_ids)) {
                go_map_badge($badge_ids, $user_badges, true, $user_id);
            }


            echo "<div class='loot_info go_chain_loot_info' style='font-size: .7em;  background-color: rgba(255, 255, 255, 0.7);'>";
            go_map_loot($chain_xp, $chain_gold, $chain_health, $chain_my_xp, $chain_my_gold, $chain_my_health);


            echo "</div>";
        }

        echo "</ul>"; //closes the main list of columns PrimaryNav
        /*
         * //Show map at end of map in own colum
        //badge for map
        $badge_ids = get_term_meta($last_map_id, "pod_achievement", true);
        if (!empty($badge_ids)) {
            go_map_badge($badge_ids, $user_badges, true);
        }*/


        echo "</div>";
    }
    echo "<div class='loot_info go_map_loot_info' style='font-size: .8em;  background-color: rgba(255, 255, 255, 0.7); display: none;'>";
    go_map_loot($map_xp, $map_gold, $map_health, $map_my_xp, $map_my_gold, $map_my_health);

    echo "</div>";

    echo "</div>";
    if ($reload == false) {echo "</div>";}
}

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
 *
 */
function go_make_map_dropdown($taxonomy, $last_map_id, $user_id = null){
    /* Get all task chains with no parents--these are the top level on the map.  They are chains of chains (realms). */
    //$taxonomy = 'task_chains';
    /*$term_args0=array(
          'hide_empty' => false,
          'order' => 'ASC',
          'parent' => '0'
    );*/
    global $wpdb;

    $is_admin = go_user_is_admin();
    $title = 'Choose';
    if($taxonomy = 'task_chains'){
        $map_data = go_term_data($last_map_id);
        $name = $map_data[0];

        $map_prefix = "";



        $is_hidden = get_term_meta($last_map_id, 'go_hide_map', true);
        if ($is_hidden && !$is_admin) {

        } else if ($is_hidden && $is_admin) {
            $map_prefix = "Hidden: ";
        }

        $title = $map_prefix." ".$name;
    }
    //$tax_terms_maps = get_terms($taxonomy,$term_args0);
    $is_admin = go_user_is_admin();
    $tax_terms_maps = go_get_terms_ordered($taxonomy, '0');

    if($user_id != null){


        $user_name = go_get_fullname($user_id);
        echo "<div style='justify-content:center; align-items:center; display: flex;'><div class='go_map_prev' style='display: none; padding-right: 30px;'>Prev</div><h1 id='go_map_user' data-uid='".intval($user_id)."' >Viewing map as ". $user_name . "</h1><div class='go_map_next' style='display: none; padding-left: 30px;'>Next</div></div>";

    }

    echo"
	<div id='sitemap' style='text-align:center;'>   
    <div class='dropdown' onclick='go_map_dropDown()'>
      <p  class='dropbtn title'>$title<span id='go_drop_arrow'><i class='fas fa-caret-down'></i></span></p>
      <div id='go_Dropdown' class='dropdown-content .dropdown-menu'>";


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
        $is_hidden = get_term_meta( $term_id, 'go_hide_map', true );
        if($is_hidden){
            if($is_admin){
                $prefix = 'Hidden: ';
            }else {
                continue;
            }
        }
        echo "
                <div id='mapLink_$term_id' >
                <a onclick=go_show_map($term_id)>$prefix $tax_term_map->name</a></div>";

        // }
    }
    echo"</div></div></div> ";
}

function go_update_chain_order() {
    //check_ajax_referer( 'go_update_last_map' );

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
        $i = 0;
        foreach($terms as $term){
            $i++;
            update_term_meta(intval($term), 'go_order', $i);
        }
        $map_id = $_POST['map_id'];
        go_reset_map_transient($map_id);
        echo "success";
        die();
    }
}

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
        $i = 0;
        $lock_info = array();
        foreach($tasks as $task){
            $task_id = $task[0];
            $nested = $task[1];
            $i++;
            update_post_meta(intval($task_id), 'go-location_map_options_nested', $nested);
            update_post_meta(intval($task_id), 'go-location_map_loc', $chain_id);
            wp_set_post_terms( $task_id, array($chain_id), 'task_chains', false );
            update_post_meta(intval($task_id), 'go-location_map_order_item', $i);
            $key = 'go_post_data_' . $task_id;
            go_delete_transient($key);
            $user_id = get_current_user_id();
            $task_is_locked = go_task_locks($task_id, true, $user_id, false);
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



function go_quick_edit_task(){
    //check_ajax_referer( 'go_clipboard_messages' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_quick_edit_task' ) ) {
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

        if(!empty($post_title)) {
            $post = array(
                'ID' => esc_sql($post_id),
                'post_title' => wp_strip_all_tags($post_title)
            );
            wp_update_post($post, true);
        }

        if(!empty($nested)) {
            if ($nested === 'false') {
                $last_nested_value = get_post_meta($post_id, 'go-location_map_options_nested');
                $last_nested_value = (isset($last_nested_value[0]) ?  $last_nested_value[0] : false);
                if ($last_nested_value){
                    $term_id = get_post_meta(intval($post_id), 'go-location_map_loc');
                    $post_ids = go_get_chain_posts($term_id, true);
                    $new_order = array();
                    $in_nest = false;
                    foreach ($post_ids as $this_post_id){
                        $is_nested = get_post_meta($this_post_id, 'go-location_map_options_nested');
                        $is_nested = (isset($is_nested[0]) ?  $is_nested[0] : false);
                        if($is_nested){
                            if($post_id == $this_post_id ){
                                $in_nest = true;
                            }else{
                                $new_order[] = $this_post_id;
                            }
                        }else{
                            if($in_nest){
                                $in_nest = false;
                                $new_order[] = $post_id;
                            }
                            $new_order[] = $this_post_id;
                        }
                    }

                    $i = 0;
                    foreach ($new_order as $task){
                        $i++;
                        update_post_meta(intval($task), 'go-location_map_order_item', $i);
                    }
                }
            }
        }




        if(!empty($nested)) {
            if ($nested === 'true') {
                $nested = 1;
            } else {
                $nested = 0;
            }
            update_post_meta($post_id, 'go-location_map_options_nested', $nested);
        }

        if(!empty($optional)) {
            if ($optional === 'true') {
                $optional = 1;
            } else {
                $optional = 0;
            }
            update_post_meta($post_id, 'go-location_map_options_optional', $optional);
        }

        if(!empty($hidden)) {
            if ($hidden === 'true') {
                $hidden = 1;
            } else {
                $hidden = 0;
            }
            update_post_meta($post_id, 'go-location_map_options_hidden', $hidden);
        }



        $key = 'go_post_data_' . $post_id;
        go_delete_transient($key);


    }

    $user_id = get_current_user_id();
    $last_map_id = get_user_option('go_last_map', $user_id);
    go_make_single_map($last_map_id, true, $user_id);
    die();
}