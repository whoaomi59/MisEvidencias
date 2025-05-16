<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include 'config.php';

// Verifica si se proporciona un ID de mes
if (isset($_GET['id'])) {
    $mesId = intval($_GET['id']);

    // Consulta solo el mes correspondiente
    $queryMes = $conn->query("SELECT * FROM meses WHERE id = $mesId");

    if ($mes = $queryMes->fetch_assoc()) {
        // Busca las carpetas relacionadas con ese mes
        $queryCarpetas = $conn->query("SELECT id, nombre FROM carpetas WHERE mes_id = $mesId");

        $carpetas = [];
        while ($carpeta = $queryCarpetas->fetch_assoc()) {
            $carpetaId = $carpeta['id'];

            // Consulta archivos por carpeta
            $queryArchivos = $conn->query("SELECT id, nombre FROM evidencias WHERE carpeta_id = $carpetaId");

            $archivos = [];
            while ($archivo = $queryArchivos->fetch_assoc()) {
                $archivos[] = $archivo;
            }

            $carpeta['archivos'] = $archivos; // añadir archivos a la carpeta
            $carpetas[] = $carpeta;
        }

        $mes['carpetas'] = $carpetas;

        echo json_encode($mes);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Mes no encontrado"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Falta el parámetro 'id'"]);
}
?>
