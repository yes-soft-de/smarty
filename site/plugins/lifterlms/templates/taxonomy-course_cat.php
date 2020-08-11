<?php
/**
 * Template: Archive for the Course Category taxonomy.
 *
 * @package LifterLMS/Templates
 *
 * @since Unknown
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

if ( get_queried_object()->slug === 'meditations' ):
	llms_get_template( 'category-meditations.php' );
else:
	llms_get_template( 'archive-course.php' );
endif;
