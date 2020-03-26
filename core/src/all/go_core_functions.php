<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-05-15
 * Time: 21:44
 */



//clone a post
//if it's a template clone it as a task
/**
 * @param bool $is_template
 * @param bool $print
 */
function go_clone_post_new($is_template = false, $print = false){
    global $wpdb;
    /*
     * get the original post id
     */
    $post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
    $global = (isset($_GET['global']) ? $_GET['global']  : false );

    if ($post_id == 0){
        if ($print){
            echo get_admin_url(null, 'post-new.php?post_type=tasks');
            die();
        }
    }
    /*
     * and all the original post data then
     */
    if($global == "true" && is_gameful()){
        $primary_blog_id = get_main_site_id();
        switch_to_blog(intval($primary_blog_id));
    }
    $post = get_post( $post_id );


    /*
     * if you don't want current user to be the new post author,
     * then change next couple of lines to this: $new_post_author = $post->post_author;
     */
    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;

    /*
     * if post data exists, create the post duplicate
     */
    if (isset( $post ) && $post != null) {

        if ($is_template) {
            $post_type = 'tasks';
        }else{
            $post_type = $post->post_type;
        }

        /*
         * new post data array
         */
        $args = array(
            'comment_status' => $post->comment_status,
            'ping_status'    => $post->ping_status,
            'post_author'    => $new_post_author,
            'post_content'   => $post->post_content,
            'post_excerpt'   => $post->post_excerpt,
            'post_name'      => $post->post_name,
            'post_parent'    => $post->post_parent,
            'post_password'  => $post->post_password,
            'post_status'    => 'draft',
            'post_title'     => $post->post_title . " copy",
            'post_type'      => $post_type,
            'to_ping'        => $post->to_ping,
            'menu_order'     => $post->menu_order
        );

        /*
         * insert the post by wp_insert_post() function
         */
        if($global == "true" && is_gameful()){
            restore_current_blog();
        }
        $new_post_id = wp_insert_post( $args );
        if($global !== "true") {
            /*
             * get all current post terms ad set them to the new post draft
             */
            $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
            }
        }
        /*
         * duplicate all post meta just in two SQL queries
         */
        if($global == "true" && is_gameful()){
            $primary_blog_id = get_main_site_id();
            switch_to_blog(intval($primary_blog_id));
        }
        $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
        if($global == "true" && is_gameful()){
            restore_current_blog();
        }
        if (count($post_meta_infos)!=0) {
            $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
            foreach ($post_meta_infos as $meta_info) {
                $meta_key = $meta_info->meta_key;
                if( $meta_key == '_wp_old_slug' ) continue;
                $meta_value = addslashes($meta_info->meta_value);
                $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
            }
            $sql_query.= implode(" UNION ALL ", $sql_query_sel);
            $wpdb->query($sql_query);
        }

        if ($print){
            echo  admin_url( 'post.php?action=edit&post=' . $new_post_id );
            die();
        }
        /*
         * finally, redirect to the edit post screen for the new draft
         */
        wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
        exit;
    } else {
        wp_die('Post creation failed, could not find original post: ' . $post_id);
    }
}

/**
 * Called by the ajax dataloaders.
 * @param $TIMESTAMP
 * @return false|string
 */
function go_clipboard_time($TIMESTAMP){
    if ($TIMESTAMP != null) {
        $time = date("m/d/y g:i A", strtotime($TIMESTAMP));
    }else{
        $time = "N/A";
    }
    return $time;
}


//this then uses select2 and ajax to make dropdown
//if is ca
/**
 * @param $taxonomy
 * @param null $is_lightbox (location is needed in case there are multiple dropdowns for the same taxonomy ie: messages on the clipboard)
 * @param bool $value //optional: the value of the term
 * @param bool $value_name //optional: the title of the term
 * @param bool $activate_filter
 */
function go_make_tax_select ($taxonomy, $location = 'page', $value = false, $value_name = false, $activate_filter = false){

    if($activate_filter){
        $class = 'go_activate_filter';
    }else{
        $class = '';
    }

    echo "<select id='go_". $location . "_" . $taxonomy . "_select' ";
    if ($value) {
        echo " data-value='$value' ";
    }
    if($value_name){
        echo "data-value_name='$value_name' ";
    }
    echo " class='$class'></select>";
}



/**
 * @param $term_ids
 * @return array|string|null
 */
function go_print_term_list($term_ids){
    if (is_serialized($term_ids)) {
        $term_ids = unserialize($term_ids);
    }

    if (!is_array($term_ids)){
            $term_ids = explode(',', $term_ids);
    }
    if (is_array($term_ids)){
        $list = array();
        foreach ($term_ids as $term_id){
            $term_id = intval($term_id);
            if (!empty($term_id)) {
                $term = get_term($term_id);
                if (!empty($term)) {
                    $name = $term->name;
                    $list[] = $name;
                }
            }
        }
        if(!empty($list)) {
            $list = implode("<br>", $list);
            //$list = '<span class="tooltip" data-tippy-content="'. $list .'">'. $list . '</span>';
            $list = '<span>' . $list . '</span>';
        }else{
            $list = '';
        }

    }
    else{
        $list = null;

    }
    return $list;
}


/**
 * @param $key
 * @return string
 */
function go_prefix_key($key){

    global $wpdb;
    $prefix = $wpdb->prefix;
    if ($prefix) {
        $key = $prefix . $key;
    }
    return $key;
}

function go_get_terms_ordered($taxonomy, $parent = '', $number = ''){
    $args = array(
        'number' => $number,
        'parent' => $parent,
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'go_order',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'key' => 'go_order',
                'value' => 0,
                'compare' => '>='
            )
        ),
        'hide_empty' => false
    );

    $terms = get_terms($taxonomy, $args);//the rows

    return $terms;
}

function go_get_ordered_posts($term_id){
    $taxonomy = null;
    if(empty($post_slug)) {
        $term = get_term($term_id);
        $taxonomy = $term->taxonomy;
    }

    if($taxonomy === 'task_chains'){
        $post_type = 'tasks';
        $order_key_name = 'go-location_map_order_item';
        $toggle_key = 'go-location_map_toggle';
        $loc_key = 'go-location_map_loc';
    }
    else if($taxonomy === 'store_types'){
        $post_type = 'go_store';
        $order_key_name = 'go-store-location_store_item';
        $toggle_key = 'go-store-location_store-sec_toggle';
        $loc_key = 'go-store-location_store-sec_loc';
    }else{
        return;
    }
    $args = array(
        'post_type' => $post_type,
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'posts_per_page' => -1,
        'meta_key' => $order_key_name,
        'post_status' => 'publish',
        'suppress_filters' => true,
        'meta_query' => array(
            array(
                'key'     => $toggle_key,
                'value'   => 1,
            )
        ),
        'meta_query' => array(
            array(
                'key'     => $loc_key,
                'value'   => $term_id,
            )
        ),
    );


    $posts = get_posts($args);

    return $posts;
}


function go_get_page_uri(){
    $request_uri = (isset($_SERVER['REQUEST_URI']) ?  $_SERVER['REQUEST_URI'] : null);//page currently being loaded

    $str = basename($request_uri);
    $sub = substr($str, 0, 1);
    if ($sub == '?'){
        $request_uri = str_replace($str, '', $request_uri);
        $str = basename($request_uri);
    }
    $page_uri = strtok($str,'?');


    return $page_uri;
}

function go_get_fullname($user_id = ''){
    if(empty($user_id)){
        $user_id = get_current_user_id();
    }

    if(empty($user_id)) {
        return;
    }
    $user_data = get_userdata($user_id);

    $user_display_name = go_get_user_display_name($user_id);

    $full_name_toggle = get_option('options_go_full-names_toggle');
    $is_admin = go_user_is_admin();

    //$show_real_names = go_show_hidden();

    if ($full_name_toggle == 'full' || ($is_admin)) {
        $name = "{$user_data->first_name} {$user_data->last_name} ({$user_display_name})";
    } else if ($full_name_toggle == 'first') {
        $name = "{$user_data->first_name} ({$user_display_name})";

    } else {
        $name = $user_display_name;
    }

    return $name;
}
//
function go_get_user_display_name($user_id = ''){
    if(empty($user_id)){
        $user_id = get_current_user_id();
    }
    $user_data = get_userdata($user_id);
    if(empty($user_id)) {
        return;
    }

    $user_display_name = get_user_option('go_nickname', $user_id);
    if (empty($user_display_name)) {
        $user_display_name = get_user_meta($user_id, 'nickname', true);
        if (empty($user_display_name)) {
            $user_display_name = $user_data->display_name;
        }
    }

    $name = $user_display_name;

    return $name;
}

function go_get_website($user_id = ''){
    if(empty($user_id)){
        $user_id = get_current_user_id();
    }
    $website = get_user_option( 'go_website', $user_id );
    if(empty($website)){
        $current_user = get_userdata($user_id);
        $website = $current_user->user_url;
    }
    return $website;
}
//
function go_get_avatar($user_id = false, $avatar_html = false, $size = 'thumbnail'){
    if(!$user_id){
        $user_id =  get_current_user_id();
    }
    $user_avatar_id = get_user_option( 'go_avatar', $user_id );
    if (wp_attachment_is_image($user_avatar_id)  ) {

        $user_avatar = wp_get_attachment_image($user_avatar_id, $size, false, array( "class" => "avatar avatar-64 photo" ));
    }else{
        if ($avatar_html) {
            $user_avatar = $avatar_html;
        }else{
            $user_avatar = false;
        }
    }
    return $user_avatar;
}

function go_override_avatar ($avatar_html, $id_or_email, $size, $default, $alt) {
    $user = false;
    $avatar = $avatar_html;
    if ( is_numeric( $id_or_email ) ) {

        $id = (int) $id_or_email;
        $user = get_user_by( 'id' , $id );

    } elseif ( is_object( $id_or_email ) ) {

        if ( ! empty( $id_or_email->user_id ) ) {
            $id = (int) $id_or_email->user_id;
            $user = get_user_by( 'id' , $id );
        }

    } else {
        $user = get_user_by( 'email', $id_or_email );
    }

    if ( $user && is_object( $user ) ) {
        $user_id = $user->ID;
        $new_avatar = go_get_avatar($user_id, $avatar_html, array(64, 64));
        if(!empty($new_avatar)){
            $avatar = $new_avatar;
        }

    }

    return $avatar;
}


add_action( 'admin_bar_menu', function(){
    add_filter ('get_avatar', 'go_override_avatar', 1, 5);
},0);

add_action( 'wp_after_admin_bar_render', function(){
    remove_filter ('get_avatar', 'go_override_avatar', 1, 5);
});




function go_acf_labels( $field ) {
    //badges
    //groups
    $text = $field['label'];
    preg_match_all("/\[[^\]]*\]/", $text, $matches);
    $my_matches = $matches[0];
    foreach($my_matches as $match){
        $replace_with = go_acf_replace_text($match);
        if(!empty($replace_with)){
            $field['label'] = str_replace($match, $replace_with, $field['label']);
        }
    }

    $instructions = $field['instructions'];
    preg_match_all("/\[[^\]]*\]/", $instructions, $matches);
    $my_matches = $matches[0];
    foreach($my_matches as $match){
        $replace_with = go_acf_replace_text($match);
        if(!empty($replace_with)){
            $field['instructions'] = str_replace($match, $replace_with, $field['instructions']);
        }
    }

    if(isset($field['choices']['parent'])) {
        $parent_text = $field['choices']['parent'];
        preg_match_all("/\[[^\]]*\]/", $parent_text, $matches);
        $my_matches = $matches[0];
        foreach ($my_matches as $match) {
            $replace_with = go_acf_replace_text($match);
            if (!empty($replace_with)) {
                $field['choices']['parent'] = str_replace($match, $replace_with, $field['choices']['parent']);
            }
        }
    }

    if(isset($field['choices']['child'])){
        $child_text = $field['choices']['child'];
        preg_match_all("/\[[^\]]*\]/", $child_text, $matches);
        $my_matches = $matches[0];
        foreach ($my_matches as $match) {
            $replace_with = go_acf_replace_text($match);
            if (!empty($replace_with)) {
                $field['choices']['child'] = str_replace($match, $replace_with, $field['choices']['child']);
            }
        }
    }



    return $field;

}
add_filter('acf/prepare_field', 'go_acf_labels');

function go_acf_replace_text($match){
    $replace_with = $match;
    if($match === '[Experience]'){
        $replace_with = get_option("options_go_loot_xp_name");
    }
    else if($match === '[XP]'){
        $replace_with = get_option("options_go_loot_xp_abbreviation");
    }
    else if($match === '[Gold]'){
        $replace_with = go_get_gold_name();
    }
    else if($match === '[G]'){
        $replace_with =  go_get_loot_short_name('gold');
    }
    else if($match === '[Reputation]'){
        $replace_with = get_option("options_go_loot_health_name");
    }
    else if($match === '[Rep]'){
        $replace_with = get_option("options_go_loot_health_abbreviation");
    }
    else if($match === '[Badges]'){
        $replace_with = ucwords(get_option('options_go_badges_name_plural'));
    }
    else if($match === '[Badge]'){
        $replace_with = ucwords(get_option('options_go_badges_name_singular'));
    }
    else if($match === '[Group]'){
        $replace_with = ucwords(get_option('options_go_groups_name_singular'));
    }
    else if($match === '[Groups]'){
        $replace_with = ucwords(get_option('options_go_groups_name_plural'));
    }
    return $replace_with;
}


function go_acf_select_labels( $field ) {
    //badges
    //groups
    $choices = $field['choices'];
    foreach ($choices as $key => $value){
        $text = $value;
        if(is_string($text)) {
            preg_match_all("/\[[^\]]*\]/", $text, $matches);
            $my_matches = $matches[0];

            foreach ($my_matches as $match) {
                if ($match === '[Experience]') {
                    $replace_with = get_option("options_go_loot_xp_name");
                } else if ($match === '[XP]') {
                    $replace_with = get_option("options_go_loot_xp_abbreviation");
                } else if ($match === '[Gold]') {
                    $replace_with = go_get_gold_name();
                } else if ($match === '[G]') {
                    $replace_with = go_get_loot_short_name('gold');
                } else if ($match === '[Reputation]') {
                    $replace_with = get_option("options_go_loot_health_name");
                } else if ($match === '[Rep]') {
                    $replace_with = get_option("options_go_loot_health_abbreviation");
                } else if ($match === '[Badges]') {
                    $replace_with = ucwords(get_option('options_go_badges_name_plural'));
                } else if ($match === '[Badge]') {
                    $replace_with = ucwords(get_option('options_go_badges_name_singular'));
                } else if ($match === '[Group]') {
                    $replace_with = ucwords(get_option('options_go_groups_name_singular'));
                } else if ($match === '[Groups]') {
                    $replace_with = ucwords(get_option('options_go_groups_name_plural'));
                }

                if (!empty($replace_with)) {
                    $text = str_replace($match, $replace_with, $text);
                    $field['choices'][$key] = $text;
                }
            }
        }
    }

    return $field;

}
add_filter('acf/prepare_field/type=select', 'go_acf_select_labels');


add_filter( 'generate_404_text','generate_custom_404_text' );
function generate_custom_404_text()
{
    return '';
}
add_filter( 'get_search_form','go_remove_search_form' );
function go_remove_search_form()
{
    $template = $GLOBALS['template'];
    $template_file = substr($template, strrpos($template, '/') + 1);
    if ($template_file === '404.php') {
        return '';
    }
}

add_action( 'edit_terms', 'go_before_update_terms', 10, 2 );
function go_before_update_terms( $term_id, $taxonomy ) {
    // do something after update

    $term = get_term($term_id, $taxonomy);
    $termParent = $term->parent;
    global $originalTermParent;
    $originalTermParent = $termParent;
    //$newParent = $_POST['parent'];

    /*
    if(empty($termParent)){//if there is no current parent, make it the last parent term in the order
        $order = get_term_meta($term_id, 'go_order', true);
        if(empty($order)){
            $myterms = get_terms( array( 'taxonomy' => $taxonomy, 'parent' => 0, 'hide_empty' => false ) );
            $count = count($myterms);
            update_term_meta($term_id, 'go_order', $count+1);
        }
    }

    else if($termParent != $newParent){
        $children = get_term_children( $newParent, $taxonomy );
        $count = count($children);
        update_term_meta($term_id, 'go_order', $count+1);
    }*/
}

add_action( 'edited_term', 'go_after_update_terms', 10, 3 );
function go_after_update_terms( $term_id, $ttid, $taxonomy ) {
    // do something after update

    $term = get_term($term_id, $taxonomy);
    $newParent = $term->parent;
    global $originalTermParent;

    //$newParent = $_POST['parent'];

    if(empty($newParent)){
        $order = get_term_meta($term_id, 'go_order', true);
        if(empty($order)){
            $myterms = get_terms( array( 'taxonomy' => $taxonomy, 'parent' => 0, 'hide_empty' => false ) );
            $count = count($myterms);
            update_term_meta($term_id, 'go_order', $count+1);
        }
    }
    else if($originalTermParent != $newParent){
        $children = get_term_children( $newParent, $taxonomy );
        $count = count($children);
        update_term_meta($term_id, 'go_order', $count+1);
    }
}

add_action( 'create_term', 'go_last_in_order', 10, 3 );
function go_last_in_order( $term_id, $ttid, $taxonomy ) {
    // do something after update
    $term = get_term($term_id, $taxonomy);
    $termParent = $term->parent;
    if(empty($termParent)){
        $order = get_term_meta($term_id, 'go_order', true);
        if(empty($order)){
            $myterms = get_terms( array( 'taxonomy' => $taxonomy, 'parent' => 0, 'hide_empty' => false ) );
            $count = count($myterms);
            update_term_meta($term_id, 'go_order', $count+1);
        }
    }
    else {
        $children = get_term_children( $termParent, $taxonomy );
        $count = count($children);
        update_term_meta($term_id, 'go_order', $count+1);
    }
}

function go_get_all_admin(){
    $users = get_users( 'role=administrator' );
    $user_ids = array();
    foreach($users as $user){
        $user_ids[] = $user->id;
    }
    return $user_ids;
}

function go_get_gold_name(){
    $gold_name = get_option('options_go_loot_gold_name');
    $coins_currency = get_option("options_go_loot_gold_currency");
    if($coins_currency === 'coins') {
        $gold_name = get_option("options_go_loot_gold_coin_names_gold_coin_name");
    }
    return $gold_name;
}

function go_get_link_from_option($option_name){
    $option = get_option($option_name);
    $option = urlencode((string)$option);
    $link = get_site_url(null, $option);
    return $link;
}


/**
 * Can be called as a function or shortcode
 * @param $var //can be an array of atts if shortcode, or just a single variable
 * @param null $message
 * @param false $icon_only
 * @return string
 */
function go_copy_var_to_clipboard($var, $message = null, $icon_only = false){
    if(is_array($var)){
        //$var = $var['content'];
        $var = (isset($var['content']) ?  $var['content'] : null);
        if(empty($var)){
           return;
        }
        $message = (isset($var['message']) ?  $var['message'] : null);
        $icon_only = (isset($var['icon_only']) ?  $var['icon_only'] : false);
    }
    if(empty($message)){
        $message = 'Copy to Clipboard';
    }
    if($icon_only){
        $copy_icon = "  <span onclick='go_copy_to_clipboard(this)' class='tooltip' data-tippy-content='$message'>
                            <span class='tooltip_click' data-tippy-content='Copied!'>
                                <span  style='background-color: white; display:none;' class='go_copy_this'>$var</span> 
                                <i style='' class='fas fa-1x fa-link'></i>
                            </span>
                    </span>";
    }else {
        $copy_icon = "  <span onclick='go_copy_to_clipboard(this)' class='tooltip' data-tippy-content='$message'>
                            <span class='tooltip_click' data-tippy-content='Copied!'>
                                <span class='go_copy_this' style='background-color: white;'>$var</span> 
                                <i style='' class='fas fa-1x fa-link'></i>
                            </span>
                    </span>";
    }
    return $copy_icon;
}
add_shortcode ( 'copy_to_clipboard', 'go_copy_var_to_clipboard' );

// Allow for shortcodes in messages
function go_acf_load_field_message($field  ) {
    $type = get_post_type();
    if ($type !== "acf-field-group") {
        //$field['message'] = do_shortcode($field['message']);
        $field['message'] = apply_filters( 'go_awesome_text', $field['message'] );
        $field['message'] = urldecode($field['message']);

    }
    return $field;
}

add_filter('acf/load_field/type=message', 'go_acf_load_field_message', 10, 3);

function go_customize_register( $wp_customize ) {
    /**
     * Add our Header & Navigation Panel
     */

        $wp_customize->add_panel( 'go_panel',
            array(
                'title' => __( 'Gameful Display' ),
                'description' => esc_html__( 'Adjust the Player Bar and Map display settings.' ), // Include html tags such as
                'priority' => 10, // Not typically needed. Default is 160
                'capability' => 'edit_theme_options', // Not typically needed. Default is edit_theme_options
                'theme_supports' => '', // Rarely needed
                'active_callback' => '', // Rarely needed
            )
        );

    $wp_customize->add_section( 'go_map_controls_section',
        array(
            'title' => __( 'Map Display' ),
            'description' => esc_html__( 'Customize the Map. Navigate to the map page in the preview panel to see your changes before you save them.' ),
            'panel' => 'go_panel',
            'priority' => 10, // Not typically needed. Default is 160
            'capability' => 'edit_theme_options', // Not typically needed. Default is edit_theme_options

        )
    );


    // Test of Slider Custom Control
    $wp_customize->add_setting( 'go_map_font_size_control',
        array(
            'default' => '17',
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'absint'
        )
    );
    $wp_customize->add_control( new Skyrocket_Slider_Custom_Control( $wp_customize, 'go_map_font_size_control',
        array(
            'label' => __( 'Map Font Size (px)', 'go' ),
            'section' => 'go_map_controls_section',
            'settings' => 'go_map_font_size_control',
            'input_attrs' => array(
                'min' => 8,
                'max' => 24,
                'step' => 1,
            ),
        )
    ) );

    $google_fonts_default = json_encode(
        array(
            'font' => 'Open Sans',
            'regularweight' => 'regular',
            'italicweight' => 'italic',
            'boldweight' => '700',
            'category' => 'sans-serif'
        )
    );
    // Test of Google Font Select Control
    $wp_customize->add_setting( 'go_map_google_font_select',
        array(
            'default' => $google_fonts_default,
            //'sanitize_callback' => 'skyrocket_google_font_sanitization',
            'type' => 'option',
        )
    );
    $wp_customize->add_control( new Skyrocket_Google_Font_Select_Custom_Control( $wp_customize, 'go_map_google_font_select',
        array(
            'label' => __( 'Map Font', 'go' ),
            'description' => esc_html__( 'All Google Fonts sorted alphabetically', 'skyrocket' ),
            'section' => 'go_map_controls_section',
            'settings' => 'go_map_google_font_select',
            'input_attrs' => array(
                'font_count' => 'all',
                'orderby' => 'alpha',
            ),
        )
    ) );

    $wp_customize->add_setting( 'go_map_bkg_color',
        array(
            'default' => "#ffffff",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_map_bkg_color',
        array(
            'label' => __( 'Map Background Color', 'go' ),
            //'description' => esc_html__( 'Change the Map Background Color', 'go' ),
            'section' => 'go_map_controls_section',
            'type' => 'color',
        )
    ) ;

    $wp_customize->add_setting( 'go_map_font_color',
        array(
            'default' => "#ffffff",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_map_font_color',
        array(
            'label' => __( 'Map Font Color', 'go' ),
            //'description' => esc_html__( 'Change the Map Font Color', 'go' ),
            'section' => 'go_map_controls_section',
            'type' => 'color',
        )
    ) ;


    $wp_customize->add_setting( 'go_map_chain_color',
        array(
            'default' => "#c3eafb",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_map_chain_color',
        array(
            'label' => __( 'Column Heading Color', 'go' ),
            //'description' => esc_html__( 'Change the Quest Category Color', 'go' ),
            'section' => 'go_map_controls_section',
            'type' => 'color',
        )
    ) ;
    $wp_customize->add_setting( 'go_map_chain_font_color',
        array(
            'default' => "#000000",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_map_chain_font_color',
        array(
            'label' => __( 'Column Heading Font Color', 'go' ),
            //'description' => esc_html__( 'Change the Map Font Color', 'go' ),
            'section' => 'go_map_controls_section',
            'type' => 'color',
        )
    ) ;
    $wp_customize->add_setting( 'go_map_available_color',
        array(
            'default' => "#fff7aa",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_map_available_color',
        array(
            'label' => __( 'Available Quest Color', 'go' ),
            //'description' => esc_html__( 'Change the Available Quest Color', 'go' ),
            'section' => 'go_map_controls_section',
            'type' => 'color',
        )
    ) ;
    $wp_customize->add_setting( 'go_map_available_font_color',
        array(
            'default' => "#000000",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_map_available_font_color',
        array(
            'label' => __( 'Map Available Font Color', 'go' ),
            //'description' => esc_html__( 'Change the Map Font Color', 'go' ),
            'section' => 'go_map_controls_section',
            'type' => 'color',
        )
    ) ;


    $wp_customize->add_setting( 'go_map_done_color',
        array(
            'default' => "#cee3ac",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_map_done_color',
        array(
            'label' => __( 'Finished Quest Color', 'go' ),
            //'description' => esc_html__( 'Change the Finished Quest Color', 'go' ),
            'section' => 'go_map_controls_section',
            'type' => 'color',
        )
    ) ;
    $wp_customize->add_setting( 'go_map_done_font_color',
        array(
            'default' => "#000000",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_map_done_font_color',
        array(
            'label' => __( 'Map Finished Font Color', 'go' ),
            //'description' => esc_html__( 'Change the Map Font Color', 'go' ),
            'section' => 'go_map_controls_section',
            'type' => 'color',
        )
    ) ;


    $wp_customize->add_setting( 'go_map_locked_color',
        array(
            'default' => "#cccccc",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_map_locked_color',
        array(
            'label' => __( 'Locked Quest Color', 'go' ),
            //'description' => esc_html__( 'Change the Locked Quest Color', 'go' ),
            'section' => 'go_map_controls_section',
            'type' => 'color',
        )
    ) ;
    $wp_customize->add_setting( 'go_map_locked_font_color',
        array(
            'default' => "#000000",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_map_locked_font_color',
        array(
            'label' => __( 'Map Locked Font Color', 'go' ),
            //'description' => esc_html__( 'Change the Map Font Color', 'go' ),
            'section' => 'go_map_controls_section',
            'type' => 'color',
        )
    ) ;




    //PLAYERBAR SECTION

    $wp_customize->add_section( 'go_playerbar',
        array(
            'title' => __( 'PlayerBar Colors' ),
            'description' => esc_html__( 'Customize the PlayerBar' ),
            'panel' => 'go_panel',
            'priority' => 160, // Not typically needed. Default is 160
            'capability' => 'edit_theme_options', // Not typically needed. Default is edit_theme_options

        )
    );

    // Another Test of WPColorPicker Alpha Color Picker Control
    $wp_customize->add_setting( 'go_playerbar_bkg_wpcolorpicker',
        array(
            'default' => "#268FBB",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_playerbar_bkg_wpcolorpicker',
        array(
            'label' => __( 'Background Color', 'go' ),
            'description' => esc_html__( 'Change the PlayerBar Background Color', 'go' ),
            'section' => 'go_playerbar',
            'type' => 'color',
        )
    ) ;

    // Another Test of WPColorPicker Alpha Color Picker Control
    $wp_customize->add_setting( 'go_playerbar_link_wpcolorpicker',
        array(
            'default' => "#FFFFFF",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_playerbar_link_wpcolorpicker',
        array(
            'label' => __( 'Link Color', 'go' ),
            'description' => esc_html__( 'Change the PlayerBar Link Color', 'go' ),
            'section' => 'go_playerbar',
            'type' => 'color',
        )
    ) ;

    // Another Test of WPColorPicker Alpha Color Picker Control
    $wp_customize->add_setting( 'go_playerbar_hover_wpcolorpicker',
        array(
            'default' => "#FFFFFF",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_playerbar_hover_wpcolorpicker',
        array(
            'label' => __( 'Link Hover Color', 'go' ),
            'description' => esc_html__( 'Change the PlayerBar Link Hover Color', 'go' ),
            'section' => 'go_playerbar',
            'type' => 'color',
        )
    ) ;

    // Another Test of WPColorPicker Alpha Color Picker Control
    $wp_customize->add_setting( 'go_playerbar_dropdown_wpcolorpicker',
        array(
            'default' => "#268FBB",
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_hex_rgba_sanitization'
        )
    );
    $wp_customize->add_control( 'go_playerbar_dropdown_wpcolorpicker',
        array(
            'label' => __( 'Dropdown Color', 'go' ),
            'description' => esc_html__( 'Change the PlayerBar DropDown Menu Color', 'go' ),
            'section' => 'go_playerbar',
            'type' => 'color',
        )
    ) ;
//***VIDEO SECTION***//
    $wp_customize->add_section( 'go_video_display',
        array(
            'title' => __( 'Videos' ),
            'description' => esc_html__( 'Customize Video Display' ),
            'panel' => 'go_panel',
            'priority' => 160, // Not typically needed. Default is 160
            'capability' => 'edit_theme_options', // Not typically needed. Default is edit_theme_options

        )
    );
    $wp_customize->add_setting( 'go_video_width_type_control',
        array(
            'default' => 'px',
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'absint'
        )
    );
    $wp_customize->add_control( 'go_video_width_type_control',
        array(
            'label' => __( 'Video Width', 'go' ),
            'section' => 'go_video_display',
            'settings' => 'go_video_width_type_control',
            'type' => 'radio',
            'capability' => 'edit_theme_options', // Optional. Default: 'edit_theme_options'
            'choices' => array( // Optional.
                'px' => 'Pixels (px)',
                '%' => 'Percent (%)',

            )
        )
    );
    $wp_customize->add_setting( 'go_video_width_px_control',
        array(
            'default' => '400',
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'absint'
        )
    );
    $wp_customize->add_control( new Skyrocket_Slider_Custom_Control( $wp_customize, 'go_video_width_px_control',
        array(
            'label' => __( 'Video Width (px)', 'go' ),
            'section' => 'go_video_display',
            'settings' => 'go_video_width_px_control',
            'input_attrs' => array(
                'min' => 200,
                'max' => 1000,
                'step' => 10,
            ),
        )
    ) );

    $wp_customize->add_setting( 'go_video_width_percent_control',
        array(
            'default' => '100',
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'absint'
        )
    );
    $wp_customize->add_control( new Skyrocket_Slider_Custom_Control( $wp_customize, 'go_video_width_percent_control',
        array(
            'label' => __( 'Video Width (%)', 'go' ),
            'section' => 'go_video_display',
            'settings' => 'go_video_width_percent_control',
            'input_attrs' => array(
                'min' => 10,
                'max' => 100,
                'step' => 5,
            ),
        )
    ) );

    $wp_customize->add_setting( 'go_video_lightbox_toggle_switch',
        array(
            'default' => 1,
            'transport' => 'refresh',
            'type' => 'option',
            //'sanitize_callback' => 'skyrocket_switch_sanitization'
        )
    );
    $wp_customize->add_control( new Skyrocket_Toggle_Switch_Custom_control( $wp_customize, 'go_video_lightbox_toggle_switch',
        array(
            'label' => __( 'Use Lightbox for Video', 'go' ),
            'section' => 'go_video_display',
            'settings' => 'go_video_lightbox_toggle_switch',
        )
    ) );


}
add_action( 'customize_register', 'go_customize_register', 11 );


function add_custom_css() {
    wp_enqueue_style('custom-css', get_template_directory_uri() . '/custom.css');
    // Add dynamic style if a single page is displayed
    if ( !is_admin() ) {
        $xp_toggle = get_option('options_go_loot_xp_toggle');
        $health_toggle = get_option('options_go_loot_health_toggle');
        $height = 91;
        if(!$xp_toggle){
            $height = $height -16;
        }
        if(!$health_toggle){
            $height = $height -16;
        }
        $color = "#000111";
        $custom_css = "body{ height: {$height}px; }";
        wp_add_inline_style( 'custom-css', $custom_css );

        wp_localize_script(
            'go_frontend',
            'GO_STYLES',
            array(

                    'playerbar_height'          => $height,
            )
        );


    }
}
add_action( 'wp_enqueue_scripts', 'add_custom_css', 99 );


add_action( 'wp_head', 'go_user_bar_dynamic_styles', 99 );
add_action( 'admin_head', 'go_user_bar_dynamic_styles', 99 );
function go_user_bar_dynamic_styles() {

    $bkg_color = get_option('go_playerbar_bkg_wpcolorpicker');
    if(!$bkg_color) {
        $bkg_color = get_option('options_go_user_bar_background_color');//Old Option
    }
    if(!$bkg_color) {
        $bkg_color = "#268FBB";
    }

    $link_color = get_option('go_playerbar_link_wpcolorpicker');
    if(!$link_color) {
        $link_color = get_option('options_go_user_bar_link_color');//Old Option
    }
    if(!$link_color) {
        $link_color = "#FFFFFF";
    }

    $hover_color = get_option('go_playerbar_hover_wpcolorpicker');
    if(!$hover_color) {
        $hover_color = get_option('options_go_user_bar_hover_color');//Old Option
    }
    if(!$hover_color) {
        $hover_color = "#FFFFFF";
    }

    $drop_bkg_color = get_option('go_playerbar_dropdown_wpcolorpicker');
    if(!$drop_bkg_color) {
        $drop_bkg_color = get_option('options_go_user_bar_dropdown_bkg');//Old Option
    }
    if(!$drop_bkg_color) {
        $drop_bkg_color = "#268FBB";
    }

    $map_bkg = get_option('go_map_bkg_color');
    $map_bkg  = ($map_bkg ?  $map_bkg : "#ffffff");

    $map_font_color = get_option('go_map_font_color');
    $map_font_color  = ($map_font_color ?  $map_font_color : "#000000");

    $map_available = get_option('go_map_available_color');
    $map_available  = ($map_available ?  $map_available : "#fff7aa");

    $map_available_font_color = get_option('go_map_available_font_color');
    $map_available_font_color  = ($map_available_font_color ?  $map_available_font_color : "#000000");

    $map_done = get_option('go_map_done_color');
    $map_done  = ($map_done ?  $map_done : "#cee3ac");

    $map_done_font_color = get_option('go_map_done_font_color');
    $map_done_font_color  = ($map_done_font_color ?  $map_done_font_color : "#000000");

    $map_locked = get_option('go_map_locked_color');
    $map_locked  = ($map_locked ?  $map_locked : "#cccccc");

    $map_locked_font_color = get_option('go_map_locked_font_color');
    $map_locked_font_color  = ($map_locked_font_color ?  $map_locked_font_color : "#000000");
//
    $chain_box = get_option('go_map_chain_color');
    $chain_box  = ($chain_box ?  $chain_box : "#c3eafb");

    $go_map_chain_font_color = get_option('go_map_chain_font_color');
    $go_map_chain_font_color  = ($go_map_chain_font_color ?  $go_map_chain_font_color : "#000000");

//
    ?>
    <style type="text/css" media="screen">
        #go_user_bar_top { background-color:<?php echo $bkg_color; ?> !important; color:<?php echo $link_color; ?>; }
        #go_user_bar a:link { color:<?php echo $link_color; ?>; text-decoration: none; }
        #go_user_bar a:visited { color:<?php echo $link_color; ?>; text-decoration: none; }
        #go_user_bar a:hover { color:<?php echo $hover_color; ?>; text-decoration: none; }
        #go_user_bar a:active { color:<?php echo $hover_color; ?>; text-decoration: underline; }
        .progress-bar-border { border-color:<?php echo $link_color; ?>; }
        #go_user_bar .userbar_dropdown-content {background-color: <?php echo $drop_bkg_color; ?>;  color:<?php echo $link_color; ?>; }



        #maps .done {
            background-color: <?php echo $map_done; ?>;
            color: <?php echo $map_done_font_color; ?>; }

        #maps .locked {
            background-color: <?php echo $map_locked; ?>;
            color: <?php echo $map_locked_font_color; ?>; }

        #maps .available,
        .dropbtn, .dropbtn:hover, .dropbtn:focus, #go_Dropdown{
            background-color: <?php echo $map_available; ?>;
            color: <?php echo $map_available_font_color; ?>; }

        #mapwrapper {
            background-color: <?php echo $map_bkg; ?>;
            color: <?php echo $map_font_color; ?>;
        }

        #maps .go_task_chain_map_box{
            background-color: <?php echo $chain_box; ?>;
            color: <?php echo $go_map_chain_font_color; ?>;
        }

    </style>
    <?php

}


/*
function go_show_hidden($user_id =null){
    if(empty($user_id)){
        $user_id = get_current_user_id();
    }
$is_admin = go_user_is_admin();
$show_hidden = false;
if($is_admin){
    $admin_view = get_user_option('go_admin_view', $user_id);
    if($admin_view === 'player'){
        $show_hidden = true;
    }
}
return $show_hidden;
}//
*/

//add_filter( 'option_generate_settings','lh_single_posts_settings' );
function lh_single_posts_settings( $options ) {
    $options['generate_package_typography'] = 'deactivated';
        $options['generate_package_spacing'] = 'deactivated';
        $options['generate_package_site_library'] = 'deactivated';
    $options['generate_package_sections'] = 'deactivated';
    $options['generate_package_secondary_nav'] = 'deactivated';
    $options['generate_package_menu_plus'] = 'deactivated';
    $options['generate_package_elements'] = 'deactivated';
    $options['generate_package_disable_elements'] = 'deactivated';
    $options['generate_package_copyright'] = 'deactivated';
    $options['generate_package_colors'] = 'deactivated';
    $options['generate_package_blog'] = 'deactivated';
    $options['generate_package_backgrounds'] = 'deactivated';
    return $options;
}

function go_leaderboard_filters($type = 'reader', $user_id = null) {

    $is_admin = go_user_is_admin();

    $initial = $badge_ids = (isset($_GET['is_initial_single_stage']) ?  $_GET['is_initial_single_stage'] : false);
    if($initial && !$is_admin){
        $type = 'single_quest';
    }


    $user_id_data = '';
    if ($type === 'reader'){
        $filter_on_change = false;
        $show_action_filters = true;
        $show_user_filters = true;
        $status_filter = true;
        $order_filter = true;
    }
    else if ($type === 'leaderboard'){
        $filter_on_change = true;
        $show_action_filters = false;
        $show_user_filters = true;
        $status_filter = false;
        $order_filter = false;
    }
    else if ($type === 'clipboard'){
        $filter_on_change = false;
        $show_action_filters = true;
        $show_user_filters = true;
        $status_filter = false;
        $order_filter = false;
    }
    else if ($type === 'single_quest'){
        $filter_on_change = false;
        $show_action_filters = false;
        $show_user_filters = true;
        $status_filter = false;
        $order_filter = false;
    }
    else if ($type === 'blog'){
        $filter_on_change = true;
        if($is_admin) {
            $show_action_filters = true;
            $order_filter = true;
        }else{
            $show_action_filters = false;
            $order_filter = false;
        }
        $show_user_filters = false;
        $status_filter = true;
    }
    else {//if($type === 'quest_stage')
        $filter_on_change = true;
        $show_action_filters = false;
        $show_user_filters = true;
        $status_filter = false;
        $order_filter = true;
    }



    //acf_form_head();

    $task_name = get_option( 'options_go_tasks_name_plural'  );

    $post_id = (isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : '');
    $stage = (isset($_REQUEST['stage']) ? $_REQUEST['stage'] : false);
    $is_single_stage = (isset($_REQUEST['is_single_stage']) ? $_REQUEST['is_single_stage'] : false);
    $is_initial_single_stage = (isset($_REQUEST['is_initial_single_stage']) ? $_REQUEST['is_initial_single_stage'] : false);
    if($is_initial_single_stage === 'true'){
        $is_single_stage = true;
    }

    if($post_id){
        $tasks = "data-tasks='{$post_id}'";
        $task_option = "<option value='$post_id' selected>$post_id</option>";
    }else{
        $tasks = '';
        $task_option = '';
    }

    if($stage){
        $stage = "data-stage='{$stage}'";
    }

   /* if(is_numeric($post_id)){
        $post_id = "data-post_id='{$post_id}'";
    }*/

    if($filter_on_change){
        $filter_on_change_data = "data-filter_on_change='true'";
    }else{
        $filter_on_change_data = "data-filter_on_change='false'";
    }

    if(is_numeric($user_id)){
        $user_id_data = "data-user_id='{$user_id}'";
    }






    ?>


    <?php
    //<h3>User Filter</h3>
    //<h3>Blog Post Filter</h3>
    // <div id="go_action_filters_1" style="padding: 0px 20px 20px 20px;">
    //<div id="go_action_filters_2" style="padding: 40px 20px 20px 20px;">

    ?>
<div id="go_leaderboard_filters" style="flex-wrap: wrap;" data-type="<?php echo $type; ?>"  <?php echo " " . $filter_on_change_data . " "  . $stage . " "  . $user_id_data . " "  . $tasks; ?>>
    <?php
    echo "<h3>Filters</h3>";
    if($show_user_filters) {
        ?>
        <div id="go_user_filters">

            <div id="go_user_filters_1" style="display: flex;"
            ">
            <div class="user_filter"><label
                        for="go_reader_user_go_sections_select">Section </label><?php go_make_tax_select('user_go_sections', 'reader', false, false, true); ?>
            </div>
            <div class="user_filter"><label
                        for="go_reader_user_go_groups_select">Group </label><?php go_make_tax_select('user_go_groups', 'reader', false, false, true); ?>
            </div>
            <div class="user_filter"><label
                        for="go_reader_go_badges_select">Badge </label><?php go_make_tax_select('go_badges', 'reader', false, false, true); ?>
            </div>
        </div>
        </div>
        <?php
    }

    if($show_action_filters) {
        $display = 'display:flex;';
    }else{
        $display = 'display:none;';
    }
        ?>
        <div id="go_action_filters" style="<?php echo $display; ?>">
            <?php
            if($is_single_stage ) {
                $display = 'display:none;';
            }else{
                $display = 'display:flex;';
            }
            ?>

            <div id="go_datepicker_container"
                 style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 400px; <?php echo $display; ?> ">
                <div id="go_datepicker_clipboard">
                    <i class="fa fa-calendar" style="float: left;"></i>&nbsp;
                    <span id="go_datepicker"></span> <i id="go_reset_datepicker" class=""
                                                        select2-selection__clear><b> × </b></i><i
                            class="fa fa-caret-down"></i>
                </div>
            </div>


            <div style="padding-top: 10px; <?php echo $display; ?>">
                                <span id="go_task_filters"><label
                                            for="go_clipboard_task_select"><?php echo $task_name; ?> </label><select
                                            id="go_task_select" class="js-store_data"
                                            style="width:250px;" ><?php echo $task_option; ?></select></span>
            </div>

        </div>
            <?php
        //END OF ACTION FILTERS




        if($status_filter){
            ?>
            <div style="width: 100%;
    display: flex;
    flex-wrap: wrap;">
            <div class="status_filters">
                <?php
                if($is_admin){
                    $action = (isset($_GET['action']) ? $_GET['action'] : false);
                    if($action === 'go_filter_reader'){
                        $unread = 'checked';
                        $read = 'checked';
                    }else if($type === 'reader'){
                        $unread = 'checked';
                        $read = '';
                    }else if($type === 'blog'){
                        $unread = 'checked';
                        $read = 'checked';
                    }else{
                        $read = 'checked';
                        $unread = '';
                    }

                    ?>
                    <input type="checkbox" id="go_reader_unread" class="go_reader_input" value="unread"
                           <?php echo $unread;?>><label for="go_reader_unread">Unread </label>
                    <input type="checkbox" id="go_reader_read" class="go_reader_input" value="read" <?php echo $read;?>><label
                            for="go_reader_read">Read </label>
                    <?php
                }
                else {
                    ?>
                    <input type="checkbox" id="go_reader_published" class="go_reader_input" value="go_reader_published"
                           checked><label for="go_reader_go_reader_published">Published </label>
                    <?php
                }
                    ?>

                <input type="checkbox" id="go_reader_reset" class="go_reader_input" value="reset"><label
                        for="go_reader_reset">Reset </label>
                <input type="checkbox" id="go_reader_trash" class="go_reader_input" value="trash"><label
                        for="go_reader_trash">Trash </label>
                <input type="checkbox" id="go_reader_draft" class="go_reader_input" value="draft"><label
                        for="go_reader_draft">Draft </label>
            </div>


<?php
if($order_filter){

    $oldest = '';
    $newest = '';
    $action = (isset($_GET['action']) ? $_GET['action'] : false);
    if($action === 'go_filter_reader'){
        $oldest = 'checked';
    }else if($type === 'reader'){
        $oldest = 'checked';
    }else if($type === 'blog'){
        $newest = 'checked';
    }else{
        $oldest = 'checked';
    }
    ?>
    <div class="order_filter">
        <input type="radio" id="go_reader_order_oldest" class="go_reader_input" name="go_reader_order"
               value="ASC" <?php echo $oldest; ?>><label for="go_reader_order_oldest"> Oldest First</label>
        <input type="radio" id="go_reader_order_newest" class="go_reader_input" name="go_reader_order"
               value="DESC" <?php echo $newest; ?>><label for="go_reader_order_newest"> Newest First</label>
        <span class="tooltip"
              data-tippy-content="Posts are sorted by the last modified time."><span><i
                        class="fa fa-info-circle"></i></span> </span>
    </div>
    <?php

}
?>

            </div>
            <?php
        }


    if(!$filter_on_change) {
        ?>
        <div style="width: 100%;
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;">
        <?php
        if($order_filter){

            $oldest = '';
            $newest = '';
            $action = (isset($_GET['action']) ? $_GET['action'] : false);
            if($action === 'go_filter_reader'){
                $oldest = 'checked';
            }else if($type === 'reader'){
                $oldest = 'checked';
            }else if($type === 'blog'){
                $newest = 'checked';
            }else{
                $oldest = 'checked';
            }

        }
        ?>
        <div id="go_leaderboard_update_button"
             style="padding:20px; display: flex;">
            <div style="margin-right: 30px; float:left;">
                <button class="go_reset_filters dt-button ui-button ui-state-default ui-button-text-only buttons-collection">
                        <span class="ui-button-text">Clear Filters <i class="fa fa-undo"
                                                                      aria-hidden="true"></i></span>
                </button>
            </div>
            <div style="">
                <button class="go_apply_filters dt-button ui-button ui-state-default ui-button-text-only buttons-collection">
                    <span class="ui-button-text">Refresh Data <i class="fa fa-refresh"
                                                                 aria-hidden="true"></i></span></button>
            </div>
        </div>
        </div>
        <?php
    }

    ?>

    </div>


    <?php

}

//gets the task id from a Blog post Id
//works for v4 and v5 blog_posts
function go_get_task_id($blog_post_id){
    global $wpdb;
    $aTable = "{$wpdb->prefix}go_actions";
    $task_id = wp_get_post_parent_id($blog_post_id);

    if(!$task_id){
        $task_id = get_post_meta($blog_post_id, 'go_blog_task_id', true);
        //$go_blog_task_id = (isset($blog_meta['go_blog_task_id'][0]) ? $blog_meta['go_blog_task_id'][0] : null);
    }
    if(!$task_id) {
        $task_id = $wpdb->get_var($wpdb->prepare("SELECT source_id
				FROM {$aTable} 
				WHERE result = %d AND  action_type = %s
				ORDER BY id DESC LIMIT 1",
            intval($blog_post_id),
            'task'));
    }
    return $task_id;
}