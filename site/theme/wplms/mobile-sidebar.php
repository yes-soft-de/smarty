<?php


?>
<div class="pagesidebar">
    <div class="sidebarcontent">    
        <a id="close_menu_sidebar" title="Close"><span></span></a>
        <?php
            $args = apply_filters('wplms-mobile-menu',array(
                'theme_location'  => 'mobile-menu',
                'container'       => '',
                'menu_class'      => 'sidemenu',
                'items_wrap' => '<div class="mobile_icons"><a id="mobile_searchicon"><i class="fa fa-search"></i></a>'.( (function_exists('WC')) ?'<a href="'.esc_url( wc_get_cart_url() ).'"><span class="fa fa-shopping-basket"><em>'.WC()->cart->cart_contents_count.'</em></span></a>':'').'</div><ul id="%1$s" class="%2$s">%3$s</ul>',
                'fallback_cb'     => 'vibe_set_menu',
            ));

            wp_nav_menu( $args );
        ?>
    </div>
</div>  