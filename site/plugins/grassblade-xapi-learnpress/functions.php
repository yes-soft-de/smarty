<?php
/**
 * @package GrassBlade 
 * @version 2.4
 */
/*
Plugin Name: Experience API for LearnPress by GrassBlade
Plugin URI: https://www.nextsoftwaresolutions.com/experience-api-for-learnpress/
Description: Experience API (xAPI) integration for LearnPress LMS with GrassBlade xAPI Companion plugin.
Author: Next Software Solutions
Version: 2.4
Author URI: https://www.nextsoftwaresolutions.com
*/

class grassblade_learnpress {
	public $version = "2.4";
	public $install_link = "https://thimpress.com/learnpress-lms-pricing/?ref=liveaspankaj&campaign=addon_info_page";

	function __construct() {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if(!class_exists('grassblade_addons'))
		require_once(dirname(__FILE__)."/addon_plugins/functions.php");

		add_action('admin_menu', array($this, 'menu'), 11);
		add_action( 'plugins_loaded', array($this, "plugins_loaded") );

	}

	function plugins_loaded() {
		load_plugin_textdomain( 'grassblade-learnpress', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );

	    $lp_plugin_file_path = WP_PLUGIN_DIR . '/learnpress/learnpress.php';
		if ( defined("GRASSBLADE_VERSION") && version_compare(GRASSBLADE_VERSION, '2.0.4', '>=') && is_plugin_active('learnpress/learnpress.php') ) {
			$this->run();
		}
		else if(empty($_GET["page"]) || $_GET["page"] != "grassblade-learnpress")
			add_action( 'admin_notices', array($this, 'installation_notice') );
	}

	function run(){

		add_action( 'learn-press/after-content-item-summary/lp_lesson', array($this, 'remove_lesson_mark_complete_button'), 1);

		add_action("grassblade_completed", array($this, 'learnpress_content_completed'), 10, 3);

		add_action( 'learn-press/user-enrolled-course', array($this,'user_enrolled'), 1, 3); 

		//add_action("learn-press/before-content-item-summary/lp_quiz", array($this, 'remove_quiz'), 1);

		//add_action("grassblade_xapi_tracked", array($this, 'content_started' ), 10, 3);
                
        //apply_filters( 'learn_press_user_has_completed_quiz', $completed, $quiz_id, $this );

        //add_filter( 'learn_press_user_has_completed_quiz', '__return_true' );

        //add_filter("gb_profile_data", array($this,'user_profile'), 10, 2);

        add_filter('grassblade_content_post',array( $this,'content_post'),10,1);

        add_filter("grassblade_lms_mark_complete_button_id",array($this,"get_mark_complete_btn_id"), 11, 2);

		add_filter("grassblade_lms_next_link",array($this,"get_next_link"), 11, 2);
	}

	function installation_notice() {
		?>
		<div class="error"><p>There are problems with <b>Experience API for Learnpress</b> plugin dependencies. Please <a href="<?php echo admin_url("admin.php?page=grassblade-learnpress");?>">click here</a> to check for details.</p></div>
		<?php
	}

	/**
	 * Generate an activation URL for a plugin like the ones found in WordPress plugin administration screen.
	 *
	 * @param  string $plugin A plugin-folder/plugin-main-file.php path (e.g. "my-plugin/my-plugin.php")
	 *
	 * @return string         The plugin activation url
	 */
	function activate_plugin($plugin, $url = false)
	{
		$link = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $plugin ), 'activate-plugin_' . $plugin );

		if($url)
			return $link;

		$link = '<a href="#" onClick="return grassblade_lp_activate_plugin(\''.$link.'\');">Activate</a>';
		return $link;
	}
	function install_plugin($plugin)
	{
		//$link = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin ), 'install-plugin_' . $plugin );
		$link = '<a href="'.$this->install_link.'">'.__('Get Now').'</a>';
		return $link;
	}
	function menu() {

		global $submenu, $admin_page_hooks;
		$icon = plugin_dir_url(__FILE__)."img/icon-gb.png";

		if(empty( $admin_page_hooks[ "grassblade-lrs-settings" ] )) {
			add_menu_page("GrassBlade", "GrassBlade", "manage_options", "grassblade-lrs-settings", array($this, 'menu_page'), $icon, null);
		}

		add_submenu_page("grassblade-lrs-settings", "LearnPress LMS", "LearnPress LMS",'manage_options','grassblade-learnpress', array($this, 'menu_page') );
	}

	function menu_page(){
		
	    if (!current_user_can('manage_options'))
	    {
	      wp_die( __('You do not have sufficient permissions to access this page.') );
	    }

	    $grassblade_plugin_file_path = WP_PLUGIN_DIR . '/grassblade/grassblade.php';
		if(!defined("GRASSBLADE_VERSION") && file_exists($grassblade_plugin_file_path)) {
			$grassblade_plugin_data = get_plugin_data($grassblade_plugin_file_path);
			define('GRASSBLADE_VERSION', @$grassblade_plugin_data['Version']);
		}

		if (!file_exists($grassblade_plugin_file_path) ) {
	    	$xapi_td .= '<td colspan="2">
							<a class="buy-btn" href="https://www.nextsoftwaresolutions.com/grassblade-xapi-companion/">'.__("Buy Now", "grassblade-xapi-learnpres").'</a>
						</td>';
	    }
	    else if( version_compare(GRASSBLADE_VERSION, '2.0.4', '<' ) ) {
	    	$xapi_td = '<td colspan="2">
							<a class="buy-btn" href="https://www.nextsoftwaresolutions.com/grassblade-xapi-companion/">'.__("Get Latest Version", "grassblade-xapi-learnpress").'</a>
						</td>';   	
	    }
	    else {
	    	$xapi_td = '<td><img src="'.plugin_dir_url(__FILE__).'img/check.png"/> '.(defined("GRASSBLADE_VERSION")? GRASSBLADE_VERSION:"").'</td>';
	    	if ( !is_plugin_active('grassblade/grassblade.php') ) {
				$xapi_td .= '<td>'.$this->activate_plugin("grassblade/grassblade.php").'</td>';
			}else {
	    		$xapi_td .= '<td><img src="'.plugin_dir_url(__FILE__).'img/check.png"/></td>';
	    	}
	    }

	    $lp_plugin_file_path = WP_PLUGIN_DIR . '/learnpress/learnpress.php';

	    if (!file_exists( $lp_plugin_file_path ) ) {
	    	$lp_td = '<td colspan="2">'.$this->install_plugin('learnpress').'</td>';
	    } else {
		$lp_plugin_data = get_plugin_data($lp_plugin_file_path);
	    	$lp_td = '<td><img src="'.plugin_dir_url(__FILE__).'img/check.png"/> '.(@$lp_plugin_data['Version']).'</td>';
	    	if ( !is_plugin_active('learnpress/learnpress.php') ) {
				$lp_td .= '<td>'.$this->activate_plugin("learnpress/learnpress.php").'</td>';
			} else {
	    		$lp_td .= '<td><img src="'.plugin_dir_url(__FILE__).'img/check.png"/></td>';
	    	}
	    }

	    if(function_exists("grassblade_settings")) {
			$grassblade_settings = grassblade_settings();	
			$endpoint = $grassblade_settings["endpoint"];
			if(!empty($endpoint)) {
				if(strpos($endpoint, "gblrs.com"))
					$lrs_html = '<img src="'.plugin_dir_url(__FILE__).'img/check.png"/>';
				else if(strpos($endpoint, "grassblade-lrs"))
					$lrs_html = "GrassBlade LRS Installed";
				else 
					$lrs_html = '<img src="'.plugin_dir_url(__FILE__).'img/no.png"/> Other LRS? <a class="buy-btn" href="https://www.nextsoftwaresolutions.com/grassblade-lrs-experience-api/">Buy GrassBlade Cloud LRS</a>';
			}
	    }
	    if(empty($lrs_html))
		$lrs_html = '<a class="buy-btn" href="https://www.nextsoftwaresolutions.com/grassblade-lrs-experience-api/">Buy GrassBlade Cloud LRS</a>';
	?>
	    <style>
	    	hr {
	    		max-width: 90%;
			    margin-left: 0px;
			    border-top: 1px solid #62A21D;
	    	}
			.text{
				font-weight: 400;
				font-size: 15px;
			}
			.requirements {
				font-weight: 500;
				font-size: 16px;
			}
			table {
				border-collapse: collapse;
				min-width: 40%;
				text-align: center;
			}
			thead {
				background-color: #83BA39;
			}
			table, td, th {
			  border: 1px solid #ddd;
			}
			td{
			 padding: 18px;
			}
			th {
			 padding: 8px;
			}
			.links {
				text-decoration: none;
				margin-top: 10px !important;
				color: #000000;
			}
			.buy-btn{
				margin: 10px 0px 5px 0px !important;
				text-transform: capitalize !important;
	    		border-top: 1px solid #e6c628 !important;
				background: -webkit-linear-gradient(top,#e6c628,#82ba39) !important;
				padding: 7.5px 15px !important;
				border-radius: 9px !important;
			    text-shadow: rgba(0,0,0,.4) 0 1px 0 !important;
			    color: white !important;
			    font-size: 14px !important;
			    font-weight: bold !important;
			    font-family: Arial,serif !important;
			    text-decoration: none !important;
			    vertical-align: middle !important;
			}
			#grassblade_learnpress {
				background: white;
			    margin: 20px;
			    padding: 20px 40px;
			}
			#grassblade_learnpress img {
				vertical-align: middle;
			}
		</style>
		<script type="text/javascript">
			function grassblade_lp_activate_plugin(url) {
				jQuery.get(url, function(data) {
					window.location.reload();
				});
				return false;
			}
		</script>
		</style>
		<div id="grassblade_learnpress">
			<h2>
				<img style="margin-right: 10px;" src="<?php echo plugin_dir_url(__FILE__)."img/icon_30x30.png"; ?>"/>
				Experience API For LearnPress by Grassblade
			</h2>
			<hr>
			<div>
				<p class="text">To use xAPI Content on your LearnPress Lesson page, you need to meet the following requirements. Then follow this one-time setup process.</p>
				<h2>Requirements:</h2>
				<table class="requirements-tbl">
					<thead>
						<tr>
							<th>SNo</th>
							<th>Requirements</th>
							<th>Installed</th>
							<th>Active</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>1. </td>
							<td>
								<a class="links" href="https://www.nextsoftwaresolutions.com/grassblade-xapi-companion/">GrassBlade xAPI Companion v2.0.4+</a>
							</td>
							<?php echo $xapi_td; ?>
						</tr>
						<tr>
							<td>2. </td>
							<td><a class="links" href="<?php echo $this->install_link; ?>">LearnPress LMS</a></td>
							<?php echo $lp_td; ?>
						</tr>
						<tr>
							<td>3. </td>
							<td><a class="links" href="https://www.nextsoftwaresolutions.com/grassblade-lrs-experience-api/">GrassBlade Cloud LRS</a></td>
							<td colspan="2">
								<?php echo $lrs_html; ?>
							</td>
						</tr>
					</tbody>
				</table>
				<br>
				<h2>Useful Links:</h2>
				<ul>
					<li><a class="links" href="https://www.nextsoftwaresolutions.com/kbtopic/learnpress/" target="_blank">1. Getting started with Experience API Integration for LearnPress.</a></li>
				</ul>
			</div>	
		</div>
	<?php }
      
    /**
	 *
	 * Remove Lesson Mark Complete Button.
	 *
	 */

    function remove_lesson_mark_complete_button() {

		$lesson_id = LP_Global::course_item()->get_id();
                
		if( !empty($lesson_id) ) 
		{
			$content_id = get_post_meta($lesson_id, "show_xapi_content", true);
			if(!empty($content_id)) {
				$completion_enabled = grassblade_xapi_content::is_completion_tracking_enabled($content_id);
			}

			if(empty($completion_enabled)) {
				$content_ids = get_post_meta($lesson_id, "show_xapi_content_blocks", false);
				foreach ($content_ids as $content_id) {
					$completion_enabled = grassblade_xapi_content::is_completion_tracking_enabled($content_id);

					if($completion_enabled)
						break;				
				}
			}

			$completion_type = grassblade_xapi_content::post_completion_type($lesson_id);
			if(!empty($completion_enabled) && $completion_type == 'hide_button')
			{
				remove_action( 'learn-press/after-content-item-summary/lp_lesson', 'learn_press_content_item_lesson_complete_button');
			}
		}
		?>
		<style type="text/css">
		button.lp-button.button-complete-item[disabled="disabled"] {
		    background-color: #bfbfbf !important;
		}
		</style>

		<?php
	}


	function is_enrolled($course_id, $user) {
		global $current_user;

		if(!is_object($user) && is_numeric($user)) {
			$user = get_user_by("id", $user);
		}

		if(empty($user->ID) || empty($course_id))
			return false;

		if(!empty($current_user->ID) && $current_user->ID == $user->ID)
			return learn_press_is_enrolled_course($course_id, $user->ID);

		//Fixing learn_press_is_enrolled_course bug in LearnPress 3.2.6.9+
		$temp_current_user = $current_user;
		$current_user = $user;
		$is_enrolled = learn_press_is_enrolled_course($course_id, $user->ID);
		$current_user = $temp_current_user;

		return $is_enrolled;
	}

	/**
	 * Content Completion.
	 *
	 *
	 * @param obj $statement.
	 * @param int|string $content_id xAPI Content ID.
	 * @param obj $user User Object.
	 *
	 */

	function learnpress_content_completed($statement, $content_id, $user) {

		grassblade_show_trigger_debug_messages("learnpress_content_completed ");

		$xapi_content = get_post_meta($content_id, "xapi_content", true);

		if(empty($xapi_content["completion_tracking"])) {
			grassblade_show_trigger_debug_messages( "\nCompletion tracking not enabled. " );
			return true;
		}
		
		global $wpdb;

		$meta_post_ids = $wpdb->get_col( $wpdb->prepare("select post_id from $wpdb->postmeta where meta_key = 'show_xapi_content' AND meta_value = '%d'", $content_id) );
	    
        $block_post_ids = $wpdb->get_col($wpdb->prepare("select post_id from $wpdb->postmeta where meta_key = 'show_xapi_content_blocks' AND meta_value = '%d' ORDER BY meta_id ASC ", $content_id ));

		$post_ids = array_merge($block_post_ids,$meta_post_ids);

		foreach ($post_ids as $post_id) {

			$course_data = learn_press_get_item_courses( $post_id );

			$course_id = $course_data[0]->ID;

			$is_enroll = $this->is_enrolled($course_id, $user);

			if ($is_enroll) {

				$completed = grassblade_xapi_content::post_contents_completed($post_id,$user->ID);

				if(empty($completed)) {
					grassblade_show_trigger_debug_messages(  " Post not completed yet " );
					continue;

				} else {

					$post_data = get_post($post_id);

					if ( !empty($post_data->ID) && $post_data->post_type == 'lp_lesson' ) {

						$user_data = learn_press_get_user($user->ID);

						wp_set_current_user($user->ID);

						grassblade_show_trigger_debug_messages( " complete_lesson: lesson_id: ".$post_id." course_id:".$course_id." user_id: ".$user->ID );
						$r = $user_data->complete_lesson( $post_id, $course_id, true );
						grassblade_show_trigger_debug_messages ( " => ".print_r($r, true));

					}

					/*if ($post_data->post_type == 'lp_quiz') {

						$quiz_id= (int)$post_id;
						$is_started = learn_press_user_has_started_quiz( $user->ID, $quiz_id );

						if ($is_started) {
							$user->finish_quiz( $post_id, $course_id, true );
						} else {

							$obj_global = new LP_Global;
							$obj_global->set_user($user);

							$user_data = learn_press_get_user($user->ID);

							wp_set_current_user($user->ID);
							wp_set_auth_cookie($user->ID);

							$user_data->start_quiz( $post_id, $course_id, true );
							
							$user_data->finish_quiz( $post_id, $course_id, true );
							
						} 
						
					} */

				} // end of completed is not empty

			} else {
				grassblade_show_trigger_debug_messages(  " User: ".$user->ID." not enrolled in ".$course_id );
			} // end of is enroll	

		} // end of foreach

	} // end of learnpress_content_completed function

	/**
	 *
	 * Remove Learnpress Native Quiz.
	 *
	 */

	/*
	function remove_quiz(){
		$quiz_id = LP_Global::course_item_quiz()->get_id();

		if( !empty($quiz_id) ) 
		{
			$content_id = get_post_meta($quiz_id, "show_xapi_content", true);
			if(!empty($content_id)) {
				$completion_enabled = grassblade_xapi_content::is_completion_tracking_enabled($content_id);
			}

			if(empty($completion_enabled)) {
				$content_ids = get_post_meta($quiz_id, "show_xapi_content_blocks", false);
				foreach ($content_ids as $content_id) {
					$completion_enabled = grassblade_xapi_content::is_completion_tracking_enabled($content_id);

					if($completion_enabled)
						break;				
				}
			}

			if(!empty($completion_enabled)) 
			{
				remove_action( 'learn-press/quiz-buttons', 'learn_press_quiz_start_button');
				remove_action( 'learn-press/before-content-item-summary/lp_quiz', 'learn_press_content_item_quiz_title');
				remove_action( 'learn-press/before-content-item-summary/lp_quiz', 'learn_press_content_item_quiz_intro');
			}
		}
	} 
	*/

	/**
	 * Start Content.
	 *
	 *
	 * @param obj $statement.
	 * @param int|string $content_id xAPI Content ID.
	 * @param obj $user User Object.
	 *
	 */

	/*
	function content_started($statement, $content_id, $user) {

	    $statement_obj = json_decode($statement);

	    if(!is_object($statement_obj) || empty($user->ID) || empty($content_id))
	    	return;

	    if(is_string($statement_obj->verb))
	    	$verb = $statement_obj->verb;
	    else if(is_object($statement_obj->verb) && is_string($statement_obj->verb->id))
	    	$verb = $statement_obj->verb->id;
	    else
	    	return;

		$user_id = $user->ID;

	    if(in_array($verb, array("attempted", "launched", "initialized", "http://adlnet.gov/expapi/verbs/attempted", "http://adlnet.gov/expapi/verbs/launched", "http://adlnet.gov/expapi/verbs/initialized"))) {

	    	$this->learnpress_quiz_started($statement, $content_id, $user);

			$started = get_user_meta($user_id, "content_started_".$content_id, true );
			if(empty($started)) {
				update_user_meta($user_id, "content_started_".$content_id, time() );
			}
			
	    } // end of if

	} // end of content_started function
	*/

	/**
	 * Start LearnPress Quiz.
	 *
	 *
	 * @param obj $statement.
	 * @param int|string $content_id xAPI Content ID.
	 * @param obj $user User Object.
	 *
	 */

	/*
	function learnpress_quiz_started($statement, $content_id, $user) {

		$xapi_content = get_post_meta($content_id, "xapi_content", true);

		if(empty($xapi_content["completion_tracking"])) {
			grassblade_show_trigger_debug_messages( "\nCompletion tracking not enabled. " );
			return true;
		}
		
		global $wpdb;

		$meta_post_ids = $wpdb->get_col( $wpdb->prepare("select post_id from $wpdb->postmeta where meta_key = 'show_xapi_content' AND meta_value = '%d'", $content_id) );

	    
	    $block_post_ids = $wpdb->get_col($wpdb->prepare("select post_id from $wpdb->postmeta where meta_key = 'show_xapi_content_blocks' AND meta_value = '%d' ORDER BY meta_id ASC ", $content_id ));


		$post_ids = array_merge($block_post_ids,$meta_post_ids);


		foreach ($post_ids as $post_id) {

			$post = get_post($post_id);

			$is_started = learn_press_user_has_started_quiz( $user->ID, $post_id );

			if (!$is_started) {

				if ($post->post_type == 'lp_quiz') {

					$course_data = learn_press_get_item_courses( $post_id );

					$course_id = $course_data[0]->ID;

					$is_enroll = $this->is_enrolled($course_id,$user);

					if ($is_enroll) {
						$completed = grassblade_xapi_content::post_contents_completed($post_id,$user->ID);

						if(!empty($completed)){
							return;
						} else {

							//$user = learn_press_get_user($user->ID);
							$user->start_quiz( $post_id, $course_id, true );
						}
					} else {
						grassblade_show_trigger_debug_messages(  " User: ".$user->ID." not enrolled in ".$course_id );
					}				
				} // end of if
				
			} // end of if
			
		} // end of foreach

	} // end of learnpress_quiz_started function 
	*/

	function content_post($post) {
		if(empty($post))
			return $post;

		if(empty($post->ID) && $post->type != 'lp_course')
			return $post;

		if (is_null( LP_Global::course_item()))
			return $post;

		$lesson_id = LP_Global::course_item()->get_id();
		$lesson = get_post($lesson_id);

		return $lesson;
	}

	function get_mark_complete_btn_id($return,$post){
		if(empty($post->ID))
			return $return;

		if(!in_array($post->post_type, array('lp_lesson'))){
			return $return;
		} else {
			return '.button-complete-lesson';

		}
	}

	function get_next_link($return,$post){
		if(empty($post->ID))
			return $return;

		if(!in_array($post->post_type, array('lp_lesson'))){
			return $return;
		} else {

			$course    = LP_Global::course();
			$next_item = $prev_item = false;
			
			if(empty($course))
				return $return;

			if ( $next_id = $course->get_next_item() ) {
				$next_item = $course->get_item( $next_id );
				if(method_exists($next_item, "get_permalink"))
				return $next_item->get_permalink();
			}
		}
		return $return;
	}

	/**
	 * Course Enrollment.
	 *
	 *
	 * @param int $course_id.
	 * @param int $user_id.
	 * @param obj $user_course.
	 *
	 */
	function user_enrolled($course_id, $user_id, $user_course){
		if (class_exists('grassblade_events_tracking')) { 
			grassblade_events_tracking::send_enrolled($user_id,$course_id);
		}// end of if grassblade_events_tracking class exists
	}

	/**
	 * User Profile Data.
	 *
	 * @param array $profile_data.
	 * @param int $user_id.
	 *
	 * @return array $profile_data Profile details.
	 */

	/* function user_profile($profile_data,$user_id) {

		$courses = array();

		$student_courses = learn_press_get_enrolled_courses( $user_id );

		var_dump($student_courses); exit;

		//has_finished_course( $course_id, $force = false ) 

		$completed = 0;
		foreach ($student_courses as $key => $course) {

			var_dump($course); exit;
			$lp_user = new LP_Abstract_User($user_id);
			var_dump($course->ID);
			var_dump($lp_user); exit;
			$course_obj = $lp_user->get_course_info( $course->ID, $field = null, $force = false );

			var_dump($course_obj); exit;

			$courses[] = array( 'course_id'  => $course->ID,
								'course_title'  => $course->post_title,
								'course_progress'  => $course->course_progress,
								'course_url' => get_permalink($course->ID),
								'next_level'  => learn_press_get_lessons( $course->ID )
							 );

			if ($course->course_progress == '100') {
				$completed++;
			}
		}

		$profile_data['courses'] = $courses;
		$profile_data['total_course'] = count($courses);
		$profile_data['total_completed'] = $completed;

		var_dump($profile_data); exit;

		return $profile_data;
	} */

} // end of grassblade_learnpress class

$lp = new grassblade_learnpress();
