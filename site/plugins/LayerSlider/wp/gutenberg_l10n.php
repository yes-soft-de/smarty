<?php

$l10n_ls_gutenberg = array(

	// Block options
	'BlockDesc' 			=> __('Insert a LayerSlider slider or popup to your pages and posts.', 'LayerSlider'),
	'BlockEditLabel' 		=> __('Choose slider', 'LayerSlider'),

	'OverridePanel' 		=> __('Override Slider Settings', 'LayerSlider'),
	'OverridePanelDesc' 	=> __('Overriding slider settings is optional. It can be useful if you want to make small changes to the same slider in certain situations without having duplicates. For example, you might want to change the slider skin on some pages to fit better to a different page style.', 'LayerSlider'),

	'SkinLabel' 			=> __('Skin', 'LayerSlider'),
	'SkinInherit' 			=> __('No override', 'LayerSlider'),

	'AutoStartLabel' 		=> __('Auto-Start Slideshow', 'LayerSlider'),
	'AutoStartInherit' 		=> __('No override', 'LayerSlider'),
	'AutoStartEnable' 		=> __('Enabled', 'LayerSlider'),
	'AutoStartDisable' 		=> __('Disabled', 'LayerSlider'),

	'FirstSlideLabel' 		=> __('Start With Slide', 'LayerSlider'),
	'FirstSlideInherit' 	=> __('No override', 'LayerSlider'),


	'LayoutPanel' 			=> __('Layout', 'LayerSlider'),
	'LayoutPanelDesc' 		=> __('The Gutenberg editor has a native Spacer block, which you can also use to make more room around the slider.', 'LayerSlider'),
	'MarginLabel' 			=> __('Margins', 'LayerSlider'),
	'MarginAutoPlaceholder' => __('auto', 'LayerSlider'),
	'MarginTopLabel' 		=> __('top', 'LayerSlider'),
	'MarginRightLabel' 		=> __('right', 'LayerSlider'),
	'MarginBottomLabel' 	=> __('bottom', 'LayerSlider'),
	'MarginLeftLabel' 		=> __('left', 'LayerSlider'),


	'PlaceholderDesc' 		=> __('Open the Slider Library with the button below and select the slider you want to insert.', 'LayerSlider'),
	'SliderLibraryButton' 	=> __('Slider Library', 'LayerSlider'),

	'skins' 			=> array()
);


$skins = LS_Sources::getSkins();
foreach( $skins as $handle => $skin ) {
	$l10n_ls_gutenberg['skins'][ $handle ] = $skin['name'];
}