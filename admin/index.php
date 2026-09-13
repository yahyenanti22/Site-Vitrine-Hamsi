<?php
/**
 * Tableau de bord : résumé du site.
 */
require __DIR__ . '/includes/auth.php';

$pdo = db();
$stats = [
    'products'        => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'products_active' => (int) $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'actif'")->fetchColumn(),
    'featured'        => (int) $pdo->query("SELECT COUNT(*) FROM products WHERE is_featured = 1 AND status = 'actif'")->fetchColumn(),
    'categories'      => (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
    'admins'          => (int) $pdo->query('SELECT COUNT(*) FROM administrators')->fetchColumn(),
    'messages'        => (int) $pdo->query('SELECT COUNT(*) FROM contacts')->fetchColumn(),
    'messages_new'    => (int) $pdo->query("SELECT COUNT(*) FROM contacts WHERE status = 'nouveau'")->fetchColumn(),
    'subscribers'     => (int) $pdo->query('SELECT COUNT(*) FROM newsletter_subscribers')->fetchColumn(),
];

$recentMessages = $pdo->query('SELECT id, name, email, subject, status, created_at FROM contacts ORDER BY created_at DESC LIMIT 5')->fetchAll();
$recentProducts = $pdo->query(
    'SELECT p.id, p.name, p.image, p.price, p.status, p.updated_at, c.name AS category_name
     FROM products p LEFT JOIN categories c ON c.id = p.category_id
     ORDER BY p.updated_at DESC LIMIT 5'
)->fetchAll();

$adminTitle = 'Tableau de bord';
$activeMenu = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<div class="stat-grid">
    <a class="stat-card" href="produits.php">
        <span class="stat-card__icon stat-card__icon--mint"><?= icon('package') ?></span>
        <span class="stat-card__value"><?= $stats['products'] ?></span>
        <span class="stat-card__label">Produits</span>
        <span class="stat-card__meta"><?= $stats['products_active'] ?> en ligne · <?= $stats['featured'] ?> phares</span>
    </a>
    <a class="stat-card" href="categories.php">
        <span class="stat-card__icon stat-card__icon--slate"><?= icon('tag') ?></span>
        <span class="stat-card__value"><?= $stats['categories'] ?></span>
        <span class="stat-card__label">Catégories</span>
        <span class="stat-card__meta">Filtres de la page produits</span>
    </a>
    <a class="stat-card" href="messages.php">
        <span class="stat-card__icon stat-card__icon--lavender"><?= icon('inbox') ?></span>
        <span class="stat-card__value"><?= $stats['messages'] ?></span>
        <span class="stat-card__label">Messages de contact</span>
        <span class="stat-card__meta"><?= $stats['messages_new'] ?> non lu<?= $stats['messages_new'] > 1 ? 's' : '' ?></span>
    </a>
    <a class="stat-card" href="messages.php?onglet=newsletter">
        <span class="stat-card__icon stat-card__icon--mint"><?= icon('mail') ?></span>
        <span class="stat-card__value"><?= $stats['subscribers'] ?></span>
        <span class="stat-card__label">Abonnés newsletter</span>
        <span class="stat-card__meta">Inscrits depuis le pied de page</span>
    </a>
    <a class="stat-card" href="admins.php">
        <span class="stat-card__icon stat-card__icon--slate"><?= icon('users') ?></span>
        <span class="stat-card__value"><?= $stats['admins'] ?></span>
        <span class="stat-card__label">Administrateurs</span>
        <span class="stat-card__meta">Comptes ayant accès à l'admin</span>
    </a>
</div>

<div class="quick-actions">
    <a class="btn btn--primary" href="produit-ajouter.php"><?= icon('plus') ?> Ajouter un produit</a>
    <a class="btn btn--soft" href="parametres.php"><?= icon('palette') ?> Logo, nom et couleurs</a>
    <a class="btn btn--ghost" href="../index.php" target="_blank" rel="noopener"><?= icon('external') ?> Voir le site</a>
</div>

<div class="panel-grid">
    <section class="panel">
        <div class="panel__head">
            <h2>Derniers messages</h2>
            <a class="link-small" href="messages.php">Tout voir</a>
        </div>
        <?php if ($recentMessages): ?>
            <ul class="list-rows">
                <?php foreach ($recentMessages as $m): ?>
                    <li>
                        <div>
                            <strong><?= e($m['name']) ?></strong>
                            <small><?= e($m['subject'] !== '' ? $m['subject'] : 'Sans sujet') ?> — <?= e(format_date($m['created_at'])) ?></small>
                        </div>
                        <span class="status status--<?= e($m['status']) ?>"><?= $m['status'] === 'nouveau' ? 'Nouveau' : ($m['status'] === 'lu' ? 'Lu' : 'Traité') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty">Aucun message pour le moment. Ils apparaîtront ici dès qu'un visiteur utilisera le formulaire de contact.</p>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="panel__head">
            <h2>Produits modifiés récemment</h2>
            <a class="link-small" href="produits.php">Gérer</a>
        </div>
        <?php if ($recentProducts): ?>
            <ul class="list-rows">
                <?php foreach ($recentProducts as $p): ?>
                    <li>
                        <img class="thumb thumb--sm" src="<?= e(media_url($p['image'])) ?>" alt="" width="44" height="44">
                        <div>
                            <strong><?= e($p['name']) ?></strong>
                            <small><?= e($p['category_name'] ?? 'Sans catégorie') ?> — <?= e(format_price($p['price'])) ?></small>
                        </div>
                        <a class="icon-action" href="produit-modifier.php?id=<?= (int) $p['id'] ?>" aria-label="Modifier <?= e($p['name']) ?>"><?= icon('edit') ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty">Aucun produit. <a href="produit-ajouter.php">Ajoutez votre premier produit</a>.</p>
        <?php endif; ?>
    </section>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
