<?php
/**
 * The template for displaying Course Curriculum
 *
 * Override this template by copying it to yourtheme/course/single/curriculum.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.2
 */

if ( !defined( 'ABSPATH' ) ) exit;
global $post;
$id= get_the_ID();

$class='';
if(class_exists('WPLMS_tips')){
	$wplms_settings = WPLMS_tips::init();
	$settings = $wplms_settings->lms_settings;
	if(isset($settings['general']['curriculum_accordion'])){
		$class="accordion";	
	}
}

?>
<h3 class="heading">
	<span><?php  _e('Course Curriculum','vibe'); ?></span>
</h3>

<div class="course_curriculum <?php echo $class; ?>">
<?php
do_action('wplms_course_curriculum_section',$id);

$course_curriculum = bp_course_get_full_course_curriculum($id); 

if(!empty($course_curriculum)){
	$request_uri = explode('/', $_SERVER['REQUEST_URI']);

	if ( !in_array('pre-wellness', $request_uri, false) ||
       !in_array('pre-business', $request_uri, false) ||
       !in_array('pre-tune', $request_uri, false) ):
	
		$lessonType = array( 
			'about' 	=> '', 
			'video' 	=> 'play', 
			'audio' 	=> 'music-file-1',
			'article'   => '', 
		);
		$unitType = array( '', 'play', 'music-file-1', 'podcast' );

		echo '<ul class="nav nav-tabs mb-3 mx-auto" role="tablist" id="myTab" style="width: fit-content;">';
		foreach($course_curriculum as $lesson) {
			switch($lesson['type']){
				case 'section':	?>
						<li class="" role="presentation">
							<a href="#<?php echo strtolower( $lesson['title'] ); ?>" role="tab" data-toggle="tab">
								<?php
									$icon = strtolower( $lesson['title'] );
									switch ($icon) {
										case 'video' :
											echo '<i class="fa fa-video-camera fa-fw"></i>';
											break;
										case 'audio' :
											echo '<i class="fa fa-microphone fa-fw"></i>';
											break;
										case 'article' :
											echo '<i class="fa fa-file-text fa-fw"></i>';
											break;
										case 'about' :
											echo '<i class="fa fa-info-circle fa-fw"></i>';
											break;
									}
								?>
								<?php echo $lesson['title']; ?>
							</a>
						</li>
					<?php
				break;
			}
		}
		echo '</ul>';

		echo '<div class="tab-content" id="myTabContent">';
		foreach ( $lessonType as $item => $key) {
			foreach ( $unitType as $type ) {		
				if ( $key == $type ) {					
					echo '<div class="tab-pane" id="' . $item . '">';
					foreach($course_curriculum as $lesson) {
							switch($lesson['type']) {
								case 'unit': 
									$varLesson = explode(' ', strtolower( $lesson['title'] ) );
									
									if ( in_array( $item, $varLesson ) ) {  ?>
			
										<table class="table">			
											<tr class="course_lesson d-block p-0" style="display: table !important; width: 100%">
												<td class="curriculum-icon"><i class="icon-<?php echo $lesson['icon']; ?>"></i></td>
												<td><?php echo apply_filters('wplms_curriculum_course_lesson',(!empty($lesson['link'])?'<a href="'.$lesson['link'].'">':''). strtolower( $lesson['title'] ). (!empty($lesson['link'])?'</a>':''),$lesson['id'],$id); ?></td>
												<td><?php echo $lesson['labels']; ?> </td>
												<td><?php echo $lesson['duration']; ?></td>
											</tr>
										</table>

									<?php
									} elseif ( $lesson['icon'] == $type ) {  ?>
			
										<table class="table">			
											<tr class="course_lesson d-block p-0" style="display: table !important; width: 100%">
												<td class="curriculum-icon"><i class="icon-<?php echo $lesson['icon']; ?>"></i></td>
												<td><?php echo apply_filters('wplms_curriculum_course_lesson',(!empty($lesson['link'])?'<a href="'.$lesson['link'].'">':''). strtolower( $lesson['title'] ). (!empty($lesson['link'])?'</a>':''),$lesson['id'],$id); ?></td>
												<td><?php echo $lesson['labels']; ?> </td>
												<td><?php echo $lesson['duration']; ?></td>
											</tr>
										</table>


									<?php
									}
									do_action('wplms_curriculum_course_unit_details',$lesson);
								break;
							} // end Switch 
						} // end foreach
					echo '</div>';
				}
			}
		}
		echo '</div>';
	else:
		echo '<table class="table">';
		foreach($course_curriculum as $lesson){ 
			switch($lesson['type']){
				case 'unit':
					?>
					<tr class="course_lesson">
						<td class="curriculum-icon"><i class="icon-<?php echo $lesson['icon']; ?>"></i></td>
						<td><?php echo apply_filters('wplms_curriculum_course_lesson',(!empty($lesson['link'])?'<a href="'.$lesson['link'].'">':''). $lesson['title']. (!empty($lesson['link'])?'</a>':''),$lesson['id'],$id); ?></td>
						<td><?php echo $lesson['labels']; ?> </td>
						<td><?php echo $lesson['duration']; ?></td>
					</tr>
					<?php
					do_action('wplms_curriculum_course_unit_details',$lesson);
				break;
				case 'quiz':
					?>
					<tr class="course_lesson">
						<td class="curriculum-icon"><i class="icon-<?php echo $lesson['icon']; ?>"></i></td>
						<td><?php echo apply_filters('wplms_curriculum_course_quiz',(($lesson['link'])?'<a href="'.$lesson['link'].'">':''). $lesson['title'].(isset($lesson['free'])?$lesson['free']:'') . (!empty($lesson['link'])?'</a>':''),$lesson['id'],$id); ?></td>
						<td><?php echo $lesson['labels']; ?> </td>
						<td><?php echo $lesson['duration']; ?></td>
					</tr>
					<?php
					do_action('wplms_curriculum_course_quiz_details',$lesson);
				break;
				case 'section':
					?>
					<tr class="course_section">
						<td colspan="4"><?php echo $lesson['title']; ?></td>
					</tr>
					<?php
				break;
			}
		}
		echo '</table>';
	endif;
}else{
	?>
	<div class="message"><?php echo _x('No curriculum found !','Error message for no curriculum found in course curriculum ','vibe'); ?></div>
	<?php	
}
?>
</div>

<?php

?>
