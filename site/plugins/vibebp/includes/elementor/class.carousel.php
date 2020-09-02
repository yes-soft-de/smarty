<?php

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly.
}

class Vibe_Carousel extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{


    public function get_name() {
		return 'vibe-carousel';
	}

	public function get_title() {
		return __( 'Vibe Carousel', 'vibeapp' );
	}

	public function get_icon() {
		return 'dashicons dashicons-editor-code';
	}

	public function get_categories() {
		return [ 'vibeapp','VibeApp' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Controls', 'vibeapp' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title',
			[
				'label' => __( 'Carousel Title', 'vibeapp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'placeholder' => __( 'Enter Carousel Title', 'vibeapp' ),
			]
		);

		$this->add_control(
			'more_link',
			[
				'label' => __( 'Show More link', 'vibeapp' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibeapp' ),
						'icon' => 'fa fa-x',
					],
					'1' => [
						'title' => __( 'Yes', 'vibeapp' ),
						'icon' => 'fa fa-check',
					],
				],
			]
		);


		$this->add_control(
			'show_controls',
			[
				'label' =>__('Show Direction arrows', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibeapp' ),
						'icon' => 'fa fa-x',
					],
					'1' => [
						'title' => __( 'Yes', 'vibeapp' ),
						'icon' => 'fa fa-check',
					],
				],
			]
		);

		$this->add_control(
			'show_controlnav',
			[
				'label' =>__('Show Control dots', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibeapp' ),
						'icon' => 'fa fa-x',
					],
					'1' => [
						'title' => __( 'Yes', 'vibeapp' ),
						'icon' => 'fa fa-check',
					],
				],
			]
		);


		$v_post_types = array();
	    $post_types=get_post_types('','objects'); 

	    foreach ( $post_types as $post_type ){
	        if( !in_array($post_type->name, array('attachment','revision','nav_menu_item')))
	           $v_post_types[$post_type->name]=$post_type->label;
	    }
	    

		$this->add_control(
			'post_type',
			[
				'label' => __('Enter Post Type<br /><span style="font-size:11px;">(Select Post Type from Posts/Courses/Clients/Products ...)</span>', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'course',
				'options' => $v_post_types,
			]
		);
		$this->add_control(
			'taxonomy',
			[
				'label' => __('Enter relevant Taxonomy name used for Filter buttons (example : course-cat,event-type..)', 'vibeapp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'placeholder' => __( 'example : course-cat,event-type..', 'vibeapp' ),
			]
		);
		
		$this->add_control(
			'term',
			[
				'label' => __('Enter Taxonomy Term Slug <br />(optional, only if above is selected, comma saperated for multiple terms): ', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'placeholder' => __( 'Enter Taxonomy Terms', 'vibeapp' ),
			]
		);

		$this->add_control(
			'post_ids',
			[
				'label' => __('Enter Post Ids', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'url',
				'placeholder' => __( 'Enter comma saparated', 'vibeapp' ),
			]
		);

		$this->add_control(
			'course_style',
			[
				'label' => __('Post Order', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'rated',
				'options' => array(
	                'recent' => 'Recently published',
	                'alphabetical'=> 'Alphabetical',
	                'random' => 'Random'
                ),
			]
		);
		
		$this->add_control(
			'featured_style',
			[
				'label' => __( 'Featured Style', 'vibeapp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => plugins_url('../images/thumb_2.png',__FILE__),
				'options' => apply_filters('vibeapp_featured_styles_options',array(
	                'course' => 'course',
                    'course2' => 'Course 2',
                    'postblock' => 'Postblock',
                    'side'=> 'Side',
                    'blogpost' => 'Blogpost' ,
                    'images_only'=> 'Images only',
                    'testimonial'=> 'Testimonial',
                    'testimonial2'=> 'Testimonial 2',
                    'general'=> 'general',
                    'generic'=> 'generic',
                    'simple'=> 'simple',
                    'blog_card'=> 'Blog card',
                    'generic_card'=> 'Generic card',
                )),
			]
		);

		$this->add_control(
			'auto_slide',
			[
				'label' =>__('Auto slide/rotate', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibeapp' ),
						'icon' => 'fa fa-x',
					],
					'1' => [
						'title' => __( 'Yes', 'vibeapp' ),
						'icon' => 'fa fa-check',
					],
				],
			]
		);

		$this->add_control(
			'carousel_max',
			[
				'label' =>__('Maximum Number of blocks in One screen', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 12,
				'step' => 1,
				'default' => 4,
			]
		);

		$this->add_control(
			'carousel_min',
			[
				'label' =>__('Minimum Number of blocks in one Screen', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 12,
				'step' => 1,
				'default' => 2,
			]
		);

		$this->add_control(
			'carousel_move',
			[
				'label' =>__('Move blocks in one slide', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 12,
				'step' => 1,
				'default' => 1,
			]
		);

		$this->add_control(
			'carousel_number',
			[
				'label' =>__('Total Number of Blocks', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 99,
				'step' => 1,
				'default' => 6,
			]
		);

		$this->add_control(
			'carousel_excerpt_length',
			[
				'label' =>__('Excerpt Length in Block (in characters)', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 10,
				'max' => 200,
				'step' => 5,
				'default' => 100,
			]
		);


		$this->add_control(
			'carousel_link',
			[
				'label' => __('Show Link button on image hover', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => __( 'No', 'vibeapp' ),
						'icon' => 'fa fa-x',
					],
					'1' => [
						'title' => __( 'Yes', 'vibeapp' ),
						'icon' => 'fa fa-check',
					],
				],
			]
		);


		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$shortcode = '[vibeapp_carousel 
	    title="'.($settings['title']).'" 
	    show_title="'.(empty($settings['title'])?0:1).'" 
	    show_more="'.(empty($settings['more_link'])?0:1).'" 
	    more_link="'.($settings['more_link']).'" 
	    show_controls="'.($settings['show_controls']).'" 
	    show_controlnav="'.($settings['show_controlnav']).'" 
	    post_type="'.($settings['post_type']).'" 	
	    taxonomy="'.(empty($settings['taxonomy'])?0:$settings['taxonomy']).'" 
	    term="'.(empty($settings['term'])?0:$settings['term']).'" 
	    post_ids="'.($settings['post_ids']).'" 
	    course_style="'.($settings['course_style']).'" 
	    featured_style="'.($settings['featured_style']).'" 
	    auto_slide="'.(isset($settings['auto_slide'])?$settings['auto_slide']:'').'" 
	    column_width="'.($settings['column_width']).'" 
	    carousel_max="'.($settings['carousel_max']).'" 
	    carousel_min="'.($settings['carousel_min']).'" 
	    carousel_move="'.($settings['carousel_move']).'" 
	    carousel_number="'.($settings['carousel_number']).'" 
	    carousel_rows="'.($settings['carousel_rows']).'" 
	    carousel_excerpt_length="'.($settings['carousel_excerpt_length']).'" 
	    carousel_link="'.($settings['carousel_link']).'"] [/vibeapp_carousel]';
		
		//echo $shortcode;

		echo do_shortcode($shortcode);
	}

}


