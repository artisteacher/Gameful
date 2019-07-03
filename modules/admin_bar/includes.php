<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 12/22/18
 * Time: 5:35 AM
 */

//conditional includes
if ( !is_admin() ) {
    /*global $pagenow;
     if ( 'xxx.php' == $pagenow) {
         include_once('public/public.php');
         include_once('src/public_ajax.php');
     }*/
    $request_uri = (isset($_SERVER['REQUEST_URI']) ?  $_SERVER['REQUEST_URI'] : null);//pages to not load this on
    if ( strpos($request_uri, 'user_archive') != true) {

        include_once('src/public.php');
    }


    if ($request_uri === '/leaderboard/') {//only load on the leaderboard
        include_once('src/public_ajax.php');
    }
}else if ( defined( 'DOING_AJAX' )) {
    $ajax_action  = (isset($_REQUEST['action']) ?  $_REQUEST['action'] : null);
    $actions = array("go_update_admin_view", "go_stats_leaderboard_dataloader_ajax", "go_make_leaderboard_filter");
    if(in_array($ajax_action, $actions)) {
        //add_action( 'wp_ajax_xxx', 'xxx' ); //OK
        include_once('src/public_ajax.php');
        include_once('src/ajax.php');

        add_action( 'wp_ajax_go_update_admin_view', 'go_update_admin_view' ); //OK
        add_action( 'wp_ajax_go_stats_leaderboard_dataloader_ajax', 'go_stats_leaderboard_dataloader_ajax');
        add_action( 'wp_ajax_go_make_leaderboard_filter', 'go_make_leaderboard_filter');
    }
    else{
        $debug_this = 'stop';//put a breakpoint here to debug
    }


    add_action( 'wp_ajax_go_new_task_from_template', 'go_new_task_from_template');
    add_action( 'wp_ajax_go_clone_post_new_menu_bar', 'go_clone_post_new_menu_bar');
    add_action( 'admin_action_go_new_task_from_template_as_draft', 'go_new_task_from_template_as_draft' );

}else{
    /*global $pagenow;
    if ( 'xxx.php' == $pagenow) {
        include_once('src/admin.php');

    }*/
}

//always include
include_once('src/functions.php');