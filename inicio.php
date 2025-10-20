<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    if (isset($_COOKIE['recuerdame']) && !empty($_COOKIE['recuerdame'])) {
        $_SESSION['usuario'] = $_COOKIE['recuerdame'];
    } else {
        header('Location: sesion.php'); 
        exit;
    }
}

if (!empty($_POST) && isset($_POST['cerrar'])) {
    session_unset();
    session_destroy();
    setcookie("recuerdame", '', time() - 3600, "/");
    header('Location: sesion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Inicio</title>
    <style>
        body {
            background-color: #8ebd7d;
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 100px;
        }
        h1 {
            color: #020202ff;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #3e7b31;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #8febb2ff;
            color: black;
        }
    </style>
</head>
<body>
    <h1>Bienvenido </h1>
    <form method="POST" action="">
        <button type="submit" name="cerrar">Cerrar sesión</button>
    </form>
</body>
</html>
