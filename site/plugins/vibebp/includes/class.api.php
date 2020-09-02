<?php
/**
 * Initialise plugin
 *
 * @class       VibeBP_Init
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 * @copyright   VibeThemes
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



class VibeBP_API_Init{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_API_Init();
        return self::$instance;
    }

	private function __construct(){

		add_action('rest_api_init',array($this,'rest_api'));
		//add_filter('bp_rest_activity_get_item_permissions_check',array($this,'auth'),99,2);
		
	}

	function rest_api(){


        register_rest_route( Vibe_BP_API_NAMESPACE, '/loggedinmenu/', array(
            array(
                'methods'             => 'POST',
                'callback'            =>  array( $this, 'loggedin_menu' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));

        register_rest_route( Vibe_BP_API_NAMESPACE, '/profilemenu/', array(
            array(
                'methods'             => 'POST',
                'callback'            =>  array( $this, 'profile_menu' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        )); 

        register_rest_route( Vibe_BP_API_NAMESPACE, '/getPost/', array(
            array(
                'methods'             => 'POST',
                'callback'            =>  array( $this, 'getPost' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        )); 

        
		register_rest_route( Vibe_BP_API_NAMESPACE, '/sociallogin/', array(
			array(
				'methods'             => 'POST',
				'callback'            =>  array( $this, 'social_login' ),
				'permission_callback' => array( $this, 'social_login_verify' ),
			),
		));

        register_rest_route(Vibe_BP_API_NAMESPACE,'/sidebar/(?P<sidebar_id>[^/]+)',
            array(
                'methods'              => 'POST',
                'callback'             => array( $this, 'get_sidebar' ),
                'permissions_callback' => array( $this, 'user_permissions_check' )
            )
        );
        register_rest_route(Vibe_BP_API_NAMESPACE,'/save_sidebar',
            array(
                'methods'              => 'POST',
                'callback'             => array( $this, 'save_sidebar' ),
                'permissions_callback' => array( $this, 'user_permissions_check' )
            )
        );

        register_rest_route(
            Vibe_BP_API_NAMESPACE,'/widget/(?P<id_base>[^/]+)/(?P<widget_number>[\d]+)',
            array(
                'methods'              => 'post',
                'callback'             => array( $this, 'get_widget' ),
                'permission_callback'  => array( $this, 'user_permissions_check' )
            )
        );


        register_rest_route(
            Vibe_BP_API_NAMESPACE,'/process_notification',
            array(
                'methods'              => 'post',
                'callback'             => array( $this, 'process_notification' ),
                'permission_callback'  => array( $this, 'user_permissions_check' )
            )
        );

        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/fetch_media', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'get_member_attachments' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/upload_media', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'upload_attachment' ),
                'permission_callback' => array( $this, 'user_upload_permissions_check' ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/upload_media_stream', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'upload_attachment_stream' ),
                'permission_callback' => array( $this, 'user_upload_stream_permissions_check' ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/upload_media_stream/(?P<file>\w+)', array(
            array(
                'methods'             =>  'PATCH',
                'callback'            =>  array( $this, 'patch_upload_attachment_stream' ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/upload_media_stream/(?P<file>\w+)', array(
            array(
                'methods'             =>  'HEAD',
                'callback'            =>  array( $this, 'get_upload_attachment_offset' ),
            ),
        ));

        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/upload_media_stream/(?P<file>\w+)/complete_stream', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'finalise_upload_attachment_stream' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));
        register_rest_route( Vibe_BP_API_NAMESPACE, '/user/delete_media', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'delete_attachment' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));
        
        
        register_rest_route( Vibe_BP_API_NAMESPACE, '/registerUser', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'registerUser' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));

        register_rest_route( Vibe_BP_API_NAMESPACE, '/forgotPassword', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'forgotPassword' ),
                'permission_callback' => array( $this, 'user_permissions_check' ),
            ),
        ));
        
        register_rest_route( Vibe_BP_API_NAMESPACE, '/chat/upload', array(
            array(
                'methods'             =>  'POST',
                'callback'            =>  array( $this, 'upload_chat_attachment' ),
                'permission_callback' => array( $this, 'user_upload_permissions_check' ),
            ),
        ));
        
	}



    function user_permissions_check($request){
        
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

        if (empty($secret_key)){
            return false;
        }
        try {
            $user_data = JWT::decode($token, $secret_key, array('HS256'));

            $this->user = $user_data->data->user;
            
            /** Let the user modify the data before send it back */
            return true;

        }catch (Exception $e) {
            /** Something is wrong trying to decode the token, send back the error */
            return false;
        }
        

        return false;
    }

    function user_upload_permissions_check($request){

        $body = json_decode(stripslashes($_POST['body']),true);

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

    function social_login_verify($request){

        /*
        #https://firebase.google.com/docs/auth/admin/verify-id-tokens [Learn and Credit aka@vibe]
        */
 
    	$body = json_decode($request->get_body());

    	if($body->security == vibebp_get_api_security() || $body->client_id == vibebp_get_setting('client_id')){

            $firebase_public_keys = file_get_contents(plugin_dir_path(__FILE__).'core/keys.txt');
            if(!$firebase_public_keys){
                return false;
            }
            /** Try to decode the token */ /** Else return exception*/
            try {
                $token = JWT::decode($token, json_decode($firebase_public_keys), array('RS256'));
                $data = array(
                    'status' => 1,
                    'data' => $token,
                    'message'=>_x('Valid Token','Valid Token','vibebp')
                );
                return true;
            }catch (Exception $e) {
                $data = array(
                    'status' => 0,
                    'data' => 'jwt_auth_invalid_token',
                    'message'=>$e->getMessage()
                );
                return $data;
            }

			return true;	
		}

    	

    	return false;
    }


    function loggedin_menu($request){
        //$this->user->id
        $menuLocations = get_nav_menu_locations(); 
        $menu = apply_filters('vibebp_loggedin_menu','loggedin',$this->user);
        $menuID = $menuLocations[$menu]; 
        if(empty($menuID)){
            return new WP_REST_Response(array('status'=>0,'message'=>__('No menu connected to loggedinmenu location.','vibebp')));
        }
        add_filter('wp_setup_nav_menu_item',array($this,'remove_buddypress_invalid_url'));
        $primaryNav = wp_get_nav_menu_items($menuID);

        return new WP_REST_Response(array('status'=>1,'menu'=>$primaryNav));
    }

    function profile_menu($request){
        $menuLocations = get_nav_menu_locations(); 
        $menu = apply_filters('vibebp_profile_menu','profile',$this->user);
        $menuID = $menuLocations[$menu]; 
        
        $nav = get_transient('bp_rest_api_nav');
        if(!empty($nav)){
            foreach($nav as $key=>$value){
                $nav[$key]['icon'] = apply_filters('vibebp_component_icon','vicon vicon-menu',$value['css_id']);
            }    
        }
        
        if(empty($menuID)){
            return new WP_REST_Response(array('status'=>1,'menu'=>$nav,'bpmenu'=>$nav,'message'=>__('No menu set for profile.','vibebp')));
        }
        add_filter('wp_setup_nav_menu_item',array($this,'remove_buddypress_invalid_url'));
        $primaryNav = wp_get_nav_menu_items($menuID);
        return new WP_REST_Response(array('status'=>1,'menu'=>$primaryNav,'bpmenu'=>$nav));
    }

    function remove_buddypress_invalid_url($menu_item){
        $menu_classes = $menu_item->classes;

        if ( is_array( $menu_classes ) ) {
            $menu_classes = implode( ' ', $menu_item->classes);
        }
        preg_match( '/\sbp-(.*)-nav/', $menu_classes, $matches );

        if ( empty( $matches[1] ) ) {
            return $menu_item;
        }

        $menu_item->icon = apply_filters('vibebp_component_icon','vicon vicon-menu',$matches[1]);
        $menu_item->post_content = $matches[1];
        $menu_item->css_id=$matches[1];
        $menu_item->_invalid =0;

        $bp_pages = bp_nav_menu_get_loggedin_pages();
        $menu_item->guid = $bp_pages[$matches[1]]->guid;
        return $menu_item;
    }

    function social_login($request){

    	$secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;

    	if (!$secret_key) {
          	return new WP_REST_Response(array(
          		'status'=>0,
            	'code'=>'vibebp_jwt_security_missing',
              	'message'=>_x('Secret key missing','JWT authentication error','vibebp'),
              )
        	);
        }

        $body = json_decode($request->get_body());

        $user_id = email_exists($body->user->email);
        $user = get_userdata($user_id);
	        
        /** If the authentication fails return a error*/
        if (!$user) {
            $name = explode(' ', $body->user->name);
            $user_id = wp_insert_user(array(
                'user_login'=>$body->user->email,
                'user_pass'=>wp_generate_password(18,false,false),
                'user_nicename'=>$body->user->name,
                'user_email'=>$body->user->email,
                'display_name'=>$body->user->name,
                'nickname'=>$body->user->name,
                'first_name'=>$name[0],
                'last_name'=>((count($name) > 1)?$name[(count($name)-1)]:''),
            ));

            if($user_id){
                $user = get_userdata($user_id);    
            }else{
                return new WP_REST_Response(
                    array(
                        'status'=>0,
                        'message'=>_x('Unable to register.','login error','vibebp')
                    )
                );
            }
            
        }

        update_user_meta($user_id,'firebase_uid',$body->user->uid);
        bp_update_user_last_activity($user_id);
        
        do_action('vibebp_user_registered',$user_id);
        /** Valid credentials, the user exists create the according Token */
        $issuedAt = time();
        $notBefore = apply_filters( VIBEBP.'_token_expire_not_before', $issuedAt, $issuedAt);
        $expire = apply_filters( VIBEBP.'_token_expire', $issuedAt  + (DAY_IN_SECONDS * 7), $issuedAt);


        $token = array(
            'iss' => get_bloginfo('url'),
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expire,
            'data' => apply_filters('vibebp_jwt_token_data',array(
                'user' => array(
                    'id' => $user->ID,
                    'username'=>$user->user_login,
                    'slug'=>$user->user_nicename,
                    'email'=>$user->user_email,
                    'avatar'=> (function_exists('bp_core_fetch_avatar')?bp_core_fetch_avatar(array(
                                    'item_id' => $user->ID,
                                    'object'  => 'user',
                                    'type'=>'full',
                                    'html'    => false
                                )):get_avatar($user->user_email,240)),
                    'displayname'=>$user->display_name,
                    'roles'=>$user->roles,
                    'caps'=> $user->allcaps,
                    'profile_link'=>vibebp_get_profile_link($user->user_nicename)
	                ),
	            )
            ),
        );
        /** Let the user modify the token data before the sign. */
        $token = JWT::encode(apply_filters(VIBEBP.'jwt_auth_token_before_sign', $token, $user), $secret_key);
        /** The token is signed, now create the object with no sensible user data to the client*/
        $data = array(
        	'status' => 1,
            'token' => $token,
            'message'=>_x('Token generated','Token generated','vibebp')
        );
        /** Let the user modify the data before send it back */
        return new WP_REST_Response(apply_filters(VIBEBP.'jwt_auth_token_before_dispatch', $data, $user));
    }


    function get_sidebar($request){
        global $wp_registered_sidebars, $sidebars_widgets;
        $sidebar_id = $request->get_param( 'sidebar_id' );
        
        if(empty($this->user)){
            $body = json_decode($request->get_body(),true);
            $token = $body['token'];
            $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
            try {
                $user_data = JWT::decode($token, $secret_key, array('HS256'));
                $this->user = $user_data->data->user;
            }catch (Exception $e) {
                return new WP_REST_Response($data,200);
            }
        }
        $data = array('status'=>0,'user'=>$this->user);


        if ( ! isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
            return new WP_REST_Response(array('status'=>0,'message'=>__('No such sidebar.','vibebp').$sidebar_id,'sidebars'=>$wp_registered_sidebars));
        }
        
        

        $widgets = get_user_meta($this->user->id,$sidebar_id,true);
        if(empty($widgets)){
            if(!empty($sidebars_widgets[ $sidebar_id ])){
                $data['status']=1;
                $data['widgets']=array();
                $data['unusedwidgets']=[];
                foreach ( $sidebars_widgets[ $sidebar_id ] as $widget ) {
                    preg_match( '/^(.+)-(\d+)$/', $widget, $matches );
                    $data['widgets'][]=rest_url( Vibe_BP_API_NAMESPACE.'/widget/' . $matches[1] . '/' . $matches[2] );
                }
            }
        }else{
            $data['status']=1;
            $data['widgets']=$widgets;
            $data['unusedwidgets']=[];
            if(!empty($sidebars_widgets[ $sidebar_id ])){
                foreach ( $sidebars_widgets[ $sidebar_id ] as $widget ) {
                    preg_match( '/^(.+)-(\d+)$/', $widget, $matches );

                    $link = rest_url( Vibe_BP_API_NAMESPACE.'/widget/' . $matches[1] . '/' . $matches[2] );
                    $flag=0;
                    if(!empty($widgets)){
                        foreach($widgets as $wid){
                            if(is_array($wid)){
                                if($wid['link'] == $link){
                                    $flag=1;
                                }
                            }else{
                                if($wid == $link){
                                    $flag=1;
                                }
                            }
                        }
                    }
                    if(empty($flag)){
                        $data['unusedwidgets'][]=array(
                            'link'=>$link,
                            'name'=>$matches[1]
                        );
                    }
                }
            }
        }
        
        return new WP_REST_Response($data,200);
    }

    function save_sidebar($request){
        $body = json_decode($request->get_body(),true);

        if(empty($this->user)){
            $body = json_decode($request->get_body(),true);
            $token = $body['token'];
            $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
            try {
                $user_data = JWT::decode($token, $secret_key, array('HS256'));
                $this->user = $user_data->data->user;
            }catch (Exception $e) {
                return new WP_REST_Response($data,200);
            }
        }

        update_user_meta($this->user->id,$body['sidebar_id'],$body['widgets']);
        return new WP_REST_Response(
            array('status'=>1,'user'=>$this->user,'widgets'=>$body['widgets'],'message'=>__('Sidebar Saved.','vibebp')),200); 
    }

    function get_widget($request){

        $id_base = $request->get_param( 'id_base' );
        $number  = absint( $request->get_param( 'widget_number' ) );

        global $wp_registered_widgets;
        if ( ! isset( $wp_registered_widgets[ $id_base . '-' . $number ] ) ) {
            return new WP_REST_Response(array('status'=>0,'message'=>__('No widget found!','vibebp')),200);
        }
        $widget_options = get_option( 'widget_' . $id_base );
        $widget            = $wp_registered_widgets[ $id_base . '-' . $number ];
        $widget['id_base'] = $id_base;
        $widget['options'] = $widget_options[ $number ];
        ob_start();
        the_widget(get_class($widget['callback'][0]),$widget['options']);
        $widget['html']=ob_get_clean();
        unset($widget['callback']);
         return new WP_REST_Response(array('status'=>1,'widget'=>$widget),200);
    }


    function process_notification($request){
        $body = json_decode($request->get_body(),true);
        $message = vibebp_process_notification($body); 
        if($message){
            if(is_array($message)){
                return new WP_REST_Response(
                    array(
                        'status'=>1,
                        'text'=>$message['message'],
                        'actions'=>$message['actions'],
                        'event'=>$body['type'],
                        'item_id'=>(!empty($body['chat_id'])?$body['chat_id']:'')
                    )
                ,200);    
            }else{
                return new WP_REST_Response(array('status'=>1,'text'=>$message),200);    
            }
        }else{
            return new WP_REST_Response(array('status'=>0,'message'=>$message),200);
        }
        
    }

    function getPost($request){
        $body = json_decode($request->get_body(),true);

        $query = new WP_Query(array(
            'post_type'=>$body['post_type'],
            'p'=>$body['id']
        ));
        if($query->have_posts()){
            while($query->have_posts()){
                $query->the_post();
                do_action('vibebp_getPost',$body,$this); //extract user if content restricted to user
                ob_start();
                the_content();
                $content = ob_get_clean();
            }
        }else{
            $content = '<div class="message">'.__('No page found.','vibebp').'</div>';
        }
        return new WP_REST_Response(array('status'=>1,'content'=>$content),200);
    }

    function get_member_attachments($request){
        $post = json_decode(file_get_contents('php://input'));
        $post = json_decode(json_encode($post),true);
        $list = array();

        $posts_per_page = 20;  // defualt per_page
        if(!empty($post['posts_per_page'])){
            if($post['posts_per_page']<=20){
                $posts_per_page = $post['posts_per_page'];
            }
        }

        if(!empty($post)){
            $media_query = new WP_Query(
                array(
                    'post_type' => 'attachment',
                    'post_status' => 'published',
                    'post_mime_type' => $post['post_mime_type']?$post['post_mime_type']:"",
                    'posts_per_page' => $posts_per_page,
                    'paged' => $post['paged']?$post['paged']:1,
                    'author'=>$this->user->id,
                    's' => $post['search_terms']?$post['search_terms']:''
                )
            );
            foreach ($media_query->posts as $post) {
                $list[] = $this->get_single_attachment($post);
            }
        }
        if(empty($list)){
            $data = array(
                'status' => 1,
                'message' => _x('No Media Found','No Media Found','vibe'),
                'data' => $list,
            );
        }else{
            $data = array(
                'status' => 1,
                'message' => _x('Media Found','Media Found','vibe'),
                'data' => $list,
                'total'=> $media_query->found_posts
            );
        }
        $data = apply_filters('vibe_fetch_media',$data,$post);
        return new WP_REST_Response($data, 200); 
    }

    function get_single_attachment($post){
        $attachment_id = $post->ID;
        $data = array(
            'name' => $post->post_name,
            'id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id)
        );
        $post_mime_type = get_post_mime_type($post);
        if(!empty($post_mime_type)){
            if(!isset($this->keyed_mime_types)){
                $this->keyed_mime_types = $this->keyed_mime_types();
            }
            if(!empty($this->keyed_mime_types[$post_mime_type])){
                $data['type'] = $this->keyed_mime_types[$post_mime_type];
            }else{
                $data['type'] = null;
            }
        }
        return $data;
    }

    function keyed_mime_types(){
        $key_pair = array();
        $mime_types = wp_get_mime_types();
        $a_mime_types = array();
        if(!empty($mime_types)){
            foreach ($mime_types as $key=>$value) {
                $expoloed_keys = explode("|",$key);
                foreach($expoloed_keys as $key1=>$value1){
                    $a_mime_types[$value1] = $value;
                }
            }
        }
        $ext_types = wp_get_ext_types();
        if(!empty($ext_types)){
            foreach ($ext_types as $key=>$value) {
                foreach($value  as $key1=>$value1){
                    if(!empty($a_mime_types[$value1])){
                        $key_pair[$a_mime_types[$value1]] = $key;
                    }   
                }
            }
        }
        return  $key_pair;
    }

    function user_upload_stream_permissions_check($request){
        
        $meta_data = $request->get_header('Upload-Metadata');
        if(!empty($meta_data)){
            $meta_data = explode(',',$request->get_header('Upload-Metadata'));
            $data = explode(' ',$meta_data[0]);
            if($data[0] == 'token'){
                $token = base64_decode($data[1]);
                /** Get the Secret Key */
                $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;

                if (empty($secret_key)){
                    return false;
                }
                try {
                    $user_data = JWT::decode($token, $secret_key, array('HS256'));
                    $this->user = $user_data->data->user;
                    return true;
                }catch (Exception $e) {
                    return false;
                }
            }
        }
        
        return false;
    }
    function upload_attachment_stream($request){


        $user_id = $this->user->id;
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        $meta_data = explode(',',$request->get_header('Upload-Metadata'));
        
        $fileData = array();
        foreach($meta_data as $data){
            
            foreach($meta_data as $data){
                if(strpos($data, 'filename') !== false){
                    $mdata = explode(' ', $data);
                    $fileName = base64_decode($mdata[1]);
                }else if(strpos($data, 'filetype') !== false){
                    $mdata = explode(' ', $data);
                    $fileType = base64_decode($mdata[1]);
                }else{
                    $mdata = explode(' ', $data);
                    $fileData[$mdata[0]] = base64_decode($mdata[1]);
                }
            }
        }

        $check = wp_check_filetype($fileName);

        if($check['ext'] && !empty($fileName)){
            
            $upload_dir_base = wp_upload_dir();
            $file_name = $upload_dir_base['path'].'/'.$fileName;

            if ( !file_exists( $file_name ) ) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->put_contents( $file_name, '' );
                $response = new WP_REST_Response($data, 201);

                $blog_id = '';
                if(function_exists('get_current_blog_id')){
                    $blog_id = get_current_blog_id();
                }
                // Addition of security stops offline uploads !! 
                $key = wp_generate_password(6,false,false);


                $fileData['name']=$fileName;
                $fileData['ext']=$fileType;
                $fileData['path']=$file_name;
                $fileData['offset']=0;
                $fileData['author']=$this->user->id;

                set_transient($key,$fileData,DAY_IN_SECONDS);

                $response->header( 'Location', get_rest_url($blog_id,Vibe_BP_API_NAMESPACE).'/user/upload_media_stream/'.$key); 
                return $response;
            }
        }

        return new WP_REST_Response(array('status'=>0,'message'=>__('Upload failed','vibebp')), 200);
    }

    function patch_upload_attachment_stream($request){
        
        $key = $request->get_param('file');
        $file = get_transient($key);

        if(!empty($file)){
            if(file_exists($file['path'])){
                $file['offset'] = $file['offset']+$request->get_header('Content-Length');
                set_transient($key,$file,DAY_IN_SECONDS);
                $return = $this->patch($file['path'],$request->get_header('Upload-Offset'),file_get_contents('php://input'));
                if($return){
                    $response = new WP_REST_Response($data, 204);
                    $response->header( 'Upload-Offset', $file['offset']); 
                    return $response;
                }
            }
        }
        return new WP_REST_Response(array('status'=>0,'message'=>__('Upload failed','vibebp')), 200);
    }

    function get_upload_attachment_offset($request){
        $key = $request->get_param('file');
        $response = new WP_REST_Response('', 204);
        $response->header( 'Upload-Offset', $file['offset']); 
        return $response;
    }


    function finalise_upload_attachment_stream($request){
        $key = $request->get_param('file');
        $file = get_transient($key);
        delete_transient($key);
        
        $filetype = wp_check_filetype( basename( $file['path'] ), null );

        $external_process = apply_filters('vibebp_finalise_upload_attachment_stream',0,$file); 
        if(empty($external_process)){
            $wp_upload_dir = wp_upload_dir();
            $attachment = array(
                'guid'           => $wp_upload_dir['url'] . '/' . basename( $file['path'] ), 
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file['path'] ) ),
                'post_content'   => '',
                'post_status'    => 'inherit',
                'post_author'    => $file['author']
            );
             
            // Insert the attachment.
            if($file['parent']){
                $attach_id = wp_insert_attachment( $attachment, $file['name'], $file['parent'] );    
            }else{
                $attach_id = wp_insert_attachment( $attachment, $file['name']);    
            }
            
            require_once( ABSPATH . 'wp-admin/includes/media.php' );
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
          
        
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file['path'] );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            $relativefilePath = ltrim(str_replace($wp_upload_dir['basedir'],'',$file['path']),'/');
            update_post_meta($attach_id,'_wp_attached_file',$relativefilePath); //wordpress bug
        }

        return new WP_REST_Response(apply_filters('vibebp_upload_attachment_stream_message',array('status'=>1,'message'=>sprintf(__('%s upload complete','vibebp'),basename($file['path'])))), 200);
    }

    function patch($file, $offset, $new_data) {
        if (!$f = fopen($file, 'r+b')) {
            return false;
        }
        fseek($f, $offset);
        fwrite($f, $new_data, strlen($new_data));
        return true;
        fclose($f);
    }

    function upload_attachment($request){

        if(empty($this->user)){
            new WP_REST_Response(array(), 401);
        }
        $body = json_decode(stripslashes($_POST['body']),true);
        $user_id = $this->user->id;
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if(!empty($_FILES['file'])){
            $uploadedfile = $_FILES['file'];
            $file_mime_type= $_FILES['file']['type'];
            $file_size=$_FILES['file']['size'];
            $upload_overrides = array( 'test_form' => false );

            $can_upload = apply_filters('vibe_can_upload_media',true,$_FILES,$request);
            if($can_upload){
                $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
                if ( $movefile && ! isset( $movefile['error'] ) ) {
                    if ( $movefile && !isset( $movefile['error'] ) ) {
                        $filePath=$movefile['url'];
                        $attachment = array(
                            'guid'           => $filePath,
                            'post_mime_type' => $movefile['type'],
                            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filePath ) ),
                            'post_content'   => '',
                            'post_status'    => 'inherit',
                            'post_author'      => $user_id,
                            'post_size'      => $_FILES['file']['size']
                        );
                        // Insert the attachment.
                        $attach_id = wp_insert_attachment( $attachment, $filePath );
                        if(!empty($attach_id)){
                            $post = get_post($attach_id);
                            if($post){
                                $attachment_data = $this->get_single_attachment($post);
                                $return = array('status'=> 1,'message'=>_x('File is valid, and was successfully uploaded','vibe'),'data'=>$attachment_data);
                            }
                        }
                    }
                } else {
                    /**
                     * Error generated by _wp_handle_upload()
                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                     */
                    $return=array('status'=> 0,'message'=>_x('File uploading failed','vibe'),'data'=>$movefile);
                }   
            }else{
                $return=array('status'=> 0,'message'=>_x('Can not upload','vibe'),'data'=>[]);
            }
        }else{
            $return=array('status'=> 0,'message'=>_x('File not found','vibe'),'data'=>[]);
        }
        return new WP_REST_Response($return, 200);
    }

    function delete_attachment($request){
        $body = json_decode($request->get_body(),true);
        
        $author = get_post_field('post_author',$body['media']['id']);
        if($this->user->id == $author){
            if(wp_delete_attachment($body['media']['id'])){
                return new WP_REST_Response(array('status'=>1,'message'=>__('Attachment deleted.','vibebp')), 200);
            }
        }

        return new WP_REST_Response(array('status'=>0,'message'=>__('Can not delete attachment.','vibebp')), 200);
    }

    function forgotPassword($request){

        $body = json_decode($request->get_body(),true);

        if(empty($body['email']) || !is_email($body['email'])){
            return new WP_REST_Response(array('status'=>0,'message'=>__('Invalid email.','vibebp')), 200);
        }

        if(!email_exists($body['email'])){
            return new WP_REST_Response(array('status'=>0,'message'=>__('No registered user found with this mail id !','forgot password email message','vibebp')),200);
        }

        $user_data = get_user_by( 'email', trim( $body['email'] ) );
        // Redefining user_login ensures we return the right case in the email.
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;

        //Important WordPress hooks
        do_action( 'retreive_password', $user_login );
        
        $allow = apply_filters( 'allow_password_reset', true, $user_data->ID );
        if ( ! $allow ) {
            return new WP_REST_Response(array('status'=>0,'message'=>__('Password reset is not allowed for this user','vibebp')),200);
        } elseif ( is_wp_error( $allow ) ) {
            return new WP_REST_Response(array('status'=>0,'message'=>__('Password reset is not allowed.','vibebp')),200);
        }

        // Generate something random for a password reset key.
        $key = wp_generate_password( 20, false );

        do_action( 'retrieve_password_key', $user_login, $key );

        // Now insert the key, hashed, into the DB.
        if ( empty( $wp_hasher ) ) {
            require_once ABSPATH . WPINC . '/class-phpass.php';
            $wp_hasher = new PasswordHash( 8, true );
        }

        global $wpdb;
        $hashed = time() . ':' . $wp_hasher->HashPassword( $key );
        $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

        
        $email_type = $args['action'];
        $bpargs = array(
            'tokens' => array(
                'user.username'=>$user_login,
                'user.forgotpasswordlink'=> '<a href="'.site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login').'">'.site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login').'</a>',
                ),
        );
        
        $email_template = apply_filters('vibebp_forgot_password','wplms_forgot_password');
        
        $check = bp_send_email( $email_template,$user_email, $bpargs );
        //vibebp_forgot_password
        if ( function_exists('bp_send_email') && !$check){
            return new WP_REST_Response(array('status'=>1,'message'=>__('The e-mail could not be sent.','vibebp') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.','vibebp') ),200);
        }else{
            return new WP_REST_Response(array('status'=>1,'message'=>__('Please check your email for password recovery !','forgot password mail message','vibebp')),200);
        }
        return new WP_REST_Response(array('status'=>1,'message'=>__('Something went wrong','vibebp')), 200);
    }

    function registerUser($request){
        $body = json_decode($request->get_body(),true);

        $has_email=0;
        $has_password=0;
        if(is_array($body)){
            foreach($body as $key => $value){
                if($value['type'] == 'email'){
                    $has_email=1;
                    if(!is_email($value['value'])){
                        return new WP_REST_Response(array('status'=>0,'message'=>__('Invalid email.','vibebp')), 200);
                    }else{
                        $has_email = $value['value'];
                    }
                }
                if($value['type'] == 'password'){
                    $has_password=$value['type'];
                }
            }
        }
        

        if($has_password && $has_email || apply_filters('vibebp_register_user_bypass',0,$body)){
            if(email_exists($has_email)){
                return new WP_REST_Response(array('status'=>0,'message'=>__('User already registered with email ID.','vibebp')), 200);
            }else{
                $user_args['user_email'] = $has_email;
                $user_args['user_login'] = $user_args['user_email'];
                //$user_id = wp_insert_user($user_args);
                $usermeta = array();
                $user_id = bp_core_signup_user( $user_args['user_login'], $has_password, $user_args['user_email'], $usermeta );
                do_action('vibebp_register_user',$user_id,$body);
                return new WP_REST_Response(array('status'=>1,'message'=>__('Please check your email to activate your account.','vibebp')), 200);
            }

        }else{
            return new WP_REST_Response(array('status'=>0,'message'=>__('Email ID or Password Missing','registration password email message','vibebp')),200);
        }
    }


    function upload_chat_attachment($request){

        $body = json_decode(stripslashes($_POST['body']),true);
        $user_id = $this->user->id;
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if(!empty($_FILES['file'])){
            $uploadedfile = $_FILES['file'];
            $file_mime_type= $_FILES['file']['type'];
            $file_size=$_FILES['file']['size'];
            $upload_overrides = array( 'test_form' => false );

            $can_upload = apply_filters('vibe_chat_can_upload_media',true,$_FILES,$this->user->id);
            if($can_upload){
                $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
                if ( $movefile && ! isset( $movefile['error'] ) ) {
                    if ( $movefile && !isset( $movefile['error'] ) ) {
                        $filePath=$movefile['url'];
                        return new WP_REST_Response(array('status'=>1,'url'=>$filePath),200);
                    }else{
                        return new WP_REST_Response(array('status'=>0,'message'=>$movefile['error']),200);
                    }
                }
            }
        }

        return new WP_REST_Response(array('status'=>0,'message'=>__('Something webt wrong','vibebp')),200);
    }
}

VibeBP_API_Init::init();