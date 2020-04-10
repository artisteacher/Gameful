<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-07-06
 * Time: 11:43
 */

function go_localize_all_pages(){

    global $go_debug;
    if($go_debug) {
        wp_localize_script( 'go_all_pages_js', 'go_debug', 'true' );
    }else{
        wp_localize_script( 'go_all_pages_js', 'go_debug', 'false' );
    }

    $go_lightbox_switch = get_option( 'go_video_lightbox_toggle_switch' );
    if($go_lightbox_switch === false){
        $go_lightbox_switch = 1;
    }
    $go_video_unit = get_option ('go_video_width_type_control');
    if ($go_video_unit == '%'){
        $percent = get_option( 'go_video_width_percent_control' );
        if($percent === false){
            $percent = 100;
        }
        $go_fitvids_maxwidth = $percent."%";
    }else{
        $pixels = get_option( 'go_video_width_px_control' );
        if($pixels === false){
            $pixels = 400;
        }
        $go_fitvids_maxwidth = $pixels."px";
    }

    /*
           * Clipboard Scripts
           */
    wp_localize_script(
        'go_all_pages_js',
        'GO_CLIPBOARD_DATA_frontend',
        array(
            'nonces' => array(
                'go_clipboard_activity' => wp_create_nonce( 'go_clipboard_activity' ),
                'go_clipboard_save_filters'     => wp_create_nonce( 'go_clipboard_save_filters' ),
                'go_clipboard_activity_stats_ajax'     => wp_create_nonce( 'go_clipboard_activity_stats_ajax' ),
                'go_clipboard_store'     => wp_create_nonce( 'go_clipboard_store' ),
                //'go_clipboard_store_stats_ajax'     => wp_create_nonce( 'go_clipboard_store_stats_ajax' ),
            ),
        )
    );



    wp_localize_script(
        'go_all_pages_js',
        'GO_EVERY_PAGE_DATA',
        array(
            'nonces' => array(
                'go_stats_lightbox'             => wp_create_nonce( 'go_stats_lightbox' ), //Most of the stats nonces could be loaded in the lightbox
                'go_update_last_map'            => wp_create_nonce('go_update_last_map'),//at login and on map
                'go_stats_about'                => wp_create_nonce( 'go_stats_about' ),
                'go_stats_task_list'            => wp_create_nonce( 'go_stats_task_list' ),
                'go_stats_store_list'           => wp_create_nonce( 'go_stats_store_list' ),
                'go_stats_activity_list'        => wp_create_nonce( 'go_stats_activity_list' ),
                'go_stats_messages'             => wp_create_nonce( 'go_stats_messages' ),
                'go_stats_single_task_activity_list'       => wp_create_nonce( 'go_stats_single_task_activity_list' ),
                'go_stats_badges_list'          => wp_create_nonce( 'go_stats_badges_list' ),
                'go_stats_groups_list'          => wp_create_nonce( 'go_stats_groups_list' ),
                'go_make_leaderboard'           => wp_create_nonce( 'go_make_leaderboard' ),
                'go_stats_lite'                 => wp_create_nonce( 'go_stats_lite' ),
                'go_to_this_map'                => wp_create_nonce('go_to_this_map'),//only needed on map page and clipboard
                'go_the_lb_ajax'                => wp_create_nonce( 'go_the_lb_ajax' ),//could happen anywhere with shortcode
                'go_update_bonus_loot'          => wp_create_nonce('go_update_bonus_loot'),
                'go_create_admin_message'       => wp_create_nonce('go_create_admin_message'),//every page because can be done in stats, reader, and clipboard
                'go_send_message'               => wp_create_nonce('go_send_message'),//every page because can be done in stats, reader, and clipboard
                'go_blog_user_task'             => wp_create_nonce('go_blog_user_task'),//all, called on clipboard and map
                'go_blog_favorite_toggle'       => wp_create_nonce('go_blog_favorite_toggle'),//reader, tasks, and clipboard
                'go_mark_one_read_toggle'       => wp_create_nonce('go_mark_one_read_toggle'),
                'go_send_feedback'              => wp_create_nonce('go_send_feedback'),
                'go_blog_revision'              => wp_create_nonce('go_blog_revision'),
                'go_restore_revision'           => wp_create_nonce('go_restore_revision'),
                'go_clone_post_new_menu_bar'    => wp_create_nonce('go_clone_post_new_menu_bar'),//all pages for admin users
                'go_user_map_ajax'              => wp_create_nonce('go_user_map_ajax'),
                'go_get_likes_list'             => wp_create_nonce('go_get_likes_list'),
                'go_print_grade_scales'         => wp_create_nonce('go_print_grade_scales'),
                'go_check_messages_ajax'        => wp_create_nonce('go_check_messages_ajax'),
                'go_attendance_check_ajax'      => wp_create_nonce('go_attendance_check_ajax'),
                'go_loadmore_reader'            => wp_create_nonce('go_loadmore_reader'),
                'go_update_map_order'         => wp_create_nonce('go_update_map_order'),
                'go_update_chain_order'         => wp_create_nonce('go_update_chain_order'),
                'go_update_badge_group_sort'         => wp_create_nonce('go_update_badge_group_sort'),
                //'go_update_group_order'         => wp_create_nonce('go_update_group_order'),
                'go_update_task_order'          => wp_create_nonce('go_update_task_order'),
                'go_quick_edit'            => wp_create_nonce('go_quick_edit'),
                'go_edit_frontend'            => wp_create_nonce('go_edit_frontend'),
                'go_update_badges_page'            => wp_create_nonce('go_update_badges_page'),
            ),
            'go_lightbox_switch'            => $go_lightbox_switch,
            'go_fitvids_maxwidth'           => $go_fitvids_maxwidth
        )
    );
}



