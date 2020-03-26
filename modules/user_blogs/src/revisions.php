<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 1/14/19
 * Time: 8:26 PM
 */

add_action( 'wp_restore_post_revision',    'pmr_restore_revision', 10, 2 );
function pmr_restore_revision( $post_id, $revision_id ) {
    $post_meta      = get_post_meta($post_id);
    $revision_meta      = get_post_meta($revision_id);

    //remove post meta
    foreach($post_meta as $meta_key=>$meta_value) {
        delete_post_meta($post_id, $meta_key);
    }

    //add revision meta to post
    foreach($revision_meta as $meta_key=>$meta_value) {
        update_post_meta($post_id, $meta_key, $meta_value);
    }
}

function pmr_save_post( $post_id, $post ) {
    if ( $parent_id = wp_is_post_revision( $post_id ) ) {
        $parent = get_post($parent_id);

        $blog_meta = get_post_meta($parent_id);
        //Save values from created array into db
        foreach($blog_meta as $meta_key=>$meta_value) {
            update_post_meta($post_id, $meta_key, $meta_value);
        }

    }


}
add_action( 'save_post', 'pmr_save_post', 10, 2 );
