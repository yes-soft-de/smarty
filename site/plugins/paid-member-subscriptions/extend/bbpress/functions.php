<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Initializes the bbPress add-on meta-box
 *
 */
function pms_init_meta_boxes_bbpress() {

	$post_types = array( 'forum', 'topic', 'reply' );

	foreach( $post_types as $post_type ) {

		add_meta_box( 'pms_post_content_restriction_bbpress', __( 'Content Restriction', 'paid-member-subscriptions' ), 'pms_meta_box_post_content_restriction_bbpress_output', $post_type );

	}

}
if( class_exists( 'bbPress') )
	add_action( 'add_meta_boxes', 'pms_init_meta_boxes_bbpress' );

/**
 * Output callback for the bbPress add-on meta-box
 *
 */
function pms_meta_box_post_content_restriction_bbpress_output() {

	echo '<div class="pms-icon-wrapper"><span class="dashicons dashicons-lock"></span></div>';

	echo '<h4>' . __( 'Create member only forums with just a few clicks.', 'paid-member-subscriptions' ) . '</h4>';

	echo '<p>' . __( "Allow only members to have access to forums and topics with Paid Member Subscriptions's bbPress Add-On.", 'paid-member-subscriptions' ) . '</p>';

	echo '<a href="https://www.cozmoslabs.com/add-ons/paid-member-subscriptions-bbpress/" target="_blank" class="button-secondary">' . __( 'Learn More', 'paid-member-subscriptions' ) . '</a>';

}
