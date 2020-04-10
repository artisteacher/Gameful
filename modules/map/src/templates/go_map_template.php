<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/31/18
 * Time: 12:25 PM
 */


/**
 * The template for displaying map pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */


function go_map_title() {
    $title = get_option('options_go_locations_map_title');
    return $title; // add dynamic content to this title (if needed)
}
add_action( 'pre_get_document_title', 'go_map_title' );

$is_admin = go_user_is_admin();
if($is_admin){
    acf_form_head();
    }
get_header();
//$map_name = get_option( 'options_go_locations_map_map_link');
go_make_map();


get_footer();