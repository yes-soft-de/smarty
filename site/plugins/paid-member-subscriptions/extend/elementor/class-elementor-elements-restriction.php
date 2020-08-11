<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Controls_Manager;

class PMS_Elementor_Content_Restriction extends PMS_Elementor {
	protected function content_restriction() {
		// Setup controls
		$this->register_controls();

		// Filter widget content
		add_filter( 'elementor/widget/render_content', array( $this, 'widget_render' ), 10, 2 );

		// Filter sections display & add custom messages
		add_action('elementor/frontend/section/should_render', array( $this, 'section_render' ), 10, 2 );
		add_action('elementor/frontend/section/after_render', array( $this, 'section_custom_messages' ), 10, 2 );

		// Filter elementor the_content hook
		add_action( 'elementor/frontend/the_content', array( $this, 'filter_elementor_templates' ), 20 );
	}

	// Register controls to sections and widgets
	protected function register_controls() {
		foreach( $this->locations as $where )
			add_action('elementor/element/'.$where['element'].'/'.$this->section_name.'/before_section_end', array( $this, 'add_controls' ), 10, 2 );
	}

	// Define controls
	public function add_controls( $element, $args ) {
		$element_type = $element->get_type();

		$element->add_control(
			'pms_restriction_loggedin_users', array(
				'label'       => __( 'Restrict to logged in users', 'paid-member-subscriptions' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Allow only logged in users to see this content.', 'paid-member-subscriptions' ),
			)
		);

		$element->add_control(
			'pms_restriction_subscription_plans_heading', array(
				'label'     => __( 'Restrict by Subscription Plans', 'paid-member-subscriptions' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$element->add_control(
            'pms_restriction_subscription_plans', array(
                'type'        => Controls_Manager::SELECT2,
                'options'     => pms_get_subscription_plans_list(),
                'multiple'    => 'true',
				'label_block' => 'true',
				'description' => __( 'Allow only members of the selected plans to see this content.', 'paid-member-subscriptions' ),
            )
        );

		$element->add_control(
			'pms_restriction_custom_messages_heading', array(
				'label'     => __( 'Restriction Messages', 'paid-member-subscriptions' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$element->add_control(
			'pms_restriction_default_messages', array(
				'label'       => __( 'Enable Restriction Messages', 'paid-member-subscriptions' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Replace hidden content with the default messages from PMS -> Settings -> Content Restriction, a custom message or an Elementor Template.', 'paid-member-subscriptions' ),
			)
		);

		$element->add_control(
			'pms_restriction_custom_messages', array(
				'label'       => __( 'Enable Custom Messages', 'paid-member-subscriptions' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Add a custom message or template.', 'paid-member-subscriptions' ),
				'condition'   => array(
					'pms_restriction_default_messages' => 'yes'
				)
			)
		);

		$element->add_control(
			'pms_restriction_custom_messages_type', array(
				'label'   => __( 'Content type', 'paid-member-subscriptions' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'text' => array(
						'title' => __( 'Text', 'paid-member-subscriptions' ),
						'icon'  => 'fa fa-align-left',
					),
					'template' => array(
						'title' => __( 'Template', 'paid-member-subscriptions' ),
						'icon'  => 'fa fa-th-large',
					)
				),
				'default'   => 'text',
				'condition' => array(
					'pms_restriction_default_messages' => 'yes',
					'pms_restriction_custom_messages'  => 'yes'
				),
			)
		);

		//DCE_HELPER::get_all_template()
		$element->add_control(
			'pms_restriction_fallback_template', array(
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_elementor_templates(),
				'label'       => __( 'Select Template', 'paid-member-subscriptions' ),
				'default'     => '',
				'label_block' => 'true',
				'condition'   => array(
					'pms_restriction_default_messages'     => 'yes',
					'pms_restriction_custom_messages'      => 'yes',
					'pms_restriction_custom_messages_type' => 'template'
				),
			)
		);

		$element->add_control(
			'pms_restriction_fallback_text', array(
				'type'        => Controls_Manager::WYSIWYG,
				'default'     => 'You need to register before accessing this content.',
				'condition'   => array(
					'pms_restriction_default_messages'     => 'yes',
					'pms_restriction_custom_messages'      => 'yes',
					'pms_restriction_custom_messages_type' => 'text'
				),
			)
		);

	}

	// Verifies is element is hidden
	public function is_hidden( $element ) {
		$settings = $element->get_settings();

		if( !empty( $settings['pms_restriction_subscription_plans'] ) && is_user_logged_in() ) {

			if( pms_is_member_of_plan( $settings['pms_restriction_subscription_plans'] ) || current_user_can( 'manage_options' ) )
				return false;
			else
				return true;

		} else if ( !is_user_logged_in() && (
					( $settings['pms_restriction_loggedin_users'] == 'yes' ) || ( !empty( $settings['pms_restriction_subscription_plans'] ) )
				) ) {

			return true;
		}

		return false;
	}

	// Retrieves custom element message or the default message from PMS settings
	private function get_custom_message( $element ) {
		$settings = $element->get_settings();

		if( $settings['pms_restriction_default_messages'] != 'yes' )
			return false;

		if( $settings['pms_restriction_custom_messages'] == 'yes' ) {

			if( $settings['pms_restriction_custom_messages_type'] == 'text' )
				return $settings['pms_restriction_fallback_text'];
			elseif( $settings['pms_restriction_custom_messages_type'] == 'template' ) {
				return $this->render_template( $settings['pms_restriction_fallback_template'] );
			}
		} else {
			if( is_user_logged_in() )
				return pms_get_restriction_content_message( 'non_members' );
			else
				return pms_get_restriction_content_message( 'logged_out' );
		}
	}

	// Widget display & custom messages
	public function widget_render( $content, $widget ) {
		if( $this->is_hidden( $widget ) ) {

			if( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				$widget->add_render_attribute( '_wrapper', 'class', 'pms-visibility-hidden' );

				return $content;
			}

			if( $message = $this->get_custom_message( $widget ) ) {
				return $message;
			}

			return '<style>' . $widget->get_unique_selector() . '{ display: none; }</style>';
		}

		return $content;
	}

	// Section display
	public function section_render( $should_render, $element ) {
		if( $this->is_hidden( $element ) === true )
			return false;

		return $should_render;
	}

	// Section custom messages
	public function section_custom_messages( $element ) {
		if( $this->is_hidden( $element ) && ( $message = $this->get_custom_message( $element ) ) ) {

			$element->add_render_attribute(
				'_wrapper', 'class', array(
					'elementor-section',
				)
			);

			$element->before_render();
				echo $message;
			$element->after_render();
		}
	}

	// Render an Elementor template based on ID
	// Based on Elementor Pro template shortcode
	public function render_template( $id ) {
		return Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $id, true );
	}

	// Retrieve defined Elementor templates
	private function get_elementor_templates() {
		$templates = array();

		foreach( \Elementor\Plugin::instance()->templates_manager->get_source('local')->get_items() as $template ) {
			$templates[$template['template_id']] = $template['title'] . ' (' . $template['type'] . ')';
		}

		return $templates;
	}

	public function filter_elementor_templates( $content ){
		$document = \Elementor\Plugin::$instance->documents->get_current();

		return pms_filter_content( $content, $document->get_post() );
	}
}

new PMS_Elementor_Content_Restriction;
