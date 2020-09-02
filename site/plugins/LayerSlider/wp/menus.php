<?php

// Register "New" admin menu bar menu
add_action('admin_bar_menu', 'ls_admin_bar', 999 );
function ls_admin_bar($wp_admin_bar) {
	$wp_admin_bar->add_node(array(
		'parent' => 'new-content',
		'id'    => 'ab-ls-add-new',
		'title' => 'LayerSlider',
		'href'  => admin_url('admin.php?page=layerslider')
	));
}

// Register sidebar menu
add_action('admin_menu', 'layerslider_settings_menu');
function layerslider_settings_menu() {

	// Menu hook
	global $layerslider_hook;

	$capability = get_option('layerslider_custom_capability', 'manage_options');
	$icon = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="rgba(240, 245, 250, 0.6)" d="M.485 5.782l9.099 4.128c.266.121 .566.121 .832 0l9.099-4.128c.646-.293.646-1.27 0-1.564L10.416.09a1 1 0 0 0-.832 0L.485 4.218c-.646.293-.646 1.271 0 1.564zm19.03 3.448-2.269-1.029-6.314 2.862c-.295.134-.609.202-.932.202s-.636-.068-.932-.202L2.754 8.202l-2.27 1.029c-.646.293-.646 1.27 0 1.563l9.099 4.125c.266.12 .566.12 .832 0L19.515 10.793c.646-.293.646-1.27 0-1.562zm0 4.992-2.261-1.025-6.323 2.866c-.295.134-.609.202-.932.202s-.636-.068-.932-.202L2.746 13.198.485 14.223c-.646.293-.646 1.27 0 1.563l9.099 4.125c.266.12 .566.12 .832 0L19.515 15.785c.646-.293.646-1.27 0-1.562z"/></svg>');

	// Add main page
	$layerslider_hook = add_menu_page(
		'LayerSlider WP', 'LayerSlider WP',
		$capability, 'layerslider', 'layerslider_router',
		$icon
	);

	// Add "All Sliders" submenu
	add_submenu_page(
		'layerslider', 'LayerSlider WP', __('Sliders', 'LayerSlider'),
		$capability, 'layerslider', 'layerslider_router'
	);


	// Add "Settings" submenu
	add_submenu_page(
		'layerslider', 'LayerSlider Options', __('Options', 'LayerSlider'),
		$capability, 'layerslider-options', 'layerslider_router'
	);

	// Add "Add-Ons" submenu
	add_submenu_page(
		'layerslider', 'LayerSlider Add-Ons', __('Add-Ons', 'LayerSlider'),
		$capability, 'layerslider-addons', 'layerslider_router'
	);

}

// Help menu
add_filter('contextual_help', 'layerslider_help', 10, 3);
function layerslider_help($contextual_help, $screen_id, $screen) {

	if( strpos( $screen->base, 'layerslider') !== false ) {
		$screen->add_help_tab(array(
			'id' => 'help',
			'title' => __('Getting Help', 'LayerSlider'),
			'content' => '<p>'. sprintf(__('Please read our  %sOnline Documentation%s carefully, it will likely answer all of your questions.<br><br>You can also check the %sFAQs%s for additional information, including our support policies and licensing rules.', 'LayerSlider'), '<a href="https://layerslider.kreaturamedia.com/documentation/" target="_blank">', '</a>', '<a href="https://kreatura.ticksy.com/" target="_blank">', '</a>').'</p>'
		));
	}
}

function layerslider_router() {

	// Get current screen details
	$screen = get_current_screen();


	if( strpos( $screen->base, 'layerslider-options' ) !== false ) {

		// Avoid PHP undef notice
		$section = ! empty( $_GET['section'] ) ? $_GET['section'] : false;

		switch( $section ) {
			case 'system-status':
				include(LS_ROOT_PATH.'/views/system_status.php');
				break;

			case 'revisions':
				include(LS_ROOT_PATH.'/views/revisions.php');
				break;

			case 'about':
				include(LS_ROOT_PATH.'/views/about.php');
				break;

			case 'skin-editor':
				include(LS_ROOT_PATH.'/views/skin_editor.php');
				break;

			case 'css-editor':
				include(LS_ROOT_PATH.'/views/css_editor.php');
				break;

			case 'transition-builder':
				include(LS_ROOT_PATH.'/views/transition_builder.php');
				break;

			default:
				include(LS_ROOT_PATH.'/views/settings.php');
				break;
		}


	} elseif( strpos( $screen->base, 'layerslider-addons') !== false ) {
		include(LS_ROOT_PATH.'/views/addons.php');

	} elseif(isset($_GET['action']) && $_GET['action'] == 'edit') {
		include(LS_ROOT_PATH.'/views/slider_edit.php');

	} else {
		include(LS_ROOT_PATH.'/views/slider_list.php');
	}
}



?>
