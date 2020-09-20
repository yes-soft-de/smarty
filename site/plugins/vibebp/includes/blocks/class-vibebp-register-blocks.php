<?php
/**
 * Register blocks.
 *
 * @package VibeBP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load registration for our blocks.
 *
 * @since 1.6.0
 */
class VibeBP_Register_Blocks {


	/**
	 * This plugin's instance.
	 *
	 * @var VibeBP_Register_Blocks
	 */
	private static $instance;

	/**
	 * Registers the plugin.
	 */
	public static function register() {
		if ( null === self::$instance ) {
			self::$instance = new VibeBP_Register_Blocks();
		}
	}

	/**
	 * The Plugin version.
	 *
	 * @var string $_slug
	 */
	private $_slug;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->counter = 0;
		$this->_slug = 'vibebp';

		add_action( 'init', array( $this, 'register_blocks' ), 99 );
		add_action( 'init', array($this,'vibebp_block_assets'));
		//add_filter( 'block_categories', array($this,'block_category'), 1, 2);


		// Hook: Frontend assets.
		add_action( 'enqueue_block_assets', array($this,'block_assets'),9 );

		add_action( 'enqueue_block_editor_assets', array($this,'editor_assets' ));
	}

	/**
	 * Add actions to enqueue assets.
	 *
	 * @access public
	 */
	public function register_blocks() {

		// Return early if this function does not exist.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Shortcut for the slug.
		$slug = $this->_slug;

		register_block_type(
			$slug . '/accordion',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
			)
		);
		register_block_type(
			$slug . '/alert',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
			)
		);
		register_block_type(
			$slug . '/author',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
			)
		);
		register_block_type(
			$slug . '/dynamic-separator',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
			)
		);
		register_block_type(
			$slug . '/gist',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
			)
		);
		register_block_type(
			$slug . '/highlight',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
			)
		);
		register_block_type(
			$slug . '/gallery-carousel',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
			)
		);
		register_block_type(
			$slug . '/gallery-masonry',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
			)
		);
		register_block_type(
			$slug . '/gallery-stacked',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
			)
		);

	}

	function get_profiile_fields_array(){
		$groups = bp_xprofile_get_groups( array(
			'fetch_fields' => true
		) );
 		$options_array = array(0=>array('value'=>0,'label'=>_x('Select field','','vibebp')));
 		if(!empty($groups)){
 			foreach($groups as $group){
			
				if ( !empty( $group->fields ) ) {
					//CHECK IF FIELDS ENABLED
					foreach ( $group->fields as $field ) {
						$field = xprofile_get_field( $field->id );
						$options_array[$field->id] = array(
							'value'=>$field->id,
							'label'=>$field->name,
							'admin_value'=>bp_get_profile_field_data(array('field'=>$field->id,'user_id'=>get_current_user_id()))
						);
						
					} // end for
					
				}
				
			}
 		}

 		return apply_filters('vibebp_get_profile_fields_array',$options_array);
	}

	function block_assets(){


		
    }



	function editor_assets() {

		/*wp_enqueue_script(
			'vibe-shortcodes-gutenblocks-js', // Handle.
			plugins_url( 'blocks.build.js', __FILE__ ), 
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), VIBE_SHORTCODES_VERSION,
			true 
		);*/

		$color_options = apply_filters('vibe_bp_gutenberg_text_color_options',array(

			array(
				'name'=>_x('Black','','vibebp'),
				'color'=>'#000000'
			),
			array(
				'name'=>_x('White','','vibebp'),
				'color'=>'#ffffff'
			),

		));

		$fontsizeunit_options = apply_filters('vibe_bp_gutenberg_text_color_options',array(
			'px','em','rem','pt','vh','vw','%'
		));

		$profile_field_styles = apply_filters('vibebp_profile_field_styles',array(
			array(
				'label' => _x('Basic','','vibebp'),
				'value' => 'basic'
			),
			array(
				'label' => _x('Stack','','vibebp'),
				'value' => 'stack'
			),
			array(
				'label' => _x('Justified','','vibebp'),
				'value' => 'justified'
			),
			array(
				'label' => _x('No label','','vibebp'),
				'value' => 'nolabel'
			),
		));

		$fontwieght_options = apply_filters('vibe_bp_gutenberg_text_color_options',array(
			'100','200','300','400','500','600','700','800','900'
		));

		$default_last_active = 0;
		$user_id = bp_displayed_user_id();
		if(!empty($user_id)){
			$default_last_active = $this->vibe_bp_get_user_last_activity( $user_id );
		}else{
			$user_id = get_current_user_id();
			$default_last_active = $this->vibe_bp_get_user_last_activity( $user_id );
		}

		$settings = apply_filters('vibe_bp_gutenberg_data',array(
			'default_avatar'=>plugins_url( '../../assets/images/avatar.jpg',  __FILE__ ),
			'default_profile_value'=>_x('default value','','vibebp'),
			'default_name'=>_x('default name','','vibebp'),
			'profile_fields' => (array)$this->get_profiile_fields_array(),
			'default_text_color_options' => $color_options,
			'fontsizeunit_options' => $fontsizeunit_options,
			'fontwieght_options'=>$fontwieght_options,
			'default_last_active' => $default_last_active,
			'profile_field_styles' => $profile_field_styles,
			'current_user'=>wp_get_current_user(),
			'api_url'=>home_url().'/wp-json/'.Vibe_BP_API_NAMESPACE,
		));

		wp_localize_script( 'vibebp-editor', 'vibe_bp_gutenberg_data', $settings );
		

		/*wp_enqueue_script(
			'vibe-shortcodes-gutenblocks-editor-js', // Handle.
			plugins_url( 'vibeshortcodes.blockeditor.js',  __FILE__ ), 
			array( 'jquery' ), VIBE_SHORTCODES_VERSION,
			true 
		);*/
	
		// Styles.
		/*wp_enqueue_style(
			'vibe-bp-gutenblocks-css', // Handle.
			plugins_url( '../../../assets/css/vibebp.blockseditor.css', __FILE__ ),
			array( 'wp-edit-blocks' ) ,VIBE_SHORTCODES_VERSION
		);*/
	}

	function block_category( $categories, $post ) {
		$categories[] = array(
							'slug' => 'vibe-bp',
							'title' => __( 'Vibe Buddypress', 'vibebp' ),
						);
		return $categories;
	}

	function vibebp_block_assets(){
		$this->_slug = 'vibebp';
		// Shortcut for the slug.
		$slug = $this->_slug;
		wp_register_style(
			'vibebp-style-css', // Handle.
			plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), 
			array( 'wp-editor' ), 
			null 
		);

		// Register block editor script for backend.
		wp_register_script(
			'vibebp-block-js', // Handle.
			plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
			null, 
			true 
		);

		// Register block editor styles for backend.
		wp_register_style(
			'vibebp-block-editor-css',
			plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ),
			array( 'wp-edit-blocks' ), 
			null 
		);


		register_block_type(
			'vibebp/bpavatar', 
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
			)
		);

		register_block_type(
			'vibebp/bpprofilefield', 
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
			)
		);

		register_block_type(
			'vibebp/displayname', 
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
			)
		);

		register_block_type(
			$slug . '/lastactive',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'lastactive')
			)
		);

		register_block_type(
			$slug . '/publicfriends',
			array(
				'editor_script' => $slug . '-editor',
				'editor_style'  => $slug . '-editor',
				'style'         => $slug . '-frontend',
				'render_callback' => array($this,'publicfriends')
			)
		);

	}

	function publicfriends($atts){

		if(!function_exists('friends_get_alphabetically'))
			return '';
		$styles = array(

			'font-size'=>$atts['customFontSize'].'px',
			'font-weight'=>$atts['fontWeight'],
			'color'=>$atts['font']['Color'],
			'font-family'=>$atts['fontFamily'],
			//'line-height'=>props.attributes.lineHeight,
			'letter-spacing'=>$atts['letterSpacing'].'px',
			'text-transform'=>$atts['textTransform']
		);

		$html = '';
		$stylestring = '';
		foreach ($styles as $key => $style) {
			$stylestring .= $key.':'.$style.';';
		}
		$time = 0;
		$user_id = bp_displayed_user_id();
		if(empty($user_id))
			$user_id = get_current_user_id();
		if(!empty($user_id)){

			$friends=friends_get_alphabetically($user_id,$atts['friendnumber'],1,'');
    		if( $friends['total'] ){
    			$html .= '<div class="publicfriends_member" style="'.$stylestring.'">';
    			foreach ($friends['users'] as $key => $friend) {
    				
    				$avatar = bp_core_fetch_avatar( array( 'item_id' => $friend->ID,'type'=>'full', 'html' => false ) );
    				$name = bp_core_get_user_displayname( $friend->ID );
    				$html .= '<div class="public_friend">
                 				<img src="'.$avatar.'" />
                 				'.($atts['showNames']?'<span>'.$name.'</span>':'').'
                 			</div>';
    			}
				
	               
	            $html .= '</div>';
			}
		}
		return  $html;
	}

	function lastactive($atts){
		$styles = array(

			'font-size'=>$atts['customFontSize'].'px',
			'font-weight'=>$atts['fontWeight'],
			'color'=>$atts['font']['Color'],
			'font-family'=>$atts['fontFamily'],
			//'line-height'=>props.attributes.lineHeight,
			'letter-spacing'=>$atts['letterSpacing'].'px',
			'text-transform'=>$atts['textTransform']
		);


		$stylestring = '';
		foreach ($styles as $key => $style) {
			$stylestring .= $key.':'.$style.';';
		}
		$time = 0;
		$user_id = bp_displayed_user_id();
		if(!empty($user_id)){
			$time = $this->vibe_bp_get_user_last_activity( $user_id );
		}else{
			$user_id = get_current_user_id();
			$time = $this->vibe_bp_get_user_last_activity( $user_id );
		}

		return '<div className="lastactive_member" style="'.$stylestring.'">
	                 <span className="bp_last_active"><label>'.$time.'</label></span>
	            </div>';

	}

	


	function vibe_bp_get_user_last_activity($user_id){
		ob_start();
		bp_last_activity($user_id);
		$html = ob_get_clean();
		return $html;
	}
	
}

VibeBP_Register_Blocks::register();
