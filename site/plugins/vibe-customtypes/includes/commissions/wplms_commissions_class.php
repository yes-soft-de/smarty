<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if(!class_exists('WPLMS_Instructor_Commission')){

	class WPLMS_Instructor_Commission{

	function get_order_report_data($start_date,$end_date) {
		global $wpdb;
		$order_item_meta_table=$wpdb->prefix.'woocommerce_order_itemmeta';
		$order_items_table=$wpdb->prefix.'woocommerce_order_items';
		$inst_commissions = $wpdb->get_results("SELECT order_items.order_id,order_meta.meta_key as instructor,order_meta.meta_value as commission
			FROM {$wpdb->posts} as posts
			LEFT JOIN $order_items_table as order_items ON posts.ID = order_items.order_id
			LEFT JOIN $order_item_meta_table as order_meta ON order_items.order_item_id = order_meta.order_item_id
			WHERE posts.post_type='shop_order'
			AND posts.post_status='wc-completed'
			AND posts.post_date BETWEEN '$start_date' AND '$end_date'
			AND order_meta.meta_key LIKE '%commission%'");

		if(is_array($inst_commissions)){
			foreach($inst_commissions as $inst_commission){
				$order_ids[]=$inst_commission->order_id;
				$inst=explode('commission',$inst_commission->instructor);
				if(is_numeric($inst[1])){
					$instructor_commissions[$inst[1]] += $inst_commission->commission;
				}
			}
			if(is_array($order_ids))
				$order_id_string = implode(',',$order_ids);
		}
		return $instructor_commissions;
	}

	}
}

if(!class_exists('WPLMS_Commissions')){

	class WPLMS_Commissions extends WPLMS_Instructor_Commission{


		public function instructor_data($start_date,$end_date){// End function
			$instructor_data=$this->get_order_report_data($start_date,$end_date);		
			return $instructor_data;
		}

	}
}
?>