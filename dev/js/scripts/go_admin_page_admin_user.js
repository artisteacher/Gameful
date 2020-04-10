jQuery(document).ready(function(){


    setTimeout(set_height_mce, 1000);

    jQuery("input,select").bind("keydown", function (e) {
        var keyCode = e.keyCode || e.which;
        if(keyCode === 13) {
            e.preventDefault();
            jQuery('input, select, textarea')
                [jQuery('input,select,textarea').index(this)+1].focus();
        }
    });

    jQuery('.go_new_task_from_template_button').one('click', function(){
        go_clone_post_new_menu_bar();
    });


    var h2 = jQuery('h2').filter(function() {
        return jQuery(this).text() === "Personal Options";
    });
    h2.next().remove();
    h2.remove();

    var h2 = jQuery('h2').filter(function() {
        return jQuery(this).text() === "About the user";
    });
    h2.next().remove();
    h2.remove();

    var h2 = jQuery('h2').filter(function() {
        return jQuery(this).text() === "About Yourself";
    });
    h2.next().remove();
    h2.remove();

    jQuery('.user-nickname-wrap, .user-url-wrap, .user-display-name-wrap').hide();

    jQuery('.acf-icon.-copy').each(function() {
        if(jQuery(this).closest('#go_attendance_schedules').length) {
            // code
        }else{
            jQuery(this).remove();
        }
    });


    jQuery('#_customize-input-go_reset_map').click(function () {
        console.log('go_reset_map');
        jQuery('#sub-accordion-section-go_map_controls_section .wp-picker-default').click();
        //jQuery('#go_map_font_size_control').val('15').trigger('change');
    });


//variables to set the acf fields based on query strings
    /*
    const urlParams = new URLSearchParams(window.location.search);
    const chain_id = urlParams.get('chain_id');
    const chain_name = urlParams.get('chain_name');
    jQuery('#acf-field_5a960f458bf8c-field_5ab197179d24a-field_5ab197699d24b').prop("checked", 'true').trigger('change').find('.acf-switch').addClass('-on').removeClass('-off');
    //jQuery('.tax_field_5a960f468bf8e').val(myParam).trigger('change.select2');

    jQuery('.tax_field_5a960f468bf8e').select2("trigger", "select", {
        data: { id: chain_id, text: chain_name }
    });
    */


});



//  This collapses acf repeater fields if go_acf_header class is applied

function go_acf_repeater_accordion(){
    jQuery('.go_acf_header').one('click', function(e) {

        console.log("collapse repeater");
        jQuery('.go_acf_header').off();
        if(jQuery(e.target).is('input, textarea')){
            console.log("prevent collapse");
            e.preventDefault();
        }else {
            jQuery(this).closest('.acf-row').find('.-collapse').trigger('click');
        }
        //jQuery(this).closest('.acf-row').hide();
        go_acf_repeater_accordion();

    });
}



//fix https://stackoverflow.com/questions/9588025/change-tinymce-editors-height-dynamically
function set_height_mce() {
    jQuery('.go_call_to_action .mce-edit-area iframe').height( 100 );

}


//REMOVE THIS
/*
on the create new taxonomy term page,
this hides the acf stuff until a parent map is selected
 */
function go_hide_child_tax_acfs() {
console.log("go_hide_child_tax_acfs");
    if(jQuery('.edit-tags-php.taxonomy-task_chains #parent').length) {
        jQuery('select option:contains("None")').text('Select');
        jQuery('.term-parent-wrap p').html('Select the map for this Map Section (aka: quest chain). If no parent map is selected, then this will be a new map.');
    }
    else if(jQuery('.edit-tags-php.taxonomy-go_badges #parent').length) {
        jQuery('select option:contains("None")').text('NEW '+ go_badge_name + ' CATEGORY');
        var go_badge_name_lc = go_badge_name.toLowerCase();
        jQuery('.term-parent-wrap p').html('Choose a category for this '+ go_badge_name_lc + '. If no category is selected, this will be a new category.');
    }
    else if(jQuery('.edit-tags-php.taxonomy-user_go_groups #parent').length) {
        jQuery('select option:contains("None")').text('NEW '+ go_group_name + ' CATEGORY');
        var go_group_name_lc = go_group_name.toLowerCase();
        jQuery('.term-parent-wrap p').html('Choose a category for this '+ go_group_name_lc + '. If no category is selected, this will be a new category.');
    }
    else if(jQuery('.term-php.taxonomy-task_chains #parent').length) {
        jQuery('.term-parent-wrap p').html('Choose a map for this Map Section.');
    }
    else if(jQuery('.term-php.taxonomy-go_badges #parent').length) {
        var go_badge_name_lc = go_badge_name.toLowerCase();
        jQuery('.term-parent-wrap p').html('Choose a category for this '+ go_badge_name_lc + '.');
    }
    else if(jQuery('.term-php.taxonomy-user_go_groups #parent').length) {
        var go_group_name_lc = go_group_name.toLowerCase();
        jQuery('.term-parent-wrap p').html('Choose a category for this '+ go_group_name_lc + '.');
    }


    if(jQuery('.taxonomy-task_chains #parent, .taxonomy-go_badges #parent, .taxonomy-user_go_groups #parent, .term-php.taxonomy-store_types #parent').val() == -1){
        jQuery('.go_child_term').hide();
        jQuery('#go_map_shortcode_id').show();
    }
    else{
        jQuery('.go_child_term').show();
        jQuery('#go_map_shortcode_id').hide();
    }

    if(jQuery(".term-php").length) {
        if (jQuery('.term-php.taxonomy-task_chains #parent, .term-php.taxonomy-go_badges #parent, .term-php.taxonomy-user_go_groups #parent, .term-php.taxonomy-store_types #parent').val() == -1) {
            jQuery('.term-parent-wrap').hide();
        } else {
            jQuery('.term-parent-wrap #parent option[value="-1"]').remove();
            jQuery('.term-parent-wrap').show();
        }
    }

    var map_id = jQuery('[name="tag_ID"]').val();
    if (map_id == null) {
        jQuery('#go_map_shortcode_id').hide();
    }

    //store item shortcode--add item id to bottom
    var item_id = jQuery('#post_ID').val();
    //jQuery('#go_store_item_id .acf-input').html('[go_store id="' + item_id + '"]');
    jQuery('#go_store_item_id .acf-input').each(function() {
        let text = jQuery(this).html();
        //text = text.replace("{map_id}", map_id);
        text = text.replace(new RegExp("{item_id}", "g"), item_id);
        jQuery(this).html(text);
    });

    //map shortcode message
    //var map_id = jQuery('[name="tag_ID"]').val();
    //console.log(map_id);
    var map_name = jQuery('#name').val();
    //jQuery('#go_map_shortcode_id .acf-input').find('Place this code in a content area to link directly to this map.<br><br>[go_single_map_link map_id="' + map_id + '"]' + map_name + '[/go_single_map_link]');
    jQuery('#go_map_shortcode_id .acf-input').each(function() {
        let text = jQuery(this).html();
        //text = text.replace("{map_id}", map_id);
        text = text.replace(new RegExp("{map_id}", "g"), map_id);
        jQuery(this).html(text);
        //text = text.replace("{map_url}", map_url);
        text = text.replace(new RegExp("{map_url}", "g"), map_url);
        jQuery(this).html(text);
        //text = text.replace("{map_name}", map_name);
        text = text.replace(new RegExp("{map_name}", "g"), map_name);
        jQuery(this).html(text);
    });

    go_activate_tippy();

    if (map_id == null) {
        jQuery('#go_map_shortcode_id').hide();
    }

}



