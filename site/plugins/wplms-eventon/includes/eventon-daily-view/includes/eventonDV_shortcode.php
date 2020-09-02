<?php
/**
 * EventON dailyView shortcode
 *
 * Handles all shortcode related functions
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	dailyView/Functions/shortcode
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class evo_dv_shortcode{

	static $add_script;

	function __construct(){
		add_shortcode('add_eventon_dv', array($this,'evoDV_generate_calendar'));
		add_filter('eventon_shortcode_popup',array($this,'evoDV_add_shortcode_options'), 10, 1);
	}

	//	Shortcode processing
		function evoDV_generate_calendar($atts){
			global $eventon_dv, $eventon;
			
			$eventon_dv->is_running_dv=true;
			
			add_filter('eventon_shortcode_defaults', array($this,'evoDV_add_shortcode_defaults'), 10, 1);		
			
			ob_start();				
				echo $eventon_dv->frontend->generate_calendar($atts);
			
			$eventon_dv->is_running_dv=false;
			return ob_get_clean();
					
		}
	// add new default shortcode arguments
			function evoDV_add_shortcode_defaults($arr){
				
				return array_merge($arr, array(
					'fixed_day'=>0,
					'day_incre'=>0,
					'hide_sort_options'=>'no',
					'hide_date_box'=>'no',
					'mo1st'=>'',
					'today'=>'no',
					'header_title'=>'',
					'wplms_course'=>'',
					'wplms_batch'=>''
				));	
			}

	// ADD shortcode buttons to eventON shortcode popup
		function evoDV_add_shortcode_options($shortcode_array){
			
			global $evo_shortcode_box;
			
			$new_shortcode_array = array(
				array(
					'id'=>'s_DV',
					'name'=>'DailyView',
					'code'=>'add_eventon_dv',
					'variables'=>array(
						$evo_shortcode_box->shortcode_default_field('cal_id'),
						array(
							'name'=>'Show Only Today Events',
							'type'=>'YN',
							'guide'=>'YES = show only today events',
							'var'=>'today',
							'default'=>'no',
							'afterstatement'=>'titles'
						),
							array(
								'name'=>'Title for Calendar',
								'placeholder'=>'eg. Today Events',
								'type'=>'text',
								'guide'=>'Set a title for the today events calendar',
								'var'=>'header_title','default'=>'0',
								'closestatement'=>'titles'
							),
						$evo_shortcode_box->shortcode_default_field('show_et_ft_img'),
						$evo_shortcode_box->shortcode_default_field('ft_event_priority'),
						array(
							'name'=>'Day Increment',
							'type'=>'text',
							'placeholder'=>'eg. +1',
							'guide'=>'Change starting date (eg. +1)',
							'var'=>'day_incre',
							'default'=>'0'
						),$evo_shortcode_box->shortcode_default_field('month_incre'),
						$evo_shortcode_box->shortcode_default_field('event_type'),
						$evo_shortcode_box->shortcode_default_field('event_type_2'),
						array(
							'name'=>'Fixed Day',
							'type'=>'text',
							'guide'=>'Set fixed day as calendar focused day (integer)',
							'var'=>'fixed_day',
							'guide'=>'Both fixed month and year should be set for this to work',
							'default'=>'0',
							'placeholder'=>'eg. 10'
						),
						$evo_shortcode_box->shortcode_default_field('fixed_month'),
						$evo_shortcode_box->shortcode_default_field('fixed_year'),
						$evo_shortcode_box->shortcode_default_field('etc_override'),
						$evo_shortcode_box->shortcode_default_field('evc_open'),						
						$evo_shortcode_box->shortcode_default_field('event_order'),
						$evo_shortcode_box->shortcode_default_field('lang'),						
						$evo_shortcode_box->shortcode_default_field('jumper'),
						array(
							'name'=>'Switch to first of month',
							'type'=>'YN',
							'guide'=>'Yes = when switching month focus day will go to 1st of new month',
							'var'=>'mo1st',
							'default'=>'no'
						),array(
							'name'=>'Hide Focus Date Section',
							'type'=>'YN',
							'guide'=>'Yes = will hide the focus date section above the days stripe on the calendar',
							'var'=>'hide_date_box',
							'default'=>'no'
						),array(
							'name'=>'WPLMS Course',
							'type'=>'text',
							'placeholder'=>'Course id Or 1 for Current Course',
							'guide'=>'Enter course id of the course events',
							'var'=>'wplms_course',
							'default'=>'0'
						),
					)
				)
			);

			return array_merge($shortcode_array, $new_shortcode_array);
		}


	
}





?>