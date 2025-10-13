<?php
$calificaciones = [];
$NP = 0;


if (isset($_POST["calificacion"])) {
    $NP = 0;
    $calificaciones = [];

    foreach ($_POST["calificacion"] as $nota) {
        if ($nota == "NP" || $nota == "np" || $nota == "Np" || $nota == "nP") {
            $NP++;
        } else {
            $calificaciones[] = $nota * 1; // convierte a número
        }
    }

    if (count($calificaciones) > 0) {
        $total = count($calificaciones);
        $suma = array_sum($calificaciones);
        $aprobados = 0;

        foreach ($calificaciones as $n) {
            if ($n >= 6) $aprobados++;
        }

        $reprobados = $total - $aprobados;
        $porc_aprobados = ($aprobados / $total) * 100;
        $porc_reprobados = ($reprobados / $total) * 100;
        $mejor = max($calificaciones);
        $peor = min($calificaciones);
        $promedio = $suma / $total;
    } else {
        $porc_aprobados = $porc_reprobados = $mejor = $peor = $promedio = 0;
    }
} else {
    $NP = $porc_aprobados = $porc_reprobados = $mejor = $peor = $promedio = 0;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Estadísticas de Calificaciones</title>
<style>
    table {
        border-collapse: collapse;
        width: 50%;
        margin: 20px auto;
        text-align: left;
    }
    td {
        border: 2px solid black;
        padding: 10px;
    }
    .titulo {
        font-weight: bold;
        background-color: #C1EB60;
        text-align: center;
    }
</style>
</head>
<body>

<table>
    <tr>
        <td class="titulo" colspan="2">Estadísticas de Calificaciones</td>
    </tr>
    <tr>
        <td>Total de NP</td>
        <td><?= $NP ?></td>
    </tr>
    <tr>
        <td>Porcentaje de aprobados</td>
        <td><?= $porc_aprobados ?>%</td>
    </tr>
    <tr>
        <td>Porcentaje de reprobados</td>
        <td><?= $porc_reprobados ?>%</td>
    </tr>
    <tr>
        <td>Mejor calificación</td>
        <td><?= $mejor ?></td>
    </tr>
    <tr>
        <td>Peor calificación</td>
        <td><?= $peor ?></td>
    </tr>
    <tr>
        <td>Promedio general</td>
        <td><?= $promedio ?></td>
    </tr>
</table>

<div>
    <a href="caso1.php">Volver a registrar calificaciones</a>
</div>

</body>
</html>
