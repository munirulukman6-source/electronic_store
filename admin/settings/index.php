<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'classes/Order.php';
require_once ROOT_PATH . 'classes/Cart.php';
require_once ROOT_PATH . 'classes/Inventory.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
requireAdmin();

$db = Database::getInstance();

// ── Process POST BEFORE any HTML output ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $skip = ['csrf_token', 'action', 'submit'];
    foreach ($_POST as $key => $val) {
        if (in_array($key, $skip)) continue;
        $exists = $db->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        if ($exists) $db->update('settings', ['setting_value' => is_array($val) ? json_encode($val) : $val], 'setting_key = ?', [$key]);
        else          $db->insert('settings', ['setting_key' => $key, 'setting_value' => $val]);
    }
    setFlash('success', 'Settings saved successfully.');
    redirect(BASE_URL . 'admin/settings/');
}

// ── Now it's safe to load the sidebar and render the page ────────
$pageTitle  = 'Settings';
$breadcrumb = [['label' => 'Settings', 'active' => true]];

require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/views/layouts/admin_sidebar.php';

$allSettings = $db->fetchAll("SELECT setting_key, setting_value, setting_group, label, description, input_type FROM settings ORDER BY setting_group, id");
$grouped = [];
foreach ($allSettings as $s) $grouped[$s['setting_group']][] = $s;

$groupLabels = ['general'=>'General','payment'=>'Payment & Tax','display'=>'Display','inventory'=>'Inventory','orders'=>'Orders','reviews'=>'Reviews','loyalty'=>'Loyalty','email'=>'Email (SMTP)','newsletter'=>'Newsletter','analytics'=>'Analytics','compare'=>'Comparison','wishlist'=>'Wishlist'];
?>

<div class="page-header"><h4><i class="fas fa-cog me-2"></i>System Settings</h4><p>Configure your ElectroStore application</p></div>

<form method="POST">
    <?= csrfField() ?>
    <div class="row g-3">
        <div class="col-lg-3">
            <div class="nav flex-column nav-pills sticky-top" style="top:80px" id="settingsTabs">
                <?php $first = true; foreach ($grouped as $group => $settings): ?>
                <button type="button" class="nav-link text-start <?= $first ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#grp-<?= $group ?>">
                    <?= clean($groupLabels[$group] ?? ucfirst($group)) ?>
                </button>
                <?php $first = false; endforeach; ?>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="tab-content">
                <?php $first = true; foreach ($grouped as $group => $settings): ?>
                <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="grp-<?= $group ?>">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-0 fw-semibold bg-transparent">
                            <i class="fas fa-sliders-h me-2 text-primary"></i><?= clean($groupLabels[$group] ?? ucfirst($group)) ?> Settings
                        </div>
                        <div class="card-body">
                            <?php foreach ($settings as $s): ?>
                            <div class="mb-3">
                                <label class="form-label"><?= clean($s['label'] ?? ucwords(str_replace('_',' ',$s['setting_key']))) ?></label>
                                <?php if ($s['input_type'] === 'textarea'): ?>
                                    <textarea name="<?= $s['setting_key'] ?>" class="form-control" rows="3"><?= clean($s['setting_value'] ?? '') ?></textarea>
                                <?php elseif ($s['input_type'] === 'select'): ?>
                                    <select name="<?= $s['setting_key'] ?>" class="form-select">
                                        <option value="0" <?= ($s['setting_value'] ?? '') === '0' ? 'selected' : '' ?>>Disabled</option>
                                        <option value="1" <?= ($s['setting_value'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option>
                                    </select>
                                <?php elseif ($s['input_type'] === 'checkbox' || in_array($s['setting_key'], ['enable_reviews','enable_wishlist','enable_compare','enable_newsletter','tax_inclusive','review_approval','maintenance_mode','dark_mode_default','smtp_secure'])): ?>
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="<?= $s['setting_key'] ?>" value="0">
                                        <input class="form-check-input" type="checkbox" name="<?= $s['setting_key'] ?>" value="1" <?= ($s['setting_value'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label text-muted small"><?= clean($s['description'] ?? 'Toggle this setting') ?></label>
                                    </div>
                                <?php elseif ($s['input_type'] === 'password' || str_contains($s['setting_key'], '_pass')): ?>
                                    <input type="password" name="<?= $s['setting_key'] ?>" class="form-control" value="<?= clean($s['setting_value'] ?? '') ?>" placeholder="••••••••">
                                <?php elseif ($s['input_type'] === 'number' || str_contains($s['setting_key'], '_rate') || str_contains($s['setting_key'], '_port') || str_contains($s['setting_key'], '_per_page') || str_contains($s['setting_key'], '_ratio') || str_contains($s['setting_key'], 'threshold') || str_contains($s['setting_key'], '_max')): ?>
                                    <input type="number" name="<?= $s['setting_key'] ?>" class="form-control" value="<?= clean($s['setting_value'] ?? '') ?>" step="0.01">
                                <?php else: ?>
                                    <input type="text" name="<?= $s['setting_key'] ?>" class="form-control" value="<?= clean($s['setting_value'] ?? '') ?>">
                                <?php endif; ?>
                                <?php if ($s['description'] && !in_array($s['input_type'] ?? '', ['checkbox'])): ?>
                                    <div class="form-text"><?= clean($s['description']) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php $first = false; endforeach; ?>
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary px-5 fw-semibold"><i class="fas fa-save me-2"></i>Save All Settings</button>
            </div>
        </div>
    </div>
</form>

</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="<?= ASSETS_URL ?>js/admin.js"></script>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrfToken() ?>';</script>
</body></html>