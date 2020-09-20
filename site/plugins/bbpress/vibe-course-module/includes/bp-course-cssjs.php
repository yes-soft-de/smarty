<?php

/**
 * NOTE: You should always use the wp_enqueue_script() and wp_enqueue_style() functions to include
 * javascript and css files.
 */

 if ( ! defined( 'ABSPATH' ) ) exit;
function bp_course_add_css() {
	if ( ! function_exists( 'vibe_logo_url' ) ) return; // Checks if WPLMS is active in current site in WP Multisite
	wp_enqueue_style( 'bp-course-css', plugins_url( 'css/course_template.css',__FILE__ ), array(),BP_COURSE_MOD_VERSION);
	}
add_action( 'wp_enqueue_scripts', 'bp_course_add_css');

function bp_course_add_js() {
	global $bp;
	if ( ! function_exists( 'vibe_logo_url' ) ) return; // Checks if WPLMS is active in current site in WP Multisite

	$take_course_page_id = vibe_get_option('take_course_page');
	$create_course_page_id = vibe_get_option('create_course');
	if(function_exists('vibe_get_option') && function_exists('icl_object_id')){
		$take_course_page_id = icl_object_id($take_course_page_id,'page',true);
		$create_course_page_id = icl_object_id($create_course_page_id,'page',true);
	}
	wp_enqueue_script( 'bp-extras-js', plugins_url( '/vibe-course-module/includes/js/course-module-js.min.js' ),array('jquery'),bp_course_version(),true);
	if(function_exists('vibe_get_option')){
		if(is_singular('unit') || is_singular('question') || is_singular('quiz') || is_singular('wplms-assignment') || is_page($take_course_page_id) || is_page($create_course_page_id) || isset($_GET['edit']) ){
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('jquery-ui-droppable');
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_script('knob-js',plugins_url( '/vibe-course-module/includes/js/jquery.knob.min.js'),array('jquery'),bp_course_version(),true);

			add_action('wp_footer',function(){
				?>
				<script>
				var isMobile = false; //initiate as false
				// device detection
				if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
				    || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true;

		    		if(isMobile){
		    			 document.write('<script type="text/javascript" src="<?php echo plugins_url( '/vibe-course-module/includes/js/jquery.uitouchpunch.js' ); ?>"><\/script>');
		    		}
				</script>
				<?php
			});
		}
		if(is_page(vibe_get_option('take_course_page'))){
			wp_playlist_scripts('video');
		}
	}
	if(function_exists('bp_is_directory')){
		if((bp_is_directory() && bp_current_component() == 'course') || is_post_type_archive('course')){
			wp_enqueue_script('jquery-ui-datepicker');
		}
	}
	
	$action = bp_current_action();
	if(isset($_GET['action'])){
		$action = $_GET['action'];
	}
	if(in_array($action, array('admin','submissions','stats'))){
		wp_enqueue_script('knob-js',plugins_url( '/vibe-course-module/includes/js/jquery.knob.min.js'),array('jquery'),bp_course_version(),true);
	}
	
	wp_enqueue_script( 'bp-course-js', plugins_url( '/vibe-course-module/includes/js/course.js' ),array('jquery','wp-mediaelement','buddypress-js'),bp_course_version(),true);
	$user_id = get_current_user_id();
	$nonce = wp_create_nonce('vibe_tour_security'.$user_id,'vibe_tour_security');

	$color=bp_wplms_get_theme_color();
	$single_dark_color=bp_wplms_get_theme_single_dark_color();
	$translation_array = array( 
		'timeout' => _x( 'TIMEOUT','displayed to suer when quiz times out.','vibe' ), 
		'too_fast_answer' => _x( 'Too Fast or Answer not marked.','Quiz answer being marked very fast','vibe' ), 
		'answer_saved' => _x( 'Answer Saved.','Save answer on every question, confirmation message','vibe' ), 
		'processing' => _x( 'Processing...','Quiz question anwer save under progress','vibe' ), 
		'saving_answer' => _x( 'Saving Answer...please wait','Saving quiz answers under progress','vibe' ), 
		'remove_user_text' => __( 'This step is irreversible. Are you sure you want to remove the User from the course ?','vibe' ), 
		'remove_user_button' => __( 'Confirm, Remove User from Course','vibe' ), 
		'confirm' => _x( 'Confirm','Confirm button for various popup confirmation messages','vibe' ), 
		'cancel' => _x( 'Cancel','Cancel button for various popup confirmation messages','vibe' ), 
		'reset_user_text' => __( 'This step is irreversible. All Units, Quiz results would be reset for this user. Are you sure you want to Reset the Course for this User?','vibe' ), 
		'reset_user_button' => __( 'Confirm, Reset Course for this User','vibe' ), 
		'quiz_reset' => __( 'This step is irreversible. All Questions answers would be reset for this user. Are you sure you want to Reset the Quiz for this User? ','vibe' ), 
		'quiz_reset_button' => __( 'Confirm, Reset Quiz for this User','vibe' ), 
		'marks_saved' => __( 'Marks Saved','vibe' ), 
		'quiz_marks_saved' => __( 'Quiz Marks Saved','vibe' ), 
		'save_quiz' => __( 'Save Quiz progress','vibe' ), 
		'saved_quiz_progress' => __( 'Saved','vibe' ), 
		'submit_quiz' => __( 'Submit Quiz','vibe' ), 
		'sending_messages' => __( 'Sending Messages ...','vibe' ), 
		'adding_students' => __( 'Adding Students to Course ...','vibe' ), 
		'successfuly_added_students' => __( 'Students successfully added to Course','vibe' ),
		'unable_add_students' => __( 'Unable to Add students to Course','vibe' ),
		'select_fields' => __( 'Please select fields to download','vibe' ),
		'download' => __( 'Download','vibe' ),
		'timeout' => __( 'TIMEOUT','vibe' ),
		'theme_color' => $color,
		'single_dark_color' => $single_dark_color,
		'for_course' => __( 'for Course','vibe' ),
		'active_filters' => __( 'Active Filters','vibe' ),
		'clear_filters' => __( 'Clear all filters','vibe' ),
		'remove_comment' => __( 'Are you sure you want to remove this note?','vibe' ),
		'remove_comment_button' => __( 'Confirm, remove note','vibe' ), 
		'private_comment'=> __( 'Make Private','vibe' ), 
		'add_comment'=> __( 'Add your note','vibe' ), 
		'submit_quiz_error'=> __( 'Please add questions or retake the quiz !','vibe' ), 
		'remove_announcement'=> __( 'Are you sure you want to remove this Annoucement?','vibe' ), 
		'start_quiz_notification'=> __( 'You\'re about to start the Quiz. Please click confirm to begin the quiz.','vibe' ), 
		'submit_quiz_notification'=> __( 'Are you sure you want to submit the quiz. Submitting the quiz will freeze all your answers, you can not change them.  Please confirm.','vibe' ), 
		'check_results'=> __( 'Check results','vibe' ), 
		'correct'=> __( 'Correct','vibe' ), 
		'incorrect'=> __( 'Incorrect','vibe' ),
		'confirm_apply'=> _x('Are you sure you want to apply for this Course ?','confirmation message when user clicks on apply for course','vibe'),
		'instructor_uncomplete_unit' => _x('Are you sure you want mark this unit "incomplete" for the user ?','Popup confirmation message when instructor marks the unit uncomplete for the user.','vibe'),
		'instructor_complete_unit'=> _x('Are you sure you want to mark this unit "complete" for the user ?','Popup confirmation message ','vibe'),
		'unanswered_questions' => __( 'You have few unanswered questions. Are you sure you want to continue ?','vibe' ), 
		'enter_more_characters' => __( 'Please enter 4 or more characters ...','vibe' ),
		'correct_answer'=> __( 'Correct Answer','vibe' ), 
		'explanation'=> __( 'Explanation','vibe' ), 
		'And'=> __( 'and','vibe' ), 
		//tour strings
		'go' =>  _x('Go','tour','vibe'),
		'security' => $nonce,
		'confirm_course_retake'=>_x('Are you sure you want to retake course?','retake confrim','vibe'),
		);
	wp_localize_script( 'bp-course-js', 'vibe_course_module_strings', $translation_array );
}

add_action( 'wp_enqueue_scripts', 'bp_course_add_js');

add_action('admin_enqueue_scripts','bp_course_admin_scripts');
function bp_course_admin_scripts(){
	wp_enqueue_script( 'bp-graph-js', plugins_url( '/vibe-course-module/includes/js/jquery.flot.min.js' ) );
}
?>