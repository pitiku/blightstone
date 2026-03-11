<?php
// Conexión (usa tus variables de Railway)

$host = 'gateway01.eu-central-1.prod.aws.tidbcloud.com';
$user = '2p7TUipr1WHHH3f.root';
$pass = '5ZcNOCkyQA9VGvfL';
$db   = 'BS';
$dsn = "mysql:host=$host;dbname=$db;port=4000;charset=utf8mb4";

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => '', 
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $tabla = $_GET['tabla'];
    $columna = $_GET['columna'];
    $id_campo = $_GET['id_campo']; // Nombre de la columna clave primaria
    $id_valor = $_GET['id_valor'];

    // Consulta específica para el BLOB
    $stmt = $pdo->prepare("SELECT `$columna` FROM `$tabla` WHERE `$id_campo` = ?");
    $stmt->execute([$id_valor]);
    $file = $stmt->fetch();

    if ($file) {
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"archivo_{$columna}_{$id_valor}.bin\"");
        echo $file[$columna];
    }
} catch (Exception $e) { die("Error"); }
?>
