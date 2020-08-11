<?php
/*
    This is the template for the Archive
    This file refer to every archive page ex : (category, tag, author)
    @package sunsettheme
*/
?>
<?php get_header() ?>
<!-- This is the standard html code that we will find in every standard wordpress theme -->
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <header class="archive-header text-center">
            <?php the_archive_title('<h1 class="page-title">', '</h1>'); ?>
        </header>
        <div class="container sunset-posts-container">
            <?php
            if ( have_posts() ):
                echo '<div class="page-limit">';
                while( have_posts() ):
                    the_post();
                    /*
                     * Get The template-parts for post format | fetch all posts
                     * inside template-parts directory will fetch all files start with content name
                     */
                    get_template_part( 'template-parts/content', get_post_format() );   // ex : template-parts/content, template-parts/content-image ...
                endwhile;

  	            the_posts_pagination();

                echo '</div>';
            endif;
            ?>
        </div>

    </main>
</div><!--#primary-->

<?php get_footer() ?>
