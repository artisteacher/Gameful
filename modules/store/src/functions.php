<?php
/*
Module Name: Super Store
Description: Creates a store for CubeGold using a custom content type! Nifty Huh?
Author: Vincent Astolfi (vincentastolfi)
Contributing Author: Semar Yousif
Creation Date: 05/09/13
*/
// Includes

//include('includes/lightbox/backend-lightbox.php');


//https://stackoverflow.com/questions/25310665/wordpress-how-to-create-a-rewrite-rule-for-a-file-in-a-custom-plugin
add_action('init', 'go_store_page');
function go_store_page(){
    if(is_gameful() && is_main_site()){
    	$hide = true;
    }
    else{
    	$hide = false;
    }
    if (!$hide) {
        $store_name = get_option('options_go_store_store_link');
        //add_rewrite_rule( "store", 'index.php?query_type=user_blog&uname=$matches[1]', "top");
        add_rewrite_rule($store_name, 'index.php?' . $store_name . '=true', "top");
    }
}

// Query Vars
add_filter( 'query_vars', 'go_store_register_query_var' );
function go_store_register_query_var( $vars ) {
    $store_name = get_option( 'options_go_store_store_link');
    $vars[] = $store_name;
    return $vars;
}


/* Template Include */
add_filter('template_include', 'go_store_template_include', 1, 1);
function go_store_template_include($template)
{
    if(is_gameful() && is_main_site()){
    	$hide = true;
    }
    else{
    	$hide = false;
    }
    if (!$hide) {
        global $wp_query; //Load $wp_query object
        $store_name = get_option('options_go_store_store_link');

        $page_value = (isset($wp_query->query_vars[$store_name]) ? $wp_query->query_vars[$store_name] : false); //Check for query var "blah"
        if ($page_value && $page_value == "true") { //Verify "blah" exists and value is "true".
            return plugin_dir_path(__FILE__) . 'templates/go_store_template.php'; //Load your template or file
        }
    }
    return $template; //Load normal template when $page_value != "true" as a fallback
}


function go_make_store_new() {

    echo "<div id='go_store_container' style='padding:30px;  background-color: white;'>";
    $store_title = get_option( 'options_go_store_title');
    echo "<h1>{$store_title}</h1>";

    $html = get_option('go_store_html');
    if(empty($html)){
        $html = go_make_store_html();

        update_option( 'go_store_html', $html );
    }
    echo $html;
    echo "</div>";
}
add_shortcode('go_make_store', 'go_make_store_new');



function go_register_store_tax_and_cpt() {
	
	/*
	 * Store Types Taxonomy
	 */
	$store_name = get_option( 'options_go_store_name' );
	$cat_labels = array(
		'name' => _x( ' Categories', 'store_types' ),
		'singular_name' => _x( $store_name.' Item Category', 'store_types' ),
		'search_items' =>  _x( 'Search '.$store_name.' Categories' , 'store_types'),
		'all_items' => _x( 'All '.$store_name.' Categories', 'store_types' ),
		'parent_item' => _x( $store_name.' Section (Set as none to make this a new store section)' , 'store_types'),
		'parent_item_colon' => _x( $store_name.' Section (Set as none to make this a new store section):' , 'store_types'),
		'edit_item' => _x( 'Edit '.$store_name.' Category' , 'store_types'),
		'update_item' => _x( 'Update '.$store_name.' Category' , 'store_types'),
		'add_new_item' => _x( 'Add New '.$store_name.' Category' , 'store_types'),
		'new_item_name' => _x( 'New '.$store_name.' Category' , 'store_types'),
	);
    $cat_args = array(
        'labels' => $cat_labels,
        'public' => true,
        'show_in_nav_menus' => false,
        'show_in_menu' => true,
        'show_ui' => true,
        'show_tagcloud' => true,
        'show_admin_column' => false,
        'hierarchical' => true,
        'rewrite' => true,
        'query_var' => true
    );
    register_taxonomy( 'store_types', array( 'go_store' ), $cat_args );


	/*
	 * Store Custom Post Type
	 */
	 
	$labels_cpt = array(
		'name' => _x( $store_name , 'store-types'),
		'menu_name' => _x( $store_name , 'store-types'),
		'singular_name' => _x( $store_name.' Item' , 'store-types'),
		'add_new' => _x( 'New '.$store_name.' Item' , 'store-types'),
		'add_new_item' => _x( 'New '.$store_name.' Item' , 'store-types'),
		'edit' => _x( 'Edit '.$store_name.' Items' , 'store-types'),
		'edit_item' => _x( 'Edit '.$store_name.' Item' , 'store-types'),
		'new_item' => _x( 'New '.$store_name.' Item' , 'store-types'),
		'view' => _x( 'View Items' , 'store-types'),
		'view_item' => _x( 'View '.$store_name.' Item' , 'store-types'),
		'search_items' => _x( 'Search '.$store_name.' Items' , 'store-types'),
		'not_found' => _x( 'No '.$store_name.' Items found' , 'store-types'),
		'not_found_in_trash' => _x( 'No '.$store_name.' Items found in Trash' , 'store-types'),
		'parent' => 'Parent Store Item',
		'name_admin_bar'        => _x( $store_name , 'store-types'),
		'archives'              => 'Item Archives',
		'attributes'            => 'Item Attributes',
		'parent_item_colon'     => 'Parent Item:',
		'all_items'             => 'Item List',
		'update_item'           => 'Update Item',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'uploaded_to_this_item' => 'Uploaded to this item',
		'items_list'            => 'Items list',
		'items_list_navigation' => 'Items list navigation',
		'filter_items_list'     => 'Filter items list',
	);
	$args = array(
        'labels' => $labels_cpt,
		'hierarchical' => false,
		'description' => _x( $store_name , 'store-types'),
        'supports'              => array( 'title', 'comments' ),
		'taxonomies' => array(''),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 20,
		'menu_icon' => '',
		'show_in_nav_menus' => true,
		'exclude_from_search' => false,
		'has_archive' => true,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => true ,
		'capability_type' => 'post'

	);
	register_post_type( 'go_store', $args );
}
add_action( 'init', 'go_register_store_tax_and_cpt', 0 );

/**
 * Update store on post save, delete or trash
 * @param  integer $post_id Current post ID
 * @return integer          Current post ID
 */
function go_update_store_post_save( $post_id ) {
    $post = get_post( $post_id );
    // Check for post type.
    if ( 'go_store' !== $post->post_type ) {
        return;
    }
    //delete task data transient
    $key = 'go_post_data_' . $post_id;
    delete_transient($key);

    $html = go_make_store_html();

    update_option( 'go_store_html', $html );

}

add_action( 'wp_trash_post', 'go_update_store_post_save' );
add_action( 'deleted_post', 'go_update_store_post_save' );
add_action( 'save_post', 'go_update_store_post_save');


function go_fix_store_item_count( $post_id ) {
    $post = get_post( $post_id );
    // Check for post type.
    if ( 'go_store' !== $post->post_type ) {
        return;
    }

    $term_ids = wp_get_object_terms( $post_id, 'store_types', 'ids' );
    wp_delete_object_term_relationships($post_id, 'store_types');
    wp_update_term_count( $term_ids, 'store_types', true );

}
add_action( 'deleted_post', 'go_fix_store_item_count' );


/**
 * Update store on store term
 * @param  integer $post_id Current post ID
 * @return integer          Current post ID
 */
function go_update_store_term_save( $term_id ) {

    //$html = go_make_store_html();

    delete_option( 'go_store_html' );
}

add_action( "delete_store_types", 'go_update_store_term_save', 10, 4 );
add_action( "create_store_types", 'go_update_store_term_save', 10, 4 );
add_action( "edit_store_types", 'go_update_store_term_save', 10, 4 );


/* No Idea What This Does!
function go_new_item_permalink( $return, $post_id, $new_title, $new_slug ) {
	if ( strpos( $return, 'edit-slug' ) !== false ) {
		$return .= '<span id="edit-slug button button-small hide-if-no-js"><a href="javascript:void(0)" onclick = "document.getElementById(\'go_lightbox\' ).style.display=\'block\';document.getElementById(\'fade\' ).style.display=\'block\'" class="button button-small" >Insert </a></span>';
		return $return;
	}
}
add_filter( 'get_sample_permalink_html', 'go_new_item_permalink', 5, 4 );
*/


function go_make_store_html() {

    //$args = array('hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC', 'parent' => '0');

    /* Get all task chains with no parents--these are the sections of the store.  */
    $taxonomy = 'store_types';

    $rows = go_get_terms_ordered($taxonomy, '0');
    ob_start();
    echo '
        <div id="storemap" style="display:block;">';

    /* For each Store Category with no parent, get all the children.  These are the store rows.*/
    $chainParentNum = 0;
    echo '<div id="store">';
    //for each row
    foreach ($rows as $row) {
        $chainParentNum++;
        $row_id = $row->term_id;//id of the row
        $custom_fields = get_term_meta($row_id);
        $cat_hidden = (isset($custom_fields['go_hide_store_cat'][0]) ? $custom_fields['go_hide_store_cat'][0] : null);
        if ($cat_hidden == true) {
            continue;
        }


        echo "<div id='row_$chainParentNum' class='store_row_container'>
                            <div class='parent_cat'><h2>$row->name</h2></div>
                            <div class='store_row'>
                            ";//row title and row container


        //$column_args = array('hide_empty' => false, 'orderby' => 'order', 'order' => 'ASC', 'parent' => $row_id,);

        //$columns = get_terms($taxonomy, $column_args);
        $columns = go_get_terms_ordered($taxonomy, $row_id);
        /*Loop for each chain.  Prints the chain name then looks up children (quests). */
        foreach ($columns as $column) {
            $column_id = $column->term_id;
            $custom_fields = get_term_meta($column_id);
            $cat_hidden = (isset($custom_fields['go_hide_store_cat'][0]) ? $custom_fields['go_hide_store_cat'][0] : null);
            if ($cat_hidden == true) {
                continue;
            }


            echo "<div class ='store_cats'><h3>$column->name</h3><ul class='store_items'>";
            /*Gets a list of store items that are assigned to each chain as array. Ordered by post ID */

            ///////////////
            ///

            $args = array('tax_query' => array(array('taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => $column_id,)), 'orderby' => 'meta_value_num', 'order' => 'ASC', 'posts_per_page' => -1, 'meta_key' => 'go-store-location_store_item', 'meta_value' => '', 'post_type' => 'go_store', 'post_mime_type' => '', 'post_parent' => '', 'author' => '', 'author_name' => '', 'post_status' => 'publish', 'suppress_filters' => true);


            $go_store_objs = get_posts($args);

            //////////////////
            /// ////////////////////
            //$go_store_ids = get_objects_in_term( $column_id, $taxonomy );

            /*Only loop through for first item in array.  This will get the correct order
            of items from the post metadata */

            if (!empty($go_store_objs)) {

                foreach ($go_store_objs as $go_store_obj) {

                    $status = get_post_status($go_store_obj);



                    if ($status !== 'publish') {
                        continue;
                    }
                    $store_item_id = $go_store_obj->ID;

                    $custom_fields = go_post_meta( $store_item_id );

                    $xp_toggle = (isset($custom_fields['go_loot_reward_toggle_xp'][0]) ?  $custom_fields['go_loot_reward_toggle_xp'][0] : null);
                    $xp_value = (isset($custom_fields['go_loot_loot_xp'][0]) ?  $custom_fields['go_loot_loot_xp'][0] : null);
                    $gold_toggle = (isset($custom_fields['go_loot_reward_toggle_gold'][0]) ?  $custom_fields['go_loot_reward_toggle_gold'][0] : null);
                    $gold_value = (isset($custom_fields['go_loot_loot_gold'][0]) ?  $custom_fields['go_loot_loot_gold'][0] : null);
                    $health_toggle = (isset($custom_fields['go_loot_reward_toggle_health'][0]) ?  $custom_fields['go_loot_reward_toggle_health'][0] : null);
                    $health_value = (isset($custom_fields['go_loot_loot_health'][0]) ?  $custom_fields['go_loot_loot_health'][0] : null);

                    $store_item_name = get_the_title($go_store_obj);
                    //echo "<li><a id='$row' class='go_str_item' onclick='go_lb_opener(this.id);'>$store_item_name</a></li> ";
                    echo "<li><div><a id='$store_item_id' class='go_str_item' >$store_item_name</a></div>";
                    echo "<div class='go_store_loot_list'>";
                    if (!empty($xp_value)){
                        if ($xp_toggle == 1 ){
                            $loot_class = 'go_store_loot_list_reward';
                            $loot_direction = "+";

                        }
                        else{
                            $loot_class = 'go_store_loot_list_cost';
                            $loot_direction = "-";
                        }
                        $xp_value = go_display_shorthand_currency('xp', $xp_value);
                        echo "<div id = 'go_store_loot_list_xp' class='go_store_loot_list_item " . $loot_class . "' >" . $loot_direction . $xp_value ."</div > ";
                    }

                    if (!empty($gold_value)){
                        if ($gold_toggle == 1 ){
                            $loot_class = 'go_store_loot_list_reward';
                            $loot_direction = "+";
                        }
                        else{
                            $loot_class = 'go_store_loot_list_cost';
                            $loot_direction = "-";
                        }
                        $gold_value = go_display_shorthand_currency('gold', $gold_value);
                        echo "<div id = 'go_store_loot_list_gold' class='go_store_loot_list_item " . $loot_class . "' >"  . $loot_direction . $gold_value . "</div > ";
                    }

                    if (!empty($health_value)){
                        if ($health_toggle == 1 ){
                            $loot_class = 'go_store_loot_list_reward';
                            $loot_direction = "+";
                        }
                        else{
                            $loot_class = 'go_store_loot_list_cost';
                            $loot_direction = "-";
                        }
                        $health_value = go_display_shorthand_currency('health', $health_value);
                        echo "<div id = 'go_store_loot_list_health' class='go_store_loot_list_item " . $loot_class . "' >" . $loot_direction . $health_value . "</div > ";
                    }

                    echo "</div>";
                    echo "</li> ";
                    //echo "<button id='$row' class='go_str_item' >$store_item_name</button> ";
                }
            }
            echo "</ul></div> ";
        }
        echo "</div></div> ";
    }
    echo "</div></div>";
    $store_html = ob_get_contents();
    ob_end_clean();

    return $store_html;
}


