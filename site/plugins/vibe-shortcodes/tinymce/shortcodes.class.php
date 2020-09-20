<?php

// load wordpress
require_once('get_wp.php');

class vibe_shortcodes{
	var	$conf;
	var	$popup;
	var	$params;
	var	$shortcode;
	var $cparams;
	var $cshortcode;
	var $popup_title;
	var $no_preview;
	var $has_child;
	var	$output;
	var	$errors;

	// --------------------------------------------------------------------------

	function __construct( $popup ){
		if( file_exists( dirname(__FILE__) . '/config.php' ) ){
			$this->conf = dirname(__FILE__) . '/config.php';
			$this->popup = $popup;
			
			$this->formate_shortcode();
		}
		else{
			$this->append_error('Config file does not exist');
		}
	}
	
	// --------------------------------------------------------------------------
	
	function formate_shortcode(){
		// get config file
		require_once( $this->conf );
		
		if( isset( $vibe_shortcodes[$this->popup]['child_shortcode'] ) )
			$this->has_child = true;
		
		if( isset( $vibe_shortcodes ) && is_array( $vibe_shortcodes ) ){
			// get shortcode config stuff
			$this->params = $vibe_shortcodes[$this->popup]['params'];
			$this->shortcode = $vibe_shortcodes[$this->popup]['shortcode'];
			$this->popup_title = $vibe_shortcodes[$this->popup]['popup_title'];
			
			// adds stuff for js use			
			$this->append_output( "\n" . '<div id="_vibe_shortcode" class="hidden">' . $this->shortcode . '</div>' );
			$this->append_output( "\n" . '<div id="_vibe_popup" class="hidden">' . $this->popup . '</div>' );
			
			if( isset( $vibe_shortcodes[$this->popup]['no_preview'] ) && $vibe_shortcodes[$this->popup]['no_preview'] ){
				//$this->append_output( "\n" . '<div id="_vibe_preview" class="hidden">false</div>' );
				$this->no_preview = true;		
			}
			
			// filters and excutes params
			if(is_Array($this->params))
			foreach( $this->params as $pkey => $param ){
				// prefix the fields names and ids with vibe_
				$pkey = 'vibe_' . $pkey;
				if(!isset($param['desc'])){$param['desc'] =''; }
				// popup form row start
				$row_start  = '<tbody>' . "\n";
				$row_start .= '<tr class="form-row '.$pkey.'">' . "\n";
				$row_start .= '<td class="label">' . $param['label'] . '</td>' . "\n";
				$row_start .= '<td class="field">' . "\n";
				// popup form row end
				$row_end	= '<span class="vibe-form-desc">' . $param['desc'] . '</span>' . "\n";
				$row_end   .= '</td>' . "\n";
				$row_end   .= '</tr>' . "\n";					
				$row_end   .= '</tbody>' . "\n";
				
				switch( $param['type'] ){
					case 'text' :
						
						// prepare
						$output  = $row_start;
						$output .= '<input type="text" class="vibe-form-text vibe-input" name="' . $pkey . '" id="' . $pkey . '" value="' . (isset($param['std'])?$param['std']:'') . '" />' . "\n";
						$output .= $row_end;
						
						// append
						$this->append_output( $output );
						
						break;

					case 'number' :
						
						// prepare
						$output  = $row_start;
						$output .= '<input type="number" class="vibe-form-text vibe-input" name="' . $pkey . '" id="' . $pkey . '" value="' . (isset($param['std'])?$param['std']:'') . '" />' . "\n";
						$output .= $row_end;
						
						// append
						$this->append_output( $output );
						
						break;
                                            
                    case 'color' :
						
						// prepare
						$output  = $row_start;
						$output .= '<input type="text" class="vibe-form-text vibe-input popup-colorpicker" name="' . $pkey . '" id="' . $pkey . '" value="' . (isset($param['std'])?$param['std']:'') . '" />' . "\n";
						$output .= $row_end;
						
						// append
						$this->append_output( $output );
						
					break;

					case 'date' :
						
						// prepare
						$output  = $row_start;
						$output .= '<input type="datetime-local" placeholder="YYYY-MM-DD HH:MM:SS" class="vibe-form-text vibe-input date-picker-field" name="' . $pkey . '" id="' . $pkey . '" value="' . (($param['std'])?$param['std']:'') . '" />' . "\n";
						$output .= $row_end;
						
						// append
						$this->append_output( $output );
						
						break;

					 case 'slide' :
						
						// prepare
						$output  = $row_start;
						$output .= '<input type="text" class="vibe-form-text vibe-input popup-slider" name="' . $pkey . '" id="' . $pkey . '" value="' . (isset($param['std'])?$param['std']:'') . '" />' . "\n";
						$output .= '<div class="slider-range" data-min="' . $param['min'] . '" data-max="' . $param['max'] . '" data-std="' . (isset($param['std'])?$param['std']:'') . '"></div>';
                                                $output .= $row_end;
						
						// append
						$this->append_output( $output );
						
						break;   
						 	 
					case 'textarea' :
						
						// prepare
						$output  = $row_start;
						$output .= '<textarea rows="10" cols="30" name="' . $pkey . '" id="' . $pkey . '" class="vibe-form-textarea vibe-input">' . (isset($param['std'])?$param['std']:'') . '</textarea>' . "\n";
						$output .= $row_end;
						
						// append
						$this->append_output( $output );
						
						break;
					case 'conditional' :
						
						// prepare
						$output  = $row_start;
						$output .= '<select name="' . $pkey . '" id="' . $pkey . '" class="vibe-form-select vibe-input">' . "\n";
						
						foreach( $param['options'] as $value => $option ){
							$output .= '<option value="' . $value . '">' . $option . '</option>' . "\n";
						}
						
						$output .= '</select><script>jQuery(document).ready(function(){';
						if(isset($param['condition'])){
							foreach($param['condition'] as $switch_value => $hide_show_elements){
								$output .='
								if(jQuery(this).val() == "'.$switch_value.'"){';
									foreach($hide_show_elements as $element => $class){
										$output .='jQuery("tr.vibe_'.$element.'").removeClass("vibe_hide vibe_show");
										jQuery("tr.vibe_'.$element.'").addClass("'.$class.'");
										';
										if($class == 'vibe_hide'){
											$output .= 'jQuery("tr.vibe_'.$element.' .vibe-input").val("");';
										}
									}
								$output .='}
								jQuery("#' . $pkey . '").on("change",function(){
									if(jQuery(this).val() == "'.$switch_value.'"){';
									foreach($hide_show_elements as $element => $class){
										$output .='jQuery("tr.vibe_'.$element.'").removeClass("vibe_hide vibe_show");
										jQuery("tr.vibe_'.$element.'").addClass("'.$class.'");';	
										if($class == 'vibe_hide'){
											$output .= 'jQuery("tr.vibe_'.$element.' .vibe-input").val("");';
										}
									}
									$output .='}
								});';		
							}
							$output .='});';
						}
						
						$output.='</script>' . "\n";
						$output .= $row_end;
						
						// append
						$this->append_output( $output );
						
						break;
					case 'select' :
						
						// prepare
						$output  = $row_start;
						$output .= '<select name="' . $pkey . '" id="' . $pkey . '" class="vibe-form-select vibe-input">' . "\n";
						
						foreach( $param['options'] as $value => $option ){
							$output .= '<option value="' . $value . '">' . $option . '</option>' . "\n";
						}
						
						$output .= '</select>' . "\n";
						$output .= $row_end;
						
						// append
						$this->append_output( $output );
						
						break;
					case 'multiselect' :
						
						// prepare
						$output  = $row_start;
						$output .= '<select name="' . $pkey . '" id="' . $pkey . '" class="vibe-form-select vibe-input" multiple>' . "\n";
						
						foreach( $param['options'] as $value => $option ){
							$output .= '<option value="' . $value . '">' . $option . '</option>' . "\n";
						}
						
						$output .= '</select>' . "\n";
						$output .= $row_end;
						
						// append
						$this->append_output( $output );
						
						break;
					case 'select_hide' :
						
						// prepare
						$output  = $row_start;
						$output .= '<select name="' . $pkey . '" id="' . $pkey . '" class="vibe-form-select-hide vibe-input" rel-hide="'.$param['level'].'">' . "\n";
						
						foreach( $param['options'] as $value => $option ){
							$output .= '<option value="' . $value . '">' . $option . '</option>' . "\n";
						}
						
						$output .= '</select>' . "\n";
						$output .= $row_end;
						
						// append
						$this->append_output( $output );
						
					break;

					case 'checkbox' :
						
						// prepare
						$output  = $row_start;
						$output .= '<label for="' . $pkey . '" class="vibe-form-checkbox">' . "\n";
						$output .= '<input type="checkbox" class="vibe-input" name="' . $pkey . '" id="' . $pkey . '" ' . ( $param['std'] ? 'checked' : '' ) . ' />' . "\n";
						$output .= ' ' . $param['checkbox_text'] . '</label>' . "\n";
						$output .= $row_end;
						
						// append
						$this->append_output( $output );
						
					break; 
					case 'socialicon' :
					case 'icon' :
                     	$output  = $row_start;
                   		$output .='<ul class="the-icons unstyled">
									<li>
									<i class="icon-elusive-icons"></i>
									<span class="i-name">icon-elusive-icons</span>
									</li>
									<li>
									<i class="icon-elusive-icons-1"></i>
									<span class="i-name">icon-elusive-icons-1</span>
									</li>
									<li>
									<i class="icon-elusive-icons-2"></i>
									<span class="i-name">icon-elusive-icons-2</span>
									</li>
									<li>
									<i class="icon-elusive-icons-3"></i>
									<span class="i-name">icon-elusive-icons-3</span>
									</li>
									<li>
									<i class="icon-elusive-icons-4"></i>
									<span class="i-name">icon-elusive-icons-4</span>
									</li>
									<li>
									<i class="icon-elusive-icons-5"></i>
									<span class="i-name">icon-elusive-icons-5</span>
									</li>
									<li>
									<i class="icon-elusive-icons-6"></i>
									<span class="i-name">icon-elusive-icons-6</span>
									</li>
									<li>
									<i class="icon-elusive-icons-7"></i>
									<span class="i-name">icon-elusive-icons-7</span>
									</li>
									<li>
									<i class="icon-crown"></i>
									<span class="i-name">icon-crown</span>
									</li>
									<li>
									<i class="icon-burst"></i>
									<span class="i-name">icon-burst</span>
									</li>
									<li>
									<i class="icon-anchor"></i>
									<span class="i-name">icon-anchor</span>
									</li>
									<li>
									<i class="icon-dollar"></i>
									<span class="i-name">icon-dollar</span>
									</li>
									<li>
									<i class="icon-dollar-bill"></i>
									<span class="i-name">icon-dollar-bill</span>
									</li>
									<li>
									<i class="icon-foot"></i>
									<span class="i-name">icon-foot</span>
									</li>
									<li>
									<i class="icon-hearing-aid"></i>
									<span class="i-name">icon-hearing-aid</span>
									</li>
									<li>
									<i class="icon-guide-dog"></i>
									<span class="i-name">icon-guide-dog</span>
									</li>
									<li>
									<i class="icon-first-aid"></i>
									<span class="i-name">icon-first-aid</span>
									</li>
									<li>
									<i class="icon-paint-bucket"></i>
									<span class="i-name">icon-paint-bucket</span>
									</li>
									<li>
									<i class="icon-pencil"></i>
									<span class="i-name">icon-pencil</span>
									</li>
									<li>
									<i class="icon-paw"></i>
									<span class="i-name">icon-paw</span>
									</li>
									<li>
									<i class="icon-paperclip"></i>
									<span class="i-name">icon-paperclip</span>
									</li>
									<li>
									<i class="icon-pound"></i>
									<span class="i-name">icon-pound</span>
									</li>
									<li>
									<i class="icon-shopping-cart"></i>
									<span class="i-name">icon-shopping-cart</span>
									</li>
									<li>
									<i class="icon-sheriff-badge"></i>
									<span class="i-name">icon-sheriff-badge</span>
									</li>
									<li>
									<i class="icon-shield"></i>
									<span class="i-name">icon-shield</span>
									</li>
									<li>
									<i class="icon-trees"></i>
									<span class="i-name">icon-trees</span>
									</li>
									<li>
									<i class="icon-trophy"></i>
									<span class="i-name">icon-trophy</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont"></i>
									<span class="i-name">icon-fontawesome-webfont</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-1"></i>
									<span class="i-name">icon-fontawesome-webfont-1</span>
									</li>
									<li>
									<i class="icon-address"></i>
									<span class="i-name">icon-address</span>
									</li>
									<li>
									<i class="icon-adjust"></i>
									<span class="i-name">icon-adjust</span>
									</li>
									<li>
									<i class="icon-air"></i>
									<span class="i-name">icon-air</span>
									</li>
									<li>
									<i class="icon-alert"></i>
									<span class="i-name">icon-alert</span>
									</li>
									<li>
									<i class="icon-archive"></i>
									<span class="i-name">icon-archive</span>
									</li>
									<li>
									<i class="icon-battery"></i>
									<span class="i-name">icon-battery</span>
									</li>
									<li>
									<i class="icon-behance"></i>
									<span class="i-name">icon-behance</span>
									</li>
									<li>
									<i class="icon-bell"></i>
									<span class="i-name">icon-bell</span>
									</li>
									<li>
									<i class="icon-block"></i>
									<span class="i-name">icon-block</span>
									</li>
									<li>
									<i class="icon-book"></i>
									<span class="i-name">icon-book</span>
									</li>
									<li>
									<i class="icon-camera"></i>
									<span class="i-name">icon-camera</span>
									</li>
									<li>
									<i class="icon-cancel"></i>
									<span class="i-name">icon-cancel</span>
									</li>
									<li>
									<i class="icon-cancel-circled"></i>
									<span class="i-name">icon-cancel-circled</span>
									</li>
									<li>
									<i class="icon-cancel-squared"></i>
									<span class="i-name">icon-cancel-squared</span>
									</li>
									<li>
									<i class="icon-cc"></i>
									<span class="i-name">icon-cc</span>
									</li>
									<li>
									<i class="icon-cc-share"></i>
									<span class="i-name">icon-cc-share</span>
									</li>
									<li>
									<i class="icon-cc-zero"></i>
									<span class="i-name">icon-cc-zero</span>
									</li>
									<li>
									<i class="icon-ccw"></i>
									<span class="i-name">icon-ccw</span>
									</li>
									<li>
									<i class="icon-cd"></i>
									<span class="i-name">icon-cd</span>
									</li>
									<li>
									<i class="icon-chart-area"></i>
									<span class="i-name">icon-chart-area</span>
									</li>
									<li>
									<i class="icon-screen"></i>
									<span class="i-name">icon-screen</span>
									</li>
									<li>
									<i class="icon-delicious"></i>
									<span class="i-name">icon-delicious</span>
									</li>
									<li>
									<i class="icon-instagram"></i>
									<span class="i-name">icon-instagram</span>
									</li>
									<li>
									<i class="icon-alarm"></i>
									<span class="i-name">icon-alarm</span>
									</li>
									<li>
									<i class="icon-envelope"></i>
									<span class="i-name">icon-envelope</span>
									</li>
									<li>
									<i class="icon-chat"></i>
									<span class="i-name">icon-chat</span>
									</li>
									<li>
									<i class="icon-inbox-alt"></i>
									<span class="i-name">icon-inbox-alt</span>
									</li>
									<li>
									<i class="icon-calculator"></i>
									<span class="i-name">icon-calculator</span>
									</li>
									<li>
									<i class="icon-camera-1"></i>
									<span class="i-name">icon-camera-1</span>
									</li>
									<li>
									<i class="icon-brightness-half"></i>
									<span class="i-name">icon-brightness-half</span>
									</li>
									<li>
									<i class="icon-list"></i>
									<span class="i-name">icon-list</span>
									</li>
									<li>
									<i class="icon-spinner"></i>
									<span class="i-name">icon-spinner</span>
									</li>
									<li>
									<i class="icon-windows"></i>
									<span class="i-name">icon-windows</span>
									</li>
									<li>
									<i class="icon-comments"></i>
									<span class="i-name">icon-comments</span>
									</li>
									<li>
									<i class="icon-rewind"></i>
									<span class="i-name">icon-rewind</span>
									</li>
									<li>
									<i class="icon-light-bulb"></i>
									<span class="i-name">icon-light-bulb</span>
									</li>
									<li>
									<i class="icon-iphone"></i>
									<span class="i-name">icon-iphone</span>
									</li>
									<li>
									<i class="icon-heart"></i>
									<span class="i-name">icon-heart</span>
									</li>
									<li>
									<i class="icon-calendar"></i>
									<span class="i-name">icon-calendar</span>
									</li>
									<li>
									<i class="icon-task"></i>
									<span class="i-name">icon-task</span>
									</li>
									<li>
									<i class="icon-store"></i>
									<span class="i-name">icon-store</span>
									</li>
									<li>
									<i class="icon-sound"></i>
									<span class="i-name">icon-sound</span>
									</li>
									<li>
									<i class="icon-fork-and-spoon"></i>
									<span class="i-name">icon-fork-and-spoon</span>
									</li>
									<li>
									<i class="icon-grid"></i>
									<span class="i-name">icon-grid</span>
									</li>
									<li>
									<i class="icon-portfolio"></i>
									<span class="i-name">icon-portfolio</span>
									</li>
									<li>
									<i class="icon-pin-alt"></i>
									<span class="i-name">icon-pin-alt</span>
									</li>
									<li>
									<i class="icon-question"></i>
									<span class="i-name">icon-question</span>
									</li>
									<li>
									<i class="icon-cmd"></i>
									<span class="i-name">icon-cmd</span>
									</li>
									<li>
									<i class="icon-newspaper-alt"></i>
									<span class="i-name">icon-newspaper-alt</span>
									</li>
									<li>
									<i class="icon-moon"></i>
									<span class="i-name">icon-moon</span>
									</li>
									<li>
									<i class="icon-home"></i>
									<span class="i-name">icon-home</span>
									</li>
									<li>
									<i class="icon-sound-alt"></i>
									<span class="i-name">icon-sound-alt</span>
									</li>
									<li>
									<i class="icon-sound-off"></i>
									<span class="i-name">icon-sound-off</span>
									</li>
									<li>
									<i class="icon-ipad"></i>
									<span class="i-name">icon-ipad</span>
									</li>
									<li>
									<i class="icon-stop"></i>
									<span class="i-name">icon-stop</span>
									</li>
									<li>
									<i class="icon-circle-full"></i>
									<span class="i-name">icon-circle-full</span>
									</li>
									<li>
									<i class="icon-forward"></i>
									<span class="i-name">icon-forward</span>
									</li>
									<li>
									<i class="icon-exclamation"></i>
									<span class="i-name">icon-exclamation</span>
									</li>
									<li>
									<i class="icon-settings"></i>
									<span class="i-name">icon-settings</span>
									</li>
									<li>
									<i class="icon-newspaper"></i>
									<span class="i-name">icon-newspaper</span>
									</li>
									<li>
									<i class="icon-grid-alt"></i>
									<span class="i-name">icon-grid-alt</span>
									</li>
									<li>
									<i class="icon-clock"></i>
									<span class="i-name">icon-clock</span>
									</li>
									<li>
									<i class="icon-pause"></i>
									<span class="i-name">icon-pause</span>
									</li>
									<li>
									<i class="icon-globe"></i>
									<span class="i-name">icon-globe</span>
									</li>
									<li>
									<i class="icon-clipboard"></i>
									<span class="i-name">icon-clipboard</span>
									</li>
									<li>
									<i class="icon-attachment"></i>
									<span class="i-name">icon-attachment</span>
									</li>
									<li>
									<i class="icon-forbid-1"></i>
									<span class="i-name">icon-forbid-1</span>
									</li>
									<li>
									<i class="icon-circle-half"></i>
									<span class="i-name">icon-circle-half</span>
									</li>
									<li>
									<i class="icon-inbox"></i>
									<span class="i-name">icon-inbox</span>
									</li>
									<li>
									<i class="icon-fork-and-knife"></i>
									<span class="i-name">icon-fork-and-knife</span>
									</li>
									<li>
									<i class="icon-brightness"></i>
									<span class="i-name">icon-brightness</span>
									</li>
									<li>
									<i class="icon-browser"></i>
									<span class="i-name">icon-browser</span>
									</li>
									<li>
									<i class="icon-hyperlink"></i>
									<span class="i-name">icon-hyperlink</span>
									</li>
									<li>
									<i class="icon-in-alt"></i>
									<span class="i-name">icon-in-alt</span>
									</li>
									<li>
									<i class="icon-menu"></i>
									<span class="i-name">icon-menu</span>
									</li>
									<li>
									<i class="icon-compose"></i>
									<span class="i-name">icon-compose</span>
									</li>
									<li>
									<i class="icon-anchor-1"></i>
									<span class="i-name">icon-anchor-1</span>
									</li>
									<li>
									<i class="icon-gallary"></i>
									<span class="i-name">icon-gallary</span>
									</li>
									<li>
									<i class="icon-cloud"></i>
									<span class="i-name">icon-cloud</span>
									</li>
									<li>
									<i class="icon-pin"></i>
									<span class="i-name">icon-pin</span>
									</li>
									<li>
									<i class="icon-play"></i>
									<span class="i-name">icon-play</span>
									</li>
									<li>
									<i class="icon-tag-stroke"></i>
									<span class="i-name">icon-tag-stroke</span>
									</li>
									<li>
									<i class="icon-tag-fill"></i>
									<span class="i-name">icon-tag-fill</span>
									</li>
									<li>
									<i class="icon-brush"></i>
									<span class="i-name">icon-brush</span>
									</li>
									<li>
									<i class="icon-bars"></i>
									<span class="i-name">icon-bars</span>
									</li>
									<li>
									<i class="icon-eject"></i>
									<span class="i-name">icon-eject</span>
									</li>
									<li>
									<i class="icon-book-1"></i>
									<span class="i-name">icon-book-1</span>
									</li>
									<li>
									<i class="icon-chart"></i>
									<span class="i-name">icon-chart</span>
									</li>
									<li>
									<i class="icon-key-fill"></i>
									<span class="i-name">icon-key-fill</span>
									</li>
									<li>
									<i class="icon-aperture-alt"></i>
									<span class="i-name">icon-aperture-alt</span>
									</li>
									<li>
									<i class="icon-book-alt"></i>
									<span class="i-name">icon-book-alt</span>
									</li>
									<li>
									<i class="icon-list-1"></i>
									<span class="i-name">icon-list-1</span>
									</li>
									<li>
									<i class="icon-map-pin-fill"></i>
									<span class="i-name">icon-map-pin-fill</span>
									</li>
									<li>
									<i class="icon-move-horizontal-alt1"></i>
									<span class="i-name">icon-move-horizontal-alt1</span>
									</li>
									<li>
									<i class="icon-headphones"></i>
									<span class="i-name">icon-headphones</span>
									</li>
									<li>
									<i class="icon-x"></i>
									<span class="i-name">icon-x</span>
									</li>
									<li>
									<i class="icon-check"></i>
									<span class="i-name">icon-check</span>
									</li>
									<li>
									<i class="icon-award-stroke"></i>
									<span class="i-name">icon-award-stroke</span>
									</li>
									<li>
									<i class="icon-wrench"></i>
									<span class="i-name">icon-wrench</span>
									</li>
									<li>
									<i class="icon-sun-fill"></i>
									<span class="i-name">icon-sun-fill</span>
									</li>
									<li>
									<i class="icon-move-horizontal-alt2"></i>
									<span class="i-name">icon-move-horizontal-alt2</span>
									</li>
									<li>
									<i class="icon-left-quote"></i>
									<span class="i-name">icon-left-quote</span>
									</li>
									<li>
									<i class="icon-clock-1"></i>
									<span class="i-name">icon-clock-1</span>
									</li>
									<li>
									<i class="icon-share"></i>
									<span class="i-name">icon-share</span>
									</li>
									<li>
									<i class="icon-map-pin-stroke"></i>
									<span class="i-name">icon-map-pin-stroke</span>
									</li>
									<li>
									<i class="icon-battery-full"></i>
									<span class="i-name">icon-battery-full</span>
									</li>
									<li>
									<i class="icon-paperclip-1"></i>
									<span class="i-name">icon-paperclip-1</span>
									</li>
									<li>
									<i class="icon-beaker-alt"></i>
									<span class="i-name">icon-beaker-alt</span>
									</li>
									<li>
									<i class="icon-bolt"></i>
									<span class="i-name">icon-bolt</span>
									</li>
									<li>
									<i class="icon-at"></i>
									<span class="i-name">icon-at</span>
									</li>
									<li>
									<i class="icon-pin-1"></i>
									<span class="i-name">icon-pin-1</span>
									</li>
									<li>
									<i class="icon-cloud-1"></i>
									<span class="i-name">icon-cloud-1</span>
									</li>
									<li>
									<i class="icon-layers-alt"></i>
									<span class="i-name">icon-layers-alt</span>
									</li>
									<li>
									<i class="icon-fullscreen-exit-alt"></i>
									<span class="i-name">icon-fullscreen-exit-alt</span>
									</li>
									<li>
									<i class="icon-left-quote-alt"></i>
									<span class="i-name">icon-left-quote-alt</span>
									</li>
									<li>
									<i class="icon-move-horizontal"></i>
									<span class="i-name">icon-move-horizontal</span>
									</li>
									<li>
									<i class="icon-volume-mute"></i>
									<span class="i-name">icon-volume-mute</span>
									</li>
									<li>
									<i class="icon-undo"></i>
									<span class="i-name">icon-undo</span>
									</li>
									<li>
									<i class="icon-umbrella"></i>
									<span class="i-name">icon-umbrella</span>
									</li>
									<li>
									<i class="icon-pen-alt2"></i>
									<span class="i-name">icon-pen-alt2</span>
									</li>
									<li>
									<i class="icon-heart-stroke"></i>
									<span class="i-name">icon-heart-stroke</span>
									</li>
									<li>
									<i class="icon-list-nested"></i>
									<span class="i-name">icon-list-nested</span>
									</li>
									<li>
									<i class="icon-move-vertical"></i>
									<span class="i-name">icon-move-vertical</span>
									</li>
									<li>
									<i class="icon-info"></i>
									<span class="i-name">icon-info</span>
									</li>
									<li>
									<i class="icon-pause-1"></i>
									<span class="i-name">icon-pause-1</span>
									</li>
									<li>
									<i class="icon-move-vertical-alt1"></i>
									<span class="i-name">icon-move-vertical-alt1</span>
									</li>
									<li>
									<i class="icon-spin"></i>
									<span class="i-name">icon-spin</span>
									</li>
									<li>
									<i class="icon-pen"></i>
									<span class="i-name">icon-pen</span>
									</li>
									<li>
									<i class="icon-plus-1"></i>
									<span class="i-name">icon-plus-1</span>
									</li>
									<li>
									<i class="icon-cog"></i>
									<span class="i-name">icon-cog</span>
									</li>
									<li>
									<i class="icon-reload"></i>
									<span class="i-name">icon-reload</span>
									</li>
									<li>
									<i class="icon-heart-fill"></i>
									<span class="i-name">icon-heart-fill</span>
									</li>
									<li>
									<i class="icon-equalizer"></i>
									<span class="i-name">icon-equalizer</span>
									</li>
									<li>
									<i class="icon-article"></i>
									<span class="i-name">icon-article</span>
									</li>
									<li>
									<i class="icon-cd-1"></i>
									<span class="i-name">icon-cd-1</span>
									</li>
									<li>
									<i class="icon-link"></i>
									<span class="i-name">icon-link</span>
									</li>
									<li>
									<i class="icon-pilcrow"></i>
									<span class="i-name">icon-pilcrow</span>
									</li>
									<li>
									<i class="icon-hash"></i>
									<span class="i-name">icon-hash</span>
									</li>
									<li>
									<i class="icon-check-alt"></i>
									<span class="i-name">icon-check-alt</span>
									</li>
									<li>
									<i class="icon-key-stroke"></i>
									<span class="i-name">icon-key-stroke</span>
									</li>
									<li>
									<i class="icon-folder-stroke"></i>
									<span class="i-name">icon-folder-stroke</span>
									</li>
									<li>
									<i class="icon-first"></i>
									<span class="i-name">icon-first</span>
									</li>
									<li>
									<i class="icon-eyedropper"></i>
									<span class="i-name">icon-eyedropper</span>
									</li>
									<li>
									<i class="icon-reload-alt"></i>
									<span class="i-name">icon-reload-alt</span>
									</li>
									<li>
									<i class="icon-aperture"></i>
									<span class="i-name">icon-aperture</span>
									</li>
									<li>
									<i class="icon-rain"></i>
									<span class="i-name">icon-rain</span>
									</li>
									<li>
									<i class="icon-beaker"></i>
									<span class="i-name">icon-beaker</span>
									</li>
									<li>
									<i class="icon-bars-alt"></i>
									<span class="i-name">icon-bars-alt</span>
									</li>
									<li>
									<i class="icon-image"></i>
									<span class="i-name">icon-image</span>
									</li>
									<li>
									<i class="icon-spin-alt"></i>
									<span class="i-name">icon-spin-alt</span>
									</li>
									<li>
									<i class="icon-pen-alt-stroke"></i>
									<span class="i-name">icon-pen-alt-stroke</span>
									</li>
									<li>
									<i class="icon-brush-alt"></i>
									<span class="i-name">icon-brush-alt</span>
									</li>
									<li>
									<i class="icon-document-alt-fill"></i>
									<span class="i-name">icon-document-alt-fill</span>
									</li>
									<li>
									<i class="icon-layers"></i>
									<span class="i-name">icon-layers</span>
									</li>
									<li>
									<i class="icon-compass"></i>
									<span class="i-name">icon-compass</span>
									</li>
									<li>
									<i class="icon-unlock-stroke"></i>
									<span class="i-name">icon-unlock-stroke</span>
									</li>
									<li>
									<i class="icon-box"></i>
									<span class="i-name">icon-box</span>
									</li>
									<li>
									<i class="icon-right-quote-alt"></i>
									<span class="i-name">icon-right-quote-alt</span>
									</li>
									<li>
									<i class="icon-last"></i>
									<span class="i-name">icon-last</span>
									</li>
									<li>
									<i class="icon-award-fill"></i>
									<span class="i-name">icon-award-fill</span>
									</li>
									<li>
									<i class="icon-pen-alt-fill"></i>
									<span class="i-name">icon-pen-alt-fill</span>
									</li>
									<li>
									<i class="icon-lock-fill"></i>
									<span class="i-name">icon-lock-fill</span>
									</li>
									<li>
									<i class="icon-calendar-alt-stroke"></i>
									<span class="i-name">icon-calendar-alt-stroke</span>
									</li>
									<li>
									<i class="icon-move-vertical-alt2"></i>
									<span class="i-name">icon-move-vertical-alt2</span>
									</li>
									<li>
									<i class="icon-steering-wheel"></i>
									<span class="i-name">icon-steering-wheel</span>
									</li>
									<li>
									<i class="icon-minus"></i>
									<span class="i-name">icon-minus</span>
									</li>
									<li>
									<i class="icon-map-pin-alt"></i>
									<span class="i-name">icon-map-pin-alt</span>
									</li>
									<li>
									<i class="icon-eye"></i>
									<span class="i-name">icon-eye</span>
									</li>
									<li>
									<i class="icon-calendar-alt-fill"></i>
									<span class="i-name">icon-calendar-alt-fill</span>
									</li>
									<li>
									<i class="icon-play-alt"></i>
									<span class="i-name">icon-play-alt</span>
									</li>
									<li>
									<i class="icon-fullscreen"></i>
									<span class="i-name">icon-fullscreen</span>
									</li>
									<li>
									<i class="icon-target"></i>
									<span class="i-name">icon-target</span>
									</li>
									<li>
									<i class="icon-dial"></i>
									<span class="i-name">icon-dial</span>
									</li>
									<li>
									<i class="icon-ampersand"></i>
									<span class="i-name">icon-ampersand</span>
									</li>
									<li>
									<i class="icon-question-mark"></i>
									<span class="i-name">icon-question-mark</span>
									</li>
									<li>
									<i class="icon-moon-stroke"></i>
									<span class="i-name">icon-moon-stroke</span>
									</li>
									<li>
									<i class="icon-movie"></i>
									<span class="i-name">icon-movie</span>
									</li>
									<li>
									<i class="icon-battery-charging"></i>
									<span class="i-name">icon-battery-charging</span>
									</li>
									<li>
									<i class="icon-document-stroke"></i>
									<span class="i-name">icon-document-stroke</span>
									</li>
									<li>
									<i class="icon-document-alt-stroke"></i>
									<span class="i-name">icon-document-alt-stroke</span>
									</li>
									<li>
									<i class="icon-lightbulb"></i>
									<span class="i-name">icon-lightbulb</span>
									</li>
									<li>
									<i class="icon-calendar-1"></i>
									<span class="i-name">icon-calendar-1</span>
									</li>
									<li>
									<i class="icon-unlock-fill"></i>
									<span class="i-name">icon-unlock-fill</span>
									</li>
									<li>
									<i class="icon-battery-empty"></i>
									<span class="i-name">icon-battery-empty</span>
									</li>
									<li>
									<i class="icon-sun-stroke"></i>
									<span class="i-name">icon-sun-stroke</span>
									</li>
									<li>
									<i class="icon-chart-alt"></i>
									<span class="i-name">icon-chart-alt</span>
									</li>
									<li>
									<i class="icon-battery-half"></i>
									<span class="i-name">icon-battery-half</span>
									</li>
									<li>
									<i class="icon-lock-stroke"></i>
									<span class="i-name">icon-lock-stroke</span>
									</li>
									<li>
									<i class="icon-book-alt2"></i>
									<span class="i-name">icon-book-alt2</span>
									</li>
									<li>
									<i class="icon-loop-alt1"></i>
									<span class="i-name">icon-loop-alt1</span>
									</li>
									<li>
									<i class="icon-fullscreen-exit"></i>
									<span class="i-name">icon-fullscreen-exit</span>
									</li>
									<li>
									<i class="icon-volume"></i>
									<span class="i-name">icon-volume</span>
									</li>
									<li>
									<i class="icon-mic"></i>
									<span class="i-name">icon-mic</span>
									</li>
									<li>
									<i class="icon-right-quote"></i>
									<span class="i-name">icon-right-quote</span>
									</li>
									<li>
									<i class="icon-play-1"></i>
									<span class="i-name">icon-play-1</span>
									</li>
									<li>
									<i class="icon-folder-fill"></i>
									<span class="i-name">icon-folder-fill</span>
									</li>
									<li>
									<i class="icon-moon-fill"></i>
									<span class="i-name">icon-moon-fill</span>
									</li>
									<li>
									<i class="icon-home-1"></i>
									<span class="i-name">icon-home-1</span>
									</li>
									<li>
									<i class="icon-camera-2"></i>
									<span class="i-name">icon-camera-2</span>
									</li>
									<li>
									<i class="icon-star"></i>
									<span class="i-name">icon-star</span>
									</li>
									<li>
									<i class="icon-read-more"></i>
									<span class="i-name">icon-read-more</span>
									</li>
									<li>
									<i class="icon-document-fill"></i>
									<span class="i-name">icon-document-fill</span>
									</li>
									<li>
									<i class="icon-excel-table-1"></i>
									<span class="i-name">icon-excel-table-1</span>
									</li>
									<li>
									<i class="icon-arrow-1-up"></i>
									<span class="i-name">icon-arrow-1-up</span>
									</li>
									<li>
									<i class="icon-female-symbol"></i>
									<span class="i-name">icon-female-symbol</span>
									</li>
									<li>
									<i class="icon-delivery-transport-2"></i>
									<span class="i-name">icon-delivery-transport-2</span>
									</li>
									<li>
									<i class="icon-content-41"></i>
									<span class="i-name">icon-content-41</span>
									</li>
									<li>
									<i class="icon-clip-paper-1"></i>
									<span class="i-name">icon-clip-paper-1</span>
									</li>
									<li>
									<i class="icon-check-5"></i>
									<span class="i-name">icon-check-5</span>
									</li>
									<li>
									<i class="icon-feed-rss-2"></i>
									<span class="i-name">icon-feed-rss-2</span>
									</li>
									<li>
									<i class="icon-server-1"></i>
									<span class="i-name">icon-server-1</span>
									</li>
									<li>
									<i class="icon-harddrive"></i>
									<span class="i-name">icon-harddrive</span>
									</li>
									<li>
									<i class="icon-car"></i>
									<span class="i-name">icon-car</span>
									</li>
									<li>
									<i class="icon-direction-move-1"></i>
									<span class="i-name">icon-direction-move-1</span>
									</li>
									<li>
									<i class="icon-certificate-file"></i>
									<span class="i-name">icon-certificate-file</span>
									</li>
									<li>
									<i class="icon-analytics-file-1"></i>
									<span class="i-name">icon-analytics-file-1</span>
									</li>
									<li>
									<i class="icon-male-symbol"></i>
									<span class="i-name">icon-male-symbol</span>
									</li>
									<li>
									<i class="icon-send-to-front"></i>
									<span class="i-name">icon-send-to-front</span>
									</li>
									<li>
									<i class="icon-movie-play-file-1"></i>
									<span class="i-name">icon-movie-play-file-1</span>
									</li>
									<li>
									<i class="icon-bookmark-tag"></i>
									<span class="i-name">icon-bookmark-tag</span>
									</li>
									<li>
									<i class="icon-filled-folder-1"></i>
									<span class="i-name">icon-filled-folder-1</span>
									</li>
									<li>
									<i class="icon-check-clipboard-1"></i>
									<span class="i-name">icon-check-clipboard-1</span>
									</li>
									<li>
									<i class="icon-clouds-cloudy"></i>
									<span class="i-name">icon-clouds-cloudy</span>
									</li>
									<li>
									<i class="icon-gears-setting"></i>
									<span class="i-name">icon-gears-setting</span>
									</li>
									<li>
									<i class="icon-html"></i>
									<span class="i-name">icon-html</span>
									</li>
									<li>
									<i class="icon-palm-tree"></i>
									<span class="i-name">icon-palm-tree</span>
									</li>
									<li>
									<i class="icon-wallet-money"></i>
									<span class="i-name">icon-wallet-money</span>
									</li>
									<li>
									<i class="icon-hospital"></i>
									<span class="i-name">icon-hospital</span>
									</li>
									<li>
									<i class="icon-previous-1"></i>
									<span class="i-name">icon-previous-1</span>
									</li>
									<li>
									<i class="icon-mailbox-1"></i>
									<span class="i-name">icon-mailbox-1</span>
									</li>
									<li>
									<i class="icon-arrow-1-right"></i>
									<span class="i-name">icon-arrow-1-right</span>
									</li>
									<li>
									<i class="icon-dropbox"></i>
									<span class="i-name">icon-dropbox</span>
									</li>
									<li>
									<i class="icon-rocket"></i>
									<span class="i-name">icon-rocket</span>
									</li>
									<li>
									<i class="icon-credit-card"></i>
									<span class="i-name">icon-credit-card</span>
									</li>
									<li>
									<i class="icon-campfire"></i>
									<span class="i-name">icon-campfire</span>
									</li>
									<li>
									<i class="icon-yang-ying"></i>
									<span class="i-name">icon-yang-ying</span>
									</li>
									<li>
									<i class="icon-omg-smiley"></i>
									<span class="i-name">icon-omg-smiley</span>
									</li>
									<li>
									<i class="icon-angry-smiley"></i>
									<span class="i-name">icon-angry-smiley</span>
									</li>
									<li>
									<i class="icon-television-tv"></i>
									<span class="i-name">icon-television-tv</span>
									</li>
									<li>
									<i class="icon-camera-surveillance-1"></i>
									<span class="i-name">icon-camera-surveillance-1</span>
									</li>
									<li>
									<i class="icon-apple"></i>
									<span class="i-name">icon-apple</span>
									</li>
									<li>
									<i class="icon-content-14"></i>
									<span class="i-name">icon-content-14</span>
									</li>
									<li>
									<i class="icon-hour-glass"></i>
									<span class="i-name">icon-hour-glass</span>
									</li>
									<li>
									<i class="icon-content-7"></i>
									<span class="i-name">icon-content-7</span>
									</li>
									<li>
									<i class="icon-arrow-right-1"></i>
									<span class="i-name">icon-arrow-right-1</span>
									</li>
									<li>
									<i class="icon-image-photo-file-1"></i>
									<span class="i-name">icon-image-photo-file-1</span>
									</li>
									<li>
									<i class="icon-bus"></i>
									<span class="i-name">icon-bus</span>
									</li>
									<li>
									<i class="icon-blink-smiley"></i>
									<span class="i-name">icon-blink-smiley</span>
									</li>
									<li>
									<i class="icon-bubbles-talk-1"></i>
									<span class="i-name">icon-bubbles-talk-1</span>
									</li>
									<li>
									<i class="icon-brush-1"></i>
									<span class="i-name">icon-brush-1</span>
									</li>
									<li>
									<i class="icon-send-to-back"></i>
									<span class="i-name">icon-send-to-back</span>
									</li>
									<li>
									<i class="icon-camera-video-3"></i>
									<span class="i-name">icon-camera-video-3</span>
									</li>
									<li>
									<i class="icon-battery-low"></i>
									<span class="i-name">icon-battery-low</span>
									</li>
									<li>
									<i class="icon-movie-play-1"></i>
									<span class="i-name">icon-movie-play-1</span>
									</li>
									<li>
									<i class="icon-home-1-1"></i>
									<span class="i-name">icon-home-1-1</span>
									</li>
									<li>
									<i class="icon-cd-cover-music"></i>
									<span class="i-name">icon-cd-cover-music</span>
									</li>
									<li>
									<i class="icon-linkedin-alt"></i>
									<span class="i-name">icon-linkedin-alt</span>
									</li>
									<li>
									<i class="icon-video-1"></i>
									<span class="i-name">icon-video-1</span>
									</li>
									<li>
									<i class="icon-bookmark-star-favorite"></i>
									<span class="i-name">icon-bookmark-star-favorite</span>
									</li>
									<li>
									<i class="icon-play-1-1"></i>
									<span class="i-name">icon-play-1-1</span>
									</li>
									<li>
									<i class="icon-pause-1-1"></i>
									<span class="i-name">icon-pause-1-1</span>
									</li>
									<li>
									<i class="icon-paint-brush-2"></i>
									<span class="i-name">icon-paint-brush-2</span>
									</li>
									<li>
									<i class="icon-train"></i>
									<span class="i-name">icon-train</span>
									</li>
									<li>
									<i class="icon-happy-smiley"></i>
									<span class="i-name">icon-happy-smiley</span>
									</li>
									<li>
									<i class="icon-missile-rocket"></i>
									<span class="i-name">icon-missile-rocket</span>
									</li>
									<li>
									<i class="icon-cloud-2"></i>
									<span class="i-name">icon-cloud-2</span>
									</li>
									<li>
									<i class="icon-bookmark-file-1"></i>
									<span class="i-name">icon-bookmark-file-1</span>
									</li>
									<li>
									<i class="icon-scooter"></i>
									<span class="i-name">icon-scooter</span>
									</li>
									<li>
									<i class="icon-magnet"></i>
									<span class="i-name">icon-magnet</span>
									</li>
									<li>
									<i class="icon-letter-mail-1"></i>
									<span class="i-name">icon-letter-mail-1</span>
									</li>
									<li>
									<i class="icon-color-palette"></i>
									<span class="i-name">icon-color-palette</span>
									</li>
									<li>
									<i class="icon-content-43"></i>
									<span class="i-name">icon-content-43</span>
									</li>
									<li>
									<i class="icon-bubble-talk-1"></i>
									<span class="i-name">icon-bubble-talk-1</span>
									</li>
									<li>
									<i class="icon-content-34"></i>
									<span class="i-name">icon-content-34</span>
									</li>
									<li>
									<i class="icon-carton-milk"></i>
									<span class="i-name">icon-carton-milk</span>
									</li>
									<li>
									<i class="icon-male-user-4"></i>
									<span class="i-name">icon-male-user-4</span>
									</li>
									<li>
									<i class="icon-ink-pen"></i>
									<span class="i-name">icon-ink-pen</span>
									</li>
									<li>
									<i class="icon-camera-1-1"></i>
									<span class="i-name">icon-camera-1-1</span>
									</li>
									<li>
									<i class="icon-snow-weather"></i>
									<span class="i-name">icon-snow-weather</span>
									</li>
									<li>
									<i class="icon-refresh-reload-1"></i>
									<span class="i-name">icon-refresh-reload-1</span>
									</li>
									<li>
									<i class="icon-at-email"></i>
									<span class="i-name">icon-at-email</span>
									</li>
									<li>
									<i class="icon-umbrella-1"></i>
									<span class="i-name">icon-umbrella-1</span>
									</li>
									<li>
									<i class="icon-lock-secure-1"></i>
									<span class="i-name">icon-lock-secure-1</span>
									</li>
									<li>
									<i class="icon-hand-stop"></i>
									<span class="i-name">icon-hand-stop</span>
									</li>
									<li>
									<i class="icon-battery-half-1"></i>
									<span class="i-name">icon-battery-half-1</span>
									</li>
									<li>
									<i class="icon-text-document"></i>
									<span class="i-name">icon-text-document</span>
									</li>
									<li>
									<i class="icon-layers-1"></i>
									<span class="i-name">icon-layers-1</span>
									</li>
									<li>
									<i class="icon-paypal"></i>
									<span class="i-name">icon-paypal</span>
									</li>
									<li>
									<i class="icon-helicopter"></i>
									<span class="i-name">icon-helicopter</span>
									</li>
									<li>
									<i class="icon-content-42"></i>
									<span class="i-name">icon-content-42</span>
									</li>
									<li>
									<i class="icon-clothes-hanger"></i>
									<span class="i-name">icon-clothes-hanger</span>
									</li>
									<li>
									<i class="icon-plus-zoom"></i>
									<span class="i-name">icon-plus-zoom</span>
									</li>
									<li>
									<i class="icon-unlock"></i>
									<span class="i-name">icon-unlock</span>
									</li>
									<li>
									<i class="icon-microscope"></i>
									<span class="i-name">icon-microscope</span>
									</li>
									<li>
									<i class="icon-click-hand-1"></i>
									<span class="i-name">icon-click-hand-1</span>
									</li>
									<li>
									<i class="icon-briefcase"></i>
									<span class="i-name">icon-briefcase</span>
									</li>
									<li>
									<i class="icon-3-css"></i>
									<span class="i-name">icon-3-css</span>
									</li>
									<li>
									<i class="icon-google-plus-1"></i>
									<span class="i-name">icon-google-plus-1</span>
									</li>
									<li>
									<i class="icon-close-off-2"></i>
									<span class="i-name">icon-close-off-2</span>
									</li>
									<li>
									<i class="icon-music-file-1"></i>
									<span class="i-name">icon-music-file-1</span>
									</li>
									<li>
									<i class="icon-tree"></i>
									<span class="i-name">icon-tree</span>
									</li>
									<li>
									<i class="icon-forward-1"></i>
									<span class="i-name">icon-forward-1</span>
									</li>
									<li>
									<i class="icon-script"></i>
									<span class="i-name">icon-script</span>
									</li>
									<li>
									<i class="icon-edit-pen-1"></i>
									<span class="i-name">icon-edit-pen-1</span>
									</li>
									<li>
									<i class="icon-content-1"></i>
									<span class="i-name">icon-content-1</span>
									</li>
									<li>
									<i class="icon-cash-register"></i>
									<span class="i-name">icon-cash-register</span>
									</li>
									<li>
									<i class="icon-call-old-telephone"></i>
									<span class="i-name">icon-call-old-telephone</span>
									</li>
									<li>
									<i class="icon-hail-weather"></i>
									<span class="i-name">icon-hail-weather</span>
									</li>
									<li>
									<i class="icon-gift"></i>
									<span class="i-name">icon-gift</span>
									</li>
									<li>
									<i class="icon-square-vector-2"></i>
									<span class="i-name">icon-square-vector-2</span>
									</li>
									<li>
									<i class="icon-van"></i>
									<span class="i-name">icon-van</span>
									</li>
									<li>
									<i class="icon-male-user-3"></i>
									<span class="i-name">icon-male-user-3</span>
									</li>
									<li>
									<i class="icon-content-8"></i>
									<span class="i-name">icon-content-8</span>
									</li>
									<li>
									<i class="icon-battery-charging-1"></i>
									<span class="i-name">icon-battery-charging-1</span>
									</li>
									<li>
									<i class="icon-rewind-1"></i>
									<span class="i-name">icon-rewind-1</span>
									</li>
									<li>
									<i class="icon-check-1"></i>
									<span class="i-name">icon-check-1</span>
									</li>
									<li>
									<i class="icon-airplane"></i>
									<span class="i-name">icon-airplane</span>
									</li>
									<li>
									<i class="icon-hat-magician"></i>
									<span class="i-name">icon-hat-magician</span>
									</li>
									<li>
									<i class="icon-boat"></i>
									<span class="i-name">icon-boat</span>
									</li>
									<li>
									<i class="icon-crown-king-1"></i>
									<span class="i-name">icon-crown-king-1</span>
									</li>
									<li>
									<i class="icon-bike"></i>
									<span class="i-name">icon-bike</span>
									</li>
									<li>
									<i class="icon-sad-smiley"></i>
									<span class="i-name">icon-sad-smiley</span>
									</li>
									<li>
									<i class="icon-burning-fire"></i>
									<span class="i-name">icon-burning-fire</span>
									</li>
									<li>
									<i class="icon-thermometer"></i>
									<span class="i-name">icon-thermometer</span>
									</li>
									<li>
									<i class="icon-map-pin-5"></i>
									<span class="i-name">icon-map-pin-5</span>
									</li>
									<li>
									<i class="icon-happy-smiley-very"></i>
									<span class="i-name">icon-happy-smiley-very</span>
									</li>
									<li>
									<i class="icon-eye-view-1"></i>
									<span class="i-name">icon-eye-view-1</span>
									</li>
									<li>
									<i class="icon-cannabis-hemp"></i>
									<span class="i-name">icon-cannabis-hemp</span>
									</li>
									<li>
									<i class="icon-interface-window-1"></i>
									<span class="i-name">icon-interface-window-1</span>
									</li>
									<li>
									<i class="icon-document-file-1"></i>
									<span class="i-name">icon-document-file-1</span>
									</li>
									<li>
									<i class="icon-arrow-1-left"></i>
									<span class="i-name">icon-arrow-1-left</span>
									</li>
									<li>
									<i class="icon-nurse-user"></i>
									<span class="i-name">icon-nurse-user</span>
									</li>
									<li>
									<i class="icon-content-44"></i>
									<span class="i-name">icon-content-44</span>
									</li>
									<li>
									<i class="icon-flag-mark"></i>
									<span class="i-name">icon-flag-mark</span>
									</li>
									<li>
									<i class="icon-square-vector-1"></i>
									<span class="i-name">icon-square-vector-1</span>
									</li>
									<li>
									<i class="icon-monitor-screen-1"></i>
									<span class="i-name">icon-monitor-screen-1</span>
									</li>
									<li>
									<i class="icon-next-1"></i>
									<span class="i-name">icon-next-1</span>
									</li>
									<li>
									<i class="icon-doctor"></i>
									<span class="i-name">icon-doctor</span>
									</li>
									<li>
									<i class="icon-favorite-map-pin"></i>
									<span class="i-name">icon-favorite-map-pin</span>
									</li>
									<li>
									<i class="icon-rain-weather"></i>
									<span class="i-name">icon-rain-weather</span>
									</li>
									<li>
									<i class="icon-polaroid"></i>
									<span class="i-name">icon-polaroid</span>
									</li>
									<li>
									<i class="icon-analytics-chart-graph"></i>
									<span class="i-name">icon-analytics-chart-graph</span>
									</li>
									<li>
									<i class="icon-medal-outline-star"></i>
									<span class="i-name">icon-medal-outline-star</span>
									</li>
									<li>
									<i class="icon-lightbulb-shine"></i>
									<span class="i-name">icon-lightbulb-shine</span>
									</li>
									<li>
									<i class="icon-arrow-down-1"></i>
									<span class="i-name">icon-arrow-down-1</span>
									</li>
									<li>
									<i class="icon-favorite-heart-outline"></i>
									<span class="i-name">icon-favorite-heart-outline</span>
									</li>
									<li>
									<i class="icon-advertising-megaphone-2"></i>
									<span class="i-name">icon-advertising-megaphone-2</span>
									</li>
									<li>
									<i class="icon-interface-windows"></i>
									<span class="i-name">icon-interface-windows</span>
									</li>
									<li>
									<i class="icon-ipod"></i>
									<span class="i-name">icon-ipod</span>
									</li>
									<li>
									<i class="icon-radar-2"></i>
									<span class="i-name">icon-radar-2</span>
									</li>
									<li>
									<i class="icon-minus-zoom"></i>
									<span class="i-name">icon-minus-zoom</span>
									</li>
									<li>
									<i class="icon-crhistmas-spruce-tree"></i>
									<span class="i-name">icon-crhistmas-spruce-tree</span>
									</li>
									<li>
									<i class="icon-arrow-cursor"></i>
									<span class="i-name">icon-arrow-cursor</span>
									</li>
									<li>
									<i class="icon-medal-rank-star"></i>
									<span class="i-name">icon-medal-rank-star</span>
									</li>
									<li>
									<i class="icon-database-5"></i>
									<span class="i-name">icon-database-5</span>
									</li>
									<li>
									<i class="icon-battery-full-1"></i>
									<span class="i-name">icon-battery-full-1</span>
									</li>
									<li>
									<i class="icon-chart-graph-file-1"></i>
									<span class="i-name">icon-chart-graph-file-1</span>
									</li>
									<li>
									<i class="icon-case-medic"></i>
									<span class="i-name">icon-case-medic</span>
									</li>
									<li>
									<i class="icon-disc-floppy-font"></i>
									<span class="i-name">icon-disc-floppy-font</span>
									</li>
									<li>
									<i class="icon-sun-weather"></i>
									<span class="i-name">icon-sun-weather</span>
									</li>
									<li>
									<i class="icon-parking-sign"></i>
									<span class="i-name">icon-parking-sign</span>
									</li>
									<li>
									<i class="icon-code-html-file-1"></i>
									<span class="i-name">icon-code-html-file-1</span>
									</li>
									<li>
									<i class="icon-date"></i>
									<span class="i-name">icon-date</span>
									</li>
									<li>
									<i class="icon-hand-hold"></i>
									<span class="i-name">icon-hand-hold</span>
									</li>
									<li>
									<i class="icon-cup-2"></i>
									<span class="i-name">icon-cup-2</span>
									</li>
									<li>
									<i class="icon-lightning-weather"></i>
									<span class="i-name">icon-lightning-weather</span>
									</li>
									<li>
									<i class="icon-cloud-sun"></i>
									<span class="i-name">icon-cloud-sun</span>
									</li>
									<li>
									<i class="icon-compressed-zip-file"></i>
									<span class="i-name">icon-compressed-zip-file</span>
									</li>
									<li>
									<i class="icon-road"></i>
									<span class="i-name">icon-road</span>
									</li>
									<li>
									<i class="icon-arrow-left-1"></i>
									<span class="i-name">icon-arrow-left-1</span>
									</li>
									<li>
									<i class="icon-building-24"></i>
									<span class="i-name">icon-building-24</span>
									</li>
									<li>
									<i class="icon-tennis-24"></i>
									<span class="i-name">icon-tennis-24</span>
									</li>
									<li>
									<i class="icon-skiing-24"></i>
									<span class="i-name">icon-skiing-24</span>
									</li>
									<li>
									<i class="icon-bus-24"></i>
									<span class="i-name">icon-bus-24</span>
									</li>
									<li>
									<i class="icon-park2-24"></i>
									<span class="i-name">icon-park2-24</span>
									</li>
									<li>
									<i class="icon-circle-24"></i>
									<span class="i-name">icon-circle-24</span>
									</li>
									<li>
									<i class="icon-golf-24"></i>
									<span class="i-name">icon-golf-24</span>
									</li>
									<li>
									<i class="icon-star-24"></i>
									<span class="i-name">icon-star-24</span>
									</li>
									<li>
									<i class="icon-water-24"></i>
									<span class="i-name">icon-water-24</span>
									</li>
									<li>
									<i class="icon-disability-24"></i>
									<span class="i-name">icon-disability-24</span>
									</li>
									<li>
									<i class="icon-art-gallery-24"></i>
									<span class="i-name">icon-art-gallery-24</span>
									</li>
									<li>
									<i class="icon-religious-jewish-24"></i>
									<span class="i-name">icon-religious-jewish-24</span>
									</li>
									<li>
									<i class="icon-marker-24"></i>
									<span class="i-name">icon-marker-24</span>
									</li>
									<li>
									<i class="icon-campsite-24"></i>
									<span class="i-name">icon-campsite-24</span>
									</li>
									<li>
									<i class="icon-prison-24"></i>
									<span class="i-name">icon-prison-24</span>
									</li>
									<li>
									<i class="icon-baseball-24"></i>
									<span class="i-name">icon-baseball-24</span>
									</li>
									<li>
									<i class="icon-pharmacy-24"></i>
									<span class="i-name">icon-pharmacy-24</span>
									</li>
									<li>
									<i class="icon-zoo-24"></i>
									<span class="i-name">icon-zoo-24</span>
									</li>
									<li>
									<i class="icon-triangle-stroked-24"></i>
									<span class="i-name">icon-triangle-stroked-24</span>
									</li>
									<li>
									<i class="icon-star-stroked-24"></i>
									<span class="i-name">icon-star-stroked-24</span>
									</li>
									<li>
									<i class="icon-slaughterhouse-24"></i>
									<span class="i-name">icon-slaughterhouse-24</span>
									</li>
									<li>
									<i class="icon-parking-24"></i>
									<span class="i-name">icon-parking-24</span>
									</li>
									<li>
									<i class="icon-heliport-24"></i>
									<span class="i-name">icon-heliport-24</span>
									</li>
									<li>
									<i class="icon-restaurant-24"></i>
									<span class="i-name">icon-restaurant-24</span>
									</li>
									<li>
									<i class="icon-shop-24"></i>
									<span class="i-name">icon-shop-24</span>
									</li>
									<li>
									<i class="icon-religious-christian-24"></i>
									<span class="i-name">icon-religious-christian-24</span>
									</li>
									<li>
									<i class="icon-museum-24"></i>
									<span class="i-name">icon-museum-24</span>
									</li>
									<li>
									<i class="icon-cross"></i>
									<span class="i-name">icon-cross</span>
									</li>
									<li>
									<i class="icon-toilets-24"></i>
									<span class="i-name">icon-toilets-24</span>
									</li>
									<li>
									<i class="icon-rail-underground-24"></i>
									<span class="i-name">icon-rail-underground-24</span>
									</li>
									<li>
									<i class="icon-basketball-24"></i>
									<span class="i-name">icon-basketball-24</span>
									</li>
									<li>
									<i class="icon-beer-24"></i>
									<span class="i-name">icon-beer-24</span>
									</li>
									<li>
									<i class="icon-airfield-24"></i>
									<span class="i-name">icon-airfield-24</span>
									</li>
									<li>
									<i class="icon-wetland-24"></i>
									<span class="i-name">icon-wetland-24</span>
									</li>
									<li>
									<i class="icon-soccer-24"></i>
									<span class="i-name">icon-soccer-24</span>
									</li>
									<li>
									<i class="icon-dam"></i>
									<span class="i-name">icon-dam</span>
									</li>
									<li>
									<i class="icon-bank-24"></i>
									<span class="i-name">icon-bank-24</span>
									</li>
									<li>
									<i class="icon-fuel-24"></i>
									<span class="i-name">icon-fuel-24</span>
									</li>
									<li>
									<i class="icon-school-24"></i>
									<span class="i-name">icon-school-24</span>
									</li>
									<li>
									<i class="icon-commercial-24"></i>
									<span class="i-name">icon-commercial-24</span>
									</li>
									<li>
									<i class="icon-religious-muslim-24"></i>
									<span class="i-name">icon-religious-muslim-24</span>
									</li>
									<li>
									<i class="icon-america-football-24"></i>
									<span class="i-name">icon-america-football-24</span>
									</li>
									<li>
									<i class="icon-swimming-24"></i>
									<span class="i-name">icon-swimming-24</span>
									</li>
									<li>
									<i class="icon-square-24"></i>
									<span class="i-name">icon-square-24</span>
									</li>
									<li>
									<i class="icon-circle-stroked-24"></i>
									<span class="i-name">icon-circle-stroked-24</span>
									</li>
									<li>
									<i class="icon-rail-above-24"></i>
									<span class="i-name">icon-rail-above-24</span>
									</li>
									<li>
									<i class="icon-monument-24"></i>
									<span class="i-name">icon-monument-24</span>
									</li>
									<li>
									<i class="icon-cinema-24"></i>
									<span class="i-name">icon-cinema-24</span>
									</li>
									<li>
									<i class="icon-ferry-24"></i>
									<span class="i-name">icon-ferry-24</span>
									</li>
									<li>
									<i class="icon-fast-food-24"></i>
									<span class="i-name">icon-fast-food-24</span>
									</li>
									<li>
									<i class="icon-cemetery-24"></i>
									<span class="i-name">icon-cemetery-24</span>
									</li>
									<li>
									<i class="icon-park-24"></i>
									<span class="i-name">icon-park-24</span>
									</li>
									<li>
									<i class="icon-telephone-24"></i>
									<span class="i-name">icon-telephone-24</span>
									</li>
									<li>
									<i class="icon-rail-24"></i>
									<span class="i-name">icon-rail-24</span>
									</li>
									<li>
									<i class="icon-college-24"></i>
									<span class="i-name">icon-college-24</span>
									</li>
									<li>
									<i class="icon-warehouse-24"></i>
									<span class="i-name">icon-warehouse-24</span>
									</li>
									<li>
									<i class="icon-hospital-24"></i>
									<span class="i-name">icon-hospital-24</span>
									</li>
									<li>
									<i class="icon-cricket-24"></i>
									<span class="i-name">icon-cricket-24</span>
									</li>
									<li>
									<i class="icon-parking-garage-24"></i>
									<span class="i-name">icon-parking-garage-24</span>
									</li>
									<li>
									<i class="icon-harbor-24"></i>
									<span class="i-name">icon-harbor-24</span>
									</li>
									<li>
									<i class="icon-cafe-24"></i>
									<span class="i-name">icon-cafe-24</span>
									</li>
									<li>
									<i class="icon-police-24"></i>
									<span class="i-name">icon-police-24</span>
									</li>
									<li>
									<i class="icon-industrial-24"></i>
									<span class="i-name">icon-industrial-24</span>
									</li>
									<li>
									<i class="icon-garden-24"></i>
									<span class="i-name">icon-garden-24</span>
									</li>
									<li>
									<i class="icon-triangle-24"></i>
									<span class="i-name">icon-triangle-24</span>
									</li>
									<li>
									<i class="icon-bar-24"></i>
									<span class="i-name">icon-bar-24</span>
									</li>
									<li>
									<i class="icon-place-of-worship-24"></i>
									<span class="i-name">icon-place-of-worship-24</span>
									</li>
									<li>
									<i class="icon-oil-well-24"></i>
									<span class="i-name">icon-oil-well-24</span>
									</li>
									<li>
									<i class="icon-library-24"></i>
									<span class="i-name">icon-library-24</span>
									</li>
									<li>
									<i class="icon-alcohol-shop-24"></i>
									<span class="i-name">icon-alcohol-shop-24</span>
									</li>
									<li>
									<i class="icon-bicycle-24"></i>
									<span class="i-name">icon-bicycle-24</span>
									</li>
									<li>
									<i class="icon-town-hall-24"></i>
									<span class="i-name">icon-town-hall-24</span>
									</li>
									<li>
									<i class="icon-music-24"></i>
									<span class="i-name">icon-music-24</span>
									</li>
									<li>
									<i class="icon-square-stroked-24"></i>
									<span class="i-name">icon-square-stroked-24</span>
									</li>
									<li>
									<i class="icon-pitch-24"></i>
									<span class="i-name">icon-pitch-24</span>
									</li>
									<li>
									<i class="icon-danger-24"></i>
									<span class="i-name">icon-danger-24</span>
									</li>
									<li>
									<i class="icon-fire-station-24"></i>
									<span class="i-name">icon-fire-station-24</span>
									</li>
									<li>
									<i class="icon-theatre-24"></i>
									<span class="i-name">icon-theatre-24</span>
									</li>
									<li>
									<i class="icon-marker-stroked-24"></i>
									<span class="i-name">icon-marker-stroked-24</span>
									</li>
									<li>
									<i class="icon-lodging-24"></i>
									<span class="i-name">icon-lodging-24</span>
									</li>
									<li>
									<i class="icon-embassy-24"></i>
									<span class="i-name">icon-embassy-24</span>
									</li>
									<li>
									<i class="icon-airport-24"></i>
									<span class="i-name">icon-airport-24</span>
									</li>
									<li>
									<i class="icon-logging-24"></i>
									<span class="i-name">icon-logging-24</span>
									</li>
									<li>
									<i class="icon-aim"></i>
									<span class="i-name">icon-aim</span>
									</li>
									<li>
									<i class="icon-aim-alt"></i>
									<span class="i-name">icon-aim-alt</span>
									</li>
									<li>
									<i class="icon-amazon"></i>
									<span class="i-name">icon-amazon</span>
									</li>
									<li>
									<i class="icon-app-store"></i>
									<span class="i-name">icon-app-store</span>
									</li>
									<li>
									<i class="icon-apple-1"></i>
									<span class="i-name">icon-apple-1</span>
									</li>
									<li>
									<i class="icon-arto"></i>
									<span class="i-name">icon-arto</span>
									</li>
									<li>
									<i class="icon-aws"></i>
									<span class="i-name">icon-aws</span>
									</li>
									<li>
									<i class="icon-baidu"></i>
									<span class="i-name">icon-baidu</span>
									</li>
									<li>
									<i class="icon-basecamp"></i>
									<span class="i-name">icon-basecamp</span>
									</li>
									<li>
									<i class="icon-bebo"></i>
									<span class="i-name">icon-bebo</span>
									</li>
									<li>
									<i class="icon-behance-1"></i>
									<span class="i-name">icon-behance-1</span>
									</li>
									<li>
									<i class="icon-bing"></i>
									<span class="i-name">icon-bing</span>
									</li>
									<li>
									<i class="icon-blip"></i>
									<span class="i-name">icon-blip</span>
									</li>
									<li>
									<i class="icon-blogger"></i>
									<span class="i-name">icon-blogger</span>
									</li>
									<li>
									<i class="icon-bnter"></i>
									<span class="i-name">icon-bnter</span>
									</li>
									<li>
									<i class="icon-brightkite"></i>
									<span class="i-name">icon-brightkite</span>
									</li>
									<li>
									<i class="icon-cinch"></i>
									<span class="i-name">icon-cinch</span>
									</li>
									<li>
									<i class="icon-cloudapp"></i>
									<span class="i-name">icon-cloudapp</span>
									</li>
									<li>
									<i class="icon-coroflot"></i>
									<span class="i-name">icon-coroflot</span>
									</li>
									<li>
									<i class="icon-creative-commons"></i>
									<span class="i-name">icon-creative-commons</span>
									</li>
									<li>
									<i class="icon-dailybooth"></i>
									<span class="i-name">icon-dailybooth</span>
									</li>
									<li>
									<i class="icon-delicious-1"></i>
									<span class="i-name">icon-delicious-1</span>
									</li>
									<li>
									<i class="icon-designbump"></i>
									<span class="i-name">icon-designbump</span>
									</li>
									<li>
									<i class="icon-designfloat"></i>
									<span class="i-name">icon-designfloat</span>
									</li>
									<li>
									<i class="icon-designmoo"></i>
									<span class="i-name">icon-designmoo</span>
									</li>
									<li>
									<i class="icon-deviantart"></i>
									<span class="i-name">icon-deviantart</span>
									</li>
									<li>
									<i class="icon-digg"></i>
									<span class="i-name">icon-digg</span>
									</li>
									<li>
									<i class="icon-digg-alt"></i>
									<span class="i-name">icon-digg-alt</span>
									</li>
									<li>
									<i class="icon-diigo"></i>
									<span class="i-name">icon-diigo</span>
									</li>
									<li>
									<i class="icon-dribbble-2"></i>
									<span class="i-name">icon-dribbble-2</span>
									</li>
									<li>
									<i class="icon-dropbox-1"></i>
									<span class="i-name">icon-dropbox-1</span>
									</li>
									<li>
									<i class="icon-drupal"></i>
									<span class="i-name">icon-drupal</span>
									</li>
									<li>
									<i class="icon-dzone"></i>
									<span class="i-name">icon-dzone</span>
									</li>
									<li>
									<i class="icon-ebay"></i>
									<span class="i-name">icon-ebay</span>
									</li>
									<li>
									<i class="icon-ember"></i>
									<span class="i-name">icon-ember</span>
									</li>
									<li>
									<i class="icon-etsy"></i>
									<span class="i-name">icon-etsy</span>
									</li>
									<li>
									<i class="icon-evernote"></i>
									<span class="i-name">icon-evernote</span>
									</li>
									<li>
									<i class="icon-facebook-1"></i>
									<span class="i-name">icon-facebook-1</span>
									</li>
									<li>
									<i class="icon-facebook-places"></i>
									<span class="i-name">icon-facebook-places</span>
									</li>
									<li>
									<i class="icon-facto"></i>
									<span class="i-name">icon-facto</span>
									</li>
									<li>
									<i class="icon-feedburner"></i>
									<span class="i-name">icon-feedburner</span>
									</li>
									<li>
									<i class="icon-flickr"></i>
									<span class="i-name">icon-flickr</span>
									</li>
									<li>
									<i class="icon-folkd"></i>
									<span class="i-name">icon-folkd</span>
									</li>
									<li>
									<i class="icon-formspring"></i>
									<span class="i-name">icon-formspring</span>
									</li>
									<li>
									<i class="icon-forrst"></i>
									<span class="i-name">icon-forrst</span>
									</li>
									<li>
									<i class="icon-foursquare"></i>
									<span class="i-name">icon-foursquare</span>
									</li>
									<li>
									<i class="icon-friendfeed"></i>
									<span class="i-name">icon-friendfeed</span>
									</li>
									<li>
									<i class="icon-friendster"></i>
									<span class="i-name">icon-friendster</span>
									</li>
									<li>
									<i class="icon-gdgt"></i>
									<span class="i-name">icon-gdgt</span>
									</li>
									<li>
									<i class="icon-github"></i>
									<span class="i-name">icon-github</span>
									</li>
									<li>
									<i class="icon-github-alt"></i>
									<span class="i-name">icon-github-alt</span>
									</li>
									<li>
									<i class="icon-goodreads"></i>
									<span class="i-name">icon-goodreads</span>
									</li>
									<li>
									<i class="icon-google"></i>
									<span class="i-name">icon-google</span>
									</li>
									<li>
									<i class="icon-google-buzz"></i>
									<span class="i-name">icon-google-buzz</span>
									</li>
									<li>
									<i class="icon-google-talk"></i>
									<span class="i-name">icon-google-talk</span>
									</li>
									<li>
									<i class="icon-gowalla"></i>
									<span class="i-name">icon-gowalla</span>
									</li>
									<li>
									<i class="icon-gowalla-alt"></i>
									<span class="i-name">icon-gowalla-alt</span>
									</li>
									<li>
									<i class="icon-grooveshark"></i>
									<span class="i-name">icon-grooveshark</span>
									</li>
									<li>
									<i class="icon-hacker-news"></i>
									<span class="i-name">icon-hacker-news</span>
									</li>
									<li>
									<i class="icon-hi5"></i>
									<span class="i-name">icon-hi5</span>
									</li>
									<li>
									<i class="icon-hype-machine"></i>
									<span class="i-name">icon-hype-machine</span>
									</li>
									<li>
									<i class="icon-hyves"></i>
									<span class="i-name">icon-hyves</span>
									</li>
									<li>
									<i class="icon-icq"></i>
									<span class="i-name">icon-icq</span>
									</li>
									<li>
									<i class="icon-identi"></i>
									<span class="i-name">icon-identi</span>
									</li>
									<li>
									<i class="icon-instapaper"></i>
									<span class="i-name">icon-instapaper</span>
									</li>
									<li>
									<i class="icon-itunes"></i>
									<span class="i-name">icon-itunes</span>
									</li>
									<li>
									<i class="icon-kik"></i>
									<span class="i-name">icon-kik</span>
									</li>
									<li>
									<i class="icon-krop"></i>
									<span class="i-name">icon-krop</span>
									</li>
									<li>
									<i class="icon-last-1"></i>
									<span class="i-name">icon-last-1</span>
									</li>
									<li>
									<i class="icon-linkedin"></i>
									<span class="i-name">icon-linkedin</span>
									</li>
									<li>
									<i class="icon-linkedin-alt-1"></i>
									<span class="i-name">icon-linkedin-alt-1</span>
									</li>
									<li>
									<i class="icon-livejournal"></i>
									<span class="i-name">icon-livejournal</span>
									</li>
									<li>
									<i class="icon-lovedsgn"></i>
									<span class="i-name">icon-lovedsgn</span>
									</li>
									<li>
									<i class="icon-meetup"></i>
									<span class="i-name">icon-meetup</span>
									</li>
									<li>
									<i class="icon-metacafe"></i>
									<span class="i-name">icon-metacafe</span>
									</li>
									<li>
									<i class="icon-ming"></i>
									<span class="i-name">icon-ming</span>
									</li>
									<li>
									<i class="icon-mister-wong"></i>
									<span class="i-name">icon-mister-wong</span>
									</li>
									<li>
									<i class="icon-mixx"></i>
									<span class="i-name">icon-mixx</span>
									</li>
									<li>
									<i class="icon-mixx-alt"></i>
									<span class="i-name">icon-mixx-alt</span>
									</li>
									<li>
									<i class="icon-mobileme"></i>
									<span class="i-name">icon-mobileme</span>
									</li>
									<li>
									<i class="icon-msn-messenger"></i>
									<span class="i-name">icon-msn-messenger</span>
									</li>
									<li>
									<i class="icon-myspace"></i>
									<span class="i-name">icon-myspace</span>
									</li>
									<li>
									<i class="icon-myspace-alt"></i>
									<span class="i-name">icon-myspace-alt</span>
									</li>
									<li>
									<i class="icon-newsvine"></i>
									<span class="i-name">icon-newsvine</span>
									</li>
									<li>
									<i class="icon-official"></i>
									<span class="i-name">icon-official</span>
									</li>
									<li>
									<i class="icon-openid"></i>
									<span class="i-name">icon-openid</span>
									</li>
									<li>
									<i class="icon-orkut"></i>
									<span class="i-name">icon-orkut</span>
									</li>
									<li>
									<i class="icon-pandora"></i>
									<span class="i-name">icon-pandora</span>
									</li>
									<li>
									<i class="icon-path"></i>
									<span class="i-name">icon-path</span>
									</li>
									<li>
									<i class="icon-paypal-1"></i>
									<span class="i-name">icon-paypal-1</span>
									</li>
									<li>
									<i class="icon-photobucket"></i>
									<span class="i-name">icon-photobucket</span>
									</li>
									<li>
									<i class="icon-picasa"></i>
									<span class="i-name">icon-picasa</span>
									</li>
									<li>
									<i class="icon-picassa"></i>
									<span class="i-name">icon-picassa</span>
									</li>
									<li>
									<i class="icon-pinboard"></i>
									<span class="i-name">icon-pinboard</span>
									</li>
									<li>
									<i class="icon-ping"></i>
									<span class="i-name">icon-ping</span>
									</li>
									<li>
									<i class="icon-pingchat"></i>
									<span class="i-name">icon-pingchat</span>
									</li>
									<li>
									<i class="icon-playstation"></i>
									<span class="i-name">icon-playstation</span>
									</li>
									<li>
									<i class="icon-plixi"></i>
									<span class="i-name">icon-plixi</span>
									</li>
									<li>
									<i class="icon-plurk"></i>
									<span class="i-name">icon-plurk</span>
									</li>
									<li>
									<i class="icon-podcast"></i>
									<span class="i-name">icon-podcast</span>
									</li>
									<li>
									<i class="icon-posterous"></i>
									<span class="i-name">icon-posterous</span>
									</li>
									<li>
									<i class="icon-qik"></i>
									<span class="i-name">icon-qik</span>
									</li>
									<li>
									<i class="icon-quik"></i>
									<span class="i-name">icon-quik</span>
									</li>
									<li>
									<i class="icon-quora"></i>
									<span class="i-name">icon-quora</span>
									</li>
									<li>
									<i class="icon-rdio"></i>
									<span class="i-name">icon-rdio</span>
									</li>
									<li>
									<i class="icon-readernaut"></i>
									<span class="i-name">icon-readernaut</span>
									</li>
									<li>
									<i class="icon-reddit"></i>
									<span class="i-name">icon-reddit</span>
									</li>
									<li>
									<i class="icon-retweet"></i>
									<span class="i-name">icon-retweet</span>
									</li>
									<li>
									<i class="icon-robo"></i>
									<span class="i-name">icon-robo</span>
									</li>
									<li>
									<i class="icon-rss-1"></i>
									<span class="i-name">icon-rss-1</span>
									</li>
									<li>
									<i class="icon-scribd"></i>
									<span class="i-name">icon-scribd</span>
									</li>
									<li>
									<i class="icon-sharethis"></i>
									<span class="i-name">icon-sharethis</span>
									</li>
									<li>
									<i class="icon-simplenote"></i>
									<span class="i-name">icon-simplenote</span>
									</li>
									<li>
									<i class="icon-skype-1"></i>
									<span class="i-name">icon-skype-1</span>
									</li>
									<li>
									<i class="icon-slashdot"></i>
									<span class="i-name">icon-slashdot</span>
									</li>
									<li>
									<i class="icon-slideshare"></i>
									<span class="i-name">icon-slideshare</span>
									</li>
									<li>
									<i class="icon-smugmug"></i>
									<span class="i-name">icon-smugmug</span>
									</li>
									<li>
									<i class="icon-soundcloud"></i>
									<span class="i-name">icon-soundcloud</span>
									</li>
									<li>
									<i class="icon-spotify"></i>
									<span class="i-name">icon-spotify</span>
									</li>
									<li>
									<i class="icon-squarespace"></i>
									<span class="i-name">icon-squarespace</span>
									</li>
									<li>
									<i class="icon-squidoo"></i>
									<span class="i-name">icon-squidoo</span>
									</li>
									<li>
									<i class="icon-steam"></i>
									<span class="i-name">icon-steam</span>
									</li>
									<li>
									<i class="icon-stumbleupon"></i>
									<span class="i-name">icon-stumbleupon</span>
									</li>
									<li>
									<i class="icon-technorati"></i>
									<span class="i-name">icon-technorati</span>
									</li>
									<li>
									<i class="icon-threewords"></i>
									<span class="i-name">icon-threewords</span>
									</li>
									<li>
									<i class="icon-tribe"></i>
									<span class="i-name">icon-tribe</span>
									</li>
									<li>
									<i class="icon-tripit"></i>
									<span class="i-name">icon-tripit</span>
									</li>
									<li>
									<i class="icon-tumblr"></i>
									<span class="i-name">icon-tumblr</span>
									</li>
									<li>
									<i class="icon-twitter-1"></i>
									<span class="i-name">icon-twitter-1</span>
									</li>
									<li>
									<i class="icon-twitter-alt-1"></i>
									<span class="i-name">icon-twitter-alt-1</span>
									</li>
									<li>
									<i class="icon-vcard"></i>
									<span class="i-name">icon-vcard</span>
									</li>
									<li>
									<i class="icon-viddler"></i>
									<span class="i-name">icon-viddler</span>
									</li>
									<li>
									<i class="icon-vimeo"></i>
									<span class="i-name">icon-vimeo</span>
									</li>
									<li>
									<i class="icon-virb"></i>
									<span class="i-name">icon-virb</span>
									</li>
									<li>
									<i class="icon-w3"></i>
									<span class="i-name">icon-w3</span>
									</li>
									<li>
									<i class="icon-whatsapp"></i>
									<span class="i-name">icon-whatsapp</span>
									</li>
									<li>
									<i class="icon-wikipedia"></i>
									<span class="i-name">icon-wikipedia</span>
									</li>
									<li>
									<i class="icon-windows-1"></i>
									<span class="i-name">icon-windows-1</span>
									</li>
									<li>
									<i class="icon-wists"></i>
									<span class="i-name">icon-wists</span>
									</li>
									<li>
									<i class="icon-wordpress"></i>
									<span class="i-name">icon-wordpress</span>
									</li>
									<li>
									<i class="icon-wordpress-alt"></i>
									<span class="i-name">icon-wordpress-alt</span>
									</li>
									<li>
									<i class="icon-xing"></i>
									<span class="i-name">icon-xing</span>
									</li>
									<li>
									<i class="icon-yahoo"></i>
									<span class="i-name">icon-yahoo</span>
									</li>
									<li>
									<i class="icon-yahoo-buzz"></i>
									<span class="i-name">icon-yahoo-buzz</span>
									</li>
									<li>
									<i class="icon-yahoo-messenger"></i>
									<span class="i-name">icon-yahoo-messenger</span>
									</li>
									<li>
									<i class="icon-yelp"></i>
									<span class="i-name">icon-yelp</span>
									</li>
									<li>
									<i class="icon-zerply"></i>
									<span class="i-name">icon-zerply</span>
									</li>
									<li>
									<i class="icon-zootool"></i>
									<span class="i-name">icon-zootool</span>
									</li>
									<li>
									<i class="icon-zynga"></i>
									<span class="i-name">icon-zynga</span>
									</li>
									<li>
									<i class="icon-align-center"></i>
									<span class="i-name">icon-align-center</span>
									</li>
									<li>
									<i class="icon-align-justify"></i>
									<span class="i-name">icon-align-justify</span>
									</li>
									<li>
									<i class="icon-align-left"></i>
									<span class="i-name">icon-align-left</span>
									</li>
									<li>
									<i class="icon-align-right"></i>
									<span class="i-name">icon-align-right</span>
									</li>
									<li>
									<i class="icon-archive-1"></i>
									<span class="i-name">icon-archive-1</span>
									</li>
									<li>
									<i class="icon-atom"></i>
									<span class="i-name">icon-atom</span>
									</li>
									<li>
									<i class="icon-bag"></i>
									<span class="i-name">icon-bag</span>
									</li>
									<li>
									<i class="icon-bank-notes"></i>
									<span class="i-name">icon-bank-notes</span>
									</li>
									<li>
									<i class="icon-barbell"></i>
									<span class="i-name">icon-barbell</span>
									</li>
									<li>
									<i class="icon-bars-1"></i>
									<span class="i-name">icon-bars-1</span>
									</li>
									<li>
									<i class="icon-battery-0"></i>
									<span class="i-name">icon-battery-0</span>
									</li>
									<li>
									<i class="icon-battery-1"></i>
									<span class="i-name">icon-battery-1</span>
									</li>
									<li>
									<i class="icon-battery-2"></i>
									<span class="i-name">icon-battery-2</span>
									</li>
									<li>
									<i class="icon-battery-3"></i>
									<span class="i-name">icon-battery-3</span>
									</li>
									<li>
									<i class="icon-battery-4"></i>
									<span class="i-name">icon-battery-4</span>
									</li>
									<li>
									<i class="icon-battery-power"></i>
									<span class="i-name">icon-battery-power</span>
									</li>
									<li>
									<i class="icon-beer"></i>
									<span class="i-name">icon-beer</span>
									</li>
									<li>
									<i class="icon-bolt-1"></i>
									<span class="i-name">icon-bolt-1</span>
									</li>
									<li>
									<i class="icon-bones"></i>
									<span class="i-name">icon-bones</span>
									</li>
									<li>
									<i class="icon-book-close"></i>
									<span class="i-name">icon-book-close</span>
									</li>
									<li>
									<i class="icon-book-open"></i>
									<span class="i-name">icon-book-open</span>
									</li>
									<li>
									<i class="icon-bookmark"></i>
									<span class="i-name">icon-bookmark</span>
									</li>
									<li>
									<i class="icon-box-1"></i>
									<span class="i-name">icon-box-1</span>
									</li>
									<li>
									<i class="icon-browser-1"></i>
									<span class="i-name">icon-browser-1</span>
									</li>
									<li>
									<i class="icon-bubble"></i>
									<span class="i-name">icon-bubble</span>
									</li>
									<li>
									<i class="icon-bubble-1"></i>
									<span class="i-name">icon-bubble-1</span>
									</li>
									<li>
									<i class="icon-bubble-2"></i>
									<span class="i-name">icon-bubble-2</span>
									</li>
									<li>
									<i class="icon-bubble-3"></i>
									<span class="i-name">icon-bubble-3</span>
									</li>
									<li>
									<i class="icon-bucket"></i>
									<span class="i-name">icon-bucket</span>
									</li>
									<li>
									<i class="icon-calculator-1"></i>
									<span class="i-name">icon-calculator-1</span>
									</li>
									<li>
									<i class="icon-calendar-2"></i>
									<span class="i-name">icon-calendar-2</span>
									</li>
									<li>
									<i class="icon-camera-3"></i>
									<span class="i-name">icon-camera-3</span>
									</li>
									<li>
									<i class="icon-cardiac-pulse"></i>
									<span class="i-name">icon-cardiac-pulse</span>
									</li>
									<li>
									<i class="icon-cd-2"></i>
									<span class="i-name">icon-cd-2</span>
									</li>
									<li>
									<i class="icon-character"></i>
									<span class="i-name">icon-character</span>
									</li>
									<li>
									<i class="icon-clipboard-1"></i>
									<span class="i-name">icon-clipboard-1</span>
									</li>
									<li>
									<i class="icon-clock-2"></i>
									<span class="i-name">icon-clock-2</span>
									</li>
									<li>
									<i class="icon-cloud-3"></i>
									<span class="i-name">icon-cloud-3</span>
									</li>
									<li>
									<i class="icon-coffee"></i>
									<span class="i-name">icon-coffee</span>
									</li>
									<li>
									<i class="icon-comment"></i>
									<span class="i-name">icon-comment</span>
									</li>
									<li>
									<i class="icon-connection-0"></i>
									<span class="i-name">icon-connection-0</span>
									</li>
									<li>
									<i class="icon-connection-1"></i>
									<span class="i-name">icon-connection-1</span>
									</li>
									<li>
									<i class="icon-connection-2"></i>
									<span class="i-name">icon-connection-2</span>
									</li>
									<li>
									<i class="icon-connection-3"></i>
									<span class="i-name">icon-connection-3</span>
									</li>
									<li>
									<i class="icon-connection-4"></i>
									<span class="i-name">icon-connection-4</span>
									</li>
									<li>
									<i class="icon-credit-cards"></i>
									<span class="i-name">icon-credit-cards</span>
									</li>
									<li>
									<i class="icon-crop"></i>
									<span class="i-name">icon-crop</span>
									</li>
									<li>
									<i class="icon-cube"></i>
									<span class="i-name">icon-cube</span>
									</li>
									<li>
									<i class="icon-diamond"></i>
									<span class="i-name">icon-diamond</span>
									</li>
									<li>
									<i class="icon-email"></i>
									<span class="i-name">icon-email</span>
									</li>
									<li>
									<i class="icon-email-plane"></i>
									<span class="i-name">icon-email-plane</span>
									</li>
									<li>
									<i class="icon-enter"></i>
									<span class="i-name">icon-enter</span>
									</li>
									<li>
									<i class="icon-eyedropper-1"></i>
									<span class="i-name">icon-eyedropper-1</span>
									</li>
									<li>
									<i class="icon-file"></i>
									<span class="i-name">icon-file</span>
									</li>
									<li>
									<i class="icon-file-add"></i>
									<span class="i-name">icon-file-add</span>
									</li>
									<li>
									<i class="icon-file-broken"></i>
									<span class="i-name">icon-file-broken</span>
									</li>
									<li>
									<i class="icon-file-settings"></i>
									<span class="i-name">icon-file-settings</span>
									</li>
									<li>
									<i class="icon-files"></i>
									<span class="i-name">icon-files</span>
									</li>
									<li>
									<i class="icon-flag"></i>
									<span class="i-name">icon-flag</span>
									</li>
									<li>
									<i class="icon-folder"></i>
									<span class="i-name">icon-folder</span>
									</li>
									<li>
									<i class="icon-folder-add"></i>
									<span class="i-name">icon-folder-add</span>
									</li>
									<li>
									<i class="icon-folder-check"></i>
									<span class="i-name">icon-folder-check</span>
									</li>
									<li>
									<i class="icon-folder-settings"></i>
									<span class="i-name">icon-folder-settings</span>
									</li>
									<li>
									<i class="icon-forbidden"></i>
									<span class="i-name">icon-forbidden</span>
									</li>
									<li>
									<i class="icon-frames"></i>
									<span class="i-name">icon-frames</span>
									</li>
									<li>
									<i class="icon-glass"></i>
									<span class="i-name">icon-glass</span>
									</li>
									<li>
									<i class="icon-graph"></i>
									<span class="i-name">icon-graph</span>
									</li>
									<li>
									<i class="icon-grid-1"></i>
									<span class="i-name">icon-grid-1</span>
									</li>
									<li>
									<i class="icon-heart-1"></i>
									<span class="i-name">icon-heart-1</span>
									</li>
									<li>
									<i class="icon-home-2"></i>
									<span class="i-name">icon-home-2</span>
									</li>
									<li>
									<i class="icon-invoice"></i>
									<span class="i-name">icon-invoice</span>
									</li>
									<li>
									<i class="icon-ipad-1"></i>
									<span class="i-name">icon-ipad-1</span>
									</li>
									<li>
									<i class="icon-ipad-2"></i>
									<span class="i-name">icon-ipad-2</span>
									</li>
									<li>
									<i class="icon-lab"></i>
									<span class="i-name">icon-lab</span>
									</li>
									<li>
									<i class="icon-laptop"></i>
									<span class="i-name">icon-laptop</span>
									</li>
									<li>
									<i class="icon-list-2"></i>
									<span class="i-name">icon-list-2</span>
									</li>
									<li>
									<i class="icon-lock"></i>
									<span class="i-name">icon-lock</span>
									</li>
									<li>
									<i class="icon-locked"></i>
									<span class="i-name">icon-locked</span>
									</li>
									<li>
									<i class="icon-map"></i>
									<span class="i-name">icon-map</span>
									</li>
									<li>
									<i class="icon-measure"></i>
									<span class="i-name">icon-measure</span>
									</li>
									<li>
									<i class="icon-meter"></i>
									<span class="i-name">icon-meter</span>
									</li>
									<li>
									<i class="icon-micro"></i>
									<span class="i-name">icon-micro</span>
									</li>
									<li>
									<i class="icon-micro-mute"></i>
									<span class="i-name">icon-micro-mute</span>
									</li>
									<li>
									<i class="icon-microwave"></i>
									<span class="i-name">icon-microwave</span>
									</li>
									<li>
									<i class="icon-modem"></i>
									<span class="i-name">icon-modem</span>
									</li>
									<li>
									<i class="icon-mute"></i>
									<span class="i-name">icon-mute</span>
									</li>
									<li>
									<i class="icon-newspaper-1"></i>
									<span class="i-name">icon-newspaper-1</span>
									</li>
									<li>
									<i class="icon-paperclip-2"></i>
									<span class="i-name">icon-paperclip-2</span>
									</li>
									<li>
									<i class="icon-pencil-1"></i>
									<span class="i-name">icon-pencil-1</span>
									</li>
									<li>
									<i class="icon-phone"></i>
									<span class="i-name">icon-phone</span>
									</li>
									<li>
									<i class="icon-phone-2"></i>
									<span class="i-name">icon-phone-2</span>
									</li>
									<li>
									<i class="icon-phone-3"></i>
									<span class="i-name">icon-phone-3</span>
									</li>
									<li>
									<i class="icon-picture"></i>
									<span class="i-name">icon-picture</span>
									</li>
									<li>
									<i class="icon-pie-chart"></i>
									<span class="i-name">icon-pie-chart</span>
									</li>
									<li>
									<i class="icon-pill"></i>
									<span class="i-name">icon-pill</span>
									</li>
									<li>
									<i class="icon-pin-2"></i>
									<span class="i-name">icon-pin-2</span>
									</li>
									<li>
									<i class="icon-power"></i>
									<span class="i-name">icon-power</span>
									</li>
									<li>
									<i class="icon-printer-1"></i>
									<span class="i-name">icon-printer-1</span>
									</li>
									<li>
									<i class="icon-printer-2"></i>
									<span class="i-name">icon-printer-2</span>
									</li>
									<li>
									<i class="icon-refresh"></i>
									<span class="i-name">icon-refresh</span>
									</li>
									<li>
									<i class="icon-reload-1"></i>
									<span class="i-name">icon-reload-1</span>
									</li>
									<li>
									<i class="icon-screen-1"></i>
									<span class="i-name">icon-screen-1</span>
									</li>
									<li>
									<i class="icon-select"></i>
									<span class="i-name">icon-select</span>
									</li>
									<li>
									<i class="icon-set"></i>
									<span class="i-name">icon-set</span>
									</li>
									<li>
									<i class="icon-settings-1"></i>
									<span class="i-name">icon-settings-1</span>
									</li>
									<li>
									<i class="icon-shorts"></i>
									<span class="i-name">icon-shorts</span>
									</li>
									<li>
									<i class="icon-speaker"></i>
									<span class="i-name">icon-speaker</span>
									</li>
									<li>
									<i class="icon-star-1"></i>
									<span class="i-name">icon-star-1</span>
									</li>
									<li>
									<i class="icon-stopwatch"></i>
									<span class="i-name">icon-stopwatch</span>
									</li>
									<li>
									<i class="icon-sun"></i>
									<span class="i-name">icon-sun</span>
									</li>
									<li>
									<i class="icon-syringe"></i>
									<span class="i-name">icon-syringe</span>
									</li>
									<li>
									<i class="icon-tag"></i>
									<span class="i-name">icon-tag</span>
									</li>
									<li>
									<i class="icon-train-1"></i>
									<span class="i-name">icon-train-1</span>
									</li>
									<li>
									<i class="icon-trash-1"></i>
									<span class="i-name">icon-trash-1</span>
									</li>
									<li>
									<i class="icon-unlocked"></i>
									<span class="i-name">icon-unlocked</span>
									</li>
									<li>
									<i class="icon-volume-1"></i>
									<span class="i-name">icon-volume-1</span>
									</li>
									<li>
									<i class="icon-volume-down"></i>
									<span class="i-name">icon-volume-down</span>
									</li>
									<li>
									<i class="icon-volume-up"></i>
									<span class="i-name">icon-volume-up</span>
									</li>
									<li>
									<i class="icon-wifi-1"></i>
									<span class="i-name">icon-wifi-1</span>
									</li>
									<li>
									<i class="icon-wifi-2"></i>
									<span class="i-name">icon-wifi-2</span>
									</li>
									<li>
									<i class="icon-wifi-3"></i>
									<span class="i-name">icon-wifi-3</span>
									</li>
									<li>
									<i class="icon-window-delete"></i>
									<span class="i-name">icon-window-delete</span>
									</li>
									<li>
									<i class="icon-windows-2"></i>
									<span class="i-name">icon-windows-2</span>
									</li>
									<li>
									<i class="icon-zoom-in-1"></i>
									<span class="i-name">icon-zoom-in-1</span>
									</li>
									<li>
									<i class="icon-magnifying-glass-1"></i>
									<span class="i-name">icon-magnifying-glass-1</span>
									</li>
									<li>
									<i class="icon-search-2"></i>
									<span class="i-name">icon-search-2</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-2"></i>
									<span class="i-name">icon-fontawesome-webfont-2</span>
									</li>
									<li>
									<i class="icon-envelope-1"></i>
									<span class="i-name">icon-envelope-1</span>
									</li>
									<li>
									<i class="icon-download-3"></i>
									<span class="i-name">icon-download-3</span>
									</li>
									<li>
									<i class="icon-upload-3"></i>
									<span class="i-name">icon-upload-3</span>
									</li>
									<li>
									<i class="icon-stumbleupon-1"></i>
									<span class="i-name">icon-stumbleupon-1</span>
									</li>
									<li>
									<i class="icon-user"></i>
									<span class="i-name">icon-user</span>
									</li>
									<li>
									<i class="icon-users"></i>
									<span class="i-name">icon-users</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-3"></i>
									<span class="i-name">icon-fontawesome-webfont-3</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-4"></i>
									<span class="i-name">icon-fontawesome-webfont-4</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-5"></i>
									<span class="i-name">icon-fontawesome-webfont-5</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-6"></i>
									<span class="i-name">icon-fontawesome-webfont-6</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-7"></i>
									<span class="i-name">icon-fontawesome-webfont-7</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-8"></i>
									<span class="i-name">icon-fontawesome-webfont-8</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-9"></i>
									<span class="i-name">icon-fontawesome-webfont-9</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-10"></i>
									<span class="i-name">icon-fontawesome-webfont-10</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-11"></i>
									<span class="i-name">icon-fontawesome-webfont-11</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-12"></i>
									<span class="i-name">icon-fontawesome-webfont-12</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-13"></i>
									<span class="i-name">icon-fontawesome-webfont-13</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-14"></i>
									<span class="i-name">icon-fontawesome-webfont-14</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-15"></i>
									<span class="i-name">icon-fontawesome-webfont-15</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-16"></i>
									<span class="i-name">icon-fontawesome-webfont-16</span>
									</li>
									<li>
									<i class="icon-bookmark-1"></i>
									<span class="i-name">icon-bookmark-1</span>
									</li>
									<li>
									<i class="icon-book-open-1"></i>
									<span class="i-name">icon-book-open-1</span>
									</li>
									<li>
									<i class="icon-flash"></i>
									<span class="i-name">icon-flash</span>
									</li>
									<li>
									<i class="icon-feather-1"></i>
									<span class="i-name">icon-feather-1</span>
									</li>
									<li>
									<i class="icon-flag-1"></i>
									<span class="i-name">icon-flag-1</span>
									</li>
									<li>
									<i class="icon-google-circles"></i>
									<span class="i-name">icon-google-circles</span>
									</li>
									<li>
									<i class="icon-heart-2"></i>
									<span class="i-name">icon-heart-2</span>
									</li>
									<li>
									<i class="icon-heart-empty"></i>
									<span class="i-name">icon-heart-empty</span>
									</li>
									<li>
									<i class="icon-right-open-big"></i>
									<span class="i-name">icon-right-open-big</span>
									</li>
									<li>
									<i class="icon-left-open-big"></i>
									<span class="i-name">icon-left-open-big</span>
									</li>
									<li>
									<i class="icon-up-open-big"></i>
									<span class="i-name">icon-up-open-big</span>
									</li>
									<li>
									<i class="icon-down-open-big"></i>
									<span class="i-name">icon-down-open-big</span>
									</li>
									<li>
									<i class="icon-symbol-woman"></i>
									<span class="i-name">icon-symbol-woman</span>
									</li>
									<li>
									<i class="icon-measure-1"></i>
									<span class="i-name">icon-measure-1</span>
									</li>
									<li>
									<i class="icon-symbol-mixed"></i>
									<span class="i-name">icon-symbol-mixed</span>
									</li>
									<li>
									<i class="icon-letter"></i>
									<span class="i-name">icon-letter</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-17"></i>
									<span class="i-name">icon-fontawesome-webfont-17</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-18"></i>
									<span class="i-name">icon-fontawesome-webfont-18</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-19"></i>
									<span class="i-name">icon-fontawesome-webfont-19</span>
									</li>
									<li>
									<i class="icon-arrow-1-down"></i>
									<span class="i-name">icon-arrow-1-down</span>
									</li>
									<li>
									<i class="icon-arrow-up-1"></i>
									<span class="i-name">icon-arrow-up-1</span>
									</li>
									<li>
									<i class="icon-fork"></i>
									<span class="i-name">icon-fork</span>
									</li>
									<li>
									<i class="icon-curved-arrow"></i>
									<span class="i-name">icon-curved-arrow</span>
									</li>
									<li>
									<i class="icon-forward-2"></i>
									<span class="i-name">icon-forward-2</span>
									</li>
									<li>
									<i class="icon-reload-2"></i>
									<span class="i-name">icon-reload-2</span>
									</li>
									<li>
									<i class="icon-arrows-out"></i>
									<span class="i-name">icon-arrows-out</span>
									</li>
									<li>
									<i class="icon-arrows-expand"></i>
									<span class="i-name">icon-arrows-expand</span>
									</li>
									<li>
									<i class="icon-arrows-compress"></i>
									<span class="i-name">icon-arrows-compress</span>
									</li>
									<li>
									<i class="icon-arrows-in"></i>
									<span class="i-name">icon-arrows-in</span>
									</li>
									<li>
									<i class="icon-zoom-out"></i>
									<span class="i-name">icon-zoom-out</span>
									</li>
									<li>
									<i class="icon-coverflow"></i>
									<span class="i-name">icon-coverflow</span>
									</li>
									<li>
									<i class="icon-coverflow-line"></i>
									<span class="i-name">icon-coverflow-line</span>
									</li>
            					</ul>';
                                                  
						   
						$output .= '<input type="hidden" class="capture-input vibe-form-text vibe-input" name="' . $pkey . '" id="' . $pkey . '" value="' . (isset($param['std'])?$param['std']:'') . '" />' . "\n";
						$output .= $row_end;	
						// append
						$this->append_output( $output );
                    break;
				}
			}
			
			// checks if has a child shortcode
			if( isset( $vibe_shortcodes[$this->popup]['child_shortcode'] ) ){
				// set child shortcode
				$this->cparams = $vibe_shortcodes[$this->popup]['child_shortcode']['params'];
				$this->cshortcode = $vibe_shortcodes[$this->popup]['child_shortcode']['shortcode'];
			
				// popup parent form row start
				$prow_start  = '<tbody>' . "\n";
				$prow_start .= '<tr class="form-row has-child">' . "\n";
				$prow_start .= '<td><a href="#" id="form-child-add" class="vibe-btn green">' . $vibe_shortcodes[$this->popup]['child_shortcode']['clone_button'] . '</a>' . "\n";
				$prow_start .= '<div class="child-clone-rows">' . "\n";
				
				// for js use
				$prow_start .= '<div id="_vibe_cshortcode" class="hidden">' . $this->cshortcode . '</div>' . "\n";
				
				// start the default row
				$prow_start .= '<div class="child-clone-row">' . "\n";
				$prow_start .= '<ul class="child-clone-row-form">' . "\n";
				
				// add $prow_start to output
				$this->append_output( $prow_start );
				
				foreach( $this->cparams as $cpkey => $cparam ){
				
					// prefix the fields names and ids with vibe_
					$cpkey = 'vibe_' . $cpkey;
					if(!isset($cparam['desc'])){$cparam['desc'] =''; }
					// popup form row start
					$crow_start  = '<li class="child-clone-row-form-row">' . "\n";
					$crow_start .= '<div class="child-clone-row-label">' . "\n";
					$crow_start .= '<label>' . $cparam['label'] . '</label>' . "\n";
					$crow_start .= '</div>' . "\n";
					$crow_start .= '<div class="child-clone-row-field">' . "\n";
					
					// popup form row end
					$crow_end	  = '<span class="child-clone-row-desc">' . $cparam['desc'] . '</span>' . "\n";
					$crow_end   .= '</div>' . "\n";
					$crow_end   .= '</li>' . "\n";
					
					switch( $cparam['type'] ){
						case 'text' :
							
							// prepare
							$coutput  = $crow_start;
							$coutput .= '<input type="text" class="vibe-form-text vibe-cinput" name="' . $cpkey . '" id="' . $cpkey . '" value="' . $cparam['std'] . '" />' . "\n";
							$coutput .= $crow_end;
							
							// append
							$this->append_output( $coutput );
							
							break;
							
						case 'textarea' :
							
							// prepare
							$coutput  = $crow_start;
							$coutput .= '<textarea rows="10" cols="30" name="' . $cpkey . '" id="' . $cpkey . '" class="vibe-form-textarea vibe-cinput">' . $cparam['std'] . '</textarea>' . "\n";
							$coutput .= $crow_end;
							
							// append
							$this->append_output( $coutput );
							
							break;
							
						case 'select' :
							
							// prepare
							$coutput  = $crow_start;
							$coutput .= '<select name="' . $cpkey . '" id="' . $cpkey . '" class="vibe-form-select vibe-cinput">' . "\n";
							
							foreach( $cparam['options'] as $value => $option ){
								$coutput .= '<option value="' . $value . '">' . $option . '</option>' . "\n";
							}
							
							$coutput .= '</select>' . "\n";
							$coutput .= $crow_end;
							
							// append
							$this->append_output( $coutput );
							
						break;
						case 'multiselect' :
							
							// prepare
							$coutput  = $crow_start;
							$coutput .= '<select name="' . $cpkey . '" id="' . $cpkey . '" class="vibe-form-select vibe-cinput" style="height:50px;" multiple >' . "\n";
							
							foreach( $cparam['options'] as $value => $option ){
								$coutput .= '<option value="' . $value . '">' . $option . '</option>' . "\n";
							}
							
							$coutput .= '</select>' . "\n";
							$coutput .= $crow_end;
							
							// append
							$this->append_output( $coutput );
							
						break;
						case 'color' :
						
							// prepare
							$coutput  = $crow_start;
							$coutput .= '<input type="text" class="vibe-form-text vibe-cinput popup-colorpicker" name="' . $cpkey . '" id="' . $cpkey . '" value="' . $cparam['std'] . '" />' . "\n";
							$coutput .= $crow_end;
							
							// append
							$this->append_output( $coutput );
							
						break;  
					 	case 'slide' :
						
						// prepare
						$coutput  = $crow_start;
						$coutput .= '<input type="text" class="vibe-form-text vibe-input popup-slider" name="' . $cpkey . '" id="' . $cpkey . '" value="' . $cparam['std'] . '" />' . "\n";
						$coutput .= '<div class="slider-range" data-min="' . $cparam['min'] . '" data-max="' . $cparam['max'] . '" data-std="' . $cparam['std'] . '"></div>';
                                                $coutput .= $crow_end;
						
						// append
						$this->append_output( $output );
						
						break; 
                                            
						case 'checkbox' :
							
							// prepare
							$coutput  = $crow_start;
							$coutput .= '<label for="' . $cpkey . '" class="vibe-form-checkbox">' . "\n";
							$coutput .= '<input type="checkbox" class="vibe-cinput" name="' . $cpkey . '" id="' . $cpkey . '" ' . ( $cparam['std'] ? 'checked' : '' ) . ' />' . "\n";
							$coutput .= ' ' . $cparam['checkbox_text'] . '</label>' . "\n";
							$coutput .= $crow_end;
							
							// append
							$this->append_output( $coutput );
							
						break;
                        case 'socialicon' :
                        case 'icon' : 
                         	$coutput  = $crow_start;
                       		$coutput .='<ul class="the-icons unstyled">
									<li>
									<i class="icon-elusive-icons"></i>
									<span class="i-name">icon-elusive-icons</span>
									</li>
									<li>
									<i class="icon-elusive-icons-1"></i>
									<span class="i-name">icon-elusive-icons-1</span>
									</li>
									<li>
									<i class="icon-elusive-icons-2"></i>
									<span class="i-name">icon-elusive-icons-2</span>
									</li>
									<li>
									<i class="icon-elusive-icons-3"></i>
									<span class="i-name">icon-elusive-icons-3</span>
									</li>
									<li>
									<i class="icon-elusive-icons-4"></i>
									<span class="i-name">icon-elusive-icons-4</span>
									</li>
									<li>
									<i class="icon-elusive-icons-5"></i>
									<span class="i-name">icon-elusive-icons-5</span>
									</li>
									<li>
									<i class="icon-elusive-icons-6"></i>
									<span class="i-name">icon-elusive-icons-6</span>
									</li>
									<li>
									<i class="icon-elusive-icons-7"></i>
									<span class="i-name">icon-elusive-icons-7</span>
									</li>
									<li>
									<i class="icon-crown"></i>
									<span class="i-name">icon-crown</span>
									</li>
									<li>
									<i class="icon-burst"></i>
									<span class="i-name">icon-burst</span>
									</li>
									<li>
									<i class="icon-anchor"></i>
									<span class="i-name">icon-anchor</span>
									</li>
									<li>
									<i class="icon-dollar"></i>
									<span class="i-name">icon-dollar</span>
									</li>
									<li>
									<i class="icon-dollar-bill"></i>
									<span class="i-name">icon-dollar-bill</span>
									</li>
									<li>
									<i class="icon-foot"></i>
									<span class="i-name">icon-foot</span>
									</li>
									<li>
									<i class="icon-hearing-aid"></i>
									<span class="i-name">icon-hearing-aid</span>
									</li>
									<li>
									<i class="icon-guide-dog"></i>
									<span class="i-name">icon-guide-dog</span>
									</li>
									<li>
									<i class="icon-first-aid"></i>
									<span class="i-name">icon-first-aid</span>
									</li>
									<li>
									<i class="icon-paint-bucket"></i>
									<span class="i-name">icon-paint-bucket</span>
									</li>
									<li>
									<i class="icon-pencil"></i>
									<span class="i-name">icon-pencil</span>
									</li>
									<li>
									<i class="icon-paw"></i>
									<span class="i-name">icon-paw</span>
									</li>
									<li>
									<i class="icon-paperclip"></i>
									<span class="i-name">icon-paperclip</span>
									</li>
									<li>
									<i class="icon-pound"></i>
									<span class="i-name">icon-pound</span>
									</li>
									<li>
									<i class="icon-shopping-cart"></i>
									<span class="i-name">icon-shopping-cart</span>
									</li>
									<li>
									<i class="icon-sheriff-badge"></i>
									<span class="i-name">icon-sheriff-badge</span>
									</li>
									<li>
									<i class="icon-shield"></i>
									<span class="i-name">icon-shield</span>
									</li>
									<li>
									<i class="icon-trees"></i>
									<span class="i-name">icon-trees</span>
									</li>
									<li>
									<i class="icon-trophy"></i>
									<span class="i-name">icon-trophy</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont"></i>
									<span class="i-name">icon-fontawesome-webfont</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-1"></i>
									<span class="i-name">icon-fontawesome-webfont-1</span>
									</li>
									<li>
									<i class="icon-address"></i>
									<span class="i-name">icon-address</span>
									</li>
									<li>
									<i class="icon-adjust"></i>
									<span class="i-name">icon-adjust</span>
									</li>
									<li>
									<i class="icon-air"></i>
									<span class="i-name">icon-air</span>
									</li>
									<li>
									<i class="icon-alert"></i>
									<span class="i-name">icon-alert</span>
									</li>
									<li>
									<i class="icon-archive"></i>
									<span class="i-name">icon-archive</span>
									</li>
									<li>
									<i class="icon-battery"></i>
									<span class="i-name">icon-battery</span>
									</li>
									<li>
									<i class="icon-behance"></i>
									<span class="i-name">icon-behance</span>
									</li>
									<li>
									<i class="icon-bell"></i>
									<span class="i-name">icon-bell</span>
									</li>
									<li>
									<i class="icon-block"></i>
									<span class="i-name">icon-block</span>
									</li>
									<li>
									<i class="icon-book"></i>
									<span class="i-name">icon-book</span>
									</li>
									<li>
									<i class="icon-camera"></i>
									<span class="i-name">icon-camera</span>
									</li>
									<li>
									<i class="icon-cancel"></i>
									<span class="i-name">icon-cancel</span>
									</li>
									<li>
									<i class="icon-cancel-circled"></i>
									<span class="i-name">icon-cancel-circled</span>
									</li>
									<li>
									<i class="icon-cancel-squared"></i>
									<span class="i-name">icon-cancel-squared</span>
									</li>
									<li>
									<i class="icon-cc"></i>
									<span class="i-name">icon-cc</span>
									</li>
									<li>
									<i class="icon-cc-share"></i>
									<span class="i-name">icon-cc-share</span>
									</li>
									<li>
									<i class="icon-cc-zero"></i>
									<span class="i-name">icon-cc-zero</span>
									</li>
									<li>
									<i class="icon-ccw"></i>
									<span class="i-name">icon-ccw</span>
									</li>
									<li>
									<i class="icon-cd"></i>
									<span class="i-name">icon-cd</span>
									</li>
									<li>
									<i class="icon-chart-area"></i>
									<span class="i-name">icon-chart-area</span>
									</li>
									<li>
									<i class="icon-screen"></i>
									<span class="i-name">icon-screen</span>
									</li>
									<li>
									<i class="icon-delicious"></i>
									<span class="i-name">icon-delicious</span>
									</li>
									<li>
									<i class="icon-instagram"></i>
									<span class="i-name">icon-instagram</span>
									</li>
									<li>
									<i class="icon-alarm"></i>
									<span class="i-name">icon-alarm</span>
									</li>
									<li>
									<i class="icon-envelope"></i>
									<span class="i-name">icon-envelope</span>
									</li>
									<li>
									<i class="icon-chat"></i>
									<span class="i-name">icon-chat</span>
									</li>
									<li>
									<i class="icon-inbox-alt"></i>
									<span class="i-name">icon-inbox-alt</span>
									</li>
									<li>
									<i class="icon-calculator"></i>
									<span class="i-name">icon-calculator</span>
									</li>
									<li>
									<i class="icon-camera-1"></i>
									<span class="i-name">icon-camera-1</span>
									</li>
									<li>
									<i class="icon-brightness-half"></i>
									<span class="i-name">icon-brightness-half</span>
									</li>
									<li>
									<i class="icon-list"></i>
									<span class="i-name">icon-list</span>
									</li>
									<li>
									<i class="icon-spinner"></i>
									<span class="i-name">icon-spinner</span>
									</li>
									<li>
									<i class="icon-windows"></i>
									<span class="i-name">icon-windows</span>
									</li>
									<li>
									<i class="icon-comments"></i>
									<span class="i-name">icon-comments</span>
									</li>
									<li>
									<i class="icon-rewind"></i>
									<span class="i-name">icon-rewind</span>
									</li>
									<li>
									<i class="icon-light-bulb"></i>
									<span class="i-name">icon-light-bulb</span>
									</li>
									<li>
									<i class="icon-iphone"></i>
									<span class="i-name">icon-iphone</span>
									</li>
									<li>
									<i class="icon-heart"></i>
									<span class="i-name">icon-heart</span>
									</li>
									<li>
									<i class="icon-calendar"></i>
									<span class="i-name">icon-calendar</span>
									</li>
									<li>
									<i class="icon-task"></i>
									<span class="i-name">icon-task</span>
									</li>
									<li>
									<i class="icon-store"></i>
									<span class="i-name">icon-store</span>
									</li>
									<li>
									<i class="icon-sound"></i>
									<span class="i-name">icon-sound</span>
									</li>
									<li>
									<i class="icon-fork-and-spoon"></i>
									<span class="i-name">icon-fork-and-spoon</span>
									</li>
									<li>
									<i class="icon-grid"></i>
									<span class="i-name">icon-grid</span>
									</li>
									<li>
									<i class="icon-portfolio"></i>
									<span class="i-name">icon-portfolio</span>
									</li>
									<li>
									<i class="icon-pin-alt"></i>
									<span class="i-name">icon-pin-alt</span>
									</li>
									<li>
									<i class="icon-question"></i>
									<span class="i-name">icon-question</span>
									</li>
									<li>
									<i class="icon-cmd"></i>
									<span class="i-name">icon-cmd</span>
									</li>
									<li>
									<i class="icon-newspaper-alt"></i>
									<span class="i-name">icon-newspaper-alt</span>
									</li>
									<li>
									<i class="icon-moon"></i>
									<span class="i-name">icon-moon</span>
									</li>
									<li>
									<i class="icon-home"></i>
									<span class="i-name">icon-home</span>
									</li>
									<li>
									<i class="icon-sound-alt"></i>
									<span class="i-name">icon-sound-alt</span>
									</li>
									<li>
									<i class="icon-sound-off"></i>
									<span class="i-name">icon-sound-off</span>
									</li>
									<li>
									<i class="icon-ipad"></i>
									<span class="i-name">icon-ipad</span>
									</li>
									<li>
									<i class="icon-stop"></i>
									<span class="i-name">icon-stop</span>
									</li>
									<li>
									<i class="icon-circle-full"></i>
									<span class="i-name">icon-circle-full</span>
									</li>
									<li>
									<i class="icon-forward"></i>
									<span class="i-name">icon-forward</span>
									</li>
									<li>
									<i class="icon-exclamation"></i>
									<span class="i-name">icon-exclamation</span>
									</li>
									<li>
									<i class="icon-settings"></i>
									<span class="i-name">icon-settings</span>
									</li>
									<li>
									<i class="icon-newspaper"></i>
									<span class="i-name">icon-newspaper</span>
									</li>
									<li>
									<i class="icon-grid-alt"></i>
									<span class="i-name">icon-grid-alt</span>
									</li>
									<li>
									<i class="icon-clock"></i>
									<span class="i-name">icon-clock</span>
									</li>
									<li>
									<i class="icon-pause"></i>
									<span class="i-name">icon-pause</span>
									</li>
									<li>
									<i class="icon-globe"></i>
									<span class="i-name">icon-globe</span>
									</li>
									<li>
									<i class="icon-clipboard"></i>
									<span class="i-name">icon-clipboard</span>
									</li>
									<li>
									<i class="icon-attachment"></i>
									<span class="i-name">icon-attachment</span>
									</li>
									<li>
									<i class="icon-forbid-1"></i>
									<span class="i-name">icon-forbid-1</span>
									</li>
									<li>
									<i class="icon-circle-half"></i>
									<span class="i-name">icon-circle-half</span>
									</li>
									<li>
									<i class="icon-inbox"></i>
									<span class="i-name">icon-inbox</span>
									</li>
									<li>
									<i class="icon-fork-and-knife"></i>
									<span class="i-name">icon-fork-and-knife</span>
									</li>
									<li>
									<i class="icon-brightness"></i>
									<span class="i-name">icon-brightness</span>
									</li>
									<li>
									<i class="icon-browser"></i>
									<span class="i-name">icon-browser</span>
									</li>
									<li>
									<i class="icon-hyperlink"></i>
									<span class="i-name">icon-hyperlink</span>
									</li>
									<li>
									<i class="icon-in-alt"></i>
									<span class="i-name">icon-in-alt</span>
									</li>
									<li>
									<i class="icon-menu"></i>
									<span class="i-name">icon-menu</span>
									</li>
									<li>
									<i class="icon-compose"></i>
									<span class="i-name">icon-compose</span>
									</li>
									<li>
									<i class="icon-anchor-1"></i>
									<span class="i-name">icon-anchor-1</span>
									</li>
									<li>
									<i class="icon-gallary"></i>
									<span class="i-name">icon-gallary</span>
									</li>
									<li>
									<i class="icon-cloud"></i>
									<span class="i-name">icon-cloud</span>
									</li>
									<li>
									<i class="icon-pin"></i>
									<span class="i-name">icon-pin</span>
									</li>
									<li>
									<i class="icon-play"></i>
									<span class="i-name">icon-play</span>
									</li>
									<li>
									<i class="icon-tag-stroke"></i>
									<span class="i-name">icon-tag-stroke</span>
									</li>
									<li>
									<i class="icon-tag-fill"></i>
									<span class="i-name">icon-tag-fill</span>
									</li>
									<li>
									<i class="icon-brush"></i>
									<span class="i-name">icon-brush</span>
									</li>
									<li>
									<i class="icon-bars"></i>
									<span class="i-name">icon-bars</span>
									</li>
									<li>
									<i class="icon-eject"></i>
									<span class="i-name">icon-eject</span>
									</li>
									<li>
									<i class="icon-book-1"></i>
									<span class="i-name">icon-book-1</span>
									</li>
									<li>
									<i class="icon-chart"></i>
									<span class="i-name">icon-chart</span>
									</li>
									<li>
									<i class="icon-key-fill"></i>
									<span class="i-name">icon-key-fill</span>
									</li>
									<li>
									<i class="icon-aperture-alt"></i>
									<span class="i-name">icon-aperture-alt</span>
									</li>
									<li>
									<i class="icon-book-alt"></i>
									<span class="i-name">icon-book-alt</span>
									</li>
									<li>
									<i class="icon-list-1"></i>
									<span class="i-name">icon-list-1</span>
									</li>
									<li>
									<i class="icon-map-pin-fill"></i>
									<span class="i-name">icon-map-pin-fill</span>
									</li>
									<li>
									<i class="icon-move-horizontal-alt1"></i>
									<span class="i-name">icon-move-horizontal-alt1</span>
									</li>
									<li>
									<i class="icon-headphones"></i>
									<span class="i-name">icon-headphones</span>
									</li>
									<li>
									<i class="icon-x"></i>
									<span class="i-name">icon-x</span>
									</li>
									<li>
									<i class="icon-check"></i>
									<span class="i-name">icon-check</span>
									</li>
									<li>
									<i class="icon-award-stroke"></i>
									<span class="i-name">icon-award-stroke</span>
									</li>
									<li>
									<i class="icon-wrench"></i>
									<span class="i-name">icon-wrench</span>
									</li>
									<li>
									<i class="icon-sun-fill"></i>
									<span class="i-name">icon-sun-fill</span>
									</li>
									<li>
									<i class="icon-move-horizontal-alt2"></i>
									<span class="i-name">icon-move-horizontal-alt2</span>
									</li>
									<li>
									<i class="icon-left-quote"></i>
									<span class="i-name">icon-left-quote</span>
									</li>
									<li>
									<i class="icon-clock-1"></i>
									<span class="i-name">icon-clock-1</span>
									</li>
									<li>
									<i class="icon-share"></i>
									<span class="i-name">icon-share</span>
									</li>
									<li>
									<i class="icon-map-pin-stroke"></i>
									<span class="i-name">icon-map-pin-stroke</span>
									</li>
									<li>
									<i class="icon-battery-full"></i>
									<span class="i-name">icon-battery-full</span>
									</li>
									<li>
									<i class="icon-paperclip-1"></i>
									<span class="i-name">icon-paperclip-1</span>
									</li>
									<li>
									<i class="icon-beaker-alt"></i>
									<span class="i-name">icon-beaker-alt</span>
									</li>
									<li>
									<i class="icon-bolt"></i>
									<span class="i-name">icon-bolt</span>
									</li>
									<li>
									<i class="icon-at"></i>
									<span class="i-name">icon-at</span>
									</li>
									<li>
									<i class="icon-pin-1"></i>
									<span class="i-name">icon-pin-1</span>
									</li>
									<li>
									<i class="icon-cloud-1"></i>
									<span class="i-name">icon-cloud-1</span>
									</li>
									<li>
									<i class="icon-layers-alt"></i>
									<span class="i-name">icon-layers-alt</span>
									</li>
									<li>
									<i class="icon-fullscreen-exit-alt"></i>
									<span class="i-name">icon-fullscreen-exit-alt</span>
									</li>
									<li>
									<i class="icon-left-quote-alt"></i>
									<span class="i-name">icon-left-quote-alt</span>
									</li>
									<li>
									<i class="icon-move-horizontal"></i>
									<span class="i-name">icon-move-horizontal</span>
									</li>
									<li>
									<i class="icon-volume-mute"></i>
									<span class="i-name">icon-volume-mute</span>
									</li>
									<li>
									<i class="icon-undo"></i>
									<span class="i-name">icon-undo</span>
									</li>
									<li>
									<i class="icon-umbrella"></i>
									<span class="i-name">icon-umbrella</span>
									</li>
									<li>
									<i class="icon-pen-alt2"></i>
									<span class="i-name">icon-pen-alt2</span>
									</li>
									<li>
									<i class="icon-heart-stroke"></i>
									<span class="i-name">icon-heart-stroke</span>
									</li>
									<li>
									<i class="icon-list-nested"></i>
									<span class="i-name">icon-list-nested</span>
									</li>
									<li>
									<i class="icon-move-vertical"></i>
									<span class="i-name">icon-move-vertical</span>
									</li>
									<li>
									<i class="icon-info"></i>
									<span class="i-name">icon-info</span>
									</li>
									<li>
									<i class="icon-pause-1"></i>
									<span class="i-name">icon-pause-1</span>
									</li>
									<li>
									<i class="icon-move-vertical-alt1"></i>
									<span class="i-name">icon-move-vertical-alt1</span>
									</li>
									<li>
									<i class="icon-spin"></i>
									<span class="i-name">icon-spin</span>
									</li>
									<li>
									<i class="icon-pen"></i>
									<span class="i-name">icon-pen</span>
									</li>
									<li>
									<i class="icon-plus-1"></i>
									<span class="i-name">icon-plus-1</span>
									</li>
									<li>
									<i class="icon-cog"></i>
									<span class="i-name">icon-cog</span>
									</li>
									<li>
									<i class="icon-reload"></i>
									<span class="i-name">icon-reload</span>
									</li>
									<li>
									<i class="icon-heart-fill"></i>
									<span class="i-name">icon-heart-fill</span>
									</li>
									<li>
									<i class="icon-equalizer"></i>
									<span class="i-name">icon-equalizer</span>
									</li>
									<li>
									<i class="icon-article"></i>
									<span class="i-name">icon-article</span>
									</li>
									<li>
									<i class="icon-cd-1"></i>
									<span class="i-name">icon-cd-1</span>
									</li>
									<li>
									<i class="icon-link"></i>
									<span class="i-name">icon-link</span>
									</li>
									<li>
									<i class="icon-pilcrow"></i>
									<span class="i-name">icon-pilcrow</span>
									</li>
									<li>
									<i class="icon-hash"></i>
									<span class="i-name">icon-hash</span>
									</li>
									<li>
									<i class="icon-check-alt"></i>
									<span class="i-name">icon-check-alt</span>
									</li>
									<li>
									<i class="icon-key-stroke"></i>
									<span class="i-name">icon-key-stroke</span>
									</li>
									<li>
									<i class="icon-folder-stroke"></i>
									<span class="i-name">icon-folder-stroke</span>
									</li>
									<li>
									<i class="icon-first"></i>
									<span class="i-name">icon-first</span>
									</li>
									<li>
									<i class="icon-eyedropper"></i>
									<span class="i-name">icon-eyedropper</span>
									</li>
									<li>
									<i class="icon-reload-alt"></i>
									<span class="i-name">icon-reload-alt</span>
									</li>
									<li>
									<i class="icon-aperture"></i>
									<span class="i-name">icon-aperture</span>
									</li>
									<li>
									<i class="icon-rain"></i>
									<span class="i-name">icon-rain</span>
									</li>
									<li>
									<i class="icon-beaker"></i>
									<span class="i-name">icon-beaker</span>
									</li>
									<li>
									<i class="icon-bars-alt"></i>
									<span class="i-name">icon-bars-alt</span>
									</li>
									<li>
									<i class="icon-image"></i>
									<span class="i-name">icon-image</span>
									</li>
									<li>
									<i class="icon-spin-alt"></i>
									<span class="i-name">icon-spin-alt</span>
									</li>
									<li>
									<i class="icon-pen-alt-stroke"></i>
									<span class="i-name">icon-pen-alt-stroke</span>
									</li>
									<li>
									<i class="icon-brush-alt"></i>
									<span class="i-name">icon-brush-alt</span>
									</li>
									<li>
									<i class="icon-document-alt-fill"></i>
									<span class="i-name">icon-document-alt-fill</span>
									</li>
									<li>
									<i class="icon-layers"></i>
									<span class="i-name">icon-layers</span>
									</li>
									<li>
									<i class="icon-compass"></i>
									<span class="i-name">icon-compass</span>
									</li>
									<li>
									<i class="icon-unlock-stroke"></i>
									<span class="i-name">icon-unlock-stroke</span>
									</li>
									<li>
									<i class="icon-box"></i>
									<span class="i-name">icon-box</span>
									</li>
									<li>
									<i class="icon-right-quote-alt"></i>
									<span class="i-name">icon-right-quote-alt</span>
									</li>
									<li>
									<i class="icon-last"></i>
									<span class="i-name">icon-last</span>
									</li>
									<li>
									<i class="icon-award-fill"></i>
									<span class="i-name">icon-award-fill</span>
									</li>
									<li>
									<i class="icon-pen-alt-fill"></i>
									<span class="i-name">icon-pen-alt-fill</span>
									</li>
									<li>
									<i class="icon-lock-fill"></i>
									<span class="i-name">icon-lock-fill</span>
									</li>
									<li>
									<i class="icon-calendar-alt-stroke"></i>
									<span class="i-name">icon-calendar-alt-stroke</span>
									</li>
									<li>
									<i class="icon-move-vertical-alt2"></i>
									<span class="i-name">icon-move-vertical-alt2</span>
									</li>
									<li>
									<i class="icon-steering-wheel"></i>
									<span class="i-name">icon-steering-wheel</span>
									</li>
									<li>
									<i class="icon-minus"></i>
									<span class="i-name">icon-minus</span>
									</li>
									<li>
									<i class="icon-map-pin-alt"></i>
									<span class="i-name">icon-map-pin-alt</span>
									</li>
									<li>
									<i class="icon-eye"></i>
									<span class="i-name">icon-eye</span>
									</li>
									<li>
									<i class="icon-calendar-alt-fill"></i>
									<span class="i-name">icon-calendar-alt-fill</span>
									</li>
									<li>
									<i class="icon-play-alt"></i>
									<span class="i-name">icon-play-alt</span>
									</li>
									<li>
									<i class="icon-fullscreen"></i>
									<span class="i-name">icon-fullscreen</span>
									</li>
									<li>
									<i class="icon-target"></i>
									<span class="i-name">icon-target</span>
									</li>
									<li>
									<i class="icon-dial"></i>
									<span class="i-name">icon-dial</span>
									</li>
									<li>
									<i class="icon-ampersand"></i>
									<span class="i-name">icon-ampersand</span>
									</li>
									<li>
									<i class="icon-question-mark"></i>
									<span class="i-name">icon-question-mark</span>
									</li>
									<li>
									<i class="icon-moon-stroke"></i>
									<span class="i-name">icon-moon-stroke</span>
									</li>
									<li>
									<i class="icon-movie"></i>
									<span class="i-name">icon-movie</span>
									</li>
									<li>
									<i class="icon-battery-charging"></i>
									<span class="i-name">icon-battery-charging</span>
									</li>
									<li>
									<i class="icon-document-stroke"></i>
									<span class="i-name">icon-document-stroke</span>
									</li>
									<li>
									<i class="icon-document-alt-stroke"></i>
									<span class="i-name">icon-document-alt-stroke</span>
									</li>
									<li>
									<i class="icon-lightbulb"></i>
									<span class="i-name">icon-lightbulb</span>
									</li>
									<li>
									<i class="icon-calendar-1"></i>
									<span class="i-name">icon-calendar-1</span>
									</li>
									<li>
									<i class="icon-unlock-fill"></i>
									<span class="i-name">icon-unlock-fill</span>
									</li>
									<li>
									<i class="icon-battery-empty"></i>
									<span class="i-name">icon-battery-empty</span>
									</li>
									<li>
									<i class="icon-sun-stroke"></i>
									<span class="i-name">icon-sun-stroke</span>
									</li>
									<li>
									<i class="icon-chart-alt"></i>
									<span class="i-name">icon-chart-alt</span>
									</li>
									<li>
									<i class="icon-battery-half"></i>
									<span class="i-name">icon-battery-half</span>
									</li>
									<li>
									<i class="icon-lock-stroke"></i>
									<span class="i-name">icon-lock-stroke</span>
									</li>
									<li>
									<i class="icon-book-alt2"></i>
									<span class="i-name">icon-book-alt2</span>
									</li>
									<li>
									<i class="icon-loop-alt1"></i>
									<span class="i-name">icon-loop-alt1</span>
									</li>
									<li>
									<i class="icon-fullscreen-exit"></i>
									<span class="i-name">icon-fullscreen-exit</span>
									</li>
									<li>
									<i class="icon-volume"></i>
									<span class="i-name">icon-volume</span>
									</li>
									<li>
									<i class="icon-mic"></i>
									<span class="i-name">icon-mic</span>
									</li>
									<li>
									<i class="icon-right-quote"></i>
									<span class="i-name">icon-right-quote</span>
									</li>
									<li>
									<i class="icon-play-1"></i>
									<span class="i-name">icon-play-1</span>
									</li>
									<li>
									<i class="icon-folder-fill"></i>
									<span class="i-name">icon-folder-fill</span>
									</li>
									<li>
									<i class="icon-moon-fill"></i>
									<span class="i-name">icon-moon-fill</span>
									</li>
									<li>
									<i class="icon-home-1"></i>
									<span class="i-name">icon-home-1</span>
									</li>
									<li>
									<i class="icon-camera-2"></i>
									<span class="i-name">icon-camera-2</span>
									</li>
									<li>
									<i class="icon-star"></i>
									<span class="i-name">icon-star</span>
									</li>
									<li>
									<i class="icon-read-more"></i>
									<span class="i-name">icon-read-more</span>
									</li>
									<li>
									<i class="icon-document-fill"></i>
									<span class="i-name">icon-document-fill</span>
									</li>
									<li>
									<i class="icon-excel-table-1"></i>
									<span class="i-name">icon-excel-table-1</span>
									</li>
									<li>
									<i class="icon-arrow-1-up"></i>
									<span class="i-name">icon-arrow-1-up</span>
									</li>
									<li>
									<i class="icon-female-symbol"></i>
									<span class="i-name">icon-female-symbol</span>
									</li>
									<li>
									<i class="icon-delivery-transport-2"></i>
									<span class="i-name">icon-delivery-transport-2</span>
									</li>
									<li>
									<i class="icon-content-41"></i>
									<span class="i-name">icon-content-41</span>
									</li>
									<li>
									<i class="icon-clip-paper-1"></i>
									<span class="i-name">icon-clip-paper-1</span>
									</li>
									<li>
									<i class="icon-check-5"></i>
									<span class="i-name">icon-check-5</span>
									</li>
									<li>
									<i class="icon-feed-rss-2"></i>
									<span class="i-name">icon-feed-rss-2</span>
									</li>
									<li>
									<i class="icon-server-1"></i>
									<span class="i-name">icon-server-1</span>
									</li>
									<li>
									<i class="icon-harddrive"></i>
									<span class="i-name">icon-harddrive</span>
									</li>
									<li>
									<i class="icon-car"></i>
									<span class="i-name">icon-car</span>
									</li>
									<li>
									<i class="icon-direction-move-1"></i>
									<span class="i-name">icon-direction-move-1</span>
									</li>
									<li>
									<i class="icon-certificate-file"></i>
									<span class="i-name">icon-certificate-file</span>
									</li>
									<li>
									<i class="icon-analytics-file-1"></i>
									<span class="i-name">icon-analytics-file-1</span>
									</li>
									<li>
									<i class="icon-male-symbol"></i>
									<span class="i-name">icon-male-symbol</span>
									</li>
									<li>
									<i class="icon-send-to-front"></i>
									<span class="i-name">icon-send-to-front</span>
									</li>
									<li>
									<i class="icon-movie-play-file-1"></i>
									<span class="i-name">icon-movie-play-file-1</span>
									</li>
									<li>
									<i class="icon-bookmark-tag"></i>
									<span class="i-name">icon-bookmark-tag</span>
									</li>
									<li>
									<i class="icon-filled-folder-1"></i>
									<span class="i-name">icon-filled-folder-1</span>
									</li>
									<li>
									<i class="icon-check-clipboard-1"></i>
									<span class="i-name">icon-check-clipboard-1</span>
									</li>
									<li>
									<i class="icon-clouds-cloudy"></i>
									<span class="i-name">icon-clouds-cloudy</span>
									</li>
									<li>
									<i class="icon-gears-setting"></i>
									<span class="i-name">icon-gears-setting</span>
									</li>
									<li>
									<i class="icon-html"></i>
									<span class="i-name">icon-html</span>
									</li>
									<li>
									<i class="icon-palm-tree"></i>
									<span class="i-name">icon-palm-tree</span>
									</li>
									<li>
									<i class="icon-wallet-money"></i>
									<span class="i-name">icon-wallet-money</span>
									</li>
									<li>
									<i class="icon-hospital"></i>
									<span class="i-name">icon-hospital</span>
									</li>
									<li>
									<i class="icon-previous-1"></i>
									<span class="i-name">icon-previous-1</span>
									</li>
									<li>
									<i class="icon-mailbox-1"></i>
									<span class="i-name">icon-mailbox-1</span>
									</li>
									<li>
									<i class="icon-arrow-1-right"></i>
									<span class="i-name">icon-arrow-1-right</span>
									</li>
									<li>
									<i class="icon-dropbox"></i>
									<span class="i-name">icon-dropbox</span>
									</li>
									<li>
									<i class="icon-rocket"></i>
									<span class="i-name">icon-rocket</span>
									</li>
									<li>
									<i class="icon-credit-card"></i>
									<span class="i-name">icon-credit-card</span>
									</li>
									<li>
									<i class="icon-campfire"></i>
									<span class="i-name">icon-campfire</span>
									</li>
									<li>
									<i class="icon-yang-ying"></i>
									<span class="i-name">icon-yang-ying</span>
									</li>
									<li>
									<i class="icon-omg-smiley"></i>
									<span class="i-name">icon-omg-smiley</span>
									</li>
									<li>
									<i class="icon-angry-smiley"></i>
									<span class="i-name">icon-angry-smiley</span>
									</li>
									<li>
									<i class="icon-television-tv"></i>
									<span class="i-name">icon-television-tv</span>
									</li>
									<li>
									<i class="icon-camera-surveillance-1"></i>
									<span class="i-name">icon-camera-surveillance-1</span>
									</li>
									<li>
									<i class="icon-apple"></i>
									<span class="i-name">icon-apple</span>
									</li>
									<li>
									<i class="icon-content-14"></i>
									<span class="i-name">icon-content-14</span>
									</li>
									<li>
									<i class="icon-hour-glass"></i>
									<span class="i-name">icon-hour-glass</span>
									</li>
									<li>
									<i class="icon-content-7"></i>
									<span class="i-name">icon-content-7</span>
									</li>
									<li>
									<i class="icon-arrow-right-1"></i>
									<span class="i-name">icon-arrow-right-1</span>
									</li>
									<li>
									<i class="icon-image-photo-file-1"></i>
									<span class="i-name">icon-image-photo-file-1</span>
									</li>
									<li>
									<i class="icon-bus"></i>
									<span class="i-name">icon-bus</span>
									</li>
									<li>
									<i class="icon-blink-smiley"></i>
									<span class="i-name">icon-blink-smiley</span>
									</li>
									<li>
									<i class="icon-bubbles-talk-1"></i>
									<span class="i-name">icon-bubbles-talk-1</span>
									</li>
									<li>
									<i class="icon-brush-1"></i>
									<span class="i-name">icon-brush-1</span>
									</li>
									<li>
									<i class="icon-send-to-back"></i>
									<span class="i-name">icon-send-to-back</span>
									</li>
									<li>
									<i class="icon-camera-video-3"></i>
									<span class="i-name">icon-camera-video-3</span>
									</li>
									<li>
									<i class="icon-battery-low"></i>
									<span class="i-name">icon-battery-low</span>
									</li>
									<li>
									<i class="icon-movie-play-1"></i>
									<span class="i-name">icon-movie-play-1</span>
									</li>
									<li>
									<i class="icon-home-1-1"></i>
									<span class="i-name">icon-home-1-1</span>
									</li>
									<li>
									<i class="icon-cd-cover-music"></i>
									<span class="i-name">icon-cd-cover-music</span>
									</li>
									<li>
									<i class="icon-linkedin-alt"></i>
									<span class="i-name">icon-linkedin-alt</span>
									</li>
									<li>
									<i class="icon-video-1"></i>
									<span class="i-name">icon-video-1</span>
									</li>
									<li>
									<i class="icon-bookmark-star-favorite"></i>
									<span class="i-name">icon-bookmark-star-favorite</span>
									</li>
									<li>
									<i class="icon-play-1-1"></i>
									<span class="i-name">icon-play-1-1</span>
									</li>
									<li>
									<i class="icon-pause-1-1"></i>
									<span class="i-name">icon-pause-1-1</span>
									</li>
									<li>
									<i class="icon-paint-brush-2"></i>
									<span class="i-name">icon-paint-brush-2</span>
									</li>
									<li>
									<i class="icon-train"></i>
									<span class="i-name">icon-train</span>
									</li>
									<li>
									<i class="icon-happy-smiley"></i>
									<span class="i-name">icon-happy-smiley</span>
									</li>
									<li>
									<i class="icon-missile-rocket"></i>
									<span class="i-name">icon-missile-rocket</span>
									</li>
									<li>
									<i class="icon-cloud-2"></i>
									<span class="i-name">icon-cloud-2</span>
									</li>
									<li>
									<i class="icon-bookmark-file-1"></i>
									<span class="i-name">icon-bookmark-file-1</span>
									</li>
									<li>
									<i class="icon-scooter"></i>
									<span class="i-name">icon-scooter</span>
									</li>
									<li>
									<i class="icon-magnet"></i>
									<span class="i-name">icon-magnet</span>
									</li>
									<li>
									<i class="icon-letter-mail-1"></i>
									<span class="i-name">icon-letter-mail-1</span>
									</li>
									<li>
									<i class="icon-color-palette"></i>
									<span class="i-name">icon-color-palette</span>
									</li>
									<li>
									<i class="icon-content-43"></i>
									<span class="i-name">icon-content-43</span>
									</li>
									<li>
									<i class="icon-bubble-talk-1"></i>
									<span class="i-name">icon-bubble-talk-1</span>
									</li>
									<li>
									<i class="icon-content-34"></i>
									<span class="i-name">icon-content-34</span>
									</li>
									<li>
									<i class="icon-carton-milk"></i>
									<span class="i-name">icon-carton-milk</span>
									</li>
									<li>
									<i class="icon-male-user-4"></i>
									<span class="i-name">icon-male-user-4</span>
									</li>
									<li>
									<i class="icon-ink-pen"></i>
									<span class="i-name">icon-ink-pen</span>
									</li>
									<li>
									<i class="icon-camera-1-1"></i>
									<span class="i-name">icon-camera-1-1</span>
									</li>
									<li>
									<i class="icon-snow-weather"></i>
									<span class="i-name">icon-snow-weather</span>
									</li>
									<li>
									<i class="icon-refresh-reload-1"></i>
									<span class="i-name">icon-refresh-reload-1</span>
									</li>
									<li>
									<i class="icon-at-email"></i>
									<span class="i-name">icon-at-email</span>
									</li>
									<li>
									<i class="icon-umbrella-1"></i>
									<span class="i-name">icon-umbrella-1</span>
									</li>
									<li>
									<i class="icon-lock-secure-1"></i>
									<span class="i-name">icon-lock-secure-1</span>
									</li>
									<li>
									<i class="icon-hand-stop"></i>
									<span class="i-name">icon-hand-stop</span>
									</li>
									<li>
									<i class="icon-battery-half-1"></i>
									<span class="i-name">icon-battery-half-1</span>
									</li>
									<li>
									<i class="icon-text-document"></i>
									<span class="i-name">icon-text-document</span>
									</li>
									<li>
									<i class="icon-layers-1"></i>
									<span class="i-name">icon-layers-1</span>
									</li>
									<li>
									<i class="icon-paypal"></i>
									<span class="i-name">icon-paypal</span>
									</li>
									<li>
									<i class="icon-helicopter"></i>
									<span class="i-name">icon-helicopter</span>
									</li>
									<li>
									<i class="icon-content-42"></i>
									<span class="i-name">icon-content-42</span>
									</li>
									<li>
									<i class="icon-clothes-hanger"></i>
									<span class="i-name">icon-clothes-hanger</span>
									</li>
									<li>
									<i class="icon-plus-zoom"></i>
									<span class="i-name">icon-plus-zoom</span>
									</li>
									<li>
									<i class="icon-unlock"></i>
									<span class="i-name">icon-unlock</span>
									</li>
									<li>
									<i class="icon-microscope"></i>
									<span class="i-name">icon-microscope</span>
									</li>
									<li>
									<i class="icon-click-hand-1"></i>
									<span class="i-name">icon-click-hand-1</span>
									</li>
									<li>
									<i class="icon-briefcase"></i>
									<span class="i-name">icon-briefcase</span>
									</li>
									<li>
									<i class="icon-3-css"></i>
									<span class="i-name">icon-3-css</span>
									</li>
									<li>
									<i class="icon-google-plus-1"></i>
									<span class="i-name">icon-google-plus-1</span>
									</li>
									<li>
									<i class="icon-close-off-2"></i>
									<span class="i-name">icon-close-off-2</span>
									</li>
									<li>
									<i class="icon-music-file-1"></i>
									<span class="i-name">icon-music-file-1</span>
									</li>
									<li>
									<i class="icon-tree"></i>
									<span class="i-name">icon-tree</span>
									</li>
									<li>
									<i class="icon-forward-1"></i>
									<span class="i-name">icon-forward-1</span>
									</li>
									<li>
									<i class="icon-script"></i>
									<span class="i-name">icon-script</span>
									</li>
									<li>
									<i class="icon-edit-pen-1"></i>
									<span class="i-name">icon-edit-pen-1</span>
									</li>
									<li>
									<i class="icon-content-1"></i>
									<span class="i-name">icon-content-1</span>
									</li>
									<li>
									<i class="icon-cash-register"></i>
									<span class="i-name">icon-cash-register</span>
									</li>
									<li>
									<i class="icon-call-old-telephone"></i>
									<span class="i-name">icon-call-old-telephone</span>
									</li>
									<li>
									<i class="icon-hail-weather"></i>
									<span class="i-name">icon-hail-weather</span>
									</li>
									<li>
									<i class="icon-gift"></i>
									<span class="i-name">icon-gift</span>
									</li>
									<li>
									<i class="icon-square-vector-2"></i>
									<span class="i-name">icon-square-vector-2</span>
									</li>
									<li>
									<i class="icon-van"></i>
									<span class="i-name">icon-van</span>
									</li>
									<li>
									<i class="icon-male-user-3"></i>
									<span class="i-name">icon-male-user-3</span>
									</li>
									<li>
									<i class="icon-content-8"></i>
									<span class="i-name">icon-content-8</span>
									</li>
									<li>
									<i class="icon-battery-charging-1"></i>
									<span class="i-name">icon-battery-charging-1</span>
									</li>
									<li>
									<i class="icon-rewind-1"></i>
									<span class="i-name">icon-rewind-1</span>
									</li>
									<li>
									<i class="icon-check-1"></i>
									<span class="i-name">icon-check-1</span>
									</li>
									<li>
									<i class="icon-airplane"></i>
									<span class="i-name">icon-airplane</span>
									</li>
									<li>
									<i class="icon-hat-magician"></i>
									<span class="i-name">icon-hat-magician</span>
									</li>
									<li>
									<i class="icon-boat"></i>
									<span class="i-name">icon-boat</span>
									</li>
									<li>
									<i class="icon-crown-king-1"></i>
									<span class="i-name">icon-crown-king-1</span>
									</li>
									<li>
									<i class="icon-bike"></i>
									<span class="i-name">icon-bike</span>
									</li>
									<li>
									<i class="icon-sad-smiley"></i>
									<span class="i-name">icon-sad-smiley</span>
									</li>
									<li>
									<i class="icon-burning-fire"></i>
									<span class="i-name">icon-burning-fire</span>
									</li>
									<li>
									<i class="icon-thermometer"></i>
									<span class="i-name">icon-thermometer</span>
									</li>
									<li>
									<i class="icon-map-pin-5"></i>
									<span class="i-name">icon-map-pin-5</span>
									</li>
									<li>
									<i class="icon-happy-smiley-very"></i>
									<span class="i-name">icon-happy-smiley-very</span>
									</li>
									<li>
									<i class="icon-eye-view-1"></i>
									<span class="i-name">icon-eye-view-1</span>
									</li>
									<li>
									<i class="icon-cannabis-hemp"></i>
									<span class="i-name">icon-cannabis-hemp</span>
									</li>
									<li>
									<i class="icon-interface-window-1"></i>
									<span class="i-name">icon-interface-window-1</span>
									</li>
									<li>
									<i class="icon-document-file-1"></i>
									<span class="i-name">icon-document-file-1</span>
									</li>
									<li>
									<i class="icon-arrow-1-left"></i>
									<span class="i-name">icon-arrow-1-left</span>
									</li>
									<li>
									<i class="icon-nurse-user"></i>
									<span class="i-name">icon-nurse-user</span>
									</li>
									<li>
									<i class="icon-content-44"></i>
									<span class="i-name">icon-content-44</span>
									</li>
									<li>
									<i class="icon-flag-mark"></i>
									<span class="i-name">icon-flag-mark</span>
									</li>
									<li>
									<i class="icon-square-vector-1"></i>
									<span class="i-name">icon-square-vector-1</span>
									</li>
									<li>
									<i class="icon-monitor-screen-1"></i>
									<span class="i-name">icon-monitor-screen-1</span>
									</li>
									<li>
									<i class="icon-next-1"></i>
									<span class="i-name">icon-next-1</span>
									</li>
									<li>
									<i class="icon-doctor"></i>
									<span class="i-name">icon-doctor</span>
									</li>
									<li>
									<i class="icon-favorite-map-pin"></i>
									<span class="i-name">icon-favorite-map-pin</span>
									</li>
									<li>
									<i class="icon-rain-weather"></i>
									<span class="i-name">icon-rain-weather</span>
									</li>
									<li>
									<i class="icon-polaroid"></i>
									<span class="i-name">icon-polaroid</span>
									</li>
									<li>
									<i class="icon-analytics-chart-graph"></i>
									<span class="i-name">icon-analytics-chart-graph</span>
									</li>
									<li>
									<i class="icon-medal-outline-star"></i>
									<span class="i-name">icon-medal-outline-star</span>
									</li>
									<li>
									<i class="icon-lightbulb-shine"></i>
									<span class="i-name">icon-lightbulb-shine</span>
									</li>
									<li>
									<i class="icon-arrow-down-1"></i>
									<span class="i-name">icon-arrow-down-1</span>
									</li>
									<li>
									<i class="icon-favorite-heart-outline"></i>
									<span class="i-name">icon-favorite-heart-outline</span>
									</li>
									<li>
									<i class="icon-advertising-megaphone-2"></i>
									<span class="i-name">icon-advertising-megaphone-2</span>
									</li>
									<li>
									<i class="icon-interface-windows"></i>
									<span class="i-name">icon-interface-windows</span>
									</li>
									<li>
									<i class="icon-ipod"></i>
									<span class="i-name">icon-ipod</span>
									</li>
									<li>
									<i class="icon-radar-2"></i>
									<span class="i-name">icon-radar-2</span>
									</li>
									<li>
									<i class="icon-minus-zoom"></i>
									<span class="i-name">icon-minus-zoom</span>
									</li>
									<li>
									<i class="icon-crhistmas-spruce-tree"></i>
									<span class="i-name">icon-crhistmas-spruce-tree</span>
									</li>
									<li>
									<i class="icon-arrow-cursor"></i>
									<span class="i-name">icon-arrow-cursor</span>
									</li>
									<li>
									<i class="icon-medal-rank-star"></i>
									<span class="i-name">icon-medal-rank-star</span>
									</li>
									<li>
									<i class="icon-database-5"></i>
									<span class="i-name">icon-database-5</span>
									</li>
									<li>
									<i class="icon-battery-full-1"></i>
									<span class="i-name">icon-battery-full-1</span>
									</li>
									<li>
									<i class="icon-chart-graph-file-1"></i>
									<span class="i-name">icon-chart-graph-file-1</span>
									</li>
									<li>
									<i class="icon-case-medic"></i>
									<span class="i-name">icon-case-medic</span>
									</li>
									<li>
									<i class="icon-disc-floppy-font"></i>
									<span class="i-name">icon-disc-floppy-font</span>
									</li>
									<li>
									<i class="icon-sun-weather"></i>
									<span class="i-name">icon-sun-weather</span>
									</li>
									<li>
									<i class="icon-parking-sign"></i>
									<span class="i-name">icon-parking-sign</span>
									</li>
									<li>
									<i class="icon-code-html-file-1"></i>
									<span class="i-name">icon-code-html-file-1</span>
									</li>
									<li>
									<i class="icon-date"></i>
									<span class="i-name">icon-date</span>
									</li>
									<li>
									<i class="icon-hand-hold"></i>
									<span class="i-name">icon-hand-hold</span>
									</li>
									<li>
									<i class="icon-cup-2"></i>
									<span class="i-name">icon-cup-2</span>
									</li>
									<li>
									<i class="icon-lightning-weather"></i>
									<span class="i-name">icon-lightning-weather</span>
									</li>
									<li>
									<i class="icon-cloud-sun"></i>
									<span class="i-name">icon-cloud-sun</span>
									</li>
									<li>
									<i class="icon-compressed-zip-file"></i>
									<span class="i-name">icon-compressed-zip-file</span>
									</li>
									<li>
									<i class="icon-road"></i>
									<span class="i-name">icon-road</span>
									</li>
									<li>
									<i class="icon-arrow-left-1"></i>
									<span class="i-name">icon-arrow-left-1</span>
									</li>
									<li>
									<i class="icon-building-24"></i>
									<span class="i-name">icon-building-24</span>
									</li>
									<li>
									<i class="icon-tennis-24"></i>
									<span class="i-name">icon-tennis-24</span>
									</li>
									<li>
									<i class="icon-skiing-24"></i>
									<span class="i-name">icon-skiing-24</span>
									</li>
									<li>
									<i class="icon-bus-24"></i>
									<span class="i-name">icon-bus-24</span>
									</li>
									<li>
									<i class="icon-park2-24"></i>
									<span class="i-name">icon-park2-24</span>
									</li>
									<li>
									<i class="icon-circle-24"></i>
									<span class="i-name">icon-circle-24</span>
									</li>
									<li>
									<i class="icon-golf-24"></i>
									<span class="i-name">icon-golf-24</span>
									</li>
									<li>
									<i class="icon-star-24"></i>
									<span class="i-name">icon-star-24</span>
									</li>
									<li>
									<i class="icon-water-24"></i>
									<span class="i-name">icon-water-24</span>
									</li>
									<li>
									<i class="icon-disability-24"></i>
									<span class="i-name">icon-disability-24</span>
									</li>
									<li>
									<i class="icon-art-gallery-24"></i>
									<span class="i-name">icon-art-gallery-24</span>
									</li>
									<li>
									<i class="icon-religious-jewish-24"></i>
									<span class="i-name">icon-religious-jewish-24</span>
									</li>
									<li>
									<i class="icon-marker-24"></i>
									<span class="i-name">icon-marker-24</span>
									</li>
									<li>
									<i class="icon-campsite-24"></i>
									<span class="i-name">icon-campsite-24</span>
									</li>
									<li>
									<i class="icon-prison-24"></i>
									<span class="i-name">icon-prison-24</span>
									</li>
									<li>
									<i class="icon-baseball-24"></i>
									<span class="i-name">icon-baseball-24</span>
									</li>
									<li>
									<i class="icon-pharmacy-24"></i>
									<span class="i-name">icon-pharmacy-24</span>
									</li>
									<li>
									<i class="icon-zoo-24"></i>
									<span class="i-name">icon-zoo-24</span>
									</li>
									<li>
									<i class="icon-triangle-stroked-24"></i>
									<span class="i-name">icon-triangle-stroked-24</span>
									</li>
									<li>
									<i class="icon-star-stroked-24"></i>
									<span class="i-name">icon-star-stroked-24</span>
									</li>
									<li>
									<i class="icon-slaughterhouse-24"></i>
									<span class="i-name">icon-slaughterhouse-24</span>
									</li>
									<li>
									<i class="icon-parking-24"></i>
									<span class="i-name">icon-parking-24</span>
									</li>
									<li>
									<i class="icon-heliport-24"></i>
									<span class="i-name">icon-heliport-24</span>
									</li>
									<li>
									<i class="icon-restaurant-24"></i>
									<span class="i-name">icon-restaurant-24</span>
									</li>
									<li>
									<i class="icon-shop-24"></i>
									<span class="i-name">icon-shop-24</span>
									</li>
									<li>
									<i class="icon-religious-christian-24"></i>
									<span class="i-name">icon-religious-christian-24</span>
									</li>
									<li>
									<i class="icon-museum-24"></i>
									<span class="i-name">icon-museum-24</span>
									</li>
									<li>
									<i class="icon-cross"></i>
									<span class="i-name">icon-cross</span>
									</li>
									<li>
									<i class="icon-toilets-24"></i>
									<span class="i-name">icon-toilets-24</span>
									</li>
									<li>
									<i class="icon-rail-underground-24"></i>
									<span class="i-name">icon-rail-underground-24</span>
									</li>
									<li>
									<i class="icon-basketball-24"></i>
									<span class="i-name">icon-basketball-24</span>
									</li>
									<li>
									<i class="icon-beer-24"></i>
									<span class="i-name">icon-beer-24</span>
									</li>
									<li>
									<i class="icon-airfield-24"></i>
									<span class="i-name">icon-airfield-24</span>
									</li>
									<li>
									<i class="icon-wetland-24"></i>
									<span class="i-name">icon-wetland-24</span>
									</li>
									<li>
									<i class="icon-soccer-24"></i>
									<span class="i-name">icon-soccer-24</span>
									</li>
									<li>
									<i class="icon-dam"></i>
									<span class="i-name">icon-dam</span>
									</li>
									<li>
									<i class="icon-bank-24"></i>
									<span class="i-name">icon-bank-24</span>
									</li>
									<li>
									<i class="icon-fuel-24"></i>
									<span class="i-name">icon-fuel-24</span>
									</li>
									<li>
									<i class="icon-school-24"></i>
									<span class="i-name">icon-school-24</span>
									</li>
									<li>
									<i class="icon-commercial-24"></i>
									<span class="i-name">icon-commercial-24</span>
									</li>
									<li>
									<i class="icon-religious-muslim-24"></i>
									<span class="i-name">icon-religious-muslim-24</span>
									</li>
									<li>
									<i class="icon-america-football-24"></i>
									<span class="i-name">icon-america-football-24</span>
									</li>
									<li>
									<i class="icon-swimming-24"></i>
									<span class="i-name">icon-swimming-24</span>
									</li>
									<li>
									<i class="icon-square-24"></i>
									<span class="i-name">icon-square-24</span>
									</li>
									<li>
									<i class="icon-circle-stroked-24"></i>
									<span class="i-name">icon-circle-stroked-24</span>
									</li>
									<li>
									<i class="icon-rail-above-24"></i>
									<span class="i-name">icon-rail-above-24</span>
									</li>
									<li>
									<i class="icon-monument-24"></i>
									<span class="i-name">icon-monument-24</span>
									</li>
									<li>
									<i class="icon-cinema-24"></i>
									<span class="i-name">icon-cinema-24</span>
									</li>
									<li>
									<i class="icon-ferry-24"></i>
									<span class="i-name">icon-ferry-24</span>
									</li>
									<li>
									<i class="icon-fast-food-24"></i>
									<span class="i-name">icon-fast-food-24</span>
									</li>
									<li>
									<i class="icon-cemetery-24"></i>
									<span class="i-name">icon-cemetery-24</span>
									</li>
									<li>
									<i class="icon-park-24"></i>
									<span class="i-name">icon-park-24</span>
									</li>
									<li>
									<i class="icon-telephone-24"></i>
									<span class="i-name">icon-telephone-24</span>
									</li>
									<li>
									<i class="icon-rail-24"></i>
									<span class="i-name">icon-rail-24</span>
									</li>
									<li>
									<i class="icon-college-24"></i>
									<span class="i-name">icon-college-24</span>
									</li>
									<li>
									<i class="icon-warehouse-24"></i>
									<span class="i-name">icon-warehouse-24</span>
									</li>
									<li>
									<i class="icon-hospital-24"></i>
									<span class="i-name">icon-hospital-24</span>
									</li>
									<li>
									<i class="icon-cricket-24"></i>
									<span class="i-name">icon-cricket-24</span>
									</li>
									<li>
									<i class="icon-parking-garage-24"></i>
									<span class="i-name">icon-parking-garage-24</span>
									</li>
									<li>
									<i class="icon-harbor-24"></i>
									<span class="i-name">icon-harbor-24</span>
									</li>
									<li>
									<i class="icon-cafe-24"></i>
									<span class="i-name">icon-cafe-24</span>
									</li>
									<li>
									<i class="icon-police-24"></i>
									<span class="i-name">icon-police-24</span>
									</li>
									<li>
									<i class="icon-industrial-24"></i>
									<span class="i-name">icon-industrial-24</span>
									</li>
									<li>
									<i class="icon-garden-24"></i>
									<span class="i-name">icon-garden-24</span>
									</li>
									<li>
									<i class="icon-triangle-24"></i>
									<span class="i-name">icon-triangle-24</span>
									</li>
									<li>
									<i class="icon-bar-24"></i>
									<span class="i-name">icon-bar-24</span>
									</li>
									<li>
									<i class="icon-place-of-worship-24"></i>
									<span class="i-name">icon-place-of-worship-24</span>
									</li>
									<li>
									<i class="icon-oil-well-24"></i>
									<span class="i-name">icon-oil-well-24</span>
									</li>
									<li>
									<i class="icon-library-24"></i>
									<span class="i-name">icon-library-24</span>
									</li>
									<li>
									<i class="icon-alcohol-shop-24"></i>
									<span class="i-name">icon-alcohol-shop-24</span>
									</li>
									<li>
									<i class="icon-bicycle-24"></i>
									<span class="i-name">icon-bicycle-24</span>
									</li>
									<li>
									<i class="icon-town-hall-24"></i>
									<span class="i-name">icon-town-hall-24</span>
									</li>
									<li>
									<i class="icon-music-24"></i>
									<span class="i-name">icon-music-24</span>
									</li>
									<li>
									<i class="icon-square-stroked-24"></i>
									<span class="i-name">icon-square-stroked-24</span>
									</li>
									<li>
									<i class="icon-pitch-24"></i>
									<span class="i-name">icon-pitch-24</span>
									</li>
									<li>
									<i class="icon-danger-24"></i>
									<span class="i-name">icon-danger-24</span>
									</li>
									<li>
									<i class="icon-fire-station-24"></i>
									<span class="i-name">icon-fire-station-24</span>
									</li>
									<li>
									<i class="icon-theatre-24"></i>
									<span class="i-name">icon-theatre-24</span>
									</li>
									<li>
									<i class="icon-marker-stroked-24"></i>
									<span class="i-name">icon-marker-stroked-24</span>
									</li>
									<li>
									<i class="icon-lodging-24"></i>
									<span class="i-name">icon-lodging-24</span>
									</li>
									<li>
									<i class="icon-embassy-24"></i>
									<span class="i-name">icon-embassy-24</span>
									</li>
									<li>
									<i class="icon-airport-24"></i>
									<span class="i-name">icon-airport-24</span>
									</li>
									<li>
									<i class="icon-logging-24"></i>
									<span class="i-name">icon-logging-24</span>
									</li>
									<li>
									<i class="icon-aim"></i>
									<span class="i-name">icon-aim</span>
									</li>
									<li>
									<i class="icon-aim-alt"></i>
									<span class="i-name">icon-aim-alt</span>
									</li>
									<li>
									<i class="icon-amazon"></i>
									<span class="i-name">icon-amazon</span>
									</li>
									<li>
									<i class="icon-app-store"></i>
									<span class="i-name">icon-app-store</span>
									</li>
									<li>
									<i class="icon-apple-1"></i>
									<span class="i-name">icon-apple-1</span>
									</li>
									<li>
									<i class="icon-arto"></i>
									<span class="i-name">icon-arto</span>
									</li>
									<li>
									<i class="icon-aws"></i>
									<span class="i-name">icon-aws</span>
									</li>
									<li>
									<i class="icon-baidu"></i>
									<span class="i-name">icon-baidu</span>
									</li>
									<li>
									<i class="icon-basecamp"></i>
									<span class="i-name">icon-basecamp</span>
									</li>
									<li>
									<i class="icon-bebo"></i>
									<span class="i-name">icon-bebo</span>
									</li>
									<li>
									<i class="icon-behance-1"></i>
									<span class="i-name">icon-behance-1</span>
									</li>
									<li>
									<i class="icon-bing"></i>
									<span class="i-name">icon-bing</span>
									</li>
									<li>
									<i class="icon-blip"></i>
									<span class="i-name">icon-blip</span>
									</li>
									<li>
									<i class="icon-blogger"></i>
									<span class="i-name">icon-blogger</span>
									</li>
									<li>
									<i class="icon-bnter"></i>
									<span class="i-name">icon-bnter</span>
									</li>
									<li>
									<i class="icon-brightkite"></i>
									<span class="i-name">icon-brightkite</span>
									</li>
									<li>
									<i class="icon-cinch"></i>
									<span class="i-name">icon-cinch</span>
									</li>
									<li>
									<i class="icon-cloudapp"></i>
									<span class="i-name">icon-cloudapp</span>
									</li>
									<li>
									<i class="icon-coroflot"></i>
									<span class="i-name">icon-coroflot</span>
									</li>
									<li>
									<i class="icon-creative-commons"></i>
									<span class="i-name">icon-creative-commons</span>
									</li>
									<li>
									<i class="icon-dailybooth"></i>
									<span class="i-name">icon-dailybooth</span>
									</li>
									<li>
									<i class="icon-delicious-1"></i>
									<span class="i-name">icon-delicious-1</span>
									</li>
									<li>
									<i class="icon-designbump"></i>
									<span class="i-name">icon-designbump</span>
									</li>
									<li>
									<i class="icon-designfloat"></i>
									<span class="i-name">icon-designfloat</span>
									</li>
									<li>
									<i class="icon-designmoo"></i>
									<span class="i-name">icon-designmoo</span>
									</li>
									<li>
									<i class="icon-deviantart"></i>
									<span class="i-name">icon-deviantart</span>
									</li>
									<li>
									<i class="icon-digg"></i>
									<span class="i-name">icon-digg</span>
									</li>
									<li>
									<i class="icon-digg-alt"></i>
									<span class="i-name">icon-digg-alt</span>
									</li>
									<li>
									<i class="icon-diigo"></i>
									<span class="i-name">icon-diigo</span>
									</li>
									<li>
									<i class="icon-dribbble-2"></i>
									<span class="i-name">icon-dribbble-2</span>
									</li>
									<li>
									<i class="icon-dropbox-1"></i>
									<span class="i-name">icon-dropbox-1</span>
									</li>
									<li>
									<i class="icon-drupal"></i>
									<span class="i-name">icon-drupal</span>
									</li>
									<li>
									<i class="icon-dzone"></i>
									<span class="i-name">icon-dzone</span>
									</li>
									<li>
									<i class="icon-ebay"></i>
									<span class="i-name">icon-ebay</span>
									</li>
									<li>
									<i class="icon-ember"></i>
									<span class="i-name">icon-ember</span>
									</li>
									<li>
									<i class="icon-etsy"></i>
									<span class="i-name">icon-etsy</span>
									</li>
									<li>
									<i class="icon-evernote"></i>
									<span class="i-name">icon-evernote</span>
									</li>
									<li>
									<i class="icon-facebook-1"></i>
									<span class="i-name">icon-facebook-1</span>
									</li>
									<li>
									<i class="icon-facebook-places"></i>
									<span class="i-name">icon-facebook-places</span>
									</li>
									<li>
									<i class="icon-facto"></i>
									<span class="i-name">icon-facto</span>
									</li>
									<li>
									<i class="icon-feedburner"></i>
									<span class="i-name">icon-feedburner</span>
									</li>
									<li>
									<i class="icon-flickr"></i>
									<span class="i-name">icon-flickr</span>
									</li>
									<li>
									<i class="icon-folkd"></i>
									<span class="i-name">icon-folkd</span>
									</li>
									<li>
									<i class="icon-formspring"></i>
									<span class="i-name">icon-formspring</span>
									</li>
									<li>
									<i class="icon-forrst"></i>
									<span class="i-name">icon-forrst</span>
									</li>
									<li>
									<i class="icon-foursquare"></i>
									<span class="i-name">icon-foursquare</span>
									</li>
									<li>
									<i class="icon-friendfeed"></i>
									<span class="i-name">icon-friendfeed</span>
									</li>
									<li>
									<i class="icon-friendster"></i>
									<span class="i-name">icon-friendster</span>
									</li>
									<li>
									<i class="icon-gdgt"></i>
									<span class="i-name">icon-gdgt</span>
									</li>
									<li>
									<i class="icon-github"></i>
									<span class="i-name">icon-github</span>
									</li>
									<li>
									<i class="icon-github-alt"></i>
									<span class="i-name">icon-github-alt</span>
									</li>
									<li>
									<i class="icon-goodreads"></i>
									<span class="i-name">icon-goodreads</span>
									</li>
									<li>
									<i class="icon-google"></i>
									<span class="i-name">icon-google</span>
									</li>
									<li>
									<i class="icon-google-buzz"></i>
									<span class="i-name">icon-google-buzz</span>
									</li>
									<li>
									<i class="icon-google-talk"></i>
									<span class="i-name">icon-google-talk</span>
									</li>
									<li>
									<i class="icon-gowalla"></i>
									<span class="i-name">icon-gowalla</span>
									</li>
									<li>
									<i class="icon-gowalla-alt"></i>
									<span class="i-name">icon-gowalla-alt</span>
									</li>
									<li>
									<i class="icon-grooveshark"></i>
									<span class="i-name">icon-grooveshark</span>
									</li>
									<li>
									<i class="icon-hacker-news"></i>
									<span class="i-name">icon-hacker-news</span>
									</li>
									<li>
									<i class="icon-hi5"></i>
									<span class="i-name">icon-hi5</span>
									</li>
									<li>
									<i class="icon-hype-machine"></i>
									<span class="i-name">icon-hype-machine</span>
									</li>
									<li>
									<i class="icon-hyves"></i>
									<span class="i-name">icon-hyves</span>
									</li>
									<li>
									<i class="icon-icq"></i>
									<span class="i-name">icon-icq</span>
									</li>
									<li>
									<i class="icon-identi"></i>
									<span class="i-name">icon-identi</span>
									</li>
									<li>
									<i class="icon-instapaper"></i>
									<span class="i-name">icon-instapaper</span>
									</li>
									<li>
									<i class="icon-itunes"></i>
									<span class="i-name">icon-itunes</span>
									</li>
									<li>
									<i class="icon-kik"></i>
									<span class="i-name">icon-kik</span>
									</li>
									<li>
									<i class="icon-krop"></i>
									<span class="i-name">icon-krop</span>
									</li>
									<li>
									<i class="icon-last-1"></i>
									<span class="i-name">icon-last-1</span>
									</li>
									<li>
									<i class="icon-linkedin"></i>
									<span class="i-name">icon-linkedin</span>
									</li>
									<li>
									<i class="icon-linkedin-alt-1"></i>
									<span class="i-name">icon-linkedin-alt-1</span>
									</li>
									<li>
									<i class="icon-livejournal"></i>
									<span class="i-name">icon-livejournal</span>
									</li>
									<li>
									<i class="icon-lovedsgn"></i>
									<span class="i-name">icon-lovedsgn</span>
									</li>
									<li>
									<i class="icon-meetup"></i>
									<span class="i-name">icon-meetup</span>
									</li>
									<li>
									<i class="icon-metacafe"></i>
									<span class="i-name">icon-metacafe</span>
									</li>
									<li>
									<i class="icon-ming"></i>
									<span class="i-name">icon-ming</span>
									</li>
									<li>
									<i class="icon-mister-wong"></i>
									<span class="i-name">icon-mister-wong</span>
									</li>
									<li>
									<i class="icon-mixx"></i>
									<span class="i-name">icon-mixx</span>
									</li>
									<li>
									<i class="icon-mixx-alt"></i>
									<span class="i-name">icon-mixx-alt</span>
									</li>
									<li>
									<i class="icon-mobileme"></i>
									<span class="i-name">icon-mobileme</span>
									</li>
									<li>
									<i class="icon-msn-messenger"></i>
									<span class="i-name">icon-msn-messenger</span>
									</li>
									<li>
									<i class="icon-myspace"></i>
									<span class="i-name">icon-myspace</span>
									</li>
									<li>
									<i class="icon-myspace-alt"></i>
									<span class="i-name">icon-myspace-alt</span>
									</li>
									<li>
									<i class="icon-newsvine"></i>
									<span class="i-name">icon-newsvine</span>
									</li>
									<li>
									<i class="icon-official"></i>
									<span class="i-name">icon-official</span>
									</li>
									<li>
									<i class="icon-openid"></i>
									<span class="i-name">icon-openid</span>
									</li>
									<li>
									<i class="icon-orkut"></i>
									<span class="i-name">icon-orkut</span>
									</li>
									<li>
									<i class="icon-pandora"></i>
									<span class="i-name">icon-pandora</span>
									</li>
									<li>
									<i class="icon-path"></i>
									<span class="i-name">icon-path</span>
									</li>
									<li>
									<i class="icon-paypal-1"></i>
									<span class="i-name">icon-paypal-1</span>
									</li>
									<li>
									<i class="icon-photobucket"></i>
									<span class="i-name">icon-photobucket</span>
									</li>
									<li>
									<i class="icon-picasa"></i>
									<span class="i-name">icon-picasa</span>
									</li>
									<li>
									<i class="icon-picassa"></i>
									<span class="i-name">icon-picassa</span>
									</li>
									<li>
									<i class="icon-pinboard"></i>
									<span class="i-name">icon-pinboard</span>
									</li>
									<li>
									<i class="icon-ping"></i>
									<span class="i-name">icon-ping</span>
									</li>
									<li>
									<i class="icon-pingchat"></i>
									<span class="i-name">icon-pingchat</span>
									</li>
									<li>
									<i class="icon-playstation"></i>
									<span class="i-name">icon-playstation</span>
									</li>
									<li>
									<i class="icon-plixi"></i>
									<span class="i-name">icon-plixi</span>
									</li>
									<li>
									<i class="icon-plurk"></i>
									<span class="i-name">icon-plurk</span>
									</li>
									<li>
									<i class="icon-podcast"></i>
									<span class="i-name">icon-podcast</span>
									</li>
									<li>
									<i class="icon-posterous"></i>
									<span class="i-name">icon-posterous</span>
									</li>
									<li>
									<i class="icon-qik"></i>
									<span class="i-name">icon-qik</span>
									</li>
									<li>
									<i class="icon-quik"></i>
									<span class="i-name">icon-quik</span>
									</li>
									<li>
									<i class="icon-quora"></i>
									<span class="i-name">icon-quora</span>
									</li>
									<li>
									<i class="icon-rdio"></i>
									<span class="i-name">icon-rdio</span>
									</li>
									<li>
									<i class="icon-readernaut"></i>
									<span class="i-name">icon-readernaut</span>
									</li>
									<li>
									<i class="icon-reddit"></i>
									<span class="i-name">icon-reddit</span>
									</li>
									<li>
									<i class="icon-retweet"></i>
									<span class="i-name">icon-retweet</span>
									</li>
									<li>
									<i class="icon-robo"></i>
									<span class="i-name">icon-robo</span>
									</li>
									<li>
									<i class="icon-rss-1"></i>
									<span class="i-name">icon-rss-1</span>
									</li>
									<li>
									<i class="icon-scribd"></i>
									<span class="i-name">icon-scribd</span>
									</li>
									<li>
									<i class="icon-sharethis"></i>
									<span class="i-name">icon-sharethis</span>
									</li>
									<li>
									<i class="icon-simplenote"></i>
									<span class="i-name">icon-simplenote</span>
									</li>
									<li>
									<i class="icon-skype-1"></i>
									<span class="i-name">icon-skype-1</span>
									</li>
									<li>
									<i class="icon-slashdot"></i>
									<span class="i-name">icon-slashdot</span>
									</li>
									<li>
									<i class="icon-slideshare"></i>
									<span class="i-name">icon-slideshare</span>
									</li>
									<li>
									<i class="icon-smugmug"></i>
									<span class="i-name">icon-smugmug</span>
									</li>
									<li>
									<i class="icon-soundcloud"></i>
									<span class="i-name">icon-soundcloud</span>
									</li>
									<li>
									<i class="icon-spotify"></i>
									<span class="i-name">icon-spotify</span>
									</li>
									<li>
									<i class="icon-squarespace"></i>
									<span class="i-name">icon-squarespace</span>
									</li>
									<li>
									<i class="icon-squidoo"></i>
									<span class="i-name">icon-squidoo</span>
									</li>
									<li>
									<i class="icon-steam"></i>
									<span class="i-name">icon-steam</span>
									</li>
									<li>
									<i class="icon-stumbleupon"></i>
									<span class="i-name">icon-stumbleupon</span>
									</li>
									<li>
									<i class="icon-technorati"></i>
									<span class="i-name">icon-technorati</span>
									</li>
									<li>
									<i class="icon-threewords"></i>
									<span class="i-name">icon-threewords</span>
									</li>
									<li>
									<i class="icon-tribe"></i>
									<span class="i-name">icon-tribe</span>
									</li>
									<li>
									<i class="icon-tripit"></i>
									<span class="i-name">icon-tripit</span>
									</li>
									<li>
									<i class="icon-tumblr"></i>
									<span class="i-name">icon-tumblr</span>
									</li>
									<li>
									<i class="icon-twitter-1"></i>
									<span class="i-name">icon-twitter-1</span>
									</li>
									<li>
									<i class="icon-twitter-alt-1"></i>
									<span class="i-name">icon-twitter-alt-1</span>
									</li>
									<li>
									<i class="icon-vcard"></i>
									<span class="i-name">icon-vcard</span>
									</li>
									<li>
									<i class="icon-viddler"></i>
									<span class="i-name">icon-viddler</span>
									</li>
									<li>
									<i class="icon-vimeo"></i>
									<span class="i-name">icon-vimeo</span>
									</li>
									<li>
									<i class="icon-virb"></i>
									<span class="i-name">icon-virb</span>
									</li>
									<li>
									<i class="icon-w3"></i>
									<span class="i-name">icon-w3</span>
									</li>
									<li>
									<i class="icon-whatsapp"></i>
									<span class="i-name">icon-whatsapp</span>
									</li>
									<li>
									<i class="icon-wikipedia"></i>
									<span class="i-name">icon-wikipedia</span>
									</li>
									<li>
									<i class="icon-windows-1"></i>
									<span class="i-name">icon-windows-1</span>
									</li>
									<li>
									<i class="icon-wists"></i>
									<span class="i-name">icon-wists</span>
									</li>
									<li>
									<i class="icon-wordpress"></i>
									<span class="i-name">icon-wordpress</span>
									</li>
									<li>
									<i class="icon-wordpress-alt"></i>
									<span class="i-name">icon-wordpress-alt</span>
									</li>
									<li>
									<i class="icon-xing"></i>
									<span class="i-name">icon-xing</span>
									</li>
									<li>
									<i class="icon-yahoo"></i>
									<span class="i-name">icon-yahoo</span>
									</li>
									<li>
									<i class="icon-yahoo-buzz"></i>
									<span class="i-name">icon-yahoo-buzz</span>
									</li>
									<li>
									<i class="icon-yahoo-messenger"></i>
									<span class="i-name">icon-yahoo-messenger</span>
									</li>
									<li>
									<i class="icon-yelp"></i>
									<span class="i-name">icon-yelp</span>
									</li>
									<li>
									<i class="icon-zerply"></i>
									<span class="i-name">icon-zerply</span>
									</li>
									<li>
									<i class="icon-zootool"></i>
									<span class="i-name">icon-zootool</span>
									</li>
									<li>
									<i class="icon-zynga"></i>
									<span class="i-name">icon-zynga</span>
									</li>
									<li>
									<i class="icon-align-center"></i>
									<span class="i-name">icon-align-center</span>
									</li>
									<li>
									<i class="icon-align-justify"></i>
									<span class="i-name">icon-align-justify</span>
									</li>
									<li>
									<i class="icon-align-left"></i>
									<span class="i-name">icon-align-left</span>
									</li>
									<li>
									<i class="icon-align-right"></i>
									<span class="i-name">icon-align-right</span>
									</li>
									<li>
									<i class="icon-archive-1"></i>
									<span class="i-name">icon-archive-1</span>
									</li>
									<li>
									<i class="icon-atom"></i>
									<span class="i-name">icon-atom</span>
									</li>
									<li>
									<i class="icon-bag"></i>
									<span class="i-name">icon-bag</span>
									</li>
									<li>
									<i class="icon-bank-notes"></i>
									<span class="i-name">icon-bank-notes</span>
									</li>
									<li>
									<i class="icon-barbell"></i>
									<span class="i-name">icon-barbell</span>
									</li>
									<li>
									<i class="icon-bars-1"></i>
									<span class="i-name">icon-bars-1</span>
									</li>
									<li>
									<i class="icon-battery-0"></i>
									<span class="i-name">icon-battery-0</span>
									</li>
									<li>
									<i class="icon-battery-1"></i>
									<span class="i-name">icon-battery-1</span>
									</li>
									<li>
									<i class="icon-battery-2"></i>
									<span class="i-name">icon-battery-2</span>
									</li>
									<li>
									<i class="icon-battery-3"></i>
									<span class="i-name">icon-battery-3</span>
									</li>
									<li>
									<i class="icon-battery-4"></i>
									<span class="i-name">icon-battery-4</span>
									</li>
									<li>
									<i class="icon-battery-power"></i>
									<span class="i-name">icon-battery-power</span>
									</li>
									<li>
									<i class="icon-beer"></i>
									<span class="i-name">icon-beer</span>
									</li>
									<li>
									<i class="icon-bolt-1"></i>
									<span class="i-name">icon-bolt-1</span>
									</li>
									<li>
									<i class="icon-bones"></i>
									<span class="i-name">icon-bones</span>
									</li>
									<li>
									<i class="icon-book-close"></i>
									<span class="i-name">icon-book-close</span>
									</li>
									<li>
									<i class="icon-book-open"></i>
									<span class="i-name">icon-book-open</span>
									</li>
									<li>
									<i class="icon-bookmark"></i>
									<span class="i-name">icon-bookmark</span>
									</li>
									<li>
									<i class="icon-box-1"></i>
									<span class="i-name">icon-box-1</span>
									</li>
									<li>
									<i class="icon-browser-1"></i>
									<span class="i-name">icon-browser-1</span>
									</li>
									<li>
									<i class="icon-bubble"></i>
									<span class="i-name">icon-bubble</span>
									</li>
									<li>
									<i class="icon-bubble-1"></i>
									<span class="i-name">icon-bubble-1</span>
									</li>
									<li>
									<i class="icon-bubble-2"></i>
									<span class="i-name">icon-bubble-2</span>
									</li>
									<li>
									<i class="icon-bubble-3"></i>
									<span class="i-name">icon-bubble-3</span>
									</li>
									<li>
									<i class="icon-bucket"></i>
									<span class="i-name">icon-bucket</span>
									</li>
									<li>
									<i class="icon-calculator-1"></i>
									<span class="i-name">icon-calculator-1</span>
									</li>
									<li>
									<i class="icon-calendar-2"></i>
									<span class="i-name">icon-calendar-2</span>
									</li>
									<li>
									<i class="icon-camera-3"></i>
									<span class="i-name">icon-camera-3</span>
									</li>
									<li>
									<i class="icon-cardiac-pulse"></i>
									<span class="i-name">icon-cardiac-pulse</span>
									</li>
									<li>
									<i class="icon-cd-2"></i>
									<span class="i-name">icon-cd-2</span>
									</li>
									<li>
									<i class="icon-character"></i>
									<span class="i-name">icon-character</span>
									</li>
									<li>
									<i class="icon-clipboard-1"></i>
									<span class="i-name">icon-clipboard-1</span>
									</li>
									<li>
									<i class="icon-clock-2"></i>
									<span class="i-name">icon-clock-2</span>
									</li>
									<li>
									<i class="icon-cloud-3"></i>
									<span class="i-name">icon-cloud-3</span>
									</li>
									<li>
									<i class="icon-coffee"></i>
									<span class="i-name">icon-coffee</span>
									</li>
									<li>
									<i class="icon-comment"></i>
									<span class="i-name">icon-comment</span>
									</li>
									<li>
									<i class="icon-connection-0"></i>
									<span class="i-name">icon-connection-0</span>
									</li>
									<li>
									<i class="icon-connection-1"></i>
									<span class="i-name">icon-connection-1</span>
									</li>
									<li>
									<i class="icon-connection-2"></i>
									<span class="i-name">icon-connection-2</span>
									</li>
									<li>
									<i class="icon-connection-3"></i>
									<span class="i-name">icon-connection-3</span>
									</li>
									<li>
									<i class="icon-connection-4"></i>
									<span class="i-name">icon-connection-4</span>
									</li>
									<li>
									<i class="icon-credit-cards"></i>
									<span class="i-name">icon-credit-cards</span>
									</li>
									<li>
									<i class="icon-crop"></i>
									<span class="i-name">icon-crop</span>
									</li>
									<li>
									<i class="icon-cube"></i>
									<span class="i-name">icon-cube</span>
									</li>
									<li>
									<i class="icon-diamond"></i>
									<span class="i-name">icon-diamond</span>
									</li>
									<li>
									<i class="icon-email"></i>
									<span class="i-name">icon-email</span>
									</li>
									<li>
									<i class="icon-email-plane"></i>
									<span class="i-name">icon-email-plane</span>
									</li>
									<li>
									<i class="icon-enter"></i>
									<span class="i-name">icon-enter</span>
									</li>
									<li>
									<i class="icon-eyedropper-1"></i>
									<span class="i-name">icon-eyedropper-1</span>
									</li>
									<li>
									<i class="icon-file"></i>
									<span class="i-name">icon-file</span>
									</li>
									<li>
									<i class="icon-file-add"></i>
									<span class="i-name">icon-file-add</span>
									</li>
									<li>
									<i class="icon-file-broken"></i>
									<span class="i-name">icon-file-broken</span>
									</li>
									<li>
									<i class="icon-file-settings"></i>
									<span class="i-name">icon-file-settings</span>
									</li>
									<li>
									<i class="icon-files"></i>
									<span class="i-name">icon-files</span>
									</li>
									<li>
									<i class="icon-flag"></i>
									<span class="i-name">icon-flag</span>
									</li>
									<li>
									<i class="icon-folder"></i>
									<span class="i-name">icon-folder</span>
									</li>
									<li>
									<i class="icon-folder-add"></i>
									<span class="i-name">icon-folder-add</span>
									</li>
									<li>
									<i class="icon-folder-check"></i>
									<span class="i-name">icon-folder-check</span>
									</li>
									<li>
									<i class="icon-folder-settings"></i>
									<span class="i-name">icon-folder-settings</span>
									</li>
									<li>
									<i class="icon-forbidden"></i>
									<span class="i-name">icon-forbidden</span>
									</li>
									<li>
									<i class="icon-frames"></i>
									<span class="i-name">icon-frames</span>
									</li>
									<li>
									<i class="icon-glass"></i>
									<span class="i-name">icon-glass</span>
									</li>
									<li>
									<i class="icon-graph"></i>
									<span class="i-name">icon-graph</span>
									</li>
									<li>
									<i class="icon-grid-1"></i>
									<span class="i-name">icon-grid-1</span>
									</li>
									<li>
									<i class="icon-heart-1"></i>
									<span class="i-name">icon-heart-1</span>
									</li>
									<li>
									<i class="icon-home-2"></i>
									<span class="i-name">icon-home-2</span>
									</li>
									<li>
									<i class="icon-invoice"></i>
									<span class="i-name">icon-invoice</span>
									</li>
									<li>
									<i class="icon-ipad-1"></i>
									<span class="i-name">icon-ipad-1</span>
									</li>
									<li>
									<i class="icon-ipad-2"></i>
									<span class="i-name">icon-ipad-2</span>
									</li>
									<li>
									<i class="icon-lab"></i>
									<span class="i-name">icon-lab</span>
									</li>
									<li>
									<i class="icon-laptop"></i>
									<span class="i-name">icon-laptop</span>
									</li>
									<li>
									<i class="icon-list-2"></i>
									<span class="i-name">icon-list-2</span>
									</li>
									<li>
									<i class="icon-lock"></i>
									<span class="i-name">icon-lock</span>
									</li>
									<li>
									<i class="icon-locked"></i>
									<span class="i-name">icon-locked</span>
									</li>
									<li>
									<i class="icon-map"></i>
									<span class="i-name">icon-map</span>
									</li>
									<li>
									<i class="icon-measure"></i>
									<span class="i-name">icon-measure</span>
									</li>
									<li>
									<i class="icon-meter"></i>
									<span class="i-name">icon-meter</span>
									</li>
									<li>
									<i class="icon-micro"></i>
									<span class="i-name">icon-micro</span>
									</li>
									<li>
									<i class="icon-micro-mute"></i>
									<span class="i-name">icon-micro-mute</span>
									</li>
									<li>
									<i class="icon-microwave"></i>
									<span class="i-name">icon-microwave</span>
									</li>
									<li>
									<i class="icon-modem"></i>
									<span class="i-name">icon-modem</span>
									</li>
									<li>
									<i class="icon-mute"></i>
									<span class="i-name">icon-mute</span>
									</li>
									<li>
									<i class="icon-newspaper-1"></i>
									<span class="i-name">icon-newspaper-1</span>
									</li>
									<li>
									<i class="icon-paperclip-2"></i>
									<span class="i-name">icon-paperclip-2</span>
									</li>
									<li>
									<i class="icon-pencil-1"></i>
									<span class="i-name">icon-pencil-1</span>
									</li>
									<li>
									<i class="icon-phone"></i>
									<span class="i-name">icon-phone</span>
									</li>
									<li>
									<i class="icon-phone-2"></i>
									<span class="i-name">icon-phone-2</span>
									</li>
									<li>
									<i class="icon-phone-3"></i>
									<span class="i-name">icon-phone-3</span>
									</li>
									<li>
									<i class="icon-picture"></i>
									<span class="i-name">icon-picture</span>
									</li>
									<li>
									<i class="icon-pie-chart"></i>
									<span class="i-name">icon-pie-chart</span>
									</li>
									<li>
									<i class="icon-pill"></i>
									<span class="i-name">icon-pill</span>
									</li>
									<li>
									<i class="icon-pin-2"></i>
									<span class="i-name">icon-pin-2</span>
									</li>
									<li>
									<i class="icon-power"></i>
									<span class="i-name">icon-power</span>
									</li>
									<li>
									<i class="icon-printer-1"></i>
									<span class="i-name">icon-printer-1</span>
									</li>
									<li>
									<i class="icon-printer-2"></i>
									<span class="i-name">icon-printer-2</span>
									</li>
									<li>
									<i class="icon-refresh"></i>
									<span class="i-name">icon-refresh</span>
									</li>
									<li>
									<i class="icon-reload-1"></i>
									<span class="i-name">icon-reload-1</span>
									</li>
									<li>
									<i class="icon-screen-1"></i>
									<span class="i-name">icon-screen-1</span>
									</li>
									<li>
									<i class="icon-select"></i>
									<span class="i-name">icon-select</span>
									</li>
									<li>
									<i class="icon-set"></i>
									<span class="i-name">icon-set</span>
									</li>
									<li>
									<i class="icon-settings-1"></i>
									<span class="i-name">icon-settings-1</span>
									</li>
									<li>
									<i class="icon-shorts"></i>
									<span class="i-name">icon-shorts</span>
									</li>
									<li>
									<i class="icon-speaker"></i>
									<span class="i-name">icon-speaker</span>
									</li>
									<li>
									<i class="icon-star-1"></i>
									<span class="i-name">icon-star-1</span>
									</li>
									<li>
									<i class="icon-stopwatch"></i>
									<span class="i-name">icon-stopwatch</span>
									</li>
									<li>
									<i class="icon-sun"></i>
									<span class="i-name">icon-sun</span>
									</li>
									<li>
									<i class="icon-syringe"></i>
									<span class="i-name">icon-syringe</span>
									</li>
									<li>
									<i class="icon-tag"></i>
									<span class="i-name">icon-tag</span>
									</li>
									<li>
									<i class="icon-train-1"></i>
									<span class="i-name">icon-train-1</span>
									</li>
									<li>
									<i class="icon-trash-1"></i>
									<span class="i-name">icon-trash-1</span>
									</li>
									<li>
									<i class="icon-unlocked"></i>
									<span class="i-name">icon-unlocked</span>
									</li>
									<li>
									<i class="icon-volume-1"></i>
									<span class="i-name">icon-volume-1</span>
									</li>
									<li>
									<i class="icon-volume-down"></i>
									<span class="i-name">icon-volume-down</span>
									</li>
									<li>
									<i class="icon-volume-up"></i>
									<span class="i-name">icon-volume-up</span>
									</li>
									<li>
									<i class="icon-wifi-1"></i>
									<span class="i-name">icon-wifi-1</span>
									</li>
									<li>
									<i class="icon-wifi-2"></i>
									<span class="i-name">icon-wifi-2</span>
									</li>
									<li>
									<i class="icon-wifi-3"></i>
									<span class="i-name">icon-wifi-3</span>
									</li>
									<li>
									<i class="icon-window-delete"></i>
									<span class="i-name">icon-window-delete</span>
									</li>
									<li>
									<i class="icon-windows-2"></i>
									<span class="i-name">icon-windows-2</span>
									</li>
									<li>
									<i class="icon-zoom-in-1"></i>
									<span class="i-name">icon-zoom-in-1</span>
									</li>
									<li>
									<i class="icon-magnifying-glass-1"></i>
									<span class="i-name">icon-magnifying-glass-1</span>
									</li>
									<li>
									<i class="icon-search-2"></i>
									<span class="i-name">icon-search-2</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-2"></i>
									<span class="i-name">icon-fontawesome-webfont-2</span>
									</li>
									<li>
									<i class="icon-envelope-1"></i>
									<span class="i-name">icon-envelope-1</span>
									</li>
									<li>
									<i class="icon-download-3"></i>
									<span class="i-name">icon-download-3</span>
									</li>
									<li>
									<i class="icon-upload-3"></i>
									<span class="i-name">icon-upload-3</span>
									</li>
									<li>
									<i class="icon-stumbleupon-1"></i>
									<span class="i-name">icon-stumbleupon-1</span>
									</li>
									<li>
									<i class="icon-user"></i>
									<span class="i-name">icon-user</span>
									</li>
									<li>
									<i class="icon-users"></i>
									<span class="i-name">icon-users</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-3"></i>
									<span class="i-name">icon-fontawesome-webfont-3</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-4"></i>
									<span class="i-name">icon-fontawesome-webfont-4</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-5"></i>
									<span class="i-name">icon-fontawesome-webfont-5</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-6"></i>
									<span class="i-name">icon-fontawesome-webfont-6</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-7"></i>
									<span class="i-name">icon-fontawesome-webfont-7</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-8"></i>
									<span class="i-name">icon-fontawesome-webfont-8</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-9"></i>
									<span class="i-name">icon-fontawesome-webfont-9</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-10"></i>
									<span class="i-name">icon-fontawesome-webfont-10</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-11"></i>
									<span class="i-name">icon-fontawesome-webfont-11</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-12"></i>
									<span class="i-name">icon-fontawesome-webfont-12</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-13"></i>
									<span class="i-name">icon-fontawesome-webfont-13</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-14"></i>
									<span class="i-name">icon-fontawesome-webfont-14</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-15"></i>
									<span class="i-name">icon-fontawesome-webfont-15</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-16"></i>
									<span class="i-name">icon-fontawesome-webfont-16</span>
									</li>
									<li>
									<i class="icon-bookmark-1"></i>
									<span class="i-name">icon-bookmark-1</span>
									</li>
									<li>
									<i class="icon-book-open-1"></i>
									<span class="i-name">icon-book-open-1</span>
									</li>
									<li>
									<i class="icon-flash"></i>
									<span class="i-name">icon-flash</span>
									</li>
									<li>
									<i class="icon-feather-1"></i>
									<span class="i-name">icon-feather-1</span>
									</li>
									<li>
									<i class="icon-flag-1"></i>
									<span class="i-name">icon-flag-1</span>
									</li>
									<li>
									<i class="icon-google-circles"></i>
									<span class="i-name">icon-google-circles</span>
									</li>
									<li>
									<i class="icon-heart-2"></i>
									<span class="i-name">icon-heart-2</span>
									</li>
									<li>
									<i class="icon-heart-empty"></i>
									<span class="i-name">icon-heart-empty</span>
									</li>
									<li>
									<i class="icon-right-open-big"></i>
									<span class="i-name">icon-right-open-big</span>
									</li>
									<li>
									<i class="icon-left-open-big"></i>
									<span class="i-name">icon-left-open-big</span>
									</li>
									<li>
									<i class="icon-up-open-big"></i>
									<span class="i-name">icon-up-open-big</span>
									</li>
									<li>
									<i class="icon-down-open-big"></i>
									<span class="i-name">icon-down-open-big</span>
									</li>
									<li>
									<i class="icon-symbol-woman"></i>
									<span class="i-name">icon-symbol-woman</span>
									</li>
									<li>
									<i class="icon-measure-1"></i>
									<span class="i-name">icon-measure-1</span>
									</li>
									<li>
									<i class="icon-symbol-mixed"></i>
									<span class="i-name">icon-symbol-mixed</span>
									</li>
									<li>
									<i class="icon-letter"></i>
									<span class="i-name">icon-letter</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-17"></i>
									<span class="i-name">icon-fontawesome-webfont-17</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-18"></i>
									<span class="i-name">icon-fontawesome-webfont-18</span>
									</li>
									<li>
									<i class="icon-fontawesome-webfont-19"></i>
									<span class="i-name">icon-fontawesome-webfont-19</span>
									</li>
									<li>
									<i class="icon-arrow-1-down"></i>
									<span class="i-name">icon-arrow-1-down</span>
									</li>
									<li>
									<i class="icon-arrow-up-1"></i>
									<span class="i-name">icon-arrow-up-1</span>
									</li>
									<li>
									<i class="icon-fork"></i>
									<span class="i-name">icon-fork</span>
									</li>
									<li>
									<i class="icon-curved-arrow"></i>
									<span class="i-name">icon-curved-arrow</span>
									</li>
									<li>
									<i class="icon-forward-2"></i>
									<span class="i-name">icon-forward-2</span>
									</li>
									<li>
									<i class="icon-reload-2"></i>
									<span class="i-name">icon-reload-2</span>
									</li>
									<li>
									<i class="icon-arrows-out"></i>
									<span class="i-name">icon-arrows-out</span>
									</li>
									<li>
									<i class="icon-arrows-expand"></i>
									<span class="i-name">icon-arrows-expand</span>
									</li>
									<li>
									<i class="icon-arrows-compress"></i>
									<span class="i-name">icon-arrows-compress</span>
									</li>
									<li>
									<i class="icon-arrows-in"></i>
									<span class="i-name">icon-arrows-in</span>
									</li>
									<li>
									<i class="icon-zoom-out"></i>
									<span class="i-name">icon-zoom-out</span>
									</li>
									<li>
									<i class="icon-coverflow"></i>
									<span class="i-name">icon-coverflow</span>
									</li>
									<li>
									<i class="icon-coverflow-line"></i>
									<span class="i-name">icon-coverflow-line</span>
									</li>
            					</ul>';
                                                  
						$coutput .= '<input type="hidden" class="capture-input vibe-form-text vibe-cinput" name="' . $cpkey . '" id="' . $cpkey . '" value="' . $cparam['std'] . '" />' . "\n";
						$coutput .= $crow_end;	
							// append
						$this->append_output( $coutput );
                        break;     
                                              
					}
				}
				
				// popup parent form row end
				$prow_end    = '</ul>' . "\n";		// end .child-clone-row-form
				$prow_end   .= '<a href="#" class="child-clone-row-remove">Remove</a>' . "\n";
				$prow_end   .= '</div>' . "\n";		// end .child-clone-row
				
				
				$prow_end   .= '</div>' . "\n";		// end .child-clone-rows
				$prow_end   .= '</td>' . "\n";
				$prow_end   .= '</tr>' . "\n";					
				$prow_end   .= '</tbody>' . "\n";
				
				// add $prow_end to output
				$this->append_output( $prow_end );
			}			
		}
	}
	
	// --------------------------------------------------------------------------
	
	function append_output( $output ){
		$this->output = $this->output . "\n" . $output;		
	}
	
	// --------------------------------------------------------------------------
	
	function reset_output( $output ){
		$this->output = '';
	}
	
	// --------------------------------------------------------------------------
	
	function append_error( $error ){
		$this->errors = $this->errors . "\n" . $error;
	}
}

?>