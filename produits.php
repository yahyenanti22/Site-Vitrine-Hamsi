<?php
require __DIR__ . '/includes/bootstrap.php';

// Catégories pour les filtres
$categories = db()->query('SELECT id, name, slug FROM categories ORDER BY sort_order ASC, name ASC')->fetchAll();

// Filtre par catégorie (paramètre GET validé contre la liste en base)
$activeSlug = isset($_GET['categorie']) && is_string($_GET['categorie']) ? $_GET['categorie'] : '';
$activeCategory = null;
foreach ($categories as $cat) {
    if ($cat['slug'] === $activeSlug) {
        $activeCategory = $cat;
        break;
    }
}

$sql = "SELECT p.*, c.label AS category_label
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.status = 'actif'";
$params = [];
if ($activeCategory) {
    $sql .= ' AND p.category_id = :cat';
    $params[':cat'] = $activeCategory['id'];
}
$sql .= ' ORDER BY p.sort_order ASC, p.id ASC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = $activeCategory ? 'Nos produits — ' . $activeCategory['name'] : 'Nos produits';
$pageDescription = 'Découvrez les purées, compotes et céréales infantiles Hamsi, issues de la biodiversité africaine.';
$bodyClass = 'page-products';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero page-hero--center">
    <div class="container">
        <span class="badge badge--mint badge--lg"><?= icon('leaf') ?> Catalogue authentique</span>
        <h1 class="page-hero__title">Nos produits</h1>
        <p class="page-hero__text">Découvrez nos recettes pensées pour accompagner les petits dans leurs premières découvertes alimentaires.</p>

        <nav class="filter-pills" aria-label="Filtrer par catégorie">
            <a class="pill<?= $activeCategory ? '' : ' is-active' ?>" href="produits.php"<?= $activeCategory ? '' : ' aria-current="true"' ?>>Tous</a>
            <?php foreach ($categories as $cat): ?>
                <?php $isActive = $activeCategory && $activeCategory['id'] === $cat['id']; ?>
                <a class="pill<?= $isActive ? ' is-active' : '' ?>" href="produits.php?categorie=<?= e(rawurlencode($cat['slug'])) ?>"<?= $isActive ? ' aria-current="true"' : '' ?>><?= e($cat['name']) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</section>

<section class="section section--catalog">
    <div class="container">
        <?php if ($products): ?>
            <div class="grid grid--3 grid--catalog">
                <?php foreach ($products as $product) { render_product_card($product); } ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>Aucun produit n'est disponible dans cette catégorie pour le moment.</p>
                <a class="btn btn--primary btn--sm" href="produits.php">Voir tous les produits</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
