/**
 * FAQ edit screen: an "Order" box in the sidebar.
 *
 * WordPress stores the order in the built-in menu_order field, but its default
 * panel is labelled "Parent" and talks about child pages. This replaces it with
 * a plain number field. Plain JavaScript, no build step.
 */
( function ( wp ) {
	const { registerPlugin } = wp.plugins;
	const { PluginDocumentSettingPanel } = wp.editor;
	const { TextControl } = wp.components;
	const { useSelect, useDispatch } = wp.data;
	const { createElement: el } = wp.element;
	const { __ } = wp.i18n;

	function FaqOrderPanel() {
		const order = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'menu_order' );
		}, [] );
		const { editPost } = useDispatch( 'core/editor' );

		return el(
			PluginDocumentSettingPanel,
			{ name: 'wrenfold-faq-order', title: __( 'Order', 'wrenfold-faq' ), initialOpen: true },
			el( TextControl, {
				label: __( 'Position in the list', 'wrenfold-faq' ),
				help: __( 'Lower numbers show first. Tip: use 10, 20, 30 so you can slot new questions in between.', 'wrenfold-faq' ),
				type: 'number',
				min: 0,
				value: order || 0,
				onChange: function ( value ) {
					editPost( { menu_order: parseInt( value, 10 ) || 0 } );
				},
				__nextHasNoMarginBottom: true,
				__next40pxDefaultSize: true,
			} )
		);
	}

	registerPlugin( 'wrenfold-faq-order', { render: FaqOrderPanel } );

	// Hide the default "Parent" panel; FAQs don't have parents.
	wp.domReady( function () {
		wp.data.dispatch( 'core/editor' ).removeEditorPanel( 'page-attributes' );
	} );
} )( window.wp );
