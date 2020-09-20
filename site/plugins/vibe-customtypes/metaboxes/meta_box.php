<?php


 if ( ! defined( 'ABSPATH' ) ) exit;
 
function meta_box_find_field_type( $needle, $haystack ) {
    foreach ( $haystack as $item )
        if ( $item['type'] == $needle )
            return true;
    return false;
}

class custom_add_meta_box {
	
	var $id; // string meta box id
	var $title; // string title
	var $fields; // array fields
	var $page; // string|array post type to add meta box to
	var $js; // bool including javascript or not
	
    public function __construct( $id, $title, $fields, $page, $js ) {
		$this->id = $id;
		$this->title = $title;
		$this->fields = $fields;
		$this->page = $page;
		$this->js = $js;
		
		if(!is_array($this->page)) {
			$this->page = array($this->page);
		}
		
        add_action( 'admin_print_scripts-post-new.php',  array( $this, 'admin_enqueue_scripts' ),100 ); 
        add_action( 'admin_print_scripts-post.php',  array( $this, 'admin_enqueue_scripts' ),100);

		add_action( 'admin_enqueue_scripts', array( $this, 'persistent_admin_scripts' ),10,1);
		add_action( 'admin_head',  array( $this, 'admin_head' ) );
		add_action( 'admin_menu', array( $this, 'add_box' ) );
		add_action( 'save_post',  array( $this, 'save_box' ),999);
    }
	
	function admin_enqueue_scripts() {

		$post_type = get_post_type();

		$vc_post_types = apply_filters('vibe_custom_types_enqueue_scripts',array('course','quiz','unit','news','wplms-assignment','wplms-event','question','post','testimonial','popup','certificate','dwqa-question','forum','download'));

		if(in_array($post_type,$vc_post_types)){

	        wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery', 'jquery-ui-core' ) );
			wp_enqueue_script( 'jquery-ui-slider', array( 'jquery', 'jquery-ui-core' ) );

			wp_enqueue_script( 'timepicker_box', VIBE_PLUGIN_URL . '/vibe-customtypes/metaboxes/js/jquery.timePicker.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'graph_box', VIBE_PLUGIN_URL . '/vibe-customtypes/metaboxes/js/jquery.flot.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'graph_resize_box', VIBE_PLUGIN_URL . '/vibe-customtypes/metaboxes/js/jquery.flot.resize.min.js', array( 'jquery' ) );
	        wp_register_style( 'jqueryui', VIBE_PLUGIN_URL . '/vibe-customtypes/metaboxes/css/jqueryui.css' );

	        wp_deregister_script('badgeos-select2');
	        wp_dequeue_script('badgeos-select2');
	        wp_deregister_script('select2');
	        wp_dequeue_script('select2');
	        wp_enqueue_style( 'select2', VIBE_PLUGIN_URL .'/vibe-customtypes/metaboxes/css/select2.min.css');
			wp_enqueue_script( 'select2', VIBE_PLUGIN_URL .'/vibe-customtypes/metaboxes/js/select2.min.js');
			
		}
	}
	
	function persistent_admin_scripts($hook){

		if(in_array($hook,array('lms_page_lms-settings','post-new.php','post.php','lms_page_lms-stats'))){
			wp_enqueue_script( 'meta_box_js', VIBE_PLUGIN_URL . '/vibe-customtypes/metaboxes/js/scripts.js', array( 'jquery','iris','jquery-ui-core','jquery-ui-sortable','jquery-ui-slider','jquery-ui-datepicker'),'2.6' );
			wp_enqueue_media();
			wp_enqueue_style( 'meta_box_css', VIBE_PLUGIN_URL . '/vibe-customtypes/metaboxes/css/meta_box.css',array(),'3.8');
		}
		if($hook == 'term.php'){
			wp_enqueue_media();
		}
	}
	
	// scripts
	function admin_head() {
		global $post, $post_type;
		$hook = get_current_screen();

		if (in_array($post_type, $this->page) && $this->js == true && $hook->base == 'post') { 

			echo '<script type="text/javascript">
						jQuery(function() {';
			if(is_array($this->fields))
			foreach ( $this->fields as $field ) {
				// date
				if( $field['type'] == 'date' )
					echo 'jQuery("#' . $field['id'] . '").datepicker({
							dateFormat: \'yy-mm-dd\'});';
				// slider
				if ( $field['type'] == 'slider' ) {
					$value = get_post_meta( $post->ID, $field['id'], true );
					if ( $value == '' ) $value = $field['min'];
					echo 'jQuery( "#' . $field['id'] . '-slider" ).slider({
								value: ' . $value . ',
								min: ' . $field['min'] . ',
								max: ' . $field['max'] . ',
								step: ' . $field['step'] . ',
								slide: function( event, ui ) {
									jQuery( "#' . $field['id'] . '" ).val( ui.value );
								}
							});';
				}
                    if ( $field['type'] == 'gmap' ) {

                    	if(function_exists('vibe_get_option')){
                    		$zoom=vibe_get_option('zoom_level');
                    		$contact_ll=vibe_get_option('contact_ll');
                    		$ll = explode(',',$contact_ll);
                    		if(isset($ll) && is_array($ll)){
                    			$lat=$ll[0];
                        		$lng=$ll[1];
                    		}
                    	}
                                $value = get_post_meta( $post->ID, $field['id'], true );	

								if(isset($value['latitude']) && $value['latitude'])
									$lat= $value['latitude'];
								else
								  (!(isset($lat) && $lat)?$lat = '+37.0625' :'');
								 
								if(isset($value['longitude']) && $value['longitude'])
									$lng= $value['longitude'];
								else	
									((!isset($lng) && $lng)?$lng = '-95.677068' :'');
                                
                                if(!isset($zoom))
									$zoom= 17;

                                    echo "if(typeof google != 'undefined'){
									var geocoder = new google.maps.Geocoder();

									function geocodePosition(pos) {
									  geocoder.geocode({
									    latLng: pos
									  }, function(responses) {
									    if (responses && responses.length > 0) {
									        var address = '',city = '', state = '', zip = '', country = '', formattedAddress = '';
									                     for (var i = 0; i < responses[0].address_components.length; i++) {
									                          var addr = responses[0].address_components[i];
									                          // check if this entry in address_components has a type of country
									                          if (addr.types[0] == 'country'){
									                              document.getElementById('country').value = addr.long_name;
									                              country = addr.long_name;
									                          }else if (addr.types[0] == 'postal_code'){       // Zip
									                              document.getElementById('pincode').value = addr.short_name;
									                              zip = addr.long_name;
									                          }else if (addr.types[0] == ['administrative_area_level_1']){       // State
									                              document.getElementById('state').value = addr.long_name;
									                              state = addr.long_name;
									                          }else if (addr.types[0] == ['locality']){       // City
									                              document.getElementById('city').value = addr.long_name;
									                              city = addr.long_name;
									                              }
									                      }
									                      
									      var staddr=responses[0].formatted_address;
									      staddr=staddr.replace(country,'');staddr=staddr.replace(zip,'');staddr=staddr.replace(city,'');staddr=staddr.replace(state,'');
									      staddr=staddr.replace(', ,','');
									      document.getElementById('staddress').value = staddr;                
									      updateMarkerAddress(responses[0].formatted_address);
									    } else {
									      updateMarkerAddress('Cannot determine address at this location.');
									    }
									  });
									}

									function updateMarkerStatus(str) { 
									}

									function updateMarkerPosition(latLng) {

									  document.getElementById('latitude').value = latLng.lat();
									  document.getElementById('longitude').value =  latLng.lng();

									}

									function updateMarkerAddress(str) {
									    if(str != null)
									    document.getElementById('address').innerHTML = str;
									}

									function initialize() {
									var lat;var lng;
									lat = $lat;
									lng = $lng;



									  var latLng = new google.maps.LatLng(lat,lng);
									  var map = new google.maps.Map(document.getElementById('mapCanvas'), {
									    zoom: ".$zoom.",
									    center: latLng,
									    mapTypeId: google.maps.MapTypeId.ROADMAP
									  });
									  var marker = new google.maps.Marker({
									    position: latLng,
									    title: 'Property',
									    map: map,
									    draggable: true
									  });
									  
									  // Update current position info.
									  updateMarkerPosition(latLng);
									  geocodePosition(latLng);
									  
									  // Add dragging event listeners.
									  google.maps.event.addListener(marker, 'dragstart', function() {
									    updateMarkerAddress('Dragging...');
									  });
									  
									  google.maps.event.addListener(marker, 'drag', function() {
									    updateMarkerStatus('Dragging...');
									    updateMarkerPosition(marker.getPosition());
									  });
									  
									  google.maps.event.addListener(marker, 'dragend', function() {
									    updateMarkerStatus('Drag ended');
									    geocodePosition(marker.getPosition());
									    
									  });
									}
									    
									// Onload handler to fire off the app.
									google.maps.event.addDomListener(window, 'load', initialize);
									}";
                                }
			}
			
			echo '});
				</script>';
		};
	}
	
	function add_box() {
		foreach ($this->page as $page) {
			add_meta_box( $this->id, $this->title, array( $this, 'meta_box_callback' ), $page, 'normal', 'high');
		}
	}
	
	function meta_box_callback() {
		global $post, $post_type;
		// Use nonce for verification
		echo '<input type="hidden" name="' . $post_type . '_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__) ) . '" />';
		
		// Begin the field table and loop
		echo '<table class="form-table meta_box">';
		if(is_array($this->fields))
		foreach ( $this->fields as $field) {
			
			// get data for this field
			extract( $field );
			if ( !empty( $desc ) )
				$desc = '<span class="description">' . $desc . '</span>';
				
			// get value of this field if it exists for this post

				$meta = get_post_meta( $post->ID, $id, true);
			
			if(function_exists('vibe_get_option')){
				$cache_duration = vibe_get_option('cache_duration');
				if(!isset($cache_duration) || !$cache_duration)
					$cache_duration = 0;
			}
			
			// begin a table row with
			echo '<tr>
					<th><label for="' . $id . '">' . $label . '</label></th>
					<td>';
					switch( $type ) {

                        case 'number':
                        	if($meta == '' || !isset($meta)){ $meta = $std; }
							echo '<input type="number" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $meta ) . '" size="20" />
									<br />' . $desc;
						break;
						// text
						case 'text':
							echo '<input type="text" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $meta ) . '" size="20" />
									<br />' . $desc;
						break;
						case 'duration':
							echo '<select name="' . $id . '" id="' . $id . '" class="select">';
                            if($meta == '' || !isset($meta)){$meta=$std;}
                            $options = array(
                            		array('value' =>'1','label'=>__('Seconds','vibe-customtypes')),
                            		array('value' =>'60','label'=>__('Minutes','vibe-customtypes')),
									array('value' =>'3600','label'=>__('Hours','vibe-customtypes')),
									array('value' =>'86400','label'=>__('Days','vibe-customtypes')),
									array('value' =>'604800','label'=>__('Weeks','vibe-customtypes')),
									array('value' =>'2592000','label'=>__('Months','vibe-customtypes')),
									array('value' =>'31536000','label'=>__('Years','
										vibe-customtypes'))
                            	);
							foreach ( $options as $option )
								echo '<option' . selected( esc_attr( $meta ), $option['value'], false ) . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
							echo '</select><br />' . $desc;
						
						break;
						// textarea
						case 'textarea':
							echo '<textarea name="' . $id . '" id="' . $id . '" cols="60" rows="4">' . esc_attr( $meta ) . '</textarea>
									<br />' . $desc;
						break;
						// editor
						case 'editor':
							wp_editor(  $meta, $id , array(
							        'wpautop' => true,
							        'media_buttons' => true,
							        'teeny' => true,
							        'textarea_rows' => '4',
							        'textarea_cols' => '30',
							        'tinymce' => array(
							                        'theme_advanced_buttons1' => 'save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,cut,copy,paste,pastetext,pasteword,',
							                        'theme_advanced_buttons2' => "styleselect,formatselect,fontselect,fontsizeselect,",
							                        'theme_advanced_buttons3' => ",bullist,numlist,|,outdent,indent,blockquote,|,link,anchor,image,|,insertdate,forecolor,backcolor,|,tablecontrols,|,hr,|,fullscreen",
							                        'theme_advanced_buttons4' => "",
							                        'theme_advanced_text_colors' => '0f3156,636466,0486d3',
							                    ),
							        'quicktags' => array(
							            'buttons' => 'b,i,ul,ol,li,link,close'
							        )
							    ) );
							echo '<br />' . $desc;
						break;
						case 'faicons':
							echo "<ul class='the-icons unstyled'><li><i class='fa fa-var-500px'></i><span class='i-name'>fa fa-var-500px</span></li><li><i class='fa fa-var-adjust'></i><span class='i-name'>fa fa-var-adjust</span></li><li><i class='fa fa-var-adn'></i><span class='i-name'>fa fa-var-adn</span></li><li><i class='fa fa-var-align-center'></i><span class='i-name'>fa fa-var-align-center</span></li><li><i class='fa fa-var-align-justify'></i><span class='i-name'>fa fa-var-align-justify</span></li><li><i class='fa fa-var-align-left'></i><span class='i-name'>fa fa-var-align-left</span></li><li><i class='fa fa-var-align-right'></i><span class='i-name'>fa fa-var-align-right</span></li><li><i class='fa fa-var-amazon'></i><span class='i-name'>fa fa-var-amazon</span></li><li><i class='fa fa-var-ambulance'></i><span class='i-name'>fa fa-var-ambulance</span></li><li><i class='fa fa-var-anchor'></i><span class='i-name'>fa fa-var-anchor</span></li><li><i class='fa fa-var-android'></i><span class='i-name'>fa fa-var-android</span></li><li><i class='fa fa-var-angellist'></i><span class='i-name'>fa fa-var-angellist</span></li><li><i class='fa fa-var-angle-double-down'></i><span class='i-name'>fa fa-var-angle-double-down</span></li><li><i class='fa fa-var-angle-double-left'></i><span class='i-name'>fa fa-var-angle-double-left</span></li><li><i class='fa fa-var-angle-double-right'></i><span class='i-name'>fa fa-var-angle-double-right</span></li><li><i class='fa fa-var-angle-double-up'></i><span class='i-name'>fa fa-var-angle-double-up</span></li><li><i class='fa fa-var-angle-down'></i><span class='i-name'>fa fa-var-angle-down</span></li><li><i class='fa fa-var-angle-left'></i><span class='i-name'>fa fa-var-angle-left</span></li><li><i class='fa fa-var-angle-right'></i><span class='i-name'>fa fa-var-angle-right</span></li><li><i class='fa fa-var-angle-up'></i><span class='i-name'>fa fa-var-angle-up</span></li><li><i class='fa fa-var-apple'></i><span class='i-name'>fa fa-var-apple</span></li><li><i class='fa fa-var-archive'></i><span class='i-name'>fa fa-var-archive</span></li><li><i class='fa fa-var-area-chart'></i><span class='i-name'>fa fa-var-area-chart</span></li><li><i class='fa fa-var-arrow-circle-down'></i><span class='i-name'>fa fa-var-arrow-circle-down</span></li><li><i class='fa fa-var-arrow-circle-left'></i><span class='i-name'>fa fa-var-arrow-circle-left</span></li><li><i class='fa fa-var-arrow-circle-o-down'></i><span class='i-name'>fa fa-var-arrow-circle-o-down</span></li><li><i class='fa fa-var-arrow-circle-o-left'></i><span class='i-name'>fa fa-var-arrow-circle-o-left</span></li><li><i class='fa fa-var-arrow-circle-o-right'></i><span class='i-name'>fa fa-var-arrow-circle-o-right</span></li><li><i class='fa fa-var-arrow-circle-o-up'></i><span class='i-name'>fa fa-var-arrow-circle-o-up</span></li><li><i class='fa fa-var-arrow-circle-right'></i><span class='i-name'>fa fa-var-arrow-circle-right</span></li><li><i class='fa fa-var-arrow-circle-up'></i><span class='i-name'>fa fa-var-arrow-circle-up</span></li><li><i class='fa fa-var-arrow-down'></i><span class='i-name'>fa fa-var-arrow-down</span></li><li><i class='fa fa-var-arrow-left'></i><span class='i-name'>fa fa-var-arrow-left</span></li><li><i class='fa fa-var-arrow-right'></i><span class='i-name'>fa fa-var-arrow-right</span></li><li><i class='fa fa-var-arrow-up'></i><span class='i-name'>fa fa-var-arrow-up</span></li><li><i class='fa fa-var-arrows'></i><span class='i-name'>fa fa-var-arrows</span></li><li><i class='fa fa-var-arrows-alt'></i><span class='i-name'>fa fa-var-arrows-alt</span></li><li><i class='fa fa-var-arrows-h'></i><span class='i-name'>fa fa-var-arrows-h</span></li><li><i class='fa fa-var-arrows-v'></i><span class='i-name'>fa fa-var-arrows-v</span></li><li><i class='fa fa-var-asterisk'></i><span class='i-name'>fa fa-var-asterisk</span></li><li><i class='fa fa-var-at'></i><span class='i-name'>fa fa-var-at</span></li><li><i class='fa fa-var-automobile'></i><span class='i-name'>fa fa-var-automobile</span></li><li><i class='fa fa-var-backward'></i><span class='i-name'>fa fa-var-backward</span></li><li><i class='fa fa-var-balance-scale'></i><span class='i-name'>fa fa-var-balance-scale</span></li><li><i class='fa fa-var-ban'></i><span class='i-name'>fa fa-var-ban</span></li><li><i class='fa fa-var-bank'></i><span class='i-name'>fa fa-var-bank</span></li><li><i class='fa fa-var-bar-chart'></i><span class='i-name'>fa fa-var-bar-chart</span></li><li><i class='fa fa-var-bar-chart-o'></i><span class='i-name'>fa fa-var-bar-chart-o</span></li><li><i class='fa fa-var-barcode'></i><span class='i-name'>fa fa-var-barcode</span></li><li><i class='fa fa-var-bars'></i><span class='i-name'>fa fa-var-bars</span></li><li><i class='fa fa-var-battery-0'></i><span class='i-name'>fa fa-var-battery-0</span></li><li><i class='fa fa-var-battery-1'></i><span class='i-name'>fa fa-var-battery-1</span></li><li><i class='fa fa-var-battery-2'></i><span class='i-name'>fa fa-var-battery-2</span></li><li><i class='fa fa-var-battery-3'></i><span class='i-name'>fa fa-var-battery-3</span></li><li><i class='fa fa-var-battery-4'></i><span class='i-name'>fa fa-var-battery-4</span></li><li><i class='fa fa-var-battery-empty'></i><span class='i-name'>fa fa-var-battery-empty</span></li><li><i class='fa fa-var-battery-full'></i><span class='i-name'>fa fa-var-battery-full</span></li><li><i class='fa fa-var-battery-half'></i><span class='i-name'>fa fa-var-battery-half</span></li><li><i class='fa fa-var-battery-quarter'></i><span class='i-name'>fa fa-var-battery-quarter</span></li><li><i class='fa fa-var-battery-three-quarters'></i><span class='i-name'>fa fa-var-battery-three-quarters</span></li><li><i class='fa fa-var-bed'></i><span class='i-name'>fa fa-var-bed</span></li><li><i class='fa fa-var-beer'></i><span class='i-name'>fa fa-var-beer</span></li><li><i class='fa fa-var-behance'></i><span class='i-name'>fa fa-var-behance</span></li><li><i class='fa fa-var-behance-square'></i><span class='i-name'>fa fa-var-behance-square</span></li><li><i class='fa fa-var-bell'></i><span class='i-name'>fa fa-var-bell</span></li><li><i class='fa fa-var-bell-o'></i><span class='i-name'>fa fa-var-bell-o</span></li><li><i class='fa fa-var-bell-slash'></i><span class='i-name'>fa fa-var-bell-slash</span></li><li><i class='fa fa-var-bell-slash-o'></i><span class='i-name'>fa fa-var-bell-slash-o</span></li><li><i class='fa fa-var-bicycle'></i><span class='i-name'>fa fa-var-bicycle</span></li><li><i class='fa fa-var-binoculars'></i><span class='i-name'>fa fa-var-binoculars</span></li><li><i class='fa fa-var-birthday-cake'></i><span class='i-name'>fa fa-var-birthday-cake</span></li><li><i class='fa fa-var-bitbucket'></i><span class='i-name'>fa fa-var-bitbucket</span></li><li><i class='fa fa-var-bitbucket-square'></i><span class='i-name'>fa fa-var-bitbucket-square</span></li><li><i class='fa fa-var-bitcoin'></i><span class='i-name'>fa fa-var-bitcoin</span></li><li><i class='fa fa-var-black-tie'></i><span class='i-name'>fa fa-var-black-tie</span></li><li><i class='fa fa-var-bluetooth'></i><span class='i-name'>fa fa-var-bluetooth</span></li><li><i class='fa fa-var-bluetooth-b'></i><span class='i-name'>fa fa-var-bluetooth-b</span></li><li><i class='fa fa-var-bold'></i><span class='i-name'>fa fa-var-bold</span></li><li><i class='fa fa-var-bolt'></i><span class='i-name'>fa fa-var-bolt</span></li><li><i class='fa fa-var-bomb'></i><span class='i-name'>fa fa-var-bomb</span></li><li><i class='fa fa-var-book'></i><span class='i-name'>fa fa-var-book</span></li><li><i class='fa fa-var-bookmark'></i><span class='i-name'>fa fa-var-bookmark</span></li><li><i class='fa fa-var-bookmark-o'></i><span class='i-name'>fa fa-var-bookmark-o</span></li><li><i class='fa fa-var-briefcase'></i><span class='i-name'>fa fa-var-briefcase</span></li><li><i class='fa fa-var-btc'></i><span class='i-name'>fa fa-var-btc</span></li><li><i class='fa fa-var-bug'></i><span class='i-name'>fa fa-var-bug</span></li><li><i class='fa fa-var-building'></i><span class='i-name'>fa fa-var-building</span></li><li><i class='fa fa-var-building-o'></i><span class='i-name'>fa fa-var-building-o</span></li><li><i class='fa fa-var-bullhorn'></i><span class='i-name'>fa fa-var-bullhorn</span></li><li><i class='fa fa-var-bullseye'></i><span class='i-name'>fa fa-var-bullseye</span></li><li><i class='fa fa-var-bus'></i><span class='i-name'>fa fa-var-bus</span></li><li><i class='fa fa-var-buysellads'></i><span class='i-name'>fa fa-var-buysellads</span></li><li><i class='fa fa-var-cab'></i><span class='i-name'>fa fa-var-cab</span></li><li><i class='fa fa-var-calculator'></i><span class='i-name'>fa fa-var-calculator</span></li><li><i class='fa fa-var-calendar'></i><span class='i-name'>fa fa-var-calendar</span></li><li><i class='fa fa-var-calendar-check-o'></i><span class='i-name'>fa fa-var-calendar-check-o</span></li><li><i class='fa fa-var-calendar-minus-o'></i><span class='i-name'>fa fa-var-calendar-minus-o</span></li><li><i class='fa fa-var-calendar-o'></i><span class='i-name'>fa fa-var-calendar-o</span></li><li><i class='fa fa-var-calendar-plus-o'></i><span class='i-name'>fa fa-var-calendar-plus-o</span></li><li><i class='fa fa-var-calendar-times-o'></i><span class='i-name'>fa fa-var-calendar-times-o</span></li><li><i class='fa fa-var-camera'></i><span class='i-name'>fa fa-var-camera</span></li><li><i class='fa fa-var-camera-retro'></i><span class='i-name'>fa fa-var-camera-retro</span></li><li><i class='fa fa-var-car'></i><span class='i-name'>fa fa-var-car</span></li><li><i class='fa fa-var-caret-down'></i><span class='i-name'>fa fa-var-caret-down</span></li><li><i class='fa fa-var-caret-left'></i><span class='i-name'>fa fa-var-caret-left</span></li><li><i class='fa fa-var-caret-right'></i><span class='i-name'>fa fa-var-caret-right</span></li><li><i class='fa fa-var-caret-square-o-down'></i><span class='i-name'>fa fa-var-caret-square-o-down</span></li><li><i class='fa fa-var-caret-square-o-left'></i><span class='i-name'>fa fa-var-caret-square-o-left</span></li><li><i class='fa fa-var-caret-square-o-right'></i><span class='i-name'>fa fa-var-caret-square-o-right</span></li><li><i class='fa fa-var-caret-square-o-up'></i><span class='i-name'>fa fa-var-caret-square-o-up</span></li><li><i class='fa fa-var-caret-up'></i><span class='i-name'>fa fa-var-caret-up</span></li><li><i class='fa fa-var-cart-arrow-down'></i><span class='i-name'>fa fa-var-cart-arrow-down</span></li><li><i class='fa fa-var-cart-plus'></i><span class='i-name'>fa fa-var-cart-plus</span></li><li><i class='fa fa-var-cc'></i><span class='i-name'>fa fa-var-cc</span></li><li><i class='fa fa-var-cc-amex'></i><span class='i-name'>fa fa-var-cc-amex</span></li><li><i class='fa fa-var-cc-diners-club'></i><span class='i-name'>fa fa-var-cc-diners-club</span></li><li><i class='fa fa-var-cc-discover'></i><span class='i-name'>fa fa-var-cc-discover</span></li><li><i class='fa fa-var-cc-jcb'></i><span class='i-name'>fa fa-var-cc-jcb</span></li><li><i class='fa fa-var-cc-mastercard'></i><span class='i-name'>fa fa-var-cc-mastercard</span></li><li><i class='fa fa-var-cc-paypal'></i><span class='i-name'>fa fa-var-cc-paypal</span></li><li><i class='fa fa-var-cc-stripe'></i><span class='i-name'>fa fa-var-cc-stripe</span></li><li><i class='fa fa-var-cc-visa'></i><span class='i-name'>fa fa-var-cc-visa</span></li><li><i class='fa fa-var-certificate'></i><span class='i-name'>fa fa-var-certificate</span></li><li><i class='fa fa-var-chain'></i><span class='i-name'>fa fa-var-chain</span></li><li><i class='fa fa-var-chain-broken'></i><span class='i-name'>fa fa-var-chain-broken</span></li><li><i class='fa fa-var-check'></i><span class='i-name'>fa fa-var-check</span></li><li><i class='fa fa-var-check-circle'></i><span class='i-name'>fa fa-var-check-circle</span></li><li><i class='fa fa-var-check-circle-o'></i><span class='i-name'>fa fa-var-check-circle-o</span></li><li><i class='fa fa-var-check-square'></i><span class='i-name'>fa fa-var-check-square</span></li><li><i class='fa fa-var-check-square-o'></i><span class='i-name'>fa fa-var-check-square-o</span></li><li><i class='fa fa-var-chevron-circle-down'></i><span class='i-name'>fa fa-var-chevron-circle-down</span></li><li><i class='fa fa-var-chevron-circle-left'></i><span class='i-name'>fa fa-var-chevron-circle-left</span></li><li><i class='fa fa-var-chevron-circle-right'></i><span class='i-name'>fa fa-var-chevron-circle-right</span></li><li><i class='fa fa-var-chevron-circle-up'></i><span class='i-name'>fa fa-var-chevron-circle-up</span></li><li><i class='fa fa-var-chevron-down'></i><span class='i-name'>fa fa-var-chevron-down</span></li><li><i class='fa fa-var-chevron-left'></i><span class='i-name'>fa fa-var-chevron-left</span></li><li><i class='fa fa-var-chevron-right'></i><span class='i-name'>fa fa-var-chevron-right</span></li><li><i class='fa fa-var-chevron-up'></i><span class='i-name'>fa fa-var-chevron-up</span></li><li><i class='fa fa-var-child'></i><span class='i-name'>fa fa-var-child</span></li><li><i class='fa fa-var-chrome'></i><span class='i-name'>fa fa-var-chrome</span></li><li><i class='fa fa-var-circle'></i><span class='i-name'>fa fa-var-circle</span></li><li><i class='fa fa-var-circle-o'></i><span class='i-name'>fa fa-var-circle-o</span></li><li><i class='fa fa-var-circle-o-notch'></i><span class='i-name'>fa fa-var-circle-o-notch</span></li><li><i class='fa fa-var-circle-thin'></i><span class='i-name'>fa fa-var-circle-thin</span></li><li><i class='fa fa-var-clipboard'></i><span class='i-name'>fa fa-var-clipboard</span></li><li><i class='fa fa-var-clock-o'></i><span class='i-name'>fa fa-var-clock-o</span></li><li><i class='fa fa-var-clone'></i><span class='i-name'>fa fa-var-clone</span></li><li><i class='fa fa-var-close'></i><span class='i-name'>fa fa-var-close</span></li><li><i class='fa fa-var-cloud'></i><span class='i-name'>fa fa-var-cloud</span></li><li><i class='fa fa-var-cloud-download'></i><span class='i-name'>fa fa-var-cloud-download</span></li><li><i class='fa fa-var-cloud-upload'></i><span class='i-name'>fa fa-var-cloud-upload</span></li><li><i class='fa fa-var-cny'></i><span class='i-name'>fa fa-var-cny</span></li><li><i class='fa fa-var-code'></i><span class='i-name'>fa fa-var-code</span></li><li><i class='fa fa-var-code-fork'></i><span class='i-name'>fa fa-var-code-fork</span></li><li><i class='fa fa-var-codepen'></i><span class='i-name'>fa fa-var-codepen</span></li><li><i class='fa fa-var-codiepie'></i><span class='i-name'>fa fa-var-codiepie</span></li><li><i class='fa fa-var-coffee'></i><span class='i-name'>fa fa-var-coffee</span></li><li><i class='fa fa-var-cog'></i><span class='i-name'>fa fa-var-cog</span></li><li><i class='fa fa-var-cogs'></i><span class='i-name'>fa fa-var-cogs</span></li><li><i class='fa fa-var-columns'></i><span class='i-name'>fa fa-var-columns</span></li><li><i class='fa fa-var-comment'></i><span class='i-name'>fa fa-var-comment</span></li><li><i class='fa fa-var-comment-o'></i><span class='i-name'>fa fa-var-comment-o</span></li><li><i class='fa fa-var-commenting'></i><span class='i-name'>fa fa-var-commenting</span></li><li><i class='fa fa-var-commenting-o'></i><span class='i-name'>fa fa-var-commenting-o</span></li><li><i class='fa fa-var-comments'></i><span class='i-name'>fa fa-var-comments</span></li><li><i class='fa fa-var-comments-o'></i><span class='i-name'>fa fa-var-comments-o</span></li><li><i class='fa fa-var-compass'></i><span class='i-name'>fa fa-var-compass</span></li><li><i class='fa fa-var-compress'></i><span class='i-name'>fa fa-var-compress</span></li><li><i class='fa fa-var-connectdevelop'></i><span class='i-name'>fa fa-var-connectdevelop</span></li><li><i class='fa fa-var-contao'></i><span class='i-name'>fa fa-var-contao</span></li><li><i class='fa fa-var-copy'></i><span class='i-name'>fa fa-var-copy</span></li><li><i class='fa fa-var-copyright'></i><span class='i-name'>fa fa-var-copyright</span></li><li><i class='fa fa-var-creative-commons'></i><span class='i-name'>fa fa-var-creative-commons</span></li><li><i class='fa fa-var-credit-card'></i><span class='i-name'>fa fa-var-credit-card</span></li><li><i class='fa fa-var-credit-card-alt'></i><span class='i-name'>fa fa-var-credit-card-alt</span></li><li><i class='fa fa-var-crop'></i><span class='i-name'>fa fa-var-crop</span></li><li><i class='fa fa-var-crosshairs'></i><span class='i-name'>fa fa-var-crosshairs</span></li><li><i class='fa fa-var-css3'></i><span class='i-name'>fa fa-var-css3</span></li><li><i class='fa fa-var-cube'></i><span class='i-name'>fa fa-var-cube</span></li><li><i class='fa fa-var-cubes'></i><span class='i-name'>fa fa-var-cubes</span></li><li><i class='fa fa-var-cut'></i><span class='i-name'>fa fa-var-cut</span></li><li><i class='fa fa-var-cutlery'></i><span class='i-name'>fa fa-var-cutlery</span></li><li><i class='fa fa-var-dashboard'></i><span class='i-name'>fa fa-var-dashboard</span></li><li><i class='fa fa-var-dashcube'></i><span class='i-name'>fa fa-var-dashcube</span></li><li><i class='fa fa-var-database'></i><span class='i-name'>fa fa-var-database</span></li><li><i class='fa fa-var-dedent'></i><span class='i-name'>fa fa-var-dedent</span></li><li><i class='fa fa-var-delicious'></i><span class='i-name'>fa fa-var-delicious</span></li><li><i class='fa fa-var-desktop'></i><span class='i-name'>fa fa-var-desktop</span></li><li><i class='fa fa-var-deviantart'></i><span class='i-name'>fa fa-var-deviantart</span></li><li><i class='fa fa-var-diamond'></i><span class='i-name'>fa fa-var-diamond</span></li><li><i class='fa fa-var-digg'></i><span class='i-name'>fa fa-var-digg</span></li><li><i class='fa fa-var-dollar'></i><span class='i-name'>fa fa-var-dollar</span></li><li><i class='fa fa-var-dot-circle-o'></i><span class='i-name'>fa fa-var-dot-circle-o</span></li><li><i class='fa fa-var-download'></i><span class='i-name'>fa fa-var-download</span></li><li><i class='fa fa-var-dribbble'></i><span class='i-name'>fa fa-var-dribbble</span></li><li><i class='fa fa-var-dropbox'></i><span class='i-name'>fa fa-var-dropbox</span></li><li><i class='fa fa-var-drupal'></i><span class='i-name'>fa fa-var-drupal</span></li><li><i class='fa fa-var-edge'></i><span class='i-name'>fa fa-var-edge</span></li><li><i class='fa fa-var-edit'></i><span class='i-name'>fa fa-var-edit</span></li><li><i class='fa fa-var-eject'></i><span class='i-name'>fa fa-var-eject</span></li><li><i class='fa fa-var-ellipsis-h'></i><span class='i-name'>fa fa-var-ellipsis-h</span></li><li><i class='fa fa-var-ellipsis-v'></i><span class='i-name'>fa fa-var-ellipsis-v</span></li><li><i class='fa fa-var-empire'></i><span class='i-name'>fa fa-var-empire</span></li><li><i class='fa fa-var-envelope'></i><span class='i-name'>fa fa-var-envelope</span></li><li><i class='fa fa-var-envelope-o'></i><span class='i-name'>fa fa-var-envelope-o</span></li><li><i class='fa fa-var-envelope-square'></i><span class='i-name'>fa fa-var-envelope-square</span></li><li><i class='fa fa-var-eraser'></i><span class='i-name'>fa fa-var-eraser</span></li><li><i class='fa fa-var-eur'></i><span class='i-name'>fa fa-var-eur</span></li><li><i class='fa fa-var-euro'></i><span class='i-name'>fa fa-var-euro</span></li><li><i class='fa fa-var-exchange'></i><span class='i-name'>fa fa-var-exchange</span></li><li><i class='fa fa-var-exclamation'></i><span class='i-name'>fa fa-var-exclamation</span></li><li><i class='fa fa-var-exclamation-circle'></i><span class='i-name'>fa fa-var-exclamation-circle</span></li><li><i class='fa fa-var-exclamation-triangle'></i><span class='i-name'>fa fa-var-exclamation-triangle</span></li><li><i class='fa fa-var-expand'></i><span class='i-name'>fa fa-var-expand</span></li><li><i class='fa fa-var-expeditedssl'></i><span class='i-name'>fa fa-var-expeditedssl</span></li><li><i class='fa fa-var-external-link'></i><span class='i-name'>fa fa-var-external-link</span></li><li><i class='fa fa-var-external-link-square'></i><span class='i-name'>fa fa-var-external-link-square</span></li><li><i class='fa fa-var-eye'></i><span class='i-name'>fa fa-var-eye</span></li><li><i class='fa fa-var-eye-slash'></i><span class='i-name'>fa fa-var-eye-slash</span></li><li><i class='fa fa-var-eyedropper'></i><span class='i-name'>fa fa-var-eyedropper</span></li><li><i class='fa fa-var-facebook'></i><span class='i-name'>fa fa-var-facebook</span></li><li><i class='fa fa-var-facebook-f'></i><span class='i-name'>fa fa-var-facebook-f</span></li><li><i class='fa fa-var-facebook-official'></i><span class='i-name'>fa fa-var-facebook-official</span></li><li><i class='fa fa-var-facebook-square'></i><span class='i-name'>fa fa-var-facebook-square</span></li><li><i class='fa fa-var-fast-backward'></i><span class='i-name'>fa fa-var-fast-backward</span></li><li><i class='fa fa-var-fast-forward'></i><span class='i-name'>fa fa-var-fast-forward</span></li><li><i class='fa fa-var-fax'></i><span class='i-name'>fa fa-var-fax</span></li><li><i class='fa fa-var-feed'></i><span class='i-name'>fa fa-var-feed</span></li><li><i class='fa fa-var-female'></i><span class='i-name'>fa fa-var-female</span></li><li><i class='fa fa-var-fighter-jet'></i><span class='i-name'>fa fa-var-fighter-jet</span></li><li><i class='fa fa-var-file'></i><span class='i-name'>fa fa-var-file</span></li><li><i class='fa fa-var-file-archive-o'></i><span class='i-name'>fa fa-var-file-archive-o</span></li><li><i class='fa fa-var-file-audio-o'></i><span class='i-name'>fa fa-var-file-audio-o</span></li><li><i class='fa fa-var-file-code-o'></i><span class='i-name'>fa fa-var-file-code-o</span></li><li><i class='fa fa-var-file-excel-o'></i><span class='i-name'>fa fa-var-file-excel-o</span></li><li><i class='fa fa-var-file-image-o'></i><span class='i-name'>fa fa-var-file-image-o</span></li><li><i class='fa fa-var-file-movie-o'></i><span class='i-name'>fa fa-var-file-movie-o</span></li><li><i class='fa fa-var-file-o'></i><span class='i-name'>fa fa-var-file-o</span></li><li><i class='fa fa-var-file-pdf-o'></i><span class='i-name'>fa fa-var-file-pdf-o</span></li><li><i class='fa fa-var-file-photo-o'></i><span class='i-name'>fa fa-var-file-photo-o</span></li><li><i class='fa fa-var-file-picture-o'></i><span class='i-name'>fa fa-var-file-picture-o</span></li><li><i class='fa fa-var-file-powerpoint-o'></i><span class='i-name'>fa fa-var-file-powerpoint-o</span></li><li><i class='fa fa-var-file-sound-o'></i><span class='i-name'>fa fa-var-file-sound-o</span></li><li><i class='fa fa-var-file-text'></i><span class='i-name'>fa fa-var-file-text</span></li><li><i class='fa fa-var-file-text-o'></i><span class='i-name'>fa fa-var-file-text-o</span></li><li><i class='fa fa-var-file-video-o'></i><span class='i-name'>fa fa-var-file-video-o</span></li><li><i class='fa fa-var-file-word-o'></i><span class='i-name'>fa fa-var-file-word-o</span></li><li><i class='fa fa-var-file-zip-o'></i><span class='i-name'>fa fa-var-file-zip-o</span></li><li><i class='fa fa-var-files-o'></i><span class='i-name'>fa fa-var-files-o</span></li><li><i class='fa fa-var-film'></i><span class='i-name'>fa fa-var-film</span></li><li><i class='fa fa-var-filter'></i><span class='i-name'>fa fa-var-filter</span></li><li><i class='fa fa-var-fire'></i><span class='i-name'>fa fa-var-fire</span></li><li><i class='fa fa-var-fire-extinguisher'></i><span class='i-name'>fa fa-var-fire-extinguisher</span></li><li><i class='fa fa-var-firefox'></i><span class='i-name'>fa fa-var-firefox</span></li><li><i class='fa fa-var-flag'></i><span class='i-name'>fa fa-var-flag</span></li><li><i class='fa fa-var-flag-checkered'></i><span class='i-name'>fa fa-var-flag-checkered</span></li><li><i class='fa fa-var-flag-o'></i><span class='i-name'>fa fa-var-flag-o</span></li><li><i class='fa fa-var-flash'></i><span class='i-name'>fa fa-var-flash</span></li><li><i class='fa fa-var-flask'></i><span class='i-name'>fa fa-var-flask</span></li><li><i class='fa fa-var-flickr'></i><span class='i-name'>fa fa-var-flickr</span></li><li><i class='fa fa-var-floppy-o'></i><span class='i-name'>fa fa-var-floppy-o</span></li><li><i class='fa fa-var-folder'></i><span class='i-name'>fa fa-var-folder</span></li><li><i class='fa fa-var-folder-o'></i><span class='i-name'>fa fa-var-folder-o</span></li><li><i class='fa fa-var-folder-open'></i><span class='i-name'>fa fa-var-folder-open</span></li><li><i class='fa fa-var-folder-open-o'></i><span class='i-name'>fa fa-var-folder-open-o</span></li><li><i class='fa fa-var-font'></i><span class='i-name'>fa fa-var-font</span></li><li><i class='fa fa-var-fonticons'></i><span class='i-name'>fa fa-var-fonticons</span></li><li><i class='fa fa-var-fort-awesome'></i><span class='i-name'>fa fa-var-fort-awesome</span></li><li><i class='fa fa-var-forumbee'></i><span class='i-name'>fa fa-var-forumbee</span></li><li><i class='fa fa-var-forward'></i><span class='i-name'>fa fa-var-forward</span></li><li><i class='fa fa-var-foursquare'></i><span class='i-name'>fa fa-var-foursquare</span></li><li><i class='fa fa-var-frown-o'></i><span class='i-name'>fa fa-var-frown-o</span></li><li><i class='fa fa-var-futbol-o'></i><span class='i-name'>fa fa-var-futbol-o</span></li><li><i class='fa fa-var-gamepad'></i><span class='i-name'>fa fa-var-gamepad</span></li><li><i class='fa fa-var-gavel'></i><span class='i-name'>fa fa-var-gavel</span></li><li><i class='fa fa-var-gbp'></i><span class='i-name'>fa fa-var-gbp</span></li><li><i class='fa fa-var-ge'></i><span class='i-name'>fa fa-var-ge</span></li><li><i class='fa fa-var-gear'></i><span class='i-name'>fa fa-var-gear</span></li><li><i class='fa fa-var-gears'></i><span class='i-name'>fa fa-var-gears</span></li><li><i class='fa fa-var-genderless'></i><span class='i-name'>fa fa-var-genderless</span></li><li><i class='fa fa-var-get-pocket'></i><span class='i-name'>fa fa-var-get-pocket</span></li><li><i class='fa fa-var-gg'></i><span class='i-name'>fa fa-var-gg</span></li><li><i class='fa fa-var-gg-circle'></i><span class='i-name'>fa fa-var-gg-circle</span></li><li><i class='fa fa-var-gift'></i><span class='i-name'>fa fa-var-gift</span></li><li><i class='fa fa-var-git'></i><span class='i-name'>fa fa-var-git</span></li><li><i class='fa fa-var-git-square'></i><span class='i-name'>fa fa-var-git-square</span></li><li><i class='fa fa-var-github'></i><span class='i-name'>fa fa-var-github</span></li><li><i class='fa fa-var-github-alt'></i><span class='i-name'>fa fa-var-github-alt</span></li><li><i class='fa fa-var-github-square'></i><span class='i-name'>fa fa-var-github-square</span></li><li><i class='fa fa-var-gittip'></i><span class='i-name'>fa fa-var-gittip</span></li><li><i class='fa fa-var-glass'></i><span class='i-name'>fa fa-var-glass</span></li><li><i class='fa fa-var-globe'></i><span class='i-name'>fa fa-var-globe</span></li><li><i class='fa fa-var-google'></i><span class='i-name'>fa fa-var-google</span></li><li><i class='fa fa-var-google-plus'></i><span class='i-name'>fa fa-var-google-plus</span></li><li><i class='fa fa-var-google-plus-square'></i><span class='i-name'>fa fa-var-google-plus-square</span></li><li><i class='fa fa-var-google-wallet'></i><span class='i-name'>fa fa-var-google-wallet</span></li><li><i class='fa fa-var-graduation-cap'></i><span class='i-name'>fa fa-var-graduation-cap</span></li><li><i class='fa fa-var-gratipay'></i><span class='i-name'>fa fa-var-gratipay</span></li><li><i class='fa fa-var-group'></i><span class='i-name'>fa fa-var-group</span></li><li><i class='fa fa-var-h-square'></i><span class='i-name'>fa fa-var-h-square</span></li><li><i class='fa fa-var-hacker-news'></i><span class='i-name'>fa fa-var-hacker-news</span></li><li><i class='fa fa-var-hand-grab-o'></i><span class='i-name'>fa fa-var-hand-grab-o</span></li><li><i class='fa fa-var-hand-lizard-o'></i><span class='i-name'>fa fa-var-hand-lizard-o</span></li><li><i class='fa fa-var-hand-o-down'></i><span class='i-name'>fa fa-var-hand-o-down</span></li><li><i class='fa fa-var-hand-o-left'></i><span class='i-name'>fa fa-var-hand-o-left</span></li><li><i class='fa fa-var-hand-o-right'></i><span class='i-name'>fa fa-var-hand-o-right</span></li><li><i class='fa fa-var-hand-o-up'></i><span class='i-name'>fa fa-var-hand-o-up</span></li><li><i class='fa fa-var-hand-paper-o'></i><span class='i-name'>fa fa-var-hand-paper-o</span></li><li><i class='fa fa-var-hand-peace-o'></i><span class='i-name'>fa fa-var-hand-peace-o</span></li><li><i class='fa fa-var-hand-pointer-o'></i><span class='i-name'>fa fa-var-hand-pointer-o</span></li><li><i class='fa fa-var-hand-rock-o'></i><span class='i-name'>fa fa-var-hand-rock-o</span></li><li><i class='fa fa-var-hand-scissors-o'></i><span class='i-name'>fa fa-var-hand-scissors-o</span></li><li><i class='fa fa-var-hand-spock-o'></i><span class='i-name'>fa fa-var-hand-spock-o</span></li><li><i class='fa fa-var-hand-stop-o'></i><span class='i-name'>fa fa-var-hand-stop-o</span></li><li><i class='fa fa-var-hashtag'></i><span class='i-name'>fa fa-var-hashtag</span></li><li><i class='fa fa-var-hdd-o'></i><span class='i-name'>fa fa-var-hdd-o</span></li><li><i class='fa fa-var-header'></i><span class='i-name'>fa fa-var-header</span></li><li><i class='fa fa-var-headphones'></i><span class='i-name'>fa fa-var-headphones</span></li><li><i class='fa fa-var-heart'></i><span class='i-name'>fa fa-var-heart</span></li><li><i class='fa fa-var-heart-o'></i><span class='i-name'>fa fa-var-heart-o</span></li><li><i class='fa fa-var-heartbeat'></i><span class='i-name'>fa fa-var-heartbeat</span></li><li><i class='fa fa-var-history'></i><span class='i-name'>fa fa-var-history</span></li><li><i class='fa fa-var-home'></i><span class='i-name'>fa fa-var-home</span></li><li><i class='fa fa-var-hospital-o'></i><span class='i-name'>fa fa-var-hospital-o</span></li><li><i class='fa fa-var-hotel'></i><span class='i-name'>fa fa-var-hotel</span></li><li><i class='fa fa-var-hourglass'></i><span class='i-name'>fa fa-var-hourglass</span></li><li><i class='fa fa-var-hourglass-1'></i><span class='i-name'>fa fa-var-hourglass-1</span></li><li><i class='fa fa-var-hourglass-2'></i><span class='i-name'>fa fa-var-hourglass-2</span></li><li><i class='fa fa-var-hourglass-3'></i><span class='i-name'>fa fa-var-hourglass-3</span></li><li><i class='fa fa-var-hourglass-end'></i><span class='i-name'>fa fa-var-hourglass-end</span></li><li><i class='fa fa-var-hourglass-half'></i><span class='i-name'>fa fa-var-hourglass-half</span></li><li><i class='fa fa-var-hourglass-o'></i><span class='i-name'>fa fa-var-hourglass-o</span></li><li><i class='fa fa-var-hourglass-start'></i><span class='i-name'>fa fa-var-hourglass-start</span></li><li><i class='fa fa-var-houzz'></i><span class='i-name'>fa fa-var-houzz</span></li><li><i class='fa fa-var-html5'></i><span class='i-name'>fa fa-var-html5</span></li><li><i class='fa fa-var-i-cursor'></i><span class='i-name'>fa fa-var-i-cursor</span></li><li><i class='fa fa-var-ils'></i><span class='i-name'>fa fa-var-ils</span></li><li><i class='fa fa-var-image'></i><span class='i-name'>fa fa-var-image</span></li><li><i class='fa fa-var-inbox'></i><span class='i-name'>fa fa-var-inbox</span></li><li><i class='fa fa-var-indent'></i><span class='i-name'>fa fa-var-indent</span></li><li><i class='fa fa-var-industry'></i><span class='i-name'>fa fa-var-industry</span></li><li><i class='fa fa-var-info'></i><span class='i-name'>fa fa-var-info</span></li><li><i class='fa fa-var-info-circle'></i><span class='i-name'>fa fa-var-info-circle</span></li><li><i class='fa fa-var-inr'></i><span class='i-name'>fa fa-var-inr</span></li><li><i class='fa fa-var-instagram'></i><span class='i-name'>fa fa-var-instagram</span></li><li><i class='fa fa-var-institution'></i><span class='i-name'>fa fa-var-institution</span></li><li><i class='fa fa-var-internet-explorer'></i><span class='i-name'>fa fa-var-internet-explorer</span></li><li><i class='fa fa-var-intersex'></i><span class='i-name'>fa fa-var-intersex</span></li><li><i class='fa fa-var-ioxhost'></i><span class='i-name'>fa fa-var-ioxhost</span></li><li><i class='fa fa-var-italic'></i><span class='i-name'>fa fa-var-italic</span></li><li><i class='fa fa-var-joomla'></i><span class='i-name'>fa fa-var-joomla</span></li><li><i class='fa fa-var-jpy'></i><span class='i-name'>fa fa-var-jpy</span></li><li><i class='fa fa-var-jsfiddle'></i><span class='i-name'>fa fa-var-jsfiddle</span></li><li><i class='fa fa-var-key'></i><span class='i-name'>fa fa-var-key</span></li><li><i class='fa fa-var-keyboard-o'></i><span class='i-name'>fa fa-var-keyboard-o</span></li><li><i class='fa fa-var-krw'></i><span class='i-name'>fa fa-var-krw</span></li><li><i class='fa fa-var-language'></i><span class='i-name'>fa fa-var-language</span></li><li><i class='fa fa-var-laptop'></i><span class='i-name'>fa fa-var-laptop</span></li><li><i class='fa fa-var-lastfm'></i><span class='i-name'>fa fa-var-lastfm</span></li><li><i class='fa fa-var-lastfm-square'></i><span class='i-name'>fa fa-var-lastfm-square</span></li><li><i class='fa fa-var-leaf'></i><span class='i-name'>fa fa-var-leaf</span></li><li><i class='fa fa-var-leanpub'></i><span class='i-name'>fa fa-var-leanpub</span></li><li><i class='fa fa-var-legal'></i><span class='i-name'>fa fa-var-legal</span></li><li><i class='fa fa-var-lemon-o'></i><span class='i-name'>fa fa-var-lemon-o</span></li><li><i class='fa fa-var-level-down'></i><span class='i-name'>fa fa-var-level-down</span></li><li><i class='fa fa-var-level-up'></i><span class='i-name'>fa fa-var-level-up</span></li><li><i class='fa fa-var-life-bouy'></i><span class='i-name'>fa fa-var-life-bouy</span></li><li><i class='fa fa-var-life-buoy'></i><span class='i-name'>fa fa-var-life-buoy</span></li><li><i class='fa fa-var-life-ring'></i><span class='i-name'>fa fa-var-life-ring</span></li><li><i class='fa fa-var-life-saver'></i><span class='i-name'>fa fa-var-life-saver</span></li><li><i class='fa fa-var-lightbulb-o'></i><span class='i-name'>fa fa-var-lightbulb-o</span></li><li><i class='fa fa-var-line-chart'></i><span class='i-name'>fa fa-var-line-chart</span></li><li><i class='fa fa-var-link'></i><span class='i-name'>fa fa-var-link</span></li><li><i class='fa fa-var-linkedin'></i><span class='i-name'>fa fa-var-linkedin</span></li><li><i class='fa fa-var-linkedin-square'></i><span class='i-name'>fa fa-var-linkedin-square</span></li><li><i class='fa fa-var-linux'></i><span class='i-name'>fa fa-var-linux</span></li><li><i class='fa fa-var-list'></i><span class='i-name'>fa fa-var-list</span></li><li><i class='fa fa-var-list-alt'></i><span class='i-name'>fa fa-var-list-alt</span></li><li><i class='fa fa-var-list-ol'></i><span class='i-name'>fa fa-var-list-ol</span></li><li><i class='fa fa-var-list-ul'></i><span class='i-name'>fa fa-var-list-ul</span></li><li><i class='fa fa-var-location-arrow'></i><span class='i-name'>fa fa-var-location-arrow</span></li><li><i class='fa fa-var-lock'></i><span class='i-name'>fa fa-var-lock</span></li><li><i class='fa fa-var-long-arrow-down'></i><span class='i-name'>fa fa-var-long-arrow-down</span></li><li><i class='fa fa-var-long-arrow-left'></i><span class='i-name'>fa fa-var-long-arrow-left</span></li><li><i class='fa fa-var-long-arrow-right'></i><span class='i-name'>fa fa-var-long-arrow-right</span></li><li><i class='fa fa-var-long-arrow-up'></i><span class='i-name'>fa fa-var-long-arrow-up</span></li><li><i class='fa fa-var-magic'></i><span class='i-name'>fa fa-var-magic</span></li><li><i class='fa fa-var-magnet'></i><span class='i-name'>fa fa-var-magnet</span></li><li><i class='fa fa-var-mail-forward'></i><span class='i-name'>fa fa-var-mail-forward</span></li><li><i class='fa fa-var-mail-reply'></i><span class='i-name'>fa fa-var-mail-reply</span></li><li><i class='fa fa-var-mail-reply-all'></i><span class='i-name'>fa fa-var-mail-reply-all</span></li><li><i class='fa fa-var-male'></i><span class='i-name'>fa fa-var-male</span></li><li><i class='fa fa-var-map'></i><span class='i-name'>fa fa-var-map</span></li><li><i class='fa fa-var-map-marker'></i><span class='i-name'>fa fa-var-map-marker</span></li><li><i class='fa fa-var-map-o'></i><span class='i-name'>fa fa-var-map-o</span></li><li><i class='fa fa-var-map-pin'></i><span class='i-name'>fa fa-var-map-pin</span></li><li><i class='fa fa-var-map-signs'></i><span class='i-name'>fa fa-var-map-signs</span></li><li><i class='fa fa-var-mars'></i><span class='i-name'>fa fa-var-mars</span></li><li><i class='fa fa-var-mars-double'></i><span class='i-name'>fa fa-var-mars-double</span></li><li><i class='fa fa-var-mars-stroke'></i><span class='i-name'>fa fa-var-mars-stroke</span></li><li><i class='fa fa-var-mars-stroke-h'></i><span class='i-name'>fa fa-var-mars-stroke-h</span></li><li><i class='fa fa-var-mars-stroke-v'></i><span class='i-name'>fa fa-var-mars-stroke-v</span></li><li><i class='fa fa-var-maxcdn'></i><span class='i-name'>fa fa-var-maxcdn</span></li><li><i class='fa fa-var-meanpath'></i><span class='i-name'>fa fa-var-meanpath</span></li><li><i class='fa fa-var-medium'></i><span class='i-name'>fa fa-var-medium</span></li><li><i class='fa fa-var-medkit'></i><span class='i-name'>fa fa-var-medkit</span></li><li><i class='fa fa-var-meh-o'></i><span class='i-name'>fa fa-var-meh-o</span></li><li><i class='fa fa-var-mercury'></i><span class='i-name'>fa fa-var-mercury</span></li><li><i class='fa fa-var-microphone'></i><span class='i-name'>fa fa-var-microphone</span></li><li><i class='fa fa-var-microphone-slash'></i><span class='i-name'>fa fa-var-microphone-slash</span></li><li><i class='fa fa-var-minus'></i><span class='i-name'>fa fa-var-minus</span></li><li><i class='fa fa-var-minus-circle'></i><span class='i-name'>fa fa-var-minus-circle</span></li><li><i class='fa fa-var-minus-square'></i><span class='i-name'>fa fa-var-minus-square</span></li><li><i class='fa fa-var-minus-square-o'></i><span class='i-name'>fa fa-var-minus-square-o</span></li><li><i class='fa fa-var-mixcloud'></i><span class='i-name'>fa fa-var-mixcloud</span></li><li><i class='fa fa-var-mobile'></i><span class='i-name'>fa fa-var-mobile</span></li><li><i class='fa fa-var-mobile-phone'></i><span class='i-name'>fa fa-var-mobile-phone</span></li><li><i class='fa fa-var-modx'></i><span class='i-name'>fa fa-var-modx</span></li><li><i class='fa fa-var-money'></i><span class='i-name'>fa fa-var-money</span></li><li><i class='fa fa-var-moon-o'></i><span class='i-name'>fa fa-var-moon-o</span></li><li><i class='fa fa-var-mortar-board'></i><span class='i-name'>fa fa-var-mortar-board</span></li><li><i class='fa fa-var-motorcycle'></i><span class='i-name'>fa fa-var-motorcycle</span></li><li><i class='fa fa-var-mouse-pointer'></i><span class='i-name'>fa fa-var-mouse-pointer</span></li><li><i class='fa fa-var-music'></i><span class='i-name'>fa fa-var-music</span></li><li><i class='fa fa-var-navicon'></i><span class='i-name'>fa fa-var-navicon</span></li><li><i class='fa fa-var-neuter'></i><span class='i-name'>fa fa-var-neuter</span></li><li><i class='fa fa-var-newspaper-o'></i><span class='i-name'>fa fa-var-newspaper-o</span></li><li><i class='fa fa-var-object-group'></i><span class='i-name'>fa fa-var-object-group</span></li><li><i class='fa fa-var-object-ungroup'></i><span class='i-name'>fa fa-var-object-ungroup</span></li><li><i class='fa fa-var-odnoklassniki'></i><span class='i-name'>fa fa-var-odnoklassniki</span></li><li><i class='fa fa-var-odnoklassniki-square'></i><span class='i-name'>fa fa-var-odnoklassniki-square</span></li><li><i class='fa fa-var-opencart'></i><span class='i-name'>fa fa-var-opencart</span></li><li><i class='fa fa-var-openid'></i><span class='i-name'>fa fa-var-openid</span></li><li><i class='fa fa-var-opera'></i><span class='i-name'>fa fa-var-opera</span></li><li><i class='fa fa-var-optin-monster'></i><span class='i-name'>fa fa-var-optin-monster</span></li><li><i class='fa fa-var-outdent'></i><span class='i-name'>fa fa-var-outdent</span></li><li><i class='fa fa-var-pagelines'></i><span class='i-name'>fa fa-var-pagelines</span></li><li><i class='fa fa-var-paint-brush'></i><span class='i-name'>fa fa-var-paint-brush</span></li><li><i class='fa fa-var-paper-plane'></i><span class='i-name'>fa fa-var-paper-plane</span></li><li><i class='fa fa-var-paper-plane-o'></i><span class='i-name'>fa fa-var-paper-plane-o</span></li><li><i class='fa fa-var-paperclip'></i><span class='i-name'>fa fa-var-paperclip</span></li><li><i class='fa fa-var-paragraph'></i><span class='i-name'>fa fa-var-paragraph</span></li><li><i class='fa fa-var-paste'></i><span class='i-name'>fa fa-var-paste</span></li><li><i class='fa fa-var-pause'></i><span class='i-name'>fa fa-var-pause</span></li><li><i class='fa fa-var-pause-circle'></i><span class='i-name'>fa fa-var-pause-circle</span></li><li><i class='fa fa-var-pause-circle-o'></i><span class='i-name'>fa fa-var-pause-circle-o</span></li><li><i class='fa fa-var-paw'></i><span class='i-name'>fa fa-var-paw</span></li><li><i class='fa fa-var-paypal'></i><span class='i-name'>fa fa-var-paypal</span></li><li><i class='fa fa-var-pencil'></i><span class='i-name'>fa fa-var-pencil</span></li><li><i class='fa fa-var-pencil-square'></i><span class='i-name'>fa fa-var-pencil-square</span></li><li><i class='fa fa-var-pencil-square-o'></i><span class='i-name'>fa fa-var-pencil-square-o</span></li><li><i class='fa fa-var-percent'></i><span class='i-name'>fa fa-var-percent</span></li><li><i class='fa fa-var-phone'></i><span class='i-name'>fa fa-var-phone</span></li><li><i class='fa fa-var-phone-square'></i><span class='i-name'>fa fa-var-phone-square</span></li><li><i class='fa fa-var-photo'></i><span class='i-name'>fa fa-var-photo</span></li><li><i class='fa fa-var-picture-o'></i><span class='i-name'>fa fa-var-picture-o</span></li><li><i class='fa fa-var-pie-chart'></i><span class='i-name'>fa fa-var-pie-chart</span></li><li><i class='fa fa-var-pied-piper'></i><span class='i-name'>fa fa-var-pied-piper</span></li><li><i class='fa fa-var-pied-piper-alt'></i><span class='i-name'>fa fa-var-pied-piper-alt</span></li><li><i class='fa fa-var-pinterest'></i><span class='i-name'>fa fa-var-pinterest</span></li><li><i class='fa fa-var-pinterest-p'></i><span class='i-name'>fa fa-var-pinterest-p</span></li><li><i class='fa fa-var-pinterest-square'></i><span class='i-name'>fa fa-var-pinterest-square</span></li><li><i class='fa fa-var-plane'></i><span class='i-name'>fa fa-var-plane</span></li><li><i class='fa fa-var-play'></i><span class='i-name'>fa fa-var-play</span></li><li><i class='fa fa-var-play-circle'></i><span class='i-name'>fa fa-var-play-circle</span></li><li><i class='fa fa-var-play-circle-o'></i><span class='i-name'>fa fa-var-play-circle-o</span></li><li><i class='fa fa-var-plug'></i><span class='i-name'>fa fa-var-plug</span></li><li><i class='fa fa-var-plus'></i><span class='i-name'>fa fa-var-plus</span></li><li><i class='fa fa-var-plus-circle'></i><span class='i-name'>fa fa-var-plus-circle</span></li><li><i class='fa fa-var-plus-square'></i><span class='i-name'>fa fa-var-plus-square</span></li><li><i class='fa fa-var-plus-square-o'></i><span class='i-name'>fa fa-var-plus-square-o</span></li><li><i class='fa fa-var-power-off'></i><span class='i-name'>fa fa-var-power-off</span></li><li><i class='fa fa-var-print'></i><span class='i-name'>fa fa-var-print</span></li><li><i class='fa fa-var-product-hunt'></i><span class='i-name'>fa fa-var-product-hunt</span></li><li><i class='fa fa-var-puzzle-piece'></i><span class='i-name'>fa fa-var-puzzle-piece</span></li><li><i class='fa fa-var-qq'></i><span class='i-name'>fa fa-var-qq</span></li><li><i class='fa fa-var-qrcode'></i><span class='i-name'>fa fa-var-qrcode</span></li><li><i class='fa fa-var-question'></i><span class='i-name'>fa fa-var-question</span></li><li><i class='fa fa-var-question-circle'></i><span class='i-name'>fa fa-var-question-circle</span></li><li><i class='fa fa-var-quote-left'></i><span class='i-name'>fa fa-var-quote-left</span></li><li><i class='fa fa-var-quote-right'></i><span class='i-name'>fa fa-var-quote-right</span></li><li><i class='fa fa-var-ra'></i><span class='i-name'>fa fa-var-ra</span></li><li><i class='fa fa-var-random'></i><span class='i-name'>fa fa-var-random</span></li><li><i class='fa fa-var-rebel'></i><span class='i-name'>fa fa-var-rebel</span></li><li><i class='fa fa-var-recycle'></i><span class='i-name'>fa fa-var-recycle</span></li><li><i class='fa fa-var-reddit'></i><span class='i-name'>fa fa-var-reddit</span></li><li><i class='fa fa-var-reddit-alien'></i><span class='i-name'>fa fa-var-reddit-alien</span></li><li><i class='fa fa-var-reddit-square'></i><span class='i-name'>fa fa-var-reddit-square</span></li><li><i class='fa fa-var-refresh'></i><span class='i-name'>fa fa-var-refresh</span></li><li><i class='fa fa-var-registered'></i><span class='i-name'>fa fa-var-registered</span></li><li><i class='fa fa-var-remove'></i><span class='i-name'>fa fa-var-remove</span></li><li><i class='fa fa-var-renren'></i><span class='i-name'>fa fa-var-renren</span></li><li><i class='fa fa-var-reorder'></i><span class='i-name'>fa fa-var-reorder</span></li><li><i class='fa fa-var-repeat'></i><span class='i-name'>fa fa-var-repeat</span></li><li><i class='fa fa-var-reply'></i><span class='i-name'>fa fa-var-reply</span></li><li><i class='fa fa-var-reply-all'></i><span class='i-name'>fa fa-var-reply-all</span></li><li><i class='fa fa-var-retweet'></i><span class='i-name'>fa fa-var-retweet</span></li><li><i class='fa fa-var-rmb'></i><span class='i-name'>fa fa-var-rmb</span></li><li><i class='fa fa-var-road'></i><span class='i-name'>fa fa-var-road</span></li><li><i class='fa fa-var-rocket'></i><span class='i-name'>fa fa-var-rocket</span></li><li><i class='fa fa-var-rotate-left'></i><span class='i-name'>fa fa-var-rotate-left</span></li><li><i class='fa fa-var-rotate-right'></i><span class='i-name'>fa fa-var-rotate-right</span></li><li><i class='fa fa-var-rouble'></i><span class='i-name'>fa fa-var-rouble</span></li><li><i class='fa fa-var-rss'></i><span class='i-name'>fa fa-var-rss</span></li><li><i class='fa fa-var-rss-square'></i><span class='i-name'>fa fa-var-rss-square</span></li><li><i class='fa fa-var-rub'></i><span class='i-name'>fa fa-var-rub</span></li><li><i class='fa fa-var-ruble'></i><span class='i-name'>fa fa-var-ruble</span></li><li><i class='fa fa-var-rupee'></i><span class='i-name'>fa fa-var-rupee</span></li><li><i class='fa fa-var-safari'></i><span class='i-name'>fa fa-var-safari</span></li><li><i class='fa fa-var-save'></i><span class='i-name'>fa fa-var-save</span></li><li><i class='fa fa-var-scissors'></i><span class='i-name'>fa fa-var-scissors</span></li><li><i class='fa fa-var-scribd'></i><span class='i-name'>fa fa-var-scribd</span></li><li><i class='fa fa-var-search'></i><span class='i-name'>fa fa-var-search</span></li><li><i class='fa fa-var-search-minus'></i><span class='i-name'>fa fa-var-search-minus</span></li><li><i class='fa fa-var-search-plus'></i><span class='i-name'>fa fa-var-search-plus</span></li><li><i class='fa fa-var-sellsy'></i><span class='i-name'>fa fa-var-sellsy</span></li><li><i class='fa fa-var-send'></i><span class='i-name'>fa fa-var-send</span></li><li><i class='fa fa-var-send-o'></i><span class='i-name'>fa fa-var-send-o</span></li><li><i class='fa fa-var-server'></i><span class='i-name'>fa fa-var-server</span></li><li><i class='fa fa-var-share'></i><span class='i-name'>fa fa-var-share</span></li><li><i class='fa fa-var-share-alt'></i><span class='i-name'>fa fa-var-share-alt</span></li><li><i class='fa fa-var-share-alt-square'></i><span class='i-name'>fa fa-var-share-alt-square</span></li><li><i class='fa fa-var-share-square'></i><span class='i-name'>fa fa-var-share-square</span></li><li><i class='fa fa-var-share-square-o'></i><span class='i-name'>fa fa-var-share-square-o</span></li><li><i class='fa fa-var-shekel'></i><span class='i-name'>fa fa-var-shekel</span></li><li><i class='fa fa-var-sheqel'></i><span class='i-name'>fa fa-var-sheqel</span></li><li><i class='fa fa-var-shield'></i><span class='i-name'>fa fa-var-shield</span></li><li><i class='fa fa-var-ship'></i><span class='i-name'>fa fa-var-ship</span></li><li><i class='fa fa-var-shirtsinbulk'></i><span class='i-name'>fa fa-var-shirtsinbulk</span></li><li><i class='fa fa-var-shopping-bag'></i><span class='i-name'>fa fa-var-shopping-bag</span></li><li><i class='fa fa-var-shopping-basket'></i><span class='i-name'>fa fa-var-shopping-basket</span></li><li><i class='fa fa-var-shopping-cart'></i><span class='i-name'>fa fa-var-shopping-cart</span></li><li><i class='fa fa-var-sign-in'></i><span class='i-name'>fa fa-var-sign-in</span></li><li><i class='fa fa-var-sign-out'></i><span class='i-name'>fa fa-var-sign-out</span></li><li><i class='fa fa-var-signal'></i><span class='i-name'>fa fa-var-signal</span></li><li><i class='fa fa-var-simplybuilt'></i><span class='i-name'>fa fa-var-simplybuilt</span></li><li><i class='fa fa-var-sitemap'></i><span class='i-name'>fa fa-var-sitemap</span></li><li><i class='fa fa-var-skyatlas'></i><span class='i-name'>fa fa-var-skyatlas</span></li><li><i class='fa fa-var-skype'></i><span class='i-name'>fa fa-var-skype</span></li><li><i class='fa fa-var-slack'></i><span class='i-name'>fa fa-var-slack</span></li><li><i class='fa fa-var-sliders'></i><span class='i-name'>fa fa-var-sliders</span></li><li><i class='fa fa-var-slideshare'></i><span class='i-name'>fa fa-var-slideshare</span></li><li><i class='fa fa-var-smile-o'></i><span class='i-name'>fa fa-var-smile-o</span></li><li><i class='fa fa-var-soccer-ball-o'></i><span class='i-name'>fa fa-var-soccer-ball-o</span></li><li><i class='fa fa-var-sort'></i><span class='i-name'>fa fa-var-sort</span></li><li><i class='fa fa-var-sort-alpha-asc'></i><span class='i-name'>fa fa-var-sort-alpha-asc</span></li><li><i class='fa fa-var-sort-alpha-desc'></i><span class='i-name'>fa fa-var-sort-alpha-desc</span></li><li><i class='fa fa-var-sort-amount-asc'></i><span class='i-name'>fa fa-var-sort-amount-asc</span></li><li><i class='fa fa-var-sort-amount-desc'></i><span class='i-name'>fa fa-var-sort-amount-desc</span></li><li><i class='fa fa-var-sort-asc'></i><span class='i-name'>fa fa-var-sort-asc</span></li><li><i class='fa fa-var-sort-desc'></i><span class='i-name'>fa fa-var-sort-desc</span></li><li><i class='fa fa-var-sort-down'></i><span class='i-name'>fa fa-var-sort-down</span></li><li><i class='fa fa-var-sort-numeric-asc'></i><span class='i-name'>fa fa-var-sort-numeric-asc</span></li><li><i class='fa fa-var-sort-numeric-desc'></i><span class='i-name'>fa fa-var-sort-numeric-desc</span></li><li><i class='fa fa-var-sort-up'></i><span class='i-name'>fa fa-var-sort-up</span></li><li><i class='fa fa-var-soundcloud'></i><span class='i-name'>fa fa-var-soundcloud</span></li><li><i class='fa fa-var-space-shuttle'></i><span class='i-name'>fa fa-var-space-shuttle</span></li><li><i class='fa fa-var-spinner'></i><span class='i-name'>fa fa-var-spinner</span></li><li><i class='fa fa-var-spoon'></i><span class='i-name'>fa fa-var-spoon</span></li><li><i class='fa fa-var-spotify'></i><span class='i-name'>fa fa-var-spotify</span></li><li><i class='fa fa-var-square'></i><span class='i-name'>fa fa-var-square</span></li><li><i class='fa fa-var-square-o'></i><span class='i-name'>fa fa-var-square-o</span></li><li><i class='fa fa-var-stack-exchange'></i><span class='i-name'>fa fa-var-stack-exchange</span></li><li><i class='fa fa-var-stack-overflow'></i><span class='i-name'>fa fa-var-stack-overflow</span></li><li><i class='fa fa-var-star'></i><span class='i-name'>fa fa-var-star</span></li><li><i class='fa fa-var-star-half'></i><span class='i-name'>fa fa-var-star-half</span></li><li><i class='fa fa-var-star-half-empty'></i><span class='i-name'>fa fa-var-star-half-empty</span></li><li><i class='fa fa-var-star-half-full'></i><span class='i-name'>fa fa-var-star-half-full</span></li><li><i class='fa fa-var-star-half-o'></i><span class='i-name'>fa fa-var-star-half-o</span></li><li><i class='fa fa-var-star-o'></i><span class='i-name'>fa fa-var-star-o</span></li><li><i class='fa fa-var-steam'></i><span class='i-name'>fa fa-var-steam</span></li><li><i class='fa fa-var-steam-square'></i><span class='i-name'>fa fa-var-steam-square</span></li><li><i class='fa fa-var-step-backward'></i><span class='i-name'>fa fa-var-step-backward</span></li><li><i class='fa fa-var-step-forward'></i><span class='i-name'>fa fa-var-step-forward</span></li><li><i class='fa fa-var-stethoscope'></i><span class='i-name'>fa fa-var-stethoscope</span></li><li><i class='fa fa-var-sticky-note'></i><span class='i-name'>fa fa-var-sticky-note</span></li><li><i class='fa fa-var-sticky-note-o'></i><span class='i-name'>fa fa-var-sticky-note-o</span></li><li><i class='fa fa-var-stop'></i><span class='i-name'>fa fa-var-stop</span></li><li><i class='fa fa-var-stop-circle'></i><span class='i-name'>fa fa-var-stop-circle</span></li><li><i class='fa fa-var-stop-circle-o'></i><span class='i-name'>fa fa-var-stop-circle-o</span></li><li><i class='fa fa-var-street-view'></i><span class='i-name'>fa fa-var-street-view</span></li><li><i class='fa fa-var-strikethrough'></i><span class='i-name'>fa fa-var-strikethrough</span></li><li><i class='fa fa-var-stumbleupon'></i><span class='i-name'>fa fa-var-stumbleupon</span></li><li><i class='fa fa-var-stumbleupon-circle'></i><span class='i-name'>fa fa-var-stumbleupon-circle</span></li><li><i class='fa fa-var-subscript'></i><span class='i-name'>fa fa-var-subscript</span></li><li><i class='fa fa-var-subway'></i><span class='i-name'>fa fa-var-subway</span></li><li><i class='fa fa-var-suitcase'></i><span class='i-name'>fa fa-var-suitcase</span></li><li><i class='fa fa-var-sun-o'></i><span class='i-name'>fa fa-var-sun-o</span></li><li><i class='fa fa-var-superscript'></i><span class='i-name'>fa fa-var-superscript</span></li><li><i class='fa fa-var-support'></i><span class='i-name'>fa fa-var-support</span></li><li><i class='fa fa-var-table'></i><span class='i-name'>fa fa-var-table</span></li><li><i class='fa fa-var-tablet'></i><span class='i-name'>fa fa-var-tablet</span></li><li><i class='fa fa-var-tachometer'></i><span class='i-name'>fa fa-var-tachometer</span></li><li><i class='fa fa-var-tag'></i><span class='i-name'>fa fa-var-tag</span></li><li><i class='fa fa-var-tags'></i><span class='i-name'>fa fa-var-tags</span></li><li><i class='fa fa-var-tasks'></i><span class='i-name'>fa fa-var-tasks</span></li><li><i class='fa fa-var-taxi'></i><span class='i-name'>fa fa-var-taxi</span></li><li><i class='fa fa-var-television'></i><span class='i-name'>fa fa-var-television</span></li><li><i class='fa fa-var-tencent-weibo'></i><span class='i-name'>fa fa-var-tencent-weibo</span></li><li><i class='fa fa-var-terminal'></i><span class='i-name'>fa fa-var-terminal</span></li><li><i class='fa fa-var-text-height'></i><span class='i-name'>fa fa-var-text-height</span></li><li><i class='fa fa-var-text-width'></i><span class='i-name'>fa fa-var-text-width</span></li><li><i class='fa fa-var-th'></i><span class='i-name'>fa fa-var-th</span></li><li><i class='fa fa-var-th-large'></i><span class='i-name'>fa fa-var-th-large</span></li><li><i class='fa fa-var-th-list'></i><span class='i-name'>fa fa-var-th-list</span></li><li><i class='fa fa-var-thumb-tack'></i><span class='i-name'>fa fa-var-thumb-tack</span></li><li><i class='fa fa-var-thumbs-down'></i><span class='i-name'>fa fa-var-thumbs-down</span></li><li><i class='fa fa-var-thumbs-o-down'></i><span class='i-name'>fa fa-var-thumbs-o-down</span></li><li><i class='fa fa-var-thumbs-o-up'></i><span class='i-name'>fa fa-var-thumbs-o-up</span></li><li><i class='fa fa-var-thumbs-up'></i><span class='i-name'>fa fa-var-thumbs-up</span></li><li><i class='fa fa-var-ticket'></i><span class='i-name'>fa fa-var-ticket</span></li><li><i class='fa fa-var-times'></i><span class='i-name'>fa fa-var-times</span></li><li><i class='fa fa-var-times-circle'></i><span class='i-name'>fa fa-var-times-circle</span></li><li><i class='fa fa-var-times-circle-o'></i><span class='i-name'>fa fa-var-times-circle-o</span></li><li><i class='fa fa-var-tint'></i><span class='i-name'>fa fa-var-tint</span></li><li><i class='fa fa-var-toggle-down'></i><span class='i-name'>fa fa-var-toggle-down</span></li><li><i class='fa fa-var-toggle-left'></i><span class='i-name'>fa fa-var-toggle-left</span></li><li><i class='fa fa-var-toggle-off'></i><span class='i-name'>fa fa-var-toggle-off</span></li><li><i class='fa fa-var-toggle-on'></i><span class='i-name'>fa fa-var-toggle-on</span></li><li><i class='fa fa-var-toggle-right'></i><span class='i-name'>fa fa-var-toggle-right</span></li><li><i class='fa fa-var-toggle-up'></i><span class='i-name'>fa fa-var-toggle-up</span></li><li><i class='fa fa-var-trademark'></i><span class='i-name'>fa fa-var-trademark</span></li><li><i class='fa fa-var-train'></i><span class='i-name'>fa fa-var-train</span></li><li><i class='fa fa-var-transgender'></i><span class='i-name'>fa fa-var-transgender</span></li><li><i class='fa fa-var-transgender-alt'></i><span class='i-name'>fa fa-var-transgender-alt</span></li><li><i class='fa fa-var-trash'></i><span class='i-name'>fa fa-var-trash</span></li><li><i class='fa fa-var-trash-o'></i><span class='i-name'>fa fa-var-trash-o</span></li><li><i class='fa fa-var-tree'></i><span class='i-name'>fa fa-var-tree</span></li><li><i class='fa fa-var-trello'></i><span class='i-name'>fa fa-var-trello</span></li><li><i class='fa fa-var-tripadvisor'></i><span class='i-name'>fa fa-var-tripadvisor</span></li><li><i class='fa fa-var-trophy'></i><span class='i-name'>fa fa-var-trophy</span></li><li><i class='fa fa-var-truck'></i><span class='i-name'>fa fa-var-truck</span></li><li><i class='fa fa-var-try'></i><span class='i-name'>fa fa-var-try</span></li><li><i class='fa fa-var-tty'></i><span class='i-name'>fa fa-var-tty</span></li><li><i class='fa fa-var-tumblr'></i><span class='i-name'>fa fa-var-tumblr</span></li><li><i class='fa fa-var-tumblr-square'></i><span class='i-name'>fa fa-var-tumblr-square</span></li><li><i class='fa fa-var-turkish-lira'></i><span class='i-name'>fa fa-var-turkish-lira</span></li><li><i class='fa fa-var-tv'></i><span class='i-name'>fa fa-var-tv</span></li><li><i class='fa fa-var-twitch'></i><span class='i-name'>fa fa-var-twitch</span></li><li><i class='fa fa-var-twitter'></i><span class='i-name'>fa fa-var-twitter</span></li><li><i class='fa fa-var-twitter-square'></i><span class='i-name'>fa fa-var-twitter-square</span></li><li><i class='fa fa-var-umbrella'></i><span class='i-name'>fa fa-var-umbrella</span></li><li><i class='fa fa-var-underline'></i><span class='i-name'>fa fa-var-underline</span></li><li><i class='fa fa-var-undo'></i><span class='i-name'>fa fa-var-undo</span></li><li><i class='fa fa-var-university'></i><span class='i-name'>fa fa-var-university</span></li><li><i class='fa fa-var-unlink'></i><span class='i-name'>fa fa-var-unlink</span></li><li><i class='fa fa-var-unlock'></i><span class='i-name'>fa fa-var-unlock</span></li><li><i class='fa fa-var-unlock-alt'></i><span class='i-name'>fa fa-var-unlock-alt</span></li><li><i class='fa fa-var-unsorted'></i><span class='i-name'>fa fa-var-unsorted</span></li><li><i class='fa fa-var-upload'></i><span class='i-name'>fa fa-var-upload</span></li><li><i class='fa fa-var-usb'></i><span class='i-name'>fa fa-var-usb</span></li><li><i class='fa fa-var-usd'></i><span class='i-name'>fa fa-var-usd</span></li><li><i class='fa fa-var-user'></i><span class='i-name'>fa fa-var-user</span></li><li><i class='fa fa-var-user-md'></i><span class='i-name'>fa fa-var-user-md</span></li><li><i class='fa fa-var-user-plus'></i><span class='i-name'>fa fa-var-user-plus</span></li><li><i class='fa fa-var-user-secret'></i><span class='i-name'>fa fa-var-user-secret</span></li><li><i class='fa fa-var-user-times'></i><span class='i-name'>fa fa-var-user-times</span></li><li><i class='fa fa-var-users'></i><span class='i-name'>fa fa-var-users</span></li><li><i class='fa fa-var-venus'></i><span class='i-name'>fa fa-var-venus</span></li><li><i class='fa fa-var-venus-double'></i><span class='i-name'>fa fa-var-venus-double</span></li><li><i class='fa fa-var-venus-mars'></i><span class='i-name'>fa fa-var-venus-mars</span></li></ul>";
							if($meta == '' || !isset($meta)){$meta=$std;}
						   	echo '<input type="text" class="capture-input vibe-form-text vibe-input" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $meta ) . '" />' . "\n";
							echo $desc;
						break;
						case 'icon':
							echo '<ul class="the-icons unstyled"><li><i class="fa fa-var-adn"></i><span class="i-name">fa fa-var-adn</span></li><li><i class="icon-elusive-icons-1"></i><span class="i-name">icon-elusive-icons-1</span></li><li><i class="icon-elusive-icons-2"></i><span class="i-name">icon-elusive-icons-2</span></li><li><i class="icon-elusive-icons-3"></i><span class="i-name">icon-elusive-icons-3</span></li><li><i class="icon-elusive-icons-4"></i><span class="i-name">icon-elusive-icons-4</span></li><li><i class="icon-elusive-icons-5"></i><span class="i-name">icon-elusive-icons-5</span></li><li><i class="icon-elusive-icons-6"></i><span class="i-name">icon-elusive-icons-6</span></li><li><i class="icon-elusive-icons-7"></i><span class="i-name">icon-elusive-icons-7</span></li><li><i class="icon-crown"></i><span class="i-name">icon-crown</span></li><li><i class="icon-burst"></i><span class="i-name">icon-burst</span></li><li><i class="icon-anchor"></i><span class="i-name">icon-anchor</span></li><li><i class="icon-dollar"></i><span class="i-name">icon-dollar</span></li><li><i class="icon-dollar-bill"></i><span class="i-name">icon-dollar-bill</span></li><li><i class="icon-foot"></i><span class="i-name">icon-foot</span></li><li><i class="icon-hearing-aid"></i><span class="i-name">icon-hearing-aid</span></li><li><i class="icon-guide-dog"></i><span class="i-name">icon-guide-dog</span></li><li><i class="icon-first-aid"></i><span class="i-name">icon-first-aid</span></li><li><i class="icon-paint-bucket"></i><span class="i-name">icon-paint-bucket</span></li><li><i class="icon-pencil"></i><span class="i-name">icon-pencil</span></li><li><i class="icon-paw"></i><span class="i-name">icon-paw</span></li><li><i class="icon-paperclip"></i><span class="i-name">icon-paperclip</span></li><li><i class="icon-pound"></i><span class="i-name">icon-pound</span></li><li><i class="icon-shopping-cart"></i><span class="i-name">icon-shopping-cart</span></li><li><i class="icon-sheriff-badge"></i><span class="i-name">icon-sheriff-badge</span></li><li><i class="icon-shield"></i><span class="i-name">icon-shield</span></li><li><i class="icon-trees"></i><span class="i-name">icon-trees</span></li><li><i class="icon-trophy"></i><span class="i-name">icon-trophy</span></li><li><i class="icon-fontawesome-webfont"></i><span class="i-name">icon-fontawesome-webfont</span></li><li><i class="icon-fontawesome-webfont-1"></i><span class="i-name">icon-fontawesome-webfont-1</span></li><li><i class="icon-address"></i><span class="i-name">icon-address</span></li><li><i class="icon-adjust"></i><span class="i-name">icon-adjust</span></li><li><i class="icon-air"></i><span class="i-name">icon-air</span></li><li><i class="icon-alert"></i><span class="i-name">icon-alert</span></li><li><i class="icon-archive"></i><span class="i-name">icon-archive</span></li><li><i class="icon-battery"></i><span class="i-name">icon-battery</span></li><li><i class="icon-behance"></i><span class="i-name">icon-behance</span></li><li><i class="icon-bell"></i><span class="i-name">icon-bell</span></li><li><i class="icon-block"></i><span class="i-name">icon-block</span></li><li><i class="icon-book"></i><span class="i-name">icon-book</span></li><li><i class="icon-camera"></i><span class="i-name">icon-camera</span></li><li><i class="icon-cancel"></i><span class="i-name">icon-cancel</span></li><li><i class="icon-cancel-circled"></i><span class="i-name">icon-cancel-circled</span></li><li><i class="icon-cancel-squared"></i><span class="i-name">icon-cancel-squared</span></li><li><i class="icon-cc"></i><span class="i-name">icon-cc</span></li><li><i class="icon-cc-share"></i><span class="i-name">icon-cc-share</span></li><li><i class="icon-cc-zero"></i><span class="i-name">icon-cc-zero</span></li><li><i class="icon-ccw"></i><span class="i-name">icon-ccw</span></li><li><i class="icon-cd"></i><span class="i-name">icon-cd</span></li><li><i class="icon-chart-area"></i><span class="i-name">icon-chart-area</span></li><li><i class="icon-screen"></i><span class="i-name">icon-screen</span></li><li><i class="icon-delicious"></i><span class="i-name">icon-delicious</span></li><li><i class="icon-instagram"></i><span class="i-name">icon-instagram</span></li><li><i class="icon-alarm"></i><span class="i-name">icon-alarm</span></li><li><i class="icon-envelope"></i><span class="i-name">icon-envelope</span></li><li><i class="icon-chat"></i><span class="i-name">icon-chat</span></li><li><i class="icon-inbox-alt"></i><span class="i-name">icon-inbox-alt</span></li><li><i class="icon-calculator"></i><span class="i-name">icon-calculator</span></li><li><i class="icon-camera-1"></i><span class="i-name">icon-camera-1</span></li><li><i class="icon-brightness-half"></i><span class="i-name">icon-brightness-half</span></li><li><i class="icon-list"></i><span class="i-name">icon-list</span></li><li><i class="icon-spinner"></i><span class="i-name">icon-spinner</span></li><li><i class="icon-windows"></i><span class="i-name">icon-windows</span></li><li><i class="icon-comments"></i><span class="i-name">icon-comments</span></li><li><i class="icon-rewind"></i><span class="i-name">icon-rewind</span></li><li><i class="icon-light-bulb"></i><span class="i-name">icon-light-bulb</span></li><li><i class="icon-iphone"></i><span class="i-name">icon-iphone</span></li><li><i class="icon-heart"></i><span class="i-name">icon-heart</span></li><li><i class="icon-calendar"></i><span class="i-name">icon-calendar</span></li><li><i class="icon-task"></i><span class="i-name">icon-task</span></li><li><i class="icon-store"></i><span class="i-name">icon-store</span></li><li><i class="icon-sound"></i><span class="i-name">icon-sound</span></li><li><i class="icon-fork-and-spoon"></i><span class="i-name">icon-fork-and-spoon</span></li><li><i class="icon-grid"></i><span class="i-name">icon-grid</span></li><li><i class="icon-portfolio"></i><span class="i-name">icon-portfolio</span></li><li><i class="icon-pin-alt"></i><span class="i-name">icon-pin-alt</span></li><li><i class="icon-question"></i><span class="i-name">icon-question</span></li><li><i class="icon-cmd"></i><span class="i-name">icon-cmd</span></li><li><i class="icon-newspaper-alt"></i><span class="i-name">icon-newspaper-alt</span></li><li><i class="icon-moon"></i><span class="i-name">icon-moon</span></li><li><i class="icon-home"></i><span class="i-name">icon-home</span></li><li><i class="icon-sound-alt"></i><span class="i-name">icon-sound-alt</span></li><li><i class="icon-sound-off"></i><span class="i-name">icon-sound-off</span></li><li><i class="icon-ipad"></i><span class="i-name">icon-ipad</span></li><li><i class="icon-stop"></i><span class="i-name">icon-stop</span></li><li><i class="icon-circle-full"></i><span class="i-name">icon-circle-full</span></li><li><i class="icon-forward"></i><span class="i-name">icon-forward</span></li><li><i class="icon-exclamation"></i><span class="i-name">icon-exclamation</span></li><li><i class="icon-settings"></i><span class="i-name">icon-settings</span></li><li><i class="icon-newspaper"></i><span class="i-name">icon-newspaper</span></li><li><i class="icon-grid-alt"></i><span class="i-name">icon-grid-alt</span></li><li><i class="icon-clock"></i><span class="i-name">icon-clock</span></li><li><i class="icon-pause"></i><span class="i-name">icon-pause</span></li><li><i class="icon-globe"></i><span class="i-name">icon-globe</span></li><li><i class="icon-clipboard"></i><span class="i-name">icon-clipboard</span></li><li><i class="icon-attachment"></i><span class="i-name">icon-attachment</span></li><li><i class="icon-forbid-1"></i><span class="i-name">icon-forbid-1</span></li><li><i class="icon-circle-half"></i><span class="i-name">icon-circle-half</span></li><li><i class="icon-inbox"></i><span class="i-name">icon-inbox</span></li><li><i class="icon-fork-and-knife"></i><span class="i-name">icon-fork-and-knife</span></li><li><i class="icon-brightness"></i><span class="i-name">icon-brightness</span></li><li><i class="icon-browser"></i><span class="i-name">icon-browser</span></li><li><i class="icon-hyperlink"></i><span class="i-name">icon-hyperlink</span></li><li><i class="icon-in-alt"></i><span class="i-name">icon-in-alt</span></li><li><i class="icon-menu"></i><span class="i-name">icon-menu</span></li><li><i class="icon-compose"></i><span class="i-name">icon-compose</span></li><li><i class="icon-anchor-1"></i><span class="i-name">icon-anchor-1</span></li><li><i class="icon-gallary"></i><span class="i-name">icon-gallary</span></li><li><i class="icon-cloud"></i><span class="i-name">icon-cloud</span></li><li><i class="icon-pin"></i><span class="i-name">icon-pin</span></li><li><i class="icon-play"></i><span class="i-name">icon-play</span></li><li><i class="icon-tag-stroke"></i><span class="i-name">icon-tag-stroke</span></li><li><i class="icon-tag-fill"></i><span class="i-name">icon-tag-fill</span></li><li><i class="icon-brush"></i><span class="i-name">icon-brush</span></li><li><i class="icon-bars"></i><span class="i-name">icon-bars</span></li><li><i class="icon-eject"></i><span class="i-name">icon-eject</span></li><li><i class="icon-book-1"></i><span class="i-name">icon-book-1</span></li><li><i class="icon-chart"></i><span class="i-name">icon-chart</span></li><li><i class="icon-key-fill"></i><span class="i-name">icon-key-fill</span></li><li><i class="icon-aperture-alt"></i><span class="i-name">icon-aperture-alt</span></li><li><i class="icon-book-alt"></i><span class="i-name">icon-book-alt</span></li><li><i class="icon-list-1"></i><span class="i-name">icon-list-1</span></li><li><i class="icon-map-pin-fill"></i><span class="i-name">icon-map-pin-fill</span></li><li><i class="icon-move-horizontal-alt1"></i><span class="i-name">icon-move-horizontal-alt1</span></li><li><i class="icon-headphones"></i><span class="i-name">icon-headphones</span></li><li><i class="icon-x"></i><span class="i-name">icon-x</span></li><li><i class="icon-check"></i><span class="i-name">icon-check</span></li><li><i class="icon-award-stroke"></i><span class="i-name">icon-award-stroke</span></li><li><i class="icon-wrench"></i><span class="i-name">icon-wrench</span></li><li><i class="icon-sun-fill"></i><span class="i-name">icon-sun-fill</span></li><li><i class="icon-move-horizontal-alt2"></i><span class="i-name">icon-move-horizontal-alt2</span></li><li><i class="icon-left-quote"></i><span class="i-name">icon-left-quote</span></li><li><i class="icon-clock-1"></i><span class="i-name">icon-clock-1</span></li><li><i class="icon-share"></i><span class="i-name">icon-share</span></li><li><i class="icon-map-pin-stroke"></i><span class="i-name">icon-map-pin-stroke</span></li><li><i class="icon-battery-full"></i><span class="i-name">icon-battery-full</span></li><li><i class="icon-paperclip-1"></i><span class="i-name">icon-paperclip-1</span></li><li><i class="icon-beaker-alt"></i><span class="i-name">icon-beaker-alt</span></li><li><i class="icon-bolt"></i><span class="i-name">icon-bolt</span></li><li><i class="icon-at"></i><span class="i-name">icon-at</span></li><li><i class="icon-pin-1"></i><span class="i-name">icon-pin-1</span></li><li><i class="icon-cloud-1"></i><span class="i-name">icon-cloud-1</span></li><li><i class="icon-layers-alt"></i><span class="i-name">icon-layers-alt</span></li><li><i class="icon-fullscreen-exit-alt"></i><span class="i-name">icon-fullscreen-exit-alt</span></li><li><i class="icon-left-quote-alt"></i><span class="i-name">icon-left-quote-alt</span></li><li><i class="icon-move-horizontal"></i><span class="i-name">icon-move-horizontal</span></li><li><i class="icon-volume-mute"></i><span class="i-name">icon-volume-mute</span></li><li><i class="icon-undo"></i><span class="i-name">icon-undo</span></li><li><i class="icon-umbrella"></i><span class="i-name">icon-umbrella</span></li><li><i class="icon-pen-alt2"></i><span class="i-name">icon-pen-alt2</span></li><li><i class="icon-heart-stroke"></i><span class="i-name">icon-heart-stroke</span></li><li><i class="icon-list-nested"></i><span class="i-name">icon-list-nested</span></li><li><i class="icon-move-vertical"></i><span class="i-name">icon-move-vertical</span></li><li><i class="icon-info"></i><span class="i-name">icon-info</span></li><li><i class="icon-pause-1"></i><span class="i-name">icon-pause-1</span></li><li><i class="icon-move-vertical-alt1"></i><span class="i-name">icon-move-vertical-alt1</span></li><li><i class="icon-spin"></i><span class="i-name">icon-spin</span></li><li><i class="icon-pen"></i><span class="i-name">icon-pen</span></li><li><i class="icon-plus-1"></i><span class="i-name">icon-plus-1</span></li><li><i class="icon-cog"></i><span class="i-name">icon-cog</span></li><li><i class="icon-reload"></i><span class="i-name">icon-reload</span></li><li><i class="icon-heart-fill"></i><span class="i-name">icon-heart-fill</span></li><li><i class="icon-equalizer"></i><span class="i-name">icon-equalizer</span></li><li><i class="icon-article"></i><span class="i-name">icon-article</span></li><li><i class="icon-cd-1"></i><span class="i-name">icon-cd-1</span></li><li><i class="icon-link"></i><span class="i-name">icon-link</span></li><li><i class="icon-pilcrow"></i><span class="i-name">icon-pilcrow</span></li><li><i class="icon-hash"></i><span class="i-name">icon-hash</span></li><li><i class="icon-check-alt"></i><span class="i-name">icon-check-alt</span></li><li><i class="icon-key-stroke"></i><span class="i-name">icon-key-stroke</span></li><li><i class="icon-folder-stroke"></i><span class="i-name">icon-folder-stroke</span></li><li><i class="icon-first"></i><span class="i-name">icon-first</span></li><li><i class="icon-eyedropper"></i><span class="i-name">icon-eyedropper</span></li><li><i class="icon-reload-alt"></i><span class="i-name">icon-reload-alt</span></li><li><i class="icon-aperture"></i><span class="i-name">icon-aperture</span></li><li><i class="icon-rain"></i><span class="i-name">icon-rain</span></li><li><i class="icon-beaker"></i><span class="i-name">icon-beaker</span></li><li><i class="icon-bars-alt"></i><span class="i-name">icon-bars-alt</span></li><li><i class="icon-image"></i><span class="i-name">icon-image</span></li><li><i class="icon-spin-alt"></i><span class="i-name">icon-spin-alt</span></li><li><i class="icon-pen-alt-stroke"></i><span class="i-name">icon-pen-alt-stroke</span></li><li><i class="icon-brush-alt"></i><span class="i-name">icon-brush-alt</span></li><li><i class="icon-document-alt-fill"></i><span class="i-name">icon-document-alt-fill</span></li><li><i class="icon-layers"></i><span class="i-name">icon-layers</span></li><li><i class="icon-compass"></i><span class="i-name">icon-compass</span></li><li><i class="icon-unlock-stroke"></i><span class="i-name">icon-unlock-stroke</span></li><li><i class="icon-box"></i><span class="i-name">icon-box</span></li><li><i class="icon-right-quote-alt"></i><span class="i-name">icon-right-quote-alt</span></li><li><i class="icon-last"></i><span class="i-name">icon-last</span></li><li><i class="icon-award-fill"></i><span class="i-name">icon-award-fill</span></li><li><i class="icon-pen-alt-fill"></i><span class="i-name">icon-pen-alt-fill</span></li><li><i class="icon-lock-fill"></i><span class="i-name">icon-lock-fill</span></li><li><i class="icon-calendar-alt-stroke"></i><span class="i-name">icon-calendar-alt-stroke</span></li><li><i class="icon-move-vertical-alt2"></i><span class="i-name">icon-move-vertical-alt2</span></li><li><i class="icon-steering-wheel"></i><span class="i-name">icon-steering-wheel</span></li><li><i class="icon-minus"></i><span class="i-name">icon-minus</span></li><li><i class="icon-map-pin-alt"></i><span class="i-name">icon-map-pin-alt</span></li><li><i class="icon-eye"></i><span class="i-name">icon-eye</span></li><li><i class="icon-calendar-alt-fill"></i><span class="i-name">icon-calendar-alt-fill</span></li><li><i class="icon-play-alt"></i><span class="i-name">icon-play-alt</span></li><li><i class="icon-fullscreen"></i><span class="i-name">icon-fullscreen</span></li><li><i class="icon-target"></i><span class="i-name">icon-target</span></li><li><i class="icon-dial"></i><span class="i-name">icon-dial</span></li><li><i class="icon-ampersand"></i><span class="i-name">icon-ampersand</span></li><li><i class="icon-question-mark"></i><span class="i-name">icon-question-mark</span></li><li><i class="icon-moon-stroke"></i><span class="i-name">icon-moon-stroke</span></li><li><i class="icon-movie"></i><span class="i-name">icon-movie</span></li><li><i class="icon-battery-charging"></i><span class="i-name">icon-battery-charging</span></li><li><i class="icon-document-stroke"></i><span class="i-name">icon-document-stroke</span></li><li><i class="icon-document-alt-stroke"></i><span class="i-name">icon-document-alt-stroke</span></li><li><i class="icon-lightbulb"></i><span class="i-name">icon-lightbulb</span></li><li><i class="icon-calendar-1"></i><span class="i-name">icon-calendar-1</span></li><li><i class="icon-unlock-fill"></i><span class="i-name">icon-unlock-fill</span></li><li><i class="icon-battery-empty"></i><span class="i-name">icon-battery-empty</span></li><li><i class="icon-sun-stroke"></i><span class="i-name">icon-sun-stroke</span></li><li><i class="icon-chart-alt"></i><span class="i-name">icon-chart-alt</span></li><li><i class="icon-battery-half"></i><span class="i-name">icon-battery-half</span></li><li><i class="icon-lock-stroke"></i><span class="i-name">icon-lock-stroke</span></li><li><i class="icon-book-alt2"></i><span class="i-name">icon-book-alt2</span></li><li><i class="icon-loop-alt1"></i><span class="i-name">icon-loop-alt1</span></li><li><i class="icon-fullscreen-exit"></i><span class="i-name">icon-fullscreen-exit</span></li><li><i class="icon-volume"></i><span class="i-name">icon-volume</span></li><li><i class="icon-mic"></i><span class="i-name">icon-mic</span></li><li><i class="icon-right-quote"></i><span class="i-name">icon-right-quote</span></li><li><i class="icon-play-1"></i><span class="i-name">icon-play-1</span></li><li><i class="icon-folder-fill"></i><span class="i-name">icon-folder-fill</span></li><li><i class="icon-moon-fill"></i><span class="i-name">icon-moon-fill</span></li><li><i class="icon-home-1"></i><span class="i-name">icon-home-1</span></li><li><i class="icon-camera-2"></i><span class="i-name">icon-camera-2</span></li><li><i class="icon-star"></i><span class="i-name">icon-star</span></li><li><i class="icon-read-more"></i><span class="i-name">icon-read-more</span></li><li><i class="icon-document-fill"></i><span class="i-name">icon-document-fill</span></li><li><i class="icon-excel-table-1"></i><span class="i-name">icon-excel-table-1</span></li><li><i class="icon-arrow-1-up"></i><span class="i-name">icon-arrow-1-up</span></li><li><i class="icon-female-symbol"></i><span class="i-name">icon-female-symbol</span></li><li><i class="icon-delivery-transport-2"></i><span class="i-name">icon-delivery-transport-2</span></li><li><i class="icon-content-41"></i><span class="i-name">icon-content-41</span></li><li><i class="icon-clip-paper-1"></i><span class="i-name">icon-clip-paper-1</span></li><li><i class="icon-check-5"></i><span class="i-name">icon-check-5</span></li><li><i class="icon-feed-rss-2"></i><span class="i-name">icon-feed-rss-2</span></li><li><i class="icon-server-1"></i><span class="i-name">icon-server-1</span></li><li><i class="icon-harddrive"></i><span class="i-name">icon-harddrive</span></li><li><i class="icon-car"></i><span class="i-name">icon-car</span></li><li><i class="icon-direction-move-1"></i><span class="i-name">icon-direction-move-1</span></li><li><i class="icon-certificate-file"></i><span class="i-name">icon-certificate-file</span></li><li><i class="icon-analytics-file-1"></i><span class="i-name">icon-analytics-file-1</span></li><li><i class="icon-male-symbol"></i><span class="i-name">icon-male-symbol</span></li><li><i class="icon-send-to-front"></i><span class="i-name">icon-send-to-front</span></li><li><i class="icon-movie-play-file-1"></i><span class="i-name">icon-movie-play-file-1</span></li><li><i class="icon-bookmark-tag"></i><span class="i-name">icon-bookmark-tag</span></li><li><i class="icon-filled-folder-1"></i><span class="i-name">icon-filled-folder-1</span></li><li><i class="icon-check-clipboard-1"></i><span class="i-name">icon-check-clipboard-1</span></li><li><i class="icon-clouds-cloudy"></i><span class="i-name">icon-clouds-cloudy</span></li><li><i class="icon-gears-setting"></i><span class="i-name">icon-gears-setting</span></li><li><i class="icon-html"></i><span class="i-name">icon-html</span></li><li><i class="icon-palm-tree"></i><span class="i-name">icon-palm-tree</span></li><li><i class="icon-wallet-money"></i><span class="i-name">icon-wallet-money</span></li><li><i class="icon-hospital"></i><span class="i-name">icon-hospital</span></li><li><i class="icon-previous-1"></i><span class="i-name">icon-previous-1</span></li><li><i class="icon-mailbox-1"></i><span class="i-name">icon-mailbox-1</span></li><li><i class="icon-arrow-1-right"></i><span class="i-name">icon-arrow-1-right</span></li><li><i class="icon-dropbox"></i><span class="i-name">icon-dropbox</span></li><li><i class="icon-rocket"></i><span class="i-name">icon-rocket</span></li><li><i class="icon-credit-card"></i><span class="i-name">icon-credit-card</span></li><li><i class="icon-campfire"></i><span class="i-name">icon-campfire</span></li><li><i class="icon-yang-ying"></i><span class="i-name">icon-yang-ying</span></li><li><i class="icon-omg-smiley"></i><span class="i-name">icon-omg-smiley</span></li><li><i class="icon-angry-smiley"></i><span class="i-name">icon-angry-smiley</span></li><li><i class="icon-television-tv"></i><span class="i-name">icon-television-tv</span></li><li><i class="icon-camera-surveillance-1"></i><span class="i-name">icon-camera-surveillance-1</span></li><li><i class="icon-apple"></i><span class="i-name">icon-apple</span></li><li><i class="icon-content-14"></i><span class="i-name">icon-content-14</span></li><li><i class="icon-hour-glass"></i><span class="i-name">icon-hour-glass</span></li><li><i class="icon-content-7"></i><span class="i-name">icon-content-7</span></li><li><i class="icon-arrow-right-1"></i><span class="i-name">icon-arrow-right-1</span></li><li><i class="icon-image-photo-file-1"></i><span class="i-name">icon-image-photo-file-1</span></li><li><i class="icon-bus"></i><span class="i-name">icon-bus</span></li><li><i class="icon-blink-smiley"></i><span class="i-name">icon-blink-smiley</span></li><li><i class="icon-bubbles-talk-1"></i><span class="i-name">icon-bubbles-talk-1</span></li><li><i class="icon-brush-1"></i><span class="i-name">icon-brush-1</span></li><li><i class="icon-send-to-back"></i><span class="i-name">icon-send-to-back</span></li><li><i class="icon-camera-video-3"></i><span class="i-name">icon-camera-video-3</span></li><li><i class="icon-battery-low"></i><span class="i-name">icon-battery-low</span></li><li><i class="icon-movie-play-1"></i><span class="i-name">icon-movie-play-1</span></li><li><i class="icon-home-1-1"></i><span class="i-name">icon-home-1-1</span></li><li><i class="icon-cd-cover-music"></i><span class="i-name">icon-cd-cover-music</span></li><li><i class="icon-linkedin-alt"></i><span class="i-name">icon-linkedin-alt</span></li><li><i class="icon-video-1"></i><span class="i-name">icon-video-1</span></li><li><i class="icon-bookmark-star-favorite"></i><span class="i-name">icon-bookmark-star-favorite</span></li><li><i class="icon-play-1-1"></i><span class="i-name">icon-play-1-1</span></li><li><i class="icon-pause-1-1"></i><span class="i-name">icon-pause-1-1</span></li><li><i class="icon-paint-brush-2"></i><span class="i-name">icon-paint-brush-2</span></li><li><i class="icon-train"></i><span class="i-name">icon-train</span></li><li><i class="icon-happy-smiley"></i><span class="i-name">icon-happy-smiley</span></li><li><i class="icon-missile-rocket"></i><span class="i-name">icon-missile-rocket</span></li><li><i class="icon-cloud-2"></i><span class="i-name">icon-cloud-2</span></li><li><i class="icon-bookmark-file-1"></i><span class="i-name">icon-bookmark-file-1</span></li><li><i class="icon-scooter"></i><span class="i-name">icon-scooter</span></li><li><i class="icon-magnet"></i><span class="i-name">icon-magnet</span></li><li><i class="icon-letter-mail-1"></i><span class="i-name">icon-letter-mail-1</span></li><li><i class="icon-color-palette"></i><span class="i-name">icon-color-palette</span></li><li><i class="icon-content-43"></i><span class="i-name">icon-content-43</span></li><li><i class="icon-bubble-talk-1"></i><span class="i-name">icon-bubble-talk-1</span></li><li><i class="icon-content-34"></i><span class="i-name">icon-content-34</span></li><li><i class="icon-carton-milk"></i><span class="i-name">icon-carton-milk</span></li><li><i class="icon-male-user-4"></i><span class="i-name">icon-male-user-4</span></li><li><i class="icon-ink-pen"></i><span class="i-name">icon-ink-pen</span></li><li><i class="icon-camera-1-1"></i><span class="i-name">icon-camera-1-1</span></li><li><i class="icon-snow-weather"></i><span class="i-name">icon-snow-weather</span></li><li><i class="icon-refresh-reload-1"></i><span class="i-name">icon-refresh-reload-1</span></li><li><i class="icon-at-email"></i><span class="i-name">icon-at-email</span></li><li><i class="icon-umbrella-1"></i><span class="i-name">icon-umbrella-1</span></li><li><i class="icon-lock-secure-1"></i><span class="i-name">icon-lock-secure-1</span></li><li><i class="icon-hand-stop"></i><span class="i-name">icon-hand-stop</span></li><li><i class="icon-battery-half-1"></i><span class="i-name">icon-battery-half-1</span></li><li><i class="icon-text-document"></i><span class="i-name">icon-text-document</span></li><li><i class="icon-layers-1"></i><span class="i-name">icon-layers-1</span></li><li><i class="icon-paypal"></i><span class="i-name">icon-paypal</span></li><li><i class="icon-helicopter"></i><span class="i-name">icon-helicopter</span></li><li><i class="icon-content-42"></i><span class="i-name">icon-content-42</span></li><li><i class="icon-clothes-hanger"></i><span class="i-name">icon-clothes-hanger</span></li><li><i class="icon-plus-zoom"></i><span class="i-name">icon-plus-zoom</span></li><li><i class="icon-unlock"></i><span class="i-name">icon-unlock</span></li><li><i class="icon-microscope"></i><span class="i-name">icon-microscope</span></li><li><i class="icon-click-hand-1"></i><span class="i-name">icon-click-hand-1</span></li><li><i class="icon-briefcase"></i><span class="i-name">icon-briefcase</span></li><li><i class="icon-3-css"></i><span class="i-name">icon-3-css</span></li><li><i class="icon-google-plus-1"></i><span class="i-name">icon-google-plus-1</span></li><li><i class="icon-close-off-2"></i><span class="i-name">icon-close-off-2</span></li><li><i class="icon-music-file-1"></i><span class="i-name">icon-music-file-1</span></li><li><i class="icon-tree"></i><span class="i-name">icon-tree</span></li><li><i class="icon-forward-1"></i><span class="i-name">icon-forward-1</span></li><li><i class="icon-script"></i><span class="i-name">icon-script</span></li><li><i class="icon-edit-pen-1"></i><span class="i-name">icon-edit-pen-1</span></li><li><i class="icon-content-1"></i><span class="i-name">icon-content-1</span></li><li><i class="icon-cash-register"></i><span class="i-name">icon-cash-register</span></li><li><i class="icon-call-old-telephone"></i><span class="i-name">icon-call-old-telephone</span></li><li><i class="icon-hail-weather"></i><span class="i-name">icon-hail-weather</span></li><li><i class="icon-gift"></i><span class="i-name">icon-gift</span></li><li><i class="icon-square-vector-2"></i><span class="i-name">icon-square-vector-2</span></li><li><i class="icon-van"></i><span class="i-name">icon-van</span></li><li><i class="icon-male-user-3"></i><span class="i-name">icon-male-user-3</span></li><li><i class="icon-content-8"></i><span class="i-name">icon-content-8</span></li><li><i class="icon-battery-charging-1"></i><span class="i-name">icon-battery-charging-1</span></li><li><i class="icon-rewind-1"></i><span class="i-name">icon-rewind-1</span></li><li><i class="icon-check-1"></i><span class="i-name">icon-check-1</span></li><li><i class="icon-airplane"></i><span class="i-name">icon-airplane</span></li><li><i class="icon-hat-magician"></i><span class="i-name">icon-hat-magician</span></li><li><i class="icon-boat"></i><span class="i-name">icon-boat</span></li><li><i class="icon-crown-king-1"></i><span class="i-name">icon-crown-king-1</span></li><li><i class="icon-bike"></i><span class="i-name">icon-bike</span></li><li><i class="icon-sad-smiley"></i><span class="i-name">icon-sad-smiley</span></li><li><i class="icon-burning-fire"></i><span class="i-name">icon-burning-fire</span></li><li><i class="icon-thermometer"></i><span class="i-name">icon-thermometer</span></li><li><i class="icon-map-pin-5"></i><span class="i-name">icon-map-pin-5</span></li><li><i class="icon-happy-smiley-very"></i><span class="i-name">icon-happy-smiley-very</span></li><li><i class="icon-eye-view-1"></i><span class="i-name">icon-eye-view-1</span></li><li><i class="icon-cannabis-hemp"></i><span class="i-name">icon-cannabis-hemp</span></li><li><i class="icon-interface-window-1"></i><span class="i-name">icon-interface-window-1</span></li><li><i class="icon-document-file-1"></i><span class="i-name">icon-document-file-1</span></li><li><i class="icon-arrow-1-left"></i><span class="i-name">icon-arrow-1-left</span></li><li><i class="icon-nurse-user"></i><span class="i-name">icon-nurse-user</span></li><li><i class="icon-content-44"></i><span class="i-name">icon-content-44</span></li><li><i class="icon-flag-mark"></i><span class="i-name">icon-flag-mark</span></li><li><i class="icon-square-vector-1"></i><span class="i-name">icon-square-vector-1</span></li><li><i class="icon-monitor-screen-1"></i><span class="i-name">icon-monitor-screen-1</span></li><li><i class="icon-next-1"></i><span class="i-name">icon-next-1</span></li><li><i class="icon-doctor"></i><span class="i-name">icon-doctor</span></li><li><i class="icon-favorite-map-pin"></i><span class="i-name">icon-favorite-map-pin</span></li><li><i class="icon-rain-weather"></i><span class="i-name">icon-rain-weather</span></li><li><i class="icon-polaroid"></i><span class="i-name">icon-polaroid</span></li><li><i class="icon-analytics-chart-graph"></i><span class="i-name">icon-analytics-chart-graph</span></li><li><i class="icon-medal-outline-star"></i><span class="i-name">icon-medal-outline-star</span></li><li><i class="icon-lightbulb-shine"></i><span class="i-name">icon-lightbulb-shine</span></li><li><i class="icon-arrow-down-1"></i><span class="i-name">icon-arrow-down-1</span></li><li><i class="icon-favorite-heart-outline"></i><span class="i-name">icon-favorite-heart-outline</span></li><li><i class="icon-advertising-megaphone-2"></i><span class="i-name">icon-advertising-megaphone-2</span></li><li><i class="icon-interface-windows"></i><span class="i-name">icon-interface-windows</span></li><li><i class="icon-ipod"></i><span class="i-name">icon-ipod</span></li><li><i class="icon-radar-2"></i><span class="i-name">icon-radar-2</span></li><li><i class="icon-minus-zoom"></i><span class="i-name">icon-minus-zoom</span></li><li><i class="icon-crhistmas-spruce-tree"></i><span class="i-name">icon-crhistmas-spruce-tree</span></li><li><i class="icon-arrow-cursor"></i><span class="i-name">icon-arrow-cursor</span></li><li><i class="icon-medal-rank-star"></i><span class="i-name">icon-medal-rank-star</span></li><li><i class="icon-database-5"></i><span class="i-name">icon-database-5</span></li><li><i class="icon-battery-full-1"></i><span class="i-name">icon-battery-full-1</span></li><li><i class="icon-chart-graph-file-1"></i><span class="i-name">icon-chart-graph-file-1</span></li><li><i class="icon-case-medic"></i><span class="i-name">icon-case-medic</span></li><li><i class="icon-disc-floppy-font"></i><span class="i-name">icon-disc-floppy-font</span></li><li><i class="icon-sun-weather"></i><span class="i-name">icon-sun-weather</span></li><li><i class="icon-parking-sign"></i><span class="i-name">icon-parking-sign</span></li><li><i class="icon-code-html-file-1"></i><span class="i-name">icon-code-html-file-1</span></li><li><i class="icon-date"></i><span class="i-name">icon-date</span></li><li><i class="icon-hand-hold"></i><span class="i-name">icon-hand-hold</span></li><li><i class="icon-cup-2"></i><span class="i-name">icon-cup-2</span></li><li><i class="icon-lightning-weather"></i><span class="i-name">icon-lightning-weather</span></li><li><i class="icon-cloud-sun"></i><span class="i-name">icon-cloud-sun</span></li><li><i class="icon-compressed-zip-file"></i><span class="i-name">icon-compressed-zip-file</span></li><li><i class="icon-road"></i><span class="i-name">icon-road</span></li><li><i class="icon-arrow-left-1"></i><span class="i-name">icon-arrow-left-1</span></li><li><i class="icon-building-24"></i><span class="i-name">icon-building-24</span></li><li><i class="icon-tennis-24"></i><span class="i-name">icon-tennis-24</span></li><li><i class="icon-skiing-24"></i><span class="i-name">icon-skiing-24</span></li><li><i class="icon-bus-24"></i><span class="i-name">icon-bus-24</span></li><li><i class="icon-park2-24"></i><span class="i-name">icon-park2-24</span></li><li><i class="icon-circle-24"></i><span class="i-name">icon-circle-24</span></li><li><i class="icon-golf-24"></i><span class="i-name">icon-golf-24</span></li><li><i class="icon-star-24"></i><span class="i-name">icon-star-24</span></li><li><i class="icon-water-24"></i><span class="i-name">icon-water-24</span></li><li><i class="icon-disability-24"></i><span class="i-name">icon-disability-24</span></li><li><i class="icon-art-gallery-24"></i><span class="i-name">icon-art-gallery-24</span></li><li><i class="icon-religious-jewish-24"></i><span class="i-name">icon-religious-jewish-24</span></li><li><i class="icon-marker-24"></i><span class="i-name">icon-marker-24</span></li><li><i class="icon-campsite-24"></i><span class="i-name">icon-campsite-24</span></li><li><i class="icon-prison-24"></i><span class="i-name">icon-prison-24</span></li><li><i class="icon-baseball-24"></i><span class="i-name">icon-baseball-24</span></li><li><i class="icon-pharmacy-24"></i><span class="i-name">icon-pharmacy-24</span></li><li><i class="icon-zoo-24"></i><span class="i-name">icon-zoo-24</span></li><li><i class="icon-triangle-stroked-24"></i><span class="i-name">icon-triangle-stroked-24</span></li><li><i class="icon-star-stroked-24"></i><span class="i-name">icon-star-stroked-24</span></li><li><i class="icon-slaughterhouse-24"></i><span class="i-name">icon-slaughterhouse-24</span></li><li><i class="icon-parking-24"></i><span class="i-name">icon-parking-24</span></li><li><i class="icon-heliport-24"></i><span class="i-name">icon-heliport-24</span></li><li><i class="icon-restaurant-24"></i><span class="i-name">icon-restaurant-24</span></li><li><i class="icon-shop-24"></i><span class="i-name">icon-shop-24</span></li><li><i class="icon-religious-christian-24"></i><span class="i-name">icon-religious-christian-24</span></li><li><i class="icon-museum-24"></i><span class="i-name">icon-museum-24</span></li><li><i class="icon-cross"></i><span class="i-name">icon-cross</span></li><li><i class="icon-toilets-24"></i><span class="i-name">icon-toilets-24</span></li><li><i class="icon-rail-underground-24"></i><span class="i-name">icon-rail-underground-24</span></li><li><i class="icon-basketball-24"></i><span class="i-name">icon-basketball-24</span></li><li><i class="icon-beer-24"></i><span class="i-name">icon-beer-24</span></li><li><i class="icon-airfield-24"></i><span class="i-name">icon-airfield-24</span></li><li><i class="icon-wetland-24"></i><span class="i-name">icon-wetland-24</span></li><li><i class="icon-soccer-24"></i><span class="i-name">icon-soccer-24</span></li><li><i class="icon-dam"></i><span class="i-name">icon-dam</span></li><li><i class="icon-bank-24"></i><span class="i-name">icon-bank-24</span></li><li><i class="icon-fuel-24"></i><span class="i-name">icon-fuel-24</span></li><li><i class="icon-school-24"></i><span class="i-name">icon-school-24</span></li><li><i class="icon-commercial-24"></i><span class="i-name">icon-commercial-24</span></li><li><i class="icon-religious-muslim-24"></i><span class="i-name">icon-religious-muslim-24</span></li><li><i class="icon-america-football-24"></i><span class="i-name">icon-america-football-24</span></li><li><i class="icon-swimming-24"></i><span class="i-name">icon-swimming-24</span></li><li><i class="icon-square-24"></i><span class="i-name">icon-square-24</span></li><li><i class="icon-circle-stroked-24"></i><span class="i-name">icon-circle-stroked-24</span></li><li><i class="icon-rail-above-24"></i><span class="i-name">icon-rail-above-24</span></li><li><i class="icon-monument-24"></i><span class="i-name">icon-monument-24</span></li><li><i class="icon-cinema-24"></i><span class="i-name">icon-cinema-24</span></li><li><i class="icon-ferry-24"></i><span class="i-name">icon-ferry-24</span></li><li><i class="icon-fast-food-24"></i><span class="i-name">icon-fast-food-24</span></li><li><i class="icon-cemetery-24"></i><span class="i-name">icon-cemetery-24</span></li><li><i class="icon-park-24"></i><span class="i-name">icon-park-24</span></li><li><i class="icon-telephone-24"></i><span class="i-name">icon-telephone-24</span></li><li><i class="icon-rail-24"></i><span class="i-name">icon-rail-24</span></li><li><i class="icon-college-24"></i><span class="i-name">icon-college-24</span></li><li><i class="icon-warehouse-24"></i><span class="i-name">icon-warehouse-24</span></li><li><i class="icon-hospital-24"></i><span class="i-name">icon-hospital-24</span></li><li><i class="icon-cricket-24"></i><span class="i-name">icon-cricket-24</span></li><li><i class="icon-parking-garage-24"></i><span class="i-name">icon-parking-garage-24</span></li><li><i class="icon-harbor-24"></i><span class="i-name">icon-harbor-24</span></li><li><i class="icon-cafe-24"></i><span class="i-name">icon-cafe-24</span></li><li><i class="icon-police-24"></i><span class="i-name">icon-police-24</span></li><li><i class="icon-industrial-24"></i><span class="i-name">icon-industrial-24</span></li><li><i class="icon-garden-24"></i><span class="i-name">icon-garden-24</span></li><li><i class="icon-triangle-24"></i><span class="i-name">icon-triangle-24</span></li><li><i class="icon-bar-24"></i><span class="i-name">icon-bar-24</span></li><li><i class="icon-place-of-worship-24"></i><span class="i-name">icon-place-of-worship-24</span></li><li><i class="icon-oil-well-24"></i><span class="i-name">icon-oil-well-24</span></li><li><i class="icon-library-24"></i><span class="i-name">icon-library-24</span></li><li><i class="icon-alcohol-shop-24"></i><span class="i-name">icon-alcohol-shop-24</span></li><li><i class="icon-bicycle-24"></i><span class="i-name">icon-bicycle-24</span></li><li><i class="icon-town-hall-24"></i><span class="i-name">icon-town-hall-24</span></li><li><i class="icon-music-24"></i><span class="i-name">icon-music-24</span></li><li><i class="icon-square-stroked-24"></i><span class="i-name">icon-square-stroked-24</span></li><li><i class="icon-pitch-24"></i><span class="i-name">icon-pitch-24</span></li><li><i class="icon-danger-24"></i><span class="i-name">icon-danger-24</span></li><li><i class="icon-fire-station-24"></i><span class="i-name">icon-fire-station-24</span></li><li><i class="icon-theatre-24"></i><span class="i-name">icon-theatre-24</span></li><li><i class="icon-marker-stroked-24"></i><span class="i-name">icon-marker-stroked-24</span></li><li><i class="icon-lodging-24"></i><span class="i-name">icon-lodging-24</span></li><li><i class="icon-embassy-24"></i><span class="i-name">icon-embassy-24</span></li><li><i class="icon-airport-24"></i><span class="i-name">icon-airport-24</span></li><li><i class="icon-logging-24"></i><span class="i-name">icon-logging-24</span></li><li><i class="icon-aim"></i><span class="i-name">icon-aim</span></li><li><i class="icon-aim-alt"></i><span class="i-name">icon-aim-alt</span></li><li><i class="icon-amazon"></i><span class="i-name">icon-amazon</span></li><li><i class="icon-app-store"></i><span class="i-name">icon-app-store</span></li><li><i class="icon-apple-1"></i><span class="i-name">icon-apple-1</span></li><li><i class="icon-arto"></i><span class="i-name">icon-arto</span></li><li><i class="icon-aws"></i><span class="i-name">icon-aws</span></li><li><i class="icon-baidu"></i><span class="i-name">icon-baidu</span></li><li><i class="icon-basecamp"></i><span class="i-name">icon-basecamp</span></li><li><i class="icon-bebo"></i><span class="i-name">icon-bebo</span></li><li><i class="icon-behance-1"></i><span class="i-name">icon-behance-1</span></li><li><i class="icon-bing"></i><span class="i-name">icon-bing</span></li><li><i class="icon-blip"></i><span class="i-name">icon-blip</span></li><li><i class="icon-blogger"></i><span class="i-name">icon-blogger</span></li><li><i class="icon-bnter"></i><span class="i-name">icon-bnter</span></li><li><i class="icon-brightkite"></i><span class="i-name">icon-brightkite</span></li><li><i class="icon-cinch"></i><span class="i-name">icon-cinch</span></li><li><i class="icon-cloudapp"></i><span class="i-name">icon-cloudapp</span></li><li><i class="icon-coroflot"></i><span class="i-name">icon-coroflot</span></li><li><i class="icon-creative-commons"></i><span class="i-name">icon-creative-commons</span></li><li><i class="icon-dailybooth"></i><span class="i-name">icon-dailybooth</span></li><li><i class="icon-delicious-1"></i><span class="i-name">icon-delicious-1</span></li><li><i class="icon-designbump"></i><span class="i-name">icon-designbump</span></li><li><i class="icon-designfloat"></i><span class="i-name">icon-designfloat</span></li><li><i class="icon-designmoo"></i><span class="i-name">icon-designmoo</span></li><li><i class="icon-deviantart"></i><span class="i-name">icon-deviantart</span></li><li><i class="icon-digg"></i><span class="i-name">icon-digg</span></li><li><i class="icon-digg-alt"></i><span class="i-name">icon-digg-alt</span></li><li><i class="icon-diigo"></i><span class="i-name">icon-diigo</span></li><li><i class="icon-dribbble-2"></i><span class="i-name">icon-dribbble-2</span></li><li><i class="icon-dropbox-1"></i><span class="i-name">icon-dropbox-1</span></li><li><i class="icon-drupal"></i><span class="i-name">icon-drupal</span></li><li><i class="icon-dzone"></i><span class="i-name">icon-dzone</span></li><li><i class="icon-ebay"></i><span class="i-name">icon-ebay</span></li><li><i class="icon-ember"></i><span class="i-name">icon-ember</span></li><li><i class="icon-etsy"></i><span class="i-name">icon-etsy</span></li><li><i class="icon-evernote"></i><span class="i-name">icon-evernote</span></li><li><i class="icon-facebook-1"></i><span class="i-name">icon-facebook-1</span></li><li><i class="icon-facebook-places"></i><span class="i-name">icon-facebook-places</span></li><li><i class="icon-facto"></i><span class="i-name">icon-facto</span></li><li><i class="icon-feedburner"></i><span class="i-name">icon-feedburner</span></li><li><i class="icon-flickr"></i><span class="i-name">icon-flickr</span></li><li><i class="icon-folkd"></i><span class="i-name">icon-folkd</span></li><li><i class="icon-formspring"></i><span class="i-name">icon-formspring</span></li><li><i class="icon-forrst"></i><span class="i-name">icon-forrst</span></li><li><i class="icon-foursquare"></i><span class="i-name">icon-foursquare</span></li><li><i class="icon-friendfeed"></i><span class="i-name">icon-friendfeed</span></li><li><i class="icon-friendster"></i><span class="i-name">icon-friendster</span></li><li><i class="icon-gdgt"></i><span class="i-name">icon-gdgt</span></li><li><i class="icon-github"></i><span class="i-name">icon-github</span></li><li><i class="icon-github-alt"></i><span class="i-name">icon-github-alt</span></li><li><i class="icon-goodreads"></i><span class="i-name">icon-goodreads</span></li><li><i class="icon-google"></i><span class="i-name">icon-google</span></li><li><i class="icon-google-buzz"></i><span class="i-name">icon-google-buzz</span></li><li><i class="icon-google-talk"></i><span class="i-name">icon-google-talk</span></li><li><i class="icon-gowalla"></i><span class="i-name">icon-gowalla</span></li><li><i class="icon-gowalla-alt"></i><span class="i-name">icon-gowalla-alt</span></li><li><i class="icon-grooveshark"></i><span class="i-name">icon-grooveshark</span></li><li><i class="icon-hacker-news"></i><span class="i-name">icon-hacker-news</span></li><li><i class="icon-hi5"></i><span class="i-name">icon-hi5</span></li><li><i class="icon-hype-machine"></i><span class="i-name">icon-hype-machine</span></li><li><i class="icon-hyves"></i><span class="i-name">icon-hyves</span></li><li><i class="icon-icq"></i><span class="i-name">icon-icq</span></li><li><i class="icon-identi"></i><span class="i-name">icon-identi</span></li><li><i class="icon-instapaper"></i><span class="i-name">icon-instapaper</span></li><li><i class="icon-itunes"></i><span class="i-name">icon-itunes</span></li><li><i class="icon-kik"></i><span class="i-name">icon-kik</span></li><li><i class="icon-krop"></i><span class="i-name">icon-krop</span></li><li><i class="icon-last-1"></i><span class="i-name">icon-last-1</span></li><li><i class="icon-linkedin"></i><span class="i-name">icon-linkedin</span></li><li><i class="icon-linkedin-alt-1"></i><span class="i-name">icon-linkedin-alt-1</span></li><li><i class="icon-livejournal"></i><span class="i-name">icon-livejournal</span></li><li><i class="icon-lovedsgn"></i><span class="i-name">icon-lovedsgn</span></li><li><i class="icon-meetup"></i><span class="i-name">icon-meetup</span></li><li><i class="icon-metacafe"></i><span class="i-name">icon-metacafe</span></li><li><i class="icon-ming"></i><span class="i-name">icon-ming</span></li><li><i class="icon-mister-wong"></i><span class="i-name">icon-mister-wong</span></li><li><i class="icon-mixx"></i><span class="i-name">icon-mixx</span></li><li><i class="icon-mixx-alt"></i><span class="i-name">icon-mixx-alt</span></li><li><i class="icon-mobileme"></i><span class="i-name">icon-mobileme</span></li><li><i class="icon-msn-messenger"></i><span class="i-name">icon-msn-messenger</span></li><li><i class="icon-myspace"></i><span class="i-name">icon-myspace</span></li><li><i class="icon-myspace-alt"></i><span class="i-name">icon-myspace-alt</span></li><li><i class="icon-newsvine"></i><span class="i-name">icon-newsvine</span></li><li><i class="icon-official"></i><span class="i-name">icon-official</span></li><li><i class="icon-openid"></i><span class="i-name">icon-openid</span></li><li><i class="icon-orkut"></i><span class="i-name">icon-orkut</span></li><li><i class="icon-pandora"></i><span class="i-name">icon-pandora</span></li><li><i class="icon-path"></i><span class="i-name">icon-path</span></li><li><i class="icon-paypal-1"></i><span class="i-name">icon-paypal-1</span></li><li><i class="icon-photobucket"></i><span class="i-name">icon-photobucket</span></li><li><i class="icon-picasa"></i><span class="i-name">icon-picasa</span></li><li><i class="icon-picassa"></i><span class="i-name">icon-picassa</span></li><li><i class="icon-pinboard"></i><span class="i-name">icon-pinboard</span></li><li><i class="icon-ping"></i><span class="i-name">icon-ping</span></li><li><i class="icon-pingchat"></i><span class="i-name">icon-pingchat</span></li><li><i class="icon-playstation"></i><span class="i-name">icon-playstation</span></li><li><i class="icon-plixi"></i><span class="i-name">icon-plixi</span></li><li><i class="icon-plurk"></i><span class="i-name">icon-plurk</span></li><li><i class="icon-podcast"></i><span class="i-name">icon-podcast</span></li><li><i class="icon-posterous"></i><span class="i-name">icon-posterous</span></li><li><i class="icon-qik"></i><span class="i-name">icon-qik</span></li><li><i class="icon-quik"></i><span class="i-name">icon-quik</span></li><li><i class="icon-quora"></i><span class="i-name">icon-quora</span></li><li><i class="icon-rdio"></i><span class="i-name">icon-rdio</span></li><li><i class="icon-readernaut"></i><span class="i-name">icon-readernaut</span></li><li><i class="icon-reddit"></i><span class="i-name">icon-reddit</span></li><li><i class="icon-retweet"></i><span class="i-name">icon-retweet</span></li><li><i class="icon-robo"></i><span class="i-name">icon-robo</span></li><li><i class="icon-rss-1"></i><span class="i-name">icon-rss-1</span></li><li><i class="icon-scribd"></i><span class="i-name">icon-scribd</span></li><li><i class="icon-sharethis"></i><span class="i-name">icon-sharethis</span></li><li><i class="icon-simplenote"></i><span class="i-name">icon-simplenote</span></li><li><i class="icon-skype-1"></i><span class="i-name">icon-skype-1</span></li><li><i class="icon-slashdot"></i><span class="i-name">icon-slashdot</span></li><li><i class="icon-slideshare"></i><span class="i-name">icon-slideshare</span></li><li><i class="icon-smugmug"></i><span class="i-name">icon-smugmug</span></li><li><i class="icon-soundcloud"></i><span class="i-name">icon-soundcloud</span></li><li><i class="icon-spotify"></i><span class="i-name">icon-spotify</span></li><li><i class="icon-squarespace"></i><span class="i-name">icon-squarespace</span></li><li><i class="icon-squidoo"></i><span class="i-name">icon-squidoo</span></li><li><i class="icon-steam"></i><span class="i-name">icon-steam</span></li><li><i class="icon-stumbleupon"></i><span class="i-name">icon-stumbleupon</span></li><li><i class="icon-technorati"></i><span class="i-name">icon-technorati</span></li><li><i class="icon-threewords"></i><span class="i-name">icon-threewords</span></li><li><i class="icon-tribe"></i><span class="i-name">icon-tribe</span></li><li><i class="icon-tripit"></i><span class="i-name">icon-tripit</span></li><li><i class="icon-tumblr"></i><span class="i-name">icon-tumblr</span></li><li><i class="icon-twitter-1"></i><span class="i-name">icon-twitter-1</span></li><li><i class="icon-twitter-alt-1"></i><span class="i-name">icon-twitter-alt-1</span></li><li><i class="icon-vcard"></i><span class="i-name">icon-vcard</span></li><li><i class="icon-viddler"></i><span class="i-name">icon-viddler</span></li><li><i class="icon-vimeo"></i><span class="i-name">icon-vimeo</span></li><li><i class="icon-virb"></i><span class="i-name">icon-virb</span></li><li><i class="icon-w3"></i><span class="i-name">icon-w3</span></li><li><i class="icon-whatsapp"></i><span class="i-name">icon-whatsapp</span></li><li><i class="icon-wikipedia"></i><span class="i-name">icon-wikipedia</span></li><li><i class="icon-windows-1"></i><span class="i-name">icon-windows-1</span></li><li><i class="icon-wists"></i><span class="i-name">icon-wists</span></li><li><i class="icon-wordpress"></i><span class="i-name">icon-wordpress</span></li><li><i class="icon-wordpress-alt"></i><span class="i-name">icon-wordpress-alt</span></li><li><i class="icon-xing"></i><span class="i-name">icon-xing</span></li><li><i class="icon-yahoo"></i><span class="i-name">icon-yahoo</span></li><li><i class="icon-yahoo-buzz"></i><span class="i-name">icon-yahoo-buzz</span></li><li><i class="icon-yahoo-messenger"></i><span class="i-name">icon-yahoo-messenger</span></li><li><i class="icon-yelp"></i><span class="i-name">icon-yelp</span></li><li><i class="icon-zerply"></i><span class="i-name">icon-zerply</span></li><li><i class="icon-zootool"></i><span class="i-name">icon-zootool</span></li><li><i class="icon-zynga"></i><span class="i-name">icon-zynga</span></li><li><i class="icon-align-center"></i><span class="i-name">icon-align-center</span></li><li><i class="icon-align-justify"></i><span class="i-name">icon-align-justify</span></li><li><i class="icon-align-left"></i><span class="i-name">icon-align-left</span></li><li><i class="icon-align-right"></i><span class="i-name">icon-align-right</span></li><li><i class="icon-archive-1"></i><span class="i-name">icon-archive-1</span></li><li><i class="icon-atom"></i><span class="i-name">icon-atom</span></li><li><i class="icon-bag"></i><span class="i-name">icon-bag</span></li><li><i class="icon-bank-notes"></i><span class="i-name">icon-bank-notes</span></li><li><i class="icon-barbell"></i><span class="i-name">icon-barbell</span></li><li><i class="icon-bars-1"></i><span class="i-name">icon-bars-1</span></li><li><i class="icon-battery-0"></i><span class="i-name">icon-battery-0</span></li><li><i class="icon-battery-1"></i><span class="i-name">icon-battery-1</span></li><li><i class="icon-battery-2"></i><span class="i-name">icon-battery-2</span></li><li><i class="icon-battery-3"></i><span class="i-name">icon-battery-3</span></li><li><i class="icon-battery-4"></i><span class="i-name">icon-battery-4</span></li><li><i class="icon-battery-power"></i><span class="i-name">icon-battery-power</span></li><li><i class="icon-beer"></i><span class="i-name">icon-beer</span></li><li><i class="icon-bolt-1"></i><span class="i-name">icon-bolt-1</span></li><li><i class="icon-bones"></i><span class="i-name">icon-bones</span></li><li><i class="icon-book-close"></i><span class="i-name">icon-book-close</span></li><li><i class="icon-book-open"></i><span class="i-name">icon-book-open</span></li><li><i class="icon-bookmark"></i><span class="i-name">icon-bookmark</span></li><li><i class="icon-box-1"></i><span class="i-name">icon-box-1</span></li><li><i class="icon-browser-1"></i><span class="i-name">icon-browser-1</span></li><li><i class="icon-bubble"></i><span class="i-name">icon-bubble</span></li><li><i class="icon-bubble-1"></i><span class="i-name">icon-bubble-1</span></li><li><i class="icon-bubble-2"></i><span class="i-name">icon-bubble-2</span></li><li><i class="icon-bubble-3"></i><span class="i-name">icon-bubble-3</span></li><li><i class="icon-bucket"></i><span class="i-name">icon-bucket</span></li><li><i class="icon-calculator-1"></i><span class="i-name">icon-calculator-1</span></li><li><i class="icon-calendar-2"></i><span class="i-name">icon-calendar-2</span></li><li><i class="icon-camera-3"></i><span class="i-name">icon-camera-3</span></li><li><i class="icon-cardiac-pulse"></i><span class="i-name">icon-cardiac-pulse</span></li><li><i class="icon-cd-2"></i><span class="i-name">icon-cd-2</span></li><li><i class="icon-character"></i><span class="i-name">icon-character</span></li><li><i class="icon-clipboard-1"></i><span class="i-name">icon-clipboard-1</span></li><li><i class="icon-clock-2"></i><span class="i-name">icon-clock-2</span></li><li><i class="icon-cloud-3"></i><span class="i-name">icon-cloud-3</span></li><li><i class="icon-coffee"></i><span class="i-name">icon-coffee</span></li><li><i class="icon-comment"></i><span class="i-name">icon-comment</span></li><li><i class="icon-connection-0"></i><span class="i-name">icon-connection-0</span></li><li><i class="icon-connection-1"></i><span class="i-name">icon-connection-1</span></li><li><i class="icon-connection-2"></i><span class="i-name">icon-connection-2</span></li><li><i class="icon-connection-3"></i><span class="i-name">icon-connection-3</span></li><li><i class="icon-connection-4"></i><span class="i-name">icon-connection-4</span></li><li><i class="icon-credit-cards"></i><span class="i-name">icon-credit-cards</span></li><li><i class="icon-crop"></i><span class="i-name">icon-crop</span></li><li><i class="icon-cube"></i><span class="i-name">icon-cube</span></li><li><i class="icon-diamond"></i><span class="i-name">icon-diamond</span></li><li><i class="icon-email"></i><span class="i-name">icon-email</span></li><li><i class="icon-email-plane"></i><span class="i-name">icon-email-plane</span></li><li><i class="icon-enter"></i><span class="i-name">icon-enter</span></li><li><i class="icon-eyedropper-1"></i><span class="i-name">icon-eyedropper-1</span></li><li><i class="icon-file"></i><span class="i-name">icon-file</span></li><li><i class="icon-file-add"></i><span class="i-name">icon-file-add</span></li><li><i class="icon-file-broken"></i><span class="i-name">icon-file-broken</span></li><li><i class="icon-file-settings"></i><span class="i-name">icon-file-settings</span></li><li><i class="icon-files"></i><span class="i-name">icon-files</span></li><li><i class="icon-flag"></i><span class="i-name">icon-flag</span></li><li><i class="icon-folder"></i><span class="i-name">icon-folder</span></li><li><i class="icon-folder-add"></i><span class="i-name">icon-folder-add</span></li><li><i class="icon-folder-check"></i><span class="i-name">icon-folder-check</span></li><li><i class="icon-folder-settings"></i><span class="i-name">icon-folder-settings</span></li><li><i class="icon-forbidden"></i><span class="i-name">icon-forbidden</span></li><li><i class="icon-frames"></i><span class="i-name">icon-frames</span></li><li><i class="icon-glass"></i><span class="i-name">icon-glass</span></li><li><i class="icon-graph"></i><span class="i-name">icon-graph</span></li><li><i class="icon-grid-1"></i><span class="i-name">icon-grid-1</span></li><li><i class="icon-heart-1"></i><span class="i-name">icon-heart-1</span></li><li><i class="icon-home-2"></i><span class="i-name">icon-home-2</span></li><li><i class="icon-invoice"></i><span class="i-name">icon-invoice</span></li><li><i class="icon-ipad-1"></i><span class="i-name">icon-ipad-1</span></li><li><i class="icon-ipad-2"></i><span class="i-name">icon-ipad-2</span></li><li><i class="icon-lab"></i><span class="i-name">icon-lab</span></li><li><i class="icon-laptop"></i><span class="i-name">icon-laptop</span></li><li><i class="icon-list-2"></i><span class="i-name">icon-list-2</span></li><li><i class="icon-lock"></i><span class="i-name">icon-lock</span></li><li><i class="icon-locked"></i><span class="i-name">icon-locked</span></li><li><i class="icon-map"></i><span class="i-name">icon-map</span></li><li><i class="icon-measure"></i><span class="i-name">icon-measure</span></li><li><i class="icon-meter"></i><span class="i-name">icon-meter</span></li><li><i class="icon-micro"></i><span class="i-name">icon-micro</span></li><li><i class="icon-micro-mute"></i><span class="i-name">icon-micro-mute</span></li><li><i class="icon-microwave"></i><span class="i-name">icon-microwave</span></li><li><i class="icon-modem"></i><span class="i-name">icon-modem</span></li><li><i class="icon-mute"></i><span class="i-name">icon-mute</span></li><li><i class="icon-newspaper-1"></i><span class="i-name">icon-newspaper-1</span></li><li><i class="icon-paperclip-2"></i><span class="i-name">icon-paperclip-2</span></li><li><i class="icon-pencil-1"></i><span class="i-name">icon-pencil-1</span></li><li><i class="icon-phone"></i><span class="i-name">icon-phone</span></li><li><i class="icon-phone-2"></i><span class="i-name">icon-phone-2</span></li><li><i class="icon-phone-3"></i><span class="i-name">icon-phone-3</span></li><li><i class="icon-picture"></i><span class="i-name">icon-picture</span></li><li><i class="icon-pie-chart"></i><span class="i-name">icon-pie-chart</span></li><li><i class="icon-pill"></i><span class="i-name">icon-pill</span></li><li><i class="icon-pin-2"></i><span class="i-name">icon-pin-2</span></li><li><i class="icon-power"></i><span class="i-name">icon-power</span></li><li><i class="icon-printer-1"></i><span class="i-name">icon-printer-1</span></li><li><i class="icon-printer-2"></i><span class="i-name">icon-printer-2</span></li><li><i class="icon-refresh"></i><span class="i-name">icon-refresh</span></li><li><i class="icon-reload-1"></i><span class="i-name">icon-reload-1</span></li><li><i class="icon-screen-1"></i><span class="i-name">icon-screen-1</span></li><li><i class="icon-select"></i><span class="i-name">icon-select</span></li><li><i class="icon-set"></i><span class="i-name">icon-set</span></li><li><i class="icon-settings-1"></i><span class="i-name">icon-settings-1</span></li><li><i class="icon-shorts"></i><span class="i-name">icon-shorts</span></li><li><i class="icon-speaker"></i><span class="i-name">icon-speaker</span></li><li><i class="icon-star-1"></i><span class="i-name">icon-star-1</span></li><li><i class="icon-stopwatch"></i><span class="i-name">icon-stopwatch</span></li><li><i class="icon-sun"></i><span class="i-name">icon-sun</span></li><li><i class="icon-syringe"></i><span class="i-name">icon-syringe</span></li><li><i class="icon-tag"></i><span class="i-name">icon-tag</span></li><li><i class="icon-train-1"></i><span class="i-name">icon-train-1</span></li><li><i class="icon-trash-1"></i><span class="i-name">icon-trash-1</span></li><li><i class="icon-unlocked"></i><span class="i-name">icon-unlocked</span></li><li><i class="icon-volume-1"></i><span class="i-name">icon-volume-1</span></li><li><i class="icon-volume-down"></i><span class="i-name">icon-volume-down</span></li><li><i class="icon-volume-up"></i><span class="i-name">icon-volume-up</span></li><li><i class="icon-wifi-1"></i><span class="i-name">icon-wifi-1</span></li><li><i class="icon-wifi-2"></i><span class="i-name">icon-wifi-2</span></li><li><i class="icon-wifi-3"></i><span class="i-name">icon-wifi-3</span></li><li><i class="icon-window-delete"></i><span class="i-name">icon-window-delete</span></li><li><i class="icon-windows-2"></i><span class="i-name">icon-windows-2</span></li><li><i class="icon-zoom-in-1"></i><span class="i-name">icon-zoom-in-1</span></li><li><i class="icon-magnifying-glass-1"></i><span class="i-name">icon-magnifying-glass-1</span></li><li><i class="icon-search-2"></i><span class="i-name">icon-search-2</span></li><li><i class="icon-fontawesome-webfont-2"></i><span class="i-name">icon-fontawesome-webfont-2</span></li><li><i class="icon-envelope-1"></i><span class="i-name">icon-envelope-1</span></li><li><i class="icon-download-3"></i><span class="i-name">icon-download-3</span></li><li><i class="icon-upload-3"></i><span class="i-name">icon-upload-3</span></li><li><i class="icon-stumbleupon-1"></i><span class="i-name">icon-stumbleupon-1</span></li><li><i class="icon-user"></i><span class="i-name">icon-user</span></li><li><i class="icon-users"></i><span class="i-name">icon-users</span></li><li><i class="icon-fontawesome-webfont-3"></i><span class="i-name">icon-fontawesome-webfont-3</span></li><li><i class="icon-fontawesome-webfont-4"></i><span class="i-name">icon-fontawesome-webfont-4</span></li><li><i class="icon-fontawesome-webfont-5"></i><span class="i-name">icon-fontawesome-webfont-5</span></li><li><i class="icon-fontawesome-webfont-6"></i><span class="i-name">icon-fontawesome-webfont-6</span></li><li><i class="icon-fontawesome-webfont-7"></i><span class="i-name">icon-fontawesome-webfont-7</span></li><li><i class="icon-fontawesome-webfont-8"></i><span class="i-name">icon-fontawesome-webfont-8</span></li><li><i class="icon-fontawesome-webfont-9"></i><span class="i-name">icon-fontawesome-webfont-9</span></li><li><i class="icon-fontawesome-webfont-10"></i><span class="i-name">icon-fontawesome-webfont-10</span></li><li><i class="icon-fontawesome-webfont-11"></i><span class="i-name">icon-fontawesome-webfont-11</span></li><li><i class="icon-fontawesome-webfont-12"></i><span class="i-name">icon-fontawesome-webfont-12</span></li><li><i class="icon-fontawesome-webfont-13"></i><span class="i-name">icon-fontawesome-webfont-13</span></li><li><i class="icon-fontawesome-webfont-14"></i><span class="i-name">icon-fontawesome-webfont-14</span></li><li><i class="icon-fontawesome-webfont-15"></i><span class="i-name">icon-fontawesome-webfont-15</span></li><li><i class="icon-fontawesome-webfont-16"></i><span class="i-name">icon-fontawesome-webfont-16</span></li><li><i class="icon-bookmark-1"></i><span class="i-name">icon-bookmark-1</span></li><li><i class="icon-book-open-1"></i><span class="i-name">icon-book-open-1</span></li><li><i class="icon-flash"></i><span class="i-name">icon-flash</span></li><li><i class="icon-feather-1"></i><span class="i-name">icon-feather-1</span></li><li><i class="icon-flag-1"></i><span class="i-name">icon-flag-1</span></li><li><i class="icon-google-circles"></i><span class="i-name">icon-google-circles</span></li><li><i class="icon-heart-2"></i><span class="i-name">icon-heart-2</span></li><li><i class="icon-heart-empty"></i><span class="i-name">icon-heart-empty</span></li><li><i class="icon-right-open-big"></i><span class="i-name">icon-right-open-big</span></li><li><i class="icon-left-open-big"></i><span class="i-name">icon-left-open-big</span></li><li><i class="icon-up-open-big"></i><span class="i-name">icon-up-open-big</span></li><li><i class="icon-down-open-big"></i><span class="i-name">icon-down-open-big</span></li><li><i class="icon-symbol-woman"></i><span class="i-name">icon-symbol-woman</span></li><li><i class="icon-measure-1"></i><span class="i-name">icon-measure-1</span></li><li><i class="icon-symbol-mixed"></i><span class="i-name">icon-symbol-mixed</span></li><li><i class="icon-letter"></i><span class="i-name">icon-letter</span></li><li><i class="icon-fontawesome-webfont-17"></i><span class="i-name">icon-fontawesome-webfont-17</span></li><li><i class="icon-fontawesome-webfont-18"></i><span class="i-name">icon-fontawesome-webfont-18</span></li><li><i class="icon-fontawesome-webfont-19"></i><span class="i-name">icon-fontawesome-webfont-19</span></li><li><i class="icon-arrow-1-down"></i><span class="i-name">icon-arrow-1-down</span></li><li><i class="icon-arrow-up-1"></i><span class="i-name">icon-arrow-up-1</span></li><li><i class="icon-fork"></i><span class="i-name">icon-fork</span></li><li><i class="icon-curved-arrow"></i><span class="i-name">icon-curved-arrow</span></li><li><i class="icon-forward-2"></i><span class="i-name">icon-forward-2</span></li><li><i class="icon-reload-2"></i><span class="i-name">icon-reload-2</span></li><li><i class="icon-arrows-out"></i><span class="i-name">icon-arrows-out</span></li><li><i class="icon-arrows-expand"></i><span class="i-name">icon-arrows-expand</span></li><li><i class="icon-arrows-compress"></i><span class="i-name">icon-arrows-compress</span></li><li><i class="icon-arrows-in"></i><span class="i-name">icon-arrows-in</span></li><li><i class="icon-zoom-out"></i><span class="i-name">icon-zoom-out</span></li><li><i class="icon-coverflow"></i><span class="i-name">icon-coverflow</span></li><li><i class="icon-coverflow-line"></i><span class="i-name">icon-coverflow-line</span></li></ul>';
						   if($meta == '' || !isset($meta)){$meta=$std;}
						   	echo '<input type="text" class="capture-input vibe-form-text vibe-input" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $meta ) . '" />' . "\n";
							echo $desc;
						break;
                                                // color
                        case 'color':
							echo '<input type="text" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $meta ) . '" size="10" class="color" />
									<br />' . $desc;
						break;  
						case 'checkbox':
                                                       if(!isset($std))$std=0;
                                                        if(!isset($meta)){$meta=$std;}
							echo '<div class="checkbox_button"></div>
                                                              <input type="checkbox" name="' . $id . '" id="' . $id . '" ' . checked( esc_attr( $meta ), 1, 0 ) . ' class="checkbox_val" value="1" />
								<label for="' . $id . '">' . $desc . '</label>';
						break;
						case 'showhide':
						case 'yesno':

							echo '<div class="select_button yesno">';
							foreach ( $options as $key => $option ){
								echo '<span>'.$option['label'].'</span>';
							}
							echo '</div>';
                            echo '<select name="' . $id . '" id="' . $id . '" class="select_val">';
                            
                            if($meta == '' || !isset($meta)){$meta=$std;}

							foreach ( $options as $key => $option ){
								echo '<option ' . selected( esc_attr( $meta ), $option['value'], false ) . ' value="' .$option['value'] . '">' .  $option['label'] . '</option>';
							}
							echo '</select><br />' . $desc;
						break;
						// select
						case 'select':
							echo '<select name="' . $id . '" id="' . $id . '" class="select">';
                            if($meta == '' || !isset($meta)){$meta=$std;}
							foreach ( $options as $option )
								echo '<option' . selected( esc_attr( $meta ), $option['value'], false ) . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
							echo '</select><br />' . $desc;
						break;
						// select
						case 'selectcpt':
							echo '<select name="' . $id . '" id="' . $id . '" data-id="'.$post->ID.'" data-placeholder="'.sprintf(__('Select a %s','vibe-customtypes'),$post_type).'" data-cpt="'.$post_type.'" class="selectcpt">';

                            if($meta == '' || !isset($meta)){$meta=$std;}

							if(!empty($meta)){
								echo '<option value="' . $meta . '" selected="selected">' . get_the_title($meta) . '</option>';
							}
							echo '</select><br />' . $desc;
						break;
						
						case 'selectmulticpt': 
							echo '<select name="' . $id . '[]" id="' . $id . '" data-id="'.$post->ID.'" class="selectcpt" data-cpt="'.$post_type.'" data-placeholder="'.sprintf(__('Select multiple %s','vibe-customtypes'),$post_type).'" multiple>';
                            if($meta == '' || !isset($meta)){$meta=$std;}
                            if(is_array($meta)){
                            	foreach($meta as $id){
                            		echo '<option value="' . $id . '" selected="selected">' . get_the_title($id) . '</option>';		
                            	}
                            }
							echo '</select><br />' . $desc;
						break;
                       	// Multiselect
						case 'multiselect':
							echo '<select name="' . $id . '[]" id="' . $id . '" multiple class="select2-select">';
                                                        if($meta == '' || !isset($meta)){$meta=array();}
							if(isset($options) && is_array($options))                                                        
							foreach ( $options as $option )
								echo '<option value="' . $option['value'] . '" '.(in_array($option['value'],$meta)?'SELECTED':'').'>' . $option['label'] . '</option>';
							echo '</select><br />' . $desc;
						break;
						// radio
						case 'radio':
							foreach ( $options as $option )
								echo '<input type="radio" name="' . $id . '" id="' . $id . '-' . $option['value'] . '" value="' . $option['value'] . '" ' . checked( esc_attr( $meta ), $option['value'], false ) . ' />
										<label for="' . $id . '-' . $option['value'] . '">' . $option['label'] . '</label><br />';
							echo '' . $desc;
						break;
                        case 'radio_img': 
                            if($meta == '' || !isset($meta)){$meta=$std;}
							foreach ( $options as $option )
								echo '<div class="radio-image-wrapper">
                                                                        <label for="' . $option['value'] . '">
                                                                            <img src="'.$option['image'].'">
                                                                            <div class="select '.((esc_attr( $meta ) == $option['value'])?"selected":"").'"></div>
                                                                        </label>
                                                                        <input type="radio" class="radio_img" name="' . $id . '" id="' . $id . '-' . $option['value'] . '" value="' . $option['value'] . '" ' . checked( esc_attr( $meta ), $option['value'], false ) . ' />
                                                                     </div>';
							echo '' . $desc;
						break;
						// checkbox_group
						case 'checkbox_group':
							foreach ( $options as $option )
								echo '<input type="checkbox" value="' . $option['value'] . '" name="' . $id . '[]" id="' . $id . '-' . $option['value'] . '"' , is_array( $meta ) && in_array( $option['value'], $meta ) ? ' checked="checked"' : '' , ' /> 
										<label for="' . $id . '-' . $option['value'] . '">' . $option['label'] . '</label><br />';
							echo '' . $desc;
						break;
						case 'date':
							echo '<input type="text" class="datepicker" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $meta ) . '" size="30" />
									<br />' . $desc;
						break;
						case 'time':
							echo '<input type="text" class="timepicker" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $meta ) . '" size="30" />
									<br />' . $desc;
						break;
						case 'gmap':
                            if(is_admin())
                    			wp_enqueue_script( 'meta_box-gmap','http://maps.google.com/maps/api/js?sensor=false');            
                                                   
                                                    $city = $state = 'New York';$country = 'United States'; $pincode='22005';$staddress ='';
                                                       if(isset($meta)){
                                                           if(isset($meta['latitude']))
                                                              $lat =  esc_attr( $meta['latitude'] );
                                                           if(isset($meta['latitude']))     
                                                               $long =  esc_attr( $meta['longitude'] );
                                                           if(isset($meta['staddress']))     
                                                               $staddress =  esc_attr( $meta['staddress'] );
                                                           if(isset($meta['city']))     
                                                               $city =  esc_attr( $meta['city'] );
                                                           if(isset($meta['state']))     
                                                               $state =  esc_attr( $meta['state'] );
                                                           if(isset($meta['pincode']))     
                                                               $pincode =  esc_attr( $meta['pincode'] );
                                                           if(isset($meta['country']))     
                                                               $country =  esc_attr( $meta['country'] );
                                                           
                                                       }
							echo '<div id="mapCanvas"></div>
                                                                    <div id="infoPanel">
                                                                    <h4>Current position:</h4>
                                                                    <div class="markerStatus"></div>
                                                                                <label  style="display:block;width:200px;float:left;">'.__('Latitude','vibe-customtypes').'</label><input type="text" class="text" id="latitude" name="' . $field['id'] . '[latitude] value="' . $lat . '" size="20"  />
                                                                                <label  style="display:block;width:200px;float:left;">'.__('Longitude','vibe-customtypes').'</label><input type="text" class="text" id="longitude" name="' . $field['id'] . '[longitude]" value="' . $long . '" size="20"  />     
                                                                                <br /><b  style="width:200px;float:left;">'.__('Closest Matching Address','vibe-customtypes').'</b>
                                                                                <div id="address"></div>    
                                                                                <br />
                                                                                <label style="width:200px;float:left;">'.__('Street Address','vibe-customtypes').'</label><input type="text" class="text" id="staddress" name="' . $field['id'] . '[staddress]" value="' . $staddress . '" size="20"  />     <br />
                                                                                <label style="width:200px;float:left;">'.__('City','vibe-customtypes').'</label><input type="text" class="text" id="city" name="' . $field['id'] . '[city]" value="' . $city . '" size="20"  />     <br />
                                                                                <label style="width:200px;float:left;">'.__('State','vibe-customtypes').'</label><input type="text" class="text" id="state" name="' . $field['id'] . '[state]" value="' . $state . '" size="20"  />     <br />
                                                                                <label style="width:200px;float:left;">'.__('Zip/Pin Code','vibe-customtypes').'</label><input type="text" class="text" id="pincode" name="' . $field['id'] . '[pincode]" value="' . $pincode . '" size="20"  />     <br />
                                                                                <label style="width:200px;float:left;">'.__('Country','vibe-customtypes').'</label><input type="text" class="text" id="country" name="' . $field['id'] . '[country]" value="' . $country . '" size="20"  />         <br />
                                                                              </div>
									<br />' . $desc;
						break;
						case 'groups':

						if(class_exists('BP_Groups_Group')){
							
							echo '<select name="' . $id . '" id="' . $id . '" class="selectgroup" data-placeholder="'.__('Select a group','vibe-customtypes').'">';
							
							if(!empty($meta)){
								$group = groups_get_group( array( 'group_id' => $meta ) );
								echo '<option value="'.$meta.'" selected="SELECTED">'.$group->name.'</option>';
							}	

							echo '</select>';
						}else{
							_e('Buddypress Groups is not Active','vibe-customtypes');
						}
						
						
						echo '<br />' . $desc;
						break;
						case 'selectmultigroups':
						    if(class_exists('BP_Groups_Group')){
						        echo '<select name="' . $id . '[]" id="' . $id . '" class="select select2-select" data-placeholder="'.__('Select groups','vibe-customtypes').'" multiple>';
						        if($meta == '' || !isset($meta)){$meta=$std;}
						        $vgroups =  groups_get_groups(array(
						        'type'=>'alphabetical',
						        'per_page'=>999,
						        'show_hidden'=>true
						        ));
						        foreach($vgroups['groups'] as $vgroup){
						            if(is_array($meta))
						                echo '<option  '.(in_array($vgroup->id,$meta)?'SELECTED':'').' value="' . $vgroup->id . '">' . $vgroup->name . '</option>';
						            else
						                echo '<option value="' . $vgroup->id . '" '.selected($vgroup->id,$meta).'>' . $vgroup->name . '</option>';
						        }
						        echo '</select>';
						    }else{
						        _e('Buddypress Groups is not Active','vibe-customtypes');
						    }
						    echo '<br />' . $desc;
						break; 
						case 'curriculum':

							echo '<a class="meta_box_add_section button-primary button-large" href="#">'.__('Add Section','vibe-customtypes').'</a>
									<a class="meta_box_add_posttype1 button-primary button-large" href="#">Add '.$post_type1.'</a>
									<a class="meta_box_add_posttype2 button button-primary button-large" href="#">Add '.$post_type2.'</a>
									<ul id="' . $field['id'] . '-repeatable" class="meta_box_repeatable">';
							$i = 0;
							if ( $meta ) {
								foreach( $meta as $row ) {
									echo '<li><span class="sort handle dashicons dashicons-sort"></span>
												 <input type="text" name="' . $field['id'] . '[' . $i . ']" id="' . $field['id'] . '" class="'.(is_numeric($row)?'small postid':'').'" value="' . esc_attr( $row ) . '" size="30" READONLY /> <a href="'.get_edit_post_link($row).'"><span>'.(is_numeric($row)?get_the_title($row):'').'</span></a>
												<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
									$i++;
								}
							} 
							echo '<li class="section hide"><span class="sort handle dashicons dashicons-sort"></span>
										<input type="text" rel-name="' . $field['id'] . '[]" id="' . $field['id'] . '" value="" size="30" />
										<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';

							echo '<li class="posttype1 hide">
									<select rel-name="' . $field['id'] . '[]"  data-id="'.$post->ID.'" class="" data-cpt="'. $post_type1.'" data-placeholder="'.sprintf(__('Select a %s','vibe-customtypes'),$post_type1).'">
									</select>';
							echo '<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';											
							echo '<li class="posttype2 hide">
									<select rel-name="' . $field['id'] . '[]" class=""  data-id="'.$post->ID.'" data-cpt="'. $post_type2.'" data-placeholder="'.sprintf(__('Select a %s','vibe-customtypes'),$post_type2).'">
									</select>
									<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';											
							echo '</ul>
								<span class="description">' . $field['desc'] . '</span>';
						break;
                                            
                        case 'tax_select':
							echo '<select name="' . $id . '" id="' . $id . '" class="select2-select">
									<option value="">'.__('Select ','vibe-customtypes').'</option>'; // Select One
							$terms = get_terms( $id, 'get=all' );
							$selected = wp_get_object_terms( $post->ID, $id );
							foreach ( $terms as $term ) 
									echo '<option value="' . $term->slug . '"' . selected( $selected[0]->slug, $term->slug, false ) . '>' . $term->name . '</option>'; 
							$taxonomy = get_taxonomy( $id);
							echo '</select> &nbsp;<span class="description"><a href="' . home_url() . '/wp-admin/edit-tags.php?taxonomy=' . $id . '&post_type=' . $post_type . '">Manage ' . $taxonomy->label . '</a></span>
								<br />' . $desc;
						break;
						case 'dynamic_taxonomy':
							echo '<select name="' . $id . '[]" id="' . $id . '" multiple class="select2-select">
									<option value="">'.__('Select Taxonomy','vibe-customtypes').'</option>'; // Select One
							$terms = get_terms( $taxonomy, array('fields' => 'id=>name') );
							if($meta == '' || !isset($meta)){$meta=array();}

							if(isset($terms) && is_array($terms))                                                        
							foreach ($terms as $key=>$term )
								echo '<option value="' . $key . '" '.(in_array($key,$meta)?'SELECTED':'').'>' . $term . '</option>';
							echo '</select><br />' . $desc;
						break;
						case 'dynamic_quiz_questions':
							if($meta == '' || !isset($meta)){$meta=array();}
							$terms = get_terms($taxonomy);
							$terms_array  = array();
							if(!empty($terms)){
								foreach($terms as $term){
									$terms_array[$term->term_id] = array('id'=>$term->term_id,'name'=>$term->name,'count'=>$term->count);
								}
							}
							echo '<a class="meta_box_question_tags_add button button-primary button-large" href="#">'.__('Add More','vibe-customtypes').'</a>
									<ul id="' . $field['id'] . '-repeatable" class="meta_box_repeatable">';
							$total_marks=0;

							if ( $meta ) {
								if(!empty($meta)){
									if(!isset($meta['tags'])){
										$newmeta = array();
										$newmeta['tags'][] = $meta;
										$newmeta['number'][] = get_post_meta($post->ID,'vibe_quiz_number_questions',true);
										$newmeta['marks'][] = get_post_meta($post->ID,'vibe_quiz_marks_per_question',true);
										$meta = $newmeta;
									}
								}
								if(!empty($meta['tags']) && !empty($meta['numbers'])){
									$tags = $meta['tags'];
									$numbers = $meta['numbers'];
									$marks = $meta['marks'];
									foreach( $tags as $i=>$tag ) {

										if(is_string($tag))
											$tag = unserialize($tag);

										if(!isset($numbers[$i]) || !$numbers[$i]) $numbers[$i]=0;

										if(!isset($marks[$i]) || !$marks[$i]){
											$marks[$i]=get_post_meta($post->ID,'vibe_quiz_marks_per_question',true);
											if(empty($marks[$i])){$marks[$i]=0;}
										} 
										$total_number = $total_number+intval(esc_attr( $numbers[$i] ));
										$total_marks = $total_marks+intval(esc_attr( $marks[$i] ))*intval(esc_attr( $numbers[$i] ));
										$tags_string ='';

										if(is_array($tag) && !empty($terms_array)){
											foreach($tag as $t){
												$tags_string .= $terms_array[$t]['name'].' ('.$terms_array[$t]['count'].')&nbsp;,&nbsp;'	;
											}
										}else{
											$tags_string .= $terms_array[$tag]['name'].' ('.$terms_array[$tag]['count'].')&nbsp;,&nbsp;'	;
										}
										
										echo '<li><span class="sort handle dashicons dashicons-sort"></span>';

										if(is_array($tag)){
											foreach($tag as $tid => $t){
												echo '<input type="hidden" name="' . $field['id'] . '[tags]['.$i.'][]" value="'.(isset($t['id'])?$t['id']:(is_numeric($t)?$t:'')).'" />';	
											}
										}else{
											echo '<input type="hidden" name="' . $field['id'] . '[tags]['.$i.'][]" value="'. $tag.'" />';
										}
										echo '<strong>'.$tags_string. '</strong>
											  	<input type="number" name="' . $field['id'] . '[numbers]['.$i.']" placeholder="'.__('Number of questions to pull','vibe-customtypes').'" class="count" value="'.esc_attr( $numbers[$i] ).'"/>
											  	<input type="number" name="' . $field['id'] . '[marks]['.$i.']" placeholder="'.__('Marks for pulled questions','vibe-customtypes').'" class="marks" value="'.esc_attr( $marks[$i] ).'"/>
											  	<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a>
												</li>';
									}
								}
							} 
							echo '<li class="hide">
									<select rel-name="' . $field['id'] . '[tags]" multiple data-placeholder="'.__('Select Tag','vibe-customtypes').'">';
									if(!empty($terms_array)){
										foreach($terms_array as $term){
											echo '<option value="'.$term['id'].'">'.$term['name'].' ('.$term['count'].')</option>';
										}
									}
								  echo '</select>
								 <input type="number" class="count" rel-name="' . $field['id'] . '[numbers]" placeholder="'.__('Number of questions to pull','vibe-customtypes').'" value="0" />
								 <input type="number" class="marks" rel-name="' . $field['id'] . '[marks]" placeholder="'.__('Marks per question','vibe-customtypes').'" value="0" /> 
								 <a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
							echo '</ul>

							    <strong>'.__('Total Question Count :','vibe-customtypes').' <span id="total_question_number"> '.(empty($total_number)?0:$total_number).'</span> , '.__('Total Marks :','vibe-customtypes').' <span id="total_question_marks"> '.$total_marks.'</span></strong><br />
								<span class="description">' . $field['desc'] . '</span>';
						break;
						// slider
						case 'slider':
						$value = $meta != '' ? intval( $meta ) : '0';
							echo '<div id="' . $id . '-slider"></div>
									<input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" size="5" />
									<br />' . $desc;
						break;

						// image
						case 'image':
							$image = VIBE_PLUGIN_URL.'/vibe-customtypes/metaboxes/images/image.png';	
							echo '<span class="meta_box_default_image" style="display:none">' . $image . '</span>';
                            if ( $meta ) {
								$image = wp_get_attachment_image_src( intval( $meta ), 'full' );
								$image = $image[0];
							}else
                                $meta='';
							echo	'<input name="' . $id . '" id="'.$id.'" type="hidden" class="meta_box_upload_image" value="' . intval( $meta ) . '" />
								<img src="' . $image . '" id="image_'.$id.'" class="meta_box_preview_image" alt="" /><br />
								<input class="meta_box_upload_image_button button" type="button" rel="' . $post->ID . '" data-title="'.$label.'" data-save="#'.$id.'" data-target="#image_'.$id.'" value="'.__('Choose Image','vibe-customtypes').'" />
								<small>&nbsp;<a href="#" class="meta_box_clear_image_button">'.__('Remove Image','vibe-customtypes').'</a></small>
								<br clear="all" />' . $desc;
						break;
						// repeatable
						case 'questions_repeatable':
							echo '<a class="meta_box_repeatable_add button button-primary button-large" href="#">'.__('Add Question','vibe-customtypes').'</a>
									<ul id="' . $field['id'] . '-repeatable" class="meta_box_repeatable">';
							$i = 0;
							if ( $meta ) {
								foreach( $meta as $row ) {
									$user_info = get_userdata($row);
									echo '<li><span class="sort handle dashicons dashicons-sort"></span>
												<input type="text" name="' . $field['id'] . '[]" id="' . $field['id'] . $i .'" value="' . esc_attr( $row ) . '" size="30" /><span>'. $user_info->user_login.'</span>
												<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
									$i++;
								}
							} 
								echo '<li class="hide"><span class="sort handle dashicons dashicons-sort"></span>
										<input type="text" rel-name="' . $field['id'] . '[question][]" id="' . $field['id'] .$i .'" value="" placeholder="'.__('Type Question','vibe-customtypes').'" size="30" />
										<input type="text" rel-name="' . $field['id'] . '[option][]" id="' . $field['id'] .$i .'" value="" placeholder="'.__('Type Question','vibe-customtypes').'" size="30" />
										<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
							
							echo '</ul>
								<span class="description">' . $field['desc'] . '</span>';
						break;
						case 'user_repeatable':
							echo '<a class="meta_box_repeatable_add button button-primary button-large" href="#">'.__('Add More','vibe-customtypes').'</a>
									<ul id="' . $field['id'] . '-repeatable" class="meta_box_repeatable">';
							$i = 0;
							if ( $meta ) {
								foreach( $meta as $row ) {
									$user_info = get_userdata($row);
									echo '<li><span class="sort handle dashicons dashicons-sort"></span>
												<input type="text" name="' . $field['id'] . '[]" id="' . $field['id'] . $i .'" value="' . esc_attr( $row ) . '" size="30" /><span>'. $user_info->user_login.'</span>
												<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
									$i++;
								}
							} 
								echo '<li class="hide"><span class="sort handle dashicons dashicons-sort"></span>
											<input type="text" rel-name="' . $field['id'] . '[]" id="' . $field['id'] .$i .'" value="" size="30" />
											<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
							
							echo '</ul>
								<span class="description">' . $field['desc'] . '</span>';
						break;

						case 'repeatable':
							echo '<a class="meta_box_repeatable_add button button-primary button-large" href="#">'.__('Add More','vibe-customtypes').'</a>
									<ul id="' . $field['id'] . '-repeatable" class="meta_box_repeatable">';
	
							if ( $meta ) {
								foreach( $meta as $row ) {
									echo '<li><span class="sort handle dashicons dashicons-sort"></span>
												<input type="text" name="' . $field['id'] . '[]" id="' . $field['id'] . '" value="' . esc_attr( $row ) . '" size="30" />
												<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
								}
							} 
								echo '<li class="hide"><span class="sort handle dashicons dashicons-sort"></span>
											<input type="text" rel-name="' . $field['id'] . '[]" id="' . $field['id'] . '" value="" size="30" />
											<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
							
							echo '</ul>
								<span class="description">' . $field['desc'] . '</span>';
						break;
						case 'repeatable_count':
							echo '<a class="meta_box_repeatable_add button button-primary button-large" href="#">'.__('Add More','vibe-customtypes').'</a>
									<ul id="' . $field['id'] . '-repeatable" class="meta_box_repeatable">';
							$i=1;
							
								echo '<li class="hide"><span class="sort handle dashicons dashicons-sort"></span><span class="count">'.$i.'</span>
											<input type="text" rel-name="' . $field['id'] . '[]" id="' . $field['id'] . '" value="" size="30" />
											<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
							
							if ( !empty($meta) && is_array($meta) ) {
								foreach( $meta as $row ) {
									echo '<li><span class="sort handle dashicons dashicons-sort"></span><span class="count">'.$i.'</span>
												<input type="text" name="' . $field['id'] . '[]" id="' . $field['id'] . '" value="' . esc_attr( $row ) . '" size="30" />
												<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
								$i++;}
							} 
							echo '</ul>
								<span class="description">' . $field['desc'] . '</span>';
						break;
						// repeatable
						case 'repeatable_select':
							echo '<a class="meta_box_repeatable_add button button-primary button-large" href="#">'.__('Add More','vibe-customtypes').'</a>
									<ul id="' . $field['id'] . '-repeatable" class="meta_box_repeatable">';
							$i = 0;
							if ( $meta ) {

								foreach( $meta as $row ) {

									echo '<li><span class="sort handle dashicons dashicons-sort"></span>
											  <select name="' . $field['id'] . '[' . $i . ']" id="' . $field['id'] . '">';
											  foreach ( $options as $option )
													echo '<option value="' . $option['value'] . '" '.selected($option['value'],esc_attr( $row )).'>' . $option['label'] . '</option>';

											  echo '</select>
												<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a>
												</li>';
									$i++;
								}
							} 
								echo '<li  class="hide"><span class="sort handle dashicons dashicons-sort"></span>
											<select name="' . $field['id'] . '[' . $i . ']" id="' . $field['id'] . '">';
											  foreach ( $options as $option )
													echo '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';

											  echo '</select>
											<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
							
							echo '</ul>
								<span class="description">' . $field['desc'] . '</span>';
						break;
						case 'repeatable_selectcpt':
							echo '<a class="meta_box_repeatable_add button button-primary button-large" href="#">'.__('Add More','vibe-customtypes').'</a>
									<ul id="' . $field['id'] . '-repeatable" class="meta_box_repeatable">';
							$total_marks=0;
							if ( $meta ) {
								
								if(!empty($meta['ques']) && !empty($meta['marks'])){
									$quess = $meta['ques'];
									$marks = $meta['marks'];
									
									foreach( $quess as $i => $ques ) {
										if(!isset($marks[$i]) || !$marks[$i]) $marks[$i]=0;
										$total_marks = $total_marks+intval(esc_attr( $marks[$i] ));
										echo '<li><span class="sort handle dashicons dashicons-sort"></span>
												<input type="hidden" name="' . $field['id'] . '[ques][]" value="'. $ques.'" />
												<strong>'.get_the_title($ques). '</strong>
											  	<input type="number" name="' . $field['id'] . '[marks][]" placeholder="'.__('Marks for a Correct answer','vibe-customtypes').'" value="'.esc_attr( $marks[$i] ).'"/>
											  	<a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a>
												</li>';
									}
								}
							} 
							echo '<li class="hide">
									<select rel-name="' . $field['id'] . '[ques][]"  data-id="'.$post->ID.'" data-cpt="'.$post_type.'" data-placeholder="'.__('Select','vibe-customtypes').'">';
								  echo '</select>
								 <input type="number" rel-name="' . $field['id'] . '[marks][]" placeholder="'.__('Marks for a Correct answer','vibe-customtypes').'" value="0" /> 
								 <a class="meta_box_repeatable_remove" href="#"><span class="dashicons dashicons-no"></span></a></li>';
							echo '</ul>

							    <strong>'.__('Total marks for the Quiz :','vibe-customtypes').' <span id="total_quiz_marks"> '.$total_marks.'</span></strong><br />
								<span class="description">' . $field['desc'] . '</span>';
						break;
						case 'payments':
						echo '<ul id="instructor_payments"><li><strong>'.__('Instructor','vibe-customtypes').'</strong><span>'.__('Email','vibe-customtypes').'</span><span>'.__('Commission','vibe-customtypes').'</span></li>';
						if(is_array($meta))
						foreach($meta as $key=>$row){
							if(isset($row['set']) && $row['set'])
								echo '<li><strong>'.get_the_author_meta('display_name',$key).'</strong><span>'.$row['email'].'</span><span>'.$row['commission'].'</span></li>';
						}
						echo '</ul>';
						break;
                        case 'gallery':
                            global $post;
                        ?>
                        <div id="vibe_gallery_container">
                        <ul class="vibe_gallery">
						<?php
                        if(!$meta || $meta == 'Array') $meta = '';
                        if($meta){
						$attachments = array_filter( explode( ',', $meta ) );
						if ( is_array($attachments ) && $attachments)
							foreach ( $attachments as $attachment_id ) {
								echo '<li class="slider_image" data-attachment_id="' . $attachment_id . '">
									' . wp_get_attachment_image( $attachment_id, 'full' ) . '
									<ul class="actions">
										<li><a href="#" class="delete" title="' . __( 'Delete image', 'vibe-customtypes' ) . '">' . __( 'Delete', 'vibe-customtypes' ) . '</a></li>
									</ul>
								</li>';
							}
                        }
					?>
				</ul>
	            <?php
                echo '<input type="hidden" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $meta ) . '" />';
                ?>
		

	</div>
	<p class="add_gallery hide-if-no-js">
		<a href="#" class="button-primary"><?php _e( 'Add Gallery images', 'vibe-customtypes' ); ?></a>
	</p>
	<script type="text/javascript">
		jQuery(document).ready(function($){

			// Uploading files
			var media_frame;
			var $image_gallery_ids = $('#<?php echo $id;?>');
			var $media = $('#vibe_gallery_container ul.vibe_gallery');

			jQuery('.add_gallery').on( 'click', 'a', function( event ) {

				var $el = $(this);
				var attachment_ids = $image_gallery_ids.val();

				event.preventDefault();

				// If the media frame already exists, reopen it.
				if ( media_frame ) {
					media_frame.open();
					return;
				}

				// Create the media frame.
				media_frame = wp.media.frames.downloadable_file = wp.media({
					// Set the title of the modal.
					title: '<?php _e( 'Add Images to Gallery', 'vibe-customtypes' ); ?>',
					button: {
						text: '<?php _e( 'Add to Gallery', 'vibe-customtypes' ); ?>',
					},
					multiple: true
				});

				// When an image is selected, run a callback.
				media_frame.on( 'select', function() {

					var selection = media_frame.state().get('selection');

					selection.map( function( attachment ) {

						attachment = attachment.toJSON();

						if ( attachment.id ) {
							attachment_ids = attachment_ids ? attachment_ids + "," + attachment.id : attachment.id;

							$media.append('\
								<li class="slider_image" data-attachment_id="' + attachment.id + '">\
									<img src="' + attachment.url + '" />\
									<ul class="actions">\
										<li><a href="#" class="delete" title="<?php _e( 'Delete', 'vibe-customtypes' ); ?>"><?php _e( 'Delete', 'vibe-customtypes' ); ?></a></li>\
									</ul>\
								</li>');
						}

					} );

					$image_gallery_ids.val( attachment_ids );
				});

				// Finally, open the modal.
				media_frame.open();
			});

			// Image ordering
			$media.sortable({
				items: 'li.slider_image',
				cursor: 'move',
				scrollSensitivity:40,
				forcePlaceholderSize: true,
				forceHelperSize: false,
				helper: 'clone',
				opacity: 0.65,
				placeholder: 'wc-metabox-sortable-placeholder',
				start:function(event,ui){
					ui.item.css('background-color','#f6f6f6');
				},
				stop:function(event,ui){
					ui.item.removeAttr('style');
				},
				update: function(event, ui) {
					var attachment_ids = '';

					$('#vibe_media_container ul li.image').css('cursor','default').each(function() {
						var attachment_id = jQuery(this).attr( 'data-attachment_id' );
						attachment_ids = attachment_ids + attachment_id + ',';
					});

					$image_gallery_ids.val( attachment_ids );
				}
			});

			// Remove images
			$('#vibe_gallery_container').on( 'click', 'a.delete', function() {

				$(this).closest('li.slider_image').remove();

				var attachment_ids = '';

				$('#vibe_gallery_container ul li.slider_image').css('cursor','default').each(function() {
					var attachment_id = jQuery(this).attr( 'data-attachment_id' );
					attachment_ids = attachment_ids + attachment_id + ',';
				});

				$image_gallery_ids.val( attachment_ids );

				return false;
			} );

		});
	</script>
	<?php
						break;
                                                 case 'audio':
                                                    global $post;
                                                    ?>
                                                <div id="vibe_audio_container">
                        <ul class="vibe_audio">
			<?php
                                if(!$meta || $meta == 'Array') $meta = '';
                                if($meta){
				$attachments = array_filter( explode( ',', $meta ) );
                                
                                
				if ( is_array($attachments ) && $attachments)
					foreach ( $attachments as $attachment_id ) {
						echo '<li class="audio_file" data-attachment_id="' . $attachment_id . '">
							' . wp_get_attachment_image( $attachment_id, 'full' ) . '
							<ul class="actions">
								<li><a href="#" class="delete" title="' . __( 'Delete audio file', 'vibe-customtypes' ) . '">' . __( 'Delete', 'vibe-customtypes' ) . '</a></li>
							</ul>
						</li>';
					}
                                }
			?>
		</ul>
            <?php
                echo '<input type="hidden" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $meta ) . '" />';
              ?>
		

	</div>
	<p class="add_audio hide-if-no-js">
		<a href="#" class="button-primary"><?php _e( 'Add Audio Files', 'vibe-customtypes' ); ?></a>
	</p>
	<script type="text/javascript">
		jQuery(document).ready(function($){

			// Uploading files
			var media_frame;
			var $image_gallery_ids = $('#<?php echo $id;?>');
			var $media = $('#vibe_audio_container ul.vibe_audio');

			jQuery('.add_audio').on( 'click', 'a', function( event ) {

				var $el = $(this);
				var attachment_ids = $image_gallery_ids.val();

				event.preventDefault();

				// If the media frame already exists, reopen it.
				if ( media_frame ) {
					media_frame.open();
					return;
				}

				// Create the media frame.
				media_frame = wp.media.frames.downloadable_file = wp.media({
					// Set the title of the modal.
					title: '<?php _e( 'Add Audio', 'vibe-customtypes' ); ?>',
					button: {
						text: '<?php _e( 'Add Audio', 'vibe-customtypes' ); ?>',
					},
					multiple: true
				});

				// When an image is selected, run a callback.
				media_frame.on( 'select', function() {

					var selection = media_frame.state().get('selection');

					selection.map( function( attachment ) {

						attachment = attachment.toJSON();

						if ( attachment.id ) {
							attachment_ids = attachment_ids ? attachment_ids + "," + attachment.id : attachment.id;

							$media.append('\
								<li class="audio_file" data-attachment_id="' + attachment.id + '">\
									<img src="' + attachment.url + '" />\
									<ul class="actions">\
										<li><a href="#" class="delete" title="<?php _e( 'Delete', 'vibe-customtypes' ); ?>"><?php _e( 'Delete', 'vibe-customtypes' ); ?></a></li>\
									</ul>\
								</li>');
						}

					} );

					$image_gallery_ids.val( attachment_ids );
				});

				// Finally, open the modal.
				media_frame.open();
			});

			// Image ordering
			$media.sortable({
				items: 'li.audio_file',
				cursor: 'move',
				scrollSensitivity:40,
				forcePlaceholderSize: true,
				forceHelperSize: false,
				helper: 'clone',
				opacity: 0.65,
				placeholder: 'wc-metabox-sortable-placeholder',
				start:function(event,ui){
					ui.item.css('background-color','#f6f6f6');
				},
				stop:function(event,ui){
					ui.item.removeAttr('style');
				},
				update: function(event, ui) {
					var attachment_ids = '';

					$('#vibe_audio_container ul li.audio_file').css('cursor','default').each(function() {
						var attachment_id = jQuery(this).attr( 'data-attachment_id' );
						attachment_ids = attachment_ids + attachment_id + ',';
					});

					$image_gallery_ids.val( attachment_ids );
				}
			});

			// Remove images
			$('#vibe_audio_container').on( 'click', 'a.delete', function() {

				$(this).closest('li.audio_file').remove();

				var attachment_ids = '';

				$('#vibe_audio_container ul li.audio_file').css('cursor','default').each(function() {
					var attachment_id = jQuery(this).attr( 'data-attachment_id' );
					attachment_ids = attachment_ids + attachment_id + ',';
				});

				$image_gallery_ids.val( attachment_ids );

				return false;
			} );

		});
	</script>
	<?php
						break;
                                                 case 'video':
                                                    global $post;
                                                    ?>
                                                <div id="vibe_media_container">
                        <ul class="vibe_media">
			<?php
                                if(!$meta || $meta == 'Array') $meta = '';
                                if($meta){
				$attachments = array_filter( explode( ',', $meta ) );
                                
                                
				if ( is_array($attachments ) && $attachments)
					foreach ( $attachments as $attachment_id ) {
						echo '<li class="slider_image" data-attachment_id="' . $attachment_id . '">
							' . wp_get_attachment_image( $attachment_id, 'full' ) . '
							<ul class="actions">
								<li><a href="#" class="delete" title="' . __( 'Delete video file', 'vibe-customtypes' ) . '">' . __( 'Delete', 'vibe-customtypes' ) . '</a></li>
							</ul>
						</li>';
					}
                                }
			?>
		</ul>
            <?php
                echo '<input type="hidden" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $meta ) . '" />';
              ?>
		

	</div>
	<p class="add_video hide-if-no-js">
		<a href="#" class="button-primary"><?php _e( 'Add Video Files', 'vibe-customtypes' ); ?></a>
	</p>
	<script type="text/javascript">
		jQuery(document).ready(function($){

			// Uploading files
			var media_frame;
			var $image_gallery_ids = $('#<?php echo $id;?>');
			var $media = $('#vibe_media_container ul.vibe_media');

			jQuery('.add_video').on( 'click', 'a', function( event ) {

				var $el = $(this);
				var attachment_ids = $image_gallery_ids.val();

				event.preventDefault();

				// If the media frame already exists, reopen it.
				if ( media_frame ) {
					media_frame.open();
					return;
				}

				// Create the media frame.
				media_frame = wp.media.frames.downloadable_file = wp.media({
					// Set the title of the modal.
					title: '<?php _e( 'Add Video Files', 'vibe-customtypes' ); ?>',
					button: {
						text: '<?php _e( 'Add Video', 'vibe-customtypes' ); ?>',
					},
					multiple: true
				});

				// When an image is selected, run a callback.
				media_frame.on( 'select', function() {

					var selection = media_frame.state().get('selection');

					selection.map( function( attachment ) {

						attachment = attachment.toJSON();

						if ( attachment.id ) {
							attachment_ids = attachment_ids ? attachment_ids + "," + attachment.id : attachment.id;

							$media.append('\
								<li class="slider_image" data-attachment_id="' + attachment.id + '">\
									<img src="' + attachment.url + '" />\
									<ul class="actions">\
										<li><a href="#" class="delete" title="<?php _e( 'Delete', 'vibe-customtypes' ); ?>"><?php _e( 'Delete', 'vibe-customtypes' ); ?></a></li>\
									</ul>\
								</li>');
						}

					} );

					$image_gallery_ids.val( attachment_ids );
				});

				// Finally, open the modal.
				media_frame.open();
			});

			// Image ordering
			$media.sortable({
				items: 'li.slider_image',
				cursor: 'move',
				scrollSensitivity:40,
				forcePlaceholderSize: true,
				forceHelperSize: false,
				helper: 'clone',
				opacity: 0.65,
				placeholder: 'wc-metabox-sortable-placeholder',
				start:function(event,ui){
					ui.item.css('background-color','#f6f6f6');
				},
				stop:function(event,ui){
					ui.item.removeAttr('style');
				},
				update: function(event, ui) {
					var attachment_ids = '';

					$('#vibe_media_container ul li.image').css('cursor','default').each(function() {
						var attachment_id = jQuery(this).attr( 'data-attachment_id' );
						attachment_ids = attachment_ids + attachment_id + ',';
					});

					$image_gallery_ids.val( attachment_ids );
				}
			});

			// Remove images
			$('#vibe_media_container').on( 'click', 'a.delete', function() {

				$(this).closest('li.slider_image').remove();

				var attachment_ids = '';

				$('#vibe_media_container ul li.slider_image').css('cursor','default').each(function() {
					var attachment_id = jQuery(this).attr( 'data-attachment_id' );
					attachment_ids = attachment_ids + attachment_id + ',';
				});

				$image_gallery_ids.val( attachment_ids );

				return false;
			} );

		});
	</script>
	<?php
						break;

						case 'note':
							echo $desc;
						break;
						case 'multiattachments':

							if(!$meta || $meta == 'Array') $meta = '';
                            $attachments = array();

                            if(!empty($meta)){
								$attachments = $meta;
							}else{
								global $wpdb;
								$connected_attachments = $wpdb->get_results("SELECT ID from {$wpdb->posts} WHERE post_type = 'attachment' AND post_parent = $post->ID"); 
								if(!empty($connected_attachments)){
									foreach($connected_attachments as $att){
										$attachments[] = $att->ID;
									}
								}
							}
							echo '<ul class="' . $field['id'] . '_attachments attachment_list">';
							if(!empty($attachments)){
								
								foreach($attachments as $attachment_id){
									echo '<li><span class="sort dashicons dashicons-move"></span>';
									echo '<strong>'.get_the_title($attachment_id).'</strong>';
									echo '<input type="hidden" name="' . $field['id'] . '[]" value="'.$attachment_id.'">';
									echo '<span class="remove_attachment dashicons dashicons-no"></span>';
									echo '</li>';
								}
							}
							echo '</ul>';
							?>
							<a class="add_attachments button-primary" data-add="<?php echo $field['id']; ?>_attachments"><?php _e( 'Add Attachments', 'vibe-customtypes' ); ?></a>
							<?php

							?>
							<script type="text/javascript">
								jQuery(document).ready(function($){

									// Uploading files
									var attachment_frame;
									//var $image_gallery_ids = $('#<?php echo $id;?>');
									var $media = $('.<?php echo $field['id']; ?>_attachments');

									jQuery('.add_attachments').on( 'click',function( event ) {

										var $el = $(this);
										var attachment_ids = $("input[name=\'<?php echo $field['id']; ?>\']").val();

										event.preventDefault();

										// If the media frame already exists, reopen it.
										if ( attachment_frame ) {
											attachment_frame.open();
											return;
										}

										// Create the media frame.
										attachment_frame = wp.media.frames.downloadable_file = wp.media({
											// Set the title of the modal.
											title: '<?php _e( 'Add Attachments', 'vibe-customtypes' ); ?>',
											button: {
												text: '<?php _e( 'Add Attachment', 'vibe-customtypes' ); ?>',
											},
											multiple: true
										});

										// When an image is selected, run a callback.
										attachment_frame.on( 'select', function() {

											var selection = attachment_frame.state().get('selection');

											selection.map( function( attachment ) {

												attachment = attachment.toJSON();
												console.log(attachment.title);

												if ( attachment.id ) {
													
													$media.append('\
														<li><span class="sort dashicons dashicons-move"></span>\
															<strong>'+attachment.title+'</strong>\
															<input type="hidden" name="<?php echo $field['id']; ?>[]" value="' + attachment.id + '"/>\
															<span class="remove_attachment dashicons dashicons-no"></span>\
														</li>');
												}

											} );

										});

										// Finally, open the modal.
										attachment_frame.open();
									});

									// Image ordering
									$media.sortable({
										items: 'li',
										handle:'.sort',
										cursor: 'move',
										scrollSensitivity:40,
										opacity: 0.65,
										start:function(event,ui){
											ui.item.css('background-color','#f6f6f6');
										},
										stop:function(event,ui){
											ui.item.removeAttr('style');
										},
									});

									// Remove images
									$('.attachment_list .remove_attachment').on( 'click', function() {
										$(this).parent('li').remove();
									} );

								});
							</script>
						<?php	
						break;
						default:
							$type=apply_filters('custom_meta_box_type',$type,$meta,$id,$desc,$post_type);
						break;
					} //end switch
			echo '</td></tr>';
		} // end foreach
		echo '</table>'; // end table
		wp_nonce_field('vibe_security','vibe_security');
	}
	
	// Save the Data
	function save_box( $post_id ) {
		global $post, $post_type;
		
		// verify nonce
		if ( ! ( in_array($post_type, $this->page) && @wp_verify_nonce( $_POST[$post_type . '_meta_box_nonce'],  basename( __FILE__ ) ) ) )
			return $post_id;
		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
		// check permissions
		if ( !current_user_can( 'edit_posts', $post_id ) )
			return $post_id;

		// loop through fields and save the data
		foreach ( $this->fields as $field ) {
			                    
			if(isset($field['id'])){

				if( $field['type'] == 'tax_select' ) {
				// save taxonomies
					if ( isset( $_POST[$field['id']] ) )
						$term = $_POST[$field['id']];
					wp_set_object_terms( $post_id, $term, $field['id'] );
				}
				// save the rest
                //print_r($_POST[$field['id']]);

              	$old = get_post_meta( $post_id, $field['id'], true );
                $old='';
                $new=$old;
                                
				if ( isset( $_POST[$field['id']] ) )
					$new = $_POST[$field['id']];

				if(isset($field['type']) && ($field['type'] == 'checkbox' || $field['type'] == 'available' || $field['type'] == 'featured')){ 
					if ( !isset( $_POST[$field['id']] ) ){
                        $new = 0;
                    }
					
				}	
				if(isset($field['type']) && $field['type'] == 'gmap') { 
					if ( isset($_POST[$field['id']]) && is_array( $_POST[$field['id']])){
                        if(isset($_POST[$field['id']]['city']))
							update_post_meta($post_id,'vibe_gmap_city',$_POST[$field['id']]['city']);
                        if(isset($_POST[$field['id']]['state']))
                            update_post_meta($post_id,'vibe_gmap_state',$_POST[$field['id']]['state']);
                        if(isset($_POST[$field['id']]['pincode']))
                            update_post_meta($post_id,'vibe_gmap_pincode',$_POST[$field['id']]['pincode']);
                        if(isset($_POST[$field['id']]['country'])) 
                            update_post_meta($post_id,'vibe_gmap_country',$_POST[$field['id']]['country']);
                    }
				}//end-gmap
				if(isset($field['type']) && $field['type'] == 'image') { 
					if ( !isset( $_POST[$field['id']] ) || !$_POST[$field['id']]){
						$new = ' ';
                                        }
				}
				if(isset($field['type']) && ($field['type'] == 'textarea' || $field['type'] == 'editor')) { 
					if ( !isset( $_POST[$field['id']] ) || !$_POST[$field['id']])
						$new = ' ';
				}
				/*if( $field['type'] == 'sliders' ) {
					$disable = get_post_meta( $post_id, 'vibe_disable_featured', true );
					if((isset($disable) && $disable =='disable') || (!isset($new[0]['image']) || $new[0]['image'] == '')){
						$new=$old;
					}
				}*/
				
				if ( $new && $new != $old ) {
					if ( is_array( $new ) ) {
						foreach ( $new as &$item ) {
                            if(is_array($item)){
                                foreach ( $item as &$item2 ) {
                                	if($field['type'] == 'editor')
                                		$item2 =  $item2 ;
                                	else{
                                		if(is_Array($item2)){
                                			$item2 = serialize($item2);	
                                		}else{
                                			$item2 = esc_attr( $item2 );	
                                		}
                                	}
                            	}
                            	unset($item2);
                            }else{
                              	if($field['type'] == 'editor')
                            		$item =  $item ;
                                else{
                                	$item = esc_attr( $item );  
                                }		
                              		
                            }
						} //end foreach
						unset( $item );
					} else {
						if(isset($field['type']) && $field['type'] != 'editor')
							$new = esc_attr( $new );
					}
					update_post_meta( $post_id, $field['id'], $new );
				} elseif ( !isset($new) && $old ) {
					delete_post_meta( $post_id, $field['id'], $old );
				}elseif(!$new){
                	update_post_meta( $post_id, $field['id'], $new );
                }
            }//isset field['id']
		} // end foreach fields
	} // end save-box
} //end class

class vibe_meta_box_ajax {

	public static $instance;
    
    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new vibe_meta_box_ajax;
        return self::$instance;
    }

	private function __construct(){
		add_action('wp_ajax_get_admin_select_cpt',array($this,'get_admin_select_cpt'));
		add_Action('wp_ajax_get_groups',array($this,'get_groups'));
	}

	function get_admin_select_cpt(){
        $user_id = get_current_user_id();

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','vibe-customtypes');
             die();
        } 
        $id = $_POST['post_id'];
        $q = $_POST['q'];
        $args = array(
            'post_type'=>$_POST['cpt'],
            'posts_per_page'=>99,
            's'=>$q['term']
            );

        $args = apply_filters('wplms_backend_cpt_query',$args,$id);
        
        $query = new WP_Query($args);
        $return = array();
        if($query->have_posts()){
            while($query->have_posts()){
                $query->the_post();
                $return[] = array('id'=>get_the_ID(),'text'=>get_the_title());
            }
        }
        wp_reset_postdata();
        print_r(json_encode($return));
        die();
    }

    function get_groups(){

    	 if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','vibe-customtypes');
             die();
        } 
        $id = $_POST['id'];
        $q = $_POST['q'];

        global $bp,$wpdb;
        $return = array();
        $search_terms_like = '%'.$q['term'].'%';
		$groups = $wpdb->get_results( $wpdb->prepare( "SELECT id , name  FROM {$bp->groups->table_name} WHERE ( name LIKE %s OR description LIKE %s )", $search_terms_like, $search_terms_like ) );
    	
    	if(!empty($groups)){
    		foreach($groups as $group){
	    		$return[] = array('id'=>$group->id,'text'=>$group->name);
	    	}
    	}
    	print_r(json_encode($return));
        die();
    }
}

vibe_meta_box_ajax::init();