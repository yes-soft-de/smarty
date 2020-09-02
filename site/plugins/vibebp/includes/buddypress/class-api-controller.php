<?php

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'Vibe_BP_API_Rest_Controller' ) ) {
	
	class Vibe_BP_API_Rest_Controller extends WP_REST_Controller {

		/**
		 * The resource type.
		 * 
		 * @since 3.0.0
		 * 
		 * @var mixed|void
		 */
		protected $type;

		protected $id;

		/**
		 * Constructs the REST controller.
		 *
		 * @since 3.0.0
		 *
		 * @param $type
		 */
		public function __construct( $type ) {

			$this->type = $type;
			$this->namespace = Vibe_BP_API_NAMESPACE;
			$this->registered_components = apply_filters('vibe_bp_api_registered_components',
				array(
						'members',
					)
				);
		}

		/**
		 * Check if a given request has access to create items.
		 *
		 * @since 3.0.0
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function create_items_permissions_check( $request ) {

			/**
			 * Filter the response of the get items permission check.
			 *
			 * @since 3.0.0
			 */
			return apply_filters( 'vibe_bp_api_create_items_capability', current_user_can( 'edit_posts' ), $request, $this->type );
		}

		/**
		 * Check if a given request has access to create a specific item.
		 *
		 * @since 3.0.0
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function create_item_permissions_check( $request ) {
			return $this->create_items_permissions_check( $request );
		}

		/**
		 * Check if a given request has access to get items.
		 *
		 * @since 3.0.0
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function get_items_permissions_check( $request ) {

			/**
			 * Filter the response of the get items permission check.
			 *
			 * @since 3.0.0
			 */
			return apply_filters( 'vibe_bp_api_get_items_capability', true, $request, $this->type );
		}

		/**
		 * Check if a given request has access to get a specific item.
		 *
		 * @since 3.0.0
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function get_item_permissions_check( $request ) {
			return $this->get_items_permissions_check( $request );
		}

		/**
		 * Check if a given request has access to update items.
		 *
		 * @since 3.0.0
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function update_items_permissions_check( $request ) {
			return $this->create_items_permissions_check( $request );
		}

		/**
		 * Check if a given request has access to update a specific item.
		 *
		 * @since 3.0.0
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function update_item_permissions_check( $request ) {
			return $this->create_items_permissions_check( $request );
		}

		/**
		 * Check if a given request has access to delete items.
		 *
		 * @since 3.0.0
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function delete_items_permissions_check( $request ) {
			return $this->create_items_permissions_check( $request );
		}
		
		/**
		 * Check if a given request has access to delete a specific item.
		 *
		 * @since 3.0.0
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|bool
		 */
		public function delete_item_permissions_check( $request ) {
			return $this->create_items_permissions_check( $request );
		}


		/**
		 * Returns an error when no post could be found.
		 *
		 * @since 3.0.0
		 *
		 * @return WP_Error
		 */
		protected function no_post_error() {
			return new WP_Error(
				'bp-course-api-no-post',
				_x( 'The post could not be found', 'error message', 'vibe' ),
				array( 'status' => 404 )
			);
		}

		/**
		 * Returns an error when no items could be found.
		 *
		 * @since 3.0.0
		 *
		 * @return WP_Error
		 */
		protected function no_items_error() {
			return new WP_Error(
				'bp-course-api-no-items',
				_x( 'No items could be found', 'error message', 'vibe' ),
				array( 'status' => 404 )
			);
		}
		
		/**
		 * Returns an error when no item could be found.
		 *
		 * @since 3.0.0
		 *
		 * @return WP_Error
		 */
		protected function no_item_error() {
			return new WP_Error(
				'bp-course-api-no-item',
				_x( 'No item could be found', 'error message', 'vibe' ),
				array( 'status' => 404 )
			);
		}
		
		/**
		 * Returns an error when the request is improperly formatted.
		 *
		 * @since 3.0.0
		 *
		 * @return WP_Error
		 */
		protected function invalid_request_error() {
			return new WP_Error(
				'bp-course-api-invalid-request',
				_x( 'The request format is invalid', 'error message', 'vibe' ),
				array( 'status' => 500 )
			);
		}

		/**
		 * Prepare the item for create or update operation.
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return WP_Error|object $prepared_item
		 */
		protected function prepare_item_for_database( $request ) {
			return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be over-ridden in subclass.", 'vibe' ), __METHOD__ ), array( 'status' => 405 ) );
		}

		/**
		 * Prepare the item for the REST response.
		 *
		 * @param mixed $item WordPress representation of the item.
		 * @param WP_REST_Request $request Request object.
		 * @return WP_REST_Response $response
		 */
		public function prepare_item_for_response( $item, $request ) {
			return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be over-ridden in subclass.", 'vibe' ), __METHOD__ ), array( 'status' => 405 ) );
		}

		/**
		 * Prepare a response for inserting into a collection.
		 *
		 * @param WP_REST_Response $response Response object.
		 * @return array Response data, ready for insertion into collection data.
		 */
		public function prepare_response_for_collection( $response ) {
			if ( ! ( $response instanceof WP_REST_Response ) ) {
				return $response;
			}

			$data = (array) $response->get_data();
			$server = rest_get_server();

			if ( method_exists( $server, 'get_compact_response_links' ) ) {
				$links = call_user_func( array( $server, 'get_compact_response_links' ), $response );
			} else {
				$links = call_user_func( array( $server, 'get_response_links' ), $response );
			}

			if ( ! empty( $links ) ) {
				$data['_links'] = $links;
			}

			return $data;
		}

		/**
		 * Filter a response based on the context defined in the schema.
		 *
		 * @param array $data
		 * @param string $context
		 * @return array
		 */
		public function filter_response_by_context( $data, $context ) {

			$schema = $this->get_item_schema();
			foreach ( $data as $key => $value ) {
				if ( empty( $schema['properties'][ $key ] ) || empty( $schema['properties'][ $key ]['context'] ) ) {
					continue;
				}

				if ( ! in_array( $context, $schema['properties'][ $key ]['context'] ) ) {
					unset( $data[ $key ] );
				}

				if ( 'object' === $schema['properties'][ $key ]['type'] && ! empty( $schema['properties'][ $key ]['properties'] ) ) {
					foreach ( $schema['properties'][ $key ]['properties'] as $attribute => $details ) {
						if ( empty( $details['context'] ) ) {
							continue;
						}
						if ( ! in_array( $context, $details['context'] ) ) {
							if ( isset( $data[ $key ][ $attribute ] ) ) {
								unset( $data[ $key ][ $attribute ] );
							}
						}
					}
				}
			}

			return $data;
		}

		/**
		 * Get the item's schema, conforming to JSON Schema.
		 *
		 * @return array
		 */
		public function get_item_schema() {
			return $this->add_additional_fields_schema( array() );
		}

		/**
		 * Get the item's schema for display / public consumption purposes.
		 *
		 * @return array
		 */
		public function get_public_item_schema() {

			$schema = $this->get_item_schema();

			foreach ( $schema['properties'] as &$property ) {
				if ( isset( $property['arg_options'] ) ) {
					unset( $property['arg_options'] );
				}
			}

			return $schema;
		}

		/**
		 * Get the query params for collections.
		 *
		 * @return array
		 */
		public function get_collection_params() {
			return array(
				'context'                => $this->get_context_param(),
				'page'                   => array(
					'description'        => __( 'Current page of the collection.', 'vibe' ),
					'type'               => 'integer',
					'default'            => 1,
					'sanitize_callback'  => 'absint',
					'validate_callback'  => 'rest_validate_request_arg',
					'minimum'            => 1,
				),
				'per_page'               => array(
					'description'        => __( 'Maximum number of items to be returned in result set.', 'vibe' ),
					'type'               => 'integer',
					'default'            => 10,
					'minimum'            => 1,
					'maximum'            => 100,
					'sanitize_callback'  => 'absint',
					'validate_callback'  => 'rest_validate_request_arg',
				),
				'search'                 => array(
					'description'        => __( 'Limit results to those matching a string.', 'vibe' ),
					'type'               => 'string',
					'sanitize_callback'  => 'sanitize_text_field',
					'validate_callback'  => 'rest_validate_request_arg',
				),
			);
		}

		/**
		 * Get the magical context param.
		 *
		 * Ensures consistent description between endpoints, and populates enum from schema.
		 *
		 * @param array     $args
		 * @return array
		 */
		public function get_context_param( $args = array() ) {
			$param_details = array(
				'description'        => __( 'Scope under which the request is made; determines fields present in response.', 'vibe' ),
				'type'               => 'string',
				'sanitize_callback'  => 'sanitize_key',
				'validate_callback'  => 'rest_validate_request_arg',
			);
			$schema = $this->get_item_schema();
			if ( empty( $schema['properties'] ) ) {
				return array_merge( $param_details, $args );
			}
			$contexts = array();
			foreach ( $schema['properties'] as $key => $attributes ) {
				if ( ! empty( $attributes['context'] ) ) {
					$contexts = array_merge( $contexts, $attributes['context'] );
				}
			}
			if ( ! empty( $contexts ) ) {
				$param_details['enum'] = array_unique( $contexts );
				rsort( $param_details['enum'] );
			}
			return array_merge( $param_details, $args );
		}

		/**
		 * Add the values from additional fields to a data object.
		 *
		 * @param array  $object
		 * @param WP_REST_Request $request
		 * @return array modified object with additional fields.
		 */
		protected function add_additional_fields_to_object( $object, $request ) {

			$additional_fields = $this->get_additional_fields();

			foreach ( $additional_fields as $field_name => $field_options ) {

				if ( ! $field_options['get_callback'] ) {
					continue;
				}

				$object[ $field_name ] = call_user_func( $field_options['get_callback'], $object, $field_name, $request, $this->get_object_type() );
			}

			return $object;
		}

		/**
		 * Update the values of additional fields added to a data object.
		 *
		 * @param array  $object
		 * @param WP_REST_Request $request
		 */
		protected function update_additional_fields_for_object( $object, $request ) {

			$additional_fields = $this->get_additional_fields();

			foreach ( $additional_fields as $field_name => $field_options ) {

				if ( ! $field_options['update_callback'] ) {
					continue;
				}

				// Don't run the update callbacks if the data wasn't passed in the request.
				if ( ! isset( $request[ $field_name ] ) ) {
					continue;
				}

				call_user_func( $field_options['update_callback'], $request[ $field_name ], $object, $field_name, $request, $this->get_object_type() );
			}
		}

		/**
		 * Add the schema from additional fields to an schema array.
		 *
		 * The type of object is inferred from the passed schema.
		 *
		 * @param array $schema Schema array.
		 */
		protected function add_additional_fields_schema( $schema ) {
			if ( empty( $schema['title'] ) ) {
				return $schema;
			}

			/**
			 * Can't use $this->get_object_type otherwise we cause an inf loop.
			 */
			$object_type = $schema['title'];

			$additional_fields = $this->get_additional_fields( $object_type );

			foreach ( $additional_fields as $field_name => $field_options ) {
				if ( ! $field_options['schema'] ) {
					continue;
				}

				$schema['properties'][ $field_name ] = $field_options['schema'];
			}

			return $schema;
		}

		/**
		 * Get all the registered additional fields for a given object-type.
		 *
		 * @param  string $object_type
		 * @return array
		 */
		protected function get_additional_fields( $object_type = null ) {

			if ( ! $object_type ) {
				$object_type = $this->get_object_type();
			}

			if ( ! $object_type ) {
				return array();
			}

			global $wp_rest_additional_fields;

			if ( ! $wp_rest_additional_fields || ! isset( $wp_rest_additional_fields[ $object_type ] ) ) {
				return array();
			}

			return $wp_rest_additional_fields[ $object_type ];
		}

		/**
		 * Get the object type this controller is responsible for managing.
		 *
		 * @return string
		 */
		protected function get_object_type() {
			$schema = $this->get_item_schema();

			if ( ! $schema || ! isset( $schema['title'] ) ) {
				return null;
			}

			return $schema['title'];
		}

		/**
		 * Get an array of endpoint arguments from the item schema for the controller.
		 *
		 * @param string $method HTTP method of the request. The arguments
		 *                       for `CREATABLE` requests are checked for required
		 *                       values and may fall-back to a given default, this
		 *                       is not done on `EDITABLE` requests. Default is
		 *                       WP_REST_Server::CREATABLE.
		 * @return array $endpoint_args
		 */
		public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {

			$schema                = $this->get_item_schema();
			$schema_properties     = ! empty( $schema['properties'] ) ? $schema['properties'] : array();
			$endpoint_args = array();

			foreach ( $schema_properties as $field_id => $params ) {

				// Arguments specified as `readonly` are not allowed to be set.
				if ( ! empty( $params['readonly'] ) ) {
					continue;
				}

				$endpoint_args[ $field_id ] = array(
					'validate_callback' => 'rest_validate_request_arg',
					'sanitize_callback' => 'rest_sanitize_request_arg',
				);

				if ( isset( $params['description'] ) ) {
					$endpoint_args[ $field_id ]['description'] = $params['description'];
				}

				if ( WP_REST_Server::CREATABLE === $method && isset( $params['default'] ) ) {
					$endpoint_args[ $field_id ]['default'] = $params['default'];
				}

				if ( WP_REST_Server::CREATABLE === $method && ! empty( $params['required'] ) ) {
					$endpoint_args[ $field_id ]['required'] = true;
				}

				foreach ( array( 'type', 'format', 'enum' ) as $schema_prop ) {
					if ( isset( $params[ $schema_prop ] ) ) {
						$endpoint_args[ $field_id ][ $schema_prop ] = $params[ $schema_prop ];
					}
				}

				// Merge in any options provided by the schema property.
				if ( isset( $params['arg_options'] ) ) {

					// Only use required / default from arg_options on CREATABLE endpoints.
					if ( WP_REST_Server::CREATABLE !== $method ) {
						$params['arg_options'] = array_diff_key( $params['arg_options'], array( 'required' => '', 'default' => '' ) );
					}

					$endpoint_args[ $field_id ] = array_merge( $endpoint_args[ $field_id ], $params['arg_options'] );
				}
			}

			return $endpoint_args;
		}

		public static function http_response_data($status=1,$message='',$data){
			return array(
				'status' => $status,
				'message' => $message,
				'data' => $data
			);
		}
	}
}