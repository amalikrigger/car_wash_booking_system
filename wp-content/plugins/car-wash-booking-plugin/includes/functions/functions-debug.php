<?php
/**
 * Debug Functions for Car Wash Booking System
 *
 * @package Car_Wash_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Output debug information to browser console, error log, or return as string
 *
 * @param mixed  $data       The data to debug
 * @param string $output_to  Where to output the debug data: 'console', 'log', or 'return' (default: 'console')
 * @param string $label      Optional label for the debug output (default: 'Debug Objects')
 * @return string|void       String if output_to is 'return', void otherwise
 */
function cwb_debug( $data, $output_to = 'console', $label = 'Debug Objects' ) {
    $formatted_output = '';
    
    // Format data based on type
    if ( is_null( $data ) ) {
        $formatted_output = 'NULL';
    } elseif ( is_bool( $data ) ) {
        $formatted_output = $data ? 'true' : 'false';
    } elseif ( is_array( $data ) || is_object( $data ) ) {
        $formatted_output = print_r( $data, true );
    } else {
        $formatted_output = (string) $data;
    }
    
    // Output based on selected method
    switch ( $output_to ) {
        case 'console':
            // Escape output for JavaScript
            $escaped_output = str_replace( 
                array( "\n", "\r", "'", '</', '>' ), 
                array( "\\n", "\\r", "\'", '<\/', '\>' ), 
                $formatted_output 
            );
            echo "<script>console.log('" . esc_js( $label ) . ":', '" . $escaped_output . "');</script>";
            break;
            
        case 'log':
            // Output to PHP error log
            error_log( $label . ': ' . $formatted_output );
            break;
            
        case 'return':
        default:
            // Return the formatted string
            return $formatted_output;
    }
}

/**
 * Legacy function for backward compatibility
 *
 * @param mixed $data The data to debug
 * @deprecated Use cwb_debug() instead
 */
function debug_to_console( $data ) {
    _deprecated_function( __FUNCTION__, '1.2.0', 'cwb_debug' );
    cwb_debug( $data );
}
