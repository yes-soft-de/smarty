<?php

/**
 * The template for displaying Course font
 *
 * Override this template by copying it to yourtheme/course/single/front.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.0
 */

if ( !defined( 'ABSPATH' ) ) exit;
global $post;
$id= get_the_ID();

do_action('wplms_course_before_front_main');


?>

<div class="course_title">
	<?php vibe_breadcrumbs(); ?>
	<h1><?php the_title(); ?></h1>
	<?php if(!empty($post->post_excerpt) && strpos($post->post_content,$post->post_excerpt) === false){ echo '<h6>';the_excerpt(); echo '</h6>';} ?>
</div>
<div class="students_undertaking">
	<?php
	$students_undertaking=array();
	$students_undertaking = bp_course_get_students_undertaking();
	$students=get_post_meta(get_the_ID(),'vibe_students',true);

	echo '<strong>'.$students.__(' STUDENTS ENROLLED','vibe').'</strong>';

	echo '<ul>';
	$i=0;
	foreach($students_undertaking as $student){
		$i++;
		echo '<li>'.get_avatar($student).'</li>';
		if($i>5)
			break;
	}
	echo '</ul>';
	?>
</div>
<?php 
do_action('wplms_before_course_description');
?>
<div class="course_description">
	<div class="small_desc">
	<?php 
		$more_flag = 1;
		$content=get_the_content(); 
		$middle=strpos( $post->post_content, '<!--more-->' );
		if($middle){
			echo apply_filters('the_content',substr($content, 0, $middle));
		}else{
			$more_flag=0;
			echo apply_filters('the_content',$content);
		}
	?>
	<?php 
		if($more_flag){
			echo '<a href="#" id="more_desc" class="link" data-middle="'.$middle.'">'.__('READ MORE','vibe').'</a>';
		}
	?>
	</div>
	<?php 
		if($more_flag){ 
	?>
		<div class="full_desc">
		<?php 
			echo apply_filters('the_content',substr($content, $middle,-1));
		?>
		<?php 
			echo '<a href="#" id="less_desc" class="link">'.__('LESS','vibe').'</a>';
		?>
		</div>
	<?php
		}
	?>	

</div>
<?php
do_action('wplms_after_course_description');
?>

<div class="course_reviews">
<?php
	 comments_template('/course-review.php',true);
?>
</div>
<?php
