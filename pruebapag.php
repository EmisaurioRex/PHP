<?php

define('DB_SERVER', '127.0.0.1:3306'); 
define('DB_BASE', 'tiendita');   
define('DB_USR', 'root');        
define('DB_PASS', '1234');

$server = DB_SERVER;
$base = DB_BASE;
$usr = DB_USR;
$pass = DB_PASS;


function ejecutar($query, $server, $base, $usr, $pass) {
    $cnx = mysqli_connect($server, $usr, $pass, $base);
    if (mysqli_connect_errno()) {
        return false;
    }

    $res = mysqli_query($cnx, $query);
    mysqli_close($cnx);

    return $res;
}

function insertar($query, $server, $base, $usr, $pass) {
    $cnx = mysqli_connect($server, $usr, $pass, $base);
    if (mysqli_connect_errno()) {
        return false;
    }

    $res = mysqli_query($cnx, $query);
    $id = mysqli_insert_id($cnx); 
    
    mysqli_close($cnx);

    return $res ? $id : false;
}

function seleccionar($query, $server, $base, $usr, $pass) {
    $resultados = [];
    
    $cnx = mysqli_connect($server, $usr, $pass, $base);
    if (mysqli_connect_errno()) {
        return false;
    }
    
    $res = mysqli_query($cnx, $query);
    if ($res) {
         while ($registro = mysqli_fetch_row($res) ) {
            $resultados[] = $registro;
        }
        
        mysqli_free_result($res);
    }
   
    mysqli_close($cnx);
    
    return $resultados;
}


$mensaje = '';
$accion = isset($_POST['accion']) ? $_POST['accion'] : (isset($_GET['accion']) ? $_GET['accion'] : '');
$producto_a_editar = [];
$nombre_archivo = "pruebapag.php"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $cnx_saneamiento = mysqli_connect($server, $usr, $pass, $base);
    if (mysqli_connect_errno()) { die("Error de conexión para sanitización: " . mysqli_connect_error()); }

    $nombre = isset($_POST['nombre']) ? mysqli_real_escape_string($cnx_saneamiento, $_POST['nombre']) : '';
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
    $id = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
    
    mysqli_close($cnx_saneamiento);

    switch ($accion) {
        case 'agregar':
            $query = "INSERT INTO productos (nombre, precio) VALUES ('$nombre', $precio)";
            if (insertar($query, $server, $base, $usr, $pass)) {
                $mensaje = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Producto agregado con éxito.</div>';
            } else {
                $mensaje = '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Error al agregar producto.</div>';
            }
            break;

        case 'modificar':
            $query = "UPDATE productos SET nombre='$nombre', precio=$precio WHERE id_producto=$id";
            if (ejecutar($query, $server, $base, $usr, $pass)) {
                $mensaje = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Producto modificado con éxito.</div>';
            } else {
                $mensaje = '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Error al modificar producto.</div>';
            }
            $accion = ''; 
            break;
            
        case 'eliminar':
            $query = "DELETE FROM productos WHERE id_producto=$id";
            if (ejecutar($query, $server, $base, $usr, $pass)) {
                $mensaje = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Producto eliminado con éxito.</div>';
            } else {
                $mensaje = '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Error al eliminar producto.</div>';
            }
            break;
    }
}

if ($accion === 'editar' && isset($_GET['id'])) {
    $id_editar = intval($_GET['id']);
    $query = "SELECT id_producto, nombre, precio FROM productos WHERE id_producto = $id_editar";
    $resultado = seleccionar($query, $server, $base, $usr, $pass);
    
    if (!empty($resultado)) {
        $producto_a_editar = [
            'id' => $resultado[0][0],
            'nombre' => $resultado[0][1],
            'precio' => $resultado[0][2]
        ];
    } else {
        $mensaje = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Producto no encontrado.</div>';
        $accion = ''; 
    }
}

$query_productos = "SELECT id_producto, nombre, precio FROM productos ORDER BY id_producto DESC";
$productos = seleccionar($query_productos, $server, $base, $usr, $pass); 

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD de Productos - Mi Tienda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .crud-container { max-width: 1000px; margin: 30px auto; }
        .producto-row:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>

    <div class="container crud-container">
        <header class="mb-5 text-center">
            <h1 class="display-5 text-primary"><i class="fas fa-box"></i> TIENDITA EL ESFUERZO (CRUD)</h1>
        </header>
        
        <?php echo $mensaje; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm p-3 mb-4">
                    <h4 class="card-title text-center text-<?php echo !empty($producto_a_editar) ? 'warning' : 'success'; ?>">
                        <?php echo !empty($producto_a_editar) ? '<i class="fas fa-edit"></i> Modificar Producto' : '<i class="fas fa-plus-circle"></i> Agregar Nuevo Producto'; ?>
                    </h4>
                    <hr>
                    
                    <form method="POST" action="<?php echo $nombre_archivo; ?>"> 
                        <input type="hidden" name="accion" value="<?php echo !empty($producto_a_editar) ? 'modificar' : 'agregar'; ?>">
                        
                        <?php if (!empty($producto_a_editar)): ?>
                            <input type="hidden" name="id_producto" value="<?php echo $producto_a_editar['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Producto:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                value="<?php echo !empty($producto_a_editar) ? htmlspecialchars($producto_a_editar['nombre']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio:</label>
                            <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0.01" 
                                value="<?php echo !empty($producto_a_editar) ? htmlspecialchars($producto_a_editar['precio']) : ''; ?>" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-<?php echo !empty($producto_a_editar) ? 'warning' : 'success'; ?> btn-lg">
                                <?php echo !empty($producto_a_editar) ? '<i class="fas fa-save"></i> GUARDAR CAMBIOS' : '<i class="fas fa-plus"></i> AGREGAR PRODUCTO'; ?>
                            </button>
                            <?php if (!empty($producto_a_editar)): ?>
                                <a href="<?php echo $nombre_archivo; ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Cancelar Edición</a> 
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-md-8">
                <h4 class="text-center text-info mb-3">📋 Listado y Consulta de Productos</h4>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($productos)): ?>
                                <?php foreach ($productos as $p): ?>
                                    <tr class="producto-row">
                                        <td><?php echo $p[0]; ?></td>
                                        <td><?php echo htmlspecialchars($p[1]); ?></td>
                                        <td>$<?php echo number_format($p[2], 2); ?></td>
                                        <td>
                                            <!-- Botón de Modificar (Editar) -->
                                            <a href="<?php echo $nombre_archivo; ?>?accion=editar&id=<?php echo $p[0]; ?>" class="btn btn-sm btn-warning me-1"><i class="fas fa-pencil-alt"></i> Editar</a> 
                                            
                                            <!-- Formulario de Eliminar (usa POST para seguridad) -->
                                            <form method="POST" action="<?php echo $nombre_archivo; ?>" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar el producto: <?php echo htmlspecialchars($p[1]); ?>?');"> 
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id_producto" value="<?php echo $p[0]; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-danger">No hay productos registrados en la base de datos.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir JavaScript de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
