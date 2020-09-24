<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div id="footerbottom">
	<div id="scrolltop">
        <a><i class="icon-arrow-1-up"></i><span><?php _e('top','vibe'); ?></span></a>
    </div>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-12">
                <?php $copyright=vibe_get_option('copyright'); echo (isset($copyright)?do_shortcode($copyright):'&copy; 2013, All rights reserved.'); ?>
            </div>
        </div>
    </div>
</div>
</div><!-- END PUSHER -->
</div><!-- END MAIN -->
	<!-- SCRIPTS -->
<?php
wp_footer();
?>   
</body>
</html>