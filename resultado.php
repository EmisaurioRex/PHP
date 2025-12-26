<?php

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'error';
$mensaje = isset($_GET['msg']) ? $_GET['msg'] : 'No se recibió ningún mensaje de estado.';


if ($tipo === 'exito') {
    $titulo = "¡Operación Exitosa!";
    $color = "green"; 
} else {
    $titulo = "¡Ha Ocurrido un Error!";
    $color = "red"; 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado de la Operación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 50px;
            text-align: center;
        }
        .contenedor-mensaje {
            border: 2px solid <?php echo $color; ?>;
            padding: 20px;
            max-width: 400px;
            margin: 0 auto;
            border-radius: 5px;
        }
        h1 {
            color: <?php echo $color; ?>;
        }
        .mensaje-texto {
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        .volver-btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <div class="contenedor-mensaje">
        <h1><?php echo $titulo; ?></h1>
        
        <p class="mensaje-texto">
            <?php echo $mensaje; ?>
        </p>
        
        <a href="Pruebota.php" class="volver-btn">
            Volver al Listado
        </a>
    </div>

</body>

</html>
