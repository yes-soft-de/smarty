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

class Vibe_BP_API_Members{

    public static $instance;
    public static function init(){
    if ( is_null( self::$instance ) )
        self::$instance = new Vibe_BP_API_Members();

        return self::$instance;
    }

    function get_members($args){

    	$defaults = array(
    		'type'                => '',
			'page'                => 1,
			'per_page'            => 20,
			'max'                 => false,

			'page_arg'            => 'upage',  // See https://buddypress.trac.wordpress.org/ticket/3679.

			'include'             => false,    // Pass a user_id or a list (comma-separated or array) of user_ids to only show these users.
			'exclude'             => false,    // Pass a user_id or a list (comma-separated or array) of user_ids to exclude these users.

			'user_id'             => '', // Pass a user_id to only show friends of this user.
			'member_type'         => '',
			'member_type__in'     => '',
			'member_type__not_in' => '',
			'search_terms'        => false,

			'meta_key'            => false,    // Only return users with this usermeta.
			'meta_value'          => false,    // Only return users where the usermeta value matches. Requires meta_key.

			'populate_extras'     => true 
    	);

    	$members =array();
    	wp_parse_args($args,$defaults);
    	
		if ( bp_has_members( $args ) ) :
		 	while ( bp_members() ) :  bp_the_member();
		 		global $members_template;
		 		$member = (array)$members_template->member;
		 		$members[] = $this->prepare_item_from_context($member,$args);
		 	endwhile;
		endif;

		
		return apply_filters('bp_api_get_members',$members,$args) ;
		
    }

    function prepare_item_from_context($member,$args){
    	if(!empty($args['context'])){
			
			switch ($args['context']) {
				case 'view':
					$view_context = apply_filters('vibe_bp_api_get_member_view_scope',array('total_friend_count','display_name','id'),$member,$args); 
					$new_member =array();
					foreach ($member as $key => $value) {
						if(in_array($key,$view_context)){
							$new_member[$key] = $value;
						}
					}
					$member =$new_member;

				break;
				
				case 'loggedin':
					$member =$new_member;
				break;


				default:
					
				break;
			}
		}

		return $member;
    }

}

Vibe_BP_API_Members::init();