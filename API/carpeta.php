<?php
// CORS headers - siempre al inicio del archivo
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Respuesta rápida para preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mes_id = $_GET['mes_id'] ?? null;

    if ($mes_id !== null) {
        $stmt = $conn->prepare("SELECT * FROM carpetas WHERE mes_id = ? ORDER BY fecha_creacion DESC");
        $stmt->bind_param("i", $mes_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $carpetas = [];
        while ($row = $result->fetch_assoc()) {
            $carpetas[] = $row;
        }
        echo json_encode($carpetas);
    } else {
        echo json_encode(["status" => "error", "message" => "mes_id es requerido"]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $nombre = $data['nombre'] ?? null;
    $mes_id = $data['mes_id'] ?? null;

    if ($nombre && $mes_id) {
        $stmt = $conn->prepare("INSERT INTO carpetas (nombre, mes_id) VALUES (?, ?)");
        $stmt->bind_param("si", $nombre, $mes_id);
        $stmt->execute();

        echo json_encode(["status" => "ok", "id" => $stmt->insert_id]);
    } else {
        echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    }
}
?>
