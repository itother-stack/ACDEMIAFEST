-- =====================================================================
--  EVENT REGISTRATION WEBSITE — DATABASE UPDATE (schema version 4)
--
--  Safe to run on an EXISTING database or an EMPTY one, and safe to run
--  more than once. It only:
--    * creates missing tables
--    * adds missing columns
--    * converts incompatible old column types (DATE / ENUM -> VARCHAR)
--    * gives old NOT NULL columns a default so new inserts do not fail
--  No table, column or row is ever deleted.
--
--  How to run: phpMyAdmin > select your database > "SQL" tab >
--  paste this whole file (or use "Import") > Go.
--
--  Note: the website also performs this upgrade automatically on the first
--  page load, and Admin > System Check > "Repair Database" does the same.
-- =====================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- 1. Tables
-- ---------------------------------------------------------------------
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

-- ---------------------------------------------------------------------
-- 2. Missing columns
-- ---------------------------------------------------------------------
-- app_meta.meta_value
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'app_meta' AND COLUMN_NAME = 'meta_value'), 'ALTER TABLE `app_meta` ADD COLUMN `meta_value` TEXT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- admins.username
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins' AND COLUMN_NAME = 'username'), 'ALTER TABLE `admins` ADD COLUMN `username` VARCHAR(100) NOT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- admins.password_hash
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins' AND COLUMN_NAME = 'password_hash'), 'ALTER TABLE `admins` ADD COLUMN `password_hash` VARCHAR(255) NOT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- admins.created_at
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins' AND COLUMN_NAME = 'created_at'), 'ALTER TABLE `admins` ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_name
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_name'), 'ALTER TABLE `events` ADD COLUMN `event_name` VARCHAR(255) NOT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_date
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_date'), 'ALTER TABLE `events` ADD COLUMN `event_date` VARCHAR(100) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_venue
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_venue'), 'ALTER TABLE `events` ADD COLUMN `event_venue` VARCHAR(500) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.contact_phone
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'contact_phone'), 'ALTER TABLE `events` ADD COLUMN `contact_phone` VARCHAR(50) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.offer_zone
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'offer_zone'), 'ALTER TABLE `events` ADD COLUMN `offer_zone` TEXT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.expert_details
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'expert_details'), 'ALTER TABLE `events` ADD COLUMN `expert_details` TEXT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.logo_path
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'logo_path'), 'ALTER TABLE `events` ADD COLUMN `logo_path` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.photo_path
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'photo_path'), 'ALTER TABLE `events` ADD COLUMN `photo_path` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_type
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_type'), 'ALTER TABLE `events` ADD COLUMN `event_type` VARCHAR(100) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.city
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'city'), 'ALTER TABLE `events` ADD COLUMN `city` VARCHAR(150) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_time
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_time'), 'ALTER TABLE `events` ADD COLUMN `event_time` VARCHAR(100) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.description
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'description'), 'ALTER TABLE `events` ADD COLUMN `description` TEXT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.seat_limit
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'seat_limit'), 'ALTER TABLE `events` ADD COLUMN `seat_limit` VARCHAR(50) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.sort_order
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'sort_order'), 'ALTER TABLE `events` ADD COLUMN `sort_order` INT NOT NULL DEFAULT 0', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.is_active
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'is_active'), 'ALTER TABLE `events` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.created_at
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'created_at'), 'ALTER TABLE `events` ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- agents.event_id
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'agents' AND COLUMN_NAME = 'event_id'), 'ALTER TABLE `agents` ADD COLUMN `event_id` INT UNSIGNED NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- agents.agent_name
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'agents' AND COLUMN_NAME = 'agent_name'), 'ALTER TABLE `agents` ADD COLUMN `agent_name` VARCHAR(255) NOT NULL DEFAULT ''''', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.event_id
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'event_id'), 'ALTER TABLE `student_registrations` ADD COLUMN `event_id` INT UNSIGNED NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.interested_in
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'interested_in'), 'ALTER TABLE `student_registrations` ADD COLUMN `interested_in` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.student_name
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'student_name'), 'ALTER TABLE `student_registrations` ADD COLUMN `student_name` VARCHAR(255) NOT NULL DEFAULT ''''', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.gender
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'gender'), 'ALTER TABLE `student_registrations` ADD COLUMN `gender` VARCHAR(20) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.mobile_number
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'mobile_number'), 'ALTER TABLE `student_registrations` ADD COLUMN `mobile_number` VARCHAR(20) NOT NULL DEFAULT ''''', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.whatsapp_number
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'whatsapp_number'), 'ALTER TABLE `student_registrations` ADD COLUMN `whatsapp_number` VARCHAR(20) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.parent_mobile
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'parent_mobile'), 'ALTER TABLE `student_registrations` ADD COLUMN `parent_mobile` VARCHAR(20) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.email_id
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'email_id'), 'ALTER TABLE `student_registrations` ADD COLUMN `email_id` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.city
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'city'), 'ALTER TABLE `student_registrations` ADD COLUMN `city` VARCHAR(150) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.courses
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'courses'), 'ALTER TABLE `student_registrations` ADD COLUMN `courses` TEXT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.ref1_name
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'ref1_name'), 'ALTER TABLE `student_registrations` ADD COLUMN `ref1_name` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.ref1_mobile
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'ref1_mobile'), 'ALTER TABLE `student_registrations` ADD COLUMN `ref1_mobile` VARCHAR(20) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.ref2_name
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'ref2_name'), 'ALTER TABLE `student_registrations` ADD COLUMN `ref2_name` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.ref2_mobile
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'ref2_mobile'), 'ALTER TABLE `student_registrations` ADD COLUMN `ref2_mobile` VARCHAR(20) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.source
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'source'), 'ALTER TABLE `student_registrations` ADD COLUMN `source` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.status
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'status'), 'ALTER TABLE `student_registrations` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT ''Completed''', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.wa_status
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'wa_status'), 'ALTER TABLE `student_registrations` ADD COLUMN `wa_status` VARCHAR(20) NOT NULL DEFAULT ''Pending''', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.wa_sent_at
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'wa_sent_at'), 'ALTER TABLE `student_registrations` ADD COLUMN `wa_sent_at` DATETIME NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.created_at
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'created_at'), 'ALTER TABLE `student_registrations` ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.last_updated
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'last_updated'), 'ALTER TABLE `student_registrations` ADD COLUMN `last_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.image_path
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'image_path'), 'ALTER TABLE `gallery_photos` ADD COLUMN `image_path` VARCHAR(255) NOT NULL DEFAULT ''''', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.title
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'title'), 'ALTER TABLE `gallery_photos` ADD COLUMN `title` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.caption
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'caption'), 'ALTER TABLE `gallery_photos` ADD COLUMN `caption` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.tag
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'tag'), 'ALTER TABLE `gallery_photos` ADD COLUMN `tag` VARCHAR(100) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.sort_order
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'sort_order'), 'ALTER TABLE `gallery_photos` ADD COLUMN `sort_order` INT NOT NULL DEFAULT 0', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.is_active
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'is_active'), 'ALTER TABLE `gallery_photos` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.created_at
SET @sql = (SELECT IF((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'created_at'), 'ALTER TABLE `gallery_photos` ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- 3. Fix incompatible column types from older versions (DATE / ENUM / too short)
-- ---------------------------------------------------------------------
-- app_meta.meta_value type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'app_meta' AND COLUMN_NAME = 'meta_value' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext'))), 'ALTER TABLE `app_meta` MODIFY COLUMN `meta_value` TEXT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- admins.username type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins' AND COLUMN_NAME = 'username' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 100))), 'ALTER TABLE `admins` MODIFY COLUMN `username` VARCHAR(100) NOT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- admins.password_hash type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins' AND COLUMN_NAME = 'password_hash' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `admins` MODIFY COLUMN `password_hash` VARCHAR(255) NOT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- admins.created_at type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins' AND COLUMN_NAME = 'created_at' AND (DATA_TYPE NOT IN ('datetime','timestamp'))), 'ALTER TABLE `admins` MODIFY COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_name type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_name' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `events` MODIFY COLUMN `event_name` VARCHAR(255) NOT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_date type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_date' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 100))), 'ALTER TABLE `events` MODIFY COLUMN `event_date` VARCHAR(100) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_venue type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_venue' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 500))), 'ALTER TABLE `events` MODIFY COLUMN `event_venue` VARCHAR(500) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.contact_phone type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'contact_phone' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 50))), 'ALTER TABLE `events` MODIFY COLUMN `contact_phone` VARCHAR(50) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.offer_zone type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'offer_zone' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext'))), 'ALTER TABLE `events` MODIFY COLUMN `offer_zone` TEXT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.expert_details type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'expert_details' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext'))), 'ALTER TABLE `events` MODIFY COLUMN `expert_details` TEXT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.logo_path type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'logo_path' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `events` MODIFY COLUMN `logo_path` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.photo_path type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'photo_path' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `events` MODIFY COLUMN `photo_path` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_type type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_type' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 100))), 'ALTER TABLE `events` MODIFY COLUMN `event_type` VARCHAR(100) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.city type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'city' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 150))), 'ALTER TABLE `events` MODIFY COLUMN `city` VARCHAR(150) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_time type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_time' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 100))), 'ALTER TABLE `events` MODIFY COLUMN `event_time` VARCHAR(100) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.description type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'description' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext'))), 'ALTER TABLE `events` MODIFY COLUMN `description` TEXT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.seat_limit type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'seat_limit' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 50))), 'ALTER TABLE `events` MODIFY COLUMN `seat_limit` VARCHAR(50) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.sort_order type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'sort_order' AND (DATA_TYPE NOT IN ('int','tinyint','smallint','mediumint','bigint'))), 'ALTER TABLE `events` MODIFY COLUMN `sort_order` INT NOT NULL DEFAULT 0', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.is_active type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'is_active' AND (DATA_TYPE NOT IN ('int','tinyint','smallint','mediumint','bigint'))), 'ALTER TABLE `events` MODIFY COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.created_at type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'created_at' AND (DATA_TYPE NOT IN ('datetime','timestamp'))), 'ALTER TABLE `events` MODIFY COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- agents.event_id type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'agents' AND COLUMN_NAME = 'event_id' AND (DATA_TYPE NOT IN ('int','tinyint','smallint','mediumint','bigint'))), 'ALTER TABLE `agents` MODIFY COLUMN `event_id` INT UNSIGNED NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- agents.agent_name type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'agents' AND COLUMN_NAME = 'agent_name' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `agents` MODIFY COLUMN `agent_name` VARCHAR(255) NOT NULL DEFAULT ''''', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.event_id type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'event_id' AND (DATA_TYPE NOT IN ('int','tinyint','smallint','mediumint','bigint'))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `event_id` INT UNSIGNED NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.interested_in type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'interested_in' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `interested_in` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.student_name type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'student_name' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `student_name` VARCHAR(255) NOT NULL DEFAULT ''''', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.gender type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'gender' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 20))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `gender` VARCHAR(20) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.mobile_number type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'mobile_number' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 20))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `mobile_number` VARCHAR(20) NOT NULL DEFAULT ''''', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.whatsapp_number type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'whatsapp_number' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 20))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `whatsapp_number` VARCHAR(20) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.parent_mobile type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'parent_mobile' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 20))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `parent_mobile` VARCHAR(20) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.email_id type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'email_id' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `email_id` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.city type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'city' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 150))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `city` VARCHAR(150) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.courses type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'courses' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext'))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `courses` TEXT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.ref1_name type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'ref1_name' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `ref1_name` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.ref1_mobile type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'ref1_mobile' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 20))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `ref1_mobile` VARCHAR(20) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.ref2_name type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'ref2_name' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `ref2_name` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.ref2_mobile type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'ref2_mobile' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 20))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `ref2_mobile` VARCHAR(20) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.source type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'source' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `source` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.status type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'status' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 20))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `status` VARCHAR(20) NOT NULL DEFAULT ''Completed''', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.wa_status type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'wa_status' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 20))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `wa_status` VARCHAR(20) NOT NULL DEFAULT ''Pending''', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.wa_sent_at type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'wa_sent_at' AND (DATA_TYPE NOT IN ('datetime','timestamp'))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `wa_sent_at` DATETIME NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.created_at type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'created_at' AND (DATA_TYPE NOT IN ('datetime','timestamp'))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.last_updated type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'last_updated' AND (DATA_TYPE NOT IN ('datetime','timestamp'))), 'ALTER TABLE `student_registrations` MODIFY COLUMN `last_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.image_path type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'image_path' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `gallery_photos` MODIFY COLUMN `image_path` VARCHAR(255) NOT NULL DEFAULT ''''', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.title type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'title' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `gallery_photos` MODIFY COLUMN `title` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.caption type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'caption' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 255))), 'ALTER TABLE `gallery_photos` MODIFY COLUMN `caption` VARCHAR(255) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.tag type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'tag' AND (DATA_TYPE NOT IN ('varchar','char','text','tinytext','mediumtext','longtext') OR (DATA_TYPE = 'varchar' AND CHARACTER_MAXIMUM_LENGTH < 100))), 'ALTER TABLE `gallery_photos` MODIFY COLUMN `tag` VARCHAR(100) NULL DEFAULT NULL', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.sort_order type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'sort_order' AND (DATA_TYPE NOT IN ('int','tinyint','smallint','mediumint','bigint'))), 'ALTER TABLE `gallery_photos` MODIFY COLUMN `sort_order` INT NOT NULL DEFAULT 0', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.is_active type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'is_active' AND (DATA_TYPE NOT IN ('int','tinyint','smallint','mediumint','bigint'))), 'ALTER TABLE `gallery_photos` MODIFY COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.created_at type
SET @sql = (SELECT IF((SELECT COUNT(*) > 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'created_at' AND (DATA_TYPE NOT IN ('datetime','timestamp'))), 'ALTER TABLE `gallery_photos` MODIFY COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP', 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- 4. Old NOT NULL columns without a default (they would make inserts fail)
--    The existing column type is kept, only the default/NULL rule changes.
-- ---------------------------------------------------------------------
-- app_meta.meta_value default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `app_meta` MODIFY COLUMN `meta_value` ', COLUMN_TYPE, ' ', 'NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'app_meta' AND COLUMN_NAME = 'meta_value' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- app_meta: unknown old columns -> optional
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `app_meta` ', GROUP_CONCAT(CONCAT('MODIFY COLUMN `', COLUMN_NAME, '` ', COLUMN_TYPE, ' NULL DEFAULT NULL') SEPARATOR ', ')) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'app_meta' AND COLUMN_NAME NOT IN ('meta_key','meta_value') AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%' AND EXTRA NOT LIKE '%GENERATED%' HAVING COUNT(*) > 0), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- admins.created_at default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `admins` MODIFY COLUMN `created_at` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT CURRENT_TIMESTAMP') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins' AND COLUMN_NAME = 'created_at' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- admins: unknown old columns -> optional
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `admins` ', GROUP_CONCAT(CONCAT('MODIFY COLUMN `', COLUMN_NAME, '` ', COLUMN_TYPE, ' NULL DEFAULT NULL') SEPARATOR ', ')) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins' AND COLUMN_NAME NOT IN ('id','username','password_hash','created_at') AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%' AND EXTRA NOT LIKE '%GENERATED%' HAVING COUNT(*) > 0), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_date default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `event_date` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_date' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_venue default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `event_venue` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_venue' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.contact_phone default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `contact_phone` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'contact_phone' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.offer_zone default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `offer_zone` ', COLUMN_TYPE, ' ', 'NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'offer_zone' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.expert_details default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `expert_details` ', COLUMN_TYPE, ' ', 'NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'expert_details' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.logo_path default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `logo_path` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'logo_path' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.photo_path default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `photo_path` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'photo_path' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_type default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `event_type` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_type' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.city default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `city` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'city' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.event_time default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `event_time` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'event_time' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.description default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `description` ', COLUMN_TYPE, ' ', 'NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'description' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.seat_limit default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `seat_limit` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'seat_limit' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.sort_order default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `sort_order` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'sort_order' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.is_active default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `is_active` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'is_active' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events.created_at default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` MODIFY COLUMN `created_at` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT CURRENT_TIMESTAMP') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'created_at' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- events: unknown old columns -> optional
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `events` ', GROUP_CONCAT(CONCAT('MODIFY COLUMN `', COLUMN_NAME, '` ', COLUMN_TYPE, ' NULL DEFAULT NULL') SEPARATOR ', ')) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME NOT IN ('id','event_name','event_date','event_venue','contact_phone','offer_zone','expert_details','logo_path','photo_path','event_type','city','event_time','description','seat_limit','sort_order','is_active','created_at') AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%' AND EXTRA NOT LIKE '%GENERATED%' HAVING COUNT(*) > 0), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- agents.event_id default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `agents` MODIFY COLUMN `event_id` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'agents' AND COLUMN_NAME = 'event_id' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- agents.agent_name default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `agents` MODIFY COLUMN `agent_name` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT ''''') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'agents' AND COLUMN_NAME = 'agent_name' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- agents: unknown old columns -> optional
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `agents` ', GROUP_CONCAT(CONCAT('MODIFY COLUMN `', COLUMN_NAME, '` ', COLUMN_TYPE, ' NULL DEFAULT NULL') SEPARATOR ', ')) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'agents' AND COLUMN_NAME NOT IN ('id','event_id','agent_name') AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%' AND EXTRA NOT LIKE '%GENERATED%' HAVING COUNT(*) > 0), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.event_id default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `event_id` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'event_id' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.interested_in default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `interested_in` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'interested_in' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.student_name default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `student_name` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT ''''') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'student_name' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.gender default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `gender` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'gender' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.mobile_number default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `mobile_number` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT ''''') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'mobile_number' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.whatsapp_number default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `whatsapp_number` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'whatsapp_number' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.parent_mobile default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `parent_mobile` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'parent_mobile' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.email_id default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `email_id` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'email_id' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.city default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `city` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'city' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.courses default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `courses` ', COLUMN_TYPE, ' ', 'NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'courses' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.ref1_name default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `ref1_name` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'ref1_name' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.ref1_mobile default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `ref1_mobile` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'ref1_mobile' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.ref2_name default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `ref2_name` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'ref2_name' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.ref2_mobile default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `ref2_mobile` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'ref2_mobile' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.source default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `source` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'source' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.status default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `status` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT ''Completed''') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'status' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.wa_status default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `wa_status` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT ''Pending''') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'wa_status' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.wa_sent_at default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `wa_sent_at` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'wa_sent_at' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.created_at default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `created_at` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT CURRENT_TIMESTAMP') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'created_at' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations.last_updated default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` MODIFY COLUMN `last_updated` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME = 'last_updated' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- student_registrations: unknown old columns -> optional
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `student_registrations` ', GROUP_CONCAT(CONCAT('MODIFY COLUMN `', COLUMN_NAME, '` ', COLUMN_TYPE, ' NULL DEFAULT NULL') SEPARATOR ', ')) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_registrations' AND COLUMN_NAME NOT IN ('id','event_id','interested_in','student_name','gender','mobile_number','whatsapp_number','parent_mobile','email_id','city','courses','ref1_name','ref1_mobile','ref2_name','ref2_mobile','source','status','wa_status','wa_sent_at','created_at','last_updated') AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%' AND EXTRA NOT LIKE '%GENERATED%' HAVING COUNT(*) > 0), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.image_path default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `gallery_photos` MODIFY COLUMN `image_path` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT ''''') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'image_path' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.title default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `gallery_photos` MODIFY COLUMN `title` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'title' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.caption default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `gallery_photos` MODIFY COLUMN `caption` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'caption' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.tag default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `gallery_photos` MODIFY COLUMN `tag` ', COLUMN_TYPE, ' ', 'NULL DEFAULT NULL') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'tag' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.sort_order default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `gallery_photos` MODIFY COLUMN `sort_order` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT 0') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'sort_order' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.is_active default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `gallery_photos` MODIFY COLUMN `is_active` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT 1') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'is_active' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos.created_at default
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `gallery_photos` MODIFY COLUMN `created_at` ', COLUMN_TYPE, ' ', 'NOT NULL DEFAULT CURRENT_TIMESTAMP') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME = 'created_at' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_photos: unknown old columns -> optional
SET @sql = (SELECT IFNULL((SELECT CONCAT('ALTER TABLE `gallery_photos` ', GROUP_CONCAT(CONCAT('MODIFY COLUMN `', COLUMN_NAME, '` ', COLUMN_TYPE, ' NULL DEFAULT NULL') SEPARATOR ', ')) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gallery_photos' AND COLUMN_NAME NOT IN ('id','image_path','title','caption','tag','sort_order','is_active','created_at') AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%' AND EXTRA NOT LIKE '%GENERATED%' HAVING COUNT(*) > 0), 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- 5. Mark the database as up to date
-- ---------------------------------------------------------------------
REPLACE INTO `app_meta` (`meta_key`, `meta_value`) VALUES ('schema_version', '4');
