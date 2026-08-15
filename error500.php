<?php
// error500.php — Internal Server Error
http_response_code(500);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>500 — Server Error | ElectroStore</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container py-5 text-center">
    <div class="py-5">
        <div class="display-1 fw-black text-warning mb-0" style="font-size:7rem;line-height:1;opacity:.12">500</div>
        <i class="fas fa-exclamation-triangle fa-4x text-warning mb-4 d-block"></i>
        <h2 class="fw-bold mb-2">Internal Server Error</h2>
        <p class="text-muted lead mb-4">Something went wrong on our end. Please try again shortly.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="/" class="btn btn-primary px-4"><i class="fas fa-home me-2"></i>Go Home</a>
            <button onclick="location.reload()" class="btn btn-outline-warning px-4"><i class="fas fa-redo me-2"></i>Try Again</button>
        </div>
        <p class="mt-4 small text-muted">If this keeps happening, contact <a href="mailto:support@electrostore.com">support@electrostore.com</a></p>
    </div>
</div>
</body>
</html>
