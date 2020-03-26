<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-05-27
 * Time: 19:30
 */



/**
 * ADD LEADERBOARD PAGE
 */

add_action('init', 'go_leaderboard_rewrite');
function go_leaderboard_rewrite(){
    $page_name = urlencode(get_option('options_go_stats_leaderboard_name'));
    $page_name = (isset($page_name) ? $page_name : 'leaderboard');
    //$page_name = 'leaderboard';
    add_rewrite_rule($page_name, 'index.php?' . $page_name . '=true', "top");

}

// Query Vars
//adds the query var
//this is then used in the rewrite and to load the template
add_filter( 'query_vars', 'go_leaderboard_query_var' );
function go_leaderboard_query_var( $vars ) {
    if(!is_gameful() || !is_main_site()) {
        $page_name = urlencode(get_option('options_go_stats_leaderboard_name'));
        $page_name = (isset($page_name) ? $page_name : 'leaderboard');
        $vars[] = $page_name;
    }
    return $vars;

}

/* LEADERBOARD Include Template*/
add_filter('template_include', 'go_leaderboard_template_include', 1, 1);
function go_leaderboard_template_include($template){
    if(!is_gameful() || !is_main_site()) {
        $page_name = urlencode(get_option('options_go_stats_leaderboard_name'));
        $page_name = (isset($page_name) ? $page_name : 'leaderboard');
        global $wp_query; //Load $wp_query object

        $page_value = (isset($wp_query->query_vars[$page_name]) ? $wp_query->query_vars[$page_name] : false); //Check for query var "blah"

        if ($page_value && ($page_value == "true")) { //Verify "blah" exists and value is "true".

            return plugin_dir_path(__FILE__) . 'templates/leaderboard.php'; //Load your template or file
        }
    }
    return $template; //Load normal template when $page_value != "true" as a fallback
}

