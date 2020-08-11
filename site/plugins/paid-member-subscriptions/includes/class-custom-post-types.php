<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Base class to register post types more easily
 *
 */
Class PMS_Custom_Post_Type {

    /**
     * Custom post type slug
     *
     * @access public
     * @var string
     */
    public $post_type;

    /**
     * Custom post type singular name
     *
     * @access public
     * @var string
     */
    public $singular_name;

    /**
     * Custom post type plural name
     *
     * @access public
     * @var string
     */
    public $plural_name;

    /**
     * Custom post type arguments
     *
     * @access public
     * @var array
     */
    public $args;


    /*
     * Constructor
     *
     */
    public function __construct( $post_type = '', $singular_name = '', $plural_name = '', $args = array() ) {

        $this->post_type     = $post_type;
        $this->singular_name = $singular_name;
        $this->plural_name   = $plural_name;
        $this->args          = $args;

        // Filter arguments before anything
        add_action( 'init', array( $this, 'filter_arguments' ) );

        // Hook the register post type method to init
        add_action( 'init', array( $this, 'register_post_type' ) );

        // Save post hook
        add_action( 'save_post', array( $this, 'save_post' ) );

        // Add removable query args
        add_filter( 'removable_query_args', array( $this, 'removable_query_args' ) );

        // Display admin notices
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        // Enqueue scripts
        if( is_admin() )
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

    }


    /*
     * Helper method to filter the arguments. Due to the fact that certain WP functions are not available before the "init" hook
     * the filtering will be done on init to avoid errors.
     *
     */
    public function filter_arguments() {

        // For the moment only administrators should be able to see custom post types
        if( !current_user_can( 'manage_options' ) ) {
            $this->args['show_ui'] = false;
        }

        $this->args = apply_filters( 'pms_custom_post_type_args', $this->args, $this->post_type );

    }


    /*
     * Method that registers a new custom post type
     *
     */
    public function register_post_type() {

        do_action( 'pms_before_register_post_type', $this->post_type );

        // Labels
        $labels = array(
            'name'               => sprintf( _x( '%s', 'post type general name', 'paid-member-subscriptions' ), $this->plural_name ),
            'singular_name'      => sprintf( _x( '%s', 'post type singular name', 'paid-member-subscriptions' ), $this->singular_name ),
            'menu_name'          => sprintf( _x( '%s', 'admin menu', 'paid-member-subscriptions' ), $this->plural_name ),
            'name_admin_bar'     => sprintf( _x( '%s', 'add new on admin bar', 'paid-member-subscriptions' ), $this->singular_name ),
            'add_new'            => _x( 'Add New', 'paid-member-subscriptions' ),
            'add_new_item'       => sprintf( __( 'Add New %s', 'paid-member-subscriptions' ), $this->singular_name ),
            'new_item'           => sprintf( __( 'New %s', 'paid-member-subscriptions' ), $this->singular_name ),
            'edit_item'          => sprintf( __( 'Edit %s', 'paid-member-subscriptions' ), $this->singular_name ),
            'view_item'          => sprintf( __( 'View %s', 'paid-member-subscriptions' ), $this->singular_name ),
            'all_items'          => sprintf( __( '%s', 'paid-member-subscriptions' ), $this->plural_name ),
            'search_items'       => sprintf( __( 'Search %s', 'paid-member-subscriptions' ), $this->plural_name ),
            'parent_item_colon'  => sprintf( __( 'Parent %s:', 'paid-member-subscriptions' ), $this->plural_name ),
            'not_found'          => sprintf( __( 'No %s found', 'paid-member-subscriptions' ), strtolower( $this->plural_name ) ),
            'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'paid-member-subscriptions' ), strtolower( $this->plural_name ) )
        );

        $defaults = array(
            'labels' => apply_filters( 'pms_register_post_type_args_labels', $labels, $this->post_type )
        );

        $args = wp_parse_args( $this->args, $defaults );

        register_post_type( $this->post_type, apply_filters( 'pms_register_post_type_' . $this->post_type , $args ) );

    }


    /*
     * Method to validate and save data
     *
     */
    public function save_post( $post_id ) {

        // Skip if there's an auto-save going on
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        do_action( 'pms_save_post_' . $this->post_type, $post_id );

    }


    /*
     * Add query args we wish WP to remove after page load
     * This function should be replaced by children classes
     *
     */
    function removable_query_args( $query_args ) {

        return $query_args;

    }


    /*
     * Display admin notices for custom post types
     * This function should be replaced by children classes
     *
     */
    public function admin_notices() {}


    /*
     * Method to enqueue scripts on the admin side
     * It enqueues files based on a sanitized string from the post type slug
     *
     */
    public function enqueue_admin_scripts() {

        $screen = get_current_screen();

        if( $screen->post_type != $this->post_type )
            return;

        // Sanitize the filename by removing the prefix and changing the underscores to dashes

        // In case we have dashes in the post type slug, replace them with underscores
        $js_file_name = str_replace( '-', '_', $this->post_type );
        $js_file_name = 'cpt-' . str_replace( '_', '-', str_replace( 'pms_', '', $js_file_name ) ) . '.js';

        // If the file exists where it should be, enqueue it
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'assets/js/admin/' . $js_file_name ) )
            wp_enqueue_script( $this->post_type . '-js', PMS_PLUGIN_DIR_URL . 'assets/js/admin/' . $js_file_name, array( 'jquery' ) );


        do_action( 'pms_cpt_enqueue_admin_scripts_' . $this->post_type );

    }

    /**
     * 0-5 reserved for Group Subscriptions
     * @return array   error messages
     */
    public function get_admin_notices(){

        return apply_filters( 'pms_cpt_admin_notice_messages', array() );

    }

}
