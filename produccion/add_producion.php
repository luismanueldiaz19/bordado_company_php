<?php
include '../conexion.php';
include '../utils.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validaciones iniciales
$start_date = $data['start_date'] ?? null;
$usuario_id = $data['usuario_id'] ?? null;
$orden_items_id = $data['orden_items_id'] ?? null;
$tipo_trabajo_id = $data['tipo_trabajo_id'] ?? null;
$campoos = $data['campoos'] ?? [];

if (!$start_date || !$usuario_id || !$orden_items_id || !$tipo_trabajo_id || !is_array($campoos)) {
    json_response(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

pg_query($conn, "BEGIN");

// Insertar hoja_produccion
$sql_hoja = "INSERT INTO public.hoja_produccion(start_date, usuario_id, orden_items_id, tipo_trabajo_id)
             VALUES ($1, $2, $3, $4) RETURNING hoja_produccion_id";

$res_hoja = pg_query_params($conn, $sql_hoja, [$start_date, $usuario_id, $orden_items_id, $tipo_trabajo_id]);

if (!$res_hoja) {
    pg_query($conn, "ROLLBACK");
    json_response(['success' => false, 'message' => 'Error insertando hoja_produccion']);
    exit;
}

$hoja_id = pg_fetch_result($res_hoja, 0, 'hoja_produccion_id');

$errores = [];
$mensajes = [];

$sql_campo = "INSERT INTO public.hoja_produccion_campos(hoja_produccion_id, campo_id, cant)
              VALUES ($1, $2, $3)";

foreach ($campoos as $campo) {
    $campo_id = $campo['campo_id'] ?? null;
    $cant = isset($campo['cant']) ? intval($campo['cant']) : 0;

    if (!$campo_id) continue;

    $res_campo = pg_query_params($conn, $sql_campo, [$hoja_id, $campo_id, $cant]);

    if (!$res_campo) {
        $errores[] = "Error al insertar campo con ID: $campo_id";
    } else {
        $mensajes[] = "Campo ID $campo_id insertado correctamente"; // Esto solo se mostrará al final
    }
}

// Finalizar transacción
if (count($errores) > 0) {
    pg_query($conn, "ROLLBACK");
    json_response([
        'success' => false,
        'hoja_id' => $hoja_id,
        'message' => 'Errores al insertar campos',
        'errores' => $errores,
        'mensajes_exitosos' => $mensajes
    ]);
    exit;
}

pg_query($conn, "COMMIT");
json_response([
    'success' => true,
    'hoja_id' => $hoja_id,
    'message' => 'Hoja insertada correctamente',
    'mensajes' => $mensajes
]);
