<?php


define('DB_SERVER', 'localhost'); 
define('DB_BASE', 'vetadmin_db'); 
define('DB_USR', 'root'); 
define('DB_PASS', '1234'); 


function ejecutar($query, $server = DB_SERVER, $base = DB_BASE, $usr = DB_USR, $pass = DB_PASS) {
    $cnx = mysqli_connect($server, $usr, $pass, $base);
    if (mysqli_connect_errno()) {
        die("ERROR FATAL DE CONEXION (funcion ejecutar): " . mysqli_connect_error());
    }

    $res = mysqli_query($cnx, $query);
    mysqli_close($cnx);

    return $res;
}


function insertar($query, $server = DB_SERVER, $base = DB_BASE, $usr = DB_USR, $pass = DB_PASS) {
    $cnx = mysqli_connect($server, $usr, $pass, $base);
    if (mysqli_connect_errno()) {
        die("ERROR FATAL DE CONEXION (funcion insertar): " . mysqli_connect_error());
    }

    $res = mysqli_query($cnx, $query);
    $id = mysqli_insert_id($cnx); 
    
    mysqli_close($cnx);

    return $res ? $id : false;
}


function seleccionar($query, $server = DB_SERVER, $base = DB_BASE, $usr = DB_USR, $pass = DB_PASS) {
    $resultados = [];
    
    $cnx = mysqli_connect($server, $usr, $pass, $base);
    if (mysqli_connect_errno()) {
        die("ERROR FATAL DE CONEXION (funcion seleccionar): " . mysqli_connect_error());
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

