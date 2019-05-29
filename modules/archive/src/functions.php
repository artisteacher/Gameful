<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 10/13/18
 * Time: 8:41 PM
 */






/**
 *
 */
//add_action( 'init', 'go_custom_rewrite_archive' );
/**
 *
 */
function go_custom_rewrite_archive() {
    // we are telling wordpress that if somebody access yoursite.com/all-post/user/username
    // wordpress will do a request on this query var yoursite.com/index.php?query_type=user_blog&uname=username
    //flush_rewrite_rules();

    //add_rewrite_rule( "^user/([^/]*)/page/(.*)/?", 'index.php?query_type=user_blog&uname=$matches[1]&paged=$matches[2]', "top");
    add_rewrite_rule( "^user_archive/(.*)", 'index.php?query_type=user_archive&uname=$matches[1]', "top");

}

/**
 * @param $vars
 * @return array
 */
function go_custom_query_archive($vars ) {
    // we will register the two custom query var on wordpress rewrite rule
    $vars[] = 'query_type';
    $vars[] = 'view';
    return $vars;
}
add_filter( 'query_vars', 'go_custom_query_archive' );

/**
 * @param $template
 * @return string
 */
function go_template_loader_archive($template){

    // get the custom query var we registered
    $query_var = get_query_var('query_type');

    // load the custom template if ?query_type=all_post is  found on wordpress url/request
    if( $query_var == 'user_archive' ){
        $directory = plugin_dir_path( __FILE__ ) . '/templates/go_save_blog.php';
        //$directory = plugin_dir_path( __FILE__ ) . '/templates/go_save_blog.php';
        add_filter( 'show_admin_bar', '__return_false' );
        return $directory;
    }
    return $template;
}
add_filter('template_include', 'go_template_loader_archive');

