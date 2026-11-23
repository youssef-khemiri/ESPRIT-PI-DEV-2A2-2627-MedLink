<?php
require_once __DIR__ . '/../../controllers/UserController.php';

$controller = new UserController();
$admin = $controller->requireAdmin();   // non connecté ou utilisateur normal -> redirigé vers le Front Office
$users = $controller->getAllUsers();
$flash = $controller->pullFlash();
$csrf  = $controller->csrfToken();

$pageTitle = 'Gestion des utilisateurs';
$pageSubtitle = 'Utilisateurs';
include __DIR__ . '/_header.php';
?>
<section class="mvc-content mvc-content-light">
  <div class="container">
    <div class="mvc-section-heading"><h2>Liste des utilisateurs</h2><div class="coral-line center-divider"></div><p>Seuls les administrateurs peuvent ajouter, modifier ou supprimer des comptes et passer un utilisateur en administrateur.</p></div>

    <?php if ($flash && !empty($flash['messages'])): ?>
      <div class="mvc-alert <?= ($flash['type'] ?? '') === 'success' ? 'mvc-alert-success' : 'mvc-alert-error' ?>">
        <ul><?php foreach ($flash['messages'] as $m): ?><li><?= $h($m) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <div class="mvc-admin-actions">
      <a class="btn btn-teal" href="<?= UserController::backFormUrl() ?>"><i class="fas fa-plus"></i> Ajouter un utilisateur</a>
      <a class="btn btn-coral" href="<?= UserController::frontUrl() ?>">Voir le site</a>
    </div>

    <div class="mvc-panel" style="margin-top:24px">
      <?php if (!$users): ?>
        <div class="mvc-empty">Aucun utilisateur enregistré.</div>
      <?php else: ?>
      <div class="mvc-table-wrap">
        <table class="mvc-table">
          <thead><tr><th>ID</th><th>Nom</th><th>E-mail</th><th>Âge</th><th>Type</th><th>Inscrit le</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= (int) $u['id'] ?></td>
              <td><strong><?= $h($u['first_name'] . ' ' . $u['last_name']) ?></strong></td>
              <td><?= $h($u['email']) ?></td>
              <td><?= (int) $u['age'] ?></td>
              <td><span class="badge-type <?= $u['type'] === 'admin' ? 'badge-admin' : 'badge-normal' ?>"><?= $h($u['type']) ?></span></td>
              <td><?= $h(date('d/m/Y', strtotime($u['created_at']))) ?></td>
              <td>
                <div class="actions">
                  <a class="btn btn-teal" href="<?= UserController::backFormUrl((int) $u['id']) ?>">Modifier</a>
                  <?php if ((int) $u['id'] !== (int) $admin['id']): ?>
                  <form method="post" action="<?= UserController::actionUrl('admin-delete') ?>" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                    <input type="hidden" name="csrf" value="<?= $h($csrf) ?>">
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <button class="btn btn-coral" type="submit">Supprimer</button>
                  </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php include __DIR__ . '/_footer.php'; ?>
