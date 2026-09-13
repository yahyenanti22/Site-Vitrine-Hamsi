<?php
/**
 * Liste des produits avec recherche et filtres.
 */
require __DIR__ . '/includes/auth.php';

$q = isset($_GET['q']) && is_string($_GET['q']) ? trim(mb_substr($_GET['q'], 0, 100)) : '';
$cat = isset($_GET['categorie']) && is_string($_GET['categorie']) && ctype_digit($_GET['categorie']) ? (int) $_GET['categorie'] : 0;
$statut = isset($_GET['statut']) && in_array($_GET['statut'], ['actif', 'inactif'], true) ? $_GET['statut'] : '';

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(p.name LIKE :q OR p.short_description LIKE :q2)';
    $params[':q'] = '%' . $q . '%';
    $params[':q2'] = '%' . $q . '%';
}
if ($cat > 0) {
    $where[] = 'p.category_id = :cat';
    $params[':cat'] = $cat;
}
if ($statut !== '') {
    $where[] = 'p.status = :statut';
    $params[':statut'] = $statut;
}

$sql = 'SELECT p.id, p.name, p.slug, p.image, p.price, p.format, p.age_label, p.status, p.is_featured, p.sort_order, c.name AS category_name
        FROM products p LEFT JOIN categories c ON c.id = p.category_id'
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . ' ORDER BY p.sort_order ASC, p.id ASC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = db()->query('SELECT id, name FROM categories ORDER BY sort_order, name')->fetchAll();
$total = (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn();

$adminTitle = 'Produits';
$activeMenu = 'produits';
require __DIR__ . '/includes/header.php';
?>

<div class="page-actions">
    <p class="text-muted"><?= count($products) ?> produit<?= count($products) > 1 ? 's' : '' ?> affiché<?= count($products) > 1 ? 's' : '' ?> sur <?= $total ?></p>
    <a class="btn btn--primary" href="produit-ajouter.php"><?= icon('plus') ?> Ajouter un produit</a>
</div>

<form class="filters" method="get" action="produits.php" role="search">
    <div class="filters__search">
        <?= icon('search') ?>
        <label class="visually-hidden" for="q">Rechercher</label>
        <input id="q" type="search" name="q" value="<?= e($q) ?>" placeholder="Rechercher un produit…">
    </div>
    <label class="visually-hidden" for="f-cat">Catégorie</label>
    <select id="f-cat" name="categorie">
        <option value="">Toutes les catégories</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>"<?= $cat === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <label class="visually-hidden" for="f-statut">Statut</label>
    <select id="f-statut" name="statut">
        <option value="">Tous les statuts</option>
        <option value="actif"<?= $statut === 'actif' ? ' selected' : '' ?>>Actifs</option>
        <option value="inactif"<?= $statut === 'inactif' ? ' selected' : '' ?>>Inactifs</option>
    </select>
    <button class="btn btn--soft btn--sm" type="submit">Filtrer</button>
    <?php if ($q !== '' || $cat || $statut !== ''): ?><a class="link-small" href="produits.php">Réinitialiser</a><?php endif; ?>
</form>

<?php if ($products): ?>
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Produit</th>
                <th scope="col">Catégorie</th>
                <th scope="col">Format</th>
                <th scope="col">Prix</th>
                <th scope="col">Statut</th>
                <th scope="col" class="t-right">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td data-label="Produit">
                    <div class="cell-product">
                        <img class="thumb" src="<?= e(media_url($p['image'])) ?>" alt="" width="56" height="56" loading="lazy">
                        <div>
                            <a class="cell-product__name" href="produit-modifier.php?id=<?= (int) $p['id'] ?>"><?= e($p['name']) ?></a>
                            <small><?= e($p['age_label']) ?><?php if ((int) $p['is_featured'] === 1): ?> · <span class="featured-flag"><?= icon('star') ?> Produit phare</span><?php endif; ?></small>
                        </div>
                    </div>
                </td>
                <td data-label="Catégorie"><?= e($p['category_name'] ?? '—') ?></td>
                <td data-label="Format"><?= e($p['format'] !== '' ? $p['format'] : '—') ?></td>
                <td data-label="Prix"><?= e($p['price'] !== null ? format_price($p['price']) : '—') ?></td>
                <td data-label="Statut"><span class="status status--<?= e($p['status']) ?>"><?= $p['status'] === 'actif' ? 'Actif' : 'Inactif' ?></span></td>
                <td class="t-right">
                    <div class="row-actions">
                        <?php if ($p['status'] === 'actif'): ?>
                            <a class="icon-action" href="../produit.php?slug=<?= e(rawurlencode($p['slug'])) ?>" target="_blank" rel="noopener" aria-label="Voir <?= e($p['name']) ?> sur le site" title="Voir sur le site"><?= icon('external') ?></a>
                        <?php endif; ?>
                        <a class="icon-action" href="produit-modifier.php?id=<?= (int) $p['id'] ?>" aria-label="Modifier <?= e($p['name']) ?>" title="Modifier"><?= icon('edit') ?></a>
                        <form method="post" action="produit-supprimer.php" data-confirm="Supprimer définitivement le produit « <?= e($p['name']) ?> » et son image ?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button class="icon-action icon-action--danger" type="submit" aria-label="Supprimer <?= e($p['name']) ?>" title="Supprimer"><?= icon('trash') ?></button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="panel empty-panel">
        <span class="stat-card__icon stat-card__icon--mint"><?= icon('package') ?></span>
        <p><?= $total === 0 ? 'Aucun produit pour l\'instant.' : 'Aucun produit ne correspond à ces filtres.' ?></p>
        <a class="btn btn--primary btn--sm" href="<?= $total === 0 ? 'produit-ajouter.php' : 'produits.php' ?>"><?= $total === 0 ? 'Ajouter un produit' : 'Voir tous les produits' ?></a>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
