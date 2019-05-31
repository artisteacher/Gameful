<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 10/13/18
 * Time: 8:45 PM
 */

function go_make_user_archive_zip(){

    //remove the auth check script
    remove_action( 'wp_enqueue_scripts', 'go_login_session_expired' );


    //create folder
    //put html file in it
    //change all links in the content to relative
    //add media folder
    //put media in it
    //zip it up
    $archive_type = (isset($_POST['archive_type']) ? $_POST['archive_type'] : null);
    if ($archive_type == 'private'){
        $is_private = true;
    }else{
        $is_private = false;
    }

    $current_user_id = get_current_user_id();

    mkdir(plugin_dir_path( __FILE__ ) . 'archive_temp/' . $current_user_id  . '/temp/', 0777, 1);
    //mkdir(plugin_dir_path( __FILE__ ) . 'temp/media', 0777);
    ob_start();
    generate_archive_list($is_private);
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

function go_add_utf8_archive(){
    ?>
    <meta charset="utf-8" />
    <?php
};

function generate_archive_list($is_private = false){

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
    $user_obj = wp_get_current_user();
    $current_user_id = get_current_user_id();



    $is_admin = go_user_is_admin($current_user_id);

    $user_fullname = $user_obj->first_name . ' ' . $user_obj->last_name;
    $user_login = $user_obj->user_login;
    $user_display_name = $user_obj->display_name;
    $user_website = $user_obj->user_url;
    $page_title = $user_display_name . "'s Blog";

    ?>

    <script>
        document.title = "<?php echo $page_title; ?>";//set page title
    </script><?php
        go_stats_header($current_user_id, true, false, false, false, false, false, true, true, $is_private);

    ?>
    <div id='loader_container' style='display:none; height: 250px; width: 100%; padding: 10px 30px; '>
        <div id='loader'>
            <i class='fas fa-spinner fa-pulse fa-4x'></i>
        </div>
    </div>
    <?php


    go_get_blog_posts($current_user_id, true, $is_private);

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


function go_delete_temp_archive($dirPath = false){
    //check nonce, but move the work to a helper function

    if (!$dirPath) {
        $current_user_id = get_current_user_id();
        $dirPath = plugin_dir_path(__FILE__) . 'archive_temp/' . $current_user_id . '/';
    }
    /*
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            go_delete_temp_archive($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);*/

    //$dir = 'samples' . DIRECTORY_SEPARATOR . 'sampledirtree';
    $it = new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it,
        RecursiveIteratorIterator::CHILD_FIRST);
    foreach($files as $file) {
        if ($file->isDir()){
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    rmdir($dirPath);
}
