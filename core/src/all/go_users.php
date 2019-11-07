<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 10/13/18
 * Time: 8:06 PM
 */

/**
 * Filters the comment author to show the gameon display name
 */
add_filter('get_comment_author', 'go_comment_author', 10, 3);
function go_comment_author(  $author,  $comment_id,  $comment  ) {
    // Get the comment ID from WP_Query
    $user_id = $comment->user_id;
    return go_get_user_display_name($user_id);
}

add_filter('get_comment_author_url', 'go_comment_author_url', 10, 3);
function go_comment_author_url(  $author,  $comment_id,  $comment  ) {
    // Get the comment ID from WP_Query

    //$user_id = $comment->user_id;
    $user_blog_link = '';
    $user_id = (isset($comment->user_id) ?  $comment->user_id : null);
    if(!empty($user_id)) {
        $user_info = get_userdata($user_id);
        $info_login = $user_info->user_login;
        $user_blog_link = get_site_url(null, '/user/' . $info_login);
    }
    return $user_blog_link;
}

/**
 * Get user's first and last name, else just their first name, else their
 * display name. Defalts to the current user if $user_id is not provided.
 *
 * @param  mixed  $user_id The user ID or object. Default is current user.
 * @return string          The user's name.
 */
function go_get_users_name( $user_id = null ) {
    $user_info = $user_id ? new WP_User( $user_id ) : wp_get_current_user();
    if ( $user_info->first_name ) {
        if ( $user_info->last_name ) {
            return $user_info->first_name . ' ' . $user_info->last_name;
        }
        return $user_info->first_name;
    }
    $user_id = $user_info->ID;
    return go_get_user_display_name($user_id);
}

/**
 * Determines whether or not a user is an administrator with management capabilities.
 *
 * @since 3.0.0
 *
 * @param int $user_id Optional. The user ID.
 * @return boolean True if the user has the 'administrator' role and has the 'manage_options'
 *                 capability. False otherwise.
 */
function go_user_is_admin( $user_id = null ) {
    if ( empty( $user_id ) ) {
        $user_id = get_current_user_id();
    } else {
        $user_id = (int) $user_id;
    }

    if(user_can( $user_id, 'manage_options' ) || is_super_admin($user_id)) {
        return true;
    }
    return false;
}



// Deletes all rows related to a user in the individual and total tables upon deleting said user.
function go_user_delete( $user_id ) {
    global $wpdb;
    $table_name_go_totals = "{$wpdb->prefix}go_loot";
    $table_name_go_tasks = "{$wpdb->prefix}go_tasks";
    $table_name_go_actions = "{$wpdb->prefix}go_actions";

    $wpdb->delete( $table_name_go_totals, array( 'uid' => $user_id ) );
    $wpdb->delete( $table_name_go_tasks, array( 'uid' => $user_id ) );
    $wpdb->delete( $table_name_go_actions, array( 'uid' => $user_id ) );
}


//allow site admins to edit users
function mc_admin_users_caps( $caps, $cap, $user_id, $args ){

    foreach( $caps as $key => $capability ){

        if( $capability != 'do_not_allow' )
            continue;

        switch( $cap ) {
            case 'edit_user':
            case 'edit_users':
                $caps[$key] = 'edit_users';
                break;
            case 'delete_user':
            case 'delete_users':
                $caps[$key] = 'delete_users';
                break;
            case 'create_users':
                $caps[$key] = $cap;
                break;
        }
    }

    return $caps;
}
add_filter( 'map_meta_cap', 'mc_admin_users_caps', 1, 4 );
remove_all_filters( 'enable_edit_any_user_configuration' );
add_filter( 'enable_edit_any_user_configuration', '__return_true');

/**
 * Checks that both the editing user and the user being edited are
 * members of the blog and prevents the super admin being edited.
 */
function mc_edit_permission_check() {
    global $current_user, $profileuser;

    $screen = get_current_screen();

    wp_get_current_user();

    if( ! is_super_admin( $current_user->ID ) && in_array( $screen->base, array( 'user-edit', 'user-edit-network' ) ) ) { // editing a user profile
        if ( is_super_admin( $profileuser->ID ) ) { // trying to edit a superadmin while less than a superadmin
            wp_die( __( 'You do not have permission to edit this user.' ) );
        } elseif ( ! ( is_user_member_of_blog( $profileuser->ID, get_current_blog_id() ) && is_user_member_of_blog( $current_user->ID, get_current_blog_id() ) )) { // editing user and edited user aren't members of the same blog
            wp_die( __( 'You do not have permission to edit this user.' ) );
        }
    }

}
add_filter( 'admin_head', 'mc_edit_permission_check', 1, 4 );


//filters all but the subscriber and contributor role in the dropdown for all but super admin
add_filter('editable_roles', 'allow_only_default_role', 1, 1);
function allow_only_default_role($all_roles)
{

    if (is_super_admin()) {
        return $all_roles;
    }
    else {
        foreach ( $all_roles as $name => $role ) {
            if($name != 'subscriber' && $name != 'contributor' ) {
                unset($all_roles[$name]);
            }
        }

    }
    return $all_roles;
}

function posts_for_current_author($query) {
    global $pagenow;

    if( 'edit.php' != $pagenow || !$query->is_admin )
        return $query;

    if( !current_user_can( 'edit_others_posts' ) ) {
        global $user_ID;
        $query->set('author', $user_ID );
    }
    return $query;
}
add_filter('pre_get_posts', 'posts_for_current_author');


//make the blog the author page
add_filter('author_link', 'remove_author_link', 10, 1 );
function remove_author_link($link)
{

    $link = str_replace('/author/', '/user/', $link);
    return $link;
}



// Adds user id to the totals table upon user creation.
/*
function go_user_registration ( $user_id ) {
    global $wpdb;
    $table_name_go_totals = "{$wpdb->prefix}go_loot";
    $table_name_capabilities = "{$wpdb->prefix}capabilities";
    $role = get_option( 'go_role', 'subscriber' );
    $user_role = get_user_option("{$table_name_capabilities}", $user_id);
    if ( array_search( 1, $user_role ) == $role || array_search( 1, $user_role ) == 'administrator' ) {

        // this should update the user's rank metadata
        //go_update_ranks( $user_id, 0 );
        $default_health = get_option('options_go_loot_health_starting');
        // this should set the user's points to 0
        $wpdb->insert( $table_name_go_totals, array( 'uid' => $user_id), array(

            'health' => $default_health,
        ) );
    }
}*/



/*
function go_add_user_to_totals_table_at_login($user_login, $user){
    $user_id = $user->ID;
    if(is_user_member_of_blog()) {
        go_add_user_to_totals_table($user_id);
    }
}*/
//add_action('wp_login', 'go_add_user_to_totals_table_at_login', 10, 2);

