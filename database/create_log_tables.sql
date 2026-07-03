-- Tabel untuk menyimpan log aktivitas user
CREATE TABLE `tbl_user_logs` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `user_type` enum('mahasiswa','admin') NOT NULL,
  `activity_type` varchar(100) NOT NULL,
  `activity_description` text,
  `ip_address` varchar(45),
  `user_agent` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `additional_data` json,
  PRIMARY KEY (`id_log`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_activity_type` (`activity_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk log autentikasi
CREATE TABLE `tbl_auth_logs` (
  `id_auth_log` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `user_type` enum('mahasiswa','admin') NOT NULL,
  `action` enum('login','logout','password_change','login_failed','session_timeout') NOT NULL,
  `ip_address` varchar(45),
  `user_agent` text,
  `status` enum('success','failed') NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `session_duration` int(11) NULL COMMENT 'Duration in seconds',
  PRIMARY KEY (`id_auth_log`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk log absensi
CREATE TABLE `tbl_attendance_logs` (
  `id_attendance_log` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `attendance_type` enum('masuk','pulang','izin','sakit') NOT NULL,
  `attendance_time` datetime NOT NULL,
  `location_lat` decimal(10,8) NULL,
  `location_lng` decimal(11,8) NULL,
  `location_address` text,
  `photo_filename` varchar(255) NULL,
  `status` enum('on_time','late','early_leave') NOT NULL,
  `reason` text NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_attendance_log`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_attendance_type` (`attendance_type`),
  KEY `idx_attendance_time` (`attendance_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk log kegiatan
CREATE TABLE `tbl_activity_logs` (
  `id_activity_log` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `action` enum('create','update','delete') NOT NULL,
  `activity_id` int(11) NULL,
  `activity_content` text,
  `time_start` time NULL,
  `time_end` time NULL,
  `activity_date` date NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_activity_log`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_activity_date` (`activity_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk log profil
CREATE TABLE `tbl_profile_logs` (
  `id_profile_log` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `action` enum('update_photo','update_personal_data','update_academic_data') NOT NULL,
  `field_changed` varchar(100) NULL,
  `old_value` text NULL,
  `new_value` text NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_profile_log`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk log admin
CREATE TABLE `tbl_admin_logs` (
  `id_admin_log` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` varchar(50) NOT NULL,
  `action` enum('create_mahasiswa','update_mahasiswa','delete_mahasiswa','export_data','print_report','update_settings','system_maintenance') NOT NULL,
  `target_id` varchar(50) NULL COMMENT 'ID of affected record',
  `action_description` text,
  `ip_address` varchar(45),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_admin_log`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk log sistem
CREATE TABLE `tbl_system_logs` (
  `id_system_log` int(11) NOT NULL AUTO_INCREMENT,
  `log_level` enum('info','warning','error','critical') NOT NULL,
  `log_category` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `stack_trace` text NULL,
  `ip_address` varchar(45),
  `user_id` varchar(50) NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_system_log`),
  KEY `idx_log_level` (`log_level`),
  KEY `idx_log_category` (`log_category`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 