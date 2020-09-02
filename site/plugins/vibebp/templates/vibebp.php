<?php
/**
 * Template Name: Layout (No Header,Footer)
 */

if ( !defined( 'ABSPATH' ) ) exit;


get_header();


if ( have_posts() ) : while ( have_posts() ) : the_post();
    the_content();
endwhile;
endif;
?>
<?php

get_footer();
