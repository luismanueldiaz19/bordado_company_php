<?php
include '../conexion.php';
include '../utils.php';

header('Content-Type: application/json');

// Leer datos JSON desde el cuerpo de la petición
$data = json_decode(file_get_contents('php://input'), true);

// Validar existencia de campos obligatorios
$requiredFields = [
    'id_cliente', 'estado_prioritario', 'estado_general', 'name_logo', 'ficha',
    'observaciones', 'fecha_creacion', 'fecha_entrega', 'estado_entrega',
    'usuario_id', 'items'
];

$missingFields = [];
foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        $missingFields[] = $field;
    }
}

// Validar que "items" sea un arreglo no vacío
if (isset($data['items']) && (!is_array($data['items']) || count($data['items']) == 0)) {
    $missingFields[] = 'items (vacío o no es arreglo)';
}

if (!empty($missingFields)) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos obligatorios o mal estructurados: ' . implode(', ', $missingFields)
    ]);
    exit;
}

// 1. Insertar en list_ordenes
$sql = "INSERT INTO public.list_ordenes (
    id_cliente, estado_prioritario, estado_general, name_logo,
    ficha, observaciones, fecha_creacion, fecha_entrega, estado_entrega, usuario_id
) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10) RETURNING list_ordenes_id";

$params = [
    $data['id_cliente'],
    $data['estado_prioritario'],
    $data['estado_general'],
    $data['name_logo'],
    $data['ficha'],
    $data['observaciones'],
    $data['fecha_creacion'],
    $data['fecha_entrega'],
    $data['estado_entrega'],
    $data['usuario_id']
];

$result = pg_query_params($conn, $sql, $params);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Error al crear la orden.']);
    exit;
}

$row = pg_fetch_assoc($result);
$list_ordenes_id = $row['list_ordenes_id'];

$insertedItems = [];

foreach ($data['items'] as $item) {
    // Validar datos mínimos del item
    if (!isset($item['id_producto'], $item['cant'], $item['detalles_productos'], $item['nota'], $item['estado_produccion'])) {
        continue;
    }

    // 2. Insertar en orden_items
    $sqlItem = "INSERT INTO public.orden_items (
        list_ordenes_id, id_producto, cant, detalles_productos, nota, estado_produccion
    ) VALUES ($1, $2, $3, $4, $5, $6) RETURNING orden_items_id";

    $paramsItem = [
        $list_ordenes_id,
        $item['id_producto'],
        $item['cant'],
        $item['detalles_productos'],
        $item['nota'],
        $item['estado_produccion']
    ];

    $resItem = pg_query_params($conn, $sqlItem, $paramsItem);

    if (!$resItem) continue;

    $rowItem = pg_fetch_assoc($resItem);
    $orden_items_id = $rowItem['orden_items_id'];
    $insertedItems[] = $orden_items_id;

    // 3. Insertar en planificacion_work si hay departamentos
    if (isset($item['departamentos']) && is_array($item['departamentos'])) {
        foreach ($item['departamentos'] as $id_depart) {
            $sqlDept = "INSERT INTO public.planificacion_work (orden_items_id, id_depart) VALUES ($1, $2)";
            $paramsDept = [$orden_items_id, $id_depart];
            pg_query_params($conn, $sqlDept, $paramsDept);
        }
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Orden creada con éxito',
    'list_ordenes_id' => $list_ordenes_id,
    'orden_items_ids' => $insertedItems
]);
