<?php
/**
 * Initialise plugin
 *
 * @class       VibeBp_Templates
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIBEBP_TEMPLATES{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VIBEBP_TEMPLATES();
        return self::$instance;
    }

	private function __construct(){
		
		add_action( 'bp_register_theme_packages',array($this, 'template_package') );
		
		//add_filter('bp_get_template_stack',array($this,'template_stack'));
		//add_filter('bp_add_template_stack_locations',array($this,'template_stack'));
	}

	 
	// replace member-header.php with the template overload from the plugin
	function bp_tol_maybe_replace_template( $templates, $slug, $name ) {

		//handling slugs and loading our custom templates : 
		//would check specific member also for custom templates for each member

		switch ($slug) {
			case 'members/single/home':
				$templates = array( 'members/single/index.php' );
				break;
			
			case 'members/index':
				$templates = array( 'members/index.php' );
				break;

			default:
				$templates = array( 'members/single/index.php' );
				break;
		}

	    return $templates;
	}
	 
	 
	function template_package() {

		bp_register_theme_package( array(
			'id'      => 'vibebp',
			'name'    => __( 'Vibe BP', 'vibebp' ),
			'version' => VIBEBP_VERSION,
			'dir'     => trailingslashit(dirname(__FILE__) . '../templates' ),
			'url'     => trailingslashit( plugins_url() . '/vibebp/templates' )
		) );


        bp_register_template_stack('vibebp_get_template',5);
	     
	    // if viewing a member page, overload the template
	    //if ( bp_is_user()  ) 
	    //add_filter( 'bp_get_template_part', array($this,'template_stack') ,10, 3 );
	     
	}

	function template_stack($stack){

		return array_unshift($stack, vibebp_get_template());
	}

}
VIBEBP_TEMPLATES::init();


function vibebp_get_template($template = null){
	return plugin_dir_path(__FILE__).'../templates/'.$template ;
}