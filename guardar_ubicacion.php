<?php
define('API_CALL', true); // Evitar mensajes de echo en conexion.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir la conexión
require_once 'conexion.php';

// Obtener datos del POST
$data = json_decode(file_get_contents("php://input"));

// Validar que se recibieron todos los datos
if (isset($data->latitud) && isset($data->longitud)) {
    
    try {
        // Preparar la consulta SQL
        $sql = "INSERT INTO ubicaciones (latitud, longitud, descripcion) 
                VALUES (:latitud, :longitud, :descripcion)";
        
        $stmt = $conexion->prepare($sql);
        
        // Validar y limpiar datos
        $latitud = filter_var($data->latitud, FILTER_VALIDATE_FLOAT);
        $longitud = filter_var($data->longitud, FILTER_VALIDATE_FLOAT);
        $descripcion = isset($data->descripcion) ? htmlspecialchars($data->descripcion, ENT_QUOTES, 'UTF-8') : 'Sin descripción';
        
        if ($latitud === false || $longitud === false) {
            throw new Exception("Coordenadas inválidas");
        }
        
        // Bind de parámetros
        $stmt->bindParam(':latitud', $latitud);
        $stmt->bindParam(':longitud', $longitud);
        $stmt->bindParam(':descripcion', $descripcion);
        
        // Ejecutar
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Ubicación guardada exitosamente',
                'id' => $conexion->lastInsertId(),
                'data' => [
                    'latitud' => $latitud,
                    'longitud' => $longitud,
                    'descripcion' => $descripcion
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            throw new Exception("Error al guardar en la base de datos");
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos. Se requiere latitud y longitud.'
    ], JSON_UNESCAPED_UNICODE);
}
?>