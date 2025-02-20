<?php
function hwb_get_vehicles() {
    check_ajax_referer('hwb_nonce', 'nonce');

    global $wpdb;
    $location_id = intval($_POST['location_id']);

    // Query for vehicles associated with the location
    $vehicles = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT vt.id, vt.name, vt.icon FROM {$wpdb->prefix}hwb_vehicle_types vt
             JOIN {$wpdb->prefix}hwb_location_vehicle_types lvt ON vt.id = lvt.vehicle_type_id
             WHERE lvt.location_id = %d",
            $location_id
        ),
        ARRAY_A
    );

    // Generate vehicle list HTML
    $html = '';
    if (!empty($vehicles)) {
        foreach ($vehicles as $vehicle) {
            $html .= "<li class='hwb-vehicle' data-id='" . esc_attr($vehicle['id']) . "'>
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

add_action('wp_ajax_hwb_get_vehicles', 'hwb_get_vehicles');
add_action('wp_ajax_nopriv_hwb_get_vehicles', 'hwb_get_vehicles');

/**
 * AJAX handler to fetch packages based on vehicle type.
 */
function hwb_get_packages() {
    check_ajax_referer('hwb_nonce', 'nonce');

    global $wpdb;
    $vehicle_type_id = intval($_POST['vehicle_type_id']);

    $packages = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT p.id, p.name, p.price, p.duration
             FROM {$wpdb->prefix}hwb_packages p
             JOIN {$wpdb->prefix}hwb_vehicle_packages vp ON p.id = vp.package_id
             WHERE vp.vehicle_type_id = %d",
            $vehicle_type_id
        ),
        ARRAY_A
    );

    $html = '';
    foreach ($packages as $package) {
        // Get associated services
        $services = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT s.name
                 FROM {$wpdb->prefix}hwb_services s
                 JOIN {$wpdb->prefix}hwb_package_services ps ON s.id = ps.service_id
                 WHERE ps.package_id = %d",
                $package['id']
            ),
            ARRAY_A
        );

        $serviceListHtml = '<ul class="hwb-package-service-list hwb-list-reset hwb-clear-fix">';
        foreach ($services as $service) {
            $serviceListHtml .= '<li>' . esc_html($service['name']) . '</li>';
        }
        $serviceListHtml .= '</ul>';

        // Format the duration for display
        $formatted_duration = hwb_format_duration($package['duration']);

         $html .= "<li class='hwb-package hwb-package-id-" . esc_attr($package['id']) . "'
                             data-id='" . esc_attr($package['id']) . "'
                             data-duration='" . esc_attr($package['duration']) . "'
                             data-price='" . esc_attr($package['price']) . "'>  <!-- Ensure price is included -->
                             <h5 class='hwb-package-name'>" . esc_html($package['name']) . "</h5>
                             <div class='hwb-package-price'>
                                 <span class='hwb-package-price-currency'>$</span>
                                 <span class='hwb-package-price-unit'>" . esc_html($package['price']) . "</span>
                                 <span class='hwb-package-price-decimal'>00</span>
                             </div>
                             <div class='hwb-package-duration'>
                                 <i class='fa-regular fa-clock'></i>
                                 <span>" . esc_html($formatted_duration) . "</span>
                             </div>
                             $serviceListHtml
                             <div class='hwb-button-box'>
                                 <a class='hwb-button' href='#' onClick='return false;'>Book Now</a>
                             </div>
                         </li>";
    }

    echo $html;
    wp_die();
}

add_action('wp_ajax_hwb_get_packages', 'hwb_get_packages');
add_action('wp_ajax_nopriv_hwb_get_packages', 'hwb_get_packages');

function hwb_get_addons() {
    check_ajax_referer('hwb_nonce', 'nonce');

    global $wpdb;
    $package_id = intval($_POST['package_id']);

    $addons = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT s.id, s.name, s.price, s.duration
             FROM {$wpdb->prefix}hwb_services s
             JOIN {$wpdb->prefix}hwb_package_addons pa ON s.id = pa.service_id
             WHERE pa.package_id = %d",
            $package_id
        ),
        ARRAY_A
    );

    $html = '';
    if (!empty($addons)) {
        $html .= '<ul class="hwb-service-list hwb-list-reset hwb-clear-fix">';
        foreach ($addons as $addon) {
            // Format the duration for display
            $formatted_duration = hwb_format_duration($addon['duration']);

            $html .= "<li class='hwb-clear-fix hwb-service-id-" . esc_attr($addon['id']) . "'
                        data-id='" . esc_attr($addon['id']) . "'
                        data-duration='" . esc_attr($addon['duration']) . "'
                        data-price='" . esc_attr($addon['price']) . "'>
                        <div class='hwb-service-name'>" . esc_html($addon['name']) . "</div>
                        <div class='hwb-service-duration'>
                            <span class='hwb-meta-icon hwb-meta-icon-duration'></span>" . esc_html($formatted_duration) . "
                        </div>
                        <div class='hwb-service-price'>
                            <span class='hwb-meta-icon hwb-meta-icon-price'></span>$" . esc_html($addon['price']) . "
                        </div>
                        <div class='hwb-button-box'>
                            <a class='hwb-button' href='#'>Select</a>
                        </div>
                      </li>";
        }
        $html .= '</ul>';
    } else {
        $html .= '<div class="hwb-disabled">No add-on services available for this package.</div>';
    }

    echo $html;
    wp_die();
}

add_action('wp_ajax_hwb_get_addons', 'hwb_get_addons');
add_action('wp_ajax_nopriv_hwb_get_addons', 'hwb_get_addons');

function hwb_get_available_slots() {
    global $wpdb;

    // Get inputs from AJAX
    $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : null;
    $duration = isset($_POST['package_duration']) ? intval($_POST['package_duration']) : null;

    if (!$date || !$duration) {
        wp_send_json_error(['message' => 'Invalid input data.', 'received_date' => $date, 'received_duration' => $duration]);
    }

    $startDate = new DateTime($date);
    $endDate = (clone $startDate)->modify('+6 days');
    $now = new DateTime(); // Current date and time
    $responseSlots = [];

    // Fetch settings (minimum booking time, grace period, advance booking period)
    $settings = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}hwb_settings LIMIT 1");
    $minimumBookingTime = $settings ? intval($settings->minimum_booking_time) : 0;
    $advanceBookingPeriod = $settings ? intval($settings->advance_booking_period) : 30;
    $gracePeriod = $settings ? intval($settings->grace_period) : 0;

    // Calculate the latest booking date allowed
    $latestBookingDate = (clone $now)->modify("+{$advanceBookingPeriod} days");

    // Fetch weekday time ranges
    $weekdayRanges = $wpdb->get_results("
        SELECT weekday, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time
        FROM {$wpdb->prefix}hwb_weekday_time_ranges
    ");

    // Fetch specific date time ranges
    $dateRanges = $wpdb->get_results("
        SELECT date, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time
        FROM {$wpdb->prefix}hwb_date_time_ranges
        WHERE date BETWEEN '{$startDate->format('Y-m-d')}' AND '{$endDate->format('Y-m-d')}'
    ", OBJECT_K);

    // Fetch excluded dates and times
    $excludedDates = $wpdb->get_results("
        SELECT start_date, end_date, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time
        FROM {$wpdb->prefix}hwb_excluded_dates
    ");

    // Fetch existing bookings
    $bookings = $wpdb->get_results("
        SELECT date, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time
        FROM {$wpdb->prefix}hwb_bookings
        WHERE date BETWEEN '{$startDate->format('Y-m-d')}' AND '{$endDate->format('Y-m-d')}'
    ");

    // Fetch resource capacity
    $resourceCapacity = $wpdb->get_var("SELECT SUM(capacity) FROM {$wpdb->prefix}hwb_resources");

    // Generate slots for each day in the date range
    for ($i = 0; $i < 7; $i++) {
        $currentDate = clone $startDate;
        $currentDate->modify("+{$i} days");

        // Skip days beyond `advance_booking_period`
        if ($currentDate > $latestBookingDate) {
            continue;
        }

        $dayOfWeek = $currentDate->format('l'); // e.g., Monday, Tuesday
        $slots = [];

        // Check if there are specific date time ranges for the current day
        if (isset($dateRanges[$currentDate->format('Y-m-d')])) {
            // Use specific date time ranges for this day (takes precedence over everything)
            $specificRange = $dateRanges[$currentDate->format('Y-m-d')];
            $slots = generate_slots(
                $specificRange->start_time,
                $specificRange->end_time,
                $duration,
                $gracePeriod,
                $currentDate,
                $now,
                $minimumBookingTime
            );
        } else {
            // Use weekday ranges if no specific date range is set
            foreach ($weekdayRanges as $range) {
                if (strcasecmp($range->weekday, $dayOfWeek) === 0) { // Case-insensitive comparison
//                 wp_send_json_error($range->weekday);

                    $slots = array_merge($slots, generate_slots(
                        $range->start_time,
                        $range->end_time,
                        $duration,
                        $gracePeriod,
                        $currentDate,
                        $now,
                        $minimumBookingTime
                    ));
                }
            }

            // Remove excluded times
            foreach ($excludedDates as $exclude) {
                $excludeStart = new DateTime($exclude->start_date);
                $excludeEnd = new DateTime($exclude->end_date);

                if ($currentDate >= $excludeStart && $currentDate <= $excludeEnd) {
                    $slots = filter_slots($slots, $exclude->start_time, $exclude->end_time);
                }
            }
        }

        // Remove booked slots
        foreach ($bookings as $booking) {
            if ($booking->date === $currentDate->format('Y-m-d')) {
                $slots = filter_booked_slots($slots, $bookings, $duration, $resourceCapacity, $currentDate, $gracePeriod);
            }
        }

        $responseSlots[$currentDate->format('Y-m-d')] = $slots;
    }

    // Send JSON response
    wp_send_json_success(['slots' => $responseSlots]);
}

add_action('wp_ajax_hwb_get_available_slots', 'hwb_get_available_slots');
add_action('wp_ajax_nopriv_hwb_get_available_slots', 'hwb_get_available_slots');

// Update `generate_slots` to include `minimum_booking_time`
function generate_slots($startTime, $endTime, $duration, $gracePeriod, $currentDate, $now, $minimumBookingTime) {
    $slots = [];

    // Parse start and end times into DateTime objects for the specific date
    $start = (clone $currentDate)->setTime(substr($startTime, 0, 2), substr($startTime, 3, 2));
    $end = (clone $currentDate)->setTime(substr($endTime, 0, 2), substr($endTime, 3, 2));

    // Ensure we are working with the correct date range
    if ($start < $now->modify("+{$minimumBookingTime} minutes")) {
        $start = (clone $now)->modify("+{$minimumBookingTime} minutes");
    }

    while ($start < $end) {
        // Calculate the end of the current slot
        $slotEnd = (clone $start)->modify("+{$duration} minutes")->modify("+{$gracePeriod} minutes");
        if ($slotEnd > $end) {
            break; // Stop if the slot doesn't fit within the range
        }

        // Add the slot if it is valid
        $slots[] = $start->format('H:i');

        // Move to the next slot
        $start->modify("+{$duration} minutes")->modify("+{$gracePeriod} minutes");
    }

    return $slots;
}

// Helper function to filter slots for excluded dates and times
function filter_slots($slots, $excludeStart, $excludeEnd) {
    $filteredSlots = [];
    foreach ($slots as $slot) {
        $slotTime = new DateTime($slot);

        // If excludeStart or excludeEnd is null, treat it as a full-day exclusion
        if ($excludeStart && $excludeEnd) {
            if ($slotTime < new DateTime($excludeStart) || $slotTime >= new DateTime($excludeEnd)) {
                $filteredSlots[] = $slot; // Add slot if it doesn't fall within the exclusion period
            }
        } else {
            // Full-day exclusion
            $filteredSlots = [];
            break; // Skip all slots if it's a full-day exclusion
        }
    }

    return $filteredSlots;
}

// Helper function to filter booked slots with capacity
function filter_booked_slots($slots, $bookings, $duration, $resourceCapacity, $currentDate, $gracePeriod) {
    $filteredSlots = [];
    $currentDateFormatted = $currentDate->format('Y-m-d'); // Format the current day for comparison

    // Filter bookings for the current day
    $dayBookings = array_filter($bookings, function ($booking) use ($currentDateFormatted) {
        return $booking->date === $currentDateFormatted; // Only keep bookings for the current day
    });

    foreach ($slots as $slot) {
        $slotStart = new DateTime($slot);
        $slotEnd = (clone $slotStart)->modify("+{$duration} minutes");

        // Initialize workers array for each slot iteration
        $workers = array_fill(0, $resourceCapacity, []); // Each worker starts with an empty schedule

        foreach ($dayBookings as $booking) {
            $bookingStart = new DateTime($booking->start_time);
            $bookingEnd = new DateTime($booking->end_time);
            $adjustedBookingEnd = (clone $bookingEnd)->modify("+{$gracePeriod} minutes");
            $adjustedSlotEnd = (clone $slotEnd)->modify("+{$gracePeriod} minutes");

            // Check if the booking overlaps with the current slot

            // Time Slot: 10:00 AM - 11:15 AM
            // Booking: 10:00 AM - 10:30 AM
            // Booking: 10:30 AM - 11:00 AM

            // 10:30 - 11:00
            // 10:45 - 11:15
            // 9:30 - 10:00
            // Booking: 10:00 AM - 10:45 AM
            // Booking: 10:00 AM - 10:45 AM

            // Loop through the bookings
            // Check to see if the booking overlaps with the current slot
            // If the slot does not conflict with any of the bookings, add the slot to filteredSlots
            // If the slot does conflict with one of the bookings, loop through the workers
            // While looping through the workers, check to see if the slot conflicts with any of the worker's assigned times
            // If the slot does not conflict with any of the worker's assigned times, assign the booking to the worker
            // If the slot does conflict with any of the worker's assigned times, continue to the next worker
            // If the slot does conflict with all of the workers' assigned times, do not add the slot to filteredSlots

            if ($slotStart < $adjustedBookingEnd && $adjustedSlotEnd > $bookingStart) {
                // Try to assign this booking to a worker
                foreach ($workers as &$workerSchedule) {
                    $conflict = false;
                    foreach ($workerSchedule as $assignedTime) {
                        $adjustedAssignedTimeEnd = (clone $assignedTime['end'])->modify("+{$gracePeriod} minutes");
                        if (($bookingStart < $adjustedAssignedTimeEnd && $adjustedBookingEnd > $assignedTime['start'])) {
                            $conflict = true;
                            break;
                        }
                    }

                    if (!$conflict) {
                        // Assign the booking to this worker
                        $workerSchedule[] = ['start' => $bookingStart, 'end' => $bookingEnd];
                        break;
                    }
                }
            }
        }

        // Check if at least one worker can take on the time slot
        $canAssignSlot = false;
        foreach ($workers as $workerSchedule) {
            $conflict = false;
            $adjustedSlotEnd = (clone $slotEnd)->modify("+{$gracePeriod} minutes");
            foreach ($workerSchedule as $assignedTime) {
                $adjustedAssignedTimeEnd = (clone $assignedTime['end'])->modify("+{$gracePeriod} minutes");
                if (($slotStart < $adjustedAssignedTimeEnd && $adjustedSlotEnd > $assignedTime['start'])) {
                    $conflict = true;
                    break;
                }
            }
            if (!$conflict) {
                $canAssignSlot = true;
                break;
            }
        }

        // Add the slot only if at least one worker can take it
        if ($canAssignSlot) {
            $filteredSlots[] = $slot;
        }
    }

    return $filteredSlots;
}

// function hwb_get_available_slots() {
//     if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hwb_nonce')) {
//         wp_send_json_error(['message' => 'Invalid nonce.'], 400);
//     }
//
//     // Get parameters from the request
//     $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : null;
//     $package_duration = isset($_POST['package_duration']) ? intval($_POST['package_duration']) : null;
//
//     if (!$date || !$package_duration) {
//         wp_send_json_error(['message' => 'Missing required parameters.'], 400);
//     }
//
//     // For now, return hardcoded time slots
//     $hardcoded_slots = [
//         '2025-01-26' => ['09:00', '10:00', '11:00', '14:00', '16:00'],
//         '2025-01-27' => ['09:30', '10:30', '12:00', '13:30'],
//         '2025-01-28' => ['10:00', '11:00', '13:00', '15:00'],
//         '2025-01-29' => ['09:00', '10:30', '12:00', '14:30', '16:00'],
//         '2025-01-30' => [],
//         '2025-02-01' => ['09:00', '11:00', '13:00', '15:00'],
//         '2025-02-02' => ['08:30', '10:30', '12:30', '14:30']
//     ];
//
//     // Format the response slots for the requested date
//     $response_slots = [];
//     $start_date = new DateTime($date);
//     for ($i = 0; $i < 7; $i++) {
//         $current_date = $start_date->format('Y-m-d');
//         $response_slots[$current_date] = isset($hardcoded_slots[$current_date]) ? $hardcoded_slots[$current_date] : [];
//         $start_date->modify('+1 day');
//     }
//
//     // Send JSON response
//     wp_send_json_success(['slots' => $response_slots]);
// }
//
// add_action('wp_ajax_hwb_get_available_slots', 'hwb_get_available_slots');
// add_action('wp_ajax_nopriv_hwb_get_available_slots', 'hwb_get_available_slots');

// Helper function to format duration
function hwb_format_duration($duration) {
    $hours = floor($duration / 60);
    $minutes = $duration % 60;

    if ($hours > 0 && $minutes > 0) {
        return "{$hours}h {$minutes}min";
    } elseif ($hours > 0) {
        return "{$hours}h";
    } else {
        return "{$minutes}min";
    }
}

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}
?>
