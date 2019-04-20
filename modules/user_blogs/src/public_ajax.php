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

    if(($is_admin || $is_current_user) && $show_private){
            $query_statuses = array("read", "unread", "reset", "draft", "trash");
            $private_query = array();
    }else{
        $query_statuses = array("read", "unread");
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
        'meta_query' => array($private_query),

    );
//build query
    $query = new WP_Query( $arg );

// get query request
    $posts = $query->get_posts();

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
                    go_blog_post($post_id, false, true, false, true);
                    //go_user_feedback_container($post_id);
                }
                ?>


                <div class="pagination">
                    <?php
                    echo paginate_links( array(
                        'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                        'total'        => $query->max_num_pages,
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
}