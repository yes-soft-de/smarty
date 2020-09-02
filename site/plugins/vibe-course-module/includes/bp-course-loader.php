<?php

// Exit if accessed directly
// It's a good idea to include this in each of your plugin files, for increased security on
// improperly configured servers
if ( !defined( 'ABSPATH' ) ) exit;
 
/*
 * If you want the users of your component to be able to change the values of your other custom constants,
 * you can use this code to allow them to add new definitions to the wp-config.php file and set the value there.
 *
 *
 *	if ( !defined( 'BP_course_CONSTANT' ) )
 *		define ( 'BP_course_CONSTANT', 'some value' // or some value without quotes if integer );
 */

/**
 * You should try hard to support translation in your component. It's actually very easy.
 * Make sure you wrap any rendered text in __() or _e() and it will then be translatable.
 *
 * You must also provide a text domain, so translation files know which bits of text to translate.
 * Throughout this course the text domain used is 'bp-course', you can use whatever you want.
 * Put the text domain as the second parameter:
 *
 * __( 'This text will be translatable', 'vibe' ); // Returns the first parameter value
 * _e( 'This text will be translatable', 'vibe' ); // Echos the first parameter value
 */

/**
 * Implementation of BP_Component
 *
 * BP_Component is the base class that all BuddyPress components use to set up their basic
 * structure, including global data, navigation elements, and admin bar information. If there's
 * a particular aspect of this class that is not relevant to your plugin, just leave it out.
 *
 * @package BuddyPress_Course_Component
 * @since 1.6
 */

class BP_Course_Component extends BP_Component {

	/**
	 * Constructor method
	 *
	 * You can do all sorts of stuff in your constructor, but it's recommended that, at the
	 * very least, you call the parent::start() function. This tells the parent BP_Component
	 * to begin its setup routine.
	 *
	 * BP_Component::start() takes three parameters:
	 *   (1) $id   - A unique identifier for the component. Letters, numbers, and underscores
	 *		 only.
	 *   (2) $name - This is a translatable name for your component, which will be used in
	 *               various places through the BuddyPress admin screens to identify it.
	 *   (3) $path - The path to your plugin directory. Primarily, this is used by
	 *		 BP_Component::includes(), to include your plugin's files. See loader.php
	 *		 to see how BP_course_PLUGIN_DIR was defined.
	 *
	 * @package BuddyPress_Course_Component
	 * @since 1.6
	 */

	function __construct() {
		global $bp;
		parent::start(
			BP_COURSE_SLUG,
			__( 'Course', 'vibe' ),
			BP_COURSE_MOD_PLUGIN_DIR
		);

		if ( ! defined( 'BP_COURSE_RESULTS_SLUG' ) )
			define( 'BP_COURSE_RESULTS_SLUG', 'course-results' );

		if ( ! defined( 'BP_COURSE_STATS_SLUG ' ) )
			define( 'BP_COURSE_STATS_SLUG', 'course-stats' );

		/**
		 * BuddyPress-dependent plugins are loaded too late to depend on BP_Component's
		 * hooks, so we must call the function directly.
		 */
		 $this->includes();

		/**
		 * Put your component into the active components array, so that
		 *   bp_is_active( 'course' );
		 * returns true when appropriate. We have to do this manually, because non-core
		 * components are not saved as active components in the database.
		 */
		$bp->active_components[$this->id] = '1';

		/**
		 * Hook the register_post_types() method. If you're using custom post types to store
		 * data (which is recommended), you will need to hook your function manually to
		 * 'init'.
		 */
		add_action( 'init', array( &$this, 'register_post_types' ) );
	}

	/**
	 * Include your component's files
	 *
	 * BP_Component has a method called includes(), which will automatically load your plugin's
	 * files, as long as they are properly named and arranged. BP_Component::includes() loops
	 * through the $includes array, defined below, and for each $file in the array, it tries
	 * to load files in the following locations:
	 *   (1) $this->path . '/' . $file - For course, if your $includes array is defined as
	 *           $includes = array( 'notifications.php', 'filters.php' );
	 *       BP_Component::includes() will try to load these files (assuming a typical WP
	 *       setup):
	 *           /wp-content/plugins/bp-course/notifications.php
	 *           /wp-content/plugins/bp-course/filters.php
	 *       Our includes function, listed below, uses a variation on this method, by specifying
	 *       the 'includes' directory in our $includes array.
	 *   (2) $this->path . '/bp-' . $this->id . '/' . $file - Assuming the same $includes array
	 *       as above, BP will look for the following files:
	 *           /wp-content/plugins/bp-course/bp-course/notifications.php
	 *           /wp-content/plugins/bp-course/bp-course/filters.php
	 *   (3) $this->path . '/bp-' . $this->id . '/' . 'bp-' . $this->id . '-' . $file . '.php' -
	 *       This is the format that BuddyPress core components use to load their files. Given
	 *       an $includes array like
	 *           $includes = array( 'notifications', 'filters' );
	 *       BP looks for files at:
	 *           /wp-content/plugins/bp-course/bp-course/bp-course-notifications.php
	 *           /wp-content/plugins/bp-course/bp-course/bp-course-filters.php
	 *
	 * If you'd prefer not to use any of these naming or organizational schemas, you are not
	 * required to use parent::includes(); your own includes() method can require the files
	 * manually. For course:
	 *    require( $this->path . '/includes/notifications.php' );
	 *    require( $this->path . '/includes/filters.php' );
	 *
	 * Notice that this method is called directly in $this->__construct(). While this step is
	 * not necessary for BuddyPress core components, plugins are loaded later, and thus their
	 * includes() method must be invoked manually.
	 *
	 * Our course component includes a fairly large number of files. Your component may not
	 * need to have versions of all of these files. What follows is a short description of
	 * what each file does; for more details, open the file itself and see its inline docs.
	 *   - -actions.php       - Functions hooked to bp_actions, mainly used to catch action
	 *			    requests (save, delete, etc)
	 *   - -screens.php       - Functions hooked to bp_screens. These are the screen functions
	 *			    responsible for the display of your plugin's content.
	 *   - -filters.php	  - Functions that are hooked via apply_filters()
	 *   - -classes.php	  - Your plugin's classes. Depending on how you organize your
	 *			    plugin, this could mean: a database query class, a custom post
	 *			    type data schema, and so forth
	 *   - -activity.php      - Functions related to the BP Activity Component. This is where
	 *			    you put functions responsible for creating, deleting, and
	 *			    modifying activity items related to your component
	 *   - -template.php	  - Template tags. These are functions that are called from your
	 *			    templates, or from your screen functions. If your plugin
	 *			    contains its own version of the WordPress Loop (such as
	 *			    bp_course_has_items()), those functions should go in this file.
	 *   - -functions.php     - Miscellaneous utility functions required by your component.
	 *   - -notifications.php - Functions related to email notification, as well as the
	 *			    BuddyPress notifications that show up in the admin bar.
	 *   - -widgets.php       - If your plugin includes any sidebar widgets, define them in this
	 *			    file.
	 *   - -buddybar.php	  - Functions related to the BuddyBar.
	 *   - -adminbar.php      - Functions related to the WordPress Admin Bar.
	 *   - -cssjs.php	  - Here is where you set up and enqueue your CSS and JS.
	 *   - -ajax.php	  - Functions used in the process of AJAX requests.
	 *
	 * @package BuddyPress_Course_Component
	 * @since 1.6
	 */

	function includes($includes = array()) {

		// Files to include
		$includes = array(
			'includes/bp-course-actions.php',
			'includes/bp-course-screens.php',
			'includes/bp-course-filters.php',
			'includes/bp-course-classes.php',
			'includes/bp-course-activity.php',
			'includes/bp-course-template.php',
			'includes/bp-course-functions.php',
			'includes/bp-course-tour.php',
			'includes/bp-course-notifications.php',
			'includes/bp-course-widgets.php',
			'includes/bp-course-cssjs.php',
			'includes/bp-course-ajax.php',
			'includes/bp-course-offline.php',
			'includes/bp-course-mailer.php',
			'includes/bp-course-scheduler.php',
			'includes/bp-course-api.php',
			'includes/api/class-api-tracker-controller.php',			
		);

		parent::includes( $includes );

		// As an course of how you might do it manually, let's include the functions used
		// on the WordPress Dashboard conditionally:
		if ( is_admin() || is_network_admin() ) {
			include( BP_COURSE_MOD_PLUGIN_DIR . '/includes/bp-course-admin.php' );
		}
	}

	/**
	 * Set up your plugin's globals
	 *
	 * Use the parent::setup_globals() method to set up the key global data for your plugin:
	 *   - 'slug'			- This is the string used to create URLs when your component
	 *				  adds navigation underneath profile URLs. For course,
	 *				  in the URL http://testbp.com/members/boone/course, the
	 *				  'course' portion of the URL is formed by the 'slug'.
	 *				  Site admins can customize this value by defining
	 *				  BP_course_SLUG in their wp-config.php or bp-custom.php
	 *				  files.
	 *   - 'root_slug'		- This is the string used to create URLs when your component
	 *				  adds navigation to the root of the site. In other words,
	 *				  you only need to define root_slug if your component is a
	 *				  "root component". Eg, in:
	 *				    http://testbp.com/course/test
	 *				  'course' is a root slug. This should always be defined
	 *				  in terms of $bp->pages; see the course below. Site admins
	 *				  can customize this value by changing the permalink of the
	 *				  corresponding WP page in the Dashboard. NOTE:
	 *				  'root_slug' requires that 'has_directory' is true.
	 *   - 'has_directory'		- Set this to true if your component requires a top-level
	 *				  directory, such as http://testbp.com/course. When
	 *				  'has_directory' is true, BP will require that site admins
	 *				  associate a WordPress page with your component. NOTE:
	 *				  When 'has_directory' is true, you must also define your
	 *				  component's 'root_slug'; see previous item. Defaults to
	 *				  false.
	 *   - 'notification_callback'  - The name of the function that is used to format BP
	 *				  admin bar notifications for your component.
	 *   - 'search_string'		- If your component is a root component (has_directory),
	 *				  you can provide a custom string that will be used as the
	 *				  default text in the directory search box.
	 *   - 'global_tables'		- If your component creates custom database tables, store
	 *				  the names of the tables in a $global_tables array, so that
	 *				  they are available to other BP functions.
	 *
	 * You can also use this function to put data directly into the $bp global.
	 *
	 * @package BuddyPress_Course_Component
	 * @since 1.6
	 *
	 * @global obj $bp BuddyPress's global object
	 */
	public function setup_globals($args=array()) {
		global $bp;

		// Defining the slug in this way makes it possible for site admins to override it
		if ( !defined( 'BP_COURSE_SLUG' ) )
			define( 'BP_COURSE_SLUG', $this->id );

		if ( !defined( 'BP_COURSE_INSTRUCTOR_SLUG' ) )
			define( 'BP_COURSE_INSTRUCTOR_SLUG', 'instructor-courses');
		
		// Global tables for the course component. Build your table names using
		// $bp->table_prefix (instead of hardcoding 'wp_') to ensure that your component
		// works with $wpdb, multisite, and custom table prefixes.
		/*
		$global_tables = array(
			'table_name'      => $bp->table_prefix . 'bp_example'
		);
		*/
		
		// Set up the $globals array to be passed along to parent::setup_globals()
		$globals = array(
			'slug'                  => BP_COURSE_SLUG,
			'root_slug'             => isset( $bp->pages->{$this->id}->slug ) ? $bp->pages->{$this->id}->slug : BP_COURSE_SLUG,
			'has_directory'         => true, // Set to false if not required
			'directory_title'       => _x( 'Course Directory', 'Course directory title', 'vibe' ),
			'notification_callback' => 'bp_course_format_notifications',
			'search_string'         => __( 'Search courses ...', 'vibe' ),
			//'global_tables'         => $global_tables
		);
		// Let BP_Component::setup_globals() do its work.
		parent::setup_globals( $globals );

	}

	/**
	 * Set up your component's navigation.
	 *
	 * The navigation elements created here are responsible for the main site navigation (eg
	 * Profile > Activity > Mentions), as well as the navigation in the BuddyBar. WP Admin Bar
	 * navigation is broken out into a separate method; see
	 * BP_course_Component::setup_admin_bar().
	 *
	 * @global obj $bp
	 */
	function setup_nav($main_nav = array(), $sub_nav = array()) {

		$show_for_displayed_user=apply_filters('wplms_user_profile_courses',false);
		$main_nav = array(
			'name'            => sprintf( __( 'Courses <span>%s</span>', 'vibe' ), bp_course_get_total_course_count_for_user() ),
			'slug' 		      => BP_COURSE_SLUG,
			'position' 	      => 5,
			'screen_function'     => 'bp_course_my_courses',
			'show_for_displayed_user' => $show_for_displayed_user, //Change for admin
			'default_subnav_slug' => BP_COURSE_SLUG,
		);

		// Add 'course' to the main navigation
		if(function_exists('vibe_get_option')){
			$course_view = vibe_get_option('course_view');
			if(isset($course_view) && $course_view){
				$main_nav['show_for_displayed_user']=$show_for_displayed_user; //Change for admin
			}
		}

		$course_link = trailingslashit( bp_loggedin_user_domain() . BP_COURSE_SLUG );

		// Determine user to use
		if ( bp_displayed_user_domain() ) {
			$user_domain = bp_displayed_user_domain();
		} elseif ( bp_loggedin_user_domain() ) {
			$user_domain = bp_loggedin_user_domain();
		} else {
			$user_domain = false;
		}

		if ( !empty( $user_domain ) ) {
			$user_access = bp_is_my_profile();
			$user_access = apply_filters('wplms_user_profile_courses',$user_access);
			$sub_nav[] = array(
				'name'            =>  __('My Courses', 'vibe' ),
				'slug'            => BP_COURSE_SLUG,
				'parent_url'      => $course_link,
				'parent_slug'     => BP_COURSE_SLUG,
				'screen_function' => 'bp_course_my_courses',
				'user_has_access' => $user_access,
				'position'        => 10
			);
			
			bp_core_new_subnav_item( array(
				'name' 		  => __( 'Results', 'vibe' ),
				'slug' 		  => BP_COURSE_RESULTS_SLUG,
				'parent_slug'     => BP_COURSE_SLUG,
				'parent_url'      => $course_link,
				'screen_function' => 'bp_course_my_results',
				'position' 	  => 30,
				'user_has_access' => $user_access // Only the logged in user can access this on his/her profile
			) );

			bp_core_new_subnav_item( array(
				'name' 		  => __( 'Stats', 'vibe' ),
				'slug' 		  => BP_COURSE_STATS_SLUG,
				'parent_slug'     => BP_COURSE_SLUG,
				'parent_url'      => $course_link,
				'screen_function' => 'bp_course_stats',
				'position' 	  => 40,
				'user_has_access' => $user_access // Only the logged in user can access this on his/her profile
			) );

			$sub_nav[] = array(
				'name'            =>  sprintf( __( 'Instructing Courses <span>%s</span>', 'vibe' ), bp_course_get_instructor_course_count_for_user( bp_loggedin_user_id() ) ),
				'slug'            => BP_COURSE_INSTRUCTOR_SLUG,
				'parent_slug'     => BP_COURSE_SLUG,
				'parent_url'      => $course_link,
				'screen_function' => 'bp_course_instructor_courses',
				'position'        => 50,
				'user_has_access' => bp_is_my_profile_intructor(),
			);
			
			parent::setup_nav( $main_nav, $sub_nav );
		}

		// If your component needs additional navigation menus that are not handled by
	
		if ( bp_is_course_component() && bp_is_single_item() ) {

			// Reset sub nav
			$sub_nav = array();

			// Add 'courses' to the main navigation
			$main_nav = array(
				'name'                => __( 'Home', 'vibe' ),
				'slug'                => get_current_course_slug(),
				'position'            => -1, // Do not show in BuddyBar
				'screen_function'     => 'bp_screen_course_home',
				'default_subnav_slug' => $this->default_extension,
				'item_css_id'         => $this->id
			);

			parent::setup_nav( $main_nav, $sub_nav );
		}

		if ( isset( $this->current_course->user_has_access ) ) {
			do_action( 'courses_setup_nav', $this->current_course->user_has_access );
		} else {
			do_action( 'courses_setup_nav');
		}
		
	}//End setup nav

	/**
	 * If your component needs to store data, it is highly recommended that you use WordPress
	 * custom post types for that data, instead of creating custom database tables.
	 *
	 * In the future, BuddyPress will have its own bp_register_post_types hook. For the moment,
	 * hook to init. See BP_course_Component::__construct().
	 *
	 * @package BuddyPress_Course_Component
	 * @since 1.6
	 * @see http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	function register_post_types() {
		
	}

	function register_taxonomies() {

	}
}

/**
 * Loads your component into the $bp global
 *
 * This function loads your component into the $bp global. By hooking to bp_loaded, we ensure that
 * BP_course_Component is loaded after BuddyPress's core components. This is a good thing because
 * it gives us access to those components' functions and data, should our component interact with
 * them.
 *
 * Keep in mind that, when this function is launched, your component has only started its setup
 * routine. Using print_r( $bp->course ) or var_dump( $bp->course ) at the end of this function
 * will therefore only give you a partial picture of your component. If you need to dump the content
 * of your component for troubleshooting, try doing it at bp_init, ie
 *   function bp_course_var_dump() {
 *   	  global $bp;
 *	  var_dump( $bp->course );
 *   }
 *   add_action( 'bp_init', 'bp_course_var_dump' );
 * It goes without saying that you should not do this on a production site!
 *
 * @package BuddyPress_Course_Component
 * @since 1.6
 */
function bp_course_load_core_component() {
	global $bp;	
	$bp->course = new BP_Course_Component;
}
add_action( 'bp_loaded', 'bp_course_load_core_component' );

add_action( 'admin_notices', 'wplms_check_plugin_notice' );
function wplms_check_plugin_notice() {

	if(!defined('THEME_SHORT_NAME'))
		return;
	
	$wplms = wp_get_theme();
	$value=$wplms->get('Version');
	$parent = $wplms->parent();
	
	if(!empty($parent) && defined('THEME_SHORT_NAME') && $wplms->name != 'WPLMS Blank ChildTheme'){

		if(in_array($wplms->name,array('WPLMS Modern','WPLMS Instructor','WPLMS one Page','WPLMS ChildTheme I'))){			
			$wplms_parent = wp_get_theme(THEME_SHORT_NAME);
			$val = $wplms_parent->get('Version');
			if (version_compare($val, "2.6") < 0) {
				echo '<div class="error">
				    <p>'.sprintf(__( 'Please Update the %s theme to latest version %s', 'vibe' ),$wplms_parent->name,'2.6').'</p>
				  </div>';
			}

			if (version_compare($value, "2.6") < 0) {
				echo '<div class="error">
				    <p>'.sprintf(__( 'Please Update the %s theme to latest version %s', 'vibe' ),$wplms->name,'2.6').'</p>
				  </div>';
			}
		}

	}else{
		$wplms = wp_get_theme(THEME_SHORT_NAME);
		$value=$wplms->get('Version');
		if (version_compare($value, "2.6") < 0) {
			echo '<div class="error notice is-dismissible">
			    <p>'.sprintf(__( 'Please Update the %s theme to latest version %s', 'vibe' ),$wplms->name,'2.6').'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
			  </div>';
		}
	}
}