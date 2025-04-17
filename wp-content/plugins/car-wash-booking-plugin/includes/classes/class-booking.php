<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

include_once plugin_dir_path(__FILE__) . '../functions/functions-availability.php';

class CWB_Booking {
    /**
     * Get available booking slots for a given date and duration.
     *
     * @param string $date     Date in YYYY-MM-DD format.
     * @param int    $duration Booking duration in minutes.
     * @return array Array of available time slots (in HH:MM format).
     */
    public static function get_available_slots( $date, $duration ) {
        global $wpdb;

        $startDate = new DateTime($date);
        $endDate = (clone $startDate)->modify('+6 days');
        $now = new DateTime(); // Current date and time
        $responseSlots = [];

        // Fetch settings individually by name (fixes the property access issue)
        $minimumBookingTime = intval($wpdb->get_var("SELECT value FROM {$wpdb->prefix}cwb_settings WHERE name = 'minimum_booking_time'") ?: 30);
        $advanceBookingPeriod = intval($wpdb->get_var("SELECT value FROM {$wpdb->prefix}cwb_settings WHERE name = 'advance_booking_period'") ?: 7);
        $gracePeriod = intval($wpdb->get_var("SELECT value FROM {$wpdb->prefix}cwb_settings WHERE name = 'grace_period'") ?: 15);

        // Calculate the latest booking date allowed
        $latestBookingDate = (clone $now)->modify("+{$advanceBookingPeriod} days");

        // Fetch weekday time ranges
        $weekdayRanges = $wpdb->get_results("
            SELECT weekday, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time
            FROM {$wpdb->prefix}cwb_weekday_time_ranges
        ");

        // Fetch specific date time ranges
        $dateRanges = $wpdb->get_results("
            SELECT date, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time
            FROM {$wpdb->prefix}cwb_date_time_ranges
            WHERE date BETWEEN '{$startDate->format('Y-m-d')}' AND '{$endDate->format('Y-m-d')}'
        ", OBJECT_K);

        // Fetch excluded dates and times
        $excludedDates = $wpdb->get_results("
            SELECT start_date, end_date, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time
            FROM {$wpdb->prefix}cwb_excluded_dates
        ");

        // Fetch existing bookings - UPDATED QUERY
        $bookings = $wpdb->get_results("
            SELECT
                DATE(start_datetime) as date,
                TIME_FORMAT(TIME(start_datetime), '%H:%i') AS start_time,
                TIME_FORMAT(TIME(end_datetime), '%H:%i') AS end_time
            FROM {$wpdb->prefix}cwb_bookings
            WHERE start_datetime BETWEEN '{$startDate->format('Y-m-d')} 00:00:00' AND '{$endDate->format('Y-m-d')} 23:59:59'
        ");

        // Fetch resource capacity
        $resourceCapacity = $wpdb->get_var("SELECT SUM(capacity) FROM {$wpdb->prefix}cwb_resources");

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
                    $duration, // Use $duration directly
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
                            $duration, // Use $duration directly
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
                    $slots = filter_booked_slots($slots, $bookings, $duration, $resourceCapacity, $currentDate, $gracePeriod); // Use $duration directly
                }
            }

            $responseSlots[$currentDate->format('Y-m-d')] = $slots;
        }

        return $responseSlots;
    }
}