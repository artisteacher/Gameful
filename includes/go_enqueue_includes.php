<?php

/**
 * Place external JS in the footer
 * Used as the last param to wp_register_script() and wp_enqueue_script()
 */
$js_in_footer = true;
/**
 * URL strings for external scripts
 */


//Datatables
//$go_datatables_js_url    = 'https://cdn.datatables.net/v/ju/jszip-2.5.0/dt-1.10.18/b-1.5.2/b-colvis-1.5.1/b-html5-1.5.2/b-print-1.5.2/cr-1.5.0/fc-3.2.5/fh-3.1.4/kt-2.4.0/r-2.2.2/sc-1.5.0/sl-1.2.6/datatables.min.js';
//$go_datatables_css_url   = 'https://cdn.datatables.net/v/ju/jszip-2.5.0/dt-1.10.18/b-1.5.6/b-colvis-1.5.6/b-html5-1.5.6/b-print-1.5.6/cr-1.5.0/r-2.2.2/sl-1.3.0/datatables.min.css';
//datatables dependency
//$go_datatables_ns_js_url = 'https://cdn.datatables.net/plug-ins/1.10.19/sorting/natural.js';

//pdf make--it is almost 1MB should it be included in GO --what happens when it fails?
$go_pdfmake_js_url       = 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js';
$go_pdfmake_fonts_js_url  = 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js';

/**
 * @param $hook
 */
function go_includes ($hook) {

    // Bring variables from beginning of file into function scope
    global $js_in_footer;
    global $go_pdfmake_js_url, $go_pdfmake_fonts_js_url, $font_awesome_url;
    
    /**
     * Font Awesome
     * https://fontawesome.com
     */
    wp_enqueue_script ('my_font-awesome', 'https://kit.fontawesome.com/5437e6a2c1.js', false, null, false  );
    //wp_enqueue_script( 'my_font_awesome' );
    //wp_register_style( 'go_font_awesome', plugin_dir_url( __FILE__ ).'fontawesome/css/all.css', null, 5 );
    //wp_register_style( 'go_font-awesome', 'https://use.fontawesome.com/releases/v5.7.1/css/all.css', null, 5.7 );
   // wp_enqueue_style( 'go_font_awesome' );

   // wp_register_style ('font-awesome', $font_awesome_url , null, 4.7 );
   // wp_enqueue_style('font-awesome');

    //wp_enqueue_style( 'custom-google-fonts', 'https://fonts.googleapis.com/css?family=B612+Mono&display=swap', false );
    wp_enqueue_style( 'custom-google-fonts', 'https://fonts.googleapis.com/css?family=B612+Mono|Teko&display=swap', false );

    //on change password page
    wp_enqueue_script( 'password-strength-meter' );

    /**
     * jQuery theme for datatables
     */
    wp_register_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css', null, 1.112 );
    wp_enqueue_style( 'jquery-ui-css' );

    /**
     * PDF Make by Bartek Pampuch
     * http://pdfmake.org
     */
    wp_register_script( 'go_pdf_make', $go_pdfmake_js_url, null, 'v1.7.13', $js_in_footer );
    wp_enqueue_script( 'go_pdf_make' );

    wp_register_script( 'go_pdf_make_fonts', $go_pdfmake_fonts_js_url, null, 'v1.7.13', $js_in_footer );
    wp_enqueue_script( 'go_pdf_make_fonts' );

    /**
     * Frontend Media
     */
    //Not sure why this needs to be enqueued here since it is in the included plugin. Check at some future point.
    wp_register_script( 'go_frontend_media', plugin_dir_url( __FILE__ ).'wp-frontend-media-master/js/frontend.js', array( 'jquery' ), '2015-05-07', true);
    wp_enqueue_script( 'go_frontend_media' );
}


?>
