# MEDLINK — Template Projet Web

Site web du **MEDLINK** : authentification, Front Office et Back Office
d'administration des utilisateurs, développé en **PHP** selon l'architecture **MVC, sans
framework**, dans le cadre du module *Projet Technologies Web 2A* (année universitaire
2026-2027).

## Description

### Objectif

Fournir une application web complète et sécurisée de gestion des utilisateurs d'une clinique,
en respectant la structure imposée par l'étude de cas : `config.php`, `models/`, `controllers/`
et `views/` (`assets`, `FrontOffice`, `BackOffice`).

### Problème résolu

Le projet centralise la gestion des comptes de la clinique : inscription des patients, connexion,
distinction des rôles (utilisateur normal / administrateur), et administration des comptes par
un Back Office réservé aux administrateurs. Il évite ainsi la gestion manuelle des accès et
applique les règles de sécurité de base (jeton CSRF, requêtes préparées, sessions durcies,
contrôle d'accès côté serveur).

### Fonctionnalités principales

- **Inscription et connexion** sur une page unique à deux onglets.
- **Gestion des rôles** : un utilisateur inscrit est toujours de type `normal` ; seul un
  administrateur peut promouvoir un compte en `admin`.
- **Back Office (CRUD)** : consulter, ajouter, modifier et supprimer des utilisateurs.
- **Validation des saisies** côté navigateur (JavaScript) et côté serveur (PHP).
- **Sécurité** : jeton CSRF sur tous les formulaires POST, requêtes SQL préparées (PDO),
  régénération de l'identifiant de session, blocage temporaire après 5 échecs de connexion,
  expiration de session par inactivité.
- **Interface responsive** : charte graphique MEDLINK (turquoise / corail) et thème sombre
  pour la page d'authentification.

## Table des matières

- [Installation](#installation)
- [Utilisation](#utilisation)
- [Scénarios de test](#scénarios-de-test)
- [Répartition des tâches](#répartition-des-tâches)
- [Contribution](#contribution)
- [Licence](#licence)

## Installation

1. Clonez le repository :

   ```bash
   git clone https://github.com/votre-utilisateur/Template-Projet-Web.git
   cd "Template Projet Web"
   ```

2. Si vous utilisez **WAMP** ou **XAMPP** :

   - Placez le projet dans le dossier `www` (WAMP) ou `htdocs` (XAMPP).
   - Démarrez **Apache** et **MySQL** depuis l'interface de WAMP / XAMPP.
   - Accédez au projet via `http://localhost/Template%20Projet%20Web/`.

3. Créez la base de données :

   - Au premier chargement, la base `gestion_utilisateurs` et la table `users` sont créées
     automatiquement.
   - Sinon, importez le fichier `database.sql` dans **phpMyAdmin**.

4. Vérifiez la configuration dans `config.php` :

   - `BASE_URL` est calculée automatiquement (le dossier contient des espaces).
   - `SESSION_TIMEOUT` définit le délai d'expiration par inactivité (1800 secondes par défaut).

5. Connectez-vous avec le compte administrateur par défaut :

   | Champ | Valeur |
   | --- | --- |
   | E-mail | `admin@gmail.com` |
   | Mot de passe | `admin` |

## Utilisation

### Accès selon le rôle

- **Visiteur non connecté** : toute page du Front Office ou du Back Office le redirige vers la
  page de connexion.
- **Utilisateur normal** : arrive sur le Front Office (`views/FrontOffice/index.php`) ; l'URL du
  Back Office le renvoie au Front Office.
- **Administrateur** : arrive sur le Back Office (`views/BackOffice/index.php`) et peut gérer
  les comptes.

Le bouton **Déconnexion** est un formulaire POST protégé par un jeton CSRF : ouvrir directement
l'adresse `...UserController.php?action=logout` ne déconnecte pas l'utilisateur.

### Validation des saisies

Les règles sont déclarées dans le HTML par l'attribut `data-rule` et appliquées par
`views/assets/app.js` :

| Règle | Contrôle effectué |
| --- | --- |
| `required` | Champ obligatoire. |
| `email` | Format d'adresse e-mail valide (expression régulière). |
| `age` | Entier compris entre 1 et 120. |
| `password` | Le mot de passe n'est pas « nettoyé » (les espaces sont conservés). |
| `optional` | Un champ vide est accepté (ex. mot de passe lors d'une modification). |

Fonctionnement du script :

- Le message d'erreur s'affiche sous le champ concerné (classes `has-error` / `field-error`).
- Le champ est contrôlé quand on le quitte (`blur`), puis à chaque frappe une fois en erreur.
- À l'envoi, tous les champs sont contrôlés ; en cas d'erreur, le formulaire n'est pas envoyé
  (`preventDefault`) et le premier champ fautif reçoit le focus.
- Cette validation est une **aide pour l'utilisateur** : la vraie protection est la validation
  PHP du contrôleur, qui refait tous les contrôles.

### Structure du projet

```text
Template Projet Web/
├── index.php                 # redirection vers le Front Office
├── config.php                # session sécurisée, BASE_URL, connexion PDO
├── database.sql              # base + table users + compte admin
├── models/
│   └── User.php              # classe User
├── controllers/
│   └── UserController.php    # authentification, CRUD, rôles, sécurité
└── views/
    ├── assets/
    │   ├── app.js            # validation JavaScript des formulaires
    │   ├── style.css         # charte graphique MEDLINK
    │   ├── auth.css          # style de la page de connexion / inscription
    │   └── medlink-logo.png
    ├── FrontOffice/
    │   ├── index.php         # page d'accueil
    │   └── login.php         # connexion / création de compte
    └── BackOffice/
        ├── _header.php
        ├── _footer.php
        ├── index.php         # liste des utilisateurs
        └── user-form.php     # formulaire d'ajout / modification
```

## Scénarios de test

| Scénario | Résultat attendu |
| --- | --- |
| Visiteur non connecté ouvre le Front Office ou le Back Office | Redirection vers la connexion. |
| Connexion avec un e-mail invalide ou un mot de passe vide | Message d'erreur, pas de connexion. |
| Connexion avec un mauvais mot de passe | « E-mail ou mot de passe incorrect ». |
| Inscription valide | Compte créé avec le type « normal », retour à l'onglet Connexion. |
| Inscription avec âge 0 ou 200, ou e-mail déjà utilisé | Refusée avec message. |
| Inscription en tentant d'envoyer `type=admin` | Le compte est quand même « normal ». |
| Utilisateur normal connecté | Arrive au Front Office ; l'URL du Back Office le renvoie au Front Office. |
| Administrateur connecté | Arrive au Back Office ; peut ajouter, modifier, supprimer. |
| Administrateur change le type d'un utilisateur en « admin » | Pris en compte immédiatement. |
| Administrateur essaie de se supprimer ou de se retirer le rôle admin | Refusé. |
| Formulaire sans jeton CSRF | Refusé. |
| Déconnexion puis retour arrière | La page protégée n'est plus accessible. |
| Ouvrir directement `...UserController.php?action=logout` | Ne déconnecte pas (POST + CSRF exigés). |
| 5 mots de passe faux de suite | « Trop de tentatives échouées », connexion bloquée 60 s, même avec le bon mot de passe. |
| Inactivité (`SESSION_TIMEOUT` à 10, attendre 11 s) | Retour à la connexion avec « Votre session a expiré ». |
| Cookie de session dans les outils développeur | `MEDLINKSESSID` avec `HttpOnly` et `SameSite=Lax`. |

## Répartition des tâches

| Étudiant | Module | Branche | Fichiers principaux |
| --- | --- | --- | --- |
| Yasmine Ben Miled | 1/6 — Pages de connexion et de création de compte | `feature/login-register-pages` | `views/FrontOffice/login.php`, `views/assets/auth.css` |
| Tasnim Debbiche | 2/6 — Front Office | `feature/front-office` | `views/FrontOffice/index.php`, `views/assets/style.css` |
| Mariem Chiboub | 3/6 — Back Office | `feature/back-office` | `views/BackOffice/*` |
| Mariem Marzouk | 4/6 — Base de données et modèle | `feature/database-model` | `config.php`, `database.sql`, `models/User.php` |
| Khadija Arfa | 5/6 — Contrôleur, CRUD et sécurité | `feature/controller-crud-security` | `controllers/UserController.php` |
| Youssef Khmiri | 6/6 — Validation JavaScript et intégration finale | `feature/input-validation` | `views/assets/app.js`, `index.php`, `README.md` |

Ordre d'intégration conseillé, une fusion / pull request par étape :

1. Mariem Marzouk — `config.php`, `database.sql`, `models/User.php`
2. Khadija Arfa — `controllers/UserController.php`
3. Tasnim Debbiche — Front Office, `style.css`, logo
4. Youssef Khmiri — `app.js` (validation)
5. Yasmine Ben Miled — connexion / inscription, `auth.css`
6. Mariem Chiboub — Back Office
7. Youssef Khmiri — `index.php`, vérification complète, tests, README.md, puis push de la
   version finale

## Contribution

Nous remercions tous ceux qui ont contribué à ce projet !

### Contributeurs

Les personnes suivantes ont contribué à ce projet en ajoutant des fonctionnalités, en corrigeant
des bugs ou en améliorant la documentation :

- **Yasmine Ben Miled** — pages de connexion / inscription et `auth.css`.
- **Tasnim Debbiche** — Front Office et charte graphique.
- **Mariem Chiboub** — Back Office (liste et formulaire utilisateur).
- **Mariem Marzouk** — configuration, base de données et modèle `User`.
- **Khadija Arfa** — contrôleur, CRUD, rôles et sécurité.
- **Youssef Khmiri** — validation JavaScript des formulaires et intégration finale.

> Les liens vers les profils GitHub de chaque contributeur sont à compléter avant le push final.

### Comment contribuer ?

1. **Forkez le projet** : sur la page GitHub du projet, cliquez sur le bouton **Fork** pour
   créer une copie du dépôt dans votre propre compte.
2. **Clonez votre fork** sur votre machine locale :

   ```bash
   git clone https://github.com/votre-utilisateur/Template-Projet-Web.git
   cd "Template Projet Web"
   ```

3. **Créez une branche** en respectant la nomenclature demandée par le professeur :

   ```bash
   git checkout -b feature/ma-fonctionnalite
   ```

4. **Apportez vos modifications**, puis ajoutez et validez vos changements :

   ```bash
   git add .
   git commit -m "Description courte de la modification"
   ```

5. **Poussez la branche** et ouvrez une **pull request** vers la branche principale :

   ```bash
   git push origin feature/ma-fonctionnalite
   ```

Chaque commit doit être effectué par l'étudiant lui-même, avec son compte GitHub, afin que sa
contribution apparaisse dans l'historique du repository. Les tâches sont suivies dans le
**GitHub Project Board** du projet.

## Licence

Ce projet est sous la licence **MIT**. Pour plus de détails, consultez le fichier
[LICENSE](./LICENSE).

### Détails sur la licence MIT

La licence MIT est une licence de logiciel libre, courte et permissive : elle autorise
l'utilisation, la modification et la redistribution du code, y compris à des fins commerciales,
à condition de conserver l'avis de copyright et le texte de la licence. Le logiciel est fourni
« en l'état », sans garantie d'aucune sorte.
