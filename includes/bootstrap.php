<?php
/**
 * Point d'entrée commun à toutes les pages (site public et administration).
 * - charge la configuration et les fonctions
 * - démarre une session sécurisée
 * - envoie les en-têtes de sécurité HTTP
 */
if (!defined('HAMSI')) {
    define('HAMSI', true);
}
define('BASE_PATH', dirname(__DIR__));

// Préfixe des URL relatives : '' pour le site public, '../' pour /admin
if (!defined('ROOT_PREFIX')) {
    define('ROOT_PREFIX', '');
}

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/icons.php';
require_once BASE_PATH . '/includes/components.php';

date_default_timezone_set('Africa/Djibouti');
mb_internal_encoding('UTF-8');

if (!APP_DEBUG) {
    ini_set('display_errors', '0');
}

// ------------------------------------------------------------------
// Session sécurisée
// ------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    session_name('HAMSI_SESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ------------------------------------------------------------------
// En-têtes de sécurité
// ------------------------------------------------------------------
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        . "font-src 'self' https://fonts.gstatic.com; script-src 'self'; "
        . "frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
}
