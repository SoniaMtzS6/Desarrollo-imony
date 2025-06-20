<?php
include 'functions.php';

$id = $_GET['id'] ?? null;
$showModal = isset($_GET['success']) && $_GET['success'] == 1;

// Código de producción comentado
/*
$tokenid = "";
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.pomelo.la/oauth/token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "client_id": "cxq8Yq6wFgE53FxOzgHAGrCzRe5n4KgL",
    "client_secret": "hfGy54beNj08H6v7AnvTgo2g5zYGXZ9kyTdtpEHs5b_Vzgv1WDypnyFUz6273YO9",
    "audience": "https://auth-prod.pomelo.la",
    "grant_type": "client_credentials"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);
curl_close($curl);

if ($response) {
    $data = json_decode($response, true);
    if (isset($data['access_token'])) { 
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.pomelo.la/secure-data/v1/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(array(
                "user_id" => $id
            )),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $data['access_token']
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['access_token'])) { 
                $tokenid = "https://secure-data-web.pomelo.la/v1/activate-card?auth=".$data['access_token']."&country=mex";
                header("Location: asignartarjeta.php?id=$id&success=1");
                exit;
            }
        }
    }
}
*/

// Código local
try {
    $conn = getDbConnection();
    
    // Obtener información del usuario
    $stmt = $conn->prepare("SELECT * FROM user WHERE id_ = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $usuario = $res->fetch_assoc();
    
    if (!$usuario) {
        echo "Error al obtener información del usuario";
        exit;
    }
    
    // Combinar nombre y apellido para mostrar
    $usuario['nombre_completo'] = $usuario['name'] . ' ' . $usuario['surname'];
    
    // Crear la tabla local si no existe
    $conn->query("CREATE TABLE IF NOT EXISTS tarjetas_local (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(50) NOT NULL,
        card_id VARCHAR(50) NOT NULL,
        last_four VARCHAR(4),
        status VARCHAR(20),
        fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
} catch (Exception $e) {
    error_log("Error al obtener usuario: " . $e->getMessage());
    die("Error al obtener información del usuario");
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="ligth" data-color-theme="Blue_Theme" data-layout="vertical">


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/page-account-settings.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:21 GMT -->
<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" />

  <!-- Core Css -->
  <link rel="stylesheet" href="assets/css/style.css" />
  <title>Asignar Tarjeta</title>
</head>

<body class="link-sidebar">
  <!-- Preloader -->
  <div class="preloader">
    <img src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper">
  <?php include 'header.php'; ?>

      

      <div class="body-wrapper">
        <div class="container-fluid">
          <div class="mb-4">
            <div class="row align-items-center">
              <div class="col-md-6 col-lg-5">
                <h4 class="mb-8 breadcrumb-title"><span class="text-primary">Usuarios- asignar tarjeta</span>
                </h4>
              
              </div>
              <div class="col-md-6 col-lg-7">
                
              </div>
            </div>
          </div>
          <div class="card">
            <ul class="nav nav-pills user-profile-tab" id="pills-tab" role="tablist">
              <!-- <li class="nav-item" role="presentation">
                <button class="nav-link position-relative rounded-0 active d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-account-tab" data-bs-toggle="pill" data-bs-target="#pills-account" type="button" role="tab" aria-controls="pills-account" aria-selected="true">
                  <i class="ti ti-user-circle me-2 fs-6"></i>
                  <span class="d-none d-md-block">Cuenta</span>
                </button>
              </li> -->
             
             
              <li class="nav-item" role="presentation">
                <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-carga-tab" data-bs-toggle="pill" data-bs-target="#pills-carga" type="button" role="tab" aria-controls="pills-notifications" aria-selected="false">
                  <i class="ti ti-credit-card me-2 fs-6"></i>
                  <span class="d-none d-md-block">Asignacion</span>
                </button>
              </li>
              
              <!-- <li class="nav-item" role="presentation">
                <button class="nav-link position-relative rounded-0 d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-security" type="button" role="tab" aria-controls="pills-security" aria-selected="false">
                  <i class="ti ti-lock me-2 fs-6"></i>
                  <span class="d-none d-md-block">Seguridad</span>
                </button>
              </li> -->
            </ul>
            <div class="card-body">
              <div class="tab-content" id="pills-tabContent">
            
                <div class="tab-pane fade " id="pills-notifications" role="tabpanel" aria-labelledby="pills-notifications-tab" tabindex="0">
                  <div class="row justify-content-center">
                    <div class="col-lg-9">
                      <div class="card border shadow-none">
                        <div class="card-body p-4">
                          <h4 class="card-title">Notificaciones</h4>
                          <p class="card-subtitle mb-4">
                            En este apartado podras ver las notificaciones de compra de cada usuario.
                          </p>
                         
                          <div>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                              <div class="d-flex align-items-center gap-3">
                                <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                  <i class="ti ti-article text-dark d-block fs-7" width="22" height="22"></i>
                                </div>
                                <div>
                                  <h5 class="fs-4 fw-semibold">Compra Establecimiento 1</h5>
                                  <p class="mb-0">Realizada: 22/2/2024</p>
                                </div>
                              </div>
                              <div class="form-check form-switch mb-0">
                                <p class="mb-0">240.00</p>
                              </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center gap-3">
                                  <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                    <i class="ti ti-article text-dark d-block fs-7" width="22" height="22"></i>
                                  </div>
                                  <div>
                                    <h5 class="fs-4 fw-semibold">Compra Establecimiento 2</h5>
                                    <p class="mb-0">Realizada: 22/2/2024</p>
                                  </div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                  <p class="mb-0">240.00</p>
                                </div>
                              </div>
                              <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center gap-3">
                                  <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                    <i class="ti ti-article text-dark d-block fs-7" width="22" height="22"></i>
                                  </div>
                                  <div>
                                    <h5 class="fs-4 fw-semibold">Compra Establecimiento 13</h5>
                                    <p class="mb-0">Realizada: 22/2/2024</p>
                                  </div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                  <p class="mb-0">240.00</p>
                                </div>
                              </div>
                            
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-9">
                      <div class="card border shadow-none">
                        <div class="card-body p-4">
                          <h4 class="card-title">Saldo</h4>
                          <p class="card-subtitle">Consulta el saldo asociado</p>
                          <div class="d-flex align-items-center justify-content-between mt-7">
                            <div class="d-flex align-items-center gap-3">
                              <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                <i class="ti ti-clock-hour-4 text-dark d-block fs-7" width="22" height="22"></i>
                              </div>
                              <div>
                                <p class="mb-0">Pesos Mexicanos</p>
                                <h5 class="fs-4 fw-semibold">300.00</h5>
                              </div>
                            </div>
                            <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Download">
                              <i class="ti ti-download"></i>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    
                  </div>
                </div>
                <div class="tab-pane fade show active" id="pills-carga" role="tabpanel" aria-labelledby="pills-notifications-tab" tabindex="0">
                    <div class="row justify-content-center">
                      <div class="col-lg-9">
                        <div class="card">
                          <div class="card-body p-4">


                            <h4 class="card-title">Asignar tarjeta</h4>
                            <p class="card-subtitle mb-4">
                              Introduce numero de tarjeta y Pin y activa tu tarjeta
                            </p>
                           
                            <form id="asignarTarjetaForm" action="servicios/asignartarjeta_service.php" method="POST">
                                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id); ?>">
                                
                                <div class="mb-4">
                                    <label class="form-label">Usuario</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre_completo'] ?? ''); ?>" readonly>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Numero de 16 digitos de la tarjeta</label>
                                    <input type="text" class="form-control" name="numero_tarjeta" maxlength="16" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">PIN (4 dígitos)</label>
                                    <input type="password" class="form-control" name="pin" maxlength="4" required>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Activar</button>
                                </div>
                            </form>
                          </div>
                        </div>
                      </div>
                     
                      
                      
                    </div>
                  </div>
                <div class="tab-pane fade " id="pills-bills" role="tabpanel" aria-labelledby="pills-bills-tab" tabindex="0">
                  <div class="row justify-content-center">
                    
                    <div class="col-lg-9">
                      <div class="card border shadow-none">
                        <div class="card-body p-4">
                          <h4 class="card-title">Estado de la tarjeta : <span class="text-success">Activo</span>
                          </h4>
                          <p class="card-subtitle">Puedes consultar el estado de la tarjeta.</p>
                          <div class="d-flex align-items-center justify-content-between mt-7 mb-3">
                            <div class="d-flex align-items-center gap-3">
                              <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                <i class="ti ti-package text-dark d-block fs-7" width="22" height="22"></i>
                              </div>
                              <div>
                                <p class="mb-0">Monto asignado</p>
                                <h5 class="fs-4 fw-semibold">750.000</h5>
                              </div>
                            </div>
                            <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add">
                              <i class="ti ti-circle-plus"></i>
                            </a>
                          </div>
                          <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-primary">Activar</button>
                            <button class="btn bg-danger-subtle text-danger">Desactivar</button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-9">
                      <div class="card border shadow-none">
                        <div class="card-body p-4">
                          <h4 class="card-title">Tarjetas asignadas</h4>
                          <p class="card-subtitle">Septiembre 20, 2024</p>
                          <div class="d-flex align-items-center justify-content-between mt-7">
                            <div class="d-flex align-items-center gap-3">
                              <div class="text-bg-light rounded-1 p-6 d-flex align-items-center justify-content-center">
                                <i class="ti ti-credit-card text-dark d-block fs-7" width="22" height="22"></i>
                              </div>
                              <div>
                                <h5 class="fs-4 fw-semibold">Visa</h5>
                                <p class="mb-0 text-dark">4562 5632 5487 2102</p>
                              </div>
                            </div>
                            <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit">
                              <i class="ti ti-pencil-minus"></i>
                            </a>
                          </div>
                          <p class="my-2">Si tu cancelas la tarjeta sera desasignada del cliente y el monto regresado a la cuenta madre.</p>
                          <div class="d-flex align-items-center gap-3">
                            <button class="btn bg-danger-subtle text-danger">Eliminar tarjeta</button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="d-flex align-items-center justify-content-end gap-6">
                       
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab" tabindex="0">
                  <div class="row">
                    <div class="col-lg-8">
                      <div class="card border shadow-none">
                        <div class="card-body p-4">
                          <h4 class="card-title mb-3">Two-factor Authentication</h4>
                          <div class="d-flex align-items-center justify-content-between pb-7">
                            <p class="card-subtitle mb-0">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Corporis sapiente
                              sunt earum officiis laboriosam ut.</p>
                            <button class="btn btn-primary">Enable</button>
                          </div>
                          <div class="d-flex align-items-center justify-content-between py-3 border-top">
                            <div>
                              <h5 class="fs-4 fw-semibold mb-0">Authentication App</h5>
                              <p class="mb-0">Google auth app</p>
                            </div>
                            <button class="btn bg-primary-subtle text-primary">Setup</button>
                          </div>
                          <div class="d-flex align-items-center justify-content-between py-3 border-top">
                            <div>
                              <h5 class="fs-4 fw-semibold mb-0">Another e-mail</h5>
                              <p class="mb-0">E-mail to send verification link</p>
                            </div>
                            <button class="btn bg-primary-subtle text-primary">Setup</button>
                          </div>
                          <div class="d-flex align-items-center justify-content-between py-3 border-top">
                            <div>
                              <h5 class="fs-4 fw-semibold mb-0">SMS Recovery</h5>
                              <p class="mb-0">Your phone number or something</p>
                            </div>
                            <button class="btn bg-primary-subtle text-primary">Setup</button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="card">
                        <div class="card-body p-4">
                          <div class="text-bg-light rounded-1 p-6 d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="ti ti-device-laptop text-primary d-block fs-7" width="22" height="22"></i>
                          </div>
                          <h4 class="card-title mb-0">Devices</h4>
                          <p class="mb-3">Lorem ipsum dolor sit amet consectetur adipisicing elit Rem.</p>
                          <button class="btn btn-primary mb-4">Sign out from all devices</button>
                          <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                              <i class="ti ti-device-mobile text-dark d-block fs-7" width="26" height="26"></i>
                              <div>
                                <h5 class="fs-4 fw-semibold mb-0">iPhone 14</h5>
                                <p class="mb-0">London UK, Oct 23 at 1:15 AM</p>
                              </div>
                            </div>
                            <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)">
                              <i class="ti ti-dots-vertical"></i>
                            </a>
                          </div>
                          <div class="d-flex align-items-center justify-content-between py-3">
                            <div class="d-flex align-items-center gap-3">
                              <i class="ti ti-device-laptop text-dark d-block fs-7" width="26" height="26"></i>
                              <div>
                                <h5 class="fs-4 fw-semibold mb-0">Macbook Air</h5>
                                <p class="mb-0">Gujarat India, Oct 24 at 3:15 AM</p>
                              </div>
                            </div>
                            <a class="text-dark fs-6 d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)">
                              <i class="ti ti-dots-vertical"></i>
                            </a>
                          </div>
                          <button class="btn bg-primary-subtle text-primary w-100 py-1">Need Help ?</button>
                        </div>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="d-flex align-items-center justify-content-end gap-6">
                        <button class="btn btn-primary">Save</button>
                        <button class="btn bg-danger-subtle text-danger">Cancel</button>
                      </div>
                    </div>
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
      <button class="btn btn-primary p-3 rounded-circle d-flex align-items-center justify-content-center customizer-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
        <i class="icon ti ti-settings fs-7"></i>
      </button>

      <div class="offcanvas customizer offcanvas-end" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
          <h4 class="offcanvas-title fw-semibold" id="offcanvasExampleLabel">
            Settings
          </h4>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body h-n80" data-simplebar>
          <h6 class="fw-semibold fs-4 mb-2">Theme</h6>

          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check light-layout" name="theme-layout" id="light-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="light-layout">
              <i class="icon ti ti-brightness-up fs-7 me-2"></i>Light
            </label>

            <input type="radio" class="btn-check dark-layout" name="theme-layout" id="dark-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="dark-layout">
              <i class="icon ti ti-moon fs-7 me-2"></i>Dark
            </label>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Theme Direction</h6>
          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check" name="direction-l" id="ltr-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="ltr-layout">
              <i class="icon ti ti-text-direction-ltr fs-7 me-2"></i>LTR
            </label>

            <input type="radio" class="btn-check" name="direction-l" id="rtl-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="rtl-layout">
              <i class="icon ti ti-text-direction-rtl fs-7 me-2"></i>RTL
            </label>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Theme Colors</h6>

          <div class="d-flex flex-row flex-wrap gap-3 customizer-box color-pallete" role="group">
            <input type="radio" class="btn-check" name="color-theme-layout" id="Blue_Theme" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium d-flex align-items-center justify-content-center" onclick="handleColorTheme('Blue_Theme')" for="Blue_Theme" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="BLUE_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-1">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="Aqua_Theme" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium d-flex align-items-center justify-content-center" onclick="handleColorTheme('Aqua_Theme')" for="Aqua_Theme" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="AQUA_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-2">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="Purple_Theme" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium d-flex align-items-center justify-content-center" onclick="handleColorTheme('Purple_Theme')" for="Purple_Theme" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="PURPLE_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-3">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="green-theme-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium d-flex align-items-center justify-content-center" onclick="handleColorTheme('Green_Theme')" for="green-theme-layout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="GREEN_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-4">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="cyan-theme-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium d-flex align-items-center justify-content-center" onclick="handleColorTheme('Cyan_Theme')" for="cyan-theme-layout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="CYAN_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-5">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="orange-theme-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium d-flex align-items-center justify-content-center" onclick="handleColorTheme('Orange_Theme')" for="orange-theme-layout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="ORANGE_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-6">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Layout Type</h6>
          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <div>
              <input type="radio" class="btn-check" name="page-layout" id="vertical-layout" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary fw-medium" for="vertical-layout">
                <i class="icon ti ti-layout-sidebar-right fs-7 me-2"></i>Vertical
              </label>
            </div>
            <div>
              <input type="radio" class="btn-check" name="page-layout" id="horizontal-layout" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary fw-medium" for="horizontal-layout">
                <i class="icon ti ti-layout-navbar fs-7 me-2"></i>Horizontal
              </label>
            </div>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Container Option</h6>

          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check" name="layout" id="boxed-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="boxed-layout">
              <i class="icon ti ti-layout-distribute-vertical fs-7 me-2"></i>Boxed
            </label>

            <input type="radio" class="btn-check" name="layout" id="full-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="full-layout">
              <i class="icon ti ti-layout-distribute-horizontal fs-7 me-2"></i>Full
            </label>
          </div>

          <h6 class="fw-semibold fs-4 mb-2 mt-5">Sidebar Type</h6>
          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <a href="javascript:void(0)" class="fullsidebar">
              <input type="radio" class="btn-check" name="sidebar-type" id="full-sidebar" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary fw-medium" for="full-sidebar">
                <i class="icon ti ti-layout-sidebar-right fs-7 me-2"></i>Full
              </label>
            </a>
            <div>
              <input type="radio" class="btn-check " name="sidebar-type" id="mini-sidebar" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary fw-medium" for="mini-sidebar">
                <i class="icon ti ti-layout-sidebar fs-7 me-2"></i>Collapse
              </label>
            </div>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Card With</h6>

          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check" name="card-layout" id="card-with-border" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="card-with-border">
              <i class="icon ti ti-border-outer fs-7 me-2"></i>Border
            </label>

            <input type="radio" class="btn-check" name="card-layout" id="card-without-border" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary fw-medium" for="card-without-border">
              <i class="icon ti ti-border-none fs-7 me-2"></i>Shadow
            </label>
          </div>
        </div>
      </div>
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
  <!-- Modal de éxito -->
  <div class="modal fade" id="modalExito" tabindex="-1" aria-labelledby="modalExitoLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalExitoLabel">¡Éxito!</h5>
        </div>
        <div class="modal-body">
          La tarjeta fue asignada correctamente al usuario activo.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
        </div>
      </div>
    </div>
  </div>
  <?php if ($showModal): ?>
  <script>
    var myModal = new bootstrap.Modal(document.getElementById('modalExito'));
    window.onload = function() {
      myModal.show();
    }
  </script>
  <?php endif; ?>
</body>


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/page-account-settings.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:21 GMT -->
</html>