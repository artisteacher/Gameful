<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/21/18
 * Time: 6:04 PM
 */

function go_get_task_names($uniqueTasks, $quest_count){


    $is_first = true;
    $task_title = "";
    $i = 0;
    foreach ($uniqueTasks as $task_id) {
        $i++;
        if (!$is_first && $quest_count > 2 && $i < $quest_count) {
            $task_title = $task_title . ", ";
        }
        if ($i == $quest_count && $quest_count > 1) {
            $task_title = $task_title . ", and ";
        }
        $task_title = $task_title . get_the_title($task_id);
        $is_first = false;
    }
    return $task_title;
}

function go_get_user_names($uniqueUsers, $user_count){
    $is_first = true;
    $user_name = "";
    $i = 0;
    foreach ($uniqueUsers as $user_id) {
        $i++;
        //$task_id = intval($task['task_id']);
        if (!$is_first && $user_count > 2 && $i < $user_count) {
            $user_name = $user_name . ", ";
        }
        if ($i == $user_count && $user_count > 1) {
            $user_name = $user_name . ", and ";
        }
        $user = get_userdata($user_id);
        $this_user_name = $user->first_name . ' ' . $user->last_name;
        if (empty($this_user_name) || $this_user_name == ' ') {
            $this_user_name = '';
            $user_name = $user->display_name;
        }
        $user_name = $user_name . $this_user_name;
        $is_first = false;
    }

    return $user_name;
}

function go_get_reset_mixed($reset_vars){

    $uniqueTasks = array_unique(array_map(function ($i) { return $i['task']; }, $reset_vars));
    //$quest_count = count($uniqueTasks);
    echo "<ul style='text-align: left;'>";

    foreach ($uniqueTasks as $task){
        echo "<li>";
        echo get_the_title($task) . ": ";
        $uniqueUsers = array();
        foreach ($reset_vars as $reset_var) {
          if ($reset_var['task'] == $task) {
              $uniqueUsers[] = $reset_var['uid'];
          }
        }
        $user_count = count($uniqueUsers);
        echo go_get_user_names($uniqueUsers, $user_count);
        echo "</li>";
    }

    echo "</ul>";

    //Logic
    //get all the

}

function go_create_admin_message (){
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_create_admin_message');
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_create_admin_message' ) ) {
        echo "refresh";
        die( );
    }

    $user_id = (isset($_POST['user_ids']) ?  $_POST['user_id'] : null);

    //$user_ids = $_POST['user_ids'];
    //$post_id = $_POST['post_id'];
    $message_type = $_POST['message_type'];
    //$user_ids = array_unique($user_ids);

    $reset_vars = (isset($_POST['reset_vars']) ?  $_POST['reset_vars'] : null);



    $type = '';
    if (empty($reset_vars) && empty($user_id)){
        $type = 'no_users';
        $title = 'No Users Selected';
        $message = "Please select a user from the list.";
    }
    else{

        $title = 'Send a Message';
        ob_start();
        if ($message_type == 'single_reset' || $message_type == 'multiple_reset' || $message_type == 'reset_stage'){
            $type = 'reset';



            $uniqueTasks = array_unique(array_map(function ($i) { return $i['task']; }, $reset_vars));
            $quest_count = count($uniqueTasks);

            $uniqueUsers = array_unique(array_map(function ($i) { return $i['uid']; }, $reset_vars));
            $user_count = count($uniqueUsers);

            //Get task custom name variable
            if ($quest_count > 1){
                $task_name = get_option('options_go_tasks_name_plural');
            } else{
                $task_name = get_option('options_go_tasks_name_singular');
            }
            $title = 'Reset '.  $task_name . ' <span class="tooltip" data-tippy-content="Resetting removes all loot and rewards. <br> <br>If the bonus loot had already been awarded, it is also removed and the user will not have another attempt." style="font-size: .6em;"> <span><i class="fa fa-info-circle"></i></span></span>';


            //Build message of what is being reset
            //logic:
            //message for single task and multiple users
            //message for multiple tasks and single user
            //message for multiple tasks and users
            ?>

            <div id="go_messages_container" class="go_reset_task_message">
                <form method="post">
            <?php

           //BUILD THE RESET MESSAGE WINDOW TOP WITH A LIST OF TASKS AND USERS
            if (($quest_count == 1 && $user_count >= 1) || ($quest_count >= 1 && $user_count == 1)) { //message for single task and/or single users
                $task_title = go_get_task_names($uniqueTasks, $quest_count);
                $user_name = go_get_user_names($uniqueUsers, $user_count);
                ?>
                <table>
                    <tr valign="top">
                        <?php

                        echo "<th scope=\"row\" style='text-align: left'>" . $task_name . ":</th>";
                        ?>
                        <td>
                            <div style="text-align: left;">
                                <?php
                                echo $task_title;
                                ?>
                            </div>
                        </td>

                    </tr>
                    <tr valign="top">
                        <?php
                        if ($user_count > 1) {
                            echo "<th scope=\"row\" style='text-align: left'>Users:</th>";
                        } else {
                            echo "<th scope=\"row\" style='text-align: left'>User:</th>";
                        }
                        ?>

                        <td>
                            <div style="text-align: left;">
                                <?php
                                echo $user_name;
                                ?>
                            </div>
                        </td>
                    </tr>
                </table>
                <?php
            }
            else {//message for multiple tasks and users



                ?>
                            <div>
                                <p>Warning: Multiple Values for Users and <?php echo $task_name ?> were selected. Please double check that you want to reset all these <?php echo $task_name ?>.</p>
                            </div>

                <?php
                go_get_reset_mixed($reset_vars);

            }

            ?>
            <div id="go_messages" style="display:flex;">
                <br>

                <div id="messages_form" style="text-align: left;">

                    <input id="go_custom_message_toggle" type="checkbox">
                    <label for="go_custom_message_toggle">Add a Custom Message </label><span class="tooltip" data-tippy-content="Users will be notified of reset. You can add a custom message."><span><i class="fa fa-info-circle"></i></span> </span>
                    <table id="go_custom_message_table" class="form-table" style="display: none;">
                        <tr valign="top">
                            <th scope="row">Message</th>
                            <td><textarea name="message" class="widefat" cols="50" rows="5"></textarea></td>
                        </tr>

                    </table>

                    <br>
                    <input id="go_additional_penalty_toggle" type="checkbox">
                    <label for="go_additional_penalty_toggle">Assign Consequence </label><span class="tooltip" data-tippy-content="In addition to removing loot that had been awarded, you may assign an additional penalty."><span><i class="fa fa-info-circle"></i></span></span>


                    <div id="go_loot_table" class="go-acf-field go-acf-field-group go_penalty_table" data-type="group" style="display:none;">

                        <div class="go-acf-input">
                            <div id="go_penalty_table" class="go-acf-fields -top -border" >
                                <div class="go-acf-field go-acf-field-group go-acf-hide-label go-acf-no-padding go-acf-table-no-border"
                                     data-name="reward_toggle" data-type="group">
                                    <div class="go-acf-input">
                                        <table class="go-acf-table ">
                                            <thead>
                                            <tr>
                                                <th>
                                                    <div class="go-acf-th">
                                                        <label>-<?php echo go_get_loot_short_name('xp');?></label></div>
                                                </th>
                                                <th>
                                                    <div class="go-acf-th">
                                                        <label>-<?php echo go_get_loot_short_name('gold');?></label></div>
                                                </th>
                                                <th>
                                                    <div class="go-acf-th">
                                                        <label>-<?php echo go_get_loot_short_name('health');?></label></div>
                                                </th>

                                            </tr>


                                            </thead>
                                            <tbody>


                                            <tr class="go-acf-row">
                                                <td class="go-acf-field go-acf-field-number go_reward go_xp  data-name="
                                                    xp
                                                " data-type="number">
                                                <div class="go-acf-input">
                                                    <div class="go-acf-input-wrap"><input name="xp" type="number"
                                                                                          value="" min="0" step="1" placeholder="0" oninput="validity.valid||(value='');" class="xp_messages go_pink">
                                                    </div>
                                                </div>
                                                </td>
                                                <td class="go-acf-field go-acf-field-number go_reward go_gold"
                                                    data-name="gold" data-type="number">
                                                    <div class="go-acf-input">
                                                        <div class="go-acf-input-wrap"><input name="gold" type="number"
                                                                                              value="" min="0"
                                                                                              step=".01" placeholder="0" oninput="validity.valid||(value='');" class="gold_messages go_pink"></div>
                                                    </div>
                                                </td>
                                                <td class="go-acf-field go-acf-field-number go_reward go_health "
                                                    data-name="health" data-type="number">
                                                    <div class="go-acf-input">
                                                        <div class="go-acf-input-wrap"><input name="health"
                                                                                              type="number" value=""
                                                                                              min="0" step=".01" placeholder="0" oninput="validity.valid||(value='');" class="health_messages go_pink"></div>
                                                    </div>
                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                    <p></p>
                                    <div class="go-acf-input">
                                        <table class="go-acf-table">
                                            <thead>
                                            <tr>
                                                <th>
                                                    <div class="go-acf-th">
                                                        <label>Remove <?php echo get_option('options_go_badges_name_plural');?></label></div>
                                                </th>
                                                <th>
                                                    <div class="go-acf-th">
                                                        <label>Remove Groups</label></div>
                                                </th>

                                            </tr>

                                            </thead>
                                            <tbody>

                                            <tr class="go-acf-row">
                                                <td class="go-acf-field go-acf-field-true-false go_reward go_badges"
                                                    data-name="gold" data-type="true_false" class="go_pink">
                                                    <?php go_make_tax_select('go_badges', "messages_"); ?>

                                                </td>
                                                <td class="go-acf-field go-acf-field-true-false go_reward go_gold"
                                                    data-name="gold" data-type="true_false" class="go_pink">
                                                    <?php go_make_tax_select('user_go_groups', "messages_"); ?>
                                                </td>

                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>



            </div>


            </div></form>
        <?php

        }
        else {//this is not a reset, so build a regular message
            $type = 'message';
            $uniqueUsers = array_unique(array_map(function ($i) { return $i['uid']; }, $reset_vars));
            $user_count = count($uniqueUsers);
            ?>


            <div id="go_messages_container">
                <form method="post">
                    <div id="go_messages" style="display:flex;">

                        <div id="messages_form">
                            <div style="float:left; padding: 20px 0;"><?php go_messages_canned(); ?></div>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">To</th>
                                    <td style="width: 100%;">
                                        <div>
                                            <?php
                                            $is_first = true;
                                            foreach ($uniqueUsers as $user_id) {
                                                $user = get_userdata($user_id);
                                                if (!$is_first) {
                                                    echo ", ";
                                                }
                                                $user_fullname = $user->first_name . ' ' . $user->last_name;
                                                if (empty($user_fullname) || $user_fullname = ' ') {
                                                    $user_fullname = $user->display_name;
                                                }
                                                echo $user_fullname;
                                                $is_first = false;
                                            }
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Title</th>
                                    <td style="width: 100%;"><input class="go_messages_title_input" type="text" name="title" value="" style="width: 100%;"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Message</th>
                                    <td><textarea class="go_messages_message_input" name="message" class="widefat" cols="50" rows="5"></textarea></td>
                                </tr>

                            </table>
                            <div id="go_loot_table" class="go-acf-field go-acf-field-group" data-type="group">
                                <div class="go-acf-input">
                                    <div class="go-acf-fields -top -border">
                                        <div class="go-acf-field go-acf-field-group go-acf-hide-label go-acf-no-padding go-acf-table-no-border"
                                             data-name="reward_toggle" data-type="group">
                                            <div class="go-acf-input">
                                                <table class="go-acf-table">
                                                    <thead>
                                                    <tr>
                                                        <th>
                                                            <div class="go-acf-th">
                                                                <label><?php echo go_get_loot_short_name('xp');?></label></div>
                                                        </th>
                                                        <th>
                                                            <div class="go-acf-th">
                                                                <label><?php echo go_get_loot_short_name('gold');?></label></div>
                                                        </th>
                                                        <th>
                                                            <div class="go-acf-th">
                                                                <label><?php echo go_get_loot_short_name('health');?></label></div>
                                                        </th>

                                                    </tr>


                                                    </thead>
                                                    <tbody>
                                                    <tr class="go-acf-row">
                                                        <td class="go-acf-field go-acf-field-true-false go_reward go_xp"
                                                            data-name="xp" data-type="true_false">
                                                            <div class="go-acf-input">
                                                                <div class="go-acf-true-false">
                                                                    <input value="0" type="hidden">
                                                                    <label>
                                                                        <input name="xp_toggle" type="checkbox" value="1"
                                                                               class="go-acf-switch-input go_messages_toggle_input xp_toggle_messages">
                                                                        <div class="go-acf-switch"><span class="go-acf-switch-on"
                                                                                                         style="min-width: 36px;">+</span><span
                                                                                    class="go-acf-switch-off"
                                                                                    style="min-width: 36px;">-</span>
                                                                            <div class="go-acf-switch-slider"></div>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="go-acf-field go-acf-field-true-false go_reward go_gold"
                                                            data-name="gold" data-type="true_false">
                                                            <div class="go-acf-input">
                                                                <div class="go-acf-true-false">
                                                                    <input value="0" type="hidden">
                                                                    <label>
                                                                        <input name="gold_toggle" type="checkbox"
                                                                               class="go-acf-switch-input go_messages_toggle_input gold_toggle_messages">
                                                                        <div class="go-acf-switch"><span class="go-acf-switch-on"
                                                                                                         style="min-width: 36px;">+</span><span
                                                                                    class="go-acf-switch-off"
                                                                                    style="min-width: 36px;">-</span>
                                                                            <div class="go-acf-switch-slider"></div>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="go-acf-field go-acf-field-true-false go_reward go_health"
                                                            data-name="health" data-type="true_false">
                                                            <div class="go-acf-input">
                                                                <div class="go-acf-true-false">
                                                                    <input value="0" type="hidden">
                                                                    <label>
                                                                        <input name="health_toggle" type="checkbox"
                                                                               value="1" class="go-acf-switch-input go_messages_toggle_input health_toggle_messages">
                                                                        <div class="go-acf-switch"><span class="go-acf-switch-on"
                                                                                                         style="min-width: 36px;">+</span><span
                                                                                    class="go-acf-switch-off"
                                                                                    style="min-width: 36px;">-</span>
                                                                            <div class="go-acf-switch-slider"></div>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr class="go-acf-row">
                                                        <td class="go-acf-field go-acf-field-number go_reward go_xp  data-name="
                                                            xp
                                                        " data-type="number">
                                                        <div class="go-acf-input">
                                                            <div class="go-acf-input-wrap"><input name="xp" type="number"
                                                                                                  value="" min="0" step="1" placeholder="0" class="xp_messages go_messages_xp_input" oninput="validity.valid||(value='');">
                                                            </div>
                                                        </div>
                                                        </td>
                                                        <td class="go-acf-field go-acf-field-number go_reward go_gold"
                                                            data-name="gold" data-type="number">
                                                            <div class="go-acf-input">
                                                                <div class="go-acf-input-wrap"><input name="gold" type="number"
                                                                                                      value="" min="0"
                                                                                                      step=".01" placeholder="0" class="gold_messages go_messages_gold_input" oninput="validity.valid||(value='');"></div>
                                                            </div>
                                                        </td>
                                                        <td class="go-acf-field go-acf-field-number go_reward go_health "
                                                            data-name="health" data-type="number">
                                                            <div class="go-acf-input">
                                                                <div class="go-acf-input-wrap"><input name="health"
                                                                                                      type="number" value=""
                                                                                                      min="0" step=".01" placeholder="0" class="health_messages go_messages_health_input" oninput="validity.valid||(value='');"></div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    </tbody>
                                                </table>
                                            </div>
                                            <p></p>
                                            <div class="go-acf-input">
                                                <table class="go-acf-table">
                                                    <thead>
                                                    <tr>
                                                        <th>
                                                            <div class="go-acf-th">
                                                                <label><?php echo get_option('options_go_badges_name_plural');?></label></div>
                                                        </th>
                                                        <th>
                                                            <div class="go-acf-th">
                                                                <label>Groups</label></div>
                                                        </th>

                                                    </tr>

                                                    </thead>
                                                    <tbody>
                                                    <tr class="go-acf-row">
                                                        <td class="go-acf-field go-acf-field-true-false go_reward go_xp"
                                                            data-name="xp" data-type="true_false">
                                                            <div class="go-acf-input">
                                                                <div class="go-acf-true-false">
                                                                    <input value="0" type="hidden">
                                                                    <label>
                                                                        <input name="badges_toggle" type="checkbox"
                                                                               value="1" class="go-acf-switch-input badges_toggle_messages">
                                                                        <div class="go-acf-switch"><span class="go-acf-switch-on"
                                                                                                         style="min-width: 36px;">+</span><span
                                                                                    class="go-acf-switch-off"
                                                                                    style="min-width: 36px;">-</span>
                                                                            <div class="go-acf-switch-slider"></div>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="go-acf-field go-acf-field-true-false go_reward go_gold"
                                                            data-name="gold" data-type="true_false">
                                                            <div class="go-acf-input">
                                                                <div class="go-acf-true-false">
                                                                    <input value="0" type="hidden">
                                                                    <label>
                                                                        <input name="groups_toggle" type="checkbox"
                                                                               value="1" class="go-acf-switch-input groups_toggle_messages">
                                                                        <div class="go-acf-switch"><span class="go-acf-switch-on"
                                                                                                         style="min-width: 36px;">+</span><span
                                                                                    class="go-acf-switch-off"
                                                                                    style="min-width: 36px;">-</span>
                                                                            <div class="go-acf-switch-slider"></div>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </td>

                                                    </tr>
                                                    <tr class="go-acf-row">
                                                        <td class="go-acf-field go-acf-field-true-false go_reward go_gold"
                                                            data-name="gold" data-type="true_false">
                                                            <?php go_make_tax_select('go_badges', "messages_"); ?>

                                                        </td>
                                                        <td class="go-acf-field go-acf-field-true-false go_reward go_gold"
                                                            data-name="gold" data-type="true_false">
                                                            <?php go_make_tax_select('user_go_groups', "messages_"); ?>
                                                        </td>

                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--<p class="go_message_submit"><input type="button" id="go_message_submit"
                                                                class="button button-primary" value="Send"></p>
                            -->
                        </div>


                    </div>
                </form>

            </div>

            <?php
        }
        $message = ob_get_contents();

        ob_end_clean();

    }


    echo json_encode(
        array(
            'type' => $type,
            'title' => $title,
            'message' => $message
        )
    );
    die();
}

/**
 * Check for new admin messages
 */
function go_admin_messages(){

    //$user_id = get_current_user_id();
    //check_ajax_referer( 'go_admin_messages');
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_admin_messages' ) ) {
        echo "refresh";
        die( );
    }
    go_check_messages();
    die();
}

function go_reset_message($this_message, $message, $penalty, $xp, $gold, $health, $xp_task, $gold_task, $health_task, $badge_array, $group_array, $badge_array_task, $group_array_task ){
    $xp_message = '';
    $gold_message = '';
    $health_message = '';
    $badge_message = '';
    $group_message = '';

    $loot_message = '';

    $xp_p_message = '';
    $gold_p_message = '';
    $health_p_message = '';
    $badge_p_message = '';
    $group_p_message = '';

    $penalty_message = '';
//reset stage
    if($xp_task != 0){
        $xp_message = go_display_shorthand_currency('xp',$xp_task,false );
    }
    if($gold_task != 0){
        //$gold_message = "<br>" . go_get_loot_short_name('gold').":".$gold_task;
        $gold_message = go_display_shorthand_currency('gold',$gold_task,false, true);
    }
    if($health_task != 0){
        //$health_message = "<br>" . go_get_loot_short_name('health').":".$health_task;
        $health_message = go_display_shorthand_currency('health',$health_task,false);
    }
    if (count($badge_array_task)){
        //$badge_name = get_option('options_go_badges_name_plural');
        $badge_message = "<br>" . get_option('options_go_badges_name_plural') . ": ". go_print_badge_list($badge_array_task);
    }
    if (count($group_array_task)){
        $group_message = "<br>Groups: ". go_print_group_list($group_array_task);
    }
    if (($xp_task != 0) || ($gold_task != 0) || ($health_task != 0) || count($badge_array_task) > 0 || count($group_array_task) > 0) {
        $loot_message = "<br><br>Loot Removed:" . $xp_message . $gold_message . $health_message . $badge_message . $group_message;
    }

    //$message = $this_message . $message . $loot_message ;
    if($penalty == 'true') {
        if ($xp != 0) {
            $xp_p_message = "<br>" . go_get_loot_short_name('xp') . ":" . $xp;
        }
        if ($gold != 0) {
            $gold_p_message = "<br>" . go_get_loot_short_name('gold') . ":" . $gold;
        }
        if ($health != 0) {
            $health_p_message = "<br>" . go_get_loot_short_name('health') . ":" . $health;
        }
        if (count($badge_array)) {
            //$badge_name = get_option('options_go_badges_name_plural');

            $badge_p_message = "<br>" . get_option('options_go_badges_name_plural') . ": " . go_print_badge_list($badge_array);

        }
        if (count($group_array)) {
            $group_p_message = "<br>Groups: " . go_print_group_list($group_array);
        }
        if (($xp != 0) || ($gold != 0) || ($health != 0) || count($badge_array) > 0 || count($group_array) > 0) {
            $penalty_message = "<br><br>Consequence:" . $xp_p_message . $gold_p_message . $health_p_message . $badge_p_message . $group_p_message;
        }
        //add the task loot removed to the additional penalties
        $xp = $xp + $xp_task;
        $gold = $gold + $gold_task;
        $health = $health + $health_task;
    }

    $message = $this_message . $message . $loot_message . $penalty_message ;
    return $message;
}

function go_send_message(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_send_message');
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_send_message' ) ) {
        echo "refresh";
        die( );
    }

    global $wpdb;
    $go_task_table_name = "{$wpdb->prefix}go_tasks";

    $title = ( !empty( $_POST['title'] ) ? $_POST['title'] : "" );

    $message = stripslashes( !empty( $_POST['message'] ) ? $_POST['message'] : "" );

    $type = ( !empty( $_POST['message_type'] ) ? $_POST['message_type'] : "message" );// can be message, or reset

    $penalty = ( !empty( $_POST['penalty'] ) ? $_POST['penalty'] : false );// can be message, or reset

    $xp = ($_POST['xp']);
    $gold = ($_POST['gold']);
    $health = ($_POST['health']);
    $go_blog_task_id = null;

    $badges_toggle = $_POST['badges_toggle'];
    $badge_ids = $_POST['badges'];
    if (!is_array($badge_ids)){
        $badge_ids = array();
    }

    $groups_toggle = $_POST['groups_toggle'];
    $group_ids = $_POST['groups'];
    if (!is_array($group_ids)){
        $group_ids = array();
    }

    $reset_vars = $_POST['reset_vars'];

    $task_name = get_option('options_go_tasks_name_singular');

    $uniqueUsers = array_unique(array_map(function ($i) { return $i['uid']; }, $reset_vars));
    if ($type == "message") {
    $reset_vars = $uniqueUsers;
    }
    if ($type == "reset_stage") {
       $task_t= $reset_vars[0]['task'];
       $user_t= get_post_field( 'post_author', $task_t );
       $uniqueUsers = array($user_t);
    }

    foreach ($reset_vars as $vars){
        if ($type == "reset"){
            $task_id = $vars['task'];
            $user_id = $vars['uid'];
            $task_title = get_the_title($task_id);
            $title = "The following " .$task_name . " has been reset: ". $task_title .".";
            $this_message = "All loot and rewards earned have been removed.";

            if(!empty($message)) {
                $message = "<br><br>" . $message;
            }


            $tasks = $wpdb->get_results($wpdb->prepare("SELECT *
			FROM {$go_task_table_name}
			WHERE uid = %d and post_id = %d
			ORDER BY last_time DESC", $user_id, $task_id
            ));
            $task = $tasks[0];
            $xp_task = ($task->xp * -1);
            $gold_task = ($task->gold * -1);
            $health_task = ($task->health * -1);
            $badge_task = unserialize($task->badges);
            $group_task = unserialize($task->groups);
            if (!is_array($badge_task)){
                $badge_task = array();
            }
            if (!is_array($group_task)){
                $group_task = array();
            }

            $message = go_reset_message($this_message, $message, $penalty, $xp, $gold, $health, $xp_task, $gold_task, $health_task, $badge_ids, $group_ids, $badge_task, $group_task );



            //update task table
            $wpdb->update($go_task_table_name, array('status' => -2,// integer (number)
                'bonus_status' => 0, 'xp' => 0, 'gold' => 0, 'health' => 0, 'badges' => null, 'groups' => null), array('uid' => $user_id, 'post_id' => $task_id), array('%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s'), array('%d', '%d'));



            $xp = $xp + $xp_task;
            $gold = $gold + $gold_task;
            $health = $health + $health_task;


            if (!empty($badge_task)) {//if badges were earned, remove them
                //$badge_ids = serialize($badge_array);
                //go_remove_badges($badge_ids, $uid, false);//remove badges
                $badge_ids = array_merge($badge_task, $badge_ids);
            }

            if (!empty($group_task)) {//if groups were earned, remove them
                //$group_ids = serialize($group_array);
                //go_remove_groups($group_ids, $uid, false);//remove groups
                $group_ids = array_merge($group_task, $group_ids);
            }



        }
        else if($type == 'reset_stage'){
            $type = 'reset';

            $blog_post_id = $vars['task'];
            //$user_id = $vars['uid'];
            $user_id = get_post_field( 'post_author', $blog_post_id );


            if ($blog_post_id != 0 && !empty($blog_post_id)) {

                $blog_meta = get_post_custom($blog_post_id);
                $stage_num = (isset($blog_meta['go_blog_task_stage'][0]) ? $blog_meta['go_blog_task_stage'][0] : null);
                $bonus_stage_num = (isset($blog_meta['go_blog_bonus_stage'][0]) ? $blog_meta['go_blog_bonus_stage'][0] : null);
                $aTable = "{$wpdb->prefix}go_actions";
                //$go_blog_task_id =

                //get task_id from the blog post id
                $go_blog_task_id = go_get_task_id($blog_post_id);

                if(empty($go_blog_task_id)) {//this post is not associated with a task
                    die();//put error message here
                }
                else{
                    $task_title = get_the_title($go_blog_task_id);
                    $title = "A blog post from the following " .$task_name . " has been reset: ". $task_title .".";
                    $this_message = "All loot and rewards earned on this ". $task_name. " from this point forward have been removed.";

                    if ($stage_num !== null) {//if a stage number was sent (it is not a bonus stage)
                        $stage_type = 'stage';
                        $new_status_task = $stage_num;
                        $stage_num = $stage_num + 1 ;

                        $new_bonus_status_task = null;


                        //get all tasks with a ID that is greater and add loot then subtract
                        //get all blog post IDs and set as trash

                    }
                    else{//if it is a bonus only mark that one and remove the loot
                        $stage_type = 'bonus_status';
                        $new_status_task = null;
                        $new_bonus_status_task = $bonus_stage_num;
                    }

                    ////////////////////
                    ///
                    ///
                    //get the first time this task is in the actions table
                    //this gives the first row because it is searching for the blog post id and sorted by id
                    $result = $wpdb->get_results($wpdb->prepare("SELECT id, uid, xp, gold, health, badges, groups, check_type, result
                    FROM {$aTable} 
                    WHERE result = %d AND source_id = %d AND action_type = %s
                    ORDER BY id DESC LIMIT 1",
                    $blog_post_id,
                    $go_blog_task_id,
                    'task'), ARRAY_A);

                    $loot = $result;
                    $result = $result[0];
                    //$result = json_decode(json_encode($result), true);
                    $id = $result['id'];
                    $uid = $result['uid'];

                    if ($stage_type === 'stage'){ //remove all loot since this stage, including this stage and mark all other blog posts deleted

                        //get all actions on this stage so the loot can be added up
                        $loot = $wpdb->get_results($wpdb->prepare("SELECT xp, gold, health, badges, groups, check_type, result
                            FROM {$aTable} 
                            WHERE uid = %d AND source_id = %d AND action_type = %s AND id >= %d
                            ORDER BY id ", $uid, $go_blog_task_id, 'task', $id), ARRAY_A);
                    }
                    $xp_task = 0;
                    $gold_task = 0;
                    $health_task = 0;
                    $badge_array = array();
                    $group_array = array();

                    foreach($loot as $loot_row){
                        $xp_task = $loot_row['xp'] + $xp_task;
                        $gold_task = $loot_row['gold'] + $gold_task;
                        $health_task = $loot_row['health'] + $health_task;
                        $badges_task = $loot_row['badges'];
                        $groups_task = $loot_row['groups'];
                        $check_type = $loot_row['check_type'];
                        $result = $loot_row['result'];

                        if ($check_type === "blog"){
                            $post = array( 'ID' => intval($result ), 'post_status' => 'reset' );
                            wp_update_post($post);
                            //wp_trash_post( intval($result ) );
                        }

                        $badges_task = unserialize($badges_task);
                        $groups_task = unserialize($groups_task);
                        if (!is_array($badges_task)){
                            $badges_task = array();
                        }
                        if (!is_array($groups_task)){
                            $groups_task = array();
                        }
                        $badge_array = array_merge($badges_task, $badge_array);
                        $group_array = array_merge($groups_task, $group_array);
                    }



                    $time = current_time('mysql');
                    $last_time = $time;

                    //loot to be removed
                    $xp_task = ($xp_task) * -1;
                    $gold_task = ($gold_task) * -1;
                    $task_health = ($health_task) * -1;

                    $message = go_reset_message($this_message, $message, $penalty, $xp, $gold, $health, $xp_task, $gold_task, $health_task, $badge_ids, $group_ids, $badge_array, $group_array );



                    if (!empty($badge_array)) {//if badges were earned, remove them
                        //$badge_ids = serialize($badge_array);
                        //go_remove_badges($badge_ids, $uid, false);//remove badges
                        $badge_ids = array_merge($badge_array, $badge_ids);
                    }

                    if (!empty($group_array)) {//if groups were earned, remove them
                        //$group_ids = serialize($group_array);
                        //go_remove_groups($group_ids, $uid, false);//remove groups
                        $group_ids = array_merge($group_array, $group_ids);
                    }

                    $xp = $xp + $xp_task;
                    $gold = $gold + $gold_task;
                    $health = $health + $health_task;


                    $new_status_task = intval($new_status_task);
                    $new_bonus_status_task = intval($new_bonus_status_task);
                    if ($stage_type === 'bonus_status'){
                        $update_col = "bonus_status = -1 + bonus_status ";
                        $update_col = max($update_col,0);
                    }else{
                        $update_col = "status = {$new_status_task}, bonus_status = 0";
                    }

                    $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE {$go_task_table_name} 
                    SET 
                        {$update_col},
                        xp = {$xp_task} + xp,
                        gold = {$gold_task} + gold,
                        health = {$task_health} + health,
                        last_time = IFNULL('{$last_time}', last_time)         
                    WHERE uid= %d AND post_id=%d ",
                            intval($uid),
                            intval($go_blog_task_id)
                        )
                    );

                }
            }

        }
        else {//this is a regular message
            $go_blog_task_id = null;
            $user_id = $vars;

        }


        ////START MESSAGE CONSTRUCTION
            //the results are combined for saving in the database as a serialized array
        $result = array();
        $result[] = $title;
        $result[] = $message;

        //store the badge and group toggles so later we know if they were awarded or taken.
        if ($badges_toggle == "true" && !empty($badge_ids)) {//if badges toggle is true and badges exist
            $result[] = "badges+";
            $badge_ids = serialize($badge_ids);
        }else if ($badges_toggle == "false" && !empty($badge_ids)) {//else if badges toggle is false and badges exist
            $result[] = "badges-";
            $badge_ids = serialize($badge_ids);
        }else {
            $result[] = "badges0";
            $badge_ids = null;
        }

        if ($groups_toggle == "true" && !empty($group_ids)) {//if groups toggle is true and groups exist
            $result[] = "groups+";
            $group_ids = serialize($group_ids);
        }else if ($groups_toggle == "false" && !empty($group_ids)) {//else if groups toggle is false and groups exist
            $result[] = "groups-";
            $group_ids = serialize($group_ids);
        }else{
            $result[] = "groups0";
            $group_ids = null;
        }
        $result = serialize($result);

        //Update the DB as needed
        //add/subtrct groups and badges
        if ($badges_toggle == "true" && !empty($badge_ids)) {//if badges toggle is true and badges exist
            go_add_badges($badge_ids, $user_id, false);//add badges
        } else if ($badges_toggle == "false" && !empty($badge_ids)) {//else if badges toggle is false and badges exist
            go_remove_badges($badge_ids, $user_id, false);//remove badges
        }

        if ($groups_toggle == "true" && !empty($group_ids)) {//if groups toggle is true and groups exist
            go_add_groups($group_ids, $user_id, false);//add groups
        } else if ($groups_toggle == "false" && !empty($group_ids)) {//else if groups toggle is false and groups exist
            go_remove_groups($group_ids, $user_id, false);//remove groups
        }

        //update actions
        go_update_actions($user_id, $type, $go_blog_task_id, 1, null, null, $result, null, null, null, null, $xp, $gold, $health, $badge_ids, $group_ids, false, false);
    }

    //set new message user option to true so each user gets the message
    foreach ($uniqueUsers as $user_id) {
        $user_id = intval($user_id);
        update_user_option($user_id, 'go_new_messages', true);
    } //end foreach user
}

function go_messages_canned(){
    echo "<select class='go_messages_canned'>";
    echo "<option>Canned Feedback</option>";
    $num_preset = get_option('options_go_messages_canned');
    $i = 0;
    while ($i < $num_preset){
        $title = get_option('options_go_messages_canned_'.$i.'_title');
        $title = htmlspecialchars($title);
        $message = get_option('options_go_messages_canned_'.$i.'_message');
        $message = htmlspecialchars($message);
        $toggle = get_option('options_go_messages_canned_'.$i.'_toggle');
        $xp = get_option('options_go_messages_canned_'.$i.'_defaults_xp');
        $gold = get_option('options_go_messages_canned_'.$i.'_defaults_gold');
        $health = get_option('options_go_messages_canned_'.$i.'_defaults_health');

        echo '<option class="go_messages_option" value="'.$i.'" data-message="'.$message.'" data-toggle="'.$toggle.'" data-xp="'.$xp.'" data-gold="'.$gold.'" data-health="'.$health.'" data-title="'.$title.'">'.$title.'</option>';
        $i++;
    }
    echo "</select>";

}

?>