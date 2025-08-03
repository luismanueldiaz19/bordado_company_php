<?php
include '../conexion.php';
include '../utils.php';

header('Content-Type: application/json');

try {
    // Verificar que la solicitud sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Se requiere POST.');
    }

    // Obtener el JSON recibido
    $data = json_decode(file_get_contents('php://input'), true);

    $hoja_id = $data['hoja_produccion_id'] ?? null;

    if (!$hoja_id) {
        throw new Exception('Falta el campo obligatorio: hoja_produccion_id');
    }

    // Ejecutar la eliminación
    $sql = "DELETE FROM public.hoja_produccion WHERE hoja_produccion_id = $1";
    $params = [$hoja_id];
    $res = pg_query_params($conn, $sql, $params);

    if (!$res) {
        throw new Exception('Error al eliminar: ' . pg_last_error($conn));
    }

    json_response([
        'success' => true,
        'message' => 'Hoja de producción eliminada correctamente'
    ]);

} catch (Exception $e) {
    json_response([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
