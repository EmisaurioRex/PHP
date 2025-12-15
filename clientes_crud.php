<?php

session_start();
require_once 'conexion.php'; 

$login_page = 'login.php'; 
$nombre_archivo_principal = 'clientes.php'; 
$nombre_archivo_actual = 'clientes_crud.php'; 



if (!isset($_SESSION['usuario'])) {
    if (isset($_COOKIE['recuerdame']) && !empty($_COOKIE['recuerdame'])) {
        $_SESSION['usuario'] = $_COOKIE['recuerdame'];
    } else {
        header('Location: ' . $login_page); 
        exit;
    }
}

$usuario_actual = htmlspecialchars($_SESSION['usuario'] ?? 'Admin'); 
$rol_actual = 'Administrador'; 
$pagina_activa = 'clientes'; 

$accion = isset($_GET['accion']) ? $_GET['accion'] : ''; 

$server = DB_SERVER;
$base = DB_BASE;
$usr = DB_USR;
$pass = DB_PASS;

$msg_tipo = '';
$msg_texto = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_post'])) {
    
    $accion_post = $_POST['accion_post'];
    $id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
    
    $cnx_saneamiento = mysqli_connect($server, $usr, $pass, $base);
    $res = false;
    
    if ($accion_post == 'insertar' || $accion_post == 'actualizar') {
        $nombre = isset($_POST['nombre']) ? mysqli_real_escape_string($cnx_saneamiento, $_POST['nombre']) : '';
        $apellido = isset($_POST['apellido']) ? mysqli_real_escape_string($cnx_saneamiento, $_POST['apellido']) : '';
        $telefono = isset($_POST['telefono']) ? mysqli_real_escape_string($cnx_saneamiento, $_POST['telefono']) : NULL;
        $email = isset($_POST['email']) ? mysqli_real_escape_string($cnx_saneamiento, $_POST['email']) : NULL;
        $direccion = isset($_POST['direccion']) ? mysqli_real_escape_string($cnx_saneamiento, $_POST['direccion']) : NULL;
        
        if (empty($nombre) || empty($apellido)) {
            $msg_tipo = 'error';
            $msg_texto = urlencode("Error: El nombre y apellido del cliente son obligatorios.");
        } else {
            if ($accion_post == 'insertar') {
                $query = "INSERT INTO clientes (nombre, apellido, telefono, email, direccion) VALUES ('$nombre', '$apellido', " . ($telefono ? "'$telefono'" : "NULL") . ", " . ($email ? "'$email'" : "NULL") . ", " . ($direccion ? "'$direccion'" : "NULL") . ")";
                $res = function_exists('insertar') ? insertar($query, $server, $base, $usr, $pass) : ejecutar($query, $server, $base, $usr, $pass);
                if ($res) {
                    $msg_texto = urlencode("El cliente fue agregado correctamente.");
                } else {
                    $msg_texto = urlencode("Error al intentar insertar el cliente.");
                }
            } else { 
                $query = "UPDATE clientes SET nombre = '$nombre', apellido = '$apellido', telefono = " . ($telefono ? "'$telefono'" : "NULL") . ", email = " . ($email ? "'$email'" : "NULL") . ", direccion = " . ($direccion ? "'$direccion'" : "NULL") . " WHERE id_cliente = $id_cliente";
                $res = ejecutar($query, $server, $base, $usr, $pass);
                if ($res) {
                    $msg_texto = urlencode("El cliente ID $id_cliente fue actualizado correctamente.");
                } else {
                    $msg_texto = urlencode("Error al intentar actualizar el cliente.");
                }
            }
            $msg_tipo = $res ? 'exito' : 'error';
        }
    }
    
    elseif ($accion_post == 'eliminar') {
        
        if ($id_cliente <= 0) {
            $msg_tipo = 'error';
            $msg_texto = urlencode("Error: ID de cliente no encontrado para la eliminación.");
        } else {
            $query = "DELETE FROM clientes WHERE id_cliente=$id_cliente";
            
            if (ejecutar($query, $server, $base, $usr, $pass)) {
                $msg_tipo = 'exito';
                $msg_texto = urlencode("El cliente ID $id_cliente ha sido eliminado correctamente.");
            } else {
                $msg_tipo = 'error';
                $msg_texto = urlencode("Hubo un error al intentar eliminar el cliente. Revise permisos de BD.");
            }
        }
    }
    
    mysqli_close($cnx_saneamiento);
    header("Location: $nombre_archivo_principal?tipo=$msg_tipo&msg=$msg_texto");
    exit();
}


$datos_cliente = [];

if ($accion == 'editar' && isset($_GET['id'])) {
    $id_editar = intval($_GET['id']);
    $query_select = "SELECT id_cliente, nombre, apellido, telefono, email, direccion FROM clientes WHERE id_cliente=$id_editar";
    $resultados = seleccionar($query_select);
    
    if (empty($resultados)) {
        header("Location: $nombre_archivo_principal?tipo=error&msg=" . urlencode("Cliente no encontrado para edición."));
        exit();
    }
    
    $datos_cliente = [
        'id_cliente' => $resultados[0][0],
        'nombre' => $resultados[0][1],
        'apellido' => $resultados[0][2],
        'telefono' => $resultados[0][3],
        'email' => $resultados[0][4],
        'direccion' => $resultados[0][5],
    ];
}

$titulo_pagina = ($accion == 'crear') ? 'Registrar Nuevo Cliente' : 'Editar Cliente';
$accion_post_form = ($accion == 'crear') ? 'insertar' : 'actualizar';
$boton_texto = ($accion == 'crear') ? '<i class="fas fa-plus"></i> Crear Cliente' : '<i class="fas fa-save"></i> Guardar Cambios';


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?> | VetAdmin</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        :root {
            --primary-color: #3b5998; 
            --success-color: #0f9d58; 
            --warning-color: #fbbc05; 
            --info-color: #4285f4; 
            --light-bg: #f7f9fc;
            --white: #ffffff;
            --text-dark: #333;
            --text-medium: #666;
            --shadow-light: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            --shadow-hover: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        body { font-family: 'Poppins', sans-serif; margin: 0; display: flex; min-height: 100vh; background-color: var(--light-bg); }
        .sidebar { width: 260px; background-color: var(--primary-color); color: var(--white); }
        .main-content { flex-grow: 1; display: flex; flex-direction: column; }
        .page-content { padding: 30px; flex-grow: 1; }
        .clients-panel { background-color: var(--white); border-radius: 10px; box-shadow: var(--shadow-light); padding: 25px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: var(--text-dark); }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .form-actions button, .form-actions a { padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 14px; margin-right: 10px; }
        .save-button { background-color: var(--success-color); color: white; border: none; cursor: pointer; }
        .cancel-button { background-color: #6c757d; color: var(--white); border: 1px solid #ccc; }
        
        .message-error { padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
    
    <div class="sidebar">
        </div>
    
    <div class="main-content">
        <header class="header">
            </header>

        <main class="page-content">
            <h2 class="page-title"><?php echo $titulo_pagina; ?></h2>

            <div class="clients-panel">
                
                <?php if ($msg_tipo == 'error'): ?>
                    <div class="message-error">Error: <?php echo htmlspecialchars(urldecode($msg_texto)); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $nombre_archivo_actual; ?>"> 
                    
                    <input type="hidden" name="accion_post" value="<?php echo $accion_post_form; ?>">
                    
                    <?php if ($accion == 'editar'): ?>
                        <input type="hidden" name="id_cliente" value="<?php echo htmlspecialchars($datos_cliente['id_cliente']); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="nombre">Nombre (*)</label>
                        <input type="text" id="nombre" name="nombre" 
                            value="<?php echo htmlspecialchars($datos_cliente['nombre'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido (*)</label>
                        <input type="text" id="apellido" name="apellido" 
                            value="<?php echo htmlspecialchars($datos_cliente['apellido'] ?? ''); ?>" required>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" 
                            value="<?php echo htmlspecialchars($datos_cliente['telefono'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                            value="<?php echo htmlspecialchars($datos_cliente['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion" 
                            value="<?php echo htmlspecialchars($datos_cliente['direccion'] ?? ''); ?>">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="save-button"><?php echo $boton_texto; ?></button>
                        <a href="<?php echo $nombre_archivo_principal; ?>" class="cancel-button"><i class="fas fa-times"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>