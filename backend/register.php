<?php
// Incluir la conexión a PostgreSQL
include('db_connection.php');

// Parámetros de conexión con Supabase
$supabase_url = "https://sgmaaskbrllwuhnmgfeg.supabase.co";
$supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InNnbWFhc2ticmxsd3Vobm1nZmVnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzMxOTIwOTcsImV4cCI6MjA0ODc2ODA5N30.-iMegnHzxaVs0XoScVgm-GExTJdHgTKzjLZWfLyRmQI"; 

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir los datos del formulario
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validaciones básicas
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Error: Todos los campos son obligatorios.";
    }

    if ($password !== $confirm_password) {
        $error = "Error: Las contraseñas no coinciden.";
    }

    // Validación de la contraseña (mínimo 6 caracteres, por ejemplo)
    if (strlen($password) < 6) {
        $error = "Error: La contraseña debe tener al menos 6 caracteres.";
    }

    // Si hay errores, mostrar el mensaje
    if (isset($error)) {
        echo "<div class='alert alert-danger'>$error</div>";
    } else {
        // Encriptar la contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insertar los datos en PostgreSQL
        $query = "INSERT INTO clientes (first_name, last_name, email, password, created_at) VALUES ($1, $2, $3, $4, $5)";
        $result = pg_query_params($conn, $query, [$first_name, $last_name, $email, $hashed_password, date("Y-m-d H:i:s")]);

        if (!$result) {
            echo "<div class='alert alert-danger'>Error: No se pudo registrar al cliente en PostgreSQL.</div>";
        } else {
            // Insertar los mismos datos en Supabase
            $data = [
                "first_name" => $first_name,
                "last_name" => $last_name,
                "email" => $email,
                "password" => $hashed_password,
                "created_at" => date("Y-m-d H:i:s")
            ];

            // Configurar solicitud HTTP a la API de Supabase
            $options = [
                'http' => [
                    'header' => "Content-Type: application/json\r\nAuthorization: Bearer $supabase_key",
                    'method' => 'POST',
                    'content' => json_encode($data),
                ],
            ];

            // Crear el contexto de la solicitud
            $context = stream_context_create($options);

            // Enviar la solicitud a Supabase
            $result_supabase = file_get_contents("$supabase_url/rest/v1/clientes", false, $context);

            if ($result_supabase === FALSE) {
                echo "<div class='alert alert-danger'>Error: No se pudo registrar al cliente en Supabase.</div>";
            } else {
                // Verificar la respuesta de Supabase
                $response = json_decode($result_supabase, true);
                if (isset($response['message']) && $response['message'] !== "success") {
                    echo "<div class='alert alert-danger'>Error en la respuesta de Supabase: " . $response['message'] . "</div>";
                } else {
                    // Mensaje de éxito
                    echo "<div class='alert alert-success'>Cliente registrado con éxito en PostgreSQL y Supabase.</div>";
                    // Redirigir o mostrar una confirmación
                }
            }
        }
    }
}
?>
