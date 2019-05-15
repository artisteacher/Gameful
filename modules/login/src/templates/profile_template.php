<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/31/18
 * Time: 12:25 PM
 */


/**
 * The template for displaying user profile
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header();

echo "<div id='go_profile_wrapper' style='max-width: 1100px; margin: 20px auto;'><h2 style='padding-top:10px;'>Profile</h2>";

if ( is_user_logged_in() ) {

    $updated  = (isset($_GET['updated']) ) ? $_GET['updated'] : 0;
    if ($updated === "true") {
        echo '<p class="success">Your Profile was updated.</p><br><br>';
    }

    //need avatar and course, section and seat.

    echo '<div class="go_user_actions"><a href="/wp-login.php?action=logout" class="go_logout">Logout</a> – ';
    echo '<a href="#" class="go_password_change_modal">Change Password</a></div>';
    //$groups = array('group_5a8fb5fbe075d');
    $groups = array();
    $fields = array('field_5cd4be08e7077', 'field_5cd1d1de5491b', 'field_5cd1d21168754', 'field_5cd1d13769aa9', 'field_5cd4f996c0d86', 'field_5cd4f7b4366c6', 'field_5cd4f7b43672b' );

    $form =  my_acf_user_form_func($groups, $fields);

    echo $form;






}
echo "</div>";

wp_footer();


