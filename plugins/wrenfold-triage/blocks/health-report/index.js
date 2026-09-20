/**
 * Site health report block: editor side.
 *
 * Plain JavaScript using WordPress's own libraries, so there is no build step.
 * The front end is rendered in PHP (render.php); the editor shows a preview of
 * that same output plus one setting.
 */
( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, SelectControl, Disabled } = wp.components;
	const { __ } = wp.i18n;
	const ServerSideRender = wp.serverSideRender;

	registerBlockType( 'wrenfold/health-report', {
		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps();

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Report settings', 'wrenfold-triage' ) },
						el( SelectControl, {
							label: __( 'How much to show', 'wrenfold-triage' ),
							value: attributes.show,
							options: [
								{ label: __( 'The whole report', 'wrenfold-triage' ), value: 'all' },
								{ label: __( 'Just the summary', 'wrenfold-triage' ), value: 'summary' },
							],
							onChange: function ( value ) {
								setAttributes( { show: value } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el( 'p', null, __( 'The numbers come from the last scan. Run one under Site Triage in the admin menu.', 'wrenfold-triage' ) )
					)
				),
				el(
					'div',
					blockProps,
					el(
						Disabled,
						null,
						el( ServerSideRender, {
							block: 'wrenfold/health-report',
							attributes: attributes,
							skipBlockSupportAttributes: true,
						} )
					)
				)
			);
		},
		save: function () {
			return null; // Rendered by PHP.
		},
	} );
} )( window.wp );
