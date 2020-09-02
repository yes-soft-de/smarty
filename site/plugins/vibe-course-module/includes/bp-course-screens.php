<?php

/********************************************************************************
 * Screen Functions
 *
 * Screen functions are the controllers of BuddyPress. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */

 if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * If your component uses a top-level directory, this function will catch the requests and load
 * the index page.
 *
 * @package BuddyPress_Template_Pack
 * @since 1.6
 */
function bp_course_directory_setup() {
	if ( bp_is_course_component() && !bp_current_action() && !bp_current_item() ) {
		// This wrapper function sets the $bp->is_directory flag to true, which help other
		// content to display content properly on your directory.
		bp_update_is_directory( true, BP_COURSE_SLUG );

		// Add an action so that plugins can add content or modify behavior
		do_action( 'bp_course_directory_setup' );

		bp_core_load_template( apply_filters( 'course_directory_template', 'course/index' ) );
	}
}
add_action( 'bp_screens', 'bp_course_directory_setup' );


function bp_course_my_results(){
	do_action( 'bp_course_screen_my_results' );
	bp_core_load_template( apply_filters( 'bp_course_template_my_courses', 'members/single/home' ) );
}

/**
 * bp_course_my_courses()
 *
 * Sets up and displays the screen output for the sub nav item "course/my_courses"
 */

function bp_course_my_courses() {
	do_action( 'bp_course_screen_my_courses' );
	bp_core_load_template(apply_filters( 'bp_course_template_my_courses', 'members/single/home' ));
}

function bp_course_stats() {
	do_action( 'bp_course_screen_course_stats' );
	bp_core_load_template( apply_filters( 'bp_course_template_course_stats', 'members/single/home' ) );

}

/**
 * bp_course_instructor_courses()
 *
 * Sets up and displays the screen output for the sub nav item "course/instructor-courses"
 */

function bp_course_instructor_courses() {

	do_action( 'bp_course_instructing_courses' );

	bp_core_load_template( apply_filters( 'bp_course_instructor_courses', 'members/single/home' ) );
}
/**
 * The following screen functions are called when the Settings subpanel for this component is viewed
 */
function bp_course_screen_settings_menu() {
	global $bp, $current_user, $bp_settings_updated, $pass_error;

	if ( isset( $_POST['submit'] ) ) {
		/* Check the nonce */
		check_admin_referer('bp-course-admin');

		$bp_settings_updated = true;

		/**
		 * This is when the user has hit the save button on their settings.
		 * The best place to store these settings is in wp_usermeta.
		 */
		update_user_meta( $bp->loggedin_user->id, 'bp-course-option-one', attribute_escape( $_POST['bp-course-option-one'] ) );
	}

	add_action( 'bp_template_content_header', 'bp_course_screen_settings_menu_header' );
	add_action( 'bp_template_title', 'bp_course_screen_settings_menu_title' );
	add_action( 'bp_template_content', 'bp_course_screen_settings_menu_content' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

	function bp_course_screen_settings_menu_header() {
		_e( 'Course Settings Header', 'vibe' );
	}

	function bp_course_screen_settings_menu_title() {
		_e( 'Course Settings', 'vibe' );
	}

	function bp_course_screen_settings_menu_content() {
		global $bp, $bp_settings_updated; ?>

		<?php if ( $bp_settings_updated ) { ?>
			<div id="message" class="updated fade">
				<p><?php _e( 'Changes Saved.', 'vibe' ) ?></p>
			</div>
		<?php } ?>

		<form action="<?php echo $bp->loggedin_user->domain . 'settings/course-admin'; ?>" name="bp-course-admin-form" id="account-delete-form" class="bp-course-admin-form" method="post">

			<input type="checkbox" name="bp-course-option-one" id="bp-course-option-one" value="1"<?php if ( '1' == get_user_meta( $bp->loggedin_user->id, 'bp-course-option-one', true ) ) : ?> checked="checked"<?php endif; ?> /> <?php _e( 'Do you love clicking checkboxes?', 'vibe' ); ?>
			<p class="submit">
				<input type="submit" value="<?php _e( 'Save Settings', 'vibe' ) ?> &raquo;" id="submit" name="submit" />
			</p>

			<?php
			/* This is very important, don't leave it out. */
			wp_nonce_field( 'bp-course-admin' );
			?>

		</form>
	<?php
	}

/*=== SINGLE COURSE SCREENS ====*/	

function bp_screen_course_home() {

	if ( ! bp_is_single_item() ) {
		return false;
	}

	do_action( 'bp_screen_course_home' );

	bp_core_load_template( apply_filters( 'bp_template_course_home', 'courses/single/home' ) );
}

function bp_screen_course_structure(){
	
}

add_action('wplms_course_admin_bulk_actions','bp_course_admin_bulk_actions',10);
function bp_course_admin_bulk_actions(){
	echo '<ul>'.apply_filters('wplms_course_admin_bulk_actions_list',
			'<li><a href="#" class="expand_message tip" title="'.__('Send Bulk Message','vibe').'"><i class="icon-letter-mail-1"></i></a></li>').
		'</ul>';
}

add_action('wplms_course_admin_bulk_actions','bp_course_admin_extend_student_subscription',10);
function bp_course_admin_extend_student_subscription(){
	$user_id = get_current_user_id();
	$course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400,get_the_ID());
	echo'
		<div class="bulk_extend_subscription_students">
			<input type="number" id="extend_amount" class="form_field" placeholder="'.sprintf(__('Enter extend amount ( in %s','vibe'),calculate_duration_time($course_duration_parameter)).')'.'">
	 		<a href="#" id="extend_course_subscription" data-course="'.get_the_ID().'" class="button full">'.__('Extend Subscription','vibe').'</a>
	 		<input type="hidden" id="sender" value="'.$user_id.'" />';
	 	echo '</div>';
		
}

add_action('wplms_course_admin_bulk_actions','bp_course_admin_bulk_send_message',10);
function bp_course_admin_bulk_send_message(){
	$user_id = get_current_user_id();
	echo'
		<div class="bulk_message">
			<input type="text" id="bulk_subject" class="form_field" placeholder="'.__('Type Message Subject','vibe').'">
			<textarea id="bulk_message" placeholder="'.__('Type Message','vibe').'"></textarea>
			<div class="checkbox">
				<input type="checkbox" id="all_bulk_students" value="1"><label for="all_bulk_students">'.__('All Students','vibe').'</label>
			</div>
	 		<a href="#" id="send_course_message" data-course="'.get_the_ID().'" class="button full">'.__('Send Message','vibe').'</a>
	 		<input type="hidden" id="sender" value="'.$user_id.'" />';
	 	echo '</div>';
		
}

add_action('wplms_course_admin_bulk_actions','bp_course_admin_add_students',20);
function bp_course_admin_add_students(){
	$instructor_add_students = vibe_get_option('instructor_add_students');
	if((isset($instructor_add_students) && $instructor_add_students) || current_user_can('publish_posts')){
		$user_id = get_current_user_id();
		echo'
			<div class="bulk_add_students">
				<select id="student_usernames" style="width: 100%" class="selectusers" data-placeholder="'.__('Enter Student Usernames/Emails, separated by comma','vibe').'" multiple>
				</select>
		 		<a href="#" id="add_student_to_course" data-course="'.get_the_ID().'" class="button full">'.__('Add Students','vibe').'</a>';
	 	echo '</div>';
	}
}


add_action('wplms_course_admin_bulk_actions','bp_course_admin_assign_students',20);
function bp_course_admin_assign_students(){
	$instructor_assign_students = vibe_get_option('instructor_assign_students');
	if((isset($instructor_assign_students) && $instructor_assign_students) || current_user_can('publish_posts')){
		$user_id = get_current_user_id();
		echo'
		<div class="bulk_assign_students">
			<select id="assign_action" name="assign_action">
				<option value="add_badge">'.__('ASSIGN COURSE BADGE','vibe').'</option>
				<option value="add_certificate">'.__('ASSIGN COURSE CERTIFICATE','vibe').'</option>
				<option value="remove_badge">'.__('REMOVE COURSE BADGE','vibe').'</option>
				<option value="remove_certificate">'.__('REMOVE COURSE CERTIFICATE','vibe').'</option>
			</select>
			<a href="#" id="assign_course_badge_certificate" data-course="'.get_the_ID().'" class="button full">'.__('Assign Action','vibe').'</a>';
	 	echo '</div>';
	}
}

add_action('wplms_course_admin_bulk_actions','bp_course_admin_change_status',20);
function bp_course_admin_change_status(){
	$instructor_assign_students = vibe_get_option('instructor_assign_students');
	if((isset($instructor_assign_students) && $instructor_assign_students) || current_user_can('publish_posts')){
		$user_id = get_current_user_id();
		echo'
		<div class="bulk_change_status">
			<select id="status_action" name="status_action">
				<option value="start_course">'.__('Start Course','vibe').'</option>
				<option value="continue_course">'.__('Continue Course','vibe').'</option>
				<option value="under_evaluation">'.__('Under Evaluation','vibe').'</option>
				<option value="finish_course">'.__('Finished Course','vibe').'</option>
			</select>
			<input type="number" id="finish_marks" class="hide form_field" placeholder="'.__('Enter marks','vibe').'" />
			<a href="#" id="change_course_status" data-course="'.get_the_ID().'" class="button full">'.__('Change Course Status','vibe').'</a>';
	 	echo '</div>';
	}
}
