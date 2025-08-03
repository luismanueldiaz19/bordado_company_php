<?php
include '../conexion.php';
include '../utils.php';

try {
    // Verifica si la solicitud es POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Método no permitido
        throw new Exception('Método no permitido. Solo se acepta POST.');
    }

    // Verifica conexión
    if (!$conn) {
        throw new Exception('No se pudo establecer la conexión a la base de datos.');
    }

    $query = "
        SELECT  
            m.planificacion_work_id,
            departments.name_department,
            l.ficha,
            l.num_orden,
            l.name_logo,
            l.fecha_entrega,
            l.estado_prioritario,
            l.id_cliente,
            cliente.nombre,
            p.name_producto,
            i.cant,
            i.detalles_productos,
            m.orden_items_id,
            i.nota,
            i.estado_produccion,
            i.fecha_item_creacion, 
            m.id_depart, 
            m.estado_planificacion_work
        FROM public.planificacion_work AS m
        JOIN public.orden_items i ON i.orden_items_id = m.orden_items_id
        JOIN public.list_producto p ON p.id_producto = i.id_producto
        JOIN public.departments ON departments.id = m.id_depart
        JOIN public.list_ordenes l ON l.list_ordenes_id = i.list_ordenes_id
        JOIN public.cliente ON cliente.id_cliente = l.id_cliente
        ORDER BY l.fecha_entrega ASC
    ";

    $res = pg_query($conn, $query);

    if (!$res) {
        throw new Exception('Error al ejecutar la consulta: ' . pg_last_error($conn));
    }

    $data = pg_fetch_all($res) ?? [];

    json_response([
        'success' => true,
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(http_response_code() === 200 ? 500 : http_response_code());
    json_response([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
