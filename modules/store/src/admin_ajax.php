<?php

function go_make_store_html() {

    //$args = array('hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC', 'parent' => '0');

    /* Get all task chains with no parents--these are the sections of the store.  */
    $taxonomy = 'store_types';

    //$xp_abbr = get_option( "options_go_loot_xp_abbreviation" );
    //$gold_abbr = get_option( "options_go_loot_gold_abbreviation" );
    //$health_abbr = get_option( "options_go_loot_health_abbreviation" );

    //$rows = get_terms($taxonomy, $args);//the rows

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
                    $custom_fields = get_post_custom($store_item_id);
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


?>
