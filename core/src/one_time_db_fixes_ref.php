<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2020-04-04
 * Time: 21:12
 */



//add_action('init', 'fix_favorites');
function fix_favorites()
{

    $sites = get_sites();
    $sites  = array_column($sites, 'blog_id');
    foreach ($sites as $site) {
        $fixed = get_option('go_fix_favorites');
        if($fixed){
            restore_current_blog();
            return;
        }

        $mail = get_blog_option($site, 'admin_email');
        $user_from_email = get_user_by('email', $mail);
        if(is_object($user_from_email)) {
            $user_id = $user_from_email->ID;
            if ($user_id) {
                switch_to_blog(intval($site));
                $user_array = array();
                $user_array[] = $user_id;
                $user_array = serialize($user_array);

                //get all posts with a favorite

                global $wpdb;
                $pmTable = "{$wpdb->prefix}postmeta";

                $wpdb->query( $wpdb->prepare("
                    UPDATE $pmTable
                    SET meta_value = %s
                    WHERE meta_key = 'go_blog_favorite'
                        AND meta_value = %s",
                    $user_array,
                    'true') );
                update_option('go_fix_favorites', true);
                restore_current_blog();

                //set favorite to an array with this user_id
            }
        }
    }
}

////add_action('init', 'fix_trashed');
function fix_trashed()
{

    $sites = get_sites();
    $sites  = array_column($sites, 'blog_id');
    foreach ($sites as $site) {
        switch_to_blog(intval($site));
        $fixed = get_option('go_fix_trashed');
        if($fixed){
            restore_current_blog();
            return;
        }

        $args = array(
            'post_type'         => 'go_blogs',
            'status' => 'ids'
        );
        //$query = new WP_Query($args);


        global $wpdb;
        $pmTable = "{$wpdb->prefix}postmeta";
        $pTable = "{$wpdb->prefix}posts";
        $aTable = "{$wpdb->prefix}go_actions";
        $tTable = "{$wpdb->prefix}go_tasks";

        $rows = $wpdb->get_results(
            "SELECT t3.*, t4.status, t4.bonus_status
              FROM
                (SELECT t1.ID, stage, bonus_status AS tbonus, uid, source_id
              FROM
                (SELECT ID
                FROM $pTable 
                WHERE post_status = 'trash'
                AND post_type = 'go_blogs') AS t1
                INNER JOIN {$aTable} AS t2 ON t1.ID = t2.result
                WHERE t2.action_type = 'blog_post' ) AS t3
                INNER JOIN {$tTable} AS t4 ON t3.source_id = t4.post_id
                WHERE t4.uid = t3.uid", ARRAY_A);
        $posts_array = array_column($rows, 'post_id');

        foreach($rows as $row){
            if(is_numeric($row['tbonus']) && $row['tbonus']>0){
                if($row['bonus_status'] > 0){
                    //mark as unread
                    wp_update_post(array(
                        'ID'    =>  $row['ID'],
                        'post_status'   =>  'unread'
                    ));
                }
            }else if(is_numeric($row['stage']) && $row['stage']>0){
                if($row['status'] >= $row['stage']){
                    //mark as unread
                    wp_update_post(array(
                        'ID'    =>  $row['ID'],
                        'post_status'   =>  'unread'
                    ));
                }
            }
            update_option('go_fix_trashed', true);
        }

    }
}

//add_action('init', 'go_fix_hidden');
function go_fix_hidden()
{

    $sites = get_sites();
    $sites  = array_column($sites, 'blog_id');
    foreach ($sites as $site) {
        switch_to_blog(intval($site));

        $fixed = get_option('go_fix_hidden');
        if($fixed){
            restore_current_blog();
            return;
        }

        //get all hidden posts
        //add new meta is true
        $args=array(
            'post_type'        => 'tasks',
            'orderby'          => 'meta_value_num',
            'order'            => 'ASC',
            'posts_per_page'   => -1,
            //'meta_key'         => 'go-location_map_opt',
            'post_status'      => 'publish',
            'suppress_filters' => true,
            'meta_query' => array(
                array(
                    'key'     => 'go-location_map_opt',
                    'value'   => 1,
                )
            ),

        );

        $posts = get_posts($args);
        foreach($posts as $post){
            $post_id= $post->ID;
            update_post_meta($post_id, 'go-location_map_options_hidden', 1 );
            update_post_meta($post_id, 'go-location_map_options_nested', 1 );
            update_post_meta($post_id, 'go-location_map_options_optional', 1 );
            $key = 'go_post_data_' . $post_id;
            go_delete_transient($key);
        }

        // $data = $data;
        update_option('go_fix_hidden', true);


        restore_current_blog();
    }

}


//add_action('init', 'go_fix_attendance');
function go_fix_attendance()
{

    $sites = get_sites();
    $sites  = array_column($sites, 'blog_id');
    foreach ($sites as $site) {

        switch_to_blog(intval($site));
        $fixed = get_option('go_fix_attendance');
        if($fixed){
            restore_current_blog();
            return;
        }

        $count = get_option('options_go_timed_award');
        $i=0;
        while($i < $count){

            $title = get_option('options_go_timed_award_'.$i.'_award_title');
            $active = get_option('options_go_timed_award_'.$i.'_active');

            update_option('options_go_timed_award_'.$i.'_information_award_title', $title);
            update_option('options_go_timed_award_'.$i.'_information_active', $active);
            $i++;
        }

        // $data = $data;
        update_option('go_fix_attendance', true);


        restore_current_blog();
    }

}


//add_action('init', 'go_fixed_canned_messages');
function go_fixed_canned_messages()
{

    $sites = get_sites();
    $sites  = array_column($sites, 'blog_id');
    foreach ($sites as $site) {

        switch_to_blog(intval($site));
        $fixed = get_option('go_fixed_canned_messages');
        if($fixed){
            restore_current_blog();
            return;
        }

        $count = get_option('options_go_messages_canned');
        $i=0;
        while($i < $count){

            $toggle = get_option('options_go_messages_canned_'.$i.'_toggle');
            if($toggle){
                update_option('options_go_messages_canned_'.$i.'_radio', 'add');
            }
            else{
                update_option('options_go_messages_canned_'.$i.'_radio', 'remove');
            }

            $i++;
        }

        // $data = $data;
        update_option('go_fixed_canned_messages', true);

        restore_current_blog();
    }

}


//This moves the old featured images to the new game on ones
//add_action('init', 'temp_convert_all_featured_images', 1);
function temp_convert_all_featured_images(){
    //get all posts
    //for each post
    $args = array(
        'post_type' => 'tasks',
        'meta_query' => array(
            array(
                'key' => '_thumbnail_id',
            )
        )
    );
    $query = new WP_Query($args);

    $posts = $query->posts;
    foreach($posts as $post){
        $image_id = get_post_meta($post->ID,'_thumbnail_id');
        $image_id = $image_id[0];
        $post_id = $post->ID;
        update_post_meta( $post_id, 'go_featured_image', $image_id );
        delete_post_meta( $post_id, '_thumbnail_id' );
        $key = 'go_post_data_' . $post_id;
        go_delete_transient($key);

    }
    //if it has featured image
    //set new featured image
    //remove old retured image
}
