<?php

if ( ! defined( 'ABSPATH' ) ) exit;
$id= vibe_get_bp_page_id('members');
?>

<section id="title">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
             <div class="col-md-9 col-sm-8">
                <div class="pagetitle">
                	<?php echo vibe_breadcrumbs(); ?>
                    <h1><?php echo get_the_title($id); ?></h1>
                    <?php the_sub_title($id); ?>
                </div>
            </div>
             <div class="col-md-3 col-sm-4">
            	<?php 
            		do_action('wplms_be_instructor_button');	
				?>
            </div>
        </div>
    </div>
</section>
<section id="content">
	<div id="buddypress">
    <div class="<?php echo vibe_get_container(); ?>">
		<?php do_action( 'bp_before_directory_members_page' ); ?>
		<div class="padder">
			<?php do_action( 'bp_before_directory_members' ); ?>
				<form action="" method="post" id="members-directory-form" class="dir-form">
				<div class="row">	
					<?php do_action( 'bp_before_directory_members_tabs' ); ?>
					<div class="col-md-9 col-sm-8">	
						<div class="item-list-tabs" role="navigation">
							<ul>
								<li class="selected" id="members-all"><a href="<?php echo trailingslashit( bp_get_root_domain() . '/' . bp_get_members_root_slug() ); ?>"><?php printf( __( 'All Members <span>%s</span>', 'vibe' ), bp_get_total_member_count() ); ?></a></li>

								<?php if ( is_user_logged_in() && bp_is_active( 'friends' ) && bp_get_total_friend_count( bp_loggedin_user_id() ) ) : ?>

									<li id="members-personal"><a href="<?php echo bp_loggedin_user_domain() . bp_get_friends_slug() . '/my-friends/' ?>"><?php printf( __( 'My Friends <span>%s</span>', 'vibe' ), bp_get_total_friend_count( bp_loggedin_user_id() ) ); ?></a></li>

								<?php endif; ?>
								
								<?php do_action( 'bp_members_directory_member_types' ); ?>

							</ul>
						</div><!-- .item-list-tabs -->
						<div class="item-list-tabs" id="subnav" role="navigation">
							<ul>
								<li>
									<div id="member-dir-search" class="dir-search" role="search">
										<?php bp_directory_members_search_form(); ?>
									</div><!-- #group-dir-search -->
								</li>
								<li class="switch_view">
									<div class="grid_list_wrapper">
										<a id="list_view" class="active"><i class="icon-list-1"></i></a>
										<a id="grid_view"><i class="icon-grid"></i></a>
									</div>
								</li>
								<?php do_action( 'bp_members_directory_member_sub_types' ); ?>

								<li id="members-order-select" class="last filter">

									<label for="members-order-by"><?php _e( 'Order By:', 'vibe' ); ?></label>
									<select id="members-order-by">
									<?php $default_selection = apply_filters('wplms_members_default_order',''); ?>
										<option value="active" <?php selected('active',$default_selection); ?>><?php _e( 'Last Active', 'vibe' ); ?></option>
										<option value="newest" <?php selected('newest',$default_selection); ?>><?php _e( 'Newest Registered', 'vibe' ); ?></option>

										<?php if ( bp_is_active( 'xprofile' ) ) : ?>

											<option value="alphabetical" <?php selected('alphabetical',$default_selection); ?>><?php _e( 'Alphabetical', 'vibe' ); ?></option>

										<?php endif; ?>

										<?php do_action( 'bp_members_directory_order_options' ); ?>

									</select>
								</li>
							</ul>
						</div>

						<div id="members-dir-list" class="members dir-list">

							<?php locate_template( array( 'members/members-loop.php' ), true ); ?>

						</div><!-- #members-dir-list -->

						<?php do_action( 'bp_directory_members_content' ); ?>

						<?php wp_nonce_field( 'directory_members', '_wpnonce-member-filter' ); ?>

						<?php do_action( 'bp_after_directory_members_content' ); ?>
					</div>	
					<div class="col-md-3 col-sm-4">
						<?php
							$sidebar = apply_filters('wplms_sidebar','buddypress',$id);
			                if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
		               	<?php endif; ?>
					</div>
				</div>	
			</form><!-- #members-directory-form -->
		<?php do_action( 'bp_after_directory_members' ); ?>
		</div><!-- .padder -->
	</div><!-- #content -->
	<?php do_action( 'bp_after_directory_members_page' ); ?>
	</div>
</section>	
</div>