<?php
/**
 * AID Database Schema
 * SQL to be executed during installation.
 */

$sql = "
CREATE TABLE IF NOT EXISTS `aid_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `role` ENUM('admin', 'editor') DEFAULT 'admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `aid_applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `app_id` VARCHAR(36) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `status` ENUM('test', 'live') DEFAULT 'test',
    `allowed_domains` TEXT,
    `redirect_urls` TEXT,
    `branding_config` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `aid_api_keys` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `app_id` INT NOT NULL,
    `api_key` VARCHAR(64) NOT NULL UNIQUE,
    `secret` VARCHAR(64) NOT NULL,
    `type` ENUM('test', 'live') NOT NULL,
    `last_used` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`app_id`) REFERENCES `aid_applications`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `aid_verifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `verification_id` VARCHAR(64) NOT NULL UNIQUE,
    `app_id` INT NOT NULL,
    `session_id` VARCHAR(64),
    `device_id` VARCHAR(64),
    `status` ENUM('CREATED', 'PENDING', 'CHALLENGE', 'VERIFIED', 'FAILED', 'EXPIRED', 'CANCELLED') DEFAULT 'CREATED',
    `provider` VARCHAR(50),
    `method` VARCHAR(50),
    `action` VARCHAR(50),
    `risk_score` INT DEFAULT 0,
    `fingerprint` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NULL,
    `completed_at` TIMESTAMP NULL,
    FOREIGN KEY (`app_id`) REFERENCES `aid_applications`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `aid_policies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `app_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `rules` JSON NOT NULL,
    `is_default` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`app_id`) REFERENCES `aid_applications`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `aid_device_trust` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `device_id` VARCHAR(64) NOT NULL,
    `app_id` INT NOT NULL,
    `trust_level` INT DEFAULT 1,
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`app_id`) REFERENCES `aid_applications`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_device_app` (`device_id`, `app_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `aid_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `verification_id` VARCHAR(64),
    `event` VARCHAR(100),
    `details` JSON,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `idx_verif_status` ON `aid_verifications`(`status`);
CREATE INDEX `idx_verif_app` ON `aid_verifications`(`app_id`);
CREATE INDEX `idx_logs_verif` ON `aid_logs`(`verification_id`);
";

return $sql;
