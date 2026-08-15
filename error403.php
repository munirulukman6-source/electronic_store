<?php
// error403.php — Access Denied
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
http_response_code(403);
$pageTitle = '403 — Access Denied';
require_once ROOT_PATH . 'views/layouts/header.php';
?>
<div class="container py-5 text-center">
    <div class="py-5">
        <div class="display-1 fw-black text-danger mb-0" style="font-size:7rem;line-height:1;opacity:.12">403</div>
        <i class="fas fa-lock fa-4x text-danger mb-4 d-block"></i>
        <h2 class="fw-bold mb-2">Access Denied</h2>
        <p class="text-muted lead mb-4">You don't have permission to view this page.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?= BASE_URL ?>" class="btn btn-primary px-4"><i class="fas fa-home me-2"></i>Go Home</a>
            <?php if (!isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-outline-primary px-4"><i class="fas fa-sign-in-alt me-2"></i>Login</a>
            <?php endif; ?>
            <button onclick="history.back()" class="btn btn-outline-secondary px-4"><i class="fas fa-arrow-left me-2"></i>Go Back</button>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
