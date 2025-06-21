<!DOCTYPE php>
<html lang="en" dir="ltr" data-bs-theme="ligth" data-color-theme="Blue_Theme" data-layout="vertical">


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/authentication-login.php by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:30 GMT -->
<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" />

  <!-- Core Css -->
  <link rel="stylesheet" href="assets/css/style.css" />
  <title>interstellar Admin</title>
</head>

<body>
  <!-- Preloader -->
  <div class="preloader">
    <img src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/images/logos/favicon.png" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper" class="p-0 auth-customizer-none">
    <div class="position-relative overflow-hidden min-vh-100 w-100">
      <div class="position-relative">
        <div class="row gx-0">
          <div class="col-lg-6 col-xl-5 col-xxl-4 d-lg-block d-none">
            <div class="auth-left-bg min-vh-100 bg-body row justify-content-center align-items-center p-5">
              <div class="col-lg-8 bg-icon">
                <div class="text-center">
                  <img src="img/logo.png" class="circle-bottom" style="width: 250px;" alt="Logo-white" />
                  <br>
                  <br>
                  <!-- <p class="mb-0 text-white opacity-75">
                    Lorem ipsum dolor sit amet, consectetur
                    adipiscing elit. Vivamus ullamcorper, ex eget
                    pharetra, nisi libero mollis odio, vitae suscipit eros
                    lacus tellus. Nullam et vehicula neque.
                  </p> -->
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-xl-7 col-xxl-8 position-relative overflow-hidden bg-white">
            <div class="pt-7 pb-9 px-7 overflow-auto vh-100 bg-body row justify-content-center align-items-center">
              <div class="d-flex align-items-center justify-content-sm-end justify-content-center px-0 gap-3 flex-wrap flex-md-nowrap">
                
              </div>
              <div class="d-flex flex-column justify-content-center gap-5 auth-form mx-auto">
                <div class="d-flex flex-column gap-2">
                  <h2 class="">Bienvenido a Interstellar</h2>
                  <p class="fs-3 fw-normal text-body-color">Plataforma administrativa</p>
                </div>
                <div>
                <form action="servicios/login.php" method="POST">   <!-- href="authentication-two-steps.php" -->
                    <div class="mb-4">
                      <label for="Email1" class="form-label fw-semibold fs-3 text-dark">Email </label>
                      <input type="email" class="form-control rounded-5 text-dark" name="email" id="Email1" placeholder="info@interestellar.com">
                    </div>
                    <div class="mb-4">
                      <div class="d-flex align-items-center justify-content-between">
                        <label for="Password" class="form-label fw-semibold fs-3 text-dark">Password</label>
                        <a href="authentication-forgot-password.php" class="fw-normal fs-2 text-primary link-dark">Perdiste tu password?</a>
                      </div>
                      <input type="password" class="form-control rounded-5 text-dark" name="password" id="Password" placeholder="Introduce tu password">
                    </div>
                    <div class="form-check mb-4">
                      <input type="checkbox" class="form-check-input" id="exampleCheck1" checked>
                      <label class="form-check-label" for="exampleCheck1">Mantenerme en sesion</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-8 rounded-pill">Aceder</button>
                  </form>
                  <div class="position-relative text-center my-4">
                    <p class="mb-0 px-3 d-inline-block bg-body z-index-5 position-relative">
                      
                    </p>
                    <span class="border-top w-100 position-absolute top-50 start-50 translate-middle"></span>
                  </div>
                  
                
                </div>
              </div>
              <p class="fs-11 fw-normal mt-5 mb-0 text-sm-end text-center px-0 text-body-color">2024 Derechos reservados de Interestellar</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="dark-transparent sidebartoggler"></div>
  <!-- Import Js Files -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/libs/simplebar/dist/simplebar.min.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.dark.init.js"></script> -->
  <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/theme.js"></script>
  <!-- <script src="https://bootstrapdemos.adminmart.com/seodash/dist/assets/js/theme/app.min.js"></script> -->

  <!-- solar icons -->
  <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
</body>


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/authentication-login.php by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:30 GMT -->
</html>