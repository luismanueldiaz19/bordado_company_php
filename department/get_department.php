<?php
include '../conexion.php';
include '../utils.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'success' => false,
        'message' => 'Método no permitido. Solo se permite POST.'
    ]);
    exit;
}

$query = "SELECT id, name_department, date, statu, nivel, id_key_work, type
          FROM public.departments
          ORDER BY name_department ASC";

$res = pg_query($conn, $query);

if (!$res) {
    json_response([
        'success' => false,
        'message' => 'Error al obtener datos: ' . pg_last_error($conn)
    ]);
    exit;
}

$rows = pg_fetch_all($res) ?? [];

json_response([
    'success' => true,
    'data' => $rows,
    'total' => count($rows)
]);
