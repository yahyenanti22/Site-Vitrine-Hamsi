<?php
/**
 * Barre supérieure de l'administration : titre, nom de l'administrateur connecté, déconnexion.
 */
defined('HAMSI') || exit('Accès interdit');

$me = current_admin();
?>
<header class="topbar">
    <button class="topbar__toggle" type="button" aria-controls="sidebar" aria-expanded="false" aria-label="Ouvrir le menu"><?= icon('menu') ?></button>
    <h1 class="topbar__title"><?= e($adminTitle) ?></h1>
    <div class="topbar__user">
        <span class="topbar__avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($me['name'], 0, 1))) ?></span>
        <span class="topbar__hello">
            Bienvenue, <strong><?= e($me['name']) ?></strong>
            <small><?= $me['role'] === 'super_admin' ? 'Administrateur principal' : 'Administrateur' ?></small>
        </span>
        <a class="btn btn--ghost btn--sm topbar__logout" href="deconnexion.php"><?= icon('logout') ?><span>Déconnexion</span></a>
    </div>
</header>
