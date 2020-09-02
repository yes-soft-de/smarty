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
<h2 class="heading">
	<span><?php  _e('Course Curriculum','vibe'); ?></span>
</h2>

<div class="course_curriculum <?php echo $class; ?>">
<?php
do_action('wplms_course_curriculum_section',$id);

$course_curriculum = bp_course_get_full_course_curriculum($id); 

if(!empty($course_curriculum)){

	echo '<table class="table">';
	foreach($course_curriculum as $lesson){ 
		switch($lesson['type']){
			case 'unit':
				?>
				<tr class="course_lesson unit_<?php echo $lesson['id']; ?>">
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
}else{
	?>
	<div class="message"><?php echo _x('No curriculum found !','Error message for no curriculum found in course curriculum ','vibe'); ?></div>
	<?php	
}
?>
</div>

<?php

?>