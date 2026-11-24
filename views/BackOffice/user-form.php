<?php
require_once __DIR__ . '/../../controllers/UserController.php';

$controller = new UserController();
$admin = $controller->requireAdmin();
$flash = $controller->pullFlash();
$csrf  = $controller->csrfToken();

// Mode modification si ?id=... existe, sinon ajout
$id   = (int) ($_GET['id'] ?? 0);
$item = $id > 0 ? $controller->findById($id) : null;
if ($id > 0 && $item === null) {
    $controller->flash(['type' => 'error', 'messages' => ['Utilisateur introuvable.']]);
    header('Location: ' . UserController::backUrl());
    exit;
}
$old  = $flash['old'] ?? [];                       // valeurs saisies si erreur de validation
$val  = fn($k) => $old[$k] ?? ($item[$k] ?? '');
$type = $val('type') ?: 'normal';
$isSelf = $item && (int) $item['id'] === (int) $admin['id'];

$pageTitle = $item ? 'Modifier un utilisateur' : 'Ajouter un utilisateur';
$pageSubtitle = $pageTitle;
include __DIR__ . '/_header.php';
?>
<section class="mvc-content mvc-content-light">
  <div class="container">
    <div class="mvc-panel mvc-form-panel">
      <span class="med-card-meta">GESTION DES UTILISATEURS</span>
      <h2><?= $item ? 'Modifier un utilisateur' : 'Ajouter un utilisateur' ?></h2><div class="coral-line"></div>
      <p class="panel-intro">Le type « admin » donne accès au Back Office. Un utilisateur « normal » n'a accès qu'au Front Office.</p>

      <?php if ($flash && !empty($flash['messages'])): ?>
        <div class="mvc-alert mvc-alert-error"><ul><?php foreach ($flash['messages'] as $m): ?><li><?= $h($m) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>

      <form method="post" action="<?= UserController::actionUrl($item ? 'admin-update' : 'admin-create') ?>" class="med-form light-form mvc-form-light" data-validate novalidate>
        <input type="hidden" name="csrf" value="<?= $h($csrf) ?>">
        <?php if ($item): ?><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><?php endif; ?>

        <div class="form-row">
          <div class="form-group" style="margin-bottom:0"><label for="first_name">Prénom</label><input id="first_name" name="first_name" type="text" maxlength="100" data-rule="required" value="<?= $h($val('first_name')) ?>"><small class="field-error"></small></div>
          <div class="form-group" style="margin-bottom:0"><label for="last_name">Nom</label><input id="last_name" name="last_name" type="text" maxlength="100" data-rule="required" value="<?= $h($val('last_name')) ?>"><small class="field-error"></small></div>
        </div>
        <div class="form-row">
          <div class="form-group" style="margin-bottom:0"><label for="email">Adresse e-mail</label><input id="email" name="email" type="email" maxlength="150" data-rule="required email" value="<?= $h($val('email')) ?>"><small class="field-error"></small></div>
          <div class="form-group" style="margin-bottom:0"><label for="age">Âge</label><input id="age" name="age" type="number" min="1" max="120" data-rule="required age" value="<?= $h($val('age')) ?>"><small class="field-error"></small></div>
        </div>
        <div class="form-row">
          <div class="form-group" style="margin-bottom:0">
            <label for="password">Mot de passe<?= $item ? ' (laisser vide pour ne pas le changer)' : '' ?></label>
            <input id="password" name="password" type="password" autocomplete="new-password" data-rule="<?= $item ? 'optional password' : 'required password' ?>">
            <small class="field-error"></small>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label for="type">Type d'utilisateur</label>
            <select id="type" name="type" <?= $isSelf ? 'title="Vous ne pouvez pas modifier votre propre rôle"' : '' ?>>
              <option value="normal" <?= $type === 'normal' ? 'selected' : '' ?> <?= $isSelf ? 'disabled' : '' ?>>normal</option>
              <option value="admin" <?= $type === 'admin' ? 'selected' : '' ?>>admin</option>
            </select>
          </div>
        </div>

        <button class="btn btn-teal" type="submit">Enregistrer</button>
        <a class="btn btn-coral" href="<?= UserController::backUrl() ?>">Annuler</a>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/_footer.php'; ?>
