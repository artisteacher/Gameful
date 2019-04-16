<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 12/22/18
 * Time: 5:35 AM
 */

//conditional includes
if ( !is_admin() ) {
    include_once('src/public_ajax.php');
}else if ( defined( 'DOING_AJAX' )) {
    include_once('src/ajax.php');
    include_once('src/public_ajax.php');
    add_action( 'wp_ajax_go_check_quiz_answers', 'go_check_quiz_answers' );
    add_action( 'wp_ajax_go_save_quiz_result', 'go_save_quiz_result' );//OK


}else{
   // include_once('admin/admin.php');
}

//always include
//include_once('functions.php');