<?php
/**
 * Pied de page commun du site public.
 */
defined('HAMSI') || exit('Accès interdit');

$newsletterFlashes = get_flashes('newsletter');
$phone = setting('contact_phone');
$email = setting('contact_email');
$address = setting('contact_address');
?>
</main>

<footer class="site-footer" id="newsletter">
    <div class="container site-footer__grid">
        <div class="site-footer__brand">
            <a class="brand" href="<?= e(url('index.php')) ?>">
                <span class="brand__logo brand__logo--sm"><img src="<?= e(media_url(setting('logo'), 'assets/images/logo-icon.png')) ?>" alt="" width="28" height="28"></span>
                <span class="brand__name"><?= e(setting('site_name', 'HAMSI')) ?></span>
            </a>
            <p><?= e(setting('site_tagline')) ?></p>
        </div>

        <div>
            <h2 class="site-footer__title">Navigation</h2>
            <ul class="site-footer__links">
                <li><a href="<?= e(url('index.php')) ?>">Accueil</a></li>
                <li><a href="<?= e(url('produits.php')) ?>">Nos produits</a></li>
                <li><a href="<?= e(url('apropos.php')) ?>">À propos</a></li>
                <li><a href="<?= e(url('engagement.php')) ?>">Notre engagement</a></li>
                <li><a href="<?= e(url('contact.php')) ?>">Contact</a></li>
            </ul>
        </div>

        <div>
            <h2 class="site-footer__title">Contact</h2>
            <ul class="site-footer__links site-footer__links--contact">
                <?php if ($address !== ''): ?><li><?= e($address) ?></li><?php endif; ?>
                <?php if ($email !== ''): ?><li><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li><?php endif; ?>
                <?php if ($phone !== ''): ?><li><a href="<?= e(tel_href($phone)) ?>"><?= e($phone) ?></a></li><?php endif; ?>
            </ul>
        </div>

        <div>
            <h2 class="site-footer__title">Newsletter</h2>
            <p>Recevez nos conseils pour les parents et les nouveautés de la gamme.</p>
            <?php foreach ($newsletterFlashes as $f): ?>
                <p class="form-note form-note--<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></p>
            <?php endforeach; ?>
            <form class="newsletter-form" action="<?= e(url('newsletter.php')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="return" value="<?= e(current_page()) ?>">
                <div class="hp-field" aria-hidden="true"><label>Ne pas remplir <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                <label class="visually-hidden" for="newsletter-email">Votre email</label>
                <input id="newsletter-email" type="email" name="email" placeholder="Votre email" required maxlength="190" autocomplete="email">
                <button class="btn btn--primary btn--sm" type="submit">S'inscrire</button>
            </form>
        </div>
    </div>

    <div class="site-footer__bottom">
        <div class="container">© <?= date('Y') ?> <?= e(setting('site_name', 'HAMSI')) ?> Babyfood. Tous droits réservés.</div>
    </div>
</footer>

<a class="chat-fab" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener" aria-label="Discuter avec nous sur WhatsApp"><?= icon('chat') ?></a>
</body>
</html>
