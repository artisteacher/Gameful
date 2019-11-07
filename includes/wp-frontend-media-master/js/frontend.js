//(function($) {

//$(document).ready( function() {
function go_upload_frontend(div_id, mime_types) {

    var val = "#" + div_id ;
    console.log("value: " + val);

    var file_frame; // variable for the wp.media file_frame

    // attach a click event (or whatever you want) to some element on your page
    //$( '#frontend-button' ).on( 'click', function( event ) {
    //event.preventDefault();

    //add ajax function that converts extensions into list of mime types for the media uploader
    var title = "Select a file"
    if (mime_types.length != 0) {
        var restricted = mime_types.replace(/,/g, ', ')
        title = title + " (Allowed types: " + restricted + ")";
    }

    // Add page slug to media uploader settings
    _wpPluploadSettings['defaults']['multipart_params']['admin_page']= 'gif';
    //if the file_frame has already been created, just reuse it
    if (file_frame) {
        file_frame.open();
        return;
    }
    else {
        file_frame = wp.media.frames.file_frame = wp.media({
            title: title,
            button: {
                text: 'Select',
            },
            multiple: false, // set this to true for multiple file selection
            library: {
                type: mime_types
            }
        });

    }

    jQuery.ajax({
        type: 'post',
        url: MyAjax.ajaxurl,
        data:{
            action: 'go_media_filter_ajax',
            mime_types : mime_types
        },
        success: function( res ) {
            console.log("filtered");
        }
    });

    file_frame.on('select', function () {
        const attachment = file_frame.state().get('selection').first().toJSON();
        // do something with the file here
        $('#frontend-button').attr('value', 'Change File');
        $('#go_stage_error_msg').hide();
        if (attachment.type == 'image') {
            console.log('image');
            var url = '';

            if(val === "#go_this_avatar"){//if this is a change avatar store item
                if (typeof attachment.sizes.thumbnail !== 'undefined') {
                    // your code here
                    url = attachment.sizes.thumbnail.url;

                }else{
                    url = attachment.url;
                }
                $('#go_change_avatar').show();
                $(val).attr('src', url);
                $(val).css('max-width', '150px');
                $(val).closest('a').attr('data-featherlight', url);
            }else {
                if (typeof attachment.sizes.medium !== 'undefined') {
                    // your code here
                    url = attachment.sizes.medium.url;

                }else{
                    url = attachment.url;
                }
                $(val).attr('src', url);
                $(val).css('max-width', '300px');
                $(val).closest('a').attr('data-featherlight', attachment.url);
            }

        }
        else{
            console.log('other');
            $(val).attr('src', attachment.icon);
        }
        $(val).attr('value', attachment.id);
        $(val).next().html(attachment.title);
        //$(val).closest('.go_checks_and_buttons').find('.file_title').html(attachment.title);
        //$('#go_result_media_name').html(attachment.title);

    });

    file_frame.open();
}
	//});
//});

//})(jQuery);