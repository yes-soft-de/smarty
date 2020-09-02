<?php

if ( ! defined( 'ABSPATH' ) ) 
	exit;


class VibeBP_Field_Type_Location extends BP_XProfile_Field_Type {

	public function __construct() {

		parent::__construct();

		$this->name     = _x( 'Location', 'xprofile field type', 'vibebp' );
		$this->category = _x( 'VibeBP', 'xprofile field type category', 'vibebp' );

		$this->set_format( '/^.+$/', 'replace' );
		do_action( 'bp_xprofile_field_type_location', $this );
	}

	
	public function edit_field_html( array $raw_properties = array() ) {

        // reset user_id.
	    if ( isset( $raw_properties['user_id'] ) ) {
			unset( $raw_properties['user_id'] );
		}

		$html = $this->get_edit_field_html_elements( array_merge(
			array(
				'type'  => 'location',
				'value' => bp_get_the_profile_field_edit_value(),
                'class' => 'vibebp-location'
			),
			$raw_properties
		) );
		?>

        <legend id="<?php bp_the_profile_field_input_name(); ?>-1">
			<?php bp_the_profile_field_name(); ?>
			<?php bp_the_profile_field_required_label(); ?>
        </legend>

		<?php do_action( bp_get_the_profile_field_errors_action() ); ?>

        <input <?php echo $html; ?>>

		<?php if ( bp_get_the_profile_field_description() ) : ?>
            <p class="description"
               id="<?php bp_the_profile_field_input_name(); ?>-3"><?php bp_the_profile_field_description(); ?></p>
		<?php endif; ?>

		<?php
	}

	/**
     * Admin field list html.
     *
	 * @param array $raw_properties properties.
	 */
	public function admin_field_html( array $raw_properties = array() ) {

	    $html = $this->get_edit_field_html_elements( array_merge(
			array( 'type' => 'text', 'class'=>'vibebp_location' ),
			$raw_properties
		) );
		?>

        <input <?php echo $html; ?>>
		<?php
	}

}
