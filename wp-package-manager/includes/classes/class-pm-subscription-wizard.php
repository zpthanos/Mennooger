<?php
// includes/classes/class-pm-subscription-wizard.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Subscription_Wizard
 *
 * Renders and processes a multi-step subscription wizard on the front end.
 */
class PM_Subscription_Wizard {

    /** Steps configuration */
    protected $steps = [];

    /** AJAX action hook */
    const AJAX_ACTION = 'pm_submit_subscription';

    public function __construct() {
        $this->define_steps();

        // Shortcode registration is handled by PM_Shortcodes

        // Enqueue assets on front end
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // AJAX handler
        add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, [ $this, 'handle_wizard_submit' ] );
        add_action( 'wp_ajax_'        . self::AJAX_ACTION, [ $this, 'handle_wizard_submit' ] );
    }

    /**
     * Define the wizard steps, titles, descriptions, and field keys.
     */
    protected function define_steps() {
        $this->steps = [
            'business' => [
                'title'       => __( 'ΣΤΟΙΧΕΙΑ ΕΠΙΧΕΙΡΗΣΗΣ', 'wc-pm' ),
                'description' => '',
                'fields'      => [ 'company_name','company_slogan','company_type','company_afm','referrer_source' ],
            ],
            'address' => [
                'title'       => __( 'ΔΙΕΥΘΥΝΣΗ ΕΠΙΧΕΙΡΗΣΗΣ', 'wc-pm' ),
                'description' => __( 'Συμπληρώστε τη διεύθυνση για αποστολή υλικού.', 'wc-pm' ),
                'fields'      => [ 'address','area','postcode','city','region','company_phone' ],
            ],
            'online' => [
                'title'       => __( 'ΔΙΑΔΙΚΤΥΑΚΗ ΠΑΡΟΥΣΙΑ', 'wc-pm' ),
                'description' => '',
                'fields'      => [ 'company_email','website','contact_fname','contact_lname','contact_phone','contact_pref' ],
            ],
            'languages' => [
                'title'       => __( 'ΕΠΙΘΥΜΗΤΕΣ ΓΛΩΣΣΕΣ', 'wc-pm' ),
                'description' => __( 'Επιλέξτε γλώσσες εμφάνισης καταλόγου.', 'wc-pm' ),
                'fields'      => [ 'languages' ],
            ],
            'files' => [
                'title'       => __( 'ΑΡΧΕΙΑ ΚΑΤΑΛΟΓΟΥ & ΣΥΜΦΩΝΙΑ', 'wc-pm' ),
                'description' => __( 'Φορτώστε τα αρχεία και αποδεχτείτε τους όρους.', 'wc-pm' ),
                'fields'      => array_merge(
                    array_map( function( $i ) { return "catalog_file_$i"; }, range(1,12) ),
                    [ 'consent' ]
                ),
            ],
        ];
    }

    /**
     * Enqueue front-end wizard CSS/JS.
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'wc-pm-wizard', WC_PM_URL . 'assets/css/frontend-wizard.css', [], WC_PM_VERSION );
        wp_enqueue_script( 'wc-pm-wizard', WC_PM_URL . 'assets/js/frontend-wizard.js', [ 'jquery' ], WC_PM_VERSION, true );
        wp_localize_script( 'wc-pm-wizard', 'PM_Subscription_Ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'action'   => self::AJAX_ACTION,
            'nonce'    => wp_create_nonce( 'pm_wizard_nonce' ),
        ] );
    }

    /**
     * Shortcode callback: render the full wizard HTML.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML
     */
    public function render_subscription_wizard( $atts ) {
        ob_start();
        include WC_PM_PATH . 'templates/frontend/form-wizard.php';
        return ob_get_clean();
    }

    /**
     * Handle AJAX submission of the wizard.
     */
    public function handle_wizard_submit() {
        check_ajax_referer( 'pm_wizard_nonce', 'nonce' );

        // Optional CAPTCHA check here...

        // Collect and sanitize all step fields
        $data = [];
        foreach ( $this->steps as $step ) {
            foreach ( $step['fields'] as $field ) {
                if ( isset( $_POST[ $field ] ) ) {
                    $data[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
                }
            }
        }

        // Validate required fields, AFM, file uploads, checkbox consent...
        // On error: wp_send_json_error( [ 'field' => $field ] );

        // Create subscription record
        global $wpdb;
        $now = current_time( 'mysql' );
        $inserted = $wpdb->insert(
            $wpdb->prefix . 'pm_user_subscriptions',
            [
                'user_id'          => get_current_user_id() ?: 0,
                'subscription_type'=> $data['subscription_type'] ?? 'μηνιαία',
                'start_date'       => $data['start_date'] ?? $now,
                'end_date'         => $data['end_date'] ?? $now,
                'status'           => 'pending',
                'label'            => $data['subscription_type'] ?? '',
                'afm'              => $data['company_afm'],
                'meta_data'        => wp_json_encode( $data ),
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [ '%d','%s','%s','%s','%s','%s','%s','%s','%s' ]
        );

        if ( ! $inserted ) {
            wp_send_json_error( __( 'Σφάλμα δημιουργίας συνδρομής.', 'wc-pm' ) );
        }

        $subscription_id = intval( $wpdb->insert_id );

        // Optionally create a pending payment submission...
        // pm_create_submission( 'subscription', $data, $subscription_id );

        // Fire hook
        do_action( 'pm_after_subscription_complete', $subscription_id, $data );

        wp_send_json_success( [ 'subscription_id' => $subscription_id ] );
    }
}

