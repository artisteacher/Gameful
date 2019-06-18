//This file is js on the acf pages
//

jQuery(document).ready(function($){
    // make sure acf is loaded, it should be, but just in case
    if (typeof acf == 'undefined') {
        return;
    }


		// triger the ready action on page load
		//$('[data-key="field_579376f522130"] select').trigger('ready');
    var go_store_toggle = GO_ACF_DATA.go_store_toggle;
    var go_map_toggle = GO_ACF_DATA.go_map_toggle;
    var go_gold_toggle = GO_ACF_DATA.go_gold_toggle;
    var go_xp_toggle = GO_ACF_DATA.go_xp_toggle;
    var go_health_toggle = GO_ACF_DATA.go_health_toggle;
    var go_badges_toggle = GO_ACF_DATA.go_badges_toggle;


    if (go_store_toggle == 0){

	}

    if (go_map_toggle == 0){
        jQuery(".go_map").hide();
        jQuery('.acf-th[data-name="map"]').hide();
    }

    if (go_gold_toggle == 0){
        jQuery(".go_gold").hide();
        jQuery('.acf-th[data-name="gold"]').hide();

    }

    jQuery()

    if (go_xp_toggle == 0){
        jQuery(".go_xp").hide();
        jQuery('.acf-th[data-name="xp"]').hide();
    }

    if (go_health_toggle == 0){
        jQuery(".go_health").hide();
        jQuery('.acf-th[data-name="health"]').hide();
    }

    if (go_badges_toggle == 0){
        jQuery(".go_badges").hide();
        jQuery('option[value="go_badge_lock"]').hide();
    }



});


