<?php
require __DIR__ . '/conexion.php';
//lectura del JSON enviado por js
$data = json_decode(file_get_contents('php://input'), true);

$nome = trim($data['nome']);
$stock = (int) ($data['stock'] ?? 0);
$desc = trim($data['descricion']);
$img = trim($data['imaxe']);
$fecha = date('YYYY-mm-dd H:i:s');
//validación del usuario
if($nome === '' || mb_strlen($nome) > 15){
    http_response_code(400);
    exit;
}
//prueba a guardar
try{
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk->insert([
        'id' => uniqid(),
        'nome' => $nome,
        'stock' => $stock,
        'descricion' => $desc,
        'img' => $img,
        'fecha' => $fecha
    ]);

    $resultado = $manager->executeBulkWrite($produtos, $bulk);

    if ($resultado->getInsertedCount() !== 1) {
        throw new RuntimeException("mira eu non sei que fixeches, pero aquí non hai nada");
    }
}catch(Throwable $duende){
    http_response_code(500);
    error_log('Tremendo erro, a ver que é : ' . $duende->getMessage());
}
?>