<?php

namespace App\Repository;

use App\Db\Mysql;
use App\Entity\CovoiturageEntity;

// Repository responsable des accès à la table "covoiturages"
// (création, recherche, statistiques, listes pour l'admin, etc.)
class CovoiturageRepository
{
    // Connexion PDO vers la base MySQL
    private \PDO $conn;
    // Nom de la table des covoiturages
    private string $table = 'covoiturages';

    // Au constructeur, on récupère l'instance unique de connexion MySQL
    public function __construct()
    {
        $this->conn = Mysql::getInstance()->getPDO();
    }

    // Créé un nouveau covoiturage en base à partir d'une entité
    public function create(CovoiturageEntity $c): bool
    {
        $sql = "INSERT INTO {$this->table} (driver_id, vehicle_id, adresse_depart, adresse_arrivee, depart, arrivee, prix, status, created_at)
                VALUES (:driver_id, :vehicle_id, :adresse_depart, :adresse_arrivee, :depart, :arrivee, :prix, :status, NOW())";

        // Prépare la requête SQL avec des paramètres nommés
        $stmt = $this->conn->prepare($sql);
        $ok = $stmt->execute([
            ':driver_id' => $c->getDriverId(),
            ':vehicle_id' => $c->getVehicleId(),
            ':adresse_depart' => $c->getAdresseDepart(),
            ':adresse_arrivee' => $c->getAdresseArrivee(),
            ':depart' => $c->getDepart(),
            ':arrivee' => $c->getArrivee(),
            ':prix' => $c->getPrix(),
            ':status' => $c->getStatus(),
        ]);

        // Si l'insertion réussit, on renseigne l'id auto-incrémenté dans l'entité
        if ($ok) {
            $c->setId((int)$this->conn->lastInsertId());
        }
        return $ok;
    }

    // Recherche de covoiturages par ville de départ, d'arrivée, date, préférences, tri…
    // Retourne un tableau de lignes SQL (array associatif)
    public function search(?string $depart = null, ?string $arrivee = null, ?string $date = null, array $prefs = [], ?string $sort = null, ?string $dir = null, ?int $currentUserId = null): array
    {
        $sql = "SELECT c.*,
               u.pseudo AS driver_pseudo, u.photo AS driver_photo, u.note AS driver_note,
               v.marque AS vehicle_marque, v.modele AS vehicle_modele, v.couleur AS vehicle_couleur,
               v.places_dispo AS vehicle_places,
               v.preferences AS vehicle_preferences, v.custom_preferences AS vehicle_prefs_custom,
               COALESCE(v.places_dispo, 0) - COALESCE(COUNT(p.id), 0) AS places_restantes,
               COALESCE(COUNT(p.id), 0) AS reservations_count";

        // Participation personnelle (optionnelle)
        if ($currentUserId !== null) {
            $sql .= ",
                                             (
                                                 SELECT ps.status
                                                 FROM participations ps
                                                 WHERE ps.covoiturage_id = c.id AND ps.passager_id = :me
                                                 ORDER BY ps.date_participation DESC
                                                 LIMIT 1
                                             ) AS my_participation_status,
                                             EXISTS (
                                                 SELECT 1
                                                 FROM participations ps2
                                                 WHERE ps2.covoiturage_id = c.id AND ps2.passager_id = :me AND ps2.status <> 'annulee'
                                             ) AS has_my_participation";
        }

        $sql .= "
                FROM {$this->table} c
                LEFT JOIN users u ON u.id = c.driver_id
                LEFT JOIN vehicles v ON v.id = c.vehicle_id
        LEFT JOIN participations p ON p.covoiturage_id = c.id AND p.status = 'confirmee'
    ";

        $sql .= " WHERE 1=1";
        // N'afficher que les trajets actifs (exclut les trajets annulés/terminés)
        $sql .= " AND c.status NOT IN ('annule','termine')";
        // Cacher les trajets passés (uniquement les départs futurs ou en cours)
        $sql .= " AND c.depart >= NOW()";
        $params = [];
        // Filtre éventuel sur la ville/adresse de départ
        if ($depart !== null && $depart !== '') {
            $sql .= " AND c.adresse_depart LIKE :depart";
            $params[':depart'] = '%' . $depart . '%';
        }
        // Filtre éventuel sur la ville/adresse d'arrivée
        if ($arrivee !== null && $arrivee !== '') {
            $sql .= " AND c.adresse_arrivee LIKE :arrivee";
            $params[':arrivee'] = '%' . $arrivee . '%';
        }
        // Filtre éventuel sur le jour précis (date sans l'heure)
        if ($date !== null && $date !== '') {
            $sql .= " AND DATE(c.depart) = :date";
            $params[':date'] = $date;
        }
        if (!empty($prefs)) {
            // Whitelist simple
            $allowed = ['fumeur', 'non-fumeur', 'animaux', 'pas-animaux'];
            $prefs = array_values(array_intersect($allowed, array_map('strval', (array) $prefs)));
            foreach ($prefs as $idx => $p) {
                $ph = ":pref$idx";
                $sql .= " AND FIND_IN_SET($ph, v.preferences) > 0";
                $params[$ph] = $p;
            }
        }
        $sql .= " GROUP BY c.id";

        // Tri sécurisé : on ne permet de trier que sur des colonnes connues
        $allowedSort = [
            'date'  => 'c.depart',
            'price' => 'c.prix'
        ];
        $orderBy = $allowedSort[$sort ?? 'date'] ?? 'c.depart';
        $direction = strtoupper($dir ?? 'ASC');
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'ASC';
        }
        $sql .= " ORDER BY $orderBy $direction LIMIT 100";

        if ($currentUserId !== null) {
            $params[':me'] = $currentUserId;
        }
        // Prépare puis exécute la requête avec tous les paramètres
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Récupère un covoiturage (une seule ligne) avec infos véhicule et conducteur
    public function findOneWithVehicleById(int $id): ?array
    {
        $sql = "SELECT c.*,
                   v.places_dispo AS vehicle_places,
                   v.marque AS vehicle_marque,
                   v.modele AS vehicle_modele,
                   v.couleur AS vehicle_couleur,
                   v.immatriculation AS vehicle_immatriculation,

                   v.preferences AS vehicle_preferences,
                   v.custom_preferences AS vehicle_prefs_custom,

                   u.pseudo AS driver_pseudo,
                   u.photo AS driver_photo
            FROM {$this->table} c
            LEFT JOIN vehicles v ON v.id = c.vehicle_id
            LEFT JOIN users u ON u.id = c.driver_id
            WHERE c.id = :id
            LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }


    // Liste les covoiturages dont l'utilisateur est le conducteur
    public function findByDriverId(int $driverId): array
    {
        $sql = "SELECT c.*,
                              u.pseudo AS driver_pseudo,
                              v.marque AS vehicle_marque, v.modele AS vehicle_modele, v.couleur AS vehicle_couleur,
                              v.places_dispo AS vehicle_places,
                              COALESCE(v.places_dispo, 0) - COALESCE((
                                 SELECT COUNT(*) FROM participations p WHERE p.covoiturage_id = c.id AND p.status = 'confirmee'
                              ), 0) AS places_restantes,
                              -- Détails additionnels
                              (SELECT COUNT(*) FROM participations p WHERE p.covoiturage_id = c.id AND p.status = 'confirmee') AS confirmed_count,
                              (SELECT COUNT(*) FROM participations p WHERE p.covoiturage_id = c.id AND p.status = 'en_attente_validation') AS pending_count,
                              (SELECT GROUP_CONCAT(u2.pseudo SEPARATOR ', ')
                                  FROM participations p2
                                  JOIN users u2 ON u2.id = p2.passager_id
                                 WHERE p2.covoiturage_id = c.id AND p2.status = 'confirmee') AS confirmed_passengers
                     FROM {$this->table} c
                     LEFT JOIN users u ON u.id = c.driver_id
                     LEFT JOIN vehicles v ON v.id = c.vehicle_id
                     WHERE c.driver_id = :d
                     ORDER BY c.depart DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':d' => $driverId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Met à jour le statut d'un covoiturage (avec contrôle sur les valeurs autorisées)
    public function updateStatus(int $id, string $status): bool
    {
        $allowed = ['en_attente', 'demarre', 'termine', 'annule'];
        if (!in_array($status, $allowed, true)) return false;
        $sql = "UPDATE {$this->table} SET status = :s WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':s' => $status, ':id' => $id]);
    }

    // === Stats ===
    // Retourne le nombre total de covoiturages en base
    public function countAll(): int
    {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }

    // Retourne la somme de tous les prix de covoiturages
    public function sumPrixAll(): float
    {
        $stmt = $this->conn->query("SELECT COALESCE(SUM(prix),0) FROM {$this->table}");
        return (float) $stmt->fetchColumn();
    }

    // Compte les covoiturages qui partent aujourd'hui
    public function countToday(): int
    {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table} WHERE DATE(depart) = CURDATE()");
        return (int) $stmt->fetchColumn();
    }

    // Retourne, pour chaque jour, le nombre de covoiturages sur une période donnée
    public function seriesByDay(int $days = 7): array
    {
        // Sécurise la fenêtre de jours (entre 1 et 60)
        $days = max(1, min(60, $days));
        $sql = "SELECT DATE(depart) AS d, COUNT(*) AS n
                FROM {$this->table}
                WHERE depart >= DATE_SUB(CURDATE(), INTERVAL :d DAY)
                GROUP BY DATE(depart)
                ORDER BY d ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':d' => $days]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $out = [];
        foreach ($rows as $r) {
            // Clé = date (YYYY-MM-DD), valeur = nombre de trajets
            $out[$r['d']] = (int)$r['n'];
        }
        return $out;
    }

    // Calcule la somme des prix par jour sur une période donnée
    public function sumPrixByDay(int $days = 7): array
    {
        // Sécurise la fenêtre de jours (entre 1 et 60)
        $days = max(1, min(60, $days));
        $sql = "SELECT DATE(depart) AS d, SUM(prix) AS s
                FROM {$this->table}
                WHERE depart >= DATE_SUB(CURDATE(), INTERVAL :d DAY)
                GROUP BY DATE(depart)
                ORDER BY d ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':d' => $days]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $out = [];
        foreach ($rows as $r) {
            // Clé = date (YYYY-MM-DD), valeur = somme des prix
            $out[$r['d']] = (float)$r['s'];
        }
        return $out;
    }

    // Liste pour l'admin : tous les covoiturages avec quelques stats simples
    public function findAllAdmin(string $scope = 'all', int $limit = 500): array
    {
        // Sécurise la limite pour éviter de charger trop de lignes
        $limit = max(1, min(1000, $limit));
        $sql = "SELECT c.*,
                       u.pseudo AS driver_pseudo,
                       v.marque AS vehicle_marque, v.modele AS vehicle_modele, v.couleur AS vehicle_couleur,
                       v.places_dispo AS vehicle_places,
                       (SELECT COUNT(*) FROM participations p WHERE p.covoiturage_id = c.id AND p.status = 'confirmee') AS confirmed_count,
                       (SELECT COUNT(*) FROM participations p WHERE p.covoiturage_id = c.id AND p.status = 'en_attente_validation') AS pending_count
                FROM {$this->table} c
                LEFT JOIN users u ON u.id = c.driver_id
                LEFT JOIN vehicles v ON v.id = c.vehicle_id
                WHERE 1=1";

        // Date/heure actuelle pour filtrer passé / futur / en cours
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $params = [];
        switch ($scope) {
            case 'past':
                // Trajets déjà partis
                $sql .= " AND c.depart < :now";
                $params[':now'] = $now;
                break;
            case 'ongoing':
                // Trajets en cours (statut "demarre")
                $sql .= " AND c.status = 'demarre'";
                break;
            case 'future':
                // Trajets à venir
                $sql .= " AND c.depart >= :now";
                $params[':now'] = $now;
                break;
            case 'all':
            default:
                // pas de filtre supplémentaire
                break;
        }

        $sql .= " ORDER BY c.depart DESC LIMIT {$limit}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Destinations populaires basées sur le nombre de trajets à venir (non annulés/terminés).
     * Retourne un tableau de destinations avec les départs les plus fréquents et prix min/moyen.
     * Format:
     * [
     *   [
     *     'arrivee' => 'Paris',
     *     'count' => 42,
     *     'departures' => [
     *        ['depart' => 'Lille', 'count' => 12, 'min_prix' => 20.0, 'avg_prix' => 28.5],
     *        ...
     *     ]
     *   ],
     *   ...
     * ]
     */
    // Retourne les destinations les plus demandées avec, pour chaque destination,
    // les villes de départ les plus fréquentes (et quelques stats de prix)
    public function popularDestinations(int $destLimit = 6, int $perDestDepartLimit = 4, int $daysBack = 30): array
    {
        // Sécurise les paramètres pour éviter des requêtes trop lourdes
        $destLimit = max(1, min(24, $destLimit));
        $perDestDepartLimit = max(1, min(10, $perDestDepartLimit));
        $daysBack = max(0, min(365, $daysBack));

        // Top destinations par nombre de trajets à venir OU partis sur les 30 derniers jours
        $whereWindow = $daysBack > 0
            ? "(c.depart >= NOW() OR c.depart >= DATE_SUB(NOW(), INTERVAL {$daysBack} DAY))"
            : "c.depart >= NOW()";
        $sqlTop = "SELECT c.adresse_arrivee AS arrivee, COUNT(*) AS cnt
                                     FROM {$this->table} c
                                     WHERE c.status NOT IN ('annule','termine')
                                         AND {$whereWindow}
                                         AND c.adresse_arrivee IS NOT NULL AND c.adresse_arrivee <> ''
                                     GROUP BY c.adresse_arrivee
                                     ORDER BY cnt DESC, arrivee ASC
                                     LIMIT {$destLimit}";
        $topStmt = $this->conn->query($sqlTop);
        $tops = $topStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Si aucune destination trouvée, on retourne un tableau vide
        if (empty($tops)) return [];

        $out = [];
        $sqlDeps = "SELECT c.adresse_depart AS depart, COUNT(*) AS cnt, MIN(c.prix) AS min_prix, AVG(c.prix) AS avg_prix
                                        FROM {$this->table} c
                                        WHERE c.status NOT IN ('annule','termine')
                                            AND {$whereWindow}
                                            AND c.adresse_arrivee = :arr
                                            AND c.adresse_depart IS NOT NULL AND c.adresse_depart <> ''
                                        GROUP BY c.adresse_depart
                                        ORDER BY cnt DESC, depart ASC
                                        LIMIT {$perDestDepartLimit}";
        $depStmt = $this->conn->prepare($sqlDeps);

        foreach ($tops as $row) {
            $arr = (string) $row['arrivee'];
            $depStmt->execute([':arr' => $arr]);
            $deps = $depStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $out[] = [
                'arrivee' => $arr,
                'count' => (int)$row['cnt'],
                'departures' => array_map(function ($d) {
                    return [
                        'depart' => (string)$d['depart'],
                        'count' => (int)$d['cnt'],
                        'min_prix' => isset($d['min_prix']) ? (float)$d['min_prix'] : null,
                        'avg_prix' => isset($d['avg_prix']) ? (float)$d['avg_prix'] : null,
                    ];
                }, $deps),
            ];
        }

        return $out;
    }
}
