<?php
require_once __DIR__ . '/../includes/conexion.php';

function descontar_stock(MongoDB\BSON\ObjectId $id, int $cantidad): bool {
    global $manager, $produtos;
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['_id' => $id, 'stock' => ['$gte' => $cantidad]],
        ['$inc' => ['stock' => -$cantidad]]
    );
    $res = $manager->executeBulkWrite($produtos, $bulk);
    return $res->getMatchedCount() === 1;
}

function devolver_stock(MongoDB\BSON\ObjectId $id, int $cantidad): bool {
    global $manager, $produtos;
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['_id' => $id],
        ['$inc' => ['stock' => $cantidad]]
    );
    $res = $manager->executeBulkWrite($produtos, $bulk);
    return $res->getMatchedCount() === 1;
}
?>