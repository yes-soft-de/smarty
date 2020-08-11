<?php
/**
 * Export Class
 *
 * This is the base class for all export methods. Each data export type (customers, payments, etc) extend this class
 *
 * @package     paid-member-subscriptions
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Cristian Antohe. Initial code extracted from Easy Digital Downloads by Pippin Williamson.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.7.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * PMS_Export Class
 *
 * @since 1.7.6
 */
class PMS_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.7.6
	 */
	public $export_type = 'default';

	/**
	 * Can we export?
	 *
	 * @since 1.7.6
	 * @return bool Whether we can export or not
	 */
	public function can_export() {
		return (bool) apply_filters( 'pms_export_capability', current_user_can( 'manage_options' ) );
	}
	/**
	 * Is function disabled
	 *
	 * @since 1.7.6
	 * @var string
	 * @return bool Whether we can export or not
	 */
	public function is_func_disabled( $function ) {
		$disabled = explode( ',',  ini_get( 'disable_functions' ) );

		return in_array( $function, $disabled );
	}
	/**
	 * Set the export headers
	 *
	 * @since 1.7.6
	 * @return void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! $this->is_func_disabled( 'set_time_limit' ) )
			set_time_limit( 0 );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=pms-export-' . $this->export_type . '-' . date( 'm-d-Y' ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Set the CSV columns
	 *
	 * @since 1.7.6
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'id'   => __( 'ID',   'paid-member-subscriptions' ),
			'date' => __( 'Date', 'paid-member-subscriptions' )
		);
		return $cols;
	}

	/**
	 * Retrieve the CSV columns
	 *
	 * @since 1.7.6
	 * @return array $cols Array of the columns
	 */
	public function get_csv_cols() {
		$cols = $this->csv_cols();
		return apply_filters( 'pms_export_csv_cols_' . $this->export_type, $cols );
	}

	/**
	 * Output the CSV columns
	 *
	 * @since 1.7.6
	 * @uses PMS_Export::get_csv_cols()
	 * @return void
	 */
	public function csv_cols_out() {
		$cols = $this->get_csv_cols();
		$i = 1;
		foreach( $cols as $col_id => $column ) {
			echo '"' . addslashes( $column ) . '"';
			echo $i == count( $cols ) ? '' : ',';
			$i++;
		}
		echo "\r\n";
	}

	/**
	 * Get the data being exported
	 *
	 * @since 1.7.6
	 * @return array $data Data for Export
	 */
	public function get_data() {
		// Just a sample data array
		$data = array(
			0 => array(
				'id'   => '',
				'data' => date( 'F j, Y' )
			),
			1 => array(
				'id'   => '',
				'data' => date( 'F j, Y' )
			)
		);

		return $data;
	}

	/**
	 * Output the CSV rows
	 *
	 * @since 1.7.6
	 * @return void
	 */
	public function csv_rows_out() {
		$data = $this->get_data();

		$cols = $this->get_csv_cols();

		// Output each row
		foreach ( $data as $row ) {
			$i = 1;
			foreach ( $row as $col_id => $column ) {
				// Make sure the column is valid
				if ( array_key_exists( $col_id, $cols ) ) {
					echo '"' . addslashes( $column ) . '"';
					echo $i == count( $cols ) ? '' : ',';
					$i++;
				}
			}
			echo "\r\n";
		}
	}

	/**
	 * Perform the export
	 *
	 * @since 1.7.6
	 * @uses PMS_Export::can_export()
	 * @uses PMS_Export::headers()
	 * @uses PMS_Export::csv_cols_out()
	 * @uses PMS_Export::csv_rows_out()
	 * @return void
	 */
	public function export() {
		if ( ! $this->can_export() )
			wp_die( __( 'You do not have permission to export data.', 'paid-member-subscriptions' ), __( 'Error', 'paid-member-subscriptions' ), array( 'response' => 403 ) );

		// Set headers
		$this->headers();

		// Output CSV columns (headers)
		$this->csv_cols_out();

		// Output CSV rows
		$this->csv_rows_out();

		wp_die( '', '', array( 'response' => 400 ));
	}
}
