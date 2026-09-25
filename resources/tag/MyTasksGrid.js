window.ext = window.ext || {};
ext.unifiedTaskOverview = ext.unifiedTaskOverview || {};
ext.unifiedTaskOverview.ui = ext.unifiedTaskOverview.ui || {};

/**
 * Prefix the tasks of each type are registered with. The list of the REST endpoint
 * holds every kind of task, so each list keeps the ones of its own type only.
 *
 * Kept in step with MyTasks::TYPES.
 *
 * @type {Object}
 */
const TYPE_PREFIXES = {
	task: 'simple-tasks-task',
	workflow: 'workflows-activity-',
	readconfirmation: 'page-read-confirmation'
};

/**
 * List of the open tasks of one type of the current user, shown by the <mytasks> tag.
 *
 * @param {Object} cfg Config, "items" holds the tasks as delivered by the
 *  "unifiedtaskoverview/list" REST endpoint, "type" the type of task to show
 */
ext.unifiedTaskOverview.ui.MyTasksGrid = function ( cfg ) {
	cfg = Object.assign( { padded: true, expanded: false }, cfg || {} );

	ext.unifiedTaskOverview.ui.MyTasksGrid.parent.call( this, cfg );

	this.type = cfg.type || 'task';
	this.items = ( cfg.items || [] ).filter( ( item ) => this.isOfType( item ) );
	this.$element.addClass( 'uto-mytasks-grid' );

	if ( this.items.length === 0 ) {
		this.$element.append(
			$( '<p>' ).addClass( 'uto-mytasks-notice' )
				.text( mw.message( 'unifiedtaskoverview-mytasks-empty' ).text() )
		);
		return;
	}

	this.grid = new OOJSPlus.ui.data.GridWidget( {
		style: 'differentiate-rows',
		pageSize: 10,
		columns: this.makeColumns(),
		data: this.makeRows()
	} );
	this.grid.setColumnsVisibility( this.grid.visibleColumns );
	this.$element.append( this.grid.$element );
};

OO.inheritClass( ext.unifiedTaskOverview.ui.MyTasksGrid, OO.ui.PanelLayout );

/**
 * @return {Object}
 */
ext.unifiedTaskOverview.ui.MyTasksGrid.prototype.makeColumns = function () {
	const columns = {};

	const instances = this.getInstances();
	if ( instances.length > 0 ) {
		columns.instance = {
			headerText: mw.message( 'unifiedtaskoverview-mytasks-column-instance' ).text(),
			type: 'text',
			sortable: true,
			filter: { type: 'list', list: instances },
			valueParser: ( value, row ) => {
				if ( !value ) {
					return '';
				}
				const $badge = this.makeWikiBadge( value, row.source );
				return new OO.ui.HtmlSnippet( $badge[ 0 ].outerHTML );
			}
		};
	}

	columns.namespace = {
		headerText: mw.message( 'unifiedtaskoverview-mytasks-column-namespace' ).text(),
		type: 'text',
		sortable: true,
		hidden: true,
		filter: { type: 'list', list: this.getNamespaces() }
	};
	columns.page = {
		headerText: mw.message( 'unifiedtaskoverview-mytasks-column-page' ).text(),
		type: 'url',
		urlProperty: 'url',
		sortable: true,
		filter: { type: 'text' }
	};
	columns.description = {
		headerText: mw.message( 'unifiedtaskoverview-mytasks-column-description' ).text(),
		type: 'text',
		maxLabelLength: 100
	};

	return columns;
};

/**
 * @return {Array} Rows for the grid, sorted by the sort key of the task type
 */
ext.unifiedTaskOverview.ui.MyTasksGrid.prototype.makeRows = function () {
	const items = this.items.slice().sort( ( a, b ) => a.sortkey - b.sortkey );

	return items.map( ( item ) => {
		const source = item.source || {};
		return {
			page: this.getPageLabel( item ),
			namespace: item.namespace || '',
			url: item.url,
			description: this.makeDescription( item ),
			instance: source.display_text || '',
			source: source
		};
	} );
};

/**
 * Page a task belongs to.
 *
 * Descriptors are headed by the page they refer to, except for tasks of SimpleTasks,
 * which are headed by the task itself - those are named by the page stored with them.
 *
 * @param {Object} item
 * @return {string}
 */
ext.unifiedTaskOverview.ui.MyTasksGrid.prototype.getPageLabel = function ( item ) {
	if ( this.headerIsTask() ) {
		return item.page_name || item.page_title || '';
	}

	return this.toPlainText( item.header );
};

/**
 * Description of a task: the task itself, if the header does not already name it, its
 * subheader ("Read confirmation required", the due date, ...) and the details of the
 * task, if it provides any.
 *
 * @param {Object} item
 * @return {string}
 */
ext.unifiedTaskOverview.ui.MyTasksGrid.prototype.makeDescription = function ( item ) {
	const parts = [];
	if ( this.headerIsTask() ) {
		parts.push( this.toPlainText( item.header ) );
	}
	if ( item.subheader ) {
		parts.push( this.toPlainText( item.subheader ) );
	}
	if ( item.body && !this.headerIsTask() ) {
		parts.push( this.toPlainText( item.body ) );
	}

	return parts.filter( ( part ) => part !== '' ).join( ' · ' );
};

/**
 * @return {boolean} Whether the header of the tasks names the task and not its page
 */
ext.unifiedTaskOverview.ui.MyTasksGrid.prototype.headerIsTask = function () {
	return this.type === 'task';
};

/**
 * @param {Object} item
 * @return {boolean} Whether the task is of the type the list shows
 */
ext.unifiedTaskOverview.ui.MyTasksGrid.prototype.isOfType = function ( item ) {
	const prefix = TYPE_PREFIXES[ this.type ];

	return !!prefix && item.type.indexOf( prefix ) === 0;
};

/**
 * Task descriptors deliver parsed wikitext, while the grid sorts and filters on plain
 * strings.
 *
 * @param {string} html
 * @return {string}
 */
ext.unifiedTaskOverview.ui.MyTasksGrid.prototype.toPlainText = function ( html ) {
	if ( !html ) {
		return '';
	}

	return $( '<div>' ).html( html ).text().replace( /\s+/g, ' ' ).trim();
};

/**
 * Sign of the wiki a task comes from, in the color of that wiki.
 *
 * @param {string} name Display name of the wiki
 * @param {Object} source Wiki info of the task, as delivered by BlueSpiceWikiFarm
 * @return {jQuery}
 */
ext.unifiedTaskOverview.ui.MyTasksGrid.prototype.makeWikiBadge = function ( name, source ) {
	if ( window.ext.bluespiceWikiFarm && ext.bluespiceWikiFarm.util ) {
		const $badge = ext.bluespiceWikiFarm.util.getWikiBadge( source );
		return $badge.css( '--wiki-color', ext.bluespiceWikiFarm.util.getWikiColor( source ) );
	}

	const $fallback = $( '<span>' ).addClass( 'uto-mytasks-instance' ).text( name );
	if ( source.color && source.color.background ) {
		$fallback.css( '--wiki-color', source.color.background );
	}

	return $fallback;
};

/**
 * @return {Array} Distinct namespaces of all tasks, for the namespace filter
 */
ext.unifiedTaskOverview.ui.MyTasksGrid.prototype.getNamespaces = function () {
	const namespaces = [];
	this.items.forEach( ( item ) => {
		const name = item.namespace || '';
		if ( name && !namespaces.includes( name ) ) {
			namespaces.push( name );
		}
	} );

	return namespaces.sort();
};

/**
 * Main wiki first, every other instance by its display name.
 *
 * @return {Array} Distinct instances of all tasks, for the instance filter
 */
ext.unifiedTaskOverview.ui.MyTasksGrid.prototype.getInstances = function () {
	const instances = [];
	this.items.forEach( ( item ) => {
		const name = item.source ? item.source.display_text : '';
		if ( name && !instances.some( ( instance ) => instance.name === name ) ) {
			instances.push( { name: name, isRoot: !!item.source.is_root } );
		}
	} );

	instances.sort( ( a, b ) => {
		if ( a.isRoot !== b.isRoot ) {
			return a.isRoot ? -1 : 1;
		}

		return a.name.localeCompare( b.name, undefined, { numeric: true, sensitivity: 'base' } );
	} );

	return instances.map( ( instance ) => instance.name );
};
