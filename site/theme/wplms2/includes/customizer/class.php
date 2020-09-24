<?php

/**
 * FILE: class.php 
 * Author: Mr.Vibe 
 * Credits: www.VibeThemes.com
 * Project: WPLMS
 */
if ( !defined( 'ABSPATH' ) ) exit;
add_action( 'customize_register', 'themename_customize_register' );
function themename_customize_register($wp_customize) {

    class Vibe_Customize_Slider_Control extends WP_Customize_Control {
        public $type = 'text';
     
        public function render_content() {
            ?>
            <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <input type="text" class="slider_value text" data-min="<?php echo esc_html( $this->min ); ?>" data-max="<?php echo esc_html( $this->max ); ?>" <?php $this->link(); ?> value="<?php echo $this->value(); ?>" />
            <div class="customizer_slider"></div>
            </label>
            <?php
        }
    }

    class Vibe_Customize_Textarea_Control extends WP_Customize_Control {
        public $type = 'textarea';
     
        public function render_content() {
            ?>
            <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <textarea rows="5" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
            </label>
            <?php
        }
    }


    class Vibe_Customize_ImgSelect_Control extends WP_Customize_Control {
        
        public $type = 'hidden';

         

        public function enqueue() {
            ?>
            <style>
                ul.imgselect_choices {
                    display: grid;
                    grid-template-columns: 1fr 1fr 1fr 1fr;
                    grid-gap: 5px;
                }

                ul.imgselect_choices span.selected {
                    border: 2px solid #06cc4e;
                    display: inline-block;
                    line-height: 0;
                    box-shadow: 0 0 5px rgba(32,204,95,0.78);
                }
            </style>
            <?php
        }

        public function render_content() {
            $control = 'vibe_'.rand(0,9999999);
            $type = 'hidden';
            ?>
            <div class="vibe_customizer_<?php echo $control; ?>">
                <label><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span></label>
                <ul class="imgselect_choices">
                <?php

                    foreach($this->choices as $key=>$choice){
                        echo '<li><span class="'.(($this->value() == $key)?'selected':'').'" data-val="'.$key.'"><img src="'.$choice.'" ></span></li>';
                    }
                ?>
                </ul><input type="<?php echo $type; ?>"  <?php echo $this->get_link(); ?> />
                <script>
                    jQuery('.vibe_customizer_<?php echo $control; ?> input[type="<?php echo $type; ?>"]').val(jQuery('.vibe_customizer_<?php echo $control; ?> .imgselect_choices span.selected').attr('data-val'));
                    jQuery('.vibe_customizer_<?php echo $control; ?> .imgselect_choices span').on('click',function(){
                        jQuery('.vibe_customizer_<?php echo $control; ?> .imgselect_choices span.selected').removeClass('selected');
                        jQuery(this).addClass('selected');
                        jQuery('.vibe_customizer_<?php echo $control; ?> input[type="<?php echo $type; ?>"]').val(jQuery(this).attr('data-val'));
                        jQuery('.vibe_customizer_<?php echo $control; ?> input[type="<?php echo $type; ?>"]').trigger('change');
                    });
                </script>
            </div>
            <?php
        }
    }

    class Vibe_Customize_Color_Control extends WP_Customize_Control {
    
        public $type = 'alpha-color';
        public $palette;
        public $show_opacity;


        public function enqueue() {
            wp_enqueue_style('alpha-color-picker',VIBE_URL.'/includes/customizer/alpha-color-picker.css',array('wp-color-picker'),WPLMS_VERSION);
            wp_enqueue_script(
                'alpha-color-picker',VIBE_URL.'/includes/customizer/alpha-color-picker.js',array( 'jquery', 'wp-color-picker' ),WPLMS_VERSION,true);
        }
        public function render_content() {
            // Process the palette
            if ( is_array( $this->palette ) ) {
                $palette = implode( '|', $this->palette );
            } else {
                // Default to true.
                $palette = ( empty($this->palette) || false === $this->palette || 'false' === $this->palette ) ? 'false' : 'true';
            }
            // Support passing show_opacity as string or boolean. Default to true.
            $show_opacity = ( false === $this->show_opacity || 'false' === $this->show_opacity ) ? 'false' : 'true';
            // Begin the output. ?>
            <div class="vibe_customizer_<?php echo $control; ?>">
                <label>
                <?php // Output the label and description if they were passed in.
                if ( isset( $this->label ) && '' !== $this->label ) {
                    echo '<span class="customize-control-title">' . sanitize_text_field( $this->label ) . '</span>';
                }
                if ( isset( $this->description ) && '' !== $this->description ) {
                    echo '<span class="description customize-control-description">' . sanitize_text_field( $this->description ) . '</span>';
                } ?>
                </label>
                <input class="alpha-color-control" type="text" data-show-opacity="<?php echo $show_opacity; ?>" data-palette="<?php echo esc_attr( $palette ); ?>" data-default-color="<?php echo esc_attr( $this->settings['default']->default ); ?>" <?php $this->link(); ?>  />
            </div>
            <?php
        }
    }

    class Vibe_Customize_Checkbox_control extends WP_Customize_Control{
        public $type = 'toogle_checkbox';
        public function render_content(){
            ?>
            <div class="checkbox_switch">
                <div class="onoffswitch">
                    <input type="checkbox" id="<?php echo esc_attr($this->id); ?>" name="<?php echo esc_attr($this->id); ?>" class="onoffswitch-checkbox" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); checked( $this->value() ); ?>>
                    <label class="onoffswitch-label" for="<?php echo esc_attr($this->id); ?>"></label>
                </div>
                <span class="customize-control-title onoffswitch_label"><?php echo esc_html( $this->label ); ?></span>
                <p><?php echo wp_kses_post($this->description); ?></p>
            </div>
            <?php
        }
    }
}


?>
