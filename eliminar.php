<?php
define('API_CALL', true); // Evitar mensajes de echo en conexion.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir la conexión
require_once 'conexion.php';

// Obtener datos del POST
$data = json_decode(file_get_contents("php://input"));

// Validar que se recibió el ID
if (!empty($data->id)) {
    
    try {
        // Preparar la consulta SQL
        $sql = "DELETE FROM ubicaciones WHERE id = :id";
        
        $stmt = $conexion->prepare($sql);
        
        // Validar que el ID es un número
        $id = filter_var($data->id, FILTER_VALIDATE_INT);
        
        if ($id === false || $id <= 0) {
            throw new Exception("ID inválido");
        }
        
        // Bind de parámetros
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        // Ejecutar
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Ubicación eliminada exitosamente',
                    'id' => $id
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró la ubicación con ese ID'
                ], JSON_UNESCAPED_UNICODE);
            }
        } else {
            throw new Exception("Error al ejecutar la consulta");
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
        'message' => 'ID no proporcionado'
    ], JSON_UNESCAPED_UNICODE);
}
?>