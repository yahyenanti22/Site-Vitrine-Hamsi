# HAMSI — Site vitrine + espace d'administration

Site vitrine de la marque **Hamsi** (babyfood africain) avec un espace d'administration sécurisé.

Développé en **PHP 8 / MySQL** avec **PDO**, HTML5, CSS3 et un peu de JavaScript pour les interactions.
Aucun framework JavaScript, aucune API externe : le HTML est généré côté serveur par PHP à partir de la base MySQL.

---

## 1. Prérequis

| Élément | Version minimale |
|---|---|
| PHP | 8.0 (testé avec 8.3) |
| MySQL / MariaDB | 5.7 / 10.4 |
| Serveur web | Apache (WAMP, XAMPP, Laragon…) |

Extensions PHP nécessaires : `pdo_mysql`, `mbstring`, `fileinfo` (toutes activées par défaut dans WAMP et XAMPP).

---

## 2. Installer WAMP ou XAMPP

1. Télécharger **WAMP** (<https://www.wampserver.com>) ou **XAMPP** (<https://www.apachefriends.org>).
2. Lancer l'installation en gardant les options par défaut.
3. Démarrer le serveur et vérifier que **Apache** et **MySQL** sont bien actifs (icône verte pour WAMP, boutons « Running » pour XAMPP).

---

## 3. Installer le projet

1. Décompresser l'archive `site-vitrine-complet.zip`.
2. Copier le dossier obtenu dans le répertoire web du serveur :
   - WAMP : `C:\wamp64\www\hamsi`
   - XAMPP : `C:\xampp\htdocs\hamsi`

Le dossier doit contenir directement `index.php`, `admin/`, `assets/`, etc.

---

## 4. Créer et importer la base de données

1. Ouvrir **phpMyAdmin** : <http://localhost/phpmyadmin>
2. Onglet **Importer** → **Choisir un fichier** → sélectionner `database/database.sql`
3. Cliquer sur **Exécuter**.

Le script crée la base `hamsi_db`, toutes les tables, les catégories, les 11 produits, les paramètres du site et le compte administrateur de test. Inutile de créer la base à la main : le script s'en charge.

---

## 5. Configurer la connexion MySQL

Les identifiants sont regroupés dans un seul fichier : **`config/database.php`**.

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');   // MariaDB sous WAMP utilise souvent 3307
define('DB_NAME', 'hamsi_db');
define('DB_USER', 'root');
define('DB_PASS', '');       // vide par défaut sous WAMP et XAMPP
```

Ces valeurs conviennent à une installation WAMP/XAMPP standard. À modifier uniquement si votre MySQL utilise un autre port ou un mot de passe.

Pendant le développement, passer `APP_DEBUG` à `true` dans ce même fichier affiche le détail des erreurs.

---

## 6. Accéder au site

| | URL |
|---|---|
| Site public | <http://localhost/hamsi/> |
| Administration | <http://localhost/hamsi/admin/login.php> |

### Compte administrateur de test

| | |
|---|---|
| Identifiant | `admin` (ou `admin@hamsi.com`) |
| Mot de passe | `Hamsi@2026` |

Le mot de passe est enregistré en base sous forme de **hash `password_hash()`**, jamais en clair.
**Changez-le dès la première connexion** (voir la section 12).

---

## 7. Gérer les produits

Administration → **Produits**

- **Ajouter** : bouton « Ajouter un produit ». Seuls le nom et la description courte sont obligatoires.
- **Modifier** : icône crayon sur la ligne du produit.
- **Supprimer** : icône corbeille, avec demande de confirmation. L'image du produit est supprimée en même temps.
- **Changer l'image** : dans le formulaire, « Remplacer l'image ». JPG, PNG ou WEBP, 2 Mo maximum. L'ancienne image est effacée automatiquement du dossier `uploads/products/`.

Quelques champs utiles :

- **Statut** : « Inactif » retire le produit du site public sans le supprimer.
- **Produit phare** : affiche le produit dans la section « Produits phares » de l'accueil (3 maximum).
- **Ordre d'affichage** : les plus petits nombres apparaissent en premier.
- **Ingrédients** : un par ligne. Un détail facultatif peut être ajouté après une barre verticale.
  ```
  Patate douce bio | Récolte locale - 60%
  Carottes tendres | Récolte locale - 38%
  ```
- Les champs **Informations nutritionnelles**, **Format & quantité** et **Conseils de dégustation** alimentent les onglets de la fiche produit. Un onglet laissé vide n'apparaît pas sur le site.

### Catégories

Administration → **Catégories**. Elles servent de filtres sur la page « Nos produits ». Supprimer une catégorie ne supprime pas ses produits : ils passent simplement sans catégorie.

---

## 8. Modifier le logo

Administration → **Paramètres** → bloc **Logo** → « Changer le logo ».

JPG, PNG ou WEBP, 1 Mo maximum. La charte graphique recommande une image carrée de 401 × 401 px (minimum 223 × 213 px). Le nouveau logo apparaît aussitôt dans l'en-tête, le pied de page, l'administration et le favicon.

---

## 9. Modifier le nom du site

Administration → **Paramètres** → **Nom du site**. Il est repris dans l'en-tête, le pied de page, le titre des onglets du navigateur et l'administration.

---

## 10. Modifier les couleurs

Administration → **Paramètres** → **Couleurs principales**. Six couleurs sont réglables, au sélecteur ou en saisissant un code hexadécimal :

| Réglage | Rôle |
|---|---|
| Couleur principale | Boutons WhatsApp, bandeaux verts, liens actifs |
| Couleur secondaire | Badges et pastilles d'icônes (vert menthe) |
| Couleur des boutons | Fond des boutons principaux |
| Couleur du texte | Titres et textes |
| Couleur de fond | Fond général des pages |
| Couleur des blocs | Fond des sections teintées |

Les couleurs de la charte Hamsi sont rappelées sous le formulaire :
`#C3DFC9` · `#B3C2D1` · `#A591C6` · `#8474B6` · `#100429`

Elles sont enregistrées en base et relues à chaque affichage : les modifications sont visibles immédiatement sur le site public.

---

## 11. Gérer les administrateurs

Administration → **Administrateurs** (réservé à l'administrateur principal).

- **Ajouter** un compte : nom, identifiant, email, mot de passe et rôle.
- **Modifier** un compte existant.
- **Supprimer** un compte, avec confirmation.

Deux rôles existent :

- **Administrateur** : produits, catégories et messages.
- **Administrateur principal** : en plus, les comptes et les paramètres du site.

Deux sécurités sont en place : personne ne peut supprimer son propre compte, et le dernier administrateur principal ne peut être ni supprimé ni rétrogradé.

---

## 12. Changer le mot de passe administrateur

Administration → **Administrateurs** → icône crayon sur votre ligne → bloc **Mot de passe**.

Pour modifier votre propre mot de passe, saisissez d'abord le mot de passe actuel (vérifié avec `password_verify()`), puis le nouveau deux fois. Il doit contenir au moins 8 caractères, dont une lettre et un chiffre, et il est enregistré avec `password_hash()`.

Laisser ces champs vides conserve le mot de passe existant.

---

## 13. Messages de contact

Les messages envoyés depuis la page Contact sont enregistrés dans la table `contacts` et consultables dans Administration → **Messages**. Chaque message peut être marqué comme lu, traité ou non lu, recevoir une réponse par email, ou être supprimé. Le nombre de messages non lus s'affiche dans le menu latéral.

Les inscriptions à la newsletter du pied de page sont enregistrées dans la table `newsletter_subscribers`.

---

## 14. Structure du projet

```
hamsi/
├── index.php               Accueil
├── produits.php            Catalogue (filtrable par catégorie)
├── produit.php             Fiche d'un produit
├── apropos.php             À propos
├── engagement.php          Notre engagement
├── contact.php             Contact + formulaire
├── newsletter.php          Traitement du formulaire newsletter
│
├── config/
│   └── database.php        Connexion PDO centralisée
│
├── includes/
│   ├── bootstrap.php       Session sécurisée, en-têtes HTTP, chargements
│   ├── functions.php       Sécurité, paramètres, uploads, formatage
│   ├── components.php      Cartes produits réutilisables
│   ├── icons.php           Icônes SVG intégrées
│   ├── header.php  navbar.php  footer.php
│
├── assets/
│   ├── css/style.css
│   ├── js/main.js          Menu mobile, onglets de la fiche produit
│   └── images/             Logo, mascotte et photos des pages
│
├── admin/
│   ├── login.php  index.php  deconnexion.php
│   ├── produits.php  produit-ajouter.php  produit-modifier.php  produit-supprimer.php
│   ├── categories.php  messages.php
│   ├── admins.php  admin-ajouter.php  admin-modifier.php  admin-supprimer.php
│   ├── parametres.php
│   ├── includes/           init.php, auth.php, gabarits et formulaires
│   └── assets/             admin.css, admin.js
│
├── uploads/
│   ├── products/           Images des produits
│   └── site/               Logos envoyés depuis l'administration
│
├── database/
│   └── database.sql        Base complète + données initiales
│
└── README.md
```

---

## 15. Base de données

| Table | Contenu |
|---|---|
| `administrators` | Comptes d'administration (mots de passe hashés) |
| `categories` | Purées, Compotes, Céréales |
| `products` | Produits, prix, images, onglets de la fiche |
| `site_settings` | Nom, logo, couleurs, coordonnées, WhatsApp |
| `contacts` | Messages du formulaire de contact |
| `newsletter_subscribers` | Inscrits à la newsletter |
| `login_attempts` | Tentatives de connexion (anti-force brute) |

---

## 16. Sécurité mise en place

- **PDO** avec requêtes préparées partout (aucune concaténation de SQL).
- Mots de passe **`password_hash()` / `password_verify()`**, jamais stockés en clair.
- **Sessions** avec cookie `HttpOnly`, `SameSite=Lax`, `use_strict_mode`, identifiant régénéré à la connexion et expiration après 2 h d'inactivité.
- Pages d'administration protégées par `require_login()` : toute page appelée sans session valide redirige vers la connexion.
- **Jeton CSRF** vérifié sur tous les formulaires POST.
- **Protection XSS** : toutes les valeurs affichées passent par `htmlspecialchars()`.
- **Uploads** : extension et **type MIME réel** vérifiés, dimensions contrôlées, taille limitée, nom de fichier aléatoire, destination forcée dans `uploads/`. Un fichier `.htaccess` empêche l'exécution de tout PHP déposé dans ce dossier.
- Accès direct interdit aux dossiers `config/`, `includes/` et `database/`.
- Limitation à 5 tentatives de connexion par quart d'heure et par adresse IP.
- En-têtes de sécurité : `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`.

---

## 17. Contenu et images

Les textes proviennent de la charte graphique Hamsi (vision, mission, valeurs, ODD, signification du logo et de la mascotte).

Les **coordonnées** enregistrées sont celles de la charte : `simanrachid@gmail.com`, `+253 77 74 37 09` et `+253 77 71 65 35`. L'adresse indiquée, « Djibouti », est une déduction à partir de l'indicatif téléphonique : à corriger dans les Paramètres si nécessaire.

Le **logo** et la **mascotte** ont été extraits du PDF de la charte en haute résolution.

Les **photos** des produits et des pages ont été récupérées dans les maquettes fournies, qui sont de faible résolution ; elles ont été agrandies automatiquement. Pour un rendu optimal, remplacez-les par les fichiers d'origine : les images produits depuis l'administration, les autres directement dans `assets/images/` en conservant les mêmes noms de fichiers.

Deux prix ne figuraient pas sur les maquettes (Compote Mangue & Papaye, Céréales Millet & Baobab) et ont reçu une valeur provisoire, à ajuster dans l'administration.

---

## 18. En cas de problème

**« Connexion à la base de données impossible »**
MySQL n'est pas démarré, la base n'a pas été importée, ou le port est différent. Sous WAMP, MariaDB écoute souvent sur le port **3307** : ajustez `DB_PORT` dans `config/database.php`.

**Les images ne s'affichent pas**
Vérifiez que le dossier `uploads/` a bien été copié et qu'il est accessible en écriture.

**« La session a expiré » à l'envoi d'un formulaire**
Le jeton CSRF a expiré ou les cookies sont bloqués. Rechargez la page et renvoyez le formulaire.

**Erreur au téléversement d'une grande image**
Augmentez `upload_max_filesize` et `post_max_size` dans le `php.ini` de WAMP/XAMPP, puis redémarrez Apache.
#   S i t e - V i t r i n e - H a m s i  
 