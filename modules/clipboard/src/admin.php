<?php


/**
 * Prints menu and container for clipboard
 */
function go_clipboard_menu() {
    //acf_form_head();

    if ( ! go_user_is_admin() ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    } else {
        $task_name = get_option( 'options_go_tasks_name_plural'  );
        //go_clipboard_filters();
        go_leaderboard_filters($type = 'clipboard')
        ?>


        <div id="records_tabs" style="clear: both; margin-left: -9999px; margin-right: 20px;">
            <ul>
                <li class="clipboard_tabs" tab="clipboard"><a href="#clipboard_wrap">Stats</a></li>
                <li class="clipboard_tabs" tab="store"><a href="#clipboard_store_wrap">Store</a></li>

                <li class="clipboard_tabs" tab="messages"><a href="#clipboard_messages_wrap">Messages</a></li>

                <li class="clipboard_tabs" tab="activity"><a href="#clipboard_activity_wrap"><?php echo $task_name; ?></a></li>

                <li class="clipboard_tabs" tab="attendance"><a href="#clipboard_attendance_wrap">Attendance</a></li>
            </ul>
            <div id="clipboard_wrap">
                <div id="clipboard_stats_datatable_container"></div>
            </div>

            <div id="clipboard_store_wrap">
                <div id="clipboard_store_datatable_container"></div>
            </div>

            <div id="clipboard_messages_wrap">
                <div id="clipboard_messages_datatable_container"></div>
            </div>

            <div id="clipboard_activity_wrap">
                <div id="clipboard_activity_datatable_container"></div>
            </div>

            <div id="clipboard_attendance_wrap">
                <div id="clipboard_attendance_datatable_container"></div>
            </div>
        </div>
        <?php
        //go_hidden_footer();
    }
}



