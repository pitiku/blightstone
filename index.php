<?php // Versión de prueba 2

$host = 'gateway01.eu-central-1.prod.aws.tidbcloud.com';
$user = '2p7TUipr1WHHH3f.root';
$pass = '5ZcNOCkyQA9VGvfL';
$db   = 'Rift';
$port = 4000;
$tabla = 'Z_errors'; // Puedes cambiar esto por cualquier tabla de tu DB

$dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // ESTO ES LO QUE SOLUCIONA EL INSECURE TRANSPORT:
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // Evita errores de certificados auto-firmados
        PDO::MYSQL_ATTR_SSL_CA => '',                   // Activa el modo SSL en el driver
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES'");
    
    // 2. Ejecutar la consulta
    $stmt = $pdo->query("SELECT * FROM $tabla LIMIT 50");
    
    // 3. Obtener todos los datos en un array
    $resultados = $stmt->fetchAll();

    // Si la tabla está vacía, no hay nada que mostrar
    if (empty($resultados)) {
        die("La tabla '$tabla' está vacía o no existe.");
    }

    // 4. Extraer los nombres de las columnas de la primera fila
    $columnas = array_keys($resultados[0]);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        table { border-collapse: collapse; width: 90%; margin: 20px auto; font-family: sans-serif; }
        th { background-color: #007bff; color: white; text-transform: uppercase; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #ddd; }
    </style>
</head>
<body>

<h2 style="text-align:center;">Datos de la tabla: <?php echo htmlspecialchars($tabla); ?></h2>

<table>
    <thead>
        <tr>
            <?php foreach ($columnas as $columna): ?>
                <th><?php echo htmlspecialchars($columna); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($resultados as $fila): ?>
            <tr>
                <?php foreach ($fila as $valor): ?>
                    <td><?php echo htmlspecialchars($valor ?? 'NULL'); ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>

