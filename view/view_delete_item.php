<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Item...</title>
    <base href="<?= $web_root ?>">
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="font/bootstrap-icons.css">
</head>
<body class="bg-app">
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card bg-app-card border border-dark-subtle" style="width: 24rem;">
            <div class="container text-center ">
                <div class="d-block text-danger border-bottom mb-3 border-secondary pb-3">
                    <span ><i style="font-size: 90px;" class="bi bi-trash3 "></i></span>
                    <h2>Are you sure?</h2>
                </div> 
                <p class="mt-2 text-light text-decoration-none lh-base fs-6">Do you realy want to delete item 
                    <span class="fw-bold"><?= $item->getTitle()?></span> by <?= $item->getOwnerPseudo()?></p>    
                <p class="my-3 text-light text-decoration-none ">This process cannot be undone.</p>  
                <a href="item/show_item/<?= $item->getId()?>/1/<?= $from_url ?>" class="btn text-light bg-secondary mb-3 me-1">Cancel</a>
            
                <form action="item/delete_item/<?= $item->getId()?>" method="post" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <button name="delete" type="submit" class="btn btn-danger mb-3 ms-1">Delete</button> 
                </form>
            </div>
        </div>
    </div>        
</body>
</html>