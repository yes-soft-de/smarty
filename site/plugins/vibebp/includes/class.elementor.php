<?php
/**
 * Initialise Elementor plugin
 *
 * @class       VibeBP_Init
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class VibeBP_Elementor_Init{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Elementor_Init();
        return self::$instance;
    }

	private function __construct(){
		
		add_post_type_support('member-profile','elementor');
		add_post_type_support('member-card','elementor');
		add_post_type_support('group-card','elementor');
		add_post_type_support('group-layout','elementor');    
		add_action( 'elementor/elements/categories_registered', array($this,'add_elementor_widget_categories' ));
		add_action( 'elementor/editor/before_enqueue_scripts', [$this, 'enqueue_font']);
	}

	function add_elementor_widget_categories( $elements_manager ) {
			$elements_manager->add_category(
				'vibebp',
				[
					'title' => __( 'Vibe BuddyPress', 'vibebp' ),
					'icon' => 'dashicons dashicons-groups',
				]
			);
	}

	function enqueue_font(){
		wp_enqueue_style('vicons',plugins_url('../assets/vicons.css',__FILE__),array(),VIBEBP_VERSION);
	}

}
VibeBP_Elementor_Init::init();


		
final class VibeBP_Elementor_Extension {


	const VERSION = '1.0.0';

	const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

	const MINIMUM_PHP_VERSION = '5.6';

	private static $_instance = null;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {

		add_action( 'init', [ $this, 'i18n' ] );
		add_action( 'plugins_loaded', [ $this, 'init' ] );

	}


	public function i18n() {

		load_plugin_textdomain( 'vibebp' );

	}


	public function init() {



		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
		}

		// Add Plugin actions
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
		add_action( 'elementor/controls/controls_registered', [ $this, 'init_controls' ] );
		
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'widget_scripts' ] );
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'widget_styles' ] );



	}


	public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'vibebp' ),
			'<strong>' . esc_html__( 'VibebBp Elementor Extension', 'vibebp' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'vibebp' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}


	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'vibebp' ),
			'<strong>' . esc_html__( 'VibebBp Elementor Extension', 'vibebp' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'vibebp' ) . '</strong>',
			 self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'vibebp' ),
			'<strong>' . esc_html__( 'VibeBP Elementor Extension', 'vibebp' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'vibebp' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}


	public function init_widgets() {



		global $post;

		if($post->post_type === 'page'){
			//if($post->ID == vibe_get_bp_page_id('members')){
				
				//Directory
				require_once( __DIR__ . '/elementor/directory/members.php' );
				\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Members_Directory());
			//}
			//if($post->ID == vibe_get_bp_page_id('groups')){
				require_once( __DIR__ . '/elementor/directory/groups.php' );
				\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Groups_Directory() );
			//}
		}

		if($post->post_type == 'member-profile' || $post->post_type == 'member-card'){
			require_once( __DIR__ . '/elementor/directory/members.php' );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Members_Directory());
			require_once( __DIR__ . '/elementor/directory/groups.php' );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Groups_Directory() );
			
			//Profile 
			// Include Widget files
			require_once( __DIR__ . '/elementor/profile/avatar.php' );
			require_once( __DIR__ . '/elementor/profile/field.php' );
			require_once( __DIR__ . '/elementor/profile/friends.php' );
			require_once( __DIR__ . '/elementor/profile/groups.php' );
			require_once( __DIR__ . '/elementor/profile/data.php' );

			
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Avatar());
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Field() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Friends() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Groups() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Profile_Data() );
		}

		if($post->post_type == 'group-layout' || $post->post_type == 'group-card'){	


			//Group
			require_once( __DIR__ . '/elementor/groups/avatar.php' );
			require_once( __DIR__ . '/elementor/groups/title.php' );
			require_once( __DIR__ . '/elementor/groups/description.php' );
			require_once( __DIR__ . '/elementor/groups/members.php' ); 
			require_once( __DIR__ . '/elementor/groups/data.php' ); 

			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Groups_Avatar());
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Groups_Title() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Groups_Description() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Groups_Members() );
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VibeBP_Group_Data() );
		}

		require_once( __DIR__ . '/elementor/class.carousel.php' );
			
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Vibe_Carousel() );
		
		require_once( __DIR__ . '/elementor/class.grid.php' );
			
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Vibe_Grid() );
	}


	public function init_controls() {

		// Include Control files
		require_once( __DIR__ . '/elementor/controls/grid_control/class.grid.php' );

		// Register control
		\Elementor\Plugin::$instance->controls_manager->register_control( 'grid_control', new \VibeappGrid_Control() );

	}

	function widget_scripts(){
		wp_register_script('flatpickr',plugins_url('../assets/js/flatpickr.min.js',__FILE__),array(),VIBEBP_VERSION,true);
		wp_register_script('vibebp-members-directory-js',plugins_url('../assets/js/members.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION,true);

	}

	function widget_styles(){
		wp_register_style('vicons',plugins_url('../assets/vicons.css',__FILE__),array(),VIBEBP_VERSION);
		wp_register_style('vibebp-front',plugins_url('../assets/css/front.css',__FILE__),array(),VIBEBP_VERSION);	
	}

}

VibeBP_Elementor_Extension::instance();