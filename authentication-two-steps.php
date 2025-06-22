<?php
session_start();

// Validar que exista la sesión de usuario. Si no, redirigir al login.
if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit; // Asegurarse de que el script se detiene después de la redirección
}




function enmascararCorreo($correo) {
  if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
      return "correo inválido";
  }

  [$usuario, $dominio] = explode('@', $correo);
  
  $longitud = strlen($usuario);
  
  if ($longitud <= 2) {
      $usuarioMascara = str_repeat('*', $longitud);
  } else {
      $usuarioMascara = substr($usuario, 0, 1) .
                        str_repeat('*', $longitud - 2) .
                        substr($usuario, -1);
  }

  return $usuarioMascara . '@' . $dominio;
}
// Validar si el correo se obtuvo correctamente


// Obtener el correo electrónico del usuario logueado
$correo = $_SESSION["usuario"]["email"] ?? null;

if (!$correo) {
  die("No se pudo recuperar el correo del usuario.");
  header("Location: index.php");
}

$correo = enmascararCorreo($correo);

 ?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="ligth" data-color-theme="Blue_Theme" data-layout="vertical">


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/authentication-two-steps.phpby HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:30 GMT -->
<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" />

  <!-- Core Css -->
  <link rel="stylesheet" href="assets/css/style.css" />
  <title>Interestellar Admin</title>
</head>

<body>
  <!-- Preloader -->
  <div class="preloader">
    <img src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper" class="auth-customizer-none p-0">
    <div class="position-relative overflow-hidden min-vh-100 w-100">
      <div class="position-relative">
        <div class="row gx-0">
          <div class="col-lg-6 col-xl-5 col-xxl-4 d-lg-block d-none">
            <div class="auth-left-bg min-vh-100 bg-body row justify-content-center align-items-center p-5">
              <div class="col-lg-8 bg-icon">
                <div class="text-center">
                <img src="img/logo.png" class="circle-bottom" style="width: 250px;" alt="Logo-white" />
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-xl-7 col-xxl-8 position-relative overflow-hidden bg-white">
            <div class="pt-7 pb-9 px-7 overflow-auto vh-100 bg-body row justify-content-center align-items-center">
              <div class="auth-form mt-0">
                <div class="mb-5">
                  <h3 class="fw-bolder fs-7 mb-3">Verificacion de 2 pasos</h3>
                  <p>Te hemos enviado un codigo a tu correo electronico.
                  </p>
                  <h6 class="fw-bolder"><?php echo $correo; ?></h6>
                </div>
                <form id="form-codigo" action="servicios/verificar_codigo.php" method="POST">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Introduce tu código de 6 dígitos aquí</label>
                  <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <input type="text" class="form-control text-center codigo" name="digit1" maxlength="1" required>
                    <input type="text" class="form-control text-center codigo" name="digit2" maxlength="1" required>
                    <input type="text" class="form-control text-center codigo" name="digit3" maxlength="1" required>
                    <input type="text" class="form-control text-center codigo" name="digit4" maxlength="1" required>
                    <input type="text" class="form-control text-center codigo" name="digit5" maxlength="1" required>
                    <input type="text" class="form-control text-center codigo" name="digit6" maxlength="1" required>
                  </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-8 mb-4">Verificar</button>
                <div class="d-flex align-items-center">
                  <p class="fs-4 mb-0 text-dark">¿No ves tu código?</p>
                  <a class="text-primary fw-medium ms-2" href="javascript:void(0)" onclick="reenviarCodigo()">Reenviar</a>
                  <span id="mensajeReenvio" class="ms-2 text-success d-none">Código reenviado</span>
                </div>
              </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="dark-transparent sidebartoggler"></div>

  <script>
  function reenviarCodigo() {
    fetch('servicios/reenviar_codigo.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      }
    }).then(response => response.text())
      .then(data => {
        document.getElementById('mensajeReenvio').classList.remove('d-none');
        console.log('Respuesta:', data);
      })
      .catch(error => console.error('Error:', error));
  }

  // Función para manejar la navegación automática entre casillas
  document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.codigo');
    
    inputs.forEach((input, index) => {
      // Manejar entrada de dígitos
      input.addEventListener('input', function(e) {
        const value = e.target.value;
        
        // Solo permitir números
        if (!/^\d*$/.test(value)) {
          e.target.value = '';
          return;
        }
        
        // Si se ingresó un dígito, mover al siguiente input
        if (value.length === 1 && index < inputs.length - 1) {
          inputs[index + 1].focus();
        }
      });
      
      // Manejar tecla de borrado (Backspace)
      input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
          // Si la casilla está vacía y presiona backspace, ir a la anterior
          inputs[index - 1].focus();
        }
      });
      
      // Manejar teclas de flecha
      input.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft' && index > 0) {
          inputs[index - 1].focus();
        } else if (e.key === 'ArrowRight' && index < inputs.length - 1) {
          inputs[index + 1].focus();
        }
      });
      
      // Pegar código completo
      input.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedData = (e.clipboardData || window.clipboardData).getData('text');
        const digits = pastedData.replace(/\D/g, '').slice(0, 6);
        
        if (digits.length === 6) {
          inputs.forEach((input, i) => {
            input.value = digits[i] || '';
          });
          inputs[5].focus(); // Enfocar la última casilla
        }
      });
    });
  });
</script>
  
  <!-- Import Js Files -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/simplebar/dist/simplebar.min.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.dark.init.js"></script> -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/theme.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.min.js"></script> -->

  <!-- solar icons -->
  <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
</body>


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/authentication-two-steps.phpby HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:30 GMT -->
</html>