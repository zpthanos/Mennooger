<?php
// includes/helpers/validate.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*--------------------------------------------------------------*/
/*  Generic validators                                          */
/*--------------------------------------------------------------*/
function pm_validate_text( $text, $required = false ) {
    return ! ( $required && trim( wp_unslash( $text ) ) === '' );
}

function pm_validate_email( $email ) {
    return is_email( sanitize_email( wp_unslash( $email ) ) );
}

function pm_validate_tel( $tel ) {
    return (bool) preg_match( '/^[\\d\\+\\-\\s]+$/', wp_unslash( $tel ) );
}

function pm_validate_number( $number ) {
    return is_numeric( wp_unslash( $number ) );
}

function pm_validate_select( $value, array $choices, $required = false ) {
    $val = sanitize_text_field( wp_unslash( $value ) );
    return ( ! $required || $val !== '' ) && array_key_exists( $val, $choices );
}

/*--------------------------------------------------------------*/
/*  Greek AFM (Tax-ID)                                          */
/*--------------------------------------------------------------*/
if ( ! function_exists( 'pm_validate_afm' ) ) {
    function pm_validate_afm( $afm ) {
        $afm = preg_replace( '/\\D/', '', wp_unslash( $afm ) );
        if ( strlen( $afm ) !== 9 ) {
            return false;
        }
        $sum = 0;
        for ( $i = 0; $i < 8; $i++ ) {
            $sum += intval( $afm[ $i ] ) * ( 1 << ( 8 - $i ) );  // 2^(8-i)
        }
        $check = ( $sum % 11 ) % 10;
        return $check === intval( $afm[8] );
    }
}
