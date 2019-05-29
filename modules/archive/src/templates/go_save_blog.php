<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-05-21
 * Time: 06:01
 */


/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

//get_header();
wp_head();


$user_obj = wp_get_current_user();
$current_user_id = get_current_user_id();

$view = get_query_var('view');
if ($view == 'private'){
    $is_private = true;
}else{
    $is_private = false;
}
$is_private = true;

$is_admin = go_user_is_admin($current_user_id);

$user_fullname = $user_obj->first_name . ' ' . $user_obj->last_name;
$user_login = $user_obj->user_login;
$user_display_name = $user_obj->display_name;
$user_website = $user_obj->user_url;
$page_title = $user_display_name . "'s Blog";


?>
<script>
    document.title = "<?php echo $page_title; ?>";//set page title
</script><?php
$use_local_avatars = get_option('options_go_avatars_local');
$user_avatar_id = get_user_option('go_avatar', $current_user_id);
$user_avatar = wp_get_attachment_image($user_avatar_id);


?>
<div id='go_stats_lite_wrapper'>
    <div id='go_stats_lay_lite' class='go_datatables'>
        <div id='go_stats_header_lite'>
            <div class="go_stats_id_card">
                <div class='go_stats_gravatar'><?php echo $user_avatar; ?></div>

                <div class='go_stats_user_info'>
                    <?php echo "<h2>{$user_fullname}</h2>{$user_display_name}<br>"; ?>



                </div>


            </div>

        </div>
    </div>
</div>
<div id='loader_container' style='display:none; height: 250px; width: 100%; padding: 10px 30px; '>
    <div id='loader'>
        <i class='fas fa-spinner fa-pulse fa-4x'></i>
    </div>
</div>
<?php


go_get_blog_posts($current_user_id, true, $is_private);

?>
    <script>

        jQuery( document ).ready( function() {
            //console.log("opener1");
            //jQuery(".go_blog_opener").one("click", function(e){
            //    go_blog_opener( this );
            //});
            // remove existing editor instance
            //tinymce.execCommand('mceRemoveEditor', true, 'go_blog_post');
            //tinymce.execCommand('mceRemoveEditor', true, 'go_blog_post_lightbox');
            //jQuery('#go_hidden_mce').remove();
            //jQuery('#go_hidden_mce_edit').remove();
            jQuery('html').attr('style', 'margin-top: 0px !important');

            jQuery('body').attr('style', 'margin-top: 0px !important');
        });

    </script>
<?php

wp_footer();