function getTasks() {
	return $.ajax( {
		url: mw.util.wikiScript( 'rest' ) + '/unifiedtaskoverview/list',
		contentType: 'application/json',
		dataType: 'json'
	} );
}

/**
 * The wiki badges of the instance column come from BlueSpiceWikiFarm, which is an
 * optional dependency: outside of a farm that module simply does not exist.
 *
 * @return {jQuery.Promise}
 */
function loadWikiFarm() {
	const module = 'ext.bluespice.wikiFarm.bootstrap';
	if ( mw.loader.getState( module ) === null ) {
		return $.Deferred().resolve().promise();
	}

	return mw.loader.using( module );
}

function loadTaskModules( tasks ) {
	const modules = [];
	tasks.forEach( ( task ) => {
		( task.RLmodules || [] ).forEach( ( module ) => {
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

function showAnonNotice( $containers ) {
	$containers.each( ( index, element ) => {
		$( element ).append( new OO.ui.MessageWidget( {
			type: 'notice',
			inline: true,
			label: mw.message( 'unifiedtaskoverview-mytasks-anon' ).text()
		} ).$element );
	} );
}

$( () => {
	// eslint-disable-next-line no-jquery/no-global-selector
	if ( $( '.ve-activated' ).length > 0 ) {
		return;
	}

	// eslint-disable-next-line no-jquery/no-global-selector
	const $containers = $( '.uto-mytasks' );
	if ( !$containers.length ) {
		return;
	}

	if ( mw.user.isAnon() ) {
		showAnonNotice( $containers );
		return;
	}

	// Failing to load the farm module must not cost the user their task list
	loadWikiFarm().catch( () => {} ).then( getTasks ).done( ( tasks ) => {
		tasks = tasks || [];
		loadTaskModules( tasks );
		$containers.each( ( index, element ) => {
			const types = ( $( element ).attr( 'data-types' ) || '' )
				.split( ',' )
				.map( ( type ) => type.trim() )
				.filter( ( type ) => type !== '' );
			const grid = new ext.unifiedTaskOverview.ui.MyTasksGrid( {
				items: tasks,
				types: types
			} );
			$( element ).append( grid.$element );
		} );
	} ).fail( () => {
		$containers.append(
			$( '<p>' ).addClass( 'uto-mytasks-notice' )
				.text( mw.message( 'unifiedtaskoverview-mytasks-error' ).text() )
		);
	} );
} );
