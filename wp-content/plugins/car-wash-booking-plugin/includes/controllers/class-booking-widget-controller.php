<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CWB_Booking_Widget_Controller {
    public function render_booking_widget() {
        $location_data = cwb_get_locations_with_configs();

        $view_data = array(
            'locations' => $location_data['locations'],
            'location_fields_configs' => $location_data['location_fields_configs'],
        );

        ob_start();
        include plugin_dir_path(__FILE__) . '../../public/views/booking-widget.php';
        $output_string = ob_get_clean();
        return $output_string;
    }
}