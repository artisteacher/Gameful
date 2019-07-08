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
if(is_user_logged_in()){
    $join_url = site_url('join');

    wp_redirect(go_get_user_redirect());
}


//https://codex.wordpress.org/Customizing_the_Login_Form#Make_a_Custom_Login_Page
//
//  wp_head();
//get_header();
get_header();
///////////


echo "<div id ='go_login_center'>";

echo "<div id='go_login_container'>";

if ( ! is_user_logged_in() ) { // Display WordPress login form:

    $logo = get_option('options_go_login_logo');
    //$url = wp_get_attachment_image($logo, array('250', '250'));
    $url = wp_get_attachment_image_src($logo, 'medium');
    $url = $url[0];

    if ($url) {
        echo "<div><div style=' width: 300px; margin: 0 auto;'><img src='$url' width='100%'</div></div>";
    }


    $login  = (isset($_GET['login']) ) ? $_GET['login'] : 0;
    if ($login === "failed") {
        echo '<p class="error"><strong>ERROR:</strong> Invalid username and/or password.</p>';
    } elseif ($login === "empty") {
        echo '<p class="error"><strong>ERROR:</strong> Username and/or Password is empty.</p>';
    } elseif ($login === "checkemail") {
        echo '<p class="success"> Check your email for instructions on resetting your password.</p>';
    } elseif ($login === "false") {
        echo '<p class="success"> You are logged out now.</p><br><br>';
    }elseif ($login === "bad_domain") {
        echo '<p class="error">';
        echo  go_domain_restrictions_message();
        echo '</p>';
    }




   // Display WordPress login form:
        $args = array(
            'redirect' => get_home_url('login'),
            'form_id' => 'game-on-login',
            'label_username' => __( 'Username' ),
            'label_password' => __( 'Password' ),
            'label_remember' => __( 'Remember Me' ),
            'label_log_in' => __( 'Log In' ),
            'remember' => true
        );
        wp_login_form( $args );
    //NOTE: function go_added_login_field inserts the reset password link
        ?>
        </div>
        <a href="<?php echo home_url();?>">Home</a>
        <?php
        $registration_allowed = get_option('options_allow_registration');
        if ($registration_allowed) {
            ?>|
            <a href="<?php echo home_url('register'); ?>">Register</a>
            <?php
        }
        echo "</div>";
    $limit_domains_toggle = get_option('options_limit_domains_toggle');
    if($limit_domains_toggle && $registration_allowed) {
        $message = go_domain_restrictions_message();
        if (!empty($message)) {
            echo "<div style='max-width: 800px; margin: 0 auto;'>";
            echo $message;
            echo "</div>";
        }
    }

}

    ?>
<script>
    jQuery( document ).ready( function()  {
        jQuery('#go_login_center').fadeIn(1000);
    });
</script>
<?php



/*
    $background = get_option('options_login_appearance_background_color');

    jQuery('body').css('background', '<?php echo $background;?>');
*/



wp_footer();
/*
} else { // If logged in:
    $redirect_url = home_url('profile');
    wp_redirect( $redirect_url );
    wp_loginout( home_url() ); // Display "Log Out" link.
}*/


