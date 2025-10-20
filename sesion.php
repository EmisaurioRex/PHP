<?php  
session_start();  

$USUARIO = "Pepe"; 
$PASSWORD = "1233"; 
$COOKIE_NA = "recuerdame"; 
$Tiempo = 50; 

if (isset($_SESSION["usuario"])) {
    header('Location: inicio.php');
    exit;
}

if (isset($_COOKIE[$COOKIE_NA]) && !empty($_COOKIE[$COOKIE_NA])) {
    $_SESSION["usuario"] = $_COOKIE[$COOKIE_NA];
    header('Location: inicio.php');
    exit;
}

if (!empty($_POST)) { 
    $usuario = $_POST['usuario'] ?? '';
    $contrasenia = $_POST['contrasenia'] ?? '';
    $recordar = isset($_POST['recordar']);

    if ($usuario === $USUARIO && $contrasenia === $PASSWORD) {
        $_SESSION['usuario'] = $usuario;

        if ($recordar) {
            setcookie($COOKIE_NA, $usuario, time() + $Tiempo, "/");
        } else {
            setcookie($COOKIE_NA, '', time() - 3600, "/");
        }
        header("Location: inicio.php");
        exit;
    } else {
        $_SESSION['login_error'] = "Usuario o contraseña incorrectos :(";
        header("Location: error.php");
        exit;
    
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Inicio de sesión</title>
    <style>
        body {
            background-color: #94cdcfff;
            padding: 20px;
        }
        button {
            margin-top: 10px;
            padding: 8px 16px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #8febb2ff;
        }
        table {
            border-collapse: collapse;
        }
    </style>
</head>
<body>
    <form method="POST" action="">
        <table>
            <tr>
                <td>Usuario</td>
                <td><input type="text" name="usuario"></td>
            </tr>
            <tr>
                <td>Contraseña</td>
                <td><input type="password" name="contrasenia"></td>
            </tr>
            <tr>
                <td><input type="checkbox" name="recordar"></td>
                <td>Recuérdame</td>
            </tr>
        </table>
        <div style="text-align:center;">
            <button type="submit">INGRESAR</button>
        </div>

    </form>
</body>
</html>
