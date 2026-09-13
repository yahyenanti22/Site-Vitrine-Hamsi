<?php
require __DIR__ . '/includes/bootstrap.php';

$errors = [];
$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if (is_post()) {
    $old = [
        'name'    => post_str('name', 100),
        'email'   => post_str('email', 190),
        'subject' => post_str('subject', 150),
        'message' => post_str('message', 5000),
    ];

    if (!csrf_verify()) {
        $errors['global'] = 'La session a expiré. Rechargez la page et renvoyez votre message.';
    } elseif (post_str('website') !== '') {
        // Champ piège rempli : envoi automatique (robot). On fait comme si tout allait bien.
        flash('success', 'Merci ! Votre message a bien été envoyé.', 'contact');
        redirect('contact.php#formulaire');
    } elseif (isset($_SESSION['last_contact_at']) && time() - $_SESSION['last_contact_at'] < 30) {
        $errors['global'] = 'Vous venez d\'envoyer un message. Patientez quelques secondes avant d\'en envoyer un autre.';
    } else {
        if (mb_strlen($old['name']) < 2) {
            $errors['name'] = 'Indiquez votre nom (2 caractères minimum).';
        }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Indiquez une adresse email valide.';
        }
        if (mb_strlen($old['message']) < 10) {
            $errors['message'] = 'Votre message doit contenir au moins 10 caractères.';
        }

        if (!$errors) {
            $stmt = db()->prepare(
                'INSERT INTO contacts (name, email, subject, message, ip_address) VALUES (:name, :email, :subject, :message, :ip)'
            );
            $stmt->execute([
                ':name'    => $old['name'],
                ':email'   => $old['email'],
                ':subject' => $old['subject'],
                ':message' => $old['message'],
                ':ip'      => client_ip(),
            ]);
            $_SESSION['last_contact_at'] = time();
            flash('success', 'Merci ! Votre message a bien été envoyé. Notre équipe vous répondra sous 24 heures.', 'contact');
            redirect('contact.php#formulaire');
        }
    }
}

$phone = setting('contact_phone');
$phone2 = setting('contact_phone_2');
$email = setting('contact_email');
$address = setting('contact_address');
$hours = setting('contact_hours');

$pageTitle = 'Contact';
$pageDescription = 'Une question sur nos recettes ou une commande ? Contactez l\'équipe Hamsi par WhatsApp, téléphone ou email.';
$bodyClass = 'page-contact';
require __DIR__ . '/includes/header.php';
?>

<section class="section section--contact-intro">
    <div class="container">
        <span class="badge badge--lavender"><?= icon('heart') ?> Équipe à votre écoute</span>
        <h1 class="page-title">Parlons-nous</h1>
        <p class="page-intro">Une question sur nos recettes, besoin d'un conseil en diversification alimentaire ? Notre équipe est à votre écoute.</p>

        <div class="contact-top">
            <div class="wa-card">
                <span class="wa-card__icon"><?= icon('chat') ?></span>
                <h2 class="wa-card__title">Discutez directement avec nous sur WhatsApp</h2>
                <p>Une réponse instantanée pour toutes vos urgences de parents ou pour passer commande en un clin d'œil.</p>
                <a class="btn btn--white btn--lg" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener"><?= icon('chat') ?> Commander / Poser une question sur WhatsApp</a>
            </div>

            <div class="coords-card">
                <h2 class="coords-card__title">Nos Coordonnées</h2>
                <ul class="coords-list">
                    <?php if ($phone !== '' || $phone2 !== ''): ?>
                        <li>
                            <span class="icon-circle icon-circle--mint icon-circle--sm"><?= icon('phone') ?></span>
                            <span><small>Téléphone</small>
                                <?php if ($phone !== ''): ?><a href="<?= e(tel_href($phone)) ?>"><?= e($phone) ?></a><?php endif; ?>
                                <?php if ($phone2 !== ''): ?><a href="<?= e(tel_href($phone2)) ?>"><?= e($phone2) ?></a><?php endif; ?>
                            </span>
                        </li>
                    <?php endif; ?>
                    <?php if ($email !== ''): ?>
                        <li>
                            <span class="icon-circle icon-circle--mint icon-circle--sm"><?= icon('mail') ?></span>
                            <span><small>Email</small><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($address !== ''): ?>
                        <li>
                            <span class="icon-circle icon-circle--mint icon-circle--sm"><?= icon('map-pin') ?></span>
                            <span><small>Adresse</small><?= e($address) ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
                <?php if ($hours !== ''): ?>
                    <p class="coords-card__hours"><?= icon('clock') ?> <?= e($hours) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="section section--tight" id="formulaire">
    <div class="container">
        <div class="form-card">
            <div class="form-card__form">
                <p class="eyebrow eyebrow--green">Formulaire direct</p>
                <h2 class="form-card__title">Envoyez-nous un message</h2>
                <p class="form-card__lead">Vous préférez l'e-mail ? Remplissez ce formulaire et notre équipe vous répondra sous 24 heures.</p>

                <?php render_flashes('contact'); ?>
                <?php if (isset($errors['global'])): ?>
                    <div class="alert alert--error" role="alert"><?= icon('alert') ?><span><?= e($errors['global']) ?></span></div>
                <?php elseif ($errors): ?>
                    <div class="alert alert--error" role="alert"><?= icon('alert') ?><span>Certains champs sont à corriger.</span></div>
                <?php endif; ?>

                <form class="contact-form" action="contact.php#formulaire" method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="hp-field" aria-hidden="true"><label>Ne pas remplir <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                    <div class="form-row form-row--2">
                        <div class="field<?= isset($errors['name']) ? ' has-error' : '' ?>">
                            <label for="c-name">Votre nom</label>
                            <input id="c-name" type="text" name="name" value="<?= e($old['name']) ?>" placeholder="Ex. Amina Hassan" required maxlength="100" autocomplete="name">
                            <?php if (isset($errors['name'])): ?><p class="field__error"><?= e($errors['name']) ?></p><?php endif; ?>
                        </div>
                        <div class="field<?= isset($errors['email']) ? ' has-error' : '' ?>">
                            <label for="c-email">Votre email</label>
                            <input id="c-email" type="email" name="email" value="<?= e($old['email']) ?>" placeholder="amina@exemple.com" required maxlength="190" autocomplete="email">
                            <?php if (isset($errors['email'])): ?><p class="field__error"><?= e($errors['email']) ?></p><?php endif; ?>
                        </div>
                    </div>
                    <div class="field">
                        <label for="c-subject">Sujet</label>
                        <input id="c-subject" type="text" name="subject" value="<?= e($old['subject']) ?>" placeholder="Conseil diversification, question produit…" maxlength="150">
                    </div>
                    <div class="field<?= isset($errors['message']) ? ' has-error' : '' ?>">
                        <label for="c-message">Message</label>
                        <textarea id="c-message" name="message" rows="5" placeholder="Comment pouvons-nous vous aider ?" required maxlength="5000"><?= e($old['message']) ?></textarea>
                        <?php if (isset($errors['message'])): ?><p class="field__error"><?= e($errors['message']) ?></p><?php endif; ?>
                    </div>
                    <button class="btn btn--primary btn--lg" type="submit">Envoyer le message</button>
                </form>
            </div>

            <figure class="photo-overlay photo-overlay--contact">
                <img class="photo-overlay__img" src="assets/images/contact-accompagnement.jpg" alt="Une maman donne à manger à son bébé dans la cuisine" loading="lazy" width="878" height="832">
                <figcaption class="float-card float-card--quote">
                    <strong>« Un accompagnement bienveillant »</strong>
                    <small>Des recettes conçues avec une nutritionniste spécialiste de la petite enfance, et une équipe qui répond à vos questions.</small>
                </figcaption>
            </figure>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
