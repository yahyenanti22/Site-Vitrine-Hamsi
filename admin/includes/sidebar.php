<?php
/**
 * Menu latéral de l'administration.
 */
defined('HAMSI') || exit('Accès interdit');

$unread = unread_messages_count();
$menu = [
    'dashboard'  => ['index.php', 'grid', 'Tableau de bord'],
    'produits'   => ['produits.php', 'package', 'Produits'],
    'categories' => ['categories.php', 'tag', 'Catégories'],
    'messages'   => ['messages.php', 'inbox', 'Messages'],
    'admins'     => ['admins.php', 'users', 'Administrateurs'],
    'parametres' => ['parametres.php', 'sliders', 'Paramètres'],
];
?>
<aside class="sidebar" id="sidebar" aria-label="Menu d'administration">
    <a class="sidebar__brand" href="index.php">
        <span class="sidebar__logo"><img src="<?= e(media_url(setting('logo'), 'assets/images/logo-icon.png')) ?>" alt="" width="36" height="36"></span>
        <span><strong><?= e(setting('site_name', 'HAMSI')) ?></strong><small>Administration</small></span>
    </a>

    <nav class="sidebar__nav">
        <?php foreach ($menu as $key => [$href, $ico, $label]): ?>
            <a class="sidebar__link<?= $activeMenu === $key ? ' is-active' : '' ?>" href="<?= e($href) ?>"<?= $activeMenu === $key ? ' aria-current="page"' : '' ?>>
                <?= icon($ico) ?><span><?= e($label) ?></span>
                <?php if ($key === 'messages' && $unread > 0): ?><span class="sidebar__count" title="Messages non lus"><?= $unread ?></span><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar__bottom">
        <a class="sidebar__link" href="../index.php" target="_blank" rel="noopener"><?= icon('external') ?><span>Voir le site</span></a>
        <a class="sidebar__link sidebar__link--logout" href="deconnexion.php"><?= icon('logout') ?><span>Déconnexion</span></a>
    </div>
</aside>
<div class="sidebar-backdrop" data-sidebar-close hidden></div>
