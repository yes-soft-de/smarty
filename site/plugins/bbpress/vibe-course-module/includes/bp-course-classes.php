<?php

/**
 * This function should include all classes and functions that access the database.
 * In most BuddyPress components the database access classes are treated like a model,
 * where each table has a class that can be used to create an object populated with a row
 * from the corresponding database table.
 *
 * By doing this you can easily save, update and delete records using the class, you're also
 * abstracting database access.
 *
 * This function uses WP_Query and wp_insert_post() to fetch and store data, using WordPress custom
 * post types. This method for data storage is highly recommended, as it assures that your data
 * will be maximally compatible with WordPress's security and performance optimization features, in
 * addition to making your plugin easier to extend for other developers. The suggested
 * implementation here (where the WP_Query object is set as the query property on the
 * BP_course_Highfive object in get()) is one suggested implementation.
 */

if ( ! defined( 'ABSPATH' ) ) exit;
class BP_COURSE {
	var $id;
	var $date;
	var $query;

	/**
	 * bp_course_tablename()
	 *
	 * This is the constructor, it is auto run when the class is instantiated.
	 * It will either create a new empty object if no ID is set, or fill the object
	 * with a row from the table if an ID is provided.
	 */
	function __construct( $args = array() ) {
		// Set some defaults
		$defaults = array(
			'id'			=> 0,
			'instructor' => 0,
			'date' 	=> date( 'Y-m-d H:i:s' )
		);

		// Parse the defaults with the arguments passed
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		if ( $id ) {
			$this->id = $id;
			$this->populate( $this->id );
		} else {
			foreach( $r as $key => $value ) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * populate()
	 *
	 * This method will populate the object with a row from the database, based on the
	 * ID passed to the constructor.
	 */
	function populate() {
		global $wpdb, $bp;

		$post = get_post($this->id);
	}

	/**
	 * save()
	 *
	 * This method will save an object to the database. It will dynamically switch between
	 * INSERT and UPDATE depending on whether or not the object already exists in the database.
	 */

	function save() {
		global $wpdb, $bp;
		// Call a before save action here
		do_action( 'bp_course_data_before_save', $this );
		return $result;
	}

	/**
	 * Fire the WP_Query
	 *
	 * @package BuddyPress_Course_Component
	 * @since 1.6
	 */
	function get( $args = array() ) {
		
		// Only run the query once
		if ( empty( $this->query ) ) {
			$defaults = array(
			'id'		=> 0,
			'per_page'	=> 5,
			'paged'		=> 1,
			'order'		=> 'DESC',
			'orderby'	=> 'menu_order',
			'meta_key'  => '',
			'tax_query' => ''
			);

			$r = wp_parse_args( $args, $defaults );
			$r = apply_filters('wplms_course_drectory_default_order',$r);
			extract( $r );

			if(isset($user) && $user){
				if(empty($meta_query)){$meta_query = array();}
				array_push($meta_query,array(
					'key' => $user,
					'compare' => 'EXISTS'
					));
			}

			$query_args = array(
				'post_status'	 => 'publish',
				'post_type'	 => BP_COURSE_CPT,
				'order' => $order,
				'orderby'=> $orderby,
				'posts_per_page' => $per_page,
				'paged'		 => $paged,
			);

			if(!empty($post__in)){
				$query_args['post__in'] = $post__in;
			}

			if(isset($search_terms) && $search_terms){
				$query_args['s']=$search_terms;
			}

			if(isset($s) && $s){
				$query_args['s']=$s;
			}

			if(isset($meta_key) && $meta_key)
				$query_args['meta_key']=$meta_key;

			if(isset($meta_query) && is_array($meta_query)){
				$query_args['meta_query']= $meta_query;
			}else{
				unset($query_args['meta_query']);
			}

			if(isset($tax_query) && is_array($tax_query)){
				$query_args['tax_query']= $tax_query;
			}else{
				unset($query_args['tax_query']);
			}

			if(!empty($post_status)){
				$query_args['post_status']=$post_status;
			}else{
				if(current_user_can('edit_posts')){
					$query_args['post_status'] = array('publish','draft','pending','future','private');
				}	
			}

			// Some optional query args
			// Note that some values are cast as arrays. This allows you to query for multiple
			// authors/recipients at a time
			if ( isset($instructor )){
				if ( function_exists('get_coauthors')) {
					$instructor_name = get_the_author_meta('user_nicename',$instructor);
					if(isset($instructor_name))
						$query_args['author_name'] = $instructor_name;
					else
						$query_args['author'] = $instructor;
				}else
					$query_args['author'] = $instructor;
			}

			if(isset($author__in) && is_array($author__in) && count($author__in)){
				
				if ( function_exists('get_coauthors')) {
					$author_names = array();
					foreach($author__in as $author_id){
						$instructor_name = get_the_author_meta('user_nicename',$author_id);
						$author_names[] = $instructor_name;
					}

					if(isset($tax_query) && is_array($tax_query)){
						$query_args['tax_query'][]= array(
								'taxonomy'=>'author',
								'field'=>'name',
								'terms' => $author_names,
							);
					}else{
						$query_args['tax_query']= array(
							'relation' => 'AND',
								array(
									'taxonomy'=>'author',
									'field'=>'name',
									'terms' => $author_names,
								)
							);
					}
				}else
					$query_args['author__in']=$author__in;

			}
			if(isset($id) && $id){
				$query_args['p']=$id;
			}

			if(isset($user) && $user){
				global $bp;
				if(bp_is_my_profile()){
					unset($query_args['tax_query']);
					if(empty($query_args['meta_query'])){
						$query_args['meta_query']=array(array(
							'key' => $user,
							'compare' => 'EXISTS'
						));
					}else{
						$query_args['meta_query'][]=array(
							'key' => $user,
							'compare' => 'EXISTS'
						);
					}
				}
			}

			$query_args = apply_filters('bp_course_wplms_filters',$query_args);
		
			if(isset($id) && $id){
				$this->query = new WP_Query( $query_args );
			}else{
				//Cache only for Longer Complex queries
				$cache_key = 'wplms';
				$cache_key .=$this->implode_r('_',$query_args);

				$cache_duration = vibe_get_option('cache_duration'); 

				if(!isset($cache_duration)) $cache_duration=0;

				if($cache_duration){
					$this->query = get_transient($cache_key);
				}else{$this->query=false;}

				$cache_key=str_replace('draft_pending_future_private','dpfp',$cache_key);

				if ( false === $this->query) {

					$this->query = new WP_Query( $query_args );
					if($cache_duration)
						set_transient($cache_key,$this->query,$cache_duration);
				}
			}

			if(empty($this->query->query_vars['posts_per_page'])){
				$this->query->query_vars['posts_per_page'] = 1;
			}
			// Let's also set up some pagination
			$this->pag_links = paginate_links( array(
				'base' => esc_url(add_query_arg( 'items_page', '%#%' )),
				'format' => '',
				'total' => ceil( (int) $this->query->found_posts / (int) $this->query->query_vars['posts_per_page'] ),
				'current' => (int) $paged,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
				'mid_size' => 1
			) );
		}
	}

	/**
	 * Part of our bp_course_has_high_fives() loop
	 *
	 * @package BuddyPress_Course_Component
	 * @since 1.6
	 */
	function have_posts() {
		return $this->query->have_posts();
	}

	/**
	 * Part of our bp_course_has_high_fives() loop
	 *
	 * @package BuddyPress_Course_Component
	 * @since 1.6
	 */
	function the_post() {
		return $this->query->the_post();
	}

	/**
	 * delete()
	 *
	 * This method will delete the corresponding row for an object from the database.
	 */
	function delete() {
		return wp_trash_post( $this->id );
	}

	/* Static Functions */

	/**
	 * Static functions can be used to bulk delete items in a table, or do something that
	 * doesn't necessarily warrant the instantiation of the class.
	 *
	 * Look at bp-core-classes.php for courses of mass delete.
	 */

	function delete_all() {

	}

	function delete_by_course_id() {

	}

	public function bp_get_search_default_text(){

	}
	
	public function implode_r($glue,$array) {
	    $out = '';
	    foreach ($array as $k=>$item) {

	        if (is_array($item)) {
	            $out .= $this->implode_r($glue,$item).$glue;
	        } else {
	        	switch($k){
		 		case 'post_status':
		 		case 'post_type':
		 		case 'relation':
		 		case 'compare':
		 			$out .= '';
		 		break;
		 		case 'order':
		 			$out .= (($item == 'DESC')?'D':'S').$glue;
		 		break;
		 		case 'orderby':
		 		if(is_array($item))
		 			$out .= 'doby'.$glue;
		 		else	
		 			$out .= substr($item,0,1).substr($item, -1).$glue;
		 		break;
		 		default:    
		 		if(strlen($item)){
		 			if(strlen($item) > 7)            
		 				$item = substr($item,0,1).substr($item,-1);
			 		$out .= $item.$glue; 
			 	}
		 		break;
	        	}
	        }
	    }
	    $out = substr($out, 0, 0-strlen($glue));

	    return $out;
	}
	
	public static function course_exists( $slug) {
		global $post;
		$args=array(
		  'name' => $slug,
		  'post_type' => BP_COURSE_SLUG,
		  'post_status' => 'publish',
		  'showposts' => 1,
		  'caller_get_posts'=> 1
		);
		$my_posts = get_posts($args);

		if(bp_course_get_post_type( $my_posts[0]->ID ) == BP_COURSE_SLUG){
			return true;
		}else
			return false;
	}
}

?>