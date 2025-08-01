<?php
include '../conexion.php';
include '../utils.php';

$data = json_decode(file_get_contents("php://input"), true);

// Validación básica
if (empty($data['nombre']) || empty($data['precio'])) {
    json_response(["success" => false, "message" => "Nombre y precio son obligatorios"], 400);
}

// Valores por defecto si no vienen en el JSON
$descripcion      = $data['descripcion'] ?? '';
$precio           = $data['precio'];
$precio_mayor     = $data['precio_mayor'] ?? 0;
$precio_oferta    = $data['precio_oferta'] ?? 0;
$precio_especial  = $data['precio_especial'] ?? 0;
$itbis            = $data['itbis'] ?? 18;
$tipo             = $data['tipo'] ?? 'BIEN';
$codigo           = $data['codigo'] ?? 'N/A';

// Consulta SQL
$sql = "INSERT INTO productos 
        (nombre, descripcion, precio, precio_mayor, precio_oferta, precio_especial, itbis, tipo,codigo)
        VALUES ($1, $2, $3, $4, $5, $6, $7, $8,$9)";

$params = [
    $data['nombre'],
    $descripcion,
    $precio,
    $precio_mayor,
    $precio_oferta,
    $precio_especial,
    $itbis,
    $tipo,
    $codigo,
];

$result = pg_query_params($conn, $sql, $params);

if ($result) {
    json_response(["success" => true, "message" => "Producto creado exitosamente"]);
} else {
    json_response(["success" => false, "message" => "Error al crear producto: " . pg_last_error($conn)], 500);
}
