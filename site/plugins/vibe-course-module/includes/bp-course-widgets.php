<?php

/**
 * In this file you should create and register widgets for your component.
 *
 * Widgets should be small, contained functionality that a site administrator can drop into
 * a widget enabled zone (column, sidebar etc)
 *
 * Good courses of suitable widget functionality would be short lists of updates or featured content.
 *
 * For course the friends and groups components have widgets to show the active, newest and most popular
 * of each.
 */

 if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'widgets_init', 'bp_course_register_widgets' );

function bp_course_register_widgets() {
    register_widget('BP_Course_Widget');
    register_widget('BP_Instructor_Widget');
    register_widget('BP_Course_Search_Widget');
    register_widget('BP_Course_Stats_Widget');
    register_widget('BP_Course_Filter_Widget');
    register_widget('BP_Course_Reviews_Widget');
    register_widget('BP_Course_Related_Courses_Widget');
    if(function_exists('bp_is_active') && bp_is_active('xprofile'))
    	register_widget('Member_Types');
    if(function_exists('bp_is_active') && bp_is_active('groups'))
    register_widget('Group_Types');
    
}

          

/*======= Vibe Member Types ======== */  

class Member_Types extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'Member Types Navigation', 'description' => __('Member Types Navigation', 'vibe') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'member_types' );
    parent::__construct( 'member_types', __('Member Types Navigation', 'vibe'), $widget_ops, $control_ops );
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    	global $bp;
		if($bp->current_component != 'members' || empty($bp->members)){
			return;
		}
		if(!function_exists('bp_get_member_type_object') || !function_exists('bp_get_members_member_type_base') || !function_exists('bp_get_member_types'))
			return;
    	extract( $args );
    
	    $title = (isset($instance['title'])?apply_filters('widget_title', $instance['title'] ):'');
	    $exclude_member_types = (isset($instance['exclude_member_types'])?esc_attr($instance['exclude_member_types']):'');
	    
	    
	    echo $before_widget;
	   
	    $type_object = bp_get_member_type_object( bp_get_current_member_type() );
	    if(!empty($type_object->directory_slug)){
	    	$current_type = $type_object->directory_slug;
	    }
	    
	   	$mt_base_slug = bp_get_members_member_type_base();
	  	if(function_exists('bp_get_member_types')){
	        $all_member_types = bp_get_member_types( array(), 'objects' );
	    }
	    $args['exclude'] = $exclude_member_types;
	    $pages = get_option('bp-pages');
	    $members_dir = isset($pages['members'])?$pages['members']:0;
	    echo '<div class="course_cat_nav"><ul class=" '.$order.'">';

	    echo '<li class="'.((bp_current_component() == 'members' && is_page() && empty($current_type ))?'current-cat':'').'"><a href="'.get_permalink($members_dir).'">'.get_the_title($members_dir).'</a></li>';
	    global $wpdb;
	    $orderArray = array();
	    $saved_member_types = get_option('wplms_member_types');
	    foreach($saved_member_types as $key => $saved_member_type){
	    	$orderArray[] = $saved_member_type['id'];
	    }
	    
		$ordered = array();
	    foreach ($orderArray as $key) {
	        if (array_key_exists($key, $all_member_types)) {
	            $ordered[$key] = $all_member_types[$key];
	            unset($all_member_types[$key]);
	        }
	    }
	    $all_member_types =  $ordered + $all_member_types;
	    if(!empty($exclude_member_types)){
	    	$exclude_member_types_array = explode(',',$exclude_member_types); 
	    }
	    if(!empty($exclude_member_types_array)){
	    	foreach ($exclude_member_types_array as  $exclude) {
	    		unset($all_member_types[$exclude]);
	    	}
	    }
	    if(!empty($all_member_types)){
	    	foreach($all_member_types as $key => $member_type){

	    		if ( ! bp_is_root_blog() ) {
					switch_to_blog( bp_get_root_blog_id() );
				}
				$sql = array(
					'select' => "SELECT tt.count FROM {$wpdb->term_taxonomy} tt LEFT JOIN {$wpdb->terms} t",
					'on'     => 'ON tt.term_id = t.term_id',
					'where'  => $wpdb->prepare( "WHERE tt.taxonomy = '%s' AND t.slug = '%s'", 'bp_member_type',$key),
				);
				$member_type_count = $wpdb->get_var( join( ' ', $sql ) );
				restore_current_blog();

				if(empty($member_type_count))
					$member_type_count=0;
		    	echo '<li class="'.$key.' '.((!empty($current_type ) && $current_type  == $key)?'current-cat':'').'">
			    		<a href="'.get_permalink($members_dir).(!empty($mt_base_slug)?$mt_base_slug:'type').'/'.$key.'">'.$member_type->labels['name'].'
			    			<span>'.$member_type_count .'</span>
			    		</a> 
		    		</li>';
		    }
	    }
	    
	    echo '</ul></div>';
	    echo $after_widget;
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['exclude_member_types'] = $new_instance['exclude_member_types'];
  
    
    return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                    'title'  => __('Member Types','vibe'),
                    'exclude_ids'  => '',
                    'hierarchial'=>0,
                    'sort'  => 'DESC',
                    'order' => ''
                    );
      
      $instance = wp_parse_args( (array) $instance, $defaults );
      $title  = esc_attr($instance['title']);
      $exclude_member_types = esc_attr($instance['exclude_member_types']);
                                 
        ?>
         
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
     
        <p>
          <label for="<?php echo $this->get_field_id('exclude_member_types'); ?>"><?php _e('Exclude member types slugs (comma saperated):','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('exclude_member_types'); ?>" name="<?php echo $this->get_field_name('exclude_member_types'); ?>" type="text" value="<?php echo $exclude_member_types; ?>" />
        </p>
        
        <?php 
        wp_reset_query();
        wp_reset_postdata();
    }
}

/*======= Vibe Group Types ======== */  

class Group_Types extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'Group Types Navigation', 'description' => __('Group Types Navigation', 'vibe') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'group_types' );
    parent::__construct( 'group_types', __('Group Types Navigation', 'vibe'), $widget_ops, $control_ops );
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    	if(!function_exists('bp_is_active') || !bp_is_active('groups'))
            return;
    	global $bp;
		if($bp->current_component != 'groups' || empty($bp->groups)){
			return;
		}
		if(!function_exists('bp_groups_get_group_type_object') || !function_exists('bp_groups_get_group_types'))
			return;
    	extract( $args );
    
	    $title = (isset($instance['title'])?apply_filters('widget_title', $instance['title'] ):'');
	    $exclude_group_types = (isset($instance['exclude_group_types'])?esc_attr($instance['exclude_group_types']):'');
	    
	    
	    echo $before_widget;
	    $type_object = bp_groups_get_group_type_object( bp_get_current_group_directory_type() );
	    if(!empty($type_object->directory_slug)){
	    	$current_type = $type_object->directory_slug;
	    }
	    
	   	$gt_base_slug = bp_get_groups_group_type_base();
	  	if(function_exists('bp_groups_get_group_types')){
	        $all_group_types = bp_groups_get_group_types( array(), 'objects' );
	    }
	    $args['exclude'] = $exclude_group_types;
	    $pages = get_option('bp-pages');
	    $groups_dir = isset($pages['groups'])?$pages['groups']:0;
	    echo '<div class="course_cat_nav"><ul class=" '.$order.'">';

	    echo '<li class="'.((bp_current_component() == 'groups' && is_page() && empty($current_type))?'current-cat':'').'"><a href="'.get_permalink($groups_dir).'">'.get_the_title($groups_dir).'</a></li>';
	    global $wpdb;
	    $orderArray = array();
	    $saved_group_types = get_option('wplms_group_types');
	    foreach($saved_group_types as $key => $saved_group_type){
	    	$orderArray[] = $saved_group_type['id'];
	    }
	    
		$ordered = array();
	    foreach ($orderArray as $key) {
	        if (array_key_exists($key, $all_group_types)) {
	            $ordered[$key] = $all_group_types[$key];
	            unset($all_group_types[$key]);
	        }
	    }
	    $all_group_types =  $ordered + $all_group_types;
	    if(!empty($exclude_group_types)){
	    	$exclude_group_types_array = explode(',',$exclude_group_types); 
	    }
	    if(!empty($exclude_group_types_array)){
	    	foreach ($exclude_group_types_array as  $exclude) {
	    		unset($all_group_types[$exclude]);
	    	}
	    }
	    if(!empty($all_group_types)){
	    	foreach($all_group_types as $key => $group_type){

	    		if ( ! bp_is_root_blog() ) {
					switch_to_blog( bp_get_root_blog_id() );
				}
				$sql = array(
					'select' => "SELECT tt.count FROM {$wpdb->term_taxonomy} tt LEFT JOIN {$wpdb->terms} t",
					'on'     => 'ON tt.term_id = t.term_id',
					'where'  => $wpdb->prepare( "WHERE tt.taxonomy = '%s' AND t.slug = '%s'", 'bp_group_type',$key),
				);
				$group_type_count = $wpdb->get_var( join( ' ', $sql ) );
				restore_current_blog();

				if(empty($group_type_count))
					$group_type_count=0;
		    	echo '<li class="'.$key.' '.((!empty($current_type) && $current_type == $key)?'current-cat':'').'">
			    		<a href="'.get_permalink($groups_dir).(!empty($gt_base_slug)?$gt_base_slug:'type').'/'.$key.'">'.$group_type->labels['name'].'
			    			<span>'.$group_type_count .'</span>
			    		</a> 
		    		</li>';
		    }
	    }
	    
	    echo '</ul></div>';
	    echo $after_widget;
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['exclude_group_types'] = $new_instance['exclude_group_types'];
  
    
    return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                    'title'  => __('Group Types','vibe'),
                    'exclude_ids'  => '',
                    'hierarchial'=>0,
                    'sort'  => 'DESC',
                    'order' => ''
                    );
      
      $instance = wp_parse_args( (array) $instance, $defaults );
      $title  = esc_attr($instance['title']);
      $exclude_group_types = esc_attr($instance['exclude_group_types']);
                                 
        ?>
         
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
     
        <p>
          <label for="<?php echo $this->get_field_id('exclude_group_types'); ?>"><?php _e('Exclude group types slugs (comma saperated):','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('exclude_group_types'); ?>" name="<?php echo $this->get_field_name('exclude_group_types'); ?>" type="text" value="<?php echo $exclude_group_types; ?>" />
        </p>
        
        <?php 
        wp_reset_query();
        wp_reset_postdata();
    }
}

class BP_Course_Widget extends WP_Widget {

	function __construct() {
	  $widget_ops = array( 'classname' => 'BuddyPress Course Widget', 'description' => 'Displays Courses in single, list & carousel formats.' );
	  $control_ops = array( 'width' => 250, 'height' => 350,'id_base' => 'bp_course_widget');
	  parent::__construct( 'bp_course_widget',  __('BuddyPress Course Widget','vibe'), $widget_ops, $control_ops);
	  }

	function widget( $args, $instance ) {
		global $bp;
		extract( $args );

		extract( $instance, EXTR_SKIP );
		echo $before_widget;
		if(isset($title) && $title !='')
		echo $before_title .
		     $title .
		     $after_title; 

		     //Preparing Query
		     if(isset($ids) && $ids !='' && strlen($ids) > 5){
		     	$course_ids = explode(',',$ids);
		     	$the_query= new WP_QUERY(array( 'post_type' => 'course', 'post__in' => $course_ids ) );
		     }else{

		     	$qargs = array('post_type' => 'course');
		     	if(isset($category) && $category !='' && $category != 'none'){
		     		$qargs['course-cat'] = $category;
		     	}
		     	if($orderby =='name' || $orderby == 'comment_count' || $orderby == 'date' || $orderby == 'title' || $orderby == 'rand'){
		     		$qargs['orderby'] = $orderby;
		     	}else{
		     		if($orderby == 'vibe_start_date'){
		     			$qargs['orderby'] = 'meta_value';
		                $qargs['meta_key'] = 'vibe_start_date';
		                $qargs['meta_type'] = 'DATE';
		                $qargs['order'] = 'ASC';
		                $today = date('Y-m-d');
		                if(empty($qargs['meta_query'])){
		                    $qargs['meta_query'] = array(
		                              array(
		                                  'key'     => 'vibe_start_date',
		                                  'value'   => $today,
		                                  'compare' => '>='
		                              )
		                          );
		                }else{
		                    $qargs['meta_query'][] = array(
		                                  'key'     => 'vibe_start_date',
		                                  'value'   => $today,
		                                  'compare' => '>='
		                          );
		                }
		     		}else{
		     			//Average rating and Number of students in course
		     			$qargs['orderby']='meta_value_num';
		     			$qargs['meta_key'] = $orderby;	
		     		}
		     	}

		     	$qargs['posts_per_page'] = $max_items;
		     	if(empty($qargs['order']))
		     		$qargs['order'] = $order;

		     	$qargs = apply_filters('bp_course_wplms_filters',$qargs);
		     	$the_query= new WP_Query($qargs);
		     }

		     switch($style){
		     	case 'list':
		     	case 'list1':
		     		echo '<ul class="widget_course_list no-ajax">';
		     	break;
		     	case 'carousel':
		     		echo '<div class="widget_carousel flexslider  no-ajax"><ul class="slides">';
		     	break;
		     }
		     ?>     
	<?php

	while($the_query->have_posts()):$the_query->the_post();
	global $post;
	switch($style){
		     	case 'list':

		     	echo '<li><a href="'.get_permalink($post->ID).'">'.get_the_post_thumbnail($post->ID,'thumbnail').'<h6>'.get_the_title($post->ID).'<span>'.__('by','vibe').' '.bp_core_get_user_displayname($post->post_author).'</span></h6></a></li>';
		     	break;
		     	case 'list1':
		     	echo '<li><a href="'.get_permalink($post->ID).'">'.get_the_post_thumbnail($post->ID,'thumbnail').'</a><div class="course-list1"><h6><a href="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a></h6>'.bp_course_get_course_meta().'</div></li>';
		     	break;
		     	case 'carousel':
		     	echo '<li>';
		     	echo thumbnail_generator($post,'course','3','0',true,true);
		     	echo '</li>';
		     	break;
		     	default:
		     	echo '<div class="single_course">';
		     	echo thumbnail_generator($post,'course','3','0',true,true);
		     	echo '</div>';
		     	break;
		     }

	endwhile;
	wp_reset_postdata();
	?>
	<?php
		switch($style){
				case 'list1':
		     	case 'list':
		     		echo '</ul>';
		     	break;
		     	case 'carousel':
		     		echo '</ul></div>';
		     	break;
		     }
	?>
	<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['style'] = strip_tags( $new_instance['style'] );
		$instance['category'] = strip_tags( $new_instance['category'] );
		$instance['orderby'] = strip_tags( $new_instance['orderby'] );
		$instance['order'] = strip_tags( $new_instance['order'] );
		$instance['ids'] = strip_tags( $new_instance['ids'] );
		$instance['max_items'] = strip_tags( $new_instance['max_items'] );

		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'title'=> 'Course','style' => 'single','orderby'=>'name','order'=>'ASC','category'=>'','ids'=>'', 'max_items' => 5 );

		$instance = wp_parse_args( (array) $instance, $defaults );
		$course_cats=get_terms('course-cat','orderby=count&hide_empty=0');
		extract( $instance, EXTR_SKIP );

		?>
		<p><label for="bp-course-widget-ids"><?php _e( 'Widget Title', 'vibe' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-course-widget-style"><?php _e( 'Style', 'vibe' ); ?> 
		<select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">
			<option value="single" <?php selected('single',esc_attr( $style )); ?>><?php _e('Single','vibe'); ?></option>
			<option value="list" <?php selected('list',esc_attr( $style )); ?>><?php _e('List','vibe'); ?></option>
			<option value="list1" <?php selected('list1',esc_attr( $style )); ?>><?php _e('Ratings List','vibe'); ?></option>
			<option value="carousel" <?php selected('carousel',esc_attr( $style )); ?>><?php _e('Carousel','vibe'); ?></option>
		</select>
		</p>
		<p><label for="bp-course-widget-category"><?php _e( 'Select Course Category', 'vibe' ); ?> 
		<select id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
			<option value=""><?php _e('None','vibe'); ?></option>
		<?php
			foreach($course_cats as $course_cat){
				echo '<option value="'.$course_cat->slug.'" '.selected($course_cat->slug,esc_attr( $category )).'>'.$course_cat->name.'</option>';
			}
		?>
		</select>
		</p>
		<p><label for="bp-course-widget-orderby"><?php _e( 'Order By', 'vibe' ); ?> 
		<select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
			<option value="rand" <?php selected('rand',$orderby); ?>><?php _e('Random','vibe'); ?></option>
			<option value="name" <?php selected('name',$orderby); ?>><?php _e('Name','vibe'); ?></option>
			<option value="title" <?php selected('title',$orderby ); ?>><?php _e('Course Title','vibe'); ?></option>
			<option value="comment_count" <?php selected('comment_count', $orderby ); ?>><?php _e('Number of Reviews','vibe'); ?></option>
			<option value="date" <?php selected('date',$orderby ); ?>><?php _e('Date Published','vibe'); ?></option>
			<option value="average_rating" <?php selected('average_rating',$orderby ); ?>><?php _e('Rating','vibe'); ?></option>
			<option value="vibe_students" <?php selected('vibe_students',$orderby ); ?>><?php _e('Number of Students','vibe'); ?></option>
			<option value="vibe_start_date" <?php selected('vibevibe_start_date_students',$orderby ); ?>><?php _e('Upcoming Courses (Start Date)','vibe'); ?></option>
		</select>
		</p>
		<p><label for="bp-course-widget-order"><?php _e( 'Sort ', 'vibe' ); ?> 
		<select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
			<option value="ASC" <?php selected('ASC',esc_attr( $order )); ?>><?php _e('Ascending','vibe'); ?></option>
			<option value="DESC" <?php selected('DESC',esc_attr( $order )); ?>><?php _e('Decending','vibe'); ?></option>
		</select>
		</p>
		<p><label for="bp-course-widget-ids"><?php _e( 'Specific Courses (enter comma saperated ids)', 'vibe' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'ids' ); ?>" name="<?php echo $this->get_field_name( 'ids' ); ?>" type="text" value="<?php echo esc_attr( $ids ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-course-widget-max"><?php _e( 'Number of Courses to show', 'vibe' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_items' ); ?>" name="<?php echo $this->get_field_name( 'max_items' ); ?>" type="text" value="<?php echo esc_attr( $max_items ); ?>" style="width: 30%" /></label></p>
	<?php
	}
}

class BP_Instructor_Widget extends WP_Widget {

	function __construct() {
	  $widget_ops = array( 'classname' => 'BuddyPress Instructor Widget', 'description' => 'Displays Current Instructor details widget.' );
	  $control_ops = array( 'width' => 250, 'height' => 350,'id_base' => 'bp_instructor_widget');
	  parent::__construct( 'bp_instructor_widget',  __('BuddyPress Instructor Widget','vibe'), $widget_ops, $control_ops);
	  }

	function widget( $args, $instance ) {
		global $bp;
		extract( $args );
		extract( $instance, EXTR_SKIP );
		echo $before_widget;
		if(isset($title) && $title !='')
		echo $before_title .
		     $title .
		     $after_title; 

		     if(is_single()){
		     	global $post;
				$instructor=$post->post_author;
		     }

		     if(!isset($max_items)){$max_items = 5;}
		    echo '<div class="course_instructor_widget">';
		    echo bp_course_get_instructor('instructor_id='.$instructor);
		    echo '<div class="description">'.bp_course_get_instructor_description('instructor_id='.$instructor).'</div>';
		    $instructing_courses=apply_filters('wplms_instructing_courses_endpoint','instructing-courses');
		    echo '<a href="'.get_author_posts_url($instructor).$instructing_courses.'" class="tip" title="'.__('Check all Courses created by ','vibe').bp_core_get_user_displayname($instructor).'"><i class="icon-plus-1"></i></a>';
		    echo '<h5>'.__('More Courses by ','vibe').bp_core_get_user_displayname($instructor).'</h5>';
		    if(empty($max_items)){
		    	echo '</div>'; 
		
	 			echo $after_widget;
		    	return;
		    }
		    echo '<ul class="widget_course_list">';
		    $query_args = apply_filters('bp_instructor_widget',array(
		    	'post_type'=> 'course',
		    	'author'=>$instructor,
		    	'posts_per_page'=> $max_items, 
		    	));
		    if(function_exists('vibe_get_option')){
		    	$excluded_courses=vibe_get_option('hide_courses');
	    		$query_args['post__not_in'] = $excluded_courses;	
		    }
		    
		    $query = new WP_Query($query_args);
		    if($query->have_posts()):
		    	while($query->have_posts()):$query->the_post();
		    	global $post;
		    	echo '<li><a href="'.get_permalink($post->ID).'">'.get_the_post_thumbnail($post->ID,'thumbnail').'<h6>'.get_the_title($post->ID).'<span>'.__('by','vibe').' '.bp_core_get_user_displayname($post->post_author).'</span></h6></a>';
		    	endwhile;
		    endif;
		    wp_reset_postdata();
		    echo '</ul>';
		    echo '</div>'; 
		     //Preparing Query
		    
	 echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['instructor'] = strip_tags( $new_instance['instructor'] );
		$instance['max_items'] = strip_tags( $new_instance['max_items'] );

		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'title'=> 'Instructor Details',
			'instructor' => '1','max_items' => 5 );

		$instance = wp_parse_args( (array) $instance, $defaults );
		extract( $instance, EXTR_SKIP );
		$title = esc_attr($instance['title']);
		?>
		<p><label for="bp-instructor-widget-title"><?php _e( 'Widget Title', 'vibe' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-instructor-widget-title"><?php _e( 'Fallback Instructor ID', 'vibe' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'instructor' ); ?>" name="<?php echo $this->get_field_name( 'instructor' ); ?>" type="text" value="<?php echo esc_attr( $instructor ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-instructor-widget-max"><?php _e( 'Number of Courses by the Instructor to show', 'vibe' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_items' ); ?>" name="<?php echo $this->get_field_name( 'max_items' ); ?>" type="text" value="<?php echo esc_attr( $max_items ); ?>" style="width: 30%" /></label></p>
	<?php
	}
}

class BP_Course_Search_Widget extends WP_Widget {

	function __construct() {
	  $widget_ops = array( 'classname' => 'buddypress-course-search-widget', 'description' => 'Displays Advanced search for Courses.' );
	  $control_ops = array( 'width' => 250, 'height' => 350,'id_base' => 'bp_course_search_widget');
	  parent::__construct( 'bp_course_search_widget',  __('BuddyPress Course Search Widget','vibe'), $widget_ops, $control_ops);
	  }

	function widget( $args, $instance ) {
		global $bp;
		extract( $args );
		extract( $instance, EXTR_SKIP );
		echo $before_widget;
		if(isset($title) && $title !='')
		echo $before_title .
		     $title .
		     $after_title; 

		     $html ='<form role="search" method="get" id="searchform" action="'.home_url( '/' ).'">
		     			<input type="hidden" name="post_type" value="'.BP_COURSE_SLUG.'" />
		     			<ul>';

		     if(isset($cats) && $cats == 1){

		     	$cat_val= isset($_GET['course-cat'])?$_GET['course-cat']:'';

		     	$course_cats = get_terms('course-cat',array('hide_empty'=>false));
		     	$html .= '<li><select name="course-cat" class="chosen chzn-select" data-placeholder="'.__('Select Course Category','vibe').'">';
		     	$html .='<option value="">'.__('Select Course Category','vibe').'</option>';
		     	foreach($course_cats as $term){
		     		$html .='<option value="'.$term->slug.'" '.(isset($cat_val)?selected($cat_val,$term->slug,false):'').'>'.$term->name.'</option>';
		     	}
		     	$html .= '</select></li>'; 
		     }

		     if(isset($instructors) && $instructors == 1){
		     	$admin_flag = apply_filters('wplms_show_admin_in_instructors',1);
		     	$instructors = array();
		     	if($admin_flag){
		     		$args = array(
	                'role' => 'administrator' // instructor
		    		);
					$user_query = new WP_User_Query( $args );

					if ( !empty( $user_query->results ) ) {
						foreach ( $user_query->results as $user ) {
				        	$instructors[$user->ID] =$user->display_name;
				        }
						      
					}
		     	}
		     	$args = array(
	                'role' => 'instructor' // instructor
	    		);
				$user_query = new WP_User_Query( $args );
				if ( !empty( $user_query->results ) ) {
			        foreach ( $user_query->results as $user ) {
			        	$instructors[$user->ID] =$user->display_name;
			        }    
				}	 
				$html .='<li><select name="instructor" class="chosen chzn-select" data-placeholder="'.__('Select Instructor','vibe').'">';
				$html .='<option value="">'.__('Select Instructor','vibe').'</option>';
				$inst_val = isset($_GET['instructor'])?$_GET['instructor']:'';
				foreach($instructors as $id=>$name){
					$html .='<option value="'.$id.'" '.(!empty($inst_val)?selected($inst_val,$id,false):'').'>'.$name.'</option>';
				}
				$html .='</select></li>';  
		     }

		    $locationoption = vibe_get_option('location'); 
		    if(isset($locationoption) && $locationoption && isset($location) && $location == 1){
		     	$location_val=$_GET['location'];
		     	$location_vals = get_terms('location');
		     	$html .= '<li><select name="location" class="chosen chzn-select" data-placeholder="'.__('Select Course Location','vibe').'">';
		     	$html .='<option value="">'.__('Select Course Location','vibe').'</option>';
		     	foreach($location_vals as $term){
		     		$html .='<option value="'.$term->slug.'" '.(isset($location_val)?selected($location_val,$term->slug,false):'').'>'.$term->name.'</option>';
		     	}
		     	$html .= '</select></li>';
		    }
		    $leveloption = vibe_get_option('level'); 
		    if(isset($leveloption) && $leveloption && isset($level) && $level == 1){
		     	$level_val=$_GET['level'];
		     	$level_vals = get_terms('level');
		     	$html .= '<li><select name="level" class="chosen chzn-select" data-placeholder="'.__('Select Course Level','vibe').'">';
		     	$html .='<option value="">'.__('Select Course Level','vibe').'</option>';
		     	foreach($level_vals as $term){
		     		$html .='<option value="'.$term->slug.'" '.(isset($level_val)?selected($level_val,$term->slug,false):'').'>'.$term->name.'</option>';
		     	}
		     	$html .= '</select></li>';
		    }

			$html .='<li><input type="text" value="'.(isset($_GET['s'])?$_GET['s']:'').'" name="s" id="s" placeholder="'.__('Type Keywords..','vibe').'" /></li>
				<li><input type="submit" id="searchsubmit" value="'.__('Search','vibe').'" /></li></ul>
				</form>';

			echo $html;
		    
	echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['cats'] = strip_tags( $new_instance['cats'] );
		$instance['instructors'] = strip_tags( $new_instance['instructors'] );
		$instance['location'] = strip_tags( $new_instance['location'] );
		$instance['level'] = strip_tags( $new_instance['level'] );
		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 
			'title'=> 'Advanced Course Search Widget',
			'instructors' => 1,
			'cats' => 1,
			'location'=>1,
			'level' => 1,
			 );

		$instance = wp_parse_args( (array) $instance, $defaults );
		extract( $instance, EXTR_SKIP );

		if(function_Exists('vibe_get_option')){
			$leveloption = vibe_get_option('level'); 
			$locationoption = vibe_get_option('location'); 
		}

		$title = esc_attr($instance['title']);
		$cats = esc_attr($instance['cats']);
		$instructors = esc_attr($instance['instructors']);

		if(isset($leveloption) && $leveloption)
			$level = esc_attr($instance['level']);

		if(isset($locationoption) && $locationoption)
			$location = esc_attr($instance['location']);

		?>
		<p><label for="bp-course-search-widget-title"><?php _e( 'Widget Title', 'vibe' ); ?> <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-course-cat-dropdown"><?php _e( 'Show Course Category Dropdown', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'cats' ); ?>" name="<?php echo $this->get_field_name( 'cats' ); ?>" type="checkbox" value="1" <?php checked($cats,1,true) ?>/></label></p>
		<p><label for="bp-instructor-dropdown"><?php _e( 'Show Instructor Dropdown', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'instructors' ); ?>" name="<?php echo $this->get_field_name( 'instructors' ); ?>" type="checkbox" value="1"  <?php checked($instructors,1,true) ?>/></label></p>
		<?php 
		if(isset($locationoption) && $locationoption){
		?>
			<p><label for="bp-instructor-location"><?php _e( 'Show Location Dropdown', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'location' ); ?>" name="<?php echo $this->get_field_name( 'location' ); ?>" type="checkbox" value="1"  <?php checked($location,1,true) ?>/></label></p>
		<?php
		}
		if(isset($leveloption) && $leveloption){
		?>
			<p><label for="bp-instructor-level"><?php _e( 'Show Level Dropdown', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'level' ); ?>" name="<?php echo $this->get_field_name( 'level' ); ?>" data-x="<?php echo $level; ?>" type="checkbox" value="1"  <?php checked($level,1,true) ?>/></label></p>
		<?php
		}
	}
}

class BP_Course_Stats_Widget extends WP_Widget {

	function __construct() {
	  $widget_ops = array( 'classname' => 'buddypress-course-stats-widget', 'description' => 'Displays Stats for Course.' );
	  $control_ops = array( 'width' => 250, 'height' => 350,'id_base' => 'bp_course_stats_widget');
	  parent::__construct( 'bp_course_stats_widget',  __('BuddyPress Course Stats Widget','vibe'), $widget_ops, $control_ops);
	  }

	function widget( $args, $instance ) {
		global $bp,$wpdb;
		extract( $args );
		extract( $instance, EXTR_SKIP );
		echo $before_widget;
		if(isset($title) && $title !='')
		echo $before_title .
		     $title .
		     $after_title; 

		if($course == 'current')     
			$course = get_the_ID();

		if(is_numeric($course)){
			if($students){
				$ct=$wpdb->get_results("
						SELECT SUM(rel.meta_value) as total_students
					    FROM {$wpdb->posts} AS posts
					    LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
					    WHERE 	posts.post_type 	= 'course'
					    AND     posts.ID = $course
						AND 	posts.post_status 	= 'publish'
						AND 	rel.meta_key   = 'vibe_students'
					");
				$total_students=(empty($ct[0]->total_students)?0:$ct[0]->total_students);
			}
			if($badgecertificates){
				$total_badges = get_post_meta($course,'badge',true);
				$total_certificates= get_post_meta($course,'pass',true);
			}
		}else{
			if($students){
				$ct=$wpdb->get_results("
						SELECT SUM(rel.meta_value) as total_students
					    FROM {$wpdb->posts} AS posts
					    LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
					    WHERE 	posts.post_type 	= 'course'
						AND 	posts.post_status 	= 'publish'
						AND 	rel.meta_key   = 'vibe_students'
					");
				$total_students=(empty($ct[0]->total_students)?0:$ct[0]->total_students);
			}
			if($badgecertificates){
				$ct=$wpdb->get_results("
							SELECT SUM(rel.meta_value) as badge
						    FROM {$wpdb->posts} AS posts
						    LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
						    WHERE 	posts.post_type 	= 'course'
							AND 	posts.post_status 	= 'publish'
							AND 	rel.meta_key   = 'badge'
						");
				$total_badges = (empty($ct[0]->badge)?0:$ct[0]->badge);
				$ct=$wpdb->get_results("
							SELECT SUM(rel.meta_value) as certificates
						    FROM {$wpdb->posts} AS posts
						    LEFT JOIN {$wpdb->postmeta} AS rel ON posts.ID = rel.post_id
						    WHERE 	posts.post_type 	= 'course'
							AND 	posts.post_status 	= 'publish'
							AND 	rel.meta_key   = 'pass'
						");
				$total_certificates= (empty($ct[0]->certificates)?0:$ct[0]->certificates);
			}
		}    
		 
		echo '<div class="stat_num">';
		if($students)
		    echo '<strong class="tip" title="'.__('TOTAL STUDENTS','vibe').'"><i class="icon-myspace-alt"></i><span>'.$total_students.'</span></strong>';
		if($badgecertificates)
		    echo '<strong  class="tip" title="'.__('BADGES','vibe').'"><i class="icon-award-stroke"></i><span>'.$total_badges.'</span></strong>
		        <strong  class="tip" title="'.__('CERTIFICATES','vibe').'"><i class="icon-certificate-file"></i><span>'.$total_certificates.'</span></strong>';

		echo '</div>';                
	 echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['course'] = strip_tags( $new_instance['course'] );
		$instance['students'] = strip_tags( $new_instance['students'] );
		$instance['badgecertificates'] = strip_tags( $new_instance['badgecertificates'] );
		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 
			'title'=> 'Course Stats Widget',
			'course' => '',
			 );

		$instance = wp_parse_args( (array) $instance, $defaults );
		extract( $instance, EXTR_SKIP );
		$title = esc_attr($instance['title']);
		$course = esc_attr($instance['course']);
		$students = esc_attr($instance['students']);
		$badgecertificates = esc_attr($instance['badgecertificates']);
		?>
		<p><label for="bp-course-stats-widget-title"><?php _e( 'Widget Title', 'vibe' ); ?> <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-course-stats-dropdown"><?php _e( 'Select Course', 'vibe' ); ?> 
			<select id="<?php echo $this->get_field_id( 'course' ); ?>" name="<?php echo $this->get_field_name( 'course' ); ?>">
				<option value="" <?php selected('',$course); ?>><?php _e('All','vibe'); ?></option>
				<option value="current" <?php selected('current',$course); ?>><?php _e('Current Course (defaults to all)','vibe'); ?></option>
				<?php
					$args = array('post_type'=>'course','posts_per_page'=>-1);
					$the_query= new WP_QUERY($args);
					while($the_query->have_posts()):$the_query->the_post();
					echo '<option value="'.get_the_ID().'" '.selected('current',$course).'>'.get_the_title().'</option>';
					endwhile;
					wp_reset_postdata();
				?>
			</select>
		</label></p>
		<p><label for="bp-course-stats-level"><?php _e( 'Show Students Stats', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'students' ); ?>" name="<?php echo $this->get_field_name( 'students' ); ?>" type="checkbox" value="1"  <?php checked($students,1,true) ?>/></label></p>
		<p><label for="bp-course-certificates-level"><?php _e( 'Show Badge/Certificates Stats', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'badgecertificates' ); ?>" name="<?php echo $this->get_field_name( 'badgecertificates' ); ?>" type="checkbox" value="1"  <?php checked($badgecertificates,1,true) ?>/></label></p>
		<?php
	}
}

class BP_Course_Filter_Widget extends WP_Widget {

	function __construct() {
	  $widget_ops = array( 'classname' => 'buddypress-course-filter-widget', 'description' => 'Displays Category Filter for Course. Only use in Course Directory Page.' );
	  $control_ops = array( 'width' => 250, 'height' => 350,'id_base' => 'bp_course_filter_widget');
	  parent::__construct( 'bp_course_filter_widget',  __('BuddyPress Course Filter Widget','vibe'), $widget_ops, $control_ops);
	  }

	function widget( $args, $instance ) {
		global $bp,$wpdb;
		extract( $args );
		extract( $instance, EXTR_SKIP );

		if(!is_post_type_archive('course') && !is_tax('course-cat') && !is_tax('level') && !is_tax('location') && !(function_exists('bp_is_directory') && bp_is_directory('course') && $bp->current_component == 'course')){
			return;
		}

		echo $before_widget.'<div class="course_filters '.(empty($auto_click)?'':'auto_click').'">';
		
		/* Exclude check     */
		$exclude_array=array();
		if(isset($exclude) && $exclude){
			if(strpos($exclude,',')){
				$exclude_array=explode(',',$exclude);
			}else{
				$exclude_array[]=$exclude;
			}
		}

		if(isset($category) && $category){
			$cat_args = apply_filters('wplms_course_filters_course_cat',array('orderby'=>'term_group','order'=>'DESC','parent'=>0));
			$categories =  get_terms('course-cat',$cat_args);
			if(isset($categories) && is_array($categories)){
				echo '<h4>'.$category_label.'</h4>';
				echo '<ul class="category_filter">';
				foreach($categories as $category){
					if(!in_array($category->slug,$exclude_array)){

						$sub_args=array('orderby'=>'term_group','order'=>'DESC','child_of'=>$category->term_id);
						$sub_categories =  get_terms('course-cat',$sub_args);	

						echo '<li>'.((isset($sub_categories) && is_Array($sub_categories) && count($sub_categories))?'<span></span>':'').'
						<div class="checkbox">
							<input id="'.$category->slug.'" type="checkbox" class="bp-course-category-filter" name="bp-course-category-filter" value="'.$category->slug.'" /> 
							<label for="'.$category->slug.'">'.$category->name.(!empty($show_post_count)?' ('.$category->count.')':'').'</label>
						</div>';
						
						if(isset($sub_categories) && is_array($sub_categories) && count($sub_categories)){
							echo '<ul class="sub_categories">';
							foreach($sub_categories as $sub_category){
								echo '<li>
								<div class="checkbox">
									<input id="'.$sub_category->slug.'" type="checkbox" class="bp-course-category-filter" name="bp-course-category-filter" value="'.$sub_category->slug.'" /> 
									<label for="'.$sub_category->slug.'">'.$sub_category->name.(!empty($show_post_count)?' ('.$sub_category->count.')':'').'</label>
								</div>';
							}
							echo '</ul>';
						} 
						echo '</li>';
					}
				}
				echo '</ul>';
			}
		}

		if(isset($location) && $location){ 
			$args = apply_filters('wplms_course_filters_location',array('orderby'=>'count','order'=>'DESC',));
			$categories =  get_terms('location',$args);
			if(isset($categories) && is_array($categories)){
				echo '<h4>'.$location_label.'</h4>';
				echo '<ul class="location_filter">';
				foreach($categories as $category){
					if(!in_array($category->slug,$exclude_array)){
						echo '<li>
						<div class="checkbox">
							<input id="'.$category->slug.'" type="checkbox" class="bp-course-location-filter" name="bp-course-category-level" value="'.$category->slug.'" /> 
							<label for="'.$category->slug.'">'.$category->name.(!empty($show_post_count)?' ('.$category->count.')':'').'</label>
						</div></li>';
					}
				}
				echo '</ul>';
			}
		}

		if(isset($level) && $level){
			$args = apply_filters('wplms_course_filters_level',array('orderby'=>'count','order'=>'DESC',));
			$categories =  get_terms('level',$args);
			if(isset($categories) && is_array($categories)){
				echo '<h4>'.$level_label.'</h4>';
				echo '<ul class="level_filter">';
				foreach($categories as $category){
					if(!in_array($category->slug,$exclude_array)){
						echo '<li>
						<div class="checkbox">
							<input id="'.$category->slug.'" type="checkbox" class="bp-course-level-filter" name="bp-course-category-level" value="'.$category->slug.'" /> 
							<label for="'.$category->slug.'">'.$category->name.(!empty($show_post_count)?' ('.$category->count.')':'').'</label>
						</div></li>';
					}
				}
				echo '</ul>';
			}
		}

		if(isset($instructor) && $instructor){
			echo '<h4>'.$instructor_label.'</h4>';
			echo '<ul class="instructor_filter">';
			$flag = apply_filters('wplms_show_admin_in_instructors',1);
			if(isset($flag) && $flag){
				$instructor_args = apply_filters('wplms_course_filter_admin_args',array('role' => 'Administrator'));
				$user_query = new WP_User_Query($instructor_args);
				// User Loop
				$published_course = '';
				if ( ! empty( $user_query->results ) ) {
					foreach ( $user_query->results as $user ) {
						if(!empty($show_post_count)){
							if(function_exists('count_user_posts_by_type')){
								$published_course = ' ('.count_user_posts_by_type($user->ID,'course').')';
							}
						}
						echo '<li>
						<div class="checkbox">
							<input id="user'.$user->ID.'" type="checkbox" class="bp-course-instructor-filter" name="bp-course-instructor-filter" value="'.$user->ID.'" /> <label for="user'.$user->ID.'">'.$user->display_name.$published_course.'</label>
						</div></li>';
					}
				}
			}
			$instructor_args = apply_filters('wplms_course_filter_instructor_args',array('role' => 'Instructor'));
			$user_query = new WP_User_Query($instructor_args);
			// User Loop
			if ( ! empty( $user_query->results ) ) {
				foreach ( $user_query->results as $user ) {
					if(!empty($show_post_count)){
						if(function_exists('count_user_posts_by_type')){
							$published_course = ' ('.count_user_posts_by_type($user->ID,'course').')';
						}
					}
					echo '<li><div class="checkbox">
						<input id="user'.$user->ID.'" type="checkbox" class="bp-course-instructor-filter" name="bp-course-instructor-filter" value="'.$user->ID.'" /> <label for="user'.$user->ID.'">'.$user->display_name.$published_course.'</label>
						</div></li>';
				}
			}
			echo '</ul>';
		}

		if(isset($offline) && $offline){
				echo '<h4>'.$offline_label.'</h4>';
				echo '<ul class="type_filter">';
				echo '<li><div class="radio"><input id="all_online" type="radio" class="bp-course-offline-filter" name="bp-course-offline-filter" value="all" /><label for="all_online">'.__('All','vibe').'</label></div></li>';
				echo '<li><div class="radio"><input id="online" type="radio" class="bp-course-offline-filter" name="bp-course-offline-filter" value="H" /><label for="online">'.__('Online','vibe').'</label></div></li>';
				echo '<li><div class="radio"><input id="offline" type="radio" class="bp-course-offline-filter" name="bp-course-offline-filter" value="S" /><label for="offline">'.__('Offline','vibe').'</label></div></li>';
				echo '</ul>';
		}

		if(isset($upcoming) && $upcoming){
				wp_enqueue_script('jquery-ui-datepicker');
				echo '<h4>'.$upcoming_label.'</h4>';
				echo '<ul class="date_filter">';
				echo '<li><input type="text" id="start_date" class="datepicker form_field bp-course-date-filter" data-type="start_date" value="" placeholder="'.__('Start Date','vibe').'"></li>';
				echo '<li><input type="text" id="end_date" class="datepicker form_field bp-course-date-filter"  data-type="end_date" value="" placeholder="'.__('End Date','vibe').'"></li>';
				echo '</ul>';
		}

		if(isset($free) && $free){
				echo '<h4>'.$free_label.'</h4>';
				echo '<ul class="type_filter">';
				echo '<li><div class="radio"><input id="all" type="radio" class="bp-course-free-filter" name="bp-course-free-filter" value="all"  /> <label for="all">'.__('All','vibe').'</label></div></li>';
				echo '<li><div class="radio"><input id="free" type="radio" class="bp-course-free-filter" name="bp-course-free-filter" value="free" /> <label for="free">'.__('Free','vibe').'</label></div></li>';
				echo '<li><div class="radio"><input id="paid" type="radio" class="bp-course-free-filter" name="bp-course-free-filter" value="paid" /> <label for="paid">'.__('Paid','vibe').'</label></div></li>';
				echo '</ul>';
		}
		if(empty($auto_click)){
			echo '<a id="submit_filters" class="button full">'.__('Filter Results','vibe').'</a>';	
		}
		
	 	echo '</div>'.$after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['category'] = strip_tags( $new_instance['category'] );
		$instance['category_label'] = strip_tags( $new_instance['category_label'] );
		$instance['offline'] = strip_tags( $new_instance['offline'] );
		$instance['offline_label'] = strip_tags( $new_instance['offline_label'] );
		$instance['free'] = strip_tags( $new_instance['free'] );
		$instance['free_label'] = strip_tags( $new_instance['free_label'] );
		$instance['upcoming'] = strip_tags( $new_instance['upcoming'] );
		$instance['upcoming_label'] = strip_tags( $new_instance['upcoming_label'] );
		$instance['location'] = strip_tags( $new_instance['location'] );
		$instance['location_label'] = strip_tags( $new_instance['location_label'] );
		$instance['level'] = strip_tags( $new_instance['level'] );
		$instance['level_label'] = strip_tags( $new_instance['level_label'] );
		$instance['instructor'] = strip_tags( $new_instance['instructor'] );
		$instance['instructor_label'] = strip_tags( $new_instance['instructor_label'] );
		$instance['exclude'] = strip_tags( $new_instance['exclude'] );
		$instance['auto_click'] = strip_tags( $new_instance['auto_click'] );
		$instance['show_post_count'] = strip_tags( $new_instance['show_post_count'] );
		
		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 
			'category' => 1,
			'category_label'=>__('Course Categories','vibe'),
			'location'=>0,
			'location_label'=>__('Location','vibe'),
			'level'=>0,
			'level_label'=>__('Levels','vibe'),
			'offline' => 0,
			'offline_label'=>__('Type','vibe'),
			'free' => 1,
			'free_label'=>__('Price','vibe'),
			'instructor'=>0,
			'instructor_label'=>__('Instructors','vibe'),
			'upcoming'=>0,
			'upcoming_courses'=>__('Upcoming Courses','vibe'),
			'exlude'=>'',
			'auto_click'=>0,
			'show_post_count'=>0,
			 );

		$instance = wp_parse_args( (array) $instance, $defaults );
		extract( $instance, EXTR_SKIP );
		
		$category_label = esc_attr($instance['category_label']);
		$category = esc_attr($instance['category']);
		$location = esc_attr($instance['location']);
		$location_label = esc_attr($instance['location_label']);
		$level = esc_attr($instance['level']);
		$level_label = esc_attr($instance['level_label']);
		$offline = esc_attr($instance['offline']);
		$offline_label = esc_attr($instance['offline_label']);
		$free = esc_attr($instance['free']);
		$free_label = esc_attr($instance['free_label']);
		$upcoming = esc_attr($instance['upcoming']);
		$upcoming_label = esc_attr($instance['upcoming_label']);
		$exclude = esc_attr($instance['exclude']);
		$instructor = esc_attr($instance['instructor']);
		$instructor_label = esc_attr($instance['instructor_label']);
		$auto_click = esc_attr($instance['auto_click']);
		$show_post_count = esc_attr($instance['show_post_count']);
		?>
		
		<p><label for="bp-course-filter-category"><?php _e( 'Show Course category filter', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" type="checkbox" value="1"  <?php checked($category,1,true) ?>/></label></p>
		<p><label for="bp-course-filter-cat-title"><?php _e( 'Course Categories Label', 'vibe' ); ?> <input id="<?php echo $this->get_field_id( 'category_label' ); ?>" name="<?php echo $this->get_field_name( 'category_label' ); ?>" type="text" value="<?php echo esc_attr( $category_label ); ?>" style="width: 30%" /></label></p>
		
		<p><label for="bp-course-filter-free"><?php _e( 'Show Free/Paid/All filter', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'free' ); ?>" name="<?php echo $this->get_field_name( 'free' ); ?>" type="checkbox" value="1"  <?php checked($free,1,true) ?>/></label></p>
		<p><label for="bp-course-filter-free-title"><?php _e( 'Free/Paid Label', 'vibe' ); ?> <input id="<?php echo $this->get_field_id( 'free_label' ); ?>" name="<?php echo $this->get_field_name( 'free_label' ); ?>" type="text" value="<?php echo esc_attr( $free_label ); ?>" style="width: 30%" /></label></p>

		<p><label for="bp-course-filter-offline"><?php _e( 'Show Offline/Online filter', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'offline' ); ?>" name="<?php echo $this->get_field_name( 'offline' ); ?>" type="checkbox" value="1"  <?php checked($offline,1,true) ?>/></label></p>
		<p><label for="bp-course-filter-offline-title"><?php _e( 'Online/Offline Label', 'vibe' ); ?> <input id="<?php echo $this->get_field_id( 'offline_label' ); ?>" name="<?php echo $this->get_field_name( 'offline_label' ); ?>" type="text" value="<?php echo esc_attr( $offline_label ); ?>" style="width: 30%" /></label></p>

		<p><label for="bp-course-filter-upcoming"><?php _e( 'Show Upcoming/Past Courses filter', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'upcoming' ); ?>" name="<?php echo $this->get_field_name( 'upcoming' ); ?>" type="checkbox" value="1"  <?php checked($upcoming,1,true) ?>/></label></p>
		<p><label for="bp-course-filter-upcoming-title"><?php _e( 'Upcoming courses Label', 'vibe' ); ?> <input id="<?php echo $this->get_field_id( 'upcoming_label' ); ?>" name="<?php echo $this->get_field_name( 'upcoming_label' ); ?>" type="text" value="<?php echo esc_attr( $upcoming_label ); ?>" style="width: 30%" /></label></p>
		<?php

		if(function_exists('vibe_get_option'))
			$enable_location=vibe_get_option('location');

		if(isset($enable_location) && $enable_location){
			?>
			<p><label for="bp-course-filter-location"><?php _e( 'Show Location filter', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'location' ); ?>" name="<?php echo $this->get_field_name( 'location' ); ?>" type="checkbox" value="1"  <?php checked($location,1,true) ?>/></label></p>
			<p><label for="bp-course-filter-location-title"><?php _e( 'Location Label', 'vibe' ); ?> <input id="<?php echo $this->get_field_id( 'location_label' ); ?>" name="<?php echo $this->get_field_name( 'location_label' ); ?>" type="text" value="<?php echo esc_attr( $location_label ); ?>" style="width: 30%" /></label></p>
			<?php
		}
		?>
		<?php

		if(function_exists('vibe_get_option'))
			$enable_level=vibe_get_option('level');

		if(isset($enable_level) && $enable_level){
			?>
			<p><label for="bp-course-filter-level"><?php _e( 'Show Levels filter', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'level' ); ?>" name="<?php echo $this->get_field_name( 'level' ); ?>" type="checkbox" value="1"  <?php checked($level,1,true) ?>/></label></p>
			<p><label for="bp-course-filter-free-title"><?php _e( 'Levels Label', 'vibe' ); ?> <input id="<?php echo $this->get_field_id( 'level_label' ); ?>" name="<?php echo $this->get_field_name( 'level_label' ); ?>" type="text" value="<?php echo esc_attr( $level_label ); ?>" style="width: 30%" /></label></p>
			<?php
		}
		?>
		<p><label for="bp-course-filter-instructor"><?php _e( 'Show Instructor filter', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'instructor' ); ?>" name="<?php echo $this->get_field_name( 'instructor' ); ?>" type="checkbox" value="1"  <?php checked($instructor,1,true) ?>/></label></p>
		<p><label for="bp-course-filter-instructor-title"><?php _e( 'Instructor Label', 'vibe' ); ?> <input id="<?php echo $this->get_field_id( 'instructor_label' ); ?>" name="<?php echo $this->get_field_name( 'instructor_label' ); ?>" type="text" value="<?php echo esc_attr( $instructor_label ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-course-filter-exclude"><?php _e( 'Exclude Category/Level Slugs (comma saperated)', 'vibe' ); ?> <input id="<?php echo $this->get_field_id( 'exclude' ); ?>" name="<?php echo $this->get_field_name( 'exclude' ); ?>" type="text" value="<?php echo esc_attr( $exclude ); ?>" /></label></p>
		<p><label for="bp-course-filter-auto-click"><?php _e( 'Auto Click on input select (hides filter button)', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'auto_click' ); ?>" name="<?php echo $this->get_field_name( 'auto_click' ); ?>" type="checkbox" value="1"  <?php checked($auto_click,1,true) ?>/></label></p>
		<p><label for="bp-course-filter-show-post-count"><?php _e( 'Show course count', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'show_post_count' ); ?>" name="<?php echo $this->get_field_name( 'show_post_count' ); ?>" type="checkbox" value="1"  <?php checked($show_post_count,1,true) ?>/></label></p>
		<?php
	}
}

class BP_Course_Reviews_Widget extends WP_Widget {

	function __construct() {
	  $widget_ops = array( 'classname' => 'Course Reviews Widget', 'description' => 'Displays Courses Reviews in single, list & carousel formats.' );
	  $control_ops = array( 'width' => 250, 'height' => 350,'id_base' => 'bp_course_reviews_widget');
	  parent::__construct( 'bp_course_reviews_widget',  __('Course Reviews Widget','vibe'), $widget_ops, $control_ops);
	  }

	function widget( $args, $instance ) {
		global $bp;
		extract( $args );
		extract( $instance, EXTR_SKIP );
		echo $before_widget;
		if(isset($title) && $title !='')
		echo $before_title .
		     $title .
		     $after_title; 

		     //Preparing Query
		     global $wpdb;

		     if(isset($ids) && $ids !='' && strlen($ids) > 5){
		     	$review_ids = explode(',',$ids);
		     	$comments = new WP_Comment_Query(array( 'ID' => $review_ids ) );
		     }else{

		     	$qargs = array('post_type' => 'course');
		     	if(isset($course) && $course !='' && $course != 'none'){
		     		$qargs['post_id'] = $course;
		     	}

		     	$qargs['order'] = $order;
		     	if($orderby == 'comment_date_gmt'){
		     		$qargs['orderby'] = $orderby;
		     	}else if( $orderby == 'rand'){
		     		$qargs['orderby'] = 'disable';
		     		$x = rand(0,10);
		     		if($x%2){$qargs['order'] = 'ASC';}else{$qargs['order'] = 'DESC';}
		     	}else{
		     		$qargs['orderby']='meta_value';
		     		$qargs['meta_key'] = $orderby;
		     	}

		     	$qargs['number'] = $max_items;
		     	$comment_query = new WP_Comment_Query($qargs);

		     }

		     switch($style){
		     	case 'carousel':
		     		echo '<div class="widget_carousel flexslider  no-ajax"><ul class="slides">';
		     	break;
		     	default:
		     		echo '<ul class="widget_reviews_list no-ajax">';
		     	break;
		     }
		     ?>     
	<?php

	$comments = $comment_query->query( $qargs );
	if($comments){
		foreach($comments as $comment){
			switch($style){
		     	case 'list':
			     	echo '<li>';
			     	$course = '<a href="'.get_permalink($comment->comment_post_ID).'">'.get_the_title($comment->comment_post_ID).'</a>';
			     	$title = get_comment_meta($comment->comment_ID,'review_title',true);
			     	$rating = get_comment_meta($comment->comment_ID,'review_rating',true);
			     	if(isset($comment->user_id) && $comment->user_id){ 
			     		$avatar = get_avatar($comment->user_id);
			     		$name = bp_core_get_user_displayname($comment->user_id);
			     	}else{
			     		$default = vibe_get_option('default_avatar');
			     		$avatar = '<img src="'.$default.'" alt="'.__('Default avatar','vibe').'" />';
			     		$name = $comment->comment_author;
			     	}
			     	echo $avatar;
			     	echo '<div class="list_course_review"><small>'.$name.' - '.$course.'</small>';
			     	echo '<h4>'.$title.'<span>'.bp_course_display_rating($rating).'</span></h4></div>';
		     		echo '</li>';
		     	break;
		     	default:
			     	echo '<li><div class="course_review">'; 
			     	$course = '<a href="'.get_permalink($comment->comment_post_ID).'">'.get_the_title($comment->comment_post_ID).'</a>';
			     	$title = get_comment_meta($comment->comment_ID,'review_title',true);
			     	$rating = get_comment_meta($comment->comment_ID,'review_rating',true);
			     	echo '<small>'.$course.'</small>';
			     	echo '<h4>'.$title.'<span>'.bp_course_display_rating($rating).'</span></h4>';	
			     	echo $comment->comment_content;	
			     	if(isset($comment->user_id) && $comment->user_id){ 
			     		$avatar = get_avatar($comment->user_id);
			     		$name = bp_core_get_user_displayname($comment->user_id);
			     	}else{
			     		$default = vibe_get_option('default_avatar');
			     		$avatar = '<img src="'.$default.'" alt="'.__('Default avatar','vibe').'" />';
			     		$name = $comment->comment_author;
			     	}
			     	
			     	echo '<div class="review_author">'.$avatar.'<h5>'.$name.'</h5>';
			     	echo '</div></li>';
		     	break;
		    }
		}
	}

	?>
	<?php
		switch($style){
	     	case 'carousel':
	     		echo '</ul></div>';
	     	break;
	     	default:
	     		echo '</ul>';
	     	break;
		}
	?>
	<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['style'] = strip_tags( $new_instance['style'] );
		$instance['course'] = strip_tags( $new_instance['course'] );
		$instance['orderby'] = strip_tags( $new_instance['orderby'] );
		$instance['order'] = strip_tags( $new_instance['order'] );
		$instance['ids'] = strip_tags( $new_instance['ids'] );
		$instance['max_items'] = strip_tags( $new_instance['max_items'] );

		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'title'=> 'Course Reviews','style' => 'single','orderby'=>'name','order'=>'ASC','category'=>'','course'=>'','ids'=>'', 'max_items' => 5 );

		$instance = wp_parse_args( (array) $instance, $defaults );
		extract( $instance, EXTR_SKIP );

		?>
		<p><label for="bp-course-reviews-widget-title"><?php _e( 'Widget Title', 'vibe' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-course-reviews-widget-course"><?php _e( 'Course ID (optional)', 'vibe' ); ?> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'course' ); ?>" name="<?php echo $this->get_field_name( 'course' ); ?>" type="text" value="<?php echo esc_attr( $course ); ?>" style="width: 30%" /></label>
		</p>
		<p><label for="bp-course-reviews-widget-style"><?php _e( 'Style', 'vibe' ); ?> 
		<select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">
			<option value="single" <?php selected('single',esc_attr( $style )); ?>><?php _e('Single','vibe'); ?></option>
			<option value="list" <?php selected('list',esc_attr( $style )); ?>><?php _e('List','vibe'); ?></option>
			<option value="carousel" <?php selected('carousel',esc_attr( $style )); ?>><?php _e('Carousel','vibe'); ?></option>
		</select>
		</p>
		<p><label for="bp-course-reviews-widget-orderby"><?php _e( 'Order By', 'vibe' ); ?> 
		<select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
			<option value="comment_date_gmt" <?php selected('comment_date_gmt',$orderby); ?>><?php _e('Recent','vibe'); ?></option>
			<option value="rand" <?php selected('rand',$orderby); ?>><?php _e('Random','vibe'); ?></option>
			<option value="review_rating" <?php selected('review_rating',$orderby ); ?>><?php _e('Rating','vibe'); ?></option>
		</select>
		</p>
		<p><label for="bp-course-reviews-widget-order"><?php _e( 'Sort ', 'vibe' ); ?> 
		<select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
			<option value="ASC" <?php selected('ASC',esc_attr( $order )); ?>><?php _e('Ascending','vibe'); ?></option>
			<option value="DESC" <?php selected('DESC',esc_attr( $order )); ?>><?php _e('Decending','vibe'); ?></option>
		</select>
		</p>
		<p><label for="bp-course-reviews-widget-ids"><?php _e( 'Specific Reviews/Comments (enter comma saperated ids)', 'vibe' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'ids' ); ?>" name="<?php echo $this->get_field_name( 'ids' ); ?>" type="text" value="<?php echo esc_attr( $ids ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-course-reviews-widget-max"><?php _e( 'Number of Reviews to show', 'vibe' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_items' ); ?>" name="<?php echo $this->get_field_name( 'max_items' ); ?>" type="text" value="<?php echo esc_attr( $max_items ); ?>" style="width: 30%" /></label></p>
	<?php
	
	}
}

class BP_Course_Related_Courses_Widget extends WP_Widget {

	function __construct() {
	  $widget_ops = array( 'classname' => 'buddypress-related-course-widget', 'description' => 'Displays related courses for Course.' );
	  $control_ops = array( 'width' => 250, 'height' => 350,'id_base' => 'bp_course_related_courses_widget');
	  parent::__construct( 'bp_course_related_courses_widget',  __('BuddyPress Related Courses Widget','vibe'), $widget_ops, $control_ops);
	  }

	function widget( $args, $instance ) {
		global $bp,$wpdb;
		extract( $args );
		extract( $instance, EXTR_SKIP );
		echo $before_widget;
		if(isset($title) && $title !='')
		echo $before_title .
		     $title .
		     $after_title; 

		     global $post;
		     if($post->post_type == 'course')
		     	$post_not_in = $post->ID;

		    echo '<div class="widget_carousel flexslider  no-ajax"><ul class="slides">';

		    if(isset($course) && $course !='' && strlen($course) > 1){
		     	$course_ids = explode(',',$course);
		     	$the_query = new WP_Query(array( 'ID' => $course_ids ) );
		    }else{
		    	global $post;
		    	$args = array('post_type' => 'course','post__not_in'=>array($post->ID),'posts_per_page'=>$number);

		    	if($category){
		    		if(is_singular('course')){
		    			global $post;
		    			$terms_list = wp_get_post_terms($post->ID, 'course-cat', array("fields" => "all"));

		    			if(isset($terms_list) && is_array($terms_list)){
		    				$args['tax_query'] = array('relation'=> 'AND' );
		    				$terms_slug_array = array();
		    				foreach($terms_list as $term){
		    					$terms_slug_array[]=$term->slug;
		    				}
		    				$args['tax_query'][]= array(
		    						'taxonomy' => 'course-cat',
									'field'    => 'slug',
									'terms'    =>$terms_slug_array,
		    						);
		    			}
		    		}
		    	}
		    	if($instructor){
		    		global $post;
		    		if(is_singular('course')){
		    			$instructor_ids = apply_filters('wplms_course_instructor',get_post_field('post_author',$post->ID),$post->ID);
		    			if(is_numeric($instructor_ids)){
		    				$instructor_ids = array($instructor_ids);
		    			}
		    			$args['author__in'] = $instructor_ids;
		    		}
		    	}
		    	if($location){
		    		if(is_singular('course')){
		    			$terms_list = wp_get_post_terms(get_the_ID(), 'location', array("fields" => "all"));
		    			if(isset($terms_list) && is_array($terms_list)){
		    				if(!isset($args['tax_query']['relation']))
		    					$args['tax_query']=array('relation'=> 'AND' );
		    				$terms_slug_array = array();
		    				foreach($terms_list as $term){
		    					$terms_slug_array[]=$term->slug;
		    				}
		    				$args['tax_query'][]= array(
		    						'taxonomy' => 'location',
									'field'    => 'slug',
									'terms'    =>$terms_slug_array,
		    						);
		    			}
		    		}
		    	}
		    	if($level){
		    		if(is_singular('course')){
		    			$terms_list = wp_get_post_terms(get_the_ID(), 'level', array("fields" => "all"));
		    			if(isset($terms_list) && is_array($terms_list)){
		    				if(!isset($args['tax_query']['relation']))
		    					$args['tax_query']=array('relation'=> 'AND' );
		    				$terms_slug_array = array();
		    				foreach($terms_list as $term){
		    					$terms_slug_array[]=$term->slug;
		    				}
		    				$args['tax_query'][]= array(
		    						'taxonomy' => 'level',
									'field'    => 'slug',
									'terms'    =>$terms_slug_array,
		    						);
		    			}
		    		}
		    	}
		    	if($linkage){
		    		if(is_singular('course')){
		    			$terms_list = wp_get_post_terms(get_the_ID(), 'linkage', array("fields" => "all"));
		    			if(isset($terms_list) && is_array($terms_list)){
		    				if(!isset($args['tax_query']['relation']))
		    					$args['tax_query']= array('relation'=> 'AND' );
		    				$terms_slug_array = array();
		    				foreach($terms_list as $term){
		    					$terms_slug_array[]=$term->slug;
		    				}
		    				$args['tax_query'][]= array(
		    						'taxonomy' => 'linkage',
									'field'    => 'slug',
									'terms'    =>$terms_slug_array,
		    						);
		    			}
		    		}
		    	}
		    	if(!empty($post_not_in)){
		    		$args['post__not_in'] = array($post_not_in);
		    	}
		    	$the_query = new WP_Query($args);
		    }
		    if($the_query->have_posts()){
			    while($the_query->have_posts()):$the_query->the_post();
			    echo '<li>';
			    if(empty($style)){$style='course2';}
			    echo thumbnail_generator($the_query->post,$style,3,0,0,0);
	            echo '</li>';
	            endwhile;
	        }else{
	        	echo '<div class="message">'.__('No related courses found !','vibe').'</div>';
	        }
            wp_reset_postdata();
            echo '</ul></div>';
	 echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['category'] = strip_tags( $new_instance['category'] );
		$instance['instructor'] = strip_tags( $new_instance['instructor'] );
		$instance['location'] = strip_tags( $new_instance['location'] );
		$instance['level'] = strip_tags( $new_instance['level'] );
		$instance['linkage'] = strip_tags( $new_instance['linkage'] );
		$instance['number'] = strip_tags( $new_instance['number'] );
		$instance['course'] = strip_tags( $new_instance['course'] );
		$instance['style'] = strip_tags( $new_instance['style'] );

		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 
			'title'=> 'Related Courses Widget',
			'category' => 0,
			'instructor' => 0,
			'location' => 0,
			'level' => 0,
			'linkage' => 0,
			'number' => 3,
			'course' => '',
			'style'=>'course2'
			 );

		$instance = wp_parse_args( (array) $instance, $defaults );
		extract( $instance, EXTR_SKIP );
		$title = esc_attr($instance['title']);
		$category = esc_attr($instance['category']);
		$instructor = esc_attr($instance['instructor']);
		$location = esc_attr($instance['location']);
		$level = esc_attr($instance['level']);
		$linkage = esc_attr($instance['linkage']);
		$number = esc_attr($instance['number']);
		$course = esc_attr($instance['course']);
		$style = esc_attr($instance['course2']);
		?>
		<p><label for="bp-course-related-courses-widget-title"><?php _e( 'Widget Title', 'vibe' ); ?> <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-course-related-courses-category"><?php _e( 'Show from same Course Category', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" type="checkbox" value="1"  <?php checked($category,1,true) ?>/></label></p>
		<p><label for="bp-courserelated-courses-instructor"><?php _e( 'Show from same Instructor', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'instructor' ); ?>" name="<?php echo $this->get_field_name( 'instructor' ); ?>" type="checkbox" value="1"  <?php checked($instructor,1,true) ?>/></label></p>
		<?php
			if(vibe_get_option('location')){
		?>
		<p><label for="bp-course-related-courses-location"><?php _e( 'Show from same Course location', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'location' ); ?>" name="<?php echo $this->get_field_name( 'location' ); ?>" type="checkbox" value="1"  <?php checked($location,1,true) ?>/></label></p>
		<?php
		}
		if(vibe_get_option('level')){
		?>
		<p><label for="bp-course-related-courses-level"><?php _e( 'Show from same Course Level', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'level' ); ?>" name="<?php echo $this->get_field_name( 'level' ); ?>" type="checkbox" value="1"  <?php checked($level,1,true) ?>/></label></p>
		<?php
		}
		if(vibe_get_option('linkage')){
		?>
		<p><label for="bp-courserelated-courses-linkage"><?php _e( 'Show from same Linkage', 'vibe' ); ?> <input class="checkbox" id="<?php echo $this->get_field_id( 'linkage' ); ?>" name="<?php echo $this->get_field_name( 'linkage' ); ?>" type="checkbox" value="1"  <?php checked($instructor,1,true) ?>/></label></p>
		<?php
		}
		?>
		<p><label for="bp-course-related-courses-widget-number"><?php _e( 'Numer of Courses', 'vibe' ); ?> <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" style="width: 30%" /></label></p>
		<p><label for="bp-course-related-courses-widget-style"><?php _e( 'Block Style', 'vibe' ); ?> </label>
		<select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">
		<?php
			$v_thumb_styles = apply_filters('vibe_builder_thumb_styles',array(
                            'course'=> plugins_url('images/thumb_2.png',__FILE__),
                            'course2'=> plugins_url('images/thumb_8.png',__FILE__),
                            'side'=> plugins_url('images/thumb_3.png',__FILE__),
                            'blogpost'=> plugins_url('images/thumb_6.png',__FILE__),
                            'images_only'=> plugins_url('images/thumb_4.png',__FILE__),
                                ));
			foreach($v_thumb_styles as $thumb_style => $val){
				echo '<option value="'.$thumb_style.'" '.(($thumb_style == $style)?'selected':'').'>'.$thumb_style.'</option>';
			}
		?>	
		</select>
		</p>
		<p><label for="bp-course-related-courses-widget-course"><?php _e( 'Comma saperated courses', 'vibe' ); ?> <input id="<?php echo $this->get_field_id( 'course' ); ?>" name="<?php echo $this->get_field_name( 'course' ); ?>" type="text" value="<?php echo esc_attr( $course ); ?>" style="width: 30%" /></label></p>
		<?php
	}
}
