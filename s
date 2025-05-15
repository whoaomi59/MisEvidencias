<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include 'config.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo "ID del mes requerido";
    exit;
}

$mes_id = intval($_GET['id']);

// Buscar nombre del mes
$sqlMes = $conn->prepare("SELECT nombre FROM meses WHERE id = ?");
$sqlMes->bind_param("i", $mes_id);
$sqlMes->execute();
$resultMes = $sqlMes->get_result();

if ($resultMes->num_rows === 0) {
    http_response_code(404);
    echo "Mes no encontrado";
    exit;
}

$mes = $resultMes->fetch_assoc();
$nombreMes = $mes['nombre'];

$zip = new ZipArchive();
$zipFileName = tempnam(sys_get_temp_dir(), "evidencias_") . ".zip";

if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
    http_response_code(500);
    echo "No se pudo crear el archivo ZIP";
    exit;
}

$imagenesAgregadas = 0;

// Buscar carpetas del mes
$sqlCarpetas = $conn->prepare("SELECT id, nombre FROM carpetas WHERE mes_id = ?");
$sqlCarpetas->bind_param("i", $mes_id);
$sqlCarpetas->execute();
$resultCarpetas = $sqlCarpetas->get_result();

while ($carpeta = $resultCarpetas->fetch_assoc()) {
    $carpetaId = $carpeta['id'];
    $carpetaName = $carpeta['nombre'];

    $sqlEvidencias = $conn->prepare("SELECT id, nombre FROM evidencias WHERE carpeta_id = ?");
    $sqlEvidencias->bind_param("i", $carpetaId);
    $sqlEvidencias->execute();
    $resultEvidencias = $sqlEvidencias->get_result();

    while ($evidencia = $resultEvidencias->fetch_assoc()) {
        $evidenciaId = $evidencia['id'];
        $evidenciaNombre = $evidencia['nombre'];

        $sqlImagenes = $conn->prepare("SELECT ruta FROM imagenes WHERE evidencia_id = ?");
        $sqlImagenes->bind_param("i", $evidenciaId);
        $sqlImagenes->execute();
        $resultImagenes = $sqlImagenes->get_result();

        while ($imagen = $resultImagenes->fetch_assoc()) {
            // Aquí concatenamos solo __DIR__ con la ruta relativa de la BD, que ya incluye 'uploads/'
            $imgPath = __DIR__ . '/' . $imagen['ruta'];

            if (file_exists($imgPath)) {
                $imgName = basename($imgPath);
                $zipPath = "$nombreMes/$carpetaName/$evidenciaNombre/$imgName";

                $zip->addFile($imgPath, $zipPath);
                $imagenesAgregadas++;
            } else {
                error_log("No existe imagen: $imgPath");
            }
        }
    }
}

$zip->close();

if ($imagenesAgregadas === 0) {
    http_response_code(404);
    echo "No se encontraron imágenes para incluir en el ZIP.";

    if (file_exists($zipFileName)) {
        unlink($zipFileName);
    }
    exit;
}

// Enviar archivo zip para descarga
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="Evidencias_'.$nombreMes.'.zip"');
header('Content-Length: ' . filesize($zipFileName));

readfile($zipFileName);

if (file_exists($zipFileName)) {
    unlink($zipFileName);
}

exit;
?>
