<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 1/1/19
 * Time: 11:25 PM
 */

function go_update_admin_view (){
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_update_admin_view'  );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_update_admin_view' ) ) {
        echo "refresh";
        die( );
    }

    if(empty($_POST) || !isset($_POST)) {
        ajaxStatus('error', 'Nothing to update.');
    } else {
        try {
            //$user_id = ( ! empty( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0 ); // User id posted from ajax function

            $go_admin_view = $_POST['go_admin_view'];
            //check_ajax_referer('go_update_admin_view', 'security' );
            $user_id = wp_get_current_user();
            $user_id = get_current_user_id();
            update_user_option( $user_id, 'go_admin_view', $go_admin_view );
            die();
        } catch (Exception $e){
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }

}


function go_new_task_from_template(){
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }
    $task_name = get_option('options_go_tasks_name_singular');
    $templates = get_posts([
        'post_type' => 'tasks_templates',
        'post_status' => 'any',
        'numberposts' => -1
        // 'order'    => 'ASC'
    ]);
    if ($templates) {
        //create a select dropdown
        echo '<h3>Choose a Template:</h3><select class="go_new_task_from_template" name="new_task">';
        echo '<option value="0">New Empty '.$task_name.'</option>';
        foreach ($templates as $template){
            $post_id = $template->ID;
            $title = $template->post_title;
            echo '<option value="' .$post_id.'">' .$title.'</option>';
        }
        echo '</select>';
        echo '<br><button class="submit-button button go_new_task_from_template_button" type="submit" style="float: right;">Create '.$task_name.'</button>';

       // $url_new_task = get_admin_url(null, 'post-new.php?post_type=tasks');
       // echo '<br><br>-or-<br><br><p style="float:right;"><a href="'. $url_new_task .'">Create New Empty '.$task_name.'</a></p>';
    }




    die();
}