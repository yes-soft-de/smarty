<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


class PMS_Plugin_Usage_Tracker {

	private $data;

	public function init() {

		add_action( 'admin_init',              array( $this, 'register_admin_notice' ) );
		add_action( 'admin_init',              array( $this, 'allow_deny_tracking' ) );

		if( $this->is_tracking_allowed() ){
			add_filter( 'cron_schedules',          array( $this, 'custom_cron_schedule') );
			add_action( 'init',                    array( $this, 'schedule_event' ) );
			add_action( 'pms_usage_tracker_event', array( $this, 'send_usage_data' ) );
		} else if( wp_next_scheduled ( 'pms_usage_tracker_event' ) )
			wp_clear_scheduled_hook( 'pms_usage_tracker_event' );

		//add_action( 'init', array( $this, 'send_usage_data' ) );

	}


	/**
	 * Schedules a daily cron event for sending plugin usage data
	 *
	 */
	public function schedule_event() {

		if( ! wp_next_scheduled ( 'pms_usage_tracker_event' ) )
			wp_schedule_event( time(), 'weekly', 'pms_usage_tracker_event' );

	}


	/**
	 * Checks if plugin usage tracking is allowed
	 *
	 * @return bool
	 *
	 */
	public function is_tracking_allowed() {

		$settings = get_option( 'pms_misc_settings', false );

		if( isset( $settings['allow-usage-tracking'] ) && $settings['allow-usage-tracking'] == 1 )
			return true;

		return false;

	}


	/**
	 * Sends the plugin's usage data to our server
	 *
	 */
	public function send_usage_data() {

		if( ! $this->is_tracking_allowed() )
			return;

		$this->setup_data();

		$data                = $this->data;
		$data['import-data'] = true;
		$data['target']      = 'pms';

		$request = wp_remote_post( 'https://usagetracker.cozmoslabs.com', array( 'body' => $data ) );

		// look for errors ?
		// echo wp_remote_retrieve_body( $request );
		// die();

	}

	/**
	 * Prepares the data that we want to collect
	 *
	 */
	private function setup_data() {

		$data = array();

		$data['site_url']        = home_url();
		$data['php_version']     = $this->get_php_version();
		$data['wp_version']      = get_bloginfo( 'version' );
		$data['wp_locale']       = get_locale();
		$data['plugin_version']  = PMS_VERSION;
		$data['product_version'] = pms_get_product_version();

		$active_theme            = wp_get_theme();

		if( $active_theme->exists() ){
			$data['theme'] = array(
				'name'       => $active_theme->get( 'Name' ),
				'textdomain' => $active_theme->get( 'TextDomain' ),
				'url'        => $active_theme->get( 'ThemeURI' ),
			);
		}

		if( ! function_exists( 'get_plugins' ) )
			include ABSPATH . '/wp-admin/includes/plugin.php';

		$data['installed_plugins']  = array_keys( get_plugins() );
		$data['active_plugins']     = get_option( 'active_plugins', array() );
		$data['add_ons']            = $this->get_add_ons_data();
		$data['settings']           = $this->get_settings();
		$data['integrations']       = $this->get_integrations();
		$data['statistics']         = $this->get_statistics();
		$data['subscription_plans'] = pms_get_subscription_plans();

		$this->data = $data;

	}

	/**
	 * Returns an array with add-ons data
	 *
	 * @return array
	 *
	 */
	private function get_add_ons_data() {

		$plugins = get_plugins();
		$add_ons = array();

		foreach( $plugins as $plugin_slug => $plugin_details ) {

			if( strpos( $plugin_slug, 'pms-add-on-' ) === 0 ) {

				$add_ons[] = array(
					'slug'    => str_replace( 'pms-add-on-', '', str_replace( '/index.php', '', $plugin_slug ) ),
					'version' => $plugin_details['Version'],
					'active'  => $this->is_addon_active( $plugin_slug )
				);

			}

		}

		return $add_ons;

	}

	private function is_addon_active( $slug ){

		if( !is_plugin_active( $slug ) )
			return false;

		$clean_plugin_slug = str_replace( 'pms-add-on-', '', str_replace( '/index.php', '', $slug ) );

		switch( $clean_plugin_slug ){

			case 'bbpress':

				if( is_plugin_active( 'bbpress/bbpress.php' ) )
					return true;
				else
					return false;

			case 'content-dripping':

				return $this->are_posts_defined( 'pms-content-dripping' );

			case 'discount-codes';

				return $this->are_posts_defined( 'pms-discount-codes' );

			case 'email-reminders':

				return $this->are_posts_defined( 'pms-email-reminders' );

				break;

			case 'global-content-restriction':

				foreach( pms_get_subscription_plans( true ) as $plan ) {
					$gcr_rules = get_post_meta( $plan->id, 'pms_nr_of_rules', true );

					if( !empty( $gcr_rules ) )
						return true;
				}

				return false;

			case 'group-memberships':

				foreach( pms_get_subscription_plans( true ) as $plan ) {
					if( $plan->type == 'group' )
						return true;
				}

				return false;

			case 'invoices':

				$invoices_settings = get_option( 'pms_invoices_settings', array() );

				if( !empty( $invoices_settings['company_details'] ) )
					return true;
				else
					return false;

			case 'labels-edit';

				$strings = get_option( 'pmsle_backup', false );

				if ( !empty( $strings ) )
					return true;
				else
					return false;

			case 'member-subscription-fixed-period':

				foreach( pms_get_subscription_plans( true ) as $plan ) {
					if( $plan->type == 'fixed-period' )
						return true;
				}

				return false;

			case 'pay-what-you-want':

				foreach( pms_get_subscription_plans( true ) as $plan ) {

					$pwyw_enabled = get_post_meta( $plan->id, 'pms_subscription_plan_pay_what_you_want', true );

					if( $pwyw_enabled == '1' )
						return true;

				}

				return false;

			case 'paypal-standard-recurring-payments':

				$active_gateways = pms_get_active_payment_gateways();

				if( in_array( 'paypal_standard', $active_gateways ) )
					return true;
				else
					return false;

			case 'paypal-express-pro':

				$active_gateways = pms_get_active_payment_gateways();

				if( in_array( 'paypal_express', $active_gateways ) )
					return true;
				else
					return false;

			case 'stripe':

				$active_gateways = pms_get_active_payment_gateways();

				if( in_array( 'stripe_intents', $active_gateways ) || in_array( 'stripe', $active_gateways ) )
					return true;
				else
					return false;

			case 'tax':

				if( function_exists( 'pms_tax_enabled' ) )
					return pms_tax_enabled();

				break;

		}

		return true;
	}

	/**
	 * Register an admin notice to ask tracking permissions
	 *
	 */
	public function register_admin_notice() {

		if( ! current_user_can( 'manage_options' ) )
			return;

		if( $this->is_tracking_allowed() )
			return;

		$notice = get_option( 'pms_admin_notice_usage_tracking', false );

		if( $notice == '1' )
			return;

		$message = '<strong>' . __( 'Help us improve Paid Member Subscriptions', 'paid-member-subscriptions' ) . '</strong>';
		$message .= __( 'Allow Paid Member Subscriptions to anonymously track the plugin\'s usage. Data provided by this tracking helps us improve the plugin.<br>', 'paid-member-subscriptions' );
		$message .= sprintf( __( 'No sensitive data is shared. %sLearn More%s', 'paid-member-subscriptions' ) . '<br>', '<a href="https://www.cozmoslabs.com/docs/paid-member-subscriptions/usage-tracking/" target="_blank">', '</a>' );
		$message .= '<a href="' .wp_nonce_url( add_query_arg( 'pms_action', 'allow_tracking' ), 'pms_admin_notice_allow_tracking', 'pmstkn' ). '" class="button-primary">Allow Tracking</a> <a href="' .wp_nonce_url( add_query_arg( 'pms_action', 'deny_tracking' ), 'pms_admin_notice_deny_tracking', 'pmstkn' ). '" class="button">Don\'t allow</a>';

        new PMS_Add_General_Notices( 'pms_usage_tracking',
            $message,
            'updated' );

	}

	public function allow_deny_tracking(){

		if( empty( $_REQUEST['pmstkn'] ) || !isset( $_REQUEST['pms_action'] ) || !in_array( $_REQUEST['pms_action'], array( 'allow_tracking', 'deny_tracking' ) ) )
			return;

		if( wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_admin_notice_allow_tracking' ) ){

			update_option( 'pms_admin_notice_usage_tracking', '1' );

			$settings = get_option( 'pms_misc_settings', false );

			if( $settings !== false ){

				$settings['allow-usage-tracking'] = 1;
				update_option( 'pms_misc_settings', $settings );

			}

		} else if( wp_verify_nonce( $_REQUEST['pmstkn'], 'pms_admin_notice_deny_tracking' ) ){

			update_option( 'pms_admin_notice_usage_tracking', '1' );

			$settings = get_option( 'pms_misc_settings', false );

			if( $settings !== false ){

				unset( $settings['allow-usage-tracking'] );
				update_option( 'pms_misc_settings', $settings );

			}

		}

		wp_redirect( remove_query_arg( array( 'pms_action', 'pmstkn' ) ) );
		exit;

	}

	public function custom_cron_schedule( $schedules ){

		$schedules['weekly'] = array(
			'interval' => 60 * 60 * 24 * 7, // week in seconds
			'display' => __( 'Weekly', 'paid-member-subscriptions' )
		);

		return $schedules;

	}

	private function are_posts_defined( $post_type ){

		$query = new \WP_Query( array(
		    'post_type' => $post_type,
		) );

		return $query->have_posts();

	}

	private function get_settings(){

		$settings = array();

		$general_settings = get_option( 'pms_general_settings', false );

		if( $general_settings !== false ){

			// Remove page settings from general
			foreach( $general_settings as $key => $value ){
				if( strpos( $key, '_page' ) !== false || $value == 0 )
					unset( $general_settings[$key] );
			}

			$settings['general'] = $general_settings;

		}

		$payments_settings = get_option( 'pms_payments_settings', false );

		if( $payments_settings !== false ){

			unset( $payments_settings['gateways'] );
			unset( $payments_settings['test_mode'] );

			$settings['payments'] = $payments_settings;

		}

		$cr_settings = get_option( 'pms_content_restriction_settings', false );

		if( $cr_settings !== false ){

			if( isset( $cr_settings['content_restrict_type'] ) )
				$settings['content-restriction']['content_restrict_type'] = $cr_settings['content_restrict_type'];

			if( isset( $cr_settings['comments_restriction'], $cr_settings['comments_restriction']['option'] ) && $cr_settings['comments_restriction']['option'] != 'off' )
				$settings['content-restriction']['comments_restriction'] = 1;

			if( isset( $cr_settings['restricted_post_preview'], $cr_settings['restricted_post_preview']['option'] ) && $cr_settings['restricted_post_preview']['option'] != 'none' )
				$settings['content-restriction']['restricted_post_preview'] = 1;

		}

		$misc_settings = get_option( 'pms_misc_settings', false );

		if( $misc_settings !== false ){

			if( isset( $misc_settings['gdpr'], $misc_settings['gdpr']['gdpr_checkbox'] ) && $misc_settings['gdpr']['gdpr_checkbox'] == 'enabled' )
				$settings['misc']['gdpr'] = 1;

			if( isset( $misc_settings['recaptcha'], $misc_settings['recaptcha']['display_form'] )
				&& !empty( $misc_settings['recaptcha']['display_form'] ) && !empty( $misc_settings['recaptcha']['site_key'] )
				&& !empty( $misc_settings['recaptcha']['secret_key'] ) ){

				$settings['misc']['recaptcha'] = 1;

			}

			if( isset( $misc_settings['hide-admin-bar'] ) && $misc_settings['hide-admin-bar'] == '1' )
				$settings['misc']['hide-admin-bar'] = 1;

		}

		$woo_settings = get_option( 'pms_woocommerce_settings', false );

		if( $woo_settings !== false ){

			if( isset( $woo_settings['product_discounted_message'] ) )
				unset( $woo_settings['product_discounted_message'] );

			$settings['woocommerce'] = $woo_settings;

		}

		$tax_settings = get_option( 'pms_tax_settings', false );

		if( $tax_settings !== false ){

			if( isset( $tax_settings['merchant-vat-country'] ) )
				unset( $tax_settings['merchant-vat-country'] );

			$settings['tax'] = $tax_settings;

		}

		$invoices_settings = get_option( 'pms_invoices_settings', false );

		if( $invoices_settings !== false ){

			if( isset( $invoices_settings['reset_yearly'] ) && $invoices_settings['reset_yearly'] == '1' )
				$settings['invoices']['reset_yearly'] = 1;

			if( !empty( $invoices_settings['logo'] ) )
				$settings['invoices']['invoice_logo'] = 1;

		}

		return $settings;

	}

	private function get_integrations(){

		$integrations = array();

		if( did_action( 'elementor/loaded' ) )
			$integrations[] = 'elementor';

		if( is_plugin_active( 'siteorigin-panels/siteorigin-panels.php' ) )
			$integrations[] = 'siteorigin';

		if( defined( 'FL_BUILDER_VERSION' ) )
			$integrations[] = 'beaver-builder';

		if( defined( 'WPB_VC_VERSION' ) )
			$integrations[] = 'wpbakery';

		if( defined( 'PROFILE_BUILDER_VERSION' ) )
			$integrations[] = 'profile-builder';

		if( is_plugin_active( 'woocommerce/woocommerce.php' ) )
			$integrations[] = 'woocommerce';

		if( function_exists( 'mc4wp_get_integration' ) ){

			$mcwp = mc4wp_get_integration( 'paid-member-subscriptions' );

			if( isset( $mcwp->options ) && $mcwp->options['enabled'] == '1' )
				$integrations[] = 'mailchimp-for-wp';

		}

		return $integrations;

	}

	private function get_statistics(){

		$statistics = array();

		$count_users         = count_users();
		$statistics['users'] = $count_users['total_users'];

		$statistics['payments'] = pms_get_payments_count();
		$statistics['members']  = pms_get_members( array(), true );

		$first_payment                    = pms_get_payments( array( 'order' => 'ASC', 'number' => 1 ) );
		$statistics['first_payment_date'] = isset( $first_payment[0] ) && !empty( $first_payment[0]->id ) ? $first_payment[0]->date : '';

		$last_payment                    = pms_get_payments( array( 'number' => 1 ) );
		$statistics['last_payment_date'] = isset( $last_payment[0] ) && !empty( $last_payment[0]->id ) ? $last_payment[0]->date : '';

		return $statistics;

	}

	private function get_php_version(){

		$version = phpversion();

		// remove everything after +
		if( strpos( $version, '+' ) !== false ){
			$version = explode( '+', $version );
			$version = $version[0];
		}

		return $version;

	}

}

$pms_plugin_usage_tracker = new PMS_Plugin_Usage_Tracker();
$pms_plugin_usage_tracker->init();
