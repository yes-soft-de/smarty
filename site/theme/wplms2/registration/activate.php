<?php 

if ( !defined( 'ABSPATH' ) ) exit;

$activate_id = vibe_get_bp_page_id('activate');
get_header( vibe_get_header() ); ?>

<section id="title">
	<?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="pagetitle">
                    <?php
                        $breadcrumbs=get_post_meta($activate_id,'vibe_breadcrumbs',true);
                        if(vibe_validate($breadcrumbs)){
                         vibe_breadcrumbs();
                        }
                    ?>
                    <h1><?php the_title(); ?></h1>
                    <?php the_sub_title($activate_id); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="content">
	<div class="<?php echo vibe_get_container(); ?>">
		<div class="col-md-9 col-sm-8">
		
		<div class="content padder">

		<?php do_action( 'bp_before_activation_page' ); ?>

		<div class="page" id="activate-page">

			<h3><?php if ( bp_account_was_activated() ) :
				_e( 'Account Activated', 'vibe' );
			else :
				_e( 'Activate your Account', 'vibe' );
			endif; ?></h3>

			<?php do_action( 'template_notices' ); ?>

			<?php do_action( 'bp_before_activate_content' ); ?>

			<?php if ( bp_account_was_activated() ) : ?>

				<?php if ( isset( $_GET['e'] ) ) : ?>
					<p><?php _e( 'Your account was activated successfully! Your account details have been sent to you in a separate email.', 'vibe' ); ?></p>
				<?php else : ?>
					<p><?php printf( __( 'Your account was activated successfully! You can now <a href="%s">log in</a> with the username and password you provided when you signed up.', 'vibe' ), wp_login_url( bp_get_root_domain() ) ); ?></p>
				<?php endif; ?>

			<?php else : ?>

				<p><?php _e( 'Please provide a valid activation key.', 'vibe' ); ?></p>

				<form action="" method="get" class="standard-form" id="activation-form">

					<label for="key"><?php _e( 'Activation Key:', 'vibe' ); ?></label>
					<input type="text" name="key" id="key" value="" />

					<p class="submit">
						<input type="submit" name="submit" class="button" value="<?php _e( 'Activate', 'vibe' ); ?>" />
					</p>

				</form>

			<?php endif; ?>

			<?php do_action( 'bp_after_activate_content' ); ?>

		</div><!-- .page -->

		<?php do_action( 'bp_after_activation_page' ); ?>

		</div><!-- .padder -->
	</div>
	<div class="col-md-3 col-sm-4">
		<div class="sidebar">
			<?php
		 		$sidebar = apply_filters('wplms_sidebar','buddypress',$activate_id);
                if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
           	<?php endif; ?>
		</div>
	</div>
</div>	
</section><!-- #content -->


<?php get_footer( vibe_get_footer() );  