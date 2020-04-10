jQuery( document ).ready( function() {

    if(typeof GO_EDIT_STORE_DATA !== 'undefined') {
        var is_store = GO_EDIT_STORE_DATA.is_store_edit;
    }
    if (is_store) {
        var id = GO_EDIT_STORE_DATA.postid;
        var store_name = GO_EDIT_STORE_DATA.store_name;
        var link = "<a class='go_str_item ab-item'  data-post_id=" + id + "'' >View " + store_name + " Item</a>"
        //console.log(link);
        jQuery('#wp-admin-bar-view').html(link);
    }
});
