<?php


Class WPLMS_oAuth_Apps{


	public static $instance;
	
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_oAuth_Apps();
        return self::$instance;
    }

	private function __construct(){
		
		$this->wplms_api_tracker = '';
		$this->global_updates = '';
	}
	
	function clients(){
		
		$this->show_clients();
	}

	

	function display(){
		$this->get_apps();
		$this->handle_save();
		$this->new_app_form();
		$this->show_apps();
	}

	function get_apps(){
		if(empty($this->apps)){
			$this->apps = get_option('wplms_apps');
		}
	}

	function remove_update(){
		if(empty($_POST['update_actions']) || empty($_POST['id']) || !wp_verify_nonce('update_actions',$_POST['update_actions'])){
			echo _x('Security check failed','','vibe-customtypes');
			die();
		}
			
		if(!empty($_POST['id'])){
			$this->get_global_updates();
			if(!empty($this->wplms_api_tracker) && empty($this->global_updates)){
				if(array_key_exists($_POST['id'], $this->global_updates)){
					unset($this->global_updates[$_POST['id']]);
					$this->wplms_api_tracker['updates'] = $this->global_updates;
					update_option('wplms_api_tracker',$this->wplms_api_tracker);
				}

			}
		}
		die();
	}

	function get_global_updates(){
		$this->global_updates = array();
		if(empty($this->wplms_api_tracker) && empty($this->global_updates)){
			$this->wplms_api_tracker = get_option('wplms_api_tracker');
			if(isset($this->wplms_api_tracker['updates']))
			$this->global_updates = $this->wplms_api_tracker['updates'];
		}
	}

	function send_updates(){
		$this->get_global_updates();
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		?>
		<a class="send_update_toggle button button-primary"><?php echo _x('Send New Update','send update button ','vibe-customtypes')?></a><br/>
		<div id="update_to_all_form">
			<a class="hide_update_form"><span class="dashicons dashicons-no"></span></a>
			<h4><?php echo _x('Send update to all app users.','','vibe-customtypes');?></h4>
			<?php wp_nonce_field('update_to_all','update_to_all');?>
			<textarea cols="60" rows="5" name="update_content" class="update_content" placeholder="<?php echo _x('Your update content.','contane label','vibe-customtypes')?>"></textarea><br/>
			<label class="min"><?php echo _x('Html tags supported.','contane label','vibe-customtypes')?></label><br/><br/>
			<a class="send_update button button-primary"><?php echo _x('Send Update','send update button ','vibe-customtypes')?></a>
		</div>
		<?php
		if(!empty($this->global_updates)){
			wp_nonce_field('update_actions','update_actions');
			echo '<h4>'._x('Sent Updates','','vibe-customtypes').'</h4>';
			echo '<table class="wp-list-table widefat fixed striped wp_list_test_links">
				<thead>
				<tr>
					<th scope="col" id="" class="manage-column column-name column-primary">'._x('Content','content','vibe-customtypes').'</th><th scope="col" id="description" class="manage-column column-description">'._x('Time','app table label','vibe-customtypes').'</th><th scope="col" id="client_id" class="manage-column column-client_id">'._x('Actions','app table label','vibe-customtypes').'</th>	</tr>
				</thead>

				<tbody id="the-list" data-wp-lists="list:wp_list_text_link">';
				foreach($this->global_updates as $k=> $update){
					echo '<tr class="update_row">
						<td class="name column-name">
							<strong><a class="" title="">'.stripcslashes($update['content']).'</a></strong>
						</td>
						<td class="time column-time">'.date_i18n($date_format.' '.$time_format , $update['time']).'</td>
						<td class="actions column-actions"><a href="javascript:void(0)" data-id="'.$k.'" class="remove_update button">'._x('Remove','','vibe-customtypes').'</a></td></tr>';
						
				}						
				echo '</tbody><tfoot>
				<tr>
					<th scope="col" id="" class="manage-column column-name column-primary">'._x('Content','content','vibe-customtypes').'</th><th scope="col" id="description" class="manage-column column-description">'._x('Time','app table label','vibe-customtypes').'</th><th scope="col" id="client_id" class="manage-column column-client_id">'._x('Actions','app table label','vibe-customtypes').'</th>	</tr>
				</tfoot>

				</table>';
		}else{
			echo '<div class="notice message is-dimissable"><p>'._x('No updates found.','','vibe-customtypes').'</p></div>';
		}
		

		?>
		<style>
			#update_to_all_form label.min{
				opacity: 0.7;
			    font-size: 90%;
			    font-style: italic;
			}
			a.hide_update_form{color:red;}
			a.send_update_toggle.button.button-primary {
			    margin-bottom: 30px;
			}
			a.hide_update_form {
			    position: absolute;
			    top: 5px;
			    right: 5px;
			    cursor: pointer;
			}
			#update_to_all_form textarea{width:100%;}
			#update_to_all_form{
				display: none;
				padding: 1em 2em 2em;
		    	background: #fff;
		    	position: relative;
		    	width: 50vw;
		    	border: 1px solid rgba(0,0,0,0.1);
		    	margin-bottom: 30px;
			}
		</style>
		<script>
			
			jQuery(document).ready(function($){
				$('.send_update_toggle').on('click',function(){
					$('#update_to_all_form').toggle(300);
				});
				$('#update_to_all_form .hide_update_form').on('click',function(){
					$('.send_update_toggle').trigger('click');
				});
				$('.remove_update').on('click',function(){
					if(confirm("<?php echo _x('Are sure you want to remove this update?','','vibe-customtypes');?>")){
						var $this = $(this);
						var init_text = $this.text();
						$this.text('<?php echo _x('Removing...','removing...','vibe-customtypes');?>')
						jQuery.ajax({
			              type: "POST",
			              url: ajaxurl,
			              data: { action: "remove_update",
			                      id: $this.data('id'),
			                      security:$('#update_actions').val()
			                    },
			              cache: false,
			              success: function (html) {
			              	$this.closest('.update_row').remove();
			              	$this.text(html);
			                setTimeout(function(){
			                	$this.text(init_text);
			                },4000);
			              }
			            });
					}
				});

				$('.send_update').on('click',function(){
					var $this = $(this);
					if(!$this.parent().find(".update_content").val()){
						alert("<?php echo _x('Please enter some content.','','vibe-customtypes');?>");
						return false;
					}
					var init_text = $this.text();
					$this.text('<?php echo _x('Sending...','sending...','vibe-customtypes');?>')
					jQuery.ajax({
		              type: "POST",
		              url: ajaxurl,
		              data: { action: "api_send_notice_to_all",
		                      message: $this.parent().find(".update_content").val(),
		                      security:$('#update_actions').val()
		                    },
		              cache: false,
		              success: function (html) {
		              	
		              	$this.text(html);
		                setTimeout(function(){
		                	$this.text(init_text);
		                },4000);
		              }
		            });
				});
			});	
		</script>
		<?php
	}

	function new_app_form(){
		echo '<form method="post"><input type="submit" name="wplms_new_app" value="'._x('Create new app','new app button label','vibe-customtypes').'" class="button-primary">';
		wp_nonce_field('wplms_create_newapp','create_app_button');
		echo '</form>';

		if(((isset($_POST['wplms_new_app']) && wp_verify_nonce($_POST['create_app_button'],'wplms_create_newapp')) || (isset($_POST['edit_app']))) && current_user_can('manage_options')){
			echo '<div class="card"><h2>'.(isset($_POST['edit_app'])?_x('Edit app','form title','vibe-customtypes'):_x('Create a new app','form title','vibe-customtypes')).'</h2><form method="post">';

			echo '<p><input type="text" name="app_name" class="regular-text" placeholder="'._x('App Name','wp admin form','vibe-customtypes').'" value="'.(isset($_POST['edit_app'])?$this->apps[$_POST['edit_app']]['name']:'').'"></p>';
			echo '<p><input type="text" name="app_description" class="regular-text" value="'.(isset($_POST['edit_app'])?$this->apps[$_POST['edit_app']]['description']:'').'"" placeholder="'._x('App Description','wp admin form','vibe-customtypes').'"></p>';
			echo '<p><input type="text" name="app_redirect_uri" class="regular-text" placeholder="'._x('Redirect URI','wp admin form','vibe-customtypes').'" value="'.(isset($_POST['edit_app'])?$this->apps[$_POST['edit_app']]['redirect_uri']:site_url()).'"></p>';
			echo '<p><input type="submit" name="wplms_add_app" value="'.(isset($_POST['edit_app'])?_x('Edit app','new app button label','vibe-customtypes'):_x('Create new app','new app button label','vibe-customtypes')).'" class="button-primary">&nbsp;<a class="button" onClick="">'.__('Close','vibe-customtypes').'</a></p>';
			if(isset($_POST['edit_app'])){
				echo '<input type="hidden" name="edit_app_key" value="'.$_POST['edit_app'].'">';
			}
			wp_nonce_field('wplms_create_newapp','create_app');
			echo '</form></div>';
		}
	}

	function handle_save(){

		if(current_user_can('manage_options') && isset($_POST['delete_app'])){
			unset($this->apps[$_POST['delete_app']]);
			update_option('wplms_apps',$this->apps);
			return;
		}
		if(isset($_POST['wplms_add_app']) && wp_verify_nonce($_POST['create_app'],'wplms_create_newapp') && current_user_can('manage_options')){

			if(empty($_POST['app_name']) || empty($_POST['app_description']) || empty($_POST['app_redirect_uri'])){
				$this->display_message(_x('Empty Name/Descript/Redirect URI','validation message','vibe-customtypes'),'error');
				return;
			}
			$new_app = array(
				'name' 			=>	$_POST['app_name'],
				'description'	=>	$_POST['app_description'],
				'redirect_uri'	=>  $_POST['app_redirect_uri'], 
			);

			$client_id = wp_generate_password(23,false);
			$client_secret = wp_generate_password( 40, true, false );
			$new_app['app_id'] = $client_id;
			$new_app['app_secret'] = $client_secret;
			if(empty($this->apps)){
				$this->apps = array();
			}
			if(isset($_POST['edit_app_key'])){
				$this->apps[$_POST['edit_app_key']] = $new_app;
				$this->display_message(_x('App Updated successfully','success message','vibe-customtypes'),'updated');
			}else{
				$this->apps[] = $new_app;	
				$this->display_message(_x('App Created successfully','success message','vibe-customtypes'),'updated');
			}
			
			update_option('wplms_apps',$this->apps);
			
			return;
		}
	}

	function show_apps(){
		if(empty($this->apps) && empty($_POST)){
			$this->display_message(_x('No apps found ! Create a new App','app message','vibe-customtypes'),'updated');
		}else if(!empty($this->apps)){

			add_thickbox();
			echo '<table class="wp-list-table widefat fixed striped wp_list_test_links">
				<thead>
				<tr>
					<th scope="col" id="name" class="manage-column column-name column-primary">'._x('App Name','app table label','vibe-customtypes').'</th><th scope="col" id="description" class="manage-column column-description">'._x('App Description','app table label','vibe-customtypes').'</th><th scope="col" id="client_id" class="manage-column column-client_id">'._x('App ID','app table label','vibe-customtypes').'</th>	</tr>
				</thead>

				<tbody id="the-list" data-wp-lists="list:wp_list_text_link">';
				foreach($this->apps as $k=> $app){
					
					echo '<tr>
						<td class="name column-name">
							<strong><a class="thickbox" title="Edit">'.$app['name'].'</a></strong>
							<div class="row-actions">
								<form method="post" id="form_'.$app['app_id'].'" style="float:left">
									<span class="edit"><a onclick="document.getElementById(\'form_'.$app['app_id'].'\').submit();">'.__('Edit','vibe-customtypes').'</a> | </span>
									<input type="hidden" name="edit_app" value="'.$k.'" />
								</form>
								<form method="post" id="form_delete_'.$app['app_id'].'" style="float:left">	
									<span class="trash"><a class="submitdelete" onclick="confirm(\''. __('Are you sure you want to delete this app ?','vibe_customtypes').'\') && document.getElementById(\'form_delete_'.$app['app_id'].'\').submit();">'.__('Delete','vibe-customtypes').'</a> | </span>
									<input type="hidden" name="delete_app" value="'.$k.'" />
								</form>	
								<span class="view"><a  href="#TB_inline?width=400&height=100&inlineId=app_'.$app['app_id'].'"  class="thickbox">'.__('Show Secret','vibe-customtypes').'</a></span>
							</div>
						</td>
						<td class="description column-description">'.$app['description'].'</td>
						<td class="client_id column-client_id">'.$app['app_id'].'</td></tr>';
						echo '<div id="app_'.$app['app_id'].'" style="display:none;text-align:center"><h3 style="background:#f6f6f6;border:2px dashed #eee;text-align:center;padding:20px;">'.htmlentities($app['app_secret']).'</h3></div>';
				}						
				echo '</tbody><tfoot>
				<tr>
					<th scope="col" class="manage-column column-name column-primary">'._x('App Name','app table label','vibe-customtypes').'</th><th scope="col" class="manage-column column-description">'._x('App Description','app table label','vibe-customtypes').'</th><th scope="col" class="manage-column column-client_id">'._x('App ID','app table label','vibe-customtypes').'</th>	</tr>
				</tfoot>

				</table>';
		}
	}

	function display_message($message,$status){

		echo '<div id="message" class="message '.$status.'"><p>'.$message.'</p></div>';
	}


	function get_clients(){

		

	}


	function show_clients(){

		//GETTING CLIENTS :

        wp_deregister_script('badgeos-select2');
        wp_dequeue_script('badgeos-select2');
        wp_deregister_script('select2');
        wp_dequeue_script('select2');
        wp_dequeue_style('badgeos-select2-css');
        wp_deregister_style('badgeos-select2-css');
        
        wp_enqueue_script('wplms-api-clients-select2-js',VIBE_PLUGIN_URL.'/vibe-customtypes/metaboxes/js/select2.min.js');
		wp_enqueue_style('wplms-api-clients-select2-css',VIBE_PLUGIN_URL.'/vibe-customtypes/metaboxes/css/select2.min.css');
		
		wp_nonce_field('vibe_security','vibe_security');
		global $wpdb;
		if(!empty($_POST['clear_all_connected_clients']) && wp_verify_nonce($_POST['clear_all_connected_clients'],'clear_all_connected_clients')){
			$access_tokens_results = $wpdb->get_results("SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'access_tokens'");
			if(!empty($access_tokens_results)){
				$access_tokens_array = array();
				foreach($access_tokens_results as $ac_tokens){
					$access_tokens = unserialize($ac_tokens->meta_value);
					foreach($access_tokens as $a_tokens){
						$access_tokens_array[] = '"'.$a_tokens.'"';
					}
				}
			
				$wpdb->delete( $wpdb->usermeta, 
					array('meta_key'=>'access_tokens'), 
					array('%s')
				);

				if(!empty($access_tokens_array)){
					$access_tokens_in = implode(',',$access_tokens_array);
			
					$wpdb->get_results("DELETE FROM {$wpdb->usermeta} WHERE meta_key IN ($access_tokens_in)");
				}
				
			}
			
			$this->display_message(_x('Connected clients deleted successfully','success message','vibe-customtypes'),'updated');
			
		}

		if(!empty($_POST['clear_all_expired_clients']) && wp_verify_nonce($_POST['clear_all_expired_clients'],'clear_all_expired_clients')){
			$access_tokens_results = $wpdb->get_results("SELECT * FROM {$wpdb->usermeta} WHERE meta_key = 'access_tokens'");
			$access_tokens_array = array();
			$users_access_tokens_array=array();
			foreach($access_tokens_results as $key=>$ac_tokens){
				$access_tokens = unserialize($ac_tokens->meta_value);
				$users_access_tokens_array[$access_tokens_results[$key]->user_id]=count($access_tokens);
				foreach($access_tokens as $a_tokens){
					$access_tokens_array[] = '"'.$a_tokens.'"';
				}
			}

			$access_tokens_in = implode(',',$access_tokens_array);
		
			$results = $wpdb->get_results("SELECT * FROM {$wpdb->usermeta} WHERE meta_key IN ($access_tokens_in)");
			
			$expired_tokens_array=array();
			$expired_tokens_full_array=array();
			foreach($results as $key=>$result){
				$result_unserialized = unserialize($result->meta_value);
				if(strtotime($result_unserialized['expires']) <= time() ){
					$expired_tokens_array[]='"'.$results[$key]->meta_key.'"';
					$expired_tokens_full_array[]=array(
						'user_id'=>$result_unserialized['user_id'],
						'token'=>$result_unserialized['access_token'],
						);

				}
			}
			$expired_tokens_in = implode(',',$expired_tokens_array);
			$flag = $wpdb->get_results("DELETE FROM {$wpdb->usermeta} WHERE meta_key IN ($expired_tokens_in)");
			$user_tokens = array();
			foreach($expired_tokens_full_array as $key=>$expired_token_full){
				$user_tokens[$expired_token_full['user_id']][]=$expired_token_full['token'];
			}
			
			$access_meta = array();
			foreach($users_access_tokens_array as $key=>$count){
				if(count($user_tokens[$key]) == $count){
					$access_meta[] = '"'.$key.'"';
				}
			}
			if(!empty($access_meta)){
				$access_meta_in  =  implode(',',$access_meta);
				$wpdb->get_results("DELETE FROM {$wpdb->usermeta} WHERE meta_key='access_token' AND user_id IN ($access_meta_in )");
			}
			$this->display_message(_x('Connected expired clients deleted successfully','success message','vibe-customtypes'),'updated');
		}

		if(isset($_POST) && isset($_POST['token'])){
			if(isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'],$_POST['token'])){
				
				$access_token = $_POST['token'];

				$token = $wpdb->get_row("SELECT * FROM {$wpdb->usermeta} WHERE meta_key = '".$access_token."'");

				$user_id = $token->user_id;

				$access_tokens = get_user_meta($user_id,'access_tokens',true);
				if(in_array($access_token,$access_tokens)){
					$k = array_search($access_token,$access_tokens);
					unset($access_tokens[$k]);
					update_user_meta($user_id,'access_tokens',$access_tokens);
				}
				delete_user_meta($user_id,$access_token);

				//Remove the refresh token
				
				$refresh_token = $wpdb->get_var("SELECT meta_key FROM {$wpdb->usermeta} WHERE umeta_id = ".($token->umeta_id+1)." AND meta_value LIKE '%refresh_token%' AND user_id = $user_id");

			
				$refresh_tokens = get_user_meta($user_id,'refresh_tokens',true);
				if(!empty($refresh_tokens)){
					if(in_array($refresh_token,$refresh_tokens)){
						$k = array_search($refresh_token,$refresh_tokens);
						unset($refresh_tokens[$k]);
						update_user_meta($user_id,'refresh_tokens',$refresh_tokens);
					}
					delete_user_meta($user_id,$refresh_token);
				}
			}
		}
		
		// $access_tokens_results = $wpdb->get_results("SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'access_tokens'");
		if(isset($_GET['wplms_api_clients_userid'])){
			$wplms_api_clients_userid = $_GET['wplms_api_clients_userid'];
		}

		$total_query     = "SELECT COUNT(meta_value) FROM {$wpdb->usermeta} WHERE meta_key = 'access_tokens'";
		if(!empty($wplms_api_clients_userid) && (is_numeric($wplms_api_clients_userid) || is_array($wplms_api_clients_userid))){
			if(is_array($wplms_api_clients_userid)){
				$user_ids = implode(',', $wplms_api_clients_userid);
				$total_query .= 'AND user_id IN ('.$user_ids.')';
				
			}else{
				
				$user_ids = $wplms_api_clients_userid;
				$total_query .= 'AND user_id = '.$user_ids;
			}
		}
		$total             = $wpdb->get_var( $total_query );

		$items_per_page = apply_filters('wplms_api_connected_clients_number',5);

		$page             = isset( $_REQUEST['wplms_cc_page'] ) ? abs( (int) $_REQUEST['wplms_cc_page'] ) : 1;
		$offset         = ( $page * $items_per_page ) - $items_per_page;
		$add_where = '';
		if(!empty($wplms_api_clients_userid) && (is_numeric($wplms_api_clients_userid) || is_array($wplms_api_clients_userid))){
			
			if(is_array($wplms_api_clients_userid)){
				$user_ids = implode(',', $wplms_api_clients_userid);
				$add_where .= 'AND user_id IN ('.$user_ids.')';
				
			}else{
				
				$user_ids = $wplms_api_clients_userid;
				$add_where .= 'AND user_id = '.$user_ids;
			}
		}

		if(isset($_GET['wplms_api_clients_orderby'])){
			$wplms_api_clients_orderby = sanitize_text_field($_GET['wplms_api_clients_orderby']);
		}else{
			$wplms_api_clients_orderby = 'DESC';
		}
		

		$result = $wpdb->get_results( "SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'access_tokens' {$add_where} ORDER BY umeta_id $wplms_api_clients_orderby LIMIT ${offset}, ${items_per_page}" );
		
		$totalPage         = ceil($total / $items_per_page);

		if(!empty($result)){
			foreach($result as $ac_tokens){
				$access_tokens = unserialize($ac_tokens->meta_value);
				if(!empty($access_tokens) && is_array($access_tokens)){

					foreach($access_tokens as $access_token){
						
						$client = $wpdb->get_row("SELECT * FROM {$wpdb->usermeta} WHERE meta_key = '".$access_token."'");

						$client->meta_value = unserialize($client->meta_value);
						$this->clients[] = array(
							'client_id'		=> $client->meta_value['client_id'],
							'user_id' 		=> $client->user_id,
							'access_token'	=> $client->meta_key,
							'expires'		=> $client->meta_value['expires'],
							'device'		=> $client->meta_value['device'],
							'model'			=> $client->meta_value['model'],
							'platform'		=> $client->meta_value['platform']
						);
					}
				}
			}
		}





		if(empty($this->clients)){
			echo '<div class="message"><p>'._x('No connected clients !','no clients found','vibe-customtypes').'</p></div>';
			return;
		}
		add_thickbox();
		//pagination :

		if($totalPage > 1){

			$clients_pagination     =  '<div class="wplms_cc_page"><span>Page '.$page.' of '.$totalPage.'</span>'.paginate_links( array(
			'base' => add_query_arg( 'wplms_cc_page', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => $totalPage,
			'current' => $page
			)).'</div>';
			
		}

		//order options 
		$order_options = apply_filters('wplms_api_connected_cleints_order_options',array(
				'DESC'=>_x('Latest','','vibe-customtypes'),
				'ASC'=>_x('Oldest','','vibe-customtypes'),
				
			)
		);

		?>

		<form id="api_username_orderby_form" method="get" action="?<?php echo $_SERVER['QUERY_STRING']; ?>">
			<?php
			
				foreach($_GET as $key => $value){
					if(!in_array($key,array('wplms_api_clients_userid[]','wplms_api_clients_orderby','wplms_cc_page'))){
						echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
					}
				}
			?>
			<?php echo $clients_pagination; ?>
			<select name="wplms_api_clients_userid[]" id="wplms_api_clients_userid" class="selectusers_clients" data-placeholder="<?php echo __('Enter Student Usernames/Emails, separated by comma','vibe-customtypes');?>" multiple>
				<?php
				$user_ids = $_GET['wplms_api_clients_userid'];
                if(!empty($user_ids) ){
                	if(is_array($user_ids)){
                		foreach ($user_ids as $userid) {
	                       $user =  get_user_by($userid);
	                       echo '<option value="'.$userid.'" selected="selected">'.bp_core_fetch_avatar(array('item_id' => $userid, 'type' => 'thumb', 'width' => 32, 'height' => 32)).''.bp_core_get_user_displayname($userid).'</option>';
	                    }
                	}else{
                		echo '<option value="'.$user_ids.'" selected="selected">'.bp_core_fetch_avatar(array('item_id' => $user_ids, 'type' => 'thumb', 'width' => 32, 'height' => 32)).''.bp_core_get_user_displayname($user_ids).'</option>';
                	}
                    
                }
                ?>
			</select>
			<label><?php echo _x('Order by','search text api connected client','vibe-customtypes');?></label>
			<select name="wplms_api_clients_orderby">
				<?php

				foreach ($order_options as $key => $value) {
					echo '<option value="'.$key.'" '.((!empty($_GET['wplms_api_clients_orderby']) && $key==$_GET['wplms_api_clients_orderby'])?'selected="selected"':'').'>'.$value.'</option>';
				}
				?>
			</select>
			<input type="submit" class="button button-primary" value="<?php echo _x('Go','go button label','vibe-customtypes'); ?>">

		</form>

		<?php
		echo '<table class="wp-list-table widefat fixed striped wp_list_test_links">
				<thead>
				<tr>
					<th scope="col" class="manage-column column-name column-primary">'._x('Client ID','app table label','vibe-customtypes').'</th><th scope="col" class="manage-column column-description">'._x('User','app table label','vibe-customtypes').'</th><th scope="col" class="manage-column column-client_id">'._x('Access Token','app table label','vibe-customtypes').'</th><th scope="col" id="client_id" class="manage-column column-client_id">'._x('Expires','app table label','vibe-customtypes').'</th>
						<th scope="col" id="device_id" class="manage-column column-device_id">'._x('Device Info','app table label','vibe-customtypes').'</th>
					<th>
						<i class="dashicons dashicons-email" title="'._x('Send private update','','vibe-customtypes').'"></i>/
						<i class="dashicons dashicons-visibility" title="'._x('View sent updates','','vibe-customtypes').'"></i></th><th>'._x('Remove','app table label','vibe-customtypes').'</th>	</tr>
				</thead>

				<tbody id="the-list" data-wp-lists="list:wp_list_text_link">';
		$all_users = array();
		foreach($this->clients as $client){
			if(!empty($client['client_id']) && !empty($client['user_id'])){
				$all_users[] = $client['user_id'];
				echo '<tr><td>'.$client['client_id'].'</td>
						<td>'.(function_exists('bp_core_get_user_displayname')?bp_core_get_user_displayname($client['user_id']):$client['user_id']).'</td>
					<td>'.$client['access_token'].'</td>
					<td>'.$client['expires'].'</td>
					<td>'.(empty($client['platform'])?'--':$client['platform'].' '.$client['model'].' - '.$client['device']).'</td>
					<td>
						<a class="send_update_notice" title="'._x('Send private update to this user','','vibe-customtypes').'"><i class="dashicons dashicons-email"></i></a>/
						<a class="thickbox view_recent_notice" title="'._x('View sent notices','','vibe-customtypes').'" href="#TB_inline?width=800&amp;inlineId=user_'.$client['user_id'].'"><i class="dashicons dashicons-visibility"></i></a>
						<div class="hide_message">
							<textarea class="update_message"></textarea>
							<input type="hidden" class="api_client_id" value="'.$client['client_id'].'">
							<input type="hidden" class="api_user_id" value="'.$client['user_id'].'">
							<a class="send_notice button">'.__('Send','vibe-customtypes').'</a>
						</div>
					</td>
					<td><form method="post"><input type="submit" value="'._x('Remove','app table label','vibe-customtypes').'" class="button" /><input type="hidden" name="token" value="'.$client['access_token'].'" />';

					wp_nonce_field($client['access_token']);
					echo '</form></td></tr>';
			}
		}

		echo '</tbody><tfoot>
				<tr>
					<th scope="col" class="manage-column column-name column-primary">'._x('Client ID','app table label','vibe-customtypes').'</th><th scope="col" class="manage-column column-description">'._x('User','app table label','vibe-customtypes').'</th><th scope="col" class="manage-column column-client_id">'._x('Access Token','app table label','vibe-customtypes').'</th><th scope="col" id="client_id" class="manage-column column-client_id">'._x('Expires','app table label','vibe-customtypes').'</th><th scope="col" id="device_id" class="manage-column column-device_id">'._x('Device Info','app table label','vibe-customtypes').'</th><th>'._x('Remove','app table label','vibe-customtypes').'</th></tr>
				</tfoot>

				</table>';

		//pagination :

		

		echo		'<hr>'.__('Actions','vibe-customtypes').' : 
				<form class="clear_clients_form" method="post">
				<button type="submit" name="clear_all_connected_clients" value="1" class="clear_clients button button-primary" id="clear_all_connected_clients">'.__('Clear all','vibe-customtypes').'</button>
				'.wp_nonce_field('clear_all_connected_clients','clear_all_connected_clients').'
				</form>

				<form class="clear_clients_form" method="post">
				<button type="submit" name="clear_all_expired_clients" value="1"  class="clear_clients_expired button button-primary" id="clear_all_expired_clients">'.__('Clear all expired clients','vibe-customtypes').'</button>
				'.wp_nonce_field('clear_all_expired_clients','clear_all_expired_clients').'
				</form>
				<script>jQuery(".send_update_notice").click(function(){
					jQuery(this).parent().find(".hide_message").toggle(200);
				});
				jQuery(".clear_clients").on("click",function(event){
					if(confirm("'._x('Do you want to clear all clients ?','app notice','vibe-customtypes').'")){

					}else{
						event.preventDefault();
					}
				});
				jQuery(".clear_clients_expired").on("click",function(event){
					if(confirm("'._x('Do you want to clear all expired clients ?','app notice','vibe-customtypes').'")){

					}else{
						event.preventDefault();
					}
				});
				jQuery(".send_notice").on("click",function(){
					console.log("CHECK");
					var $this = jQuery(this);
					jQuery.ajax({
		              type: "POST",
		              url: ajaxurl,
		              data: { action: "api_send_notice_to_user",
		                      user_id: jQuery(this).parent().find(".api_user_id").val(),
		                      client_id: jQuery(this).parent().find(".api_client_id").val(),
		                      message: jQuery(this).parent().find(".update_message").val()
		                    },
		              cache: false,
		              success: function (html) {
		              	var d = $this.html();
		              	$this.html(html);
		                setTimeout(function(){
		                	$this.html(d);
		                },4000);
		              }
		            });
				});

				</script>';
				?>
				<script>
				
				jQuery(document).ready(function($){

					jQuery('.selectusers_clients').each(function(){
				    	var $this = jQuery(this);
					    $this.select2({
					        minimumInputLength: 4,
					        placeholder: jQuery(this).attr('data-placeholder'),
					        closeOnSelect: true,
					        language: {
					          inputTooShort: function() {
					            return '<?php echo _x('Please type atleast four characters','','vibe-customtypes');?>';
					          }
					        },
					        ajax: {
					            url: ajaxurl,
					            type: "POST",
					            dataType: 'json',
					            delay: 250,
					            data: function(term){ 
					                    return  {   action: 'select_users_api_clients', 
					                                security: jQuery('#vibe_security').val(),
					                                q: term,
					                            }
					            },
					            processResults: function (data) {
					                return {
					                    results: data
					                };
					            },       
					            cache:true  
					        },
					        templateResult: function(data){
					            return '<img width="32" src="'+data.image+'">'+data.text;
					        },
					        templateSelection: function(data){
					            return '<img width="32" src="'+data.image+'">'+data.text;
					        },
					        escapeMarkup: function (m) {
					            return m;
					        }
					    });
				  	});

					

					$('.remove_update_user').on('click',function(){
						if(confirm("<?php echo _x('Are sure you want to remove this update?','','vibe-customtypes');?>")){
							var $this = $(this);
							var init_text = $this.text();
							$this.text('<?php echo _x('Removing...','removing...','vibe-customtypes');?>')
							jQuery.ajax({
				              type: "POST",
				              url: ajaxurl,
				              data: { action: "remove_update_user",
				                      id: $this.data('id'),
				                      user_id:$this.data('user_id'),
				                      security:$('#update_actions').val()
				                    },
				              cache: false,
				              success: function (html) {
				              	$this.closest('.update_row').remove();
				              	$this.text(html);
				                setTimeout(function(){
				                	$this.text(init_text);
				                },4000);
				              }
				            });
						}
					});
				});
				</script>
				<?php
				echo'
				<style>
					.hide_message{display:none;}form.clear_clients_form{display:inline-block;}
					.send_update_notice,.view_recent_notice{cursor:pointer;}
					.wplms_cc_page {
					    margin: 15px 0;
					    float:right;
					}
					input.button.button-primary {
					    margin-top: 5px;
					}
					.wplms_cc_page span {margin:5px;}
					.wplms_cc_page a:not(.current),.wplms_cc_page span.page-numbers{background:#006799;color:#FFF;padding:5px 8px;border-radius:3px;font-size:1.2em;text-decoration:none;}
					.wplms_cc_page span.page-numbers.current{opacity:0.6;}
				</style>';
				wp_nonce_field('update_actions','update_actions');
				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );
				$all_users = array_unique($all_users);
				
				foreach ($all_users as $user_id) {
					$meta = get_user_meta($user_id,'wplms_api_tracker',true);
					$updates = $meta['updates'];
					echo '<div id="user_'.$user_id.'" style="display:none;text-align:center">';
					if(!empty($updates)){
						
						echo '<h3> '.sprintf(_x('All Updates sent to %s','','vibe-customtypes'),bp_core_get_user_displayname($user_id)).'</h3><table class="wp-list-table widefat fixed striped wp_list_test_links">
							<thead>
							<tr>
								<th scope="col" id="" class="manage-column column-name column-primary">'._x('Content','content','vibe-customtypes').'</th><th scope="col" id="description" class="manage-column column-description">'._x('Time','app table label','vibe-customtypes').'</th>
								<th scope="col" id="client_id" class="manage-column column-client_id">'._x('Delivered','Delivered table label','vibe-customtypes').'</th>
								<th scope="col" id="client_id" class="manage-column column-client_id">'._x('Actions','app table label','vibe-customtypes').'</th>	
							</tr>
							</thead>

							<tbody id="the-list" data-wp-lists="list:wp_list_text_link">';
							foreach($updates as $k=> $update){
								$access_time = _x('Not delivered yet','','vibe-customtypes');
								if(!empty($update['access_time']) ){
									$access_time = date_i18n($date_format.' '.$time_format , $update['access_time']);
								}
								echo '<tr class="update_row">
									<td class="name column-name">
										<strong><a class="" title="">'.stripcslashes($update['content']).'</a></strong>
									</td>
									<td class="time column-time">'.date_i18n($date_format.' '.$time_format , $update['time']).'</td>
									<td class="received column-name">
										<strong><a class="received" title="">'.$access_time.'</a></strong>
									</td>
									<td class="actions column-actions"><a href="javascript:void(0)" data-id="'.$k.'" data-user_id="'.$user_id.'" class="remove_update_user button">'._x('Remove','','vibe-customtypes').'</a></td></tr>';
									
							}						
							echo '</tbody><tfoot>
							<tr>
								<th scope="col" id="" class="manage-column column-name column-primary">'._x('Content','content','vibe-customtypes').'</th><th scope="col" id="description" class="manage-column column-description">'._x('Time','app table label','vibe-customtypes').'</th>
								<th scope="col" id="client_id" class="manage-column column-client_id">'._x('Delivered','Delivered table label','vibe-customtypes').'</th>
								<th scope="col" id="client_id" class="manage-column column-client_id">'._x('Actions','app table label','vibe-customtypes').'</th>	
							</tr>
							</tfoot>

							</table>';
					}else{
						echo '<div class="notice update-nag message"><p>'._x('No updates found.','','vibe-customtypes').'</p></div>';
					}

					echo '</div>';
				}
				

	}

}
