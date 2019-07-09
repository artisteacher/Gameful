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

    $go_lightbox_switch = get_option( 'options_go_video_lightbox' );
    $go_video_unit = get_option ('options_go_video_width_unit');
    $go_fitvids_maxwidth = "";
    if ($go_video_unit == 'px'){
        $go_fitvids_maxwidth = get_option('options_go_video_width_pixels')."px";
    }
    if ($go_video_unit == '%'){
        $go_fitvids_maxwidth = get_option('options_go_video_width_percent')."%";
    }
    wp_localize_script(
        'go_all_pages_js',
        'GO_EVERY_PAGE_DATA',
        array(
            'nonces' => array(
                'go_stats_lightbox'             => wp_create_nonce( 'go_stats_lightbox' ), //Most of the stats nonces could be loaded in the lightbox
                'go_stats_about'                => wp_create_nonce( 'go_stats_about' ),
                'go_stats_task_list'            => wp_create_nonce( 'go_stats_task_list' ),
                'go_stats_store_list'           => wp_create_nonce( 'go_stats_store_list' ),
                'go_stats_activity_list'        => wp_create_nonce( 'go_stats_activity_list' ),
                'go_stats_messages'             => wp_create_nonce( 'go_stats_messages' ),
                'go_stats_single_task_activity_list'       => wp_create_nonce( 'go_stats_single_task_activity_list' ),
                'go_stats_badges_list'          => wp_create_nonce( 'go_stats_badges_list' ),
                'go_stats_groups_list'          => wp_create_nonce( 'go_stats_groups_list' ),
                'go_stats_leaderboard'          => wp_create_nonce( 'go_stats_leaderboard' ),
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
            ),
            'go_lightbox_switch'            => $go_lightbox_switch,
            'go_fitvids_maxwidth'           => $go_fitvids_maxwidth
        )
    );
}