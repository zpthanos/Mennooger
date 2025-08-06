<?php
// includes/helpers/validate.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Validate that a text field is non-empty if required.
 *
 * @param string $text
 * @param bool   $required
 * @return bool
 */
function pm_validate_text( $text, $required = false ) {
    if ( $required && trim( wp_unslash( $text ) ) === '' ) {
        return false;
    }
    return true;
}

/**
 * Validate an email address.
 *
 * @param string $email
 * @return bool
 */
function pm_validate_email( $email ) {
    $email = sanitize_email( wp_unslash( $email ) );
    return is_email( $email );
}

/**
 * Validate a telephone string (digits, +, –, spaces).
 *
 * @param string $tel
 * @return bool
 */
function pm_validate_tel( $tel ) {
    return (bool) preg_match( '/^[\d\+\-\s]+$/', wp_unslash( $tel ) );
}

/**
 * Validate that a value is numeric.
 *
 * @param mixed $number
 * @return bool
 */
function pm_validate_number( $number ) {
    return is_numeric( wp_unslash( $number ) );
}

/**
 * Validate a select value against allowed choices.
 *
 * @param string $value
 * @param array  $choices
 * @param bool   $required
 * @return bool
 */
function pm_validate_select( $value, array $choices, $required = false ) {
    $val = sanitize_text_field( wp_unslash( $value ) );
    if ( $required && $val === '' ) {
        return false;
    }
    return array_key_exists( $val, $choices );
}

/**
 * Validate a Greek AFM (Tax ID).
 *
 * @param string $afm
 * @return bool
 */
function pm_validate_afm( $afm ) {
    $afm = preg_replace( '/\D/', '', wp_unslash( $afm ) );
    if ( strlen( $afm ) !== 9 ) {
        return false;
    }
    $sum = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        // weight = 2^(8−i)
        $sum += intval( $afm[ $i ] ) * ( 1 << ( 8 - $i ) );
    }
    $mod   = $sum % 11;
    $check = $mod % 10;
    return $check === intval( $afm[8] );
}
