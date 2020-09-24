					<?php do_action( 'bp_after_group_home_content' ); ?>
				</div>
				<div class="col-md-3">
					<div class="sidebar">
	                    <?php
	                    $sidebar = apply_filters('wplms_sidebar','buddypress');
	                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
	                    <?php endif; ?>
	    			</div>
				</div>
			</div><!-- .padder -->
		</div><!-- #container -->
	</div>
</section>	