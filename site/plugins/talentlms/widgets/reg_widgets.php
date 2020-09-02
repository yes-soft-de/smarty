<?php
// snom 7

class Tlms_Widget extends WP_Widget {

	protected $_version = '1.0.0'; 

    public function __construct() {
        
		parent::__construct(
            'Tlms_widget', // Base ID
            'TalentLMS Widget', // Name
            array( 'description' => __( 'A TalentLMS Widget', 'talentlms' ) ) // Args
        );
    }
	
    public function widget( $args, $instance ) { 
       
		extract( $args );
		$this->enqueue_widget_assets();		
        $title = apply_filters( 'widget_title', $instance['title'] );		

        echo $before_widget;
        if ( ! empty( $title ) ) {
            echo $before_title . $title . $after_title;
        }
        $courses = tlms_selectCourses(); ?>
		<div class="tlms-widget-container">
			<?php foreach ($courses as $course): ?>
				<div class="tlms-widget-item">
					<a href="<?php echo get_site_url(); ?>/courses/?tlms-course=<?php echo $course->id; ?>"><img src="<?php echo $course->big_avatar; ?>" alt="<?php echo $course->name; ?>" /><?php echo $course->name; echo ($course->course_code) ? "(".$course->course_code.")":''; ?></a>
				</div>
			<?php endforeach; ?>
		</div>
        <?php
        echo $after_widget;
    }
	
    public function form( $instance ) {
        
		if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        } else {
            $title = __( 'Our Courses', 'talentlms' );
        } ?>
        <p>
			<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <?php
    }
	
    public function update( $new_instance, $old_instance ) {
        
		$instance = array();
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
 
        return $instance;
    }
	
	public function enqueue_widget_assets(){
		
		wp_enqueue_style( 'tlms-widget',  _TLMS_BASEURL_ . '/css/talentlms-widget.css', '', $this->_version ); 
	} 
}

add_action( 'widgets_init', function() { register_widget( 'Tlms_Widget' ); } );
