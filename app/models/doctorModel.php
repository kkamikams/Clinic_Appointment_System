<?php

class DoctorModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getDoctorById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT d.*,
                GROUP_CONCAT(
                    DISTINCT ds.dayOfWeek
                    ORDER BY FIELD(
                        ds.dayOfWeek,
                        'Monday','Tuesday','Wednesday',
                        'Thursday','Friday','Saturday','Sunday'
                    )
                    SEPARATOR ','
                ) AS workingDays,
                MIN(ds.shiftStart) AS shiftStart,
                MAX(ds.shiftEnd) AS shiftEnd
            FROM doctors d
            LEFT JOIN doctorSchedules ds ON ds.doctorId = d.id
            WHERE d.id = ?
            GROUP BY d.id
        ");

        $stmt->bind_param('i', $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function updateDutyStatus($todayName, $currentTime)
    {
        $stmt = $this->conn->prepare("
            UPDATE doctors d
            SET d.status = CASE
                WHEN EXISTS (
                    SELECT 1 
                    FROM doctorSchedules ds 
                    WHERE ds.doctorId = d.id 
                      AND ds.dayOfWeek = ?
                      AND ? BETWEEN ds.shiftStart AND ds.shiftEnd
                ) THEN 'On Duty'
                ELSE 'Off Duty'
            END
            WHERE d.employmentStatus = 'Active'
              AND d.status != 'Break'
        ");

        $stmt->bind_param('ss', $todayName, $currentTime);
        $stmt->execute();
    }

    public function getTotalDoctors()
    {
        return $this->conn->query("
            SELECT COUNT(*) 
            FROM doctors 
            WHERE employmentStatus != 'Inactive'
        ")->fetch_row()[0];
    }

    public function getOnDutyCount()
    {
        return $this->conn->query("
            SELECT COUNT(*) 
            FROM doctors 
            WHERE status = 'On Duty' 
              AND employmentStatus = 'Active'
        ")->fetch_row()[0];
    }

    public function getOnLeaveCount()
    {
        return $this->conn->query("
            SELECT COUNT(*) 
            FROM doctors 
            WHERE employmentStatus = 'On Leave'
        ")->fetch_row()[0];
    }

    public function getTotalSpecializations()
    {
        return $this->conn->query("
            SELECT COUNT(DISTINCT specialization) 
            FROM doctors 
            WHERE employmentStatus != 'Inactive'
        ")->fetch_row()[0];
    }

    public function getAllDoctors($todayName)
    {
        $sql = "
            SELECT
                d.id, d.doctorCode, d.firstName, d.middleName, d.lastName,
                d.specialization, d.contactNumber, d.patientCapacity,
                d.status, d.employmentStatus, d.emailAddress,
                d.prcLicenseNo, d.yearsOfExperience,

                COUNT(DISTINCT a.id) AS currentLoad,

                GROUP_CONCAT(
                    DISTINCT ds.dayOfWeek
                    ORDER BY FIELD(ds.dayOfWeek,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
                    SEPARATOR ','
                ) AS workingDays,

                MIN(ds.shiftStart) AS shiftStart,
                MAX(ds.shiftEnd) AS shiftEnd,

                MAX(CASE WHEN ds.dayOfWeek = ? THEN 1 ELSE 0 END) AS hasToday,
                MAX(CASE WHEN ds.dayOfWeek = ? THEN ds.shiftStart END) AS todayStart,
                MAX(CASE WHEN ds.dayOfWeek = ? THEN ds.shiftEnd END) AS todayEnd

            FROM doctors d

            LEFT JOIN appointments a
                ON a.doctorId = d.id
               AND a.appointmentDate = CURDATE()
               AND a.status != 'Cancelled'

            LEFT JOIN doctorSchedules ds
                ON ds.doctorId = d.id

            GROUP BY d.id
            ORDER BY d.lastName, d.firstName
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $todayName, $todayName, $todayName);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
