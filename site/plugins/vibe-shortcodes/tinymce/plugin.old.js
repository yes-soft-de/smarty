(function ()
{
	// create vibeShortcodes plugin
	tinymce.create("tinymce.plugins.vibeShortcodes",
	{
		init: function ( ed, url )
		{
			ed.addCommand("vibePopup", function ( a, params )
			{
				var popup = params.identifier;
				
				// load thickbox
				tb_show("Insert Shortcode", url + "/popup.php?popup=" + popup + "&width=" + 800);
			});
		},
		createControl: function ( btn, e )
		{
			if ( btn == "vibe_button" )
			{	
				var a = this;
				
				var btn = e.createSplitButton('vibe_button', {
                                        title: "Insert Shortcode",
					image: VibeShortcodes.shortcodes_folder +"/tinymce/images/icon.png",
					icons: false
                });

                btn.onRenderMenu.add(function (c, b) 
				{	
                    a.addWithPopup( b, "Accordion", "accordion" );	
					a.addWithPopup( b, "Buttons", "button" );
                    a.addWithPopup( b, "Columns", "columns" );
                    a.addWithPopup( b, "Counter", "counter" );
                    a.addWithPopup( b, "Countdown", "countdown" );
                    a.addWithPopup( b, "Course", "course" );
                    a.addWithPopup( b, "Divider", "divider" );
					a.addWithPopup( b, "Forms", "forms" );
					a.addWithPopup( b, "Gallery", "gallery" );
                    a.addWithPopup( b, "Google Maps", "maps" );
					a.addWithPopup( b, "Heading", "heading" );
					a.addWithPopup( b, "Icons", "icons" );
                    a.addWithPopup( b, "Note", "note" );
                    a.addWithPopup( b, "Popups", "popups" );
                    a.addWithPopup( b, "Progress Bar", "progressbar" );
                    a.addWithPopup( b, "PullQuote", "pullquote" );
                    a.addWithPopup( b, "Round Progress", "roundprogress" );
                    a.addWithPopup( b, "Survey", "survey_result" );
					a.addWithPopup( b, "Tabs", "tabs" );
                    a.addWithPopup( b, "Team", "team_member" );
					a.addWithPopup( b, "Testimonial", "testimonial" );
                    a.addWithPopup( b, "Tooltips", "tooltip" );
                    a.addWithPopup( b, "Video", "iframevideo" );
				});
                
                return btn;
			}
			
			return null;
		},
		addWithPopup: function ( ed, title, id ) {
			ed.add({
				title: title,
				onclick: function () {
					tinyMCE.activeEditor.execCommand("vibePopup", false, {
						title: title,
						identifier: id
					})
				}
			})
		},
		addImmediate: function ( ed, title, sc) {
			ed.add({
				title: title,
				onclick: function () {
					tinyMCE.activeEditor.execCommand( "mceInsertContent", false, sc );
				}
			})
		},
		getInfo: function () {
			return {
				longname: 'Vibe Shortcodes'
			}
		}
	});
	
	// add vibeShortcodes plugin
	tinymce.PluginManager.add("vibeShortcodes", tinymce.plugins.vibeShortcodes);
})();