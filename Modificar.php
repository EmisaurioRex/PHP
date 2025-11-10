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

function ejecutar($query, $server, $base, $usr, $pass) {
    $cnx = mysqli_connect($server, $usr, $pass, $base);
    if (mysqli_connect_errno()) {
        error_log("Error de conexión: " . mysqli_connect_error());
        return false;
    }
    $res = mysqli_query($cnx, $query);
    mysqli_close($cnx);
    return $res;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'modificar') {

    $cnx_saneamiento = mysqli_connect($server, $usr, $pass, $base);
    $nombre = isset($_POST['nombre']) ? mysqli_real_escape_string($cnx_saneamiento, $_POST['nombre']) : '';
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
    $id = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
    mysqli_close($cnx_saneamiento);

    if ($id <= 0 || empty($nombre) || $precio <= 0) {
        $msg = urlencode("Error: Los datos para modificar el producto no son válidos.");
        header("Location: resultado.php?tipo=error&msg=$msg");
        exit();
    }

    $query = "UPDATE productos SET nombre='$nombre', precio=$precio WHERE id_producto=$id";

    if (ejecutar($query, $server, $base, $usr, $pass)) {
        $msg = urlencode("La modificación del producto fue exitosa.");
        header("Location: resultado.php?tipo=exito&msg=$msg");
    } else {
        $msg = urlencode("Hubo un error al intentar modificar el producto ID $id. Contacta a soporte.");
        header("Location: resultado.php?tipo=error&msg=$msg");
    }
} else {
    header("Location: $nombre_archivo_principal");
}
exit();
?>