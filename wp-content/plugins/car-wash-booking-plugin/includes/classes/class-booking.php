<?php
/**
 * Booking class for Car Wash Booking system
 *
 * @package Car_Wash_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . '../functions/functions-availability.php';

/**
 * CWB_Booking class - Handles booking functionality and availability checking
 */
class CWB_Booking {
    /**
     * Get available time slots for a given date and service duration
     * 
     * @param string $date     The starting date in Y-m-d format
     * @param int    $duration The service duration in minutes
     * 
     * @return array Available time slots organized by date
     */
    public static function get_available_slots( $date, $duration ) {
        global $wpdb;

        // Validate inputs
        if (!$date || !strtotime($date)) {
            return ['error' => 'Invalid date provided'];
        }
        
        if (!is_numeric($duration) || $duration <= 0) {
            return ['error' => 'Invalid duration provided'];
        }

        try {
            $startDate = new DateTime($date);
            $endDate = (clone $startDate)->modify('+6 days');
            $now = new DateTime();
            $responseSlots = [];

            // Get system settings
            $settings = self::get_system_settings($wpdb);
            $minimumBookingTime = $settings['minimum_booking_time'];
            $advanceBookingPeriod = $settings['advance_booking_period'];
            $gracePeriod = $settings['grace_period'];
            $latestBookingDate = (clone $now)->modify("+{$advanceBookingPeriod} days");

            // Get availability data from database
            $weekdayRanges = self::get_weekday_ranges($wpdb);
            $dateRanges = self::get_date_ranges($wpdb, $startDate, $endDate);
            $excludedDates = self::get_excluded_dates($wpdb);
            $bookings = self::get_existing_bookings($wpdb, $startDate, $endDate);
            $resourceCapacity = self::get_resource_capacity($wpdb);

            // Generate available slots for each day in the range
            for ($i = 0; $i < 7; $i++) {
                $currentDate = clone $startDate;
                $currentDate->modify("+{$i} days");

                if ($currentDate > $latestBookingDate) {
                    continue;
                }

                $dayOfWeek = $currentDate->format('l');
                $formattedDate = $currentDate->format('Y-m-d');
                $slots = [];

                // Check for date-specific time ranges
                if (isset($dateRanges[$formattedDate])) {
                    $specificRange = $dateRanges[$formattedDate];
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
                    // Use standard weekday ranges
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

                    // Filter out excluded dates/times
                    foreach ($excludedDates as $exclude) {
                        $excludeStart = new DateTime($exclude->start_date);
                        $excludeEnd = new DateTime($exclude->end_date);

                        if ($currentDate >= $excludeStart && $currentDate <= $excludeEnd) {
                            $slots = filter_slots($slots, $exclude->start_time, $exclude->end_time);
                        }
                    }
                }

                // Filter out already booked slots
                foreach ($bookings as $booking) {
                    if ($booking->date === $formattedDate) {
                        $slots = filter_booked_slots(
                            $slots, 
                            $bookings, 
                            $duration, 
                            $resourceCapacity, 
                            $currentDate, 
                            $gracePeriod
                        );
                    }
                }

                $responseSlots[$formattedDate] = $slots;
            }

            return $responseSlots;
            
        } catch (Exception $e) {
            return ['error' => 'An error occurred while processing dates: ' . $e->getMessage()];
        }
    }

    /**
     * Get system settings from database
     * 
     * @param object $wpdb WordPress database object
     * 
     * @return array System settings
     */
    private static function get_system_settings($wpdb) {
        $settings = $wpdb->get_results("
            SELECT name, value 
            FROM {$wpdb->prefix}cwb_settings 
            WHERE name IN ('minimum_booking_time', 'advance_booking_period', 'grace_period')
        ", OBJECT_K);
        
        return [
            'minimum_booking_time' => isset($settings['minimum_booking_time']) ? 
                intval($settings['minimum_booking_time']->value) : 30,
            'advance_booking_period' => isset($settings['advance_booking_period']) ? 
                intval($settings['advance_booking_period']->value) : 7,
            'grace_period' => isset($settings['grace_period']) ? 
                intval($settings['grace_period']->value) : 15
        ];
    }

    /**
     * Get weekday time ranges from database
     * 
     * @param object $wpdb WordPress database object
     * 
     * @return array Weekday time ranges
     */
    private static function get_weekday_ranges($wpdb) {
        return $wpdb->get_results("
            SELECT weekday, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time
            FROM {$wpdb->prefix}cwb_weekday_time_ranges
        ");
    }

    /**
     * Get date-specific time ranges from database
     * 
     * @param object $wpdb      WordPress database object
     * @param object $startDate Start date object
     * @param object $endDate   End date object
     * 
     * @return array Date-specific time ranges
     */
    private static function get_date_ranges($wpdb, $startDate, $endDate) {
        return $wpdb->get_results("
            SELECT date, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time
            FROM {$wpdb->prefix}cwb_date_time_ranges
            WHERE date BETWEEN '{$startDate->format('Y-m-d')}' AND '{$endDate->format('Y-m-d')}'
        ", OBJECT_K);
    }

    /**
     * Get excluded dates from database
     * 
     * @param object $wpdb WordPress database object
     * 
     * @return array Excluded dates
     */
    private static function get_excluded_dates($wpdb) {
        return $wpdb->get_results("
            SELECT start_date, end_date, TIME_FORMAT(start_time, '%H:%i') AS start_time, TIME_FORMAT(end_time, '%H:%i') AS end_time
            FROM {$wpdb->prefix}cwb_excluded_dates
        ");
    }

    /**
     * Get existing bookings from database
     * 
     * @param object $wpdb      WordPress database object
     * @param object $startDate Start date object
     * @param object $endDate   End date object
     * 
     * @return array Existing bookings
     */
    private static function get_existing_bookings($wpdb, $startDate, $endDate) {
        return $wpdb->get_results("
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
    }

    /**
     * Get total resource capacity from database
     * 
     * @param object $wpdb WordPress database object
     * 
     * @return int Total resource capacity
     */
    private static function get_resource_capacity($wpdb) {
        $capacity = $wpdb->get_var("SELECT SUM(capacity) FROM {$wpdb->prefix}cwb_resources");
        return $capacity ? intval($capacity) : 1; // Default to 1 if no resources defined
    }
}
