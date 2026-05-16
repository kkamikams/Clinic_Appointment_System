<?php
class patientModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // FROM: $totalPatients = $conn->query("SELECT COUNT(*) FROM patients WHERE status != 'Inactive'")
    public function getTotalPatients()
    {
        return $this->conn->query(
            "SELECT COUNT(*) FROM patients WHERE status != 'Inactive'"
        )->fetch_row()[0];
    }

    // FROM: $activeCount = $conn->query("SELECT COUNT(*) FROM patients WHERE status = 'Active'")
    public function getActiveCount()
    {
        return $this->conn->query(
            "SELECT COUNT(*) FROM patients WHERE status = 'Active'"
        )->fetch_row()[0];
    }

    // FROM: $critical = $conn->query("SELECT COUNT(*) FROM patients WHERE patientCondition = 'Critical'")
    public function getCriticalCount()
    {
        return $this->conn->query(
            "SELECT COUNT(*) FROM patients WHERE patientCondition = 'Critical'"
        )->fetch_row()[0];
    }

    // FROM: $sql = "SELECT p.id, p.patientCode..." + $patients = $conn->query($sql)
    public function getAllPatients()
    {
        $sql = "
            SELECT
                p.id, p.patientCode, p.firstName, p.middleName, p.lastName,
                p.gender, p.dateOfBirth, p.contactNumber, p.emailAddress, p.address,
                p.status, p.patientCondition,
                TIMESTAMPDIFF(YEAR, p.dateOfBirth, CURDATE()) AS age,
                GREATEST(
                    COALESCE(MAX(a.appointmentDate), '1000-01-01'),
                    COALESCE(MAX(f.followUpDate),    '1000-01-01')
                ) AS lastVisit,
                (
                    SELECT CONCAT(d2.firstName, '|||', d2.lastName)
                    FROM appointments a3
                    JOIN doctors d2 ON d2.id = a3.doctorId
                    WHERE a3.patientId = p.id AND a3.status = 'Completed'
                    ORDER BY a3.appointmentDate DESC LIMIT 1
                ) AS docName
            FROM patients p
            LEFT JOIN appointments a
                ON a.patientId = p.id AND a.status = 'Completed'
            LEFT JOIN followups f
                ON f.patientId = p.id AND f.status = 'Completed'
            GROUP BY p.id
            ORDER BY p.lastName, p.firstName
        ";
        return $this->conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function createFromBooking(array $data): int
    {
        $lastCode  = (int) $this->conn->query(
            "SELECT MAX(CAST(SUBSTRING_INDEX(patientCode, '-', -1) AS UNSIGNED)) FROM patients"
        )->fetch_row()[0];
        $pCode     = 'PAT-' . date('Y') . '-' . str_pad($lastCode + 1, 3, '0', STR_PAD_LEFT);

        $firstName  = $data['firstName']  ?? '';
        $middleName = $data['middleName'] ?? '';
        $lastName   = $data['lastName']   ?? '';
        $gender     = in_array($data['gender'] ?? '', ['Male', 'Female', 'Other']) ? $data['gender'] : 'Other';
        $dob        = !empty($data['dob']) ? $data['dob'] : null;
        $contact    = $data['contact'] ?? null;
        $email      = $data['email']   ?? null;
        $address    = $data['address'] ?? null;

        $stmt = $this->conn->prepare("
        INSERT INTO patients
            (patientCode, firstName, middleName, lastName, gender, dateOfBirth,
             contactNumber, emailAddress, address, status, patientCondition)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', 'Stable')
    ");
        $stmt->bind_param(
            'sssssssss',
            $pCode,
            $firstName,
            $middleName,
            $lastName,
            $gender,
            $dob,
            $contact,
            $email,
            $address
        );
        $stmt->execute();
        return $this->conn->insert_id;
    }
}
