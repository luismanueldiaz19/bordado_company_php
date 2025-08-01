<?php
include '../conexion.php';
include '../utils.php';

$res = pg_query($conn, "SELECT id_usuario, nombre, username, rol_id, activo, creado_en FROM usuarios ORDER BY creado_en DESC");
$data = pg_fetch_all($res) ?? [];

json_response($data);
