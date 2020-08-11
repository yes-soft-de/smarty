<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


Class PMS_Plugin_Notifications {

	/**
	 *
	 *
	 */
	public $notifications = array();


	/**
	 *
	 *
	 */
	private static $_instance = null;


	/**
	 *
	 *
	 */
	protected function __construct() {}


	/**
	 *
	 *
	 */
	public static function get_instance() {

		if( is_null( self::$_instance ) )
			self::$_instance = new PMS_Plugin_Notifications();

		return self::$_instance;

	}


	/**
	 *
	 *
	 */
	public function add_notification( $notification_id = '', $notification_message = '', $notification_class = 'update-nag', $count_in_menu = true, $count_in_submenu = array() ) {

		if( empty( $notification_id ) )
			return;

		if( empty( $notification_message ) )
			return;

		global $current_user;

		if( get_user_meta( $current_user->ID, $notification_id . '_dismiss_notification' ) )
			return;

		$this->notifications[$notification_id] = array(
			'id' 	  		   => $notification_id,
			'message' 		   => $notification_message,
			'class'   		   => $notification_class,
			'count_in_menu'    => $count_in_menu,
			'count_in_submenu' => $count_in_submenu
		);


		if( $this->is_plugin_page() ) {
			new PMS_Add_General_Notices( $notification_id, $notification_message, $notification_class );
		}

	}


	/**
	 *
	 *
	 */
	public function get_notifications() {

		return $this->notifications;

	}


	/**
	 *
	 *
	 */
	public function get_notification( $notification_id = '' ) {

		if( empty( $notification_id ) )
			return null;

		$notifications = $this->get_notifications();

		if( ! empty( $notifications[$notification_id] ) )
			return $notifications[$notification_id];
		else
			return null;

	}


	/**
	 *
	 *
	 */
	public function dismiss_notification( $notification_id = '' ) {

		global $current_user;

        add_user_meta( $current_user->ID, $notification_id . '_dismiss_notification', 'true', true );

	}


	/**
	 *
	 *
	 */
	public function get_count_in_menu() {

		$count = 0;

		foreach( $this->notifications as $notification ) {

			if( ! empty( $notification['count_in_menu'] ) )
				$count++;

		}

		return $count;

	}


	/**
	 *
	 *
	 */
	public function get_count_in_submenu( $submenu = '' ) {

		if( empty( $submenu ) )
			return 0;

		$count = 0;

		foreach( $this->notifications as $notification ) {

			if( empty( $notification['count_in_submenu'] ) )
				continue;

			if( ! is_array( $notification['count_in_submenu'] ) )
				continue;

			if( ! in_array( $submenu, $notification['count_in_submenu'] ) )
				continue;

			$count++;

		}

		return $count;

	}


	/**
	 *
	 *
	 */
	public function is_plugin_page() {

		if( ! empty( $_GET['page'] ) && false !== strpos( $_GET['page'], 'pms-' ) )
			return true;

		if( ! empty( $_GET['post_type'] ) && false !== strpos( $_GET['post_type'], 'pms-' ) )
			return true;

		if( ! empty( $_GET['post'] ) && false !== strpos( get_post_type( (int)$_GET['post'] ), 'pms-' ) )
			return true;

		return false;

	}

}