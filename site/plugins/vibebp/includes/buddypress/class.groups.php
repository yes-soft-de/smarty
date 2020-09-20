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

class Vibe_BP_API_Groups{

    public static $instance;
    public static function init(){
    if ( is_null( self::$instance ) )
        self::$instance = new Vibe_BP_API_Groups();

        return self::$instance;
    }

    function get_groups($args){

    	$defaults = array(
			'type'               => null,
			'orderby'            => 'date_created',
			'order'              => 'DESC',
			'per_page'           => $items_per_page ,
			'page'               => null,
			'user_id'            => 0,
			'slug'               => array(),
			'search_terms'       => false,
			'search_columns'     => array(),
			'group_type'         => '',
			'group_type__in'     => '',
			'group_type__not_in' => '',
			'meta_query'         => false,
			'include'            => false,
			'parent_id'          => null,
			'update_meta_cache'  => true,
			'update_admin_cache' => false,
			'exclude'            => false,
			'show_hidden'        => false,
			'status'             => array(),
			'fields'             => 'all',
		);

    	$groups =array();
    	wp_parse_args($args,$defaults);
    	
		if ( bp_has_groups( $args ) ) :
		 	while ( bp_groups() ) : bp_the_group();
		 		global $groups_template;
		 		$group = (array)$groups_template->group;
		 		
		 		$groups[] = $this->prepare_item_from_context($group,$args);
		 	endwhile;
		endif;

		
		return apply_filters('bp_api_get_groups',$groups,$args) ;
		
    }

    function prepare_item_from_context($group,$args){
    	if(!empty($args['context'])){
			
			switch ($args['context']) {
				case 'view':
					$view_context = apply_filters('vibe_bp_api_get_group_view_scope',array('total_friend_count','display_name','id'),$group,$args); 
					$new_member =array();
					foreach ($group as $key => $value) {
						if(in_array($key,$view_context)){
							$new_group[$key] = $value;
						}
					}
					$group =$new_group;

				break;
				
				case 'loggedin':
					$group =$new_group;
				break;


				default:
					
				break;
			}
		}

		return $group;
    }

}

Vibe_BP_API_Groups::init();