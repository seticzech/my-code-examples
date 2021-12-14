/* aPortal crm */

$(document).ready ( function ()
{
	// add new partner
	$('.physical_entity').change ( function ()
	{
		if ($('.physical_entity:checked').val () == "1") {
			
			$('#physical_entity').css ( { display : 'block' } );
			$('#corporate_entity').css ( { display : 'none' } );
		}
		else {
			$('#physical_entity').css ( { display : 'none' } );
			$('#corporate_entity').css ( { display : 'block' } );
		}
	} );

} );
