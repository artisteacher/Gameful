<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 12/22/18
 * Time: 5:35 AM
 */

//conditional includes
if ( !is_admin() ) {
    //include_once('public/public.php');
}else if ( defined( 'DOING_AJAX' )) {
    include_once('src/ajax.php');

    add_action( 'wp_ajax_go_reset_all_users', 'go_reset_all_users' ); //OK
    //add_action( 'wp_ajax_go_upgade4', 'go_upgade4' ); //OK
    add_action( 'wp_ajax_go_update_go_ajax_v5', 'go_update_go_ajax_v5' ); //OK
    add_action( 'wp_ajax_go_update_go_ajax_v5_check', 'go_update_go_ajax_v5_check' ); //OK
}else{
    include_once('src/admin.php');
}

//always include
//include_once('src/functions.php');