<?php
/*
Plugin Name: Vibe ShortCodes
Plugin URI: http://www.vibethemes.com
Description: Create unlimited shortcodes
Author: VibeThemes
Version: 3.8
Author URI: http://www.vibethemes.com
Text Domain: vibe-shortcodes
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if( !defined('VIBE_PLUGIN_URL')){
    define('VIBE_PLUGIN_URL',plugins_url());
}

/*====== BEGIN VSLIDER======*/

include_once('classes/vibeshortcodes.class.php');
include_once('shortcodes.php');
include_once('ajaxcalls.php');
include_once('upload_handler.php');

/*====== INSTALLATION HOOKS VSLIDER======*/        
// Runs when plugin is activated and creates new database field
register_activation_hook(__FILE__,'vibe_shortcodes_install');
function vibe_shortcodes_install() {
    
}
add_action('plugins_loaded','vibe_shortcodes_translations');
function vibe_shortcodes_translations(){
	$locale = apply_filters("plugin_locale", get_locale(), 'vibe-shortcodes');
	$lang_dir = dirname( __FILE__ ) . '/languages/';
	$mofile        = sprintf( '%1$s-%2$s.mo', 'vibe-shortcodes', $locale );

	$mofile_local  = $lang_dir . $mofile;
	$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;
	if ( file_exists( $mofile_global ) ) {
		load_textdomain( 'vibe-shortcodes', $mofile_global );
	} else {
		load_textdomain( 'vibe-shortcodes', $mofile_local );
	}	
}

add_action( 'init', 'vibe_shortcodes_update' );
function vibe_shortcodes_update() {
	/* Load Plugin Updater */
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'autoupdate/class-plugin-update.php' );
	/* Updater Config */
	$config = array(
		'base'      => plugin_basename( __FILE__ ), //required
		'dashboard' => true,
		'repo_uri'  => 'http://www.vibethemes.com/',  //required
		'repo_slug' => 'vibe-shortcodes',  //required
	);

	/* Load Updater Class */
	new Vibe_Shortcodes_Auto_Update( $config );
	new WPLMS_ZIP_UPLOAD_HANDLER();
}
