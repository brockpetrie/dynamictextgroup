(function($) {

	/* * 	
	 * *	The Dynamic Text Group field provides a method to dynamically add a text field or text field groups to a section entry.
	 * *	@author: Brock Petrie, brockpetrie@gmail.com
	 * *	@source: http://github.com/brockpetrie/datetime
	 * */
	 
	$(document).ready(function() {

		// Initialize Stage
		$('div.field-dynamictextgroup').each(function() {
			var manager = $(this),
				help = manager.find('label i'),
				stage = manager.find('div.stage'),
				selection = stage.find('ul.selection');
				
			// Hide label help
			help.hide();
			
			// Set errors
			selection.find('input.invalid').parents('span.fields').addClass('invalid');
							
			/*-----------------------------------------------------------------------*/

			// Removing the focus
			selection.delegate('input', 'blur.textgroup', function(event) {
				var input = $(this),
					fields = input.parent().parent(),
					end;

				fields.removeClass('focus');
				help.hide();
			});
		
			// Adding the focus
			selection.delegate('input', 'focus.textgroup', function() {
				var input = $(this),
					fields = input.parent().parent().addClass('focus'),
					label = input.parent().find('label').html();
					
				help.html(label);
				help.fadeIn('fast');
			});
			
		});

	});
		
})(jQuery.noConflict());
