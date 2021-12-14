/*  */

var ajaxReqCnt = 0;
var sUsername = '';
var id = 0;

$(document).ready ( function ()
{
	// gettext
	var gt = new Gettext ( { 'domain' : 'messages' } );
	
	// tree items
	if($('#itemTree').length) {
		initTree ();
		initMoveItems ();
	}
	
	// hover effect (table.records)
	$('table.records td').hover 
	(
		function ()
		{
			$(this).parent ().addClass ('colHover');
		},
		function ()
		{
			$(this).parent ().removeClass ('colHover');
		}
	);
	
	// click on the table.records row
	$('table.records td.link').click ( function ()
	{
		location.replace ( $(this).parent ().attr ('url') );
	} );
	
	// open new vindow for editing
	$('.winOpen').click ( function ()
	{
		var url = $(this).attr ('href');
		var width = $(this).attr ('width');
		var height = $(this).attr ('height');
		var left = (screen.availWidth - width) / 2;
		var top = (screen.availHeight - height) / 2;
		
		window.open ( url, 'child', 'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top );
		
		return false;
	} );
	
	// initialise browser
	if($('#browseServer').length)
		initBrowseBut ();
	
	// initialise datepicker
	if($('.datepicker').length)
		$('.datepicker').datepick ();
	
	// hide isRequired element
	if($('#formList').length)
		$('.isRequired').css ({ 'display' : 'none' });
	
	// tabs
	if ($('.menuTabs').length) {
		
		$('.menuTabs td.tab a'). click ( function () {
			
			$('.menuTabs td.current').removeClass ('current');
			$('td .tabLeftCornerCurrent').removeClass ('tabLeftCornerCurrent').addClass ('tabLeftCorner');
			$('td .tabRightCornerCurrent').removeClass ('tabRightCornerCurrent').addClass ('tabRightCorner');
			$('td .tabTextCurrent').removeClass ('tabTextCurrent').addClass ('tabText');
			$(this). parent ().parent ().addClass ('current');
			$('td.current .tabLeftCorner').removeClass ('tabLeftCorner').addClass ('tabLeftCornerCurrent');
			$('td.current .tabRightCorner').removeClass ('tabRightCorner').addClass ('tabRightCornerCurrent');
			$('td.current .tabText').removeClass ('tabText').addClass ('tabTextCurrent');
			
			$('.menuTabs td.tab').each (function (i) {
				if ($(this).hasClass ('current')) {
					$('#table' + i).css ('display', 'table');
				} else {
					$('#table' + i).css ('display', 'none');
				}
			});
			$(this).blur ();
			return false;
		} );
		
		// prace s formularem pro editaci polozky menu
		$('select#menu-type'). change ( function () {
			
			itemType = $('select#menu-type').parent ().attr ('itemType');
			
			if ($('select#menu-type').val () == itemType) {
				for (i=0; i<$('.menuTabs td.tab').length; i++) {
					$('#table' + i).css ('display', 'none');
				}
				$('#table0').css ('display', 'table');
				$('.menuTabs td.tab').css ('display', 'block');
			}
			else {
				for (i=0; i<$('.menuTabs td.tab').length; i++) {
					$('#table' + i).css ('display', 'none');
				}
				$('#table1').css ('display', 'table');
				$('.menuTabs td.tab').css ('display', 'none');
				$('.menuTabs td.tab').removeClass ('current');
				$('.menuTabs td.tab:first').addClass ('current').css ('display', 'block');
				$('.menuTabs td .tabLeftCornerCurrent').removeClass ('tabLeftCornerCurrent').addClass ('tabLeftCorner');
				$('.menuTabs td .tabRightCornerCurrent').removeClass ('tabRightCornerCurrent').addClass ('tabRightCorner');
				$('.menuTabs td .tabTextCurrent').removeClass ('tabTextCurrent').addClass ('tabText');
				$('.menuTabs td.current .tabLeftCorner').removeClass ('tabLeftCorner').addClass ('tabLeftCornerCurrent');
				$('.menuTabs td.current .tabRightCorner').removeClass ('tabRightCorner').addClass ('tabRightCornerCurrent');
				$('.menuTabs td.current .tabText').removeClass ('tabText').addClass ('tabTextCurrent');
			}
		} );
		
		// prepinani () v zalozce routovani
		$('input.formRoute2'). change ( function () {
			
			if ($('input.formRoute:checked').val ()) {
				
				alert($('input.formRoute:checked').val ());
				
				$('#newRule').css ('display', 'table-cell');
				$('#selectRule').css ('display', 'none');
			}
			else {
				
				alert($('input.formRoute:checked').val ())
				
				$('#selectRule').css ('display', 'table-cell');
				$('#newRule').css ('display', 'none');
			}
		} );
	}
	
	if ($('#butTarget_business_partners_select').length) {
		
		$('#butTarget_business_partners_select').click ( function ()
		{
			if ($('#orderer').val ()) {
				$('#butTarget_business_partners_select').blur ();
				return true;
			}
			else {
				alert ( gt.gettext ( "Enter at least the first few characters of the name of the client." ) );
				$('#orderer').focus ();
				return false;
			}
			$('#butTarget_business_partners_select').blur ();
		} );
	}
	
	// nastavi parametr pro reload rodicovskeho okna pri uspesne zmene v dialogovem okne
	if ($('#success').length) {
		window.parent.setSuccess ();
	}
	
	// my account
	if($('.username').length) {
		id = ($('#id_user').length) ? $('#id_user').val () : 0;
		checkUsername($('.username input').val ());
		
		$('.username input').change ( function () { checkUsername($('.username input').val ()) });
		$('.username input').keyup ( function () { checkUsername($('.username input').val ()) });
	}
	
} );

/* initialise tree items */

function initTree ()
{
	// expand / colapse item tree
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

/* move items */

function initMoveItems ()
{
	$('li.targetFolder a.itemLink').click ( function ()
	{
		if ($(this).children ('span').hasClass( 'current' )) return false;
		
		var path = $(this).attr ('href');
		var jsonData = {};
		
		$('body').css ("cursor", "wait");
		$('li.targetFolder a.itemLink').css ("cursor", "wait");
		
		window.parent.$('.checkbox').each (function ( i ) {
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
				
				window.parent.location.replace('/vfs/docs/get');
			},
			success: function () {
				
				window.parent.initDocs ();
				window.parent.location.replace('/vfs/docs/get');
			}
		} );
		return false;
	} );
}

/* browse server */

function initBrowseBut ()
{
	$('#browseServer').click ( function ()
	{
		$(this).blur ();
		
		type = ($('#browseServer.image').length) ? 'image' : 'link';
		
		openFileBrowser( '/vfs/browser/index/type/' + type, 750, 600 );
		
	} );
}

function openFileBrowser( url, width, height )
{
	var iLeft = ( window.screen.width  - width ) / 2 ;
	var iTop  = ( window.screen.height - height ) / 2 ;

	var sOptions = "toolbar=no,status=no,resizable=yes,dependent=yes,scrollbars=yes" ;
	sOptions += ",width=" + width ;
	sOptions += ",height=" + height ;
	sOptions += ",left=" + iLeft ;
	sOptions += ",top=" + iTop ;

	window.open( url, 'dialogBrowseWindow', sOptions ) ;
}

function SetUrl( url )
{
	target = ($('#browseServer.image').length) ? '#tmbn' : '#url';
	
	$(target).val ( url );
}

/* check username */
/* change image, status: 0 = error, 1 = ok, 2 = confirm */

function setUsernameStatus(status)
{
	var img_src = '/public/img/cms/ico/ico_username_';
	var img_stat = '';
	switch (status) {
		case 1:
			img_stat = 'ok.png';
			break;
		case 2:
			img_stat = 'check.png';
			break;
		default:
			img_stat = 'bad.png';
	}
	$('.username').css('background', 'url("' + img_src + img_stat + '") right center no-repeat');
}

/* on/off submit button */

function setSubmit(status)
{
	if (status) {
		$('#butSubmit').removeAttr("disabled");
	} else {
		$('#butSubmit').attr("disabled","true");
	}
}

/* confirm username (AJAX) */

function checkUsername(username)
{
	if (username == '') {
		ajaxReqCnt = 0;
		sUsername = '';
		setSubmit(false);
		return setUsernameStatus(0);
	}
	if (username == sUsername) {
		return;
	}
	ajaxReqCnt++;
	sUsername = username;
	setUsernameStatus(2);
	$.ajax({
		type: "POST",
		url: "/cms/account/check/username/" + username + "/reqcnt/" + ajaxReqCnt + "/id/" + id,
		success: function(json) {
			var result = $.evalJSON(json).result;
			var reqcnt = $.evalJSON(json).reqcnt;
			if (reqcnt == ajaxReqCnt) {
				if (result) {
					if ($('.username input').val() != '') {
						setUsernameStatus(1);
						setSubmit(true);
					}
				} else {
					setUsernameStatus(0);
					setSubmit(false);
				}
			}
		}
	});
}
