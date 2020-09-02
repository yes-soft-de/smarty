<?php

 if ( ! defined( 'ABSPATH' ) ) exit;

 class Wplms_group_types{

 	protected $option = 'wplms_group_types';
	public static $instance;
    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_group_types();
        return self::$instance;
    }

    public function __construct(){
    	add_filter('lms_general_settings',array($this,'generate_group_types_form'));
    	add_filter('wplms_lms_commission_tabs',array($this,'add_group_types_settings'));
    	add_action('wp_ajax_save_group_types',array($this,'save_group_types'));
        add_action('wp_ajax_reset_group_types',array($this,'reset_group_types'));
        add_action('admin_enqueue_scripts',array($this,'add_vue_scripts'));
        add_action( 'bp_groups_register_group_types',array($this, 'wplms_register_group_types_with_directory' ));
    	
    }

    function add_group_types_settings($tabs){
    	if(!isset($_GET['tab']) || $_GET['tab'] == 'general'){
	    	$tabs['group_types'] = _x('Group Types','configure Course menu in LMS - Settings','vibe-customtypes');
 		}
 		return $tabs;
    }

   function wplms_register_group_types_with_directory() {
        $group_types = get_option($this->option);
        if(!empty($group_types)){
            foreach($group_types as $key => $group_type){
                if(!empty($group_type)){
                     bp_groups_register_group_type( $group_type['id'], array(
                        'labels' => array(
                            'name'          => $group_type['pname'],
                            'singular_name' => $group_type['sname'],
                        ),
                        'has_directory' => $group_type['id'],
                        // New parameters as of BP 2.7.
                        'show_in_create_screen' => apply_filters('wplms_group_type_show_in_create_screen_param',true),
                        'show_in_list' => apply_filters('wplms_group_type_show_in_list_param',true),
                        'description' => $group_type['pname'],
                        'create_screen_checked' => apply_filters('wplms_group_type_create_screen_checked_param',false)

                    ));
                }
                
            }
        }
    }   

 
    function generate_group_types_form($settings){
    	if(!isset($_GET['sub']) || $_GET['sub'] != 'group_types')
    		return $settings;
        if(!function_exists('bp_is_active') || (function_exists('bp_is_active') && !bp_is_active('groups')))
            return;
 
    	if(in_array( 'badgeos/badgeos.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'badgeos/badgeos.php'))){
            wp_deregister_script('badgeos-select2');
            wp_dequeue_script('badgeos-select2');
            wp_deregister_script('select2');
            wp_dequeue_script('select2');
            wp_dequeue_style('badgeos-select2-css');
            wp_deregister_style('badgeos-select2-css');
        }
        
        
        if(function_exists('bp_groups_get_group_types')){
            $group_types_array=array();
            $group_types = bp_groups_get_group_types( array(), 'objects' );
            if(!empty($group_types)){
                foreach ($group_types as $key => $group_type) {
                    $group_types_array[] = array(
                        'show_settings'=>false,
                        'id'=> $key,
                        'sname'=>$group_type->labels['singular_name'],
                        'pname'=>$group_type->labels['name'],
                        'error'=>'',

                        );
                }

                echo '<script>var existing_group_types = '.json_encode($group_types_array).';</script>';
            }
        }
        
       ?>
        <section  id="group_types_wrapper">

            <button @click="add" id="add" class="button button-primary"><?php echo _x('Add another','group types','vibe-customtypes');?></button>
          
            <ul v-sortable id="group_types_cont">
                <li v-for="(listing,index) in listings" class="mt_listing">
                    <small class="dashicons dashicons-menu"></small>
                    <span>{{listing.sname}}</span>
                    <ul v-bind:class="{ show: listing.show_settings }" class="mt_listing_settings">
                        <li><label><?php echo _x('Slug','','vibe-customtypes');?></label>{{listing.id}}</li>
                        <li><label><?php echo _x('Singular Name','','vibe-customtypes');?></label><input type="text" name="slisting" placeholder="<?php echo _x('group type singular name','','vibe-customtypes')?>" class="form-control" v-model="listing.sname" @keyup="check_data(listing,listing.sname)" ></li>

                        <li><label><?php echo _x('Plural Name','','vibe-customtypes');?></label><input type="text" name="plisting" placeholder="<?php echo _x('group type plural name','','vibe-customtypes')?>" class="form-control" v-model="listing.pname" @keyup="check_data(listing,listing.pname)"></li>

                        
                        <li class="ithaserror red" v-html="listing.error"></li>
                    </ul>
                    <em @click="remove_data(index)" class="red remove_group_type action_point dashicons dashicons-no"></em>
                    <em @click="listing.show_settings = !listing.show_settings" class="mt_setting_toggle action_point dashicons dashicons-edit"></em>
                </li>
            </ul>
            <?php wp_nonce_field('vibe_security','vibe_security');?>
            <button v-bind:class="{ loading: loading }" @click="save_mts"  class="button button-primary">{{save_settings_text}}</button>
            <button v-bind:class="{ loading: loading_rs }" class="reset_group_types button"  @click="reset_mts">{{reset_settings_text}}</button>
        </section>
        <style>
            .mt_listing {
                display: block;
                margin: 15px;
                background: #fff;
                width: 85%;
                position: relative;
                padding: 15px;
                border-radius: 3px;
            }
            .mt_listing .action_point {
                right: 10px;
                position: absolute;
                top: 15px;
            }
            em.mt_setting_toggle.action_point {
                right: 40px;
            }
            .red{
                color:red;
            }
        	.remove_group_type,.mt_setting_toggle{
        		cursor: pointer;
        	}
            .ithaserror {
                display: block;
                color: red;
                margin: 10px 2px;
            }
            .loading{
                opacity: 0.5;
            }
            .mt_listing_settings{
                display: none;
            }
            .mt_listing_settings.show{
                display: block;
                margin: 10px;
            }
            .mt_listing_settings>li>label {
                width: 100px;
                display: inline-block;
            }
            

        </style>
        <?php
       
        return array();
    }

    function add_vue_scripts(){
        if(!isset($_GET['sub']) || $_GET['sub'] != 'group_types')
            return;
        wp_enqueue_script('vue-js',VIBE_PLUGIN_URL.'/vibe-customtypes/metaboxes/js/vue.min.js');
        wp_enqueue_script('vue-Sortable-js',VIBE_PLUGIN_URL.'/vibe-customtypes/metaboxes/js/Sortable.min.js');
        add_action('admin_footer',function(){
            ?>
            <script>
                if(typeof existing_group_types != 'undefined'){
                    
                    var group_types = existing_group_types;        
                }else{
                    var group_types = [{
                                show_settings:false,
                                id:"<?php echo _x('N.A','group types','vibe-customtypes')?>",
                                sname: "<?php echo _x('N.A','group types','vibe-customtypes')?>",
                                pname:"",
                                error:""
                            }];
                }
                var vlm =  new Vue({
                    el:"#group_types_wrapper",
                    data: {
                        listings: group_types,
                        hasError: false,
                        disabled_saving:true,
                        loading:false,
                        save_settings_text:"<?php echo _x('Save setings','group types','vibe-customtypes');?>",
                        reset_settings_text:"<?php echo _x('Reset setings','group types','vibe-customtypes');?>",
                        loading_rs:false,
                        show_mt_setting:0,
                    },
                    methods: {
                        add: function (event) {
                            this.listings.push({
                                show_settings:false,
                                id:"<?php echo _x('N.A','group types','vibe-customtypes')?>",
                                sname: "<?php echo _x('N.A','group types','vibe-customtypes')?>",
                                pname:"",
                                error:""
                            });
                        },
                        show_mt:function(index,event){
                            if(this.show_mt_setting)
                            this.show_mt_setting = 1;
                            return !(show_mt_setting+index);
                        },
                        save_mts: function(event) {
                            var listing_data=[];
                            var vuethis = this;
                            if(this.disabled_saving == true){
                                alert("<?php echo _x('Please remove special characters.','group types','vibe-customtypes')?>");
                                return false;  
                            }
                            if(this.loading == true || this.disabled_saving == true){
                                return false;
                            }
                            this.loading = true;
                            var old_sv_text = this.save_settings_text;
                            jQuery.each(this.listings,function(k,listing){
                                if(typeof listing == 'undefined' || typeof listing.sname == 'undefined' || listing.sname == ''){
                                }else{
                                    listing_data.push({id:listing.id,sname:listing.sname,pname:listing.pname});
                                }
                            });
                            console.log(listing_data);
                            jQuery.ajax({
                                url: ajaxurl,
                                data: { action: 'save_group_types', 
                                        security: jQuery('#vibe_security').val(),
                                        group_types : JSON.stringify(listing_data),
                                      },
                                method: 'POST',
                                success:function(html){
                                    newtext = html;
                                    vuethis.save_settings_text = newtext;
                                    setTimeout(function(){
                                        vuethis.loading = false;
                                        vuethis.save_settings_text  = old_sv_text;
                                    },3000);
                                }
                            });
                        },
                        reset_mts:function(event){
                            if(this.loading_rs == true){
                                return false;
                            }
                            this.loading_rs = true;
                            var old_rs_text = this.reset_settings_text;
                            jQuery.ajax({
                                url: ajaxurl,
                                data: { action: 'reset_group_types', 
                                        security: jQuery('#vibe_security').val(),
                                      },
                                method: 'POST',
                                success:function(html){
                                    location.reload();

                                }
                            });
                        },
                        remove_data:function(index,event){
                            this.listings.splice(index,1);
                            var that = this;
                            jQuery.each(this.listings,function(k,listing){
                                if(typeof listing.sname != 'undefined' || listing.sname != '' || typeof listing.pname != 'undefined' || listing.pname != ''){
                                    var found1 =  listing.sname.match(/\`|\~|\!|\@|\#|\$|\%|\^|\&|\*|\(|\)|\+|\=|\[|\{|\]|\}|\||\\|\'|\<|\,|\.|\>|\?|\/|\""|\;|\:/g);
                                    var found2 = listing.pname.match(/\`|\~|\!|\@|\#|\$|\%|\^|\&|\*|\(|\)|\+|\=|\[|\{|\]|\}|\||\\|\'|\<|\,|\.|\>|\?|\/|\""|\;|\:/g);
                                    if(found1 || found2){
                                        that.disabled_saving = true;
                                        return false; 
                                    }else{
                                        that.disabled_saving = false;
                                    }
                                }else{
                                    that.disabled_saving = false;
                                } 
                            });
                            if(that.disabled_saving){
                                this.disabled_saving = true;
                            }
                            
                        },
                        check_data:function(listing,value,event){
                            if(typeof value != 'undefined' || value != ''){
                                var found =  value.match(/\`|\~|\!|\@|\#|\$|\%|\^|\&|\*|\(|\)|\+|\=|\[|\{|\]|\}|\||\\|\'|\<|\,|\.|\>|\?|\/|\""|\;|\:/g);
                                if(found){
                                    listing.error = "<?php echo _x('Special Characters are not allowed','group types','vibe-customtypes');?>";
                                    this.disabled_saving = true;
                                }else{
                                    listing.error = "";
                                    this.disabled_saving = false;
                                }
                            }else{
                                this.disabled_saving = false;
                                listing.error = "";
                            }
                        }
                    },
                    watch: {
                        loading: function(){
                            console.log('Loading Changed');
                        }
                    },
                    mounted: function(){
                        var self = this;
                        self.$nextTick(function(){
                          var sortable = Sortable.create(document.getElementById('group_types_cont'), {
                            onEnd: function(e) {
                              var clonedItems = self.listings.filter(function(item){
                               return item;
                              });
                              clonedItems.splice(e.newIndex, 0, clonedItems.splice(e.oldIndex, 1)[0]);
                              self.listings = [];
                              self.$nextTick(function(){
                                self.listings = clonedItems;
                              });
                            }
                          }); 
                        });
                    }
                });
            </script>
            <?php
        },999);
    }

    function save_group_types(){
    	if ( !isset($_POST['security']) || !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') || !is_user_logged_in() || !current_user_can('edit_posts')){
                _e('Security check Failed. Contact Administrator.','vibe-customtypes');
                die();
        }
        $group_types = stripcslashes($_POST['group_types']);
        $group_types = json_decode($group_types,true);
        $final_group_types = array();
        if(!empty($group_types)){
        	foreach($group_types as $group_type){
	        	$sname = sanitize_text_field($group_type['sname']);
	        	$final_group_types[] = array(
	        		'id'=>sanitize_title($sname),
	        		'sname'=>$sname,
                    'pname'=>sanitize_text_field($group_type['pname'])
	        		);
	        }
        }
        
        update_option($this->option,$final_group_types);
        echo _x('Saved','','vibe-customtypes');
        die();
    }

    function reset_group_types(){
        if ( !isset($_POST['security']) || !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') || !is_user_logged_in() || !current_user_can('edit_posts')){
                _e('Security check Failed. Contact Administrator.','vibe-customtypes');
                die();
        }
        
        
        delete_option($this->option);
        echo _x('Reset','','vibe-customtypes');
        die();
    }
}	
add_action('plugins_loaded',function(){
	Wplms_group_types::init();
},11);