window.ext = window.ext || {};
ext.unifiedTaskOverview = ext.unifiedTaskOverview || {};
ext.unifiedTaskOverview.ui = ext.unifiedTaskOverview.ui || {};

ext.unifiedTaskOverview.ui.TileWidget = function ( cfg ) {
	ext.unifiedTaskOverview.ui.TileWidget.parent.call( this, cfg );

	this.$icon = $( '<div>' ).addClass( 'icn-cnt task-' + cfg.type ); // eslint-disable-line mediawiki/class-doc
	this.$content = $( '<div>' ).addClass( 'tile-content' );
	this.$header = $( '<div>' ).addClass( 'tile-header' );
	const $header = $( '<div>' ).addClass( 'task-header' ).html( cfg.header || '' );
	const $subheader = $( '<div>' ).addClass( 'task-subheader' ).html( cfg.subheader || '' );
	this.$body = $( '<div>' ).addClass( 'task-body' ).html( cfg.body || '' );
	this.$link = $( '<a>' ).attr( 'href', cfg.url )
		.append(
			this.$content.append(
				this.$header.append(
					this.$icon,
					$( '<div>' ).addClass( 'tile-header-content' ).append( $header, $subheader )
				),
				$( '<div>' ).addClass( 'act-container' ).append( this.$body )
			)
		);

	this.taskData = cfg;
	this.$element.addClass( 'uto-tile' ).append( this.$link );

	mw.hook( 'unifiedtaskoverview.tile.init' ).fire( this, this.taskData );
};

OO.inheritClass( ext.unifiedTaskOverview.ui.TileWidget, OO.ui.Widget );
