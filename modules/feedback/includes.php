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
    include_once('src/ajax.php');
    //include_once('src/admin_ajax.php');
    add_action( 'wp_ajax_go_filter_reader', 'go_filter_reader' );
    add_action( 'wp_ajax_go_filter_quest_reader', 'go_filter_quest_reader' );
    add_action( 'wp_ajax_go_reader_bulk_read', 'go_reader_bulk_read' );
    add_action( 'wp_ajax_go_reader_read_printed', 'go_reader_read_printed' );
    add_action( 'wp_ajax_go_num_posts', 'go_num_posts' );
    add_action( 'wp_ajax_go_mark_one_read_toggle', 'go_mark_one_read_toggle' );
    add_action( 'wp_ajax_go_send_feedback', 'go_send_feedback' );
    add_action( 'wp_ajax_go_loadmore_reader', 'go_loadmore_reader' );
    add_action( 'wp_ajax_nopriv_go_loadmore_reader', 'go_loadmore_reader' );
    add_action( 'wp_ajax_go_blog_revision', 'go_blog_revision' );
    add_action( 'wp_ajax_go_restore_revision', 'go_restore_revision' );
    add_action( 'wp_ajax_go_blog_favorite_toggle', 'go_blog_favorite_toggle');
    add_action( 'wp_ajax_go_get_likes_list', 'go_get_likes_list');
    include_once('src/public_ajax.php');
}else{
    //include_once('src/admin.php');
    //include_once('src/admin_ajax.php');
}

//always include
include_once('src/functions.php');