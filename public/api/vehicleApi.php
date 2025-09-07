<?php
header("Content-Type: application/json");

// DB CONNECTION 
require_once __DIR__ . '/../../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

// GET all vehicles
if ($method === 'GET' && $action === 'getAll') {
    $stmt = $pdo->query("SELECT * FROM vehicles");
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "data" => $vehicles]);
    exit;
}

// GET single vehicle by ID 
if ($method === 'GET' && $action === 'get' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id=?");
    $stmt->execute([$id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($vehicle) {
        echo json_encode(["status" => "success", "data" => $vehicle]);
    } else {
        echo json_encode(["status" => "error", "message" => "Vehicle not found"]);
    }
    exit;
}

// POST update full vehicle details
if ($method === 'POST' && $action === 'update') {
    $input = json_decode(file_get_contents('php://input'), true);

    $stmt = $pdo->prepare("
        UPDATE vehicles 
        SET type=?, brand=?, model=?, registration_number=?, rental_price=?, availability_status=? 
        WHERE id=?
    ");

    $stmt->execute([
        $input['type'],
        $input['brand'],
        $input['model'],
        $input['registration_number'],
        $input['rental_price'],
        $input['availability_status'],
        $input['id']
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Vehicle updated successfully']);
    } else {
        echo json_encode(["status" => "error", "message" => "No changes made or invalid ID"]);
    }

    exit;
}

// POST update only availability status
if ($method === 'POST' && $action === 'updateStatus') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['vehicle_id'], $input['availability_status'])) {
        echo json_encode(['status' => 'error', 'message' => 'vehicle_id and availability_status required']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE vehicles SET availability_status=? WHERE id=?");
    $stmt->execute([
        $input['availability_status'],
        $input['vehicle_id']
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Vehicle status updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid vehicle ID or no change']);
    }

    exit;
}