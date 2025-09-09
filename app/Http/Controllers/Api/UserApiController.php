<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? null);
$id = $_GET['id'] ?? ($_POST['id'] ?? null);

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
    echo json_encode($user ? ["status" => "success", "data" => $user] : ["status" => "error", "message" => "User not found"]);
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

// ============================ CREATE USER ==========================
if ($method === 'POST' && $action === 'create') {
    $name = $_POST['name'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $role = $_POST['role'] ?? 'customer';

    if (!$name || !$email || !$password) {
        echo json_encode(["status" => "error", "message" => "Name, Email, and Password are required"]);
        exit;
    }

    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$name, $email, $hashedPassword, $role]);

        echo json_encode(["status" => "success", "message" => "User created successfully", "id" => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Email already exists or invalid data"]);
    }
    exit;
}

// ============================ UPDATE USER ==========================
if ($method === 'POST' && $action === 'update' && $id) {
    $name = $_POST['name'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $role = $_POST['role'] ?? null;

    $fields = [];
    $params = [];

    if ($name) { $fields[] = "name = ?"; $params[] = $name; }
    if ($email) { $fields[] = "email = ?"; $params[] = $email; }
    if ($password) { $fields[] = "password = ?"; $params[] = password_hash($password, PASSWORD_DEFAULT); }
    if ($role) { $fields[] = "role = ?"; $params[] = $role; }

    if (empty($fields)) {
        echo json_encode(["status" => "error", "message" => "No fields to update"]);
        exit;
    }

    $params[] = $id;
    $sql = "UPDATE users SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(["status" => "success", "message" => "User updated successfully"]);
    exit;
}

// ============================ DELETE USER ==========================
if ($method === 'POST' && $action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode($stmt->rowCount()
        ? ["status" => "success", "message" => "User deleted successfully"]
        : ["status" => "error", "message" => "User not found or already deleted"]
    );
    exit;
}

// ============================ INVALID REQUEST ==========================
echo json_encode(["status" => "error", "message" => "Invalid request"]);
exit;
