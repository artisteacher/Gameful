<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 12/22/18
 * Time: 5:35 AM
 */

//conditional includes


if ( !is_admin() ) {
    include_once('src/public.php');
    include_once('src/public_ajax.php');
}else if ( defined( 'DOING_AJAX' )) {
    include_once('src/public_ajax.php');
    include_once('src/ajax.php');

    add_action( 'wp_ajax_go_update_admin_view', 'go_update_admin_view' ); //OK

    add_action( 'wp_ajax_go_stats_leaderboard_dataloader_ajax', 'go_stats_leaderboard_dataloader_ajax');
    add_action( 'wp_ajax_go_new_task_from_template', 'go_new_task_from_template');
    add_action( 'wp_ajax_go_clone_post_new_menu_bar', 'go_clone_post_new_menu_bar');
    add_action( 'admin_action_go_new_task_from_template_as_draft', 'go_new_task_from_template_as_draft' );
}else{
    //include_once('admin/admin.php');
}

//always include
include_once('src/functions.php');

/*
 * Admin Menu & Admin Bar
 */
//add_action( 'admin_bar_init', 'go_admin_bar' );
