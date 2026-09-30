-- =====================================================================
--  EVENT REGISTRATION WEBSITE — DATABASE SCHEMA (fresh install, version 4)
--  For an EXISTING database use database_update.sql instead.
--  phpMyAdmin > select your database > Import > choose this file > Go
--  (or open /install.php in the browser, which does the same and more)
-- =====================================================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `app_meta` (
  `meta_key` VARCHAR(100) NOT NULL,
  `meta_value` TEXT NULL,
  PRIMARY KEY (`meta_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_name` VARCHAR(255) NOT NULL,
  `event_date` VARCHAR(100) NULL DEFAULT NULL,
  `event_venue` VARCHAR(500) NULL DEFAULT NULL,
  `contact_phone` VARCHAR(50) NULL DEFAULT NULL,
  `offer_zone` TEXT NULL,
  `expert_details` TEXT NULL,
  `logo_path` VARCHAR(255) NULL DEFAULT NULL,
  `photo_path` VARCHAR(255) NULL DEFAULT NULL,
  `event_type` VARCHAR(100) NULL DEFAULT NULL,
  `city` VARCHAR(150) NULL DEFAULT NULL,
  `event_time` VARCHAR(100) NULL DEFAULT NULL,
  `description` TEXT NULL,
  `seat_limit` VARCHAR(50) NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `agents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` INT UNSIGNED NULL DEFAULT NULL,
  `agent_name` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_event` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `student_registrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` INT UNSIGNED NULL DEFAULT NULL,
  `interested_in` VARCHAR(255) NULL DEFAULT NULL,
  `student_name` VARCHAR(255) NOT NULL DEFAULT '',
  `gender` VARCHAR(20) NULL DEFAULT NULL,
  `mobile_number` VARCHAR(20) NOT NULL DEFAULT '',
  `whatsapp_number` VARCHAR(20) NULL DEFAULT NULL,
  `parent_mobile` VARCHAR(20) NULL DEFAULT NULL,
  `email_id` VARCHAR(255) NULL DEFAULT NULL,
  `city` VARCHAR(150) NULL DEFAULT NULL,
  `courses` TEXT NULL,
  `ref1_name` VARCHAR(255) NULL DEFAULT NULL,
  `ref1_mobile` VARCHAR(20) NULL DEFAULT NULL,
  `ref2_name` VARCHAR(255) NULL DEFAULT NULL,
  `ref2_mobile` VARCHAR(20) NULL DEFAULT NULL,
  `source` VARCHAR(255) NULL DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'Completed',
  `wa_status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
  `wa_sent_at` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event` (`event_id`),
  KEY `idx_mobile` (`mobile_number`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gallery_photos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `image_path` VARCHAR(255) NOT NULL DEFAULT '',
  `title` VARCHAR(255) NULL DEFAULT NULL,
  `caption` VARCHAR(255) NULL DEFAULT NULL,
  `tag` VARCHAR(100) NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_active_sort` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

REPLACE INTO `app_meta` (`meta_key`, `meta_value`) VALUES ('schema_version', '4');
