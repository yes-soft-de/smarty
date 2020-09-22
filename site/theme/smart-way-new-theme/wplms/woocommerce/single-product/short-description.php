<?php
/**
 * Single product short description
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/short-description.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  Automattic
 * @package WooCommerce/Templates
 * @version 3.3.0
 */

global $post;

$vcourses=array();

$vcourses=apply_filters('wplms_product_course_order_filter',vibe_sanitize(get_post_meta($post->ID,'vibe_courses',false)));

if(!empty($vcourses) && count($vcourses)){
	echo '<div class="connected_courses"><h6>';
	_e('Courses Included','vibe');
	echo '</h6><ul>';
	foreach($vcourses as $course){
		echo '<li><a href="'.get_permalink($course).'"><i class="icon-book-open"></i> '.get_the_title($course).'</a></li>';
	}
	echo '</ul></div>';
}

if ( ! $post->post_excerpt ) return;
?>
<div itemprop="description" class="woocommerce-product-details__short-description">
	<?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ) ?>
</div>