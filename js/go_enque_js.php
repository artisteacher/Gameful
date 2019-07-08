<?php

/*
 * Registering Scripts/Styles For The Front-end
 */

add_action( 'wp_enqueue_scripts', 'go_scripts' );
function go_scripts () {
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
    $is_admin = go_user_is_admin($user_id);

    wp_localize_script( 'go_frontend', 'SiteURL', get_site_url() );
    wp_localize_script( 'go_frontend', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    wp_localize_script( 'go_frontend', 'PluginDir', array( 'url' => plugin_dir_url( dirname(__FILE__)) ) );

    wp_localize_script(
        'go_frontend',
        'GO_FRONTEND_DATA',
        array(
            'nonces' => array(
                'go_update_admin_view'          => wp_create_nonce( 'go_update_admin_view' ),//on task pages only
                'go_update_last_map'            => wp_create_nonce('go_update_last_map'),//at login and on map
                'go_blog_opener'                => wp_create_nonce('go_blog_opener'),//this is the form, needed all over the place on front end
                'go_blog_submit'                => wp_create_nonce('go_blog_submit'),
                'go_blog_trash'                 => wp_create_nonce('go_blog_trash'),//on reader, blog, tasks
                'go_filter_reader'              => wp_create_nonce('go_filter_reader'),//only needed on reader
                'go_reader_bulk_read'           => wp_create_nonce('go_reader_bulk_read'),
                'go_reader_read_printed'        => wp_create_nonce('go_reader_read_printed'),
                'go_show_private'               => wp_create_nonce('go_show_private'),
                'go_num_posts'                  => wp_create_nonce('go_num_posts'),
                'go_buy_item'                   => wp_create_nonce( 'go_buy_item' ),
                'go_get_purchase_count'         => wp_create_nonce( 'go_get_purchase_count' ),
            ),
            'go_is_admin'                   => $is_admin,
            'userID'	=>  $user_id
        )
    );

    go_localize_all_pages();

}
