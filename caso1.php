<?php
$alumnos = ["Juan", "Ana", "Luis", "Marta", "Carlos", "Maria", "Pedro", "Juana", "Jose", "Yare"];
$califs = ["0","1","2","3","4","5","6","7","8","9","10","NP"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Lista de Calificaciones</title>
    <style>
        body { 
            background-color: #C1EB60; 
            padding: 20px; 
            font-family: Arial, sans-serif;
        }
        h1 { 
            text-align: center; 
        }
        form { 
            max-width: 600px; 
            margin: auto; 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        tr, td { 
            border: 2px solid black; 
            padding: 8px; 
            text-align: center;
        }
        select { 
            padding: 4px; 
        }
        button { 
            margin-top: 10px; 
            padding: 8px 16px; 
            background-color: #007bff; 
            border: none; 
            color: white; 
            border-radius: 5px; 
            cursor: pointer;
        }
        button:hover { 
            background-color: #8febb2ff; 
        }
    </style>
</head>
<body>
    <h1>Lista de Calificaciones</h1>
    <form method="POST" action="estadistica.php">
        <table>
            <tr>
                <td>Nombre</td>
                <td>Calificación</td>
            </tr>
            <?php foreach($alumnos as $alumno): ?>
            <tr>
                <td><?= $alumno ?></td>
                <td>
                    <select name="calificacion[<?= $alumno ?>]">
                        <?php foreach($califs as $calif): ?>
                            <option value="<?= $calif ?>"><?= $calif ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div style="text-align:center;">
            <button type="submit">Calcular Estadísticas</button>
        </div>
    </form>
</body>
</html>



