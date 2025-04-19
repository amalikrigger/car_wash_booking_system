<?php
if (!defined('ABSPATH')) {
    exit;
}

function cwb_get_locations_with_configs() {
    $cached_data = get_transient('cwb_locations_data');

    if ($cached_data === false) {
        $location_fields_configs = [];
        $locations = CWB_Location::get_all();

        foreach ($locations as $location) {
            $location_fields_configs[$location['id']] = CWB_Location::get_fields_config($location['id']);
        }

        $cached_data = [
            'locations' => $locations,
            'location_fields_configs' => $location_fields_configs
        ];

        set_transient('cwb_locations_data', $cached_data, DAY_IN_SECONDS);
    }

    return $cached_data;
}

function cwb_clear_locations_cache() {
    delete_transient('cwb_locations_data');
}

function cwb_update_location($location_data) {
    cwb_clear_locations_cache();
}