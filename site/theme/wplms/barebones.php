<?php
/**
 * Template Name: BareBones (No Header,Footer)
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<?php
    wp_head();
?>
</head>
<body <?php body_class(); ?>>
<?php
if ( have_posts() ) : while ( have_posts() ) : the_post();
    the_content();
endwhile;
endif;
?>
<?php
wp_footer(); 
?>
</body>
</html>