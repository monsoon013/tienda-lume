<?php

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/conexion.php';

header('Content-Type: application/json; charset=utf-8');

requireAdmin();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    fail(405, 'Método no permitido');
}

$data = json_decode(file_get_contents('php://input'), true);
if(!is_array($data)){
    fail(400, 'JSON no válido');
}

$erros = [];
$nombre = trim($data['nombre'] ?? '');
$precio = $data['precio'] ?? null;
$stock = $data['stock'] ?? null;

if($nombre === '' || mb_strlen($nombre) > 100){
    $erros[] = "O nome é obligatorio";
}

if(!is_numeric($precio) || $precio <= 0){
    $erros[] = 'O precio ten que ser maior de 0';
}

if(!is_int($stock) || $stock < 0){
    $erros[] = 'O stock debe ser un entero igual o maior que 0';
}

if($erros){
    http_response_code(422);
    echo json_encode(['erros' => $erros]);
    exit;
}

$descripcion = is_string($data['descripcion'] ?? null) ? trim($data['descripcion']) : '';

try{
    $bulk = new MongoDB\Driver\BulkWrite;
    $resultado = $bulk->insert([
        'nombre' => $nombre,
        'descripcion' => trim($data['descripcion'] ?? ''),
        'precio' => (float) $precio,
        'stock' => $stock,
        'creado' => new MongoDB\BSON\UTCDateTime(),
    ]);
    $manager->executeBulkWrite($produtos, $bulk);

}catch(Throwable $e){
    error_log('crear productos: ' . $e->getMessage());
    fail(500, 'No se creó el producto');
}

http_response_code(201);
echo json_encode([
    'ok' => true, 
    'id' => (string) $idNuevo
]);
?>