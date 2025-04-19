<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

include_once plugin_dir_path(__FILE__) . '../functions/functions-availability.php';

class CWB_Booking {
    public static function get_available_slots( $date, $duration ) {
        global $wpdb;

        $startDate = new DateTime($date);
        $endDate = (clone $startDate)->modify('+6 days');
        $now = new DateTime();
        $responseSlots = [];

        $minimumBookingTime = intval($wpdb->get_var("SELECT value FROM {$wpdb->prefix}cwb_settings WHERE name = 'minimum_booking_time'") ?: 30);
        $advanceBookingPeriod = intval($wpdb->get_var("SELECT value FROM {$wpdb->prefix}cwb_settings WHERE name = 'advance_booking_period'") ?: 7);
        $gracePeriod = intval($wpdb->get_var("SELECT value FROM {$wpdb->prefix}cwb_settings WHERE name = 'grace_period'") ?: 15);

        $latestBookingDate = (clone $now)->modify("+{$advanceBookingPeriod} days");

        $weekdayRanges = $wpdb->get_results("
            SELECT weekday, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time
            FROM {$wpdb->prefix}cwb_weekday_time_ranges
        ");

        $dateRanges = $wpdb->get_results("
            SELECT date, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time
            FROM {$wpdb->prefix}cwb_date_time_ranges
            WHERE date BETWEEN '{$startDate->format('Y-m-d')}' AND '{$endDate->format('Y-m-d')}'
        ", OBJECT_K);

        $excludedDates = $wpdb->get_results("
            SELECT start_date, end_date, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time
            FROM {$wpdb->prefix}cwb_excluded_dates
        ");

        $bookings = $wpdb->get_results("
            SELECT
                DATE(b.start_datetime) as date,
                TIME_FORMAT(TIME(b.start_datetime), '%H:%i') AS start_time,
                TIME_FORMAT(TIME(DATE_ADD(b.start_datetime, INTERVAL 
                    CASE 
                        WHEN b.package_id IS NOT NULL THEN (SELECT duration FROM {$wpdb->prefix}cwb_packages WHERE id = b.package_id)
                        ELSE 0
                    END MINUTE)), '%H:%i') AS end_time
            FROM {$wpdb->prefix}cwb_bookings b
            WHERE b.start_datetime BETWEEN '{$startDate->format('Y-m-d')} 00:00:00' AND '{$endDate->format('Y-m-d')} 23:59:59'
        ");

        $resourceCapacity = $wpdb->get_var("SELECT SUM(capacity) FROM {$wpdb->prefix}cwb_resources");

        for ($i = 0; $i < 7; $i++) {

            $currentDate = clone $startDate;
            $currentDate->modify("+{$i} days");

            if ($currentDate > $latestBookingDate) {
                continue;
            }

            $dayOfWeek = $currentDate->format('l');
            $slots = [];

            if (isset($dateRanges[$currentDate->format('Y-m-d')])) {
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
                foreach ($weekdayRanges as $range) {
                    if (strcasecmp($range->weekday, $dayOfWeek) === 0) {
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

                foreach ($excludedDates as $exclude) {
                    $excludeStart = new DateTime($exclude->start_date);
                    $excludeEnd = new DateTime($exclude->end_date);

                    if ($currentDate >= $excludeStart && $currentDate <= $excludeEnd) {
                        $slots = filter_slots($slots, $exclude->start_time, $exclude->end_time);
                    }
                }
            }

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
