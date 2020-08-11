<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends core PMS_Submenu_Page base class to create and add custom functionality
 * for the reports section in the admin section
 *
 */
Class PMS_Submenu_Page_Reports extends PMS_Submenu_Page {

    /*
     * The start date to filter results
     *
     * @var string
     *
     */
    private $start_date;


    /*
     * The end date to filter results
     *
     * @var string
     *
     */
    private $end_date;


    /*
     * Array of payments retrieved from the database given the user filters
     *
     * @var array
     *
     */
    private $queried_payments = array();


    /*
     * Array with the formatted results ready for chart.js usage
     *
     * @var array
     *
     */
    private $results = array();


    /*
     * Method that initializes the class
     *
     */
    public function init() {

        // Enqueue admin scripts
        add_action( 'pms_submenu_page_enqueue_admin_scripts_before_' . $this->menu_slug, array( $this, 'admin_scripts' ) );

        // Hook the output method to the parent's class action for output instead of overwriting the
        // output method
        add_action( 'pms_output_content_submenu_page_' . $this->menu_slug, array( $this, 'output' ) );

        // Process different actions within the page
        add_action( 'init', array( $this, 'process_data' ) );

        // Output filters
        add_action( 'pms_reports_filters', array( $this, 'output_filters' ) );

        // Period reports table
        add_action( 'pms_reports_page_bottom', array( $this, 'output_reports_table' ) );

        add_action( 'admin_print_footer_scripts', array( $this, 'output_chart_js_data' ) );

    }


    /*
     * Method to enqueue admin scripts
     *
     */
    public function admin_scripts() {

        wp_enqueue_script( 'pms-chart-js', PMS_PLUGIN_DIR_URL . 'assets/js/admin/libs/chart/chart.js' );

    }


    /*
     * Method that processes data on reports admin pages
     *
     */
    public function process_data() {

        // Get current actions
        $action = !empty( $_REQUEST['pms-action'] ) ? $_REQUEST['pms-action'] : '';

        // Get default results if no filters are applied by the user
        if( empty($action) && !empty( $_REQUEST['page'] ) && $_REQUEST['page'] == 'pms-reports-page' ) {

            $this->queried_payments = $this->get_filtered_payments();

            $results = $this->prepare_payments_for_output( $this->queried_payments );

        } else {

            // Verify correct nonce
            if( !isset( $_REQUEST['_wpnonce'] ) || !wp_verify_nonce( $_REQUEST['_wpnonce'], 'pms_reports_nonce' ) )
                return;

            // Filtering results
            if( $action == 'filter_results' ) {

                $this->queried_payments = $this->get_filtered_payments();

                $results = $this->prepare_payments_for_output( $this->queried_payments );

            }

        }

        if( !empty( $results ) )
            $this->results = $results;

    }


    /*
     * Return an array of payments payments depending on the user's input filters
     *
     * @return array
     *
     */
    private function get_filtered_payments() {

        if( empty( $_REQUEST['pms-filter-time'] ) || $_REQUEST['pms-filter-time'] == 'current_month' )
            $date = date("Y-m");

        else
            $date = sanitize_text_field( $_REQUEST['pms-filter-time'] );

        $date_time        = new DateTime( $date );
        $month_total_days = $date_time->format( 't' );

        $this->start_date = $date . '-01';
        $this->end_date   = $date . '-' . $month_total_days . ' 23:59:59';

        $args = apply_filters( 'pms_reports_get_filtered_payments_args', array( 'status' => 'completed', 'date' => array( $this->start_date, $this->end_date ), 'order' => 'ASC', 'number' => '-1' ) );

        $payments = pms_get_payments( $args );

        return $payments;

    }


    /*
     * Get filtered results by date
     *
     * @param $start_date - has format Y-m-d
     * @param $end_date   - has format Y-m-d
     *
     * @return array
     *
     */
    private function prepare_payments_for_output( $payments = array() ) {

        $results = array();

        $first_day = new DateTime( $this->start_date );
        $first_day = $first_day->format('j');

        $last_day  = new DateTime( $this->end_date );
        $last_day  = $last_day->format('j');

        for( $i = $first_day; $i <= $last_day; $i++ ) {
            if( !isset( $results[$i] ) )
                $results[$i] = array( 'earnings' => 0, 'payments' => 0 );
        }

        if( !empty( $payments ) ) {
            foreach( $payments as $payment ) {
                $payment_date = new DateTime( $payment->date );

                $results[ $payment_date->format('j') ]['earnings'] += $payment->amount;
                $results[ $payment_date->format('j') ]['payments'] += 1;
            }
        }

        return apply_filters( 'pms_reports_get_filtered_results', $results, $this->start_date, $this->end_date );

    }


    /*
     * Method to output content in the custom page
     *
     */
    public function output() {
        $active_tab = 'pms-reports-page';
        include_once 'views/view-page-reports.php';

    }


    /*
     * Outputs the input filter's the admin has at his disposal
     *
     */
    public function output_filters() {

        echo '<select name="pms-filter-time">';

            echo '<option value="current_month">' . __( 'Current month', 'paid-member-subscriptions' ) . '</option>';

            for ($i = 1; $i <= 12; $i++) {
                $month = date("Y-m", strtotime( date( 'Y-m-01' ) . " -$i months"));
                echo '<option value="' . $month . '" ' . ( !empty( $_GET['pms-filter-time'] ) ? selected( $month, $_GET['pms-filter-time'], false ) : '' ) . '>' . date( 'F', strtotime( $month ) ) . ' ' . date( 'Y', strtotime( $month ) ) . '</option>';
            }

        echo '</select>';

    }


    /*
     * Outputs a summary with the payments and earnings for the selected period
     *
     */
    public function output_reports_table() {

        $payments_count  = count( $this->queried_payments );
        $payments_amount = 0;

        if( !empty( $this->queried_payments ) ) {
            foreach( $this->queried_payments as $payment )
                $payments_amount += $payment->amount;
        }

        echo '<div class="postbox">';
            echo '<div class="inside">';
                echo '<h4>' . __( 'Summary', 'paid-member-subscriptions' ) . '</h4>';
                echo '<p>' . __( 'Total earnings for the selected period: ', 'paid-member-subscriptions' ) . '<strong>' . pms_get_currency_symbol( pms_get_active_currency() ) . $payments_amount . '</strong>' . '</p>';
                echo '<p>' . __( 'Total number of payments for the selected period: ', 'paid-member-subscriptions' ) . '<strong>' . $payments_count . '</strong>' . '</p>';
            echo '</div>';
        echo '</div>';

    }


    /*
     * Output the javascript data as variables
     *
     */
    public function output_chart_js_data() {

        if( empty( $this->results ) )
            return;

        $results = $this->results;

        // Start ouput
        echo '<script type="text/javascript">';

            // Echo the labels of the chart
            $chart_labels_js_array = $data_set_earnings_js_array = $data_set_payments_js_array = '[';

            foreach( $results as $key => $details ) {

                $chart_labels_js_array      .= $key . ',';
                $data_set_earnings_js_array .= $details['earnings'] . ', ';
                $data_set_payments_js_array .= $details['payments'] . ', ';

            }
            $chart_labels_js_array      .= ']';
            $data_set_earnings_js_array .= ']';
            $data_set_payments_js_array .= ']';



            echo 'var pms_currency = "' . html_entity_decode(pms_get_currency_symbol( pms_get_active_currency() )) . '";';

            echo 'var pms_chart_labels = ' . $chart_labels_js_array . ';';
            echo 'var pms_chart_earnings = ' . $data_set_earnings_js_array . ';';
            echo 'var pms_chart_payments = ' . $data_set_payments_js_array . ';';

        echo '</script>';

    }

}

global $pms_submenu_page_reports;

$pms_submenu_page_reports = new PMS_Submenu_Page_Reports( 'paid-member-subscriptions', __( 'Reports', 'paid-member-subscriptions' ), __( 'Reports', 'paid-member-subscriptions' ), 'manage_options', 'pms-reports-page', 20 );
$pms_submenu_page_reports->init();
