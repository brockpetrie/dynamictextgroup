(function($) {

	var schematic = [];
	
	var additions = {
		labels		: new Array()
	};
	var renames = {
		labels		: new Array(),
		handles		: new Array()
	};
	var deletions = {
		handles		: new Array()
	};
			

	var dtgEditor = {
		
		minw		: 80,
		area		: 780,
		boxOrder	: new Array(),

		init: function () {
			// Initialize stage area
			dtgEditor.area = $('#stageHolder').innerWidth()-10;
			$('#stage').width(dtgEditor.area);
			
			// Bind new field creation event to Add button
			$('#add').bind('click',function() { dtgEditor.fieldMaster(); return false; });
			// If exists, parse existing schema
			if ($('#fieldschema').val() != '') dtgEditor.parseSchema(JSON.parse($('#fieldschema').val()));
			
			var dupeMsg = $('<div></div>').attr('id','dupeMsg').addClass('msg msgbad').text('Duplicates exist! All labels must be unique.').hide().appendTo($('#messages'));
			
			$(window).resize(function() {
				dtgEditor.area = $('#stageHolder').innerWidth()-10;
				$('#stage').width(dtgEditor.area);
			});
		},
		
		
		parseSchema: function(schema) {
			if (schema[0].label) {
				var ct = schema.length;
				for (var i=0; i<ct; i++) {
					dtgEditor.fieldMaster(null, schema[i].label, schema[i].handle, schema[i].width, schema[i].options);
				}
			}
		},
		
		
		buildSchema: function() {
			var _f = $('#fieldschema');
			var _r = $('#renfields');
			var _a = $('#addfields');
			var _d = $('#delfields');
			
			schematic = [];
			additions.labels = [];
			renames.labels = [];
			renames.handles = [];
			
			$(".box", "#stage").each(function() {
				var field = {};
				field.label = $('.tfield', $(this)).val();
				field.width = Math.round(dtgEditor.parseWidth($(this).innerWidth())*10)/10;
				field.options = {};
				field.options.required = $('input[name="required"]', $(this)).attr('checked');
				field.options.type = $('#fieldType', $(this)).val();
				//if (field.options.type == 'select') field.options.selectItems = $('#selectItems', $(this)).val();
				if (field.options.type == 'select') {
					var str = $('#customSelect', $(this)).val();
					field.options.customSelect = str;
					//alert(field.options.customSelect);
					var str = $('#selectItems', $(this)).val();
					if (str.match("^\\[") && str.match("\\]$")) {
						field.options.selectItems = JSON.parse(str);
					} else {
						field.options.selectItems = str;
					}
				}
				if (field.options.type == 'text') field.options.validationRule = $('#validationRule', $(this)).val();
				schematic.push(field);
				
				if ($(this).hasClass('new')) {
					additions.labels.push($('.tfield', $(this)).val());
				} else {
					var orig = $('#original', $(this)).val();
					var val = $('.tfield', $(this)).val();
					if (orig != val) {
						renames.labels.push(val);
						renames.handles.push($('.tfield', $(this)).attr('id'));
					}
				}
			});
			
			if (schematic[0]) { _f.val(JSON.stringify(schematic)); } else { _f.val(''); }
			if (renames.labels[0]) { _r.val(JSON.stringify(renames)); } else { _r.val(''); }
			if (additions.labels[0]) { _a.val(JSON.stringify(additions)); } else { _a.val(''); }
			if (deletions.handles[0]) { _d.val(JSON.stringify(deletions)); } else { _d.val(''); }
		},
		
		
		fieldMaster: function(del, fLabel, fHandle, fWidth, fOpts) {
			var oldOrder = dtgEditor.boxOrder.length;
			var emptyspace = false;
			var isNew = fLabel ? false : true;
			
			if (del) {
				// Remove field
				emptyspace = $(del).innerWidth()/(dtgEditor.boxOrder.length-1);
				if (!$(del).hasClass('new')) {
					deletions.handles.push($('.tfield', $(del)).attr('id'));
				}
				$(del).remove();
				dtgEditor.buildSchema();
				
			} else {
				// Add field
				var newclass = fLabel ? '' : ' new';
				var fLabel = fLabel ? fLabel : 'Field '+(dtgEditor.boxOrder.length+1);
				var fHandle = fHandle ? fHandle : 'f'+(dtgEditor.boxOrder.length+1);
				if (!fOpts) {
					fOpts = {};
					fOpts.required = false;
					fOpts.type = 'text';
					fOpts.selectItems = '';
					fOpts.customSelect = '';
				}
				
				var boxy = $('<div></div>').attr('id', 'box'+(dtgEditor.boxOrder.length+1)).addClass('box'+newclass).html('<div class="inner"><div class="handle"></div></div>');
				
				// Field Label input
				var textfield = $('<input type="text" class="tfield" />').attr('id', fHandle).attr('name', fHandle).val(fLabel);
				$(textfield).appendTo($('.inner', boxy));
				$(textfield).change(function() {
					dtgEditor.nameCheck();
				});
				
				// Field Options holder
				var optionsHolder = $('<div class="options"></div>');
				var originalName = $('<input type="hidden" style="display:none;" id="original" />').val(fLabel).appendTo($(optionsHolder));
				var options = $('<ul class="optiondrop"></ul>');
				$(options).hide();
				$(optionsHolder).click(function() {
					$(options).slideToggle(150);
					$(this).toggleClass('active');
				});
				
				// Field Type select
				var fieldType = $('<select id="fieldType" name="fieldType"></select>')
					.append('<option value="text">Textfield</option>')
					.append('<option value="select">Select List</option>')
					.append('<option value="checkbox">Checkbox</option>')
					.append('<option value="radio">Radio Button</option>');
				$(fieldType).val(fOpts.type);
				var fieldTypeLabel = $('<label for="fieldType">Field Type<br /></label>').append(fieldType);
				
				// Select options
				var selectItems = $('<input type="text" name="selectItems" id="selectItems" value="" placeholder="e.g. Option 1, Option 2, Option 3" />');
				var selectItemsLabel = $('<label for="selectItems">List Values<br /></label>').append(selectItems).append('<em>Comma-separated values or JS object</em>');
				
				var customSelect = $('<input type="text" name="customSelect" id="customSelect" value="" />');
				var customSelectLabel = $('<label for="customSelect"><br />Custom Override<br /></label>').append(customSelect).append('<em>Advanced users only, see the readme</em>');
				
				// Validation rule
				var validationRule = $('<input type="text" name="validationRule" id="validationRule" value="" placeholder="Enter a regex pattern" />');
				var validationRuleLabel = $('<label for="validationRule">Validation Rule<br /></label>').append(validationRule).append('<br />');
				$('<a href="#" class="valOption">number</a>').click(function(){ $(validationRule).val('/^-?(?:\\d+(?:\\.\\d+)?|\\.\\d+)$/i'); dtgEditor.buildSchema(); return false; }).appendTo(validationRuleLabel);
				$('<a href="#" class="valOption">email</a>').click(function(){ $(validationRule).val('/^\\w(?:\\.?[\\w%+-]+)*@\\w(?:[\\w-]*\\.)+?[a-z]{2,}$/i'); dtgEditor.buildSchema(); return false; }).appendTo(validationRuleLabel);
				$('<a href="#" class="valOption">URI</a>').click(function(){ $(validationRule).val('/^[^\\s:\\/?#]+:(?:\\/{2,3})?[^\\s.\\/?#]+(?:\\.[^\\s.\\/?#]+)*(?:\\/[^\\s?#]*\\??[^\\s?#]*(#[^\\s#]*)?)?$/'); dtgEditor.buildSchema(); return false; }).appendTo(validationRuleLabel);
				
				// Required checkbox
				var requiredBox = $('<input type="checkbox" name="required" />');
				if (fOpts.required) $(requiredBox).attr('checked','checked');
				$(requiredBox).change(function() { dtgEditor.buildSchema(); });
				var requiredBoxLabel = $('<label></label>').append(requiredBox).append('Required');
				
				// Append field type options
				var fieldTypeHolder = $('<li></li>').append(fieldTypeLabel).appendTo($(options));
				var selectItemsHolder = $('<li></li>').append(selectItemsLabel).append(customSelectLabel).appendTo($(options)).hide();
				var validationRuleHolder = $('<li></li>').append(validationRuleLabel).appendTo($(options)).hide();
				var requiredBoxHolder = $('<li></li>').append(requiredBoxLabel).appendTo($(options));
				
				// Find current field type and show appropriate options
				switch(fOpts.type) {
					case 'select':
						$(selectItemsHolder).show();
						var items = fOpts.selectItems instanceof Object ? JSON.stringify(fOpts.selectItems) : fOpts.selectItems;
						$(selectItems).val(items);
						$(customSelect).val(fOpts.customSelect);
						break;
					case 'text':
						$(validationRuleHolder).show();
						$(validationRule).val(fOpts.validationRule);
						break;
				}
				
				$(selectItems).change(function() { dtgEditor.buildSchema(); });
				$(customSelect).change(function() { dtgEditor.buildSchema(); });
				$(validationRule).change(function() { dtgEditor.buildSchema(); });
				
				$(fieldType).change(function() {
					// clear options
					$(validationRuleHolder).slideUp(250);
					$(selectItemsHolder).slideUp(250);
					// show appropriate options
					switch($(this).val()) {
					case 'select':
						$(selectItemsHolder).slideDown(250);
						break;
					case 'text':
						$(validationRuleHolder).slideDown(250);
						break;
					case 'checkbox':
						break;
					case 'radio':
						break;
					}
					dtgEditor.buildSchema();
				});
				
				
				// Field Delete button
				var deleteButton = $('<button class="delete" href="#">Delete Field</button>').bind('click',function() { dtgEditor.fieldMaster(boxy); return false; });
				$('<li></li>').append(deleteButton).appendTo($(options));
				
				$(optionsHolder).appendTo($('.inner', boxy));
				$(options).appendTo($('.inner', boxy));
				
				$(boxy).appendTo($('#stage'));
				$(boxy).resize(function() {
					//$(textfield).width($(boxy).width()-45);
				});
				
				var fWidth = fWidth ? fWidth : 100/(dtgEditor.boxOrder.length+1);
				$(boxy).width(fWidth+'%');
				
				$(boxy).resizable({
					handles		: 'e',
					maxHeight	: 12,
					minHeight	: 12,
					minWidth	: dtgEditor.minw,
					disabled	: true,
					stop		: function() {
						dtgEditor.convertToPerc();
						dtgEditor.initResize();
					}
				});
				$('span', boxy).bind('click',function() { dtgEditor.fieldMaster(boxy); });
			}
			
			dtgEditor.refreshStage();
		
			// Adjust widths
			if (isNew) {
				var offsetw = 0;
				for (var i=0; i<oldOrder; i++) {
					var targ = $(".box", "#stage")[i];
					var curw = $(targ).innerWidth();
					var neww = emptyspace ? curw+emptyspace : Math.floor(curw*(oldOrder/dtgEditor.boxOrder.length));
					offsetw += neww;
					$(targ).width(neww);
				}
				if (!del) $(".box:last", "#stage").width(dtgEditor.area-offsetw);
				dtgEditor.convertToPerc();
			}
			dtgEditor.initResize();
			dtgEditor.nameCheck();
		},
		
		
		initResize: function() {
			// Set resizeables
			$(".box", "#stage").each(function (i, element) {
				var maxw = $(this).outerWidth(true) + $($(".box", "#stage")[i+1]).outerWidth(true) - dtgEditor.minw;
				$(this).resizable( "option", "disabled", false );
				$(this).resizable( "option", "alsoResizeReverse", $(".box", "#stage")[i+1] );
				$(this).resizable( "option", "maxWidth", maxw);
			});
			$(".box:last").resizable( "option", "disabled", true );
			dtgEditor.buildSchema();
		},
		
		
		convertToPerc: function() {
			// Converts field width values from px to percentages
			$(".box", "#stage").each(function () {
				var perc = dtgEditor.parseWidth($(this).outerWidth());
				$(this).width(perc+'%');
			});
		},
		
		
		parseWidth: function(val) {
			// Returns a width percentage
			var perc = (val/dtgEditor.area)*100;
			return perc;
		},
		
		
		nameCheck: function(v) {
			// Makes sure there aren't any duplicate labels
			var labels = new Array();
			var dupes = new Array();
			var ct = new Array();
			
			$('.tfield').each(function() { labels.push($(this).val()); });
			labels.sort();
			
			$.each(labels, function(i, val) {
				if (val == labels[i+1]) dupes.push(val);
				return (i != labels.length-1);
			});
			
			$('.tfield').each(function() {
				if ($.inArray($(this).val(), dupes) > -1) {
					$(this).parent().parent().addClass('error');
				} else {
					$(this).parent().parent().removeClass('error');
				}
			});
			
			if (dupes.length == 0) { 
				dtgEditor.buildSchema();
				$('#dupeMsg').fadeOut(250);
				$('input[type="submit"]').removeAttr('disabled');
			} else {
				$('#dupeMsg').fadeIn(250);
				$('input[type="submit"]').attr('disabled','disabled');
			}
		},
		
		
		refreshStage: function() {
			// Reinitiate the sortable object
			$('.handle', '#stage').mousedown(function() {
				$('.tfield', '#stage').blur();
			});
			$("#stage").sortable({
				placeholder	: 'placeholder',
				handle		: '.handle',
				start		: function(event, ui) {
					//$(ui.item).fadeTo('fast', 0.75);
					$('.placeholder').css('width', $(ui.item)[0].style.width).append('<div class="placeholder-inner"></div>');
				},
				stop		: function(event, ui) {
					dtgEditor.boxOrder = $('#stage').sortable('toArray');
					//$(ui.item).fadeTo('fast', 1);
					dtgEditor.initResize();
				}
			});
			dtgEditor.boxOrder = $('#stage').sortable('toArray');
		},
	}
	
	/*$('.dropdown').hide();

	$(document).bind('click', function(e) {
		var target = $( e.target );
		if ( target.closest('#nav').length < 1 ) {
			nav.find('ul.dropdown').hide();
			return;
		}
		if ( target.parent().is('span') ) {
			var li = target.closest('li.menu');
			li.siblings().find('ul.dropdown').hide();
			li.find('ul.dropdown').toggle();
			e.preventDefault();
		}
	})*/
	
	
	$(document).ready(function () {
		dtgEditor.init();
	});
		
})(jQuery.noConflict());
