<?php
/**
 * Modification d'un produit (y compris le remplacement de son image).
 */
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/product-functions.php';

$id = isset($_GET['id']) && ctype_digit((string) $_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = db()->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute([':id' => $id]);
$existing = $stmt->fetch();
if (!$existing) {
    flash('error', 'Ce produit est introuvable. Il a peut-être été supprimé.');
    redirect('produits.php');
}

$categories = product_categories();
$product = $existing;
$errors = [];

if (post_too_large()) {
    $errors['global'] = 'L\'envoi est trop volumineux. Choisissez une image de 2 Mo maximum.';
} elseif (is_post()) {
    if (!csrf_verify()) {
        $errors['global'] = 'La session a expiré. Renvoyez le formulaire.';
    } else {
        [$data, $errors] = product_validate();
        $upload = ['path' => null, 'error' => null];
        if (!$errors) {
            $upload = handle_image_upload($_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE], 'products', PRODUCT_IMAGE_MAX);
            if ($upload['error']) {
                $errors['image'] = $upload['error'];
            }
        }

        if (!$errors) {
            $newImage = $existing['image'];
            $oldToDelete = null;
            if ($upload['path']) {                       // nouvelle image envoyée
                $newImage = $upload['path'];
                $oldToDelete = $existing['image'];
            } elseif (isset($_POST['remove_image'])) {  // suppression demandée
                $newImage = null;
                $oldToDelete = $existing['image'];
            }

            $slug = $data['name'] !== $existing['name'] ? unique_slug('products', $data['name'], $id) : $existing['slug'];

            $update = db()->prepare(
                'UPDATE products SET category_id = :category_id, name = :name, slug = :slug, short_description = :short_description,
                    description = :description, age_label = :age_label, format = :format, price = :price, image = :image,
                    ingredients = :ingredients, nutrition_info = :nutrition_info, format_info = :format_info,
                    tasting_tips = :tasting_tips, is_featured = :is_featured, status = :status, sort_order = :sort_order
                 WHERE id = :id'
            );
            $update->execute([
                ':category_id' => $data['category_id'], ':name' => $data['name'], ':slug' => $slug,
                ':short_description' => $data['short_description'], ':description' => $data['description'],
                ':age_label' => $data['age_label'], ':format' => $data['format'], ':price' => $data['price'],
                ':image' => $newImage, ':ingredients' => $data['ingredients'], ':nutrition_info' => $data['nutrition_info'],
                ':format_info' => $data['format_info'], ':tasting_tips' => $data['tasting_tips'],
                ':is_featured' => $data['is_featured'], ':status' => $data['status'], ':sort_order' => $data['sort_order'],
                ':id' => $id,
            ]);

            // L'ancienne image n'est supprimée qu'après la mise à jour réussie de la base
            if ($oldToDelete && $oldToDelete !== $newImage) {
                delete_upload($oldToDelete);
            }

            flash('success', 'Le produit « ' . $data['name'] . ' » a été mis à jour.');
            redirect('produits.php');
        }
        $product = array_merge($existing, $data);
    }
}

$adminTitle = 'Modifier : ' . $existing['name'];
$activeMenu = 'produits';
require __DIR__ . '/includes/header.php';
?>
<div class="page-actions">
    <a class="back-link" href="produits.php"><?= icon('arrow-left') ?> Retour aux produits</a>
    <?php if ($existing['status'] === 'actif'): ?>
        <a class="btn btn--ghost btn--sm" href="../produit.php?slug=<?= e(rawurlencode($existing['slug'])) ?>" target="_blank" rel="noopener"><?= icon('external') ?> Voir sur le site</a>
    <?php endif; ?>
</div>
<?php if (isset($errors['global'])): ?>
    <div class="alert alert--error" role="alert"><?= icon('alert') ?><span><?= e($errors['global']) ?></span></div>
<?php elseif ($errors): ?>
    <div class="alert alert--error" role="alert"><?= icon('alert') ?><span>Les modifications n'ont pas été enregistrées : corrigez les champs signalés.</span></div>
<?php endif; ?>
<?php render_product_form($product, $errors, $categories, 'Enregistrer les modifications'); ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
