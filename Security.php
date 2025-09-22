<?php
/**
 * Classe de sécurité
 * Fournit des méthodes pour sécuriser l'application
 */
class Security
{
    /**
     * Constantes pour la configuration
     */
    const DEFAULT_TOKEN_LENGTH = 32;
    const DEFAULT_ALGORITHM = PASSWORD_DEFAULT;
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOGIN_TIMEOUT = 900; // 15 minutes en secondes
    const PASSWORD_MIN_LENGTH = 8;
    const SESSION_LIFETIME = 3600; // 1 heure en secondes
    
    /**
     * Nettoie une entrée utilisateur
     * 
     * @param mixed $data Les données à nettoyer
     * @return mixed Les données nettoyées
     */
    public static function sanitizeInput($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    /**
     * Vérifie si une adresse email est valide
     * 
     * @param string $email L'adresse email à vérifier
     * @return bool True si l'email est valide
     */
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Hash un mot de passe
     * 
     * @param string $password Le mot de passe à hasher
     * @return string Le hash du mot de passe
     */
    public static function hashPassword($password)
    {
        return password_hash($password, self::DEFAULT_ALGORITHM);
    }

    /**
     * Vérifie un mot de passe
     * 
     * @param string $password Le mot de passe en clair
     * @param string $hash Le hash du mot de passe
     * @return bool True si le mot de passe correspond
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Génère un token d'authentification
     * 
     * @param int $length Longueur du token
     * @return string Le token généré
     */
    public static function generateAuthToken($length = self::DEFAULT_TOKEN_LENGTH)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Vérifie si une requête contient un token CSRF valide
     * 
     * @return bool True si le token CSRF est valide
     */
    public static function checkCsrf()
    {
        if (!isset($_POST['csrf_token']) || !self::verifyCsrfToken($_POST['csrf_token'])) {
            // Log tentative CSRF
            error_log('Tentative CSRF détectée : ' . $_SERVER['REMOTE_ADDR']);
            return false;
        }
        return true;
    }

    /**
     * Vérifie si l'utilisateur est connecté, redirige sinon
     * 
     * @param string $redirect_url URL de redirection si non connecté
     * @return void
     */
    public static function requireLogin($redirect_url = '')
    {
        if (!self::isLoggedIn()) {
            $url = !empty($redirect_url) ? $redirect_url : SITE_URL . '?page=login';
            self::redirect($url, ['error' => 'Vous devez être connecté pour accéder à cette page']);
        }
    }

    /**
     * Vérifie si l'utilisateur est un administrateur, redirige sinon
     * 
     * @param string $redirect_url URL de redirection si non admin
     * @return void
     */
    public static function requireAdmin($redirect_url = '')
    {
        if (!self::isAdmin()) {
            $url = !empty($redirect_url) ? $redirect_url : SITE_URL;
            self::redirect($url, ['error' => 'Vous n\'avez pas les droits pour accéder à cette page']);
        }
    }

    /**
     * Protège contre les attaques XSS en définissant les en-têtes de sécurité
     * 
     * @return void
     */
    public static function setSecurityHeaders()
    {
        // Protection contre le clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Protection contre les attaques MIME sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Protection XSS pour les navigateurs récents
        header('X-XSS-Protection: 1; mode=block');
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://code.jquery.com 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'unsafe-inline'; img-src 'self' data:; font-src 'self' https://cdnjs.cloudflare.com; connect-src 'self'");
        
        // Strict Transport Security pour forcer HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        
        // Protection contre le référencement
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Désactiver la mise en cache pour les pages sensibles
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }
    
    /**
     * Vérifie si l'utilisateur est connecté
     * 
     * @return bool True si l'utilisateur est connecté
     */
    private static function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Vérifie si l'utilisateur est administrateur
     * 
     * @return bool True si l'utilisateur est administrateur
     */
    private static function isAdmin()
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    /**
     * Vérifie un token CSRF
     * 
     * @param string $token Le token à vérifier
     * @return bool True si le token est valide
     */
    private static function verifyCsrfToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Redirige vers une URL avec des paramètres
     * 
     * @param string $url L'URL de redirection
     * @param array $params Les paramètres à passer
     * @return void
     */
    private static function redirect($url, $params = [])
    {
        if (!empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Initialise une session sécurisée
     * 
     * @return void
     */
    public static function initSecureSession()
    {
        // Démarrer la session avec des paramètres sécurisés
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.gc_maxlifetime', self::SESSION_LIFETIME);
        
        // Régénérer l'ID de session pour éviter la fixation de session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        } else {
            session_regenerate_id(true);
        }
        
        // Définir les en-têtes de sécurité
        self::setSecurityHeaders();
    }
    
    /**
     * Vérifie la force d'un mot de passe
     * 
     * @param string $password Le mot de passe à vérifier
     * @return array Tableau avec le statut et les messages d'erreur
     */
    public static function checkPasswordStrength($password)
    {
        $errors = [];
        
        // Vérifier la longueur minimale
        if (strlen($password) < self::PASSWORD_MIN_LENGTH) {
            $errors[] = "Le mot de passe doit contenir au moins " . self::PASSWORD_MIN_LENGTH . " caractères.";
        }
        
        // Vérifier la présence de chiffres
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
        }
        
        // Vérifier la présence de lettres minuscules
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre minuscule.";
        }
        
        // Vérifier la présence de lettres majuscules
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre majuscule.";
        }
        
        // Vérifier la présence de caractères spéciaux
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial.";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Vérifie si une adresse IP est bloquée
     * 
     * @param string $ip L'adresse IP à vérifier
     * @return bool True si l'IP est bloquée
     */
    public static function isIpBlocked($ip = null)
    {
        if ($ip === null) {
            $ip = self::getClientIp();
        }
        
        // Vérifier dans la base de données ou le cache
        // Cette implémentation est un exemple, à adapter selon votre système
        $blockedIps = self::getBlockedIps();
        return in_array($ip, $blockedIps);
    }
    
    /**
     * Bloque une adresse IP
     * 
     * @param string $ip L'adresse IP à bloquer
     * @param int $duration Durée du blocage en secondes
     * @return void
     */
    public static function blockIp($ip = null, $duration = 3600)
    {
        if ($ip === null) {
            $ip = self::getClientIp();
        }
        
        // Ajouter l'IP à la liste des IPs bloquées
        // Cette implémentation est un exemple, à adapter selon votre système
        $blockedIps = self::getBlockedIps();
        $blockedIps[$ip] = time() + $duration;
        
        // Sauvegarder la liste mise à jour
        self::saveBlockedIps($blockedIps);
        
        // Log l'action
        error_log("IP bloquée : $ip pour $duration secondes");
    }
    
    /**
     * Vérifie les tentatives de connexion
     * 
     * @param string $username Nom d'utilisateur
     * @return bool True si l'utilisateur peut se connecter
     */
    public static function checkLoginAttempts($username)
    {
        // Cette implémentation est un exemple, à adapter selon votre système
        $attempts = self::getLoginAttempts($username);
        
        // Vérifier si l'utilisateur a dépassé le nombre maximum de tentatives
        if (count($attempts) >= self::MAX_LOGIN_ATTEMPTS) {
            // Vérifier si le délai d'attente est écoulé
            $lastAttempt = end($attempts);
            if (time() - $lastAttempt < self::LOGIN_TIMEOUT) {
                return false;
            }
            
            // Réinitialiser les tentatives si le délai est écoulé
            self::resetLoginAttempts($username);
        }
        
        return true;
    }
    
    /**
     * Enregistre une tentative de connexion
     * 
     * @param string $username Nom d'utilisateur
     * @return void
     */
    public static function logLoginAttempt($username)
    {
        // Cette implémentation est un exemple, à adapter selon votre système
        $attempts = self::getLoginAttempts($username);
        $attempts[] = time();
        
        // Ne garder que les tentatives récentes
        $attempts = array_filter($attempts, function($timestamp) {
            return time() - $timestamp < self::LOGIN_TIMEOUT;
        });
        
        self::saveLoginAttempts($username, $attempts);
    }
    
    /**
     * Réinitialise les tentatives de connexion
     * 
     * @param string $username Nom d'utilisateur
     * @return void
     */
    public static function resetLoginAttempts($username)
    {
        // Cette implémentation est un exemple, à adapter selon votre système
        self::saveLoginAttempts($username, []);
    }
    
    /**
     * Obtient l'adresse IP du client
     * 
     * @return string L'adresse IP du client
     */
    public static function getClientIp()
    {
        $ip = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Nettoyer l'adresse IP
        $ip = filter_var($ip, FILTER_VALIDATE_IP);
        
        return $ip;
    }
    
    /**
     * Obtient la liste des IPs bloquées
     * 
     * @return array Liste des IPs bloquées
     */
    private static function getBlockedIps()
    {
        // Cette implémentation est un exemple, à adapter selon votre système
        // Dans un cas réel, vous utiliseriez une base de données ou un cache
        if (file_exists(__DIR__ . '/blocked_ips.json')) {
            $data = file_get_contents(__DIR__ . '/blocked_ips.json');
            $blockedIps = json_decode($data, true) ?: [];
            
            // Nettoyer les entrées expirées
            $now = time();
            foreach ($blockedIps as $ip => $expiry) {
                if ($expiry < $now) {
                    unset($blockedIps[$ip]);
                }
            }
            
            return $blockedIps;
        }
        
        return [];
    }
    
    /**
     * Sauvegarde la liste des IPs bloquées
     * 
     * @param array $blockedIps Liste des IPs bloquées
     * @return void
     */
    private static function saveBlockedIps($blockedIps)
    {
        // Cette implémentation est un exemple, à adapter selon votre système
        file_put_contents(__DIR__ . '/blocked_ips.json', json_encode($blockedIps));
    }
    
    /**
     * Obtient les tentatives de connexion
     * 
     * @param string $username Nom d'utilisateur
     * @return array Liste des tentatives
     */
    private static function getLoginAttempts($username)
    {
        // Cette implémentation est un exemple, à adapter selon votre système
        if (file_exists(__DIR__ . '/login_attempts.json')) {
            $data = file_get_contents(__DIR__ . '/login_attempts.json');
            $attempts = json_decode($data, true) ?: [];
            
            return isset($attempts[$username]) ? $attempts[$username] : [];
        }
        
        return [];
    }
    
    /**
     * Sauvegarde les tentatives de connexion
     * 
     * @param string $username Nom d'utilisateur
     * @param array $attempts Liste des tentatives
     * @return void
     */
    private static function saveLoginAttempts($username, $attempts)
    {
        // Cette implémentation est un exemple, à adapter selon votre système
        $allAttempts = [];
        
        if (file_exists(__DIR__ . '/login_attempts.json')) {
            $data = file_get_contents(__DIR__ . '/login_attempts.json');
            $allAttempts = json_decode($data, true) ?: [];
        }
        
        $allAttempts[$username] = $attempts;
        file_put_contents(__DIR__ . '/login_attempts.json', json_encode($allAttempts));
    }
    
    /**
     * Génère un token CSRF et le stocke en session
     * 
     * @return string Le token CSRF généré
     */
    public static function generateCsrfToken()
    {
        $token = self::generateAuthToken();
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    /**
     * Vérifie si une chaîne contient des caractères dangereux
     * 
     * @param string $string La chaîne à vérifier
     * @return bool True si la chaîne contient des caractères dangereux
     */
    public static function containsDangerousCharacters($string)
    {
        // Liste de caractères dangereux
        $dangerousChars = ['<', '>', '&', '"', "'", ';', '\\', '/', '(', ')', '{', '}', '[', ']'];
        
        foreach ($dangerousChars as $char) {
            if (strpos($string, $char) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Vérifie si une URL est sûre
     * 
     * @param string $url L'URL à vérifier
     * @return bool True si l'URL est sûre
     */
    public static function isUrlSafe($url)
    {
        // Vérifier si l'URL est valide
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Vérifier si l'URL est sur le même domaine
        $parsedUrl = parse_url($url);
        $parsedHost = parse_url(SITE_URL);
        
        if (!isset($parsedUrl['host']) || !isset($parsedHost['host'])) {
            return false;
        }
        
        return $parsedUrl['host'] === $parsedHost['host'];
    }
    
    /**
     * Génère une URL propre (SEO-friendly) à partir d'une chaîne
     * 
     * @param string $string La chaîne à convertir en URL propre
     * @param bool $lowercase Convertir en minuscules
     * @param bool $removeAccents Supprimer les accents
     * @param string $separator Séparateur à utiliser
     * @return string L'URL propre générée
     */
    public static function generateSlug($string, $lowercase = true, $removeAccents = true, $separator = '-')
    {
        // Supprimer les accents si demandé
        if ($removeAccents) {
            $string = self::removeAccents($string);
        }
        
        // Convertir en minuscules si demandé
        if ($lowercase) {
            $string = mb_strtolower($string, 'UTF-8');
        }
        
        // Remplacer les caractères spéciaux par le séparateur
        $string = preg_replace('/[^a-zA-Z0-9\s]/', $separator, $string);
        
        // Remplacer les espaces par le séparateur
        $string = preg_replace('/\s+/', $separator, $string);
        
        // Supprimer les séparateurs multiples
        $string = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $string);
        
        // Supprimer les séparateurs au début et à la fin
        $string = trim($string, $separator);
        
        return $string;
    }
    
    /**
     * Supprime les accents d'une chaîne
     * 
     * @param string $string La chaîne à traiter
     * @return string La chaîne sans accents
     */
    private static function removeAccents($string)
    {
        // Tableau de correspondance des caractères accentués
        $unwanted_array = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'ñ' => 'n',
            'ç' => 'c',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y',
            'Ñ' => 'N',
            'Ç' => 'C'
        ];
        
        return strtr($string, $unwanted_array);
    }
    
    /**
     * Génère une URL propre pour une ressource
     * 
     * @param string $title Le titre de la ressource
     * @param int $id L'identifiant de la ressource
     * @param string $type Le type de ressource (article, produit, etc.)
     * @return string L'URL propre générée
     */
    public static function generateCleanUrl($title, $id, $type = 'page')
    {
        // Générer le slug à partir du titre
        $slug = self::generateSlug($title);
        
        // Construire l'URL
        $url = SITE_URL . $type . '/' . $slug . '-' . $id;
        
        return $url;
    }
    
    /**
     * Extrait l'ID d'une URL propre
     * 
     * @param string $url L'URL propre
     * @return int|null L'ID extrait ou null si non trouvé
     */
    public static function extractIdFromCleanUrl($url)
    {
        // Extraire le dernier segment de l'URL
        $segments = explode('/', rtrim($url, '/'));
        $lastSegment = end($segments);
        
        // Extraire l'ID à la fin du segment
        if (preg_match('/-(\d+)$/', $lastSegment, $matches)) {
            return (int)$matches[1];
        }
        
        return null;
    }
    
    /**
     * Vérifie si une URL est une URL propre valide
     * 
     * @param string $url L'URL à vérifier
     * @return bool True si l'URL est une URL propre valide
     */
    public static function isValidCleanUrl($url)
    {
        // Vérifier si l'URL est valide
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Vérifier si l'URL est sur le même domaine
        $parsedUrl = parse_url($url);
        $parsedHost = parse_url(SITE_URL);
        
        if (!isset($parsedUrl['host']) || !isset($parsedHost['host'])) {
            return false;
        }
        
        if ($parsedUrl['host'] !== $parsedHost['host']) {
            return false;
        }
        
        // Vérifier si l'URL contient un ID valide
        return self::extractIdFromCleanUrl($url) !== null;
    }
} 