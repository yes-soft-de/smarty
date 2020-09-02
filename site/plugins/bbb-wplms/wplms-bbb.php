<?php
/*
Plugin Name: WPLMS BigBluebutton
Plugin URI: http://www.Vibethemes.com
Description: Integrates Bigbluebutton with wplms
Version: 1.4
Author: VibeThemes,alexhal
Author URI: http://www.vibethemes.com
License: GPL2
Text Domain: wplms-bbb
*/
/*
Copyright 2014  VibeThemes  (email : vibethemes@gmail.com)

WPLMS BigBluebutton program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

WPLMS BigBluebutton program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WPLMS BigBluebutton program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include_once 'classes/wplmsbbb.class.php';


if(class_exists('Wplms_Bbb'))
{   
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('Wplms_Bbb', 'activate'));
    register_deactivation_hook(__FILE__, array('Wplms_Bbb', 'deactivate'));

    // instantiate the plugin class
    add_action('init',function(){
        $active_plugins =get_option( 'active_plugins' );
        if ( (in_array( 'vibe-customtypes/vibe-customtypes.php', apply_filters( 'active_plugins', $active_plugins ) ) || function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'vibe-customtypes/vibe-customtypes.php')) && 

                (in_array( 'vibe-course-module/loader.php', apply_filters( 'active_plugins', $active_plugins ) ) || function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'vibe-course-module/loader.php')) &&

                    (in_array( 'bigbluebutton/bigbluebutton.php', apply_filters( 'active_plugins', $active_plugins ) ) || function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'bigbluebutton/bigbluebutton.php')) &&

                    (in_array( 'buddypress/bp-loader.php', apply_filters( 'active_plugins', $active_plugins ) ) || function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'buddypress/bp-loader.php')) 
                    && defined('BIGBLUEBUTTON_VERSION')

           ) {
                $Wplms_Bbb = Wplms_Bbb::init();
                add_action('bp_init','show_meetings_tab');
            
        }
        if((!in_array( 'bigbluebutton/bigbluebutton.php', apply_filters( 'active_plugins',$active_plugins ) ) && !(function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'bigbluebutton/bigbluebutton.php'))) || !defined('BIGBLUEBUTTON_VERSION')){
            add_action('admin_notices','bigbluebutton_inactive_notice');
            
        }
    },2);

    function bigbluebutton_inactive_notice(){
        $class = 'notice notice-error is-dismissible';
            $message = sprintf(__( 'Wplms bbb needs %s plugin to be activated and its version should be 3.0.0 or above', 'wplms-bbb' ),'<a href="https://wordpress.org/plugins/bigbluebutton/">Bigbluebutton</a>');

          printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
    }
    
    add_action('plugins_loaded','wplms_bbb_translations');
    function wplms_bbb_translations(){
        $locale = apply_filters("plugin_locale", get_locale(), 'wplms-bbb');
        $lang_dir = dirname( __FILE__ ) . '/languages/';
        $mofile        = sprintf( '%1$s-%2$s.mo', 'bbb-wplms', $locale );
        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

        if ( file_exists( $mofile_global ) ) {
            load_textdomain( 'wplms-bbb', $mofile_global );
        } else {
            load_textdomain('wplms-bbb', $mofile_local );
        }  
    }
}


function show_meetings_tab(){
    include_once 'classes/class.groups.php';
}
