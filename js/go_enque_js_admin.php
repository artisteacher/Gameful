<?php

function go_admin_scripts ($hook) {
    global $post;
    global $go_js_version;
    global $go_debug;

    $user_id = get_current_user_id();
    //is the current user an admin
    $is_admin = go_user_is_admin($user_id);

    /*
     * Registering Scripts For Admin Pages
     */

    wp_enqueue_style( 'dashicons' );

    /*
     * Combined scripts for every admin page. Combine all scripts unless the page needs localization.
     */

    wp_register_script( 'go_admin_user', plugin_dir_url( __FILE__ ).'min/go_admin_user-min.js', array( 'jquery' ), $go_js_version, true);

    wp_register_script( 'go_combined_js_depend', plugin_dir_url( __FILE__ ).'min/go_combine_dependencies-min.js', array( 'jquery' ), $go_js_version, true);
    wp_enqueue_script( 'go_combined_js_depend' );

    //this one doesn't minify for some reason
    //wp_register_script( 'go_admin-tools', plugin_dir_url( __FILE__ ).'scripts/go_tools.js', array( 'jquery' ), $go_js_version, true);


    /*
     * Enqueue Scripts For Admin Pages (Except for page specific ones below)
     */

    // Dependencies
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-accordion' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'jquery-ui-draggable' );
    wp_enqueue_script( 'jquery-ui-droppable' );
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script( 'jquery-ui-spinner' );
    wp_enqueue_script( 'jquery-ui-progressbar' );
    wp_enqueue_script( 'jquery-effects-core' );
    /**
     * Tabs
     */
    wp_enqueue_script( 'jquery-ui-tabs');
    //wp_enqueue_script( 'go_featherlight_min' );

    //Combined Scripts
    //wp_enqueue_script( 'go_scripts' );

    //END Combined Scripts
    //single script
    //wp_enqueue_script( 'go_admin-tools' );


    //LOCALIZE

    $go_lightbox_switch = get_option( 'options_go_video_lightbox' );
    $go_video_unit = get_option ('options_go_video_width_unit');
    $go_fitvids_maxwidth = "";
    if ($go_video_unit == 'px'){
        $go_fitvids_maxwidth = get_option('options_go_video_width_pixels')."px";
    }
    if ($go_video_unit == '%'){
        $go_fitvids_maxwidth = get_option('options_go_video_width_percent')."%";
    }

    // Localization for all admin page
    wp_localize_script( 'go_admin_user', 'SiteURL', get_site_url() );
    wp_localize_script( 'go_admin_user', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    wp_localize_script( 'go_admin_user', 'PluginDir', array( 'url' => plugin_dir_url(dirname(__FILE__) ) ) );
    wp_localize_script(
        'go_admin_user',
        'GO_EVERY_PAGE_DATA',
        array(
            'nonces' => array(
                'go_deactivate_plugin'         	=> wp_create_nonce( 'go_deactivate_plugin' ),
                'go_admin_bar_stats'           	=> wp_create_nonce( 'go_admin_bar_stats' ),
                'go_stats_about'               	=> wp_create_nonce( 'go_stats_about' ),
                'go_stats_task_list'           	=> wp_create_nonce( 'go_stats_task_list' ),
                'go_stats_item_list'           	=> wp_create_nonce( 'go_stats_item_list' ),
                'go_stats_activity_list'       	=> wp_create_nonce( 'go_stats_activity_list' ),
                'go_stats_messages'       	    => wp_create_nonce( 'go_stats_messages' ),
                'go_stats_single_task_activity_list' => wp_create_nonce( 'go_stats_single_task_activity_list' ),
                'go_stats_badges_list'         	=> wp_create_nonce( 'go_stats_badges_list' ),
                'go_stats_groups_list'         	=> wp_create_nonce( 'go_stats_groups_list' ),
                'go_stats_leaderboard'         	=> wp_create_nonce( 'go_stats_leaderboard' ),
                'go_stats_lite'                	=> wp_create_nonce( 'go_stats_lite' ),
                'go_upgade4'                   	=> wp_create_nonce( 'go_upgade4'),//could be just on tools
                'go_reset_all_users'			=> wp_create_nonce( 'go_reset_all_users'),//could be just on tools
                'go_the_lb_ajax' 				=> wp_create_nonce( 'go_the_lb_ajax' ),//could be just create store item page
                'go_create_admin_message' 		=> wp_create_nonce('go_create_admin_message'),
                'go_send_message' 				=> wp_create_nonce('go_send_message'),
                'go_blog_lightbox_opener'       => wp_create_nonce('go_blog_lightbox_opener'),//when is this done in backend?
                'go_blog_user_task'             => wp_create_nonce('go_blog_user_task'),
                'go_user_map_ajax'              => wp_create_nonce('go_user_map_ajax'),
                'go_update_last_map'            => wp_create_nonce('go_update_last_map'),
                'go_blog_favorite_toggle'            => wp_create_nonce('go_blog_favorite_toggle'),
                'go_update_go_ajax_v5_check'            => wp_create_nonce('go_update_go_ajax_v5_check'),
                'go_update_go_ajax_v5'            => wp_create_nonce('go_update_go_ajax_v5'),
                'go_mark_one_read_toggle'       => wp_create_nonce('go_mark_one_read_toggle'),
                'go_send_feedback'              => wp_create_nonce('go_send_feedback'),
                'go_blog_revision'              => wp_create_nonce('go_blog_revision'),
                'go_restore_revision'              => wp_create_nonce('go_restore_revision')
                //'go_num_posts'                  => wp_create_nonce('go_num_posts'),//reader? isn't this just frontend
                //'go_mark_one_read'              => wp_create_nonce('go_mark_one_read'),
               // 'go_send_feedback'              => wp_create_nonce('go_send_feedback')
            ),
            'go_is_admin'                   => $is_admin,
            'go_lightbox_switch'            => $go_lightbox_switch,
            'go_fitvids_maxwidth'           => $go_fitvids_maxwidth
        )
    );
    
    if($go_debug) {
        wp_localize_script( 'go_admin_user', 'go_debug', 'true' );
    }else{
        wp_localize_script( 'go_admin_user', 'go_debug', 'false' );
    }

    $is_admin_user = go_user_is_admin();
    if ($is_admin_user){
        wp_localize_script(
            'go_admin_user',
            'GO_ADMIN_DATA',
            array(
                'nonces' => array(
                    'go_admin_messages'         => wp_create_nonce( 'go_admin_messages'),
                )
            )
        );

        wp_enqueue_script( 'go_admin_user' );

        if ( 'toplevel_page_go_clipboard' === $hook ) {

            /*
             * Clipboard Scripts
             */

            //COMBINED
            //wp_enqueue_script( 'go_clipboard' );

            // Localization
            //wp_localize_script( 'go_admin_user', 'Minutes_limit', array( 'limit' => get_option( 'go_minutes_color_limit' ) ) );
            wp_localize_script(
                'go_admin_user',
                'GO_CLIPBOARD_DATA',
                array(
                    'nonces' => array(
                        'go_clipboard_stats'          => wp_create_nonce( 'go_clipboard_stats' ),
                        'go_clipboard_activity' => wp_create_nonce( 'go_clipboard_activity' ),
                        'go_clipboard_messages' => wp_create_nonce( 'go_clipboard_messages'),
                        'go_clipboard_store' => wp_create_nonce( 'go_clipboard_store'),
                        'go_clipboard_save_filters'     => wp_create_nonce( 'go_clipboard_save_filters' )
                    ),
                )
            );
        }

        // Enqueue and Localization for options page
        if ( 'toplevel_page_go_options' === $hook ) {


            wp_localize_script('go_admin_user', 'levelGrowth', get_option('options_go_loot_xp_levels_growth'));
            wp_localize_script('go_admin_user', 'go_is_options_page', array('is_options_page' => true));
        }

        if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
            if ( 'go_store' === $post->post_type ) {
                //wp_enqueue_script('go_edit_store');
                $id = get_the_ID();
                $store_name = get_option( 'options_go_store_name');
                wp_localize_script( 'go_admin_user', 'GO_EDIT_STORE_DATA', array( 'postid' => $id , 'store_name' => $store_name, 'is_store_edit' => true ));
            }
        }
    }


    /**
     * Resize All Images on Client Side
     */
    //wp_enqueue_script( 'client-resize' , plugins_url( 'scripts/client-side-image-resize.js' , __FILE__ ) , array('media-editor' ) , '0.0.1' );
    /*
    wp_localize_script( 'client-resize' , 'client_resize' , array(
        'plupload' => array(
            'resize' => array(
                'enabled' => true,
                'width' => 1920, // enter your width here
                'height' => 1200, // enter your width here
                'quality' => 90,
            ),
        )
    ) );
    */

}

function go_admin_enqueue_scripts_acf() {

    global $go_js_version;
    wp_register_script( 'go_acf_js', plugin_dir_url( __FILE__ ).'scripts/go_acf_admin.js', array( 'jquery' ), $go_js_version, true);
    wp_enqueue_script( 'go_acf_js');

}

add_action('acf/input/admin_enqueue_scripts', 'go_admin_enqueue_scripts_acf');



?>
