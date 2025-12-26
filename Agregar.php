<?php

define('DB_SERVER', '127.0.0.1:3306'); 
define('DB_BASE', 'tiendita'); 
define('DB_USR', 'root');
define('DB_PASS', '1234');

$server = DB_SERVER;
$base = DB_BASE;
$usr = DB_USR;
$pass = DB_PASS;
$nombre_archivo_principal = "Pruebota.php";

function insertar($query, $server, $base, $usr, $pass) {
    $cnx = mysqli_connect($server, $usr, $pass, $base);
    if (mysqli_connect_errno()) {
        error_log("Error de conexión: " . mysqli_connect_error());
        return false;
    }
    $res = mysqli_query($cnx, $query);
    $id = mysqli_insert_id($cnx); 
    mysqli_close($cnx);
    return $res ? $id : false;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {

    $cnx_saneamiento = mysqli_connect($server, $usr, $pass, $base);
    $nombre = isset($_POST['nombre']) ? mysqli_real_escape_string($cnx_saneamiento, $_POST['nombre']) : '';
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
    mysqli_close($cnx_saneamiento);

    if (empty($nombre) || $precio <= 0) {
        $msg = urlencode("Error: Los datos del producto no son válidos. Vuelve a intentarlo.");
        header("Location: resultado.php?tipo=error&msg=$msg");
        exit();
    }

    $query = "INSERT INTO productos (nombre, precio) VALUES ('$nombre', $precio)";

    if (insertar($query, $server, $base, $usr, $pass)) {
        $msg = urlencode("El producto fue agregado correctamente.");
        header("Location: resultado.php?tipo=exito&msg=$msg");
    } else {
        $msg = urlencode("Hubo un error al intentar agregar el producto. Contacta a soporte.");
        header("Location: resultado.php?tipo=error&msg=$msg");
    }
} else {
    header("Location: $nombre_archivo_principal");
}
exit();
?>