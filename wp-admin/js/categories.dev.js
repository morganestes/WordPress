jQuery(document).ready(function($) {
	var options = false, addAfter, addAfter2, delAfter;
	if ( document.forms['addcat'].category_parent )
		options = document.forms['addcat'].category_parent.options;

	addAfter = function( r, settings ) {
		var name, id;

		name = $("<span>" + $('name', r).text() + "</span>").html();
		id = $('cat', r).attr('id');
		options[options.length] = new Option(name, id);

		addAfter2( r, settings );
	}

	addAfter2 = function( x, r ) {
		var t = $(r.parsed.responses[0].data);
		if ( t.length == 1 )
			inlineEditTax.addEvents($(t.id));
	}

	delAfter = function( r, settings ) {
		var id = $('cat', r).attr('id'), o;
		for ( o = 0; o < options.length; o++ )
			if ( id == options[o].value )
				options[o] = null;
	}

	if ( options )
		$('#the-list').wpList( { addAfter: addAfter, delAfter: delAfter } );
	else
		$('#the-list').wpList({ addAfter: addAfter2 });

});
