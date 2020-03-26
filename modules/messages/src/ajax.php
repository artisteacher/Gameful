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
                                                                <?php go_make_tax_select('go_badges', 'lightbox'); ?>

                                                            </td>
                                                            <?php
                                                        }
                                                        if ($go_groups_toggle) {
                                                            ?>
                                                            <td class="go-acf-field go-acf-field-true-false go_reward go_groups"
                                                                data-name="groups" data-type="true_false"
                                                                class="go_pink">
                                                                <?php go_make_tax_select('user_go_groups', 'lightbox'); ?>
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
                            <div style="padding: 20px 0; display: flex; justify-content: space-between;">
                            <div id="go_canned_wrapper"><?php go_messages_canned(); ?></div>
                            <?php
                            $tippy = '<span class="tooltip" data-tippy-content="This allows for creating information that only admin can see about a student. No loot can be assigned on a note." style="font-size: 1em;"> <span><i class="fa fa-info-circle"></i></span></span>';

                            ?>
                            <div id="go_note_wrapper"><input id="go_note" type="checkbox" name="go_note" value="note"> Make this a note. No message will show to the student.<?php echo $tippy; ?></div></div>
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


                            <?php
                            if ($go_xp_toggle || $go_gold_toggle || $go_health_toggle || $go_badges_toggle || $go_groups_toggle) {
                                // <input name="loot_toggle" type="checkbox" class="go-acf-switch-input go_messages_toggle_input">
                                /*
                                 * <div class="go-acf-switch"><span class="go-acf-switch-on" style="min-width: 36px;">Award</span><span class="go-acf-switch-off" style="min-width: 36px;">Penalty</span>
                                                <div class="go-acf-switch-slider"></div>
                                            </div>
                                 *///
                                ?>
                                <tr class="go_loot_radio">
                                    <th scope="row">Loot</th>
                                    <td>
                                <div class="go-acf-input go_loot_table">
                                    <div class="go-acf-true-false">
                                        <input value="0" type="hidden">
                                        <label>
                                            <div id="message_loot_toggle" style="display: flex; padding-top: 20px;">
                                                <span style="padding-left: 15px;"><label><input type="radio" name="message_loot_toggle" value="none" checked > None</label></span>
                                                <span style="padding-left: 15px;"><label><input type="radio" name="message_loot_toggle" value="remove" style="padding-left: 10px;"> Remove</label></span>
                                                <span style="padding-left: 15px;"><label><input type="radio" name="message_loot_toggle" value="add" style="padding-left: 10px;"> Add</label></span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                    </td>
                                </tr>
                            </table>
                                <div id="go_loot_table" class="go-acf-field go-acf-field-group" data-type="group" style="display: none;">
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
                                                                <th>
                                                                    <div class="go-acf-th">
                                                                        <label>Sections</label>
                                                                    </div>
                                                                </th>

                                                            </tr>

                                                            </thead>
                                                            <tbody>
                                                            <tr class="go-acf-row">
                                                                <?php
                                                                if ($go_badges_toggle) {
                                                                    ?>
                                                                    <td class="go-acf-field go-acf-field-true-false go_reward go_gold"
                                                                        data-name="gold" data-type="true_false">
                                                                        <?php go_make_tax_select('go_badges', 'lightbox'); ?>

                                                                    </td>
                                                                    <?php
                                                                }
                                                                if ($go_groups_toggle) {
                                                                    ?>
                                                                    <td class="go-acf-field go-acf-field-true-false go_reward go_gold"
                                                                        data-name="gold" data-type="true_false">
                                                                        <?php go_make_tax_select('user_go_groups', 'lightbox'); ?>
                                                                    </td>
                                                                    <?php
                                                                }

                                                                ?>
                                                                <td class="go-acf-field go-acf-field-true-false go_reward go_sections"
                                                                    data-name="gold" data-type="true_false">
                                                                    <?php go_make_tax_select('user_go_sections', 'lightbox'); ?>
                                                                </td>
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
function go_check_messages_ajax(){
    //$user_id = get_current_user_id();
    //check_ajax_referer( 'go_admin_messages');
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_check_messages_ajax' ) ) {
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
        $radio = get_option('options_go_messages_canned_'.$i.'_radio');
        $toggle = get_option('options_go_messages_canned_'.$i.'_toggle');
        $xp = get_option('options_go_messages_canned_'.$i.'_defaults_xp');
        $gold = get_option('options_go_messages_canned_'.$i.'_defaults_gold');
        $health = get_option('options_go_messages_canned_'.$i.'_defaults_health');

        $badge = get_option('options_go_messages_canned_'.$i.'_terms_badge');
        $group = get_option('options_go_messages_canned_'.$i.'_terms_group');
        $section = get_option('options_go_messages_canned_'.$i.'_terms_section');
        $badge_name = '';
        $group_name = '';
        $section_name = '';
        if($badge){
            $obj = get_term($badge);
            if (!empty($obj)) {
                $badge_name = $obj->name;
            }
        }
        if($group){
            $obj = get_term($group);
            if (!empty($group)) {
                $group_name = $obj->name;
            }
        }
        if($section){
            $obj = get_term($section);
            if (!empty($section)) {
                $section_name = $obj->name;
            }
        }

        if(!$radio){//sets radio for old style toggles
            if($xp || $gold || $health ){
                if($toggle) {
                    $radio = 'add';
                }else{
                    $radio = 'remove';
                }
            }
        }

        echo '<option class="go_messages_option" value="'.$i.'" data-message="'.$message.'" data-radio="'.$radio.'" data-xp="'.$xp.'" data-gold="'.$gold.'" data-health="'.$health.'" data-badge="'.$badge.'" data-group="'.$group.'" data-section="'.$section.'" data-badge_name="'.$badge_name.'" data-group_name="'.$group_name.'" data-section_name="'.$section_name.'" data-title="'.$title.'">'.$title.'</option>';
        $i++;
    }
    echo "</select>";

}
