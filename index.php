<?php
require __DIR__ . '/includes/bootstrap.php';

// Produits phares : récupérés côté serveur depuis MySQL
$featured = db()->query(
    "SELECT p.*, c.label AS category_label
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.status = 'actif' AND p.is_featured = 1
     ORDER BY p.sort_order ASC, p.id ASC
     LIMIT 3"
)->fetchAll();

// Si aucun produit n'est marqué « phare », on affiche les 3 premiers produits actifs
if (!$featured) {
    $featured = db()->query(
        "SELECT p.*, c.label AS category_label FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.status = 'actif' ORDER BY p.sort_order ASC, p.id ASC LIMIT 3"
    )->fetchAll();
}

$bodyClass = 'page-home';
require __DIR__ . '/includes/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero hero--home">
    <div class="container hero__grid">
        <div class="hero__content">
            <span class="badge badge--mint badge--lg"><?= icon('leaf') ?> 100% Naturel &amp; Africain</span>
            <h1 class="hero__title">Le meilleur de la nature africaine pour éveiller bébé</h1>
            <p class="hero__text">Des purées onctueuses, des compotes vitaminées et des céréales saines, confectionnées avec amour à partir des trésors de nos terroirs.</p>
            <div class="hero__actions">
                <a class="btn btn--primary" href="produits.php">Découvrir nos produits <?= icon('arrow-right') ?></a>
                <a class="btn btn--slate" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener"><?= icon('chat') ?> Commander sur WhatsApp</a>
            </div>
        </div>
        <figure class="hero-card">
            <img src="assets/images/hero-accueil.jpg" alt="Hamsi l'éléphanteau devant un bol de purée, des mangues et des bananes" width="600" height="420">
            <figcaption class="hero-card__caption">
                <span class="icon-circle icon-circle--mint icon-circle--sm"><?= icon('heart') ?></span>
                <span class="hero-card__text"><strong>Hamsi l'Éléphanteau</strong><small>Force calme, sagesse et longévité</small></span>
                <span class="hero-card__tag">100% Bio</span>
            </figcaption>
        </figure>
    </div>
</section>

<!-- ============ QUI EST HAMSI ============ -->
<section class="section section--tight">
    <div class="container">
        <div class="intro-panel">
            <div class="intro-panel__text">
                <p class="eyebrow">Qui est Hamsi ?</p>
                <h2 class="section-title">Une alimentation saine, locale et adaptée aux tout-petits</h2>
                <p>Hamsi est la marque africaine qui révolutionne le babyfood en Afrique. Nos purées de légumes, compotes de fruits et céréales infantiles sont issus de la biodiversité africaine.</p>
                <a class="link-arrow" href="apropos.php">Découvrir Hamsi <?= icon('arrow-right') ?></a>
            </div>
            <div class="intro-panel__items">
                <div class="mini-card">
                    <span class="mini-card__icon"><?= icon('leaf') ?></span>
                    <strong>Légumes</strong>
                    <small>Purées douces</small>
                </div>
                <div class="mini-card">
                    <span class="mini-card__icon"><?= icon('wheat') ?></span>
                    <strong>Céréales</strong>
                    <small>Millet &amp; Baobab</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ PRODUITS PHARES (MySQL) ============ -->
<section class="section section--tight">
    <div class="container">
        <div class="section-head section-head--split">
            <div>
                <p class="eyebrow">Nos indispensables</p>
                <h2 class="section-title section-title--lg">Produits phares</h2>
            </div>
            <a class="link-muted link-arrow" href="produits.php">Voir tous nos produits <?= icon('arrow-right') ?></a>
        </div>

        <?php if ($featured): ?>
            <div class="grid grid--3">
                <?php foreach ($featured as $product) { render_featured_card($product); } ?>
            </div>
        <?php else: ?>
            <p class="empty-state">Nos produits arrivent très bientôt.</p>
        <?php endif; ?>
    </div>
</section>

<!-- ============ POURQUOI CHOISIR HAMSI ============ -->
<section class="section section--tinted">
    <div class="container">
        <div class="section-head section-head--center">
            <p class="eyebrow">Nos piliers</p>
            <h2 class="section-title">Pourquoi choisir Hamsi ?</h2>
            <p class="section-lead">Parce que chaque cuillère compte pour donner le meilleur départ possible à votre enfant.</p>
        </div>
        <div class="grid grid--4">
            <article class="pillar-card">
                <span class="icon-circle icon-circle--mint"><?= icon('utensils') ?></span>
                <h3>Alimentation saine</h3>
                <p>Des recettes africaines revisitées avec une nutritionniste spécialiste de la petite enfance.</p>
            </article>
            <article class="pillar-card">
                <span class="icon-circle icon-circle--mint"><?= icon('sprout') ?></span>
                <h3>Biodiversité africaine</h3>
                <p>Mise en avant des super-aliments locaux : moringa, baobab, fonio, patate douce.</p>
            </article>
            <article class="pillar-card">
                <span class="icon-circle icon-circle--mint"><?= icon('badge-check') ?></span>
                <h3>Sécurité alimentaire</h3>
                <p>Des conditions de production strictement encadrées par les normes en sécurité et hygiène alimentaire.</p>
            </article>
            <article class="pillar-card">
                <span class="icon-circle icon-circle--mint"><?= icon('eye') ?></span>
                <h3>Transparence</h3>
                <p>De la ferme jusqu'au bol de bébé, une transparence sur tout notre process : on vous dit tout.</p>
            </article>
        </div>
    </div>
</section>

<!-- ============ VALEURS ============ -->
<section class="section">
    <div class="container">
        <div class="grid grid--3">
            <article class="value-card">
                <span class="icon-circle icon-circle--mint icon-circle--lg"><?= icon('shield') ?></span>
                <h3>Sécurité</h3>
                <p>Des standards de fabrication irréprochables pour protéger la santé fragile des bébés.</p>
            </article>
            <article class="value-card">
                <span class="icon-circle icon-circle--mint icon-circle--lg"><?= icon('compass') ?></span>
                <h3>Découverte</h3>
                <p>Éveiller les sens des tout-petits aux saveurs authentiques et variées de l'Afrique.</p>
            </article>
            <article class="value-card">
                <span class="icon-circle icon-circle--mint icon-circle--lg"><?= icon('users') ?></span>
                <h3>Partage</h3>
                <p>Soutenir les familles et créer une communauté bienveillante de parents engagés.</p>
            </article>
        </div>
    </div>
</section>

<!-- ============ NOTRE HISTOIRE ============ -->
<section class="section section--tight">
    <div class="container">
        <div class="story-card">
            <div class="story-card__media">
                <img src="assets/images/histoire-accueil.jpg" alt="Une maman et son bébé partagent un repas Hamsi" loading="lazy" width="732" height="432">
            </div>
            <div class="story-card__text">
                <p class="eyebrow">Notre histoire</p>
                <h2 class="section-title">Une marque africaine pensée pour les petits.</h2>
                <p>Née du constat du manque de produits infantiles adaptés et locaux, Hamsi s'engage à offrir aux parents une alternative saine, moderne et profondément ancrée dans notre patrimoine culinaire.</p>
                <a class="link-arrow" href="apropos.php">Découvrir notre histoire <?= icon('arrow-right') ?></a>
            </div>
        </div>
    </div>
</section>

<!-- ============ NOTRE ENGAGEMENT ============ -->
<section class="section section--tight">
    <div class="container">
        <div class="story-card story-card--reverse">
            <div class="story-card__media">
                <img src="assets/images/engagement-femmes.jpg" alt="Femmes agricultrices récoltant des légumes" loading="lazy" width="896" height="652">
            </div>
            <div class="story-card__text">
                <p class="eyebrow">Notre engagement</p>
                <h2 class="section-title">Agir pour l'avenir de nos enfants.</h2>
                <p>Au-delà de la nutrition, Hamsi lutte activement contre la dénutrition infantile et soutient l'autonomisation des femmes agricultrices locales qui cultivent nos ingrédients avec soin.</p>
                <a class="link-arrow" href="engagement.php">Découvrir notre engagement <?= icon('arrow-right') ?></a>
            </div>
        </div>
    </div>
</section>

<!-- ============ CTA WHATSAPP ============ -->
<section class="cta-band">
    <div class="container cta-band__inner">
        <span class="icon-circle icon-circle--dark icon-circle--lg"><?= icon('chat') ?></span>
        <h2 class="section-title">Une question ? Une commande ?</h2>
        <p>Notre équipe est à votre écoute pour vous conseiller sur les meilleurs produits adaptés à l'âge et aux besoins de votre bébé.</p>
        <a class="btn btn--primary btn--lg" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener"><?= icon('chat') ?> Commander sur WhatsApp</a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
