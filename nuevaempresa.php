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
                  <h4 class="card-title mb-0">Nueva empresa</h4>
                </div>
                <div class="card-body">
                  <form class="needs-validation" method="POST" action="servicios/crear_empresa.php" >
                    <div class="row">
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="validationTooltip01">Nombre</label>
                        <input type="text" class="form-control" id="validationTooltip01" placeholder="Empresa"  required name="nombre"/>
                      <div class="invalid-tooltip">
                          Por favor proporcionar nombre de la empresa.
                        </div>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="validationTooltip02">RFC</label>
                        <input type="text" class="form-control" id="validationTooltip02" placeholder="RFC"  required name="rfc" />
                      <div class="invalid-tooltip">
                          Por favor proporcionar el rfc de la empresa.
                        </div>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="validationTooltipUsername">Alias</label>
                        <div class="input-group">
                          <span class="input-group-text" id="validationTooltipUsernamePrepend">@</span>
                          <input type="text" class="form-control" id="validationTooltipUsername" name="alias" placeholder="Username" aria-describedby="validationTooltipUsernamePrepend" required />
                          <div class="invalid-tooltip">
                            Escoje un alias.
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="validationTooltip03">Pais</label>
                        <input type="text" class="form-control" id="validationTooltip03" placeholder="Pais" name="pais" required />
                        <div class="invalid-tooltip">
                          Por favor proporcionar la ciudad.
                        </div>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label class="form-label" for="validationTooltip04">Estado</label>
                        <input type="text" class="form-control" id="validationTooltip04" placeholder="State" name="estado"  required />
                        <div class="invalid-tooltip">
                          Por favor proporcionar  el estado.
                        </div>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label class="form-label" for="validationTooltip05">Codigo postal</label>
                        <input type="text" class="form-control" id="validationTooltip05" placeholder="Zip" name="codigo" required />
                        <div class="invalid-tooltip">
                          Por favor proporcionar el codigo postal.
                        </div>
                      </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label" for="validationTooltip03">Monto maximo</label>
                          <input type="number" class="form-control" id="validationTooltip03" placeholder="monto" name="monto" required />
                          <div class="invalid-tooltip">
                            Por favor proporcionar el monto maximo.
                          </div>
                        </div>
                        <div class="col-md-3 mb-3">
                          <label class="form-label" for="validationTooltip04">Numero de tarjetas</label>
                          <input type="number" class="form-control" id="validationTooltip04" placeholder="tarjetas" name="ntarjetas" required />
                          <div class="invalid-tooltip">
                            Por favor proporcionar Numero de tarjetas.
                          </div>
                        </div>
                       
                      </div>
                    <button class="btn btn-primary" type="submit">
                      Agregar empresa
                    </button>
                  </form>
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
</body>


<!-- Mirrored from bootstrapdemos.adminmart.com/seodash/dist/dark/form-bootstrap-validation.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Sep 2024 04:46:22 GMT -->
</html>