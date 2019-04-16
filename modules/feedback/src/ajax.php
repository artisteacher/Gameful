<?php


function go_filter_reader(){
    //check_ajax_referer( 'go_filter_reader' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_filter_reader' ) ) {
        echo "refresh";
        die( );
    }
    go_reader_get_posts();
    die();
}

function go_reader_bulk_read(){
    //check_ajax_referer( 'go_reader_bulk_read' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_reader_bulk_read' ) ) {
        echo "refresh";
        die( );
    }
    $task_ids = (isset($_POST['post_ids']) ?  $_POST['post_ids'] : '');
    //$task_ids = json_decode($task_ids);

    foreach($task_ids as $task_id){
        $status = get_post_status($task_id);
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





