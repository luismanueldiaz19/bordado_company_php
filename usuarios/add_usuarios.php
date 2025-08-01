<?php
include '../conexion.php';
include '../utils.php';

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$nombre   = $data['nombre'] ?? '';
$rol_id   = $data['rol_id'] ?? null;

if (!$username || !$password || !$nombre) {
  json_response(["error" => "Faltan campos obligatorios"], 400);
}

// Verifica si ya existe
$res = pg_query_params($conn, "SELECT * FROM usuarios WHERE username = $1", [$username]);
if (pg_num_rows($res) > 0) {
  json_response(["error" => "El usuario ya existe"], 409);
}

$passwordHash = password_hash($password, PASSWORD_BCRYPT);

$sql = "INSERT INTO usuarios (nombre, username, password_hash, rol_id)
        VALUES ($1, $2, $3, $4)";

pg_query_params($conn, $sql, [$nombre, $username, $passwordHash, $rol_id]);

json_response(["ok" => true]);
