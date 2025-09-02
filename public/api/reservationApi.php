<?php
header("Content-Type: application/json");

// DB CONNECTION 
require_once __DIR__ . '/../../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

//  GET all reservations 
if ($method === 'GET' && $action === 'getAll') {
    $stmt = $pdo->query("SELECT * FROM reservations");
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "data" => $reservations]);
    exit;
}

//  GET single reservation by id
if ($method === 'GET' && $action === 'get' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id=?");
    $stmt->execute([$id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($reservation) {
        echo json_encode(["status" => "success", "data" => $reservation]);
    } else {
        echo json_encode(["status" => "error", "message" => "Reservation not found"]);
    }
    exit;
}

// GET reservations by status
if ($method === 'GET' && $action === 'getByStatus') {
    $status = $_GET['status'] ?? null;

    if (!$status) {
        echo json_encode(["status" => "error", "message" => "Status is required"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE status=?");
    $stmt->execute([$status]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $reservations]);
    exit;
}

// POST add reservation 
if ($method === 'POST' && $action === 'add') {
    $input = json_decode(file_get_contents("php://input"), true);

    // Default values
    $status = 'ongoing';
    $customer_id = 1; // Put for now first 

    $now = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("
        INSERT INTO reservations 
            (customer_id, vehicle_id, pickup_date, return_date, days, total_cost, payment_method, status, created_at, updated_at) 
        VALUES (?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->execute([
        $input['customer_id'],
        $input['vehicle_id'],
        $input['pickup_date'],
        $input['return_date'],
        $input['days'],   // ✅ use user input
        $input['total_cost'],
        $input['payment_method'],
        $status,
        $now,
        $now
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "Reservation added",
        "id" => $pdo->lastInsertId()
    ]);
    exit;
}

// PUT update reservation status only 
if ($method === 'PUT' && $action === 'edit' && $id) {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['status'])) {
        echo json_encode(["status" => "error", "message" => "Status is required"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE reservations SET status=? WHERE id=?");
    $stmt->execute([$input['status'], $id]);

    echo json_encode(["status" => "success", "message" => "Reservation status updated"]);
    exit;
}

// DELETE reservation 
if ($method === 'DELETE' && $action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM reservations WHERE id=?");
    $stmt->execute([$id]);

    echo json_encode(["status" => "success", "message" => "Reservation deleted"]);
    exit;
}

// Invalid action 
echo json_encode(["status" => "error", "message" => "Invalid action"]);
