<?php
/**
 * The template for displaying Quiz/Assignment/Coruse submissions in course admin
 *
 * Override this template by copying it to yourtheme/course/single/submissions.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.1
 */

$course_id=get_the_ID();
global $wpdb;

/*========================================================================*/
/*   01. ASSIGNMENTS SUBMISSIONS
/*========================================================================*/
if ( in_array( 'wplms-assignments/wplms-assignments.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'wplms-assignments/wplms-assignments.php'))) {
echo '<div class="submissions"><h4 class="minmax">';
_e('ASSIGNMENTS SUBMISSIONS','vibe');
echo '<i class="icon-plus-1"></i></h4>';

$assignment_submissions = $wpdb->get_results($wpdb->prepare ("SELECT meta_key,post_id FROM $wpdb->postmeta WHERE meta_value = '0' && post_id IN (
SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'vibe_assignment_course' AND meta_value = %d)",$course_id), ARRAY_A); // Internal Query

	if(count($assignment_submissions)){
				echo '<ul class="assignment_students">';
				foreach($assignment_submissions as $assignment_submission ){
					if(is_numeric($assignment_submission['meta_key'])){
					$member_id=$assignment_submission['meta_key'];
					$assignment_id=$assignment_submission['post_id'];
					$bp_name = bp_core_get_userlink( $member_id );

					if(!isset($student_field))
						$student_field='Location';

					$profile_data = 'field='.$student_field.'&user_id='.$member_id;
					
					$bp_location ='';
					if(bp_is_active('xprofile'))
			    	$bp_location = bp_get_profile_field_data($profile_data);

					echo '<li id="as'.$member_id.'">';
			    	echo get_avatar($member_id);
			    	echo '<h6>'. $bp_name . '</h6>';
				    if ($bp_location) {
				    	echo '<span>'. $bp_location . '</span>';
				    }
				    // PENDING AJAX SUBMISSIONS
				    echo '<ul> 
				    		<li><a class="tip reset_assignment_user" data-assignment="'.$assignment_id.'" data-user="'.$member_id.'" title="'.__('Reset Assignment for User','vibe').'"><i class="icon-reload"></i></a></li>
				    		<li><a class="tip evaluate_assignment_user" data-assignment="'.$assignment_id.'" data-user="'.$member_id.'" title="'.__('Evaluate Assignment : ','vibe').get_the_title($assignment_id).'"><i class="icon-check-clipboard-1"></i></a></li>
				    	  </ul>';
				    echo '</li>';
					}
				}
				echo '</ul>';
				
			}

	wp_nonce_field('vibe_assignment','asecurity');
	echo '</div>';
}

/*========================================================================*/
/*   02. QUIZ SUBMISSIONS
/*========================================================================*/
echo '<div class="submissions"><h4 class="minmax">';
_e('QUIZ SUBMISSIONS','vibe');
echo '<i class="icon-plus-1"></i></h4>';
$student_field=vibe_get_option('student_field');


$curriculum=vibe_sanitize(get_post_meta(get_the_ID(),'vibe_course_curriculum',false));
echo '<ul class="quiz_students">';
if(isset($curriculum) && is_array($curriculum))
foreach($curriculum as $c){

	if(is_numeric($c)){
		if(get_post_type($c) == 'quiz'){
			// RUN META QUERY : GET ALL POST META WITH VALUE 0 FOR UNCHECKED QUIZ, THE KEY IS THE USERID
			$members_unchecked_quiz = $wpdb->get_results( "select meta_key from $wpdb->postmeta where meta_value = '0' && post_id = $c", ARRAY_A); // Internal Query
			

			if(count($members_unchecked_quiz)){
				foreach($members_unchecked_quiz as $unchecked_quiz ){
					if(is_numeric($unchecked_quiz['meta_key'])){
					$member_id=$unchecked_quiz['meta_key'];
					$bp_name = bp_core_get_userlink( $member_id );

					if(isset($student_field))
						$profile_data = 'field='.$student_field.'&user_id='.$member_id;
					else
						$profile_data='user_id='.$member_id;
					$bp_location ='';
					if(bp_is_active('xprofile'))
			    	$bp_location = bp_get_profile_field_data($profile_data);
					echo '<li id="qs'.$member_id.'">';
			    	echo get_avatar($member_id);
			    	echo '<h6>'. $bp_name . '</h6>';
				    if ($bp_location) {
				    	echo '<span>'. $bp_location . '</span>';
				    }
				    // PENDING AJAX SUBMISSIONS
				    echo '<ul> 
				    		<li><a class="tip reset_quiz_user" data-quiz="'.$c.'" data-user="'.$member_id.'" title="'.__('Reset Quiz for User','vibe').'"><i class="icon-reload"></i></a></li>
				    		<li><a class="tip evaluate_quiz_user" data-quiz="'.$c.'" data-user="'.$member_id.'" title="'.__('Evaluate Quiz for User','vibe').'"><i class="icon-check-clipboard-1"></i></a></li>
				    	  </ul>';
				    echo '</li>';
					}
				}
			}
		}
	}
}
echo '</ul>';
wp_nonce_field('vibe_quiz','qsecurity');
echo '</div>';

/*========================================================================*/
/*   03. COURSE SUBMISSIONS
/*========================================================================*/
echo '<div class="submissions"><h4 class="minmax">';
_e('COURSE SUBMISSIONS','vibe');
echo '<i class="icon-plus-1"></i></h4>';
// ALL MEMBERS who SUBMITTED COURSE STATUS CODE 2
$members_submit_course = $wpdb->get_results( "select meta_key from $wpdb->postmeta where meta_value = '2' && post_id = $course_id", ARRAY_A); // Internal Query
if(count($members_submit_course)){
	echo '<ul class="course_students">';
	foreach($members_submit_course as $submit_course ){

		if(is_numeric($submit_course['meta_key'])){
		$member_id=$submit_course['meta_key'];

		$bp_name = bp_core_get_userlink( $member_id );
    	if(isset($student_field))
			$profile_data = 'field='.$student_field.'&user_id='.$member_id;
		else
			$profile_data='user_id='.$member_id;

		$bp_location ='';
		if(bp_is_active('xprofile'))
    	$bp_location = bp_get_profile_field_data($profile_data);

		echo '<li id="s'.$member_id.'">';
    	echo get_avatar($member_id);
    	echo '<h6>'. $bp_name . '</h6>';
	    if ($bp_location) {
	    	echo '<span>'. $bp_location . '</span>';
	    }
	    // PENDING AJAX SUBMISSIONS
	    echo '<ul> 
	    		<li><a class="tip evaluate_course_user" data-course="'.$course_id.'" data-user="'.$member_id.'" title="'.__('Evaluate Course for User','vibe').'"><i class="icon-check-clipboard-1"></i></a></li>
	    	  </ul>';
	    echo '</li>';
		}
	}
	echo '</ul>';
	wp_nonce_field($course_id,'security');
}
echo '</div>';

?>