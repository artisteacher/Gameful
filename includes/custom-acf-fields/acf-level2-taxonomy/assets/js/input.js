(function($){

	/**
	*  initialize_field
	*
	*  This function will initialize the $field.
	*
	*  @date	30/11/17
	*  @since	5.6.5
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize_field( $field ) {
        var taxonomy = $field.find(".l2select_wrapper").data("taxonomy");
        console.log("taxonomy");
        console.log(taxonomy);
        //$field.doStuff();
       /* $field.find(".l2select_wrapper").select2({
            ajax: {
                url: ajaxurl, // AJAX URL is predefined in WordPress admin
                dataType: 'json',
                delay: 400, // delay in ms while typing when to perform a AJAX search
                data: function (params) {
                    return {
                        q: params.term, // search query
                        action: 'go_make_taxonomy_dropdown_ajax', // AJAX action for admin-ajax.php
                        taxonomy: taxonomy
                };
                },
                processResults: function( data ) {
                    console.log("INITIALIZE");
                    return {
                        results: data
                    };
                },
                cache: false
            },
            minimumInputLength: 0, // the minimum of symbols to input before perform a search
            //multiple: false,
            placeholder: "Select",
            allowClear: true
        });*/

        $field.find('select').select2({
            ajax: {
                url: MyAjax.ajaxurl, // AJAX URL is predefined in WordPress admin
                dataType: 'json',
                delay: 400, // delay in ms while typing when to perform a AJAX search

                data: function (params) {

                    return {
                        q: params.term, // search query
                        is_frontend: is_frontend,
                        action: 'go_make_taxonomy_dropdown_ajax', // AJAX action for admin-ajax.php
                        taxonomy: taxonomy,
                        //parents_only: parents_only,
                    };
                },
                processResults: function( data ) {
                    console.log("processing");
                    console.log(data);
                    console.log("INITIALIZE");
                    return {
                        results: data,

                    };

                },
                success: function( data ) {
                    console.log("success_select2");

                    //go_after_ajax();
                },
                cache: false
            },
            minimumInputLength: 0, // the minimum of symbols to input before perform a search
            //multiple: false,
            placeholder: "Select",
            allowClear: true
        });

        $field.find('select').on('select2:select', function() {
                acf_level2_taxonomy_update(this);
            }
        )

	}

    //console.log(typeof acf.add_action);
	if( typeof acf.add_action !== 'undefined' ) {
		/*
		*  ready & append (ACF5)
		*
		*  These two events are called when a field element is ready for initizliation.
		*  - ready: on page load similar to $(document).ready()
		*  - append: on new DOM elements appended via repeater field or other AJAX calls
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		acf.add_action('ready_field/type=level2_taxonomy', initialize_field);
		acf.add_action('append_field/type=level2_taxonomy', initialize_field);
        //acf.add_action('append', initialize_field);
		
		
	}
})(jQuery);

//sets the value that will be returned in the hidden input
function acf_level2_taxonomy_update(obj) {
    console.log("acf_level2_taxonomy_update");
    //var selected = jQuery(obj).children('option:selected');
    console.log(obj);
    //var val = jQuery(obj).children('option:selected').val();
    var myval = jQuery(obj).select2('val');
    console.log(myval);
    if(myval !== null) {
        myval = myval.toString();
        //myval = JSON.stringify(myval);
        myval = myval.replace(/,/g, ".");
    }
    //var val = jQuery(obj).select2('val').serializeArray();
    console.log("value:");
    console.log(myval);
    jQuery(obj).siblings('input').val(myval);


    //jQuery(obj).val(myval);


    //////////

    var order_field = jQuery(obj).closest('.l2select_wrapper').data('order_field');

    if(order_field != 'none') {
        order_field = jQuery("#" + order_field);
        console.log("order_field: ");


        var key = jQuery(order_field).data('key');
        var list_id = "#list_" + key;
        console.log(list_id);
        jQuery(list_id).html("Loading . . . ");
        console.log("acf_load_order_field_list");
        var term_id = myval;
        var key = jQuery(order_field).data('key');
        var name = jQuery(order_field).data('name');
        var post_id = jQuery(order_field).data('post_id');

        //var url = admin_url('admin-ajax.php');

        var order_key_name = jQuery(order_field).data('order_key_name');
        var nonce = jQuery(order_field).data('nonce');
        console.log("term_id: " + term_id);
        jQuery.ajax({
            type: "get",
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                action: "acf_load_order_field_list",
                key: key,
                post_id: post_id,
                name: name,
                term_id: term_id,
                order_key_name: order_key_name
            },
            success: function (res) {
                console.log("res: " + res);
                jQuery(list_id).html(res);
                return res;

            }
        });
    }


}

