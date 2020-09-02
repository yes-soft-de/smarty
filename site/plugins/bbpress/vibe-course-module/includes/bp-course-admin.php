<?php
// Remaining file for later use FRONT END EDITING

if ( !defined( 'ABSPATH' ) ) exit;

class BP_Course_Admin{

	public static $instance;
	public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new BP_Course_Admin();
        return self::$instance;
    }

	public function __construct(){
		add_action('bbp_forum_metabox',array($this,'enable_course_connectivity'),10,1);
		add_action('bbp_forum_attributes_metabox_save',array($this,'save_support'),10,1);
	}

	function enable_course_connectivity($post_id){
		
		?>
		<hr>
		<p>
			<strong class="label"><?php esc_html_e( 'WPLMS Course', 'vibe' ); ?></strong>
			<label class="screen-reader-text" for="wplms_course_forum_select"><?php esc_html_e( '
			Forum for Course :', 'vibe' ) ?></label>
			<?php 
				$course_id = get_post_meta($post_id,'vibe_forum',true);
				$coruse_query = new WP_Query(array('post_type'=>'course','posts_per_page'=>-1)); 
				echo '<select name="vibe_forum" id="wplms_course_forum_select" data-id="'.$post_id.'" data-placeholder="'.__('Select a Course','vibe').'" data-cpt="course" class="selectcpt">';

				if(!empty($course_id)){
					echo '<option value="' . $course_id . '" selected="selected">' . get_the_title($course_id) . '</option>';
				}
				echo '</select>';
				wp_nonce_field('vibe_security','vibe_security');
			?>

		</p>
		<?php
	}

	function save_support($forum_id){
		if(isset($_POST['vibe_forum'])){
			update_post_meta($forum_id,'vibe_forum',$_POST['vibe_forum']);
		}else{
			delete_post_meta($forum_id,'vibe_forum');
		}
	}
}

BP_Course_Admin::init();