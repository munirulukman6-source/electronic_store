# ⚡ ElectroStore Ghana — Electronic Devices E-Commerce System v2.0

**A complete PHP/MySQL e-commerce management system** for selling electronic devices, built with Bootstrap 5, Chart.js, and AJAX. Designed as a Final Year Computer Science project with production-ready features.

---

## 📋 Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Default Credentials](#default-credentials)
- [Project Structure](#project-structure)
- [System Modules](#system-modules)
- [API Documentation](#api-documentation)
- [Database Schema](#database-schema)
- [Screenshots](#screenshots)

---

## ✨ Features

### 🛒 Customer Features
- Product browsing with live AJAX search
- Advanced filtering (category, brand, price, rating, stock)
- Product comparison (up to 4 products)
- Product wishlist with toggle
- Shopping cart with session persistence and login merge
- Checkout with address management
- Multiple payment methods (Mobile Money, Cash on Delivery, Bank Transfer)
- Order tracking with real-time timeline
- Product reviews with star ratings, pros & cons
- Loyalty points system (earn & redeem)
- Customer tier system (Bronze → Silver → Gold → Platinum)
- Multi-currency display (GHS, USD, EUR, GBP)
- Dark / Light mode toggle
- Recently viewed products tracking
- Newsletter subscription

### 🔧 Admin Features
- Comprehensive dashboard with KPI cards and charts
- Product management with multi-image upload and spec builder
- Category & brand management with hierarchical categories
- Inventory management with stock-in, adjustments, and low-stock alerts
- Complete order management with status timeline
- Customer management with tier and loyalty oversight
- Coupon & promo code system (%, fixed, free shipping, buy-X-get-Y)
- Flash sales with countdown timers
- Product bundles with bundle pricing
- Return & refund management
- Supplier management
- Staff & employee management
- Sales reports with Chart.js visualizations
- Inventory reports with stock valuation
- Customer analytics
- Newsletter subscriber management
- System settings panel
- Audit trail & activity logs
- REST API for mobile app integration

---

## 🛠 Technology Stack

| Layer       | Technology                             |
|-------------|----------------------------------------|
| Backend     | PHP 8.0+ (OOP, MVC Pattern)           |
| Database    | MySQL 8.0+ with stored procedures     |
| Frontend    | Bootstrap 5.3, HTML5, CSS3            |
| JavaScript  | Vanilla JS + jQuery 3.7               |
| Charts      | Chart.js 4.4                          |
| UI Extras   | SweetAlert2, Toastr, Select2, Swiper  |
| Server      | Apache (XAMPP / LAMP)                  |
| Icons       | Font Awesome 6                         |

---

## 🚀 Installation

### Requirements
- XAMPP (PHP 8.0+, MySQL 8.0+, Apache)
- Web browser (Chrome, Firefox, Edge)

### Step-by-Step

**1. Install XAMPP**
Download and install XAMPP from [https://apachefriends.org](https://apachefriends.org)

**2. Copy project files**
```
Copy the `electronic_store` folder to:
C:\xampp\htdocs\electronic_store\     (Windows)
/opt/lampp/htdocs/electronic_store/  (Linux)
```

**3. Start XAMPP services**
- Start Apache
- Start MySQL

**4. Create database**
1. Open browser → `http://localhost/phpmyadmin`
2. Click **New** → name: `electronic_store` → click **Create**
3. Select the database → click **Import**
4. Choose file: `database/electronic_store.sql`
5. Click **Go**

**5. Configure database connection**
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'electronic_store');
define('DB_USER', 'root');       // Your MySQL username
define('DB_PASS', '');           // Your MySQL password (empty by default in XAMPP)
```

**6. Configure base URL**
Edit `config/config.php`:
```php
define('BASE_URL', 'http://localhost/electronic_store/');
```

**7. Set folder permissions**
Ensure these folders are writable:
```
uploads/
logs/
```

**8. Access the application**
```
Store:       http://localhost/electronic_store/
Admin Panel: http://localhost/electronic_store/admin/
API:         http://localhost/electronic_store/api/v1/products.php?endpoint=health
```

---

## 🔑 Default Credentials

| Role              | Username      | Password   | Email                       |
|-------------------|---------------|------------|-----------------------------|
| Administrator     | admin         | admin123   | admin@electrostore.com      |
| Inventory Manager | inventory1    | admin123   | inventory@electrostore.com  |
| Sales Officer     | sales1        | admin123   | sales@electrostore.com      |
| Customer 1        | customer1     | admin123   | customer1@gmail.com         |
| Customer 2        | customer2     | admin123   | customer2@gmail.com         |

> ⚠️ **Change all passwords immediately after installation in production!**

---

## 📁 Project Structure

```
electronic_store/
├── 📄 index.php                 Homepage
├── 📄 shop.php                  Product listing
├── 📄 product.php               Product detail
├── 📄 cart.php                  Shopping cart
├── 📄 checkout.php              Checkout process
├── 📄 wishlist.php              Customer wishlist
├── 📄 compare.php               Product comparison
├── 📄 order_success.php         Order confirmation
├── 📄 .htaccess                 Apache config & URL rewriting
│
├── 📁 config/
│   ├── config.php               App configuration
│   └── database.php             DB credentials
│
├── 📁 classes/
│   ├── Database.php             PDO singleton wrapper
│   ├── User.php                 Auth & user management
│   ├── Product.php              Product CRUD & search
│   ├── Order.php                Order lifecycle
│   ├── Cart.php                 Cart operations
│   └── Inventory.php            Stock + Wishlist + Loyalty + Notification + Report
│
├── 📁 includes/
│   └── functions.php            Global helper functions
│
├── 📁 views/
│   ├── layouts/
│   │   ├── header.php           Site header + navbar
│   │   ├── footer.php           Site footer + JS
│   │   └── admin_sidebar.php    Admin layout
│   └── products/
│       └── _card.php            Product card partial
│
├── 📁 auth/
│   ├── login.php                Login page
│   ├── register.php             Registration
│   ├── logout.php               Logout handler
│   └── forgot_password.php      Password reset
│
├── 📁 customer/
│   ├── profile.php              Customer profile
│   ├── orders.php               Order history
│   └── loyalty.php              Loyalty points
│
├── 📁 admin/
│   ├── index.php                Dashboard
│   ├── products/                Product management
│   ├── orders/                  Order management
│   ├── inventory/               Inventory control
│   ├── customers/               Customer management
│   ├── reports/                 Sales analytics
│   ├── coupons/                 Coupon management
│   ├── flash_sales/             Flash sale events
│   ├── returns/                 Return requests
│   ├── suppliers/               Supplier management
│   ├── staff/                   Staff management
│   └── settings/                System settings
│
├── 📁 ajax/
│   ├── search.php               Live product search
│   ├── cart.php                 Cart AJAX operations
│   ├── wishlist.php             Wishlist toggle
│   ├── notifications.php        Notification handler
│   ├── newsletter.php           Newsletter subscribe
│   ├── currency.php             Currency switcher
│   └── admin_action.php         Admin AJAX actions
│
├── 📁 api/
│   └── v1/
│       └── products.php         REST API endpoints
│
├── 📁 assets/
│   ├── css/
│   │   ├── style.css            Frontend styles + dark mode
│   │   └── admin.css            Admin panel styles
│   └── js/
│       ├── main.js              Frontend JavaScript
│       └── admin.js             Admin JavaScript
│
├── 📁 uploads/
│   ├── products/                Product images
│   └── avatars/                 User avatars
│
└── 📁 database/
    └── electronic_store.sql     Complete SQL script
```

---

## 📦 System Modules

| # | Module              | Features |
|---|---------------------|----------|
| 1 | User Authentication | Login, Register, Logout, Forgot Password, 2FA, Remember Me, Role-Based Access |
| 2 | Product Management  | CRUD, Multi-image upload, Spec builder, Tags, SEO, Flash sales, Bundles |
| 3 | Inventory Control   | Stock In, Adjustments, Low Stock Alerts, Supplier tracking, Reports |
| 4 | Order Management    | Place, Track, Update, Cancel, Invoice generation, Tracking timeline |
| 5 | Customer Management | Profiles, Tier system, Loyalty points, Purchase history |
| 6 | Shopping Cart       | Session + DB, Guest merge, Coupon apply, Points redemption |
| 7 | Payment System      | Mobile Money, Cash on Delivery, Bank Transfer simulation |
| 8 | Reporting           | Sales, Revenue, Inventory, Customer reports with Chart.js + Export |
| 9 | Coupon System       | Percentage, Fixed, Free Shipping, Buy-X-Get-Y, Usage limits |
| 10| Flash Sales         | Time-limited deals with live countdown timers |
| 11| Product Bundles     | Bundle pricing with automatic discount calculation |
| 12| Return Management   | Return requests, Approval workflow, Refund tracking |
| 13| Wishlist            | Save products, Move to cart, Price drop alerts |
| 14| Product Reviews     | Star ratings, Pros & Cons, Verified purchase badge |
| 15| Loyalty Points      | Earn on purchase, Redeem at checkout, Tier upgrades |
| 16| Multi-Currency      | GHS, USD, EUR, GBP with real-time display |
| 17| Dark/Light Mode     | CSS variables based, persisted in localStorage |
| 18| REST API            | JSON endpoints for mobile app integration |
| 19| Newsletter          | Subscribe/unsubscribe with email queue |
| 20| Supplier Management | Supplier profiles, inventory linkage |
| 21| Staff Management    | Employee records, departments, roles |
| 22| Audit Logs          | Complete action trail with IP logging |
| 23| Notifications       | In-app bell with badge counter |
| 24| Settings Panel      | Grouped configuration for all modules |

---

## 🌐 API Documentation

Base URL: `http://localhost/electronic_store/api/v1/products.php`

| Method | Endpoint               | Description             |
|--------|------------------------|-------------------------|
| GET    | `?endpoint=health`     | API health check        |
| GET    | `?endpoint=products`   | List products (paginated)|
| GET    | `?endpoint=product&slug=X` | Single product detail |
| GET    | `?endpoint=categories` | All categories          |
| GET    | `?endpoint=brands`     | All brands              |
| GET    | `?endpoint=flash_sales`| Active flash sales      |
| GET    | `?endpoint=search&q=X` | Search products         |
| GET    | `?endpoint=currencies` | Available currencies    |
| GET    | `?endpoint=settings`   | Public settings         |

---

## 🗄 Database Schema

**45 tables** covering all system modules:
`roles` · `users` · `customers` · `customer_tiers` · `staff` · `categories` · `brands` · `suppliers` · `products` · `product_images` · `product_tags` · `product_videos` · `product_faqs` · `inventory` · `flash_sales` · `product_bundles` · `bundle_items` · `coupons` · `coupon_usage` · `cart` · `cart_items` · `wishlist` · `orders` · `order_items` · `order_tracking` · `payments` · `invoices` · `returns` · `return_items` · `reviews` · `loyalty_points_log` · `notifications` · `newsletter_subscribers` · `email_queue` · `sms_queue` · `recently_viewed` · `abandoned_carts` · `search_logs` · `product_views` · `audit_logs` · `settings` · `backup_logs` · `currencies` · `taxes` · `shipping_zones`

---

## 📊 Enhanced Features Added

Beyond the original specification, this system adds:

1. **Wishlist Module** — Save products for later purchase
2. **Coupon System** — Promo codes with multiple discount types
3. **Flash Sales** — Time-limited deals with countdown timers
4. **Product Bundles** — Buy multiple products at a discounted price
5. **Loyalty Points** — Earn and redeem points with tier upgrades
6. **Multi-Currency** — Switch between GHS, USD, EUR, GBP
7. **Supplier Management** — Link inventory to suppliers
8. **Return Management** — Complete returns & refund workflow
9. **Dark Mode** — Full dark/light theme toggle
10. **REST API** — JSON endpoints for mobile app integration
11. **Product Tags** — Improve search discoverability
12. **Product Bundles** — Sell related products together
13. **Newsletter** — Email subscription management
14. **Audit Trail** — Comprehensive activity logging
15. **Staff Management** — Employee records and departments
16. **Recently Viewed** — Track customer browsing history
17. **Abandoned Cart Tracking** — Recovery analytics
18. **Search Analytics** — Log and analyze search terms
19. **Product FAQs** — Per-product FAQ accordion
20. **Product Videos** — Embed YouTube/Vimeo demos
21. **Shipping Zones** — Zone-based shipping rates
22. **Tax Management** — Configurable VAT rates
23. **Customer Tiers** — Bronze/Silver/Gold/Platinum VIP system
24. **System Settings** — GUI configuration for all modules
25. **Stored Procedures** — Atomic order placement and reports

---

## 🔒 Security Features

- Password hashing with `bcrypt` (cost factor 12)
- CSRF token protection on all forms and AJAX
- SQL injection prevention with PDO prepared statements
- XSS prevention with `htmlspecialchars` on all output
- Account lockout after 5 failed login attempts
- Remember-me with secure HTTP-only cookie tokens
- Role-based access control on every admin page
- Input sanitization via `clean()` helper
- Apache security headers (X-Frame-Options, X-XSS-Protection, etc.)
- Session regeneration on login

---

## 👨‍💻 Development Notes

- **Architecture**: MVC-inspired (Models in `/classes`, Views in `/views`, Controllers in page files)
- **OOP**: All business logic encapsulated in classes with PDO
- **AJAX**: Cart, wishlist, search, notifications use `fetch()` API
- **Charts**: Chart.js for dashboard, reports with canvas rendering
- **Responsive**: Bootstrap 5 grid, mobile-first design
- **Accessibility**: ARIA labels, semantic HTML, focus management

---

## 📝 License

This project is developed as a Final Year Computer Science project. For educational use. All rights reserved.

---

*Built with ❤️ using PHP 8 + MySQL + Bootstrap 5 | ElectroStore Ghana v2.0*
