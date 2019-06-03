<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/31/18
 * Time: 12:25 PM
 */


/**
 * The template for displaying map pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */


//https://codex.wordpress.org/Customizing_the_Login_Form#Make_a_Custom_Login_Page
if ( ! is_user_logged_in() ) { // Display WordPress login form:
    //go_include_password_checker();
    wp_head();
    ///////////
    echo "<div id ='go_login_center'><div id='go_login_container'>";

    $queryvar  = (isset($_GET['lostpassword']) ) ? $_GET['lostpassword'] : 0;
    if ($queryvar === "invalid") {
        echo '<p class="error"><strong>ERROR:</strong> Invalid username or email.</p><br><br>';
    }

?>

    <form name="lostpasswordform" id="lostpasswordform" action="<?php echo wp_lostpassword_url(); ?>" method="post">
    <p>
        <label>Username or E-mail:<br>
        <input type="text" name="user_login" id="user_login" class="input" value="" size="20" tabindex="10"></label>
    </p>

    <p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="Get New Password" tabindex="100"></p>
</form>
</div> <a href="<?php echo home_url();?>">Home</a> |
    <a href="<?php echo home_url('registration');?>">Register</a>
    </div>
    <?php
    wp_footer();
    ?>
<script>
    jQuery( document ).ready( function()  {
        jQuery('#go_login_center').fadeIn(1000);
        jQuery('body').css('background', 'lightgrey');
    });
</script>
<?php


} else { // If logged in:
    $redirect_url = home_url('profile');
    wp_redirect( $redirect_url );
    wp_loginout( home_url() ); // Display "Log Out" link.
}


