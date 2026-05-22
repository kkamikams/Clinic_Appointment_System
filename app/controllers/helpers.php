<?php

// Records an action to the recentActivity table (e.g. appointment booked, patient updated)
function logActivity($conn, $type, $desc, $refId = null, $refType = null)
{
    $stmt = $conn->prepare(
        "INSERT INTO recentActivity (activityType, description, referenceId, referenceType) 
         VALUES (?,?,?,?)"
    );
    $stmt->bind_param('ssis', $type, $desc, $refId, $refType);
    $stmt->execute();
}

// Generates a unique sequential code like APP-2025-0012 based on the highest existing number
function generateAppointmentCode($conn): string
{
    $max = (int) $conn->query(
        "SELECT MAX(CAST(SUBSTRING_INDEX(appointmentCode, '-', -1) AS UNSIGNED)) 
         FROM appointments"
    )->fetch_row()[0];
    return 'APP-' . date('Y') . '-' . str_pad($max + 1, 4, '0', STR_PAD_LEFT);
}

// Returns a doctor's weekly schedule sorted Monday–Sunday
function getDoctorSchedule($conn, int $doctorId): array
{
    $stmt = $conn->prepare("
    SELECT dayOfWeek, shiftStart, shiftEnd
    FROM doctorSchedules
    WHERE doctorId = ?
    ORDER BY FIELD(dayOfWeek,'Monday','Tuesday','Wednesday',
                   'Thursday','Friday','Saturday','Sunday')
");
    $stmt->bind_param('i', $doctorId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Builds 30-minute time slots for a doctor on a given date, marking each as available or booked
function getAvailableSlots($conn, int $doctorId, string $date): array
{
    $dow      = date('l', strtotime($date));
    $stmt = $conn->prepare("
    SELECT shiftStart, shiftEnd FROM doctorSchedules
    WHERE doctorId = ? AND dayOfWeek = ?
    LIMIT 1
");
    $stmt->bind_param('is', $doctorId, $dow);
    $stmt->execute();
    $schedule = $stmt->get_result()->fetch_assoc();

    if (!$schedule) return [];

    $stmt2 = $conn->prepare("
    SELECT TIME_FORMAT(appointmentTime,'%H:%i') AS t
    FROM appointments
    WHERE doctorId = ?
      AND appointmentDate = ?
      AND status != 'Cancelled'
");
    $stmt2->bind_param('is', $doctorId, $date);
    $stmt2->execute();
    $booked = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    $bookedTimes = array_column($booked, 't');

    $slots    = [];
    $start    = strtotime($date . ' ' . $schedule['shiftStart']);
    $end      = strtotime($date . ' ' . $schedule['shiftEnd']);
    $interval = 30 * 60;

    // Generate a slot every 30 minutes between shift start and end
    for ($t = $start; $t < $end; $t += $interval) {
        $hhmm    = date('H:i', $t);
        $isToday  = ($date === date('Y-m-d'));
        $isPast   = $isToday && ($t <= time());
        $slots[] = [
            'value'     => $hhmm,
            'label'     => date('g:i A', $t),
            'available' => !in_array($hhmm, $bookedTimes) && !$isPast,
            'past'      => $isPast,
        ];
    }
    return $slots;
}
