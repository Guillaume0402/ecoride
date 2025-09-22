-- Version de PHP : 8.3.14
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET time_zone = "+00:00";

-- Charset / Collation
/*!40101 SET NAMES utf8mb4 */
;

-- Base de données : `ecoride`

-- roles
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `role_name` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ux_roles_role_name` (`role_name`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

INSERT IGNORE INTO
    `roles` (`id`, `role_name`)
VALUES (1, 'utilisateur'),
    (2, 'employe'),
    (3, 'admin');

-- users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `pseudo` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role_id` INT NOT NULL DEFAULT 1,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `credits` INT NULL DEFAULT 20,
    `note` DECIMAL(4, 2) NULL DEFAULT 0.00,
    `photo` VARCHAR(255) NULL,
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    `travel_role` ENUM(
        'passager',
        'chauffeur',
        'les-deux'
    ) NOT NULL DEFAULT 'passager',
    `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `email_verification_token` VARCHAR(64) NULL,
    `email_verification_expires` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ux_users_pseudo` (`pseudo`),
    UNIQUE KEY `ux_users_email` (`email`),
    KEY `fk_users_role` (`role_id`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- transactions (optionnel si vous l'utilisez tout de suite)
CREATE TABLE IF NOT EXISTS `transactions` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `montant` DECIMAL(10, 2) NOT NULL,
    `type` ENUM('debit', 'credit') NOT NULL,
    `motif` VARCHAR(255) NULL,
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_transactions_user` (`user_id`),
    CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- fuel_types
CREATE TABLE IF NOT EXISTS `fuel_types` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `type_name` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ux_fuel_types_name` (`type_name`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

INSERT IGNORE INTO
    `fuel_types` (`id`, `type_name`)
VALUES (1, 'essence'),
    (2, 'diesel'),
    (3, 'electrique'),
    (4, 'hybride');

-- vehicles
CREATE TABLE IF NOT EXISTS `vehicles` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `marque` VARCHAR(50) NOT NULL,
    `modele` VARCHAR(50) NOT NULL,
    `couleur` VARCHAR(50) NULL,
    `immatriculation` VARCHAR(20) NOT NULL,
    `date_premiere_immatriculation` DATE NOT NULL,
    `fuel_type_id` INT NULL,
    `places_dispo` INT NOT NULL,
    `preferences` TEXT NULL,
    `custom_preferences` TEXT NULL,
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ux_vehicles_immat` (`immatriculation`),
    KEY `fk_vehicles_user` (`user_id`),
    KEY `fk_vehicles_fuel` (`fuel_type_id`),
    CONSTRAINT `fk_vehicles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
    CONSTRAINT `fk_vehicles_fuel` FOREIGN KEY (`fuel_type_id`) REFERENCES `fuel_types` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- 1) Table covoiturages
CREATE TABLE IF NOT EXISTS `covoiturages` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `driver_id` INT NOT NULL,
    `vehicle_id` INT NOT NULL,
    `adresse_depart` VARCHAR(255) NOT NULL,
    `adresse_arrivee` VARCHAR(255) NOT NULL,
    `depart` DATETIME NOT NULL,
    `arrivee` DATETIME NOT NULL,
    `prix` DECIMAL(10, 2) NOT NULL,
    `places_reservees` INT NOT NULL DEFAULT 0,
    `status` ENUM(
        'en_attente',
        'demarre',
        'termine',
        'annule'
    ) NOT NULL DEFAULT 'en_attente',
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_covoit_driver` (`driver_id`),
    KEY `fk_covoit_vehicle` (`vehicle_id`),
    KEY `idx_covoit_depart` (`depart`),
    CONSTRAINT `fk_covoit_driver` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_covoit_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- Table participations
CREATE TABLE IF NOT EXISTS `participations` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `covoiturage_id` INT NOT NULL,
    `passager_id` INT NOT NULL,
    `status` ENUM(
        'confirmee',
        'annulee',
        'en_attente_validation'
    ) NOT NULL DEFAULT 'en_attente_validation',
    `date_participation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_participations_covoit` (`covoiturage_id`),
    KEY `fk_participations_user` (`passager_id`),
    KEY `idx_participations_covoit_status` (`covoiturage_id`, `status`),
    KEY `idx_participations_passager_status` (`passager_id`, `status`),
    CONSTRAINT `fk_participations_covoit` FOREIGN KEY (`covoiturage_id`) REFERENCES `covoiturages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_participations_user` FOREIGN KEY (`passager_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

COMMIT;