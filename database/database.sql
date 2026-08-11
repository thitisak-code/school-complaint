CREATE DATABASE IF NOT EXISTS `school_complaint` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `school_complaint`;

-- ตารางเก็บเรื่องร้องเรียน
CREATE TABLE IF NOT EXISTS `complaints` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ticket_code` VARCHAR(12) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `details` TEXT NOT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'in_progress', 'resolved', 'rejected') NOT NULL DEFAULT 'pending',
  `admin_reply` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_code` (`ticket_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตารางผู้ดูแลระบบ (ครู)
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- สร้างบัญชีครูเริ่มต้น (User: admin | Pass: admin1234)
INSERT INTO `admin_users` (`username`, `password`, `fullname`) VALUES
('admin', '$2y$10$44.R2m4S0y1R32OqRk8QieXJ/BfF3r/l.wO0Z.8N0Z0Z0Z0Z0Z0Z0', 'ครูผู้ดูแลระบบ');