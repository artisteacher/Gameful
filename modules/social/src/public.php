<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-12-30
 * Time: 22:02
 */

/**Leaderboard Stuff Below
 *
 */

/**
 *
 */

function go_social_content() {

    wp_localize_script('go_frontend', 'IsLeaderboard', 'true');
    //$user_id = 0;
    //Get the user_id for the stats
    if ( ! empty( $_POST['uid'] ) ) {
        $user_id = (int) $_POST['uid'];
    } else {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
    }

    $current_user_id = get_current_user_id();

    if($current_user_id === $user_id){
        //reactivate the stats icon in admin bar if this is the current user
        ?>
        <script>
            //jQuery("#wp-admin-bar-go_stats").off().one("click", function(){ go_stats_lightbox_page_button()});
            jQuery(".go_user_bar_stats").off().one("click", function(){ go_stats_lightbox_page_button(<?php echo $user_id; ?>)});
        </script>
        <?php
    }



    go_leaderboard_filters('leaderboard');
    $social_feed = get_option('options_go_stats_leaderboard_show_social_feed');
    ?>

    <div id="social_tabs">
        <ul>
            <li class="social_tabs" tab="leaderboard"><a href="#social_leaderboard">LEADERBOARD</a></li>
            <li class="social_tabs" tab="users"><a href="#social_users">USERS</a></li>
            <?php

            if ($social_feed) {
            ?>
            <li class="social_tabs" tab="feed"><a href="#social_feed">SOCIAL FEED</a></li>
            <?php
            }
            ?>
        </ul>
        <div id="social_leaderboard">
            <div id="social_leaderboard_datatable_container"></div>
        </div>
        <div id="social_users">
            <?php
            if ($social_feed) {
                ?>
                <button onclick='go_followers_list(this)' class='go_social_button'>Followers</button>
                <button onclick='go_following_list(this)' class='go_social_button'>Following</button>
                <?php
            }
            ?>
            <div id="social_users_datatable_container"></div>
        </div>
        <?php

        if ($social_feed) {
            ?>

            <div id="social_feed">
            <span><button onclick='go_followers_list(this)' class='go_social_button'>Followers</button>
                <button onclick='go_following_list(this)' class='go_social_button'>Following</button>
            <input id='go_show_all_feed' type='checkbox' name='go_show_all_feed' style='display: inline;'> Show All Users</span>
                <div id="social_feed_container"></div>
            </div>
            <?php
        }
        ?>

    </div>

    <?php

}

