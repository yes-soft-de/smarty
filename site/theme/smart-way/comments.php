<?php
/*
		This is the template for the Comments
		@package sunsettheme
*/

// Security Line, Check For User Password If By Some Way There Is A Login Page Or Created One In The Future And THe User Isn't LoggedIn
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">
	<?php if ( have_comments() ): ?>
		<h2 class="comment-title">
			<?php
				/*
				 * _nx: give us the ability different format of the same sentence and than compare these sentences with specific variable
				 * if the variable is singular it will return the singular string of our text inside a specific context
				 * &ldquo; &rdquo; : it's html code to print the double code [ "" ], it's one for left and other for right
				 * %1$s: code give us the ability to access the first value of '_nx' function
				 * %2$s: code give us the ability to access the second value of '_nx' function
				 * 'comments title': will automatically grab the comments title for every section
				 * number_format_i18n : it convert an integer number to a format based on the local or location that user is accessing the website
				 * */
				printf(
					esc_html( _nx( 'One comment on &ldquo;%2$s&rdquo;', '%1$s comments on &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'sunsettheme' ) ),
					number_format_i18n( get_comments_number() ),
					'<span>' . get_the_title() . '</span>'
				);
			?>
		</h2>

		<?php sunset_get_post_comments_navigation(); ?>

		<ol class="comment-list">
			<?php
				$args = array(
					'walker'        => null, // null : is the default
					'max_depth'     => 2,
					'style'         => 'ol',
					'callback'      => null,
					'end-callback'  => null, // null : is the default
					'type'          => 'all',
					'reply_text'    => 'Reply',
					'page'          => '',   // '' : empty is the default, it mean bring all comments and create pagination
					'per_page'      => '',   // '' : default, fetch how many comments in the page
					'avatar_size'   => 64,   // it's in pixel, 0 value make the avatar hide
					'reverse_top_level' => null, // true: will start with the most resent comments, and will end with the less resent comments
																				// null: default value, system will print the default order, older at the top and newer at bottom
					'echo'          => true  // true: will echo everything, false: will return only without echo anything
				);
				wp_list_comments( $args );
			?>
		</ol>

		<?php sunset_get_post_comments_navigation(); ?>

		<!--Check If The Comments Is Closed For Some Reasons And There Is Comments Inserted Before-->
		<?php if ( ! comments_open() && get_comments_number() ): ?>
			<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'sunsettheme' ); ?></p>
		<?php endif; ?>

	<?php endif; ?>
  <?php

    // And to change the (author, email, url) structure we have to group theme inside other array and apply them to our comment_form arguments using apply_filters function
	  $fields = array(

		  'author' =>
			  '<div class="form-group"><label for="author">' . __( 'Name', 'domainreference' ) . '</label> <span class="required">*</span> <input id="author" name="author" type="text" class="form-control" value="' . esc_attr( $commenter['comment_author'] ) . '" required="required" /></div>',

		  'email' =>
			  '<div class="form-group"><label for="email">' . __( 'Email', 'domainreference' ) . '</label> <span class="required">*</span><input id="email" name="email" class="form-control" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" required="required" /></div>',

		  'url' =>
			  '<div class="form-group last-field"><label for="url">' . __( 'Website', 'domainreference' ) . '</label><input id="url" name="url" class="form-control" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" /></div>'

	  );

	  // We Have To Use the comment_form arguments if we want to change the wordpress form to our form style
	  $args = array(
		  'class_submit'    => 'btn btn-block btn-lg dark-blue',
		  'label_submit'    => __( 'Submit Comment' ),
		  'comment_field'   =>          // For Textarea Structure
			  '<div class="form-group"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label> <span class="required">*</span><textarea id="comment" class="form-control" name="comment" rows="4" required="required"></textarea></p>',
		  'fields'          => apply_filters( 'comment_form_default_fields', $fields )
	  );

	  comment_form( $args );
  ?>
</div>

