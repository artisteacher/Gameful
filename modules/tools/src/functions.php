<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-06-01
 * Time: 05:48
 */


/**
 *
 */

/**
 *
 */


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

