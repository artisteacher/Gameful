<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 1/1/19
 * Time: 11:28 PM
 */

function go_clone_post_new_menu_bar(){
    //add nonce check here
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_admin_remove_notification_' . get_current_user_id() );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_clone_post_new_menu_bar' ) ) {
        echo "refresh";
        die( );
    }

    //if nonce checks, then do the clone
    go_clone_post_new(true, true);
    die();
}

/**
 *
 */
function go_admin_remove_notification() {
    if ( ! current_user_can( 'manage_options' ) ) {
        die( -1 );
    }

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_admin_remove_notification_' . get_current_user_id() );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_admin_remove_notification' ) ) {
        echo "refresh";
        die( );
    }

    update_option( 'go_display_admin_explanation', false );

    die( );
}


//Get the time on task from two times as timestamps
//or as one variable passed as a number of seconds
/**
 * @param $current_time
 * @param bool $TIMESTAMP
 * @return string
 */
function go_time_on_task($current_time, $TIMESTAMP =false){
    if ($TIMESTAMP != false) {
        $delta_time = strtotime($current_time) - strtotime($TIMESTAMP);
        $d = 'days';
        $h = 'hours';
        $m = 'minutes';
        $s = 'seconds';
        $title = "Time On Task: ";
    }else{
        $delta_time = $current_time;
        $d = 'd';
        $h = 'h';
        $m = 'm';
        $s = 's';
        $title = "";
    }
    $days = floor( $delta_time/86400);
    $delta_time = $delta_time % 86400;
    $hours = floor($delta_time / 3600);
    $delta_time = $delta_time % 3600;
    $minutes = floor($delta_time / 60);
    $delta_time = $delta_time % 60;
    $seconds = $delta_time;




    //$time_on_task = "{$days} days {$hours} hours and {$minutes} minutes and {$seconds} seconds";
    $time_on_task = "";
    if ($days>0){
        $time_on_task .= "{$days}{$d} ";
    }
    if ($hours>0){
        $time_on_task .= "{$hours}{$h} ";
    }
    if ($minutes>0){
        $time_on_task .= "{$minutes}{$m} ";
    }
    if ($seconds>0){
        $time_on_task .= "{$seconds}{$s}";
    }
    $result ="";
    $time = date("m/d/y g:i A", strtotime($TIMESTAMP));
    if ($TIMESTAMP != false) {
        $result .= "<div style='text-align:right;'>Time Submitted: " . $time . "</div>";
    }
    $result .= "<div style='text-align:right;'>". $title .$time_on_task . "</div></div>";
    return $result;
}

/**
 *
 */
function go_make_cpt_select2_ajax() {
    // we will pass post IDs and titles to this array
    $return = array();

    // you can use WP_Query, query_posts() or get_posts() here - it doesn't matter
    $search_results = new WP_Query( array(
        's'=> $_GET['q'], // the search query
        'post_type'=> $_GET['cpt'], // the search query
        //'post_status' => 'publish', // if you don't want drafts to be returned
        //'ignore_sticky_posts' => 1,
        //'posts_per_page' => 50 // how much to show at once\

    ) );
    if( $search_results->have_posts() ) :
        while( $search_results->have_posts() ) : $search_results->the_post();
            // shorten the title a little
            $title = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;
            $return[] = array( $search_results->post->ID, $title ); // array( Post ID, Post Title )
        endwhile;
    endif;
    echo json_encode( $return );
    die;

}

//this is called by the custom ACF acf-level2-taxonomy
//it is also used by the function for creating Select2 dropdowns
function go_make_taxonomy_dropdown_ajax(){
    // we will pass post IDs and titles to this array
    $return = array();

    $results = array();

    $is_acf = (isset($_GET['taxonomy2acf']) ?  $_GET['taxonomy2acf'] : false);

    if($is_acf){

        $field = acf_get_field( $_GET['field_key']);
        if( !$field ) return false;
        // bail early if taxonomy does not exist
        if( !taxonomy_exists($field['taxonomy']) ) return false;
        $taxonomy = $field['taxonomy'];

    }
    else {


        $taxonomy = $_GET['taxonomy']; // taxonomy
    }
    //$parents_only = $_GET['parents_only']; // is it hierarchical

    $is_hier = is_taxonomy_hierarchical( $taxonomy );
    $parents_only = $_GET['parents_only']; // is it hierarchical
    if($parents_only === 'true'){
        $is_hier = false;
    }
    ////////////////
    if ($is_hier === true || $is_hier === "true") {
        //$args = array('hide_empty' => false, 'orderby' => 'order', 'order' => 'ASC', 'parent' => '0');

        //parent terms
       // $parents = get_terms($taxonomy, $args);
        $parents = go_get_terms_ordered($taxonomy, '0');

        foreach ( $parents as $parent ) {
            $title = ( mb_strlen( $parent->name ) > 50 ) ? mb_substr( $parent->name, 0, 49 ) . '...' : $parent->name;
            //$return[] = array( $parent->term_id, $title, true ); // array( Post ID, Post Title )

            //$args = array('hide_empty' => false, 'orderby' => 'order', 'order' => 'ASC', 'parent' => $parent->term_id);
            $parent = $parent->term_id;
            //children terms
            //$children = get_terms($taxonomy, $args);
            $children = go_get_terms_ordered($taxonomy, $parent);
            if (!empty($children)){
                $return[] = array( $parent->term_id, $title, true ); // array( Post ID, Post Title )

            }
            foreach ( $children as $child ) {
                $title = ( mb_strlen( $child->name ) > 50 ) ? mb_substr( $child->name, 0, 49 ) . '...' : $child->name;
                $return[] = array( $child->term_id, $title, false ); // array( Post ID, Post Title )
            }
        }
        $terms = $return;
        $i = -1;
        $c = 0;
        foreach ($terms as $term){
            if ($term[2] == true){
                $i++;
                $results[$i]['text'] = $term[1];
                $c = 0;
            }
            else {
                $results[$i]['children'][$c]['id'] = $term[0];
                $results[$i]['children'][$c]['text'] = $term[1];
                $c++;
            }
        }
    }else{
        //$args = array('hide_empty' => false, 'orderby' => 'order', 'order' => 'ASC');
        //children terms
        //$children = get_terms($taxonomy, $args);
        if($parents_only === 'true'){
            $children = go_get_terms_ordered($taxonomy, '0');
            $title = "Top Level Only";
            $results[] = array(
                'id' => '0',
                'text' => $title ); // array( Post ID, Post Title )
        }else {
            $children = go_get_terms_ordered($taxonomy, '');
        }
        foreach ( $children as $child ) {

            $title = ( mb_strlen( $child->name ) > 50 ) ? mb_substr( $child->name, 0, 49 ) . '...' : $child->name;
            $results[] = array(
                'id' => $child->term_id,
                'text' => $title ); // array( Post ID, Post Title )
        }

    }


    /*
    ////////////////////////
    $args = array(
        'hide_empty' => false,
        'orderby' => 'order',
        'order' => 'ASC',
        'search'=> $_GET['q'], // the search query)
        'posts_per_page' => 50, // how much to show at once\
    );

    $search_results = get_terms($taxonomy, $args);

    if( count($search_results) > 0 ){
        foreach ($search_results as $search_result){
            $title = ( mb_strlen( $search_result->name ) > 50 ) ? mb_substr( $search_result->name, 0, 49 ) . '...' : $search_result->name;
            //$return[] = array( $search_result->term_id, $title ); // array( Post ID, Post Title )
        }
    }
    */


    echo json_encode( $results );
    die;
}

