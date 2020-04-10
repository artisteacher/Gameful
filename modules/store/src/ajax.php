<?php

function go_buy_item() {
    //global $wpdb;
    //check_ajax_referer( 'go_buy_item' );
    //$user_id = get_current_user_id();

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_buy_item' ) ) {
        echo "refresh";
        die( );
    }

    $user_id = ( ! empty( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0 ); // User id posted from ajax function
    $is_logged_in = ! empty( $user_id ) && $user_id > 0  || is_user_member_of_blog( $user_id ) ? true : false;
    if (!$is_logged_in){
        //echo 'Error: You must be logged in to use the store.';
        echo "<script> new Noty({
                type: 'info',
                layout: 'topRight',
                text: 'Error: You must be logged in to use the store.',
                visibilityControl: true,
                theme: 'sunset'
                }).show();parent.window.$.featherlight.current().close();</script>";
        die();
    }


    $post_id = ( ! empty( $_POST["the_id"] ) ? (int) $_POST["the_id"] : 0 );
    $custom_fields = go_post_meta( $post_id );

    $purchase_count = go_get_purchase_count($post_id, $user_id, $custom_fields);
    $purchase_limit = go_get_purchase_limit($post_id, $user_id, $custom_fields, $purchase_count);
    if (!is_int($purchase_limit)){
        $purchase_limit = 0;
    }
    $qty = ( ! empty( $_POST['qty'] ) && (int) $_POST['qty'] > 0 ? (int) $_POST['qty'] : 1 );
    if ($qty > $purchase_limit){
        echo "<script> new Noty({
                type: 'error',
                layout: 'topRight',
                text: 'Error: You exceeded your loot available. Try a lower quantity.' ,
                theme: 'sunset',
                visibilityControl: true
                }).show();parent.window.$.featherlight.current().close();</script>";
        die();
    }

    $store_limit_toggle = ( ($custom_fields['go-store-options_limit_toggle'][0] == true ) ? $custom_fields['go-store-options_limit_toggle'][0] : null );
    if ($store_limit_toggle) {
        $purchase_remaining_max = go_get_purchase_limit($post_id, $user_id, $custom_fields, null);
        if (!is_int($purchase_remaining_max)){
            //$message = $purchase_remaining_max;
            $purchase_remaining_max = 0;

        }

        if ($qty > $purchase_remaining_max) {
            //echo 'Error: You attempted to buy more than your current limit. Try again.';
            echo "<script> new Noty({
                type: 'info',
                layout: 'topRight',
                text: 'Error: You attempted to buy more than your current limit.',
                theme: 'sunset',
                visibilityControl: true
                }).show();parent.window.$.featherlight.current().close();</script>";
            die();
        }
    }

    $the_title = go_the_title($post_id);
    $xp = 0;
    $gold = 0;
    $health = 0;

    $store_abs_cost_xp = (isset($custom_fields['go_loot_loot_xp'][0]) ?  $custom_fields['go_loot_loot_xp'][0] : null);
    if (get_option( 'options_go_loot_xp_toggle' ) && $store_abs_cost_xp > 0){
        $store_toggle_xp = (isset($custom_fields['go_loot_reward_toggle_xp'][0]) ?  $custom_fields['go_loot_reward_toggle_xp'][0] : null);
        if ($store_toggle_xp == false){
            $xp = $qty * ($store_abs_cost_xp) * -1;
        }
        else{
            $xp = $qty * ($store_abs_cost_xp);
        }
    }

    $store_abs_cost_gold = (isset($custom_fields['go_loot_loot_gold'][0]) ?  $custom_fields['go_loot_loot_gold'][0] : null);
    if (get_option( 'options_go_loot_gold_toggle' )  && $store_abs_cost_gold > 0){
        $store_toggle_gold = (isset($custom_fields['go_loot_reward_toggle_gold'][0]) ?  $custom_fields['go_loot_reward_toggle_gold'][0] : null);
        if ($store_toggle_gold == false){
            $gold = $qty * ($store_abs_cost_gold) * -1;
        }
        else{
            $gold = $qty * ($store_abs_cost_gold);
        }
    }

    $store_abs_cost_health = (isset($custom_fields['go_loot_loot_health'][0]) ?  $custom_fields['go_loot_loot_health'][0] : null);
    if (get_option( 'options_go_loot_health_toggle' ) && $store_abs_cost_health > 0){
        $store_toggle_health = (isset($custom_fields['go_loot_reward_toggle_health'][0]) ?  $custom_fields['go_loot_reward_toggle_health'][0] : null);
        if ($store_toggle_health == false){
            $health = $qty * ($store_abs_cost_health) * -1;
        }
        else{
            $health = $qty * ($store_abs_cost_health);
        }

    }

    ob_start();

    //BADGES
    $badge_ids = (isset($custom_fields['go_purch_reward_badges'][0]) ?  $custom_fields['go_purch_reward_badges'][0] : null);
    if (!empty($badge_ids)) {
        go_add_badges ($badge_ids, $user_id, true);
        $badge_ids = serialize($badge_ids);
    }
    //GROUPS
    $group_ids = (isset($custom_fields['go_purch_reward_groups'][0]) ?  $custom_fields['go_purch_reward_groups'][0] : null);
    if (!empty($group_ids)) {
        go_add_groups ($group_ids, $user_id, true);
        $group_ids = serialize($badge_ids);
    }


    go_update_actions( $user_id, 'store',  $post_id, $qty, null, null, 'purchase', null, null, null, null,  $xp, $gold, $health, $badge_ids, $group_ids, true);

    $go_admin_message = (isset($custom_fields['go-store-options_admin_notifications'][0]) ?  $custom_fields['go-store-options_admin_notifications'][0] : null);

    if ($go_admin_message){

        $username = go_get_user_display_name($user_id);
        $result = array();
        $result[] = $username . " bought " . $the_title;
        $result[] = "";

        $result = serialize($result);
        //$admin_users = get_option('options_go_admin_user_notifications');
        $args = array(
            'role'    => 'administrator',
            'orderby' => 'user_nicename',
            'order'   => 'ASC'
        );
        $admin_users = get_users( $args );

        foreach ($admin_users as $admin_user) {
            //go_update_actions(intval($admin_user), 'admin_notification', null , 1, null, null, $result, null, null, null, null, $xp, $gold, $health, $badge_ids, $group_ids, 'admin');
            global $wpdb;
            $go_actions_table_name = "{$wpdb->prefix}go_actions";
            //$time = date( 'Y-m-d G:i:s', current_time( 'timestamp', 0 ) )
            $time = current_time('mysql');
            $admin_user_id = $admin_user->ID;
            $wpdb->insert($go_actions_table_name, array('uid' => $admin_user_id, 'action_type' => 'admin_notification', 'source_id' => null, 'TIMESTAMP' => $time, 'stage' => 1, 'bonus_status' => null, 'check_type' => null, 'result' => $result, 'quiz_mod' => null, 'late_mod' => null, 'timer_mod' => null, 'global_mod' => null, 'xp' => null, 'gold' => null, 'health' => null, 'badges' => null, 'groups' => null, 'xp_total' => null, 'gold_total' => null, 'health_total' => null));


            update_user_option($admin_user_id, 'go_new_messages', true);
        }
        //go_update_actions($user_id, 'message', null , 1, null, null, $result, null, null, null, null, $xp, $gold, $health, $badge_ids, $group_ids, false);
        //update_user_option($user_id, 'go_new_messages');
    }
    $time = current_time('m-d-Y g:i A');

    echo "<script> new Noty({
    type: 'info',
    layout: 'topRight',
    text: '<h2>Receipt</h2><br>Item: " . addslashes($the_title) . " <br>Quantity: " . addslashes($qty) . " <br>Time: " . addslashes($time) . "',
    theme: 'sunset',
    visibilityControl: true,
    callbacks: {
                    beforeShow: function() { go_noty_close_oldest();},
                }
    //timeout: '3000'
    
}).show();
</script>";

    // stores the contents of the buffer and then clears it
    $buffer = ob_get_contents();

    ob_end_clean();

    //$locked_content_toggle = get_option( 'options_go_purch_reward_go_locked_content' );
    $locked_content_toggle = (isset($custom_fields['go_purch_reward_go_locked_content'][0]) ?  $custom_fields['go_purch_reward_go_locked_content'][0] : false);
    $buffer2 = null;
    if($locked_content_toggle){
        $locked_content = '';
        if($locked_content_toggle ==='custom') {

            // $locked_content = get_option( 'options_go_purch_reward_purchased_message' );
            $locked_content = (isset($custom_fields['go_purch_reward_purchased_message'][0]) ? $custom_fields['go_purch_reward_purchased_message'][0] : null);
            $buffer2 = apply_filters('go_awesome_text', $locked_content);
        }
        else if($locked_content_toggle =='avatar') {

            ob_start();

            //load custom content here
            echo "<div id='go_purchased_content' style='display: block;'>";
            echo "<h3>Change Your Avatar</h3>";
            $media_id = get_user_option('go_avatar', $user_id);
            go_upload_check_blog($media_id, 'go_this_avatar', 'image', 'go_this_avatar');
            echo '<div id="go_change_avatar" style="display: none;"><button class="go_change_avatar" type="button" onclick="go_change_avatar( this);" style="position: relative; z-index: 1;">Change Avatar</button></div>';
            echo "</div>";


            $buffer2 = ob_get_contents();
            ob_end_clean();
        }
    }
    //$buffer2 = "yes";

    if(empty($buffer2)){
        $buffer2 = "<script>jQuery.featherlight.close();</script>";
    }

    echo json_encode(
        array(
            'json_status' => 'success',
            'messages' => $buffer,
            'content' => $buffer2
        )
    );
    die();
}

// Main Lightbox Ajax Function
function go_the_lb_ajax() {

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_the_lb_ajax');
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_the_lb_ajax' ) ) {
        echo "refresh";
        die( );
    }


    $post_id = (int) $_POST['the_item_id'];
    $skip_locks = (isset($_POST['skip_locks']) ?  $_POST['skip_locks'] : false);

    $the_title = go_the_title($post_id);
    $custom_fields = go_post_meta($post_id);

    $item_content = (isset($custom_fields['go_store_item_desc'][0]) ?  $custom_fields['go_store_item_desc'][0] : null);
    $the_content  = apply_filters( 'the_content', $item_content );

    $user_id = get_current_user_id();
    $is_logged_in = ! empty( $user_id ) && $user_id > 0 ? true : false;
    $is_admin = go_user_is_admin(  );

    ob_start();

    $unlock_flag = true;
    echo "<div class='go_store_lightbox_container'>";
    if($skip_locks == false) {

        /*if ($is_admin) {
            echo edit_post_link('edit', null, null, $post_id);
        }*/
        $task_is_locked = false;
        if ($custom_fields['go_lock_toggle'][0] == true || $custom_fields['go_sched_toggle'][0] == true) {
            $task_is_locked = go_task_locks($post_id, false, $user_id, "Item", $custom_fields);
        }
        $this1 = ob_get_contents();
        $go_password_lock = (isset($custom_fields['go_password_lock'][0]) ? $custom_fields['go_password_lock'][0] : null);
        if ($go_password_lock == true) {
            $task_is_locked = true;
        }
        //Get option (show password field) from custom fields
        if ($go_password_lock && $is_logged_in) {
            //Show password unlock
            ?>
            <div class='go_lock go_store_lock'><h3>Unlock</h3><input id='go_store_password_result' class='clickable' type='password' placeholder='Enter Password'>
                <div id="go_store_buttons" style="overflow: auto; position: relative; padding: 10px; min-height: 40px;">
                    <p id='go_store_error_msg' style='display: none; color: red;'></p>
                    <button style="float: right; cursor: pointer;" id="go_store_pass_button" class="progress"
                            check_type="unlock_store" button_type="continue" admin_lock="true">Submit
                    </button>
                </div>
            </div>
            <?php
        } else if ($task_is_locked == true && $is_logged_in) { //change this code to show admin override box
            //if ($is_logged_in) { //add of show password field is on
            ?>
            <div id="go_store_admin_override" style="overflow: auto; width: 100%;">
                <div style="float: right; font-size: .8em;">Admin Override</div>
            </div>

            <div class='go_lock go_store_lock go_lock go_password' style="display:none;"><h3>Admin Override</h3><input id='go_store_password_result' class='clickable' type='password' placeholder='Enter Password'>
                <div id="go_store_buttons" style="overflow: auto; position: relative; padding: 10px; min-height: 40px;">
                    <p id='go_store_error_msg' style='display: none; color: red;'></p>
                    <button style="float: right; cursor: pointer;" id="go_store_pass_button" class="progress"
                            check_type="unlock_store" button_type="continue" admin_lock="true">Submit
                    </button>
                </div>
            </div>
            <?php
        }
        ?>
        <script>
            jQuery(document).ready(function () {
                jQuery('#go_store_pass_button').one("click", function (e) {
                    go_store_password(<?php echo $post_id; ?>);
                });
            });
        </script>
        <?php

        //$task_is_locked = go_display_locks($post_id, $user_id, $is_admin, 'item', $badge_name, $custom_fields, $is_logged_in, 'Item');

    }
    else{//skip locks is true--this is a request from the password field
        //check the password and return an error
        //or return the store item
        $result = (!empty($_POST['result']) ? (string)$_POST['result'] : ''); // Contains the result from the password field
        $result = go_lock_password_validate($result, $custom_fields);
        if ($result == 'password' || $result ==  'master password') {
            //set unlock flag
            $unlock_flag = 'password_valid';
            //the password is correct so just continue
        }
        else {//the password is invalid
            $unlock_flag = 'bad_password';
        }
    }
    if (!$task_is_locked) {


        $store_abs_cost_xp = (isset($custom_fields['go_loot_loot_xp'][0]) ? $custom_fields['go_loot_loot_xp'][0] : null);
        if (get_option('options_go_loot_xp_toggle') && $store_abs_cost_xp > 0) {
            $xp_on = true;
            $xp_name = get_option('options_go_loot_xp_name');
            //$xp_abbr         = get_option( 'options_go_loot_xp_abbreviation' );
            //$user_xp = go_return_points( $user_id );
            $store_toggle_xp = (isset($custom_fields['go_loot_reward_toggle_xp'][0]) ? $custom_fields['go_loot_reward_toggle_xp'][0] : null);
            $store_cost_xp = go_display_shorthand_currency('xp', $store_abs_cost_xp);

        } else {
            $xp_on = false;
        }

        $store_abs_cost_gold = (isset($custom_fields['go_loot_loot_gold'][0]) ? $custom_fields['go_loot_loot_gold'][0] : null);
        if (get_option('options_go_loot_gold_toggle') && $store_abs_cost_gold > 0) {
            $gold_on = true;
            $gold_name = go_get_gold_name();
            $store_toggle_gold = (isset($custom_fields['go_loot_reward_toggle_gold'][0]) ? $custom_fields['go_loot_reward_toggle_gold'][0] : null);
            $store_cost_gold = go_display_shorthand_currency('gold', $store_abs_cost_gold);
        } else {
            $gold_on = false;
        }

        $store_abs_cost_health = (isset($custom_fields['go_loot_loot_health'][0]) ? $custom_fields['go_loot_loot_health'][0] : null);
        if (get_option('options_go_loot_health_toggle') && $store_abs_cost_health > 0) {
            $health_on = true;
            $health_name = get_option('options_go_loot_health_name');
            $store_toggle_health = (isset($custom_fields['go_loot_reward_toggle_health'][0]) ? $custom_fields['go_loot_reward_toggle_health'][0] : null);
            $store_cost_health = go_display_shorthand_currency('health', $store_abs_cost_health);
        } else {
            $health_on = false;
        }

        $purchase_count = go_get_purchase_count($post_id, $user_id, $custom_fields);

        $store_limit_toggle = (($custom_fields['go-store-options_limit_toggle'][0] == true) ? $custom_fields['go-store-options_limit_toggle'][0] : null);
        if ($store_limit_toggle) {
            $store_limit = (($custom_fields['go-store-options_limit_num'][0] == true) ? $custom_fields['go-store-options_limit_num'][0] : null);
        }
        $purchase_remaining_max = go_get_purchase_limit($post_id, $user_id, $custom_fields, $purchase_count);
        if (!is_int($purchase_remaining_max)){
            $message = $purchase_remaining_max;
            $purchase_remaining_max = 0;

        }

        $badges_toggle = get_option('options_go_badges_toggle');
        if ($badges_toggle) {
            $badges = (($custom_fields['go_purch_reward_badges'][0] == true) ? $custom_fields['go_purch_reward_badges'][0] : null);
            $badges = unserialize($badges);
        }
        $groups = (($custom_fields['go_purch_reward_groups'][0] == true) ? $custom_fields['go_purch_reward_groups'][0] : null);
        $groups = unserialize($groups);


        echo '<div id="light" class="top_content">';

        echo "<div class='text_wrapper'>";
        //title
        echo "<h1>{$the_title}</h1>";

        //description
        if(!empty($the_content)) {
            echo '<div id="go_store_description" >' . $the_content . '</div>';
        }
        echo "</div>";


        }
        echo "</div>";//end loot and top content

    //loot
    echo "<div id='go_store_loot'>";
    if (($xp_on && $store_toggle_xp == false) || ($gold_on && $store_toggle_gold == false) || ($health_on && $store_toggle_health == false)) {
        echo "<div class='go_cost loot_container'><div id='gp_store_minus' class='go_store_round_button'><div>–</div></div><div class='go_store_loot'>";
        if ($xp_on && $store_toggle_xp == false) {
            echo '<div class="loot-box down">' . $store_cost_xp . '</div>';
        }
        if ($gold_on && $store_toggle_gold == false) {
            echo '<div class="loot-box down">' . $store_cost_gold .  '</div>';
        }
        if ($health_on && $store_toggle_health == false) {
            echo '<div class="loot-box down">' . $store_cost_health . '</div>';
        }

        echo "</div></div>";
    }
    //

    if (($xp_on && $store_toggle_xp == true) || ($gold_on && $store_toggle_gold == true) || ($health_on && $store_toggle_health == true) || (!empty($badges)) || (!empty($groups))) {
        echo "<div class='go_reward loot_container'><div id='gp_store_plus' class='go_store_round_button'><div>+</div></div><div class='go_store_loot'>";
        if ($xp_on && $store_toggle_xp == true) {
            echo '<div class="loot-box up">' . $store_cost_xp .  '</div>';
        }
        if ($gold_on && $store_toggle_gold == true) {
            echo '<div class="loot-box up">' . $store_cost_gold . '</div>';
        }
        if ($health_on && $store_toggle_health == true) {
            echo '<div class="loot-box up">' . $store_cost_health . '</div>';
        }


        if (!empty($badges) || !empty($groups)) {
            echo '<div id="go_badges_groups" style="display: flex; flex-wrap: wrap">';
            if (!empty($badges)) {
                $badges_name_plural = get_option('options_go_badges_name_plural');
                echo '<div id="go_store_badges" style="padding:10px;"><b>' . $badges_name_plural . '</b>';
                foreach ($badges as $badge) {
                    $term = get_term($badge);
                    $name = $term->name;
                    echo '<br>' . $name;
                }
                echo '</div>';
            }

            if (!empty($groups)) {
                echo '<div id="go_store_groups" style="padding:10px;"><b>Groups</b>';
                foreach ($groups as $group) {
                    $term = get_term($group);
                    $name = $term->name;
                    echo '<br>' . $name . '</br>';
                }
                echo '</div>';
            }
            echo "</div>";
        }

        echo "</div></div>";//close the reward container
        echo "</div>";


        ?>
        <div class="go_store_actions" >
            <?php
            $store_multiple_toggle = (isset($custom_fields['go-store-options_multiple'][0]) ? $custom_fields['go-store-options_multiple'][0] : null);

            if ($purchase_remaining_max > 0 && $store_multiple_toggle) {
                ?>

                <div class="quantity_container">Qty: <input class="go_qty" type="number" value="1" disabled="disabled">
                </div>
                <?php
            }
            ?>

            <div id="go_purch_limits">
                <?php
                //$store_limit_duration = false;
                $store_limit_frequency = ucwords( ($custom_fields['go-store-options_limit_toggle'][0] == true ) ? $custom_fields['go-store-options_limit_frequency'][0] : null );

                if ($store_limit_frequency == 'Total') {
                    $var1 = ' ';
                } else {
                    $var1 = ' / ';
                }

                if ($store_limit_toggle) {
                    ?>
                    <div id="golb-fr-purchase-limit"
                         val="<?php echo(!empty($purchase_remaining_max) ? $purchase_remaining_max : 0); ?>"><?php echo(($store_limit_toggle) ? "Limit {$store_limit}{$var1}{$store_limit_frequency}" : 'No limit'); ?></div>
                    <?php
                }
                ?>
                <div id="golb-purchased">
                    <?php
                    if (is_null($purchase_count)) {
                        echo 'Quantity purchased: 0';
                    } else {
                        echo "Quantity purchased: {$purchase_count}";
                    }
                    ?>
                </div>
            </div>

            <?php
            if ($purchase_remaining_max > 0) {
                ?>
                <button id="golb-fr-buy" class="buy_button"
                     onclick="goBuytheItem( '<?php echo $post_id; ?>', '<?php echo $purchase_count ?>' ); this.removeAttribute( 'onclick' );"><?php
                    $custom_toggle = ( ($custom_fields['go-store-options_custom_button_text_toggle'][0] == true ) ? $custom_fields['go-store-options_custom_button_text_toggle'][0] : false );
                    if ($custom_toggle){
                        $custom_button = ( ($custom_fields['go-store-options_custom_button_text_button_text'][0] == true ) ? $custom_fields['go-store-options_custom_button_text_button_text'][0] : 'Buy' );
                    }else{
                        $custom_button = "Buy";
                    }
                    echo $custom_button; ?>
                </button>
                <?php
            }
            if ($purchase_remaining_max == 0) {
                ?>
                <div class="error"><?php echo $message; ?></div>
                <?php
            }
            ?>

        </div></div>

        <?php
    }
    echo "</div>";
    $store_lightbox_html = ob_get_contents();
    ob_end_clean();

    echo json_encode(array('json_status' => $unlock_flag, 'html' => $store_lightbox_html));
    die;

}


function go_change_avatar(){
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_the_lb_ajax');
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_change_avatar' ) ) {
        echo "refresh";
        die( );
    }

    $media_id = (int) $_POST['media_id'];
    $user_id = get_current_user_id();
    update_user_option( $user_id, 'go_avatar', $media_id, false );

    $url = wp_get_attachment_image_src(  $media_id, 'thumbnail' );
   // $url = (isset($url) ?  $url : wp_get_attachment_image_src(  $media_id, 'full' ));

    echo $url[0];
    die();

}