<?php
/**
 * Suppression d'un produit (POST + jeton CSRF uniquement) et de son image.
 */
require __DIR__ . '/includes/auth.php';

if (!is_post()) {
    redirect('produits.php');
}
require_csrf('produits.php');

$id = ctype_digit(post_str('id', 11)) ? (int) post_str('id', 11) : 0;
$stmt = db()->prepare('SELECT id, name, image FROM products WHERE id = :id');
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    flash('error', 'Ce produit est introuvable.');
    redirect('produits.php');
}

db()->prepare('DELETE FROM products WHERE id = :id')->execute([':id' => $id]);
delete_upload($product['image']);

flash('success', 'Le produit « ' . $product['name'] . ' » a été supprimé.');
redirect('produits.php');
