<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 12/22/18
 * Time: 5:35 AM
 */
//this all is only needs to be loaded on clipboard
//conditional includes
if ( !is_admin() ) {
    include_once('src/public_ajax.php');

}else if ( defined( 'DOING_AJAX' )) {
    include_once('src/ajax.php');
    include_once('src/public_ajax.php');

    add_action( 'wp_ajax_go_make_user_archive_zip', 'go_make_user_archive_zip' ); //OK
    add_action( 'wp_ajax_go_delete_temp_archive', 'go_delete_temp_archive' ); //OK

}else{
    //include_once('admin/admin.php');
}

//always include
include_once('src/functions.php');

