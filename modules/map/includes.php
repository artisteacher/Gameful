<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 12/22/18
 * Time: 5:35 AM
 */



//conditional includes
if ( !is_admin() ) {
    $page_uri = go_get_page_uri();
    $map_name = get_option( 'options_go_locations_map_map_link');
    if ($page_uri == $map_name) {
        add_action('wp_enqueue_scripts', 'go_scripts');
        include_once('src/public_ajax.php');
    }
    //set the default map on login
    function go_default_map($user_login, $user){
        $is_admin = is_admin();
        $default_map = get_option('options_go_locations_map_default', '');
        $user_id = $user->ID;
        if ($default_map) {
            update_user_option($user_id, 'go_last_map', $default_map);
        }
    }
    add_action('wp_login', 'go_default_map', 10, 2);


}else if ( defined( 'DOING_AJAX' )) {
    include_once('src/public_ajax.php');
    include_once('src/ajax.php');

    add_action( 'wp_ajax_go_update_last_map', 'go_update_last_map' ); //OK
    add_action( 'wp_ajax_go_to_this_map', 'go_to_this_map' ); //OK
    add_action( 'wp_ajax_nopriv_go_update_last_map', 'go_update_last_map' ); //OK
    add_action( 'wp_ajax_nopriv_go_to_this_map', 'go_to_this_map' ); //OK
    add_action( 'wp_ajax_go_user_map_ajax', 'go_user_map_ajax' );

}else{
    //include_once('admin/admin.php');
}

//always include
include_once('src/functions.php');

function map_localize_script(){

        wp_localize_script(
            'go_frontend',
            'go_is_map',
            array(true)
        );


}