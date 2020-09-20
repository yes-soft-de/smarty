<?php
get_header(vibe_get_header());
if ( have_posts() ) : while ( have_posts() ) : the_post();
?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-10">
                <div class="pagetitle">
                    <?php vibe_breadcrumbs(); ?>
                    <h1><?php the_title(); ?></h1>
                    <?php the_sub_title(); ?>
                </div>
            </div>
            <div class="col-md-2">
                <div class="postdate">
                    <i class="icon-calendar"></i> <?php the_date(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="content">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="content">
                    <div class="main_content">
                    <?php
                        the_question();
                    ?>
                    </div>
                    <?php
                    do_action('wplms_question_after_content');
                    ?>
                </div>
                <?php
                endwhile;
                endif;

                do_action('wplms_front_end_question_controls');
                ?>
            </div>
        </div>
    </div>
</section>

<?php
get_footer(vibe_get_footer());
?>