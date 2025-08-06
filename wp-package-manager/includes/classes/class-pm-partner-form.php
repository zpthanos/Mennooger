<?php
// includes/classes/class-pm-interest-form.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Interest_Form
 *
 * Renders and processes the “Interest” form.
 */
class PM_Interest_Form {

    /** Submission type */
    const SUBMISSION_TYPE = 'interest';

    /** AJAX action */
    const AJAX_ACTION = 'pm_submit_interest';

    /** Field definitions – populated in constructor */
    protected $fields = [];

    /**
     * PM_Interest_Form constructor.
     */
    public function __construct() {
        // Move field initialisation here (so no $this usage in a property)
        $this->fields = $this->get_default_fields();

        // Asset enqueue/localise
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // AJAX handlers
        add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, [ $this, 'handle_submission' ] );
        add_action( 'wp_ajax_'        . self::AJAX_ACTION, [ $this, 'handle_submission' ] );
    }

    /**
     * Default field map – mirrors Partner-form style.
     *
     * @return array
     */
    protected function get_default_fields() {
        return [
            'full_name'   => [
                'type'        => 'text',
                'label'       => __( 'Ονοματεπώνυμο', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ Ιωάννης Παπαδόπουλος', 'wc-pm' ),
            ],
            'email'       => [
                'type'        => 'email',
                'label'       => __( 'Email', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'π.χ example@email.com', 'wc-pm' ),
            ],
            'phone'       => [
                'type'        => 'tel',
                'label'       => __( 'Τηλέφωνο', 'wc-pm' ),
                'required'    => false,
                'placeholder' => __( 'π.χ 6991234567', 'wc-pm' ),
            ],
            'message'     => [
                'type'        => 'textarea',
                'label'       => __( 'Μήνυμα Ενδιαφέροντος', 'wc-pm' ),
                'required'    => true,
                'placeholder' => __( 'Γράψτε το μήνυμά σας…', 'wc-pm' ),
            ],
            'consent'     => [
                'type'     => 'checkbox',
                'label'    => __( 'Συμφωνώ με τους όρους', 'wc-pm' ),
                'required' => true,
            ],
            'hp_1'        => [
                'type'     => 'hidden',
                'required' => false,
            ],
        ];
    }

    /** Enqueue front-end assets */
    public function enqueue_assets() {
        wp_enqueue_style( 'wc-pm-frontend', WC_PM_URL . 'assets/css/frontend.css', [], WC_PM_VERSION );
        wp_enqueue_script( 'wc-pm-forms',   WC_PM_URL . 'assets/js/frontend-forms.js', [], WC_PM_VERSION, true );
        wp_localize_script( 'wc-pm-forms', 'PM_Interest_Ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'action'   => self::AJAX_ACTION,
            'nonce'    => wp_create_nonce( 'pm_interest_nonce' ),
        ] );
    }

    /** Shortcode renderer */
    public function render_interest_form( $atts ) {
        ob_start();
        include WC_PM_PATH . 'templates/frontend/form-interest.php';
        return ob_get_clean();
    }

    /** AJAX submission handler */
    public function handle_submission() {
        check_ajax_referer( 'pm_interest_nonce', 'nonce' );

        $data = [];
        foreach ( $this->fields as $key => $cfg ) {
            $val = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';

            if ( $cfg['required'] && empty( $val ) ) {
                wp_send_json_error( [ 'field' => $key, 'message' => __( 'Required field', 'wc-pm' ) ] );
            }
            $data[ $key ] = $val;
        }

        // Honeypot
        if ( ! empty( $_POST['hp_1'] ) ) {
            wp_send_json_error( [ 'message' => 'Spam detected' ], 400 );
        }

        // Store submission
        $submission_id = pm_create_submission( self::SUBMISSION_TYPE, $data );

        do_action( 'pm_after_interest_form_submission', $data, $submission_id );

        wp_send_json_success( [ 'submission_id' => $submission_id ] );
    }
}

// Instantiate immediately
new PM_Interest_Form();
