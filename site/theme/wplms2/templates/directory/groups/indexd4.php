<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$id= vibe_get_bp_page_id('groups');

$header = vibe_get_customizer('header_style');
if($header == 'transparent' || $header == 'generic'){
    echo '<section id="title"><div class="container"><div class="pagetitle"><h1>'.get_the_title($id).'</h1></div></div></section>';
}
?>

<?php do_action( 'bp_before_directory_groups_page' ); ?>

	
<section id="content">
	<div id="buddypress">
	    <div class="<?php echo vibe_get_container(); ?>">
	    	<div class="padder">
				<?php do_action( 'bp_before_directory_groups' ); ?>

					<form action="" method="post" id="groups-directory-form" class="dir-form">
						<div class="row">
							<div class="col-md-9 col-sm-9 col-md-push-3 col-sm-push-3">
								<div class="pagetitle">
									<div class="col-md-8">
										<h1><?php echo get_the_title($id); ?></h1>
				                    	<?php the_sub_title($id); ?>
				                	</div>
				                	<div class="col-md-4">
										<div class="dir-search" role="search">
											<?php bp_directory_groups_search_form(); ?>
										</div>
									</div>
				                </div>

				                <?php do_action( 'bp_before_directory_groups_content' ); ?>
								<?php do_action( 'template_notices' ); ?>

								<div class="item-list-tabs" id="subnav" role="navigation">
									<ul>

										<?php do_action( 'bp_groups_directory_group_types' ); ?>
										<li class="switch_view">
											<div class="grid_list_wrapper">
												<a id="list_view" class="active"><i class="icon-list-1"></i></a>
												<a id="grid_view"><i class="icon-grid"></i></a>
											</div>
										</li>
										<li id="groups-order-select" class="last filter">

											<label for="groups-order-by"><?php _e( 'Order By:', 'vibe' ); ?></label>
											<select id="groups-order-by">
												<option value="active"><?php _e( 'Last Active', 'vibe' ); ?></option>
												<option value="popular"><?php _e( 'Most Members', 'vibe' ); ?></option>
												<option value="newest"><?php _e( 'Newly Created', 'vibe' ); ?></option>
												<option value="alphabetical"><?php _e( 'Alphabetical', 'vibe' ); ?></option>

												<?php do_action( 'bp_groups_directory_order_options' ); ?>

											</select>
										</li>
									</ul>
								</div>

								<div id="groups-dir-list" class="groups dir-list">

									<?php locate_template( array( 'groups/groups-loop.php' ), true ); ?>

								</div><!-- #groups-dir-list -->

								<?php do_action( 'bp_directory_groups_content' ); ?>

								<?php wp_nonce_field( 'directory_groups', '_wpnonce-groups-filter' ); ?>

								<?php do_action( 'bp_after_directory_groups_content' ); ?>
							</div>	
							<div class="col-md-3 col-sm-3  col-md-pull-9 col-sm-pull-9">
								<div class="item-list-tabs" role="navigation">
									<ul>
										<li class="selected" id="groups-all"><a href="<?php echo trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() ); ?>"><?php printf( __( 'All Groups <span>%s</span>', 'vibe' ), bp_get_total_group_count() ); ?></a></li>

										<?php if ( is_user_logged_in() && bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) : ?>

											<li id="groups-personal"><a href="<?php echo trailingslashit( bp_loggedin_user_domain() . bp_get_groups_slug() . '/my-groups' ); ?>"><?php printf( __( 'My Groups <span>%s</span>', 'vibe' ), bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ); ?></a></li>

										<?php endif; ?>

										<?php do_action( 'bp_groups_directory_group_filter' ); ?>

									</ul>
								</div><!-- .item-list-tabs -->
								<?php if ( is_user_logged_in() && bp_user_can_create_groups() && $flag ) : ?> 
									<a class="button create-group-button full" href="<?php echo trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/create' ); ?>"><?php _e( 'Create a Group', 'vibe' ); ?></a>
								<?php endif; ?>
								<div class="buddysidebar">
									<?php
										$sidebar = apply_filters('wplms_sidebar','buddypress',$id);
						                if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
					               	<?php endif; ?>
								</div>
							</div>
						</div>
					</form><!-- #groups-directory-form -->

						<?php do_action( 'bp_after_directory_groups' ); ?>

					</div><!-- .padder -->
				</div><!-- #container -->
			</div>
</section>								
<?php do_action( 'bp_after_directory_groups_page' ); ?>