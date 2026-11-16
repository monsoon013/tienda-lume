<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/stock.php';

header('Content-Type: application/json; charset=utf-8');

$usuario = requireAuth();

function compensar(array $hechas):void{
    foreach($hechas as $h){
        $ok = devolver_stock($h['id'], $h['cantidad']);
        if(!$ok){
            error_log('No se pudo devolver el stock del producto ' . $h['id']);
        }
    }
}

function leer_producto(MongoDB\BSON\ObjectId $id){
    global $manager, $produtos;

    try{
        $query = new MongoDB\Driver\Query(
            ['_id' => $id],
            ['projection' => ['nombre' => 1, 'precio' => 1], 'limit' => 1] 
        );
        $res = $manager->executeQuery($produtos, $query)->toArray();
        return $res[0] ?? null;
    }catch (Throwable $e){
        error_log($e->getMessage());
        return null;
    }
}



if($_SERVER["REQUEST_METHOD"] !== "POST"){
    fail(405, 'Método no permitido');
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if(!is_array($data)){
    fail(400, 'JSON no válido');
}

$submitted = $data['lineas'] ?? null;
if(!is_array($submitted) || count($submitted) === 0 || count($submitted) > 50){
    fail(422, 'Cantidad en el carrito inválida');
}

$pedido = [];
foreach($submitted as $linea){
    if(!is_array($linea)){
        fail(422, 'Línea no válida');
    }
    try{
        $id = new MongoDB\BSON\ObjectId($linea['id'] ?? '');

    }catch (Throwable $e){
        fail(422, 'Id del producto no válido');
    }

    $cant = $linea['cantidad'] ?? null;
    if(!is_int($cant) || $cant < 1 || $cant > 99){
        fail(422, 'La cantidad tiene que ser un entero');
    }

    $clave = (string) $id;
    if(isset($pedido[$clave])){
        $pedido[$clave]['cantidad'] += $cant;
    }else{
        $pedido[$clave] = ['id' => $id, 'cantidad' => $cant];
    }

    if($pedido[$clave]['cantidad'] > 99){
        fail(422, 'Máximo 99 unidades por producto');
    }
}

$done = [];
$total = 0.0;

foreach($pedido as $item){
    if(!descontar_stock($item['id'], $item['cantidad'])){
        $p = leer_producto($item['id']);
        $nome = $p->nome ?? 'un producto';
        compensar($hechas);
        fail(409, 'Stock insuficiente: ' . $nome);
    }

    $hechas[] = ['id' => $item['id'], 'cantidad' => $item['cantidad'], 'nombre' => '', 'precio' => 0];
    $ultimo = array_key_last($hechas);              

    $producto = leer_producto($item['id']);
    $precio   = $producto->precio ?? null;
    if ($producto === null || !is_numeric($precio)) {
        compensar($hechas);                          
        fail(500, 'Error al procesar el producto');
    }

    $hechas[$ultimo]['nombre'] = (string) $producto->nombre;
    $hechas[$ultimo]['precio'] = (float) $precio;

    $total += (float) $precio * $item['cantidad'];
}

$total = round($total, 2);                         


$lineasDoc = [];
foreach ($hechas as $h) {
    $lineasDoc[] = [
        'producto' => $h['id'],                    
        'nombre'   => $h['nombre'],
        'precio'   => $h['precio'],
        'cantidad' => $h['cantidad'],
    ];
}

try {
    global $manager, $compras;
    $bulk = new MongoDB\Driver\BulkWrite();
    $idCompra = $bulk->insert([                      
        'usuario' => $usuario->_id,                  
        'estado'  => 'pendiente',
        'lineas'  => $lineasDoc,
        'total'   => $total,
        'creado'  => new MongoDB\BSON\UTCDateTime(), 
    ]);
    $manager->executeBulkWrite($compras, $bulk);     
} catch (Throwable $e) {
    error_log($e->getMessage());                     
    compensar($hechas);                              
    fail(500, 'No se pudo registrar la compra');    
}


http_response_code(201);                             
echo json_encode([
    'id'    => (string) $idCompra,                  
    'total' => $total,
]);



?>
