<?php
require_once __DIR__ . '/config.php';
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Поиск
$search = $_GET['search'] ?? '';
$searchCondition = '';
$searchParam = '';

if (!empty($search)) {
    $searchCondition = "WHERE name LIKE :search OR description LIKE :search";
    $searchParam = "%$search%";
}

// Получаем товары из БД
try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT * FROM models $searchCondition ORDER BY id DESC");
        $stmt->execute(['search' => $searchParam]);
    } else {
        $stmt = $pdo->query("SELECT * FROM models ORDER BY id DESC");
    }
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $error = "Ошибка БД: " . $e->getMessage();
}

// Добавление товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $images = [];
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Обработка загруженных фото
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === 0 && !empty($tmp_name)) {
                $ext = strtolower(pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array($ext, $allowed)) {
                    $filename = time() . '_' . uniqid() . '.' . $ext;
                    $destination = $uploadDir . $filename;
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $images[] = 'uploads/' . $filename;
                    }
                }
            }
        }
    }
    
    // Выбор фото из общего списка
    if (isset($_POST['selected_images']) && is_array($_POST['selected_images'])) {
        foreach ($_POST['selected_images'] as $selectedImg) {
            $selectedImg = trim($selectedImg);
            if (file_exists($modelsDir . $selectedImg)) {
                $images[] = 'models/' . $selectedImg;
            } elseif (file_exists($uploadDir . $selectedImg)) {
                $images[] = 'uploads/' . $selectedImg;
            }
        }
    }
    
    $categoryStr = !empty($_POST['categories']) ? implode(',', $_POST['categories']) : '';
    $imagesJson = json_encode($images, JSON_UNESCAPED_SLASHES);
    
    try {
        $sql = "INSERT INTO models (name, description, price, category, base_size, production_time, images) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            (float)$_POST['price'],
            $categoryStr,
            $_POST['base_size'],
            $_POST['production_time'],
            $imagesJson
        ]);
        
        header('Location: admin.php?success=1');
        exit;
        
    } catch (PDOException $e) {
        $error = "Ошибка добавления: " . $e->getMessage();
    }
}

// Редактирование товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $stmt = $pdo->prepare("SELECT images FROM models WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    $existing = $stmt->fetch();
    $existingImages = $existing ? (json_decode($existing['images'], true) ?? []) : [];
    
    $newImages = [];
    
    // Загружаем новые фото
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === 0 && !empty($tmp_name)) {
                $ext = strtolower(pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array($ext, $allowed)) {
                    $filename = time() . '_' . uniqid() . '.' . $ext;
                    $destination = $uploadDir . $filename;
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $newImages[] = 'uploads/' . $filename;
                    }
                }
            }
        }
    }
    
    // Выбор фото из общего списка
    if (isset($_POST['selected_images']) && is_array($_POST['selected_images'])) {
        foreach ($_POST['selected_images'] as $selectedImg) {
            $selectedImg = trim($selectedImg);
            if (file_exists($modelsDir . $selectedImg)) {
                $newImages[] = 'models/' . $selectedImg;
            } elseif (file_exists($uploadDir . $selectedImg)) {
                $newImages[] = 'uploads/' . $selectedImg;
            }
        }
    }
    
    $allImages = array_merge($existingImages, $newImages);
    $categoryStr = !empty($_POST['categories']) ? implode(',', $_POST['categories']) : '';
    $imagesJson = json_encode($allImages, JSON_UNESCAPED_SLASHES);
    
    try {
        $sql = "UPDATE models SET name=?, description=?, price=?, category=?, base_size=?, production_time=?, images=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            (float)$_POST['price'],
            $categoryStr,
            $_POST['base_size'],
            $_POST['production_time'],
            $imagesJson,
            $_POST['id']
        ]);
        
        header('Location: admin.php?edit=' . $_POST['id'] . '&success=1');
        exit;
        
    } catch (PDOException $e) {
        $error = "Ошибка обновления: " . $e->getMessage();
    }
}

// Удаление фото
if (isset($_GET['delete_image']) && isset($_GET['product_id']) && isset($_GET['image_path'])) {
    $productId = (int)$_GET['product_id'];
    $imagePath = $_GET['image_path'];
    
    try {
        $stmt = $pdo->prepare("SELECT images FROM models WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        $images = $product ? (json_decode($product['images'], true) ?? []) : [];
        
        $images = array_values(array_filter($images, function($img) use ($imagePath) {
            return $img !== $imagePath;
        }));
        
        // Удаляем файл физически
        $localPath = __DIR__ . '/' . $imagePath;
        if (file_exists($localPath)) {
            unlink($localPath);
        }
        
        $stmt = $pdo->prepare("UPDATE models SET images = ? WHERE id = ?");
        $stmt->execute([json_encode($images, JSON_UNESCAPED_SLASHES), $productId]);
        
        header('Location: admin.php?edit=' . $productId . '&image_deleted=1');
        exit;
    } catch (PDOException $e) {
        header('Location: admin.php?edit=' . $productId . '&error=1');
        exit;
    }
}

// Удаление товара
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("SELECT images FROM models WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        $images = $product ? (json_decode($product['images'], true) ?? []) : [];
        
        foreach ($images as $img) {
            $localPath = __DIR__ . '/' . $img;
            if (file_exists($localPath)) {
                unlink($localPath);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM models WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {}
    
    header('Location: admin.php');
    exit;
}

// Получаем редактируемый товар
$editProduct = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM models WHERE id = ?");
        $stmt->execute([(int)$_GET['edit']]);
        $editProduct = $stmt->fetch();
        
        if ($editProduct && $editProduct['category']) {
            $editProduct['categories_array'] = explode(',', $editProduct['category']);
        } else {
            $editProduct['categories_array'] = [];
        }
    } catch (PDOException $e) {
        $editProduct = null;
    }
}

// Получаем ВСЕ фото из обеих папок в один список
$allAvailableImages = [];

// Фото из папки models
if (is_dir($modelsDir)) {
    $files = scandir($modelsDir);
    if ($files !== false) {
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($file, ['.', '..']) && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $allAvailableImages[] = [
                    'path' => 'models/' . $file,
                    'url' => '/models/' . $file,
                    'name' => $file,
                    'folder' => 'models'
                ];
            }
        }
    }
}

// Фото из папки uploads
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    if ($files !== false) {
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($file, ['.', '..']) && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $allAvailableImages[] = [
                    'path' => 'uploads/' . $file,
                    'url' => '/uploads/' . $file,
                    'name' => $file,
                    'folder' => 'uploads'
                ];
            }
        }
    }
}

// Сортируем по имени
usort($allAvailableImages, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Макетная студия Ильи Филиппенко | Админ панель</title>
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

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .admin-header {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            border: 1px solid #EBE9E4;
        }

        .admin-header h1 {
            font-size: 1.2rem;
            font-weight: 450;
            letter-spacing: 1px;
            color: #2C2B28;
        }

        .admin-header p {
            font-size: 0.7rem;
            color: #A8A59E;
            margin-top: 0.2rem;
        }

        .btn {
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 450;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-primary {
            background: #2C2B28;
            color: white;
        }

        .btn-primary:hover {
            background: #1A1A18;
        }

        .btn-warning {
            background: #F5F4F1;
            color: #4A4945;
            border: 1px solid #EBE9E4;
        }

        .btn-warning:hover {
            background: #EBE9E4;
        }

        .btn-danger {
            background: #DC3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #E3E1DB;
            color: #6B6963;
        }

        .btn-outline:hover {
            background: #F5F4F1;
            border-color: #C2BFB8;
            color: #2C2B28;
        }

        .search-bar {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 0.5rem 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            gap: 0.75rem;
            align-items: center;
            border: 1px solid #EBE9E4;
        }

        .search-bar input {
            flex: 1;
            padding: 0.6rem 0;
            border: none;
            font-size: 0.8rem;
            font-family: inherit;
            background: transparent;
            outline: none;
        }

        .search-bar input::placeholder {
            color: #C2BFB8;
        }

        .search-bar button {
            padding: 0.5rem 1rem;
            background: #2C2B28;
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 0.7rem;
            font-family: inherit;
        }

        .form-card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #EBE9E4;
        }

        .form-card h2 {
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            letter-spacing: 0.5px;
            color: #2C2B28;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.75rem;
            font-weight: 450;
            color: #4A4945;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.7rem 0.8rem;
            border: 1px solid #E3E1DB;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.8rem;
            background: #FFFFFF;
            transition: border 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #C2BFB8;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }

        .category-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.4rem 0.5rem;
            background: #FAFAF8;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.75rem;
            color: #4A4945;
        }

        .category-checkbox input {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #8B887F;
        }

        .images-section {
            margin-top: 1rem;
            border-top: 1px solid #EBE9E4;
            padding-top: 1rem;
        }

        .images-title {
            font-size: 0.75rem;
            font-weight: 500;
            color: #4A4945;
            margin-bottom: 0.75rem;
        }

        .images-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 0.5rem;
            max-height: 350px;
            overflow-y: auto;
            padding: 0.75rem;
            background: #FAFAF8;
            border-radius: 12px;
            border: 1px solid #EBE9E4;
        }

        .image-option {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #E3E1DB;
            cursor: pointer;
            transition: all 0.2s;
            background: #FFFFFF;
        }

        .image-option.selected {
            border-color: #2C2B28;
            box-shadow: 0 0 0 2px rgba(44, 43, 40, 0.2);
        }

        .image-option img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-option input {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #2C2B28;
            background: rgba(255,255,255,0.8);
            border-radius: 3px;
        }

        .image-preview {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }

        .image-preview-item {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #EBE9E4;
            background: #FAFAF8;
        }

        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview-item .delete-image-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #DC3545;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.2s;
            z-index: 10;
        }

        .image-preview-item .delete-image-btn:hover {
            background: #c82333;
        }

        .badge {
            display: inline-block;
            font-size: 0.55rem;
            padding: 0.15rem 0.4rem;
            border-radius: 20px;
            margin-top: 0.25rem;
            background: #F0EFEC;
            color: #A8A59E;
        }

        .success-msg, .info-msg {
            padding: 0.8rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.8rem;
        }

        .success-msg {
            background: #E6F4EA;
            color: #2D6A2D;
        }

        .info-msg {
            background: #E8F0FE;
            color: #1A73E8;
        }

        .error-msg {
            background: #FEF2F0;
            color: #DC3545;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.8rem;
        }

        small {
            color: #A8A59E;
            font-size: 0.65rem;
        }

        .table-count {
            font-size: 0.7rem;
            color: #A8A59E;
            margin-bottom: 0.75rem;
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 16px;
            border: 1px solid #EBE9E4;
            background: #FFFFFF;
        }

        .admin-table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
        }

        .admin-table th {
            background: #FAFAF8;
            padding: 0.8rem 1rem;
            text-align: left;
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #A8A59E;
            border-bottom: 1px solid #EBE9E4;
        }

        .admin-table td {
            padding: 0.8rem 1rem;
            font-size: 0.8rem;
            border-bottom: 1px solid #EBE9E4;
            color: #4A4945;
            vertical-align: middle;
        }

        .admin-table tr:last-child td {
            border-bottom: none;
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .categories-grid {
                grid-template-columns: 1fr;
            }
            .search-bar {
                flex-wrap: wrap;
            }
            .search-bar button {
                width: 100%;
            }
            .admin-header {
                flex-direction: column;
                text-align: center;
            }
            .images-grid {
                max-height: 250px;
            }
            .image-option {
                width: 70px;
                height: 70px;
            }
            .image-preview-item {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-header">
        <div>
            <h1>Макетная студия</h1>
            <p>Администрирование</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="index.php" class="btn btn-outline">На сайт</a>
            <a href="logout.php" class="btn btn-danger">Выйти</a>
        </div>
    </div>
    
    <?php if(isset($_GET['success'])): ?>
        <div class="success-msg">✅ Операция успешно выполнена</div>
    <?php endif; ?>
    
    <?php if(isset($_GET['image_deleted'])): ?>
        <div class="info-msg">🗑️ Фото удалено</div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
        <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if($editProduct): ?>
        <!-- Режим редактирования -->
        <div class="form-card">
            <h2>✏️ Редактирование макета: <?= htmlspecialchars($editProduct['name']) ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
                <input type="hidden" name="edit" value="1">
                
                <div class="form-group">
                    <label>Название</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($editProduct['name']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" rows="3" required><?= htmlspecialchars($editProduct['description']) ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Цена (₽)</label>
                        <input type="number" name="price" required value="<?= $editProduct['price'] ?>">
                    </div>
                    <div class="form-group">
                        <label>Размер основания (см)</label>
                        <input type="text" name="base_size" placeholder="например: 25×15 см" value="<?= htmlspecialchars($editProduct['base_size'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Срок изготовления</label>
                    <input type="text" name="production_time" placeholder="например: 14-21 день" value="<?= htmlspecialchars($editProduct['production_time'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Категории</label>
                    <div class="categories-grid">
                        <?php 
                        $editCats = $editProduct['categories_array'] ?? [];
                        foreach($allCategories as $key => $label): 
                        ?>
                            <label class="category-checkbox">
                                <input type="checkbox" name="categories[]" value="<?= htmlspecialchars($key) ?>" <?= in_array($key, $editCats) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Текущие фото макета -->
                <?php 
                $currentImages = json_decode($editProduct['images'], true);
                if(!empty($currentImages)):
                ?>
                <div class="form-group">
                    <label>📷 Текущие фото макета</label>
                    <div class="image-preview">
                        <?php foreach($currentImages as $img): ?>
                            <div class="image-preview-item">
                                <img src="/<?= htmlspecialchars($img) ?>">
                                <a href="admin.php?delete_image=1&product_id=<?= $editProduct['id'] ?>&image_path=<?= urlencode($img) ?>" class="delete-image-btn" onclick="return confirm('Удалить это фото?')">×</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small>Нажмите × на фото, чтобы удалить его. Страница перезагрузится, и вы продолжите редактирование.</small>
                </div>
                <?php endif; ?>
                
                <!-- Все доступные фото для добавления -->
                <div class="images-section">
                    <div class="images-title">📷 Добавить фото из библиотеки</div>
                    <?php if(!empty($allAvailableImages)): ?>
                        <div class="images-grid">
                            <?php foreach($allAvailableImages as $img): ?>
                                <label class="image-option" title="<?= $img['folder'] === 'models' ? 'Из папки models' : 'Загружено ранее' ?>">
                                    <img src="<?= $img['url'] ?>">
                                    <input type="checkbox" name="selected_images[]" value="<?= basename($img['path']) ?>">
                                    <span class="badge"><?= $img['folder'] === 'models' ? '📁' : '📤' ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <small>✓ Отметьте фото, которые хотите добавить к макету.</small>
                    <?php else: ?>
                        <div style="padding: 1rem; text-align: center; background: #FAFAF8; border-radius: 12px; color: #A8A59E; font-size: 0.75rem;">
                            Нет доступных фото. Загрузите новые ниже.
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Загрузка новых фото -->
                <div class="form-group" style="margin-top: 1rem;">
                    <label>📸 Загрузить новые фото</label>
                    <input type="file" name="images[]" multiple accept="image/*">
                    <small>Можно выбрать несколько файлов (jpg, png, webp).</small>
                </div>
                
                <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">💾 Сохранить изменения</button>
                <a href="admin.php" class="btn btn-outline" style="margin-left: 0.5rem;">Отмена</a>
            </form>
        </div>
    <?php else: ?>
        <!-- Режим добавления нового макета -->
        <div class="form-card">
            <h2>➕ Новый макет</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add" value="1">
                
                <div class="form-group">
                    <label>Название</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" rows="3" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Цена (₽)</label>
                        <input type="number" name="price" required>
                    </div>
                    <div class="form-group">
                        <label>Размер основания (см)</label>
                        <input type="text" name="base_size" placeholder="например: 25×15 см">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Срок изготовления</label>
                    <input type="text" name="production_time" placeholder="например: 14-21 день">
                </div>
                
                <div class="form-group">
                    <label>Категории</label>
                    <div class="categories-grid">
                        <?php foreach($allCategories as $key => $label): ?>
                            <label class="category-checkbox">
                                <input type="checkbox" name="categories[]" value="<?= htmlspecialchars($key) ?>">
                                <span><?= htmlspecialchars($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Все доступные фото -->
                <div class="images-section">
                    <div class="images-title">📷 Выбрать фото из библиотеки</div>
                    <?php if(!empty($allAvailableImages)): ?>
                        <div class="images-grid">
                            <?php foreach($allAvailableImages as $img): ?>
                                <label class="image-option" title="<?= $img['folder'] === 'models' ? 'Из папки models' : 'Загружено ранее' ?>">
                                    <img src="<?= $img['url'] ?>">
                                    <input type="checkbox" name="selected_images[]" value="<?= basename($img['path']) ?>">
                                    <span class="badge"><?= $img['folder'] === 'models' ? '📁' : '📤' ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <small>✓ Отметьте нужные фото. Они будут добавлены к макету.</small>
                    <?php else: ?>
                        <div style="padding: 1rem; text-align: center; background: #FAFAF8; border-radius: 12px; color: #A8A59E; font-size: 0.75rem;">
                            Нет доступных фото. Загрузите новые ниже.
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Загрузка новых фото -->
                <div class="form-group" style="margin-top: 1rem;">
                    <label>📸 Загрузить новые фото</label>
                    <input type="file" name="images[]" multiple accept="image/*">
                    <small>Можно выбрать несколько файлов (jpg, png, webp).</small>
                </div>
                
                <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">➕ Добавить макет</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Список макетов -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin: 1.5rem 0 1rem;">
        <h2 style="font-size: 0.9rem; font-weight: 500; letter-spacing: 0.5px;">Список макетов</h2>
        <div class="table-count">Всего: <?= count($products) ?> макетов</div>
    </div>

    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Поиск по названию или описанию..." value="<?= htmlspecialchars($search) ?>">
        <button onclick="doSearch()">Найти</button>
        <?php if (!empty($search)): ?>
            <button onclick="clearSearch()" class="btn-outline" style="background: transparent;">Сбросить</button>
        <?php endif; ?>
    </div>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Фото</th>
                    <th>Название</th>
                    <th>Цена</th>
                    <th>Размер</th>
                    <th>Срок</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($products as $p): ?>
                <tr>
                    <td style="width: 50px;"><?= $p['id'] ?></td>
                    <td style="width: 60px;">
                        <?php 
                        $images = json_decode($p['images'], true);
                        if(!empty($images) && is_array($images)): ?>
                            <img src="/<?= htmlspecialchars($images[0]) ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">
                        <?php else: ?>—<?php endif; ?>
                     </div>
                    <td>
                        <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                        <span style="font-size: 0.65rem; color: #A8A59E;"><?= htmlspecialchars(mb_substr($p['description'] ?? '', 0, 50)) ?>...</span>
                     </div>
                    <td style="white-space: nowrap;"><?= number_format($p['price'], 0, '', ' ') ?> ₽</div>
                    <td style="white-space: nowrap;"><?= htmlspecialchars($p['base_size'] ?? '—') ?></div>
                    <td style="white-space: nowrap;"><?= htmlspecialchars($p['production_time'] ?? '—') ?></div>
                    <td style="width: 100px;">
                        <div class="table-actions">
                            <a href="admin.php?edit=<?= $p['id'] ?>" class="btn btn-warning" style="padding: 0.3rem 0.7rem;">✏️</a>
                            <a href="admin.php?delete=<?= $p['id'] ?>" class="btn btn-danger" style="padding: 0.3rem 0.7rem;" onclick="return confirm('Удалить макет?')">🗑️</a>
                        </div>
                     </div>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($products)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: #A8A59E;">Нет макетов. Добавьте первый.</div>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function doSearch() {
    const searchValue = document.getElementById('searchInput').value;
    if (searchValue.trim()) {
        location.href = '?search=' + encodeURIComponent(searchValue.trim());
    } else {
        location.href = '?';
    }
}

function clearSearch() {
    location.href = '?';
}

document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        doSearch();
    }
});
</script>
</body>
</html>