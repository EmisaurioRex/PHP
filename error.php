<?php
session_start();

if (!isset($_SESSION['login_error'])) {
    header('Location: sesion.php');
    exit;
}

$error_mensaje = $_SESSION['login_error'];
unset($_SESSION['login_error']); 

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Error de Ingreso</title>
    <style>
        body {
            background-color: #fce4e4;
            text-align: center;
            padding: 50px;
        }
        .error {
            color: red;
            font-size: 1.2em;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #ff5757;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>
    <h1>¡Error de Ingreso!</h1>
    <p class="error"> <?php echo ($error_mensaje); ?> </p>
    <a href="sesion.php">Regresar e intentar de nuevo</a>
</body>
</html>