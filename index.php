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

    // 1. Listado de tablas para el Dropdown
    $tablas_query = $pdo->query("SHOW TABLES");
    $todas_las_tablas = $tablas_query->fetchAll(PDO::FETCH_COLUMN);
    $tabla_actual = $_GET['t'] ?? ($todas_las_tablas[0] ?? '');

    if (!$tabla_actual) die("No hay tablas en la base de datos.");

    // 2. Obtener datos (Limitado a 500 para no saturar memoria con BLOBs)
    $stmt = $pdo->query("SELECT * FROM `$tabla_actual` LIMIT 500");
    $datos = $stmt->fetchAll();
    
    // 3. Detectar METADATOS (Para identificar BLOBs)
    $es_blob = [];
    for ($i = 0; $i < $stmt->columnCount(); $i++) {
        $meta = $stmt->getColumnMeta($i);
        // En PHP/PDO, los tipos BLOB suelen reportarse como 'BLOB' o 'string' con flags
        // Verificamos si el nombre del tipo contiene 'BLOB'
        $es_blob[$meta['name']] = (isset($meta['native_type']) && strpos(strtoupper($meta['native_type']), 'BLOB') !== false);
    }
    
    $columnas = !empty($datos) ? array_keys($datos[0]) : [];
    $pk_name = $columnas[0] ?? 'id'; // Asumimos que la primera col es la ID

} catch (PDOException $e) {
    die("Error crítico: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TiDB Explorer Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-size: 0.9rem; }
        .main-card { background: white; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.05); padding: 25px; margin-top: 30px; }
        .blob-btn { --bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem; }
        tfoot input { width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container-fluid px-4">
    <div class="main-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-primary m-0">TiDB Manager <span class="badge bg-secondary fs-6"><?= $tabla_actual ?></span></h4>
            
            <form method="GET" class="d-flex align-items-center gap-2">
                <label class="text-muted small fw-bold">TABLA:</label>
                <select name="t" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 200px;">
                    <?php foreach ($todas_las_tablas as $t): ?>
                        <option value="<?= $t ?>" <?= $t == $tabla_actual ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <table id="tiTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <?php foreach ($columnas as $col): ?>
                        <th><?= htmlspecialchars($col) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($datos as $fila): ?>
                    <tr>
                        <?php foreach ($fila as $col_name => $valor): ?>
                            <td>
                                <?php if ($es_blob[$col_name] && !empty($valor)): ?>
                                    <a href="download.php?t=<?= urlencode($tabla_actual) ?>&c=<?= urlencode($col_name) ?>&pk=<?= urlencode($pk_name) ?>&id=<?= urlencode($fila[$pk_name]) ?>" 
                                       class="btn btn-outline-primary blob-btn">
                                        📥 Descargar File
                                    </a>
                                <?php else: ?>
                                    <span title="<?= htmlspecialchars($valor ?? '') ?>">
                                        <?= htmlspecialchars(strlen($valor ?? '') > 60 ? substr($valor, 0, 60) . '...' : ($valor ?? 'NULL')) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <?php foreach ($columnas as $col): ?>
                        <th><input type="text" placeholder="<?= $col ?>"></th>
                    <?php endforeach; ?>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#tiTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pageLength: 15,
        initComplete: function () {
            this
