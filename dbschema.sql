-- Booking Statuses Table
CREATE TABLE {prefix}cwb_booking_statuses (
                                              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                              name VARCHAR(50) NOT NULL UNIQUE COMMENT 'e.g., Pending, Confirmed, Cancelled, Completed',
    description VARCHAR(255),
    is_default TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Is this the default status?',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_is_default (is_default)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Payment Statuses Table
CREATE TABLE {prefix}cwb_payment_statuses (
                                              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                              name VARCHAR(50) NOT NULL UNIQUE COMMENT 'e.g., Pending, Paid, Failed, Refunded',
    description VARCHAR(255),
    is_default TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Is this the default status?',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_is_default (is_default)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Payment Methods Table
CREATE TABLE {prefix}cwb_payment_methods (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Locations Table
CREATE TABLE {prefix}cwb_locations (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Location Fields Configuration
CREATE TABLE {prefix}cwb_location_fields_config (
                                                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                                    location_id BIGINT UNSIGNED NOT NULL,
                                                    field_name VARCHAR(50) NOT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY unique_location_field (location_id, field_name),
    FOREIGN KEY (location_id) REFERENCES {prefix}cwb_locations (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Vehicle Types Table
CREATE TABLE {prefix}cwb_vehicle_types (
                                           id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                           name VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Link table for locations and vehicle types
CREATE TABLE {prefix}cwb_location_vehicle_types (
                                                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                                    location_id BIGINT UNSIGNED NOT NULL,
                                                    vehicle_type_id BIGINT UNSIGNED NOT NULL,
                                                    UNIQUE KEY unique_location_vehicle (location_id, vehicle_type_id),
    FOREIGN KEY (location_id) REFERENCES {prefix}cwb_locations (id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_type_id) REFERENCES {prefix}cwb_vehicle_types (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Packages Table
CREATE TABLE {prefix}cwb_packages (
                                      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                      name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    duration INT UNSIGNED NOT NULL COMMENT 'Duration in minutes',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    KEY idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Package Pricing Table
CREATE TABLE {prefix}cwb_package_pricing (
                                             id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                             package_id BIGINT UNSIGNED NOT NULL,
                                             location_id BIGINT UNSIGNED,
                                             start_date DATE,
                                             end_date DATE,
                                             weekday VARCHAR(10),
    start_time TIME,
    end_time TIME,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (package_id) REFERENCES {prefix}cwb_packages (id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES {prefix}cwb_locations (id) ON DELETE CASCADE,
    INDEX idx_package_date_range (package_id, start_date, end_date),
    INDEX idx_package_weekday_time (package_id, weekday, start_time, end_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Services Table (for package inclusions and add-ons)
CREATE TABLE {prefix}cwb_services (
                                      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                      name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    duration INT UNSIGNED NOT NULL COMMENT 'Duration in minutes',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    KEY idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Service Pricing Table
CREATE TABLE {prefix}cwb_service_pricing (
                                             id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                             service_id BIGINT UNSIGNED NOT NULL,
                                             location_id BIGINT UNSIGNED,
                                             start_date DATE,
                                             end_date DATE,
                                             weekday VARCHAR(10),
    start_time TIME,
    end_time TIME,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (service_id) REFERENCES {prefix}cwb_services (id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES {prefix}cwb_locations (id) ON DELETE CASCADE,
    INDEX idx_service_date_range (service_id, start_date, end_date),
    INDEX idx_service_weekday_time (service_id, weekday, start_time, end_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Package-Service Association (services included in packages)
CREATE TABLE {prefix}cwb_package_services (
                                              id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                              package_id BIGINT UNSIGNED NOT NULL,
                                              service_id BIGINT UNSIGNED NOT NULL,
                                              UNIQUE KEY unique_package_service (package_id, service_id),
    FOREIGN KEY (package_id) REFERENCES {prefix}cwb_packages (id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES {prefix}cwb_services (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Package-AddOn Association (available add-ons for packages)
CREATE TABLE {prefix}cwb_package_addons (
                                            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                            package_id BIGINT UNSIGNED NOT NULL,
                                            service_id BIGINT UNSIGNED NOT NULL,
                                            UNIQUE KEY unique_package_addon (package_id, service_id),
    FOREIGN KEY (package_id) REFERENCES {prefix}cwb_packages (id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES {prefix}cwb_services (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Vehicle-Package Association
CREATE TABLE {prefix}cwb_vehicle_packages (
                                              id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                              vehicle_type_id BIGINT UNSIGNED NOT NULL,
                                              package_id BIGINT UNSIGNED NOT NULL,
                                              UNIQUE KEY unique_vehicle_package (vehicle_type_id, package_id),
    FOREIGN KEY (vehicle_type_id) REFERENCES {prefix}cwb_vehicle_types (id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES {prefix}cwb_packages (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Resources Table (staff, equipment, etc.)
CREATE TABLE {prefix}cwb_resources (
                                       id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                       name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    capacity INT UNSIGNED NOT NULL DEFAULT 1,
    location_id BIGINT UNSIGNED NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (location_id) REFERENCES {prefix}cwb_locations (id) ON DELETE CASCADE,
    KEY idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Resource Availability Table
CREATE TABLE {prefix}cwb_resource_availability (
                                                   id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                                   resource_id BIGINT UNSIGNED NOT NULL,
                                                   date DATE NOT NULL,
                                                   start_time TIME NOT NULL,
                                                   end_time TIME NOT NULL,
                                                   is_available TINYINT(1) NOT NULL DEFAULT 1,
    reason VARCHAR(255),
    FOREIGN KEY (resource_id) REFERENCES {prefix}cwb_resources (id) ON DELETE CASCADE,
    UNIQUE KEY unique_resource_date_time (resource_id, date, start_time, end_time),
    INDEX idx_resource_date (resource_id, date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Customers Table
CREATE TABLE {prefix}cwb_customers (
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
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_email (email),
    KEY idx_user_id (user_id),
    KEY idx_customer_type_id (customer_type_id),
    FOREIGN KEY (customer_type_id) REFERENCES {prefix}cwb_customer_types (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Customer Types Table
CREATE TABLE {prefix}cwb_customer_types (
                                            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                            name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Bookings Table
CREATE TABLE {prefix}cwb_bookings (
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
    FOREIGN KEY (customer_id) REFERENCES {prefix}cwb_customers (id) ON DELETE RESTRICT,
    FOREIGN KEY (location_id) REFERENCES {prefix}cwb_locations (id) ON DELETE RESTRICT,
    FOREIGN KEY (vehicle_type_id) REFERENCES {prefix}cwb_vehicle_types (id) ON DELETE RESTRICT,
    FOREIGN KEY (package_id) REFERENCES {prefix}cwb_packages (id) ON DELETE RESTRICT,
    FOREIGN KEY (resource_id) REFERENCES {prefix}cwb_resources (id) ON DELETE SET NULL,
    FOREIGN KEY (booking_status_id) REFERENCES {prefix}cwb_booking_statuses (id) ON DELETE RESTRICT,
    FOREIGN KEY (booking_source_id) REFERENCES {prefix}cwb_booking_sources(id) ON DELETE SET NULL,
    FOREIGN KEY (marketing_campaign_id) REFERENCES {prefix}cwb_marketing_campaigns(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Booking Services Table
CREATE TABLE {prefix}cwb_booking_services (
                                              id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                              booking_id BIGINT UNSIGNED NOT NULL,
                                              service_id BIGINT UNSIGNED NOT NULL,
                                              quantity INT UNSIGNED NOT NULL DEFAULT 1,
                                              price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES {prefix}cwb_bookings (id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES {prefix}cwb_services (id) ON DELETE RESTRICT,
    UNIQUE KEY unique_booking_service (booking_id, service_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Booking Add-ons Table
CREATE TABLE {prefix}cwb_booking_addons (
                                            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                            booking_id BIGINT UNSIGNED NOT NULL,
                                            service_id BIGINT UNSIGNED NOT NULL,
                                            price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES {prefix}cwb_bookings (id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES {prefix}cwb_services (id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Payments Table (NEW)
CREATE TABLE {prefix}cwb_payments (
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
    FOREIGN KEY (booking_id) REFERENCES {prefix}cwb_bookings (id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES {prefix}cwb_payment_methods (id) ON DELETE RESTRICT,
    FOREIGN KEY (payment_status_id) REFERENCES {prefix}cwb_payment_statuses (id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Booking Status History Table
CREATE TABLE {prefix}cwb_booking_history (
                                             id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                             booking_id BIGINT UNSIGNED NOT NULL,
                                             status VARCHAR(20) NOT NULL, -- Still using string status for history for simplicity
    notes TEXT,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES {prefix}cwb_bookings (id) ON DELETE CASCADE,
    KEY idx_booking_id (booking_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Booking Sources Table
CREATE TABLE {prefix}cwb_booking_sources (
                                             id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                             name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Marketing Campaigns Table
CREATE TABLE {prefix}cwb_marketing_campaigns (
                                                 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                                 name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE,
    start_date DATE,
    end_date DATE,
    description TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Weekday Time Ranges (availability)
CREATE TABLE {prefix}cwb_weekday_time_ranges (
                                                 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                                 weekday VARCHAR(10) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (location_id) REFERENCES {prefix}cwb_locations (id) ON DELETE CASCADE,
    KEY idx_weekday (weekday)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Date-specific Time Ranges (overrides weekday settings)
CREATE TABLE {prefix}cwb_date_time_ranges (
                                              id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                              date DATE NOT NULL,
                                              start_time TIME NOT NULL,
                                              end_time TIME NOT NULL,
                                              location_id BIGINT UNSIGNED NOT NULL,
                                              FOREIGN KEY (location_id) REFERENCES {prefix}cwb_locations (id) ON DELETE CASCADE,
    KEY idx_date (date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Excluded Dates (holidays, closures)
CREATE TABLE {prefix}cwb_excluded_dates (
                                            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                            start_date DATE NOT NULL,
                                            end_date DATE NOT NULL,
                                            start_time TIME,
                                            end_time TIME,
                                            reason VARCHAR(255),
    location_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (location_id) REFERENCES {prefix}cwb_locations (id) ON DELETE CASCADE,
    KEY idx_date_range (start_date, end_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- Settings Table
CREATE TABLE {prefix}cwb_settings (
                                      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                      name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    value TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name (name),
    INDEX idx_setting_category (category)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;