<?php
add_action( 'admin_enqueue_scripts', 'go_admin_scripts' );
function go_admin_scripts ($hook) {
    global $post;
    global $go_js_version;

    /*
     * Registering Scripts For Admin Pages
     */

    wp_enqueue_style( 'dashicons' );

    /*
     * Combined scripts for every admin page. Combine all scripts unless the page needs localization.
     */

    wp_register_script( 'go_admin_user', plugin_dir_url( __FILE__ ).'min/go_admin_user-min.js', array( 'jquery' ), $go_js_version, true);

    wp_register_script( 'go_all_pages_js', plugin_dir_url( __FILE__ ).'min/go_all-min.js', array('jquery'), $go_js_version, true);
    wp_enqueue_script( 'go_all_pages_js' );

    wp_register_script( 'go_combined_js_depend', plugin_dir_url( __FILE__ ).'min/go_combine_dependencies-min.js', array( 'jquery' ), $go_js_version, true);
    wp_enqueue_script( 'go_combined_js_depend' );

    //this one doesn't minify for some reason
    //wp_register_script( 'go_admin-tools', plugin_dir_url( __FILE__ ).'scripts/go_tools.js', array( 'jquery' ), $go_js_version, true);

    /*
     * Enqueue Scripts For Admin Pages (Except for page specific ones below)
     */

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
    //wp_enqueue_script( 'go_featherlight_min' );

    //Combined Scripts
    //wp_enqueue_script( 'go_scripts' );

    //END Combined Scripts
    //single script
    //wp_enqueue_script( 'go_admin-tools' );

    //LOCALIZE
    // Localization for all admin page
    wp_localize_script( 'go_admin_user', 'SiteURL', get_site_url() );
    wp_localize_script( 'go_admin_user', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    wp_localize_script( 'go_admin_user', 'PluginDir', array( 'url' => plugin_dir_url(dirname(__FILE__) ) ) );

    wp_localize_script(
        'go_admin_user',
        'GO_ADMIN_PAGE_DATA',
        array(
            'nonces' => array(
                'go_user_map_ajax'              => wp_create_nonce('go_user_map_ajax'),//on the clipboard
                'go_reset_all_users'			=> wp_create_nonce( 'go_reset_all_users'),//could be just on tools
                'go_flush_all_permalinks'			=> wp_create_nonce( 'go_flush_all_permalinks'),//could be just on tools
            ),
        )
    );

    go_localize_all_pages();

    $is_admin_user = go_user_is_admin();
    if ($is_admin_user){
        wp_localize_script(
            'go_admin_user',
            'GO_ADMIN_DATA',
            array(
                'nonces' => array(
                    'go_admin_messages' => wp_create_nonce('go_admin_messages'),
                )
            )
        );

        wp_localize_script(
            'go_admin_user',
            'GO_ACF_DATA',
            array(
                'go_store_toggle'       => get_option('options_go_store_toggle') ,
                'go_map_toggle'         => get_option('options_go_locations_map_toggle') ,
                'go_gold_toggle'        => get_option('options_go_loot_gold_toggle') ,
                'go_xp_toggle'          => get_option('options_go_loot_xp_toggle') ,
                'go_health_toggle'      => get_option('options_go_loot_health_toggle') ,
                'go_badges_toggle'      => get_option('options_go_badges_toggle'),
                //'go_leaderboard_toggle'      => get_option('options_go_stats_leaderboard_toggle')

            )
        );

        wp_enqueue_script( 'go_admin_user' );

        if ( 'toplevel_page_go_clipboard' === $hook ) {

            /*
             * Clipboard Scripts
             */
            wp_localize_script(
                'go_admin_user',
                'GO_CLIPBOARD_DATA',
                array(
                    'nonces' => array(
                        'go_clipboard_stats'          => wp_create_nonce( 'go_clipboard_stats' ),
                        'go_clipboard_activity' => wp_create_nonce( 'go_clipboard_activity' ),
                        'go_clipboard_messages' => wp_create_nonce( 'go_clipboard_messages'),
                        'go_clipboard_store' => wp_create_nonce( 'go_clipboard_store'),
                        'go_clipboard_save_filters'     => wp_create_nonce( 'go_clipboard_save_filters' )
                    ),
                )
            );


            wp_localize_script(
                'go_admin_user',
                'go_is_map',
                array(true)
            );
        }

        // Enqueue and Localization for options page
        if ( 'options_page_go_options' === $hook ) {
            wp_localize_script('go_admin_user', 'levelGrowth', get_option('options_go_loot_xp_levels_growth'));
            wp_localize_script('go_admin_user', 'go_is_options_page', array('is_options_page' => true));
        }

        if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
            if ( 'go_store' === $post->post_type ) {
                //wp_enqueue_script('go_edit_store');
                $id = get_the_ID();
                $store_name = get_option( 'options_go_store_name');
                wp_localize_script( 'go_admin_user', 'GO_EDIT_STORE_DATA', array( 'postid' => $id , 'store_name' => $store_name, 'is_store_edit' => true ));
            }
        }
    }


    if ($hook === 'toplevel_page_game-tools') {
        wp_localize_script(
            'go_admin_user',
            'go_is_tools',
            array(true)
        );
    }


}

function go_admin_enqueue_scripts_acf() {

    global $go_js_version;
    wp_register_script( 'go_acf_js', plugin_dir_url( __FILE__ ).'scripts/go_acf_admin.js', array( 'jquery' ), $go_js_version, true);
    wp_enqueue_script( 'go_acf_js');

}

add_action('acf/input/admin_enqueue_scripts', 'go_admin_enqueue_scripts_acf');


