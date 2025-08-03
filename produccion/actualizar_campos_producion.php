<?php
include '../conexion.php';
include '../utils.php';

header('Content-Type: application/json');

try {
    // Verificar que el método sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Se requiere POST.');
    }

    // Obtener y decodificar los datos del cuerpo de la solicitud
    $data = json_decode(file_get_contents('php://input'), true);

    // Validar datos requeridos
    $hoja_produccion_campos_id = $data['hoja_produccion_campos_id'] ?? null;
    $cant = $data['cant'] ?? null;


   $missingFields = [];

if (!$hoja_produccion_campos_id) $missingFields[] = 'hoja_produccion_campos_id';
if (!$cant) $missingFields[] = 'cant';
if (!empty($missingFields)) {
    json_response([
        'success' => false,
        'message' => 'Faltan datos obligatorios: ' . implode(', ', $missingFields)
    ]);
    exit;
}

    // Preparar y ejecutar la consulta
    $sql = "UPDATE public.hoja_produccion_campos SET cant=$1 WHERE hoja_produccion_campos_id = $2";

    $params = [$cant, $hoja_produccion_campos_id];

    $res = pg_query_params($conn, $sql, $params);

    if (!$res) {
        throw new Exception('Error al actualizar: ' . pg_last_error($conn));
    }

    json_response([
        'success' => true,
        'message' => 'Cantidad Actualizada Correctamente' . $hoja_produccion_campos_id
    ]);
} catch (Exception $e) {
    json_response([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
