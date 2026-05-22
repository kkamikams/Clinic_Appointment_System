<?php
class dashboardModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAppointmentsToday($today)
    {
        $result = $this->conn->query(
            "SELECT COUNT(*) FROM appointments WHERE appointmentDate = '$today'"
        );
        if (!$result) return 0;
        $row = $result->fetch_row();
        return $row ? (int)$row[0] : 0;
    }

    public function getAppointmentsYesterday($today)
    {
        $result = $this->conn->query(
            "SELECT COUNT(*) FROM appointments WHERE appointmentDate = DATE_SUB('$today', INTERVAL 1 DAY)"
        );
        if (!$result) return 0;
        $row = $result->fetch_row();
        return $row ? (int)$row[0] : 0;
    }

    public function getPatientsThisMonth()
    {
        $result = $this->conn->query(
            "SELECT COUNT(*) FROM patients WHERE MONTH(createdAt)=MONTH(CURDATE()) AND YEAR(createdAt)=YEAR(CURDATE())"
        );
        if (!$result) return 0;
        $row = $result->fetch_row();
        return $row ? (int)$row[0] : 0;
    }

    public function getPatientsLastMonth()
    {
        // YEAR() is checked alongside MONTH() to avoid matching the same month from a prior year
        $result = $this->conn->query(
            "SELECT COUNT(*) FROM patients WHERE MONTH(createdAt)=MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) AND YEAR(createdAt)=YEAR(DATE_SUB(CURDATE(),INTERVAL 1 MONTH))"
        );
        if (!$result) return 0;
        $row = $result->fetch_row();
        return $row ? (int)$row[0] : 0;
    }

    public function getTotalActivePatients()
    {
        $result = $this->conn->query(
            "SELECT COUNT(*) FROM patients WHERE status='Active'"
        );
        if (!$result) return 0;
        $row = $result->fetch_row();
        return $row ? (int)$row[0] : 0;
    }

    public function getTotalActiveDoctors()
    {
        $result = $this->conn->query(
            "SELECT COUNT(*) FROM doctors WHERE employmentStatus='Active'"
        );
        if (!$result) return 0;
        $row = $result->fetch_row();
        return $row ? (int)$row[0] : 0;
    }

    public function getOnDutyCount()
    {
        $result = $this->conn->query(
            "SELECT COUNT(*) FROM doctors WHERE status='On Duty'"
        );
        if (!$result) return 0;
        $row = $result->fetch_row();
        return $row ? (int)$row[0] : 0;
    }

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
        ");
        if (!$result) return [];

        // Index rows by date so missing days can be filled with zeros below
        $indexed = array_column($result->fetch_all(MYSQLI_ASSOC), null, 'date');

        // Build a continuous 7-day array, defaulting days with no appointments to zero
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

    public function getTodayAppointments($today)
    {
        $result = $this->conn->query("
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
        ");
        if (!$result) return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Marks active doctors as 'Off Duty' if they have no schedule entry for today.
    // Skips doctors already set to 'Off Duty' to avoid unnecessary writes.
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

    // Cancelled appointments are excluded so currentLoad reflects only active bookings
    public function getDutyDoctors($today)
    {
        $result = $this->conn->query("
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
        ");
        if (!$result) return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getRecentActivity()
    {
        $result = $this->conn->query(
            "SELECT * FROM recentActivity ORDER BY createdAt DESC LIMIT 8"
        );
        if (!$result) return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getStatusBreakdown($today)
    {
        $result = $this->conn->query("
            SELECT
                SUM(status = 'Completed')   AS completed,
                SUM(status = 'Pending')     AS pending,
                SUM(status = 'In Progress') AS inProgress,
                SUM(status = 'Cancelled')   AS cancelled
            FROM appointments
            WHERE appointmentDate = '$today'
        ");

        $empty = ['completed' => 0, 'pending' => 0, 'inProgress' => 0, 'cancelled' => 0];
        if (!$result) return $empty;
        $row = $result->fetch_assoc();
        if (!$row) return $empty;

        return [
            'completed'  => (int) $row['completed'],
            'pending'    => (int) $row['pending'],
            'inProgress' => (int) $row['inProgress'],
            'cancelled'  => (int) $row['cancelled'],
        ];
    }
}
