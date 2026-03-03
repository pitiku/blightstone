<?php
// 1. Configuración de conexión (Sácalo de TiDB -> Connect)
$host = 'gateway01.tu-region.prod.aws.tidbcloud.com';
$user = 'tu_usuario.root';
$pass = 'tu_password';
$db   = 'test';
$port = 4000;

// 2. TiDB REQUIERE SSL (Seguridad)
// En Railway/Vercel no necesitas descargar el archivo .pem, 
// puedes usar la ruta del sistema que ya suelen tener.
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, "/etc/ssl/certs/ca-certificates.crt", NULL, NULL);

if (!mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión");
}

// 3. Recibir datos de Unity (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? 'SinNombre';
    $puntos = $_POST['puntos'] ?? 0;

    // 4. Insertar de forma segura
    $stmt = $conn->prepare("INSERT INTO jugadores (nombre, puntos) VALUES (?, ?)");
    $stmt->bind_param("si", $nombre, $puntos);

    if ($stmt->execute()) {
        // 5. ¡AQUÍ ESTÁ TU ID! Lo devolvemos a Unity
        echo $conn->insert_id; 
    } else {
        http_response_code(500);
        echo "Error al insertar";
    }
    $stmt->close();
}

$conn->close();
?>