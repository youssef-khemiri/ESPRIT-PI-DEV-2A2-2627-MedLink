<?php
/* Gabarit commun du Back Office — inclus par index.php et user-form.php.
   Variables attendues : $admin (utilisateur connecté), $pageTitle, $pageSubtitle */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MEDLINK — Back Office</title>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/views/assets/style.css">
</head>
<body class="mvc-page">
<header class="sticky-navbar">
  <div class="container nav-flex">
    <nav class="main-nav mvc-admin-nav">
      <a class="active" href="<?= UserController::backUrl() ?>"><i class="fas fa-heartbeat"></i> MEDLINK Admin</a>
      <a href="<?= UserController::backUrl() ?>">Utilisateurs</a>
      <a href="<?= UserController::frontUrl() ?>">Voir le site</a>
    </nav>
    <div class="user-chip">
      <span class="hello"><i class="fas fa-user-shield"></i> <strong><?= $h($admin['first_name'] . ' ' . $admin['last_name']) ?></strong></span>
      <form method="post" action="<?= UserController::actionUrl('logout') ?>">
        <input type="hidden" name="csrf" value="<?= $h($csrf) ?>">
        <button class="btn btn-coral" type="submit"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
      </form>
    </div>
  </div>
</header>
<section class="hero-section mvc-admin-hero">
  <div class="hero-overlay"></div>
  <div class="container hero-container">
    <div class="top-info-bar">
      <div class="logo-area"><a href="<?= UserController::backUrl() ?>" class="brand-name" aria-label="MEDLINK"><img src="<?= BASE_URL ?>/views/assets/medlink-logo.png" alt="MEDLINK — Global Healthcare Network"></a></div>
      <div class="contact-widgets">
        <div class="widget"><div class="widget-icon"><i class="fas fa-sliders-h"></i></div><div class="widget-text"><strong>ESPACE ADMINISTRATION</strong><span>Gestion des utilisateurs</span></div></div>
      </div>
    </div>
    <div class="page-hero-content">
      <span class="subtitle">BACK OFFICE</span>
      <h1><?= $h($pageTitle) ?></h1>
      <div class="teal-divider center-divider"></div>
      <div class="breadcrumb"><span>Administration</span> &nbsp;/&nbsp; <span><?= $h($pageSubtitle) ?></span></div>
    </div>
  </div>
</section>
<main class="mvc-main">
