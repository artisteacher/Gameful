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

/**
 * @param $uniqueUsers
 * @param $user_count
 * @return string
 */
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

            $user_name = go_get_user_display_name($user_id);
        }
        $user_name = $user_name . $this_user_name;
        $is_first = false;
    }

    return $user_name;
}

/**
 * @param $reset_vars
 */
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

/**
 *
 */
function go_create_admin_message ()
{
    if (!is_user_logged_in()) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_create_admin_message');
    if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_create_admin_message')) {
        echo "refresh";
        die();
    }

    $user_id = (isset($_POST['user_ids']) ? $_POST['user_id'] : null);

    $message_type = $_POST['message_type'];

    $reset_vars = (isset($_POST['reset_vars']) ? $_POST['reset_vars'] : null);


    if ($message_type == 'single_reset' || $message_type == 'multiple_reset' || $message_type == 'reset_stage') {

        if (empty($reset_vars) && empty($user_id)){
            $task_name = get_option( 'options_go_tasks_name_singular'  );
            $type = 'no_users';
            $title = "No $task_name Selected";
            $message = "Please select a $task_name from the list.";
        }
        else {
            ob_start();
            $type = 'reset';
            $uniqueTasks = array_unique(array_map(function ($i) {
                return $i['task'];
            }, $reset_vars));
            $quest_count = count($uniqueTasks);

            $uniqueUsers = array_unique(array_map(function ($i) {
                return $i['uid'];
            }, $reset_vars));
            $user_count = count($uniqueUsers);

            //Get task custom name variable
            if ($quest_count > 1) {
                $task_name = get_option('options_go_tasks_name_plural');
            } else {
                $task_name = get_option('options_go_tasks_name_singular');
            }

            if($message_type === 'reset_stage'){
                $task_name .= " to this stage.";
            }
            $title = 'Reset ' . $task_name . ' <span class="tooltip" data-tippy-content="Resetting removes all loot and rewards. <br> <br>If the bonus loot had already been awarded, it is also removed and the user will not have another attempt." style="font-size: .6em;"> <span><i class="fa fa-info-circle"></i></span></span>';


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
                    } else {//message for multiple tasks and users


                        ?>
                        <div>
                            <p>Warning: Multiple Values for Users and <?php echo $task_name ?> were selected. Please
                                double
                                check that you want to reset all these <?php echo $task_name ?>.</p>
                        </div>

                        <?php
                        go_get_reset_mixed($reset_vars);

                    }

                    ?>
                    <div id="go_messages" style="display:flex;">
                        <br>

                        <div id="messages_form" style="text-align: left;">

                            <input id="go_custom_message_toggle" type="checkbox">
                            <label for="go_custom_message_toggle">Add a Custom Message </label><span class="tooltip"
                                                                                                     data-tippy-content="Users will be notified of reset. You can add a custom message."><span><i
                                            class="fa fa-info-circle"></i></span> </span>
                            <table id="go_custom_message_table" class="form-table" style="display: none;">
                                <tr valign="top">
                                    <td><?php
                                        $settings = array(
                                            //'tinymce'=> array( 'menubar'=> false, 'toolbar1' => 'undo,redo', 'toolbar2' => ''),
                                            'tinymce'=>true,
                                            //'wpautop' =>false,
                                            'textarea_name' => 'go_message_text_area',
                                            'media_buttons' => true,
                                            //'teeny' => true,
                                            'menubar' => false,
                                            'drag_drop_upload' => true);

                                        //echo "<button id='go_save_button' class='progress left'  check_type='blog' button_type='save'  admin_lock='true' >Save Draft</button> ";

                                        //$id = $_POST['editorID'];
                                        //$content = $_POST['content'];

                                        //wp_editor( $content, $id );
                                        wp_editor('', 'go_message_text_area_id', $settings);

                                        /*<textarea class="go_messages_message_input summernote" name="message" class="widefat"
                                                  cols="50"
                                                  rows="5" style="width: 100%"></textarea>*/
                                        ?>

                                    </td>
                                </tr>

                            </table>

                            <br>
                            <input id="go_additional_penalty_toggle" type="checkbox">
                            <label for="go_additional_penalty_toggle">Assign Consequence </label><span class="tooltip"
                                                                                                       data-tippy-content="In addition to removing loot that had been awarded, you may assign an additional penalty."><span><i
                                            class="fa fa-info-circle"></i></span></span>


                            <div id="go_loot_table" class="go-acf-field go-acf-field-group go_penalty_table"
                                 data-type="group" style="display:none;">

                                <div class="go-acf-input">
                                    <div id="go_penalty_table" class="go-acf-fields -top -border">
                                        <div class="go-acf-field go-acf-field-group go-acf-hide-label go-acf-no-padding go-acf-table-no-border"
                                             data-name="reward_toggle" data-type="group">
                                            <div class="go-acf-input">
                                                <table class="go-acf-table ">
                                                    <thead>
                                                    <tr>
                                                        <?php
                                                        $go_gold_toggle = get_option('options_go_loot_gold_toggle');
                                                        $go_xp_toggle = get_option('options_go_loot_xp_toggle');
                                                        $go_health_toggle = get_option('options_go_loot_health_toggle');
                                                        $go_badges_toggle = get_option('options_go_badges_toggle');
                                                        $go_groups_toggle = get_option('options_go_groups_toggle');

                                                        if ($go_xp_toggle) {
                                                            ?>
                                                            <th>
                                                                <div class="go-acf-th">
                                                                    <label>-<?php echo go_get_loot_short_name('xp'); ?></label>
                                                                </div>
                                                            </th>
                                                            <?php
                                                        }
                                                        if ($go_gold_toggle) {
                                                            ?>

                                                            <th>
                                                                <div class="go-acf-th">
                                                                    <label>-<?php echo go_get_loot_short_name('gold'); ?></label>
                                                                </div>
                                                            </th>
                                                            <?php
                                                        }
                                                        if ($go_health_toggle) {
                                                            ?>
                                                            <th>
                                                                <div class="go-acf-th">
                                                                    <label>-<?php echo go_get_loot_short_name('health'); ?></label>
                                                                </div>
                                                            </th>
                                                            <?php
                                                        }

                                                        ?>

                                                    </tr>


                                                    </thead>
                                                    <tbody>


                                                    <tr class="go-acf-row">
                                                        <?php
                                                        if ($go_xp_toggle) {
                                                            ?>
                                                            <td class="go-acf-field go-acf-field-number go_reward go_xp"  data-name="
                                                                xp
                                                            " data-type="number">
                                                            <div class="go-acf-input">
                                                                <div class="go-acf-input-wrap"><input name="xp"
                                                                                                      type="number"
                                                                                                      value="" min="0"
                                                                                                      step="1"
                                                                                                      placeholder="0"
                                                                                                      oninput="validity.valid||(value='');"
                                                                                                      class="xp_messages go_pink">
                                                                </div>
                                                            </div>
                                                            </td>
                                                            <?php
                                                        }
                                                        if ($go_gold_toggle) {
                                                            ?>
                                                            <td class="go-acf-field go-acf-field-number go_reward go_gold"
                                                                data-name="gold" data-type="number">
                                                                <div class="go-acf-input">
                                                                    <div class="go-acf-input-wrap"><input name="gold"
                                                                                                          type="number"
                                                                                                          value=""
                                                                                                          min="0"
                                                                                                          step=".01"
                                                                                                          placeholder="0"
                                                                                                          oninput="validity.valid||(value='');"
                                                                                                          class="gold_messages go_pink">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <?php
                                                        }
                                                        if ($go_health_toggle) {
                                                            ?>
                                                            <td class="go-acf-field go-acf-field-number go_reward go_health "
                                                                data-name="health" data-type="number">
                                                                <div class="go-acf-input">
                                                                    <div class="go-acf-input-wrap"><input name="health"
                                                                                                          type="number"
                                                                                                          value=""
                                                                                                          min="0"
                                                                                                          step=".01"
                                                                                                          placeholder="0"
                                                                                                          oninput="validity.valid||(value='');"
                                                                                                          class="health_messages go_pink">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <?php
                                                        }
                                                        ?>
                                                    </tr>

                                                    </tbody>
                                                </table>
                                            </div>
                                            <p></p>
                                            <div class="go-acf-input">
                                                <table class="go-acf-table">
                                                    <thead>
                                                    <tr>
                                                        <?php
                                                        if ($go_badges_toggle) {
                                                            ?>
                                                            <th>
                                                                <div class="go-acf-th">
                                                                    <label>Remove <?php echo get_option('options_go_badges_name_plural'); ?></label>
                                                                </div>
                                                            </th>
                                                            <?php
                                                        }
                                                        if ($go_groups_toggle) {
                                                            ?>
                                                            <th>
                                                                <div class="go-acf-th">
                                                                    <label>Remove Groups</label></div>
                                                            </th>
                                                            <?php
                                                        }
                                                        ?>

                                                    </tr>

                                                    </thead>
                                                    <tbody>

                                                    <tr class="go-acf-row">
                                                        <?php
                                                        if ($go_badges_toggle) {
                                                            ?>
                                                            <td class="go-acf-field go-acf-field-true-false go_reward go_badges"
                                                                data-name="badges" data-type="true_false"
                                                                class="go_pink">
                                                                <?php go_make_tax_select('go_badges', true); ?>

                                                            </td>
                                                            <?php
                                                        }
                                                        if ($go_groups_toggle) {
                                                            ?>
                                                            <td class="go-acf-field go-acf-field-true-false go_reward go_groups"
                                                                data-name="groups" data-type="true_false"
                                                                class="go_pink">
                                                                <?php go_make_tax_select('user_go_groups', true); ?>
                                                            </td>
                                                            <?php
                                                        }
                                                        ?>

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
            $message = ob_get_contents();

            ob_end_clean();
        }
    }
    else {//this is not a reset, so build a regular message

        if (empty($reset_vars) && empty($user_id)){
            $type = 'no_users';
            $title = 'No Users Selected';
            $message = "Please select a user from the list.";
        }
        else {
            ob_start();
            $title = 'Send a Message';
            $type = 'message';
            $uniqueUsers = array_unique(array_map(function ($i) {
                return $i['uid'];
            }, $reset_vars));
            //$user_count = count($uniqueUsers);
            ?>


            <div id="go_messages_container">
                <form method="post">
                    <div id="go_messages" style="display:flex;">

                        <div id="messages_form" style="width: 100%">
                            <div style="float:left; padding: 20px 0;"><?php go_messages_canned(); ?></div>
                            <table class="form-table" style="clear:both;">
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
                                                    $user_fullname = go_get_user_display_name($user_id);
                                                }
                                                echo $user_fullname;
                                                $is_first = false;
                                            }
                                            $go_gold_toggle = get_option('options_go_loot_gold_toggle');
                                            $go_xp_toggle = get_option('options_go_loot_xp_toggle');
                                            $go_health_toggle = get_option('options_go_loot_health_toggle');
                                            $go_badges_toggle = get_option('options_go_badges_toggle');
                                            $go_badges_name_plural = get_option('options_go_badges_name_plural');
                                            $go_groups_toggle = get_option('options_go_groups_toggle');
                                            $go_groups_name_plural = get_option('options_go_groups_name_plural');
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Title</th>
                                    <td style="width: 100%;"><input class="go_messages_title_input" type="text"
                                                                    name="title"
                                                                    value="" style="width: 100%;"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Message</th>
                                    <td><?php
                                        $settings = array(
                                            //'tinymce'=> array( 'menubar'=> false, 'toolbar1' => 'undo,redo', 'toolbar2' => ''),
                                            'tinymce'=>true,
                                            //'wpautop' =>false,
                                            'textarea_name' => 'go_message_text_area',
                                            'media_buttons' => true,
                                            //'teeny' => true,
                                            'menubar' => false,
                                            'drag_drop_upload' => true);

                                        //echo "<button id='go_save_button' class='progress left'  check_type='blog' button_type='save'  admin_lock='true' >Save Draft</button> ";

                                        //$id = $_POST['editorID'];
                                        //$content = $_POST['content'];

                                        //wp_editor( $content, $id );
                                        wp_editor('', 'go_message_text_area_id', $settings);

                                        /*<textarea class="go_messages_message_input summernote" name="message" class="widefat"
                                                  cols="50"
                                                  rows="5" style="width: 100%"></textarea>*/
                                        ?></td>
                                </tr>

                            </table>
                            <?php
                            if ($go_xp_toggle || $go_gold_toggle || $go_health_toggle || $go_badges_toggle || $go_groups_toggle) {
                                ?>
                                <div class="go-acf-input go_loot_table">
                                    <div class="go-acf-true-false">
                                        <input value="0" type="hidden">
                                        <label>
                                            <input name="loot_toggle" type="checkbox" class="go-acf-switch-input go_messages_toggle_input">
                                           <div class="go-acf-switch"><span class="go-acf-switch-on" style="min-width: 36px;">Award</span><span class="go-acf-switch-off" style="min-width: 36px;">Penalty</span>
                                                <div class="go-acf-switch-slider"></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div id="go_loot_table" class="go-acf-field go-acf-field-group" data-type="group">
                                    <div class="go-acf-input">
                                        <div class="go-acf-fields -top -border">
                                            <div class="go-acf-field go-acf-field-group go-acf-hide-label go-acf-no-padding go-acf-table-no-border"
                                                 data-name="reward_toggle" data-type="group">
                                                <?php
                                                if ($go_xp_toggle || $go_gold_toggle || $go_health_toggle) {
                                                    ?>

                                                    <div class="go-acf-input">
                                                        <table class="go-acf-table">
                                                            <thead>
                                                            <tr>
                                                                <?php
                                                                if ($go_xp_toggle) {
                                                                    ?>
                                                                    <th>
                                                                        <div class="go-acf-th">
                                                                            <label><?php echo go_get_loot_short_name('xp'); ?></label>
                                                                        </div>
                                                                    </th>
                                                                    <?php
                                                                }
                                                                if ($go_gold_toggle) {
                                                                    ?>
                                                                    <th>
                                                                        <div class="go-acf-th">
                                                                            <label><?php echo go_get_loot_short_name('gold'); ?></label>
                                                                        </div>
                                                                    </th>
                                                                    <?php
                                                                }
                                                                if ($go_health_toggle) {
                                                                    ?>
                                                                    <th>
                                                                        <div class="go-acf-th">
                                                                            <label><?php echo go_get_loot_short_name('health'); ?></label>
                                                                        </div>
                                                                    </th>
                                                                    <?php
                                                                }
                                                                ?>

                                                            </tr>


                                                            </thead>
                                                            <tbody>

                                                            <tr class="go-acf-row">
                                                                <?php
                                                                if ($go_xp_toggle) {
                                                                    ?>
                                                                    <td class="go-acf-field go-acf-field-number go_reward go_xp" data-name="
                                                                        xp
                                                                    " data-type="number">
                                                                    <div class="go-acf-input">
                                                                        <div class="go-acf-input-wrap">
                                                                            <input name="xp"
                                                                                   type="number"
                                                                                   value="" min="0"
                                                                                   step="1"
                                                                                   placeholder="0"
                                                                                   class="xp_messages go_messages_xp_input"
                                                                                   oninput="validity.valid||(value='');">
                                                                        </div>
                                                                    </div>
                                                                    </td>
                                                                    <?php
                                                                }
                                                                if ($go_gold_toggle) {
                                                                    ?>
                                                                    <td class="go-acf-field go-acf-field-number go_reward go_gold"
                                                                        data-name="gold" data-type="number">
                                                                        <div class="go-acf-input">
                                                                            <div class="go-acf-input-wrap"><input
                                                                                        name="gold"
                                                                                        type="number"
                                                                                        value="" min="0"
                                                                                        step=".01"
                                                                                        placeholder="0"
                                                                                        class="gold_messages go_messages_gold_input"
                                                                                        oninput="validity.valid||(value='');">
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <?php
                                                                }
                                                                if ($go_health_toggle) {
                                                                    ?>
                                                                    <td class="go-acf-field go-acf-field-number go_reward go_health "
                                                                        data-name="health" data-type="number">
                                                                        <div class="go-acf-input">
                                                                            <div class="go-acf-input-wrap"><input
                                                                                        name="health"
                                                                                        type="number"
                                                                                        value=""
                                                                                        min="0" step=".01"
                                                                                        placeholder="0"
                                                                                        class="health_messages go_messages_health_input"
                                                                                        oninput="validity.valid||(value='');">
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </tr>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <p></p>
                                                    <?php
                                                }
                                                if ($go_groups_toggle || $go_badges_toggle) {
                                                    ?>
                                                    <div class="go-acf-input">
                                                        <table class="go-acf-table">
                                                            <thead>
                                                            <tr>
                                                                <?php
                                                                if ($go_badges_toggle) {
                                                                    ?>
                                                                    <th>
                                                                        <div class="go-acf-th">
                                                                            <label><?php echo $go_badges_name_plural ?></label>
                                                                        </div>
                                                                    </th>
                                                                    <?php
                                                                }
                                                                if ($go_groups_toggle) {
                                                                    ?>
                                                                    <th>
                                                                        <div class="go-acf-th">
                                                                            <label><?php echo $go_groups_name_plural ?></label>
                                                                        </div>
                                                                    </th>
                                                                    <?php
                                                                }
                                                                ?>

                                                            </tr>

                                                            </thead>
                                                            <tbody>
                                                            <tr class="go-acf-row">
                                                                <?php
                                                                if ($go_badges_toggle) {
                                                                    ?>
                                                                    <td class="go-acf-field go-acf-field-true-false go_reward go_gold"
                                                                        data-name="gold" data-type="true_false">
                                                                        <?php go_make_tax_select('go_badges', true); ?>

                                                                    </td>
                                                                    <?php
                                                                }
                                                                if ($go_groups_toggle) {
                                                                    ?>
                                                                    <td class="go-acf-field go-acf-field-true-false go_reward go_gold"
                                                                        data-name="gold" data-type="true_false">
                                                                        <?php go_make_tax_select('user_go_groups', true); ?>
                                                                    </td>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <!--<p class="go_message_submit"><input type="button" id="go_message_submit"
                                                                class="button button-primary" value="Send"></p>
                            -->
                        </div>


                    </div>
                </form>

            </div>

            <?php
            $message = ob_get_contents();
            ob_end_clean();
        }
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

/**
 * Constructs the text of the message shown to students when a task/stage is reset
 * @param $this_message
 * @param $message
 * @param $penalty
 * @param $xp
 * @param $gold
 * @param $health
 * @param $xp_task
 * @param $gold_task
 * @param $health_task
 * @param $badge_array
 * @param $group_array
 * @param $badge_array_task
 * @param $group_array_task
 * @return string
 */
function go_reset_message($message, $penalty, $xp, $gold, $health, $xp_task, $gold_task, $health_task, $badge_array, $group_array, $badge_array_task, $group_array_task ){
    $loot_message = '';
    $p_message = '';

//reset stage
    if (!empty($xp_task) || !empty($gold_task) || !empty($health_task) || !empty($badge_array_task) || !empty($group_array_task)) {
        $loot_message = "<ul>";

        if ($xp_task != 0) {
            $loot_message .= "<li>";
            $loot_message .= go_display_shorthand_currency('xp', $xp_task, false);
            $loot_message .= "</li>";
        }
        if ($gold_task != 0) {
            //$gold_message = "<br>" . go_get_loot_short_name('gold').":".$gold_task;
            $loot_message .= "<li>";
            $loot_message .= go_display_shorthand_currency('gold', $gold_task, false, false );
            $loot_message .= "</li>";
        }
        if ($health_task != 0) {
            $loot_message .= "<li>";
            //$health_message = "<br>" . go_get_loot_short_name('health').":".$health_task;
            $loot_message .= go_display_shorthand_currency('health', $health_task, false);
            $loot_message .= "</li>";
        }


        
        if (count($badge_array_task)) {
            //$badge_name = get_option('options_go_badges_name_plural');
            $loot_message .= "<li>";
            $loot_message .= get_option('options_go_badges_name_plural') . ": " . go_print_term_list($badge_array_task);
            $loot_message .= "</li>";
        }
        if (count($group_array_task)) {
            $loot_message .= "<li>";
            $loot_message .= get_option('options_go_groups_name_plural') . ": " . go_print_term_list($group_array_task);
            $loot_message .= "</li>";
        }
        $loot_message .= "</ul>";

    }
   // if (($xp_task != 0) || ($gold_task != 0) || ($health_task != 0) || count($badge_array_task) > 0 || count($group_array_task) > 0) {
        //$loot_message = "<br><br>Loot Removed:" . $xp_message . $gold_message . $health_message . $badge_message . $group_message;
    //}

    //$message = $this_message . $message . $loot_message ;
    if($penalty == 'true') {
        if (!empty($xp) || !empty($gold) || !empty($health) || !empty($badge_array) || !empty($group_array)) {
            $p_message = "Consequence:";
            $p_message .= "<ul>";
            if ($xp != 0) {
                $p_message .= "<li>";
                $p_message .= go_display_shorthand_currency('xp', $xp, false);
                $p_message .= "</li>";
            }
            if ($gold != 0) {
                $p_message .= "<li>";
                $p_message .= go_display_shorthand_currency('gold', $gold, false, false );
                $p_message .= "</li>";
            }
            if ($health != 0) {
                $p_message .= "<li>";
                $p_message .= go_display_shorthand_currency('health', $health, false);
                $p_message .= "</li>";
            }


            if (count($badge_array)) {
                //$badge_name = get_option('options_go_badges_name_plural');
                $p_message .= "<li>";
                $p_message .= get_option('options_go_badges_name_plural') . ": " . go_print_term_list($badge_array);
                $p_message .= "</li>";
            }
            if (count($group_array)) {
                $p_message .= "<li>";
                $p_message .= get_option('options_go_groups_name_plural') . ": " . go_print_term_list($group_array);
                $p_message .= "</li>";
            }
            $p_message .= "</ul>";
        }
    }

    $message = $message . $loot_message . $p_message ;
    return $message;
}

/**
 * @param bool $skip_ajax
 * @param string $title
 * @param string $sent_message
 * @param string $type
 * @param string $penalty
 * @param int $sent_xp
 * @param int $sent_gold
 * @param int $sent_health
 * @param null $task_id //only sent from when undo causes bankruptcy and a rep penalty is applied
 * @param bool $loot_toggle
 * @param string $sent_badge_id
 * @param string $sent_group_id
 * @param string $reset_vars
 */
function go_send_message($skip_ajax = false, $title = '', $sent_message = '', $type = '', $penalty = '', $sent_xp = 0, $sent_gold = 0, $sent_health = 0, $task_id = null, $loot_toggle = false, $sent_badge_id = '', $sent_group_id = '', $reset_vars = ''){

    if(!$skip_ajax) {
        if (!is_user_logged_in()) {
            echo "login";
            die();
        }

        //check_ajax_referer( 'go_send_message');
        if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_send_message')) {
            echo "refresh";
            die();
        }

        $title = (!empty($_POST['title']) ? $_POST['title'] : "");
        $title  = do_shortcode( $title );
        $sent_message = stripslashes(!empty($_POST['message']) ? $_POST['message'] : "");
        $sent_message  = do_shortcode( $sent_message );
        $type = (!empty($_POST['message_type']) ? $_POST['message_type'] : "message");// can be message, or reset

        $penalty = (!empty($_POST['penalty']) ? $_POST['penalty'] : false);// can be message, or reset

        $sent_xp = ($_POST['xp']);
        $sent_gold = ($_POST['gold']);
        $sent_health = ($_POST['health']);

        $loot_toggle = $_POST['loot_toggle'];
        if($loot_toggle == '1'){
            $loot_toggle = true;
        }else{
            $loot_toggle = false;
        }
        $sent_badge_id = $_POST['badges'];

        //$groups_toggle = $_POST['groups_toggle'];
        $sent_group_id = $_POST['groups'];

        $reset_vars = $_POST['reset_vars'];
    }


    global $wpdb;
    $go_task_table_name = "{$wpdb->prefix}go_tasks";

    $task_name = get_option('options_go_tasks_name_singular');

    foreach ($reset_vars as $vars){
        $user_id = $vars['uid'];
        $message = '';
        $xp_task = 0;
        $gold_task = 0;
        $health_task = 0;
        $badge_array = array();
        $group_array = array();
        $status = 0;
        $bonus_status = 0;
        $last_time = current_time('mysql');
        if ($type == "reset" || $type == "reset_stage") {
            if ($type == "reset") {//set the reset variables for a full quest reset
                $task_id = $vars['task'];
                $task_title = get_the_title($task_id);
                $title = "The following " . $task_name . " has been reset: " . $task_title . ".";
                $message = "All loot and rewards earned have been removed.";
                if (!empty($sent_message)) {      //if there is a custom message
                    $message = $sent_message."<br><br>".$message;
                }

                //get task table info
                $tasks = $wpdb->get_results($wpdb->prepare("SELECT *
                    FROM {$go_task_table_name}
                    WHERE uid = %d and post_id = %d
                    ORDER BY last_time DESC", $user_id, $task_id
                ));

                $task = $tasks[0];//the array of task info for this user

                $xp_task = ($task->xp * -1);
                $gold_task = ($task->gold * -1);
                $health_task = ($task->health * -1);

                //this info is a serialized array--convert it to an array or create an empty array
                $badge_array = unserialize($task->badges);
                $group_array = unserialize($task->groups);
                if (!is_array($badge_array)) {
                    if(is_numeric($badge_array)){
                        $badge_array = array($badge_array);
                    }else{
                        $badge_array = array();
                    }
                }
                if (!is_array($group_array)) {
                    if(is_numeric($group_array)) {
                        $group_array = array($group_array);
                    }else{
                        $group_array = array();
                    }
                }

            }
            else if ($type == 'reset_stage') {//set the reset variables for a stage reset by resetting a blog post
                $type = 'reset';
                $blog_post_id = $vars['task'];//this variable is not a task_id, but is the blog_id--we use that to find the task_id
                //$user_id = $vars['uid'];
                //$user_id = get_post_field('post_author', $blog_post_id);
                if ($blog_post_id != 0 && !empty($blog_post_id)) {
                    //get task_id from the blog post id
                    $task_id = go_get_task_id($blog_post_id);
                    //ERROR CHECK
                    if (empty($task_id)) {//this post is not associated with a task
                        die();//maybe put error message here
                    }

                    $task_title = get_the_title($task_id);
                    $task_url = get_permalink($task_id);
                    $title = "A blog post from the following " . $task_name . " has been reset: <a href='{$task_url}'>" . $task_title . "</a>.";
                    $message = "All loot and rewards earned on this " . $task_name . " from this point forward have been removed.";
                    if (!empty($sent_message)) {
                        $message = $sent_message."<br><br>".$message;
                    }

                    //get info about when this blog post was submitted

                    //get the last time this task is in the actions table
                    //this gives the first row because it is searching for the blog post id and sorted by id
                    $aTable = "{$wpdb->prefix}go_actions";
                    $result = $wpdb->get_results($wpdb->prepare("SELECT id, stage, bonus_status
                        FROM {$aTable} 
                        WHERE result = %d AND source_id = %d AND action_type = 'task'
                        ORDER BY id DESC LIMIT 1",
                        $blog_post_id,
                        $task_id
                        ), ARRAY_A
                    );

                    $first_row = $result[0];
                    //$result = json_decode(json_encode($result), true);
                    $id = $first_row['id'];//this row ID of the last time this blog post was submitted.
                    $status = $first_row['stage'];//the stage this was submitted on--empty if it was a bonus task

                    //get task table info
                    $bonus_status = $wpdb->get_var($wpdb->prepare("SELECT bonus_status
                        FROM {$go_task_table_name}
                        WHERE uid = %d and post_id = %d
                        ORDER BY id DESC", $user_id, $task_id
                    ));

                    //$current_bonus_status = $tasks[0]->bonus_status;

                    //get all loot on this stage since the last time this blog post was submitted
                    if (!empty($status)) {//remove all loot since this stage, including this stage and (maybe?) mark all other blog posts deleted
                        $status = intval($status) -1;
                        $bonus_status = 0;
                        //get all actions on this stage so the loot can be added up
                        $loot = $wpdb->get_results($wpdb->prepare("SELECT xp, gold, health, badges, groups, check_type, result
                        FROM {$aTable} 
                        WHERE uid = %d AND source_id = %d AND id >= %d
                        ORDER BY id ", $user_id, $task_id, $id), ARRAY_A);


                        foreach ($loot as $loot_row) {
                            $xp_task = $loot_row['xp'] + $xp_task;
                            $gold_task = $loot_row['gold'] + $gold_task;
                            $health_task = $loot_row['health'] + $health_task;
                            $badges_task = $loot_row['badges'];
                            $groups_task = $loot_row['groups'];
                            $check_type = $loot_row['check_type'];
                            $result = $loot_row['result'];

                            //set all posts submitted after this post to reset
                            if ($check_type === "blog") {
                                if(is_numeric($result)) {
                                    $post = array('ID' => intval($result), 'post_status' => 'reset');
                                    wp_update_post($post);
                                }
                            }

                            //combine any badges and groups earned into an array.  This should only be one badge.
                            $badge_task = unserialize($badges_task);
                            $group_task = unserialize($groups_task);

                            if (!is_array($badge_task)) {
                                if (is_numeric($badge_task)) {
                                    $badge_task = array($badge_task);
                                } else {
                                    $badge_task = array();
                                }
                            }
                            if (!is_array($group_task)) {
                                if (is_numeric($group_task)) {
                                    $group_task = array($group_task);
                                } else {
                                    $group_task = array();
                                }
                            }

                            $badge_array = array_unique(array_merge($badge_task, $badge_array));
                            $group_array = array_unique(array_merge($group_task, $group_array));
                            //END combine badges and groups
                        }
                    }
                    else if (!empty($bonus_status)) {
                        $bonus_status = $bonus_status -1;

                        //get status--don't change it because this is a bonus stage reset
                        $status = $wpdb->get_var($wpdb->prepare("SELECT status
                        FROM {$go_task_table_name}
                        WHERE uid = %d and post_id = %d
                        ORDER BY id DESC", $user_id, $task_id
                        ));


                        $bonus_stage_loot = $wpdb->get_results($wpdb->prepare("SELECT xp, gold, health, check_type
                        FROM {$aTable} 
                        WHERE uid = %d AND source_id = %d AND id >= %d AND result = %d
                        ORDER BY id DESC LIMIT 1", $user_id, $task_id, $id, $blog_post_id), ARRAY_A);


                            $xp_task = $bonus_stage_loot[0]['xp'];
                            $gold_task = $bonus_stage_loot[0]['gold'];
                            $health_task = $bonus_stage_loot[0]['health'];
                            /*
                            $gold_task = $loot_row['gold'] + $gold_task;
                            $health_task = $loot_row['health'] + $health_task;
                            $badges_task = $loot_row['badges'];
                            $groups_task = $loot_row['groups'];
                            $check_type = $loot_row['check_type'];
                            $result = $loot_row['result'];


                            //set all posts submitted after this post to reset
                            if ($check_type === "blog") {
                                if(is_numeric($result)) {
                                    $post = array('ID' => intval($result), 'post_status' => 'reset');
                                    wp_update_post($post);
                                }
                            }

                            //combine any badges and groups earned into an array.  This should only be one badge.
                            $badge_task = unserialize($badges_task);
                            $group_task = unserialize($groups_task);

                            if (!is_array($badge_task)) {
                                if (is_numeric($badge_task)) {
                                    $badge_task = array($badge_task);
                                } else {
                                    $badge_task = array();
                                }
                            }
                            if (!is_array($group_task)) {
                                if (is_numeric($group_task)) {
                                    $group_task = array($group_task);
                                } else {
                                    $group_task = array();
                                }
                            }

                            $badge_array = array_unique(array_merge($badge_task, $badge_array));
                            $group_array = array_unique(array_merge($group_task, $group_array));
                            //END combine badges and groups
                            */
                        //}


                        $post = array('ID' => intval($blog_post_id), 'post_status' => 'reset');
                        wp_update_post($post);
                    }else{
                        die();
                    }

                    //loot to be removed
                    $xp_task = ($xp_task) * -1;
                    $gold_task = ($gold_task) * -1;
                    $health_task = ($health_task) * -1;




                }

            }
            //below is for both resets and reset_stage

            //set class
            $class = array('reset');
            if (!empty($sent_xp) || !empty($sent_gold) || !empty($sent_health)) {
                $class[] = 'down';
            }

            $class = serialize($class);

            //update task table
            /*
            $wpdb->update($go_task_table_name,
                array('status' => $status, 'bonus_status' => $bonus_status, 'xp' => $xp_task, 'gold' => $gold_task, 'health' => $health_task, 'badges' => null, 'groups' => null, 'last_time' => $last_time, 'class' => $class ),//data
                array('uid' => $user_id, 'post_id' => $task_id),//where
                array('%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s'),//data format
                array('%d', '%d')//where data format
            );*/

            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$go_task_table_name} 
                    SET 
                        status = {$status}, 
                        bonus_status = {$bonus_status},
                        xp = GREATEST(({$xp_task} + xp), 0),
                        gold = GREATEST(({$gold_task} + gold ), 0),
                        health = GREATEST(({$health_task} + health ), 0),
                        last_time = '{$last_time}',
                        class = '{$class}'     
                    WHERE uid= %d AND post_id=%d ",
                    intval($user_id),
                    intval($task_id)
                )
            );

            //This returns the string that is sent as a message.  It doesn't do the database changes.
            $message = go_reset_message($message, $penalty, $sent_xp, $sent_gold, $sent_health, $xp_task, $gold_task, $health_task, $sent_badge_id, $sent_group_id, $badge_array, $group_array);

            //combine the penalty loot and task loot. This is what will be removed
            $xp = $sent_xp + $xp_task;
            $gold = $sent_gold + $gold_task;
            $health = $sent_health + $health_task;

            //if (!empty($badge_task)) {//if a badge was earned, merge them with the sent badge to be removed later
                $badge_ids = array_merge($badge_array, array($sent_badge_id));
           // }

           // if (!empty($group_task)) {//if a group was earned, merge it with the sent group to be removed later
                $group_ids = array_merge($group_array, array($sent_group_id));
           // }

        }
        else {//this is a regular message
            if(!empty($sent_badge_id)) {
                $badge_ids = array($sent_badge_id);
            }else{ $badge_ids = array();}

            if(!empty($sent_group_id)) {
                $group_ids = array($sent_group_id);
            }else{ $group_ids = array();}

            $xp = $sent_xp;
            $gold = $sent_gold;
            $health = $sent_health;

            $message = $sent_message;

        }


        ////START MESSAGE CONSTRUCTION
            //the results are combined for saving in the database as a serialized array
        $result = array();
        $result[] = $title;
        $result[] = $message;

        //store the badge and group toggles so later we know if they were awarded or taken.
        if ($loot_toggle && !empty($badge_ids)) {//if badges toggle is true and badges exist
            $result[] = "badges+";
            $badge_ids = go_add_badges($badge_ids, $user_id, true);//add badges
            $badge_ids = serialize($badge_ids);
        }else if (!$loot_toggle && !empty($badge_ids)) {//else if badges toggle is false and badges exist
            $result[] = "badges-";
            $badge_ids = go_remove_badges($badge_ids, $user_id, true);//remove badges
            $badge_ids = serialize($badge_ids);
        }else {
            $result[] = "badges0";
            $badge_ids = null;
        }

        //add to DB and then serialize for storage with the message
        if ($loot_toggle  && !empty($group_ids)) {//if groups toggle is true and groups exist
            $result[] = "groups+";
            go_add_groups($group_ids, $user_id, true);//add groups
            $group_ids = serialize($group_ids);
        }else if (!$loot_toggle && !empty($group_ids)) {//else if groups toggle is false and groups exist
            $result[] = "groups-";
            go_remove_groups($group_ids, $user_id, true);//remove groups
            $group_ids = serialize($group_ids);
        }else{
            $result[] = "groups0";
            $group_ids = null;
        }
        $result = serialize($result);

        if(empty($title) && empty($message) && empty($badge_ids) && empty($group_ids) && empty($xp) && empty($gold) && empty($health)){
            die();
        }

        //update actions
        go_update_actions($user_id, $type, $task_id, 1, null, null, $result, null, null, null, null, $xp, $gold, $health, $badge_ids, $group_ids, true);
        update_user_option($user_id, 'go_new_messages', true);
    }

}

/**
 *
 */
function go_messages_canned(){
    echo "<select class='go_messages_canned'>";
    echo "<option>Canned Messages</option>";
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
