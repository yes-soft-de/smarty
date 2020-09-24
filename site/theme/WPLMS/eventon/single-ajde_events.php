<?php
if ( !defined( 'ABSPATH' ) ) exit;
global $eventon;
if(!empty($eventon->version) && version_compare($eventon->version, '2.5.4')<0){
/*
 *	The template for displaying single event
 *
 *	Override this tempalte by coping it to ....yourtheme/eventon/single-ajde_events.php
 *	This template is built based on wordpress twentythirteen theme standards and may not fit your custom
 *	theme correctly, in which case you may have to add custom styles to fix style issues
 *
 *	@Author: AJDE
 *	@EventON
 *	@version: 2.4.8
 */



if(!class_exists('evo_sinevent') && !empty($eventon->version) && version_compare($eventon->version, '2.4.9') < 0)
wp_die('Please update your eventon plugin to latest version','vibe');

get_header(vibe_get_header());
$oneevent = new evo_sinevent();

	//do_action('eventon_before_main_content');
	
?>
<?php /* The loop */ ?>
<?php while ( have_posts() ) : the_post(); ?>
	<?php
	$title=get_post_meta(get_the_ID(),'vibe_title',true);

	if(!isset($title) || !$title || (vibe_validate($title))){

	?>
	<section id="title">
		<?php do_action('wplms_before_title'); ?>
	    <div class="<?php echo vibe_get_container(); ?>">
	        <div class="row">
	            <div class="col-md-12">
	                <div class="pagetitle">
	                    <?php 
	                        $breadcrumbs=get_post_meta(get_the_ID(),'vibe_breadcrumbs',true);
	                        if(!isset($breadcrumbs) || !$breadcrumbs || vibe_validate($breadcrumbs)){
	                            vibe_breadcrumbs();
	                        }   
	                    ?>
	                    <h1><?php the_title(); ?></h1>
	                    <?php the_sub_title(); ?>
	                </div>
	            </div>
	        </div>
	    </div>
	</section>
	<?php
	}
	?>
<section id="content">
	<div class="<?php echo vibe_get_container(); ?>">
		<div class="row">
			<div class="col-md-9 col-sm-8">
			
				<div id='main'>
					<div class='evo_page_body'>
						<div class='evo_page_content <?php echo ($oneevent->has_evo_se_sidebar())? 'evo_se_sidarbar':null;?>'>
							
								
								<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

									<div class="entry-content">

									<?php	


										$oneevent->page_content();
										
										/* use this if you move the content-single-event.php else where along this file*/
										//require_once('content-single-event.php');



									?>		
									</div><!-- .entry-content -->

									<footer class="entry-meta">
										<?php edit_post_link( __( 'Edit', 'vibe' ), '<span class="edit-link">', '</span>' ); ?>
									</footer><!-- .entry-meta -->
								</article><!-- #post -->
							

						</div><!-- #content -->
					</div><!-- #primary -->
					<div class="clear"></div>
					<?php
					if(!empty($eventon->version) && version_compare($eventon->version, '2.5.3')>=0) 
						comments_template();
					?>
				</div>
				<?php 	//do_action('eventon_after_main_content'); ?>
			
			</div>
			<div class="col-md-3 col-sm-4">
                <div class="sidebar">
					<?php $oneevent->sidebar(); ?>
			   	</div>
            </div>
		</div>
		<?php endwhile; ?>
    </div>
</section>
	
<?php
get_footer(vibe_get_footer());


}else{
/*
 *	The template for displaying single event
 *
 *	Override this tempalte by coping it to ....yourtheme/eventon/single-ajde_events.php
 *	This template is built based on wordpress twentythirteen theme standards and may not fit your custom
 *	theme correctly, in which case you may have to add custom styles to fix style issues
 *
 *	@Author: AJDE
 *	@EventON
 *	@version: 2.5.4
 */

	
//do_action('eventon_before_main_content');
	
get_header(vibe_get_header());



?>
<section id="title">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="pagetitle">
                    <?php  vibe_breadcrumbs(); ?>
                    <h1><?php the_title(); ?></h1>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="content">
	<div class="<?php echo vibe_get_container(); ?>">
		<div class="row">
			<div class="col-md-9 col-sm-8">
				<div id='main'>
					<div class='evo_page_body'>

						<?php do_action('eventon_single_content_wrapper');?>

							<?php /* The loop */ ?>
							<?php while ( have_posts() ) : the_post(); ?>

								<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

									<div class="entry-content">

									<?php	
										do_action('eventon_single_content');
									?>		
									</div><!-- .entry-content -->

									<footer class="entry-meta">
										<?php edit_post_link( __( 'Edit', 'twentythirteen' ), '<span class="edit-link">', '</span>' ); ?>
									</footer><!-- .entry-meta -->
								</article><!-- #post -->
							<?php endwhile; ?>

					</div><!-- #content -->
					</div><!--have to put extra closing div coz of #eventon bad template-->
				</div><!-- #primary -->
			</div>			
			<div class="col-md-3 col-sm-4">
				<?php
						
						do_action('eventon_single_after_loop');

					?>
			</div>
		</div>
	</div>
</section>
<?php 	//do_action('eventon_after_main_content'); ?>
<?php 
get_footer(vibe_get_footer());
}
?>