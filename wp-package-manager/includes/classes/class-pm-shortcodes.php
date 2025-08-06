<?php
// includes/classes/class-pm-shortcodes.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Shortcodes
 *
 * Dynamically registers all front-end shortcodes based on admin settings.
 */
class PM_Shortcodes {

    /** Instances of form/rendering classes */
    protected $package_crud;
    protected $subscription_wizard;
    protected $form_renderer;
    protected $partner_form;
    protected $interest_form;
    protected $payment_form;
    protected $afm_lookup;

    public function __construct() {
        // Instantiate renderers
        $this->package_crud         = new PM_Package_CRUD();
        $this->subscription_wizard  = new PM_Subscription_Wizard();
        $this->form_renderer        = new PM_Form_Renderer();
        $this->partner_form         = new PM_Partner_Form();
        $this->interest_form        = new PM_Interest_Form();
        $this->payment_form         = new PM_Payment_Form();
        $this->afm_lookup           = new PM_AFM_Lookup();

        // Register the shortcodes
        add_action( 'init', [ $this, 'register_shortcodes' ] );
    }

    /**
     * Read shortcode slugs from settings and register them
     */
    public function register_shortcodes() {
        $mapping = [
            // option_key         default_slug             callback
            [ 'pm_sc_listing',     'pm_listing',            [ $this->package_crud,        'render_package_archive' ] ],
            [ 'pm_sc_subscription','pm_subscription',       [ $this->subscription_wizard, 'render_subscription_wizard' ] ],
            [ 'pm_sc_single',      'pm_single_package',     [ $this->form_renderer,       'render_single_package' ] ],
            [ 'pm_sc_partner',     'pm_partner',            [ $this->partner_form,        'render_partner_form' ] ],
            [ 'pm_sc_interest',    'pm_interest',           [ $this->interest_form,       'render_interest_form' ] ],
            [ 'pm_sc_payment',     'pm_payment',            [ $this->payment_form,        'render_payment_form' ] ],
            [ 'pm_sc_afm',         'pm_afm_lookup',         [ $this->afm_lookup,          'render_afm_lookup_form' ] ],
        ];

        foreach ( $mapping as list( $opt_key, $default, $callback ) ) {
            $slug = get_option( $opt_key, $default );
            // Validate slug format: only letters, numbers, underscores, hyphens
            if ( preg_match( '/^[a-zA-Z0-9_-]+$/', $slug ) ) {
                add_shortcode( $slug, $callback );
            }
        }
    }
}

// Instantiate to hook into init()
new PM_Shortcodes();
