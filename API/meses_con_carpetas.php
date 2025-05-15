<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include 'config.php';

$queryMeses = $conn->query("SELECT * FROM meses ORDER BY fecha_inicio DESC");
$meses = [];

while ($mes = $queryMeses->fetch_assoc()) {
  $mesId = $mes['id'];
  $queryCarpetas = $conn->query("SELECT id, nombre FROM carpetas WHERE mes_id = $mesId");

  $carpetas = [];
  while ($carpeta = $queryCarpetas->fetch_assoc()) {
    $carpetas[] = $carpeta;
  }

  $mes['carpetas'] = $carpetas;
  $meses[] = $mes;
}

echo json_encode($meses);
