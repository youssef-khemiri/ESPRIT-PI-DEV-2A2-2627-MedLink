<?php
/**
 * config.php — Configuration et connexion PDO à MySQL
 *
 * - Démarre une session sécurisée (cookie HttpOnly + SameSite, expiration après inactivité).
 * - Calcule BASE_URL (le dossier contient des espaces : "Template Projet Web").
 * - Crée automatiquement la base de données, la table `users` et un compte
 *   administrateur par défaut lors du tout premier chargement (XAMPP : root / sans mot de passe).
 */

// ---- Sécurité des sessions ----
const SESSION_TIMEOUT = 1800;   // déconnexion automatique après 30 minutes d'inactivité

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');   // refuse un identifiant de session inventé par le client
    ini_set('session.use_only_cookies', '1');  // jamais d'identifiant de session dans l'URL
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('MEDLINKSESSID');             // nom propre au projet (évite les conflits avec d'autres projets XAMPP)
    session_set_cookie_params([
        'lifetime' => 0,                       // cookie supprimé à la fermeture du navigateur
        'path'     => '/',
        'secure'   => $https,                  // envoyé uniquement en HTTPS quand le site est en HTTPS
        'httponly' => true,                    // inaccessible au JavaScript (protège du vol par XSS)
        'samesite' => 'Lax',                   // limite l'envoi du cookie depuis d'autres sites (CSRF)
    ]);
    session_start();
}

// Expiration par inactivité
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    $wasLoggedIn = isset($_SESSION['user_id']);
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
    if ($wasLoggedIn) {
        $_SESSION['flash'] = ['type' => 'error', 'tab' => 'login', 'messages' => ['Votre session a expiré par inactivité. Veuillez vous reconnecter.']];
    }
}
$_SESSION['last_activity'] = time();

// En-têtes de sécurité HTTP
header('X-Frame-Options: SAMEORIGIN');         // empêche l'affichage du site dans une iframe étrangère (clickjacking)
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

// ---- BASE_URL : chemin web du projet, ex. /Template%20Projet%20Web ----
$docRoot = rtrim(str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$projDir = str_replace('\\', '/', __DIR__);
$relPath = ($docRoot !== '' && stripos($projDir, $docRoot) === 0) ? substr($projDir, strlen($docRoot)) : '';
define('BASE_URL', implode('/', array_map('rawurlencode', explode('/', $relPath))));

class Config
{
    private const HOST    = '127.0.0.1';
    private const DB_NAME = 'gestion_utilisateurs';
    private const USER    = 'root';
    private const PASS    = '';

    // Compte administrateur créé automatiquement si la table est vide (à changer ensuite via le Back Office)
    private const DEFAULT_ADMIN_EMAIL    = 'admin@gmail.com';
    private const DEFAULT_ADMIN_PASSWORD = 'admin';

    private static ?PDO $pdo = null;

    public static function getConnexion(): PDO
    {
        if (self::$pdo === null) {
            try {
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                try {
                    $pdo = new PDO(self::dsn(true), self::USER, self::PASS, $options);
                } catch (PDOException $e) {
                    // 1049 = base inconnue : première exécution, on la crée
                    if ((int) $e->getCode() !== 1049) {
                        throw $e;
                    }
                    $server = new PDO(self::dsn(false), self::USER, self::PASS, $options);
                    $server->exec('CREATE DATABASE IF NOT EXISTS `' . self::DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
                    $pdo = new PDO(self::dsn(true), self::USER, self::PASS, $options);
                }

                self::install($pdo);
                self::$pdo = $pdo;
            } catch (PDOException $e) {
                die('Erreur de connexion à la base de données : ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
                    . '<br>Vérifiez que MySQL est démarré dans XAMPP.');
            }
        }
        return self::$pdo;
    }

    private static function dsn(bool $withDb): string
    {
        return 'mysql:host=' . self::HOST . ($withDb ? ';dbname=' . self::DB_NAME : '') . ';charset=utf8mb4';
    }

    /** Crée la table si besoin et insère l'administrateur par défaut quand elle est vide. */
    private static function install(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
            `id`         INT(11) NOT NULL AUTO_INCREMENT,
            `first_name` VARCHAR(100) NOT NULL,
            `last_name`  VARCHAR(100) NOT NULL,
            `email`      VARCHAR(150) NOT NULL,
            `age`        INT(11) NOT NULL,
            `password`   VARCHAR(255) NOT NULL,
            `type`       ENUM('normal','admin') NOT NULL DEFAULT 'normal',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_users_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        if ((int) $pdo->query('SELECT COUNT(*) FROM `users`')->fetchColumn() === 0) {
            $stmt = $pdo->prepare('INSERT INTO `users` (first_name, last_name, email, age, password, type)
                                   VALUES (:fn, :ln, :em, :age, :pw, :type)');
            $stmt->execute([
                ':fn'   => 'Admin',
                ':ln'   => 'MEDLINK',
                ':em'   => self::DEFAULT_ADMIN_EMAIL,
                ':age'  => 30,
                ':pw'   => self::DEFAULT_ADMIN_PASSWORD,
                ':type' => 'admin',
            ]);
        }
    }
}
