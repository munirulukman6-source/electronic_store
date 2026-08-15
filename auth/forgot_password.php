<?php
$pageTitle = 'Forgot Password';
require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/User.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
if (isLoggedIn()) redirect(BASE_URL);

$step    = get('step', 'request');   // request | reset
$token   = get('token', '');
$message = '';
$msgType = 'info';

// ── Step 1: request reset email ──────────────────────────────────
if ($step === 'request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $userModel = new User();
    $result    = $userModel->forgotPassword(post('email'));
    if ($result['success']) {
        // In a real app, email the link. For XAMPP demo we show it.
        $resetUrl  = BASE_URL . 'auth/forgot_password.php?step=reset&token=' . $result['token'];
        $message   = 'Reset link generated! <strong>Demo mode:</strong> <a href="' . $resetUrl . '" class="alert-link">Click here to reset your password</a>';
        $msgType   = 'success';
    } else {
        $message = $result['message'];
        $msgType = 'danger';
    }
}

// ── Step 2: reset password ───────────────────────────────────────
if ($step === 'reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $newPass = post('password');
    $confirm = post('confirm_password');
    if ($newPass !== $confirm) {
        $message = 'Passwords do not match.'; $msgType = 'danger';
    } elseif ($pwdErrors = validatePassword($newPass)) {
        $message = implode('. ', $pwdErrors); $msgType = 'danger';
    } else {
        $userModel = new User();
        $result    = $userModel->resetPassword(post('token'), $newPass);
        $message   = $result['message'];
        $msgType   = $result['success'] ? 'success' : 'danger';
        if ($result['success']) {
            setFlash('success', 'Password reset! Please sign in with your new password.');
            redirect(BASE_URL . 'auth/login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" id="htmlRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — <?= getSetting('site_name', APP_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <a href="<?= BASE_URL ?>" class="text-decoration-none">
                <i class="fas fa-bolt text-warning"></i>
                <span style="color:var(--es-text)"><?= getSetting('site_name', APP_NAME) ?></span>
            </a>
        </div>

        <?php if ($step === 'reset' && $token): ?>
        <!-- ── Step 2: Set new password ── -->
        <h5 class="fw-bold text-center mb-1">Set New Password</h5>
        <p class="text-muted text-center small mb-4">Choose a strong password for your account</p>

        <?php if ($message): ?>
        <div class="alert alert-<?= $msgType ?> py-2 small"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <?= csrfField() ?>
            <input type="hidden" name="token" value="<?= clean($token) ?>">
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" id="newPass" class="form-control border-start-0 ps-0 border-end-0"
                           placeholder="Minimum 8 characters" required>
                    <button type="button" class="input-group-text bg-transparent"
                            onclick="togglePass('newPass','eye1')"><i class="fas fa-eye text-muted" id="eye1"></i></button>
                </div>
                <div id="pwdStrength" class="mt-1"></div>
            </div>
            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="confirm_password" id="confPass" class="form-control border-start-0 ps-0 border-end-0"
                           placeholder="Repeat new password" required>
                    <button type="button" class="input-group-text bg-transparent"
                            onclick="togglePass('confPass','eye2')"><i class="fas fa-eye text-muted" id="eye2"></i></button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                <i class="fas fa-key me-2"></i>Reset Password
            </button>
        </form>

        <?php else: ?>
        <!-- ── Step 1: Request reset link ── -->
        <h5 class="fw-bold text-center mb-1">Forgot Password?</h5>
        <p class="text-muted text-center small mb-4">Enter your email and we'll send a reset link</p>

        <?php if ($message): ?>
        <div class="alert alert-<?= $msgType ?> py-2 small"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <?= csrfField() ?>
            <div class="mb-4">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 ps-0"
                           placeholder="your@email.com" required autofocus
                           value="<?= clean($_POST['email'] ?? '') ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-semibold py-2 mb-3">
                <i class="fas fa-paper-plane me-2"></i>Send Reset Link
            </button>
        </form>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>auth/login.php" class="small text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>Back to Login
            </a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('htmlRoot').setAttribute('data-bs-theme', localStorage.getItem('es_theme') || 'light');
function togglePass(id, iconId) {
    const i = document.getElementById(id), ic = document.getElementById(iconId);
    i.type = i.type === 'password' ? 'text' : 'password';
    ic.className = i.type === 'password' ? 'fas fa-eye text-muted' : 'fas fa-eye-slash text-muted';
}
document.getElementById('newPass')?.addEventListener('input', function () {
    const v = this.value, el = document.getElementById('pwdStrength');
    if (!el) return;
    let s = 0;
    if (v.length >= 8)            s++;
    if (/[A-Z]/.test(v))         s++;
    if (/[0-9]/.test(v))         s++;
    if (/[^a-zA-Z0-9]/.test(v)) s++;
    const c = ['','#dc3545','#fd7e14','#ffc107','#198754'];
    const l = ['','Weak','Fair','Good','Strong'];
    el.innerHTML = s ? `<div class="progress" style="height:4px"><div class="progress-bar" style="width:${s*25}%;background:${c[s]}"></div></div><small style="color:${c[s]}">${l[s]}</small>` : '';
});
</script>
</body>
</html>
