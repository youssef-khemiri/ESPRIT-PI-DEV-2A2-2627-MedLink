<?php
require_once __DIR__ . '/../../controllers/UserController.php';

$controller = new UserController();
$user = $controller->requireLogin();   // non connecté -> redirigé vers login.php
$csrf = $controller->csrfToken();
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MEDLINK — Clinique médicale</title>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/views/assets/style.css">
</head>
<body class="mvc-page">

<header class="sticky-navbar">
  <div class="container nav-flex">
    <nav class="main-nav">
      <a class="active" href="#accueil">Accueil</a>
      <a href="#services">Services</a>
      <a href="#apropos">À propos</a>
      <a href="#horaires">Horaires</a>
    </nav>
    <div class="user-chip">
      <span class="hello">Bonjour, <strong><?= $h($user['first_name']) ?></strong></span>
      <?php if ($user['type'] === 'admin'): ?>
        <a class="btn btn-teal" href="<?= UserController::backUrl() ?>"><i class="fas fa-cog"></i> Back Office</a>
      <?php endif; ?>
      <form method="post" action="<?= UserController::actionUrl('logout') ?>">
        <input type="hidden" name="csrf" value="<?= $h($csrf) ?>">
        <button class="btn btn-coral" type="submit"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
      </form>
    </div>
  </div>
</header>

<section id="accueil" class="hero-section">
  <div class="hero-overlay"></div>
  <div class="container hero-container">
    <div class="top-info-bar">
      <div class="logo-area"><a href="<?= UserController::frontUrl() ?>" class="brand-name" aria-label="MEDLINK"><img src="<?= BASE_URL ?>/views/assets/medlink-logo.png" alt="MEDLINK — Global Healthcare Network"></a></div>
      <div class="contact-widgets">
        <div class="widget"><div class="widget-icon"><i class="far fa-clock"></i></div><div class="widget-text"><strong>OUVERT 7J/7</strong><span>Lun - Sam : 08h00 - 20h00</span></div></div>
        <div class="widget"><div class="widget-icon"><i class="fas fa-phone-alt"></i></div><div class="widget-text"><strong>URGENCES</strong><span>+216 00 000 000</span></div></div>
      </div>
    </div>
    <div class="page-hero-content">
      <span class="subtitle">VOTRE SANTÉ, NOTRE PRIORITÉ</span>
      <h1>Une clinique à l'écoute de vos besoins</h1>
      <div class="teal-divider center-divider"></div>
      <p>Des équipes médicales qualifiées, des équipements modernes et un accompagnement personnalisé à chaque étape de votre parcours de soins.</p>
      <a href="#services" class="btn btn-coral">Découvrir nos services</a>
    </div>
  </div>
</section>

<main class="mvc-main">

  <section id="services" class="mvc-content">
    <div class="container">
      <div class="mvc-section-heading">
        <h2>Nos services</h2><div class="coral-line center-divider"></div>
        <p>Une offre de soins complète, regroupée au même endroit pour simplifier votre quotidien.</p>
      </div>
      <div class="grid-3">
        <article class="mvc-info-card"><div class="icon"><i class="fas fa-stethoscope"></i></div><h3>Médecine générale</h3><p>Consultations, suivi médical régulier et orientation vers les spécialistes.</p></article>
        <article class="mvc-info-card"><div class="icon"><i class="fas fa-heartbeat"></i></div><h3>Cardiologie</h3><p>Dépistage, bilans et suivi des pathologies cardiovasculaires.</p></article>
        <article class="mvc-info-card"><div class="icon"><i class="fas fa-apple-alt"></i></div><h3>Nutrition &amp; diététique</h3><p>Conseils alimentaires adaptés à votre état de santé et à vos objectifs.</p></article>
        <article class="mvc-info-card"><div class="icon"><i class="fas fa-baby"></i></div><h3>Pédiatrie</h3><p>Un suivi attentif de la santé des enfants, de la naissance à l'adolescence.</p></article>
        <article class="mvc-info-card"><div class="icon"><i class="fas fa-x-ray"></i></div><h3>Imagerie médicale</h3><p>Radiologie et échographie avec des équipements de dernière génération.</p></article>
        <article class="mvc-info-card"><div class="icon"><i class="fas fa-flask"></i></div><h3>Laboratoire d'analyses</h3><p>Analyses rapides et fiables, résultats disponibles dans les meilleurs délais.</p></article>
      </div>
    </div>
  </section>

  <section id="apropos" class="mvc-content mvc-content-light">
    <div class="container">
      <div class="split-2">
        <div>
          <span class="med-card-meta">À PROPOS</span>
          <h2 style="font-size:32px">Des soins de qualité, une approche humaine</h2>
          <div class="coral-line"></div>
          <p style="color:var(--text-muted);font-size:14px;margin-bottom:14px">MEDLINK réunit des professionnels de santé engagés autour d'une même mission : vous offrir des soins sûrs, accessibles et personnalisés.</p>
          <ul class="about-points">
            <li><i class="fas fa-check-circle"></i>Équipe médicale pluridisciplinaire</li>
            <li><i class="fas fa-check-circle"></i>Équipements modernes et entretenus</li>
            <li><i class="fas fa-check-circle"></i>Dossier patient confidentiel et sécurisé</li>
            <li><i class="fas fa-check-circle"></i>Accueil et accompagnement personnalisés</li>
          </ul>
        </div>
        <div class="teal-panel">
          <h2>Pourquoi nous choisir ?</h2><div class="coral-line"></div>
          <p>Plus de 15 spécialités réunies, un suivi continu et une équipe disponible pour répondre à vos questions.</p>
          <ul class="info-list">
            <li><span>Médecins spécialistes</span><strong>40+</strong></li>
            <li><span>Patients accompagnés</span><strong>10 000+</strong></li>
            <li><span>Années d'expérience</span><strong>15</strong></li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section id="horaires" class="mvc-content">
    <div class="container">
      <div class="split-2">
        <div class="coral-panel">
          <h2>Horaires d'ouverture</h2><div class="white-line"></div>
          <ul class="info-list hours-list">
            <li><span>Lundi - Vendredi</span><strong>08h00 - 20h00</strong></li>
            <li><span>Samedi</span><strong>08h00 - 14h00</strong></li>
            <li><span>Dimanche</span><strong>Urgences uniquement</strong></li>
          </ul>
        </div>
        <div class="mvc-panel">
          <h3>Nous contacter</h3><div class="coral-line"></div>
          <ul class="about-points">
            <li><i class="fas fa-map-marker-alt"></i>Adresse de la clinique, Tunis</li>
            <li><i class="fas fa-phone-alt"></i>+216 00 000 000</li>
            <li><i class="fas fa-envelope"></i>contact@medlink.tn</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

</main>

<footer class="main-footer">
  <div class="container footer-grid">
    <div class="footer-col"><h3>MEDLINK</h3><div class="coral-line"></div><p>Votre santé, notre priorité. Une clinique moderne, à l'écoute de chaque patient.</p></div>
    <div class="footer-col"><h3>Explorer</h3><div class="coral-line"></div><ul class="footer-links"><li><a href="#services">Services</a></li><li><a href="#apropos">À propos</a></li><li><a href="#horaires">Horaires</a></li></ul></div>
    <div class="footer-col"><h3>Information importante</h3><div class="coral-line"></div><p>Les informations de ce site ne remplacent pas une consultation ou un avis médical personnalisé.</p></div>
  </div>
  <div class="mvc-footer-note">MEDLINK — Clinique médicale</div>
</footer>

<script src="<?= BASE_URL ?>/views/assets/app.js"></script>
</body>
</html>
