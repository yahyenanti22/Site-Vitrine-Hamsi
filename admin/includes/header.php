<?php
/**
 * En-tête HTML de l'administration.
 * Variables attendues : $adminTitle (titre de la page), $activeMenu (clé du menu actif)
 */
defined('HAMSI') || exit('Accès interdit');

$adminTitle = $adminTitle ?? 'Administration';
$activeMenu = $activeMenu ?? '';
$adminCssVersion = @filemtime(__DIR__ . '/../assets/css/admin.css') ?: 1;
$adminJsVersion = @filemtime(__DIR__ . '/../assets/js/admin.js') ?: 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($adminTitle) ?> | Administration <?= e(setting('site_name', 'HAMSI')) ?></title>
    <link rel="icon" type="image/png" href="<?= e(media_url(setting('logo'), 'assets/images/favicon.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css?v=<?= (int) $adminCssVersion ?>">
    <style><?= theme_css_vars() ?></style>
    <script src="assets/js/admin.js?v=<?= (int) $adminJsVersion ?>" defer></script>
</head>
<body class="admin-body">
<div class="admin-layout">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <div class="admin-main">
        <?php require __DIR__ . '/navbar.php'; ?>
        <main class="admin-content" id="contenu">
            <?php render_admin_flashes(); ?>
