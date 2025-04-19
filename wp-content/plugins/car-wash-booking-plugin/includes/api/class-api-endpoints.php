<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

include_once plugin_dir_path(__FILE__) . '../functions/functions-formatting.php';
include_once plugin_dir_path(__FILE__) . '../functions/functions-availability.php';
include_once plugin_dir_path(__FILE__) . '../functions/functions-debug.php';

class CWB_API_Endpoints {
    public function __construct() {
        add_action( 'wp_ajax_cwb_get_vehicles',        array( $this, 'get_vehicles' ) );
        add_action( 'wp_ajax_nopriv_cwb_get_vehicles', array( $this, 'get_vehicles' ) );
        add_action( 'wp_ajax_cwb_get_packages',        array( $this, 'get_packages' ) );
        add_action( 'wp_ajax_nopriv_cwb_get_packages', array( $this, 'get_packages' ) );
        add_action( 'wp_ajax_cwb_get_addons',          array( $this, 'get_addons' ) );
        add_action( 'wp_ajax_nopriv_cwb_get_addons',     array( $this, 'get_addons' ) );
        add_action( 'wp_ajax_cwb_get_available_slots', array( $this, 'get_available_slots' ) );
        add_action( 'wp_ajax_nopriv_cwb_get_available_slots', array( $this, 'get_available_slots' ) );
    }

    public function get_vehicles() {
        check_ajax_referer( 'cwb_nonce', 'nonce' );

        $location_id = intval($_POST['location_id']);
        $vehicles = CWB_Vehicle_Type::get_by_location( $location_id );

        $html = '';
        if (!empty($vehicles)) {
            foreach ($vehicles as $vehicle) {
                $html .= "<li class='cwb-vehicle' data-id='" . esc_attr($vehicle['id']) . "'>
                            <div>
                                <i class='" . esc_attr($vehicle['icon']) . "'></i>
                                <div>" . esc_html($vehicle['name']) . "</div>
                            </div>
                          </li>";
            }
        } else {
            $html .= "<li>No vehicles available for this location.</li>";
        }

        echo $html;
        wp_die();
    }

    public function get_packages() {
        check_ajax_referer( 'cwb_nonce', 'nonce' );

        $vehicle_type_id = intval($_POST['vehicle_type_id']);
        $packages = CWB_Package::get_by_vehicle_type( $vehicle_type_id );

        $html = '';
        foreach ($packages as $package) {
            $services = CWB_Package::get_services( $package['id'] );

            $serviceListHtml = '<ul class="cwb-package-service-list cwb-list-reset cwb-clear-fix">';
            foreach ($services as $service) {
                $serviceListHtml .= '<li>' . esc_html($service['name']) . '</li>';
            }
            $serviceListHtml .= '</ul>';

            $formatted_duration = cwb_format_duration($package['duration']);

            $html .= "<li class='cwb-package cwb-package-id-" . esc_attr($package['id']) . "'
                             data-id='" . esc_attr($package['id']) . "'
                             data-duration='" . esc_attr($package['duration']) . "'
                             data-price='" . esc_attr($package['price']) . "'>
                             <h5 class='cwb-package-name'>" . esc_html($package['name']) . "</h5>
                             <div class='cwb-package-price'>
                                 <span class='cwb-package-price-currency'>$</span>
                                 <span class='cwb-package-price-unit'>" . esc_html($package['price']) . "</span>
                                 <span class='cwb-package-price-decimal'>00</span>
                             </div>
                             <div class='cwb-package-duration'>
                                 <i class='fa-regular fa-clock'></i>
                                 <span>" . esc_html($formatted_duration) . "</span>
                             </div>
                             $serviceListHtml
                             <div class='cwb-button-box'>
                                 <a class='cwb-button' href='#' onClick='return false;'>Book Now</a>
                             </div>
                         </li>";
        }

        echo $html;
        wp_die();
    }

    public function get_addons() {
        check_ajax_referer( 'cwb_nonce', 'nonce' );

        $package_id = intval($_POST['package_id']);
        $addons = CWB_Service::get_addons_by_package( $package_id );

        $html = '';
        if (!empty($addons)) {
            $html .= '<ul class="cwb-service-list cwb-list-reset cwb-clear-fix">';
            foreach ($addons as $addon) {
                $formatted_duration = cwb_format_duration($addon['duration']);

                $html .= "<li class='cwb-clear-fix cwb-service-id-" . esc_attr($addon['id']) . "'
                            data-id='" . esc_attr($addon['id']) . "'
                            data-duration='" . esc_attr($addon['duration']) . "'
                            data-price='" . esc_attr($addon['price']) . "'>
                            <div class='cwb-service-name'>" . esc_html($addon['name']) . "</div>
                            <div class='cwb-service-duration'>
                                <span class='cwb-meta-icon cwb-meta-icon-duration'></span>" . esc_html($formatted_duration) . "
                            </div>
                            <div class='cwb-service-price'>
                                <span class='cwb-meta-icon cwb-meta-icon-price'></span>$" . esc_html($addon['price']) . "
                            </div>
                            <div class='cwb-button-box'>
                                <a class='cwb-button' href='#'>Select</a>
                            </div>
                          </li>";
            }
            $html .= '</ul>';
        } else {
            $html .= '<div class="cwb-disabled">No add-on services available for this package.</div>';
        }

        echo $html;
        wp_die();
    }

    public function get_available_slots() {
        global $wpdb;

        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : null;
        $duration = isset($_POST['duration']) ? intval($_POST['duration']) : null; // Expecting 'duration' now

        if (!$date || !$duration) {
            wp_send_json_error(['message' => 'Invalid input data.', 'received_date' => $date, 'received_duration' => $duration]);
        }

        $slots = CWB_Booking::get_available_slots( $date, $duration );

        wp_send_json_success(['slots' => $slots]);
    }
}

new cwb_API_Endpoints();




