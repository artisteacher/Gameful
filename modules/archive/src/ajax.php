<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 10/13/18
 * Time: 8:45 PM
 */

/*
function go_make_admin_archive(){

    $is_admin = go_user_is_admin();
    if (!$is_admin){
        return;
    }

    //remove the auth check script
    remove_action( 'wp_enqueue_scripts', 'go_login_session_expired' );
    $archive_type = (isset($_POST['archive_type']) ? $_POST['archive_type'] : null);
    if ($archive_type == 'private'){
        $is_private = true;
    }else{
        $is_private = false;
    }

    $current_user_id = get_current_user_id();
    mkdir(plugin_dir_path( __FILE__ ) . 'archive_temp/' . $current_user_id  . '/temp/', 0777, 1);

    ob_start();
    generate_single_archive($is_private);
    $content = ob_get_contents();
    ob_end_clean();

    $destination = plugin_dir_path( __FILE__ )  . 'archive_temp/' . $current_user_id .'/temp/';

    $content = convert_urls($content, $destination);


    //Copy JS and CSS files
    mkdir($destination . 'styles/min/',0777,1  );
    mkdir($destination . 'js/min/',0777,1  );


    //$origin_file_path = plugin_dir_path( __FILE__ ) ;
    $go_frontend = dirname(__DIR__, 3) . '/js/min/go_frontend-min.js';
    $go_combine_dependencies = dirname(__DIR__, 3) . '/js/min/go_combine_dependencies-min.js';
    $go_combine_dependencies_css = dirname(__DIR__, 3) . '/styles/min/go_combine_dependencies.css';
    $go_frontend_css = dirname(__DIR__, 3) . '/styles/min/go_frontend.css';
    $go_styles = dirname(__DIR__, 3) . '/styles/min/go_styles.css';
    //$destination_file_path = preg_replace( '/(https?:)?\/\/' . addcslashes( $home_url, '/' ) . '/i', $destination, $match );

    copy( $go_frontend, $destination . 'js/min/go_frontend-min.js' );
    copy( $go_combine_dependencies, $destination . 'js/min/go_combine_dependencies-min.js' );

    copy( $go_combine_dependencies_css, $destination . 'styles/min/go_combine_dependencies.css' );
    copy( $go_frontend_css, $destination . 'styles/min/go_frontend.css' );
    copy( $go_styles, $destination . 'styles/min/go_styles.css' );

    //put the html in the index file
    file_put_contents($destination . 'index.html',$content);


    ////////////
    $zip_dir = plugin_dir_path( __FILE__ )  . 'archive_temp/' . $current_user_id . "/zip/";
    mkdir( $zip_dir,0777,1  );
    //$dir = plugin_dir_path( __FILE__ ) . 'temp/';
    $zip_file = $zip_dir . "MyBlogArchive.zip";

// Get real path for our folder
    $rootPath = realpath($destination);

// Initialize archive object
    $zip = new ZipArchive();
    $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file)
    {
        // Skip directories (they would be added automatically)
        if (!$file->isDir())
        {
            // Get real and relative path for current file
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);

            // Add current file to archive
            $zip->addFile($filePath, $relativePath);
        }
    }


    $success = $zip->close();
    if($success) {
        $zip_url = plugin_dir_url(__FILE__)  . 'archive_temp/' . $current_user_id . "/zip/MyBlogArchive.zip";
        echo $zip_url;
    }else{
        echo 0;
    }
    die();

}
*/


function go_make_user_archive_zip(){
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }

    //check_ajax_referer( 'go_clipboard_activity_' . get_current_user_id() );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_make_user_archive_zip' ) ) {
        echo "refresh";
        die( );
    }
    //remove the auth check script
    remove_action( 'wp_enqueue_scripts', 'go_login_session_expired' );
    go_clean_up_archive_temp_folder();//clean up old files in the archive_temp_folder

    //create folder
    //put html file in it
    //change all links in the content to relative
    //add media folder
    //put media in it
    //zip it up
    $archive_type = (isset($_POST['archive_type']) ? $_POST['archive_type'] : null);
    $is_admin_archive = (isset($_POST['is_admin_archive']) ? $_POST['is_admin_archive'] : null);


    if ($archive_type == 'private'){
        $is_private = true;
    }else{
        $is_private = false;
    }
    //mkdir(plugin_dir_path( __FILE__ ) . 'temp/media', 0777);
    ob_start();

    //PUT CODE HERE TO GENERATE USER LIST IF THIS IS ADMIN ARCHIVE
    if ($is_admin_archive) {
        $is_admin = go_user_is_admin();

        //if this is an admin archive, bail if this user is not an admin
        if (!$is_admin) {
            return;
        }

        $user_list = (isset($_POST['archive_vars']) ? $_POST['archive_vars'] : null);
        generate_user_list($user_list, $is_private);

    }else {
        generate_single_archive($is_private);
    }
    $content = ob_get_contents();
    ob_end_clean();

    $current_user_id = get_current_user_id();
    $destination = plugin_dir_path( __FILE__ )  . 'archive_temp/' . $current_user_id .'/temp/';
    mkdir($destination, 0777, 1);


    $content = convert_urls($content, $destination);
    //put the html in the index file
    file_put_contents($destination . 'index.html',$content);

    $num_done = 0;
    if ($is_admin_archive) {
        $user_count = count($user_list);
        $five_percent = intval($user_count/20);
        $next_update = $five_percent;
        foreach ($user_list as $user) {
            $user_id = $user['uid'];

            $user_info = get_userdata($user_id);
            $username = $user_info->user_login;
            ob_start();
            generate_single_archive($is_private, $user_id);
            $content = ob_get_contents();
            ob_end_clean();
            $user_destination = $destination . 'users/' . $username .'/';
            mkdir($user_destination, 0777, 1);
            $content = convert_urls($content, $user_destination);
            file_put_contents($user_destination .'/index.html',$content);
            $num_done++;
            if ($num_done >= $next_update) {
                update_user_option($current_user_id, 'archive_done_num', $num_done);
                $next_update = $next_update + $five_percent;
            }
            sleep(1);
        }
        update_user_option($current_user_id, 'archive_done_num', 'done');
    }


    //Copy JS and CSS files
    mkdir($destination . 'styles/min/',0777,1  );
    mkdir($destination . 'js/min/',0777,1  );


    //$origin_file_path = plugin_dir_path( __FILE__ ) ;
    $go_frontend = dirname(__DIR__, 3) . '/js/min/go_frontend-min.js';
    $go_combine_dependencies = dirname(__DIR__, 3) . '/js/min/go_combine_dependencies-min.js';
    $go_combine_dependencies_css = dirname(__DIR__, 3) . '/styles/min/go_combine_dependencies.css';
    $go_frontend_css = dirname(__DIR__, 3) . '/styles/min/go_frontend.css';
    $go_styles = dirname(__DIR__, 3) . '/styles/min/go_styles.css';
    //$destination_file_path = preg_replace( '/(https?:)?\/\/' . addcslashes( $home_url, '/' ) . '/i', $destination, $match );

    copy( $go_frontend, $destination . 'js/min/go_frontend-min.js' );
    copy( $go_combine_dependencies, $destination . 'js/min/go_combine_dependencies-min.js' );

    copy( $go_combine_dependencies_css, $destination . 'styles/min/go_combine_dependencies.css' );
    copy( $go_frontend_css, $destination . 'styles/min/go_frontend.css' );
    copy( $go_styles, $destination . 'styles/min/go_styles.css' );

    ////////////
    $zip_dir = plugin_dir_path( __FILE__ )  . 'archive_temp/' . $current_user_id . "/zip/";
    mkdir( $zip_dir,0777,1  );
    //$dir = plugin_dir_path( __FILE__ ) . 'temp/';
    $zip_file = $zip_dir . "MyBlogArchive.zip";

// Get real path for our folder
    $rootPath = realpath($destination);

// Initialize archive object
    $zip = new ZipArchive();
    $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
    /** @var SplFileInfo[] $files */
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file)
    {
        // Skip directories (they would be added automatically)
        if (!$file->isDir())
        {
            // Get real and relative path for current file
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);

            // Add current file to archive
            $zip->addFile($filePath, $relativePath);
        }
    }


    $success = $zip->close();
    if($success) {
        $zip_url = plugin_dir_url(__FILE__)  . 'archive_temp/' . $current_user_id . "/zip/MyBlogArchive.zip";
        echo $zip_url;
    }else{
        echo 0;
    }
die();

}

function go_archive_progress(){
    //add nonce check
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_archive_progress' ) ) {
        echo "refresh";
        die( );
    }

    $current_user_id = get_current_user_id();
    $first = (isset($_POST['first']) ? $_POST['first'] : false);
    if ($first === 'true') {
        update_user_option($current_user_id, 'archive_done_num', 0);
        $done = 0;
    }else {
        $done = get_user_option('archive_done_num');
    }
    echo $done;
    die();
}

function go_add_utf8_archive(){
    ?>
    <meta charset="utf-8" />
    <?php
};

function generate_single_archive($is_private = false, $user_id = false){

    if(!$user_id) {
        $user_obj = wp_get_current_user();
        $user_id = get_current_user_id();
        $admin_archive_dir = '';
        $is_admin_archive = false;
    }else{
        $admin_archive_dir = '../../';
        $user_obj = get_userdata($user_id);
        $is_admin_archive = true;
    }

    /* Describe what the code snippet does so you can remember later on */
    add_action('wp_head', 'go_add_utf8_archive');

    wp_head();


    ?>
    <link rel='stylesheet' id='go_combine_dependencies'  href='<?php echo $admin_archive_dir; ?>styles/min/go_combine_dependencies.css' type='text/css' media='all' />
    <link rel='stylesheet' id='go_frontend'  href='<?php echo $admin_archive_dir; ?>styles/min/go_frontend.css' type='text/css' media='all' />
    <link rel='stylesheet' id='go_styles'  href='<?php echo $admin_archive_dir; ?>styles/min/go_styles.css' type='text/css' media='all' />
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script
            src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
            integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
            crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <?php





    $user_display_name = $user_obj->display_name;
    $page_title = $user_display_name . "'s Blog";

    ?>

    <script>
        document.title = "<?php echo $page_title; ?>";//set page title
    </script><?php
    if($is_admin_archive){
        ?>

        <div><a href="../../index.html"><button>"Back to User List</button></a></div>
       <?php
    }
        go_stats_header($user_id, true, false, false, false, false, false, true, true, $is_private);

    ?>
    <div id='loader_container' style='display:none; height: 250px; width: 100%; padding: 10px 30px; '>
        <div id='loader'>
            <i class='fas fa-spinner fa-pulse fa-4x'></i>
        </div>
    </div>
    <?php


    go_get_blog_posts($user_id, true, $is_private);

    ?>
    <script>

        jQuery( document ).ready( function() {
            //console.log("opener1");
            //jQuery(".go_blog_opener").one("click", function(e){
            //    go_blog_opener( this );
            //});
            // remove existing editor instance
            //tinymce.execCommand('mceRemoveEditor', true, 'go_blog_post');
            //tinymce.execCommand('mceRemoveEditor', true, 'go_blog_post_lightbox');
            //jQuery('#go_hidden_mce').remove();
            //jQuery('#go_hidden_mce_edit').remove();
            jQuery('html').attr('style', 'margin-top: 0px !important');

            jQuery('body').attr('style', 'margin-top: 0px !important');
        });

    </script>
    <script type = "text/javascript" src = "<?php echo $admin_archive_dir; ?>js/min/go_combine_dependencies-min.js" ></script>
    <script type = "text/javascript" src = "<?php echo $admin_archive_dir; ?>js/min/go_frontend-min.js" ></script>
    <?php

    wp_footer();
    //do_action( 'wp_footer' );

    //do_action( 'wp_print_footer_scripts' );
}

function generate_user_list($user_list, $is_private){

    /* Describe what the code snippet does so you can remember later on */
    add_action('wp_head', 'go_add_utf8_archive');



    wp_head();

    ?>
    <link rel='stylesheet' id='go_combine_dependencies'  href='styles/min/go_combine_dependencies.css' type='text/css' media='all' />
    <link rel='stylesheet' id='go_frontend'  href='styles/min/go_frontend.css' type='text/css' media='all' />
    <link rel='stylesheet' id='go_styles'  href='styles/min/go_styles.css' type='text/css' media='all' />
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script
            src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
            integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
            crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <?php

    $page_title = "Blog Archive";

    ?>

    <script>
        document.title = "<?php echo $page_title; ?>";//set page title
    </script>
    <div style="width:800px; margin: auto;">
    <h1>Blog Archive</h1>

    <?php

    $section = (isset($_POST['section']) ? $_POST['section'] : null);
    $group = (isset($_POST['group']) ? $_POST['group'] : null);
    $badge = (isset($_POST['badge']) ? $_POST['badge'] : null);
    $sectionTermName = '';
    $groupsTermName = '';
    $badgeTermName = '';
    if(!empty($section)) {
        $sectionTermObject = get_term_by('id', absint($section), 'user_go_sections');
        $sectionTermName = $sectionTermObject->name;
    }
    if(!empty($group)) {
        $groupsTermObject = get_term_by('id', absint($group), 'user_go_groups');
        $groupsTermName = $groupsTermObject->name;
    }
    if(!empty($badge)) {
        $basgeTermObject = get_term_by('id', absint($badge), 'go_badges');
        $badgeTermName = $basgeTermObject->name;
    }

    if(!empty($sectionTermName) || !empty($groupsTermName) || !empty($badgeTermName)){
        echo "<h3>These users are from ";
        if (!empty($sectionTermName)) {
            echo "Section: $sectionTermName";
            $and = true;
        }
        if (!empty($groupsTermName) ) {
            if ($and) {
                echo " and ";
            }
            echo "Group: $groupsTermName";
            $and = true;
        }
        if (!empty($groupsTermName) ) {
            if ($and) {
                echo " and ";
            }
            echo "Badge: $badgeTermName";
        }
        echo ".</h3>";
    }

    if($is_private){
        echo "<p>This archive contains all user posts–including private posts, drafts, and trash–as well as the admin feedback.  It is intended as an instructor record only. <b>Do not place this archive in a publicly available location.</b></p>";
    }

    foreach ($user_list as $user){
        $user_id = $user['uid'];
        $user_info = get_userdata($user_id);
        $username = $user_info->user_login;
        $first_name = $user_info->first_name;
        $last_name = $user_info->last_name;
        echo "$first_name $last_name: <a href='users/$username/index.html'>$username </a><br>";
    }

    ?>
    </div>
    <script>

        jQuery( document ).ready( function() {
            //console.log("opener1");
            //jQuery(".go_blog_opener").one("click", function(e){
            //    go_blog_opener( this );
            //});
            // remove existing editor instance
            //tinymce.execCommand('mceRemoveEditor', true, 'go_blog_post');
            //tinymce.execCommand('mceRemoveEditor', true, 'go_blog_post_lightbox');
            //jQuery('#go_hidden_mce').remove();
            //jQuery('#go_hidden_mce_edit').remove();
            jQuery('html').attr('style', 'margin-top: 0px !important');

            jQuery('body').attr('style', 'margin-top: 0px !important');
        });

    </script>
    <script type = "text/javascript" src = "js/min/go_combine_dependencies-min.js" ></script>
    <script type = "text/javascript" src = "js/min/go_frontend-min.js" ></script>
    <?php

    wp_footer();
    //do_action( 'wp_footer' );

    //do_action( 'wp_print_footer_scripts' );
}

function convert_urls($content, $destination){
    $home_url = home_url();
    $pattern = '/^(https?:)?\/\//';
    $home_url = preg_replace( $pattern, '', $home_url ) . '/';
    //$destination_url = '/media';
    $home_path = get_home_path();

    //files to exclude
    $login = $home_url . 'wp-login.php';
    $wp_includes = $home_url . 'wp-includes';
    $wp_admin = $home_url . 'wp-admin';

    //copy all the files linked to the temp folder
    if(preg_match_all('/(https?:)?\/\/' . addcslashes( $home_url, '/' ) . '.+?(?=\"|\')' . '/is', $content, $matches)) {
        foreach ($matches[0] as $match){


            if(preg_match('/(https?:)?\/\/' . addcslashes( $login, '/' ) . '/i', $match, $exclude)){
                continue;
            }
            if(preg_match('/(https?:)?\/\/' . addcslashes( $wp_includes, '/' ) . '/i', $match, $exclude)){
                continue;
            }
            if(preg_match('/(https?:)?\/\/' . addcslashes( $wp_admin, '/' ) . '/i', $match, $exclude)){
                continue;
            }
            $origin_file_path =  preg_replace( '/(https?:)?\/\/' . addcslashes( $home_url, '/' ) . '/i', $home_path, $match );
            $destination_file_path = preg_replace( '/(https?:)?\/\/' . addcslashes( $home_url, '/' ) . '/i', $destination, $match );

            $destination_file_dir = preg_replace( '/([^\/]+$)/', '', $destination_file_path );
            mkdir($destination_file_dir,0777,1  );
            copy( $origin_file_path, $destination_file_path );
        }

    }

    //get the full size image of all images so they can be opened in a lightbox
    if(preg_match_all('/wp-image-'. '.+?(?=\"|\'|\ )' .'/is', $content, $matches)) {
        foreach ($matches[0] as $match){
            $media_id =  preg_replace( '/wp-image-/', '', $match );
            $origin_file_path = get_attached_file( $media_id);
            $destination_file_path = str_replace( $home_path, $destination, $origin_file_path );
            $destination_file_dir = preg_replace( '/([^\/]+$)/', '', $destination_file_path );
            mkdir($destination_file_dir,0777,1  );
            copy( $origin_file_path, $destination_file_path );
        }
    }
    //get the full size image for any image linked
    /*
    jQuery('[class*= wp-image]').each(function(  ) {
        var fullSize = jQuery( this ).hasClass( "size-full" );
        //console.log("fullsize:" + fullSize);
        if (fullSize == true) {
            var imagesrc = jQuery(this).attr('src');
        }else{

            var class1 = jQuery(this).attr('class');
            //console.log(class1);
            //var patt = /w3schools/i;
            var regEx = /.*wp-image/;
            var imageID = class1.replace(regEx, 'wp-image');
            //console.log(imageID);

            var src1 = jQuery(this).attr('src');
            //console.log(src1);
            //var patt = /w3schools/i;
            var regEx2 = /-([^-]+).$/;


            //var regEx3 = /\.[0-9a-z]+$/i;
            var patt1 = /\.[0-9a-z]+$/i;
            var m1 = (src1).match(patt1);

            //var imagesrc = src1.replace(regEx2, regEx3);
            var imagesrc = src1.replace(regEx2, m1);
            //console.log(imagesrc);
        }
        jQuery(this).featherlight(imagesrc);
    });*/

    //change the links to relative links
    $content = preg_replace( '/(https?:)?\/\/' . addcslashes( $home_url, '/' ) . '/i', '', $content );
    // replace wp_json_encode'd urls, as used by WP's `concatemoji`
    // e.g. {"concatemoji":"http:\/\/www.example.org\/wp-includes\/js\/wp-emoji-release.min.js?ver=4.6.1"}
    $content = str_replace( addcslashes( $home_url, '/' ), addcslashes( '', '/' ), $content );
    // replace encoded URLs, as found in query params
    // e.g. http://example.org/wp-json/oembed/1.0/embed?url=http%3A%2F%2Fexample%2Fcurrent%2Fpage%2F"
    $content = preg_replace( '/(https?%3A)?%2F%2F' . addcslashes( urlencode( $home_url ), '.' ) . '/i', urlencode( '' ), $content );



    return $content;
}

