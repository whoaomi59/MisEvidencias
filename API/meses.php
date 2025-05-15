<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $result = $conn->query("SELECT * FROM meses ORDER BY fecha_inicio DESC");
  $meses = [];

  while ($row = $result->fetch_assoc()) {
    $meses[] = $row;
  }

  echo json_encode($meses);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer el contenido JSON enviado
    $data = json_decode(file_get_contents('php://input'), true);

    // Validar que existan los campos
    $nombre = $data['nombre'] ?? null;
    $fecha_inicio = $data['fecha_inicio'] ?? null;
    $fecha_fin = $data['fecha_fin'] ?? null;

    if ($nombre && $fecha_inicio && $fecha_fin) {
        $stmt = $conn->prepare("INSERT INTO meses (nombre, fecha_inicio, fecha_fin) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $fecha_inicio, $fecha_fin);
        $stmt->execute();

        echo json_encode(["status" => "ok", "id" => $stmt->insert_id]);
    } else {
        echo json_encode(["status" => "error", "message" => "Faltan datos requeridos"]);
    }
}

?>
