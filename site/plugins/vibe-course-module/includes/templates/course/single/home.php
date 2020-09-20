<?php 
/**
 * The template for displaying Course home
 *
 * Override this template by copying it to yourtheme/course/single/home.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.1
 */

	get_header( 'buddypress' );
?>
<section id="content">
	<div id="buddypress">
	    <div class="container">
	        <div class="row">
	            <div class="col-md-3 col-sm-3">
					<?php if ( bp_course_has_items() ) : while ( bp_course_has_items() ) : bp_course_the_item(); ?>

					<?php do_action( 'bp_before_course_home_content' ); ?>

					<div id="item-header" role="complementary">

						<?php locate_template( array( 'course/single/course-header.php' ), true ); ?>

					</div><!-- #item-header -->
			
				<div id="item-nav">
					<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
						<ul>
							<?php bp_get_options_nav(); ?>
							<?php

							if(function_exists('bp_course_nav_menu'))
								bp_course_nav_menu();
							else{
							?>	
							<li id="home" class="<?php echo (!isset($_GET['action'])?'selected':''); ?>"><a href="<?php bp_course_permalink(); ?>"><?php  _e( 'Home', 'vibe' ); ?></a></li>
							<li id="curriculum" class="<?php echo (($_GET['action']=='curriculum')?'selected':''); ?>"><a href="<?php bp_course_permalink(); ?>?action=curriculum"><?php  _e( 'Curriculum', 'vibe' ); ?></a></li>
							<li id="members" class="<?php echo (($_GET['action']=='members')?'selected':''); ?>"><a href="<?php bp_course_permalink(); ?>?action=members"><?php  _e( 'Members', 'vibe' ); ?></a></li>
							
							<?php
							}
							$vgroup=get_post_meta(get_the_ID(),'vibe_group',true);
							if(isset($vgroup) && $vgroup){
								$group=groups_get_group(array('group_id'=>$vgroup));
							?>
							<li id="group"><a href="<?php echo bp_get_group_permalink($group); ?>"><?php  _e( 'Group', 'vibe' ); ?></a></li>
							<?php
							}
							$forum=get_post_meta(get_the_ID(),'vibe_forum',true);
							if(isset($forum) && $forum){
							?>
							<li id="forum"><a href="<?php echo get_permalink($forum); ?>"><?php  _e( 'Forum', 'vibe' ); ?></a></li>
							<?php 
							}
							if(is_super_admin() || is_instructor()){
								?>
								<li id="admin" class="<?php echo ((isset($_GET['action']) && $_GET['action']=='admin')?'selected':''); ?>"><a href="<?php bp_course_permalink(); ?>?action=admin"><?php  _e( 'Admin', 'vibe' ); ?></a></li>
								<?php
							}
							?>
							<?php do_action( 'bp_course_options_nav' ); ?>
						</ul>
					</div>
				</div><!-- #item-nav -->
			</div>
			<div class="col-md-6 col-sm-6">	
			<?php do_action( 'template_notices' ); ?>
			<div id="item-body">

				<?php 
				
				do_action( 'bp_before_course_body' );

				/**
				 * Does this next bit look familiar? If not, go check out WordPress's
				 * /wp-includes/template-loader.php file.
				 *
				 * @todo A real template hierarchy? Gasp!
				 */

				if(isset($_GET['action']) && $_GET['action']):

					switch($_GET['action']){
						case 'curriculum':
							locate_template( array( 'course/single/curriculum.php'  ), true );
						break;
						case 'members':
							locate_template( array( 'course/single/members.php'  ), true );
						break;
						case 'events':
							locate_template( array( 'course/single/events.php'  ), true );
						break;
						case 'admin':
							$uid = bp_loggedin_user_id();
							$authors=array($post->post_author);
							$authors = apply_filters('wplms_course_instructors',$authors,$post->ID);
							
							if(current_user_can( 'manage_options' ) || in_array($uid,$authors)){
								locate_template( array( 'course/single/admin.php'  ), true );	
							}else{
								locate_template( array( 'course/single/front.php' ) );
							}
						break;
						default:
							locate_template( array( 'course/single/front.php' ) );
					}
					do_action('wplms_load_templates');
				else :
					
					if ( isset($_POST['review_course']) && isset($_POST['review']) && wp_verify_nonce($_POST['review'],get_the_ID()) ){
						 global $withcomments;
					      $withcomments = true;
					      comments_template('/course-review.php',true);
					}else if(isset($_POST['submit_course']) && isset($_POST['review']) && wp_verify_nonce($_POST['review'],get_the_ID())){ // Only for Validation purpose

						bp_course_check_course_complete();
						$user_id=get_current_user_id();
						do_action('wplms_submit_course',$post->ID,$user_id);
						
					// Looking at home location
					}else if ( bp_is_course_home() ){

						// Use custom front if one exists
						$custom_front = locate_template( array( 'course/single/front.php' ) );
						if     ( ! empty( $custom_front   ) ) : load_template( $custom_front, true );
						
						elseif ( bp_is_active( 'structure'  ) ) : locate_template( array( 'course/single/structure.php'  ), true );

						// Otherwise show members
						elseif ( bp_is_active( 'members'  ) ) : locate_template( array( 'course/single/members.php'  ), true );

						endif;

					// Not looking at home
					}else {

						// Course Admin/Instructor
						if     ( bp_is_course_admin_page() ) : locate_template( array( 'course/single/admin.php'        ), true );

							// Course Members
						elseif ( bp_is_course_members()    ) : locate_template( array( 'course/single/members.php'      ), true );

						// Anything else (plugins mostly)
						else                                : locate_template( array( 'course/single/plugins.php'      ), true );

						endif;
					}
				endif;
					
				do_action( 'bp_after_course_body' ); ?>

			</div><!-- #item-body -->

			<?php do_action( 'bp_after_course_home_content' ); ?>

			<?php endwhile; endif; ?>
			</div>
			<div class="col-md-3 col-sm-3">	
				<div class="widget pricing">
					<?php the_course_button(); ?>
					<?php the_course_details(); ?>
				</div>

			 	<?php
			 		$sidebar = apply_filters('wplms_sidebar','coursesidebar',get_the_ID());
	                if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
               	<?php endif; ?>
			</div>
		</div><!-- .padder -->
		
	</div><!-- #container -->
	</div>
</section>	
<?php get_footer( 'buddypress' ); ?>