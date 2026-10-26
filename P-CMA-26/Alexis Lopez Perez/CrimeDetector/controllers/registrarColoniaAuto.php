<?php
session_start();
require_once '../libs/conexion.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$cp       = trim($data['codigo_postal'] ?? '');
$nombre   = trim($data['nombre'] ?? '');
$latitud  = filter_var($data['latitud'] ?? null, FILTER_VALIDATE_FLOAT);
$longitud = filter_var($data['longitud'] ?? null, FILTER_VALIDATE_FLOAT);

if (!empty($cp) && !empty($nombre) && $latitud !== false && $longitud !== false) {
    try {
        // Verificar si la colonia ya existe por Código Postal
        $stmtCheck = $pdo->prepare("SELECT CODIGO_POSTAL FROM colonias WHERE CODIGO_POSTAL = :cp");
        $stmtCheck->execute([':cp' => $cp]);

        if (!$stmtCheck->fetch()) {
            // Insertar la nueva colonia incluyendo LATITUD y LONGITUD
            $sqlInsert = "INSERT INTO colonias (CODIGO_POSTAL, NOMBRE, LATITUD, LONGITUD) 
                          VALUES (:cp, :nombre, :latitud, :longitud)";
            
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                ':cp'       => $cp,
                ':nombre'   => $nombre,
                ':latitud'  => $latitud,
                ':longitud' => $longitud
            ]);
        }

        echo json_encode([
            'success' => true, 
            'codigo_postal' => $cp, 
            'nombre' => $nombre,
            'latitud' => $latitud,
            'longitud' => $longitud
        ]);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Datos incompletos o coordenadas inválidas']);