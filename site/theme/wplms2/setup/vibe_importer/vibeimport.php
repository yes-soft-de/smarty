<?php

/**
 * FILE: vibeimport.php 
 * Author: Mr.Vibe 
 * Credits: www.VibeThemes.com
 * Project: WPLMS
 */

function vibe_import($file){

	require_once ABSPATH . 'wp-admin/includes/import.php';
	$file_path = apply_filters('wplms_setup_import_file_path',VIBE_PATH. "/setup/data/$file.xml",$file);

	if( !class_exists('WP_Import') )
		require_once ('wordpress-importer.php');
			if( class_exists('WP_Import') ){
     			
		if( file_exists($file_path) ){

			do_action('wplms_before_sample_data_import',$file);

			$WP_Import = new WP_Import();

			if ( ! function_exists ( 'wp_insert_category' ) )
				include ( ABSPATH . 'wp-admin/includes/taxonomy.php' );
			if ( ! function_exists ( 'post_exists' ) )
				include ( ABSPATH . 'wp-admin/includes/post.php' );
			if ( ! function_exists ( 'comment_exists' ) )
				include ( ABSPATH . 'wp-admin/includes/comment.php' );

				$WP_Import->fetch_attachments = true;
				$WP_Import->allow_fetch_attachments();

				$WP_Import->import( $file_path );


                _e('Import Complete !','vibe');   
                echo '<a href="'.admin_url('options-permalink.php').'" target="_blank" class="button button-primary" style="margin-top:15px;">'.__('Save Permalinks','vibe').'</a><br />';
                echo '<a href="'.admin_url('options-general.php?page=bp-components').'" target="_blank" class="button button-primary" style="margin-top:15px;">'.__('Save Components','vibe').'</a>';
                do_action('wplms_after_sample_data_import',$file);
		}else{
			echo __("Unable to locate Sample Data file.", 'vibe') ;
		}
	}else{
		echo __("Couldn't install the test demo data as we were unable to use the WP_Import class.", "vibe");
	}	
}
?>