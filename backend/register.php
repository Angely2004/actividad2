<?php
// Configuración para conectar con PostgreSQL
$host = "localhost";
$dbname = "actividad2";
$user = "postgres";
$password = "25678";

$conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Error: No se pudo conectar a la base de datos PostgreSQL.");
}

// Validar que se reciban los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Verificar que todos los campos estén completos
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        die("Error: Todos los campos son obligatorios.");
    }

    // Verificar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        die("Error: Las contraseñas no coinciden.");
    }

    // Encriptar la contraseña para mayor seguridad
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insertar los datos en PostgreSQL
    $query = "INSERT INTO clientes (first_name, last_name, email, password) VALUES ($1, $2, $3, $4)";
    $result = pg_query_params($conn, $query, [$first_name, $last_name, $email, $hashed_password]);
    if (!$result) {
        die("Error: No se pudo registrar al cliente en PostgreSQL.");
    }

    // Conexión con Supabase
    $supabase_url = "https://tusupabaseurl.supabase.co"; // Cambiar por tu URL de Supabase
    $supabase_key = "tu_clave_de_api"; // Cambiar por tu clave de API de Supabase

    $data = [
        "first_name" => $first_name,
        "last_name" => $last_name,
        "email" => $email,
        "password" => $hashed_password,
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\nAuthorization: Bearer $supabase_key",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context = stream_context_create($options);
    $result_supabase = file_get_contents("$supabase_url/rest/v1/clientes", false, $context);

    if ($result_supabase === FALSE) {
        die("Error: No se pudo registrar al cliente en Supabase.");
    }

    echo "Cliente registrado con éxito en PostgreSQL y Supabase.";
} else {
    die("Error: Método de solicitud no válido.");
}
?>
