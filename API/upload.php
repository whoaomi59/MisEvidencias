<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $evidencia_id = $_POST['evidencia_id'];
  $uploaded = [];

  foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {
    $fileName = basename($_FILES['imagenes']['name'][$index]);
    $targetPath = "uploads/" . time() . "_" . $fileName;

    if (move_uploaded_file($tmpName, $targetPath)) {
      $stmt = $conn->prepare("INSERT INTO imagenes (evidencia_id, ruta) VALUES (?, ?)");
      $stmt->bind_param("is", $evidencia_id, $targetPath);
      $stmt->execute();
      $uploaded[] = $targetPath;
    }
  }

  echo json_encode(["status" => "ok", "archivos" => $uploaded]);
}
?>
