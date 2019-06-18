<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-05-27
 * Time: 19:30
 */

function go_get_avatar($user_id = false){
    if(!$user_id){
        $user_id =  get_current_user_id();
    }
    $user_avatar_id = get_user_option( 'go_avatar', $user_id );
    if (wp_attachment_is_image($user_avatar_id)  ) {
        $user_avatar = wp_get_attachment_image($user_avatar_id);
    }else{
        $user_avatar = get_avatar($user_id);
    }
    return $user_avatar;
}


function go_stats_header($user_id, $show_stats_link = true, $show_internal_links = true, $show_blog_link = true, $is_blog = false, $show_loot = false ){

    ?>
    <input type="hidden" id="go_stats_hidden_input" value="<?php echo $user_id; ?>"/>
    <?php
    $user_data = get_userdata( $user_id );

    $current_user_id = get_current_user_id();
    $is_admin = go_user_is_admin($current_user_id);
    $full_name_toggle = get_option('options_go_full-names_toggle');
    $user_fullname = $user_data->first_name.' '.$user_data->last_name;

    $user_login =  $user_data->user_login;
    $user_display_name = $user_data->display_name;
    $user_website = $user_data->user_url;


    //$user_avatar_id = get_user_option( 'go_avatar', $user_id );
    //$user_avatar = wp_get_attachment_image($user_avatar_id);

    $user_avatar = go_get_avatar($user_id);

/////////////////////////
///
    if($show_loot) {
        $xp_toggle = get_option('options_go_loot_xp_toggle');
        $gold_toggle = get_option('options_go_loot_gold_toggle');
        $health_toggle = get_option('options_go_loot_health_toggle');

        if ($xp_toggle) {
            $progress_bar = xp_progress_bar($user_id);
        } else {
            $progress_bar = '';
        }


        if ($health_toggle) {
            $health_bar = go_health_bar($user_id);
        } else {
            $health_bar = '';
        }

        if ($gold_toggle) {
            // the user's current amount of currency
            $go_current_gold = go_get_user_loot($user_id, 'gold');

        } else {
            $go_current_gold = 0;
        }
    }
    ?>
    <div id='go_stats_header_container' class='go_datatables'>
        <div id='go_stats_header'>
            <div class="go_stats_id_card">
                <div class='go_stats_gravatar'><?php echo $user_avatar; ?></div>

                <div class='go_stats_user_info'>
                    <?php
                    if ($full_name_toggle || $is_admin){
                        echo "<h2>{$user_fullname}</h2>{$user_display_name}<br>";
                    }else{
                        echo "<h2>{$user_display_name}</h2>";
                    }
                    go_user_links($user_id, $show_stats_link, $show_internal_links, $show_blog_link);
                    ?>

                </div>
            </div>


            <?php
           if($show_loot) {
               ?>
               <div class="go_stats_bars">
                   <?php
                   if ($xp_toggle) {
                       $rank = go_get_rank($user_id);
                       $rank_num = $rank['rank_num'];
                       $current_rank = $rank['current_rank'];
                       $go_option_ranks = get_option('options_go_loot_xp_levels_name_singular');

                       echo '<div class="go_stats_rank"><h3>' . $go_option_ranks . ' ' . $rank_num . ": " . $current_rank . '</h3></div>';
                   }
                   echo $progress_bar;
                   //echo "<div id='go_stats_user_points'><span id='go_stats_user_points_value'>{$current_points}</span> {$points_name}</div><div id='go_stats_user_currency'><span id='go_stats_user_currency_value'>{$current_currency}</span> {$currency_name}</div><div id='go_stats_user_bonus_currency'><span id='go_stats_user_bonus_currency_value'>{$current_bonus_currency}</span> {$bonus_currency_name}</div>{$current_penalty} {$penalty_name}<br/>{$current_minutes} {$minutes_name}";
                   echo $health_bar;


                   if ($xp_toggle) {
                       //echo '<div class="go_stats_xp">' . go_display_longhand_currency('xp', $go_current_xp) . '</div>';
                   }
                   if ($gold_toggle) {
                       echo '<div class="go_stats_gold" style="display: flex; padding: 10px;">' . go_display_longhand_currency('gold', $go_current_gold) . '</div>';
                   }
                   if ($health_toggle) {
                      // echo '<div class="go_stats_health">' . go_display_longhand_currency('health', $go_current_health) . '</div>';
                   }
                   ?>
               </div>

               <?php
           }

        ?>
        </div>
        <?php
        if ($show_internal_links && $is_blog) {
            if (($current_user_id === $user_id) || $is_admin) {
                $hide_private = get_user_option('go_show_private');
                $checked = '';
                if ($hide_private) {
                    $checked = 'checked';
                };
                echo "<div id='go_blog_actions' style='' >";
                echo "<div style=''><input id='go_show_private' data-userid='{$user_id}' type='checkbox' {$checked} ><label for='go_show_private'> Show Private, Trashed, and Reset Posts </label></div>";
                if ($current_user_id === $user_id) {
                    echo '<div style=""> <button class="go_blog_opener" blog_post_id ="">New Post</button></div>';
                }

                echo "</div>";
            }
        }
        ?>
    </div>


    <?php
}

function xp_progress_bar($user_id){
    // the user's current amount of experience (points)
    $go_current_xp = go_get_user_loot($user_id, 'xp');

    $rank = go_get_rank($user_id);
    $rank_num = $rank['rank_num'];
    $current_rank = $rank['current_rank'];
    $current_rank_points = $rank['current_rank_points'];
    //$next_rank = $rank['next_rank'];
    $next_rank_points = $rank['next_rank_points'];


    if ($next_rank_points != false) {
        $rank_threshold_diff = $next_rank_points - $current_rank_points;
        $pts_to_rank_threshold = $go_current_xp - $current_rank_points;
        $pts_to_rank_up_str = "XP:{$go_current_xp} L{$rank_num}:{$pts_to_rank_threshold}/{$rank_threshold_diff}";
        $percentage = $pts_to_rank_threshold / $rank_threshold_diff * 100;
        //$color = barColor( $go_current_health, 0 );
        $color = "#39b54a";
    } else {
        $pts_to_rank_up_str = "XP:$go_current_xp L:$rank_num";;
        $percentage = 100;
        $color = "gold";
    }
    if ($percentage <= 0) {
        $percentage = 0;
    } else if ($percentage >= 100) {
        $percentage = 100;
    }
    $progress_bar = '<div class="go_admin_bar_progress_bar_border progress-bar-border">' . '<div class="go_admin_bar_progress_bar stats_progress_bar" ' .
        'style="width: ' . $percentage . '%; background-color: ' . $color . ' ;">' .
        '</div>' .
        '<div class="points_needed_to_level_up go_admin_bar_text">' .
        $pts_to_rank_up_str .
        '</div>' .
        '</div>';

    return $progress_bar;
}

function go_health_bar($user_id){
    // the user's current amount of bonus currency,
    // also used for coloring the admin bar
    $go_current_health = go_get_user_loot($user_id, 'health');
    $health_percentage = intval($go_current_health / 2);
    if ($health_percentage <= 0) {
        $health_percentage = 0;
    } else if ($health_percentage >= 100) {
        $health_percentage = 100;
    }
    $health_bar = '<div class="go_admin_health_bar_border progress-bar-border">' . '<div class="go_admin_bar_health_bar stats_progress_bar progress_bar" ' . 'style="width: ' . $health_percentage . '%; background-color: red ;">' . '</div>' . '<div class="health_bar_percentage_str go_admin_bar_text ">' . "Health Mod: " . $go_current_health . "%" . '</div>' . '</div>';

    return $health_bar;
}