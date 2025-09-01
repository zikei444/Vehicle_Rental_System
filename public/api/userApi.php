<?php
header("Content-Type: application/json");


// DB CONNECTION 
require_once __DIR__ . '/../../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

// ------------------ GET all users ------------------
if ($method === 'GET' && $action === 'getAll') {
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status"=>"success","data"=>$users]);
    exit;
}

// ------------------ GET single user by ID ------------------
if ($method === 'GET' && $action === 'get' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo json_encode(["status"=>"success","data"=>$user]);
    } else {
        echo json_encode(["status"=>"error","message"=>"User not found"]);
    }
    exit;
}

// ------------------ GET all customers (join users + customers) ------------------
if ($method === 'GET' && $action === 'getCustomers') {
    $stmt = $pdo->query("
        SELECT customers.id AS customer_id, users.name, users.email, customers.phoneNo
        FROM customers
        JOIN users ON customers.user_id = users.id
    ");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status"=>"success","data"=>$customers]);
    exit;
}

echo json_encode(["status"=>"error","message"=>"Invalid action"]);