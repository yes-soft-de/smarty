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
			<div class="option" draggable="true" data-type="image" data-id="profile_pic" data-default="<?php echo plugins_url('../assets/images/avatar.jpg',__FILE__); ?>" data-value="<?php echo bp_core_fetch_avatar(array(
                'item_id' 	=> get_current_user_id(),
                'object'  	=> 'user',
                'type'		=>'full',
                'html'    	=> false
            )); ?>" >
				<span class="vicon vicon-image"></span>
				<span><?php _e('Profile Pic','vibebp'); ?></span>
			</div>
			<?php
				if(bp_is_active('friends')){
					?>
					<div class="option" draggable="true" data-type="profile_data" data-id="friend_count" data-default="<?php _e('Friends','vibebp'); ?>" data-value="5" >
						<span class="vicon vicon-face-smile"></span>
						<span><?php _e('Friends Count','vibebp'); ?></span>
					</div>
					<?php
				}

				if(bp_is_active('groups')){
					?>
					<div class="option" draggable="true" data-type="profile_data" data-id="group_count" data-default="<?php _e('Groups','vibebp'); ?>" data-value="5" >
						<span class="vicon vicon-user"></span>
						<span><?php _e('Groups Count','vibebp'); ?></span>
					</div>
					<?php
				}

				if(vibebp_get_setting('bp_followers','bp')){
					?>
					<div class="option" draggable="true" data-type="profile_data" data-id="follower_count" data-default="<?php _e('Followers','vibebp'); ?>" data-value="5" >
						<span class="vicon vicon-comments-smiley"></span>
						<span><?php _e('Follower Count','vibebp'); ?></span>
					</div>
					<div class="option" draggable="true" data-type="profile_data" data-id="following_count" data-default="<?php _e('Following','vibebp'); ?>" data-value="6" >
						<span class="vicon vicon-comments-smiley"></span>
						<span><?php _e('Following Count','vibebp'); ?></span>
					</div>
					<?php
				}

				//For more data and custom add changes in class-api-members-controller.php for front display
				do_action('vibebp_member_card_element_options');

				$groups = array();
				$fields = array();
				if(function_exists('bp_xprofile_get_groups')){
					$groups = bp_xprofile_get_groups( array(
						'fetch_fields' => true
					) );
				}
				

				if(!empty($groups)){
					foreach($groups as $group){
						if(!empty($group->fields)){
							foreach ( $group->fields as $field ) {
								$val = xprofile_get_field_data( $field->id, get_current_user_id());

								$fields[]=array(
									'id'=>$field->id,
									'name'=>$field->name,
									'type'=>$field->type,
									'value'=> $val
								);
							}
						}
					}
				}
				if(!empty($fields)){
					$member_card = get_option('member_card');
					
					foreach($fields as $field){
						echo '<div class="option" draggable="true" data-id="'.$field['id'].'" data-type="field" data-default="'.$field['name'].'" data-value="'.$field['id'].'">
								<span class="vicon vicon-move"></span>
								<span>'.$field['name'].'</span>
							</div>';
					}

					echo '<script>var bpfields = '.json_encode($fields).';</script>';
					if(!empty($member_card)){
						echo '<script>var mainCard ='.stripslashes($member_card).';</script>';
					}
					echo '<script>var card_styles = '.json_encode(array(
						array('key'=>'vbpsmall','label'=>__('Small','vibebp')),
						array('key'=>'vbplarge','label'=>__('Large','vibebp')),
						array('key'=>'vbptag','label'=>__('Tag','vibebp')),
						array('key'=>'vbpdescription','label'=>__('Description','vibebp')),
						array('key'=>'vbplabel_stacked','label'=>__('Label Stacked','vibebp')),
						array('key'=>'vbplabel_spaced','label'=>__('Label Spaced','vibebp')),
					)).'</script>';
				}
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
wp_enqueue_script('vibebp-card-builder-js',plugins_url('../../assets/backend/lib/card_builder.js',__FILE__),array('jquery'),VIBEBP_VERSION);