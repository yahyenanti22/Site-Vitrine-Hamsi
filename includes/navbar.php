<?php
/**
 * Barre de navigation du site public.
 */
defined('HAMSI') || exit('Accès interdit');

$current = current_page();
$navItems = [
    'index.php'      => 'Accueil',
    'produits.php'   => 'Nos produits',
    'apropos.php'    => 'À propos',
    'engagement.php' => 'Notre engagement',
    'contact.php'    => 'Contact',
];
// La fiche produit active l'onglet « Nos produits »
if ($current === 'produit.php') {
    $current = 'produits.php';
}
?>
<header class="site-header">
    <div class="container site-header__inner">
        <a class="brand" href="<?= e(url('index.php')) ?>" aria-label="<?= e(setting('site_name', 'HAMSI')) ?> — retour à l'accueil">
            <span class="brand__logo"><img src="<?= e(media_url(setting('logo'), 'assets/images/logo-icon.png')) ?>" alt="" width="40" height="40"></span>
            <span class="brand__name"><?= e(setting('site_name', 'HAMSI')) ?></span>
        </a>

        <nav class="main-nav" id="main-nav" aria-label="Navigation principale">
            <ul class="main-nav__list">
                <?php foreach ($navItems as $file => $label): ?>
                    <li>
                        <a href="<?= e(url($file)) ?>" class="main-nav__link<?= $current === $file ? ' is-active' : '' ?>"<?= $current === $file ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a class="btn btn--primary main-nav__cta" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener"><?= icon('chat') ?> Commander sur WhatsApp</a>
        </nav>

        <div class="site-header__actions">
            <a class="btn btn--primary btn--sm site-header__wa" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener"><?= icon('chat') ?> WhatsApp</a>
            <a class="icon-btn" href="<?= e(url('admin/login.php')) ?>" aria-label="Espace administrateur" title="Espace administrateur"><?= icon('user') ?></a>
            <button class="nav-toggle" type="button" aria-controls="main-nav" aria-expanded="false" aria-label="Ouvrir le menu">
                <span class="nav-toggle__open"><?= icon('menu') ?></span>
                <span class="nav-toggle__close"><?= icon('close') ?></span>
            </button>
        </div>
    </div>
</header>
