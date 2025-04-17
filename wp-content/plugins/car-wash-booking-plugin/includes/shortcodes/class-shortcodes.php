<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include the booking widget controller
include_once plugin_dir_path(__FILE__) . '../controllers/class-booking-widget-controller.php';

class CWB_Shortcodes {

    public function __construct() {
        add_shortcode('cwb_booking_widget', array( $this, 'booking_widget_shortcode' ));
    }

    /**
     * Shortcode callback for displaying the booking widget.
     *
     * @return string HTML output of the booking widget.
     */
    public function booking_widget_shortcode() {
        $controller = new CWB_Booking_Widget_Controller(); // Instantiate the controller
        return $controller->render_booking_widget(); // Call the controller method to render the form
    }
}

new CWB_Shortcodes();