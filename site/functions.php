<?php
// Функция загрузки данных из JSON
function loadData() {
    $jsonFile = __DIR__ . '/data.json';
    if (file_exists($jsonFile)) {
        $jsonContent = file_get_contents($jsonFile);
        return json_decode($jsonContent, true);
    }
    return ['products' => []];
}

// Функция сохранения данных в JSON
function saveData($data) {
    $jsonFile = __DIR__ . '/data.json';
    file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Функция получения всех товаров
function getAllProducts() {
    $data = loadData();
    return $data['products'];
}

// Функция получения товара по ID
function getProductById($id) {
    $data = loadData();
    foreach ($data['products'] as $product) {
        if ($product['id'] == $id) {
            return $product;
        }
    }
    return null;
}

// Функция добавления товара
function addProduct($name, $description, $price, $area, $category) {
    $data = loadData();
    $newId = count($data['products']) > 0 ? max(array_column($data['products'], 'id')) + 1 : 1;
    
    $newProduct = [
        'id' => $newId,
        'name' => $name,
        'description' => $description,
        'price' => (float)$price,
        'area' => (int)$area,
        'category' => $category
    ];
    
    $data['products'][] = $newProduct;
    saveData($data);
    return true;
}

// Функция обновления товара
function updateProduct($id, $name, $description, $price, $area, $category) {
    $data = loadData();
    foreach ($data['products'] as &$product) {
        if ($product['id'] == $id) {
            $product['name'] = $name;
            $product['description'] = $description;
            $product['price'] = (float)$price;
            $product['area'] = (int)$area;
            $product['category'] = $category;
            break;
        }
    }
    saveData($data);
    return true;
}

// Функция удаления товара
function deleteProduct($id) {
    $data = loadData();
    $data['products'] = array_filter($data['products'], function($product) use ($id) {
        return $product['id'] != $id;
    });
    $data['products'] = array_values($data['products']); // Переиндексация
    saveData($data);
    return true;
}
?>