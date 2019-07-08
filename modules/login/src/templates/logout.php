<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/31/18
 * Time: 12:25 PM
 */


/**
 * The template for displaying login page
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

if(!is_user_logged_in()){
    $log_in_link = get_site_url(null, 'login');
    wp_redirect($log_in_link);
}


//https://codex.wordpress.org/Customizing_the_Login_Form#Make_a_Custom_Login_Page
//
get_header();
//get_header();
///////////
echo "<div id ='go_login_center'>";

echo "<div id='go_login_container'>";
$log_out_link = get_site_url(null, 'logout');

echo "Are you sure you want to leave?<br>";
echo "<a href='$log_out_link' class='go_logout'>Log out</a>";

echo "</div></div>"

    //echo " | ";
    //wp_register('', ''); // Display "Site Admin" link.


?>
    <script>
        jQuery( document ).ready( function()  {
            jQuery('#go_login_center').fadeIn(1000);
        });
    </script>
<?php




wp_footer();

