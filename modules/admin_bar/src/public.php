<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-05-11
 * Time: 09:07
 */

if ( !in_array($request_uri, ['/login/','/?login=failed', '/?login=empty', '/lostpassword/', '/?lostpassword=invalid', '/?login=checkemail'], true ) ) {
    add_action('wp_head', 'go_player_bar_v5');

    function go_user_bar_dynamic_styles() {

        $bkg_color = get_option('options_go_user_bar_background_color');
        $link_color = get_option('options_go_user_bar_link_color');
        $hover_color = get_option('options_go_user_bar_hover_color');

        ?>
        <style type="text/css" media="screen">
            #go_user_bar { background-color:<?php echo $bkg_color; ?>; }
            #go_user_bar a:link { color:<?php echo $link_color; ?>; text-decoration: none; }
            #go_user_bar a:visited { color:<?php echo $link_color; ?>; text-decoration: none; }
            #go_user_bar a:hover { color:<?php echo $hover_color; ?>; text-decoration: none; }
            #go_user_bar a:active { color:<?php echo $hover_color; ?>; text-decoration: underline; }
        </style>
        <?php

    }
    add_action( 'wp_head', 'go_user_bar_dynamic_styles', 99 );


    function go_player_bar_v5() {

        echo '<div id="go_user_bar"><div id="go_user_bar_inner" style="display: none;">';
        //get options for what to show
        $go_home_switch = get_option( 'options_go_home_toggle' );
        $go_search_switch = get_option( 'options_go_search_toggle' );
        $go_map_switch = get_option( 'options_go_locations_map_toggle' );
        $go_store_switch = get_option( 'options_go_store_toggle' );
        $go_stats_switch = get_option( 'options_go_stats_toggle' );
        $go_blog_switch = get_option('options_go_blogs_toggle');


        $user_id = get_current_user_id();
        $is_admin = go_user_is_admin($user_id);


        if (is_user_logged_in()) {


            //displays Timer in admin bar
            $post_id = get_the_ID();
            $timer_on = get_post_meta($post_id, 'go_timer_toggle', true);
            if ($timer_on) {

                $atts = shortcode_atts(array(
                    'id' => '', // ID defined in Shortcode
                    'cats' => '', // Cats defined in Shortcode
                ), '');
                $id = $atts['id'];
                //$custom_fields = get_post_custom($id); // Just gathering some data about this task with its post id
                echo '<div id="go_timer"><i class="fa fa-clock-o ab-icon" aria-hidden="true"></i><div><span class="days"></span>d : </div><div><span class="hours"></span>h : </div><div><span class="minutes"></span>m : </div><div><span class="seconds"></span>s</div>';
            }


            /**
             * Get the percentage for the XP Bar/health Bar and the Loot for the totals
             * Show bars and create dropdown
             */
            $xp_toggle = get_option('options_go_loot_xp_toggle');
            $gold_toggle = get_option('options_go_loot_gold_toggle');
            $health_toggle = get_option('options_go_loot_health_toggle');

            $user_loot = go_get_loot($user_id);

            if ($health_toggle || $xp_toggle || $health_toggle) {
                echo '<div id="go_user_bar_loot" class="userbar_dropdown">';
                echo '<div class="userbar_dropbtn narrow_content go_user_bar_icon"><i class="fas fa-bars ab-icon" aria-hidden="true"></i></div>';

                echo '<div class="userbar_dropbtn wide_content">';


                if ($health_toggle || $xp_toggle) {
                    echo "<div>";

                    if ($xp_toggle) {
                        // the user's current amount of experience (points)
                        //$go_current_xp = go_get_user_loot($user_id, 'xp');
                        $go_current_xp = $user_loot['xp'];

                        $rank = go_get_rank($user_id);
                        $rank_num = $rank['rank_num'];
                        $current_rank = $rank['current_rank'];
                        $current_rank_points = $rank['current_rank_points'];
                        $next_rank = $rank['next_rank'];
                        $next_rank_points = $rank['next_rank_points'];

                        $go_ranks_name = get_option('options_go_loot_xp_levels_name_singular');
                        //$points_array = $go_option_ranks['points'];

                        /*
                         * Here we are referring to last element manually,
                         * since we don't want to modifiy
                         * the arrays with the array_pop function.
                         */
                        //$max_rank_index = count( $points_array ) - 1;
                        //$max_rank_points = (int) $points_array[ $max_rank_index ];

                        if ($next_rank_points != false) {
                            $rank_threshold_diff = $next_rank_points - $current_rank_points;
                            $pts_to_rank_threshold = $go_current_xp - $current_rank_points;
                            $pts_to_rank_up_str = "L{$rank_num}: {$pts_to_rank_threshold} / {$rank_threshold_diff}";
                            $percentage = $pts_to_rank_threshold / $rank_threshold_diff * 100;
                            //$color = barColor( $go_current_health, 0 );
                            $color = "#39b54a";
                        } else {
                            $pts_to_rank_up_str = $current_rank;
                            $percentage = 100;
                            $color = "gold";
                        }
                        if ($percentage <= 0) {
                            $percentage = 0;
                        } else if ($percentage >= 100) {
                            $percentage = 100;
                        }
                        echo '<div id="go_admin_bar_progress_bar_border" class="progress-bar-border">' . '<div id="go_admin_bar_progress_bar" class="progress_bar" ' .
                            'style="width: ' . $percentage . '%; background-color: ' . $color . ' ;">' .
                            '</div>' .
                            '<div id="points_needed_to_level_up" class="go_admin_bar_text">' .
                            $pts_to_rank_up_str .
                            '</div>' .
                            '</div>';
                    }

                    if ($health_toggle) {
                        // the user's current amount of bonus currency,
                        // also used for coloring the admin bar
                        //$go_current_health = go_get_user_loot($user_id, 'health');
                        $go_current_health = $user_loot['health'];
                        $health_percentage = intval($go_current_health / 2);
                        $name = get_option("options_go_loot_health_abbreviation");;
                        if ($health_percentage <= 0) {
                            $health_percentage = 0;
                        } else if ($health_percentage >= 100) {
                            $health_percentage = 100;
                        }
                        echo '<div id="go_admin_health_bar_border" class="progress-bar-border">' . '<div id="go_admin_bar_health_bar" class="progress_bar" ' . 'style="width: ' . $health_percentage . '%; background-color: red ;">' . '</div>' . '<div id="health_bar_percentage_str" class="go_admin_bar_text">' . $name . ": " . $go_current_health . "%" . '</div>' . '</div>';

                    }

                    echo "</div>";
                }

                if ($gold_toggle) {
                    // the user's current amount of currency
                    //$go_current_gold = go_get_user_loot($user_id, 'gold');
                    $go_current_gold = $user_loot['gold'];
                    echo '<div id="go_admin_bar_gold" class="admin_bar_loot">' . go_display_longhand_currency('gold', $go_current_gold, false, false, true) . '</div>';
                }
                echo "</div>";//end of the dropdown hover area


                //start of dropdown content
                $dropdown_content = "<div class='userbar_dropdown-content'>";
                if ($xp_toggle && ($go_current_xp != 0)) {
                    $dropdown_content .= '<div id="go_admin_bar_rank">' . $go_ranks_name . ' ' . $rank_num . ": " . $current_rank . '</div><br>';
                    $dropdown_content .= '<div id="go_admin_bar_xp">' . go_display_longhand_currency('xp', $go_current_xp) . '</div><br>';

                }

                if ($gold_toggle && ($go_current_gold != 0)) {
                    $dropdown_content .= '<div id="go_admin_bar_gold">' . go_display_longhand_currency('gold', $go_current_gold, false, true, false) . '</div><br>';
                    //$wp_admin_bar->add_node(array('id' => 'go_gold', 'title' => '<div id="go_admin_bar_gold">' . go_display_shorthand_currency('gold', $go_current_gold, false, 'names') . '</div>', 'href' => '#', 'parent' => 'go_info',));
                }

                if ($health_toggle && $go_current_health) {
                    $dropdown_content .= '<div id="go_admin_bar_health">' . go_display_longhand_currency('health', $go_current_health) . '</div>';
                    //$wp_admin_bar->add_node(array('id' => 'go_health', 'title' => '<div id="go_admin_bar_health">' . go_display_longhand_currency('health', $go_current_health) . '</div>', 'href' => '#', 'parent' => 'go_info',));
                }
                $dropdown_content .= "</div>";

                echo $dropdown_content;
            }

            echo '</div>';//end of dropdown

        }
        if ($go_home_switch) {
            //acf_form_head();
            $go_home_link = get_site_url();
            echo '<div class="go_user_bar_icon go_user_bar_home"><a href="'.$go_home_link.'"><i class="fas fa-home ab-icon" aria-hidden="true"></i><br><div class="go_player_bar_text">Home</div></a></div>';
        }
        if (is_user_logged_in()) {
            if ($go_stats_switch) {
                //acf_form_head();
                $stats_name = get_option('options_go_stats_name');
                echo '<div class="go_user_bar_icon go_user_bar_stats"><a href="javascript:void(0)"><i class="fas fa-chart-area ab-icon" aria-hidden="true"></i><br><div class="go_player_bar_text">' . $stats_name . '</div><div id="go_stats_page"></div></a><script>  jQuery(".go_user_bar_stats").one("click", function(){ go_admin_bar_stats_page_button()}); </script></div>';
            }

            $leaderboard_toggle = get_option('options_go_stats_leaderboard_toggle');
            if ($leaderboard_toggle) {

                $go_leaderboard_link = get_site_url(null, 'leaderboard');


                echo '<div class="go_user_bar_icon"><a href="'.$go_leaderboard_link.'"><i class="fas fa-trophy ab-icon" aria-hidden="true"></i><br><div class="go_player_bar_text" id="go_store_page">Leaderboard</div></a></div>';

            }


            if ($go_blog_switch) {
                //acf_form_head();
                //$stats_name = get_option('options_go_stats_name');

                $user_info = get_userdata($user_id);
                $userloginname = $user_info->user_login;
                $user_blog_link = get_site_url(null, '/user/' . $userloginname);

                echo '<div class="go_user_bar_icon"><a href="'.$user_blog_link.'"><i class="fas fa-thumbtack ab-icon" aria-hidden="true"></i><br><div class="go_player_bar_text">My Blog</div></a></div>';
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


        $go_login_link = get_site_url(null, 'login');

        if (is_user_logged_in()) {
            $login_text = 'Profile';
        }
        else{
            $login_text = 'Login';
        }
        $avatar = get_user_option('go_avatar');
        if (is_user_logged_in() && $avatar) {

            $avatar = wp_get_attachment_image($avatar, array('29', '29'));
            echo '<div class="go_user_bar_icon userbar_dropdown"><a href="' . $go_login_link . '">'.$avatar.'<br><div class="go_player_bar_text" id="go_user_link">' . $login_text . '</div></a>';
        }else {
            echo '<div class="go_user_bar_icon userbar_dropdown"><a href="' . $go_login_link . '"><i class="fas fa-user ab-icon" aria-hidden="true"></i><br><div class="go_player_bar_text" id="go_user_link">' . $login_text . '</div></a>';
        }
        if (is_user_logged_in()) {

            echo ' <div class="userbar_dropdown-content" style="text-align: left;"><div><a href="'.$go_login_link.'">View Profile</a></div><br><div> <a href="/wp-login.php?action=logout" class="go_logout">Logout</a></div><br><div><a href="#" class="go_password_change_modal">Change Password</a></div></div>';
                
        }
        echo "</div>";

        if ($go_search_switch) {
            echo '<div class="go_user_bar_icon userbar_dropdown"><a href="javascript:void(0)"><i class="fas fa-search ab-icon"></i></a>';

            //start of dropdown content
            $dropdown_content = "<div class='userbar_dropdown-content search '>";
            $dropdown_content .= '
                <form role="search" method="get" id="go_admin_bar_task_search_form" class="searchform" action="' . home_url('/') . '">
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

        echo "<div id='go_password_change_lightbox'></div>";


    }
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
    }*/
    if (!empty($_POST['user_id'])) {
        $current_user_id = (int)$_POST['user_id'];
    }
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
    }

    ?>

    <div id="go_leaderboard_wrapper" class="go_datatables">
        <div id="go_leaderboard_filters">
            <span>Section:<?php go_make_tax_select('user_go_sections', 'clipboard_', 'id', $section, $section_name); ?></span>
            <span>Group:<?php go_make_tax_select('user_go_groups', 'clipboard_', 'id', $group, $group_name); ?></span>

        </div>


        <div id="go_leaderboard_flex">

            <div id="go_leaderboard" class="go_leaderboard_layer">

                <table id='go_leaders_datatable' class='pretty display'>
                    <thead>
                    <tr>
                        <th></th>
                        <?php
                        if ($full_name_toggle || $is_admin){
                            echo "<th class='header'><a href='#'>Full Name</a></th>";
                        }
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


