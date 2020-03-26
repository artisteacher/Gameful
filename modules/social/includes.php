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
    if ($request_uri === '/leaderboard/') {//only load on the leaderboard
        include_once('src/public_ajax.php');
    }
}else if ( defined( 'DOING_AJAX' )) {
    $ajax_action  = (isset($_REQUEST['action']) ?  $_REQUEST['action'] : null);
    $actions = array( "go_leaderboard_dataloader_ajax", "go_make_leaderboard_filter", "go_make_leaderboard",
        "go_follow_request", "go_follow_request_accept", "go_follow_unfollow", "go_follow_remove_follower", 'go_follow_request_deny',
        "go_make_feed", "go_followers_list", "go_following_list" );
    if(in_array($ajax_action, $actions)) {
        //add_action( 'wp_ajax_xxx', 'xxx' ); //OK
        include_once('src/public_ajax.php');
        include_once('src/ajax.php');

       //OK
        add_action( 'wp_ajax_go_leaderboard_dataloader_ajax', 'go_leaderboard_dataloader_ajax');
        add_action( 'wp_ajax_go_make_leaderboard_filter', 'go_make_leaderboard_filter');
        add_action( 'wp_ajax_go_make_leaderboard', 'go_make_leaderboard' ); //OK
        add_action( 'wp_ajax_go_follow_request', 'go_follow_request' ); //OK
        add_action( 'wp_ajax_go_follow_request_accept', 'go_follow_request_accept' ); //OK
        add_action( 'wp_ajax_go_follow_unfollow', 'go_follow_unfollow' ); //OK
        add_action( 'wp_ajax_go_follow_remove_follower', 'go_follow_remove_follower' ); //OK
        add_action( 'wp_ajax_go_follow_request_deny', 'go_follow_request_deny' ); //OK
        add_action( 'wp_ajax_go_make_feed', 'go_make_feed' ); //OK
        add_action( 'wp_ajax_go_followers_list', 'go_followers_list' ); //OK
        add_action( 'wp_ajax_go_following_list', 'go_following_list' ); //OK
        //add_action( 'wp_ajax_go_stats_leaderboard_dataloader_ajax', 'go_stats_leaderboard_dataloader_ajax');
    }
    else{
        $debug_this = 'stop';//put a breakpoint here to debug
    }


}else{
    //include_once('admin/admin.php');
}

//always include
include_once('src/functions.php');