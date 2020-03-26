<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 10/13/18
 * Time: 10:25 PM
 */

/**
 * @param $user_id
 * @param bool $show_stats_link
 * @param bool $show_internal_links
 * @param bool $show_blog_link
 * @param bool $is_clipboard
 * @param $website_link
 * @param $login
 */
function go_user_links($user_id, $show_stats_link = false, $show_internal_links = false, $show_blog_link = false, $is_clipboard = false, $website_link = null, $map = null ) {
    //if current user is admin, set all to true
    global $wpdb;
    $is_admin = false;
    if (!$is_clipboard) { //if this is NOT the clipboard
        $current_id = get_current_user_id();
        $is_admin = go_user_is_admin();
        //if this is an admin
      // if ($current_id == $user_id) {//if this is the current user
           //$is_current_user = true;
      //  }
    }

    echo" <div class='go_user_links'>";



    if (($show_stats_link || $is_clipboard) && is_user_logged_in()){//show the stats link
            echo "<div class='go_user_link_stats go_user_link' uid='{$user_id}'><a href='javascript:void(0);';'><i class='fas fa-chart-area ' aria-hidden='true'></i></a></div>";
    }

    //show the map on the clipboard
    if ($is_clipboard || ($map & $is_admin & $show_internal_links)){
        echo "<div class='go_user_link go_user_map' data-user_id='{$user_id}'><a href='javascript:void(0);'><i class='fas fa-sitemap' aria-hidden='true'></i></a></div>";
    }

    //show profile link to admin unless it is an archive
    if ($show_internal_links && ($is_admin || $is_clipboard)) {
        if($is_clipboard){
            echo "<div class='go_user_link'><a onclick='go_user_profile_link({$user_id})' href='javascript:void(0);'><i class='fas fa-user' aria-hidden='true'></i></a></div>";
        }else if($is_admin) {
            $user_edit_link = get_edit_user_link($user_id);
            echo "<div class='go_user_link'><a href='$user_edit_link' target='_blank'><i class='fas fa-user' aria-hidden='true'></i></a></div>";
        }
    }

    //show blog link unless it is the blog page
    if ($show_blog_link) {
        $blog_toggle = get_option('options_go_blogs_toggle');
        if ($blog_toggle) {
            /*if ($is_clipboard) {
                $info_login = $login;
            } else {
                $user_info = get_userdata($user_id);
                $info_login = $user_info->user_login;
            }*/
            $user_blog_link = get_site_url(null, '/user/' . $user_id);
            echo " <div class='go_user_link'><a href='$user_blog_link' target='_blank'><i class='fas fa-thumbtack'></i></a></div>";
        }
    }

    //show messages to admin
    if($show_internal_links && ($is_clipboard || $is_admin)){
        echo "<div class='go_stats_messages_icon go_user_link ' data-uid='" . $user_id . "' ><a href='javascript:void(0);' ><i class='fas fa-bullhorn' aria-hidden='true'></i></a></div>";
        //make the messages icon a link to this user
    }


    //show the website link
    if (is_null($website_link)) {
        $website_link = go_get_website($user_id);
    }
    if (!empty($website_link)) {
        echo " <div class='go_user_link'><a href='$website_link' target='_blank'><i class='fas fa-globe-americas' aria-hidden='true'></i></a></div>";
    }


    echo "</div>";

}
//has ajax call
function go_user_profile_link(){
    $user_id = $_POST['uid'];
    $user_id = intval($user_id);

    $user_edit_link = get_edit_user_link($user_id);
    echo $user_edit_link;
    die();
}
/**
 * @param $check_type
 * @param $result
 * @param $stage
 * @param $time
 * @param $bonus
 * @return string
 */
/*
function go_result_link($check_type, $result, $stage, $time, $bonus = false){
    if ($bonus){
        $stage = 'Bonus ' . $stage ;
    }
    else{
        $stage = 'Stage: ' . $stage ;
    }
    $link = '';
    if ($check_type == 'URL'){
        $link = "<a href='{$result}' class='tooltip' target='_blank'><span class=\"dashicons dashicons-admin-site\"></span><span class=\"tooltiptext\">{$stage} at <br> {$time}</span></a>";
    }
    else if ($check_type == 'upload'){
        $image_url = wp_get_attachment_url($result);
        $is_image = wp_attachment_is_image($result);
        if ($is_image) {
            $link = "<a href='javascript:void(0);' class='tooltip' data-featherlight='{$image_url}'><span class=\"dashicons dashicons-format-image\"></span> <span class=\"tooltiptext\">{$stage} at <br> {$time}</span></a>";
        }else{
            $link = "<a href='{$image_url}' class='tooltip' target='_blank'><span class=\"dashicons dashicons-media-default\"></span><span class=\"tooltiptext\">{$stage} at <br> {$time}</span></a>";
        }
    }
    else if ($check_type == 'blog'){
        $link = "<a href='javascript:void(0);' onclick='return false' id='$result' class='go_blog_lightbox tooltip' target='_blank'><span class=\"dashicons dashicons-admin-post\"></span><span class=\"tooltiptext\">{$stage} at <br> {$time}</span></a>";
        //$link = "<a href='javascript:;' id='$result' class='go_blog_lightbox' target='_blank'><span class=\"dashicons dashicons-admin-post\"></span>{$stage} at <br> {$time}</a>";

    }
    return $link;

}*/

/**
 * @param $check_type
 * @param $result
 * @param $stage
 * @param $time
 * @param $bonus
 * @return string
 */
/*
function go_bonus_result_link($check_type, $result, $stage, $time, $bonus = true){
    if ($bonus){
        $stage = 'Bonus ' . $stage ;
    }
    else{
        $stage = 'Stage: ' . $stage ;
    }
    $link = '';
    if ($check_type == 'URL'){
        $link = "<a href='{$result}' class='tooltip' target='_blank'><span class=\"dashicons dashicons-admin-site\"></span>{$stage} at <br> {$time}</a>";
    }
    else if ($check_type == 'upload'){
        $image_url = wp_get_attachment_url($result);
        $is_image = wp_attachment_is_image($result);
        if ($is_image) {
            $link = "<a href='javascript:void(0);' class='tooltip' data-featherlight='{$image_url}'><span class=\"dashicons dashicons-format-image\"></span>{$stage} at <br> {$time}</a>";
        }else{
            $link = "<a href='{$image_url}' class='tooltip' target='_blank'><span class=\"dashicons dashicons-media-default\"></span>{$stage} at <br> {$time}</a>";
        }
    }
    else if ($check_type == 'blog'){
        //echo "<a href='javascript:;' id='$result' class='go_blog_lightbox tooltip' target='_blank'><span class=\"dashicons dashicons-admin-post\"></span><span class=\"tooltiptext\">{$stage} at <br> {$time}</span></a>";
        $link = "<a href='javascript:;' id='$result' class='go_blog_lightbox' target='_blank'><span class=\"dashicons dashicons-admin-post \"></span>{$stage} at {$time}</a>";

    }
    return $link;

}
*/
