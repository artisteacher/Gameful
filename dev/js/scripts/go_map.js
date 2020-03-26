if (typeof (go_is_map) !== 'undefined') {
    jQuery( document ).ready( function() {

        if(jQuery('#go_map_container').length > 0){
            jQuery('body').show(function() {
                // Animation complete.
                go_setup_map();
            });
        }



    });

    jQuery(document).keydown(function(event){
        if(event.which=="17" || event.which=="91") {
            openInNewTab = true;
            //console.log(openInNewTab);
        }
    });

    jQuery(document).keyup(function(){
        openInNewTab = false;
    });

    var openInNewTab = false;
}

function go_setup_map(){
    console.log("go_setup_map");

    go_show_map_loot(true);

    if(jQuery('#maps').hasClass('sortable')) {
        jQuery(".primaryNav").sortable({
            update: function (event, ui) {
                console.log('update');
                var terms = [];
                jQuery(event.target).find('.go_task_chain').each(function () {
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
                        map_id: map_id,
                    },
                    /**
                     * A function to be called if the request fails.
                     * Assumes they are not logged in and shows the login message in lightbox
                     */
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
            }
        });

        jQuery(".tasks").sortable({
            //helper: "clone",
            connectWith: ".connectedSortable, .go_nested_list",
            placeholder: "sortable-placeholder",
            start: function (event, ui) {
                jQuery('#maps').addClass('show_nest_border');
                jQuery('.tasks').addClass('sorting');
                var height = ui.item.height();
                jQuery('.tasks.sorting').css('min-height', height);
                ui.placeholder.height(ui.item.height());
            },
            stop: function (event, ui) {
                jQuery('#maps').removeClass('show_nest_border');
                jQuery('.tasks').removeClass('sorting');
            },
            update: function (event, ui) {
                console.log('update');
                var tasks = [];
                jQuery(event.target).find('.task_container').each(function () {
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
                var chain_id = jQuery(event.target).data('chain_id');
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
                    /**
                     * A function to be called if the request fails.
                     * Assumes they are not logged in and shows the login message in lightbox
                     */
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

        });

        jQuery(".go_nested_list").sortable({
            //helper: "clone",
            connectWith: ".connectedSortable, .go_nested_list",
            start: function (event, ui) {
                jQuery('#maps').addClass('show_nest_border');
            },
            stop: function (event, ui) {
                jQuery('#maps').removeClass('show_nest_border');
            },
            update: function (event, ui) {
                console.log('update');
                var tasks = [];
                jQuery(event.target).find('.go_nested_toggle').remove();
                jQuery(event.target).find('.go_nested_hover').contents().unwrap();
                jQuery(event.target).find('.go_nested_list').contents().unwrap();
                jQuery(event.target).find('.task_container').removeClass('hasNested');
                jQuery(event.target).find('.task_container').each(function () {
                    jQuery(this).find('.task_container').insertAfter(this);
                });




                jQuery(event.target).closest('.tasks').find('.task_container').each(function () {
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
                var chain_id = jQuery(event.target).closest('.tasks').data('chain_id');
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
                    /**
                     * A function to be called if the request fails.
                     * Assumes they are not logged in and shows the login message in lightbox
                     */
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

        });
    }

    go_disable_tooltips(true);

    //wrapped();

    jQuery(window).resize(function() {
        jQuery('.primaryNav').css('flex-direction', 'row');
        wrapped();
        //make sure icons don't overlap dropdown

    });


    jQuery(document.body).click( function(e) {
        go_map_closeMenu();
    });

    jQuery(".dropdown").click( function(e) {
        e.stopPropagation(); // this stops the event from bubbling up to the body
    });

    //add onclick to to optional toggles
    //jQuery('.go_nested_toggle').click(go_nested_toggle);
    jQuery('.go_nested_hover').hover(function() {
            el = jQuery(this);

            timeout = setTimeout(function(){
                // do stuff on hover
                console.log('go_nested_hover');

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


}


function wrapped() {
    console.log('wrapped');
    //var offset_top_prev;
    var offset_top_prev = jQuery('.go_task_chain').offset().top;
    //console.log(offset_top_prev);


    jQuery('.go_task_chain').each(function() {
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

    var drop = jQuery('.dropdown').offset();
    var width = jQuery('.dropdown').width();
    var icons = jQuery('.go_map_action_icons').offset();

    if((drop.left + width + 10) > icons.left ){
        jQuery('.dropdown').css('margin-top', 50);
    }else{
        jQuery('.dropdown').css('margin-top', 10);
    }

}

//CLOSE DROPDOWN MENU
function go_map_closeMenu(){
    jQuery('#go_Dropdown').fadeOut(200);
    //$('.add').removeClass('active');
}
//END CLOSE DROPDOWN




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
        } else {
            sessionStorage.setItem('map_actions', 'true');
            go_actions_tooltip();
            jQuery('.go_map_action_icons .tooltip_toggle .inactive').hide();
            jQuery('.go_map_action_icons .tooltip_toggle .active').show();

        }
    }else{
        if (map_actions === "false") {
            jQuery('.go_map_action_icons .tooltip_toggle .inactive').show();
            jQuery('.go_map_action_icons .tooltip_toggle .active').hide();
        }
    }

}


function go_quests_frontend(target){
    console.log('go_quests_frontend');

    var loader_html = go_loader_html('big');
    console.log(loader_html);
    jQuery("#quest_frontend_wrapper").append(loader_html);
    jQuery("#quest_frontend_loader").show();
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
            is_single_stage : 'true'
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
            //go_map_check_if_done();

            //go_load_daterangepicker('go_setup_clipboard');

            //Tabs
            /*
            if (jQuery('#records_tabs').length) {
                jQuery('#records_tabs').tabs();
                jQuery("#records_tabs").css("margin-left", '');
            }*/
            go_clipboard_activity_datatable(true);
            jQuery("#go_clipboard_activity_datatable").DataTable().columns.adjust().responsive.recalc();
            //add task select2
            go_make_select2_cpt('#go_task_select', 'tasks');



            go_make_select2_filter('user_go_sections','reader', true);

            go_make_select2_filter('user_go_groups','reader', true);

            go_make_select2_filter('go_badges','reader', true);


            //ADD Blue background and glow to filter button if unmatch toggle is clicked

            go_setup_filter_buttons(false);



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

function go_quick_edit_show(target){
    console.log('go_quick_edit_show');
    //console.log(target);
    //jQuery(target).parent().parent().find('.tools').hide();
    var html = jQuery(target).closest('.task_container').find('.quickedit_form_container').html();

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
            console.log('go_quick_edit_task');

            jQuery(".go_quick_edit_task").off();
            var nonce = GO_EVERY_PAGE_DATA.nonces.go_quick_edit_task;
            var post_id = jQuery(event.target).closest('.swal2-container').find('.quickedit_form').data('post_id');
            var hidden = jQuery(event.target).closest('.swal2-container').find('.hidden_checkbox').is(":checked");
            var nested = jQuery(event.target).closest('.swal2-container').find('.nested_checkbox').is(":checked");
            var optional = jQuery(event.target).closest('.swal2-container').find('.optional_checkbox').is(":checked");

            var title = jQuery(event.target).closest('.swal2-container').find('.post_title').val();

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
                    action: 'go_quick_edit_task',
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
            jQuery('#maps').html(result.value);
            go_setup_map();
            //jQuery('#maps').hide();
            Swal.fire({
                title: `Done.`
            })
        }
    });
    /*
    })
        .then((result) => {
            if (result.value) {
                go_quick_edit_task(event)

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

function go_quick_edit_task(event){
        console.log('go_quick_edit_task');
    //console.log(event.target);
    jQuery(".go_quick_edit_task").off();
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_quick_edit_task;
    var post_id = jQuery(event.target).closest('.swal2-container').find('.quickedit_form').data('post_id');
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
            action: 'go_quick_edit_task',
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


        }
    });


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
    var loader_html = go_loader_html('big');
    jQuery("#mapwrapper").html(loader_html);
    var nonce = GO_EVERY_PAGE_DATA.nonces.go_update_last_map;
    var uid = jQuery('#go_map_user').data("uid");

	jQuery.ajax({
		type: "POST",
		url : MyAjax.ajaxurl,
			data: {
                is_frontend: is_frontend,
                action:'go_update_last_map',
				goLastMap : mapid,
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
				//go_resizeMap();
                go_setup_map();

                jQuery( ".go_blog_user_task" ).off().one("click", function () {
                    go_blog_user_task(this);
                });
			}

	});
}


//Resize map function, also runs on window load
function go_resizeMap() {
 	console.log("resize");
	//get mapid from data
	var mapNum = jQuery("#maps").data('mapid');

    var mapID = "#map_" + mapNum;

    //var taskCount = ((jQuery(mapID + " .primaryNav > li").length)-1);
    var taskCount = ((jQuery(mapID + " .primaryNav > li").length));
    if (taskCount == 0){
        taskCount = 1;
    }
    if (taskCount == Infinity){
        taskCount = 1;
    }
    var taskWidth = (100/taskCount);
    var minWidth = ((jQuery(mapID).width()) / taskCount);

    console.log("taskCount: " + taskCount);
    console.log("minWidth: " + minWidth);

    //set the width of the tasks on the map
    //jQuery(mapID + " .primaryNav li").css("width", taskWidth + "%");

    /*
    if (taskWidth == 100) {
        jQuery(mapID + ' .primaryNav > li').css("width","90%");
        jQuery(mapID + ' .primaryNav li').css("float","right");
        jQuery(mapID + ' .tasks > li').css("width","80%");
        jQuery(mapID + " .primaryNav li").addClass("singleCol");
        //jQuery(mapID + " .primaryNav li").css("background", "url('../wp-content/plugins/game-on-master/styles/images/map/vertical-line.png') center top no-repeat");

    }
    else if (minWidth >= 130){
       // jQuery(mapID + " .primaryNav li").css("float","left");

        jQuery(mapID + " .primaryNav li").css("width", taskWidth + "%");
        jQuery(mapID + ' .tasks > li').css("width","100%");
        jQuery(mapID + " .primaryNav li").css("background", "");
        jQuery(mapID + " .primaryNav li").removeClass("singleCol");

    }
    else {
        jQuery(mapID + ' .primaryNav > li').css("width","100%");/jQuery(mapID + ' .primaryNav li').css("float","right");
        jQuery(mapID + ' .tasks > li').css("width","95%");
        //jQuery(mapID + " .primaryNav li").css("background", "url('../wp-content/plugins/game-on-master/styles/images/map/vertical-line.png') center top no-repeat");
        jQuery(mapID + " .primaryNav li").addClass("singleCol");
    }
    */

        jQuery('#sitemap').show();
        jQuery('#maps').show();
        go_activate_tippy();




        /*jQuery('.task, .go_task_chain').each(function(){
            var width = jQuery( this ).width();
            jQuery( this ).width(width - 1);
        });*/

}

/* When the user clicks on the button,
toggle between hiding and showing the dropdown content */
function go_map_dropDown() {
    //document.getElementById("go_Dropdown").classList.toggle("show");
    jQuery("#go_Dropdown").toggle();
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
                        //go_resizeMap();
                        jQuery(window).on('resize', function () {
                            //go_resizeMap();
                        });

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


//I think this was supposed to check the dropdown to see if the maps were done.
//It doesn't work
/*
function go_map_check_if_done() {
    go_resizeMap();
    //declare idArray
    var idArray = [];
    //make array of all the maps ids
    jQuery('.map').each(function () {
        idArray.push(this.id);
    });
    console.log("IDS" + idArray);
    console.log(idArray.length);
    //for each map do something
    var mapNum = 0;
    for (var i = 0; i < idArray.length; i++){
        var mapNum = mapNum++;
        var mapNumID = "#mapLink_" + mapNum;
        var mapNumClass = "#mapLink_" + mapNum + ' .mapLink';
        var mapID = "#map_" + mapNum;
        var countAvail = "#" + idArray[i] + " .available_color";
        var countDone = "#" + idArray[i] + " .checkmark";
        var numAvail = jQuery(countAvail).length;
        var numDone = jQuery(countDone).length;


        if (numAvail == 0){
            if (numDone == 0){

                jQuery(mapNumID).addClass("filtered");
            }
            else {

                jQuery(mapNumID).addClass("done");
                jQuery(mapNumClass).addClass("checkmark");
            }
        }
    }

    //go_resizeMap();
  }
  */