<?php

if ( !defined( 'ABSPATH' ) ) exit;

//Enhanced version from Lee-Mason Backend panel: Thanks to :leemason.github.com
if ( ! class_exists('VIBE_Options') ){
	
	if(!defined('VIBE_OPTIONS_DIR')){
		define('VIBE_OPTIONS_DIR', get_template_directory().'/options/');
	}
	
	if(!defined('VIBE_OPTIONS_URL')){
		define('VIBE_OPTIONS_URL',get_template_directory_uri().'/options/' );
	}

class VIBE_Options{
	
	protected $framework_url = 'http://www.VibeThemes.com/';
	protected $framework_version = '2.0';
		
	public $dir = VIBE_OPTIONS_DIR;
	public $url = VIBE_OPTIONS_URL;
	public $page = '';
	public $args = array();
	public $sections = array();
	public $extra_tabs = array();
	public $errors = array();
	public $warnings = array();
	public $options = array();
	
	

	/**
	 * Class Constructor. Defines the args for the theme options class
	 *
	 * @since VIBE_Options 1.0
	 *
	 * @param $array $args Arguments. Class constructor arguments.
	*/
	function __construct($sections = array(), $args = array(), $extra_tabs = array()){
		
		$defaults = array();
		
		$defaults['opt_name'] = '';//must be defined by theme/plugin
		
		$defaults['google_api_key'] = '';//must be defined for use with google webfonts field type
		
		$defaults['menu_icon'] = VIBE_OPTIONS_URL.'/img/menu_icon.png';
		$defaults['menu_title'] = __('Options', 'vibe');
		$defaults['page_icon'] = 'icon-themes';
		$defaults['page_title'] = __('Options', 'vibe');
		$defaults['page_slug'] = '_options';
		$defaults['page_cap'] = 'manage_options';
		$defaults['page_type'] = 'menu';
		$defaults['page_parent'] = '';
		$defaults['page_position'] = 100;
		$defaults['allow_sub_menu'] = true;
		
		$defaults['show_import_export'] = true;
		$defaults['dev_mode'] = true;
		$defaults['stylesheet_override'] = false;
		
		$defaults['footer_credit'] = '<span id="footer-thankyou">'.__('Options Panel created using', 'vibe').' the <a href="'.$this->framework_url.'" target="_blank">VIBE Theme Options Framework</a> Version '.$this->framework_version.'</span>';
		
		$defaults['help_tabs'] = array();
		$defaults['help_sidebar'] = '';
		
		//get args
		$this->args = wp_parse_args($args, $defaults);
		$this->args = apply_filters('vibe-opts-args-'.$this->args['opt_name'], $this->args);
		
		//get sections
		$this->sections = apply_filters('vibe-opts-sections-'.$this->args['opt_name'], $sections);
		
		//get extra tabs
		$this->extra_tabs = apply_filters('vibe-opts-extra-tabs-'.$this->args['opt_name'], $extra_tabs);
		
		//set option with defaults
		add_action('init', array($this, '_set_default_options'));
		
		//options page
		add_action('admin_menu', array($this, '_options_page'));
		
		//register setting
		add_action('admin_init', array($this, '_register_setting'));
		
		//add the js for the error handling before the form
		add_action('vibe-opts-page-before-form-'.$this->args['opt_name'], array($this, '_errors_js'), 1);
		
		//add the js for the warning handling before the form
		add_action('vibe-opts-page-before-form-'.$this->args['opt_name'], array($this, '_warnings_js'), 2);
		
		//hook into the wp feeds for downloading the exported settings
		add_action('do_feed_vibeopts-'.$this->args['opt_name'], array($this, '_download_options'), 1, 1);
		
		//get the options for use later on
		$this->options = get_option($this->args['opt_name']);
		
	}//function
	
	
	/**
	 * ->get(); This is used to return and option value from the options array
	 *
	 * @since VIBE_Options 1.2
	 *
	 * @param $array $args Arguments. Class constructor arguments.
	*/
	function get($opt_name, $default = null){
		return (!empty($this->options[$opt_name])) ? $this->options[$opt_name] : $default;
	}//function
	
	/**
	 * ->set(); This is used to set an arbitrary option in the options array
	 *
	 * @since VIBE_Options 1.2
	 * 
	 * @param string $opt_name the name of the option being added
	 * @param mixed $value the value of the option being added
	 */
	function set($opt_name = '', $value = '') {
/*
                         if($_FILES['eot']['name'])
                            $eot = wp_upload_bits( $_FILES['eot']['name'], null, file_get_contents( $_FILES['eot']['tmp_name'] ) );
                        if($_FILES['ttf']['name'])
                            $ttf = wp_upload_bits( $_FILES['ttf']['name'], null, file_get_contents( $_FILES['ttf']['tmp_name'] ) );
                        if($_FILES['woff']['name'])
                            $woff = wp_upload_bits( $_FILES['woff']['name'], null, file_get_contents( $_FILES['woff']['tmp_name'] ) );
                        if($_FILES['svg']['name'])
                            $svg = wp_upload_bits( $_FILES['eot']['svg'], null, file_get_contents( $_FILES['svg']['tmp_name'] ) );
*/		
                        
		if($opt_name != ''){
			$this->options[$opt_name] = $value;
			update_option($this->args['opt_name'], $this->options);
		}//if
	}
	
	/**
	 * ->show(); This is used to echo and option value from the options array
	 *
	 * @since VIBE_Options 1.2
	 *
	 * @param $array $args Arguments. Class constructor arguments.
	*/
	function show($opt_name, $default = ''){
		$option = $this->get($opt_name);
		if(!is_array($option) && $option != ''){
			echo $option;
		}elseif($default != ''){
			echo $default;
		}
	}//function
	
	
	
	/**
	 * Get default options into an array suitable for the settings API
	 *
	 * @since VIBE_Options 1.0
	 *
	*/
	function _default_values(){
		
		$defaults = array();
		
		foreach($this->sections as $k => $section){
			
			if(isset($section['fields'])){
		
				foreach($section['fields'] as $fieldk => $field){
					
					if(!isset($field['std'])){$field['std'] = '';}
						
						$defaults[$field['id']] = $field['std'];
					
				}//foreach
			
			}//if
			
		}//foreach
		
		//fix for notice on first page load
		$defaults['last_tab'] = 0;

		return $defaults;
		
	}
	
	
	
	/**
	 * Set default options on admin_init if option doesnt exist (theme activation hook caused problems, so admin_init it is)
	 *
	 * @since VIBE_Options 1.0
	 *
	*/
	function _set_default_options(){
		if(!get_option($this->args['opt_name'])){
			add_option($this->args['opt_name'], $this->_default_values());
		}
		$this->options = get_option($this->args['opt_name']);
	}//function
	
	
	/**
	 * Class Theme Options Page Function, creates main options page.
	 *
	 * @since VIBE_Options 1.0
	*/
	function _options_page(){
		if($this->args['page_type'] == 'submenu'){
			if(!isset($this->args['page_parent']) || empty($this->args['page_parent'])){
				$this->args['page_parent'] = 'themes.php';
			}
			$this->page = add_theme_page(
							$this->args['page_parent'],
							$this->args['page_title'], 
							$this->args['menu_title'], 
							$this->args['page_cap'], 
							$this->args['page_slug'], 
							array(&$this, '_options_page_html')
						);
		}else{
			$this->page = add_menu_page(
							$this->args['page_title'], 
							$this->args['menu_title'], 
							$this->args['page_cap'], 
							$this->args['page_slug'], 
							array(&$this, '_options_page_html'),
							$this->args['menu_icon'],
							$this->args['page_position']
						);
		

		if(true === $this->args['allow_sub_menu']){
						
			//this is needed to remove the top level menu item from showing in the submenu
			add_submenu_page($this->args['page_slug'],$this->args['page_title'],'',$this->args['page_cap'],$this->args['page_slug'],function(){return null;});
						
						
			foreach($this->sections as $k => $section){
							
				add_submenu_page(
						$this->args['page_slug'],
						$section['title'], 
						$section['title'], 
						$this->args['page_cap'], 
						$this->args['page_slug'].'&tab='.$k, 
						function(){return null;}
				);
					
			}
			
			if(true === $this->args['show_import_export']){
				
				add_submenu_page(
						$this->args['page_slug'],
						__('Import / Export', 'vibe'), 
						__('Import / Export', 'vibe'), 
						$this->args['page_cap'], 
						$this->args['page_slug'].'&tab=import_export_default', 
						function(){return null;}
				);
					
			}//if
						

			foreach($this->extra_tabs as $k => $tab){
				
				add_submenu_page(
						$this->args['page_slug'],
						$tab['title'], 
						$tab['title'], 
						$this->args['page_cap'], 
						$this->args['page_slug'].'&tab='.$k, 
						function(){return null;}
				);
				
			}

			if(true === $this->args['dev_mode']){
						
				add_submenu_page(
						$this->args['page_slug'],
						__('Dev Mode Info', 'vibe'), 
						__('Dev Mode Info', 'vibe'), 
						$this->args['page_cap'], 
						$this->args['page_slug'].'&tab=dev_mode_default', 
						function(){return null;}
				);
				
			}//if

		}//if			
						
			
		}//else

		add_action('admin_print_styles-'.$this->page, array($this, '_enqueue'));
		add_action('load-'.$this->page, array($this, '_load_page'));
	}//function	
	
	

	/**
	 * enqueue styles/js for theme page
	 *
	 * @since VIBE_Options 1.0
	*/
	function _enqueue(){
		
		wp_register_style(
				'vibe-opts-css', 
				$this->url.'css/options.css',
				array('farbtastic'),
				time(),
				'all'
			);
		
		
			
			
		if(false === $this->args['stylesheet_override']){
			wp_enqueue_style('vibe-opts-css');
		}
		
		
		wp_enqueue_script(
			'vibe-opts-js', 
			$this->url.'js/options.js', 
			array('jquery'),
			time(),
			true
		);
		
		wp_localize_script('vibe-opts-js', 'vibe_opts', array('reset_confirm' => __('Are you sure? Resetting will loose all custom values.', 'vibe'), 'opt_name' => $this->args['opt_name']));
		
		do_action('vibe-opts-enqueue-'.$this->args['opt_name']);
		
		
		foreach($this->sections as $k => $section){
			
			if(isset($section['fields'])){
				
				foreach($section['fields'] as $fieldk => $field){
					
					if(isset($field['type'])){
					
						$field_class = 'VIBE_Options_'.$field['type'];
						
						if(!class_exists($field_class)){
							require_once($this->dir.'fields/'.$field['type'].'/field_'.$field['type'].'.php');
						}//if
				
						if(class_exists($field_class) && method_exists($field_class, 'enqueue')){
							$enqueue = new $field_class('','',$this);
							$enqueue->enqueue();
						}//if
						
					}//if type
					
				}//foreach
			
			}//if fields
			
		}//foreach
			
		
	}//function
	
	/**
	 * Download the options file, or display it
	 *
	 * @since VIBE_Options 1.0.1
	*/
	function _download_options(){
		//-'.$this->args['opt_name']
		if(!isset($_GET['secret']) || $_GET['secret'] != md5(AUTH_KEY.SECURE_AUTH_KEY)){wp_die('Invalid Secret for options use');exit;}
		if(!isset($_GET['feed'])){wp_die('No Feed Defined');exit;}
		$backup_options = get_option(str_replace('vibeopts-','',$_GET['feed']));
		$backup_options['vibe-opts-backup'] = '1';
		$content = '###'.serialize($backup_options).'###';
		
		
		if(isset($_GET['action']) && $_GET['action'] == 'download_options'){
			header('Content-Description: File Transfer');
			header('Content-type: application/txt');
			header('Content-Disposition: attachment; filename="'.str_replace('vibeopts-','',$_GET['feed']).'_options_'.date('d-m-Y').'.txt"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			echo $content;
			exit;
		}else{
			echo $content;
			exit;
		}
	}
	
	
	
	
	/**
	 * show page help
	 *
	 * @since VIBE_Options 1.0
	*/
	function _load_page(){
		
		//do admin head action for this page
		add_action('admin_head', array(&$this, 'admin_head'));
		
		//do admin footer text hook
		add_filter('admin_footer_text', array(&$this, 'admin_footer_text'));
		
		$screen = get_current_screen();
		
		if(is_array($this->args['help_tabs'])){
			foreach($this->args['help_tabs'] as $tab){
				$screen->add_help_tab($tab);
			}//foreach
		}//if
		
		if($this->args['help_sidebar'] != ''){
			$screen->set_help_sidebar($this->args['help_sidebar']);
		}//if
		
		do_action('vibe-opts-load-page-'.$this->args['opt_name'], $screen);
		
	}//function
	
	
	/**
	 * do action vibe-opts-admin-head for theme options page
	 *
	 * @since VIBE_Options 1.0
	*/
	function admin_head(){
		
		do_action('vibe-opts-admin-head-'.$this->args['opt_name'], $this);
		
	}//function
	
	
	function admin_footer_text($footer_text){
		return $this->args['footer_credit'];
	}//function
	
	
	
	
	/**
	 * Register Option for use
	 *
	 * @since VIBE_Options 1.0
	*/
	function _register_setting(){
		
		register_setting($this->args['opt_name'].'_group', $this->args['opt_name'], array(&$this,'_validate_options'));
		foreach($this->sections as $k => $section){
			
			add_settings_section($k.'_section', $section['title'], array(&$this, '_section_desc'), $k.'_section_group');
			
			if(isset($section['fields'])){
			
				foreach($section['fields'] as $fieldk => $field){
					
					if(isset($field['title'])){
					
						$th = (isset($field['sub_desc']))?$field['title'].'<span class="description">'.$field['sub_desc'].'</span>':$field['title'];
					}else{
						$th = '';
					}
					
					add_settings_field($fieldk.'_field', $th, array(&$this,'_field_input'), $k.'_section_group', $k.'_section', $field); // checkbox
					
				}//foreach
			
			}//if(isset($section['fields'])){
			
		}//foreach
	}//function
	
	
	
	/**
	 * Validate the Options options before insertion
	 *
	 * @since VIBE_Options 1.0
	*/
	function _validate_options($plugin_options){
		
		set_transient('vibe-opts-saved', '1', 1000 );
		if(!empty($plugin_options['import'])){
			
			if($plugin_options['import_code'] != ''){
				$import = $plugin_options['import_code'];
			}elseif($plugin_options['import_link'] != ''){
				$import = wp_remote_retrieve_body( wp_remote_get($plugin_options['import_link']) );
			}
			
			$imported_options = unserialize(trim($import,'###'));
			if(is_array($imported_options) && isset($imported_options['vibe-opts-backup']) && $imported_options['vibe-opts-backup'] == '1'){
				$imported_options['imported'] = 1;
				return $imported_options;
			}
			
			
		}
		
		if(!empty($plugin_options['defaults'])){
			$plugin_options = $this->_default_values();
			return $plugin_options;
		}//if set defaults

		
		//validate fields (if needed)
		$plugin_options = $this->_validate_values($plugin_options, $this->options);
		
		if($this->errors){
			set_transient('vibe-opts-errors-'.$this->args['opt_name'], $this->errors, 1000 );		
		}//if errors
		
		if($this->warnings){
			set_transient('vibe-opts-warnings-'.$this->args['opt_name'], $this->warnings, 1000 );		
		}//if errors
		
		do_action('vibe-opts-options-validate-'.$this->args['opt_name'], $plugin_options, $this->options);
		
		
		unset($plugin_options['defaults']);
		unset($plugin_options['import']);
		unset($plugin_options['import_code']);
		unset($plugin_options['import_link']);
		
		return $plugin_options;	
	
	}//function
	
	
	
	
	/**
	 * Validate values from options form (used in settings api validate function)
	 * calls the custom validation class for the field so authors can override with custom classes
	 *
	 * @since VIBE_Options 1.0
	*/
	function _validate_values($plugin_options, $options){
		foreach($this->sections as $k => $section){
			
			if(isset($section['fields'])){
			
				foreach($section['fields'] as $fieldk => $field){
					$field['section_id'] = $k;
					
					if(isset($field['type']) && $field['type'] == 'multi_text'){continue;}//we cant validate this yet
					
					if(!isset($plugin_options[$field['id']]) || $plugin_options[$field['id']] == ''){
						continue;
					}
					
					//force validate of custom filed types
					
					if(isset($field['type']) && !isset($field['validate'])){
						if($field['type'] == 'color' || $field['type'] == 'color_gradient'){
							$field['validate'] = 'color';
						}elseif($field['type'] == 'date'){
							$field['validate'] = 'date';
						}
					}//if
	
					if(isset($field['validate'])){
						$validate = 'VIBE_Validation_'.$field['validate'];
						
						if(!class_exists($validate)){
							require_once($this->dir.'validation/'.$field['validate'].'/validation_'.$field['validate'].'.php');
						}//if
						
						if(class_exists($validate)){
							$validation = new $validate($field, $plugin_options[$field['id']], $options[$field['id']]);
							$plugin_options[$field['id']] = $validation->value;
							if(isset($validation->error)){
								$this->errors[] = $validation->error;
							}
							if(isset($validation->warning)){
								$this->warnings[] = $validation->warning;
							}
							continue;
						}//if
					}//if
					
					
					if(isset($field['validate_callback']) && function_exists($field['validate_callback'])){
						
						$callbackvalues = call_user_func($field['validate_callback'], $field, $plugin_options[$field['id']], $options[$field['id']]);
						$plugin_options[$field['id']] = $callbackvalues['value'];
						if(isset($callbackvalues['error'])){
							$this->errors[] = $callbackvalues['error'];
						}//if
						if(isset($callbackvalues['warning'])){
							$this->warnings[] = $callbackvalues['warning'];
						}//if
						
					}//if
					
					
				}//foreach
			
			}//if(isset($section['fields'])){
			
		}//foreach
		return $plugin_options;
	}//function
	
	
	
	
	
	
	
	
	/**
	 * HTML OUTPUT.
	 *
	 * @since VIBE_Options 1.0
	*/
	function _options_page_html(){
		echo '<div class="wrap vibe-options-wrap">';
			echo (isset($this->args['intro_text']))?$this->args['intro_text']:'';
			
			do_action('vibe-opts-page-before-form-'.$this->args['opt_name']);

			echo '<form method="post" action="options.php" enctype="multipart/form-data" id="vibe-opts-form-wrapper">';
				settings_fields($this->args['opt_name'].'_group');
				
				$this->options['last_tab'] = (isset($_GET['tab']) && !get_transient('vibe-opts-saved'))?$_GET['tab']:$this->options['last_tab'];
				
				echo '<input type="hidden" id="last_tab" name="'.$this->args['opt_name'].'[last_tab]" value="'.$this->options['last_tab'].'" />';
				
				echo '<div id="vibe-opts-header">';
                                echo '<div class="vibe-logo"><img src="'.$this->url.'img/vibe-logo.png" alt="brought to you by VibeThemes" /></div>';
                                submit_button('', 'vibe-button vibe-save', '', false);
				echo '<div class="clear"></div><!--clearfix-->';
				echo '</div>';
				
					if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' && get_transient('vibe-opts-saved') == '1'){
						if(isset($this->options['imported']) && $this->options['imported'] == 1){
							echo '<div id="vibe-opts-imported">'.apply_filters('vibe-opts-imported-text-'.$this->args['opt_name'], __('<strong>Settings Imported!</strong>', 'vibe')).'</div>';
						}else{
							echo '<div id="vibe-opts-save">'.apply_filters('vibe-opts-saved-text-'.$this->args['opt_name'], __('<strong>Settings Saved!</strong>', 'vibe')).'</div>';
						}
						delete_transient('vibe-opts-saved');
					}
					echo '<div id="vibe-opts-save-warn">'.apply_filters('vibe-opts-changed-text-'.$this->args['opt_name'], __('<strong>Settings have changed!, you should save them!</strong>', 'vibe')).'</div>';
					echo '<div id="vibe-opts-field-errors">'.__('<strong><span></span> error(s) were found!</strong>', 'vibe').'</div>';
					
					echo '<div id="vibe-opts-field-warnings">'.__('<strong><span></span> warning(s) were found!</strong>', 'vibe').'</div>';
				
				echo '<div class="clear"></div><!--clearfix-->';
				
				echo '<div id="vibe-opts-sidebar">';
					echo '<ul id="vibe-opts-group-menu">';
						foreach($this->sections as $k => $section){
							$icon = (!isset($section['icon']))?'<i class="dashicons dashicons-home"></i> ':'<i class="dashicons dashicons-'.$section['icon'].'"></i>';
							echo '<li id="'.$k.'_section_group_li" class="vibe-opts-group-tab-link-li">';
								echo '<a href="javascript:void(0);" id="'.$k.'_section_group_li_a" class="vibe-opts-group-tab-link-a" data-rel="'.$k.'">'.$icon.'<span>'.$section['title'].'</span></a>';
							echo '</li>';
						}
						
						echo '<li class="divide">&nbsp;</li>';
						
						do_action('vibe-opts-after-section-menu-items-'.$this->args['opt_name'], $this);
						
						if(true === $this->args['show_import_export']){
							echo '<li id="import_export_default_section_group_li" class="vibe-opts-group-tab-link-li">';
									echo '<a href="javascript:void(0);" id="import_export_default_section_group_li_a" class="vibe-opts-group-tab-link-a" data-rel="import_export_default"><i class="icon-white icon-refresh"></i> <span>'.__('Import / Export', 'vibe').'</span></a>';
							echo '</li>';
							echo '<li class="divide">&nbsp;</li>';
						}//if
						
						
						
						
						
						foreach($this->extra_tabs as $k => $tab){
							$icon = (!isset($section['icon']))?'<i class="icon-white icon-home"></i> ':'<i class="icon-white icon-'.$section['icon'].'"></i>';
							echo '<li id="'.$k.'_section_group_li" class="vibe-opts-group-tab-link-li">';
								echo '<a href="javascript:void(0);" id="'.$k.'_section_group_li_a" class="vibe-opts-group-tab-link-a custom-tab" data-rel="'.$k.'">'.$icon.'<span>'.$tab['title'].'</span></a>';
							echo '</li>';
						}

						
						if(true === $this->args['dev_mode']){
							echo '<li id="dev_mode_default_section_group_li" class="vibe-opts-group-tab-link-li">';
									echo '<a href="javascript:void(0);" id="dev_mode_default_section_group_li_a" class="vibe-opts-group-tab-link-a custom-tab" data-rel="dev_mode_default"><i class="icon-white icon-info-sign"></i> <span>'.__('Dev Mode Info', 'vibe').'</span></a>';
							echo '</li>';
						}//if
						
					echo '</ul>';
				echo '</div>';
				
				echo '<div id="vibe-opts-main">';
				
					foreach($this->sections as $k => $section){
						echo '<div id="'.$k.'_section_group'.'" class="vibe-opts-group-tab">';
							do_settings_sections($k.'_section_group');
						echo '</div>';
					}					
					
					
					if(true === $this->args['show_import_export']){
						echo '<div id="import_export_default_section_group'.'" class="vibe-opts-group-tab">';
							echo '<h3>'.__('Import / Export Options', 'vibe').'</h3>';
							
							echo '<h4>'.__('Import Options', 'vibe').'</h4>';
							
							echo '<p><a href="javascript:void(0);" id="vibe-opts-import-code-button" class="button-secondary">'.__('Import from file','vibe').'</a> <a href="javascript:void(0);" id="vibe-opts-import-link-button" class="button-secondary">'.__('Import from URL','vibe').'</a></p>';
							
							echo '<div id="vibe-opts-import-code-wrapper">';
							
								echo '<div class="vibe-opts-section-desc">';
				
									echo '<p class="description" id="import-code-description">'.apply_filters('vibe-opts-import-file-description',__('Input your backup file below and hit Import to restore your sites options from a backup.', 'vibe')).'</p>';
								
								echo '</div>';
								
								echo '<textarea id="import-code-value" name="'.$this->args['opt_name'].'[import_code]" class="large-text" rows="8"></textarea>';
							
							echo '</div>';
							
							
							echo '<div id="vibe-opts-import-link-wrapper">';
							
								echo '<div class="vibe-opts-section-desc">';
									
									echo '<p class="description" id="import-link-description">'.apply_filters('vibe-opts-import-link-description',__('Input the URL to another sites options set and hit Import to load the options from that site.', 'vibe')).'</p>';
								
								echo '</div>';

								echo '<input type="text" id="import-link-value" name="'.$this->args['opt_name'].'[import_link]" class="large-text" value="" />';
							
							echo '</div>';
							
							
							
							echo '<p id="vibe-opts-import-action"><input type="submit" id="vibe-opts-import" name="'.$this->args['opt_name'].'[import]" class="button-primary" value="'.__('Import', 'vibe').'"> <span>'.apply_filters('vibe-opts-import-warning', __('WARNING! This will overwrite any existing options, please proceed with caution!', 'vibe')).'</span></p>';
							echo '<div id="import_divide"></div>';
							
							echo '<h4>'.__('Export Options', 'vibe').'</h4>';
							echo '<div class="vibe-opts-section-desc">';
								echo '<p class="description">'.apply_filters('vibe-opts-backup-description', __('Here you can copy/download your themes current option settings. Keep this safe as you can use it as a backup should anything go wrong. Or you can use it to restore your settings on this site (or any other site). You also have the handy option to copy the link to yours sites settings. Which you can then use to duplicate on another site', 'vibe')).'</p>';
							echo '</div>';
							
								echo '<p><a href="javascript:void(0);" id="vibe-opts-export-code-copy" class="button-secondary">Copy</a> <a href="'.esc_url(add_query_arg(array('feed' => 'vibeopts-'.$this->args['opt_name'], 'action' => 'download_options', 'secret' => md5(AUTH_KEY.SECURE_AUTH_KEY)), site_url())).'" id="vibe-opts-export-code-dl" class="button-primary">'.__('Download','vibe').'</a> <a href="javascript:void(0);" id="vibe-opts-export-link" class="button-secondary">'.__('Copy Link','vibe').'</a></p>';
								$backup_options = $this->options;
								$backup_options['vibe-opts-backup'] = '1';
								$encoded_options = '###'.serialize($backup_options).'###';
								echo '<textarea class="large-text" id="vibe-opts-export-code" rows="8">';print_r($encoded_options);echo '</textarea>';
								echo '<input type="text" class="large-text" id="vibe-opts-export-link-value" value="'.esc_url(add_query_arg(array('feed' => 'vibeopts-'.$this->args['opt_name'], 'secret' => md5(AUTH_KEY.SECURE_AUTH_KEY)), site_url())).'" />';
							
						echo '</div>';
					}
					
					
					
					foreach($this->extra_tabs as $k => $tab){
						echo '<div id="'.$k.'_section_group'.'" class="vibe-opts-group-tab">';
						echo '<h3>'.$tab['title'].'</h3>';
						echo $tab['content'];
						echo '</div>';
					}

					
					
					if(true === $this->args['dev_mode']){
						echo '<div id="dev_mode_default_section_group'.'" class="vibe-opts-group-tab">';
							echo '<h3>'.__('Dev Mode Info', 'vibe').'</h3>';
							echo '<div class="vibe-opts-section-desc">';
							echo '<textarea class="large-text" rows="24">'.print_r($this, true).'</textarea>';
							echo '</div>';
						echo '</div>';
					}
					
					
					do_action('vibe-opts-after-section-items-'.$this->args['opt_name'], $this);
				
					echo '<div class="clear"></div><!--clearfix-->';
				echo '</div>';
				echo '<div class="clear"></div><!--clearfix-->';
				
				echo '<div id="vibe-opts-footer">';
				
					if(isset($this->args['share_icons'])){
						echo '<div id="vibe-opts-share">';
						foreach($this->args['share_icons'] as $link){
							echo '<a href="'.$link['link'].'" title="'.$link['title'].'" target="_blank"><img src="'.$link['img'].'"/></a>';
						}
						echo '</div>';
					}
					
					submit_button('', 'vibe-button vibe-save', '', false);
					submit_button(__('Reset to Defaults', 'vibe'), 'vibe-button', $this->args['opt_name'].'[defaults]', false);
					echo '<div class="clear"></div><!--clearfix-->';
				echo '</div>';
			
			echo '</form>';
			
			do_action('vibe-opts-page-after-form-'.$this->args['opt_name']);
			
			echo '<div class="clear"></div><!--clearfix-->';	
		echo '</div><!--wrap-->';

	}//function
	
	
	
	/**
	 * JS to display the errors on the page
	 *
	 * @since VIBE_Options 1.0
	*/	
	function _errors_js(){
		
		if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' && get_transient('vibe-opts-errors-'.$this->args['opt_name'])){
				$errors = get_transient('vibe-opts-errors-'.$this->args['opt_name']);
				$section_errors = array();
				foreach($errors as $error){
					$section_errors[$error['section_id']] = (isset($section_errors[$error['section_id']]))?$section_errors[$error['section_id']]:0;
					$section_errors[$error['section_id']]++;
				}
				
				
				echo '<script type="text/javascript">';
					echo 'jQuery(document).ready(function(){';
						echo 'jQuery("#vibe-opts-field-errors span").html("'.count($errors).'");';
						echo 'jQuery("#vibe-opts-field-errors").show();';
						
						foreach($section_errors as $sectionkey => $section_error){
							echo 'jQuery("#'.$sectionkey.'_section_group_li_a").append("<span class=\"vibe-opts-menu-error\">'.$section_error.'</span>");';
						}
						
						foreach($errors as $error){
							echo 'jQuery("#'.$error['id'].'").addClass("vibe-opts-field-error");';
							echo 'jQuery("#'.$error['id'].'").closest("td").append("<span class=\"vibe-opts-th-error\">'.$error['msg'].'</span>");';
						}
					echo '});';
				echo '</script>';
				delete_transient('vibe-opts-errors-'.$this->args['opt_name']);
			}
		
	}//function
	
	
	
	/**
	 * JS to display the warnings on the page
	 *
	 * @since VIBE_Options 1.0.3
	*/	
	function _warnings_js(){
		
		if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' && get_transient('vibe-opts-warnings-'.$this->args['opt_name'])){
				$warnings = get_transient('vibe-opts-warnings-'.$this->args['opt_name']);
				$section_warnings = array();
				foreach($warnings as $warning){
					$section_warnings[$warning['section_id']] = (isset($section_warnings[$warning['section_id']]))?$section_warnings[$warning['section_id']]:0;
					$section_warnings[$warning['section_id']]++;
				}
				
				
				echo '<script type="text/javascript">';
					echo 'jQuery(document).ready(function(){';
						echo 'jQuery("#vibe-opts-field-warnings span").html("'.count($warnings).'");';
						echo 'jQuery("#vibe-opts-field-warnings").show();';
						
						foreach($section_warnings as $sectionkey => $section_warning){
							echo 'jQuery("#'.$sectionkey.'_section_group_li_a").append("<span class=\"vibe-opts-menu-warning\">'.$section_warning.'</span>");';
						}
						
						foreach($warnings as $warning){
							echo 'jQuery("#'.$warning['id'].'").addClass("vibe-opts-field-warning");';
							echo 'jQuery("#'.$warning['id'].'").closest("td").append("<span class=\"vibe-opts-th-warning\">'.$warning['msg'].'</span>");';
						}
					echo '});';
				echo '</script>';
				delete_transient('vibe-opts-warnings-'.$this->args['opt_name']);
			}
		
	}//function
	
	

	
	
	/**
	 * Section HTML OUTPUT.
	 *
	 * @since VIBE_Options 1.0
	*/	
	function _section_desc($section){
		
		$id = rtrim($section['id'], '_section');
		
		if(isset($this->sections[$id]['desc']) && !empty($this->sections[$id]['desc'])) {
			echo '<div class="vibe-opts-section-desc">'.$this->sections[$id]['desc'].'</div>';
		}
		
	}//function
	
	
	
	
	/**
	 * Field HTML OUTPUT.
	 *
	 * Gets option from options array, then calls the speicfic field type class - allows extending by other devs
	 *
	 * @since VIBE_Options 1.0
	*/
	function _field_input($field){
		
		
		if(isset($field['callback']) && function_exists($field['callback'])){
			$value = (isset($this->options[$field['id']]))?$this->options[$field['id']]:'';
			do_action('vibe-opts-before-field-'.$this->args['opt_name'], $field, $value);
			call_user_func($field['callback'], $field, $value);
			do_action('vibe-opts-after-field-'.$this->args['opt_name'], $field, $value);
			return;
		}
		
		if(isset($field['type'])){
			
			$field_class = 'VIBE_Options_'.$field['type'];
			
			if(class_exists($field_class)){
				require_once($this->dir.'fields/'.$field['type'].'/field_'.$field['type'].'.php');
			}//if
			
			if(class_exists($field_class)){
				$value = (isset($this->options[$field['id']]))?$this->options[$field['id']]:'';
				do_action('vibe-opts-before-field-'.$this->args['opt_name'], $field, $value);
				$render = '';
				$render = new $field_class($field, $value, $this);
				$render->render();
				do_action('vibe-opts-after-field-'.$this->args['opt_name'], $field, $value);
			}//if
			
		}//if $field['type']
		
	}//function

	
}//class
}//if

?>