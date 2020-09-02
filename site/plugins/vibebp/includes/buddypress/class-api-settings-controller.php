<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'VIBE_BP_API_Rest_Settings_Controller' ) ) {
	
	class VIBE_BP_API_Rest_Settings_Controller extends WP_REST_Controller{
		
		public static $instance;
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new VIBE_BP_API_Rest_Settings_Controller();
	        return self::$instance;
	    }
	    public function __construct( ) {
			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->type= Vibe_BP_API_SETTINGS_TYPE;
			$this->register_routes();
		}

		public function register_routes() {

			register_rest_route( $this->namespace, '/' .$this->type.'/save/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'save_general_settings' ),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/email/', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_user_email_settings' ),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));


			register_rest_route( $this->namespace, '/' .$this->type.'/email/set', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'set_email_notification_settings' ),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/' .$this->type.'/export_data', array(
				array(
					'methods'             => 'POST',
					'callback'            =>  array( $this, 'get_export_data_settings' ),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));

			
			register_rest_route( $this->namespace, '/profile/avatar', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'set_avatar'),
					'permission_callback' => array( $this, 'get_get_avatar_settings_permissions' ),
				),
			));

			register_rest_route( $this->namespace, '/avatar', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'get_avatar'),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));

			
			register_rest_route( $this->namespace, '/search/', array(
				array(
					'methods'             =>  'POST',
					'callback'            =>  array( $this, 'search'),
					'permission_callback' => array( $this, 'get_settings_permissions' ),
				),
			));
		}


		/*
	    PERMISSIONS
	     */
	    function get_settings_permissions($request){

	    	$body = json_decode($request->get_body(),true);
	       	
	        if (empty($body['token'])){
	           	$client_id = $request->get_param('client_id');
	           	if($client_id == vibebp_get_setting('client_id')){
	           		return true;
	           	}
	        }else{
	        	$token = $body['token'];
	        }
	        /** Get the Secret Key */
	        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
	        if (!$secret_key) {
	          	return false;
	        }
	        /** Try to decode the token */ /** Else return exception*/
	        try {
	            $user_data = JWT::decode($token, $secret_key, array('HS256'));
	            /*
		        avatar: "//www.gravatar.com/avatar/73745bceffd75a7e5a1203d9f0e9fe44?s=150&#038;r=g&#038;d=mm"
				caps: ["subscriber"]
				displayname: "test"
				email: "q@q.com"
				id: "2"
				profile_link: "http://localhost/appointments/members/test"
				slug: "test"
				username: "test"*/
		        $this->user = $user_data->data->user;
		        /** Let the user modify the data before send it back */
	        	return true;

	        }catch (Exception $e) {
	            /** Something is wrong trying to decode the token, send back the error */
	            return false;
	        }
	    	

	    	return false;
	    }

	    function get_avatar($request){

	    	$body = json_decode($request->get_body(),true);
	    	$name = '';
	    	if(!empty($body['type'])){
	    		switch($body['type']){
	    			case 'friends':
	    				$key = 'user_'.$body['ids']['item_id'];
	    				$avatar = bp_core_fetch_avatar(array(
                            'item_id' => $body['ids']['item_id'],
                            'object'  => 'user',
                            'type'=>'thumb',
                            'html'    => false
                        ));
                        $name = bp_core_get_user_displayname($body['ids']['item_id']);
	    			break;
	    			case 'group':
	    				$key = 'group_'.$body['ids']['item_id'];
	    				$avatar = bp_core_fetch_avatar(array(
                            'item_id' => $body['ids']['item_id'],
                            'object'  => 'group',
                            'type'=>'thumb',
                            'html'    => false
                        ));
	    			break;
	    			case 'activity':
	    				$key = 'user_'.$body['ids']['secondary_item_id'];
	    				$avatar = bp_core_fetch_avatar(array(
                            'item_id' => $body['ids']['secondary_item_id'],
                            'object'  => 'user',
                            'type'=>'thumb',
                            'html'    => false
                        ));
                        $name = bp_core_get_user_displayname($body['ids']['secondary_item_id']);
	    			break;
	    			default:
	    				$key = apply_filters('vibebp_get_avatar_key','user_'.$body['ids']['user_id'],$body['type'],$body['ids']);
	    				$avatar = apply_filters('vibebp_get_avatar',bp_core_fetch_avatar(array(
                            'item_id' => $body['ids']['user_id'],
                            'object'  => 'user',
                            'type'=>'thumb',
                            'html'    => false
                        )),$body['type'],$body['ids']);
                         $name = bp_core_get_user_displayname($body['ids']['user_id']);
	    			break;
	    		}
	    	}

	    	return new WP_REST_Response( array('status'=>1,'value'=>array('avatar'=>$avatar,'name'=>$name),'key'=>$key), 200 ); 
	    }

	    function search($request){
	    	$body = json_decode($request->get_body(),true);

	    	$return = array();
	    	switch($body['type']){
	    		case 'user':
	    		case 'member':
	    			$args = array(
						'search'         => $body['search'],
						'search_columns' => array( 'user_login', 'user_email','user_nicename' ),
						'number'=>5,
						'fields'=>array('ID','display_name')
					);
					$user_query = new WP_User_Query( $args );
					$results = $user_query->get_results();
					
					if(!empty($results)){
						foreach($results as $user){
							$return[]= array('id'=>$user->ID,'name'=>$user->display_name,'avatar'=>$avatar = apply_filters('vibebp_get_avatar',bp_core_fetch_avatar(array(
		                            'item_id' => $user->ID,
		                            'object'  => 'user',
		                            'type'=>'thumb',
		                            'html'    => false
		                        ))));
						}
					}
	    		break;
	    	}

	    	return new WP_REST_Response( array('status'=>1,'results'=>$return), 200 );
	    }

	    function save_general_settings($request){

	    	$args = json_decode($request->get_body(),true);

	    	$status = 1;
	    	$update_user = get_userdata( $this->user->id );


	    	$bp            = buddypress(); // The instance
			$email_error   = false;        // invalid|blocked|taken|empty|nochange
			$pass_error    = false;        // invalid|mismatch|empty|nochange
			$pass_changed  = false;        // true if the user changes their password
			$email_changed = false;        // true if the user changes their email
			$feedback_type = 'error';      // success|error
			$feedback      = array();      // array of strings for feedback.
			$type = $args['type'];

	    	// Validate the user again for the current password when making a big change.
			if ($type=='email' ) {
				if(( is_super_admin() ) || ( !empty( $args['pwd'] ) && wp_check_password( $args['pwd'], $update_user->user_pass, $this->user->id ) )){
					if ( !empty( $args['email'] ) ) {

						// What is missing from the profile page vs signup -
						// let's double check the goodies.
						$user_email     = sanitize_email( esc_html( trim( $args['email'] ) ) );
						$old_user_email = $this->user->user_email;

						// User is changing email address.
						if ( $old_user_email != $user_email ) {

							// Run some tests on the email address.
							$email_checks = bp_core_validate_email_address( $user_email );

							if ( true !== $email_checks ) {
								if ( isset( $email_checks['invalid'] ) ) {
									$email_error = 'invalid';
								}

								if ( isset( $email_checks['domain_banned'] ) || isset( $email_checks['domain_not_allowed'] ) ) {
									$email_error = 'blocked';
								}

								if ( isset( $email_checks['in_use'] ) ) {
									$email_error = 'taken';
								}
							}

							// Store a hash to enable email validation.
							if ( false === $email_error ) {
								$hash = wp_generate_password( 32, false );

								$pending_email = array(
									'hash'     => $hash,
									'newemail' => $user_email,
								);

								bp_update_user_meta( $this->user->id, 'pending_email_change', $pending_email );
								$verify_link = bp_core_get_user_domain($this->user->id) . bp_get_settings_slug() . '/?verify_email_change=' . $hash;

								

								// Send the verification email.
								$args = array(
									'tokens' => array(
										'displayname'    => bp_core_get_user_displayname( $this->user->id ),
										'old-user.email' => $old_user_email,
										'user.email'     => $user_email,
										'verify.url'     => esc_url( $verify_link ),
									),
								);
								bp_send_email( 'settings-verify-email-change', $this->user->id, $args );

								// We mark that the change has taken place so as to ensure a
								// success message, even though verification is still required.
								$args['email'] = $update_user->user_email;
								$email_changed = true;
							}

						// No change.
						} else {
							$email_error = false;
						}

					// Email address cannot be empty.
					} else {

						$email_error = 'empty';
					}
					
					

					
				}else{
					$pass_error = 'invalid';
				}
				

				/* Email Change Attempt ******************************************/

				

			// Password Error.
			} elseif($type=='password' ) {
				

				/* Password Change Attempt ***************************************/

				if ( !empty( $args['pass1'] ) && !empty( $args['pass2'] ) ) {

					if ( ( $args['pass1'] == $args['pass2'] ) && !strpos( " " . wp_unslash( $args['pass1'] ), "\\" ) ) {

						// Password change attempt is successful.
						if ( ( ! empty( $update_user->user_pass ) && $update_user->user_pass != $args['pass1'] ) || is_super_admin() )  {
							$update_user->user_pass = $args['pass1'];
							$pass_changed = true;

						// The new password is the same as the current password.
						} else {
							$pass_error = 'same';
						}

					// Password change attempt was unsuccessful.
					} else {
						$pass_error = 'mismatch';
					}

				// Both password fields were empty.
				} elseif ( empty( $args['pass1'] ) && empty( $args['pass2'] ) ) {
					$pass_error = false;

				// One of the password boxes was left empty.
				} elseif ( ( empty( $args['pass1'] ) && !empty( $args['pass2'] ) ) || ( !empty( $args['pass1'] ) && empty( $args['pass2'] ) ) ) {
					$pass_error = 'empty';
				}

				// The structure of the $update_user object changed in WP 3.3, but
				// wp_update_user() still expects the old format.
				if ( isset( $update_user->data ) && is_object( $update_user->data ) ) {
					$update_user = $update_user->data;
					$update_user = get_object_vars( $update_user );

					// Unset the password field to prevent it from emptying out the
					// user's user_pass field in the database.
					// @see wp_update_user().
					if ( false === $pass_changed ) {
						unset( $update_user['user_pass'] );
					}
				}
				
			}

			// Email feedback.
			switch ( $email_error ) {
				case 'invalid' :
					$feedback['email_invalid']  = __( 'That email address is invalid. Check the formatting and try again.', 'buddypress' );
					break;
				case 'blocked' :
					$feedback['email_blocked']  = __( 'That email address is currently unavailable for use.', 'buddypress' );
					break;
				case 'taken' :
					$feedback['email_taken']    = __( 'That email address is already taken.', 'buddypress' );
					break;
				case 'empty' :
					$feedback['email_empty']    = __( 'Email address cannot be empty.', 'buddypress' );
					break;
				case false :
					// No change.
					break;
			}
			// Password feedback.
			switch ( $pass_error ) {
				case 'invalid' :
					$feedback['pass_error']    = __( 'Your current password is invalid.', 'buddypress' );
					break;
				case 'mismatch' :
					$feedback['pass_mismatch'] = __( 'The new password fields did not match.', 'buddypress' );
					break;
				case 'empty' :
					$feedback['pass_empty']    = __( 'One of the password fields was empty.', 'buddypress' );
					break;
				case 'same' :
					$feedback['pass_same'] 	   = __( 'The new password must be different from the current password.', 'buddypress' );
					break;
				case false :
					// No change.
					break;
			}
			

			// No errors so show a simple success message.
			if ( ( ( false === $email_error ) || ( false == $pass_error ) ) && ( ( true === $pass_changed ) || ( true === $email_changed ) ) ) {

				// Clear cached data, so that the changed settings take effect
					// on the current page load.
				if ( ( false === $email_error ) && ( false === $pass_error ) && ( wp_update_user( $update_user ) ) ) {
					$this->user = bp_core_get_core_userdata( $this->user->id );
					$feedback[]    = __( 'Your settings have been saved.', 'buddypress' );
					$feedback_type = 'success';
				}
				

			// Some kind of errors occurred.
			} elseif ( ( ( false === $email_error ) || ( false === $pass_error ) ) && ( ( false === $pass_changed ) || ( false === $email_changed ) ) ) {
				if ( bp_is_my_profile() ) {
					$feedback['nochange'] = __( 'No changes were made to your account.', 'buddypress' );
				} else {
					$feedback['nochange'] = __( 'No changes were made to this account.', 'buddypress' );
				}
			}

			if(!empty($feedback_type) && $feedback_type == 'success'){
				do_action( 'bp_core_general_settings_after_save' );
				return new WP_REST_Response( array('status'=>1,'message'=>implode( "\n", $feedback )), 200 );
			}
			
			return new WP_REST_Response( array('status'=>0,'message'=>implode( "\n", $feedback )), 200 );

	    }

	    function get_user_email_settings($request){

    		$args = json_decode($request->get_body(),true);

    		$email_notices = array();

    		if(bp_is_active('activity')){
    			get_user_meta($this->user->id,'notification_activity_new_mention',true);

    			$email_notices['notification_activity_new_mention'] = array(
    				'label'=>sprintf(__( 'A member mentions you in an update using "@%s"', 'vibebp' ),bp_core_get_username( $this->user->id ) ),
    			);
				$email_notices['notification_activity_new_reply'] = array( 'label'=> __( "A member replies to an update or comment you've posted", 'vibebp' ));
    		}

    		if(bp_is_active('messages')){
    			$email_notices['notification_messages_new_message'] = array( 'label'=> __( 'A member sends you a new message', 'vibebp' ));
    		}

    		if(bp_is_active('friends')){
    			$email_notices['notification_friends_friendship_request'] = array( 'label'=> _x( 'A member sends you a friendship request', 'Friend settings on notification settings page', 'vibebp' ));
    			$email_notices['notification_friends_friendship_accepted']= array( 'label'=> _x( 'A member accepts your friendship request', 'Friend settings on notification settings page', 'vibebp' ));
    		}

    		if(bp_is_active('groups')){
    			$email_notices['notification_groups_invite']= array( 'label'=> _x( 'A member invites you to join a group', 'group settings on notification settings page','vibebp' ));
    			$email_notices['notification_groups_group_updated']= array( 'label'=> _x( 'Group information is updated', 'group settings on notification settings page', 'vibebp' ));
    			$email_notices['notification_groups_admin_promotion']= array( 'label'=> _x( 'You are promoted to a group administrator or moderator', 'group settings on notification settings page', 'vibebp' ));
    			$email_notices['notification_groups_membership_request']= array( 'label'=> _x( 'A member requests to join a private group for which you are an admin', 'group settings on notification settings page', 'vibebp' ));
    			$email_notices['notification_membership_request_completed']= array( 'label'=> _x( 'Your request to join a group has been approved or denied', 'group settings on notification settings page', 'vibebp' ));
    		}

    		if(!empty($email_notices)){
    			foreach($email_notices as $key=>$notice){
    				
    				$value = get_user_meta($this->user->id,$key,true);
    				if(empty($value)){
    					$value = 'yes';
    				}
    				$email_notices[$key]['value'] = $value;
    			}
    		}
    		$email_notices = apply_filters('vibebp_buddypress_email_settings',$email_notices);
    		return new WP_REST_Response( $email_notices, 200 );
    	}

	   	function set_email_notification_settings($request){

	    	$body = json_decode($request->get_body(),true);
	    	if(!empty($body['setting'])){
	    		update_user_meta( $this->user->id , $body['setting'] , $body['value']);	
	    		$data=array(
	    			'status' => 1,
	    			'data' => true,
	    			'message' => _x('All Settings Updated','All Settings Not Updated','vibebp')
				);
	    	}
	    	
    		$data=apply_filters( 'vibe_bp_api_set_email_notification_settings', $data , $request );
    		return new WP_REST_Response( $data, 200 ); 
	    }

	    function get_export_data_settings($request){
	    	$body = json_decode($request->get_body(),true);
	    	$request = bp_settings_get_personal_data_request($this->user->id);

	    	if ( $request ){
		    	$return = array('status'=> $request->status);

		    	if ( 'request-completed' === $request->status ){
		    		if ( bp_settings_personal_data_export_exists( $request ) ){
		    			$return['message'] = __( 'Your request for an export of personal data has been completed.', 'vibebp' );

		    			$return['submessage'] = sprintf( esc_html__( 'You may download your personal data by clicking on the link below. For privacy and security, we will automatically delete the file on %s, so please download it before then.', 'vibebp' ), bp_settings_get_personal_data_expiration_date( $request ) );

		    			$return['report_link'] .= bp_settings_get_personal_data_export_url( $request );
		    			$return['label']=__('Download Report','vibebp');
		    		}else{
		    			$return['message']= __( 'Your previous request for an export of personal data has expired.', 'vibebp' );
		    			$return['submessage']=__( 'Please click on the button below to make a new request.', 'vibebp' );
		    			$return['report_link'] = 0;
		    			$return['label']=__('Request New Report','vibebp');
		    		}
		    		
		    	}elseif ( 'request-confirmed' === $request->status ){
		    		
		    		$return['message']=sprintf(__( 'You previously requested an export of your personal data on %s.', 'vibebp' ), bp_settings_get_personal_data_confirmation_date( $request ) );
					$return['submessage']= __( 'You will receive a link to download your export via email once we are able to fulfill your request.', 'vibebp' );
					$return['label']=0;

		    	}
		    }else{
		    	$return = array('status'=> 'not_requested','label'=>__('Request Data','vibebp'));

		    	$return['message']=__( 'You can request an export of your personal data, containing the following items if applicable:', 'vibebp' );
		    	ob_start();
		    	bp_settings_data_exporter_items();
		    	$return['exports'] = ob_get_clean();
		    	$return['submessage']=__( 'If you want to make a request, please click on the button below:', 'vibebp' );
		    }

		    return new WP_REST_Response( $return, 200 ); 
	    }
	   
	    function get_get_avatar_settings_permissions($request){
	    	$body = json_decode(stripslashes($_POST['body']),true);

	        if (empty($body['token'])){
           		return false;
	        }else{
	        	$token = $body['token'];
	        }
	        /** Get the Secret Key */
	        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
	        if (!$secret_key) {
	          	return false;
	        }
	        /** Try to decode the token */ /** Else return exception*/
	        try {
	            $user_data = JWT::decode($token, $secret_key, array('HS256'));
		        $this->user = $user_data->data->user;
	        	return true;

	        }catch (Exception $e) {
	            /** Something is wrong trying to decode the token, send back the error */
	            return false;
	        }
	    	

	    	return false;
	    } 

	    function set_avatar($request){

	    	$body = json_decode(stripslashes($_POST['body']),true);
	   	
	    	$return = array(
	    		'status'=>1,
	    		'message'=>__('Avatar uploaded','vibebp')
	    	);
	    	if ( !empty( $_FILES )  ) {
	    		
				add_filter('bp_attachment_upload_overrides',function($overrides){
					$overrides['test_form'] = FALSE;
					return $overrides;
				});

				$bp = buddypress();
				$bp->displayed_user = $this->user;

				if ( ! isset( $bp->avatar_admin ) ) {
					$bp->avatar_admin = new stdClass();
				}
//$avatar_size              = ( 'full' == $params['type'] ) ? '-bpfull' : '-bpthumb';
				
				
				$avatar = bp_core_avatar_handle_upload($_FILES, 'bp_members_avatar_upload_dir' );
				
				$bp->avatar_admin->step = 'crop-image';

				if ( $avatar ) { 
					
					
					$cropargs = array(
						'object'        => 'user',
						'avatar_dir'    => 'avatars',
						'item_id'       => $this->user->id,
						'original_file' => $bp->avatar_admin->image->url,
						'crop_x'        => $body['cropdata']['x'],
						'crop_y'        => $body['cropdata']['y'],
						'crop_w'        => $body['cropdata']['width'],
						'crop_h'        => $body['cropdata']['height']
					);
					$return['debug'] = $cropargs; 

					//bp_core_avatar_handle_crop( $cropargs );
					vibebp_avatar_handle_crop($cropargs,$this->user->id);

					$return['avatar'] = bp_core_fetch_avatar(array(
                                        'item_id' => $this->user->id,
                                        'object'  => 'user',
                                        'type'=>'full',

                                        'html'    => false
                                    ));
				}else{
					$return['status'] = 0;
				}
			}

			return new WP_REST_Response( $return, 200 ); 
	    }

	}
}

VIBE_BP_API_Rest_Settings_Controller::init();



function vibebp_avatar_handle_crop($args,$user_id){



	$args['item_id'] = (int) $args['item_id'];

	
	$relative_path = sprintf( '/%s/%s/%s', $args['avatar_dir'], $args['item_id'], basename( $args['original_file'] ) );

	$upload_path = bp_core_avatar_upload_path();
	$url         = bp_core_avatar_url();
	$upload_dir  = bp_upload_dir();

	$absolute_path = $upload_path . $relative_path;

	
	// Bail if the avatar is not available.
	if ( ! file_exists( $absolute_path ) )  {
		return false;
	}


	/** This filter is documented in bp-core/bp-core-avatars.php */
	$avatar_folder_dir = apply_filters( 'bp_core_avatar_folder_dir', $upload_path . '/' . $args['avatar_dir'] . '/' . $args['item_id'], $args['item_id'], $args['object'], $args['avatar_dir'] );
	
	
	// Bail if the avatar folder is missing for this item_id.
	if ( ! file_exists( $avatar_folder_dir ) ) {
		return false;
	}
	
	// Delete the existing avatar files for the object.
	$existing_avatar = bp_core_fetch_avatar( array(
		'object'  => $args['object'],
		'item_id' => $args['item_id'],
		'html' => false,
	) );
	
	/**
	 * Check that the new avatar doesn't have the same name as the
	 * old one before deleting
	 */
	if ( ! empty( $existing_avatar ) && $existing_avatar !== $url . $relative_path ) {
		bp_core_delete_existing_avatar( array( 'object' => $args['object'], 'item_id' => $args['item_id'], 'avatar_path' => $avatar_folder_dir ) );
	}
	
	// Make sure we at least have minimal data for cropping.
	if ( empty( $args['crop_w'] ) ) {
		$args['crop_w'] = bp_core_avatar_full_width();
	}

	if ( empty( $args['crop_h'] ) ) {
		$args['crop_h'] = bp_core_avatar_full_height();
	}

	// Get the file extension.
	$data = @getimagesize( $absolute_path );
	$ext  = $data['mime'] == 'image/png' ? 'png' : 'jpg';

	$args['original_file'] = $absolute_path;
	$args['src_abs']       = false;
	$avatar_types = array( 'full' => '', 'thumb' => '' );
	
	foreach ( $avatar_types as $key_type => $type ) {
		if ( 'thumb' === $key_type ) {
			$args['dst_w'] = bp_core_avatar_thumb_width();
			$args['dst_h'] = bp_core_avatar_thumb_height();
		} else {
			$args['dst_w'] = bp_core_avatar_full_width();
			$args['dst_h'] = bp_core_avatar_full_height();
		}
		
		$filename         = wp_unique_filename( $avatar_folder_dir, uniqid() . "-bp{$key_type}.{$ext}" );
		$args['dst_file'] = $avatar_folder_dir . '/' . $filename;

		$avatar_types[ $key_type ] = wp_crop_image( $args['original_file'], (int) $args['crop_x'], (int) $args['crop_y'], (int) $args['crop_w'], (int) $args['crop_h'], (int) $args['dst_w'], (int) $args['dst_h'], $args['src_abs'], $args['dst_file'] );
	}

	// Remove the original.
	@unlink( $absolute_path );

	return $avatar_types;
}

