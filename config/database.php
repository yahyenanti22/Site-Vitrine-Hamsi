<?php
/**
 * Configuration de la base de données (connexion centralisée PDO).
 * Seul fichier à modifier pour adapter les identifiants MySQL.
 */
defined('HAMSI') || exit('Accès interdit');

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');      // WAMP : MySQL = 3306, MariaDB = 3307
define('DB_NAME', 'hamsi_db');
define('DB_USER', 'root');
define('DB_PASS', '');          // WAMP/XAMPP : mot de passe vide par défaut
define('DB_CHARSET', 'utf8mb4');

// Mettre à true uniquement en développement pour afficher le détail des erreurs.
define('APP_DEBUG', true);

/**
 * Retourne l'instance PDO unique (créée à la première demande).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('[HAMSI] Connexion MySQL impossible : ' . $e->getMessage());
            http_response_code(500);
            $detail = APP_DEBUG ? '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>' : '';
            exit('<!DOCTYPE html><html lang="fr"><meta charset="utf-8"><title>Erreur de connexion</title>'
                . '<body style="font-family:system-ui,sans-serif;max-width:640px;margin:80px auto;padding:0 24px;color:#201439">'
                . '<h1 style="font-size:1.5rem">Connexion à la base de données impossible</h1>'
                . '<p>Vérifiez que MySQL est démarré, que la base <strong>' . DB_NAME . '</strong> a été importée '
                . '(fichier <code>database/database.sql</code>) et que les identifiants de <code>config/database.php</code> sont corrects.</p>'
                . $detail . '</body></html>');
        }
    }

    return $pdo;
}
