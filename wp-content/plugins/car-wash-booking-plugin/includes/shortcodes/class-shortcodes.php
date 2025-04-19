<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

include_once plugin_dir_path(__FILE__) . '../controllers/class-booking-widget-controller.php';

class CWB_Shortcodes {

    public function __construct() {
        add_shortcode('cwb_booking_widget', array( $this, 'booking_widget_shortcode' ));
    }

    public function booking_widget_shortcode() {
        $controller = new CWB_Booking_Widget_Controller();
        return $controller->render_booking_widget();
    }
}

new CWB_Shortcodes();