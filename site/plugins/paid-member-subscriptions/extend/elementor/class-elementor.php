<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Controls_Manager;

class PMS_Elementor {
    private static $_instance = null;
    public $locations = array(
        array(
            'element' => 'common',
            'action'  => '_section_style',
        ),
        array(
            'element' => 'section',
            'action'  => 'section_advanced',
        )
    );
    public $section_name = 'pms_section_visibility_settings';

	/**
	 * Register plugin action hooks and filters
	 */
	public function __construct() {
        // Add category
        add_action( 'elementor/elements/categories_registered', array( $this, 'add_category' ) );

		// Register widgets
		add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ) );

        // Load Elements restriction class
        require_once( __DIR__ . '/class-elementor-elements-restriction.php' );

        // Register new section to display restriction controls
        $this->register_sections();

        $this->content_restriction();
	}

    /**
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @return PMS_Elementor An instance of the class.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) )
            self::$_instance = new self();

        return self::$_instance;
    }

	/**
	 * Include Widgets files
	 */
	private function include_widgets_files() {
		require_once( __DIR__ . '/widgets/class-widget-account.php' );
		require_once( __DIR__ . '/widgets/class-widget-login.php' );
		require_once( __DIR__ . '/widgets/class-widget-recover-password.php' );
		require_once( __DIR__ . '/widgets/class-widget-register.php' );
	}

	/**
	 * Register Widgets
	 */
	public function register_widgets() {
		$this->include_widgets_files();

		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new PMS_Elementor_Account_Widget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new PMS_Elementor_Login_Widget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new PMS_Elementor_Recover_Password_Widget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new PMS_Elementor_Register_Widget() );
	}

    private function register_sections() {
        foreach( $this->locations as $where ) {
            add_action( 'elementor/element/'.$where['element'].'/'.$where['action'].'/after_section_end', array( $this, 'add_section' ), 10, 2 );
        }
    }

    public function add_category( $elements_manager ) {
        $elements_manager->add_category(
            'paid-member-subscriptions',
            array(
                'title' => __( 'Paid Member Subscriptions Shortcodes', 'paid-member-subscriptions' ),
                'icon'  => 'fa fa-plug',
            )
        );
    }

    public function add_section( $element, $args ) {
        $exists = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), $this->section_name );

        if( !is_wp_error( $exists ) )
            return false;

        $element->start_controls_section(
            $this->section_name, array(
                'tab'   => Controls_Manager::TAB_ADVANCED,
                'label' => __( 'Content Restriction', 'paid-member-subscriptions' )
            )
        );

        $element->end_controls_section();
    }

    protected function content_restriction(){}
}

// Instantiate Plugin Class
PMS_Elementor::instance();
