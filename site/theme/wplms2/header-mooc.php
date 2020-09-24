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
        <header class="mooc <?php if(isset($fix) && $fix){echo 'fix';} ?>">
            <div class="<?php echo vibe_get_container(); ?>">
                    <?php
                        if(is_front_page()){
                            echo '<h1 id="logo">';
                        }else{
                            echo '<h2 id="logo">';
                        }
                        $url = apply_filters('wplms_logo_url',VIBE_URL.'/assets/images/logo.png','header');
                        if(!empty($url)){
                    ?>
                        <a href="<?php echo vibe_site_url(); ?>"><img src="<?php  echo $url; ?>" alt="<?php echo get_bloginfo('name'); ?>" /></a>
                    <?php
                        }
                        if(is_front_page()){
                            echo '</h1>';
                        }else{
                            echo '</h2>';
                        }
                            
                    ?>
                    <div id="mooc_menu"> 
                          <?php
                            $args = apply_filters('wplms-main-menu',array(
                                 'theme_location'  => 'main-menu',
                                 'container'       => 'nav',
                                 'menu_class'      => 'menu',
                                 'walker'          => new vibe_walker,
                                 'fallback_cb'     => 'vibe_set_menu'
                             ));
                            wp_nav_menu( $args ); 
                        ?>
                    </div>
                    <ul class="topmenu">
                        <li><?php do_action('wplms_header_nav_search'); ?></li>
                        <?php do_action('wplms_header_top_login'); ?>
                        <?php
                        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )  || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php'))) { global $woocommerce;
                        ?>
                            <li><a class=" vbpcart"><span class="fa fa-shopping-basket"><?php echo (($woocommerce->cart->cart_contents_count)?'<em>'.$woocommerce->cart->cart_contents_count.'</em>':''); ?></span></a>
                            <div class="woocart"><div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div></div>
                            </li>
                        <?php
                        }
                        if ( function_exists('bp_loggedin_user_link') && is_user_logged_in() ) :
                        ?>
                        <li><a href="<?php bp_loggedin_user_link(); ?>" class="smallimg vbplogin"><?php $n=vbp_current_user_notification_count(); echo ((isset($n) && $n)?'<em></em>':''); bp_loggedin_user_avatar( 'type=full' ); ?><span><?php bp_loggedin_user_fullname(); ?></span></a></li>
                        <?php
                        else:
                        ?>
                        <li><a href="#login" rel="nofollow" class=" vbplogin"><span><?php _e('LOGIN','vibe'); ?></span></a></li>
                        <?php
                        endif;    
                        ?>
                    </ul>
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
                <a id="trigger">
                    <span class="lines"></span>
                </a>
            </div>
        </header>
