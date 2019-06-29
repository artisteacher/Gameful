<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 10/13/18
 * Time: 8:20 PM
 */


/**
 * Activate for existing sites on plugin activation
 * @param $network_wide
 */
function go_update_db_ms( ) {
    global $wpdb;
    if ( is_multisite() ) {
        // Get all blogs in the network and update db on each one
        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        foreach ( $blog_ids as $blog_id ) {
            switch_to_blog( $blog_id );
            go_update_db_check();
            restore_current_blog();
        }
    }else{
        go_update_db_check();
    }
}

/**
 * MULTISITE FUNCTIONS
 * https://sudarmuthu.com/blog/how-to-properly-create-tables-in-wordpress-multisite-plugins/
 */

/**
 * Creating table whenever a new blog is created
 * Only do this if plugin is active network wide
 * @param $blog_id
 * @param $user_id
 * @param $domain
 * @param $path
 * @param $site_id
 * @param $meta
 */
function go_on_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
    $is_ms = go_is_ms_active_network_wide();
    if ( $is_ms ) {
        switch_to_blog( $blog_id );
        go_update_db();
        restore_current_blog();
    }
}
add_action( 'wpmu_new_blog', 'go_on_create_blog', 10, 6 );

/**
 * // Deleting the tables whenever a blog is deleted
 * @param $tables
 * @return array
 */
function go_on_delete_blog( $tables ) {
    global $wpdb;
    $tables[] = $wpdb->prefix . 'go_tasks';
    $tables[] = $wpdb->prefix . 'go_actions';
    $tables[] = $wpdb->prefix . 'go_loot';
    //$tables[] = $wpdb->prefix . 'go_totals';

    return $tables;
}
add_filter( 'wpmu_drop_tables', 'go_on_delete_blog' );






/**
 * Registers Game On custom post types and taxonomies, then
 * updates the site's rewrite rules to mitigate cpt and
 * permalink conflicts. flush_rewrite_rules() must always
 * be called AFTER custom post types and taxonomies are
 * registered.
 */
/**
 * Flush rewrite rules on activation
 */
function go_flush_rewrites() {
    //CPT registration functions are here and also hooked into init
    //the init action runs after the activation hook that calls this function
    //running the registration functions here makes the cpts available at the flush
    go_register_task_tax_and_cpt();// on init priority 0
    go_register_store_tax_and_cpt();// on init priority 0
    go_blogs();// on init priority 0


    //These run on init as well.
    //It might seem unnecessary to run them every load with init but is needed because
    //it makes sure the rewrites are always available even if another plugin flushes the rules.
    go_blogs_rewrite();// on init priority 10 (default), adds rewrite rule
    go_map_page();// on init priority 10 (default), adds rewrite rule
    go_store_page();// on init priority 10 (default), adds rewrite rule
    go_reader_page();// on init priority 10 (default), adds rewrite rule
    go_login_rewrite(); // on init priority 10 (default), adds rewrite rule
    go_reset_password_rewrite();// on init priority 10 (default), adds rewrite rule
    go_profile_rewrite();// on init priority 10 (default), adds rewrite rule
    go_registration_rewrite();// on init priority 10 (default), adds rewrite rule
    go_leaderboard_rewrite();// on init priority 10 (default), adds rewrite rule

    flush_rewrite_rules();
    //use a tool to flush them on a multisite as needed.
}

//When does the flush need to happen.
//Activation on a non-multisite
//Manually on multisite with a tool.
//Is there a hook for the flush.




//creates a page for the store on activation of plugin
function go_store_activate() {
    $my_post = array(
        'post_title'    => 'Store',
        'post_content'  => '[go_make_store]',
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_type'   => 'page',
    );
    // Insert the post into the database

    $page = get_page_by_path( "store" , OBJECT );

    if ( ! isset($page) ){
        wp_insert_post( $my_post );
    }
}


/**
 *
 */
function go_map_activate() {
    $my_post = array(
        'post_title'    => 'Map',
        'post_content'  => '[go_make_map]',
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_type'   => 'page',
    );

    $page = get_page_by_path( "map" , OBJECT );

    if ( ! isset($page) ){
        wp_insert_post( $my_post );
    }
}

/**
 *
 */
function go_reader_activate() {
    $my_post = array(
        'post_title'    => 'Reader',
        'post_content'  => '[go_make_reader]',
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_type'   => 'page',
    );

    $page = get_page_by_path( "Reader" , OBJECT );

    if ( ! isset($page) ){
        wp_insert_post( $my_post );
    }
}




/**
 * Changes roles so subscribers can upload media
 */
function go_media_access() {
    $role = get_role( 'subscriber' );
    $role->add_cap( 'upload_files' );
}

function go_tsk_actv_activate() {
    add_option( 'go_tsk_actv_do_activation_redirect', true );
    update_option( 'go_display_admin_explanation', true );
}



//this is the activation notification
function go_admin_head_notification() {
    if ( get_option( 'go_display_admin_explanation' ) && current_user_can( 'manage_options' ) ) {
        $nonce = wp_create_nonce( 'go_admin_remove_notification' );
        echo "<div id='go_activation_message' class='update-nag' style='font-size: 16px; padding-right: 20px;'>

      <div style='float:right;' <a href='javascript:;' onclick='go_remove_admin_notification()'><i class=\"far fa-times-circle\"></i></a></div>

			<div style='position: relative; padding: 10px 30px; clear:both;'>
			 This is a fresh installation of <a href='https://github.com/mcmick/game-on-v4/releases' target='_blank'>Game On</a>.
				<br>
				<br>
				Visit the <a href='http://maclab.guhsd.net/game-on' target='_blank'>documentation page</a>.
				<br>
				<br>
				Visit our <a href='https://www.youtube.com/channel/UC1G3josozpubdzaINcFjk0g' >YouTube Channel</a> for the most recent updates.
				<br>
			</div>
			
		</div>
		<script>
			function go_remove_admin_notification() {
				jQuery.ajax( {
					type: 'post',
					url: MyAjax.ajaxurl,
					data: {
						_ajax_nonce: '{$nonce}',
						action: 'go_admin_remove_notification'
					},
					success: function( ) {
							jQuery('#go_activation_message').remove();
					}
				} );
			}
		</script>";
    }
}
add_action( 'admin_notices', 'go_admin_head_notification' );


/*
//this is the activation notification
function go_upgrade_notification() {
    if ( !get_site_option( 'go_update_version_5') && current_user_can( 'manage_options' ) ) {
        $url = get_site_url(null, 'wp-admin/admin.php?page=game-tools');
        echo "<div id='go_activation_message' class='update-nag' style='font-size: 16px; padding-right: 50px;'>.
            <h2>Game On needs to upgrade it's database to v5.</h2>
            <p>Please use the upgrade tool on the <a href='{$url}'>tools page</a></p>
			
		</div>";
    }
}
add_action( 'admin_notices', 'go_upgrade_notification' );
*/



function go_tsk_actv_redirect() {
    if ( get_option( 'go_tsk_actv_do_activation_redirect', false ) ) {
        delete_option( 'go_tsk_actv_do_activation_redirect' );
        if ( ! isset( $_GET['activate-multi'] ) ) {
            wp_redirect( 'admin.php?page=go_options' );
        }
    }
}
add_action( 'admin_init', 'go_tsk_actv_redirect' );



//add_action('init', 'go_v5_update_db');



