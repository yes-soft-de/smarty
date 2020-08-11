<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Elementor widget for our pms-account shortcode
 */
class PMS_Elementor_Login_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 */
	public function get_name() {
		return 'pms-login';
	}

	/**
	 * Get widget title.
	 *
	 */
	public function get_title() {
		return __( 'Login', 'paid-member-subscriptions' );
	}

	/**
	 * Get widget icon.
	 *
	 */
	public function get_icon() {
		return 'eicon-lock-user';
	}

	/**
	 * Get widget categories.
	 *
	 */
	public function get_categories() {
		return array( 'paid-member-subscriptions' );
	}

	/**
	 * Register widget controls
	 *
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'pms_content_section',
			array(
				'label' => __( 'Redirects', 'paid-member-subscriptions' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'pms_after_login_redirect_url',
			array(
				'label'      => __( 'After login', 'paid-member-subscriptions' ),
				'type'       => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'Enter URL', 'paid-member-subscriptions' ),
			)
		);

		$this->add_control(
			'pms_after_logout_redirect_url',
			array(
				'label'      => __( 'After logout', 'paid-member-subscriptions' ),
				'type'       => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'Enter URL', 'paid-member-subscriptions' ),
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Render widget output in the front-end
	 *
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		echo do_shortcode( '[pms-login redirect_url='.$settings['pms_after_login_redirect_url'].' logout_redirect_url='.$settings['pms_after_logout_redirect_url'].']');

	}

}
