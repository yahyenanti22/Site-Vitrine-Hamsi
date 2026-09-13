<?php
/**
 * Fonctions partagées par produit-ajouter.php et produit-modifier.php.
 */
defined('HAMSI') || exit('Accès interdit');

const PRODUCT_IMAGE_MAX = 2 * 1024 * 1024; // 2 Mo

/** Valeurs par défaut d'un nouveau produit. */
function product_defaults(): array
{
    return [
        'id' => null, 'category_id' => null, 'name' => '', 'short_description' => '', 'description' => '',
        'age_label' => '', 'format' => '', 'price' => '', 'image' => null, 'ingredients' => '',
        'nutrition_info' => '', 'format_info' => '', 'tasting_tips' => '', 'is_featured' => 0,
        'status' => 'actif', 'sort_order' => 0,
    ];
}

function product_categories(): array
{
    return db()->query('SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC')->fetchAll();
}

/**
 * Valide les données envoyées par le formulaire produit.
 * @return array [données nettoyées, erreurs]
 */
function product_validate(): array
{
    $errors = [];
    $data = [
        'name'              => post_str('name', 150),
        'category_id'       => post_str('category_id', 11),
        'age_label'         => post_str('age_label', 50),
        'format'            => post_str('format', 50),
        'price'             => str_replace([',', ' '], ['.', ''], post_str('price', 20)),
        'short_description' => post_str('short_description', 255),
        'description'       => post_str('description', 10000),
        'ingredients'       => post_str('ingredients', 5000),
        'nutrition_info'    => post_str('nutrition_info', 5000),
        'format_info'       => post_str('format_info', 5000),
        'tasting_tips'      => post_str('tasting_tips', 5000),
        'is_featured'       => isset($_POST['is_featured']) ? 1 : 0,
        'status'            => post_str('status', 10),
        'sort_order'        => post_str('sort_order', 6),
    ];

    if (mb_strlen($data['name']) < 2) {
        $errors['name'] = 'Le nom du produit est obligatoire (2 caractères minimum).';
    }
    if ($data['short_description'] === '') {
        $errors['short_description'] = 'Ajoutez une courte description (affichée sur les cartes produits).';
    }

    // Catégorie : vide ou identifiant existant
    if ($data['category_id'] === '') {
        $data['category_id'] = null;
    } elseif (!ctype_digit($data['category_id'])) {
        $errors['category_id'] = 'Catégorie invalide.';
    } else {
        $stmt = db()->prepare('SELECT COUNT(*) FROM categories WHERE id = :id');
        $stmt->execute([':id' => (int) $data['category_id']]);
        if ((int) $stmt->fetchColumn() === 0) {
            $errors['category_id'] = 'Cette catégorie n\'existe plus.';
        }
        $data['category_id'] = (int) $data['category_id'];
    }

    // Prix : facultatif, nombre positif avec 2 décimales maximum
    if ($data['price'] === '') {
        $data['price'] = null;
    } elseif (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $data['price'])) {
        $errors['price'] = 'Prix invalide. Exemple : 3,50';
    }

    if (!in_array($data['status'], ['actif', 'inactif'], true)) {
        $errors['status'] = 'Statut invalide.';
    }

    $data['sort_order'] = preg_match('/^-?\d{1,5}$/', $data['sort_order']) ? (int) $data['sort_order'] : 0;

    return [$data, $errors];
}

/** Formulaire produit (ajout et modification). */
function render_product_form(array $product, array $errors, array $categories, string $submitLabel): void
{
    $err = fn(string $k) => isset($errors[$k]) ? '<p class="field__error">' . e($errors[$k]) . '</p>' : '';
    $cls = fn(string $k) => isset($errors[$k]) ? ' has-error' : '';
    $priceValue = $product['price'] === null || $product['price'] === '' ? '' : str_replace('.', ',', (string) $product['price']);
    ?>
    <form method="post" enctype="multipart/form-data" class="form-layout" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= PRODUCT_IMAGE_MAX ?>">

        <div class="form-layout__main">
            <section class="panel">
                <h2 class="panel__title">Informations principales</h2>
                <div class="field<?= $cls('name') ?>">
                    <label for="name">Nom du produit <span class="req">*</span></label>
                    <input id="name" type="text" name="name" value="<?= e($product['name']) ?>" required maxlength="150">
                    <?= $err('name') ?>
                </div>
                <div class="form-row form-row--3">
                    <div class="field<?= $cls('category_id') ?>">
                        <label for="category_id">Catégorie</label>
                        <select id="category_id" name="category_id">
                            <option value="">— Sans catégorie —</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= (int) $c['id'] ?>"<?= (string) $product['category_id'] === (string) $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('category_id') ?>
                    </div>
                    <div class="field">
                        <label for="age_label">Âge conseillé</label>
                        <input id="age_label" type="text" name="age_label" value="<?= e($product['age_label']) ?>" maxlength="50" placeholder="Dès 6 mois">
                    </div>
                    <div class="field">
                        <label for="format">Format</label>
                        <input id="format" type="text" name="format" value="<?= e($product['format']) ?>" maxlength="50" placeholder="200g ou 4 x 100g">
                    </div>
                </div>
                <div class="field<?= $cls('short_description') ?>">
                    <label for="short_description">Description courte <span class="req">*</span></label>
                    <input id="short_description" type="text" name="short_description" value="<?= e($product['short_description']) ?>" maxlength="255" required>
                    <p class="field__hint">Affichée sur les cartes produits (255 caractères maximum).</p>
                    <?= $err('short_description') ?>
                </div>
                <div class="field">
                    <label for="description">Description détaillée</label>
                    <textarea id="description" name="description" rows="4"><?= e($product['description']) ?></textarea>
                    <p class="field__hint">Affichée sur la fiche produit. Laissez vide pour reprendre la description courte.</p>
                </div>
            </section>

            <section class="panel">
                <h2 class="panel__title">Onglets de la fiche produit</h2>
                <div class="field">
                    <label for="ingredients">Ingrédients</label>
                    <textarea id="ingredients" name="ingredients" rows="4" placeholder="Patate douce bio | Récolte locale - 60%&#10;Carottes tendres | Récolte locale - 38%"><?= e($product['ingredients']) ?></textarea>
                    <p class="field__hint">Un ingrédient par ligne. Détail facultatif après une barre verticale « | ».</p>
                </div>
                <div class="field">
                    <label for="nutrition_info">Informations nutritionnelles</label>
                    <textarea id="nutrition_info" name="nutrition_info" rows="3"><?= e($product['nutrition_info']) ?></textarea>
                </div>
                <div class="field">
                    <label for="format_info">Format &amp; quantité (complément)</label>
                    <textarea id="format_info" name="format_info" rows="2"><?= e($product['format_info']) ?></textarea>
                </div>
                <div class="field">
                    <label for="tasting_tips">Conseils de dégustation</label>
                    <textarea id="tasting_tips" name="tasting_tips" rows="3"><?= e($product['tasting_tips']) ?></textarea>
                </div>
            </section>
        </div>

        <aside class="form-layout__side">
            <section class="panel">
                <h2 class="panel__title">Image</h2>
                <div class="image-picker<?= $cls('image') ?>">
                    <img class="image-picker__preview" id="image-preview" src="<?= e(media_url($product['image'])) ?>" alt="Aperçu de l'image du produit" width="320" height="240">
                    <label class="btn btn--soft btn--sm btn--block" for="image"><?= icon('image') ?> <?= $product['image'] ? 'Remplacer l\'image' : 'Choisir une image' ?></label>
                    <input class="visually-hidden" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-preview="image-preview">
                    <p class="field__hint" data-file-name>JPG, PNG ou WEBP — 2 Mo maximum.</p>
                    <?= $err('image') ?>
                    <?php if (!empty($product['id']) && !empty($product['image'])): ?>
                        <label class="check"><input type="checkbox" name="remove_image" value="1"> Supprimer l'image actuelle</label>
                    <?php endif; ?>
                </div>
            </section>

            <section class="panel">
                <h2 class="panel__title">Publication</h2>
                <div class="field<?= $cls('price') ?>">
                    <label for="price">Prix (<?= e(setting('currency', '€')) ?>)</label>
                    <input id="price" type="text" inputmode="decimal" name="price" value="<?= e($priceValue) ?>" placeholder="3,50" maxlength="12">
                    <?= $err('price') ?>
                </div>
                <div class="field<?= $cls('status') ?>">
                    <label for="status">Statut</label>
                    <select id="status" name="status">
                        <option value="actif"<?= $product['status'] === 'actif' ? ' selected' : '' ?>>Actif — visible sur le site</option>
                        <option value="inactif"<?= $product['status'] === 'inactif' ? ' selected' : '' ?>>Inactif — masqué</option>
                    </select>
                </div>
                <div class="field">
                    <label for="sort_order">Ordre d'affichage</label>
                    <input id="sort_order" type="number" name="sort_order" value="<?= (int) $product['sort_order'] ?>" min="-9999" max="9999">
                    <p class="field__hint">Les plus petits nombres s'affichent en premier.</p>
                </div>
                <label class="check"><input type="checkbox" name="is_featured" value="1"<?= (int) $product['is_featured'] === 1 ? ' checked' : '' ?>> Afficher dans « Produits phares » (accueil)</label>
            </section>

            <div class="form-actions">
                <button class="btn btn--primary btn--block" type="submit"><?= icon('check') ?> <?= e($submitLabel) ?></button>
                <a class="btn btn--ghost btn--block" href="produits.php">Annuler</a>
            </div>
        </aside>
    </form>
    <?php
}
