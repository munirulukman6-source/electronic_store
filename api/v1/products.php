<?php
// api/v1/products.php — REST API for mobile app integration
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

require_once $_SERVER['DOCUMENT_ROOT'] . '/electronic_store/config/config.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/Product.php';
require_once ROOT_PATH . 'includes/functions.php';

// ── API Key authentication ────────────────────────────────────
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
$db     = Database::getInstance();
$validKey = $db->fetchColumn("SELECT setting_value FROM settings WHERE setting_key = 'api_key'");
if ($validKey && $apiKey !== $validKey) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid API key.']);
    exit;
}

$method   = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? 'products';
$id       = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($endpoint) {

    // ── GET /api/v1/products.php?endpoint=products ────────────
    case 'products':
        if ($method === 'GET') {
            $pModel  = new Product();
            $filters = [
                'search'       => $_GET['search']   ?? '',
                'category_slug'=> $_GET['category'] ?? '',
                'brand_slug'   => $_GET['brand']    ?? '',
                'min_price'    => $_GET['min_price'] ?? '',
                'max_price'    => $_GET['max_price'] ?? '',
                'sort'         => $_GET['sort']      ?? 'newest',
                'in_stock'     => $_GET['in_stock']  ?? '',
            ];
            $page   = max(1, (int)($_GET['page'] ?? 1));
            $limit  = min(50, max(1, (int)($_GET['limit'] ?? PRODUCTS_PER_PAGE)));
            $result = $pModel->getAll($filters, $page, $limit);

            // Format prices
            foreach ($result['data'] as &$p) {
                $p['price_formatted']     = formatPrice($p['price']);
                $p['eff_price_formatted'] = formatPrice($p['effective_price']);
                $p['primary_image_url']   = productImageUrl($p['primary_image'] ?? '');
            }
            echo json_encode(['success' => true, 'data' => $result]);
        }
        break;

    // ── GET /api/v1/products.php?endpoint=product&slug=... ───
    case 'product':
        if ($method === 'GET') {
            $slug  = $_GET['slug'] ?? '';
            $pModel= new Product();
            $p     = $slug ? $pModel->getBySlug($slug) : ($id ? $pModel->getById($id) : null);
            if (!$p) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Product not found.']); break; }
            $p['price_formatted']   = formatPrice($p['price']);
            $p['eff_price_formatted']= formatPrice($p['effective_price']);
            $p['images'] = array_map(fn($img) => array_merge($img, ['url' => productImageUrl($img['image_path'])]), $p['images']);
            echo json_encode(['success' => true, 'data' => $p]);
        }
        break;

    // ── GET /api/v1/products.php?endpoint=categories ─────────
    case 'categories':
        $cats = $db->fetchAll("SELECT id,category_name,slug,icon,description FROM categories WHERE status=1 ORDER BY sort_order,category_name");
        echo json_encode(['success' => true, 'data' => $cats]);
        break;

    // ── GET /api/v1/products.php?endpoint=brands ─────────────
    case 'brands':
        $brands = $db->fetchAll("SELECT id,brand_name,slug,logo,website FROM brands WHERE status=1 ORDER BY brand_name");
        echo json_encode(['success' => true, 'data' => $brands]);
        break;

    // ── GET /api/v1/products.php?endpoint=flash_sales ────────
    case 'flash_sales':
        $pModel = new Product();
        $sales  = $pModel->getActiveFlashSales();
        foreach ($sales as &$s) {
            $s['sale_price_fmt']     = formatPrice($s['sale_price']);
            $s['original_price_fmt'] = formatPrice($s['original_price']);
            $s['image_url']          = productImageUrl($s['primary_image'] ?? '');
        }
        echo json_encode(['success' => true, 'data' => $sales]);
        break;

    // ── GET /api/v1/products.php?endpoint=search ─────────────
    case 'search':
        $q = $_GET['q'] ?? '';
        if (strlen(trim($q)) < 2) { echo json_encode(['success'=>true,'data',[]]); break; }
        $pModel  = new Product();
        $results = $pModel->search($q, 20);
        foreach ($results as &$r) {
            $r['price_formatted'] = formatPrice($r['effective_price']);
            $r['image_url']       = productImageUrl($r['primary_image'] ?? '');
        }
        echo json_encode(['success' => true, 'data' => $results, 'query' => $q]);
        break;

    // ── GET /api/v1/products.php?endpoint=settings ───────────
    case 'settings':
        $settings = $db->fetchAll("SELECT setting_key, setting_value FROM settings WHERE is_public = 1");
        $flat = [];
        foreach ($settings as $s) $flat[$s['setting_key']] = $s['setting_value'];
        echo json_encode(['success' => true, 'data' => $flat]);
        break;

    // ── GET /api/v1/products.php?endpoint=currencies ─────────
    case 'currencies':
        $currs = $db->fetchAll("SELECT currency_code,currency_name,symbol,exchange_rate,is_default FROM currencies WHERE is_active=1");
        echo json_encode(['success' => true, 'data' => $currs]);
        break;

    // ── Healthcheck ───────────────────────────────────────────
    case 'health':
        echo json_encode([
            'success'   => true,
            'status'    => 'OK',
            'version'   => APP_VERSION,
            'timestamp' => date('c'),
            'currency'  => DEFAULT_CURRENCY,
        ]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => "Unknown endpoint: $endpoint",
            'available' => ['products','product','categories','brands','flash_sales','search','settings','currencies','health']]);
}
