<?php include 'functions.php'; ?>
<?php





$conn = getDbConnection();
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

$idempresa = $_GET['idempresa'] ?? null;
$empresa = null;

if ($idempresa) {
    $stmt = $conn->prepare("SELECT * FROM empresas WHERE ID_EMPRESA = ?");
    $stmt->bind_param("i", $idempresa);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $empresa = $resultado->fetch_assoc();
    }
    $stmt->close();
}

$conn->close();
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
                  <h4 class="card-title mb-0">Editar empresa</h4>
                </div>
                <div class="card-body">
                  <form class="needs-validation" method="POST" action="servicios/editarempresa.php" >
                    <div class="row">
                      <div class="col-md-4 mb-3">
                      <input type="hidden" name="idempresa" value="<?php echo htmlspecialchars($idempresa); ?>" />
                        <label class="form-label" for="validationTooltip01">Nombre</label>
                        <input type="text" class="form-control" id="validationTooltip01" placeholder="Empresa"  required name="nombre"  value="<?php echo htmlspecialchars($empresa['NOMBRE_EMPRESA'] ?? ''); ?>" />
                      <div class="invalid-tooltip">
                          Por favor proporcionar nombre de la empresa.
                        </div>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="validationTooltip02">RFC</label>
                        <input type="text" class="form-control" id="validationTooltip02" placeholder="RFC"  required name="rfc"  value="<?php echo htmlspecialchars($empresa['RFC'] ?? ''); ?>"  />
                      <div class="invalid-tooltip">
                          Por favor proporcionar el rfc de la empresa.
                        </div>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="validationTooltipUsername">Alias</label>
                        <div class="input-group">
                          <span class="input-group-text" id="validationTooltipUsernamePrepend">@</span>
                          <input type="text" class="form-control" id="validationTooltipUsername" name="alias" placeholder="Username" aria-describedby="validationTooltipUsernamePrepend" required  value="<?php echo htmlspecialchars($empresa['ALIAS'] ?? ''); ?>"  />
                          <div class="invalid-tooltip">
                            Escoje un alias.
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="validationTooltip03">Pais</label>
                        <input type="text" class="form-control" id="validationTooltip03" placeholder="Pais" name="pais"  value="<?php echo htmlspecialchars($empresa['PAIS'] ?? ''); ?>"  required />
                        <div class="invalid-tooltip">
                          Por favor proporcionar la ciudad.
                        </div>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label class="form-label" for="validationTooltip04">Estado</label>
                        <input type="text" class="form-control" id="validationTooltip04" placeholder="State" name="estado"  required   value="<?php echo htmlspecialchars($empresa['ESTADO'] ?? ''); ?>" />
                        <div class="invalid-tooltip">
                          Por favor proporcionar  el estado.
                        </div>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label class="form-label" for="validationTooltip05">Codigo postal</label>
                        <input type="text" class="form-control" id="validationTooltip05" placeholder="Zip" name="codigo" required  value="<?php echo htmlspecialchars($empresa['CODIGO_POSTAL'] ?? ''); ?>"  />
                        <div class="invalid-tooltip">
                          Por favor proporcionar el codigo postal.
                        </div>
                      </div>
                    </div>
            
                    <button class="btn btn-primary" type="submit">
                      Guardar empresa
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <div class="row" hidden>
            <div class="col-12">
            
              <div class="card">
                <div class="border-bottom title-part-padding">
                  <div class="row">
                      <div class="col-md-8 mb-3">
                        <h4 class="card-title mb-0">Tecmoin</h4>
                      </div>

                      <div class="col-md-2 mb-3">
                        <b><label class="form-label" for="validationTooltip02">Monto al corte:  5,000</label></b>
                      </div>
                      <div class="col-md-2 mb-3">
                        <b><label class="form-label" for="validationTooltip02">Tarjetas al corte:  7</label></b>
                      </div>

                  </div>
                </div>
                <div class="card-body">
                  <form class="needs-validation" method="POST" action="servicios/cargaempresa.php" >
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="validationTooltip01">Monto acumulado</label>
                        <input type="text" class="form-control" id="validationTooltip01" value="10,000"   name="nombre" readonly/>
                      
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="validationTooltip02">Numero de tarjeta acumulado</label>
                        <input type="text" class="form-control" id="validationTooltip02" value="10"   name="rfc" readonly />
                     
                      </div>
                    
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="validationTooltip01">Monto gastado</label>
                        <input type="text" class="form-control" id="validationTooltip01" value="5,000"   name="nombre" readonly/>
                      
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="validationTooltip02">Numero de tarjetas ocupadas</label>
                        <input type="text" class="form-control" id="validationTooltip02" value="3"   name="rfc" readonly />
                     
                      </div>
                    
                    </div>
                    <div class="row">
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="validationTooltip03">Agregar monto</label>
                        <input type="text" class="form-control" id="validationTooltip03" placeholder="Monto" name="pais" required />
                        <div class="invalid-tooltip">
                          Proporciona un monto.
                        </div>
                      </div>
                      <div class="col-md-2 mb-3">
                        <br>
                        <button class="btn btn-primary" type="submit">
                         Agregar
                        </button>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="validationTooltip04">Agregar tarjetas</label>
                        <input type="text" class="form-control" id="validationTooltip04" placeholder="Tarjetas" name="estado"  required />
                        <div class="invalid-tooltip">
                         Proporciona un numero de tarjetas.
                        </div>
                      </div>
                      <div class="col-md-2 mb-3">
                        <br>
                        <button class="btn btn-primary" type="submit">
                          Agregar
                        </button>
                      </div>
                      
                    </div>
                    <div class="row">
                        
                    <div class="table-responsive">
                      <table class="table mb-0 align-middle text-nowrap">
                        <thead class="text-dark fs-4">
                          <tr>
                            <th class="align-top">
                              <h6 class="fs-11 fw-medium mb-0 ">Movimiento</h6>
                            </th>
                            <th class="align-top">
                              <h6 class="fs-11 fw-medium mb-0 ">Monto</h6>
                            </th>
                            <th class="align-top">
                              <h6 class="fs-11 fw-medium mb-0 ">Tarjetas</h6>
                            </th>
                          </tr>
                        </thead>
                        <tbody class="table-group-divider border-primary">
                        <tr>
                          <td>Fecha de transacción</td>
                          <td>10/12/2024</td>
                          <td>10/12/2024</td>
                         </tr>
                         <tr>
                          <td>Deposito/Agregar</td>
                          <td>$0</td>
                          <td>10</td>
                         </tr>
                         <tr style=" background-color: lightblue !important;border: solid 1px;">
                          <td><b>Totales</b></td>
                          <td>$10,000</td>
                          <td>10</td>
                         </tr>
                         
                         <tr>
                          <td>Fecha de transacción</td>
                          <td>10/12/2024</td>
                          <td>10/12/2024</td>
                         </tr>
                         <tr>
                          <td>Deposito/Agregar</td>
                          <td>$10,000</td>
                          <td>0</td>
                         </tr>
                         <tr style=" background-color: lightblue !important;border: solid 1px;">
                          <td><b>Totales</b></td>
                          <td>$10,000</td>
                          <td>0</td>
                         </tr>
                        
                        </tbody>
                      </table>
                    </div>


                    </div>
                   
                  </form>
                </div>
              </div>
            </div>
          </div>

          <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['perfil'] === 'Superadministrador'): ?>
          <div class="row justify-content-center mt-4">
            <div class="col-md-6">
              <div class="card shadow-sm">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-3">
                    <div class="flex-grow-1">
                      <label class="form-label mb-1">Monto asignado</label>
                      <div style="font-size:2rem; font-weight:bold; color:#2a4365; background:#e9ecef; border-radius:8px; padding:10px 20px; display:inline-block; min-width:120px;">
                        $<?php echo number_format($empresa['saldo_actual'] ?? 0, 2); ?>
                      </div>
                    </div>
                  </div>
                  <hr>
                  <form method="POST" action="servicios/movimiento_saldo.php" class="mt-3">
                    <input type="hidden" name="id_empresa" value="<?php echo $empresa['ID_EMPRESA']; ?>">
                    <div class="mb-3">
                      <label for="monto" class="form-label">Monto</label>
                      <input type="number" step="0.01" min="0" class="form-control" name="monto" required placeholder="0.00">
                    </div>
                    <div class="mb-3">
                      <label for="comentario" class="form-label">Comentario</label>
                      <input type="text" class="form-control" name="comentario" placeholder="Motivo del movimiento">
                    </div>
                    <div class="d-flex gap-2">
                      <button type="submit" name="accion" value="ASIGNACION" class="btn btn-success flex-fill">Asignar monto</button>
                      <button type="submit" name="accion" value="RETIRO" class="btn btn-danger flex-fill">Retirar monto</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>

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
</body>


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/form-bootstrap-validation.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:22 GMT -->
</html>