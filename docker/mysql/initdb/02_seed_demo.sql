-- 02_seed_demo.sql — EcoRide (démo DWWM)
-- 3 comptes + véhicules + covoiturages (passés & à venir jusqu'en mars) + participations + transactions
-- Mot de passe démo : EcoRide!234 (hash à coller dans __HASH__)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
/*!40101 SET NAMES utf8mb4 */;

START TRANSACTION;

-- Nettoyage (utile si tu rejoues le seed manuellement ; sur init à froid ça ne gêne pas)
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE participations;
TRUNCATE TABLE covoiturages;
TRUNCATE TABLE vehicles;
TRUNCATE TABLE transactions;
TRUNCATE TABLE users;

SET FOREIGN_KEY_CHECKS = 1;

-- 1) USERS (3 comptes)
-- role_id: 1 utilisateur, 2 employe, 3 admin
INSERT INTO users
(id, pseudo, email, password, role_id, is_active, credits, note, photo, created_at, travel_role, email_verified, email_verification_token, email_verification_expires)
VALUES
(1, 'admin',    'admin@ecoride.local',    '$2y$10$8jQUwQOoQ6o3cZlglToPSefxdnuzfoeN5JctTZV/dGz1DDPOSoS0i', 3, 1, 202, 4.80, '/assets/images/logo.svg', '2026-01-10 10:00:00', 'les-deux', 1, NULL, NULL),
(2, 'employee', 'employee@ecoride.local', '$2y$10$8jQUwQOoQ6o3cZlglToPSefxdnuzfoeN5JctTZV/dGz1DDPOSoS0i', 2, 1, 117, 4.40, '/assets/images/logo.svg', '2026-01-10 10:05:00', 'chauffeur', 1, NULL, NULL),
(3, 'user',     'user@ecoride.local',     '$2y$10$8jQUwQOoQ6o3cZlglToPSefxdnuzfoeN5JctTZV/dGz1DDPOSoS0i', 1, 1,  31, 4.10, NULL,                   '2026-01-10 10:10:00', 'passager', 1, NULL, NULL);

-- 2) VEHICLES (un véhicule par compte)
-- fuel_type_id: 1 essence, 2 diesel, 3 electrique, 4 hybride (déjà seedé dans init)
INSERT INTO vehicles
(id, user_id, marque, modele, couleur, immatriculation, date_premiere_immatriculation, fuel_type_id, places_dispo, preferences, custom_preferences, created_at)
VALUES
(11, 1, 'Tesla',   'Model 3', 'Blanc', 'AA-101-AA', '2023-05-12', 3, 3, 'non-fumeur,pas-animaux', 'Trajet éco-friendly', '2026-01-10 10:20:00'),
(12, 2, 'Renault', 'Clio',    'Bleu',  'BB-202-BB', '2021-09-03', 1, 4, 'non-fumeur,animaux',    'Musique OK',         '2026-01-10 10:25:00'),
(13, 3, 'Peugeot', '208',     'Gris',  'CC-303-CC', '2020-02-18', 2, 4, 'non-fumeur,pas-animaux', NULL,                '2026-01-10 10:30:00');

-- 3) COVOITURAGES
-- Passés (termine) + futurs (en_attente) jusqu'en mars 2026
INSERT INTO covoiturages
(id, driver_id, vehicle_id, adresse_depart, adresse_arrivee, depart, arrivee, prix, places_reservees, status, created_at)
VALUES
-- PASSÉ : employé conduit, user participe
(101, 2, 12, 'Toulouse', 'Albi',     '2025-12-15 08:00:00', '2025-12-15 09:05:00',  7.00, 1, 'termine',   '2025-12-10 12:00:00'),

-- PASSÉ : admin conduit, user participe
(102, 1, 11, 'Paris',    'Orléans',  '2026-01-05 18:30:00', '2026-01-05 20:00:00', 12.00, 1, 'termine',   '2026-01-02 09:00:00'),

-- PASSÉ : employé conduit, admin participe (montre que l’admin peut être passager)
(103, 2, 12, 'Lyon',     'Grenoble', '2026-01-18 07:30:00', '2026-01-18 08:40:00', 10.00, 1, 'termine',   '2026-01-15 15:00:00'),

-- FUTUR : user demande une participation
(201, 2, 12, 'Toulouse', 'Montauban','2026-02-10 17:30:00', '2026-02-10 18:20:00',  8.00, 0, 'en_attente','2026-02-01 10:00:00'),

-- FUTUR : user demande une participation (jusqu’en mars)
(202, 1, 11, 'Paris',    'Reims',    '2026-03-05 09:00:00', '2026-03-05 10:40:00', 15.00, 0, 'en_attente','2026-02-20 10:00:00'),

-- FUTUR : autre trajet employé (mars)
(203, 2, 12, 'Lille',    'Arras',    '2026-03-20 18:00:00', '2026-03-20 18:45:00',  9.00, 0, 'en_attente','2026-03-01 10:00:00');

-- 4) PARTICIPATIONS
-- Pour les trajets terminés : confirmee
-- Pour les trajets futurs : en_attente_validation
INSERT INTO participations
(id, covoiturage_id, passager_id, status, date_participation)
VALUES
(1001, 101, 3, 'confirmee',              '2025-12-12 14:00:00'),
(1002, 102, 3, 'confirmee',              '2026-01-03 11:00:00'),
(1003, 103, 1, 'confirmee',              '2026-01-16 09:00:00'),

(1004, 201, 3, 'en_attente_validation',  '2026-02-02 12:00:00'),
(1005, 202, 3, 'en_attente_validation',  '2026-02-21 12:00:00');

-- 5) TRANSACTIONS (uniquement pour les trajets terminés)
-- Règle simple : passager = debit du prix ; conducteur = credit du prix
INSERT INTO transactions
(user_id, montant, type, motif, created_at)
VALUES
-- Trajet 101 (user -> debit 7, employee -> credit 7)
(3,  7.00, 'debit',  'Participation trajet #101', '2025-12-15 09:10:00'),
(2,  7.00, 'credit', 'Crédit conducteur trajet #101 - passager #3', '2025-12-15 09:15:00'),

-- Trajet 102 (user -> debit 12, admin -> credit 12)
(3, 12.00, 'debit',  'Participation trajet #102', '2026-01-05 20:05:00'),
(1, 12.00, 'credit', 'Crédit conducteur trajet #102 - passager #3', '2026-01-05 20:10:00'),

-- Trajet 103 (admin -> debit 10, employee -> credit 10)
(1, 10.00, 'debit',  'Participation trajet #103', '2026-01-18 08:45:00'),
(2, 10.00, 'credit', 'Crédit conducteur trajet #103 - passager #1', '2026-01-18 08:50:00');

COMMIT;
