<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * HTML Output for the Add-ons page
 */

$pms_add_ons            = PMS_Submenu_Page_Addons::add_ons_get_remote_content();
$pms_get_all_plugins    = get_plugins();
$pms_get_active_plugins = get_option( 'active_plugins' );
$ajax_nonce             = wp_create_nonce( 'pms-activate-addon' );
$pms_serial_status      = pms_get_serial_number_status();
$pms_serial_number      = pms_get_serial_number();
?>
<div class="wrap pms-wrap pms-add-ons-wrap">
    <h1><?php _e( 'Activate your licence', 'paid-member-subscriptions' ); ?></h1>

    <div class="pms-serial-wrap">
        <form method="post" action="options.php">

            <?php settings_fields( 'pms_serial_number' ); ?>

            <label for="pms_serial_number"><?php _e( 'Serial number', 'paid-member-subscriptions' ); ?></label>
            <div class="pms-add-on-serial-number-wrapper <?php PMS_Submenu_Page_Addons::add_ons_output_styling_class( $pms_serial_status ); ?>">
                <input type="<?php echo ( ( !empty( $pms_serial_status ) && $pms_serial_status == 'notFound' ) || !$pms_serial_number ? 'text' : 'password' ); ?>" name="pms_serial_number" class="<?php PMS_Submenu_Page_Addons::add_ons_output_styling_class( $pms_serial_status ); ?>" id="pms_serial_number" value="<?php echo ( !empty( $pms_serial_number ) ? pms_get_serial_number() : '' ); ?>">                <span class="status-dot"></span>
            </div>

            <?php submit_button( __( 'Save Changes', 'paid-member-subscriptions' ) ); ?>

            <div class="pms-serial-wrap__status <?php PMS_Submenu_Page_Addons::add_ons_output_styling_class( $pms_serial_status ); ?>">

                <?php
                    PMS_Submenu_Page_Addons::add_ons_output_serial_number_status_message();
                ?>
            </div>
        </form>

        <p>
            <?php _e( 'The serial number is used to access the premium add-ons, any updates made to them and support.', 'paid-member-subscriptions' ); ?>
        </p>

    </div>

    <h2><?php _e( 'Recommended Plugins', 'paid-member-subscriptions' ) ?></h2>
    <div>

        <?php
        $trp_add_on_exists = 0;
        $trp_add_on_is_active = 0;
        $trp_add_on_is_network_active = 0;
        // Check to see if add-on is in the plugins folder
        foreach ($pms_get_all_plugins as $pms_plugin_key => $pms_plugin) {
            if( strtolower($pms_plugin['Name']) == strtolower( 'TranslatePress - Multilingual' ) && strpos(strtolower($pms_plugin['AuthorName']), strtolower('Cozmoslabs')) !== false) {
                $trp_add_on_exists = 1;
                if (in_array($pms_plugin_key, $pms_get_active_plugins)) {
                    $trp_add_on_is_active = 1;
                }
                // Consider the add-on active if it's network active
                if (is_plugin_active_for_network($pms_plugin_key)) {
                    $trp_add_on_is_network_active = 1;
                    $trp_add_on_is_active = 1;
                }
                $plugin_file = $pms_plugin_key;
            }
        }
        ?>
        <div class="plugin-card pms-recommended-plugin pms-add-on">
            <div class="plugin-card-top">
                <a target="_blank" href="https://wordpress.org/plugins/translatepress-multilingual/">
                    <img src="<?php echo PMS_PLUGIN_DIR_URL . 'assets/images/trp-recommended.png'; ?>" width="100%">
                </a>
                <h3 class="pms-add-on-title">
                    <a target="_blank" href="https://wordpress.org/plugins/translatepress-multilingual/">TranslatePress</a>
                </h3>
                <h3 class="pms-add-on-price"><?php  _e( 'Free', 'paid-member-subscriptions' ) ?></h3>
                <p class="pms-add-on-description">
                    <?php _e( 'Translate your Paid Member Subscriptions checkout with a WordPress translation plugin that anyone can use. It offers a simpler way to translate WordPress sites, with full support for WooCommerce and site builders.', 'paid-member-subscriptions' ) ?>
                    <a href="<?php admin_url();?>plugin-install.php?tab=plugin-information&plugin=translatepress-multilingual&TB_iframe=true&width=772&height=875" class="thickbox" aria-label="More information about TranslatePress - Multilingual" data-title="TranslatePress - Multilingual"><?php _e( 'More Details' ); ?></a>
                </p>
            </div>
            <div class="plugin-card-bottom pms-add-on-compatible">
                <?php
                if ($trp_add_on_exists) {

                    // Display activate/deactivate buttons
                    if (!$trp_add_on_is_active) {
                        echo '<a class="pms-add-on-activate right button button-secondary" href="' . $plugin_file . '" data-nonce="' . $ajax_nonce . '">' . __('Activate', 'paid-member-subscriptions') . '</a>';

                        // If add-on is network activated don't allow deactivation
                    } elseif (!$trp_add_on_is_network_active) {
                        echo '<a class="pms-add-on-deactivate right button button-secondary" href="' . $plugin_file . '" data-nonce="' . $ajax_nonce . '">' . __('Deactivate', 'paid-member-subscriptions') . '</a>';
                    }

                    // Display message to the user
                    if( !$trp_add_on_is_active ){
                        echo '<span class="dashicons dashicons-no-alt"></span><span class="pms-add-on-message">' . __('Plugin is <strong>inactive</strong>', 'paid-member-subscriptions') . '</span>';
                    } else {
                        echo '<span class="dashicons dashicons-yes"></span><span class="pms-add-on-message">' . __('Plugin is <strong>active</strong>', 'paid-member-subscriptions') . '</span>';
                    }

                } else {
                    // handles the in-page download
                    $pms_paid_link_class = 'button-secondary';
                    $pms_paid_link_text = __('Install Now', 'paid-member-subscriptions');

                    echo '<a class="right install-now button ' . $pms_paid_link_class . '" href="'. wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=translatepress-multilingual'), 'install-plugin_translatepress-multilingual') .'" data-add-on-slug="translatepress-multilingual" data-add-on-name="TranslatePress - Multilingual" data-nonce="' . $ajax_nonce . '">' . $pms_paid_link_text . '</a>';
                    echo '<span class="dashicons dashicons-yes"></span><span class="pms-add-on-message">' . __('Compatible with Paid Member Subscriptions.', 'paid-member-subscriptions') . '</span>';

                }
                ?>
                <div class="spinner"></div>
                <span class="pms-add-on-user-messages pms-error-manual-install"><?php printf(__('Could not install plugin. Retry or <a href="%s" target="_blank">install manually</a>.', 'paid-member-subscriptions'), esc_url( 'https://www.wordpress.org/plugins/translatepress-multilingual' )) ?></a>.</span>
            </div>
        </div>


        <?php
        $pb_add_on_exists = 0;
        $pb_add_on_is_active = 0;
        $pb_add_on_is_network_active = 0;
        // Check to see if add-on is in the plugins folder
        foreach ($pms_get_all_plugins as $pms_plugin_key => $pms_plugin) {
            if( in_array( strtolower($pms_plugin['Name']), array( strtolower( 'Profile Builder' ), strtolower( 'Profile Builder Hobbyist' ), strtolower( 'Profile Builder Pro' ) ) ) && strpos(strtolower($pms_plugin['AuthorName']), strtolower('Cozmoslabs')) !== false) {
                $pb_add_on_exists = 1;
                if (in_array($pms_plugin_key, $pms_get_active_plugins)) {
                    $pb_add_on_is_active = 1;
                }
                // Consider the add-on active if it's network active
                if (is_plugin_active_for_network($pms_plugin_key)) {
                    $pb_add_on_is_network_active = 1;
                    $pb_add_on_is_active = 1;
                }
                $plugin_file = $pms_plugin_key;

                if( $pb_add_on_is_active )
                    break;
            }
        }
        ?>
        <div class="plugin-card pms-recommended-plugin pms-add-on">
            <div class="plugin-card-top">
                <a target="_blank" href="http://wordpress.org/plugins/profile-builder/">
                    <img src="<?php echo PMS_PLUGIN_DIR_URL . 'assets/images/pb-recommended.png'; ?>" width="100%">
                </a>
                <h3 class="pms-add-on-title">
                    <a target="_blank" href="http://wordpress.org/plugins/profile-builder/">Profile Builder</a>
                </h3>
                <h3 class="pms-add-on-price"><?php  _e( 'Free', 'paid-member-subscriptions' ) ?></h3>
                <p class="pms-add-on-description">
                    <?php _e( "Capture more user information on the registration form with the help of Profile Builder's custom user profile fields and/or add an Email Confirmation process to verify your customers accounts.", 'paid-member-subscriptions' ) ?>
                    <a href="<?php admin_url();?>plugin-install.php?tab=plugin-information&plugin=profile-builder&TB_iframe=true&width=772&height=875" class="thickbox" aria-label="More information about Profile Builder" data-title="Profile Builder"><?php _e( 'More Details' ); ?></a>
                </p>
            </div>
            <div class="plugin-card-bottom pms-add-on-compatible">
                <?php
                if ($pb_add_on_exists) {

                    // Display activate/deactivate buttons
                    if (!$pb_add_on_is_active) {
                        echo '<a class="pms-add-on-activate right button button-secondary" href="' . $plugin_file . '" data-nonce="' . $ajax_nonce . '">' . __('Activate', 'paid-member-subscriptions') . '</a>';

                        // If add-on is network activated don't allow deactivation
                    } elseif (!$pb_add_on_is_network_active) {
                        echo '<a class="pms-add-on-deactivate right button button-secondary" href="' . $plugin_file . '" data-nonce="' . $ajax_nonce . '">' . __('Deactivate', 'paid-member-subscriptions') . '</a>';
                    }

                    // Display message to the user
                    if( !$pb_add_on_is_active ){
                        echo '<span class="dashicons dashicons-no-alt"></span><span class="pms-add-on-message">' . __('Plugin is <strong>inactive</strong>', 'paid-member-subscriptions') . '</span>';
                    } else {
                        echo '<span class="dashicons dashicons-yes"></span><span class="pms-add-on-message">' . __('Plugin is <strong>active</strong>', 'paid-member-subscriptions') . '</span>';
                    }

                } else {
                    // handles the in-page download
                    $pms_paid_link_class = 'button-secondary';
                    $pms_paid_link_text = __('Install Now', 'paid-member-subscriptions');

                    echo '<a class="right install-now button ' . $pms_paid_link_class . '" href="'. wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=profile-builder'), 'install-plugin_profile-builder') .'" data-add-on-slug="profile-builder" data-add-on-name="Profile Builder" data-nonce="' . $ajax_nonce . '">' . $pms_paid_link_text . '</a>';
                    echo '<span class="dashicons dashicons-yes"></span><span class="pms-add-on-message">' . __('Compatible with Paid Member Subscriptions.', 'paid-member-subscriptions') . '</span>';

                }
                ?>
                <div class="spinner"></div>
                <span class="pms-add-on-user-messages pms-error-manual-install"><?php printf(__('Could not install plugin. Retry or <a href="%s" target="_blank">install manually</a>.', 'paid-member-subscriptions'), esc_url( 'http://www.wordpress.org/plugins/profile-builder' )) ?></a>.</span>
            </div>
        </div>

    </div>


    <div class="clear"></div>


    <h2 id="pms-addons-title"><?php echo $this->page_title; ?></h2>
    <?php
    //for now we only have the free version, maybe this will change in the future
    $version = 'Free';
    ?>

    <p>
        <?php
            if ( pms_get_serial_number() )
                printf( __( 'The paid add-ons can be downloaded from your <a href="%s">Cozmoslabs Account</a> page.', 'paid-member-subscriptions'), esc_url( 'https://www.cozmoslabs.com/account/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=PMS&utm_content=add-on-page-active-serial-add-ons-download-message' ) );
        ?>
    </p>

    <span id="pms-add-on-activate-button-text" class="pms-add-on-user-messages"><?php echo __( 'Activate', 'paid-member-subscriptions' ); ?></span>

    <span id="pms-add-on-downloading-message-text" class="pms-add-on-user-messages"><?php echo __( 'Downloading and installing...', 'paid-member-subscriptions' ); ?></span>
    <span id="pms-add-on-download-finished-message-text" class="pms-add-on-user-messages"><?php echo __( 'Installation complete', 'paid-member-subscriptions' ); ?></span>

    <span id="pms-add-on-activated-button-text" class="pms-add-on-user-messages"><?php echo __( 'Add-On is Active', 'paid-member-subscriptions' ); ?></span>
    <span id="pms-add-on-activated-message-text" class="pms-add-on-user-messages"><?php echo __( 'Add-On has been activated', 'paid-member-subscriptions' ) ?></span>
    <span id="pms-add-on-activated-error-button-text" class="pms-add-on-user-messages"><?php echo __( 'Retry Install', 'paid-member-subscriptions' ) ?></span>

    <span id="pms-add-on-is-active-message-text" class="pms-add-on-user-messages"><?php echo __( 'Add-On is <strong>active</strong>', 'paid-member-subscriptions' ); ?></span>
    <span id="pms-add-on-is-not-active-message-text" class="pms-add-on-user-messages"><?php echo __( 'Add-On is <strong>inactive</strong>', 'paid-member-subscriptions' ); ?></span>

    <span id="pms-add-on-deactivate-button-text" class="pms-add-on-user-messages"><?php echo __( 'Deactivate', 'paid-member-subscriptions' ) ?></span>
    <span id="pms-add-on-deactivated-message-text" class="pms-add-on-user-messages"><?php echo __( 'Add-On has been deactivated.', 'paid-member-subscriptions' ) ?></span>

    <div id="the-list">

        <?php

        if( $pms_add_ons === false ) {

            echo __('Something went wrong, we could not connect to the server. Please try again later.', 'paid-member-subscriptions');

        } else {

            foreach( $pms_add_ons as $key => $pms_add_on ) {

                $pms_add_on_exists = 0;
                $pms_add_on_is_active = 0;
                $pms_add_on_is_network_active = 0;

                // Check to see if add-on is in the plugins folder
                foreach ($pms_get_all_plugins as $pms_plugin_key => $pms_plugin) {
                    if (strpos(strtolower($pms_plugin['Name']), strtolower($pms_add_on['name'])) !== false && strpos(strtolower($pms_plugin['AuthorName']), strtolower('Cozmoslabs')) !== false) {
                        $pms_add_on_exists = 1;

                        if (in_array($pms_plugin_key, $pms_get_active_plugins)) {
                            $pms_add_on_is_active = 1;
                        }

                        // Consider the add-on active if it's network active
                        if (is_plugin_active_for_network($pms_plugin_key)) {
                            $pms_add_on_is_network_active = 1;
                            $pms_add_on_is_active = 1;
                        }

                        if( empty( $pms_add_on['plugin_file'] ) )
                            $pms_add_on['plugin_file'] = $pms_plugin_key;
                    }
                }



                echo '<div class="plugin-card pms-add-on">';
                echo '<div class="plugin-card-top">';

                if( ! empty( $pms_add_on['publish_date'] ) && strtotime( $pms_add_on['publish_date'] ) > time() - 2 * WEEK_IN_SECONDS )
                    echo '<div class="pms-add-on-corner-ribbon">' . __( 'New!', 'paid-member-subscriptions' ) . '</div>';

                echo '<a target="_blank" href="' . esc_attr( $pms_add_on['url'] ) . '?utm_source=wpbackend&utm_medium=clientsite&utm_content=add-on-page-plugin-cards&utm_campaign=PMS' . $version . '">';
                    echo '<img src="' . esc_attr( $pms_add_on['thumbnail_url'] ) . '" />';
                echo '</a>';

                echo '<h3 class="pms-add-on-title">';
                    echo '<a target="_blank" href="' . esc_attr( $pms_add_on['url'] ) . '?utm_source=wpbackend&utm_medium=clientsite&utm_content=add-on-page-plugin-cards&utm_campaign=PMS' . $version . '">';
                        echo esc_html( $pms_add_on['name'] );
                    echo '</a>';
                echo '</h3>';

                if( !( $pms_add_on['paid']) && $pms_add_on_exists ) {

                    if( $pms_add_on['paid'] )
                        $bundle_name = ( in_array( 'pro', $pms_add_on['product_version_type'] ) ? 'Pro' : 'Hobbyist' );
                    else
                        $bundle_name = 'Free';

                    echo '<h3 class="pms-add-on-price">' . __( 'Available in: ', 'paid-member-subscriptions' ) . $bundle_name . ' ' . __( 'version', 'paid-member-subscriptions' ) . '</h3>';

                } else if ( !$pms_add_on_exists ) {

                    if( $pms_add_on['paid'] )
                        $bundle_name = ( in_array( 'pro', $pms_add_on['product_version_type'] ) ? 'Pro' : 'Hobbyist' );
                    else
                        $bundle_name = 'Free';

                    if ( !pms_get_serial_number() )
                        echo '<h3 class="pms-add-on-price">' . __( 'Available in: ', 'paid-member-subscriptions' ) . $bundle_name . ' ' . __( 'version', 'paid-member-subscriptions' ) . '</h3>';

                }

                echo '<p class="pms-add-on-description">' . esc_html( $pms_add_on['description'] ) . '</p>';

                echo '</div>';

                $pms_version_validation = version_compare( PMS_VERSION, $pms_add_on['product_version']);

                ($pms_version_validation != -1) ? $pms_version_validation_class = 'pms-add-on-compatible' : $pms_version_validation_class = 'pms-add-on-not-compatible';

                echo '<div class="plugin-card-bottom ' . $pms_version_validation_class . '">';

                // PB minimum version number is all good
                if ($pms_version_validation != -1) {

                    // PB version type does match
                    if (in_array(strtolower($version), $pms_add_on['product_version_type'])) {

                        if ($pms_add_on_exists) {

                            // Display activate/deactivate buttons
                            if (!$pms_add_on_is_active) {
                                echo '<a class="pms-add-on-activate right button button-secondary" href="' . esc_attr( $pms_add_on['plugin_file'] ) . '" data-nonce="' . $ajax_nonce . '">' . __('Activate', 'paid-member-subscriptions') . '</a>';

                                // If add-on is network activated don't allow deactivation
                            } elseif (!$pms_add_on_is_network_active) {
                                echo '<a class="pms-add-on-deactivate right button button-secondary" href="' . esc_attr( $pms_add_on['plugin_file'] ) . '" data-nonce="' . $ajax_nonce . '">' . __('Deactivate', 'paid-member-subscriptions') . '</a>';
                            }

                            // Display message to the user
                            if (!$pms_add_on_is_active) {
                                echo '<span class="dashicons dashicons-no-alt"></span><span class="pms-add-on-message">' . __('Add-On is <strong>inactive</strong>', 'paid-member-subscriptions') . '</span>';
                            } else {
                                echo '<span class="dashicons dashicons-yes"></span><span class="pms-add-on-message">' . __('Add-On is <strong>active</strong>', 'paid-member-subscriptions') . '</span>';
                            }

                            echo '<div class="spinner"></div>';

                        } else {

                            $pms_paid_href_utm_text = '?utm_source=wpbackend&utm_medium=clientsite&utm_content=add-on-page-buy-button&utm_campaign=PMS' . $version;

                            echo '<a target="_blank" class="right button button-primary" href="' . esc_attr( $pms_add_on['url'] . $pms_paid_href_utm_text ) . '" data-add-on-slug="paid-member-subscriptions-' . esc_attr( $pms_add_on['slug'] ) . '" data-add-on-name="' . esc_attr( $pms_add_on['name'] ) . '" data-nonce="' . $ajax_nonce . '">' . __('Learn More', 'paid-member-subscriptions') . '</a>';
                            echo '<span class="dashicons dashicons-yes"></span><span class="pms-add-on-message">' . __('Compatible with your version of Paid Member Subscriptions.', 'paid-member-subscriptions') . '</span>';

                        }

                        // PB version type does not match
                    } else {

                        echo '<a target="_blank" class="button button-secondary right" href="http://www.cozmoslabs.com/paid-member-subscriptions/?utm_source=wpbackend&utm_medium=clientsite&utm_content=add-on-page-upgrade-button&utm_campaign=PMS' . $version . '">' . __('Upgrade Paid Member Subscriptions', 'paid-member-subscriptions') . '</a>';
                        echo '<span class="dashicons dashicons-no-alt"></span><span class="pms-add-on-message">' . __('Not compatible with Paid Member Subscriptions', 'paid-member-subscriptions') . ' ' . $version . '</span>';

                    }

                } else {

                    // If PMS version is older than the minimum required version of the add-on
                    echo ' ' . '<a class="button button-secondary right" href="' . admin_url('plugins.php') . '">' . __('Update', 'paid-member-subscriptions') . '</a>';
                    echo '<span class="pms-add-on-message">' . __('Not compatible with your version of Paid Member Subscriptions.', 'paid-member-subscriptions') . '</span><br />';
                    echo '<span class="pms-add-on-message">' . __('Minimum required Paid Member Subscriptions version:', 'paid-member-subscriptions') . '<strong> ' . $pms_add_on['product_version'] . '</strong></span>';

                }

                // We had to put this error here because we need the url of the add-on
                echo '<span class="pms-add-on-user-messages pms-error-manual-install">' . sprintf(__('Could not install add-on. Retry or <a href="%s" target="_blank">install manually</a>.', 'paid-member-subscriptions'), esc_url($pms_add_on['url'])) . '</span>';

                echo '</div>';
                echo '</div>';

            } /* end $pms_add_ons foreach */
        }

        ?>
    </div>

</div>
