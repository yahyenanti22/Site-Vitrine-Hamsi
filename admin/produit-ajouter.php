<?php
/**
 * Ajout d'un produit.
 */
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/product-functions.php';

$categories = product_categories();
$product = product_defaults();
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
            $stmt = db()->prepare(
                'INSERT INTO products (category_id, name, slug, short_description, description, age_label, format, price, image,
                    ingredients, nutrition_info, format_info, tasting_tips, is_featured, status, sort_order)
                 VALUES (:category_id, :name, :slug, :short_description, :description, :age_label, :format, :price, :image,
                    :ingredients, :nutrition_info, :format_info, :tasting_tips, :is_featured, :status, :sort_order)'
            );
            $stmt->execute([
                ':category_id' => $data['category_id'], ':name' => $data['name'], ':slug' => unique_slug('products', $data['name']),
                ':short_description' => $data['short_description'], ':description' => $data['description'],
                ':age_label' => $data['age_label'], ':format' => $data['format'], ':price' => $data['price'],
                ':image' => $upload['path'], ':ingredients' => $data['ingredients'], ':nutrition_info' => $data['nutrition_info'],
                ':format_info' => $data['format_info'], ':tasting_tips' => $data['tasting_tips'],
                ':is_featured' => $data['is_featured'], ':status' => $data['status'], ':sort_order' => $data['sort_order'],
            ]);
            flash('success', 'Le produit « ' . $data['name'] . ' » a été ajouté.');
            redirect('produits.php');
        }
        $product = array_merge($product, $data);
    }
}

$adminTitle = 'Ajouter un produit';
$activeMenu = 'produits';
require __DIR__ . '/includes/header.php';
?>
<a class="back-link" href="produits.php"><?= icon('arrow-left') ?> Retour aux produits</a>
<?php if (isset($errors['global'])): ?>
    <div class="alert alert--error" role="alert"><?= icon('alert') ?><span><?= e($errors['global']) ?></span></div>
<?php elseif ($errors): ?>
    <div class="alert alert--error" role="alert"><?= icon('alert') ?><span>Le produit n'a pas été enregistré : corrigez les champs signalés.</span></div>
<?php endif; ?>
<?php render_product_form($product, $errors, $categories, 'Ajouter le produit'); ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
