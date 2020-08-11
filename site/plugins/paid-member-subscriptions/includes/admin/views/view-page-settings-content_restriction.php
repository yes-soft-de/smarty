<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * HTML Output for the Content Restriction tab
 */
?>

<h3><?php _e( 'General', 'paid-member-subscriptions' ); ?></h3>

<!-- Type of Restriction -->
<div class="pms-form-field-wrapper">

    <label class="pms-form-field-label">
        <?php _e( 'Type of Restriction', 'paid-member-subscriptions' ); ?>
    </label>

    <?php
        $content_restrict_types = apply_filters( 'pms_general_content_restrict_types', array( 'message' => __( 'Message', 'paid-member-subscriptions' ), 'redirect' => __( 'Redirect', 'paid-member-subscriptions' ), 'template' => __( 'Template', 'paid-member-subscriptions' ) ) );

        $current_type = 0;

        foreach( $content_restrict_types as $type_slug => $type_label ): ?>

            <label for="pms-content-restrict-type-<?php echo $type_slug; ?>">

                 <input type="radio" id="pms-content-restrict-type-<?php echo esc_attr( $type_slug ); ?>" value="<?php echo esc_attr( $type_slug ); ?>" <?php if( ( $current_type == 0 && empty( $this->options['content_restrict_type'] ) ) || ( ! empty( $this->options['content_restrict_type'] ) && $this->options['content_restrict_type'] == $type_slug ) ) echo 'checked="checked"'; ?> name="pms_content_restriction_settings[content_restrict_type]">
                 <?php echo $type_label; ?>

            </label>

            <?php
            $current_type++;

         endforeach;
    ?>

    <p class="description" style="margin-top: 10px;"><?php _e( 'If you select "Messages" the post\'s content will be protected by being replaced with a custom message.', 'paid-member-subscriptions' ); ?></p>
    <p class="description"><?php _e( 'If you select "Redirect" the post\'s content will be protected by redirecting the user to the URL you specify. The redirect happens only when accessing a single post. On archive pages the restriction message will be displayed, instead of the content.', 'paid-member-subscriptions' ); ?></p>
    <p class="description"><?php _e( 'If you select "Template" the template for the restricted post/page will change to the selected template in the Restriction Template section below.', 'paid-member-subscriptions' ); ?></p>

</div>

<!-- Redirect URL -->
<h3><?php _e( 'Restriction Redirect', 'paid-member-subscriptions' ); ?></h3>

<div class="pms-form-field-wrapper">
    <label class="pms-form-field-label"><?php _e( 'Redirect URL', 'paid-member-subscriptions' ); ?></label>
    <input type="text" class="widefat" name="pms_content_restriction_settings[content_restrict_redirect_url]" value="<?php echo ( ! empty( $this->options['content_restrict_redirect_url'] ) ? esc_attr( $this->options['content_restrict_redirect_url'] ) : '' ); ?>" />
</div>

<!-- Restrict Messages -->
<h3><?php _e( 'Restriction Messages', 'paid-member-subscriptions' ); ?></h3>

<div class="pms-form-field-wrapper">
    <label class="pms-form-field-label"><?php _e( 'Messages for logged-out users', 'paid-member-subscriptions' ); ?></label>
    <?php wp_editor( pms_get_restriction_content_message( 'logged_out' ), 'messages_logged_out', array( 'textarea_name' => 'pms_content_restriction_settings[logged_out]', 'editor_height' => 250 ) ); ?>
</div>

<div class="pms-form-field-wrapper">
    <label class="pms-form-field-label"><?php _e( 'Messages for logged-in non-member users', 'paid-member-subscriptions' ); ?></label>
    <?php wp_editor( pms_get_restriction_content_message( 'non_members' ), 'messages_non_members', array( 'textarea_name' => 'pms_content_restriction_settings[non_members]', 'editor_height' => 250 ) ); ?>
</div>

<!-- Other restrict messages -->
<?php do_action( $this->menu_slug . '_tab_content_restriction_restrict_messages_bottom', $this->options); ?>


<!-- Template Restrict -->
<h3><?php _e( 'Restriction Template', 'paid-member-subscriptions' ); ?></h3>

<div class="pms-form-field-wrapper">
    <label for="pms-content-restriction-template-select" class="pms-form-field-label"><?php _e( 'Template', 'paid-member-subscriptions' ); ?></label>
    <select id="pms-content-restriction-template-select" name="pms_content_restriction_settings[content_restrict_template]">
        <option value=''><?php _e( 'Default', 'paid-member-subscriptions' ) ?></option>
        <?php
        $selected_template = ( ! empty( $this->options['content_restrict_template'] ) ? esc_attr( $this->options['content_restrict_template'] ) : '' );
        $templates = get_page_templates( null, 'page' );

        //add the single.php template if it exists
        $single_template = locate_template(array('single.php'));
        if (!empty($single_template)) {
            $templates['Single'] = 'single.php';
        }

        ksort( $templates );
        foreach ( array_keys( $templates ) as $template ) {
            $selected = selected( $selected_template, $templates[ $template ], false );
            echo "\n\t<option value='" . esc_attr( $templates[ $template ] ) . "' $selected>" . esc_html( $template ) . "</option>";
        }

        ?>
    </select>
    <p class="description"><?php _e( 'Select which template should load instead of the default one when a post is restricted and the restriction type is set to "Template".', 'paid-member-subscriptions' ); ?></p>
</div>


<h3><?php _e( 'Misc', 'paid-member-subscriptions' ); ?></h3>

<!-- Restrict Comments -->
<div class="pms-form-field-wrapper pms-form-field-rpp">
    <label class="pms-form-field-label" for="restricted-posts-preview"><?php _e( 'Restrict Comments', 'paid-member-subscriptions' ) ?></label>

    <div>
        <label>
            <input type="radio" name="pms_content_restriction_settings[comments_restriction][option]" value="off" <?php echo ( !isset( $this->options['comments_restriction']['option'] ) || $this->options['comments_restriction']['option'] == 'off' ? 'checked' : '' ); ?> />
            <span><?php _e( 'Off', 'paid-member-subscriptions' ); ?></span>
        </label>
    </div>
    <div>
        <label>
            <input type="radio" name="pms_content_restriction_settings[comments_restriction][option]" value="restrict-replies" <?php echo ( isset( $this->options['comments_restriction']['option'] ) && $this->options['comments_restriction']['option'] == 'restrict-replies' ? 'checked' : '' ); ?> />
            <span><?php _e( 'Restrict replying, but allow users to view comments', 'paid-member-subscriptions' ); ?></span>
        </label>
    </div>
    <div>
        <label>
            <input type="radio" name="pms_content_restriction_settings[comments_restriction][option]" value="restrict-everything" <?php echo ( isset( $this->options['comments_restriction']['option'] ) && $this->options['comments_restriction']['option'] == 'restrict-everything' ? 'checked' : '' ); ?> />
            <span><?php _e( 'Restrict everything', 'paid-member-subscriptions' ); ?></span>
        </label>
    </div>

    <p class="description"><?php _e( 'Restrict comments if they are enabled.', 'paid-member-subscriptions' ); ?></p>
</div>

<!-- Restricted Posts Preview -->
<div class="pms-form-field-wrapper pms-form-field-rpp">
    <label class="pms-form-field-label" for="restricted-posts-preview"><?php _e( 'Restricted Posts Preview', 'paid-member-subscriptions' ) ?></label>

    <div>
        <label>
            <input type="radio" name="pms_content_restriction_settings[restricted_post_preview][option]" value="none" <?php echo ( !isset( $this->options['restricted_post_preview']['option'] ) || $this->options['restricted_post_preview']['option'] == 'none' ? 'checked' : '' ); ?> />
            <span><?php _e( 'None', 'paid-member-subscriptions' ); ?></span>
        </label>
    </div>
    <div>
        <label>
            <input type="radio" name="pms_content_restriction_settings[restricted_post_preview][option]" value="trim-content" <?php echo ( isset( $this->options['restricted_post_preview']['option'] ) && $this->options['restricted_post_preview']['option'] == 'trim-content' ? 'checked' : '' ); ?> />
            <span>
                <?php echo sprintf( __( 'Show the first %s words of the post\'s content', 'paid-member-subscriptions' ), '<input name="pms_content_restriction_settings[restricted_post_preview][trim_content_length]" type="text" value="' . ( isset( $this->options['restricted_post_preview']['trim_content_length'] ) ? esc_attr( $this->options['restricted_post_preview']['trim_content_length'] ) : 20 ) . '" style="width: 50px;" />' ); ?>
            </span>
        </label>
    </div>
    <div>
        <label>
            <input type="radio" name="pms_content_restriction_settings[restricted_post_preview][option]" value="more-tag" <?php echo ( isset( $this->options['restricted_post_preview']['option'] ) && $this->options['restricted_post_preview']['option'] == 'more-tag' ? 'checked' : '' ); ?> />
            <span><?php _e( 'Show the content before the "more" tag', 'paid-member-subscriptions' ); ?></span>
        </label>
    </div>

    <p class="description"><?php _e( 'Show a portion of the restricted post to logged-out users or users that are not subscribed to a plan.', 'paid-member-subscriptions' ); ?></p>
</div>
