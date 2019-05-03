<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-03-30
 * Time: 18:39
 */

function go_make_reader() {
    wp_localize_script( 'go_frontend', 'IsReader', 'true' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    //echo "success";
    go_reader_header();

    //video options
    $go_lightbox_switch = get_option( 'options_go_video_lightbox' );
    $go_video_unit = get_option ('options_go_video_width_unit');
    if ($go_video_unit == 'px'){
        $go_fitvids_maxwidth = get_option('options_go_video_width_pixels')."px";
    }
    if ($go_video_unit == '%'){
        $go_fitvids_maxwidth = get_option('options_go_video_width_percent')."%";
    }
    echo "<div id='go_wrapper' data-lightbox='{$go_lightbox_switch}' data-maxwidth='{$go_fitvids_maxwidth}' >";

    echo "<div id='loader_container' style='display:none; height: 250px; width: 100%; padding: 10px 30px; '>
                <div id='loader'>
                <i class='fas fa-spinner fa-pulse fa-4x'></i>
                </div>
          </div>
           <div id='go_posts_wrapper'>";
                go_reader_get_posts();
    echo "</div></div>";
    //get_sidebar();
}
add_shortcode( 'go_make_reader','go_make_reader' );


function go_reader_header() {
    //acf_form_head();

        $task_name = get_option( 'options_go_tasks_name_plural'  );
        ?>
        <div id="go_leaderboard_filters" style="display: flex; flex-wrap: wrap;">
            <div id="go_user_filters" style="padding: 0 20px 20px 20px;">
                <h3>User Filter</h3>
                <div id="go_user_filters_1" style="">
                    <span><label for="go_clipboard_user_go_sections_select">Section </label><?php go_make_tax_select('user_go_sections' , "clipboard_"); ?></span>
                    <br><span><label for="go_clipboard_user_go_groups_select">Group </label><?php go_make_tax_select('user_go_groups', "clipboard_"); ?></span>
                    <br><span><label for="go_clipboard_go_badges_select">Badge </label><?php go_make_tax_select('go_badges', "clipboard_"); ?></span>
                    <br>
                </div>
            </div>
                <div id="go_action_filters" style="display: flex; flex-wrap: wrap ;">
                 <div id="go_action_filters_1" style="padding: 0px 20px 20px 20px;">
                     <h3>Blog Post Filters</h3>
                     <div id="go_datepicker_container" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                         <div id="go_datepicker_clipboard">
                             <i class="fa fa-calendar" style="float: left;"></i>&nbsp;
                             <span id="go_datepicker"></span> <i id="go_reset_datepicker" class=""select2-selection__clear><b> × </b></i><i class="fa fa-caret-down"></i>
                         </div>
                     </div>
                        <div style="padding-top: 10px;">
                            <span id="go_task_filters"><label for="go_clipboard_task_select"><?php echo $task_name; ?> </label><select id="go_task_select" class="js-store_data" style="width:250px;"></select></span>
                        </div>
                        <div>
                            Status<br>
                            <input type="checkbox" id="go_reader_unread" class="go_reader_input" value="unread" checked><label for="go_reader_unread">Unread </label>
                            <input type="checkbox" id="go_reader_read" class="go_reader_input" value="read"><label for="go_reader_read">Read </label>
                            <input type="checkbox" id="go_reader_reset" class="go_reader_input" value="reset"><label for="go_reader_reset">Reset </label>
                            <input type="checkbox" id="go_reader_trash" class="go_reader_input" value="trash"><label for="go_reader_trash">Trash </label>
                            <input type="checkbox" id="go_reader_draft" class="go_reader_input" value="draft"><label for="go_reader_draft">Draft </label>
                        </div>
                 </div>
    
                <div id="go_action_filters_2" style="padding: 40px 20px 20px 20px;">
                    <div>
                        Order <span class="tooltip" data-tippy-content="Posts are sorted by the last modified time."><span><i class="fa fa-info-circle"></i></span> </span><br>
                        <input type="radio" id="go_reader_order_oldest" class="go_reader_input" name="go_reader_order" value="ASC" checked><label for="go_reader_order_oldest"> Oldest First</label><br>
                        <input type="radio" id="go_reader_order_newest" class="go_reader_input" name="go_reader_order" value="DESC"><label for="go_reader_order_newest"> Newest First</label>
                    </div>
                    <br>

                </div>
            </div>
            <div id="go_leaderboard_update_button" style="padding:20px; display: flex; justify-content: flex-end; width: 100%;">
                <div style="margin-right: 30px; float:left;"><button class="go_reset_clipboard dt-button ui-button ui-state-default ui-button-text-only buttons-collection"><span class="ui-button-text">Clear Filters <i class="fa fa-undo" aria-hidden="true"></i></span></button></div>
                <div style="margin-right: 60px;"><button class="go_update_clipboard dt-button ui-button ui-state-default ui-button-text-only buttons-collection"><span class="ui-button-text">Refresh Data <i class="fa fa-refresh" aria-hidden="true"></i></span></button></div>
            </div>
            
        </div>


        <?php




}





?>
