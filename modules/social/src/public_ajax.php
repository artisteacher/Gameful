<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-05-12
 * Time: 20:13
 */

/*
function go_make_leaderboard_filter(){
    $current_id = get_current_user_id();

    //set the last searched leaderboard as an user option
    if(is_user_member_of_blog() || go_user_is_admin()) {
        $return = array();
        $taxonomy = $_POST['taxonomy'];
        $term_id = intval(get_user_option('go_leaderboard_' . $taxonomy, $current_id));
        $obj = get_term($term_id);
        $term_name = (mb_strlen($obj->name) > 50) ? mb_substr($obj->name, 0, 49) . '...' : $obj->name;
        //$return = array($term_id, $term_name);
        $term_id = intval($term_id);
        if ($term_id < 1){
            $term_id = '';
            $term_name = '';
        }
        $return['term_id'] = $term_id;
        $return['term_name'] = $term_name;
        echo json_encode( $return );
    }

    die();

}*/

function go_make_leaderboard() {
    // vars
    $is_ajax = false;

    if(defined('DOING_AJAX') && DOING_AJAX) {
        $is_ajax = true;
        if (!is_user_logged_in()) {
            echo "login";
            die();
        }
        //check_ajax_referer('go_stats_leaderboard_');
        if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], 'go_make_leaderboard')) {
            echo "refresh";
            die();
        }
    }


    $show_all = (isset($_POST['show_all']) ?  $_POST['show_all'] : false);

    if(!$show_all || $show_all === "false" ) {
        // prepares tab titles
        $xp_name = get_option("options_go_loot_xp_name");
        $gold_name = go_get_gold_name();

        $health_name = get_option("options_go_loot_health_name");
        $badges_name = get_option('options_go_badges_name_plural');

        $xp_toggle = get_option('options_go_loot_xp_toggle');
        $gold_toggle = get_option('options_go_loot_gold_toggle');
        $health_toggle = get_option('options_go_loot_health_toggle');

        $badges_toggle = get_option('options_go_badges_toggle');
        $table_id = "go_leaders_datatable";
        $suffix = "";
    }else{
        $suffix = "_all";
        $table_id = "go_leaders_datatable_all";
    }

    ?>

    <div id="go_leaderboard_wrapper<?php echo $suffix; ?>" class="go_datatables" data-show_all="<?php echo $show_all; ?>">

        <div id="go_leaderboard_flex<?php echo $suffix; ?>">

            <div id="go_leaderboard<?php echo $suffix; ?>" class="go_leaderboard_layer">

                <table id='go_leaders_datatable<?php echo $suffix; ?>' class='pretty display'>
                    <thead>
                    <tr>
                        <th></th>
                        <?php
                        /*
                        if ($full_name_toggle == 'full' || $is_admin){
                            echo "<th class='header'><a href='#'>Full Name</a></th>";
                        }else if ($full_name_toggle == 'first'){
                            echo "<th class='header'><a href='#'>First Name</a></th>";
                        }*/
                        ?>
                        <th class='header'><a href="#">Name</a></th>
                         <?php
                        $full_name_toggle = get_option('options_go_full-names_toggle');
                        $is_admin = go_user_is_admin();
                        if ($full_name_toggle == 'full' || $is_admin){
                            echo "<th class='header'><a href=\"#\">Full Name</a></th>";
                        }else if ($full_name_toggle == 'first'){
                            echo "<th class='header'><a href=\"#\">First Name</a></th>";
                        }
                        ?>

                        <th class='header'><a href="#">Links</a></th>

                        <?php

                        if($show_all == "false") {
                            if ($xp_toggle) {
                                echo "<th class='header'><a href='#'>" . $xp_name . "</a></th>";
                            }
                            if ($gold_toggle) {
                                echo "<th class='header'><a href='#'>" . $gold_name . "</a></th>";
                            }
                            if ($health_toggle) {
                                echo "<th class='header'><a href='#'>" . $health_name . "</a></th>";
                            }
                            if ($badges_toggle) {
                                echo "<th class='header'><a href='#'>" . $badges_name . "</a></th>";
                            }
                        }
                        else{
                            $social_feed = get_option('options_go_stats_leaderboard_show_social_feed');
                            if ($social_feed) {
                                echo "<th class='header'><a href='#'>Follow</a></th>";
                            }
                        }
                        ?>

                    </tr>
                    </thead>
                    <tbody></table>
            </div>

        </div>
    </div>


    <?php
    if($is_ajax) {
        die();
    }

}