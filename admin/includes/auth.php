<?php
/**
 * Protection des pages admin : à inclure en première ligne de chaque page protégée.
 * Un visiteur non connecté est redirigé vers login.php.
 */
require_once __DIR__ . '/init.php';
require_login();
