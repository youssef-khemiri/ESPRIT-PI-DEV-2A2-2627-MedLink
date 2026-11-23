<?php
/**
 * controllers/UserController.php — Logique métier des utilisateurs
 *
 * Page de connexion/inscription (views/FrontOffice/login.php) : login, register (le type est TOUJOURS 'normal'), logout
 * Back Office (admin seul): liste, ajout, modification, suppression des utilisateurs
 *                           (c'est ici qu'un admin peut créer un admin ou changer le type d'un utilisateur)
 *
 * Les formulaires envoient leurs données à ce fichier : controllers/UserController.php?action=...
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/User.php';

class UserController
{
    private PDO $db;
    private ?array $current = null;
    private bool $currentLoaded = false;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    /* ======================= URLs ======================= */

    public static function frontUrl(): string { return BASE_URL . '/views/FrontOffice/index.php'; }
    public static function loginUrl(): string { return BASE_URL . '/views/FrontOffice/login.php'; }
    public static function backUrl(): string { return BASE_URL . '/views/BackOffice/index.php'; }
    public static function backFormUrl(?int $id = null): string
    {
        return BASE_URL . '/views/BackOffice/user-form.php' . ($id ? '?id=' . $id : '');
    }
    public static function actionUrl(string $action): string
    {
        return BASE_URL . '/controllers/UserController.php?action=' . $action;
    }

    /* =================== Session / flash / CSRF =================== */

    public function csrfToken(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    private function checkCsrf(string $fallbackUrl): void
    {
        $sent = (string) ($_POST['csrf'] ?? '');
        if ($sent === '' || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
            $this->flash(['type' => 'error', 'tab' => 'login', 'messages' => ['Session expirée, veuillez réessayer.']]);
            $this->redirect($fallbackUrl);
        }
    }

    public function flash(array $data): void { $_SESSION['flash'] = $data; }

    public function pullFlash(): ?array
    {
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    public function noCache(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }

    /* ===================== Accès / rôles ===================== */

    /** Utilisateur connecté (relu en base à chaque requête, donc le type est toujours à jour). */
    public function currentUser(): ?array
    {
        if (!$this->currentLoaded) {
            $this->currentLoaded = true;
            $id = (int) ($_SESSION['user_id'] ?? 0);
            if ($id > 0) {
                $this->current = $this->findById($id);
                if ($this->current === null) {
                    unset($_SESSION['user_id']);
                }
            }
        }
        return $this->current;
    }

    /** Réservé aux admins : sinon retour au Front Office. */
    public function requireAdmin(): array
    {
        $this->noCache();
        $user = $this->currentUser();
        if ($user === null) {
            $this->redirect(self::loginUrl());                 // pas connecté -> page de connexion
        }
        if ($user['type'] !== User::TYPE_ADMIN) {
            $this->redirect(self::frontUrl());                 // utilisateur normal -> Front Office
        }
        return $user;
    }

    /** Réservé aux utilisateurs connectés : sinon page de connexion. */
    public function requireLogin(): array
    {
        $this->noCache();
        $user = $this->currentUser();
        if ($user === null) {
            $this->redirect(self::loginUrl());
        }
        return $user;
    }

    /* ======================= Validation ======================= */

    private function collect(array $src): array
    {
        return [
            'first_name' => trim((string) ($src['first_name'] ?? '')),
            'last_name'  => trim((string) ($src['last_name'] ?? '')),
            'email'      => strtolower(trim((string) ($src['email'] ?? ''))),
            'age'        => trim((string) ($src['age'] ?? '')),
            'password'   => (string) ($src['password'] ?? ''),
        ];
    }

    private function validate(array $d, bool $passwordRequired): array
    {
        $errors = [];
        if ($d['first_name'] === '' || mb_strlen($d['first_name']) > 100) {
            $errors[] = 'Le prénom est obligatoire (100 caractères maximum).';
        }
        if ($d['last_name'] === '' || mb_strlen($d['last_name']) > 100) {
            $errors[] = 'Le nom est obligatoire (100 caractères maximum).';
        }
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 150) {
            $errors[] = 'Veuillez saisir une adresse e-mail valide.';
        }
        if (filter_var($d['age'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 120]]) === false) {
            $errors[] = 'L\'âge doit être un nombre entier entre 1 et 120.';
        }
        if ($passwordRequired && $d['password'] === '') {
            $errors[] = 'Le mot de passe est obligatoire.';
        }
        if (mb_strlen($d['password']) > 255) {
            $errors[] = 'Le mot de passe est trop long (255 caractères maximum).';
        }
        return $errors;
    }

    private function oldInput(array $d): array
    {
        unset($d['password']);
        return $d;
    }

    /* ============== Accès base de données (PDO) ============== */

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM `users` WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM `users` WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function emailTaken(string $email, ?int $exceptId = null): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM `users` WHERE email = :email AND id <> :id');
        $stmt->execute([':email' => $email, ':id' => $exceptId ?? 0]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getAllUsers(): array
    {
        return $this->db->query('SELECT * FROM `users` ORDER BY id ASC')->fetchAll();
    }

    private function insertUser(User $u): void
    {
        $stmt = $this->db->prepare('INSERT INTO `users` (first_name, last_name, email, age, password, type)
                                    VALUES (:fn, :ln, :em, :age, :pw, :type)');
        $stmt->execute([
            ':fn'   => $u->getFirstName(),
            ':ln'   => $u->getLastName(),
            ':em'   => $u->getEmail(),
            ':age'  => $u->getAge(),
            ':pw'   => $u->getPassword(),
            ':type' => $u->getType(),
        ]);
    }

    private function updateUser(User $u, bool $changePassword): void
    {
        $sql = 'UPDATE `users` SET first_name = :fn, last_name = :ln, email = :em, age = :age, type = :type'
             . ($changePassword ? ', password = :pw' : '') . ' WHERE id = :id';
        $params = [
            ':fn'   => $u->getFirstName(),
            ':ln'   => $u->getLastName(),
            ':em'   => $u->getEmail(),
            ':age'  => $u->getAge(),
            ':type' => $u->getType(),
            ':id'   => $u->getId(),
        ];
        if ($changePassword) {
            $params[':pw'] = $u->getPassword();
        }
        $this->db->prepare($sql)->execute($params);
    }

    private function deleteUser(int $id): void
    {
        $this->db->prepare('DELETE FROM `users` WHERE id = :id')->execute([':id' => $id]);
    }

    /* =================== Actions Front Office =================== */

    public function login(): void
    {
        $email    = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $errors   = [];

        // Anti force brute : après 5 échecs, connexion bloquée 60 secondes
        $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'until' => 0];
        if ($attempts['until'] > time()) {
            $wait = $attempts['until'] - time();
            $this->flash(['type' => 'error', 'tab' => 'login', 'messages' => ['Trop de tentatives échouées. Réessayez dans ' . $wait . ' seconde(s).'], 'old' => ['email' => $email]]);
            $this->redirect(self::loginUrl());
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Veuillez saisir une adresse e-mail valide.';
        }
        if ($password === '') {
            $errors[] = 'Le mot de passe est obligatoire.';
        }

        $row = null;
        if (!$errors) {
            $row = $this->findByEmail($email);
            if ($row === null || !hash_equals((string) $row['password'], $password)) {
                $errors[] = 'E-mail ou mot de passe incorrect.';
            }
        }

        if ($errors) {
            if (in_array('E-mail ou mot de passe incorrect.', $errors, true)) {   // seuls les vrais échecs d'identifiants comptent
                $attempts['count']++;
                if ($attempts['count'] >= 5) {
                    $attempts = ['count' => 0, 'until' => time() + 60];
                }
                $_SESSION['login_attempts'] = $attempts;
            }
            $this->flash(['type' => 'error', 'tab' => 'login', 'messages' => $errors, 'old' => ['email' => $email]]);
            $this->redirect(self::loginUrl());
        }

        unset($_SESSION['login_attempts']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $row['id'];

        // Admin -> Back Office ; utilisateur normal -> Front Office
        $this->redirect($row['type'] === User::TYPE_ADMIN ? self::backUrl() : self::frontUrl());
    }

    public function register(): void
    {
        $d = $this->collect($_POST);
        $errors = $this->validate($d, true);
        if (!$errors && $this->emailTaken($d['email'])) {
            $errors[] = 'Cette adresse e-mail est déjà utilisée.';
        }

        if ($errors) {
            $this->flash(['type' => 'error', 'tab' => 'register', 'messages' => $errors, 'old' => $this->oldInput($d)]);
            $this->redirect(self::loginUrl());
        }

        // Inscription publique : le type est forcé à 'normal' (le champ "type" du formulaire est ignoré).
        $user = new User($d['first_name'], $d['last_name'], $d['email'], (int) $d['age'], $d['password'], User::TYPE_NORMAL);
        $this->insertUser($user);

        $this->flash(['type' => 'success', 'tab' => 'login', 'messages' => ['Compte créé avec succès. Vous pouvez maintenant vous connecter.'], 'old' => ['email' => $d['email']]]);
        $this->redirect(self::loginUrl());
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        $this->redirect(self::loginUrl());
    }

    /* =================== Actions Back Office (admin) =================== */

    private function postedType(): string
    {
        $type = (string) ($_POST['type'] ?? User::TYPE_NORMAL);
        return in_array($type, [User::TYPE_NORMAL, User::TYPE_ADMIN], true) ? $type : User::TYPE_NORMAL;
    }

    public function adminCreate(): void
    {
        $this->requireAdmin();
        $d = $this->collect($_POST);
        $type = $this->postedType();
        $errors = $this->validate($d, true);
        if (!$errors && $this->emailTaken($d['email'])) {
            $errors[] = 'Cette adresse e-mail est déjà utilisée.';
        }

        if ($errors) {
            $this->flash(['type' => 'error', 'messages' => $errors, 'old' => $this->oldInput($d) + ['type' => $type]]);
            $this->redirect(self::backFormUrl());
        }

        $this->insertUser(new User($d['first_name'], $d['last_name'], $d['email'], (int) $d['age'], $d['password'], $type));
        $this->flash(['type' => 'success', 'messages' => ['Utilisateur ajouté avec succès.']]);
        $this->redirect(self::backUrl());
    }

    public function adminUpdate(): void
    {
        $admin  = $this->requireAdmin();
        $id     = (int) ($_POST['id'] ?? 0);
        $target = $this->findById($id);
        if ($target === null) {
            $this->flash(['type' => 'error', 'messages' => ['Utilisateur introuvable.']]);
            $this->redirect(self::backUrl());
        }

        $d = $this->collect($_POST);
        $type = $this->postedType();
        $errors = $this->validate($d, false);
        if (!$errors && $this->emailTaken($d['email'], $id)) {
            $errors[] = 'Cette adresse e-mail est déjà utilisée.';
        }
        // Un admin ne peut pas se retirer lui-même le rôle admin (il reste ainsi toujours au moins un admin)
        if ($id === (int) $admin['id'] && $type !== User::TYPE_ADMIN) {
            $errors[] = 'Vous ne pouvez pas retirer votre propre rôle d\'administrateur.';
        }

        if ($errors) {
            $this->flash(['type' => 'error', 'messages' => $errors, 'old' => $this->oldInput($d) + ['type' => $type]]);
            $this->redirect(self::backFormUrl($id));
        }

        $user = new User($d['first_name'], $d['last_name'], $d['email'], (int) $d['age'], $d['password'], $type);
        $user->setId($id);
        $this->updateUser($user, $d['password'] !== '');
        $this->flash(['type' => 'success', 'messages' => ['Utilisateur modifié avec succès.']]);
        $this->redirect(self::backUrl());
    }

    public function adminDelete(): void
    {
        $admin = $this->requireAdmin();
        $id    = (int) ($_POST['id'] ?? 0);

        if ($id === (int) $admin['id']) {
            $this->flash(['type' => 'error', 'messages' => ['Vous ne pouvez pas supprimer votre propre compte.']]);
        } elseif ($this->findById($id) === null) {
            $this->flash(['type' => 'error', 'messages' => ['Utilisateur introuvable.']]);
        } else {
            $this->deleteUser($id);
            $this->flash(['type' => 'success', 'messages' => ['Utilisateur supprimé.']]);
        }
        $this->redirect(self::backUrl());
    }

    /* ======================= Routage ======================= */

    public function handleRequest(): void
    {
        $action = (string) ($_GET['action'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(self::loginUrl());
        }

        $isAdminAction = str_starts_with($action, 'admin-');
        $this->checkCsrf($isAdminAction ? self::backUrl() : self::loginUrl());

        switch ($action) {
            case 'login':        $this->login(); break;
            case 'logout':       $this->logout(); break;
            case 'register':     $this->register(); break;
            case 'admin-create': $this->adminCreate(); break;
            case 'admin-update': $this->adminUpdate(); break;
            case 'admin-delete': $this->adminDelete(); break;
            default:             $this->redirect(self::loginUrl());
        }
    }
}

// Quand ce fichier est appelé directement par un formulaire (et non inclus par une vue), on traite la requête.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    (new UserController())->handleRequest();
}
