<?php
//Header File
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
<div id="global" class="global">
    <?php
        get_template_part('mobile','sidebar');
    ?>  
    <div class="pusher">
        <?php
            $fix=vibe_get_option('header_fix');
        ?>
        <header class="sleek transparent <?php if(isset($fix) && $fix){echo 'fix';} ?>">
            <div class="<?php echo vibe_get_container(); ?>">
                <div class="row">
                    <div class="col-md-9 col-sm-6 col-xs-4">
                        <?php

                            if(is_home()){
                                echo '<h1 id="logo">';
                            }else{
                                echo '<h2 id="logo">';
                            }

                            $url = apply_filters('wplms_logo_url',VIBE_URL.'/assets/images/logo.png','header');
                            if(!empty($url)){
                        ?>
                        
                            <a href="<?php echo vibe_site_url('','logo'); ?>"><img src="<?php  echo $url; ?>" alt="<?php echo get_bloginfo('name'); ?>" /></a>
                        <?php

                            }
                            if(is_home()){
                                echo '</h1>';
                            }else{
                                echo '</h2>';
                            }

                            $args = apply_filters('wplms-main-menu',array(
                                 'theme_location'  => 'main-menu',
                                 'container'       => 'nav',
                                 'menu_class'      => 'menu',
                                 'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s<li><a id="new_searchicon"><i class="fa fa-search"></i></a></li></ul>',
                                 'walker'          => new vibe_walker,
                                 'fallback_cb'     => 'vibe_set_menu'
                             ));
                            wp_nav_menu( $args ); 
                        ?>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-8">
                        <?php
                            $show_cart = apply_filters('wplms_header_show_cart',1);
                            if ( function_exists('bp_loggedin_user_link') && is_user_logged_in() ) :
                                ?>
                                <ul class="topmenu">
                                    <li><a href="<?php bp_loggedin_user_link(); ?>" class="smallimg vbplogin"><?php $n=vbp_current_user_notification_count(); echo ((isset($n) && $n)?'<em></em>':''); bp_loggedin_user_avatar( 'type=full' ); ?><span><?php bp_loggedin_user_fullname(); ?></span></a></li>
                                    <?php
                                    
                                    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )  || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php')) && $show_cart) { global $woocommerce;
                                    ?>
                                    <li><a class="smallimg vbpcart"><span class="fa fa-shopping-basket"><?php echo (($woocommerce->cart->cart_contents_count)?'<em>'.$woocommerce->cart->cart_contents_count.'</em>':''); ?></span></a>
                                    <div class="woocart"><div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div></div>
                                    </li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            <?php
                            else :
                                ?>
                                <ul class="topmenu">
                                    <li><a href="#login" rel="nofollow" class=" vbplogin"><span><?php _e('LOGIN','vibe'); ?></span></a></li>
                                    <?php
                                    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )  || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php')) && $show_cart) { global $woocommerce;
                                    ?>
                                    <li><a class=" vbpcart"><span class="fa fa-shopping-cart"><?php echo (($woocommerce->cart->cart_contents_count)?'<em>'.$woocommerce->cart->cart_contents_count.'</em>':''); ?></span></a>
                                    <div class="woocart"><?php woocommerce_mini_cart(); ?></div>
                                    </li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            <?php
                            endif;
                        ?>
                        <?php
                            $style = vibe_get_login_style();
                            if(empty($style)){
                                $style='default_login';
                            }
                        ?>
                        <div id="vibe_bp_login" class="<?php echo $style; ?>">
                        <?php
                            vibe_include_template("login/$style.php");
                        ?>
                       </div>
                    </div>
                    <a id="trigger">
                        <span class="lines"></span>
                    </a>
                </div>
            </div>
        </header>
