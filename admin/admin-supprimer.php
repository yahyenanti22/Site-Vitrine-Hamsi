<?php
/**
 * Suppression d'un compte administrateur (réservé à l'administrateur principal).
 * La confirmation est demandée par JavaScript avant l'envoi du formulaire.
 */
require __DIR__ . '/includes/auth.php';
require_super_admin();

if (!is_post()) {
    redirect('admins.php');
}
require_csrf('admins.php');

$id = isset($_POST['id']) && ctype_digit((string) $_POST['id']) ? (int) $_POST['id'] : 0;
$me = current_admin();

if ($id <= 0) {
    flash('error', 'Compte introuvable.');
    redirect('admins.php');
}

// On ne supprime pas son propre compte
if ($id === (int) $me['id']) {
    flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
    redirect('admins.php');
}

$stmt = db()->prepare('SELECT id, name, role FROM administrators WHERE id = :id');
$stmt->execute([':id' => $id]);
$target = $stmt->fetch();

if (!$target) {
    flash('error', 'Ce compte n\'existe plus.');
    redirect('admins.php');
}

// On conserve toujours au moins un administrateur principal
if ($target['role'] === 'super_admin') {
    $supers = (int) db()->query("SELECT COUNT(*) FROM administrators WHERE role = 'super_admin'")->fetchColumn();
    if ($supers <= 1) {
        flash('error', 'Impossible de supprimer le dernier administrateur principal.');
        redirect('admins.php');
    }
}

db()->prepare('DELETE FROM administrators WHERE id = :id')->execute([':id' => $id]);
flash('success', 'Le compte de ' . $target['name'] . ' a été supprimé.');
redirect('admins.php');
