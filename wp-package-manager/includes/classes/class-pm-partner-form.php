<?php
// includes/classes/class-pm-partner-form.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'PM_Partner_Form' ) ) :

/**
 * Class PM_Partner_Form
 *
 * Renders and processes the Partner (Συνεργάτης) form.
 */
class PM_Partner_Form {

    const SUBMISSION_TYPE = 'partner';
    const AJAX_ACTION     = 'pm_submit_partner';

    /** Field map is populated at runtime */
    protected $fields = [];

    /* --------------------------------------------------------------------- */
    /*  Constructor                                                          */
    /* --------------------------------------------------------------------- */
    public function __construct() {

        // Build field map (no __() in property default!)
        $this->fields = $this->get_default_fields();

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, [ $this, 'handle_submission' ] );
        add_action( 'wp_ajax_'        . self::AJAX_ACTION, [ $this, 'handle_submission' ] );
    }

    /**
     * Return default field definitions.
     */
    protected function get_default_fields() {
        return [
            'partner_name' => [
                'type'        => 'text',
                'label'       => __( 'Επωνυμία συνεργάτη', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ ΠΑΠΑΔΟΠΟΥΛΟΣ ΑΕ', 'wc-pm' ),
            ],
            'partner_afm'  => [
                'type'        => 'text',
                'label'       => __( 'ΑΦΜ συνεργάτη', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ 999123456', 'wc-pm' ),
                'validate'    => 'afm',
            ],
            'partner_email' => [
                'type'        => 'email',
                'label'       => __( 'Email συνεργάτη', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ papadopoulos@gmail.com', 'wc-pm' ),
            ],
            'partner_phone' => [
                'type'        => 'tel',
                'label'       => __( 'Τηλέφωνο συνεργάτη', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ 6991234567', 'wc-pm' ),
            ],
            'company_name' => [
                'type'        => 'text',
                'label'       => __( 'Επωνυμία επιχείρησης', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ Ελιά και Διόσμος', 'wc-pm' ),
            ],
            'company_afm'  => [
                'type'        => 'text',
                'label'       => __( 'ΑΦΜ επιχείρησης', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ 999123456', 'wc-pm' ),
                'validate'    => 'afm',
            ],
            'company_type' => [
                'type'     => 'select',
                'label'    => __( 'Είδος επιχείρησης', 'wc-pm' ),
                'required' => false,
                'choices'  => [
                    ''            => __( 'Επιλέξτε', 'wc-pm' ),
                    'Εστιατόριο'  => 'Εστιατόριο',
                    'Καφέ'        => 'Καφέ',
                    'Ξενοδοχείο'  => 'Ξενοδοχείο',
                    'Beach Bar'   => 'Beach Bar',
                    'Πιτσαρία'    => 'Πιτσαρία',
                    'Fast Food'   => 'Fast Food',
                    'Bar - Club'  => 'Bar - Club',
                    'Άλλο'        => 'Άλλο',
                ],
            ],
            'company_city' => [
                'type'        => 'text',
                'label'       => __( 'Έδρα επιχείρησης', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ Αθήνα', 'wc-pm' ),
            ],
            'contact_fname' => [
                'type'        => 'text',
                'label'       => __( 'Όνομα υπεύθυνου', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ Ιωάννης', 'wc-pm' ),
            ],
            'contact_lname' => [
                'type'        => 'text',
                'label'       => __( 'Επώνυμο υπεύθυνου', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ Παπαδόπουλος', 'wc-pm' ),
            ],
            'contact_phone' => [
                'type'        => 'tel',
                'label'       => __( 'Κινητό υπεύθυνου', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ 6991234567', 'wc-pm' ),
            ],
            'contact_pref' => [
                'type'     => 'radio',
                'label'    => __( 'Τρόπος λήψης μηνυμάτων', 'wc-pm' ),
                'required' => false,
                'choices'  => [
                    'SMS'      => 'SMS',
                    'Viber'    => 'Viber',
                    'WhatsApp' => 'WhatsApp',
                ],
            ],
            'contact_email' => [
                'type'        => 'email',
                'label'       => __( 'Email υπεύθυνου', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ papadopoulos@gmail.com', 'wc-pm' ),
            ],
            'consent' => [
                'type'     => 'checkbox',
                'label'    => __( 'Συμφωνώ ότι…', 'wc-pm' ),
                'required' => true,
            ],
            'hp_1' => [ 'type' => 'hidden', 'required' => false ],
            'hp_2' => [ 'type' => 'hidden', 'required' => false ],
        ];
    }

    /* --------------------------------------------------------------------- */
    /*  Assets                                                               */
    /* --------------------------------------------------------------------- */
    public function enqueue_assets() {
        wp_enqueue_style( 'wc-pm-frontend', WC_PM_URL . 'assets/css/frontend.css', [], WC_PM_VERSION );
        wp_enqueue_script( 'wc-pm-forms',   WC_PM_URL . 'assets/js/frontend-forms.js', [], WC_PM_VERSION, true );
        wp_localize_script( 'wc-pm-forms', 'PM_Partner_Ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'action'   => self::AJAX_ACTION,
            'nonce'    => wp_create_nonce( 'pm_partner_nonce' ),
        ] );
    }

    /* --------------------------------------------------------------------- */
    /*  Shortcode renderer                                                   */
    /* --------------------------------------------------------------------- */
    public function render_partner_form( $atts ) {
        ob_start();
        include WC_PM_PATH . 'templates/frontend/form-partner.php';
        return ob_get_clean();
    }

    /* --------------------------------------------------------------------- */
    /*  AJAX submission                                                      */
    /* --------------------------------------------------------------------- */
    public function handle_submission() {
        check_ajax_referer( 'pm_partner_nonce', 'nonce' );

        $data = [];
        foreach ( $this->fields as $key => $cfg ) {
            $val = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';

            if ( $cfg['required'] && '' === $val ) {
                wp_send_json_error( [ 'field' => $key, 'message' => __( 'Required field', 'wc-pm' ) ] );
            }

            if ( ! empty( $cfg['validate'] ) && 'afm' === $cfg['validate'] && ! pm_validate_afm( $val ) ) {
                wp_send_json_error( [ 'field' => $key, 'message' => __( 'Invalid ΑΦΜ', 'wc-pm' ) ] );
            }

            $data[ $key ] = $val;
        }

        if ( ! empty( $_POST['hp_1'] ) || ! empty( $_POST['hp_2'] ) ) {
            wp_send_json_error( [ 'message' => 'Spam detected' ], 400 );
        }

        $submission_id = pm_create_submission( self::SUBMISSION_TYPE, $data );
        do_action( 'pm_after_partner_form_submission', $data, $submission_id );

        wp_send_json_success( [ 'submission_id' => $submission_id ] );
    }
}

/* Instantiate immediately */
new PM_Partner_Form();

endif; // guard
