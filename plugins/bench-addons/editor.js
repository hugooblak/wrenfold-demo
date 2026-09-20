/**
 * Bench Addons: editor side.
 *
 * Both blocks are rendered in PHP, so the editor just shows a live preview of
 * that output. No build step.
 */
( function ( wp ) {
	[ 'wf-bench/spec-table', 'wf-bench/team-card' ].forEach( function ( name ) {
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
