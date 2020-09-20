<?php
/**
 * Adds settings to the permalinks admin settings page.
 *
 * @class       Vibe_CustomTypes_Admin_Permalink_Settings
 * @author      VibeThemes
 * @category    Admin
 * @package     Vibe customtypes/Admin
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Vibe_CustomTypes_Admin_Permalink_Settings' ) ) :

/**
 * Vibe_CustomTypes_Admin_Permalink_Settings Class
 */
class Vibe_CustomTypes_Admin_Permalink_Settings {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		$this->settings_init();
		$this->settings_save();
	}

	/**
	 * Init our settings.
	 */
	public function settings_init() {
		add_settings_section( 'vibe-customtypes-permalink', __( 'Courses permalink base', 'vibe-customtypes' ), array( $this, 'settings' ), 'permalink' );
		add_settings_field(
			'wplms_course_category_slug',            // id
			__( 'Course category base', 'vibe-customtypes' ),   // setting title
			array( $this, 'course_category_slug_input' ),  // display callback
			'permalink',                                    // settings page
			'optional'                                      // settings section
		);
	}

	/**
	 * Show a slug input box.
	 */
	public function course_category_slug_input() {
		$permalinks = get_option( 'vibe_course_permalinks' );
		?>
		<input name="vibe_course_category_slug" type="text" class="regular-text code" value="<?php if ( isset( $permalinks['course_category_base'] ) ) echo esc_attr( $permalinks['course_category_base'] ); ?>" placeholder="<?php echo _x('course-cat', 'slug', 'vibe-customtypes') ?>" />
		<?php
	}



	public function courses_uri(){
		$bp_pages = get_option('bp-pages');
		if(isset($bp_pages) && is_array($bp_pages) && isset($bp_pages['course'])){
		  	$courses_page_id = $bp_pages['course'];
			return $courses_page_id;
		}
		return 0;
	}

	/**
	 * Show the settings.
	 */
	public function settings() {
		echo wpautop( __( 'These settings control the permalinks used for courses. These settings only apply when <strong>not using "default" permalinks above</strong>.', 'vibe-customtypes' ) );

		$permalinks = get_option( 'vibe_course_permalinks' );
		$course_permalink = $permalinks['course_base'];
		$quiz_permalink = $permalinks['quiz_base'];
		$news_permalink = $permalinks['news_base'];
		$unit_permalink = $permalinks['unit_base'];
		$curriculum_slug = ($permalinks['curriculum_slug'])?$permalinks['curriculum_slug']:'curriculum';
		$members_slug = ($permalinks['members_slug'])?$permalinks['members_slug']:'members';
		$activity_slug = ($permalinks['activity_slug'])?$permalinks['activity_slug']:'activity';
		$admin_slug = ($permalinks['admin_slug'])?$permalinks['admin_slug']:'admin';
		$submissions_slug = ($permalinks['submissions_slug'])?$permalinks['submissions_slug']:'submissions';
		$stats_slug = ($permalinks['stats_slug'])?$permalinks['stats_slug']:'stats';
		$news_slug = ($permalinks['news_slug'])?$permalinks['news_slug']:'course-news';

		$courses_page_id   = $this->courses_uri();

		$base_slug      = urldecode( ( $courses_page_id > 0 && get_post( $courses_page_id ) ) ? get_page_uri( $courses_page_id ) : _x( 'course', 'default-slug', 'vibe-customtypes' ) );
		$course_base   = BP_COURSE_SLUG;


		$structures = array(
			0 => '/' . trailingslashit( $course_base ),
			1 => '/' . trailingslashit( $base_slug ),
		);

		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label><input name="course_permalink" type="radio" value="<?php echo esc_attr( $structures[0] ); ?>" class="base_to_go_course" <?php checked( trim($structures[0],'/'), trim($course_permalink,'/') ); ?> /> <?php _e( 'Course', 'vibe-customtypes' ); ?></label></th>
					<td><code><?php echo esc_html( home_url() ); ?>/<?php echo BP_COURSE_SLUG; ?>/sample-course/</code></td>
				</tr>
				<tr>
					<th><label><input name="course_permalink" type="radio" value="<?php echo esc_attr( $structures[1] ); ?>" class="base_to_go_course" <?php checked( trim($structures[1],'/'), trim($course_permalink,'/') ); ?> /> <?php _e( 'Course Directory', 'vibe-customtypes' ); ?></label></th>
					<td><code><?php echo esc_html( home_url() ); ?>/<?php echo esc_html( $base_slug ); ?>/sample-course/</code></td>
				</tr>
				<tr>
					<th><label><input name="course_permalink" id="vibe_course_custom_selection" type="radio" value="custom" class="tog" <?php checked( !in_array( $course_permalink, $structures ), false ); ?> />
						<?php _e( 'Custom Base', 'vibe-customtypes' ); ?></label></th>
					<td>
						<input name="course_permalink_structure" id="vibe_course_permalink_structure" type="text" value="<?php echo esc_attr( $course_permalink ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Enter a custom base to use. A base <strong>must</strong> be set or WordPress will use default instead.', 'vibe-customtypes' ); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
		<table class="form-table">	
			<tbody>
				<tr><th><h3><?php _e('Quiz Permalinks','vibe-customtypes'); ?></h3></th></tr>
				<tr>
					<th><label><input name="quiz_permalink" type="radio" value="<?php echo esc_attr( '/'.WPLMS_QUIZ_SLUG ); ?>" class="base_to_go_quiz" <?php checked( WPLMS_QUIZ_SLUG, trim($quiz_permalink,'/') ); ?> /> <?php _e( 'Quiz', 'vibe-customtypes' ); ?></label></th>
					<td><code><?php echo esc_html( home_url() ); ?>/<?php echo WPLMS_QUIZ_SLUG; ?>/sample-quiz/</code></td>
				</tr>
				<tr>
					<th><label><input name="quiz_permalink" id="vibe_quiz_custom_selection" type="radio" value="custom" class="tog" />
						<?php _e( 'Custom Base', 'vibe-customtypes' ); ?></label></th>
					<td>
						<input name="quiz_permalink_structure" id="vibe_quiz_permalink_structure" type="text" value="<?php echo esc_attr( $quiz_permalink ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Enter a custom base to use. A base <strong>must</strong> be set or WordPress will use default instead.', 'vibe-customtypes' ); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
		<table class="form-table">	
			<tbody>
				<tr><th><h3><?php _e('Unit Permalinks','vibe-customtypes'); ?></h3></th></tr>
				<tr>
					<th><label><input name="unit_permalink" type="radio" value="<?php echo esc_attr( '/'.WPLMS_UNIT_SLUG ); ?>" class="base_to_go_unit" <?php checked( WPLMS_UNIT_SLUG, trim($unit_permalink,'/') ); ?>/> <?php _e( 'Unit', 'vibe-customtypes' ); ?></label></th>
					<td><code><?php echo esc_html( home_url() ); ?>/<?php echo WPLMS_UNIT_SLUG; ?>/sample-unit/</code></td>
				</tr>
				<tr>
					<th><label><input name="unit_permalink" id="vibe_unit_custom_selection" type="radio" value="custom" class="tog" />
						<?php _e( 'Custom Base', 'vibe-customtypes' ); ?></label></th>
					<td>
						<input name="unit_permalink_structure" id="vibe_unit_permalink_structure" type="text" value="<?php echo esc_attr( $unit_permalink ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Enter a custom base to use. A base <strong>must</strong> be set or WordPress will use default instead.', 'vibe-customtypes' ); ?></span>
					</td>
				</tr>
			</tbody>
		</table>

		<?php if(function_exists('vibe_get_option')){$show_news = vibe_get_option('show_news');if(!empty($show_news)){?>
		<table class="form-table">	
			<tbody>
				<tr><th><h3><?php _e('News Permalinks','vibe-customtypes'); ?></h3></th></tr>
				<tr>
					<th><label><input name="news_permalink" type="radio" value="<?php echo esc_attr( '/'.WPLMS_NEWS_SLUG ); ?>" class="base_to_go_news" <?php checked( WPLMS_NEWS_SLUG, trim($news_permalink,'/') ); ?> /> <?php _e( 'News', 'vibe-customtypes' ); ?></label></th>
					<td><code><?php echo esc_html( home_url() ); ?>/<?php echo WPLMS_NEWS_SLUG; ?>/sample-news/</code></td>
				</tr>
				<tr>
					<th><label><input name="news_permalink" id="vibe_news_custom_selection" type="radio" value="custom" class="tog" />
						<?php _e( 'Custom Base', 'vibe-customtypes' ); ?></label></th>
					<td>
						<input name="news_permalink_structure" id="vibe_news_permalink_structure" type="text" value="<?php echo esc_attr( $news_permalink ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Enter a custom base to use. A base <strong>must</strong> be set or WordPress will use default instead.', 'vibe-customtypes' ); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
		<?php }} ?>

		<h3><?php _e('Course Action End points','vibe-customtypes'); ?></h3>
		<p><?php _e('Course action points, these endpoints are appended to your course URLs to handle specific actions. They should be unique.','vibe-customtypes'); ?></p>
		<table class="form-table">	
			<tbody>
				<tr>
					<th><label><?php _e('Curriculum','vibe-customtypes'); ?></label></th>
					<td>
						<input name="curriculum_slug" type="text" value="<?php echo esc_attr( $curriculum_slug ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Course Curriculum slug', 'vibe-customtypes' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label><?php _e('Activity','vibe-customtypes'); ?></label></th>
					<td>
						<input name="activity_slug" type="text" value="<?php echo esc_attr( $activity_slug ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Course Activity slug', 'vibe-customtypes' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label><?php _e('Members','vibe-customtypes'); ?></label></th>
					<td>
						<input name="members_slug" type="text" value="<?php echo esc_attr( $members_slug ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Course Members slug', 'vibe-customtypes' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label><?php _e('Admin','vibe-customtypes'); ?></label></th>
					<td>
						<input name="admin_slug" type="text" value="<?php echo esc_attr( $admin_slug ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Course Admin slug', 'vibe-customtypes' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label><?php _e('Submissions','vibe-customtypes'); ?></label></th>
					<td>
						<input name="submissions_slug" type="text" value="<?php echo esc_attr( $submissions_slug ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Course Admin - Submissions slug', 'vibe-customtypes' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label><?php _e('Statistics','vibe-customtypes'); ?></label></th>
					<td>
						<input name="stats_slug" type="text" value="<?php echo esc_attr( $stats_slug ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Course Admin - Statistics slug', 'vibe-customtypes' ); ?></span>
					</td>
				</tr>
				<?php
				if(defined('WPLMS_NEWS_SLUG') && post_type_exists('news')){
					?>
					<tr>
						<th><label><?php _e('News','vibe-customtypes'); ?></label></th>
						<td>
							<input name="news_slug" type="text" value="<?php echo esc_attr( $news_slug ); ?>" class="regular-text code"> <span class="description"><?php _e( 'Course Admin - News slug', 'vibe-customtypes' ); ?></span>
						</td>
					</tr>
					<?php
				}
				?>
				<?php 
					do_action('wplms_course_action_point_permalink_settings');
				?>
			</tbody>
		</table>
		<script type="text/javascript">
			jQuery( function() {
				jQuery('input.base_to_go_course').change(function() { 
					jQuery('#vibe_course_permalink_structure').val( jQuery( this ).val() );
				});
				jQuery('#vibe_course_permalink_structure').focus( function(){
					jQuery('#vibe_course_permalink_structure').click();
				} );

				jQuery('input.base_to_go_quiz').change(function() {
					jQuery('#vibe_quiz_permalink_structure').val( jQuery( this ).val() );
				});
				jQuery('input.base_to_go_unit').change(function() {
					jQuery('#vibe_unit_permalink_structure').val( jQuery( this ).val() );
				});
				jQuery('input.base_to_go_news').change(function() {
					jQuery('#vibe_news_permalink_structure').val( jQuery( this ).val() );
				});
				
				jQuery('#vibe_quiz_permalink_structure').focus( function(){
					jQuery('#vibe_quiz_permalink_structure').click();
				} );
				jQuery('#vibe_unit_permalink_structure').focus( function(){
					jQuery('#vibe_unit_permalink_structure').click();
				} );
				jQuery('#vibe_news_permalink_structure').focus( function(){
					jQuery('#vibe_news_permalink_structure').click();
				} );
			} );
		</script>
		<?php
	}

	/**
	 * Save the settings.
	 */
	public function settings_save() {

		if ( ! is_admin() ) {
			return;
		}

		// We need to save the options ourselves; settings api does not trigger save for the permalinks page
		if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) && isset( $_POST['course_permalink'] ) ) {
			

			$permalinks = get_option( 'vibe_course_permalinks' );

			if ( ! $permalinks ) {
				$permalinks = array();
			}

			// Cat and tag bases
			$vibe_course_category_slug  = sanitize_text_field( $_POST['vibe_course_category_slug'] );

			// Product base
			$course_permalink = sanitize_text_field( $_POST['course_permalink'] );
			$quiz_permalink = sanitize_text_field( $_POST['quiz_permalink'] );
			$unit_permalink = sanitize_text_field( $_POST['unit_permalink'] );
			$news_permalink = sanitize_text_field( $_POST['news_permalink'] );

			if ( $course_permalink == 'custom' ) {
				// Get permalink without slashes
				$course_permalink = trim( sanitize_text_field( $_POST['course_permalink_structure'] ), '/' );

				// This is an invalid base structure and breaks pages
				if ( '%course-cat%' == $course_permalink ) {
					$course_permalink = _x( 'course', 'slug', 'vibe-customtypes' ) . '/' . $course_permalink;
				}

				// Prepending slash
				$course_permalink = '/' . $course_permalink;
			} elseif ( empty( $course_permalink ) ) {
				$course_permalink = false;
			}

			if ( $quiz_permalink == 'custom' ) {
				$quiz_permalink = trim( sanitize_text_field( $_POST['quiz_permalink_structure'] ), '/' );
				$quiz_permalink = '/' . $quiz_permalink;
			} elseif ( empty( $quiz_permalink ) ) {
				$quiz_permalink = false;
			}

			if ( $unit_permalink == 'custom' ) {
				$unit_permalink = trim( sanitize_text_field( $_POST['unit_permalink_structure'] ), '/' );
				$unit_permalink = '/' . $unit_permalink;
			} elseif ( empty( $unit_permalink ) ) {
				$unit_permalink = false;
			}

			if ( $news_permalink == 'custom' ) {
				$news_permalink = trim( sanitize_text_field( $_POST['news_permalink_structure'] ), '/' );
				$news_permalink = '/' . $news_permalink;
			} elseif ( empty( $news_permalink ) ) {
				$news_permalink = false;
			}

			$permalinks['course_category_base'] = untrailingslashit( $vibe_course_category_slug );
			$permalinks['course_base'] = untrailingslashit( $course_permalink );
			$permalinks['quiz_base'] = untrailingslashit( $quiz_permalink );
			$permalinks['unit_base'] = untrailingslashit( $unit_permalink );
			$permalinks['news_base'] = untrailingslashit( $news_permalink );


			$courses_page_id   = $this->courses_uri();
			$courses_permalink = ( $courses_page_id > 0 && get_post( $courses_page_id ) ) ? get_page_uri( $courses_page_id ) : _x( 'shop', 'default-slug', 'vibe-customtypes' );

			if ( $courses_page_id && trim( $permalinks['course_base'], '/' ) === $courses_permalink ) {
				$permalinks['use_verbose_page_rules'] = true;
			}

			if(!empty($_POST['curriculum_slug'])){
				$curriculum_slug = trim( sanitize_text_field( $_POST['curriculum_slug'] ), '/' );
				$curriculum_slug = '/' . $curriculum_slug;
				$permalinks['curriculum_slug'] = untrailingslashit( $curriculum_slug );
			}

			if(!empty($_POST['members_slug'])){
				$members_slug = trim( sanitize_text_field( $_POST['members_slug'] ), '/' );
				$members_slug = '/' . $members_slug;
				$permalinks['members_slug'] = untrailingslashit( $members_slug );
			}
			
			if(!empty($_POST['activity_slug'])){
				$activity_slug = trim( sanitize_text_field( $_POST['activity_slug'] ), '/' );
				$activity_slug = '/' . $activity_slug;
				$permalinks['activity_slug'] = untrailingslashit( $activity_slug );
			}

			if(!empty($_POST['admin_slug'])){
				$admin_slug = trim( sanitize_text_field( $_POST['admin_slug'] ), '/' );
				$admin_slug = '/' . $admin_slug;
				$permalinks['admin_slug'] = untrailingslashit( $admin_slug );
			}
			
			if(!empty($_POST['submissions_slug'])){
				$submissions_slug = trim( sanitize_text_field( $_POST['submissions_slug'] ), '/' );
				$submissions_slug = '/' . $submissions_slug;
				$permalinks['submissions_slug'] = untrailingslashit( $submissions_slug );
			}

			if(!empty($_POST['stats_slug'])){
				$stats_slug = trim( sanitize_text_field( $_POST['stats_slug'] ), '/' );
				$stats_slug = '/' . $stats_slug;
				$permalinks['stats_slug'] = untrailingslashit( $stats_slug );
			}

			if(!empty($_POST['news_slug'])){
				$news_slug = trim( sanitize_text_field( $_POST['news_slug'] ), '/' );
				if($news_slug == 'news' || $news_slug == '/news'){
					$news_slug = 'course-news';
				}
				$news_slug = '/' . $news_slug;
				$permalinks['news_slug'] = untrailingslashit( $news_slug );
			}

			$permalinks = apply_filters('wplms_save_vibe_course_permalinks',$permalinks);

			update_option( 'vibe_course_permalinks', $permalinks );
		}
	}
}

endif;

add_action('admin_init','initiate_vibe_permalinks');
function initiate_vibe_permalinks(){
	return new Vibe_CustomTypes_Admin_Permalink_Settings();	
}


class Vibe_CustomTypes_Permalinks{
	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_CustomTypes_Permalinks();

        return self::$instance;
    }

    private function __construct(){
    	if(empty($this->permalinks))
    		$this->permalinks = get_option( 'vibe_course_permalinks' );

    	$this->end_points = apply_filters('vibe_course_action_slugs',array('curriculum_slug','members_slug','activity_slug','admin_slug','submissions_slug','stats_slug','news_slug'));

    	add_action('init', array($this,'add_endpoints'));
    	add_filter( 'request', array($this,'filter_request' ));
		add_action( 'template_redirect', array($this,'catch_vars' ));
		add_filter('wplms_course_admin_slugs',array($this,'admin_slugs'));
    }

    function add_endpoints(){
    	if(!empty($this->permalinks)){
	        foreach($this->permalinks as $key => $item){
	            if(in_array($key,$this->end_points)){
	            	$item = str_replace('/','',$item);
	            	add_rewrite_endpoint($item, EP_ALL);    
	            }
	        }
	    }
    }

    function filter_request( $vars ){

    	if(empty($this->permalinks))
			$this->permalinks = get_option( 'vibe_course_permalinks' );

		if(!empty($this->permalinks)){
			foreach($this->permalinks  as $key => $item){
				$item = str_replace('/','',$item);

				if( isset( $vars[$item] ) && in_array($key,$this->end_points)) {
					if(empty($vars[$item])){
						$vars[$item] = true;	
						$vars[$key] = true;	
					}else{
						$vars[$vars[$item]] = true;
						$vars[$item] = true;
						$vars[$key] = true;
					}
				}
			}
		}
	    return $vars;
	}


	function catch_vars(){ 
		global $bp,$wp_query,$post;
		
		$course_id = $post->ID;
		
		if(empty($this->permalinks))
			$this->permalinks = get_option( 'vibe_course_permalinks' );

		if(!empty($this->permalinks) && is_object($bp)){
			
			if(!empty($this->permalinks) && !empty($this->permalinks['course_base'])){
				$this->permalinks['course_base'] = str_replace('/','',$this->permalinks['course_base']);
			}
			
			if( is_array($bp->unfiltered_uri) && isset($bp->unfiltered_uri[1]) && ($bp->unfiltered_uri[0] == $this->permalinks['course_base'] || $bp->unfiltered_uri[0] == BP_COURSE_SLUG)){

				$bp->current_component = BP_COURSE_SLUG;
				if(empty($course_id)){
					$posts = get_posts(array('post_type' => BP_COURSE_SLUG,
				    'posts_per_page' => 1,
				    'post_name__in'  => array($bp->unfiltered_uri[1])
				    ));
				    if(!empty($posts)){
				    	$course_id = $posts[0]->ID;	
				    	global $post;
				    	$post = $posts[0];	
				    }
				}else{
					$bp->current_item = $course_id;	
				}
		    	
				foreach($this->permalinks  as $key => $item){ 
					$item = str_replace('/','',$item);
					if( get_query_var( $item ) && in_array($key,$this->end_points)){ 
				        $bp->current_action = str_replace('_slug','',$key);
				    }
				}

				add_filter('body_class',function($class){$class[]='single-course';return $class;});
				if(function_exists('bp_get_template_part')){
					global $wp_query,$post,$withcomments;
					
					status_header( 200 );
					$wp_query->queried_object = $post;
					$wp_query->queried_object_id = $post->ID;
					$wp_query->is_single = true;
					$wp_query->is_404      = false;
					if($post->comments_status  == 'open'){
						$withcomments = true;
					}
					bp_get_template_part('course/single/home');
					exit;
				}
			}
		}
	}

	function admin_slugs($slug){

		$tips = WPLMS_tips::init();
		
		if(empty($tips->settings['revert_permalinks'])){
			$admin_slug = str_replace('/','',$this->permalinks['admin_slug']);
			switch($slug){
				case '?action=admin':
					$slug = $admin_slug;
				break;
				case '?action=admin&submissions':
					if(!empty($this->permalinks['submissions_slug'])){
						$slug = $admin_slug.$this->permalinks['submissions_slug'];
					}
				break;
				case '?action=admin&stats':
					if(!empty($this->permalinks['stats_slug'])){
						$slug = $admin_slug.$this->permalinks['stats_slug'];
					}
				break;
			}
		}
		return $slug;
	}
}


Vibe_CustomTypes_Permalinks::init();




//add_filter('post_type_link', 'wplms_unit_permalinks', 10, 3);
//add_filter('post_type_link', 'wplms_quiz_permalinks', 10, 3);

function wplms_unit_permalinks($permalink, $post, $leavename){
	$post_id = $post->ID;
	if($post->post_type != 'unit' || empty($permalink) || in_array($post->post_status, array('draft', 'pending', 'auto-draft')))
		return $permalink;
	global $wpdb;
	$course_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key= 'vibe_course_curriculum' AND meta_value LIKE %s LIMIT 1;", "%{$post_id}%" ) );
	if(is_numeric($course_id)){
		$slug = get_post_field('post_name',$course_id);
	}
	 
	if(empty($slug)) { $slug = WPLMS_UNIT_SLUG; }
	 
	$permalink = str_replace('%unitcourse%', $slug, $permalink);
	 
	return $permalink;
}


function wplms_quiz_permalinks($permalink, $post, $leavename){
	$post_id = $post->ID;
	if($post->post_type != 'quiz' || empty($permalink) || in_array($post->post_status, array('draft', 'pending', 'auto-draft')))
		return $permalink;
	global $wpdb;
	$course_id =  get_post_meta($post_id,'vibe_quiz_course',true);
	if(is_numeric($course_id)){
		$slug = get_post_field('post_name',$course_id);
	}
	 
	if(empty($slug)) { $slug = WPLMS_QUIZ_SLUG; }
	 
	$permalink = str_replace('%quizcourse%', $slug, $permalink);
	 
	return $permalink;
}



