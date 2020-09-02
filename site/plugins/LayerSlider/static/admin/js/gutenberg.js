var __ 					= wp.i18n.__,
	el 					= wp.element.createElement,
	registerBlockType 	= wp.blocks.registerBlockType,

	// Inspector Layout
	BlockControls 		= wp.editor.BlockControls,
	InspectorControls 	= wp.editor.InspectorControls,
	PanelBody			= wp.components.PanelBody,
	PanelRow 			= wp.components.PanelRow,

	// Controls
	Button 				= wp.components.Button,
	Icon 				= wp.components.Icon,
	IconButton 			= wp.components.IconButton,
	TextControl 		= wp.components.TextControl,
	ToggleControl 		= wp.components.ToggleControl,
	SelectControl 		= wp.components.SelectControl,

	// Misc
	Placeholder 		= wp.components.Placeholder;

// In some rare cases the globally loaded LS_GB_l10n
// variable might not be available due to plugins making
// changes in the WP script queue. The below makes sure
// that we can at least avoid undef JS errors.
if( typeof LS_GB_l10n === 'undefined' ) {
	LS_GB_l10n = {};
}


var LS_IconElement = el('svg',
	{
		width: 20,
		height: 20
	},
	el( 'path',
		{
			d: "M.485 5.782l9.099 4.128c.266.121 .566.121 .832 0l9.099-4.128c.646-.293.646-1.27 0-1.564L10.416.09a1 1 0 0 0-.832 0L.485 4.218c-.646.293-.646 1.271 0 1.564zm19.03 3.448-2.269-1.029-6.314 2.862c-.295.134-.609.202-.932.202s-.636-.068-.932-.202L2.754 8.202l-2.27 1.029c-.646.293-.646 1.27 0 1.563l9.099 4.125c.266.12 .566.12 .832 0L19.515 10.793c.646-.293.646-1.27 0-1.562zm0 4.992-2.261-1.025-6.323 2.866c-.295.134-.609.202-.932.202s-.636-.068-.932-.202L2.746 13.198.485 14.223c-.646.293-.646 1.27 0 1.563l9.099 4.125c.266.12 .566.12 .832 0L19.515 15.785c.646-.293.646-1.27 0-1.562z"
		}
	)
);

registerBlockType( 'kreatura/layerslider', {

	title: 'LayerSlider',
	description: LS_GB_l10n.BlockDesc,
	keywords: [
		'animation',
		'gallery',
		'popup'
	],
	icon: LS_IconElement,
	category: 'widgets',
	supports: {
		html: false
	},

	attributes: {

		id: {
			type: 'string',
			default: ''
		},

		name: {
			type: 'string',
			default: ''
		},

		previewURL: {
			type: 'string',
			default: ''
		},

		autostart: {
			type: 'string',
			default: ''
		},

		firstslide: {
			type: 'string',
			default: ''
		},

		skin: {
			type: 'string',
			default: ''
		},

		slideCount: {
			type: 'integer',
			default: 1
		},

		marginTop: {
			type: 'string',
			default: ''
		},

		marginRight: {
			type: 'string',
			default: ''
		},

		marginBottom: {
			type: 'string',
			default: ''
		},

		marginLeft: {
			type: 'string',
			default: ''
		}
	},

	edit: function( props  ) {

		var attrs = props.attributes;
		var controls = [];



		// INSPECTOR CONTROLS
		// --------------------------------

		var skinsOptions = [{
			value: '',
			label: LS_GB_l10n.SkinInherit
		}];

		if( LS_GB_l10n.skins ) {
			for( var skinHandle in LS_GB_l10n.skins ) {
				skinsOptions.push({
					value: skinHandle,
					label: LS_GB_l10n.skins[ skinHandle ]
				})
			}
		}


		var lsInspectorControls =

			el( InspectorControls, {},

				// Panel Body
				el( PanelBody, { title: LS_GB_l10n.OverridePanel },

					// Description
					el( 'p', null, LS_GB_l10n.OverridePanelDesc ),


					// Skins
					el( SelectControl, {
						label: LS_GB_l10n.SkinLabel,
						value: attrs.skin,
						onChange: function( newValue ) {
							props.setAttributes({ skin: newValue })
						},
						options: skinsOptions
					}),


					// Auto Start Slideshow
					el( SelectControl, {
						label: LS_GB_l10n.AutoStartLabel,
						value: attrs.autostart,
						onChange: function( newValue ) {
							props.setAttributes({ autostart: newValue })
						},
						options: [
							{ value: '', label: LS_GB_l10n.AutoStartInherit },
							{ value: 'enabled', label: LS_GB_l10n.AutoStartEnable },
							{ value: 'disabled', label: LS_GB_l10n.AutoStartDisable },
						]
					}),


					// First Slide
					el( TextControl, {
						label: LS_GB_l10n.FirstSlideLabel,
						value: attrs.firstslide,
						placeholder: LS_GB_l10n.FirstSlideInherit,
						type: 'number',
						onChange: function( newValue ) {
							props.setAttributes({ firstslide: newValue });
						}
					}),
				),

				el( PanelBody, { title: LS_GB_l10n.LayoutPanel },

					// Description
					el( 'p', null, LS_GB_l10n.LayoutPanelDesc ),

					// Margin Controls
					el( 'p', {}, LS_GB_l10n.MarginLabel ),
					el( 'div', { className: 'ls-gb-margin-holder' },

						el( TextControl, {
							className: 'ls-gb-margin ls-gb-margin-top',
							value: attrs.marginTop,
							placeholder: '0px',
							onChange: function( newValue ) {
								props.setAttributes({ marginTop: newValue });
							}
						}),

						el( TextControl, {
							className: 'ls-gb-margin ls-gb-margin-right',
							value: attrs.marginRight,
							placeholder: LS_GB_l10n.MarginAutoPlaceholder,
							onChange: function( newValue ) {
								props.setAttributes({ marginRight: newValue });
							}
						}),

						el( TextControl, {
							className: 'ls-gb-margin ls-gb-margin-bottom',
							value: attrs.marginBottom,
							placeholder: '0px',
							onChange: function( newValue ) {
								props.setAttributes({ marginBottom: newValue });
							}
						}),

						el( TextControl, {
							className: 'ls-gb-margin ls-gb-margin-left',
							value: attrs.marginLeft,
							placeholder: LS_GB_l10n.MarginAutoPlaceholder,
							onChange: function( newValue ) {
								props.setAttributes({ marginLeft: newValue });
							}
						})
					)
				)
			);




		// BLOCK PLACEHOLDER
		// --------------------------------
		var lsBlockPlaceholder =
			el( Placeholder, {
					icon: el( Icon, {
						className: 'editor-block-icon block-editor-block-icon',
						icon: LS_IconElement
					}),
					label: 'LayerSlider',
					instructions: LS_GB_l10n.PlaceholderDesc,
				},

				el( Button, {
					isDefault: true,
					isLarge: true,
					onClick: function() {

						LS_SliderLibrary.open({
							onChange: function( sliderData ) {
								props.setAttributes({
									id: sliderData.id.toString(),
									name: sliderData.name,
									previewURL: sliderData.previewurl,
									slideCount: sliderData.slidecount
								});
							}
						});
					}
				}, LS_GB_l10n.SliderLibraryButton )

			);



		// Block Controls
		var lsBlockControls =

			el( BlockControls, null,
				el( wp.components.Toolbar, null,


					el( wp.components.ToolbarButton, {
						label: LS_GB_l10n.BlockEditLabel,
						icon: 'edit',
						onClick: function() {
							LS_SliderLibrary.open({
								onChange: function( sliderData ) {
									props.setAttributes({
										id: sliderData.id.toString(),
										name: sliderData.name,
										previewURL: sliderData.previewurl,
										slideCount: sliderData.slidecount
									});
								}
							});
						}
					})

				)
			);




		// BLOCK CONTENT
		// --------------------------------
		var classNames = 'ls-gb-block-content';

		if( ! attrs.previewURL ) {
			classNames += ' no-preview';
		}

		var lsBlockContent = el(
			'div', {
				className: classNames,
				style: {
					backgroundImage: 'url('+attrs.previewURL+')',
					marginTop: attrs.marginTop ? parseInt( attrs.marginTop )+'px' : 0,
					marginRight: attrs.marginRight ? parseInt( attrs.marginRight )+'px' : 0,
					marginBottom: attrs.marginBottom ? parseInt( attrs.marginBottom )+'px' : 0,
					marginLeft: attrs.marginLeft ? parseInt( attrs.marginLeft )+'px' : 0,
				}
			},
				el('div', { className: 'info' },
					el( 'div', { className: 'name' }, attrs.name )
				),

				el('span', { className: 'ls-arrow-left dashicons dashicons-arrow-left-alt2' }),
				el('span', { className: 'ls-arrow-right dashicons dashicons-arrow-right-alt2' }),
		);



		if( attrs.slideCount && attrs.slideCount > 1 ) {

			var lsSlidesHolder = el('div', { className: 'ls-slides-holder' } );
			lsBlockContent.props.children.push( lsSlidesHolder );

			if( ! lsSlidesHolder.props.children ) {
				lsSlidesHolder.props.children = [];
			}

			for( var c = 0; c < attrs.slideCount; c++ ) {

				lsSlidesHolder.props.children.push(
					el('span', { className: 'ls-slide-marker dashicons dashicons-marker' })
				);
			}
		}


		if( ! attrs.id ) {
			controls.push( lsBlockPlaceholder );
		} else {
			controls.push( lsBlockControls, lsBlockContent, lsInspectorControls );
		}

		return controls;
	},


	save: function( props ) {

		// We're going to be rendering in PHP, so save() can just return null.
		return null;
	}

});