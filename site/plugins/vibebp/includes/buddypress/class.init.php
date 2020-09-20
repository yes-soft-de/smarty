<?php
/**
 * Action functions for Course Module
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Vibe Course Module
 * @version     2.0
 */

 if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_BP_API_Init{

    public static $instance;
    public static function init(){
    if ( is_null( self::$instance ) )
        self::$instance = new Vibe_BP_API_Init();

        return self::$instance;
    }

    private function __construct(){

    	add_filter('bp_activity_get',array($this,'add_user_avatar'));
    }

    function add_user_avatar($activities){

    	if(!empty($activities) && REST_REQUEST){
    		foreach($activities['activities'] as $key => $activity){
    			$activities['activities'][$key] = $this->recursively_capture_avatar($activity);
    		}

    	}
    	return $activities;
    }

    function recursively_capture_avatar($activity){
        $activity->avatar =bp_core_fetch_avatar(
            array(
                'item_id'=>$activity->user_id,
                'object'=>'user',
                'type'=>'thumb',
                'html'=>false
            )
        );
        if(!empty($activity->children)){
            foreach((Array)$activity->children as $key=>$child_activity){
                $activity->children[$key] = $this->recursively_capture_avatar($child_activity);
            }
        }

        return $activity;
    }
}
Vibe_BP_API_Init::init();