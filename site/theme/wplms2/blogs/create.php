<?php

/**
 * BuddyPress - Create Blog
 *
 * @package BuddyPress
 * @subpackage bp-default
 */
if ( !defined( 'ABSPATH' ) ) exit;

$capablity=vibe_get_option('group_create');
if(isset($capablity)){
	switch($capablity){
		case 2: 
			if(!current_user_can('edit_posts'))
			wp_die(__('Only Instructors can create sites','vibe'),'Access Denied',array(500,true));
		break;
		case 3:
			if(!current_user_can('manage_options'))
			wp_die(__('Only Admins can create sites','vibe'),'Access Denied',array(500,true));
		break;
	}

}


get_header( vibe_get_header() ); ?>


<?php do_action( 'bp_before_create_blog_content_template' ); ?>
<section id="blogtitle">
	<?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-9 col-sm-8">
                <div class="pagetitle">
                    <h1><?php the_title(); ?></h1>
                    <?php the_sub_title(); ?>
                </div>
            </div>
            <div class="col-md-3 col-sm-4">
            	<?php if ( is_user_logged_in() && bp_user_can_create_groups() ) : ?> 
					<a class="button" href="<?php echo trailingslashit( bp_get_root_domain() . '/' . bp_get_blogs_root_slug() ); ?>"><?php _e( 'Site Directory', 'vibe' ); ?></a>
				<?php endif; ?>
            </div>
        </div>
    </div>
</section>
<section id="content">
	<div id="buddypress">
	    <div class="<?php echo vibe_get_container(); ?>">
	    	<div class="padder">
			<?php do_action( 'bp_before_create_blog_content' ); ?>
			<div class="row">	
				<div class="col-md-9 col-sm-8">	
					<?php do_action( 'template_notices' ); ?>		
					<?php if ( bp_blog_signup_enabled() ) : ?>

						<?php bp_show_blog_signup_form(); ?>

					<?php else: ?>

						<div id="message" class="info">
							<p><?php _e( 'Site registration is currently disabled', 'vibe' ); ?></p>
						</div>

					<?php endif; ?>

					<?php do_action( 'bp_after_create_blog_content' ); ?>
					
					<?php do_action( 'bp_after_create_blog_content_template' ); ?>
				</div>
				<div class="col-md-3 col-sm-4">
					<?php get_sidebar( 'buddypress' ); ?>
				</div>	
			</div>	
			</div><!-- .padder -->
			<?php do_action( 'bp_after_directory_blogs_content' ); ?>
		</div>	
	</div><!-- #content -->
</section>	
</div>

<?php get_footer( vibe_get_footer() );  