<?php
/**
 * Connexion à l'espace administrateur.
 */
require __DIR__ . '/includes/init.php';

if (admin_logged_in() && current_admin() !== null) {
    redirect('index.php');
}

$error = '';
$info = '';
$identifier = '';

if (isset($_GET['logout'])) {
    $info = 'Vous êtes déconnecté. À bientôt !';
} elseif (isset($_GET['expire'])) {
    $info = 'Votre session a expiré après une période d\'inactivité. Reconnectez-vous.';
}

if (is_post()) {
    $identifier = post_str('identifier', 190);
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $ip = client_ip();

    // Nombre d'échecs récents pour cette adresse IP
    $countStmt = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts WHERE ip_address = :ip AND attempted_at > (NOW() - INTERVAL ' . LOGIN_WINDOW_MINUTES . ' MINUTE)'
    );
    $countStmt->execute([':ip' => $ip]);
    $recentFailures = (int) $countStmt->fetchColumn();

    if (!csrf_verify()) {
        $error = 'La session a expiré. Rechargez la page puis reconnectez-vous.';
    } elseif ($recentFailures >= LOGIN_MAX_ATTEMPTS) {
        $error = 'Trop de tentatives de connexion. Réessayez dans ' . LOGIN_WINDOW_MINUTES . ' minutes.';
    } elseif ($identifier === '' || $password === '') {
        $error = 'Renseignez votre identifiant (email ou nom d\'utilisateur) et votre mot de passe.';
    } else {
        $stmt = db()->prepare('SELECT id, name, password FROM administrators WHERE email = :email OR username = :username LIMIT 1');
        $stmt->execute([':email' => mb_strtolower($identifier), ':username' => $identifier]);
        $admin = $stmt->fetch();

        // Hash factice si le compte n'existe pas : même temps de réponse (pas d'indice sur les comptes)
        $hash = $admin['password'] ?? '$2y$10$yQHeenB2U.V3iAUUpDjIteDzBgZRGhF8X1Z9BaKEGqWZ.IF/pOUWi';

        if (password_verify($password, $hash) && $admin) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['last_activity'] = time();
            unset($_SESSION['csrf_token']);

            db()->prepare('DELETE FROM login_attempts WHERE ip_address = :ip OR attempted_at < (NOW() - INTERVAL 1 DAY)')->execute([':ip' => $ip]);
            db()->prepare('UPDATE administrators SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $admin['id']]);

            // Mise à niveau automatique du hash si l'algorithme par défaut a évolué
            if (password_needs_rehash($admin['password'], PASSWORD_DEFAULT)) {
                db()->prepare('UPDATE administrators SET password = :p WHERE id = :id')
                    ->execute([':p' => password_hash($password, PASSWORD_DEFAULT), ':id' => $admin['id']]);
            }

            flash('success', 'Connexion réussie. Bienvenue, ' . $admin['name'] . ' !');
            redirect('index.php');
        }

        db()->prepare('INSERT INTO login_attempts (ip_address, identifier) VALUES (:ip, :id)')
            ->execute([':ip' => $ip, ':id' => mb_substr($identifier, 0, 190)]);
        $remaining = LOGIN_MAX_ATTEMPTS - $recentFailures - 1;
        $error = 'Identifiant ou mot de passe incorrect.'
            . ($remaining > 0 && $remaining <= 2 ? ' Il vous reste ' . $remaining . ' tentative' . ($remaining > 1 ? 's' : '') . '.' : '');
    }
}
$cssVersion = @filemtime(__DIR__ . '/assets/css/admin.css') ?: 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Connexion | Administration <?= e(setting('site_name', 'HAMSI')) ?></title>
    <link rel="icon" type="image/png" href="<?= e(media_url(setting('logo'), 'assets/images/favicon.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css?v=<?= (int) $cssVersion ?>">
    <style><?= theme_css_vars() ?></style>
    <script src="assets/js/admin.js" defer></script>
</head>
<body class="login-body">
<main class="login-card">
    <div class="login-card__brand">
        <img src="../assets/images/logo-hamsi.png" alt="Logo <?= e(setting('site_name', 'HAMSI')) ?>" width="240" height="216">
        <p>Espace réservé à l'équipe <?= e(setting('site_name', 'HAMSI')) ?> pour gérer les produits, les messages et les paramètres du site.</p>
    </div>

    <div class="login-card__form">
        <h1>Connexion</h1>
        <p class="text-muted">Accédez au tableau de bord de l'administration.</p>

        <?php if ($info !== ''): ?>
            <div class="alert alert--info" role="status"><?= icon('info') ?><span><?= e($info) ?></span></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert--error" role="alert"><?= icon('alert') ?><span><?= e($error) ?></span></div>
        <?php endif; ?>

        <form method="post" action="login.php" class="form-stack" novalidate>
            <?= csrf_field() ?>
            <div class="field">
                <label for="identifier">Email ou nom d'utilisateur</label>
                <input id="identifier" type="text" name="identifier" value="<?= e($identifier) ?>" required maxlength="190" autocomplete="username" autofocus>
            </div>
            <div class="field">
                <label for="password">Mot de passe</label>
                <div class="password-field">
                    <input id="password" type="password" name="password" required maxlength="200" autocomplete="current-password">
                    <button type="button" class="password-toggle" data-toggle-password="password" aria-label="Afficher le mot de passe"><?= icon('eye') ?></button>
                </div>
            </div>
            <button class="btn btn--primary btn--block" type="submit"><?= icon('lock') ?> Se connecter</button>
        </form>
        <a class="login-card__back" href="../index.php"><?= icon('arrow-left') ?> Retour au site</a>
    </div>
</main>
</body>
</html>
