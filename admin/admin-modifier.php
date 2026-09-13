<?php
/**
 * Modification d'un compte administrateur.
 * - l'administrateur principal peut modifier tous les comptes
 * - un administrateur ne peut modifier que le sien
 */
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/admin-functions.php';

$me = current_admin();
$id = isset($_GET['id']) && ctype_digit((string) $_GET['id']) ? (int) $_GET['id'] : (int) $me['id'];

if ($id !== (int) $me['id'] && !is_super_admin()) {
    flash('error', 'Vous ne pouvez modifier que votre propre compte.');
    redirect('admins.php');
}

$stmt = db()->prepare('SELECT id, name, username, email, role, password FROM administrators WHERE id = :id');
$stmt->execute([':id' => $id]);
$admin = $stmt->fetch();

if (!$admin) {
    flash('error', 'Ce compte n\'existe pas.');
    redirect('admins.php');
}

$isSelf = $id === (int) $me['id'];
$canSetRole = is_super_admin() && !$isSelf; // on ne retire pas son propre rôle par mégarde
$currentHash = $admin['password'];
unset($admin['password']);
$errors = [];

if (is_post()) {
    if (!csrf_verify()) {
        $errors['global'] = 'La session a expiré. Renvoyez le formulaire.';
    } else {
        [$data, $errors] = admin_validate($id);
        if (!$canSetRole) {
            $data['role'] = $admin['role'];
        }

        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');
        $current = (string) ($_POST['current_password'] ?? '');
        $newHash = null;

        if ($password !== '' || $confirm !== '') {
            // Pour son propre compte, on vérifie le mot de passe actuel avant de le changer
            if ($isSelf && !password_verify($current, $currentHash)) {
                $errors['current_password'] = 'Le mot de passe actuel est incorrect.';
            } elseif ($policyError = password_policy_error($password)) {
                $errors['password'] = $policyError;
            } elseif ($password !== $confirm) {
                $errors['password_confirm'] = 'Les deux mots de passe ne correspondent pas.';
            } else {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        // Le dernier administrateur principal doit le rester
        if (!$errors && $admin['role'] === 'super_admin' && $data['role'] !== 'super_admin') {
            $supers = (int) db()->query("SELECT COUNT(*) FROM administrators WHERE role = 'super_admin'")->fetchColumn();
            if ($supers <= 1) {
                $errors['global'] = 'Ce compte est le dernier administrateur principal : son rôle ne peut pas être modifié.';
            }
        }

        if (!$errors) {
            $sql = 'UPDATE administrators SET name = :name, username = :username, email = :email, role = :role'
                . ($newHash !== null ? ', password = :password' : '') . ' WHERE id = :id';
            $params = [
                ':name'     => $data['name'],
                ':username' => $data['username'],
                ':email'    => $data['email'],
                ':role'     => $data['role'],
                ':id'       => $id,
            ];
            if ($newHash !== null) {
                $params[':password'] = $newHash;
            }
            db()->prepare($sql)->execute($params);

            if ($isSelf) {
                $_SESSION['admin_name'] = $data['name'];
            }
            flash('success', 'Le compte de ' . $data['name'] . ' a été mis à jour'
                . ($newHash !== null ? ', y compris son mot de passe.' : '.'));
            redirect('admins.php');
        }
        $admin = array_merge($admin, $data);
    }
}

$adminTitle = $isSelf ? 'Mon compte' : 'Modifier un administrateur';
$activeMenu = 'admins';
require __DIR__ . '/includes/header.php';
?>
<a class="back-link" href="admins.php"><?= icon('arrow-left') ?> Retour aux administrateurs</a>
<?php if (isset($errors['global'])): ?>
    <div class="alert alert--error" role="alert"><?= icon('alert') ?><span><?= e($errors['global']) ?></span></div>
<?php elseif ($errors): ?>
    <div class="alert alert--error" role="alert"><?= icon('alert') ?><span>Le compte n'a pas été enregistré : corrigez les champs signalés.</span></div>
<?php endif; ?>
<?php render_admin_form($admin, $errors, 'Enregistrer les modifications', $canSetRole, $isSelf); ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
