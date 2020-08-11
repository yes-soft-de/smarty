<?php

add_action( 'wp_ajax_nopriv_smart_way_load_more', 'smart_way_load_more' );    // If The User Not Logged In
add_action( 'wp_ajax_smart_way_load_more', 'smart_way_load_more' );           // If The User Logged In

function smart_way_load_more() {
	$courseId = $_REQUEST['courseId'];
//	$classes = $_REQUEST['classes'];

	$course = new LLMS_Course( $courseId );
	$output = '';
	if ( $courseId ):
		foreach ( $course->get_lessons( 'lessons' ) as $lesson ):
			if ( wp_get_attachment_url( get_post_thumbnail_id( $lesson->id ) ) ) {
				$image = wp_get_attachment_url( get_post_thumbnail_id( $lesson->id ) );
			} else {
				$image = get_template_directory_uri() . '/img/inner-peace-meditation.jpg';
			}
			$output .= '
			<li class="llms-loop-item mx-auto meditations ' . ($lesson->is_free() ? 'freeLesson' : '') . ' p-4 my-3">
		      <div class="llms-loop-item-content m-0" style="">
		        <div class="row">
		          <div class="col-7">
			          <a class="llms-loop-link" href="' . get_the_permalink( $lesson->id ) . '">
			            <img src="' . $image . '" alt="Relax" class="llms-featured-image wp-post-image">
			            <div class="d-inline-block meditation-div-title">
			              <span class="meditation-title d-block">' . $lesson->title . '</span>
			              <span class="meditation-shadow-title d-block">' . $lesson->title . '</span>
			            </div>
			          </a><!-- .llms-loop-link -->
		          </div>
		          <div class="col-5 text-right align-self-center">
		            <span class="meditation-play"></span>
		          </div>
		        </div>		
		      </div><!-- .llms-loop-item-content -->
	    	</li>';

		endforeach;
	else:
		return 0;
	endif;

	echo $output;

	die();

}



/*
	========================
		SINGLE POST CUSTOM FUNCTIONS
	========================
*/
function sunset_post_navigation() {
	$nav = '<div class="row">';

	$prev = get_previous_post_link( '<div class="post-link-nav"><span class="sunset-icon sunset-chevron-left" aria-hidden="true"></span> %link</div>', '%title' );
	$nav .= '<div class="col-xs-12 col-sm-6">' . $prev . '</div>';

	$next = get_next_post_link( '<div class="post-link-nav">%link <span class="sunset-icon sunset-chevron-right" aria-hidden="true"></span></div>', '%title' );
	$nav .= '<div class="col-xs-12 col-sm-6 text-right">' . $next . '</div>';
	$nav .= '</div>';
	return $nav;
}




/*
 ================================
		CONTACT FORM AJAX FUNCTION
	===============================
 */
add_action( 'wp_ajax_nopriv_sunset_save_user_contact_form', 'sunset_save_user_contact_form' );    // If The User Not Logged In
add_action( 'wp_ajax_sunset_save_user_contact_form', 'sunset_save_user_contact_form' );           // If The User Logged In

function sunset_save_user_contact_form() {

	$title = wp_strip_all_tags($_POST["name"]);
	$email = wp_strip_all_tags($_POST["email"]);
	$message = wp_strip_all_tags($_POST["message"]);

	$args = array(
		'post_title'    => $title,
		'post_content'  => $message,
		'post_author'   => 1,  // because we don't know the id of the user we insert 1 referring to the first admin user
		'post_status'   => 'publish',
		'post_type'     => 'sunset-contact', // this post_type fetching from custom-post-type.php file
		'meta_input'    => array(
			'_contact_email_value_key' => $email
		)
	);
	/*
	 * wp_insert_post : it's allow us to save whatever information in whatever post type ex ( $title, $email, $message )
	 * it has by default sanitize and validate all value passing through of it
	 * the only thing that have to do is check on stripping, so we have to use wp_strip_all_tags
	 */
	$postID = wp_insert_post( $args );

  if ( $postID != 0 ) {

	  $to = get_bloginfo('admin_email');
	  $subject = 'Sunset Contact Form - '.$title;

	  $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $to .'>'; // 'From: Alex <me@alecaddd.com>'
	  $headers[] = 'Reply-To: '.$title.' <'.$email.'>';
	  $headers[] = 'Content-Type: text/html: charset=UTF-8';

	  wp_mail($to, $subject, $message, $headers);

  }

	echo $postID;

	die();

}








