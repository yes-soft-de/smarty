<?php

if ( ! defined( 'ABSPATH' ) ) exit;


class VibeBP_Field_Types{

 	public static $instance;
    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Field_Types();
        return self::$instance;
    }

    public function __construct(){

    	add_filter( 'bp_xprofile_get_field_types', array( $this, 'register_field_types' ), 10, 1 );
    }


    function register_field_types($fields){

    	$fields = array_merge( $fields, $this->get_field_types() );
		return $fields;
    }	

    function includes(){
    	include_once 'xprofile_fields/class.field_type.color.php';	
    	include_once 'xprofile_fields/class.field_type.country.php';	
    	include_once 'xprofile_fields/class.field_type.location.php';	
        include_once 'xprofile_fields/class.field_type.social.php';   
    }

    function get_field_types(){

    	$this->includes();

    	$fields = array(
			'color' => 'VibeBP_Field_Type_Color',
			'country' => 'VibeBP_Field_Type_Country',
			'location' => 'VibeBP_Field_Type_Location',
            'social' => 'VibeBP_Field_Type_Social',
		);

		return $fields;
    }

}

VibeBP_Field_Types::init();

