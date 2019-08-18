<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-05-27
 * Time: 19:30
 */



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

    $user_display_name = go_get_user_display_name($user_id);
    $user_website = $user_data->user_url;


    //$user_avatar_id = get_user_option( 'go_avatar', $user_id );
    //$user_avatar = wp_get_attachment_image($user_avatar_id);

    $user_avatar = get_avatar($user_id);

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
                    echo "<h2>{$user_display_name}</h2>";

                    if ($full_name_toggle == 'full' || $is_admin){
                        echo "<h3>{$user_fullname}</h3><br>";
                    }else if ($full_name_toggle == 'first'){
                        echo "<h3>{$user_data->first_name}</h3><br>";
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
