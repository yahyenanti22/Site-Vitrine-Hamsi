<?php
/**
 * En-tête commun du site public.
 * Variables attendues (facultatives) : $pageTitle, $pageDescription, $bodyClass
 */
defined('HAMSI') || exit('Accès interdit');

$siteName = setting('site_name', 'HAMSI');
$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . $siteName : $siteName . ' — Babyfood africain, sain et local';
$pageDescription = $pageDescription ?? setting('site_tagline');
$bodyClass = $bodyClass ?? '';
$cssVersion = @filemtime(BASE_PATH . '/assets/css/style.css') ?: 1;
$jsVersion = @filemtime(BASE_PATH . '/assets/js/main.js') ?: 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <link rel="icon" type="image/png" href="<?= e(media_url(setting('logo'), 'assets/images/favicon.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>?v=<?= (int) $cssVersion ?>">
    <style><?= theme_css_vars() ?></style>
    <script src="<?= e(url('assets/js/main.js')) ?>?v=<?= (int) $jsVersion ?>" defer></script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#contenu">Aller au contenu</a>
<?php require BASE_PATH . '/includes/navbar.php'; ?>
<main id="contenu">
