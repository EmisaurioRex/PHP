<?php
session_start();

$login_page = 'login.php';
$dashboard_page = 'inicio.php'; 
$crud_page = 'tratamientos_crud.php'; 
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
$pagina_activa = 'tratamientos';


$query_tratamientos = "
    SELECT
        t.id_tratamiento, 
        t.fecha_inicio, 
        t.descripcion, 
        t.dosis, 
        t.costo, 
        t.fecha_fin_estimada,
        p.nombre AS nombre_paciente,
        p.especie
    FROM tratamientos t
    JOIN pacientes p ON t.id_paciente = p.id_paciente
    ORDER BY t.fecha_inicio DESC
";


$tratamientos = seleccionar($query_tratamientos) ?: [];



$mensaje_feedback = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'create_ok':
            $mensaje_feedback = '<p class="success-feedback">✅ Tratamiento registrado exitosamente.</p>';
            break;
        case 'update_ok':
            $mensaje_feedback = '<p class="success-feedback">✏️ Tratamiento actualizado exitosamente.</p>';
            break;
        case 'delete_ok':
            $mensaje_feedback = '<p class="success-feedback">🗑️ Tratamiento eliminado correctamente.</p>';
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
    <title>Tratamientos | VetAdmin</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        :root {
            --primary-color: #3b5998; 
            --success-color: #0f9d58; 
            --warning-color: #fbbc05; 
            --info-color: #4285f4; 
            --error-color: #cc0000;
            --light-bg: #f7f9fc;
            --white: #ffffff;
            --text-dark: #333;
            --text-medium: #666;
            --shadow-light: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
        }
        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: var(--light-bg); display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--primary-color); color: var(--white); padding: 20px 0; display: flex; flex-direction: column; flex-shrink: 0; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); }
        .vet-admin-title { padding: 0 20px 30px; font-size: 24px; font-weight: 700; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 15px; }
        .nav-link { display: flex; align-items: center; padding: 14px 25px; color: rgba(255, 255, 255, 0.85); text-decoration: none; transition: background-color 0.3s, color 0.3s; font-size: 15px; position: relative; font-weight: 400; }
        .nav-link:hover { background-color: #2d4373; color: var(--white); }
        .nav-link.active { background-color: var(--white); color: var(--primary-color); font-weight: 600; }
        .nav-link.active::before { content: ""; width: 5px; height: 100%; background-color: #8febb2; position: absolute; left: 0; top: 0; border-top-right-radius: 3px; border-bottom-right-radius: 3px; }
        .nav-link i { margin-right: 12px; font-size: 16px; }
        .main-content { flex-grow: 1; display: flex; flex-direction: column; }
        .header { display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; background-color: var(--white); box-shadow: var(--shadow-light); flex-shrink: 0; }
        .user-profile { display: flex; align-items: center; gap: 15px; }
        .user-info { font-size: 13px; text-align: right; line-height: 1.3; }
        .user-avatar { width: 40px; height: 40px; background-color: #8febb2; border-radius: 50%; display: flex; justify-content: center; align-items: center; color: var(--primary-color); font-weight: 700; font-size: 16px; }
        .logout-button { background: none; border: none; color: var(--text-medium); font-size: 18px; cursor: pointer; transition: color 0.2s; padding: 0; margin-left: 5px; }
        .logout-button:hover { color: #cc0000; }
        .dashboard-content { padding: 30px; flex-grow: 1; }
        h2.page-title { font-size: 28px; color: var(--text-dark); margin-bottom: 30px; font-weight: 600; }
        .data-panel { flex: 1; background-color: var(--white); border-radius: 10px; box-shadow: var(--shadow-light); padding: 25px; }
        .data-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; }
        .new-button { padding: 8px 18px; background-color: var(--primary-color); color: var(--white); border: none; border-radius: 6px; font-size: 14px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: background-color 0.3s; }
        .new-button:hover { background-color: #2d4373; }
        .empty-message { text-align: center; color: var(--text-medium); font-style: italic; padding: 30px 0; }
        .treatment-table { width: 100%; border-collapse: collapse; text-align: left; }
        .treatment-table th, .treatment-table td { padding: 12px 15px; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .treatment-table th { background-color: var(--light-bg); color: var(--text-dark); font-weight: 600; text-transform: uppercase; }
        .treatment-table tr:hover { background-color: #f9f9f9; }
        .status-ongoing { color: var(--info-color); font-weight: 600; }
        .status-finished { color: var(--success-color); font-weight: 600; }
        .status-pending { color: var(--warning-color); font-weight: 600; }
        .action-button { background: none; border: none; cursor: pointer; font-size: 14px; padding: 5px 8px; border-radius: 4px; transition: background-color 0.2s, color 0.2s; text-decoration: none; display: inline-block; }
        .edit-button { color: var(--info-color); }
        .delete-button { color: var(--error-color); }
        .success-feedback { padding: 10px 20px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-feedback { padding: 10px 20px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    
    <div class="sidebar" style="background-color: var(--primary-color);">
        <div class="vet-admin-title">VetAdmin</div>
        
        <nav class="sidebar-nav">
            <a href="inicio.php" class="nav-link"> <i class="fas fa-chart-line"></i> Inicio </a>
            <a href="citas.php" class="nav-link"> <i class="fas fa-calendar-alt"></i> Citas </a>
            <a href="clientes.php" class="nav-link"> <i class="fas fa-user-friends"></i> Clientes </a>
            <a href="pacientes.php" class="nav-link"> <i class="fas fa-paw"></i> Pacientes </a>
            <a href="tratamientos.php" class="nav-link active"> <i class="fas fa-syringe"></i> Tratamientos </a>
        </nav>
    </div>

    <div class="main-content">
        
        <header class="header">
            <div class="search-bar">
                <input type="text" id="tratamientoSearch" placeholder="Buscar tratamientos, pacientes..." />
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

        <main class="dashboard-content">
            <h2 class="page-title">Gestión de Tratamientos</h2>

            <?php echo $mensaje_feedback;  ?>

            <section class="data-panel">
                <div class="data-header">
                    <h3>Lista de Tratamientos</h3>
                    <a href="<?php echo $crud_page; ?>" class="new-button"><i class="fas fa-plus"></i> Registrar Tratamiento</a>
                </div>
                
                <div class="treatment-table-container">
                    <table class="treatment-table" id="treatmentTable">
                        <thead>
                            <tr>
                                <th>Paciente</th>
                                <th>Fecha Inicio</th>
                                <th>Descripción / Dosis</th>
                                <th>Costo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tratamientos)): ?>
                            <tr>
                                <td colspan="6">
                                    <p class="empty-message">Aún no hay tratamientos registrados.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($tratamientos as $tratamiento): 
                                    
                                    $tratamiento_id_for_crud = $tratamiento[0];
                                    $estado = 'En curso';
                                    $estado_class = 'status-ongoing';
                                    $fecha_fin_estimada = $tratamiento[5];

                                    if ($fecha_fin_estimada) {
                                        $fecha_actual = date('Y-m-d'); 
                                        $fecha_fin_str = date('Y-m-d', strtotime($fecha_fin_estimada));
                                        
                                        if ($fecha_fin_str <= $fecha_actual) { 
                                            $estado = 'Finalizado';
                                            $estado_class = 'status-finished';
                                        } else {
                                            $estado = 'Pendiente Fin';
                                            $estado_class = 'status-pending';
                                        }
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($tratamiento[6]); ?></strong> 
                                        <br><small><?php echo htmlspecialchars($tratamiento[7]); ?></small>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($tratamiento[1])); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($tratamiento[2]); ?>
                                        <br><small>Dosis: <?php echo htmlspecialchars($tratamiento[3]); ?></small>
                                    </td>
                                    <td>$<?php echo number_format((float)$tratamiento[4], 2, '.', ','); ?></td>
                                    <td><span class="<?php echo $estado_class; ?>"><?php echo $estado; ?></span></td>
                                    <td>
                                        <a href="<?php echo $crud_page; ?>?action=editar&id=<?php echo htmlspecialchars($tratamiento_id_for_crud); ?>" class="action-button edit-button" title="Editar"><i class="fas fa-edit"></i></a>
                                        <a 
                                            href="<?php echo $crud_page; ?>?action=eliminar&id=<?php echo htmlspecialchars($tratamiento_id_for_crud); ?>" 
                                            class="action-button delete-button" 
                                            title="Eliminar"
                                            onclick="return confirm('¿Está seguro de que desea eliminar este tratamiento?');"
                                        >
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('tratamientoSearch');
            const table = document.getElementById('treatmentTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            searchInput.addEventListener('keyup', function() {
                const filter = searchInput.value.toLowerCase();
                let rowCount = 0;

                Array.from(rows).forEach(function(row) {
                    if (row.querySelector('.empty-message')) return; 

                    let textContent = '';
                    for (let i = 0; i < 5; i++) {
                        textContent += row.cells[i].textContent + ' ';
                    }
                    textContent = textContent.toLowerCase();

                    if (textContent.includes(filter)) {
                        row.style.display = '';
                        rowCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>