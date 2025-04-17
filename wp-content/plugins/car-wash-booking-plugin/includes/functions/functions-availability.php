<?php

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

        $slotEnd = (clone $start)->modify("+{$duration} minutes");
//         $slotEnd = (clone $start)->modify("+{$duration} minutes")->modify("+{$gracePeriod} minutes");
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