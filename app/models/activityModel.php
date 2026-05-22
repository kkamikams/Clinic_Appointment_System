<?php

class ActivityModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    // Returns the 8 most recent activity entries
    public function recent(): array
    {
        return $this->conn->query("
            SELECT * FROM recentActivity
            ORDER BY createdAt DESC
            LIMIT 8
        ")->fetch_all(MYSQLI_ASSOC);
    }

    // Returns a paginated and optionally type-filtered list of activity entries
    public function all(int $limit, int $offset, string $type): array
    {
        $type  = $this->conn->real_escape_string($type);
        $where = $type ? "WHERE activityType LIKE '%$type%'" : '';

        $total = (int) $this->conn->query(
            "SELECT COUNT(*) FROM recentActivity $where"
        )->fetch_row()[0];

        $rows = $this->conn->query(
            "SELECT * FROM recentActivity $where ORDER BY createdAt DESC LIMIT $limit OFFSET $offset"
        )->fetch_all(MYSQLI_ASSOC);

        return ['rows' => $rows, 'total' => $total];
    }

    // Returns today's activity count, appointment count, and on-duty doctor count
    public function stats(): array
    {
        $today = date('Y-m-d');

        $todayCnt  = (int) $this->conn->query(
            "SELECT COUNT(*) FROM recentActivity WHERE DATE(createdAt)='$today'"
        )->fetch_row()[0];

        $totalAppt = (int) $this->conn->query(
            "SELECT COUNT(*) FROM appointments WHERE appointmentDate='$today'"
        )->fetch_row()[0];

        $onDuty = (int) $this->conn->query(
            "SELECT COUNT(*) FROM doctors WHERE status='On Duty'"
        )->fetch_row()[0];

        return [
            'today_activity' => $todayCnt,
            'appt_today'     => $totalAppt,
            'on_duty'        => $onDuty,
        ];
    }
}
