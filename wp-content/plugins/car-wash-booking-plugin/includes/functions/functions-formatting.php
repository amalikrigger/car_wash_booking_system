<?php
/**
 * Formatting Functions for Car Wash Booking System
 *
 * @package Car_Wash_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Format duration in minutes to a human-readable string (e.g., "2h 30min")
 *
 * @param int $duration Duration in minutes
 * @return string Formatted duration string
 */
function cwb_format_duration( $duration ) {
    // Validate input
    if ( ! is_numeric( $duration ) ) {
        return '';
    }
    
    // Handle negative values
    $duration = abs( $duration );
    
    // Handle special case for zero
    if ( $duration === 0 ) {
        return '0min';
    }
    
    $hours = floor( $duration / 60 );
    $minutes = $duration % 60;

    if ( $hours > 0 && $minutes > 0 ) {
        return "{$hours}h {$minutes}min";
    } elseif ( $hours > 0 ) {
        return "{$hours}h";
    } else {
        return "{$minutes}min";
    }
}

/**
 * Format price with currency symbol
 *
 * @param float  $price    The price to format
 * @param string $currency Currency symbol (default: '$')
 * @return string Formatted price string
 */
function cwb_format_price( $price, $currency = '$' ) {
    // Validate input
    if ( ! is_numeric( $price ) ) {
        return '';
    }
    
    return $currency . number_format( (float) $price, 2, '.', ',' );
}
