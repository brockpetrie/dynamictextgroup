(function($) {

	/**
	 *	The Dynamic Text Group field provides a method to dynamically add a text field or text field groups to a section entry.
	 *	@author: Brock Petrie, brockpetrie@gmail.com
	 *	@source: http://github.com/brockpetrie/dynamictextgroup
	 */
	var DynamicTextGroup = {
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
	};

	// Duplicator stuff
	$(document).ready(function() {
		var $frame = $('div.frame', 'div.field-dynamictextgroup');

		DynamicTextGroup.parseBadItems();
		DynamicTextGroup.constructed();

		$frame.symphonyDuplicator().each(function() {
			var $self = $(this);

			// Custom selects
			$('.dtg select.styled').each(function() {
				$(this).select2().removeClass('styled');
			});

			// Set errors
			$self.find('input.invalid').parents('span.fields').addClass('invalid');

			/*-----------------------------------------------------------------------*/

			// Focus
			$self.on('focus.textgroup', 'input, select', function(event) {
				$(this).parent().parent().parent().addClass('focus');
			});

			$self.on('blur.textgroup', 'input, select', function(event) {
				$(this).parent().parent().parent().removeClass('focus');
			});
		});
	});

})(jQuery.noConflict());
