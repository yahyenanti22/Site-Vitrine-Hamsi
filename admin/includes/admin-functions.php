<?php
/**
 * Fonctions partagées par admin-ajouter.php et admin-modifier.php.
 */
defined('HAMSI') || exit('Accès interdit');

function admin_defaults(): array
{
    return ['id' => null, 'name' => '', 'username' => '', 'email' => '', 'role' => 'admin'];
}

/**
 * Valide les informations d'un compte administrateur.
 * @param int|null $excludeId Identifiant à ignorer lors du test d'unicité (modification)
 * @return array [données, erreurs]
 */
function admin_validate(?int $excludeId = null): array
{
    $errors = [];
    $data = [
        'name'     => post_str('name', 100),
        'username' => mb_strtolower(post_str('username', 50)),
        'email'    => mb_strtolower(post_str('email', 190)),
        'role'     => post_str('role', 20),
    ];

    if (mb_strlen($data['name']) < 2) {
        $errors['name'] = 'Indiquez le nom de l\'administrateur (2 caractères minimum).';
    }
    if (!preg_match('/^[a-z0-9._-]{3,50}$/', $data['username'])) {
        $errors['username'] = 'Identifiant invalide : 3 à 50 caractères, lettres minuscules, chiffres, point, tiret ou underscore.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Indiquez une adresse email valide.';
    }
    if (!in_array($data['role'], ['admin', 'super_admin'], true)) {
        $data['role'] = 'admin';
    }

    // Unicité de l'identifiant et de l'email
    foreach (['username' => 'Cet identifiant est déjà utilisé.', 'email' => 'Cette adresse email est déjà utilisée.'] as $field => $message) {
        if (isset($errors[$field])) {
            continue;
        }
        $sql = "SELECT COUNT(*) FROM administrators WHERE {$field} = :value" . ($excludeId ? ' AND id <> :id' : '');
        $stmt = db()->prepare($sql);
        $params = [':value' => $data[$field]];
        if ($excludeId) {
            $params[':id'] = $excludeId;
        }
        $stmt->execute($params);
        if ((int) $stmt->fetchColumn() > 0) {
            $errors[$field] = $message;
        }
    }

    return [$data, $errors];
}

/**
 * Affiche le formulaire d'un compte administrateur.
 *
 * @param array  $admin       Données affichées
 * @param array  $errors      Erreurs par champ
 * @param string $submitLabel Libellé du bouton
 * @param bool   $canSetRole  Autorise la modification du rôle
 * @param bool   $askCurrent  Demande le mot de passe actuel (modification de son propre compte)
 */
function render_admin_form(array $admin, array $errors, string $submitLabel, bool $canSetRole, bool $askCurrent = false): void
{
    $isEdit = !empty($admin['id']);
    $err = static fn (string $f): string => isset($errors[$f])
        ? '<span class="field__error">' . e($errors[$f]) . '</span>' : '';
    $cls = static fn (string $f): string => isset($errors[$f]) ? ' has-error' : '';
    ?>
    <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-layout">
            <div>
                <section class="panel">
                    <h2 class="panel__title">Informations du compte</h2>

                    <div class="field">
                        <label for="name">Nom complet <span class="req" aria-hidden="true">*</span></label>
                        <input class="input<?= $cls('name') ?>" id="name" type="text" name="name" value="<?= e($admin['name']) ?>" required maxlength="100" autocomplete="name">
                        <?= $err('name') ?>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label for="username">Identifiant de connexion <span class="req" aria-hidden="true">*</span></label>
                            <input class="input<?= $cls('username') ?>" id="username" type="text" name="username" value="<?= e($admin['username']) ?>" required maxlength="50" autocomplete="username">
                            <span class="field__hint">Lettres minuscules, chiffres, point, tiret ou underscore.</span>
                            <?= $err('username') ?>
                        </div>
                        <div class="field">
                            <label for="email">Email <span class="req" aria-hidden="true">*</span></label>
                            <input class="input<?= $cls('email') ?>" id="email" type="email" name="email" value="<?= e($admin['email']) ?>" required maxlength="190" autocomplete="email">
                            <?= $err('email') ?>
                        </div>
                    </div>
                </section>

                <section class="panel">
                    <h2 class="panel__title">Mot de passe</h2>
                    <?php if ($isEdit): ?>
                        <p class="text-muted">Laissez ces champs vides pour conserver le mot de passe actuel.</p>
                    <?php endif; ?>

                    <?php if ($askCurrent): ?>
                        <div class="field">
                            <label for="current_password">Mot de passe actuel</label>
                            <div class="password-field">
                                <input class="input<?= $cls('current_password') ?>" id="current_password" type="password" name="current_password" maxlength="200" autocomplete="current-password">
                                <button class="password-toggle" type="button" data-toggle-password="current_password" aria-label="Afficher le mot de passe"><?= icon('eye') ?></button>
                            </div>
                            <span class="field__hint">Obligatoire uniquement si vous changez de mot de passe.</span>
                            <?= $err('current_password') ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="field">
                            <label for="password"><?= $isEdit ? 'Nouveau mot de passe' : 'Mot de passe' ?><?= $isEdit ? '' : ' <span class="req" aria-hidden="true">*</span>' ?></label>
                            <div class="password-field">
                                <input class="input<?= $cls('password') ?>" id="password" type="password" name="password" maxlength="200" autocomplete="new-password"<?= $isEdit ? '' : ' required' ?>>
                                <button class="password-toggle" type="button" data-toggle-password="password" aria-label="Afficher le mot de passe"><?= icon('eye') ?></button>
                            </div>
                            <span class="field__hint">8 caractères minimum, avec au moins une lettre et un chiffre.</span>
                            <?= $err('password') ?>
                        </div>
                        <div class="field">
                            <label for="password_confirm">Confirmer le mot de passe</label>
                            <input class="input<?= $cls('password_confirm') ?>" id="password_confirm" type="password" name="password_confirm" maxlength="200" autocomplete="new-password">
                            <?= $err('password_confirm') ?>
                        </div>
                    </div>
                </section>
            </div>

            <div class="form-layout__side">
                <section class="panel">
                    <h2 class="panel__title">Rôle</h2>
                    <?php if ($canSetRole): ?>
                        <div class="field">
                            <label for="role">Niveau d'accès</label>
                            <select class="select" id="role" name="role">
                                <option value="admin"<?= $admin['role'] === 'admin' ? ' selected' : '' ?>>Administrateur</option>
                                <option value="super_admin"<?= $admin['role'] === 'super_admin' ? ' selected' : '' ?>>Administrateur principal</option>
                            </select>
                            <span class="field__hint">L'administrateur principal gère aussi les comptes et les paramètres du site.</span>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">
                            <?= $admin['role'] === 'super_admin' ? 'Administrateur principal' : 'Administrateur' ?>.
                            Seul l'administrateur principal peut modifier les rôles.
                        </p>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button class="btn btn--primary btn--block" type="submit"><?= icon('check') ?> <?= e($submitLabel) ?></button>
                        <a class="btn btn--ghost btn--block" href="admins.php">Annuler</a>
                    </div>
                </section>
            </div>
        </div>
    </form>
    <?php
}
