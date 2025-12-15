<?php

session_start();

require_once 'conexion.php'; 

$login_page = 'login.php';
$nombre_archivo_principal = 'pacientes.php'; 
$nombre_archivo_actual = 'pacientes_crud.php';

$server = DB_SERVER;
$base = DB_BASE;
$usr = DB_USR;
$pass = DB_PASS;


if (!isset($_SESSION['usuario'])) {
    header('Location: ' . $login_page);
    exit;
}

$usuario_actual = htmlspecialchars($_SESSION['usuario'] ?? 'Admin');
$rol_actual = 'Administrador';
$pagina_activa = 'pacientes'; 

$msg_tipo = '';
$msg_texto = '';

$accion_actual = isset($_GET['accion']) ? htmlspecialchars($_GET['accion']) : 'crear'; 
$id_paciente = isset($_GET['id']) ? intval($_GET['id']) : 0; 

$paciente_datos = [
    'id_paciente' => 0, 
    'nombre' => '', 'especie' => '', 'raza' => '', 'fecha_nacimiento' => '', 'sexo' => '', 'id_cliente' => 0
];



$query_clientes = "SELECT id_cliente, nombre, apellido FROM clientes ORDER BY apellido ASC";
$clientes_duenos = seleccionar($query_clientes); 
if ($clientes_duenos === false) {
    $clientes_duenos = [];
    $msg_tipo = 'error';
    $msg_texto = urlencode("Error al cargar la lista de clientes (dueños). Verifique 'conexion.php' y el estado del servidor MySQL.");
}



if ($accion_actual == 'eliminar' && $id_paciente > 0) {
    $query_delete = "DELETE FROM pacientes WHERE id_paciente = $id_paciente";
    
    $res = function_exists('ejecutar') ? ejecutar($query_delete, $server, $base, $usr, $pass) : false;

    $msg_tipo = $res ? 'exito' : 'error';
    $msg_texto = urlencode($res ? "El paciente fue eliminado correctamente." : "Hubo un error al intentar eliminar el paciente.");
    header("Location: $nombre_archivo_principal?tipo=$msg_tipo&msg=$msg_texto");
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion_post = isset($_POST['accion_post']) ? htmlspecialchars($_POST['accion_post']) : '';

    if ($accion_post == 'insertar_paciente' || $accion_post == 'actualizar_paciente') {
        
        $cnx_saneamiento = mysqli_connect($server, $usr, $pass, $base);
        
        if (mysqli_connect_errno()) {
            $msg_tipo = 'error';
            $msg_texto = urlencode("ERROR FATAL DE CONEXIÓN: La BD no está disponible para procesar el formulario.");
            $accion_actual = ($id_paciente > 0) ? 'editar' : 'crear'; 

        } else {
            $id_paciente_post = isset($_POST['id_paciente']) ? intval($_POST['id_paciente']) : 0;
            $nombre = isset($_POST['nombre']) ? mysqli_real_escape_string($cnx_saneamiento, trim($_POST['nombre'])) : '';
            $especie = isset($_POST['especie']) ? mysqli_real_escape_string($cnx_saneamiento, trim($_POST['especie'])) : '';
            $raza = isset($_POST['raza']) ? mysqli_real_escape_string($cnx_saneamiento, trim($_POST['raza'])) : NULL;
            $fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? mysqli_real_escape_string($cnx_saneamiento, $_POST['fecha_nacimiento']) : NULL;
            $sexo = isset($_POST['sexo']) ? mysqli_real_escape_string($cnx_saneamiento, $_POST['sexo']) : NULL;
            $id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
            
            mysqli_close($cnx_saneamiento); 

            $paciente_datos = [
                'id_paciente' => $id_paciente_post, 'nombre' => $nombre, 'especie' => $especie,
                'raza' => $raza, 'fecha_nacimiento' => $fecha_nacimiento, 'sexo' => $sexo, 'id_cliente' => $id_cliente
            ];
            $accion_actual = ($id_paciente_post > 0) ? 'editar' : 'crear'; 
            $id_paciente = $id_paciente_post;


            if (empty($nombre) || empty($especie) || $id_cliente <= 0) {
                $msg_tipo = 'error';
                $msg_texto = urlencode("Error: Nombre, Especie y Dueño son campos obligatorios.");
            } else {
                $raza_sql = $raza ? "'$raza'" : "NULL";
                $fecha_nacimiento_sql = $fecha_nacimiento ? "'$fecha_nacimiento'" : "NULL";
                $sexo_sql = $sexo ? "'$sexo'" : "NULL";

                if ($id_paciente_post > 0) {
                    $query = "UPDATE pacientes SET id_cliente = $id_cliente, nombre = '$nombre', especie = '$especie', raza = $raza_sql, fecha_nacimiento = $fecha_nacimiento_sql, sexo = $sexo_sql WHERE id_paciente = $id_paciente_post";
                    $res = function_exists('ejecutar') ? ejecutar($query, $server, $base, $usr, $pass) : false;
                    $msg_texto = ($res) ? "El paciente $nombre fue actualizado correctamente." : "Hubo un error al intentar actualizar el paciente.";
                } else {
                    $query = "INSERT INTO pacientes (id_cliente, nombre, especie, raza, fecha_nacimiento, sexo) VALUES ($id_cliente, '$nombre', '$especie', $raza_sql, $fecha_nacimiento_sql, $sexo_sql)";
                    $res = function_exists('insertar') ? insertar($query, $server, $base, $usr, $pass) : ejecutar($query, $server, $base, $usr, $pass);
                    $msg_texto = ($res) ? "El paciente $nombre fue agregado correctamente." : "Hubo un error al intentar agregar el paciente.";
                }
                
                $msg_tipo = ($res) ? 'exito' : 'error';
                header("Location: $nombre_archivo_principal?tipo=$msg_tipo&msg=" . urlencode($msg_texto));
                exit();
            }
        }
    }
}




if ($accion_actual == 'editar' && $id_paciente > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    
    $query_paciente = "SELECT id_paciente, id_cliente, nombre, especie, raza, fecha_nacimiento, sexo FROM pacientes WHERE id_paciente = $id_paciente";
    
    $paciente_a_editar = seleccionar($query_paciente); 

    if (!empty($paciente_a_editar)) {
        $datos = $paciente_a_editar[0]; 
        
        $paciente_datos = [
            'id_paciente' => $datos[0], 'id_cliente' => $datos[1], 'nombre' => $datos[2], 
            'especie' => $datos[3], 'raza' => $datos[4], 'fecha_nacimiento' => $datos[5], 
            'sexo' => $datos[6]
        ];
    } else {
        $accion_actual = 'crear'; 
        $id_paciente = 0;
    }
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo ($accion_actual == 'crear') ? 'Registrar Nuevo Paciente' : 'Editar Paciente'; ?> | VetAdmin
    </title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        :root {
            --primary-color: #3b5998; --success-color: #0f9d58; --light-bg: #f7f9fc; --white: #ffffff;
            --text-dark: #333; --shadow-light: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: var(--light-bg); display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--primary-color); color: var(--white); padding: 20px 0; flex-shrink: 0; }
        .vet-admin-title { padding: 0 20px 30px; font-size: 24px; font-weight: 700; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 15px; }
        .nav-link { display: flex; align-items: center; padding: 14px 25px; color: rgba(255, 255, 255, 0.85); text-decoration: none; font-size: 15px; position: relative; font-weight: 400; }
        .nav-link.active { background-color: var(--white); color: var(--primary-color); font-weight: 600; }
        .main-content { flex-grow: 1; display: flex; flex-direction: column; }
        .header { display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; background-color: var(--white); box-shadow: var(--shadow-light); flex-shrink: 0; }
        .user-profile { display: flex; align-items: center; gap: 15px; }
        .user-info { font-size: 13px; text-align: right; line-height: 1.3; }
        .user-avatar { width: 40px; height: 40px; background-color: #8febb2; border-radius: 50%; }
        .page-content { padding: 30px; flex-grow: 1; }
        h2.page-title { font-size: 28px; color: var(--text-dark); margin-bottom: 30px; font-weight: 600; }
        .clients-panel { background-color: var(--white); border-radius: 10px; box-shadow: var(--shadow-light); padding: 25px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: var(--text-dark); }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .form-row { display: flex; gap: 20px; }
        .form-row .form-group { flex: 1; }
        .form-actions button, .form-actions a { padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 14px; margin-right: 10px; display: inline-flex; align-items: center; gap: 8px; transition: background-color 0.3s; }
        .save-button { background-color: var(--success-color); color: white; border: none; cursor: pointer; }
        .save-button:hover { background-color: #0c8247; }
        .cancel-button { background-color: #6c757d; color: var(--white); border: none; }
        .cancel-button:hover { background-color: #5a6268; }
        .message-error { padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; margin-bottom: 15px; border-radius: 4px; font-weight: 500;}
    </style>
</head>
<body>
    
    <div class="sidebar">
        <div class="vet-admin-title">VetAdmin</div>
        <nav class="sidebar-nav">
            <a href="inicio.php" class="nav-link"> <i class="fas fa-chart-line"></i> Inicio </a>
            <a href="citas.php" class="nav-link"> <i class="fas fa-calendar-alt"></i> Citas </a>
            <a href="clientes.php" class="nav-link"> <i class="fas fa-user-friends"></i> Clientes </a>
            <a href="pacientes.php" class="nav-link active"> <i class="fas fa-paw"></i> Pacientes </a>
            <a href="tratamientos.php" class="nav-link"> <i class="fas fa-syringe"></i> Tratamientos </a>
        </nav>
    </div>
    
    <div class="main-content">
        <header class="header">
            <div class="search-bar"> </div>
            <div class="user-profile">
                <div class="user-info">
                    <strong>Dr. <?php echo $usuario_actual; ?></strong>
                    <span><?php echo $rol_actual; ?></span>
                </div>
                <div class="user-avatar">AD</div>
                <a href="logout.php" title="Cerrar Sesión" style="color: #333; margin-left: 10px; text-decoration: none;"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <div class="page-content">
            <h2 class="page-title">
                <?php echo ($accion_actual == 'crear') ? 'Registrar Nuevo Paciente' : 'Editar Paciente: ' . htmlspecialchars($paciente_datos['nombre'] ?? 'ID ' . $id_paciente); ?>
            </h2>

            <div class="clients-panel">

                <?php if ($msg_tipo == 'error' && $msg_texto): ?>
                    <div class="message-error"><?php echo urldecode($msg_texto); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $nombre_archivo_actual; ?>" novalidate>

                    <input type="hidden" name="accion_post" value="<?php echo ($accion_actual == 'crear') ? 'insertar_paciente' : 'actualizar_paciente'; ?>">
                    <input type="hidden" name="id_paciente" value="<?php echo $paciente_datos['id_paciente']; ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre del Paciente <span style="color: red;">*</span></label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($paciente_datos['nombre']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="especie">Especie <span style="color: red;">*</span></label>
                            <input type="text" id="especie" name="especie" value="<?php echo htmlspecialchars($paciente_datos['especie']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="raza">Raza</label>
                            <input type="text" id="raza" name="raza" value="<?php echo htmlspecialchars($paciente_datos['raza']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($paciente_datos['fecha_nacimiento']); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="sexo">Sexo</label>
                            <select id="sexo" name="sexo">
                                <option value="" <?php echo ($paciente_datos['sexo'] == '') ? 'selected' : ''; ?>>Seleccionar</option>
                                <option value="Macho" <?php echo ($paciente_datos['sexo'] == 'Macho') ? 'selected' : ''; ?>>Macho</option>
                                <option value="Hembra" <?php echo ($paciente_datos['sexo'] == 'Hembra') ? 'selected' : ''; ?>>Hembra</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="id_cliente">Dueño (Cliente) <span style="color: red;">*</span></label>
                            <select id="id_cliente" name="id_cliente" required>
                                <option value="">Seleccione un Cliente</option>
                                <?php foreach ($clientes_duenos as $cliente): 
                                    $cliente_id = htmlspecialchars($cliente[0]);
                                    $cliente_nombre = htmlspecialchars("{$cliente[1]} {$cliente[2]}");
                                ?>
                                    <option 
                                        value="<?php echo $cliente_id; ?>"
                                        <?php echo ($paciente_datos['id_cliente'] == $cliente_id) ? 'selected' : ''; ?>
                                    >
                                        <?php echo $cliente_nombre; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 20px;">
                        <button type="submit" class="save-button">
                            <i class="fas fa-save"></i> 
                            <?php echo ($accion_actual == 'crear') ? 'Guardar Paciente' : 'Actualizar Paciente'; ?>
                        </button>
                        <a href="<?php echo $nombre_archivo_principal; ?>" class="cancel-button">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</body>
</html>