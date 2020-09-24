<?php
/**
 * Custom Walker
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/

if ( !defined( 'ABSPATH' ) ) exit;

class vibe_walker extends Walker_Nav_Menu{
       
      function start_el(&$output, $item, $depth =0, $args=Array(),$id=0) 
      { 
           global $wp_query;
           $menuargs = $args;
           $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

           $class_names = $value = '';

           $classes = empty( $item->classes ) ? array() : (array) $item->classes;
           
           $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
           if((!empty($item->sidebar) && strlen($item->sidebar) > 2) || (!empty($item->megamenu_type))){
              $class_names .= ' megadrop '.(empty($item->megamenu_type)?'':$item->megamenu_type);
           }
           
           if(!empty($item->menu_width)){
                $width = (is_numeric($item->menu_width)?$item->menu_width.'px':$item->menu_width);         
           }
           
           $class_names = ' class="'. esc_attr( $class_names ) . '" '.(!empty($item->menu_width)?'data-width="'.$width.'"':'');

           $output .= $indent . '<li id="main-menu-item-'. $item->ID . '"' . $value . $class_names .'>';

           $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
           $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
           $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
           $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

           $prepend = '<strong>';
           $append = '</strong>';
           $description  = ! empty( $item->description ) ? '<span>'.esc_attr( $item->description ).'</span>' : '';

           if($depth != 0)
            {
	           $description = $append = $prepend = "";
            }
           
            $item_output = $menuargs->before;
            $mega_menu_id = 'vibe-mega-'.sanitize_title($item->megamenu_type).rand(0,99);
            
            switch($item->columns){
                case 1:
                  $class = 'col-md-12 clear1';
                break;
                case 2:
                  $class = 'col-md-6 clear2';
                break;
                case 3:
                  $class = 'col-md-4 clear3';
                break;
                case 4:
                case 5:
                  $class = 'col-md-3 clear4';
                break;
            }
            

            if(!empty($item->sidebar) && strlen($item->sidebar) > 2){
                $item_output .= $this->sidebar($item->sidebar,$item->columns,$item);
            }else if(!empty($item->megamenu_type) && strlen($item->megamenu_type) > 2){
                if($item->megamenu_type == 'cat_subcat'){

                  $item_output .= '<div id="'.$mega_menu_id.'" class="menu-cat_subcat column'.$item->columns.'">';
                  if(!empty($item->taxonomy)){
                      $args =array(
                                  'taxonomy' => $item->taxonomy,
                                  'hide_empty' => false,
                                );
                      if($item->taxonomy == 'course-cat'){
                          $args['orderby']='meta_value_num';
                          $args['meta_key']='course_cat_order';
                          $args['order']= 'desc';
                      }
                      $args = apply_filters('wplms_megamenu_filter',$args,$item);
                      $terms = get_terms($args );
                      $term_array = $hide_terms = array();
                      if(!empty($item->hide_taxonomy_terms)){
                        $hide_terms = explode(',',$item->hide_taxonomy_terms);
                      }

                      if(!empty($terms)){
                        foreach($terms as $term){
                          if(!in_array($term->slug,$hide_terms)){
                            if(empty($term->parent)){
                              $term_array[$term->term_id]['term'] = array('title'=>$term->name,'slug'=>$term->slug);  
                            }else{
                              $term_array[$term->parent]['children'][$term->term_id] =  array('title'=>$term->name,'slug'=>$term->slug);  
                            }
                          }
                        }
                      }


                      if(!empty($term_array)){
                        $item_output .= '<ul class="taxonomy_menu '.$item->taxonomy.'_menu">';
                       
                           

                          foreach($term_array as $id => $t){
                            
                            $item_output .= '<li>';
                            $item_output .= '<a href="'.get_term_link($id).'" class="term_'.(empty($t['term']['slug'])?'':$t['term']['slug']).'">'.$t['term']['title'].'</a>';
                          
                          if(isset($t['children'])){
                            $item_output .='<div class="sub_cat_menu column'.$item->columns.' '.$item->taxonomy.'_'.(empty($t['term']['slug'])?'':$t['term']['slug']).'_menu "><div class="row"><div class="taxonomy_submenu ">';
                            foreach($t['children'] as $k=>$child){

                              $item_output .= '<div class="'.$class.'"><a href="'.get_term_link($k).'">'.$child['title'].'</a></div>';
                            }
                            $item_output .='</div></div></div>';
                          }
                          $item_output .= '</li>';
                        }  
                        $item_output .= '</ul>';
                      }
                      
                  }
                  $item_output .= '</div>';
                }
                if($item->megamenu_type == 'cat_posts'){

                  $item_output .= '<div id="'.$mega_menu_id.'" class="menu-cat_subcat column'.$item->columns.'">';
                  if(!empty($item->taxonomy)){
                      $args =array(
                                  'taxonomy' => $item->taxonomy,
                                  'hide_empty' => false,
                                );
                      if($item->taxonomy == 'course-cat'){
                          $args['orderby']='meta_value_num';
                          $args['meta_key']='course_cat_order';
                          $args['order']= 'desc';
                      }
                      $args = apply_filters('wplms_megamenu_filter',$args,$item);
                      $terms = get_terms($args );


                      if(!empty($terms)){
                        $hide_terms = array();
                        if(!empty($item->hide_taxonomy_terms)){
                          $hide_terms = explode(',',$item->hide_taxonomy_terms);
                        }
                        $item_output .= '<ul class="taxonomy_menu '.$item->taxonomy.'_posts_menu">';
                        foreach($terms as $term){
                          if(!in_array($term->slug,$hide_terms)){
                            $item_output .= '<li>';
                            $item_output .= '<a href="'.get_term_link($term->term_id).'">'.$term->name.'</a>';
                            $item_output .='<div class="sub_cat_menu sub_posts_menu column'.$item->columns.'"><div class="row"><div class="taxonomy_submenu '.$item->taxonomy.'_'.$term->slug.'_menu">';

                            $max = (empty($item->max_elements)?$item->columns:$item->max_elements);
                            $args = apply_filters('wplms_megamenu_filter',array(
                                'post_type' => 'any',  
                                'orderby'=>'menu_order',
                                'order'=>'desc',
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => $item->taxonomy, 
                                        'field' => 'slug',     
                                        'terms' => $term->slug,
                                    )
                                ),
                                'posts_per_page'=>$max,
                                'cache_results'=>true
                            ),$item);
                            $query = new WP_Query( $args );

                            while ( $query->have_posts() ) : $query->the_post();
                              global $post;
                              $item_output .= '<div class="'.$class.'">';
                              $item_output .= '<a href="'.get_permalink($post->ID).'">';
                              $item_output .= '<div class="menu_featured">';
                              if(has_post_thumbnail()){ 
                                $item_output .= get_the_post_thumbnail($post->ID,'medium'); 
                              }
                              $item_output .= '<strong>'.get_the_title().'</strong>';

                              $item_output .= '</div>';
                              $item_output .= '</a>';
                              $item_output .='</div>';
                            endwhile;

                            wp_reset_query();
                            $item_output .='</div></div></div>';
                            $item_output .= '</li>';
                          }
                        }
                      }
                  }
                  $item_output .= '</div>';
                }
            }else{
              $item_output .= '<a'. $attributes .'>';
              $item_output .= $menuargs->link_before .$prepend.apply_filters( 'the_title', $item->title, $item->ID ).$append;
              $item_output .= $description;
              
              if(!empty($item->attr_title))
                $item_output .= '<span>'.$item->attr_title.'</span>';

              $item_output .= $menuargs->link_after;
              $item_output .= '</a>';
            }
            
            $item_output .= $menuargs->after;

            $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $menuargs );
            }
            
        /* 
	 * Show a sidebar
	 */
	function sidebar($name,$columns,$item){
		
		if(function_exists('dynamic_sidebar')){
			ob_start();
          if(is_object($item)){
            $width = (is_numeric($item->menu_width)?$item->menu_width.'px':$item->menu_width);
          }
            if(empty($width)){$width = '100%';}
			echo '<div id="vibe-mega-'.sanitize_title($name).'" data-width="'.$width.'" class="menu-sidebar column'.$columns.'">';
			 dynamic_sidebar($name);		
			echo '</div>';
			return ob_get_clean();
		}
		return 'none';
	}
}
