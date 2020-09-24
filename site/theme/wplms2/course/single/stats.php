<?php
/**
 * The template for displaying Course Stats in Course -> Admin
 *
 * Override this template by copying it to yourtheme/course/single/stats.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.1
 */
if ( !defined( 'ABSPATH' ) ) exit;
		$course_id=get_the_ID();
		$students=get_post_meta($course_id,'vibe_students',true);

		$avg=get_post_meta($course_id,'average',true);
		$pass=get_post_meta($course_id,'pass',true);
		$badge=get_post_meta($course_id,'badge',true);


		echo '<div class="course_grade">
				<ul>
					<li>'.__('Total Number of Students who took this course','vibe').' <strong>'.$students.'</strong></li>
					<li>'.__('Average Percentage obtained by Students','vibe').' <strong>'.$avg.' <span>'.__('out of 100','vibe').'</span></strong></li>
					<li>'.__('Number of Students who got a Badge','vibe').' <strong>'.$badge.'</strong></li>
					<li>'.__('Number of Passed Students','vibe').' <strong>'.$pass.'</strong></li>
				</ul>
			</div>';
		echo '<div id="average"><span>'.__('Average Marks obtained by Students','vibe').'</span><input type="text" class="dial" data-max="100" value="'.(empty($avg)?0:$avg).'"></div>';
		echo '<div id="pass"><span>'.__('Number of Passed Students','vibe').'</span><input type="text" class="dial" data-max="'.$students.'" value="'.(empty($pass)?0:$pass).'"></div>';	
		echo '<div id="badge"><span>'.__('Number of Students who got a Badge','vibe').'</span><input type="text" class="dial" data-max="'.$students.'" value="'.(empty($badge)?0:$badge).'"></div>';

		
		
		
		$curriculum= bp_course_get_full_course_curriculum(get_the_ID());
		if(!is_array($curriculum)){
			echo '<div class="error">'.__('No curriculum defined','vibe').'</div>';
		}else{

		foreach($curriculum as $c){
			if(is_numeric($c)){
				if(get_post_type($c) == 'quiz'){
					$qavg=get_post_meta($c,'average',true);

					$ques = vibe_sanitize(get_post_meta($c,'vibe_quiz_questions',false));
					if(isset($ques['marks']) && is_array($ques['marks']))
						$qmax= array_sum($ques['marks']);
					else{
						$dynamic = get_post_meta($c,'vibe_quiz_dynamic',true);
						if(isset($dynamic) && $dynamic !='H'){
							$n = get_post_meta($c,'vibe_quiz_number_questions',true);
							$m = get_post_meta($c,'vibe_quiz_marks_per_question',true);
							if(is_numeric($n) && is_numeric($m))
								$qmax=$n*$m;
							else
								$qmax=100;
						}else
							$qmax=100;
					}

					echo '<div class="course_quiz">
							<h5>'.__('Average Marks in Quiz ','vibe').' '.get_the_title($c).'</h5>
							<input type="text" class="dial" data-max="'.$qmax.'" value="'.$qavg.'">
						</div>';			
				}
			}
		}
		
		do_action('wplms_course_stats_panel',get_the_ID());
		echo '<div class="calculate_panel"><strong>'.__('Calculate :','vibe').'</strong>';
			echo '<a href="#" id="calculate_avg_course" data-courseid="'.get_the_ID().'" class="tip" title="'.__('Calculate Statistics for Course','vibe').'"> <i class="icon-calculator"></i> </a>';
			wp_nonce_field('vibe_security','security'); // Just random text to verify
		echo '</div>';
		} 
		
