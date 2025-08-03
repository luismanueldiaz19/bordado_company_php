<?php
include '../conexion.php';
include '../utils.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception('Método no permitido. Solo se acepta POST.');
    }

    if (!$conn) {
        throw new Exception('No se pudo establecer la conexión a la base de datos.');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $area = $input['area'] ?? null;

    if (!$area) {
        throw new Exception('El campo "area" es requerido.');
    }

    $query = "
        SELECT 
            tt.tipo_trabajo_id,
            tt.name_trabajo,
            tt.area_trabajo,
            tt.index_orden,
            tt.url_imagen,
            tt.created_at,
            tc.campo_id,
            tc.nombre_campo,
            tc.tipo_dato
        FROM public.tipo_trabajo tt
        LEFT JOIN public.trabajo_campos tc ON tt.tipo_trabajo_id = tc.tipo_trabajo_id
        WHERE tt.area_trabajo = $1
        ORDER BY tt.tipo_trabajo_id, tc.campo_id
    ";

    $res = pg_query_params($conn, $query, [$area]);

    if (!$res) {
        throw new Exception('Error al ejecutar la consulta: ' . pg_last_error($conn));
    }

    $rows = pg_fetch_all($res) ?? [];

    // Agrupar por name_trabajo
    $grouped = [];
    foreach ($rows as $row) {
        $key = $row['name_trabajo'];
        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'tipo_trabajo_id' => $row['tipo_trabajo_id'],
                'name_trabajo' => $row['name_trabajo'],
                'area_trabajo' => $row['area_trabajo'],
                'index_orden' => $row['index_orden'],
                'url_imagen' => $row['url_imagen'],
                'created_at' => $row['created_at'],
                'campos' => []
            ];
        }

        // Si hay campos, agrégalos
        if (!empty($row['campo_id'])) {
            $grouped[$key]['campos'][] = [
                'campo_id' => $row['campo_id'],
                'nombre_campo' => $row['nombre_campo'],
                'tipo_dato' => $row['tipo_dato']
            ];
        }
    }

    // Convertir a array indexado
    $result = array_values($grouped);

    json_response([
        'success' => true,
        'data' => $result
    ]);

} catch (Exception $e) {
    http_response_code(http_response_code() === 200 ? 500 : http_response_code());
    json_response([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
