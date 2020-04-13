
jQuery( document ).ready( function() {
    console.log('customizer script');
//add functionality to button in the customizer
    jQuery('#_customize-input-go_reset_map').click(function () {
        console.log('go_reset_map');
        jQuery('#sub-accordion-section-go_map_controls_section .wp-picker-default').click();
    });

});


/**
 * This file adds some LIVE to the Theme Customizer live preview. To leverage
 * this, set your custom settings to 'postMessage' and then add your handling
 * here. Your javascript should grab settings from customizer controls, and
 * then make any necessary changes to the page using jQuery.
 *
 */
( function( $ ) {

    // Update the site title in real time...
    wp.customize( 'go_map_font_size_control', function( value ) {
        value.bind( function( newval ) {
            $( '#go_map_container, .featherlight.store .featherlight-content'  ).css('font-size', newval +'px' );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_map_bkg_color', function( value ) {
        value.bind( function( newval ) {
            $( '#go_map_container .featherlight.store .featherlight-content' ).css('background-color', newval );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_map_font_color', function( value ) {
        value.bind( function( newval ) {
            $( '#go_map_container .featherlight.store .featherlight-content' ).css('color', newval );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_map_chain_color', function( value ) {
        value.bind( function( newval ) {
            $( '#maps .go_task_chain_map_box' ).css('background-color', newval );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_map_chain_font_color', function( value ) {
        value.bind( function( newval ) {
            $( '#maps .go_task_chain_map_box' ).css('color', newval );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_map_available_color', function( value ) {
        value.bind( function( newval ) {
            $( '#maps .available, .go_store_actions, .go_checks_and_buttons.active' ).css('background-color', newval );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_map_available_font_color', function( value ) {
        value.bind( function( newval ) {
            $( '#maps .available, .go_store_actions, .go_checks_and_buttons.active' ).css('color', newval );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_map_done_color', function( value ) {
        value.bind( function( newval ) {
            $( '#maps .done, .go_checks_and_buttons' ).css('background-color', newval );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_map_done_font_color', function( value ) {
        value.bind( function( newval ) {
            $( '#maps .done, .go_checks_and_buttons' ).css('color', newval );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_map_locked_color', function( value ) {
        value.bind( function( newval ) {
            $( '#maps .locked, .go_locks, .go_lock, .go_late_mods, .go_sched_access_message, .go_timer_message' ).css('background-color', newval );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_map_locked_font_color', function( value ) {
        value.bind( function( newval ) {
            $( '#maps .locked, .go_locks, .go_lock, .go_late_mods, .go_sched_access_message, .go_timer_message' ).css('color', newval );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_store_up_color', function( value ) {
        value.bind( function( newval ) {
            $( '#maps .go_store_loot_list_reward, .go_store_lightbox_container .go_reward, .loot-box.up, #gp_store_plus' ).css('background-color', newval );
        } );
    } );
//
    // Update the site title in real time...
    wp.customize( 'go_store_up_font_color', function( value ) {
        value.bind( function( newval ) {
            $( '#maps .go_store_loot_list_reward, .go_store_lightbox_container .go_reward, .loot-box.up, #gp_store_plus' ).css('color', newval );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_store_down_color', function( value ) {
        value.bind( function( newval ) {
            $( '#maps .go_store_loot_list_cost, .go_store_lightbox_container .go_cost, .loot-box.down, #gp_store_minus' ).css('background-color', newval );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_store_down_font_color', function( value ) {
        value.bind( function( newval ) {
            $( '#maps .go_store_loot_list_cost, .go_store_lightbox_container .go_cost, .loot-box.down, #gp_store_minus' ).css('color', newval );
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_sound_up', function( value ) {
        value.bind( function( newval ) {
        //<script>var audio = new Audio( PluginDir.url + '". $sound ."' ); audio.play();</script>
            var script = '<script>var audio = new Audio( PluginDir.url +"' + 'media/sounds/' + newval + '" ); audio.play();</script>';
            console.log(script);
            $( 'body' ).append(script);
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_sound_down', function( value ) {
        value.bind( function( newval ) {
            //<script>var audio = new Audio( PluginDir.url + '". $sound ."' ); audio.play();</script>
            var script = '<script>var audio = new Audio( PluginDir.url +"' + 'media/sounds/' + newval + '" ); audio.play();</script>';
            console.log(script);
            $( 'body' ).append(script);
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_sound_level_up', function( value ) {
        value.bind( function( newval ) {
            //<script>var audio = new Audio( PluginDir.url + '". $sound ."' ); audio.play();</script>
            var script = '<script>var audio = new Audio( PluginDir.url +"' + 'media/sounds/' + newval + '" ); audio.play();</script>';
            console.log(script);
            $( 'body' ).append(script);
        } );
    } );

    // Update the site title in real time...
    wp.customize( 'go_sound_level_down', function( value ) {
        value.bind( function( newval ) {
            //<script>var audio = new Audio( PluginDir.url + '". $sound ."' ); audio.play();</script>
            var script = '<script>var audio = new Audio( PluginDir.url +"' + 'media/sounds/' + newval + '" ); audio.play();</script>';
            console.log(script);
            $( 'body' ).append(script);
        } );
    } );


} )( jQuery );