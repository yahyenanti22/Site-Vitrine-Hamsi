<?php
/**
 * Initialisation de l'espace administrateur (sans exiger de connexion).
 * Utilisé directement par login.php ; les autres pages passent par auth.php.
 */
define('ROOT_PREFIX', '../');
require_once __DIR__ . '/../../includes/bootstrap.php';

const ADMIN_SESSION_TIMEOUT = 7200;   // déconnexion après 2 h d'inactivité
const LOGIN_MAX_ATTEMPTS = 5;         // tentatives autorisées...
const LOGIN_WINDOW_MINUTES = 15;      // ...sur cette durée, par adresse IP

function admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

/** Administrateur connecté, relu en base à chaque requête (null s'il a été supprimé). */
function current_admin(bool $refresh = false): ?array
{
    static $admin = false;
    if ($admin === false || $refresh) {
        $admin = null;
        if (admin_logged_in()) {
            $stmt = db()->prepare('SELECT id, name, username, email, role, last_login_at FROM administrators WHERE id = :id');
            $stmt->execute([':id' => (int) $_SESSION['admin_id']]);
            $admin = $stmt->fetch() ?: null;
        }
    }
    return $admin;
}

function is_super_admin(): bool
{
    $admin = current_admin();
    return $admin !== null && $admin['role'] === 'super_admin';
}

/** Ferme proprement la session administrateur. */
function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}

/** Bloque l'accès aux pages admin pour les visiteurs non connectés. */
function require_login(): void
{
    if (!admin_logged_in()) {
        redirect('login.php');
    }
    // Expiration après inactivité
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > ADMIN_SESSION_TIMEOUT) {
        admin_logout();
        redirect('login.php?expire=1');
    }
    // Compte supprimé entre-temps
    if (current_admin() === null) {
        admin_logout();
        redirect('login.php');
    }
    $_SESSION['last_activity'] = time();
    $_SESSION['admin_name'] = current_admin()['name'];
}

/** Réserve une page à l'administrateur principal. */
function require_super_admin(): void
{
    if (!is_super_admin()) {
        flash('error', 'Cette action est réservée à l\'administrateur principal.');
        redirect('index.php');
    }
}

/** Refuse les formulaires POST sans jeton CSRF valide. */
function require_csrf(string $redirectTo): void
{
    if (!csrf_verify()) {
        flash('error', 'La session a expiré ou le formulaire est invalide. Réessayez.');
        redirect($redirectTo);
    }
}

/** Détecte un envoi dépassant post_max_size (PHP vide alors $_POST et $_FILES). */
function post_too_large(): bool
{
    return is_post() && empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0;
}

/** Règles de mot de passe administrateur. */
function password_policy_error(string $password): ?string
{
    if (mb_strlen($password) < 8) {
        return 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        return 'Le mot de passe doit contenir au moins une lettre et un chiffre.';
    }
    if (strlen($password) > 72) {
        return 'Le mot de passe ne doit pas dépasser 72 caractères.';
    }
    return null;
}

/** Messages flash de l'administration. */
function render_admin_flashes(): void
{
    foreach (get_flashes('main') as $f) {
        $type = in_array($f['type'], ['success', 'error', 'info'], true) ? $f['type'] : 'info';
        echo '<div class="alert alert--' . $type . '" role="' . ($type === 'error' ? 'alert' : 'status') . '">'
            . icon($type === 'success' ? 'check-circle' : ($type === 'error' ? 'alert' : 'info'))
            . '<span>' . e($f['message']) . '</span></div>';
    }
}

/** Nombre de messages non lus (badge du menu). */
function unread_messages_count(): int
{
    return (int) db()->query("SELECT COUNT(*) FROM contacts WHERE status = 'nouveau'")->fetchColumn();
}
