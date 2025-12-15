<?php

session_start();

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
$pagina_activa = 'citas'; 

require_once 'conexion.php'; 


$query_citas = "
    SELECT 
        c.id_cita, c.fecha_hora, c.motivo, c.estado,
        p.nombre AS nombre_paciente, p.especie,
        cl.nombre AS nombre_cliente, cl.apellido AS apellido_cliente
    FROM citas c
    JOIN pacientes p ON c.id_paciente = p.id_paciente
    JOIN clientes cl ON p.id_cliente = cl.id_cliente
    ORDER BY c.fecha_hora DESC
";

$citas = seleccionar($query_citas);

if ($citas === false) {
    $citas = [];
}

$mensaje_feedback = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'create_ok':
            $mensaje_feedback = '<p class="success-feedback">✅ Cita creada exitosamente.</p>';
            break;
        case 'update_ok':
            $mensaje_feedback = '<p class="success-feedback">✏️ Cita actualizada exitosamente.</p>';
            break;
        case 'delete_ok':
            $mensaje_feedback = '<p class="success-feedback">🗑️ Cita eliminada correctamente.</p>';
            break;
        case 'error':
            $mensaje_feedback = '<p class="error-feedback">❌ Ha ocurrido un error al procesar la solicitud.</p>';
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Citas | VetAdmin</title>
    
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

        .search-bar input {
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 25px;
            width: 350px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-icon {
            font-size: 18px;
            color: var(--text-medium);
            cursor: pointer;
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
            font-size: 16px;
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

        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
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

        /* Feedback Messages */
        .success-feedback, .error-feedback {
            padding: 10px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .success-feedback {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-feedback {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }


        .appointments-panel {
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
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 15px;
        }

        .data-header h3 {
            margin: 0;
            font-size: 20px;
            color: var(--text-dark);
            font-weight: 600;
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

        .empty-message {
            text-align: center;
            color: var(--text-medium);
            font-style: italic;
            padding: 30px 0;
        }
        
        .appointment-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .action-button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            padding: 5px;
            border-radius: 4px;
            transition: background-color 0.2s, color 0.2s;
            text-decoration: none; /* Es un enlace */
        }

        .edit-button {
            color: var(--info-color); /* Azul */
        }

        .edit-button:hover {
            background-color: rgba(66, 133, 244, 0.1);
        }

        .delete-button {
            color: #cc0000; /* Rojo */
        }

        .delete-button:hover {
            background-color: rgba(204, 0, 0, 0.1);
        }


        .status-tag {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            color: var(--white);
            text-align: center;
            text-transform: uppercase;
        }

        .status-confirmada { background-color: var(--success-color); } 
        .status-pendiente { background-color: var(--warning-color); } 
        .status-cancelada { background-color: #cc0000; } 

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
            <h2 class="page-title">Gestión de Citas</h2>

            <?php echo $mensaje_feedback;  ?>

            <div class="appointments-panel">
                <div class="data-header">
                    <h3>Citas Programadas</h3>
                    <a href="citas_crud.php" class="new-button"><i class="fas fa-plus"></i> Nueva Cita</a>
                </div>
                
                <?php if (empty($citas)): ?>
                    <p class="empty-message">No hay citas programadas para mostrar.</p>
                <?php else: ?>
                    <ul class="appointment-list">
                        <?php foreach ($citas as $cita): 
                           
                            $cita_id_for_crud = $cita[0];
                        ?>
                            <li class="list-item">
                                <div>
                                    <strong><?php echo date('d/m/Y H:i', strtotime($cita[1])); ?></strong>
                                    <br>
                                    Paciente: <?php echo htmlspecialchars($cita[4]); ?> (<?php echo htmlspecialchars($cita[5]); ?>) | Dueño: <?php echo htmlspecialchars($cita[6] . ' ' . $cita[7]); ?>
                                    <small> | Motivo: <?php echo htmlspecialchars($cita[2]); ?></small>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="status-tag status-<?php echo strtolower($cita[3]); ?>">
                                        <?php echo htmlspecialchars($cita[3]); ?>
                                    </span>
                                    
                                    <a href="citas_crud.php?action=editar&id=<?php echo $cita_id_for_crud; ?>" class="action-button edit-button" title="Editar Cita"><i class="fas fa-edit"></i></a>
                                    <a 
                                        href="citas_crud.php?action=eliminar&id=<?php echo $cita_id_for_crud; ?>" 
                                        class="action-button delete-button" 
                                        title="Eliminar Cita" 
                                        onclick="return confirm('¿Está seguro de que desea eliminar esta cita? Esta acción es irreversible.');"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
            </div>
        </main>
    </div>
</body>
</html>