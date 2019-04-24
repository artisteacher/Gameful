<?php


function go_filter_reader(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_filter_reader' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_filter_reader' ) ) {
        echo "refresh";
        die( );
    }
    go_reader_get_posts();
    die();
}

function go_reader_bulk_read(){

    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_reader_bulk_read' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_reader_bulk_read' ) ) {
        echo "refresh";
        die( );
    }

    global $wpdb;
    $sQuery1 = "SELECT SQL_CALC_FOUND_ROWS
            t4.ID, t4.post_status";

    $where = (isset($_POST['where']) ? $_POST['where'] : null);
    $pWhere = stripslashes($where);
    $order = (isset($_POST['order']) ? $_POST['order'] : null);

    $squery = (isset($_POST['query']) ? $_POST['query'] : null);
    $sQuery2 = stripslashes($squery);

    $sQuery = $sQuery1 . $sQuery2 . $pWhere . " ORDER BY t4.post_modified " . $order ;


    $posts = $wpdb->get_results($sQuery, ARRAY_A);
    //$task_ids = array_column($posts, 'ID');
    //$task_ids = json_decode($task_ids);

    foreach($posts as $post){
        //$status = get_post_status($task_id);
        $task_id = $post['ID'];
        $status = $post['post_status'];
        if($status == 'unread') {
            $query = array(
                'ID' => $task_id,
                'post_status' => 'read',
            );
            wp_update_post($query, true);
        }
    }
    return ("Posts were marked as read.");
    die();
}

function go_num_posts()
{

    if (!is_user_logged_in()) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_reader_bulk_read' );
    //if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_reader_bulk_read')) {
       // echo "refresh";
       // die();
    //}
    $where = (isset($_POST['where']) ? $_POST['where'] : null);
    $where = stripslashes($where);
    $order = (isset($_POST['order']) ? $_POST['order'] : null);

    $squery = (isset($_POST['query']) ? $_POST['query'] : null);
    $sQuery = stripslashes($squery);

    //$tQuery = (isset($_POST['tQuery']) ? $_POST['tQuery'] : null);
    //$tQuery = stripslashes($tQuery);



    go_reader_get_posts($sQuery, $where, $order);


    //echo "Posts were marked as read.";
    die();
}





