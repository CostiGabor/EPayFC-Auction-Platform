<?php 
// Si pas de session on en démarre une
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && http_response_code() < 400) {
    $_SESSION['last_page'] = $_SERVER['REQUEST_URI'];
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="https://code.jquery.com/ui/1.14.2/jquery-ui.js"></script>
    <script src="js/jquery.ui.touch-punch.js"></script>
    <script src="js/popper.min.js" type="text/javascript"></script>
    <script src="js/bootstrap.min.js" type="text/javascript"></script>
    
    <?php if(strtolower(trim($page_title)) == "browse" || strtolower(trim($page_title)) == "my items") { ?>
        <script src="js/search_bar.js" type="text/javascript"></script>
       
    <?php } ?>
    <?php if(strtolower(trim($page_title)) == "manage images" ) { ?>
        <script src="js/drag_and_drop.js" type="text/javascript"></script>
    <?php } ?>
    <?php if(strtolower(trim($page_title)) == "show item" ) { ?>
        <script src="js/carousel.js" type="text/javascript"></script>
        <script src="js/reviews.js" type="text/javascript"></script>
    <?php } ?>
            <!-- popUP et validations add/edit item -->
    <?php if(strtolower(trim($page_title)) == "edit item" || strtolower(trim($page_title)) == "add item") { ?>
        <script src="js/popup.js" type="text/javascript"></script>
        <script src="js/validations.js" type="text/javascript"></script>
    <?php } ?>
    <?php if(strtolower(trim($page_title)) == "categories" ) { ?>
        <script src="js/manage_categories.js" type="text/javascript"></script>
    <?php } ?>
    <?php if(strtolower(trim($page_title)) == "exam" ) { ?>
        <script src="js/exam.js" type="text/javascript"></script>
    <?php } ?>
</head>
<body class=" with-fixed-footer bg-app ">
    <header class="w-100 bg-app-bar text-app-title p-2 text-center fw-bold sticky-top  border-bottom-app-color z-2">
       <div class="d-flex align-items-center">

            <div style="width: 40px;">
                <?php if (!empty($back_url)) : ?>
                    <a href="<?= $back_url ?>" class="text-app-title">
                        <i class="bi bi-arrow-left fs-4"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="flex-grow-1 text-center">
                <?= $page_title ?>
                <i class="bi <?= $icon ?> fs-4 ms-2"></i>
            </div>

        </div>
    </header>
