(function($) {

	/* * 	
	 * *	The Dynamic Text Group field provides a method to dynamically add a text field or text field groups to a section entry.
	 * *	@author: Brock Petrie, brockpetrie@gmail.com
	 * *	@source: http://github.com/brockpetrie/datetime
	 * */
	 
	$(document).ready(function() {
		$('.styled').customStyle();
		// Initialize Stage
		$('div.field-dynamictextgroup').each(function() {
			var manager = $(this),
				help = manager.find('label i'),
				stage = manager.find('div.stage'),
				selection = stage.find('ul.selection');
			
			stage.bind('constructanim', function() {
				$('.styled').customStyle();
			});
			
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
	
	// Script to stylize the select lists
	$.fn.extend({
		customStyle : function(options) {
			if(!$.browser.msie || ($.browser.msie&&$.browser.version>6)){
				return this.each(function() {
					var currentSelected = $(this).find(':selected');
					$(this).after('<span class="customStyleSelectBox"><span class="customStyleSelectBoxInner">'+currentSelected.text()+'</span></span>').css({position:'absolute', opacity:0,fontSize:$(this).next().css('font-size')});
					var selectBoxSpan = $(this).next();
					var selectBoxWidth = parseInt($(this).width()) - parseInt(selectBoxSpan.css('padding-left')) -parseInt(selectBoxSpan.css('padding-right'));			
					var selectBoxSpanInner = selectBoxSpan.find(':first-child');
					selectBoxSpan.css({display:'inline-block'});
					selectBoxSpanInner.css({width:'100%', display:'inline-block'});
					var selectBoxHeight = parseInt(selectBoxSpan.height()) + parseInt(selectBoxSpan.css('padding-top')) + parseInt(selectBoxSpan.css('padding-bottom'));
					$(this).height(selectBoxHeight).change(function(){
						// selectBoxSpanInner.text($(this).val()).parent().addClass('changed');   This was not ideal
						selectBoxSpanInner.text($(this).find(':selected').text()).parent().addClass('changed');
						// Thanks to Juarez Filho & PaddyMurphy
					});
					$(this).removeClass('styled');
				});
			}
		}
	});
	
		
})(jQuery.noConflict());
