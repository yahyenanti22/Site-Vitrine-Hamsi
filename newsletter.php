<?php
/**
 * Inscription à la newsletter (formulaire du pied de page).
 * Accepte uniquement des requêtes POST, puis redirige vers la page d'origine.
 */
require __DIR__ . '/includes/bootstrap.php';

// Page de retour limitée aux pages publiques connues (évite les redirections ouvertes)
$allowedReturns = ['index.php', 'produits.php', 'produit.php', 'apropos.php', 'engagement.php', 'contact.php'];
$return = post_str('return', 40);
$returnUrl = (in_array($return, $allowedReturns, true) && $return !== 'produit.php' ? $return : 'index.php') . '#newsletter';

if (!is_post()) {
    redirect('index.php');
}

if (!csrf_verify()) {
    flash('error', 'La session a expiré. Réessayez.', 'newsletter');
    redirect($returnUrl);
}

// Champ piège anti-robot
if (post_str('website') !== '') {
    flash('success', 'Merci, votre inscription est confirmée.', 'newsletter');
    redirect($returnUrl);
}

$email = mb_strtolower(post_str('email', 190));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Indiquez une adresse email valide.', 'newsletter');
    redirect($returnUrl);
}

$stmt = db()->prepare('INSERT IGNORE INTO newsletter_subscribers (email) VALUES (:email)');
$stmt->execute([':email' => $email]);

flash('success', $stmt->rowCount() > 0
    ? 'Merci, votre inscription est confirmée.'
    : 'Cette adresse est déjà inscrite à notre newsletter.', 'newsletter');
redirect($returnUrl);
