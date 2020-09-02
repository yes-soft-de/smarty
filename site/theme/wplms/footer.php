<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<footer>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="footertop">
                <?php 
                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar('topfootersidebar') ) : ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="footerbottom">
                <?php 
                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar('bottomfootersidebar') ) : ?>
                <?php endif; ?>
            </div>
        </div>
    </div> 
    <div id="scrolltop">
        <a><i class="icon-arrow-1-up"></i><span><?php _e('top','vibe'); ?></span></a>
    </div>
</footer>
<div id="footerbottom">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-3">
                <h2 id="footerlogo">
                <?php
                    $url = apply_filters('wplms_logo_url',VIBE_URL.'/assets/images/logo.png','footer');
                    if(!empty($url)){
                ?>    

                    <a href="<?php echo vibe_site_url('','logo'); ?>"><img src="<?php  echo $url; ?>" alt="<?php echo get_bloginfo('name'); ?>" /></a>
                <?php 
                    }
                ?>
                </h2>
                <?php $copyright=vibe_get_option('copyright'); echo (isset($copyright)?do_shortcode($copyright):'&copy; 2013, All rights reserved.'); ?>
            </div>
            <div class="col-md-9">
                <?php
                    $footerbottom_right = vibe_get_option('footerbottom_right');
                    if(isset($footerbottom_right) && $footerbottom_right){
                        echo '<div id="footer_social_icons">';
                        echo vibe_socialicons();
                        echo '</div>';
                    }else{
                        ?>
                        <div id="footermenu">
                            <?php
                                    $args = array(
                                        'theme_location'  => 'footer-menu',
                                        'container'       => '',
                                        'menu_class'      => 'footermenu',
                                        'fallback_cb'     => 'vibe_set_menu',
                                    );
                                    wp_nav_menu( $args );
                            ?>
                        </div> 
                        <?php
                    }
                ?>
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