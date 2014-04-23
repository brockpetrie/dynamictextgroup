(function($) {

	$.fn.dtgEditor = function(options) {
		var objects = this,
			settings = {
				minw: 80,
				area: 780
			};

		$.extend(settings, options);

	/*-------------------------------------------------------------------------
		Utilities
	-------------------------------------------------------------------------*/

		function parseWidth(val) {
			// Returns a width percentage
			var perc = (val / settings.area) * 100;

			return perc;
		}

	/*-------------------------------------------------------------------------
		Init
	-------------------------------------------------------------------------*/

		// Initialise
		objects.each(function dtgInit() {
			var dtg = $(this),
				boxOrder = [];

			// Local variables
			dtg.schematic = [];
			dtg.additions = {
				labels: []
			};
			dtg.deletions = {
				handles: []
			};
			dtg.renames = {
				labels: [],
				handles: []
			};

			// Functions
			dtg.parseSchema = function(schema) {
				if (schema[0].label) {
					var ct = schema.length;
					for (var i=0; i<ct; i++) {
						dtg.fieldMaster(null, schema[i].label, schema[i].handle, schema[i].width, schema[i].options);
					}
				}
			};
			dtg.fieldMaster = function(del, fLabel, fHandle, fWidth, fOpts) {
				var oldOrder = boxOrder.length;
				var emptyspace = false;
				var isNew = fLabel ? false : true;

				if (del) {
					// Remove field
					emptyspace = $(del).innerWidth()/(boxOrder.length-1);
					if (!$(del).hasClass('new')) {
						dtg.deletions.handles.push($('.tfield', $(del)).attr('id'));
					}
					$(del).remove();
					dtg.buildSchema();

				} else {
					// Add field
					var newclass = fLabel ? '' : ' new';
					var fLabel = fLabel ? fLabel : 'Field '+(boxOrder.length+1);
					var fHandle = fHandle ? fHandle : 'f'+(boxOrder.length+1);
					if (!fOpts) {
						fOpts = {};
						fOpts.required = false;
						fOpts.type = 'text';
						fOpts.selectItems = '';
						fOpts.customSelect = '';
					}

					var boxy = $('<div></div>').attr('id', 'box'+(boxOrder.length+1)).addClass('box'+newclass).html('<div class="inner"><div class="handle"></div></div>');

					// Field Label input
					var textfield = $('<input type="text" class="tfield" />').attr('id', fHandle).attr('name', fHandle).val(fLabel);
					$(textfield).appendTo($('.inner', boxy)).change(function() {
						dtg.nameCheck();
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
					$('<a href="#" class="valOption">number</a>').click(function(){ $(validationRule).val('/^-?(?:\\d+(?:\\.\\d+)?|\\.\\d+)$/i'); dtg.buildSchema(); return false; }).appendTo(validationRuleLabel);
					$('<a href="#" class="valOption">email</a>').click(function(){ $(validationRule).val('/^\\w(?:\\.?[\\w%+-]+)*@\\w(?:[\\w-]*\\.)+?[a-z]{2,}$/i'); dtg.buildSchema(); return false; }).appendTo(validationRuleLabel);
					$('<a href="#" class="valOption">URI</a>').click(function(){ $(validationRule).val('/^[^\\s:\\/?#]+:(?:\\/{2,3})?[^\\s.\\/?#]+(?:\\.[^\\s.\\/?#]+)*(?:\\/[^\\s?#]*\\??[^\\s?#]*(#[^\\s#]*)?)?$/'); dtg.buildSchema(); return false; }).appendTo(validationRuleLabel);

					// Required checkbox
					var requiredBox = $('<input type="checkbox" name="required" />');
					if (fOpts.required) $(requiredBox).attr('checked','checked');
					$(requiredBox).change(function() { dtg.buildSchema(); });
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

					$(selectItems).change(function() { dtg.buildSchema(); });
					$(customSelect).change(function() { dtg.buildSchema(); });
					$(validationRule).change(function() { dtg.buildSchema(); });

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
						case 'radio':
							break;
						}
						dtg.buildSchema();
					});


					// Field Delete button
					var deleteButton = $('<button class="delete" href="#">Delete Field</button>').bind('click',function() { dtg.fieldMaster(boxy); return false; });
					$('<li></li>').append(deleteButton).appendTo($(options));

					$(optionsHolder).appendTo($('.inner', boxy));
					$(options).appendTo($('.inner', boxy));

					$(boxy).appendTo($('.dtg-stage', dtg));

					var fWidth = fWidth ? fWidth : 100/(boxOrder.length+1);
					$(boxy).width(fWidth+'%');

					$(boxy).resizable({
						handles		: 'e',
						maxHeight	: 12,
						minHeight	: 12,
						minWidth	: settings.minw,
						disabled	: true,
						stop		: function() {
							dtg.convertToPerc();
							dtg.initResize();
						}
					});
					$('span', boxy).bind('click',function() { dtg.fieldMaster(boxy); });
				}

				dtg.refreshStage();

				// Adjust widths
				if (isNew) {
					var offsetw = 0;
					for (var i=0; i<oldOrder; i++) {
						var targ = $(".box", dtg.find(".dtg-stage"))[i];
						var curw = $(targ).innerWidth();
						var neww = emptyspace ? curw+emptyspace : Math.floor(curw*(oldOrder/boxOrder.length));
						offsetw += neww;
						$(targ).width(neww);
					}
					if (!del) $(".box:last", dtg.find(".dtg-stage")).width(settings.area-offsetw);
					dtg.convertToPerc();
				}
				dtg.initResize();
				dtg.nameCheck();
			};
			dtg.buildSchema = function() {
				var _f = $('.dtg-fieldschema', dtg);
				var _r = $('.dtg-renfields', dtg);
				var _a = $('.dtg-addfields', dtg);
				var _d = $('.dtg-delfields', dtg);

				dtg.schematic = [];
				dtg.additions.labels = [];
				dtg.renames.labels = [];
				dtg.renames.handles = [];

				$(".box", dtg.find(".dtg-stage")).each(function() {
					var field = {};
					field.label = $('.tfield', $(this)).val();
					field.width = Math.round(parseWidth($(this).innerWidth())*10)/10;
					field.options = {};
					field.options.required = $('input[name="required"]', $(this)).is(':checked');
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
					dtg.schematic.push(field);

					if ($(this).hasClass('new')) {
						dtg.additions.labels.push($('.tfield', $(this)).val());
					} else {
						var orig = $('#original', $(this)).val();
						var val = $('.tfield', $(this)).val();
						if (orig != val) {
							dtg.renames.labels.push(val);
							dtg.renames.handles.push($('.tfield', $(this)).attr('id'));
						}
					}
				});

				if (dtg.schematic[0]) { _f.val(JSON.stringify(dtg.schematic)); } else { _f.val(''); }
				if (dtg.renames.labels[0]) { _r.val(JSON.stringify(dtg.renames)); } else { _r.val(''); }
				if (dtg.additions.labels[0]) { _a.val(JSON.stringify(dtg.additions)); } else { _a.val(''); }
				if (dtg.deletions.handles[0]) { _d.val(JSON.stringify(dtg.deletions)); } else { _d.val(''); }
			};
			dtg.initResize = function() {
				// Set resizeables
				$(".box", dtg.find(".dtg-stage")).each(function (i, element) {
					var maxw = $(this).outerWidth(true) + $($(".box", dtg.find(".dtg-stage"))[i+1]).outerWidth(true) - settings.minw;

					$( ".selector" ).resizable({
						disabled: false,
						alsoResizeReverse: $(".box", dtg.find(".dtg-stage"))[i+1],
						maxWidth: maxw
					});
				});

				$(".box:last", dtg).resizable({
					disabled: true
				});

				dtg.buildSchema();
			};
			dtg.convertToPerc = function() {
				// Converts field width values from px to percentages
				$(".box", dtg.find(".dtg-stage")).each(function () {
					var perc = parseWidth($(this).outerWidth());
					$(this).width(perc+'%');
				});
			};
			dtg.nameCheck = function(v) {
				// Makes sure there aren't any duplicate labels
				var labels = new Array();
				var dupes = new Array();
				var ct = new Array();

				$('.tfield', dtg).each(function() { labels.push($(this).val()); });
				labels.sort();

				$.each(labels, function(i, val) {
					if (val == labels[i+1]) dupes.push(val);
					return (i != labels.length-1);
				});

				$('.tfield', dtg).each(function() {
					if ($.inArray($(this).val(), dupes) > -1) {
						$(this).parent().parent().addClass('error');
					} else {
						$(this).parent().parent().removeClass('error');
					}
				});

				if (dupes.length == 0) {
					dtg.buildSchema();
					$('#dupeMsg', dtg).fadeOut(250);
					$('input[type="submit"]').removeAttr('disabled');
				} else {
					$('#dupeMsg', dtg).fadeIn(250);
					$('input[type="submit"]').attr('disabled','disabled');
				}
			};
			dtg.refreshStage = function() {
				// Reinitiate the sortable object
				$('.handle', dtg.find('.dtg-stage')).mousedown(function() {
					$('.tfield', dtg.find('.dtg-stage')).blur();
				});
				dtg.find(".dtg-stage").sortable({
					placeholder	: 'placeholder',
					handle		: '.handle',
					start		: function(event, ui) {
						//$(ui.item).fadeTo('fast', 0.75);
						$('.placeholder').css('width', $(ui.item)[0].style.width).append('<div class="placeholder-inner"></div>');
					},
					stop		: function(event, ui) {
						boxOrder = dtg.find('.dtg-stage').sortable('toArray');
						dtg.initResize();
					}
				});
				boxOrder = dtg.find('.dtg-stage').sortable('toArray');
			};

			// Initialise stage
			$(window).resize(function() {
				settings.area = $('.dtg-stageholder', dtg).innerWidth() - 10;
				$('.dtg-stage', dtg).width(settings.area);
			}).trigger('resize');

			// Bind new field creation event to Add button
			dtg.on('click', '.dtg-add', function() {
				dtg.fieldMaster();

				return false;
			});

			// If exists, parse existing schema
			if ($('.dtg-fieldschema', dtg).val() != '') {
				dtg.parseSchema(JSON.parse($('.dtg-fieldschema', dtg).val()));
			}

			var dupeMsg = $('<div></div>')
				.attr('id','dupeMsg')
				.addClass('msg msgbad')
				.text('Duplicates exist! All labels must be unique.')
				.hide().appendTo($('.dtg-messages', dtg));
		});
	};

	$(document).ready(function () {
		$('.field-dynamictextgroup').dtgEditor();
	});


	// jQuery UI Resizeable plugin that allows for inverse sizing in the Field Editor (i.e. making one field larger makes its adjacent sibling smaller)
	$.ui.plugin.add("resizable","alsoResizeReverse",{start:function(){var e=$(this).data("ui-resizable"),t=e.options,n=function(e){$(e).each(function(){var e=$(this);e.data("ui-resizable-alsoresize-reverse",{width:parseInt(e.width(),10),height:parseInt(e.height(),10),left:parseInt(e.css("left"),10),top:parseInt(e.css("top"),10)})})};if(typeof t.alsoResizeReverse==="object"&&!t.alsoResizeReverse.parentNode){if(t.alsoResizeReverse.length){t.alsoResizeReverse=t.alsoResizeReverse[0];n(t.alsoResizeReverse)}else{$.each(t.alsoResizeReverse,function(e){n(e)})}}else{n(t.alsoResizeReverse)}},resize:function(e,t){var n=$(this).data("ui-resizable"),r=n.options,i=n.originalSize,s=n.originalPosition,o={height:n.size.height-i.height||0,width:n.size.width-i.width||0,top:n.position.top-s.top||0,left:n.position.left-s.left||0},u=function(e,n){$(e).each(function(){var e=$(this),r=$(this).data("ui-resizable-alsoresize-reverse"),i={},s=n&&n.length?n:e.parents(t.originalElement[0]).length?["width","height"]:["width","height","top","left"];$.each(s,function(e,t){var n=(r[t]||0)-(o[t]||0);if(n&&n>=0){i[t]=n||null}});e.css(i)})};if(typeof r.alsoResizeReverse==="object"&&!r.alsoResizeReverse.nodeType){$.each(r.alsoResizeReverse,function(e,t){u(e,t)})}else{u(r.alsoResizeReverse)}},stop:function(){$(this).removeData("resizable-alsoresize-reverse")}})

})(jQuery.noConflict());
