!function($){function i(i){}void 0!==acf.add_action?(acf.add_action("ready_field/type=CHILD_TAXONOMY","initialize_field"),acf.add_action("append_field/type=CHILD_TAXONOMY","initialize_field")):$(document).on("acf/setup_fields",function(i,d){$(d).find('.field[data-field_type="CHILD_TAXONOMY"]').each(function(){$(this)})})}(jQuery);