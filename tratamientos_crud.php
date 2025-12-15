<?php

session_start();

$login_page = 'login.php';
$dashboard_page = 'inicio.php'; 
$nombre_archivo_principal = 'tratamientos.php'; 
$nombre_archivo_actual = 'tratamientos_crud.php';

require_once 'conexion.php'; 



if (!isset($_SESSION['usuario'])) {
    header('Location: ' . $login_page);
    exit;
}

$usuario_actual = htmlspecialchars($_SESSION['usuario'] ?? 'Admin'); 
$rol_actual = 'Administrador'; 
$pagina_activa = 'tratamientos'; 
$mensaje = ''; 
$server = DB_SERVER;
$base = DB_BASE;
$usr = DB_USR;
$pass = DB_PASS;



$query_pacientes = "SELECT id_paciente, nombre, especie FROM pacientes ORDER BY nombre";
$pacientes = seleccionar($query_pacientes) ?: []; 

$tratamiento_id = null; 
$tratamiento_fecha_inicio = date('Y-m-d');
$tratamiento_descripcion = '';
$tratamiento_dosis = '';
$tratamiento_costo = '';
$tratamiento_fecha_fin = '';
$tratamiento_id_paciente = '';

if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $tratamiento_id = (int)$_GET['id'];
    
    $query_tratamiento = "
        SELECT id_paciente, fecha_inicio, descripcion, dosis, costo, fecha_fin_estimada 
        FROM tratamientos 
        WHERE id_tratamiento = $tratamiento_id
    ";
    $datos_tratamiento = seleccionar($query_tratamiento);
    
    if ($datos_tratamiento && count($datos_tratamiento) > 0) {
        $data = $datos_tratamiento[0];
        $tratamiento_id_paciente = $data[0];
        $tratamiento_fecha_inicio = date('Y-m-d', strtotime($data[1]));
        $tratamiento_descripcion = $data[2];
        $tratamiento_dosis = $data[3];
        $tratamiento_costo = $data[4];
        $tratamiento_fecha_fin = $data[5] ? date('Y-m-d', strtotime($data[5])) : '';
    } else {
        $mensaje = '<p class="error">Tratamiento no encontrado.</p>';
        $tratamiento_id = null; 
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_tratamiento'])) {
    $id = $_POST['id_tratamiento'] ? (int)$_POST['id_tratamiento'] : null;
    
    $cnx_escape = mysqli_connect($server, $usr, $pass, $base); 
    
    if (!$cnx_escape) {
        $mensaje = '<p class="error">ERROR FATAL: No se pudo establecer la conexión para procesar los datos. Revisar credenciales en conexion.php</p>';
        
        $tratamiento_id = $id; 
        $tratamiento_id_paciente = (int)$_POST['id_paciente'];
        $tratamiento_fecha_inicio = htmlspecialchars($_POST['fecha_inicio']);
        $tratamiento_descripcion = htmlspecialchars($_POST['descripcion']);
        $tratamiento_dosis = htmlspecialchars($_POST['dosis']);
        $tratamiento_costo = htmlspecialchars($_POST['costo']);
        $tratamiento_fecha_fin = htmlspecialchars($_POST['fecha_fin_estimada']);

    } else {
        $id_paciente = (int)$_POST['id_paciente'];
        $fecha_inicio = mysqli_real_escape_string($cnx_escape, $_POST['fecha_inicio']);
        $descripcion = mysqli_real_escape_string($cnx_escape, $_POST['descripcion']);
        $dosis = mysqli_real_escape_string($cnx_escape, $_POST['dosis']);
        $costo = (float)$_POST['costo'];
        
        if (empty($_POST['fecha_fin_estimada'])) {
            $fecha_fin = "NULL";
        } else {
            $fecha_fin = "'" . mysqli_real_escape_string($cnx_escape, $_POST['fecha_fin_estimada']) . "'";
        }

        mysqli_close($cnx_escape); 
        if (empty($fecha_inicio) || empty($descripcion) || $id_paciente <= 0 || $costo < 0) {
            $mensaje = '<p class="error">Error: Paciente, Fecha de Inicio, Descripción y Costo son obligatorios.</p>';
            
            $tratamiento_id = $id; 
            $tratamiento_id_paciente = $id_paciente;
            $tratamiento_fecha_inicio = htmlspecialchars($_POST['fecha_inicio']);
            $tratamiento_descripcion = htmlspecialchars($_POST['descripcion']);
            $tratamiento_dosis = htmlspecialchars($_POST['dosis']);
            $tratamiento_costo = htmlspecialchars($_POST['costo']);
            $tratamiento_fecha_fin = htmlspecialchars($_POST['fecha_fin_estimada']);

        } else {
            $campos_valores_str = [
                'id_paciente' => $id_paciente,
                'fecha_inicio' => "'$fecha_inicio'",
                'descripcion' => "'$descripcion'",
                'dosis' => "'$dosis'",
                'costo' => $costo,
                'fecha_fin_estimada' => $fecha_fin 
            ];
            
            $resultado = false;
            $query_ejecutar = '';
            
            if ($id) {
                $set_clauses = [];
                foreach ($campos_valores_str as $campo => $valor) {
                    $set_clauses[] = "$campo = $valor";
                }
                $query_ejecutar = "UPDATE tratamientos SET " . implode(', ', $set_clauses) . " WHERE id_tratamiento = $id";

                $resultado = function_exists('ejecutar') ? ejecutar($query_ejecutar, $server, $base, $usr, $pass) : false; 

            } else {
                $campos = implode(', ', array_keys($campos_valores_str));
                $valores = implode(', ', array_values($campos_valores_str));
                $query_ejecutar = "INSERT INTO tratamientos ($campos) VALUES ($valores)";

                $resultado = function_exists('insertar') ? insertar($query_ejecutar, $server, $base, $usr, $pass) : ejecutar($query_ejecutar, $server, $base, $usr, $pass);
            }
            
            if ($resultado) {
                $msg_tipo = $id ? 'update_ok' : 'create_ok';
                header("Location: $nombre_archivo_principal?msg=" . $msg_tipo);
                exit;
            } else {
                $cnx_debug = mysqli_connect($server, $usr, $pass, $base);
                if ($cnx_debug) {
                    $error_sql = mysqli_error($cnx_debug);
                    mysqli_close($cnx_debug);
                    
                    $mensaje = '<p class="error">ERROR SQL AL GUARDAR:<br>' . htmlspecialchars($error_sql) . 
                                '<br>Consulta Fallida: ' . htmlspecialchars($query_ejecutar) . '</p>';
                } else {
                     $mensaje = '<p class="error">ERROR CRÍTICO: No se pudo guardar el registro y la base de datos no es accesible para ver el error.</p>';
                }
            }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query_ejecutar = "DELETE FROM tratamientos WHERE id_tratamiento = $id";
    
    $resultado = function_exists('ejecutar') ? ejecutar($query_ejecutar, $server, $base, $usr, $pass) : false;
    
    if ($resultado) {
        header("Location: $nombre_archivo_principal?msg=delete_ok");
        exit;
    } else {
        header("Location: $nombre_archivo_principal?msg=error");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tratamiento_id ? 'Editar' : 'Nuevo'; ?> Tratamiento | VetAdmin</title>
    
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

        * { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: var(--light-bg); 
            display: flex;
            min-height: 100vh;
        }

        .sidebar { width: 260px; background-color: var(--primary-color); color: var(--white); padding: 20px 0; display: flex; flex-direction: column; flex-shrink: 0; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); }
        .vet-admin-title { padding: 0 20px 30px; font-size: 24px; font-weight: 700; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 15px; }
        .nav-link { display: flex; align-items: center; padding: 14px 25px; color: rgba(255, 255, 255, 0.85); text-decoration: none; transition: background-color 0.3s; }
        .nav-link.active { background-color: var(--white); color: var(--primary-color); font-weight: 600; position: relative; }
        .nav-link.active::before { content: ""; width: 5px; height: 100%; background-color: #8febb2; position: absolute; left: 0; top: 0; border-top-right-radius: 3px; border-bottom-right-radius: 3px; }
        .nav-link i { margin-right: 12px; }
        .main-content { flex-grow: 1; display: flex; flex-direction: column; }
        .header { display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; background-color: var(--white); box-shadow: var(--shadow-light); flex-shrink: 0; }
        .page-content { padding: 30px; flex-grow: 1; }
        h2.page-title { font-size: 28px; color: var(--text-dark); margin-bottom: 30px; font-weight: 600; text-align: center; } 

        .crud-panel {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow-light);
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); }
        .form-control, .form-select, .form-textarea {
            width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s;
        }

        .form-control:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--primary-color); outline: none; box-shadow: 0 0 0 2px rgba(59, 89, 152, 0.2);
        }

        .form-row { display: flex; gap: 20px; }
        .form-row .form-group { flex: 1; }
        .form-textarea { resize: vertical; min-height: 100px; }

        .form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 30px; }

        .btn-submit { padding: 10px 20px; background-color: var(--primary-color); color: var(--white); border: none; border-radius: 6px; font-size: 15px; cursor: pointer; transition: background-color 0.3s; }
        .btn-submit:hover { background-color: #2d4373; }

        .btn-cancel { padding: 10px 20px; background-color: #6c757d; color: var(--white); border: none; border-radius: 6px; font-size: 15px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: background-color 0.3s; }
        .btn-cancel:hover { background-color: #5a6268; }

        .success, .error { padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .user-profile { align-items: center; gap: 15px; display: flex;}
        .logout-button { background: none; border: none; color: #333; font-size: 18px; cursor: pointer; transition: color 0.2s; padding: 0; margin-left: 5px; text-decoration: none; }
        .logout-button:hover { color: #cc0000; }
        .user-avatar { width: 40px; height: 40px; background-color: #8febb2; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--text-dark); flex-shrink: 0; }
    </style>
</head>
<body>
    
    <div class="sidebar">
        <div class="vet-admin-title">VetAdmin</div>
        
        <nav class="sidebar-nav">
            <a href="<?php echo $dashboard_page; ?>" class="nav-link">
                <i class="fas fa-chart-line"></i> Inicio
            </a>
            <a href="citas.php" class="nav-link">
                <i class="fas fa-calendar-alt"></i> Citas
            </a>
            <a href="clientes.php" class="nav-link">
                <i class="fas fa-user-friends"></i> Clientes
            </a>
            <a href="pacientes.php" class="nav-link">
                <i class="fas fa-paw"></i> Pacientes
            </a>
            <a href="tratamientos.php" class="nav-link active">
                <i class="fas fa-syringe"></i> Tratamientos
            </a>
        </nav>
    </div>

    <div class="main-content">
        
        <header class="header">
            <div class="search-bar">
                </div>
            <div class="user-profile">
                <i class="fas fa-bell notification-icon"></i>
                
                <div class="user-info">
                    <strong>Dr. <?php echo $usuario_actual; ?></strong>
                    <br>
                    <small><?php echo $rol_actual; ?></small>
                </div>
                
                <div class="user-avatar"><?php echo strtoupper(substr($usuario_actual, 0, 1)); ?></div>
                
                <a href="logout.php" class="logout-button" title="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </header>

        <div class="page-content">
            <h2 class="page-title">
                <?php echo $tratamiento_id ? 'Editar Tratamiento' : 'Registrar Nuevo Tratamiento'; ?>
            </h2>

            <div class="crud-panel">
                
                <?php echo $mensaje;  ?>

                <form method="POST" action="<?php echo $nombre_archivo_actual; ?>" novalidate>
                    <input type="hidden" name="id_tratamiento" value="<?php echo htmlspecialchars($tratamiento_id ?? ''); ?>">
                    <input type="hidden" name="guardar_tratamiento" value="1">

                    <div class="form-group">
                        <label for="id_paciente">Paciente <span style="color: red;">*</span></label>
                        <select id="id_paciente" name="id_paciente" class="form-select" required>
                            <option value="">Seleccione un Paciente</option>
                            <?php 
                            foreach ($pacientes as $paciente): 
                                $id = htmlspecialchars($paciente[0]);
                                $nombre_completo = htmlspecialchars("{$paciente[1]} ({$paciente[2]})");
                            ?>
                                <option 
                                    value="<?php echo $id; ?>"
                                    <?php echo ($tratamiento_id_paciente == $id) ? 'selected' : ''; ?>
                                >
                                    <?php echo $nombre_completo; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha de Inicio <span style="color: red;">*</span></label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" 
                                value="<?php echo htmlspecialchars($tratamiento_fecha_inicio ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="fecha_fin_estimada">Fecha Fin Estimada</label>
                            <input type="date" id="fecha_fin_estimada" name="fecha_fin_estimada" class="form-control"
                                value="<?php echo htmlspecialchars($tratamiento_fecha_fin ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción del Tratamiento <span style="color: red;">*</span></label>
                        <textarea id="descripcion" name="descripcion" class="form-textarea" required><?php echo htmlspecialchars($tratamiento_descripcion ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="dosis">Dosis/Instrucciones</label>
                            <input type="text" id="dosis" name="dosis" class="form-control"
                                value="<?php echo htmlspecialchars($tratamiento_dosis ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="costo">Costo Estimado ($) <span style="color: red;">*</span></label>
                            <input type="number" step="0.01" id="costo" name="costo" class="form-control"
                                value="<?php echo htmlspecialchars($tratamiento_costo ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="<?php echo $nombre_archivo_principal; ?>" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> 
                            <?php echo $tratamiento_id ? 'Actualizar Tratamiento' : 'Guardar Tratamiento'; ?>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>