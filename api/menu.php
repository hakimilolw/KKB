<?php
// Main API file: api/menu.php

require 'db.php'; 

session_start();

header("Content-Type: application/json");

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// --- HANDLE GET REQUESTS ---
if ($method === 'GET') {
    switch ($action) {
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

        case 'get-my-orders':
            if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode([]); exit; }
            $userId = $_SESSION['user_id'];
            $sql = "SELECT o.id, o.total_amount, o.status, o.created_at, GROUP_CONCAT(CONCAT(oi.quantity, 'x ', oi.item_name) SEPARATOR ', ') as items_summary FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE o.customer_id = ? GROUP BY o.id ORDER BY o.created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            echo json_encode($stmt->fetchAll());
            break;

        case 'get-items':
            $stmt = $pdo->query("SELECT i.*, c.name as category_name FROM items i LEFT JOIN categories c ON i.category_id = c.id ORDER BY i.id DESC");
            echo json_encode($stmt->fetchAll());
            break;

        case 'get-categories':
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC");
            echo json_encode($stmt->fetchAll());
            break;
            
        case 'get-item-addons':
            $itemId = $_GET['id'];
            $stmt = $pdo->prepare("SELECT addon_category_id FROM item_addons WHERE item_id = ?");
            $stmt->execute([$itemId]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
            break;

        case 'get-all-addons':
            $stmt = $pdo->query("SELECT * FROM addon_categories ORDER BY display_order ASC");
            $categories = $stmt->fetchAll();
            $optionsStmt = $pdo->prepare("SELECT * FROM addons WHERE addon_category_id = ?");
            $result = [];
            foreach($categories as $category) {
                $optionsStmt->execute([$category['id']]);
                $category['options'] = $optionsStmt->fetchAll();
                $result[] = $category;
            }
            echo json_encode($result);
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

        case 'get-online-orders':
            $sql = "SELECT o.id, o.total_amount, u.name as customer_name, GROUP_CONCAT(CONCAT(oi.quantity, 'x ', oi.item_name) SEPARATOR ', ') as items_summary 
                    FROM orders o 
                    JOIN order_items oi ON o.id = oi.order_id
                    LEFT JOIN users u ON o.customer_id = u.id
                    WHERE o.status = 'New' AND o.order_type = 'Online' 
                    GROUP BY o.id 
                    ORDER BY o.created_at ASC";
            $stmt = $pdo->query($sql);
            echo json_encode($stmt->fetchAll());
            break;
        
        case 'get-order-history':
            $sql = "SELECT o.id, o.total_amount, o.order_type, o.created_at, u.name as customer_name, GROUP_CONCAT(CONCAT(oi.quantity, 'x ', oi.item_name) SEPARATOR ', ') as items_summary 
                    FROM orders o 
                    JOIN order_items oi ON o.id = oi.order_id
                    LEFT JOIN users u ON o.customer_id = u.id
                    WHERE o.status = 'Completed' 
                    GROUP BY o.id 
                    ORDER BY o.created_at DESC 
                    LIMIT 50";
            $stmt = $pdo->query($sql);
            echo json_encode($stmt->fetchAll());
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found.']);
            break;
    }
    exit;
}

// --- HANDLE POST REQUESTS ---
if ($method === 'POST') {
    // Check if it's form-data (for file uploads) or json
    if (strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        $data = $_POST; // For form-data
    }

    switch ($action) {
        case 'add-item':
        case 'update-item':
            // This logic is now handled at the top of the file before the switch statements
            // It's kept here as a placeholder to prevent "Action not found" errors
            // The actual logic with file upload is at the top.
            break;

        case 'place-online-order':
        case 'place-walkin-order':
            try {
                $pdo->beginTransaction();
                $orderType = ($action === 'place-online-order') ? 'Online' : 'Walk-in';
                $status = ($action === 'place-online-order') ? 'New' : 'Completed';
                $customerId = ($action === 'place-online-order' && isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : null;

                $sqlOrder = "INSERT INTO orders (customer_id, total_amount, status, order_type) VALUES (?, ?, ?, ?)";
                $stmtOrder = $pdo->prepare($sqlOrder);
                $stmtOrder->execute([$customerId, $data['total'], $status, $orderType]);
                $orderId = $pdo->lastInsertId();
                
                $sqlItem = "INSERT INTO order_items (order_id, item_id, item_name, quantity, unit_price) VALUES (?, ?, ?, ?, ?)";
                $stmtItem = $pdo->prepare($sqlItem);
                foreach ($data['items'] as $item) {
                    $stmtItem->execute([$orderId, $item['id'], $item['name'], $item['quantity'], $item['finalPrice']]);
                }
                $pdo->commit();
                echo json_encode(['success' => true, 'orderId' => $orderId]);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'complete-order':
            $id = $data['id'];
            $stmt = $pdo->prepare("UPDATE orders SET status = 'Completed' WHERE id = ?");
            if ($stmt->execute([$id])) echo json_encode(['success' => true]);
            else echo json_encode(['success' => false, 'message' => 'Failed to update order status.']);
            break;

        case 'delete-item':
            $id = $data['id'];
            $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
            if ($stmt->execute([$id])) echo json_encode(['success' => true]);
            else echo json_encode(['success' => false, 'message' => 'Failed to delete item.']);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found for POST request.']);
            break;
    }
    exit;
}
?>
