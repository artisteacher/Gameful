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


if (is_user_logged_in()){
    $redirect_url = get_home_url();
    wp_redirect($redirect_url);
}

acf_form_head();
$ajaxurl =  admin_url( 'admin-ajax.php' );
?>
<script>
    var ajaxurl = MyAjax.ajaxurl;
</script>
<?php
wp_localize_script( 'go_frontend', 'ajaxurl', $ajaxurl);
get_header();

echo "<div id='go_profile_wrapper' style='max-width: 1100px; margin: 20px auto;'><h2 style='padding-top:10px;'>Register</h2>";
$updated  = (isset($_GET['updated']) ) ? $_GET['updated'] : 0;
    if ($updated === "true") {

        echo '<p class="success">Your Profile was created.</p>';
        if (is_user_logged_in()){
            $page_name = 'login';
            $redirect_url = get_home_url($page_name);
            echo "<p><a href='$redirect_url'>Login</a></p>";
            echo '<br><br>';

        }

    }
    else {

        $groups = array();
        //$fields = array('field_5cd9f85e5f788', 'field_5cd4fa743159f', 'field_5cd4be08e7077', 'field_5cd1d1de5491b', 'field_5cd1d21168754', 'field_5cd1d13769aa9', 'field_5cd4f7b4366c6', 'field_5cd4f7b43672b', 'field_5cd3638830f17', 'field_5cd363d130f18', 'field_5cd52c8f46296');
        $fields = array('field_5cd4fa743159f', 'field_5cd4be08e7077', 'field_5cd3638830f17', 'field_5cd363d130f18', 'field_5cd52c8f46296');


        $form = go_acf_user_form_func($groups, $fields, 'register');

        echo $form;

    }





echo "</div>";



wp_footer();


