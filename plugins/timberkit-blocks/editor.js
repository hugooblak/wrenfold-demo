/**
 * Timberkit Blocks: editor side. Both blocks render in PHP.
 */
( function ( wp ) {
	[ 'wf-timberkit/price-list', 'wf-timberkit/team-card' ].forEach( function ( name ) {
		wp.blocks.registerBlockType( name, {
			edit: function ( props ) {
				return wp.element.createElement(
					'div',
					wp.blockEditor.useBlockProps(),
					wp.element.createElement(
						wp.components.Disabled,
						null,
						wp.element.createElement( wp.serverSideRender, { block: name, attributes: props.attributes, skipBlockSupportAttributes: true } )
					)
				);
			},
			save: function () {
				return null;
			},
		} );
	} );
} )( window.wp );
