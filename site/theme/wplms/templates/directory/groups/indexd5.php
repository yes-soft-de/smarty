<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$id= vibe_get_bp_page_id('groups');
?>

<?php do_action( 'bp_before_directory_groups_page' ); ?>

<section id="title">
	<?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="pagetitle center">
                    <h1><?php echo get_the_title($id); ?></h1>
                    <?php the_sub_title($id); ?>
                    <div id="group-dir-search" class="dir-search" role="search">
						<?php bp_directory_groups_search_form(); ?>
					</div><!-- #group-dir-search -->
                </div>
            </div>
        </div>
    </div>
</section>
	
<section id="content">
	<div id="buddypress" class="directory5">
	    <div class="<?php echo vibe_get_container(); ?>">
	    	<div class="padder">
				<?php do_action( 'bp_before_directory_groups' ); ?>

					<form action="" method="post" id="groups-directory-form" class="dir-form">
						<div class="row">
							<div class="col-md-12">
								<?php do_action( 'bp_before_directory_groups_content' ); ?>
								<?php do_action( 'template_notices' ); ?>
							</div>
						</div>	
						<div class="row">
							<div class="col-md-12">
								<div class="item-list-tabs" role="navigation">
									<ul>
										<li class="selected" id="groups-all"><a href="<?php echo trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() ); ?>"><?php printf( __( 'All Groups <span>%s</span>', 'vibe' ), bp_get_total_group_count() ); ?></a></li>

										<?php if ( is_user_logged_in() && bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) : ?>

											<li id="groups-personal"><a href="<?php echo trailingslashit( bp_loggedin_user_domain() . bp_get_groups_slug() . '/my-groups' ); ?>"><?php printf( __( 'My Groups <span>%s</span>', 'vibe' ), bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ); ?></a></li>

										<?php endif; ?>

										<?php do_action( 'bp_groups_directory_group_filter' ); ?>

									</ul>
								</div><!-- .item-list-tabs -->
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
						</div>
					</form><!-- #groups-directory-form -->

						<?php do_action( 'bp_after_directory_groups' ); ?>

					</div><!-- .padder -->
				</div><!-- #container -->
			</div>
</section>								
<?php do_action( 'bp_after_directory_groups_page' ); ?>