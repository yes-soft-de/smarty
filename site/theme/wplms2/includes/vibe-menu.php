<?php

if ( !defined( 'ABSPATH' ) ) exit;


class vibe_menu {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {

		// add custom menu fields to menu
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'vibe_add_nav_fields' ) );

		// save menu custom fields
		add_action( 'wp_update_nav_menu_item', array( $this, 'vibe_update_nav_fields'), 10, 3 );
		
		// edit menu walker
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'vibe_edit_walker'), 10, 2 );

	} // end constructor
	
	
	
	/**
	 * Add custom fields to $item nav object
	 * in order to be used in custom Walker
	 *
	 * @access      public
	 * @since       1.0 
	 * @return      void
	*/
	function vibe_add_nav_fields( $menu_item ) {
	      $menu_item->megamenu_type = get_post_meta( $menu_item->ID, '_menu_item_megamenu_type', true );
        $menu_item->taxonomy = get_post_meta( $menu_item->ID, '_menu_item_taxonomy', true );
        $menu_item->hide_taxonomy_terms = get_post_meta( $menu_item->ID, '_menu_item_hide_taxonomy_terms', true );
	    	$menu_item->sidebar = get_post_meta( $menu_item->ID, '_menu_item_sidebar', true );
        $menu_item->max_elements = get_post_meta( $menu_item->ID, '_menu_item_max_elements', true );
        $menu_item->columns = get_post_meta( $menu_item->ID, '_menu_item_columns', true );
        $menu_item->menu_width = get_post_meta( $menu_item->ID, '_menu_item_menu_width', true );
        
	    return $menu_item;
	    
	}
	
	/**
	 * Save menu custom fields
	 *
	 * @access      public
	 * @since       1.0 
	 * @return      void
	*/
	function vibe_update_nav_fields( $menu_id, $menu_item_db_id, $args ) {
	
	    // Check if element is properly sent
	    if ( isset($_REQUEST['menu-item-sidebar']) && is_array( $_REQUEST['menu-item-sidebar']) ) {
	        $sidebar_value = $_REQUEST['menu-item-sidebar'][$menu_item_db_id];
	        update_post_meta( $menu_item_db_id, '_menu_item_sidebar', $sidebar_value );
	    }
      if ( isset($_REQUEST['menu-item-megamenu_type']) && is_array( $_REQUEST['menu-item-megamenu_type']) ) {
          $megamenu_type = $_REQUEST['menu-item-megamenu_type'][$menu_item_db_id];
          update_post_meta( $menu_item_db_id, '_menu_item_megamenu_type', $megamenu_type );
      }
	    if ( isset($_REQUEST['menu-item-columns']) && is_array( $_REQUEST['menu-item-columns']) ) {
	        $sidebar_columns = $_REQUEST['menu-item-columns'][$menu_item_db_id];
	        update_post_meta( $menu_item_db_id, '_menu_item_columns', $sidebar_columns );
	    }
      if ( isset($_REQUEST['menu-item-taxonomy']) && is_array( $_REQUEST['menu-item-taxonomy']) ) {
          $taxonomy = $_REQUEST['menu-item-taxonomy'][$menu_item_db_id];
          update_post_meta( $menu_item_db_id, '_menu_item_taxonomy', $taxonomy );
      }
      if ( isset($_REQUEST['menu-item-hide_taxonomy_terms']) && is_array( $_REQUEST['menu-item-hide_taxonomy_terms']) ) {
          $taxonomy = $_REQUEST['menu-item-hide_taxonomy_terms'][$menu_item_db_id];
          update_post_meta( $menu_item_db_id, '_menu_item_hide_taxonomy_terms', $taxonomy );
      }
      if ( isset($_REQUEST['menu-item-max_elements']) && is_array( $_REQUEST['menu-item-max_elements']) ) {
          $max_elements = $_REQUEST['menu-item-max_elements'][$menu_item_db_id];
          update_post_meta( $menu_item_db_id, '_menu_item_max_elements', $max_elements );
      }
      if ( isset($_REQUEST['menu-item-menu_width']) && is_array( $_REQUEST['menu-item-menu_width']) ) {
          $menu_width = $_REQUEST['menu-item-menu_width'][$menu_item_db_id];
          update_post_meta( $menu_item_db_id, '_menu_item_menu_width', $menu_width );
      }
	}
	
	/**
	 * Define new Walker edit
	 *
	 * @access      public
	 * @since       1.0 
	 * @return      void
	*/
	function vibe_edit_walker($walker,$menu_id) {
	
	    return 'Walker_Nav_Menu_Edit_Custom';
	    
	}

}

// instantiate plugin's class
$GLOBALS['vibe_menu'] = new vibe_menu();


include_once( 'menu/edit_custom_walker.php' );
include_once( 'menu/custom_walker.php' );




  /*====== WPLMS MENUS ======*/


  add_action( 'load-nav-menus.php', 'wplms_wp_nav_menu_meta_box' );

  function wplms_wp_nav_menu_meta_box(){
    add_meta_box( 'add-wplms-nav-menu', __( 'WPLMS', 'vibe' ), 'wplms_admin_do_wp_nav_menu_meta_box', 'nav-menus', 'side', 'default' );
  }

  function wplms_admin_do_wp_nav_menu_meta_box() {
    global $nav_menu_selected_id;

    $walker = new WPLMS_backend_menu( false );
    $args   = array( 'walker' => $walker );

    $post_type_name = 'wplms';

    $tabs = array();

    $tabs['membersonly']['label']  = __( 'Members-Only', 'vibe' );
    $tabs['membersonly']['pages']  = wplms_nav_menu_get_members_pages();

    $tabs['instructorsonly']['label'] = __( 'Instructors-Only', 'vibe' );
    $tabs['instructorsonly']['pages'] = wplms_nav_menu_get_instructors_pages();

    ?>

    <div id="wplms-menu" class="posttypediv">
      <h4><?php _e( 'Members-Only', 'vibe' ) ?></h4>
      <p><?php _e( '<em>Members-Only</em> These links are only visible to logged in users.', 'vibe' ) ?></p>

      <div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-loggedin" class="tabs-panel tabs-panel-active">
        <ul id="wplms-menu-checklist-loggedin" class="categorychecklist form-no-clear">
          <?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $tabs['membersonly']['pages'] ), 0, (object) $args );?>
        </ul>
      </div>

      <h4><?php _e( 'Instructors-Only', 'vibe' ) ?></h4>
      <p><?php _e( '<em>Instructors-Only</em> links are visible to Instructors.', 'vibe' ) ?></p>

      <div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-loggedout" class="tabs-panel tabs-panel-active">
        <ul id="buddypress-menu-checklist-loggedout" class="categorychecklist form-no-clear">
          <?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $tabs['instructorsonly']['pages'] ), 0, (object) $args );?>
        </ul>
      </div>

      <p class="button-controls">
        <span class="add-to-menu">
          <input type="submit"<?php if ( function_exists( 'wp_nav_menu_disabled_check' ) ) : wp_nav_menu_disabled_check( $nav_menu_selected_id ); endif; ?> class="button-secondary submit-add-to-menu right" value="<?php _e( 'Add to Menu', 'vibe' ); ?>" name="add-custom-menu-item" id="submit-wplms-menu" />
          <span class="spinner"></span>
        </span>
      </p>
    </div><!-- /#buddypress-menu -->

    <?php
  }

  function wplms_nav_menu_get_instructors_pages(){
  	$create_course_id = vibe_get_option('create_course');
  	$wplms_page_args = array(
    'edit_course'=>(object) array(
       'ID'             => $create_course_id,
      'post_title'     => __('Create/Edit Course page','vibe'),
      'post_type'      => 'page',
      'post_status'    => 'publish',
      'comment_status' => 'closed',
      'guid'           => get_permalink($create_course_id)
    )
  	);
 

  return $wplms_page_args;
  }
  
  function wplms_nav_menu_get_members_pages() {
	
	$start_course_id = vibe_get_option('take_course_page');
	$notes_discussion_id = vibe_get_option('unit_comments');

   $wplms_page_args = array(
    'dashboard'=>(object) array(
      'ID'             => -1,
      'post_title'     => __('Dashboard','vibe'),
      'post_excerpt'   => 'dashboard',
      'post_type'      => 'page',
      'post_status'    => 'publish',
      'comment_status' => 'closed',
      'guid'           => bp_loggedin_user_domain().'dashboard'
    ),
  	'my_courses'=>(object) array(
      'ID'             => -1,
      'post_title'     => __('My Courses','vibe'),
      'post_excerpt'   => BP_COURSE_SLUG,
      'post_type'      => 'page',
      'post_status'    => 'publish',
      'comment_status' => 'closed',
      'guid'           => bp_loggedin_user_domain().BP_COURSE_SLUG
    ),
    'start_course'=>(object) array(
      'ID'             => $start_course_id,
      'post_title'     => __('Start Course page','vibe'),
      'post_type'      => 'page',
      'post_status'    => 'publish',
      'comment_status' => 'closed',
      'guid'           => get_permalink($start_course_id)
    ),
    'unit_comments'=>(object) array(
      'ID'             => $notes_discussion_id,
      'post_title'     => __('Notes & Discussion page','vibe'),
      'post_type'      => 'page',
      'post_status'    => 'publish',
      'comment_status' => 'closed',
      'guid'           => get_permalink($notes_discussion_id)
    ),
  );
  return $wplms_page_args;
}