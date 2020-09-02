<?php
/**
 * Customizer plugin
 *
 * @class       VibeBP_Customizer
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 * @copyright   VibeThemes
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}




class VibeBP_Customizer{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Customizer();
        return self::$instance;
    }

	private function __construct(){

		add_action('customize_register', array($this,'vibebp_customize'));
		add_action('wp_head',array($this,'generate_css'));
	}

	function generate_controls(){
		return apply_filters('vibebp_customizer_config',array(
		    'sections' => array(
                'vibebp_general_settings'=>'VibeBp General Settings',
            	'vibebp_light_colors'=>'VibeBp Light Colors',
            	'vibebp_dark_colors'=>'VibeBp Dark Colors',
            ),
		    'controls' => array(
                'vibebp_general_settings' => array( 
                    'loggedin_profile_header' => array(
                        'label' => 'Disable Header in My Profile',
                        'type'  => 'toggle',
                        'default' => ''
                    ),
                    'loggedin_profile_footer' => array(
                        'label' => 'Disable Footer in My Profile',
                        'type'  => 'toggle',
                        'default' => ''
                    ),
                ),
		        'vibebp_light_colors' => array( 
                    'light_primary' => array(
                        'label' => 'Primary Color',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'light_primarycolor' => array(
                        'label' => 'Text Color on  Primary background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    
                    'light_body' => array(
                        'label' => 'Body Background',
                        'type'  => 'color',
                        'default' => ''
                    ), 
                    'light_text' => array(
                        'label' => 'Text color',
                        'type'  => 'color',
                        'default' => ''
                    ), 
                    'light_bold' => array(
                        'label' => 'Heading / Title color',
                        'type'  => 'color',
                        'default' => ''
                    ), 
                    'light_sidebar' => array(
                        'label' => 'Sidebar Background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'light_border' => array(
                        'label' => 'Border color',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'light_darkborder' => array(
                        'label' => 'Dark Border',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'light_shadow' => array(
                        'label' => 'Shadow color',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'light_dark' => array(
                        'label' => 'Darker Background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'light_light' => array(
                        'label' => 'Lighter Background',
                        'type'  => 'color',
                        'default' => ''
                    ),  
                ),
		        'vibebp_dark_colors' => array( 
                    'dark_primary' => array(
                        'label' => 'Primary Color',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'dark_primarycolor' => array(
                        'label' => 'Text Color on  Primary background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    
                    'dark_body' => array(
                        'label' => 'Body Background',
                        'type'  => 'color',
                        'default' => ''
                    ), 
                    'dark_text' => array(
                        'label' => 'Text color',
                        'type'  => 'color',
                        'default' => ''
                    ), 
                    'dark_bold' => array(
                        'label' => 'Heading / Title color',
                        'type'  => 'color',
                        'default' => ''
                    ), 
                    'dark_sidebar' => array(
                        'label' => 'Sidebar Background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'dark_border' => array(
                        'label' => 'Border color',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'dark_darkborder' => array(
                        'label' => 'Dark Border',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'dark_shadow' => array(
                        'label' => 'Shadow color',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'dark_dark' => array(
                        'label' => 'Darker Background',
                        'type'  => 'color',
                        'default' => ''
                    ),
                    'dark_dark' => array(
                        'label' => 'darker Background',
                        'type'  => 'color',
                        'default' => ''
                    ),  
                )
			)
		));
	}

	function vibebp_customize($wp_customize) {


	    $vibe_customizer = $this->generate_controls();


	    $wp_customize->add_panel( 
			'vibebp_settings',
			array(
				'priority'       => 10001,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '',
				'title'          => __('VibeBP Settings', 'mytheme'),
				'description'    => __('VibeBP Settings', 'mytheme'),
			)
		);

		$i=9991; // Show sections after the WordPress default sections
	    if(isset($vibe_customizer) && is_Array($vibe_customizer)){
	        foreach($vibe_customizer['sections'] as $key=>$value){
	        	

	            $wp_customize->add_section( $key, array(
	            'title'          => $value,
	            'panel'			 => 'vibebp_settings',
	            'priority'       => $i,
	        ) );
	            $i = $i+4;
	        }
	    }
	    if(isset($vibe_customizer) && is_array($vibe_customizer)){
		    foreach($vibe_customizer['controls'] as $section => $settings){ 
		    	$i=1;
		        foreach($settings as $control => $type){
		            $i=$i+2;
		            $wp_customize->add_setting( 'vibebp_customizer['.$control.']', array(
	                    'label'         => $type['label'],
	                    'type'           => 'option',
	                    'capability'     => 'edit_theme_options',
	                    'transport'  => 'refresh',
	                    'sanitize_callback'=> 'vibebp_sanitizer',
	                    'default'       => (empty($type['default'])?'':$type['default'])
	                ) );
		            
		            switch($type['type']){
		                case 'color':
		                        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $control, array(
		                        'label'   => $type['label'],
		                        'section' => $section,
		                        'settings'   => 'vibebp_customizer['.$control.']',
		                        'priority'       => $i
		                        ) ) );            
		                    break;   
		                case 'select':
		                        $wp_customize->add_control( $control, array(
	                                'label'   => $type['label'],
	                                'section' => $section,
	                                'settings'   => 'vibebp_customizer['.$control.']',
	                                'priority'   => $i,
	                                'type'    => 'select',
	                                'choices'    => (empty($type['choices'])?'':$type['choices'])                       
	                                ) );
		                break;
                        case 'toggle':
                            $wp_customize->add_control( new VibeBp_Customizer_Toggle_Control( $wp_customize, $control, array(
                                    'label'       => $type['label'],
                                    'section'     => $section,
                                    'settings'   => 'vibebp_customizer['.$control.']',
                                    'priority'   => $i,
                                    'type'        => 'ios',// light, ios, flat
                            ) ) );
                        break;
		            }
		        }
		    }
		}

	}

	function generate_css(){

		$customizer=get_option('vibebp_customizer');
		if(!empty($customizer)){
			echo '<style>';
			$light = $dark = [];
			foreach($customizer as $key=>$customise){
				if(!empty($customise)){
					if(stripos($key, 'light_') !== false){
						$lkey = str_replace('light_','--',$key);
						$light[]= $lkey.':'.$customise;
                        if($lkey == 'primary'){
                            echo '.button.is-primary{background-color:'.$customise.';}';
                        }
					}else if(stripos($key, 'dark_') !== false){
						$dkey = str_replace('dark_','--',$key);
						$dark[]=$dkey.':'.$customise;
                        if($dkey == 'primary'){
                            echo '.vibebp_myprofile.dark_theme .button.is-primary{background-color:'.$customise.';}';
                        }
					}else{
                        if($key == 'loggedin_profile_header'){
                            echo '.vibebp_my_profile header,.vibebp_my_profile #headertop,.vibebp_my_profile .header_content,.vibebp_my_profile #title{display:none;}.pusher{overflow:visible;}.vibebp_my_profile #vibebp_member{padding-top:0;}';
                        }
                        if($key == 'loggedin_profile_footer'){
                            echo '.vibebp_my_profile footer,.vibebp_my_profile #footerbottom{display:none;} #vibebp_member{padding-top:0 !important;} ';
                        }
                    }
                    
				}
			}
			echo ':root{'.implode(';',$light).'}';
			echo '.vibebp_myprofile.dark_theme::root{'.implode(';',$dark).'}';
			echo '</style>';
		}
	}
}

VibeBP_Customizer::init();


add_action('customize_register', function(){
class VibeBp_Customizer_Toggle_Control extends WP_Customize_Control {
    public $type = 'ios';

    /**
     * Enqueue scripts/styles.
     *
     * @since 3.4.0
     */
    public function enqueue() {
        wp_enqueue_script( 'customizer-toggle-control', plugins_url('../assets/js/vibebp_toggle.js',__FILE__), array( 'jquery' ), rand(), true );
        wp_enqueue_style( 'customizer-toggle-control', plugins_url('../assets/css/vibebp_toggle.css',__FILE__), array(), rand() );

        $css = '
            .disabled-control-title {
                color: #a0a5aa;
            }
            input[type=checkbox].tgl-light:checked + .tgl-btn {
                background: #0085ba;
            }
            input[type=checkbox].tgl-light + .tgl-btn {
              background: #a0a5aa;
            }
            input[type=checkbox].tgl-light + .tgl-btn:after {
              background: #f7f7f7;
            }
            input[type=checkbox].tgl-ios:checked + .tgl-btn {
              background: #0085ba;
            }
            input[type=checkbox].tgl-flat:checked + .tgl-btn {
              border: 4px solid #0085ba;
            }
            input[type=checkbox].tgl-flat:checked + .tgl-btn:after {
              background: #0085ba;
            }
        ';
        wp_add_inline_style( 'pure-css-toggle-buttons', $css );
    }

    /**
     * Render the control's content.
     *
     * @author soderlind
     * @version 1.2.0
     */
    public function render_content() {
        ?>
        <label class="customize-toogle-label">
            <div style="display:flex;flex-direction: row;justify-content: flex-start;">
                <span class="customize-control-title" style="flex: 2 0 0; vertical-align: middle;"><?php echo esc_html( $this->label ); ?></span>
                <input id="cb<?php echo $this->instance_number; ?>" type="checkbox" class="tgl tgl-<?php echo $this->type; ?>" value="<?php echo esc_attr( $this->value() ); ?>"
                                        <?php
                                        $this->link();
                                        checked( $this->value() );
                                        ?>
                 />
                <label for="cb<?php echo $this->instance_number; ?>" class="tgl-btn"></label>
            </div>
            <?php if ( ! empty( $this->description ) ) : ?>
            <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>
        </label>
        <?php
    }

    /**
     * Plugin / theme agnostic path to URL
     *
     * @see https://wordpress.stackexchange.com/a/264870/14546
     * @param string $path  file path
     * @return string       URL
     */
    private function abs_path_to_url( $path = '' ) {
        $url = str_replace(
            wp_normalize_path( untrailingslashit( ABSPATH ) ),
            site_url(),
            wp_normalize_path( $path )
        );
        return esc_url_raw( $url );
    }
}
},9);