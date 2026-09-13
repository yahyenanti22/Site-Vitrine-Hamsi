<?php
/**
 * Gestion des catégories de produits.
 */
require __DIR__ . '/includes/auth.php';

$errors = [];
$form = ['id' => null, 'name' => '', 'label' => '', 'description' => '', 'sort_order' => 0];

// Mode édition : pré-remplissage du formulaire
if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM categories WHERE id = :id');
    $stmt->execute([':id' => (int) $_GET['edit']]);
    $found = $stmt->fetch();
    if ($found) {
        $form = $found;
    }
}

if (is_post()) {
    require_csrf('categories.php');
    $action = post_str('action', 20);

    if ($action === 'delete') {
        $id = ctype_digit(post_str('id', 11)) ? (int) post_str('id', 11) : 0;
        $stmt = db()->prepare('SELECT name FROM categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $name = $stmt->fetchColumn();
        if ($name !== false) {
            // Les produits liés passent automatiquement en « Sans catégorie » (ON DELETE SET NULL)
            db()->prepare('DELETE FROM categories WHERE id = :id')->execute([':id' => $id]);
            flash('success', 'La catégorie « ' . $name . ' » a été supprimée.');
        }
        redirect('categories.php');
    }

    if ($action === 'save') {
        $id = ctype_digit(post_str('id', 11)) ? (int) post_str('id', 11) : null;
        $form = [
            'id'          => $id,
            'name'        => post_str('name', 100),
            'label'       => post_str('label', 100),
            'description' => post_str('description', 2000),
            'sort_order'  => preg_match('/^-?\d{1,5}$/', post_str('sort_order', 6)) ? (int) post_str('sort_order', 6) : 0,
        ];
        if (mb_strlen($form['name']) < 2) {
            $errors['name'] = 'Le nom de la catégorie est obligatoire.';
        }
        if (!$errors) {
            $label = $form['label'] !== '' ? $form['label'] : $form['name'];
            if ($id) {
                $stmt = db()->prepare('UPDATE categories SET name = :name, slug = :slug, label = :label, description = :description, sort_order = :sort WHERE id = :id');
                $stmt->execute([':name' => $form['name'], ':slug' => unique_slug('categories', $form['name'], $id), ':label' => $label,
                    ':description' => $form['description'], ':sort' => $form['sort_order'], ':id' => $id]);
                flash('success', 'La catégorie « ' . $form['name'] . ' » a été mise à jour.');
            } else {
                $stmt = db()->prepare('INSERT INTO categories (name, slug, label, description, sort_order) VALUES (:name, :slug, :label, :description, :sort)');
                $stmt->execute([':name' => $form['name'], ':slug' => unique_slug('categories', $form['name']), ':label' => $label,
                    ':description' => $form['description'], ':sort' => $form['sort_order']]);
                flash('success', 'La catégorie « ' . $form['name'] . ' » a été ajoutée.');
            }
            redirect('categories.php');
        }
    }
}

$categories = db()->query(
    'SELECT c.*, COUNT(p.id) AS product_count
     FROM categories c LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id ORDER BY c.sort_order ASC, c.name ASC'
)->fetchAll();

$adminTitle = 'Catégories';
$activeMenu = 'categories';
require __DIR__ . '/includes/header.php';
?>

<div class="form-layout">
    <div class="form-layout__main">
        <?php if ($categories): ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th scope="col">Nom (filtre)</th><th scope="col">Libellé des cartes</th><th scope="col">Produits</th><th scope="col">Ordre</th><th scope="col" class="t-right">Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr>
                        <td data-label="Nom"><strong><?= e($c['name']) ?></strong><br><small class="text-muted">?categorie=<?= e($c['slug']) ?></small></td>
                        <td data-label="Libellé"><?= e($c['label']) ?></td>
                        <td data-label="Produits"><?= (int) $c['product_count'] ?></td>
                        <td data-label="Ordre"><?= (int) $c['sort_order'] ?></td>
                        <td class="t-right">
                            <div class="row-actions">
                                <a class="icon-action" href="categories.php?edit=<?= (int) $c['id'] ?>" aria-label="Modifier <?= e($c['name']) ?>" title="Modifier"><?= icon('edit') ?></a>
                                <form method="post" action="categories.php" data-confirm="Supprimer la catégorie « <?= e($c['name']) ?> » ?<?= (int) $c['product_count'] > 0 ? ' Ses ' . (int) $c['product_count'] . ' produit(s) seront conservés sans catégorie.' : '' ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                    <button class="icon-action icon-action--danger" type="submit" aria-label="Supprimer <?= e($c['name']) ?>" title="Supprimer"><?= icon('trash') ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="panel empty-panel"><p>Aucune catégorie. Créez-en une avec le formulaire.</p></div>
        <?php endif; ?>
    </div>

    <aside class="form-layout__side">
        <section class="panel">
            <h2 class="panel__title"><?= $form['id'] ? 'Modifier la catégorie' : 'Nouvelle catégorie' ?></h2>
            <form method="post" action="categories.php<?= $form['id'] ? '?edit=' . (int) $form['id'] : '' ?>" class="form-stack" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= e($form['id']) ?>">
                <div class="field<?= isset($errors['name']) ? ' has-error' : '' ?>">
                    <label for="cat-name">Nom <span class="req">*</span></label>
                    <input id="cat-name" type="text" name="name" value="<?= e($form['name']) ?>" maxlength="100" placeholder="Purées" required>
                    <?php if (isset($errors['name'])): ?><p class="field__error"><?= e($errors['name']) ?></p><?php endif; ?>
                </div>
                <div class="field">
                    <label for="cat-label">Libellé affiché sur les cartes</label>
                    <input id="cat-label" type="text" name="label" value="<?= e($form['label']) ?>" maxlength="100" placeholder="Purée bio">
                </div>
                <div class="field">
                    <label for="cat-desc">Description</label>
                    <textarea id="cat-desc" name="description" rows="3"><?= e($form['description']) ?></textarea>
                </div>
                <div class="field">
                    <label for="cat-order">Ordre d'affichage</label>
                    <input id="cat-order" type="number" name="sort_order" value="<?= (int) $form['sort_order'] ?>" min="-9999" max="9999">
                </div>
                <button class="btn btn--primary btn--block" type="submit"><?= icon('check') ?> <?= $form['id'] ? 'Enregistrer' : 'Ajouter la catégorie' ?></button>
                <?php if ($form['id']): ?><a class="btn btn--ghost btn--block" href="categories.php">Annuler</a><?php endif; ?>
            </form>
        </section>
    </aside>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
