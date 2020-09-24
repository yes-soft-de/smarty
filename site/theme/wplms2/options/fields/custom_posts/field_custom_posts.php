<?php
class VIBE_Options_custom_posts extends VIBE_Options{	
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since VIBE_Options 1.0.5
	*/
	function __construct($field = array(), $value ='', $parent){
		
		parent::__construct($parent->sections, $parent->args, $parent->extra_tabs);
		$this->field = $field;
		$this->value = $value;
		//$this->render();
		
	}//function
	
	
	
	/**
	 * Field Render Function.
	 *
	 * Takes the vars and outputs the HTML for the field in the settings
	 *
	 * @since VIBE_Options 1.0.5
	*/
	function render(){
		
		$class = (isset($this->field['class']))?$this->field['class']:'regular-text';
		
		echo '<ul id="'.$this->field['id'].'-ul">';
		
		//if(isset($this->value) && is_array($this->value)){
                    $n=0;
                    if(isset($this->value['post-type-name'])){
                    $n= count($this->value['post-type-name']);
                    }
                    if($n == 0){
                        echo "<h4 id='intro'>Click on Add New to start adding new Custom Post Types !</h4>";
                    }else{
			for($i=0;$i<$n;$i++){
			//&& ($this->value['post-type-name'][$i]!='')
                            if(isset($this->value['post-type-name'][$i]) ){
                       echo '<li class="vibe-label vibe-new"><h2>Custom Post Type : '.$this->value['post-type-name'][$i].' &rsaquo;</h2><a href="javascript:void(0);" class="vibe-opts-custom-post-remove">'.__('Remove', 'vibe').'</a>
                              <ul class="vibe-custom" style="display:none">
                                <li><label>Post Type Name</label> <input type="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][post-type-name][]"  value="'.$this->value['post-type-name'][$i].'" class="'.$class.'" /><a href="#" title="The post type name.  Used to retrieve custom post type content.  Should be short and sweet" style="cursor: help;">?</a></li>
                                <li><label>Label </label><input type="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label][]"  value="'.$this->value['label'][$i].'" class="'.$class.'" /></li>
                                <li><label>Singular Label</label><input type="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][singular-label][]"  value="'.$this->value['singular-label'][$i].'" class="'.$class.'" /></li>
                                <li><label>Description</label><textarea class="'.$class.'" name="'.$this->args['opt_name'].'['.$this->field['id'].'][description][]"  >'.$this->value['description'][$i].'</textarea></li>
                                <li class="vibe-drop"><h4>Advanced Options &rsaquo;</h4>
                                    <ul>
                                        <li><label>Public</label> <input type="checkbox" class="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][public][]" value="1" '.((isset($this->value['public'][$i]))? ' checked="checked"' : '').' /></li>
                                        <li><label>Show UI</label> <input type="checkbox" class="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][showui][]" value="1" '.((isset($this->value['showui'][$i]))? ' checked="checked"' : '').' /></li>
                                        <li><label>Has Archive</label> <input type="checkbox" class="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][hasarchive][]" value="1" '.((isset($this->value['hasarchive'][$i]))? ' checked="checked"' : '').'/></li>
                                        <li><label>Exclude from Search</label> <input type="checkbox" class="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][excludesearch][]" value="1" '.((isset($this->value['excludesearch'][$i]))? ' checked="checked"' : '').' /></li>
                                        <li><label>Hierarchial</label> <input type="checkbox" class="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][hierarchial][]" value="1" '.((isset($this->value['hierarchial'][$i]))? ' checked="checked"' : '').' /></li>
                                        <li><label>Rewrite</label> <input type="checkbox" class="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][rewrite][]" value="1" '.((isset($this->value['rewrite'][$i]))? ' checked="checked"' : '').' /></li>
                                        <li><label>Publicly Query Var</label> <input type="checkbox" class="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][queryvar][]" value="1" '.((isset($this->value['queryvar'][$i]))? ' checked="checked"' : '').' /></li>
                                        <li><label>Capability Type</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][capability][]" value="post" /><a href="#" title="The post type to use for checking read, edit, and delete capabilities" style="cursor: help;">?</a></li>    
                                        <li><label>Custom Rewrite Slug</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][rewriteslug][]" value="'.$this->value['rewriteslug'][$i].'" /><a href="#" title="Custom Post Type rewrite slug" style="cursor: help;">?</a></li>        
                                        <li><label>Show in Menu</label> <input type="checkbox" class="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][showmenu][]" value="1" '.((isset($this->value['showmenu'][$i]))? ' checked="checked"' : '').' /></li>
                                        <li><label>Show in Nav Menu</label> <input type="checkbox" class="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][shownavmenu][]" value="1" '.((isset($this->value['shownavmenu'][$i]))? ' checked="checked"' : '').' /></li>
                                        <li><label>Show in Admin bar Menu</label> <input type="checkbox" class="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][showadminmenu][]" value="1" '.((isset($this->value['showadminmenu'][$i]))? ' checked="checked"' : '').' /></li>  
                                        <li><label>Menu Position</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][menuposition][]" value="'.$this->value['menuposition'][$i].'" /></li>    
                                        <li><label><h5>Supports</h5></label> </li>
                                        <li><label>Title</label><input type="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-title][]" tabindex="11" value="1" '.((isset($this->value['supports-title'][$i]))? ' checked="checked"' : '').'><a href="#" title="Adds the title meta box when creating content for this custom post type" style="cursor: help;">?</a></li>
                                        <li><label>Editor </label> <input type="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-editor][]" tabindex="12" value="1" '.((isset($this->value['supports-editor'][$i]))? ' checked="checked"' : '').'">&nbsp;<a href="#" title="Adds the content editor meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Excerpt </label><input type="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-excerpt][]" tabindex="13" value="1" '.((isset($this->value['supports-excerpt'][$i]))? ' checked="checked"' : '').'>&nbsp;<a href="#" title="Adds the excerpt meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Trackbacks</label><input type="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-trackbacks][]" tabindex="14" value="1" '.((isset($this->value['supports-trackbacks'][$i]))? ' checked="checked"' : '').'>&nbsp;<a href="#" title="Adds the trackbacks meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Custom Fields</label><input type="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-customfields][]" tabindex="15" value="1" '.((isset($this->value['supports-customfields'][$i]))? ' checked="checked"' : '').'>&nbsp;<a href="#" title="Adds the custom fields meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Comments</label><input type="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-comments][]" tabindex="16" value="1" '.((isset($this->value['supports-comments'][$i]))? ' checked="checked"' : '').'>&nbsp;<a href="#" title="Adds the comments meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Revision</label><input type="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-revisions][]" tabindex="17" value="1" '.((isset($this->value['supports-revisions'][$i]))? ' checked="checked"' : '').'>&nbsp;<a href="#" title="Adds the revisions meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Featured Image</label><input type="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-thumbnail][]" tabindex="18" value="1" '.((isset($this->value['supports-thumbnail'][$i]))? ' checked="checked"' : '').'>&nbsp;<a href="#" title="Adds the featured image meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Author</label><input type="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-author][]" tabindex="19" value="1" '.((isset($this->value['supprots-author'][$i]))? ' checked="checked"' : '').'>&nbsp;<a href="#" title="Adds the author meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Page Attributes</label><input type="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-pageattributes][]" tabindex="20" value="1" '.((isset($this->value['supports-pageattributes'][$i]))? ' checked="checked"' : '').'>&nbsp; <a href="#" title="Adds the page attribute meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Post Formats</label><input type="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-postformats][]" tabindex="20" value="1" '.((isset($this->value['supports-postformats'][$i]))? ' checked="checked"' : '').'>&nbsp;  </li>
                                        <li><label><h5>Built-in Taxonomies</h5></label></li> 
                                        ';
                                        $taxonomies=get_taxonomies('','names'); 
                                        foreach ($taxonomies as $taxonomy ) {
                                        echo '<li><label>'.$taxonomy.'</label><input type="checkbox" name="'.$this->args['opt_name'].'['.$this->field['id'].'][taxonomy]['.$taxonomy.'][]" tabindex="20" value="1" '.((isset($this->value['taxonomy'][$taxonomy][$i]))? ' checked="checked"' : '').'>';
                                        }
                                echo '
                                </ul>
                                </li>
                                <li class="vibe-drop"><h4>Advanced Label Options &rsaquo;</h4>
                                    <ul>
                                        <li><label>Menu Name</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-menu-name][]" value="'.$this->value['label-menu-name'][$i].'"/><a href="#" title="Custom menu name for your custom post type.eg: My Movies" style="cursor: help;">?</a></li>
                                        <li><label>Add New</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-add-new][]" value="'.$this->value['label-add-new'][$i].'"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: Add New" style="cursor: help;">?</a></li>
                                        <li><label>Add New Item</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-add-new-item][]" value="'.$this->value['label-add-new-item'][$i].'"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types. eg: Add New Movie" style="cursor: help;">?</a></li>
                                        <li><label>Edit</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-edit][]" value="'.$this->value['label-edit'][$i].'"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: Edit" style="cursor: help;">?</a></li>
                                        <li><label>Edit Item</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-edit-item][]" value="'.$this->value['label-edit-item'][$i].'"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g:Edit Movie" style="cursor: help;">?</a></li>
                                        <li><label>New Item</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-new-item][]" value="'.$this->value['label-new-item'][$i].'"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: New Movie" style="cursor: help;">?</a></li>
                                        <li><label>View </label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-view][]" value="'.$this->value['label-view'][$i].'"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g:View Movie" style="cursor: help;">?</a></li>  
                                        <li><label>View Item</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-view-item][]" value="'.$this->value['label-view-item'][$i].'"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: View Movie" style="cursor: help;">?</a></li>  
                                        <li><label>Search Item</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-search-item][]" value="'.$this->value['label-search-item'][$i].'"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: Search Movies" style="cursor: help;">?</a></li>  
                                        <li><label>Not Found</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-not-found][]" value="'.$this->value['label-not-found'][$i].'"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: No Movies Found" style="cursor: help;">?</a></li>  
                                        <li><label>Bot Found in Trash</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-not-found-trash][]" value="'.$this->value['label-not-found-trash'][$i].'"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: No Movies Found in Trash" style="cursor: help;">?</a></li>  
                                        <li><label>Parent</label> <input type="text" class="text" name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-parent][]" value="'.$this->value['label-parent'][$i].'"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: Parent Movie" style="cursor: help;">?</a></li>      
                                        
                                </ul>
                                </li>
                                </li>
                            </ul>
                            
                            </li>';
				
                            }
			}//foreach
                    }//End else
                //}
		
		echo '<li id="new_custom_post" class="vibe-label" style="display:none;"><h2>New Custom Post Type </h2><a href="javascript:void(0);" class="vibe-opts-custom-post-remove">'.__('Remove', 'vibe').'</a>
                              <ul class="vibe-custom">
                                <li><label>Post Type Name</label> <input type="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][post-type-name][]"  value="" class="'.$class.'" /><a href="#" title="The post type name.  Used to retrieve custom post type content.  Should be short and sweet" style="cursor: help;">?</a></li>
                                <li><label>Label </label><input type="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label][]"  value="" class="'.$class.'" /></li>
                                <li><label>Singular Label</label><input type="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][singular-label][]"  value="" class="'.$class.'" /></li>
                                <li><label>Description</label><textarea class="'.$class.'" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][description][]"  ></textarea></li>
                                <li class="vibe-drop"><h4>Advanced Options &rsaquo;</h4>
                                    <ul>
                                        <li><label>Public</label> <input type="checkbox" class="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][public][]" value="1" checked /></li>
                                        <li><label>Show UI</label> <input type="checkbox" class="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][showui][]" value="1" checked /></li>
                                        <li><label>Has Archive</label> <input type="checkbox" class="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][hasarchive][]" value="1" /></li>
                                        <li><label>Exclude from Search</label> <input type="checkbox" class="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][excludesearch][]" value="1" /></li>
                                        <li><label>Hierarchial</label> <input type="checkbox" class="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][hierarchial][]" value="1" /></li>
                                        <li><label>Rewrite</label> <input type="checkbox" class="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][rewrite][]" value="1" checked /></li>
                                        <li><label>Publicly Query Var</label> <input type="checkbox" class="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][queryvar][]" value="1" checked /></li>
                                        <li><label>Capability Type</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][capability][]" value="post" /><a href="#" title="The post type to use for checking read, edit, and delete capabilities" style="cursor: help;">?</a></li>    
                                        <li><label>Custom Rewrite Slug</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][rewriteslug][]" value="" /><a href="#" title="Custom Post Type rewrite slug" style="cursor: help;">?</a></li>        
                                        <li><label>Show in Menu</label> <input type="checkbox" class="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][showmenu][]" value="1" checked /></li>
                                        <li><label>Show in Nav Menu</label> <input type="checkbox" class="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][shownavmenu][]" value="1" checked /></li>
                                        <li><label>Show in Admin bar Menu</label> <input type="checkbox" class="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][showadminmenu][]" value="1" checked /></li>    
                                        <li><label>Menu Position</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][menuposition][]" value="" /></li>    
                                        <li><label><h5>Supports</h5></label> </li>
                                        <li><label>Title</label><input type="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-title][]" tabindex="11" value="1" checked="checked"><a href="#" title="Adds the title meta box when creating content for this custom post type" style="cursor: help;">?</a></li>
                                        <li><label>Editor </label> <input type="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-editor][]" tabindex="12" value="1" checked="checked">&nbsp;<a href="#" title="Adds the content editor meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Excerpt </label><input type="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-excerpt][]" tabindex="13" value="1" checked="checked">&nbsp;<a href="#" title="Adds the excerpt meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Trackbacks</label><input type="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-trackbacks][]" tabindex="14" value="1" checked="checked">&nbsp;<a href="#" title="Adds the trackbacks meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Custom Fields</label><input type="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-customfields][]" tabindex="15" value="1" checked="checked">&nbsp;<a href="#" title="Adds the custom fields meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Comments</label><input type="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-comments][]" tabindex="16" value="1" checked="checked">&nbsp;<a href="#" title="Adds the comments meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Revision</label><input type="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-revisions][]" tabindex="17" value="1" checked="checked">&nbsp;<a href="#" title="Adds the revisions meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Featured Image</label><input type="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-thumbnail][]" tabindex="18" value="1" checked="checked">&nbsp;<a href="#" title="Adds the featured image meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Author</label><input type="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-author][]" tabindex="19" value="1" checked="checked">&nbsp;<a href="#" title="Adds the author meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Page Attributes</label><input type="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-pageattributes][]" tabindex="20" value="1" checked="checked">&nbsp; <a href="#" title="Adds the page attribute meta box when creating content for this custom post type" style="cursor: help;">?</a> </li>
                                        <li><label>Post Formats</label><input type="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][supports-postformats][]" tabindex="20" value="1" checked="checked">&nbsp;</li>
                                        <li><label><h5>Built-in Taxonomies</h5></label></li> 
                                        ';
                                        $taxonomies=get_taxonomies('','names'); 
                                        foreach ($taxonomies as $taxonomy ) {
                                        echo '<li><label>'.$taxonomy.'</label><input type="checkbox" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][taxonomy]['.$taxonomy.'][]" tabindex="20" value="1">';
                                        }
                                echo '
                                </ul>
                                </li>
                                <li class="vibe-drop"><h4>Advanced Label Options &rsaquo;</h4>
                                    <ul>
                                        <li><label>Menu Name</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-menu-name][]" value=""/><a href="#" title="Custom menu name for your custom post type.eg: My Movies" style="cursor: help;">?</a></li>
                                        <li><label>Add New</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-add-new][]" value="Add New"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: Add New" style="cursor: help;">?</a></li>
                                        <li><label>Add New Item</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-add-new-item][]" value=" New"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types. eg: Add New Movie" style="cursor: help;">?</a></li>
                                        <li><label>Edit</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-edit][]" value="Edit"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: Edit" style="cursor: help;">?</a></li>
                                        <li><label>Edit Item</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-edit-item][]" value="Edit"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g:Edit Movie" style="cursor: help;">?</a></li>
                                        <li><label>New Item</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-new-item][]" value="New"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: New Movie" style="cursor: help;">?</a></li>
                                        <li><label>View </label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-view][]" value="View"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g:View Movie" style="cursor: help;">?</a></li>  
                                        <li><label>View Item</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-view-item][]" value="View item"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: View Movie" style="cursor: help;">?</a></li>  
                                        <li><label>Search Item</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-search-item][]" value="Search"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: Search Movies" style="cursor: help;">?</a></li>  
                                        <li><label>Not Found</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-not-found][]" value="Not Found"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: No Movies Found" style="cursor: help;">?</a></li>  
                                        <li><label>Bot Found in Trash</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-not-found-trash][]" value="Nout found in trash"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: No Movies Found in Trash" style="cursor: help;">?</a></li>  
                                        <li><label>Parent</label> <input type="text" class="text" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][label-parent][]" value="Parent"/><a href="#" title="Post type label.  Used in the admin menu for displaying post types.e.g: Parent Movie" style="cursor: help;">?</a></li>      
                                        
                                </ul>
                                </li>
                                </li>
                            </ul>
                            
                            </li>';
		
		echo '</ul>';
		
		echo '<a href="javascript:void(0);" class="vibe-btn green vibe-opts-custom-post-add " rel-id="'.$this->field['id'].'-ul" rel-name="'.$this->args['opt_name'].'['.$this->field['id'].'][]">'.__('Add More', 'vibe').'</a><br/>';
		
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description">'.$this->field['desc'].'</span>':'';
		
	}//function
	
	
	/**
	 * Enqueue Function.
	 *
	 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
	 *
	 * @since VIBE_Options 1.0.5
	*/
	function enqueue(){
		
		wp_enqueue_script(
			'vibe-opts-field-custom-posts-js', 
			VIBE_OPTIONS_URL.'fields/custom_posts/field_custom_posts.js', 
			array('jquery'),
			time(),
			true
		);
		
	}//function
	
}//class
?>