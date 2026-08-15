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

// ── Auth guards (must happen BEFORE any HTML output) ─────────────
requireLogin();
if (!isCustomer()) redirect(BASE_URL . 'admin/');

// ── Now safe to load profile ────────────────────────────────────
$pageTitle = 'My Profile';
$userModel = new User();
$profile   = $userModel->getCustomerProfile($_SESSION['user_id']);
$errors    = [];
$success   = '';

// ── Handle profile update ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $action = post('action');

    if ($action === 'update_profile') {
        $errors = [];
        if (strlen(post('full_name')) < 3) $errors[] = 'Full name must be at least 3 characters.';
        if (!filter_var(post('email'), FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';

        if (empty($errors)) {
            // Avatar upload
            if (!empty($_FILES['avatar']['name'])) {
                $upload = uploadImage($_FILES['avatar'], 'avatars');
                if ($upload['success']) {
                    if ($profile['avatar']) deleteFile($profile['avatar']);
                    $userModel->updateAvatar($_SESSION['user_id'], $upload['path']);
                    $profile['avatar'] = $upload['path'];
                } else {
                    $errors[] = $upload['message'];
                }
            }

            if (empty($errors)) {
                $result = $userModel->updateProfile($_SESSION['user_id'], [
                    'full_name'    => post('full_name'),
                    'first_name'   => post('first_name'),
                    'last_name'    => post('last_name'),
                    'phone'        => post('phone'),
                    'address'      => post('address'),
                    'city'         => post('city'),
                    'state'        => post('state'),
                    'country'      => post('country'),
                    'postal_code'  => post('postal_code'),
                    'gender'       => post('gender'),
                    'date_of_birth'=> post('date_of_birth'),
                ]);
                setFlash('success', $result['message']);
                redirect(BASE_URL . 'customer/profile.php');
            }
        }
    }

    if ($action === 'change_password') {
        $result = $userModel->changePassword($_SESSION['user_id'], post('current_password'), post('new_password'));
        $result['success']
            ? (setFlash('success', $result['message']) & redirect(BASE_URL . 'customer/profile.php'))
            : ($errors[] = $result['message']);
    }

    if ($action === 'update_notifications') {
        $db = Database::getInstance();
        $db->update('customers', [
            'newsletter'         => isset($_POST['newsletter']) ? 1 : 0,
            'email_notifications'=> isset($_POST['email_notifications']) ? 1 : 0,
            'sms_notifications'  => isset($_POST['sms_notifications']) ? 1 : 0,
        ], 'user_id = ?', [$_SESSION['user_id']]);
        setFlash('success', 'Notification preferences saved.');
        redirect(BASE_URL . 'customer/profile.php');
    }
}

// Reload after any changes
$profile = $userModel->getCustomerProfile($_SESSION['user_id']);
$loyalty = new Loyalty();
$balance = $loyalty->getBalance($_SESSION['customer_id']);
$db      = Database::getInstance();
$orderStats = $db->fetchOne(
    "SELECT COUNT(*) AS total, COALESCE(SUM(total),0) AS spent FROM orders WHERE customer_id = ? AND payment_status='paid'",
    [$_SESSION['customer_id']]
);

// ── Now include the header (output starts) ───────────────────────
require_once ROOT_PATH . 'views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row g-4">

        <!-- Sidebar nav -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-3">
                <!-- Profile summary -->
                <div class="card-body text-center py-4">
                    <div class="position-relative d-inline-block mb-3">
                        <?php if (!empty($profile['avatar'])): ?>
                            <img src="<?= productImageUrl($profile['avatar']) ?>" class="rounded-circle object-fit-cover border border-3 border-primary"
                                 width="90" height="90" alt="Avatar">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary bg-opacity-15 d-flex align-items-center justify-content-center mx-auto border border-3 border-primary"
                                 style="width:90px;height:90px">
                                <i class="fas fa-user fa-2x text-primary"></i>
                            </div>
                        <?php endif; ?>
                        <label for="quickAvatarInput" class="position-absolute bottom-0 end-0 btn btn-sm btn-primary rounded-circle p-1"
                               style="width:28px;height:28px;cursor:pointer" title="Change photo">
                            <i class="fas fa-camera" style="font-size:.6rem"></i>
                        </label>
                        <form id="quickAvatarForm" method="POST" enctype="multipart/form-data" class="d-none">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="update_profile">
                            <input type="file" id="quickAvatarInput" name="avatar" accept="image/*"
                                   onchange="this.closest('form').submit()">
                        </form>
                    </div>
                    <div class="fw-bold fs-6"><?= clean($profile['full_name'] ?? '') ?></div>
                    <div class="small text-muted"><?= clean($profile['email'] ?? '') ?></div>
                    <div class="mt-2">
                        <span class="badge" style="background:<?= clean($profile['badge_color'] ?? '#6c757d') ?>">
                            <i class="fas <?= clean($profile['badge_icon'] ?? 'fa-medal') ?> me-1"></i>
                            <?= clean($profile['tier_name'] ?? 'Bronze') ?>
                        </span>
                    </div>
                </div>

                <!-- Stats strip -->
                <div class="card-footer bg-transparent border-0 pt-0 pb-3">
                    <div class="row g-1 text-center">
                        <div class="col-4 border-end">
                            <div class="fw-bold text-primary"><?= number_format($orderStats['total'] ?? 0) ?></div>
                            <div style="font-size:.65rem" class="text-muted">Orders</div>
                        </div>
                        <div class="col-4 border-end">
                            <div class="fw-bold text-success" style="font-size:.85rem"><?= formatPrice($orderStats['spent'] ?? 0) ?></div>
                            <div style="font-size:.65rem" class="text-muted">Spent</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-warning"><?= number_format($balance) ?></div>
                            <div style="font-size:.65rem" class="text-muted">Points</div>
                        </div>
                    </div>
                </div>

                <!-- Nav links -->
                <div class="list-group list-group-flush">
                    <a href="<?= BASE_URL ?>customer/profile.php" class="list-group-item list-group-item-action active"><i class="fas fa-user me-2"></i>My Profile</a>
                    <a href="<?= BASE_URL ?>customer/orders.php" class="list-group-item list-group-item-action"><i class="fas fa-box me-2 text-success"></i>My Orders</a>
                    <a href="<?= BASE_URL ?>wishlist.php" class="list-group-item list-group-item-action"><i class="fas fa-heart me-2 text-danger"></i>Wishlist</a>
                    <a href="<?= BASE_URL ?>customer/loyalty.php" class="list-group-item list-group-item-action"><i class="fas fa-coins me-2 text-warning"></i>Loyalty Points</a>
                    <a href="<?= BASE_URL ?>auth/logout.php" class="list-group-item list-group-item-action text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-lg-9">
            <h4 class="fw-bold mb-4"><i class="fas fa-user-cog me-2 text-primary"></i>Account Settings</h4>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>

            <!-- Tab nav -->
            <ul class="nav nav-tabs mb-4" id="profileTabs">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPersonal"><i class="fas fa-id-card me-1"></i>Personal Info</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabAddress"><i class="fas fa-map-marker-alt me-1"></i>Address</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPassword"><i class="fas fa-lock me-1"></i>Password</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabNotifs"><i class="fas fa-bell me-1"></i>Notifications</button></li>
            </ul>

            <div class="tab-content">
                <!-- Personal Info -->
                <div class="tab-pane fade show active" id="tabPersonal">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="update_profile">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-control" value="<?= clean($profile['first_name'] ?? '') ?>" required></div>
                                    <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control" value="<?= clean($profile['last_name'] ?? '') ?>"></div>
                                    <div class="col-12"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" value="<?= clean($profile['full_name'] ?? '') ?>" required></div>
                                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= clean($profile['email'] ?? '') ?>" required></div>
                                    <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" value="<?= clean($profile['phone'] ?? '') ?>"></div>
                                    <div class="col-md-6"><label class="form-label">Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="<?= clean($profile['date_of_birth'] ?? '') ?>"></div>
                                    <div class="col-md-6"><label class="form-label">Gender</label>
                                        <select name="gender" class="form-select">
                                            <option value="">Prefer not to say</option>
                                            <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other'] as $v=>$l): ?>
                                            <option value="<?= $v ?>" <?= ($profile['gender'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12"><label class="form-label">Profile Photo</label><input type="file" name="avatar" class="form-control" accept="image/*"><div class="form-text">JPG, PNG or WEBP — max 5 MB</div></div>
                                </div>
                                <div class="mt-4"><button type="submit" class="btn btn-primary fw-semibold px-5"><i class="fas fa-save me-2"></i>Save Changes</button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="tab-pane fade" id="tabAddress">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <form method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="update_profile">
                                <input type="hidden" name="full_name" value="<?= clean($profile['full_name'] ?? '') ?>">
                                <div class="row g-3">
                                    <div class="col-12"><label class="form-label">Street Address</label><textarea name="address" class="form-control" rows="2"><?= clean($profile['address'] ?? '') ?></textarea></div>
                                    <div class="col-md-6"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="<?= clean($profile['city'] ?? '') ?>"></div>
                                    <div class="col-md-6"><label class="form-label">Region / State</label><input type="text" name="state" class="form-control" value="<?= clean($profile['state'] ?? '') ?>"></div>
                                    <div class="col-md-6"><label class="form-label">Country</label>
                                        <select name="country" class="form-select">
                                            <option value="Ghana" <?= ($profile['country'] ?? '') === 'Ghana' ? 'selected' : '' ?>>Ghana</option>
                                            <option value="Nigeria">Nigeria</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Postal Code</label><input type="text" name="postal_code" class="form-control" value="<?= clean($profile['postal_code'] ?? '') ?>"></div>
                                </div>
                                <div class="mt-4"><button type="submit" class="btn btn-primary fw-semibold px-5"><i class="fas fa-save me-2"></i>Save Address</button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="tab-pane fade" id="tabPassword">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <form method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="change_password">
                                <div class="row g-3">
                                    <div class="col-12"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                                    <div class="col-md-6"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required></div>
                                    <div class="col-md-6"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
                                </div>
                                <div class="mt-4"><button type="submit" class="btn btn-warning fw-semibold px-5"><i class="fas fa-key me-2"></i>Change Password</button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="tab-pane fade" id="tabNotifs">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <form method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="update_notifications">
                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex justify-content-between align-items-center p-3 border rounded-3">
                                        <div><i class="fas fa-envelope me-2"></i><span class="fw-semibold small">Newsletter & Promotional Emails</span></div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="newsletter" value="1" <?= ($profile['newsletter'] ?? 0) ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center p-3 border rounded-3">
                                        <div><i class="fas fa-bell me-2"></i><span class="fw-semibold small">Email Notifications</span></div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="email_notifications" value="1" <?= ($profile['email_notifications'] ?? 0) ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center p-3 border rounded-3">
                                        <div><i class="fas fa-mobile-alt me-2"></i><span class="fw-semibold small">SMS Notifications</span></div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="sms_notifications" value="1" <?= ($profile['sms_notifications'] ?? 0) ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4"><button type="submit" class="btn btn-primary fw-semibold px-5"><i class="fas fa-save me-2"></i>Save Preferences</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>