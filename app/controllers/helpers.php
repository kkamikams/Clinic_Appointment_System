<?php

function logActivity($conn, $type, $desc, $refId = null, $refType = null)
{
    $stmt = $conn->prepare(
        "INSERT INTO recentActivity (activityType, description, referenceId, referenceType) 
         VALUES (?,?,?,?)"
    );
    $stmt->bind_param('ssis', $type, $desc, $refId, $refType);
    $stmt->execute();
}

function generateAppointmentCode($conn): string
{
    $max = (int) $conn->query(
        "SELECT MAX(CAST(SUBSTRING_INDEX(appointmentCode, '-', -1) AS UNSIGNED)) 
         FROM appointments"
    )->fetch_row()[0];
    return 'APP-' . date('Y') . '-' . str_pad($max + 1, 4, '0', STR_PAD_LEFT);
}

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

    for ($t = $start; $t < $end; $t += $interval) {
        $hhmm    = date('H:i', $t);
        $slots[] = [
            'value'     => $hhmm,
            'label'     => date('g:i A', $t),
            'available' => !in_array($hhmm, $bookedTimes),
        ];
    }
    return $slots;
}
