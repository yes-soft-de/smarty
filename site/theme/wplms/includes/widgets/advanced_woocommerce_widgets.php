<?php

/**
 * FILE: advanced_woocommerce_widgets.php 
 * Created on Apr 4, 2013 at 4:09:14 PM 
 * Author: Mr.Vibe 
 * Credits: www.VibeThemes.com
 * Project: Max 
 * License: GPLv2
 */
if ( !defined( 'ABSPATH' ) ) exit;

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php')) ) {
    // Put your plugin code here


add_action( 'widgets_init','vibe_woo_widgets' );
function vibe_woo_widgets() {
	//register_widget('WC_Widget_Advanced_Layered_Nav');
    //register_widget('WC_Widget_Advanced_Layered_Nav_Filters');
    register_widget('vibe_woocommerce_carousels'); 
        //register_widget('vibe_woocommerce_mega_carousel'); 
}

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Widget_Advanced_Layered_Nav extends WP_Widget {

	var $widget_cssclass;
	var $widget_description;
	var $widget_idbase;
	var $widget_name;

	/**
	 * constructor
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {

		/* Widget variable settings. */
		$this->widget_cssclass 		= 'woocommerce widget_layered_nav';
		$this->widget_description	= __( 'Shows a custom attribute in a widget which lets you narrow down the list of products when viewing product categories.', 'vibe' );
		$this->widget_idbase 		= 'vibe_woocommerce_layered_nav';
		$this->widget_name 			= __( 'Vibe Advanced Layered Nav', 'vibe' );

		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->widget_cssclass, 'description' => $this->widget_description );

		/* Create the widget. */
		parent::__construct( 'vibe_woocommerce_layered_nav', $this->widget_name, $widget_ops );
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	function widget( $args, $instance ) {
            
		global $_chosen_attributes, $woocommerce, $_attributes_array;

		extract( $args );

		if ( ! is_post_type_archive( 'product' ) && ! is_tax( array_merge( $_attributes_array, array( 'product_cat', 'product_tag' ) ) ) )
			return;

		$current_term 	= $_attributes_array && is_tax( $_attributes_array ) ? get_queried_object()->term_id : '';
		$current_tax 	= $_attributes_array && is_tax( $_attributes_array ) ? get_queried_object()->taxonomy : '';

		$title 			= apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
		$taxonomy 		= $woocommerce->attribute_taxonomy_name($instance['attribute']);
		$query_type 	= isset( $instance['query_type'] ) ? $instance['query_type'] : 'and';
		$display_type 	= isset( $instance['display_type'] ) ? $instance['display_type'] : 'list';

		if ( ! taxonomy_exists( $taxonomy ) )
			return;

		$terms = get_terms( $taxonomy, array( 'hide_empty' => '1' ) );

		if ( count( $terms ) > 0 ) {

			ob_start();

			$found = false;

			echo $before_widget . $before_title . $title . $after_title;

			// Force found when option is selected - do not force found on taxonomy attributes
			if ( ! $_attributes_array || ! is_tax( $_attributes_array ) )
				if ( is_array( $_chosen_attributes ) && array_key_exists( $taxonomy, $_chosen_attributes ) )
					$found = true;} 

				// List display
				echo "<ul class='layered_nav_items'>";

				foreach ( $terms as $term ) {

					// Get count based on current view - uses transients
					$transient_name = 'wc_ln_count_' . md5( sanitize_key( $taxonomy ) . sanitize_key( $term->term_id ) );

					if ( false === ( $_products_in_term = get_transient( $transient_name ) ) ) {

						$_products_in_term = get_objects_in_term( $term->term_id, $taxonomy );

						set_transient( $transient_name, $_products_in_term );
					}

					$option_is_set = ( isset( $_chosen_attributes[ $taxonomy ] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) );

					// If this is an AND query, only show options with count > 0
					if ( $query_type == 'and' ) {

						$count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->filtered_product_ids ) );

						// skip the term for the current archive
						if ( $current_term == $term->term_id )
							continue;

						if ( $count > 0 && $current_term !== $term->term_id )
							$found = true;

						if ( $count == 0 && ! $option_is_set )
							continue;

					// If this is an OR query, show all options so search can be expanded
					} else {

						// skip the term for the current archive
						if ( $current_term == $term->term_id )
							continue;

						$count = sizeof( array_intersect( $_products_in_term, $woocommerce->query->unfiltered_product_ids ) );

						if ( $count > 0 )
							$found = true;

					}

					$arg = 'filter_' . sanitize_title( $instance['attribute'] );

					$current_filter = ( isset( $_GET[ $arg ] ) ) ? explode( ',', $_GET[ $arg ] ) : array();

					if ( ! is_array( $current_filter ) )
						$current_filter = array();

					$current_filter = array_map( 'esc_attr', $current_filter );

					if ( ! in_array( $term->term_id, $current_filter ) )
						$current_filter[] = $term->term_id;

					// Base Link decided by current page
					$shop_page_id = '';
	                if(function_exists('wc_get_page_id')){
	                   $shop_page_id = wc_get_page_id('shop');
	                }else{
	                  $shop_page_id =  woocommerce_get_page_id( 'shop' );
	                }
					if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
						$link = home_url();
					} elseif ( is_post_type_archive( 'product' ) || is_page( $shop_page_id ) ) {
						$link = get_post_type_archive_link( 'product' );
					} else {
						$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
					}

					// All current filters
					if ( $_chosen_attributes ) {
						foreach ( $_chosen_attributes as $name => $data ) {
							if ( $name !== $taxonomy ) {

								//exclude query arg for current term archive term
								while ( in_array( $current_term, $data['terms'] ) ) {
									$key = array_search( $current_term, $data );
									unset( $data['terms'][$key] );
								}

								if ( ! empty( $data['terms'] ) )
									$link = add_query_arg( sanitize_title( str_replace( 'pa_', 'filter_', $name ) ), implode(',', $data['terms']), $link );

								if ( $data['query_type'] == 'or' )
									$link = add_query_arg( sanitize_title( str_replace( 'pa_', 'query_type_', $name ) ), 'or', $link );
							}
						}
					}

					// Min/Max
					if ( isset( $_GET['min_price'] ) )
						$link = add_query_arg( 'min_price', $_GET['min_price'], $link );

					if ( isset( $_GET['max_price'] ) )
						$link = add_query_arg( 'max_price', $_GET['max_price'], $link );

					// Current Filter = this widget
					if ( isset( $_chosen_attributes[ $taxonomy ] ) && is_array( $_chosen_attributes[ $taxonomy ]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) ) {

						$class = 'class="chosen layered_nav '.strtolower($term->name).'"';

						// Remove this term is $current_filter has more than 1 term filtered
						if ( sizeof( $current_filter ) > 1 ) {
							$current_filter_without_this = array_diff( $current_filter, array( $term->term_id ) );
							$link = add_query_arg( $arg, implode( ',', $current_filter_without_this ), $link );
						}

					} else {

						$class = 'class="layered_nav '.strtolower($term->name).'"';
						$link = add_query_arg( $arg, implode( ',', $current_filter ), $link );

					}

					// Search Arg
					if ( get_search_query() )
						$link = add_query_arg( 's', get_search_query(), $link );

					// Post Type Arg
					if ( isset( $_GET['post_type'] ) )
						$link = add_query_arg( 'post_type', $_GET['post_type'], $link );

					// Query type Arg
					if ( $query_type == 'or' && ! ( sizeof( $current_filter ) == 1 && isset( $_chosen_attributes[ $taxonomy ]['terms'] ) && is_array( $_chosen_attributes[ $taxonomy ]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[ $taxonomy ]['terms'] ) ) )
						$link = add_query_arg( 'query_type_' . sanitize_title( $instance['attribute'] ), 'or', $link );

					echo '<li ' . $class . '>';

					echo ( $count > 0 || $option_is_set ) ? '<a href="' . esc_url( apply_filters( 'woocommerce_layered_nav_link', $link ) ) . '">' : '<span>';

					echo $term->name;

					echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';

					echo ' <small class="count">' . $count . '</small></li>';

				}

				echo "</ul>";


			echo $after_widget;

			if ( ! $found )
				ob_end_clean();
			else
				echo ob_get_clean();
		}
	/**
	 * update function.
	 *
	 * @see WP_Widget->update
	 * @access public
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		global $woocommerce;

		if ( empty( $new_instance['title'] ) )
			$new_instance['title'] = $woocommerce->attribute_label( $new_instance['attribute'] );

		$instance['title'] 			= strip_tags( stripslashes($new_instance['title'] ) );
		$instance['attribute'] 		= stripslashes( $new_instance['attribute'] );
		$instance['query_type'] 	= stripslashes( $new_instance['query_type'] );

		return $instance;
	}

	/**
	 * form function.
	 *
	 * @see WP_Widget->form
	 * @access public
	 * @param array $instance
	 * @return void
	 */
	function form( $instance ) {
		global $woocommerce;

		if ( ! isset( $instance['query_type'] ) )
			$instance['query_type'] = 'and';
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'vibe' ) ?></label>
		<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php if ( isset( $instance['title'] ) ) echo esc_attr( $instance['title'] ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'attribute' ); ?>"><?php _e( 'Attribute:', 'vibe' ) ?></label>
		<select id="<?php echo esc_attr( $this->get_field_id( 'attribute' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'attribute' ) ); ?>">
			<?php
			$attribute_taxonomies = $woocommerce->get_attribute_taxonomies();
			if ( $attribute_taxonomies )
				foreach ( $attribute_taxonomies as $tax )
					if ( taxonomy_exists( $woocommerce->attribute_taxonomy_name( $tax->attribute_name ) ) )
						echo '<option value="' . $tax->attribute_name . '" ' . selected( ( isset( $instance['attribute'] ) && $instance['attribute'] == $tax->attribute_name ), true, false ) . '>' . $tax->attribute_name . '</option>';
			?>
		</select></p>

		
		<p><label for="<?php echo $this->get_field_id( 'query_type' ); ?>"><?php _e( 'Query Type:', 'vibe' ) ?></label>
		<select id="<?php echo esc_attr( $this->get_field_id( 'query_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'query_type' ) ); ?>">
			<option value="and" <?php selected( $instance['query_type'], 'and' ); ?>><?php _e( 'AND', 'vibe' ); ?></option>
			<option value="or" <?php selected( $instance['query_type'], 'or' ); ?>><?php _e( 'OR', 'vibe' ); ?></option>
		</select></p>
		<?php
	}
}



function woocommerce_advanced_layered_nav_init( ) {

	if ( is_active_widget( false, false, 'vibe_woocommerce_layered_nav', true ) && ! is_admin() ) {

		global $_chosen_attributes, $woocommerce, $_attributes_array;

		$_chosen_attributes = $_attributes_array = array();

		$attribute_taxonomies = $woocommerce->get_attribute_taxonomies();
		if ( $attribute_taxonomies ) {
			foreach ( $attribute_taxonomies as $tax ) {

		    	$attribute = sanitize_title( $tax->attribute_name );
		    	$taxonomy = $woocommerce->attribute_taxonomy_name( $attribute );

				// create an array of product attribute taxonomies
				$_attributes_array[] = $taxonomy;

		    	$name = 'filter_' . $attribute;
		    	$query_type_name = 'query_type_' . $attribute;

		    	if ( ! empty( $_GET[ $name ] ) && taxonomy_exists( $taxonomy ) ) {

		    		$_chosen_attributes[ $taxonomy ]['terms'] = explode( ',', $_GET[ $name ] );

		    		if ( ! empty( $_GET[ $query_type_name ] ) && $_GET[ $query_type_name ] == 'or' )
		    			$_chosen_attributes[ $taxonomy ]['query_type'] = 'or';
		    		else
		    			$_chosen_attributes[ $taxonomy ]['query_type'] = 'and';

				}
			}
	    }

	    add_filter('loop_shop_post_in', 'woocommerce_layered_nav_query');
    }
}

add_action( 'init', 'woocommerce_advanced_layered_nav_init', 2 );




/**
 *  Advanced Layered Navigation Fitlers Widget
 *
 */

class WC_Widget_Advanced_Layered_Nav_Filters extends WP_Widget {

	var $widget_cssclass;
	var $widget_description;
	var $widget_idbase;
	var $widget_name;

	/**
	 * constructor
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {

		/* Widget variable settings. */
		$this->widget_cssclass 		= 'woocommerce widget_layered_nav_filters';
		$this->widget_description	= __( 'Shows active layered nav filters so users can see and deactivate them.', 'vibe' );
		$this->widget_idbase 		= 'vibe_woocommerce_layered_nav_filters';
		$this->widget_name 			= __( 'Vibe Advanced Layered Nav Filters', 'vibe' );

		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->widget_cssclass, 'description' => $this->widget_description );

		/* Create the widget. */
		parent::__construct( 'vibe_woocommerce_layered_nav_filters', $this->widget_name, $widget_ops );
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	function widget( $args, $instance ) {
		global $_chosen_attributes, $woocommerce, $_attributes_array;

		extract( $args );

		if ( ! is_post_type_archive( 'product' ) && is_array( $_attributes_array ) && ! is_tax( array_merge( $_attributes_array, array( 'product_cat', 'product_tag' ) ) ) )
			return;

		$current_term 	= $_attributes_array && is_tax( $_attributes_array ) ? get_queried_object()->term_id : '';
		$current_tax 	= $_attributes_array && is_tax( $_attributes_array ) ? get_queried_object()->taxonomy : '';

		$title = ( ! isset( $instance['title'] ) ) ? __( 'Active filters', 'vibe' ) : $instance['title'];
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base);

		// Price
		$min_price = isset( $_GET['min_price'] ) ? esc_attr( $_GET['min_price'] ) : 0;
		$max_price = isset( $_GET['max_price'] ) ? esc_attr( $_GET['max_price'] ) : 0;

		if ( count( $_chosen_attributes ) > 0 || $min_price > 0 || $max_price > 0 ) {

			echo $before_widget;
			if ( $title ) {
				echo $before_title . $title . $after_title;
			}

			echo "<ul>";

			// Attributes
			if(isset($_chosen_attributes)){
			foreach ( $_chosen_attributes as $taxonomy => $data ) {

				foreach ( $data['terms'] as $term_id ) {
					$term 				= get_term( $term_id, $taxonomy );
					$taxonomy_filter 	= str_replace( 'pa_', '', $taxonomy );
					$current_filter 	= ! empty( $_GET[ 'filter_' . $taxonomy_filter ] ) ? $_GET[ 'filter_' . $taxonomy_filter ] : '';
					$new_filter			= array_map( 'absint', explode( ',', $current_filter ) );
					$new_filter			= array_diff( $new_filter, array( $term_id ) );

					$link = remove_query_arg( 'filter_' . $taxonomy_filter );

					if ( sizeof( $new_filter ) > 0 )
						$link = add_query_arg( 'filter_' . $taxonomy_filter, implode( ',', $new_filter ), $link );

					echo '<li class="chosen layered_nav ' . strtolower($term->name) . '"><a title="' . __( 'Remove filter', 'vibe' ) . '" href="' . $link . '"><span class="remove"><i class="icon-cancel"></i></span></a></li>';
				}
			}
			}
			if ( $min_price ) {
				$link = remove_query_arg( 'min_price' );
				echo '<li class="chosen layered_nav min_price"><a title="' . __( 'Remove filter', 'vibe' ) . '" href="' . $link . '"><span class="remove"><i class="icon-cancel"></i></span>'.__('Greater than','vibe').' ' . woocommerce_price( $min_price ) . '</a></li>';
			}

			if ( $max_price ) {
				$link = remove_query_arg( 'max_price' );
				echo '<li class="chosen layered_nav max_price"><a title="' . __( 'Remove filter', 'vibe' ) . '" href="' . $link . '"><span class="remove"><i class="icon-cancel"></i></span>'.__('Less than','vibe').'  ' . woocommerce_price( $max_price ) . '</a></li>';
			}

			echo "</ul>";

			echo $after_widget;
		}
	}
}




}


/*======= Vibe WooCommerce Carousel ======== */  

class vibe_woocommerce_carousels extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function __construct() {
		$widget_ops = array( 'classname' => 'vibe_woocommerce_carousels', 'description' => __('Vibe WooCommerce Carousels ', 'vibe') );
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'vibe_woocommerce_carousels' );
		parent::__construct( 'vibe_woocommerce_carousels', __('Vibe WooCommerce Carousels', 'vibe'), $widget_ops, $control_ops );
	}
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
                
                $shortcode= '['.$instance['function'].' per_page="'.$instance['number'].'" ]';
		
               
		echo $before_widget;

		// Display the widget title 
		if ( $title )
			echo '<h3 class="heading"><span>' . $title . '</span></h3>';
                
                $rand='woo_prds'.rand(1,999);
              
               
               echo '<div id="'.$rand.'" class="vibe_carousel flexslider '.$instance['controls'].' columns'.$instance['columns'].'">'
                       .do_shortcode($shortcode).
                    '</div>';
                
                
                echo $after_widget;
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {		
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['function'] = $new_instance['function'];
        $instance['columns'] = $new_instance['columns'];
        $instance['controls'] = $new_instance['controls'];
        $instance['number'] = $new_instance['number'];
        return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {	
        $defaults = array( 
                                'title'  => 'Featured Products',
                                'function'  => '',
                                'scroll'  => '',
                                'columns'  => '',
                                'controls'  => '',
                                'number'  => ''
                    );
	$instance = wp_parse_args( (array) $instance, $defaults );
                
        $title 	= esc_attr($instance['title']);                               
        ?>
         
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
	<p>
          <label for="<?php echo $this->get_field_id('function'); ?>"><?php _e('WooCommerce Function ','vibe'); ?></label> 
           <select class="select" name="<?php echo $this->get_field_name('function'); ?>">
               <option value="featured_products" <?php selected($instance['function'],'featured_products'); ?>>Featured Products</option>              
               <option value="recent_products" <?php selected($instance['function'],'recent_products'); ?>>Recent Products</option>              
               <option value="sale_products" <?php selected($instance['function'],'sale_products'); ?>>Sale Products</option>              
               <option value="best_selling_products" <?php selected($instance['function'],'best_selling_products'); ?>>Best Selling Products</option>              
               <option value="top_rated_products" <?php selected($instance['function'],'top_rated_products'); ?>>Top Rated Products</option>                         
           </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('columns'); ?>"><?php _e('Product Columns','vibe'); ?></label> 
           <select class="select" name="<?php echo $this->get_field_name('columns'); ?>">
               <option value="1" <?php selected($instance['columns'],1); ?>>1</option>              
               <option value="2" <?php selected($instance['columns'],2); ?>>2</option>              
               <option value="3" <?php selected($instance['columns'],3); ?>>3</option>                        
               <option value="4" <?php selected($instance['columns'],4); ?>>4</option>                        
               <option value="5" <?php selected($instance['columns'],5); ?>>5</option>                        
               <option value="6" <?php selected($instance['columns'],6); ?>>6</option>                        
           </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of Products in Carousel:','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" value="<?php echo $instance['number']; ?>" />
        </p>
        
         <p>
          <label for="<?php echo $this->get_field_id('controls'); ?>"><?php _e('Carousel Controls','vibe'); ?></label> 
           <select class="select" name="<?php echo $this->get_field_name('controls'); ?>">
               <option value="direction" <?php selected($instance['controls'],'direction'); ?>>Direction Arrows</option>              
               <option value="control" <?php selected($instance['controls'],'control'); ?>>Control Buttons</option>              
               <option value="none" <?php selected($instance['controls'],'none'); ?>>None</option>                       
           </select>
        </p>
        
        <?php 
    }
}


/*======= Vibe WooCommerce Carousel ======== */  

class vibe_woocommerce_mega_carousel extends WP_Widget {
 
 
    /** constructor -- name this the same as the class above */
    function __construct() {
		$widget_ops = array( 'classname' => 'vibe_woocommerce_mega_carousel', 'description' => __('Vibe WooCommerce Mega Carousel ', 'vibe') );
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'vibe_woocommerce_mega_carousel' );
		parent::__construct( 'vibe_woocommerce_mega_carousel', __('Vibe WooCommerce Mega Carousel', 'vibe'), $widget_ops, $control_ops );
	}
        
 
    /** @see WP_Widget::widget -- do not rename this */
    function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
                $button = array();
                
                if(is_array($instance['function'])){ $i=0;
                    if(is_array($instance['fx_title']))
                    $names= explode(',',$instance['fx_title']);
                    
                    foreach($instance['function'] as $function){ 
                        $shortcode[$i] = $function;
                        
                        if(isset($names[$i]))
                            $button[$i]=$names[$i];
                        else{ 
                            switch($instance['function'][$i]){
                                case 'featured_products':$button[$i] = 'Featured Products';
                                    break;
                                case 'recent_products':$button[$i] = 'Recent Products';
                                    break;
                                case 'sale_products':$button[$i] = 'Sale Products';
                                    break;
                                case 'best_selling_products':$button[$i] = 'Best Selling Products';
                                    break;
                                case 'top_rated_products':$button[$i] = 'Top Rated Products';
                                    break;
                            }
                        }
                            
                        $i++;
                    }
                  
                }
		$btnstring='';$i=0;
                foreach($button as $btn){
                    $btnstring .= '<a class="megacarousel" rel-shortcode="'.$shortcode[$i].'">'.$btn.'</a>';
                    $i++;
                    if($i < count($button))
                    $btnstring .= ' / ';
                }
                
		echo $before_widget;

                
		// Display the widget title 
		if ( $title )
			echo '<h3 class="heading"><span>' . $btnstring . '</span></h3>';
                
                $rand='woo_prds'.rand(1,999);
               foreach($shortcode as $sh){
               echo '<div class="vibe_mega_carousel vibe_carousel flexslider '.$instance['controls'].'  columns'.$instance['columns'].'">
                      ['.$sh.' per_page="'.$instance['number'].'] 
                     </div>';
               }
                
                echo $after_widget;
                
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {		
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['function'] = $new_instance['function'];
                $instance['fx_title'] = $new_instance['fx_title'];
                $instance['columns'] = $new_instance['columns'];
                $instance['controls'] = $new_instance['controls'];
                $instance['number'] = $new_instance['number'];
        return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {	
        $defaults = array( 
                                'title'  => 'Featured Products',
                                'function'  => array(),
                                'fx_title'  => '',
                                'columns'  => '',
                                'controls'  => '',
                                'number'  => ''
                    );
	$instance = wp_parse_args( (array) $instance, $defaults );
               
        $title 	= esc_attr($instance['title']);                               
        ?>
         
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
	<p>
          <label for="<?php echo $this->get_field_id('function'); ?>"><?php _e('WooCommerce Function ','vibe'); ?></label> 
           <select class="select chzn_select" name="<?php echo $this->get_field_name('function'); ?>[]" multiple>
               <option value="featured_products" <?php  if(in_array('featured_products',$instance['function']))echo 'SELECTED'; ?>>Featured Products</option>              
               <option value="recent_products" <?php if(in_array('recent_products',$instance['function'])) echo 'SELECTED'; ?>>Recent Products</option>              
               <option value="sale_products" <?php if(in_array('sale_products',$instance['function']))echo 'SELECTED'; ?>>Sale Products</option>              
               <option value="best_selling_products" <?php if(in_array('best_selling_products',$instance['function']))echo 'SELECTED'; ?>>Best Selling Products</option>              
               <option value="top_rated_products" <?php if(in_array('top_rated_products',$instance['function']))echo 'SELECTED'; ?>>Top Rated Products</option>                         
           </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('fx_title'); ?>"><?php _e('Above selected WooCommerce Functions Titles (comma seperated)','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('fx_title'); ?>" name="<?php echo $this->get_field_name('fx_title'); ?>" type="text" value="<?php echo $instance['fx_title']; ?>" /> 
        </p>
        <p>
        <p>
          <label for="<?php echo $this->get_field_id('columns'); ?>"><?php _e('Product Columns','vibe'); ?></label> 
           <select class="select" name="<?php echo $this->get_field_name('columns'); ?>">
               <option value="1" <?php selected($instance['columns'],1); ?>>1</option>              
               <option value="2" <?php selected($instance['columns'],2); ?>>2</option>              
               <option value="3" <?php selected($instance['columns'],3); ?>>3</option>                        
               <option value="4" <?php selected($instance['columns'],4); ?>>4</option>                        
               <option value="5" <?php selected($instance['columns'],5); ?>>5</option>                        
               <option value="6" <?php selected($instance['columns'],6); ?>>6</option>                        
           </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of Products in Carousel:','vibe'); ?></label> 
          <input class="regular_text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" value="<?php echo $instance['number']; ?>" />
        </p>
        
         <p>
          <label for="<?php echo $this->get_field_id('controls'); ?>"><?php _e('Carousel Controls','vibe'); ?></label> 
           <select class="select" name="<?php echo $this->get_field_name('controls'); ?>">
               <option value="direction" <?php selected($instance['controls'],'direction'); ?>>Direction Arrows</option>              
               <option value="control" <?php selected($instance['controls'],'control'); ?>>Control Buttons</option>              
               <option value="none" <?php selected($instance['controls'],'none'); ?>>None</option>                       
           </select>
        </p>
        
        <?php 
    }
}