<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class PMS_Meta_Box {

    /**
     * Meta box id
     *
     * @access public
     * @var string
     */
    public $id;

    /**
     * Meta box title
     *
     * @access public
     * @var string
     */
    public $title;

    /**
     * Post type to assign
     *
     * @access public
     * @var string
     */
    public $post_type;

    /**
     * Meta box position
     *
     * @access public
     * @var string
     */
    public $context;

    /**
     * Meta fields keys and a set of default values
     *
     * @access public
     * @var array
     */
    public $meta_default_values = array();



    /*
     * Constructor
     *
     */
    public function __construct( $id = '', $title ='', $post_type = '', $context = 'advanced' ) {

        $this->id        = $id;
        $this->title     = $title;
        $this->post_type = $post_type;
        $this->context   = $context;

        if( $this->post_type == pms_get_current_post_type() ) {

            add_action( 'add_meta_boxes' , array( $this, 'remove_meta_boxes' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

            // Enqueue scripts
            if( is_admin() )
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        }

        // Save posts, pages and custom post types
        add_action( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );

        // Save attachments, which are handled different in the WP core
        add_action( 'attachment_updated', array( $this, 'save_meta_box' ), 10, 2 );
    }


    /*
     * Method to remove certain meta boxes that are not needed
     *
     */
    public function remove_meta_boxes() {

        $removable_meta_boxes_post_types = array( 'pms-subscription' );

        if( in_array( $this->post_type, $removable_meta_boxes_post_types ) )
            remove_meta_box('slugdiv', $this->post_type, 'normal');

    }

    /*
     * Method that adds the meta box
     *
     */
    public function add_meta_box() {

        add_meta_box( $this->id, $this->title, array( $this, 'output_content' ), $this->post_type, $this->context );

    }


    /*
     * Method to display content in the meta-box
     *
     */
    public function output_content( $post ) {

        // Action to output content before default meta-box content
        do_action( 'pms_output_content_meta_box_' . $this->post_type . '_' . $this->id . '_before', $post );

        // Action to output default meta-box content
        do_action( 'pms_output_content_meta_box_' . $this->post_type . '_' . $this->id, $post );

        // Action to output content after default meta-box content
        do_action( 'pms_output_content_meta_box_' . $this->post_type . '_' . $this->id . '_after', $post );
    }


    /*
     * Method returns the post meta with default values if they are not set
     *
     */
    public function get_post_meta( $post, $default_values = array() ) {

        $post_meta = pms_get_post_meta( $post->ID );

        if( !empty( $default_values ) ) {
            foreach( $default_values as $meta_key => $default_value ) {
                if( !isset( $post_meta[ $meta_key ] ) ) {
                    $post_meta[ $meta_key ] = $default_value;
                }
            }
        }

        return $post_meta;
    }


    /*
     * Method that saves the meta box data
     *
     */
    public function save_meta_box( $post_id, $post ) {

        /*
         * Must verify nonce
         */

        // Skip if there's an auto-save going on
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;


        do_action( 'pms_save_meta_box_' . $this->post_type, $post_id, $post );

    }


    /*
     * Method to enqueue scripts on the admin side
     * It enqueues files based on a sanitized string from the meta-box id
     *
     */
    public function enqueue_admin_scripts() {

        $screen = get_current_screen();

        if( $screen->id != $this->post_type )
            return;

        // Sanitize the filename by removing the prefix and changing the underscores to dashes
        $js_file_name = 'meta-box-' . str_replace( '_', '-', str_replace( 'pms_', '', $this->id ) ) . '.js';

        // If the file exists where it should be, enqueue it
        if( file_exists( PMS_PLUGIN_DIR_PATH . 'assets/js/admin/' . $js_file_name ) )
            wp_enqueue_script( $this->id . '-js', PMS_PLUGIN_DIR_URL . 'assets/js/admin/' . $js_file_name, array( 'jquery' ) );


        do_action( 'pms_meta_box_enqueue_admin_scripts_' . $this->id );

    }
}
