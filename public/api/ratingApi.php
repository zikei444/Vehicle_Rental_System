<?php
header("Content-Type: application/json");

// Database connection
$host = "localhost";
$user = "root";
$pass = "123456"; 
$db   = "vehiclerentaldb"; 

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB connection failed"]));
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

// GET all approved ratings (for customers)
if ($method === 'GET' && $action === 'getAll') {
    $result = $conn->query("SELECT * FROM ratings WHERE approved = 1");
    $ratings = [];
    while($row = $result->fetch_assoc()) $ratings[] = $row;
    echo json_encode(["status"=>"success","data"=>$ratings]);
    exit;
}

// POST add rating (default approved = 0, pending admin approval)
if ($method === 'POST' && $action === 'add') {
    $input = json_decode(file_get_contents("php://input"),true);
    $stmt = $conn->prepare("INSERT INTO ratings (rental_id, vehicle_id, user_id, rating, comment, approved) VALUES (?,?,?,?,?,0)");
    $stmt->bind_param("iiiis",$input['rental_id'],$input['vehicle_id'],$input['user_id'],$input['rating'],$input['comment']);
    $stmt->execute();
    echo json_encode(["status"=>"success","message"=>"Rating submitted, pending admin approval"]);
    exit;
}

// PUT update rating (user edit before approval)
if ($method === 'PUT' && $action === 'edit' && $id) {
    $input = json_decode(file_get_contents("php://input"),true);
    $stmt = $conn->prepare("UPDATE ratings SET rating=?, comment=? WHERE id=? AND approved=0");
    $stmt->bind_param("isi",$input['rating'],$input['comment'],$id);
    $stmt->execute();
    echo json_encode(["status"=>"success","message"=>"Rating updated (if not yet approved)"]);
    exit;
}

// DELETE rating
if ($method === 'DELETE' && $action === 'delete' && $id) {
    $stmt = $conn->prepare("DELETE FROM ratings WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    echo json_encode(["status"=>"success","message"=>"Rating deleted"]);
    exit;
}

// ADMIN: Approve rating
if ($method === 'PUT' && $action === 'approve' && $id) {
    $stmt = $conn->prepare("UPDATE ratings SET approved=1 WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    echo json_encode(["status"=>"success","message"=>"Rating approved"]);
    exit;
}

// ADMIN: View all ratings (approved + pending)
if ($method === 'GET' && $action === 'getAllAdmin') {
    $result = $conn->query("SELECT * FROM ratings");
    $ratings = [];
    while($row = $result->fetch_assoc()) $ratings[] = $row;
    echo json_encode(["status"=>"success","data"=>$ratings]);
    exit;
}

echo json_encode(["status"=>"error","message"=>"Invalid action"]);
$conn->close();
