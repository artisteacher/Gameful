<?php

//Redirect to homepage after logout and remove cookies
//add_action('wp_logout','auto_redirect_after_logout');
function auto_redirect_after_logout($k){
    $HTTP_REFERER = (isset($_SERVER['HTTP_REFERER']) ?  $_SERVER['HTTP_REFERER'] : null);//page currently being loaded
    //for multisite
    if(is_gameful()) {
        $details  = get_blog_details();
        $siteurl = $details -> siteurl;
    }else{
        $siteurl = home_url();
    }

    $strip_path = str_replace($siteurl, '', $HTTP_REFERER);
    $strip_slashes = str_replace('/','',$strip_path);
    $HTTP_REFERER = strtok($strip_slashes,'?');

    if ($HTTP_REFERER !== 'join'){
        array_map(function ($k) {
            setcookie($k, FALSE, time()-YEAR_IN_SECONDS, '', COOKIE_DOMAIN);

        }, array_keys($_COOKIE));

        // Redirect to 'siteurl' since by default WordPress redirects to its login
        // URL, which actually sets a new cookie
        header('Location: '.get_option('siteurl'));


        //wp_redirect( home_url() );
        exit();
    }
}


//remove dashboard
//add_action( 'admin_menu', 'Wps_remove_tools', 99 );
function Wps_remove_tools(){
	if ( ! get_option('go_dashboard_toggle') && ! current_user_can('administrator') ){
		remove_menu_page( 'index.php' ); //dashboard
	}
}

function go_display_admin_bar() {
    $is_admin = go_user_is_admin();
    $blog_id = get_current_blog_id();
$is_logged_in = is_user_logged_in();
	if($is_admin || ($blog_id == 1 && $is_logged_in)){
	    return true;
    }
	else{
	    return false;
    }
}
add_filter( 'show_admin_bar', 'go_display_admin_bar' );


/**
 * Redirect back to homepage and not allow access to
 * WP admin for Subscribers.
 */
function go_redirect_admin(){
    if ( ! defined('DOING_AJAX') && ! current_user_can('edit_posts') ) {
        wp_redirect( site_url() );
        exit;
    }
}
add_action( 'admin_init', 'go_redirect_admin' );

/*
 * Admin Menu & Admin Bar
 */
add_action( 'admin_bar_menu', 'go_admin_bar_v5', 90);
function go_admin_bar_v5() {
    global $wp_admin_bar;

    $user_id = get_current_user_id();
    $is_admin = go_user_is_admin($user_id);

    if(is_admin_bar_showing()) {

       /* if (!is_user_logged_in()) {
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_toolbar_login',
                    'title' => 'Login',
                    'href' => wp_login_url()
                )
            );
        }*/

        $wp_admin_bar->remove_node('new-tasks');
        $wp_admin_bar->remove_node('new-go_store');




        ///
        /// add the Game On menu (options and shortcuts to admin pages) ///
        ///
        ///
        if ($is_admin ) {//only show to admin
            $wp_admin_bar->add_node(array('id' => 'go_section_pipe', 'title' => ' | ', 'href' => 'javascript:void(0)',));

            if(is_admin()){
                $url = home_url();
                $icon = '<span class="ab-icon dashicons dashicons-admin-home"></span> ';
                $sub_title = 'View Site';
            }else{
                $url = get_admin_url();
                $icon = '<span class="ab-icon dashicons dashicons-dashboard"></span> ';
                $sub_title = 'Dashboard';
            }


            $site_name = get_bloginfo( 'name' );
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_options',
                   'title' => $icon . $site_name,
                    'href' => $url,
                )
            );


            $wp_admin_bar->add_node(
                array(
                    'id' => 'site_link',
                    'title' => $sub_title,
                    'href' => $url,
                    'parent' => 'go_options',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );



            /*$wp_admin_bar->add_group(
                array(
                    'id' => 'go_site_name_menu',
                    'parent' => 'site-name',
                    'meta' => array('class' => 'go_site_name_menu')
                )
            );*/

            /*
             * Game On Links
             */
            // displays GO options page link
            /*
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_help',
                    'title' => 'Help',
                    'href' => get_admin_url() . 'admin.php?page=game-on',
                    'parent' => 'go_options',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );*/

            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_options',
                    'title' => 'Gameful Me Options',
                    'href' => get_admin_url() . 'admin.php?page=game-on-options',
                    'parent' => 'go_options',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays Task edit page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_tasks',
                    'title' => get_option('options_go_tasks_name_plural'),
                    'href' => get_admin_url() . 'edit.php?post_type=tasks',
                    'parent' => 'go_options',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays chains page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_chains',
                    'title' => get_option('options_go_tasks_name_plural') . ' Maps',
                    'href' => esc_url(get_admin_url()) . 'edit-tags.php?taxonomy=task_chains&post_type=tasks',
                    'parent' => 'go_options',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays Store Item edit page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_store',
                    'title' => get_option('options_go_store_name') . ' Items',
                    'href' => get_admin_url() . 'edit.php?post_type=go_store',
                    'parent' => 'go_options',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays Store Categories page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_store_types',
                    'title' => get_option('options_go_store_name') . ' Categories',
                    'href' => esc_url(get_admin_url()) . 'edit-tags.php?taxonomy=store_types&post_type=go_store',
                    'parent' => 'go_options',
                    'meta' => array('class' => 'go_options')
                )
            );

            // displays Badges
            $badges_toggle = get_option('options_go_badges_toggle');
            if ($badges_toggle) {
                $wp_admin_bar->add_node(array('id' => 'go_nav_badges', 'title' => ucfirst(get_option('options_go_badges_name_plural')), 'href' => esc_url(get_admin_url()) . 'edit-tags.php?taxonomy=go_badges', 'parent' => 'go_options', 'meta' => array('class' => 'go_site_name_menu_item')));
            }
/*
            // displays Store Categories page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_users',
                    'title' => 'Users',
                    'href' => esc_url(get_admin_url()) . 'users.php',
                    'parent' => 'go_options',
                    'meta' => array('class' => 'go_options')
                )
            );

            // displays User Groups page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_user_types',
                    'title' => 'User Groups',
                    'href' => esc_url(get_admin_url()) . 'edit-tags.php?taxonomy=user_go_groups',
                    'parent' => 'go_options',
                    'meta' => array('class' => 'go_options')
                )
            );
*/
            /*
             * Default WP Links
             */

            // displays Post edit page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_posts',
                    'title' => 'Posts',
                    'href' => esc_url(get_admin_url()) . 'edit.php',
                    'parent' => 'appearance'
                )
            );

            // displays Page edit page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_pages',
                    'title' => 'Pages',
                    'href' => esc_url(get_admin_url()) . 'edit.php?post_type=page',
                    'parent' => 'appearance'
                )
            );

            // displays Media Library page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_media',
                    'title' => 'Media',
                    'href' => esc_url(get_admin_url()) . 'upload.php',
                    'parent' => 'appearance'
                )
            );

            // displays Plugins page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_plugins',
                    'title' => 'Plugins',
                    'href' => esc_url(get_admin_url()) . 'plugins.php',
                    'parent' => 'appearance'
                )
            );

            // displays Users page link
            /*
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_users',
                    'title' => 'Users',
                    'href' => esc_url( get_admin_url() ).'users.php',
                    'parent' => 'appearance',
                )
            );
            */
        }
        ////////////END GAME ON MENU//////


        ///
        /// ADD ITEM MENU ///
        ///
        if ($is_admin ) {//only show to admin
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_add_content',
                    'title' => '<i class="fas fa-plus-circle ab-icon" aria-hidden="true"></i>',
                    'href' => ''
                )
            );

            // Add Quest
            /*$wp_admin_bar->add_node(
                array(
                    'id' => 'go_add_quest',
                    'title' => 'Add ' . get_option('options_go_tasks_name_singular') ,
                    'href' => '',
                    'parent' => 'go_add_content',
                    'meta' => array('class' => 'go_options')
                )
            );*/

            // Add Quest
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_add_quest_from_template',
                    'title' => 'Add ' . get_option('options_go_tasks_name_singular') ,
                    'href' => '',
                    'parent' => 'go_add_content',
                    'meta' => array('class' => 'go_options go_add_quest_from_template')
                )
            );

            // Add store Item
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_add_store_item',
                    //'title' => 'Add ' . get_option('options_go_store_name') ,
                    'title' => 'Add Store Item',
                    'href' => get_admin_url(null, 'post-new.php?post_type=go_store'),
                    'parent' => 'go_add_content',
                    'meta' => array('class' => 'go_options')
                )
            );

        }




        //VIEW TYPE ON QUESTS
        if (is_user_member_of_blog() || go_user_is_admin()) {
           // $wp_admin_bar->remove_menu('wp-logo');
            /**
             * If is admin, show the dropdown for view type
             */
            if ($is_admin) {
                $post_type = get_post_type();
                $admin_view = get_user_option('go_admin_view', $user_id);
                if (!empty ($admin_view)) {
                    if ($admin_view == 'all') {
                        $all_selected = 'selected = "selected"';
                    } else {
                        $all_selected = null;
                    }
                    if ($admin_view == 'player') {
                        $player_selected = 'selected = "selected"';
                    } else {
                        $player_selected = null;
                    }
                    if ($admin_view == 'user') {
                        $user_selected = 'selected = "selected"';
                    } else {
                        $user_selected = null;
                    }
                    if ($admin_view == 'guest') {
                        $guest_selected = 'selected = "selected"';
                    } else {
                        $guest_selected = null;
                    }

                } else {
                    $all_selected = null;
                    $player_selected = null;
                    $user_selected = null;
                    $guest_selected = null;
                }
                $content = '<form>
                            View: <select id="go_select_admin_view" onchange="go_update_admin_view(this.value)">
                                <option value="user" ' . $user_selected . '>Player Mode: Locks On</option>
                                <option value="player" ' . $player_selected . '>Admin Mode: No Locks</option>
                                <option value="all" ' . $all_selected . ' >All Stages</option>
                                <option value="guest" ' . $guest_selected . '>Guest</option>
                            </select>
                        </form>';
                if ($post_type == 'tasks' && !is_admin()) {
                    $wp_admin_bar->add_menu(array('id' => 'go_admin_view_form', 'parent' => 'top-secondary', 'title' => $content));
                }
            }
        }

        //READER AND CLIPBOARD FOR ADMINS ONLY
        if ($is_admin) {
            //$go_store_link = get_permalink(get_page_by_path($go_store_link));
            $reader_link = get_site_url(null, 'reader');
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_reader',
                    'title' => '<i class="fas fa-book-reader ab-icon" aria-hidden="true"></i><div id="go_reader_link" style="float: right;"></div>',
                    'href' => $reader_link,
                )
            );

            $wp_admin_bar->add_node(array('id' => 'go_clipboard', 'title' => '<i class="fas fa-clipboard-list ab-icon" aria-hidden="true"></i><div id="go_clipboard_adminbar" style="float: right;"></div>', 'href' => get_admin_url() . 'admin.php?page=go_clipboard',));
        }

        //MAP AND STORE LINKS IF YOU ARE ON THE BACKEND
        if (is_admin()){
            $go_map_switch = get_option( 'options_go_locations_map_toggle' );
            $go_store_switch = get_option( 'options_go_store_toggle' );
            if ($go_map_switch) {
                $map_url = get_option('options_go_locations_map_map_link');
                $go_map_link = (string)$map_url;
                //$go_map_link = get_permalink(get_page_by_path($go_map_link));
                $go_map_link = get_site_url(null, $go_map_link);
                $name = get_option('options_go_locations_map_name');
                $wp_admin_bar->add_node(
                    array(
                        'id' => 'go_map',
                        //'title' => '<i class="fas fa-sitemap ab-icon" aria-hidden="true"></i><div id="go_map_page" class="admin_map" style="float: right;" >' . $name . '</div>',
                        'title' => '<i class="fas fa-sitemap ab-icon" aria-hidden="true"></i><div id="go_map_page" class="admin_map" style="float: right;" ></div>',
                        'href' => $go_map_link,
                    )
                );
            };

            if ($go_store_switch) {
                $go_store_link = get_option('options_go_store_store_link');
                //$go_store_link = get_permalink(get_page_by_path($go_store_link));
                $go_store_link = get_site_url(null, $go_store_link);
                $name = get_option('options_go_store_name');
                $wp_admin_bar->add_node(
                    array(
                        'id' => 'go_store',
                        //'title' => '<i class="fas fa-store ab-icon" aria-hidden="true"></i><div id="go_store_page" style="float: right;">' . $name . '</div>',
                        'title' => '<i class="fas fa-exchange-alt ab-icon" aria-hidden="true"></i><div id="go_store_page" style="float: right;"></div>',
                        'href' => $go_store_link,
                    )
                );
            };


        }



    }

}

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

function go_admin_bar_remove_items() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu( 'wp-logo' );
    $wp_admin_bar->remove_menu('customize');
    $wp_admin_bar->remove_menu('new-content');
    $wp_admin_bar->remove_menu('site-name');
    if(!is_super_admin()) {
        $wp_admin_bar->remove_menu('updates');
        $wp_admin_bar->remove_menu( 'w3tc' );
    }
}
add_action( 'wp_before_admin_bar_render', 'go_admin_bar_remove_items', 0 );


add_action( 'admin_bar_menu', 'remove_howdy', 11 );
function remove_howdy( $wp_admin_bar ) {
    $user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    $profile_url = get_edit_profile_url( $user_id );

    if ( 0 != $user_id ) {
        /* Add the "My Account" menu */
        $avatar = get_avatar( $user_id, 28 );
        $howdy = sprintf( __('Welcome, %1$s'), $current_user->display_name );
        $class = empty( $avatar ) ? '' : 'with-avatar';

        $wp_admin_bar->add_menu( array(
            'id' => 'my-account',
            'parent' => 'top-secondary',
            'title' => $howdy . $avatar,
            'href' => $profile_url,
            'meta' => array(
                'class' => $class,
            ),
        ) );

    }
}
