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



$is_admin = go_user_is_admin();
if($is_admin){
    acf_form_head();
}

function go_store_title() {
    $store_title = get_option('options_go_store_title');
    return $store_title; // add dynamic content to this title (if needed)
}
add_action( 'pre_get_document_title', 'go_store_title' );

get_header();
//$map_name = get_option( 'options_go_locations_map_map_link');
go_make_map('store_types');


get_footer();


/*
get_header();
$store_name = get_option( 'options_go_store_store_link');

go_make_store_new();


get_footer();
*/