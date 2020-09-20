<?php
get_header(vibe_get_header());
if ( have_posts() ) : while ( have_posts() ) : the_post();

$class = get_post_meta(get_the_ID(),'vibe_popup_class',true);
$width = get_post_meta(get_the_ID(),'vibe_popup_width',true);
$height = get_post_meta(get_the_ID(),'vibe_popup_height',true);
?>
<section id="content">
    <div class="container center">
        <div class="popup-content <?php echo $class; ?>" style="display:inline-block;width:<?php echo $width; ?>px;max-height:<?php echo $height; ?>px;">
        	<style>
        		<?php
        			echo get_post_meta(get_the_ID(),'vibe_custom_css',true);
        		?>
        	</style>
            <?php
            the_content();
            endwhile;
            endif;
             ?>
        </div>
    </div>
</section>

<?php
get_footer(vibe_get_footer());
?>