
jQuery(window).ready(function(){
    //jQuery(".mejs-container").hide();
	Vids_Fit_and_Box();
});

function Vids_Fit_and_Box(){
    runmefirst(function() {
        Max_width_and_LightboxNow();
       go_native_video_resize();
    });
};

function runmefirst(callback) {
    fitVidsNow();
    callback();
};


function fitVidsNow(){
        jQuery("body").fitVids();
       // var local_customSelector = "mejs-container";
    	jQuery("body").fitVids({customSelector: "video"});
}

function go_native_video_resize() {

    jQuery(window).resize(function() {

    	//initializes featherlight on the class "featherlight_wrapper_vid_link"
		jQuery('.featherlight_wrapper_vid_link').featherlight({
			targetAttr: 'href',
			afterOpen: function(event){
				jQuery(".featherlight-content").css("overflow","hidden")
			}
		});

		//fixes height on native WP video shortcode on resize of window
        jQuery("video.wp-video-shortcode").css("height", "");
        setTimeout(function(){ jQuery("mediaelementwrapper .wp-video-shortcode, .mejs-container").css("height", "");
            var vidHeight = jQuery("video.wp-video-shortcode").height();
            //console.log("h:" + vidHeight);
            jQuery(".mejs-container").css("height", vidHeight);
            jQuery(".fluid-width-video-wrapper:has(.mejs-container)").css("padding-top", "");
            //jQuery(".mejs-container").show();

        }, 1000);

    }).resize();

}

function Max_width_and_LightboxNow(){  
        //do stuff
		//add a max width video wrapper to the fitVid
		var _maxwidth = jQuery("#go_wrapper").data('maxwidth');
        //var fluid_width_video_wrapper = {};
        jQuery(".fluid-width-video-wrapper:not(.fit)").each(function(){

	        jQuery(this).wrap('<div class="max-width-video-wrapper" style="position:relative;"><div>');
	        jQuery(this).addClass('fit');
	        jQuery( ".max-width-video-wrapper").css("max-width", _maxwidth);
        });

    	//Toggle lightbox on and off based on option
    	var lightbox_switch = jQuery("#go_wrapper").data('lightbox');

    	if (lightbox_switch === 1){
            //alert (lightbox_switch);
			//add a featherlight lightroom wrapper to the fitvids iframes
			jQuery(".max-width-video-wrapper:not(.wrapped):has(iframe)").each(function(){
				jQuery(this).prepend('<a style="display:block;" class="featherlight_wrapper_iframe" href="#" ><span style="position:absolute; width:100%; height:100%; top:0; left: 0; z-index: 1;"></span></a>');
				jQuery(this).addClass('wrapped');

			});

            //adds a html link to the wrapper for featherlight lightbox

            jQuery('[class^="featherlight_wrapper_iframe"]').each(function(){
                var _src = jQuery(this).parent().find('.fluid-width-video-wrapper').parent().html();

                console.log("src2:" + _src);
                //_src="<div class=\"fluid-width-video-wrapper fit\" style=\"padding-top: 56.1905%;\"><iframe src=\"https://www.youtube.com/embed/zRvOnnoYhKw?feature=oembed?&autoplay=1\" frameborder=\"0\" allow=\"autoplay; encrypted-media\" allowfullscreen=\"\" name=\"fitvid0\"></iframe></div>"
                jQuery(this).attr("href", "<div id=\"go_video_container\" style=\"height: 90vh; overflow: hidden;\">" + _src + "</div>");
                //var _href = jQuery(this).attr("data-featherlight");
                //jQuery(this).attr("href", _href + '?&autoplay=1');
                //activates the lightbox
                //jQuery.featherlight.defaults.closeOnClick = true;
                //jQuery.featherlight.defaults.iframeWidth = '100%';
                //jQuery.featherlight.defaults.iframeHeight = '100%';
                //jQuery(this).featherlight();
                jQuery('.featherlight_wrapper_iframe').featherlight({
                    targetAttr: 'href',
                    closeOnEsc: true,
                    afterOpen: function(event){
                        jQuery(".featherlight-content").css({
							'width' : '90%',
                            'overflow' : 'hidden'
                        });
                        jQuery(".featherlight-content iframe").css({
                            'height' : '86vh'
                        });
                        jQuery(".featherlight-content iframe")[0].src += "&autoplay=1";
                        ev.preventDefault();

                    }
                });
            });


            //add a featherlight lightroom wrapper to the fitvids native video
            jQuery(".max-width-video-wrapper:not(.wrapped):has(video)").each(function(){
                //jQuery(this).prepend('<a style="display:block;" class="featherlight_wrapper_native_vid" href="#" data-featherlight="iframe" ><span style="position:absolute; width:100%; height:100%; top:0; left: 0; z-index: 4;"></span></a>');

                jQuery(this).prepend('<a  class="featherlight_wrapper_vid_native" href="#"><span style=\'position:absolute; width:100%; height:100%; top:0; left: 0; z-index: 4;\'></span></a>');


                jQuery(this).addClass('wrapped');
            });

            //adds a html link to the wrapper for featherlight lightbox
            setTimeout(function(){
            jQuery('[class^="featherlight_wrapper_vid_native"]').each(function(){
                    var _src = jQuery(this).parent().find('video').attr('src');
                    	//var _src = jQuery(this).attr('src');
                    console.log("SRC:" + _src);
                    	//jQuery(this).attr("href", _src);
                    	//var _href = jQuery(this).attr("href");
                    	//console.log("href:" + _href);
                    //jQuery(this).parent().find('source').attr("src", _src );
                	jQuery(this).attr("href", "<div id=\"go_video_container\" style=\"height: 90vh; overflow: hidden;\"> <video controls autoplay style=\"height: 100%;\"> <source src=\"" + _src + "\" type=\"video/mp4\">Your browser does not support the video tag.</video></div>" );
                    	//activates the lightbox
                    	//jQuery.featherlight.defaults.closeOnClick = true;
                    //jQuery.featherlight.defaults.iframeWidth = '100%';
                    //jQuery.featherlight.defaults.iframeHeight = '100%';
                    //jQuery(this).featherlight();
					jQuery('.featherlight_wrapper_vid_native').featherlight({
						targetAttr: 'href',
                        closeOnEsc: true,
						afterOpen: function(event){
							jQuery(".featherlight-content").css("overflow","hidden")
						}
					});
                });
            }, 200);

        }
 }

 function go_test_after(){
	console.log("hello");
 }
