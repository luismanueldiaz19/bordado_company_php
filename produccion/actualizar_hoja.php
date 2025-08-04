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
    $hoja_id = $data['hoja_produccion_id'] ?? null;
    $start_date = $data['start_date'] ?? null;
    $end_date = $data['end_date'] ?? null;
    $estado_hoja = $data['estado_hoja'] ?? null;
    $observaciones_hoja = $data['observaciones_hoja'] ?? null;
    $orden_items_id = $data['orden_items_id'] ?? null;

   $missingFields = [];

if (!$hoja_id) $missingFields[] = 'hoja_produccion_id';
if (!$start_date) $missingFields[] = 'start_date';
if (!$orden_items_id) $missingFields[] = 'orden_items_id';
if (!$estado_hoja) $missingFields[] = 'estado_hoja';

if (!empty($missingFields)) {
    json_response([
        'success' => false,
        'message' => 'Faltan datos obligatorios: ' . implode(', ', $missingFields)
    ]);
    exit;
}

    // Preparar y ejecutar la consulta
    $sql = "UPDATE public.hoja_produccion
            SET start_date = $1,
                end_date = $2,
                estado_hoja = $3,
                observaciones_hoja = $4
            WHERE hoja_produccion_id = $5";

    $params = [$start_date, $end_date, $estado_hoja, $observaciones_hoja, $hoja_id];

    $res = pg_query_params($conn, $sql, $params);

    if (!$res) {
        throw new Exception('Error al actualizar: ' . pg_last_error($conn));
    }
    // ✅ Actualizar estado_produccion en orden_items
     $sql_update_orden = "UPDATE public.orden_items SET estado_produccion = $1 WHERE orden_items_id = $2";
     $res_update = pg_query_params($conn, $sql_update_orden, [$estado_hoja, $orden_items_id]);

if (!$res_update) {

    json_response(['success' => false, 'message' => 'Error actualizando estado de orden_items']);
    exit;
}

    json_response([
        'success' => true,
        'message' => 'Hoja de produccion actualizada correctamente'
    ]);
} catch (Exception $e) {
    json_response([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
