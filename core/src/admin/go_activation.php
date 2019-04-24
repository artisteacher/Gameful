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
        // Get all blogs in the network and activate plugin on each one
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
    // call your CPT registration function here (it should also be hooked into 'init')
    go_register_task_tax_and_cpt();
    go_register_store_tax_and_cpt();
    go_blog_tags();
    go_blogs();
    go_custom_rewrite();
    //go_reader_page();
    go_map_page();
    go_store_page();
    flush_rewrite_rules();
    go_custom_rewrite();
    //go_reader_page();
    go_map_page();
    go_store_page();

}



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
    //$role->add_cap( 'edit_posts' );

    //$role = get_role( 'contributor' );
    //$role->add_cap( 'upload_files' );

}

function go_tsk_actv_activate() {
    add_option( 'go_tsk_actv_do_activation_redirect', true );
    update_option( 'go_display_admin_explanation', true );
}



//this is the activation notification
function go_admin_head_notification() {
    if ( get_option( 'go_display_admin_explanation' ) && current_user_can( 'manage_options' ) ) {
        $nonce = wp_create_nonce( 'go_admin_remove_notification' );
        $url = get_site_url(null, 'wp-admin/admin.php?page=game-tools');
        echo "<div id='go_activation_message' class='update-nag' style='font-size: 16px; padding-right: 50px;'>This is a fresh installation of <a href='https://github.com/mcmick/game-on-v4/releases' target='_blank'>Game On</a>.

			<div style='position: relative; left: 20px;'>
				<br>
				Visit the <a href='http://maclab.guhsd.net/game-on' target='_blank'>documentation page</a>.
				<br>
				<br>
				Visit our <a href='https://www.youtube.com/channel/UC1G3josozpubdzaINcFjk0g' >YouTube Channel</a> for the most recent updates.
				<br>
				<br>
				Did you just update from version 3? Check out the <a href='{$url}'>upgrade tool</a>.
				<br>
				<br>
			</div>
			<a href='javascript:;' onclick='go_remove_admin_notification()'>Dismiss messsage</a>
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




function go_tsk_actv_redirect() {
    if ( get_option( 'go_tsk_actv_do_activation_redirect', false ) ) {
        delete_option( 'go_tsk_actv_do_activation_redirect' );
        if ( ! isset( $_GET['activate-multi'] ) ) {
            wp_redirect( 'admin.php?page=go_options' );
        }
    }
}
add_action( 'admin_init', 'go_tsk_actv_redirect' );

function go_v5_update_db(){
    if ( !get_site_option( 'go_update_version' ) ) {
        update_option('go_update_version', true );

        $query = new WP_Query(array(
            'post_type' => 'tasks',
            'posts_per_page' => 10000
        ));


        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            //get number of stages
            $stage_count = get_post_meta($post_id, 'go_stages', true);
            //UPDATE ALL STAGES
            for ($i = 0; $i <= $stage_count; $i++) {
                //add a uniqueID to the stage
                update_post_meta($post_id, 'go_stages_' . $i . '_uniqueid', $post_id . "_v4_stage" . $i);

                //this is used to set the required elements on the new repeater field
                $element_count = 0;

                //move old elements over
                $title = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_title', true);
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_title', $title);

                $private = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_private', true);
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_private', $private);

                $text = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_blog_text_toggle', true);
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_text_toggle', $text);

                $min = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_blog_text_minimum_length', true);
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_text_minimum_length', $min);

                $check_type = get_post_meta($post_id, 'go_stages_' . $i . '_check', true);

                if ($check_type == 'none' || $check_type == 'quiz' || $check_type == 'password'){
                    update_post_meta($post_id, 'go_stages_' . $i . '_check_v5', $check_type);
                }else{
                    update_post_meta($post_id, 'go_stages_' . $i . '_check_v5', 'blog');
                }

                //update the required elements, if the old check was a blog
                if ($check_type == 'blog') {
                    //if a URL was required, add it as a new required element
                    $url = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_url_toggle', true);
                    if ($url) {
                        update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_element', 'URL');
                        $validate = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_url_url_validation', true);
                        if (!empty($validate)) {
                            update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_requirements_url_validation', $validate);
                        }
                        // add a uniqueID
                        update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_url");
                        $element_count++;
                    }

                    //if a file upload was required, add it as a new required element
                    $file = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_attach_file_toggle', true);
                    if ($file) {
                        update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_element', 'File');
                        $restrict = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_attach_file_restrict_file_types', true);
                        if ($restrict) {
                            $types = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_attach_file_allowed_types', true);
                            update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_requirements_allowed_types', $types);
                        }
                        // add a uniqueID
                        update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_file");
                        $element_count++;
                    }

                    //if video was required, add it as a new required element
                    $video = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_video', true);
                    if ($video) {
                        update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_element', 'Video');
                        // add a uniqueID
                        update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_video");
                        $element_count++;
                    }
                    //add the count of the required elements. These are the repeater rows.
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements', $element_count);
                }
                //update if it was a file upload
                //add file as the only required element on the blog
                else if($check_type=='upload'){
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_0_element', 'File');
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements', 1);
                    //update_post_meta($post_id, 'go_stages_' . $i . '_check', 'blog');
                    // add a uniqueID
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_file");

                }
                //update if it was a URL
                //add URL as the only required element on the blog
                else if($check_type=='URL'){
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_0_element', 'URL');
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements', 1);
                    //update_post_meta($post_id, 'go_stages_' . $i . '_check', 'blog');
                    // add a uniqueID
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_url");

                }
            }//END STAGE UPDATE

            //BONUS STAGE UPDATE
            $bonus_element_count = 0;
            $check_type = get_post_meta($post_id, 'go_bonus_stage_check', true);
            if ($check_type == 'none' || $check_type == 'quiz' || $check_type == 'password'){
                update_post_meta($post_id, 'go_bonus_stage_check_v5', $check_type);
            }else{
                update_post_meta($post_id, 'go_bonus_stage_check_v5', 'blog');
            }

            if ($check_type == 'blog') {
                $private = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_private', true);
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_private', $private);

                $text = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_blog_text_toggle', true);
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_text_toggle', $text);

                $min = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_blog_text_minimum_length', true);
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_text_minimum_length', $min);


                //if URL was required, add it as a new required element
                $url = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_url_toggle', true);
                if ($url) {
                    update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_element', 'URL');
                    $validate = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_url_url_validation', true);
                    if (!empty($validate)) {
                        update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_requirements_url_validation', $validate);
                    }
                    $bonus_element_count++;
                    // add a uniqueID
                    update_post_meta($post_id, 'go_bonus_stage__blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_url");

                }

                //if file was required, add it as a new required element
                $file = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_attach_file_toggle', true);
                if ($file) {
                    update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_element', 'File');
                    $restrict = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_attach_file_restrict_file_types', true);
                    if ($restrict) {
                        $types = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_attach_file_allowed_types', true);
                        update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_requirements_allowed_types', $types);
                    }
                    $bonus_element_count++;
                    // add a uniqueID
                    update_post_meta($post_id, 'go_bonus_stage__blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_file");
                }

                //if video was required, add it as a new required element
                $video = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_video', true);
                if ($video) {
                    update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_element', 'Video');
                    $bonus_element_count++;
                    // add a uniqueID
                    update_post_meta($post_id, 'go_bonus_stage__blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_video");
                }

                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements', $bonus_element_count);

            }
            //update if it was a file upload on bonus.
            //add File as the only required element on the blog
            else if($check_type=='upload'){
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_0_element', 'File');
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements', 1);
                //update_post_meta($post_id, 'go_bonus_stage_check', 'blog');
                // add a uniqueID
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_file");
            }
            //update if it was a URL on bonus
            //add URL as the only required element on the blog
            else if($check_type=='URL'){
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_0_element', 'URL');
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements', 1);
                //update_post_meta($post_id, 'go_bonus_stage_check', 'blog');
                // add a uniqueID
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_url");
            }
            $key = 'go_post_data_' . $post_id;
            delete_transient($key);
        }
        wp_reset_query();
    }
}

//add_action('init', 'go_v5_update_db');



