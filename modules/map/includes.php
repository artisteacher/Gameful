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

        /*
        //INCLUDE ACF and ACF custom fields
        include_once( dirname(__DIR__) . '/../includes/acf/acf.php');
        include_once( dirname(__DIR__) . '/../includes/custom-acf-fields/wp-acf-unique_id-master/acf-unique_id.php');
        include_once( dirname(__DIR__) . '/../includes/custom-acf-fields/acf-recaptcha-master/acf-recaptcha.php');
        include_once( dirname(__DIR__) . '/../includes/custom-acf-fields/acf-order-posts/acf-order-posts.php');
        include_once( dirname(__DIR__) . '/../includes/custom-acf-fields/acf-level2-taxonomy/acf-level2-taxonomy.php');
        include_once( dirname(__DIR__) . '/../includes/custom-acf-fields/acf-quiz/acf-quiz.php');
        include_once( dirname(__DIR__) . '/../includes/custom-acf-fields/acf-typography-field/acf-typography.php');
        include_once( dirname(__DIR__) . '/../includes/custom-acf-fields/advanced-custom-fields-font-awesome/acf-font-awesome.php');*/
    }
    add_action('wp_enqueue_scripts', 'go_scripts');
    include_once('src/public_ajax.php');
    //set the default map on login
    function go_default_map($user_login, $user){
        $is_admin = is_admin();
        $default_map = get_option('options_go_locations_map_default', '');
        $user_id = $user->ID;
        $is_hidden = get_term_meta( $default_map, 'go_hide_map', true );
        if ($default_map && $is_hidden != true) {
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
    add_action( 'wp_ajax_go_update_map_order', 'go_update_map_order' );
    add_action( 'wp_ajax_go_update_chain_order', 'go_update_chain_order' );
    add_action( 'wp_ajax_go_update_badge_group_sort', 'go_update_badge_group_sort' );
    add_action( 'wp_ajax_go_update_task_order', 'go_update_task_order' );

}else{
    //include_once('admin/admin.php');
}

//always include
include_once('src/functions.php');