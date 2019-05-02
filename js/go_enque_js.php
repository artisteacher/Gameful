<?php

/*
 * Registering Scripts/Styles For The Front-end
 */

function go_scripts () {
    global $go_js_version;
    global $go_debug;
    wp_register_script( 'go_wp_media', get_site_url(null, 'wp-admin/css/media.css'), null, $go_js_version );
	/*
	 * Registering Scripts For The Front-end
	 */
    wp_enqueue_script( 'mce-view' );

	wp_enqueue_style( 'dashicons' );

    //wp_register_script( 'go_loadmore', plugin_dir_url( __FILE__ ).'dev/scripts/go_load_more.js', null, $go_js_version );

    //COMBINED FILE
    wp_register_script( 'go_frontend', plugin_dir_url( __FILE__ ).'min/go_frontend-min.js', array('jquery'), $go_js_version, true);
    wp_enqueue_script( 'go_frontend' );

    wp_register_script( 'go_combined_js_depend', plugin_dir_url( __FILE__ ).'min/go_combine_dependencies-min.js', array( 'jquery' ), $go_js_version, true);
    wp_enqueue_script( 'go_combined_js_depend' );

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

    /*
     * Localizing Scripts For The Front-end
     */

    $is_admin = go_user_is_admin();
    if ($is_admin){
        //wp_register_script('go_admin_notification_listener', plugins_url('min/go_admin_notifications-min.js', __FILE__), array('jquery'), $go_js_version, true);
        //wp_enqueue_script( 'go_admin_notification_listener' );
        wp_localize_script(
            'go_frontend',
            'GO_ADMIN_DATA',
            array(
                'nonces' => array(
                    'go_admin_messages'         => wp_create_nonce( 'go_admin_messages'),
                )
            )
        );
    }

    // Localization
    $user_id = get_current_user_id();
    //is the current user an admin
    $is_admin = go_user_is_admin($user_id);
    $go_lightbox_switch = get_option( 'options_go_video_lightbox' );
    $go_video_unit = get_option ('options_go_video_width_unit');
    $go_fitvids_maxwidth = "";
    if ($go_video_unit == 'px'){
        $go_fitvids_maxwidth = get_option('options_go_video_width_pixels')."px";
    }
    if ($go_video_unit == '%'){
        $go_fitvids_maxwidth = get_option('options_go_video_width_percent')."%";
    }

    wp_localize_script( 'go_frontend', 'SiteURL', get_site_url() );
    wp_localize_script( 'go_frontend', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    wp_localize_script( 'go_frontend', 'PluginDir', array( 'url' => plugin_dir_url( dirname(__FILE__)) ) );
    if($go_debug) {
        wp_localize_script( 'go_frontend', 'go_debug', 'true' );
    }else{
        wp_localize_script( 'go_frontend', 'go_debug', 'false' );
    }
    wp_localize_script(
        'go_frontend',
        'GO_EVERY_PAGE_DATA',
        array(
            'nonces' => array(
                'go_deactivate_plugin'          => wp_create_nonce( 'go_deactivate_plugin' ),
                'go_admin_bar_stats'            => wp_create_nonce( 'go_admin_bar_stats' ),
                'go_stats_about'                => wp_create_nonce( 'go_stats_about' ),
                'go_stats_task_list'            => wp_create_nonce( 'go_stats_task_list' ),
                'go_stats_item_list'            => wp_create_nonce( 'go_stats_item_list' ),
                'go_stats_activity_list'        => wp_create_nonce( 'go_stats_activity_list' ),
                'go_stats_messages'             => wp_create_nonce( 'go_stats_messages' ),
                'go_stats_single_task_activity_list'       => wp_create_nonce( 'go_stats_single_task_activity_list' ),
                //'go_stats_penalties_list'      => wp_create_nonce( 'go_stats_penalties_list' ),
                'go_stats_badges_list'          => wp_create_nonce( 'go_stats_badges_list' ),
                'go_stats_groups_list'          => wp_create_nonce( 'go_stats_groups_list' ),
                //'go_stats_leaderboard_choices' => wp_create_nonce( 'go_stats_leaderboard_choices' ),
                'go_stats_leaderboard'          => wp_create_nonce( 'go_stats_leaderboard' ),
                //'go_stats_leaderboard2'        => wp_create_nonce( 'go_stats_leaderboard2' ),
                'go_stats_lite'                 => wp_create_nonce( 'go_stats_lite' ),
                'go_update_admin_view'          => wp_create_nonce( 'go_update_admin_view' ),
                'go_the_lb_ajax'                => wp_create_nonce( 'go_the_lb_ajax' ),
                'go_update_bonus_loot'          => wp_create_nonce('go_update_bonus_loot'),
                'go_create_admin_message'       => wp_create_nonce('go_create_admin_message'),
                'go_send_message'               => wp_create_nonce('go_send_message'),
                'go_blog_opener'                => wp_create_nonce('go_blog_opener'),
                'go_blog_trash'                 => wp_create_nonce('go_blog_trash'),
                'go_blog_submit'                => wp_create_nonce('go_blog_submit'),
                'go_to_this_map'                => wp_create_nonce('go_to_this_map'),
                'go_blog_lightbox_opener'       => wp_create_nonce('go_blog_lightbox_opener'),
                'go_blog_user_task'             => wp_create_nonce('go_blog_user_task'),
                'go_user_map_ajax'              => wp_create_nonce('go_user_map_ajax'),
                'go_update_last_map'            => wp_create_nonce('go_update_last_map'),
                'go_blog_favorite_toggle'       => wp_create_nonce('go_blog_favorite_toggle'),
                'go_filter_reader'              => wp_create_nonce('go_filter_reader'),
                'go_reader_bulk_read'           => wp_create_nonce('go_reader_bulk_read'),
                'go_reader_read_printed'        => wp_create_nonce('go_reader_read_printed'),
                'go_show_private'               => wp_create_nonce('go_show_private'),
                'go_num_posts'                  => wp_create_nonce('go_num_posts'),
                'go_mark_one_read_toggle'       => wp_create_nonce('go_mark_one_read_toggle'),
                'go_send_feedback'              => wp_create_nonce('go_send_feedback'),
                'go_blog_revision'              => wp_create_nonce('go_blog_revision'),
                'go_restore_revision'              => wp_create_nonce('go_restore_revision')

            ),
            'go_is_admin'                   => $is_admin,
            'go_lightbox_switch'            => $go_lightbox_switch,
            'go_fitvids_maxwidth'           => $go_fitvids_maxwidth
        )
    );
    wp_localize_script(
        'go_frontend',
        'GO_BUY_ITEM_DATA',
        array(
            'nonces' => array(
                'go_buy_item'           => wp_create_nonce( 'go_buy_item' ),
                'go_get_purchase_count' => wp_create_nonce( 'go_get_purchase_count' ),
            ),
            'userID'	=>  $user_id
        )

    );


    /**
     * Resize All Images on Client Side
     */
    /*
    //wp_enqueue_script( 'client-resize' , plugins_url( 'scripts/client-side-image-resize.js' , __FILE__ ) , array('media-editor' ) , '0.0.1' );
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
?>