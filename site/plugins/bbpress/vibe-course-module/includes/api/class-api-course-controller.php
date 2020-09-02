<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'BP_Course_Rest_Course_Controller' ) ) {
	
	class BP_Course_Rest_Course_Controller extends BP_Course_Rest_Controller {


		/**
		 * Register the routes for the objects of the controller.
		 *
		 * @since 3.0.0
		 */
		public function register_routes() {

			register_rest_route( $this->namespace, '/track', array(
				array(
					'methods'             =>  WP_REST_Server::READABLE,
					'callback'            =>  array( $this, 'track_components' ),
				),
			));
			register_rest_route( $this->namespace, '/track/(?P<id>\d+)?', array(
				array(
					'methods'             =>  WP_REST_Server::READABLE,
					'callback'            =>  array( $this, 'track_components' ),
					'permission_callback' => array( $this, 'get_api_tracker_permissions' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));

			register_rest_route( $this->namespace, '/blog', array(
				array(
					'methods'             =>  WP_REST_Server::READABLE,
					'callback'            =>  array( $this, 'get_blog' ),
				),
			));
			register_rest_route( $this->namespace, '/blog/(?P<id>\d+)?', array(
				array(
					'methods'             =>  WP_REST_Server::READABLE,
					'callback'            =>  array( $this, 'get_blogpost' ),
					'args'                     	=>  array(
						'id'                       	=>  array(
							'validate_callback'     =>  function( $param, $request, $key ) {
														return is_numeric( $param );
													}
						),
					),
				),
			));


			register_rest_route( $this->namespace, '/contact', array(
				array(
					'methods'                   =>  'POST',
					'callback'                  =>  array( $this, 'contact_message' ),
					'permission_callback' => array( $this, 'get_contact_message_permissions_check' ),
				),
			));

			/* =================== */

			register_rest_route( $this->namespace, '/' . $this->type, array(
				array(
					'methods'             =>  WP_REST_Server::READABLE,
					'callback'            =>  array( $this, 'get_items' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_item_schema' ),
			));

			register_rest_route( $this->namespace, '/' . $this->type . '/(?P<id>\d+)?', array(
				'methods'                   =>  WP_REST_Server::READABLE,
				'callback'                  =>  array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                     	=>  array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					'id'                       	=>  array(
						'validate_callback'     =>  function( $param, $request, $key ) {
													return is_numeric( $param );
												}
					),
				),
				'schema' => array( $this, 'get_item_schema' ),

			) );

			register_rest_route( $this->namespace, '/' . $this->type . '/featured', array(
				'methods'                   =>  WP_REST_Server::READABLE,
				'callback'                  =>  array( $this, 'get_featured_courses' ),
				'permission_callback'       =>  array( $this, 'get_featured_permissions_check' ),
				'args'                     	=>  array(
					'id'                       	=>  array(
						'validate_callback'     =>  function( $param, $request, $key ) {
													return is_numeric( $param );
												}
						),
					),
			) );

			register_rest_route( $this->namespace, '/' . $this->type . '/popular', array(
				'methods'                   =>  WP_REST_Server::READABLE,
				'callback'                  =>  array( $this, 'get_popular_courses' ),
				'permission_callback'       =>  array( $this, 'get_popular_permissions_check' ),
				'args'                     	=>  array(
					'id'                       	=>  array(
						'validate_callback'     =>  function( $param, $request, $key ) {
													return is_numeric( $param );
												}
						),
					),
			) );

			register_rest_route( $this->namespace, '/' . $this->type . '/filters(?P<filter>.+)?', array(
				'methods'                   =>  WP_REST_Server::READABLE,
				'callback'                  =>  array( $this, 'get_filtered_courses' ),
				'permission_callback'       =>  array( $this, 'get_filtered_courses_permissions_check' ),
				'args'                     	=>  array(
					'id'                       	=>  array(
						'validate_callback'     =>  function( $param, $request, $key ) {
													return is_numeric( $param );
												}
						),
					),
			) );

			register_rest_route( $this->namespace, '/instructors', array(
				'methods'                   =>  WP_REST_Server::READABLE,
				'callback'                  =>  array( $this, 'get_instructors' ),
				'permission_callback'       =>  array( $this, 'get_instructors_permissions_check' ),
				'args'                     	=>  array(
					'id'                       	=>  array(
						'validate_callback'     =>  function( $param, $request, $key ) {
													return is_numeric( $param );
												}
						),
					),
			) );

			register_rest_route( $this->namespace, '/instructors/(?P<instructor_id>\d+)?', array(
				'methods'                   =>  WP_REST_Server::READABLE,
				'callback'                  =>  array( $this, 'get_instructor' ),
				'permission_callback'       =>  array( $this, 'get_instructor_permissions_check' ),
				'args'                     	=>  array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					'id'                       	=>  array(
						'validate_callback'     =>  function( $param, $request, $key ) {
													return is_numeric( $param );
												}
					),
				),
			) );


			/* Taxonomies controller */
			register_rest_route( $this->namespace, '/' . $this->type . '/taxonomy(?P<taxonomy>.+)?', array(
				'methods'                   =>  WP_REST_Server::READABLE,
				'callback'                  =>  array( $this, 'get_course_terms' ),
				'permission_callback'       =>  array( $this, 'get_course_terms_permissions_check' ),
				'args'                     	=>  array(
					'id'                       	=>  array(
						'validate_callback'     =>  function( $param, $request, $key ) {
													return is_numeric( $param );
												}
						),
					),
			) );

			register_rest_route( $this->namespace, '/' . $this->type . '/category/(?P<id>\d+)?', array(
				'methods'                   =>  WP_REST_Server::READABLE,
				'callback'                  =>  array( $this, 'get_category' ),
				'permission_callback' 		=> array( $this, 'get_category_permissions_check' ),
				'args'                     	=>  array(
					'context' 				=> $this->get_context_param( array( 'default' => 'view' ) ),
					'id'                       	=>  array(
						'validate_callback'     =>  function( $param, $request, $key ) {
													return is_numeric( $param );
												}
					),
				),
				'schema' => array( $this, 'get_item_schema' ),

			) );
			
		}


		/*
	    TRACKER PERMISSIONS
	     */
	    function get_api_tracker_permissions($request){
	    	
	    	if(isset($request['id'])){
	    		$id = $request['id'];
		    	if(!is_numeric($id))
		    		return false;

		    	$user = get_userdata( $id );
				if ( $user === false ) {
				    return false;
				}else{
					$this->user_id = $id;
				}
	    	}else{
	    		$this->user_id = 0;
	    	}
	    	

	    	return true;
	    }

		public function track_components($request){


			$tracker = BP_Course_Rest_Tracker_Controller::init();
	    	
	    	
			$lastaccess = 0;
	    	if(isset($request['access'])){
	    		$lastaccess = intval($request['access']);
	    	}

	    	$api_tracker = $tracker->fetch_tracker($lastaccess);

	    	if(!empty($this->user_id)){
	    		
	    		$user_tracker = $tracker->fetch_tracker($lastaccess,$this->user_id);
	    		
	    		$user_access_tracker = $api_tracker_updates = array();

	    		if(is_array($user_tracker) && !empty($user_tracker)){
	    			

	    			// Track Access time
	    			if(!empty($user_tracker['updates'])){
	    				foreach($user_tracker['updates'] as $k=>$tracker_item){
	    					if($tracker_item['time'] > $lastaccess){
	    						if(empty($tracker_item['access_time'])){
			    					$user_tracker['updates'][$k]['access_time'] = time();
			    				}
	    					}
		    			}
		    			update_user_meta($this->user_id,'wplms_api_tracker',$user_tracker);
	    			}
	    		

	    			$api_tracker['counter']+=$user_tracker['counter'];

	    			if(!isset($api_tracker['updates']) || empty($api_tracker['updates'])){$api_tracker['updates']=array();}else{
	    				foreach($api_tracker['updates'] as $key=>$update){
	    					if($update['time'] < $lastaccess){
	    						unset($api_tracker['updates'][$k]);
	    					}
	    				}
	    			}
    				if(empty($user_tracker['updates'])){$user_tracker['updates']=array();}else{
    					foreach($user_tracker['updates'] as $key=>$update){
	    					if($update['time'] < $lastaccess){
	    						unset($user_tracker['updates'][$k]);
	    					}
	    				}
    				}
    				
	    			$api_tracker_updates = array_merge($api_tracker['updates'],$user_tracker['updates']);
	    			
					function cmp( $a, $b ) { 
					  if(  $a['time'] ==  $b['time'] ){ return 0 ; } 
					  return ($a['time'] < $b['time']) ? 1 : -1;
					} 

					if(!empty($api_tracker_updates)){
						usort($api_tracker_updates,'cmp');	
					}
					
	    			$user_tracker['updates'] = $api_tracker_updates;
	    			$api_tracker = array_merge($api_tracker,$user_tracker);
	    			
	    		}
	    	}

	    	$api_tracker['version'] = bp_course_get_setting( 'api_version', 'api','number' );
	    	if(!$api_tracker['version']){$api_tracker['version'] = 1;}

	    	
	    	

	    	

	    	if(isset($request['client_id'])){

	    		$client_id = $request['client_id'];
				$apps = get_option('wplms_apps');
				foreach($apps as $key => $app){
					if($app['app_id'] == $client_id){
						$api_tracker['client_secret'] = $app['app_secret'];
					}
				}

				$state = bp_course_get_setting( 'api_security_state', 'api','string' );
				$api_tracker['state'] = $state;

				$allcourses = wp_count_posts('course');
	    		$api_tracker['allcourses'] = intval($allcourses->publish);
	    		$num = wp_count_terms('course-cat');
	    		if(!is_wp_error($num)){
	    			$api_tracker['allcoursecategories'] = intval($num);	
	    		}
	    		
	    		$num = wp_count_terms('location');
	    		if(!is_wp_error($num)){
	    			$api_tracker['allcourselocations'] = intval($num);	
	    		}

	    		$num = wp_count_terms('level');
	    		if(!is_wp_error($num)){
	    			$api_tracker['allcourselevels'] = intval($num);	
	    		}

				$allposts = wp_count_posts('post');
				$api_tracker['blog'] = intval($allposts->publish);
	    	}

			return new WP_REST_Response( $api_tracker, 200 );
		}
		/**
		 * Get a collection of items
		 *
		 * @since 3.0.0
		 * 
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|WP_REST_Response
		 */
		public function get_items( $request ) {

			
			// Prepare the element data
			$posts_data = array();
			
			$defaults = array(
				'post_type'  	=> 'course',
				'post_status'	=> 'publish',
				'orderby' 		=> 'alphabetical',
				'order'			=> 'ASC',
				'per_page'		=>	20,
				'paged'			=>	0
			);


			$args = array();
			//Enter REQUEST IN ARGS FOR FILTERS
			
			$args = wp_parse_args($args,$defaults);

			if ( bp_course_has_items( $args ) ):
				while ( bp_course_has_items() ) : bp_course_the_item();
					global $post;
					$posts[]=$this->prepare_item_for_response($post,$request);
				endwhile;	
			endif;
			
			/**
			 * Filter the response.
			 *
			 * @since 3.0.0
			 *
			 * @param array $element_data
			 * @param WP_REST_Request $request
			 */
			$posts_data = apply_filters( 'bp_course_api_get_courses', $posts, $request );

			return new WP_REST_Response( $posts_data, 200 );
		}
		
		function get_user_from_token($token){

			global $wpdb;
			$user_id = $wpdb->get_var("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '$token'");

			if(is_numeric($user_id)){
				return $user_id;
			}

			return false;
			
		}

		function get_item_permissions_check($request){
			$headers = vibe_getallheaders();

			if(isset($headers['Authorization'])){
				$token = $headers['Authorization'];
				$this->token = $token;
				$this->user_id = $this->get_user_from_token($token);
				if($this->user_id){
					return true;
				}
			}
			return true;
		}
		/**
		 * Get one item from the post_types
		 *
		 * @since 3.0.0
		 * 
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|WP_REST_Response
		 */
		public function get_item( $request ) {
			
			$id = $request['id'];

			$post  = get_post( $id );

			if ( empty( $id ) || empty( $post->ID ) || ! in_array( $post->post_type, $this->registered_post_types ) ) {
				return new WP_Error( "bp_course_api_rest_{$this->type}_invalid_id", __( 'Invalid post id.', 'vibe' ), array( 'status' => 404 ) );
			}

			$request['context'] = 'full';
			if(!empty($this->user_id)){
				$request['context'] = 'loggedin';
			}
			$course = $this->prepare_item_for_response($post,$request);
			$post_data = array($course);
			
			/**
			 * Filter the response.
			 *
			 * @since 3.0.0
			 *
			 * @param array $element_data
			 * @param WP_REST_Request $request
			 */
			$post_data = apply_filters( 'bp_course_api_get_course', $post_data, $request );
			
			return new WP_REST_Response( $post_data, 200 );
		}

		/**
		 * Prepare the item for the REST response
		 *
		 * @since 3.0.0
		 * 
		 * @param Tailor_Element $item
		 * @param WP_REST_Request $request Request object.
		 * @return array
		 */
		public function prepare_item_for_response( $data, $request ) {

			$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

			$data    = $this->add_additional_fields_to_object( $data, $request );
			$data    = $this->filter_response_by_context( $data, $context );

			// Wrap the data in a response object.
			$response = rest_ensure_response( $data );

			//$response->add_links( $this->prepare_links( $course ) );
			/**
		 	* Filter the data for a response.
		 	*
		 	* The dynamic portion of the hook name, $this->post_type, refers to post_type of the post being
		 	* prepared for the response.
		 	*
		 	* @param WP_REST_Response   $response   The response object.
		 	* @param WP_Post            $post       Post object.
		 	* @param WP_REST_Request    $request    Request object.
		 	*/
			return apply_filters( "bp_course_rest_prepare_course", $response, $course, $request );
		}


		/**
		 * Get product data.
		 *
		 * @param COURSE $course
		 * @return array
		 */
		protected function add_additional_fields_to_object( $course, $request ) {

			$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
			switch($context){

				case 'full':
					$data = array(
						'course'				=> array(
							'id'                    => $course->ID,
							'name'                  => $course->post_title,
							'date_created'          => strtotime( $course->post_date_gmt ),
							'status'                => $course->post_status,	
							'price'                 => $this->get_price($course),
							'price_html'            => $this->get_price_html($course),
							'total_students'        => (int) get_post_meta( $course->ID, 'vibe_students', true ), 
							'seats'                 => bp_course_get_max_students($course->ID),
							'start_date'            => $this->get_course_start_date($course),
							'average_rating'        => $this->get_average_rating($course),
							'rating_count'          => $this->get_rating_count($course),
							'featured_image'		=> $this->get_course_featured_image($course),	
							'categories'			=> $this->get_taxonomy_terms($course,'course-cat'),	
							'instructor'            => $this->get_course_instructor($course->post_author),	
							'menu_order'            => $course->menu_order,
							),
						'description'			=> do_shortcode($course->post_content),
						'curriculum'            => $this->get_curriculum( $course ),
						'reviews'				=> $this->get_reviews($course),
						'instructors'			=> $this->get_course_instructors($course),
					);
					

					$data['purchase_link'] = $this->get_purchase_link($course);
				break;
				case 'loggedin':
					$data = array(
						'course'				=> array(
							'id'                    => $course->ID,
							'name'                  => $course->post_title,
							'date_created'          => strtotime( $course->post_date_gmt ),
							'status'                => $course->post_status,	
							'price'                 => $this->get_price($course),
							'price_html'            => $this->get_price_html($course),
							'total_students'        => (int) get_post_meta( $course->ID, 'vibe_students', true ), 
							'seats'                 => bp_course_get_max_students($course->ID),
							'start_date'            => $this->get_course_start_date($course),
							'average_rating'        => $this->get_average_rating($course),
							'rating_count'          => $this->get_rating_count($course),
							'featured_image'		=> $this->get_course_featured_image($course),	
							'categories'			=> $this->get_taxonomy_terms($course,'course-cat'),	
							'instructor'            => $this->get_course_instructor($course->post_author),	
							'menu_order'            => $course->menu_order,
							'user_status'			=>bp_course_get_user_course_status($this->user_id,$course->ID),
							'user_expiry'			=>bp_course_get_user_expiry_time($this->user_id,$course->ID),
							),
						'description'			=> do_shortcode($course->post_content),
						'curriculum'            => $this->get_curriculum( $course ),
						'reviews'				=> $this->get_reviews($course),
						'instructors'			=> $this->get_course_instructors($course),
					);
					

					$data['purchase_link'] = $this->get_purchase_link($course);
				break;
				default:
					$data = apply_filters('wplms_fetch_course_api',array(),$course,$request);

					if(empty($data)){
						$data = array(
							'id'                    => $course->ID,
							'name'                  => $course->post_title,
							'date_created'          => strtotime( $course->post_date_gmt ),
							'status'                => $course->post_status,	
							'price'                 => $this->get_price($course),
							'price_html'            => $this->get_price_html($course),
							'total_students'        => (int) get_post_meta( $course->ID, 'vibe_students', true ), 
							'seats'                 => bp_course_get_max_students($course->ID),
							'start_date'            => $this->get_course_start_date($course),
							'average_rating'        => $this->get_average_rating($course),
							'rating_count'          => $this->get_rating_count($course),
							'featured_image'		=> $this->get_course_featured_image($course),	
							'categories'			=> $this->get_taxonomy_terms($course,'course-cat'),	
							'instructor'            => $this->get_course_instructor($course->post_author),	
							'menu_order'            => $course->menu_order,	
						);
					}
				break;
			}

			return $data;
		}

		public function get_course_instructor($instructor_id){

			$field = 'Speciality';
			if(function_exists('vibe_get_option'))
			$field = vibe_get_option('instructor_field');

			return array(
				'id'     => $instructor_id, 
				'name'   => bp_core_get_user_displayname($instructor_id),
				'avatar' => bp_course_get_instructor_avatar_url($instructor_id),
				'sub'    => (bp_is_active('xprofile')?bp_get_profile_field_data('field='.$field.'&user_id='.$instructor_id):''),
			);
		}

		function get_member($user_id){
			$field = 'Location';
			if(function_exists('vibe_get_option'))
			$field = vibe_get_option('student_field');

			return array(
				'id'     => $user_id, 
				'name'   => bp_core_get_user_displayname($user_id),
				'avatar' => bp_course_get_instructor_avatar_url($user_id),
				'sub'    => (bp_is_active('xprofile')?bp_get_profile_field_data('field='.$field.'&user_id='.$user_id):''),
			);
		}

		public function get_price($course){
			$price = false;

			$free = get_post_meta($course->ID,'vibe_course_free',true);
			
			if(!empty($free) && $free == 'S'){
				$course->price = 0;
				return 0;
			}

			if(function_exists('wc_get_product')){
				$product_id = get_post_meta($course->ID,'vibe_product',true);
				if(get_post_type($product_id) == 'product'){
					$product = wc_get_product($product_id);
					$course->product = $product;
					$price = $product->get_price();
				}
				
			}
			return $price;
		}

		public function get_average_rating($course){
			$rating=get_post_meta($course->ID,'average_rating',true);
			if(empty($rating)){$rating = 0;}
			return $rating;
		}

		public function get_rating_count($course){
			$count=get_post_meta($course->ID,'rating_count',true);
			if(empty($count)){$count = 0;}
			return $count;	
		}

		public function get_course_featured_image($course){

			$post_thumbnail_id = get_post_thumbnail_id( $course );
			if(!empty($post_thumbnail_id)){
				$image = wp_get_attachment_image_src($post_thumbnail_id,'medium');
				$image = $image[0];
			}

			if(empty($image)){
                $image = vibe_get_option('default_course_avatar');
                if(empty($image)){
                    $image = VIBE_URL.'/assets/images/avatar.jpg';
                }
            }

            return $image;
		}

		public function get_price_html($course){

			$free = get_post_meta($course->ID,'vibe_course_free',true);
			if(isset($free) && $free != 'H'){
				return _x('FREE','REST API FREE course label','vibe');
			}
			$price_html =array();
			$single_price = '';

			$version =  bp_course_get_setting( 'app_version', 'api','number' ); 
			
			if(function_exists('WC')){
				$cart_url =  get_permalink( woocommerce_get_page_id( 'cart' ) );
				$woo_price = array();
				$product_id = get_post_meta($course->ID,'vibe_product',true);
				if(is_numeric($product_id)){
					$product = wc_get_product($product_id);
					if(is_object($product)){

						if($product->is_type( 'variable' )){

							$variations = $product->get_available_variations();
							foreach($variations as $variation){
								$cart_url = $cart_url.'?add-to-cart='.$product_id.'&variation_id='.$variation['variation_id'];
		    					foreach($variation['attributes'] as $key => $value){
		    						$cart_url = $cart_url.'&'.$key.'='.$value;
		    					}
		    					$variable_is_wplms = get_post_meta($variation['variation_id'],'variable_is_wplms',true);
		    					
		    					if(!empty($variable_is_wplms) && $variable_is_wplms == 'on'){
			    					
			    					$course_subscription_ed = get_post_meta($variation['variation_id'],'vibe_subscription',true);

			    					if(!empty($course_subscription_ed)){
			    						$duration = get_post_meta($variation['variation_id'],'vibe_duration',true);
			  							$product_duration_parameter = apply_filters('vibe_product_duration_parameter',86400,$variation['variation_id']);
			  							if(!empty($duration)){
			  								$course_subscription =  $duration*$product_duration_parameter;
			  							}
			    					}else{
			    						$course_subscription = bp_course_get_course_duration($course->ID);
			    					}
			    					$course_retakes = '';
			    					$quiz_retakes = '';
			    					$course_certificate = get_post_meta($variation['variation_id'],'vibe_enable_certificate',true);
			    					$course_badge = get_post_meta($variation['variation_id'],'vibe_enable_badge',true);
			    					$course_retake = get_post_meta($variation['variation_id'],'vibe_enable_course_retakes',true);
			    					$quiz_retake = get_post_meta($variation['variation_id'],'vibe_enable_quiz_retakes',true);

			    					if(!empty($course_retake) && $course_retake == 'S'){
			    						 $course_retakes = get_post_meta($variation['variation_id'],'vibe_course_retakes',true); 
			    					}
			    					if(!empty($quiz_retake) && $quiz_retake == 'S'){
			    						$quiz_retakes = get_post_meta($variation['variation_id'],'vibe_quiz_retakes',true);
			    					}

			    					$min_price = $variation['display_price'];

			    					$var_price = array(	
											'type' => 'inapp',
											'source' => 'woocommerce',
											'value' => $min_price,
											'html'=> $variation['price_html'],
											'link'=> $cart_url,
											'extras'=>array(
													array('id'=>'subscription','value' => $course_subscription,'label'=>_x('Subscription','','vibe')),
													array('id'=>'course_certificate','value' => $course_certificate,'label'=>_x('Course Certificate','','vibe')),
													array('id'=>'course_badge','value' => $course_badge,'label'=>_x('Course Badge','','vibe')),
													array('id'=>'course_retakes','value' => $course_retakes,'label'=>_x('Course Retakes','','vibe')),
													array('id'=>'quiz_retakes','value' => $quiz_retakes,'label'=>_x('Quiz Retakes','','vibe')),
													
												),
										);
			    					if(function_exists('groups_get_group')){
			    						$batch_id = get_post_meta($variation['variation_id'],'vibe_course_batches',true);
				    					$batch = groups_get_group( array( 'group_id' => $batch_id) );
				    					array_push($var_price['extras'],array('id'=>'batch','value' =>  $batch->name,'label'=>_x('Batch','','vibe')));
			    					}

									array_push($price_html,$var_price);

			    				}
							}

						}else{
							
							if($version > 1){
								$cart_url = $cart_url.'?add-to-cart='.$product_id;
								array_push($price_html,array(	
												'type' => 'inapp',
												'source' => 'woocommerce',
												'value' =>  $product->get_price(),
												'html'=> $product->get_price_html(),
												'link'=> $cart_url,
												'extras'=>array(
													array(
														'id'=>'subscription',
														'value' => bp_course_get_course_duration($course->ID),
														'label'=>_x('Subscription','','vibe')
													),
												),
											));
							}else{
								$single_price = $product->get_price_html();
							}
						}

					}
				}
			}

			if(function_exists('pmpro_getAllLevels')){
				$pmpro_price = array();
				$membership_ids = get_post_meta($course->ID,'vibe_pmpro_membership',true);
				if(isset($membership_ids) && is_array($membership_ids) && count($membership_ids)){
				//$membership_id = min($membership_ids);
				$levels=pmpro_getAllLevels();
					foreach($levels as $level){
						if(in_array($level->id,$membership_ids)){
							$link = get_option('pmpro_levels_page_id');
							$link = get_permalink($link).'#'.$level->id;
							$pmpro_price = array(	
											'type' => 'inapp',
											'source' => 'pmpro_membership',
											'value' =>  '',
											'html'=> $level->name,
											'link'=> $link,
											'id'=>$level->id,
											'extras'=>array(),
										);
							
							array_push($price_html,$pmpro_price);
						}
					}
			    }
			}

			if(function_exists('mycred')){
				$mycred_price  = array();
				$points=get_post_meta($course->ID,'vibe_mycred_points',true);
				if(isset($points) && is_numeric($points)){
					$mycred = mycred();
					
					$subscription = get_post_meta($course->ID,'vibe_mycred_subscription',true);
					if(isset($subscription) && $subscription && $subscription !='H'){
						$duration = get_post_meta($course->ID,'vibe_mycred_duration',true);
						$duration_parameter = get_post_meta($course->ID,'vibe_mycred_duration_parameter',true);
						$duration = $duration*$duration_parameter;

					}
						$mycred_price = array(	
											'type' => 'post',
											'source' => 'mycred',
											'value' =>  $points,
											'html'=> $points,
											'link'=> $link,
											'extras'=>array(),
										);
					if(function_exists('tofriendlytime')){
						$points_html .= ' <span class="subs"> '.$mycred->format_creds($points).' '.__('per','vibe').' '.tofriendlytime($duration).'</span>';
						$mycred_price['html']  = $points_html;
						
					}
					array_push($price_html,$mycred_price);
				}
			}

			if(empty($price_html)){

				if($version > 1){
					$coming_soon = get_post_meta($course->ID,'vibe_coming_soon',true);
					if(!empty($coming_soon) && function_exists('vibe_validate') && vibe_validate($coming_soon)){
						array_push($price_html,
							array(	
								'type' => 'na',
								'source' => 'na',
								'value' =>  '',
								'html'=> __('Coming Soon','vibe'),
								'extras'=>array(),
							)
						);
					}else{
						array_push($price_html,
							array(	
								'type' => 'na',
								'source' => 'na',
								'value' =>  '',
								'html'=> __('Private','vibe'),
								'extras'=>array(),
							)
						);
					}
				}
			}

			

			//currently bailing out multiple pricing if product is not variable 
			if(!empty($single_price)){
				return $single_price;
			}
			return $price_html;

		}

		public function get_course_start_date($course){

			$start_date = bp_course_get_start_date($course->ID);
			return strtotime($start_date);
		}

		public function is_online($course){
			$check = get_post_meta( $course->ID, 'vibe_course_offline', true );
			if(!empty($check) && $check == 'S'){
				return true;
			}
			return false;
		}


		public function get_taxonomy_terms($course,$taxonomy = 'course-cat'){

			$args = array("fields" => "all");
			$course_terms = wp_get_post_terms($course->ID,$taxonomy);
			$terms = array();

			foreach($course_terms as $term){
				
				if($taxonomy == 'course-cat'){
					$thumbnail_id = get_term_meta( $term->term_id, 'course_cat_thumbnail_id', true );	
				}else{
					$thumbnail_id = false;
				}
				
                if ( $thumbnail_id ) {
                    $image = wp_get_attachment_image_src( $thumbnail_id,'medium' );
                    if(!empty($image) && !is_wp_error($image)){
                    	$image=$image[0];	
                    }
                }

                if(empty($image)){
                    $image = vibe_get_option('default_avatar');
                    if(empty($image)){
                        $image = VIBE_URL.'/assets/images/avatar.jpg';
                    }
                }
				$terms[] = array(
					'id'   => $term->term_id,
					'name' => $term->name,
					'slug' => $term->slug,
					'image'=> $image
				);	
			}
			
			return $terms;
		}

		public function get_purchase_link($course){
			if(function_exists('WC')){
				$product_id = get_post_meta($course->ID,'vibe_product',true);
				$courses = get_post_meta($product_id,'vibe_courses',true);
				if(is_array($courses) && in_array($course->ID,$courses)){
					return get_permalink($product_id).'?redirect';
				}else if($courses == $course->ID){
					return get_permalink($product_id).'?redirect';
				}
			}
			return false;
		}

		public function get_curriculum($course){

			$curriculum = bp_course_get_curriculum($course->ID);
			if(empty($curriculum))
				return false;

			$curriculum_arr = array();
			foreach($curriculum as $key => $item){
				if(is_numeric($item)){
					if(bp_course_get_post_type($item) == 'unit'){
						$curriculum_arr[] = apply_filters('bp_course_api_course_curriculum_unit',array(
							'key'		=> $key,
							'id'		=> $item,
							'type'		=> 'unit',
							'title'		=> get_the_title($item),
							'duration'	=> bp_course_get_unit_duration($item),
							'meta'		=> array()
						));
					}else if(bp_course_get_post_type($item) == 'quiz'){
						$curriculum_arr[] = apply_filters('bp_course_api_course_curriculum_quiz',array(
							'key'		=> $key,
							'id'		=> $item,
							'type'		=> 'quiz',
							'title'		=> get_the_title($item),
							'duration'	=> bp_course_get_quiz_duration($item),
							'meta'		=> array(),
						));
					}

				}else{
					$curriculum_arr[] = apply_filters('bp_course_api_course_curriculum_section',array(
						'key'		=> $key,
						'id'		=> 0,
						'type'		=> 'section',
						'title'		=> $item,
						'duration'	=> 0,
						'meta'		=> array()
					));
				}
			}	

			return $curriculum_arr;
		}

		public function get_reviews($course){
			$reviews = array();
			$args = apply_filters('bp_course_api_course_reviews',array(
				'post_id' 	=> $course->ID,
				'status' => 'approve',
				'orderby'	=> 'comment_date',
				'order'		=> 'DESC',
				'number'	=> 5,
				));
			$comments = get_comments($args);
			if(!empty($comments)){
	            foreach($comments as $comment){
	            	$title =  get_comment_meta( $comment->comment_ID, 'review_title',true);
	            	$rating = get_comment_meta( $comment->comment_ID, 'review_rating',true);	
	            	$review = array(
	            		'id' 		=> $comment->comment_ID,
	            		'title'		=> $title,
	            		'content'	=> $comment->comment_content,
	            		'rating'	=> $rating,
	            		'member'	=> $this->get_member($comment->user_id)
		        	);
		        	array_push($reviews, $review);
	            }
	            
	        }
			return $reviews;
		}

		public function get_course_instructors($course){
			$authors = array();
			$course_authors=array($course->post_author);
			$course_authors = apply_filters('wplms_course_instructors',$course_authors,$course->ID);

			if(!empty($course_authors)){

				
				
				if(function_exists('vibe_get_option')){
					$field = vibe_get_option('instructor_field');		
					$biofield = vibe_get_option('instructor_about');	
				}
				if(empty($biofield)){$biofield = 'Bio';}
				if(empty($field)){$field = 'Speciality';}

	            foreach($course_authors as $author_id){
	            	
	            	$author = array(
	            		'id'     => $author_id, 
						'name'   => bp_core_get_user_displayname($author_id),
						'avatar' => bp_course_get_instructor_avatar_url($author_id),
						'sub'    => (bp_is_active('xprofile')?bp_get_profile_field_data('field='.$field.'&user_id='.$author_id):''),
						'average_rating' => vibe_get_instructor_average_rating($author_id),
						'student_count'=> vibe_get_instructor_student_count($author_id),
						'course_count'=>bp_course_get_instructor_course_count_for_user($author_id),
						'bio'=> (bp_is_active('xprofile')?bp_get_profile_field_data('field='.$biofield.'&user_id='.$author_id):''),
		        	);
		        	array_push($authors, $author);
	            }
	            
	        }
			return $authors;
		}
		/**
		 * Check if the user is logged in and that user can earn courses.
		 *
		 * @since 3.0
		 *
		 * @return array
		 */
		function get_featured_permissions_check(){
			return true;
		}

		/**
		 * Get the plugin schema, conforming to JSON Schema.
		 *
		 * @since 3.0
		 *
		 * @return array
		 */
		function get_featured_courses($request){

			// Prepare the element data
			$posts_data = array();
			
			$defaults = array(
				'post_type'  	=> 'course',
				'post_status'	=> 'publish',
				'orderby' 		=> 'menu_order',
				'order'			=> 'DESC',
				'per_page'		=>	(empty($request['per_view'])?5:$request['per_view']),
				'paged'			=>	0,
				'meta_query'	=> array(
					'relation'	=> 'AND',
					array(
						'key'	  =>'featured',
						'value'	  => 1,
						'compare' => '='
						)
					)
			);


			$args = array();
			//Enter REQUEST IN ARGS FOR FILTERS
			
			$args = wp_parse_args($args,$defaults);

			if ( bp_course_has_items( $args ) ):
				while ( bp_course_has_items() ) : bp_course_the_item();
					global $post;
					$posts[]=$this->prepare_item_for_response($post,$request);
				endwhile;	
			endif;
			
			/**
			 * Filter the response.
			 *
			 * @since 3.0.0
			 *
			 * @param array $element_data
			 * @param WP_REST_Request $request
			 */
			$posts_data = apply_filters( 'bp_course_api_get_featured_courses', $posts, $request );

			return new WP_REST_Response( $posts_data, 200 );

		}

		/**
		 * Check if the user is logged in and that user can earn courses.
		 *
		 * @since 3.0
		 *
		 * @return array
		 */
		function get_popular_permissions_check(){
			return true;
		}

		/**
		 * Get the plugin schema, conforming to JSON Schema.
		 *
		 * @since 3.0
		 *
		 * @return array
		 */
		function get_popular_courses($request){


			// Prepare the element data
			$posts_data = array();
			
			$defaults = array(
				'post_type'  	=> 'course',
				'post_status'	=> 'publish',
				'meta_key'		=> 'vibe_students',
				'orderby' 		=> 'meta_value_num',
				'order'			=> 'DESC',
				'per_page'		=>	(empty($request['per_view'])?5:$request['per_view']),
				'paged'			=>	0,
			);


			$args = array();
			//Enter REQUEST IN ARGS FOR FILTERS
			
			$args = wp_parse_args($args,$defaults);

			if ( bp_course_has_items( $args ) ):
				while ( bp_course_has_items() ) : bp_course_the_item();
					global $post;
					$posts[]=$this->prepare_item_for_response($post,$request);
				endwhile;	
			endif;
			
			/**
			 * Filter the response.
			 *
			 * @since 3.0.0
			 *
			 * @param array $element_data
			 * @param WP_REST_Request $request
			 */
			$posts_data = apply_filters( 'bp_course_api_get_featured_courses', $posts, $request );

			return new WP_REST_Response( $posts_data, 200 );

		}

		/**
		 * Check if the user is logged in and that user can earn courses.
		 *
		 * @since 3.0
		 *
		 * @return array
		 */
		function get_filtered_courses_permissions_check(){
			return true;
		}

		/**
		 * Get the plugin schema, conforming to JSON Schema.
		 *
		 * @since 3.0
		 *
		 * @return array
		 */
		function get_filtered_courses($request){

			$filters = urldecode($request['filter']);
			$filters = json_decode($filters);
			
			$filters = (Array)$filters;
			$defaults = array(
				'post_type'  	=> 'course',
				'per_page'=>	(empty($request['per_view'])?5:$request['per_view']),
				's'				=> (isset($filters['search'])?$filters['search']:''),
				'paged'			=>	(isset($filters['paged'])?$filters['paged']:0),
			);

			if(isset($filters['sort'])){
				$filter = $filters['sort'];
				switch($filter){
					case 'popular':
						$args['orderby'] = 'meta_value_num';
						$args['meta_key'] = 'vibe_students';
					break;
					case 'newest':
						$args['orderby'] = 'date';
					break;
					case 'rated':
						$args['orderby'] = 'meta_value_num';
						$args['meta_key'] = 'average_rating';
					break;
					case 'alphabetical':
						$args['orderby'] = 'title';
						$args['order'] = 'ASC';
					break;
					case 'start_date':
						$args['orderby'] = 'meta_value';
						$args['meta_key'] = 'vibe_start_date';
						$args['meta_type'] = 'DATE';
						$args['order'] = 'ASC';
						if(empty($order['meta_query'])){
							$args['meta_query']=array(array(
							'key' => 'vibe_start_date',
							'value' => current_time('mysql'),
							'compare'=>'>='  
							));
						}
					break;
					default:
						$args['orderby'] = '';
					break;
				}
			}

			if(isset($filters['filters'])){

				$course_categories=array();
				$course_levels=array();
				$course_location=array();
				$type=array();

				$args['tax_query']=array('relation'=>'AND');
				if(empty($args['meta_query'])){
					$args['meta_query']=array();
				}
				
				if(is_array($filters['filters'])){
					foreach($filters['filters'] as $filter){
						$filter = (Array)$filter;
						switch($filter['type']){
							case 'taxonomy':
								
								if(!empty($filter['values'])){
									$args['tax_query'][]=array(
										'taxonomy' => $filter['taxonomy'],
										'terms'    => $filter['values'],
										'field'    => 'term_id',
									);
								}
							break;
							case 'free':
								$args['meta_query']['relation'] = 'AND';
								$args['meta_query'][]=array(
									'key'		=> 'vibe_course_free',
									'value' 	=> ((!empty($filter['values']) && $filter['values']  == 'Free')?'S':'H'),
									'compare'	=> '='
								);
							break;
							case 'offline':
								$args['meta_query']['relation'] = 'AND';
								$args['meta_query'][]=array(
									'key' 		=> 'vibe_course_offline',
									'value' 	=> ($filter['values']?'S':'H'),
									'compare'	=> '='
								);
							break;
							case 'instructors':
								$args['author__in']=$filter['values'];
							break;
							case 'level':
								$course_levels[]=$extra->value;
							break;
							case 'location':
								$course_location[]=$extra->value;
							break;
							case 'start_date':
								$args['meta_query']['relation'] = 'AND';
								$args['meta_query'][]=array(
									'key' 		=> 'vibe_start_date',
									'value' 	=> $filter['values'],
									'compare'	=> '>='  
								);
							break;
							case 'end_date':
								$args['meta_query']['relation'] = 'AND';
								$args['meta_query'][]=array(
									'key' 		=> 'vibe_start_date',
									'value' 	=> $filter['values'],
									'compare'	=> '<='
								);
							break;
						}
					}
				}
			}
			
			$args = wp_parse_args($args,$defaults);
			if(isset($args['search'])){
				$args['search_terms'] = $args['search'];	
				unset($args['search']);
			}
			
			if ( bp_course_has_items( $args ) ):
				while ( bp_course_has_items() ) : bp_course_the_item();
					global $post;
					
					$posts[]=$this->prepare_item_for_response($post,$request);
				endwhile;	
			endif;
			
			/**
			 * Filter the response.
			 *
			 * @since 3.0.0
			 *
			 * @param array $element_data
			 * @param WP_REST_Request $request
			 */
			$posts_data = apply_filters( 'bp_course_api_get_featured_courses', $posts, $request );

			return new WP_REST_Response( $posts_data, 200 );

		}

		
		/**
		 * Check if the user is logged in and that user can earn courses.
		 *
		 * @since 3.0
		 *
		 * @return array
		 */
		function get_instructors_permissions_check(){
			return true;
		}

		/**
		 * Get the plugin schema, conforming to JSON Schema.
		 *
		 * @since 3.0
		 *
		 * @return array
		 */
		function get_instructors($request){

			$no=999;
			$args = apply_filters('wplms_allinstructors',array(
                'role' => 'instructor', // instructor
    			'number' => $no, 
                'orderby' => 'post_count', 
                'order' => 'DESC' 
    		));
			$user_query = new WP_User_Query( $args );
			$args = apply_filters('wplms_alladmins',array(
                'role' => 'administrator', // instructor
                'number' => $no, 
                'orderby' => 'post_count', 
                'order' => 'DESC' 
            ));

			$flag = apply_filters('wplms_show_admin_in_instructors',1);
			if(isset($flag) && $flag)
			    $admin_query = new WP_User_Query( $args );

			$instructors=array();
			if ( isset($admin_query) && !empty( $admin_query->results ) ) {
			    foreach ( $admin_query->results as $user ) {
			        $instructors[$user->ID]=$user->user_email;
			    }
			}

			if ( !empty( $user_query->results ) ) {
			    foreach ( $user_query->results as $user ) {
			        $instructors[$user->ID]=$user->user_email;
			    }
			}

			$sub=$bio='';
			if(bp_is_active('xprofile')){
				$ifield2 = vibe_get_option('instructor_about');
				$ifield = vibe_get_option('instructor_field');
			}
			if(!empty($instructors)){
				foreach($instructors as $instructor_id => $instructor_email){
					$avatar = bp_core_fetch_avatar( array( 'item_id' => $instructor_id,'type'=>'full', 'html' => false ) );
						
					if(!isset($ifield) || $ifield =='')$ifield='Speciality';
	                $sub= bp_get_profile_field_data( 'field='.$ifield.'&user_id=' .$instructor_id ); 

	                
					if(!isset($ifield2) || $ifield2 =='')$ifield2='Bio';
					$bio = bp_get_profile_field_data( 'field='.$ifield2.'&user_id=' .$instructor_id ); 
					
					if(empty($avatar)){
						$avatar = vibe_get_option('default_avatar');
						if(empty($avatar)){
							$avatar = VIBE_URL.'/assets/images/avatar.jpg';
						}
					}
					$all_instructors[] = array(
									'id'=> $instructor_id,
									'name'=> bp_core_get_user_displayname( $instructor_id ),
									'sub'=> $sub,
									'email'=>$instructor_email,
									'avatar'=> $avatar,
									'average_rating' => vibe_get_instructor_average_rating($instructor_id),
									'student_count'=> vibe_get_instructor_student_count($instructor_id),
									'course_count'=>bp_course_get_instructor_course_count_for_user($instructor_id),
								);
				}
			}
			
			$instructors_data = apply_filters( 'bp_course_api_get_all_instructors', $all_instructors, $request );

			return new WP_REST_Response( $instructors_data, 200 );
		}
		/**
		 * Get the plugin schema, conforming to JSON Schema.
		 *
		 * @since 3.0
		 *
		 * @return array
		 */
		function get_instructor_permissions_check(){
			return true;
		}

		/**
		 * Get the plugin schema, conforming to JSON Schema.
		 *
		 * @since 3.0
		 *
		 * @return array
		 */
		function get_instructor($request){

			$instructor_id = $request['instructor_id'];
			$user_id =$instructor_id; 
			$instructor = array();

			$bio = '';$social_fields=$about_fields=array();
			if(bp_is_active('xprofile')){
                
                $ifield = vibe_get_option('instructor_about');
				if(!isset($ifield) || $ifield =='')$ifield='Bio';
				$bio = bp_get_profile_field_data( 'field='.$ifield.'&user_id=' .$instructor_id ); 

				$instructor_field_group = vibe_get_option('instructor_field_group');
				$social_field_group = vibe_get_option('social_field_group');

				$skip = 0;
				if(empty($instructor_field_group) && empty($social_field_group)){
					$skip = 1;
				}

				if(function_exists('bp_xprofile_get_groups') && !$skip){
					$groups = bp_xprofile_get_groups( array(
						'fetch_fields' => true,
					));

					if(!empty($groups)){
						foreach($groups as $group){
							$field_group = array();
							$field_group['id'] = $group->id;
							$field_group['name'] = $group->name;
							
							if($group->name == $instructor_field_group){
								if ( !empty( $group->fields ) ) {

									foreach($group->fields as $field){

										$f = array(
											'id' => $field->id,
											'type' => $field->type,
											'name' => $field->name,
											'value' => bp_get_profile_field_data(array('field'=>$field->id,'user_id'=>$user_id)),
										);

										if($field->field_order){
											$about_fields[$field->field_order] = $f;
										}else{
											$about_fields[] = $f;
										}
									}
								}
							}

							if($group->name == $social_field_group){
								if ( !empty( $group->fields ) ) {

									foreach($group->fields as $field){
										$url = false;
										$value  = bp_get_profile_field_data(array('field'=>$field->id,'user_id'=>$user_id));
										preg_match('/href="(.*?)"/', $value, $match);
										if(!empty($match) && !empty($match[1])){
											$url = $match[1];
										}
										$f = array(
											'id' => $field->id,
											'type' => $field->type,
											'name' => $field->name,
											'value' => $url,
										);

										if($field->field_order){
											$social_fields[$field->field_order] = $f;
										}else{
											$social_fields[] = $f;
										}
									}
								}
							}
							
						}
					}
				}
			}

			$courses = array();
			$query_args = array(
				'post_type'=>'course',
				'posts_per_page'=>5
			);
			if ( function_exists('get_coauthors')) {
				$author_names = array();
				$instructor_name = get_the_author_meta('user_nicename',$instructor_id);
				$author_names[] = $instructor_name;

				$query_args['tax_query']= array(
					'relation' => 'AND',
					array(
						'taxonomy'=>'author',
						'field'=>'name',
						'terms' => $author_names,
					)
				);
			}else{
				$query_args['author__in']=$author__in;
			}

			if ( bp_course_has_items( $query_args ) ):
				while ( bp_course_has_items() ) : bp_course_the_item();
					global $post;
					$courses[]=$this->prepare_item_for_response($post,$request);
				endwhile;	
			endif;


			$instructor = array(
				'bio'		=> $bio,
				'social'	=> $social_fields,
				'about'		=> $about_fields,
				'courses'	=> $courses,
			);
			$instructor_data = apply_filters( 'bp_course_api_get_all_instructors', $instructor, $request );

			return new WP_REST_Response( $instructor_data, 200 );
		}

		/**
		 * Get a collection of Course categories
		 *
		 * @since 3.0.0
		 * 
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|WP_REST_Response
		 */
		public function get_course_terms( $request ) {

			$taxonomy = $request['taxonomy'];
			if(empty($taxonomy)){
				return;
			}
			// Prepare the element data
			$posts_data = array();
			
			$defaults = array(
				'taxonomy'	=> $taxonomy,
				'number'	=> 99,
				'hide_empty'=>false,
			);


			if($taxonomy == 'course-cat'){
				$defaults['meta_key'] = 'course_cat_order';
				$defaults['orderby'] = 'course_cat_order';
				$defaults['order'] = 'DESC';
			}

			$args = array();
			//Enter REQUEST IN ARGS FOR FILTERS
			
			$args = wp_parse_args($args,$defaults);

			$categories = array();
			$terms = get_terms($args);
			

            if(!empty($terms) && !is_wp_error($terms)){

                foreach($terms as $term){ 
        			$term->image = $this->get_term_image($term);
                    $categories[]=$term;
                }
            }
			
			/**
			 * Filter the response.
			 *
			 * @since 3.0.0
			 *
			 * @param array $element_data
			 * @param WP_REST_Request $request
			 */
			$posts_data = apply_filters( 'bp_course_api_get_course_terms', $categories, $request );

			return new WP_REST_Response( $posts_data, 200 );
		}

		private function get_term_image($term){

			$thumbnail_id = get_term_meta( $term->term_id, 'course_cat_thumbnail_id', true );
            if ( $thumbnail_id ) {
                $image = wp_get_attachment_image_src( $thumbnail_id,'medium' );
                if(!empty($image) && !is_wp_error($image)){
                	$image=$image[0];	
                }
            }

            if(empty($image)){
                $image = vibe_get_option('default_avatar');
                if(empty($image)){
                    $image = VIBE_URL.'/assets/images/avatar.jpg';
                }
            }

            return $image;
		}

		private function get_terms_hierarchical($terms, $output = array(), $parent_id = 0, $level = 0) {
		    
		    foreach ($terms as $term) {
		        if ($parent_id == $term->parent) {
		            $output[] = $term;
		            $output = $this->get_terms_hierarchical($terms, $output, $term->term_id, $level + 1);
		        }
		    }
		    return $output;
		}

		public function get_course_terms_permissions_check(){
			return true;
		}

		/**
		 * Single Course category item
		 *
		 * @since 3.0.0
		 * 
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|WP_REST_Response
		 */
		

		public function get_category($request){

			$id = $request['id'];
			$defaults = array(
				'taxonomy'	=>'course-cat',
				'number'	=> 8,
			);


			$args = array('child_of'=> $id);
			//Enter REQUEST IN ARGS FOR FILTERS
			
			$args = wp_parse_args($args,$defaults);

			$categories = array();

			$fullcategory = array('child'=>array(),'courses'=>array());

			$terms = get_terms($args);
            if(!empty($terms)){
                foreach($terms as $term){ 
                    $thumbnail_id = get_term_meta( $term->term_id, 'course_cat_thumbnail_id', true );
                    if ( $thumbnail_id ) {
                        $image = wp_get_attachment_image_src( $thumbnail_id,'medium' );
                        if(!empty($image) && !is_wp_error($image)){
                        	$image=$image[0];	
                        }
                    } 
                    if(empty($image)){
                        $image = vibe_get_option('default_avatar');
                        if(empty($image)){
                            $image = VIBE_URL.'/assets/images/avatar.jpg';
                        }
                    }

                    if(isset($image))
                    	$term->image = $image;
                    
                    $categories[]=$term;
                }
                $fullcategory['child'] = $categories;
            }

            $courses = array();
			$course_args = array(
				'post_type'  	=> 'course',
				'post_status'	=> 'publish',
				'orderby' 		=> 'alphabetical',
				'order'			=> 'ASC',
				'per_page'		=>	20,
				'paged'			=>	0,
				'tax_query' => array(
					array(
						'taxonomy' => 'course-cat',
						'field'    => 'id',
						'terms'    => $id,
					),
				),
			);


			if ( bp_course_has_items( $course_args ) ):
				while ( bp_course_has_items() ) : bp_course_the_item();
					global $post;
					$courses[]=$this->prepare_item_for_response($post,$request);
				endwhile;	
			endif;
			
			$fullcategory['courses'] = $courses;

			
			/**
			 * Filter the response.
			 *
			 * @since 3.0.0
			 *
			 * @param array $element_data
			 * @param WP_REST_Request $request
			 */
			$fullcategory = apply_filters( 'bp_course_api_get_full_category', $fullcategory, $request );

			return new WP_REST_Response( $fullcategory, 200 );
		}

		public function get_category_permissions_check(){
			return true;
		}

		/**
		 * Get the plugin schema, conforming to JSON Schema.
		 *
		 * @since 0.1.0
		 *
		 * @return array
		 */
		public function get_item_schema() {
			$schema = array(
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => 'course',
				'type'       => 'object',
				'properties' => array(
					'id' => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'A unique alphanumeric ID for the object.', 'vibe' ),
						'readonly'    => true,
						'type'        => 'integer',
					),
					'author' => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'The ID for the creator of the object.', 'vibe' ),
						'type'        => 'integer',
					),
					'link' => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'The permalink to this object on the site.', 'vibe' ),
						'format'      => 'url',
						'type'        => 'string',
					),
					'component' => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'The vibe component the object relates to.', 'vibe' ),
						'type'        => 'string',
						'enum'        => array_keys( bp_core_get_components() ),
					),
					'type' => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'The activity type of the object.', 'vibe' ),
						'type'        => 'string',
						'enum'        => (function_exists('bp_activity_get_types')?array_keys( bp_activity_get_types()):''),
					),
					'title' => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'HTML title of the object.', 'vibe' ),
						'type'        => 'string',
					),
					'content' => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'HTML content of the object.', 'vibe' ),
						'type'        => 'string',
					),
					'date' => array(
						'description' => __( "The date the object was published, in the site's timezone.", 'vibe' ),
						'type'        => 'string',
						'format'      => 'date-time',
						'context'     => array( 'view', 'edit' ),
					),
					'status' => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'Whether the object has been marked as spam or not.', 'vibe' ),
						'type'        => 'string',
						'enum'        => array( 'published', 'spam' ),
					),
					'parent' => array(
						'description'  => __( 'The ID of the parent of the object.', 'vibe' ),
						'type'         => 'integer',
						'context'      => array( 'view', 'edit' ),
					),
				)
			);
			return $schema;
		}

		/*
		GEt Blog
		 */
		
		public function get_blog($request){

			$per_page = ($request['per_page'])?$request['per_page']:5;
			$paged = ($request['page'])?$request['page']:1;

			$args = array(
				'post_type'=>'post',
				'post_status' => 'publish',
				'posts_per_page'=> $per_page,
				'paged' => $paged,
				);
			$the_query = new WP_Query($args);
			$posts = array();


			if($the_query->have_posts()){
				while($the_query->have_posts()){
					$the_query->the_post();
					if(has_post_thumbnail()){
						$thumb = get_the_post_thumbnail_url(get_the_ID(),'large');
					}else{
						$thumb = vibe_get_option('default_course_avatar');
						if(empty($thumb)){
			                $thumb = VIBE_URL.'/assets/images/avatar.jpg';
			            }
					}
					
					$posts[] = array(
						'id'=>get_the_ID(),
						'title'=>get_the_title(),
						'date'=> get_the_date(),
						'excerpt'=>get_the_excerpt(),
						'featured_image'=> $thumb,
						'author'=> $this->get_course_instructor(get_the_author_id()),
						'comments'=> get_comments_number(),
					);
				}
			}
			
			return new WP_REST_Response( $posts, 200 );
		}

		public function get_blogpost($request){

			$post_id = $request['id'];

			$wppost = get_post($post_id);
			if(has_post_thumbnail()){
				$thumb = get_the_post_thumbnail($post_id,'large');
			}else{
				$thumb = vibe_get_option('default_course_avatar');
				if(empty($thumb)){
	                $thumb = VIBE_URL.'/assets/images/avatar.jpg';
	            }
			}
			$post= array(
				'id'=>$wppost->ID,
				'title'=>$wppost->post_title,
				'featured_image'=> $thumb,
				'content'=> apply_filters('the_content',$wppost->post_content),
			);

			return new WP_REST_Response( $post, 200 );
		}

		/*
		Contact message
		 */
		
		function get_contact_message_permissions_check($request){
			$post = json_decode(file_get_contents('php://input'));

			/*$apps = get_option('wplms_apps');
			foreach($apps as $key => $app){
				if($app['app_id'] == $client_id){
					$api_tracker['client_secret'] = $app['app_secret'];
				}
			}*/
			$state = bp_course_get_setting( 'api_security_state', 'api','string' );
			if($state == $post->state){
				return true;
			}


			return false;
		}

		function contact_message($request){
			$post = json_decode(file_get_contents('php://input'));
			
			if (empty($post->contact->email) || !filter_var($post->contact->email, FILTER_VALIDATE_EMAIL)) {
	               $return = array('message'=>__("Unable to send message! Please check email id..","vibe"),'status'=>0);
	        }else{


				$message = $post->contact->message.' <hr> '.$post->contact->name.' ( '.$post->contact->email.' ) ';
				$bpargs = array(
		            'tokens' => array('user.message' => $message),
		        );
		        $tax = bp_get_email_tax_type();
		        $term = 'wplms_contact_form_email';
		        
		        $to = get_option('admin_email'); 

		        if(is_array($to)){
		            foreach($to as $t){
		                $flag = bp_send_email( 'wplms_contact_form_email',$t, $bpargs );   
		            }
		        }else{
		            $flag = bp_send_email( 'wplms_contact_form_email',$to, $bpargs );
		        }

		        if ( $flag ) {
		            $return = array('message'=>__("Message sent!","vibe"),'status'=>1);
		        }else{
		        	$return = array('message'=>__("Unable to send message! Please try again later..","vibe"),'status'=>0);
		        }
		    }

	        return new WP_REST_Response( $return, 200 );
		}
	}
}
