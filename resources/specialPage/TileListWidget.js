window.ext = window.ext || {};
ext.unifiedTaskOverview = ext.unifiedTaskOverview || {};
ext.unifiedTaskOverview.ui = ext.unifiedTaskOverview.ui || {};

require( './TileWidget.js' );

ext.unifiedTaskOverview.ui.TileListWidget = function ( cfg ) {
	ext.unifiedTaskOverview.ui.TileListWidget.parent.call( this, cfg );

	this.activeFilter = 'all';
	this.tileWidgets = [];

	this.searchInput = new OO.ui.SearchInputWidget( {
		placeholder: mw.message( 'unifiedtaskoverview-search-placeholder' ).plain()
	} );
	this.searchInput.connect( this, { change: 'onSearch' } );

	this.$tileList = $( '<ul>' ).addClass( 'ul-tiles' );
	this.$element.addClass( 'task-overview' )
		.append( this.searchInput.$element, this.$tileList );

	const items = cfg.items || [];
	if ( items.length === 0 ) {
		const $icon = $( '<div>' ).addClass( 'notasks-icon' );
		const $msg = $( '<p>' ).text( mw.message( 'unifiedtaskoverview-label-no-task' ).plain() );
		this.$tileList.append( $( '<div>' ).addClass( 'no-tasks' ).append( $msg, $icon ) );
	} else {
		items.forEach( ( item ) => {
			const tile = new ext.unifiedTaskOverview.ui.TileWidget( item );
			this.tileWidgets.push( tile );
			this.$tileList.append( tile.$element );
		} );
	}
};

OO.inheritClass( ext.unifiedTaskOverview.ui.TileListWidget, OO.ui.Widget );

ext.unifiedTaskOverview.ui.TileListWidget.prototype.applyFilter = function ( filterKey ) {
	this.activeFilter = filterKey;
	this.tileWidgets.forEach( ( tile ) => {
		tile.toggle( filterKey === 'all' || tile.taskData.wiki_id === filterKey );
	} );
};

ext.unifiedTaskOverview.ui.TileListWidget.prototype.onSearch = function ( value ) {
	const search = value.toLowerCase();
	this.tileWidgets.forEach( ( tile ) => {
		if ( search === '' ) {
			tile.toggle( this.activeFilter === 'all' || tile.taskData.wiki_id === this.activeFilter );
			return;
		}
		const text = [
			tile.taskData.type,
			tile.taskData.header,
			tile.taskData.subheader,
			tile.taskData.body
		].join( ' ' ).toLowerCase();
		// eslint-disable-next-line es-x/no-array-prototype-includes
		tile.toggle( text.includes( search ) );
	} );
};

ext.unifiedTaskOverview.ui.TileListWidget.prototype.buildFilterData = function () {
	const wikiCount = {};
	const wikiSource = {};
	this.tileWidgets.forEach( ( tile ) => {
		if ( !tile.taskData.source ) {
			return;
		}
		const wikiId = tile.taskData.wiki_id;
		wikiCount[ wikiId ] = ( wikiCount[ wikiId ] || 0 ) + 1;
		wikiSource[ wikiId ] = tile.taskData.source;
	} );
	if ( wikiCount.length === 0 ) {
		return [];
	}

	const filterData = [
		{ items: [
			{
				key: 'all',
				count: this.tileWidgets.length,
				label: mw.message( 'unifiedtaskoverview-filter-all' ).plain()
			}
		] }
	];
	const items = [];
	for ( const wikiId in wikiCount ) {
		items.push( {
			key: wikiId,
			label: wikiSource[ wikiId ].display_text,
			count: wikiCount[ wikiId ],
			attr: wikiSource[ wikiId ]
		} );
	}

	filterData.push( {
		label: mw.message( 'unifiedtaskoverview-filter-wikis-section-label' ).plain(),
		items: ext.unifiedTaskOverview.ui.TileListWidget.static.sortByInstance( items )
	} );
	return filterData;
};

/**
 * Main wiki first, every other instance by its display name.
 *
 * @param {Array} items Filter items, each holding the wiki info of its instance in "attr"
 * @return {Array} The same items, sorted
 */
ext.unifiedTaskOverview.ui.TileListWidget.static.sortByInstance = function ( items ) {
	return items.sort( ( a, b ) => {
		const aIsRoot = !!( a.attr && a.attr.is_root );
		const bIsRoot = !!( b.attr && b.attr.is_root );
		if ( aIsRoot !== bIsRoot ) {
			return aIsRoot ? -1 : 1;
		}

		return ( a.label || '' ).localeCompare( b.label || '', undefined, { numeric: true, sensitivity: 'base' } );
	} );
};
