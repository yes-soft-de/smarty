<?php
/**
 * Admin Menu Items
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 1.0.0
 * @version 3.37.19
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Menus class
 *
 * @since 1.0.0
 * @since 3.19.0 Added action scheduler posts table.
 * @since 3.35.0 Sanitize input data.
 * @since 3.37.19 Load tools on the status page.
 */
class LLMS_Admin_Menus {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @since 3.19.0 Add action scheduler posts table.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'status_page_actions' ) );
		add_action( 'admin_init', array( $this, 'builder_page_actions' ) );
		add_action( 'load-admin_page_llms-course-builder', array( $this, 'builder_title' ) );

		add_filter( 'custom_menu_order', array( $this, 'submenu_order' ) );
		add_action( 'admin_menu', array( $this, 'display_admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'display_admin_menu_late' ), 7777 );

		// Shame shame shame.
		add_action( 'admin_menu', array( $this, 'instructor_menu_hack' ) );

		add_filter( 'action_scheduler_post_type_args', array( $this, 'action_scheduler_menu' ) );

	}

	/**
	 * If WP_DEBUG is not enabled, expose the schedule-action post type management via direct link
	 *
	 * EG: site.com/wp-admin/edit.php?post_type=scheduled-action
	 *
	 * @since 3.19.0
	 *
	 * @param array $args Default custom post type arguments.
	 * @return array
	 */
	public function action_scheduler_menu( $args ) {

		// If WP_DEBUG is enabled the menu item will already be displayed under "tools.php".
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return $args;
		}

		// Otherwise we'll add a hidden menu accessible via direct link only.
		return array_merge(
			$args,
			array(
				'show_ui'           => true,
				'show_in_menu'      => '',
				'show_in_admin_bar' => false,
			)
		);

	}

	/**
	 * Remove the default menu page from the submenu
	 *
	 * @since 1.0.0
	 * @since 3.2.0 Unknown.
	 *
	 * @param boolean $custom Whether custom menu order is enabled or not.
	 * @return boolean
	 */
	public function submenu_order( $custom ) {

		global $submenu;

		if ( isset( $submenu['lifterlms'] ) ) {
			unset( $submenu['lifterlms'][0] );
		}

		return $custom;

	}


	/**
	 * Handle init actions on the course builder page
	 *
	 * Used for post-locking redirects when taking over from another user
	 * on the course builder page.
	 *
	 * @since 3.13.0
	 * @since 3.16.7 Unknown.
	 *
	 * @return void
	 */
	public function builder_page_actions() {

		if ( ! isset( $_GET['page'] ) || 'llms-course-builder' !== $_GET['page'] ) {
			return;
		}

		if ( ! empty( $_GET['get-post-lock'] ) && ! empty( $_GET['course_id'] ) ) {
			$post_id = absint( $_GET['course_id'] );
			check_admin_referer( 'lock-post_' . $post_id );
			wp_set_post_lock( $post_id );
			wp_redirect(
				add_query_arg(
					array(
						'page'      => 'llms-course-builder',
						'course_id' => $post_id,
					),
					admin_url( 'admin.php' )
				)
			);
			exit();

		}

		add_action( 'admin_bar_menu', array( 'LLMS_Admin_Builder', 'admin_bar_menu' ), 100, 1 );

	}

	/**
	 * Set the global $title variable for the builder
	 *
	 * Prevents the <title> in the admin head being partially empty on builder screen.
	 *
	 * @since 3.14.9
	 *
	 * @return void
	 */
	public function builder_title() {
		global $title;
		$title = __( 'Course Builder', 'lifterlms' );
	}

	/**
	 * Admin Menu
	 *
	 * @since 1.0.0
	 * @since 3.13.0 Unknown.
	 *
	 * @return void
	 */
	public function display_admin_menu() {

		global $menu;

		$menu[51] = array( '', 'read', 'llms-separator', '', 'wp-menu-separator' );

		add_menu_page( 'lifterlms', 'LifterLMS', 'read', 'lifterlms', '__return_empty_string', plugin_dir_url( LLMS_PLUGIN_FILE ) . 'assets/images/lifterlms-icon.png', 51 );

		add_submenu_page( 'lifterlms', __( 'LifterLMS Settings', 'lifterlms' ), __( 'Settings', 'lifterlms' ), 'manage_lifterlms', 'llms-settings', array( $this, 'settings_page_init' ) );

		add_submenu_page( 'lifterlms', __( 'LifterLMS Reporting', 'lifterlms' ), __( 'Reporting', 'lifterlms' ), 'view_lifterlms_reports', 'llms-reporting', array( $this, 'reporting_page_init' ) );

		add_submenu_page( 'lifterlms', __( 'LifterLMS Import', 'lifterlms' ), __( 'Import', 'lifterlms' ), 'manage_lifterlms', 'llms-import', array( $this, 'import_page_init' ) );

		add_submenu_page( 'lifterlms', __( 'LifterLMS Status', 'lifterlms' ), __( 'Status', 'lifterlms' ), 'manage_lifterlms', 'llms-status', array( $this, 'status_page_init' ) );

		add_submenu_page( null, __( 'LifterLMS Course Builder', 'lifterlms' ), __( 'Course Builder', 'lifterlms' ), 'edit_courses', 'llms-course-builder', array( $this, 'builder_init' ) );

	}

	/**
	 * Add items to the admin menu with a later priority
	 *
	 * @since 3.5.0
	 * @since 3.22.0 Unknown.
	 *
	 * @return void
	 */
	public function display_admin_menu_late() {

		/**
		 * Disable the display and output of LifterLMS Add-ons screen.
		 *
		 * @since Unknown
		 *
		 * @param boolean $display Whether or not to display the screen. Defaults to `false` which shows the screen.
		 */
		if ( apply_filters( 'lifterlms_disable_addons_screen', false ) ) {
			return;
		}

		add_submenu_page( 'lifterlms', __( 'LifterLMS Add-ons, Courses, and Resources', 'lifterlms' ), __( 'Add-ons & more', 'lifterlms' ), 'manage_lifterlms', 'llms-add-ons', array( $this, 'add_ons_page_init' ) );

	}

	/**
	 * Output the add-ons screen
	 *
	 * @since 3.5.0
	 * @since 3.22.0
	 *
	 * @return void
	 */
	public function add_ons_page_init() {
		require_once 'class.llms.admin.addons.php';
		$view = new LLMS_Admin_AddOns();
		$view->handle_actions();
		$view->output();
	}

	/**
	 * Output the HTML for the Course Builder
	 *
	 * @since 3.13.0
	 * @since 3.16.0 Unknown.
	 *
	 * @return void
	 */
	public function builder_init() {
		require_once 'class.llms.admin.builder.php';
		LLMS_Admin_Builder::output();
	}

	/**
	 * Outputs the LifterLMS Importer Screen HTML
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function import_page_init() {
		LLMS_Admin_Import::output();
	}

	/**
	 * Removes edit.php from the admin menu for instructors/asst instructors
	 *
	 * Note: The post screen is still technically accessible.
	 *
	 * Posts will need to be submitted for review as the instructors only actually have
	 * the capability of a contributor with regards to posts
	 * but this hack will allow instructors to publish new lessons, quizzes, & questions.
	 *
	 * @since 3.13.0
	 *
	 * @link https://core.trac.wordpress.org/ticket/22895
	 * @link https://core.trac.wordpress.org/ticket/16808
	 *
	 * @return void
	 */
	public function instructor_menu_hack() {
		$user = wp_get_current_user();
		if ( array_intersect( array( 'instructor', 'instructors_assistant' ), $user->roles ) ) {
			remove_menu_page( 'edit.php' );
		}
	}

	/**
	 * Output the HTML for admin settings screens
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	public function settings_page_init() {
		include_once 'class.llms.admin.settings.php';
		LLMS_Admin_Settings::output();
	}

	/**
	 * Output the HTML for the reporting screens
	 *
	 * @since 3.2.0
	 * @since 3.13.0 Unknown.
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return void
	 */
	public function reporting_page_init() {

		if ( isset( $_GET['student_id'] ) && ! llms_current_user_can( 'view_lifterlms_reports', llms_filter_input( INPUT_GET, 'student_id', FILTER_SANITIZE_NUMBER_INT ) ) ) {
			wp_die( __( 'You do not have permission to access this content.', 'lifterlms' ) );
		}

		require_once 'reporting/class.llms.admin.reporting.php';
		$gb = new LLMS_Admin_Reporting();
		$gb->output();

	}

	/**
	 * Include files used on the Status page.
	 *
	 * @since 3.37.19
	 *
	 * @return void
	 */
	protected function status_page_includes() {

		// Main Status Page.
		require_once 'class.llms.admin.page.status.php';

		// Tools.
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/llms-abstract-admin-tool.php';
		foreach ( glob( LLMS_PLUGIN_DIR . 'includes/admin/tools/class-llms-admin-tool-*.php' ) as $tool_path ) {
			require_once $tool_path;
		}

	}

	/**
	 * Handle form submission actions on the status pages
	 *
	 * @since 3.11.2
	 * @since 3.37.19 Load tools-related files.
	 *
	 * @return void
	 */
	public function status_page_actions() {
		$this->status_page_includes();
		LLMS_Admin_Page_Status::handle_actions();
	}

	/**
	 * Output the HTML for the Status Pages
	 *
	 * @since Unknown
	 * @since 3.11.2 Unknown.
	 * @since 3.37.19 Load tools-related files.
	 *
	 * @return void
	 */
	public function status_page_init() {
		$this->status_page_includes();
		LLMS_Admin_Page_Status::output();
	}

}

return new LLMS_Admin_Menus();
