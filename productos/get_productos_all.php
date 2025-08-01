<?php
include '../conexion.php';
include '../utils.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$all = isset($_GET['all']) && $_GET['all'] === 'true';

if ($all) {
    // Traer todos sin paginar ni filtrar
    $query = "SELECT id_producto, nombre,codigo, descripcion, precio, itbis, tipo, creado_en, precio_mayor, precio_oferta, precio_especial FROM public.productos ORDER BY creado_en DESC";
    $result = pg_query($conn, $query);
    $productos = pg_fetch_all($result) ?? [];

    echo json_encode([
        'success' => true,
        'productos' => $productos,
        'total' => count($productos),
    ]);
    exit;
}

// Aquí continúa tu lógica original con limit, offset y filtro...

// Aquí continúa tu lógica original con limit, offset y filtro...
