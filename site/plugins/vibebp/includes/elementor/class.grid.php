<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

 class Vibe_Grid extends \Elementor\Widget_Base  // We'll use this just to avoid function name conflicts 
{

    public function get_name() {
		return 'post grid';
	}

	public function get_title() {
		return __( 'Vibe Grid', 'vibeapp' );
	}

	public function get_icon() {
		return 'dashicons dashicons-grid-view';
	}

	public function get_categories() {
		return [ 'vibeapp' ];
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
			'taxonomy',
			[
				'label' => __( 'Enter Taxonomy Slug', 'vibeapp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'placeholder' => __( 'Enter Taxonomy Slug', 'vibeapp' ),
			]
		);


		$terms = get_terms( 'post_tag', array(
		    'hide_empty' => false,
		) );
		$termarray = array();
		foreach($terms as $term){
			$termarray[$term->slug]=$term->name;
		}
		$this->add_control(
			'term',
			[
				'label' => __('Taxonomy Term ', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'placeholder' => __( 'Enter Taxonomy Term/s', 'vibeapp' ),
			]
		);

		$this->add_control(
			'post_ids',
			[
				'label' => __( 'Or Enter Specific Post Ids (comma saperated)', 'vibeapp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'placeholder' => __( 'Enter post ids', 'vibeapp' ),
			]
		);

		$v_post_types = array();
	    $post_types=get_post_types('','objects'); 

	    foreach ( $post_types as $post_type ){
	        if( !in_array($post_type->name, array('attachment','revision','nav_menu_item','sliders','modals','shop','shop_order','shop_coupon','forum','topic','reply')))
	           $v_post_types[$post_type->name]=$post_type->label;
	    }
	    

		$this->add_control(
			'post_type',
			[
				'label' => __('Select Post Type', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'post',
				'options' => $v_post_types,
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
			'grid_excerpt_length',
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
			'grid_width',
			[
				'label' =>__('Block width', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 10,
				'max' => 1600,
				'step' => 1,
				'default' => 268,
			]
		);

		$this->add_control(
			'featured_style',
			[
				'label' => __( 'Featured Style', 'vibeapp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => plugins_url('../images/thumb_2.png',__FILE__),
				'options' => array(
	                'course' => 'course',
                    'course2' => 'course2',
                    'course3' => 'course3',
                    'course4' => 'course4',
                    'course5' => 'course5',
                    'course6' => 'course6',
                    'course7' => 'course7',
                    'course8' => 'course8',
                    'course9' => 'course9',
                    'course10' => 'course10',
                    'postblock' => 'postblock',
                    'side'=> 'side',
                    'blogpost' => 'blogpost' ,
                    'images_only'=> 'Images only',
                    'testimonial'=> 'testimonial',
                    'testimonial2'=> 'testimonial2',
                    'event_card'=> 'event_card',
                    'general'=> 'general',
                    'generic'=> 'generic',
                    'simple'=> 'simple',
                    'blog_card'=> 'Blog card',
                    'generic_card'=> 'Generic card',
                ),
			]
		);

		$this->add_control(
			'grid_control',
			[
				'label' =>__('Control your grid', 'vibeapp'),
				'type' => 'grid_control',
				
				'default' => '{"rows":3,"columns":2,"grid":[{"col":"1/3","row":"1/3"},{"col":1,"row":3},{"col":2,"row":3}]}',
			]
		);

		$this->add_control(
			'gutter',
			[
				'label' =>__('Spacing between Columns (in px)', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 200,
				'step' => 1,
				'default' => 30,
			]
		);
		
		$this->add_control(
			'column_align_verticle',
			[
				'label' => __('Adjust Vertically', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'center',
				'options' => array(
	                'start'=>_x('Start','','vibeapp'),
	                'end' => _x('End','','vibeapp'),
	                'center' => _x('Center','','vibeapp'),
	               	'stretch' => _x('Stretch','','vibeapp'),
                ),
			]
		);
		
		$this->add_control(
			'column_align_horizontal',
			[
				'label' => __('Adjust Horizontally', 'vibeapp'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'center',
				'options' => array(
	                'start'=>_x('Start','','vibeapp'),
	                'end' => _x('End','','vibeapp'),
	                'center' => _x('Center','','vibeapp'),
	               	'stretch' => _x('Stretch','','vibeapp'),
                ),
			]
		);

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings_for_display();
		
		echo Vibebp_Shortcodes::vibebp_grid($settings);
	}

}