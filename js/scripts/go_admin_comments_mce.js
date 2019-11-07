//This file is loaded by a filter in the shortcode.php file.
( function() {

	tinymce.PluginManager.add( 'go_admin_comment', function( editor, url ) {
		editor.addButton( 'go_admin_comment', {
			title: "Insert Admin Comment",
			onclick: function( e ) {
				go_tinymce_insert_content_admin ( editor, '[comment][/comment]' );
			},
		});
	});

})();


function go_tinymce_insert_content_admin( editor, content ) {
	console.log('go_tinymce_insert_content');
	if ( content.search( '("|\' )' ) !== -1 ) {
		var content_index = content.search( '("|\' )' ) + 1;
	} else if ( content.search( '\\]\\[' ) !== -1 ) {
		var content_index = content.search( '\\]\\[' ) + 1;
	}
	if ( typeof( content_index ) !== 'undefined' ) {
		editor.insertContent( content );
		var bm_1 = editor.selection.getBookmark( 1 );
		var bm_2 = bm_1;
		var bm_2_node = bm_2.rng.startContainer;
		bm_2.rng.setStart( bm_2_node, content_index );
		bm_2.rng.setEnd( bm_2_node, content_index );
		editor.selection.moveToBookmark( bm_2 );
	} else {
		editor.insertContent( content );
	}
}