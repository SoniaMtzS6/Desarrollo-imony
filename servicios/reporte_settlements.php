<?php
include '../functions.php';
include 'config_pomelo.php';

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

if (count($accounts) === 0) {
    die("No hay cuentas relacionadas con esta empresa.");
}

// Filtros de fecha
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01'); // Primer día del mes actual
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d'); // Hoy

// Validar credenciales de Pomelo
if (!validatePomeloCredentials()) {
    die(getPomeloErrorMessage("configurar credenciales"));
}

// Obtener token de Pomelo
$token = getPomeloToken();
if (!$token) {
    die(getPomeloErrorMessage("obtener token de acceso"));
}

// Obtener datos de la empresa
$stmt = $conn->prepare("SELECT NOMBRE_EMPRESA FROM empresas WHERE ID_EMPRESA = ?");
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();
$empresa = $result->fetch_assoc();
$stmt->close();

$empresaNombre = $empresa ? $empresa['NOMBRE_EMPRESA'] : 'Empresa';

// Arrays para almacenar datos
$settlements = [];
$transactions = [];
$movimientos = [];

// Obtener settlements y transacciones para cada cuenta
foreach ($accounts as $accountId) {
    // Obtener settlements
    $settlementsData = getPomeloSettlements($token, $accountId, $fecha_inicio, $fecha_fin);
    if ($settlementsData && isset($settlementsData['data'])) {
        foreach ($settlementsData['data'] as $settlement) {
            $settlements[] = array(
                'id' => $settlement['id'],
                'account_id' => $accountId,
                'amount' => $settlement['attributes']['amount'] ?? 0,
                'currency' => $settlement['attributes']['currency'] ?? 'MXN',
                'status' => $settlement['attributes']['status'] ?? 'unknown',
                'created_at' => $settlement['attributes']['created_at'] ?? '',
                'type' => 'settlement'
            );
        }
    }
    
    // Obtener transacciones
    $transactionsData = getPomeloTransactions($token, $accountId, $fecha_inicio, $fecha_fin);
    if ($transactionsData && isset($transactionsData['data'])) {
        foreach ($transactionsData['data'] as $transaction) {
            $transactions[] = array(
                'id' => $transaction['id'],
                'account_id' => $accountId,
                'amount' => $transaction['attributes']['amount'] ?? 0,
                'currency' => $transaction['attributes']['currency'] ?? 'MXN',
                'status' => $transaction['attributes']['status'] ?? 'unknown',
                'created_at' => $transaction['attributes']['created_at'] ?? '',
                'type' => 'transaction',
                'description' => $transaction['attributes']['description'] ?? ''
            );
        }
    }
}

// Combinar y ordenar todos los movimientos
$movimientos = array_merge($settlements, $transactions);
usort($movimientos, function($a, $b) {
    return strtotime($a['created_at']) - strtotime($b['created_at']);
});

// Calcular totales
$totalSettlements = 0;
$totalTransactions = 0;
$totalMovements = 0;

foreach ($settlements as $settlement) {
    $totalSettlements += $settlement['amount'];
}

foreach ($transactions as $transaction) {
    $totalTransactions += $transaction['amount'];
}

$totalMovements = $totalSettlements + $totalTransactions;

// Generar reporte
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
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
        .settlement-row {
            background-color: #e8f5e8;
        }
        .transaction-row {
            background-color: #fff3cd;
        }
        .summary-card {
            border-left: 4px solid #007bff;
        }
        .settlement-card {
            border-left: 4px solid #28a745;
        }
        .transaction-card {
            border-left: 4px solid #ffc107;
        }
        .export-buttons {
            position: sticky;
            top: 0;
            background: white;
            z-index: 1000;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">
                        <i class="bi bi-graph-up"></i>
                        Reporte de Movimientos vs Liquidaciones
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
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <input type="hidden" name="id_empresa" value="<?php echo $idEmpresa; ?>">
                            <div class="col-md-4">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                       value="<?php echo $fecha_inicio; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                       value="<?php echo $fecha_fin; ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Filtrar
                                    </button>
                                    <a href="?id_empresa=<?php echo $idEmpresa; ?>" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Resumen -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card summary-card">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-cash-stack"></i>
                                    Total Movimientos
                                </h6>
                                <h3 class="text-primary">$<?php echo number_format($totalMovements, 2); ?> MXN</h3>
                                <small class="text-muted"><?php echo count($movimientos); ?> registros</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card settlement-card">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-bank"></i>
                                    Total Liquidaciones
                                </h6>
                                <h3 class="text-success">$<?php echo number_format($totalSettlements, 2); ?> MXN</h3>
                                <small class="text-muted"><?php echo count($settlements); ?> registros</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card transaction-card">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-credit-card"></i>
                                    Total Transacciones
                                </h6>
                                <h3 class="text-warning">$<?php echo number_format($totalTransactions, 2); ?> MXN</h3>
                                <small class="text-muted"><?php echo count($transactions); ?> registros</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de exportación -->
                <?php if (!empty($movimientos)): ?>
                <div class="export-buttons mb-3">
                    <div class="d-flex gap-2">
                        <a href="exportar_settlements.php?id_empresa=<?php echo $idEmpresa; ?>&fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>&formato=csv" 
                           class="btn btn-success">
                            <i class="bi bi-file-earmark-text"></i> Exportar CSV
                        </a>
                        <a href="exportar_settlements.php?id_empresa=<?php echo $idEmpresa; ?>&fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>&formato=xls" 
                           class="btn btn-warning">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                        </a>
                        <a href="exportar_settlements.php?id_empresa=<?php echo $idEmpresa; ?>&fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?>&formato=pdf" 
                           class="btn btn-danger">
                            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tabla de movimientos -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-table"></i>
                            Detalle de Movimientos
                        </h5>
                    </div>
                    <div class="card-body">
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
                                                No hay movimientos para el período seleccionado
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($movimientos as $movimiento): ?>
                                            <tr class="<?php echo $movimiento['type'] === 'settlement' ? 'settlement-row' : 'transaction-row'; ?>">
                                                <td>
                                                    <?php echo date('d/m/Y H:i', strtotime($movimiento['created_at'])); ?>
                                                </td>
                                                <td>
                                                    <?php if ($movimiento['type'] === 'settlement'): ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-bank"></i> Liquidación
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-credit-card"></i> Transacción
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
                                                    <?php echo htmlspecialchars($movimiento['description'] ?? 'N/A'); ?>
                                                </td>
                                                <td class="fw-bold">
                                                    $<?php echo number_format($movimiento['amount'], 2); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($movimiento['currency']); ?>
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
                                                        <?php echo ucfirst($movimiento['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Gráfico comparativo -->
                <?php if (!empty($movimientos)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-bar-chart"></i>
                                Comparativo Mensual
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="comparisonChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <?php if (!empty($movimientos)): ?>
    <script>
        // Preparar datos para el gráfico
        const settlements = <?php echo json_encode($settlements); ?>;
        const transactions = <?php echo json_encode($transactions); ?>;
        
        // Agrupar por mes
        const monthlyData = {};
        
        settlements.forEach(item => {
            const month = new Date(item.created_at).toISOString().substr(0, 7);
            if (!monthlyData[month]) {
                monthlyData[month] = { settlements: 0, transactions: 0 };
            }
            monthlyData[month].settlements += parseFloat(item.amount);
        });
        
        transactions.forEach(item => {
            const month = new Date(item.created_at).toISOString().substr(0, 7);
            if (!monthlyData[month]) {
                monthlyData[month] = { settlements: 0, transactions: 0 };
            }
            monthlyData[month].transactions += parseFloat(item.amount);
        });
        
        const months = Object.keys(monthlyData).sort();
        const settlementData = months.map(month => monthlyData[month].settlements);
        const transactionData = months.map(month => monthlyData[month].transactions);
        
        // Crear gráfico
        const ctx = document.getElementById('comparisonChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months.map(month => {
                    const date = new Date(month + '-01');
                    return date.toLocaleDateString('es-ES', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Liquidaciones',
                    data: settlementData,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                }, {
                    label: 'Transacciones',
                    data: transactionData,
                    backgroundColor: 'rgba(255, 193, 7, 0.8)',
                    borderColor: 'rgba(255, 193, 7, 1)',
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
                                return '$' + value.toLocaleString('es-MX');
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.parsed.y.toLocaleString('es-MX');
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

<?php
$conn->close();
?> 