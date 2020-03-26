<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-06-02
 * Time: 15:16
 */


function go_archive_tool_page(){
    /* add a new menu item */
    add_submenu_page(
        null,  // parent slug
        'Create User Blog Archive', // page_title
        'Create User Blog Archive', // menu_title
        'manage_options', // capability
        'tool_blog_archive', // menu_slug
        'go_create_user_blog_archive' //callable
    );
}
add_action( 'admin_menu', 'go_archive_tool_page');

add_action( 'go_user_management_card' ,'go_archive_tool_card');
function go_archive_tool_card(){
    ?>
    <div class="card">
        <h2>Archive and Reset</h2>
        <p>Create an archive of blog posts for selected users.  This is useful for record keeping if you are going to delete users from the site at the end of a course.
            You can also remove all user content with this tool.</p>
        <a href="<?php  menu_page_url( 'tool_blog_archive', true ); ?>"><button id="go_blog_archive_button">View Tool</button></a>
    </div>
    <?php
}