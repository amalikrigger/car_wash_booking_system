<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function cwb_initialize_database() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Locations table
    $locations_table = "CREATE TABLE {$wpdb->prefix}cwb_locations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        address VARCHAR(255) NOT NULL
    ) $charset_collate;";

    // Location Fields Configuration Table - NEW TABLE
    $location_fields_config_table = "CREATE TABLE {$wpdb->prefix}cwb_location_fields_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        location_id INT NOT NULL,
        message_enabled BOOLEAN NOT NULL DEFAULT 0,
        gratuity_enabled BOOLEAN NOT NULL DEFAULT 0,
        street_enabled BOOLEAN NOT NULL DEFAULT 0,
        zip_code_enabled BOOLEAN NOT NULL DEFAULT 0,
        city_enabled BOOLEAN NOT NULL DEFAULT 0,
        state_enabled BOOLEAN NOT NULL DEFAULT 0,
        country_enabled BOOLEAN NOT NULL DEFAULT 0,
        lat_long_enabled BOOLEAN NOT NULL DEFAULT 0,
        service_location_enabled BOOLEAN NOT NULL DEFAULT 0,
        FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Vehicle Types table
    $vehicle_types_table = "CREATE TABLE {$wpdb->prefix}cwb_vehicle_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        icon VARCHAR(255) NOT NULL
    ) $charset_collate;";

    // Location to Vehicle Type mapping table
    $location_vehicle_table = "CREATE TABLE {$wpdb->prefix}cwb_location_vehicle_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        location_id INT NOT NULL,
        vehicle_type_id INT NOT NULL,
        FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations(id) ON DELETE CASCADE,
        FOREIGN KEY (vehicle_type_id) REFERENCES {$wpdb->prefix}cwb_vehicle_types(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Packages table
    $packages_table = "CREATE TABLE {$wpdb->prefix}cwb_packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        duration INT NOT NULL COMMENT 'Duration in minutes'
    ) $charset_collate;";

    // Services table
    $services_table = "CREATE TABLE {$wpdb->prefix}cwb_services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        duration INT NOT NULL COMMENT 'Duration in minutes'
    ) $charset_collate;";

    // Package to Service mapping
    $package_service_table = "CREATE TABLE {$wpdb->prefix}cwb_package_services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        package_id INT NOT NULL,
        service_id INT NOT NULL,
        FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}cwb_services(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Vehicle to Package mapping
    $vehicle_package_table = "CREATE TABLE {$wpdb->prefix}cwb_vehicle_packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vehicle_type_id INT NOT NULL,
        package_id INT NOT NULL,
        FOREIGN KEY (vehicle_type_id) REFERENCES {$wpdb->prefix}cwb_vehicle_types(id) ON DELETE CASCADE,
        FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Package to Add-On Service mapping table
    $package_addon_table = "CREATE TABLE {$wpdb->prefix}cwb_package_addons (
        package_id INT NOT NULL,
        service_id INT NOT NULL,
        PRIMARY KEY (package_id, service_id),
        FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}cwb_services(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Settings table
    $settings_table = "CREATE TABLE {$wpdb->prefix}cwb_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        minimum_booking_time INT NOT NULL,         -- Minutes before booking is allowed
        advance_booking_period INT NOT NULL,       -- Maximum days in advance booking is allowed
        grace_period INT NOT NULL DEFAULT 0        -- Minutes between bookings
    ) $charset_collate;";

    // Weekday Time Ranges table
    $weekday_time_ranges_table = "CREATE TABLE {$wpdb->prefix}cwb_weekday_time_ranges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        weekday ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL
    ) $charset_collate;";

    // Specific Date Time Ranges table
    $date_time_ranges_table = "CREATE TABLE {$wpdb->prefix}cwb_date_time_ranges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL
    ) $charset_collate;";

    // Excluded Dates table
    $excluded_dates_table = "CREATE TABLE {$wpdb->prefix}cwb_excluded_dates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        start_time TIME DEFAULT NULL,
        end_time TIME DEFAULT NULL
    ) $charset_collate;";

    // Resources table
    $resources_table = "CREATE TABLE {$wpdb->prefix}cwb_resources (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        capacity INT NOT NULL DEFAULT 1           -- Max concurrent bookings this resource can handle
    ) $charset_collate;";

    // Bookings table
    $bookings_table = "CREATE TABLE {$wpdb->prefix}cwb_bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        package_id INT NOT NULL,
        date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        resource_id INT DEFAULT NULL,
        user_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Execute all table creation queries
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($locations_table);
    dbDelta($location_fields_config_table); // ADDED TABLE CREATION
    dbDelta($vehicle_types_table);
    dbDelta($location_vehicle_table);
    dbDelta($packages_table);
    dbDelta($services_table);
    dbDelta($package_service_table);
    dbDelta($vehicle_package_table);
    dbDelta($package_addon_table);
    dbDelta($settings_table);
    dbDelta($weekday_time_ranges_table);
    dbDelta($date_time_ranges_table);
    dbDelta($excluded_dates_table);
    dbDelta($resources_table);
    dbDelta($bookings_table);

    // Populate tables with default data
    cwb_populate_default_data();
}

function cwb_populate_default_data() {
    global $wpdb;

    // Pre-populate locations if the table is empty.
    $existing_locations = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_locations");

    if ($existing_locations == 0) {
        $wpdb->insert(
            "{$wpdb->prefix}cwb_locations",
            [
                'name' => 'Remote',
                'address' => 'Remote Location'
            ],
            ['%s', '%s']
        );

        $wpdb->insert(
            "{$wpdb->prefix}cwb_locations",
            [
                'name' => 'St. Croix',
                'address' => 'St. Croix Location'
            ],
            ['%s', '%s']
        );
    }

    // Pre-populate location fields config - NEW DATA POPULATION
    $existing_location_fields_config = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_location_fields_config");
    if ($existing_location_fields_config == 0) {
        $locations = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}cwb_locations", ARRAY_A);
        foreach ($locations as $location) {
            $config = [
                'location_id' => $location['id'],
                'message_enabled' => 1, // Enabled for both by default
                'gratuity_enabled' => 1, // Enabled for both by default
                'street_enabled' => ($location['id'] == 1 ? 1 : 0), // Street enabled for Remote only
                'zip_code_enabled' => ($location['id'] == 1 ? 1 : 0), // Zip code enabled for Remote only
                'city_enabled' => ($location['id'] == 1 ? 1 : 0), // City enabled for Remote only
                'state_enabled' => ($location['id'] == 1 ? 1 : 0), // State enabled for Remote only
                'country_enabled' => ($location['id'] == 1 ? 1 : 0), // Country enabled for Remote only
                'lat_long_enabled' => ($location['id'] == 1 ? 1 : 0), // Coordinates enabled for Remote only
                'service_location_enabled' => ($location['id'] == 2 ? 1 : 0), // Service Location for St. Croix only
            ];
            $wpdb->insert(
                "{$wpdb->prefix}cwb_location_fields_config",
                $config,
                ['%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d']
            );
        }
    }

    // Pre-populate vehicle types if the table is empty.
    $existing_vehicle_types = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_vehicle_types");

    if ($existing_vehicle_types == 0) {
        $vehicle_types = [
            ['name' => 'Regular Size Car', 'icon' => 'fa-solid fa-car-side'],
            ['name' => 'Medium Size Car', 'icon' => 'fa-solid fa-car-side'],
            ['name' => 'Compact SUV', 'icon' => 'fa-solid fa-car'],
            ['name' => 'Pickup Truck', 'icon' => 'fa-solid fa-truck-pickup'],
            ['name' => 'Minivan', 'icon' => 'fa-solid fa-van-shuttle']
        ];

        foreach ($vehicle_types as $vehicle) {
            $wpdb->insert(
                "{$wpdb->prefix}cwb_vehicle_types",
                $vehicle,
                ['%s', '%s']
            );
        }
    }

    // Associate all vehicle types with both locations.
    $locations = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}cwb_locations", ARRAY_A);
    $vehicle_types = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}cwb_vehicle_types", ARRAY_A);

    foreach ($locations as $location) {
        foreach ($vehicle_types as $vehicle) {
            // Skip associating "Minivan" with "St. Croix".
            if ($location['name'] === 'St. Croix' && $vehicle['name'] === 'Minivan') {
                continue;
            }

            $wpdb->insert(
                "{$wpdb->prefix}cwb_location_vehicle_types",
                [
                    'location_id' => $location['id'],
                    'vehicle_type_id' => $vehicle['id']
                ],
                ['%d', '%d']
            );
        }
    }

    // Insert default settings if the table is empty.
    $existing_settings = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_settings");

    if ($existing_settings == 0) {
        $wpdb->insert(
            "{$wpdb->prefix}cwb_settings",
            [
                'minimum_booking_time' => 30, // 30 minutes
                'advance_booking_period' => 7, // 7 days for testing
                'grace_period' => 15 // 15 minutes
            ],
            ['%d', '%d', '%d']
        );
    }

    // Insert default weekday time ranges if the table is empty.
    $existing_weekday_time_ranges = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_weekday_time_ranges");

    if ($existing_weekday_time_ranges == 0) {
        $weekday_time_ranges = [
            ['weekday' => 'Monday', 'start_time' => '09:00:00', 'end_time' => '12:00:00'],
            ['weekday' => 'Wednesday', 'start_time' => '13:00:00', 'end_time' => '16:00:00'],
            ['weekday' => 'Friday', 'start_time' => '10:00:00', 'end_time' => '14:00:00']
        ];

        foreach ($weekday_time_ranges as $range) {
            $wpdb->insert(
                "{$wpdb->prefix}cwb_weekday_time_ranges",
                $range,
                ['%s', '%s', '%s']
            );
        }
    }

    // Insert default specific date time ranges if the table is empty.
    $existing_date_time_ranges = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_date_time_ranges");

    if ($existing_date_time_ranges == 0) {
        $specific_date_ranges = [
            ['date' => '2025-02-05', 'start_time' => '10:00:00', 'end_time' => '12:00:00'], // Special event
            ['date' => '2025-02-06', 'start_time' => '09:00:00', 'end_time' => '11:00:00']  // Special event
        ];

        foreach ($specific_date_ranges as $range) {
            $wpdb->insert(
                "{$wpdb->prefix}cwb_date_time_ranges",
                $range,
                ['%s', '%s', '%s']
            );
        }
    }

    // Insert default excluded dates if the table is empty.
    $existing_excluded_dates = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_excluded_dates");

    if ($existing_excluded_dates == 0) {
        $excluded_dates = [
            ['start_date' => '2025-02-07', 'end_date' => '2025-02-07'], // Maintenance day
        ];

        foreach ($excluded_dates as $excluded_date) {
            $wpdb->insert(
                "{$wpdb->prefix}cwb_excluded_dates",
                $excluded_date,
                ['%s', '%s']
            );
        }
    }

    // Insert default resources if the table is empty.
    $existing_resources = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_resources");

    if ($existing_resources == 0) {
        $resources = [
            ['name' => 'Washer 1', 'capacity' => 2],
            ['name' => 'Washer 2', 'capacity' => 1]
        ];

        foreach ($resources as $resource) {
            $wpdb->insert(
                "{$wpdb->prefix}cwb_resources",
                $resource,
                ['%s', '%d']
            );
        }
    }

    // Insert packages if the table is empty.
    $existing_packages = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_packages");

    if ($existing_packages == 0) {
        $packages = [
            ['name' => 'Hood Wash', 'price' => 45.00, 'duration' => 40], // 40 minutes
            ['name' => 'Fresh Cabin', 'price' => 50.00, 'duration' => 55], // 55 minutes
            ['name' => 'Fresh & Fly', 'price' => 80.00, 'duration' => 100], // 1h 40min
            ['name' => 'Full Detail', 'price' => 105.00, 'duration' => 110], // 1h 50min
            ['name' => 'Deep Cleanse', 'price' => 80.00, 'duration' => 75], // 1h 15min
        ];

        foreach ($packages as $package) {
            $wpdb->insert(
                "{$wpdb->prefix}cwb_packages",
                $package,
                ['%s', '%f', '%d']
            );
        }
    }

    // Insert services if the table is empty
    $existing_services = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_services");

    if ($existing_services == 0) {
        $services = [
            ['name' => 'Exterior Wash', 'price' => 25.00, 'duration' => 25], // 25 minutes
            ['name' => 'Wheel Shine', 'price' => 15.00, 'duration' => 20], // 20 minutes
            ['name' => 'Tire Dressing', 'price' => 10.00, 'duration' => 15], // 15 minutes
            ['name' => 'Windows In & Out', 'price' => 20.00, 'duration' => 20], // 20 minutes
            ['name' => 'Paint Protection', 'price' => 50.00, 'duration' => 30], // 30 minutes
            ['name' => 'Interior Vacuum', 'price' => 30.00, 'duration' => 25], // 25 minutes
            ['name' => 'Trim Dressing', 'price' => 15.00, 'duration' => 15], // 15 minutes
            ['name' => 'Steam Cleaner', 'price' => 35.00, 'duration' => 40], // 40 minutes
            ['name' => 'Trash Removal', 'price' => 10.00, 'duration' => 10], // 10 minutes
            ['name' => 'Air Freshener', 'price' => 5.00, 'duration' => 5], // 5 minutes
        ];

        foreach ($services as $service) {
            $wpdb->insert(
                "{$wpdb->prefix}cwb_services",
                $service,
                ['%s', '%f', '%d']
            );
        }
    }

    // Associate services with packages.
    $package_services = [
        'Hood Wash' => ['Exterior Wash', 'Wheel Shine', 'Tire Dressing', 'Windows In & Out', 'Paint Protection'],
        'Fresh Cabin' => ['Carpet', 'Floor Mats', 'Seats', 'Steering Wheel', 'Trash Removal', 'Trim Dressing', 'Windows In & Out', 'Interior Vacuum', 'Door Shuts, Air Vents & Vinyls', 'Air Freshener'],
        'Fresh & Fly' => ['Carpet', 'Engine Clean', 'Exterior Wash', 'Floor Mats', 'Headliner', 'Seats', 'Steam Cleaner', 'Steering Wheel', 'Trash Removal', 'Trim Dressing', 'Wheel Shine', 'Tire Dressing', 'Windows In & Out', 'Interior Vacuum', 'Door Shuts, Air Vents & Vinyls', 'Air Freshener', 'Paint Protection'],
        'Full Detail' => ['Carpet', 'Deep Trunk Clean', 'Engine Clean', 'Exterior Wash', 'Floor Mats', 'Headliner', 'Seats', 'Steam Cleaner', 'Steering Wheel', 'Trash Removal', 'Trim Dressing', 'Wheel Shine', 'Tire Dressing', 'Windows In & Out', 'Interior Vacuum', 'Trunk Vacuum', 'Door Shuts, Air Vents & Vinyls', 'Air Freshener', 'Paint Protection'],
        'Deep Cleanse' => ['Carpet', 'Deep Trunk Clean', 'Engine Clean', 'Floor Mats', 'Headliner', 'Seats', 'Steam Cleaner', 'Steering Wheel', 'Trash Removal', 'Trim Dressing', 'Windows In & Out', 'Interior Vacuum', 'Trunk Vacuum', 'Door Shuts, Air Vents & Vinyls', 'Air Freshener']
    ];

    foreach ($package_services as $package_name => $service_names) {
        $package_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}cwb_packages WHERE name = %s", $package_name));

        foreach ($service_names as $service_name) {
            $service_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}cwb_services WHERE name = %s", $service_name));
            $wpdb->insert(
                "{$wpdb->prefix}cwb_package_services",
                ['package_id' => $package_id, 'service_id' => $service_id],
                ['%d', '%d']
            );
        }
    }

    // Associate packages with vehicles.
    $vehicle_types = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}cwb_vehicle_types", ARRAY_A);

    foreach ($vehicle_types as $vehicle) {
        $packages = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}cwb_packages", ARRAY_A);

        foreach ($packages as $package) {
            if ($vehicle['name'] === 'Minivan' && $package['name'] !== 'Fresh & Fly') {
                continue; // Skip packages not allowed for Minivan
            }

            $wpdb->insert(
                "{$wpdb->prefix}cwb_vehicle_packages",
                ['vehicle_type_id' => $vehicle['id'], 'package_id' => $package['id']],
                ['%d', '%d']
            );
        }
    }

    // Associate add-on services with packages.
    $existing_addons = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_package_addons");

    if ($existing_addons == 0) {
        $package_addons = [
            'Hood Wash' => ['Exterior Wash', 'Wheel Shine', 'Tire Dressing'],
            'Fresh Cabin' => ['Interior Vacuum', 'Windows In & Out', 'Trim Dressing'],
            'Fresh & Fly' => ['Steam Cleaner', 'Trash Removal', 'Paint Protection'],
            'Full Detail' => ['Tire Dressing', 'Trim Dressing', 'Air Freshener'],
            'Deep Cleanse' => ['Exterior Wash', 'Steam Cleaner', 'Paint Protection'],
        ];

        foreach ($package_addons as $package_name => $addon_services) {
            $package_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}cwb_packages WHERE name = %s",
                $package_name
            ));

            foreach ($addon_services as $service_name) {
                $service_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}cwb_services WHERE name = %s",
                    $service_name
                ));

                $wpdb->insert(
                    "{$wpdb->prefix}cwb_package_addons",
                    ['package_id' => $package_id, 'service_id' => $service_id],
                    ['%d', '%d']
                );
            }
        }
    }

    // Insert default bookings for testing.
    $existing_bookings = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_bookings");

    if ($existing_bookings == 0) {
        $bookings = [
            ['package_id' => 1, 'date' => '2025-02-05', 'start_time' => '10:00:00', 'end_time' => '10:30:00', 'resource_id' => 1, 'user_id' => 1],
            ['package_id' => 1, 'date' => '2025-02-05', 'start_time' => '11:00:00', 'end_time' => '11:30:00', 'resource_id' => 1, 'user_id' => 2],
            ['package_id' => 2, 'date' => '2025-02-06', 'start_time' => '09:30:00', 'end_time' => '10:00:00', 'resource_id' => 2, 'user_id' => 3],
        ];

        foreach ($bookings as $booking) {
            $wpdb->insert(
                "{$wpdb->prefix}cwb_bookings",
                $booking,
                ['%d', '%s', '%s', '%s', '%d', '%d']
            );
        }
    }
}

/**
 * Get location fields configuration.
 */
function cwb_get_location_fields_config( $location_id ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'cwb_location_fields_config';
    $result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE location_id = %d", $location_id ), ARRAY_A );

    return $result ? $result : []; // Return array, empty if no config found
}

/**
 * Insert a test booking into the database.
 */
function cwb_insert_booking( $data ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'cwb_bookings';

    $result = $wpdb->insert(
        $table_name,
        [
            'location_id' => $data['location_id'],
            'vehicle_type_id' => $data['vehicle_type_id'],
            'package_id' => $data['package_id'],
            'date' => $data['date'],
            'time' => $data['time'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'street' => $data['street'],
            'zip_code' => $data['zip_code'],
            'city' => $data['city'],
            'state' => $data['state'],
            'country' => $data['country'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'vehicle_make_model' => $data['vehicle_make_model'],
            'message' => $data['message'],
            'gratuity' => $data['gratuity'],
            'payment_type' => $data['payment_type'],
            'privacy_policy_accepted' => $data['privacy_policy_accepted'],
            'terms_accepted' => $data['terms_accepted'],
            'total_price' => $data['total_price'],
        ],
        [
            '%d', '%d', '%d', '%s', '%s', // Integers and strings
            '%s', '%s', '%s', '%s', '%s', '%s', // PII fields
            '%s', '%s', '%s', '%s', '%f', // Other fields
            '%s', '%d', '%d', '%f' // Boolean and float
        ]
    );

    return $result ? $wpdb->insert_id : false;
}

/**
 * Get locations for booking.
 */
function cwb_get_locations() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'cwb_locations';
    $results = $wpdb->get_results( "SELECT id, name FROM $table_name", ARRAY_A );

    return $results;
}

/**
 * Get vehicle types for booking.
 */
function cwb_get_vehicle_types() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'cwb_vehicle_types';
    $results = $wpdb->get_results( "SELECT id, name FROM $table_name", ARRAY_A );

    return $results;
}

?>
