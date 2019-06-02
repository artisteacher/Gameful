<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 1/11/19
 * Time: 1:13 AM
 */



function go_admin_tools_menu_content() {
    global $go_js_version;

    ?>

    <div id="go_tools_wrapper" class="wrap">

        <h2>Updates</h2>
    <?php
    if($go_js_version >= 4) {
        ?>

        <div class="go_tools_section">
            <div class="card">
                <h2>Update v4 to v5</h2>
                <p>This will update your v4 tasks to v5. The v4 content will be left unchanged and new post metadata
                    will be created.</p>
                <button id="go_tool_update_v5">Update</button>
            </div>
        </div>
        <?php
    }
        if($go_js_version < 5) {
            ?>
            <div class="go_tools_section">
                <div class="card">
                    <h2>Update v3 to v4</h2>
                    <p>This will update your v3 posts and store items to v4. It's not perfect, but it's better then
                        starting from scratch. The v3 content will be left unchanged and new post metadata will be
                        created.</p>
                    <button id="go_tool_update">Update</button>
                </div>
                <div class="card">
                    <h2>Update v3 to v4--but don't update the quest loot.</h2>
                    <p>This is just like the other v3 upgrade tool, but it does not copy any of the rewards. This is if
                        you want all your old quests for reference, but don't want them playable for rewards. </p>
                    <button id="go_tool_update_no_loot">Update-No Loot</button>
                </div>
            </div>
            <?php
        }


            ?>
        <h2>User Management</h2>
        <div class="go_tools_section">
            <div class="card">
                <h2>Reset All User Data</h2>
                <p>Reset tasks, history, and loot for all users. Blog posts and media will remain.</p>
                <button id="go_reset_all_users">Reset All Users</button>
            </div>
            <div class="card">
                <h2>User Blog Archive</h2>
                <p>Create an archive of blog posts for selected users.  This is useful for record keeping if you are going to delete users from the site at the end of a course.</p>
                <a href="<?php  menu_page_url( 'tool_blog_archive', true ); ?>"><button id="go_blog_archive_button">Create Archive</button></a>
            </div>
        </div>
        <div class="go_tools_section">
            <div class="">
                <h2>More Tools Coming Soon!</h2>
                <p>Export/Import Tool</p>
            </div
        </div>

    </div>



    <?php

}


function go_create_user_blog_archive(){

    //add a table with a button to create archive here
    echo "<h2>Create Archive</h2><p>Select Users to create an archive of their blogs. This can be done anytime, but is typically done at the end of a course before deleting users from the site.</p>";

    go_clipboard_filters();
    ?>
    <div id="records_tabs" style="clear: both; margin-right: 20px;">
        <ul>
            <li class="clipboard_tabs" tab="clipboard"><a href="#clipboard_wrap">Select Users to Archive</a></li>
        </ul>
        <div id="clipboard_wrap">
            <div id="clipboard_stats_datatable_container"></div>
        </div>

    </div>

<?php
}
