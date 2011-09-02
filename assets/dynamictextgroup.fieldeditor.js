(function($) {

	/* * 	
	 * *	The Dynamic Text Group field provides a method to dynamically add a text field or text field groups to a section entry.
	 * *	@author: Brock Petrie, brockpetrie@gmail.com
	 * *	@source: http://github.com/brockpetrie/datetime
	 * */
	 
	var minw = 60;
	var area = 780;
	var boxOrder = new Array();
	var deletions = new Array();
	
	function parseSchema(schema) {
		if (schema != '') {
			schema = schema.split('||');
			var ct = schema.length;
			for (var i=0; i<ct; i++) {
				var itm = schema[i].split('//');
				fieldMaster(null, itm[0], itm[1], itm[2], itm[3]);
			}
		}
	}
	
	function buildSchema() {
		var schema = new Array();
		var renames = new Array();
		var additions = new Array();
		
		var _f = $('#fieldschema');
		var _r = $('#renfields');
		var _a = $('#addfields');
		var _d = $('#delfields');
		
		_f.attr('value', '');
		_a.attr('value', '');
		_r.attr('value', '');
		_d.attr('value', '');
		
		for (var i=0; i<boxOrder.length; i++) {
			var targ = '#'+boxOrder[i];
			
			schema[i] = $('.tfield', targ).attr('value');
			schema[i] += '//';
			schema[i] += parseWidth($(targ).innerWidth());
			schema[i] += '//';
			schema[i] += $('input', $('ul', targ)).attr('checked');
			
			if ($(targ).hasClass('new')) {
				additions.push($('.tfield', targ).attr('value'));
			} else {
				var orig = $('#original', targ).attr('value');
				var val = $('.tfield', targ).attr('value');
				if (orig != val) {
					renames.push($('.tfield', targ).attr('id')+'//'+val);
				}
			}
		}
		_f.attr('value', schema.join('||'));
		_r.attr('value', renames.join('||'));
		_a.attr('value', additions.join('||'));
		_d.attr('value', deletions.join('||'));
	}
	
	function fieldMaster(del, fLabel, fHandle, fWidth, fReq) {
		var oldOrder = boxOrder.length;
		var emptyspace = false;
		
		if (del) {
			// Remove field
			emptyspace = $(del).innerWidth()/(boxOrder.length-1);
			if (!$(del).hasClass('new')) {
				deletions.push($('.tfield', $(del)).attr('id'));
			}
			$(del).remove();
			buildSchema();
			
		} else {
			// Add field
			var __new = fLabel ? '' : ' new';
			var boxy = $('<div></div>').attr('id', 'box'+(boxOrder.length+1)).addClass('box'+__new).html('<div class="inner"><div class="handle"></div></div>');
			var __boxlabel = fLabel ? fLabel : 'Label';
			var __boxhandle = fHandle ? fHandle : 'f'+(boxOrder.length+1);
			var textfield = $('<input />').attr('type', 'text').attr('class', 'tfield').attr('id', __boxhandle).attr('name', __boxhandle).attr('value', __boxlabel);
			$(textfield).appendTo($('.inner', boxy));
			$(textfield).blur(function() {
				buildSchema();
			});
			
			var optionsHolder = $('<div class="options"></div>');
			$(optionsHolder).html('<input type="hidden" style="display:none;" id="original" value="'+__boxlabel+'" />');
			var options = $('<ul></ul>').appendTo($(optionsHolder));
			var __boxreq = fReq ? 'checked' : '';
			var option = '<label><input type="checkbox" name="required" '+ __boxreq +' /> Required</label>';
			$('<li></li>').append(option).appendTo($(options));
			option = $('<a href="#">Delete Field</a>').bind('click',function() { fieldMaster(boxy); return false; });
			$('<li></li>').append(option).appendTo($(options));
			$(optionsHolder).appendTo($('.inner', boxy));
			
			$(boxy).appendTo($('#stage'));
			
			var __boxwidth = fWidth ? Math.round(area*fWidth)+'px' : parseWidth(Math.round(area/(boxOrder.length+1))) ;
			$(boxy).css('width', __boxwidth);
			
			$(boxy).resizable({
				handles		: 'e',
				maxHeight	: 12,
				minHeight	: 12,
				minWidth	: minw,
				disabled	: true,
				stop		: function() {
					initResize();
				}
			});
			$('span', boxy).bind('click',function() { fieldMaster(boxy); });
		}
		
		refreshStage();
	
		// Adjust widths
		if (!fWidth) {
			var offsetw = 0;
			for (var i=0; i<oldOrder; i++) {
				var targ = $(".box", "#stage")[i];
				var curw = $(targ).innerWidth();
				var neww = emptyspace ? curw+emptyspace : Math.floor(curw*(oldOrder/boxOrder.length));
				offsetw += neww;
				$(targ).css('width', neww+'px');
			}
			if (!del) $(".box:last", "#stage").css('width', area-offsetw+'px');
		}
		initResize();
	}
	
	function parseWidth(val) {
		var perc = (val/area);
		return perc;
	}
	
	function initResize() {
		// Set resizeables
		for (var i=0; i < (boxOrder.length-1); i++) {
			var targ = $(".box", "#stage")[i];
			var maxw = $(targ).outerWidth(true) + $($(".box", "#stage")[i+1]).outerWidth(true) - minw;
			$(targ).resizable( "option", "disabled", false );
			$(targ).resizable( "option", "alsoResizeReverse", $(".box", "#stage")[i+1] );
			$(targ).resizable( "option", "maxWidth", maxw);
		}
		$(".box:last").resizable( "option", "disabled", true );
		
		buildSchema();
	}
	
	function refreshStage() {
		// Reinitiate the sortable object
		$("#stage").sortable({
			placeholder	: 'placeholder',
			handle		: '.handle',
			start		: function(event, ui) {
				$(ui.item).fadeTo('fast', 0.75);
				$('.placeholder').css('width', $(ui.item).innerWidth()-6);
			},
			stop		: function(event, ui) {
				boxOrder = $('#stage').sortable('toArray');
				$(ui.item).fadeTo('fast', 1);
				initResize();
			}
		});
		$( "#stage" ).disableSelection();
		boxOrder = $('#stage').sortable('toArray');
	}
	 
	 
	$(document).ready(function() {
		$("#add").bind('click',function() { fieldMaster(); return false; });
		parseSchema($('#fieldschema').attr('value'));
	});
		
})(jQuery.noConflict());
