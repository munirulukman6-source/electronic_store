<?php /* auth/register.php */
$pageTitle = 'Create Account';
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
if (isLoggedIn()) redirect(BASE_URL);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $full    = post('full_name');
    $email   = post('email');
    $uname   = post('username');
    $pass    = post('password');
    $confirm = post('confirm_password');
    $phone   = post('phone');

    if (strlen($full) < 3)        $errors[] = 'Full name must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email address required.';
    if (strlen($uname) < 4)       $errors[] = 'Username must be at least 4 characters.';
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $uname)) $errors[] = 'Username: letters, digits, underscores only.';
    $pwdErrors = validatePassword($pass);
    foreach ($pwdErrors as $e) $errors[] = $e;
    if ($pass !== $confirm)       $errors[] = 'Passwords do not match.';
    if (!isset($_POST['terms']))  $errors[] = 'You must accept the Terms & Conditions.';

    if (empty($errors)) {
        $userModel = new User();
        $result    = $userModel->register(['full_name' => $full, 'email' => $email, 'username' => $uname, 'password' => $pass, 'phone' => $phone]);
        if ($result['success']) {
            setFlash('success', 'Account created! Please sign in.');
            redirect(BASE_URL . 'auth/login.php');
        }
        $errors[] = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" id="htmlRoot">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Create Account — <?= getSetting('site_name', APP_NAME) ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/style.css">
</head>
<body>
<div class="auth-wrapper py-5">
    <div class="auth-card" style="max-width:520px">
        <div class="auth-logo mb-3">
            <a href="<?= BASE_URL ?>" class="text-decoration-none d-inline-flex align-items-center gap-2">
                <span class="brand-icon-wrap"><i class="fas fa-bolt"></i></span>
                <span class="brand-name"><?= getSetting('site_name', APP_NAME) ?></span>
            </a>
        </div>
        <h4 class="fw-extrabold text-center mb-1">Create an Account</h4>
        <p class="text-muted text-center small mb-4">Join our store for faster checkout and exclusive rewards</p>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2 rounded-3">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $e): ?><li class="small"><?= clean($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Full Name *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" name="full_name" class="form-control border-start-0" placeholder="Kwame Mensah" value="<?= clean($_POST['full_name'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body border-end-0 text-muted">@</span>
                        <input type="text" name="username" class="form-control border-start-0" placeholder="kwame_mensah" value="<?= clean($_POST['username'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body border-end-0"><i class="fas fa-phone text-muted"></i></span>
                        <input type="tel" name="phone" class="form-control border-start-0" placeholder="+233 24 123 4567" value="<?= clean($_POST['phone'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Email Address *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control border-start-0" placeholder="you@example.com" value="<?= clean($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password" id="passInput" class="form-control border-start-0 border-end-0" placeholder="Min 8 chars" required>
                        <button type="button" class="input-group-text bg-body" onclick="togglePass('passInput','eyePass')"><i class="fas fa-eye text-muted" id="eyePass"></i></button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="confirm_password" id="confirmInput" class="form-control border-start-0 border-end-0" placeholder="Repeat password" required>
                        <button type="button" class="input-group-text bg-body" onclick="togglePass('confirmInput','eyeConfirm')"><i class="fas fa-eye text-muted" id="eyeConfirm"></i></button>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="terms" id="terms" <?= isset($_POST['terms']) ? 'checked' : '' ?> required>
                        <label class="form-check-label small text-muted" for="terms">
                            I agree to the <a href="#" class="text-primary fw-bold text-decoration-none">Terms of Service</a> and <a href="#" class="text-primary fw-bold text-decoration-none">Privacy Policy</a>
                        </label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold py-3 mt-4 shadow-sm">
                <i class="fas fa-user-plus me-2"></i>Create Free Account
            </button>
        </form>

        <div class="text-center mt-3">
            <p class="small text-muted mb-0">Already registered? <a href="<?= BASE_URL ?>auth/login.php" class="fw-bold text-primary text-decoration-none">Sign In</a></p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass(id, iconId) {
    const inp = document.getElementById(id), icon = document.getElementById(iconId);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'password' ? 'fas fa-eye text-muted' : 'fas fa-eye-slash text-muted';
}
// Theme init
const t = localStorage.getItem('es_theme') || 'light';
document.getElementById('htmlRoot').setAttribute('data-bs-theme', t);
</script>
</body>
</html>
