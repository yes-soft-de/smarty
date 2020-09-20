<?php

if ( !defined( 'ABSPATH' ) ) exit;

add_action( 'widgets_init', 'vibe_bp_widgets' );


function vibe_bp_widgets() {
    register_widget('vibe_bp_login');
    register_widget('vibe_course_categories'); 
    register_widget('vibecertificatecode'); 
    register_widget('vibe_course_categories_nav');
}


/* Creates the widget itself */

if ( !class_exists('vibe_bp_login') ) {
	class vibe_bp_login extends WP_Widget {
	
		function __construct() {
			$widget_ops = array( 'classname' => 'vibe-bp-login', 'description' => __( 'Vibe BuddyPress Login', 'vibe' ) );
			parent::__construct( 'vibe_bp_login', __( 'Vibe BuddyPress Login Widget','vibe' ), $widget_ops);
		}
		
		function widget( $args, $instance ) {
			extract( $args );
			
			echo $before_widget;
			
			if ( is_user_logged_in() ) :
				do_action( 'bp_before_sidebar_me' ); ?>
				<div id="sidebar-me">
					<div id="bpavatar">
						<?php bp_loggedin_user_avatar( 'type=full' ); ?>
					</div>
					<ul>
						<li id="username"><a href="<?php bp_loggedin_user_link(); ?>"><?php bp_loggedin_user_fullname(); ?></a></li>
						<li><a href="<?php echo bp_loggedin_user_domain() . BP_XPROFILE_SLUG ?>/" title="<?php _e('View profile','vibe'); ?>"><?php _e('View profile','vibe'); ?></a></li>
						<li id="vbplogout"><a href="<?php echo wp_logout_url( get_permalink() ); ?>" id="destroy-sessions" rel="nofollow" class="logout" title="<?php _e( 'Log Out','vibe' ); ?>"><i class="icon-close-off-2"></i> <?php _e('LOGOUT','vibe'); ?></a></li>
						<li id="admin_panel_icon"><?php if (current_user_can("edit_posts"))
					       echo '<a href="'.vibe_site_url() .'wp-admin/" title="'.__('Access admin panel','vibe').'"><i class="icon-settings-1"></i></a>'; ?>
					  </li>
					</ul>	
					<ul>
            <?php
            $nav = '';
            if(function_exists('bp_course_get_nav_permalinks'))
              $nav = bp_course_get_nav_permalinks();
            $loggedin_menu = array(
              'courses'=>array(
                          'icon' => 'icon-book-open-1',
                          'label' => __('Courses','vibe'),
                          'link' => bp_loggedin_user_domain().BP_COURSE_SLUG
                          ),
              'stats'=>array(
                          'icon' => 'icon-analytics-chart-graph',
                          'label' => __('Stats','vibe'),
                          'link' => bp_loggedin_user_domain().BP_COURSE_SLUG.'/'. BP_COURSE_STATS_SLUG
                          )
              );
            if ( bp_is_active( 'messages' ) ){
              $loggedin_menu['messages']=array(
                          'icon' => 'icon-letter-mail-1',
                          'label' => __('Inbox','vibe').(messages_get_unread_count()?' <span>' . messages_get_unread_count() . '</span>':''),
                          'link' => bp_loggedin_user_domain().BP_MESSAGES_SLUG
                          );
              $n=bp_notifications_get_unread_notification_count( bp_loggedin_user_id() );
              $loggedin_menu['notifications']=array(
                          'icon' => 'icon-exclamation',
                          'label' => __('Notifications','vibe').(($n)?' <span>'.$n.'</span>':''),
                          'link' => bp_loggedin_user_domain().BP_NOTIFICATIONS_SLUG
                          );
            }
            if ( bp_is_active( 'groups' ) ){
              $loggedin_menu['groups']=array(
                          'icon' => 'icon-myspace-alt',
                          'label' => __('Groups','vibe'),
                          'link' => bp_loggedin_user_domain().BP_GROUPS_SLUG 
                          );
            }
            
            $loggedin_menu['settings']=array(
                          'icon' => 'icon-settings',
                          'label' => __('Settings','vibe'),
                          'link' => bp_loggedin_user_domain().BP_SETTINGS_SLUG
                          );
            $loggedin_menu = apply_filters('wplms_logged_in_top_menu',$loggedin_menu);
            foreach($loggedin_menu as $item){
              echo '<li><a href="'.$item['link'].'"><i class="'.$item['icon'].'"></i>'.$item['label'].'</a></li>';
            }
            ?>
					</ul>
				
				<?php
				do_action( 'bp_sidebar_me' ); ?>
				</div>
				<?php do_action( 'bp_after_sidebar_me' );
			
			/***** If the user is not logged in, show the log form and account creation link *****/
			
			else :
				if(!isset($user_login))$user_login='';
				do_action( 'bp_before_sidebar_login_form' ); ?>
				
				
				<form name="login-form" id="vbp-login-form" class="standard-form" action="<?php echo apply_filters('wplms_login_widget_action',vibe_site_url( 'wp-login.php', 'login-post' )); ?>" method="post">
					<label><?php _e( 'Username', 'vibe' ); ?><br />
					<input type="text" name="log" id="side-user-login" class="input" tabindex="1" value="<?php echo esc_attr( stripslashes( $user_login ) ); ?>" /></label>
					
					<label><?php _e( 'Password', 'vibe' ); ?> <a href="<?php echo wp_lostpassword_url( get_permalink() ); ?>" tabindex="5" class="tip" title="<?php _e('Forgot Password','vibe'); ?>"><i class="icon-question"></i></a><br />
					<input type="password" tabindex="2" name="pwd" id="sidebar-user-pass" class="input" value="" /></label>
					
          <div class="checkbox small">
            <input type="checkbox" name="sidebar-rememberme" id="sidebar-rememberme" value="forever" /><label for="sidebar-rememberme"><?php _e( 'Remember Me', 'vibe' ); ?></label>
          </div>
					
					<?php do_action( 'bp_sidebar_login_form' ); ?>
					<input type="submit" name="user-submit" id="sidebar-wp-submit" data-security="<?php echo wp_create_nonce('wplms_signon'); ?>" value="<?php _e( 'Log In','vibe' ); ?>" tabindex="100" />
					<input type="hidden" name="user-cookie" value="1" />
					<?php if ( bp_get_signup_allowed() ) :
            $registration_link = apply_filters('wplms_buddypress_registration_link',site_url( BP_REGISTER_SLUG . '/' ));
						printf( __( '<a href="%s" class="vbpregister" title="'.__('Create an account','vibe').'" tabindex="5" >'.__( 'Sign Up','vibe' ).'</a> ', 'vibe' ), $registration_link );
					endif; ?>
          <?php do_action( 'login_form' ); //BruteProtect FIX ?>
				</form>
				
				
				<?php do_action( 'bp_after_sidebar_login_form' );
			endif;
			
			echo $after_widget;
		}
		
		/* Updates the widget */
		
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			return $instance;
		}
		
		/* Creates the widget options form */
		
		function form( $instance ) {
			
		}
	
	} 
} 



          
/*======= Vibe Course Categories ======== */  

class vibe_course_categories extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'Course Categories', 'description' => __('Course Categories ', 'vibe') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'vibe_course_categories' );
    parent::__construct( 'vibe_course_categories', __('Course Categories', 'vibe'), $widget_ops, $control_ops );
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
    $exclude_terms = (isset($instance['exclude_terms'])?esc_attr($instance['exclude_terms']):'');
	  $sort = esc_attr($instance['sort']);
	  $order = esc_attr($instance['order']); 
    
    echo $before_widget;

    // Display the widget title 
    if ( $title )
    echo $before_title . $title . $after_title;
    

    $args = apply_filters('wplms_course_filters_course_cat',array(
        'title_li'=>'',
        'taxonomy'=>'course-cat',
    		'orderby'    => $order,
		 	  'order' => $sort
    	));

    if(!empty($exclude_terms)){
      $exclude_terms= explode(',',$exclude_terms);
      foreach($exclude_terms as $k=>$term){
         if(!is_numeric($term)){
            $term =  get_term_by( 'slug', $term, 'course-cat');
            $exclude_terms[$k] = $term->term_id;
         }
      }
    }
    	
    $args['exclude'] = $exclude_terms;

    echo '<ul class="'.$order.' '.(empty($instance['hierarchial'])?'':'hierarchial').'">';


    if($order == 'hierarchial' || !empty($instance['hierarchial'])){ 
      $args['hierarchial']=1;
      $args['orderby']= 'name';
    }
  	echo wp_list_categories($args);
    echo '</ul>';
    echo $after_widget;
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['exclude_terms'] = $new_instance['exclude_terms'];
    $instance['sort'] = $new_instance['sort'];
    $instance['order'] = $new_instance['order'];
    $instance['hierarchial'] = $new_instance['hierarchial'];
    
    return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                    'title'  => __('Course Categories','vibe'),
                    'exclude_ids'  => '',
                    'hierarchial'=>0,
                    'sort'  => 'DESC',
                    'order' => ''
                    );
  		
  		$instance = wp_parse_args( (array) $instance, $defaults );
      $title  = esc_attr($instance['title']);
      $hierarchial = esc_attr($instance['hierarchial']);
      $exclude_terms = esc_attr($instance['exclude_terms']);
		  $sort = esc_attr($instance['sort']);
		  $order = esc_attr($instance['order']);                               
        ?>
         
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
  		<p>
          <label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order by','vibe'); ?></label> 
           <select class="select" name="<?php echo $this->get_field_name('order'); ?>">
           		<option value="name" <?php selected('name',$order); ?>><?php _e('Name','vibe'); ?></option>
           		<option value="slug" <?php selected('slug',$order); ?>><?php _e('Slug','vibe'); ?></option>
           		<option value="count" <?php selected('count',$order); ?>><?php _e('Course Count','vibe'); ?></option>
            </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('sort'); ?>"><?php _e('Sort Order ','vibe'); ?></label> 
           <select class="select" name="<?php echo $this->get_field_name('sort'); ?>">
           		<option value="ASC" <?php selected('ASC',$sort); ?>><?php _e('Ascending','vibe'); ?></option>
           		<option value="DESC" <?php selected('DESC',$sort); ?>><?php _e('Descending','vibe'); ?></option>
            </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('hierarchial'); ?>">
          <input type="checkbox" id="<?php echo $this->get_field_id('hierarchial'); ?>" name="<?php echo $this->get_field_name('hierarchial'); ?>" value="1" <?php checked($hierarchial,1); ?>/><?php _e('hierarchial ','vibe'); ?></label> 
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('exclude_terms'); ?>"><?php _e('Exclude Course Category Terms slugs (comma saperated):','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('exclude_terms'); ?>" name="<?php echo $this->get_field_name('exclude_terms'); ?>" type="text" value="<?php echo $exclude_terms; ?>" />
        </p>
        
        <?php 
        wp_reset_query();
        wp_reset_postdata();
    }
}

  
/*======= Vibe Gallery ======== */    

 class vibecertificatecode extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'vibecertificatecode', 'description' => __('Vibe Certificate Code validator', 'vibe') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'vibecertificatecode' );
    parent::__construct( 'vibecertificatecode', __('Vibe Certificate Code validator', 'vibe'), $widget_ops, $control_ops );
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = apply_filters('widget_title', $instance['title'] );
                
    
    echo $before_widget;
    echo '<div class="certificate_code_validator">';
    // Display the widget title 
    if ( $title )
	    echo $before_title . $title . $after_title;
		$certificate_page = vibe_get_option('certificate_page');
		echo '<form action="'.get_permalink($certificate_page).'" method="get">';
		echo '<input type="text" class="form_field" name="certificate_code" placeholder="'.__('Enter Certificate Code','vibe').'" />';
		echo '<input type="submit" class="button primary small" value="'.__('Validate','vibe').'" />';
		echo '</form>
			  </div>';
	    echo $after_widget;

    }
 	
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
	    $instance = $old_instance;
	    $instance['title'] = strip_tags($new_instance['title']);
	    return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                    'title'  => 'Certificate Code',
                    );
  		$instance = wp_parse_args( (array) $instance, $defaults );
                
        $title  = esc_attr($instance['title']);                           
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php 
    }
}   



/*======= Vibe Course Categories ======== */  

class vibe_course_categories_nav extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function __construct() {
    $widget_ops = array( 'classname' => 'Course Categories Navigation', 'description' => __('Course Categories Navigation', 'vibe') );
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'vibe_course_categories_nav' );
    parent::__construct( 'vibe_course_categories_nav', __('Course Categories Navigation', 'vibe'), $widget_ops, $control_ops );
  }
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
    extract( $args );

    //Our variables from the widget settings.
    $title = (isset($instance['title'])?apply_filters('widget_title', $instance['title'] ):'');
    $exclude_terms = (isset($instance['exclude_terms'])?esc_attr($instance['exclude_terms']):'');
    $sort = (isset($instance['sort'])?esc_attr($instance['sort']):'');
    $order = (isset($instance['order'])?esc_attr($instance['order']):''); 
    
    echo $before_widget;

    

    $args = apply_filters('wplms_course_filters_course_cat',array(
        'title_li'=>'',
        'taxonomy'=>'course-cat',
        'orderby'    => $order,
        'order' => $sort,
        'hierarchial'=>1,
         'walker' => new Vibe_Course_Nav_Walker(),
      ));

    if(!empty($exclude_terms)){
      $exclude_terms= explode(',',$exclude_terms);
      foreach($exclude_terms as $k=>$term){
         if(!is_numeric($term)){
            $term =  get_term_by( 'slug', $term, 'course-cat');
            $exclude_terms[$k] = $term->term_id;
         }
      }
    }
      
    $args['exclude'] = $exclude_terms;
    $pages = get_option('bp-pages');
    $course_dir = isset($pages['course'])?$pages['course']:0;
    if(function_exists('icl_object_id')){
      $course_dir = icl_object_id($course_dir, 'page', true);
    }
    echo '<div class="course_cat_nav"><ul class=" '.$order.'">';

    echo '<li class="'.((bp_current_component() == 'course' && is_page())?'current-cat':'').'"><a href="'.get_permalink($course_dir).'">'.get_the_title($course_dir).'</a></li>';
    echo wp_list_categories($args);
    echo '</ul></div>';
    echo $after_widget;
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['exclude_terms'] = $new_instance['exclude_terms'];
    $instance['sort'] = $new_instance['sort'];
    $instance['order'] = $new_instance['order'];
    $instance['hierarchial'] = $new_instance['hierarchial'];
    
    return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
        $defaults = array( 
                    'title'  => __('Course Categories','vibe'),
                    'exclude_ids'  => '',
                    'hierarchial'=>0,
                    'sort'  => 'DESC',
                    'order' => ''
                    );
      
      $instance = wp_parse_args( (array) $instance, $defaults );
      $title  = esc_attr($instance['title']);
      $hierarchial = esc_attr($instance['hierarchial']);
      $exclude_terms = esc_attr($instance['exclude_terms']);
      $sort = esc_attr($instance['sort']);
      $order = esc_attr($instance['order']);                               
        ?>
         
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
      <p>
          <label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order by','vibe'); ?></label> 
           <select class="select" name="<?php echo $this->get_field_name('order'); ?>">
              <option value="" <?php selected('',$order); ?>><?php _e('Default','vibe'); ?></option>
              <option value="name" <?php selected('name',$order); ?>><?php _e('Name','vibe'); ?></option>
              <option value="slug" <?php selected('slug',$order); ?>><?php _e('Slug','vibe'); ?></option>
              <option value="count" <?php selected('count',$order); ?>><?php _e('Course Count','vibe'); ?></option>
            </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('sort'); ?>"><?php _e('Sort Order ','vibe'); ?></label> 
           <select class="select" name="<?php echo $this->get_field_name('sort'); ?>">
              <option value="ASC" <?php selected('ASC',$sort); ?>><?php _e('Ascending','vibe'); ?></option>
              <option value="DESC" <?php selected('DESC',$sort); ?>><?php _e('Descending','vibe'); ?></option>
            </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('exclude_terms'); ?>"><?php _e('Exclude Course Category Terms slugs (comma saperated):','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('exclude_terms'); ?>" name="<?php echo $this->get_field_name('exclude_terms'); ?>" type="text" value="<?php echo $exclude_terms; ?>" />
        </p>
        
        <?php 
        wp_reset_query();
        wp_reset_postdata();
    }
}


class Vibe_Course_Nav_Walker extends Walker_Category {

        function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
                extract($args);
                $cat_name = esc_attr( $category->name );
                $cat_name = apply_filters( 'list_cats', $cat_name, $category );
                $link = '<a href="' . esc_url( get_term_link($category) ) . '" ';
                if ( $use_desc_for_title == 0 || empty($category->description) )
                        $link .= 'title="' . esc_attr( sprintf(__( 'View all posts filed under %s','vibe' ), $cat_name) ) . '"';
                else
                        $link .= 'title="' . esc_attr( strip_tags( apply_filters( 'category_description', $category->description, $category ) ) ) . '"';
                $link .= '>';
                $link .= $cat_name ;
                $link .= '<span>' . intval($category->count) . '</span>';
                $link .= '</a>';                    
                $output .= "\t<li";
                $class = 'cat-item cat-item-' . $category->term_id;


                // YOUR CUSTOM CLASS
                if ($depth)
                    $class .= ' sub-'.sanitize_title_with_dashes($category->name);


                if ( !empty($current_category) ) {
                        $_current_category = get_term( $current_category, $category->taxonomy );
                        if ( $category->term_id == $current_category )
                                $class .=  ' current-cat';
                        elseif ( $category->term_id == $_current_category->parent )
                                $class .=  ' current-cat-parent';
                }
                $output .=  ' class="' . $class . '" data-slug="' . $category->slug . '"';
                $output .= ">$link\n";
                
                
        } // function start_el

} // class Custom_Walker_Category