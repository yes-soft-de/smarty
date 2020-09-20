<?php
/**
 * Front End Events creation for EventOn with WPLMS
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	WPLMS-eventon/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Wplms_EventOn_Front_End{

	public static $instance;
    
    public static function init(){
    	
        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_EventOn_Front_End();
        return self::$instance;
    }

	private function __construct(){ 
		add_filter('wplms_course_creation_tabs',array($this,'event_creation'),9);
		add_action('wplms_front_end_generate_fields_default',array($this,'events_handle'),10,2);
		add_action('wplms_front_end_generate_fields_default',array($this,'evcal_date'),10,2);
		add_action('wp_ajax_save_course_events',array($this,'save_course_events'));
		add_filter('wplms_front_end_element_taxonomy',array($this,'event_taxonomies'),10,3);
		add_filter('wplms_front_end_metaboxes',array($this,'event_settings'),10,3);
		add_filter('wp_ajax_save_event',array($this,'save_event'));
		add_filter('wplms_front_end_new_tax_cap',array($this,'remove_new'),10,2);
	}


	function event_creation($tabs){	
		if(!class_exists('WPLMS_Front_End_Fields')){
			return $tabs;
		}
		
		$fields = array(
					'icon'=> 'pin-2',
					'title'=> __('Events','wplms-eventon' ),
					'subtitle'=>  __('Add Course Events','wplms-eventon' ),
					'fields'=> array(
						array(
							'label'=> __('Events','wplms-eventon' ),
							'type'=> 'events',
							'id'=>'vibe_events',
							'help'=> __('Existing events','wplms-eventon' )
							),
						array(
							'label'=>__('Save Events','wplms-eventon' ),
							'id'=>'save_course_events_button',
							'type'=>'button'
							),
						),
			);
		$new_tabs = array();
		foreach($tabs as $key => $tab){
			$new_tabs[$key] = $tab;
			if($key == 'course_components'){
				$new_tabs['events'] = $fields;
			}
		}
		return $new_tabs;
	}

	function events_handle($field,$course_id = null){
		
		if($field['type'] == 'events'){
			if(!empty($course_id)){
				$event_args = array(
					'post_type'=>'ajde_events',
					'post_status'=>'publish',
					'meta_query'=>array(
							array(
								'key'     => 'wplms_ev_course',
								'value'   => $course_id,
								'compare' => '=',
							),
						),
					);

					$events = new WP_Query($event_args);
					echo '<ul class="course_events">';
					if($events->have_posts()){
						
						while($events->have_posts()){
							$events->the_post();
							$start = get_post_meta(get_the_ID(),'evcal_srow',true);
							echo '<li><strong class="title" data-id="'.get_the_ID().'"><i class="icon-pin-2"></i> [ '.(empty($start)?'':date_i18n( get_option( 'date_format' ), $start )).' ] '.get_the_title().'</strong>
	                                <ul class="data_links">
	                                    <li><a class="edit_event" title="'.__('Edit Event','wplms-eventon' ).'"><span class="dashicons dashicons-edit"></span></a></li>
	                                    <li><a class="event_preview" title="'.__('Preview','wplms-eventon' ).'" target="_blank"><span class="dashicons dashicons-visibility"></span></a></li>
	                                    <li><a class="event_remove" title="'.__('Remove','wplms-eventon' ).'"><span class="dashicons dashicons-no-alt"></span></a></li>
	                                    <li><a class="event_delete" title="'.__('Delete','wplms-eventon' ).'"><span class="dashicons dashicons-trash"></span></a></li>
	                                </ul>
	                            </li>';
							
						}
					}
					echo '</ul>';
					wp_reset_postdata();
				}else{
					echo '<ul class="course_events">';
					echo '</ul>';
				}
					?>
					<ul class="hide">
						<li><strong class="title" data-id=""><i class="icon-pin-2"></i> <span></span></strong>
	                        <ul class="data_links">
	                            <li><a class="edit_event" title="<?php _e('Edit Event','wplms-eventon' ); ?>"><span class="dashicons dashicons-edit"></span></a></li>
	                            <li><a class="event_preview" title="<?php _e('Preview','wplms-eventon' ); ?>" target="_blank"><span class="dashicons dashicons-visibility"></span></a></li>
	                            <li><a class="event_remove" title="<?php _e('Remove','wplms-eventon' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></li>
	                            <li><a class="event_delete" title="<?php _e('Delete','wplms-eventon' ); ?>"><span class="dashicons dashicons-trash"></span></a></li>
	                        </ul>
	                    </li>
					</ul>
					<ul id="event_hidden_base" style="display:none;">
	                    <li class="new_event">
	                    	<div class="add_cpt">
								<div class="col-md-6">
									<a class="more"><i class="icon-pin-2"></i> <?php _e('Select existing event','wplms-eventon' ); ?></a>
									<div class="select_existing_cpt">
										<select data-cpt="ajde_events" data-placeholder="<?php _e('Search an event','wplms-eventon' ); ?>">
										</select>
										<a class="use_selected_event button"><?php _e('Set Event','wplms-eventon' ); ?></a>
									</div>
								</div>
								<div class="col-md-6">
									<a class="more"><i class="icon-pin-2"></i> <?php _e('Create new Event','wplms-eventon' ); ?></a>
									<div class="new_cpt">
										<input type="text" class="form_field vibe_event_title" name="name" placeholder="<?php _e('Event title','wplms-eventon' ); ?>">
										<input type="hidden" class="vibe_cpt" value="ajde_events" />
										
										<a class="button small create_new_event"><?php _e('Create Event','wplms-eventon' ); ?></a>
									</div>
								</div>
							</div>	
							<a class="rem"><i class="icon-x"></i></a>
	                    </li>
	                </ul>
					<div class="add_element">
						<a id="add_event" class="button primary"><?php _e('Add Event','wplms-eventon' ); ?></a>
	                </div>
	                <script>
	                jQuery(document).ready(function($){

                		$('#events').on('active',function(){ console.log('events_active');
                			$('.wplms-taxonomy select').change(function(event){
					            var new_tax = $(this).parent().parent().find('.wplms-new-taxonomy');
					            if($(this).val() === 'new'){
					                new_tax.addClass('animate cssanim fadeIn load');
					            }else{
					                new_tax.removeClass('animate cssanim fadeIn load');
					            }
					        });
                			$('.select2').each(function(){
						        if($(this).hasClass('select2-hidden-accessible'))
						            return;
						        
						         if(!$(this).hasClass('selectcpt')){
						            $(this).select2();
						         }
						            
						    });
                			$('.new_event .more').unbind('click');
                			$('.new_event .more').click(function(){$(this).next().toggle(200);});
                			$('#close_element_button,.close-pop').unbind('click');
                			$('#close_element_button,.close-pop').click(function(){
	                            $(this).parent().hide(200).remove();
	                        });
	                        
	                        $('#add_event').unbind('click');
	                        $('#add_event').on('click',function(){
		                		var clone = $('#event_hidden_base .new_event').clone();
						        clone.find('.select_existing_cpt select').addClass('selecteventcpt');
						        $('ul.course_events').append(clone);
						        $('#events').trigger('active');
						        return false;
	                		});
	                		
                			$('.selecteventcpt').each(function(){
					            if($(this).hasClass('select2-hidden-accessible'))
					                return;

					            var cpt = $(this).attr('data-cpt');
					            var placeholder = $(this).attr('data-placeholder');
					            $(this).select2({
					                minimumInputLength: 4,
					                placeholder: placeholder,
					                closeOnSelect: true,
					                ajax: {
					                    url: ajaxurl,
					                    type: "POST",
					                    dataType: 'json',
					                    delay: 250,
					                    data: function(term){ 
					                            return  {   action: 'get_select_cpt', 
					                                        security: $('#security').val(),
					                                        cpt: cpt,
					                                        q: term,
					                                    }
					                    },
					                    processResults: function (data) {
					                        return {
					                            results: data
					                        };
					                    },       
					                    cache:true  
					                },
					            });
					        });
							$('.use_selected_event').unbind('click');
					        $('.use_selected_event').on('click',function(){
					            var $this = $(this);
					            var clone = $('#event_hidden_base').prev().clone();
					            var id = $(this).parent().find('.selecteventcpt').val();
					            var title = $(this).parent().find('.selecteventcpt option:selected').text();
					            $(clone).find('.title > span').text(title);
					            $(clone).find('.title').attr('data-id',id);
					            var html = clone.html();
					            $('.vibe_vibe_events ul.course_events').append(html);
					            $this.closest('.new_event').remove();
					             $('#events').trigger('active');
					        });
				        	$('.create_new_event').unbind('click');
					        $('.create_new_event').on('click',function(){
					            var $this = $(this);
					            
					            if($this.hasClass('disabled')){
					                return;
					            }
					            var defaulttxt = $this.text();
					            var parent = $(this).parent();
					            var title = parent.find('.vibe_event_title').val();
					            
					            $this.addClass('disabled');
					            $.ajax({
					                type: "POST",
					                url: ajaxurl,
					                data: { action: 'create_new_curriculum', 
					                        security: $('#security').val(),
					                        title: title,
					                        cpt:$this.parent().find('.vibe_cpt').val()
					                      },
					                cache: false,
					                success: function (html) {
					                    $this.removeClass('disabled');
					                    if($.isNumeric(html)){
					                        var clone = $('#event_hidden_base').prev().clone();
					                        $(clone).find('.title > span').text(title);
					                        $(clone).find('.title').attr('data-id',html);
					                        var html =clone.html();
					                        $('.vibe_vibe_events ul.course_events').append(html);
					                        $this.closest('.new_event').remove();
					                        $('#close_element_button').click(function(){
					                            $(this).parent().hide(200).remove();
					                        });
					                        $('#events').trigger('active');
					                    }else{
					                        $this.html(html);
					                        setTimeout(function(){$this.html(defaulttxt);}, 5000);
					                    }
					                }
					            });

					        });
							$('.data_links .event_preview').unbind('click');
					        $('.data_links .event_preview').on('click',function(){
					            var $this = $(this);
					            var defaulttxt = $this.html();
					            $.ajax({
					                    type: "POST",
					                    url: ajaxurl,
					                    data: { action: 'preview_element', 
					                            security: $('#security').val(),
					                            course_id:$('#course_id').val(),
					                            element_id: $this.parent().parent().parent().find('.title').attr('data-id'),
					                          },
					                    cache: false,
					                    success: function (html) {
					                     
					                        var parent = $('#events');
					                        parent.append(html);

					                        var height = parent.find('.element_overlay').outerHeight()+60;

					                        parent.css('height',height+'px');
					                        parent.css('overflow-y','scroll');
					                        parent.trigger('active');

					                        $('.element_overlay .close-pop').click(function(){
					                            $(this).parent().remove();
					                        });
					                        $('.accordion_trigger').on('click',function(){
					                            $(this).parent().toggleClass('open');
					                        });
					                        
					                    }
					            });
					        });
					        $('.data_links .event_remove').unbind('click');
					        $('.data_links .event_remove').on('click',function(){
					            $(this).closest('.data_links').closest('li').remove();
					        });
					        $('.data_links .event_delete').unbind('click');
					        $('.data_links .event_delete').on('click',function(){
					            var $this = $(this);

					            if($this.hasClass('disabled'))
					                return;

					            $this.addClass('disabled');
					            var post_id = $(this).closest('.data_links').parent().find('.title').attr('data-id');
					            $.confirm({
					                  text: wplms_front_end_messages.delete_confirm,
					                  confirm: function() {
					                   
					                     $.ajax({
					                            type: "POST",
					                            url: ajaxurl,
					                            data: { action: 'delete_element', 
					                                    security: $('#security').val(),
					                                    id:post_id,  
					                                  },
					                            cache: false,
					                            success: function (html) {
					                                $this.removeClass('disabled');
					                                if($.isNumeric(html)){
					                                    $this.closest('.data_links').parent('li').remove();
					                                }
					                            }
					                    });
					                  },
					                  cancel: function() {
					                      $this.removeClass('disabled');
					                  },
					                  confirmButton: wplms_front_end_messages.delete_confirm_button,
					                  cancelButton: vibe_course_module_strings.cancel
					              });
					        });
							$('.data_links .edit_event').unbind('click');
					        $('.data_links .edit_event').on('click',function(){
					            var $this = $(this);
					            var defaulttxt = $this.html();
					            $.ajax({
					                    type: "POST",
					                    url: ajaxurl,
					                    data: { action: 'get_element', 
					                            security: $('#security').val(),
					                            course_id:$('#course_id').val(),
					                            element_id: $this.parent().parent().parent().find('.title').attr('data-id'),
					                          },
					                    cache: false,
					                    success: function (html) {
					                        
					                        var parent = $('#events');
					                        $('#events').append(html);

					                        var height = parent.find('.element_overlay').outerHeight()+60;

					                        parent.css('height',height+'px');
					                        parent.css('overflow-y','scroll');
					                        parent.trigger('active');

					                        $('.element_overlay .close-pop').click(function(){
					                            $(this).parent().remove();
					                        });
					                        $('.add_cpt .more').click(function(event){
					                            $('.select_existing_cpt,.new_cpt').hide();
					                            $(this).next().toggle(200);
					                        });
					                        $('.accordion_trigger').on('click',function(){
					                            $(this).parent().toggleClass('open');
					                        });
					                        
					                    }
					            });
					        });

							$('#save_course_events_button').on('click',function(){
								var course_id=$('#course_id').val();
						        var $this = $(this);
						        var defaulttxt = $this.html();
						        var events = [];
						        if($(this).hasClass('disabled'))
						            return;

						        $('ul.course_events li').each(function() {
						            var val =  $(this).find('strong.title').attr('data-id');
						            if(typeof val != 'undefined'){
						                var data = { id: val };  
						                events.push(data);                 
						            } 
						        });

						        $this.addClass('disabled');

						        $.confirm({
						          text: wplms_front_end_messages.save_course_confirm,
						          confirm: function() {
						             $.ajax({
						                    type: "POST",
						                    url: ajaxurl,
						                    data: { action: 'save_course_events', 
						                            security: $('#security').val(),
						                            course_id: course_id,
						                            events: JSON.stringify(events),
						                          },
						                    cache: false,
						                    success: function (html) {
						                        $this.removeClass('disabled');
						                        if($.isNumeric(html)){
						                            $('#course_creation_tabs').trigger('increment');
						                        }else{
						                            $this.html(html);
						                            setTimeout(function(){$this.html(defaulttxt);}, 2000);
						                        }
						                    }
						            });
						          },
						          cancel: function(){
						              $this.removeClass('disabled');
						          },
						          confirmButton: wplms_front_end_messages.save_course_confirm_button,
						          cancelButton: vibe_course_module_strings.cancel
						          });
							});
							$('#save_element_button').unbind('click');
        					$('#save_element_button').on('click',function(event){
					            var $this = $(this);
					                var defaulttxt = $this.html();
					                $this.addClass('disabled');

					                var settings = [];
					                var main = '.element_overlay';
					                tinyMCE.triggerSave();
					                $(main).find('.post_field').each(function() {
					                        

					                        if($(this).is(':radio:checked')){ 
					                            var data = {id:$(this).attr('name'),value: $(this).val()};
					                        }
					                        if($(this).is('select')){
					                            if($(this).is("select[multiple]")){
					                                var values = {};

					                                $(this).find('option:selected').each(function(i,selected){
					                                    values[i] = $(selected).val();
					                                });
					                                var data = {id:$(this).attr('data-id'),value: values};
					                            }else{
					                                var data = {id:$(this).attr('data-id'),value: $(this).val()};
					                            }
					                        }
					                        if($(this).hasClass('repeatable')){
					                            var values = {};
					                            $(this).find('li').each(function(i,selected){
					                                values[i] = $(this).find('input').val();
					                            });
					                            var data = {id:$(this).attr('data-id'),value: values};
					                        }

					                        if($(this).is('input[type="text"]')){
					                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
					                        }
					                        if($(this).is('input[type="number"]')){
					                            var data = {id:$(this).attr('data-id'),type: $(this).attr('name'),value: $(this).val()};
					                        }
					                        if($(this).is('input[type="hidden"]')){
					                            var data = {id:$(this).attr('data-id'),type: $(this).attr('data-type'),value: $(this).val()};
					                        }
					                        if($(this).is('textarea')){
					                            if($(this).hasClass('wp-editor-area')){
					                                tinyMCE.triggerSave();
					                                var data = {id:$(this).attr('id'),type: $(this).attr('name'),value: $(this).val()};    
					                            }else{
					                                var data = {id:$(this).attr('data-id'),type: $(this).attr('name'),value: $(this).val()};   
					                            }
					                        }
					                        settings.push(data);
					                });

					                $.confirm({
					                  text: wplms_front_end_messages.save_confirm,
					                  confirm: function() {
					                   
					                     $.ajax({
					                            type: "POST",
					                            url: ajaxurl,
					                            data: { action: 'save_event', 
					                                    security: $('#security').val(),
					                                    id:$this.attr('data-id'),
					                                    course_id:$('#course_id').val(),
					                                    settings:JSON.stringify(settings)    
					                                  },
					                            cache: false,
					                            success: function (html) {
					                                $this.removeClass('disabled');
					                                $this.html(html);
					                                setTimeout(function(){$this.html(defaulttxt);}, 5000);
					                            }
					                    });
					                  },
					                  cancel: function() {
					                      $this.removeClass('disabled');
					                  },
					                  confirmButton: wplms_front_end_messages.save_confirm_button,
					                  cancelButton: vibe_course_module_strings.cancel
					              });
					        });
                		});
		
						
						$('#events').trigger('active');
	                });
	                </script>
	                <style>

	                </style>
					<?php
			
		}
	}

	function save_course_events(){

        $user_id= get_current_user_id();
        $course_id =$_POST['course_id'];

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security')  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-eventon');
             die();
        }
         if(!is_numeric($course_id) || get_post_type($course_id) != 'course'){
            _e('Invalid Course id, please edit a course','wplms-eventon');
             die();
        }

        $course_author = get_post_field('post_author',$course_id);
        if($course_author != $user_id && !current_user_can('manage_options')){
            _e('Invalid Course Instructor','wplms-eventon');
             die();
        }

        $events = json_decode(stripslashes($_POST['events']));
        global $wpdb;
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %d",'wplms_ev_course',$course_id));
        if(is_array($events) && isset($events)){
        	foreach($events as $event){
	            update_post_meta($event->id,'wplms_ev_course',$course_id);
	            do_action('wplms_course_event_updated',$event->id,$course_id);
	        }
        }
        echo $course_id;
        do_action('wplms_course_events_updated',$course_id,$curriculum);
		die();
	}

	function save_event(){

        $user_id= get_current_user_id();
        $course_id =$_POST['course_id'];
        $event_id = $_POST['id'];
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security')  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-eventon');
             die();
        }
         if(!is_numeric($course_id) || get_post_type($course_id) != 'course'){
            _e('Invalid Course id, please edit a course','wplms-eventon');
             die();
        }

        $course_author = get_post_field('post_author',$course_id);
        if($course_author != $user_id && !current_user_can('manage_options')){
            _e('Invalid Course Instructor','wplms-eventon');
             die();
        }

        $settings = json_decode(stripslashes($_POST['settings']));
        $flags = array();
        $content = array();
        foreach($settings as $setting){
        	if(isset($setting->type) && in_Array($setting->type,array('post_title','post_content_ajde_events'))){
        		if($setting->type == 'post_content_ajde_events')
        			$setting->type = 'post_content';

        		$content[$setting->type] = $setting->value;
        	}
        	if(!in_Array($setting->id,array('event_type','event_type_id','event_location','event_location_id','event_organizer','event_organizer_id','evo_rep_WK'))){
	        	update_post_meta($event_id,$setting->id,$setting->value);
	        	if($setting->id == 'evcal_srow'){
	        		$setting->value = intval($setting->value);
	        		$hour = date('H', $setting->value);
	        		update_post_meta($event_id,'evcal_start_time_hour',$hour);
	        		$min = date('i', $setting->value);
	        		update_post_meta($event_id,'evcal_start_time_min',$min);
	        	}
	        	if($setting->id == 'evcal_erow'){
	        		$setting->value = intval($setting->value);
	        		$hour = date('H', $setting->value);
	        		update_post_meta($event_id,'evcal_end_time_hour',$hour);
	        		$min = date('i', $setting->value);
	        		update_post_meta($event_id,'evcal_end_time_min',$min);
	        	}
        	}else{
        		
        		if($setting->id == 'event_type_id' && !empty($setting->value)){
        			if($setting->value == 'new'){
        				$flags[] = 'event_type';
        			}else{
        				wp_set_post_terms( $event_id, array($setting->value), 'event_type');
        			}
        		}
        		if($setting->id == 'event_location_id' && !empty($setting->value)){
        			if($setting->value == 'new'){
        				$flags[] = 'event_location';
        			}else{
        				$term = get_term($setting->value, 'event_location');
        				if(!empty($term) && !is_wp_error($term))
        					wp_set_object_terms( $event_id, $term->slug, 'event_location');
        			}
        		}
        		if($setting->id == 'event_organizer_id' && !empty($setting->value)){
        			if($setting->value == 'new'){
        				$flags[] = 'event_organizer';
        			}else{
        				$term = get_term($setting->value, 'event_organizer');
        				if(!empty($term) && !is_wp_error($term))
        					wp_set_object_terms( $event_id, $term->slug, 'event_organizer');
        			}
        		}
        		if(in_array($setting->id,$flags)){
        			if($setting->id == 'event_type'){ // Hierarchial
        				//$setting->value = intval($setting->value);
			            if (term_exists($setting->value, $setting->id)) {
			            	if ( !is_object_in_term( $event_id, $setting->id, $setting->value ))
			               		wp_set_post_terms( $event_id, array($setting->value), $setting->id);
			            }else{
	    					$new_term = wp_insert_term($setting->value, $setting->id);
	    					if(!is_wp_error($new_term)){
	    						$setting->value = $new_term['term_id'];
	    						wp_set_post_terms( $event_id, array($setting->value), $setting->id);
	    					}
	    				}
        			}else{
        				$term = get_term($setting->value, $setting->id);
        				
			            if (is_wp_error($term) || empty($term)) {
			            	$new_term = wp_insert_term($setting->value, $setting->id);
			            	if(!is_wp_error($new_term)){
	    						wp_set_post_terms( $event_id, $setting->value, $setting->id);
	    					}
			            }else{
	    					if ( !is_object_in_term( $event_id, $setting->id, $setting->value ))
			               		wp_set_post_terms( $event_id, $term->slug, $setting->id);
	    				}
        			}
        			
        		}

        		if($setting->id == 'evo_rep_WK'){
        			$array = array();
        			foreach($setting->value as $val){
        				$array[] = $val;
        			}
        			update_post_meta($event_id,$setting->id,$array);
        		}
        	}
        }
        if(!empty($content)){
        	$content['ID'] = $event_id;
        	wp_update_post($content);
        }
        _e('Settings Saved','wplms-eventon');
        do_action('wplms_course_event_updated',$course_id,$event_id,$settings);
		die();
	}

	function event_taxonomies($field,$taxonomy,$post_type){
		if($post_type == 'ajde_events'){
			$field = array(
                            'label'=> __('Event Type','wplms-eventon'),
                            'type'=> 'taxonomy',
                            'taxonomy'=> 'event_type',
                            'from'=>'taxonomy',
                            'value_type'=>'single',
                            'style'=>'',
                            'id' => 'event_type_id',
                            'default'=> __('Select Category','wplms-eventon'),
                        );
            $fields = WPLMS_Front_End_Fields::init();
            $fields->generate_fields($field);

			$field = array(
                            'label'=> __('Event Location','wplms-eventon'),
                            'type'=> 'taxonomy',
                            'taxonomy'=> 'event_location',
                            'from'=>'taxonomy',
                            'value_type'=>'single',
                            'style'=>'',
                            'id' => 'event_location_id',
                            'default'=> __('Select Location','wplms-eventon'),
                        );
            $fields->generate_fields($field);
			$field =array(
                            'label'=> __('Event Organiser','wplms-eventon'),
                            'type'=> 'taxonomy',
                            'taxonomy'=> 'event_organizer',
                            'from'=>'taxonomy',
                            'value_type'=>'single',
                            'style'=>'',
                            'id' => 'event_organizer_id',
                            'default'=> __('Select Event Organiser','wplms-eventon'),
                        );
		}	
		return $field;
	}



	function evcal_date($field,$course_id = null){
		
		if($field['type'] == 'evcal_date'){

			$format = get_option( 'date_format' );
			$event_id = $_POST['element_id'];
			if(!empty($event_id)){
				$field['value'] = intval($field['value']);
				if(function_exists('eventon_get_langed_pretty_time')){
			 		$time = eventon_get_langed_pretty_time($field['value'], 'm/d/Y H:i');
				}else{
					$time = '';
			 	}
			 	$date = explode(' ',$time);
			 	echo '<div class="field_wrapper '. $field['style'].'">';
				echo '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
				echo '<input type="text" placeholder="'.$field['default'].'" value="'.$date[0].'" class="mid_box ev_date_box '.(empty($field['text'])?'form_field':'').'" data-id="'.$field['id'].'" data-type="'.$field['type'].'"/>';
				echo   '<style>.ui-datepicker{z-index:99 !important;}</style>';
				echo '<input type="text" placeholder="'.$field['default'].'" value="'.$date[1].'" class="mid_box ev_time_box '.(empty($field['text'])?'form_field':'').'" data-id="'.$field['id'].'" data-type="'.$field['type'].'"/>';
				echo   '<input type="hidden" data-id="'.$field['id'].'"  id="'.$field['id'].'" value="'.$field['value'].'" class="post_field" />
				<script>
				jQuery(document).ready(function(){
                    jQuery( ".ev_time_box" ).timePicker({
                      show24Hours: true,
                      separator:":",
                      step: 15
                    });
					jQuery( ".ev_date_box" ).datepicker({
	                    dateFormat: "mm/dd/yy",
	                    numberOfMonths: 1,
	                    showButtonPanel: true,
	                    onSelect: function(d,i){
					          if(d !== i.lastVal){
					              jQuery(this).change();
					          }
					     }
	                });
					jQuery(".ev_time_box,.ev_date_box").on("change",function(){
						var id = jQuery(this).attr("data-id");
						var datetime = jQuery(".ev_date_box[data-id=\'"+id+"\']").val()+" "+jQuery(".ev_time_box[data-id=\'"+id+"\']").val(); 
						var myDate = new Date(datetime);
						var timestamp = myDate.getTime()/1000;
						jQuery("#"+id+"").val(timestamp)
					});
                });</script></div>';
			}
				

		}

	}
					
	function event_settings($settings,$post_type,$course_id){
		if($post_type =='ajde_events'){
			$event_id = $_POST['element_id'];
			$settings = apply_filters('wplms_front_end_event_settings',array(
					array( // Single checkbox
						'label'	=> __('Event Sub-Title','wplms-eventon'), // <label>
						'desc'	=> __('Event Sub- Title.','wplms-eventon'), // description
						'id'	=> 'evcal_subtitle', // field id and name
						'type'	=> 'textarea', // type of field
						'value' => get_post_meta($event_id,'evcal_subtitle',true),
				        'std'   => ''
	                ), 
	                array( // Single checkbox
						'label'	=> __('All Day Event','wplms-eventon'), // <label>
						'desc'	=> __('All Day Event','wplms-eventon'), // description
						'id'	=> 'evcal_allday', // field id and name
						'value' => get_post_meta($event_id,'evcal_allday',true),
						'type'	=> 'switch', // type of field
						'options'  => array('no'=>__('No','wplms-eventon' ),'yes'=>__('Yes','wplms-eventon' )),
				        'std'   => ''
	                ),
	                array( // Single checkbox
						'label'	=> __('Event Start date','wplms-eventon'), // <label>
						'desc'	=> __('Start date for Event','wplms-eventon'), // description
						'id'	=> 'evcal_srow', // field id and name
						'type'	=> 'evcal_date', // type of field
						'value' => get_post_meta($event_id,'evcal_srow',true),
				        'std'   => ''
	                ),
	                array( // Single checkbox
						'label'	=> __('Event End date','wplms-eventon'), // <label>
						'desc'	=> __('End date for Event','wplms-eventon'), // description
						'id'	=> 'evcal_erow', // field id and name
						'type'	=> 'evcal_date', // type of field
						'value' => get_post_meta($event_id,'evcal_erow',true),
				        'std'   => ''
	                ),
	                array( // Single checkbox
						'label'	=> __('Repeating Event','wplms-eventon'), // <label>
						'desc'	=> __('Repeating Event','wplms-eventon'), // description
						'id'	=> 'evcal_repeat', // field id and name
						'type'	=> 'conditionalswitch', // type of field
						'hide_nodes'=> array('evcal_rep_freq','evp_repeat_rb','evo_rep_WK','evcal_rep_gap','evcal_rep_num'),
						'options'  => array('no'=>__('No','wplms-eventon' ),'yes'=>__('Yes','wplms-eventon' )),
						'value' => get_post_meta($event_id,'evcal_repeat',true),
				        'std'   => ''
	                ),
	                array( // Single checkbox
						'label'	=> __('Repeat Frequency','wplms-eventon'), // <label>
						'desc'	=> __('Event repeats after every Day/Week/Month','wplms-eventon'), // description
						'id'	=> 'evcal_rep_freq', // field id and name
						'type'	=> 'select', // type of field
						'options'=> array(array('value'=>'daily','label'=>__('Daily','wplms-eventon' )),array('value'=>'weekly','label'=>__('Weekly','wplms-eventon' )),array('value'=>'monthly','label'=>__('Monthly','wplms-eventon' )),array('value'=>'yearly','label'=>__('Yearly','wplms-eventon' ))),
						'value' => get_post_meta($event_id,'evcal_rep_freq',true),
				        'std'   => ''
	                ),
	                array( // Single checkbox
						'label'	=> __('Repeat Event By','wplms-eventon'), // <label>
						'desc'	=> __('Repeat Event By days of month or days of week','wplms-eventon'), // description
						'id'	=> 'evp_repeat_rb', // field id and name
						'type'	=> 'switch', // type of field
						'options'  => array('dom'=>__('Day of Month','wplms-eventon' ),'dow'=>__('Day of Week','wplms-eventon' )),
						'value' => get_post_meta($event_id,'evcal_repeat',true),
				        'std'   => ''
	                ),
	                
	                array( // Single checkbox
						'label'	=> __('Days of Week','wplms-eventon'), // <label>
						'desc'	=> __('Event repeats on following days of week','wplms-eventon'), // description
						'id'	=> 'evo_rep_WK', // field id and name
						'type'	=> 'multiselect', // type of field
						'options'=> array(array('value'=>0,'label'=>__('Sunday','wplms-eventon' )),array('value'=>1,'label'=>__('Monday','wplms-eventon' )),array('value'=>2,'label'=>__('Tuesday','wplms-eventon' )),array('value'=>3,'label'=>__('Wednesday','wplms-eventon' )),array('value'=>4,'label'=>__('Thursday','wplms-eventon' )),array('value'=>5,'label'=>__('Friday','wplms-eventon' )),array('value'=>6,'label'=>__('Saturday','wplms-eventon' ))),
						'value' => get_post_meta($event_id,'evo_rep_WK',true),
				        'std'   => ''
	                ),
	                array( // Single checkbox
						'label'	=> __('Repeat Gap','wplms-eventon'), // <label>
						'desc'	=> __('Event repeats and gaps for every Day/Week/Month','wplms-eventon'), // description
						'id'	=> 'evcal_rep_gap', // field id and name
						'type'	=> 'number', // type of field
						'value' => get_post_meta($event_id,'evcal_rep_gap',true),
				        'std'   => ''
	                ), 
	                array( // Single checkbox
						'label'	=> __('Maximum Repeat','wplms-eventon'), // <label>
						'desc'	=> __('Number of times event will repeat','wplms-eventon'), // description
						'id'	=> 'evcal_rep_num', // field id and name
						'type'	=> 'number', // type of field
						'value' => get_post_meta($event_id,'evcal_rep_num',true),
				        'std'   => ''
	                ), 
				),$course_id);
		}
		return $settings;
	}

	function remove_new($enable,$field){
		if(in_array($field['id'],array('event_location_id','event_organizer_id'))){
			return false;
		}
		return $enable;
	}
}


Wplms_EventOn_Front_End::init();
