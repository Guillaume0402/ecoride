-- Seed JawsDB (données fournies le 2025-09-22)
-- Hypothèses: schéma déjà créé via init.sql ; on ne touche pas aux tables roles/fuel_types

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET time_zone = "+00:00";
/*!40101 SET NAMES utf8mb4 */
;

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM participations;

DELETE FROM covoiturages;

DELETE FROM vehicles;

DELETE FROM transactions;

DELETE FROM users;

SET FOREIGN_KEY_CHECKS = 1;

-- users (credits en INT)
INSERT INTO
    `users` (
        `id`,
        `pseudo`,
        `email`,
        `password`,
        `role_id`,
        `is_active`,
        `credits`,
        `note`,
        `photo`,
        `created_at`,
        `travel_role`,
        `email_verified`,
        `email_verification_token`,
        `email_verification_expires`
    )
VALUES (
        7,
        'g.maignaut',
        'g.maignaut@gmail.com',
        '$argon2id$v=19$m=131072,t=3,p=2$VWUuUjE0WXNtQ2dqVmV4UA$+OCHnpBoumoxsXfJeu/agCz6FEIoab+NDyPJBI8Mn3s',
        1,
        1,
        20,
        4.28,
        '/uploads/8483e39e9850260c.jpg',
        '2025-09-16 08:16:33',
        'les-deux',
        1,
        NULL,
        NULL
    ),
    (
        8,
        'yanismaignaut',
        'yanismaignaut@gmail.com',
        '$argon2id$v=19$m=131072,t=3,p=2$NGFnLjB5YWt3WVRyOU8yeA$14PVU5KVBoHfpyewVWWCrzhtCk/JaYdGBzIHasCEpys',
        2,
        1,
        20,
        3.75,
        '/assets/images/logo.svg',
        '2025-09-16 09:15:04',
        'les-deux',
        1,
        NULL,
        NULL
    ),
    (
        10,
        'admin',
        'maignaut.g@gmail.com',
        '$argon2id$v=19$m=131072,t=3,p=2$WjFiYkp4THJTdU5Rck1EVA$/5sxRCJ1TG4yyCr4lm0MwSqNu3Yj4DpSlUFUJnPzhEo',
        3,
        1,
        20,
        0.00,
        '/assets/images/logo.svg',
        '2025-09-17 11:11:09',
        'les-deux',
        1,
        NULL,
        NULL
    ),
    (
        11,
        'user1',
        'user1@example.com',
        '$2b$12$pf6gnMbXhRNYfVsjd0JxsuNbHCSZuy7c8RAi.tABKy4vfxRsh4tUS',
        1,
        1,
        20,
        3.72,
        NULL,
        '2025-09-19 06:03:23',
        'passager',
        1,
        NULL,
        NULL
    ),
    (
        12,
        'user2',
        'user2@example.com',
        '$2b$12$pf6gnMbXhRNYfVsjd0JxsuNbHCSZuy7c8RAi.tABKy4vfxRsh4tUS',
        1,
        1,
        20,
        4.08,
        NULL,
        '2025-09-19 06:03:23',
        'chauffeur',
        1,
        NULL,
        NULL
    ),
    (
        13,
        'user3',
        'user3@example.com',
        '$2b$12$pf6gnMbXhRNYfVsjd0JxsuNbHCSZuy7c8RAi.tABKy4vfxRsh4tUS',
        1,
        1,
        20,
        4.26,
        NULL,
        '2025-09-19 06:03:23',
        'passager',
        1,
        NULL,
        NULL
    ),
    (
        14,
        'user4',
        'user4@example.com',
        '$2b$12$pf6gnMbXhRNYfVsjd0JxsuNbHCSZuy7c8RAi.tABKy4vfxRsh4tUS',
        1,
        1,
        20,
        3.84,
        NULL,
        '2025-09-19 06:03:23',
        'les-deux',
        1,
        NULL,
        NULL
    ),
    (
        15,
        'user5',
        'user5@example.com',
        '$2b$12$pf6gnMbXhRNYfVsjd0JxsuNbHCSZuy7c8RAi.tABKy4vfxRsh4tUS',
        1,
        1,
        20,
        3.63,
        NULL,
        '2025-09-19 06:03:23',
        'les-deux',
        1,
        NULL,
        NULL
    ),
    (
        16,
        'user6',
        'user6@example.com',
        '$2b$12$pf6gnMbXhRNYfVsjd0JxsuNbHCSZuy7c8RAi.tABKy4vfxRsh4tUS',
        1,
        1,
        20,
        4.84,
        NULL,
        '2025-09-19 06:03:23',
        'passager',
        1,
        NULL,
        NULL
    ),
    (
        17,
        'user7',
        'user7@example.com',
        '$2b$12$pf6gnMbXhRNYfVsjd0JxsuNbHCSZuy7c8RAi.tABKy4vfxRsh4tUS',
        1,
        1,
        20,
        3.88,
        NULL,
        '2025-09-19 06:03:23',
        'passager',
        1,
        NULL,
        NULL
    ),
    (
        18,
        'employee1',
        'employee1@example.com',
        '$2b$12$pf6gnMbXhRNYfVsjd0JxsuNbHCSZuy7c8RAi.tABKy4vfxRsh4tUS',
        2,
        1,
        20,
        4.53,
        NULL,
        '2025-09-19 06:03:23',
        'chauffeur',
        1,
        NULL,
        NULL
    ),
    (
        19,
        'employee2',
        'employee2@example.com',
        '$2b$12$pf6gnMbXhRNYfVsjd0JxsuNbHCSZuy7c8RAi.tABKy4vfxRsh4tUS',
        2,
        1,
        20,
        3.53,
        NULL,
        '2025-09-19 06:03:23',
        'chauffeur',
        1,
        NULL,
        NULL
    ),
    (
        20,
        'employee3',
        'employee3@example.com',
        '$2b$12$pf6gnMbXhRNYfVsjd0JxsuNbHCSZuy7c8RAi.tABKy4vfxRsh4tUS',
        2,
        1,
        20,
        3.75,
        NULL,
        '2025-09-19 06:03:23',
        'chauffeur',
        1,
        NULL,
        NULL
    );

-- vehicles
INSERT INTO
    `vehicles` (
        `id`,
        `user_id`,
        `marque`,
        `modele`,
        `couleur`,
        `immatriculation`,
        `date_premiere_immatriculation`,
        `fuel_type_id`,
        `places_dispo`,
        `preferences`,
        `custom_preferences`,
        `created_at`
    )
VALUES (
        1,
        7,
        'Renault',
        'C3',
        'Marron',
        '123AZ23',
        '2025-09-01',
        1,
        1,
        'fumeur,animaux',
        '',
        '2025-09-16 09:13:15'
    ),
    (
        2,
        8,
        'Citroën',
        'Scenic',
        'Marron',
        '433GR65',
        '2025-09-17',
        3,
        3,
        'fumeur,animaux',
        'Rigolade et bonne humeur',
        '2025-09-17 11:04:14'
    ),
    (
        3,
        8,
        'Peugeot',
        'Scenic',
        'blanche',
        '212RE55',
        '2025-09-10',
        1,
        1,
        'fumeur,non-fumeur,animaux,pas-animaux',
        '',
        '2025-09-18 07:58:32'
    ),
    (
        4,
        8,
        'Citroën',
        'Scenic 3',
        'blanche',
        '980AA98',
        '2025-09-03',
        4,
        1,
        'fumeur,non-fumeur,animaux,pas-animaux',
        '',
        '2025-09-18 07:59:18'
    ),
    (
        5,
        8,
        'Peugeot',
        'Scenic',
        'Noire',
        '567YY76',
        '2025-09-16',
        2,
        1,
        'fumeur,non-fumeur,animaux,pas-animaux',
        '',
        '2025-09-18 08:02:59'
    ),
    (
        48,
        11,
        'Peugeot',
        '208',
        'Bleu',
        'AA123BB',
        '2020-03-15',
        3,
        3,
        'non-fumeur, pas-animaux',
        NULL,
        '2025-09-19 06:13:19'
    ),
    (
        49,
        11,
        'Renault',
        'Clio',
        'Rouge',
        'BB234CC',
        '2019-07-21',
        1,
        4,
        'fumeur, animaux',
        'Musique pendant le trajet',
        '2025-09-19 06:13:19'
    ),
    (
        50,
        12,
        'Citroën',
        'C3',
        'Noir',
        'CC345DD',
        '2021-06-10',
        2,
        4,
        'non-fumeur, pas-animaux',
        NULL,
        '2025-09-19 06:13:19'
    ),
    (
        51,
        13,
        'Volkswagen',
        'Golf',
        'Gris',
        'DD456EE',
        '2018-11-30',
        1,
        3,
        'non-fumeur, pas-animaux',
        NULL,
        '2025-09-19 06:13:19'
    ),
    (
        52,
        13,
        'Tesla',
        'Model 3',
        'Blanc',
        'EE567FF',
        '2023-02-05',
        3,
        4,
        'non-fumeur, pas-animaux',
        'Trajet éco-friendly',
        '2025-09-19 06:13:19'
    ),
    (
        53,
        14,
        'Ford',
        'Focus',
        'Vert',
        'FF678GG',
        '2020-12-01',
        1,
        4,
        'fumeur, animaux',
        NULL,
        '2025-09-19 06:13:19'
    ),
    (
        54,
        15,
        'Toyota',
        'Yaris',
        'Jaune',
        'GG789HH',
        '2021-04-12',
        4,
        3,
        'non-fumeur, pas-animaux',
        'Confort familial',
        '2025-09-19 06:13:19'
    ),
    (
        55,
        16,
        'BMW',
        'Serie 1',
        'Noir',
        'HH890II',
        '2019-08-08',
        1,
        4,
        'non-fumeur, pas-animaux',
        NULL,
        '2025-09-19 06:13:19'
    ),
    (
        56,
        16,
        'Nissan',
        'Leaf',
        'Blanc',
        'II901JJ',
        '2022-09-22',
        3,
        4,
        'non-fumeur, pas-animaux',
        'Recharge gratuite incluse',
        '2025-09-19 06:13:19'
    ),
    (
        57,
        17,
        'Audi',
        'A3',
        'Bleu marine',
        'JJ012KK',
        '2017-05-19',
        1,
        4,
        'fumeur, animaux',
        NULL,
        '2025-09-19 06:13:19'
    ),
    (
        58,
        18,
        'Mercedes',
        'Classe A',
        'Gris argent',
        'KK123LL',
        '2018-10-11',
        1,
        4,
        'non-fumeur, pas-animaux',
        NULL,
        '2025-09-19 06:13:19'
    ),
    (
        59,
        18,
        'Peugeot',
        '3008',
        'Rouge',
        'LL234MM',
        '2021-01-15',
        4,
        5,
        'non-fumeur, pas-animaux',
        'Trajet long-courrier',
        '2025-09-19 06:13:19'
    ),
    (
        60,
        19,
        'Renault',
        'Zoe',
        'Blanc',
        'MM345NN',
        '2022-07-07',
        3,
        4,
        'non-fumeur, pas-animaux',
        'Écologique',
        '2025-09-19 06:13:19'
    ),
    (
        61,
        20,
        'Fiat',
        '500',
        'Rose',
        'NN456OO',
        '2020-02-25',
        1,
        3,
        'non-fumeur, pas-animaux',
        NULL,
        '2025-09-19 06:13:19'
    ),
    (
        62,
        10,
        'Peugeot',
        'Scenic',
        'Noire',
        '567UU76',
        '2025-09-01',
        4,
        3,
        'fumeur,animaux',
        '',
        '2025-09-22 10:13:28'
    );

-- covoiturages
INSERT INTO
    `covoiturages` (
        `id`,
        `driver_id`,
        `vehicle_id`,
        `adresse_depart`,
        `adresse_arrivee`,
        `depart`,
        `arrivee`,
        `prix`,
        `places_reservees`,
        `status`,
        `created_at`
    )
VALUES (
        316,
        7,
        1,
        'Toulouse',
        'Fleurance',
        '2025-09-22 12:52:00',
        '2025-09-22 13:52:00',
        5.00,
        0,
        'termine',
        '2025-09-22 09:52:58'
    ),
    (
        317,
        7,
        1,
        'Fleurance',
        'Toulouse',
        '2025-09-22 14:53:00',
        '2025-09-22 15:53:00',
        5.00,
        0,
        'termine',
        '2025-09-22 09:53:38'
    ),
    (
        318,
        8,
        3,
        'Paris',
        'Lille',
        '2025-09-22 12:54:00',
        '2025-09-22 13:54:00',
        5.00,
        0,
        'termine',
        '2025-09-22 09:54:40'
    ),
    (
        319,
        8,
        2,
        'Toulouse',
        'Auch',
        '2025-09-22 15:55:00',
        '2025-09-22 16:55:00',
        5.00,
        0,
        'termine',
        '2025-09-22 09:55:27'
    ),
    (
        320,
        10,
        62,
        'Toulouse',
        'Fleurance',
        '2025-09-22 16:13:00',
        '2025-09-22 17:13:00',
        5.00,
        0,
        'termine',
        '2025-09-22 10:13:54'
    );

-- participations
INSERT INTO
    `participations` (
        `id`,
        `covoiturage_id`,
        `passager_id`,
        `status`,
        `date_participation`
    )
VALUES (
        1199,
        316,
        10,
        'confirmee',
        '2025-09-22 10:27:10'
    ),
    (
        1200,
        319,
        10,
        'confirmee',
        '2025-09-22 10:31:47'
    ),
    (
        1201,
        318,
        7,
        'confirmee',
        '2025-09-22 10:32:19'
    ),
    (
        1202,
        320,
        7,
        'confirmee',
        '2025-09-22 10:32:33'
    ),
    (
        1203,
        317,
        8,
        'confirmee',
        '2025-09-22 10:33:14'
    ),
    (
        1204,
        320,
        8,
        'confirmee',
        '2025-09-22 10:33:21'
    );

-- transactions
INSERT INTO
    `transactions` (
        `id`,
        `user_id`,
        `montant`,
        `type`,
        `motif`,
        `created_at`
    )
VALUES (
        3608,
        10,
        5.00,
        'debit',
        'Participation trajet #316',
        '2025-09-22 10:29:34'
    ),
    (
        3609,
        7,
        5.00,
        'debit',
        'Participation trajet #318',
        '2025-09-22 10:32:59'
    ),
    (
        3610,
        10,
        5.00,
        'debit',
        'Participation trajet #319',
        '2025-09-22 10:33:01'
    ),
    (
        3611,
        8,
        5.00,
        'debit',
        'Participation trajet #317',
        '2025-09-22 10:33:51'
    ),
    (
        3612,
        7,
        5.00,
        'debit',
        'Participation trajet #320',
        '2025-09-22 10:34:13'
    ),
    (
        3613,
        8,
        5.00,
        'debit',
        'Participation trajet #320',
        '2025-09-22 10:34:15'
    ),
    (
        3614,
        8,
        5.00,
        'credit',
        'Crédit conducteur trajet #318 - passager #7',
        '2025-09-22 11:14:38'
    ),
    (
        3615,
        7,
        5.00,
        'credit',
        'Crédit conducteur trajet #316 - passager #10',
        '2025-09-22 11:16:08'
    ),
    (
        3616,
        7,
        5.00,
        'credit',
        'Crédit conducteur trajet #317 - passager #8',
        '2025-09-22 13:07:52'
    ),
    (
        3617,
        8,
        5.00,
        'credit',
        'Crédit conducteur trajet #319 - passager #10',
        '2025-09-22 14:03:36'
    ),
    (
        3618,
        10,
        5.00,
        'credit',
        'Crédit conducteur trajet #320 - passager #7',
        '2025-09-22 14:16:07'
    ),
    (
        3619,
        10,
        5.00,
        'credit',
        'Crédit conducteur trajet #320 - passager #8',
        '2025-09-22 14:16:51'
    );

COMMIT;