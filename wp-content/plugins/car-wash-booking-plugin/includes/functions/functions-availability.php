<?php

function generate_slots($startTime, $endTime, $duration, $gracePeriod, $currentDate, $now, $minimumBookingTime) {
    $slots = [];

    $start = (clone $currentDate)->setTime(substr($startTime, 0, 2), substr($startTime, 3, 2));
    $end = (clone $currentDate)->setTime(substr($endTime, 0, 2), substr($endTime, 3, 2));

    if ($start < $now->modify("+{$minimumBookingTime} minutes")) {
        $start = (clone $now)->modify("+{$minimumBookingTime} minutes");
    }

    while ($start < $end) {

        $slotEnd = (clone $start)->modify("+{$duration} minutes");
        if ($slotEnd > $end) {
            break;
        }

        $slots[] = $start->format('H:i');

        $start->modify("+{$duration} minutes")->modify("+{$gracePeriod} minutes");
    }

    return $slots;
}

function filter_slots($slots, $excludeStart, $excludeEnd) {
    $filteredSlots = [];
    foreach ($slots as $slot) {
        $slotTime = new DateTime($slot);

        if ($excludeStart && $excludeEnd) {
            if ($slotTime < new DateTime($excludeStart) || $slotTime >= new DateTime($excludeEnd)) {
                $filteredSlots[] = $slot;
            }
        } else {
            $filteredSlots = [];
            break;
        }
    }

    return $filteredSlots;
}

function filter_booked_slots($slots, $bookings, $duration, $resourceCapacity, $currentDate, $gracePeriod) {
    $filteredSlots = [];
    $currentDateFormatted = $currentDate->format('Y-m-d');

    $dayBookings = array_filter($bookings, function ($booking) use ($currentDateFormatted) {
        return $booking->date === $currentDateFormatted;
    });

    foreach ($slots as $slot) {
        $slotStart = new DateTime($slot);
        $slotEnd = (clone $slotStart)->modify("+{$duration} minutes");

        $workers = array_fill(0, $resourceCapacity, []);

        foreach ($dayBookings as $booking) {
            $bookingStart = new DateTime($booking->start_time);
            $bookingEnd = new DateTime($booking->end_time);
            $adjustedBookingEnd = (clone $bookingEnd)->modify("+{$gracePeriod} minutes");
            $adjustedSlotEnd = (clone $slotEnd)->modify("+{$gracePeriod} minutes");

            if ($slotStart < $adjustedBookingEnd && $adjustedSlotEnd > $bookingStart) {
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
                        $workerSchedule[] = ['start' => $bookingStart, 'end' => $bookingEnd];
                        break;
                    }
                }
            }
        }

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

        if ($canAssignSlot) {
            $filteredSlots[] = $slot;
        }
    }

    return $filteredSlots;
}