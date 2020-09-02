<?php

if(!class_exists('Wplms_Vc'))
{   
    class Wplms_Vc  // We'll use this just to avoid function name conflicts 
    {
        

        public static $instance;
    
        public static function init(){
            if ( is_null( self::$instance ) )
                self::$instance = new Wplms_Vc;
            return self::$instance;
        }
       


       
        function __construct(){
        	

		    if ( in_array( 'js_composer/js_composer.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'js_composer/js_composer.php'))){

				$this->v_widget_areas = $this->v_post_types =array();
	            
	            global $wp_registered_sidebars;
				foreach($wp_registered_sidebars as $sidebar){
					$this->v_widget_areas[$sidebar['id']]=$sidebar['id'];
				};
			              
			    $this->v_post_types = array(
			    	__('Select Post Type','vibe-customtypes') => '',
			    	__('Post','vibe-customtypes') => 'post',
			    	__('Page','vibe-customtypes') => 'page',
			    	__('Courses','vibe-customtypes') => 'course',
			    	__('Quiz','vibe-customtypes') => 'quiz',
			    	__('Assignments','vibe-customtypes') => 'wplms-assignments',
			    	__('Certificates','vibe-customtypes') => 'certificate',
			    	__('Testimonials','vibe-customtypes') => 'testimonials',
			    	__('Events','vibe-customtypes') => 'ajde-event',
			    	__('Course News','vibe-customtypes') => 'news',
			    );

			    $this->taxonomy=array(
			    	'course'=>array(
			    		'course-cat'=>__('Course Category','vibe-customtypes'),
			    		'location'=>__('Course Location','vibe-customtypes'),
			    		'level'=>__('Course Level','vibe-customtypes'),
			    	),
			    	'quiz'=>array(
			    		'quiz-type'=>__('Quiz Type','vibe-customtypes'),
			    	),
			    	'wplms-assignments'=>array(
			    		'assignment-type'=>__('Assignment Type','vibe-customtypes'),
			    	)
			    );

			   

			     //Get List of All Products
			     
			    
			    $this->v_thumb_styles = apply_filters('vibe_builder_thumb_styles',array(
			                            ''=> plugins_url('../images/thumb_1.png',__FILE__),
			                            'course'=> plugins_url('../images/thumb_2.png',__FILE__),
			                            'course2'=> plugins_url('../images/thumb_8.png',__FILE__),
			                            'course3'=> plugins_url('../images/thumb_8.jpg',__FILE__),
			                            'course4'=> plugins_url('../images/thumb_9.jpg',__FILE__),
			                            'course5'=> plugins_url('../images/thumb_10.jpg',__FILE__),
			                            'course6'=> plugins_url('../images/thumb_13.jpg',__FILE__),
			                            'postblock'=> plugins_url('../images/thumb_11.jpg',__FILE__),
			                            'side'=> plugins_url('../images/thumb_3.png',__FILE__),
			                            'blogpost'=> plugins_url('../images/thumb_6.png',__FILE__),
			                            'images_only'=> plugins_url('../images/thumb_4.png',__FILE__),
			                            'testimonial'=> plugins_url('../images/thumb_5.png',__FILE__),
			                            'testimonial2'=> plugins_url('../images/testimonial2.jpg',__FILE__),
			                            'event_card'=> plugins_url('../images/thumb_7.png',__FILE__),
			                            'general'=> plugins_url('../images/thumb_12.png',__FILE__),
			                            'generic'=> plugins_url('../images/generic.jpg',__FILE__),
			                            'simple'=> plugins_url('../images/simple.jpg',__FILE__),
			                          ));
			    

			    add_action( 'vc_before_init', array($this,'v_builder_mapper' ));
			}
		    
		}


		function v_builder_mapper() {
		    // Title
		    vc_map(
		        array(
		            'name' => __( 'Vibe Carousel','vibe-customtypes'),
		            'base' => 'v_carousel',
		            'category' => __( 'Vibe Builder' ,'vibe-customtypes'),
		            'group' => 'Vibe Builder',
		            'icon'        => 'vibe-builder-icon',
		            'params' => array(
		                array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Show Carousel title', 'vibe-customtypes' ),
							'param_name'  => 'show_title',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
						array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Title/Heading', 'vibe-customtypes'),
		                    'param_name' => 'title',
		                    'value' => __('Heading', 'vibe-customtypes'),
		                    'dependency' => array(
								'element' => 'show_title',
								'value' => '1',
							),
		                ),
						array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Show More link', 'vibe-customtypes' ),
							'param_name'  => 'show_more',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
						array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('More link', 'vibe-customtypes'),
		                    'param_name' => 'more_link',
		                    'value' => '',
		                    'description' => __('Static link to more units in carousel, plus sign', 'vibe-customtypes'),
							'dependency' => array(
								'element' => 'show_more',
								'value' => '1',
							),
		                ),
		                array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Slider Controls : Direction Arrows', 'vibe-customtypes' ),
							'param_name'  => 'show_controls',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
						array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Slider Controls : Control Dots', 'vibe-customtypes' ),
							'param_name'  => 'show_controlnav',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
						array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__('Select Carousel Post Type', 'vibe-customtypes'),
							'param_name'  => 'post_type',
							'value'       => $this->v_post_types,
						),
						array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__('Order', 'vibe-customtypes'),
							'param_name'  => 'course_style',
							'value'       => array(
							                __('Recently published','vibe-customtypes') => 'recent',
				                __('Most Students','vibe-customtypes') =>'popular' ,
				                __('Featured','vibe-customtypes') => 'featured',
				                __('Highest Rated','vibe-customtypes') => 'rated',
				                __('Most Reviews','vibe-customtypes') => 'reviews',
				                __('Upcoming Courses (Start Date)','vibe-customtypes') => 'start_date',
				                __('Expired Courses (Past Start Date)','vibe-customtypes') => 'expired_start_date',
				                __('Free Courses','vibe-customtypes')=>'free',
				                __('Random','vibe-customtypes')=>'random'
							                ),
							'dependency' => array(
								'element' => 'post_type',
								'value' => 'course',
							),
						),
						
						array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Taxonomy ', 'vibe-customtypes'),
		                    'param_name' => 'taxonomy',
		                    'value' => '',
		                    'description' => __('Optionally select a taxonomy to fetch post types from.', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Taxonomy Term', 'vibe-customtypes'),
		                    'param_name' => 'term',
		                    'value' => '',
		                    'description' => __('Select a Term if taxonomy is selected', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Manually add Post IDs', 'vibe-customtypes'),
		                    'param_name' => 'post_ids',
		                    'value' => '',
		                    'description' => __('Comma saperated post ids, ignores taxonomy and terms.', 'vibe-customtypes'),
		                ),
		                array(
							'type'        => 'radio_images',
							'admin_label' => true,
							'heading'     => esc_html__('Carousel/Rotating Block Style', 'vibe-customtypes'),
							'param_name'  => 'featured_style',
							'value'       => $this->v_thumb_styles,
							'std'		  => 'course'
						),
						array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Auto Slide', 'vibe-customtypes' ),
							'param_name'  => 'auto_slide',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
						array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Carousel Block width', 'vibe-customtypes'),
		                    'param_name' => 'column_width',
		                    'value' => '268',
		                    'description' => __('Optionally set caoursel block width', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Maximum Number of blocks in One screen', 'vibe-customtypes'),
		                    'param_name' => 'carousel_max',
		                    'value' => '4',
		                    'description' => __('Responsiveness, for Largest supported screen resolution, set maximum blocks in one screen', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Minimum Number of blocks in One screen', 'vibe-customtypes'),
		                    'param_name' => 'carousel_min',
		                    'value' => '',
		                    'description' => __('Responsiveness, for smallest supported screen resolution, set minimum blocks in one screen', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Number of blocks in one slide', 'vibe-customtypes'),
		                    'param_name' => 'carousel_move',
		                    'value' => '',
		                    'description' => __('Number of blocks to rotate in one carousel slide moves', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Total number of blocks in carousel', 'vibe-customtypes'),
		                    'param_name' => 'carousel_number',
		                    'value' => '',
		                    'description' => __('Total blocks', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Carousel Excerpt length', 'vibe-customtypes'),
		                    'param_name' => 'carousel_excerpt_length',
		                    'value' => '',
		                    'description' => __('If carousel has excerpt then set the length of the excerpt', 'vibe-customtypes'),
		                ),
		                array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Show Link button on image hover', 'vibe-customtypes' ),
							'param_name'  => 'carousel_link',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
		            )
		        )
		    );
			
			vc_map(
		        array(
		            'name' => __( 'Vibe Taxonomy Carousel' ,'vibe-customtypes'),
		            'base' => 'v_taxonomy_carousel',
		            'category' => __( 'Vibe Builder' ,'vibe-customtypes'),
		            'group' => 'Vibe Builder',
		            'icon'        => 'vibe-builder-icon',
		            'params' => array(
			            array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Title/Heading', 'vibe-customtypes'),
		                    'param_name' => 'title',
		                    'value' => __('Heading', 'vibe-customtypes'),
		                    
		                ),

		                array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' =>  __('Include Term Slugs (optional, comma saperated)', 'vibe-customtypes'),
		                    'param_name' => 'term_slugs',
		                    'value' => '',
		                    'description' => __('Comma saperated term slugs.', 'vibe-customtypes'),
		                ),
			          
			             array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__('OrderBy', 'vibe-customtypes'),
							'param_name'  => 'orderby',
							'value'       => array(__('Alphabetical', 'vibe-customtypes')=>'name',__('Description', 'vibe-customtypes')=>'description',__('Custom Order','vibe-customtypes')=>'meta_value_num'),
							'std' => 1
						),
			          
			            array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__('Order', 'vibe-customtypes'),
							'param_name'  => 'order',
							'value'       => array(
							                'Descending' => 'DESC',
							                'Ascending' => 'ASC',
							                
							                ),
						),
			            array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Slider Controls : Direction Arrows', 'vibe-customtypes' ),
							'param_name'  => 'show_controls',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
						array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Slider Controls : Control Dots', 'vibe-customtypes' ),
							'param_name'  => 'show_controlnav',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),

			            array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Auto Slide', 'vibe-customtypes' ),
							'param_name'  => 'auto_slide',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
						array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Carousel Block width', 'vibe-customtypes'),
		                    'param_name' => 'column_width',
		                    'value' => '268',
		                    'description' => __('Optionally set caoursel block width', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Maximum Number of blocks in One screen', 'vibe-customtypes'),
		                    'param_name' => 'carousel_max',
		                    'value' => '4',
		                    'description' => __('Responsiveness, for Largest supported screen resolution, set maximum blocks in one screen', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Minimum Number of blocks in One screen', 'vibe-customtypes'),
		                    'param_name' => 'carousel_min',
		                    'value' => '',
		                    'description' => __('Responsiveness, for smallest supported screen resolution, set minimum blocks in one screen', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Number of blocks in one slide', 'vibe-customtypes'),
		                    'param_name' => 'carousel_move',
		                    'value' => '',
		                    'description' => __('Number of blocks to rotate in one carousel slide moves', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Total number of blocks in carousel', 'vibe-customtypes'),
		                    'param_name' => 'carousel_number',
		                    'value' => '',
		                    'description' => __('Total blocks', 'vibe-customtypes'),
		                ),
			                        
			           
			        )
		        )
		    );
		    
		    vc_map(
		        array(
		            'name' => __( 'Vibe Member Carousel' ,'vibe-customtypes'),
		            'base' => 'v_member_carousel',
		            'category' => __( 'Vibe Builder' ,'vibe-customtypes'),
		            'group' => 'Vibe Builder',
		            'icon'        => 'vibe-builder-icon',
		            'params' => array(

			             array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Show title', 'vibe-customtypes' ),
							'param_name'  => 'show_title',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
						array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Title/Heading', 'vibe-customtypes'),
		                    'param_name' => 'title',
		                    'value' => __('Heading', 'vibe-customtypes'),
		                    'dependency' => array(
								'element' => 'show_title',
								'value' => '1',
							),
		                ),
						array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Show More link', 'vibe-customtypes' ),
							'param_name'  => 'show_more',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
						array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('More link', 'vibe-customtypes'),
		                    'param_name' => 'more_link',
		                    'value' => '',
		                    'description' => __('Static link to more units in carousel, plus sign', 'vibe-customtypes'),
							'dependency' => array(
								'element' => 'show_more',
								'value' => '1',
							),
		                ),

			            array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Slider Controls : Direction Arrows', 'vibe-customtypes' ),
							'param_name'  => 'show_controls',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
						array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Slider Controls : Control Dots', 'vibe-customtypes' ),
							'param_name'  => 'show_controlnav',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
			            array(
	                        'heading' => __('Select member type', 'vibe-customtypes'),
	                        'admin_label' => true,
	                        'param_name'  => 'member_type',
	                        'type' => 'dropdown',
	                        'value' => apply_filters('vibe_editor_member_types',array(_x('All','Select option in Member carousel','vibe-customtypes')=>'all',_x('Student','Select option in Member carousel','vibe-customtypes')=>'student',_x('Instructor','Select option in Member carousel','vibe-customtypes')=>'instructor')),
	                        'std' => '',
			            ),
			            array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' =>__('Or Enter Specific Member Ids', 'vibe-customtypes'),
		                    'param_name' => 'member_ids',
		                    'value' => '',
		                    'description' => __('Comma saperated Member ids', 'vibe-customtypes'),
		                ),    
			             
			            array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' =>__('Enter Profile fields (comma saperated field "names")', 'vibe-customtypes'),
		                    'param_name' => 'profile_fields',
		                    'value' => '',
		                ),
			            array(
							'type'        => 'radio_images',
							'admin_label' => true,
							'heading'     =>  __('Display Style', 'vibe-customtypes'),
							'param_name'  => 'style',
							'value'       => apply_filters('vibe_builder_cmember_styles',			array(
			                                ''=> plugins_url('../images/member_block1.jpg',__FILE__),
			                                'member2'=> plugins_url('../images/member_block2.jpg',__FILE__),
			                                'member3'=> plugins_url('../images/member_block1.jpg',__FILE__),
			                            )),
							'std'		  => ''
						),
			            array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Auto Slide', 'vibe-customtypes' ),
							'param_name'  => 'auto_slide',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
			                        
			            array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Carousel Block width', 'vibe-customtypes'),
		                    'param_name' => 'column_width',
		                    'value' => '268',
		                    'description' => __('Optionally set caoursel block width', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Maximum Number of blocks in One screen', 'vibe-customtypes'),
		                    'param_name' => 'carousel_max',
		                    'value' => '4',
		                    'description' => __('Responsiveness, for Largest supported screen resolution, set maximum blocks in one screen', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Minimum Number of blocks in One screen', 'vibe-customtypes'),
		                    'param_name' => 'carousel_min',
		                    'value' => '',
		                    'description' => __('Responsiveness, for smallest supported screen resolution, set minimum blocks in one screen', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Number of blocks in one slide', 'vibe-customtypes'),
		                    'param_name' => 'carousel_move',
		                    'value' => '',
		                    'description' => __('Number of blocks to rotate in one carousel slide moves', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Total number of blocks in carousel', 'vibe-customtypes'),
		                    'param_name' => 'carousel_number',
		                    'value' => '',
		                    'description' => __('Total blocks', 'vibe-customtypes'),
		                ),
			            array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Show Link button on image hover', 'vibe-customtypes' ),
							'param_name'  => 'carousel_link',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
			                         
			        ),
		        )
		    );

		    /* -- gRID -- */
		    vc_map(
		        array(
		            'name' => __( 'Vibe Grid' ,'vibe-customtypes'),
		            'base' => 'v_grid',
		            'category' => __( 'Vibe Builder','vibe-customtypes'),
		            'group' => 'Vibe Builder',
		            'icon'        => 'vibe-builder-icon',
		            'params' => array(
		                array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Show Grid title', 'vibe-customtypes' ),
							'param_name'  => 'show_title',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
						array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Title/Heading', 'vibe-customtypes'),
		                    'param_name' => 'title',
		                    'value' => __('Heading', 'vibe-customtypes'),
		                    'dependency' => array(
								'element' => 'show_title',
								'value' => '1',
							),
		                ),
		                array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__('Select a Post Type', 'vibe-customtypes'),
							'param_name'  => 'post_type',
							'value'       => $this->v_post_types,
						),
						array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__('Order', 'vibe-customtypes'),
							'param_name'  => 'course_style',
							'value'       =>array(
								__('Recently published','vibe-customtypes') => 'recent',
				                __('Most Students','vibe-customtypes') =>'popular' ,
				                __('Featured','vibe-customtypes') => 'featured',
				                __('Highest Rated','vibe-customtypes') => 'rated',
				                __('Most Reviews','vibe-customtypes') => 'reviews',
				                __('Upcoming Courses (Start Date)','vibe-customtypes') => 'start_date',
				                __('Expired Courses (Past Start Date)','vibe-customtypes') => 'expired_start_date',
				                __('Free Courses','vibe-customtypes')=>'free',
				                __('Random','vibe-customtypes')=>'random'
			                ),
							'dependency' => array(
								'element' => 'post_type',
								'value' => 'course',
							),
						),
						
						array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Taxonomy ', 'vibe-customtypes'),
		                    'param_name' => 'taxonomy',
		                    'value' => '',
		                    'description' => __('Optionally select a taxonomy to fetch post types from.', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Taxonomy Term', 'vibe-customtypes'),
		                    'param_name' => 'term',
		                    'value' => '',
		                    'description' => __('Select a Term if taxonomy is selected', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Manually add Post IDs', 'vibe-customtypes'),
		                    'param_name' => 'post_ids',
		                    'value' => '',
		                    'description' => __('Comma saperated post ids, ignores taxonomy and terms.', 'vibe-customtypes'),
		                ),
		                array(
							'type'        => 'radio_images',
							'admin_label' => true,
							'heading'     => esc_html__('Featured Media Block Style', 'vibe-customtypes'),
							'param_name'  => 'featured_style',
							'value'       => $this->v_thumb_styles,
							'std'		  => 'course'
						),
						
		                 array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Grid Masonry Layout', 'vibe-customtypes' ),
							'param_name'  => 'masonry',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),

		                 array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Grid Masonry Layout', 'vibe-customtypes' ),
							'param_name'  => 'grid_columns',
							'value'       => array(
								esc_html__( '1 Columns in FullWidth', 'vibe-customtypes' )  => 'clear1 col-md-12',
								esc_html__( '2 Columns in FullWidth', 'vibe-customtypes' ) => 'clear2 col-md-6',
								esc_html__( '3 Columns in FullWidth', 'vibe-customtypes' ) => 'clear3 col-md-4',
								esc_html__( '4 Columns in FullWidth', 'vibe-customtypes' ) => 'clear4 col-md-3',
								esc_html__( '6 Columns in FullWidth', 'vibe-customtypes' ) => 'clear6 col-md-2',
							),
							'dependency' => array(
								'element' => 'masonry',
								'value' => '0',
							),
						),

		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Masonry Grid Column Width(in px)', 'vibe-customtypes'),
		                    'param_name' => 'column_width',
		                    'value' => '268',
		                    'description' => __('Optionally set block width', 'vibe-customtypes'),
		                    'dependency' => array(
								'element' => 'masonry',
								'value' => '1',
							),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Spacing between Columns (in px)', 'vibe-customtypes'),
		                    'param_name' => 'gutter',
		                    'value' => '30',
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Total Number of Blocks in Grid', 'vibe-customtypes'),
		                    'param_name' => 'grid_number',
		                    'value' => '6',
		                    'description' => __('Blocks in grid screen/page', 'vibe-customtypes'),
		                ),
		                array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Infinite Scroll', 'vibe-customtypes' ),
							'param_name'  => 'infinite',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
            			array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Enable Pagination (If infinite scroll is off)', 'vibe-customtypes' ),
							'param_name'  => 'pagination',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
		            	array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Excerpt Length (in characters)', 'vibe-customtypes'),
		                    'param_name' => 'grid_excerpt_length',
		                    'value' => '200',
		                    'description' => __('Number of characters if featured block set has excerpt', 'vibe-customtypes'),
		                ),
		                array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Show Link button on image hover', 'vibe-customtypes' ),
							'param_name'  => 'grid_link',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),               
		            )
		        )
		    );

		    /* -- fILTERABLE -- */

		    vc_map(
		        array(
		            'name' => __( 'Vibe Filterable' ,'vibe-customtypes'),
		            'base' => 'v_filterable',
		            'category' => __( 'Vibe Builder','vibe-customtypes'),
		            'group' => 'Vibe Builder',
		            'icon'        => 'vibe-builder-icon',
		            'params' => array(
		               	array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Show title', 'vibe-customtypes' ),
							'param_name'  => 'show_title',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
						array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Filterable Block Title', 'vibe-customtypes'),
		                    'param_name' => 'title',
		                    'value' => __('Heading', 'vibe-customtypes'),
		                    'dependency' => array(
								'element' => 'show_title',
								'value' => '1',
							),
		                ),
		                array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__('Select a Post Type', 'vibe-customtypes'),
							'param_name'  => 'post_type',
							'value'       => $this->v_post_types,
						),
						array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__('Order', 'vibe-customtypes'),
							'param_name'  => 'course_style',
							'value'       =>array(
								__('Recently published','vibe-customtypes') => 'recent',
				                __('Most Students','vibe-customtypes') =>'popular' ,
				                __('Featured','vibe-customtypes') => 'featured',
				                __('Highest Rated','vibe-customtypes') => 'rated',
				                __('Most Reviews','vibe-customtypes') => 'reviews',
				                __('Upcoming Courses (Start Date)','vibe-customtypes') => 'start_date',
				                __('Expired Courses (Past Start Date)','vibe-customtypes') => 'expired_start_date',
				                __('Free Courses','vibe-customtypes')=>'free',
				                __('Random','vibe-customtypes')=>'random'
			                ),
							'dependency' => array(
								'element' => 'post_type',
								'value' => 'course',
							),
						),
						
						array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Taxonomy ', 'vibe-customtypes'),
		                    'param_name' => 'taxonomy',
		                    'value' => '',
		                    'description' => __('Optionally select a taxonomy to fetch post types from.', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Taxonomy Term', 'vibe-customtypes'),
		                    'param_name' => 'term',
		                    'value' => '',
		                    'description' => __('Select a Term if taxonomy is selected', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'textfield',
		                    'holder' => 'div',
		                    'class' => '',
		                    'heading' => __('Manually add Post IDs', 'vibe-customtypes'),
		                    'param_name' => 'post_ids',
		                    'value' => '',
		                    'description' => __('Comma saperated post ids, ignores taxonomy and terms.', 'vibe-customtypes'),
		                ),
		                array(
							'type'        => 'radio_images',
							'admin_label' => true,
							'heading'     => esc_html__('Featured Media Block Style', 'vibe-customtypes'),
							'param_name'  => 'featured_style',
							'value'       => $this->v_thumb_styles,
							'std'		  => 'course'
						),
						
		                 array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Show All link', 'vibe-customtypes' ),
							'param_name'  => 'show_all',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Column Width(in px)', 'vibe-customtypes'),
		                    'param_name' => 'column_width',
		                    'value' => '268',
		                    'description' => __('Optionally set block width', 'vibe-customtypes'),
		                ),
		                array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Total Number of Blocks in screen', 'vibe-customtypes'),
		                    'param_name' => 'filterable_number',
		                    'value' => '6',
		                    'description' => __('Blocks in grid screen/page', 'vibe-customtypes'),
		                ),
            			array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Enable Pagination (If infinite scroll is off)', 'vibe-customtypes' ),
							'param_name'  => 'show_pagination',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						),
		            	array(
		                    'type' => 'number',
		                    'class' => '',
		                    'heading' => __('Excerpt Length (in characters)', 'vibe-customtypes'),
		                    'param_name' => 'filterable_excerpt_length',
		                    'value' => '200',
		                    'description' => __('Number of characters if featured block set has excerpt', 'vibe-customtypes'),
		                ),
		                array(
							'type'        => 'dropdown',
							'admin_label' => true,
							'heading'     => esc_html__( 'Show Link button on image hover', 'vibe-customtypes' ),
							'param_name'  => 'filterable_link',
							'value'       => array(
								esc_html__( 'No', 'vibe-customtypes' )  => '0',
								esc_html__( 'Yes', 'vibe-customtypes' ) => '1',
							),
						), 
		            )
		        )
		    );



		}
		

		/**
		* Function for displaying Title functionality
		*
		* @param array $atts    - the attributes of shortcode
		* @param string $content - the content between the shortcodes tags
		*
		* @return string $html - the HTML content for this shortcode.
		*/
		function vcas_title_function( $atts, $content ) {
		    $atts = shortcode_atts(
			    array(
			        'title' => __( 'This is the custom shortcode' ),
			        'title_color' => '#000000',
			    ), $atts, 'vcas_title'
			);

			$html = '<h1 class="component title ' . $atts['style']. '" style="color: ' . $atts['title_color'] . '">'. $atts['title'] . '</h1>';
			return $html;
		}
		
                
    } // END class Wplms_Vc
    add_action('init',function(){

    	if (defined('WPB_VC_VERSION') && version_compare(WPB_VC_VERSION, 4.8) >= 0) {
            if (function_exists('vc_add_shortcode_param')) {
            	vc_add_shortcode_param( 'radio_images', 'vibe_radio_images_field' );
                vc_add_shortcode_param( 'number', 'vibe_number_field' );
            }
        } else {
            if (function_exists('add_shortcode_param')) {
                add_shortcode_param( 'radio_images', 'vibe_radio_images_field' );
                add_shortcode_param( 'number', 'vibe_number_field' );
            }
        }


    	
		function vibe_radio_images_field( $settings, $value ) {

			$dependency = function_exists('vc_generate_dependencies_attributes') ? vc_generate_dependencies_attributes($settings) : '';
		   	$return = '<div class="all_radio_images" style="clear:both;">';
		   	$param_name = isset( $settings['param_name'] ) ? $settings['param_name'] : '';
		   	$type       = isset( $settings['type'] ) ? $settings['type'] : '';
			$class      = isset( $settings['class'] ) ? $settings['class'] : '';
		   	if(is_array($settings['value'])){
		   		foreach($settings['value'] as $v => $img){

		   			//$return .='';
		   			$return .= '<label class="radio_images '.(($value == $v)?'clicked':'').'" data-value="'.$v.'"><img src="'.$img.'">'.(($value == $v)?'<span></span>':'').'</label>';
		   		}
		   		$return .='<input type="hidden" name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value image_value ' . $param_name . ' ' . $class . ' ' . $dependency . '" value="'.$value.'">';
		   	}

		    return $return;
		}
		
		function vibe_number_field( $settings, $value ) {

			$dependency = function_exists('vc_generate_dependencies_attributes') ? vc_generate_dependencies_attributes($settings) : '';
		   	$param_name = isset( $settings['param_name'] ) ? $settings['param_name'] : '';
			$type       = isset( $settings['type'] ) ? $settings['type'] : '';
			$min        = isset( $settings['min'] ) ? $settings['min'] : '';
			$max        = isset( $settings['max'] ) ? $settings['max'] : '';
			$suffix     = isset( $settings['suffix'] ) ? $settings['suffix'] : '';
			$class      = isset( $settings['class'] ) ? $settings['class'] : '';
			
			$return     = '<input type="number"  min="' . $min . '" max="' . $max . '" class="wpb_vc_param_value ' . $param_name . ' ' . $type . ' ' . $class . '" name="' . $param_name . '" ' . $dependency . ' value="' . $value . '" style="max-width:100px; margin-right: 10px;" />' . $suffix;
		    return $return;
		}

    	$wplms = Wplms_Vc::init();
    },-1);
    

}