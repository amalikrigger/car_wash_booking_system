<?php
/**
 * Shortcodes Handler for Car Wash Booking System
 *
 * @package Car_Wash_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . '../controllers/class-booking-widget-controller.php';

/**
 * CWB_Shortcodes Class
 * 
 * Registers and handles all plugin shortcodes
 */
class CWB_Shortcodes {

    /**
     * Constructor - registers shortcodes
     */
    public function __construct() {
        add_shortcode('cwb_booking_widget', array( $this, 'booking_widget_shortcode' ));
    }

    /**
     * Renders the booking widget via shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output for the booking widget
     */
    public function booking_widget_shortcode( $atts = [] ) {
        // Parse attributes with defaults
        $attributes = shortcode_atts( array(
            'location_id' => 0,
            'theme' => 'default'
        ), $atts );
        
        try {
            $controller = new CWB_Booking_Widget_Controller();
            return $controller->render_booking_widget( $attributes );
        } catch ( Exception $e ) {
            if ( current_user_can( 'manage_options' ) ) {
                return '<div class="cwb-error">Error rendering booking widget: ' . esc_html( $e->getMessage() ) . '</div>';
            } else {
                return '<div class="cwb-error">Unable to load the booking widget. Please try again later.</div>';
            }
        }
    }
}

// Initialize the shortcodes handler
new CWB_Shortcodes();
