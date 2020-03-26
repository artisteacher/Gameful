if (typeof (go_is_reader_or_blog) !== 'undefined') {
    jQuery(document).ready(function() {

        //go_loadmore_reader();
       // jQuery('.go_loadmore_reader').off().one("click", function () {
            //go_loadmore_reader(this);
       // });
    });
}

/*
function go_loadmore_blog(){
    console.log('go_loadmore_blog');
    jQuery(function($){ // use jQuery code inside this to avoid "$ is not defined" error

        let query = $('.go_reader_footer').data('query');
        var button = $(this),
        data = {
            'action': 'loadmore',
            is_frontend: is_frontend,
            //'query': misha_loadmore_params.posts, // that's how we get params from wp_localize_script() function
            'page' : misha_loadmore_params.current_page,
            'myargs' : misha_loadmore_params.myargs,
            'query' : query,
        };
        console.log(data);
        jQuery.ajax({ // you can also use $.post here
            url : MyAjax.ajaxurl, // AJAX handler
            data : data,
            type : 'POST',
            beforeSend : function ( xhr ) {
                button.text('Loading...'); // change the button text, you can also add a preloader image
                console.log('go_loadmore_blog3');
            },
            success : function( data ){
                if( data ) {
                    //var prev = button.prev();
                    button.text( 'More posts' ).parent().before(data); // insert new posts
                    //console.log(prev)
                    misha_loadmore_params.current_page++;

                    if ( misha_loadmore_params.current_page == misha_loadmore_params.max_page )
                        button.remove(); // if last page, remove the button

                    go_blog_new_posts();

                    // you can also fire the "post-load" event here if you use a plugin that requires it
                    // $( document.body ).trigger( 'post-load' );
                } else {
                    button.remove(); // if no data, remove the button as well
                }
            }
        });
    });
}*/

function go_loadmore_reader(target){
    console.log('go_loadmore_reader');
    jQuery(function($){ // use jQuery code inside this to avoid "$ is not defined" error
        //$('.go_loadmore_reader').click(function(){
        //console.log("go_loadmore_reader");
        //console.log(target);

        let cards = jQuery('#go_cards_toggle').is(':checked');

        //get the offset
        let offset = $(target).data('offset');
        let limit = $('.go_reader_footer').data('limit');
        let query = $('.go_reader_footer').data('query');
        var button = $(target);
        var task = $(target).data('task');
        var postids = new Array();
        var nonce = GO_EVERY_PAGE_DATA.nonces.go_loadmore_reader;
        jQuery('.go_blog_post_wrapper').each(function( index ) {
            var post_id = jQuery( this ).data('postid') ;
            postids.push(post_id);
        });
        //go_loadmore_reader
        var data = {
                'action': 'go_loadmore_reader',
                'task' : task,
                is_frontend: is_frontend,
                'query' : query,
                'offset': offset,
                'limit': limit,
                'cards' : cards,
                postids: postids,
                _ajax_nonce: nonce,
            };
        console.log(button);


       /* if (cards){
            let last = jQuery(".go_blog_post_card_sizer").last();
        }*/

        $.ajax({ // you can also use $.post here
            url :  MyAjax.ajaxurl, // AJAX handler
            data : data,
            type : 'POST',
            beforeSend : function ( xhr ) {
                button.text('Loading...'); // change the button text, you can also add a preloader image
            },
            success : function( data ){
                if( data ) {
                    console.log(data);

                    if(jQuery('#go_cards').length){
                        if(task === 'go_mark_all_read' || task === 'go_read_printed') {
                            var grid = jQuery("#go_cards").html(data).masonry('reloadItems').masonry('layout');
                            jQuery("#go_post_found").html('<span>Posts were marked as read.</span>');
                            button.text('Posts were marked as read.');
                            jQuery('#go_read_printed_button').delay(2000).hide('slow');
                            jQuery("#go_post_found").delay(2000).hide('slow');
                        }else {
                            var grid = jQuery("#go_cards").append(data).masonry('reloadItems').masonry('layout');
                            button.text('More posts');
                        }

                        grid.imagesLoaded().progress( function() {
                            grid.masonry('layout');
                        });


                    }else {
                        button.text('More posts').parent().before(data); // insert new posts

                    }
                    go_blog_new_posts();

                    jQuery('.go_loadmore_reader').off().one("click", function () {
                        go_loadmore_reader(this);
                    });
                    jQuery('.go_loadmore_reader').data('offset', ++offset);
                    //go_reader_activate_buttons();


                    // you can also fire the "post-load" event here if you use a plugin that requires it
                    // $( document.body ).trigger( 'post-load' );
                } else {
                    button.remove(); // if no data, remove the button as well
                }
            }
        });
    });
}


