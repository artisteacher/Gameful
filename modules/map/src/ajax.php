<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 10/13/18
 * Time: 10:00 PM
 */

/**
 * @param bool $map_id
 */
function go_update_last_map() {
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_update_last_map' ) ) {
        echo "There was an error.  Please refresh the page.";
        die( );
    }

    if(empty($_POST) || !isset($_POST)) {
        ajaxStatus('error', 'Nothing to update.');
    } else {
        try {
            $taxonomy = $_POST['taxonomy'];
            $map_id = $_POST['goLastMap'];
            $user_id = get_current_user_id();

            if($taxonomy === 'store_types'){
                update_user_option( $user_id, 'go_last_store', $map_id );
            }
            else{
                update_user_option( $user_id, 'go_last_map', $map_id );
            }


            go_make_single_map(true, $map_id, true, $taxonomy);

            die();
        } catch (Exception $e){
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
}


/**
 *
 */
function go_to_this_map(){
    //check_ajax_referer( 'go_to_this_map');
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_to_this_map' ) ) {
        echo "There was an error.  Please refresh the page.";
        die( );
    }
    $user_id = get_current_user_id();
    $map_id = $_POST['map_id'];
    update_user_option( $user_id, 'go_last_map', $map_id );

    $map_url = get_option('options_go_locations_map_map_link');
    $map_url = (string) $map_url;
    $go_map_link = get_permalink( get_page_by_path($map_url) );
    echo $go_map_link;
    die;
}

function go_user_map_ajax(){
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_user_map_ajax');
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_user_map_ajax' ) ) {
        echo "There was an error.  Please refresh the page.";
        die( );
    }

    echo "<div class='go_user_map_wrapper'>";
    go_make_map('task_chains', false);
    echo "</div>";
    die();
}