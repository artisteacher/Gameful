<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-06-02
 * Time: 14:47
 */

function go_clean_up_archive_temp_folder(){
    $destination = plugin_dir_path( __FILE__ )  . 'archive_temp/*';
    $contents = glob($destination);
    $now   = time();

    foreach ($contents as $content) {
            if ($now - filemtime($content) >= 60 * 60 * 24) { // 2 days
                go_delete_temp_archive_helper($content);
            }

    }
}
