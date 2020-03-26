<?php

/*
 * Registering Scripts/Styles For The Front-end
 */

if (! function_exists('slug_scripts_masonry') ) :
    if ( ! is_admin() ) :
        function slug_scripts_masonry() {
            wp_enqueue_script('masonry');
            wp_enqueue_script('imagesloaded');
           // wp_enqueue_style('masonry’, get_template_directory_uri(). '/css/');
        }
        add_action( 'wp_enqueue_scripts', 'slug_scripts_masonry' );
    endif; //! is_admin()
endif; //! slug_scripts_masonry exists


add_action( 'wp_enqueue_scripts', 'go_scripts' );
function go_scripts () {

    if(is_gameful() && is_main_site() && !is_user_logged_in()  && is_front_page()){
        return;
    }


    global $go_js_version;
    wp_register_script( 'go_wp_media', get_site_url(null, 'wp-admin/css/media.css'), null, $go_js_version );

	/*
	 * Registering Scripts For The Front-end
	 */
    wp_enqueue_script( 'mce-view' );

	wp_enqueue_style( 'dashicons' );

    //wp_register_script( 'go_loadmore', plugin_dir_url( __FILE__ ).'dev/scripts/go_load_more.js', null, $go_js_version );

    //COMBINED FILE
    wp_register_script( 'go_frontend', plugin_dir_url( __FILE__ ).'min/go_frontend-min.js', array('jquery'), $go_js_version, true);
    wp_enqueue_script( 'go_frontend' );

    //wp_register_script( 'masonry', plugin_dir_url( __FILE__ ).'min/masonry.js', array('jquery'), $go_js_version, true);
    //wp_enqueue_script( 'masonry' );

    wp_register_script( 'go_all_pages_js', plugin_dir_url( __FILE__ ).'min/go_all-min.js', array('jquery'), $go_js_version, true);
    wp_enqueue_script( 'go_all_pages_js' );

    wp_register_script( 'go_combined_js_depend', plugin_dir_url( __FILE__ ).'min/go_combine_dependencies-min.js', array( 'jquery' ), $go_js_version, true);
    wp_enqueue_script( 'go_combined_js_depend' );

    // Dependencies
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-accordion' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'jquery-ui-draggable' );
    wp_enqueue_script( 'jquery-ui-droppable' );
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script( 'jquery-ui-spinner' );
    wp_enqueue_script( 'jquery-ui-progressbar' );
    wp_enqueue_script( 'jquery-effects-core' );
    /**
     * Tabs
     */
    wp_enqueue_script( 'jquery-ui-tabs');

    /*
     * Localizing Scripts For The Front-end
     */

    $is_admin = go_user_is_admin();
    if ($is_admin){
        //wp_register_script('go_admin_notification_listener', plugins_url('min/go_admin_notifications-min.js', __FILE__), array('jquery'), $go_js_version, true);
        //wp_enqueue_script( 'go_admin_notification_listener' );
        wp_localize_script(
            'go_all_pages_js',
            'GO_ADMIN_DATA',
            array(
                'nonces' => array(
                    'go_admin_messages'         => wp_create_nonce( 'go_admin_messages'),
                )
            )
        );
    }

    // Localization
    $user_id = get_current_user_id();
    //is the current user an admin
    $is_admin = go_user_is_admin();
    $blog_id = get_current_blog_id();
    wp_localize_script( 'go_frontend', 'SiteURL', get_site_url() );
    $ajaxurl =  admin_url( 'admin-ajax.php' );
    wp_localize_script( 'go_frontend', 'MyAjax', array( 'ajaxurl' => $ajaxurl ) );

    wp_localize_script( 'go_frontend', 'ajaxurl', $ajaxurl);
    wp_localize_script( 'go_frontend', 'PluginDir', array( 'url' => plugin_dir_url( dirname(__FILE__)) ) );
    wp_localize_script( 'go_frontend', 'blog_id', strval($blog_id));
    wp_localize_script( 'go_frontend', 'network_login', network_site_url ('login?blog_id='.$blog_id));
    wp_localize_script( 'go_frontend', 'is_frontend', "true");


    //**
    //set the nonce for the admin view change with the current uid even when in guest view
    //***
    global $go_user_guest_view;
    if($go_user_guest_view){

        $is_admin = true;
        global $current_user;
        $current_user = $go_user_guest_view;

    }
    wp_localize_script(
        'go_frontend',
        'GO_ADMIN_VIEW',
        array(
            'go_update_admin_view'          => wp_create_nonce( 'go_update_admin_view' ),//on task pages only
        )
    );
    if($go_user_guest_view){
        global $current_user;
        $current_user = -1;
    }




    wp_localize_script(
        'go_frontend',
        'GO_FRONTEND_DATA',
        array(
            'nonces' => array(
                'go_update_admin_view'          => wp_create_nonce( 'go_update_admin_view' ),//on task pages only
                'go_blog_opener'                => wp_create_nonce('go_blog_opener'),//this is the form, needed all over the place on front end
                'go_blog_post_opener'           => wp_create_nonce('go_blog_post_opener'),
                'go_blog_submit'                => wp_create_nonce('go_blog_submit'),
                'go_blog_trash'                 => wp_create_nonce('go_blog_trash'),//on reader, blog, tasks
                'go_filter_reader'              => wp_create_nonce('go_filter_reader'),//only needed on reader
                'go_reader_bulk_read'           => wp_create_nonce('go_reader_bulk_read'),
                'go_reader_read_printed'        => wp_create_nonce('go_reader_read_printed'),
                //'go_show_private'               => wp_create_nonce('go_show_private'),
                'go_num_posts'                  => wp_create_nonce('go_num_posts'),
                'go_buy_item'                   => wp_create_nonce( 'go_buy_item' ),
                'go_get_purchase_count'         => wp_create_nonce( 'go_get_purchase_count' ),
                'go_change_avatar'              => wp_create_nonce( 'go_change_avatar' ),
                'go_importer'                   => wp_create_nonce( 'go_importer' ),
                'go_blog_autosave'              => wp_create_nonce('go_blog_autosave'),
                'go_follow_request'             => wp_create_nonce('go_follow_request'),
                'go_follow_request_accept'      => wp_create_nonce('go_follow_request_accept'),
                'go_follow_unfollow'            => wp_create_nonce('go_follow_unfollow'),
                'go_follow_remove_follower'     => wp_create_nonce('go_follow_remove_follower'),
                'go_follow_request_deny'        => wp_create_nonce('go_follow_request_deny'),
                'go_leaderboard_dataloader_ajax'=> wp_create_nonce('go_leaderboard_dataloader_ajax'),
                'go_make_feed'                  => wp_create_nonce('go_make_feed'),
                'go_followers_list'             => wp_create_nonce('go_followers_list'),
                'go_following_list'             => wp_create_nonce('go_following_list'),
                'go_new_pagination_ajax'        => wp_create_nonce('go_new_pagination_ajax'),
                'go_attendance_check_ajax'      => wp_create_nonce('go_attendance_check_ajax'),
                'go_quests_frontend'            => wp_create_nonce('go_quests_frontend'),

            ),
            'go_is_admin'                   => $is_admin,
            'userID'	=>  $user_id
        )
    );


    $current_user = wp_get_current_user();
    wp_localize_script( 'go_frontend', 'TMA',
        array(
            'myurl'       => plugin_dir_url(dirname(__FILE__)),
            'id'        => $current_user->ID,
            'author'    => $current_user->display_name,
            'errors'    => array(
                'missing_fields'        => __('Select the color and the annotation text', 'tinymce-annotate'),
                'missing_annotation'    => __('Please select some text for creating an annotation', 'tinymce-annotate'),
                'missing_selected'      => __('Please select the annotation you want to delete', 'tinymce-annotate')
            ),
            'tooltips'  => array(
                'annotation_settings'   => __('Annotation settings', 'tinymce-annotate'),
                'annotation_create'     => __('Create annotation', 'tinymce-annotate'),
                'annotation_delete'     => __('Delete annotation', 'tinymce-annotate'),
                'annotation_hide'       => __('Hide annotations', 'tinymce-annotate')
            ),
            'settings'  => array(
                'setting_annotation'    => __('Annotation', 'tinymce-annotate'),
                'setting_background'    => __('Background color', 'tinymce-annotate')
            )
        )
    );



    $page_uri = go_get_page_uri();
    $map_url = get_option('options_go_locations_map_map_link');
    if ($page_uri === $map_url) {
        wp_localize_script(
            'go_frontend',
            'go_is_map',
            array(true)
        );
    }

    $reader_url = 'reader';
    if ($page_uri === $reader_url) {
        wp_localize_script(
            'go_frontend',
            'go_is_reader_or_blog',
            array(true)
        );
    }


    $blog_url = get_query_var('query_type');
    if ('user_blog' === $blog_url) {
        wp_localize_script(
            'go_frontend',
            'go_is_reader_or_blog',
            array(true)
        );
    }



    $page_uri = go_get_page_uri();
    $store_url = get_option('options_go_store_store_link');
    if ($page_uri === $store_url) {
        wp_localize_script(
            'go_frontend',
            'go_is_store',
            array(true)
        );
    }

    go_localize_all_pages();

}
