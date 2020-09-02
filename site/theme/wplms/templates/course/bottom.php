<?php
if ( ! defined( 'ABSPATH' ) ) exit;

do_action('wplms_single_course_content_end');
?>
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