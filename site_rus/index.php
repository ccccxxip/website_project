<?php
session_start();
require_once __DIR__ . '/config.php';

// Получение параметров фильтрации
$selectedCategories = $_GET['categories'] ?? [];
if (!is_array($selectedCategories)) $selectedCategories = [$selectedCategories];

$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : 9999999;

// Получаем все товары
try {
    $stmt = $pdo->query("SELECT * FROM models ORDER BY id DESC");
    $allProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    $allProducts = [];
}

// Фильтрация по цене
$products = array_filter($allProducts, function($p) use ($min_price, $max_price) {
    return $p['price'] >= $min_price && $p['price'] <= $max_price;
});

// Фильтрация по категориям
if (!empty($selectedCategories)) {
    $filtered = [];
    foreach ($products as $product) {
        if (!empty($product['category'])) {
            $productCats = explode(',', $product['category']);
            $productCats = array_map('trim', $productCats);
            foreach ($selectedCategories as $selectedCat) {
                if (in_array($selectedCat, $productCats)) {
                    $filtered[] = $product;
                    break;
                }
            }
        }
    }
    $products = $filtered;
}

$products = array_values($products);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Макетная студия</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            background: #F2F0ED;
            color: #2C2B28;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        ::-webkit-scrollbar {
            width: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #E8E6E1;
        }
        ::-webkit-scrollbar-thumb {
            background: #C2BFB8;
            border-radius: 4px;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 300px;
            background: #FFFFFF;
            border-right: 1px solid #EBE9E4;
            padding: 2rem 1.5rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }

        .logo {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #EBE9E4;
        }

        .logo h1 {
            font-size: 1.2rem;
            font-weight: 450;
            letter-spacing: 3px;
            color: #2C2B28;
        }

        .logo p {
            font-size: 0.65rem;
            color: #A8A59E;
            margin-top: 0.3rem;
            letter-spacing: 0.5px;
        }

        .filter-section {
            margin-bottom: 1.8rem;
        }

        .filter-title {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 450;
            color: #A8A59E;
            margin-bottom: 0.9rem;
        }

        .category-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .category-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            font-size: 0.8rem;
            color: #4A4945;
            padding: 0.3rem 0;
            transition: color 0.2s;
        }

        .category-item:hover {
            color: #1A1A18;
        }

        .category-item input {
            width: 15px;
            height: 15px;
            cursor: pointer;
            accent-color: #8B887F;
        }

        .price-inputs {
            display: flex;
            gap: 0.5rem;
            width: 100%;
        }

        .price-inputs input {
            flex: 1;
            min-width: 0;
            padding: 0.6rem 0.5rem;
            border: 1px solid #E3E1DB;
            background: #FFFFFF;
            font-size: 0.75rem;
            font-family: inherit;
            outline: none;
            border-radius: 12px;
            transition: border 0.2s;
        }

        .price-inputs input:focus {
            border-color: #C2BFB8;
        }

        .price-inputs input::placeholder {
            color: #C2BFB8;
            font-size: 0.7rem;
        }

        .filter-buttons {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .btn-apply {
            flex: 2;
            padding: 0.6rem;
            background: #2C2B28;
            border: none;
            font-size: 0.7rem;
            font-family: inherit;
            cursor: pointer;
            color: #FFFFFF;
            border-radius: 30px;
            transition: background 0.2s;
        }

        .btn-apply:hover {
            background: #1A1A18;
        }

        .btn-reset {
            flex: 1;
            padding: 0.6rem;
            background: transparent;
            border: 1px solid #E3E1DB;
            font-size: 0.7rem;
            font-family: inherit;
            cursor: pointer;
            color: #6B6963;
            border-radius: 30px;
            transition: all 0.2s;
        }

        .btn-reset:hover {
            background: #F5F4F1;
            border-color: #C2BFB8;
            color: #2C2B28;
        }

        .admin-link {
            display: block;
            text-align: center;
            text-decoration: none;
            padding: 0.6rem;
            background: #2C2B28;
            color: #FFFFFF;
            font-size: 0.7rem;
            margin-top: 0.5rem;
            border-radius: 30px;
            transition: background 0.2s;
        }

        .admin-link:hover {
            background: #1A1A18;
        }

        .admin-link-outline {
            display: block;
            text-align: center;
            text-decoration: none;
            padding: 0.6rem;
            background: transparent;
            color: #6B6963;
            font-size: 0.7rem;
            border: 1px solid #E3E1DB;
            margin-top: 0.5rem;
            border-radius: 30px;
            transition: all 0.2s;
        }

        .admin-link-outline:hover {
            border-color: #C2BFB8;
            color: #2C2B28;
            background: #F5F4F1;
        }

        .main-content {
            flex: 1;
            margin-left: 300px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.4rem;
            font-weight: 400;
            letter-spacing: -0.3px;
            color: #2C2B28;
        }

        .stats {
            font-size: 0.7rem;
            color: #A8A59E;
            margin-top: 0.4rem;
        }

        .active-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #EBE9E4;
        }

        .active-filters-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #A8A59E;
        }

        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            background: #F5F4F1;
            border-radius: 30px;
            font-size: 0.7rem;
            color: #4A4945;
        }

        .filter-tag-remove {
            cursor: pointer;
            font-size: 0.8rem;
            color: #A8A59E;
            transition: color 0.2s;
            line-height: 1;
        }

        .filter-tag-remove:hover {
            color: #DC3545;
        }

        .clear-all {
            font-size: 0.65rem;
            color: #A8A59E;
            cursor: pointer;
            text-decoration: underline;
            transition: color 0.2s;
        }

        .clear-all:hover {
            color: #DC3545;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.8rem;
            flex: 1;
        }

        .card {
            background: #FFFFFF;
            overflow: hidden;
            border-radius: 24px;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -12px rgba(0, 0, 0, 0.08);
        }

        .slider {
            position: relative;
            height: 240px;
            background: #F0EFEC;
            overflow: hidden;
        }

        .slider-images {
            display: flex;
            height: 100%;
            transition: transform 0.3s ease-out;
        }

        .slider-image {
            min-width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
            user-select: none;
            -webkit-user-drag: none;
        }

        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(44, 43, 40, 0.4);
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            backdrop-filter: blur(4px);
            transition: background 0.2s;
            z-index: 10;
        }

        .slider-btn:hover {
            background: rgba(44, 43, 40, 0.6);
        }

        .slider-prev { left: 12px; }
        .slider-next { right: 12px; }

        .slider-dots {
            position: absolute;
            bottom: 12px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 8px;
            z-index: 10;
        }

        .slider-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.2s;
        }

        .slider-dot.active {
            background: white;
            width: 20px;
            border-radius: 3px;
        }

        .no-image {
            height: 240px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F0EFEC;
            color: #B8B5AE;
            font-size: 0.7rem;
        }

        .card-content {
            padding: 1.3rem;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 470;
            margin-bottom: 0.5rem;
            color: #2C2B28;
        }

        .card-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
        }

        .card-category {
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #A8A59E;
        }

        .card-description {
            font-size: 0.75rem;
            color: #6B6963;
            line-height: 1.45;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .read-more {
            font-size: 0.65rem;
            color: #A8A59E;
            cursor: pointer;
            margin-top: -0.5rem;
            margin-bottom: 0.5rem;
            display: inline-block;
            transition: color 0.2s;
        }

        .read-more:hover {
            color: #2C2B28;
            text-decoration: underline;
        }

        .card-details {
            display: flex;
            justify-content: space-between;
            font-size: 0.65rem;
            color: #A8A59E;
            margin-bottom: 1rem;
            padding-top: 0.6rem;
            border-top: 1px solid #F0EFEC;
        }

        .deadline {
            color: #9B9A93;
        }

        .card-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price {
            font-size: 1.1rem;
            font-weight: 480;
            color: #2C2B28;
        }

        .btn-order {
            background: #F5F4F1;
            color: #4A4945;
            border: none;
            padding: 0.4rem 1.1rem;
            font-size: 0.7rem;
            font-family: inherit;
            cursor: pointer;
            border-radius: 30px;
            transition: all 0.2s;
        }

        .btn-order:hover {
            background: #EBE9E4;
            color: #2C2B28;
        }

        /* Модальное окно для картинок */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(44, 43, 40, 0.96);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .modal.active {
            display: flex;
        }

        .modal-img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 8px;
        }

        .modal-close {
            position: absolute;
            top: 24px;
            right: 32px;
            color: white;
            font-size: 32px;
            cursor: pointer;
            font-weight: 300;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .modal-close:hover {
            opacity: 1;
        }

        /* Модальное окно для описания */
        .desc-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(44, 43, 40, 0.8);
            backdrop-filter: blur(4px);
            z-index: 1001;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .desc-modal.active {
            display: flex;
        }

        .desc-modal-content {
            background: #FFFFFF;
            border-radius: 24px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            padding: 2rem;
            position: relative;
            cursor: default;
        }

        .desc-modal-content h3 {
            font-size: 1.2rem;
            font-weight: 470;
            margin-bottom: 1rem;
            padding-right: 2rem;
            color: #2C2B28;
        }

        .desc-modal-content p {
            font-size: 0.85rem;
            color: #4A4945;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .desc-modal-close {
            position: absolute;
            top: 16px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #A8A59E;
            transition: color 0.2s;
        }

        .desc-modal-close:hover {
            color: #2C2B28;
        }

        .desc-modal-details {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid #EBE9E4;
        }

        .desc-modal-detail {
            font-size: 0.75rem;
            color: #A8A59E;
        }

        .desc-modal-detail strong {
            color: #4A4945;
        }

        /* Модальное окно для заказа */
        .order-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(44, 43, 40, 0.8);
            backdrop-filter: blur(4px);
            z-index: 1002;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .order-modal.active {
            display: flex;
        }

        .order-modal-content {
            background: #FFFFFF;
            border-radius: 24px;
            max-width: 380px;
            width: 90%;
            padding: 2rem;
            position: relative;
            cursor: default;
            text-align: center;
        }

        .order-modal-content h3 {
            font-size: 1.2rem;
            font-weight: 470;
            margin-bottom: 0.5rem;
            color: #2C2B28;
        }

        .order-modal-subtitle {
            font-size: 0.7rem;
            color: #A8A59E;
            margin-bottom: 1.5rem;
        }

        .order-modal-contacts {
            margin-bottom: 1.5rem;
        }

        .order-modal-contact-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: #FAFAF8;
            border-radius: 16px;
            margin-bottom: 0.75rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .order-modal-contact-item:hover {
            background: #F5F4F1;
            transform: translateY(-2px);
        }

        .order-modal-contact-icon {
            font-size: 1.3rem;
        }

        .order-modal-contact-info {
            text-align: left;
        }

        .order-modal-contact-title {
            font-size: 0.7rem;
            font-weight: 500;
            color: #A8A59E;
            margin-bottom: 0.15rem;
        }

        .order-modal-contact-value {
            font-size: 0.85rem;
            font-weight: 500;
            color: #2C2B28;
        }

        .order-modal-note {
            font-size: 0.6rem;
            color: #C2BFB8;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: #FAFAF8;
            border-radius: 12px;
        }

        .order-modal-close {
            width: 100%;
            background: transparent;
            border: 1px solid #E3E1DB;
            padding: 0.6rem;
            border-radius: 30px;
            font-size: 0.7rem;
            cursor: pointer;
            color: #6B6963;
            transition: all 0.2s;
            font-family: inherit;
        }

        .order-modal-close:hover {
            background: #F5F4F1;
            border-color: #C2BFB8;
            color: #2C2B28;
        }

        .mobile-header {
            display: none;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .burger {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.4rem;
        }

        .burger span {
            display: block;
            width: 20px;
            height: 1.5px;
            background: #4A4945;
            margin: 5px 0;
            transition: 0.2s;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(44, 43, 40, 0.4);
            backdrop-filter: blur(2px);
            z-index: 90;
            opacity: 0;
            visibility: hidden;
            transition: 0.2s;
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Footer */
        footer {
            margin-top: 3rem;
            padding: 1.5rem 0 1rem;
            border-top: 1px solid #EBE9E4;
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            max-width: 1200px;
            margin: 0 auto;
            font-size: 0.7rem;
            color: #A8A59E;
        }

        .footer-copyright {
            color: #B8B5AE;
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: #A8A59E;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: #2C2B28;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 280px;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            .mobile-header {
                display: flex;
            }
            .grid {
                grid-template-columns: 1fr;
                gap: 1.2rem;
            }
            .price-inputs {
                gap: 8px;
            }
            .price-inputs input {
                padding: 0.5rem 0.4rem;
                font-size: 0.7rem;
                border-radius: 10px;
            }
            .card {
                border-radius: 20px;
            }
            .slider {
                height: 200px;
            }
            .filter-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
            .active-filters {
                gap: 0.5rem;
            }
            .filter-tag {
                font-size: 0.65rem;
                padding: 0.2rem 0.6rem;
            }
            .slider-btn {
                width: 28px;
                height: 28px;
                font-size: 16px;
            }
            .desc-modal-content {
                padding: 1.5rem;
            }
            .footer-inner {
                flex-direction: column;
                text-align: center;
                gap: 0.75rem;
            }
            .footer-links {
                justify-content: center;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="overlay" id="overlay"></div>

<div class="sidebar" id="sidebar">
    <div class="logo">
        <h1>Макетная студия</h1>
        <p>Ильи Филиппенко</p>
    </div>

    <div class="filter-section">
        <div class="filter-title">Категории</div>
        <div class="category-list" id="categoriesFilter">
            <?php foreach($allCategories as $key => $label): ?>
                <label class="category-item">
                    <input type="checkbox" value="<?= htmlspecialchars($key) ?>" class="filter-checkbox" <?= in_array($key, $selectedCategories) ? 'checked' : '' ?>>
                    <span><?= htmlspecialchars($label) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="filter-section">
        <div class="filter-title">Цена (₽)</div>
        <div class="price-inputs">
            <input type="number" id="minPrice" class="filter-input" placeholder="от" value="<?= $min_price ?: '' ?>">
            <input type="number" id="maxPrice" class="filter-input" placeholder="до" value="<?= $max_price >= 9999999 ? '' : $max_price ?>">
        </div>
    </div>

    <div class="filter-buttons">
        <button class="btn-apply" onclick="applyFilters()">Применить</button>
        <button class="btn-reset" onclick="resetFilters()">Сбросить все</button>
    </div>

    <?php if(isset($_SESSION['admin']) && $_SESSION['admin'] === true): ?>
        <a href="admin.php" class="admin-link">Админ панель</a>
        <a href="logout.php" class="admin-link-outline">Выйти</a>
    <?php else: ?>
        <a href="login.php" class="admin-link-outline">Вход</a>
    <?php endif; ?>
</div>

<main class="main-content">
    <div class="mobile-header">
        <button class="burger" id="burgerBtn">
            <span></span><span></span><span></span>
        </button>
        <div style="font-size: 0.7rem; color: #A8A59E; letter-spacing: 1px;">Макетная студия</div>
        <div style="width: 28px;"></div>
    </div>

    <div class="header">
        <h1>Каталог макетов</h1>
        <div class="stats">Найдено: <?= count($products) ?> макетов</div>
    </div>

    <?php if (!empty($selectedCategories) || ($min_price > 0) || ($max_price < 9999999)): ?>
    <div class="active-filters">
        <span class="active-filters-label">Активные фильтры:</span>
        
        <?php if ($min_price > 0): ?>
        <span class="filter-tag">
            от <?= number_format($min_price, 0, '', ' ') ?> ₽
            <span class="filter-tag-remove" onclick="removePriceFilter('min')">×</span>
        </span>
        <?php endif; ?>
        
        <?php if ($max_price < 9999999): ?>
        <span class="filter-tag">
            до <?= number_format($max_price, 0, '', ' ') ?> ₽
            <span class="filter-tag-remove" onclick="removePriceFilter('max')">×</span>
        </span>
        <?php endif; ?>
        
        <?php foreach($selectedCategories as $cat): ?>
        <span class="filter-tag">
            <?= htmlspecialchars($allCategories[$cat] ?? $cat) ?>
            <span class="filter-tag-remove" onclick="removeCategoryFilter('<?= htmlspecialchars($cat) ?>')">×</span>
        </span>
        <?php endforeach; ?>
        
        <span class="clear-all" onclick="resetFilters()">Очистить все</span>
    </div>
    <?php endif; ?>

    <div class="grid">
        <?php foreach($products as $p): ?>
        <div class="card">
            <div class="slider" data-id="<?= $p['id'] ?>">
                <div class="slider-images">
                    <?php 
                    $images = json_decode($p['images'], true);
                    if(is_array($images) && count($images) > 0):
                        foreach($images as $img):
                    ?>
                        <img src="/<?= htmlspecialchars($img) ?>" class="slider-image" onclick="openModal(this.src)" draggable="false">
                    <?php 
                        endforeach;
                    else:
                    ?>
                        <div class="no-image">нет фото</div>
                    <?php endif; ?>
                </div>
                <?php if(is_array($images) && count($images) > 1): ?>
                    <button class="slider-btn slider-prev">‹</button>
                    <button class="slider-btn slider-next">›</button>
                    <div class="slider-dots"></div>
                <?php endif; ?>
            </div>
            <div class="card-content">
                <div class="card-title"><?= htmlspecialchars($p['name']) ?></div>
                <div class="card-categories">
                    <?php 
                    if(!empty($p['category'])):
                        $productCats = explode(',', $p['category']);
                        foreach($productCats as $cat):
                            $cat = trim($cat);
                    ?>
                        <span class="card-category"><?= htmlspecialchars($cat) ?></span>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </div>
                <div class="card-description" id="desc-<?= $p['id'] ?>"><?= htmlspecialchars($p['description']) ?></div>
                <span class="read-more" onclick="openDescModal(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>', '<?= htmlspecialchars(addslashes($p['description'])) ?>', '<?= htmlspecialchars(addslashes($p['base_size'] ?? 'не указан')) ?>', '<?= htmlspecialchars(addslashes($p['production_time'] ?? 'не указан')) ?>', '<?= number_format($p['price'], 0, '', ' ') ?>')">Подробнее</span>
                <div class="card-details">
                    <span><?= htmlspecialchars($p['base_size'] ?? 'размер не указан') ?></span>
                    <span class="deadline"><?= htmlspecialchars($p['production_time'] ?? 'срок не указан') ?></span>
                </div>
                <div class="card-price">
                    <span class="price"><?= number_format($p['price'], 0, '', ' ') ?> ₽</span>
                    <button class="btn-order">Заказать</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if(count($products) == 0): ?>
            <div style="grid-column:1/-1; text-align:center; padding:3rem; background:#FAFAF8; border-radius:24px; color:#B8B5AE;">
                Ничего не найдено
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="footer-inner">
            <div class="footer-copyright">© 2026, ИП Филиппенко И.Н.</div>
            <div class="footer-links">
                <a href="privacy.php">Политика конфиденциальности</a>
                <a href="tel:+79025167807">+7 902 516 78 07</a>
                <a href="https://t.me/irkcopy" target="_blank">Telegram</a>
                <a href="#" onclick="openOrderModal(); return false;">Связаться</a>
            </div>
        </div>
    </footer>
</main>

<!-- Модальное окно для картинок -->
<div class="modal" id="modal" onclick="closeModal()">
    <span class="modal-close">&times;</span>
    <img class="modal-img" id="modalImg">
</div>

<!-- Модальное окно для описания -->
<div class="desc-modal" id="descModal" onclick="closeDescModal()">
    <div class="desc-modal-content" onclick="event.stopPropagation()">
        <span class="desc-modal-close" onclick="closeDescModal()">&times;</span>
        <h3 id="descModalTitle"></h3>
        <p id="descModalText"></p>
        <div class="desc-modal-details">
            <div class="desc-modal-detail"><strong>Размер основания:</strong> <span id="descModalSize"></span></div>
            <div class="desc-modal-detail"><strong>Срок изготовления:</strong> <span id="descModalDeadline"></span></div>
            <div class="desc-modal-detail"><strong>Цена:</strong> <span id="descModalPrice"></span> ₽</div>
        </div>
        <button class="btn-order" id="descModalOrderBtn" style="width: 100%;">Заказать</button>
    </div>
</div>

<!-- Модальное окно для заказа -->
<div class="order-modal" id="orderModal" onclick="closeOrderModal()">
    <div class="order-modal-content" onclick="event.stopPropagation()">
        <h3>Связаться</h3>
        <div class="order-modal-subtitle">Для заказа макета</div>
        
        <div class="order-modal-contacts">
            <a href="tel:+79025167807" class="order-modal-contact-item">
                <div class="order-modal-contact-icon">📞</div>
                <div class="order-modal-contact-info">
                    <div class="order-modal-contact-title">Телефон</div>
                    <div class="order-modal-contact-value">+7 902 516 78 07</div>
                </div>
            </a>
            
            <a href="https://t.me/irkcopy" target="_blank" class="order-modal-contact-item">
                <div class="order-modal-contact-icon">📱</div>
                <div class="order-modal-contact-info">
                    <div class="order-modal-contact-title">Telegram</div>
                    <div class="order-modal-contact-value">@irkcopy</div>
                </div>
            </a>
            
            <a href="tel:+79025167807" class="order-modal-contact-item">
                <div class="order-modal-contact-icon">💬</div>
                <div class="order-modal-contact-info">
                    <div class="order-modal-contact-title">MAX</div>
                    <div class="order-modal-contact-value">+7 902 516 78 07</div>
                </div>
            </a>
        </div>
        
        <div class="order-modal-note">
            Нажатие кнопки не создаёт договорных обязательств
        </div>
        
        <button class="order-modal-close" onclick="closeOrderModal()">Закрыть</button>
    </div>
</div>

<script>
function getCurrentParams() {
    const params = new URLSearchParams(window.location.search);
    return {
        categories: params.getAll('categories[]'),
        min_price: params.get('min_price'),
        max_price: params.get('max_price')
    };
}

function applyFilters() {
    const current = getCurrentParams();
    const newCategories = Array.from(document.querySelectorAll('.filter-checkbox:checked')).map(cb => cb.value);
    const newMinPrice = document.getElementById('minPrice').value;
    const newMaxPrice = document.getElementById('maxPrice').value;
    
    let allCategories = [...current.categories];
    newCategories.forEach(cat => {
        if (!allCategories.includes(cat)) {
            allCategories.push(cat);
        }
    });
    
    let finalMinPrice = current.min_price;
    let finalMaxPrice = current.max_price;
    
    if (newMinPrice !== '') {
        finalMinPrice = newMinPrice;
    }
    if (newMaxPrice !== '') {
        finalMaxPrice = newMaxPrice;
    }
    
    const params = new URLSearchParams();
    allCategories.forEach(c => params.append('categories[]', c));
    if (finalMinPrice && finalMinPrice !== '0') params.append('min_price', finalMinPrice);
    if (finalMaxPrice && finalMaxPrice !== '') params.append('max_price', finalMaxPrice);
    
    location.href = '?' + params.toString();
}

function removeCategoryFilter(category) {
    const current = getCurrentParams();
    const newCategories = current.categories.filter(c => c !== category);
    
    const params = new URLSearchParams();
    newCategories.forEach(c => params.append('categories[]', c));
    if (current.min_price) params.append('min_price', current.min_price);
    if (current.max_price) params.append('max_price', current.max_price);
    
    location.href = '?' + params.toString();
}

function removePriceFilter(type) {
    const current = getCurrentParams();
    
    const params = new URLSearchParams();
    current.categories.forEach(c => params.append('categories[]', c));
    
    if (type === 'min') {
        if (current.max_price) params.append('max_price', current.max_price);
    } else {
        if (current.min_price) params.append('min_price', current.min_price);
    }
    
    location.href = '?' + params.toString();
}

function resetFilters() {
    location.href = '?';
}

document.getElementById('minPrice')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') applyFilters();
});
document.getElementById('maxPrice')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') applyFilters();
});

document.addEventListener('DOMContentLoaded', function() {
    const current = getCurrentParams();
    document.querySelectorAll('.filter-checkbox').forEach(cb => {
        if (current.categories.includes(cb.value)) {
            cb.checked = true;
        }
    });
    
    initSliders();
});

function initSliders() {
    document.querySelectorAll('.slider').forEach(slider => {
        const container = slider.querySelector('.slider-images');
        const prevBtn = slider.querySelector('.slider-prev');
        const nextBtn = slider.querySelector('.slider-next');
        const dotsContainer = slider.querySelector('.slider-dots');
        
        if (!container) return;
        
        const images = container.querySelectorAll('.slider-image');
        const total = images.length;
        
        if (total <= 1) return;
        
        let currentIndex = 0;
        
        function updateSlider() {
            container.style.transform = `translateX(-${currentIndex * 100}%)`;
            if (dotsContainer) {
                const dots = dotsContainer.querySelectorAll('.slider-dot');
                dots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === currentIndex);
                });
            }
        }
        
        if (dotsContainer) {
            dotsContainer.innerHTML = '';
            for (let i = 0; i < total; i++) {
                const dot = document.createElement('div');
                dot.classList.add('slider-dot');
                if (i === currentIndex) dot.classList.add('active');
                dot.addEventListener('click', () => {
                    currentIndex = i;
                    updateSlider();
                });
                dotsContainer.appendChild(dot);
            }
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (currentIndex > 0) {
                    currentIndex--;
                    updateSlider();
                }
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (currentIndex < total - 1) {
                    currentIndex++;
                    updateSlider();
                }
            });
        }
        
        updateSlider();
    });
}

function openModal(src) {
    const modal = document.getElementById('modal');
    const modalImg = document.getElementById('modalImg');
    modal.classList.add('active');
    modalImg.src = src;
}

function closeModal() {
    document.getElementById('modal').classList.remove('active');
}

function openOrderModal() {
    document.getElementById('orderModal').classList.add('active');
}

function closeOrderModal() {
    document.getElementById('orderModal').classList.remove('active');
}

function openDescModal(id, name, description, baseSize, deadline, price) {
    document.getElementById('descModalTitle').innerText = name;
    document.getElementById('descModalText').innerText = description;
    document.getElementById('descModalSize').innerText = baseSize;
    document.getElementById('descModalDeadline').innerText = deadline;
    document.getElementById('descModalPrice').innerText = price;
    
    const orderBtn = document.getElementById('descModalOrderBtn');
    orderBtn.onclick = function() {
        closeDescModal();
        openOrderModal();
    };
    
    document.getElementById('descModal').classList.add('active');
}

function closeDescModal() {
    document.getElementById('descModal').classList.remove('active');
}

document.querySelectorAll('.btn-order').forEach(btn => {
    btn.addEventListener('click', () => {
        openOrderModal();
    });
});

const burgerBtn = document.getElementById('burgerBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

if (burgerBtn) {
    burgerBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    });
}

if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
    });
}
</script>
</body>
</html>