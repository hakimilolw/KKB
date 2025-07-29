<?php
// Main API file: api/menu.php

// 1. Establish database connection
require 'db.php'; 

// 2. Define main action router
$action = $_GET['action'] ?? '';

switch ($action) {

    // == Actions for Customer Page (index.php) ==
    case 'get-full-menu':
        $categoryStmt = $pdo->query("SELECT id, name, slug FROM categories ORDER BY display_order ASC");
        $categories = $categoryStmt->fetchAll();
        $itemStmt = $pdo->prepare("SELECT id, name, subcategory, type, basePrice, priceDisplay, imageUrl FROM items WHERE category_id = ?");
        $fullMenu = [];
        foreach ($categories as $category) {
            $itemStmt->execute([$category['id']]);
            $items = $itemStmt->fetchAll();
            $category['items'] = $items;
            $fullMenu[] = $category;
        }
        echo json_encode($fullMenu);
        break;

    case 'get-item-details':
        if (!isset($_GET['id'])) { http_response_code(400); echo json_encode(['error' => 'Item ID is required.']); exit; }
        $itemId = $_GET['id'];
        $itemStmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
        $itemStmt->execute([$itemId]);
        $itemDetails = $itemStmt->fetch();
        if (!$itemDetails) { http_response_code(404); echo json_encode(['error' => 'Item not found.']); exit; }
        $sql = "SELECT ac.id, ac.name, ac.selection_type FROM addon_categories ac JOIN item_addons ia ON ac.id = ia.addon_category_id WHERE ia.item_id = ? ORDER BY ac.display_order ASC";
        $catStmt = $pdo->prepare($sql);
        $catStmt->execute([$itemId]);
        $addonCategories = $catStmt->fetchAll();
        $optionsStmt = $pdo->prepare("SELECT name, price_adjustment FROM addons WHERE addon_category_id = ?");
        $customizations = [];
        foreach ($addonCategories as $category) {
            $optionsStmt->execute([$category['id']]);
            $category['options'] = $optionsStmt->fetchAll();
            $customizations[] = $category;
        }
        echo json_encode(['details' => $itemDetails, 'customizations' => $customizations]);
        break;

    case 'place-online-order':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderData = json_decode(file_get_contents('php://input'), true);
            try {
                $pdo->beginTransaction();
                $sqlOrder = "INSERT INTO orders (total_amount, status, order_type) VALUES (?, 'New', 'Online')";
                $stmtOrder = $pdo->prepare($sqlOrder);
                $stmtOrder->execute([$orderData['total']]);
                $orderId = $pdo->lastInsertId();
                $sqlItem = "INSERT INTO order_items (order_id, item_id, item_name, quantity, unit_price) VALUES (?, ?, ?, ?, ?)";
                $stmtItem = $pdo->prepare($sqlItem);
                foreach ($orderData['items'] as $item) {
                    $stmtItem->execute([$orderId, $item['id'], $item['name'], $item['quantity'], $item['finalPrice']]);
                }
                $pdo->commit();
                echo json_encode(['success' => true, 'orderId' => $orderId]);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;

    case 'get-my-orders':
        $sql = "SELECT o.id, o.total_amount, o.status, o.created_at, GROUP_CONCAT(CONCAT(oi.quantity, 'x ', oi.item_name) SEPARATOR ', ') as items_summary FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE o.order_type = 'Online' GROUP BY o.id ORDER BY o.created_at DESC LIMIT 10";
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll());
        break;

    case 'get-my-profile':
        echo json_encode([
            'name' => 'Jane Doe', 'phone' => '+60 12-345 6789', 'balance' => 50.00,
            'points' => 250, 'loyaltyProgress' => 7
        ]);
        break;

    // == Actions for Admin Page (admin.php) ==
    case 'get-items':
        $stmt = $pdo->query("SELECT i.*, c.name as category_name FROM items i LEFT JOIN categories c ON i.category_id = c.id ORDER BY i.id DESC");
        echo json_encode($stmt->fetchAll());
        break;

    case 'get-categories':
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC");
        echo json_encode($stmt->fetchAll());
        break;

    case 'get-sales-summary':
        try {
            $sales = [
                'today' => $pdo->query("SELECT IFNULL(SUM(total_amount), 0) as total FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
                'week' => $pdo->query("SELECT IFNULL(SUM(total_amount), 0) as total FROM orders WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn(),
                'month' => $pdo->query("SELECT IFNULL(SUM(total_amount), 0) as total FROM orders WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn(),
                'year' => $pdo->query("SELECT IFNULL(SUM(total_amount), 0) as total FROM orders WHERE YEAR(created_at) = YEAR(CURDATE())")->fetchColumn()
            ];
            echo json_encode(['success' => true, 'data' => $sales]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // == Actions for Staff Page (staff.php) ==
    case 'place-walkin-order':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderData = json_decode(file_get_contents('php://input'), true);
            try {
                $pdo->beginTransaction();
                $sqlOrder = "INSERT INTO orders (total_amount, status, order_type) VALUES (?, 'Completed', 'Walk-in')";
                $stmtOrder = $pdo->prepare($sqlOrder);
                $stmtOrder->execute([$orderData['total']]);
                $orderId = $pdo->lastInsertId();
                $sqlItem = "INSERT INTO order_items (order_id, item_id, item_name, quantity, unit_price) VALUES (?, ?, ?, ?, ?)";
                $stmtItem = $pdo->prepare($sqlItem);
                foreach ($orderData['items'] as $item) {
                    $stmtItem->execute([$orderId, $item['id'], $item['name'], $item['quantity'], $item['finalPrice']]);
                }
                $pdo->commit();
                echo json_encode(['success' => true, 'orderId' => $orderId]);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;
        case 'get-online-orders':
    // Fetches only new orders placed online to show in the queue
    $sql = "
        SELECT o.id, o.total_amount, GROUP_CONCAT(CONCAT(oi.quantity, 'x ', oi.item_name) SEPARATOR ', ') as items_summary
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.status = 'New' AND o.order_type = 'Online'
        GROUP BY o.id
        ORDER BY o.created_at ASC
    ";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll());
    break;

case 'complete-order':
    // Marks an order as completed
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $stmt = $pdo->prepare("UPDATE orders SET status = 'Completed' WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update order status.']);
        }
    }
    break;

case 'get-order-history':
    // Fetches the last 50 completed orders for the history view
    $sql = "
        SELECT o.id, o.total_amount, o.order_type, o.created_at, GROUP_CONCAT(CONCAT(oi.quantity, 'x ', oi.item_name) SEPARATOR ', ') as items_summary
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.status = 'Completed'
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 50
    ";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll());
    break;

    // == Default for unknown actions ==
    default:
        if (!in_array($action, ['add-item', 'update-item', 'delete-item'])) {
            http_response_code(404);
            echo json_encode(['error' => 'Action not found.']);
        }
        break;
}

// --- Logic for C.R.U.D Actions (from Admin Page) ---
if ($action === 'add-item' || $action === 'update-item') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['name']) || empty($data['category_id']) || empty($data['type'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            exit;
        }
        if ($action === 'add-item') {
            $sql = "INSERT INTO items (name, category_id, subcategory, type, basePrice, priceDisplay, imageUrl) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['name'], $data['category_id'], $data['subcategory'] ?? null, $data['type'], $data['basePrice'] ?? null, $data['priceDisplay'] ?? null, $data['imageUrl']]);
            $resultId = $pdo->lastInsertId();
        } else { // update-item
            $sql = "UPDATE items SET name = ?, category_id = ?, subcategory = ?, type = ?, basePrice = ?, priceDisplay = ?, imageUrl = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['name'], $data['category_id'], $data['subcategory'] ?? null, $data['type'], $data['basePrice'] ?? null, $data['priceDisplay'] ?? null, $data['imageUrl'], $data['id']]);
            $resultId = $data['id'];
        }
        echo json_encode(['success' => true, 'id' => $resultId]);
    }
}

if ($action === 'delete-item') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete item.']);
        }
    }
}
?>