<?php

require __DIR__ . '/includes/auth.php';

$u = actual_user();

if(!$u){
    header('Location:index.php');
    exit;
}

$esAdmin = ($u->rol === 'Admin');

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sesión</title>
</head>
<body>
 
<?php if ($esAdmin): ?>
    <h1>Hola admin</h1>
<?php else: ?>
    <h1>Hola cliente</h1>
<?php endif; ?>
   
    
 
<button id="logoutBtn">Salir</button>
 
<script>
document.getElementById('logoutBtn').addEventListener('click', async () => {
    await fetch('api/logout.php', { method: 'POST' });
    location.href = 'index.php'; // ajusta al nombre de tu página de login
});
</script>
 
</body>
</html>