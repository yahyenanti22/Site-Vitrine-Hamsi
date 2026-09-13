<?php
/**
 * Fonctions utilitaires partagées par le site public et l'administration.
 */
defined('HAMSI') || exit('Accès interdit');

// =====================================================================
//  Sécurité : échappement, CSRF, redirections
// =====================================================================

/** Échappe une valeur pour un affichage HTML sûr (protection XSS). */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Retourne (et crée si besoin) le jeton CSRF de la session. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Champ caché contenant le jeton CSRF, à placer dans chaque formulaire POST. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Vérifie le jeton CSRF envoyé avec le formulaire. */
function csrf_verify(): bool
{
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $sent);
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/** Adresse IP du visiteur (on ne fait pas confiance aux en-têtes X-Forwarded-For). */
function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

/** Récupère une chaîne POST nettoyée (espaces retirés, longueur bornée). */
function post_str(string $key, int $max = 1000): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }
    $value = trim(str_replace("\0", '', $value));
    return mb_substr($value, 0, $max);
}

// =====================================================================
//  Messages flash (affichés une seule fois après redirection)
// =====================================================================

function flash(string $type, string $message, string $channel = 'main'): void
{
    $_SESSION['flash'][$channel][] = ['type' => $type, 'message' => $message];
}

function get_flashes(string $channel = 'main'): array
{
    $messages = $_SESSION['flash'][$channel] ?? [];
    unset($_SESSION['flash'][$channel]);
    return $messages;
}

// =====================================================================
//  URL et fichiers
// =====================================================================

/** Construit une URL relative valable depuis le site public ou l'admin. */
function url(string $path = ''): string
{
    return ROOT_PREFIX . ltrim($path, '/');
}

/** URL d'un fichier (image) du projet, avec image de repli si le fichier est absent. */
function media_url(?string $path, string $fallback = 'assets/images/placeholder.svg'): string
{
    $path = (string) $path;
    if ($path !== '' && !str_contains($path, '..') && is_file(BASE_PATH . '/' . $path)) {
        return url($path);
    }
    return url($fallback);
}

/** Nom de la page courante (ex : index.php) pour activer le menu. */
function current_page(): string
{
    return basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
}

// =====================================================================
//  Paramètres du site (table site_settings)
// =====================================================================

/** Valeurs par défaut utilisées si une clé est absente de la base. */
function default_settings(): array
{
    return [
        'site_name'        => 'HAMSI',
        'site_tagline'     => 'Hamsi est la marque africaine qui révolutionne le babyfood en Afrique.',
        'logo'             => 'assets/images/logo-icon.png',
        'color_primary'    => '#4B6453',
        'color_secondary'  => '#C3DFC9',
        'color_button'     => '#4B6453',
        'color_text'       => '#201439',
        'color_background' => '#FEF7FF',
        'color_surface'    => '#F4EAFF',
        'contact_phone'    => '',
        'contact_phone_2'  => '',
        'contact_email'    => '',
        'contact_address'  => '',
        'contact_hours'    => '',
        'website'          => '',
        'whatsapp_number'  => '',
        'whatsapp_message' => 'Bonjour Hamsi, j\'aimerais avoir des informations sur vos produits.',
        'currency'         => '€',
    ];
}

/** Charge tous les paramètres (une seule requête par page). */
function settings(bool $refresh = false): array
{
    static $cache = null;
    if ($cache === null || $refresh) {
        $cache = default_settings();
        $rows = db()->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
        foreach ($rows as $row) {
            $cache[$row['setting_key']] = (string) $row['setting_value'];
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $all = settings();
    return isset($all[$key]) && $all[$key] !== '' ? $all[$key] : $default;
}

/** Enregistre (ou crée) un paramètre. */
function save_setting(string $key, string $value): void
{
    $stmt = db()->prepare(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([':k' => $key, ':v' => $value]);
}

function valid_hex_color(string $color): bool
{
    return (bool) preg_match('/^#[0-9a-fA-F]{6}$/', $color);
}

/** Variables CSS générées à partir des couleurs enregistrées en base. */
function theme_css_vars(): string
{
    $map = [
        '--color-primary'    => 'color_primary',
        '--color-secondary'  => 'color_secondary',
        '--color-button'     => 'color_button',
        '--color-text'       => 'color_text',
        '--color-background' => 'color_background',
        '--color-surface'    => 'color_surface',
    ];
    $defaults = default_settings();
    $css = ':root{';
    foreach ($map as $var => $key) {
        $value = setting($key, $defaults[$key]);
        if (!valid_hex_color($value)) {
            $value = $defaults[$key];
        }
        $css .= $var . ':' . $value . ';';
    }
    return $css . '}';
}

// =====================================================================
//  Formatage
// =====================================================================

function format_price(mixed $price): string
{
    if ($price === null || $price === '') {
        return '';
    }
    return number_format((float) $price, 2, ',', ' ') . ' ' . setting('currency', '€');
}

/** Lien WhatsApp (wa.me) avec message pré-rempli. */
function whatsapp_url(string $message = ''): string
{
    $number = preg_replace('/\D+/', '', setting('whatsapp_number'));
    $message = $message !== '' ? $message : setting('whatsapp_message');
    $base = $number !== '' ? 'https://wa.me/' . $number : 'https://wa.me/';
    return $base . ($message !== '' ? '?text=' . rawurlencode($message) : '');
}

/** Lien tel: à partir d'un numéro affiché. */
function tel_href(string $phone): string
{
    return 'tel:' . preg_replace('/[^\d+]/', '', $phone);
}

/** Transforme un texte en slug d'URL (ex : « Purée Douce » → « puree-douce »). */
function slugify(string $text): string
{
    $map = [
        'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a', 'ç' => 'c', 'é' => 'e', 'è' => 'e',
        'ê' => 'e', 'ë' => 'e', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ñ' => 'n', 'ó' => 'o', 'ô' => 'o',
        'ö' => 'o', 'õ' => 'o', 'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ÿ' => 'y', 'œ' => 'oe', 'æ' => 'ae',
    ];
    $text = strtr(mb_strtolower($text, 'UTF-8'), $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim((string) $text, '-');
    return $text !== '' ? substr($text, 0, 150) : 'element';
}

/** Génère un slug unique pour une table donnée (products ou categories). */
function unique_slug(string $table, string $name, ?int $excludeId = null): string
{
    if (!in_array($table, ['products', 'categories'], true)) {
        throw new InvalidArgumentException('Table non autorisée');
    }
    $base = slugify($name);
    $slug = $base;
    $i = 2;
    while (true) {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE slug = :slug" . ($excludeId ? ' AND id <> :id' : '');
        $stmt = db()->prepare($sql);
        $params = [':slug' => $slug];
        if ($excludeId) {
            $params[':id'] = $excludeId;
        }
        $stmt->execute($params);
        if ((int) $stmt->fetchColumn() === 0) {
            return $slug;
        }
        $slug = $base . '-' . $i++;
    }
}

/**
 * Convertit le champ « ingrédients » (un par ligne, « Nom | Détail ») en tableau.
 */
function parse_ingredients(?string $text): array
{
    $items = [];
    foreach (preg_split('/\R/', (string) $text) as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = array_map('trim', explode('|', $line, 2));
        $items[] = ['name' => $parts[0], 'detail' => $parts[1] ?? ''];
    }
    return $items;
}

/** Affiche un texte multi-lignes sous forme de paragraphes échappés. */
function paragraphs(?string $text): string
{
    $html = '';
    foreach (preg_split('/\R{2,}/', trim((string) $text)) as $block) {
        if (trim($block) !== '') {
            $html .= '<p>' . nl2br(e(trim($block))) . '</p>';
        }
    }
    return $html;
}

function format_date(?string $datetime, bool $withTime = true): string
{
    if (!$datetime) {
        return '—';
    }
    $ts = strtotime($datetime);
    return $ts ? date($withTime ? 'd/m/Y à H:i' : 'd/m/Y', $ts) : '—';
}

// =====================================================================
//  Upload d'images sécurisé
// =====================================================================

/**
 * Valide et enregistre une image envoyée.
 *
 * @param array  $file     Entrée de $_FILES
 * @param string $subdir   Sous-dossier de /uploads (products, site)
 * @param int    $maxBytes Taille maximale autorisée
 * @return array ['path' => chemin relatif|null, 'error' => message|null]
 */
function handle_image_upload(array $file, string $subdir, int $maxBytes = 2097152): array
{
    if (!in_array($subdir, ['products', 'site'], true)) {
        return ['path' => null, 'error' => 'Dossier de destination invalide.'];
    }
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['path' => null, 'error' => 'Envoi de fichier invalide.'];
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null]; // aucun fichier : pas une erreur
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return ['path' => null, 'error' => 'Le fichier dépasse la taille maximale autorisée.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => 'L\'envoi du fichier a échoué. Réessayez.'];
    }
    if ($file['size'] <= 0 || $file['size'] > $maxBytes) {
        return ['path' => null, 'error' => 'Le fichier doit peser au maximum ' . round($maxBytes / 1048576, 1) . ' Mo.'];
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['path' => null, 'error' => 'Fichier non reconnu.'];
    }

    // 1) Extension déclarée
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return ['path' => null, 'error' => 'Format non autorisé. Utilisez une image JPG, PNG ou WEBP.'];
    }

    // 2) Type MIME réel (lu dans le contenu du fichier, pas celui envoyé par le navigateur)
    $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($file['tmp_name']);
    if (!isset($mimeToExt[$mime])) {
        return ['path' => null, 'error' => 'Le fichier n\'est pas une image valide (JPG, PNG ou WEBP).'];
    }

    // 3) Vérification que le contenu est bien une image lisible
    $info = @getimagesize($file['tmp_name']);
    if ($info === false || $info[0] < 1 || $info[1] < 1 || $info[0] > 8000 || $info[1] > 8000) {
        return ['path' => null, 'error' => 'Image illisible ou dimensions invalides.'];
    }

    // 4) Nom de fichier aléatoire + extension déduite du type réel
    $dir = BASE_PATH . '/uploads/' . $subdir;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return ['path' => null, 'error' => 'Impossible de créer le dossier d\'upload.'];
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $mimeToExt[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
        return ['path' => null, 'error' => 'Impossible d\'enregistrer le fichier.'];
    }
    @chmod($dir . '/' . $filename, 0644);

    return ['path' => 'uploads/' . $subdir . '/' . $filename, 'error' => null];
}

/** Supprime un fichier précédemment envoyé (uniquement dans /uploads). */
function delete_upload(?string $relativePath): void
{
    $relativePath = (string) $relativePath;
    if ($relativePath === '' || !str_starts_with($relativePath, 'uploads/') || str_contains($relativePath, '..')) {
        return;
    }
    $full = BASE_PATH . '/' . $relativePath;
    if (is_file($full)) {
        @unlink($full);
    }
}
