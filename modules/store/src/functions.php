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
/*
    $user_id = get_current_user_id();

    $admin_view = 'player';
    if (function_exists ( 'wu_is_active_subscriber' )){
        if(wu_is_active_subscriber($user_id) && is_gameful()){
            if ( go_user_is_admin()) {
                $admin_view = get_user_option('go_admin_view', $user_id);
            }else {
                $admin_view = 'clone';
            }
        }
    }
    ?>
    <script>
        jQuery(document).ready(function() {
            jQuery('#go_store_container').addClass('<?php echo $admin_view; ?>')
        });
    </script>
<?php
*/



    echo "<div id='go_store_container' style='padding:30px;  background-color: white;'>";
    $store_title = get_option( 'options_go_store_title');
    echo "<h1>{$store_title}</h1>";
    $store_header = get_option( 'options_go_store_store_header');
    if($store_header){
        $store_header = apply_filters( 'go_awesome_text', $store_header );
        echo $store_header;
    }


    $show_clone = false;
    if (function_exists ( 'wu_is_active_subscriber' ) ){
        if((wu_is_active_subscriber()) && is_gameful()) {
           $show_clone = true;
        }
    }

    if($show_clone){
        $html = go_make_store_html();
        update_option('go_store_html', $html);
        $html = go_make_store_html(true);
    }
    else{
        $html = get_option('go_store_html');
        if(empty($html)){
            $html = go_make_store_html();

            update_option( 'go_store_html', $html );
        }
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
        'menu_name' => _x($store_name.' Categories', 'store_types' ),
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
    register_taxonomy( 'store_types', array(''  ), $cat_args );


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
		'taxonomies' => array('store_types'),
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
    go_delete_transient($key);

    $html = go_make_store_html();

    //delete_option('go_store_html');
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


function go_make_store_html($show_clone = false) {

    $user_id = get_current_user_id();
    $is_admin = go_user_is_admin();
    $is_admin_any_other_blog = go_user_is_admin_on_any_other_blog();
    if (function_exists ( 'wu_is_active_subscriber' ) ) {
        $is_subscriber = wu_is_active_subscriber($user_id);
    }else{
        $is_subscriber = false;
    }

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


        echo "<div id='row_$chainParentNum' class='store_row_container '>";







        $user_id = get_current_user_id();
        $admin_view = go_get_admin_view($user_id);
        echo "<div class='parent_cat go_show_actions'><h2>$row->name</h2>";

        if(($is_subscriber || $is_admin || $is_admin_any_other_blog) && ($admin_view === 'admin' || $is_admin_any_other_blog)){
            echo "<div class='actions_tooltip' style='display: none;'><div class='my_tooltip'><div class='go_actions_wrapper_flex'>";
            if(is_gameful()){
                do_action('gop_add_importer_icon', $row_id, 'term', $user_id, false);
            }
            if($is_admin) {
                $url = get_edit_term_link($row_id, 'store_types', 'go_store');
                echo "<div><a href='{$url}'><i class='fas fa-edit'></i></a></div>";
            }
            echo "</div></div></div>";
        }




        echo"</div>
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

            echo "<div class ='store_cats'>";

            echo "<div class='go_show_actions'><h3>$column->name</h3>";

            if(($is_subscriber || $is_admin || $is_admin_any_other_blog) && ($admin_view === 'admin' || $is_admin_any_other_blog)){
                echo "<div class='actions_tooltip' style='display: none;'><div class='my_tooltip'><div class='go_actions_wrapper_flex'>";
                if(is_gameful()){
                    do_action('gop_add_importer_icon', $column_id, 'term', $user_id, false);
                }
                if($is_admin) {
                    $url = get_edit_term_link($column_id, 'store_types', 'go_store');
                    echo "<div><a href='{$url}'><i class='fas fa-edit'></i></a></div>";
                }
                echo "</div></div></div>";
            }

            echo "</div><ul class='store_items'>";
            /*Gets a list of store items that are assigned to each chain as array. Ordered by post ID */

            ///////////////
            ///

  /*          $args = array(
                'post_type' => 'go_store',
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
                'posts_per_page' => -1,
                'meta_key' => 'go-store-location_store_item',
                'post_status' => 'publish',
                'suppress_filters' => true,
                'tax_query' => array(
                    array(
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => $column_id,)),

                'meta_value' => '',

                'post_mime_type' => '',
                'post_parent' => '',
                'author' => '',
                'author_name' => '',
               );

*/


            //$go_store_objs = get_posts($args);
            $go_store_objs = go_get_ordered_posts($column_id);

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
                    $item_toggle = (isset($custom_fields['go-store-location_store-sec_toggle'][0]) ?  $custom_fields['go-store-location_store-sec_toggle'][0] : null);
                    if(!$item_toggle){
                        continue;
                    }
                    $xp_toggle = (isset($custom_fields['go_loot_reward_toggle_xp'][0]) ?  $custom_fields['go_loot_reward_toggle_xp'][0] : null);
                    $xp_value = (isset($custom_fields['go_loot_loot_xp'][0]) ?  $custom_fields['go_loot_loot_xp'][0] : null);
                    $gold_toggle = (isset($custom_fields['go_loot_reward_toggle_gold'][0]) ?  $custom_fields['go_loot_reward_toggle_gold'][0] : null);
                    $gold_value = (isset($custom_fields['go_loot_loot_gold'][0]) ?  $custom_fields['go_loot_loot_gold'][0] : null);
                    $health_toggle = (isset($custom_fields['go_loot_reward_toggle_health'][0]) ?  $custom_fields['go_loot_reward_toggle_health'][0] : null);
                    $health_value = (isset($custom_fields['go_loot_loot_health'][0]) ?  $custom_fields['go_loot_loot_health'][0] : null);

                    $store_item_name = get_the_title($go_store_obj);
                    //echo "<li><a id='$row' class='go_str_item' onclick='go_lb_opener(this.id);'>$store_item_name</a></li> ";
                    echo "<li class='go_show_actions'><div>";

                    if(($is_subscriber || $is_admin || $is_admin_any_other_blog) && ($admin_view === 'admin' || $is_admin_any_other_blog)){
                        echo "<div class='actions_tooltip' style='display: none;'><div class='my_tooltip'><div class='go_actions_wrapper_flex'>";
                        if(is_gameful()){
                            do_action('gop_add_importer_icon', $store_item_id, 'post', $user_id, false);
                        }
                        if($is_admin) {
                            $url = get_edit_post_link($store_item_id);
                            echo "<div><a href='{$url}'><i class='fas fa-edit'></i></a></div>";
                        }
                        echo "</div></div></div>";
                    }

                    echo "<a id='$store_item_id' class='go_str_item ' >$store_item_name</a>";

                    /*     echo "<span class='go_actions_wrapper' style='display: none;'>";
                    do_action('gop_add_importer_icon', $store_item_id, 'post', null, false);
                    if($is_admin) {
                        $url = get_edit_post_link($store_item_id, 'go_store');
                        echo "<span><a href='{$url}'><i class='fas fa-edit'></i></a></span>";
                    }
                    echo "</span>";*/




                    echo "</div><div class='go_store_loot_list'>";
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


