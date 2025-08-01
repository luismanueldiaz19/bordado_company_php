<?php
include '../conexion.php';
include '../utils.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Leer parámetros del body o GET
$data = json_decode(file_get_contents("php://input"), true) ?? $_GET;

$limit = isset($data['limit']) ? (int)$data['limit'] : 10;
$offset = isset($data['offset']) ? (int)$data['offset'] : 0;
$filtro = trim($data['filtro'] ?? '');

$whereClause = '';
$params = [];
$index = 1;

if (!empty($filtro)) {
    $whereClause = "WHERE LOWER(nombre) LIKE LOWER($" . $index . ")";
    $params[] = '%' . $filtro . '%';
    $index++;
}

$query = "
    SELECT id_producto,codigo, nombre, descripcion, precio, itbis, tipo, creado_en,
           precio_mayor, precio_oferta, precio_especial
    FROM public.productos
    $whereClause
    ORDER BY creado_en DESC
    LIMIT $" . $index . " OFFSET $" . ($index + 1) . "
";

$params[] = $limit;
$params[] = $offset;

$result = pg_query_params($conn, $query, $params);

if (!$result) {
    json_response([
        'success' => false,
        'message' => 'Error en la consulta: ' . pg_last_error($conn)
    ], 500);
}

$productos = pg_fetch_all($result) ?? [];

// Total para paginación (sin limit/offset)
$totalQuery = "SELECT COUNT(*) FROM public.productos $whereClause";
$totalResult = pg_query_params($conn, $totalQuery, array_slice($params, 0, $index - 1));
$total = pg_fetch_result($totalResult, 0, 0);

json_response([
    "success" => true,
    "productos" => $productos,
    "total" => (int)$total
]);
