<?php
/**
 * Messages reçus depuis le formulaire de contact du site public.
 */
require __DIR__ . '/includes/auth.php';

// ------------------------------------------------------------------
// Actions : marquer comme lu / traité / non lu, ou supprimer
// ------------------------------------------------------------------
if (is_post()) {
    require_csrf('messages.php');

    $id = isset($_POST['id']) && ctype_digit((string) $_POST['id']) ? (int) $_POST['id'] : 0;
    $action = post_str('action', 20);

    if ($id > 0 && $action === 'supprimer') {
        db()->prepare('DELETE FROM contacts WHERE id = :id')->execute([':id' => $id]);
        flash('success', 'Le message a été supprimé.');
    } elseif ($id > 0 && in_array($action, ['nouveau', 'lu', 'traite'], true)) {
        db()->prepare('UPDATE contacts SET status = :s WHERE id = :id')->execute([':s' => $action, ':id' => $id]);
        $labels = ['nouveau' => 'non lu', 'lu' => 'lu', 'traite' => 'traité'];
        flash('success', 'Le message est marqué comme ' . $labels[$action] . '.');
    } elseif ($action === 'tout_lire') {
        db()->exec("UPDATE contacts SET status = 'lu' WHERE status = 'nouveau'");
        flash('success', 'Tous les nouveaux messages sont marqués comme lus.');
    } else {
        flash('error', 'Action inconnue.');
    }
    redirect('messages.php' . ($action !== 'tout_lire' && isset($_POST['filtre']) && is_string($_POST['filtre']) && $_POST['filtre'] !== ''
        ? '?statut=' . rawurlencode($_POST['filtre']) : ''));
}

// ------------------------------------------------------------------
// Liste filtrée
// ------------------------------------------------------------------
$statut = isset($_GET['statut']) && in_array($_GET['statut'], ['nouveau', 'lu', 'traite'], true) ? $_GET['statut'] : '';

$sql = 'SELECT * FROM contacts' . ($statut !== '' ? ' WHERE status = :s' : '') . ' ORDER BY created_at DESC, id DESC LIMIT 200';
$stmt = db()->prepare($sql);
$stmt->execute($statut !== '' ? [':s' => $statut] : []);
$messages = $stmt->fetchAll();

$counts = ['total' => 0, 'nouveau' => 0, 'lu' => 0, 'traite' => 0];
foreach (db()->query('SELECT status, COUNT(*) AS n FROM contacts GROUP BY status')->fetchAll() as $row) {
    $counts[$row['status']] = (int) $row['n'];
    $counts['total'] += (int) $row['n'];
}

$statusLabels = ['nouveau' => 'Nouveau', 'lu' => 'Lu', 'traite' => 'Traité'];

$adminTitle = 'Messages';
$activeMenu = 'messages';
require __DIR__ . '/includes/header.php';
?>

<div class="page-actions">
    <p class="text-muted">
        <?= count($messages) ?> message<?= count($messages) > 1 ? 's' : '' ?> affiché<?= count($messages) > 1 ? 's' : '' ?>
        sur <?= (int) $counts['total'] ?> · <?= (int) $counts['nouveau'] ?> non lu<?= $counts['nouveau'] > 1 ? 's' : '' ?>
    </p>
    <?php if ($counts['nouveau'] > 0): ?>
        <form method="post" action="messages.php" data-confirm="Marquer tous les nouveaux messages comme lus ?">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="tout_lire">
            <button class="btn btn--soft btn--sm" type="submit"><?= icon('check') ?> Tout marquer comme lu</button>
        </form>
    <?php endif; ?>
</div>

<form class="filters" method="get" action="messages.php">
    <label class="visually-hidden" for="f-statut">Statut</label>
    <select id="f-statut" name="statut">
        <option value="">Tous les messages (<?= (int) $counts['total'] ?>)</option>
        <option value="nouveau"<?= $statut === 'nouveau' ? ' selected' : '' ?>>Non lus (<?= (int) $counts['nouveau'] ?>)</option>
        <option value="lu"<?= $statut === 'lu' ? ' selected' : '' ?>>Lus (<?= (int) $counts['lu'] ?>)</option>
        <option value="traite"<?= $statut === 'traite' ? ' selected' : '' ?>>Traités (<?= (int) $counts['traite'] ?>)</option>
    </select>
    <button class="btn btn--soft btn--sm" type="submit">Filtrer</button>
    <?php if ($statut !== ''): ?><a class="link-small" href="messages.php">Réinitialiser</a><?php endif; ?>
</form>

<?php if ($messages): ?>
    <?php foreach ($messages as $m): ?>
        <article class="message-card message-card--<?= e($m['status']) ?>">
            <header class="message-card__head">
                <div class="message-card__who">
                    <strong><?= e($m['name']) ?></strong>
                    <small>
                        <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>
                        <?php if (!empty($m['phone'])): ?> · <a href="<?= e(tel_href($m['phone'])) ?>"><?= e($m['phone']) ?></a><?php endif; ?>
                    </small>
                </div>
                <div>
                    <span class="status status--<?= e($m['status']) ?>"><?= e($statusLabels[$m['status']] ?? $m['status']) ?></span>
                    <span class="message-card__date"><?= e(format_date($m['created_at'])) ?></span>
                </div>
            </header>

            <?php if (trim((string) $m['subject']) !== ''): ?>
                <p class="message-card__subject">Sujet : <?= e($m['subject']) ?></p>
            <?php endif; ?>
            <div class="message-card__body"><?= e($m['message']) ?></div>

            <div class="message-card__actions">
                <a class="btn btn--soft btn--sm" href="mailto:<?= e($m['email']) ?>?subject=<?= e(rawurlencode('Re : ' . ($m['subject'] !== '' ? $m['subject'] : 'Votre message'))) ?>"><?= icon('send') ?> Répondre par email</a>

                <?php foreach (['lu' => 'Marquer comme lu', 'traite' => 'Marquer comme traité', 'nouveau' => 'Marquer comme non lu'] as $value => $label): ?>
                    <?php if ($m['status'] !== $value): ?>
                        <form method="post" action="messages.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                            <input type="hidden" name="action" value="<?= e($value) ?>">
                            <input type="hidden" name="filtre" value="<?= e($statut) ?>">
                            <button class="btn btn--ghost btn--sm" type="submit"><?= $label ?></button>
                        </form>
                    <?php endif; ?>
                <?php endforeach; ?>

                <form method="post" action="messages.php" data-confirm="Supprimer définitivement le message de <?= e($m['name']) ?> ?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                    <input type="hidden" name="action" value="supprimer">
                    <input type="hidden" name="filtre" value="<?= e($statut) ?>">
                    <button class="icon-action icon-action--danger" type="submit" aria-label="Supprimer le message de <?= e($m['name']) ?>" title="Supprimer"><?= icon('trash') ?></button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
<?php else: ?>
    <div class="panel empty-panel">
        <span class="stat-card__icon stat-card__icon--lavender"><?= icon('inbox') ?></span>
        <p><?= $counts['total'] === 0 ? 'Aucun message reçu pour l\'instant. Les messages envoyés depuis la page Contact apparaîtront ici.' : 'Aucun message avec ce statut.' ?></p>
        <?php if ($counts['total'] > 0): ?><a class="btn btn--soft btn--sm" href="messages.php">Voir tous les messages</a><?php endif; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
