-- Template Projet Web — base de données (import manuel optionnel via phpMyAdmin)
-- NB : config.php crée déjà automatiquement cette base et cette table au premier chargement.

CREATE DATABASE IF NOT EXISTS `gestion_utilisateurs` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gestion_utilisateurs`;

CREATE TABLE IF NOT EXISTS `users` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Compte administrateur par défaut : admin@gmail.com / admin  (à changer via le Back Office)
-- Les mots de passe sont stockés en clair (tels que saisis) et comparés tels quels à la connexion.
-- Si l'e-mail existe déjà, le compte est remis à jour (mot de passe = admin, type = admin).
INSERT INTO `users` (first_name, last_name, email, age, password, type)
VALUES ('Admin', 'MEDLINK', 'admin@gmail.com', 30, 'admin', 'admin')
ON DUPLICATE KEY UPDATE password = VALUES(password), type = 'admin';
