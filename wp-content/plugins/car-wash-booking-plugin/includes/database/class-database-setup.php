<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class CWB_Database_Setup {
    public static function initialize_database() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "
        -- Independent tables (no foreign key dependencies) first

        -- Booking Statuses Table
        CREATE TABLE {$wpdb->prefix}cwb_booking_statuses (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE COMMENT 'e.g., Pending, Confirmed, Cancelled, Completed',
            description VARCHAR(255),
            is_default TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Is this the default status?',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_is_default (is_default)
        ) $charset_collate;

        -- Payment Statuses Table
        CREATE TABLE {$wpdb->prefix}cwb_payment_statuses (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE COMMENT 'e.g., Pending, Paid, Failed, Refunded',
            description VARCHAR(255),
            is_default TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Is this the default status?',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_is_default (is_default)
        ) $charset_collate;

        -- Payment Methods Table
        CREATE TABLE {$wpdb->prefix}cwb_payment_methods (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique identifier, e.g., stripe_credit_card',
            code VARCHAR(50) UNIQUE COMMENT 'Code-friendly identifier, e.g., stripe-cc',
            display_name VARCHAR(100) NOT NULL COMMENT 'User-friendly name, e.g., Credit Cards (Stripe)',
            description TEXT,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_is_active (is_active),
            KEY idx_code (code)
        ) $charset_collate;

        -- Locations Table
        CREATE TABLE {$wpdb->prefix}cwb_locations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            address TEXT,
            contact_info TEXT,
            time_zone VARCHAR(50) DEFAULT 'UTC',
            latitude DECIMAL(10,8),
            longitude DECIMAL(11,8),
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_is_active (is_active)
        ) $charset_collate;

        -- Customer Types Table
        CREATE TABLE {$wpdb->prefix}cwb_customer_types (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;

        -- Vehicle Types Table
        CREATE TABLE {$wpdb->prefix}cwb_vehicle_types (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            icon VARCHAR(50),
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;

        -- Booking Sources Table
        CREATE TABLE {$wpdb->prefix}cwb_booking_sources (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;

        -- Marketing Campaigns Table
        CREATE TABLE {$wpdb->prefix}cwb_marketing_campaigns (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(50) UNIQUE,
            start_date DATE,
            end_date DATE,
            description TEXT,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;

        -- Services Table (for package inclusions and add-ons)
        CREATE TABLE {$wpdb->prefix}cwb_services (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            duration INT UNSIGNED NOT NULL COMMENT 'Duration in minutes',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            KEY idx_is_active (is_active)
        ) $charset_collate;

        -- Packages Table
        CREATE TABLE {$wpdb->prefix}cwb_packages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            duration INT UNSIGNED NOT NULL COMMENT 'Duration in minutes',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            KEY idx_is_active (is_active)
        ) $charset_collate;

        -- Now tables with one level of dependency

        -- Customers Table
        CREATE TABLE {$wpdb->prefix}cwb_customers (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED COMMENT 'WordPress user ID if registered',
            customer_type_id INT UNSIGNED,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(50),
            street VARCHAR(100),
            city VARCHAR(100),
            state VARCHAR(50),
            zip_code VARCHAR(20),
            country VARCHAR(50),
            is_guest TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_email (email),
            KEY idx_user_id (user_id),
            KEY idx_customer_type_id (customer_type_id),
            FOREIGN KEY (customer_type_id) REFERENCES {$wpdb->prefix}cwb_customer_types (id) ON DELETE SET NULL
        ) $charset_collate;

        -- Resources Table (staff, equipment, etc.)
        CREATE TABLE {$wpdb->prefix}cwb_resources (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            type VARCHAR(50) NOT NULL,
            capacity INT UNSIGNED NOT NULL DEFAULT 1,
            location_id BIGINT UNSIGNED NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations (id) ON DELETE CASCADE,
            KEY idx_is_active (is_active)
        ) $charset_collate;

        -- Location Fields Configuration
        CREATE TABLE {$wpdb->prefix}cwb_location_fields_config (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            location_id BIGINT UNSIGNED NOT NULL,
            field_name VARCHAR(50) NOT NULL,
            is_required TINYINT(1) NOT NULL DEFAULT 0,
            is_visible TINYINT(1) NOT NULL DEFAULT 1,
            UNIQUE KEY unique_location_field (location_id, field_name),
            FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations (id) ON DELETE CASCADE
        ) $charset_collate;

        -- Link table for locations and vehicle types
        CREATE TABLE {$wpdb->prefix}cwb_location_vehicle_types (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            location_id BIGINT UNSIGNED NOT NULL,
            vehicle_type_id BIGINT UNSIGNED NOT NULL,
            UNIQUE KEY unique_location_vehicle (location_id, vehicle_type_id),
            FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations (id) ON DELETE CASCADE,
            FOREIGN KEY (vehicle_type_id) REFERENCES {$wpdb->prefix}cwb_vehicle_types (id) ON DELETE CASCADE
        ) $charset_collate;

        -- Package Pricing Table
        CREATE TABLE {$wpdb->prefix}cwb_package_pricing (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            package_id BIGINT UNSIGNED NOT NULL,
            location_id BIGINT UNSIGNED,
            start_date DATE,
            end_date DATE,
            weekday VARCHAR(10),
            start_time TIME,
            end_time TIME,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages (id) ON DELETE CASCADE,
            FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations (id) ON DELETE CASCADE,
            INDEX idx_package_date_range (package_id, start_date, end_date),
            INDEX idx_package_weekday_time (package_id, weekday, start_time, end_time)
        ) $charset_collate;

        -- Service Pricing Table
        CREATE TABLE {$wpdb->prefix}cwb_service_pricing (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            service_id BIGINT UNSIGNED NOT NULL,
            location_id BIGINT UNSIGNED,
            start_date DATE,
            end_date DATE,
            weekday VARCHAR(10),
            start_time TIME,
            end_time TIME,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}cwb_services (id) ON DELETE CASCADE,
            FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations (id) ON DELETE CASCADE,
            INDEX idx_service_date_range (service_id, start_date, end_date),
            INDEX idx_service_weekday_time (service_id, weekday, start_time, end_time)
        ) $charset_collate;

        -- Package-Service Association (services included in packages)
        CREATE TABLE {$wpdb->prefix}cwb_package_services (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            package_id BIGINT UNSIGNED NOT NULL,
            service_id BIGINT UNSIGNED NOT NULL,
            UNIQUE KEY unique_package_service (package_id, service_id),
            FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages (id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}cwb_services (id) ON DELETE CASCADE
        ) $charset_collate;

        -- Package-AddOn Association (available add-ons for packages)
        CREATE TABLE {$wpdb->prefix}cwb_package_addons (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            package_id BIGINT UNSIGNED NOT NULL,
            service_id BIGINT UNSIGNED NOT NULL,
            UNIQUE KEY unique_package_addon (package_id, service_id),
            FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages (id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}cwb_services (id) ON DELETE CASCADE
        ) $charset_collate;

        -- Vehicle-Package Association
        CREATE TABLE {$wpdb->prefix}cwb_vehicle_packages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            vehicle_type_id BIGINT UNSIGNED NOT NULL,
            package_id BIGINT UNSIGNED NOT NULL,
            UNIQUE KEY unique_vehicle_package (vehicle_type_id, package_id),
            FOREIGN KEY (vehicle_type_id) REFERENCES {$wpdb->prefix}cwb_vehicle_types (id) ON DELETE CASCADE,
            FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages (id) ON DELETE CASCADE
        ) $charset_collate;

        -- Resource Availability Table
        CREATE TABLE {$wpdb->prefix}cwb_resource_availability (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            resource_id BIGINT UNSIGNED NOT NULL,
            date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            is_available TINYINT(1) NOT NULL DEFAULT 1,
            reason VARCHAR(255),
            FOREIGN KEY (resource_id) REFERENCES {$wpdb->prefix}cwb_resources (id) ON DELETE CASCADE,
            UNIQUE KEY unique_resource_date_time (resource_id, date, start_time, end_time),
            INDEX idx_resource_date (resource_id, date)
        ) $charset_collate;

        -- Weekday Time Ranges (availability)
        CREATE TABLE {$wpdb->prefix}cwb_weekday_time_ranges (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            weekday VARCHAR(10) NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            location_id BIGINT UNSIGNED NOT NULL,
            FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations (id) ON DELETE CASCADE,
            KEY idx_weekday (weekday)
        ) $charset_collate;

        -- Date-specific Time Ranges (overrides weekday settings)
        CREATE TABLE {$wpdb->prefix}cwb_date_time_ranges (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            location_id BIGINT UNSIGNED NOT NULL,
            FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations (id) ON DELETE CASCADE,
            KEY idx_date (date)
        ) $charset_collate;

        -- Excluded Dates (holidays, closures)
        CREATE TABLE {$wpdb->prefix}cwb_excluded_dates (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            start_time TIME DEFAULT NULL,
            end_time TIME DEFAULT NULL,
            reason VARCHAR(255),
            location_id BIGINT UNSIGNED NOT NULL,
            FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations (id) ON DELETE CASCADE,
            KEY idx_date_range (start_date, end_date)
        ) $charset_collate;

        -- Finally, tables with multiple levels of dependency

        -- Bookings Table
        CREATE TABLE {$wpdb->prefix}cwb_bookings (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            booking_number VARCHAR(50) NOT NULL,
            booking_source_id INT UNSIGNED,
            marketing_campaign_id INT UNSIGNED,
            customer_id BIGINT UNSIGNED NOT NULL,
            location_id BIGINT UNSIGNED NOT NULL,
            vehicle_type_id BIGINT UNSIGNED NOT NULL,
            vehicle_details VARCHAR(255),
            package_id BIGINT UNSIGNED, -- Nullable if booking can be service-based
            resource_id BIGINT UNSIGNED,
            booking_status_id INT UNSIGNED NOT NULL DEFAULT 1,
            start_datetime DATETIME NOT NULL,
            end_datetime DATETIME NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            notes TEXT,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            latitude DECIMAL(10,8),
            longitude DECIMAL(11,8),
            UNIQUE KEY idx_booking_number (booking_number),
            KEY idx_booking_date (start_datetime),
            KEY idx_booking_status_id (booking_status_id),
            KEY idx_booking_source_id (booking_source_id),
            KEY idx_marketing_campaign_id (marketing_campaign_id),
            FOREIGN KEY (customer_id) REFERENCES {$wpdb->prefix}cwb_customers (id) ON DELETE RESTRICT,
            FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}cwb_locations (id) ON DELETE RESTRICT,
            FOREIGN KEY (vehicle_type_id) REFERENCES {$wpdb->prefix}cwb_vehicle_types (id) ON DELETE RESTRICT,
            FOREIGN KEY (package_id) REFERENCES {$wpdb->prefix}cwb_packages (id) ON DELETE RESTRICT,
            FOREIGN KEY (resource_id) REFERENCES {$wpdb->prefix}cwb_resources (id) ON DELETE SET NULL,
            FOREIGN KEY (booking_status_id) REFERENCES {$wpdb->prefix}cwb_booking_statuses (id) ON DELETE RESTRICT,
            FOREIGN KEY (booking_source_id) REFERENCES {$wpdb->prefix}cwb_booking_sources(id) ON DELETE SET NULL,
            FOREIGN KEY (marketing_campaign_id) REFERENCES {$wpdb->prefix}cwb_marketing_campaigns(id) ON DELETE SET NULL
        ) $charset_collate;

        -- Tables depending on bookings

        -- Booking Services Table
        CREATE TABLE {$wpdb->prefix}cwb_booking_services (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            booking_id BIGINT UNSIGNED NOT NULL,
            service_id BIGINT UNSIGNED NOT NULL,
            quantity INT UNSIGNED NOT NULL DEFAULT 1,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (booking_id) REFERENCES {$wpdb->prefix}cwb_bookings (id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}cwb_services (id) ON DELETE RESTRICT,
            UNIQUE KEY unique_booking_service (booking_id, service_id)
        ) $charset_collate;

        -- Booking Add-ons Table
        CREATE TABLE {$wpdb->prefix}cwb_booking_addons (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            booking_id BIGINT UNSIGNED NOT NULL,
            service_id BIGINT UNSIGNED NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (booking_id) REFERENCES {$wpdb->prefix}cwb_bookings (id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}cwb_services (id) ON DELETE RESTRICT
        ) $charset_collate;

        -- Payments Table (NEW)
        CREATE TABLE {$wpdb->prefix}cwb_payments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            booking_id BIGINT UNSIGNED NOT NULL,
            payment_method_id INT UNSIGNED NOT NULL, -- Foreign key to payment_methods
            transaction_id VARCHAR(255),
            amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            tax DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            gratuity DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            payment_status_id INT UNSIGNED NOT NULL DEFAULT 1, -- Foreign key to payment_statuses, default to 'Pending'
            payment_date DATETIME,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_booking_id (booking_id),
            KEY idx_payment_method_id (payment_method_id),
            KEY idx_payment_status_id (payment_status_id),
            FOREIGN KEY (booking_id) REFERENCES {$wpdb->prefix}cwb_bookings (id) ON DELETE CASCADE,
            FOREIGN KEY (payment_method_id) REFERENCES {$wpdb->prefix}cwb_payment_methods (id) ON DELETE RESTRICT,
            FOREIGN KEY (payment_status_id) REFERENCES {$wpdb->prefix}cwb_payment_statuses (id) ON DELETE RESTRICT
        ) $charset_collate;

        -- Booking Status History Table
        CREATE TABLE {$wpdb->prefix}cwb_booking_history (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            booking_id BIGINT UNSIGNED NOT NULL,
            status VARCHAR(20) NOT NULL, -- Still using string status for history for simplicity
            notes TEXT,
            created_by BIGINT UNSIGNED,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES {$wpdb->prefix}cwb_bookings (id) ON DELETE CASCADE,
            KEY idx_booking_id (booking_id)
        ) $charset_collate;

        -- Settings Table
        CREATE TABLE {$wpdb->prefix}cwb_settings (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            category VARCHAR(50),
            value TEXT,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_name (name),
            INDEX idx_setting_category (category)
        ) $charset_collate;

        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Fix: Call the populate_default_data method with proper scope
        self::populate_default_data();
    }

    public static function populate_default_data() {
        global $wpdb;

        // Booking Statuses
        $booking_statuses = [
            ['name' => 'Pending',   'description' => 'Booking request received, awaiting confirmation', 'is_default' => 1],
            ['name' => 'Confirmed', 'description' => 'Booking confirmed and scheduled', 'is_default' => 0],
            ['name' => 'Completed', 'description' => 'Service completed', 'is_default' => 0],
            ['name' => 'Cancelled', 'description' => 'Booking cancelled by customer or admin', 'is_default' => 0],
        ];
        $existing_booking_statuses = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_booking_statuses");
        if ($existing_booking_statuses == 0) {
            foreach ($booking_statuses as $status) {
                $wpdb->insert("{$wpdb->prefix}cwb_booking_statuses", $status, ['%s', '%s', '%d']);
            }
        }

        // Payment Statuses
        $payment_statuses = [
            ['name' => 'Pending',  'description' => 'Payment pending', 'is_default' => 1],
            ['name' => 'Paid',     'description' => 'Payment successfully received', 'is_default' => 0],
            ['name' => 'Failed',   'description' => 'Payment failed', 'is_default' => 0],
            ['name' => 'Refunded', 'description' => 'Payment refunded', 'is_default' => 0],
        ];
        $existing_payment_statuses = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_payment_statuses");
        if ($existing_payment_statuses == 0) {
            foreach ($payment_statuses as $status) {
                $wpdb->insert("{$wpdb->prefix}cwb_payment_statuses", $status, ['%s', '%s', '%d']);
            }
        }

        // Payment Methods
        $payment_methods = [
            ['name' => 'stripe_credit_card', 'code' => 'stripe-cc', 'display_name' => 'Credit Card (Stripe)', 'description' => 'Pay with credit card via Stripe', 'is_active' => 1],
            ['name' => 'paypal_express',     'code' => 'paypal',    'display_name' => 'PayPal Express',      'description' => 'Pay with PayPal Express', 'is_active' => 1],
            // Add more payment methods as needed
        ];
        $existing_payment_methods = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_payment_methods");
        if ($existing_payment_methods == 0) {
            foreach ($payment_methods as $method) {
                $wpdb->insert("{$wpdb->prefix}cwb_payment_methods", $method, ['%s', '%s', '%s', '%s', '%d']);
            }
        }

        // Customer Types (Example)
        $customer_types = [
            ['name' => 'Individual', 'description' => 'Individual customer'],
            ['name' => 'Corporate',  'description' => 'Corporate client'],
        ];
        $existing_customer_types = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_customer_types");
        if ($existing_customer_types == 0) {
            foreach ($customer_types as $type) {
                $wpdb->insert("{$wpdb->prefix}cwb_customer_types", $type, ['%s', '%s']);
            }
        }

        // Booking Sources (Example)
        $booking_sources = [
            ['name' => 'Website',    'description' => 'Bookings made via website form'],
            ['name' => 'Phone Call', 'description' => 'Bookings made via phone call'],
        ];
        $existing_booking_sources = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_booking_sources");
        if ($existing_booking_sources == 0) {
            foreach ($booking_sources as $source) {
                $wpdb->insert("{$wpdb->prefix}cwb_booking_sources", $source, ['%s', '%s']);
            }
        }

        // Marketing Campaigns (Example)
        $marketing_campaigns = [
            ['name' => 'Summer Promo 2025', 'code' => 'SUMMER25', 'start_date' => '2025-06-01', 'end_date' => '2025-08-31', 'description' => 'Summer promotion campaign'],
        ];
        $existing_marketing_campaigns = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_marketing_campaigns");
        if ($existing_marketing_campaigns == 0) {
            foreach ($marketing_campaigns as $campaign) {
                $wpdb->insert("{$wpdb->prefix}cwb_marketing_campaigns", $campaign, ['%s', '%s', '%s', '%s', '%s']);
            }
        }

        // --- Existing Data Population (Review and Adjust as Needed) ---

        // Pre-populate locations if the table is empty.
        $existing_locations = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_locations");
        if ($existing_locations == 0) {
            $wpdb->insert(
                "{$wpdb->prefix}cwb_locations",
                [
                    'name' => 'Remote',
                    'address' => 'Remote Location',
                    'latitude' => '34.0522', // Example latitude
                    'longitude' => '-118.2437' // Example longitude
                ],
                ['%s', '%s', '%f', '%f'] // Added latitude and longitude placeholders
            );

            $wpdb->insert(
                "{$wpdb->prefix}cwb_locations",
                [
                    'name' => 'St. Croix',
                    'address' => 'St. Croix Location',
                    'latitude' => '17.7067', // Example latitude
                    'longitude' => '-64.7444' // Example longitude
                ],
                ['%s', '%s', '%f', '%f'] // Added latitude and longitude placeholders
            );
        }

        // Pre-populate location fields config - NEW DATA POPULATION
        $existing_location_fields_config = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_location_fields_config");
        if ($existing_location_fields_config == 0) {
            $locations = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}cwb_locations", ARRAY_A);
            foreach ($locations as $location) {
                $config = [
                    'location_id' => $location['id'],
                    'field_name' => 'message',
                    'is_required' => 0,
                    'is_visible' => 1,
                ];
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_location_fields_config",
                    $config,
                    ['%d', '%s', '%d', '%d']
                );
                $config = [
                    'location_id' => $location['id'],
                    'field_name' => 'gratuity',
                    'is_required' => 0,
                    'is_visible' => 1,
                ];
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_location_fields_config",
                    $config,
                    ['%d', '%s', '%d', '%d']
                );
                $config = [
                    'location_id' => $location['id'],
                    'field_name' => 'street',
                    'is_required' => 0,
                    'is_visible' => ($location['id'] == 1 ? 1 : 0),
                ];
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_location_fields_config",
                    $config,
                    ['%d', '%s', '%d', '%d']
                );
                $config = [
                    'location_id' => $location['id'],
                    'field_name' => 'zip_code',
                    'is_required' => 0,
                    'is_visible' => ($location['id'] == 1 ? 1 : 0),
                ];
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_location_fields_config",
                    $config,
                    ['%d', '%s', '%d', '%d']
                );
                $config = [
                    'location_id' => $location['id'],
                    'field_name' => 'city',
                    'is_required' => 0,
                    'is_visible' => ($location['id'] == 1 ? 1 : 0),
                ];
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_location_fields_config",
                    $config,
                    ['%d', '%s', '%d', '%d']
                );
                $config = [
                    'location_id' => $location['id'],
                    'field_name' => 'state',
                    'is_required' => 0,
                    'is_visible' => ($location['id'] == 1 ? 1 : 0),
                ];
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_location_fields_config",
                    $config,
                    ['%d', '%s', '%d', '%d']
                );
                $config = [
                    'location_id' => $location['id'],
                    'field_name' => 'country',
                    'is_required' => 0,
                    'is_visible' => ($location['id'] == 1 ? 1 : 0),
                ];
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_location_fields_config",
                    $config,
                    ['%d', '%s', '%d', '%d']
                );
                $config = [
                    'location_id' => $location['id'],
                    'field_name' => 'lat_long',
                    'is_required' => 0,
                    'is_visible' => ($location['id'] == 1 ? 1 : 0),
                ];
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_location_fields_config",
                    $config,
                    ['%d', '%s', '%d', '%d']
                );
                $config = [
                    'location_id' => $location['id'],
                    'field_name' => 'service_location',
                    'is_required' => 0,
                    'is_visible' => ($location['id'] == 2 ? 1 : 0),
                ];
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_location_fields_config",
                    $config,
                    ['%d', '%s', '%d', '%d']
                );
                $config = [
                    'location_id' => $location['id'],
                    'field_name' => 'latitude',
                    'is_required' => ($location['id'] == 2 ? 0 : 1),
                    'is_visible' => ($location['id'] == 2 ? 0 : 1),
                ];
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_location_fields_config",
                    $config,
                    ['%d', '%s', '%d', '%d']
                );
                $config = [
                    'location_id' => $location['id'],
                    'field_name' => 'longitude',
                    'is_required' => ($location['id'] == 2 ? 0 : 1),
                    'is_visible' => ($location['id'] == 2 ? 0 : 1),
                ];
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_location_fields_config",
                    $config,
                    ['%d', '%s', '%d', '%d']
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
                    'name' => 'minimum_booking_time',
                    'value' => '30'
                ],
                ['%s', '%s']
            );
            $wpdb->insert(
                "{$wpdb->prefix}cwb_settings",
                [
                    'name' => 'advance_booking_period',
                    'value' => '7'
                ],
                ['%s', '%s']
            );
            $wpdb->insert(
                "{$wpdb->prefix}cwb_settings",
                [
                    'name' => 'grace_period',
                    'value' => '15'
                ],
                ['%s', '%s']
            );
        }

        // Insert default weekday time ranges if the table is empty.
        $existing_weekday_time_ranges = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_weekday_time_ranges");

        if ($existing_weekday_time_ranges == 0) {
            $weekday_time_ranges = [
                ['weekday' => 'Monday', 'start_time' => '09:00:00', 'end_time' => '12:00:00', 'location_id' => 1],
                ['weekday' => 'Wednesday', 'start_time' => '13:00:00', 'end_time' => '16:00:00', 'location_id' => 1],
                ['weekday' => 'Friday', 'start_time' => '10:00:00', 'end_time' => '14:00:00', 'location_id' => 1]
            ];

            foreach ($weekday_time_ranges as $range) {
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_weekday_time_ranges",
                    $range,
                    ['%s', '%s', '%s', '%d']
                );
            }
        }

        // Insert default specific date time ranges if the table is empty.
        $existing_date_time_ranges = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_date_time_ranges");

        if ($existing_date_time_ranges == 0) {
            $specific_date_ranges = [
                ['date' => '2025-02-05', 'start_time' => '10:00:00', 'end_time' => '12:00:00', 'location_id' => 1], // Special event
                ['date' => '2025-02-06', 'start_time' => '09:00:00', 'end_time' => '11:00:00', 'location_id' => 1]  // Special event
            ];

            foreach ($specific_date_ranges as $range) {
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_date_time_ranges",
                    $range,
                    ['%s', '%s', '%s', '%d']
                );
            }
        }

        // Insert default excluded dates if the table is empty.
        $existing_excluded_dates = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_excluded_dates");

        if ($existing_excluded_dates == 0) {
            $excluded_dates = [
                ['start_date' => '2025-02-07', 'end_date' => '2025-02-07', 'location_id' => 1], // Maintenance day
            ];

            foreach ($excluded_dates as $excluded_date) {
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_excluded_dates",
                    $excluded_date,
                    ['%s', '%s', '%d']
                );
            }
        }

        // Insert default resources if the table is empty.
        $existing_resources = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_resources");

        if ($existing_resources == 0) {
            $resources = [
                ['name' => 'Washer 1', 'capacity' => 2, 'type' => 'equipment', 'location_id' => 1],
                ['name' => 'Washer 2', 'capacity' => 1, 'type' => 'equipment', 'location_id' => 1]
            ];

            foreach ($resources as $resource) {
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_resources",
                    $resource,
                    ['%s', '%d', '%s', '%d']
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
                ['name' => 'Carpet', 'price' => 20.00, 'duration' => 25], // 25 minutes
                ['name' => 'Floor Mats', 'price' => 15.00, 'duration' => 20], // 20 minutes
                ['name' => 'Seats', 'price' => 25.00, 'duration' => 30], // 30 minutes
                ['name' => 'Steering Wheel', 'price' => 10.00, 'duration' => 15], // 15 minutes
                ['name' => 'Door Shuts, Air Vents & Vinyls', 'price' => 20.00, 'duration' => 25], // 25 minutes
                ['name' => 'Engine Clean', 'price' => 40.00, 'duration' => 45], // 45 minutes
                ['name' => 'Headliner', 'price' => 30.00, 'duration' => 35], // 35 minutes
                ['name' => 'Deep Trunk Clean', 'price' => 25.00, 'duration' => 30], // 30 minutes
                ['name' => 'Trunk Vacuum', 'price' => 15.00, 'duration' => 20],    // 20 minutes
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

        // Insert default customers
        $existing_customers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_customers");

        if ($existing_customers == 0) {
            $customers = [
                [
                    'customer_type_id' => 1,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john@example.com',
                    'phone' => '555-123-4567',
                    'is_guest' => 0
                ],
                [
                    'customer_type_id' => 1,
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane@example.com',
                    'phone' => '555-987-6543',
                    'is_guest' => 0
                ],
                [
                    'customer_type_id' => 2,
                    'first_name' => 'Michael',
                    'last_name' => 'Johnson',
                    'email' => 'michael@example.com',
                    'phone' => '555-456-7890',
                    'is_guest' => 0
                ]
            ];

            foreach ($customers as $customer) {
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_customers",
                    $customer,
                    ['%d', '%s', '%s', '%s', '%s']
                );
            }
        }

        // Insert default bookings for testing.
        $existing_bookings = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cwb_bookings");

        if ($existing_bookings == 0) {
            $bookings = [
                [
                    'booking_number' => 'BK-' . uniqid(),
                    'customer_id' => 1,
                    'location_id' => 1,
                    'vehicle_type_id' => 1,
                    'package_id' => 1,
                    'resource_id' => 1,
                    'booking_status_id' => 1,
                    'start_datetime' => '2025-02-05 10:00:00',
                    'end_datetime' => '2025-02-05 10:30:00',
                    'subtotal' => 45.00
                ],
                [
                    'booking_number' => 'BK-' . uniqid(),
                    'customer_id' => 2,
                    'location_id' => 1,
                    'vehicle_type_id' => 1,
                    'package_id' => 1,
                    'resource_id' => 1,
                    'booking_status_id' => 1,
                    'start_datetime' => '2025-02-05 11:00:00',
                    'end_datetime' => '2025-02-05 11:30:00',
                    'subtotal' => 45.00
                ],
                [
                    'booking_number' => 'BK-' . uniqid(),
                    'customer_id' => 3,
                    'location_id' => 1,
                    'vehicle_type_id' => 2,
                    'package_id' => 2,
                    'resource_id' => 2,
                    'booking_status_id' => 1,
                    'start_datetime' => '2025-02-06 09:30:00',
                    'end_datetime' => '2025-02-06 10:00:00',
                    'subtotal' => 50.00
                ],
            ];

            foreach ($bookings as $booking) {
                $wpdb->insert(
                    "{$wpdb->prefix}cwb_bookings",
                    $booking,
                    ['%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%f']
                );
            }
        }
    }
}
