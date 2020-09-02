<?php

defined( 'ABSPATH' ) or die();


if ( ! class_exists( 'Vibe_BP_API_Rest_WooCommerce_Controller' ) ) {
	
	class Vibe_BP_API_Rest_WooCommerce_Controller extends WP_REST_Controller{
		
		public static $instance;
		
		public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new Vibe_BP_API_Rest_WooCommerce_Controller();
	        return self::$instance;
	    }

	    public function __construct( ) {

			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->type= 'wc';
			$this->register_routes();
		}

		function register_routes(){

			register_rest_route( $this->namespace, '/'.$this->type.'/orders/', array(
				'methods'                   =>   'POST',
				'callback'                  =>  array( $this, 'get_orders' ),
				'permission_callback' => array( $this, 'get_user_permissions_check' ),
			) );

			register_rest_route( $this->namespace, '/'.$this->type.'/downloads/', array(
				'methods'                   =>   'POST',
				'callback'                  =>  array( $this, 'get_downloads' ),
				'permission_callback' => array( $this, 'get_user_permissions_check' ),
			) );

			register_rest_route( $this->namespace, '/'.$this->type.'/addresses/', array(
				'methods'                   =>   'POST',
				'callback'                  =>  array( $this, 'get_addresses' ),
				'permission_callback' => array( $this, 'get_user_permissions_check' ),
			) );

			register_rest_route( $this->namespace, '/'.$this->type.'/addresses/update', array(
				'methods'                   =>   'POST',
				'callback'                  =>  array( $this, 'update_address_field' ),
				'permission_callback' => array( $this, 'get_user_permissions_check' ),
			) );
			

		}

		function get_user_permissions_check($request){
	    	
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


	    function get_orders($request){

	    	$args = json_decode($request->get_body(),true);

	    	if(empty($this->user->id)){
	    		return new WP_REST_Response(array('stats'=>0,'message'=>__('Invalid user ID','vibebp')), 200); 
	    	}


	    	//( $fields = null, $filter = array(), $status = null, $page = 1 ) {

			// if ( ! empty( $status ) ) {
			// 	$filter['status'] = $status;
			// }

			
			// order_date
			// order_status
			$query_args = array(
				'fields'      => 'ids',
				'post_type'   => 'shop_order',
				'page'		  => $args['page'],
				'post_status' => array_keys( wc_get_order_statuses() ),
			);
			if($args['order_date']){
				$query_args['post_date'] = date('Y-m-d',intval($args['order_date']/1000));
			}

			// add status argument
			if ( ! empty( $args['status'] ) ) {
				$args['status'] = str_replace('wc-','',$args['status']);
				$statuses                  = 'wc-' . str_replace( ',', ',wc-', $args['status'] );
				$statuses                  = explode( ',', $statuses );
				$query_args['post_status'] = $statuses;
				unset( $args['status'] );
			}

			if ( ! empty( $args['type'] ) &&  $args['type'] == 'mine') {
				$query_args['meta_query'] = array(
					array(
						'key'     => '_customer_user',
						'value'   => $this->user->id,
						'compare' => '=',
					),
				);
			}
			//print_r($args['order_date']/1000);
			
			//print_r($query_args);
			$query_args = $this->merge_query_args( $query_args, $args );
			$query =  new WP_Query( $query_args );
			$orders = array();
			if($query->have_posts()){
				foreach ( $query->posts as $order_id ) {
					$orders[] = array(current( $this->get_order( $order_id, $fields, $filter ) ));	
				}
			}
			

	    	return new WP_REST_Response(array('status'=>1,'orders'=>$orders,'total_spent'=>wc_price( wc_get_customer_total_spent( $this->user->id ) ),'count'=>wc_get_customer_order_count( $this->user->id )), 200); 

	    }

	    
	    function get_downloads($request){


	    	$downloads  = array();
			$_downloads = wc_get_customer_available_downloads( $this->user->id );

			if(!empty($_downloads)){
				foreach ( $_downloads as $key => $download ) {
					$downloads[] = array(
						'download_url'        => $download['download_url'],
						'download_id'         => $download['download_id'],
						'product_id'          => $download['product_id'],
						'product_name'	      => $download['product_name'],
						'download_name'       => $download['download_name'],
						'order_id'            => $download['order_id'],
						'order_key'           => $download['order_key'],
						'downloads_remaining' => $download['downloads_remaining'],
						'access_expires'      => $download['access_expires'] ? wc_format_datetime( $download['access_expires'] ) : null,
						'file'                => $download['file'],
					);
				}
			}

			return new WP_REST_Response(array('status'=>1, 'downloads' => apply_filters( 'woocommerce_api_customer_downloads_response', $downloads, $this->user->id)),200);
	    }

	    function get_addresses($request){

	    	
			$customer      = new WC_Customer( $this->user->id );

			$addresses = array(
				'billing_address'  => array(
					'first_name'=>array('value'=>$customer->get_billing_first_name(),'label'=>__('First Name','vibebp')),
					'last_name'  => array('value'=>$customer->get_billing_last_name(),'label'=>__('Last Name','vibebp')),
					'company'    => array('value'=>$customer->get_billing_company(),'label'=>__('Company','vibebp')),
					'address_1'  => array('value'=>$customer->get_billing_address_1(),'label'=>__('Address','vibebp')),
					'address_2'  => array('value'=>$customer->get_billing_address_2(),'label'=>__('Address','vibebp')),
					'city'       => array('value'=>$customer->get_billing_city(),'label'=>__('City','vibebp')),
					'state'      => array('value'=>$customer->get_billing_state(),'label'=>__('State','vibebp')),
					'postcode'   => array('value'=>$customer->get_billing_postcode(),'label'=>__('Postcode','vibebp')),
					'country'    => array('value'=>$customer->get_billing_country(),'label'=>__('Country','vibebp')),
					'email'      => array('value'=>$customer->get_billing_email(),'label'=>__('Email','vibebp')),
					'phone'      => array('value'=>$customer->get_billing_phone(),'label'=>__('Phone','vibebp')),
				),
				'shipping_address' => array(
					'first_name' => array('value'=>$customer->get_shipping_first_name(),'label'=>__('First Name','vibebp')),
					'last_name'  => array('value'=>$customer->get_shipping_last_name(),'label'=>__('Last Name','vibebp')),
					'company'    => array('value'=>$customer->get_shipping_company(),'label'=>__('Company','vibebp')),
					'address_1'  => array('value'=>$customer->get_shipping_address_1(),'label'=>__('Address','vibebp')),
					'address_2'  => array('value'=>$customer->get_shipping_address_2(),'label'=>__('Address','vibebp')),
					'city'       => array('value'=>$customer->get_shipping_city(),'label'=>__('City','vibebp')),
					'state'      => array('value'=>$customer->get_shipping_state(),'label'=>__('State','vibebp')),
					'postcode'   => array('value'=>$customer->get_shipping_postcode(),'label'=>__('Postcode','vibebp')),
					'country'    => array('value'=>$customer->get_shipping_country(),'label'=>__('Country','vibebp')),
				),
	    	);

	    	return new WP_REST_Response(array('status'=>1, 'addresses'=>$addresses),200);
	    }

	    function update_address_field($request){

	    	$body = json_decode($request->get_body(),true);

	    	if($body['address_type'] === 'billing_address'){
	    		update_post_meta($this->user->id,'billing_'.$body['field'],$body['value']);
	    	}

	    	if($body['address_type'] === 'shipping_address'){
	    		update_post_meta($this->user->id,'shipping_'.$body['field'],$body['value']);
	    	}

	    	return new WP_REST_Response(array('status'=>1, 'message'=>__('Updated','vibebp')),200);
	    }

	    
/***===========================================================================================**/
	    //WooCommerce API Functions V3

	    public function get_order( $id, $fields = null, $filter = array() ) {

			// Get the decimal precession.
			$dp     = ( isset( $filter['dp'] ) ? intval( $filter['dp'] ) : 2 );
			
			$expand = array();

			if ( ! empty( $filter['expand'] ) ) {
				$expand = explode( ',', $filter['expand'] );
			}

			$order  = wc_get_order( $id );
			$order_data = array(
				'id'                        => $order->get_id(),
				'order_number'              => $order->get_order_number(),
				'order_key'                 => $order->get_order_key(),
				'created_at'                => esc_html( wc_format_datetime( $order->get_date_created(), get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ) ),
				'updated_at'                => esc_html( wc_format_datetime( $order->get_date_modified(), get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ) ),
				'completed_at'              => esc_html( wc_format_datetime( $order->get_date_completed(), get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ) ),
				'status'                    => $order->get_status(),
				'currency'                  => $order->get_currency(),
				'total'                     => wc_format_decimal( $order->get_total(), $dp ),
				'subtotal'                  => wc_format_decimal( $order->get_subtotal(), $dp ),
				'total_line_items_quantity' => $order->get_item_count(),
				'total_tax'                 => wc_format_decimal( $order->get_total_tax(), $dp ),
				'total_shipping'            => wc_format_decimal( $order->get_shipping_total(), $dp ),
				'cart_tax'                  => wc_format_decimal( $order->get_cart_tax(), $dp ),
				'shipping_tax'              => wc_format_decimal( $order->get_shipping_tax(), $dp ),
				'total_discount'            => wc_format_decimal( $order->get_total_discount(), $dp ),
				'shipping_methods'          => $order->get_shipping_method(),
				'payment_details' => array(
					'method_id'    => $order->get_payment_method(),
					'method_title' => $order->get_payment_method_title(),
					'paid'         => ! is_null( $order->get_date_paid() ),
				),
				'billing_address' => array(
					'first_name' => $order->get_billing_first_name(),
					'last_name'  => $order->get_billing_last_name(),
					'company'    => $order->get_billing_company(),
					'address_1'  => $order->get_billing_address_1(),
					'address_2'  => $order->get_billing_address_2(),
					'city'       => $order->get_billing_city(),
					'state'      => $order->get_billing_state(),
					'postcode'   => $order->get_billing_postcode(),
					'country'    => $order->get_billing_country(),
					'email'      => $order->get_billing_email(),
					'phone'      => $order->get_billing_phone(),
				),
				'shipping_address' => array(
					'first_name' => $order->get_shipping_first_name(),
					'last_name'  => $order->get_shipping_last_name(),
					'company'    => $order->get_shipping_company(),
					'address_1'  => $order->get_shipping_address_1(),
					'address_2'  => $order->get_shipping_address_2(),
					'city'       => $order->get_shipping_city(),
					'state'      => $order->get_shipping_state(),
					'postcode'   => $order->get_shipping_postcode(),
					'country'    => $order->get_shipping_country(),
				),
				'note'                      => $order->get_customer_note(),
				'customer_ip'               => $order->get_customer_ip_address(),
				'customer_user_agent'       => $order->get_customer_user_agent(),
				'customer_id'               => $order->get_user_id(),
				'view_order_url'            => $order->get_view_order_url(),
				'line_items'                => array(),
				'shipping_lines'            => array(),
				'tax_lines'                 => array(),
				'fee_lines'                 => array(),
				'coupon_lines'              => array(),
			);

			// Add line items.
			foreach ( $order->get_items() as $item_id => $item ) {
				$product    = $item->get_product();
				$hideprefix = ( isset( $filter['all_item_meta'] ) && 'true' === $filter['all_item_meta'] ) ? null : '_';
				$item_meta  = $item->get_formatted_meta_data( $hideprefix );

				foreach ( $item_meta as $key => $values ) {
					$item_meta[ $key ]->label = $values->display_key;
					unset( $item_meta[ $key ]->display_key );
					unset( $item_meta[ $key ]->display_value );
				}

				$line_item = array(
					'id'           => $item_id,
					'subtotal'     => wc_format_decimal( $order->get_line_subtotal( $item, false, false ), $dp ),
					'subtotal_tax' => wc_format_decimal( $item->get_subtotal_tax(), $dp ),
					'total'        => wc_format_decimal( $order->get_line_total( $item, false, false ), $dp ),
					'total_tax'    => wc_format_decimal( $item->get_total_tax(), $dp ),
					'price'        => wc_format_decimal( $order->get_item_total( $item, false, false ), $dp ),
					'quantity'     => $item->get_quantity(),
					'tax_class'    => $item->get_tax_class(),
					'name'         => $item->get_name(),
					'product_id'   => $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id(),
					'sku'          => is_object( $product ) ? $product->get_sku() : null,
					'meta'         => array_values( $item_meta ),
				);

				if ( in_array( 'products', $expand ) && is_object( $product ) ) {
					$_product_data = WC()->api->WC_API_Products->get_product( $product->get_id() );

					if ( isset( $_product_data['product'] ) ) {
						$line_item['product_data'] = $_product_data['product'];
					}
				}

				$order_data['line_items'][] = $line_item;
			}

			// Add shipping.
			foreach ( $order->get_shipping_methods() as $shipping_item_id => $shipping_item ) {
				$order_data['shipping_lines'][] = array(
					'id'           => $shipping_item_id,
					'method_id'    => $shipping_item->get_method_id(),
					'method_title' => $shipping_item->get_name(),
					'total'        => wc_format_decimal( $shipping_item->get_total(), $dp ),
				);
			}

			// Add taxes.
			foreach ( $order->get_tax_totals() as $tax_code => $tax ) {
				$tax_line = array(
					'id'       => $tax->id,
					'rate_id'  => $tax->rate_id,
					'code'     => $tax_code,
					'title'    => $tax->label,
					'total'    => wc_format_decimal( $tax->amount, $dp ),
					'compound' => (bool) $tax->is_compound,
				);

				if ( in_array( 'taxes', $expand ) ) {
					$_rate_data = WC()->api->WC_API_Taxes->get_tax( $tax->rate_id );

					if ( isset( $_rate_data['tax'] ) ) {
						$tax_line['rate_data'] = $_rate_data['tax'];
					}
				}

				$order_data['tax_lines'][] = $tax_line;
			}

			// Add fees.
			foreach ( $order->get_fees() as $fee_item_id => $fee_item ) {
				$order_data['fee_lines'][] = array(
					'id'        => $fee_item_id,
					'title'     => $fee_item->get_name(),
					'tax_class' => $fee_item->get_tax_class(),
					'total'     => wc_format_decimal( $order->get_line_total( $fee_item ), $dp ),
					'total_tax' => wc_format_decimal( $order->get_line_tax( $fee_item ), $dp ),
				);
			}

			// Add coupons.
			foreach ( $order->get_items( 'coupon' ) as $coupon_item_id => $coupon_item ) {
				$coupon_line = array(
					'id'     => $coupon_item_id,
					'code'   => $coupon_item->get_code(),
					'amount' => wc_format_decimal( $coupon_item->get_discount(), $dp ),
				);

				if ( in_array( 'coupons', $expand ) ) {
					$_coupon_data = WC()->api->WC_API_Coupons->get_coupon_by_code( $coupon_item->get_code() );

					if ( ! is_wp_error( $_coupon_data ) && isset( $_coupon_data['coupon'] ) ) {
						$coupon_line['coupon_data'] = $_coupon_data['coupon'];
					}
				}

				$order_data['coupon_lines'][] = $coupon_line;
			}

			return array( 'order' =>  $order_data );
		}

	    protected function merge_query_args( $base_args, $request_args ) {

			$args = array();

			// date
			if ( ! empty( $request_args['created_at_min'] ) || ! empty( $request_args['created_at_max'] ) || ! empty( $request_args['updated_at_min'] ) || ! empty( $request_args['updated_at_max'] ) ) {

				$args['date_query'] = array();

				// resources created after specified date
				if ( ! empty( $request_args['created_at_min'] ) ) {
					$args['date_query'][] = array( 'column' => 'post_date_gmt', 'after' => $this->server->parse_datetime( $request_args['created_at_min'] ), 'inclusive' => true );
				}

				// resources created before specified date
				if ( ! empty( $request_args['created_at_max'] ) ) {
					$args['date_query'][] = array( 'column' => 'post_date_gmt', 'before' => $this->server->parse_datetime( $request_args['created_at_max'] ), 'inclusive' => true );
				}

				// resources updated after specified date
				if ( ! empty( $request_args['updated_at_min'] ) ) {
					$args['date_query'][] = array( 'column' => 'post_modified_gmt', 'after' => $this->server->parse_datetime( $request_args['updated_at_min'] ), 'inclusive' => true );
				}

				// resources updated before specified date
				if ( ! empty( $request_args['updated_at_max'] ) ) {
					$args['date_query'][] = array( 'column' => 'post_modified_gmt', 'before' => $this->server->parse_datetime( $request_args['updated_at_max'] ), 'inclusive' => true );
				}
			}

			// search
			if ( ! empty( $request_args['q'] ) ) {
				$args['s'] = $request_args['q'];
			}

			// resources per response
			if ( ! empty( $request_args['limit'] ) ) {
				$args['posts_per_page'] = $request_args['limit'];
			}

			// resource offset
			if ( ! empty( $request_args['offset'] ) ) {
				$args['offset'] = $request_args['offset'];
			}

			// order (ASC or DESC, ASC by default)
			if ( ! empty( $request_args['order'] ) ) {
				$args['order'] = $request_args['order'];
			}

			// orderby
			if ( ! empty( $request_args['orderby'] ) ) {
				$args['orderby'] = $request_args['orderby'];

				// allow sorting by meta value
				if ( ! empty( $request_args['orderby_meta_key'] ) ) {
					$args['meta_key'] = $request_args['orderby_meta_key'];
				}
			}

			// allow post status change
			if ( ! empty( $request_args['post_status'] ) ) {
				$args['post_status'] = $request_args['post_status'];
				unset( $request_args['post_status'] );
			}

			// filter by a list of post id
			if ( ! empty( $request_args['in'] ) ) {
				$args['post__in'] = explode( ',', $request_args['in'] );
				unset( $request_args['in'] );
			}

			// exclude by a list of post id
			if ( ! empty( $request_args['not_in'] ) ) {
				$args['post__not_in'] = explode( ',', $request_args['not_in'] );
				unset( $request_args['not_in'] );
			}

			// resource page
			$args['paged'] = ( isset( $request_args['page'] ) ) ? absint( $request_args['page'] ) : 1;

			$args = apply_filters( 'woocommerce_api_query_args', $args, $request_args );

			return array_merge( $base_args, $args );
		}
	}
}


Vibe_BP_API_Rest_WooCommerce_Controller::init();
			