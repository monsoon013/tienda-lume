<?php

header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);

$duendeError = [];
$patronTexto = '/^[\p{L}\s]+$/u'; //letras con acentos y espacios

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
    
    $nome = trim($data['nome'] ?? '');
    $apelidos = trim($data['apelidos'] ?? '');
    $user = trim($data['user'] ?? '');
    $email = strtolower(trim($data['email'] ?? ''));
    $psswd = (string) ($data['psswd'] ?? '');

    if ($nome === '')
        $duendeError[] = "Debe especificar el nombre";
    else if (!preg_match($patronTexto, $nome)){
        $duendeError[] = "El nombre sólo puede contener letras y espacios";
    }

    if ($apelidos === '')
        $duendeError[] = "Debe especificar los apellidos";
    else if (!preg_match($patronTexto, $apelidos)){
        $duendeError[] = "Dudo que tu apellido tenga números";
    }

    if ($user === '')
        $duendeError[] = "Debe especificar el nombre de usuario";

    if ($email === '')
        $duendeError[] = "Debe especificar el email";
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL )){
        $duendeError[] = "Uh-oh eso no es un correo electrónico :(";
    }

    if ($psswd === '')
        $duendeError[] = "Introduce una contraseña";
    else if (strlen($psswd) < 8){
        $duendeError[] = "La contraseña debe tener al menos 8 caracteres";
    }

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

        $query = new MongoDB\Driver\Query([
            '$or' => [
                ['email' => $email],
                ['username' => $user]]
        ], ['limit' => 1]);

        if ($manager->executeQuery("lume_db.users", $query)->toArray()){
            http_response_code(409);
            echo json_encode(['erros' => ['El email o el usuario ya está registrado']]);
            exit;
        }

        $data = [
            "nome" => $nome,
            "apelidos" => $apelidos,
            "username" => $user,
            "email" => $email,
            "passwordHash" => password_hash($psswd, PASSWORD_BCRYPT),
            "rol" => "Cliente",
            "fecha" => new MongoDB\BSON\UTCDateTime()
        ];

        $bulk = new MongoDB\Driver\BulkWrite(); 
        $bulk->insert($data);

        $resultado = $manager->executeBulkWrite(
            "lume_db.users",
            $bulk
        );

        if($resultado->getInsertedCount() !== 1){
            throw new RuntimeException(
                "mira, eu non sei que fixeches, pero non se confirmou a inserción"
            );
        
        }


        http_response_code(201);
        echo json_encode(['ok' => true]);
        echo "<p>O formulario mandouse corretamente</p>";
    }catch(Throwable $duendeDB){
        http_response_code(500);
        error_log("Error na base de datos mongo: " . $duendeDB->getMessage());
        echo "Eu non vexo os dato ho";
        echo "Revisa os rexistros do contedor";
        
    }
?>