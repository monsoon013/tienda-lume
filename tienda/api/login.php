<?php

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../includes/auth.php';

$data = json_decode(file_get_contents('php://input'), true);

$duendeError = [];

    if($_SERVER["REQUEST_METHOD"] !== "POST"){
        http_response_code(405);
        echo json_encode(['erros' => ['No se ha enviado el formulario']]);
        exit;
    }

    if(empty($data)){
        http_response_code(400);
        echo json_encode(['erros' => ['Datos incompletos']]);
        exit;
    }
    
    $user = trim($data['user'] ?? '');
    $psswd = (string) ($data['pass'] ?? '');

    if ($user === '')
        $duendeError[] = "Debe especificar el nombre de usuario";

    if ($psswd === '')
        $duendeError[] = "Introduce una contraseña";

    if(count($duendeError) > 0){
        http_response_code(422);
        echo json_encode(['erros' => $duendeError]);
        exit;
    }

    try {
        $uri = getenv("MONGODB_URI");

        if(!$uri){
            throw new RuntimeException(
                "neno, non configuraches a url"
            );
        }

        $manager = new MongoDB\Driver\Manager($uri);

        $query = new MongoDB\Driver\Query(
            ['username' => $user], 
            ['limit' => 1]);

        $resultado = $manager->executeQuery("lume_db.users", $query)->toArray();
        $usuario = $resultado[0] ?? null; //si no existe
        $hash = $usuario->passwordHash ?? password_hash('relleno', PASSWORD_BCRYPT);
        if ($usuario === null || !password_verify($psswd, $hash)){
            http_response_code(401);
            echo json_encode(['erros' => ['Usuario o contraseña incorrectos']]);
            exit;
        }

        create_session((string) $usuario->_id, $usuario->username, $usuario->rol ?? 'Cliente');

        http_response_code(200);
        echo json_encode([
            'ok' => true,
            'username' => $usuario->username,
            'rol' => $usuario->rol ?? 'Cliente'
        ]);

        exit;
    }catch(Throwable $duendeDB){
        http_response_code(500);
        error_log("Error na base de datos mongo: " . $duendeDB->getMessage());
        
    }
?>