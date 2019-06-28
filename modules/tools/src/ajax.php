<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/1/18
 * Time: 9:18 PM
 */
function go_reset_all_users(){
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_reset_all_users' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_reset_all_users' ) ) {
        echo "refresh";
        die( );
    }
    global $wpdb;
    $loot_table  = $wpdb->prefix . 'go_loot';
    $wpdb->query("TRUNCATE TABLE $loot_table");

    $tasks_table  = $wpdb->prefix . 'go_tasks';
    $wpdb->query("TRUNCATE TABLE $tasks_table");

    $actions_table  = $wpdb->prefix . 'go_actions';
    $wpdb->query("TRUNCATE TABLE $actions_table");
    echo "reset";
    die();

}

/*
function go_upgade4 (){
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_upgade4' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_upgade4' ) ) {
        echo "refresh";
        die( );
    }

    global $wpdb;
    $go_posts_table = "{$wpdb->prefix}posts";
    $tasks = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT ID 
			FROM {$go_posts_table} 
			WHERE post_type = %s
			ORDER BY id DESC",
            'tasks'
        )
    );

    foreach ($tasks as $task) {
        $id = $task->ID;
        //echo $id;
        $custom_fields = get_post_custom($id);
        update_post_meta($id, 'go_stages', 3);
        $message1 = (isset($custom_fields['go_mta_quick_desc'][0]) ? $custom_fields['go_mta_quick_desc'][0] : null);
        update_post_meta($id, 'go_stages_0_content', $message1);
        $message2 = (isset($custom_fields['go_mta_accept_message'][0]) ? $custom_fields['go_mta_accept_message'][0] : null);
        update_post_meta($id, 'go_stages_1_content', $message2);
        $message3 = (isset($custom_fields['go_mta_complete_message'][0]) ? $custom_fields['go_mta_complete_message'][0] : null);
        update_post_meta($id, 'go_stages_2_content', $message3);

        //get post meta
        //copy message 1
        //copy message 2
        //copy message 3


        //STAGE 1
        $quiz_toggle = (isset($custom_fields['go_mta_test_encounter_lock'][0]) ? $custom_fields['go_mta_test_encounter_lock'][0] : null);
        $url_toggle = (isset($custom_fields['go_mta_encounter_url_key'][0]) ? $custom_fields['go_mta_encounter_url_key'][0] : null);
        $upload_toggle = (isset($custom_fields['go_mta_encounter_upload'][0]) ? $custom_fields['go_mta_encounter_upload'][0] : null);
        $password_serial = (isset($custom_fields['go_mta_encounter_admin_lock'][0]) ? $custom_fields['go_mta_encounter_admin_lock'][0] : null);
        $password_array = unserialize($password_serial);
        $password_toggle = $password_array[0];
        $password = $password_array[1];
        if ($quiz_toggle) {
            $quiz = (isset($custom_fields['go_mta_test_encounter_lock_fields'][0]) ? $custom_fields['go_mta_test_encounter_lock_fields'][0] : null);
            update_post_meta($id, 'go_stages_0_quiz', $quiz);
            update_post_meta($id, 'go_stages_0_check', 'quiz');
        } else if ($url_toggle) {
            update_post_meta($id, 'go_stages_0_check', 'URL');
        } else if ($upload_toggle) {
            update_post_meta($id, 'go_stages_0_check', 'upload');
        } else if ($password_toggle) {
            update_post_meta($id, 'go_stages_0_check', 'password');
            update_post_meta($id, 'go_stages_0_password', $password);
        } else {
            update_post_meta($id, 'go_stages_0_check', 'none');
        }

        //STAGE 2
        $quiz_toggle = (isset($custom_fields['go_mta_test_accept_lock'][0]) ? $custom_fields['go_mta_test_accept_lock'][0] : null);
        $url_toggle = (isset($custom_fields['go_mta_accept_url_key'][0]) ? $custom_fields['go_mta_accept_url_key'][0] : null);
        $upload_toggle = (isset($custom_fields['go_mta_accept_upload'][0]) ? $custom_fields['go_mta_accept_upload'][0] : null);
        $password_serial = (isset($custom_fields['go_mta_accept_admin_lock'][0]) ? $custom_fields['go_mta_accept_admin_lock'][0] : null);
        $password_array = unserialize($password_serial);
        $password_toggle = $password_array[0];
        $password = $password_array[1];
        if ($quiz_toggle) {
            $quiz = (isset($custom_fields['go_mta_test_accept_lock_fields'][0]) ? $custom_fields['go_mta_test_accept_lock_fields'][0] : null);
            update_post_meta($id, 'go_stages_1_quiz', $quiz);
            update_post_meta($id, 'go_stages_1_check', 'quiz');
        } else if ($url_toggle) {
            update_post_meta($id, 'go_stages_1_check', 'URL');
        } else if ($upload_toggle) {
            update_post_meta($id, 'go_stages_1_check', 'upload');
        } else if ($password_toggle) {
            update_post_meta($id, 'go_stages_1_check', 'password');
            update_post_meta($id, 'go_stages_1_password', $password);
        } else {
            update_post_meta($id, 'go_stages_1_check', 'none');
        }

        //STAGE 3
        $quiz_toggle = (isset($custom_fields['go_mta_test_completion_lock'][0]) ? $custom_fields['go_mta_test_completion_lock'][0] : null);
        $url_toggle = (isset($custom_fields['go_mta_completion_url_key'][0]) ? $custom_fields['go_mta_completion_url_key'][0] : null);
        $upload_toggle = (isset($custom_fields['go_mta_completion_upload'][0]) ? $custom_fields['go_mta_completion_upload'][0] : null);
        $password_serial = (isset($custom_fields['go_mta_completion_admin_lock'][0]) ? $custom_fields['go_mta_completion_admin_lock'][0] : null);
        $password_array = unserialize($password_serial);
        $password_toggle = $password_array[0];
        $password = $password_array[1];
        if ($quiz_toggle) {
            $quiz = (isset($custom_fields['go_mta_test_completion_lock_fields'][0]) ? $custom_fields['go_mta_test_completion_lock_fields'][0] : null);
            update_post_meta($id, 'go_stages_2_quiz', $quiz);
            update_post_meta($id, 'go_stages_2_check', 'quiz');
        } else if ($url_toggle) {
            update_post_meta($id, 'go_stages_2_check', 'URL');
        } else if ($upload_toggle) {
            update_post_meta($id, 'go_stages_2_check', 'upload');
        } else if ($password_toggle) {
            update_post_meta($id, 'go_stages_2_check', 'password');
            update_post_meta($id, 'go_stages_2_password', $password);
        } else {
            update_post_meta($id, 'go_stages_2_check', 'none');
        }

        $three_switch = (isset($custom_fields['go_mta_three_stage_switch'][0]) ? $custom_fields['go_mta_three_stage_switch'][0] : false);

        if ($three_switch != 'on') {
            update_post_meta($id, 'go_stages', 4);
            $message4 = (isset($custom_fields['go_mta_mastery_message'][0]) ? $custom_fields['go_mta_mastery_message'][0] : null);
            update_post_meta($id, 'go_stages_3_content', $message4);
        }


        $five_switch = (isset($custom_fields['go_mta_five_stage_switch'][0]) ? $custom_fields['go_mta_five_stage_switch'][0] : null);

        if ($three_switch != 'on' && $five_switch == 'on') {
            update_post_meta($id, 'bonus_switch', 1);
            $message5 = (isset($custom_fields['go_mta_repeat_message'][0]) ? $custom_fields['go_mta_repeat_message'][0] : null);
            update_post_meta($id, 'go_bonus_stage_content', $message5);
            //STAGE 5 --the check for understandings from stage 4 in v3 go to stage 5 in v4
            $quiz_toggle = (isset($custom_fields['go_mta_test_mastery_lock'][0]) ? $custom_fields['go_mta_test_mastery_lock'][0] : null);
            $url_toggle = (isset($custom_fields['go_mta_mastery_url_key'][0]) ? $custom_fields['go_mta_mastery_url_key'][0] : null);
            $upload_toggle = (isset($custom_fields['go_mta_mastery_upload'][0]) ? $custom_fields['go_mta_mastery_upload'][0] : null);
            $password_serial = (isset($custom_fields['go_mta_mastery_admin_lock'][0]) ? $custom_fields['go_mta_mastery_admin_lock'][0] : null);
            $password_array = unserialize($password_serial);
            $password_toggle = $password_array[0];
            $password = $password_array[1];
            if ($quiz_toggle) {
                $quiz = (isset($custom_fields['go_mta_test_mastery_lock_fields'][0]) ? $custom_fields['go_mta_test_mastery_lock_fields'][0] : null);
                update_post_meta($id, 'go_bonus_stage_quiz', $quiz);
                update_post_meta($id, 'go_bonus_stage_check', 'quiz');
            } else if ($url_toggle) {
                update_post_meta($id, 'go_bonus_stage_check', 'URL');
            } else if ($upload_toggle) {
                update_post_meta($id, 'go_bonus_stage_check', 'upload');
            } else if ($password_toggle) {
                update_post_meta($id, 'go_bonus_stage_check', 'password');
                update_post_meta($id, 'go_bonus_stage_password', $password);
            } else {
                update_post_meta($id, 'go_bonus_stage_check', 'none');
            }
        }

        $update_loot = ( ! empty( $_POST['loot'] ) ? $_POST['loot'] : 0 );
        if ($update_loot == 'true'){

            //update task loot
            $presets  = (isset($custom_fields['go_presets'][0]) ?  $custom_fields['go_presets'][0] : null);
            $presets = unserialize($presets);
            $points = $presets['points'];
            $gold = $presets['currency'];

            $xpe = $points[0];
            $xp1 = $points[1];
            $xp2 = $points[2];
            $xp3 = $points[3];
            $xpb = $points[4];

            $golde = $gold[0];
            $gold1 = $gold[1];
            $gold2 = $gold[2];
            $gold3 = $gold[3];
            $goldb = $gold[4];

            //encounter
            update_post_meta($id, 'go_entry_rewards_gold', $golde);
            update_post_meta($id, 'go_entry_rewards_xp', $xpe);

            //stage 1
            update_post_meta($id, 'go_stages_0_rewards_gold', $gold1);
            update_post_meta($id, 'go_stages_0_rewards_xp', $xp1);

            //stage 2
            update_post_meta($id, 'go_stages_1_rewards_gold', $gold2);
            update_post_meta($id, 'go_stages_1_rewards_xp', $xp2);

            //stage 3
            update_post_meta($id, 'go_stages_2_rewards_gold', $gold3);
            update_post_meta($id, 'go_stages_2_rewards_xp', $xp3);


            //bonus stage/ stage 5
            update_post_meta($id, 'go_bonus_stage_rewards_gold', $goldb);
            update_post_meta($id, 'go_bonus_stage_rewards_xp', $xpb);
        }

    }


//store content
    $store_items = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT ID 
			FROM {$go_posts_table} 
			WHERE post_type = %s
			ORDER BY id DESC",
            'go_store'
        )
    );
    foreach ($store_items as $store_item){
        $id = $store_item->ID;
        $custom_fields = get_post_custom($id);
        $store_description = $my_post_content = apply_filters('the_content', get_post_field('post_content', $id));
        update_post_meta($id, 'go_store_item_desc', $store_description);

        $store_cost = (isset($custom_fields['go_mta_store_cost'][0]) ?  $custom_fields['go_mta_store_cost'][0] : null);
        $store_cost = unserialize($store_cost);
        $store_gold = $store_cost[0];
        $store_xp = $store_cost[1];

        if (!empty($store_gold)){
            if ($store_gold > 0) {
                update_post_meta($id, 'go_loot_loot_gold', $store_gold);
            }
            if ($store_gold < 0){
                $store_gold = abs($store_gold);
                update_post_meta($id, 'go_loot_loot_gold', $store_gold);
                update_post_meta($id, 'go_loot_reward_toggle_gold', 1);
            }
        }

        if (!empty($store_xp)){
            if ($store_xp > 0) {
                update_post_meta($id, 'go_loot_loot_xp', $store_xp);
            }
            if ($store_xp < 0){
                $store_xp = abs($store_xp);
                update_post_meta($id, 'go_loot_loot_xp', $store_xp);
                update_post_meta($id, 'go_loot_reward_toggle_xp', 1);
            }
        }
    }
}
*/

/**
 * This is the check for the function that updates existing content from v4.
 * The GO table structure is updated at activation of v5.
 * This function checks if the update has been run before.
 * It runs if it hasn't and gives an option to run again if it has.
 */
/*
function go_update_go_ajax_v5_check()
{
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_upgade4' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_update_go_ajax_v5_check' ) ) {
        echo "refresh";
        die( );
    }


    $updated = get_site_option( 'go_update_version_5');
    if ( $updated  ) {//if this has never been ran, set the option
        echo 'run_again';
        die();
    }else {

        update_option('go_update_version_5', true);
        go_v5_update_db();
        die();
    }

}
*/

function go_update_go_to_v5(){
/*
 * Not run from ajax anymore--just runs on activation if needed.
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_upgade4' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_update_go_ajax_v5' ) ) {
        echo "refresh";
        die( );
    }

*/

    $query = new WP_Query(array(
        'post_type' => 'tasks',
        'posts_per_page' => 10000
    ));


    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();

        //get number of stages
        $stage_count = get_post_meta($post_id, 'go_stages', true);
        //UPDATE ALL STAGES
        for ($i = 0; $i <= $stage_count; $i++) {
            //add a uniqueID to the stage
            update_post_meta($post_id, 'go_stages_' . $i . '_uniqueid', $post_id . "_v4_stage" . $i);

            //this is used to set the required elements on the new repeater field
            $element_count = 0;

            //move old elements over
            $title = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_title', true);
            update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_title', $title);

            $private = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_private', true);
            update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_private', $private);

            $text = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_blog_text_toggle', true);
            update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_text_toggle', $text);

            $min = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_blog_text_minimum_length', true);
            update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_text_minimum_length', $min);

            $check_type = get_post_meta($post_id, 'go_stages_' . $i . '_check', true);

            if ($check_type == 'none' || $check_type == 'quiz' || $check_type == 'password'){
                update_post_meta($post_id, 'go_stages_' . $i . '_check_v5', $check_type);
            }else{
                update_post_meta($post_id, 'go_stages_' . $i . '_check_v5', 'blog');
            }

            //update the required elements, if the old check was a blog
            if ($check_type == 'blog') {
                //if a URL was required, add it as a new required element
                $url = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_url_toggle', true);
                if ($url) {
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_element', 'URL');
                    $validate = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_url_url_validation', true);
                    if (!empty($validate)) {
                        update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_requirements_url_validation', $validate);
                    }
                    // add a uniqueID
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_url");
                    $element_count++;
                }

                //if a file upload was required, add it as a new required element
                $file = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_attach_file_toggle', true);
                if ($file) {
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_element', 'File');
                    $restrict = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_attach_file_restrict_file_types', true);
                    if ($restrict) {
                        $types = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_attach_file_allowed_types', true);
                        update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_requirements_allowed_types', $types);
                    }
                    // add a uniqueID
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_file");
                    $element_count++;
                }

                //if video was required, add it as a new required element
                $video = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_video', true);
                if ($video) {
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_element', 'Video');
                    // add a uniqueID
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_video");
                    $element_count++;
                }
                //add the count of the required elements. These are the repeater rows.
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements', $element_count);
            }
            //update if it was a file upload
            //add file as the only required element on the blog
            else if($check_type=='upload'){
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_0_element', 'File');
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements', 1);
                //update_post_meta($post_id, 'go_stages_' . $i . '_check', 'blog');
                // add a uniqueID
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_file");

            }
            //update if it was a URL
            //add URL as the only required element on the blog
            else if($check_type=='URL'){
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_0_element', 'URL');
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements', 1);
                //update_post_meta($post_id, 'go_stages_' . $i . '_check', 'blog');
                // add a uniqueID
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_url");

            }
        }//END STAGE UPDATE

        //BONUS STAGE UPDATE
        $bonus_element_count = 0;
        $check_type = get_post_meta($post_id, 'go_bonus_stage_check', true);
        if ($check_type == 'none' || $check_type == 'quiz' || $check_type == 'password'){
            update_post_meta($post_id, 'go_bonus_stage_check_v5', $check_type);
        }else{
            update_post_meta($post_id, 'go_bonus_stage_check_v5', 'blog');
        }

        if ($check_type == 'blog') {
            $private = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_private', true);
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_private', $private);

            $text = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_blog_text_toggle', true);
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_text_toggle', $text);

            $min = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_blog_text_minimum_length', true);
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_text_minimum_length', $min);


            //if URL was required, add it as a new required element
            $url = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_url_toggle', true);
            if ($url) {
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_element', 'URL');
                $validate = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_url_url_validation', true);
                if (!empty($validate)) {
                    update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_requirements_url_validation', $validate);
                }
                $bonus_element_count++;
                // add a uniqueID
                update_post_meta($post_id, 'go_bonus_stage__blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_url");

            }

            //if file was required, add it as a new required element
            $file = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_attach_file_toggle', true);
            if ($file) {
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_element', 'File');
                $restrict = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_attach_file_restrict_file_types', true);
                if ($restrict) {
                    $types = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_attach_file_allowed_types', true);
                    update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_requirements_allowed_types', $types);
                }
                $bonus_element_count++;
                // add a uniqueID
                update_post_meta($post_id, 'go_bonus_stage__blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_file");
            }

            //if video was required, add it as a new required element
            $video = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_video', true);
            if ($video) {
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_element', 'Video');
                $bonus_element_count++;
                // add a uniqueID
                update_post_meta($post_id, 'go_bonus_stage__blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_video");
            }

            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements', $bonus_element_count);

        }
        //update if it was a file upload on bonus.
        //add File as the only required element on the blog
        else if($check_type=='upload'){
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_0_element', 'File');
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements', 1);
            //update_post_meta($post_id, 'go_bonus_stage_check', 'blog');
            // add a uniqueID
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_file");
        }
        //update if it was a URL on bonus
        //add URL as the only required element on the blog
        else if($check_type=='URL'){
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_0_element', 'URL');
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements', 1);
            //update_post_meta($post_id, 'go_bonus_stage_check', 'blog');
            // add a uniqueID
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_url");
        }
        $key = 'go_post_data_' . $post_id;
        delete_transient($key);
    }
    wp_reset_query();

}
