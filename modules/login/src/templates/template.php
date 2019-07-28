<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/31/18
 * Time: 12:25 PM
 */
//switch_to_blog(1);
//auth_redirect();

$source_blog_id = (isset($_GET['blog_id']) ? $_GET['blog_id'] : null);
switch_to_blog($source_blog_id);
if(is_user_logged_in()){
    wp_redirect(go_get_user_redirect());
    exit;
}
restore_current_blog();

//this form always prints as site 1
//do someredirects for situations
$is_multisite = is_multisite();
$current_blog_id = get_current_blog_id();
if($is_multisite) {
    //redirect to site 1 if this isn't site1
    if ($current_blog_id > 1) {
        //$main_login_url = get_site_url(1, 'login?blog_id=' . $current_blog_id )  ;
        $main_login_url = get_site_url(1, 'signin?blog_id=' . $current_blog_id )  ;
        wp_redirect($main_login_url);
        exit;
    }
    //redirect to signin if this is a login page
    else{
        //if this didn't originate iwth a sub site, and this is gameful, redirect to signin
        global $is_gameful;
        if ($source_blog_id === null && $is_gameful){
            wp_redirect(site_url('signin'));
            exit;
        }

    }
}
if($is_multisite) {
    switch_to_blog($source_blog_id);
}

//print the header from the login page
?>

<html <?php language_attributes(); ?>>
    <!--<![endif]-->
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>" />
        <meta name="viewport" content="width=device-width" />
        <title><?php wp_title( '|', true, 'right' ); ?></title>
        <link rel="profile" href="http://gmpg.org/xfn/11" />
        <link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>">
        <?php // Loads HTML5 JavaScript file to add support for HTML5 elements in older IE versions. ?>
        <!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
        <![endif]-->
        <?php wp_head(); ?>
    </head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="hfeed site">


    <div id="main" class="wrapper">



<?php



echo "<div id ='go_login_center'>";

echo "<div id='go_login_container'>";

if ( ! is_user_logged_in() ) { // Display WordPress login form:
    $title = get_bloginfo( 'name' );
    echo "<h2>$title</h2>";
    $logo = get_option('options_go_login_logo');
    //$url = wp_get_attachment_image($logo, array('250', '250'));
    $url = wp_get_attachment_image_src($logo, 'medium');
    $url = $url[0];

    if ($url) {
        echo "<div><div style=' width: 300px; margin: 0 auto;'><img style='width: unset; height: 125px; margin: 0 auto; display: block;'src='$url' width='100%'</div></div>";
    }

$registration_allowed = get_option('options_allow_registration');
    /*
$limit_domains_toggle = get_option('options_limit_domains_toggle');
if($limit_domains_toggle && $registration_allowed) {
    $message = go_domain_restrictions_message();
    if (!empty($message)) {
        echo "<div class='acf-notice'><p>";
        echo $message;
        echo "</p></div>";
    }
}*/


    $login  = (isset($_GET['login']) ) ? $_GET['login'] : 0;
    if ($login === "failed") {
        echo '<div class="acf-notice -error"><p><strong>ERROR:</strong><br>Invalid username and/or password.</p></div>';
    } elseif ($login === "empty") {
        echo '<div class="acf-notice -error"><p><strong>ERROR:</strong><br>Username and/or Password is empty.</p></div>';
    } elseif ($login === "checkemail") {
        echo '<div class="acf-notice  -success"><p>Check your email for instructions on resetting your password.</p></div>';
    } elseif ($login === "false") {
        echo '<div class="acf-notice  -success"><p>You are logged out.</p><br><br></div>';
    }elseif ($login === "bad_domain") {
        echo '<div class="acf-notice -error"><p><strong>ERROR:</strong><br>You used an invalid domain to sign in.</p>';
        $message = go_domain_restrictions_message();
        if (!empty($message)) {
            echo "<p>";
            echo $message;
            echo "</p>";
        }
        echo '</div>';
        ?>
        <script>



            jQuery(window).load(function(){
                var host = "." + location.host;
                //document.cookie = go_name +"=;SESSnsl expires = Thu, 01 Jan 1970 00:00:00 GMT";
                setTimeout(function(){

                    //alert("Hello");
                    document.cookie="SESSnsl=;path=/; domain="+ host +"; expires = Thu, 01 Jan 1970 00:00:00 GMT";

                }, 100);

                //alert (host);
            });

        </script>
        <?php
    }


    if($source_blog_id >1) {
        $this_redirect = network_site_url('login?blog_id=' . $source_blog_id);
    }else{
        $this_redirect  = site_url('login');
    }

   // Display WordPress login form:
    $args = array(
        'redirect' => $this_redirect,
        'form_id' => 'game-on-login',
        'label_username' => __( 'Username' ),
        'label_password' => __( 'Password' ),
        'label_remember' => __( 'Remember Me' ),
        'label_log_in' => __( 'Log In' ),
        'remember' => true
    );

    //switch to main blog to print the form
if($is_multisite) {
    restore_current_blog();
}
    wp_login_form( $args );

if($is_multisite) {
    switch_to_blog($source_blog_id);
}
 if ($registration_allowed) {
     ?>
     <a href="<?php echo home_url('register'); ?>">
         <button>Register</button>
     </a>
     <?php
 }
    //NOTE: function go_added_login_field inserts the reset password link
        ?>
    </div> <a href="<?php echo home_url();?>">Home</a></div>

        <?php
        echo "</div></div>";


}

if($is_multisite) {
    restore_current_blog();
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


