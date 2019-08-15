<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-06-10
 * Time: 20:33
 */

/**
 * @param bool $section
 * @return string
 */
function go_sectionQuery($section = false){
    if ($section === false) {
        $section = (isset($_REQUEST['section']) ? $_REQUEST['section'] : null);
    }
    if (!empty($section)) {
        global $wpdb;
        if(is_gameful()) {
            $main_site_id = get_network()->site_id;
            switch_to_blog($main_site_id);
        }
        $umTable = "{$wpdb->prefix}usermeta";
        if(is_gameful()) {
            restore_current_blog();
        }
        $key = go_prefix_key('go_section');

        $Query = "   LEFT JOIN $umTable as t3 ON t2.user_id = t3.user_id
                            WHERE (meta_key = '$key' AND meta_value = $section) 
                            GROUP BY t2.user_id";
    }else{
        return '';
    }
    return $Query;
}

/**
 * @return string
 */
function go_badgeQuery(){

    $badge = (isset($_GET['badge']) ?  $_GET['badge'] : null);
    if (!empty($badge)) {

        global $wpdb;
        $umTable = "{$wpdb->prefix}usermeta";
        $key = go_prefix_key('go_badge');

        $Query = "   LEFT JOIN $umTable as t5 ON t4.user_id = t5.user_id
                            WHERE (meta_key = '$key' AND meta_value = $badge) 
                            GROUP BY t4.user_id";
    }else{
        return '';
    }
    return $Query;
}

/**
 * @param bool $group
 * @return string
 */
function go_groupQuery($group = false){
    if($group === false) {
        $group = (isset($_GET['group']) ? $_GET['group'] : null);
    }
    if (!empty($group)) {

        global $wpdb;
        $umTable = "{$wpdb->prefix}usermeta";
        $key = go_prefix_key('go_group');

        $Query = "   LEFT JOIN $umTable AS t7 ON t6.user_id = t7.user_id
                            WHERE (meta_key = '$key' AND meta_value = $group) 
                            GROUP BY t6.user_id";
    }else{
        return '';
    }
    return $Query;
}