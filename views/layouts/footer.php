<!-- ── Footer ─────────────────────────────────────────────────── -->
<footer class="site-footer mt-5">
    <!-- Newsletter strip -->
    <div class="newsletter-strip py-4">
        <div class="container">
            <div class="row align-items-center g-3">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:1.4rem;">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-white">Subscribe & Save 10%</h5>
                            <small class="text-white text-opacity-75">Get exclusive flash deals & tech updates right in your inbox.</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <form class="d-flex gap-2" id="newsletterForm">
                        <input type="email" name="email" class="form-control" placeholder="Enter your email address…" required style="background:#fff;border:none;color:#0f172a;font-size:0.9rem;border-radius:10px;">
                        <button class="btn btn-warning px-4 text-nowrap fw-bold" type="submit">
                            <i class="fas fa-paper-plane me-1"></i>Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer main -->
    <div class="footer-main py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Brand & Contact -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="brand-icon-wrap" style="width:34px;height:34px;font-size:1rem;"><i class="fas fa-bolt"></i></span>
                        <span class="brand-name text-white fs-5 fw-extrabold"><?= getSetting('site_name', APP_NAME) ?></span>
                    </div>
                    <p class="footer-desc mb-3">
                        <?= getSetting('site_tagline', APP_TAGLINE) ?>. Genuine gadgets, authentic warranties, and fastest doorstep delivery across Ghana.
                    </p>
                    <div class="d-flex gap-2 mb-4">
                        <a href="#" class="social-btn" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-btn" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-btn" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-btn" title="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="social-btn" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                    <div class="footer-contacts">
                        <div class="footer-contact-item mb-2">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            <span><?= getSetting('site_address', '14 Independence Avenue, Accra, Ghana') ?></span>
                        </div>
                        <div class="footer-contact-item mb-2">
                            <i class="fas fa-phone text-success me-2"></i>
                            <span><?= getSetting('site_phone', '+233 302 123 456') ?></span>
                        </div>
                        <div class="footer-contact-item">
                            <i class="fas fa-envelope text-warning me-2"></i>
                            <span><?= getSetting('site_email', 'info@electrostore.com.gh') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Shop links -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">Shop</h6>
                    <ul class="footer-links list-unstyled mb-0">
                        <?php
                        $productModel2 = new Product();
                        $footerCats    = array_slice($productModel2->getCategories(), 0, 6);
                        foreach ($footerCats as $fc): if ($fc['parent_id']) continue;
                        ?>
                        <li><a href="<?= BASE_URL ?>shop.php?category=<?= $fc['slug'] ?>"><?= clean($fc['category_name']) ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="<?= BASE_URL ?>shop.php?filter=flash_sale" class="text-warning fw-semibold"><i class="fas fa-bolt me-1"></i>Flash Sales</a></li>
                    </ul>
                </div>

                <!-- Account links -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">My Account</h6>
                    <ul class="footer-links list-unstyled mb-0">
                        <li><a href="<?= BASE_URL ?>auth/register.php">Create Account</a></li>
                        <li><a href="<?= BASE_URL ?>auth/login.php">Sign In</a></li>
                        <li><a href="<?= BASE_URL ?>customer/orders.php">Order History</a></li>
                        <li><a href="<?= BASE_URL ?>wishlist.php">My Wishlist</a></li>
                        <li><a href="<?= BASE_URL ?>cart.php">Shopping Cart</a></li>
                        <li><a href="<?= BASE_URL ?>customer/loyalty.php">Reward Points</a></li>
                    </ul>
                </div>

                <!-- Payment & Security -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="footer-heading">Payment Methods</h6>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="payment-badge"><i class="fas fa-mobile-alt me-1 text-warning"></i>MTN MoMo</span>
                        <span class="payment-badge"><i class="fas fa-mobile-alt me-1 text-danger"></i>Telecel</span>
                        <span class="payment-badge"><i class="fas fa-truck me-1 text-success"></i>Cash on Delivery</span>
                        <span class="payment-badge"><i class="fas fa-university me-1 text-primary"></i>Bank Transfer</span>
                    </div>
                    <h6 class="footer-heading">Security & Trust</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="trust-badge"><i class="fas fa-shield-alt text-success"></i><span>256-bit SSL</span></div>
                        <div class="trust-badge"><i class="fas fa-undo text-primary"></i><span>Easy Returns</span></div>
                        <div class="trust-badge"><i class="fas fa-certificate text-warning"></i><span>100% Genuine</span></div>
                        <div class="trust-badge"><i class="fas fa-headset text-info"></i><span>24/7 Support</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="footer-bottom py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 footer-copy-text">
                    &copy; <?= date('Y') ?> <strong class="text-white"><?= getSetting('site_name', APP_NAME) ?></strong>. All rights reserved.
                </div>
                <div class="col-md-6 text-md-end footer-copy-text">
                    Ghana's Premier Electronics eCommerce Platform
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- ── Back to top ─────────────────────────────────────────────── -->
<button id="backToTop" class="shadow" title="Back to top">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- ── Compare floating bar ───────────────────────────────────── -->
<div id="compareBar" class="compare-bar d-none">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <strong class="text-white small me-2"><i class="fas fa-exchange-alt me-1"></i>Compare:</strong>
            <div id="compareItems" class="d-flex gap-2"></div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>compare.php" class="btn btn-warning btn-sm fw-bold" id="compareLink">Compare Now</a>
            <button class="btn btn-outline-light btn-sm" id="clearCompare">Clear</button>
        </div>
    </div>
</div>

<!-- ── Quick View Modal ────────────────────────────────────────── -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="quickViewContent">
                <div class="text-center p-5"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
const BASE_URL     = '<?= BASE_URL ?>';
const CSRF_TOKEN   = '<?= csrfToken() ?>';
const IS_LOGGED_IN = <?= isLoggedIn() ? 'true' : 'false' ?>;
</script>
<script src="<?= ASSETS_URL ?>js/main.js"></script>
</body>
</html>
