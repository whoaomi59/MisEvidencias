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
  $carpeta_id = $_GET['carpeta_id'];

  $stmt = $conn->prepare("
    SELECT e.id, e.nombre, e.fecha,
      (SELECT GROUP_CONCAT(ruta SEPARATOR '|||') FROM imagenes WHERE evidencia_id = e.id) AS imagenes
    FROM evidencias e
    WHERE carpeta_id = ?
    ORDER BY e.fecha DESC
  ");
  $stmt->bind_param("i", $carpeta_id);
  $stmt->execute();
  $result = $stmt->get_result();

  $evidencias = [];
  while ($row = $result->fetch_assoc()) {
    $rutas = explode('|||', $row['imagenes'] ?? '');
    $row['imagenes'] = array_map(fn($ruta) => ['img' => $ruta], $rutas);
    $evidencias[] = $row;
  }

  echo json_encode($evidencias);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carpeta_id = $_POST['carpeta_id'];
    $nombre = $_POST['nombre'];

    // 1. Insertar evidencia
    $stmt = $conn->prepare("INSERT INTO evidencias (carpeta_id, nombre) VALUES (?, ?)");
   $stmt->bind_param("is", $carpeta_id, $nombre);
    $stmt->execute();
    $evidencia_id = $stmt->insert_id;

    // 2. Subir imágenes
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {
        $name = basename($_FILES['imagenes']['name'][$index]);
        $targetPath = $upload_dir . uniqid() . "_" . $name;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $ruta = $targetPath;

            $stmt = $conn->prepare("INSERT INTO imagenes (evidencia_id, ruta) VALUES (?, ?)");
            $stmt->bind_param("is", $evidencia_id, $ruta);
            $stmt->execute();
        }
    }

    echo json_encode(["status" => "ok", "id" => $evidencia_id]);
}
?>
