<?php
// api.php
header("Content-Type: application/json; charset=UTF-8");
require 'db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// LECTURA (GET)
if ($metodo === 'GET') {
    $stmt = $pdo->query("SELECT * FROM tareas");
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($tareas);
} 
// CREACIÓN (POST)
elseif ($metodo === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if(!empty($data['titulo'])) {
        $stmt = $pdo->prepare("INSERT INTO tareas (titulo) VALUES (:titulo)");
        $stmt->execute(['titulo' => $data['titulo']]);
        echo json_encode(["mensaje" => "Tarea creada exitosamente"]);
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "El título es obligatorio"]);
    }
}
//eliminar
elseif ($metodo === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM tareas WHERE id = :id");
        $stmt->execute(['id' => $id]);
        echo json_encode(["mensaje" => "Tarea eliminada exitosamente"]);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "ID de tarea requerido"]);
    }
}

// actualizar
elseif ($metodo === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if(!empty($data['id']) && !empty($data['titulo'])) {
        $stmt = $pdo->prepare("UPDATE tareas SET titulo = :titulo WHERE id = :id");
        $stmt->execute([
            'titulo' => $data['titulo'],
            'id' => $data['id']
        ]);
        echo json_encode(["mensaje" => "Tarea actualizada exitosamente"]);
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "El ID y el título son obligatorios"]);
    }
}
?>