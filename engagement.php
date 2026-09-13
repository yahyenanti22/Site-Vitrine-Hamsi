<?php
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Notre engagement';
$pageDescription = 'Chez Hamsi, nous contribuons à bâtir une Afrique de demain plus autonome, plus résiliente et plus elle-même.';
$bodyClass = 'page-commitment';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero page-hero--center">
    <div class="container">
        <span class="badge badge--mint badge--upper">Notre engagement</span>
        <h1 class="page-hero__title page-hero__title--md">Construire une Afrique de demain</h1>
        <p class="page-hero__text">Chez Hamsi, nous contribuons à bâtir une Afrique de demain plus autonome, plus résiliente et plus elle-même. Nous choisissons de nous engager dans des actions concrètes qui concernent des points centraux.</p>
    </div>
</section>

<!-- Bandeau : objectifs de développement durable (ODD) de la charte -->
<section class="stats-band" aria-label="Nos engagements en chiffres">
    <div class="container stats-band__grid">
        <div class="stats-band__item"><strong>100%</strong><span>Ingrédients locaux et naturels</span></div>
        <div class="stats-band__item"><strong>ODD 2</strong><span>Faim « zéro »</span></div>
        <div class="stats-band__item"><strong>ODD 3</strong><span>Bonne santé et bien-être</span></div>
        <div class="stats-band__item"><strong>ODD 5</strong><span>Égalité entre les sexes</span></div>
    </div>
</section>

<section class="section">
    <div class="container commitment-list">

        <article class="commitment commitment--light">
            <div class="commitment__text">
                <span class="icon-circle icon-circle--mint icon-circle--sm"><?= icon('heart') ?></span>
                <h2 class="commitment__title">Lutte contre la dénutrition infantile en Afrique</h2>
                <p>Chaque enfant mérite une chance égale de grandir en bonne santé. Nous combattons la dénutrition infantile avec nos céréales qui visent le plus grand nombre d'enfants en Afrique de l'Est, grâce à des repas d'une haute densité nutritionnelle, adaptés pour prévenir les carences dès les premiers mois de vie.</p>
                <p class="check-line"><?= icon('check-circle') ?> ODD 2 — Faim « zéro »</p>
            </div>
            <div class="commitment__media">
                <img src="assets/images/engagement-denutrition.jpg" alt="Une maman nourrit son bébé dans un village" loading="lazy" width="852" height="632">
            </div>
        </article>

        <article class="commitment commitment--lavender commitment--reverse">
            <div class="commitment__text">
                <span class="icon-circle icon-circle--slate icon-circle--sm"><?= icon('leaf') ?></span>
                <h2 class="commitment__title">Alimentation saine, locale et équilibrée</h2>
                <p>Chaque enfant mérite de grandir avec une alimentation saine et équilibrée. Nous valorisons les trésors nutritionnels de notre continent : nos recettes intègrent des super-aliments d'exception comme le <strong>mil</strong>, le <strong>fonio</strong>, le <strong>moringa</strong> et le <strong>baobab</strong>.</p>
                <ul class="tag-row">
                    <li><?= icon('wheat') ?> Mil &amp; Fonio</li>
                    <li><?= icon('sprout') ?> Moringa &amp; Baobab</li>
                </ul>
            </div>
            <div class="commitment__media">
                <img src="assets/images/engagement-alimentation.jpg" alt="Bols de mil, fonio, moringa et farine" loading="lazy" width="896" height="648">
            </div>
        </article>

        <article class="commitment commitment--light">
            <div class="commitment__text">
                <span class="icon-circle icon-circle--mint icon-circle--sm"><?= icon('users') ?></span>
                <h2 class="commitment__title">Autonomisation des femmes agricultrices</h2>
                <p>Renforcer l'autonomisation des femmes pour réduire les inégalités. Derrière chaque ingrédient se cache le travail minutieux de femmes courageuses : en travaillant en circuit court avec nos coopératives partenaires, nous favorisons leur indépendance financière et l'éducation des familles.</p>
                <p class="check-line"><?= icon('check-circle') ?> ODD 5 — Égalité entre les sexes</p>
            </div>
            <div class="commitment__media">
                <img src="assets/images/engagement-femmes.jpg" alt="Femmes agricultrices portant des paniers de légumes" loading="lazy" width="896" height="652">
            </div>
        </article>

        <article class="commitment commitment--lavender commitment--reverse">
            <div class="commitment__text">
                <span class="icon-circle icon-circle--slate icon-circle--sm"><?= icon('globe') ?></span>
                <h2 class="commitment__title">Vers une Afrique plus autonome et résiliente</h2>
                <p>Produire localement, transformer localement et nourrir localement. HAMSI s'engage à bâtir une chaîne de valeur 100% africaine pour une Afrique de demain plus autonome, plus résiliente et plus elle-même.</p>
                <p class="check-line"><?= icon('trending-up') ?> Souveraineté et sécurité alimentaire</p>
            </div>
            <div class="commitment__media commitment__media--framed">
                <img src="assets/images/engagement-afrique.jpg" alt="Illustration de paysages africains — Notre engagement Hamsi" loading="lazy" width="884" height="632">
            </div>
        </article>

    </div>
</section>

<section class="cta-soft">
    <div class="container">
        <div class="cta-soft__card">
            <h2 class="cta-soft__title">Envie d'en savoir plus ou de collaborer avec nous ?</h2>
            <p>Rejoignez notre mouvement pour l'avenir de nos enfants. Discutez directement avec notre équipe via WhatsApp.</p>
            <a class="btn btn--primary" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener"><?= icon('chat') ?> Échanger sur WhatsApp</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
