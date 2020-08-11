<?php
/*
	=============================
		THEME CUSTOM POST TYPE
	=============================
*/

$contact = get_option( 'activate_contact' );
if ( @$contact == 1 ) {
	// hook for display our contact custom post in admin menu
	add_action( 'init', 'sunset_contact_custom_post_type' );
	/*
	 * create the columns in our contact custom post Using (sunset-contact) post type
	 * Filter : is like an action but it something that we trigger to added an update some prebuild information of wordpress
	 * The Hook Is : manage_ourCustomPostTypeNameVariable_posts_columns ex: manage_sunset-contact_posts_columns
     */
	add_filter( 'manage_sunset-contact_posts_columns', 'sunset_set_contact_columns' );
	// fill our columns that we create with it own value | 10: [default], is the priority | 2: refer to the two function parameters
	add_action( 'manage_sunset-contact_posts_custom_column', 'sunset_contact_custom_column', 10, 2 );
	// action to activate my function and generate the meta box
	add_action( 'add_meta_boxes', 'sunset_contact_add_meta_box' );
	// action to save the post in our contact custom post
	add_action( 'save_post', 'sunset_save_contact_email_data' );
}

/* Contact Custom Port Type */
function sunset_contact_custom_post_type() {
	$labels = array(
		'name'              => __('Messages'),
		'singular_name'     => __('Message'),
		'menu_name'         => __('Messages'),
		'name_admin_bar'    => __('Message')
	);
	$args = array(
		'labels'            => $labels,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'capability_type'   => 'post',  // We Want to be as post not page
		'hierarchical'      => false,   // Because post can't be hierarchical so must put it false
		'menu_position'     => 26,
		'menu_icon'         => 'dashicons-email-alt',
		'supports'          => array( 'title', 'editor', 'author' )
        // We Specify (title, editor, author) because we don't need all the thing inside generic post as (the thumbnail, excerpt, comments, ...)
	);
	// Register Our Post Type
	register_post_type( 'sunset-contact', $args );
}

/*
* function to create new columns and rename all columns as we want
* this $columns variable is automatically pass from this filter (manage_sunset-contact_posts_columns) so this filter graping all the columns we created as ('title', 'editor', 'author')
*/
function sunset_set_contact_columns( $columns ) {
    /*
     * TO remove any column from our page list view of the messages section
     * ex: unset($columns['author']); return $columns
     * */
	$newColumns = array();
	$newColumns['title']    = __('Full Name');
	$newColumns['message']  = __('Message');
	$newColumns['email']    = __('Email');
	$newColumns['date']     = __('Date');
	return $newColumns;
}

/*
 * Function to print the value for every columns
 * This function is a loop: it loop throw all the row , all the messages that we received in our list view messages
 * pages and it's caring just the information of that specific row
 */
function sunset_contact_custom_column( $column, $post_id ) {
	switch ( $column ) {
		case 'message' :
		// in the loop the $post_id is already set and get_the_excerpt will know witch type of message will print
		echo get_the_excerpt();
			break;
		case 'email':
			$email = get_post_meta( $post_id, '_contact_email_value_key', true );
			echo '<a href="mailto:' . $email . '">' . $email . '</a>';
			break;
	}
}

/**** Create Contact Meta Box ****/
// Even If The Meta bax not related ot the custom post type but it related to the contact post type so we generate it in this file
function sunset_contact_add_meta_box() {
	/*
	 * screen : give the ability to print the meta box inside what ever (post, page, or custom post type)
	 * so in our case we want to put it inside our custom post type (sunset-contact)
	 * $context, $priority: without these two option our meta box will always display in the bottom of the page
	*/
	add_meta_box( 'contact_email', __('User Email'), 'sunset_contact_email_callback', 'sunset-contact', 'side', 'default' );
}

// $post: will automatically pass by add_meta_box and it will care all information related to 'sunset-contact' post type
function sunset_contact_email_callback( $post ) {
	/*
	 * built-in function that generate a unique(id, value, string,...) to check if the action(saving, deleting, updating your information)
	 * is legit from a legit user inside our administrator panel
	 * wp_nonce_field : it's important to avoid that any one outside try to hack the system
	*/
	wp_nonce_field( 'sunset_save_contact_email_data', 'sunset_contact_email_meta_box_nonce' );
	/*
	 * Collect the value form meta box | retrieve the value of a custom meta box
	 * for strict rule for all the string value for our meta box they have to start with an underscore before the name
	 * true: use to defined if this value is a single value or multiple value as (array, checkbox...)
	*/
	$value = get_post_meta( $post->ID, '_contact_email_value_key', true );
	// Print The input as wordpress theme build-in
	echo '<label for="sunset_contact_email_field">' . __('User Email Address') . ': </label>';
	echo '<input type="email" id="sunset_contact_email_field" name="sunset_contact_email_field" value="' . esc_attr( $value ) . '" size="25" />';
}

// function to print the email inside our meta box and save the post
function sunset_save_contact_email_data( $post_id ) {
	// check if the nonce is not set in the $_POST Method
	if ( ! isset( $_POST['sunset_contact_email_meta_box_nonce'] ) ) {
		return;
	}
	// check if the nonce is valid and nonce is generated by wordpress and not generated by and hacker or another user
	// sunset_save_contact_email_data: is the actual function that saving my meta box
	if ( ! wp_verify_nonce( $_POST['sunset_contact_email_meta_box_nonce'], 'sunset_save_contact_email_data' ) ) {
		return;
	}
	// check is a manual save or automatically save | prevent the wordpress from saving by him self the meta box
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
		return;
	}
	// Check for user permission
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// check if our input name sunset_contact_email_field is in $_POST method
	if ( ! isset( $_POST['sunset_contact_email_field'] ) ) {
		return;
	}
	// Retrieve the data
	$my_data = sanitize_text_field( $_POST['sunset_contact_email_field'] );
	update_post_meta( $post_id, '_contact_email_value_key', $my_data );
}
