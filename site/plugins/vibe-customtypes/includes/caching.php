<?php

 if ( ! defined( 'ABSPATH' ) ) exit;
class vibe_customtypes_cache{
	
	function __construct(){
		
		$post_types = get_post_types(array('public'=>true),'objects'); 

			add_action('publish_post',array($this,'delete_cache'),10,2 );
			add_action('delete_post',array($this,'delete_cache'),10 );
			add_action('transition_post_status',array($this,'delete_cache_transition'),10,3);
		
	}

	function delete_cache($post_id, $post=NULL){
		global $wpdb;
		if(!isset($post))
			$post = get_post($post_id);
		$cache_duration = vibe_get_option('cache_duration'); if(!isset($cache_duration)) $cache_duration=0;
		if($cache_duration){
			$key = 'kposts_'.$post->post_type;
	        $instructor_content_privacy = vibe_get_option('instructor_content_privacy');
	        if($instructor_content_privacy){
	        	$user_id = get_current_user_id(); 
	        	$key .= '_'.$user_id;
	        }
	        $linkage = vibe_get_option('linkage');
	        if(isset($linkage) && $linkage){
	        	$linkage_terms = get_the_terms( $post_id, 'linkage');
	        	if(isset($linkage_terms) && is_array($linkage_terms)){
	        		foreach ( $linkage_terms as $term ) {
						$key .='_'.$term->name;
					}
	        	}
	        }
	        delete_transient($key);
	        if($post->post_type == 'course'){
		        global $wpdb;
		        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%wplms_%_course%'");
	    	}
		}	
	}

	function delete_cache_transition($new_status,$old_status, $post){
		global $wpdb;
		$cache_duration = vibe_get_option('cache_duration'); if(!isset($cache_duration)) $cache_duration=0;
		
		if($cache_duration){
			$key = 'kposts_'.$post->post_type;
	        $instructor_content_privacy = vibe_get_option('instructor_content_privacy');
	        if($instructor_content_privacy){
	        	$user_id = get_current_user_id(); 
	        	$key .= '_'.$user_id;
	        }
	        $linkage = vibe_get_option('linkage');
	        if(isset($linkage) && $linkage){
	        	$linkage_terms = get_the_terms( $post_id, 'linkage');
	        	if(isset($linkage_terms) && is_array($linkage_terms)){
	        		foreach ( $linkage_terms as $term ) {
						$key .='_'.$term->name;
					}
	        	}
	        }
			delete_transient($key);
			if($post->post_type == 'course'){
		        global $wpdb;
		        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%wplms_%_course%'");
	    	}
		}	
	}

}

new vibe_customtypes_cache;