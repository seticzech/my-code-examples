/*  */

$(document).ready ( function ()
{
	// display mode
	if($('.displayMode').length)
		initDisplayMode ();

	// restore thumbnails
	if($('.recreateThumbnails').length)
		initRecreateThumbnails ();

	// move items
	if($('.move').length)
		initMoveConfirm ();

	// item history
	if($('.revision').length)
		initItemHistory ();

	// revert to the revision
	if($('.revert').length)
		initRevertConfirm ();

	// back button
	if($('.back').length)
		initBack ();

	// initialise upload
	if($('#uploadFiles').length)
		initUpload ();
	
} );


function initDocs ()
{
	if ($('.notAllowed').length) initIsAllowed ();
	if ($('#checkedAll').length) initCheckedItems ();
	if ($('.delete').length) initDeleteConfirm ();
	if ($('.restore').length) initRestoreConfirm ();
	
	if ($('.folder').length) initDocsItem ();
	if ($('.displayMode').length) initDisplayMode ();
	if ($('.recreateThumbnails').length) initRecreateThumbnails ();
	if ($('.move').length) initMoveConfirm ();
	if ($('.revert').length) initRevertConfirm ();
	if ($('.revision').length) initItemHistory ();
	if ($('.back').length) initBack ();
	if ($('#dndTable table').length) initDnD ();
	if ($('a.reload').length) tb_init('a.reload');
	if ($('#uploadFiles').length) initUpload ();
}

/* view folder content */

function initDocsItem ()
{
	$('.folder').click ( function () {
	
		var path = $(this).attr ('href');
		var itemName = $(this).attr ('item');
		var id = $(this).parent ().attr ('id');
		var e = $('ul#' + id + ' .itemLink');
		
		getFolderContent ( path, itemName, e );
		return false;
	} );
}

function getFolderContent ( path, itemName, e )
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
				$('#RightPane .cont').html ( data );
				$('#RightPane').css ("cursor", "default");
				$('#itemTree').css ("cursor", "default");
				$('#itemTree a').css ("cursor", "pointer");
				
				initDocs ();
			}
		}
	} );
}

// Control bar ****************************************************************

/* move confirm */

function initMoveConfirm ()
{
	$('.move').click ( function ()
	{
		if ($(this).attr ('id') == "moveButton") {
			
			$(this).blur ();
			
			if ($('.checkbox:checked').length) {
				
				tb_show ( "Move items", "/vfs/docs/list?TB_iframe=true&height=450&amp;width=450", false, true, dialogMoveConfirm );
			} else {
				alert('No items selected.');
			}
		}
		return false;
	} );
}

/* change display mode */

function initDisplayMode ()
{
	$('.displayMode').change ( function () {
	
		var path = $('.displayMode option:selected').val ();
		$(this).blur ();
		
		getFolderContent ( path );
	} );
}

/* recreate thumbnails */

function initRecreateThumbnails ()
{
	$('.recreateThumbnails').click ( function () {
	
		var path = $(this).attr ('href');
		$(this).blur ();
		
		getFolderContent ( path );
		return false;
	} );
}

/* view item history (list of revisions) */

function initItemHistory ()
{
	$('.revision').click ( function ()
	{
		$('#RightPane').css ("cursor", "wait");
		
		$.ajax ( {
			type: "GET",
			url: $(this).attr ('href'),
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				var response = XMLHttpRequest.responseText;
				var code = $.evalJSON(response).code;
				var message = $.evalJSON(response).message;
				alert( message )
			},
			success: function ( data ) {
				if (data) {
					$('#RightPane .cont').html ( data );
					$('#RightPane').css ("cursor", "default");

					initDocs ();
				}
			}
		} );
		return false;
	} );
}

/* revert confirm */

function initRevertConfirm ()
{
	$('.revert').click ( function ()
	{
		if (window.confirm ( gt.gettext ( "Do you really want to proceed with the revert action?" ) )) {
			
			var id = parseInt($(this).attr ('ajax'));
			
			if ( id ) {
			
				$('#RightPane').css ("cursor", "wait");
				
				$.ajax ( {
					type: "GET",
					url: $(this).attr ('href'),
					error: function (XMLHttpRequest, textStatus, errorThrown) {
						var response = XMLHttpRequest.responseText;
						var code = $.evalJSON(response).code;
						var message = $.evalJSON(response).message;
						alert( message )
					},
					success: function () {
						$.ajax ( {
							type: "GET",
							url: '/vfs/docs/history/id/' + id,
							error: function (XMLHttpRequest, textStatus, errorThrown) {
								var response = XMLHttpRequest.responseText;
								var code = $.evalJSON(response).code;
								var message = $.evalJSON(response).message;
								alert( message )
							},
							success: function ( data ) {
								if (data) {
									$('#RightPane .cont').html ( data );
									$('#RightPane').css ("cursor", "default");

									initDocs ();
								}
							}
						} );
					}
				} );
				return false;
			}
			else {
				location.replace ( $(this).attr ('href') );
				return false;
			}
		}
	} );
}

/* return to the items list */

function initBack ()
{
	$('.back').click ( function ()
	{
		$('#RightPane').css ("cursor", "wait");
		$(this).blur ();
		
		$.ajax ( {
			type: "GET",
			url: $(this).attr ('href'),
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				var response = XMLHttpRequest.responseText;
				var code = $.evalJSON(response).code;
				var message = $.evalJSON(response).message;
				alert( message )
			},
			success: function ( data ) {
				if (data) {
					$('#RightPane .cont').html ( data );
					$('#RightPane').css ("cursor", "default");

					initDocs ();
				}
			}
		} );
		return false;
	} );
}

// SWF Upload *****************************************************************

/* Upload files */

var fileUploadDisplays = {};
var needRefresh = false;

function initUpload ()
{
	var settings = {
		flash_url : "/public/flash/swfupload.swf",
		upload_url: "/vfs/docs/upload/?hash=" + getPHPSESSID(),
		
		button_image_url: "",	// Relative to the Flash file
		button_width: "90",
		button_height: "21",
		button_placeholder_id: "uploadFiles",
		button_text: '<span class="uploadBtnLabel">' + gt.gettext ( "Upload files" ) + '</span>',
		button_text_style: ".uploadBtnLabel { font-family: verdana; font-size: 11; color: #666666; text-align: center; }",
		button_text_left_padding: 0,
		button_text_top_padding: 3,
		button_cursor: SWFUpload.CURSOR.ARROW,
		button_window_mode : SWFUpload.WINDOW_MODE.TRANSPARENT,
		
		file_types: "*.*",
		file_upload_limit: 0,
		
		file_queued_handler : fileQueued,
		//file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		//upload_success_handler : uploadSuccess,
		upload_complete_handler : uploadComplete,
		queue_complete_handler : queueComplete
	};
	
	swfu = new SWFUpload(settings);
	
	$('body').append ( '<div id="uploadDialog" style="display : none"><a href="#" id="uploadCancelBtn">' + gt.gettext ( "Cancel uploads" ) + '</a></div>' );
	$('#uploadCancelBtn').click ( function ()
	{
		if ( window.confirm ( gt.gettext ( "Do you want to cancel the upload?" ) )) {
			
			swfu.cancelQueue ();
			needRefresh = true;
			$('#uploadCancelBtn').remove ();
			$('#TB_ajaxContent').append ( '<p><strong>' + gt.gettext ( "Upload canceled" ) + ' !</strong></p>' );
			return true;
		} else
			return false;
	} );
}

function fileDialogComplete ( numFilesSelected, numFilesQueued )
{
	if ( numFilesSelected > 0 ) {
		needRefresh = false;
		tb_show ( "Uploading files", "#TB_inline?height=400&width=400&inlineId=uploadDialog", false, true, uploadCloseConfirm );
		
		this.startUpload ();
	}
}

function fileQueued ( file )
{
	// metoda se vola PRED zobrazenim thickboxu
	
	var uploadDisplay = $('#uploadDialog').prepend ( '<div id="' + file.id + '" class="uploadFile"><div class="fileStatus"><span class="fileName">' + file.name + '</span> ' + gt.gettext ( "pending" ) + '</div><div class="uploading">' + gt.gettext ( "Uploading" ) + ' ...</div><div class="progressHolder"><div class="progressBar"></div></div></div>' );
	
	fileUploadDisplays[file.id] = $('#' + file.id);
}

function uploadStart ( file )
{
	var display = fileUploadDisplays[file.id];
	
	display.find ( '.fileStatus' ).html ( '<span class="fileName">' + file.name + '</span>' );
	display.find ( '.uploading' ).css ( { display : 'block' } );
	display.find ( '.progressHolder' ).css ( { display : 'block' } );
	display.find ( '.progressBar' ).css ( { width : 0 } );
}

function uploadProgress ( file, bL, bT )
{
	var display = fileUploadDisplays[file.id];
	
	display.find ( '.progressBar' ).css ( { width : display.find ( '.progressHolder' ).width () * bL / bT } );
}

function uploadComplete ( file )
{
	var display = fileUploadDisplays[file.id];
	
	display.find ( '.fileStatus' ).html ( '<span class="fileName">' + file.name + '</span> ' + gt.gettext ( "complete" ) );
	display.find ( '.uploading' ).css ( { display : 'none' } );
	display.find ( '.progressHolder' ).css ( { display : 'none' } );
	
	// this.startUpload ();
}

function uploadError ( file, errorCode, message )
{
	alert ( message );
}

function queueComplete ( numFilesUploaded )
{
	needRefresh = true;
	$('#uploadCancelBtn').remove ();
	$('#TB_ajaxContent').append ( '<p><strong>' + gt.gettext ( "Upload complete" ) + ' !</strong> &nbsp; ' + numFilesUploaded + ' ' + gt.ngettext ( "file uploaded", "files uploaded", numFilesUploaded ) + '.</p>' )
}

function readCookie(name) {
     var nameEQ = name + "=";
     var ca = document.cookie.split(';');
     
     for(var i=0; i < ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0)==' ') c = c.substring(1,c.length);
          if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
     }
     return null;
}

function getPHPSESSID ()
{
     return readCookie('PHPSESSID');
}

// upload callback for thickbox
function uploadCloseConfirm ()
{
	if ( $('#uploadCancelBtn').length ) {
		if ( window.confirm ( gt.gettext ( "Do you want to cancel the upload?" ) ) ) {
			
			swfu.cancelQueue ();
			$('#uploadCancelBtn').remove ();
			$('#TB_ajaxContent').append ( '<p><strong>' + gt.gettext ( "Upload canceled" ) + ' !</strong></p>' );
			location.replace ( location.href.split ( '#' ).join ( '' ) );
			return true;
		} else
			return false;
	}
	
	if ( needRefresh )
		location.replace ( location.href.split ( '#' ).join ( '' ) );
	return true;
}
