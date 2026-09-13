<?php
require __DIR__ . '/includes/bootstrap.php';

$slug = isset($_GET['slug']) && is_string($_GET['slug']) ? $_GET['slug'] : '';

$stmt = db()->prepare(
    "SELECT p.*, c.name AS category_name, c.label AS category_label
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.slug = :slug AND p.status = 'actif'
     LIMIT 1"
);
$stmt->execute([':slug' => $slug]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Produit introuvable';
    $bodyClass = 'page-product';
    require __DIR__ . '/includes/header.php';
    ?>
    <section class="section">
        <div class="container empty-state empty-state--page">
            <span class="icon-circle icon-circle--mint icon-circle--lg"><?= icon('package') ?></span>
            <h1 class="section-title">Ce produit n'est plus disponible</h1>
            <p>Il a peut-être été retiré du catalogue. Découvrez nos autres recettes.</p>
            <a class="btn btn--primary" href="produits.php"><?= icon('arrow-left') ?> Retour aux produits</a>
        </div>
    </section>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Produits similaires : même catégorie d'abord, complétés par d'autres produits actifs
$similarStmt = db()->prepare(
    "SELECT p.*, c.label AS category_label
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.status = 'actif' AND p.id <> :id
     ORDER BY (p.category_id <=> :cat) DESC, p.sort_order ASC, p.id ASC
     LIMIT 3"
);
$similarStmt->execute([':id' => $product['id'], ':cat' => $product['category_id']]);
$similar = $similarStmt->fetchAll();

$ingredients = parse_ingredients($product['ingredients']);
$ingredientIcons = ['leaf', 'sprout', 'droplet', 'apple', 'wheat'];

$pageTitle = $product['name'];
$pageDescription = $product['short_description'];
$bodyClass = 'page-product';
require __DIR__ . '/includes/header.php';
?>

<section class="section section--product">
    <div class="container">
        <a class="back-link" href="produits.php"><?= icon('arrow-left') ?> Retour aux produits</a>

        <div class="product-detail">
            <div class="product-detail__media">
                <img src="<?= e(media_url($product['image'])) ?>" alt="<?= e($product['name']) ?>" width="860" height="856">
                <span class="badge badge--dark product-detail__badge-top"><?= icon('check-circle') ?> 100% Naturel &amp; Local</span>
                <span class="badge badge--light product-detail__badge-bottom">
                    <span class="brand__logo brand__logo--xs"><img src="<?= e(media_url(setting('logo'), 'assets/images/logo-icon.png')) ?>" alt="" width="22" height="22"></span>
                    Mascotte Hamsi approuvé
                </span>
            </div>

            <div class="product-detail__info">
                <div class="chip-row">
                    <?php if ($product['age_label'] !== ''): ?><span class="badge badge--mint"><?= e($product['age_label']) ?></span><?php endif; ?>
                    <?php if ($product['format'] !== ''): ?><span class="badge badge--slate"><?= e($product['format']) ?></span><?php endif; ?>
                </div>
                <h1 class="product-detail__title"><?= e($product['name']) ?></h1>
                <div class="product-detail__desc">
                    <?= paragraphs($product['description'] !== null && trim($product['description']) !== '' ? $product['description'] : $product['short_description']) ?>
                </div>

                <div class="price-box">
                    <?php if ($product['price'] !== null): ?>
                        <div class="price-box__row">
                            <span>Prix conseillé</span>
                            <strong class="price-box__price"><?= e(format_price($product['price'])) ?></strong>
                        </div>
                    <?php endif; ?>
                    <a class="btn btn--primary btn--block btn--upper" href="<?= e(product_order_url($product)) ?>" target="_blank" rel="noopener"><?= icon('chat') ?> Commander sur WhatsApp</a>
                    <p class="price-box__note">Réponse rapide de notre équipe sur WhatsApp.</p>
                </div>
            </div>
        </div>

        <!-- Onglets d'information (tous visibles sans JavaScript) -->
        <div class="product-tabs" data-tabs>
            <div class="product-tabs__list" role="tablist" aria-label="Informations produit">
                <button class="tab is-active" type="button" role="tab" id="tab-ing" aria-controls="panel-ing" aria-selected="true">Ingrédients</button>
                <button class="tab" type="button" role="tab" id="tab-nut" aria-controls="panel-nut" aria-selected="false" tabindex="-1">Informations nutritionnelles</button>
                <button class="tab" type="button" role="tab" id="tab-fmt" aria-controls="panel-fmt" aria-selected="false" tabindex="-1">Format &amp; Quantité</button>
                <button class="tab" type="button" role="tab" id="tab-tip" aria-controls="panel-tip" aria-selected="false" tabindex="-1">Conseils de dégustation</button>
            </div>

            <div class="product-tabs__panel" role="tabpanel" id="panel-ing" aria-labelledby="tab-ing">
                <h2 class="product-tabs__title">Des ingrédients soigneusement sélectionnés</h2>
                <p>Cette recette est élaborée à partir d'ingrédients sélectionnés auprès de nos producteurs partenaires locaux :</p>
                <?php if ($ingredients): ?>
                    <ul class="ingredient-grid">
                        <?php foreach ($ingredients as $i => $ing): ?>
                            <li class="ingredient">
                                <span class="icon-circle icon-circle--mint icon-circle--sm"><?= icon($ingredientIcons[$i % count($ingredientIcons)]) ?></span>
                                <span><strong><?= e($ing['name']) ?></strong><?php if ($ing['detail'] !== ''): ?><small><?= e($ing['detail']) ?></small><?php endif; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">La liste des ingrédients figure sur l'étiquette du produit.</p>
                <?php endif; ?>
            </div>

            <div class="product-tabs__panel" role="tabpanel" id="panel-nut" aria-labelledby="tab-nut">
                <h2 class="product-tabs__title">Informations nutritionnelles</h2>
                <?php if (trim((string) $product['nutrition_info']) !== ''): ?>
                    <?= paragraphs($product['nutrition_info']) ?>
                <?php else: ?>
                    <p>Les valeurs nutritionnelles détaillées sont indiquées sur l'étiquette de chaque produit. Notre équipe peut aussi vous les communiquer sur simple demande via WhatsApp.</p>
                <?php endif; ?>
            </div>

            <div class="product-tabs__panel" role="tabpanel" id="panel-fmt" aria-labelledby="tab-fmt">
                <h2 class="product-tabs__title">Format &amp; Quantité</h2>
                <?php if ($product['format'] !== ''): ?><p><strong>Format :</strong> <?= e($product['format']) ?><?= $product['age_label'] !== '' ? ' — ' . e(mb_strtolower($product['age_label'])) : '' ?></p><?php endif; ?>
                <?= paragraphs($product['format_info']) ?>
            </div>

            <div class="product-tabs__panel" role="tabpanel" id="panel-tip" aria-labelledby="tab-tip">
                <h2 class="product-tabs__title">Conseils de dégustation</h2>
                <?php if (trim((string) $product['tasting_tips']) !== ''): ?>
                    <?= paragraphs($product['tasting_tips']) ?>
                <?php else: ?>
                    <p>Suivez les indications de préparation et de conservation figurant sur l'emballage.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if ($similar): ?>
<section class="section section--similar">
    <div class="container">
        <div class="section-head">
            <h2 class="section-title section-title--lg">Produits similaires</h2>
            <p class="section-lead">D'autres recettes naturelles pour varier les plaisirs de bébé.</p>
        </div>
        <div class="grid grid--3">
            <?php foreach ($similar as $item) { render_similar_card($item); } ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
