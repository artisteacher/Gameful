<?php

/**
 * redirect on /login path
 * This is after login happens
 */
add_action('init', 'go_login_rewrite');
function go_login_rewrite(){
    $page_uri = go_get_page_uri();
    if($page_uri == 'login' && is_user_logged_in()){
        $redirect_blog_id = $_COOKIE['redirect_blog_id'];
        switch_to_blog($redirect_blog_id);
        //wp_redirect($redirect_to);
        wp_redirect(go_get_user_redirect());
        exit;
    }
}

//determine the correct login redirect page based on status and options
function go_get_user_redirect($user_id = null){
    $page = '';
    if ($user_id == null){
        $user_id = get_current_user_id();
    }
    if (!empty($user_id)) {
        $primary_blog_id = get_network()->site_id;
        if (is_user_member_of_blog($user_id) || go_user_is_admin($user_id)) {
            $redirect_to = get_option('options_go_landing_page_radio', 'home');
            //$page = get_option('options_go_landing_page_on_login', '');

            if ($redirect_to == 'store') {
                $page = get_option('options_go_store_store_link', 'store');
            } else if ($redirect_to == 'map') {
                $page = get_option('options_go_locations_map_map_link', 'map');
            } else if ($redirect_to == 'custom') {
                $page = get_option('options_go_landing_page_on_login', '');
            } else if ($redirect_to == 'home'){
                $page = '';
            } else if ($redirect_to == 'default') {
                //return null;
            }

        } else if(is_main_site()){
            $page = '';
        } else{
            $page = 'join';
        }
    }else{
        $page = 'login';
    }

    //this sets the default map on login
    if ($user_id != null) {
        $default_map = get_option('options_go_locations_map_default', '');
        if ($default_map !== '') {
            update_user_option($user_id, 'go_last_map', $default_map);
        }
    }

    return home_url($page);
}



add_action(  'login_init', 'user_registration_login_init', 0  );
function user_registration_login_init () {
    //$blog_id = get_current_blog_id();
    // $referer = $_SERVER['HTTP_REFERER'];

    //$thiss = "http://gameondev";
    //$go_site_url = site_url('login');

    $go_site_id = get_current_blog_id();
    $primary_blog_id = get_main_site_id();
    $main_domain = get_site_url($primary_blog_id);
    $protocols = array('http://', 'http://www.', 'www.', 'https://', 'https://www.');
    $main_domain = str_replace($protocols, '', $main_domain);

    $referrer = (isset($_GET['loginSocial']) ?  $_GET['loginSocial'] : null);
    setcookie("SESSnsl", '', time()-3600, '/', $main_domain);//this resets the social login cookie

    $action =(isset($_GET['action']) ?  $_GET['action'] : null);
    if($action === 'logout' ){
        wp_logout();
        wp_redirect(site_url('signin'));
        exit;
    }

    if(is_user_logged_in()){
        //$redirect = (isset($_COOKIE['redirect_blog_id']) ?  $_COOKIE['redirect_blog_id'] : $primary_blog_id);
        wp_redirect(go_get_user_redirect());
        exit;
    }

    //if we were not redirected from loginSocial (this isn't part of the Google login
    if(empty($referrer)){
        //set the redirect_blog_id cookie to the site id
        setcookie("redirect_blog_id", $go_site_id, time() + 60 * 60 * 24 * 30, '/', $main_domain);

    }
}


/**
 * THIS FUNCTION IS FOR WHEN A USER IS INVITED BY EMAIL
 * Check the wp-activate key and redirect the user to the apply page
 * based on http://www.vanbodevelops.com/tutorials/how-to-skip-the-activation-page-and-send-the-user-straight-to-the-home-page-for-wordpress-multisite
 */
add_action( 'init', 'check_activation_key_redirect_to_page' );
function check_activation_key_redirect_to_page() {
    // We check if the key is not empty
    $self = (isset($_SERVER['PHP_SELF']) ?  $_SERVER['PHP_SELF'] : null);
    if ($self == '/wp-activate.php') {
        //redirect to profile page, with key in query var
        //show a form to choose a password
        //then do the activation
        if (!empty($_GET['key']) || !empty($_POST['key'])) {
            $key = !empty($_GET['key']) ? $_GET['key'] : $_POST['key'];
            // Activates the user and send user/pass in an email

            wp_redirect(site_url('register?activate=true&key='.$key));
            exit;
        }
    }
}

/** Change default error messages **/
function go_custom_error_messages($errors) {
    if (!empty($_GET['login'])) {
        $errors->errors['failed_login'][0] = '<strong>ERROR</strong>: There was a problem with your username or password.';
    }

    return $errors;
}
add_filter( 'wp_login_errors', 'go_custom_error_messages');

add_filter( 'register', 'go_custom_registration_link');
function go_custom_registration_link($registration_url){
   if(is_gameful()){

       if(!is_main_site()) {
            $registration_url = '<a href ="';
            $registration_url .= site_url('register');
            $registration_url .= '">Register</a>';
       }else{
           $registration_url = '<a href ="';
           $registration_url .= site_url('/registration/1/single-teacher');
           $registration_url .= '">Register</a>';
           //$registration_url = null;
       }
   }
    return $registration_url;
}

add_filter('pre_option_users_can_register', 'go_registration_form_allowed');
function go_registration_form_allowed($value) {
    if(!is_main_site() || !is_gameful()) {
        if(!get_option('options_allow_registration')){
            $value = 0;
        };
        $google_only = intval(get_option('options_google-only'));
        //$registration_options = get_option('options_signup_options');
        if($google_only){
            $value = 0;
        };

    }

    return $value; // We need to return an int since get_option does not like false return values
}

add_action('login_form', 'go_remove_login_fields');
function go_remove_login_fields(){
    $google_only = intval(get_option('options_google-only'));
    if($google_only){
        //echo "Google Only";
        ?>
        <script>
            jQuery(document).ready(function() {
                jQuery("#loginform p").remove();
            });

        </script>
        <style>
            #nsl-custom-login-form-main{
                margin-top: 0px !important;
            }
            .nsl-container{
                padding: 0px !important;
            }
        </style>
        <?php
        //

        function remove_lostpassword_text ( $text ) {
            if ($text == 'Lost your password?'){$text = '';}
            return $text;
        }
        add_filter( 'gettext', 'remove_lostpassword_text' );
    };
}

//add_filter( 'register_url', 'go_register_page', 100);
function go_register_page( $register_url ) {
    if(is_gameful()) {
        //$blog_id = $_COOKIE['redirect_blog_id'];
        //$primary_blog_id = get_main_site_id();
        //if($blog_id != $primary_blog_id ){
        if(is_main_site()) {
            return home_url('/registration/1/single-teacher');
        }
    }
}


/*
part of this from: https://profiles.wordpress.org/khromov
*/
/* http://premium.wpmudev.org/forums/topic/redirect-users-to-their-blogs-homepage */
add_filter('login_redirect', 'go_login_redirect_queries', 100, 3);
function go_login_redirect_queries($redirect_to, $request_redirect_to, $user) {
    global $blog_id;

    $redirect_to = site_url('login');
    return $redirect_to;
    //$redirect_to = $_COOKIE['redirect_domain'];
    $redirect_blog_id = $_COOKIE['redirect_blog_id'];
    switch_to_blog($redirect_blog_id);
    //wp_redirect($redirect_to);
    $redirect_to = go_get_user_redirect();
    restore_current_blog();

    return $redirect_to;
}

//resets the social login cookie
//add_action('login_footer', 'go_login_footer');
/*
function go_login_footer(){


    $referer = $_SERVER['REDIRECT_QUERY_STRING'];
    $referer = urldecode($referer);
    $parts = parse_url($referer);

    if(!empty($parts['query'])) {
        parse_str($parts['query'], $query);
        $blog_id = (isset($query['blog_id']) ?  $query['blog_id'] : null);
        //echo $blog_id;
        //$gameful = is_gameful();
        //echo $gameful;
        if(is_gameful()) {
            switch_to_blog($blog_id);
            // echo 'switch';
        }
    }

    //$url = 'google.com';
    $url = site_url();
    //echo $url;
    //print_r($url);

    if(is_gameful()) {
        restore_current_blog();
    }
    ?>
    <script>
    jQuery(window).load(function(){
        var host = "." + location.host;
        //document.cookie = go_name +"=;SESSnsl expires = Thu, 01 Jan 1970 00:00:00 GMT";
        setTimeout(function(){

            //alert("Hello");
            document.cookie="SESSnsl=;path=/; domain="+ host +"; expires = Thu, 01 Jan 1970 00:00:00 GMT";

        }, 100);

        jQuery(".login h1 a").attr('href', "<?php echo $url; ?>");
        //alert (host);
    });

    </script>
    <?php
}*/


// changing the logo link from wordpress.org to your site
add_filter( 'login_headerurl', 'go_login_url', 999 );
function go_login_url($url) {
    $url = site_url();
    return $url;
}

add_action( 'login_enqueue_scripts', 'go_login_logo', 999 );
function go_login_logo() {
    $logo = get_option('options_go_login_logo');
    //$url = wp_get_attachment_image($logo, array('250', '250'));
    $url = wp_get_attachment_image_src($logo, 'medium');
    $url = $url[0];
    $meta =wp_get_attachment_metadata($logo);
    if (!empty($meta)) {
        //$width = $meta['sizes']['medium']['width'];
        $width = (isset($meta['sizes']['medium']['width']) ?  $meta['sizes']['medium']['width'] : $meta['width']);
        //$height = $meta['sizes']['medium']['height'];
        $height = (isset($meta['sizes']['medium']['height']) ?  $meta['sizes']['medium']['height'] : $meta['height']);
        if ($height > 180) {
            $scale = 180 / $height;
            $width = $width * $scale;
            $height = 180;
        }
    }
    if ($url) {
        ?>
        <style type="text/css">
            #login h1 a, .login h1 a {
                background-image: url(<?php echo $url; ?>) !important;
                height:<?php echo $height; ?>px !important;
                width:<?php echo $width; ?>px !important;
                background-size: <?php echo $width; ?>px <?php echo $height; ?>px!important;
                background-repeat: no-repeat;
                padding-bottom: 20px;
            }

            #backtoblog {display:none !important;}
        </style>

        <?php

    }

}


//USE THIS FOR INSTUCTIONS ABOUT DOMAINS
//* Add custom message to WordPress login page
function go_bad_domain_message ($errors) {
    if ('bad_domain' === $_GET['login']) {
        $referer = $_SERVER['REDIRECT_QUERY_STRING'];
        $parts = parse_url($referer);
        parse_str($parts['query'], $query);
        $blog_id = (isset($query['blog_id']) ?  $query['blog_id'] : null);
        if(is_gameful()) {
            switch_to_blog($blog_id);
        }
        $errors->add('domains', go_domain_restrictions_message());
        if(is_gameful()) {
            restore_current_blog();
        }
}
    return $errors;
}
add_filter('wp_login_errors', 'go_bad_domain_message');


/**
 * DOMAIN VALIDATION
 * An action was added to nsl plugin to allow it to validate on social login.
 */
//return list of domains that are allowed
function go_get_domain_restrictions(){
    //$current_blog_id = get_current_blog_id();
    $domains = array();
    $domain_count = get_option('options_limit_domains_domains');
    //$domains = get_field('options_limit_domains_domains');
    $i = 0;

    while ($domain_count > $i) {
        $domain = get_option('options_limit_domains_domains_' . $i . '_domain');
        if (!empty($domain)) {
            $domains[] = $domain;
        }
        $i++;
    }
    return $domains;
}

function validate_email_against_domains($email){
    $domains = go_get_domain_restrictions();
    $user_domain = substr(strrchr($email, "@"), 1);
    if(in_array($user_domain, $domains)){
        $is_valid = 1;
    }else{
        $is_valid = 0;
    }

    return $is_valid;
}

//filter for the social login
//always gets domains from the main blog (1)

/**
 * @param $args
 *
 * for this to work, the action needs to be added to the nsl plugin in the includes/user.php register function
 * get getLastLocationRedirectTo in the provider.php must also be made a public function
 * $blog_url =  $this->provider->getLastLocationRedirectTo();$args = array($email, $blog_url);do_action('nsl_limit_domains', $args);
 */
//add_action('nsl_limit_domains', 'go_limit_domains');
function go_limit_domains($email){
    //$email = $args[0];
    //$blog_url = $args[1];

    //$parts = parse_url($blog_url);
    //parse_str($parts['query'], $query);
    //$blog_id = $query['blog_id'];
    //$blog_id = (isset($query['blog_id']) ?  $query['blog_id'] : null);

    $blog_id = $_COOKIE['redirect_blog_id'];
    $primary_blog_id = get_main_site_id();


    if (empty($blog_id)){
        $blog_id = $primary_blog_id;
    }

    switch_to_blog($blog_id);


    $registration_allowed = intval(get_option('options_allow_registration'));

    if(!$registration_allowed){
        if(!is_main_site($blog_id) || !is_gameful()) {
            $go_login_link = get_site_url($blog_id, 'signin');
            wp_redirect($go_login_link . '?registration=disabled');
            return false;
        }


    };

    //$limit_toggle = get_option('options_limit_domains_toggle');
    //if($limit_toggle){
    $security_options = get_option('options_security');
    if(!is_array($security_options)){
        $security_options = array();
    }
    if(in_array('domains', $security_options)){
        if(is_gameful()) {
            switch_to_blog(intval($blog_id));
        }

        $is_valid = validate_email_against_domains($email);
        if(is_gameful()) {
            restore_current_blog();
        }



        if(!$is_valid){
            if ( is_gameful() && ($blog_id == $primary_blog_id ) ) {
                $go_login_link = network_site_url('signin');
                wp_redirect($go_login_link . '?registration=disabled');
                return false;
            } else{
                $go_login_link = get_site_url($blog_id, 'signin');
                wp_redirect($go_login_link . '?login=bad_domain');
                return false;
            }
        }
    }


    //if this is the main site and this is not an existing user, don't allow registration
    if ( is_gameful() ) {
        if($blog_id == $primary_blog_id ){
            if ( !email_exists( $email ) ) {
                $go_login_link = get_site_url($blog_id, 'signin');
                wp_redirect($go_login_link . '?registration=disabled');
                return false;
            }
        }
    }
    restore_current_blog();

    return true;
}

function go_domain_restrictions_message($message = null ) {
    $domains = go_get_domain_restrictions();

    if (!empty($domains)){
        $message = "This site only allows registration with email addresses that are in the following domains:<br>";
        $message .= '<ul style="padding:0 20px;">';
        $message .= '<li >' . implode( '</li><li>', $domains) . '</li>';
        $message .= '</ul>';
    }
    return $message;
}

function go_verify_username_password($user, $username, $password){
    $is_gameon = (isset($_POST['go_frontend_login']) ? $_POST['go_frontend_login'] : false);
    if ($is_gameon) {
        $page_name = 'login';
        //$login_page = get_home_url($page_name);
        $go_login_link = get_site_url(null, 'login');
        if ($username == "" || $password == "") {
            $redirect_to = $_POST['redirect_to'];
            $redirect_info = $redirect_to . '&' . $page_name . '=empty';
            wp_redirect($redirect_info);
            //wp_redirect($go_login_link . '?'.$page_name. '=empty');
            exit;
        }
    }
}
//add_filter('authenticate', 'go_verify_username_password', 1, 3);



/**
 * ADD PROFILE PAGE
 */

add_action('init', 'go_profile_rewrite');
function go_profile_rewrite(){
	if(is_gameful() && is_main_site()){
    	$hide = true;
    }
    else{
    	$hide = false;
    }
    if (!$hide) {
        $page_name = 'profile';
        add_rewrite_rule($page_name, 'index.php?' . $page_name . '=true', "top");
    }
}

// Query Vars
add_filter( 'query_vars', 'go_profile_query_var' );
function go_profile_query_var( $vars ) {
    $page_name = 'profile';
    $vars[] = $page_name;
    return $vars;
}

/* Template Include */
add_filter('template_include', 'go_profile_template_include', 1, 1);
function go_profile_template_include($template){

    if (!is_main_site() || !is_gameful()) {
        $page_name = 'profile';
        global $wp_query; //Load $wp_query object

        $page_value = (isset($wp_query->query_vars[$page_name]) ? $wp_query->query_vars[$page_name] : false); //Check for query var "blah"

        if ($page_value && $page_value == "true") { //Verify "blah" exists and value is "true".

            acf_form_head();
            return plugin_dir_path(__FILE__) . 'templates/user.php'; //Load your template or file

        }
    }
    return $template; //Load normal template when $page_value != "true" as a fallback
}

/************************
 * ADD Registration PAGE
 */

add_action('init', 'go_registration_rewrite');
function go_registration_rewrite(){
    if(is_gameful() && is_main_site()){
    	$hide = true;
    }
    else{
    	$hide = false;
    }
    if (!$hide) {
        $page_name = 'register';
        add_rewrite_rule($page_name, 'index.php?' . $page_name . '=true', "top");
    }
}

// Query Vars
add_filter( 'query_vars', 'go_registration_query_var' );
function go_registration_query_var( $vars ) {
    $page_name = 'register';
    $vars[] = $page_name;
    return $vars;
}


/* Template Include */
add_filter('template_include', 'go_registration_template_include', 1, 1);
function go_registration_template_include($template){

    if (!is_main_site() || !is_gameful()) {
        $page_name = 'register';
        global $wp_query; //Load $wp_query object

        $page_value = (isset($wp_query->query_vars[$page_name]) ? $wp_query->query_vars[$page_name] : false); //Check for query var "blah"

        if ($page_value && $page_value == "true") { //Verify "blah" exists and value is "true".

            return plugin_dir_path(__FILE__) . 'templates/user.php'; //Load your template or file
        }
    }
    return $template; //Load normal template when $page_value != "true" as a fallback
}




/************************
 * ADD Join PAGE
 */

add_action('init', 'go_join_rewrite', 0);
function go_join_rewrite(){
    //$blog_id = get_current_blog_id();
    //if (!is_main_site() || !is_gameful()) {
    $page_name = 'join';
    add_rewrite_rule($page_name, 'index.php?' . $page_name . '=true', "top");
    //add_rewrite_rule( 'wp-login.php\?action\=register', 'index.php?' . $page_name . '=true', "top");
    //}

}

// Query Vars
add_filter( 'query_vars', 'go_join_query_var' );
function go_join_query_var( $vars ) {
    $page_name = 'join';
    $vars[] = $page_name;
    return $vars;
}

/* Template Include */
add_filter('template_include', 'go_join_template_include', 1, 1);
function go_join_template_include($template){
    //$blog_id = get_current_blog_id();
    if (!is_main_site() || !is_gameful()) {
        $page_name = 'join';
        global $wp_query; //Load $wp_query object

        $page_value = (isset($wp_query->query_vars[$page_name]) ? $wp_query->query_vars[$page_name] : false); //Check for query var "blah"

        if ($page_value && $page_value == "true") { //Verify "blah" exists and value is "true".
            acf_form_head();
            return plugin_dir_path(__FILE__) . 'templates/user.php'; //Load your template or file
        }
    }
    return $template; //Load normal template when $page_value != "true" as a fallback
}

function go_include_password_checker(){
    $minPassword = get_site_option('options_minimum_password_strength');
    wp_localize_script( 'go_frontend', 'hasPassword', 'true' );
    wp_localize_script( 'go_frontend', 'minPassword', $minPassword );
}
//
//prints a afc form on the front end
function go_acf_user_form_func( $groups = array(), $fields = array(), $redirect = false, $post_id, $button_text = 'Update' ) {


    //set the return path for successful completion of the form
    //for profile pages, add the updated=true query variable
    //if this is a registration page, redirect to the default game on page on success
    if($redirect){
        $return = go_get_user_redirect();
    }
    else{
        $return = (site_url('profile'));
        $return = add_query_arg( 'updated', 'true', $return );
    }


    // if ( ! empty ( $form_id ) && ! empty ( $uid ) ) {
    if ( ! empty ( $groups )  ) {
        $options = array(
            'post_id' => $post_id,
            'field_groups' => $groups,
            //'fields' => array('field_5cd3638830f17'),
            'return' => $return,
            'submit_value' => $button_text
        );

        ob_start();

        acf_form( $options );

        $form = ob_get_contents();

        ob_end_clean();
        return $form;
    }


    if ( ! empty ( $fields )  ) {
        $options = array(
            'post_id' => $post_id,
            //'field_groups' => $groups,
            'fields' => $fields,
            'return' => $return,
            'submit_value' => $button_text
        );

        ob_start();

        acf_form( $options );

        $form = ob_get_contents();

        ob_end_clean();
        return $form;
    }


}

//Loads the email from the User table to the email field on the profile page
add_filter('acf/load_field/name=user_email', 'go_acf_load_email');
function go_acf_load_email( $field ) {
    $current_user = wp_get_current_user();
    $current_email = $current_user->user_email;
    if (!$current_email){
        $current_email = '';
    }
    $field['value'] = $current_email;

    return $field;

}

//Validate that an user with this email doesn't already exist.
add_filter('acf/validate_value/name=user_email', 'go_validate_email', 10, 4);
function go_validate_email($valid, $value, $field, $input){
    $user_id_email = email_exists($value);//returns the user_id of this email address, or false
    $user_id = get_current_user_id();

    $referer = rtrim($_SERVER['HTTP_REFERER'], '/');
    $page = substr($referer, strrpos($referer, '/') + 1);

    if($page != 'profile') {
        if (!empty($user_id_email)) {//if this user id exists in the database
            if ($user_id != $user_id_email) {//if this $user_id is not the current user, return an error
                $valid = 'An account with this email already exists.';
                return $valid;
            }
        }

        //$limit_domains_toggle = get_option('options_limit_domains_toggle');
        //if ($limit_domains_toggle) {
        $security_options = get_option('options_security');
        if(in_array('domains', $security_options)){
            $email = $value;
            $is_valid_email = validate_email_against_domains($email);
            if (!$is_valid_email) {
                $valid = go_domain_restrictions_message();
                return $valid;
            }
        }
    }
    //else this is a new email and is a valid for saving
    return $valid;
}

//Validate that an user with this username doesn't already exist.
add_filter('acf/validate_value/name=user_name', 'go_validate_uname', 10, 4);
function go_validate_uname($valid, $value, $field, $input){
    $username_exists = username_exists( $value );
    $is_valid = validate_username($value);
    $length = strlen($value);

    if ($username_exists ) {//if this username exists in the database
        $valid = 'This username already exists.';
        return $valid;
    }else if (!$is_valid ) {//if this username exists in the database
        $valid = 'This username is not valid.';
        return $valid;
    }else if ($length < 6 ) {//if this username exists in the database
        $valid = 'This username is too short.  Must be at least 6 characters.';
        return $valid;
    }
    //else this is a new user name and is a valid for saving
    return $valid;
}

//Validate the registration code
add_filter('acf/validate_value/key=field_5cd9f85e5f788', 'go_validate_code', 10, 4);
function go_validate_code($valid, $value, $field, $input){
    $id = get_current_blog_id();
    $code = get_option('options_registration_code_code');
    if ($code != $value) {//if the code is not correct set the error
        $valid = 'This is not the correct membership code.';
        return $valid;
    }
    //else this code is correct
    return $valid;
}

//Vaidate that the correct password was entered
add_filter('acf/validate_value/name=currentpassword', 'go_validate_password', 10, 4);
function go_validate_password($valid, $value, $field, $input){
    $user_id = get_current_user_id();
    $user_info = get_userdata($user_id);
    $user_pass = $user_info->user_pass;

    if(!(wp_check_password( $value, $user_pass, $user_id))) {
        $valid = 'This is not the current password.';
        return $valid;

    } else {

        return $valid;
    }
}

//prints the form for changing the password
function go_update_password_lightbox(){
    echo "<div style='display: none;'><div class='go_password_change_container' style='min-width: 300px;'>";
    $groups = array();
    $fields = array('field_5cd3640730f19', 'field_5cd3638830f17', 'field_5cd363d130f18', 'field_5cd52c8f46296');
    $form =  go_acf_user_form_func($groups, $fields, false, 'password_reset' );
    echo $form;
    echo "</div></div>";
    die();
}

//Load blank password fields

add_filter('acf/load_field/type=password', 'go_acf_load_password');
function go_acf_load_password( $field ) {
    $field['value'] = '';
    return $field;
}

//Don't save password to metadata.  It should be saved to the user table in the pre_save_post filter.
add_filter('acf/update_value/type=password', 'go_acf_update_password', 10, 3);
function go_acf_update_password( $value, $post_id, $field  ) {
    // override value
    $value = "";
    return $value;
}

//Turn on user registration in wordpress when turned on in Game on
//add_filter('acf/update_value/key=field_5cd8b14485247', 'go_acf_update_registration', 10, 3);
function go_acf_update_registration( $value, $post_id, $field  ) {
    // override value
    if ($value){
        update_option('users_can_register', true);
    }
    else{
        update_option('users_can_register', false);
    }
    return $value;
}

// set ACF pre-save stuff for the user profile/registration pages
add_filter('acf/pre_save_post' , 'acf_save_user' );
function acf_save_user( $post_id ) {

    error_log( 'post id: ' . $post_id);
    $page_uri = go_get_page_uri();
    //RESET PASSWORDS
    if ($post_id === 'password_reset'){
        $user_id = get_current_user_id();
        $new_password = (isset($_POST['acf']['field_5cd3638830f17']) ?  $_POST['acf']['field_5cd3638830f17'] : null);

        wp_set_password( $new_password, $user_id );
        wp_clear_auth_cookie();
        wp_set_current_user ( $user_id );
        wp_set_auth_cookie  ( $user_id );
    }
    if ($post_id === 'activate_user'){
        //activate the user

        $key = !empty($_GET['key']) ? $_GET['key'] : null;
        if(is_gameful()){
            $primary_blog_id = get_main_site_id();
            switch_to_blog(intval($primary_blog_id));
        }

        $result = wpmu_activate_signup($key);

        if (is_wp_error( $result )){
            echo "There was an error with your activation.";
            exit;
        }


        $user_id = !empty($result['user_id']) ? $result['user_id'] : null;

        if(is_gameful()){
            restore_current_blog();
        }

        if ($user_id != null) {
            //$user_id = get_current_user_id();
            $new_password = (isset($_POST['acf']['field_5cd3638830f17']) ?  $_POST['acf']['field_5cd3638830f17'] : null);
            //extract($result);
            $user_data = get_userdata($result['user_id']);
            $user_name = $user_data->user_login;
            $user_id = $user_data->ID;
            //$new_password = $result['password'];

            //set the new password
            wp_set_password( $new_password, $user_id );
            wp_clear_auth_cookie();
            wp_set_current_user ( $user_id );
            wp_set_auth_cookie  ( $user_id );

/*
            print_r($result);
            echo $user_id;
            echo $user_name;
            exit;
*/
            /*
            //login with the new password
            $creds = array();
            $creds['user_login'] = $user_name;
            $creds['user_password'] = $new_password;
            $creds['remember'] = false;
            $user = wp_signon( $creds, false );
            if ( is_wp_error($user) ) {
                echo $user->get_error_message();
                exit;
            }
            //print_r($user);
            //exit;
            // Save the user object to the session
            //setcookie('my_active_user_variable', json_encode($creds), time()+60);
            // Redirect to the network home url
            */
            wp_redirect(site_url('profile'));

            exit;
        }
        else{
            echo "ERROR:";
            print_r($result);
            exit;
        }
    }
    //UPDATE A PROFILE
    if (substr($post_id, 0 , 5) === 'user_') {

        $wp_user_id = str_replace("user_", "", $post_id);

        $display = (isset($_POST['acf']['field_5cd1d13769aa9']) ?  $_POST['acf']['field_5cd1d13769aa9'] : null);
        $display = sanitize_text_field($display);//display name
        //REGISTER A NEW USER
        //if ($post_id === 'register'){
        if ($wp_user_id == 0){
            //add a check for if user is already logged in and redirect

            if (is_user_logged_in()){
                //$redirect_url = get_home_url();
                wp_redirect( home_url() );
                return;

            }

            //get the fields and then clear their values--the values don't need to save outside of this function

            $_POST['acf']['field_5cd9f85e5f788'] = '';//membership code
            $user_name = (isset($_POST['acf']['field_5cd4fa743159f']) ?  $_POST['acf']['field_5cd4fa743159f'] : null);
            $user_name = sanitize_text_field($user_name);//username
            $_POST['acf']['field_5cd4fa743159f'] = '';

            $email = (isset($_POST['acf']['field_5cd4be08e7077']) ?  $_POST['acf']['field_5cd4be08e7077'] : null);
            $email = sanitize_email($email);//email
            $_POST['acf']['field_5cd4be08e7077'] = '';

            $first = (isset($_POST['acf']['field_5cd1d1de5491b']) ?  $_POST['acf']['field_5cd1d1de5491b'] : null);
            $first = sanitize_text_field($first);//first name
            //$_POST['acf']['field_5cd1d1de5491b'] = '';

            $last = (isset($_POST['acf']['field_5cd1d21168754']) ?  $_POST['acf']['field_5cd1d21168754'] : null);
            $last = sanitize_text_field($last);//last name
            //$_POST['acf']['field_5cd1d21168754'] = '';


            //$_POST['acf']['field_5cd1d13769aa9'] = '';

            $new_password = (isset($_POST['acf']['field_5cd3638830f17']) ?  $_POST['acf']['field_5cd3638830f17'] : null);
            $new_password = sanitize_text_field($new_password);
            $_POST['acf']['field_5cd3638830f17'] = '';
            $_POST['acf']['field_5cd363d130f18'] = '';//clear the confirm password field

            //$sections_seats = $_POST['acf']['field_5cd4f7b43672b'];//sections and seats saved with own function
            //$_POST['acf']['field_5cd4f7b43672b'] = array();//clear the field
            //if(is_gameful()) {
            //            $main_site_id = get_network()->site_id;
            //            switch_to_blog($main_site_id);
            //        }
            $user_id = wp_insert_user(
                array(
                    'user_login'	=>	$user_name,
                    'user_pass'	=>	$new_password,
                    'first_name'	=>	$first,
                    'last_name'	=>	$last,
                    'user_email'	=>	$email,
                    'display_name'	=>	$display,
                    'nickname'	=>	$display,
                    'role'		=>	'subscriber'
                )
            );
            //if(is_gameful()) {
            //            restore_current_blog();
            //        }

            //save sections and seats on registration
            $post_id = "user_".$user_id;
            //update_field( 'field_5cd4f7b43672b', $sections_seats, $this_post );


            $creds = array();
            $creds['user_login'] = $user_name;
            $creds['user_password'] = $new_password;
            $creds['remember'] = false;
            $user = wp_signon( $creds, false );
            if ( is_wp_error($user) )
                echo $user->get_error_message();
        }
        else {//this is an existing user

            $email = (isset($_POST['acf']['field_5cd4be08e7077']) ? $_POST['acf']['field_5cd4be08e7077'] : null);
            if(!empty($email)) {
                $emailField = sanitize_email($_POST['acf']['field_5cd4be08e7077']);
                //$emailField = (isset($_POST['acf']['field_5cd4be08e7077']) ?  $_POST['acf']['field_5cd4be08e7077'] : '');
                if (isset($emailField)) {

                    $args = array(
                        'ID' => $wp_user_id,
                        'user_email' => esc_attr($emailField)
                    );
                    wp_update_user($args);
                }
            }
            /*
            $website = (isset($_POST['acf']['field_5cd4f996c0d86']) ? $_POST['acf']['field_5cd4f996c0d86'] : null);
            //$website = (isset($_POST['acf']['field_5cd4f996c0d86']) ?  $_POST['acf']['field_5cd4f996c0d86'] : null);
            if (!empty($website)) {

                $args = array(
                    'ID' => $wp_user_id,
                    'user_url' => esc_attr($website)
                );
                wp_update_user($args);
            }*/

            if($page_uri === 'join' && !is_user_member_of_blog()){
                $blog_id = get_current_blog_id();
                add_user_to_blog( $blog_id, $wp_user_id, 'subscriber' );

            }
        }
    }


    return $post_id;
}

function go_update_display_name( $value, $post_id, $field  ) {
   if (substr($post_id, 0 , 5) === 'user_') {
        $user_id = str_replace("user_", "", $post_id);
        update_user_option( $user_id, 'go_nickname', $value, false );
    }
    return $value;

}
add_filter('acf/update_value/name=go_nickname', 'go_update_display_name', 5, 3);

function go_display_display_name( $value, $post_id, $field  ) {
    if (substr($post_id, 0 , 5) === 'user_') {
        $user_id = str_replace("user_", "", $post_id);
        $value = go_get_user_display_name(  $user_id );
    }
    return $value;

}
add_filter('acf/load_value/name=go_nickname', 'go_display_display_name', 10, 3);

/**
 * WEBSITE
 */
function go_update_website( $value, $post_id, $field  ) {
    if (substr($post_id, 0 , 5) === 'user_') {
        $user_id = str_replace("user_", "", $post_id);
        update_user_option( $user_id, 'go_website', $value, false );
    }
    return $value;
}
//add_filter('acf/update_value/name=go_website', 'go_update_website', 5, 3);
add_filter('acf/update_value/key=field_5d1aeb63330ce', 'go_update_website', 5, 3);
add_filter('acf/update_value/key=field_5cd4f996c0d86', 'go_update_website', 5, 3);
add_filter('acf/update_value/key=field_5d8674ff61b2c', 'go_update_website', 5, 3);

function go_display_website( $value, $post_id, $field  ) {
    echo "<span id='go_display_website' style='display: none;'>$post_id</span>";
    if (substr($post_id, 0 , 5) === 'user_') {
        $user_id = str_replace("user_", "", $post_id);
        $value = get_user_option( 'go_website', $user_id );
    }
    if(!$value){
        $value = '';
    }
    return $value;
}
//add_filter('acf/load_value/name=go_website', 'go_display_website', 10, 3);
add_filter('acf/load_value/key=field_5d1aeb63330ce', 'go_display_website', 10, 3);
add_filter('acf/load_value/key=field_5cd4f996c0d86', 'go_display_website', 10, 3);
add_filter('acf/load_value/key=field_5d8674ff61b2c', 'go_display_website', 10, 3);

function go_update_avatar( $value, $post_id, $field  ) {
    if (substr($post_id, 0 , 5) === 'user_') {
        $user_id = str_replace("user_", "", $post_id);
        update_user_option( $user_id, 'go_avatar', $value, false );
    }
    return $value;

}
add_filter('acf/update_value/name=go_avatar', 'go_update_avatar', 5, 3);

function go_display_avatar( $value, $post_id, $field  ) {
    if (substr($post_id, 0 , 5) === 'user_') {
        $user_id = str_replace("user_", "", $post_id);
        $value = get_user_option( 'go_avatar', $user_id );
    }
    return $value;

}
add_filter('acf/load_value/name=go_avatar', 'go_display_avatar', 10, 3);

//removes sections and seats from metadata before resaving them
function go_remove_sections_and_seats($user_id) {
    $key = go_prefix_key('go_section');
    delete_user_meta($user_id, $key);

    $key = go_prefix_key('go_seat');
    delete_user_meta($user_id, $key);
}
add_action('edit_user_profile_update', 'go_remove_sections_and_seats');

function go_update_sections( $value, $post_id, $field  ) {
    global $sections_cleared;

    if (substr($post_id, 0 , 5) === 'user_') {
        $user_id = str_replace("user_", "", $post_id);


        //remove sections and seats previously saved
        if(empty($sections_cleared)) {
            go_remove_sections_and_seats($user_id);
            $sections_cleared = true;
            global $sections_cleared;
        }

        $key = go_prefix_key('go_section');
        add_user_meta( $user_id, $key, $value, false );//need to use add user meta with prefix added so multiple options can be added
        //update_user_option($user_id, 'go_section', $value);//update option adds prefix, but only can save one option
    }
    $GLOBALS['section'] = $value;
    return '';

}
add_filter('acf/update_value/name=user-section', 'go_update_sections', 5, 3);
//add_filter('acf/update_value/key=field_5cd5031deb292', 'go_update_sections', 5, 3);

function go_update_seats( $value, $post_id, $field  ) {

    if (substr($post_id, 0 , 5) === 'user_') {
        $user_id = str_replace("user_", "", $post_id);
        $key = go_prefix_key('go_seat');
        $section = $GLOBALS['section'];
        $myvalue = $value . "_" . $section;
        //update_user_option($user_id, 'go_seat', $myvalue);//update option adds prefix, but only can save one option
        add_user_meta( $user_id, $key, $myvalue, false );//need to use add user meta with prefix added so multiple options can be added
    }
    return '';
}
add_filter('acf/update_value/name=user-seat', 'go_update_seats', 10, 3);

function go_display_sections_and_seats( $value, $post_id, $field  ) {
    if (substr($post_id, 0 , 5) === 'user_') {
        $user_id = str_replace("user_", "", $post_id);

        $seat_key = go_prefix_key('go_seat');
        $section_key = go_prefix_key('go_section');

        $sections = get_user_meta($user_id, $section_key, false);
        $seats = get_user_meta($user_id, $seat_key, false);


        $hook = (isset($GLOBALS['hook_suffix']) ?  $GLOBALS['hook_suffix'] : null);
        if($hook === 'user-edit.php'){
            $section_fields = array("field_5cd5031deb292");//backend editing
            $seat_fields = array("field_5cd5031deb293");
        }else{
           // $section_field = "field_5d1bca2e8c585";//multiple, no repeats
           // $seat_field = "field_5d1bca2e8c586";

            $section_fields = array("field_5cd4f7b4498dd", "field_5d1bca108c582", "field_5d1bc17d82db9",
                "field_5d1bcaa48c58b", "field_5d1bca2e8c585", "field_5d1bc9e48c57e", "field_5d1bd3f76f8a4", "field_5d1bd3f46f8a1",
                "field_5d1bc8bf8c577", "field_5d1bd3f26f89e", "field_5d1bd3ee6f89b", "field_5d1bc9fa8c580");

            $seat_fields = array("field_5cd4f7b449909", "field_5d1bca108c583", "field_5d1bcaa48c58c", "field_5d1bca2e8c586",
                "field_5d1bd3f76f8a5", "field_5d1bd3f46f8a2", "field_5d1bd3f26f89f", "field_5d1bd3ee6f89c", "field_5d1bc9918c57a", "field_5d1bc9ae8c57c");
        }
        if (!empty($sections)) {
            $value = array();
            $i = 0;
            foreach ($sections as $section) {
                if(!empty($section)) {
                    $term = get_term($section);
                    if (!empty($term)) {
                        foreach ($section_fields as $section_field) {
                            $value[$i][$section_field] = $section;
                        }
                        //$name = $term->name;
                        //echo 'Section: ' . $name;
                        if (!empty($seats)) {
                            //$name = get_option('options_go_seats_name');
                            $seat = $seats[$i];
                            $arr = explode("_", $seat, 2);
                            $first = $arr[0];
                            if (!empty($first)) {
                                //echo ": $name " . $first;
                                foreach ($seat_fields as $seat_field) {
                                    $value[$i][$seat_field] = $first;
                                }
                            }
                            $i++;

                        }
                        // echo "<br>";
                    }
                }
            }
        }
    }
    return $value;
}
add_filter('acf/load_value/name=go_section_and_seat', 'go_display_sections_and_seats', 10, 3);

//for debugging
function go_display_post_id( $value, $post_id, $field  ) {
    $name = $field['name'];
    echo "<span id='go_display_post_id' style='display: none;'>$post_id $name</span>";
    return $value;
}
add_filter('acf/load_value', 'go_display_post_id', 10, 3);