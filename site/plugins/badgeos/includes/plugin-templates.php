<?php
/**
 * Templates related Functions
 *
 * @package BadgeOS
 * @subpackage Admin
 * @author LearningTimes, LLC
 * @license http://www.gnu.org/licenses/agpl.txt GNU AGPL v3.0
 * @link https://credly.com
 */

/**
 * Display the available list of templates. 
 * 
 * @return none
 */
function badgeos_load_json_templates_data() {
    $request = wp_remote_get( 'https://badgeos.org/badgeos-templates-api.php' );

    if( is_wp_error( $request ) ) {
        return false; // Bail early
    }

    $body = wp_remote_retrieve_body( $request );

    return json_decode( $body );
}

/**
 * Display the available list of templates.
 * 
 * @return none
 */
function badgeos_load_json_addons_data() {
    $request = wp_remote_get( 'https://badgeos.org/badgeos-addon-suggestions-api.php' );
    if( is_wp_error( $request ) ) {
        return false; // Bail early
    }

    $body = wp_remote_retrieve_body( $request );

    return json_decode( $body );
}

 /**
 * Display the available list of templates.
 * 
 * @return none
 */
function badgeos_templates() {

    add_thickbox();
    $templates = badgeos_load_json_templates_data();
    
    if( is_array( $templates ) && count( $templates ) > 0 ) {
        echo '<div class="badgeos_templates">';
        wp_enqueue_script('badgeos-carouselTicker-js' );
        wp_enqueue_style( 'badgeos-admin-js' );
        wp_enqueue_script( 'badgeos-templates-js' );
        $addons = badgeos_load_json_addons_data();
        if( count( $addons ) > 0 ) {
            echo '<h1>'.__( 'Suggested Addons', 'badgeos' ).'</h1>';
            ?>
                <div id="carouselTicker" class="carouselTicker">
                    <ul class="carouselTicker__list">
                        <?php foreach( $addons as $key=>$addon ) { ?>
                            <?php if( $addon->active =='Yes' ) { ?>
                                <li class="carouselTicker__item badgeos_carousel_ticker_item_<?php echo $key;?>">
                                    <div class="badgeos-item_content badgeos-admin-carousel-ticker-item">
                                        <div class="badgeos-item-image">
                                            <img src="<?php echo $addon->image;?>" alt="<?php echo $addon->image;?>" style="width:100%">
                                            <div class="badgeos-item_seemore" data-url="<?php echo $addon->url;?>">
                                                <a href="javascript:;"><?php _e( 'Read More', 'badgeos' );?></a>
                                            </div>
                                        </div>
                                        <div class="badgeos-item-data">
                                            <div class="title"><strong><?php echo $addon->title;?></strong></div>
                                            <div class="description"><?php echo $addon->description;?></div>
                                        </div>
                                    </div>
                                    
                                </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
            <?php
        }
        echo '<h1>'.__( 'Templates', 'badgeos' ).'</h1>';
        $root_url = badgeos_get_directory_url();
        foreach( $templates as $key => $template ) {
            ?>
                <div class="badgeos_item badgeos_item_<?php echo $key;?>">
                    <div class="badgeos-template-image">
                        <img src="<?php echo $template->image;?>" alt="Denim Jeans" style="width:100%">
                    </div>
                    <h2><?php echo $template->title;?></h2>
                    <div class="badgeos-item-description"><?php echo $template->description;?></div>
                    <div class="badgeos_template_message" style="visibility:hidden;">
                        <div class="badgeos_template_info_msg"><?php _e( 'Process is complete.', 'badgeos' ); ?> <?php _e( 'Please, click', 'badgeos' ); ?> <a class="badgeos_template_response_open_popup" data-popup_id="badgeos_template_info_log_<?php echo $key;?>" class="thickbox" title="<?php _e( 'Installation Log', 'badgeos' ); ?>"><?php _e( 'here', 'badgeos' ); ?> </a> <?php _e( 'to view installation Log.', 'badgeos' ); ?> </div>
                        <div id="badgeos_template_info_log_<?php echo $key;?>" class="badgeos_template_info_log">
                            <div class="badgeos_template_info_log_content">

                            </div>
                        </div>
                    </div>
                    <p class="badgeos-item-button">
                        <input type="hidden" name="badgeos_template_id" class="badgeos_template_id" id="badgeos_template_id" value="<?php echo $key;?>" />
                        <button class="btn_badgeos_install_template" data-ajax_url="<?php echo admin_url( 'admin-ajax.php' ); ?>">
                            <?php _e( 'Install Template', 'badgeos' ); ?>
                            <img id="btn_badgeos_install_template_loader" style="background-color:#000000; visibility:hidden;" src="<?php echo $root_url.'/images/ajax-loader.gif';?>" />
                        </button>
                    </p>
                </div>
            <?php
        }
        echo '</div>';
    } else {
        ?>
            <div class="box-templates-alert">
                <?php _e( 'No Templates found.', 'badgeos' ); ?>
            </span>
        <?php
    }
}

 /**
 * Install the the plugin
 * 
 * @param $title
 * @param $install_type
 * @param $plugin_path
 * @param $plugin_slug
 * 
 * @return none
 */
function badgeos_install_plugin($title='', $install_type='', $plugin_path='', $plugin_slug='' ) {
    
    $path = badgeos_get_directory_url();
    $active_plugins = apply_filters( 'active_plugins', get_option('active_plugins' ));
    $addon_path = '';
    if( $install_type == 'bundled' ) {
        $addon_path = $path."includes/addons/".$plugin_path;
    } else {
        $addon_path = $plugin_path;
    }

    // All plugin information will be stored in an array for processing.
    $slug = str_replace( ' ', '-', strip_tags( $title ) );
    $install_type = 'install';
    if( !empty( $plugin_slug ) ) {
        if( file_exists( trailingslashit( WP_PLUGIN_DIR ).$plugin_slug ) )  {  
            $install_type = 'update';
        }
    }
    
    if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    }

    $extra         = array();
    $extra['slug'] = 'abbas'; // Needed for potentially renaming of directory name.

    $skin_args = array(
        'type'   => $install_type,
        'title'  => $title,
        'url'    => $addon_path,
        'nonce'  => $install_type . '-plugin_' . $slug,
        'plugin' => '',
        'api'    => $plugin_slug,
        'extra'  => $extra
    );
    
    if ( 'update' === $install_type ) {
        $skin = new Plugin_Upgrader_Skin( $skin_args );
    } else {
        $skin = new Plugin_Installer_Skin( $skin_args );
    }

    // Create a new instance of Plugin_Upgrader.
    $upgrader = new Plugin_Upgrader( $skin );
    if ( 'update' === $install_type ) {
        $upgrader->upgrade( $addon_path );
    } else {
        $upgrader->install( $addon_path );
    }
    
    if ( is_plugin_inactive( $plugin_slug ) ) {
        activate_plugin( $plugin_slug, '', false, true );
    }
}

/**
 * Install the templates
 */
function badgeos_install_template_callback() {
    
    set_time_limit( 0 );
    $template_id = sanitize_text_field( $_REQUEST['template_id'] );
    $templates = badgeos_load_json_templates_data();
    if( count( $templates ) > 0 ) {
        $template = $templates[$template_id];
        $all_main_reqs_met = true;
        if( !empty( $template->required_classes ) ){
            $required_classes = explode( ',', $template->required_classes );
            if( count( $required_classes ) > 0 ) {
                foreach( $required_classes as $class ) { 
                    if( ! class_exists( trim(  $class ) ) ) {
                        $all_main_reqs_met = false;
                    }
                }
            }
        }
            
        if( $all_main_reqs_met ) {
            if( $template->install_type != 'none' ) {
                badgeos_install_plugin($template->title, $template->install_type, $template->plugin_path, $template->plugin_slug);
            } else {
                if ( is_plugin_inactive( $template->plugin_slug ) ) {
                    $activate = activate_plugin( $template->plugin_slug, '', false, true );
                }
            }
            
            if( !empty( $template->required_configuration ) ) {
                echo '<p style="color:red; background-color:#ddd; border-radius:5px; padding:5px;">'.__( $template->required_configuration, 'badgeos' ).'</p>';
            }

            $addons = $template->addons; 
            if( count( $addons ) > 0 ) {
                foreach( $addons as $addon ) {
                    if( $addon->install_type != 'none' ) {
                        $all_sub_reqs_met = true;
                        if( !empty( $addon->required_classes ) ){
                            $required_sub_classes = explode( ',', $addon->required_classes );
                            if( count( $required_sub_classes ) > 0 ) {
                                foreach( $required_sub_classes as $sbclass ) { 
                                    if( ! class_exists( trim(  $sbclass ) ) ) {
                                        $all_sub_reqs_met = false;
                                    }
                                }
                            }
                        }
                        if( $all_sub_reqs_met == true ) {
                            
                            badgeos_install_plugin($addon->title, $addon->install_type, $addon->plugin_path, $addon->plugin_slug );
                            if( !empty( $addon->required_configuration ) ) {
                                echo '<p style="color:red; background-color:#ddd; border-radius:5px; padding:5px;">'.__( $addon->required_configuration, 'badgeos' ).'</p>';
                            }
                        } else {
                            echo '<h1>'.$addon->title.'</h1><p>'.__( $addon->required_message, 'badgeos' ).'</p>';
                        }
                    }
                }
            }
        } else {
            echo '<h1>'.$template->title.'</h1><p>'.__( $template->required_message, 'badgeos' ).'</p>';
        }
    }

    die();
}
add_action( 'wp_ajax_badgeos_install_template', 'badgeos_install_template_callback' );
add_action( 'wp_ajax_nopriv_badgeos_install_template', 'badgeos_install_template_callback' );