/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function(){
   
jQuery('.font_preview').each(function(){
   var $this=jQuery(this); 
   var id=$this.attr('data-ref');
   var family=jQuery('#'+id).val();
   var size=12;
   var sizeid='#carouselfontsize';
   switch(id){
       case 'carouselfontstyle':{ sizeid='#carouselfontsize';
               break;
       }
       case 'h1fontstyle':{ sizeid='#h1fontsize';
               break;
       }
        case 'h2fontstyle':{ sizeid='#h2fontsize';
               break;
       }
        case 'h3fontstyle':{ sizeid='#h3fontsize';
               break;
       }
        case 'h4fontstyle':{ sizeid='#h4fontsize';
               break;
       }
        case 'h5fontstyle':{ sizeid='#h5fontsize';
               break;
       } case 'h6fontstyle':{ sizeid='#h6fontsize';
               break;
       }
        case 'pfontstyle':{ sizeid='#pfontsize';
               break;
       }
        case 'labelfontstyle':{ sizeid='#labelfontsize';
               break;
       } 
   }
   
   size=jQuery(sizeid).val();
   jQuery(this).find('h1').css({'font-family': family,"font-size": size+"px"});
   
   jQuery('#'+id).change(function(){
       var family=jQuery('#'+id).val();
       console.log('change'+family);
       jQuery(this).find('h1').css({'font-family': family,'font-size':size});
      
      var found = jQuery.inArray(family, WebFontConfig.google);
            if (found >= 0) {
            } else {
                        WebFontConfig.google.families.push(family);
                    }
    }); 
    
    
    jQuery(sizeid).parent().find('.jqslider').blur(function(){
        size=jQuery(sizeid).val();
        $this.find('h1').css({'font-size':size});
        console.log(size);
    });
    
   });
 });   