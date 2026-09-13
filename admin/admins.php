<?php
/**
 * Liste des administrateurs.
 */
require __DIR__ . '/includes/auth.php';

$admins = db()->query(
    'SELECT id, name, username, email, role, last_login_at, created_at FROM administrators ORDER BY role ASC, name ASC'
)->fetchAll();

$me = current_admin();

$adminTitle = 'Administrateurs';
$activeMenu = 'admins';
require __DIR__ . '/includes/header.php';
?>

<div class="page-actions">
    <p class="text-muted"><?= count($admins) ?> compte<?= count($admins) > 1 ? 's' : '' ?> · Les mots de passe sont enregistrés sous forme de hash, jamais en clair.</p>
    <?php if (is_super_admin()): ?>
        <a class="btn btn--primary" href="admin-ajouter.php"><?= icon('plus') ?> Ajouter un administrateur</a>
    <?php endif; ?>
</div>

<?php if (!is_super_admin()): ?>
    <div class="alert alert--info" role="status">
        <?= icon('info') ?><span>Seul l'administrateur principal peut créer, modifier ou supprimer des comptes. Vous pouvez modifier vos propres informations.</span>
    </div>
<?php endif; ?>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Administrateur</th>
                <th scope="col">Identifiant</th>
                <th scope="col">Rôle</th>
                <th scope="col">Dernière connexion</th>
                <th scope="col" class="t-right">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($admins as $a): ?>
            <?php $isMe = (int) $a['id'] === (int) $me['id']; ?>
            <tr>
                <td data-label="Administrateur">
                    <div class="cell-product">
                        <span class="topbar__avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($a['name'], 0, 1))) ?></span>
                        <div>
                            <strong><?= e($a['name']) ?><?= $isMe ? ' (vous)' : '' ?></strong>
                            <small><?= e($a['email']) ?></small>
                        </div>
                    </div>
                </td>
                <td data-label="Identifiant"><?= e($a['username']) ?></td>
                <td data-label="Rôle">
                    <span class="tag-pill<?= $a['role'] === 'super_admin' ? '' : ' tag-pill--muted' ?>">
                        <?= $a['role'] === 'super_admin' ? 'Administrateur principal' : 'Administrateur' ?>
                    </span>
                </td>
                <td data-label="Dernière connexion"><?= e(format_date($a['last_login_at'])) ?></td>
                <td class="t-right">
                    <div class="row-actions">
                        <?php if (is_super_admin() || $isMe): ?>
                            <a class="icon-action" href="admin-modifier.php?id=<?= (int) $a['id'] ?>" aria-label="Modifier <?= e($a['name']) ?>" title="Modifier"><?= icon('edit') ?></a>
                        <?php endif; ?>
                        <?php if (is_super_admin() && !$isMe): ?>
                            <form method="post" action="admin-supprimer.php" data-confirm="Supprimer définitivement le compte de <?= e($a['name']) ?> ? Cette action est irréversible.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                <button class="icon-action icon-action--danger" type="submit" aria-label="Supprimer <?= e($a['name']) ?>" title="Supprimer"><?= icon('trash') ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
