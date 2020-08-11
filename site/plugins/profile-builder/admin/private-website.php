<?php

/**
 * Function that creates the "Private Website" submenu page
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_private_website_submenu_page() {
    add_submenu_page( null, __( 'Private Website', 'profile-builder' ), __( 'Private Website', 'profile-builder' ), 'manage_options', 'profile-builder-private-website', 'wppb_private_website_content' );
}
add_action( 'admin_menu', 'wppb_private_website_submenu_page' );


/**
 * Function that generates the default settings for private page
 */
function wppb_private_website_settings_defaults() {

    add_option( 'wppb_private_website_settings',
        array(
            'private_website'       =>  'no',
            'redirect_to'           =>  '',
            'allowed_pages'         =>  array(),
            'hide_menus'            =>  'no',
        )
    );

}

/**
 * Function that generates the content for the settings page
 */
function wppb_private_website_content() {

    wppb_private_website_settings_defaults();

    $wppb_private_website_settings = get_option( 'wppb_private_website_settings', 'not_found' );
    $all_pages = get_pages();
    ?>
    <div class="wrap wppb-wrap wppb-private-website">
        <h2><?php _e( 'Private Website Settings', 'profile-builder' );?></h2>

        <?php settings_errors(); ?>

        <?php wppb_generate_settings_tabs() ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'wppb_private_website_settings' ); ?>

            <table class="form-table">
                <tbody>

                    <tr>
                        <th><?php _e( 'Enable Private Website', 'profile-builder' ); ?></th>
                        <td>
                            <select id="private-website-enable" class="wppb-select" name="wppb_private_website_settings[private_website]">
                                <option value="no" <?php echo ( ( $wppb_private_website_settings != 'not_found' && $wppb_private_website_settings['private_website'] == 'no' ) ? 'selected' : '' ); ?>><?php _e( 'No', 'profile-builder' ); ?></option>
                                <option value="yes" <?php echo ( ( $wppb_private_website_settings != 'not_found' && $wppb_private_website_settings['private_website'] == 'yes' ) ? 'selected' : '' ); ?>><?php _e( 'Yes', 'profile-builder' ); ?></option>
                            </select>
                            <ul>
                                <li class="description"><?php _e( 'Activate Private Website. It will restrict the content, RSS and REST API for your website', 'profile-builder' ); ?></li>
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e( 'Redirect to', 'profile-builder' ); ?></th>
                        <td>
                            <select id="private-website-redirect-to-login" class="wppb-select" name="wppb_private_website_settings[redirect_to]">
                                <option value=""><?php _e( 'Default WordPress login page', 'profile-builder' ); ?></option>
                                <?php
                                if( !empty( $all_pages ) ){
                                    foreach ($all_pages as $page){
                                        ?>
                                        <option value="<?php echo $page->ID ?>" <?php echo ( ( $wppb_private_website_settings != 'not_found' && isset( $wppb_private_website_settings['redirect_to'] ) && $wppb_private_website_settings['redirect_to'] == $page->ID ) ? 'selected' : '' ); ?>><?php echo $page->post_title ?></option>
                                        <?php
                                    }
                                }
                                ?>

                            </select>
                            <ul>
                                <li class="description"><?php _e( 'Redirects to this page if not logged in. We recommend this page contains the [wppb-login] shortcode.', 'profile-builder' ); ?></li>
                                <li class="description"><?php _e( 'You can force access to wp-login.php so you don\'t get locked out of the site by accessing the link:', 'profile-builder' ); ?> <a href="<?php echo wp_login_url().'?wppb_force_wp_login=true' ?>"><?php echo wp_login_url().'?wppb_force_wp_login=true' ?></a></li>
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e( 'Allowed Pages', 'profile-builder' ); ?></th>
                        <td>
                            <select id="private-website-allowed-pages" class="wppb-select" name="wppb_private_website_settings[allowed_pages][]" multiple="multiple">
                                <?php
                                if( !empty( $all_pages ) ){
                                    foreach ($all_pages as $page){
                                        ?>
                                        <option value="<?php echo $page->ID ?>" <?php echo ( ( $wppb_private_website_settings != 'not_found' && isset( $wppb_private_website_settings['allowed_pages'] ) && in_array( $page->ID, $wppb_private_website_settings['allowed_pages'] ) ) ? 'selected' : '' ); ?>><?php echo $page->post_title ?></option>
                                        <?php
                                    }
                                }
                                ?>

                            </select>
                            <ul>
                                <li class="description"><?php _e( 'Allow these pages to be accessed even if you are not logged in', 'profile-builder' ); ?></li>
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e( 'Hide all Menus', 'profile-builder' ); ?></th>
                        <td>
                            <select id="private-website-menu-hide" class="wppb-select" name="wppb_private_website_settings[hide_menus]">
                                <option value="no" <?php echo ( ( $wppb_private_website_settings != 'not_found' && !empty($wppb_private_website_settings['hide_menus']) && $wppb_private_website_settings['hide_menus'] == 'no' ) ? 'selected' : '' ); ?>><?php _e( 'No', 'profile-builder' ); ?></option>
                                <option value="yes" <?php echo ( ( $wppb_private_website_settings != 'not_found' && !empty($wppb_private_website_settings['hide_menus']) && $wppb_private_website_settings['hide_menus'] == 'yes' ) ? 'selected' : '' ); ?>><?php _e( 'Yes', 'profile-builder' ); ?></option>
                            </select>
                            <ul>
                                <li class="description"><?php _e( 'Hide all menu items if you are not logged in.', 'profile-builder' ); ?></li>
                                <li class="description"><?php printf( __( 'We recommend "<a href="%s" target="_blank">Custom Profile Menus</a>" addon if you need different menu items for logged in / logged out users.', 'profile-builder' ), 'https://www.cozmoslabs.com/add-ons/custom-profile-menus/' ); ?></li>
                            </ul>
                        </td>
                    </tr>

                </tbody>
            </table>

            <?php submit_button( __( 'Save Changes', 'profile-builder' ) ); ?>
        </form>


    </div>
    <?php
}