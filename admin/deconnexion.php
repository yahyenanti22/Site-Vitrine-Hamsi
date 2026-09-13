<?php
/**
 * Déconnexion : destruction complète de la session puis retour à la page de connexion.
 */
require __DIR__ . '/includes/init.php';

admin_logout();
redirect('login.php?logout=1');
