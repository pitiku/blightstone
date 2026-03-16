<?php // Versión de prueba 2

$host = 'gateway01.eu-central-1.prod.aws.tidbcloud.com';
$user = '2p7TUipr1WHHH3f.root';
$pass = '5ZcNOCkyQA9VGvfL';
$db   = 'BS';
$port = 4000;
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // 2. Crear la conexión
    $pdo = new PDO($dsn, $user, $pass, $options);

    // 3. Definir la tabla y la consulta (Cambia 'usuarios' por tu tabla real)
    $tabla = 'usuarios'; 
    $stmt = $pdo->query("select count(*) as total, exception, message, version
        from z_error
        group by exception, message, version
        order by total desc;");
    $filas = $stmt->fetchAll();

    // 4. Mostrar los datos en una tabla HTML
    if ($filas) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; font-family: sans-serif;'>";
        
        // Cabeceras automáticas basadas en las columnas de la tabla
        echo "<tr>";
        foreach (array_keys($filas[0]) as $columna) {
            echo "<th style='background: #f4f4f4;'>" . htmlspecialchars($columna) . "</th>";
        }
        echo "</tr>";

        // Datos de las filas
        foreach ($filas as $fila) {
            echo "<tr>";
            foreach ($fila as $valor) {
                echo "<td>" . htmlspecialchars($valor) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "La tabla está vacía o no tiene registros.";
    }

} catch (\PDOException $e) {
    // Si hay un error de conexión, lo mostramos
    echo "Error en la conexión: " . $e->getMessage();
}
?>
