<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 1/11/19
 * Time: 1:13 AM
 */

//this downloads the xml file
add_action('init', 'go_download_game_data');
function go_download_game_data()
{
    if (isset($_GET['download'])) {
        $pass = get_option('go_export_password');
        if($pass == $_GET['password']) {
            go_export_wp2();
            die();
        }else{
            echo "Invalid link password.  Contact the exporting site for new link.";
            die();
        }
    }
}



function go_admin_tools_menu_content() {
    global $go_js_version;

    ?>

    <div id="go_tools_wrapper" class="wrap">

        <h2>Tools</h2>
    <?php

    //This should run automatically if needed.
    /*
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
    }*/

    /*
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
*/

    $pass = get_option('go_export_password');
    if(empty($pass)){
        $pass = wp_generate_password(8, false);
        update_option('go_export_password', $pass);
    }
    $export_url = home_url('wp-admin/admin.php?page=game-tools&download=true&password='.$pass);
    //<button id="go_export_game" onclick="window.location.href = 'wp-admin/admin.php?page=game-tools&download=true';">Export All Game Data</button>

            ?>
        <h2>Import and Export Tools</h2>
        <div class="go_tools_section">
            <div class="card">
                <h2>Export Game Data</h2>
                <p>You can export Tasks, Maps, Store Items and Categories, Badges, Groups, and Sections.  Does not include student blogs.</p>
                <p>The other site can retrive your export file from the following URL. Enter the link in the importer tool on the other site.</p>
                <div id="go_export_pass"><?php echo $export_url ;?></div>
                <button onclick="go_CopyToClipboard('go_export_pass')">Copy text</button>
                <script>
                    function go_CopyToClipboard(containerid) {
                        if (document.selection) {
                            var range = document.body.createTextRange();
                            range.moveToElementText(document.getElementById(containerid));
                            range.select().createTextRange();
                            document.execCommand("copy");

                        } else if (window.getSelection) {
                            var range = document.createRange();
                            range.selectNode(document.getElementById(containerid));
                            window.getSelection().addRange(range);
                            document.execCommand("copy");
                            jQuery('#'+containerid).animate({ 'zoom': 1.1 }, 200).animate({ 'zoom': 1 }, 200);
                            //alert("text copied")
                        }}
                </script>

            </div>
            <div class="card">
                <h2>Import Game Data</h2>
                <p>You will need the export URL from the site you wish to import from.</p>
                <button id="go_import_game" onclick="window.location.href = 'wp-admin/admin.php?import=gameful';">Import Game Data</button>

            </div>
        </div>



        <h2>User Management</h2>
        <div class="go_tools_section">
            <div class="card">
                <h2>Reset All User Data</h2>
                <p>Reset tasks, history, and loot for all users. Blog posts and media will remain.</p>
                <button id="go_reset_all_users">Reset All Users</button>
            </div>
            <?php
            do_action('go_user_management_card');//loads other user management cards from modules, i.e., archive
            ?>
        </div>


    </div>



    <?php
    if(is_super_admin()){
        ?>
        <h2>Super Admin Tools</h2>
        <div class="go_tools_section">
            <div class="card">
                <h2>Flush permalinks on all sites</h2>
                <p>This is a maintenance task. Only run if needed--it's expensive.</p>
                <button id="go_reset_all_users">Flush All Permalinks</button>
            </div>
            <?php
            do_action('go_flush_all_permalinks');
            ?>
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

function go_update_go_to_v5(){
    /*
     * Not run from ajax anymore--just runs on activation if needed.
        if ( !is_user_logged_in() ) {
            echo "login";
            die();
        }

        //check_ajax_referer( 'go_upgade4' );
        if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_update_go_ajax_v5' ) ) {
            echo "refresh";
            die( );
        }

    */

    $query = new WP_Query(array(
        'post_type' => 'tasks',
        'posts_per_page' => 10000
    ));


    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();

        //get number of stages
        $stage_count = get_post_meta($post_id, 'go_stages', true);
        //UPDATE ALL STAGES
        for ($i = 0; $i <= $stage_count; $i++) {
            //add a uniqueID to the stage
            update_post_meta($post_id, 'go_stages_' . $i . '_uniqueid', $post_id . "_v4_stage" . $i);

            //this is used to set the required elements on the new repeater field
            $element_count = 0;

            //move old elements over
            $title = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_title', true);
            update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_title', $title);

            $private = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_private', true);
            update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_private', $private);

            $text = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_blog_text_toggle', true);
            update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_text_toggle', $text);

            $min = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_blog_text_minimum_length', true);
            update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_text_minimum_length', $min);

            $check_type = get_post_meta($post_id, 'go_stages_' . $i . '_check', true);

            if ($check_type == 'none' || $check_type == 'quiz' || $check_type == 'password'){
                update_post_meta($post_id, 'go_stages_' . $i . '_check_v5', $check_type);
            }else{
                update_post_meta($post_id, 'go_stages_' . $i . '_check_v5', 'blog');
            }

            //update the required elements, if the old check was a blog
            if ($check_type == 'blog') {
                //if a URL was required, add it as a new required element
                $url = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_url_toggle', true);
                if ($url) {
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_element', 'URL');
                    $validate = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_url_url_validation', true);
                    if (!empty($validate)) {
                        update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_requirements_url_validation', $validate);
                    }
                    // add a uniqueID
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_url");
                    $element_count++;
                }

                //if a file upload was required, add it as a new required element
                $file = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_attach_file_toggle', true);
                if ($file) {
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_element', 'File');
                    $restrict = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_attach_file_restrict_file_types', true);
                    if ($restrict) {
                        $types = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_attach_file_allowed_types', true);
                        update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_requirements_allowed_types', $types);
                    }
                    // add a uniqueID
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_file");
                    $element_count++;
                }

                //if video was required, add it as a new required element
                $video = get_post_meta($post_id, 'go_stages_' . $i . '_blog_options_video', true);
                if ($video) {
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_element', 'Video');
                    // add a uniqueID
                    update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_video");
                    $element_count++;
                }
                //add the count of the required elements. These are the repeater rows.
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements', $element_count);
            }
            //update if it was a file upload
            //add file as the only required element on the blog
            else if($check_type=='upload'){
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_0_element', 'File');
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements', 1);
                //update_post_meta($post_id, 'go_stages_' . $i . '_check', 'blog');
                // add a uniqueID
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_file");

            }
            //update if it was a URL
            //add URL as the only required element on the blog
            else if($check_type=='URL'){
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_0_element', 'URL');
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements', 1);
                //update_post_meta($post_id, 'go_stages_' . $i . '_check', 'blog');
                // add a uniqueID
                update_post_meta($post_id, 'go_stages_' . $i . '_blog_options_v5_blog_elements_' . $element_count . '_uniqueid', $post_id."_v4_s".$i."_url");

            }
        }//END STAGE UPDATE

        //BONUS STAGE UPDATE
        $bonus_element_count = 0;
        $check_type = get_post_meta($post_id, 'go_bonus_stage_check', true);
        if ($check_type == 'none' || $check_type == 'quiz' || $check_type == 'password'){
            update_post_meta($post_id, 'go_bonus_stage_check_v5', $check_type);
        }else{
            update_post_meta($post_id, 'go_bonus_stage_check_v5', 'blog');
        }

        if ($check_type == 'blog') {
            $private = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_private', true);
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_private', $private);

            $text = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_blog_text_toggle', true);
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_text_toggle', $text);

            $min = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_blog_text_minimum_length', true);
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_text_minimum_length', $min);


            //if URL was required, add it as a new required element
            $url = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_url_toggle', true);
            if ($url) {
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_element', 'URL');
                $validate = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_url_url_validation', true);
                if (!empty($validate)) {
                    update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_requirements_url_validation', $validate);
                }
                $bonus_element_count++;
                // add a uniqueID
                update_post_meta($post_id, 'go_bonus_stage__blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_url");

            }

            //if file was required, add it as a new required element
            $file = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_attach_file_toggle', true);
            if ($file) {
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_element', 'File');
                $restrict = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_attach_file_restrict_file_types', true);
                if ($restrict) {
                    $types = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_attach_file_allowed_types', true);
                    update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_requirements_allowed_types', $types);
                }
                $bonus_element_count++;
                // add a uniqueID
                update_post_meta($post_id, 'go_bonus_stage__blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_file");
            }

            //if video was required, add it as a new required element
            $video = get_post_meta($post_id, 'go_bonus_stage_blog_options_bonus_video', true);
            if ($video) {
                update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_element', 'Video');
                $bonus_element_count++;
                // add a uniqueID
                update_post_meta($post_id, 'go_bonus_stage__blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_video");
            }

            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements', $bonus_element_count);

        }
        //update if it was a file upload on bonus.
        //add File as the only required element on the blog
        else if($check_type=='upload'){
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_0_element', 'File');
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements', 1);
            //update_post_meta($post_id, 'go_bonus_stage_check', 'blog');
            // add a uniqueID
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_file");
        }
        //update if it was a URL on bonus
        //add URL as the only required element on the blog
        else if($check_type=='URL'){
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_0_element', 'URL');
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements', 1);
            //update_post_meta($post_id, 'go_bonus_stage_check', 'blog');
            // add a uniqueID
            update_post_meta($post_id, 'go_bonus_stage_blog_options_v5_blog_elements_' . $bonus_element_count . '_uniqueid', $post_id."_v4_bonus_url");
        }
        $key = 'go_post_data_' . $post_id;
        delete_transient($key);
    }
    wp_reset_query();

}

