<?php
/**
 * Paramètres du site : nom, logo, couleurs, coordonnées et WhatsApp.
 * Tout est enregistré dans la table site_settings et relu par le site public.
 */
require __DIR__ . '/includes/auth.php';
require_super_admin();

const LOGO_MAX_SIZE = 1024 * 1024; // 1 Mo

$defaults = default_settings();
$values = settings();
$errors = [];

/** Champs texte enregistrés tels quels. */
$textFields = [
    'site_name'        => ['label' => 'Nom du site', 'max' => 60, 'required' => true],
    'site_tagline'     => ['label' => 'Phrase de présentation', 'max' => 255, 'required' => false],
    'contact_phone'    => ['label' => 'Téléphone principal', 'max' => 30, 'required' => false],
    'contact_phone_2'  => ['label' => 'Téléphone secondaire', 'max' => 30, 'required' => false],
    'contact_email'    => ['label' => 'Email de contact', 'max' => 190, 'required' => false],
    'contact_address'  => ['label' => 'Adresse', 'max' => 190, 'required' => false],
    'contact_hours'    => ['label' => 'Horaires', 'max' => 120, 'required' => false],
    'website'          => ['label' => 'Site web', 'max' => 120, 'required' => false],
    'whatsapp_number'  => ['label' => 'Numéro WhatsApp', 'max' => 20, 'required' => false],
    'whatsapp_message' => ['label' => 'Message WhatsApp pré-rempli', 'max' => 255, 'required' => false],
    'currency'         => ['label' => 'Symbole monétaire', 'max' => 5, 'required' => false],
];

$colorFields = [
    'color_primary'    => 'Couleur principale',
    'color_secondary'  => 'Couleur secondaire',
    'color_button'     => 'Couleur des boutons',
    'color_text'       => 'Couleur du texte',
    'color_background' => 'Couleur de fond',
    'color_surface'    => 'Couleur des blocs',
];

if (post_too_large()) {
    $errors['global'] = 'L\'envoi est trop volumineux. Choisissez un logo de 1 Mo maximum.';
} elseif (is_post()) {
    if (!csrf_verify()) {
        $errors['global'] = 'La session a expiré. Renvoyez le formulaire.';
    } else {
        $new = [];

        // --- Champs texte
        foreach ($textFields as $key => $config) {
            $value = post_str($key, $config['max']);
            if ($config['required'] && $value === '') {
                $errors[$key] = $config['label'] . ' est obligatoire.';
            }
            $new[$key] = $value;
        }
        if ($new['contact_email'] !== '' && !filter_var($new['contact_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['contact_email'] = 'Indiquez une adresse email valide.';
        }
        if ($new['whatsapp_number'] !== '' && !preg_match('/^\+?[\d\s.()-]{6,20}$/', $new['whatsapp_number'])) {
            $errors['whatsapp_number'] = 'Numéro invalide. Exemple : +253 77 74 37 09';
        }

        // --- Couleurs
        foreach ($colorFields as $key => $label) {
            $value = strtoupper(post_str($key, 7));
            if (!valid_hex_color($value)) {
                $errors[$key] = $label . ' : code couleur invalide (format attendu #RRGGBB).';
            }
            $new[$key] = $value;
        }

        // --- Logo
        $logoPath = null;
        if (!$errors) {
            $upload = handle_image_upload($_FILES['logo'] ?? ['error' => UPLOAD_ERR_NO_FILE], 'site', LOGO_MAX_SIZE);
            if ($upload['error']) {
                $errors['logo'] = $upload['error'];
            } else {
                $logoPath = $upload['path'];
            }
        }

        if (!$errors) {
            foreach ($new as $key => $value) {
                save_setting($key, $value);
            }
            if ($logoPath !== null) {
                $oldLogo = setting('logo');
                save_setting('logo', $logoPath);
                // L'ancien logo n'est supprimé que s'il avait été envoyé depuis l'administration
                if (str_starts_with($oldLogo, 'uploads/')) {
                    delete_upload($oldLogo);
                }
            }
            settings(true); // recharge le cache
            flash('success', 'Les paramètres ont été enregistrés. Le site public affiche déjà les modifications.');
            redirect('parametres.php');
        }

        $values = array_merge($values, $new);
    }
}

/** Valeur affichée dans le formulaire. */
$val = static fn (string $key): string => (string) ($values[$key] ?? '');
$err = static fn (string $key): string => isset($errors[$key])
    ? '<span class="field__error">' . e($errors[$key]) . '</span>' : '';
$cls = static fn (string $key): string => isset($errors[$key]) ? ' has-error' : '';

$adminTitle = 'Paramètres';
$activeMenu = 'parametres';
require __DIR__ . '/includes/header.php';
?>

<?php if (isset($errors['global'])): ?>
    <div class="alert alert--error" role="alert"><?= icon('alert') ?><span><?= e($errors['global']) ?></span></div>
<?php elseif ($errors): ?>
    <div class="alert alert--error" role="alert"><?= icon('alert') ?><span>Les paramètres n'ont pas été enregistrés : corrigez les champs signalés.</span></div>
<?php endif; ?>

<form method="post" action="parametres.php" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <div class="form-layout">
        <div>
            <!-- ---------------- Identité ---------------- -->
            <section class="panel">
                <h2 class="panel__title">Identité du site</h2>

                <div class="field">
                    <label for="site_name">Nom du site <span class="req" aria-hidden="true">*</span></label>
                    <input class="input<?= $cls('site_name') ?>" id="site_name" type="text" name="site_name" value="<?= e($val('site_name')) ?>" required maxlength="60">
                    <span class="field__hint">Affiché dans l'en-tête, le pied de page et le titre des pages.</span>
                    <?= $err('site_name') ?>
                </div>

                <div class="field">
                    <label for="site_tagline">Phrase de présentation</label>
                    <textarea class="textarea<?= $cls('site_tagline') ?>" id="site_tagline" name="site_tagline" rows="2" maxlength="255"><?= e($val('site_tagline')) ?></textarea>
                    <span class="field__hint">Reprise dans le pied de page et la description des pages.</span>
                    <?= $err('site_tagline') ?>
                </div>
            </section>

            <!-- ---------------- Couleurs ---------------- -->
            <section class="panel">
                <h2 class="panel__title">Couleurs principales</h2>
                <p class="text-muted">Ces couleurs sont appliquées immédiatement au site public et à l'administration.</p>

                <div class="color-grid">
                    <?php foreach ($colorFields as $key => $label): ?>
                        <?php $current = valid_hex_color($val($key)) ? $val($key) : $defaults[$key]; ?>
                        <div class="field">
                            <span class="field__label" id="label-<?= e($key) ?>"><?= e($label) ?></span>
                            <div class="color-field<?= $cls($key) ?>">
                                <input type="color" id="<?= e($key) ?>" value="<?= e($current) ?>" aria-labelledby="label-<?= e($key) ?>" tabindex="-1">
                                <label class="visually-hidden" for="<?= e($key) ?>-hex">Code hexadécimal — <?= e($label) ?></label>
                                <input type="text" id="<?= e($key) ?>-hex" name="<?= e($key) ?>" value="<?= e($current) ?>" maxlength="7" pattern="#[0-9a-fA-F]{6}" spellcheck="false">
                            </div>
                            <?= $err($key) ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="theme-preview" aria-hidden="true">
                    <?php foreach ($colorFields as $key => $label): ?>
                        <span class="theme-preview__swatch" data-swatch-for="<?= e($key) ?>" style="background: <?= e(valid_hex_color($val($key)) ? $val($key) : $defaults[$key]) ?>"></span>
                    <?php endforeach; ?>
                    <span class="text-muted">Aperçu des couleurs</span>
                </div>
                <p class="field__hint">Pour revenir à la charte Hamsi : principale #4B6453, secondaire #C3DFC9, texte #201439, fond #FEF7FF, blocs #F4EAFF.</p>
            </section>

            <!-- ---------------- Coordonnées ---------------- -->
            <section class="panel">
                <h2 class="panel__title">Coordonnées affichées sur le site</h2>

                <div class="form-row">
                    <div class="field">
                        <label for="contact_phone">Téléphone principal</label>
                        <input class="input<?= $cls('contact_phone') ?>" id="contact_phone" type="text" name="contact_phone" value="<?= e($val('contact_phone')) ?>" maxlength="30">
                        <?= $err('contact_phone') ?>
                    </div>
                    <div class="field">
                        <label for="contact_phone_2">Téléphone secondaire</label>
                        <input class="input<?= $cls('contact_phone_2') ?>" id="contact_phone_2" type="text" name="contact_phone_2" value="<?= e($val('contact_phone_2')) ?>" maxlength="30">
                        <?= $err('contact_phone_2') ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="field">
                        <label for="contact_email">Email de contact</label>
                        <input class="input<?= $cls('contact_email') ?>" id="contact_email" type="email" name="contact_email" value="<?= e($val('contact_email')) ?>" maxlength="190">
                        <?= $err('contact_email') ?>
                    </div>
                    <div class="field">
                        <label for="contact_address">Adresse</label>
                        <input class="input<?= $cls('contact_address') ?>" id="contact_address" type="text" name="contact_address" value="<?= e($val('contact_address')) ?>" maxlength="190">
                        <?= $err('contact_address') ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="field">
                        <label for="contact_hours">Horaires</label>
                        <input class="input<?= $cls('contact_hours') ?>" id="contact_hours" type="text" name="contact_hours" value="<?= e($val('contact_hours')) ?>" maxlength="120">
                        <?= $err('contact_hours') ?>
                    </div>
                    <div class="field">
                        <label for="website">Site web</label>
                        <input class="input<?= $cls('website') ?>" id="website" type="text" name="website" value="<?= e($val('website')) ?>" maxlength="120">
                        <?= $err('website') ?>
                    </div>
                </div>
            </section>

            <!-- ---------------- WhatsApp ---------------- -->
            <section class="panel">
                <h2 class="panel__title">Commande par WhatsApp</h2>

                <div class="form-row">
                    <div class="field">
                        <label for="whatsapp_number">Numéro WhatsApp</label>
                        <input class="input<?= $cls('whatsapp_number') ?>" id="whatsapp_number" type="text" name="whatsapp_number" value="<?= e($val('whatsapp_number')) ?>" maxlength="20" placeholder="+253 77 74 37 09">
                        <span class="field__hint">Format international. Les espaces et symboles sont ignorés dans le lien.</span>
                        <?= $err('whatsapp_number') ?>
                    </div>
                    <div class="field">
                        <label for="currency">Symbole monétaire</label>
                        <input class="input<?= $cls('currency') ?>" id="currency" type="text" name="currency" value="<?= e($val('currency')) ?>" maxlength="5">
                        <?= $err('currency') ?>
                    </div>
                </div>

                <div class="field">
                    <label for="whatsapp_message">Message pré-rempli</label>
                    <textarea class="textarea<?= $cls('whatsapp_message') ?>" id="whatsapp_message" name="whatsapp_message" rows="2" maxlength="255"><?= e($val('whatsapp_message')) ?></textarea>
                    <span class="field__hint">Texte proposé au client quand il ouvre WhatsApp depuis le site.</span>
                    <?= $err('whatsapp_message') ?>
                </div>
            </section>
        </div>

        <!-- ---------------- Logo et enregistrement ---------------- -->
        <div class="form-layout__side">
            <section class="panel">
                <h2 class="panel__title">Logo</h2>

                <div class="logo-preview">
                    <img id="logo-preview" src="<?= e(media_url(setting('logo'), 'assets/images/logo-icon.png')) ?>" alt="Logo actuel du site" width="64" height="64">
                    <div>
                        <strong>Logo actuel</strong>
                        <small><?= e(setting('logo', 'assets/images/logo-icon.png')) ?></small>
                    </div>
                </div>

                <div class="field<?= $cls('logo') ?>">
                    <label class="btn btn--soft btn--sm btn--block" for="logo"><?= icon('image') ?> Changer le logo</label>
                    <input class="visually-hidden" id="logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp" data-preview="logo-preview">
                    <p class="field__hint" data-file-name>JPG, PNG ou WEBP — 1 Mo maximum. Image carrée recommandée (401 × 401 px selon la charte).</p>
                    <?= $err('logo') ?>
                </div>

                <div class="form-actions">
                    <button class="btn btn--primary btn--block" type="submit"><?= icon('check') ?> Enregistrer les paramètres</button>
                    <a class="btn btn--ghost btn--block" href="../index.php" target="_blank" rel="noopener"><?= icon('external') ?> Voir le site</a>
                </div>
            </section>
        </div>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
