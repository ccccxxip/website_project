<?php
session_start();
$dataFile = 'data.json';
if (file_exists($dataFile)) {
    $data = json_decode(file_get_contents($dataFile), true);
    $products = $data['products'] ?? [];
} else {
    $products = [];
}

$category = $_GET['category'] ?? 'all';
$min_price = (int)($_GET['min_price'] ?? 0);
$max_price = (int)($_GET['max_price'] ?? 30000);
$selected_sizes = $_GET['size'] ?? [];

$filtered = array_filter($products, function($p) use ($category, $min_price, $max_price, $selected_sizes) {
    if ($category !== 'all' && $p['category'] !== $category) return false;
    if ($p['price'] < $min_price || $p['price'] > $max_price) return false;
    if (!empty($selected_sizes)) {
        $area = $p['area'];
        $match = false;
        foreach ($selected_sizes as $s) {
            if ($s === 'small' && $area <= 100) $match = true;
            if ($s === 'medium' && $area > 100 && $area <= 200) $match = true;
            if ($s === 'large' && $area > 200) $match = true;
        }
        if (!$match) return false;
    }
    return true;
});
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Макеты домов | Архитектурное бюро</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; background: #F4F4F2; color: #2C2C2A; overflow-x: hidden; }
        .app-wrapper { display: flex; min-height: 100vh; position: relative; }
        .side-menu { width: 300px; background-color: #FAFAF8; border-right: 1px solid #E6E4DD; padding: 2rem 1.5rem; display: flex; flex-direction: column; transition: transform 0.3s; z-index: 30; overflow-y: auto; }
        .logo-area { margin-bottom: 2rem; border-bottom: 1px solid #E8E5DE; padding-bottom: 1rem; }
        .logo { font-size: 1.6rem; font-weight: 600; color: #3A3A36; }
        .logo span { font-weight: 300; color: #8F8E89; }
        .logo-desc { font-size: 0.75rem; color: #7A7974; margin-top: 6px; }
        .section-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #9A9892; margin-bottom: 1rem; font-weight: 500; }
        .nav-links { display: flex; flex-direction: column; gap: 0.5rem; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 14px; font-weight: 500; color: #494946; transition: all 0.2s; cursor: pointer; text-decoration: none; }
        .nav-item svg { width: 22px; height: 22px; stroke: #6C6B66; stroke-width: 1.5; fill: none; }
        .nav-item.active { background-color: #EFEDE7; color: #2C2C2A; font-weight: 600; }
        .nav-item.active svg { stroke: #2C2C2A; }
        .nav-item:hover:not(.active) { background-color: #F0EFEA; }
        .filter-block { margin-top: 1rem; margin-bottom: 1.8rem; }
        .filter-title { font-size: 0.85rem; font-weight: 600; color: #4A4943; margin-bottom: 0.8rem; }
        .price-range { display: flex; gap: 0.8rem; margin-bottom: 0.8rem; }
        .range-input { flex: 1; background: #F1EFEA; border-radius: 30px; padding: 8px 12px; border: 1px solid #E2DFD8; font-size: 0.8rem; font-family: inherit; }
        input[type="range"] { width: 100%; height: 4px; -webkit-appearance: none; background: #DDD8CF; border-radius: 4px; }
        input[type="range"]::-webkit-slider-thumb { -webkit-appearance: none; width: 16px; height: 16px; border-radius: 50%; background: #5C5B54; cursor: pointer; }
        .range-labels { display: flex; justify-content: space-between; font-size: 0.7rem; color: #8B8982; margin-top: 6px; }
        .size-filters { display: flex; flex-direction: column; gap: 0.6rem; }
        .checkbox-item { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; color: #565550; cursor: pointer; }
        .checkbox-item input { width: 18px; height: 18px; accent-color: #9C9584; cursor: pointer; }
        .reset-filters { background: #F1EFEA; border: 1px solid #E2DFD8; padding: 10px 0; border-radius: 40px; font-weight: 500; font-size: 0.8rem; color: #5A5952; cursor: pointer; margin-top: 1rem; transition: 0.2s; width: 100%; text-align: center; text-decoration: none; display: block; }
        .reset-filters:hover { background: #E8E4DC; color: #2F2E2A; }
        .admin-link { background: #3A3A36; color: white !important; margin-bottom: 1rem; }
        .admin-link:hover { background: #2C2C2A; }
        .bottom-info { margin-top: auto; padding-top: 1.5rem; font-size: 0.7rem; color: #9A9892; border-top: 1px solid #EBE9E2; }
        .main-content { flex: 1; padding: 1.8rem 2.2rem; }
        .mobile-header { display: none; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .burger-btn { background: none; border: none; width: 44px; height: 44px; border-radius: 30px; display: flex; align-items: center; justify-content: center; background-color: #FAFAF8; border: 1px solid #E2DFD8; cursor: pointer; }
        .burger-icon { width: 24px; height: 20px; display: flex; flex-direction: column; justify-content: space-between; }
        .burger-icon span { display: block; height: 2px; width: 100%; background-color: #4B4A46; border-radius: 4px; }
        .section-title { font-size: 1.6rem; font-weight: 500; margin-bottom: 1rem; color: #3A3A36; }
        .filter-stats { font-size: 0.8rem; color: #7A7974; margin-bottom: 1.5rem; border-left: 3px solid #D9D4C8; padding-left: 12px; }
        .houses-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem; }
        .house-card { background: #FFFFFF; border-radius: 28px; overflow: hidden; transition: 0.25s; border: 1px solid #EBE8E1; }
        .house-card:hover { transform: translateY(-6px); box-shadow: 0 20px 30px -12px rgba(0,0,0,0.08); }
        .card-img { background-color: #E9E7E1; height: 200px; display: flex; align-items: center; justify-content: center; background-image: radial-gradient(circle at 10% 30%, #F1EFE9 2%, transparent 2.5%); background-size: 18px 18px; }
        .card-info { padding: 1.2rem; }
        .house-title { font-size: 1.35rem; font-weight: 600; margin-bottom: 0.3rem; color: #2F2F2C; }
        .house-desc { font-size: 0.85rem; color: #77756E; margin: 0.3rem 0; }
        .detail-row { display: flex; justify-content: space-between; font-size: 0.75rem; color: #8D8B83; margin: 0.5rem 0; }
        .price-row { display: flex; justify-content: space-between; align-items: baseline; margin-top: 0.8rem; }
        .price { font-weight: 700; font-size: 1.25rem; color: #4B4A43; }
        .btn-order { background: #F1EFEA; border: none; padding: 0.5rem 1.2rem; border-radius: 40px; font-weight: 500; font-size: 0.8rem; cursor: pointer; transition: 0.2s; }
        .btn-order:hover { background: #E4E1D9; }
        .menu-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); backdrop-filter: blur(2px); z-index: 25; opacity: 0; visibility: hidden; transition: 0.2s; }
        .menu-overlay.active { opacity: 1; visibility: visible; }
        footer { margin-top: 3rem; text-align: center; font-size: 0.7rem; color: #ABA9A2; padding-top: 1.5rem; border-top: 1px solid #E9E5DD; }
        @media (max-width: 800px) {
            .side-menu { position: fixed; top: 0; left: 0; height: 100%; transform: translateX(-100%); z-index: 40; }
            .side-menu.open { transform: translateX(0); }
            .mobile-header { display: flex; }
            .main-content { padding: 1rem; }
        }
    </style>
</head>
<body>
<div class="app-wrapper">
    <div class="menu-overlay" id="overlay"></div>
    <aside class="side-menu" id="sideMenu">
        <div class="logo-area">
            <div class="logo">house<span>forms</span></div>
            <div class="logo-desc">архитектурные макеты</div>
        </div>
        <div class="nav-section">
            <div class="section-label">Категории</div>
            <div class="nav-links">
                <a href="?category=all" class="nav-item <?= $category == 'all' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" stroke="currentColor"><path d="M3 9L12 3L21 9L12 15L3 9Z"/><path d="M5 10.5V18L12 22L19 18V10.5"/></svg>
                    <span>Все макеты</span>
                </a>
                <a href="?category=modern" class="nav-item <?= $category == 'modern' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" stroke="currentColor"><rect x="4" y="8" width="16" height="12" rx="1"/><path d="M8 20V12"/><path d="M16 20V12"/></svg>
                    <span>Современные</span>
                </a>
                <a href="?category=classic" class="nav-item <?= $category == 'classic' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" stroke="currentColor"><path d="M4 9L12 4L20 9V19H4V9Z"/><path d="M9 19V13H15V19"/></svg>
                    <span>Классические</span>
                </a>
                <a href="?category=eco" class="nav-item <?= $category == 'eco' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" stroke="currentColor"><path d="M12 3L5 9L5 19H19V9L12 3Z"/><path d="M9 13L12 16L15 13"/></svg>
                    <span>Эко-дизайн</span>
                </a>
            </div>
        </div>
        <div class="filter-block">
            <div class="filter-title">💰 Цена (₽)</div>
            <div class="price-range">
                <input type="number" id="minPrice" class="range-input" placeholder="от 0" value="<?= $min_price ?>">
                <input type="number" id="maxPrice" class="range-input" placeholder="до 30000" value="<?= $max_price ?>">
            </div>
            <div class="slider-container">
                <input type="range" id="priceSlider" min="0" max="30000" step="500" value="<?= $max_price ?>">
                <div class="range-labels"><span>0 ₽</span><span>15 000 ₽</span><span>30 000 ₽</span></div>
            </div>
        </div>
        <div class="filter-block">
            <div class="filter-title">📐 Площадь дома (м²)</div>
            <div class="size-filters" id="sizeFilterGroup">
                <label class="checkbox-item"><input type="checkbox" value="small" <?= in_array('small', $selected_sizes) ? 'checked' : '' ?>> до 100 м² — компактные</label>
                <label class="checkbox-item"><input type="checkbox" value="medium" <?= in_array('medium', $selected_sizes) ? 'checked' : '' ?>> 100–200 м² — средние</label>
                <label class="checkbox-item"><input type="checkbox" value="large" <?= in_array('large', $selected_sizes) ? 'checked' : '' ?>> от 200 м² — просторные</label>
            </div>
        </div>
        <?php if(isset($_SESSION['admin']) && $_SESSION['admin'] === true): ?>
            <a href="admin.php" class="reset-filters admin-link">🔧 Панель администратора</a>
            <a href="logout.php" class="reset-filters">🚪 Выйти</a>
        <?php else: ?>
            <a href="login.php" class="reset-filters">🔐 Вход для администратора</a>
        <?php endif; ?>
        <div class="bottom-info"><p>© 2025 — нейтральная архитектура</p></div>
    </aside>
    <main class="main-content">
        <div class="mobile-header">
            <button class="burger-btn" id="burgerBtn"><div class="burger-icon"><span></span><span></span><span></span></div></button>
            <div class="page-title-mobile">Макеты</div>
            <div style="width: 44px;"></div>
        </div>
        <div>
            <h1 class="section-title"><?= $category == 'all' ? 'Все макеты домов' : ($category == 'modern' ? 'Современные макеты' : ($category == 'classic' ? 'Классические макеты' : 'Эко-дизайн макеты')) ?></h1>
            <div class="filter-stats">Показано: <?= count($filtered) ?> макетов</div>
            <div class="houses-grid">
                <?php foreach($filtered as $p): ?>
                <div class="house-card">
                    <div class="card-img">
                        <svg width="80" height="80" viewBox="0 0 100 100" fill="none">
                            <?php if($p['category'] == 'modern'): ?>
                                <rect x="20" y="45" width="60" height="40" fill="#D9D4CA" stroke="#B2AD9F" stroke-width="1.2" rx="3"/>
                                <polygon points="15,48 50,20 85,48" fill="#CBC5B8" stroke="#A8A191"/>
                                <rect x="42" y="65" width="16" height="20" fill="#BCB5A6"/>
                            <?php elseif($p['category'] == 'classic'): ?>
                                <path d="M20,50 L50,28 L80,50 L80,75 L20,75 Z" fill="#DFD9CF" stroke="#B2AB9C"/>
                                <rect x="38" y="60" width="24" height="15" fill="#CFC8BB"/>
                                <circle cx="50" cy="43" r="3.5" fill="#C5BCAD"/>
                            <?php else: ?>
                                <path d="M25,58 L50,30 L75,58 L75,78 L25,78 Z" fill="#E0DCD1" stroke="#BAB29F"/>
                                <circle cx="50" cy="68" r="10" fill="#CEC6B6"/>
                                <path d="M50,30 L55,38 L45,38 Z" fill="#D2CABB"/>
                            <?php endif; ?>
                        </svg>
                    </div>
                    <div class="card-info">
                        <div class="house-title"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="house-desc"><?= htmlspecialchars($p['description']) ?></div>
                        <div class="detail-row"><span>📐 Площадь: <?= $p['area'] ?> м²</span><span><?= $p['area'] <= 100 ? 'компактный' : ($p['area'] <= 200 ? 'средний' : 'просторный') ?></span></div>
                        <div class="price-row"><span class="price"><?= number_format($p['price'], 0, '', ' ') ?> ₽</span><button class="btn-order" data-name="<?= htmlspecialchars($p['name']) ?>">Заказать макет</button></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(count($filtered) == 0): ?><div style="grid-column:1/-1; text-align:center; padding:3rem;">Ничего не найдено</div><?php endif; ?>
            </div>
        </div>
        <footer>макеты масштаб 1:100 | фильтрация по цене и площади</footer>
    </main>
</div>
<script>
function updateFilters() {
    const min = document.getElementById('minPrice').value;
    const max = document.getElementById('maxPrice').value;
    const cat = '<?= $category ?>';
    const sizes = Array.from(document.querySelectorAll('#sizeFilterGroup input:checked')).map(cb => cb.value);
    let url = `?category=${cat}&min_price=${min}&max_price=${max}`;
    sizes.forEach(s => url += `&size[]=${s}`);
    location.href = url;
}
document.getElementById('minPrice')?.addEventListener('change', updateFilters);
document.getElementById('maxPrice')?.addEventListener('change', updateFilters);
document.getElementById('priceSlider')?.addEventListener('input', e => { document.getElementById('maxPrice').value = e.target.value; updateFilters(); });
document.querySelectorAll('#sizeFilterGroup input').forEach(cb => cb.addEventListener('change', updateFilters));
document.querySelectorAll('.btn-order').forEach(btn => btn.addEventListener('click', () => alert('✅ Заявка принята! Свяжемся с вами.')));
const burger = document.getElementById('burgerBtn');
const menu = document.getElementById('sideMenu');
const overlay = document.getElementById('overlay');
burger?.addEventListener('click', () => { menu.classList.toggle('open'); overlay.classList.toggle('active'); });
overlay?.addEventListener('click', () => { menu.classList.remove('open'); overlay.classList.remove('active'); });
</script>
</body>
</html>