jQuery(document).ready(function() {
    go_loadmore_blog();
    go_loadmore_reader();
});

function go_loadmore_blog(){
    jQuery(function($){ // use jQuery code inside this to avoid "$ is not defined" error
        $('.go_loadmore_blog').click(function(){

            var button = $(this),
                data = {
                    'action': 'loadmore',
                    //'query': misha_loadmore_params.posts, // that's how we get params from wp_localize_script() function
                    'page' : misha_loadmore_params.current_page,
                    'myargs' : misha_loadmore_params.myargs
                };

            $.ajax({ // you can also use $.post here
                url : misha_loadmore_params.ajaxurl, // AJAX handler
                data : data,
                type : 'POST',
                beforeSend : function ( xhr ) {
                    button.text('Loading...'); // change the button text, you can also add a preloader image
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

    });
}

function go_loadmore_reader(){
    jQuery(function($){ // use jQuery code inside this to avoid "$ is not defined" error
        $('.go_loadmore_reader').click(function(){
            console.log("go_loadmore_reader");
            //get the offset
            let offset = $(this).data('offset');
            let limit = $(this).data('limit');
            let query = $(this).data('query');
            var button = $(this),
                data = {
                    'action': 'go_loadmore_reader',
                    'query' : query,
                    'offset': offset,
                    'limit': limit
                };

            $.ajax({ // you can also use $.post here
                url : misha_loadmore_params.ajaxurl, // AJAX handler
                data : data,
                type : 'POST',
                beforeSend : function ( xhr ) {
                    button.text('Loading...'); // change the button text, you can also add a preloader image
                },
                success : function( data ){
                    if( data ) {
                        console.log(data);

                        button.text('More posts').parent().before(data); // insert new posts
                        go_blog_new_posts();

                        jQuery('.go_loadmore_reader').data('offset', ++offset);

                        jQuery('#go_read_printed_button').off().on("click", function () {
                            go_reader_read_printed();
                        });


                        // you can also fire the "post-load" event here if you use a plugin that requires it
                        // $( document.body ).trigger( 'post-load' );
                    } else {
                        button.remove(); // if no data, remove the button as well
                    }
                }
            });
        });
    });
}


