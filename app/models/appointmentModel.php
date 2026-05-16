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

        // ── Appointment branch filters ──────────────────
        $aWhere  = [];
        $aTypes  = '';
        $aParams = [];

        if ($date) {
            $aWhere[]  = 'a.appointmentDate = ?';
            $aTypes   .= 's';
            $aParams[] = $date;
        }
        if ($search) {
            $like      = '%' . $search . '%';
            $aWhere[]  = '(CONCAT(p.firstName," ",p.lastName) LIKE ? OR CONCAT(d.firstName," ",d.lastName) LIKE ? OR a.appointmentCode LIKE ?)';
            $aTypes   .= 'sss';
            $aParams[] = $like;
            $aParams[] = $like;
            $aParams[] = $like;
        }
        if ($status) {
            $aWhere[]  = 'a.status = ?';
            $aTypes   .= 's';
            $aParams[] = $status;
        }
        if ($channel && $channel !== 'Follow-up') {
            $aWhere[]  = 'a.channel = ?';
            $aTypes   .= 's';
            $aParams[] = $channel;
        }
        if ($doctor) {
            $aWhere[]  = 'a.doctorId = ?';
            $aTypes   .= 'i';
            $aParams[] = (int)$doctor;
        }
        $aWhereSQL = $aWhere ? 'WHERE ' . implode(' AND ', $aWhere) : '';

        // ── Follow-up branch filters ────────────────────
        $fWhere  = [];
        $fTypes  = '';
        $fParams = [];

        if ($date) {
            $fWhere[]  = 'fu.followUpDate = ?';
            $fTypes   .= 's';
            $fParams[] = $date;
        }
        if ($search) {
            $like      = '%' . $search . '%';
            $fWhere[]  = '(CONCAT(p.firstName," ",p.lastName) LIKE ? OR CONCAT(d.firstName," ",d.lastName) LIKE ? OR fu.followUpCode LIKE ?)';
            $fTypes   .= 'sss';
            $fParams[] = $like;
            $fParams[] = $like;
            $fParams[] = $like;
        }
        if ($status) {
            $fWhere[]  = 'fu.status = ?';
            $fTypes   .= 's';
            $fParams[] = $status;
        }
        if ($doctor) {
            $fWhere[]  = 'fu.doctorId = ?';
            $fTypes   .= 'i';
            $fParams[] = (int)$doctor;
        }
        $fWhereSQL = $fWhere ? 'WHERE ' . implode(' AND ', $fWhere) : '';

        // ── Branch SQL ──────────────────────────────────
        $apptBranch = "
    SELECT
        a.id,
        a.appointmentCode  AS appointmentCode,
        a.appointmentDate  AS appointmentDate,
        a.appointmentTime  AS appointmentTime,
        a.status           AS status,
        a.channel          AS channel,
        a.remarks          AS remarks,
        a.address          AS appointmentAddress,
        a.patientId        AS patientId,
        a.doctorId         AS doctorId,
        CONCAT(p.firstName,' ',p.lastName) AS patientName,
        CONCAT(d.firstName,' ',d.lastName) AS doctorName,
        d.specialization   AS specialization,
        NULL               AS followUpId,
        NULL               AS fuCode,
        NULL               AS fuDate,
        0                  AS isFollowUp
    FROM appointments a
    JOIN patients p ON p.id = a.patientId
    JOIN doctors  d ON d.id = a.doctorId
    $aWhereSQL
";
        $fuBranch = "
    SELECT
        a.id,
        fu.followUpCode                    AS appointmentCode,
        fu.followUpDate                    AS appointmentDate,
        fu.followUpTime                    AS appointmentTime,
        IFNULL(fu.status, 'Pending')       AS status,
        'Follow-up'                        AS channel,
        fu.reason                          AS remarks,
        a.address                          AS appointmentAddress,
        fu.patientId,
        COALESCE(fu.doctorId, a.doctorId)  AS doctorId,
        CONCAT(p.firstName,' ',p.lastName) AS patientName,
        CONCAT(COALESCE(fd.firstName, ad.firstName, ''),' ',COALESCE(fd.lastName, ad.lastName, '')) AS doctorName,
        COALESCE(fd.specialization, ad.specialization, '—') AS specialization,
        fu.id                              AS followUpId,
        fu.followUpCode                    AS fuCode,
        fu.followUpDate                    AS fuDate,
        1                                  AS isFollowUp
    FROM followUps fu
    JOIN appointments a  ON a.id  = fu.appointmentId
    JOIN patients     p  ON p.id  = fu.patientId
    LEFT JOIN doctors fd ON fd.id = fu.doctorId
    LEFT JOIN doctors ad ON ad.id = a.doctorId
    $fWhereSQL
";

        // ── Choose which branches to include ────────────
        if ($channel === 'Follow-up') {
            $unionSQL    = $fuBranch;
            $unionTypes  = $fTypes;
            $unionParams = $fParams;
        } elseif ($channel) {
            // specific non-follow-up channel: appointments only
            $unionSQL    = $apptBranch;
            $unionTypes  = $aTypes;
            $unionParams = $aParams;
        } else {
            // no channel filter: both
            $unionSQL    = "($apptBranch) UNION ALL ($fuBranch)";
            $unionTypes  = $aTypes . $fTypes;
            $unionParams = array_merge($aParams, $fParams);
        }

        // ── Stats (appointments only, no channel filter) ─
        $sWhere  = [];
        $sTypes  = '';
        $sParams = [];
        if ($date) {
            $sWhere[] = 'a.appointmentDate = ?';
            $sTypes .= 's';
            $sParams[] = $date;
        }
        if ($doctor) {
            $sWhere[] = 'a.doctorId = ?';
            $sTypes .= 'i';
            $sParams[] = (int)$doctor;
        }
        $sWhereSQL = $sWhere ? 'WHERE ' . implode(' AND ', $sWhere) : '';

        $statsStmt = $this->conn->prepare("
        SELECT
            COUNT(*)                      AS total,
            SUM(a.status='Completed')     AS Completed,
            SUM(a.status='Pending')       AS Pending,
            SUM(a.status='In Progress')   AS InProgress,
            SUM(a.status='Cancelled')     AS Cancelled
        FROM appointments a
        JOIN patients p ON p.id = a.patientId
        JOIN doctors  d ON d.id = a.doctorId
        $sWhereSQL
    ");
        if ($sParams) $statsStmt->bind_param($sTypes, ...$sParams);
        $statsStmt->execute();
        $sRow  = $statsStmt->get_result()->fetch_assoc();
        $stats = [
            'total'       => (int)$sRow['total'],
            'Completed'   => (int)$sRow['Completed'],
            'Pending'     => (int)$sRow['Pending'],
            'In Progress' => (int)$sRow['InProgress'],
            'Cancelled'   => (int)$sRow['Cancelled'],
        ];

        // ── Total count ─────────────────────────────────
        $countStmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM ($unionSQL) AS combined"
        );
        if ($unionParams) $countStmt->bind_param($unionTypes, ...$unionParams);
        $countStmt->execute();
        $total = (int)$countStmt->get_result()->fetch_row()[0];

        // ── Paginated rows ──────────────────────────────
        $rowTypes  = $unionTypes . 'ii';
        $rowParams = array_merge($unionParams, [$limit, $offset]);
        $rowStmt   = $this->conn->prepare("
    SELECT
        id, appointmentCode, appointmentDate, appointmentTime,
        status, channel, remarks, appointmentAddress,
        patientId, doctorId, patientName, doctorName, specialization,
        followUpId, fuCode, fuDate, isFollowUp
    FROM ($unionSQL) AS combined
    ORDER BY appointmentDate DESC, appointmentTime ASC
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
                a.address AS appointmentAddress,
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
                (appointmentCode, patientId, doctorId, appointmentDate, appointmentTime, channel, status, remarks, address)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'siissssss',
            $code,
            $patientId,
            $data['doctorId'],
            $data['appointmentDate'],
            $data['appointmentTime'],
            $data['channel'],
            $data['status'],
            $data['remarks'],
            $data['patientAddress']
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

    public function updateFollowUpStatus($id, $status)
    {
        $stmt = $this->conn->prepare(
            "UPDATE followUps SET status = ? WHERE id = ?"
        );
        $stmt->bind_param('si', $status, $id);
        if (!$stmt->execute()) return false;
        logActivity($this->conn, 'followup_status', "Follow-up #{$id} status → {$status}.");
        return true;
    }

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
        fu.id,
        fu.appointmentId,
        fu.followUpCode,
        fu.followUpDate,
        fu.followUpTime,
        fu.followUpTime AS appointmentTime,
        fu.patientId,
        fu.status,
        fu.reason,
        fu.createdAt,
            CONCAT(p.firstName, ' ', p.lastName) AS patientName,
            COALESCE(NULLIF(fu.doctorId, 0), a.doctorId)    AS effectiveDoctorId,
            CONCAT(
                COALESCE(fd.firstName, ad.firstName, ''), ' ',
                COALESCE(fd.lastName,  ad.lastName,  '')
            ) AS doctorName,
            COALESCE(fd.specialization, ad.specialization, '—') AS specialization
        FROM followUps fu
        JOIN appointments a  ON a.id  = fu.appointmentId
        JOIN patients     p  ON p.id  = fu.patientId
        LEFT JOIN doctors fd ON fd.id = fu.doctorId
        LEFT JOIN doctors ad ON ad.id = a.doctorId
        WHERE fu.id = ?
    ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            $row['doctorId'] = $row['effectiveDoctorId'];
            $row['resolvedDoctorId'] = $row['effectiveDoctorId'];
        }
        return $row;
    }
    public function editFollowUp($data)
    {
        $stmt = $this->conn->prepare("
            UPDATE followUps
            SET doctorId     = ?,
                followUpDate = ?,
                followUpTime = ?,
                status       = ?,
                reason       = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            'issssi',
            $data['doctorId'],
            $data['appointmentDate'],
            $data['appointmentTime'],
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
                dateOfBirth                      AS dob,
                address
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

        $slots   = [];
        $current = strtotime($date . ' ' . $schedule['shiftStart']);
        $end     = strtotime($date . ' ' . $schedule['shiftEnd']);

        while ($current < $end) {
            $value     = date('H:i', $current);
            $label     = date('g:i A', $current);
            $available = !in_array($value . ':00', $booked) && !in_array($value, $booked);
            $slots[]   = compact('value', 'label', 'available');
            $current  += 1800;
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
