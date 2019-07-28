<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 3/29/18
 * Time: 10:03 PM
 */


/**
 * @param $field
 * @return mixed
 * Loads the seating chart from the options page in various ACF fields
 */

function acf_load_seat_choices( $field ) {

    // reset choices
    //$field['choices'] = array();
    $field['choices'] = null;
    $field['choices'][ null ] = "Select";
    $name = get_option('options_go_seats_name');
    $number = get_option('options_go_seats_number');
    $field['placeholder'] = 'Select';

    if ($number > 0){
        $i = 0;
        while ($i < $number){
            $i++;

            // vars
            $text = $name . " " . $i;


            // append to choices
            $field['choices'][ $i ] = $text;

        }
    }
    // return the field
    return $field;

}
add_filter('acf/load_field/name=user-seat', 'acf_load_seat_choices');

function acf_load_xp_levels( $field ) {

    // reset choices
    //$field['choices'] = array();
    $field['choices'] = null;
    $field['choices'][ null ] = "Select";
    $num_levels = get_option('options_go_loot_xp_levels_level');
    $number = get_option('options_go_seat_number');
    $field['placeholder'] = 'Select';

    for ($i = 0; $i < $num_levels; $i++) {
        $num = $i+1;
        $xp = get_option('options_go_loot_xp_levels_level_' . $i . '_xp');
        $level_name = get_option('options_go_loot_xp_levels_level_' . $i . '_name');
        $xp_abbr = get_option( "options_go_loot_xp_abbreviation" );

        $name = "Level" . $num . " - " . "$level_name" . " : " . $xp . " " . $xp_abbr;

        $field['choices'][ $xp ] = $name;
    }



    // return the field
    return $field;

}
add_filter('acf/load_field/key=field_5b23676184648', 'acf_load_xp_levels');
add_filter('acf/load_field/key=field_5b52731ddd4f7', 'acf_load_xp_levels');


/**
 * Flushes the rewrite rules when options page is saved.
 * It does this by setting a option to true (1) and then another action
 * actually does the update later and sets the flag back to false.
 * @param $post_id
 * @return string
 * Modified From : https://wordpress.stackexchange.com/questions/182798/flush-rewrite-rules-on-save-post-does-not-work-on-first-post-save
 * Modified From: https://support.advancedcustomfields.com/forums/topic/when-using-save_post-action-how-do-you-identify-which-options-page/
 *
 * This is needed because the options page can change some rewrite rules
 */
function acf_flush_rewrite_rules( $post_id ) {
    if ($post_id == 'options') {
        update_option( 'go-flush-rewrite-rules', 1 );
        //flush_rewrite_rules(true);
    }
}
add_action('acf/save_post', 'acf_flush_rewrite_rules', 2);

function go_late_init_flush() {
    if ( ! $option = get_option( 'go-flush-rewrite-rules' ) ) {
        return false;
    }
    if ( $option == 1 ) {
        flush_rewrite_rules();
        update_option( 'go-flush-rewrite-rules', 0 );
    }
    return true;
}
add_action( 'init', 'go_late_init_flush', 999999 );

/**
 *Loads the default options in bonus loot
 * Default is set in options and loaded on tasks
 */
function default_value_field_5b526d2e7957e($value, $post_id, $field) {
    if ($value === false) {
        $row_count = get_option('options_go_loot_bonus_loot');
        $value = array();
        if(!empty($row_count)){
            for ($i = 0; $i < $row_count; $i++) {
                $title = "options_go_loot_bonus_loot_" . $i . "_title";
                $title = get_option($title);
                $message = "options_go_loot_bonus_loot_" . $i . "_message";
                $message = get_option($message);
                $xp = "options_go_loot_bonus_loot_" . $i . "_defaults_xp";
                $xp = get_option($xp);
                $gold = "options_go_loot_bonus_loot_" . $i . "_defaults_gold";
                $gold = get_option($gold);
                $health = "options_go_loot_bonus_loot_" . $i . "_defaults_health";
                $health = get_option($health);
                $drop = "options_go_loot_bonus_loot_" . $i . "_defaults_drop_rate";
                $drop = get_option($drop);

                $loot_val = array(
                    'field_5b526d2e79583' => $xp,
                    'field_5b526d2e79584' => $gold ,
                    'field_5b526d2e79585' => $health,
                    'field_5b526d2e79588' => $drop
                );
                $row_val = array(
                    'field_5b526d2e7957f' => $title,
                    'field_5b526d2e79580' => $message,
                    'field_5b526d2e79582' => $loot_val
                );

                $value[] = $row_val;

            }

        }
    }
    return $value;
}
add_filter('acf/load_value/key=field_5b526d2e7957e', 'default_value_field_5b526d2e7957e', 10, 3);


/**
 * Only show the top level terms for a taxonomy
 */
function go_top_terms( $args, $field, $post_id  ){
    $args['parent'] = 0;
    return $args;
}
add_filter('acf/fields/taxonomy/query/key=field_5b017d76920ec', 'go_top_terms', 10, 3);



add_filter('acf/load_value/key=field_5d34f488b13ff', 'go_load_sections', 10, 3);

function go_load_sections($value, $post_id, $field){

    $terms = go_get_terms_ordered('user_go_sections');
    $value = array();
    $i = 0;
    foreach ($terms as $term){
        $name = $term->name;
        $term_id = $term->term_id;
        $value[$i] = array('field_5d34f49cb1400'=>$name, 'field_5d35279fb1592'=>$term_id);
        $i++;
    }

    return $value;
}

add_filter('acf/update_value/key=field_5d34f488b13ff', 'go_save_sections', 10, 3);
function go_save_sections($value, $post_id, $field){
    //global $go_section_order;
   // if(empty($go_section_order)){
        //$go_section_order = 1;
    //}
    $section_fields = $_REQUEST['acf']['field_5d34f488b13ff'];
    $order = 0;
    $terms = go_get_terms_ordered('user_go_sections');
    $old_terms = array();
    foreach ($terms as $term) {
        $old_terms[] = $term->term_id;
    }
    $new_terms = array();
    foreach ($section_fields as $section_field){
            $section_name = $section_field['field_5d34f49cb1400'];
            $section_id = $section_field['field_5d35279fb1592'];
            $new_terms[]=$section_id;
            $args = array(
                'name' => $section_name,
            );
            if(empty($section_id)) {
                $term_id = wp_insert_term($section_name, 'user_go_sections');
            }else{
                $term_id = wp_update_term($section_id, 'user_go_sections', $args);
            }
            $order++;
            update_term_meta($term_id['term_id'], 'go_order', $order);

    }

    $deleted_terms = array_diff($old_terms, $new_terms);
    foreach ($deleted_terms as $deleted_term){
        wp_delete_term($deleted_term, 'user_go_sections');
    }


    return '';
}