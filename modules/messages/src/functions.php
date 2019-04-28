<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/21/18
 * Time: 6:04 PM
 */

global $go_debug;
if(!$go_debug) {
    add_filter('heartbeat_received', 'go_check_messages', 10, 2);
}

function go_check_messages($response = null , $data = null){
    global $wpdb;

    if ( empty( $data['go_heartbeat'] ) ) {
        $heartbeat = false;
    }else{
        $heartbeat = true;
    }

    ob_start();
    //on each page load, check if user has new messages
    $user_id =  get_current_user_id();
    $is_logged_in = is_user_logged_in();
    $is_new_messages = get_user_option('go_new_messages');

    $xp_abbr = get_option( "options_go_loot_xp_abbreviation" );
    $gold_abbr = get_option( "options_go_loot_gold_abbreviation" );
    $health_abbr = get_option( "options_go_loot_health_abbreviation" );

    if ($is_logged_in && $is_new_messages ){
        //get unread messages
        $go_actions_table_name = "{$wpdb->prefix}go_actions";
        $actions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *
			FROM {$go_actions_table_name}
			WHERE uid = %d and (action_type = %s or action_type = %s or action_type = %s or action_type = %s)  and stage = %d
			ORDER BY id DESC",
                $user_id,
                'message',
                'reset',
                'admin_notification',
                'feedback',
                1
            )
        );
        //turn them into noty
        //set them as read
        foreach ($actions as $action) {
            $type = $action->action_type;
            $post_id = $action->source_id;
            $result = $action->result;
            $result = unserialize($result);
            $title = $result[0];
            $message = $result[1];
            $xp = $action->xp;
            $gold = $action->gold;
            $health = $action->health;
            $badges = $action->badges;
            $badges = unserialize($badges);
            $groups = $action->groups;
            $groups = unserialize($groups);

            if ($type == 'admin_notification'){
                go_noty_message_generic('alert', '', $title, '15000');
            } else if ( $type == 'reset' && $type = 'reset_stage') {
                go_noty_message_generic('warning', $title, $message, '');
            }else{



                $xp_penalty = null;
                $xp_reward = null;
                $xp_loot = null;
                if($xp != 0){
                    $xp_loot = go_display_longhand_currency('xp', $xp);
                }
                if ($xp > 0) {
                    $xp_reward = $xp_loot.'<br>';
                } else if ($xp < 0) {
                    $xp_penalty = $xp_loot.'<br>';
                }

                $gold_penalty = null;
                $gold_reward = null;
                $gold_loot = null;
                if($gold != 0){
                    $gold_loot = go_display_shorthand_currency('gold', $gold, false, 'breaks');
                }
                if ($gold > 0) {
                    $gold_reward = $gold_loot;
                }
                else if ($gold < 0) {
                    $gold_penalty = $gold_loot;
                }


                $health_penalty = null;
                $health_reward = null;
                $health_loot = null;
                if ($health != 0){
                    $health_loot = go_display_longhand_currency('health', $health);
                }
                if ($health > 0) {
                    $health_reward = $health_loot.'<br>';
                }
                else if ($health < 0) {
                    $health_penalty = $health_loot.'<br>';
                }


                $badges_toggle = get_option('options_go_badges_toggle');
                if ($badges_toggle && !empty($badges)) {
                    $badge_dir = $result[2];

                    $badges_name = get_option('options_go_badges_name_plural');

                    $badges_names = array();
                    $badges_names[] = "<br><b>" . $badges_name . ":</b>";
                    foreach ($badges as $badge) {
                        $term = get_term($badge, "go_badges");
                        if (!empty($term)) {
                            $badge_name = $term->name;
                            $badges_names[] = $badge_name;
                        }
                    }

                    if ($badge_dir == "badges+") {
                        //message for awarding badges
                        $badge_award = implode("<br>", $badges_names).'<br>';
                        $badge_penalty = null;
                    } else if ($badge_dir == "badges-") {
                        //message for taking badges
                        //get all badge names
                        $badge_penalty = implode("<br>", $badges_names).'<br>';
                        $badge_award = null;
                    } else {
                        $badge_penalty = null;
                        $badge_award = null;
                    }


                } else {
                    $badge_penalty = null;
                    $badge_award = null;
                }

                if (!empty($groups)) {
                    $groups_dir = $result[3];
                    $groups_names = array();
                    $groups_names[] = "<br><b>Groups:</b>";
                    foreach ($groups as $group) {
                        $term = get_term($group, "user_go_groups");
                        if (!empty($term)) {
                            $group_name = $term->name;
                            $groups_names[] = $group_name;
                        }
                    }

                    if ($groups_dir == "groups+") {
                        //message for awarding badges
                        $group_award = implode("<br>", $groups_names).'<br>';
                        $group_penalty = null;
                    } else if ($groups_dir == "groups-") {
                        //message for taking badges
                        //get all badge names
                        $group_penalty = implode("<br>", $groups_names).'<br>';
                        $group_award = null;
                    } else {
                        $group_penalty = null;
                        $group_award = null;
                    }
                } else {
                    $group_penalty = null;
                    $group_award = null;
                }


                if (!empty($xp_reward) || !empty($gold_reward) || !empty($health_reward) || !empty($badge_award) || !empty($group_award)) {
                    $reward = "<div class='go_messages_loot go_messages_rewards'><h3>Reward:</h3>{$xp_reward}{$gold_reward}{$health_reward}{$badge_award}{$group_award}</div>";
                } else {
                    $reward = '';
                }

                if (!empty($xp_penalty) || !empty($gold_penalty) || !empty($health_penalty) || !empty($badge_penalty) || !empty($group_penalty)) {
                    //if (empty($post_id)){
                    $penalty = "<div class='go_messages_loot go_messages_penalties'><h3>Penalty:</h3>{$xp_penalty}{$gold_penalty}{$health_penalty}{$badge_penalty}{$group_penalty}</div>";
                    //}
                    //else{
                    //    $penalty = "<h4>Additional Penalty:</h4>{$xp_penalty}{$gold_penalty}{$health_penalty}{$badge_penalty}{$group_penalty}";
                    // }
                } else {
                    $penalty = '';
                }

                $message = "<div> {$message}</div><div>{$reward}{$penalty}</div>";

                go_noty_message_generic('alert', $title, $message, '');
            }

        }
        //set messages flag to read
        $wpdb->update(
            $go_actions_table_name,
            array(
                'stage' => 0 // integer (number)
            ),
            array(
                'uid' => $user_id,
                'action_type' => 'message',
                'stage' => 1

            ),
            array(
                '%d'	// value2
            ),
            array(
                '%d',
                '%s',
                '%d'
            )
        );

        $wpdb->update(
            $go_actions_table_name,
            array(
                'stage' => 0 // integer (number)
            ),
            array(
                'uid' => $user_id,
                'action_type' => 'reset',
                'stage' => 1

            ),
            array(
                '%d'	// value2
            ),
            array(
                '%d',
                '%s',
                '%d'
            )
        );
        $wpdb->update(
            $go_actions_table_name,
            array(
                'stage' => 0 // integer (number)
            ),
            array(
                'uid' => $user_id,
                'action_type' => 'admin_notification',
                'stage' => 1

            ),
            array(
                '%d'	// value2
            ),
            array(
                '%d',
                '%s',
                '%d'
            )
        );
        $wpdb->update(
            $go_actions_table_name,
            array(
                'stage' => 0 // integer (number)
            ),
            array(
                'uid' => $user_id,
                'action_type' => 'feedback',
                'stage' => 1

            ),
            array(
                '%d'	// value2
            ),
            array(
                '%d',
                '%s',
                '%d'
            )
        );
        update_user_option($user_id, 'go_new_messages', false);
    }
    $buffer = ob_get_contents();
    ob_end_clean();
    if ($heartbeat === false ){
        echo $buffer;
    }else {
        //$buffer = 'beat';
        $response['go_message'] = $buffer;
        return $response;
    }

}
add_action( 'wp_footer', 'go_check_messages' );
add_action('go_after_stage_change', 'go_check_messages');



?>