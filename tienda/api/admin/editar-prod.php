<?php

require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

requireAdmin();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    fail(405, 'Método no permitido');
}

$data = json_decode(file_get_contents('php://input'), true);
if(!is_array($data)){
    fail(400, 'JSON no válido');
}

try{
    $id = new MongoDB\BSON\ObjectId($data['id'] ?? '');
}catch (Throwable $e){
    fail(400, 'Id de producto no válido');
}

$erros = [];
$nombre = trim($data['nombre'] ?? '');
$descripcion = trim($data['descripcion'] ?? '');
$precio = $data['precio'] ?? null;


if($nombre === '' || mb_strlen($nombre) > 100){
    $erros[] = "O nome é obligatorio";
}

if(!is_numeric($precio) || $precio <= 0){
    $erros[] = 'O precio ten que ser maior de 0';
}

if($erros){
    http_response_code(422);
    echo json_encode(['erros' => $erros]);
    exit;
}

$bulk = new MongoDB\Driver\BulkWrite;
$bulk->update(['_id' => $id], ['$set' => [
    'nombre' => $nombre,
    'descripcion' => $descripcion,
    'precio' => (float) $precio,
    'modificado' => new MongoDB\BSON\UTCDateTime(),
]]);
$resultado = $manager->executeBulkWrite($produtos, $bulk);

if($resultado->getMatchedCount() === 0){
    fail(404, 'El producto no existe');
}
echo json_encode(['ok' => true]);
?>