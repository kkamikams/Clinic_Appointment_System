<?php
class dashboardModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // FROM: $apptToday = $conn->query("SELECT COUNT(*) FROM appointments WHERE appointmentDate = '$today'")
    public function getAppointmentsToday($today)
    {
        return $this->conn->query(
            "SELECT COUNT(*) FROM appointments WHERE appointmentDate = '$today'"
        )->fetch_row()[0];
    }

    // FROM: $apptYesterday = $conn->query("SELECT COUNT(*) FROM appointments WHERE appointmentDate = DATE_SUB...")
    public function getAppointmentsYesterday($today)
    {
        return $this->conn->query(
            "SELECT COUNT(*) FROM appointments WHERE appointmentDate = DATE_SUB('$today', INTERVAL 1 DAY)"
        )->fetch_row()[0];
    }

    // FROM: $patMonth = $conn->query("SELECT COUNT(*) FROM patients WHERE MONTH(createdAt)=MONTH(CURDATE())...")
    public function getPatientsThisMonth()
    {
        return $this->conn->query(
            "SELECT COUNT(*) FROM patients WHERE MONTH(createdAt)=MONTH(CURDATE()) AND YEAR(createdAt)=YEAR(CURDATE())"
        )->fetch_row()[0];
    }

    // FROM: $patLastMonth = $conn->query("SELECT COUNT(*) FROM patients WHERE MONTH(createdAt)=MONTH(DATE_SUB...)")
    public function getPatientsLastMonth()
    {
        return $this->conn->query(
            "SELECT COUNT(*) FROM patients WHERE MONTH(createdAt)=MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) AND YEAR(createdAt)=YEAR(DATE_SUB(CURDATE(),INTERVAL 1 MONTH))"
        )->fetch_row()[0];
    }

    // FROM: $totalPatients = $conn->query("SELECT COUNT(*) FROM patients WHERE status='Active'")
    public function getTotalActivePatients()
    {
        return $this->conn->query(
            "SELECT COUNT(*) FROM patients WHERE status='Active'"
        )->fetch_row()[0];
    }

    // FROM: $totalDoctors = $conn->query("SELECT COUNT(*) FROM doctors WHERE employmentStatus='Active'")
    public function getTotalActiveDoctors()
    {
        return $this->conn->query(
            "SELECT COUNT(*) FROM doctors WHERE employmentStatus='Active'"
        )->fetch_row()[0];
    }

    // FROM: $onDutyNow = $conn->query("SELECT COUNT(*) FROM doctors WHERE status='On Duty'")
    public function getOnDutyCount()
    {
        return $this->conn->query(
            "SELECT COUNT(*) FROM doctors WHERE status='On Duty'"
        )->fetch_row()[0];
    }

    // FROM: $chartData = []; for ($i = 6; $i >= 0; $i--) { ... }
    public function getChartData()
    {
        $start = date('Y-m-d', strtotime('-6 days'));

        $result = $this->conn->query("
        SELECT
            appointmentDate                   AS date,
            COUNT(*)                          AS total,
            SUM(status = 'Completed')         AS completed,
            SUM(status = 'Cancelled')         AS cancelled
        FROM appointments
        WHERE appointmentDate >= '$start'
        GROUP BY appointmentDate
        ORDER BY appointmentDate ASC
    ")->fetch_all(MYSQLI_ASSOC);

        $indexed = array_column($result, null, 'date');

        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $d           = date('Y-m-d', strtotime("-$i days"));
            $row         = $indexed[$d] ?? ['total' => 0, 'completed' => 0, 'cancelled' => 0];
            $chartData[] = [
                'date'      => date('Y-m-d\TH:i:s.000\Z', strtotime($d)),
                'total'     => (int) $row['total'],
                'completed' => (int) $row['completed'],
                'cancelled' => (int) $row['cancelled'],
            ];
        }
        return $chartData;
    }

    // FROM: $apptRows = $conn->query("SELECT a.appointmentCode, a.appointmentTime...")
    public function getTodayAppointments($today)
    {
        return $this->conn->query("
            SELECT a.appointmentCode, a.appointmentTime, a.status, a.channel,
                CONCAT(p.firstName,' ',p.lastName) AS patientName,
                CONCAT('Dr. ',d.firstName,' ',d.lastName) AS doctorName,
                d.specialization
            FROM appointments a
            JOIN patients p ON p.id=a.patientId
            JOIN doctors d ON d.id=a.doctorId
            WHERE a.appointmentDate='$today'
            ORDER BY a.appointmentTime ASC
            LIMIT 10
        ")->fetch_all(MYSQLI_ASSOC);
    }

    // FROM: $conn->query("UPDATE doctors d LEFT JOIN doctorSchedules ds...")
    public function updateOffDutyDoctors($today)
    {
        $this->conn->query("
            UPDATE doctors d
            LEFT JOIN doctorSchedules ds ON ds.doctorId = d.id AND ds.dayOfWeek = DAYNAME('$today')
            SET d.status = 'Off Duty'
            WHERE ds.doctorId IS NULL
              AND d.employmentStatus = 'Active'
              AND d.status != 'Off Duty'
        ");
    }

    // FROM: $dutyDoctors = $conn->query("SELECT d.id, d.firstName, d.lastName...")
    public function getDutyDoctors($today)
    {
        return $this->conn->query("
            SELECT d.id, d.firstName, d.lastName, d.specialization,
                   d.patientCapacity, d.status,
                   COUNT(DISTINCT a.id) AS currentLoad,
                   MIN(ds.shiftStart) AS shiftStart,
                   MAX(ds.shiftEnd) AS shiftEnd
            FROM doctors d
            INNER JOIN doctorSchedules ds ON ds.doctorId = d.id AND ds.dayOfWeek = DAYNAME('$today')
            LEFT JOIN appointments a ON a.doctorId = d.id AND a.appointmentDate = '$today' AND a.status != 'Cancelled'
            WHERE d.employmentStatus = 'Active'
            GROUP BY d.id
            LIMIT 8
        ")->fetch_all(MYSQLI_ASSOC);
    }

    // FROM: $activities = $conn->query("SELECT * FROM recentActivity ORDER BY createdAt DESC LIMIT 8")
    public function getRecentActivity()
    {
        return $this->conn->query(
            "SELECT * FROM recentActivity ORDER BY createdAt DESC LIMIT 8"
        )->fetch_all(MYSQLI_ASSOC);
    }

    // FROM: $apptCompleted, $apptPending, $apptInProgress, $apptCancelled = $conn->query(...)
    public function getStatusBreakdown($today)
    {
        $row = $this->conn->query("
        SELECT
            SUM(status = 'Completed')   AS completed,
            SUM(status = 'Pending')     AS pending,
            SUM(status = 'In Progress') AS inProgress,
            SUM(status = 'Cancelled')   AS cancelled
        FROM appointments
        WHERE appointmentDate = '$today'
    ")->fetch_assoc();

        return [
            'completed'  => (int) $row['completed'],
            'pending'    => (int) $row['pending'],
            'inProgress' => (int) $row['inProgress'],
            'cancelled'  => (int) $row['cancelled'],
        ];
    }
}
