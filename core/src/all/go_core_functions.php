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

    if ($post_id == 0){
        if ($print){
            echo get_admin_url(null, 'post-new.php?post_type=tasks');
            die();
        }
    }
    /*
     * and all the original post data then
     */
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
        $new_post_id = wp_insert_post( $args );

        /*
         * get all current post terms ad set them to the new post draft
         */
        $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
        foreach ($taxonomies as $taxonomy) {
            $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
            wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
        }

        /*
         * duplicate all post meta just in two SQL queries
         */
        $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
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
function go_make_tax_select ($taxonomy, $is_lightbox = false, $value = false, $value_name = false, $activate_filter = false){
    if ($is_lightbox){
        $location = 'lightbox';
    }
    else{
        $location = 'page';
    }

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


function go_get_user_display_name($user_id = ''){
    if(empty($user_id)){
        $user_id = get_current_user_id();
    }
        $user_display_name = '';
    if(!empty($user_id)) {
        $user_display_name = get_user_option('go_nickname', $user_id);
        if (empty($user_display_name)) {
            $user_display_name = get_user_meta($user_id, 'nickname', true);
            if (empty($user_display_name)) {
                $current_user = get_userdata($user_id);

                $user_display_name = $current_user->display_name;
            }
        }
    }
    return $user_display_name;
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
add_filter ('get_avatar', 'go_override_avatar', 1, 5);


function go_acf_labels( $field ) {
    //badges
    //groups
    $text = $field['label'];
    preg_match_all("/\[[^\]]*\]/", $text, $matches);
    $my_matches = $matches[0];

    foreach($my_matches as $match){
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
        if(!empty($replace_with)){
            $field['label'] = str_replace($match, $replace_with, $field['label']);
        }
    }

    return $field;

}
add_filter('acf/prepare_field', 'go_acf_labels');


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
    $termParent = ($term->parent == 0) ? $term : $term->parent;
    $newParent = $_POST['parent'];
    if($termParent != $newParent){
        $children = get_term_children( $newParent, $taxonomy );
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
                                <span  style='background-color: white; padding:5px; display:none;' class='go_copy_this'>$var</span> 
                                <i style='font-size: 1em; ' class='fas fa-1x fa-clipboard-list'></i>
                            </span>
                    </span>";
    }else {
        $copy_icon = "  <span onclick='go_copy_to_clipboard(this)' class='tooltip' data-tippy-content='$message'>
                            <span class='tooltip_click' data-tippy-content='Copied!'>
                                <span class='go_copy_this' style='background-color: white; padding:5px;'>$var</span> 
                                <i style='font-size: 1.3em;' class='fas fa-1x fa-clipboard-list'></i>
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