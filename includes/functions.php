<?php
/**
 * Global Helper Functions
 * Electronic Devices E-Commerce System
 */

// ── Bootstrap (autoload classes) ─────────────────────────────────────────────
function bootstrap(): void
{
    require_once ROOT_PATH . 'config/config.php';

    if (!ob_get_level()) {
        ob_start();
    }

    // Only configure session if it has NOT been started yet
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params(SESSION_LIFETIME, '/', '', false, true);
    }

    // Start the session if it is not already active
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Regenerate CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }

    // Remember-me auto-login
    if (empty($_SESSION['logged_in']) && isset($_COOKIE['remember_token'])) {
        $user = new User();
        $user->loginViaRememberToken($_COOKIE['remember_token']);
    }
}

// ── Auth guards ───────────────────────────────────────────────────────────────
function requireLogin(string $redirect = '/auth/login.php'): void
{
    if (empty($_SESSION['logged_in'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect(BASE_URL . ltrim($redirect, '/'));
    }
}

function requireRole(string ...$roles): void
{
    requireLogin();
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        http_response_code(403);
        redirect(BASE_URL . 'error403.php');
    }
}

function requireAdmin(): void { requireRole('admin'); }
function requireStaff(): void { requireRole('admin', 'inventory_manager', 'sales_officer'); }
function isLoggedIn(): bool   { return !empty($_SESSION['logged_in']); }
function isAdmin(): bool      { return ($_SESSION['role'] ?? '') === 'admin'; }
function isCustomer(): bool   { return ($_SESSION['role'] ?? '') === 'customer'; }
function isStaff(): bool      { return in_array($_SESSION['role'] ?? '', ['admin','inventory_manager','sales_officer']); }
function currentUserId(): ?int{ return $_SESSION['user_id'] ?? null; }
function currentCustomerId(): ?int { return $_SESSION['customer_id'] ?? null; }

// ── CSRF ─────────────────────────────────────────────────────────────────────
function csrfToken(): string  { return $_SESSION['csrf_token'] ?? ''; }
function csrfField(): string  { return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">'; }
function verifyCsrf(): bool
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
function csrfCheck(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrf()) {
        http_response_code(403);
        jsonResponse(['success' => false, 'message' => 'CSRF token mismatch.']);
    }
}

// ── Flash messages ────────────────────────────────────────────────────────────
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = compact('type', 'message');
}
function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
function displayFlash(): string
{
    $flash = getFlash();
    if (!$flash) return '';
    $icons = ['success' => 'check-circle', 'danger' => 'exclamation-circle', 'warning' => 'exclamation-triangle', 'info' => 'info-circle'];
    $icon  = $icons[$flash['type']] ?? 'info-circle';
    return "<div class=\"alert alert-{$flash['type']} alert-dismissible fade show\" role=\"alert\">
                <i class=\"fas fa-$icon me-2\"></i>{$flash['message']}
                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
            </div>";
}

// ── Redirect ──────────────────────────────────────────────────────────────────
function redirect(string $url, int $code = 302): never
{
    header("Location: $url", true, $code);
    exit;
}

// ── JSON response ─────────────────────────────────────────────────────────────
function jsonResponse(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ── Sanitize input ────────────────────────────────────────────────────────────
function clean(mixed $value): string
{
    return htmlspecialchars(strip_tags(trim((string) $value)), ENT_QUOTES, 'UTF-8');
}

function post(string $key, mixed $default = ''): mixed
{
    return isset($_POST[$key]) ? clean($_POST[$key]) : $default;
}

function get(string $key, mixed $default = ''): mixed
{
    return isset($_GET[$key]) ? clean($_GET[$key]) : $default;
}

// ── Currency formatting ────────────────────────────────────────────────────────
function formatPrice(float $amount, string $currency = ''): string
{
    $symbol   = $_SESSION['currency_symbol'] ?? DEFAULT_CURRENCY_SYMBOL;
    $currency = $currency ?: ($_SESSION['currency'] ?? DEFAULT_CURRENCY);
    return $symbol . number_format($amount, 2);
}

function convertPrice(float $amount): float
{
    $rate = $_SESSION['exchange_rate'] ?? 1.0;
    return $amount * $rate;
}

// ── Date formatting ───────────────────────────────────────────────────────────
function formatDate(string $date): string
{
    return $date ? date(DATE_FORMAT, strtotime($date)) : '—';
}

function formatDateTime(string $date): string
{
    return $date ? date(DATETIME_FORMAT, strtotime($date)) : '—';
}

function timeAgo(string $date): string
{
    $diff = time() - strtotime($date);
    return match (true) {
        $diff < 60     => 'just now',
        $diff < 3600   => floor($diff / 60) . 'm ago',
        $diff < 86400  => floor($diff / 3600) . 'h ago',
        $diff < 604800 => floor($diff / 86400) . 'd ago',
        default        => formatDate($date),
    };
}

// ── File upload ───────────────────────────────────────────────────────────────
function uploadImage(array $file, string $dir = 'products'): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error code: ' . $file['error']];
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File exceeds 5 MB limit.'];
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        return ['success' => false, 'message' => 'Only JPG, PNG, WEBP, GIF allowed.'];
    }
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $name    = uniqid('img_', true) . '.' . $ext;
    $destDir = UPLOAD_PATH . $dir . '/';
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    $dest = $destDir . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => false, 'message' => 'Failed to save file.'];
    }
    return ['success' => true, 'path' => $dir . '/' . $name, 'url' => UPLOAD_URL . $dir . '/' . $name];
}

function deleteFile(string $path): void
{
    $full = UPLOAD_PATH . $path;
    if (file_exists($full)) unlink($full);
}

// ── Product image URL ─────────────────────────────────────────────────────────
// Returns a real upload path when one exists, otherwise falls back to the
// dynamic SVG generator so every product always has a beautiful image.
function productImageUrl(?string $path, string $category = '', string $name = ''): string
{
    // 1. Real uploaded file on disk
    if ($path && !str_starts_with($path, 'http') && file_exists(UPLOAD_PATH . $path)) {
        return UPLOAD_URL . $path;
    }
    // 2. Absolute URL / CDN link — pass through unchanged
    if ($path && str_starts_with($path, 'http')) {
        return $path;
    }
    // 3. Dynamic SVG generator — always works offline on XAMPP
    $cat = urlencode($category ?: 'electronics');
    $nm  = urlencode(mb_strimwidth($name, 0, 36, ''));
    $v   = $name ? (abs(crc32($name)) % 4) : 0;  // deterministic colour variant
    return BASE_URL . "assets/images/product_img.php?cat={$cat}&name={$nm}&v={$v}";
}

// ── Order status badge ────────────────────────────────────────────────────────
function orderStatusBadge(string $status): string
{
    $map = [
        'pending'          => 'secondary',
        'processing'       => 'info',
        'approved'         => 'primary',
        'packed'           => 'primary',
        'shipped'          => 'warning',
        'out_for_delivery' => 'warning',
        'delivered'        => 'success',
        'cancelled'        => 'danger',
        'returned'         => 'dark',
    ];
    $cls = $map[$status] ?? 'secondary';
    return "<span class=\"badge bg-$cls\">" . ucwords(str_replace('_', ' ', $status)) . "</span>";
}

function paymentStatusBadge(string $status): string
{
    $map = ['pending' => 'warning', 'paid' => 'success', 'partial' => 'info', 'refunded' => 'secondary', 'failed' => 'danger'];
    $cls = $map[$status] ?? 'secondary';
    return "<span class=\"badge bg-$cls\">" . ucfirst($status) . "</span>";
}

// ── Star rating ────────────────────────────────────────────────────────────────
function starRating(float $rating, bool $interactive = false): string
{
    $html = '<div class="star-rating">';
    for ($i = 1; $i <= 5; $i++) {
        $class = $i <= $rating ? 'fas fa-star text-warning' : ($i - 0.5 <= $rating ? 'fas fa-star-half-alt text-warning' : 'far fa-star text-warning');
        $html .= "<i class=\"$class\"></i>";
    }
    return $html . '</div>';
}

// ── Truncate text ─────────────────────────────────────────────────────────────
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . $suffix : $text;
}

// ── Slug generator ─────────────────────────────────────────────────────────────
function slugify(string $text): string
{
    return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $text), '-'));
}

// ── Generate order number ─────────────────────────────────────────────────────
function generateOrderNumber(): string
{
    return ORDER_PREFIX . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// ── Pagination HTML ───────────────────────────────────────────────────────────
function paginationLinks(array $paginator, string $baseUrl): string
{
    if ($paginator['last_page'] <= 1) return '';
    $html  = '<nav><ul class="pagination justify-content-center mb-0">';
    $prev  = $paginator['current_page'] - 1;
    $next  = $paginator['current_page'] + 1;
    $last  = $paginator['last_page'];
    $curr  = $paginator['current_page'];
    $sep   = str_contains($baseUrl, '?') ? '&' : '?';

    $html .= $curr > 1
        ? "<li class=\"page-item\"><a class=\"page-link\" href=\"{$baseUrl}{$sep}page=$prev\">‹ Prev</a></li>"
        : '<li class="page-item disabled"><span class="page-link">‹ Prev</span></li>';

    for ($i = max(1, $curr - 2); $i <= min($last, $curr + 2); $i++) {
        $active = $i === $curr ? 'active' : '';
        $html  .= "<li class=\"page-item $active\"><a class=\"page-link\" href=\"{$baseUrl}{$sep}page=$i\">$i</a></li>";
    }

    $html .= $curr < $last
        ? "<li class=\"page-item\"><a class=\"page-link\" href=\"{$baseUrl}{$sep}page=$next\">Next ›</a></li>"
        : '<li class=\"page-item disabled"><span class=\"page-link\">Next ›</span></li>';

    return $html . '</ul></nav>';
}

// ── Password strength ─────────────────────────────────────────────────────────
function validatePassword(string $pass): array
{
    $errors = [];
    if (strlen($pass) < 8)             $errors[] = 'At least 8 characters';
    if (!preg_match('/[A-Z]/', $pass)) $errors[] = 'One uppercase letter';
    if (!preg_match('/[0-9]/', $pass)) $errors[] = 'One digit';
    return $errors;
}

// ── Get current page for active nav ──────────────────────────────────────────
function isCurrentPage(string $path): bool
{
    return str_contains($_SERVER['REQUEST_URI'], $path);
}

function navActive(string $path): string { return isCurrentPage($path) ? 'active' : ''; }

// ── Settings helper ───────────────────────────────────────────────────────────
function getSetting(string $key, string $default = ''): string
{
    static $cache = [];
    if (!isset($cache[$key])) {
        $db = Database::getInstance();
        $val = $db->fetchColumn("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        $cache[$key] = $val !== false ? (string)$val : $default;
    }
    return $cache[$key];
}