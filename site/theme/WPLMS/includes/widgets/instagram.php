<?php

/**
 * FILE: instagram.php 
 * Author: Mr.Vibe 
 * Credits: www.VibeThemes.com
 * Project: WPLMS 
 */

if ( !defined( 'ABSPATH' ) ) exit;
	
class Vibe_Instagram_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( 'vibe-instagram-widget', 'Vibe Instagram Widget', array(
			'description' => 'Display up to your latest Instagram submissions in your sidebar.',
		) );
	}

	/**
	 * Displays the widget contents.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
                $accesstoken = isset( $instance['accesstoken'] ) ? $instance['accesstoken'] : '271783024.5500cf4.a597eaad8f604099b678805280f7e4ba';
                $clientid = isset( $instance['clientid'] ) ? $instance['clientid'] : '5500cf4eeb97454fbc61492df2899a75';
                $username = isset( $instance['username'] ) ? $instance['username'] : 'vibethemes';
		$count = isset( $instance['count'] ) ?  $instance['count'] : 10;
                $size = isset( $instance['size'] ) ?  $instance['size'] : 'small';
                
                $accesstoken=  strip_tags($accesstoken);
                $clientid=  strip_tags($clientid);
                $username=  strip_tags($username);

                add_action('wp_footer','vibe_instagram_scripts',1,1);
                echo "<script>
                        jQuery.fn.spectragram.accessData = {
                                    accessToken: '$accesstoken',
                                    clientID: '$clientid'
                            };";
                echo "".(($username)?"                
               jQuery('ul.instagram').each(function(){
                jQuery('ul.instagram').spectragram('getUserFeed',{
                        query: '$username',
                        max: '$count',
                        size: '$size'
                    });
                        });":"
                        
                 jQuery('ul.instagram').spectragram('getPopular', { 
                    size: '$size',
                    max: '$count'
                    });
                ")."";
                echo "</script>";
		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
                
                echo '<ul class="instagram '.$size.'"></ul>';
		echo $args['after_widget'];
	}
	/**
	 * Validate and update widget options.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['accesstoken'] = strip_tags( $new_instance['accesstoken'] );
                $instance['clientid'] = strip_tags( $new_instance['clientid'] );
                $instance['username'] = strip_tags( $new_instance['username'] );
		$instance['count'] = absint( $new_instance['count'] );
                $instance['size'] = absint( $new_instance['size'] );
		return $new_instance;
	}

	/**
	 * Render widget controls.
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : 'Instagram';
		$accesstoken = isset( $instance['accesstoken'] ) ? $instance['accesstoken'] : '';
                $clientid = isset( $instance['clientid'] ) ? $instance['clientid'] : '';
                $username = isset( $instance['username'] ) ? $instance['username'] : '';
		$count = isset( $instance['count'] ) ? absint( $instance['count'] ) : 10;
                $size = isset( $instance['size'] ) ? absint( $instance['size'] ) : 'small';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:','vibe'  ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'accesstoken' ); ?>"><?php _e( 'Instagram AccessToken:','vibe' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'accesstoken' ); ?>" name="<?php echo $this->get_field_name( 'accesstoken' ); ?>" type="text" value="<?php echo esc_attr( $accesstoken ); ?>" />
			<span>Get your instagram Access token from <a href="http://jelled.com/instagram/access-token">here</a></span>
		</p>
                <p>
			<label for="<?php echo $this->get_field_id( 'clientid' ); ?>"><?php _e( 'Instagram Client ID:','vibe' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'clientid' ); ?>" name="<?php echo $this->get_field_name( 'clientid' ); ?>" type="text" value="<?php echo esc_attr( $clientid ); ?>" />
		</p>
                <p>
			<label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e( 'Instagram Username (<small>If blank then Instagram popular images are shown</small>):','vibe' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" type="text" value="<?php echo esc_attr( $username ); ?>" />
		</p>
                <p>
			<label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e( 'Size:','vibe'  ); ?></label><br />
			<select name="<?php echo $this->get_field_name( 'size' ); ?>">
                            <option value="big" <?php if($size == 'big')echo 'SELECTED'; ?>><?php _e('Big','vibe'); ?></option>
                            <option value="medium" <?php if($size == 'medium')echo 'SELECTED'; ?>><?php _e('Medium','vibe'); ?></option>
                            <option value="small" <?php if($size == 'small')echo 'SELECTED'; ?>><?php _e('Small','vibe'); ?></option>
                        </select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Count:','vibe'  ); ?></label><br />
			<input type="number" min="1" max="20" value="<?php echo esc_attr( $count ); ?>" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" />
		</p>

		<?php
	}
}

// Register the widget.
add_action( 'widgets_init', 'vibe_instagram_widget_init' );
function vibe_instagram_widget_init() {
	register_widget( 'Vibe_Instagram_Widget' );
}
