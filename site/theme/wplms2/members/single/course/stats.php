<?php 
do_action( 'bp_before_course_stats' ); 
$user_id=get_current_user_id();
$user_courses=get_posts('post_type=course&numberposts=999&meta_key='.$user_id);

echo '<ul id="userstats">';
foreach($user_courses as $course){

	$course_complete_status= bp_course_get_user_course_status($user_id,$course->ID);
	$user_course_status=get_user_meta($user_id,$course->ID,true);
	$cavg = get_post_meta($course->ID,'average',true);
	if(!$cavg)$cavg= __('N/A','vibe');

	echo '<li>
			<div class="course_avatar">'.bp_course_get_avatar("id=$course->ID&size=thumbnail").'</div>
		  	<h4>'.bp_course_get_course_title("id=$course->ID").'</a><span><br />'.__('AVERAGE PERCENTAGE : ','vibe').'<span>'.$cavg.'</span></span></h4>';

	if($course_complete_status > 3){
		//$curriculum=vibe_sanitize(get_post_meta($course->ID,'vibe_course_curriculum',false));
		$curriculum=bp_course_get_curriculum_units($course->ID);
		$average=array();

		echo '<a class="showhide_indetails"><i class="icon-plus-1"></i></a>';
	
	if(function_exists('bp_course_get_course_retakes')){
		$retaks = bp_course_get_course_retakes($course->id,$user_id);
		$pending_retakes = $retaks['remaining'];
		$retakes = $retaks['total'];
	}else{
		$retakes = apply_filters('wplms_course_retake_count',get_post_meta($course->ID,'vibe_course_retakes',true),$course->ID,$user_id);
		if(isset($retakes) && $retakes){
			global $bp;
			$table_name=$bp->activity->table_name;
			$course_retakes = $wpdb->get_results($wpdb->prepare( "
								SELECT activity.content FROM {$table_name} AS activity
								WHERE 	activity.component 	= 'course'
								AND 	activity.type 	= 'retake_course'
								AND 	user_id = %d
								AND 	item_id = %d
								ORDER BY date_recorded DESC
							" ,$user_id,$course->ID));

			$pending_retakes = $retakes - count($course_retakes);
		}
	
		if($pending_retakes > 0){
			echo '<form method="post" action="'.get_permalink($course->ID).'">';
			echo '<a class="retake_submit tip" title="'.__('Number of retakes remaining','vibe').' : '.$pending_retakes.__(' out of ','vibe').$retakes.'"><i class="icon-reload"></i></a>';
			wp_nonce_field('retake'.$user_id,'security');
			echo '</form>';
		}

		if(($retakes - count($course_retakes)) > 0){
			if( $retakes >= 9999 ){
                $title = __('Unlimited Retakes','vibe');
            }else{
                $title = __('Number of retakes remaining','vibe').' : '.($retakes - count($course_retakes)).__(' out of ','vibe').$retakes;
            }
			echo '<form method="post" action="'.get_permalink($course->ID).'">';
			echo '<a class="retake_submit tip" title="'.$title.'"><i class="icon-reload"></i></a>';
			wp_nonce_field('retake'.$user_id,'security');
			echo '</form>';
		}

	} // END Retakes

	$myavg=get_post_meta($course->ID,$user_id,true);
	if(!isset($myavg) || $myavg == '')
		$myavg = __('TAKING','vibe');

	echo '<strong>'.__('MY SCORE : ','vibe').'<span>'.apply_filters('wplms_course_marks',$myavg.'/100',$course->ID).'</span></strong>';
	echo '<ul class="in_details">';
	if(isset($curriculum) && is_array($curriculum))
	foreach($curriculum as $c){
		if(is_numeric($c)){

			if(get_post_type($c) == 'quiz'){

				$myavg=get_post_meta($c,$user_id,true);
				$avg=get_post_meta($c,'average',true);

				$questions = bp_course_get_quiz_questions($c,$user_id);
				
				if(isset($questions['marks']) && is_array($questions['marks'])){
					$marks=$questions['marks'];
					$max= array_sum($marks);
				}
				
				if(isset($myavg) && $myavg !=''){
					echo '<li>'.__('Average Marks in','vibe').' '.get_the_title($c).' : '.$avg.'';
					echo '<strong>'.__('My Marks','vibe').' : '.$myavg.' / '.$max.'</strong></li>';
				}
			}else{
				$check = get_user_meta($user_id,$c,true);
				echo '<li>'.get_the_title($c).'<strong>'.(($check)?'<i class="icon-check"></i>':'<i class="icon-alarm-clock"></i>').'</strong></li>';
			}
		}
	}
	echo '</ul>';
		
	}else{
		if($user_course_status < time()){
			echo '<strong class="stats_user_course_status">'.__('Course Expired','vibe').'</strong>';
		}
		switch($course_complete_status){
			case 1:
				echo '<strong class="stats_user_course_status">'.__('Not Started','vibe').'</strong>';
			break;
			case 2:
				echo '<strong class="stats_user_course_status">'.__('In Progress','vibe').'</strong>';
			break;
			case 3:
				echo '<strong class="stats_user_course_status">'.__('Under Evaluation','vibe').'</strong>';
			break;
		}
	}

	if($course_complete_status > 3){
		$badges = get_user_meta($user_id,'badges',true);
		if(is_array($badges)){
			if(in_array($course->ID,$badges)){
				$b=bp_get_course_badge($course->ID);
				if(!empty($b)){
					$badge=wp_get_attachment_info($b); 
					$size = apply_filters('bp_course_badge_thumbnail_size','thumbnail');
					$badge_url=wp_get_attachment_image_src($b,$size);
					echo '<a class="tip ajax-badge" data-course="'.$course->post_title.'" title="'.get_post_meta($course->ID,'vibe_course_badge_title',true).'"><img src="'.$badge_url[0].'" title="'.$badge['title'].'"/></a>';
				}
			}
		}
		$certificates = get_user_meta($user_id,'certificates',true);
		if(is_array($certificates)){
			if(in_array($course->ID,$certificates)){ 
				$url = bp_get_course_certificate('user_id='.$user_id.'&course_id='.$course->ID);
	          	$class = '';
	          	$class = apply_filters('bp_course_certificate_class','',array('course_id'=>$course->ID,'user_id'=>$user_id));
	          	if(isset($_GET['regenerate_certificate']) || (strpos($url, 'jpeg') === false) ) {
	            	$class .=' regenerate_certificate';
	          	}
	          	echo '<a href="'.$url.'" class="ajax-certificate tip'.$class.'" data-user="'.$user_id.'" title="'._x('Course Certificate','certificate lable in stats','vibe').'" data-course="'.$course->ID.'" data-security="'.wp_create_nonce($user_id).'"><span><i class="icon-certificate-file"></i></span></a>';
			}
		}
	}
	do_action('wplms_user_course_stats',$user_id,$course->ID);
	echo '</li>';

}

echo '</ul>';
do_action( 'bp_before_course_stats' ); 

?>
