<?php

/**
 * ADD LOGIN PAGE
 */

add_action('init', 'go_login_rewrite');
function go_login_rewrite(){
    $page_name = 'login';
    add_rewrite_rule( $page_name, 'index.php?' . $page_name . '=true', "top");
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

    $page_value = ( isset($wp_query->query_vars[$page_name]) ? $wp_query->query_vars[$page_name] : false ); //Check for query var "blah"

    if ($page_value && ($page_value == "true" || $page_value == "failed" || $page_value == "empty" || $page_value == "checkemail")) { //Verify "blah" exists and value is "true".

        return plugin_dir_path(__FILE__).'templates/template.php'; //Load your template or file
    }

    return $template; //Load normal template when $page_value != "true" as a fallback
}


add_filter('login_form_middle','my_added_login_field');
function my_added_login_field(){
    //Output your HTML
    //this adds the lost password field
    //and a hidden input field that is used to show the error messages
    $additional_field = '<div style="float: right"><a href="/lostpassword">Lost Password?</a></div>
                        <div class="login-custom-field-wrapper" style="display: none;">
         <input type="text" tabindex="20" size="20" value="true" class="input" id="go_frontend_login" name="go_frontend_login">
     </div>';

    return $additional_field;
}

/*
 * Following 2 functions used to show login error message in same page
 */

function login_failed() {
    $is_gameon = (isset($_POST['go_frontend_login']) ? $_POST['go_frontend_login'] : false);
    if ($is_gameon) {
        $page_name = 'login';
        $login_page = get_home_url($page_name);
        wp_redirect($login_page . '?' . $page_name . '=failed');

        //add_rewrite_rule( $page_name, 'index.php?' . $page_name . '=true&login=failed', "top");
        exit;
    }
}
add_action('wp_login_failed', 'login_failed');

function verify_username_password($user, $username, $password){
    $is_gameon = (isset($_POST['go_frontend_login']) ? $_POST['go_frontend_login'] : false);
    if ($is_gameon) {
        $page_name = 'login';
        $login_page = get_home_url($page_name);
        if ($username == "" || $password == "") {
             wp_redirect($login_page . '?'.$page_name. '=empty');
             exit;
        }
    }
}
add_filter('authenticate', 'verify_username_password', 1, 3);



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
        } else {
            // Email sent
            //$redirect_url = home_url( 'member-login' );
            // $redirect_url = add_query_arg( 'checkemail', 'confirm', $redirect_url );
            $page_name = 'login';
            $login_page = get_home_url( $page_name);
            wp_redirect($login_page . '?'.$page_name. '=checkemail');
        }

        //wp_redirect( $redirect_url );
        exit;
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
        return plugin_dir_path(__FILE__).'templates/profile_template.php'; //Load your template or file

    }

    return $template; //Load normal template when $page_value != "true" as a fallback
}


/************************
 * ADD Registration PAGE
 */

add_action('init', 'go_registration_rewrite');
function go_registration_rewrite(){
    $page_name = 'registration';
    add_rewrite_rule( $page_name, 'index.php?' . $page_name . '=true', "top");
}

// Query Vars
add_filter( 'query_vars', 'go_registration_query_var' );
function go_registration_query_var( $vars ) {
    $page_name = 'registration';
    $vars[] = $page_name;
    return $vars;
}


/* Template Include */
add_filter('template_include', 'go_registration_template_include', 1, 1);
function go_registration_template_include($template){
    $page_name = 'registration';
    global $wp_query; //Load $wp_query object

    $page_value = ( isset($wp_query->query_vars[$page_name]) ? $wp_query->query_vars[$page_name] : false ); //Check for query var "blah"

    if ($page_value && $page_value == "true" ) { //Verify "blah" exists and value is "true".
        acf_form_head();
        return plugin_dir_path(__FILE__).'templates/register_template.php'; //Load your template or file
    }

    return $template; //Load normal template when $page_value != "true" as a fallback
}


/****************
 * Process User Registration
 * https://code.tutsplus.com/tutorials/build-a-custom-wordpress-user-flow-part-2-new-user-registration--cms-23810
 */

/**
 * @param $email
 * @param $first_name
 * @param $last_name
 * @return WP_Error
 */
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
}

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
function my_acf_user_form_func( $groups = array(), $fields = array(), $post_id = false, $return = '' ) {

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

    if ($user_id_email) {//if this user id exists in the database
        if ($user_id != $user_id_email) {//if this $user_id is not the current user, return an error
            $valid = 'An account with this email already exists.';
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

//Validate that an user with this username doesn't already exist.
add_filter('acf/validate_value/key=field_5cd9f85e5f788', 'go_validate_code', 10, 4);
function go_validate_code($valid, $value, $field, $input){
    $code = get_option('options_go_registration_code');
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
    $form =  my_acf_user_form_func($groups, $fields, 'password_reset', get_site_url(null, '/profile'));
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

    //RESET PASSWORDS
    if ($post_id === 'password_reset'){
        $user_id = get_current_user_id();
        $newpassword = $_POST['acf']['field_5cd3638830f17'];

        wp_set_password( $newpassword, $user_id );
        wp_clear_auth_cookie();
        wp_set_current_user ( $user_id );
        wp_set_auth_cookie  ( $user_id );
    }

    //REGISTER A NEW USER
    if ($post_id === 'register'){
        //add a check for if user is already logged in and redirect

        //get the fields and then clear their values--the values don't need to save outside of this function

        $_POST['acf']['field_5cd9f85e5f788'] = '';//membership code


        $user_name = sanitize_text_field($_POST['acf']['field_5cd4fa743159f']);
        $_POST['acf']['field_5cd4fa743159f'] = '';



        $email = sanitize_email($_POST['acf']['field_5cd4be08e7077']);
        $_POST['acf']['field_5cd4be08e7077'] = '';

        $first = sanitize_text_field($_POST['acf']['field_5cd1d1de5491b']);
        $_POST['acf']['field_5cd1d1de5491b'] = '';

        $last = sanitize_text_field($_POST['acf']['field_5cd1d21168754']);
        $_POST['acf']['field_5cd1d21168754'] = '';

        $display = sanitize_text_field($_POST['acf']['field_5cd1d13769aa9']);
        $_POST['acf']['field_5cd1d13769aa9'] = '';

        $new_password = sanitize_text_field($_POST['acf']['field_5cd3638830f17']);
        $_POST['acf']['field_5cd3638830f17'] = '';
        $_POST['acf']['field_5cd363d130f18'] = '';//clear the confirm password field

        $sections_seats = $_POST['acf']['field_5cd4f7b43672b'];//this one is a bit of a challenge to sage
        $_POST['acf']['field_5cd4f7b43672b'] = array();

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

        $this_post = "user_".$user_id;
        update_field( 'field_5cd4f7b43672b', $sections_seats, $this_post );

        $creds = array();
        $creds['user_login'] = $user_name;
        $creds['user_password'] = $new_password;
        $creds['remember'] = false;
        $user = wp_signon( $creds, false );
        if ( is_wp_error($user) )
            echo $user->get_error_message();
    }

    //UPDATE A PROFILE
    if (substr($post_id, 0 , 5) === 'user_') {

        $wp_user_id = str_replace("user_", "", $post_id);

        $emailField = $_POST['acf']['field_5cd4be08e7077'];
        //$emailField = (isset($_POST['acf']['field_5cd4be08e7077']) ?  $_POST['acf']['field_5cd4be08e7077'] : '');
        if (isset($emailField)) {

            $args = array(
                'ID'         => $wp_user_id,
                'user_email' => esc_attr( $emailField )
            );
            wp_update_user( $args );
        }

        $website =$_POST['acf']['field_5cd4f996c0d86'];
        //$website = (isset($_POST['acf']['field_5cd4f996c0d86']) ?  $_POST['acf']['field_5cd4f996c0d86'] : null);
        if (isset($website)) {

            $args = array(
                'ID'         => $wp_user_id,
                'user_url' => esc_attr( $website )
            );
            wp_update_user( $args );
        }

    }



    return $post_id;
}


//this changes the logo on the default wordpress login
function go_login_logo()
{
    $logo = get_option('options_login_appearance_logo');

    if ($logo) {

        $url = wp_get_attachment_image_src($logo, '250, 250');
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


