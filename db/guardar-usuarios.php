<?php
require __DIR__ . '/conexion.php';
//lectura del JSON enviado por js
$data = json_decode(file_get_contents('php://input'), true);

$nomeComp = trim($data['nome']) . trim($data['apelidos']);
$usuario = (trim($data['usuario']);
$correo = trim($data['correo']);
$rol = trim($data['rol']);
$fecha = date('YYYY-mm-dd H:i:s');

//validación del usuario
if($nome === '' || ){
    http_response_code(400);
    exit;
}
//prueba a guardar
try{
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk->insert([
        'id' => uniqid(),
        'nomeCompleto' => $nomeComp,
        'usuario' => $usuario,
        'correo' => $correo,
        'rol' => $rol,
        'fecha' => $fecha
    ]);

    $resultado = $manager->executeBulkWrite($usuarios, $bulk);

    if ($resultado->getInsertedCount() !== 1) {
        throw new RuntimeException("mira eu non sei que fixeches, pero aquí non hai nada");
    }
}catch(Throwable $duende){
    http_response_code(500);
    error_log('Tremendo erro, a ver que é : ' . $duende->getMessage());
}
?>