<?php
// ═══════════════════════════════════════════════════════════════
// TiCi NatureLab – Frontend Config
// Requires: tici-migration.sql to have been run once on the DB
// ═══════════════════════════════════════════════════════════════

define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_NAME',    'tici_shop');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'TiCi NatureLab');
define('SITE_URL',  'http://localhost/tici-shop');
define('CURRENCY',  '₹');

// ── Session ────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

// ── PDO singleton ──────────────────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET,
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
                 PDO::ATTR_EMULATE_PREPARES=>true]
            );
        } catch (PDOException $e) {
            die('<p style="color:red;padding:20px;font-family:monospace">DB Error: '
                .htmlspecialchars($e->getMessage()).'</p>');
        }
    }
    return $pdo;
}

// ── Session helpers ────────────────────────────────────────────
function getSessionId(): string {
    if (empty($_SESSION['shop_sid'])) $_SESSION['shop_sid'] = bin2hex(random_bytes(16));
    return $_SESSION['shop_sid'];
}
function isLoggedIn(): bool    { return !empty($_SESSION['user_id']); }
function currentUser(): ?array { return $_SESSION['user'] ?? null; }

// ── Flash ──────────────────────────────────────────────────────
function setFlash(string $type, string $msg): void { $_SESSION['flash'] = compact('type','msg'); }
function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) { $f=$_SESSION['flash']; unset($_SESSION['flash']); return $f; }
    return null;
}

// ── Formatting ─────────────────────────────────────────────────
function h(string $s): string  { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function price(float $p): string { return '₹'.number_format($p,2); }
function discount(float $orig, float $sale): int {
    return $orig > 0 ? (int)round(($orig-$sale)/$orig*100) : 0;
}
function getStars(float $r): string {
    $s=''; for($i=1;$i<=5;$i++) $s.=$i<=$r?'★':($i-0.5<=$r?'⯨':'☆'); return $s;
}

// ── Settings ───────────────────────────────────────────────────
function getSetting(string $key, string $default=''): string {
    try {
        $s=db()->prepare("SELECT setting_value FROM settings WHERE setting_key=?");
        $s->execute([$key]); return $s->fetchColumn()?:$default;
    } catch(PDOException $e){ return $default; }
}

// ── Categories — deduped by slug ───────────────────────────────
function getCategories(): array {
    return db()->query(
        "SELECT c.* FROM categories c
         INNER JOIN (
             SELECT slug, MIN(id) AS keep_id
             FROM categories
             WHERE status=1 AND slug IS NOT NULL AND slug!=''
             GROUP BY slug
         ) u ON c.id=u.keep_id
         ORDER BY c.sort_order, c.name"
    )->fetchAll();
}

// ── Image helpers (works with both admin `image` + frontend `image_path`) ──
function imgCol(string $a='pi'): string {
    return "COALESCE($a.image_path, $a.image, '') AS thumb";
}
function imgJoin(string $a='pi', string $p='p'): string {
    return "LEFT JOIN product_images $a ON $a.product_id=$p.id AND $a.is_primary=1";
}

// ── Product helpers ────────────────────────────────────────────
function getFeaturedProducts(int $limit=8): array {
    return db()->query("SELECT p.*,".imgCol().",pp.difficulty,pp.light_requirement
        FROM products p ".imgJoin()."
        LEFT JOIN plant_properties pp ON pp.product_id=p.id
        WHERE p.status=1 AND p.featured=1
        ORDER BY p.created_at DESC LIMIT $limit")->fetchAll();
}
function getBestsellers(int $limit=8): array {
    return db()->query("SELECT p.*,".imgCol().",pp.difficulty,pp.light_requirement
        FROM products p ".imgJoin()."
        LEFT JOIN plant_properties pp ON pp.product_id=p.id
        WHERE p.status=1 AND p.bestseller=1
        ORDER BY p.created_at DESC LIMIT $limit")->fetchAll();
}
function getNewArrivals(int $limit=8): array {
    return db()->query("SELECT p.*,".imgCol().",pp.difficulty,pp.light_requirement
        FROM products p ".imgJoin()."
        LEFT JOIN plant_properties pp ON pp.product_id=p.id
        WHERE p.status=1 AND p.new_arrival=1
        ORDER BY p.created_at DESC LIMIT $limit")->fetchAll();
}

// ── Cart / Wishlist counts ─────────────────────────────────────
function getCartCount(): int {
    $db=db(); $uid=$_SESSION['user_id']??null; $sid=getSessionId();
    try {
        if($uid){ $s=$db->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE user_id=?"); $s->execute([$uid]); }
        else    { $s=$db->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE session_id=?"); $s->execute([$sid]); }
        return (int)$s->fetchColumn();
    } catch(PDOException $e){ return 0; }
}
function getWishlistCount(): int {
    if(!isLoggedIn()) return 0;
    try {
        $s=db()->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id=?");
        $s->execute([$_SESSION['user_id']]); return (int)$s->fetchColumn();
    } catch(PDOException $e){ return 0; }
}
