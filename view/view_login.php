<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In</title>
  <base href="<?= $web_root ?>">
  <link href="css/bootstrap.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <link rel="stylesheet" href="font/bootstrap-icons.css">
</head>
<body class="bg-app">
  <div class="container text-center">
      <h1 class="text-light text-center py-2"> <i class="bi bi-cart4"></i>EPayFC</h1>
      <!-- Debut carte  -->
      <div class="d-flex justify-content-center">
        <div class="card bg-app-card" style="width: 22rem;">  
            <div class="card-body mb-2">
              <h3 class="text-light text-center border-bottom pb-3 mb-3">Sign In</h3>
                <?php if (count($errors) != 0): ?>
                    <div class='text-danger'>
                        <p>Please correct the following error(s) :</p>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
              <form action="user/login" method="post">
                 <div class="input-group mb-3">
                    <span  class="input-group-text bg-icon-form" id="basic-addon1"><i class="bi bi-person text-light"></i></span>
                    <input name="email" type="text" class="form-control text-light" placeholder="Email" aria-label="Email" aria-describedby="User Email">
                  </div>
                  <div class="input-group mb-3">
                    <span  class="input-group-text bg-icon-form"  id="basic-addon1"><i class="bi bi-key text-light"></i></span>
                    <input name="password" type="password" class="form-control  text-light" placeholder="Password" aria-label="Password" aria-describedby="password user">
                  </div>
                  <button type="submit" class="btn btn-primary w-100 mb-2">Login</button>
              </form>
              <a href="item" class="btn text-light w-100 bg-secondary mb-2">Continue as guest</a>
              <a href="user/signup" class="small">New here ? Click here to subscribe !</a>
              <?php if(Configuration::is_dev()){?>      
              <h5 class="text-warning fw-bold py-2 mt-3 border-top">For Debug Purpose</h5>         
              <form action="user/debug_connexion" method="post">
                <button type="submit" class="bg-transparent border-0 text-light" name="email" value="admin@epfc.eu">Login as <span class="fw-bold">admin@epfc.eu</span></a></button>
                <button type="submit" class="bg-transparent border-0 text-light" name="email" value="boverhaegen@epfc.eu">Login as <span class="fw-bold">boverhaegen@epfc.eu</span></a></button>
                <button type="submit" class="bg-transparent border-0 text-light" name="email" value="mamichel@epfc.eu">Login as <span class="fw-bold">mamichel@epfc.eu</span></a></button>
                <button type="submit" class="bg-transparent border-0 text-light" name="email" value="quhouben@epfc.eu">Login as <span class="fw-bold">quhouben@epfc.eu</span></a></button>
                <button type="submit" class="bg-transparent border-0 text-light" name="email" value="xapigeolet@epfc.eu">Login as <span class="fw-bold">xapigeolet@epfc.eu</span></a></button>
              </form>         
           
              <a href="setup/index" class="d-block text-success fw-bold text-decoration-none">Restore original data</a>
              <a href="setup/install" class="d-block text-warning fw-bold text-decoration-none">Backup personal data</a>
              <a href="setup/restore" class="d-block text-warning fw-bold text-decoration-none">Restore personal data</a>
              <?php }?>
               
            </div>
        </div>
      </div>  
  </div>  
  
  
</body>
</html>