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

$ajuste = $data['ajuste'] ?? null;
if(!is_int($ajuste) || $ajuste === 0 || abs($ajuste) > 100000){
    fail(422, 'El ajuste debe de ser un entero distinto de 0');
}

$filtro = ['_id' => $id];

if($ajuste <0){
    $filtro['stock'] = ['$gte' => -$ajuste]; //filtra que haya esa cantidad
}

$bulk = new MongoDB\Driver\BulkWrite;
$bulk->update($filtro, [
    '$inc' =>  ['stock' => $ajuste], //suma el ajuste (si es negativo, resta)
    '$set' => ['modificado' => new MongoDB\BSON\UTCDateTime()]
]);
$resultado = $manager->executeBulkWrite($produtos, $bulk);

if ($resultado->getMatchedCount() === 0) {
    $query  = new MongoDB\Driver\Query(['_id' => $id], ['limit' => 1, 'projection' => ['_id' => 1]]); //compruebo si el producto no tiene stock suficiente
    $existe = current($manager->executeQuery($produtos, $query)->toArray());

    if (!$existe) { //Si no existe
        fail(404, 'El producto no existe');
    } //Si existe, pero no tiene stock suficiente
    fail(409, 'Stock insuficiente para restar esa cantidad');
}

echo json_encode(['ok' => true]);
?>