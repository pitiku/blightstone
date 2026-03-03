<?php // Versión de prueba 2

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Configuración de conexión (Sácalo de TiDB -> Connect)
$host = 'gateway01.eu-central-1.prod.aws.tidbcloud.com';
$user = '2p7TUipr1WHHH3f.root';
$pass = '5ZcNOCkyQA9VGvfL';
$db   = 'BS';
$port = 4000;

// 3. Conexión con SSL
$conn = mysqli_init();
// Usamos la ruta de certificados por defecto de Railway
mysqli_ssl_set($conn, NULL, NULL, "/etc/ssl/certs/ca-certificates.crt", NULL, NULL);

if (!mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL))
{
    die("Error de conexión: " . mysqli_connect_error());
}

// 4. Lógica para Unity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['query']))
{
    $query = $_POST['query'];
    
    $stmt = $conn->prepare($query);
    // $stmt = $conn->prepare("INSERT INTO 0_user (user_name, is_steam_name) VALUES ('ra', 1)");
    //$stmt->bind_param("si", $nombre, $puntos);

    if ($stmt->execute())
    {
        echo $conn->insert_id; // ESTO ES LO QUE RECIBE UNITY
    }
    else
    {
        echo "Error en ejecución: " . $stmt->error;
    }

    $stmt->close();
}
else
{
    // Si entras desde el navegador (GET), solo muestra un mensaje, NO inserta.
    echo "Servidor listo. Esperando datos por POST desde Unity.";
}

$conn->close();
?>





