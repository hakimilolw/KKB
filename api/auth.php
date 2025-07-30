<?php
require 'db.php'; // Your database connection

// Start the session to store login state
session_start();

header("Content-Type: application/json");
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    switch ($action) {
        case 'register':
            if (empty($data['phone']) || empty($data['password']) || empty($data['name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'All fields are required.']);
                exit;
            }
            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE phone_number = ?");
            $stmt->execute([$data['phone']]);
            if ($stmt->fetch()) {
                http_response_code(409); // Conflict
                echo json_encode(['success' => false, 'message' => 'A user with this phone number already exists.']);
                exit;
            }
            // Hash the password for security
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (phone_number, password, name) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$data['phone'], $hashedPassword, $data['name']])) {
                // Automatically log the user in after registration
                $userId = $pdo->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $data['name'];
                $_SESSION['user_phone'] = $data['phone'];
                echo json_encode(['success' => true, 'message' => 'Registration successful!']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Registration failed.']);
            }
            break;

        case 'login':
            if (empty($data['phone']) || empty($data['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Phone and password are required.']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number = ?");
            $stmt->execute([$data['phone']]);
            $user = $stmt->fetch();
            if ($user && password_verify($data['password'], $user['password'])) {
                // Login successful, store user info in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_phone'] = $user['phone_number'];
                echo json_encode(['success' => true, 'message' => 'Login successful!', 'user' => ['name' => $user['name'], 'phone' => $user['phone_number']]]);
            } else {
                http_response_code(401); // Unauthorized
                echo json_encode(['success' => false, 'message' => 'Invalid phone number or password.']);
            }
            break;

        case 'update-profile':
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'You are not logged in.']);
                exit;
            }
            if (empty($data['name']) || empty($data['phone'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Name and phone cannot be empty.']);
                exit;
            }
            $sql = "UPDATE users SET name = ?, phone_number = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$data['name'], $data['phone'], $_SESSION['user_id']])) {
                $_SESSION['user_name'] = $data['name'];
                $_SESSION['user_phone'] = $data['phone'];
                echo json_encode(['success' => true, 'message' => 'Profile updated successfully!', 'user' => ['name' => $data['name'], 'phone' => $data['phone']]]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
            }
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($action) {
        case 'check-status':
            if (isset($_SESSION['user_id'])) {
                echo json_encode(['loggedIn' => true, 'user' => ['name' => $_SESSION['user_name'], 'phone' => $_SESSION['user_phone']]]);
            } else {
                echo json_encode(['loggedIn' => false]);
            }
            break;

        case 'logout':
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
            break;
    }
}
?>
