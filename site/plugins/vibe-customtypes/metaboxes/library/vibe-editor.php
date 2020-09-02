<?php
/**
 * Action functions for Course Module
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Vibe CUSTOM TYPES
 * @version     2.2
 */

 if ( ! defined( 'ABSPATH' ) ) exit;

class WPLMS_Page_Builder{

    public static $instance;

    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_Page_Builder();

        return self::$instance;
    }

    private function __construct(){
        add_action( 'init', array($this,'layout_editor'));
        add_action('init',array($this,'v_new_modules_init'));
        add_action( 'wp_enqueue_scripts', array($this,'vibe_builder_enqueue_shortcode_scripts'),99);

        /* AJAX CALLS */
        add_action( 'wp_ajax_add_slider_item', array($this,'v_add_slider_item'));
        add_action( 'wp_ajax_delete_layout', array($this,'delete_layout'));
        add_action( 'wp_ajax_show_module_options', array($this,'new_show_module_options'));
        add_action( 'wp_ajax_show_column_options', array($this,'new_show_column_options'));
        add_action( 'wp_ajax_save_layout',array($this,'save_layout' ));
        add_action( 'wp_ajax_append_layout', array($this,'new_append_layout' ));       
        add_action( 'wp_ajax_save_new_layout', array($this,'save_new_layout' ));
        /*== SHORTCODES ==*/
        add_shortcode('v_carousel', array($this,'custom_post_carousel'));
        add_shortcode('v_member_carousel', array($this,'member_carousel'));
        add_shortcode('v_member_grid', array($this,'member_grid'));


        add_shortcode('v_taxonomy_carousel', array($this,'taxonomy_carousel'));
        add_shortcode('v_taxonomy_grid', array($this,'taxonomy_grid'));
        add_shortcode('v_filterable', array($this,'custom_post_filterable'));
        add_shortcode('v_slider', array($this,'vibe_custom_slider'));
        add_shortcode('v_slides', array($this,'custom_attachment'));
        add_shortcode('v_grid', array($this,'vibe_post_grid'));
        add_shortcode('v_layerslider', array($this,'vibe_layerslider'));
        add_shortcode('v_featured_block', array($this,'vibe_featured_block'));
        add_shortcode('v_revslider', array($this,'vibe_revslider'));
        add_shortcode('v_text_block', array($this,'vibe_text_block'));
        add_shortcode('v_parallax_block', array($this,'vibe_parallax_block'));
        add_shortcode('v_widget_area', array($this,'new_widget_area'));
    
        
        add_shortcode('v_groups_carousel', array($this,'group_carousel'));
        add_shortcode('v_groups_grid', array($this,'group_gird'));
        /* ADMIN  PART */
        add_action( 'before_page_builder', array($this,'add_content_option' ));
        add_filter( 'the_content', array($this,'show_builder_layout'));
        add_action('wp_ajax_get_vibe_builder',array($this,'vibe_yoast_get_vibe_builder'));
        add_action( 'save_post', array($this,'vibe_editor_save_details'), 10, 2 );   
        add_action( 'before_page_builder', array($this,'disable_builder_option' ));
    }

    function layout_editor(){
        add_action( 'admin_enqueue_scripts', array($this,'v_scripts_styles'), 10, 1 );
    }

    function v_scripts_styles( $hook ) {
        if ( in_array( $hook, array( 'post-new.php', 'post.php' ) ) ){
            if( (isset($_GET['post_type']) && ($_GET['post_type'] == 'page')) || (isset($_GET['post']) && (get_post_type($_GET['post']) == 'page'))){
                $this->v_new_settings_page_js();
                $this->v_new_settings_page_css();
            }
        }
    }


    function v_add_slider_item(){
        if ( ! wp_verify_nonce( $_POST['load_nonce'], 'load_nonce' ) ) die(-1);
        
        $attachment_class = $_POST['attachment_class'];
        $change_image = (bool) $_POST['change_image'];

        preg_match( '/wp-image-([\d])+/', $attachment_class, $matches );
        $attachment_id = str_replace( 'wp-image-', '', $matches[0] );
        $attachment_image = wp_get_attachment_image( $attachment_id );
        
        if ( $change_image ) {
          echo json_encode( array( 'attachment_image' => $attachment_image, 'attachment_id' => $attachment_id ) );
        } else {
          echo '<div class="attachment clearfix" data-attachment="' . esc_attr( $attachment_id ) .'">' 
              . $attachment_image
              . '<div class="attachment_options">'
                . '<p class="clearfix">' . '<label>' . esc_html__('Description (HTML & Shortcodes allowed)', 'vibe-customtypes') . ': </label>' . '<textarea name="attachment_description[]" class="attachment_description"></textarea> </p>'
                . '<p class="clearfix">' . '<label>' . esc_html__('Link', 'vibe-customtypes') . ': </label>'. '<input name="attachment_link[]" class="attachment_link" /> </p>'
              . '</div>'
              . '<a href="#" class="delete_attachment">' . esc_html__('Delete this slide', 'vibe-customtypes') . '</a>'
              . '<a href="#" class="change_attachment_image">' . esc_html__('Change image', 'vibe-customtypes') . '</a>'
            . '</div>';
        }
        
        die();
    }
        

    function delete_layout(){
        $name = stripslashes($_POST['name']);
        $value = get_option('vibe_builder_sample_layouts');
        if(isset($value)){
                    
            if(is_string($value))
                $value=  unserialize($value);
                    
            for($i=0;$i<count($value);$i++){
                if($name == $value[$i]['name']){
                    unset($value[$i]);
                    $value = array_values($value);
                        $value=serialize($value);
                        update_option('vibe_builder_sample_layouts',$value);
                    }
                }
        }
        die();
    }
            

  function new_show_module_options(){
    if ( ! wp_verify_nonce( $_POST['load_nonce'], 'load_nonce' ) ) die(-1);
    
    $module_class = $_POST['module_class'];
    $v_module_exact_name = $_POST['module_exact_name'];
    $module_window = (int) $_POST['modal_window'];
    
    preg_match( '/m_([^\s])+/', $module_class, $matches );
    $module_name = str_replace( 'm_', '', $matches[0] );
    
    $paste_to_editor_id = isset( $_POST['paste_to_editor_id'] ) ? $_POST['paste_to_editor_id'] : '';
    
    $this->generate_module_options( $module_name, $module_window, $paste_to_editor_id, $v_module_exact_name );
    
    die();
  }


  function new_show_column_options(){
    if ( ! wp_verify_nonce( $_POST['load_nonce'], 'load_nonce' ) ) die(-1);
    
    $module_class = $_POST['et_module_class'];
    
    preg_match( '/m_column_([^\s])+/', $module_class, $matches );
    $module_name = str_replace( 'm_column_', '', $matches[0] );
    
    $paste_to_editor_id = isset( $_POST['paste_to_editor_id'] ) ? $_POST['paste_to_editor_id'] : '';
    
    $this->generate_column_options( $module_name, $paste_to_editor_id );
    
    die();
  }



function custom_post_carousel($atts, $content = null) {

    $error = new VibeErrors();
    if(!isset($atts) || !isset($atts['post_type'])){
      return $error->get_error('unsaved_editor');
    }
       
     
    $attributes = $this->v_get_attributes( $atts, "custom_post_carousel" );

    if(!isset($atts['auto_slide']))
        $atts['auto_slide']='';

    if(!empty($atts['custom_css']) && strlen($atts['custom_css'])>5)    
        $output = '<style>'.$atts['custom_css'].'</style>';
    else
        $output= '';

	$output .= "<div {$attributes['class']}{$attributes['inline_styles']}>";

	if(!isset($atts['post_ids']) || strlen($atts['post_ids']) < 2){
        
        if(isset($atts['term']) && isset($atts['taxonomy']) && $atts['term'] !='nothing_selected'){
            
            if(!empty($atts['taxonomy'])){ 
                
                if(strpos($atts['term'], ',') === false){
                    $check=term_exists($atts['term'], $atts['taxonomy']);
                    if($atts['term'] !='nothing_selected'){    
                        if ($check == 0 || $check == null || !$check) {
                            $error = new VibeErrors();
                            $output .= $error->get_error('term_taxonomy_mismatch');
                            $output .='</div>';
                            return $output;
                       } 
                    }
                }    
                $check=is_object_in_taxonomy($atts['post_type'], $atts['taxonomy']);
                if ($check == 0 || $check == null || !$check) {
                    $error = new VibeErrors();
                    $output .= $error->get_error('term_postype_mismatch');
                    $output .='</div>';
                    return $output;
               }

                $terms = $atts['term'];
                if(strpos($terms,',') !== false){
                    $terms = explode(',',$atts['term']);
                } 
            }

            
            $query_args=array( 'post_type' => $atts['post_type'],'posts_per_page' => $atts['carousel_number']);
            if(!empty($atts['taxonomy'])){ 
              $query_args['tax_query'] = array(
                  'relation' => 'AND',
                  array(
                      'taxonomy' => $atts['taxonomy'],
                      'field'    => 'slug',
                      'terms'    => $terms,
                  ),
              );
            }
        }else{
           $query_args=array('post_type'=>$atts['post_type'], 'posts_per_page' => $atts['carousel_number']);
        }
        
        if($atts['post_type'] == 'course' && isset($atts['course_style'])){
            switch($atts['course_style']){
                case 'popular':
                  $query_args['orderby'] = 'meta_value_num';
                  $query_args['meta_key'] = 'vibe_students';
                break;
                case 'featured':
                  if(empty($query_args['meta_query'])){
                    $query_args['meta_query'] = array(
                              array(
                                  'key'     => 'featured',
                                  'value'   => 1,
                                  'compare' => '>='
                              )
                          );
                  }else{
                    $query_args['meta_query'][] = array(
                                  'key'     => 'featured',
                                  'value'   => 1,
                                  'compare' => '>='
                          );
                  }
                break;
                case 'rated':
                  $query_args['orderby'] = 'meta_value_num';
                  $query_args['meta_key'] = 'average_rating';
                break;
                case 'reviews':
                  $query_args['orderby'] = 'comment_count';
                break;
                case 'start_date':
                  $query_args['orderby'] = 'meta_value';
                  $query_args['meta_key'] = 'vibe_start_date';
                  $query_args['meta_type'] = 'DATE';
                  $query_args['order'] = 'ASC';
                  $today = date('Y-m-d');
                  if(empty($query_args['meta_query'])){
                    $query_args['meta_query'] = array(
                              array(
                                  'key'     => 'vibe_start_date',
                                  'value'   => $today,
                                  'compare' => '>='
                              )
                          );
                  }else{
                    $query_args['meta_query'][] = array(
                                  'key'     => 'vibe_start_date',
                                  'value'   => $today,
                                  'compare' => '>='
                          );
                  }
                  
                break;
                case 'expired_start_date':
                  $query_args['orderby'] = 'meta_value';
                  $query_args['meta_key'] = 'vibe_start_date';
                  $query_args['meta_type'] = 'DATE';
                  $query_args['order'] = 'ASC';
                  $today = date('Y-m-d');
                  if(empty($query_args['meta_query'])){
                    $query_args['meta_query'] = array(
                              array(
                                  'key'     => 'vibe_start_date',
                                  'value'   => $today,
                                  'compare' => '<'
                              )
                          );
                  }else{
                    $query_args['meta_query'][] = array(
                                  'key'     => 'vibe_start_date',
                                  'value'   => $today,
                                  'compare' => '<'
                          );
                  }
                  
                break;
                case 'random':
                   $query_args['orderby'] = 'rand';
                break;
                case 'free':
                 if(empty($query_args['meta_query'])){
                  $query_args['meta_query'] =  array(
                      array(
                        'key'     => 'vibe_course_free',
                        'value'   => 'S',
                        'compare' => '=',
                      ),
                    );
                }else{
                  $query_args['meta_query'][] =  array(
                        'key'     => 'vibe_course_free',
                        'value'   => 'S',
                        'compare' => '=',
                    );
                }
                break;
                default:
                  $query_args['orderby'] = '';
            }
            if(empty($query_args['order']))
              $query_args['order'] = 'DESC';

            $query_args =  apply_filters('wplms_carousel_course_filters',$query_args);
        }
        
        $the_query = new WP_Query($query_args);

        }else{

          $cus_posts_ids=explode(",",$atts['post_ids']);
        	$query_args=array( 'post_type' => $atts['post_type'], 'post__in' => $cus_posts_ids , 'orderby' => 'post__in','posts_per_page'=>count($cus_posts_ids)); 
        	$the_query = new WP_Query($query_args);
        }
        
        

        if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
            $ntitle= $atts['title'];
            $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
            $ntitle = str_replace("'", '', $ntitle);
            $output .='<div id="'.$ntitle.'"></div>';
        }
        
        $more= '';
        if(isset($atts['show_more']) && $atts['show_more']) {
            $more = ' <a href="'.$atts['more_link'].'" class="heading_more">+</a>';
        }
        $noheading='';
        
        if($atts['show_title'])
            $output .='<h3 class="heading"><span>'.$atts['title'].'</span></h3>'.$more;
        else
            $noheading='noheading';
        

        $class='slides';
        
        if(empty($rand))
          $rand = rand(0,999);
        
        if(empty($atts['show_controlnav']))
          $atts['show_controlnav'] = 0;
        
        $output .= '<div id="'.$rand.'" class="vibe_carousel flexslider loading '.(($atts['carousel_max']==1)?'onecol':'').' '.$noheading.' '.((isset($atts['show_more']) && $atts['show_more'])?'more_heading':'').'" data-directionnav="'.(empty($atts['show_controls'])?0:$atts['show_controls']).'" data-controlnav="'.$atts['show_controlnav'].'" data-block-width="'.$atts['column_width'].'" data-block-max="'.$atts['carousel_max'].'" data-block-min="'.$atts['carousel_min'].'" data-block-move="'.(empty($atts['carousel_move'])?$atts['carousel_min']:$atts['carousel_move']).'" data-autoslide="'.$atts['auto_slide'].'">
  	            <ul class="'.$class.'">';
  	     $links='';
         $excerpt='';
         $thumb='';
         
         
         if($atts['column_width'] < 311)
             $cols = 'small';
         
         if(($atts['column_width'] >= 311) && ($atts['column_width'] < 460))    
             $cols='medium';
         
         if(($atts['column_width'] >= 460) && ($atts['column_width'] < 769))    
             $cols='big';
         
         if($atts['column_width'] >= 769)    
             $cols='full';

        if( $the_query->have_posts() ) {
          
        while ( $the_query->have_posts() ) : $the_query->the_post();
        global $post;
        $output .= '<li>';
        $output .= thumbnail_generator($post,$atts['featured_style'],$cols,$atts['carousel_excerpt_length']);
        $output .= '</li>';
        endwhile;
        }else{
          $error = new VibeErrors();
          $output .= $error->get_error('no_posts');
        }
        wp_reset_postdata();
        $output .= "</ul></div></div>";

	   return $output;
    }

    /*
    TAXONOMY CAROUSEL
     */
    function taxonomy_carousel($atts,$content=null){


        $attributes = $this->v_get_attributes( $atts, "taxonomy_carousel" );
        if(!isset($atts['auto_slide']))
            $atts['auto_slide']='';

        if($atts['custom_css'] && strlen($atts['custom_css'])>5)    
            $output = '<style>'.$atts['custom_css'].'</style>';
        else
            $output= '';

        $output .= "<div {$attributes['class']}{$attributes['inline_styles']}>";
        if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
            $ntitle= $atts['title'];
            $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
            $ntitle = str_replace("'", '', $ntitle);
            $output .='<div id="'.$ntitle.'"></div>';
        }
        
        $more= '';
        if(isset($atts['show_more']) && $atts['show_more']) {
            $more = ' <a href="'.$atts['more_link'].'" class="heading_more">+</a>';
        }
        $noheading='';
        
        if(!empty($atts['show_title']))
            $output .='<h3 class="heading"><span>'.$atts['title'].'</span></h3>'.$more;
        else
            $noheading='noheading';
        

        $class='slides';
        
        if(empty($rand))
          $rand = rand(0,999);

        if(!isset($atts['orderby']))
          $atts['orderby'] = '';
        if(!isset($atts['order']))
          $atts['order'] = '';

        $output .= '<div id="'.$rand.'" class="vibe_carousel flexslider loading '.(($atts['carousel_max']==1)?'onecol':'').' '.$noheading.' '.((isset($atts['show_more']) && $atts['show_more'])?'more_heading':'').'" data-directionnav="'.(Empty($atts['show_controls'])?0:$atts['show_controls']).'" data-controlnav="'.(empty($atts['show_controlnav'])?0:$atts['show_controlnav']).'" data-block-width="'.$atts['column_width'].'" data-block-max="'.$atts['carousel_max'].'" data-block-min="'.$atts['carousel_min'].'" data-block-move="'.(empty($atts['carousel_move'])?$atts['carousel_min']:$atts['carousel_move']).'" data-autoslide="'.$atts['auto_slide'].'">
                <ul class="'.$class.'">';

            if(!empty($atts['term_slugs'])){
                $slugs = explode(',',$atts['term_slugs']);
                if(!empty($slugs))
                    $args = array('taxonomy'=>'course-cat','include'=>$slugs,'number'=>$atts['carousel_number'],'orderby'=>$atts['orderby'],'order'=>$atts['order'],'meta_key'=>'course_cat_order');
            }else{
               $args = array('taxonomy'=>'course-cat','number'=>$atts['carousel_number'],'orderby'=>$atts['orderby'],'order'=>$atts['order'],'meta_key'=>'course_cat_order');
            }
            
            if(empty($args['include'])){unset($args['include']);}
            if(empty($args['orderby'])){unset($args['orderby']);unset($args['order']);unset($args['meta_key']);}

            $terms = get_terms($args);
            if(!empty($terms)){
                foreach($terms as $term){ 
                    $thumbnail_id = get_term_meta( $term->term_id, 'course_cat_thumbnail_id', true );
                    if ( $thumbnail_id ) {
                        $image = wp_get_attachment_image_src( $thumbnail_id,'medium' );
                        $image=$image[0];
                    } else {
                        $image = vibe_get_option('default_avatar');
                        if(empty($image)){
                            $image = VIBE_URL.'/assets/images/avatar.jpg';
                        }
                    }
                    $output .= '<li><a href="'.get_term_link($term).'" class="term_block"><img src="'.$image.'"><strong class="term_name"><span>'.$term->name.'</span></strong></a></li>';
                }    
            }else{
                $output.='<li><div class="message">'._x('No terms found.','no user found in selection in page builder member block','vibe-customtypes').'</div></li>';
            }

        $output .= "</ul></div></div>";

       return $output;

    }
    /*
    TAXONOMY Grid
     */
    function taxonomy_grid($atts,$content=null){


        $attributes = $this->v_get_attributes( $atts, "taxonomy_carousel" );
        if(!isset($atts['auto_slide']))
            $atts['auto_slide']='';

        if($atts['custom_css'] && strlen($atts['custom_css'])>5)    
            $output = '<style>'.$atts['custom_css'].'</style>';
        else
            $output= '';

        $output .= "<div {$attributes['class']}{$attributes['inline_styles']}>";
        if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
            $ntitle= $atts['title'];
            $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
            $ntitle = str_replace("'", '', $ntitle);
            $output .='<div id="'.$ntitle.'"></div>';
        }
        
        $more= '';
        if(isset($atts['show_more']) && $atts['show_more']) {
            $more = ' <a href="'.$atts['more_link'].'" class="heading_more">+</a>';
        }
        $noheading='';
        
        if(!empty($atts['show_title']))
            $output .='<h3 class="heading"><span>'.$atts['title'].'</span></h3>'.$more;
        else
            $noheading='noheading';
        

        $class='slides';
        
        if(empty($rand))
          $rand = rand(0,999);

        if(!isset($atts['orderby']))
          $atts['orderby'] = '';
        if(!isset($atts['order']))
          $atts['order'] = '';

        $output .= '<div id="'.$rand.'" class="vibe_carousel flexslider loading '.(($atts['carousel_max']==1)?'onecol':'').' '.$noheading.' '.((isset($atts['show_more']) && $atts['show_more'])?'more_heading':'').'" data-directionnav="'.(empty($atts['show_controls'])?0:$atts['show_controls']).'" data-controlnav="'.(empty($atts['show_controlnav'])?0:$atts['show_controlnav']).'" data-block-width="'.$atts['column_width'].'" data-block-max="'.$atts['carousel_max'].'" data-block-min="'.$atts['carousel_min'].'" data-block-move="'.(empty($atts['carousel_move'])?$atts['carousel_min']:$atts['carousel_move']).'" data-autoslide="'.$atts['auto_slide'].'">
                <ul class="'.$class.'">';

            if(!empty($atts['term_slugs'])){
                $slugs = explode(',',$atts['term_slugs']);
                if(!empty($slugs))
                    $args = array('taxonomy'=>'course-cat','include'=>$slugs,'number'=>$atts['carousel_number'],'orderby'=>$atts['orderby'],'order'=>$atts['order'],'meta_key'=>'course_cat_order');
            }else{
               $args = array('taxonomy'=>'course-cat','number'=>$atts['carousel_number'],'orderby'=>$atts['orderby'],'order'=>$atts['order'],'meta_key'=>'course_cat_order');
            }
            
            if(empty($args['include'])){unset($args['include']);}
            if(empty($args['orderby'])){unset($args['orderby']);unset($args['order']);unset($args['meta_key']);}

            $terms = get_terms($args);
            if(!empty($terms)){
                foreach($terms as $term){ 
                    $thumbnail_id = get_term_meta( $term->term_id, 'course_cat_thumbnail_id', true );
                    if ( $thumbnail_id ) {
                        $image = wp_get_attachment_image_src( $thumbnail_id,'medium' );
                        $image=$image[0];
                    } else {
                        $image = vibe_get_option('default_avatar');
                        if(empty($image)){
                            $image = VIBE_URL.'/assets/images/avatar.jpg';
                        }
                    }
                    $output .= '<li><a href="'.get_term_link($term).'" class="term_block"><img src="'.$image.'"><strong class="term_name"><span>'.$term->name.'</span></strong></a></li>';
                }    
            }else{
                $output.='<li><div class="message">'._x('No terms found.','no user found in selection in page builder member block','vibe-customtypes').'</div></li>';
            }

        $output .= "</ul></div></div>";

       return $output;

    }
    /*
    MEMBER CAROUSEL
     */
    function member_carousel($atts,$content=null){
         $attributes = $this->v_get_attributes( $atts, "member_carousel" );

        if(!isset($atts['auto_slide']))
            $atts['auto_slide']='';

        if(!empty($atts['custom_css']) && strlen($atts['custom_css'])>5)    
            $output = '<style>'.$atts['custom_css'].'</style>';
        else
            $output= '';

        $output .= "<div {$attributes['class']}{$attributes['inline_styles']}>";
        if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
            $ntitle= $atts['title'];
            $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
            $ntitle = str_replace("'", '', $ntitle);
            $output .='<div id="'.$ntitle.'"></div>';
        }
        
        $more= '';
        if(isset($atts['show_more']) && $atts['show_more']) {
            $more = ' <a href="'.$atts['more_link'].'" class="heading_more">+</a>';
        }
        $noheading='';
        
        if($atts['show_title'])
            $output .='<h3 class="heading"><span>'.$atts['title'].'</span></h3>'.$more;
        else
            $noheading='noheading';
        

        $class='slides';
        
        if(empty($rand))
          $rand = rand(0,999);

        $output .= '<div id="'.$rand.'" class="vibe_carousel flexslider loading '.(($atts['carousel_max']==1)?'onecol':'').' '.$noheading.' '.((isset($atts['show_more']) && $atts['show_more'])?'more_heading':'').'" data-directionnav="'.$atts['show_controls'].'" data-controlnav="'.$atts['show_controlnav'].'" data-block-width="'.$atts['column_width'].'" data-block-max="'.$atts['carousel_max'].'" data-block-min="'.$atts['carousel_min'].'" data-block-move="'.(empty($atts['carousel_move'])?$atts['carousel_min']:$atts['carousel_move']).'" data-autoslide="'.$atts['auto_slide'].'">
                <ul class="'.$class.'">';
            if(!empty($atts['member_ids'])){
                $users = explode(',',$atts['member_ids']);
                if(!empty($users))
                    $user_query = new WP_User_Query(array('include'=>$users));
            }else{
                switch($atts['member_type']){
                    case 'student':
                    case 'instructor':
                        $user_query = new WP_User_Query(array('role'=>$atts['member_type'],'number'=>$atts['carousel_number']));
                    break;
                    default:
                        $user_query = new WP_User_Query(array('number'=>$atts['carousel_number']));
                    break;
                }
            }
            $user_ids = $user_query->get_results();
            $field_names = array();
            if(!empty($user_ids)){
                $field_names = explode(',',$atts['profile_fields']);

                foreach($user_ids as $user){ 
                    $member = vibe_member_block($user,$atts['style'],$field_names,$atts['column_width'],$atts['carousel_link']);
                    $output .= '<li>'.$member.'</li>';
                }    
            }else{
                $output.='<li><div class="message">'._x('No user found.','no user found in selection in page builder member block','vibe-customtypes').'</div></li>';
            }

        $output .= "</ul></div></div>";

       return $output;

    }

    /*
    MEMBER GRID
     */
    function member_grid($atts,$content=null){
         $attributes = $this->v_get_attributes( $atts, "member_grid" );


        if(!empty($atts['custom_css']) && strlen($atts['custom_css'])>5)    
            $output = '<style>'.$atts['custom_css'].'</style>';
        else
            $output= '';

        $output .= "<div {$attributes['class']}{$attributes['inline_styles']}>";
        if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
            $ntitle= esc_attr($atts['title']);
            $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
            $ntitle = str_replace("'", '', $ntitle);
            $output .='<div id="'.$ntitle.'"></div>';
        }
        
        
        
        if($atts['show_title']){
            if(preg_match('/<\s?[^\>]*\/?\s?>/i', $atts['title'])){
              $output .= $atts['title'];  
            }else{
              $output .='<h3 class="heading"><span>'.$atts['title'].'</span></h3>';
            }
        }
        

        
        if(empty($rand))
          $rand = rand(0,999);

        $class='member_grid '.$atts['grid_layout'].' '.$atts['block_size'].' '.$atts['gutter'];

        /*
               
            'member_type' => array(
              'title' => __('Member Type', 'vibe-customtypes'),
              'type' => 'select',
              'options' => $v_member_types,
              'std' =>''
            ),
            
            'exclude_member_ids' => array(
              'title' => __('Exclude Specific Member Ids (comma saperated)', 'vibe-customtypes'),
              'type' => 'text',
              'std'=>''
            ),             
            'style' => array(
                    'title' => __('Display Style', 'vibe-customtypes'),
                'type' => 'radio_images',
                'options' => apply_filters('vibe_builder_cmember_styles',array(
                            ''=> plugins_url('images/member_block1.jpg',__FILE__),
                            'member2'=> plugins_url('images/member_block2.jpg',__FILE__),
                            'member3'=> plugins_url('images/member_block1.jpg',__FILE__),
                            'member4'=> plugins_url('images/member_block4.jpg',__FILE__),
                        )),
                'std'=>''
              ),  

            'grid_layout' => array(
                'title' => __('Grid Layout', 'vibe-customtypes'),
                'type' => 'radio_images',
                'options' => apply_filters('vibe_builder_grid_layouts',array(
                            ''=> plugins_url('images/grid-1.png',__FILE__),
                            'grid2'=> plugins_url('images/grid-2.png',__FILE__),
                            'grid3'=> plugins_url('images/grid-3.png',__FILE__),
                            'grid4'=> plugins_url('images/grid-4.png',__FILE__),
                            'grid5'=> plugins_url('images/grid-5.png',__FILE__),
                            'grid6'=> plugins_url('images/grid-6.png',__FILE__),
                            'grid7'=> plugins_url('images/grid-7.jpg',__FILE__),
                        )),
                'std'=>''
              ),   

            'block_size' => array(
               'title' => __('Block Size (in px) Or number of blocks in 1 row based on smallest block size ', 'vibe-customtypes'),
               'type' => 'text',
               'std' => 3
            ), 

            'gutter' => array(
              'title' => __('Spacing between Columns (in px)', 'vibe-customtypes'),
              'type' => 'text',
              'std' => '0'
            ),             
            'grid_number' => array(
              'title' => __('Total Number of Blocks in Grid', 'vibe-customtypes'),
              'type' => 'text',
              '
          */

        $output .= '<style>#member_grid'.$rand.' .member_grid{';
        if($atts['block_size']>10){
           $output .='grid-template-columns:repeat(auto-fill,minmax('.$atts['block_size'].'px, 1fr));';
        }else{
           $output .='grid-template-columns:repeat('.$atts['block_size'].',1fr);';
        }
        

        $output .= 'grid-gap:'.$atts['gutter'].'px;}';

        switch($atts['grid_layout']){
          case 'grid2':
            $output .= '.member_grid>li:first-child{grid-column-end:span 2;}';
            $output .= '.member_grid>li:last-child{grid-column-end:span 2;}';
          break;
          case 'grid3':
            $output .= '.member_grid>li:first-child{grid-column-end:span 2;}';
            $output .= '.member_grid>li:nth-child(2){grid-row-end:span 2;}';
          break;
          case 'grid4':
          if($atts['block_size']>10){
            $size = '3';
          }else{
            $size = $atts['block_size'];
          }

            $output .= '.member_grid>li:first-child{grid-column-end:span  '.($size-1).';}';
            $output .= '.member_grid>li:nth-child(2){grid-column-end:span  '.($size).';}';
          break;
          case 'grid5':
             $output .= '.member_grid>li:first-child{grid-column-end:span 2;}';
              $output .= '.member_grid>li:nth-child(2){grid-row-end:span  2;}';
             $output .= '.member_grid>li:last-child{grid-column-end:span 2;}';
          break;
          case 'grid6':
             $output .= '.member_grid>li:first-child{grid-column-end:span 2;}';
          break;
          case 'grid7':
            $output .= '.member_grid>li:first-child{grid-row-end:span 2;}';
            $output .= '.member_grid>li:last-child{grid-row-end:span 2;}';
          break;
        }

        $output .='</style><div id="member_grid'.$rand.'">
                <ul class="'.$class.'">';
            if(!empty($atts['member_ids'])){
                $users = explode(',',$atts['member_ids']);
                if(!empty($users))
                    $user_query = new WP_User_Query(array('include'=>$users));
            }else{
                switch($atts['member_type']){
                    case 'student':
                    case 'instructor':
                        $user_query = new WP_User_Query(array('role'=>$atts['member_type'],'number'=>$atts['grid_number']));
                    break;
                    default:
                    
                        $user_query = new WP_User_Query(array('number'=>$atts['carousel_number']));
                    break;
                }
            }
            $user_ids = $user_query->get_results();
            $field_names = array();
            if(!empty($user_ids)){
                $field_names = explode(',',$atts['profile_fields']);

                foreach($user_ids as $user){ 
                    $member = vibe_member_block($user,$atts['style'],$field_names,$atts['column_width'],$atts['carousel_link']);
                    $output .= '<li>'.$member.'</li>';
                }    
            }else{
                $output.='<li><div class="message">'._x('No user found.','no user found in selection in page builder member block','vibe-customtypes').'</div></li>';
            }

        $output .= "</ul></div></div>";

       return $output;

    }


    /*
    GROUPS CAROUSEL
     */
    function group_carousel($atts,$content=null){
         $attributes = $this->v_get_attributes( $atts, "member_carousel" );

        if(!isset($atts['auto_slide']))
            $atts['auto_slide']='';

        if(!empty($atts['custom_css']) && strlen($atts['custom_css'])>5)    
            $output = '<style>'.$atts['custom_css'].'</style>';
        else
            $output= '';

        $output .= "<div {$attributes['class']}{$attributes['inline_styles']}>";
        if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
            $ntitle= $atts['title'];
            $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
            $ntitle = str_replace("'", '', $ntitle);
            $output .='<div id="'.$ntitle.'"></div>';
        }
        
        $more= '';
        if(isset($atts['show_more']) && $atts['show_more']) {
            $more = ' <a href="'.$atts['more_link'].'" class="heading_more">+</a>';
        }
        $noheading='';
        
        if($atts['show_title'])
            $output .='<h3 class="heading"><span>'.$atts['title'].'</span></h3>'.$more;
        else
            $noheading='noheading';
        

        $class='slides';
        
        if(empty($rand))
          $rand = rand(0,999);

        $output .= '<div id="'.$rand.'" class="vibe_carousel flexslider loading '.(($atts['carousel_max']==1)?'onecol':'').' '.$noheading.' '.((isset($atts['show_more']) && $atts['show_more'])?'more_heading':'').'" data-directionnav="'.$atts['show_controls'].'" data-controlnav="'.$atts['show_controlnav'].'" data-block-width="'.$atts['column_width'].'" data-block-max="'.$atts['carousel_max'].'" data-block-min="'.$atts['carousel_min'].'" data-block-move="'.(empty($atts['carousel_move'])?$atts['carousel_min']:$atts['carousel_move']).'" data-autoslide="'.$atts['auto_slide'].'">
                <ul class="'.$class.'">';
            if(!empty($atts['member_ids'])){
                $users = explode(',',$atts['member_ids']);
                if(!empty($users))
                    $user_query = new WP_User_Query(array('include'=>$users));
            }else{
                switch($atts['member_type']){
                    case 'student':
                    case 'instructor':
                        $user_query = new WP_User_Query(array('role'=>$atts['member_type'],'number'=>$atts['carousel_number']));
                    break;
                    default:
                        $user_query = new WP_User_Query(array('number'=>$atts['carousel_number']));
                    break;
                }
            }
            $user_ids = $user_query->get_results();
            $field_names = array();
            if(!empty($user_ids)){
                $field_names = explode(',',$atts['profile_fields']);

                foreach($user_ids as $user){ 
                    $member = vibe_member_block($user,$atts['style'],$field_names,$atts['column_width'],$atts['carousel_link']);
                    $output .= '<li>'.$member.'</li>';
                }    
            }else{
                $output.='<li><div class="message">'._x('No user found.','no user found in selection in page builder member block','vibe-customtypes').'</div></li>';
            }

        $output .= "</ul></div></div>";

       return $output;

    }

    /*
    GROUP GRID
     */
    function group_grid($atts,$content=null){
         $attributes = $this->v_get_attributes( $atts, "member_carousel" );

        if(!isset($atts['auto_slide']))
            $atts['auto_slide']='';

        if(!empty($atts['custom_css']) && strlen($atts['custom_css'])>5)    
            $output = '<style>'.$atts['custom_css'].'</style>';
        else
            $output= '';

        $output .= "<div {$attributes['class']}{$attributes['inline_styles']}>";
        if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
            $ntitle= $atts['title'];
            $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
            $ntitle = str_replace("'", '', $ntitle);
            $output .='<div id="'.$ntitle.'"></div>';
        }
        
        $more= '';
        if(isset($atts['show_more']) && $atts['show_more']) {
            $more = ' <a href="'.$atts['more_link'].'" class="heading_more">+</a>';
        }
        $noheading='';
        
        if($atts['show_title'])
            $output .='<h3 class="heading"><span>'.$atts['title'].'</span></h3>'.$more;
        else
            $noheading='noheading';
        

        $class='slides';
        
        if(empty($rand))
          $rand = rand(0,999);

        $output .= '<div id="'.$rand.'" class="vibe_carousel flexslider loading '.(($atts['carousel_max']==1)?'onecol':'').' '.$noheading.' '.((isset($atts['show_more']) && $atts['show_more'])?'more_heading':'').'" data-directionnav="'.$atts['show_controls'].'" data-controlnav="'.$atts['show_controlnav'].'" data-block-width="'.$atts['column_width'].'" data-block-max="'.$atts['carousel_max'].'" data-block-min="'.$atts['carousel_min'].'" data-block-move="'.(empty($atts['carousel_move'])?$atts['carousel_min']:$atts['carousel_move']).'" data-autoslide="'.$atts['auto_slide'].'">
                <ul class="'.$class.'">';
            if(!empty($atts['member_ids'])){
                $users = explode(',',$atts['member_ids']);
                if(!empty($users))
                    $user_query = new WP_User_Query(array('include'=>$users));
            }else{
                switch($atts['member_type']){
                    case 'student':
                    case 'instructor':
                        $user_query = new WP_User_Query(array('role'=>$atts['member_type'],'number'=>$atts['carousel_number']));
                    break;
                    default:
                        $user_query = new WP_User_Query(array('number'=>$atts['carousel_number']));
                    break;
                }
            }
            $user_ids = $user_query->get_results();
            $field_names = array();
            if(!empty($user_ids)){
                $field_names = explode(',',$atts['profile_fields']);

                foreach($user_ids as $user){ 
                    $member = vibe_member_block($user,$atts['style'],$field_names,$atts['column_width'],$atts['carousel_link']);
                    $output .= '<li>'.$member.'</li>';
                }    
            }else{
                $output.='<li><div class="message">'._x('No user found.','no user found in selection in page builder member block','vibe-customtypes').'</div></li>';
            }

        $output .= "</ul></div></div>";

       return $output;

    }

    function custom_post_filterable($atts, $content = null) {
        if(!wp_script_is('isotope','enqueued')){
            wp_enqueue_script( 'isotope', VIBE_URL.'/assets/js/old_files/jquery.isotope.min.js');
        }   
            

        $error = new VibeErrors();
        if(!isset($atts) || !isset($atts['post_type'])){
          return $error->get_error('unsaved_editor');
        }
       
        
        $attributes = $this->v_get_attributes( $atts, "custom_post_filterable" );
        
        if(!empty($atts['custom_css']) && strlen($atts['custom_css'])>5)    
            $output = '<style>'.$atts['custom_css'].'</style>';
        else
            $output= '';
       
       $output .= "<div {$attributes['class']}{$attributes['inline_styles']}>";
  
        if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
            $ntitle= $atts['title'];
            $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
            $ntitle = str_replace("'", '', $ntitle);
            $output .='<div id="'.$ntitle.'"></div>';
        }
        if(isset($atts['taxonomy']) && $atts['taxonomy']!=''){ 
          $check=is_object_in_taxonomy($atts['post_type'], $atts['taxonomy']);
          if ($check == 0 || $check == null || !$check) {
            $error = new VibeErrors();
            $output .= $error->get_error('term_postype_mismatch');
            $output .='</div>';
            return $output;
          }
        }
         
         if($atts['column_width'] < 311)
             $cols = 'small';
         
         if(($atts['column_width'] >= 311) && ($atts['column_width'] < 460))    
             $cols='medium';
         
         if(($atts['column_width'] >= 460) && ($atts['column_width'] < 769))    
             $cols='big';
         
         if($atts['column_width'] >= 769)    
             $cols='full';
         
        global $paged,$wp_query;
        
        $query_args=array('post_type'=>$atts['post_type'], 'posts_per_page' => intval($atts['filterable_number']));
        
        if($atts['show_pagination']){
           global $paged;
          $paged = get_query_var('paged') ? get_query_var('paged') : 1;
          if ( get_query_var('paged') ) {
              $paged = get_query_var('paged');
          } elseif ( get_query_var('page') ) {
              $paged = get_query_var('page');
          } else {
              $paged = 1;
          }
          $query_args['paged']=$paged;  
        }
        
        $cat_order = array();
        $temp_query = $wp_query;
        $wp_query = null;

        if($atts['post_type'] == 'course'){
            switch($atts['course_style']){
                case 'popular':
                  $query_args['orderby'] = 'meta_value_num';
                  $query_args['meta_key'] = 'vibe_students';
                break;
                case 'featured':
                  if(empty($query_args['meta_query'])){
                    $query_args['meta_query'] = array(
                              array(
                                  'key'     => 'featured',
                                  'value'   => 1,
                                  'compare' => '>='
                              )
                          );
                  }else{
                    $query_args['meta_query'][] = array(
                                  'key'     => 'featured',
                                  'value'   => 1,
                                  'compare' => '>='
                          );
                  }
                break;
                case 'rated':
                  $query_args['orderby'] = 'meta_value_num';
                  $query_args['meta_key'] = 'average_rating';
                break;
                case 'reviews':
                  $query_args['orderby'] = 'comment_count';
                break;
                case 'start_date':
                  $query_args['orderby'] = 'meta_value';
                  $query_args['meta_key'] = 'vibe_start_date';
                  $query_args['meta_type'] = 'DATE';
                  $query_args['order'] = 'ASC';
                  $today = date('Y-m-d');
                  if(empty($query_args['meta_query'])){
                    $query_args['meta_query'] = array(
                              array(
                                  'key'     => 'vibe_start_date',
                                  'value'   => $today,
                                  'compare' => '>='
                              )
                          );
                  }else{
                    $query_args['meta_query'][] = array(
                                  'key'     => 'vibe_start_date',
                                  'value'   => $today,
                                  'compare' => '>='
                          );
                  }
                  
                break;
                case 'expired_start_date':
                  $query_args['orderby'] = 'meta_value';
                  $query_args['meta_key'] = 'vibe_start_date';
                  $query_args['meta_type'] = 'DATE';
                  $query_args['order'] = 'ASC';
                  $today = date('Y-m-d');
                  if(empty($query_args['meta_query'])){
                    $query_args['meta_query'] = array(
                              array(
                                  'key'     => 'vibe_start_date',
                                  'value'   => $today,
                                  'compare' => '<'
                              )
                          );
                  }else{
                    $query_args['meta_query'][] = array(
                                  'key'     => 'vibe_start_date',
                                  'value'   => $today,
                                  'compare' => '<'
                          );
                  }
                  
                break;
                case 'random':
                   $query_args['orderby'] = 'rand';
                break;
                case 'free':
                 if(empty($query_args['meta_query'])){
                  $query_args['meta_query'] =  array(
                      array(
                        'key'     => 'vibe_course_free',
                        'value'   => 'S',
                        'compare' => '=',
                      ),
                    );
                }else{
                  $query_args['meta_query'][] =  array(
                        'key'     => 'vibe_course_free',
                        'value'   => 'S',
                        'compare' => '=',
                    );
                }
                break;
            }
        }

        $query_args=apply_filters('vibe_editor_filterable_type',$query_args);
        query_posts($query_args);
        
        if($atts['show_title'])
        $output .='<h3 class="heading"><span>'.$atts['title'].'</span></h3>';
        
        $output .= '<div class="filterable_columns">
            <ul class="vibe_filterable">';
        if($atts['show_all'])                
        $output .='<li class="active"><a href="javascript:void();" data-filter="*" class="all">'.__('All','vibe-customtypes').'</a></li>';
        
        $exclude_terms = explode(',',$atts['exclude_terms']);

        while ( have_posts() ) : the_post();
        global $post;
        $cats=get_the_terms($post->ID,$atts['taxonomy']);
       
        if(is_array($cats))
        foreach($cats as $cat){
          if(!in_array($cat->slug,$exclude_terms)){
            $slug = str_replace('%','',$cat->slug);
            $categories[$post->ID][]=$slug;
            $all_categories[$slug]=$cat->name;
            if($atts['taxonomy'] == 'course-cat'){
              $cat_order[$slug]=(empty($cat->term_group)?0:$cat->term_group);
            }
          }
        }
        endwhile;
        wp_reset_query();
        if($atts['taxonomy'] == 'course-cat'){
          arsort($cat_order);
        }
        if(is_Array($all_categories)){
          $all_categories=  array_unique($all_categories);
          if($atts['taxonomy'] == 'course-cat'){
            foreach($cat_order as $slug=>$order){
              if(!in_array($slug,$exclude_terms)){
                $output .='<li><a href="javascript:void();" data-filter=".'.$slug.'">'.$all_categories[$slug].'</a></li>';
              }
            }
          }else{
            foreach($all_categories as $slug=>$order){
              if(!in_array($slug,$exclude_terms)){
                $output .='<li><a href="javascript:void();" data-filter=".'.$slug.'">'.$all_categories[$slug].'</a></li>';
              }
            }
          }
        }
        $output .='</ul><div class="filterableitems_container">';

        $wp_query = null;
        query_posts($query_args);
        while ( have_posts() ) : the_post();
            global $post;
            $classes = '';
            if(isset($categories[$post->ID]) && is_Array($categories[$post->ID])){
              $classes = '';
              foreach($categories[$post->ID] as $cat){
                if(is_array($cat))
                  $cat = implode(' ',$cat);

                $classes .= $cat.' ';
              }
              
            }
            $output .='<div class="filteritem '.$classes.'" style="max-width:'.$atts['column_width'].'px;width:100%;">'; 
            $output .= thumbnail_generator($post,$atts['featured_style'],$cols,$atts['filterable_excerpt_length']);
             $output .='</div>';
        endwhile;
           
            $output .='</div>';
             if($atts['show_pagination']) {
                    ob_start(); 
                    pagination();
                    $output .= ob_get_contents();
                    ob_end_clean();
                }
            $output .='</div>';
            
            $output .='</div>';
            wp_reset_query();
            $wp_query = $temp_query;
       
  return $output;
}

/*==== FlexSlider ====*/

function vibe_custom_slider($atts, $content) {
       extract(shortcode_atts(array(
				'title' => '',
        'slide_style' =>'slide1',
        'animation' => "fade",
        'auto_slide' => 1,
        'loop' => 1,
        'randomize' => 1,
        'show_directionnav'=>1,
        'show_controlnav' => 1,
        'animation_duration' => 700,
        'auto_speed' => 7000,
        'pause_on_hover' =>1 ,
        'css_class' => '',
        'custom_css' => '',
        'container_css' => ''
			), $atts));
       if(!empty($atts['custom_css']) && strlen($atts['custom_css'])>5)    
            $output = '<style>'.$atts['custom_css'].'</style>';
        else
            $output= '';

       wp_enqueue_script( 'flexslider-js', VIBE_URL.'/js/jquery.flexslider-min.js');

       $title = preg_replace('/[^a-zA-Z0-9\']/', '_', $title);
       $title = str_replace("'", '', $title).rand(1,999);;
       echo '<script>jQuery(document).ready(function(){
         jQuery("#'.$title.'").flexslider({
           animation:"'.$animation.'",
           animationLoop:'.(($loop)?'true':'false').',
           smoothHeight: true,
           slideshow:'.(($auto_slide)?'true':'false').',
           slideshowSpeed:'.$auto_speed.',
           animationSpeed:'.$animation_duration.',
           randomize : '.(($randomize)? 'true':'false').',
           directionNav: '.(($show_directionnav)? 'true':'false').',
           controlNav: '.(($show_controlnav)? 'true':'false').',
           pauseOnHove: '.(($pause_on_hover)? 'true':'false').',   
           prevText: \'<i class="icon-arrow-1-left"></i>\',
           nextText: \'<i class="icon-arrow-1-right"></i>\'    
           });
        });</script>';
        $attributes = $this->v_get_attributes( $atts, "custom_slider" );
	$output .= "<div {$attributes['class']}{$attributes['inline_styles']}>";
        if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
            $ntitle= $atts['title'];
            $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
            $ntitle = str_replace("'", '', $ntitle);
            $output .='<div id="'.$ntitle.'"></div>';
        }
        
        $output .= '<div id="'.$title.'" class="image_slider '.$slide_style.'">';
        $output .= '<ul class="slides">';
        $output .= do_shortcode($content);
        $output .= "</ul>";
        $output .= "</div>";
        $output .= "</div>";
       return $output;
    }

    function custom_attachment($atts, $content) {
       extract(shortcode_atts(array(
				'attachment_id' => '',
				'link' => ''
			), $atts));
       if(isset($attachment_id) && $attachment_id){
       $image = wp_get_attachment_image_src( $attachment_id, 'full' );
       $output  = '<li>';
       $output .= '<a href="'.$link.'">';
       $output .= '<img src="'.$image[0].'" />';
       $output .= '</a>';
       $output .= ($content)?'<div class="flex-caption">'.html_entity_decode($content).'</div>':'';
       $output .= '</li>';
       return $output;
       }
    }

    function vibe_post_grid($atts, $content = null) {
       
       wp_enqueue_script( 'isotope', VIBE_URL.'/assets/js/old_files/jquery.isotope.min.js');

        $error = new VibeErrors();
        if(!isset($atts) || !isset($atts['post_type'])){
          return $error->get_error('unsaved_editor');
        }
       
       
	     $attributes = $this->v_get_attributes( $atts, "vibe_post_grid" );
	
        if(isset($atts['masonry']) && $atts['masonry']){
            $atts['custom_css'] .= '.grid.masonry li .block { margin-bottom:'.(isset($atts['gutter'])?$atts['gutter']:'30').'px;}';
        }  
        
        if($atts['custom_css'] && strlen($atts['custom_css'])>5)    
            $output = '<style>'.$atts['custom_css'].'</style>';
        else
            $output= '';
        
	$output .= "<div {$attributes['class']}{$attributes['inline_styles']}>";
        
	if(!isset($atts['post_ids']) || strlen($atts['post_ids']) < 2){
        
        if(isset($atts['term']) && isset($atts['taxonomy']) && $atts['term'] !='nothing_selected'){
            
            if(isset($atts['taxonomy']) && $atts['taxonomy']!=''){ 
                
                if(strpos($atts['term'], ',') === false){    
                    $check=term_exists($atts['term'], $atts['taxonomy']);
                    if($atts['term'] !='nothing_selected'){    
                        if ($check == 0 || $check == null || !$check) {
                            $error = new VibeErrors();
                            $output .= $error->get_error('term_taxonomy_mismatch');
                            $output .='</div>';
                            return $output;
                        } 
                    }
                }

                $check=is_object_in_taxonomy($atts['post_type'], $atts['taxonomy'])  ;
                if ($check == 0 || $check == null || !$check) {
                    $error = new VibeErrors();
                    $output .= $error->get_error('term_postype_mismatch');
                    $output .='</div>';
                    return $output;
                }
            }


            if($atts['column_width'] < 311)
             $cols = 'small';
         
         if(($atts['column_width'] >= 311) && ($atts['column_width'] < 460))    
             $cols='medium';
         
         if(($atts['column_width'] >= 460) && ($atts['column_width'] < 769))    
             $cols='big';
         
         if($atts['column_width'] >= 769)    
             $cols='full';
         
            if(isset($atts['taxonomy']) && $atts['taxonomy']!=''){
                if($atts['taxonomy'] == 'tag'){
                    $atts['taxonomy']='tag_name'; 
                }   
            }
           
            
            $terms = $atts['term'];
            if(strpos($terms,',') !== false){
                $terms = explode(',',$atts['term']);
            }
            $query_args=array( 'post_type' => $atts['post_type'],'posts_per_page' => $atts['grid_number']);
            if(!empty($atts['taxonomy'])){
              $query_args['tax_query'] = array(
                  'relation' => 'AND',
                  array(
                      'taxonomy' => $atts['taxonomy'],
                      'field'    => 'slug',
                      'terms'    => $terms,
                  ),
              ); 
            }      
        }else{
            $query_args=array('post_type'=>$atts['post_type'], 'posts_per_page' => $atts['grid_number']);
        }
           
        
        

        if($atts['post_type'] == 'course'){
            switch($atts['course_style']){
                case 'popular':
                  $query_args['orderby'] = 'meta_value_num';
                  $query_args['meta_key'] = 'vibe_students';
                break;
                case 'featured':
                  if(empty($query_args['meta_query'])){
                    $query_args['meta_query'] = array(
                              array(
                                  'key'     => 'featured',
                                  'value'   => 1,
                                  'compare' => '>='
                              )
                          );
                  }else{
                    $query_args['meta_query'][] = array(
                                  'key'     => 'featured',
                                  'value'   => 1,
                                  'compare' => '>='
                          );
                  }
                break;
                case 'rated':
                  $query_args['orderby'] = 'meta_value_num';
                  $query_args['meta_key'] = 'average_rating';
                break;
                case 'reviews':
                  $query_args['orderby'] = 'comment_count';
                break;
                case 'start_date':
                  $query_args['orderby'] = 'meta_value';
                  $query_args['meta_key'] = 'vibe_start_date';
                  $query_args['meta_type'] = 'DATE';
                  $query_args['order'] = 'ASC';
                  $today = date('Y-m-d');
                  if(empty($query_args['meta_query'])){
                    $query_args['meta_query'] = array(
                              array(
                                  'key'     => 'vibe_start_date',
                                  'value'   => $today,
                                  'compare' => '>='
                              )
                          );
                  }else{
                    $query_args['meta_query'][] = array(
                                  'key'     => 'vibe_start_date',
                                  'value'   => $today,
                                  'compare' => '>='
                          );
                  }
                  
                break;
                case 'expired_start_date':
                  $query_args['orderby'] = 'meta_value';
                  $query_args['meta_key'] = 'vibe_start_date';
                  $query_args['meta_type'] = 'DATE';
                  $query_args['order'] = 'ASC';
                  $today = date('Y-m-d');
                  if(empty($query_args['meta_query'])){
                    $query_args['meta_query'] = array(
                              array(
                                  'key'     => 'vibe_start_date',
                                  'value'   => $today,
                                  'compare' => '<'
                              )
                          );
                  }else{
                    $query_args['meta_query'][] = array(
                                  'key'     => 'vibe_start_date',
                                  'value'   => $today,
                                  'compare' => '<'
                          );
                  }
                  
                break;
                case 'random':
                   $query_args['orderby'] = 'rand';
                break;
                case 'free':
                 if(empty($query_args['meta_query'])){
                  $query_args['meta_query'] =  array(
                      array(
                        'key'     => 'vibe_course_free',
                        'value'   => 'S',
                        'compare' => '=',
                      ),
                    );
                }else{
                  $query_args['meta_query'][] =  array(
                        'key'     => 'vibe_course_free',
                        'value'   => 'S',
                        'compare' => '=',
                    );
                }
                break;
            }
            $query_args =  apply_filters('wplms_grid_course_filters',$query_args);
        }

        }else{
          $cus_posts_ids=explode(",",$atts['post_ids']);
        	$query_args=array( 'post_type' => $atts['post_type'], 'post__in' => $cus_posts_ids,'posts_per_page'=>$atts['grid_number'] ); 
        }
        global $paged;
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;
        if ( get_query_var('paged') ) {
            $paged = get_query_var('paged');
        } elseif ( get_query_var('page') ) {
            $paged = get_query_var('page');
        } else {
            $paged = 1;
        }

        if(isset($atts['pagination']) && $atts['pagination']){
                  $query_args['paged']=$paged;       
               }
        $istyle='';       
        
        query_posts($query_args);

        $masonry=$style=$rel='';
        if(isset($atts['masonry']) && $atts['masonry']){
            $atts['grid_columns'] =' grid-item';
            $style= 'style="width:'.$atts['column_width'].'px;"'; 
            $masonry= 'masonry';
            $istyle .= ' data-width="'.$atts['column_width'].'" data-gutter="'.(isset($atts['gutter'])?$atts['gutter']:'30').'"';// Rel-width used in Masonry+infinite scroll
        }else{
          $cols = $atts['grid_columns'];
        }
        $infinite='';
        if(isset($atts['infinite']) && $atts['infinite']){
            $infinite=' inifnite_scroll';
            $paged = get_query_var('paged') ? get_query_var('paged') : 1;
            $rel = 'data-page='.$paged;
        }
        
        if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
            $ntitle= $atts['title'];
            $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
            $ntitle = str_replace("'", '', $ntitle);
            $output .='<div id="'.$ntitle.'"></div>';
        }
        
        global $wp_query;
        if($atts['show_title']){
        $output .='<h3 class="heading"><span>'.$atts['title'].'</span></h3>'; 
        }
        $output .= '<div class="vibe_grid '.$infinite.' '.$masonry.'" '.$rel.'><div class="wp_query_args" data-max-pages="'.$wp_query->max_num_pages.'">'.  json_encode($atts).'</div>';
  	 if(empty($masonry)){
        $output.= '<style>ul.grid{margin:0 -15px;}</style>';
     }


        if( have_posts() ) {
        
        $output .= '<ul class="grid '.$masonry.'" '.$istyle.'>';
        
        while ( have_posts() ) : the_post();
        global $post;
        
        
        $output .= '<li class="'.$atts['grid_columns'].'" '.$style.'>';
        $output .= thumbnail_generator($post,$atts['featured_style'],$cols,$atts['grid_excerpt_length'],$atts['grid_link']);
        $output .= '</li>';
        
        endwhile;
       
        $output .= '</ul>';
        }else{
          $error = new VibeErrors();
          $output .= $error->get_error('no_posts');
        }
        wp_reset_postdata();
        $output .= '</div>';
        
        if(isset($atts['infinite']) && $atts['infinite']){
            $output .= '<div class="load_grid"><span>'.__('Loading..','vibe-customtypes').'</span></div>
                        <div class="end_grid"><span>'.__('No more to load','vibe-customtypes').'</span></div>';
        }
        $output .="</div>";
        if(isset($atts['pagination']) && $atts['pagination']){
        ob_start();
        pagination();
        $output .=ob_get_contents();
        ob_end_clean();
        }
        wp_reset_query();
        wp_reset_postdata();
	   return $output;
    }



    function vibe_layerslider($atts, $content = null) {

       if(isset($atts['custom_css']) && strlen($atts['custom_css'])>5)    
            $output = '<style>'.$atts['custom_css'].'</style>';
        else
            $output= '';
       $attributes = $this->v_get_attributes( $atts, "layerslider" );
       $output .= "<div {$attributes['class']}{$attributes['inline_styles']}>";

        if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
            $ntitle= $atts['title'];
            $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
            $ntitle = str_replace("'", '', $ntitle);
            $output .='<div id="'.$ntitle.'"></div>';
        }
       
        if(isset($atts['id'])) 
            $output .=do_shortcode('[layerslider id="'.$atts['id'].'"]');
       
        $output .= '</div>';
	   return $output;
    }

    function vibe_revslider($atts, $content = null) {
           if(!empty($atts['custom_css']))    
                $output = '<style>'.$atts['custom_css'].'</style>';
            else
                $output= '';

           $attributes = $this->v_get_attributes( $atts, "revslider" );
           $output .= "<div {$attributes['class']}{$attributes['inline_styles']}>";
           
            if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
                $ntitle= $atts['title'];
                $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
                $ntitle = str_replace("'", '', $ntitle);
                $output .='<div id="'.$ntitle.'"></div>';
            }        
           $output .=do_shortcode('[rev_slider '.$atts['alias'].']');
           $output .= '</div>';
    	return $output;
    }


    function vibe_featured_block($atts, $content = null) {
           if(!empty($atts['custom_css']))    
                $output = '<style>'.$atts['custom_css'].'</style>';
            else
                $output= '';

           $attributes = $this->v_get_attributes( $atts, "vibe_featured_block" );
           $output .= "<div {$attributes['class']}{$attributes['inline_styles']}>";
           
            if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
                $ntitle= $atts['title'];
                $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
                $ntitle = str_replace("'", '', $ntitle);
                $output .='<div id="'.$ntitle.'"></div>';
            }        
            $rand = rand(1,999);
            if(empty($atts['style'])){
              $style = '';
           }else{
              $style = $atts['style'];
           }
           $output .= '<style>
           #featured_block'.$rand.'{
              '.(empty($atts['padding_top'])?'':'padding-top:'.$atts['padding_top'].'px;').'
              '.(empty($atts['padding_bottom'])?'':'padding-bottom:'.$atts['padding_bottom'].'px;').'
              '.(empty($atts['background_color'])?'':'background:'.$atts['background_color'].';').'
              '.(empty($atts['text_color'])?'':'color:'.$atts['text_color'].';').'';
              if(!empty($atts['border'])){
                $output .= 'border:'.$atts['border_width'].'px solid '.$atts['border_color'].';padding-left:30px;padding-right:30px;';
              }
            $output .= '}
            #featured_block'.$rand.' img.vstyle_box{'.(empty($atts['icon_size'])?'':'width:'.$atts['icon_size'].'px;').'}
            #featured_block'.$rand.' .fa{ 
              '.(empty($atts['icon_size'])?'':'font-size:'.$atts['icon_size'].'px;').'
              '.(empty($atts['icon_color'])?'':'color:'.$atts['icon_color'].';').'
            }
           </style>';
           $output  .= '<div id="featured_block'.$rand.'" class="featured_block '.$style.'">'; 

           switch($style){
              case 'style':

                $output .='<style> #featured_block'.$rand.'{
                  text-align:center;
                  '.(empty($atts['icon_size'])?'':'margin-top:'.$atts['icon_size'].'px;').'
                }
                #featured_block'.$rand.' .vstyle_box{position:absolute;
                '.(empty($atts['icon_size'])?'':'top:-'.round(($atts['icon_size']/2),0).'px;').'
                left:50%;
                '.(empty($atts['icon_size'])?'':'margin-left:-'.round(($atts['icon_size']/2),0).'px;').'
                }</style>';
                if(!empty($atts['image'])){
                  $output .='<img class="vstyle_box" src="'.$atts['image'].'"/>';  
                }
                if(!empty($atts['icon'])){
                  $output .='<span class="vstyle_box fa '.$atts['icon'].'"></span>';  
                }
                $output .= do_shortcode($content);
              break;
              case 'side':
                $output .='<div class="row">';
                $output .='<div class="col-md-4">';
                if(!empty($atts['image'])){
                  $output .='<img class="vstyle_box" src="'.$atts['image'].'"/>';  
                }
                if(!empty($atts['icon'])){
                  $output .='<span class="vstyle_box fa '.$atts['icon'].'"></span>';  
                }
                $output .='</div>';
                $output .='<div class="col-md-8">';
                $output .= do_shortcode($content);
                $output .='</div>';
                $output .='</div>';
              break;
              default:
                if(!empty($atts['image'])){
                  $output .='<img class="vstyle_box" src="'.$atts['image'].'"/>';  
                }
                if(!empty($atts['icon'])){
                  $output .='<span class="vstyle_box fa '.$atts['icon'].'"></span>';  
                }
                $output .= do_shortcode($content);
              break;
           }

           $output .= '</div>';
           $output .= '</div>';
      return $output;
    }




    function new_column( $atts, $content = null, $name = '' ){
        global $post;
        $content_span='';
        
        //$post_layout = get_post_custom_values('vibe_sidebar_layout',$post->ID);
        //$content_span = $post_layout[0];
        
        switch($name){
            case 'v_1_2': $name='col-md-6 col-sm-6';
            break;
            case 'v_1_3': $name='col-md-4 col-sm-4';
            break;
            case 'v_1_4': $name='col-md-3 col-sm-3';
            break;
            case 'v_1_4_2': $name='col-md-3 col-sm-6';
            break;
            case 'v_1_6': $name='col-md-2 col-sm-3';
            break;
            case 'v_1_12': $name='col-md-1 col-sm-2';
            break;
            case 'v_2_3': $name='col-md-8 col-sm-8';
            break;
            case 'v_3_4': $name='col-md-9 col-sm-9';
            break;
            case 'v_resizable': $name='col-md-12 fullwidth';
            break;
            case 'v_stripe':$name='stripe';
            break;
            case 'v_stripe_container':
                  $name='stripe_container';
            break;
        }
            if($name != 'stripe' && $name != 'stripe_container'){

    	           $attributes = $this->v_get_attributes( $atts, "v_column {$name}" );	

              	$output = 	"<div {$attributes['class']}{$attributes['inline_styles']}>"
              					     . do_shortcode( $this->v_fix_shortcodes($content) ) .
              				      "</div> <!-- end .v_column_{$name} -->";

            }elseif( $name == 'stripe'){

                $name .=' fullwidth';
                $attributes = $this->v_get_attributes( $atts, "v_column {$name}" );	
                
                $output = 	"</div>
                              </div>
                              </section>
                              <section class='stripe'>
                                  <!-- Begin Stripe {$name} -->
                                        <div {$attributes['class']}{$attributes['inline_styles']}>"
    					                         . do_shortcode( $this->v_fix_shortcodes($content) ) .
                                        "</div> 
                                    <!-- End Stripe{$name} -->
                              </section>          
                              <section class='main'>
                                <div class='container'>
                                    <div class='full-width'>
                                        <div class='vibe_editor clearfix'>";
                        }else{ // Stripe with Container

                            $name .=' fullwidth';
                            $attributes = $this->v_get_attributes( $atts, "v_column {$name}" );	
                            $output = 	"</div></div>
                                        </section>
                                        <section class='stripe'>
                                          <div class='container'>
                                              <!-- Begin Stripe {$name} -->
                                              <div {$attributes['class']}{$attributes['inline_styles']}>"
          					                           . do_shortcode( $this->v_fix_shortcodes($content) ) .
                                              "</div> 
                                              <!-- End Stripe{$name} -->    
                                           </div>
                                        </section>          
                                          <section class='main nextstripe'>
                                              <div class='container'>
                                                <div class='full-width'>
                                                  <div class='vibe_editor clearfix'>";
            }
    	return $output;
    }

// dialog box columns
    function new_alt_column( $atts, $content = null, $name = '' ){
    	$name = str_replace( 'alt_', '', $name );
    	$attributes = $this->$this->v_get_attributes( $atts, "v_column {$name}" );
    		
    	$output = 	"<div {$attributes['class']}{$attributes['inline_styles']}>"
    					. do_shortcode( $this->v_fix_shortcodes($content) ) .
    				"</div> <!-- end .v_column_{$name} -->";

    	return $output;
    }

    function vibe_text_block($atts, $content = null) {
            
    	$attributes = $this->v_get_attributes( $atts, "v_text_block" );
    	if(isset($atts['custom_css'] ) && $atts['custom_css'] && strlen($atts['custom_css'])>5)    
                $output = '<style>'.$atts['custom_css'].'</style>';
            else
                $output= '';  	
    	$output .= 	"<div {$attributes['class']}{$attributes['inline_styles']}>";
            
            if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
            $ntitle= $atts['title'];
            $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
            $ntitle = str_replace("'", '', $ntitle);
            $output .='<div id="'.$ntitle.'"></div>';
            }
            
    	$output .= do_shortcode( $this->v_fix_shortcodes($content) ) .
    				"</div>";

    	return $output;
    }

    function vibe_parallax_block($atts, $content = null) {
      $attributes = $this->v_get_attributes( $atts, "v_parallax_block" );
      $rand ='parallax'.rand(1,999);
      $scroll = isset($atts['scroll'])?$atts['scroll']:2;
      $rev = isset($atts['rev'])?$atts['rev']:'0';
      $height = isset($atts['height'])?$atts['height']:'0';
      $adjust = isset($atts['adjust'])?$atts['adjust']:'0';
      $padding_top = isset($atts['padding_top'])?$atts['padding_top']:'0';
      $padding_bottom = isset($atts['padding_bottom'])?$atts['padding_bottom']:'0';

      $output = '<style>#'.$rand.' {
                background: url('.(isset($atts['bg_image'])?$atts['bg_image']:'').') 50% -'.($adjust).'px;
                position:relative;background-size: cover;
                min-height:'.(is_numeric($height)?$height.'px':$height).';
                } #'.$rand.' .parallax_content{
                  '.(($padding_top)?'padding-top:'.$padding_top.'px;':'').'
                '.(($padding_bottom)?'padding-bottom:'.$padding_bottom.'px;':'').'
                }'.(isset($atts['custom_css'])?$atts['custom_css']:'').'
                </style>'; 
    	
    	$output .= 	"<div id='$rand' data-rev={$rev} data-scroll={$scroll} data-height={$height} data-adjust={$adjust} {$attributes['class']}{$attributes['inline_styles']} >
                                <div class='parallax_content'>";
            
    	if(isset($atts['title']) && $atts['title'] && $atts['title'] != 'Content'){
            $ntitle= $atts['title'];
            $ntitle = preg_replace('/[^a-zA-Z0-9\']/', '_', $ntitle);
            $ntitle = str_replace("'", '', $ntitle);
            $output .='<div id="'.$ntitle.'"></div>';
            }
            

    	$output .= do_shortcode( $this->v_fix_shortcodes($content) ) .
    				"</div></div>";

    	return $output;
    }



    function new_widget_area($atts, $content = null) {
    	extract(shortcode_atts(array(
    				'area' => 'mainsidebar'
    			), $atts));
    			
    	$attributes = $this->v_get_attributes( $atts, "vibe_sidebar" );
    	
    	ob_start();
    	dynamic_sidebar($area);
    	$widgets = ob_get_contents();
    	ob_end_clean();
    	if($atts['custom_css'] && strlen($atts['custom_css'])>5)    
                $output = '<style>'.$atts['custom_css'].'</style>';
            else
                $output= '';
    	$output .= 	"<div {$attributes['class']}{$attributes['inline_styles']}>"
    					. $widgets .
    				"</div> <!-- end sidebar -->";

    	return $output;
    }


	function new_load_convertible_scripts( $scripts_to_load ){
		
	}

	function v_new_settings_page_css(){
		wp_enqueue_style( 'v_admin_css', plugins_url( 'css/v_admin.css' , __FILE__ ) );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_style( 'thickbox' );
	}

	function v_new_settings_page_js(){	
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-resizable' );
		
    
    if ( floatval(get_bloginfo('version')) >= 3.9){
      wp_enqueue_script( 'v_admin_js',plugins_url( 'js/v_admin.js' , __FILE__ ), array('jquery','jquery-ui-core','jquery-ui-sortable','jquery-ui-draggable','jquery-ui-droppable','jquery-ui-resizable'), '1.0' );
    }else{
      wp_enqueue_script( 'v_admin_js',plugins_url( 'js/v_admin_old.js' , __FILE__ ), array('jquery','jquery-ui-core','jquery-ui-sortable','jquery-ui-draggable','jquery-ui-droppable','jquery-ui-resizable'), '1.0' );
    }
		wp_localize_script( 'v_admin_js', 'v_options', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'load_nonce' => wp_create_nonce( 'load_nonce' ), 'confirm_message' => __('Confirm Delete?', 'vibe-customtypes'), 'confirm_message_yes' => __('Yes', 'vibe-customtypes'), 'confirm_message_no' => __('No', 'vibe-customtypes'), 'saving_text' => __('Saving...', 'vibe-customtypes'), 'saved_text' => __('Saved.', 'vibe-customtypes') ) );
	}


	function v_new_modules_init(){
		global $v_modules, $v_columns, $v_sample_layouts,$wp_registered_sidebars;
		
		$v_widget_areas =$v_post_types =array();
                
		foreach($wp_registered_sidebars as $sidebar){
		$v_widget_areas[$sidebar['id']]=$sidebar['id'];
		};
              
    global $wp_roles;


    $v_member_types = array();
    foreach($wp_roles->roles as $role=>$name){
      $v_member_types[$role] = $name['name'];
    }

    $member_types = Wplms_Member_types::init();
    $member_types->get_member_types();
    if(!empty($member_types->member_types)){
      foreach($member_types->member_types as $member_type){
         $v_member_types[$member_type['id']]=$member_type['sname'];
      }  
    }
    

    $post_types=get_post_types('','objects'); 

    foreach ( $post_types as $post_type ){
        if( !in_array($post_type->name, array('attachment','revision','nav_menu_item','sliders','modals','shop','shop_order','shop_coupon','forum','topic','reply')))
           $v_post_types[$post_type->name]=$post_type->label;
    }
    
    if(!array_key_exists('news',$v_post_types)){
        $v_post_types['news'] = __('Course News','vibe-customtypes');
    }

    $v_post_types = apply_filters('vibe_builder_post_types',$v_post_types);

     //Get List of All Products
     
    
    $v_thumb_styles = apply_filters('vibe_builder_thumb_styles',array(
                            ''=> plugins_url('images/thumb_1.png',__FILE__),
                            'course'=> plugins_url('images/thumb_2.png',__FILE__),
                            'course2'=> plugins_url('images/thumb_8.png',__FILE__),
                            'course3'=> plugins_url('images/thumb_8.jpg',__FILE__),
                            'course4'=> plugins_url('images/thumb_9.jpg',__FILE__),
                            'course5'=> plugins_url('images/thumb_10.jpg',__FILE__),
                            'course6'=> plugins_url('images/thumb_13.jpg',__FILE__),
                            'postblock'=> plugins_url('images/thumb_11.jpg',__FILE__),
                            'side'=> plugins_url('images/thumb_3.png',__FILE__),
                            'blogpost'=> plugins_url('images/thumb_6.png',__FILE__),
                            'images_only'=> plugins_url('images/thumb_4.png',__FILE__),
                            'testimonial'=> plugins_url('images/thumb_5.png',__FILE__),
                            'testimonial2'=> plugins_url('images/testimonial2.jpg',__FILE__),
                            'event_card'=> plugins_url('images/thumb_7.png',__FILE__),
                            'general'=> plugins_url('images/thumb_12.png',__FILE__),
                            'generic'=> plugins_url('images/generic.jpg',__FILE__),
                            'simple'=> plugins_url('images/simple.jpg',__FILE__),
                          ));
                
/* ===== Declaring the Modules =======  */                
    $v_modules['carousel'] = array(
    			'name' => __('Carousels/Rotating Blocks', 'vibe-customtypes'),
    			'options' => array(

            'title' => array(
            	'title' => __('Title/Heading', 'vibe-customtypes'),
            	'type' => 'text',
            	'std' => __('Heading', 'vibe-customtypes')
            ), 

            'show_title' => array(
    					'title' => __('Show Title', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => __(1, 'vibe-customtypes')
    				),

            'show_more' => array(
    					'title' => __('Show more link', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => __(0, 'vibe-customtypes')
    				),            

            'more_link' => array(
    					'title' => __('More Link (User redirected to this page on click)', 'vibe-customtypes'),
    					'type' => 'text',
    					'std' => ''
    				), 

            'show_controls' => array(
    					'title' => __('Show Direction arrows', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => 1
    				), 

            'show_controlnav' => array(
              'title' => __('Show Control dots', 'vibe-customtypes'),
              'type' => 'select_yesno',
              'options' => array(0=>'No',1=>'Yes'),
              'std' => 0
            ),


            'post_type' => array(
    					'title' => __('Enter Post Type<br /><span style="font-size:11px;">(Select Post Type from Posts/Courses/Clients/Products ...)</span>', 'vibe-customtypes'),
    					'type' => 'select',
    					'options' => $v_post_types,
    					'std' => __('post', 'vibe-customtypes')
    				),

            'taxonomy' => array(
    					'title' => __('Enter Taxonomy Slug (optional)<br /><span style="font-size:11px;">(A "Taxonomy" is a grouping mechanism for posts. Like Category for Posts, Tags for Posts, Portfolio Type for Portfolio etc.. <a href="http://codex.wordpress.org/Taxonomies">more</a>)</span> ', 'vibe-customtypes'),
    					'type' => 'text',
    					'std' => ''
    				), 

		    'term' => array(
				'title' => __('Enter Taxonomy Term Slug (optional, only if above is selected, comma saperated for multiple terms): ', 'vibe-customtypes'),
				'type' => 'text',
				'std' => ''
			),   
            'post_ids' => array(
					'title' => __('Or Enter Specific Post Ids', 'vibe-customtypes'),
					'type' => 'text',
                    'std'=>''
			),   
            'course_style' => array(
              'title' => __('Course Types [Only for Post type = Course]', 'vibe-customtypes'),
              'type' => 'select',
              'options' => array(
                'recent' => 'Recently published',
                'popular' => 'Most Students',
                'featured' => 'Featured',
                'rated'  => 'Highest Rated',
                'reviews' => 'Most Reviews',
                'start_date' => 'Upcoming Courses (Start Date)',
                'expired_start_date'=>'Expired Courses (Past Start Date)',
                'free'=> 'Free Courses',
                'random' => 'Random'
                ),
              'std' => __('recent', 'vibe-customtypes')
            ),    
            'featured_style' => array(
    					'title' => __('Carousel/Rotating Block Style', 'vibe-customtypes'),
    					'type' => 'radio_images',
    					'options' => $v_thumb_styles,
    					'std' => __('excerpt', 'vibe-customtypes')
    				),
            'auto_slide' => array(
    					'title' => __('Auto slide/rotate', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => 1,
    				),            
    		    'column_width' => array(
    					'title' => __('Width each crousel block', 'vibe-customtypes'),
    					'type' => 'text',
    					'std' => __('268', 'vibe-customtypes')
    				), 
            'carousel_max' => array(
              'title' => __('Maximum Number of blocks in One screen', 'vibe-customtypes'),
              'type' => 'text',
              'std' => __('4', 'vibe-customtypes')
            ), 
            'carousel_min' => array(
              'title' => __('Minimum Number of blocks in one Screen', 'vibe-customtypes'),
              'type' => 'text',
              'std' => __('2', 'vibe-customtypes')
            ),
            'carousel_move' => array(
              'title' => __('Move blocks in one slide', 'vibe-customtypes'),
              'type' => 'text',
              'std' => 1,
            ),           
            'carousel_number' => array(
    					'title' => __('Total Number of Blocks', 'vibe-customtypes'),
    					'type' => 'text',
              'std' => __('6', 'vibe-customtypes')
    				), 
    		
            'carousel_excerpt_length' => array(
    					'title' => __('Excerpt Length in Block (in characters)', 'vibe-customtypes'),
    					'type' => 'text',
    					'std' => __('100', 'vibe-customtypes')
    				),  
            'carousel_link' => array(
    					'title' => __('Show Link button on image hover', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => __(1, 'vibe-customtypes')
    				), 
            'advanced_settings' => array(
    			      'title' => __('Show Advanced settings', 'vibe-customtypes'),
    			      'type' => 'divider',
                'std' => 3
    		    ),             
            'css_class' => array(
               'title' => __('* Custom Class name (Add Custom Class to this Block)', 'vibe-customtypes'),
               'type' => 'text'
            ),
            'container_css' => array(
                'title' => __('* Class for on containing Layout column', 'vibe-customtypes'),
                'type' => 'text'
            ),
            'custom_css' => array(
    	           'title' => __('* Add Custom CSS (Use <strong>.</strong> for class name, <strong>:hover</strong> for hover styles etc..)', 'vibe-customtypes'),
    			       'type' => 'textarea'
                ),             
	    ),
    );

                    
    /* ====== Filterable ===== */
                    
    		$v_modules['filterable'] = array(
    			'name' => __('Filterable Posts', 'vibe-customtypes'),
    			'options' => array(
                       
            'title' => array(
              	'title' => __('Filterable Block Title', 'vibe-customtypes'),
              	'type' => 'text',
              	'std' => __('Heading', 'vibe-customtypes')
              ), 
            'show_title' => array(
    					'title' => __('Show Title', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => __(1, 'vibe-customtypes')
    				), 
            'post_type' => array(
    					'title' => __('Select a Post Type', 'vibe-customtypes'),
    					'type' => 'select',
    					'options' => $v_post_types,
    					'std' => __('post', 'vibe-customtypes')
    				),    
            'taxonomy' => array(
    					'title' => __('Enter relevant Taxonomy name used for Filter buttons (example : course-cat,event-type..)', 'vibe-customtypes'),
    					'type' => 'text',
    					'std' => ''
    				),
            'exclude_terms' => array(
              'title' => __('Enter terms (comma saperated term slugs) to be "excluded" in filters', 'vibe-customtypes'),
              'type' => 'text',
              'std' => ''
            ),
            'course_style' => array(
              'title' => __('Course Types [Only for Post type = Course]', 'vibe-customtypes'),
              'type' => 'select',
              'options' => array(
                'recent' => 'Recently published',
                'popular' => 'Most Students',
                'featured' => 'Featured',
                'rated'  => 'Highest Rated',
                'reviews' => 'Most Reviews',
                'start_date' => 'Upcoming Courses (Start Date)',
                'expired_start_date'=>'Expired Courses (Past Start Date)',
                'free' => 'Free',
                'random' => 'Random'
                ),
              'std' => __('recent', 'vibe-customtypes')
            ), 
            'featured_style' => array(
    					'title' => __('Featured Media Block Style', 'vibe-customtypes'),
    					'type' => 'radio_images',
    					'options' => $v_thumb_styles,
    					'std' => __('excerpt', 'vibe-customtypes')
    				), 
            'show_all' => array(
    					'title' => __('Show All link', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => __(1, 'vibe-customtypes')
    				),   
            'column_width' => array(
    					'title' => __('Column Width (in px)', 'vibe-customtypes'),
    					'type' => 'text',
    					'std' => '200'
    				),           
            'filterable_excerpt_length' => array(
    					'title' => __('Excerpt Length (in characters)', 'vibe-customtypes'),
    					'type' => 'text',
    					'std' => __('100', 'vibe-customtypes')
    				),              
            'filterable_number' => array(
    					'title' => __('Total Number of blocks', 'vibe-customtypes'),
    					'type' => 'text',
    					'std' => __('6', 'vibe-customtypes')
    				), 
            'show_pagination' => array(
    					'title' => __('Show Pagination', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => __(1, 'vibe-customtypes')
    				),  
                  
            'filterable_link' => array(
    					'title' => __('Show Link [Links to Post]', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => __(1, 'vibe-customtypes')
    				), 
            'advanced_settings' => array(
    			     'title' => __('Show Advanced settings', 'vibe-customtypes'),
    			     'type' => 'divider',
               'std' => 3
    		    ),             
            'css_class' => array(
                     'title' => __('* Custom Class name (Add Custom Class to this Block)', 'vibe-customtypes'),
                     'type' => 'text'
                       ),
            'container_css' => array(
                      'title' => __('* Class for on containing Layout column', 'vibe-customtypes'),
                      'type' => 'text'
                       ),
            'custom_css' => array(
    		           'title' => __('* Add Custom CSS (Use <strong>.</strong> for class name, <strong>:hover</strong> for hover styles etc..)', 'vibe-customtypes'),
    			         'type' => 'textarea'
    		          ),            
    		   ),
    		);

              
    /* ===== Grid =======  */                
    		
    		$v_modules['grid'] = array(
    			'name' => __('Post Grid', 'vibe-customtypes'),
    			'options' => array(
                       
            'title' => array(
            	'title' => __('Grid Title', 'vibe-customtypes'),
            	'type' => 'text',
            	'std' => __('Heading', 'vibe-customtypes')
            ), 
            'show_title' => array(
    					'title' => __('Show Title', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => __(1, 'vibe-customtypes')
    				),    
            'post_type' => array(
    					'title' => __('Custom Post Type', 'vibe-customtypes'),
    					'type' => 'select',
    					'options' => $v_post_types,
    					'std' => __('post', 'vibe-customtypes')
    				),
            
            'taxonomy' => array(
              'title' => __('Enter Taxonomy Slug (optional)<br /><span style="font-size:11px;">(A "Taxonomy" is a grouping mechanism for posts. Like Category for Posts, Tags for Posts, Portfolio Type for Portfolio etc.. <a href="http://codex.wordpress.org/Taxonomies">more</a>)</span> ', 'vibe-customtypes'),
              'type' => 'text',
              'std' => ''
            ), 

            'term' => array(
              'title' => __('Enter Taxonomy Term Slugs (optional, only if above is selected, comma saperated if multiple): ', 'vibe-customtypes'),
              'type' => 'text',
              'std' => ''
            ),   

            'post_ids' => array(
              'title' => __('Or Enter Specific Post Ids (comma saperated)', 'vibe-customtypes'),
              'type' => 'text',
              'std'=>''
            ),             
            'course_style' => array(
              'title' => __('Course Types [Only for Post type = Course]', 'vibe-customtypes'),
              'type' => 'select',
              'options' => array(
                'recent' => 'Recently published',
                'popular' => 'Most Students',
                'featured' => 'Featured',
                'rated'  => 'Highest Rated',
                'reviews' => 'Most Reviews',
                'start_date' => 'Upcoming Courses (Start Date)',
                'expired_start_date'=>'Expired Courses (Past Start Date)',
                'free'=>'Free',
                'random' => 'Random'
                ),
              'std' => __('recent', 'vibe-customtypes')
            ),  
            'featured_style' => array(
    					'title' => __('Featured Media Block Style', 'vibe-customtypes'),
    					'type' => 'radio_images',
    					'options' => $v_thumb_styles,
    					'std' => __('excerpt', 'vibe-customtypes')
    				), 
            
            'masonry' => array(
    					'title' => __('Grid Masonry Layout', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => __(0, 'vibe-customtypes')
    				),     

    		    'grid_columns' => array(
    					'title' => __('Grid Columns', 'vibe-customtypes'),
    					'type' => 'select',
    					'options' => array(
                'clear1 col-md-12'=>'1 Columns in FullWidth',
                'clear2 col-md-6'=>'2 Columns in FullWidth',
                'clear3 col-md-4'=>'3 Columns in FullWidth',
                'clear4 col-md-3'=>'4 Columns in FullWidth',
                'clear6 col-md-2'=>'6 Columns in FullWidth',
                'grid-auto-fill'=>'Autoill'),

    					'std' => 'clear3 col-md-4'
    				), 

            'column_width' => array(
    					'title' => __('Masonry Grid Column Width(in px)', 'vibe-customtypes'),
    					'type' => 'text',
    					'std' => '200'
    				), 
            'gutter' => array(
    					'title' => __('Spacing between Columns (in px)', 'vibe-customtypes'),
    					'type' => 'text',
    					'std' => '30'
    				),             
            'grid_number' => array(
    					'title' => __('Total Number of Blocks in Grid', 'vibe-customtypes'),
    					'type' => 'text',
              'std' => __('6', 'vibe-customtypes')
    				), 
                                
    		    'infinite' => array(
    					'title' => __('Infinite Scroll', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => __(1, 'vibe-customtypes')
    				), 

            'pagination' => array(
    					'title' => __('Enable Pagination (If infinite scroll is off)', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => __(1, 'vibe-customtypes')
    				),            

            'grid_excerpt_length' => array(
    					'title' => __('Excerpt Length (in characters)', 'vibe-customtypes'),
    					'type' => 'text',
    					'std' => __('100', 'vibe-customtypes')
    				),  

            'grid_link' => array(
    					'title' => __('Show Link', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => __(1, 'vibe-customtypes')
    				), 

            'advanced_settings' => array(
    			     'title' => __('Show Advanced settings', 'vibe-customtypes'),
    			     'type' => 'divider',
               'std' => 3
    		    ),             
            'css_class' => array(
               'title' => __('* Custom Class name (Add Custom Class to this Block)', 'vibe-customtypes'),
               'type' => 'text'
             ),
            'container_css' => array(
                'title' => __('* Class for on containing Layout column', 'vibe-customtypes'),
                'type' => 'text'
             ),
            'custom_css' => array(
               'title' => __('* Add Custom CSS (Use <strong>.</strong> for class name, <strong>:hover</strong> for hover styles etc..)', 'vibe-customtypes'),
    	         'type' => 'textarea'
            ),   
    			),
    	);
    		
                    
    /* ====== Editor ===== */                
    	$v_modules['text_block'] = array(
    			'name' => __('WP Editor', 'vibe-customtypes'),
    			'options' => array(
                'title' => array(
                    	'title' => __('Reference Title', 'vibe-customtypes'),
                    	'type' => 'text',
                    	'std' => __('Content', 'vibe-customtypes')
                             ), 
        				'text_block_content' => array(
        					'title' => __('Content', 'vibe-customtypes'),
        					'type' => 'wp_editor',
        					'is_content' => true
        				),
                'advanced_settings' => array(
            			'title' => __('Show Advanced settings', 'vibe-customtypes'),
            			'type' => 'divider',
                  'std' => 4
    		          ),             
                'animation_effect' => array(
                   'title' => __('* On-Load CSS3 Animation effect on the block (<a href="http://vibethemes.com/documentation/wplms/knowledge-base/css3-transitions-in-page-builder/" target="_blank">more</a>)', 'vibe-customtypes'),
                   'type' => 'select',
                   'options' => animation_effects(),
                   'std' => ''
                 ),             
                'css_class' => array(
                   'title' => __('* Custom Class name (Add Custom Class to this Block)', 'vibe-customtypes'),
                   'type' => 'text'
                 ),
                'container_css' => array(
                  'title' => __('* Class for on containing Layout column', 'vibe-customtypes'),
                  'type' => 'text'
                 ),
                'custom_css' => array(
    		           'title' => __('* Add Custom CSS (Use <strong>.</strong> for class name, <strong>:hover</strong> for hover styles etc..)', 'vibe-customtypes'),
    			         'type' => 'textarea'
    		          ),     
    			)
    		);


    /* ====== Parallax ===== */                
        $v_modules['parallax_block'] = array(
          'name' => __('Parallax Content', 'vibe-customtypes'),
          'options' => array(
                'title' => array(
                      'title' => __('Reference Title', 'vibe-customtypes'),
                      'type' => 'text',
                      'std' => __('Parallax Title', 'vibe-customtypes')
                    ), 
                  'text_block_content' => array(
                    'title' => __('Content', 'vibe-customtypes'),
                    'type' => 'wp_editor',
                    'is_content' => true
                  ),
                  'bg_image' => array(
                        'title' => __('Upload Parallax Background image', 'vibe-customtypes').'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/adding-or-uploading-image-for-badge-parallax-logo-notworking/" target="_blank" style="text-decoration:none;color:#666">&nbsp;<span class="dashicons dashicons-warning"></span></a>',
                        'type' => 'upload',
                        'std' => ''
                    ),  
                 'rev' => array(
                      'title' => __('Background Effect', 'vibe-customtypes'),
                      'type' => 'select',
                      'options' => array(
                            ''=>__('Image Scrolls with scroll','vibe-customtypes'),
                            1=>__('Image Static with Scroll','vibe-customtypes'),
                            2=>__('Full Parallax Static with Scroll','vibe-customtypes')
                            ),
                      'std' => ''
                    ),  
                  'height' => array(
                      'title' => __('Parallax Block Height (in px)', 'vibe-customtypes'),
                      'type' => 'text',
                      'std' => '200'
                    ), 
                   'scroll' => array(
                        'title' => __('Parallax value (Scroll senstivity, lower value means higher scroll)', 'vibe-customtypes'),
                        'type' => 'text',
                        'std' => '2'
                    ),
                    'adjust' => array(
                      'title' => __('Adjust background (in px)', 'vibe-customtypes'),
                      'type' => 'text',
                      'std' => '0'
                    ),
                    'padding_top' => array(
                      'title' => __('Top Padding(in px)', 'vibe-customtypes'),
                      'type' => 'text',
                      'std' => '0'
                    ),
                    'padding_bottom' => array(
                      'title' => __('Bottom Padding(in px)', 'vibe-customtypes'),
                      'type' => 'text',
                      'std' => '0'
                    ),           
                   'advanced_settings' => array(
                          'title' => __('Show Advanced settings', 'vibe-customtypes'),
                          'type' => 'divider',
                            'std' => 4
                        ),       
                    'animation_effect' => array(
                             'title' => __('* On-Load CSS3 Animation effect on the block (<a href="http://vibethemes.com/documentation/wplms/knowledge-base/css3-transitions-in-page-builder/" target="_blank">more</a>)', 'vibe-customtypes'),
                             'type' => 'select',
                             'options' => animation_effects(),
                             'std' => ''
                               ),            
                    'css_class' => array(
                             'title' => __('* Custom Class name (Add Custom Class to this Block)', 'vibe-customtypes'),
                             'type' => 'text'
                               ),
                    'container_css' => array(
                              'title' => __('* Class for on containing Layout column', 'vibe-customtypes'),
                              'type' => 'text'
                               ),
                    'custom_css' => array(
                             'title' => __('* Add Custom CSS (Use <strong>.</strong> for class name, <strong>:hover</strong> for hover styles etc..)', 'vibe-customtypes'),
                              'type' => 'textarea'
                            ),     
          )
        ); 

    /* === Featured Content === */
    $v_content_styles = apply_filters('vibe_builder_content_styles',array(
                                ''=> plugins_url('images/featured_block1.jpg',__FILE__),
                                'center'=> plugins_url('images/featured_block2.jpg',__FILE__),
                                'side'=> plugins_url('images/featured_block3.jpg',__FILE__),
                                'style'=> plugins_url('images/featured_block4.jpg',__FILE__),
                                ));
    $v_modules['featured_block'] = array(
          'name' => __('Featured Content', 'vibe-customtypes'),
          'options' => array(
                  'title' => array(
                      'title' => __('Reference Title', 'vibe-customtypes'),
                      'type' => 'text',
                      'std' => __('reference Title', 'vibe-customtypes')
                    ), 
                  'style' => array(
                    'title' => __('Content Style', 'vibe-customtypes'),
                    'type' => 'radio_images',
                    'options' => $v_content_styles,
                    'std'=>''
                  ), 
                  'image' => array(
                        'title' => __('Upload Image (Optional)', 'vibe-customtypes'),
                        'type' => 'upload',
                        'std' => ''
                    ),
                  'icon' => array(
                        'title' => __('Select Icon (Optional)', 'vibe-customtypes'),
                        'type' => 'icon',
                        'std' => ''
                  ), 
                  'text_block_content' => array(
                    'title' => __('Content', 'vibe-customtypes'),
                    'type' => 'wp_editor',
                    'is_content' => true
                  ),
                  'style_settings' => array(
                      'title' => __('Style settings', 'vibe-customtypes'),
                      'type' => 'divider',
                      'std' => 9
                    ),  
                  'icon_size' => array(
                    'title' => __('Icon Size/Image Width (in px)', 'vibe-customtypes'),
                    'type' => 'text',
                    'std' => '0'
                  ),
                  'icon_color' => array(
                    'title' => __('Icon Color', 'vibe-customtypes'),
                    'type' => 'color',
                    'std' => '#009dd8'
                  ), 
                 'padding_top' => array(
                    'title' => __('Top Padding / Top Spacing (in px)', 'vibe-customtypes'),
                    'type' => 'text',
                    'std' => '0'
                  ),
                  'padding_bottom' => array(
                    'title' => __('Bottom Padding / Bottom Spacing (in px)', 'vibe-customtypes'),
                    'type' => 'text',
                    'std' => '0'
                  ), 
                  'background_color' => array(
                    'title' => __('Background Color', 'vibe-customtypes'),
                    'type' => 'color',
                    'std' => '#FFFFFF'
                  ), 
                  'text_color' => array(
                    'title' => __('Text Color', 'vibe-customtypes'),
                    'type' => 'color',
                    'std' => '#666666'
                  ),
                  'border' => array(
                      'title' => __('Border', 'vibe-customtypes'),
                      'type' => 'select',
                      'options' => array(
                            ''=>__('No','vibe-customtypes'),
                            '1'=>__('Yes','vibe-customtypes'),
                            ),
                      'std' => ''
                    ), 
                  'border_width' => array(
                    'title' => __('Border width', 'vibe-customtypes'),
                    'type' => 'text',
                    'std' => '1'
                  ), 
                  'border_color' => array(
                    'title' => __('border Color', 'vibe-customtypes'),
                    'type' => 'color',
                    'std' => '#EEEEEE'
                  ),         
                   'advanced_settings' => array(
                          'title' => __('Show Advanced settings', 'vibe-customtypes'),
                          'type' => 'divider',
                            'std' => 4
                        ),       
                    'animation_effect' => array(
                             'title' => __('* On-Load CSS3 Animation effect on the block (<a href="http://vibethemes.com/documentation/wplms/knowledge-base/css3-transitions-in-page-builder/" target="_blank">more</a>)', 'vibe-customtypes'),
                             'type' => 'select',
                             'options' => animation_effects(),
                             'std' => ''
                               ),            
                    'css_class' => array(
                             'title' => __('* Custom Class name (Add Custom Class to this Block)', 'vibe-customtypes'),
                             'type' => 'text'
                               ),
                    'container_css' => array(
                              'title' => __('* Class for on containing Layout column', 'vibe-customtypes'),
                              'type' => 'text'
                               ),
                    'custom_css' => array(
                             'title' => __('* Add Custom CSS (Use <strong>.</strong> for class name, <strong>:hover</strong> for hover styles etc..)', 'vibe-customtypes'),
                              'type' => 'textarea'
                            ),     
          )
        ); 

/* === MEMBER CAROUSEL === */

$v_modules['member_carousel'] = array(
                'name' => __('Member Carousels/Rotating Blocks', 'vibe-customtypes'),
                'options' => array(

            'title' => array(
                'title' => __('Title/Heading', 'vibe-customtypes'),
                'type' => 'text',
                'std' => __('Heading', 'vibe-customtypes')
            ), 

            'show_title' => array(
                        'title' => __('Show Title', 'vibe-customtypes'),
                        'type' => 'select_yesno',
                        'options' => array(0=>'No',1=>'Yes'),
                        'std' => __(1, 'vibe-customtypes')
                    ),

            'show_more' => array(
                        'title' => __('Show more link', 'vibe-customtypes'),
                        'type' => 'select_yesno',
                        'options' => array(0=>'No',1=>'Yes'),
                        'std' => __(0, 'vibe-customtypes')
                    ),            

            'more_link' => array(
                        'title' => __('More Link (User redirected to this page on click)', 'vibe-customtypes'),
                        'type' => 'text',
                        'std' => ''
                    ), 

            'show_controls' => array(
                        'title' => __('Show Direction arrows', 'vibe-customtypes'),
                        'type' => 'select_yesno',
                        'options' => array(0=>'No',1=>'Yes'),
                        'std' => 1
                    ), 

            'show_controlnav' => array(
                'title' => __('Show Control dots', 'vibe-customtypes'),
                'type' => 'select_yesno',
                'options' => array(0=>'No',1=>'Yes'),
                'std' => 0
            ),


            'member_type' => array(
                        'title' => __('Select member type', 'vibe-customtypes'),
                        'type' => 'select',
                        'options' => apply_filters('vibe_editor_member_types',array('all'=>_x('All','Select option in Member carousel','vibe-customtypes'),'student'=>_x('Student','Select option in Member carousel','vibe-customtypes'),'instructor'=>_x('Instructor','Select option in Member carousel','vibe-customtypes'))),
                        'std' => '',
                    ),
                  
            'member_ids' => array(
                        'title' => __('Or Enter Specific Member Ids', 'vibe-customtypes'),
                        'type' => 'text',
                        'std'=>''
            ),  
            'profile_fields' => array(
                        'title' => __('Enter Profile fields (comma saperated field "names")', 'vibe-customtypes'),
                        'type' => 'text',
                        'std' => ''
            ),  
            'style' => array(
                    'title' => __('Display Style', 'vibe-customtypes'),
                    'type' => 'radio_images',
                    'options' => apply_filters('vibe_builder_cmember_styles',array(
                                ''=> plugins_url('images/member_block1.jpg',__FILE__),
                                'member2'=> plugins_url('images/member_block2.jpg',__FILE__),
                                'member3'=> plugins_url('images/member_block1.jpg',__FILE__),
                            )),
                    'std'=>''
                  ),   
            'auto_slide' => array(
                        'title' => __('Auto slide/rotate', 'vibe-customtypes'),
                        'type' => 'select_yesno',
                        'options' => array(0=>'No',1=>'Yes'),
                        'std' => __(1, 'vibe-customtypes')
                    ),            
            'column_width' => array(
                    'title' => __('Width each crousel block', 'vibe-customtypes'),
                    'type' => 'text',
                    'std' => __('268', 'vibe-customtypes')
                ), 
            'carousel_max' => array(
              'title' => __('Maximum Number of blocks in One screen', 'vibe-customtypes'),
              'type' => 'text',
              'std' => __('4', 'vibe-customtypes')
            ), 
            'carousel_min' => array(
              'title' => __('Minimum Number of blocks in one Screen', 'vibe-customtypes'),
              'type' => 'text',
              'std' => __('2', 'vibe-customtypes')
            ),  
            'carousel_move' => array(
              'title' => __('Move blocks in one slide', 'vibe-customtypes'),
              'type' => 'text',
              'std' => 1,
            ),         
            'carousel_number' => array(
                        'title' => __('Total Number of Blocks', 'vibe-customtypes'),
                        'type' => 'text',
                        'std' => __('6', 'vibe-customtypes')
            ),
            'carousel_link' => array(
                        'title' => __('Show Link button on image hover', 'vibe-customtypes'),
                        'type' => 'select_yesno',
                        'options' => array(0=>'No',1=>'Yes'),
                        'std' => __(1, 'vibe-customtypes')
                    ), 
            'advanced_settings' => array(
                      'title' => __('Show Advanced settings', 'vibe-customtypes'),
                      'type' => 'divider',
                'std' => 3
                ),             
            'css_class' => array(
               'title' => __('* Custom Class name (Add Custom Class to this Block)', 'vibe-customtypes'),
               'type' => 'text'
            ),
            'container_css' => array(
                'title' => __('* Class for on containing Layout column', 'vibe-customtypes'),
                'type' => 'text'
            ),
            'custom_css' => array(
                   'title' => __('* Add Custom CSS (Use <strong>.</strong> for class name, <strong>:hover</strong> for hover styles etc..)', 'vibe-customtypes'),
                       'type' => 'textarea'
                ),             
        ),
    );    


$v_modules['member_grid'] = array(
          'name' => __('Member Grid', 'vibe-customtypes'),
          'options' => array(
                       
            'title' => array(
              'title' => __('Grid Title', 'vibe-customtypes'),
              'type' => 'text',
              'std' => __('Heading', 'vibe-customtypes')
            ), 
            'show_title' => array(
              'title' => __('Show Title', 'vibe-customtypes'),
              'type' => 'select_yesno',
              'options' => array(0=>'No',1=>'Yes'),
              'std' => __(1, 'vibe-customtypes')
            ),    
            'member_type' => array(
              'title' => __('Member Type', 'vibe-customtypes'),
              'type' => 'select',
              'options' => $v_member_types,
              'std' =>''
            ),
            'profile_fields' => array(
                        'title' => __('Enter Profile fields (comma saperated field "names")', 'vibe-customtypes'),
                        'type' => 'text',
                        'std' => ''
            ), 
            'exclude_member_ids' => array(
              'title' => __('Exclude Specific Member Ids (comma saperated)', 'vibe-customtypes'),
              'type' => 'text',
              'std'=>''
            ),             
            'style' => array(
                    'title' => __('Display Style', 'vibe-customtypes'),
                'type' => 'radio_images',
                'options' => apply_filters('vibe_builder_cmember_styles',array(
                            ''=> plugins_url('images/member_block1.jpg',__FILE__),
                            'member2'=> plugins_url('images/member_block2.jpg',__FILE__),
                            'member3'=> plugins_url('images/member_block1.jpg',__FILE__),
                            'member4'=> plugins_url('images/member_block4.jpg',__FILE__),
                        )),
                'std'=>''
              ),  

            'grid_layout' => array(
                'title' => __('Grid Layout', 'vibe-customtypes'),
                'type' => 'radio_images',
                'options' => apply_filters('vibe_builder_grid_layouts',array(
                            ''=> plugins_url('images/grid-1.png',__FILE__),
                            'grid2'=> plugins_url('images/grid-2.png',__FILE__),
                            'grid3'=> plugins_url('images/grid-3.png',__FILE__),
                            'grid4'=> plugins_url('images/grid-4.png',__FILE__),
                            'grid5'=> plugins_url('images/grid-5.png',__FILE__),
                            'grid6'=> plugins_url('images/grid-6.png',__FILE__),
                            'grid7'=> plugins_url('images/grid-7.jpg',__FILE__),
                        )),
                'std'=>''
              ),   

            'block_size' => array(
               'title' => __('Block Size (in px) Or number of blocks in 1 row based on smallest block size ', 'vibe-customtypes'),
               'type' => 'text',
               'std' => 3
            ), 

            'gutter' => array(
              'title' => __('Spacing between Columns (in px)', 'vibe-customtypes'),
              'type' => 'text',
              'std' => '0'
            ),             
            'grid_number' => array(
              'title' => __('Total Number of Blocks in Grid', 'vibe-customtypes'),
              'type' => 'text',
              'std' => __('6', 'vibe-customtypes')
            ), 


            'advanced_settings' => array(
               'title' => __('Show Advanced settings', 'vibe-customtypes'),
               'type' => 'divider',
               'std' => 3
            ),             
            'css_class' => array(
               'title' => __('* Custom Class name (Add Custom Class to this Block)', 'vibe-customtypes'),
               'type' => 'text'
             ),
            'container_css' => array(
                'title' => __('* Class for on containing Layout column', 'vibe-customtypes'),
                'type' => 'text'
             ),
            'custom_css' => array(
               'title' => __('* Add Custom CSS (Use <strong>.</strong> for class name, <strong>:hover</strong> for hover styles etc..)', 'vibe-customtypes'),
               'type' => 'textarea'
            ),   
          ),
      );

/* ====== RevSlider ===== */
                
    if ( in_array( 'revslider/revslider.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'revslider/revslider.php'))) {
      $revsliders = array();
      // Fetch all Revolution Slider list
      global $wpdb;
      $table_name = $wpdb->prefix . "revslider_sliders"; 
       $querystr = "
              SELECT title,alias
              FROM $table_name";

               $rev_sliders = $wpdb->get_results($querystr, OBJECT);
               
    foreach($rev_sliders as $sliders){ 
      $revsliders[$sliders->alias] = $sliders->title;
    }

               
    $v_modules['revslider'] = array(
    			'name' => __('Revolution Slider', 'vibe-customtypes'),
    			'options' => array(
                 'alias' => array(
        		             'title' => __('Select Slider', 'vibe-customtypes'),
        		             'type' => 'select',
                         'options' => $revsliders
                  ),  
                  'advanced_settings' => array(
                    			'title' => __('Show Advanced settings', 'vibe-customtypes'),
                    			'type' => 'divider',
                          'std' => 3
                  ),             
                  'css_class' => array(
                           'title' => __('* Custom Class name (Add Custom Class to this Block)', 'vibe-customtypes'),
                           'type' => 'text'
                   ),
                  'container_css' => array(
                            'title' => __('* Class for on containing Layout column', 'vibe-customtypes'),
                            'type' => 'text'
                   ),
                  'custom_css' => array(
                            'title' => __('* Add Custom CSS (Use <strong>.</strong> for class name, <strong>:hover</strong> for hover styles etc..)', 'vibe-customtypes'),
    			                   'type' => 'textarea'
    		          ),    
    			)
        ); 
    }
                
    if ( in_array( 'LayerSlider/layerslider.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'LayerSlider/layerslider.php')) || class_exists('LS_Shortcode')) {
                        
                        // Fetch all Layer Slider list
    $layersliders = array();
    global $wpdb;
    $table_name = $wpdb->prefix . "layerslider"; 
    $querystr = "
          SELECT id,name
          FROM $table_name";
           $layer_sliders = $wpdb->get_results($querystr, OBJECT);
           
           foreach($layer_sliders as $sliders){ 
              $layersliders[$sliders->id] = $sliders->name;
           }
    $v_modules['layerslider'] = array(
    			'name' => __('Layer Slider', 'vibe-customtypes'),
    			'options' => array(
    				'id' => array(
          					'title' => __('Select Slider', 'vibe-customtypes'),
          					'type' => 'select',
                    'options' => $layersliders
    				        ),  
            'advanced_settings' => array(
              			'title' => __('Show Advanced settings', 'vibe-customtypes'),
              			'type' => 'divider',
                    'std' => 3
    		            ),             
            'css_class' => array(
                     'title' => __('* Custom Class name (Add Custom Class to this Block)', 'vibe-customtypes'),
                     'type' => 'text'
                   ),
            'container_css' => array(
                      'title' => __('* Class for on containing Layout column', 'vibe-customtypes'),
                      'type' => 'text'
                   ),
            'custom_css' => array(
    		           'title' => __('* Add Custom CSS (Use <strong>.</strong> for class name, <strong>:hover</strong> for hover styles etc..)', 'vibe-customtypes'),
    			           'type' => 'textarea'
    		          ),    
    			   )
      ); 
    }
                
    $v_modules['taxonomy_carousel'] = array(
        'name' => __('Course Category Carousel', 'vibe-customtypes'),
        'options' => array(
            'title' => array(
                'title' => __('Title/Heading', 'vibe-customtypes'),
                'type' => 'text',
                'std' => __('Heading', 'vibe-customtypes')
            ), 
            'term_slugs' => array(
                'title' => __('Include Term Slugs (optional, comma saperated)', 'vibe-customtypes'),
                'type' => 'text',
                'std' => '',
            ),
             'orderby' => array(
                'title' => __('Show Direction arrows', 'vibe-customtypes'),
                'type' => 'select',
                'type' => 'select',
                'options' => array('name'=>__('Alphabetical', 'vibe-customtypes'),'description'=>__('Description', 'vibe-customtypes'),'meta_value_num'=>__('Custom Order','vibe-customtypes')),
                'std' => 1
            ), 
            'order' => array(
                'title' => __('Show Direction arrows', 'vibe-customtypes'),
                'type' => 'select',
                'options' => array('DESC'=>__('Decending', 'vibe-customtypes'),'ASC'=>__('Ascending', 'vibe-customtypes')),
                'std' => 1
            ), 
            'show_controls' => array(
                'title' => __('Show Direction arrows', 'vibe-customtypes'),
                'type' => 'select_yesno',
                'options' => array(0=>'No',1=>'Yes'),
                'std' => 1
            ), 
            'show_controlnav' => array(
                'title' => __('Show Control dots', 'vibe-customtypes'),
                'type' => 'select_yesno',
                'options' => array(0=>'No',1=>'Yes'),
                'std' => 0
            ),
            'auto_slide' => array(
                'title' => __('Auto slide/rotate', 'vibe-customtypes'),
                'type' => 'select_yesno',
                'options' => array(0=>'No',1=>'Yes'),
                'std' => 1
            ),            
            'column_width' => array(
                'title' => __('Width each crousel block', 'vibe-customtypes'),
                'type' => 'text',
                'std' => __('268', 'vibe-customtypes')
            ), 
            'carousel_max' => array(
                'title' => __('Maximum Number of blocks in One screen', 'vibe-customtypes'),
                'type' => 'text',
                'std' => __('4', 'vibe-customtypes')
            ), 
            'carousel_min' => array(
                'title' => __('Minimum Number of blocks in one Screen', 'vibe-customtypes'),
                'type' => 'text',
                'std' => __('2', 'vibe-customtypes')
            ),   
            'carousel_move' => array(
              'title' => __('Move blocks in one slide', 'vibe-customtypes'),
              'type' => 'text',
              'std' => 1,
            ),        
            'carousel_number' => array(
                        'title' => __('Total Number of Blocks', 'vibe-customtypes'),
                        'type' => 'text',
                        'std' => __('6', 'vibe-customtypes')
            ),
            'advanced_settings' => array(
                'title' => __('Show Advanced settings', 'vibe-customtypes'),
                'type' => 'divider',
                'std' => 3
            ),             
            'css_class' => array(
                'title' => __('* Custom Class name (Add Custom Class to this Block)', 'vibe-customtypes'),
                'type' => 'text'
            ),
            'container_css' => array(
                'title' => __('* Class for on containing Layout column', 'vibe-customtypes'),
                'type' => 'text'
            ),
            'custom_css' => array(
                'title' => __('* Add Custom CSS (Use <strong>.</strong> for class name, <strong>:hover</strong> for hover styles etc..)', 'vibe-customtypes'),
                'type' => 'textarea'
            ),   
        )
    );

                //Sidebars
    $v_modules['widget_area'] = array(
      'name' => __('Sidebar', 'vibe-customtypes'),
      'options' => array(
          	'area' => array(
              		'title' => __('Select a Sidebar', 'vibe-customtypes'),
              		'type' => 'select',
              		'options' => $v_widget_areas,
              		'std' => __('MainSidebar', 'vibe-customtypes')
              	),
              'advanced_settings' => array(
                  'title' => __('Show Advanced settings', 'vibe-customtypes'),
                  'type' => 'divider',
                  'std' => 3
                 ),             
              'css_class' => array(
                 'title' => __('* Custom Class name (Add Custom Class to this Block)', 'vibe-customtypes'),
                 'type' => 'text'
               ),
              'container_css' => array(
                  'title' => __('* Class for on containing Layout column', 'vibe-customtypes'),
                  'type' => 'text'
               ),
              'custom_css' => array(
                   'title' => __('* Add Custom CSS (Use <strong>.</strong> for class name, <strong>:hover</strong> for hover styles etc..)', 'vibe-customtypes'),
                    'type' => 'textarea'
              ),   
          )
    );
		
		
    $v_modules['slider'] = array(
    			'name' => __('FlexSlider', 'vibe-customtypes'),
    			'options' => array(
              'title' => array(
          					'title' => __('Slider ID (for reference & Css)', 'vibe-customtypes'),
          					'type' => 'text',
                    'std' => 'FlexSlider'
    				    ),
                'slide_style' => array(
                      'title' => __('Slide Style', 'vibe-customtypes'),
    				          'type' => 'radio_images',
                      'options'=>array(
                                      'slide1'=> plugins_url('images/slider_1.png',__FILE__),
                                      'slide2'=> plugins_url('images/slider_2.png',__FILE__),
                                      'slide3'=> plugins_url('images/slider_3.png',__FILE__),
                                      'slide4'=> plugins_url('images/slider_4.png',__FILE__),
                                      'slide5'=> plugins_url('images/slider_5.png',__FILE__),
                              ),
                      'std' => 'slide1'
                  ),
                  'animation' => array(
            					'title' => __('Animation Effect', 'vibe-customtypes'),
            					'type' => 'select',
            					'options' => array( 'fade'=>__('fade', 'vibe-customtypes'),'slide'=> __('slide', 'vibe-customtypes') ),
            					'std' => 'fade'
            				),
                                    
                  'slider_settings' => array(
                      'title' => __('Slider settings', 'vibe-customtypes'),
                      'type' => 'divider',
                      'std' => 12
                  ),
          				'auto_slide' => array(
          					'title' => __('Auto slide Images', 'vibe-customtypes'),
          					'type' => 'select_yesno',
          					'options' => array(0=>'No',1=>'Yes'),
          					'std' => __(1, 'vibe-customtypes')
          				),
                  'loop' => array(
            					'title' => __('Loop Slides', 'vibe-customtypes'),
            					'type' => 'select_yesno',
            					'options' => array(0=>'No',1=>'Yes'),
            					'std' => __(1, 'vibe-customtypes')
            				),
                    'randomize' => array(
            					'title' => __('Randomize Slides', 'vibe-customtypes'),
            					'type' => 'select_yesno',
            					'options' => array(0=>'No',1=>'Yes'),
            					'std' => __(1, 'vibe-customtypes')
            				),
                    'show_directionnav' => array(
                					'title' => __('Show Slider Direction arrows', 'vibe-customtypes'),
                					'type' => 'select_yesno',
                					'options' => array(0=>'No',1=>'Yes'),
                					'std' => __(1, 'vibe-customtypes')
                				),
                    'show_controlnav' => array(
              					'title' => __('Show Slider Control buttons', 'vibe-customtypes'),
              					'type' => 'select_yesno',
              					'options' => array(0=>'No',1=>'Yes'),
              					'std' => __(1, 'vibe-customtypes')
              				),
    				'animation_duration' => array(
    					'title' => __('Animation Duration (in ms)', 'vibe-customtypes'),
    					'type' => 'text',
    					'std' => '600'
    				),
    				
    				'auto_speed' => array(
    					'title' => __('Auto Animation Speed (in ms)', 'vibe-customtypes'),
    					'type' => 'text',
    					'std' => '7000'
    				),
    				'pause_on_hover' => array(
    					'title' => __('Pause Slider On Hover', 'vibe-customtypes'),
    					'type' => 'select_yesno',
    					'options' => array(0=>'No',1=>'Yes'),
    					'std' => __(1, 'vibe-customtypes')
    				),
                                    
            'css_class' => array(
                'title' => __('* Custom Class name (Add Custom Class to this Block)', 'vibe-customtypes'),
                'type' => 'text'
           ),
            'container_css' => array(
              'title' => __('* Class for on containing Layout column', 'vibe-customtypes'),
              'type' => 'text'
           ),
            'custom_css' => array(
              'title' => __('* Add Custom CSS (Use <strong>.</strong> for class name, <strong>:hover</strong> for hover styles etc..)', 'vibe-customtypes'),
              'type' => 'textarea'
          ), 
                                    
      		'images' => array(
      			'type' => 'slider_images',
            'std' => 'slides'
      		),
          'advanced_settings' => array(
             ),            
                                   
    			)
    );
        
                
		$v_modules = apply_filters( 'v_modules', $v_modules );
		
		$v_columns['1_2'] = array( 'name' => __('1/2 Column', 'vibe-customtypes') );
		$v_columns['1_3'] = array( 'name' => __('1/3 Column', 'vibe-customtypes') );
		$v_columns['1_4'] = array( 'name' => __('1/4 Column', 'vibe-customtypes') );
        $v_columns['1_4_2'] = array( 'name' => __('1/4 Column & 1/2 Column', 'vibe-customtypes') );
		$v_columns['2_3'] = array( 'name' => __('2/3 Column', 'vibe-customtypes') );
		$v_columns['3_4'] = array( 'name' => __('3/4 Column', 'vibe-customtypes') );
		$v_columns['resizable'] = array( 'name' => __('Full-Width Resizable Column', 'vibe-customtypes') );
		$v_columns['stripe_container'] = array( 'name' => __('FullScreen Stripe with Container', 'vibe-customtypes') );
        $v_columns['stripe'] = array( 'name' => __('FullScreen Stripe', 'vibe-customtypes') );
                
		$v_columns = apply_filters( 'v_columns', $v_columns );
		$v_sample_layouts='';
		$v_sample_layouts = get_option('vibe_builder_sample_layouts');
                if(is_string($v_sample_layouts))
                    $v_sample_layouts = unserialize($v_sample_layouts);
                
		foreach( $v_columns as $v_column_key => $v_column ){
			add_shortcode("v_{$v_column_key}", array($this,'new_column'));
			add_shortcode("v_alt_{$v_column_key}", array($this,'new_alt_column'));
		}
		
	}

	function vibe_layout_editor(){
		global $v_modules, $v_columns, $v_sample_layouts, $post;
		$v_helper_class = '';
		$v_convertible_settings = get_post_meta( $post->ID, '_builder_settings', true );

	?>
		<?php do_action( 'before_page_builder' ); ?>
		
		<div id="page_builder">
			<div id="vibe_editor_controls" class="clearfix">
				<a href="#" class="add_element add_column"><span><i class="dashicons dashicons-screenoptions"></i> <?php _e('COLUMNS', 'vibe-customtypes'); ?></span></a>
				<a href="#" class="add_element add_module"><span><i class="dashicons dashicons-welcome-widgets-menus"></i> <?php _e('CONTENT', 'vibe-customtypes'); ?></span></a>
				<a href="#" class="add_element add_sample_layout"><span><i class="dashicons dashicons-feedback"></i> <?php _e('SAVED LAYOUTS', 'vibe-customtypes'); ?></span></a>
			</div> <!-- #vibe_editor_controls -->
			
			<div id="modules">
				<?php

					foreach ( $v_modules as $module_key => $module_settings ){
						$class = "module m_{$module_key}";
						if ( isset( $module_settings['full_width'] ) && $module_settings['full_width'] ) $class .= ' full_width';
						
						echo "<div data-placeholder='" . esc_attr( $module_settings['name'] ) . "' data-name='" . esc_attr( $module_key ) . "' class='" . esc_attr( $class ) . "'>" . '<span class="module_name">' . esc_html( $module_settings['name'] ) . '</span>' .
						'<span class="move"></span><span class="delete"></span><span class="settings_arrow"></span><div class="module_settings"></div></div>';
					}
					if(is_array($v_columns))
					foreach ( $v_columns as $column_key => $column_settings ){
						echo "<div data-placeholder='" . esc_attr( $column_settings['name'] ) . "' data-name='" . esc_attr( $column_key ) . "' class='" . esc_attr( "module m_column m_column_{$column_key}" ) . "'>" . 
						'<span class="module_name column_name">' . esc_html( $column_settings['name'] ) . '</span>' .
						'<span class="move"></span> <span class="delete_column delete"></span></div>';
					}

					if(is_array($v_sample_layouts))
					foreach ( $v_sample_layouts as $layout_key => $layout_settings ){
						echo "<div data-placeholder='" . esc_attr( $layout_settings['name'] ) . "' data-name='" . esc_attr( $layout_key ) . "' class='" . esc_attr( "module sample_layout" ) . "'>" . 
						'<span class="module_name">' . esc_html( $layout_settings['name'] ) . '</span>' .
						'<span class="move"></span></div>';
					}
				?>
				<div id="module_separator"></div>
				<div id="active_module_settings"></div>
			</div> <!-- #modules -->
			
			<div id="layout_container">
				<div id="layout" class="clearfix">
					<?php 
						if ( is_array( $v_convertible_settings ) && $v_convertible_settings['layout_html'] ) {
							echo stripslashes( $v_convertible_settings['layout_html'] );
							$v_helper_class = ' class="hidden"';
						}
					?>
				</div> <!-- #layout -->
				<div id="v_helper"<?php echo $v_helper_class; ?>><?php esc_html_e('Drag & Drop Layout Columns and then Drag & Drop Content Blocks to each column', 'vibe-customtypes'); ?></div>
			</div> <!-- #layout_container -->
			
			<div style="display: none;">
				<?php
					wp_editor( ' ', 'v_hidden_editor');
					do_action( 'v_hidden_editor' );
				?>
			</div>
		</div> <!-- #page_builder -->
                <div class="overlay">
                                <label><?php _e('Enter name of Sample Layout','vibe-customtypes'); ?></label><input type="text" class="text" id="new_sample_layout_name" name="new_sample_layout_name" data-id="<?php global $post; echo $post->ID;?>"/>
                                <a id="save_new_sample_layout" class="vibe-button-save-new-layout"><?php _e('Save Layout', 'vibe-customtypes') ?></a>
                                <span class="remove"></span>
                </div>
		<div id="v_ajax_save">
			<img src="<?php echo plugins_url('images/loading.gif',__FILE__ ); ?>" alt="loading" id="loading" />
			<span><?php esc_html_e( 'Saving...', 'vibe-customtypes' ); ?></span>
		</div>
		
		<?php
			echo '<div id="v_save">';
                        submit_button( __('Save Changes', 'vibe-customtypes'), 'vibe-button-save', 'v_main_save' );
			echo '<a id="new_sample_layout" class="vibe-button-save-new-layout" style="display:none;">'. __('Save as New Layout', 'vibe-customtypes').'</a>';
      echo '<a id="generated_shortcode" class="vibe-button-save-new-layout" style="display:none;">'. __('Generated Shortcode', 'vibe-customtypes').'</a>';
      $shortcode = '';
      if(isset($_GET['post'])){
        $page_builder = get_post_meta(get_the_ID(),'_builder_settings',true);
        if(!empty($page_builder)){
          $shortcode = $page_builder['layout_shortcode'];
        }
      }
      echo '<textarea id="pagebuilder_generated_shortcode" class="clear" cols="60" rows="4" style="display:inline-block;margin-top:20px;display:none;">'.$shortcode.'</textarea>';
			echo '</div> <!-- end #v_save -->';
	}

	
  //add_action( 'save_post',  'save_layout');
        
	function save_layout(){
		if ( ! wp_verify_nonce( $_POST['load_nonce'], 'load_nonce' ) ){
        die(-1);
    } 
		
		$v_convertible_settings = array();
		
		$v_convertible_settings['layout_html'] = trim( $_POST['layout_html'] );
		$v_convertible_settings['layout_shortcode'] = $_POST['layout_shortcode'];
		$v_post_id = (int) $_POST['post_id'];

		if ( get_post_meta( $v_post_id, '_builder_settings', true ) ) {
      delete_post_meta($v_post_id,'_builder_settings');
      update_post_meta( $v_post_id, '_builder_settings', $v_convertible_settings );
    }else 
      add_post_meta( $v_post_id, '_builder_settings', $v_convertible_settings, true );
		
		die();
	}

	function new_append_layout(){
		global $v_sample_layouts;
		
		if ( ! wp_verify_nonce( $_POST['load_nonce'], 'load_nonce' ) ) die(-1);
		
		$layout_name = $_POST['layout_name'];
		if ( isset( $v_sample_layouts[$layout_name] ) ) echo stripslashes( $v_sample_layouts[$layout_name]['content'] );
		
		die();
	}
    
	function save_new_layout(){
		if ( ! wp_verify_nonce( $_POST['load_nonce'], 'load_nonce' ) ) die(-1);
		
                $name = stripslashes($_POST['name']);
                $postid = stripslashes($_POST['id']);
                
                $layout = get_post_meta($postid,'_builder_settings');
                
                echo $layout[0]['layout_html'];
                
                if(isset($layout[0]['layout_html'])){
                 
                $value = get_option('vibe_builder_sample_layouts');
                if(isset($value)){
                    
                    if(is_string($value))
                    $value=  unserialize($value);
                    $value[]=array('name'=>$name,
                                    'content'=>$layout[0]['layout_html']);
                    
                    $value=serialize($value);
                    update_option('vibe_builder_sample_layouts',$value);
                }else{
                    $value[]=array('name'=>$name,
                                    'content'=>$layout[0]['layout_html']);
                    $value=serialize($value);
                    add_option('vibe_builder_sample_layouts',$value);
                }
                update_option('vibe_builder_sample_layouts',$value);
                }else{
                    echo 'unable to save';
                }
                die();
            }
        


		function generate_column_options( $column_name, $paste_to_editor_id ){
			global $v_columns;
			
			$module_name = $v_columns[$column_name]['name'];
			echo '<form id="dialog_settings">'
					. '<span id="settings_title">' . esc_html( ucfirst( $module_name ) . ' ' . __('Settings', 'vibe-customtypes') ) . '</span>'
					. '<a href="#" id="close_dialog_settings"></a>'
					. '<p class="clearfix"><input type="checkbox" id="dialog_first_class" name="dialog_first_class" value="" class="v_option" /> ' . esc_html__('This is the first column in the row', 'vibe-customtypes') . '</p>';
			
			if ( 'resizable' == $column_name ) echo '<p class="clearfix"><label>' . esc_html__('Column width (%)', 'vibe-customtypes') . ':</label> <input name="dialog_width" type="text" id="dialog_width" value="100" class="regular-text v_option" /></p>';
			
			submit_button(__('Save Changes', 'vibe-customtypes'), 'vibe-button-save');
			
			echo '<input type="hidden" id="saved_module_name" value="' . esc_attr( "alt_{$column_name}" ) . '" />';
			
			if ( '' != $paste_to_editor_id ) echo '<input type="hidden" id="paste_to_editor_id" value="' . esc_attr( $paste_to_editor_id ) . '" />';
			
			echo '</form>';
		}

		function generate_module_options( $module_name, $module_window, $paste_to_editor_id, $v_module_exact_name ){
			global $v_modules;
			
			$i = 1;
			$form_id = ( 0 == $module_window ) ? 'module_settings' : 'dialog_settings';
			
			echo '<form id="' . esc_attr( $form_id ) . '">';
			echo '<span id="settings_title">' . esc_html( $v_module_exact_name . ' ' . __('Settings', 'vibe-customtypes') ) . '</span>';
			
			if ( 0 == $module_window ) echo '<a href="#" id="close_module_settings"></a>';
			else echo '<a href="#" id="close_dialog_settings"></a>';			
            
			foreach ( $v_modules[$module_name]['options'] as $option_slug => $option_settings ){
				$content_class = isset( $option_settings['is_content'] ) && $option_settings['is_content'] ? ' v_module_content' : '';
				
				echo '<p class="clearfix">';
				if ( isset( $option_settings['title'] ) ) echo "<label>{$option_settings['title']}</label>";
				
				if ( 1 == $module_window ) $option_slug = 'dialog_' . $option_slug;
				
				switch ( $option_settings['type'] ) {
					case 'wp_editor': 

						wp_editor( '', $option_slug, array(
              'editor_class' => 'wp_editor_area v_wp_editor v_option' . $content_class,
              'media_buttons' => true,
              'quicktags'     => TRUE,
            ));

						break;
					
					case 'select':
						$std = isset( $option_settings['std'] ) ? $option_settings['std'] : '';
						echo
						'<select name="' . esc_attr( $option_slug ) . '" id="' . esc_attr( $option_slug ) . '" class="chzn-select v_option' . $content_class . '">'
							. ( ( '' == $std ) ? '<option value="nothing_selected">  ' . esc_html__('Select', 'vibe-customtypes') . '  </option>' : '' );
							
                        foreach ( $option_settings['options'] as $key=>$setting_value ){ 
								echo '<option value="' . esc_attr( $key ) . '"' . selected( $key, $std, false ) . '>' . esc_html( $setting_value ) . '</option>';
							}
						echo '</select>';
						break;
            
            case 'multiselect':
						$std = isset( $option_settings['std'] ) ? $option_settings['std'] : '';
						echo
						'<select name="' . esc_attr( $option_slug ) . '" id="' . esc_attr( $option_slug ) . '" class="chzn-select v_option' . $content_class . '" multiple=multiple style="min-width:300px;" data-placeholder="Choose options...">'
							. ( ( '' == $std ) ? '<option value="nothing_selected">  ' . esc_html__('Select', 'vibe-customtypes') . '  </option>' : '' );
							
                                                foreach ( $option_settings['options'] as $key=>$setting_value ){ 
                                                    $value_array=explode(',',$std);
								echo '<option value="' . esc_attr( $key ) . '"' . (in_array( $key, $value_array )?'selected="SELECTED"':'') . '>' . esc_html( $setting_value ) . '</option>';
							}
						echo '</select>';
						break;        
            
            case 'radio_images':
						$std = isset( $option_settings['std'] ) ? $option_settings['std'] : '';
						foreach ( $option_settings['options'] as $key=>$setting_value ){ 
                                                    echo '<label class="radio_images" data-value="'.$key.'"><img src="' . esc_html( $setting_value ) . '" for="' . esc_attr( $option_slug ) . '" />
                                                                      </label>';
							}
                                                echo '<input name="' . esc_attr( $option_slug ) . '" type="hidden" id="' . esc_attr( $option_slug ) . '" value="'.( '' != $std ? esc_attr( $std ) : '' ).'" class="image_value v_option' . $content_class . '" />';
						break; 
            
            case 'select_yesno':
						$std = isset( $option_settings['std'] ) ? $option_settings['std'] : '';
						echo
						'<span class="select_yesno_button"></span>
                                                    <select name="' . esc_attr( $option_slug ) . '" id="' . esc_attr( $option_slug ) . '" class="select_yesno_val v_option' . $content_class . '">'
							. ( ( '' == $std ) ? '<option value="nothing_selected">  ' . esc_html__('Select', 'vibe-customtypes') . '  </option>' : '' );
							
                                                foreach ( $option_settings['options'] as $key=>$setting_value ){ 
								echo '<option value="' . esc_attr( $key ) . '"' . selected( $key, $std, false ) . '>' . esc_html( $setting_value ) . '</option>';
							}
						echo '</select>';
						break;  

					 case 'text':
						$std = isset( $option_settings['std'] ) ? $option_settings['std'] : '';
						echo '<input name="' . esc_attr( $option_slug ) . '" type="text" id="' . esc_attr( $option_slug ) . '" value="'.( '' != $std ? esc_attr( $std ) : '' ).'" class="text regular-text v_option' . $content_class . '" />';
						break;
            
            case 'textarea':
						$std = isset( $option_settings['std'] ) ? $option_settings['std'] : '';
						echo '<textarea name="' . esc_attr( $option_slug ) . '" id="' . esc_attr( $option_slug ) . '"  class="textarea regular-text v_option' . $content_class . '" row="5">'.( '' != $std ? esc_attr( $std ) : '' ).'</textarea>';
						break; 
            
            case 'divider':
            $std = isset( $option_settings['std'] ) ? $option_settings['std'] : '';
						echo '<span class="divider" rel-hide="'.$std.'"></span><i class="toggle closed"></i>';
						break; 

            case 'icon':
            $std = isset( $option_settings['std'] ) ? $option_settings['std'] : '';
            $this->vibe_editor_get_icons($option_slug);
            echo '<input name="' . esc_attr( $option_slug ) . '" type="text" id="' . esc_attr( $option_slug ) . '" value="'.( '' != $std ? esc_attr( $std ) : '' ).'" class="text regular-text v_option' . $content_class . '" />';
            break; 

            case 'color':
              $std = isset( $option_settings['std'] ) ? $option_settings['std'] : '';
              echo '<input name="' . esc_attr( $option_slug ) . '" type="text" id="' . esc_attr( $option_slug ) . '" value="'.( '' != $std ? esc_attr( $std ) : '' ).'" class="text color regular-text v_option' . $content_class . '" onchange="this.setAttribute(\'value\', this.value);" />';
            break;
            case 'upload':
            
						echo '<input name="' . esc_attr( $option_slug ) . '" type="hidden" id="' . esc_attr( $option_slug ) . '" value="" class="regular-text v_option v_upload_field' . $content_class . '" />' . '<img src="'.VIBE_URL.'/includes/metaboxes/images/image.png" class="uploaded_image" /><a href="#" rel-default="'.VIBE_URL.'/includes/metaboxes/images/image.png" class="remove_uploaded">cancel</a><a href="#" class="v_upload_button button">' . esc_html__('Upload', 'vibe-customtypes') . '</a>';
						break;
					 case 'slider_images':
            $std = isset( $option_settings['std'] ) ? $option_settings['std'] : '';
						echo '<div id="v_slider_images">' . '<div id="'.$std.'" class="slides v_option "></div>' . '<a href="#" id="v_add_slider_images" class="button button-primary button-large">' . esc_html__('Add Slider Image', 'vibe-customtypes') . '</a>' . '</div>';
						break;      
				}
				
				echo '</p>';
				
				++$i;
			}
			
			submit_button(__('Save Changes', 'vibe-customtypes'), 'vibe-button-save');
			
			echo '<input type="hidden" id="saved_module_name" value="' . esc_attr( $module_name ) . '" />';
			
			if ( '' != $paste_to_editor_id ) echo '<input type="hidden" id="paste_to_editor_id" value="' . esc_attr( $paste_to_editor_id ) . '" />';
			
			echo '</form>';
		}

		function v_get_attributes( $atts, $additional_classes = '', $additional_styles = '' ){
			extract( shortcode_atts(array(
            'container_css'=>'',
						'css_class' => '',
						'first_class' => '0',
						'width' => ''
					), $atts));
			$attributes = array( 'class' => '', 'inline_styles' => '' );
                        
			if ( '' != $css_class ) $css_class = ' ' . $css_class;
                        if ( '' != $container_css ) $container_css = 'data-class="' . $container_css.'"';
                        
			if ( '' != $additional_classes ) $additional_classes = ' ' . $additional_classes;
			$first_class = ( '1' == $first_class ) ? ' v_first' : ' ';
            
            $animation ='';
            if(isset($atts['animation_effect']) && $atts['animation_effect']){
            $animation = ' '.$atts['animation_effect'].'';
            }
                        
			$attributes['class'] = ' class="' . esc_attr( "v_module{$additional_classes}{$first_class}{$css_class}{$animation}" ) . '" '.$container_css.'';
			
			if ( '' != $width ) $attributes['inline_styles'] .= " width: {$width}%;";
			$attributes['inline_styles'] .= $additional_styles;
			if ( '' != $attributes['inline_styles'] ) $attributes['inline_styles'] = ' style="' . esc_attr( $attributes['inline_styles'] ) .'"';
			
			return $attributes;
		}

	
		function v_fix_shortcodes($content){   
			/*$replace_tags_from_to = array (
				'<p>[' => '[', 
				']</p>' => ']', 
				']<br />' => ']'
			);
			return strtr( $content, $replace_tags_from_to );*/
            return $content;
		}



    function disable_builder_option(){
    	global $post;
    	
    	$v_builder_enable = get_post_meta( $post->ID, '_enable_builder', true );
    	
    	wp_nonce_field( basename( __FILE__ ), 'vibe_editor_settings_nonce' );

    	echo '<p class="vibe_editor_option">'
    			. '<label for="builder_disable" class="builder_enable">'
    				. '<input name="builder_enable" type="checkbox" id="builder_enable" ' . checked( $v_builder_enable, 1, false ) . ' /></label>'
    		. '</p>';
    }


    function add_content_option(){
    	global $post;
    	
    	$v_add_content = get_post_meta( $post->ID, '_add_content', true );
    	
    	wp_nonce_field( basename( __FILE__ ), 'vibe_editor_settings_nonce' );

    	echo '<p class="vibe_editor_option content_addon">'
    			. '<label for="add_content">'
    				. __('Show Page Content','vibe-customtypes').'<select name="add_content" id="add_content" ><option value="no" '. selected($v_add_content, 'no', false).'> No</option><option value="yes_top" '. selected($v_add_content, 'yes_top', false).'> Yes, above Page Builder</option><option value="yes_below" '. selected($v_add_content, 'yes_below', false).'> Yes, Below Page Builder</option></select></label>'
    		. '</p>';
    }

    function vibe_editor_save_details( $post_id, $post ){
    	global $pagenow;

    	if ( 'post.php' != $pagenow ) return $post_id;
    		
    	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
    		return $post_id;

    	$post_type = get_post_type_object( $post->post_type );
    	if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) )
    		return $post_id;
    		
    	if ( ! isset( $_POST['vibe_editor_settings_nonce'] ) || ! wp_verify_nonce( $_POST['vibe_editor_settings_nonce'], basename( __FILE__ ) ) )
    		return $post_id;

    	if ( isset( $_POST['builder_enable'] ) )
    		update_post_meta( $post_id, '_enable_builder', 1 );
    	else
    		update_post_meta( $post_id, '_enable_builder', 0 );
            
      if ( isset( $_POST['add_content'] ) ){
        delete_post_meta($post_id, '_add_content' );
    		update_post_meta( $post_id, '_add_content', $_POST['add_content'] );
      }else{
    		update_post_meta( $post_id, '_add_content', 'no' );
      }
           
    }

    
    function vibe_builder_enqueue_shortcode_scripts() {
      global $post;
      $builder_enable = get_post_meta( $post->ID, '_enable_builder', true );
      $builder_layout = get_post_meta( $post->ID, '_builder_settings', true );
      if ( (isset($builder_layout) && !empty($builder_layout['layout_shortcode']) && has_shortcode($builder_layout['layout_shortcode'],'v_grid') ) || has_shortcode($post->post_content,'v_grid')) { 
            wp_dequeue_script('isotope');
            wp_enqueue_script( 'imagesloaded',plugins_url('js/imagesloaded.pkgd.min.js', __FILE__),array('jquery'),'1.0',true);
            wp_enqueue_script( 'isotope',plugins_url('js/isotope.pkgd.min.js',__FILE__),array('imagesloaded'),'1.0',true);
      }
    }

    function show_builder_layout( $content ){
    	global $post;
    	
    	$builder_enable = get_post_meta( $post->ID, '_enable_builder', true );
      $builder_layout = get_post_meta( $post->ID, '_builder_settings', true );
      $add_content = get_post_meta( $post->ID, '_add_content', true );
    	
            
                
    	if ( ! is_singular() || ! $builder_layout || ! is_main_query() || 0 == $builder_enable ) return $content;
    	
           
            
            
    	if ( isset($builder_layout) && '' != $builder_layout['layout_shortcode'] && $add_content == 'no') { 
             
                $content = '<div class="vibe_editor clearfix">' . 
                    do_shortcode( stripslashes( $builder_layout['layout_shortcode'] ) ) . 
                    '</div>';
              
            }
            
            if ( $builder_layout && '' != $builder_layout['layout_shortcode'] && $add_content == 'yes_top') {
                $content = $content.'<div class="vibe_editor clearfix">' . 
                    do_shortcode( stripslashes( $builder_layout['layout_shortcode'] ) ) . 
                    '</div>';
            }
            
            if ( $builder_layout && '' != $builder_layout['layout_shortcode'] && $add_content == 'yes_below') {
                $content = '<div class="vibe_editor clearfix">' . 
                    do_shortcode( stripslashes( $builder_layout['layout_shortcode'] ) ) . 
                    '</div>'.$content;
            }
            
            
    	return $content;
    } 



    function vibe_yoast_get_vibe_builder(){
      $post_id = $_POST['post_id'];
      $enabled = get_post_meta($post_id,'_enable_builder',true);
      if(!empty($enabled)){
          $content = get_post_meta($post_id,'_builder_settings',true);
          echo $content['layout_shortcode'];
      }
      die();
    }



    function vibe_editor_get_icons($id){

        ?>
        <link rel="stylesheet" href="<?php echo plugins_url( 'css/font-awesome.min.css' , __FILE__ ); ?>" type="text/css" media="all" />
        <link rel="stylesheet" href="<?php echo plugins_url( 'css/fontawesome-iconpicker.min.css' , __FILE__ ); ?>" type="text/css" media="all" />
        <script src="<?php echo plugins_url( 'js/fontawesome-iconpicker.min.js' , __FILE__ ); ?>"></script>
        <script>
        jQuery(document).ready(function($){
          $('#<?php echo $id; ?>').iconpicker({
            iconset: 'fontawesome',
            icon: 'fa-key',
            rows: 5,
            cols: 5,
            placement: 'top',
          });
        });
        </script>
        <?php
    }
}

WPLMS_Page_Builder::init();