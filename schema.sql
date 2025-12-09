-- Database schema for Lost & Found portal

CREATE DATABASE IF NOT EXISTS `lost_found`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `lost_found`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(190) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'moderator', 'admin') NOT NULL DEFAULT 'user',
  `google_id` VARCHAR(190) DEFAULT NULL,
  `facebook_id` VARCHAR(190) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `location` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('available', 'claimed') NOT NULL DEFAULT 'available',
  `found_by_user_id` INT UNSIGNED DEFAULT NULL,
  `claimed_by_user_id` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_location` (`location`),
  CONSTRAINT `fk_items_found_by` FOREIGN KEY (`found_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_items_claimed_by` FOREIGN KEY (`claimed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Example initial admin account (change email/password before using in production)
-- INSERT INTO users (name, email, password_hash, role)
-- VALUES ('Admin', 'admin@example.com', PASSWORD_HASH_HERE, 'admin');


