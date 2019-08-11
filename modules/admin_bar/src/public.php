<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-05-11
 * Time: 09:07
 */



add_action( 'wp_head', 'go_user_bar_dynamic_styles', 99 );
function go_user_bar_dynamic_styles() {

    $bkg_color = get_option('options_go_user_bar_background_color');
    $link_color = get_option('options_go_user_bar_link_color');
    $hover_color = get_option('options_go_user_bar_hover_color');
    $drop_bkg_color = get_option('options_go_user_bar_dropdown_bkg');

    ?>
    <style type="text/css" media="screen">
        #go_user_bar_top { background-color:<?php echo $bkg_color; ?> !important; color:<?php echo $link_color; ?>; }
        #go_user_bar a:link { color:<?php echo $link_color; ?>; text-decoration: none; }
        #go_user_bar a:visited { color:<?php echo $link_color; ?>; text-decoration: none; }
        #go_user_bar a:hover { color:<?php echo $hover_color; ?>; text-decoration: none; }
        #go_user_bar a:active { color:<?php echo $hover_color; ?>; text-decoration: underline; }
        .progress-bar-border { border-color:<?php echo $link_color; ?>; }
        #go_user_bar .userbar_dropdown-content {background-color: <?php echo $drop_bkg_color; ?>;  color:<?php echo $link_color; ?>; }
    </style>
    <?php

}

$is_gameful = is_gameful();
$blog_id = get_current_blog_id();
if(!is_main_site() || !$is_gameful){
    add_action('wp_head', 'go_player_bar_v5');
}

function go_player_bar_v5() {

    echo '<div id="go_user_bar"><div id="go_user_bar_top"><div id="go_user_bar_inner" style="display: none;">';

    //get options for what to show
    $go_home_switch = get_option( 'options_go_home_toggle' );
    $go_search_switch = get_option( 'options_go_search_toggle' );
    $go_map_switch = get_option( 'options_go_locations_map_toggle' );
    $go_store_switch = get_option( 'options_go_store_toggle' );
    $go_stats_switch = get_option( 'options_go_stats_toggle' );
    $go_blog_switch = get_option('options_go_blogs_toggle');
    $go_leaderboard_switch = get_option('options_go_stats_leaderboard_toggle');

    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option('options_go_loot_health_toggle');


    $user_id = get_current_user_id();
    $is_admin = go_user_is_admin($user_id);


    if (is_user_member_of_blog() || go_user_is_admin()) {


        //displays Timer in admin bar
        $post_id = get_the_ID();
        $timer_on = go_post_meta($post_id, 'go_timer_toggle', true);
        if ($timer_on) {

            /*$atts = shortcode_atts(array(
                'id' => '', // ID defined in Shortcode
                'cats' => '', // Cats defined in Shortcode
            ), '');
            $id = $atts['id'];*/
            //$custom_fields = go_post_meta($id); // Just gathering some data about this task with its post id
            echo '<div id="go_timer"><i class="fa fa-clock-o ab-icon" aria-hidden="true"></i><div><span class="days"></span>d : </div><div><span class="hours"></span>h : </div><div><span class="minutes"></span>m : </div><div><span class="seconds"></span>s</div></div>';
        }




        //$user_loot = go_get_loot($user_id);

        if ($gold_toggle) {
           $gold_bar = go_gold_bar($user_id);
           echo $gold_bar;

        }

        echo '</div>';

    }
    if ($go_home_switch) {
        //acf_form_head();
        $go_home_link = get_site_url();
        echo '<div class="go_user_bar_icon go_user_bar_home"><a href="'.$go_home_link.'"><i class="fas fa-home ab-icon" aria-hidden="true"></i><br><div class="go_player_bar_text">Home</div></a></div>';
    }
    if (is_user_member_of_blog() || go_user_is_admin()) {
        if ($go_stats_switch) {
            //acf_form_head();
            $stats_name = get_option('options_go_stats_name');
            echo '<div class="go_user_bar_icon go_user_bar_stats"><a href="javascript:void(0)"><i class="fas fa-chart-area ab-icon" aria-hidden="true"></i><br><div class="go_player_bar_text">' . $stats_name . '</div><div id="go_stats_page"></div></a><script>  jQuery(".go_user_bar_stats").one("click", function(){ go_stats_lightbox_page_button()}); </script></div>';
        }

        if ($go_leaderboard_switch) {
            $go_leaderboard_name = urlencode(get_option('options_go_stats_leaderboard_name'));
            $go_leaderboard_link = get_site_url(null, $go_leaderboard_name);


            echo "<div class='go_user_bar_icon'><a href='$go_leaderboard_link'><i class='fas fa-trophy ab-icon' aria-hidden='true'></i><br><div class='go_player_bar_text' id='go_leaderboard_page'>$go_leaderboard_name</div></a></div>";

        }


        if ($go_blog_switch) {
            //acf_form_head();
            //$stats_name = get_option('options_go_stats_name');

            $user_info = get_userdata($user_id);
            $userloginname = $user_info->user_login;
            $user_blog_link = get_site_url(null, '/user/' . $userloginname);

            echo '<div class="go_user_bar_icon"><a href="'.$user_blog_link.'"><i class="fas fa-thumbtack ab-icon" aria-hidden="true"></i><br><div class="go_player_bar_text">Blog</div></a></div>';
        }
    }

    if ($go_map_switch) {
        $map_url = get_option('options_go_locations_map_map_link');
        $go_map_link = (string)$map_url;
        //$go_map_link = get_permalink(get_page_by_path($go_map_link));
        $go_map_link = get_site_url(null, $go_map_link);
        $name = get_option('options_go_locations_map_name');
        echo '<div class="go_user_bar_icon"><a href="'.$go_map_link.'"><i class="fas fa-sitemap ab-icon" aria-hidden="true"></i><br><div id="go_map_page" class="admin_map   go_player_bar_text">' . $name . '</div></a></div>';
    }

    if ($go_store_switch) {
        $go_store_link = get_option('options_go_store_store_link');
        //$go_store_link = get_permalink(get_page_by_path($go_store_link));
        $go_store_link = get_site_url(null, $go_store_link);
        $name = get_option('options_go_store_name');
        echo '<div class="go_user_bar_icon"><a href="'.$go_store_link.'"><i class="fas fa-exchange-alt ab-icon" aria-hidden="true"></i><br><div class="go_player_bar_text" id="go_store_page">' . $name . '</div></a></div>';
    }

    /*if ($is_admin) {
        //$go_store_link = get_permalink(get_page_by_path($go_store_link));
        $reader_link = get_site_url(null, 'reader');
        echo '<div class="go_user_bar_icon"><i class="fas fa-book-reader ab-icon" aria-hidden="true"></i><br><div class="go_player_bar_text" id="go_reader_link">Reader</div></div>';

        $clipboard_link = get_admin_url() . 'admin.php?page=go_clipboard';
        echo '<div class="go_user_bar_icon"><i class="fas fa-clipboard-list ab-icon" aria-hidden="true"></i><br><div class="go_player_bar_text" id="go_clipboard_adminbar" style="float: right;">Clipboard</div></div>';

    }*/

    //if is logged in,
        //if is blog user, show profile
        //else show join
    //else show login--always show wp login.
    /*
    if (is_user_logged_in()) {

        if (is_user_member_of_blog()){

        }else{
            $join_text = 'Join';
            $go_join_link = get_site_url(null, 'join');
        }

    }
    else{

        //

    }



        //$avatar = wp_get_attachment_image($avatar, array('29', '29'));
        //echo "<div class='go_user_bar_icon userbar_dropdown'><a href='$go_login_link'>$avatar<br><div class='go_player_bar_text' id='go_user_link'>$login_text</div></a>";
    else {
        echo '<div class="go_user_bar_icon userbar_dropdown"><a href="' . $go_login_link . '"><i class="fas fa-user ab-icon" aria-hidden="true"></i><br><div class="go_player_bar_text" id="go_user_link">' . $login_text . '</div></a>';
    }*/

    //$go_login_link = wp_login_url( get_site_url(null, 'login'));

    $avatar = (is_int(get_user_option('go_avatar')) ?  wp_get_attachment_image(get_user_option('go_avatar'), array('29', '29')) : '<i class="fas fa-user ab-icon" aria-hidden="true"></i>');
    if (is_user_logged_in()) {
        //$log_out_link = get_site_url(null, 'logout');
        if(is_gameful()){
            $log_out_link = home_url('signin?action=logout' );
        }else{
            $log_out_link = wp_logout_url();
        }

        if (is_user_member_of_blog() || go_user_is_admin()){//show profile
            //$avatar = get_user_option('go_avatar');
            $text = 'Profile';
            $dropdown_text = 'View Profile';
            $link = get_site_url(null, 'profile');

            //echo ' <div class="userbar_dropdown-content" style="text-align: left;"><div><a href="'.$go_login_link.'">View Profile</a></div><br><div> <a href="'.$go_login_link.'" class="go_logout">Logout</a></div><br><div><a href="#" class="go_password_change_modal">Change Password</a></div></div>';
        }
        else{//show join
            $text = 'Join';
            $dropdown_text = 'Join Site';
            $link = get_site_url(null, 'join');
        }

        echo "<div class='go_user_bar_icon userbar_dropdown'><a href='$link'>$avatar<br><div class='go_player_bar_text' id='go_user_link'>$text</div></a>";
        //dropdown with profile and logout
        echo "<div class='userbar_dropdown-content' style='text-align: left;'><div><a href='$link'>$dropdown_text</a></div><br><div> <a href='$log_out_link' class='go_logout'>Logout</a></div></div>";

    }else{//not logged in, show login and no dropdown
        $login_text = 'Login';
        if(is_gameful()) {
            $blog_id = get_current_blog_id();
            $go_login_link = get_site_url(1, 'login');
            $go_login_link = network_site_url('signin?redirect_to=' . $go_login_link . '?blog_id=' . $blog_id);
        }else{
            $go_login_link = wp_login_url('login');
        }
        //$go_login_link = network_site_url ('signin?redirect_to=https://gameful.me/login?blog_id='.$blog_id);
        echo "<div class='go_user_bar_icon userbar_dropdown'><a href='$go_login_link'><i class='fas fa-user ab-icon' aria-hidden='true'></i><br><div class='go_player_bar_text' id='go_user_link'>$login_text</div></a>";
        //echo "<div class='go_user_bar_icon userbar_dropdown'><div id='go_login_link'><i class='fas fa-user ab-icon' aria-hidden='true'></i><br><div class='go_player_bar_text' id='go_user_link'>$login_text</div></div>";
    }


    echo "</div>";

    if ($go_search_switch) {
        echo '<div id="userbar_search"  class="go_user_bar_icon"><div class="userbar_dropdown_toggle search"><a href="javascript:void(0)"><i class="fas fa-search ab-icon"></i></a></div>';
        //start of dropdown content
        $dropdown_content = "<div class='userbar_dropdown-content search '>";
        //$dropdown_content .= '<div style="float:right;"><a href="javascript:;" onclick="go_toggle_search()"><i class="far fa-times-circle"></i></a></div>';
        $dropdown_content .= '
            <form role="search" method="get" id="go_admin_bar_task_search_form" class="searchform" action="' . home_url('/') . '" style="clear:both;">
                <div><label class="screen-reader-text" for="s">' . __('Search for:') . '</label>
                    <input type="text" value="' . get_search_query() . '" name="s" id="go_admin_bar_task_search_input" placeholder="Search for ' . strtolower(get_option("go_tasks_plural_name")) . '..."/>
                    <input type="hidden" name="post_type[]" value="tasks"/>
                    <input type="submit" id="go_admin_bar_task_search_submit" value="' . esc_attr__('Search') . '"/>
                </div>
            </form>';
        $dropdown_content .= '</div></div>';

        echo $dropdown_content;

    }


    echo "</div></div>";

    echo "<div id='user_bar_bottom'>";
    if ($xp_toggle) {
        $progress_bar = xp_progress_bar($user_id);
        echo $progress_bar;
    }

    if ($health_toggle) {
        $health_bar = go_health_bar($user_id);
        echo $health_bar;
    }

    echo "</div></div>";

    echo "<div id='go_password_change_lightbox'></div>";


}

/**Leaderboard Stuff Below
 *
 */

/**
 *
 */
function go_stats_leaderboard() {
    wp_localize_script( 'go_frontend', 'IsLeaderboard', 'true' );
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer('go_stats_leaderboard_');
    /* if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_stats_leaderboard' ) ) {
         echo "refresh";
         die( );
     }
    if (!empty($_POST['user_id'])) {
        $current_user_id = (int)$_POST['user_id'];
    }*/



    // prepares tab titles
    $xp_name = get_option("options_go_loot_xp_name");
    $gold_name = get_option("options_go_loot_gold_name");
    $health_name = get_option("options_go_loot_health_name");
    $badges_name = get_option('options_go_badges_name_singular') . " Count";



    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option('options_go_loot_health_toggle');

    $badges_toggle = get_option('options_go_badges_toggle');

    //is the current user an admin
    $current_user_id = get_current_user_id();
    $is_admin = go_user_is_admin($current_user_id);

    $full_name_toggle = get_option('options_go_full-names_toggle');

    /*
    $section = intval(get_user_option('go_leaderboard_section', $current_user_id));
    $section_name ='';
    if (is_int($section) & $section > 0){
        $parent = get_term($section);
        $section_name = ( mb_strlen( $parent->name ) > 50 ) ? mb_substr( $parent->name, 0, 49 ) . '...' : $parent->name;
    }


    $group = intval(get_user_option('go_leaderboard_group', $current_user_id));
    $group_name = '';
    if (is_int($group) & $group > 0) {
        $parent = get_term($group);
        $group_name = (mb_strlen($parent->name) > 50) ? mb_substr($parent->name, 0, 49) . '...' : $parent->name;
    }*/

    $section = 'loading';
    $section_name = " ";
    $group = 'loading';
    $group_name =  " ";
    $go_leaderboard_name = ucwords(get_option('options_go_stats_leaderboard_name'));
    ?>

    <div id="go_leaderboard_wrapper" class="go_datatables">
        <h2 style='padding-top:10px;'><?php echo $go_leaderboard_name; ?></h2>
        <div id="go_leaderboard_filters">
            <span>Section:<?php go_make_tax_select('user_go_sections', false, $section, $section_name, false); ?></span>
            <span>Group:<?php go_make_tax_select('user_go_groups', false, $group, $group_name, false); ?></span>

        </div>


        <div id="go_leaderboard_flex">

            <div id="go_leaderboard" class="go_leaderboard_layer">

                <table id='go_leaders_datatable' class='pretty display'>
                    <thead>
                    <tr>
                        <th></th>
                        <?php
                        /*
                        if ($full_name_toggle == 'full' || $is_admin){
                            echo "<th class='header'><a href='#'>Full Name</a></th>";
                        }else if ($full_name_toggle == 'first'){
                            echo "<th class='header'><a href='#'>First Name</a></th>";
                        }*/
                        ?>
                        <th class='header'><a href="#">Name</a></th>
                        <th class='header'><a href="#">Links</a></th>
                        <?php
                        if ($xp_toggle) {
                            echo "<th class='header'><a href='#'>" . $xp_name . "</a></th>";
                        }
                        if ($gold_toggle) {
                            echo "<th class='header'><a href='#'>" . $gold_name . "</a></th>";
                        }
                        if ($health_toggle) {
                            echo "<th class='header'><a href='#'>" . $health_name . "</a></th>";
                        }
                        if ($badges_toggle) {
                            echo "<th class='header'><a href='#'>" . $badges_name . "</a></th>";
                        }
                        ?>

                    </tr>
                    </thead>
                    <tbody></table>
            </div>

        </div>
    </div>


    <?php

}






