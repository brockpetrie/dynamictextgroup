(function($) {
	
	var schematic = {
		labels		: new Array(),
		widths		: new Array(),
		required	: new Array(),
	};
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
		
		minw		: 60,
		area		: 780,
		boxOrder	: new Array(),

		init: function () {
			// Initialize stage area
			dtgEditor.area = $('#stageHolder').innerWidth()-8;
			$('#stage').width(dtgEditor.area);
			
			// Bind new field creation event to Add button
			$('#add').bind('click',function() { dtgEditor.fieldMaster(); return false; });
			// If exists, parse existing schema
			if ($('#fieldschema').val() != '') dtgEditor.parseSchema(JSON.parse($('#fieldschema').val()));
			
			var dupeMsg = $('<div></div>').attr('id','dupeMsg').addClass('msg msgbad').text('Duplicates exist! All labels must be unique.').hide().appendTo($('#messages'));
			
			$(window).resize(function() {
				dtgEditor.area = $('#stageHolder').innerWidth()-8;
				$('#stage').width(dtgEditor.area);
			});
		},
		
		
		parseSchema: function(schema) {
			if (schema.labels) {
				var ct = schema.labels.length;
				for (var i=0; i<ct; i++) {
					dtgEditor.fieldMaster(null, schema.labels[i], schema.handles[i], schema.widths[i], schema.required[i]);
				}
			}
		},
		
		
		buildSchema: function() {
			var _f = $('#fieldschema');
			var _r = $('#renfields');
			var _a = $('#addfields');
			var _d = $('#delfields');
			
			schematic.labels = [];
			schematic.widths = [];
			schematic.required = [];
			additions.labels = [];
			renames.labels = [];
			renames.handles = [];
			
			$(".box", "#stage").each(function() {
				schematic.labels.push($('.tfield', $(this)).val());
				schematic.widths.push(Math.round(dtgEditor.parseWidth($(this).innerWidth())*10)/10);
				schematic.required.push($('input[name="required"]', $(this)).attr('checked'));
				
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
			
			if (schematic.labels[0]) { _f.val(JSON.stringify(schematic)); } else { _f.val(''); }
			if (renames.labels[0]) { _r.val(JSON.stringify(renames)); } else { _r.val(''); }
			if (additions.labels[0]) { _a.val(JSON.stringify(additions)); } else { _a.val(''); }
			if (deletions.handles[0]) { _d.val(JSON.stringify(deletions)); } else { _d.val(''); }
		},
		
		
		fieldMaster: function(del, fLabel, fHandle, fWidth, fReq) {
			var oldOrder = dtgEditor.boxOrder.length;
			var emptyspace = false;
			
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
				var __new = fLabel ? '' : ' new';
				var boxy = $('<div></div>').attr('id', 'box'+(dtgEditor.boxOrder.length+1)).addClass('box'+__new).html('<div class="inner"><div class="handle"></div></div>');
				var __boxlabel = fLabel ? fLabel : 'Field '+(dtgEditor.boxOrder.length+1);
				var __boxhandle = fHandle ? fHandle : 'f'+(dtgEditor.boxOrder.length+1);
				
				var textfield = $('<input />').val(__boxlabel).attr('type', 'text').attr('class', 'tfield').attr('id', __boxhandle).attr('name', __boxhandle);
				$(textfield).appendTo($('.inner', boxy));
				$(textfield).change(function() {
					dtgEditor.nameCheck();
				});
				
				var optionsHolder = $('<div class="options"></div>');
				$(optionsHolder).html('<input type="hidden" style="display:none;" id="original" value="'+__boxlabel+'" />');
				
				var options = $('<ul></ul>').appendTo($(optionsHolder));
				
				var requiredBox = $('<input />').attr('type', 'checkbox').attr('name', 'required');
				if (fReq) $(requiredBox).attr('checked','checked');
				$(requiredBox).change(function() { dtgEditor.buildSchema(); });
				var requiredBoxLabel = $('<label></label>').append(requiredBox).append('Required');
				$('<li></li>').append(requiredBoxLabel).appendTo($(options));
				
				var deleteButton = $('<a class="dtgButton del" href="#">Delete Field</a>').bind('click',function() { dtgEditor.fieldMaster(boxy); return false; });
				$('<li></li>').append(deleteButton).appendTo($(options));
				
				$(optionsHolder).appendTo($('.inner', boxy));
				
				$(boxy).appendTo($('#stage'));
				$(boxy).resize(function() {
					//$(textfield).width($(boxy).width()-45);
				});
				
				var __boxwidth = fWidth ? fWidth : 100/(dtgEditor.boxOrder.length+1);
				$(boxy).width(__boxwidth+'%');
				
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
			if (!fWidth) {
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
			$("#stage").sortable({
				placeholder	: 'placeholder',
				handle		: '.handle',
				start		: function(event, ui) {
					$(ui.item).fadeTo('fast', 0.75);
					$('.placeholder').css('width', $(ui.item).innerWidth()-6);
				},
				stop		: function(event, ui) {
					dtgEditor.boxOrder = $('#stage').sortable('toArray');
					$(ui.item).fadeTo('fast', 1);
					dtgEditor.initResize();
				}
			});
			dtgEditor.boxOrder = $('#stage').sortable('toArray');
		},
	}
	
	
	$(document).ready(function () {
		dtgEditor.init();
	});
		
})(jQuery.noConflict());
