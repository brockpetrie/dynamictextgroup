(function($) {

	/* * 	
	 * *	The Dynamic Text Group field provides a method to dynamically add a text field or text field groups to a section entry.
	 * *	@author: Brock Petrie, brockpetrie@gmail.com
	 * *	@source: http://github.com/brockpetrie/dynamictextgroup
	 * */
	 
	 
	// DynamicTextGroup stuff
	
	DynamicTextGroup = {
		checkHelper: function(type) {
			$('.fieldtype-'+type).each(function() {
				var checker = $('input[type="'+type+'"]', $(this));
				var checkData = $('input[type="hidden"]', $(this));
				if ($(checker).attr('checked')) {
					$(checkData).val('yes');
				} else {
					$(checkData).val('');
				}
			});
		},
		
		parseBadItems: function() {
			$('input#badItems').each(function() {
				var badItems = JSON.parse($(this).val());
				$.each(badItems, function(i, obj) {
					var targ = '.'+obj.handle;
					var targHolder = typeof(obj.index) == 'number' ? $('li:eq('+obj.index+')', 'div.stage') : 'div.stage';
					$(targ, targHolder).addClass('baditem');
				});
			});
		},
		
		constructed: function() {
			$('.fieldtype-radio').each(function() {
				var checker = $('input[type="radio"]', $(this));
				$(checker).change(function() {
					DynamicTextGroup.checkHelper('radio');
				});
			});
			$('.fieldtype-checkbox').each(function() {
				var checker = $('input[type="checkbox"]', $(this));
				$(checker).change(function() {
					DynamicTextGroup.checkHelper('checkbox');
				});
			});
		}
	}
	
		
	// Stage stuff
	
	$(document).ready(function() {
		$('.dtg .styled').each(function() {
			var sel = $(this);
			
			/*$('option.placeholder', this).attr('value', placeholder);*/
			sel.select2({allowClear: true});
			/*$('option.placeholder', this).attr('value', '');
			sel.removeClass('styled');
			
			sel.on("change", function() {
				if (sel.select2('val') == '') {
					sel.select2('data', {id: '', text: placeholder});
				}
			});*/
		});
		DynamicTextGroup.parseBadItems();
		DynamicTextGroup.constructed();
		
		// Initialize Duplicator
		$('div.field-dynamictextgroup').each(function() {
			var manager = $(this),
				help = manager.find('label i'),
				stage = manager.find('div.frame.dark'),
				selection = stage.find('ol');
				
			$('header', manager).mousedown(function() {
				$('.focus input', manager).blur();
			});
			
			if(!stage.is('.single')) {
				stage.symphonyDuplicator({
					orderable: true,
					collapsible: false
				});
				selection.symphonyOrderable({
					items: 'li',
					handles: 'header',
					ignore: 'input, textarea, select, a, span',
					delay: 500
				});
			}
			
			stage.on('constructshow.duplicator', 'li', function(event) {
				$('.styled', this).each(function() {
					$('option.placeholder', this).attr('value', $('option.placeholder', this).text());
					$(this).select2({allowClear: true});
					$('option.placeholder', this).attr('value', '');
					$(this).removeClass('styled');
				});
			});
			
			//$('.fields', manager).click(function() { event.stopPropagation(); });
			/*stage.bind('constructanim', function() {
				DynamicTextGroup.constructed();
			});*/
			
			// Hide label help
			help.hide();
			
			// Set errors
			selection.find('input.invalid').parents('span.fields').addClass('invalid');
							
			/*-----------------------------------------------------------------------*/

			// Text input focus
			selection.delegate('input', 'focus.textgroup', function(event) {
				var dofocus = 	$(this).parent().parent().parent().addClass('focus');
				var label = 	$(this).parent().find('label').html();
				help.html(label);
				help.fadeIn('fast');
			});
			selection.delegate('input', 'blur.textgroup', function(event) {
				$(this).parent().parent().parent().removeClass('focus');
				help.hide();
			});
			
			// Select focus
			selection.delegate('select', 'focus.textgroup', function(event) {
				var dofocus = 	$(this).parent().parent().parent().addClass('focus');
				var label = 	$(this).parent().find('label').html();
			});
			selection.delegate('select', 'blur.textgroup', function(event) {
				$(this).parent().parent().parent().removeClass('focus');
			});
			
			
		});
	});
	
	
	// Script to stylize the select lists
	$.fn.extend({
		customStyle : function(options) {
			/*if(!$.browser.msie || ($.browser.msie&&$.browser.version>6)){
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
			}*/
		}
	});
	
		
})(jQuery.noConflict());
