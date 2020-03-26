<?php
/*
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
}*/

//remove dashboard
//add_action( 'admin_menu', 'Wps_remove_tools', 99 );
function Wps_remove_tools(){
	if ( ! get_option('go_dashboard_toggle') && ! current_user_can('administrator') ){
		remove_menu_page( 'index.php' ); //dashboard
	}
}

add_filter( 'show_admin_bar', 'go_display_admin_bar' );
function go_display_admin_bar() {

    global $is_really_admin;

    $is_admin = $is_really_admin;

    $is_gameful = is_gameful();
    //$blog_id = get_current_blog_id();
    $is_logged_in = is_user_logged_in();

    $show = false;
	if($is_admin || ($is_gameful && $is_logged_in)){
        if ($is_admin){
            $show =  true;
        }else if(is_main_site()){
            $show =  true;
        }
        else {
            $user_id = get_current_user_id();
            $blogs = count(get_blogs_of_user($user_id));
            if($blogs > 1){
                $show = true;
            }
        }
    }
	return $show;
}

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
    global $is_really_admin;
    global $go_user_guest_view;
    if($go_user_guest_view){
        $is_guest_view = true;
        $user_id = $go_user_guest_view->ID;
    }else{
        $is_guest_view = false;
        $user_id = get_current_user_id();
        //
    }

    $is_admin = go_user_is_admin() ;


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

            $blogs = get_blogs_of_user($user_id);
            foreach ($blogs as $blog) {
                $blog_id = $blog->userblog_id;
                if (is_gameful()) {
                    switch_to_blog($blog_id);
                    if (!current_user_can('delete_posts')) {
                        $wp_admin_bar->remove_node('blog-' . $blog_id . '-v');
                        $wp_admin_bar->remove_node('blog-' . $blog_id . '-d');
                    } else {
                        $sub_title = get_bloginfo('name');
                        $url = home_url();
                        $wp_admin_bar->remove_node('blog-' . $blog_id);

                        $wp_admin_bar->add_menu(array(
                            'parent' => 'my-sites-list',
                            'id' => 'blog-' . $blog_id,
                            'title' => $sub_title,
                            'href' => $url,
                        ));
                    }

                    restore_current_blog();
                }

            }


            $wp_admin_bar->remove_node('new-tasks');
            $wp_admin_bar->remove_node('new-go_store');
            $comments = $wp_admin_bar->get_node('comments');
            $wp_admin_bar->remove_node('comments');//add back later
            $edit = $wp_admin_bar->get_node('edit');
            $wp_admin_bar->remove_node('edit');//add back later

            $view = $wp_admin_bar->get_node('view');
            $wp_admin_bar->remove_node('view');//add back later

            if (!$is_admin) { // IF NOT AN ADMIN (Contributors)

                $wp_admin_bar->remove_node('archive');
                $wp_admin_bar->remove_node('wu-my-account');
                $wp_admin_bar->remove_node('my-account');
                $wp_admin_bar->remove_node('search');
                $logout_url = wp_logout_url(get_home_url());
                $wp_admin_bar->add_node(
                    array(
                        'id' => 'go_logout',
                        'title' => 'Log Out',
                        'href' => $logout_url,
                        'parent' => 'top-secondary',
                    )
                );
            }

            //remove the comments and new post from the "My Sites"
            if (is_gameful()) {
                $nodes = $wp_admin_bar->get_nodes();
                foreach ($nodes as $node) {
                    $node = $node->id;
                    if (strpos($node, 'blog') !== false) {
                        if (strpos($node, '-c') !== false) {
                            $wp_admin_bar->remove_node($node);
                        }
                        if (strpos($node, '-n') !== false) {
                            $wp_admin_bar->remove_node($node);
                        }
                    }
                }
            }

        if(!$is_guest_view) {
            ///
            /// add the Game On menu (options and shortcuts to admin pages) ///
            ///
            ///
            if ($is_admin) {//only show to admin
                // $wp_admin_bar->add_node(array('id' => 'go_section_pipe', 'title' => ' | ', 'href' => 'javascript:void(0)',));

                if (is_admin()) {
                    $url = home_url();
                    $icon = '<span class="ab-icon dashicons dashicons-admin-home"></span> ';
                    $sub_title = 'View Site';
                } else {
                    $url = get_admin_url();
                    $icon = '<span class="ab-icon dashicons dashicons-dashboard"></span> ';
                    $sub_title = 'Dashboard';
                }


                $site_name = get_bloginfo('name');
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


                $wp_admin_bar->add_node(
                    array(
                        'id' => 'go_nav_options',
                        'title' => 'Settings',
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

            if ($is_admin) {//only show to admin
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
                        'title' => 'Add ' . get_option('options_go_tasks_name_singular'),
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
        }
       // $wp_admin_bar->remove_menu('wp-logo');
        /**
         * If is admin, show the dropdown for view type
         */
        if ($is_really_admin) {
            if (!is_admin()) {
               // if (!is_admin()) {
                //$admin_view = get_user_option('go_admin_view', $user_id);
                $admin_view = go_get_admin_view($user_id);
                $admin_selected = null;
                $user_selected = null;
                $guest_selected = null;
                $all_selected = null;
                $post_type = get_post_type();
                if($is_guest_view){
                    $guest_selected = 'selected';
                }else {
                    if ($admin_view == 'admin') {
                        $admin_selected = 'selected';
                    } else if ($admin_view == 'user') {
                        $user_selected = 'selected';
                    } else if ($admin_view == 'guest') {
                        $guest_selected = 'selected';
                    } else if ($admin_view == 'all') {
                        $all_selected = 'selected';
                    }

                    if ($post_type !== 'tasks') {
                        $admin_selected = 'selected';
                    }
                }


                $content = '<form>
                        View: <select id="go_select_admin_view" onchange="go_update_admin_view(this.value)">
                            <option value="admin" ' . $admin_selected . '>Admin</option>';


                if ($post_type === 'tasks') {
                    $task_name = get_option('options_go_tasks_name_singular');
                    $content .= '<option value="all" ' . $all_selected . '>All ' . $task_name . ' Content</option>';
                }
                $content .= '           <option value="user" ' . $user_selected . '>Player</option>
                            <option value="guest" ' . $guest_selected . '>Guest</option>
                        </select>
                    </form>';

                //<option value="all" ' . $all_selected . ' >All Stages</option>

                $wp_admin_bar->add_menu(array('id' => 'go_admin_view_form', 'parent' => 'top-secondary', 'title' => $content));
               // }
            }
        }


        //READER AND CLIPBOARD FOR ADMINS ONLY
        if ($is_admin && !$is_guest_view) {
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
                /*
                $map_url = get_option('options_go_locations_map_map_link');
                $go_map_link = (string)$map_url;
                //$go_map_link = get_permalink(get_page_by_path($go_map_link));
                $go_map_link = get_site_url(null, $go_map_link);
                $name = get_option('options_go_locations_map_name');*/
                $go_map_link = go_get_link_from_option('options_go_locations_map_map_link');
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
                /*
                $go_store_link = get_option('options_go_store_store_link');
                //$go_store_link = get_permalink(get_page_by_path($go_store_link));
                $go_store_link = get_site_url(null, $go_store_link);
                $name = get_option('options_go_store_name');
*/
                $go_store_link = go_get_link_from_option('options_go_store_store_link');
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

        //Move edit, view, and comments
        if ($is_admin && !$is_guest_view) {
            //$go_store_link = get_permalink(get_page_by_path($go_store_link));

            $comments = (array)$comments;
            $wp_admin_bar->add_node($comments);
            $edit = (array)$edit;
            $wp_admin_bar->add_node($edit);
            $view = (array)$view;
            $wp_admin_bar->add_node($view);
        }

    }

}


add_action( 'wp_before_admin_bar_render', 'go_reorder_admin_bar');
function go_reorder_admin_bar(){
    global $wp_admin_bar;
    //$nodes = $wp_admin_bar->get_nodes();
    $elementor =  $wp_admin_bar->get_node('elementor_edit_page');
    if($elementor){
        $wp_admin_bar->remove_node('edit');
    }
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
        $user_display_name = go_get_user_display_name($user_id);
        $howdy = sprintf( __('Welcome, %1$s'), $user_display_name );
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
