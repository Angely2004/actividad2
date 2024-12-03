<?php
/**
 * db_connection.php
 * Configuración para conectar con PostgreSQL
 * Creado por: Angely
 */

// Parámetros de conexión
$host = "localhost";        
$port = "5432";              
$dbname = "beta";           
$username = "postgres";      
$password = "25678";     

// Crear la cadena de conexión
$data_connection = "
host=$host 
port=$port 
dbname=$dbname 
user=$username 
password=$password";

// Intentar la conexión
$conn = pg_connect($data_connection);

// Verificar la conexión
if (!$conn) {
    die("Error: No se pudo conectar a la base de datos PostgreSQL. " . pg_last_error());
}

// Imprime este mensaje solo para pruebas, puedes comentarlo en producción
// echo "Conexión exitosa a la base de datos PostgreSQL.";
?>
