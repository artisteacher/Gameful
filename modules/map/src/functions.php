<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 1/2/19
 * Time: 12:51 AM
 */


/**
 *
 */
function go_make_map() {
    if ( ! is_admin() ) {
        $user_id = get_current_user_id();
        //$last_map_id = get_user_option('go_last_map', $user_id);
        $last_map_id = (isset($_GET['map_id']) ?  $_GET['map_id'] : get_user_option('go_last_map', $user_id));

        $font = get_option('options_map_font');
        $font_size = $font['font_size'];
        $font_family = $font['font_family'];
        $font_weight = $font['font_weight'];
        $font_style = $font['font_style'];

        $get_font = $font_family . ":" . $font_weight .$font_style;

        wp_enqueue_style( 'acft-gf', 'https://fonts.googleapis.com/css?family='.$get_font );


        if(!$last_map_id){
            $last_map_id = get_option('options_go_locations_map_default', '');
        }
        if(!$last_map_id){
            $taxonomy = 'task_chains';
            /*$term_args0=array(
                'hide_empty' => false,
                'order' => 'ASC',
                'parent' => '0',
                'number' => 1
            );
            $firstmap = get_terms($taxonomy,$term_args0);*/
            $firstmap = go_get_terms_ordered($taxonomy, '0', 1);
            if (!empty($firstmap)) {
                $last_map_id = $firstmap[0]->term_id;
            }else{
                $last_map_id = null;
            }
        }

        echo "<div id='go_map_container' style='font-family: $font_family; font-style: $font_style; font-weight: $font_weight; font-size: $font_size"."px;'>";
        $map_title = get_option( 'options_go_locations_map_title');
        echo "<h1>{$map_title}</h1>";
        go_make_map_dropdown();
        go_make_single_map($last_map_id, false);// do your thing
        echo "</div>";
        ?>
<script>

</script>
        <?php
    }
}
add_shortcode('go_make_map', 'go_make_map');

//this can't be wrapped in the toggle = true because it needs to be available on activation
add_action('init', 'go_map_page');
function go_map_page(){
    if(is_gameful() && is_main_site()){
    	$hide = true;
    }
    else{
    	$hide = false;
    }
    if (!$hide) {
        $map_name = get_option('options_go_locations_map_map_link');
        //add_rewrite_rule( "store", 'index.php?query_type=user_blog&uname=$matches[1]', "top");
        add_rewrite_rule($map_name, 'index.php?' . $map_name . '=true', "top");
    }
}



$go_map_switch = get_option( 'options_go_locations_map_toggle' );

if ($go_map_switch) {

// Query Vars
    add_filter('query_vars', 'go_map_register_query_var');
    function go_map_register_query_var($vars)
    {
        $map_name = get_option('options_go_locations_map_map_link');
        $vars[] = $map_name;
        return $vars;
    }


    /* Template Include */
    add_filter('template_include', 'go_map_template_include', 1, 1);
    function go_map_template_include($template)
    {
        if(is_gameful() && is_main_site()){
    		$hide = true;
    	}
    	else{
    		$hide = false;
  	 	}
  	  	if (!$hide) {
            $map_name = get_option('options_go_locations_map_map_link');
            $map_name = (isset($map_name) ?  $map_name : 'map');
            global $wp_query; //Load $wp_query object

            $page_value = (isset($wp_query->query_vars[$map_name]) ? $wp_query->query_vars[$map_name] : false); //Check for query var "blah"

            if ($page_value && $page_value == "true") { //Verify "blah" exists and value is "true".
                return plugin_dir_path(__FILE__) . 'templates/go_map_template.php'; //Load your template or file
            }

            /*
            $page_name = (isset($wp_query->query_vars['pagename']) ? $wp_query->query_vars['pagename'] : false);
            if ($page_name == $map_name) { //Verify "blah" exists and value is "true".
                return plugin_dir_path(__FILE__) . 'templates/go_map_template.php'; //Load your template or file
            }*/


        }
        return $template; //Load normal template when $page_value != "true" as a fallback
    }
}