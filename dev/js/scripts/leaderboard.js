jQuery( document ).ready( function() {

    if (typeof (IsLeaderboard) !== 'undefined') {

        //this only needs to run on the leaderboard

        go_make_select2_filter('user_go_sections', 'section', false, false);
        go_make_select2_filter('user_go_groups', 'group', false, true);
        go_stats_leaderboard_page();
    }
});


function go_stats_leaderboard_page() {
    console.log("go_stats_leaderboard_page");
    // if (jQuery("#go_leaderboard_wrapper").length == 0) {
    console.log('here');
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
                d.section = jQuery('#go_clipboard_user_go_sections_select').val();
                d.group = jQuery('#go_clipboard_user_go_groups_select').val();
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
                sortable: true,
                "orderSequence": [ "desc" ]
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
        ],
    });

    // Event listener to the range filtering inputs to redraw on input
    jQuery('#go_clipboard_user_go_sections_select, #go_clipboard_user_go_groups_select').change( function() {
        //var section = jQuery('#go_user_go_sections_select').val();
        console.log('redraw');
        if (jQuery("#go_leaders_datatable").length) {
            leaderboard.draw();
        }
    } );

    //   }
}
