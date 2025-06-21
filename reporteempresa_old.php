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
    $saldoInicial = $row["MONTO_MAXIMO"];
    $numeroTarjetas = $row["NUMERO_TARJETAS"];
} else {
    die("Empresa no encontrada.");
}
$stmt->close();

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

while ($row = $result->fetch_assoc()) {
    $entry = $row["entry_type"];
    $process = $row["process_type"];
    $type = $row["type"];
    $status = $row["result"];
    $fecha = date("Y-m-d", strtotime($row["created_at"]));
    $monto = floatval($row["monto"]);

    if ($entry === "CREDIT" && $process === "ORIGINAL" && $status == "APPROVED") {
      $depositos += $monto;
      $depositosolo = $monto;
  } elseif ($entry === "CREDIT" && $process !== "ORIGINAL" && $status == "APPROVED") {
      $asignaciones += $monto;
  } elseif ($entry === "DEBIT" && $type === "MANUAL_MOVEMENT" && $status == "APPROVED") {
      $retiros += $monto;
      $retirossolo = $monto;
  } elseif ($entry === "DEBIT" && $type !== "MANUAL_MOVEMENT" && $status == "APPROVED") {
      $retiroscompras += $monto;
      $retiroscomprassolo = $monto;
  }

    //$saldoinit = $saldoInicialt - $asignaciones - $retiros;
    $saldoinit = $depositos - $asignaciones - $retiros - $retiroscompras;

    // FILTRO POR COLUMNA (inline)
    $mostrar_fila = true;
    if (isset($_GET['saldo_inicial']) && $_GET['saldo_inicial'] !== '' && stripos((string)$saldoInicialt, $_GET['saldo_inicial']) === false) $mostrar_fila = false;
    if (isset($_GET['asignaciones']) && $_GET['asignaciones'] !== '' && stripos((string)$asignaciones, $_GET['asignaciones']) === false) $mostrar_fila = false;
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
        $tsaldo .= "<td>$".number_format($asignaciones)."</td>";
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
$saldoDisponible = $depositos - $asignaciones - $retiros - $retiroscompras;

// Mostrar datos
$mostrar = '<h2 style="font-weight:bold;">Sección "Balance de Cuenta"</h2>';
$mostrar .= '<ul>';
$mostrar .= '<li><b>Nombre Empresa:</b> ' . $empresaNombre . '</li>';
$mostrar .= '<li><b>Nombre Administrador:</b> ' . $adminNombre . '</li>';
$mostrar .= '<li><b>Periodo (Mes):</b> ' . $periodoMes . '</li>';
$mostrar .= '<li><b>Fecha:</b> ' . $fechaHoy . '</li>';
$mostrar .= '</ul>';
// $mostrar .= "<table border='1'></table>";



// <tr><th>Concepto</th><th>Monto</th></tr>
// <tr><td>Saldo Inicial</td><td>$saldoInicial</td></tr>
// <tr><td>Depósitos</td><td>$depositos</td></tr>
// <tr><td>Asignaciones a Tarjetas</td><td>$asignaciones</td></tr>
// <tr><td>Retiros A Cuenta Concentradora</td><td>$retiros</td></tr>
// <tr><td>Saldo Disponible para Asignación</td><td>$saldoDisponible</td></tr>
//echo $mostrar;
//$conn->close();

// === BLOQUE MEJORADO (NUEVA LÓGICA PARA LOCAL Y PRODUCCIÓN) ===
// Este bloque implementa la nueva estructura de columnas y cálculos para la sección "Balance de Cuenta"
// tanto para entorno local como para producción (si se desea migrar la lógica).
// Se basa únicamente en la base de datos local y sigue las definiciones de negocio proporcionadas.

// 1. Cálculo de Cuenta Concentradora
// Variables para los totales
$saldoInicialCC = $saldoInicial; // Saldo final del periodo anterior o saldo inicial si es el primer periodo
$depositosCC = $depositos; // Suma de depósitos
$asignacionesCC = $asignaciones; // Suma de asignaciones a tarjetas
$retirosCC = $retiros; // Suma de retiros a cuenta concentradora
$saldoDisponibleCC = $saldoInicialCC + $depositosCC - $asignacionesCC - $retirosCC;

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
$mostrar .= '<ul>';
$mostrar .= '<li><b>Nombre Empresa:</b> ' . $empresaNombre . '</li>';
$mostrar .= '<li><b>Nombre Administrador:</b> ' . $adminNombre . '</li>';
$mostrar .= '<li><b>Periodo (Mes):</b> ' . $periodoMes . '</li>';
$mostrar .= '<li><b>Fecha:</b> ' . $fechaHoy . '</li>';
$mostrar .= '</ul>';

// === TABLA CUENTA CONCENTRADORA ===
$mostrar .= "<h3>Cuenta Concentradora</h3>";
$mostrar .= "<table class='table table-bordered' style='background:#156082;color:white;font-weight:bold;'>";
$mostrar .= "<thead><tr>";
$mostrar .= "<th>Saldo Inicial</th><th>Depósitos</th><th>Asignaciones a Tarjetas</th><th>Retiros A Cuenta Concentradora</th><th>Saldo Disponible para Asignación</th>";
$mostrar .= "</tr></thead><tbody style='background:#eaf6fa;color:#222;font-weight:normal;'>";
$mostrar .= "<tr>";
$mostrar .= "<td>$".number_format($saldoInicialCC,2)."</td>";
$mostrar .= "<td>$".number_format($depositosCC,2)."</td>";
$mostrar .= "<td>$".number_format($asignacionesCC,2)."</td>";
$mostrar .= "<td>$".number_format($retirosCC,2)."</td>";
$mostrar .= "<td>$".number_format($saldoDisponibleCC,2)."</td>";
$mostrar .= "</tr></tbody></table>";

// === TABLA TARJETAS ===
$mostrar .= "<h3>Tarjetas</h3>";
$mostrar .= "<table class='table table-bordered' style='background:#156082;color:white;font-weight:bold;'>";
$mostrar .= "<thead><tr>";
$mostrar .= "<th>Saldo Inicial</th><th>Asignaciones</th><th>Cargos / Retiros</th><th>Retiros a Cuenta Concentradora</th><th>Saldo Disponible en Tarjetas</th>";
$mostrar .= "</tr></thead><tbody style='background:#eaf6fa;color:#222;font-weight:normal;'>";
// NOTA: Aquí debes calcular y mostrar los valores correctos por cada tarjeta si tienes esa lógica, si no, puedes mostrar una fila resumen o dejarlo preparado para el futuro.
foreach ($tarjetas as $t) {
    $mostrar .= "<tr>";
    $mostrar .= "<td>--</td>"; // Saldo Inicial (ajustar si tienes el dato)
    $mostrar .= "<td>--</td>"; // Asignaciones (ajustar si tienes el dato)
    $mostrar .= "<td>--</td>"; // Cargos / Retiros (ajustar si tienes el dato)
    $mostrar .= "<td>--</td>"; // Retiros a Cuenta Concentradora (ajustar si tienes el dato)
    $mostrar .= "<td>--</td>"; // Saldo Disponible en Tarjetas (ajustar si tienes el dato)
    $mostrar .= "</tr>";
}
$mostrar .= "</tbody></table>";
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
         

          <div class="row">
            <div class="col-12">
            
              <div class="card">
                <div class="border-bottom title-part-padding">
                  <div class="row">
                      <div class="col-md-8 mb-3">
                        <h4 class="card-title mb-0"><select name="empresa" class="form-select" aria-label="Default select example" onchange="window.location.href='?id_empresa=' + this.value;">
                                    
                                    <?php echo $empresa; ?>
                                  </select></h4>
                      </div>

                      <div class="col-md-2 mb-3">
                        <b><label class="form-label" for="validationTooltip02">Monto al corte:  $<?php echo number_format($saldoDisponible); ?></label></b>
                      </div>
                      <div class="col-md-2 mb-3">
                        <b><label class="form-label" for="validationTooltip02">Tarjetas al corte:  <?php echo number_format($numeroTarjetas-$counttarjetasusadas); ?></label></b>
                      </div>

                  </div>
                </div>
                <div class="card-body">
                  <div class="row">
                    <!-- FILTROS DE FECHA (LOCAL) -->
                    <!-- <form method="get" class="row g-3 mb-3">
                      <div class="col-auto">
                        <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                      </div>
                      <div class="col-auto">
                        <label for="fecha_fin" class="form-label">Fecha fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
                      </div>
                      <div class="col-auto align-self-end">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                      </div>
                      <input type="hidden" name="id_empresa" value="<?php echo htmlspecialchars($idEmpresa); ?>">
                    </form> -->
                  </div>
                  // === BLOQUE REPORTE VIEJO (NO ELIMINAR, USAR PARA FUTUROS REPORTES) ===
                  <!--
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
                      <div style="text-align:center; margin-top:10px;">
                        <button type="submit" class="btn btn-success" style="margin-right:8px;">Filtrar</button>
                      </div>
                    </form>
                    <div style="text-align:center; margin-top:10px;">
                      <a href="exportar_csv.php?<?php echo http_build_query($_GET); ?>" class="btn btn-info" style="margin-right:8px;">Exportar CSV</a>
                      <a href="exportar_xls.php?<?php echo http_build_query($_GET); ?>" class="btn btn-warning" style="margin-right:8px;">Exportar Excel</a>
                      <button type="button" onclick="window.print()" class="btn btn-danger" style="margin-right:8px;">Exportar a PDF</button>
                    </div>
                    -->
                  </div>
                </div>
              </div>
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