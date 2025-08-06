<?php
// includes/classes/class-pm-payment-form.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'PM_Payment_Form' ) ) :

/**
 * Class PM_Payment_Form
 *
 * Renders and processes the standalone Payment form.
 */
class PM_Payment_Form {

    const SUBMISSION_TYPE = 'payment';
    const AJAX_ACTION     = 'pm_submit_payment';

    /** Dynamic field map (populated in constructor) */
    protected $fields = [];

    /*--------------------------------------------------------------------*/
    /*  Constructor                                                       */
    /*--------------------------------------------------------------------*/
    public function __construct() {

        // Build field definitions (no runtime calls in property default!)
        $this->fields = $this->get_default_fields();

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp',                 [ $this, 'load_package_choices' ] );

        add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, [ $this, 'handle_submission' ] );
        add_action( 'wp_ajax_'        . self::AJAX_ACTION, [ $this, 'handle_submission' ] );
    }

    /**
     * Default field definitions
     *
     * @return array
     */
    protected function get_default_fields() {
        return [
            'payment_email'  => [
                'type'        => 'email',
                'label'       => __( 'Email πληρωμής', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ papadopoulos@gmail.com', 'wc-pm' ),
            ],
            'payment_amount' => [
                'type'        => 'number',
                'label'       => __( 'Ποσό', 'wc-pm' ),
                'required'    => true,
                'placeholder' => '0.00',
                'attributes'  => [ 'step' => '0.01', 'min' => '0' ],
            ],
            'payment_period' => [
                'type'     => 'select',
                'label'    => __( 'Περίοδος', 'wc-pm' ),
                'required' => true,
                'choices'  => [
                    'μηνιαία' => __( 'Μηνιαία', 'wc-pm' ),
                    'ετήσια'  => __( 'Ετήσια',  'wc-pm' ),
                ],
            ],
            'payment_package' => [
                'type'     => 'select',
                'label'    => __( 'Πακέτο', 'wc-pm' ),
                'required' => true,
                'choices'  => [], // filled in load_package_choices()
            ],
            'consent' => [
                'type'     => 'checkbox',
                'label'    => __( 'Αποδέχομαι όρους πληρωμής', 'wc-pm' ),
                'required' => true,
            ],
            'hp_1' => [ 'type' => 'hidden', 'required' => false ],
            'hp_2' => [ 'type' => 'hidden', 'required' => false ],
        ];
    }

    /*--------------------------------------------------------------------*/
    /*  Assets                                                            */
    /*--------------------------------------------------------------------*/
    public function enqueue_assets() {
        wp_enqueue_style( 'wc-pm-frontend', WC_PM_URL . 'assets/css/frontend.css', [], WC_PM_VERSION );
        wp_enqueue_script( 'wc-pm-forms',   WC_PM_URL . 'assets/js/frontend-forms.js', [], WC_PM_VERSION, true );
        wp_localize_script( 'wc-pm-forms', 'PM_Payment_Ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'action'   => self::AJAX_ACTION,
            'nonce'    => wp_create_nonce( 'pm_payment_nonce' ),
        ] );
    }

    /*--------------------------------------------------------------------*/
    /*  Populate package choices                                          */
    /*--------------------------------------------------------------------*/
    public function load_package_choices() {
        global $wpdb;
        $results = $wpdb->get_results(
            "SELECT id, title FROM {$wpdb->prefix}pm_packages WHERE status='published' ORDER BY title",
            ARRAY_A
        );
        foreach ( $results as $row ) {
            $this->fields['payment_package']['choices'][ $row['id'] ] = $row['title'];
        }
    }

    /*--------------------------------------------------------------------*/
    /*  Shortcode renderer                                                */
    /*--------------------------------------------------------------------*/
    public function render_payment_form( $atts ) {
        ob_start();
        include WC_PM_PATH . 'templates/frontend/form-payment.php';
        return ob_get_clean();
    }

    /*--------------------------------------------------------------------*/
    /*  AJAX submission handler                                           */
    /*--------------------------------------------------------------------*/
    public function handle_submission() {

        check_ajax_referer( 'pm_payment_nonce', 'nonce' );

        $data = [];
        foreach ( $this->fields as $key => $cfg ) {

            $raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';

            if ( $cfg['required'] && '' === trim( $raw ) ) {
                wp_send_json_error( [ 'field' => $key, 'message' => __( 'Απαιτούμενο πεδίο', 'wc-pm' ) ] );
            }

            /* Sanitise --------------------------------------------------*/
            switch ( $cfg['type'] ) {
                case 'email':
                    $val = sanitize_email( $raw );
                    if ( ! is_email( $val ) ) {
                        wp_send_json_error( [ 'field' => $key, 'message' => __( 'Μη έγκυρο email', 'wc-pm' ) ] );
                    }
                    break;
                case 'number':
                    $val = floatval( $raw );
                    break;
                default:
                    $val = sanitize_text_field( $raw );
            }
            $data[ $key ] = $val;
        }

        /* Validate package --------------------------------------------*/
        $pkg_id = intval( $data['payment_package'] );
        if ( ! isset( $this->fields['payment_package']['choices'][ $pkg_id ] ) ) {
            wp_send_json_error( [ 'field' => 'payment_package', 'message' => __( 'Μη έγκυρο πακέτο', 'wc-pm' ) ] );
        }

        /* Honeypot ----------------------------------------------------*/
        if ( ! empty( $_POST['hp_1'] ) || ! empty( $_POST['hp_2'] ) ) {
            wp_send_json_error( [ 'message' => 'Spam detected' ], 400 );
        }

        /* Store + hook ------------------------------------------------*/
        $submission_id = pm_create_submission( self::SUBMISSION_TYPE, $data );
        do_action( 'pm_after_payment_form_submission', $data, $submission_id );

        wp_send_json_success( [ 'submission_id' => $submission_id ] );
    }
}

/* Instantiate */
new PM_Payment_Form();

endif; // class guard
