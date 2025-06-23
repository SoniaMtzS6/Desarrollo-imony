<?php
include '../functions.php';
include 'config_settlements.php';

session_start();

// Validar sesión iniciada
if (!isset($_SESSION["usuario"])) {
    header("Location: ../index.php");
    exit;
}

// Validar segundo factor de autenticación
if ($_SESSION["usuario"]["doblefactor"] !== "1") {
    header("Location: ../authentication-two-steps.php");
    exit;
}

$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$idEmpresa = $_GET['id_empresa'] ?? (isset($_SESSION["usuario"]["id_empresa"]) ? $_SESSION["usuario"]["id_empresa"] : null);
if (!$idEmpresa) {
    die("ID de empresa requerido");
}

// Obtener cuentas de la empresa
$accounts = [];
$stmt = $conn->prepare("SELECT id_account FROM user WHERE id_empresa = ?");
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $accounts[] = $row["id_account"];
}
$stmt->close();

$noAccounts = count($accounts) === 0;

// Filtros de fecha
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

// Parámetros de paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$size = isset($_GET['size']) ? (int)$_GET['size'] : 10;

// Obtener datos de la empresa
$stmt = $conn->prepare("SELECT NOMBRE_EMPRESA FROM empresas WHERE ID_EMPRESA = ?");
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();
$empresa = $result->fetch_assoc();
$stmt->close();

$empresaNombre = $empresa ? $empresa['NOMBRE_EMPRESA'] : 'Empresa';

// Obtener datos usando la nueva API
$apiResponse = [];
if (!$noAccounts) {
    $apiResponse = getSettlementsReport($fecha_inicio, $fecha_fin, implode(',', $accounts), $page, $size);
}
$data = convertSettlementsResponse($apiResponse);

// Extraer datos y establecer valores por defecto para evitar warnings
$settlements = $data['settlements'] ?? [];
$transactions = $data['transactions'] ?? [];
$movimientos = $data['movimientos'] ?? [];
$totalSettlements = $data['totalSettlements'] ?? 0;
$totalTransactions = $data['totalTransactions'] ?? 0;
$totalMovements = $data['totalMovements'] ?? 0;
$summary = $data['summary'] ?? null;
$pagination = $data['pagination'] ?? null;
$error = $data['error'] ?? null;

// Información del entorno para debugging
$envInfo = getSettlementsEnvironmentInfo();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Movimientos vs Liquidaciones - <?php echo htmlspecialchars($empresaNombre); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table-responsive { max-height: 600px; overflow-y: auto; }
        .settlement-row { background-color: #e8f5e8; }
        .transaction-row { background-color: #fff3cd; }
        .summary-card { border-left: 4px solid #007bff; }
        .settlement-card { border-left: 4px solid #28a745; }
        .transaction-card { border-left: 4px solid #ffc107; }
        .export-buttons { position: sticky; top: 0; background: white; z-index: 1000; padding: 10px 0; border-bottom: 1px solid #dee2e6; }
        .env-badge { position: fixed; top: 10px; right: 10px; z-index: 9999; }
        .pagination-info { background-color: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php if ($envInfo['environment'] === 'local'): ?>
    <div class="env-badge">
        <span class="badge bg-warning text-dark">
            <i class="bi bi-code-slash"></i> LOCAL
        </span>
    </div>
    <?php endif; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">
                        <i class="bi bi-graph-up"></i>
                        Reporte de Movimientos vs Liquidaciones
                        <?php if ($envInfo['environment'] === 'local'): ?>
                            <small class="text-muted">(v2 - API)</small>
                        <?php endif; ?>
                    </h1>
                    <div>
                        <a href="../usuarios.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                    </div>
                </div>

                <!-- Información de la empresa -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-building"></i>
                            <?php echo htmlspecialchars($empresaNombre); ?>
                        </h5>
                        <p class="card-text">
                            <strong>Período:</strong> <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> - <?php echo date('d/m/Y', strtotime($fecha_fin)); ?>
                        </p>
                        
                        <?php if ($envInfo['environment'] === 'local'): ?>
                        <p class="card-text">
                            <strong>Entorno:</strong> 
                            <span class="badge bg-warning text-dark">
                                <?php echo strtoupper($envInfo['environment']); ?>
                            </span>
                            <small class="text-muted">(<?php echo $envInfo['base_url']; ?>)</small>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Manejo de errores -->
                <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Error:</strong> 
                    <?php 
                    if ($envInfo['environment'] === 'local') {
                        // Mensaje detallado para desarrollo
                        echo htmlspecialchars($error);
                    } else {
                        // Mensaje genérico para producción
                        echo "No se pudo generar el reporte en este momento. Por favor, intente más tarde o contacte a soporte.";
                    }
                    ?>
                </div>
                <?php endif; ?>

                <?php if ($noAccounts): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-info-circle"></i>
                    <strong>Aviso:</strong> No hay cuentas asociadas a esta empresa, por lo que no se puede generar el reporte.
                </div>
                <?php else: ?>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card summary-card">
                            <div class="card-body text-center">
                                <h5 class="card-title text-primary">
                                    <i class="bi bi-currency-dollar"></i>
                                    Total Movimientos
                                </h5>
                                <h3 class="text-primary">$<?php echo number_format($totalMovements, 2); ?></h3>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <?php echo count($movimientos); ?> registros
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card settlement-card">
                            <div class="card-body text-center">
                                <h5 class="card-title text-success">
                                    <i class="bi bi-check-circle"></i>
                                    Total Settlements
                                </h5>
                                <h3 class="text-success">$<?php echo number_format($totalSettlements, 2); ?></h3>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <?php echo count($settlements); ?> settlements
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card transaction-card">
                            <div class="card-body text-center">
                                <h5 class="card-title text-warning">
                                    <i class="bi bi-arrow-left-right"></i>
                                    Total Transacciones
                                </h5>
                                <h3 class="text-warning">$<?php echo number_format($totalTransactions, 2); ?></h3>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <?php echo count($transactions); ?> transacciones
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card <?php echo ($totalSettlements - $totalTransactions) >= 0 ? 'border-success' : 'border-danger'; ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title <?php echo ($totalSettlements - $totalTransactions) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <i class="bi bi-calculator"></i>
                                    Diferencia
                                </h5>
                                <h3 class="<?php echo ($totalSettlements - $totalTransactions) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    $<?php echo number_format($totalSettlements - $totalTransactions, 2); ?>
                                </h3>
                                <p class="card-text">
                                    <small class="text-muted">
                                        Settlements - Transacciones
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($pagination): ?>
                <div class="pagination-info">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Página <?php echo $pagination['page']; ?> de <?php echo $pagination['total_pages']; ?></strong>
                            <br>
                            <small class="text-muted">
                                Mostrando <?php echo count($movimientos); ?> de <?php echo $pagination['total']; ?> registros
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <?php if ($pagination['page'] > 1): ?>
                                <a href="?id_empresa=<?php echo $idEmpresa; ?>&fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>&page=<?php echo $pagination['page'] - 1; ?>&size=<?php echo $size; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-chevron-left"></i> Anterior
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                                <a href="?id_empresa=<?php echo $idEmpresa; ?>&fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>&page=<?php echo $pagination['page'] + 1; ?>&size=<?php echo $size; ?>" class="btn btn-sm btn-outline-primary">
                                    Siguiente <i class="bi bi-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="export-buttons">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>Movimientos</h5>
                        <div>
                            <a href="exportar_settlements.php?id_empresa=<?php echo $idEmpresa; ?>&fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>&formato=csv" class="btn btn-success btn-sm">
                                <i class="bi bi-file-earmark-csv"></i> CSV
                            </a>
                            <a href="exportar_settlements.php?id_empresa=<?php echo $idEmpresa; ?>&fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>&formato=excel" class="btn btn-success btn-sm">
                                <i class="bi bi-file-earmark-excel"></i> Excel
                            </a>
                            <!-- <a href="exportar_settlements.php?id_empresa=<?php echo $idEmpresa; ?>&fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>&formato=pdf" class="btn btn-danger btn-sm">
                                <i class="bi bi-file-earmark-pdf"></i> PDF -->
                            </a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>ID</th>
                                <th>Cuenta</th>
                                <th>Descripción</th>
                                <th>Monto</th>
                                <th>Moneda</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movimientos)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    <i class="bi bi-inbox"></i>
                                    No hay movimientos para mostrar
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($movimientos as $movimiento): ?>
                                <tr class="<?php echo $movimiento['type'] === 'settlement' ? 'settlement-row' : 'transaction-row'; ?>">
                                    <td>
                                        <i class="bi bi-calendar3"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($movimiento['created_at'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($movimiento['type'] === 'settlement'): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Settlement
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-arrow-left-right"></i> Transacción
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($movimiento['id']); ?></code>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($movimiento['account_id']); ?></code>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($movimiento['description'] ?: 'Sin descripción'); ?>
                                    </td>
                                    <td>
                                        <strong class="<?php echo $movimiento['amount'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            $<?php echo number_format($movimiento['amount'], 2); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($movimiento['currency']); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = 'bg-secondary';
                                        switch ($movimiento['status']) {
                                            case 'completed':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'pending':
                                                $statusClass = 'bg-warning';
                                                break;
                                            case 'failed':
                                                $statusClass = 'bg-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($movimiento['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($settlements) || !empty($transactions)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bar-chart"></i>
                            Comparativo Settlements vs Transacciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="comparisonChart" width="400" height="200"></canvas>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <?php if (!empty($settlements) || !empty($transactions)): ?>
    <script>
        const ctx = document.getElementById('comparisonChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Settlements', 'Transacciones', 'Diferencia'],
                datasets: [{
                    label: 'Monto ($)',
                    data: [
                        <?php echo $totalSettlements; ?>,
                        <?php echo $totalTransactions; ?>,
                        <?php echo $totalSettlements - $totalTransactions; ?>
                    ],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(0, 123, 255, 0.8)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(0, 123, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>