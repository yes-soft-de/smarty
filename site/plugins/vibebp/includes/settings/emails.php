<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class VibeBP_Email_Templates{


	public static $instance;
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Email_Templates();
        return self::$instance;
    }

	private function __construct(){

		
			 add_action('vibebp_settings_page_loaded',array($this,'install_bp_emails'));	
		
		
	}

	function install_bp_emails(){
		if(!isset($_GET['add_emails'])){
      return;
    }
		$emails = array(
			'vibebp_forgot_password'=>array(
	            'description'=> __('Forgot password ','vibe'),
	            'subject' =>  __(' Password Reset','vibe'),
	            'message' =>  __('Someone requested that the password be reset for the following account: ','vibe') . "\r\n\r\n". network_home_url( '/' ) . "\r\n\r\n". sprintf(__('Username: %s','vibe'), '{{user.username}}') . "\r\n\r\n".__('If this was a mistake, just ignore this email and nothing will happen.','vibe') . "\r\n\r\n".sprintf(__('To reset your password, visit the following address: %s','vibe'),'{{{user.forgotpasswordlink}}}') . "\r\n\r\n",
	        ),
		);

		
        $post_type = bp_get_email_post_type();
        $tax_type = bp_get_email_tax_type();
        $migrated_emails = get_terms($tax_type );
        $email_term_count = array();
        if(!empty($migrated_emails)){
          	foreach($migrated_emails as $em){
            	$email_term_count[$em->slug] = $em->count;
          	}
        }

        foreach($emails as $id=>$email){
        	
            if(!term_exists($id,$tax_type) || empty($email_term_count[$id])){

                if(!term_exists($id,$tax_type) && !isset($email_term_count[$id])){
                  $id = wp_insert_term($id,$tax_type, array('description'=> $email['description']));
                }
              
              if(!is_wp_error($id) || (empty($email_term_count[$id])) ){

                  $textbased = str_replace('titlelink','name',$email['message']);
                  $textbased = str_replace('userlink','name',$email['message']);
                  $post_id = wp_insert_post(array(
                              'post_title'=> '[{{{site.name}}}] '.$email['subject'],
                              'post_content'=> $email['message'],
                              'post_excerpt'=> $textbased,
                              'post_type'=> $post_type,
                              'post_status'=> 'publish',
                          ),true);

                  wp_set_object_terms( $post_id, $id, $tax_type );
              }
            }
        }

        //update_option('wplms_bp_emails',bp_course_version());

	}
}

VibeBP_Email_Templates::init();