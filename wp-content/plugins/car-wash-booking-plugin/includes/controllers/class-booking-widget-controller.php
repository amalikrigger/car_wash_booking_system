<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class CWB_Booking_Widget_Controller {

    /**
     * Render the booking form.
     *
     * This method handles the logic for displaying the booking form,
     * including enqueuing assets and fetching necessary data.
     *
     * @return string HTML output for the booking form.
     */
    public function render_booking_widget() {
        // Enqueue the stylesheet for the UI
        wp_enqueue_style('cwb-styles-css', plugin_dir_url(__FILE__) . '../../public/assets/css/styles.css');
        wp_enqueue_style('cwb-colors-css', plugin_dir_url(__FILE__) . '../../public/assets/css/colors.css');
        wp_enqueue_script(
            'font-awesome-kit', // Handle name
            'https://kit.fontawesome.com/c95283ecc5.js', // URL of the Font Awesome script
            [], // Dependencies (none)
            null, // Version (null means no versioning)
            true // Load in the footer
        );
        wp_enqueue_script('cwb-booking-js', plugin_dir_url(__FILE__) . '../../public/assets/js/cwb-booking.js', array('jquery'), null, true);

        // Fetch location fields configurations for all locations
        $location_fields_configs = [];
        $locations = CWB_Location::get_all();
        foreach ($locations as $location) {
            $location_fields_configs[$location['id']] = CWB_Location::get_fields_config( $location['id'] );
        }

        // Pass AJAX URL, nonce, and location fields config to the script.
        wp_localize_script('cwb-booking-js', 'cwb_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cwb_nonce'),
            'location_fields_configs' => $location_fields_configs,
        ));

        $view_data = array(
            'locations' => $locations,
            'location_fields_configs' => $location_fields_configs,
        );

        ob_start();
        include plugin_dir_path(__FILE__) . '../../templates/booking-widget.php';
        $output_string = ob_get_clean();
        return $output_string;
    }
}