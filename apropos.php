<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'À propos';
$pageDescription = 'Hamsi est la marque africaine qui révolutionne le babyfood en Afrique : vision, mission et valeurs.';
$bodyClass = 'page-about';
require __DIR__ . '/includes/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero hero--about">
    <div class="container hero__grid">
        <div class="hero__content">
            <span class="badge badge--mint"><?= icon('heart') ?> Notre Histoire &amp; Nos Racines</span>
            <h1 class="hero__title">À propos de Hamsi</h1>
            <p class="hero__text">Née d'une vision passionnée pour la santé des tout-petits, HAMSI associe l'excellence nutritionnelle aux trésors du terroir africain pour offrir à chaque enfant le meilleur départ dans la vie.</p>
        </div>
        <figure class="photo-overlay">
            <img class="photo-overlay__img" src="assets/images/apropos-hero.jpg" alt="Une maman nourrit son bébé en souriant" width="872" height="680">
            <figcaption class="float-card float-card--left">
                <span class="icon-circle icon-circle--mint"><?= icon('apple') ?></span>
                <span><strong>100% Naturel !</strong><small>Sans conservateur ni sucre ajouté</small></span>
            </figcaption>
        </figure>
    </div>
</section>

<!-- ============ QUI SOMMES-NOUS ============ -->
<section class="section">
    <div class="container split">
        <div class="split__media">
            <img class="rounded-photo" src="assets/images/apropos-ancrage.jpg" alt="Céréales, graines et légumes africains sur une natte tressée" loading="lazy" width="876" height="820">
        </div>
        <div class="split__text">
            <p class="eyebrow eyebrow--serif">Qui sommes-nous ?</p>
            <h2 class="section-title">Un ancrage africain profond, une exigence mondiale</h2>
            <p>Hamsi est la marque africaine qui révolutionne le babyfood en Afrique. Nos purées de légumes, compotes de fruits et céréales infantiles sont issus de la biodiversité africaine : nous valorisons les super-aliments locaux — mil, sorgho, fonio, patate douce.</p>
            <p>Nos recettes africaines sont revisitées avec une nutritionniste spécialiste de la petite enfance, dans des conditions de production strictement encadrées par les normes en sécurité et hygiène alimentaire. De la ferme jusqu'au bol de bébé, une transparence sur tout notre process : on vous dit tout.</p>
            <p>Notre nom, <strong>HAMSI</strong>, est une locution en langue somali typiquement destinée aux bébés et qui veut dire : <em>mange</em>. Notre mascotte, le bébé éléphant — animal emblématique du continent africain — représente la force calme, la sagesse et la longévité.</p>
            <div class="stat-pills">
                <div class="stat-pill"><strong>10+</strong><span>Trésors locaux revalorisés</span></div>
                <div class="stat-pill"><strong>100%</strong><span>Ingrédients contrôlés</span></div>
            </div>
        </div>
    </div>
</section>

<!-- ============ VISION & MISSION ============ -->
<section class="section section--tinted">
    <div class="container">
        <div class="section-head section-head--center">
            <p class="eyebrow eyebrow--serif">Cap sur l'avenir</p>
            <h2 class="section-title">Notre Vision &amp; Notre Mission</h2>
        </div>
        <div class="grid grid--2">
            <article class="vm-card">
                <span class="icon-circle icon-circle--dark"><?= icon('eye') ?></span>
                <h3>Notre Vision</h3>
                <blockquote>« Devenir la marque de produits alimentaires infantiles que chaque parent choisit pour son petit super-héros. »</blockquote>
                <p>Nous rêvons d'une génération d'enfants épanouis, nourris avec amour, conscience et des aliments d'une pureté absolue.</p>
            </article>
            <article class="vm-card vm-card--slate">
                <span class="icon-circle icon-circle--slate-dark"><?= icon('hand-heart') ?></span>
                <h3>Notre Mission</h3>
                <ol class="vm-card__list">
                    <li>Accompagner les parents dans l'étape cruciale de la diversification alimentaire de leurs enfants.</li>
                    <li>Lutter contre la dénutrition infantile en Afrique.</li>
                </ol>
                <p>Offrir des solutions accessibles, nutritives et adaptées pour que chaque repas devienne un tremplin vers la santé.</p>
            </article>
        </div>
    </div>
</section>

<!-- ============ VALEURS ============ -->
<section class="section">
    <div class="container">
        <div class="section-head section-head--center">
            <p class="eyebrow eyebrow--serif">Ce qui nous guide</p>
            <h2 class="section-title">Nos Valeurs Fondamentales</h2>
            <p class="section-lead">Sécurité. Découverte. Partage. Des principes chaleureux et bienveillants qui façonnent chacune de nos purées, compotes et céréales.</p>
        </div>
        <div class="grid grid--3">
            <article class="value-card value-card--tall">
                <span class="icon-circle icon-circle--mint icon-circle--xl"><?= icon('shield') ?></span>
                <h3>Sécurité</h3>
                <p>Une traçabilité irréprochable de la ferme au pot. Des contrôles rigoureux pour garantir des produits parfaitement sains et adaptés aux estomacs fragiles.</p>
            </article>
            <article class="value-card value-card--tall">
                <span class="icon-circle icon-circle--slate icon-circle--xl"><?= icon('compass') ?></span>
                <h3>Découverte</h3>
                <p>Éveiller la curiosité gustative des bébés à travers des textures onctueuses et des associations de saveurs inspirées des traditions et de la nature.</p>
            </article>
            <article class="value-card value-card--tall">
                <span class="icon-circle icon-circle--lavender icon-circle--xl"><?= icon('users') ?></span>
                <h3>Partage</h3>
                <p>Créer une communauté soudée de parents, partager des conseils bienveillants et s'entraider pour bâtir un avenir radieux pour nos enfants.</p>
            </article>
        </div>
    </div>
</section>

<!-- ============ CTA ============ -->
<section class="cta-dark">
    <div class="container cta-dark__inner">
        <h2 class="section-title">Rejoignez l'aventure Hamsi</h2>
        <p>Offrez à votre enfant une alimentation saine, locale et pleine d'amour. Découvrez notre gamme complète dès aujourd'hui.</p>
        <a class="btn btn--white" href="produits.php"><?= icon('bag') ?> Découvrir nos produits</a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
