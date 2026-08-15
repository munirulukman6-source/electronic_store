<?php
$pageTitle = 'Login';
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();

if (isLoggedIn()) redirect(BASE_URL . (isStaff() ? 'admin/' : ''));

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $userModel = new User();
    $result    = $userModel->login(post('email_or_username'), post('password'), isset($_POST['remember']));
    if ($result['success']) {
        $redirect = $_SESSION['redirect_after_login'] ?? null;
        unset($_SESSION['redirect_after_login']);
        redirect($redirect ?? BASE_URL . (isStaff() ? 'admin/' : ''));
    }
    $error = $result['message'];
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" id="htmlRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — <?= getSetting('site_name', APP_NAME) ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo mb-3">
            <a href="<?= BASE_URL ?>" class="text-decoration-none d-inline-flex align-items-center gap-2">
                <span class="brand-icon-wrap"><i class="fas fa-bolt"></i></span>
                <span class="brand-name"><?= getSetting('site_name', APP_NAME) ?></span>
            </a>
        </div>
        <h4 class="fw-extrabold text-center mb-1">Welcome Back</h4>
        <p class="text-muted text-center small mb-4">Sign in to manage orders, wishlist & loyalty perks</p>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show py-2 rounded-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= clean($error) ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?= displayFlash() ?>

        <form method="POST" action="" novalidate>
            <?= csrfField() ?>

            <div class="mb-3">
                <label class="form-label">Email or Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-body border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" name="email_or_username" class="form-control border-start-0"
                           placeholder="Enter your email or username" value="<?= clean($_POST['email_or_username'] ?? '') ?>" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0">Password</label>
                    <a href="<?= BASE_URL ?>auth/forgot_password.php" class="small text-primary text-decoration-none fw-semibold">Forgot password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-body border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control border-start-0 border-end-0"
                           placeholder="Enter your password" required>
                    <button type="button" class="input-group-text bg-body" id="togglePassword">
                        <i class="fas fa-eye text-muted" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" value="1">
                    <label class="form-check-label small text-muted" for="rememberMe">Remember me on this device</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold py-3 mb-3 shadow-sm">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In to Account
            </button>
        </form>

        <div class="text-center mt-3">
            <p class="small text-muted mb-0">Don't have an account yet?
                <a href="<?= BASE_URL ?>auth/register.php" class="fw-bold text-primary text-decoration-none">Create an Account</a>
            </p>
        </div>

        <div class="mt-4 p-3 bg-body-tertiary rounded-3 border">
            <p class="small fw-bold mb-2 text-muted text-center">Quick Demo Login</p>
            <div class="d-flex gap-2 flex-wrap justify-content-center">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillDemo('admin','admin123')">
                    <i class="fas fa-user-shield me-1"></i>Admin
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillDemo('customer1','admin123')">
                    <i class="fas fa-user me-1"></i>Customer
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillDemo('sales1','admin123')">
                    <i class="fas fa-briefcase me-1"></i>Sales
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('togglePassword')?.addEventListener('click', function () {
    const input = document.getElementById('passwordInput');
    const icon  = document.getElementById('eyeIcon');
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'fas fa-eye text-muted' : 'fas fa-eye-slash text-muted';
});
function fillDemo(user, pass) {
    document.querySelector('[name="email_or_username"]').value = user;
    document.querySelector('[name="password"]').value          = pass;
}
// Theme init
const t = localStorage.getItem('es_theme') || 'light';
document.getElementById('htmlRoot').setAttribute('data-bs-theme', t);
</script>
</body>
</html>
