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
    echo json_encode(["status"=>"success","data"=>$vehicles]);
    exit;
}

// GET single vehicle by ID 
if ($method === 'GET' && $action === 'get' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id=?");
    $stmt->execute([$id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($vehicle) {
        echo json_encode(["status"=>"success","data"=>$vehicle]);
    } else {
        echo json_encode(["status"=>"error","message"=>"Vehicle not found"]);
    }
    exit;
}

// POST update vehicle status
if ($method === 'POST' && $action === 'updateStatus') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("UPDATE vehicles SET availability_status=? WHERE id=?");
    $stmt->execute([$input['status'], $input['vehicle_id']]);
    
    echo json_encode(['status' => 'success', 'message' => 'Vehicle status updated']);
    exit;
}

echo json_encode(["status"=>"error","message"=>"Invalid action"]);