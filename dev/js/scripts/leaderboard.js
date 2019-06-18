jQuery( document ).ready( function() {
    if (typeof (IsLeaderboard) !== 'undefined') {//this only needs to run on the leaderboard
        //groups and the table are callbacks on success of this function call
        go_make_leaderboard_filter('user_go_sections');
    }
});

function go_make_leaderboard_filter(taxonomy){
    console.log('go_make_leaderboard_filter');
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            //_ajax_nonce: nonce,
            action: 'go_make_leaderboard_filter',
            taxonomy: taxonomy
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
            //console.log(res);
            let error = go_ajax_error_checker(res);
            if (error == 'true') return;

            if ( -1 !== res ) {

                if (res){
                    var res = JSON.parse( res );
                    go_make_select2_filter(taxonomy,false, false, false);
                    let value = res['term_id'];
                    let value_name = res['term_name'];

                    var valueSelect = jQuery('#go_page_' + taxonomy + '_select');
                    var option = new Option(value_name, value, true, true);
                    //valueSelect.append(option);
                    valueSelect.append(option).trigger('change.select2');

                    jQuery('#go_page_' + taxonomy +'_select').val(value);
                }
            }

            if(taxonomy === 'user_go_sections'){
                go_make_leaderboard_filter('user_go_groups');
            }

            if(taxonomy === 'user_go_groups'){
                go_stats_leaderboard_page();
            }

        }
    });
}

function go_stats_leaderboard_page() {
    console.log("go_stats_leaderboard_page");
    // if (jQuery("#go_leaderboard_wrapper").length == 0) {
    //var section_value = jQuery('#go_page_user_go_sections_select').val();
    //console.log(section_value);
    var is_admin = GO_EVERY_PAGE_DATA.go_is_admin;
    var initial_sort = 3;
    if (is_admin == true){
        initial_sort = 4;
    }
    let is_redraw = false;
    jQuery(".go_leaderboard_wrapper").show();
    leaderboard = jQuery('#go_leaders_datatable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": MyAjax.ajaxurl + '?action=go_stats_leaderboard_dataloader_ajax',
            "data": function(d){
                var section_value = jQuery('#go_page_user_go_sections_select').val();
                var group_value = jQuery('#go_page_user_go_groups_select').val();

                d.section = section_value;
                d.group = group_value;
            }
        },
        //"orderFixed": [[4, "desc"]],
        //"destroy": true,
        responsive: false,
        "autoWidth": false,
        "paging": true,
        "order": [[initial_sort, "desc"]],
        "drawCallback": function( settings ) {
            go_stats_links();
        },
        "searching": false,
        "columnDefs": [
            { type: 'natural', targets: '_all'},
            {
                "targets": [0],
                sortable: false
            },
            {
                "targets": [1],
                sortable: false
            },
            {
                "targets": [2],
                sortable: false
            },
            {
                "targets": [3],
                sortable: false
            },
            {
                "targets": [4],
                sortable: true,
                "orderSequence": [ "desc" ]
            },
            {
                "targets": [5],
                sortable: true,
                "orderSequence": [ "desc" ]
            },
            {
                "targets": [6],
                sortable: true,
                "orderSequence": [ "desc" ]
            },
            {
                "targets": [7],
                sortable: true,
                "orderSequence": [ "desc" ]
            },
        ],
    });

    // Event listener to the range filtering inputs to redraw on input
    jQuery('#go_page_user_go_sections_select, #go_page_user_go_groups_select').change( function() {
        //var section = jQuery('#go_user_go_sections_select').val();
        console.log('redraw');
        if (jQuery("#go_leaders_datatable").length) {
            leaderboard.draw();
        }
    } );

    //   }
}
