<?php
/**
 * Création d'un compte administrateur (réservé à l'administrateur principal).
 */
require __DIR__ . '/includes/auth.php';
require_super_admin();
require __DIR__ . '/includes/admin-functions.php';

$admin = admin_defaults();
$errors = [];

if (is_post()) {
    if (!csrf_verify()) {
        $errors['global'] = 'La session a expiré. Renvoyez le formulaire.';
    } else {
        [$data, $errors] = admin_validate();

        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');

        if ($password === '') {
            $errors['password'] = 'Choisissez un mot de passe.';
        } elseif ($policyError = password_policy_error($password)) {
            $errors['password'] = $policyError;
        } elseif ($password !== $confirm) {
            $errors['password_confirm'] = 'Les deux mots de passe ne correspondent pas.';
        }

        if (!$errors) {
            $stmt = db()->prepare(
                'INSERT INTO administrators (name, username, email, password, role)
                 VALUES (:name, :username, :email, :password, :role)'
            );
            $stmt->execute([
                ':name'     => $data['name'],
                ':username' => $data['username'],
                ':email'    => $data['email'],
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':role'     => $data['role'],
            ]);
            flash('success', 'Le compte de ' . $data['name'] . ' a été créé.');
            redirect('admins.php');
        }
        $admin = array_merge($admin, $data);
    }
}

$adminTitle = 'Ajouter un administrateur';
$activeMenu = 'admins';
require __DIR__ . '/includes/header.php';
?>
<a class="back-link" href="admins.php"><?= icon('arrow-left') ?> Retour aux administrateurs</a>
<?php if (isset($errors['global'])): ?>
    <div class="alert alert--error" role="alert"><?= icon('alert') ?><span><?= e($errors['global']) ?></span></div>
<?php elseif ($errors): ?>
    <div class="alert alert--error" role="alert"><?= icon('alert') ?><span>Le compte n'a pas été créé : corrigez les champs signalés.</span></div>
<?php endif; ?>
<?php render_admin_form($admin, $errors, 'Créer le compte', true); ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
