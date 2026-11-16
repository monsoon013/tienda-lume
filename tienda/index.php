<!DOCTYPE html>
<html lang="gal">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUME - tenda de chaquetas personalizadas</title>
    <link rel="stylesheet" href="css/style.css">

</head>
<body>
    <div class="forms">
        <form id="rexistro">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome">
            <label for="apelidos">Apelidos</label>
            <input type="text" id="apelidos" name="apelidos">
            <label for="user">Usuario</label>
            <input type="text" id="userNameSign" name="user" maxlength="15">
            <label for="contrasinal">Contrasinal</label>
            <input type="password" id="psswd" name="contrasinal">
            <label for="email">Correo eletrónico</label>
            <input type="email" id="email" name="email">
            <label for="numero-mobil">Número de móbil</label>
            <input type="tel" id="numMobil" name="numero-mobil">
            
            <p id="signupErrores"></p>

            <button type="submit" id="rexistrarse">Rexistrarse</button>
        </form>

        <form id="login">
            <label for="userNameLog">Usuario</label>
            <input type="text" id="userNameLog" name="userNameLog" maxlength="15">
            <label for="contrasinalLog">Contrasinal</label>
            <input type="password" id="psswdLog" name="contrasinalLog">        
            <p id="loginErrores"></p>

            <button type="submit" id="aceder">Aceder</button>
        </form>
    
    </div>

    <script src='js/signup.js' defer></script>
    <script src='js/login.js' defer></script>
    
</body>
</html>