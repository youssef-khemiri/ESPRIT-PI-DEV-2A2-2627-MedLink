<?php
require_once __DIR__ . '/../../controllers/UserController.php';

$controller = new UserController();
$controller->noCache();

// Déjà connecté : redirection selon le type (admin -> Back Office, normal -> Front Office)
$user = $controller->currentUser();
if ($user) {
    header('Location: ' . ($user['type'] === 'admin' ? UserController::backUrl() : UserController::frontUrl()));
    exit;
}

$flash = $controller->pullFlash();
$csrf  = $controller->csrfToken();
$e     = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$activeTab = ($flash['tab'] ?? 'login') === 'register' ? 'register' : 'login';
$old   = $flash['old'] ?? [];
$o     = fn(string $k, string $tab) => $e($activeTab === $tab ? ($old[$k] ?? '') : '');
$isSuccess = ($flash['type'] ?? '') === 'success';
$messages  = $flash['messages'] ?? [];
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>MEDLINK — Connexion</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/views/assets/auth.css">
</head>
<body>
<header class="top">
  <a class="brand" href="<?= UserController::loginUrl() ?>">MEDLINK</a>
  <nav></nav>
</header>
<main class="container">
<?php if ($isSuccess && $messages): ?><div class="flash flash-success"><?= $e($messages[0]) ?></div><?php endif; ?>

<section class="auth-shell">
  <div class="auth-copy">
    <p class="eyebrow">ESPACE SÉCURISÉ</p>
    <h1>Gérez votre compte en toute sécurité.</h1>
    <p>Créez votre compte ou connectez-vous pour accéder à votre espace personnel.</p>
    <div class="auth-points"><span>✓ Mots de passe chiffrés</span><span>✓ Espace adapté à votre rôle</span><span>✓ Session protégée</span></div>
  </div>
  <div class="auth-card">
    <div class="auth-tabs">
      <button type="button" class="auth-tab <?= $activeTab === 'login' ? 'selected' : '' ?>" data-tab="login">Connexion</button>
      <button type="button" class="auth-tab <?= $activeTab === 'register' ? 'selected' : '' ?>" data-tab="register">Créer un compte</button>
    </div>
    <?php if (!$isSuccess && $messages): ?>
      <div class="alert-error"><?php if (count($messages) === 1): ?><?= $e($messages[0]) ?><?php else: ?><ul><?php foreach ($messages as $m): ?><li><?= $e($m) ?></li><?php endforeach; ?></ul><?php endif; ?></div>
    <?php endif; ?>

    <form class="auth-form <?= $activeTab === 'login' ? 'visible' : '' ?>" id="login-form" method="post" action="<?= UserController::actionUrl('login') ?>" data-validate novalidate>
      <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
      <h2>Bon retour !</h2><p class="form-hint">Connectez-vous avec votre adresse e-mail et votre mot de passe.</p>
      <label>E-mail<input type="email" name="email" maxlength="150" autocomplete="email" data-rule="required email" value="<?= $o('email', 'login') ?>"><small class="field-error"></small></label>
      <label>Mot de passe<input type="password" name="password" autocomplete="current-password" data-rule="required password"><small class="field-error"></small></label>
      <button class="primary-button" type="submit">Se connecter <span>→</span></button>
    </form>

    <form class="auth-form <?= $activeTab === 'register' ? 'visible' : '' ?>" id="register-form" method="post" action="<?= UserController::actionUrl('register') ?>" data-validate novalidate>
      <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
      <h2>Créer votre compte</h2><p class="form-hint">Renseignez vos informations pour créer votre compte.</p>
      <div class="grid-2">
        <label>Prénom<input name="first_name" maxlength="100" autocomplete="given-name" data-rule="required" value="<?= $o('first_name', 'register') ?>"><small class="field-error"></small></label>
        <label>Nom<input name="last_name" maxlength="100" autocomplete="family-name" data-rule="required" value="<?= $o('last_name', 'register') ?>"><small class="field-error"></small></label>
      </div>
      <label>E-mail<input type="email" name="email" maxlength="150" autocomplete="email" data-rule="required email" value="<?= $o('email', 'register') ?>"><small class="field-error"></small></label>
      <label>Âge<input type="number" name="age" min="1" max="120" data-rule="required age" value="<?= $o('age', 'register') ?>"><small class="field-error"></small></label>
      <label>Mot de passe<input type="password" name="password" autocomplete="new-password" data-rule="required password"><small class="field-error"></small></label>
      <button class="primary-button" type="submit">Créer mon compte <span>→</span></button>
    </form>
  </div>
</section>
</main>
<footer class="footer">MEDLINK — Espace utilisateurs</footer>
<script src="<?= BASE_URL ?>/views/assets/app.js"></script>
<script>
// Onglets Connexion / Créer un compte
document.querySelectorAll('.auth-tab').forEach(function (button) {
  button.addEventListener('click', function () {
    document.querySelectorAll('.auth-tab').forEach(function (b) { b.classList.toggle('selected', b === button); });
    document.querySelectorAll('.auth-form').forEach(function (form) {
      form.classList.toggle('visible', form.id === button.getAttribute('data-tab') + '-form');
    });
  });
});
</script>
</body>
</html>
