<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(vibe_get_header() ); 
global $wp_query;
$curauth = $wp_query->get_queried_object();

?>
<section id="title">
	<?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
        	<div class="col-md-3">
        		<div class="instructor-avatar">
        			<?php echo bp_core_fetch_avatar( array( 'item_id' => $curauth->data->ID,'type'=>'full', 'html' => true ) ); ?>
        		</div>
        	</div>
            <div class="col-md-6">
                <div class="pagetitle">
                	<a class="link" href="<?php echo bp_core_get_user_domain( get_the_author_meta('ID')); ?>"><?php echo sprintf(_x('View %s profile','%s is Possessive, Author\'s profile','vibe'),bp_core_get_user_displayname(get_the_author_meta('ID'))); ?></a>
                   	<div class="about_instructor">
                    	<?php 
                    		$ifield = vibe_get_option('instructor_field');
							if(!isset($ifield) || $ifield =='')$ifield='Speciality';

							$bio = vibe_get_option('instructor_about');
							if(empty($bio))$bio='Bio';
							
                    		echo '<h1>'. $curauth->display_name.'</h1>';
		                    if(bp_is_active('xprofile'))
		                    echo '<h3>'.bp_get_profile_field_data( 'field='.$ifield.'&user_id=' .$curauth->data->ID ).'</h3>'; 
	                    ?>
	                    <?php
	                    	if(bp_is_active('xprofile'))
		                    echo '<div class="instructor_bio">'.bp_get_profile_field_data( 'field='.$bio.'&user_id=' .$curauth->data->ID ).'</div>'; 
	                    ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
            	<ul class="instructor_stats">
            		<li><span class="fa fa-user"></span>&nbsp;<strong><?php echo vibe_get_instructor_student_count($curauth->data->ID); ?></strong>
            		<label><?php _e('# Students in Courses','vibe'); ?></label></li>
            		<li><?php $reviews = vibe_get_instructor_average_rating($curauth->data->ID); 
            		echo '<div class="star-rating">';
            		if(function_exists('bp_course_display_rating')){
                     	echo bp_course_display_rating($reviews);
                  	}else{
						for($i=1;$i<=5;$i++){
							if($reviews >= 1){
								echo  '<span class="fa fa-star"></span>';
							}elseif(($reviews < 1 ) && ($reviews >= 0.4 ) ){
								echo  '<span class="fa fa-star-half-o"></span>';
							}else{
								echo  '<span class="fa fa-star-o"></span>';
							}
							$reviews--;
						}
					}
					echo '</div><label>'.__('Average Rating','vibe').'</label>';
            		?></li>
            	</ul>
            </div>
        </div>
    </div>
</section>
<section id="content">
	<div id="buddypress">
    <div class="<?php echo vibe_get_container(); ?>">

		<div class="padder">

		<div class="row">
			<div class="col-md-9 col-sm-8">
			<?php

				$style = vibe_get_option('default_course_block_style');
				if(Empty($style)){$style = apply_filters('wplms_instructor_courses_style','course2');}
				if ( have_posts() ) : while ( have_posts() ) : the_post();
				global $post;
				
				echo '<div class="col-md-4 col-sm-6">'.thumbnail_generator($post,$style,'3','0',true,true).'</div>';
				endwhile;
				pagination();
				endif;
			?>
			</div>	
			<div class="col-md-3 col-sm-4">
				<?php
                    $sidebar = apply_filters('wplms_sidebar','buddypress');
                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
                <?php endif; ?>
			</div>
		</div>	
		<?php do_action( 'bp_after_directory_course' ); ?>

		</div><!-- .padder -->
	
	<?php do_action( 'bp_after_directory_course_page' ); ?>
</div><!-- #content -->
</div>
</section>

<?php get_footer( vibe_get_footer() );  
