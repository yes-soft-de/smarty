(function($) {
"use strict";   
 


 			//Shortcodes
           tinymce.PluginManager.add( 'vibeShortcodes', function( editor, url ) {

				editor.addCommand("vibePopup", function ( a, params )
				{
					var popup = params.identifier;
					tb_show("Insert Shortcode", url + "/popup.php?popup=" + popup + "&width=" + 800);
				});
     
                editor.addButton( 'vibe_button', {
                    type: 'splitbutton',
                    icon: 'icon vibe-icon',
					title:  'Vibe Shortcodes',
					onclick : function(e) {},
					menu: [
					{text: 'Accordion',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Accordion',identifier: 'accordion'})
					}},
					{text: 'Buttons',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Buttons',identifier: 'button'})
					}},
					{text: 'Columns',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Columns',identifier: 'columns'})
					}},
					{text: 'Counter',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Counter',identifier: 'counter'})
					}},
					{text: 'Countdown',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Countdown',identifier: 'countdown'})
					}},
					{text: 'Course',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Course',identifier: 'course'})
					}},
					{text: 'Divider',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Divider',identifier: 'divider'})
					}},
					{text: 'Forms',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Forms',identifier: 'forms'})
					}},
					{text: 'Gallery',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Gallery',identifier: 'gallery'})
					}},
					{text: 'Google Maps',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Google Maps',identifier: 'maps'})
					}},
					{text: 'Heading',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Heading',identifier: 'heading'})
					}},
					{text: 'Icons',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Icons',identifier: 'icons'})
					}},
					{text: 'Iframe',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Iframe',identifier: 'iframe'})
					}},
					{text: 'Note',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Note',identifier: 'note'})
					}},
					{text: 'Popups',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Popups',identifier: 'popups'})
					}},
					{text: 'Progress Bar',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Progress Bar',identifier: 'progressbar'})
					}},
					{text: 'PullQuote',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'PullQuote',identifier: 'pullquote'})
					}},
					{text: 'Round Progress',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Round Progress',identifier: 'roundprogress'})
					}},
					{text: 'Survey Result',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Survery Result',identifier: 'survey_result'})
					}},
					{text: 'Tabs',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Tabs',identifier: 'tabs'})
					}},
					{text: 'Team',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Team',identifier: 'team_member'})
					}},
					{text: 'Testimonial',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Testimonial',identifier: 'testimonial'})
					}},
					{text: 'Tooltips',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Tooltips',identifier: 'tooltip'})
					}},
					{text: 'Video',onclick:function(){
						editor.execCommand("vibePopup", false, {title: 'Video',identifier: 'iframevideo'})
					}},
					]                
        	  });
         
          });
         
 
})(jQuery);
