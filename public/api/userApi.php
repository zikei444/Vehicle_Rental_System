userApi

<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? null);
$id = $_GET['id'] ?? ($_POST['id'] ?? null);

// ====================== Handle JSON POST ======================
if ($method === 'POST' && empty($_POST)) {
    $jsonInput = file_get_contents('php://input');
    $jsonData = json_decode($jsonInput, true);
    if ($jsonData) {
        $_POST = $jsonData;
        $action = $_POST['action'] ?? $action;
        $id = $_POST['id'] ?? $id;
    }
}

// ============================ REGISTRATION ==========================
if ($method === 'POST' && $action === 'register') {
    $username = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $password = $_POST['password'] ?? null;
    $password_confirmation = $_POST['password_confirmation'] ?? null;
    $role = 'customer';

    if (!$username || !$email || !$password || !$password_confirmation) {
        echo json_encode(["status" => "error", "message" => "All required fields must be filled"]);
        exit;
    }

    if ($role === 'customer' && !$phone) {
        echo json_encode(["status" => "error", "message" => "Phone number is required for customers"]);
        exit;
    }

    if ($password !== $password_confirmation) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "Email already registered"]);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $created_at = $updated_at = date("Y-m-d H:i:s");
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at, updated_at, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $email, $hashedPassword, $created_at, $updated_at, $role]);
    $userId = $pdo->lastInsertId();

    if ($role === 'customer') {
        $stmt = $pdo->prepare("INSERT INTO customers (user_id, phoneNo, created_at, updated_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $phone, $created_at, $updated_at]);
    }

    echo json_encode(["status" => "success", "message" => "User registered successfully"]);
    exit;
}

// ============================ LOGIN ==========================
if ($method === 'POST' && $action === 'login') {
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$email || !$password) {
        echo json_encode(["status" => "error", "message" => "Email and password are required"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Email not registered"]);
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        echo json_encode(["status" => "error", "message" => "Incorrect password"]);
        exit;
    }

    echo json_encode([
        "status" => "success",
        "message" => "Login successful",
        "data" => [
            "id" => $user['id'],
            "name" => $user['name'],
            "email" => $user['email'],
            "role" => $user['role']
        ]
    ]);
    exit;
}

// ============================ GET USER BY ID ==========================
if ($method === 'GET' && $action === 'get' && $id) {
    $stmt = $pdo->prepare("
        SELECT users.id, users.name, users.email, customers.phoneNo
        FROM users
        LEFT JOIN customers ON customers.user_id = users.id
        WHERE users.id = ?
    ");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo json_encode(["status" => "success", "data" => $user]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }
    exit;
}

// ============================ UPDATE PROFILE (POST or PUT) ==========================
if (($method === 'POST' || $method === 'PUT') && $action === 'update' && $id) {
    if ($method === 'PUT') {
        parse_str(file_get_contents("php://input"), $_PUT);
        $_POST['username'] = $_PUT['username'] ?? null;
        $_POST['phone'] = $_PUT['phone'] ?? null;
    }

    $username = $_POST['username'] ?? null;
    $phone = $_POST['phone'] ?? null;

    if (!$username || !$phone) {
        echo json_encode(["status" => "error", "message" => "Username and phone are required"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET name = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$username, $id]);

    $stmt = $pdo->prepare("UPDATE customers SET phoneNo = ?, updated_at = NOW() WHERE user_id = ?");
    $stmt->execute([$phone, $id]);

    echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
    exit;
}

// ============================ GET ALL USERS ==========================
if ($method === 'GET' && $action === 'getAll') {
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "data" => $users]);
    exit;
}

// ============================ GET CUSTOMERS ==========================
if ($method === 'GET' && $action === 'getCustomers') {
    $stmt = $pdo->query("
        SELECT customers.id AS customer_id, users.id AS user_id, users.name, users.email, customers.phoneNo, users.role, users.created_at, users.updated_at
        FROM customers
        JOIN users ON customers.user_id = users.id
    ");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "data" => $customers]);
    exit;
}

// ============================ INVALID ACTION ==========================
echo json_encode(["status" => "error", "message" => "Invalid action"]);
exit;
