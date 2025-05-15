<?php
require __DIR__ . '/vendor/autoload.php';
use Mpdf\Mpdf;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
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

// Obtener nombre del mes
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

// Crear archivo ZIP temporal
$zip = new ZipArchive();
$zipFileName = tempnam(sys_get_temp_dir(), "evidencias_") . ".zip";

if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
    http_response_code(500);
    echo "No se pudo crear el archivo ZIP";
    exit;
}

$archivosAgregados = 0;
$pdfTempFiles = []; // To hold temp PDF paths for deleting after ZIP close

// Obtener carpetas del mes
$sqlCarpetas = $conn->prepare("SELECT id, nombre FROM carpetas WHERE mes_id = ?");
$sqlCarpetas->bind_param("i", $mes_id);
$sqlCarpetas->execute();
$resultCarpetas = $sqlCarpetas->get_result();

while ($carpeta = $resultCarpetas->fetch_assoc()) {
    $carpetaId = $carpeta['id'];
    $carpetaName = $carpeta['nombre'];

    // Obtener evidencias de la carpeta
    $sqlEvidencias = $conn->prepare("SELECT id, nombre FROM evidencias WHERE carpeta_id = ?");
    $sqlEvidencias->bind_param("i", $carpetaId);
    $sqlEvidencias->execute();
    $resultEvidencias = $sqlEvidencias->get_result();

    while ($evidencia = $resultEvidencias->fetch_assoc()) {
        $evidenciaId = $evidencia['id'];
        $evidenciaNombre = $evidencia['nombre'];

        // Obtener imágenes de la evidencia
        $sqlImagenes = $conn->prepare("SELECT ruta FROM imagenes WHERE evidencia_id = ?");
        $sqlImagenes->bind_param("i", $evidenciaId);
        $sqlImagenes->execute();
        $resultImagenes = $sqlImagenes->get_result();

        $imagenes = [];
        while ($imagen = $resultImagenes->fetch_assoc()) {
            $imgPath = __DIR__ . '/' . $imagen['ruta'];
            if (file_exists($imgPath)) {
                $imagenes[] = $imgPath;
            } else {
                error_log("No existe imagen: $imgPath");
            }
        }

        error_log("Evidencia '$evidenciaNombre' tiene " . count($imagenes) . " imágenes.");

        if (count($imagenes) > 0) {
            try {
                // Crear PDF con mPDF
                $mpdf = new Mpdf([
                    'tempDir' => __DIR__ . '/tmp'
                ]);

                foreach ($imagenes as $imgPath) {
                    $mpdf->AddPage();
                    // Imagen tamaño A4 (210x297 mm)
                    $mpdf->Image($imgPath, 0, 0, 210, 297, '', '', true, false);
                }

                // Guardar PDF temporal
                $pdfTempPath = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
                $mpdf->Output($pdfTempPath, \Mpdf\Output\Destination::FILE);

                if (file_exists($pdfTempPath) && filesize($pdfTempPath) > 0) {
                    $zipInternalPath = "$nombreMes/$carpetaName/$evidenciaNombre.pdf";
                    $zip->addFile($pdfTempPath, $zipInternalPath);
                    $archivosAgregados++;
                    $pdfTempFiles[] = $pdfTempPath; // Save to delete later
                    error_log("PDF agregado al ZIP: $zipInternalPath");
                } else {
                    error_log("Error: PDF temporal no existe o está vacío: $pdfTempPath");
                    // Delete pdf if empty or missing
                    if (file_exists($pdfTempPath)) {
                        unlink($pdfTempPath);
                    }
                }
            } catch (\Mpdf\MpdfException $e) {
                error_log("Error generando PDF para '$evidenciaNombre': " . $e->getMessage());
            }
        }
    }
}

$zip->close();

// Delete all temp PDF files now
foreach ($pdfTempFiles as $tempFile) {
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
}

error_log("Total de archivos agregados al ZIP: $archivosAgregados");

if ($archivosAgregados === 0) {
    http_response_code(404);
    echo "No se encontraron evidencias con imágenes para incluir en el ZIP.";
    if (file_exists($zipFileName)) {
        unlink($zipFileName);
    }
    exit;
}

// Enviar ZIP para descarga
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="Evidencias_'.$nombreMes.'.zip"');
header('Content-Length: ' . filesize($zipFileName));

// Clean output buffer to prevent corruption
while (ob_get_level()) {
    ob_end_clean();
}

readfile($zipFileName);

// Borrar archivo ZIP temporal
unlink($zipFileName);
exit;
?>

