<?php
/**
 * Composants HTML réutilisables (générés côté serveur).
 */
defined('HAMSI') || exit('Accès interdit');

/** URL de la fiche d'un produit. */
function product_url(array $p): string
{
    return url('produit.php?slug=' . rawurlencode((string) $p['slug']));
}

/** Lien WhatsApp de commande pour un produit. */
function product_order_url(array $p): string
{
    $details = array_filter([(string) ($p['format'] ?? ''), format_price($p['price'] ?? null)]);
    $msg = 'Bonjour ' . setting('site_name', 'Hamsi') . ', je souhaite commander : ' . $p['name']
        . ($details ? ' (' . implode(' - ', $details) . ')' : '') . '.';
    return whatsapp_url($msg);
}

/** Carte du catalogue (page « Nos produits »). */
function render_product_card(array $p): void
{
    $ingredients = array_slice(parse_ingredients($p['ingredients'] ?? ''), 0, 3);
    ?>
    <article class="product-card">
        <a class="product-card__media" href="<?= e(product_url($p)) ?>" tabindex="-1" aria-hidden="true">
            <img src="<?= e(media_url($p['image'])) ?>" alt="" loading="lazy" width="400" height="300">
            <?php if (!empty($p['age_label'])): ?><span class="badge badge--light product-card__age"><?= e($p['age_label']) ?></span><?php endif; ?>
        </a>
        <div class="product-card__body">
            <?php if (!empty($p['category_label'])): ?><p class="product-card__cat"><?= e($p['category_label']) ?></p><?php endif; ?>
            <h3 class="product-card__title"><a href="<?= e(product_url($p)) ?>"><?= e($p['name']) ?></a></h3>
            <p class="product-card__desc"><?= e($p['short_description']) ?></p>
            <?php if ($ingredients): ?>
                <ul class="chip-list" aria-label="Ingrédients">
                    <?php foreach ($ingredients as $ing): ?><li class="chip chip--outline"><?= e($ing['name']) ?></li><?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <div class="product-card__meta">
                <span class="product-card__format"><?= $p['format'] !== '' ? 'Format : ' . e($p['format']) : '' ?></span>
                <span class="product-card__price"><?= e(format_price($p['price'])) ?></span>
            </div>
            <div class="product-card__actions">
                <a class="btn btn--soft btn--sm" href="<?= e(product_url($p)) ?>">Voir le produit</a>
                <a class="btn btn--primary btn--sm" href="<?= e(product_order_url($p)) ?>" target="_blank" rel="noopener"><?= icon('chat') ?> Commander</a>
            </div>
        </div>
    </article>
    <?php
}

/** Carte « Produits phares » (page d'accueil). */
function render_featured_card(array $p): void
{
    ?>
    <article class="featured-card">
        <a class="featured-card__media" href="<?= e(product_url($p)) ?>" tabindex="-1" aria-hidden="true">
            <img src="<?= e(media_url($p['image'])) ?>" alt="" loading="lazy" width="400" height="260">
        </a>
        <div class="featured-card__body">
            <?php if (!empty($p['age_label'])): ?><span class="badge badge--mint"><?= e($p['age_label']) ?></span><?php endif; ?>
            <h3 class="featured-card__title"><a href="<?= e(product_url($p)) ?>"><?= e($p['name']) ?></a></h3>
            <p class="featured-card__desc"><?= e($p['short_description']) ?></p>
            <div class="featured-card__footer">
                <a class="link-muted" href="<?= e(product_url($p)) ?>">Voir le produit</a>
                <a class="btn btn--primary btn--xs" href="<?= e(product_order_url($p)) ?>" target="_blank" rel="noopener"><?= icon('chat') ?> Commander</a>
            </div>
        </div>
    </article>
    <?php
}

/** Carte « Produits similaires » (fiche produit). */
function render_similar_card(array $p): void
{
    ?>
    <article class="similar-card">
        <a class="similar-card__media" href="<?= e(product_url($p)) ?>" tabindex="-1" aria-hidden="true">
            <img src="<?= e(media_url($p['image'])) ?>" alt="" loading="lazy" width="400" height="230">
            <?php if (!empty($p['age_label'])): ?><span class="badge badge--dark similar-card__age"><?= e($p['age_label']) ?></span><?php endif; ?>
        </a>
        <div class="similar-card__body">
            <h3 class="similar-card__title"><a href="<?= e(product_url($p)) ?>"><?= e($p['name']) ?></a></h3>
            <p class="similar-card__desc"><?= e($p['short_description']) ?></p>
            <div class="similar-card__footer">
                <span class="similar-card__price"><?= e(format_price($p['price'])) ?></span>
                <a class="btn btn--primary btn--sm" href="<?= e(product_url($p)) ?>">Découvrir</a>
            </div>
        </div>
    </article>
    <?php
}

/** Messages flash du site public. */
function render_flashes(string $channel = 'main'): void
{
    foreach (get_flashes($channel) as $f) {
        $type = in_array($f['type'], ['success', 'error', 'info'], true) ? $f['type'] : 'info';
        echo '<div class="alert alert--' . $type . '" role="' . ($type === 'error' ? 'alert' : 'status') . '">'
            . icon($type === 'success' ? 'check-circle' : ($type === 'error' ? 'alert' : 'info'))
            . '<span>' . e($f['message']) . '</span></div>';
    }
}
