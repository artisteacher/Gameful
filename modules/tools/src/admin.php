<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 1/11/19
 * Time: 1:13 AM
 */

function go_admin_tools_menu_content() {

    ?>

    <div id="go_tools_wrapper" class="wrap">

        <h2>Updates</h2>
        <div class="go_tools_section">
            <div class="card">
                <h2>Update v4 to v5</h2>
                <p>This will update your v4 tasks to v5. The v4 content will be left unchanged and new post metadata will be created.</p>
                <button id="go_tool_update_v5">Update</button>
            </div>
        </div>

        <div class="go_tools_section">
            <div class="card">
                <h2>Update v3 to v4</h2>
                <p>This will update your v3 posts and store items to v4. It's not perfect, but it's better then starting from scratch. The v3 content will be left unchanged and new post metadata will be created.</p>
                <button id="go_tool_update">Update</button>
            </div>
            <div class="card">
                <h2>Update v3 to v4--but don't update the quest loot.</h2>
                <p>This is just like the other v3 upgrade tool, but it does not copy any of the rewards. This is if you want all your old quests for reference, but don't want them playable for rewards. </p>
                <button id="go_tool_update_no_loot">Update-No Loot</button>
            </div>
        </div>

        <h2>User Management</h2>
        <div class="go_tools_section">
            <div class="card">
                <h2>Reset All User Data</h2>
                <p>Reset tasks, history, and loot for all users. Blog posts and media will remain.</p>
                <button id="go_reset_all_users">Reset All Users</button>
            </div>
        </div>
        <div class="go_tools_section">
            <div class="">
                <h2>More Tools Coming Soon!</h2>
                <p>Export/Import Tasks Tool</p>
                <p>Archive</p>
            </div
        </div>

    </div>



    <?php

}
