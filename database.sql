-- AutoCare Garage: Online Vehicle Service Booking and Cost Estimation System
-- Academic Project: Web Development II (CAT 1)
-- Database Schema Definition

CREATE DATABASE IF NOT EXISTS `autocare_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `autocare_db`;

-- Drop tables if they already exist to allow clean recreation
DROP TABLE IF EXISTS `booking_services`;
DROP TABLE IF EXISTS `service_bookings`;

-- 1. Main service bookings table
CREATE TABLE `service_bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_reference` VARCHAR(25) NOT NULL UNIQUE,
    `customer_name` VARCHAR(100) NOT NULL,
    `phone_number` VARCHAR(15) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `registration_number` VARCHAR(20) NOT NULL,
    `vehicle_type` VARCHAR(30) NOT NULL,
    `vehicle_model` VARCHAR(50) NOT NULL,
    `year_of_manufacture` INT NOT NULL,
    `preferred_date` DATE NOT NULL,
    `service_count` INT NOT NULL DEFAULT 1,
    `total_cost` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Itemized services table (1-to-many relationship with bookings)
CREATE TABLE `booking_services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL,
    `service_code` VARCHAR(50) NOT NULL,
    `service_name` VARCHAR(100) NOT NULL,
    `service_price` INT NOT NULL,
    CONSTRAINT `fk_booking_services_booking` 
        FOREIGN KEY (`booking_id`) 
        REFERENCES `service_bookings` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexing for fast lookups
CREATE INDEX `idx_bookings_ref` ON `service_bookings` (`booking_reference`);
CREATE INDEX `idx_bookings_date` ON `service_bookings` (`preferred_date`);
CREATE INDEX `idx_booking_services_booking` ON `booking_services` (`booking_id`);
