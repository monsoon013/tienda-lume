<?php
require_once __DIR__ . '/conexion.php';

spl_autoload_register(function (string $clase){
    $prefijo = 'Firebase\\JWT\\';
    if (strncmp($clase, $prefijo, strlen($prefijo)) !== 0){
        return;
    }
    $nombre = substr($clase, strlen($prefijo)) . '.php';
    foreach ([__DIR__ . '/../lib/php-jwt/src/', __DIR__ . '/../lib/php-jwt/'] as $carpeta) {
        if (is_file($carpeta . $nombre)) {
            require $carpeta . $nombre;
            return;
        }
    }
});


use Firebase\JWT\JWT;
use Firebase\JWT\Key;

const TOKEN_TTL = 7200; //duración de 2 horas

function jwt_secret(): string {
    $secret = getenv('JWT_SECRET');
    if(!$secret || strlen($secret) < 32){
        throw new RuntimeException('JWT_SECRET es demasiado corto, mínimo 32 caracteres');
    }
    return $secret;
}

function cookie_options(int $expira):array{
    return[
        'expires' => $expira,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Strict',
    ];
}

function create_session(string $id, string $username, string $rol) :void{
    $ahora = time();
    $jwt = JWT::encode([
        'iat' => $ahora,
        'exp'  => $ahora + TOKEN_TTL,
        'sub' => $id,
        'username' => $username,
        'rol' => $rol,
    ], jwt_secret(), 'HS256');
    setcookie('token', $jwt, cookie_options($ahora + TOKEN_TTL));
}

function actual_user(): ?object {
    global $manager, $usuarios;
    $token = $_COOKIE['token'] ?? null;
    if(!$token){
        return null;
    }
    try{
        $payload = JWT::decode($token, new Key(jwt_secret(), 'HS256'));
        $id = new MongoDB\BSON\ObjectId((string) ($payload->sub ?? ''));
        $query = new MongoDB\Driver\Query(['_id' => $id], ['limit' => 1]);
        $res = $manager->executeQuery($usuarios, $query)->toArray();

        return $res[0] ?? null;
    
    }catch(Throwable $e){
        error_log('usuario_actual: ' . get_class($e) . ' - ' . $e->getMessage());
        return null; //quiere decir que el token ha sido manipulado, caducado, ..
    }
}

function close_sesion():void{
    setcookie('token', '', cookie_options(time() - 3600));
}

function ob_rol(string $rol): object {
    $u = actual_user();
    if(!$u){
        http_response_code(401);
        echo json_encode(['erros' => ['Tienes que iniciar sesión']]);
        exit;
    }

    if($u->rol !== $rol){
        http_response_code(403);
        echo json_encode(['erros' => ['No tienes permisos']]);
        exit;
    }
    return $u;
}

function fail(int $codigo, string $mensaje): never { //la función nunca devuelve el control, siempre termina con exit.
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erros' => [$mensaje]]);
    exit;
}

function requireAuth():object{ //devuelve la función
    $u = actual_user();
    if($u === null){
        fail(401, 'No autenticado');
    }
    return $u;
}

function requireAdmin():object {
    $usuario = requireAuth();
    if(($usuario->rol ?? null) !== 'admin'){
        fail(403, 'No tienes permisos');
    }
    return $usuario;
}
?>