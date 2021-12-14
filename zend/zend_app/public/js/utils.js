/* aPortal */

jQuery.fn.swap = function (b) {
	b = jQuery(b)[0];
	var a = this[0],
	    a2 = a.cloneNode (true),
	    b2 = b.cloneNode (true),
	    stack = this;
 
	a.parentNode.replaceChild (b2, a);
	b.parentNode.replaceChild (a2, b);
 
	stack[0] = a2;
	return this.pushStack( stack );
};

/**
 * AJAX form plugin for jQuery
 */

jQuery.fn.extend({
	ajaxSubmit: function ( errorCallback, successCallback ) {
		var form;
		var sendValues = {};

		// submit button
		if (this.is(":submit")) {
			form = this.parents("form");
			sendValues[this.attr("name")] = this.val() || "";

		// form
		} else if (this.is("form")) {
			form = this;

		// invalid element, do nothing
		} else {
			return null;
		}

		// validation
		if (form.get(0).onsubmit && !form.get(0).onsubmit()) return null;

		// get values
		var values = form.serializeArray();

		for (var i = 0; i < values.length; i++) {
			var name = values[i].name;

			// multi
			if (name in sendValues) {
				var val = sendValues[name];

				if (!(val instanceof Array)) {
					val = [val];
				}

				val.push(values[i].value);
				sendValues[name] = val;
			} else {
				sendValues[name] = values[i].value;
			}
		}

		// send ajax request
		var ajaxOptions = {
			url: form.attr ("action"),
			data: sendValues,
			type: form.attr ("method") || "get"
		};

		if ( errorCallback ) {
			ajaxOptions.error = errorCallback;
		}
		
		if ( successCallback ) {
			ajaxOptions.success = successCallback;
		}

		return jQuery.ajax(ajaxOptions);
	}
});

// dnd options
var dnd = {
	table: null,
	dragTBody : null,
	dragId : 0,
	dragPos : 0,
	dropPos : 0,
	backup : null,
	options : {
		onDragClass: "trDrag",
		onDragStart: function (table, row) {
			dnd.table = table;
			dnd.backup = table.parentNode.innerHTML;
			for ( var t = 0; t<table.tBodies.length; t++ ) {
				var tBody = table.tBodies[t];
				var count = 0;
				for (var i=0; i<tBody.rows.length; i++) {
					var rows = tBody.rows[i];
					if ( $(rows).hasClass ( 'nodrag' ) || $(rows).hasClass ( 'nodrop' ) )
						continue;
					if (rows == row) {
	                	dnd.dragPos = count;
	                	dnd.dragTBody = tBody;
	                	return;
	                }
	                count++;
	            }
			}
		},
		onDrop: function (table, row) {
			var rows = dnd.dragTBody.rows;
			var count = 0;
			for (var i=0; i<rows.length; i++) {
				if ( $(rows[i]).hasClass ( 'nodrag' ) || $(rows[i]).hasClass ( 'nodrop' ) )
					continue;
                if (rows[i] == row) {
                	dnd.dropPos = count;
                	break;
                }
                count++;
            }
            if (dnd.dragPos == dnd.dropPos) return false;
            dnd.dragId = row.id;
            
            jsonData = {};
            jsonData['record'] = row.id;
            jsonData['position'] = dnd.dropPos;
            
            $.ajax({
				type: "GET",
				url: $('#dndTable table').attr ('url'),
				data: {data : $.toJSON(jsonData)},
				error: function (message) {
					alert(message);
					$('#dndTable').html ( dnd.backup );
					$('#dndTable table').tableDnD ( dnd.options );
				},
				success: function () {
					// restore stripes in the table
					for ( var t = 0; t < dnd.table.tBodies.length; t++ ) {
						var tBody = dnd.table.tBodies[t];
						for (var i=0; i<tBody.rows.length; i++) {
							var rows = tBody.rows[i];
							if ( $(rows).hasClass ( 'nodrag' ) || $(rows).hasClass ( 'nodrop' ) )
								continue;
							if (i % 2) {
			                	$(rows).removeClass ('backing');
			                } else {
			                	$(rows).addClass ('backing');
			                }
			            }
					}
					// swap dnd elements in the tree (if the tree exists)
					var dropId = 0;
					var parentId = $('ul#' + dnd.dragId).parent ().attr ('id');
					if (parentId) {
						$('ul.' + parentId).each (function (i) {
							if (i == dnd.dropPos) dropId = $(this).attr ('id');
						} );
						$('ul#' + dnd.dragId).swap ($('ul#' + dropId));
						initTree ();
						initTreeItems ();
					}
					initCheckedItems ();
				}
			} );
		}
	}
};

$(document).ready ( function ()
{
	// gettext
	var gt = new Gettext ( { 'domain' : 'messages' } );
	
	// error messages
	if($('#errorMsg').length) {
		
		tb_show ( gt.gettext ( "System message" ), "#TB_inline?inlineId=errorMsg&amp;height=200&amp;width=350", false, true );
	}
	
	// splitter
	if($('#MySplitter').length) 
	{
		var splitterHeight = document.body.clientHeight - 280 - 24;
		$('#MySplitter').css ('height', splitterHeight);
		
		// Vertical splitter.
		$('#MySplitter').splitter ({
			splitVertical: true,
			outline: true,
			resizeToWidth: true,
			cookie: "vsplitter",
			accessKey: "I"
		});
		// Horizontal splitter, nested in the right pane of the vertical splitter. 
		if ($('#TopPane').length) {
			
			$("#RightPane").splitter({
				splitHorizontal: true,
				sizeBottom: true, 
				accessKey: "H"
			}); 
		}
	}
	
	// drag & drop
	if ($('#dndTable table').length)
		$('#dndTable table').tableDnD ( dnd.options );
	
	// tree items
	if ($('#itemTree').length) 
	{
    	initTree ();
    	if($('#itemTree.docs').length) {
    		initDocs ();
    		initDocsItem ();
    	}
    	if($('#itemTree.pages').length) {
    		initPages ();
    		initPagesItem ();
    	}
    	if($('#itemTree.menu').length) {
    		initMenu;
    		initMenuItem ();
    	}
    	if($('#butExpandAll').length) initExpandCollapseBut ();
	}
	
	// checked items
	if ($('#checkedAll').length)
		initCheckedItems ();
	
	// delete button
	if ($('.delete').length)
    	initDeleteConfirm ();
	
	// restore button
	if ($('.restore').length)
    	initRestoreConfirm ();
	
	// print buttton
	if ($('.print').length)
		initPrint ();
	
	// is allowed
	if ($('.notAllowed').length)
		initIsAllowed ();
	
	// edit buttton
	if ($('.edit').length)
		editItem ();
	
	// add partner
	if ($('#partnerFormSelect').length) {
		
		partnerAdd ();
	
		$('.partnerAdd').click ( function ()
		{
			partnerAdd ();
		} );
		$('.partnerType').change ( function ()
		{
			partnerAdd ();
		} );
	}

	// slide add button
	$(document).bind ( 'click', function ( e )
	{
		if ( e.target.id == 'but1Expand' || e.target.parentNode.id == 'but1Expand' || e.target.parentNode.parentNode.id == 'but1Expand')
			return;
			
		$('#addNewMenu').slideUp ( "fast" );
	} );
	
	$('#but1Expand').click ( function ()
	{
		if ( $('#addNewMenu').is ( ':visible' ) ) {
			$('#addNewMenu').slideUp ( "fast" );
		} else {
			$('#addNewMenu').slideDown ( "fast" );
		}
		
		$('.newMenuItem').click ( function () {
			$('#addNewMenu').slideUp ( "slow" );
		} );
		
		$('#but1Expand').blur ();
	} );
	
	// login form
	if ($('#login').length)
		$('.loginBox #login_login').focus ();
	
	$('#loginForm').submit ( function ()
	{
		login = jQuery.trim ($('#login_login').val (), ' ');
		pswd = jQuery.trim ($('#login_password').val (), ' ');
		
		if (!login || !pswd) {
			$('#butLogin').blur ();
			$('#login_login').focus ();
			return false;
		}
	} );

	// search form
	$('#searchBut').click ( function ()
	{
		$(this).blur ();
		return false;
	} );

	// search form
	/*$('#searchStr').click ( function ()
	{
		if ($('#searchStr').hasClass ('default')) {
			$('#searchStr').removeClass ('default');
			$('#searchStr').val ('');
		}
	} );
	$('#searchStr').blur ( function ()
	{
		search = trim ($('#searchStr').val (), ' ');
		
		if (search == '') {
			$('#searchStr').addClass ('default');
			$('#searchStr').val ('Search');
		}
	} );
	$('#searchForm').submit ( function ()
	{
		search = trim ($('#searchStr').val (), ' ');
		hasClassDefault = ($('#searchStr').hasClass ('default')) ? 1 : 0;
		$('#searchBut').blur ();
		
		if (search == '' || hasClassDefault ) return false;
	} );*/

} );


/* thickbox callback functions */

function dialogCloseConfirm ()
{
	if ( $('#TB_iframeContent').contents().find('#success').length ) {
		window.parent.location.replace ( location.href.split ( '#' ).join ( '' ) );
	}
	return true;
}

function dialogMoveConfirm ()
{
	//window.parent.location.replace('/vfs/docs/get');
	return true;
}

function setSuccess ()
{
	$("#TB_overlay").removeData ( 'success' );
	$("#TB_overlay").data ( 'success', 1 );
}

/* not allowed action */

function initIsAllowed ()
{
	$('.notAllowed'). click ( function () {
		
		$(this).blur ();
		alert ( gt.gettext ( "You are not allowed to process this action." ) );
		return false;
	} );
}

/* checked/unchecked all items */

function initCheckedItems ()
{
	$('#checkedAll').click ( function ()
	{
		if ( $('#checkedAll').attr('checked') ) {
			$('.checkbox').attr({
				checked: true
			});
		} else {
			$('.checkbox').attr({
				checked: false
			});
		}
	} );
}

/* initialise DnD */

function initDnD ()
{
    $('#dndTable table').tableDnD ( dnd.options );
}

// Tree ***********************************************************************

/* init tree items */

function initTree ()
{
	$('.status').click ( function ()
	{
		if ($(this).hasClass ('expand'))
		{
			id = $(this).parent ().parent ().attr ('id');
			if (jQuery.trim ($(this).children ('.button').text())) {
				$(this).children ('.button').text ('+');
				$('.itemTree ul.' + id).css ( { 'display' : 'none' } );
				$(this).addClass ('colapse').removeClass ('expand');
			}
			else {
				$(this).removeClass ('expand');
			}
		}
		else if ($(this).hasClass ('colapse'))
		{
			id = $(this).parent ().parent ().attr ('id');
			if (jQuery.trim ($(this).children ('.button').text())) {
				$(this).children ('.button').text ('-');
				$('.itemTree ul.' + id).css ( { 'display' : 'block' } );
				$(this).addClass ('expand').removeClass ('colapse');
			}
		}
		$(this).blur ();
	} );
}

/* expandAll / collapseAll */

function initExpandCollapseBut ()
{
	$('#butExpandAll').click ( function ()
	{
		expandAll ();
	} );

	$('#butCollapseAll').click ( function ()
	{
		collapseAll ();
	} );
}

function expandAll ()
{
	$('.status').each ( function (i)
	{
		if ($(this).hasClass ('colapse'))
		{
			id = $(this).parent ().parent ().attr ('id');
			if (jQuery.trim ($(this).children ('.button').text())) {
				$(this).children ('.button').text ('-');
				$('.itemTree ul.' + id).css ( { 'display' : 'block' } );
				$(this).addClass ('expand').removeClass ('colapse');
			}
		}
	} );
	$('#butExpandAll').blur ();
}

function collapseAll ()
{
	$('.status').each ( function ( i )
	{
		if ($('.status').hasClass ('expand'))
		{
			id = $(this).parent ().parent ().attr ('id');
			if (jQuery.trim ($(this).children ('.button').text())) {
				$(this).children ('.button').text ('+');
				$('.itemTree ul.' + id).css ( { 'display' : 'none' } );
				$(this).addClass ('colapse').removeClass ('expand');
			}
			else {
				$(this).removeClass ('expand');
			}
		}
	} );
	$('#butCollapseAll').blur ();
}

// Menu items *****************************************************************

function initMenu ()
{
	if ($('.notAllowed').length) initIsAllowed ();
	if ($('#checkedAll').length) initCheckedItems ();
	if ($('.move').length) initDeleteConfirm ();
	if ($('.copy').length) initRestoreConfirm ();
		
	if ($('#dndTable table').length) initDnD ();
	if ($('a.reload').length) tb_init('a.reload');
}

function initMenuItem ()
{
	
	$('.menu .itemLink'). click ( function () 
	{
		if ($(this). children ('span:first').hasClass ('current'))
		{
			$(this).blur ();
			return false;
		}
		else {
			var e = $(this);
			var path = e.attr ('href');
			var itemName = e.attr ('item');
			
			getMenuContent ( path, itemName, e );
			return false;
		}
	} );
}

function getMenuContent ( path, itemName, e )
{
	$('#RightPane').css ("cursor", "wait");
	$('#itemTree').css ("cursor", "wait");
	$('#itemTree a').css ("cursor", "wait");
	if (e != null) e.css ("cursor", "wait");
	
	$.ajax ( {
		type: "GET",
		url: path,
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			var response = XMLHttpRequest.responseText;
			var code = $.evalJSON(response).code;
			var message = $.evalJSON(response).message;
			$('#RightPane').css ("cursor", "default");
			$('#itemTree').css ("cursor", "default");
			$('#itemTree a').css ("cursor", "pointer");
			alert( message )
		},
		success: function ( data ) {
			if (data) {
				if (e != null) {
					$('#itemTree .current').removeClass ('current');
					e.children ('span').addClass ('current');
					e.blur ();
				}
				if (itemName != null) $('.heading').text ( itemName );
				$('#RightPane .box-in').html ( data );
				$('#RightPane').css ("cursor", "default");
				$('#itemTree').css ("cursor", "default");
				$('#itemTree a').css ("cursor", "pointer");
				
				initMenu ();
			}
		}
	} );
}

// Control bar ****************************************************************

/* delete confirm */

function initDeleteConfirm ()
{
	$('.delete').click ( function ()
	{
		if ($(this).attr ('id') == "deleteButton") {
			
			$(this).blur ();
			
			if ($('.checkbox:checked').length) {
				
				if (window.confirm ( gt.gettext ( "Do you really want to proceed with the delete action?" ) )) {
					deleteSelected ();
				}
			} else {
				alert('No items selected.');
			}
			return false;
		} else {
			return window.confirm ( gt.gettext ( "Do you really want to proceed with the delete action?" ) );
		}
	} );
}

/* delete items */

function deleteSelected () 
{
	var path = $('#deleteButton').attr('href');
	var replaceUrl = $('#deleteButton').attr('action');
	var jsonData = {};
	$('.checkbox').each (function ( i ) {
		if (this.checked) {
			jsonData[i] = {};
			jsonData[i]['id'] = this.value;
		}
	} );
	
	$.ajax ( {
		type: "GET",
		url: path,
		data: {data : $.toJSON(jsonData)},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			var response = XMLHttpRequest.responseText;
			var code = $.evalJSON(response).code;
			var message = $.evalJSON(response).message;
			alert( message );
		},
		success: function () { location.replace( replaceUrl ); }
	} );
	return false;
}

/* restore confirm */

function initRestoreConfirm ()
{
	$('.restore').click ( function ()
	{
		if ($(this).attr ('id') == "restoreButton") {
			
			$(this).blur ();
			
			if ($('.checkbox:checked').length) {
				
				if (window.confirm ( gt.gettext ( "Do you really want to proceed with the restore action?" ) )) {
					restoreSelected ();
				}
			} else {
				alert('No items selected.');
			}
			return false;
		} else {
			return window.confirm ( gt.gettext ( "Do you really want to proceed with the restore action?" ) );
		}
	} );
}

/* restore items */

function restoreSelected () 
{
	var path = $('#restoreButton').attr('href');
	var replaceUrl = $('#restoreButton').attr('action');
	var jsonData = {};
	$('.checkbox').each (function ( i ) {
		if (this.checked) {
			jsonData[i] = {};
			jsonData[i]['id'] = this.value;
		}
	} );
	
	$.ajax({
		type: "GET",
		url: path,
		data: {data : $.toJSON(jsonData)},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			var response = XMLHttpRequest.responseText;
			var code = $.evalJSON(response).code;
			var message = $.evalJSON(response).message;
			alert( message );
		},
		success: function () { location.replace( replaceUrl ); }
	} );
}

/* print */

function initPrint ()
{
	$('.print').click ( function ()
	{
		window.print();
		return false;
	} );
}

// ****************************************************************************

// edit form
function editItem ()
{
	$('.edit').click ( function ()
	{
		var personal = $(this).attr ( 'personal' );
		
		$(this).blur ();
		$('#partnerFormSelect input').each ( function (i) {
			
			if ($(this).val () == personal)
				$(this).attr ( 'checked', 'checked' );
			else
				$(this).attr ( 'checked', '' );
			$(this).attr ( 'disabled', 'disabled' );
		} );
		
		$.ajax ( {
			type: "GET",
			url: $(this).attr ( 'href' ),
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				var response = XMLHttpRequest.responseText;
				var code = $.evalJSON(response).code;
				var message = $.evalJSON(response).message;
				alert( message )
			},
			success: function ( data ) {
				
				if (data) {
					$('#partnerForm').html ( data );
					formSubmitIni ();
				}
			}
		} );
		return false;
	} );
}

// partner add form
function partnerAdd ()
{
	var personal = $('#partnerFormSelect input:radio[name=personal]:checked').val ();
	
	$('#partnerFormSelect input').attr ( 'disabled', '' );
	$('.partnerAdd').blur ();
	$('.partnerType').blur ();
	
	$.ajax ( {
		type: "GET",
		url: "/crm/partners/add/personal/" + personal,
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			var response = XMLHttpRequest.responseText;
			var code = $.evalJSON(response).code;
			var message = $.evalJSON(response).message;
			alert( message )
		},
		success: function ( data ) {
			if (data) {
				$('#partnerForm').html ( data );
				formSubmitIni ();
			}
		}
	} );
}

// ajaxSubmit functions
function formSubmitIni ()
{
	$('form.default').submit ( function ()
	{
		$(this).ajaxSubmit ( submitErrorCallback, submitSuccessCallback );
		return false;
	} );
}
function submitErrorCallback ( XMLHttpRequest, textStatus, errorThrown )
{
	var response = XMLHttpRequest.responseText;
	var code = $.evalJSON(response).code;
	var message = $.evalJSON(response).message;
	alert( message );
}
function submitSuccessCallback ( msg )
{
	var gt = new Gettext ( { 'domain' : 'messages' } );
	
	if ( msg ) {
		$('#main').append ( '<div id="errorMsg"></div>' );
		$('#errorMsg').css ( {'display' : 'none'} ).html ( msg );
		tb_show ( gt.gettext ( "System message" ), "#TB_inline?inlineId=errorMsg&amp;height=200&amp;width=350", false, true );
		$('#errorMsg').remove ();
	} else
		location.replace ( $('form.default').attr ( 'param' ) );
}
