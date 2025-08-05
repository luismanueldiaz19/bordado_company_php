<?php
include '../conexion.php';
include '../utils.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception('Método no permitido. Solo se acepta POST.');
    }

    if (!$conn) {
        throw new Exception('No se pudo establecer la conexión a la base de datos.');
    }

    $sql = "
        SELECT 
            o.list_ordenes_id,
            o.num_orden,
            o.fecha_creacion,
            o.fecha_entrega,
            o.estado_general,
            o.estado_entrega,
            o.estado_prioritario,
            o.ficha,
            o.observaciones,
            o.name_logo,
            o.usuario_id,
            users.full_name,

            c.id_cliente,
            c.nombre AS cliente_nombre,
            c.apellido AS cliente_apellido,
            c.direccion AS cliente_direccion,
            c.telefono AS cliente_telefono,
            c.correo_electronico AS cliente_email,

            i.orden_items_id,
            i.id_producto,
            i.cant,
            i.precio_final,
            i.detalles_productos,
            i.nota,
            i.estado_produccion,
            i.fecha_item_creacion

        FROM public.list_ordenes o
        LEFT JOIN public.cliente c ON o.id_cliente = c.id_cliente
        LEFT JOIN public.orden_items i ON o.list_ordenes_id = i.list_ordenes_id
        JOIN public.users ON users.id = o.usuario_id
        ORDER BY o.num_orden, i.orden_items_id
    ";

    $res = pg_query($conn, $sql);

    if (!$res) {
        throw new Exception('Error en la consulta: ' . pg_last_error($conn));
    }

    $data = pg_fetch_all($res) ?? [];

    $grouped = [];

    foreach ($data as $row) {
        $key = $row['num_orden'];

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'num_orden' => $row['num_orden'],
                'list_ordenes_id' => $row['list_ordenes_id'],
                'fecha_creacion' => $row['fecha_creacion'],
                'fecha_entrega' => $row['fecha_entrega'],
                'estado_general' => $row['estado_general'],
                'estado_entrega' => $row['estado_entrega'],
                'estado_prioritario' => $row['estado_prioritario'],
                'ficha' => $row['ficha'],
                'observaciones' => $row['observaciones'],
                'name_logo' => $row['name_logo'],
                'usuario_id' => $row['usuario_id'],
                'full_name' => $row['full_name'],

                'cliente' => [
                    'id_cliente' => $row['id_cliente'],
                    'nombre' => $row['cliente_nombre'],
                    'apellido' => $row['cliente_apellido'],
                    'direccion' => $row['cliente_direccion'],
                    'telefono' => $row['cliente_telefono'],
                    'correo_electronico' => $row['cliente_email']
                ],

                'orden_item' => []
            ];
        }

        if (!empty($row['orden_items_id'])) {
            $grouped[$key]['orden_item'][] = [
                'orden_items_id' => $row['orden_items_id'],
                'id_producto' => $row['id_producto'],
                'cant' => $row['cant'],
                'precio_final' => $row['precio_final'],
                'detalles_productos' => $row['detalles_productos'],
                'nota' => $row['nota'],
                'estado_produccion' => $row['estado_produccion'],
                'fecha_item_creacion' => $row['fecha_item_creacion']
            ];
        }
    }

    $result = array_values($grouped);

    json_response([
        'success' => true,
        'count' => count($result),
        'data' => $result
    ]);

} catch (Exception $e) {
    http_response_code(500);
    json_response([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
