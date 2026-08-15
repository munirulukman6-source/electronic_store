<?php
// error404.php
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'includes/functions.php';
bootstrap();
http_response_code(404);
$pageTitle = '404 — Page Not Found';
require_once ROOT_PATH . 'views/layouts/header.php';
?>
<div class="container py-5 text-center">
    <div class="py-5">
        <div class="display-1 fw-black text-primary mb-0" style="font-size:7rem;line-height:1;opacity:.15">404</div>
        <i class="fas fa-search fa-4x text-primary mb-4 d-block"></i>
        <h2 class="fw-bold mb-2">Page Not Found</h2>
        <p class="text-muted lead mb-4">The page you're looking for doesn't exist or has been moved.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?= BASE_URL ?>" class="btn btn-primary px-4"><i class="fas fa-home me-2"></i>Go Home</a>
            <a href="<?= BASE_URL ?>shop.php" class="btn btn-outline-secondary px-4"><i class="fas fa-store me-2"></i>Browse Shop</a>
            <button onclick="history.back()" class="btn btn-outline-secondary px-4"><i class="fas fa-arrow-left me-2"></i>Go Back</button>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
