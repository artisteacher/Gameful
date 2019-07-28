<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-06-16
 * Time: 21:11
 */


//conditional includes
if ( !is_admin() ) {
   /*global $pagenow;
    if ( 'xxx.php' == $pagenow) {
        include_once('public/public.php');
        include_once('src/public_ajax.php');
    }*/
}else if ( defined( 'DOING_AJAX' )) {
    $ajax_action  = (isset($_REQUEST['action']) ?  $_REQUEST['action'] : null);
    $actions = array("reordering_terms", "check_if_parent_term", "go_make_taxonomy_dropdown_ajax", "check_if_parent_term");
    if(in_array($ajax_action, $actions)) {
        include_once('src/wp-term-order.php');
        //include_once('src/ajax.php');
        //add_action( 'wp_ajax_xxx', 'xxx' ); //OK
    }
    /*else{
        $debug_this = 'stop';//put a breakpoint here to debug
    }*/

}else{
    $page_uri = go_get_page_uri();
    //global $pagenow;
    if ( 'wp-adminedit-tags.php' == $page_uri) {
        //include_once('src/admin.php');
        include_once('src/wp-term-order.php');
    }
}

//always include
//include_once('src/functions.php');

