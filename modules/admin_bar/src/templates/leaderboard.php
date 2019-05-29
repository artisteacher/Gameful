<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-05-12
 * Time: 17:38
 */



get_header();



if ( is_user_logged_in() ) {


    go_stats_leaderboard();
}

get_footer();


