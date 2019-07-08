<?php
/**
 * DOMAIN VALIDATION
 * Action was added to nsl plugin to allow it to validate on social login.
 * Social login validation only works with domains set in options on site 1
 * Because of this, the domains in site 1 are added to the lists from the sub-site.
 * --they are universal on a multisite.
 * On sub-sites, the validation happens:
 * --with social login, on the join page after login/register
 * --on the registration form.
 */

//return list of domains that are allowed
function go_get_domain_restrictions(){
    $current_blog_id = get_current_blog_id();
    $blog_ids = array($current_blog_id, 1);
    $domains = array();
    $x = 0;
    foreach ($blog_ids as $blog_id) {

        if ($blog_id == 1) {//adding blog 1 domains to the list, but only once if current blog is blog 1
            if ($x> 0){
                continue;
            }
            $x++;
            switch_to_blog(1);

        }
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

        restore_current_blog();
    }
    return $domains;
}

function validate_email_against_domains($email){
    $domains = go_get_domain_restrictions();
    $user_domain = substr(strrchr($email, "@"), 1);
    $is_valid = in_array($user_domain, $domains);
    return $is_valid;
}

//filter for the social login
//always gets domains from the main blog (1)
add_action('nsl_limit_domains', 'go_limit_domains');
function go_limit_domains($email){
    $is_valid = validate_email_against_domains($email);
    if(!$is_valid){
        $go_login_link = get_site_url(null, 'login');
        wp_redirect($go_login_link . '?login=bad_domain');
        exit;
    }
}

//USE THIS FOR INSTUCTIONS ABOUT DOMAINS
//* Add custom message to WordPress login page
add_filter( 'login_message', 'go_login_message' );
function go_login_message($message){
    $go_domain_restrictions_message = go_domain_restrictions_message();
    if ( !empty($go_domain_restrictions_message) ){
        $message .= "<div style ='background-color: white; 
                                    margin-left: 0;
                                    padding: 26px 24px 46px;
                                    font-size: .9em;
                                    overflow: hidden;
                                    background: #fff;
                                    box-shadow: 0 1px 3px rgba(0,0,0,.13);'>";
        $message .= "<br>$go_domain_restrictions_message";
        $message .= "</div>";


    }
    return $message;
}

function go_domain_restrictions_message($message = null ) {
    $domains = go_get_domain_restrictions();

    if (!empty($domains)){
        $message = "This site only allows registration with email addresses that are in the following domains:<br>";
        $message .= '<ul class="acf-notice" style="padding:0 20px;">';
        $message .= '<li class="acf-notice" >' . implode( '</li><li>', $domains) . '</li>';
        $message .= '</ul>';
    }
    return $message;
}



/**
 * SET LOGIN REDIRECT BASED ON OPTIONS
 */
//after login, user should be sent to the join page and then redirected if already a member
add_action( 'login_redirect', 'go_user_redirect', 10, 3 );
function go_user_redirect( $redirect_to, $request, $user )
{
    if (isset($user) && ($user instanceof WP_User)) {

        $user_id = $user->ID;
        $redirect_url = go_get_user_redirect($user_id);
        if (!empty ($redirect_url)) {
            return  $redirect_url;
        } else {
            return;
        }

    }

}

function go_get_user_redirect($user_id = null){
    $page = '';
    if(is_user_member_of_blog($user_id)) {
        $redirect_to = get_option('options_go_landing_page_radio', 'home');
        //$page = get_option('options_go_landing_page_on_login', '');

        if ($redirect_to == 'store') {
            $page = get_option('options_go_store_store_link', 'store');
        } else if ($redirect_to == 'map') {
            $page = get_option('options_go_locations_map_map_link', 'map');
        } else if ($redirect_to == 'custom') {
            $page = get_option('options_go_landing_page_on_login', '');
        }

    }else{
        $page = 'join';
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

add_filter('init', 'go_user_page_redirect');
function go_user_page_redirect(){
    $this_page =  go_get_page_uri();

    if ($this_page === 'join' || $this_page === 'register' || $this_page === 'profile') {
        if (is_user_logged_in()) {

            if (is_user_member_of_blog()) {
                $redirect_path = 'profile';//if logged in this is a profile page
            } else {
                $redirect_path = 'join';//if logged in, but not a member of this blog, this is a join page
            }
        } else {
            $redirect_path = 'register';//else not logged in and this is a register page.
        }

        $updated = (isset($_GET['updated'])) ? $_GET['updated'] : 0;
        if ($updated && $this_page === 'join') {
            $user_id = get_current_user_id();
            $landing_page = go_get_user_redirect($user_id);
            wp_redirect($landing_page);
            exit;
        }

        if ($this_page !== $redirect_path) {
            wp_redirect(site_url($redirect_path));
            exit;
        }
    }
}

/**
 * ADD LOGIN PAGE
 */

add_action('init', 'go_login_rewrite');
function go_login_rewrite(){
    $page_name = 'login';
    add_rewrite_rule( $page_name, 'index.php?' . $page_name . '=true', "top");
    //add_rewrite_rule( $page_name, 'wp-login.php?' . $page_name . '=true', "top");
}

// Query Vars
add_filter( 'query_vars', 'go_login_query_var' );
function go_login_query_var( $vars ) {
    $page_name = 'login';
    $vars[] = $page_name;
    return $vars;
}

/* Template Include */
add_filter('template_include', 'go_login_template_include', 1, 1);
function go_login_template_include($template){
    $page_name = 'login';
    global $wp_query; //Load $wp_query object

    $page_value = ( isset($wp_query->query_vars[$page_name]) ? $wp_query->query_vars[$page_name] : false ); //Check for query var

    if ($page_value && ($page_value == "true" || $page_value == "failed" || $page_value == "empty" || $page_value == "checkemail")) { //Verify "blah" exists and value is "true".
        return plugin_dir_path(__FILE__).'templates/template.php'; //Load your template or file
    }

    return $template; //Load normal template when $page_value != "true" as a fallback
}


add_filter('login_form_middle','go_added_login_field');
function go_added_login_field(){
    //Output your HTML
    //this adds the lost password field
    //and a hidden input field that is used to show the error messages
    $link = get_site_url(null, 'lostpassword');
    $additional_field = "<div style='float: right'><a href='$link'>Lost Password?</a></div>
                        <div class='login-custom-field-wrapper' style='display: none;'>
         <input type='text' tabindex='20' size='20' value='true' class='input' id='go_frontend_login' name='go_frontend_login'>
     </div>";

    return $additional_field;
}

/*
 * Following 2 functions used to show login error message in same page
 */

function go_login_failed() {
    $is_gameon = (isset($_POST['go_frontend_login']) ? $_POST['go_frontend_login'] : false);
    if ($is_gameon) {
        $page_name = 'login';
        //$login_page = get_home_url($page_name);
        $go_login_link = get_site_url(null, 'login');
        wp_redirect($go_login_link . '?' . $page_name . '=failed');

        //add_rewrite_rule( $page_name, 'index.php?' . $page_name . '=true&login=failed', "top");
        exit;
    }
}
add_action('wp_login_failed', 'go_login_failed');

function go_verify_username_password($user, $username, $password){
    $is_gameon = (isset($_POST['go_frontend_login']) ? $_POST['go_frontend_login'] : false);
    if ($is_gameon) {
        $page_name = 'login';
        //$login_page = get_home_url($page_name);
        $go_login_link = get_site_url(null, 'login');
        if ($username == "" || $password == "") {
             wp_redirect($go_login_link . '?'.$page_name. '=empty');
             exit;
        }
    }
}
add_filter('authenticate', 'go_verify_username_password', 1, 3);



/**
 * ADD RESET PASSWORD PAGE
 */

add_action('init', 'go_reset_password_rewrite');
function go_reset_password_rewrite(){
    $page_name = 'lostpassword';
    add_rewrite_rule( $page_name, 'index.php?' . $page_name . '=true', "top");
}

// Query Vars
add_filter( 'query_vars', 'go_reset_password_query_var' );
function go_reset_password_query_var( $vars ) {
    $page_name = 'lostpassword';
    $vars[] = $page_name;
    return $vars;
}


/* Template Include */
add_filter('template_include', 'go_reset_password_template_include', 1, 1);
function go_reset_password_template_include($template){
    $page_name = 'lostpassword';
    global $wp_query; //Load $wp_query object

    $page_value = ( isset($wp_query->query_vars[$page_name]) ? $wp_query->query_vars[$page_name] : false ); //Check for query var "blah"

    if ($page_value && ($page_value == "true" || $page_value == "invalid") ) { //Verify "blah" exists and value is "true".
        return plugin_dir_path(__FILE__).'templates/reset_password_template.php'; //Load your template or file
    }

    return $template; //Load normal template when $page_value != "true" as a fallback
}

//Redirect on submit a lostpassword form
add_action( 'login_form_lostpassword', 'do_password_lost' );
function do_password_lost() {
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
        $errors = retrieve_password();
        $page_name = 'lostpassword';
        $login_page = get_home_url( $page_name);
        if ( is_wp_error( $errors ) ) {
            // Errors found
            //$redirect_url = home_url( 'member-password-lost' );
            //$redirect_url = add_query_arg( 'errors', join( ',', $errors->get_error_codes() ), $redirect_url );
            $page_name = 'lostpassword';
            $login_page = get_home_url( $page_name);
            wp_redirect($login_page . '?'.$page_name. '=invalid');
            exit;
        } else {
            // Email sent
            //$redirect_url = home_url( 'member-login' );
            // $redirect_url = add_query_arg( 'checkemail', 'confirm', $redirect_url );
            $page_name = 'login';
            $login_page = get_home_url( $page_name);
            wp_redirect($login_page . '?'.$page_name. '=checkemail');
            exit;
        }

        //wp_redirect( $redirect_url );
        //exit;
    }
}

/**
 * ADD PROFILE PAGE
 */

add_action('init', 'go_profile_rewrite');
function go_profile_rewrite(){
    $page_name = 'profile';
    add_rewrite_rule( $page_name, 'index.php?' . $page_name . '=true', "top");
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
    $page_name = 'profile';
    global $wp_query; //Load $wp_query object

    $page_value = ( isset($wp_query->query_vars[$page_name]) ? $wp_query->query_vars[$page_name] : false ); //Check for query var "blah"

    if ($page_value && $page_value == "true" ) { //Verify "blah" exists and value is "true".

        acf_form_head();
        return plugin_dir_path(__FILE__).'templates/user.php'; //Load your template or file

    }

    return $template; //Load normal template when $page_value != "true" as a fallback
}




/************************
 * ADD Registration PAGE
 */

add_action('init', 'go_registration_rewrite');
function go_registration_rewrite(){
    $page_name = 'register';
    add_rewrite_rule( $page_name, 'index.php?' . $page_name . '=true', "top");
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
    $page_name = 'register';
    global $wp_query; //Load $wp_query object

    $page_value = ( isset($wp_query->query_vars[$page_name]) ? $wp_query->query_vars[$page_name] : false ); //Check for query var "blah"

    if ($page_value && $page_value == "true" ) { //Verify "blah" exists and value is "true".
        acf_form_head();
        return plugin_dir_path(__FILE__).'templates/user.php'; //Load your template or file
    }

    return $template; //Load normal template when $page_value != "true" as a fallback
}

add_action(  'login_init', 'user_registration_login_init', 0  );
function user_registration_login_init () {
    $url = get_site_url();
    if( ! is_user_logged_in() ) {
        $action = (isset($_GET['action']) ?  $_GET['action'] : null);
        if($action == 'register') {
            wp_redirect($url . '/register');
            exit;
        }
    }
}


/************************
 * ADD Join PAGE
 */

add_action('init', 'go_join_rewrite', 0);
function go_join_rewrite(){
    $page_name = 'join';
    add_rewrite_rule( $page_name, 'index.php?' . $page_name . '=true', "top");
    //add_rewrite_rule( 'wp-login.php\?action\=register', 'index.php?' . $page_name . '=true', "top");

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
    $page_name = 'join';
    global $wp_query; //Load $wp_query object

    $page_value = ( isset($wp_query->query_vars[$page_name]) ? $wp_query->query_vars[$page_name] : false ); //Check for query var "blah"

    if ($page_value && $page_value == "true" ) { //Verify "blah" exists and value is "true".
        acf_form_head();
        return plugin_dir_path(__FILE__).'templates/user.php'; //Load your template or file
    }

    return $template; //Load normal template when $page_value != "true" as a fallback
}


/****************
 * Process User Registration
 * https://code.tutsplus.com/tutorials/build-a-custom-wordpress-user-flow-part-2-new-user-registration--cms-23810
 */

/**
 * THIS CHANGES THE DEFAULT WORDPRESS USER REGISTRATION--DO I NEED THIS
 * @param $email
 * @param $first_name
 * @param $last_name
 * @return WP_Error
 */
/*
function register_user( $email, $first_name, $last_name ) {
    $errors = new WP_Error();

    // Email address is used as both username and email. It is also the only
    // parameter we need to validate
    if ( ! is_email( $email ) ) {
        $errors->add( 'email', get_error_message( 'email' ) );
        return $errors;
    }

    if ( username_exists( $email ) || email_exists( $email ) ) {
        $errors->add( 'email_exists', get_error_message( 'email_exists') );
        return $errors;
    }

    // Generate the password so that the subscriber will have to check email...
    $password = wp_generate_password( 12, false );

    $user_data = array(
        'user_login'    => $email,
        'user_email'    => $email,
        'user_pass'     => $password,
        'first_name'    => $first_name,
        'last_name'     => $last_name,
        'nickname'      => $first_name,
    );

    $user_id = wp_insert_user( $user_data );
    wp_new_user_notification( $user_id, $password );

    return $user_id;
}
*/
/*
//THIS CHANGES THE DEFAULT WORDPRESS USER REGISTRATION--DO I NEED THIS
add_action( 'login_form_register', 'do_register_user'  );
function do_register_user() {
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
        $redirect_url = home_url( 'registration' );

        if ( ! get_option( 'users_can_register' ) ) {
            // Registration closed, display error
            $redirect_url = add_query_arg( 'register-errors', 'closed', $redirect_url );
        } else {
            $email = $_POST['email'];
            $first_name = sanitize_text_field( $_POST['first_name'] );
            $last_name = sanitize_text_field( $_POST['last_name'] );

            $result = register_user( $email, $first_name, $last_name );

            if ( is_wp_error( $result ) ) {
                // Parse errors into a string and append as parameter to redirect
                $errors = join( ',', $result->get_error_codes() );
                $redirect_url = add_query_arg( 'register-errors', $errors, $redirect_url );
            } else {
                // Success, redirect to login page.
                $redirect_url = home_url( 'registration' );
                $redirect_url = add_query_arg( 'registered', $email, $redirect_url );
            }
        }

        wp_redirect( $redirect_url );
        exit;
    }
}*/

function go_include_password_checker(){
    $minPassword = get_option('options_minimum_password_strength');
    wp_localize_script( 'go_frontend', 'hasPassword', 'true' );
    wp_localize_script( 'go_frontend', 'minPassword', $minPassword );
}

function get_error_message( $error_code ) {
    switch ( $error_code ) {
        case 'empty_username':
            return __( 'You do have an email address, right?', 'personalize-login' );

        case 'empty_password':
            return __( 'You need to enter a password to login.', 'personalize-login' );

        case 'invalid_username':
            return __(
                "We don't have any users with that email address. Maybe you used a different one when signing up?",
                'personalize-login'
            );

        case 'incorrect_password':
            $err = __(
                "The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",
                'personalize-login'
            );
            return sprintf( $err, wp_lostpassword_url() );

        default:
            break;
    }

    return __( 'An unknown error occurred. Please try again later.', 'personalize-login' );
}

//prints a afc form on the front end
function go_acf_user_form_func( $groups = array(), $fields = array(), $post_id = false, $return = '' ) {

    if (!$post_id){
        $uid = get_current_user_id();
        $post_id = 'user_'.$uid;
    }


    // if ( ! empty ( $form_id ) && ! empty ( $uid ) ) {
    if ( ! empty ( $groups )  ) {
        $options = array(
            'post_id' => $post_id,
            'field_groups' => $groups,
            //'fields' => array('field_5cd3638830f17'),
            'return' => add_query_arg( 'updated', 'true', $return )
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
            'fields' => $fields
        );

        //if this is a registration page, redirect to the default game on page on success
        //else for other pages add the updated=true query variable
        if ($post_id ==='register'){
            $options['return'] = go_get_user_redirect();
        }
        else {
            $options['return'] = add_query_arg('updated', 'true', $return);
        }

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

    if (!empty($user_id_email)) {//if this user id exists in the database
        if ($user_id != $user_id_email) {//if this $user_id is not the current user, return an error
            $valid = 'An account with this email already exists.';
            return $valid;
        }
    }

    $limit_domains_toggle = get_option('options_limit_domains_toggle');
    if ($limit_domains_toggle) {
        $email = $value;
        $is_valid_email = validate_email_against_domains($email);
        if (!$is_valid_email) {
            $valid = go_domain_restrictions_message();
            return $valid;
        }
    }
    //else this is a new email and is a valid for saving
    return $valid;
}

//Validate that an user with this username doesn't already exist.
add_filter('acf/validate_value/name=user_name', 'go_validate_uname', 10, 4);
function go_validate_uname($valid, $value, $field, $input){
    $username_exists = username_exists( $value );
    if ($username_exists) {//if this username exists in the database
            $valid = 'This username already exists';
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

    echo "<div style='display: none;'><div class='go_password_change_container'>";

    $groups = array();
    $fields = array('field_5cd3640730f19', 'field_5cd3638830f17', 'field_5cd363d130f18', 'field_5cd52c8f46296');
    $form =  go_acf_user_form_func($groups, $fields, 'password_reset', get_site_url(null, '/profile'));
    echo $form;
    //echo '<span class="password-strength"></span>';

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
        $newpassword = (isset($_POST['acf']['field_5cd3638830f17']) ?  $_POST['acf']['field_5cd3638830f17'] : null);

        wp_set_password( $newpassword, $user_id );
        wp_clear_auth_cookie();
        wp_set_current_user ( $user_id );
        wp_set_auth_cookie  ( $user_id );
    }

    //UPDATE A PROFILE
    if (substr($post_id, 0 , 5) === 'user_') {

        $wp_user_id = str_replace("user_", "", $post_id);

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

            $display = (isset($_POST['acf']['field_5cd1d13769aa9']) ?  $_POST['acf']['field_5cd1d13769aa9'] : null);
            $display = sanitize_text_field($display);//display name
            //$_POST['acf']['field_5cd1d13769aa9'] = '';

            $new_password = (isset($_POST['acf']['field_5cd3638830f17']) ?  $_POST['acf']['field_5cd3638830f17'] : null);
            $new_password = sanitize_text_field($new_password);
            $_POST['acf']['field_5cd3638830f17'] = '';
            $_POST['acf']['field_5cd363d130f18'] = '';//clear the confirm password field

            //$sections_seats = $_POST['acf']['field_5cd4f7b43672b'];//sections and seats saved with own function
            //$_POST['acf']['field_5cd4f7b43672b'] = array();//clear the field
            switch_to_blog(1);
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
            restore_current_blog();

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
        else {
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

            $website = (isset($_POST['acf']['field_5cd4f996c0d86']) ? $_POST['acf']['field_5cd4f996c0d86'] : null);
            //$website = (isset($_POST['acf']['field_5cd4f996c0d86']) ?  $_POST['acf']['field_5cd4f996c0d86'] : null);
            if (!empty($website)) {

                $args = array(
                    'ID' => $wp_user_id,
                    'user_url' => esc_attr($website)
                );
                wp_update_user($args);
            }

            if($page_uri === 'join' && !is_user_member_of_blog()){
                $blog_id = get_current_blog_id();
                add_user_to_blog( $blog_id, $wp_user_id, 'subscriber' );
            }
        }
    }






    /*
    if (substr($post_id, 0 , 5) === 'user_') {

        $user_id = str_replace("user_", "", $post_id);
        go_remove_sections_and_seats($user_id);

    }*/



    return $post_id;
}

//removes sections and seats from metadata before resaving them
function go_remove_sections_and_seats($user_id) {
        $key = go_prefix_key('go_section');
        delete_user_meta($user_id, $key);

        $key = go_prefix_key('go_seat');
        delete_user_meta($user_id, $key);
}

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
    return $value;

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
    return $value;
}
add_filter('acf/update_value/name=user-seat', 'go_update_seats', 10, 3);
//add_filter('acf/update_value/key=field_5cd5031deb293', 'go_update_seats', 10, 3);

//this changes the logo on the default wordpress login
function go_login_logo()
{
    $logo = get_option('options_go_login_logo');

    if ($logo) {

        //$url = wp_get_attachment_image_src($logo, '250, 250');
        $url = wp_get_attachment_image_src($logo, 'medium');
        $url = $url[0];
        ?>
        <style type="text/css">
            #login h1 a, .login h1 a {
                background-image: url(<?php echo $url; ?>);
                width: 100%;
                height: 150px;
                margin: 0 auto;
                background-size: unset;
                background-size: auto 150px;;
            }
        </style>

        <?php

    }
}
add_action( 'login_enqueue_scripts', 'go_login_logo' );


