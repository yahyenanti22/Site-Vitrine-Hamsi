-- =====================================================================
--  HAMSI — Site vitrine + espace administrateur
--  Base de données MySQL / MariaDB (compatible WAMP / XAMPP / phpMyAdmin)
--  Encodage : utf8mb4
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `hamsi_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `hamsi_db`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `newsletter_subscribers`;
DROP TABLE IF EXISTS `contacts`;
DROP TABLE IF EXISTS `site_settings`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `administrators`;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
--  Administrateurs
-- ---------------------------------------------------------------------
CREATE TABLE `administrators` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(100) NOT NULL,
  `username`      VARCHAR(50)  NOT NULL,
  `email`         VARCHAR(190) NOT NULL,
  `password`      VARCHAR(255) NOT NULL COMMENT 'Hash password_hash()',
  `role`          ENUM('super_admin','admin') NOT NULL DEFAULT 'admin',
  `last_login_at` DATETIME NULL DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_email` (`email`),
  UNIQUE KEY `uq_admin_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Catégories de produits
-- ---------------------------------------------------------------------
CREATE TABLE `categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL COMMENT 'Nom affiché dans les filtres (ex : Purées)',
  `slug`        VARCHAR(120) NOT NULL,
  `label`       VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Libellé affiché sur les cartes (ex : Purée bio)',
  `description` TEXT NULL,
  `sort_order`  INT NOT NULL DEFAULT 0,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_category_slug` (`slug`),
  KEY `idx_category_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Produits
-- ---------------------------------------------------------------------
CREATE TABLE `products` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`       INT UNSIGNED NULL DEFAULT NULL,
  `name`              VARCHAR(150) NOT NULL,
  `slug`              VARCHAR(170) NOT NULL,
  `short_description` VARCHAR(255) NOT NULL DEFAULT '',
  `description`       TEXT NULL,
  `age_label`         VARCHAR(50)  NOT NULL DEFAULT '' COMMENT 'Ex : Dès 6 mois',
  `format`            VARCHAR(50)  NOT NULL DEFAULT '' COMMENT 'Ex : 200g, 4 x 100g',
  `price`             DECIMAL(10,2) NULL DEFAULT NULL,
  `image`             VARCHAR(255) NULL DEFAULT NULL COMMENT 'Chemin relatif : uploads/products/...',
  `ingredients`       TEXT NULL COMMENT 'Un ingrédient par ligne : Nom | Détail',
  `nutrition_info`    TEXT NULL,
  `format_info`       TEXT NULL,
  `tasting_tips`      TEXT NULL,
  `is_featured`       TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Affiché dans « Produits phares »',
  `status`            ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
  `sort_order`        INT NOT NULL DEFAULT 0,
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_slug` (`slug`),
  KEY `idx_product_category` (`category_id`),
  KEY `idx_product_status_order` (`status`, `sort_order`),
  KEY `idx_product_featured` (`is_featured`, `status`),
  CONSTRAINT `fk_products_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Paramètres du site (clé / valeur)
-- ---------------------------------------------------------------------
CREATE TABLE `site_settings` (
  `setting_key`   VARCHAR(60) NOT NULL,
  `setting_value` TEXT NULL,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Messages du formulaire de contact
-- ---------------------------------------------------------------------
CREATE TABLE `contacts` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(190) NOT NULL,
  `phone`      VARCHAR(30)  NULL DEFAULT NULL,
  `subject`    VARCHAR(150) NOT NULL DEFAULT '',
  `message`    TEXT NOT NULL,
  `status`     ENUM('nouveau','lu','traite') NOT NULL DEFAULT 'nouveau',
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_status` (`status`),
  KEY `idx_contact_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Abonnés à la newsletter (formulaire du pied de page)
-- ---------------------------------------------------------------------
CREATE TABLE `newsletter_subscribers` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(190) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_newsletter_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  Tentatives de connexion (protection anti-force brute)
-- ---------------------------------------------------------------------
CREATE TABLE `login_attempts` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address`   VARCHAR(45)  NOT NULL,
  `identifier`   VARCHAR(190) NOT NULL DEFAULT '',
  `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attempt_ip_time` (`ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  DONNÉES INITIALES
-- =====================================================================

-- Compte administrateur de test
--   Identifiant : admin  (ou admin@hamsi.com)
--   Mot de passe : Hamsi@2026   (stocké uniquement sous forme de hash)
INSERT INTO `administrators` (`name`, `username`, `email`, `password`, `role`) VALUES
('Administrateur Hamsi', 'admin', 'admin@hamsi.com',
 '$2y$10$GDUSQEvBaaHj.HcFyZnRn.aOrxG7h8m/UEl4JVKZfvY0YyM0RS69.', 'super_admin');

-- Catégories (issues de la charte : purées de légumes, compotes de fruits, céréales infantiles)
INSERT INTO `categories` (`id`, `name`, `slug`, `label`, `description`, `sort_order`) VALUES
(1, 'Purées',   'purees',   'Purée bio',           'Purées de légumes issues de la biodiversité africaine.', 1),
(2, 'Compotes', 'compotes', 'Compote bio',         'Compotes de fruits issues de la biodiversité africaine.', 2),
(3, 'Céréales', 'cereales', 'Céréales infantiles', 'Céréales infantiles issues de la biodiversité africaine.', 3);

-- Produits
INSERT INTO `products`
(`category_id`, `name`, `slug`, `short_description`, `description`, `age_label`, `format`, `price`, `image`, `ingredients`, `nutrition_info`, `format_info`, `tasting_tips`, `is_featured`, `status`, `sort_order`) VALUES
(1, 'Purée Douceur Patate Douce & Carotte', 'puree-douceur-patate-douce-carotte',
 'Onctueuse et naturellement sucrée, riche en bêta-carotène pour la vue et le système immunitaire de bébé.',
 'Une recette veloutée et naturellement sucrée, spécialement pensée pour éveiller les papilles de bébé en douceur avec des légumes récoltés à pleine maturité.',
 'Dès 6 mois', '200g', 3.50, 'uploads/products/puree-patate-douce-carotte.jpg',
 'Patate douce bio | Récolte locale - 60%\nCarottes tendres | Récolte locale - 38%\nEau de source | Pure & légère - 2%',
 '', '', 'Servir à température ambiante ou légèrement tiédi au bain-marie. Remuer avant de servir.', 1, 'actif', 1),

(2, 'Pomme Banane Onctueuse', 'pomme-banane-onctueuse',
 '100% fruits récoltés à maturité, sans sucres ajoutés pour un goûter sain.',
 '100% fruits récoltés à maturité, sans sucres ajoutés pour un goûter sain.',
 'Dès 4 mois', '4 x 100g', 5.20, 'uploads/products/pomme-banane.jpg',
 'Pommes bio\nBananes bio', '', '', 'Idéale au goûter, à servir fraîche ou à température ambiante.', 0, 'actif', 2),

(3, 'Premier Bol Riz & Vanille', 'premier-bol-riz-vanille',
 'Des céréales faciles à digérer pour accompagner les nuits et les petits déjeuners.',
 'Des céréales faciles à digérer pour accompagner les nuits et les petits déjeuners.',
 'Dès 8 mois', '250g', 6.80, 'uploads/products/riz-vanille.jpg',
 'Farine de riz bio\nExtrait de vanille\nVitamine B1', '', '', 'Délayer dans le lait habituel de bébé en suivant les indications de l\'emballage.', 0, 'actif', 3),

(1, 'Jardin Vert Courgette & Petits Pois', 'jardin-vert-courgette-petits-pois',
 'Une texture veloutée aux notes fraîches et printanières pour le déjeuner.',
 'Une texture veloutée aux notes fraîches et printanières pour le déjeuner.',
 'Dès 6 mois', '200g', 4.80, 'uploads/products/jardin-vert.jpg',
 'Courgettes bio\nPetits pois bio', '', '', 'Servir à température ambiante ou légèrement tiédi au bain-marie. Remuer avant de servir.', 0, 'actif', 4),

(2, 'Évasion Poire & Mangue', 'evasion-poire-mangue',
 'Une touche exotique et vitaminée pour éveiller la curiosité des bébés gourmands.',
 'Une touche exotique et vitaminée pour éveiller la curiosité des bébés gourmands.',
 'Dès 6 mois', '4 x 100g', 5.50, 'uploads/products/poire-mangue.jpg',
 'Poires bio\nMangues bio', '', '', 'Idéale au goûter, à servir fraîche ou à température ambiante.', 0, 'actif', 5),

(3, 'Multicéréales & Biscuité', 'multicereales-biscuite',
 'Des céréales riches en fibres et en goût pour accompagner l\'autonomie grandissante.',
 'Des céréales riches en fibres et en goût pour accompagner l\'autonomie grandissante.',
 'Dès 10 mois', '250g', 7.10, 'uploads/products/multicereales.jpg',
 'Avoine bio\nBlé ancien\nArôme naturel', '', '', 'Délayer dans le lait habituel de bébé en suivant les indications de l\'emballage.', 0, 'actif', 6),

(2, 'Compote Mangue & Papaye', 'compote-mangue-papaye',
 'Un cocktail de vitamines tropicales pour éveiller les papilles de bébé aux saveurs douces et fruitées.',
 'Un cocktail de vitamines tropicales pour éveiller les papilles de bébé aux saveurs douces et fruitées.',
 'Dès 6 mois', '4 x 100g', 5.40, 'uploads/products/compote-mangue-papaye.jpg',
 'Mangue\nPapaye', '', '', 'Idéale au goûter, à servir fraîche ou à température ambiante.', 1, 'actif', 7),

(3, 'Céréales Millet & Baobab', 'cereales-millet-baobab',
 'Le super pouvoir du baobab associé au millet local pour des bouillies énergétiques et digestes.',
 'Le super pouvoir du baobab associé au millet local pour des bouillies énergétiques et digestes.',
 'Dès 12 mois', '250g', 6.90, 'uploads/products/cereales-millet-baobab.jpg',
 'Millet local\nBaobab', '', '', 'Délayer dans le lait habituel de bébé en suivant les indications de l\'emballage.', 1, 'actif', 8),

(1, 'Velouté Courgette & Riz', 'veloute-courgette-riz',
 'Une alliance douce et digeste pour le déjeuner.',
 'Une alliance douce et digeste pour le déjeuner.',
 'Dès 6 mois', '200g', 3.40, 'uploads/products/veloute-courgette-riz.jpg',
 'Courgette\nRiz', '', '', 'Servir à température ambiante ou légèrement tiédi au bain-marie. Remuer avant de servir.', 0, 'actif', 9),

(1, 'Potiron & Quinoa', 'potiron-quinoa',
 'Une texture onctueuse et des saveurs subtiles.',
 'Une texture onctueuse et des saveurs subtiles.',
 'Dès 8 mois', '200g', 3.60, 'uploads/products/potiron-quinoa.jpg',
 'Potiron\nQuinoa', '', '', 'Servir à température ambiante ou légèrement tiédi au bain-marie. Remuer avant de servir.', 0, 'actif', 10),

(1, 'Petits Pois & Pomme de terre', 'petits-pois-pomme-de-terre',
 'Le goût authentique du jardin pour bébé.',
 'Le goût authentique du jardin pour bébé.',
 'Dès 6 mois', '200g', 3.50, 'uploads/products/petits-pois-pomme-de-terre.jpg',
 'Petits pois\nPomme de terre', '', '', 'Servir à température ambiante ou légèrement tiédi au bain-marie. Remuer avant de servir.', 0, 'actif', 11);

-- Paramètres du site
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name',        'HAMSI'),
('site_tagline',     'Hamsi est la marque africaine qui révolutionne le babyfood en Afrique.'),
('logo',             'assets/images/logo-icon.png'),
('color_primary',    '#4B6453'),
('color_secondary',  '#C3DFC9'),
('color_button',     '#4B6453'),
('color_text',       '#201439'),
('color_background', '#FEF7FF'),
('color_surface',    '#F4EAFF'),
('contact_phone',    '+253 77 74 37 09'),
('contact_phone_2',  '+253 77 71 65 35'),
('contact_email',    'simanrachid@gmail.com'),
('contact_address',  'Djibouti'),
('contact_hours',    'Du lundi au vendredi, 9h - 18h'),
('website',          'www.hamsi.com'),
('whatsapp_number',  '25377743709'),
('whatsapp_message', 'Bonjour Hamsi, j\'aimerais avoir des informations sur vos produits.'),
('currency',         '€');
