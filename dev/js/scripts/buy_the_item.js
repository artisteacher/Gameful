//Add an on click to all store items

jQuery(window).load(function () {
//alert("store_ready");

        jQuery('.go_str_item').off().one("click", function (e) {
            go_lb_opener(this.id);
        });

});

jQuery(document).ready(function(){
    //alert("store_ready");
    if (typeof (go_is_store) !== 'undefined') {
        jQuery('#page').css("width", "100%").css('max-width', 'unset');

        go_actions_tooltip();
    }
});

// Makes it so you can press return and enter content in a field
function go_make_store_clickable() {
    //Make URL button clickable by clicking enter when field is in focus
    jQuery('.clickable').keyup(function(ev) {
        // 13 is ENTER
        if (ev.which === 13) {
            jQuery("#go_store_pass_button").click();
        }
    });
}

//open the lightbox for the store items
function go_lb_opener( id ) {
    console.log("go_lb_opener");
    console.log(jQuery('.featherlight.store').length);
    if(jQuery('.featherlight.store').length > 0) {
        console.log('already open');
        jQuery('.go_str_item').off().one("click", function(e){
            go_lb_opener( this.id );
        });
        return;
    }
    jQuery( '#light' ).css( 'display', 'block' );
    jQuery('.go_str_item').prop('onclick',null).off('click');

    if ( ! jQuery.trim( jQuery( '#lb-content' ).html() ).length ) {
        var get_id = id;
        var nonce = GO_EVERY_PAGE_DATA.nonces.go_the_lb_ajax;
        var gotoSend = {
            is_frontend: is_frontend,
            action:"go_the_lb_ajax",
            _ajax_nonce: nonce,
            the_item_id: get_id,
        };
        jQuery.ajax({
            url: MyAjax.ajaxurl,
            type:'POST',
            data: gotoSend,
            beforeSend: function() {
                jQuery( "#lb-content" ).append( '<div class="go-lb-loading"></div>' );
            },
            cache: false,
            /**
             * A function to be called if the request fails.
             * Assumes they are not logged in and shows the login message in lightbox
             */
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 400){
                    jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
                }
            },
            success: function( raw) {
                go_after_ajax();
                console.log('success');
                //console.log(raw);
                var res = JSON.parse( raw );

                try {
                    var res = JSON.parse( raw );
                } catch (e) {
                    res = {
                        json_status: '101',
                        html: ''
                    };
                }
                jQuery( "#lb-content" ).innerHTML = "";
                jQuery( "#lb-content" ).html( '' );

                jQuery.featherlight(res.html, {
                    variant: 'store',
                    afterOpen: function(event){
                        console.log("store-fitvids3");
                        //jQuery("#go_store_description").fitVids();
                        //go_fit_and_max_only("#go_store_description");
                        go_fit_and_max_only("#go_store_description");
                    }
                });
                if ( '101' === Number.parseInt( res.json_status ) ) {
                    console.log (101);
                    jQuery( '#go_store_error_msg' ).show();
                    var error = "Server Error.";
                    if ( jQuery( '#go_store_error_msg' ).text() != error ) {
                        jQuery( '#go_store_error_msg' ).text( error );
                    } else {
                        flash_error_msg_store( '#go_store_error_msg' );
                    }
                } else if ( 302 === Number.parseInt( res.json_status ) ) {
                    console.log (302);
                    window.location = res.location;

                }
                jQuery('.go_str_item').off().one("click", function(e){
                    go_lb_opener( this.id );
                });

                jQuery('#go_store_pass_button').one("click", function (e) {
                    go_store_password(id);
                });

                go_max_purchase_limit();

            }
        });
    }
}

//called when the "buy" button is clicked.
function goBuytheItem( id, count ) {
    console.log('goBuytheItem');
	var nonce = GO_FRONTEND_DATA.nonces.go_buy_item;
	var user_id = GO_FRONTEND_DATA.userID;

	//jQuery( document ).ready( function( jQuery ) {
		var gotoBuy = {
			_ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_buy_item',
			the_id: id,
			qty: jQuery( '#go_qty' ).val(),
            user_id: user_id,
		};
        console.log('goBuytheItem1');

		jQuery.ajax({
			url: MyAjax.ajaxurl,
			type: 'POST',
			data: gotoBuy,
			beforeSend: function() {
				jQuery( '#golb-fr-buy' ).innerHTML = '';
				jQuery( '#golb-fr-buy' ).html( '' );
				jQuery( '#golb-fr-buy' ).append( '<div id="go-buy-loading" class="buy_gold"></div>' );
			},
            /**
             * A function to be called if the request fails.
             * Assumes they are not logged in and shows the login message in lightbox
             */
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 400){
                    jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
                }
            },
			success: function( raw ) {
                console.log('goBuytheItem2');
                go_after_ajax();
                var res = {};
                try {
                    var res = JSON.parse( raw );
                } catch (e) {
                    res = {
                        json_status : '101',
                        html : '101 Error: Please try again.',
                        unlocked_content: ''
                    };
                }
				if ( -1 !== raw.indexOf( 'Error' ) ) {
					jQuery( '#light').html(raw);
				} else {
				    console.log('show_content');
                    //console.log(res.unlocked_content);
                    jQuery( '#light').html(res.html);

                    //testing this
                    if(res.unlocked_content != '') {

                            jQuery.featherlight(res.unlocked_content);

                    }

				}
			}
		});
	//});
}

function flash_error_msg_store( elem ) {
    var bg_color = jQuery( elem ).css( 'background-color' );
    if ( typeof bg_color === undefined ) {
        bg_color = "white";
    }
    jQuery( elem ).animate({
        color: bg_color
    }, 200, function() {
        jQuery( elem ).animate({
            color: "red"
        }, 200 );
    });
}

function go_store_password( id ){
    var pass_entered = jQuery('#go_store_password_result').attr('value').length > 0 ? true : false;
    if (!pass_entered) {
        jQuery('#go_store_error_msg').show();
        var error = "Please enter a password.";
        if (jQuery('#go_store_error_msg').text() != error) {
            jQuery('#go_store_error_msg').text(error);
        } else {
            flash_error_msg_store('#go_store_error_msg');
        }
        jQuery('#go_store_pass_button').one("click", function (e) {
            go_store_password(id);
        });
        return;
    }
    var result = jQuery( '#go_store_password_result' ).attr( 'value' );

    jQuery( '#light' ).css( 'display', 'block' );

    if ( ! jQuery.trim( jQuery( '#lb-content' ).html() ).length ) {
        var get_id = id;
        var nonce = GO_EVERY_PAGE_DATA.nonces.go_the_lb_ajax;
        var gotoSend = {
            is_frontend: is_frontend,
            action:"go_the_lb_ajax",
            _ajax_nonce: nonce,
            the_item_id: get_id,
            skip_locks: true,
            result: result
        };

        jQuery.ajax({

            url: MyAjax.ajaxurl,
            type:'POST',
            data: gotoSend,
            cache: false,
            /**
             * A function to be called if the request fails.
             * Assumes they are not logged in and shows the login message in lightbox
             */
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 400){
                    jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
                }
            },
            success: function( raw) {
                go_after_ajax();
                    var res = JSON.parse( raw );

                    try {
                        var res = JSON.parse( raw );
                    } catch (e) {
                        res = {
                            json_status: '101',
                            html: ''
                        };
                    }

                    if ( '101' === Number.parseInt( res.json_status ) ) {
                        console.log (101);
                        jQuery( '#go_store_error_msg' ).show();
                        var error = "Server Error.";
                        if ( jQuery( '#go_store_error_msg' ).text() != error ) {
                            jQuery( '#go_store_error_msg' ).text( error );
                        } else {
                            flash_error_msg_store( '#go_store_error_msg' );
                        }
                    } else if ( 302 === Number.parseInt( res.json_status ) ) {
                        console.log (302);
                        window.location = res.location;

                    }else if ( 'bad_password' ==  res.json_status ) {
                        jQuery( '#go_store_error_msg' ).show();
                        var error = "Invalid password.";
                        if ( jQuery( '#go_store_error_msg' ).text() != error ) {
                            jQuery( '#go_store_error_msg' ).text( error );
                        } else {
                            flash_error_msg_store( '#go_store_error_msg' );
                        }
                        jQuery('#go_store_pass_button').one("click", function (e) {
                            go_store_password(id);
                        });
                    }else {
                        jQuery('#go_store_pass_button').one("click", function (e) {
                            go_store_password(id);
                        });
                        jQuery('#go_store_lightbox_container').hide();
                        jQuery('.featherlight-content').html(res.html);
                        go_max_purchase_limit();


                    }
            }
        });
    }
}

function go_max_purchase_limit(){
    window.go_purchase_limit = jQuery( '#golb-fr-purchase-limit' ).attr( 'val' );

    var spinner_max_size = go_purchase_limit;

    jQuery( '#go_qty' ).spinner({
        max: spinner_max_size,
        min: 1,
        stop: function() {
            jQuery( this ).change();
        }
    });
    go_make_store_clickable();

    jQuery('#go_store_admin_override').one("click", function (e) {
        jQuery('.go_store_lock').show();
        jQuery('#go_store_admin_override').hide();
        go_make_store_clickable();

    });
}

function go_count_item( item_id ) {
	var nonce = GO_FRONTEND_DATA.nonces.go_get_purchase_count;
	jQuery.ajax({
		url: MyAjax.ajaxurl,
		type: 'POST',
		data: {
			_ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_get_purchase_count',
			item_id: item_id
		},
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
        },
		success: function( res ) {
            go_after_ajax();
			if ( -1 !== res ) {
				var count = res.toString();
				jQuery( '#golb-purchased' ).html( 'Quantity purchased: ' + count );
			}
		}
	});
}

function go_change_avatar( target ){
    console.log("go_change_avatar");
    console.log( target );
    go_enable_loading( target );
    jQuery(target).prop("onclick", null).off("click");
    let media_id = jQuery('#go_this_avatar').val();
    var nonce = GO_FRONTEND_DATA.nonces.go_change_avatar;
    jQuery.ajax({
        url: MyAjax.ajaxurl,
        type: 'POST',
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_change_avatar',
            media_id: media_id
        },
        /**
         * A function to be called if the request fails.
         * Assumes they are not logged in and shows the login message in lightbox
         */
        error: function(jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
        },
        success: function( res ) {
            go_after_ajax();
            if ( -1 !== res ) {
                swal.fire({//sw2 OK
                    text: "Your avatar was changed."
                });

                jQuery.featherlight.close();
                jQuery('.avatar-64').attr('src', res);
            }
        }
    });

}