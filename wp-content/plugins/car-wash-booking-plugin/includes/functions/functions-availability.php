<?php
/**
 * Functions for handling service availability
 *
 * @package CarWashBooking
 */

/**
 * Generate available time slots based on provided parameters
 *
 * @param string $startTime        Start time in 24-hour format (HH:MM)
 * @param string $endTime          End time in 24-hour format (HH:MM)
 * @param int    $duration         Service duration in minutes
 * @param int    $gracePeriod      Grace period between appointments in minutes
 * @param object $currentDate      DateTime object for the date we're checking
 * @param object $now              DateTime object for current time
 * @param int    $minimumBookingTime Minimum advance booking time in minutes
 * @return array                   Array of available time slots in HH:MM format
 */
function generate_slots(string $startTime, string $endTime, int $duration, int $gracePeriod, DateTime $currentDate, DateTime $now, int $minimumBookingTime): array {
    $slots = [];

    // Create DateTime objects for start and end times
    $start = (clone $currentDate)->setTime((int)substr($startTime, 0, 2), (int)substr($startTime, 3, 2));
    $end = (clone $currentDate)->setTime((int)substr($endTime, 0, 2), (int)substr($endTime, 3, 2));

    // Ensure slots don't start earlier than minimum booking time
    $minBookingDateTime = (clone $now)->modify("+{$minimumBookingTime} minutes");
    if ($start < $minBookingDateTime) {
        $start = clone $minBookingDateTime;
    }

    // Generate slots
    while ($start < $end) {
        // Calculate when this slot would end
        $slotEnd = (clone $start)->modify("+{$duration} minutes");
        
        // Skip slot if it would end after business hours
        if ($slotEnd > $end) {
            break;
        }

        // Add this time slot
        $slots[] = $start->format('H:i');

        // Move to next potential slot, accounting for duration and grace period
        $start->modify("+{$duration} minutes")->modify("+{$gracePeriod} minutes");
    }

    return $slots;
}

/**
 * Filter slots based on excluded time ranges
 *
 * @param array  $slots       Array of time slots in HH:MM format
 * @param string $excludeStart Start time of exclusion period (HH:MM)
 * @param string $excludeEnd   End time of exclusion period (HH:MM)
 * @return array              Filtered array of time slots
 */
function filter_slots(array $slots, ?string $excludeStart, ?string $excludeEnd): array {
    // If no exclusion period, return all slots
    if (empty($excludeStart) || empty($excludeEnd)) {
        return $slots;
    }
    
    $filteredSlots = [];
    
    foreach ($slots as $slot) {
        $slotTime = new DateTime($slot);

        // Add slot if it falls outside the exclusion period
        if ($slotTime < new DateTime($excludeStart) || $slotTime >= new DateTime($excludeEnd)) {
            $filteredSlots[] = $slot;
        }
    }

    return $filteredSlots;
}

/**
 * Filter slots based on existing bookings and resource availability
 *
 * @param array    $slots           Array of time slots in HH:MM format
 * @param array    $bookings        Array of booking objects
 * @param int      $duration        Service duration in minutes
 * @param int      $resourceCapacity Maximum number of concurrent bookings allowed
 * @param DateTime $currentDate     DateTime object for the date being checked
 * @param int      $gracePeriod     Grace period between appointments in minutes
 * @return array                   Filtered array of available time slots
 */
function filter_booked_slots(array $slots, array $bookings, int $duration, int $resourceCapacity, DateTime $currentDate, int $gracePeriod): array {
    $filteredSlots = [];
    $currentDateFormatted = $currentDate->format('Y-m-d');

    // Filter bookings to only include those on the current date
    $dayBookings = array_filter($bookings, function ($booking) use ($currentDateFormatted) {
        return $booking->date === $currentDateFormatted;
    });
    
    // Check each potential slot
    foreach ($slots as $slot) {
        // Convert slot time to DateTime objects for start and end times
        $slotStart = new DateTime($slot);
        $slotEnd = (clone $slotStart)->modify("+{$duration} minutes");

        // Initialize worker availability - each element represents a worker's schedule
        $workers = array_fill(0, $resourceCapacity, []);

        // First, assign existing bookings to workers
        foreach ($dayBookings as $booking) {
            $bookingStart = new DateTime($booking->start_time);
            $bookingEnd = new DateTime($booking->end_time);
            $adjustedBookingEnd = (clone $bookingEnd)->modify("+{$gracePeriod} minutes");
            
            // If this booking overlaps with our slot's time range
            if (do_times_overlap($slotStart, $slotEnd, $bookingStart, $adjustedBookingEnd, $gracePeriod)) {
                // Try to assign this booking to a worker
                $assigned = false;
                foreach ($workers as &$workerSchedule) {
                    // Check if this worker can handle this booking
                    if (can_worker_handle_booking($workerSchedule, $bookingStart, $bookingEnd, $gracePeriod)) {
                        // Assign booking to this worker
                        $workerSchedule[] = [
                            'start' => $bookingStart, 
                            'end' => $bookingEnd
                        ];
                        $assigned = true;
                        break;
                    }
                }
            }
        }
        
        // Now check if our slot can be allocated to any worker
        $canAssignSlot = false;
        foreach ($workers as $workerSchedule) {
            // Create a booking for our slot
            $slotBooking = [
                'start' => $slotStart,
                'end' => $slotEnd
            ];
            
            // Check if this worker can handle our new slot
            if (can_worker_handle_booking($workerSchedule, $slotStart, $slotEnd, $gracePeriod)) {
                $canAssignSlot = true;
                break;
            }
        }
        
        // If we found a worker who can handle this slot, add it to available slots
        if ($canAssignSlot) {
            $filteredSlots[] = $slot;
        }
    }

    return $filteredSlots;
}

/**
 * Check if a worker can handle a new booking
 *
 * @param array    $workerSchedule Worker's existing bookings
 * @param DateTime $start         Start time of new booking
 * @param DateTime $end           End time of new booking
 * @param int      $gracePeriod   Grace period between bookings in minutes
 * @return bool                   True if worker can handle the booking
 */
function can_worker_handle_booking(array $workerSchedule, DateTime $start, DateTime $end, int $gracePeriod): bool {
    $adjustedEnd = (clone $end)->modify("+{$gracePeriod} minutes");
    
    // Check for conflicts with existing bookings
    foreach ($workerSchedule as $booking) {
        $adjustedBookingEnd = (clone $booking['end'])->modify("+{$gracePeriod} minutes");
        
        // If there's overlap between the new booking and this existing booking
        if (do_times_overlap($start, $adjustedEnd, $booking['start'], $adjustedBookingEnd, 0)) {
            return false;
        }
    }
    
    // No conflicts found
    return true;
}

/**
 * Check if two time ranges overlap
 *
 * @param DateTime $start1       Start of first time range
 * @param DateTime $end1         End of first time range
 * @param DateTime $start2       Start of second time range
 * @param DateTime $end2         End of second time range
 * @param int      $gracePeriod  Additional buffer time to consider
 * @return bool                  True if the time ranges overlap
 */
function do_times_overlap(DateTime $start1, DateTime $end1, DateTime $start2, DateTime $end2, int $gracePeriod): bool {
    $adjustedEnd1 = (clone $end1)->modify("+{$gracePeriod} minutes");
    $adjustedEnd2 = (clone $end2)->modify("+{$gracePeriod} minutes");
    
    // Standard overlap check: one range starts before the other ends
    return $start1 < $adjustedEnd2 && $adjustedEnd1 > $start2;
}
