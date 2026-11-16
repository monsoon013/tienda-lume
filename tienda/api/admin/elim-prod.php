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


$bulk = new MongoDB\Driver\BulkWrite;
$bulk->delete(['_id' => $id], ['limit' => 1]);
$resultado = $manager->executeBulkWrite($produtos, $bulk);

if($resultado->getDeletedCount() === 0){
    fail(404, 'El producto no existe');
}
echo json_encode(['ok' => true]);
?>