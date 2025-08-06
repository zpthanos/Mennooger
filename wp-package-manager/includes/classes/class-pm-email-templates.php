<?php
// includes/classes/class-pm-email-templates.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Email_Templates
 *
 * Discovers and renders email templates from templates/emails/*.html.
 */
class PM_Email_Templates {

    /** Directory where the HTML templates live */
    protected $templates_dir;

    public function __construct() {
        $this->templates_dir = WC_PM_PATH . 'templates/emails/';
    }

    /**
     * Return an array of available template keys (filenames without .html).
     *
     * @return string[]
     */
    public static function get_available_templates() {
        $files = glob( WC_PM_PATH . 'templates/emails/*.html' );
        if ( ! $files ) {
            return [];
        }
        return array_map( function( $path ) {
            return basename( $path, '.html' );
        }, $files );
    }

    /**
     * Render a template by key, replacing placeholders.
     *
     * @param string $key  Template key, e.g. 'purchase-request-user'
     * @param array  $data Associative array of placeholder => value
     * @return string      The rendered HTML (or empty string if not found)
     */
    public function render( $key, array $data = [] ) {
        $file = $this->templates_dir . $key . '.html';
        if ( ! file_exists( $file ) ) {
            return '';
        }
        $html = file_get_contents( $file );
        // Replace {{placeholder}} in template
        foreach ( $data as $placeholder => $value ) {
            $html = str_replace( '{{' . $placeholder . '}}', esc_html( $value ), $html );
        }
        return $html;
    }
}
