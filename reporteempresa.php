<?php include 'functions.php'; ?>
<?php

session_start();

// Validar sesión iniciada
if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

// Validar segundo factor de autenticación
if ($_SESSION["usuario"]["doblefactor"] !== "1") {
    header("Location: authentication-two-steps.php");
    exit;
}

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://wq9cu31zl4.execute-api.us-east-2.amazonaws.com/Etapa1/fisinter_listarempresas-simples',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);

curl_close($curl);
//echo $response;

if ($response) {
  // Decodificar la respuesta JSON
    $data = json_decode($response, true); // `true` convierte JSON a array asociativo

  if (isset($data['data'][0])) {
      $empresa = '';

      if ($_SESSION["usuario"]["perfil"] === "Superadministrador") {
          foreach ($data['data'] as $user) {
              $idEmpresa = $user['idEmpresa'];
              $nombreEmpresa = $user['nombreEmpresa'];
              // $empresa .= '<option value="' . $idEmpresa . '">' . $nombreEmpresa . '</option>';
              if (isset($_SESSION["usuario"]["id_empresa"]) && $_SESSION["usuario"]["id_empresa"] == $idEmpresa) {
                $empresa .= '<option value="' . $idEmpresa . '" selected>' . $nombreEmpresa . '</option>';
              }else{
                $empresa .= '<option value="' . $idEmpresa . '">' . $nombreEmpresa . '</option>';
              }
          }
      } else {
          foreach ($data['data'] as $user) {
              $idEmpresa = $user['idEmpresa'];
              $nombreEmpresa = $user['nombreEmpresa'];

              if (isset($_SESSION["usuario"]["id_empresa"]) && $_SESSION["usuario"]["id_empresa"] == $idEmpresa) {
                  $empresa .= '<option value="' . $idEmpresa . '" selected>' . $nombreEmpresa . '</option>';
              }
          }
      }
  } else {
      echo "No se encontraron datos de usuario.";
  }
} else {
  echo "Error en la solicitud CURL.";
}


// 
// 
// 
// 

// $conn = getDbConnection();
// if ($conn->connect_error) {
//     die("Error de conexión a la base de datos: " . $conn->connect_error);
// }

// $idempresa = $_GET['idempresa'] ?? $_SESSION["usuario"]["id_empresa"];
// $empresa = null;

// if ($idempresa) {
//     $stmt = $conn->prepare("SELECT * FROM empresas WHERE ID_EMPRESA = ?");
//     $stmt->bind_param("i", $idempresa);
//     $stmt->execute();
//     $resultado = $stmt->get_result();

//     if ($resultado->num_rows === 1) {
//         $empresa = $resultado->fetch_assoc();
//     }
//     $stmt->close();
// }

// $conn->close();








$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$idEmpresa = $_GET['id_empresa'] ?? (isset($_SESSION["usuario"]["id_empresa"]) ? $_SESSION["usuario"]["id_empresa"] : null);
if (!$idEmpresa) {
    echo '<div style="color:red; font-weight:bold; margin:2em;">No se encontró el ID de empresa en la sesión ni en la URL.<br>Por favor, inicia sesión correctamente o selecciona una empresa.</div>';
    echo '<a href="index.php" style="display:inline-block; margin:1em; padding:0.5em 1em; background:#007bff; color:#fff; border-radius:5px; text-decoration:none;">Ir al inicio</a>';
    // Depuración: mostrar la sesión
    echo '<pre style="background:#eee; padding:1em;">';
    print_r($_SESSION);
    echo '</pre>';
    exit;
}

$saldoInicial = 0;
$depositos = 0;
$asignaciones = 0;
$retiros = 0;
$retiroscompras = 0;
$retiroscomprassolo = 0;
$mostrar = '';

// Datos de empresa
$stmt = $conn->prepare("SELECT NOMBRE_EMPRESA, MONTO_MAXIMO, NUMERO_TARJETAS FROM empresas WHERE ID_EMPRESA = ?");
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $empresaNombre = $row["NOMBRE_EMPRESA"];
    $numeroTarjetas = $row["NUMERO_TARJETAS"];
    $montoMaximoOriginal = $row["MONTO_MAXIMO"]; // Almacenar para uso posterior
} else {
    die("Empresa no encontrada.");
}
$stmt->close();

// Obtener el saldo real desde empresas_movimientos
$stmt_mov = $conn->prepare("SELECT total_monto FROM empresas_movimientos WHERE id_empresa = ? ORDER BY fecha_movimiento DESC, id DESC LIMIT 1");
$stmt_mov->bind_param("i", $idEmpresa);
$stmt_mov->execute();
$result_mov = $stmt_mov->get_result();
if ($result_mov->num_rows > 0) {
    $row_mov = $result_mov->fetch_assoc();
    $saldoInicial = $row_mov["total_monto"];
} else {
    // Si no hay movimientos, usar el MONTO_MAXIMO como respaldo
    $saldoInicial = isset($montoMaximoOriginal) ? $montoMaximoOriginal : 0;
}
$stmt_mov->close();

// Administrador
$sqlAdmin = "SELECT nombre FROM administradores WHERE idEmpresa = ? ORDER BY id ASC LIMIT 1";
$stmt = $conn->prepare($sqlAdmin);
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$stmt->bind_result($adminNombre);
$stmt->fetch();
$stmt->close();

// Fechas
$periodoMes = date("F Y");
$fechaHoy = date("d/m/Y");
$counttarjetasusadas=0;

// Cuentas relacionadas
$accounts = [];
$stmt = $conn->prepare("SELECT id_account FROM user WHERE id_empresa = ?");
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $accounts[] = "'" . $row["id_account"] . "'";
    ++$counttarjetasusadas;
}
$stmt->close();

if (count($accounts) === 0) {
    die("No hay cuentas relacionadas.");
}
$accountsIn = implode(",", $accounts);

// ================== FILTROS DE FECHA (NUEVO CÓDIGO LOCAL) ==================
// Formulario de filtros antes de la tabla
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// ================== FILTRO DE EMPRESA PARA BALANCE DE CUENTA ==================
$empresa_filtro = isset($_GET['empresa_filtro']) ? $_GET['empresa_filtro'] : $idEmpresa;

// Si se selecciona una empresa diferente, actualizar el ID de empresa para los cálculos
if ($empresa_filtro != $idEmpresa) {
    $idEmpresa = $empresa_filtro;
    
    // Recalcular datos de empresa con la nueva selección
    $stmt = $conn->prepare("SELECT NOMBRE_EMPRESA, MONTO_MAXIMO, NUMERO_TARJETAS FROM empresas WHERE ID_EMPRESA = ?");
    $stmt->bind_param("i", $idEmpresa);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $empresaNombre = $row["NOMBRE_EMPRESA"];
        $numeroTarjetas = $row["NUMERO_TARJETAS"];
        $montoMaximoOriginal = $row["MONTO_MAXIMO"]; // Almacenar para uso posterior
    } else {
        die("Empresa no encontrada.");
    }
    $stmt->close();
    
    // Obtener el saldo real desde empresas_movimientos para la nueva empresa
    $stmt_mov = $conn->prepare("SELECT total_monto FROM empresas_movimientos WHERE id_empresa = ? ORDER BY fecha_movimiento DESC, id DESC LIMIT 1");
    $stmt_mov->bind_param("i", $idEmpresa);
    $stmt_mov->execute();
    $result_mov = $stmt_mov->get_result();
    if ($result_mov->num_rows > 0) {
        $row_mov = $result_mov->fetch_assoc();
        $saldoInicial = $row_mov["total_monto"];
    } else {
        // Si no hay movimientos, usar el MONTO_MAXIMO como respaldo
        $saldoInicial = isset($montoMaximoOriginal) ? $montoMaximoOriginal : 0;
    }
    $stmt_mov->close();
    
    // Recalcular administrador
    $sqlAdmin = "SELECT nombre FROM administradores WHERE idEmpresa = ? ORDER BY id ASC LIMIT 1";
    $stmt = $conn->prepare($sqlAdmin);
    $stmt->bind_param("i", $idEmpresa);
    $stmt->execute();
    $stmt->bind_result($adminNombre);
    $stmt->fetch();
    $stmt->close();
    
    // Recalcular cuentas relacionadas
    $accounts = [];
    $stmt = $conn->prepare("SELECT id_account FROM user WHERE id_empresa = ?");
    $stmt->bind_param("i", $idEmpresa);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $accounts[] = "'" . $row["id_account"] . "'";
        ++$counttarjetasusadas;
    }
    $stmt->close();
    
    if (count($accounts) === 0) {
        die("No hay cuentas relacionadas para la empresa seleccionada.");
    }
    $accountsIn = implode(",", $accounts);
    
    // Reinicializar variables para recalcular movimientos
    $saldoInicial = 0;
    $depositos = 0;
    $asignaciones = 0;
    $retiros = 0;
    $retiroscompras = 0;
    $retiroscomprassolo = 0;
}

// ================== CONSULTA DE MOVIMIENTOS (NUEVO CÓDIGO LOCAL) ==================
// Comentar la consulta de producción y dejar la nueva con filtro de fechas
/*
// CONSULTA DE PRODUCCIÓN (COMENTADA)
$sqlMovimientos = "
    SELECT entry_type, account_id, result, process_type, type, created_at, total_amount as monto
    FROM activity
    WHERE account_id IN ($accountsIn) ORDER BY created_at ASC
";
*/
// CONSULTA LOCAL CON FILTRO DE FECHAS
$sqlMovimientos = "
    SELECT entry_type, account_id, result, process_type, type, created_at, total_amount as monto
    FROM activity
    WHERE account_id IN ($accountsIn)";
if ($fecha_inicio && $fecha_fin) {
    $sqlMovimientos .= " AND DATE(created_at) BETWEEN '" . $conn->real_escape_string($fecha_inicio) . "' AND '" . $conn->real_escape_string($fecha_fin) . "'";
} elseif ($fecha_inicio) {
    $sqlMovimientos .= " AND DATE(created_at) >= '" . $conn->real_escape_string($fecha_inicio) . "'";
} elseif ($fecha_fin) {
    $sqlMovimientos .= " AND DATE(created_at) <= '" . $conn->real_escape_string($fecha_fin) . "'";
}
$sqlMovimientos .= " ORDER BY created_at ASC";

//echo $sqlMovimientos;
$result = $conn->query($sqlMovimientos);
$saldoInicialt=0;
$tsaldo="";

// Reinicializar variables para el cálculo de movimientos
$depositos_act = 0;
$asignaciones_act = 0;
$retiros_act = 0;
$retiroscompras_act = 0;

while ($row = $result->fetch_assoc()) {
    $entry = $row["entry_type"];
    $process = $row["process_type"];
    $type = $row["type"];
    $status = $row["result"];
    $fecha = date("Y-m-d", strtotime($row["created_at"]));
    $monto = floatval($row["monto"]);

    if ($entry === "CREDIT" && $process === "ORIGINAL" && $status == "APPROVED") {
      $depositos_act += $monto;
      $depositosolo = $monto;
  } elseif ($entry === "CREDIT" && $process !== "ORIGINAL" && $status == "APPROVED") {
      $asignaciones_act += $monto;
  } elseif ($entry === "DEBIT" && $type === "MANUAL_MOVEMENT" && $status == "APPROVED") {
      $retiros_act += $monto;
      $retirossolo = $monto;
  } elseif ($entry === "DEBIT" && $type !== "MANUAL_MOVEMENT" && $status == "APPROVED") {
      $retiroscompras_act += $monto;
      $retiroscomprassolo = $monto;
  }

    //$saldoinit = $saldoInicialt - $asignaciones - $retiros;
    $saldoinit = $depositos_act - $asignaciones_act - $retiros_act - $retiroscompras_act;

    // FILTRO POR COLUMNA (inline)
    $mostrar_fila = true;
    if (isset($_GET['saldo_inicial']) && $_GET['saldo_inicial'] !== '' && stripos((string)$saldoInicialt, $_GET['saldo_inicial']) === false) $mostrar_fila = false;
    if (isset($_GET['asignaciones']) && $_GET['asignaciones'] !== '' && stripos((string)$asignaciones_act, $_GET['asignaciones']) === false) $mostrar_fila = false;
    if (isset($_GET['gasto']) && $_GET['gasto'] !== '' && stripos((string)$retirossolo, $_GET['gasto']) === false) $mostrar_fila = false;
    if (isset($_GET['retiro_saldo']) && $_GET['retiro_saldo'] !== '' && stripos((string)$retirossolo, $_GET['retiro_saldo']) === false) $mostrar_fila = false;
    if (isset($_GET['retiro_compra']) && $_GET['retiro_compra'] !== '' && stripos((string)$retiroscomprassolo, $_GET['retiro_compra']) === false) $mostrar_fila = false;
    if (isset($_GET['saldo_disponible']) && $_GET['saldo_disponible'] !== '' && stripos((string)$saldoinit, $_GET['saldo_disponible']) === false) $mostrar_fila = false;
    if (isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' && $fecha < $_GET['fecha_inicio']) $mostrar_fila = false;
    if (isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' && $fecha > $_GET['fecha_fin']) $mostrar_fila = false;

    if ($mostrar_fila) {
        $tsaldo .= "<tr>";
        $tsaldo .= "<td>$".number_format($saldoInicialt)."</td>";
        $tsaldo .= "<td>$".number_format($depositosolo)."</td>";
        $tsaldo .= "<td>$".number_format($asignaciones_act)."</td>";
        $tsaldo .= "<td>$".number_format($retirossolo)."</td>";
        $tsaldo .= "<td>$".number_format($retiroscomprassolo)."</td>";
        $tsaldo .= "<td>$".number_format($saldoinit)."</td>";
        $tsaldo .= "<td>".$fecha."</td>";
        $tsaldo .= "<tr>";
    }

    $saldoInicialt = $saldoinit;
    $depositosolo =0;
    $retirossolo=0;
    $retiroscomprassolo=0;
}

//$saldoInicial = $depositos;
$saldoDisponible = $depositos_act - $asignaciones_act - $retiros_act - $retiroscompras_act;

echo $mostrar;

// === BLOQUE MEJORADO (NUEVA LÓGICA PARA LOCAL Y PRODUCCIÓN) ===
// Este bloque implementa la nueva estructura de columnas y cálculos para la sección "Balance de Cuenta"
// tanto para entorno local como para producción (si se desea migrar la lógica).
// Se basa únicamente en la base de datos local y sigue las definiciones de negocio proporcionadas.

// 1. Cálculo de Cuenta Concentradora
$depositosCC = 0;
$retirosCC = 0;
$saldoInicialCC = 0;

// Obtener Saldo Inicial de la Cuenta Concentradora
$sqlSaldoInicial = "SELECT total_monto FROM empresas_movimientos WHERE id_empresa = ?";
if ($fecha_inicio) {
    $sqlSaldoInicial .= " AND fecha_movimiento < '" . $conn->real_escape_string($fecha_inicio) . "'";
}
$sqlSaldoInicial .= " ORDER BY fecha_movimiento DESC, id DESC LIMIT 1";

$stmtSaldo = $conn->prepare($sqlSaldoInicial);
$stmtSaldo->bind_param("i", $idEmpresa);
$stmtSaldo->execute();
$resultSaldo = $stmtSaldo->get_result();
if ($rowSaldo = $resultSaldo->fetch_assoc()) {
    $saldoInicialCC = floatval($rowSaldo['total_monto']);
}
$stmtSaldo->close();

// Obtener movimientos de la empresa (depósitos y retiros) dentro del período
$sqlMovimientosEmpresa = "SELECT monto_agregado, tipo_movimiento FROM empresas_movimientos WHERE id_empresa = ?";
$date_conditions = [];
if ($fecha_inicio) $date_conditions[] = "fecha_movimiento >= '" . $conn->real_escape_string($fecha_inicio) . "'";
if ($fecha_fin)   $date_conditions[] = "fecha_movimiento <= '" . $conn->real_escape_string($fecha_fin) . "'";
if (count($date_conditions) > 0) {
    $sqlMovimientosEmpresa .= " AND " . implode(' AND ', $date_conditions);
}

$stmtMovimientos = $conn->prepare($sqlMovimientosEmpresa);
$stmtMovimientos->bind_param("i", $idEmpresa);
$stmtMovimientos->execute();
$resultMovimientos = $stmtMovimientos->get_result();
while ($rowMov = $resultMovimientos->fetch_assoc()) {
    if ($rowMov['tipo_movimiento'] == 'Asignacion') {
        $depositosCC += floatval($rowMov['monto_agregado']);
    } elseif ($rowMov['tipo_movimiento'] == 'Retiro') {
        // Los retiros ya vienen con signo negativo desde la BBDD
        $retirosCC += floatval($rowMov['monto_agregado']);
    }
}
$stmtMovimientos->close();

// Las "Asignaciones a Tarjetas" se obtienen de la tabla activity
$asignacionesCC = $asignaciones_act;

// Cálculo del saldo disponible
$saldoDisponibleCC = $saldoInicialCC + $depositosCC + $retirosCC - $asignacionesCC;

// 2. Cálculo de Tarjetas (por cada tarjeta de la empresa)
$usuariosEmpresa = [];
$stmt = $conn->prepare("SELECT id_ FROM user WHERE id_empresa = ?");
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$resUsuarios = $stmt->get_result();
while ($row = $resUsuarios->fetch_assoc()) {
    $usuariosEmpresa[] = $row['id_'];
}
$stmt->close();

$tarjetas = [];
$totalAsignacionesTarjetas = 0;
$totalCargosTarjetas = 0;
$totalRetirosTarjetas = 0;
$totalSaldoTarjetas = 0;

if (count($usuariosEmpresa) > 0) {
    $listaUsuarios = implode(",", array_map('intval', $usuariosEmpresa));
    $sqlTarjetas = "SELECT t.id, t.card_id, t.last_four, t.status, t.provider, t.affinity_group_name, t.start_date, u.name as NOMBRE_USUARIO
                    FROM tarjetas_local t
                    LEFT JOIN user u ON t.user_id = u.id_
                    WHERE t.user_id IN ($listaUsuarios)";
    $resultTarjetas = $conn->query($sqlTarjetas);
    while ($row = $resultTarjetas->fetch_assoc()) {
        $tarjetas[] = [
            'id' => $row['id'],
            'card_id' => $row['card_id'],
            'last_four' => $row['last_four'],
            'status' => $row['status'],
            'provider' => $row['provider'],
            'affinity_group_name' => $row['affinity_group_name'],
            'start_date' => $row['start_date'],
            'usuario' => $row['NOMBRE_USUARIO']
        ];
    }
}

// Mostrar encabezados de sección y datos generales
$mostrar = '<h2 style="font-weight:bold;">Sección "Balance de Cuenta"</h2>';

// ================== FORMULARIO DE FILTRO DE EMPRESA ==================
$mostrar .= '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #dee2e6;">';
$mostrar .= '<h4 style="margin-bottom: 15px; color: #156082;">Filtro de Empresa</h4>';
$mostrar .= '<form method="get" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">';

// Mantener los filtros de fecha existentes
$mostrar .= '<div style="display: flex; align-items: center; gap: 10px;">';
$mostrar .= '<label style="font-weight: bold; min-width: 80px;">Empresa:</label>';
$mostrar .= '<select name="empresa_filtro" style="padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; min-width: 200px;">';

// Obtener todas las empresas disponibles
$stmt = $conn->prepare("SELECT ID_EMPRESA, NOMBRE_EMPRESA FROM empresas ORDER BY NOMBRE_EMPRESA");
$stmt->execute();
$resultEmpresas = $stmt->get_result();

while ($row = $resultEmpresas->fetch_assoc()) {
    $selected = ($row['ID_EMPRESA'] == $empresa_filtro) ? 'selected' : '';
    $mostrar .= '<option value="' . $row['ID_EMPRESA'] . '" ' . $selected . '>' . htmlspecialchars($row['NOMBRE_EMPRESA']) . '</option>';
}
$stmt->close();

$mostrar .= '</select>';
$mostrar .= '</div>';

// Mantener filtros de fecha existentes
$mostrar .= '<div style="display: flex; align-items: center; gap: 10px;">';
$mostrar .= '<label style="font-weight: bold; min-width: 80px;">Desde:</label>';
$mostrar .= '<input type="date" name="fecha_inicio" value="' . htmlspecialchars($fecha_inicio) . '" style="padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px;">';
$mostrar .= '</div>';

$mostrar .= '<div style="display: flex; align-items: center; gap: 10px;">';
$mostrar .= '<label style="font-weight: bold; min-width: 80px;">Hasta:</label>';
$mostrar .= '<input type="date" name="fecha_fin" value="' . htmlspecialchars($fecha_fin) . '" style="padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px;">';
$mostrar .= '</div>';

// Mantener el ID de empresa original si existe
if (isset($_GET['id_empresa'])) {
    $mostrar .= '<input type="hidden" name="id_empresa" value="' . htmlspecialchars($_GET['id_empresa']) . '">';
}

$mostrar .= '<button type="submit" class="btn btn-primary" style="padding: 8px 16px; background-color: #156082; border: none; color: white; border-radius: 4px; cursor: pointer;">';
$mostrar .= '<i class="fas fa-search" style="margin-right: 5px;"></i>Filtrar';
$mostrar .= '</button>';

$mostrar .= '<a href="reporteempresa.php" class="btn btn-secondary" style="padding: 8px 16px; background-color: #6c757d; border: none; color: white; border-radius: 4px; text-decoration: none; margin-left: 10px;">';
$mostrar .= '<i class="fas fa-times" style="margin-right: 5px;"></i>Limpiar';
$mostrar .= '</a>';

$mostrar .= '</form>';
$mostrar .= '</div>';

// Mostrar información de la empresa seleccionada
$mostrar .= '<div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #156082;">';
$mostrar .= '<h4 style="margin: 0 0 10px 0; color: #156082;">Información de la Empresa</h4>';
$mostrar .= '<ul style="margin: 0; padding-left: 20px;">';
$mostrar .= '<li><b>Nombre Empresa:</b> ' . htmlspecialchars($empresaNombre) . '</li>';
$mostrar .= '<li><b>Nombre Administrador:</b> ' . htmlspecialchars($adminNombre) . '</li>';
$mostrar .= '<li><b>Periodo (Mes):</b> ' . $periodoMes . '</li>';
$mostrar .= '<li><b>Fecha:</b> ' . $fechaHoy . '</li>';
$mostrar .= '<li><b>Número de Tarjetas:</b> ' . $numeroTarjetas . '</li>';
$mostrar .= '<li><b>Tarjetas en Uso:</b> ' . $counttarjetasusadas . '</li>';

// Obtener el monto máximo configurado para comparación
$stmt = $conn->prepare("SELECT MONTO_MAXIMO FROM empresas WHERE ID_EMPRESA = ?");
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $montoMaximo = $row["MONTO_MAXIMO"];
    $mostrar .= '<li><b>Monto Máximo Configurado:</b> $' . number_format($montoMaximo, 2) . '</li>';
    $mostrar .= '<li><b>Saldo Real Actual:</b> $' . number_format($saldoInicial, 2) . '</li>';
    
    // Mostrar diferencia si existe
    $diferencia = $saldoInicial - $montoMaximo;
    if ($diferencia != 0) {
        $colorDiferencia = $diferencia > 0 ? '#28a745' : '#dc3545';
        $mostrar .= '<li><b>Diferencia:</b> <span style="color: ' . $colorDiferencia . '; font-weight: bold;">$' . number_format($diferencia, 2) . '</span></li>';
    }
}
$stmt->close();

$mostrar .= '</ul>';
$mostrar .= '</div>';

// === TABLA CUENTA CONCENTRADORA ===
$mostrar .= '<h3>Cuenta Concentradora</h3>';
$mostrar .= '<table class="table table-bordered">';
$mostrar .= '<thead><tr>';
$mostrar .= '<th style="background-color: #156082; color: white;">Saldo Inicial</th>';
$mostrar .= '<th style="background-color: #156082; color: white;">Depósitos</th>';
$mostrar .= '<th style="background-color: #156082; color: white;">Asignaciones a Tarjetas</th>';
$mostrar .= '<th style="background-color: #156082; color: white;">Retiros A Cuenta Concentradora</th>';
$mostrar .= '<th style="background-color: #156082; color: white;">Saldo Disponible para Asignación</th>';
$mostrar .= '</tr></thead>';
$mostrar .= "<tbody style='background:#eaf6fa;color:#222;font-weight:normal;'>";
$mostrar .= "<tr>";
$mostrar .= "<td>$".number_format($saldoInicialCC, 2)."</td>";
$mostrar .= "<td>$".number_format($depositosCC, 2)."</td>";
$mostrar .= "<td>$".number_format($asignacionesCC, 2)."</td>";
$mostrar .= "<td>$".number_format(abs($retirosCC), 2)."</td>";
$mostrar .= "<td>$".number_format($saldoDisponibleCC, 2)."</td>";
$mostrar .= "</tr></tbody></table>";

// === HISTORIAL DE MOVIMIENTOS DE EMPRESA ===
/* $mostrar .= '<h4 style="margin-top: 20px; color: #156082;">Historial de Movimientos de Cuenta Concentradora</h4>';
$mostrar .= '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6; margin-bottom: 20px;">';

// Obtener historial de movimientos
$stmt = $conn->prepare("
    SELECT monto_agregado, total_monto, fecha_movimiento, tipo_movimiento 
    FROM empresas_movimientos 
    WHERE id_empresa = ? 
    ORDER BY fecha_movimiento DESC, id DESC 
    LIMIT 10
");
$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $mostrar .= '<table class="table table-bordered" style="margin-bottom: 0;">';
    $mostrar .= '<thead><tr>';
    $mostrar .= '<th style="background-color: #6c757d; color: white;">Fecha</th>';
    $mostrar .= '<th style="background-color: #6c757d; color: white;">Tipo</th>';
    $mostrar .= '<th style="background-color: #6c757d; color: white;">Monto Agregado</th>';
    $mostrar .= '<th style="background-color: #6c757d; color: white;">Total Acumulado</th>';
    $mostrar .= '</tr></thead>';
    $mostrar .= '<tbody style="background:#ffffff;color:#222;font-weight:normal;">';
    
    while ($row = $result->fetch_assoc()) {
        $colorTipo = $row['tipo_movimiento'] == 'Asignacion' ? '#28a745' : '#dc3545';
        $mostrar .= "<tr>";
        $mostrar .= "<td>" . htmlspecialchars($row['fecha_movimiento']) . "</td>";
        $mostrar .= "<td style='color: " . $colorTipo . "; font-weight: bold;'>" . htmlspecialchars($row['tipo_movimiento']) . "</td>";
        $mostrar .= "<td>$" . number_format($row['monto_agregado'], 2) . "</td>";
        $mostrar .= "<td style='font-weight: bold;'>$" . number_format($row['total_monto'], 2) . "</td>";
        $mostrar .= "</tr>";
    }
    
    $mostrar .= '</tbody></table>';
} else {
    $mostrar .= '<div style="text-align: center; padding: 20px; color: #6c757d; font-style: italic;">';
    $mostrar .= 'No hay movimientos registrados para esta empresa.';
    $mostrar .= '</div>';
}

$stmt->close();
$mostrar .= '</div>'; */

// === TABLA TARJETAS ===
$mostrar .= '<h3>Tarjetas</h3>';
$mostrar .= '<table class="table table-bordered">';
$mostrar .= '<thead><tr>';
$mostrar .= '<th style="background-color: #156082; color: white;">Usuario</th>';
$mostrar .= '<th style="background-color: #156082; color: white;">Últimos 4 Dígitos</th>';
$mostrar .= '<th style="background-color: #156082; color: white;">Estado</th>';
$mostrar .= '<th style="background-color: #156082; color: white;">Proveedor</th>';
$mostrar .= '<th style="background-color: #156082; color: white;">Fecha de Inicio</th>';
$mostrar .= '<th style="background-color: #156082; color: white;">Saldo Inicial</th>';
$mostrar .= '<th style="background-color: #156082; color: white;">Asignaciones</th>';
$mostrar .= '<th style="background-color: #156082; color: white;">Cargos / Retiros</th>';
$mostrar .= '<th style="background-color: #156082; color: white;">Retiros a Cuenta Concentradora</th>';
$mostrar .= '<th style="background-color: #156082; color: white;">Saldo Disponible</th>';
$mostrar .= '</tr></thead><tbody style="background:#eaf6fa;color:#222;font-weight:normal;">';

if (count($tarjetas) > 0) {
    foreach ($tarjetas as $t) {
        // Calcular valores por tarjeta (esto se puede mejorar con consultas específicas)
        $saldoInicialTarjeta = 0; // Se puede calcular desde el historial
        $asignacionesTarjeta = 0; // Se puede calcular desde activity
        $cargosTarjeta = 0; // Se puede calcular desde activity
        $retirosTarjeta = 0; // Se puede calcular desde activity
        $saldoDisponibleTarjeta = $saldoInicialTarjeta + $asignacionesTarjeta - $cargosTarjeta - $retirosTarjeta;
        
        // Acumular totales
        $totalAsignacionesTarjetas += $asignacionesTarjeta;
        $totalCargosTarjetas += $cargosTarjeta;
        $totalRetirosTarjetas += $retirosTarjeta;
        $totalSaldoTarjetas += $saldoDisponibleTarjeta;
        
        $mostrar .= "<tr>";
        $mostrar .= "<td>" . htmlspecialchars($t['usuario'] ?? 'N/A') . "</td>";
        $mostrar .= "<td>" . htmlspecialchars($t['last_four'] ?? 'N/A') . "</td>";
        $mostrar .= "<td>" . htmlspecialchars($t['status'] ?? 'N/A') . "</td>";
        $mostrar .= "<td>" . htmlspecialchars($t['provider'] ?? 'N/A') . "</td>";
        $mostrar .= "<td>" . htmlspecialchars($t['start_date'] ?? 'N/A') . "</td>";
        $mostrar .= "<td>$" . number_format($saldoInicialTarjeta, 2) . "</td>";
        $mostrar .= "<td>$" . number_format($asignacionesTarjeta, 2) . "</td>";
        $mostrar .= "<td>$" . number_format($cargosTarjeta, 2) . "</td>";
        $mostrar .= "<td>$" . number_format($retirosTarjeta, 2) . "</td>";
        $mostrar .= "<td>$" . number_format($saldoDisponibleTarjeta, 2) . "</td>";
        $mostrar .= "</tr>";
    }
    
    // Fila de totales
    $mostrar .= "<tr style='background-color: #156082; color: white; font-weight: bold;'>";
    $mostrar .= "<td colspan='5'><strong>TOTALES</strong></td>";
    $mostrar .= "<td>$" . number_format(0, 2) . "</td>"; // Total saldo inicial
    $mostrar .= "<td>$" . number_format($totalAsignacionesTarjetas, 2) . "</td>";
    $mostrar .= "<td>$" . number_format($totalCargosTarjetas, 2) . "</td>";
    $mostrar .= "<td>$" . number_format($totalRetirosTarjetas, 2) . "</td>";
    $mostrar .= "<td>$" . number_format($totalSaldoTarjetas, 2) . "</td>";
    $mostrar .= "</tr>";
} else {
    $mostrar .= "<tr><td colspan='10' style='text-align: center; font-style: italic;'>No hay tarjetas registradas para esta empresa</td></tr>";
}

$mostrar .= "</tbody></table>";

/* // === RESUMEN FINAL ===
$mostrar .= '<h3 style="margin-top: 30px;">Resumen General</h3>';
$mostrar .= '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">';
$mostrar .= '<table class="table table-bordered" style="margin-bottom: 0;">';
$mostrar .= '<thead><tr>';
$mostrar .= '<th style="background-color: #28a745; color: white;">Concepto</th>';
$mostrar .= '<th style="background-color: #28a745; color: white;">Cuenta Concentradora</th>';
$mostrar .= '<th style="background-color: #28a745; color: white;">Tarjetas</th>';
$mostrar .= '<th style="background-color: #28a745; color: white;">Diferencia</th>';
$mostrar .= '</tr></thead>';
$mostrar .= '<tbody style="background:#e8f5e8;color:#222;font-weight:normal;">';

// Fila de asignaciones
$diferenciaAsignaciones = $asignacionesCC - $totalAsignacionesTarjetas;
$mostrar .= "<tr>";
$mostrar .= "<td><strong>Asignaciones</strong></td>";
$mostrar .= "<td>$" . number_format($asignacionesCC, 2) . "</td>";
$mostrar .= "<td>$" . number_format($totalAsignacionesTarjetas, 2) . "</td>";
$mostrar .= "<td style='color: " . ($diferenciaAsignaciones == 0 ? 'green' : 'red') . ";'>$" . number_format($diferenciaAsignaciones, 2) . "</td>";
$mostrar .= "</tr>";

// Fila de retiros
$diferenciaRetiros = $retirosCC - $totalRetirosTarjetas;
$mostrar .= "<tr>";
$mostrar .= "<td><strong>Retiros</strong></td>";
$mostrar .= "<td>$" . number_format($retirosCC, 2) . "</td>";
$mostrar .= "<td>$" . number_format($totalRetirosTarjetas, 2) . "</td>";
$mostrar .= "<td style='color: " . ($diferenciaRetiros == 0 ? 'green' : 'red') . ";'>$" . number_format($diferenciaRetiros, 2) . "</td>";
$mostrar .= "</tr>";

// Fila de saldo disponible
$diferenciaSaldo = $saldoDisponibleCC - $totalSaldoTarjetas;
$mostrar .= "<tr style='background-color: #d4edda; font-weight: bold;'>";
$mostrar .= "<td><strong>Saldo Disponible</strong></td>";
$mostrar .= "<td>$" . number_format($saldoDisponibleCC, 2) . "</td>";
$mostrar .= "<td>$" . number_format($totalSaldoTarjetas, 2) . "</td>";
$mostrar .= "<td style='color: " . ($diferenciaSaldo == 0 ? 'green' : 'red') . "; font-weight: bold;'>$" . number_format($diferenciaSaldo, 2) . "</td>";
$mostrar .= "</tr>";

$mostrar .= '</tbody></table>';
$mostrar .= '</div>'; */

/* // Nota informativa
if ($diferenciaSaldo != 0) {
    $mostrar .= '<div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 8px; margin-top: 15px;">';
    $mostrar .= '<strong>Nota:</strong> Existe una diferencia entre el saldo de la cuenta concentradora y las tarjetas. ';
    $mostrar .= 'Esto puede deberse a movimientos pendientes, cargos en proceso, o diferencias en el cálculo de saldos.';
    $mostrar .= '</div>';
} */
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="ligth" data-color-theme="Blue_Theme" data-layout="vertical">


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/form-bootstrap-validation.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:22 GMT -->
<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" />

  <!-- Core Css -->
  <link rel="stylesheet" href="assets/css/style.css" />
  <title>Finister</title>
</head>

<body class="link-sidebar">
  <!-- Preloader -->
  <div class="preloader">
    <img src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper">
    <!-- Sidebar Start -->
    <?php include 'header.php'; ?>


      <div class="body-wrapper">
        <div class="container-fluid">
          <div class="mb-4">
       
          </div>
         
          <!-- Mostrar solo el nuevo bloque visual -->
          <?php echo $mostrar; ?>
          <!-- Fin del bloque visual balance de cuenta -->
          <!-- El resto del HTML de tablas viejas queda comentado o eliminado para evitar duplicidad visual -->

          <!-- Botón para abrir el modal de detalle -->
          <div style="margin: 20px 0; text-align:center;">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#detalleMovimientosModal">
              Ver detalle de movimientos
            </button>
          </div>

          <!-- Modal Bootstrap para el detalle de movimientos -->
          <div class="modal fade" id="detalleMovimientosModal" tabindex="-1" aria-labelledby="detalleMovimientosLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="detalleMovimientosLabel">Detalle de movimientos y filtros</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <!-- Aquí va el bloque original de filtros, exportación y tabla detallada -->
                  <!-- INICIO BLOQUE ORIGINAL (ahora dentro del modal) -->
                  <div class="table-responsive">
                    <form method="get">
                      <table class="table mb-0 align-middle text-nowrap">
                        <thead class="text-dark fs-4">
                          <tr>
                            <th class="align-top"><h6 class="fs-11 fw-medium mb-0 ">Saldo Inicial</h6></th>
                            <th class="align-top"><h6 class="fs-11 fw-medium mb-0 ">Asignaciones de saldo a tarjetas</h6></th>
                            <th class="align-top"><h6 class="fs-11 fw-medium mb-0 ">Gasto de tarjetas</h6></th>
                            <th class="align-top"><h6 class="fs-11 fw-medium mb-0 ">Retiro saldo a tarjetas</h6></th>
                            <th class="align-top"><h6 class="fs-11 fw-medium mb-0 ">Retiro por compra</h6></th>
                            <th class="align-top"><h6 class="fs-11 fw-medium mb-0 ">Saldo disponible de asigacion</h6></th>
                            <th class="align-top"><h6 class="fs-11 fw-medium mb-0 ">Fecha</h6></th>
                            <th></th>
                          </tr>
                          <tr>
                            <td style="position:relative;">
                              <span style="cursor:pointer;" onclick="toggleFiltro(this)">&#9660;</span>
                              <input type="text" name="saldo_inicial" value="<?php echo htmlspecialchars($_GET['saldo_inicial'] ?? ''); ?>" style="width:90px; border:1px solid #ccc; background:#fff; color:#333; border-radius:4px; padding:2px 4px; display:none; position:absolute; left:0; top:20px;">
                            </td>
                            <td style="position:relative;">
                              <span style="cursor:pointer;" onclick="toggleFiltro(this)">&#9660;</span>
                              <input type="text" name="asignaciones" value="<?php echo htmlspecialchars($_GET['asignaciones'] ?? ''); ?>" style="width:90px; border:1px solid #ccc; background:#fff; color:#333; border-radius:4px; padding:2px 4px; display:none; position:absolute; left:0; top:20px;">
                            </td>
                            <td style="position:relative;">
                              <span style="cursor:pointer;" onclick="toggleFiltro(this)">&#9660;</span>
                              <input type="text" name="gasto" value="<?php echo htmlspecialchars($_GET['gasto'] ?? ''); ?>" style="width:90px; border:1px solid #ccc; background:#fff; color:#333; border-radius:4px; padding:2px 4px; display:none; position:absolute; left:0; top:20px;">
                            </td>
                            <td style="position:relative;">
                              <span style="cursor:pointer;" onclick="toggleFiltro(this)">&#9660;</span>
                              <input type="text" name="retiro_saldo" value="<?php echo htmlspecialchars($_GET['retiro_saldo'] ?? ''); ?>" style="width:90px; border:1px solid #ccc; background:#fff; color:#333; border-radius:4px; padding:2px 4px; display:none; position:absolute; left:0; top:20px;">
                            </td>
                            <td style="position:relative;">
                              <span style="cursor:pointer;" onclick="toggleFiltro(this)">&#9660;</span>
                              <input type="text" name="retiro_compra" value="<?php echo htmlspecialchars($_GET['retiro_compra'] ?? ''); ?>" style="width:90px; border:1px solid #ccc; background:#fff; color:#333; border-radius:4px; padding:2px 4px; display:none; position:absolute; left:0; top:20px;">
                            </td>
                            <td style="position:relative;">
                              <span style="cursor:pointer;" onclick="toggleFiltro(this)">&#9660;</span>
                              <input type="text" name="saldo_disponible" value="<?php echo htmlspecialchars($_GET['saldo_disponible'] ?? ''); ?>" style="width:90px; border:1px solid #ccc; background:#fff; color:#333; border-radius:4px; padding:2px 4px; display:none; position:absolute; left:0; top:20px;">
                            </td>
                            <div style="text-align:center; margin-top:10px;">
                              <button type="submit" class="btn btn-success" style="margin-right:8px;">Filtrar</button>
                            </div>
                            <td style="min-width:240px;">
                              <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($_GET['fecha_inicio'] ?? ''); ?>" style="width:110px; border:1px solid #ccc; background:#fff; color:#333; border-radius:4px; padding:2px 4px; margin-right:4px;">
                              <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($_GET['fecha_fin'] ?? ''); ?>" style="width:110px; border:1px solid #ccc; background:#fff; color:#333; border-radius:4px; padding:2px 4px;">
                              <input type="hidden" name="id_empresa" value="<?php echo htmlspecialchars($idEmpresa); ?>">
                            </td>
                            <td></td>
                          </tr>
                        </thead>
                        <tbody class="table-group-divider border-primary">
                          <?php echo $tsaldo; ?>
                        </tbody>
                      </table>
                      <!-- <div style="text-align:center; margin-top:10px;">
                        <button type="submit" class="btn btn-success" style="margin-right:8px;">Filtrar</button>
                      </div> -->
                    </form>
                    <div style="text-align:center; margin-top:10px;">
                      <a href="exportar_csv.php?<?php echo http_build_query($_GET); ?>" class="btn btn-info" style="margin-right:8px;">Exportar CSV</a>
                      <a href="exportar_xls.php?<?php echo http_build_query($_GET); ?>" class="btn btn-warning" style="margin-right:8px;">Exportar Excel</a>
                      <button type="button" onclick="window.print()" class="btn btn-danger" style="margin-right:8px;">Exportar a PDF</button>
                    </div>
                  </div>
                  <!-- FIN BLOQUE ORIGINAL (ahora dentro del modal) -->
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
            
              
            </div>
          </div>

          <div class="text-center py-3">
            <p class="mb-0">2024 © Fisinter derechos</p>
          </div>
        </div>
      </div>
      <script>
  function handleColorTheme(e) {
    document.documentElement.setAttribute("data-color-theme", e);
  }
</script>
   
    </div>
    <div class="dark-transparent sidebartoggler"></div>
  </div>
  <!-- Import Js Files -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/simplebar/dist/simplebar.min.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.dark.init.js"></script> -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/theme.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.min.js"></script> -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/sidebarmenu.js"></script>

  <!-- solar icons -->
  <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/plugins/bootstrap-validation-init.js"></script>

  <script>
  function toggleFiltro(span) {
    var input = span.nextElementSibling;
    if (input.style.display === 'none' || input.style.display === '') {
      input.style.display = 'inline-block';
      input.focus();
    } else {
      input.style.display = 'none';
    }
  }
  </script>
</body>


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/form-bootstrap-validation.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:22 GMT -->
</html>