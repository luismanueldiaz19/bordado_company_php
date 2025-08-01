<?php
header('Content-Type: application/json');

$host = 'localhost';
$port = '5434';
$db   = 'bordado_company';
$user = 'postgres';
$pass = '123456';
// $options = "--client_encoding=UTF8";
$options = "-c client_encoding=UTF8"; // ✅ Esta es la forma correcta




try {
    // $dsn = "pgsql:host=$host;port=$port;dbname=$db;options='--client_encoding=$options'";
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;options='$options'";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo json_encode(['success' => true, 'message' => 'Conexión exitosa']);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión',
        'message' => $e->getMessage()
    ]);
    http_response_code(500); // Opcional: indica error del servidor
}
?>
