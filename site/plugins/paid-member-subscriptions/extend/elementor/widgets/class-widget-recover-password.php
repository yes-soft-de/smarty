<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Elementor widget for our pms-account shortcode
 */
class PMS_Elementor_Recover_Password_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 */
	public function get_name() {
		return 'pms-recover-password';
	}

	/**
	 * Get widget title.
	 *
	 */
	public function get_title() {
		return __( 'Recover Password', 'paid-member-subscriptions' );
	}

	/**
	 * Get widget icon.
	 *
	 */
	public function get_icon() {
		return 'eicon-shortcode';
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
			'pms_after_recovery_redirect_url',
			array(
				'label'      => __( 'After recovery', 'paid-member-subscriptions' ),
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

		echo do_shortcode( '[pms-recover-password redirect_url='.$settings['pms_after_recovery_redirect_url'].']');

	}

}
