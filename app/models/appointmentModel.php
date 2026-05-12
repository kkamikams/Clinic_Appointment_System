<?php

class appointmentModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // ─────────────────────────────────────────────
    //  LIST  (action=list)
    //  Returns: { rows, total, stats, page, limit }
    // ─────────────────────────────────────────────

    public function list($filters = [])
    {
        $date    = $filters['date']    ?? '';
        $search  = $filters['search']  ?? '';
        $status  = $filters['status']  ?? '';
        $channel = $filters['channel'] ?? '';
        $doctor  = $filters['doctor']  ?? '';
        $page    = max(1, (int)($filters['page'] ?? 1));
        $limit   = 15;
        $offset  = ($page - 1) * $limit;

        $where  = [];
        $types  = '';
        $params = [];

        if ($date) {
            $where[]  = 'a.appointmentDate = ?';
            $types   .= 's';
            $params[] = $date;
        }
        if ($search) {
            $like     = '%' . $search . '%';
            $where[]  = '(CONCAT(p.firstName," ",p.lastName) LIKE ? OR CONCAT(d.firstName," ",d.lastName) LIKE ? OR a.appointmentCode LIKE ?)';
            $types   .= 'sss';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($status) {
            $where[]  = 'a.status = ?';
            $types   .= 's';
            $params[] = $status;
        }
        if ($channel) {
            $where[]  = 'a.channel = ?';
            $types   .= 's';
            $params[] = $channel;
        }
        if ($doctor) {
            $where[]  = 'a.doctorId = ?';
            $types   .= 'i';
            $params[] = (int)$doctor;
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // ✅ Base query WITHOUT followUps — used for COUNT and stats
        $baseSQL = "
        FROM appointments a
        JOIN patients p ON p.id = a.patientId
        JOIN doctors  d ON d.id = a.doctorId
        $whereSQL
    ";

        // Total count
        $countStmt = $this->conn->prepare("SELECT COUNT(*) $baseSQL");
        if ($params) $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $total = (int) $countStmt->get_result()->fetch_row()[0];

        // Stats
        $statsStmt = $this->conn->prepare("
        SELECT
            COUNT(*)                         AS total,
            SUM(a.status = 'Completed')      AS Completed,
            SUM(a.status = 'Pending')        AS Pending,
            SUM(a.status = 'In Progress')    AS InProgress,
            SUM(a.status = 'Cancelled')      AS Cancelled
        $baseSQL
    ");
        if ($params) $statsStmt->bind_param($types, ...$params);
        $statsStmt->execute();
        $statsRow = $statsStmt->get_result()->fetch_assoc();
        $stats = [
            'total'       => (int) $statsRow['total'],
            'Completed'   => (int) $statsRow['Completed'],
            'Pending'     => (int) $statsRow['Pending'],
            'In Progress' => (int) $statsRow['InProgress'],
            'Cancelled'   => (int) $statsRow['Cancelled'],
        ];

        // ✅ Rows — followUps joined HERE only, with GROUP BY to prevent duplicates
        $rowTypes  = $types . 'ii';
        $rowParams = array_merge($params, [$limit, $offset]);

        $rowStmt = $this->conn->prepare("
    SELECT
        a.id,
        a.appointmentCode,
        a.appointmentDate,
        a.appointmentTime,
        a.status,
        a.channel,
        a.remarks,
        a.patientId,
        a.doctorId,
        CONCAT(p.firstName, ' ', p.lastName)  AS patientName,
        CONCAT(d.firstName, ' ', d.lastName)  AS doctorName,
        d.specialization,
        fu.id                                 AS followUpId,
        fu.followUpDate,
        (fu.id IS NOT NULL)                   AS isFollowUp
    FROM appointments a
    JOIN patients p ON p.id = a.patientId
    JOIN doctors  d ON d.id = a.doctorId
    LEFT JOIN followUps fu ON fu.appointmentId = a.id
    $whereSQL
    GROUP BY a.id
    ORDER BY a.appointmentDate DESC, a.appointmentTime ASC
    LIMIT ? OFFSET ?
");

        $rowStmt->bind_param($rowTypes, ...$rowParams);
        $rowStmt->execute();
        $rows = $rowStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return compact('rows', 'total', 'stats', 'page', 'limit');
    }

    // ─────────────────────────────────────────────
    //  GET SINGLE  (action=get)
    // ─────────────────────────────────────────────

    public function get($id)
    {
        $stmt = $this->conn->prepare("
            SELECT
                a.*,
                CONCAT(p.firstName, ' ', p.lastName) AS patientName,
                CONCAT(d.firstName, ' ', d.lastName) AS doctorName,
                d.specialization
            FROM appointments a
            JOIN patients p ON p.id = a.patientId
            JOIN doctors  d ON d.id = a.doctorId
            WHERE a.id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // ─────────────────────────────────────────────
    //  ADD  (action=add)
    // ─────────────────────────────────────────────

    public function add($data)
    {
        // Create new patient inline if needed
        if (empty($data['patientId']) && !empty($data['patientName'])) {
            $patientId = $this->createPatient($data);
            if (!$patientId) return false;
        } else {
            $patientId = (int) $data['patientId'];
        }

        $code = $this->generateCode('APPT');

        $stmt = $this->conn->prepare("
            INSERT INTO appointments
                (appointmentCode, patientId, doctorId, appointmentDate, appointmentTime, channel, status, remarks)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'siisssss',
            $code,
            $patientId,
            $data['doctorId'],
            $data['appointmentDate'],
            $data['appointmentTime'],
            $data['channel'],
            $data['status'],
            $data['remarks']
        );

        if (!$stmt->execute()) return false;

        $newId = $this->conn->insert_id;
        logActivity($this->conn, 'appointment_created', "Appointment {$code} created.");
        return $newId;
    }

    // ─────────────────────────────────────────────
    //  EDIT  (action=edit)
    // ─────────────────────────────────────────────

    public function edit($data)
    {
        $stmt = $this->conn->prepare("
            UPDATE appointments
            SET doctorId        = ?,
                appointmentDate = ?,
                appointmentTime = ?,
                channel         = ?,
                status          = ?,
                remarks         = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            'isssssi',
            $data['doctorId'],
            $data['appointmentDate'],
            $data['appointmentTime'],
            $data['channel'],
            $data['status'],
            $data['remarks'],
            $data['id']
        );

        if (!$stmt->execute()) return false;
        logActivity($this->conn, 'appointment_updated', "Appointment #{$data['id']} updated.");
        return true;
    }

    // ─────────────────────────────────────────────
    //  UPDATE STATUS  (action=update_status)
    //  Returns updated stats for the current filter
    // ─────────────────────────────────────────────

    public function updateStatus($id, $status)
    {
        $stmt = $this->conn->prepare(
            "UPDATE appointments SET status = ? WHERE id = ?"
        );
        $stmt->bind_param('si', $status, $id);
        if (!$stmt->execute()) return false;
        logActivity($this->conn, 'appointment_status', "Appointment #{$id} status → {$status}.");
        return true;
    }

    public function getStats($date = '')
    {
        if ($date) {
            $stmt = $this->conn->prepare("
                SELECT
                    COUNT(*)                         AS total,
                    SUM(status = 'Completed')        AS Completed,
                    SUM(status = 'Pending')          AS Pending,
                    SUM(status = 'In Progress')      AS InProgress,
                    SUM(status = 'Cancelled')        AS Cancelled
                FROM appointments
                WHERE appointmentDate = ?
            ");
            $stmt->bind_param('s', $date);
        } else {
            $stmt = $this->conn->prepare("
                SELECT
                    COUNT(*)                         AS total,
                    SUM(status = 'Completed')        AS Completed,
                    SUM(status = 'Pending')          AS Pending,
                    SUM(status = 'In Progress')      AS InProgress,
                    SUM(status = 'Cancelled')        AS Cancelled
                FROM appointments
            ");
        }
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        return [
            'total'       => (int) $r['total'],
            'Completed'   => (int) $r['Completed'],
            'Pending'     => (int) $r['Pending'],
            'In Progress' => (int) $r['InProgress'],
            'Cancelled'   => (int) $r['Cancelled'],
        ];
    }

    // ─────────────────────────────────────────────
    //  CANCEL  (action=cancel)
    // ─────────────────────────────────────────────

    public function cancel($id)
    {
        $status = 'Cancelled';
        $stmt   = $this->conn->prepare(
            "UPDATE appointments SET status = ? WHERE id = ?"
        );
        $stmt->bind_param('si', $status, $id);
        if (!$stmt->execute()) return false;
        logActivity($this->conn, 'appointment_cancelled', "Appointment #{$id} cancelled.");
        return true;
    }

    // ─────────────────────────────────────────────
    //  FOLLOW-UPS  (action=get_followup / edit_followup)
    // ─────────────────────────────────────────────

    public function getFollowUp($id)
    {
        $stmt = $this->conn->prepare("
            SELECT
                fu.*,
                CONCAT(p.firstName, ' ', p.lastName) AS patientName
            FROM followUps fu
            JOIN patients p ON p.id = fu.patientId
            WHERE fu.id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function editFollowUp($data)
    {
        $stmt = $this->conn->prepare("
            UPDATE followUps
            SET doctorId     = ?,
                followUpDate = ?,
                status       = ?,
                reason       = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            'isssi',
            $data['doctorId'],
            $data['appointmentDate'],
            $data['status'],
            $data['remarks'],
            $data['id']
        );
        if (!$stmt->execute()) return false;
        logActivity($this->conn, 'followup_updated', "Follow-up #{$data['id']} updated.");
        return true;
    }

    // ─────────────────────────────────────────────
    //  DOCTORS DROPDOWN  (action=get_doctors)
    // ─────────────────────────────────────────────

    public function getDoctors()
    {
        return $this->conn->query("
            SELECT
                id,
                CONCAT(firstName, ' ', lastName) AS name,
                specialization
            FROM doctors
            WHERE employmentStatus = 'Active'
            ORDER BY firstName ASC
        ")->fetch_all(MYSQLI_ASSOC);
    }

    // ─────────────────────────────────────────────
    //  PATIENT SEARCH  (action=get_patients)
    // ─────────────────────────────────────────────

    public function searchPatients($q)
    {
        $like = '%' . $q . '%';
        $stmt = $this->conn->prepare("
            SELECT
                id,
                patientCode,
                CONCAT(firstName, ' ', lastName) AS name,
                contactNumber                    AS contact,
                dateOfBirth                      AS dob
            FROM patients
            WHERE status = 'Active'
              AND (CONCAT(firstName, ' ', lastName) LIKE ? OR patientCode LIKE ?)
            ORDER BY firstName ASC
            LIMIT 20
        ");
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ─────────────────────────────────────────────
    //  TIME SLOTS  (action=get_slots)
    // ─────────────────────────────────────────────

    public function getSlots($doctorId, $date)
    {
        // Get doctor's shift for that day
        $day  = date('l', strtotime($date));
        $stmt = $this->conn->prepare("
            SELECT shiftStart, shiftEnd
            FROM doctorSchedules
            WHERE doctorId = ? AND dayOfWeek = ?
            LIMIT 1
        ");
        $stmt->bind_param('is', $doctorId, $day);
        $stmt->execute();
        $schedule = $stmt->get_result()->fetch_assoc();

        if (!$schedule) return [];

        // Get already-booked times for this doctor on this date
        $bookedStmt = $this->conn->prepare("
            SELECT appointmentTime
            FROM appointments
            WHERE doctorId = ? AND appointmentDate = ? AND status != 'Cancelled'
        ");
        $bookedStmt->bind_param('is', $doctorId, $date);
        $bookedStmt->execute();
        $booked = array_column(
            $bookedStmt->get_result()->fetch_all(MYSQLI_ASSOC),
            'appointmentTime'
        );

        // Generate 30-min slots between shiftStart and shiftEnd
        $slots   = [];
        $current = strtotime($date . ' ' . $schedule['shiftStart']);
        $end     = strtotime($date . ' ' . $schedule['shiftEnd']);

        while ($current < $end) {
            $value     = date('H:i', $current);
            $label     = date('g:i A', $current);
            $available = !in_array($value . ':00', $booked) && !in_array($value, $booked);
            $slots[]   = compact('value', 'label', 'available');
            $current  += 1800; // 30 minutes
        }

        return $slots;
    }

    // ─────────────────────────────────────────────
    //  LINKED MEDICAL RECORD  (action=get_linked_record)
    // ─────────────────────────────────────────────

    public function getLinkedRecord($apptId)
    {
        $stmt = $this->conn->prepare("
            SELECT recordCode, diagnosis, recordType, status
            FROM medicalRecords
            WHERE appointmentId = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $apptId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // ─────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────

    private function createPatient($data)
    {
        $nameParts  = explode(' ', trim($data['patientName']), 3);
        $firstName  = $nameParts[0] ?? '';
        $middleName = count($nameParts) === 3 ? $nameParts[1] : '';
        $lastName   = $nameParts[count($nameParts) - 1] ?? '';
        $code       = $this->generateCode('PAT');

        $stmt = $this->conn->prepare("
            INSERT INTO patients
                (patientCode, firstName, middleName, lastName, dateOfBirth, gender, contactNumber, email, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')
        ");
        $stmt->bind_param(
            'ssssssss',
            $code,
            $firstName,
            $middleName,
            $lastName,
            $data['patientDOB'],
            $data['patientGender'],
            $data['patientContact'],
            $data['patientEmail']
        );

        return $stmt->execute() ? $this->conn->insert_id : null;
    }

    private function generateCode($prefix)
    {
        return strtoupper($prefix) . '-' . strtoupper(substr(uniqid(), -6));
    }
}
