<?php
/**
 * BUDDYDRIVE Connect for WPLMS
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Initialization
 * @version     2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WPLMS_BuddyDrive{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_BuddyDrive();

        return self::$instance;
    }

    private function __construct(){
    	

    	//Activity Upload
    	add_action( 'bp_activity_post_form_button',array($this,'buddydrive_activity' ));
    	//Add Attachment in MEssages
		add_action( 'bp_messages_compose_submit_buttons', array($this,'bp_message_compose' ));

		//Custom Sharing options
    	add_filter('buddydrive_get_sharing_options',array($this,'sharing_options'));

    	//Privacy status
		add_filter('buddydrive_admin_get_item_status',array($this,'privacy_status'));



		//Course Privacy
		add_filter('buddydrive_get_stati',array($this,'drive_course_privacy'));

		//Course Scope
		add_filter('buddydrive_item_get_default',array($this,'course_scope'),10,2);

		//bp_parse_args_before_buddydrive_fetch_items_
		//Course Query
		add_filter('buddydrive_item_get',array($this,'course_drive_query'));
		add_filter('buddydrive_attachment_script_data',array($this,'script_data'));

		

		add_filter('buddydrive_get_buddyfile_check_for',array($this,'check_for'),10,2);
		add_filter('buddydrive_file_downloader_can_download',array($this,'can_download'),10,2);
		add_action('buddydrive_file_downloaded',array($this,'buddydrive_file_downloaded'),10,1);

		//Drive link in Course
		add_filter('wplms_course_nav_menu',array($this,'course_drive_link'));
		

		//Creating Folder Default PRivacy
		
		//Course Drive Upload custom fields
		//add_action('buddydrive_uploader_custom_fields',array($this,'course_fields'),10,1);
		add_action('buddydrive_save_item',array($this,'save_buddydrive'),10,2);

		//Add capability to Instructors for Uploading files in their courses
		add_filter('buddydrive_current_user_can',array($this,'add_caps'),10,4);
		//Custom CSS
		add_action('wp_footer',array($this,'run_js_css'));



		/*===== Permalink Setting === */
        add_action('wplms_course_action_point_permalink_settings',array($this,'permalink_setting'));
        add_filter('wplms_save_vibe_course_permalinks',array($this,'save_permalinks'));
        add_action('init', array($this,'add_endpoints'));
    	add_filter( 'request', array($this,'filter_request' ));
		add_action( 'template_redirect', array($this,'catch_vars' ),9);


		//add_filter( 'buddydrive_use_deprecated_ui', '__return_true' );
		add_action( 'admin_notices', array($this,'update_buddydrive_notice' ));
    }

    function update_buddydrive_notice(){
    	if(function_exists('buddydrive_get_version')){
    		$version = buddydrive_get_version();
    		if(version_compare($version, "2.0") < 0){
				echo '<div class="error notice is-dismissible">
			    <p>'.__( 'Please update Buddydrive to latest version 2.0', 'vibe').'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
			  </div>';
			}
    	}
    }

    function add_caps($can, $capability, $user_id, $args){

    	global $bp;
    	if($bp->current_component == 'course'){
    		if($capability == 'buddydrive_upload'){
    			if(is_numeric($bp->current_item)){
	    			$author_id = get_post_field('post_author',$bp->current_item);
	    			$authors = apply_filters('wplms_course_instructors',array($author_id),$bp->current_item);
	    		}
	    		$flag = apply_filters('wplms_allow_students_upload_in_drive',true);
	    		if(in_array($user_id,$authors)){

	    			return true;
	    		}else if(function_exists('bp_course_is_member') && $flag && bp_course_is_member($bp->current_item,$user_id)){
	    			return true;
	    		}
    		}
    		
    	}
    	return $can;
    }
    function parse_bd_args($r){
		//print_r($r);
		global $post;

		if(is_object($post) && $post->post_type == 'course'){
			$r['buddydrive_scope'] = 'course';
			$querystring['meta_query'][]=array(
				'key'=>'course',
				'value' =>$post->ID,
				'compare'=>'='
				);
//			$r['customs'] =[{"name":"course","val":1242},{"name":"buddydrive_course","val":1}]

		}
		return $r;
	}

	function drive_course_privacy($f){
		$f['buddydrive_course'] = array(
			'label'                     => _x( 'Restricted to a Course', 'Drive file or folder status', 'vibe' ),
			'protected'                 => true,
			'show_in_admin_status_list' => false,
			'show_in_admin_all_list'    => false,
			'buddydrive_settings'       => false,
			'buddydrive_privacy'        => 'course',
		);
		return $f;
	}


	function course_scope($query_args, $r){
		if($r['buddydrive_scope'] == 'course'){
			//print_r($query_args);
		}
		return $query_args;
	}

    function buddydrive_activity(){
    	if ( function_exists( 'buddydrive_editor' ) ) {
	        buddydrive_editor( 'whats-new' );
	    }
    }

    function bp_message_compose() {
	    if ( bp_is_my_profile() && function_exists( 'buddydrive_editor' ) ) {
	        buddydrive_editor( 'message_content' );
	    }
	}
    function sharing_options($options){
    	$options['course'] = __('Restricted to Course','vibe');
    	return $options;
    }

    function buddydrive_get_usercourses(){
    	$user_id = get_current_user_id();
    	
    	global $wpdb;
    	$courses = array();
    	if(current_user_can('manage_options')){
    		$courses_query = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'course' AND post_status = 'publish'");
    		if(!empty($courses_query)){
    			foreach($courses_query as $course_query){
    				$courses[$course_query->ID] = $course_query->post_title;
    			}
    		}
    	}else{
    		if(current_user_can('edit_posts')){
    			$courses_author = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'course' AND post_status = 'publish' AND post_author = $user_id");
    			if(!empty($courses_author)){
	    			foreach($courses_author as $course_author){
	    				$courses[$course_author->ID] = $course_author->post_title;
	    			}
	    		}
    		}

    		$courses_query = $query = $wpdb->get_results($wpdb->prepare("
								    SELECT posts.ID as id AND posts.post_title as title
							      	FROM {$wpdb->posts} AS posts
							      	LEFT JOIN {$wpdb->usermeta} AS meta ON posts.ID = meta.meta_key
							      	WHERE   posts.post_type   = %s
							      	AND   posts.post_status   = %s
							      	AND   meta.user_id   = %d
							      	",'course','publish',$user_id));
    		if(!empty($courses_query)){
    			foreach($courses_query as $course_query){
    				$courses[$course_query->id] = $course_query->title;
    			}
    		}
    	}

    	if(empty($courses)){
    		echo '<div class="message">'.__('No course found','vibe').'</div>';
    	}else{
    		if(is_singular('course')){
    			echo '<input id="buddydrive_course" class="buddydrive-customs" type="hidden" name="buddydrive_course" value="'.get_the_ID().'">';
    			die();
    		}
    		?>
    		<label for="buddydrive_course"><?php echo _x('Select a course','Select course in Buddydrive options','vibe'); ?></label>
    		<select id="buddydrive_course" class="buddydrive-customs">
    			<?php foreach($courses as $id => $course){ ?>
    			<option value="<?php echo $id; ?>"><?php echo $course; ?></option>
    			<?php } ?>
    		</select>
    		<?php
    	}
    	die();

    }


	function privacy_status($args){
		if($args[1] == 'course'){
			$args[0] = '<i class="fa fa-tasks"></i>'.__('Course Only','vibe');
		}
		return $args;
	}

	



	function course_drive_query($querystring){
		global $post,$bp;

		if($post->post_type == 'course' || $bp->current_component == 'course' || $bp->unfiltered_uri[0] == 'course'){
			$querystring['post_status'] = array('draft','buddydrive_public','buddydrive_private','buddydrive_course','publish');
            if(empty($querystring['meta_query'])){
                $querystring['meta_query']= array();    
            }
            
            if(in_array($post->post_type,array('buddydrive_file','buddydrive_folder'))){
                $course_id = $post->ID;
            }

			
			if(empty($course_id)){ // All courses page set as slug
				$course_id = $bp->current_item;
				if(empty($course_id)){
					global $wpdb;
					$action = $bp->current_action;
					if(empty($action)){$action = $bp->unfiltered_uri[1];}
					$course_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_name = '$action' and post_type = 'course'");	
				}
			}
			
			$querystring['meta_query'][]=array(
				'key'=>'course',
				'value' => $course_id,
				'compare'=>'='
				);
		}
		

		return $querystring;
	}

	function script_data($script_data){
		if(is_singular('course')){
			$script_data['bp_params']['privacy'] = 'course';
			$script_data['bp_params']['customs'] = json_encode(array(array('name'=>'course','val'=>get_the_ID()),array('name'=>'buddydrive_course','val'=>1)));
		}
		return $script_data;
	}

	

	function save_buddydrive($id,$params){

		if(!empty($params['metas']) && !empty($params['metas']->buddydrive_meta)){
			foreach($params['metas']->buddydrive_meta as $meta){
				if(in_array($meta->cname,array('buddydrive_course','course'))){
					update_post_meta($id,$meta->cname,$meta->cvalue);	
				}
			}
		}
		$params = $_POST['bp_params'];
		if(!empty($params['customs'])){
			$customs = json_decode(stripslashes($params['customs']));
			foreach($customs as $custom){
				update_post_meta($id,$custom->name,$custom->val);
			}
		}
	}

	function check_for($check_for,$buddyfile){
		$privacy = get_post_meta( $buddyfile->ID, '_buddydrive_sharing_option', true );
		if($privacy == 'course'){
			$check_for = 'course';
		}
		return $check_for;
	}

	function can_download($can_download,$buddydrive_file){
		
		$user_id = get_current_user_id();
		if($buddydrive_file->check_for == 'course'){
			$course_id = get_post_meta($buddydrive_file->ID,'course',true);
		}

		if ( $buddydrive_file->user_id == bp_loggedin_user_id() || is_super_admin() )
			$can_download = true;
		elseif ( ! bp_is_active( 'course' ) ) {
			bp_core_add_message( __( 'Course component is deactivated, please contact the administrator.', 'vibe' ), 'error' );
			bp_core_redirect( buddydrive_get_root_url() );
			$can_download = false;
		}
		elseif ( bp_course_is_member(  $course_id , bp_loggedin_user_id() ) )
			$can_download = true;
		else {
			$redirect =get_permalink( $course_id );
			bp_core_add_message( __( 'You must be member of the course to download the file', 'vibe' ), 'error' );
			bp_core_redirect( $redirect );
			$can_download = false;
		}

		return $can_download;
	}
	function buddydrive_file_downloaded($buddyfile){
		
		if(!empty($buddyfile->ID)){ 
			
			if($buddyfile->check_for == 'course'){
				if(!is_user_logged_in()){
					wp_die(__('File can not be accessed','vibe'));
				}

				$user_id = get_current_user_id();
				$enable = get_post_meta($buddyfile->ID, 'buddydrive_course', true );
				if(!empty($enable)){
					$course_id = get_post_meta($buddyfile->ID, 'course', true );
					if(!bp_course_is_member($course_id,$user_id)){
						wp_die(__('File can not be accessed','vibe'));
					}
				}				
			}
		}
	}


	/*==== Course Menu ===*/

	function course_drive_link($nav){

		if(empty($this->permalinks))
       		$this->permalinks = get_option( 'vibe_course_permalinks' );
		
        $drive_slug = (isset($this->permalinks['drive_slug']))?$this->permalinks['drive_slug']:'drive';
		$flag = apply_filters('wplms_course_drive_access',1);
		if(!empty($flag)){
			$nav['drive'] = array(
	                    'id' => 'drive',
	                    'label'=>__('Drive ','vibe'),
	                    'action' => $drive_slug,
	                    'can_view' => 1,
	                    'link'=>bp_get_course_permalink(),
	                );
		}
    	return $nav;
	}

	/*
	PERMALINKS
	 */
	function permalink_setting(){
        if(empty($this->permalinks))
       		$this->permalinks = get_option( 'vibe_course_permalinks' );
       	
        $drive_slug = ($this->permalinks['drive_slug'])?$this->permalinks['drive_slug']:'drive';
        ?>
        <tr>
            <th><label><?php _e('Drive','vibe'); ?></label></th>
            <td>
                <input name="drive_slug" type="text" value="<?php echo esc_attr( $drive_slug ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Course Drive slug', 'vibe' ); ?></span>
            </td>
        </tr>
        <?php
    }

    function save_permalinks($permalinks){
        
        if(!empty($_POST['drive_slug'])){
            $drive_slug = trim( sanitize_text_field( $_POST['drive_slug'] ), '/' );
            $drive_slug = '/' . $drive_slug;
            $permalinks['drive_slug'] = untrailingslashit( $drive_slug );
        }
        return $permalinks;
    }

    function add_endpoints(){
    	if(empty($this->permalinks))
    		$this->permalinks = get_option( 'vibe_course_permalinks' );
        
        $drive_slug = isset($this->permalinks['drive_slug'])?$this->permalinks['drive_slug']:'drive';
        $drive_slug = str_replace('/','',$drive_slug);
        add_rewrite_endpoint($drive_slug, EP_ALL);    
    }

    function filter_request( $vars ){

    	if(empty($this->permalinks))
			$this->permalinks = get_option( 'vibe_course_permalinks' );

		$drive_slug = isset($this->permalinks['drive_slug'])?$this->permalinks['drive_slug']:'drive';
		$drive_slug = str_replace('/','',$drive_slug);
		if(isset( $vars[$drive_slug])){
			$vars[$drive_slug] = true;	
		}
	    return $vars;
	}

	function run_js_css(){
		global $bp;
		if(bp_current_action('drive') || bp_current_component('drive') || (isset($_GET['action']) && $_GET['action'] == 'drive')){
		?>
		<style>
		.buddydrive-owner img{border-radius:50%;}ul.subsubsub>li { float: left; }.single-course #buddydrive-manage-actions li:nth-child(2){display:none !important;}button{border:none;padding:5px 10px;}select#buddydrive-filter{padding-right:30px !important;}span.icon-privacy.course:before {content:"\e2a9";}div#buddydrive-main span.icon-privacy.public:before{content:"\e096" !important;}div#buddydrive-main span.icon-privacy.groups:before {content: "\e012" !important;float: right;}div#buddydrive-main span.icon-privacy.password:before{content:"\e2e7" !important;}.drag-drop #drag-drop-area{border:none !important;}.buddydrive-title .buddydrive-name{padding-top:10px;}nav.buddydrive-toolbar{padding: 0 !important; border: none!important; box-shadow: none!important; float: none;}#bp-upload-ui{    border: 5px dashed rgba(0,0,0,0.2);}
		</style>
		<?php
		}
		?>
		<?php
	}
 
	function catch_vars(){ 
		global $bp,$wp_query,$post;	
		if(!class_exists('Vibe_CustomTypes_Permalinks'))	
			return;

		if(!is_object($post)){
			return;
		}
		$course_id = $post->ID;
		$this->course_id = $course_id;
		$permalinks = Vibe_CustomTypes_Permalinks::init();

		if($bp->unfiltered_uri[0] == trim($permalinks->permalinks['course_base'],'/') || $bp->unfiltered_uri[0] == BP_COURSE_SLUG){
				
				$drive_slug = (!empty($this->permalinks['drive_slug'])?$this->permalinks['drive_slug']:'drive');
				$drive_slug = str_replace('/','',$drive_slug);
				
			    if( get_query_var( $drive_slug )){ 
			    	$bp->current_component = BP_COURSE_CPT;
			    	$bp->current_item = $course_id;
			        $bp->current_action = 'drive';


			        add_action('bp_course_plugin_template_content',array($this,'course_drive'));
			        do_action('buddydrive_enqueue_scripts');
					vibe_load_template('course/single/plugins');

					exit;
			    }
		}
	}

	function course_drive(){
		if(function_exists('buddydrive_ui')){
			//buddydrive_item_nav();
			buddydrive_ui();
		}
		if(!function_exists('buddydrive_component_home_url')){
			return;
		}
	}


	function add_drive_access($settings){
		$drive_access[] = array(
							'label'=> __('Course Drive','vibe' ),
							'text'=>__('Drive Visibility','vibe' ),
							'type'=> 'select',
							'style'=>'',
							'id' => 'vibe_display_course_drive',
							'from'=> 'meta',
							'options'=>array(
								array('value'=>0,'label'=>__('Everyone','vibe')),
								array('value'=>1,'label'=>__('Logged in Users','vibe')),
								array('value'=>2,'label'=>__('Course Users','vibe')),
								array('value'=>3,'label'=>__('Instructors and Admins','vibe')),
							),
							'desc'=> __('Set Course/Drive Visibility','vibe' ),
						);
		$components = $settings['course_components']['fields'];
		array_splice($components,3,0,$drive_access);
	    $settings['course_components']['fields'] = $components;
		return $settings;
	}
}

if ( in_array( 'buddydrive/buddydrive.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )  || (function_exists('is_plugin_active') && is_plugin_active('buddydrive/buddydrive.php'))) {
    WPLMS_BuddyDrive::init(); 
}
