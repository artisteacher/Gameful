<?php



//ADMIN STUFF
/**
 * Display a custom taxonomy dropdown in admin
 * @author Mike Hemberger
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
add_action('restrict_manage_posts', 'go_filter_tasks_by_taxonomy');
function go_filter_tasks_by_taxonomy() {
    global $typenow;
    $post_type = 'tasks'; // change to your post type
    $taxonomy  = 'task_chains'; // change to your taxonomy
    if ($typenow == $post_type) {
        $selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
        $info_taxonomy = get_taxonomy($taxonomy);
        wp_dropdown_categories(array(
            'show_option_all' => sprintf( __( 'Show all %s', 'textdomain' ), $info_taxonomy->label ),
            'taxonomy'        => $taxonomy,
            'name'            => $taxonomy,
            'orderby'         => 'name',
            'selected'        => $selected,
            'show_count'      => false,
            'hide_empty'      => false,
        ));
    };
}

/**
 * Filter posts by taxonomy in admin
 * @author  Mike Hemberger
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
add_filter('parse_query', 'go_convert_task_id_to_term_in_query');
function go_convert_task_id_to_term_in_query($query) {
    if ( ! is_admin() ){
        return;
    }

    global $pagenow;
    $post_type = 'tasks'; // change to your post type
    $taxonomy  = 'task_chains'; // change to your taxonomy
    $q_vars    = &$query->query_vars;
    if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
        $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
        $q_vars[$taxonomy] = $term->slug;
    }
}

/**
 *
 */
function manage_term_columns(){


    add_filter( 'manage_edit-task_chains_columns', function ( $columns ) {
        $columns['hidden_term'] = __( 'Hidden', 'my-plugin' );
        $columns['pod_toggle'] = __( 'Pod', 'my-plugin' );
        $columns['pod_done_num'] = __( '# Needed', 'my-plugin' );
        $badge_name = ucwords(get_option('options_go_badges_name_plural'));
        $columns['pod_achievement'] = __( $badge_name, 'my-plugin' );
        return $columns;
    });


    /**
     * TASK CHAINS EDIT COLUMNS AND FIELDS
     *
     */
//remove description column
    add_filter('manage_edit-task_chains_columns', function ( $columns ) {
        if( isset( $columns['description'] ) )
            unset( $columns['description'] );
        if( isset( $columns['slug'] ) )
            unset( $columns['slug'] );
        return $columns;
    });

    /**
     * BADGES EDIT COLUMNS AND FIELDS
     *
     */
//remove description column
    add_filter('manage_edit-go_badges_columns', function ( $columns ) {
        if( isset( $columns['description'] ) )
            unset( $columns['description'] );
        if( isset( $columns['slug'] ) )
            unset( $columns['slug'] );
        if( isset( $columns['posts'] ) )
            unset( $columns['posts'] );
        return $columns;
    });

    /**
     * Groups EDIT COLUMNS AND FIELDS
     *
     */
//remove description column
    add_filter('manage_edit-user_go_sections_columns', function ( $columns ) {
        if( isset( $columns['description'] ) )
            unset( $columns['description'] );
        if( isset( $columns['slug'] ) )
            unset( $columns['slug'] );
        return $columns;
    });
//remove count column

    add_filter('manage_edit-user_go_sections_columns', function ( $columns ) {
        if( isset( $columns['posts'] ) )
            unset( $columns['posts'] );
        return $columns;
    });

    /**
     * STORE ITEMS EDIT COLUMNS AND FIELDS
     *
     */
//remove description column

//remove slug column
    add_filter('manage_edit-store_types_columns', function ( $columns ) {
        if( isset( $columns['slug'] ) )
            unset( $columns['slug'] );
        if( isset( $columns['description'] ) )
            unset( $columns['description'] );
        $columns['hidden_term'] = __( 'Hidden', 'my-plugin' );
        return $columns;
        return $columns;
    });
//remove count column



    /**
     * JOBS EDIT COLUMNS AND FIELDS
     *
     */

//remove description column
    add_filter('manage_edit-task_focus_categories_columns', function ( $columns ) {
        if( isset( $columns['description'] ) )
            unset( $columns['description'] );
        if( isset( $columns['slug'] ) )
            unset( $columns['slug'] );
        if( isset( $columns['posts'] ) )
            unset( $columns['posts'] );
        return $columns;
    });


    /**
     * SIDE MENU EDIT COLUMNS AND FIELDS
     *
     */
//remove description column
    add_filter('manage_edit-task_categories_columns', function ( $columns ) {
        if( isset( $columns['description'] ) )
            unset( $columns['description'] );
        if( isset( $columns['slug'] ) )
            unset( $columns['slug'] );
        return $columns;
    });


    /**
     * TOP MENU EDIT COLUMNS AND FIELDS
     *
     */
//remove description column
    add_filter('manage_edit-task_menus_columns', function ( $columns ) {
        if( isset( $columns['description'] ) )
            unset( $columns['description'] );
        if( isset( $columns['slug'] ) )
            unset( $columns['slug'] );
        return $columns;
    });


    /**
     * USER GROUPS EDIT COLUMNS AND FIELDS
     *
     */
//remove slug column
    add_filter('manage_edit-user_go_groups_columns', function ( $columns ) {
        if( isset( $columns['slug'] ) )
            unset( $columns['slug'] );
        if( isset( $columns['posts'] ) )
            unset( $columns['posts'] );
        return $columns;
    });
    //////Limits the dropdown to top level hierarchy.  Removes items that have a parent from the list.
    add_filter( 'taxonomy_parent_dropdown_args', 'go_limit_parents', 10, 2 );



}
add_action( 'admin_init', 'manage_term_columns' );

function go_fix_task_count( $post_id ) {
    $post = get_post( $post_id );
    // Check for post type.
    if ( 'tasks' !== $post->post_type ) {
        return;
    }

    $term_ids = wp_get_object_terms( $post_id, 'task_chains', 'ids' );
    wp_delete_object_term_relationships($post_id, 'task_chains');
    wp_update_term_count( $term_ids, 'task_chains', true );


}
add_action( 'deleted_post', 'go_fix_task_count' );

/**
 * @param $args
 * @param $taxonomy
 * @return mixed
 */
function go_limit_parents($args, $taxonomy ) {
    //if ( 'task_chains' != $taxonomy ) return $args; // no change
    $args['depth'] = '1';
    return $args;
}


function task_chains_add_field_column_contents( $content, $column_name, $term_id ) {
    switch( $column_name ) {
        case 'hidden_term' :
            $content = get_term_meta( $term_id, 'go_hide_map', true );
            if ($content == true){
                $content = '<i class="fas fa-eye-slash"></i>';
            }
            else {
                $content = '';}
            break;
        case 'pod_toggle' :
            $content = get_term_meta( $term_id, 'pod_toggle', true );
            if ($content == true){
                $content = '&#10004;';
            }
            else {
                $content = '';}
            break;
        case 'pod_done_num' :
            $content = get_term_meta( $term_id, 'pod_toggle', true );
            $all = get_term_meta( $term_id, 'pod_all', true );
            if ($content == true){
                if ($all == true){
                    $content = 'all';
                }else {
                    $content = get_term_meta($term_id, 'pod_done_num', true);
                }
            }
            else{
                $content = '';
            }
            break;
        case 'pod_achievement' :
            $term_id = get_term_meta( $term_id, 'pod_achievement', true );
            $term = get_term( $term_id, 'go_badges' );
            //$term = (isset(get_term( $term_id, 'go_badges' ) ?  get_term( $term_id, 'go_badges' ) : null));

            if (!is_wp_error($term) && !empty($term)) {
                $name = $term->name;
            }
            if(!empty($name)) {
                $content = $name;
            }
            else{
                $content = '';
            }

            break;
    }

    return $content;
}
add_filter( 'manage_task_chains_custom_column', 'task_chains_add_field_column_contents', 10, 3 );


function store_types_add_field_column_contents($content, $column_name, $term_id){
    switch( $column_name ) {
        case 'hidden_term' :
            $content = get_term_meta( $term_id, 'go_hide_store_cat', true );
            if ($content == true){
                $content = '<i class="fas fa-eye-slash"></i>';
            }
            else {
                $content = '';}
            break;

    }

    return $content;
}
add_filter( 'manage_store_types_custom_column', 'store_types_add_field_column_contents', 10, 3 );


# Called only in /wp-admin/edit.php pages
//add_action( 'load-edit.php', function() {
    //add_filter( 'views_edit-tasks', 'go_add_from_template_edit_screen' ); // tasks is my custom post type
//});
# echo the tabs
function go_add_from_template_edit_screen() {
    go_new_task_from_template(false);
        //echo '<span class="go_add_quest_from_template"><a href="javascript:void(0);">Add ' . get_option('options_go_tasks_name_singular') . ' from Template</a></span>';
}



// hide certain meta boxes on the 'YOUR_CUSTOM_POST_TYPE' custom post type
add_filter('add_meta_boxes', 'hide_meta_boxes_tasks', 99, 3);
function hide_meta_boxes_tasks() {
    remove_meta_box('postexcerpt', 'tasks', 'normal');
    remove_meta_box('trackbacksdiv', 'tasks', 'normal');
    remove_meta_box('postcustom', 'tasks', 'normal');
    remove_meta_box('slugdiv', 'tasks', 'normal');
    remove_meta_box('generate_layout_options_meta_box', 'tasks', 'normal');
    remove_meta_box('postimagediv', 'tasks', 'normal');
    ////remove_meta_box('commentstatusdiv', 'tasks', 'normal');
    //remove_meta_box('commentsdiv', 'tasks', 'normal');
    //remove_meta_box('revisionsdiv', 'tasks', 'normal');
}

// hide certain meta boxes on the 'YOUR_CUSTOM_POST_TYPE' custom post type
add_filter('add_meta_boxes', 'hide_meta_boxes_tasks_templates');
function hide_meta_boxes_tasks_templates() {
    remove_meta_box('postexcerpt', 'tasks_templates', 'normal');
    remove_meta_box('trackbacksdiv', 'tasks_templates', 'normal');
    remove_meta_box('postcustom', 'tasks_templates', 'normal');
    remove_meta_box('slugdiv', 'tasks_templates', 'normal');
    remove_meta_box('commentstatusdiv', 'tasks_templates', 'normal');
    remove_meta_box('commentsdiv', 'tasks_templates', 'normal');
    remove_meta_box('generate_layout_options_meta_box', 'tasks', 'normal');
    remove_meta_box('postimagediv', 'tasks', 'normal');
    //remove_meta_box('revisionsdiv', 'tasks', 'normal');
}