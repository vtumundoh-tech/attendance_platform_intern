<?php
/**
 * Class Logger untuk menangani semua jenis logging dalam aplikasi
 */
class Logger {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Log aktivitas user umum
     */
    public function logUserActivity($user_id, $user_type, $activity_type, $description = '', $additional_data = null) {
        $user_id = $this->sanitize($user_id);
        $user_type = $this->sanitize($user_type);
        $activity_type = $this->sanitize($activity_type);
        $description = $this->sanitize($description);
        $ip_address = $this->getClientIP();
        $user_agent = $this->getUserAgent();
        $additional_data_json = $additional_data ? json_encode($additional_data) : null;
        
        $sql = "INSERT INTO tbl_user_logs (user_id, user_type, activity_type, activity_description, ip_address, user_agent, additional_data) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssssss", $user_id, $user_type, $activity_type, $description, $ip_address, $user_agent, $additional_data_json);
        return $stmt->execute();
    }
    
    /**
     * Log autentikasi (login, logout, password change)
     */
    public function logAuth($user_id, $user_type, $action, $status = 'success', $session_duration = null) {
        $user_id = $this->sanitize($user_id);
        $user_type = $this->sanitize($user_type);
        $action = $this->sanitize($action);
        $status = $this->sanitize($status);
        $ip_address = $this->getClientIP();
        $user_agent = $this->getUserAgent();
        
        $sql = "INSERT INTO tbl_auth_logs (user_id, user_type, action, ip_address, user_agent, status, session_duration) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssssi", $user_id, $user_type, $action, $ip_address, $user_agent, $status, $session_duration);
        return $stmt->execute();
    }
    
    /**
     * Log absensi
     */
    public function logAttendance($user_id, $attendance_type, $attendance_time, $location_lat = null, $location_lng = null, $location_address = null, $photo_filename = null, $status = 'on_time', $reason = null) {
        $user_id = $this->sanitize($user_id);
        $attendance_type = $this->sanitize($attendance_type);
        $attendance_time = $this->sanitize($attendance_time);
        $location_address = $this->sanitize($location_address);
        $photo_filename = $this->sanitize($photo_filename);
        $status = $this->sanitize($status);
        $reason = $this->sanitize($reason);
        
        $sql = "INSERT INTO tbl_attendance_logs (user_id, attendance_type, attendance_time, location_lat, location_lng, location_address, photo_filename, status, reason) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssddssss", $user_id, $attendance_type, $attendance_time, $location_lat, $location_lng, $location_address, $photo_filename, $status, $reason);
        return $stmt->execute();
    }
    
    /**
     * Log kegiatan
     */
    public function logActivity($user_id, $action, $activity_id = null, $activity_content = null, $time_start = null, $time_end = null, $activity_date = null) {
        $user_id = $this->sanitize($user_id);
        $action = $this->sanitize($action);
        $activity_content = $this->sanitize($activity_content);
        $time_start = $this->sanitize($time_start);
        $time_end = $this->sanitize($time_end);
        $activity_date = $this->sanitize($activity_date);
        
        $sql = "INSERT INTO tbl_activity_logs (user_id, action, activity_id, activity_content, time_start, time_end, activity_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssissss", $user_id, $action, $activity_id, $activity_content, $time_start, $time_end, $activity_date);
        return $stmt->execute();
    }
    
    /**
     * Log perubahan profil
     */
    public function logProfileChange($user_id, $action, $field_changed = null, $old_value = null, $new_value = null) {
        $user_id = $this->sanitize($user_id);
        $action = $this->sanitize($action);
        $field_changed = $this->sanitize($field_changed);
        $old_value = $this->sanitize($old_value);
        $new_value = $this->sanitize($new_value);
        
        $sql = "INSERT INTO tbl_profile_logs (user_id, action, field_changed, old_value, new_value) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssss", $user_id, $action, $field_changed, $old_value, $new_value);
        return $stmt->execute();
    }
    
    /**
     * Log aktivitas admin
     */
    public function logAdminAction($admin_id, $action, $target_id = null, $action_description = null) {
        $admin_id = $this->sanitize($admin_id);
        $action = $this->sanitize($action);
        $target_id = $this->sanitize($target_id);
        $action_description = $this->sanitize($action_description);
        $ip_address = $this->getClientIP();
        
        $sql = "INSERT INTO tbl_admin_logs (admin_id, action, target_id, action_description, ip_address) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("sssss", $admin_id, $action, $target_id, $action_description, $ip_address);
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Failed to execute logAdminAction: " . $stmt->error);
        }
        
        return $result;
    }
    
    /**
     * Log sistem
     */
    public function logSystem($log_level, $log_category, $message, $stack_trace = null, $user_id = null) {
        $log_level = $this->sanitize($log_level);
        $log_category = $this->sanitize($log_category);
        $message = $this->sanitize($message);
        $stack_trace = $this->sanitize($stack_trace);
        $user_id = $this->sanitize($user_id);
        $ip_address = $this->getClientIP();
        
        $sql = "INSERT INTO tbl_system_logs (log_level, log_category, message, stack_trace, ip_address, user_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssss", $log_level, $log_category, $message, $stack_trace, $ip_address, $user_id);
        return $stmt->execute();
    }
    
    /**
     * Mendapatkan IP address client
     */
    private function getClientIP() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Mendapatkan user agent
     */
    private function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
    
    /**
     * Sanitasi input
     */
    private function sanitize($input) {
        if (is_null($input)) return null;
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Mendapatkan riwayat log user
     */
    public function getUserLogs($user_id, $limit = 50, $offset = 0, $activity_type = '', $date = '') {
        $user_id = $this->sanitize($user_id);
        $conditions = ["user_id = ?"];
        $params = [$user_id];
        $types = "s";

        if ($activity_type) {
            $conditions[] = "activity_type = ?";
            $params[] = $activity_type;
            $types .= "s";
        }
        if ($date) {
            $conditions[] = "DATE(created_at) = ?";
            $params[] = $date;
            $types .= "s";
        }

        $where = implode(" AND ", $conditions);
        $sql = "SELECT * FROM tbl_user_logs WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Mendapatkan riwayat absensi user
     */
    public function getUserAttendanceLogs($user_id, $limit = 50, $offset = 0) {
        $user_id = $this->sanitize($user_id);
        
        $sql = "SELECT * FROM tbl_attendance_logs WHERE user_id = ? ORDER BY attendance_time DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sii", $user_id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Mendapatkan statistik log
     */
    public function getLogStatistics($user_id = null, $date_from = null, $date_to = null) {
        $conditions = [];
        $params = [];
        $types = "";
        
        if ($user_id) {
            $conditions[] = "user_id = ?";
            $params[] = $user_id;
            $types .= "s";
        }
        
        if ($date_from) {
            $conditions[] = "created_at >= ?";
            $params[] = $date_from;
            $types .= "s";
        }
        
        if ($date_to) {
            $conditions[] = "created_at <= ?";
            $params[] = $date_to;
            $types .= "s";
        }
        
        $where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        
        $sql = "SELECT 
                    activity_type,
                    COUNT(*) as total_activities,
                    DATE(created_at) as activity_date
                FROM tbl_user_logs 
                $where_clause
                GROUP BY activity_type, DATE(created_at)
                ORDER BY activity_date DESC, total_activities DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?> 