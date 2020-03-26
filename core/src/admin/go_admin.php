<?php


/**
 * Auto update slugs
 * @author  Mick McMurray
 * Based on info from:
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
function go_update_slug( $data, $postarr ) {
    $slug_toggle = get_site_option( 'options_go_slugs_toggle');
    if ($slug_toggle) {
        $post_type = $data['post_type'];
        if ($post_type == 'tasks' || $post_type == 'go_store') {
            $data['post_name'] = wp_unique_post_slug(sanitize_title($data['post_title']), $postarr['ID'], $data['post_status'], $data['post_type'], $data['post_parent']);
        }
        return $data;
    }
}
add_filter( 'wp_insert_post_data', 'go_update_slug', 99, 2 );

// define the wp_update_term_data callback
/**
 * @param $data
 * @param $term_id
 * @param $taxonomy
 * @param $args
 * @return mixed
 */
function go_update_term_slug($data, $term_id, $taxonomy, $args ) {
    $slug_toggle = get_site_option( 'options_go_slugs_toggle');
    if ($slug_toggle) {
        $no_space_slug = sanitize_title($data['name']);
        $data['slug'] = wp_unique_term_slug($no_space_slug, (object)$args);
        return $data;
    }
};
add_filter( 'wp_update_term_data', 'go_update_term_slug', 10, 4 );

/**
 *
 */
function hide_all_slugs() {
    $slug_toggle = get_site_option( 'options_go_slugs_toggle');
    if ($slug_toggle) {
        global $post;
        $post_type = get_post_type( get_the_ID() );
        if ($post_type != 'post' && $post_type != 'page') {
            $hide_slugs = "<style type=\"text/css\"> #slugdiv, #edit-slug-box, .term-slug-wrap { display: none; }</style>";
            print($hide_slugs);
        }

    }
}
add_action( 'admin_head', 'hide_all_slugs'  );


/*
 * Function for post duplication. Dups appear as drafts. User is redirected to the edit screen
 * https://www.hostinger.com/tutorials/how-to-duplicate-wordpress-page-post#gref
 */
add_action( 'admin_action_go_duplicate_post_as_draft', 'go_duplicate_post_as_draft' );
function go_duplicate_post_as_draft(){

    if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'go_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
        wp_die('No post to duplicate has been supplied!');
    }

    /*
     * Nonce verification
     */
    if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) )
        return;

    go_clone_post_new(false);
}

//this is the function called from the task list edit table
function go_new_task_from_template_as_draft()
{
    if (!(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action']) && 'go_new_task_from_template_as_draft' == $_REQUEST['action']))) {
       // wp_die('No post to duplicate has been supplied!');
    }

    /*
     * Nonce verification
     */
    if (!isset($_GET['template_nonce']) || !wp_verify_nonce($_GET['template_nonce'], basename(__FILE__))) return;

    go_clone_post_new(true);
}


/*
 * Add the duplicate link to action list for post_row_actions
 */
function go_duplicate_post_link( $actions, $post ) {
    if (current_user_can('edit_posts') || go_user_is_admin()) {
        $task_name = get_option('options_go_tasks_name_singular');
        $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=go_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Clone</a>';
        if ($post->post_type == 'tasks_templates') {
            $actions['new_from_template'] = '<a href="' . wp_nonce_url('admin.php?action=go_new_task_from_template_as_draft&post=' . $post->ID, basename(__FILE__), 'template_nonce' ) . '" title="Duplicate this item" rel="permalink">New '.$task_name.' From Template</a>';

        }
    }
    return $actions;
}
add_filter( 'post_row_actions', 'go_duplicate_post_link', 10, 2 );

function go_duplicate_post_button($post ) {
    if (current_user_can('edit_posts') || go_user_is_admin()) {
        $task_name = get_option('options_go_tasks_name_singular');
        echo '<div style="padding: 10px;"><a class="button" href="' . wp_nonce_url('admin.php?action=go_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Clone</a></div>';
        if ($post->post_type == 'tasks_templates'){
            echo '<div style="padding: 10px;"><a class="button" href="' . wp_nonce_url('admin.php?action=go_new_task_from_template_as_draft&post=' . $post->ID, basename(__FILE__), 'template_nonce' ) . '" title="Duplicate this item" rel="permalink">New '.$task_name.' From Template</a></div>';
        }
    }
}
add_action( 'post_submitbox_misc_actions', 'go_duplicate_post_button' );

/**
 * re-order left admin menu
 */

function go_reorder_admin_menu( ) {
    global $submenu;

    $game_options_menu_items = (isset($submenu['game-on-options']) ?  $submenu['game-on-options'] : false);

    if($game_options_menu_items) {
        $game_options_menu_items = array_values($game_options_menu_items);
        $i = 0;
        foreach ($game_options_menu_items as $menu_item) {
            if ($menu_item[2] === 'options-discussion.php') {

                unset($game_options_menu_items[$i]);
                $game_options_menu_items[] = $menu_item;
                $submenu['game-on-options'] = $game_options_menu_items;
                break;
            }
            $i++;
        }
    }

    return array(
        'index.php', // Dashboard//
        //'game-on', //GO heading
        'game-on-options', //GO options
        'options-general.php', // Settings
        'go_clipboard', //GO clipboard
        'users.php', // Users
        'edit.php?post_type=tasks', // Quests
        //'edit-tags.php?taxonomy=task_chains', //Maps
        'edit.php?post_type=go_store', //store
        'edit-tags.php?taxonomy=go_badges', //badges
        'edit-comments.php', // Comments
        //'groups',
        //'edit.php?post_type=go_blogs',
        //'go_random_events',
        //'game-tools',//gameon tools
        'separator1', // --Space--

        'edit.php?post_type=page', // Pages
        'edit.php', // Posts
        'upload.php', // Media
        //'themes.php', // Appearance
        'customize.php', // Customize
        'nav-menus.php', // Menus


        'separator2', // --Space--


        //'users.php', // Users
        //'separator3', // --Space--

        'plugins.php', // Plugins
        'tools.php', // Tools

    );
}
add_filter( 'custom_menu_order', 'go_reorder_admin_menu' );
add_filter( 'menu_order', 'go_reorder_admin_menu' );

/**
 * Add new top level menus
 */
function go_add_toplevel_menu() {
    /**
     * Add GO Options Page using ACF
     */
// add sub page



    /* add a new menu item */
    /*
    add_menu_page(
        'Gameful Me Help', // page title
        'Gameful Me Help', // menu title
        'manage_options', // capability
        'game-on', // menu slug
        'go_admin_game_on_menu_content', // callback function
        'dashicons-admin-home',// icon
        1
    );*/

    /* add a new menu item */
    add_menu_page(
        'Settings', // page title
        'Settings', // menu title
        'manage_options', // capability
        'game-on-options', // menu slug
        'go_options_menu_content', // callback function
        'dashicons-admin-home', // icon
        2
    );

    /* add the sub menu under content for posts */
    add_submenu_page(
        'game-on-options', // parent slug
        'General Settings', // page_title,
        'General Settings', // menu_title,
        'manage_options', // capability,
        'options-general.php' // menu_slug,
    );

    /* add the sub menu under content for posts */
    add_submenu_page(
        'game-on-options', // parent slug
        'Comments', // page_title,
        'Comments', // menu_title,
        'manage_options', // capability,
        'options-discussion.php' // menu_slug,
    );

    if( function_exists('acf_add_options_page') ) {
        acf_add_options_page(array('page_title' => 'Game Setup', 'menu_slug' => 'go_options', 'autoload' => true, 'capability' => 'edit_posts', 'icon_url' => 'dashicons-admin-settings',
            'parent_slug' 	=> 'game-on-options',
        ));

        acf_add_options_page(array('page_title' => 'Login and Registration', 'menu_slug' => 'go_login_options', 'autoload' => true, 'capability' => 'edit_posts', 'icon_url' => 'dashicons-admin-settings',
            'parent_slug' 	=> 'game-on-options',
        ));

        acf_add_options_page(array('page_title' => 'Attendance', 'menu_slug' => 'attendance', 'autoload' => true, 'capability' => 'edit_posts', 'icon_url' => 'dashicons-admin-settings',
            'parent_slug' 	=> 'game-on-options',
        ));

        acf_add_options_page(array('page_title' => 'Canned Feedback', 'menu_slug' => 'go_feedback', 'autoload' => true, 'capability' => 'edit_posts', 'icon_url' => 'dashicons-admin-settings',
            'parent_slug' 	=> 'game-on-options',
        ));

        acf_add_options_page(array('page_title' => 'Canned Messages', 'menu_slug' => 'go_messages', 'autoload' => true, 'capability' => 'edit_posts', 'icon_url' => 'dashicons-admin-settings',
            'parent_slug' 	=> 'game-on-options',
        ));

        acf_add_options_page(array('page_title' => 'Bonus Loot Default', 'menu_slug' => 'go_bonus_loot', 'autoload' => true, 'capability' => 'edit_posts', 'icon_url' => 'dashicons-admin-settings',
            'parent_slug' 	=> 'game-on-options',
        ));

        /*acf_add_options_page(array('page_title' => 'Grade Predictor (Beta)', 'menu_slug' => 'grade_predictor', 'autoload' => true, 'capability' => 'edit_posts', 'icon_url' => 'dashicons-admin-settings',
            'parent_slug' 	=> 'game-on-options',
        ));*/


       /* acf_add_options_page(array('page_title' => 'Appearance', 'menu_slug' => 'go_appearance', 'autoload' => true, 'capability' => 'edit_posts', 'icon_url' => 'dashicons-admin-settings',
            'parent_slug' 	=> 'game-on-options',
        ));*/

        if( (is_gameful() && is_main_site()) || !is_gameful()  ) {


            acf_add_options_page(array('page_title' => 'Performance', 'menu_slug' => 'go_performance', 'autoload' => true, 'capability' => 'edit_posts', 'icon_url' => 'dashicons-admin-settings',
                'parent_slug' 	=> 'game-on-options',
            ));

        }

    }
/*
    add_submenu_page(
        'game-on-options', // parent slug
        'General Site Settings', // page_title,
        'General Site Settings', // menu_title,
        'manage_options', // capability,
        'options-general.php' // menu_slug,
    );
*/

    /* add a new menu item */
    add_menu_page(
        'Clipboard', // page title
        'Clipboard', // menu title
        'manage_options', // capability
        'go_clipboard', // menu slug
        'go_clipboard_menu', // callback function
        'dashicons-clipboard', // icon
        4
    );



    /* add a new menu item */
    /*
    $map_name = get_option('options_go_locations_map_title');
    add_menu_page(
        ucwords($map_name), // page title
        ucwords($map_name), // menu title
        'edit_posts', // capability
        'edit-tags.php?taxonomy=task_chains', // menu slug
        '', // callback function
        'dashicons-location-alt', // icon
        4 // menu position
    );
*/
    $badges_toggle = get_option('options_go_badges_toggle');
    if($badges_toggle) {
        /* add a new menu item */
        $badges_name = get_option('options_go_badges_name_plural');
        add_menu_page($badges_name, // page title
            $badges_name, // menu title
            'edit_posts', // capability
            'edit-tags.php?taxonomy=go_badges' // menu slug
        //'', // callback function
        //'', // icon
        // 4 // menu position
        );
    }


   /* if($groups_toggle) {

        $groups_name = get_option('options_go_groups_name_plural') . ' & Sections';
        // add a new menu item
        add_menu_page(
            $groups_name, // page title
            $groups_name, // Menu title
            'edit_posts', // capability
            'groups', // menu slug
            '', // callback function
            '', // icon
            4 // menu position
        );

    }*/
    //add a new menu item
    add_menu_page(
        'Tools',// page title
        'Tools',// page title
        'manage_options',// capability
        'game-tools',// menu slug
        'go_admin_tools_menu_content',// callback function
        '',// icon
        4 // menu position
    );

    //add a new menu item
    add_menu_page(
        'Tools',// page title
        'Tools',// page title
        'manage_options',// capability
        'game-tools',// menu slug
        'go_admin_tools_menu_content',// callback function
        '',// icon
        4 // menu position
    );

    //add a new menu item
    add_menu_page(
        'Customize',// page title
        'Customize',// page title
        'manage_options',// capability
        'customize.php',// menu slug
        '',// callback function
        'dashicons-admin-appearance',// icon
        2 // menu position
    );

    //add a new menu item
    add_menu_page(
        'Menus',// page title
        'Menus',// page title
        'manage_options',// capability
        'nav-menus.php',// menu slug
        '',// callback function
        'dashicons-welcome-widgets-menus',// icon
        2 // menu position
    );

    //nav-menus.php
    //remove_submenu_page( 'themes.php', 'widgets.php' );
   // remove_submenu_page( 'themes.php', 'themes.php' );

    //remove menu items for non admins
    if ( !go_user_is_admin() ) { // IF NOT AN ADMIN
        global  $menu;
        foreach ($menu as $this_menu){

            $slug = $this_menu[2];
            $menus_to_keep = array('upload.php', 'edit.php', 'edit.php?post_type=tasks', );
            if(!in_array($slug, $menus_to_keep) ) {
                remove_menu_page($slug);
            }
        }
        global  $submenu;
        remove_submenu_page( 'edit.php?post_type=tasks', 'edit.php?post_type=tasks_templates' );
        remove_submenu_page( 'edit.php?post_type=tasks', 'edit-tags.php?taxonomy=task_chains&post_type=tasks' );
    }

    global $submenu;

    $game_options_menu_items = (isset($submenu['game-on-options']) ?  $submenu['game-on-options'] : false);

    if($game_options_menu_items) {
        $game_options_menu_items = array_values($game_options_menu_items);
        $i = 0;
        foreach ($game_options_menu_items as $menu_item) {
            if ($menu_item[0] === 'Settings') {
                $menu_item[0] = 'All Settings';
                $menu_item[3] = 'All Settings';
                $submenu['game-on-options'][$i] = $menu_item;
                break;
            }
            $i++;
        }
    }
}
add_action( 'admin_menu', 'go_add_toplevel_menu');

function go_remove_toplevel_menu() {
    //remove for all non super admin
    if(is_gameful() && !is_super_admin()) {
        remove_menu_page('edit.php?post_type=elementor_library');
        remove_menu_page('elementor');
        remove_menu_page('w3tc_dashboard');

        global $submenu;
        // Still need to update cap requirements even when hidden
        if(isset($submenu['w3tc_dashboard'])) foreach( $submenu['w3tc_dashboard'] as $position => $data ) {
            $submenu['w3tc_dashboard'][$position][1] = 'manage_network';
        }
    }

    if(is_gameful()) {
        remove_menu_page('themes.php');
        remove_menu_page('tools.php');
        remove_menu_page('options-general.php');
        //remove_menu_page('options-general.php');

        remove_submenu_page('options-general.php', 'options-general.php');
        remove_submenu_page('options-general.php', 'options-discussion.php');
        remove_submenu_page('options-general.php', 'manage_privacy_options');
        remove_submenu_page('options-general.php', 'options-writing.php');
        remove_submenu_page('options-general.php', 'options-reading.php');
        remove_submenu_page('options-general.php', 'options-media.php');
        remove_submenu_page('options-general.php', 'options-permalink.php');

        remove_submenu_page('themes.php', 'widgets.php');
        remove_submenu_page('themes.php', 'themes.php');
    }

}
add_action( 'admin_init', 'go_remove_toplevel_menu');

function go_add_submenus() {
    remove_submenu_page( 'users.php', 'profile.php' );
    /* add the sub menu under content for posts */
    $task_name = get_option('options_go_tasks_name_singular');


    /*
    add_submenu_page(
        'edit.php?post_type=tasks', // parent slug
        'New ' . $task_name, // page_title,
        'New ' . $task_name, // menu_title,
        'edit_posts', // capability,
        'javascript:go_new_task_from_template();' // menu_slug,
    );

    remove_submenu_page( 'edit.php?post_type=tasks', 'post-new.php?post_type=tasks' );*/


    /* add the sub menu under content for posts */
    add_submenu_page(
        'edit.php?post_type=tasks', // parent slug
        'Templates', // page_title,
        'Templates', // menu_title,
        'edit_posts', // capability,
        'edit.php?post_type=tasks_templates' // menu_slug,
    );

    //add the sub menu under content for maps
    $map_name = get_option('options_go_locations_map_title');
    add_submenu_page(
        'edit.php?post_type=tasks', // parent slug
        'Manage ' . $map_name, // page_title,
        'Manage ' . $map_name, // menu_title,
        'edit_posts', // capability,
        'edit-tags.php?taxonomy=task_chains&post_type=tasks' // menu_slug,
    );

    /*
    // add the sub menu under content for maps
    $store_name = get_option('options_go_store_store_link');
    add_submenu_page(
        'edit.php?post_type=go_store', // parent slug
        'Manage ' . ucwords($store_name)." Categories", // page_title,
        ucwords($store_name)." Categories", // menu_title,
        'edit_posts', // capability,
        'edit-tags.php?taxonomy=store_types&post_type=go_store' // menu_slug,
    );*/

    /*
    // add the sub menu under content for posts
    add_submenu_page(
        'maps_menus', // parent slug
        'Maps & Menus', // page_title,
        'Maps & Menus', // menu_title,
        'edit_posts', // capability,
        'maps_menus' // menu_slug,
    );*/

    // add the sub menu under content for posts */
    /*
    $badges_name = get_option('options_go_badges_name_singular');
    add_submenu_page(
        'badges', // parent slug
        'Manage ' . $badges_name, // page_title,
        'Manage ' . $badges_name, // menu_title,
        'edit_posts', // capability,
        'edit-tags.php?taxonomy=go_badges' // menu_slug,
    );*/

    /* add the sub menu under content for posts */
    $groups_toggle = get_option('options_go_groups_toggle');
    if($groups_toggle) {
        $groups_name = get_option('options_go_groups_name_plural');

        add_submenu_page(
            'users.php', // parent slug
            'Manage '. $groups_name, // page_title,
            'Manage '. $groups_name, // menu_title,
            'edit_posts', // capability,
            'edit-tags.php?taxonomy=user_go_groups' // menu_slug,
        );
    }

    // add the sub menu under content for posts
    add_submenu_page(
        'users.php', // parent slug
        'Manage Sections', // page_title,
        'Manage Sections', // menu_title,
        'edit_posts', // capability,
        'edit-tags.php?taxonomy=user_go_sections' // menu_slug,
    );

    // add the sub menu under content for posts
    /*
    add_submenu_page(
        'edit-tags.php?taxonomy=task_chains', // parent slug
        'Quest Maps', // page_title,
        'Quest Maps', // menu_title,
        'edit_posts', // capability,
        'edit-tags.php?taxonomy=task_chains' // menu_slug,
    );*/

    add_submenu_page(
        'game-tools', // parent slug
        'Available Tools', // page_title,
        'Available Tools', // menu_title,
        'manage_options', // capability,
        'game-tools', // menu_slug,
        'go_admin_tools_menu_content'// callback function
    );



    add_submenu_page(
        'game-tools', // parent slug
        'Delete Site', // page_title,
        'Delete Site', // menu_title,
        'manage_options', // capability,
        'ms-delete-site.php' // menu_slug,
    );




}
add_action( 'admin_menu', 'go_add_submenus', 9 );


add_filter( 'gettext', 'go_comment_settings_title', 20, 3 );
/**
 * Change comment form default field names.
 *
 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/gettext
 */
function go_comment_settings_title( $translated_text, $text, $domain ) {
    if($text === 'Discussion Settings'){
        $translated_text = 'Comments Settings';
    }

    return $translated_text;
}

//remove add new button on tasks edit page becuase it has custom button
//and remove submenu becuase it was replaced with a pop up to select templage
add_action('admin_menu', 'go_disable_new_tasks');
function go_disable_new_tasks() {
// Hide sidebar link
    //global $submenu;
    //unset($submenu['edit.php?post_type=tasks'][10]);

// Hide link on listing page
    if (isset($_GET['post_type']) && $_GET['post_type'] == 'tasks') {
        echo '<style type="text/css">
    .page-title-action { display:none; }
    </style>';
    }
}

/**
 * Add content to submenus
 * Callbacks
 */

function go_admin_game_on_menu_content() {

    ?>


    <div class="wrap">

        <h1></a>Game-On</h1>



        <p>Game-On (GO) is an educational framework that provides teachers with a vast amount of tools to create their own <a href="http://en.wikipedia.org/wiki/Gamification" rel="nofollow">gamified</a> learning system.</p>

        <h3>Information and Help</h3>
        <ul style="list-style-position: outside; list-style-type: circle; margin-left: 30px;">
            <li><a href="http://maclab.guhsd.net/game-on" rel="nofollow">Game-On Documentation</a>: This is still v3 documentation.  v4 documentation is in the works.</li>
            <li><a href='https://www.youtube.com/channel/UC1G3josozpubdzaINcFjk0g' >YouTube</a> Visit our YouTube Channel for the most recent updates.</li>
            <li><a href="http://edex.adobe.com/group/game-on/discussions/" rel="nofollow">Adobe Education Exchange (AEE)</a> Game-On Group Forum</li>
            <ul>
                <li>The Game  <a href="https://edex.adobe.com/group/game-on/discussion/-9038000/" rel="nofollow">Questions and Observations</a> thread.</li>
                <li>If you found a bug or are having any difficulties in v3.X versions, refer to the <a href="https://edex.adobe.com/group/game-on/discussion/v9f80aa7d/" rel="nofollow">Game On v3.x Discussion</a> thread.</li>
                <li>Currently, AEE does not support thread subscription without commenting. If you'd like to recieve updates in any of the AEE threads, be sure to leave a comment. Something as simple as "Hi, following along." will do!</li>
            </ul>
            <li><a href="http://edex.adobe.com/group/game-on/discussions/" rel="nofollow">Gameful.me Forum</a></li>
            <ul>
                <li>For v4 information, bug reporting, and feature requests, please refer to the <a href="https://gameful.me/forums" rel="nofollow">forum on Gameful.me</a>.</li>
            </ul>
        </ul>
        <h3>Installation Requirements</h3>
        <p>Make sure to talk to your web hosting service provider about these technical requirements, if you have any doubts.</p>
        <h4> PHP</h4>
        <p>Make sure that your hosting service supports and maintains a PHP version of <strong>at least</strong> <code>5.3</code>. Ideally, every service would have updated their PHP versions to <code>7.1</code>, but that isn't a realistic assumption. If the most recent version is not an option, version <code>5.6</code> should do the trick.</p>
        <p>In order of best scenario: <code>7.1</code> is better than <code>5.6</code>, which is better than <code>5.3</code>.</p>
        <p>If your service does not provide a version of PHP greater than <code>5.3</code>, please be aware that there are potential compatibility issues due to the nature of the outdated software.</p>
        <h4>WordPress</h4>
        <p>We highly recommend keeping your WordPress installation up to date. This not only ensures that you receive all official <a href="https://wordpress.org/" rel="nofollow">WordPress.org</a> security updates and hotfixes, but you'll also receive the best experience when using GO.</p>
        <hr>
        <h3>Lovingly Created By</h3>
        <p>Current Authors:</p>
        <ul>
            <li>Mick McMurray</li>
        </ul>
        <p>Previous Authors/Contributors:</p>
        <ul>
            <li><a href="http://foresthoffman.com" rel="nofollow">Forest Hoffman</a></li>
            <li>Zach Hofmeister</li>
            <li>Ezio Ballarin</li>
            <li>Charles Leon</li>
            <li>Austin Vuong</li>
            <li>Vincent Astolfi</li>
            <li>Semar Yousif</li>
        </ul>
        <hr>
        <h3>For Contributors</h3>
        <p>Everything you need should be in the <a href="https://github.com/TheMacLab/game-on/wiki/">wiki</a>.</p>
        <h3>License</h3>
        <p>License:           GPLv2 or later
            License URI:       <a href="http://www.gnu.org/licenses/gpl-2.0.html" rel="nofollow">http://www.gnu.org/licenses/gpl-2.0.html</a></p>


    </div>

    <?php

}

/**
 * @param $parent_file
 * @return string
 * Fix Hierarchy on menus when items are clicked
 * show the correct sub menu
 */
function go_menu_hierarchy_correction( $parent_file ) {

    //global $parent_file;
    global $current_screen;

    /* get the base of the current screen */
    $screenbase = $current_screen->base;
    $taxonomy = $current_screen->taxonomy;

    if($screenbase ==='options-general' && is_gameful()){
        $parent_file = 'game-on-options';
    }
    else if($screenbase ==='options-discussion' && is_gameful()){
        $parent_file = 'game-on-options';
    }
    /*else if ($taxonomy == 'task_chains' || $taxonomy == 'task_menus'   || $taxonomy == 'task_categories'  ){
        //if this is the edit.php base
        if( $screenbase == 'term' ) {
            // set the parent file slug to the custom content page
            $parent_file = 'maps_menus';

        }
        else if( $screenbase == 'edit-tags' ) {
            // set the parent file slug to the custom content page
            $parent_file = 'tasks';
        }
    }*/

    else if ($taxonomy == 'go_badges'){
        if( $screenbase == 'term' ) {
            /* set the parent file slug to the custom content page */
            $parent_file = 'edit-tags.php?taxonomy=go_badges';

        }
        else if( $screenbase == 'edit-tags' ) {
            /* set the parent file slug to the custom content page */
            $parent_file = 'edit-tags.php?taxonomy=go_badges';
        }
    }
    else if ($taxonomy == 'user_go_groups'){
        if( $screenbase == 'term' ) {
            /* set the parent file slug to the custom content page */
            $parent_file = 'users.php';

        }
        else if( $screenbase == 'edit-tags' ) {
            /* set the parent file slug to the custom content page */
            $parent_file = 'users.php';
        }
    }
    else if ($taxonomy == 'user_go_sections'){
        if( $screenbase == 'term' ) {
            /* set the parent file slug to the custom content page */
            $parent_file = 'users.php';

        }
        else if( $screenbase == 'edit-tags' ) {
            /* set the parent file slug to the custom content page */
            $parent_file = 'users.php';
        }
    }
    /* return the new parent file */
    return $parent_file;

}
add_action( 'parent_file', 'go_menu_hierarchy_correction', 999 );


/**
 * Return to taxonomy page after updating a term
 * Work for any post type and all custom/built_in taxonomies
 */

add_filter( 'wp_redirect',
    function( $location ){
        $mytaxonomy = (isset($_POST['taxonomy']) ?  $_POST['taxonomy'] : null);
        $orig_referer = (isset($_POST['_wp_http_referer']) ?  $_POST['_wp_http_referer'] : false);
        parse_str($orig_referer, $output);
        //$post_type = $output['post_type'];
        $post_type = (isset($output['post_type']) ?  $output['post_type'] : null);
        if(empty($post_type)) {
            $referer = (isset($_POST['_wp_http_referer']) ? $_POST['_wp_http_referer'] : false);
            parse_str($referer, $output);
            $post_type = (isset($output['post_type']) ?  $output['post_type'] : null);
        }
        if( !empty($mytaxonomy) && !empty($post_type) ){

            //$location = add_query_arg( 'action',   'edit',               $location );
            $location = '?taxonomy=' . $mytaxonomy . '&post_type=' . $post_type;
            //$location = add_query_arg( 'tag_ID',   $_inputs['tag_ID'],   $location );
            return $location;
        }


        return $location;
    }
);


// define the after-<taxonomy>-table callback
function action_after_taxonomy_table( $taxonomy ) {
    // make action magic happen here...
    ?>
    <script>
        jQuery( document ).ready(function() {
            console.log("move it");
            jQuery('.metabox-prefs').hide();
            jQuery('#posts-filter').after("<div id='go_screen_options_container'><div id='go_screen_options' style='float: right; background-color: white;padding: 20px;'></div></div>");
            jQuery('#adv-settings').appendTo('#go_screen_options');
            jQuery('#screen-options-link-wrap').hide();
            jQuery('legend').hide();


        });

    </script>
    <?php
};

$mytaxonomy = (isset($_GET['taxonomy']) ?  $_GET['taxonomy'] : null);
if ($mytaxonomy) {
    $taxonomy = $mytaxonomy;
// add the action
    add_action("after-{$taxonomy}-table", 'action_after_taxonomy_table', 10, 1);

// run the action
    do_action('after-{$taxonomy}-table', $taxonomy);
}


function go_options_menu_content() {

    $task_name = get_option('options_go_tasks_name_singular');
    ?>

    <div id="go_tools_wrapper" class="wrap">
        <h2>Settings</h2>
        <div class="go_tools_section">

            <div class="go_tools_section">
                <a href="<?php echo get_admin_url( null, 'options-general.php', 'admin' );  ?>" style="text-decoration: none;"><div class="card">
                        <h2>General Settings</h2>
                        <ul>
                            <li>Set site name</li>
                            <li>Timezone</li>
                            <li>Date and Time Settings</li>
                            <li>Set Site Admin Email</li>
                        </ul>
                    </div></a>
            </div>


            <div class="go_tools_section">
                <a href="<?php menu_page_url('go_options'); ?>" style="text-decoration: none;"><div class="card">
                    <h2>Game Setup</h2>
                        <ul>
                            <li>Enable game features (loot, maps, store, badges, and groups)</li>
                            <li>Set Custom Names for Loot and features</li>
                            <li>Set-up levels</li>
                            <li>User Options</li>
                        </ul>
                </div></a>
            </div>

            <div class="go_tools_section">
                <a href="<?php menu_page_url('go_login_options'); ?>" style="text-decoration: none;"><div class="card">
                    <h2>Login and Registration</h2>
                        <ul>
                            <li>Enable limits on registration</li>
                            <li>Set membership code</li>
                            <li>Choose what user information to collect on registration and profile pages</li>
                        </ul>
                </div></a>
            </div>
            <div class="go_tools_section">
                <a href="<?php menu_page_url('attendance'); ?>" style="text-decoration: none;"><div class="card">
                        <h2>Attendance</h2>
                        <ul>
                            <li>Create a schedule for marking students on time or late.</li>
                            <li>Assign awards and consequences.</li>
                        </ul>
                    </div></a>
            </div>
            <div class="go_tools_section">
                <a href="<?php menu_page_url('go_feedback'); ?>" style="text-decoration: none;"><div class="card">
                    <h2>Canned Feedback</h2>
                    <p>Find yourself leaving the same feedback over and over again? Create a preset and save yourself time.</p>
                </div></a>
            </div>
            <div class="go_tools_section">
                <a href="<?php menu_page_url('go_messages'); ?>" style="text-decoration: none;"><div class="card">
                    <h2>Canned Messages</h2>
                        <ul>
                            <li>Messages can be used to reward or provide consequences for behavior. </li>
                            <li>Set or modify the presets here for common behaviors.</li>
                        </ul>
                </div></a>
            </div>
            <div class="go_tools_section">
                <a href="<?php menu_page_url('go_bonus_loot'); ?>" style="text-decoration: none;"><div class="card">
                    <h2>Bonus Loot Defaults</h2>
                        <ul>
                            <li>Students have a chance to win bonus loot upon completion of a <?php echo $task_name; ?>. </li>
                            <li>Create a default set of bonus loot that you can apply to any <?php echo $task_name; ?>.  </li>
                        </ul>
                </div></a>
            </div>

            <div class="go_tools_section">
                <a href="<?php echo get_admin_url( null, 'options-discussion.php', 'admin' );  ?>" style="text-decoration: none;"><div class="card">
                        <h2>Comments</h2>
                        <ul>
                            <li>Moderation Options</li>
                            <li>Spam Options</li>
                            <li>Word Blacklist</li>
                        </ul>
                    </div></a>
            </div>

            <?php
            /*
             * <div class="go_tools_section">
                <a href="<?php menu_page_url('go_appearance'); ?>" style="text-decoration: none;"><div class="card">
                    <h2>Appearance</h2>
                    <p>Adjust the appearance of game on menus and pages.</p>
                </div></a>
            </div>
             */
            if (!is_gameful()) {
                ?>
                <div class="go_tools_section">
                    <a href="<?php menu_page_url('go_performance'); ?>" style="text-decoration: none;"><div class="card">
                        <h2>Performance</h2>
                        <p>Rewrite slugs, and image resizing.</p>
                    </div></a>
                </div>
                <?php
            }
            ?>

        </div>
        <?php
        if (is_gameful() && is_main_site()) {
            ?>
            <h3>Site-Wide Settings</h3>
            <div class="go_tools_section">

                <a href="<?php menu_page_url('go_performance'); ?>"> <div class="go_tools_section">
                    <div class="card">
                        <h2>Performance</h2>
                        <p>Rewrite slugs, and image resizing.</p>
                    </div>
                </a>
                </div>
            </div>
            <?php
        }
        ?>
    </div>



    <?php

}


/*
if ( ! function_exists( 'cor_remove_personal_options' ) ) {

     //Removes the leftover 'Visual Editor', 'Keyboard Shortcuts' and 'Toolbar' options.

    function cor_remove_personal_options( $subject ) {
        //$subject = preg_replace( '#<h2>Personal Options</h2>.+?table>#s', '', $subject, 1 );
        //$subject = preg_replace( '#<h2>About the user</h2>.+?h2>#s', '<h2>', $subject, 1 );
        //$subject = preg_replace( '#<h2>About Yourself</h2>.+?h2>#s', '<h2>', $subject, 1 );
        //$subject = preg_replace( '#<tr class="user-display-name-wrap">.+?tr>#s', '', $subject, 1 );
        //$subject = preg_replace( '#<tr class="user-url-wrap">.+?tr>#s', '', $subject, 1 );
        //$subject = preg_replace( '#<tr class="user-nickname-wrap">.+?tr>#s', '', $subject, 1 );

        return $subject;
    }

    function cor_profile_subject_start() {
        ob_start( 'cor_remove_personal_options' );
    }

    function cor_profile_subject_end() {
        ob_end_flush();
    }
}*/
/*
add_action( 'admin_head-user-edit.php', 'cor_profile_subject_start' );
add_action( 'admin_footer-user-edit.php', 'cor_profile_subject_end' );
add_action( 'admin_head-profile.php', 'cor_profile_subject_start' );
add_action( 'admin_footer-profile.php', 'cor_profile_subject_end' );*/



function go_update_network_options( $value, $post_id, $field  ) {

    //save as network option in multisite
    if(is_gameful()) {
        $option = "options_".$field['name'];
        update_site_option($option, $value);
    }

    // do something else to the $post object via the $post_id

    // return
    return $value;

}
add_filter('acf/update_value/key=field_5cda44a3285da', 'go_update_network_options', 10, 3);
add_filter('acf/update_value/key=field_5d16871d14236', 'go_update_network_options', 10, 3);
add_filter('acf/update_value/key=field_5abc9707c66ea', 'go_update_network_options', 10, 3);
add_filter('acf/update_value/key=field_5d943730bc96f', 'go_update_network_options', 10, 3);
add_filter('acf/update_value/key=field_5d9ac4f0599f0', 'go_update_network_options', 10, 3);


//From: https://revelationconcept.com/wordpress-rename-default-posts-news-something-else/
function go_change_post_label() {
    global $menu;
    global $submenu;
    $menu[5][0] = 'Announcements';
    $submenu['edit.php'][5][0] = 'Announcements';
    $submenu['edit.php'][10][0] = 'Add Announcement';
    $submenu['edit.php'][16][0] = 'Announcement Tags';
}
function go_change_post_object() {
    global $wp_post_types;
    $labels = &$wp_post_types['post']->labels;
    $labels->name = 'Announcements';
    $labels->singular_name = 'Announcements';
    $labels->add_new = 'Add Announcement';
    $labels->add_new_item = 'Add Announcement';
    $labels->edit_item = 'Edit Announcement';
    $labels->new_item = 'Announcements';
    $labels->view_item = 'View Announcements';
    $labels->search_items = 'Search Announcements';
    $labels->not_found = 'No Announcements found';
    $labels->not_found_in_trash = 'No Announcements found in Trash';
    $labels->all_items = 'All Announcements';
    $labels->menu_name = 'Announcements';
    $labels->name_admin_bar = 'Announcements';
}

add_action( 'admin_menu', 'go_change_post_label' );
add_action( 'init', 'go_change_post_object' );



/*  Copyright 2016  Apasionados.es  (email: info@apasionados.es)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$plugin_header_translate = array( __('Show modified Date in admin lists', 'show_modified_date_in_admin_lists'), __('Shows a new, sortable, column with the modified date in the lists of pages and posts in the WordPress admin panel. It also shows the username that did the last update.', 'show_modified_date_in_admin_lists') );

add_action( 'admin_init', 'show_modified_date_in_admin_lists_language' );
function show_modified_date_in_admin_lists_language() {
    load_plugin_textdomain( 'show_modified_date_in_admin_lists', false,  dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

// Register Modified Date Column for both posts & pages
function modified_column_register( $columns ) {
    $columns['Modified'] = __( 'Modified Date', 'show_modified_date_in_admin_lists' );
    return $columns;
}
add_filter( 'manage_posts_columns', 'modified_column_register' );
add_filter( 'manage_pages_columns', 'modified_column_register' );

function modified_column_display( $column_name, $post_id ) {
    switch ( $column_name ) {
        case 'Modified':
            global $post;
            echo '<p class="mod-date">';
            echo '<em>'.get_the_modified_date().' '.get_the_modified_time().'</em><br />';
            echo '<small>' . esc_html__( 'by ', 'show_modified_date_in_admin_lists' ) . '<strong>'.get_the_modified_author().'<strong></small>';
            echo '</p>';
            break; // end all case breaks
    }
}
add_action( 'manage_posts_custom_column', 'modified_column_display', 10, 2 );
add_action( 'manage_pages_custom_column', 'modified_column_display', 10, 2 );

function modified_column_register_sortable( $columns ) {
    $columns['Modified'] = 'modified';
    return $columns;
}
add_filter( 'manage_edit-post_sortable_columns', 'modified_column_register_sortable' );
add_filter( 'manage_edit-page_sortable_columns', 'modified_column_register_sortable' );
add_filter( 'manage_edit-tasks_sortable_columns', 'modified_column_register_sortable' );



/*
// add_action('user_register', 'set_user_metaboxes');
add_action('admin_init', 'set_user_metaboxes');
function set_user_metaboxes($user_id=NULL) {

    // These are the metakeys we will need to update
    $meta_key['order'] = 'meta-box-order_post';
    $meta_key['hidden'] = 'metaboxhidden_post';

    // So this can be used without hooking into user_register
    if ( ! $user_id)
        $user_id = get_current_user_id();

    // Set the default order if it has not been set yet
    $cvalue = get_user_meta( $user_id, $meta_key['order'], true);
        $meta_value = array(
            'side' => 'submitdiv,formatdiv,categorydiv,postimagediv',
            'normal' => 'postexcerpt,tagsdiv-post_tag,postcustom,commentstatusdiv,commentsdiv,trackbacksdiv,slugdiv,authordiv,revisionsdiv',
            'advanced' => '',
        );
        update_user_meta( $user_id, $meta_key['order'], $meta_value );


    // Set the default hiddens if it has not been set yet
    $cvalue  = get_user_meta( $user_id, $meta_key['hidden'], true);
        $meta_value = array('layoutdiv', 'commentstatusdiv','commentsdiv', 'revisionsdiv');
        update_user_meta( $user_id, $meta_key['hidden'], $meta_value );

}*/
