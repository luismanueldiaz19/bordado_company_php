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

if (empty($data['id_producto'])) {
  json_response(['success' => false, 'message' => 'ID del producto requerido'], 400);
}

if (empty($data['usuario_id'])) {
  json_response(['success' => false, 'message' => 'Usuario no autenticado'], 401);
}

$id_producto = $data['id_producto'];
$usuario_id = $data['usuario_id'];

// ✅ Obtener datos antes de eliminar (para auditar)
$producto_result = pg_query_params($conn, "SELECT * FROM productos WHERE id_producto = $1", [$id_producto]);
$producto_anterior = pg_fetch_assoc($producto_result);

if (!$producto_anterior) {
  json_response(['success' => false, 'message' => 'Producto no encontrado'], 404);
}

// ✅ Eliminar producto
$result = pg_query_params($conn, "DELETE FROM productos WHERE id_producto = $1", [$id_producto]);

if ($result) {
  // ✅ Registrar auditoría
  registrarAuditoria(
    $conn,
    $usuario_id,
    'DELETE',
    'productos',
    $producto_anterior,
    null
  );

  json_response(['success' => true, 'message' => 'Producto eliminado correctamente']);
} else {
  json_response(['success' => false, 'message' => 'Error al eliminar producto: ' . pg_last_error($conn)], 500);
}

pg_close($conn);
