<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Render the booking form
 */
function hwb_booking_form_shortcode() {
    // Fetch data for dropdowns
    $locations = hwb_get_locations();
    $vehicle_types = hwb_get_vehicle_types();
    $packages = hwb_get_packages();

    ob_start();
    ?>
    <form id="hwb-booking-form" method="post" action="">
        <h2>Car Wash Booking Form</h2>

        <!-- Location Dropdown -->
        <label for="location_id">Select Location:</label>
        <select id="location_id" name="location_id" required>
            <option value="">Choose a Location</option>
            <?php foreach ( $locations as $location ): ?>
                <option value="<?php echo esc_attr( $location['id'] ); ?>"><?php echo esc_html( $location['name'] ); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Vehicle Type Dropdown -->
        <label for="vehicle_type_id">Select Vehicle Type:</label>
        <select id="vehicle_type_id" name="vehicle_type_id" required>
            <option value="">Choose a Vehicle Type</option>
            <?php foreach ( $vehicle_types as $vehicle_type ): ?>
                <option value="<?php echo esc_attr( $vehicle_type['id'] ); ?>"><?php echo esc_html( $vehicle_type['name'] ); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Package Dropdown -->
        <label for="package_id">Select Package:</label>
        <select id="package_id" name="package_id" required>
            <option value="">Choose a Package</option>
            <?php foreach ( $packages as $package ): ?>
                <option value="<?php echo esc_attr( $package['id'] ); ?>"><?php echo esc_html( $package['name'] ); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Date and Time -->
        <label for="date">Select Date:</label>
        <input type="date" id="date" name="date" required>

        <label for="time">Select Time:</label>
        <input type="time" id="time" name="time" required>

        <!-- PII Fields -->
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" required>

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="phone_number">Phone Number:</label>
        <input type="text" id="phone_number" name="phone_number" required>

        <label for="vehicle_make_model">Vehicle Make & Model:</label>
        <input type="text" id="vehicle_make_model" name="vehicle_make_model" required>

        <!-- Submit Button -->
        <button type="submit" name="hwb_submit">Submit Booking</button>
    </form>
    <?php

    // Handle form submission
    if ( isset( $_POST['hwb_submit'] ) ) {
        $data = [
            'location_id' => intval( $_POST['location_id'] ),
            'vehicle_type_id' => intval( $_POST['vehicle_type_id'] ),
            'package_id' => intval( $_POST['package_id'] ),
            'date' => sanitize_text_field( $_POST['date'] ),
            'time' => sanitize_text_field( $_POST['time'] ),
            'first_name' => sanitize_text_field( $_POST['first_name'] ),
            'last_name' => sanitize_text_field( $_POST['last_name'] ),
            'street' => '',
            'zip_code' => '',
            'city' => '',
            'state' => '',
            'country' => '',
            'email' => sanitize_email( $_POST['email'] ),
            'phone_number' => sanitize_text_field( $_POST['phone_number'] ),
            'vehicle_make_model' => sanitize_text_field( $_POST['vehicle_make_model'] ),
            'message' => '',
            'gratuity' => 0,
            'payment_type' => 'Cash',
            'privacy_policy_accepted' => 1,
            'terms_accepted' => 1,
            'total_price' => 0,
        ];

        $booking_id = hwb_insert_booking( $data );

        if ( $booking_id ) {
            echo '<p>Booking successfully created with ID: ' . esc_html( $booking_id ) . '</p>';
        } else {
            echo '<p>There was an error creating your booking. Please try again.</p>';
        }
    }

    return ob_get_clean();
}
add_shortcode( 'hwb_booking_form', 'hwb_booking_form_shortcode' );