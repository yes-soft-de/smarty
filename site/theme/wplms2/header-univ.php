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
        <header class="univ">
            
            <div class="header_univ">                
                <div id="headertop" class="<?php if(isset($fix) && $fix){echo 'fix';} ?>">
                    <div class="<?php echo vibe_get_container(); ?>">
                        <div class="header_top_main">
                            <div></div>
                            <?php

                            $args = apply_filters('wplms-top-menu',array(
                                        'theme_location'  => 'top-menu',
                                        'container'       => '',
                                        'menu_class'      => 'topmenu',
                                        'fallback_cb'     => 'vibe_set_menu',
                                    ));

                                wp_nav_menu( $args );

                            if ( function_exists('bp_loggedin_user_link') && is_user_logged_in() ) :
                                ?>
                                <ul class="topmenu">
                                    <li><a href="<?php bp_loggedin_user_link(); ?>" class="smallimg vbplogin"><?php $n=vbp_current_user_notification_count(); echo ((isset($n) && $n)?'<em></em>':''); bp_loggedin_user_avatar( 'type=full' ); ?><?php bp_loggedin_user_fullname(); ?></a></li>
                                    <?php do_action('wplms_header_top_login'); ?>
                                </ul>
                            <?php
                            else :
                                ?>
                                <ul class="topmenu">
                                    <li><a href="#login" class=" vbplogin"><?php _e('Login','vibe'); ?></a></li>
                                    <li><?php 
                                        $enable_signup = apply_filters('wplms_enable_signup',0);
                                        if ( $enable_signup ) : 
                                        $registration_link = apply_filters('wplms_buddypress_registration_link',site_url( BP_REGISTER_SLUG . '/' ));
                                        printf( __( '<a href="%s" class="vbpregister" title="'.__('Create an account','vibe').'">'.__('Sign Up','vibe').'</a> ', 'vibe' ), $registration_link );
                                    endif; ?>
                                    </li>
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
                          </div>
                          <div id="vibe_bp_login" class="<?php echo $style; ?>">
                            <?php
                                vibe_include_template("login/$style.php");
                             ?>
                           </div> 
                       </div>     
                </div>
                <div class="header_main">
                    <div class="<?php echo vibe_get_container(); ?>">
                        <div class="header_main_content">
                            <div class="logo_container">
                                <?php

                                    if(is_front_page()){
                                        echo '<h1 id="logo">';
                                    }else{
                                        echo '<h2 id="logo">';
                                    }

                                    $url = apply_filters('wplms_logo_url',VIBE_URL.'/assets/images/logo.png','header');
                                    if(!empty($url)){
                                ?>
                                    <a href="<?php echo vibe_site_url(); ?>"><img src="<?php  echo $url; ?>" width="100" height="48" alt="<?php echo get_bloginfo('name'); ?>" /></a>
                                <?php
                                    }
                                    if(is_front_page()){
                                        echo '</h1>';
                                    }else{
                                        echo '</h2>';
                                    }
                                ?>
                            </div>
                            <?php
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
                            <a id="trigger">
                                <span class="lines"></span>
                            </a> 
                        </div>
                    </div>
                </div>
            </div>
        </header>
