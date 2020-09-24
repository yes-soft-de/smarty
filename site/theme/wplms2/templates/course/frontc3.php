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


global $post;
$id= get_the_ID();

do_action('wplms_course_before_front_main');


do_action('wplms_before_course_description');
?>
<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="course_description" id="course-home">
	<?php if(!empty($post->post_excerpt) && strpos($post->post_content,$post->post_excerpt) === false){ echo '<h6>';the_excerpt(); echo '</h6>';} ?>
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

<div class="course_reviews" id="course-reviews">
<?php
	 comments_template('/course-review.php',true);
?>
</div>

