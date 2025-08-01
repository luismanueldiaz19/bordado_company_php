<?php
include '../conexion.php';
include '../utils.php';
include '../auditoria/auditoria_log.php'; // ✅ Auditoría

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Método no permitido']);
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validar datos mínimos
if (
    empty($data['id_producto']) ||
    empty($data['nombre']) ||
    !isset($data['precio']) ||
    !isset($data['itbis']) ||
    empty($data['tipo'])
) {
    json_response(['success' => false, 'message' => 'Faltan datos obligatorios'], 400);
}

if (empty($data['usuario_id'])) {
    json_response(['success' => false, 'message' => 'Usuario no autenticado'], 401);
}

$id_producto = $data['id_producto'];
$usuario_id = $data['usuario_id'];

// ✅ Obtener datos anteriores antes del UPDATE
$consultaAnterior = pg_query_params($conn, "SELECT * FROM productos WHERE id_producto = $1", [$id_producto]);
$datosAnteriores = pg_fetch_assoc($consultaAnterior);

// ✅ Realizar UPDATE
$sql = "UPDATE public.productos SET
          nombre = $1,
          descripcion = $2,
          precio = $3,
          itbis = $4,
          tipo = $5,
          precio_mayor = $6,
          precio_oferta = $7,
          precio_especial = $8
        WHERE id_producto = $9";

$params = [
    $data['nombre'],
    $data['descripcion'] ?? '',
    $data['precio'],
    $data['itbis'],
    $data['tipo'],
    $data['precio_mayor'] ?? 0,
    $data['precio_oferta'] ?? 0,
    $data['precio_especial'] ?? 0,
    $id_producto
];

$result = pg_query_params($conn, $sql, $params);

// ✅ Registrar auditoría si se actualizó correctamente
if ($result) {
    registrarAuditoria(
        $conn,
        $usuario_id,
        'UPDATE',
        'productos',
        $datosAnteriores,
        $data
    );

    json_response(['success' => true, 'message' => 'Producto actualizado correctamente']);
} else {
    json_response(['success' => false, 'message' => 'Error al actualizar: ' . pg_last_error($conn)], 500);
}

pg_close($conn);
