<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    // Если не авторизован, отправляем на страницу входа
    header('Location: login.php');
    exit;
}

$dataFile = 'data.json';
if (file_exists($dataFile)) {
    $data = json_decode(file_get_contents($dataFile), true);
    $products = $data['products'] ?? [];
} else {
    $products = [];
}

// Добавление
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $newId = count($products) > 0 ? max(array_column($products, 'id')) + 1 : 1;
    $products[] = [
        'id' => $newId,
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'price' => (float)$_POST['price'],
        'area' => (int)$_POST['area'],
        'category' => $_POST['category']
    ];
    file_put_contents($dataFile, json_encode(['products' => $products], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: admin.php');
    exit;
}

// Редактирование
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    foreach ($products as &$p) {
        if ($p['id'] == $_POST['id']) {
            $p['name'] = $_POST['name'];
            $p['description'] = $_POST['description'];
            $p['price'] = (float)$_POST['price'];
            $p['area'] = (int)$_POST['area'];
            $p['category'] = $_POST['category'];
            break;
        }
    }
    file_put_contents($dataFile, json_encode(['products' => $products], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: admin.php');
    exit;
}

// Удаление
if (isset($_GET['delete'])) {
    $products = array_values(array_filter($products, fn($p) => $p['id'] != $_GET['delete']));
    file_put_contents($dataFile, json_encode(['products' => $products], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: admin.php');
    exit;
}

$editProduct = null;
if (isset($_GET['edit'])) {
    foreach ($products as $p) {
        if ($p['id'] == $_GET['edit']) { $editProduct = $p; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель - Макеты домов</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background: #F4F4F2; color: #2C2C2A; padding: 2rem; }
        .admin-container { max-width: 1400px; margin: 0 auto; }
        .admin-header { background: white; border-radius: 20px; padding: 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .admin-header h1 { font-size: 1.6rem; font-weight: 500; color: #3A3A36; }
        .user-info { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
        .user-info span { color: #7A7974; }
        .btn { padding: 0.5rem 1rem; border-radius: 40px; border: none; cursor: pointer; text-decoration: none; display: inline-block; font-size: 0.875rem; font-weight: 500; transition: 0.2s; }
        .btn-primary { background: #3A3A36; color: white; }
        .btn-primary:hover { background: #2C2C2A; }
        .btn-warning { background: #ffc107; color: #2C2C2A; }
        .btn-warning:hover { background: #e0a800; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .btn-outline { background: #F1EFEA; color: #5A5952; }
        .btn-outline:hover { background: #E8E4DC; }
        .form-card { background: white; border-radius: 28px; padding: 1.8rem; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #EBE8E1; }
        .form-card h2 { font-size: 1.3rem; font-weight: 500; margin-bottom: 1.5rem; color: #3A3A36; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4A4943; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #E2DFD8; border-radius: 16px; font-family: inherit; font-size: 0.9rem; background: #FAFAF8; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #B9B2A2; background: white; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .admin-table { width: 100%; background: white; border-radius: 20px; overflow: hidden; border: 1px solid #EBE8E1; }
        .admin-table th { background: #F1EFEA; padding: 1rem; text-align: left; font-weight: 600; color: #4A4943; }
        .admin-table td { padding: 1rem; border-bottom: 1px solid #EBE8E1; }
        .admin-table tr:last-child td { border-bottom: none; }
        .table-actions { display: flex; gap: 0.5rem; }
        h2.section-title { font-size: 1.3rem; font-weight: 500; margin: 1.5rem 0 1rem; color: #3A3A36; }
        @media (max-width: 768px) { body { padding: 1rem; } .form-row { grid-template-columns: 1fr; } .admin-table { font-size: 0.8rem; } .table-actions { flex-direction: column; gap: 0.3rem; } .admin-header { flex-direction: column; text-align: center; } }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-header">
        <h1>🔧 Панель администратора</h1>
        <div class="user-info">
            <span>👋 Здравствуйте, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>!</span>
            <a href="index.php" class="btn btn-outline">🏠 На сайт</a>
            <a href="logout.php" class="btn btn-danger">🚪 Выйти</a>
        </div>
    </div>

    <div class="form-card">
        <h2><?= $editProduct ? '✏️ Редактировать макет' : '➕ Добавить новый макет' ?></h2>
        <form method="POST">
            <?php if($editProduct): ?>
                <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
                <input type="hidden" name="edit" value="1">
            <?php else: ?>
                <input type="hidden" name="add" value="1">
            <?php endif; ?>
            <div class="form-group"><label>Название макета</label><input type="text" name="name" required value="<?= $editProduct ? htmlspecialchars($editProduct['name']) : '' ?>"></div>
            <div class="form-group"><label>Описание</label><textarea name="description" rows="3" required><?= $editProduct ? htmlspecialchars($editProduct['description']) : '' ?></textarea></div>
            <div class="form-row">
                <div class="form-group"><label>💰 Цена (₽)</label><input type="number" name="price" required value="<?= $editProduct ? $editProduct['price'] : '' ?>"></div>
                <div class="form-group"><label>📐 Площадь (м²)</label><input type="number" name="area" required value="<?= $editProduct ? $editProduct['area'] : '' ?>"></div>
            </div>
            <div class="form-group">
                <label>🏷️ Категория</label>
                <select name="category" required>
                    <option value="modern" <?= $editProduct && $editProduct['category'] == 'modern' ? 'selected' : '' ?>>🏢 Современные</option>
                    <option value="classic" <?= $editProduct && $editProduct['category'] == 'classic' ? 'selected' : '' ?>>🏛️ Классические</option>
                    <option value="eco" <?= $editProduct && $editProduct['category'] == 'eco' ? 'selected' : '' ?>>🌿 Эко-дизайн</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?= $editProduct ? '💾 Сохранить изменения' : '➕ Добавить макет' ?></button>
            <?php if($editProduct): ?><a href="admin.php" class="btn btn-outline" style="margin-left: 0.5rem;">❌ Отмена</a><?php endif; ?>
        </form>
    </div>

    <h2 class="section-title">📋 Список всех макетов</h2>
    <table class="admin-table">
        <thead><tr><th>ID</th><th>Название</th><th>Описание</th><th>Цена</th><th>Площадь</th><th>Категория</th><th>Действия</th></tr></thead>
        <tbody>
            <?php foreach(array_reverse($products) as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                <td style="max-width: 300px;"><?= htmlspecialchars(mb_substr($p['description'], 0, 60)) ?>...</td>
                <td><?= number_format($p['price'], 0, '', ' ') ?> ₽</td>
                <td><?= $p['area'] ?> м²</td>
                <td><?= $p['category'] == 'modern' ? '🏢 Современные' : ($p['category'] == 'classic' ? '🏛️ Классические' : '🌿 Эко-дизайн') ?></td>
                <td class="table-actions"><a href="admin.php?edit=<?= $p['id'] ?>" class="btn btn-warning">✏️</a><a href="admin.php?delete=<?= $p['id'] ?>" class="btn btn-danger" onclick="return confirm('Удалить этот макет?')">🗑️</a></td>
            </tr>
            <?php endforeach; ?>
            <?php if(count($products) == 0): ?>
                <tr><td colspan="7" style="text-align:center; padding:3rem;">📭 Пока нет макетов. Добавьте первый!</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>