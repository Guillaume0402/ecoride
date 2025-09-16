-- Seed: trajet terminé + participation confirmée
-- À exécuter dans MySQL (phpMyAdmin ou mysql client)
-- Ajustez les IDs ci-dessous selon votre base

-- Hypothèses par défaut
SET @driver_id = 11;
-- utilisateur chauffeur existant
SET @vehicle_id = 1;
-- véhicule appartenant à @driver_id
SET @passager_id = 21;
-- utilisateur passager existant

-- Crée un covoiturage terminé il y a 1h
INSERT INTO
    covoiturages (
        driver_id,
        vehicle_id,
        adresse_depart,
        adresse_arrivee,
        depart,
        arrivee,
        prix,
        status
    )
VALUES (
        @driver_id,
        @vehicle_id,
        'Paris',
        'Lyon',
        NOW() - INTERVAL 3 HOUR,
        NOW() - INTERVAL 1 HOUR,
        5.00,
        'termine'
    );

SET @covoit_id = LAST_INSERT_ID();

-- Ajoute une participation confirmée
INSERT INTO
    participations (
        covoiturage_id,
        passager_id,
        status
    )
VALUES (
        @covoit_id,
        @passager_id,
        'confirmee'
    );

-- Optionnel: marquer 1 place réservée si votre UI l’affiche
UPDATE covoiturages
SET
    places_reservees = places_reservees + 1
WHERE
    id = @covoit_id;