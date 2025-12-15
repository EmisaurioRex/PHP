<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$login_page = 'login.php'; 
$dashboard_page = 'inicio.php'; 


require_once 'conexion.php'; 

if (!isset($_SESSION['usuario'])) {
    if (isset($_COOKIE['recuerdame']) && !empty($_COOKIE['recuerdame'])) {
        $_SESSION['usuario'] = $_COOKIE['recuerdame'];
    } else {
        header('Location: ' . $login_page); 
        exit;
    }
}

if (!empty($_POST) && isset($_POST['cerrar'])) {
    session_unset();
    session_destroy();
    setcookie("recuerdame", '', time() - 3600, "/");
    header('Location: ' . $login_page);
    exit;
}

$usuario_actual = htmlspecialchars($_SESSION['usuario'] ?? 'Admin'); 
$rol_actual = 'Administrador'; 
$pagina_activa = 'citas'; 
$mensaje = ''; 


$query_pacientes = "SELECT id_paciente, nombre, especie FROM pacientes ORDER BY nombre";
$pacientes = seleccionar($query_pacientes) ?: []; 

$cita_id = null;
$cita_fecha_hora = '';
$cita_motivo = '';
$cita_estado = 'pendiente';
$cita_id_paciente = '';


if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $cita_id = (int)$_GET['id'];
    
    $query_cita = "SELECT fecha_hora, motivo, estado, id_paciente FROM citas WHERE id_cita = $cita_id";
    $datos_cita = seleccionar($query_cita);
    
    if ($datos_cita && count($datos_cita) > 0) {
        $cita_fecha_hora = str_replace(' ', 'T', $datos_cita[0][0]); 
        $cita_motivo = $datos_cita[0][1];
        $cita_estado = $datos_cita[0][2];
        $cita_id_paciente = $datos_cita[0][3];
    } else {
        $mensaje = '<p class="error">Cita no encontrada.</p>';
        $cita_id = null; 
    }
}


if (!empty($_POST) && isset($_POST['guardar_cita'])) {
    $id = $_POST['id_cita'] ? (int)$_POST['id_cita'] : null;
    

    $cnx_escape = mysqli_connect(DB_SERVER, DB_USR, DB_PASS, DB_BASE); 
    $fecha_hora = mysqli_real_escape_string($cnx_escape, $_POST['fecha_hora']);
    $motivo = mysqli_real_escape_string($cnx_escape, $_POST['motivo']);
    $estado = mysqli_real_escape_string($cnx_escape, $_POST['estado']);
    $id_paciente = (int)$_POST['id_paciente'];
    
    mysqli_close($cnx_escape); 
    $campos_valores_str = [
        'fecha_hora' => "'$fecha_hora'",
        'motivo' => "'$motivo'",
        'estado' => "'$estado'",
        'id_paciente' => $id_paciente
    ];
    
    if ($id) {
        $set_clauses = [];
        foreach ($campos_valores_str as $campo => $valor) {
            $set_clauses[] = "$campo = $valor";
        }
        $query = "UPDATE citas SET " . implode(', ', $set_clauses) . " WHERE id_cita = $id";

        
        $resultado = ejecutar($query, DB_SERVER, DB_BASE, DB_USR, DB_PASS); 

        if ($resultado) {
            header('Location: citas.php?msg=update_ok');
            exit;
        } else {
            $mensaje = '<p class="error">Error al actualizar la cita. Verifique la tabla citas.</p>';
        }
    } else {
        $campos = implode(', ', array_keys($campos_valores_str));
        $valores = implode(', ', array_values($campos_valores_str));
        $query = "INSERT INTO citas ($campos) VALUES ($valores)";

        $resultado = insertar($query, DB_SERVER, DB_BASE, DB_USR, DB_PASS);
        
        if ($resultado) {
            header('Location: citas.php?msg=create_ok');
            exit;
        } else {
            $mensaje = '<p class="error">Error al crear la cita. Verifique la tabla citas.</p>';
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "DELETE FROM citas WHERE id_cita = $id";
    
    $resultado = ejecutar($query, DB_SERVER, DB_BASE, DB_USR, DB_PASS);
    
    if ($resultado) {
        header('Location: citas.php?msg=delete_ok');
        exit;
    } else {
        header('Location: citas.php?msg=error');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $cita_id ? 'Editar' : 'Crear'; ?> Cita | VetAdmin</title>
    
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control, .form-select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 89, 152, 0.2);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
        }

        .btn-submit {
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #2d4373;
        }

        .btn-cancel {
            padding: 10px 20px;
            background-color: #ccc;
            color: var(--text-dark);
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        
        .btn-cancel:hover {
            background-color: #bbb;
        }
        
        .success, .error {
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
        }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .user-profile { align-items: center; gap: 15px; display: flex;}
        .logout-button { background: none; border: none; color: var(--text-medium); font-size: 18px; cursor: pointer; transition: color 0.2s; padding: 0; margin-left: 5px; }
        .logout-button:hover { color: #cc0000; }
    </style>
</head>
<body>
    
    <div class="sidebar">
        <div class="vet-admin-title">VetAdmin</div>
        
        <nav class="sidebar-nav">
            <a href="<?php echo $dashboard_page; ?>" class="nav-link <?php echo ($pagina_activa == 'dashboard' ? 'active' : ''); ?>">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="citas.php" class="nav-link active">
                <i class="fas fa-calendar-alt"></i> Citas
            </a>
            <a href="clientes.php" class="nav-link <?php echo ($pagina_activa == 'clientes' ? 'active' : ''); ?>">
                <i class="fas fa-user-friends"></i> Clientes
            </a>
            <a href="pacientes.php" class="nav-link <?php echo ($pagina_activa == 'pacientes' ? 'active' : ''); ?>">
                <i class="fas fa-paw"></i> Pacientes
            </a>
            <a href="tratamientos.php" class="nav-link <?php echo ($pagina_activa == 'tratamientos' ? 'active' : ''); ?>">
                <i class="fas fa-syringe"></i> Tratamientos
            </a>
        </nav>
    </div>

    <div class="main-content">
        
        <header class="header">
            <div class="search-bar">
                <input type="text" placeholder="Buscar pacientes, clientes, citas..." />
            </div>
            <div class="user-profile">
                <i class="fas fa-bell notification-icon"></i>
                
                <div class="user-info">
                    <strong>Dr. <?php echo $usuario_actual; ?></strong>
                    <br>
                    <small><?php echo $rol_actual; ?></small>
                </div>
                
                <div class="user-avatar"><?php echo strtoupper(substr($usuario_actual, 0, 1)); ?></div>
                
                <form method="POST" action="" style="margin: 0;">
                    <button type="submit" name="cerrar" class="logout-button" title="Cerrar Sesión">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </header>

        <main class="page-content">
            <h2 class="page-title"><?php echo $cita_id ? 'Editar Cita Existente' : 'Programar Nueva Cita'; ?></h2>
            
            <?php echo $mensaje;  ?>

            <div class="crud-panel">
                <form method="POST" action="citas_crud.php">
                    <input type="hidden" name="id_cita" value="<?php echo $cita_id; ?>">
                    <input type="hidden" name="guardar_cita" value="1">

                    <div class="form-group">
                        <label for="id_paciente">Paciente</label>
                        <select name="id_paciente" id="id_paciente" class="form-select" required>
                            <option value="">Seleccione un paciente</option>
                            <?php foreach ($pacientes as $paciente): 
                                
                            ?>
                                <option 
                                    value="<?php echo htmlspecialchars($paciente[0]); ?>"
                                    <?php echo ($cita_id_paciente == $paciente[0]) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($paciente[1]); ?> (<?php echo htmlspecialchars($paciente[2]); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha_hora">Fecha y Hora</label>
                        <input 
                            type="datetime-local" 
                            name="fecha_hora" 
                            id="fecha_hora" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($cita_fecha_hora); ?>" 
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label for="motivo">Motivo de la Cita</label>
                        <textarea 
                            name="motivo" 
                            id="motivo" 
                            class="form-control" 
                            rows="3" 
                            required
                        ><?php echo htmlspecialchars($cita_motivo); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado de la Cita</label>
                        <select name="estado" id="estado" class="form-select" required>
                            <?php 
                                $estados = ['pendiente', 'confirmada', 'cancelada'];
                                foreach ($estados as $estado):
                            ?>
                                <option 
                                    value="<?php echo $estado; ?>"
                                    <?php echo (strtolower($cita_estado) == $estado) ? 'selected' : ''; ?>
                                >
                                    <?php echo ucfirst($estado); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <a href="citas.php" class="btn-cancel"><i class="fas fa-times"></i> Cancelar</a>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> <?php echo $cita_id ? 'Actualizar Cita' : 'Guardar Cita'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>