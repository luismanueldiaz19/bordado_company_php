<?php
include '../conexion.php';
include '../utils.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'success' => false,
        'message' => 'Método no permitido. Solo se permite POST.'
    ]);
    exit;
}

// Consulta combinada
$query = "SELECT m.tipo_trabajo_id, m.name_trabajo, m.area_trabajo, m.index_orden, m.url_imagen, m.created_at,trabajo_campos.nombre_campo,
trabajo_campos.tipo_dato,trabajo_campos.campo_id
FROM public.tipo_trabajo as m
left join public.trabajo_campos on trabajo_campos.tipo_trabajo_id = m.tipo_trabajo_id
order by m.area_trabajo,  m.index_orden ASC";

$res = pg_query($conn, $query);

if (!$res) {
    json_response([
        'success' => false,
        'message' => 'Error al obtener datos: ' . pg_last_error($conn)
    ]);
    exit;
}

$rows = pg_fetch_all($res) ?? [];

// Reorganizar por tipo_trabajo
$agrupado = [];
foreach ($rows as $row) {
    $id = $row['tipo_trabajo_id'];
    if (!isset($agrupado[$id])) {
        $agrupado[$id] = [
            'tipo_trabajo_id' => $row['tipo_trabajo_id'],
            'name_trabajo' => $row['name_trabajo'],
            'area_trabajo' => $row['area_trabajo'],
            'index_orden' => $row['index_orden'],
            'url_imagen' => $row['url_imagen'],
            'created_at' => $row['created_at'],
            'campos' => []
        ];
    }

    // Si tiene campos asociados
    if (!empty($row['campo_id'])) {
        $agrupado[$id]['campos'][] = [
            'campo_id' => $row['campo_id'],
            'nombre_campo' => $row['nombre_campo'],
            'tipo_dato' => $row['tipo_dato']
        ];
    }
}

// Convertir a array numerado
$resultado = array_values($agrupado);

json_response([
    'success' => true,
    'data' => $resultado,
    'total' => count($resultado)
]);
