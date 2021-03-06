<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/31/18
 * Time: 12:25 PM
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


get_header();

/////////////////////USER HEADER
    $user = get_query_var('uname');
    $user_obj = get_user_by('id',$user);
    if($user_obj) {
        $user_id = $user_obj->ID;

        $current_user_id = get_current_user_id();


        $is_admin = go_user_is_admin();

        $user_fullname = $user_obj->first_name . ' ' . $user_obj->last_name;
        $user_login = $user_obj->user_login;

        //$user_display_name = $user_obj->display_name;
        $user_display_name = go_get_user_display_name(  $user_id );
        //$user_website = $user_obj->user_url;
        //$user_website = go_get_website( $user_id );

        $page_title = $user_display_name . "'s Blog";


        ?>
        <script>
            document.title = "<?php echo $page_title; ?>";//set page title
        </script><?php
        $use_local_avatars = get_option('options_go_avatars_local');
        $user_avatar_id = get_user_option('go_avatar', $user_id);
        $user_avatar = wp_get_attachment_image($user_avatar_id);


        ?>
        <div id='go_stats_lite_wrapper'>

            <?php

            go_stats_header($user_id, true, true, false, true, true);
            if (($current_user_id === $user_id) || $is_admin) {
                go_leaderboard_filters('blog', $user_id);
            }

            ?>
        </div>

        <?php

        /// END USER HEADER






        wp_localize_script( 'go_frontend', 'IsReader', 'true' );

        //echo "success";

        //video options
        $go_lightbox_switch = get_option( 'go_video_lightbox_toggle_switch' );
        if($go_lightbox_switch === false){
            $go_lightbox_switch = 1;
        }
        $go_video_unit = get_option ('go_video_width_type_control');
        if ($go_video_unit == '%'){
            $percent = get_option( 'go_video_width_percent_control' );
            if($percent === false){
                $percent = 100;
            }
            $go_fitvids_maxwidth = $percent."%";
        }else{
            $pixels = get_option( 'go_video_width_px_control' );
            if($pixels === false){
                $pixels = 400;
            }
            $go_fitvids_maxwidth = $pixels."px";
        }

        $current_user_id = get_current_user_id();
        $cards = get_user_option('go_use_cards', $current_user_id);
        if($cards == "true"){
            $checked = 'checked';
        }else{
            $checked = '';
        }
        echo "<div id='go_cards_toggle_wrapper'><input id='go_cards_toggle' type='checkbox' name='cards' style='display: inline;' $checked> View with Cards</div><br>";
        echo "<div id='go_wrapper' data-lightbox='{$go_lightbox_switch}' data-maxwidth='{$go_fitvids_maxwidth}' >";

        echo "
          <div id='go_posts_wrapper' >";
        go_reader_get_posts(null, null, $order, $user_id);
        echo "</div></div>";





    }else{
    $user_id = 0;
    echo "<div style='padding:30px;'>This user does not exist.</div>";
    }

    //go_hidden_footer();
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
            jQuery('#wpadminbar').css('z-index', 99999);
            go_stats_links();
        });

    </script>
<?php

get_footer();

