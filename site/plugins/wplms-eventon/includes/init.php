<?php
/**
 * Initialise EventOn with WPLMS
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	WPLMS-eventon/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Wplms_EventOn_Init{

	public static $instance;
    
    public static function init(){
    	
        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_EventOn_Init();
        return self::$instance;
    }

	private function __construct(){ 
		add_filter('wplms_course_nav_menu',array($this,'wplms_eventon_link'));
		add_action('wplms_load_templates',array($this,'wplms_eventon_page'));

		add_filter('eventon_event_metaboxs',array($this,'wplms_fields'));
		add_filter('eventon_event_metafields',array($this,'save_wplms_fields'));
		add_filter('eventon_wp_query_args',array($this,'wplms_events'),10,3);
		add_action('eventon_calendar_header_content',array($this,'wplms_hidden_course_element'),10,2);
		add_action('eventon_init',array($this,'wplms_instructors_can_create_events'));
		//add_filter('wplms_eventon_style')
		/*===== Permalink Setting === */
        add_action('wplms_course_action_point_permalink_settings',array($this,'permalink_setting'));
        add_filter('wplms_save_vibe_course_permalinks',array($this,'save_permalinks'));
        add_action('init', array($this,'add_endpoints'));
    	add_filter( 'request', array($this,'filter_request' ));
		add_action( 'template_redirect', array($this,'catch_vars' ),9);
		
		/*=== Default Event on ===*/
		add_filter('eventon_shortcode_defaults',array($this,'add_wplms_course'));

		add_filter('lms_general_settings',array($this,'event_display_setting'));
		add_filter('wplms_eventons_course_events_shortcode',array($this,'events_shortcode'));

		add_filter('eventon_shortcode_default_values',array($this,'add_wplms_course_default'));
	}


	function add_wplms_course($args){
		global $bp;
		if(bp_current_action() == 'events'){
			$args['wplms_course'] = 1;	
		}else{
			$args['wplms_course'] = 0;	
		}
		return $args;
	}

	function wplms_eventon_link($nav_menu){
		$permalinks = get_option( 'vibe_course_permalinks' );
        $events_slug = ($permalinks['events_slug'])?$permalinks['events_slug']:'events';
    	$nav_menu['events'] = array(
                    'id' => 'events',
                    'label'=>__('Events ','wplms-eventon'),
                    'action' => $events_slug,
                    'link'=>bp_get_course_permalink(),
                );
    	return $nav_menu;
    }

    function wplms_eventon_page(){
    	if(isset($_GET['action']) && $_GET['action'] == 'events'){
    		
    		$shortcode = apply_filters('wplms_eventons_course_events_shortcode','[add_eventon_dv cal_id="'.rand(0,999).'" today="no" show_et_ft_img="no" ft_event_priority="no" wplms_course="1"]');
    		echo do_shortcode($shortcode);
    	}
    }

    function eventon_calendar(){
    	$shortcode = apply_filters('wplms_eventons_course_events_shortcode','[add_eventon_dv cal_id="'.rand(0,999).'" today="no" show_et_ft_img="no" ft_event_priority="no" wplms_course="1"]');
    	echo do_shortcode($shortcode);
    }

    function add_wplms_course_default($args){
    	$args['wplms_course'] = '';
    	return $args;
    }

    function wplms_events($args,$filters,$ecv = null){
    	global $post,$bp;

    	if(empty($ecv)){
    		return $args;
    	}
    	
    	$wplms_course = $ecv['wplms_course'];
    	
    	if (defined('DOING_AJAX') && DOING_AJAX) {
    		$evodata = $_POST['evodata'];
    		$wplms_course = $evodata['course'];
    	}

    	if(!is_numeric($wplms_course)){
    		if(strpos($wplms_course, ',') !== false){
    			$course_ids = explode(',',$wplms_course);
    			foreach($course_ids as $key=>$course_id){
    				if(get_post_type($course_id) != 'course'){
    					unset($course_ids[$key]);
    				}
    			}
    		}else{
    			return $args;
    		}
    	}else if($wplms_course > 1 && get_post_type($wplms_course) == 'course'){
    		$course_ids = array($wplms_course);
    	}else if($wplms_course == 1){
    		if($post->post_type == 'course'){$course_ids = array($post->ID);}
			if(empty($course_id) && get_post_type($bp->current_item) == 'course'){$course_ids = array($bp->current_item);}			

    	}else{
    		return $args;
    	}
	    
    	if( !empty($course_ids)) {
    		
    		$args['meta_query'][]=array(
			    			'key' => 'wplms_ev_course',
			    			'value'=> $course_ids,
			    			'compare'=> 'IN',
		    			);
			$args['meta_query']['relation'] = 'OR';
    		$args['meta_query'][]=array(
    			'key' => 'wplms_ev_course',
    			'compare'=> 'NOT EXISTS',
    		);

    		$args = apply_filters('wplms_eventon_args',$args,$course_ids);

    	}else{
    		$args['meta_query'][]=array(
    			'key' => 'wplms_ev_course',
    			'compare'=> 'NOT EXISTS',
    		);
    	}


    	return $args;
    }


    function wplms_hidden_course_element($content,$ecv  = null){
    	if(!empty($ecv['wplms_course'])){
    		if($ecv['wplms_course'] == 1){
    			echo '<span class="evo-data" data-course="'.get_the_ID().'"></span>';
    		}else{
    			echo '<span class="evo-data" data-course="'.$ecv['wplms_course'].'"></span>';
    		}
    	}
    	//
    }
	function wplms_fields($metabox){

		$metabox[]=array(
				'id'=>'wplms_ev_course',
				'name'=>__('WPLMS Courses','wplms-eventon'),
				'variation'=>'customfield',		
				'iconURL'=>'fa-book',
				'iconPOS'=>'',
				'type'=>'code',
				'content'=> self::get_course_list(),
				'slug'=>'ev_subtitle'
			);
		return $metabox;
	}

	function save_wplms_fields($fields){
		$fields[] = 'wplms_ev_course';
		return $fields;
	}

	function get_course_list(){
		// HTML - User Interaction
		ob_start();
		?>
			<div class='evcal_data_block_style1'>
				<p class='edb_icon evcal_edb_map'></p>
				<div class='evcal_db_data'>			
					<p>
					<?php
						// organier terms for event post
						$args = apply_filters('wplms_backend_cpt_query',array(
							'post_type' => 'course',
							'posts_per_page'=>-1,
							'orderby'=>'alphabetical',
							'order'=>'ASC',
							));
						unset($args['tax_query']); // remove linkage if any

						$wplms_courses = get_posts($args);
						global $post;
						$wplms_ev_course=get_post_meta($post->ID,'wplms_ev_course',true);
						if(count($wplms_courses) > 0){
							echo "<select id='wplms_ev_course' name='wplms_ev_course' class='chosen'>
								<option value=''>".__('Select a WPLMS Course','wplms-eventon')."</option>";
						    foreach ( $wplms_courses as $wplms_course ) {
						       	echo "<option value='". $wplms_course->ID ."' ".( ($wplms_ev_course == $wplms_course->ID )?"selected='selected'":"" ).">" . $wplms_course->post_title . "</option>";						        
						    }
						    echo "</select> <label for='evcal_organizer_field'>".__('Choose from published courses','wplms-eventon')."</label>";
						}

					
					?>
					<p style='clear:both'></p>
				</div>
			</div>
		<?php
		$_html = ob_get_clean();
		return $_html; 
	}

	function wplms_instructors_can_create_events(){

	   global $wp_roles;
	   
	   if ( class_exists('WP_Roles') )
	       if ( ! isset( $wp_roles ) )
	           $wp_roles = new WP_Roles();
	   
	   if(function_exists('eventon_get_core_capabilities')){
	       $capabilities = eventon_get_core_capabilities();
	       unset($capabilities['core']);
	       foreach( $capabilities as $cap_group ) {
	           foreach( $cap_group as $cap ) {
	               $wp_roles->add_cap( 'instructor', $cap );
	           }
	       }
	       $wp_roles->remove_cap( 'instructor','manage_eventon' );
	   }
	}

    function permalink_setting(){
        $permalinks = get_option( 'vibe_course_permalinks' );
        $events_slug = (!empty($permalinks['events_slug'])?$permalinks['events_slug']:'events');
        ?>
        <tr>
            <th><label><?php _e('Events','wplms-front-end'); ?></label></th>
            <td>
                <input name="events_slug" type="text" value="<?php echo esc_attr( $events_slug ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Course Events slug', 'wplms-eventon' ); ?></span>
            </td>
        </tr>
        <?php
    }

    function save_permalinks($permalinks){
        
        if(!empty($_POST['events_slug'])){
            $events_slug = trim( sanitize_text_field( $_POST['events_slug'] ), '/' );
            $events_slug = '/' . $events_slug;
            $permalinks['events_slug'] = untrailingslashit( $events_slug );
        }
        return $permalinks;
    }

    function add_endpoints(){
    	if(empty($this->permalinks))
    		$this->permalinks = get_option( 'vibe_course_permalinks' );
        
        $events_slug = isset($this->permalinks['events_slug'])?$this->permalinks['events_slug']:'events';
        $events_slug = str_replace('/','',$events_slug);
        add_rewrite_endpoint($events_slug, EP_ALL);    
    }

    function filter_request( $vars ){

    	if(empty($this->permalinks))
			$this->permalinks = get_option( 'vibe_course_permalinks' );

		$events_slug = isset($this->permalinks['events_slug'])?$this->permalinks['events_slug']:'events';
		$events_slug = str_replace('/','',$events_slug);
		if(isset( $vars[$events_slug])){
			$vars[$events_slug] = true;	
		}
	    return $vars;
	}


	function catch_vars(){ 
		global $bp,$wp_query;	
		
		if(empty($this->permalinks))
			$this->permalinks = get_option( 'vibe_course_permalinks' );


		if($bp->unfiltered_uri[0] == trim($this->permalinks['course_base'],'/') || $bp->unfiltered_uri[0] == BP_COURSE_SLUG){
				
        		$events_slug = (!empty($permalinks['events_slug'])?$permalinks['events_slug']:'events');
				$events_slug = str_replace('/','',$events_slug);
				
			    if( get_query_var( $events_slug )){ 
			    	$bp->current_component = BP_COURSE_SLUG;
			    	$bp->current_item = get_The_ID();
			        $bp->current_action = 'events';

			        add_action('bp_course_plugin_template_content',array($this,'eventon_calendar'));
					bp_get_template_part('course/single/plugins');
					exit;
			    }
		}
	}

	function events_title(){
		return sprintf(__('Events in Course %s','wplms-eventon'),get_the_title());
	}

	function event_display_setting($settings){
		$settings[]=array(
					'label'=>__('WPLMS Course Events Settings','wplms-eventon' ),
					'type'=> 'heading',
				);
		$settings[] = array(
						'label' => __('Course Events calendar display','wplms-eventon'),
						'name' =>'event_calendar_display',
						'class' => 'hide',
						'type' => 'select',
						'options'=>apply_filters('wplms_event_calendar_display',array(
							'' => __('Default, Daily Calendar','wplms-eventon'),
							'list' => __('List Calendar','wplms-eventon'),
							)),
						'desc' => __('Course - Events display','wplms-eventon') 
					);
		return $settings;
	}

	function events_shortcode($shortcode){
		if(class_exists('WPLMS_tips')){
			$tips = WPLMS_tips::init();
			if(isset($tips->settings['event_calendar_display'])){
				switch($tips->settings['event_calendar_display']){
					case 'list':
						$shortcode = '[add_eventon wplms_course="1" hide_empty_months="yes"]';
					break;
				}
			}
		}
		return $shortcode;
	}
}

Wplms_EventOn_Init::init();