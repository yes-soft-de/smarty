<?php

 if ( ! defined( 'ABSPATH' ) ) exit;

function thumbnail_generator($custom_post,$featured_style,$cols='medium',$n=100,$link=0,$zoom=0){
    $return=$read_more=$class='';    

    $more = __('Read more','vibe-customtypes');
    
    if(strlen($custom_post->post_content) > $n)
        $read_more= '<a href="'.get_permalink($custom_post->ID).'" class="link">'.$more.'</a>';

    $cache_duration = vibe_get_option('cache_duration');
    if(!empty($cache_duration)){
        $key= $featured_style.'_'.$custom_post->post_type.'_'.$custom_post->ID;
        if(is_user_logged_in()){
            $user_id = get_current_user_id();
            $user_meta = get_user_meta($user_id,$custom_post->ID,true);
            if(isset($user_meta)){
                $key .= '_'.$user_id;
            }
        }
        $result = wp_cache_get($key,'featured_block');
    }else{$result=false;}

    if ( false === $result ) {
                    
    switch($featured_style){

            case 'course':
            
                    $return .='<div class="block courseitem" data-id="'.$custom_post->ID.'">';
                    $return .='<div class="block_media">';
                    $return .= apply_filters('vibe_thumb_featured_image',featured_component($custom_post->ID,$cols),$featured_style);
                    $return .='</div>';
                    $return .='<div class="block_content">';
                    $return .= apply_filters('vibe_thumb_heading','<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>',$featured_style);
                    
                    $category='';
                    if(get_post_type($custom_post->ID) == 'course'){
                        $rating=get_post_meta($custom_post->ID,'average_rating',true);
                        $rating_count=get_post_meta($custom_post->ID,'rating_count',true);
                        $meta = '<div class="star-rating">';
                        if(function_exists('bp_course_display_rating')){
                           $meta .= bp_course_display_rating($rating);
                        }else{
                            for($i=1;$i<=5;$i++){

                                if(isset($rating)){
                                    if($rating >= 1){
                                        $meta .='<span class="fill"></span>';
                                    }elseif(($rating < 1 ) && ($rating > 0.4 ) ){
                                        $meta .= '<span class="half"></span>';
                                    }else{
                                        $meta .='<span></span>';
                                    }
                                    $rating--;
                                }else{
                                    $meta .='<span></span>';
                                }
                            }
                        }
                        $meta =  apply_filters('vibe_thumb_rating',$meta,$featured_style,$rating);
                        $meta .= '</div>';
                         
                        $free_course = get_post_meta($custom_post->ID,'vibe_course_free',true);

                        $meta .=bp_course_get_course_credits(array('id'=>$custom_post->ID));
                        
                        $meta .='<span class="clear"></span>';

                        $enable_instructor = apply_filters('wplms_display_instructor',true,$custom_post->ID);
                        if($enable_instructor){
                            $instructor_meta='';
                            if(function_exists('bp_course_get_instructor'))
                                
                                $instructor_meta .= bp_course_get_instructor(array('instructor_id'=>$custom_post->post_author,'post_id'=>$custom_post->ID));
                            
                            $meta .= apply_filters('vibe_thumb_instructor_meta',$instructor_meta,$featured_style);
                        }
                        $st = get_post_meta($custom_post->ID,'vibe_students',true);
                        if(isset($st) && $st !='')
                            $meta .= apply_filters('vibe_thumb_student_count','<strong><i class="fa fa-users"></i> '.$st.'</strong>');
    
                        $return .= $meta;
                    }
                    $return .=apply_filters('wplms_course_thumb_extras',''); // Use this filter to add Extra HTML to the course featured block
                    $return .='</div>';
                    $return .='</div>';

                break;
           case 'course2':
            
                    $return .='<div class="block courseitem course2"  data-id="'.$custom_post->ID.'">';
                    $return .='<div class="block_media">';
                    $return .= apply_filters('vibe_thumb_featured_image',featured_component($custom_post->ID,$cols),$featured_style);
                    $return .='</div>';
                    
                    $return .='<div class="block_content">';
                    $return .= apply_filters('vibe_thumb_heading','<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>',$featured_style);

                    $category='';
                    if(get_post_type($custom_post->ID) == 'course'){
                        $rating=get_post_meta($custom_post->ID,'average_rating',true);
                        $rating_count=get_post_meta($custom_post->ID,'rating_count',true);
                        $meta = '<div class="star-rating">';
                        if(function_exists('bp_course_display_rating')){
                            $meta .= bp_course_display_rating($rating);
                        }else{
                            
                            for($i=1;$i<=5;$i++){

                                if(isset($rating)){
                                    if($rating >= 1){
                                        $meta .='<span class="fill"></span>';
                                    }elseif(($rating < 1 ) && ($rating > 0.4 ) ){
                                        $meta .= '<span class="half"></span>';
                                    }else{
                                        $meta .='<span></span>';
                                    }
                                    $rating--;
                                }else{
                                    $meta .='<span></span>';
                                }
                            }
                        }
                        $meta =  apply_filters('vibe_thumb_rating',$meta,$featured_style,$rating);
                        $meta .= apply_filters('vibe_thumb_reviews','(&nbsp;'.(isset($rating_count)?$rating_count:'0').'&nbsp;'.__('REVIEWS','vibe-customtypes').'&nbsp;)',$featured_style).'</div>';
                        $free_course = get_post_meta($custom_post->ID,'vibe_course_free',true);

                        $st = get_post_meta($custom_post->ID,'vibe_students',true);
                        if(isset($st) && $st !='')
                            $meta .= apply_filters('vibe_thumb_student_count','<strong><i class="fa fa-users"></i>&nbsp;'.$st.'</strong>');
                        $meta .='<span class="clear"></span>';
                        $enable_instructor = apply_filters('wplms_display_instructor',true,$custom_post->ID);
                        if($enable_instructor){
                            $instructor_meta='';
                            if(function_exists('bp_course_get_instructor'))
                                $instructor_meta .= bp_course_get_instructor();
                            
                            $meta .= apply_filters('vibe_thumb_instructor_meta',$instructor_meta,$featured_style);
                        }
                        $meta .=bp_course_get_course_credits(array('id'=>$custom_post->ID));

                        $return .= $meta;
                    }
                    $return .=apply_filters('wplms_course_thumb_extras',''); // Use this filter to add Extra HTML to the course featured block
                    $return .='</div>';
                    $return .='</div>';

                break;  
            case 'course3':
                    
                    $return .='<div class="block courseitem course3"  data-id="'.$custom_post->ID.'">';
                    $return .='<div class="block_media">';
                    $return .= apply_filters('vibe_thumb_featured_image',featured_component($custom_post->ID,$cols),$featured_style);
                    $return .='</div>';
                    $return .= '<div class="block_content">';
                    $return .= apply_filters('vibe_thumb_heading', '<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>');
                    $return .= '<div class="course_price">'.bp_course_get_course_credits().'</div>';
                    $enable_instructor = apply_filters('wplms_display_instructor',true,$custom_post->ID);
                    if($enable_instructor){
                        if ( function_exists('get_coauthors')) {
                            $coauthors = get_coauthors( $custom_post->ID );
                            $instructors = array();
                            if(isset($coauthors)){
                                foreach($coauthors as $k=>$inst){
                                    $instructor_id = $inst->ID;
                                    $displayname = bp_core_get_user_displayname($instructor_id);
                                    $author_domain = bp_core_get_user_domain( $instructor_id );
                                    $instructor = '<a href="'.$author_domain.'" 
                                        title="'.sprintf(__('Course Author %s','vibe-customtypes'),$displayname).'">'.
                                        bp_core_fetch_avatar(array(
                                                    'item_id' => $instructor_id, 
                                                    'type' => 'thumb', 
                                                    'width' => 64, 
                                                    'height' => 64)).'</a>';
                                    $instructors[] = $instructor;
                                }
                            }
                        }

                        if(!empty($instructors)){
                            $course_instructor = implode(' ', $instructors);
                            $instructor = '<div class="course_instructor">'.$course_instructor.'</div>';

                        }else{
                            $instructor_id = $custom_post->post_author;
                            $author_domain = bp_core_get_user_domain( $instructor_id );
                            $displayname = bp_core_get_user_displayname( $instructor_id );
                            $instructor = '<a href="'.$author_domain.'" class="course_instructor" 
                                        title="'.sprintf(__('Course Author %s','vibe-customtypes'),$displayname).'">'.
                                        bp_core_fetch_avatar(array(
                                                    'item_id' => $instructor_id, 
                                                    'type' => 'thumb', 
                                                    'width' => 64, 
                                                    'height' => 64)).'</a>';
                        }
                        $return .= $instructor;
                    }
                    $return .= '<div class="course_meta">';
                    $reviews = get_post_meta($custom_post->ID,'average_rating',true);
                    $students = get_post_meta($custom_post->ID,'vibe_students',true);
                    $return .='<span class="fa fa-users">'.$students.'</span> ';
                    $return .='<div class="star-rating">';
                    if(function_exists('bp_course_display_rating')){
                       $return .= bp_course_display_rating($reviews);
                    }else{
                        for($i=1;$i<=5;$i++){
                            if($reviews >= 1){
                                $return .= '<span class="fill"></span>';
                            }elseif(($reviews < 1 ) && ($reviews >= 0.4 ) ){
                                $return .= '<span class="half"></span>';
                            }else{
                                $return .= '<span></span>';
                            }
                            $reviews--;
                        }
                    }
                    $return .= '</div></div>';
                    $return .=apply_filters('wplms_course_thumb_extras','');
                    $return .= '</div></div>';

                break; 
            case 'course4':
                    
                    $return .='<div class="block courseitem course4"  data-id="'.$custom_post->ID.'">';
                    $return .='<div class="block_media">';
                    $return .= apply_filters('vibe_thumb_featured_image',featured_component($custom_post->ID,$cols),$featured_style);
                    $return .='</div>';
                    $return .= '<div class="block_content">';
                    $return .= '<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>';

                    $enable_instructor = apply_filters('wplms_display_instructor',true,$custom_post->ID);
                    if($enable_instructor){
                        if ( function_exists('get_coauthors')) {
                            $coauthors = get_coauthors( $custom_post->ID );
                            $instructors = array();
                            if(isset($coauthors)){
                                foreach($coauthors as $k=>$inst){
                                    $instructor_id = $inst->ID;
                                    $displayname = bp_core_get_user_displayname($instructor_id);
                                    $author_domain = bp_core_get_user_domain( $instructor_id );
                                    $instructor = '<a href="'.$author_domain.'" class="course_instructor" 
                                        title="'.__('Course Author','vibe-customtypes').'" style="float:none;padding:2px;">'.$displayname.'</a>';
                                    $instructors[] = $instructor;
                                }
                            }
                        }

                        if(!empty($instructors)){
                            $instructor = implode(',', $instructors);
                        }else{
                            $instructor_id = $custom_post->post_author;
                            $author_domain = bp_core_get_user_domain( $instructor_id );
                            $displayname = bp_core_get_user_displayname( $instructor_id );
                            $instructor = '<a href="'.$author_domain.'" class="course_instructor" 
                                title="'.__('Course Author','vibe-customtypes').'">'.$displayname.'</a>';
                        }
                        $return .= $instructor;
                    }
                    $return .= '<div class="course_block_bottom">';
                    $students = get_post_meta($custom_post->ID,'vibe_students',true);
                    $return .='<span class="fa fa-users">'.$students.'</span> ';
                    $return .= '<div class="course_price">'.bp_course_get_course_credits().'</div>';
                    $return .= '</div>';
                    $return .=apply_filters('wplms_course_thumb_extras','');
                    $return .= '</div></div>';


                break; 
            case 'course5':
                    
                    $return .='<div class="block courseitem course5"  data-id="'.$custom_post->ID.'">';
                    $return .='<div class="block_media">';
                    $return .= apply_filters('vibe_thumb_featured_image',featured_component($custom_post->ID,$cols),$featured_style);
                    $return .='</div>';
                    $return .= '<div class="block_content">';
                    $return .= '<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>';
                    $return .= '<div class="course_block_bottom">';
                    if(function_exists('bp_course_get_start_date')){
                        $date = bp_course_get_start_date($custom_post->ID);
                        $date = str_replace('-','/',$date);
                        $return .='<span class="fa fa-calendar-check-o">'.(date_i18n( get_option( 'date_format' ), strtotime( $date ))).'</span>';
                    }
                    $return .= '<div class="course_price">'.bp_course_get_course_credits().'</div>';
                    
                    $return .= '</div>';
                    $return .=apply_filters('wplms_course_thumb_extras','');
                    $return .= '</div></div>';


                break; 
            case 'course6':
                    
                    $return .='<div class="block courseitem course6"  data-id="'.$custom_post->ID.'">';
                    $return .='<div class="block_media">';
                    $return .= apply_filters('vibe_thumb_featured_image',featured_component($custom_post->ID,$cols),$featured_style);
                    if($custom_post->post_type == 'course'){
                        $cats = wp_get_post_terms( $custom_post->ID,'course-cat'); 
                        if( !empty($cats)){
                            $return .='<div class="postblock_cats">';
                            foreach($cats as $cat){
                                $return .= '<a href="'.get_category_link($cat->term_id ).'" class="postblock_cat">'.$cat->name.'</a>';
                            }
                             $return .='</div>';
                        }
                    }
                    


                    if(function_exists('bp_course_get_start_date') && $custom_post->post_type == 'course'){
                        $date = bp_course_get_start_date($custom_post->ID);

                        if(!empty($date) && strtotime( $date ) > time()){


                            $date = str_replace('-','/',$date);
                            $return .='<span class="course_start_date fa fa-calendar-check-o">'.(date_i18n( get_option( 'date_format' ), strtotime( $date ))).'</span>';
                        }
                    } 

                    $return .='</div>';
                    $instructor='';
                    $enable_instructor = apply_filters('wplms_display_instructor',true,$custom_post->ID);
                    if($enable_instructor){
                        if ( function_exists('get_coauthors')) {
                            $coauthors = get_coauthors( $custom_post->ID );
                            $instructors = array();
                            if(isset($coauthors)){
                                foreach($coauthors as $k=>$inst){
                                    $instructor_id = $inst->ID;
                                    $displayname = bp_core_get_user_displayname($instructor_id);
                                    $author_domain = bp_core_get_user_domain( $instructor_id );
                                    $instructor = '<a href="'.$author_domain.'" class="course_instructor" 
                                        title="'.__('Course Author','vibe-customtypes').'" style="float:none;padding:2px;">'.$displayname.'</a>';
                                    $instructors[] = $instructor;
                                }
                            }
                        }

                        if(!empty($instructors)){
                            $instructor = implode(',', $instructors);
                        }else{
                            $instructor_id = $custom_post->post_author;
                            $author_domain = bp_core_get_user_domain( $instructor_id );
                            $displayname = bp_core_get_user_displayname( $instructor_id );
                            $instructor = '<a href="'.$author_domain.'" class="course_instructor" 
                                title="'.__('Course Author','vibe-customtypes').'">'.$displayname.'</a>';
                        }
                    }

                    $return .= '<div class="block_content">';
                    $return .= '<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>';
                     $return .= $instructor;

                     $return .= apply_filters('vibe_thumb_desc','<p class="block_desc">'.vibe_custom_types_excerpt($n,$custom_post->ID).'</p>',$featured_style);

                    $return .= '<div class="course_block_bottom">';
                    
                    

                    $rating=get_post_meta($custom_post->ID,'average_rating',true);
                    $rating_count=get_post_meta($custom_post->ID,'rating_count',true);



                    $st = get_post_meta($custom_post->ID,'vibe_students',true);
                    if(isset($st) && $st !='')
                         $return .= apply_filters('vibe_thumb_student_count','<strong><i class="fa fa-users"></i> '.$st.'</strong>');



                    $return .= '<div class="star-rating">';
                    if(function_exists('bp_course_display_rating')){
                        $return .= bp_course_display_rating($rating);
                    }
                     $return .=  apply_filters('vibe_thumb_rating',$meta,$featured_style,$rating);
                     $return .= '</div>';
                    

                    $return .= '<div class="course_price">'.bp_course_get_course_credits().'</div>';
                    
                    $return .= '</div>';
                    $return .=apply_filters('wplms_course_thumb_extras','');
                    $return .= '</div></div>';


                break;     
            case 'postblock':
                    $return .='<div class="block '.$custom_post->post_type.'_featured_block postblock">';
                    $return .='<div class="block_media">';
                    if(isset($link) && $link){
                        $return .='<span class="overlay"></span>';
                        $return .= '<a href="'.get_permalink($custom_post->ID).'" class="hover-link hyperlink"><i class="icon-hyperlink"></i></a>';
                    }
                    $return .= apply_filters('vibe_thumb_featured_image',featured_component($custom_post->ID,$cols),$featured_style);
                    
                    switch($custom_post->post_type){
                        case 'course':
                            $cats = wp_get_post_terms( $custom_post->ID,'course-cat'); 
                            if(!empty($cats)){
                                foreach($cats as $cat){
                                    $return .= '<a href="'.get_category_link($cat->term_id ).'" class="postblock_cat">'.$cat->name.'</a>';
                                }
                            }
                        break;
                        case 'unit':
                            $cats = wp_get_post_terms( $custom_post->ID,'module-tag'); 
                            if(!empty($cats)){
                                foreach($cats as $cat){
                                    $return .= '<a href="'.get_category_link($cat->term_id ).'" class="postblock_cat">'.$cat->name.'</a>';
                                }
                            }
                        break;
                        case 'quiz':
                            $cats = wp_get_post_terms( $custom_post->ID,'quiz-type'); 
                            if(!empty($cats)){
                                foreach($cats as $cat){
                                    $return .= '<a href="'.get_category_link($cat->term_id ).'" class="postblock_cat">'.$cat->name.'</a>';
                                }
                            }
                        break;
                        case 'post':
                            $cats = get_the_category(); 
                            if(!empty($cats)){
                                foreach($cats as $cat){
                                    $return .= '<a href="'.get_category_link($cat->term_id ).'" class="postblock_cat">'.$cat->name.'</a>';
                                }
                            }
                        break;
                    }
                    $return .='</div>';
                    
                    
                    $return .='<div class="block_content">';
                    if ( function_exists('get_coauthors')) {
                        $coauthors = get_coauthors( $custom_post->ID );
                        $instructors = array();
                        if(isset($coauthors)){
                            foreach($coauthors as $k=>$inst){
                                $instructor_id = $inst->ID;
                                $displayname = bp_core_get_user_displayname($instructor_id);
                                $author_domain = bp_core_get_user_domain( $instructor_id );
                                $instructor = '<a href="'.$author_domain.'" class="course_instructor" style="float:none;padding:2px;">'.$displayname.'</a>';
                                $instructors[] = $instructor;
                            }
                        }
                    }

                    if(!empty($instructors)){
                        $instructor = implode(',', $instructors);
                    }else{
                        $instructor_id = $custom_post->post_author;
                        $author_domain = bp_core_get_user_domain( $instructor_id );
                        $displayname = bp_core_get_user_displayname( $instructor_id );
                        $instructor = '<a href="'.$author_domain.'" class="course_instructor">'.$displayname.'</a>';
                    }
                    $return .= $instructor;
                    $return .= apply_filters('vibe_thumb_heading','<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>',$featured_style);
                    $return .='</div>';
                    $return .='</div>';
            break;                
           case 'side':
                    $return .='<div class="block side">';
                    $return .='<div class="block_media">';
                    if(isset($link) && $link)
                        $return .='<span class="overlay"></span>';
                    if(isset($link) && $link)
                    $return .= '<a href="'.get_permalink($custom_post->ID).'" class="hover-link hyperlink"><i class="icon-hyperlink"></i></a>';
                    $featured= getPostMeta($custom_post->ID, 'vibe_select_featured');
                   
                    $return .= apply_filters('vibe_thumb_featured_image',featured_component($custom_post->ID,$cols),$featured_style);
                    
                    $category='';
                    if(get_post_type($custom_post->ID) == 'post'){
                        $cats = get_the_category(); 
                        if(is_array($cats)){
                            foreach($cats as $cat){
                            $category .= '<a href="'.get_category_link($cat->term_id ).'">'.$cat->name.'</a> ';
                            }
                        }
                    }
                    
                    $return .='</div>';
                    
                    
                    $return .='<div class="block_content">';
                    $return .= apply_filters('vibe_thumb_heading','<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>',$featured_style);
                    $return .= apply_filters('vibe_thumb_date','<div class="date"><small>'. get_the_time('F d,Y').''.((strlen($category)>2)? ' / '.$category:'').' / '.get_comments_number( '0', '1', '%' ).' '.__(' Comments','vibe-customtypes').'</small></div>',$featured_style);
                    $return .= apply_filters('vibe_thumb_desc','<p class="block_desc">'.vibe_custom_types_excerpt($n,$custom_post->ID).'</p>',$featured_style);
                    $return .='</div>';
                    $return .='</div>';
                break;    
            case 'images_only':
                    $return .='<div class="block">';
                    $return .='<div class="block_media images_only">';
                    
                    if(isset($link) && $link){
                        $return .='<span class="overlay"></span>';
                        $return .= '<a href="'.get_permalink($custom_post->ID).'" class="hover-link hyperlink"><i class="icon-hyperlink"></i></a>';
                    }
                    
                    
                    $return .= apply_filters('vibe_thumb_featured_image',featured_component($custom_post->ID,$cols),$featured_style);
                    $return .='</div>';
                    $return .='</div>';
                break;
            case 'testimonial': 
                    $return .='<div class="block testimonials">';
                
                    $author = getPostMeta($custom_post->ID,'vibe_testimonial_author_name'); 
                    $designation=getPostMeta($custom_post->ID,'vibe_testimonial_author_designation');
                    if(has_post_thumbnail($custom_post->ID)  && $custom_post->post_type == 'testimonials'){
                        $image = apply_filters('vibe_thumb_featured_image',featured_component($custom_post->ID,$cols),$featured_style);
                    }else{
                        $mail = get_userdata($custom_post->post_author);
                        $image= get_avatar( $mail->user_email, 96 );    
                        if(function_exists('vibe_get_option') && empty($image)){
                            $av= vibe_get_option('default_avatar');
                            if(!empty($av)){$image = '<img src="'.$av.'">';}else{$image = '<img src="'.VIBE_URL.'/assets/images/avatar.jpg">';}
                        }

                    }  
                    $return .= '<div class="testimonial_item style2 clearfix">
                                    <div class="testimonial-content">    
                                        <p>'.vibe_custom_types_excerpt($n,$custom_post->ID).(($n < strlen($custom_post->post_content))?$read_more:'').'</p>
                                       <div class="author">
                                          '.$image.(isset($author)?'<h4>'.html_entity_decode($author).'</h4>':'').(isset($designation)?'<small>'.html_entity_decode($designation).'</small>':'').'
                                        </div>     
                                    </div>        
                                    
                                </div>';
                    $return .='</div>';
                break;
             case 'testimonial2': 
                    $return .='<div class="block testimonials2">';
                
                    $author=  getPostMeta($custom_post->ID,'vibe_testimonial_author_name'); 
                    $designation=getPostMeta($custom_post->ID,'vibe_testimonial_author_designation');
                    if(has_post_thumbnail($custom_post->ID) && $custom_post->post_type == 'testimonials'){
                        $image = apply_filters('vibe_thumb_featured_image',featured_component($custom_post->ID,$cols),$featured_style);
                    }else{
                        $mail = get_userdata($custom_post->post_author);
                        $image= get_avatar( $mail->user_email, 96 );    
                        if(function_exists('vibe_get_option') && empty($image)){
                            $av= vibe_get_option('default_avatar');
                            if(!empty($av)){$image = '<img src="'.$av.'">';}else{$image = '<img src="'.VIBE_URL.'/assets/images/avatar.jpg">';}
                        }

                    } 
                    
                    $return .= '<div class="testimonial_item clearfix">
                                    <div class="testimonial-content">    
                                        <h4>'.$custom_post->post_title.'</h4>
                                        <p>'.vibe_custom_types_excerpt($n,$custom_post->ID).(($n < strlen($custom_post->post_content))?$read_more:'').'</p>
                                    </div>        
                                    <div class="author">
                                      '.$image.(!empty($author)?'<h4>'.html_entity_decode($author).'</h4>':'').(!empty($designation)?'<small>'.html_entity_decode($designation).'</small>':'').'
                                    </div>    
                                </div>';
                    $return .='</div>';
                break;   
             case 'blogpost':
                    $return .='<div class="block blogpost">';
                    $return .= '<div class="blog-item">
                                '.apply_filters('vibe_thumb_date','<div class="blog-item-date">
                                    <span class="day">'.get_the_time('d').'</span>
                                    <p class="month">'.get_the_time('M').'\''.get_the_time('y').'</p>
                                </div>',$featured_style).'
                                '.apply_filters('vibe_thumb_heading','<h4><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>',$featured_style).'
                                <p>'.apply_filters('vibe_thumb_desc',vibe_custom_types_excerpt($n,$custom_post->ID),$featured_style).'</p>
                                </div>';
                    $return .='</div>';
                break; 
            case 'event_card':
                $return .= '<div class="event_card">';
                $icon_class=get_post_meta($custom_post->ID,'vibe_icon',true);
                $color=get_post_meta($custom_post->ID,'vibe_color',true);
                $start_date=get_post_meta($custom_post->ID,'vibe_start_date',true);
                $end_date=get_post_meta($custom_post->ID,'vibe_end_date',true);
                $start_time=get_post_meta($custom_post->ID,'vibe_start_time',true);
                $end_time=get_post_meta($custom_post->ID,'vibe_end_time',true);
                $show_location=get_post_meta($custom_post->ID,'vibe_show_location',true);
                $all_day=get_post_meta($custom_post->ID,'vibe_all_day',true);
                $location=vibe_sanitize(get_post_meta($custom_post->ID,'vibe_location',false));
                $repeatable = get_post_meta($custom_post->ID,'vibe_repeatable',true);
                $repeat_value = get_post_meta($custom_post->ID,'vibe_repeat_value',true);
                $repeat_unit = get_post_meta($custom_post->ID,'vibe_repeat_unit',true);
                $repeat_count = get_post_meta($custom_post->ID,'vibe_repeat_count',true);
                $return .= ' <span class="event_icon" style="color:'.$color.'"><i class="'.$icon_class.'"></i></span>
                        <h4 style="background:'.$color.'"><i class="'.$icon_class.'"></i> '.__('Event ','vibe-customtypes').'</label><span><a href="'.get_permalink($custom_post->ID).'">'.get_the_title($custom_post->ID).'</a></span></h4>
                        <ul>
                        ';
                        
                        if(isset($start_date) && $start_date !=''){
                           $return .= '<li><label><i class="icon-calendar"></i> '.__('Start Date ','vibe-customtypes').'</label><span>'.date('F j Y',strtotime($start_date)).'</span></li>';
                        } 
                        if(isset($end_date) && $end_date !=''){
                            $return .= '<li><label><i class="icon-calendar"></i> '.__('End Date ','vibe-customtypes').'</label><span>'.date('F j Y',strtotime($end_date)).'</span>';
                        }
                        if(isset($start_time) && $start_time !=''){
                             
                            $return .= '<li><label><i class="icon-clock"></i> '.__('Start Time ','vibe-customtypes').'</label><span>'.$start_time.'</span>';
                        } 
                        if(isset($end_time) && $end_time !=''){
                            $return .= '<li><label><i class="icon-clock"></i> '.__('End Time ','vibe-customtypes').'</label><span>'.$end_time.'</span>';
                        }
                        if(vibe_validate($all_day)){
                            $return .= '<li><label><i class="icon-circle-full"></i> '.__('All Day ','vibe-customtypes').'</label><span>'.__('Yes','vibe-customtypes').'</span>';
                        }
                        if(vibe_validate($repeatable)){
                            $return .= '<li><label><i class="icon-flash"></i> '.__('Frequency ','vibe-customtypes').'</label><span>'.__('Every ','vibe-customtypes').((isset($repeat_value) && $repeat_value > 1)?$repeat_value:'').' '.$repeat_unit.' '.__('for ','vibe-customtypes').$repeat_count.' '.$repeat_unit.'</span>';
                        }
                        if(vibe_validate($show_location)){
                            $return .= '<li><label><i class="icon-pin-alt"></i> '.__('Venue ','vibe-customtypes').'</label><span>'.(isset($location['staddress'])?$location['staddress']:'').(isset($location['city'])?', '.$location['city']:'').(isset($location['state'])?', '.$location['state']:'').(isset($location['country'])?', '.$location['country']:'').(isset($location['pincode'])?' - '.$location['pincode']:'').'</span>';
                        }
                        $return .= '</ul>
                        <a href="'.get_permalink($custom_post->ID).'" class="event_full_details tip" title="'.__('View full details','vibe-customtypes').'" style="background:'.$color.'"><i class="icon-plus-1"></i></a>
                    </div>';
                
                break;
            case 'generic':
                $return .='<div class="block generic">';
                $return .='<div class="block_media">';
                
                $return .= featured_component($custom_post->ID,$cols);
                
                $category='';
                if($custom_post->post_type == 'post'){
                    $cats = get_the_category(); 
                    if(is_array($cats)){
                        foreach($cats as $cat){
                        $category .= '<a href="'.get_category_link($cat->term_id ).'">'.$cat->name.'</a> ';
                        }
                    }
                }
                
                if($custom_post->post_type == 'product'){
                    $category = get_the_term_list( $custom_post->ID, 'product_cat', '', ' / ' );
                }

                if($custom_post->post_type == 'course'){
                    $category = get_the_term_list( $custom_post->ID, 'course-cat', '', ' / ' );
                }

                if($custom_post->post_type == 'quiz'){
                    $category = get_the_term_list( $custom_post->ID, 'quiz-type', '', ' / ' );
                }

                if($custom_post->post_type == 'assignment'){
                    $category = get_the_term_list( $custom_post->ID, 'assignment-type', '', ' / ' );
                }

                $return .='</div>';
                $return .='<div class="block_content">';

                $return .= apply_filters('vibe_thumb_heading','<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>',$featured_style);

                if($custom_post->post_type == 'product'){
                    if(function_exists('wc_get_product')){
                        $product = wc_get_product( $custom_post->ID );
                        $return .= '<div class="generic_category">'.((strlen($category)>2)? $category:'').'/'.$product->get_price_html().'</div>';
                    }
                }else{
                    $return .= '<div class="generic_category">'.((strlen($category)>2)? $category:'').'</div>';
                }
                $return .='</div>';
                $return .='</div>';
            break;
            case 'simple':
                $return .='<div class="block simple">';
                $return .='<div class="block_media">';
                
                $return .= featured_component($custom_post->ID,$cols);
                
                $category='';
                if($custom_post->post_type == 'post'){
                    $cats = get_the_category(); 
                    if(is_array($cats)){
                        foreach($cats as $cat){
                        $category .= '<a href="'.get_category_link($cat->term_id ).'">'.$cat->name.'</a> ';
                        }
                    }
                }
                
                if($custom_post->post_type == 'product'){
                    $category = get_the_term_list( $custom_post->ID, 'product_cat', '', ' / ' );
                }

                if($custom_post->post_type == 'course'){
                    $category = get_the_term_list( $custom_post->ID, 'course-cat', '', ' / ' );
                }

                if($custom_post->post_type == 'quiz'){
                    $category = get_the_term_list( $custom_post->ID, 'quiz-type', '', ' / ' );
                }

                if($custom_post->post_type == 'assignment'){
                    $category = get_the_term_list( $custom_post->ID, 'assignment-type', '', ' / ' );
                }

                $return .='</div>';
                $return .='<div class="block_content">';

                $return .= apply_filters('vibe_thumb_heading','<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>',$featured_style);

                if($custom_post->post_type == 'product'){
                    if(function_exists('wc_get_product')){
                        $product = wc_get_product( $custom_post->ID );
                        $return .= '<div class="generic_category">'.((strlen($category)>2)? $category:'').'/'.$product->get_price_html().'</div>';
                    }
                }else{
                    $return .= '<div class="simple_category">'.((strlen($category)>2)? $category:'').'</div>';
                }
                $return .='</div>';
                $return .='</div>';
            break;
            case 'general':
                $return .='<div class="block general">';
                $return .='<div class="block_media">';
                
                $return .= featured_component($custom_post->ID,$cols);
                
                $category='';
                if($custom_post->post_type == 'post'){
                    $cats = get_the_category(); 
                    if(is_array($cats)){
                        foreach($cats as $cat){
                        $category .= '<a href="'.get_category_link($cat->term_id ).'">'.$cat->name.'</a> ';
                        }
                    }
                }
                
                if($custom_post->post_type == 'product'){
                    $category = get_the_term_list( $custom_post->ID, 'product_cat', '', ' / ' );
                }

                if($custom_post->post_type == 'course'){
                    $category = get_the_term_list( $custom_post->ID, 'course-cat', '', ' / ' );
                }

                if($custom_post->post_type == 'quiz'){
                    $category = get_the_term_list( $custom_post->ID, 'quiz-type', '', ' / ' );
                }

                if($custom_post->post_type == 'assignment'){
                    $category = get_the_term_list( $custom_post->ID, 'assignment-type', '', ' / ' );
                }

                $return .='</div>';
                $return .='<div class="block_content">';

                if($custom_post->post_type == 'product'){
                    if(function_exists('wc_get_product')){
                        $product = wc_get_product( $custom_post->ID );
                        $return .= '<div class="general_details">'.((strlen($category)>2)? $category:'').'/'.$product->get_price_html().'</div>';
                    }
                }else{
                    $return .= '<div class="general_details">'.((strlen($category)>2)? $category:'').'</div>';
                }
                
                $return .= apply_filters('vibe_thumb_heading','<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>',$featured_style);
                $return .='</div>';
                $return .='</div>';
            break;          
            default:
                   $return .='<div class="block">';
                    $return .='<div class="block_media">';
                    
                    if(isset($link) && $link){
                        $return .='<span class="overlay"></span>';
                        $return .= '<a href="'.get_permalink($custom_post->ID).'" class="hover-link hyperlink"><i class="icon-hyperlink"></i></a>';
                    }
                    
                    $return .= featured_component($custom_post->ID,$cols);
                    
                    $category='';
                    if($custom_post->post_type == 'post'){
                        $cats = get_the_category(); 
                        if(is_array($cats)){
                            foreach($cats as $cat){
                            $category .= '<a href="'.get_category_link($cat->term_id ).'">'.$cat->name.'</a> ';
                            }
                        }
                    }
                    
                    if($custom_post->post_type == 'product'){
                        $category = get_the_term_list( $custom_post->ID, 'product_cat', '', ' / ' );
                    }

                    $return .='</div>';
                    $return .='<div class="block_content">';
                    $return .= apply_filters('vibe_thumb_heading','<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>',$featured_style);

                    if($custom_post->post_type == 'product'){
                        if(function_exists('wc_get_product')){
                            $product = wc_get_product( $custom_post->ID );
                            $return .= '<div class="date"><small>'.((strlen($category)>2)? ' / '.$category:'').'</small></div><div class="price">'.$product->get_price_html().'</div>';
                        }
                    }else{
                        $return .= apply_filters('vibe_thumb_date','<div class="date"><small>'. get_the_time('F d,Y').''.((strlen($category)>2)? ' / '.$category:'').' / '.get_comments_number( '0', '1', '%' ).' '.__(' Comments','vibe-customtypes').'</small></div>',$featured_style);
                    }
                    if($custom_post->post_type == 'product'){
                        if(function_exists('woocommerce_template_loop_add_to_cart')){
                            ob_start();
                            woocommerce_template_loop_add_to_cart( $custom_post, $product );
                            $return.= ob_get_clean();
                        }
                    }else{
                        $return .= apply_filters('vibe_thumb_desc','<p class="block_desc">'.vibe_custom_types_excerpt($n,$custom_post->ID).'</p>',$featured_style);    
                    }
                    
                    $return .='</div>';
                    $return .='</div>';

                break;
            }
            if($cache_duration)
            wp_cache_set( $course_key,$result,'featured_block',$cache_duration);
        }//end If
        return apply_filters('vibe_featured_thumbnail_style',$return,$custom_post,$featured_style);
}

//*=== Featured Component ===*//

function featured_component($custom_post_id,$cols='',$style=''){
    $custom_post_thumbnail = '';
    $default_image = vibe_get_option('default_course_avatar');
    if(!in_array($cols,array('big','small','medium','mini','full'))){
        switch($cols){
          case '2':{ $cols = 'big';
          break;}
          case '3':{ $cols = 'medium';
          break;}
          case '4':{ $cols = 'medium';
          break;}
          case '5':{ $cols = 'small';
          break;}
          case '6':{ $cols = 'small';
          break;}  
          default:{ $cols = 'full';
          break;}
        }
    }
    
    if(has_post_thumbnail($custom_post_id)){
        $custom_post_thumbnail=  '<a href="'.get_permalink().'">'.get_the_post_thumbnail($custom_post_id,$cols).'</a>';
    }else if(isset($default_image) && $default_image)
            $custom_post_thumbnail='<img src="'.$default_image.'" />';
                    
    return apply_filters('wplms_featured_component_filter',$custom_post_thumbnail,$custom_post_id,$cols,$style);   
    
}        

if(!function_exists('vibe_member_block')){
    function vibe_member_block($user,$style,$field_names = null,$width='150',$link=0){
        $member_html = '';
        if(function_exists('bp_core_fetch_avatar')){
            $avatar = bp_core_fetch_avatar(array('item_id' => $user->ID, 'type' => 'full', 'width' => $width, 'height' => $width));
        }
        if(empty($avatar)){
            $avatar = '<img src="">';
        }
        switch($style){
            case 'member2':
                $link = bp_core_get_user_domain($user->ID);
               $member_html .='<div class="member_block">';
               $member_html .='<div class="member_avatar">'.(empty($link)?'':'<a href="'.$link.'">').$avatar.(empty($link)?'':'</a>').'</div>';
               $member_html .='<div class="member_info">';
               $member_html .='<h3><a href="'.$link.'">'.$user->display_name.'</a></h3>';
               if(!empty($field_names)){
                    foreach($field_names as $field){
                        $member_html .= '<span>'.bp_get_profile_field_data('field='.$field.'&user_id='.$user->ID).'</span>';
                    }
               }
               if(function_exists('vibe_get_option') && function_exists('bp_xprofile_get_groups')){
                    $social_field_group = vibe_get_option('social_field_group');

                    $bp_xprofile_groups = $groups = bp_xprofile_get_groups( array( 'fetch_fields' => true ) );
                    $member_html .= '<ul class="socialicons">';
                    if(!empty($bp_xprofile_groups)){
                        foreach($bp_xprofile_groups as $g){
                            if($g->name == $social_field_group){
                                if ( !empty( $g->fields ) ) {
                                    foreach ( $g->fields as $field ) {
                                        $id = $field->id;
                                        $url = strip_tags(bp_get_profile_field_data( array('user_id'=> $user->ID,'field'  => $field->name) ));
                                        if(!empty($url) && strlen($url)>2){
                                            $n = strtolower($field->name);
                                            if(strpos($url,'http://') === false){$url = 'http://'.$url;}
                                            $member_html .= '<li><a href="'.$url.'" class="'.$n.'"><i class="fa fa-'.$n.'"></i></a></li>';
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $member_html .= '</ul>';
               }
               $member_html .='</div>';
               $member_html .='</div>';
            break;
            case 'member3':
                $link = bp_core_get_user_domain($user->ID);
                $member_html .='<div class="member_block3">';
                $member_html .='<div class="member_avatar">'.(empty($link)?'':'<a href="'.$link.'">').$avatar.(empty($link)?'':'</a>');
                if(function_exists('vibe_get_option') && function_exists('bp_xprofile_get_groups')){
                    $social_field_group = vibe_get_option('social_field_group');

                    $bp_xprofile_groups = $groups = bp_xprofile_get_groups( array( 'fetch_fields' => true ) );
                    $member_html .= '<ul class="socialicons">';
                    if(!empty($bp_xprofile_groups)){
                        foreach($bp_xprofile_groups as $g){
                            if($g->name == $social_field_group){
                                if ( !empty( $g->fields ) ) {
                                    foreach ( $g->fields as $field ) {
                                        $id = $field->id;
                                        $url = strip_tags(bp_get_profile_field_data( array('user_id'=> $user->ID,'field'  => $field->name) ));
                                        if(!empty($url) && strlen($url)>2){
                                            $n = strtolower($field->name);
                                            if(strpos($url,'http://') === false){$url = 'http://'.$url;}
                                            $member_html .= '<li><a href="'.$url.'" class="'.$n.'"><i class="fa fa-'.$n.'"></i></a></li>';
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $member_html .= '</ul>';
               }
               $member_html .='</div>';
                $member_html .='<div class="member_info">';
                $member_html .='<h3><a href="'.$link.'">'.$user->display_name.'</a></h3>';
                if(!empty($field_names)){
                    foreach($field_names as $field){
                        $member_html .= '<span>'.bp_get_profile_field_data('field='.$field.'&user_id='.$user->ID).'</span>';
                    }
                }
               
               $member_html .='</div>';
               $member_html .='</div>';
            break;
            case 'member4':
                $link = bp_core_get_user_domain($user->ID);
                $member_html .='<div class="member_block4">';
                $member_html .='<div class="member_avatar">'.(empty($link)?'':'<a href="'.$link.'">').$avatar.(empty($link)?'':'</a>');
               $member_html .='</div>';
                $member_html .='<div class="member_info">';
                $member_html .='<h3><a href="'.$link.'">'.$user->display_name.'</a></h3>';
                if(!empty($field_names)){
                    foreach($field_names as $field){
                        $member_html .= '<span>'.bp_get_profile_field_data('field='.$field.'&user_id='.$user->ID).'</span>';
                    }
                }
               
               if(function_exists('vibe_get_option') && function_exists('bp_xprofile_get_groups')){
                    $social_field_group = vibe_get_option('social_field_group');

                    $bp_xprofile_groups = $groups = bp_xprofile_get_groups( array( 'fetch_fields' => true ) );
                    $member_html .= '<ul class="socialicons">';
                    if(!empty($bp_xprofile_groups)){
                        foreach($bp_xprofile_groups as $g){
                            if($g->name == $social_field_group){
                                if ( !empty( $g->fields ) ) {
                                    foreach ( $g->fields as $field ) {
                                        $id = $field->id;
                                        $url = strip_tags(bp_get_profile_field_data( array('user_id'=> $user->ID,'field'  => $field->name) ));
                                        if(!empty($url) && strlen($url)>2){
                                            $n = strtolower($field->name);
                                            if(strpos($url,'http://') === false){$url = 'http://'.$url;}
                                            $member_html .= '<li><a href="'.$url.'" class="'.$n.'"><i class="fa fa-'.$n.'"></i></a></li>';
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $member_html .= '</ul>';
               }
               
               $member_html .='</div>';
               $member_html .='</div>';
            break;
            default:
                $link = bp_core_get_user_domain($user->ID);
               $member_html .='<div class="member_block">';
               $member_html .='<div class="member_avatar">'.(empty($link)?'':'<a href="'.$link.'">').$avatar.(empty($link)?'':'</a>').'</div>';
               $member_html .='<div class="member_info">';
               $member_html .='<h3><a href="'.$link.'">'.$user->display_name.'</a></h3>';
               if(!empty($field_names)){
                    foreach($field_names as $field){
                        $member_html .= '<span>'.bp_get_profile_field_data('field='.$field.'&user_id='.$user->ID).'</span>';
                    }
               }
               if(function_exists('vibe_get_option') && function_exists('bp_xprofile_get_groups')){
                    $social_field_group = vibe_get_option('social_field_group');

                    $bp_xprofile_groups = $groups = bp_xprofile_get_groups( array( 'fetch_fields' => true ) );
                    $member_html .= '<ul class="socialicons">';
                    if(!empty($bp_xprofile_groups)){
                        foreach($bp_xprofile_groups as $g){
                            if($g->name == $social_field_group){
                                if ( !empty( $g->fields ) ) {
                                    foreach ( $g->fields as $field ) {
                                        $id = $field->id;
                                        $url = strip_tags(bp_get_profile_field_data( array('user_id'=> $user->ID,'field'  => $field->name) ));
                                        if(!empty($url) && strlen($url)>2){
                                            $n = strtolower($field->name);
                                            if(strpos($url,'http://') === false){$url = 'http://'.$url;}
                                            $member_html .= '<li><a href="'.$url.'" class="'.$n.'"><i class="fa fa-'.$n.'"></i></a></li>';
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $member_html .= '</ul>';
               }
               $member_html .='</div>';
               $member_html .='</div>';
            break;
        }

        return $member_html;
    }
}

if(!function_exists('vibe_custom_types_excerpt')){

    function vibe_custom_types_excerpt($chars=0, $id = NULL) {
        global $post;
      if(!isset($id)) $id=$post->ID;
        $text = get_post($id);
            
        if(strlen($text->post_excerpt) > 10)
                $text = $text->post_excerpt . " ";
            else
                $text = $text->post_content . " ";
            
        $text = strip_tags($text);
            $ellipsis = false;
            $text = strip_shortcodes($text);
        if( strlen($text) > $chars )
            $ellipsis = true;
      

        $text = substr($text,0,intval($chars));
        
        if(function_exists('mb_convert_encoding'))
            $text = mb_convert_encoding((string)$text, 'UTF-8', mb_list_encodings());   

        $latin=preg_match("/\p{Han}+/u", $text);
        if($latin !=1)
        $text = substr($text,0,strrpos($text,' '));

        if( $ellipsis == true && $chars > 1)
            $text = $text . "...";
            
        return $text;
    }

}