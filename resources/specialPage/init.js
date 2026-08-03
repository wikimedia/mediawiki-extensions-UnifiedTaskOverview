require( './TileListWidget.js' );

function getList() {
	const dfd = $.Deferred();
	$.ajax( {
		url: mw.util.wikiScript( 'rest' ) + '/unifiedtaskoverview/list',
		contentType: 'application/json',
		dataType: 'json'
	} ).done( ( response ) => {
		dfd.resolve( response );
	} ).fail( ( jqXHR, type, status ) => {
		if ( type === 'error' ) {
			dfd.reject( {
				error: jqXHR.responseJSON || jqXHR.responseText
			} );
		}
		dfd.reject( { type: type, status: status } );
	} );
	return dfd.promise();
}

function sortItems( data ) {
	return data.sort( ( a, b ) => a.sortkey - b.sortkey );
}

function loadRLModules( data ) {
	const modules = [];
	data.forEach( ( item ) => {
		item.RLmodules.forEach( ( module ) => {
			// eslint-disable-next-line es-x/no-array-prototype-includes
			if ( !modules.includes( module ) ) {
				modules.push( module );
			}
		} );
	} );
	if ( modules.length > 0 ) {
		mw.loader.using( modules );
	}
}

function render() {
	getList().done( ( response ) => {
		const items = response.length > 0 ? sortItems( response ) : [];
		if ( items.length > 0 ) {
			loadRLModules( items );
		}

		const tileList = new ext.unifiedTaskOverview.ui.TileListWidget( { items: items } );

		const filterData = tileList.buildFilterData();
		const filterWidget = new OOJSPlus.ui.widget.FilterWidget( {} );
		filterWidget.loadData( filterData, 'all' );
		filterWidget.connect( filterWidget, {
			selectItem: ( key ) => {
				tileList.applyFilter( key );
			}
		} );
		if ( filterData.length === 0 || filterData[ 1 ].items.length <= 1 ) {
			filterWidget.toggle( false );
		}

		$( '#oojsplus-skeleton-cnt' ).empty(); // eslint-disable-line no-jquery/no-global-selector
		$( '#unifiedTaskOverview-tiles' ) // eslint-disable-line no-jquery/no-global-selector
			.append( filterWidget.$element )
			.append( tileList.$element );
	} );
}

render();
