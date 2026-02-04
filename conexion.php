<?php
// conexion.php - Adaptado para Geolocalizador (solo tabla de ubicaciones)

$host = 'localhost';
$usuario = 'root';
$password = ''; 
$base_datos = 'Geolocalizador'; // Nombre de tu base de datos existente

try {
    // Conectar directamente a la base de datos existente
    $conexion = new PDO("mysql:host=$host;dbname=$base_datos;charset=utf8mb4", $usuario, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Solo crear la tabla de ubicaciones para el geolocalizador
    $sql_crear_ubicaciones = "
        CREATE TABLE IF NOT EXISTS ubicaciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            latitud DECIMAL(10, 8) NOT NULL,
            longitud DECIMAL(11, 8) NOT NULL,
            descripcion VARCHAR(255) DEFAULT 'Sin descripción',
            fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_fecha (fecha_registro)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $conexion->exec($sql_crear_ubicaciones);
    
} catch (PDOException $e) {
    // Si se llama desde un endpoint JSON, no mostramos nada
    // Si se llama directamente, mostramos el error
    if (!defined('API_CALL')) {
        die("❌ Error de conexión o configuración: " . $e->getMessage());
    }
}
?>