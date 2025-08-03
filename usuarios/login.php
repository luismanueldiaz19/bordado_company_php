<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   include '../conexion.php';
   include '../utils.php';

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');

    $response = array();

    $data = json_decode(file_get_contents("php://input"), true);

    // Validar código y pin
    if (empty($data['code']) || empty($data['pin_code'])) {
        $response['success'] = false;
        $response['message'] = 'El código y la contraseña son requeridos';
        echo json_encode($response);
        exit;
    }

    $code = trim($data['code']);
    $pin_code = $data['pin_code'];

    // Consulta con permisos incluidos
    $query = "
        SELECT m.id, m.full_name, m.occupation, m.created, m.turn, m.code, m.type, m.statu,
               pm.modulo, pm.action, pm.id_permisos
        FROM public.users AS m
        LEFT JOIN public.permisos_usuarios p ON p.id_users = m.id
        LEFT JOIN public.permisos pm ON pm.id_permisos = p.id_permisos
        WHERE m.code = $1 AND m.pin_code = $2
    ";

    $result = pg_query_params($conn, $query, [$code, $pin_code]);

    if (!$result) {
        $response['success'] = false;
        $response['message'] = 'Error en la consulta: ' . pg_last_error($conn);
        echo json_encode($response);
        exit;
    }

    if (pg_num_rows($result) === 0) {
        $response['success'] = false;
        $response['message'] = 'Credenciales inválidas';
        echo json_encode($response);
        exit;
    }

    // Procesar usuario y permisos
    $usuario = null;
    $list_permission = array();

    while ($row = pg_fetch_assoc($result)) {
        if ($usuario === null) {
            $usuario = array(
                'id' => $row['id'],
                'full_name' => $row['full_name'],
                'occupation' => $row['occupation'],
                'created' => $row['created'],
                'turn' => $row['turn'],
                'code' => $row['code'],
                'type' => $row['type'],
                'statu' => $row['statu'],
            );
        }

        if (!empty($row['modulo'])) {
            $list_permission[] = array(
                'id_permisos' => $row['id_permisos'],
                'modulo' => $row['modulo'],
                'action' => $row['action'],
            );
        }
    }

    $usuario['list_permission'] = $list_permission;

    session_start(); // Si usas sesiones
    $response['success'] = true;
    $response['message'] = 'Inicio de sesión exitoso';
    $response['data'] = $usuario;
} else {
    $response['success'] = false;
    $response['message'] = 'Método no permitido. Solo POST.';
}

pg_close($conn);
echo json_encode($response);
