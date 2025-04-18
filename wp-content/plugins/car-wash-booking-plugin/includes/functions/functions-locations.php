<?php
if (!defined('ABSPATH')) {
    exit;
}

function cwb_get_locations_with_configs() {
    $location_fields_configs = [];
    $locations = CWB_Location::get_all();

    foreach ($locations as $location) {
        $location_fields_configs[$location['id']] = CWB_Location::get_fields_config($location['id']);
    }

    return [
        'locations' => $locations,
        'location_fields_configs' => $location_fields_configs
    ];
}