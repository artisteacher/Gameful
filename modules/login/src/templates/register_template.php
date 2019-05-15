<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/31/18
 * Time: 12:25 PM
 */


/**
 * The template for displaying registration
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header();

echo "<div id='go_profile_wrapper' style='max-width: 1100px; margin: 20px auto;'><h2 style='padding-top:10px;'>Registration</h2>";
//wp_localize_script( 'go_frontend', 'hideFooterWidgets', 'true' );

if ( is_user_logged_in() ) {

    echo 'You are already signed in.';

} elseif ( ! get_option( 'options_allow_registration' ) ) {

    echo 'Registering new users is currently not allowed.';
} else {
    go_include_password_checker();
    $groups = array();
    $fields = array('field_5cd4fa743159f', 'field_5cd4be08e7077', 'field_5cd1d1de5491b', 'field_5cd1d21168754', 'field_5cd1d13769aa9', 'field_5cd4f7b43672b' );

    $use_membership_code = get_option('options_registration_code_toggle');
    if($use_membership_code){
        $fields[]='field_5cd9f85e5f788';
    }

    $fields[]='field_5cd3638830f17';
    $fields[]='field_5cd363d130f18';
    $fields[]='field_5cd52c8f46296';
    $form =  my_acf_user_form_func($groups, $fields, 'register');

    echo $form;



}
echo "</div>";


wp_footer();
