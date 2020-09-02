<?php

//$post_type = $_GET['sub'];
// $query = new WP_Query(array(
// 	'post_type'=>$post_type,
// 	'posts_per_page'=>1,
// ));
// $post_values = array();
// if($query->have_posts()){
// 	while($query->have_posts()){
// 		$query->the_post();
// 		$post_values[] = array(
// 			'ID'=>get_the_ID(),
// 			'featured_image'=>get_the_post_thumbnail(),
// 			'post_title'=>get_the_title(),
// 		);
// 	}
// }


?>
<div id="card_builder">
	<div class="card_options">
		<div class="window_layout">
			<div class="layout_options">
				<div class="option desktop active">
					<span class="vicon vicon-desktop"></span>
					<span><?php _e('Desktop','vibebp'); ?></span>
				</div>
				<div class="option tablet">
					<span class="vicon vicon-tablet"></span>
					<span><?php _e('Tablet','vibebp'); ?></span>
				</div>
				<div class="option mobile">
					<span class="vicon vicon-mobile"></span>
					<span><?php _e('Mobile','vibebp'); ?></span>
				</div>
			</div>
		</div>
		<div class="layout_options">
			<div class="option" draggable="true" data-type="column" data-default="1" data-value="1">
				<span class="vicon vicon-layout-media-left-alt"></span>
				<span><?php _e('One columns','vibebp'); ?></span>
			</div>
			<div class="option" draggable="true" data-type="column" data-default="1-1" data-value="2">
				<span class="vicon vicon-layout-column2"></span>
				<span><?php _e('Two columns','vibebp'); ?></span>
			</div>
			<div class="option" draggable="true" data-type="column" data-default="1-1-1" data-value="3">
				<span class="vicon vicon-layout-column3"></span>
				<span><?php _e('Three columns','vibebp'); ?></span>
			</div>
			<div class="option" draggable="true" data-type="column" data-default="1-2" data-value="2">
				<span class="vicon vicon-layout-sidebar-left"></span>
				<span><?php _e('One-Two columns','vibebp'); ?></span>
			</div>
			<div class="option" draggable="true" data-type="column" data-default="2-1" data-value="2">
				<span class="vicon vicon-layout-sidebar-right"></span>
				<span><?php _e('Two-One columns','vibebp'); ?></span>
			</div>
			<div class="option" draggable="true" data-type="column" data-default="2-3" data-value="2">
				<span class="vicon vicon-layout-sidebar-left"></span>
				<span><?php _e('Two-Three columns','vibebp'); ?></span>
			</div>
			<div class="option" draggable="true" data-type="column" data-default="3-2" data-value="2">
				<span class="vicon vicon-layout-sidebar-right"></span>
				<span><?php _e('Two-Three columns','vibebp'); ?></span>
			</div>
			<div class="option" draggable="true" data-type="column" data-default="1-1-1-1" data-value="4">
				<span class="vicon vicon-layout-column4"></span>
				<span><?php _e('Four columns','vibebp'); ?></span>
			</div>
			<div class="option" draggable="true" data-type="column" data-default="1-2-1" data-value="3">
				<span class="vicon vicon-layout-sidebar-2"></span>
				<span><?php _e('One-Two-One columns','vibebp'); ?></span>
			</div>
		</div>
		<div class="element_options">
			<div class="option" draggable="true" data-type="image" data-id="featured_image" data-default="<?php echo plugins_url('../assets/images/avatar.jpg',__FILE__); ?>" data-value="<?php  ?>" >
				<span class="vicon vicon-image"></span>
				<span><?php _e('Featured Image','vibebp'); ?></span>
			</div>
			<?php
				
				
			?>
		</div>
	</div>
	<div class="card_builder_preview">
		<div class="card_builder_Wrapper">
			<h3><?php _e('Card Builder','vibebp'); ?></h3>
			<div class="card_builder"></div>
			<a class="button-primary save_member_card"><?php _e('Save','vibebp'); ?></a>
		</div>
		<div class="card_preview_wrapper">
			<h3><?php _e('Card Preview','vibebp'); ?></h3>
			<div class="card_wrapper card_preview"></div>
		</div>
</div>
<?php
wp_enqueue_style('vibebp-card-builder-css',plugins_url('../../assets/backend/css/main.css',__FILE__),array('vibebp-icons-css'),VIBEBP_VERSION);
wp_enqueue_script('vibebp-card-builder-js',plugins_url('../../assets/backend/lib/post_card_builder.js',__FILE__),array('jquery'),VIBEBP_VERSION);