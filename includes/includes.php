<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-07-06
 * Time: 09:45
 */

if ( defined( 'DOING_AJAX' )) {

    //add_action('wp_ajax_check_if_parent_term', 'check_if_parent_term'); //for term order //OK
    $action  = (isset($_POST['action']) ?  $_POST['action'] : null);
    $action = substr($action, 0 , 3);
    if ($action==='acf') {
        $acf_location = dirname(__FILE__) . '/includes/acf/acf.php';
        include($acf_location);
    }

    //INCLUDE ACF and ACF custom fields
    include_once('acf/acf.php');
    include_once('custom-acf-fields/wp-acf-unique_id-master/acf-unique_id.php');
    include_once('custom-acf-fields/acf-recaptcha-master/acf-recaptcha.php');
    include_once('custom-acf-fields/acf-order-posts/acf-order-posts.php');
    include_once('custom-acf-fields/acf-level2-taxonomy/acf-level2-taxonomy.php');
    include_once('custom-acf-fields/acf-quiz/acf-quiz.php');
    include_once('custom-acf-fields/acf-typography-field/acf-typography.php');
    include_once('custom-acf-fields/advanced-custom-fields-font-awesome/acf-font-awesome.php');

    //Allows uploading on frontend
    include( 'wp-frontend-media-master/frontend-media.php' );


}
else if ( is_admin() ) {

    //INCLUDE ACF and ACF custom fields
    include_once('acf/acf.php');
    include_once('custom-acf-fields/wp-acf-unique_id-master/acf-unique_id.php');
    include_once('custom-acf-fields/acf-recaptcha-master/acf-recaptcha.php');
    include_once('custom-acf-fields/acf-order-posts/acf-order-posts.php');
    include_once('custom-acf-fields/acf-level2-taxonomy/acf-level2-taxonomy.php');
    include_once('custom-acf-fields/acf-quiz/acf-quiz.php');
    include_once('custom-acf-fields/acf-typography-field/acf-typography.php');
    include_once('custom-acf-fields/advanced-custom-fields-font-awesome/acf-font-awesome.php');



}
else{
    //INCLUDES on Public Pages
    //Allows uploading on frontend
    include_once( 'wp-frontend-media-master/frontend-media.php' );



}

//$nsl_location = dirname(__FILE__) . '/nextend-gameon-connect/nextend-facebook-connect.php';
//include($nsl_location);
add_action('init', 'go_add_customizer_controls');
function go_add_customizer_controls(){
    include_once('customizer-custom-controls/functions.php');
}