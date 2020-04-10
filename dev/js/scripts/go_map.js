if (typeof (go_is_map) !== 'undefined') {
    jQuery( document ).ready( function() {

        if(jQuery('#go_map_container').length > 0){
            jQuery('body').show(function() {
                // Animation complete.
                go_setup_map();
            });
        }



    });

}

function go_setup_map(){
    console.log("go_setup_map");

    var taxonomy = jQuery("#maps").data('taxonomy');
    go_show_map_loot(true);

    if(jQuery('#maps').hasClass('sortable')) {

        var el = document.getElementById('go_Dropdown');
        new Sortable(el, {
            //group: 'shared', // set both lists to same group
            animation: 150,
            onUpdate: function (/**Event*/evt) {
                var itemEl = evt.item; // dragged HTMLElement
                var listEl = evt.from; //previous list

                console.log('update');
                var terms = [];
                jQuery(listEl).find('.mapLink').each(function () {
                    var term_id = jQuery(this).data('term_id');
                    terms.push(term_id);
                });
                //var map_id = jQuery('#maps').data('mapid');
                var nonce = GO_EVERY_PAGE_DATA.nonces.go_update_map_order;
                jQuery.ajax({
                    type: 'post',
                    url: MyAjax.ajaxurl,
                    data: {
                        _ajax_nonce: nonce,
                        action: 'go_update_map_order',
                        terms: terms,
                        //map_id: map_id,
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 400) {
                            jQuery(document).trigger('heartbeat-tick.wp-auth-check', [{'wp-auth-check': false}]);
                        }
                    },
                    success: function (res) {
                        console.log(res);
                        if (res !== 'success') {
                            swal.fire({
                                type: 'warning',
                                title: 'There was a problem updating the map.',
                                text: 'The page will be updated and then you can try again.',
                                confirmButtonText:
                                    "Refresh",
                                timer: 15000,
                            }).then((result) => {
                                location.reload();
                            });
                        }

                    }
                });
            },
        });


        var el = document.getElementById('primaryNav');
        console.log(el);
        new Sortable(el, {
            //group: 'shared', // set both lists to same group
            animation: 150,
            onUpdate: function (/**Event*/evt) {
                console.log('update chain order');
                console.log(evt);
                var itemEl = evt.item; // dragged HTMLElement
                var listEl = evt.from; //previous list


                var terms = [];
                jQuery(listEl).find('.go_task_chain').each(function () {
                    var term_id = jQuery(this).data('term_id');
                    terms.push(term_id);
                });
                var map_id = jQuery('#maps').data('mapid');
                var nonce = GO_EVERY_PAGE_DATA.nonces.go_update_chain_order;
                jQuery.ajax({
                    type: 'post',
                    url: MyAjax.ajaxurl,
                    data: {
                        _ajax_nonce: nonce,
                        action: 'go_update_chain_order',
                        terms: terms,
                        taxonomy: taxonomy,
                        map_id: map_id,
                    },

                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 400) {
                            jQuery(document).trigger('heartbeat-tick.wp-auth-check', [{'wp-auth-check': false}]);
                        }
                    },
                    success: function (res) {
                        console.log(res);
                        if (res !== 'success') {
                            swal.fire({
                                type: 'warning',
                                title: 'There was a problem updating the map.',
                                text: 'The page will be updated and then you can try again.',
                                confirmButtonText:
                                    "Refresh",
                                timer: 15000,
                            }).then((result) => {
                                location.reload();
                            });
                        }

                    }
                });
            },
        });




        var elements = document.getElementsByClassName('tasks');
        for (var i = 0; i < elements.length; i++) {

            var el = elements[i];
            new Sortable(el, {
                group: 'shared', // set both lists to same group
                animation: 150,
                onAdd: function (/**Event*/evt) {
                    console.log('update task order Add');
                    var itemEl = evt.item; // dragged HTMLElement
                    var NewListEl = evt.to;    // target list
                    var listEl = evt.from; //previous list

                    go_update_task_sort(NewListEl)

                },
                onRemove: function (/**Event*/evt) {
                    console.log('update task order Remove');
                    var itemEl = evt.item; // dragged HTMLElement
                    var NewListEl = evt.to;    // target list
                    var listEl = evt.from; //previous list

                    go_update_task_sort(listEl)

                },
                onUpdate: function (/**Event*/evt) {
                    console.log('update task order update');
                    var itemEl = evt.item; // dragged HTMLElement
                    var NewListEl = evt.to;    // target list
                    var listEl = evt.from; //previous list

                    go_update_task_sort(listEl)

                },
            });
        }



        var elements = document.getElementsByClassName('go_nested_list');
        for (var i = 0; i < elements.length; i++) {

            var el = elements[i];
            new Sortable(el, {
                group: 'shared', // set both lists to same group
                animation: 150,
                onAdd: function (/**Event*/evt) {
                    console.log('update nest order Add');
                    var itemEl = evt.item; // dragged HTMLElement
                    var NewListEl = evt.to;    // target list
                    var listEl = evt.from; //previous list
                    var new_task_id = null;
                    var task_id = null;
                    //if this was added from the same term, this isn't needed
                    //if the destination is the same term, this isn't needed
                    jQuery(NewListEl).find('.task_container').each(function () {
                        new_task_id = jQuery(this).find('.task').data('post_id');
                    });
                    jQuery(listEl).closest('.tasks').find('.task_container').each(function () {
                        task_id = jQuery(this).find('.task').data('post_id');
                    });

                    if(new_task_id != task_id) {
                        console.log('update Add');
                        go_update_nested_sort(NewListEl)
                    };

                },
                onRemove: function (/**Event*/evt) {
                    console.log('update nest order Remove');
                    var itemEl = evt.item; // dragged HTMLElement
                    var NewListEl = evt.to;    // target list
                    var listEl = evt.from; //previous list
                    var new_task_id = null;
                    var task_id = null;
                    //if the destination is the same term, this isn't needed
                    jQuery(NewListEl).find('.task_container').each(function () {
                        new_task_id = jQuery(this).find('.task').data('post_id');
                    });
                    jQuery(listEl).closest('.tasks').find('.task_container').each(function () {
                        task_id = jQuery(this).find('.task').data('post_id');
                    });

                    if(new_task_id != task_id) {
                        console.log('update remove');
                        go_update_nested_sort(listEl)
                    };

                },
                onUpdate: function (/**Event*/evt) {
                    console.log('update nest order update');
                    var itemEl = evt.item; // dragged HTMLElement
                    var NewListEl = evt.to;    // target list
                    var listEl = evt.from; //previous list

                    go_update_nested_sort(listEl)

                },
            });
        }
    }

    go_disable_tooltips(true);

    //wrapped();

    jQuery(window).resize(function() {
        jQuery('.primaryNav').css('flex-direction', 'row');
        wrapped();
        //make sure icons don't overlap dropdown

    });


    jQuery(document.body).click( function(e) {
        jQuery("#go_Dropdown").addClass('hidden');
    });

    jQuery(".dropdown").click( function(e) {
        e.stopPropagation(); // this stops the event from bubbling up to the body
    });

    //add onclick to to optional toggles
    //jQuery('.go_nested_toggle').click(go_nested_toggle);

    jQuery('.go_nested_toggle').mouseenter(function() {
        console.log('in');
        tippyInstances.forEach(instance => {
            instance.disable();
        });
    });

    jQuery('.go_nested_toggle').mouseleave(function() {
        console.log('out');
        tippyInstances.forEach(instance => {
            instance.enable();
        });
    });

    jQuery('.go_nested_hover').hover(function() {
            el = jQuery(this);

            timeout = setTimeout(function(){
                // do stuff on hover
                console.log('go_nested_hover');
                tippyInstances.forEach(instance => {
                    instance.hide();
                });

                jQuery(el).find('.go_nested_toggle').addClass('open');
                //jQuery(this).html("<div class='go_nested_hover' style='line-height: 17px;'><i class='fas fa-caret-up'></i></div>");
                jQuery(el).find('.go_nested_list').css('display', 'block');
            }, 150);
        },
        function(){
            clearTimeout(timeout);
            // do stuff when hover off
            console.log('go_nested_exit');
            if(!jQuery(el).find('.go_nested_toggle').hasClass('toggled_open')) {
                jQuery(this).find('.go_nested_toggle').removeClass('open');
                //jQuery(this).html("<div class='go_nested_hover' style='line-height: 17px;'><i class='fas fa-caret-up'></i></div>");
                jQuery(this).find('.go_nested_list').css('display', 'none');
            }
    });
    jQuery('.go_nested_toggle').click(go_nested_toggle);

    jQuery(".go_clone_icon").off().one("click", function(){
        go_importer(this);
    });



    //jQuery('.go_chain_loot_info').on('click', function(){

    jQuery(".go_chain_loot_info").each(function(){

        jQuery(this).closest(".go_task_chain").find(".go_task_chain_map_box").append(this);


    });

    //jQuery(".go_chain_loot_info").closest(".go_task_chain").find(".go_task_chain_map_box").append(this);

    jQuery(".go_map_loot_info").each(function(){
        jQuery(this).closest("#maps").find(".map_title").append(this);
    });

    go_actions_tooltip();

    jQuery('.task_container').has('.go_nested_toggle').addClass('hasNested');

    jQuery('.go_bonus_progress_bar').each(function() {
        var width = jQuery(this).attr('data-width');
        if(width !== '100'){
            jQuery(this).css('border-bottom-right-radius', '0');
        }
    });

    jQuery('#page').css("width", "100%" ).css('max-width', 'unset');
    jQuery('#go_map_container').show();
    jQuery('#sitemap').show();
    jQuery('#maps').show();
    go_activate_tippy();

    jQuery('.primaryNav').css('flex-direction', 'row');
    wrapped();
    go_set_nested_toggle_colors();
    console.log('map is ready');


}

//frontend map sort
function go_update_task_sort(listEl){
    console.log('go_update_task_sort');
    var tasks = [];
    jQuery(listEl).find('.task_container').each(function () {
        var task_info = [];
        var task_id = jQuery(this).find('.task').data('post_id');
        var is_nested = 0;
        if (jQuery(this).closest('ul').hasClass('go_nested_list')) {
            is_nested = 1;
            jQuery(this).find('.nested_checkbox').attr('checked', true);
        }else{
            jQuery(this).find('.nested_checkbox').attr('checked', false);
        }

        //console.log('ism');
        //console.log(is_nested);
        task_info.push(task_id);
        task_info.push(is_nested);
        tasks.push(task_info);

    });

    var chain_id = jQuery(listEl).data('chain_id');
    var taxonomy = jQuery("#maps").data('taxonomy');
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_update_task_order;

    console.log(tasks);
    console.log(chain_id);
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            action: 'go_update_task_order',
            tasks: tasks,
            chain_id: chain_id,
            taxonomy: taxonomy
        },

        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400) {
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [{'wp-auth-check': false}]);
            }
        },
        success: function (raw) {
            go_fix_task_colors(raw);
            go_set_nested_toggle_colors();
        }
    });
}

//front end map sort
function go_update_nested_sort(listEL){
    console.log('go_update_nested_sort');
    var tasks = [];
    jQuery(listEL).find('.go_nested_toggle').remove();
    jQuery(listEL).find('.go_nested_hover').contents().unwrap();
    jQuery(listEL).find('.go_nested_list').contents().unwrap();
    jQuery(listEL).find('.task_container').removeClass('hasNested');
    jQuery(listEL).find('.task_container').each(function () {
        jQuery(this).find('.task_container').insertAfter(this);
    });




    jQuery(listEL).closest('.tasks').find('.task_container').each(function () {
        //var task_id = jQuery(this).find('.task').data('post_id');
        //tasks.push( task_id );

        var task_info = [];
        var task_id = jQuery(this).find('.task').data('post_id');
        var is_nested = 0;
        if (jQuery(this).closest('ul').hasClass('go_nested_list')) {
            is_nested = 1;
            jQuery(this).find('.nested_checkbox').prop('checked', true);
        }else{
            jQuery(this).find('.nested_checkbox').prop('checked', true);
        }



        //console.log('ism');
        //console.log(is_nested);
        task_info.push(task_id);
        task_info.push(is_nested);
        tasks.push(task_info);


    });
    var chain_id = jQuery(listEL).closest('.tasks').data('chain_id');
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_update_task_order;
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            action: 'go_update_task_order',
            tasks: tasks,
            chain_id: chain_id,
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400) {
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [{'wp-auth-check': false}]);
            }
        },
        success: function (raw) {
            go_fix_task_colors(raw);
            go_set_nested_toggle_colors();
        }
    });
}

function go_set_nested_toggle_colors(){
    console.log('go_set_nested_toggle_colors');
    jQuery('.go_nested_list').each(function() {
        //jQuery(this).find('.task').last().addClass('lastNested');
        var count = 0;
        var done = 0;
        var available = 0;
        var locked = 0;
        var set = false;
        var myclass = '';
        jQuery(this).find('.task').each(function(){
            count++;
            if(jQuery(this).hasClass('available')){
                available++;
            }
            if(jQuery(this).hasClass('done')){
                done++;
            }
            if(jQuery(this).hasClass('locked')){
                locked++;
            }
        });
        if(available > 0){
            set = true;
            myclass = 'available';
        }
        if(locked > 0 && !set){
            set = true;
            myclass = 'locked';
        }
        if(!set){
            myclass = 'done';
        }
        jQuery(this).prev().removeClass('available locked done');
        jQuery(this).prev().addClass(myclass);

        if(count === 0 ){
            jQuery(this).closest('.task_container').removeClass('hasNested');
            jQuery(this).closest('.go_nested_hover').remove();
        }

    });
}

function go_fix_task_colors(raw){
    var error = go_ajax_error_checker(raw);
    if (error == 'true') return;

    // parse the raw response to get the desired JSON
    var res = {};
    try {
        var res = JSON.parse( raw );
    } catch (e) {
        res = {
            json_status: '101',
            lock_info: '',
        };
    }
    if(res.json_status !== 302){
        swal.fire({
            type: 'warning',
            title: 'There was a problem updating the map.',
            text: 'The page will be updated and then you can try again.',
            confirmButtonText:
                "Refresh",
            timer: 15000,
        }).then((result) => {
            location.reload();
        });
    }
    var status_array = res.lock_info;

    status_array.forEach(function(item, index){
        var task_id = item[0];
        var color = item[1];
        var taskClass = '.task_id_' + task_id;
        jQuery(taskClass).each(function(){
            if(jQuery(this).hasClass('done')){
            }else{
                jQuery(this).removeClass('available');
                jQuery(this).hasClass('locked');
                console.log(color);
                if(color){
                    jQuery(this).addClass('locked');
                }else{
                    jQuery(this).addClass('available');
                }
            }
        })
    });
}

/*
function go_to_task(event, link){
    console.log('go_to_task');

    if ( jQuery(event.target).parents('.tippy-popper').length) {
        return;
    }

    console.log('redirect');
    if(openInNewTab)
    {
        window.open(link, '_blank');
    }else{
       window.location = link;
    }


}*/


function wrapped() {
    console.log('wrapped');
    //var offset_top_prev;
    if(jQuery('.go_task_chain').length) {
        var offset_top_prev = jQuery('.go_task_chain').offset().top;
        //console.log(offset_top_prev);


        jQuery('.go_task_chain').each(function () {
            var offset_top = jQuery(this).offset().top;
            //console.log(offset_top);

            if (offset_top > offset_top_prev) {
                jQuery('.primaryNav').css('flex-direction', 'column');
                jQuery(this).addClass('wrapped');
            } else if (offset_top == offset_top_prev) {
                jQuery(this).removeClass('wrapped');
            }

            offset_top_prev = offset_top;
        });
    }

    var drop = jQuery('.dropdown').offset();
    var width = jQuery('.dropdown').width();
    var icons = jQuery('.go_map_action_icons').offset();
    var height = jQuery('.droptop').height();

    if((drop.left + width + 10) > icons.left ){
        jQuery('.dropdown').css('margin-top', 40);
        jQuery('#maps').css('margin-top', height + 70);
    }else{
        jQuery('.dropdown').css('margin-top', 0);
        jQuery('#maps').css('margin-top', height + 30);
    }

    jQuery()

}

function go_show_map_loot(initial = false){
console.log('go_show_map_loot');
    var visibility = localStorage.getItem('user_show_loot');
    if(initial){
        if (visibility === 'show') {
            jQuery(".loot_info").show();
        }
    }else {
        if (visibility === 'show') {
                visibility = 'hide';
        } else {
            visibility = 'show';
        }
        localStorage.setItem('user_show_loot', visibility);
        jQuery(".loot_info").toggle();
    }
}

function go_disable_tooltips(initial = false){
    console.log('go_disable_tooltips');
    var map_actions = sessionStorage.getItem('map_actions');

    if(map_actions === null || !initial) {

        if (map_actions === "true") {
            sessionStorage.setItem('map_actions', 'false');
            //const go_actions_tooltip = tippy(document.querySelectorAll('.go_show_actions'));
            tippyInstances.forEach(instance => {
                instance.destroy();
            });
            tippyInstances.length = 0; // clear it
            jQuery('.go_map_action_icons .tooltip_toggle .inactive').show();
            jQuery('.go_map_action_icons .tooltip_toggle .active').hide();

            //these are the badge tooltips
            //they should toggle opposite of the tooltips
            jQuery('.go_badge_wrap .fig_wrap.inactive').hide();
            jQuery('.go_badge_wrap .fig_wrap.active').show();
        } else {
            sessionStorage.setItem('map_actions', 'true');
            go_actions_tooltip();
            jQuery('.go_map_action_icons .tooltip_toggle .inactive').hide();
            jQuery('.go_map_action_icons .tooltip_toggle .active').show();

            //these are the badge tooltips
            //they should toggle opposite of the tooltips
            jQuery('.go_badge_wrap .fig_wrap.inactive').show();
            jQuery('.go_badge_wrap .fig_wrap.active').hide();

        }
    }else{

        if (map_actions === "false") {
            jQuery('.go_map_action_icons .tooltip_toggle .inactive').show();
            jQuery('.go_map_action_icons .tooltip_toggle .active').hide();

            //these are the badge tooltips
            //they should toggle opposite of the tooltips
            jQuery('.go_badge_wrap .fig_wrap.inactive').hide();
            jQuery('.go_badge_wrap .fig_wrap.active').show();
        }else{

                jQuery('.go_map_action_icons .tooltip_toggle .inactive').hide();
                jQuery('.go_map_action_icons .tooltip_toggle .active').show();

                //these are the badge tooltips
                //they should toggle opposite of the tooltips
                jQuery('.go_badge_wrap .fig_wrap.inactive').show();
                jQuery('.go_badge_wrap .fig_wrap.active').hide();

        }

    }

}


function go_quests_frontend(target){
    console.log('go_quests_frontend');

    var loader_html = go_loader_html('big');
    console.log(loader_html);
    jQuery("#quest_frontend_wrapper").append(loader_html);
    jQuery("#quest_frontend_loader").show();
    var taxonomy = jQuery('#maps').data('taxonomy');
    jQuery(".go_quests_frontend").off();
    var nonce = GO_FRONTEND_DATA.nonces.go_quests_frontend;
    var post_id = jQuery(target).data('post_id');

    var loader_html = go_loader_html('big');

    jQuery.featherlight(loader_html, {
        variant: 'quests',
        iframeWidth: '90%',
    });

    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            post_id: post_id,
            action: 'go_quests_frontend',
            is_single_stage : 'true',
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
            jQuery(".go_clone_icon").off().one("click", function(){
                go_importer(this);
            });
        },
        success: function( res ) {

            jQuery('.featherlight.quests .featherlight-content').append(res);

            console.log("after");
            //console.log("after");
            //go_map_check_if_done();

            //go_load_daterangepicker('go_setup_clipboard');

            //Tabs
            /*
            if (jQuery('#records_tabs').length) {
                jQuery('#records_tabs').tabs();
                jQuery("#records_tabs").css("margin-left", '');
            }*/
            if(taxonomy === 'store_types'){
                console.log(taxonomy);
                go_clipboard_store_datatable(true);
                jQuery("#go_clipboard_store_datatable").DataTable().columns.adjust()
                    .responsive.recalc();
                //add the store item filter select2
                go_make_select2_cpt('#go_store_item_select', 'go_store');
            }else {
                go_clipboard_activity_datatable(true);
                jQuery("#go_clipboard_activity_datatable").DataTable().columns.adjust().responsive.recalc();
                //add task select2
                go_make_select2_cpt('#go_task_select', 'tasks');
            }


            go_make_select2_filter('user_go_sections','reader', true);

            go_make_select2_filter('user_go_groups','reader', true);

            go_make_select2_filter('go_badges','reader', true);


            //ADD Blue background and glow to filter button if unmatch toggle is clicked

            go_setup_filter_buttons(false);

            jQuery('#go_datepicker_container').one("click", function () {
                //console.log("hi there one");
                go_load_daterangepicker('go_activate_reader');
                jQuery('#go_reset_datepicker').show();
                go_daterange_clear();
                go_highlight_apply_filters();//datapicker
            });
            jQuery('#go_store_filters').hide();

            /*
            jQuery.featherlight(res, {
                variant: 'quests',
                iframeWidth: '90%',
                beforeOpen: function() { jQuery.featherlight.close(); },
                afterOpen: function() {
                    jQuery('.featherlight.quests .featherlight-content').append(loader_html);
                },
                //beforeOpen: function() { jQuery.featherlight.close();},
                afterContent: function () {
                    console.log("after");
                    //go_map_check_if_done();

                    go_load_daterangepicker('go_setup_clipboard');


                    //Tabs
                    if (jQuery('#records_tabs').length) {
                        jQuery('#records_tabs').tabs();
                        jQuery("#records_tabs").css("margin-left", '');
                    }
                    go_clipboard_activity_datatable();
                    jQuery("#go_clipboard_activity_datatable").DataTable().columns.adjust()
                        .responsive.recalc();
                    //add task select2
                    go_make_select2_cpt('#go_task_select', 'tasks');



                    go_make_select2_filter('user_go_sections','reader', true);

                    go_make_select2_filter('user_go_groups','reader', true);

                    go_make_select2_filter('go_badges','reader', true);


                    //ADD Blue background and glow to filter button if unmatch toggle is clicked

                    go_setup_filter_buttons(false);


                    ///////////////
                }
            });
            */

        }
    });

    //var item = jQuery(target).data('item');
}

function go_trash_post(target){
    console.log('go_trash_post');
    console.log(target);
    jQuery(".go_trash_post").off();

    var taxonomy = jQuery(target).data('taxonomy');
    if(!taxonomy){
        taxonomy = jQuery('#maps').data('taxonomy');
    }
    console.log(taxonomy);

    var post_id = jQuery(target).data('post_id');
    var term_id = jQuery(target).data('term_id');
    var map_id = jQuery(target).data('map_id');

    var singular = jQuery('#mapwrapper').data('singular');
    var plural = jQuery('#mapwrapper').data('plural');
    var nest_message ='';
    var trash_message = '';
    var text = '';
    if(post_id) {
        var title = jQuery(target).data('title');
        //var title = jQuery(target).closest('.task_container').find('.title').html();
        text = '<p>You are about to move the ' + singular + ' <b>'+ title +"</b> to the trash.</p>";
        var container = jQuery('.task_container_'+post_id);
        console.log(container);
        var hasNested = jQuery(container).hasClass('hasNested');
        if(hasNested){
            console.log("hasNested");
            var children = [];
            jQuery(container).find('ul').find('li').each(function(){
                var this_id = jQuery(this).data('post_id');
                children.push(this_id)
            });
           var count = children.length;

           if(count>0){
               if (count === 1) {
                    nest_message = '<p>There is one nested ' + singular + ' that will also be moved to the trash.';

               }else{
                    nest_message = '<p>There are ' + count + ' nested ' + plural + ' that will also be moved to the trash.';
               }
               trash_message = '<br>Empty the trash to remove them permanently.</br></p>';

           }else{
               trash_message = '<p>Empty the trash to remove it permanently.</p>';
           }
        }
    }
    else if(term_id){
        var title = jQuery(target).data('title');
        //var title = jQuery(target).closest('.task_container').find('.title').html();
        if(taxonomy === 'task_chains' || taxonomy === 'store_types') {
            text = '<p>You are about to delete the section <b>' + title + ".</b> The section will be deleted immediately and this can not be undone.</p>";
            var container = jQuery('.task_chain_container_'+term_id);
            var children = [];
            jQuery(container).find('.task_container').each(function(){
                var this_id = jQuery(this).data('post_id');
                children.push(this_id)
            });
            var count = children.length;

            if(count>0){
                if (count === 1) {
                    nest_message = '<p>There is one child ' + singular + ' that will also be moved to the trash.';
                    trash_message = '<br>Empty the trash to remove it permanently.</br>';
                }else{
                    nest_message = '<p>There are ' + count + ' children ' + plural + ' that will also be moved to the trash.';
                    trash_message = '<br>Empty the trash to remove them permanently.</br></p>';
                }
            }
        }else{
            text = '<p>You are about to delete the item <b>' + title + ".</b> It will be deleted immediately and this can not be undone.</p>";
            var container = jQuery('.badges_row_'+term_id);
            var children_terms = [];
            jQuery(container).find('.go_badge_wrap').each(function(){
                var this_id = jQuery(this).data('term_id');
                children_terms.push(this_id)
            });
            var count = children_terms.length;

            if(count>0){
                if (count === 1) {
                    nest_message = '<p>There is one child item that will also be deleted immediately.';
                }else{
                    nest_message = '<p>There are ' + count + ' items that will also be deleted immediately.';
                }
            }
        }




    }
    else if(map_id){
        var title = jQuery(target).data('title');
        //var title = jQuery(target).closest('.task_container').find('.title').html();

        var container = jQuery('.map_'+map_id);
        var children = [];
        jQuery(container).find('.go_task_chain').each(function(){
            var this_id = jQuery(this).data('post_id');
            children.push(this_id)
        });
        var count = children.length;

        if(count>0){
            swal.fire({
                type: 'warning',
                title: 'This map has sections.',
                text: 'Please delete all the sections on the map before deleting the map. No action will be taken',
                confirmButtonText:
                    "Continue",
            });
            return;
        }else{
            text = '<p>You are about to delete the map <b>'+ title +".</b> The section will be deleted immediately and this can not be undone.</p>";
            term_id = map_id;
        }
    }

    text = text + nest_message + trash_message;

    ///////////////
    tippyInstances.forEach(instance => {
        instance.hide();
    });
    Swal.fire({
        title: 'Are you sure?',
        html: text,
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: (login) => {
            //console.log('go_trash_post');

            jQuery(".go_quick_edit").off();
            var nonce = GO_FRONTEND_DATA.nonces.go_trash_post;


            return jQuery.ajax({
                type: 'post',
                url: MyAjax.ajaxurl,
                data:{
                    _ajax_nonce: nonce,
                    post_id: post_id,
                    term_id: term_id,
                    children: children,
                    children_terms: children_terms,
                    taxonomy: taxonomy,
                    action: 'go_trash_post',
                    is_frontend: is_frontend,
                }
            })
                .then(res => {
                    return res;
                })
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        console.log(result.value);
        if (result.value === 0) {
            Swal.showValidationMessage(
                `Error.`
            )
        }
        if (result.value === 'refresh') {
            Swal.fire({
                title: `Error.`
            })
        }
        if (result.value) {
            if(taxonomy === 'task_chains' || taxonomy === 'store_types') {
                jQuery('#mapwrapper').html(result.value);
                go_setup_map();
            }
            else if(taxonomy === 'go_badges'){
                jQuery('#stats_badges_page').html(result.value);
                go_setup_badges_page();
            }
            else if(taxonomy === 'user_go_groups'){
                jQuery('#stats_groups_page').html(result.value);
                go_setup_groups_page()
            }
            //jQuery('#maps').hide();
            Swal.fire({
                type: "success",
            })
        }
    });

    //var item = jQuery(target).data('item');
}

function go_quick_edit_show(target){
    console.log('go_quick_edit_show');
    var taxonomy = jQuery(target).data('taxonomy');
    if(!taxonomy){
        taxonomy = jQuery('#maps').data('taxonomy');
    }
    //console.log(target);
    //jQuery(target).parent().parent().find('.tools').hide();
    var html = jQuery(target).closest('.go_actions_wrapper_flex').find('.quickedit_form_container').html();
    //jQuery(target).closest('.quick_container').hide();
    tippyInstances.forEach(instance => {
        instance.hide();
    });
    Swal.fire({
        title: 'Quick Edit',
        html: html,
        showCancelButton: true,
        confirmButtonText: 'Save',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: (login) => {
            console.log('go_quick_edit');

            jQuery(".go_quick_edit").off();
            var nonce = GO_EVERY_PAGE_DATA.nonces.go_quick_edit;

            var post_id = jQuery(event.target).closest('.swal2-container').find('.quickedit_form').data('post_id');
            var title = jQuery(event.target).closest('.swal2-container').find('.post_title').val();
            var hidden = jQuery(event.target).closest('.swal2-container').find('.hidden_checkbox').is(":checked");
            var nested = jQuery(event.target).closest('.swal2-container').find('.nested_checkbox').is(":checked");
            var optional = jQuery(event.target).closest('.swal2-container').find('.optional_checkbox').is(":checked");

            var term_id = jQuery(event.target).closest('.swal2-container').find('.quickedit_form').data('term_id');
            var term_title = jQuery(event.target).closest('.swal2-container').find('.term_title').val();
            //hidden is the same
            var pod_checkbox = jQuery(event.target).closest('.swal2-container').find('.pod_checkbox').is(":checked");
            var locked_prev_checkbox = jQuery(event.target).closest('.swal2-container').find('.locked_prev_checkbox').is(":checked");




            //console.log(post_id);
            //console.log(hidden);
            //console.log(nested);
            //console.log(optional);
            //console.log(title);
            var res = 'test';
            return jQuery.ajax({
                type: 'post',
                url: MyAjax.ajaxurl,
                data:{
                    _ajax_nonce: nonce,
                    post_id: post_id,
                    nested: nested,
                    optional: optional,
                    hidden: hidden,
                    title: title,
                    term_id: term_id,
                    term_title: term_title,
                    pod_checkbox: pod_checkbox,
                    locked_prev_checkbox: locked_prev_checkbox,
                    action: 'go_quick_edit',
                    is_frontend: is_frontend,
                    taxonomy: taxonomy
                }
            })
            .then(res => {
                return res;
            })
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        console.log(result.value);
        if (result.value === 0) {
            Swal.showValidationMessage(
                `Error.`
            )
        }
        if (result.value === 'refresh') {
            Swal.fire({
                title: `Error.`
            })
        }
        if (result.value) {
            if(taxonomy === 'task_chains' || taxonomy === 'store_types') {
                jQuery('#mapwrapper').html(result.value);
                go_setup_map();
            }
            else if(taxonomy === 'go_badges'){
                jQuery('#stats_badges_page').html(result.value);
                go_setup_badges_page();
            }
            else if(taxonomy === 'user_go_groups'){
                jQuery('#stats_groups_page').html(result.value);
                go_setup_groups_page()
            }

            //jQuery('#maps').hide();
            Swal.fire({
                type: "success",
            })
        }
    });
    /*
    })
        .then((result) => {
            if (result.value) {
                go_quick_edit(event)

            } else {
                swal.fire({
                        text: "Your user data is safe.",
                        title: "No action taken."
                    }
                );
                jQuery('#go_disable_game_on_this_site').one("click", function() {go_disable_game_on_this_site_dialog();});



            }
        });
        */

}

/*
function go_quick_edit(event){
        console.log('go_quick_edit');
    //console.log(event.target);
    jQuery(".go_quick_edit").off();
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_quick_edit;
    var post_id = jQuery(event.target).closest('.swal2-container').find('.quickedit_form').data('post_id');
    var taxonomy = jQuery("#maps").data('taxonomy');
    console.log(taxonomy);
    var hidden = jQuery(event.target).closest('.swal2-container').find('.hidden_checkbox').is(":checked");
    var nested = jQuery(event.target).closest('.swal2-container').find('.nested_checkbox').is(":checked");
    var optional = jQuery(event.target).closest('.swal2-container').find('.optional_checkbox').is(":checked");

    var title = jQuery(event.target).closest('.swal2-container').find('.post_title').val();

    //console.log(post_id);
    //console.log(hidden);
    //console.log(nested);
    //console.log(optional);
    //console.log(title);

    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            _ajax_nonce: nonce,
            post_id: post_id,
            nested: nested,
            optional: optional,
            hidden: hidden,
            title: title,
            action: 'go_quick_edit',
            is_frontend: is_frontend,
            taxonomy: taxonomy,
        },

        error: function(jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400){
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [ {'wp-auth-check': false} ]);
            }
        },
        success: function( res ) {


        }
    });


}*/

function go_edit_frontend(target, post_id = null, new_post = false){
    swal.close();

    console.log('go_edit_frontend');
    //console.log(target);
    sessionStorage.setItem('go_acf_changes', 'false');
    jQuery(".go_edit_frontend").off();
    var loader_html = go_loader_html('fountain');
    if(post_id === null) {
        post_id = jQuery(target).data('post_id');
    }
    if(target !== null) {
        var term_id = jQuery(target).data('term_id');
        var new_parent_term = jQuery(target).data('new_parent_term');
        var new_child_term = jQuery(target).data('new_child_term');
        var term_name = jQuery(target).data('term_name');
        var new_store_item = jQuery(target).data('new_store_item');
        var chain_id = jQuery(target).data('chain_id');
        var chain_name = jQuery(target).data('chain_name');
        var settings = jQuery(target).data('settings');
        var group = jQuery(target).data('group');
        var title = jQuery(target).data('title');
        var url = window.location.href;


    }
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_edit_frontend;

    var taxonomy = jQuery(target).data('taxonomy');
    if(!taxonomy){
        taxonomy = jQuery('#maps').data('taxonomy');
        jQuery.featherlight.close();
    }




    var html = '<div id="edit_container">'+loader_html+'<div id="edit_wrap" style="display: none;"></div></div>';
    if(typeof window.tippyInstances !== 'undefined' ) {
        tippyInstances.forEach(instance => {
            instance.hide();
        });
    }

    jQuery.featherlight(html,
        {
            variant: 'go_edit_frontend_lightbox',
            iframeWidth: '95%',
            iframeHeight: '95%',
            //closeOnClick:   false,          /* Close lightbox on click ('background', 'anywhere', or false) */
            //closeOnEsc:     false,                  /* Close lightbox when pressing esc */
            beforeClose: function(event){
                console.log('closing');
                console.log(new_post);
                //console.log(this); // this contains all related elements
                var changes = sessionStorage.getItem('go_acf_changes');
                if(changes === 'true') {
                    swal.fire({
                        type: 'warning',
                        title: 'You have unsaved changes. ',
                        text: 'Are you sure you want to close the editing window?',
                        confirmButtonText:
                            "Keep Editing",
                        showCancelButton: true,
                        cancelButtonText: 'Close',
                    }).then((result) => {
                        if(result.dismiss === 'cancel'){
                            sessionStorage.setItem('go_acf_changes', 'false');
                            var current = jQuery.featherlight.current();
                            current.close();
                            if(new_post){
                                location.reload();
                            }
                        }
                    });
                    return false; // prevent lightbox from opening
                }else{
                    if(new_post){
                        location.reload();
                    }
                }
                jQuery(".go_edit_frontend_badge").off().one("click", function(){
                    go_edit_frontend(this);
                });

            },
            afterOpen: function(event){
                console.log(this);
                var box = this;
                console.log('ajax');
                jQuery.ajax({
                    type: 'post',
                    url: MyAjax.ajaxurl,
                    data:{
                        _ajax_nonce: nonce,
                        post_id: post_id,
                        tag_ID: term_id,
                        new_store_item: new_store_item,
                        new_parent_term: new_parent_term,
                        new_child_term: new_child_term,
                        taxonomy: taxonomy,
                        settings: settings,
                        group: group,
                        title: title,
                        url: url,
                        action: 'go_edit_frontend',
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

                        //jQuery(box.$content).find('#loader_container').html('<h3>Processing . . .</h3>').promise().done(function(){
                            jQuery(box.$content).find('#edit_wrap').html(res).promise().done(function(){
                                //your callback logic / code here
                                jQuery('#acf-field_5e37cdd9598cb').data('minimumInputLength', 4);
                                go_initialize_acf_form(function(){

                                    go_activate_tippy();
                                    jQuery('.select2').width('100%');
                                    sessionStorage.setItem('go_acf_changes', 'false');
                                    jQuery('#loader_container').hide(100, function(){
                                        jQuery('#edit_wrap').show(100, function(){
                                            jQuery(".wp-editor-area").each(function(){
                                                var go_mce_id = jQuery(this).attr('id');
                                                console.log("go_mce_id" + go_mce_id);
                                                go_activate_tinymce_on_task_change_stage(go_mce_id, 'admin');
                                                jQuery('.acf-field').change(function(e){
                                                    //Do stuff on field change
                                                    sessionStorage.setItem('go_acf_changes', 'true');
                                                });
                                            });

                                            if(new_child_term){
                                                console.log('new child term');
                                                if(taxonomy === 'task_chains') {

                                                    jQuery('#acf-field_5e35987e47073').select2("trigger", "select", {
                                                        data: {id: new_child_term, text: term_name}
                                                    });

                                                    jQuery('.acf-field-5e35979c47071 .acf-radio-list li input').last().trigger('click');

                                                }else if (taxonomy === 'store_types'){
                                                    jQuery('#acf-field_5e37bbe75b17d').select2("trigger", "select", {
                                                        data: {id: new_child_term, text: term_name}
                                                    });
                                                    jQuery('.acf-field-5e37bb8f5b17c .acf-radio-list li input').last().trigger('click');

                                                }
                                                else if (taxonomy === 'go_badges'){
                                                    jQuery('#acf-field_5e37ce341f358').select2("trigger", "select", {
                                                        data: {id: new_child_term, text: term_name}
                                                    });
                                                    jQuery('.acf-field-5e37cdfe1f357 .acf-radio-list li input').last().trigger('click');

                                                }
                                                else if (taxonomy === 'user_go_groups'){
                                                    jQuery('#acf-field_5e389128e24d3').select2("trigger", "select", {
                                                        data: {id: new_child_term, text: term_name}
                                                    });
                                                    jQuery('.acf-field-5e389128e24ab .acf-radio-list li input').last().trigger('click');

                                                }

                                                sessionStorage.setItem('go_acf_changes', 'false');

                                            }
                                            else if (new_store_item){
                                                console.log('NEW STORE ITEM');
                                                jQuery('#acf-field_5abde7f92fd6a-field_5abde7f964b9f-field_5abde7f980bdf').prop("checked", 'true').trigger('change').find('.acf-switch').addClass('-on').removeClass('-off');
                                                jQuery('#acf-field_5abde7f92fd6a-field_5abde7f964b9f-field_5abde7f980c65').select2("trigger", "select", {
                                                    data: {id: chain_id, text: chain_name}
                                                });

                                                //jQuery('.acf-field-5abde7f92fc75').hide();
                                                //jQuery('.acf-field-5e37bb8f5b17c .acf-radio-list li input').last().trigger('click');
                                            }



                                        });
                                    });

                                });
                            });
                        //});


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
                    }
                });
            }

        });

    //console.log(post_id);
    //console.log(hidden);
    //console.log(nested);
    //console.log(optional);
    //console.log(title);
}

function go_initialize_acf_form(callback) {

    acf.do_action('append', jQuery('.acf-form'));
   callback();
}


/*
function go_nested_hover(){

}

function go_nested_exit(){

}
*/

function go_nested_toggle(){
    console.log('go_nested_toggle');

    var list = jQuery(this).next();
    if(jQuery(this).hasClass('toggled_open')){
        jQuery(this).removeClass('open');
        jQuery(this).removeClass('toggled_open');
        list.hide();
        jQuery(this).find('.nested_icon').html("<i class='fas fa-caret-down'></i>");
    }else{
        jQuery(this).addClass('open');
        jQuery(this).addClass('toggled_open');
        list.show();
        jQuery(this).find('.nested_icon').html("<i class='fas fa-caret-up'></i>");
    }


}

function go_to_this_map(map_id) {
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_to_this_map;
    jQuery.ajax({
        type: "post",
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_to_this_map',
            map_id: map_id

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
        success: function (res) {
            go_after_ajax();
            console.log("success");
            featherlight
        }
    });
}

function go_show_map(mapid) {
//https://stackoverflow.com/questions/28180584/wordpress-update-user-meta-onclick-with-ajax
//https://wordpress.stackexchange.com/questions/216140/update-user-meta-using-with-ajax
//
    console.log('go_show_map');
    var loader_html = go_loader_html('big');

    var nonce = GO_EVERY_PAGE_DATA.nonces.go_update_last_map;
    var uid = jQuery('#go_map_user').data("uid");
    var taxonomy = jQuery('#maps').data("taxonomy");
    jQuery("#mapwrapper").html(loader_html);

	jQuery.ajax({
		type: "POST",
		url : MyAjax.ajaxurl,
			data: {
                is_frontend: is_frontend,
                action:'go_update_last_map',
				goLastMap : mapid,
                taxonomy : taxonomy,
                _ajax_nonce: nonce,
                uid: uid
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
			success:function(data) {
                go_after_ajax();
          		jQuery('#mapwrapper').html(data);
				console.log("success!");
                go_setup_map();

                jQuery( ".go_blog_user_task" ).off().one("click", function () {
                    go_blog_user_task(this);
                });
			}

	});
}



/* When the user clicks on the button,
toggle between hiding and showing the dropdown content */
function go_map_dropDown(vis) {

    //document.getElementById("go_Dropdown").classList.toggle("show");
    if(vis) {
        jQuery("#go_Dropdown").removeClass('hidden');
        jQuery("#go_drop_arrow .up").show();
        jQuery("#go_drop_arrow .down").hide();
    }else{
        jQuery("#go_Dropdown").addClass('hidden');
        jQuery("#go_drop_arrow .up").hide();
        jQuery("#go_drop_arrow .down").show();
    }

//fix for selecting text on open
    if (window.getSelection) {window.getSelection().removeAllRanges();}
    else if (document.selection) {document.selection.empty();}

}

function go_user_map(target) {
    console.log("go_user_map");

    var nonce = GO_EVERY_PAGE_DATA.nonces.go_user_map_ajax;
    var user_id = jQuery(target).data("user_id");
    var loader_html = go_loader_html('big');
    jQuery(".featherlight.map .featherlight-content ").html(loader_html);
    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            is_frontend: is_frontend,
            action: 'go_user_map_ajax',
            uid: user_id
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
        success: function (res) {
            go_after_ajax();
            if (-1 !== res) {
                jQuery.featherlight(res, {
                    variant: 'map',
                    beforeOpen: function() { jQuery.featherlight.close();},
                    afterContent: function () {
                        console.log("after");
                        //go_map_check_if_done();
                        jQuery(".go_blog_user_task").off().one("click", function () {
                            go_blog_user_task(this);
                        });

                        go_setup_map();

                        go_map_add_next_prev(user_id);

                    }
                });

            }

            jQuery(target).off().one("click", function(e){
                go_user_map(this);
            });

        }
    });

}

function go_map_add_next_prev(user_id){
    console.log("go_map_add_next_prev");
    var current = jQuery('.go_user_map[data-user_id='+user_id+']');
    console.log("current: " + current);
    var index = jQuery('.go_user_map').index(current);
    console.log("index: " + index);
    var prev = jQuery( '.go_user_map' )[ index -1 ];
    var next = jQuery( '.go_user_map' )[ index +1 ];

    if(prev !== 'undefined'){
        var prev_id = jQuery(prev).data("user_id");
        if(prev_id > 0) {
            jQuery(".go_map_prev").data("user_id", prev_id).off().show().on("click", function () {
                go_user_map(this);
            });
        }
        console.log("prev_id: " + prev_id);
    }

    if(next !== 'undefined'){
        var next_id = jQuery(next).data("user_id");
        if(next_id > 0) {
            jQuery(".go_map_next").data("user_id", next_id).off().show().on("click", function () {
                go_user_map(this);
            });
        }
        console.log("next_id: " + next_id);
    }

}


function go_update_badge_group_sort(listEl) {

    console.log('go_update_badge_group_sort');
    var terms = [];
    jQuery(listEl).find('.go_badge_wrap').each(function () {
        var term_id = jQuery(this).data('term_id');
        terms.push(term_id);
    });

    var term_id = jQuery(listEl).data('term_id');
    var taxonomy = jQuery(listEl).data('taxonomy');
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_update_badge_group_sort;

    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data: {
            _ajax_nonce: nonce,
            action: 'go_update_badge_group_sort',
            terms: terms,
            term_id: term_id,
            taxonomy: taxonomy
        },

        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status === 400) {
                jQuery(document).trigger('heartbeat-tick.wp-auth-check', [{'wp-auth-check': false}]);
            }
        },
        success: function (raw) {
        console.log('success');
        }
    });
}