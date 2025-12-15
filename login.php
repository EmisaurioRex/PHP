<?php 
session_start(); 

$USUARIO = "Sasa"; 
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
            background-color: #f0f2f5; 
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .login-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 360px; 
            text-align: center;
        }

        .logo-section {
            margin-bottom: 30px;
        }

        .logo-icon {
            display: inline-block; 
            background-color: #3b5998; 
            color: white;
            padding: 8px;
            border-radius: 50%;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .logo-icon::before {
             content: "🐾"; 
        }

        h1 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #333;
        }

        p.subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group input[type="text"],
        .input-group input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; 
            font-size: 16px;
        }

        .checkbox-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .checkbox-row label {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: #555;
        }

        .checkbox-row input[type="checkbox"] {
            margin-right: 8px;
        }

        .forgot-password a {
            color: #007bff;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            padding: 12px;
            background-color: #3b5998; 
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .login-button:hover {
            background-color: #2d4373;
        }

        .help-link {
            display: block;
            margin-top: 25px;
            font-size: 14px;
            color: #666;
            text-decoration: none;
        }

        .help-link a {
            color: #007bff;
            text-decoration: none;
        }
        
        .help-link a:hover {
            text-decoration: underline;
        }

        .demo-text {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <div class="logo-icon"></div>
            <h1>VetAdmin</h1>
            <p class="subtitle">Sistema de Administración Veterinaria</p>
        </div>

        <form method="POST" action="">
            <div class="input-group">
                <input type="text" name="usuario" placeholder="nombre_usuario" required>
            </div>
            
            <div class="input-group">
                <input type="password" name="contrasenia" placeholder="Contraseña" required>
            </div>

            <div class="checkbox-row">
                <label>
                    <input type="checkbox" name="recordar">
                    Recordarme
                </label>
            </div>

            <button type="submit" class="login-button">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>