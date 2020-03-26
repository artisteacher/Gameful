<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 12/22/18
 * Time: 5:35 AM
 */

//conditional includes

if ( !is_admin() ) {

    include_once('src/public_ajax/go_shortcodes.php');
    include_once('src/public_ajax/go_locks.php');
    include_once('src/public_ajax/go_checks.php');
}else if ( defined( 'DOING_AJAX' )) {
    include_once('src/public_ajax/go_shortcodes.php');
    include_once('src/public_ajax/go_locks.php');
    include_once('src/public_ajax/go_checks.php');
    include_once('src/ajax/ajax.php');
    add_action( 'wp_ajax_go_user_profile_link', 'go_user_profile_link' );
    add_action( 'wp_ajax_go_admin_remove_notification', 'go_admin_remove_notification' ); //OK
    add_action( 'wp_ajax_go_update_bonus_loot', 'go_update_bonus_loot' );//OK
    add_action( 'wp_ajax_go_clone_post_new_menu_bar', 'go_clone_post_new_menu_bar' );//OK
    add_action( 'wp_ajax_go_make_cpt_select2_ajax', 'go_make_cpt_select2_ajax' );
    add_action( 'wp_ajax_go_make_taxonomy_dropdown_ajax', 'go_make_taxonomy_dropdown_ajax' );
    add_action( 'wp_ajax_go_attendance_check_ajax', 'go_attendance_check_ajax' );

}else{
    include_once('src/admin/go_datatable.php');
    include_once('src/admin/go_activation.php');
    include_once('src/admin/go_admin.php');
}

//always include
include_once('src/all/go_links.php');
include_once('src/all/go_media.php');
include_once('src/all/go_multisite.php');
include_once('src/all/go_transients.php');
include_once('src/all/go_mce.php');
include_once('src/all/go_loot_and_updates.php');
include_once('src/all/go_users.php');
include_once('src/all/go_core_functions.php');
include_once('src/all/go-acf-functions.php');
include_once('src/all/attendance.php');

/**
 * This places the mce in in hidden footer. Loads all of the scripts and styles that allow mce to be loaded later.
 */
add_action('admin_footer','go_hidden_footer');
add_action('wp_footer','go_hidden_footer');
function go_hidden_footer(){

    ?>
<div style="display: none;">
    <?php

    $settings = array(//'tinymce'=> array( 'menubar'=> true, 'toolbar1' => 'undo,redo', 'toolbar2' => ''),
        'tinymce'=>true,
        'media_buttons' => true,
        'menubar' => true,
        'drag_drop_upload' => true);
    //wp_editor('', 'initialize', $settings);
    wp_editor('', 'initialize');
    ?>
</div>
<?php

}

