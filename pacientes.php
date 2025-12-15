<?php
session_start();

require_once 'conexion.php'; 

$login_page = 'login.php'; 
$dashboard_page = 'inicio.php'; 

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
$pagina_activa = 'pacientes'; 

$termino_busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$condicion_busqueda = '';
$error_db = null;

if (!empty($termino_busqueda)) {
    $termino_sql = "%" . htmlspecialchars($termino_busqueda, ENT_QUOTES, 'UTF-8') . "%";

    $condicion_busqueda = "
        WHERE p.nombre LIKE '$termino_sql'
        OR p.especie LIKE '$termino_sql'
        OR p.raza LIKE '$termino_sql'
        OR c.nombre LIKE '$termino_sql'
        OR c.apellido LIKE '$termino_sql'
    ";
}

$query_pacientes = "
    SELECT 
        p.id_paciente, 
        p.nombre, 
        p.especie, 
        p.raza, 
        p.fecha_nacimiento,
        p.sexo,
        c.nombre AS nombre_cliente,
        c.apellido AS apellido_cliente
    FROM pacientes p
    JOIN clientes c ON p.id_cliente = c.id_cliente
    {$condicion_busqueda} -- Inserta la condición de búsqueda aquí
    ORDER BY p.nombre ASC
";

$pacientes = seleccionar($query_pacientes);

if ($pacientes === false) {
    $error_db = "Error al consultar la base de datos. Por favor, verifica la tabla 'pacientes' y 'clientes'.";
    $pacientes = [];
} elseif (!is_array($pacientes)) {
    $pacientes = [];
}

$msg_tipo = isset($_GET['tipo']) ? htmlspecialchars($_GET['tipo']) : ''; 
$msg_texto = isset($_GET['msg']) ? htmlspecialchars(urldecode($_GET['msg'])) : '';


function calcular_edad($fecha_nacimiento_str) {
    if (empty($fecha_nacimiento_str)) {
        return 'N/A';
    }
    try {
        $fecha_nacimiento = new DateTime($fecha_nacimiento_str);
        $hoy = new DateTime();
        $edad = $fecha_nacimiento->diff($hoy);
        
        if ($edad->y > 0) {
            return $edad->y . ' años';
        } elseif ($edad->m > 0) {
            return $edad->m . ' meses';
        } else {
            return $edad->d . ' días';
        }
    } catch (Exception $e) {
        return 'Inválida';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pacientes | VetAdmin</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        :root {
            --primary-color: #3b5998; 
            --success-color: #0f9d58; 
            --info-color: #4285f4; 
            --light-bg: #f7f9fc;
            --white: #ffffff;
            --text-dark: #333;
            --text-medium: #666;
            --shadow-light: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            --shadow-hover: 0 5px 15px rgba(0, 0, 0, 0.1); 
            --error-color: #dc3545; 
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: var(--light-bg); 
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background-color: var(--primary-color); 
            color: var(--white);
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .vet-admin-title {
            padding: 0 20px 30px;
            font-size: 24px;
            font-weight: 700;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 15px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 14px 25px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
            font-size: 15px;
            position: relative;
            font-weight: 400;
        }

        .nav-link:hover {
            background-color: #2d4373;
            color: var(--white);
        }
        
        .nav-link.active {
            background-color: var(--white); 
            color: var(--primary-color); 
            font-weight: 600;
        }

        .nav-link.active::before {
            content: "";
            width: 5px;
            height: 100%;
            background-color: #8febb2;
            position: absolute;
            left: 0;
            top: 0;
            border-top-right-radius: 3px;
            border-bottom-right-radius: 3px;
        }

        .nav-link i {
            margin-right: 12px;
            font-size: 16px;
        }

        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: var(--white);
            box-shadow: var(--shadow-light);
            flex-shrink: 0;
        }

        .search-bar {
            position: relative;
            display: flex; 
            align-items: center;
        }
        .search-bar input {
            padding: 10px 20px 10px 40px; 
            border: 1px solid #ddd;
            border-radius: 25px;
            width: 350px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .search-bar input:focus {
            border-color: var(--primary-color);
        }
        .search-bar button {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 40px;
            background: none;
            border: none;
            color: var(--text-medium);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }


        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            font-size: 13px;
            text-align: right;
            line-height: 1.3;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background-color: #8febb2;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--primary-color);
            font-weight: 700;
        }

        .logout-button {
            background: none;
            border: none;
            color: var(--text-medium);
            font-size: 18px;
            cursor: pointer;
            transition: color 0.2s;
            padding: 0;
            margin-left: 5px;
        }

        .logout-button:hover {
            color: #cc0000;
        }

        .page-content {
            padding: 30px;
            flex-grow: 1;
        }

        h2.page-title {
            font-size: 28px;
            color: var(--text-dark);
            margin-bottom: 30px;
            font-weight: 600;
        }

        .patients-panel {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow-light);
            padding: 25px;
        }

        .data-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .new-button {
            padding: 8px 18px;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
        }

        .new-button:hover {
            background-color: #2d4373;
        }
        
        .patients-table th, .patients-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        .action-button {
            background: none;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
        }
        
        .delete-button {
            border: 1px solid #cc0000; 
            color: #cc0000; 
            background: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            margin-left: 5px;
            transition: background-color 0.3s, color 0.3s;
        }
        .delete-button:hover {
            background-color: #cc0000;
            color: var(--white);
        }

        .action-button:hover {
            background-color: var(--primary-color);
            color: var(--white);
        }
        
        .status-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        .status-message i {
            margin-right: 10px;
            font-size: 1.2em;
        }
        .status-exito {
            background-color: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb;
        }
        .status-error {
            background-color: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb;
        }
        .status-info {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }
    </style>
</head>
<body>
    
    <div class="sidebar">
        <div class="vet-admin-title">VetAdmin</div>
        
        <nav class="sidebar-nav">
            <a href="<?php echo $dashboard_page; ?>" class="nav-link <?php echo ($pagina_activa == 'dashboard' ? 'active' : ''); ?>">
                <i class="fas fa-chart-line"></i> Inicio
            </a>
            <a href="citas.php" class="nav-link <?php echo ($pagina_activa == 'citas' ? 'active' : ''); ?>">
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
            <form method="GET" action="pacientes.php" class="search-bar">
                <button type="submit" title="Buscar"><i class="fas fa-search"></i></button>
                <input 
                    type="text" 
                    name="buscar" 
                    placeholder="Buscar pacientes por nombre, dueño, especie..." 
                    value="<?php echo htmlspecialchars($termino_busqueda); ?>"
                />
            </form>
            
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
            <h2 class="page-title">Gestión de Pacientes</h2>
            
            <?php if ($error_db): ?>
                <div class="status-message status-error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_db; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($msg_texto)): ?>
                <div class="status-message status-<?php echo $msg_tipo; ?>">
                     <i class="fas fa-<?php echo ($msg_tipo == 'exito' ? 'check-circle' : 'info-circle'); ?>"></i> <?php echo $msg_texto; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($termino_busqueda)): ?>
                <div class="status-message status-info">
                    <i class="fas fa-filter"></i> Mostrando resultados para: **<?php echo htmlspecialchars($termino_busqueda); ?>**
                </div>
            <?php endif; ?>

            <div class="patients-panel">
                <div class="data-header">
                    <h3>Pacientes Registrados</h3>
                    <a href="pacientes_crud.php?accion=crear" class="new-button"><i class="fas fa-plus"></i> Nuevo Paciente</a>
                </div>
                
                <div class="patients-list-container">
                    
                    <table class="patients-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Especie / Raza</th>
                                <th>Edad / Sexo</th>
                                <th>Dueño</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pacientes)): ?>
                            <tr>
                                <td colspan="5">
                                    <p class="empty-message">
                                        <?php echo !empty($termino_busqueda) ? 'No se encontraron pacientes que coincidan con la búsqueda.' : 'No hay pacientes registrados para mostrar.'; ?>
                                    </p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($pacientes as $paciente): 

                                    $paciente_nombre = htmlspecialchars($paciente[1] ?? 'N/A');
                                    $paciente_especie = htmlspecialchars($paciente[2] ?? 'N/A');
                                    $paciente_raza = htmlspecialchars($paciente[3] ?? 'N/A');
                                    $paciente_sexo = htmlspecialchars($paciente[5] ?? 'N/A');
                                    $cliente_nombre = htmlspecialchars($paciente[6] ?? '');
                                    $cliente_apellido = htmlspecialchars($paciente[7] ?? '');
                                    
                                    $dueño_completo = trim($cliente_nombre . ' ' . $cliente_apellido);
                                    if (empty($dueño_completo)) {
                                        $dueño_completo = 'N/A';
                                    }
                                    
                                    $edad_str = calcular_edad($paciente[4]);
                                ?>
                                <tr>
                                    <td><strong><?php echo $paciente_nombre; ?></strong></td>
                                    <td><?php echo $paciente_especie; ?> (<?php echo $paciente_raza; ?>)</td>
                                    <td><?php echo $edad_str; ?> / <?php echo $paciente_sexo; ?></td>
                                    <td><?php echo $dueño_completo; ?></td> 
                                    <td>
                                        <a href="pacientes_crud.php?accion=editar&id=<?php echo htmlspecialchars($paciente[0]); ?>" class="action-button"><i class="fas fa-edit"></i> Editar</a>

                                        <form method="POST" action="pacientes_crud.php" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar al paciente <?php echo $paciente_nombre; ?>?');">
                                            <input type="hidden" name="id_paciente" value="<?php echo htmlspecialchars($paciente[0]); ?>">
                                            <input type="hidden" name="accion_post" value="eliminar_paciente">
                                            <button type="submit" class="delete-button"><i class="fas fa-trash"></i> Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                        </tbody>
                    </table>
                    
                </div>
            </div>
        </main>
    </div>
</body>
</html>