<?php // Versión de prueba 2

$host = 'gateway01.eu-central-1.prod.aws.tidbcloud.com';
$user = '2p7TUipr1WHHH3f.root';
$pass = '5ZcNOCkyQA9VGvfL';
$db   = 'BS';
$port = 4000;

try {
    $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => '', 
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);

    // 2. Obtener lista de todas las tablas para el Dropdown
    $tablas_query = $pdo->query("SHOW TABLES");
    $todas_las_tablas = $tablas_query->fetchAll(PDO::FETCH_COLUMN);

    // 3. Tabla seleccionada (por defecto la primera)
    $tabla_actual = $_GET['t'] ?? $todas_las_tablas[0];

    // Validar que la tabla existe (Seguridad)
    if (!in_array($tabla_actual, $todas_las_tablas)) { die("Tabla no válida"); }

    // 4. Consultar datos
    $stmt = $pdo->query("SELECT * FROM `$tabla_actual` LIMIT 1000"); // Limitamos por rendimiento
    $datos = $stmt->fetchAll();
    $columnas = !empty($datos) ? array_keys($datos[0]) : [];

    $metadatos = [];
for ($i = 0; $i < $stmt->columnCount(); $i++) {
    $meta = $stmt->getColumnMeta($i);
    $metadatos[$meta['name']] = $meta['native_type']; // 'BLOB', 'LONG_BLOB', etc.
}

// Suponemos que la primera columna es siempre el ID/Primary Key para las descargas
$pk_name = $columnas[0];

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<?php foreach ($fila as $col_name => $valor): ?>
    <td>
        <?php if (strpos($metadatos[$col_name], 'BLOB') !== false && !empty($valor)): ?>
            <a href="download.php?tabla=<?= $tabla_actual ?>&columna=<?= $col_name ?>&id_campo=<?= $pk_name ?>&id_valor=<?= $fila[$pk_name] ?>" 
               class="btn btn-sm btn-primary">
               📥 Descargar
            </a>
        <?php else: ?>
            <?= htmlspecialchars(substr($valor, 0, 50)) . (strlen($valor) > 50 ? '...' : '') ?>
        <?php endif; ?>
    </td>
<?php endforeach; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TiDB Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .card { border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        tfoot input { width: 100%; padding: 3px; box-sizing: border-box; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>TiDB Explorer</h2>
            
            <form method="GET" class="d-flex gap-2">
                <select name="t" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($todas_las_tablas as $t): ?>
                        <option value="<?= $t ?>" <?= $t == $tabla_actual ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="table-responsive">
            <table id="mainTable" class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <?php foreach ($columnas as $col): ?>
                            <th><?= htmlspecialchars($col) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($datos as $fila): ?>
                        <tr>
                            <?php foreach ($fila as $valor): ?>
                                <td><?= htmlspecialchars($valor ?? 'NULL') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <?php foreach ($columnas as $col): ?>
                            <th><input type="text" placeholder="Filtrar <?= htmlspecialchars($col) ?>" /></th>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Inicializar DataTables
    var table = $('#mainTable').DataTable({
        "pageLength": 10,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        }
    });

    // Aplicar los filtros por columna
    table.columns().every(function() {
        var that = this;
        $('input', this.footer()).on('keyup change clear', function() {
            if (that.search() !== this.value) {
                that.search(this.value).draw();
            }
        });
    });
});
</script>

</body>
</html>

