<?php

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/auth.php';

$duendeError = [];

if($_SERVER['REQUEST_METHOD'] !== 'GET'){
    http_response_code(405);
    echo json_encode(['erros' => ['Método non permitido']]);
    exit;
}

try{
    $query = new MongoDB\Driver\Query([], ['sort' => ['nombre' => 1]]);
    $data = $manager->executeQuery($produtos, $query)->toArray();

    $prods = [];
    foreach($data as $p){
        $prods[] = [
            'id'          => (string) $p->_id,
            'nombre'      => $p->nombre ?? '',
            'descripcion' => $p->descripcion ?? '',
            'precio'      => $p->precio ?? 0,
            'stock'       => $p->stock ?? 0,
            'imagen'      => $p->imagen ?? null,
        ];
    }

    $u = actual_user();
    $esAdmin = $u !== null && ($u->rol ?? '') === 'admin';

    echo json_encode([
        'produtos' => $prods,
        'admin' => $esAdmin,
    ], JSON_UNESCAPED_UNICODE);

}catch(Throwable $e){
    error_log('Error listando productos: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['erros' => ['Error interno, revisa los logs del contenedor']]);
}

?>