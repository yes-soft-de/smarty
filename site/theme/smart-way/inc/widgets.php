<?php
/*
@package sunsettheme
	========================
		WIDGETS CLASS
	========================
	This file is about creating custom widget in admin widget page to customize what we want to add in it
*/

class Sunset_Profile_Widget extends WP_Widget {

	// Setup the widget name, description, etc...
	public function __construct()
	{
		$widget_opts = array(
			'classname'     => 'sunset-profile-widget',
			'description'   => 'Custom Sunset Profile Widget'
		);
		parent::__construct( 'sunset_profile', 'Sunset Profile', $widget_opts );
	}

	/*
	 * back-end display of widget
	 */
	public function form( $instance )
	{
		echo '<p><strong>No options for this Widget!</strong><br/>You can control the fields of this Widget from <a href="./admin.php?page=talal_sunset">This Page</a></p>';
	}

	/*
	 * front-end display of widget
	 * it's default wordpress widget function, so we don't need to call it , it'll be called automatically
	 */
	public function widget( $args, $instance )
	{
		// These Options Is The Same THat We Create In The sunset-admin.php file
		$picture = esc_attr( get_option( 'profile_picture' ) );
		$firstName = esc_attr( get_option( 'first_name' ) );
		$lastName = esc_attr( get_option( 'last_name' ) );
		$fullName = $firstName . ' ' . $lastName;
		$description = esc_attr( get_option( 'user_description' ) );

		$twitter_icon = esc_attr( get_option( 'twitter_handler' ) );
		$facebook_icon = esc_attr( get_option( 'facebook_handler' ) );
		$gplus_icon = esc_attr( get_option( 'gplus_handler' ) );

		// This Attribute Fetching from register_sidebar function that we use it in theme-support.php file
		echo $args['before_widget'];
		?>
		<div class="text-center">
			<div class="image-container">
				<div id="profile-picture-preview" class="profile-picture" style="background-image: url(<?php print $picture; ?>);"></div>
			</div>
			<h1 class="sunset-username"><?php print $fullName; ?></h1>
			<h2 class="sunset-description"><?php print $description; ?></h2>
			<div class="icons-wrapper">
				<?php if( !empty( $twitter_icon ) ): ?>
					<a href="https://twitter.com/<?php echo $twitter_icon; ?>" target="_blank"><span class="sunset-icon-sidebar sunset-icon sunset-twitter"></span></a>
				<?php endif;
					if( !empty( $gplus_icon ) ): ?>
						<a href="https://plus.google.com/u/0/+<?php echo $gplus_icon; ?>" target="_blank"><span class="sunset-icon-sidebar sunset-icon sunset-googleplus"></span></a>
					<?php endif;
					if( !empty( $facebook_icon ) ): ?>
						<a href="https://facebook.com/<?php echo $facebook_icon; ?>" target="_blank"><span class="sunset-icon-sidebar sunset-icon sunset-facebook"></span></a>
					<?php endif; ?>
			</div>
		</div>
		<?php
		// This Attribute Fetching from register_sidebar function that we use it in theme-support.php file
		echo $args['after_widget'];
	}
}
add_action( 'widgets_init', function() {
	register_widget( 'Sunset_Profile_Widget' );
});



// Edit default WordPress widgets
function sunset_tag_cloud_font_change( $args ) {
	// Edit the smallest size and the largest size for tags and make the both smallest and largest with the same size
	$args['smallest'] = 8;
	$args['largest'] = 8;
	return $args;
}
add_filter( 'widget_tag_cloud_args', 'sunset_tag_cloud_font_change' );



/*
 * function to change the default categories widget structure
 * Will be apply automatically because of the filter wp_list_categories
 */
function sunset_list_categories_output_change( $links ) {
	$links = str_replace('</a> (', '</a> <span>', $links);
	$links = str_replace(')', '</span>', $links);
	return $links;
}
add_filter( 'wp_list_categories', 'sunset_list_categories_output_change' );





// Create Custom Popular Posts Widget
class Sunset_Popular_Posts_Widgets extends WP_Widget {
	//setup the widget name, description, etc...
	public function __construct() {
		$widget_ops = array(
			'classname' => 'sunset-popular-posts-widget',
			'description' => 'Popular Posts Widget',
		);
		parent::__construct( 'sunset_popular_posts', 'Sunset Popular Posts', $widget_ops );
	}

	// back-end display of widget
	public function form( $instance ) {
		$title = ( !empty( $instance[ 'title' ] ) ? $instance[ 'title' ] : 'Popular Posts' );
		$tot = ( !empty( $instance[ 'tot' ] ) ? absint( $instance[ 'tot' ] ) : 4 );

		$output = '<p>';
		$output .= '<label for="' . esc_attr( $this->get_field_id( 'title' ) ) . '">Title:</label>';
		$output .= '<input type="text" class="widefat" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" value="' . esc_attr( $title ) . '"';
		$output .= '</p>';

		$output .= '<p>';
		$output .= '<label for="' . esc_attr( $this->get_field_id( 'tot' ) ) . '">Number of Posts:</label>';
		$output .= '<input type="number" class="widefat" id="' . esc_attr( $this->get_field_id( 'tot' ) ) . '" name="' . esc_attr( $this->get_field_name( 'tot' ) ) . '" value="' . esc_attr( $tot ) . '"';
		$output .= '</p>';

		echo $output;
	}

	// update widget When Click save button
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance[ 'title' ] = ( !empty( $new_instance[ 'title' ] ) ? strip_tags( $new_instance[ 'title' ] ) : '' );
		$instance[ 'tot' ] = ( !empty( $new_instance[ 'tot' ] ) ? absint( strip_tags( $new_instance[ 'tot' ] ) ) : 0 );

		return $instance;
	}

  // front-end display of widget in the sidebar
  public function widget( $args, $instance ) {
    $tot = absint( $instance[ 'tot' ] );

    $posts_args = array(
      'post_type'			=> 'post',
      'posts_per_page'	=> $tot,
      'meta_key'			=> 'sunset_post_views',
      'orderby'			=> 'meta_value_num',
      'order'				=> 'DESC'
    );

    $posts_query = new WP_Query( $posts_args );
    echo $args[ 'before_widget' ];

    if ( !empty( $instance[ 'title' ] ) ):
      echo $args[ 'before_title' ] . apply_filters( 'widget_title', $instance[ 'title' ] ) . $args[ 'after_title' ];
    endif;

	  if( $posts_query->have_posts() ):
		  //echo '<ul>';
		  while( $posts_query->have_posts() ): $posts_query->the_post();

			  echo '<div class="media">';
			  echo '<div class="media-left"><img class="media-object" src="' . get_template_directory_uri() . '/img/post-' . ( get_post_format() ? get_post_format() : 'standard') . '.png" alt="' . get_the_title() . '"/></div>';
			  echo '<div class="media-body">';
			  echo '<a href="' . get_the_permalink() . '" title="' . get_the_title() . '">' . get_the_title() . '</a>';
			  echo '<div class="row"><div class="col-xs-12">'. sunset_posted_footer( true ) .'</div></div>';
			  echo '</div>';
			  echo '</div>';

		  endwhile;
		  //echo '</ul>';
	  endif;

    echo $args[ 'after_widget' ];
  }

}
add_action( 'widgets_init', function() {
  register_widget( 'Sunset_Popular_Posts_Widgets' );
});
