<?php
$previousPage = $_SESSION['last_page'] ?? $web_root;

Functions::header("ErrorPage","");
?>

<body class="bg-app">
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card bg-app-card border border-danger text-light" style="">
            <div class="container text-center ">
                <div class="d-block text-danger border-bottom mb-3 border-danger pb-3">
                 
                    <span ><i style="font-size: 90px;" class="bi bi-exclamation-triangle  "></i></span>
                    <h2 class="fw-bold">ERROR</h2>
                       
                </div> 
                <p class="mt-2 text-light text-decoration-none lh-base fs-6">
                    <span class="fw-bold">
                        <?= $error ?>                        
                    </span> 
                </p>    
               
                <a href="<?= Tools::sanitize($previousPage) ?>" class="btn btn-danger mb-3 ms-1">Retour</a> 
            </div>
        </div>
    </div>        
</body>
</html>