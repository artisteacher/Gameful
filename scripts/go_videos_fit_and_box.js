
jQuery(document).ready(function(){
    // Target the #main id for fitVids
    jQuery("#main").fitVids();
    
    //add a max width video wrapper to the fitVid
    var fluid_width_video_wrapper = {};
    jQuery('[class^="fluid-width-video-wrapper"]').each(function(){
        jQuery(this).wrap('<div class="max-width-video-wrapper" style="position:relative;"><div>');
        });
    
    //add a featherlight lightroom wrapper to the oembed videos that WP generates
    var max_width_video_wrapper = {};
    jQuery('[class^="max-width-video-wrapper"]').each(function(){
        jQuery(this).prepend('<a style="display:block;" class="featherlight_wrapper" href="#" data-featherlight-iframe-width="100%" data-featherlight-iframe-height="100%" data-featherlight="iframe" ><span style="position:absolute; width:100%; height:100%; top:0; left: 0; z-index: 1;"></span></a>');
        //jQuery(this).prepend('<span style="position:absolute; width:100%; height:100%; top:0; left: 0; z-index: 1;"></span>');
        });
    
    //adds a html link to the wrapper for featherlight lightbox
    var featherlight_wrapper = {};
    jQuery('[class^="featherlight_wrapper"]').each(function(){
        var _src = jQuery(this).parent().find('iframe').attr('src');
        jQuery(this).attr("href", _src);
        var _href = jQuery(this).attr("href");
        jQuery(this).attr("href", _href + '&autoplay=1');
        
        
        //activates the lightbox
        jQuery.featherlight.defaults.closeOnClick = false;
        jQuery.featherlight.defaults.iframeWidth = '100%';
        jQuery.featherlight.defaults.iframeHeight = '100%';
        jQuery(this).featherlight();
        });
    
  });
   
(jQuery);
