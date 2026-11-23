# Module 6/6 — Validation des saisies (JavaScript) + intégration finale

Module de **Youssef Khmiri** pour le projet **Template Projet Web** (clinique MEDLINK) :
contrôle et validation des saisies côté navigateur en **JavaScript**, puis **intégration
finale** de tous les modules sur la branche principale.

## Description

### Objectif

Fournir un script unique de validation (`views/assets/app.js`) réutilisable par **tous** les
formulaires du site, sans framework et sans dépendance externe, puis vérifier l'ensemble du
projet une fois les autres modules fusionnés.

### Problème résolu

Sans ce module, chaque formulaire devrait réimplémenter ses contrôles, et l'utilisateur
découvrirait ses erreurs seulement après l'envoi au serveur. `app.js` centralise les règles
déclarées dans le HTML (`data-rule`), affiche les messages au bon endroit et bloque l'envoi
d'un formulaire invalide — ce qui évite des allers-retours inutiles vers le serveur.

### Fonctionnalités principales

- Règles déclaratives dans le HTML : `required`, `email`, `age`, `password`, `optional`.
- Message d'erreur affiché **sous le champ concerné** (classes `has-error` / `field-error`).
- Contrôle au `blur`, puis à chaque frappe une fois le champ en erreur.
- Blocage de l'envoi (`preventDefault`) et focus sur le premier champ fautif.
- Aucune donnée n'est « nettoyée » à l'insu de l'utilisateur : le mot de passe conserve ses
  espaces.
- Intégration finale : ordre de fusion, tests de bout en bout, `index.php` de redirection et
  README du repository.

## Table des matières

- [Fichiers du module](#fichiers-du-module)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Intégration finale](#intégration-finale)
- [Scénarios de test](#scénarios-de-test)
- [Contribution](#contribution)
- [Licence](#licence)

## Fichiers du module

| Fichier | Rôle |
| --- | --- |
| `views/assets/app.js` | Script de validation des formulaires. |
| `index.php` (racine) | Redirige `http://localhost/Template%20Projet%20Web/` vers `views/FrontOffice/index.php`. |

## Installation

Ce module s'installe **dans** le projet : il ne fonctionne pas seul.

1. Clonez le repository du projet :

   ```bash
   git clone https://github.com/votre-utilisateur/Template-Projet-Web.git
   cd "Template Projet Web"
   git checkout feature/input-validation
   ```

2. Vérifiez la présence des deux fichiers :

   - `views/assets/app.js`
   - `index.php` à la racine du projet

3. Placez le dossier du projet dans `htdocs` (XAMPP) ou `www` (WAMP), puis démarrez
   **Apache** et **MySQL**. La base `gestion_utilisateurs` et la table `users` se créent au
   premier chargement (sinon, importez `database.sql` dans phpMyAdmin).

4. Aucune configuration n'est nécessaire : `app.js` est chargé automatiquement par les pages
   qui l'utilisent (`views/FrontOffice/login.php`, `views/BackOffice/_footer.php`).

## Utilisation

### Déclarer un champ validé

Ajoutez l'attribut `data-rule` sur le champ et un emplacement pour le message dans son groupe :

```html
<div class="form-group">
  <label for="email">E-mail</label>
  <input type="email" id="email" name="email" data-rule="required email">
  <div class="field-error"></div>
</div>
```

Le formulaire doit porter l'attribut `data-validate` :

```html
<form method="post" action="controllers/UserController.php?action=login" data-validate>
```

### Règles disponibles

| Règle | Contrôle effectué |
| --- | --- |
| `required` | Champ obligatoire. |
| `email` | Format d'adresse e-mail valide (expression régulière). |
| `age` | Entier compris entre 1 et 120. |
| `password` | Le mot de passe n'est pas « nettoyé » (espaces conservés). |
| `optional` | Un champ vide est accepté (ex. mot de passe lors d'une modification). |

### Comportement

- Le champ est contrôlé quand on le quitte (`blur`), puis à chaque frappe une fois en erreur.
- À l'envoi, tous les champs sont contrôlés ; s'il reste une erreur, le formulaire n'est pas
  envoyé et le premier champ fautif reçoit le focus.
- Les règles sont lues dans l'ordre : `optional` (champ vide accepté), puis `required`, puis
  `email` / `age`.
- Cette validation est une **aide pour l'utilisateur** : la vraie protection est la validation
  PHP du contrôleur, qui refait tous les contrôles.

### Pages consommatrices

- Page de connexion / inscription (`views/FrontOffice/login.php`) — module 1.
- Formulaire du Back Office (`views/BackOffice/user-form.php`) — module 3.

## Intégration finale

Une fusion / pull request par étape, dans cet ordre :

1. **Mariem Marzouk** (4/6) — `config.php`, `database.sql`, `models/User.php`
2. **Khadija Arfa** (5/6) — `controllers/UserController.php`
3. **Tasnim Debbiche** (2/6) — Front Office, `style.css`, logo
4. **Youssef Khmiri** (6/6) — `app.js` (validation)
5. **Yasmine Ben Miled** (1/6) — connexion / inscription, `auth.css`
6. **Mariem Chiboub** (3/6) — Back Office
7. **Youssef Khmiri** (6/6) — `index.php`, vérification complète, tests, README.md, puis push de
   la version finale

La structure respecte l'étude de cas : `config.php`, `models/`, `controllers/`, `views/`
(`assets`, `FrontOffice`, `BackOffice`).

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

## Contribution

### Contributeur

- **Youssef Khmiri** — validation JavaScript des formulaires, `index.php`, vérification
  complète, tests et README du repository.

### Comment contribuer ?

Les commits doivent être faits par l'étudiant lui-même, avec son compte GitHub, afin que sa
contribution soit visible dans l'historique du repository.

1. **Forkez le projet**, puis clonez votre fork :

   ```bash
   git clone https://github.com/votre-utilisateur/Template-Projet-Web.git
   cd "Template Projet Web"
   ```

2. **Créez une branche** en respectant la nomenclature du professeur :

   ```bash
   git checkout -b feature/input-validation
   ```

3. **Modifiez les fichiers**, puis validez et poussez :

   ```bash
   git add .
   git commit -m "Validation JavaScript des formulaires"
   git push origin feature/input-validation
   ```

4. Ouvrez une **pull request** vers la branche principale.

### Suivi Git

- **Branche du module :** `feature/input-validation`
- **Exemples de messages de commit :** « Validation JavaScript des formulaires », « Fusion
  finale, tests et README.md du repository »
- **Tâche du GitHub Project Board :** « Validation des saisies, intégration finale et README »

## Licence

Ce projet est sous la licence **MIT**. Pour plus de détails, consultez le fichier
[LICENSE](./LICENSE).

### Détails sur la licence MIT

La licence MIT est une licence de logiciel libre, courte et permissive : elle autorise
l'utilisation, la modification et la redistribution du code, y compris à des fins commerciales,
à condition de conserver l'avis de copyright et le texte de la licence. Le logiciel est fourni
« en l'état », sans garantie d'aucune sorte.

---

### Annexe — Points à maîtriser pour la validation

- Rôle de la validation client (JavaScript) par rapport à la validation serveur (PHP).
- Fonctionnement de `data-rule` et des messages d'erreur.
- Ordre de fusion des branches et résolution d'un éventuel conflit Git.
