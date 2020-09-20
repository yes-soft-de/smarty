<?php

// This is the export file that writes json files
// Your build process should probably exclude this file from the final theme zip, but it doesn't really matter.

// Change line 100 where it has the hard coded: /../theme/images/stock/ path
// This is the path where media files are copied to during export.
// Change this to your theme folder images/stock/ path, whatever that may be.
// The importer will look for local 'images/stock/*.jpg' files during import.

// Also change the json export path near the bottom: theme/plugins/envato_setup/content/

if( isset($_POST['export_content']) ){
	//Export Content
	export_website_content();

}elseif( isset($_POST['download_export_zip']) ){
	// Download Created Zip file
	$filename = $_POST['zip_file'];

	if ( file_exists($filename) ) {
		if ( headers_sent() ){
			// HTTP header has already been sent
			return false;
		}
		// clean buffer(s)
		while ( ob_get_level() > 0 ){
			ob_end_clean();
		}
		ob_start();
		//Send headers to browser for zip download
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="'.basename($filename).'"');
		header('Content-Length: ' . filesize($filename));

		ob_flush();
		ob_clean();
		readfile($filename);
		// delete zip file after download
		unlink($filename);
	}

}else{

	// Create a form for exporting data of the website.
	echo '<div class="wplms_export_content_form">';
	echo '<div class="container">';
	echo '<div class="row">';
	echo '<form method="post">';
	wp_nonce_field('export_content','_wpnonce');
	echo '<h1>'.__('Export Website Content.','vibe').'</h1>';

	//Title of the exported content
	echo '<div class="export_title">';
	echo '<label style="font-size:14px;font-weight:500;margin-right:50px;">'.__('Enter Export Title: ','vibe').'</label>';
	echo '<input type="text" name="export_title" style="width:200px;" />';
	echo '</div>';
	echo '<br>';

	//Export post types
	echo '<div class="export_post_types">';
	echo '<h3>'.__('Post Types','vibe').'</h3>';
	foreach ( get_post_types() as $post_type ) {
	if( $post_type == 'revision' ){
	continue;
	}
	echo '<div class="checkbox" style="padding:3px 15px;width:170px;display:inline-block;"><input type="checkbox" name="post_type[]" id="'.$post_type.'" value="'.$post_type.'" checked="checked" style="float:left;margin-right:10px;margin-top:5px;" /><label for="'.$post_type.'">'.$post_type.'</label></div>';
	}
	echo '</div>';

	//Export taxonomies
	echo '<div class="export_taxonomy" style="margin-top:30px;">';
	echo '<h3>'.__('Taxonomies','vibe').'</h3>';
	foreach ( get_taxonomies() as $taxonomy ) {
	echo '<div class="checkbox" style="padding:3px 15px;width:170px;display:inline-block;"><input type="checkbox" name="taxonomy[]" id="'.$taxonomy.'" value="'.$taxonomy.'" checked="checked" style="float:left;margin-right:10px;margin-top:5px;" /><label for="'.$taxonomy.'">'.$taxonomy.'</label></div>';
	}
	echo '</div>';

	//Export options
	echo '<div class="export_options" style="margin-top:30px;">';
	echo '<h3>'.__('Options And Settings','vibe').'</h3>';
	$options = apply_filters('wplms_installer_export_options',array(
									'options' 					=> 'Options',
									'widgets' 					=> 'Widgets',
									'vibe_course_permalinks'	=> 'Permalinks',
									'vibe_customizer' 			=> 'Customizer',
									'lms_settings' 				=> 'LMS Settings',
								));
	foreach ( $options as $key => $value ) {
		echo '<div class="checkbox" style="padding:3px 15px;width:170px;display:inline-block;"><input type="checkbox" name="option[]" id="'.$key.'" value="'.$key.'" checked="checked" style="float:left;margin-right:10px;margin-top:5px;" /><label for="'.$key.'">'.$value.'</label></div>';
	}
	echo '</div>';

	echo '<input type="submit" name="export_content" class="button primary" value="'.__('Export Content','vibe').'" style="margin:30px 0;" />';
	echo '</form>';
	echo '</div></div></div>';
}



function export_website_content(){
	if ( !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'],'export_content') ){
	    _e('Security check Failed. Contact Administrator.','vibe');
	    die();
	}

	$default_content = array();
	$post_types = $_POST['post_type'];
	$taxonomies = $_POST['taxonomy'];
	$options = $_POST['option'];

	/*
	* Create directory for exporting content
	*/

	//Folder title
	if( !empty($_POST['export_title']) ){
		$stylefolder = $_POST['export_title'];
	}else{
		// which style are we writing to?
	    $stylefolder = get_option('wplms_site_style');
	    if( empty($stylefolder) ){
	    	$stylefolder = 'demo1';
	    }
	}
	
    $upload_dir_base = wp_upload_dir();
	$dir = $upload_dir_base['basedir'].'/export_demos/'.$stylefolder.'/';

	if ( function_exists('is_dir') && !is_dir( $dir ) ) {
		if( function_exists('mkdir') ){
			mkdir($dir, 0755, true) || chmod($dir, 0755);
		}
	}

	$image_dir = $dir.'images/';
	if ( function_exists('is_dir') && !is_dir( $image_dir ) ) {
		if( function_exists('mkdir') ){
			mkdir($image_dir, 0755, true) || chmod($image_dir, 0755);
		}
	}

	/*
	 *
	 * Export Post Types
	 *
	 */

	if( !empty($post_types) ){
		foreach ( $post_types as $post_type ) {

			$args = array( 'post_type' => $post_type, 'posts_per_page' => - 1 );
			$args['post_status'] = array( 'publish', 'private', 'inherit' );

			$post_datas = get_posts( $args );
			if ( !isset( $default_content[ $post_type ] ) ) {
				$default_content[ $post_type ] = array();
			}
			$object = get_post_type_object( $post_type );
			if ( $object && !empty( $object->labels->singular_name ) ) {
				$type_title = $object->labels->name;
			} else {
				$type_title = ucwords( $post_type ) . 's';
			}

			foreach ( $post_datas as $post_data ) {
				$meta = get_post_meta( $post_data->ID, '', true );

				foreach ( $meta as $meta_key => $meta_val ) {
					if (
					// which keys to nuke all the time
					in_array( $meta_key, array( '_location_id' ) )
					||
					(
						// which keys we want to keep all the time, using strpos:
						strpos( $meta_key, 'elementor' ) === false &&
						strpos( $meta_key, 'vc_' ) === false &&
						strpos( $meta_key, 'vibe_' ) === false &&
						strpos( $meta_key, '_builder' ) === false &&
						strpos( $meta_key, 'wpb_' ) === false &&
						strpos( $meta_key, '_slider' ) === false &&
						// which post types we keep all meta values for:
						! in_array( $post_type, array(
							'nav_menu_item',
							'location',
							'event','course','unit','quiz','question','wplms-assignment',
							'product',
							'wpcf7_contact_form',
						) ) &&
						// other meta keys we always want to keep:
						! in_array( $meta_key, array(
							'sliderlink',
							'slidercolor',
							'_wp_page_template',
							'_wp_attached_file',
							'_thumbnail_id',
						) )
					)
					) {
						unset( $meta[ $meta_key ] );
					} else {
						$meta[ $meta_key ] = maybe_unserialize( get_post_meta( $post_data->ID, $meta_key, true ) );
					}
				}

				// copy stock images into the images/stock/ folder for theme import.
				if ( $post_type == 'attachment' ) {
					$file = get_attached_file( $post_data->ID );
					if ( is_file( $file ) ) {
						if ( filesize( $file ) > 1500000 ) {
							$image = wp_get_image_editor( $file );
							if ( ! is_wp_error( $image ) ) {
								list( $width, $height, $type, $attr ) = getimagesize( $file );
								$image->resize( min( $width, 1200 ), null, false );
								$image->save( $file );
							}
						}
						$post_data->guid = wp_get_attachment_url( $post_data->ID );
						copy( $file, $image_dir . basename( $file ) );
					}
					// fix for incorrect GUID when renaming files with the rename plugin, causes import to bust.
				}

				$terms = array();

				/*
				 *
				 * Export Taxonomies
				 *
				 */
				if( !empty($taxonomies) ){
					foreach ( $taxonomies as $taxonomy ) {
						if( $taxonomy == 'nav_menu' ){
							$export_menu = 'yes';
						}
						$terms[ $taxonomy ] = wp_get_post_terms( $post_data->ID, $taxonomy, array( 'fields' => 'all' ) );
						if($terms[$taxonomy]){
							foreach($terms[$taxonomy] as $tax_id => $tax){
								if(!empty($tax->term_id)) {
									$terms[ $taxonomy ][ $tax_id ] -> meta = get_term_meta( $tax->term_id );
									if(!empty($terms[ $taxonomy ][ $tax_id ] -> meta)){
										foreach($terms[ $taxonomy ][ $tax_id ] -> meta as $key=>$val){
											if(is_array($val) && count($val) == 1 && isset($val[0])){
												$terms[ $taxonomy ][ $tax_id ] -> meta[$key] = $val[0];
											}
										}
									}
								}
							}
						}
					}
				}

				/*
				 *
				 * Create Default Content
				 *
				 */

				$default_content[$post_type][] = array(
						'type_title'     => $type_title,
						'post_id'        => $post_data->ID,
						'post_title'     => $post_data->post_title,
						'post_status'    => $post_data->post_status,
						'post_name'      => $post_data->post_name,
						'post_content'   => $post_data->post_content,
						'post_excerpt'   => $post_data->post_excerpt,
						'post_parent'    => $post_data->post_parent,
						'menu_order'     => $post_data->menu_order,
						'post_date'      => $post_data->post_date,
						'post_date_gmt'  => $post_data->post_date_gmt,
						'guid'           => $post_data->guid,
						'post_mime_type' => $post_data->post_mime_type,
						'meta'           => $meta,
						'terms'          => $terms,
					);
			}
		}
	}

	// put certain content at very end.
	$nav = isset( $default_content['nav_menu_item'] ) ? $default_content['nav_menu_item'] : array();
	if ( $nav ) {
		unset( $default_content['nav_menu_item'] );
		$default_content['nav_menu_item'] = $nav;
	}

	/*
	 *
	 * Export Menus
	 *
	 */

	// find the ID of our menu names so we can import them into default menu locations and also the widget positions below.
	if( isset($export_menu) && ($export_menu == 'yes') ){
		$menus    = get_terms( 'nav_menu' );
		$menu_ids = array();
		foreach ( $menus as $menu ) {
			$name = strtolower($menu->name);
			if ( $name == 'main menu' || $name == 'main' ) {
				$menu_ids['main-menu'] = $menu->term_id;
			} else if ( $name == 'top menu' || $name == 'top') {
				$menu_ids['top-menu'] = $menu->term_id;
			}else if ( $name == 'mobile menu' || $name == 'mobile' ) {
				$menu_ids['mobile-menu'] = $menu->term_id;
			}else if ( $name == 'footer menu' || $name == 'footer' ) {
				$menu_ids['footer-menu'] = $menu->term_id;
			}
		}
	}

	/*
	 *
	 * Export Widgets
	 *
	 */

	foreach ($options as $option) {
		if( $option == 'widgets' ){
			// used for me to export my widget settings.
			$widget_positions = get_option( 'sidebars_widgets' );
			$widget_options   = array();
			foreach ( $widget_positions as $sidebar_name => $widgets ) {
				if ( is_array( $widgets ) ) {
					foreach ( $widgets as $widget_name ) {
						$widget_name_strip                    = preg_replace( '#-\d+$#', '', $widget_name );
						$widget_options[ $widget_name_strip ] = get_option( 'widget_' . $widget_name_strip );
					}
				}
			}
		}

		if( $option == 'options' ){
			$export_options = 'yes';
		}
		if( $option == 'vibe_customizer' ){
			$export_customizer = 'yes';
		}
		if( $option == 'lms_settings' ){
			$export_lms_settings = 'yes';
		}
		if( $option == 'vibe_course_permalinks' ){
			$export_permalinks = 'yes';
		}
	}

	/*
	 *
	 * Export Options
	 *
	 */

	$my_options  = array();
	$all_options = wp_load_alloptions();
	foreach ( $all_options as $name => $value ) {
		if ( isset($export_options) && ($export_options == 'yes') && stristr( $name, 'wplms' ) ) {
			$my_options[ $name ] = maybe_unserialize( $value );
		}
		if ( isset($export_customizer) && ($export_customizer == 'yes') && stristr( $name, 'vibe_customizer' ) ) {
			$my_options[ $name ] = maybe_unserialize( $value );
		}
		if ( isset($export_lms_settings) && ($export_lms_settings == 'yes') && stristr( $name, 'lms_settings' ) ) {
			$my_options[ $name ] = maybe_unserialize( $value );
		}
		if ( isset($export_permalinks) && ($export_permalinks == 'yes') && stristr( $name, 'vibe_course_permalinks' ) ) {
			$my_options[ $name ] = maybe_unserialize( $value );
		}
		if ( stristr( $name, '_widget_area_manager' ) ) {
			$my_options[ $name ] = $value;
		}
		if ( stristr( $name, 'wam_' ) ) {
			$my_options[ $name ] = $value;
		}

		if ( 'theme_mods_vibe' === $name ) {
			$my_options[ $name ] = maybe_unserialize($value);
			unset($my_options[ $name ]['nav_menu_locations']);
		}
	}
	$my_options['woocommerce_cart_redirect_after_add'] = 'yes';
	$my_options['woocommerce_enable_ajax_add_to_cart'] = 'no';

	/*
	 *
	 * Upload/export files in the wordpress uploads folder
	 *
	 */

	file_put_contents( $dir . 'default.json' , json_encode( $default_content ) );
	file_put_contents( $dir . 'widget_positions.json' , json_encode( $widget_positions ) );
	file_put_contents( $dir . 'widget_options.json' , json_encode( $widget_options ) );
	file_put_contents( $dir . 'menu.json' , json_encode( $menu_ids ) );
	file_put_contents( $dir . 'options.json' , json_encode( $my_options ) );

	$zip = new ZipArchive();
	$zip_file = $upload_dir_base['basedir'].'/export_demos/'.$stylefolder.'.zip';
	$zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

	$files = glob($dir.'*.json');
	foreach ( $files as $file ) {
		$zip->addFile($file,$stylefolder.'/'.basename($file));
	}
	$images = glob($image_dir.'*.*');
	foreach ($images as $image) {
		$zip->addFile($image,$stylefolder.'/images/'.basename($image));
	}
	$zip->close();
	
	/*
	 *
	 * Export Complete
	 *
	 */

	echo '<h1>'.__('Export Done: ','vibe').'</h1>';
	echo '<form method="post">';
	echo '<input type="submit" name="download_export_zip" class="button primary" value="'.__('Download Exported Content','vibe').'" />';
	echo '<input type="hidden" name="zip_file" value="'.$zip_file.'" />';
	echo '</form>';

}
