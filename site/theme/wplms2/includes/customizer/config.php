<?php

/**
 * FILE: config.php 
 * Author: Mr.Vibe 
 * Credits: www.VibeThemes.com
 * Project: WPLMS
 */
if ( !defined( 'ABSPATH' ) ) exit;
global $vibe_options;
$google_fonts = vibe_get_option('google_fonts');
$fonts=array();
if(isset($google_fonts)&& is_array($google_fonts))
foreach($google_fonts as $font){
    $fonts[$font]=$font;
}

if(isset($vibe_options['custom_fonts']) && is_array($vibe_options['custom_fonts']) && count($vibe_options['custom_fonts']) > 0){
    $custom_fonts=array();
    foreach($vibe_options['custom_fonts'] as $font){
        $custom_fonts[$font]=$font;
    }
    $fonts= array_merge($fonts, $custom_fonts); 
}
$font_weights =array(
        '300'=>'300 : Light',
        '400'=>'400 : Normal',
        '600'=>'600 : Bold',
        '700'=>'700 : Bolder',
        '800'=>'800 : Bolder'
    );
 
                    
$vibe_customizer = array(
    'sections' => array(
                    'theme'=>'Theme',
                    'layouts'=>'Layouts',
                    'header'=>'Header',
                    'typography'=>'Typography',
                    'body'=>'Body',
                    'footer'=>'Footer',
                    'custom'=>'Custom CSS',
                    ),
    'controls' => apply_filters('wplms_customizer_config',array(
        'theme' => array( 
                            'theme_skin' => array(
                                                    'label' => 'Theme Skin',
                                                    'type'  => 'select',
                                                    'choices' => array(
                                                        ''=>__('Default','vibe'),
                                                        'minimal'=>__('Minimal','vibe'),
                                                        'elegant'=>__('Elegant','vibe'),
                                                        'modern'=>__('Modern','vibe'),
                                                        'material'=>__('Material','vibe'),
                                                        ),
                                                    'default' => ''
                                                ),
                            'primary_bg' => array(
                                                'label' => 'Theme Primary Color',
                                                'type'  => 'color',
                                                'default' => '#78c8c9'
                                                ),
                            'primary_color' => array(
                                                    'label' => 'Theme Primary Text Color',
                                                    'type'  => 'color',
                                                    'default' => '#FFF'
                                                    ),  
                            ),
        'layouts' => array( 
                    'theme_style' => array(
                                            'label' => 'Theme Layout',
                                            'type'  => 'imgselect',
                                            'choices' => array(
                                                ''=>VIBE_URL.'/includes/customizer/images/theme_layout_container.jpg',
                                                'fluid'=>VIBE_URL.'/includes/customizer/images/theme_layout_full_width.jpg',
                                                'boxed'=>VIBE_URL.'/includes/customizer/images/theme_layout_boxed.jpg',
                                                ),
                                            'default' => ''
                                        ),
                    'directory_layout' => array(
                                            'label' => 'Directory Layout',
                                            'type'  => 'imgselect',
                                            'choices' => array(
                                                ''=>VIBE_URL.'/includes/customizer/images/directory_layout_default.jpg',
                                                'd2'=>VIBE_URL.'/includes/customizer/images/directory_layout_d2.jpg',
                                                'd3'=>VIBE_URL.'/includes/customizer/images/directory_layout_d3.jpg',
                                                'd4'=>VIBE_URL.'/includes/customizer/images/directory_layout_d4.jpg',
                                                'd5'=>VIBE_URL.'/includes/customizer/images/directory_layout_d5.jpg',
                                                ),
                                            'default' => ''
                                        ),
                    'profile_layout' => array(
                                        'label' => 'Profile Layout',
                                        'type'  => 'imgselect',
                                        'choices' => array(
                                                ''=>VIBE_URL.'/includes/customizer/images/profile_layout_default.jpg',
                                                'p2'=>VIBE_URL.'/includes/customizer/images/profile_layout_p2.jpg',
                                                'p3'=>VIBE_URL.'/includes/customizer/images/profile_layout_p3.jpg',
                                                'p4'=>VIBE_URL.'/includes/customizer/images/profile_layout_p4.jpg',
                                                ),
                                        'default' => ''
                                    ), 
                    'group_layout' => array(
                                        'label' => 'Group Layout',
                                        'type'  => 'imgselect',
                                        'choices' => array(
                                                ''=>VIBE_URL.'/includes/customizer/images/group_layout_default.jpg',
                                                'g2'=>VIBE_URL.'/includes/customizer/images/group_layout_g2.jpg',
                                                'g3'=>VIBE_URL.'/includes/customizer/images/group_layout_g3.jpg',
                                                'g4'=>VIBE_URL.'/includes/customizer/images/group_layout_g4.jpg',
                                                ),
                                        'default' => ''
                                    ), 
                    'course_layout' => array(
                                        'label' => 'Course Layout',
                                        'type'  => 'imgselect',
                                        'choices' => array(
                                                ''=>VIBE_URL.'/includes/customizer/images/course_layout_default.jpg',
                                                'c2'=>VIBE_URL.'/includes/customizer/images/course_layout_c2.jpg',
                                                'c3'=>VIBE_URL.'/includes/customizer/images/course_layout_c3.jpg',
                                                'c4'=>VIBE_URL.'/includes/customizer/images/course_layout_c4.jpg',
                                                'c5'=>VIBE_URL.'/includes/customizer/images/course_layout_c5.jpg',
                                                'c6'=>VIBE_URL.'/includes/customizer/images/course_layout_c6.jpg',
                                                'blank'=>VIBE_URL.'/includes/customizer/images/theme_layout_full_width.jpg',
                                                ),
                                        'default' => ''
                                    ),
                    ),
        'header' => array(  
                            'header_style' => array(
                                                    'label' => 'Header Style',
                                                    'type'  => 'select',
                                                    'choices' => array(
                                                        ''=>__('Default','vibe'),
                                                        'generic'=>__('Generic','vibe'),
                                                        'sleek'=>__('Sleek','vibe'),
                                                        'transparent'=>__('Transparent','vibe'),
                                                        'center'=>__('Center Aligned','vibe'),
                                                        'standard'=>__('Standard','vibe'),
                                                        'standardcenter'=>__('Standard Center','vibe'),
                                                        'mooc'=>__('Mooc','vibe'),
                                                        'app'=>__('App style','vibe'),
                                                        'univ'=>__('University','vibe'),
                                                        ),
                                                    'default' => ''
                                                ),
                            'login_style' => array(
                                                    'label' => 'Login Style',
                                                    'type'  => 'select',
                                                    'choices' => array(
                                                        ''=>__('Default','vibe'),
                                                        'full_login'=>__('FullScreen','vibe'),
                                                        'pop_login'=>__('Popup','vibe'),
                                                        'bigdrop_login'=>__('Big Drop','vibe'),
                                                        'modern'=>__('Modern','vibe'),
                                                        ),
                                                    'default' => ''
                                                ),
                            'logo_size' => array(
                                                'label' => 'Logo size (height in px)',
                                                'type'  => 'slider',
                                                'default' => '48'
                                                ),
                            'logo_top_padding' => array(
                                                'label' => 'Adjust Logo top',
                                                'type'  => 'slider',
                                                'default' => '6'
                                                ),
                            'logo_bottom_padding' => array(
                                                'label' => 'Adjust Logo bottom',
                                                'type'  => 'slider',
                                                'default' => '0'
                                                ),
                            'header_top_bg' => array(
                                                'label' => 'Header Top / Fixed Background Color',
                                                'type'  => 'color',
                                                'default' => '#232b2d'
                                                ),
                            'header_top_color' => array(
                                                    'label' => 'Header Top / Fixed Text Color',
                                                    'type'  => 'color',
                                                    'default' => '#FFF'
                                                    ),  
                            'top_nav_font' => array(
                                                    'label' => 'Top Navigation Font Family',
                                                    'type'  => 'select',
                                                    'choices' => $fonts,
                                                    'default' => ''
                                                    ),
                            'header_bg' => array(
                                                    'label' => 'Header Background Color',
                                                    'type'  => 'color',
                                                    'default' => '#313b3d'
                                                    ),  
                            'header_color' => array(
                                                    'label' => 'Header Text / Menu Text Color',
                                                    'type'  => 'color',
                                                    'default' => '#FFF'
                                                    ),  
                            'header_font_size' => array(
                                                    'label' => 'Header Text / Menu Text Font size (in px)',
                                                    'type'  => 'slider',
                                                    'default' => '13'
                                                    ),  
                            'nav_bg' => array(
                                                    'label' => 'Sub Navigation Background Color',
                                                    'type'  => 'color',
                                                    'default' => '#48575a'
                                                    ), 
                            'nav_color' => array(
                                                    'label' => 'Sub-Nav Text Color',
                                                    'type'  => 'color',
                                                    'default' => '#FFF'
                                                    ),  
                            'nav_font_size' => array(
                                                    'label' => 'Sub-Nav Font size (in px)',
                                                    'type'  => 'slider',
                                                    'default' => '12'
                                                    ),
                            'nav_font' => array(
                                                    'label' => 'Navigation Font Family',
                                                    'type'  => 'select',
                                                    'choices' => $fonts,
                                                    'default' => ''
                                                    ),
                            'nav_padding' => array(
                                                'label' => 'Adjust Menu padding',
                                                'type'  => 'slider',
                                                'default' => '30'
                                                ),
                            'login_light' => array(
                                                    'label' => 'Login Drop Light background',
                                                    'type'  => 'color',
                                                    'default' => '#313b3d'
                                                    ),
                            'login_dark' => array(
                                                    'label' => 'Login drop dark background',
                                                    'type'  => 'color',
                                                    'default' => '#232b2d'
                                                    ),
                            'login_light_color' => array(
                                                    'label' => 'Login drop light text color',
                                                    'type'  => 'color',
                                                    'default' => '#fafafa'
                                                ),
                            'login_dark_color' => array(
                                                    'label' => 'Login drop dark text color',
                                                    'type'  => 'color',
                                                    'default' => '#fff'
                                                ),                                                                                              

                            ),
        'typography' => array(
            
                            'h1_font' => array(
                                                            'label' => 'H1 Font Family',
                                                            'type'  => 'select',
                                                            'choices' => $fonts,
                                                            'default' => ''
                                                            ),
                              'h1_font_weight' => array(
                                                            'label' => 'H1: Font Weight',
                                                            'type'  => 'select',
                                                            'default' => '400',
                                                            'choices' => $font_weights
                                                            ),                               
                             'h1_color' => array(
                                                            'label' => 'H1 Font Color',
                                                            'type'  => 'color',
                                                            'default' => '#474747'
                                                            ),
                             'h1_size' => array(
                                                            'label' => 'H1 Font Size (in px)',
                                                            'type'  => 'slider',
                                                            'default' => '28'
                                                            ),       
                             
                             'h2_font' => array(
                                                            'label' => 'H2 Font Family',
                                                            'type'  => 'select',
                                                            'choices' => $fonts,
                                                            'default' => ''
                                                            ),   
                               'h2_font_weight' => array(
                                                            'label' => 'H2: Font Weight',
                                                            'type'  => 'select',
                                                            'choices' => $font_weights,
                                                            'default' => '400'
                                                            ),                              
                             'h2_color' => array(
                                                            'label' => 'H2 Font Color',
                                                            'type'  => 'color',
                                                            'default' => '#474747'
                                                            ),
                             'h2_size' => array(
                                                            'label' => 'H2 Font Size (in px)',
                                                            'type'  => 'slider',
                                                            'default' => '22'
                                                            ),  
                             'h3_font' => array(
                                                            'label' => 'H3 Font Family',
                                                            'type'  => 'select',
                                                            'choices' => $fonts,
                                                            'default' => ''
                                                            ),  
                             'h3_font_weight' => array(
                                                            'label' => 'H3: Font Weight',
                                                            'type'  => 'select',
                                                            'choices' => $font_weights,
                                                            'default' => '400'
                                                            ),                               
                             'h3_color' => array(
                                                            'label' => 'H3 Font Color',
                                                            'type'  => 'color',
                                                            'default' => '#474747'
                                                            ),
                             'h3_size' => array(
                                                            'label' => 'H3 Font Size (in px)',
                                                            'type'  => 'slider',
                                                            'default' => '18'
                                                            ),     
                            'h4_font' => array(
                                                            'label' => 'H4 Font Family',
                                                            'type'  => 'select',
                                                            'choices' => $fonts,
                                                            'default' => ''
                                                            ),  
                             'h4_font_weight' => array(
                                                            'label' => 'H4: Font Weight',
                                                            'type'  => 'select',
                                                            'default' => '400',
                                                            'choices' => $font_weights,
                                                            ),                               
                             'h4_color' => array(
                                                            'label' => 'H4 Font Color',
                                                            'type'  => 'color',
                                                            'default' => '#474747'
                                                            ),
                             'h4_size' => array(
                                                            'label' => 'H4 Font Size (in px)',
                                                            'type'  => 'slider',
                                                            'default' => '16'
                                                            ),    
                             'h5_font' => array(
                                                            'label' => 'H5 Font Family',
                                                            'type'  => 'select',
                                                            'choices' => $fonts,
                                                            'default' => ''
                                                            ),   
                             'h5_font_weight' => array(
                                                            'label' => 'H5: Font Weight',
                                                            'type'  => 'select',
                                                            'choices' => $font_weights,
                                                            'default' => '400'
                                                            ),                               
                             'h5_color' => array(
                                                            'label' => 'H5 Font Color',
                                                            'type'  => 'color',
                                                            'default' => '#474747'
                                                            ),
                             'h5_size' => array(
                                                            'label' => 'H5 Font Size (in px)',
                                                            'type'  => 'slider',
                                                            'default' => '14'
                                                            ),     
                             'h6_font' => array(
                                                            'label' => 'H6 Font Family',
                                                            'type'  => 'select',
                                                            'choices' => $fonts,
                                                            'default' => ''
                                                            ),   
                             'h6_font_weight' => array(
                                                            'label' => 'H6: Font Weight',
                                                            'type'  => 'select',
                                                            'choices' => $font_weights,
                                                            'default' => '400'
                                                            ),                               
                             'h6_color' => array(
                                                            'label' => 'H6 Font Color',
                                                            'type'  => 'color',
                                                            'default' => '#474747'
                                                            ),
                             'h6_size' => array(
                                                            'label' => 'H6 Font Size (in px)',
                                                            'type'  => 'slider',
                                                            'default' => '12'
                                                            ), 
                             'heading_font' => array(
                                                            'label' => 'Heading : Font Family',
                                                            'type'  => 'select',
                                                            'choices' => $fonts,
                                                            'default' => ''
                                                            ),   
                             'heading_font_weight' => array(
                                                            'label' => 'Heading : Font Weight',
                                                            'type'  => 'select',
                                                            'choices' => array(
                                                                '300'=>'300 : Light',
                                                                '400'=>'400 : Normal',
                                                                '600'=>'600 : Bold',
                                                                '700'=>'700 : Bolder',
                                                                '800'=>'800 : Bolder'
                                                            ),
                                                            'default' => '600'
                                                            ),   
                                'heading_size' => array(
                                                            'label' => 'Heading : Font Size (in px)',
                                                            'type'  => 'slider',
                                                            'default' => '16'
                                                            ),                             
                             'heading_color' => array(
                                                            'label' => 'Heading : Font Color',
                                                            'type'  => 'color',
                                                            'default' => '#474747'
                                                            ),
                            'widget_title_font' => array(
                                                            'label' => 'Widget Title Font Family',
                                                            'type'  => 'select',
                                                            'choices' => $fonts,
                                                            'default' => ''
                                                            ),   
                             'widget_title_font_weight' => array(
                                                            'label' => 'Widget Title: Font Weight',
                                                            'type'  => 'select',
                                                            'choices' => array(
                                                                '300'=>'300 : Light',
                                                                '400'=>'400 : Normal',
                                                                '600'=>'600 : Bold',
                                                                '700'=>'700 : Bolder',
                                                                '800'=>'800 : Bolder'
                                                            ),
                                                            'default' => '600'
                                                            ),                               
                             'widget_title_color' => array(
                                                            'label' => 'Widget Title Font Color',
                                                            'type'  => 'color',
                                                            'default' => '#474747'
                                                            ),
                             'widget_title_size' => array(
                                                            'label' => 'Widget Title Size (in px)',
                                                            'type'  => 'slider',
                                                            'default' => '16'
                                                            ),  
                             'woo_prd_title_font_weight' => array(
                                                            'label' => 'WooCommerce Product Thumb Title: Font Weight',
                                                            'type'  => 'select',
                                                            'choices' => array(
                                                                '300'=>'300 : Light',
                                                                '400'=>'400 : Normal',
                                                                '600'=>'600 : Bold',
                                                                '700'=>'700 : Bolder',
                                                                '800'=>'800 : Bolder'
                                                            ),
                                                            'default' => '400'
                                                            ),                               
                             'woo_prd_title_color' => array(
                                                            'label' => 'WooCommerce Product Thumb Title Font Color',
                                                            'type'  => 'color',
                                                            'default' => '#474747'
                                                            ),
                             'woo_prd_title_size' => array(
                                                            'label' => 'WooCommerce Product Thumbnail Title Size (in px)',
                                                            'type'  => 'slider',
                                                            'default' => '14'
                                                            ), 
                             'woo_heading_title_size' => array(
                                                            'label' => 'WooCommerce Heading Size (in px)',
                                                            'type'  => 'slider',
                                                            'default' => '16'
                                                            ),                                      
                                                          
            ),

        'body' => array( 
                            'body_bg' => array(
                                                'label' => 'Body Background Color',
                                                'type'  => 'color',
                                                'default' => '#f6f6f6'
                                                ),

                            'content_bg' => array(
                                                    'label' => 'Content Area Background Color',
                                                    'type'  => 'color',
                                                    'default' => '#FFF'
                                                    ), 
                            'content_color' => array(
                                                    'label' => 'Content Area Text Color',
                                                    'type'  => 'color',
                                                    'default' => '#444'
                                                    ), 
                            'content_link_color' => array(
                                                    'label' => 'Link Color',
                                                    'type'  => 'color',
                                                    'default' => '#444'
                                                    ), 
                            'price_color' => array(
                                                    'label' => 'Price Color',
                                                    'type'  => 'color',
                                                    'default' => '#70c989'
                                                    ),
                            'body_font_size' => array(
                                                        'label' => 'Body Font Size',
                                                        'type'  => 'slider',
                                                        'default' => '14'
                                                        ), 
                            'body_font_family' => array(
                                                        'label' => 'Body Font Family',
                                                        'type'  => 'select',
                                                        'choices' => $fonts,
                                                        'default' => ''
                                                        ), 
                            'single_menu_font_size' => array(
                                                    'label' => 'Single Menu font size',
                                                    'type'  => 'slider',
                                                    'default' => '11'
                                                    ),              
                            'single_menu_font_family' => array(
                                                    'label' => 'Single Menu font family',
                                                    'type'  => 'select',
                                                    'choices' => $fonts,
                                                    'default' => ''
                                                    ),                                                          
                            'single_dark_color' => array(
                                                    'label' => 'Single Menu Dark Background Color',
                                                    'type'  => 'color',
                                                    'default' => '#232b2d'
                                                    ),
                            'single_dark_text' => array(
                                                    'label' => 'Single Menu Dark Text Color',
                                                    'type'  => 'color',
                                                    'default' => '#ffffff'
                                                    ),
                            'single_light_color' => array(
                                                    'label' => 'Single Menu Light Background Color',
                                                    'type'  => 'color',
                                                    'default' => '#313b3d'
                                                    ),
                            'single_light_text' => array(
                                                    'label' => 'Single Menu Light Text Color',
                                                    'type'  => 'color',
                                                    'default' => '#ffffff'
                                                    ),   
                            'main_button_color' => array(
                                                    'label' => 'Main Button Color',
                                                    'type'  => 'color',
                                                    'default' => '#fa7252'
                                                    ),                                                                                                                       
                            ),
        'footer' => array( 
                            'footer_style' => array(
                                                    'label' => 'Footer Style',
                                                    'type'  => 'select',
                                                    'choices' => array(
                                                        ''=>__('Default (Double Sidebars)','vibe'),
                                                        'single'=>__('Single Sidebar Footer','vibe'),
                                                        'bottom'=>__('Footer Bottom','vibe'),
                                                        'copyright'=>__('Copyright Only','vibe'),
                                                        ),
                                                    'default' => ''
                                                ),
                            'footer_bg' => array(
                                                'label' => 'Footer Background Color',
                                                'type'  => 'color',
                                                'default' => '#313b3d'
                                                ),
                            'footer_color' => array(
                                                    'label' => 'Footer Text Color',
                                                    'type'  => 'color',
                                                    'default' => '#FFF'
                                                    ), 
                            'footer_heading_color' => array(
                                                    'label' => 'Footer Heading Color',
                                                    'type'  => 'color',
                                                    'default' => '#FFF'
                                                    ), 
                            'footer_bottom_bg' => array(
                                                    'label' => 'Bottom Footer Background Color',
                                                    'type'  => 'color',
                                                    'default' => '#232b2d'
                                                    ), 
                            'footer_bottom_color' => array(
                                                    'label' => 'Bottom Footer Text Color',
                                                    'type'  => 'color',
                                                    'default' => '#FFF'
                                                    ),                                                  
                            ),
        'custom' => array( 
                            'custom_css' => array(
                                                'label' => 'Add Custom CSS',
                                                'type'  => 'textarea'
                                                ),
                            ),
        )
    )    
);

?>
