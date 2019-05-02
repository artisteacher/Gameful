<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-04-01
 * Time: 23:53
 */

//add_filter( 'wp_default_editor', create_function('', 'return "tinymce";'));
add_filter( 'wp_default_editor', function() {return 'tinymce';});

function go_get_blog_posts($user_id = null){
    // get the username based from uname value in query var request.

    $current_user_id = get_current_user_id();

    if($current_user_id === intval($user_id)){
        $is_current_user = true;
    }else{
        $is_current_user = false;
    }
    $is_admin = go_user_is_admin($current_user_id);

    $show_private = get_user_meta($user_id, 'go_show_private', true);

    if(($is_admin || $is_current_user) && intval($show_private) === 1){
        $query_statuses = array("read", "unread", "reset", "draft", "trash", "publish");
        $private_query = array();
        $private_query_v4=  array();
    }else{
        $query_statuses = array("read", "unread", "publish");
        $private_query_v4 = array(//this wasn't used in v4 posts and needs to query for when it doesn't exist
            'key'     => 'go_blog_private_post',
            'value'   => '',
            'compare' => 'NOT EXISTS',
        );
        $private_query = array(
            'key'     => 'go_blog_private_post',
            'value'   => 1,
            'compare' => '!=',
        );
    }

    $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
//$paged = get_query_var('paged');
// Query param
    $arg = array(
        'post_type'         => 'go_blogs',
        'posts_per_page'    => 5,
        'orderby'           => 'publish_date',
        'order'             => 'DESC',
        'author'       => $user_id,
        'paged' => $paged,
        'post_status' => $query_statuses,
        'meta_query' => array('relation' => 'OR', $private_query_v4, $private_query),

    );

    $arg_localize = array(
        'post_type'         => 'go_blogs',
        'posts_per_page'    => 5,
        'orderby'           => 'publish_date',
        'order'             => 'DESC',
        'author'       => $user_id,
        'post_status' => $query_statuses,
        'meta_query' => array('relation' => 'OR', $private_query_v4, $private_query),

    );
    $arg_localize = serialize($arg_localize);
//build query
    //global $go_query;
    $go_query = new WP_Query( $arg );

// get query request
    $posts = $go_query->get_posts();

//video options
    $go_lightbox_switch = get_option( 'options_go_video_lightbox' );
    $go_video_unit = get_option ('options_go_video_width_unit');
    if ($go_video_unit == 'px'){
        $go_fitvids_maxwidth = get_option('options_go_video_width_pixels')."px";
    }
    if ($go_video_unit == '%'){
        $go_fitvids_maxwidth = get_option('options_go_video_width_percent')."%";
    }

    echo "<div id='go_wrapper' data-lightbox='{$go_lightbox_switch}' data-maxwidth='{$go_fitvids_maxwidth}' style='background-color: #f2f2f2' >";
// check if there's any results
    if ( empty($posts) ) {
        echo "Author doesn't have any posts";
    } else {

        ?>

        <div class="go_blog_container1" style="display: flex; justify-content: center;">
            <div class="go_blog_container" style="    display: flex;
    justify-content: center;
    flex-direction: column;
    padding: 20px;
    flex-grow: 1;
    max-width: 800px;"><?php
                foreach ($posts as $post){
                    $post = json_decode(json_encode($post), True);//convert stdclass to array by encoding and decoding
                    $post_id = $post['ID'];
                    go_blog_post($post_id, null,false, true, false, true);
                    //go_user_feedback_container($post_id);
                }
                ?>


                <div class="pagination">
                    <?php


                    // don't display the button if there are not enough posts
                    if (  $go_query->max_num_pages > 1 )
                        echo '<div class="misha_loadmore go_loadmore_blog">More posts</div>'; // you can use <a> as well

                    echo paginate_links( array(
                        'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                        'total'        => $go_query->max_num_pages,
                        'current'      => max( 1, get_query_var( 'paged' ) ),
                        'format'       => '?paged=%#%',
                        'show_all'     => false,
                        'type'         => 'plain',
                        'end_size'     => 2,
                        'mid_size'     => 1,
                        'prev_next'    => true,
                        'prev_text'    => sprintf( '<i></i> %1$s', __( 'Newer Posts', 'text-domain' ) ),
                        'next_text'    => sprintf( '%1$s <i></i>', __( 'Older Posts', 'text-domain' ) ),
                        'add_args'     => false,
                        'add_fragment' => '',
                    ) );
                    ?>


                </div>

            </div>
        </div>

        <?php
    }
    echo "</div>";


    // now the most interesting part
    // we have to pass parameters to myloadmore.js script but we can get the parameters values only in PHP
    // you can define variables directly in your HTML but I decided that the most proper way is wp_localize_script()
    wp_localize_script( 'go_frontend', 'misha_loadmore_params', array(
        'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
        //'posts' => json_encode( $go_query->query_vars ), // everything about your loop is here
        'current_page' => get_query_var( 'paged' ) ? get_query_var('paged') : 1,
        'max_page' => $go_query->max_num_pages,
        'myargs' => $arg_localize
    ) );

    wp_enqueue_script( 'go_loadmore' );

}

//https://rudrastyh.com/wordpress/load-more-posts-ajax.html
function misha_loadmore_ajax_handler(){

    // prepare our arguments for the query
    //$args = json_decode( stripslashes( $_POST['query'] ), true );
    $paged = $_POST['page'] + 1; // we need next page to be loaded
    $myargs = $_POST['myargs'];
    $myargs = stripslashes($myargs);
    $myargs = unserialize($myargs);
    $myargs['paged'] = $paged;
    $current_user_id = get_current_user_id();



    $show_private = $myargs['meta_query'][1]['value'];

        $author = $myargs['author'];

    //double check for the private query
    if(intval($current_user_id) === intval($author)){
        $is_current_user = true;
    }else{
        $is_current_user = false;
    }
    $is_admin = go_user_is_admin($current_user_id);

    if ($show_private && (!$is_admin && !$is_current_user)){
        echo 'refresh';
        die();
    }

    $go_query = new WP_Query( $myargs );

    $posts = $go_query->get_posts();
    foreach ($posts as $post){
        $post = json_decode(json_encode($post), True);//convert stdclass to array by encoding and decoding
        $post_id = $post['ID'];
        go_blog_post($post_id, null,false, true, false, true);
        //go_user_feedback_container($post_id);
    }

    die; // here we exit the script and even no wp_reset_query() required!
}



add_action('wp_ajax_loadmore', 'misha_loadmore_ajax_handler'); // wp_ajax_{action}
add_action('wp_ajax_nopriv_loadmore', 'misha_loadmore_ajax_handler'); // wp_ajax_nopriv_{action}

//gets the task id from a Blog post Id
//works for v4 and v5 blog_posts
function go_get_task_id($post_id){
    global $wpdb;
    $aTable = "{$wpdb->prefix}go_actions";
    $task_id = wp_get_post_parent_id($post_id);

    if(!$task_id){
        $task_id = get_post_meta($post_id, 'go_blog_task_id', true);
        //$go_blog_task_id = (isset($blog_meta['go_blog_task_id'][0]) ? $blog_meta['go_blog_task_id'][0] : null);
    }
    if(!$task_id) {
        $task_id = $wpdb->get_var($wpdb->prepare("SELECT source_id
				FROM {$aTable} 
				WHERE result = %d AND  action_type = %s
				ORDER BY id DESC LIMIT 1",
            intval($post_id),
            'task'));
    }
    return $task_id;
}