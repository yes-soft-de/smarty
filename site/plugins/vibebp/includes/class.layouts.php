<?php
/**
 * Register Post Types
 *
 * @class       VibeBP_PostTypes
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VibeBP_PostTypes{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_PostTypes();
        return self::$instance;
    }

	private function __construct(){
		add_action( 'init', array($this,'post_types'),5 );
		add_filter('single_template', array($this,'layouts_template'));
    
	}

	function layouts_template($single) {


        global $post;
 
        if ( in_array($post->post_type,apply_filters('vibebp_elementor_layout_template_post_types',array('member-profile','member-card','group-layout','group-card')))) {
        	if(file_exists(get_stylesheet_directory().'/templates/vibebp.php')){
        		return get_stylesheet_directory().'/templates/vibebp.php';
        	}


        	if(file_exists(get_template_directory().'/templates/vibebp.php')){
        		
        		return get_template_directory().'/templates/vibebp.php';
        	}
            if ( file_exists( plugin_dir_path( __FILE__ ).'/templates/vibebp.php' ) ) {
                return plugin_dir_path( __FILE__ ).'/templates/vibebp.php';
            }
        }

        return $single;

    }

	function post_types(){

		/* register Post types */

		register_post_type( 'member-profile',
			array(
				'labels' => array(
					'name' => __('Member Profiles','vibebp'),
					'menu_name' => __('Member Profiles','vibebp'),
					'singular_name' => __('Profile Layout','vibebp'),
					'add_new_item' => __('Add New Profile Layout','vibebp'),
					'all_items' => __('Member Profiles','vibebp')
				),
				'public' => true,
				'show_in_rest' => false,
				'publicly_queryable' => true,
				'show_ui' => true,
				'capability_type' => 'page',
				'exclude_from_search'=>true,
	            'has_archive' => false,
				'show_in_menu' => 'vibebp',
				'show_in_admin_bar' => false,
				'show_in_nav_menus' => false,
				'supports' => array( 'title','editor','custom-fields'),
				'hierarchical' => false,
			)
		);

		register_post_type( 'member-card',
			array(
				'labels' => array(
					'name' => __('Member Cards','vibebp'),
					'menu_name' => __('Member Card','vibebp'),
					'singular_name' => __('Profile Card','vibebp'),
					'add_new_item' => __('Add New Profile Card','vibebp'),
					'all_items' => __('Member Card','vibebp')
				),
				'public' => true,
				'show_in_rest' => false,
				'publicly_queryable' => true,
				'show_ui' => true,
				'capability_type' => 'page',
				'exclude_from_search'=>true,
	            'has_archive' => false,
				'show_in_menu' => 'vibebp',
				'show_in_admin_bar' => false,
				'show_in_nav_menus' => false,
				'menu_position'=>100,
				'supports' => array( 'title','editor','custom-fields'),
				'hierarchical' => false,
			)
		);

		if(function_exists('bp_is_active') && bp_is_active('groups')){
			register_post_type( 'group-layout',
				array(
					'labels' => array(
						'name' => __('Group Layouts','vibebp'),
						'menu_name' => __('Group Layouts','vibebp'),
						'singular_name' => __('Group Layout','vibebp'),
						'add_new_item' => __('Add New Group Layout','vibebp'),
						'all_items' => __('Group Layouts','vibebp')
					),
					'public' => true,
					'show_in_rest' => true,
					'publicly_queryable' => true,
					'show_ui' => true,
					'capability_type' => 'page',
					'exclude_from_search'=>true,
		            'has_archive' => false,
					'show_in_menu' => 'vibebp',
					'show_in_admin_bar' => false,
					'show_in_nav_menus' => false,
					'supports' => array( 'title','editor','custom-fields'),
					'hierarchical' => false,
				)
			);

			register_post_type( 'group-card',
				array(
					'labels' => array(
						'name' => __('Group Card','vibebp'),
						'menu_name' => __('Group Card','vibebp'),
						'singular_name' => __('Group Card','vibebp'),
						'add_new_item' => __('Add New Group Card','vibebp'),
						'all_items' => __('Group Card','vibebp')
					),
					'public' => true,
					'show_in_rest' => true,
					'publicly_queryable' => true,
					'show_ui' => true,
					'capability_type' => 'page',
					'exclude_from_search'=>true,
		            'has_archive' => false,
					'show_in_menu' => 'vibebp',
					'show_in_admin_bar' => false,
					'show_in_nav_menus' => false,
					'supports' => array( 'title','editor','custom-fields'),
					'hierarchical' => false,
				)
			);
		}

		flush_rewrite_rules();
			
	}
	

}

VibeBP_PostTypes::init();
