<?php

/**
 * FILE: customizer.php 
 * Author: Mr.Vibe 
 * Credits: www.VibeThemes.com
 * Project: WPLMS
 */
if ( !defined( 'ABSPATH' ) ) exit;
include_once 'class.php';

//REgisterig Theme Settings/Cusomizer

function vibe_customizer_setup() {
  $customize = get_option('vibe_customizer');
  if(!isset($customize)){
      add_option('vibe_customizer','');
  }
}

// add some settings and such

add_action('customize_register', 'vibe_customize');
add_action('after_setup_theme','vibe_customizer_setup');



function vibe_customize($wp_customize) {

    require_once(dirname(__FILE__) . '/config.php');
/* =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  */
/* =  =  =  =  =  =  =  =  =  = = SECTIONS  =  =  =  =  =  =  =  =  =  =  =  */
/* =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  */
    $i=164; // Show sections after the WordPress default sections
    if(isset($vibe_customizer) && is_Array($vibe_customizer)){
        foreach($vibe_customizer['sections'] as $key=>$value){
            $wp_customize->add_section( $key, array(
            'title'          => $value,
            'priority'       => $i,
        ) );
            $i = $i+4;
        }
    }
    

/* =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  */
/* =  =  =  =  =  =  =  = = SETTINGS & CONTROLS  =  =  =  =  =  =  =  =  =  */
/* =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  =  */
if(isset($vibe_customizer) && is_array($vibe_customizer))
    foreach($vibe_customizer['controls'] as $section => $settings){ $i=1;
        foreach($settings as $control => $type){
            $i=$i+2;
            /* =  =  =  REGISTER SETTING  =  =  =  = =*/
            $wp_customize->add_setting( 'vibe_customizer['.$control.']', array(
                                                'label'         => $type['label'],
                                                'type'           => 'option',
                                                'capability'     => 'edit_theme_options',
                                                'transport'  => 'refresh',
                                                'default'       => (empty($type['default'])?'':$type['default'])
                                            ) );
            
            switch($type['type']){
                case 'color':/*
                        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $control, array(
                        'label'   => $type['label'],
                        'section' => $section,
                        'settings'   => 'vibe_customizer['.$control.']',
                        'priority'       => $i
                        ) ) );            
                    break;
                case 'alpha-color': */
                        $wp_customize->add_control( new Vibe_Customize_Color_Control( $wp_customize, $control, array(
                                    'label'   => $type['label'],
                                    'section' => $section,
                                    'settings'   => 'vibe_customizer['.$control.']',
                                    'priority'       => $i,
                                )
                            ) 
                        );            
                    break;   
                case 'image':
                        $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $control, array(
                            'label'   => $type['label'],
                            'section' => $section,
                            'settings'   => 'vibe_customizer['.$control.']',
                            'priority'       => $i
                        ) ) );
                    break;
                case 'select':
                        $wp_customize->add_control( $control, array(
                                'label'   => $type['label'],
                                'section' => $section,
                                'settings'   => 'vibe_customizer['.$control.']',
                                'priority'   => $i,
                                'type'    => 'select',
                                'choices'    => (empty($type['choices'])?'':$type['choices'])                        
                                ) );
                break;
                case 'imgselect':
                        $wp_customize->add_control( new Vibe_Customize_ImgSelect_Control( $wp_customize, $control, array(
                                'label'   => $type['label'],
                                'section' => $section,
                                'settings'   => 'vibe_customizer['.$control.']',
                                'priority'       => $i,
                                'type'    => 'imgselect',
                                'choices'    => (empty($type['choices'])?'':$type['choices'])                        
                                ) ) ); 
                break;
                case 'custom_checkbox':
                        $wp_customize->add_control( new Vibe_Customize_ImgSelect_Control( $wp_customize, $control, array(
                                'label'   => $type['label'],
                                'section' => $section,
                                'settings'   => 'vibe_customizer['.$control.']',
                                'priority'       => $i,
                                'type'    => 'custom_checkbox',
                                'choices'    => (empty($type['choices'])?'':$type['choices'])                        
                                ) ) ); 
                break;
                case 'text':
                        $wp_customize->add_control( $control, array(
                                'label'   => $type['label'],
                                'section' => $section,
                                'settings'   => 'vibe_customizer['.$control.']',
                                'priority'       => $i,
                                'type'    => 'text',
                                ) );
                    break;
                case 'slider':
                        $wp_customize->add_control( new Vibe_Customize_Slider_Control( $wp_customize, $control, array(
                                'label'   => $type['label'],
                                'section' => $section,
                                'settings'   => 'vibe_customizer['.$control.']',
                                'priority'       => $i,
                                'type'    => 'slider',
                                ) ) );
                    break;
                case 'textarea':
                        $wp_customize->add_control( new Vibe_Customize_Textarea_Control( $wp_customize, $control, array(
                                'label'   => $type['label'],
                                'section' => $section,
                                'settings'   => 'vibe_customizer['.$control.']',
                                'priority'       => $i,
                                'type'    => 'textarea',
                                ) ) );
                    break;
            }
        }
    }
}

add_action('customize_controls_print_styles', 'vibe_customize_css');

function vibe_customize_css(){
    wp_dequeue_style( 'vibe-popup-css');
    wp_enqueue_style('customizer_css',VIBE_URL.'/includes/customizer/customizer.css',array(),WPLMS_VERSION,true);
    
}
add_action('customize_controls_print_scripts', 'vibe_customize_scripts');
function vibe_customize_scripts(){
    wp_enqueue_script('wplms_customizer_js',VIBE_URL.'/includes/customizer/customizer.js',array( 'jquery' ),WPLMS_VERSION,true);
}

function wplms_get_theme_color_config($theme){

    $option = array();
    switch($theme){
        case 'minimal':
        case 'material':
            $option['header_top_bg'] = '#ffffff';
            $option['header_bg'] = '#ffffff';
            $option['nav_bg'] = '#ffffff';
            $option['body_bg'] = '#ffffff';
            $option['content_bg'] = '#ffffff';
            $option['single_dark_color'] = '#FAFAFA';
            $option['footer_bg'] = '#ffffff';
            $option['footer_bottom_bg'] = '#ffffff';
            $option['footer_bg'] = '#ffffff';
            $option['single_dark_text'] =  '#444444';
            $option['single_light_color'] = '#ffffff';
            $option['single_light_text'] =  '#444444';
            $option['header_top_color'] = '#444444';
            $option['header_color'] = '#444444';
            $option['widget_title_color'] = '#444444';
            $option['nav_color'] = '#444444';
            $option['content_color'] = '#444444';
            $option['footer_color'] = '#444444';
            $option['footer_heading_color'] = '#444444';
            $option['footer_bottom_color'] = '#444444';
        break;
        case 'modern':
            $option['header_top_bg'] = '#232b2d';
            $option['header_top_color'] =  '#ffffff';
            $option['header_bg'] = '#ffffff';
            $option['header_color'] = '#444444';
        break;
        case 'elegant':
            $option['header_top_bg'] = '#232b2d';
            $option['header_top_color'] =  '#ffffff';
            $option['single_light_color'] =  '#ffffff';
            $option['single_dark_text']='#444444';
            $option['nav_bg'] = '#009dd8';
            $option['nav_color'] = '#ffffff'; 
            $option['header_bg'] = '#ffffff';
            $option['header_color'] = '#444444';
        break;
        default:
            $option['header_top_bg'] = '#232b2d';
            $option['single_dark_color'] = '#232b2d';
            $option['footer_bottom_bg'] = '#232b2d';
            $option['nav_bg'] = '#232b2d';
            $option['header_bg'] = '#313b3d';
            $option['single_light_color'] = '#313b3d';
            $option['footer_bg'] = '#313b3d';
            $option['body_bg']= '#f9f9f9';
            $option['single_dark_text']='#ffffff';
            $option['single_light_text']='#ffffff';
            $option['content_bg']= '#ffffff';
            $option['header_top_color'] = '#232323';
            $option['header_color'] = '#232323';
            $option['nav_color']= '#232323';
            $option['footer_color']= '#232323';
            $option['footer_heading_color']= '#232323';
            $option['footer_bottom_color'] = '#ffffff';
            $option['widget_title_color']= '#232323';
            $option['content_color']= '#232323';
        break;
    }
    return $option;
}

add_action('wp_ajax_reset_customizer_colors','wplms_reset_customizer_colors');
function wplms_reset_customizer_colors(){
    $option = get_option('vibe_customizer');
    if(empty($option)){
       die(); 
    }
    $value = $_POST['value'];
    $new_option = wplms_get_theme_color_config($value);
    
    if(!empty($new_option)){
        foreach($new_option as $k=>$v){
            $option[$k] = $v;
        }
    }
    update_option('vibe_customizer',$option);
    die();
}
