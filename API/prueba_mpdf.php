<?php
// test_mpdf.php

// 1. Intenta incluir el autoload
require_once __DIR__ . '/vendor/autoload.php';

// 2. Verifica si la clase existe
if (!class_exists('Mpdf\Mpdf')) {
    die("❌ Error: No se pudo cargar la clase Mpdf\\Mpdf. Verifica la instalación.");
}

// 3. Si llega aquí, la clase fue cargada correctamente
echo "✅ La clase Mpdf\\Mpdf se cargó exitosamente.<br>";

// 4. Crear un PDF simple
try {
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML('<h1>Hola desde mPDF</h1>');
    $mpdf->Output('salida.pdf', \Mpdf\Output\Destination::INLINE); // Mostrar en navegador
} catch (\Mpdf\MpdfException $e) {
    echo "❌ Error al generar el PDF: " . $e->getMessage();
}
