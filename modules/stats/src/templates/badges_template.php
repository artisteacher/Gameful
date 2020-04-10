<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/31/18
 * Time: 12:25 PM
 */

$is_admin = go_user_is_admin();
global $wp_query;
$title = (isset($wp_query->query['pagename']) ? $wp_query->query['pagename'] : false); //Check for query

function go_badges_title() {
    global $wp_query;
    $title = (isset($wp_query->query['pagename']) ? $wp_query->query['pagename'] : false); //Check for query
    $badges_name = "edit_" . lcfirst(get_option('options_go_badges_name_plural'));
    $groups_name = "edit_" . lcfirst(get_option('options_go_groups_name_plural'));
    if($title === ($badges_name)) {
        $page_value  = ucwords(get_option('options_go_badges_name_plural'));
    }else if ($title === ($groups_name)){
        $page_value  =  ucwords(get_option('options_go_groups_name_plural'));
    }

    //$title = get_option('options_go_badges_name_plural');
    return ucwords($page_value); // add dynamic content to this title (if needed)
}
add_action( 'pre_get_document_title', 'go_badges_title' );

if($is_admin){
    acf_form_head();
}
get_header();
if($is_admin){
    $badges_name = ucwords(get_option('options_go_badges_name_plural'));
    $groups_name = ucwords(get_option('options_go_groups_name_plural'));
    $badges_title = "edit_" . lcfirst($badges_name);
    $groups_title = "edit_" . lcfirst($groups_name);
    if($title === ($badges_title)) {
        $taxonomy = 'go_badges';
        $title = $badges_name;
    }else if ($title === ($groups_title)){
        $taxonomy = 'user_go_groups';
        $title = $groups_name;
    }

    echo "<br><h2>Edit ". ucwords($title) ."</h2>";
    if(go_user_is_admin()){
        echo "<span class='go_map_action_icons ' style='right: 40px; z-index: 100; font-size: 1.3em;'>";

        echo "<div class='my_tooltip'><div class='go_actions_wrapper_flex'>";
        echo "<span class='tools task_tools'>";

        echo "<span class='go_edit_frontend_badge action_icon' data-new_parent_term='true'  data-taxonomy='$taxonomy' data-frontend_edit='true'><a><i class='far fa-plus-circle'></i></a></span>";


        echo "<span class='action_icon tooltip_toggle tooltip' onclick='go_disable_tooltips();' data-tippy-content='Toggle the admin actions tooltips on or off. Useful when doing demos or if they annoy you.'>";
        echo "<a><i class='active far fa-comment-alt-dots'></i><i style='display: none;' class='inactive far fa-comment-alt-times'></i></a>";
        echo "</span>";



        echo "</span></div></div></span>";
    }


    if($taxonomy === 'go_badges') {
        echo "<div id='stats_badges_page' class='sortable stats_badges go_page_container' data-taxonomy='$taxonomy'>";
        go_stats_badges_list(true, 'edit');
        echo "</div>";
    }else if ($taxonomy === 'user_go_groups'){
        echo "<div id='stats_groups_page' class='sortable stats_groups go_page_container' data-taxonomy='$taxonomy'>";
        go_stats_groups_list(true, 'edit');
        echo "</div>";
    }
}



get_footer();