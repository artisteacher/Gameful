<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 12/22/18
 * Time: 5:35 AM
 */

//conditional includes
if ( defined( 'DOING_AJAX' )) {
    //Clipboard
    add_action( 'wp_ajax_go_clipboard_stats', 'go_clipboard_stats' ); //OK
    add_action( 'wp_ajax_go_clipboard_activity', 'go_clipboard_activity' ); //OK
    add_action( 'wp_ajax_go_clipboard_messages', 'go_clipboard_messages' ); //OK
    add_action( 'wp_ajax_go_clipboard_store', 'go_clipboard_store' ); //OK
    add_action( 'wp_ajax_go_clipboard_stats_dataloader_ajax', 'go_clipboard_stats_dataloader_ajax' ); //OK
    add_action( 'wp_ajax_go_clipboard_store_dataloader_ajax', 'go_clipboard_store_dataloader_ajax' ); //OK
    add_action( 'wp_ajax_go_clipboard_messages_dataloader_ajax', 'go_clipboard_messages_dataloader_ajax' ); //OK
    add_action( 'wp_ajax_go_clipboard_activity_dataloader_ajax', 'go_clipboard_activity_dataloader_ajax' ); //OK

    include_once('src/ajax.php');
    include_once('src/public_ajax.php');

}else if ( is_admin() ) {
    // include_once('public/public.php');

    include_once('src/admin.php');

}else{
    include_once('src/public_ajax.php');

}

//always include

//include_once('functions.php');