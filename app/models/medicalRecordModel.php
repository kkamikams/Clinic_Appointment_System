<?php

class MedicalRecordModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getStats(): array
    {
        $today = date('Y-m-d');
        $ym    = date('Y-m');

        return [
            'total'         => (int) $this->conn->query("SELECT COUNT(*) FROM medicalRecords WHERE parentRecordId IS NULL")->fetch_row()[0],
            'today'         => (int) $this->conn->query("SELECT COUNT(*) FROM medicalRecords WHERE DATE(updatedAt)='$today'")->fetch_row()[0],
            'labPending'    => (int) $this->conn->query("SELECT COUNT(*) FROM medicalRecords WHERE recordType='Lab Result' AND status='Draft'")->fetch_row()[0],
            'prescriptions' => (int) $this->conn->query("SELECT COUNT(*) FROM medicalRecords WHERE recordType='Prescription' AND DATE_FORMAT(createdAt,'%Y-%m')='$ym'")->fetch_row()[0],
        ];
    }

    public function searchPatients(string $q): array
    {
        $q = '%' . $this->conn->real_escape_string(trim($q)) . '%';
        return $this->conn->query("
            SELECT id, patientCode,
                   TRIM(CONCAT(firstName,' ',COALESCE(NULLIF(middleName,''),''),' ',lastName)) AS name,
                   contactNumber AS contact,
                   dateOfBirth   AS dob
            FROM patients
            WHERE status != 'Inactive'
              AND (firstName LIKE '$q' OR lastName LIKE '$q'
                OR CONCAT(firstName,' ',lastName) LIKE '$q'
                OR patientCode LIKE '$q')
            ORDER BY firstName LIMIT 15
        ")->fetch_all(MYSQLI_ASSOC);
    }

    public function searchDoctors(string $q): array
    {
        $q = '%' . $this->conn->real_escape_string(trim($q)) . '%';
        return $this->conn->query("
            SELECT id, doctorCode,
                   TRIM(CONCAT(firstName,' ',COALESCE(NULLIF(middleName,''),''),' ',lastName)) AS name,
                   specialization
            FROM doctors
            WHERE employmentStatus = 'Active'
              AND (firstName LIKE '$q' OR lastName LIKE '$q'
                OR CONCAT(firstName,' ',lastName) LIKE '$q'
                OR specialization LIKE '$q' OR doctorCode LIKE '$q')
            ORDER BY firstName LIMIT 15
        ")->fetch_all(MYSQLI_ASSOC);
    }

    public function getPatientDoctor(int $patientId): ?array
    {
        $row = $this->conn->query("
            SELECT a.doctorId,
                   TRIM(CONCAT(d.firstName,' ',COALESCE(NULLIF(d.middleName,''),''),' ',d.lastName)) AS doctorName,
                   d.specialization
            FROM appointments a
            JOIN doctors d ON d.id = a.doctorId
            WHERE a.patientId = $patientId AND a.status != 'Cancelled'
            ORDER BY a.appointmentDate DESC LIMIT 1
        ")->fetch_assoc();
        return $row ?: null;
    }

    public function getAppointments(int $patientId, string $q): array
    {
        $q         = '%' . $this->conn->real_escape_string($q) . '%';
        $patFilter = $patientId ? "AND a.patientId = $patientId" : '';
        return $this->conn->query("
            SELECT a.id, a.appointmentCode, a.appointmentDate, a.remarks, a.doctorId,
                   TRIM(CONCAT(d.firstName,' ',COALESCE(NULLIF(d.middleName,''),''),' ',d.lastName)) AS doctorName,
                   d.specialization
            FROM appointments a
            JOIN doctors d ON d.id = a.doctorId
            WHERE a.status != 'Cancelled' $patFilter
              AND (a.appointmentCode LIKE '$q' OR CONCAT(d.firstName,' ',d.lastName) LIKE '$q')
            ORDER BY a.appointmentDate DESC LIMIT 15
        ")->fetch_all(MYSQLI_ASSOC);
    }

    public function getAppointmentDetails(int $apptId): ?array
    {
        $row = $this->conn->query("
            SELECT a.id, a.appointmentCode, a.remarks, a.appointmentDate, a.doctorId,
                   TRIM(CONCAT(d.firstName,' ',COALESCE(NULLIF(d.middleName,''),''),' ',d.lastName)) AS doctorName,
                   d.specialization, a.patientId,
                   TRIM(CONCAT(p.firstName,' ',p.lastName)) AS patientName,
                   p.patientCode
            FROM appointments a
            JOIN doctors d ON d.id = a.doctorId
            LEFT JOIN patients p ON p.id = a.patientId
            WHERE a.id = $apptId LIMIT 1
        ")->fetch_assoc();
        return $row ?: null;
    }

    public function list(array $filters): array
    {
        $search = trim($filters['search'] ?? '');
        $type   = trim($filters['type']   ?? '');
        $status = trim($filters['status'] ?? '');
        $page   = max(1, (int)($filters['page'] ?? 1));
        $limit  = 15;
        $offset = ($page - 1) * $limit;

        $where = [];
        if ($search !== '') {
            $s       = '%' . $this->conn->real_escape_string($search) . '%';
            $where[] = "(CONCAT(p.firstName,' ',p.lastName) LIKE '$s' OR m.recordCode LIKE '$s' OR m.diagnosis LIKE '$s')";
        }
        if ($type !== '')   $where[] = "m.recordType = '" . $this->conn->real_escape_string($type) . "'";
        if ($status !== '') $where[] = "m.status = '"     . $this->conn->real_escape_string($status) . "'";

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Only count parent records (one per patient)
        $parentWhere = $where ? $whereSQL . ' AND m.parentRecordId IS NULL' : 'WHERE m.parentRecordId IS NULL';

        $total = (int) $this->conn->query("
    SELECT COUNT(*) FROM medicalRecords m
    JOIN patients p ON p.id = m.patientId $parentWhere
")->fetch_row()[0];

        $rows = $this->conn->query("
    SELECT 
        m.id, m.recordCode, m.status, m.createdAt, m.updatedAt,
        p.patientCode, COALESCE(p.photoUrl, IF(u.firstName = p.firstName AND u.lastName = p.lastName, u.profilePic, NULL)) AS patPhoto,
        TRIM(CONCAT(p.firstName,' ',p.lastName)) AS patientName,
        TRIM(CONCAT(d.firstName,' ',d.lastName)) AS doctorName,
        d.specialization,
        (SELECT COUNT(*) FROM medicalRecords c WHERE c.parentRecordId = m.id) + 1 AS entryCount,
        (SELECT c2.diagnosis FROM medicalRecords c2 
         WHERE c2.parentRecordId = m.id OR c2.id = m.id
         ORDER BY c2.createdAt DESC LIMIT 1) AS latestDiagnosis,
        (SELECT c2.status FROM medicalRecords c2 
         WHERE c2.parentRecordId = m.id OR c2.id = m.id
         ORDER BY c2.createdAt DESC LIMIT 1) AS latestStatus,
        (SELECT c2.recordType FROM medicalRecords c2 
         WHERE c2.parentRecordId = m.id OR c2.id = m.id
         ORDER BY c2.createdAt DESC LIMIT 1) AS latestType,
        (SELECT COALESCE(c2.updatedAt, c2.createdAt) FROM medicalRecords c2 
         WHERE c2.parentRecordId = m.id OR c2.id = m.id
         ORDER BY c2.createdAt DESC LIMIT 1) AS lastUpdated
    FROM medicalRecords m
    JOIN patients p ON p.id = m.patientId
    JOIN doctors  d ON d.id = m.doctorId
    LEFT JOIN users u ON u.emailAddress = p.emailAddress
    $parentWhere
    ORDER BY lastUpdated DESC
    LIMIT $limit OFFSET $offset
")->fetch_all(MYSQLI_ASSOC);

        return compact('rows', 'total', 'page', 'limit');
    }

    public function get(int $id): ?array
    {
        // Get the parent record
        $row = $this->conn->query("
        SELECT m.*, p.patientCode,
               TRIM(CONCAT(p.firstName,' ',p.lastName)) AS patientName,
               TRIM(CONCAT(d.firstName,' ',d.lastName)) AS doctorName,
               d.specialization, a.appointmentCode, a.appointmentDate
        FROM medicalRecords m
        JOIN patients  p ON p.id = m.patientId
        JOIN doctors   d ON d.id = m.doctorId
        LEFT JOIN appointments a ON a.id = m.appointmentId
        WHERE m.id = $id AND m.parentRecordId IS NULL
        LIMIT 1
    ")->fetch_assoc();

        if (!$row) return null;

        // Get all child entries (follow-up visits)
        $entries = $this->conn->query("
        SELECT m.*,
               TRIM(CONCAT(d.firstName,' ',d.lastName)) AS doctorName,
               d.specialization,
               a.appointmentCode, a.appointmentDate
        FROM medicalRecords m
        LEFT JOIN doctors d ON d.id = m.doctorId
        LEFT JOIN appointments a ON a.id = m.appointmentId
        WHERE m.parentRecordId = $id
        ORDER BY m.createdAt ASC
    ")->fetch_all(MYSQLI_ASSOC);

        $row['entries'] = $entries;

        // Audit log
        $rid      = (int) $row['id'];
        $auditLog = [];
        $logs     = $this->conn->query("
        SELECT action, changedBy, changedAt, oldValue, newValue
        FROM medicalRecordAudit
        WHERE recordId = $rid ORDER BY changedAt DESC
    ");
        if ($logs) {
            while ($l = $logs->fetch_assoc()) {
                $auditLog[] = [
                    'action' => $l['action'],
                    'by'     => $l['changedBy'] ?? 'Admin',
                    'at'     => date('M j, g:i A', strtotime($l['changedAt'])),
                    'type'   => $l['action'] === 'Created' ? 'create' : ($l['action'] === 'Status Changed' ? 'status' : 'edit'),
                    'from'   => $l['oldValue'] ?? '',
                    'to'     => $l['newValue'] ?? '',
                ];
            }
        }
        $row['auditLog'] = $auditLog;
        return $row;
    }

    public function add(array $body, string $changedBy): array
    {
        $patientId     = (int)($body['patientId']     ?? 0) ?: 'NULL';
        $doctorIdRaw = (int)($body['doctorId'] ?? 0);
        if ($doctorIdRaw > 0) {
            $check = $this->conn->query("SELECT id FROM doctors WHERE id = $doctorIdRaw LIMIT 1");
            $doctorId = ($check && $check->num_rows > 0) ? $doctorIdRaw : 'NULL';
        } else {
            $doctorId = 'NULL';
        }
        $appointmentId = (int)($body['appointmentId'] ?? 0) ?: 'NULL';
        $recordType    = $this->conn->real_escape_string($body['recordType']   ?? 'Consultation');
        $diagnosis     = $this->conn->real_escape_string($body['diagnosis']    ?? '');
        $icdCode       = $this->conn->real_escape_string($body['icdCode']      ?? '');
        $prescription  = $this->conn->real_escape_string($body['prescription'] ?? '');
        $notes         = $this->conn->real_escape_string($body['notes']        ?? '');
        $status        = $this->conn->real_escape_string($body['status']       ?? 'Draft');
        $followUpRaw   = ($body['followUpDate'] ?? '') ?: null;
        $followUpDate  = $followUpRaw ? "'" . $this->conn->real_escape_string($followUpRaw) . "'" : 'NULL';

        // Check if this patient already has a parent record
        $existingParent = null;
        if ($patientId !== 'NULL') {
            $existingParent = $this->conn->query("
            SELECT id, recordCode FROM medicalRecords 
            WHERE patientId = $patientId AND parentRecordId IS NULL 
            LIMIT 1
        ")->fetch_assoc();
        }

        // Generate code
        $lastCode = $this->conn->query("
        SELECT MAX(CAST(SUBSTRING_INDEX(recordCode, '-', -1) AS UNSIGNED)) FROM medicalRecords
    ")->fetch_row()[0];
        $code = 'REC-' . date('Y') . '-' . str_pad((int)$lastCode + 1, 4, '0', STR_PAD_LEFT);

        // parentRecordId: NULL if first record, or existing parent's id if returning patient
        $parentRecordId = $existingParent ? $existingParent['id'] : 'NULL';

        $ok = $this->conn->query("
        INSERT INTO medicalRecords
            (recordCode, patientId, doctorId, appointmentId, recordType,
             diagnosis, icdCode, prescription, notes, status, followUpDate, parentRecordId)
        VALUES
            ('$code', $patientId, $doctorId, $appointmentId, '$recordType',
             '$diagnosis', '$icdCode', '$prescription', '$notes', '$status', $followUpDate, $parentRecordId)
    ");

        if (!$ok) return ['success' => false, 'error' => $this->conn->error];

        $newId = $this->conn->insert_id;

        // If no existing parent, this new record IS the parent — no change needed
        // If there was an existing parent, this new record is a child entry

        $this->conn->query("
        INSERT INTO medicalRecordAudit (recordId, action, changedBy, changedAt)
        VALUES ($newId, 'Created', '$changedBy', NOW())
    ");

        if ($followUpRaw) {
            $fromCode = $existingParent ? $existingParent['recordCode'] : $code;

            // If this record is linked to a follow-up, resolve the original appointmentId
            // so the new follow-up row correctly appears in the appointments list
            // Start with the raw integer from the body, not the 'NULL' string version
            $resolvedAppointmentId = (int)($body['appointmentId'] ?? 0);

            // If linked to a follow-up, use that follow-up's appointmentId
            $followUpIdFromBody = (int)($body['followUpId'] ?? 0);
            if ($followUpIdFromBody) {
                $fuRow = $this->conn->query("SELECT appointmentId FROM followUps WHERE id = $followUpIdFromBody LIMIT 1")->fetch_assoc();
                if ($fuRow && $fuRow['appointmentId']) {
                    $resolvedAppointmentId = (int)$fuRow['appointmentId'];
                }
            }

            // Final fallback: find patient's most recent appointment
            if (!$resolvedAppointmentId && is_numeric($patientId)) {
                $fallback = $this->conn->query("
        SELECT id FROM appointments 
        WHERE patientId = $patientId AND status != 'Cancelled'
        ORDER BY appointmentDate DESC LIMIT 1
    ")->fetch_row();
                if ($fallback) $resolvedAppointmentId = (int)$fallback[0];
            }

            $this->createFollowUp($patientId, $resolvedAppointmentId, $followUpRaw, $fromCode, false, $body['doctorId'] ?? null);
        }

        return ['success' => true, 'recordCode' => $code];
    }

    public function edit(array $body, string $changedBy): array
    {
        $id            = (int)($body['id']            ?? 0);
        $patientId     = (int)($body['patientId']     ?? 0) ?: 'NULL';
        $doctorIdRaw = (int)($body['doctorId'] ?? 0);
        if ($doctorIdRaw > 0) {
            $check = $this->conn->query("SELECT id FROM doctors WHERE id = $doctorIdRaw LIMIT 1");
            $doctorId = ($check && $check->num_rows > 0) ? $doctorIdRaw : 'NULL';
        } else {
            $doctorId = 'NULL';
        }
        $appointmentId = (int)($body['appointmentId'] ?? 0) ?: 'NULL';
        $recordType    = $this->conn->real_escape_string($body['recordType']   ?? 'Consultation');
        $diagnosis     = $this->conn->real_escape_string($body['diagnosis']    ?? '');
        $icdCode       = $this->conn->real_escape_string($body['icdCode']      ?? '');
        $prescription  = $this->conn->real_escape_string($body['prescription'] ?? '');
        $notes         = $this->conn->real_escape_string($body['notes']        ?? '');
        $status        = $this->conn->real_escape_string($body['status']       ?? 'Draft');
        $followUpRaw   = ($body['followUpDate'] ?? '') ?: null;
        $followUpDate  = $followUpRaw ? "'" . $this->conn->real_escape_string($followUpRaw) . "'" : 'NULL';

        $ok = $this->conn->query("
            UPDATE medicalRecords SET
                patientId = $patientId, doctorId = $doctorId, appointmentId = $appointmentId,
                recordType = '$recordType', diagnosis = '$diagnosis', icdCode = '$icdCode',
                prescription = '$prescription', notes = '$notes', status = '$status',
                followUpDate = $followUpDate, updatedAt = NOW()
            WHERE id = $id
        ");

        if (!$ok) return ['success' => false, 'error' => $this->conn->error];

        $this->conn->query("
            INSERT INTO medicalRecordAudit (recordId, action, changedBy, changedAt)
            VALUES ($id, 'Edited', '$changedBy', NOW())
        ");

        if ($followUpRaw && $patientId !== 'NULL') {
            $recCode = $this->conn->query("SELECT recordCode FROM medicalRecords WHERE id=$id")->fetch_row()[0] ?? '';
            $exists  = $this->conn->query("
                SELECT id FROM followups
                WHERE patientId = $patientId
                  AND followUpDate = '" . $this->conn->real_escape_string($followUpRaw) . "'
                  AND status NOT IN ('Cancelled') LIMIT 1
            ")->fetch_row();
            $resolvedApptId = (int)($body['appointmentId'] ?? 0);
            $followUpIdFromBody = (int)($body['followUpId'] ?? 0);
            if ($followUpIdFromBody) {
                $fuRow = $this->conn->query("SELECT appointmentId FROM followUps WHERE id = $followUpIdFromBody LIMIT 1")->fetch_assoc();
                if ($fuRow && $fuRow['appointmentId']) {
                    $resolvedApptId = (int)$fuRow['appointmentId'];
                }
            }
            if (!$exists) $this->createFollowUp(
                (int)($body['patientId'] ?? 0),
                $resolvedApptId,
                $followUpRaw,
                $recCode,
                true,
                $body['doctorId'] ?? null
            );
        }

        return ['success' => true];
    }

    public function updateStatus(int $id, string $status, string $changedBy): array
    {
        $s         = $this->conn->real_escape_string($status);
        $oldStatus = $this->conn->query("SELECT status FROM medicalRecords WHERE id=$id")->fetch_row()[0] ?? '';
        $ok = $this->conn->query("UPDATE medicalRecords SET status='$s', updatedAt=NOW() WHERE id=$id OR parentRecordId=$id");

        if ($ok && $oldStatus !== $status) {
            $oldEsc = $this->conn->real_escape_string($oldStatus);
            $this->conn->query("
                INSERT INTO medicalRecordAudit (recordId, action, changedBy, changedAt, oldValue, newValue)
                VALUES ($id, 'Status Changed', '$changedBy', NOW(), '$oldEsc', '$s')
            ");
        }

        return ['success' => (bool)$ok, 'stats' => $this->getStats()];
    }

    public function delete(int $id): bool
    {
        return (bool) $this->conn->query("DELETE FROM medicalRecords WHERE id=$id");
    }

    public function getFollowUps(): array
    {
        return $this->conn->query("
            SELECT mr.id AS recordId, mr.recordCode, mr.followUpDate, mr.diagnosis,
                   CONCAT(p.firstName,' ',p.lastName) AS patientName,
                   p.id AS patientId, p.patientCode,
                   CONCAT('Dr. ',d.firstName,' ',d.lastName) AS doctorName,
                   d.id AS doctorId
            FROM medicalRecords mr
            JOIN patients p ON p.id = mr.patientId
            JOIN doctors  d ON d.id = mr.doctorId
            WHERE mr.followUpDate IS NOT NULL
              AND mr.followUpDate >= CURDATE()
              AND NOT EXISTS (
                  SELECT 1 FROM appointments a
                  WHERE a.patientId = mr.patientId
                    AND a.appointmentDate = mr.followUpDate
                    AND a.status NOT IN ('Cancelled')
              )
            ORDER BY mr.followUpDate ASC LIMIT 10
        ")->fetch_all(MYSQLI_ASSOC);
    }

    private function createFollowUp($patientId, $appointmentId, string $followUpRaw, string $fromCode, bool $isEdit = false, $doctorId = null): void
    {
        $followUpEsc = $this->conn->real_escape_string($followUpRaw);
        $folLast     = $this->conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(followUpCode, '-', -1) AS UNSIGNED)) FROM followups")->fetch_row()[0];
        $folCode     = 'FOL-' . date('Y') . '-' . str_pad((int)$folLast + 1, 4, '0', STR_PAD_LEFT);
        $reason      = 'Follow-up from record ' . $fromCode . ($isEdit ? ' (edit)' : '');

        // If doctorId not passed directly, pull it from the linked appointment
        if (!$doctorId && $appointmentId && $appointmentId !== 'NULL') {
            $doctorId = $this->conn->query("SELECT doctorId FROM appointments WHERE id = $appointmentId")->fetch_row()[0] ?? null;
        }
        $doctorSql = $doctorId ? (int)$doctorId : 'NULL';

        // Extract raw integer values since $patientId/$appointmentId may already be 'NULL' string
        $patientIdSql     = is_numeric($patientId)     ? (int)$patientId     : 'NULL';
        $appointmentIdSql = is_numeric($appointmentId) ? (int)$appointmentId : 'NULL';

        // If appointmentId is still NULL, try to find the original appointment for this patient
        if ($appointmentIdSql === 'NULL' && $patientIdSql !== 'NULL') {
            $fallback = $this->conn->query("
        SELECT id FROM appointments 
        WHERE patientId = $patientIdSql AND status != 'Cancelled'
        ORDER BY appointmentDate DESC LIMIT 1
    ")->fetch_row();
            if ($fallback) $appointmentIdSql = (int)$fallback[0];
        }

        $this->conn->query("
    INSERT INTO followups (followUpCode, patientId, doctorId, appointmentId, followUpDate, reason, status)
    VALUES ('$folCode', $patientIdSql, $doctorSql, $appointmentIdSql, '$followUpEsc', '$reason', 'Pending')
");
        if (function_exists('logActivity')) {
            logActivity($this->conn, 'New Follow-up', "Follow-up $folCode created from $fromCode", $this->conn->insert_id, 'Followup');
        }
    }
}
