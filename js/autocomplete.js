//autocomplete de correo
$('#correo').autocomplete({
		source: function( request, response ) {
		$.ajax({
			url : 'autocomplete.php',
			dataType: "json",
			data: {
			name_startsWith: request.term,
			type: 'correo',
			row_num : 1
		},
			success: function( data ) {
				response( $.map( data, function( item ) {
					var code = item.split("|");
					return {
						label: code[0],
						value: code[0],
						data : item
					}
				}));
			}
		});
	},
		autoFocus: true,	      	
		minLength: 0,
		select: function( event, ui ) {
			var names = ui.item.data.split("|");
			console.log(names[1], names[2]);						
			$('#correo').val(names[1]);
		}		      	
});