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

get_header();
echo "<div class='go_reader_wrapper' style='max-width: 1100px; margin: 0 auto;'>";
echo "<h2 style='padding-top:10px;'>Reader</h2>";
go_make_reader();

echo "</div>";

get_footer();
