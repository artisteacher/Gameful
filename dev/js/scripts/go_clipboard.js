jQuery( document ).ready( function() {
    //jQuery( "#datepicker_clipboard" ).datepicker({ firstDay: 0 });
    //jQuery("#datepicker_clipboard").datepicker('setDate', new Date());//today's date

    //var nonce = GO_CLIPBOARD_DATA.nonces.go_clipboard_stats;

    if (typeof (GO_CLIPBOARD_DATA) !== 'undefined') {

        go_load_daterangepicker('clipboard');


        //Tabs
        if (jQuery('#records_tabs').length) {
            jQuery('#records_tabs').tabs();
            jQuery('.clipboard_tabs').click(function () {
                //console.log("tabs");
                tab = jQuery(this).attr('tab');
                switch (tab) {

                    case 'clipboard':
                        //console.log("stats1");
                        go_clipboard_stats_datatable(false);
                        //force window resize on load to initialize responsive behavior
                        jQuery("#go_clipboard_stats_datatable").DataTable().columns.adjust()
                            .responsive.recalc();
                        break;
                    case 'store':
                        go_clipboard_store_datatable();
                        //force window resize on load to initialize responsive behavior
                        jQuery("#go_clipboard_store_datatable").DataTable().columns.adjust()
                            .responsive.recalc();
                        //add the store item filter select2
                        go_make_select2_cpt('#go_store_item_select', 'go_store');
                        break;
                    case 'messages':
                        //console.log("messages");
                        go_clipboard_messages_datatable();
                        //force window resize on load to initialize responsive behavior
                        jQuery("#go_clipboard_messages_datatable").DataTable().columns.adjust()
                            .responsive.recalc();
                        break;
                    case 'activity':
                        //console.log("activity");
                        go_clipboard_activity_datatable();
                        jQuery("#go_clipboard_activity_datatable").DataTable().columns.adjust()
                            .responsive.recalc();
                        //add task select2
                        go_make_select2_cpt('#go_task_select', 'tasks');

                        break;
                }
            });
        }

        if (jQuery("#records_tabs").length) {
            //go_clipboard_stats_datatable(false);
            jQuery("#records_tabs").css("margin-left", '');

        }


        // Get saved data from sessionStorage
        var unmatched = localStorage.getItem('go_clipboard_unmatched');

        if (unmatched == true || unmatched == 'true') {
            jQuery('#go_unmatched_toggle').prop('checked', true);
        }

        go_make_select2_filter('user_go_sections',false, true);

        go_make_select2_filter('user_go_groups',false, true);

        go_make_select2_filter('go_badges',false, true);

        jQuery('#go_unmatched_toggle').change(function () {
            go_activate_apply_filters();//unmetched toggle
            //jQuery('.go_apply_filters').addClass("bluepulse");
            //jQuery('.go_apply_filters').html('<span class="ui-button-text">Apply Filters<i class="fas fa-filter" aria-hidden="true"></i></span>');
        });

        go_clipboard_stats_datatable();//draw the stats tab on load

        //ADD Blue background and glow to filter button if unmatch toggle is clicked

        go_setup_reset_filter_button(false);
    }
});




//not used
function go_cache_menu( data, menu){
    // the type of the cacheData should be changed to object
    sessionStorage.setItem('go_menu_' + menu, JSON.stringify(data));
}

//not used?
function go_get_menu_data(taxonomy){
    jQuery.ajax({
        type: "get",
        url: MyAjax.ajaxurl,
        data: {
            //_ajax_nonce: nonce,
            action: 'go_clipboard_save_filters',
            section: section,
            badge: badge,
            group: group,
            unmatched: unmatched
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
           return res;
        }
    });
}

function go_toggle( source ) {
	checkboxes = jQuery( '.go_checkbox' );
	for (var i = 0, n = checkboxes.length; i < n ;i++) {
		checkboxes[ i ].checked = source.checked;
	}
}

function go_clipboard_callback() {
    //*******************//
    // ALL TABS
    //*******************//
        //Apply on click to the stats and messages buttons in the table
        go_stats_links();

        //apply on click to the messages button at the top
        jQuery('.go_messages_icon_multiple_clipboard').parent().prop('onclick',null).off('click');
        jQuery(".go_messages_icon_multiple_clipboard").parent().one("click", function(e){
            go_messages_opener(null, null, "multiple_messages", this);
        });

        go_activate_tippy();

    //*******************//
    //GET CURRENT TAB
    //*******************//
        var current_tab = jQuery("#records_tabs").find("[aria-selected='true']").attr('aria-controls');
        console.log(current_tab);

    //IF CURRENT TAB IS . . .
        if (current_tab == "clipboard_wrap"){
            //recalculate for responsive behavior
            jQuery("#go_clipboard_stats_datatable").DataTable().columns.adjust()
                .responsive.recalc();

            //hide action filters
            jQuery('#go_action_filters').hide();

            //update button--set this table to update
            jQuery('.go_apply_filters').prop('onclick',null).off('click');//unbind click
            jQuery('.go_apply_filters').one("click", function () {
                Clipboard.draw();
                    //go_clipboard_stats_datatable(true);
                go_clipboard_update();
            });

            //search
            //unbind search
            jQuery("div.dataTables_filter input").unbind();
            //search on clear with 'x'
            document.querySelector("#go_clipboard_stats_datatable_filter input").onsearch = function (e) {
                Clipboard.search( this.value ).draw();
            };
        }
        else if (current_tab == "clipboard_store_wrap") {
            //recalculate for responsive behavior
            jQuery("#go_clipboard_store_datatable").DataTable().columns.adjust()
                .responsive.recalc();

            //show action filters
            jQuery('#go_action_filters').show();
            jQuery('#go_store_filters').show();
            jQuery('#go_task_filters').hide();

            //update button--set this table to update
            jQuery('.go_apply_filters').prop('onclick',null).off('click');//unbind click
            jQuery('.go_apply_filters').one("click", function () {
                //go_clipboard_store_datatable(true);
                Store.draw();
                go_clipboard_update();
            });

            //search
            jQuery("div.dataTables_filter input").unbind();
            //search on clear with 'x'
            document.querySelector("#go_clipboard_store_datatable_filter input").onsearch = function (e) {
                Store.search( this.value ).draw();
            };
        }
        else if (current_tab == "clipboard_messages_wrap") {
            //recalculate for responsive behavior
            jQuery("#go_clipboard_messages_datatable").DataTable().columns.adjust()
                .responsive.recalc();

            //show/hide filters
            jQuery('#go_action_filters').show();
            jQuery('#go_store_filters').hide();
            jQuery('#go_task_filters').hide();

            //update button--set this table to update
            jQuery('.go_apply_filters').prop('onclick',null).off('click');//unbind click
            jQuery('.go_apply_filters').one("click", function () {
                Messages.draw();
                go_clipboard_update();
            });



            //search
            jQuery("div.dataTables_filter input").unbind();
            //search on clear with 'x'
            document.querySelector("#go_clipboard_messages_datatable_filter input").onsearch = function (e) {
                Messages.search( this.value ).draw();
            };
        }
        else if (current_tab == "clipboard_activity_wrap") {
            //recalculate for responsive behavior
            jQuery("#go_clipboard_activity_datatable").DataTable().columns.adjust()
                .responsive.recalc();

            //show date filter
            jQuery('#go_action_filters').show();
            jQuery('#go_store_filters').hide();
            jQuery('#go_task_filters').show();

            //update button--set this table to update
            jQuery('.go_apply_filters').prop('onclick',null).off('click');//unbind click
            jQuery('.go_apply_filters').one("click", function () {
                Activity.draw();
                go_clipboard_update();
            });

            //search
            jQuery("div.dataTables_filter input").unbind();
            //search on clear with 'x'
            document.querySelector("#go_clipboard_activity_datatable_filter input").onsearch = function (e) {
                Activity.search( this.value ).draw();
            };

            /*
            //apply on click to the reset button at the top
            jQuery('.go_reset_icon').prop('onclick',null).off('click');
            jQuery(".go_reset_icon").one("click", function(e){
                go_messages_opener();
            });
            */
            go_enable_reset_buttons();
        }
}

function go_clipboard_update() {
    console.log("go_clipboard_update");
    go_save_filters();
    jQuery('.go_apply_filters').removeClass("bluepulse");
    jQuery('.go_apply_filters').html('<span class="ui-button-text">Refresh Data <span class="dashicons dashicons-update" style="vertical-align: center;"></span></span>');




    //*******************//
    //GET CURRENT TAB
    //*******************//
    var current_tab = jQuery("#records_tabs").find("[aria-selected='true']").attr('aria-controls');
    //IF CURRENT TAB IS . . .
    if (current_tab == "clipboard_wrap"){
        //Clear other tables
        //jQuery('#go_clipboard_stats_datatable').remove();
        jQuery('#go_clipboard_store_datatable').remove();
        jQuery('#go_clipboard_messages_datatable').remove();
    }
    else if (current_tab == "clipboard_store_wrap") {
        //Clear other tabs
        jQuery('#go_clipboard_stats_datatable').remove();
        //jQuery('#go_clipboard_store_datatable').remove();
        jQuery('#go_clipboard_messages_datatable').remove();
    }
    else if (current_tab == "clipboard_messages_wrap") {
        //Clear other tabs
        jQuery('#go_clipboard_stats_datatable').remove();
        jQuery('#go_clipboard_store_datatable').remove();
        //jQuery('#go_clipboard_messages_datatable').remove();
    }
    else if (current_tab == "clipboard_activity_wrap") {
    }
}

//this now saves to session data
function go_save_filters(){
    console.log("go_save_clipboard_filters");
    //SESSION STORAGE
    var section = jQuery( '#go_page_user_go_sections_select' ).val();
    var section_name = jQuery("#go_page_user_go_sections_select option:selected").text();
    var group = jQuery( '#go_page_user_go_groups_select' ).val();
    var group_name = jQuery("#go_page_user_go_groups_select option:selected").text();
    var badge = jQuery( '#go_page_go_badges_select' ).val();
    var badge_name = jQuery("#go_page_go_badges_select option:selected").text();

    var unmatched = document.getElementById("go_unmatched_toggle").checked;

    localStorage.setItem('user_go_sections', section);
    localStorage.setItem('go_badges', badge);
    localStorage.setItem('user_go_groups', group);
    localStorage.setItem('user_go_sections_name', section_name);
    localStorage.setItem('go_badges_name', badge_name);
    localStorage.setItem('user_go_groups_name', group_name);
    localStorage.setItem('go_unmatched', unmatched);

    /*
    if(is_reader){
        var date = jQuery('#go_datepicker_clipboard span').html();
        var tasks = jQuery("#go_task_select").val();
        var unread = jQuery('#go_reader_unread').prop('checked');
        var read = jQuery('#go_reader_read').prop('checked');
        var reset = jQuery('#go_reader_reset').prop('checked');
        var trash = jQuery('#go_reader_trash').prop('checked');
        var draft = jQuery('#go_reader_draft').prop('checked');
        var order = jQuery("input[name='go_reader_order']:checked").val();
        var limit = jQuery('#go_posts_num').val();
        localStorage.setItem('go_reader_date', date);
        localStorage.setItem('go_reader_tasks', tasks);
        localStorage.setItem('go_reader_unread', unread);
        localStorage.setItem('go_reader_read', read);
        localStorage.setItem('go_reader_reset', reset);
        localStorage.setItem('go_reader_trash', trash);
        localStorage.setItem('go_reader_draft', draft);
        localStorage.setItem('go_reader_order', order);
        localStorage.setItem('go_reader_limit', limit);
    }
    */

    /*
    //THIS IS FOR SAVING AS OPTION IN DB WITH AJAX
    //ajax to save the values
    var nonce = GO_CLIPBOARD_DATA.nonces.go_clipboard_save_filters;
    var section = jQuery( '#go_page_user_go_sections_select' ).val();
    var group = jQuery( '#go_page_user_go_groups_select' ).val();
    var badge = jQuery( '#go_page_go_badges_select' ).val();
    var unmatched = document.getElementById("go_unmatched_toggle").checked;
    //alert ("badge " + badge);
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            action: 'go_clipboard_save_filters',
            section: section,
            badge: badge,
            group: group,
            unmatched: unmatched
        },
        success: function( res ) {
            console.log("values saved");
        }
    });
    */
}




/*
function go_filter_clipboard_datatables(filter_badges) { //function that filters all tables on draw
    jQuery.fn.dataTable.ext.search.push(
        function( settings, data, dataIndex ) {
            var mytable = settings.sTableId;
            //if (mytable == "go_clipboard_stats_datatable" || mytable == "go_clipboard_messages_datatable" || mytable == "go_clipboard_activity_datatable") {
                var section = jQuery('#go_page_user_go_sections_select').val();
                var group = jQuery('#go_page_user_go_groups_select').val();
                var badge = jQuery('#go_page_go_badges_select').val();
                var badges =  data[4] ;
                var groups =  data[3] ; // use data for the filter by column
                var sections = data[2]; // use data for the filter by column



                groups = JSON.parse(groups);
                //sections = JSON.parse(sections);
                badges = JSON.parse(badges);
                //console.log("badges" + badges);
                //console.log("sections" + sections);

                var inlist = true;
                if( group == "none" || jQuery.inArray(group, groups) != -1) {
                    inlist = true;
                }else {
                    inlist = false;
                }

                if (inlist){
                    if( section == "none" || sections == section) {
                        inlist = true;
                    }else {
                        inlist = false;
                    }
                }
                if (filter_badges == true) {
                    if (inlist) {
                        if (badge == "none" || jQuery.inArray(badge, badges) != -1) {
                            inlist = true;
                            //console.log(inlist);
                        } else {
                            inlist = false;
                            //console.log(inlist);
                        }
                    }
                }
                return inlist;
            //}
            //else{
             //   return true;
           // }
        });
}
*/



function go_clipboard_stats_datatable(refresh) {
	if (jQuery("#go_clipboard_stats_datatable").length == 0  || refresh == true) {
        jQuery("#clipboard_stats_datatable_container").html("<h2>Loading . . .</h2>");
        var nonce = GO_CLIPBOARD_DATA.nonces.go_clipboard_stats;
        //console.log("refresh" + refresh);
        //console.log("stats");
        jQuery.ajax({
            type: "post",
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                action: 'go_clipboard_stats',
                refresh: refresh
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
                console.log("success");
                //console.log(res);
                if (-1 !== res) {
                    jQuery('#clipboard_stats_datatable_container').html(res);

                    Clipboard = jQuery('#go_clipboard_stats_datatable').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": MyAjax.ajaxurl + '?action=go_clipboard_stats_dataloader_ajax',
                            "data": function(d){
                                //d.user_id = jQuery('#go_stats_hidden_input').val();
                                //d.user_id = jQuery('#go_stats_hidden_input').val();
                                d.section = jQuery('#go_page_user_go_sections_select').val();
                                d.group = jQuery('#go_page_user_go_groups_select').val();
                                d.badge = jQuery('#go_page_go_badges_select').val();
                            }
                        },
                        "bPaginate": true,
                        //colReorder: true,
                        "order": [[6, "desc"]],
                        responsive: true,
                        "autoWidth": false,
                        stateSave: true,
                        stateLoadParams: function( settings, data ) {
                            //if (data.order) delete data.order;
                            if (data.search) delete data.search;
                            if (data.start) delete data.start;
                        },
                        "stateDuration": 31557600,
                        searchDelay: 1000,
                        dom: 'lBfrtip',
                        "drawCallback": function(settings ) {
                            go_clipboard_callback();
                        },
                        "columnDefs": [
                            { type: 'natural', targets: '_all'  },
                            {
                                "targets": [0],
                                className: 'noVis',
                                "width": "1px",
                                sortable: false
                            },
                            {
                                "targets": [1],
                                className: 'noVis',
                                "width": "20px",
                                sortable: false
                            },
                            {
                                "targets": [7],
                                //className: 'noVis',
                                sortable: false
                            },
                        ],
                        buttons: [
                            {
                                text: '<span class="go_messages_icon_multiple_clipboard">Message <i class="fas fa-bullhorn" aria-hidden="true"></i><span>',
                                action: function ( e, dt, node, config ) {

                                }

                            },
                            {
                                extend: 'collection',
                                text: 'Export ...',
                                buttons: [{
                                    extend: 'pdf',
                                    title: 'Gameful Me Data Export',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    },
                                    orientation: 'landscape'
                                },{
                                    extend: 'excel',
                                    title: 'Gameful Me Data Export',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    }
                                }, {
                                    extend: 'csv',
                                    title: 'Gameful Me Data Export',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    }
                                }],

                            },
                            {
                                extend: 'colvis',
                                columns: ':not(.noVis)',
                                postfixButtons: ['colvisRestore'],
                                text: 'Column Visibility'
                            }
                        ]
                    });

                    //Filter the table
                    //go_filter_clipboard_datatables(true);
                    //redraw table
                    //Clipboard.draw();
                }
            }
        });
    }else{
        go_clipboard_callback();
    }
}

function go_clipboard_store_datatable(refresh) {

    if ( jQuery( "#go_clipboard_store_datatable" ).length == 0  || refresh == true) {
        jQuery("#clipboard_store_datatable_container").html("<h2>Loading . . .</h2>");
        var nonce = GO_CLIPBOARD_DATA.nonces.go_clipboard_store;
        jQuery.ajax({
            url: MyAjax.ajaxurl,
            type: "post",
            data: {
                _ajax_nonce: nonce,
                action: 'go_clipboard_store'
                //go_clipboard_messages_datatable: jQuery( '#go_clipboard_store_datatable' ).val()
            },
            /**
             * A function to be called if the request fails.
             * Assumes they are not logged in and shows the login message in lightbox
             */
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 400){
                    jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
                }
                go_clipboard_callback();
            },
            success: function( res ) {
                //console.log("success");
                if (-1 !== res) {
                    jQuery('#clipboard_store_datatable_container').html(res);
                    //go_filter_datatables();
                    Store = jQuery('#go_clipboard_store_datatable').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": MyAjax.ajaxurl + '?action=go_clipboard_store_dataloader_ajax',
                            "data": function(d){

                                //d.user_id = jQuery('#go_stats_hidden_input').val();
                                d.date = jQuery('#go_datepicker').html();
                                d.section = jQuery('#go_page_user_go_sections_select').val();
                                d.group = jQuery('#go_page_user_go_groups_select').val();
                                d.badge = jQuery('#go_page_go_badges_select').val();
                                d.unmatched = document.getElementById("go_unmatched_toggle").checked;
                                d.store_item = jQuery("#go_store_item_select").val();
                            }
                        },
                        "bPaginate": true,
                        //colReorder: true,
                        "order": [[8, "desc"]],
                        responsive: true,
                        "autoWidth": false,
                        stateSave: true,
                        stateLoadParams: function( settings, data ) {
                            //if (data.order) delete data.order;
                            if (data.search) delete data.search;
                            if (data.start) delete data.start;
                        },
                        "stateDuration": 31557600,
                        searchDelay: 1000,
                        dom: 'lBfrtip',
                        "drawCallback": function( settings ) {
                            go_clipboard_callback();
                        },
                        "columnDefs": [
                            { type: 'natural', targets: '_all' },
                            {
                                "targets": [0],
                                className: 'noVis',
                                "width": "5px",
                                sortable: false
                            },
                            {
                                "targets": [1],
                                className: 'noVis',
                                "width": "20px",
                                sortable: false
                            },
                            {
                                "targets": [7],
                                sortable: false
                            },
                            {
                                "targets": [9],
                                sortable: true
                            }
                        ],
                        buttons: [
                            {
                                text: '<span class="go_messages_icon_multiple_clipboard">Message <i class="fas fa-bullhorn" aria-hidden="true"></i><span>',
                                action: function ( e, dt, node, config ) {
                                }
                            },
                            {
                                extend: 'collection',
                                text: 'Export ...',
                                buttons: [{
                                    extend: 'pdf',
                                    title: 'Gameful Me Data Export',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    },
                                    orientation: 'landscape'
                                },{
                                    extend: 'excel',
                                    title: 'Gameful Me Data Export',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    }
                                }, {
                                    extend: 'csv',
                                    title: 'Gameful Me Data Export',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    }
                                }],
                            },
                            {
                                extend: 'colvis',
                                columns: ':not(.noVis)',
                                postfixButtons: ['colvisRestore'],
                                text: 'Column Visibility'
                            }
                        ]
                    });
                }
            }
        });
    }
    else{
        go_clipboard_callback();
    }
}

function go_clipboard_messages_datatable(refresh) {
    if ( jQuery( "#go_clipboard_messages_datatable" ).length == 0  || refresh == true) {
        jQuery("#clipboard_messages_datatable_container").html("<h2>Loading . . .</h2>");

        var nonce = GO_CLIPBOARD_DATA.nonces.go_clipboard_messages;

        jQuery.ajax({
            type: "post",
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                action: 'go_clipboard_messages'
                //go_clipboard_messages_datatable: jQuery( '#go_clipboard_messages_datatable' ).val()
            },
            /**
             * A function to be called if the request fails.
             * Assumes they are not logged in and shows the login lightbox
             */
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 400){
                    jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
                }
            },
            success: function( res ) {
                //console.log("success");
                if (-1 !== res) {
                    jQuery('#clipboard_messages_datatable_container').html(res);
                    //go_filter_datatables();
                    Messages = jQuery('#go_clipboard_messages_datatable').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": MyAjax.ajaxurl + '?action=go_clipboard_messages_dataloader_ajax',
                            "data": function(d){
                                //d.user_id = jQuery('#go_stats_hidden_input').val();
                                d.date = jQuery('#go_datepicker').html();
                                d.section = jQuery('#go_page_user_go_sections_select').val();
                                d.group = jQuery('#go_page_user_go_groups_select').val();
                                d.badge = jQuery('#go_page_go_badges_select').val();
                                d.unmatched = document.getElementById("go_unmatched_toggle").checked;
                            }
                        },
                        "bPaginate": true,
                        //colReorder: true,
                        "order": [[8, "desc"]],
                        responsive: true,
                        "autoWidth": false,
                        searchDelay: 1000,
                        stateSave: true,
                        stateLoadParams: function( settings, data ) {
                            //if (data.order) delete data.order;
                            if (data.search) delete data.search;
                            if (data.start) delete data.start;
                        },
                        "stateDuration": 31557600,
                        dom: 'lBfrtip',
                        "drawCallback": function( settings ) {
                            go_clipboard_callback();
                        },
                        "columnDefs": [
                            { type: 'natural', targets: '_all' },
                            {
                                "targets": [0],
                                className: 'noVis',
                                "width": "5px",
                                sortable: false
                            },
                            {
                                "targets": [1],
                                className: 'noVis',
                                "width": "20px",
                                sortable: false
                            },
                            {
                                "targets": [7],
                                sortable: false
                            },
                            {
                                "targets": [9],
                                sortable: false
                            }
                        ],
                        buttons: [
                            {
                                text: '<span class="go_messages_icon_multiple_clipboard">Message <i class="fas fa-bullhorn" aria-hidden="true"></i><span>',
                                action: function ( e, dt, node, config ) {
                                }
                            },
                            {
                                extend: 'collection',
                                text: 'Export ...',
                                buttons: [{
                                    extend: 'pdf',
                                    title: 'Gameful Me Data Export',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    },
                                    orientation: 'landscape'
                                },{
                                    extend: 'excel',
                                    title: 'Gameful Men Data Export',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    }
                                }, {
                                    extend: 'csv',
                                    title: 'Gameful Me Data Export',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    }
                                }],
                            },
                            {
                                extend: 'colvis',
                                columns: ':not(.noVis)',
                                postfixButtons: ['colvisRestore'],
                                text: 'Column Visibility'
                            }
                        ]
                    });

                    //search only on enter key
                    jQuery("div.dataTables_filter input").unbind();
                    jQuery("div.dataTables_filter input").keyup( function (e) {
                        if (e.key === 13) {
                            Messages.search( this.value ).draw();
                        }
                    });
                }
            }
        });
    }
    else{
        go_clipboard_callback();
    }
}

function go_clipboard_activity_datatable(refresh) {
    if ( jQuery( "#go_clipboard_activity_datatable" ).length == 0  || refresh == true) {
        jQuery("#clipboard_activity_datatable_container").html("<h2>Loading . . .</h2>");

        var nonce = GO_CLIPBOARD_DATA.nonces.go_clipboard_activity;

        console.log("date: " + jQuery('#go_datepicker').html());
        console.log("section: " + jQuery('#go_page_user_go_sections_select').val());
        console.log("group: " + jQuery('#go_page_user_go_groups_select').val());
        console.log("badges: " + jQuery('#go_page_go_badges_select').val());
        console.log("unmatched: " + document.getElementById("go_unmatched_toggle").checked);
        console.log("tasks: " + jQuery("#go_task_select").val());

        //console.log(date);
        jQuery.ajax({
            type: "post",
            url: MyAjax.ajaxurl,
            data: {
                _ajax_nonce: nonce,
                action: 'go_clipboard_activity',
                date: jQuery('#go_datepicker').html()
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
                //console.log("success");
                if (-1 !== res) {
                    jQuery('#clipboard_activity_datatable_container').html(res);
                    //go_filter_datatables();
                    Activity = jQuery('#go_clipboard_activity_datatable').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": MyAjax.ajaxurl + '?action=go_clipboard_activity_dataloader_ajax',
                            "data": function(d){
                                //d.user_id = jQuery('#go_stats_hidden_input').val();
                                d.date = jQuery('#go_datepicker').html();
                                d.section = jQuery('#go_page_user_go_sections_select').val();
                                d.group = jQuery('#go_page_user_go_groups_select').val();
                                d.badge = jQuery('#go_page_go_badges_select').val();
                                d.unmatched = document.getElementById("go_unmatched_toggle").checked;
                                d.tasks = jQuery("#go_task_select").val();
                                for (var i = 0, len = d.columns.length; i < len; i++) {
                                    if (! d.columns[i].search.value) delete d.columns[i].search;
                                    if (d.columns[i].searchable === true) delete d.columns[i].searchable;
                                    if (d.columns[i].orderable === true) delete d.columns[i].orderable;
                                    if (d.columns[i].data === d.columns[i].name) delete d.columns[i].name;
                                }
                                delete d.search.regex;
                            }
                        },
                        deferRender: true,
                        "bPaginate": true,
                        //colReorder: true,
                        "order": [11, "asc"],
                        responsive: true,
                        "autoWidth": false,
                        //stateSave: true,
                        stateLoadParams: function( settings, data ) {
                            //if (data.order) delete data.order;
                            if (data.search) delete data.search;
                            if (data.start) delete data.start;
                        },
                        "stateDuration": 31557600,
                        dom: 'lBfrtip',
                        "drawCallback": function( settings ) {
                            go_clipboard_callback();
                        },
                        "columnDefs": [
                            { type: 'natural', targets: '_all' },
                            {
                                "targets": [0],
                                className: 'noVis',
                                "width": "5px",
                                sortable: false
                            },
                            {
                                "targets": [1],
                                className: 'noVis',
                                "width": "20px",
                                sortable: false
                            },
                            {
                                "targets": [7, 9, 14],
                                sortable: false
                            }
                        ],
                        buttons: [
                            {
                                text: '<span class="go_messages_icon_multiple_clipboard">Message <i class="fas fa-bullhorn" aria-hidden="true"></i><span>',
                                action: function ( e, dt, node, config ) {
                                }
                            },
                            {
                                text: '<span class="go_tasks_reset_multiple_clipboard">Reset <i class="fas fa-times-circle" aria-hidden="true"></i><span>',
                                action: function ( e, dt, node, config ) {
                                }
                            },

                            {
                                extend: 'collection',
                                text: 'Export ...',
                                buttons: [{
                                    extend: 'pdf',
                                    title: 'Gameful Me Data Export',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    },
                                    orientation: 'landscape'
                                },{
                                    extend: 'excel',
                                    title: 'Gameful Me Data Export',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    }
                                }, {
                                    extend: 'csv',
                                    title: 'Gameful Me Data Export',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    }
                                }],

                            },
                            {
                                extend: 'colvis',
                                columns: ':not(.noVis)',
                                postfixButtons: ['colvisRestore'],
                                text: 'Column Visibility'
                            },


                        ]

                    });

                    // Add event listener for opening and closing more actions
                    jQuery('#go_clipboard_activity_datatable .show_more').click( function () {
                        var hidden = jQuery(this).hasClass('shown');
                        //console.log(hidden);
                        if (hidden == false) {
                            jQuery(this).addClass('shown');
                            jQuery(this).siblings('.hidden_action').show();
                            jQuery(this).find('.hide_more_actions').show();
                            jQuery(this).find('.show_more_actions').hide();
                            //console.log("show");
                        }else{
                            jQuery(this).removeClass('shown');
                            jQuery(this).siblings('.hidden_action').hide();
                            jQuery(this).find('.hide_more_actions').hide();
                            jQuery(this).find('.show_more_actions').show();
                            //console.log("hide");
                        }
                    });

                }
            }
        });
    }else{
        go_clipboard_callback();
    }
}
