<?php
// includes/helpers/sanitize.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sanitize a generic text field.
 *
 * @param string $text
 * @return string
 */
function pm_sanitize_text( $text ) {
    return sanitize_text_field( wp_unslash( $text ) );
}

/**
 * Sanitize an email field.
 *
 * @param string $email
 * @return string
 */
function pm_sanitize_email( $email ) {
    return sanitize_email( wp_unslash( $email ) );
}

/**
 * Sanitize a telephone field (digits, +, –, spaces).
 *
 * @param string $phone
 * @return string
 */
function pm_sanitize_tel( $phone ) {
    $clean = wp_unslash( $phone );
    return preg_replace( '/[^\d\+\-\s]/', '', $clean );
}

/**
 * Sanitize a number (float) field.
 *
 * @param mixed $number
 * @return float
 */
function pm_sanitize_number( $number ) {
    return floatval( wp_unslash( $number ) );
}

/**
 * Sanitize a select field given allowed choices.
 *
 * @param string $value
 * @param array  $choices
 * @return string
 */
function pm_sanitize_select( $value, array $choices ) {
    $val = sanitize_text_field( wp_unslash( $value ) );
    return array_key_exists( $val, $choices ) ? $val : '';
}

/**
 * Sanitize a checkbox boolean value.
 *
 * @param mixed $value
 * @return bool
 */
function pm_sanitize_checkbox( $value ) {
    return filter_var( wp_unslash( $value ), FILTER_VALIDATE_BOOLEAN );
}

/**
 * Stub for file uploads—let WordPress handle via wp_handle_upload().
 *
 * @param array $file
 * @return array
 */
function pm_sanitize_file( $file ) {
    return $file;
}
